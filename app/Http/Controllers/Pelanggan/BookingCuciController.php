<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingCuciRequest;
use App\Models\{BookingCuci, Notifikasi, PaketCuci, PromoMember, RiwayatPoin};
use App\Services\{BookingCuciService, VehicleGalleryService, VerifiedCarOwnershipService};
use Illuminate\Http\Request;
use Throwable;

class BookingCuciController extends Controller
{
    private function customer(Request $r)
    {
        $p = $r->user()->pelanggan;
        abort_unless($p, 404);
        return $p;
    }

    public function index(Request $r)
    {
        $p = $this->customer($r);
        $cid = $p->id_pelanggan;
        $rows = BookingCuci::with(['kendaraan', 'paket', 'petugas', 'transaksi'])
            ->where('id_pelanggan', $cid)
            ->latest('tanggal')
            ->latest('jam')
            ->paginate(15);

        $packages = PaketCuci::where('status', 'aktif')->orderBy('harga')->get();
        $activeBookingId = $r->integer('detail') ?: null;
        $activeBooking = $activeBookingId
            ? BookingCuci::with(['kendaraan', 'paket', 'petugas', 'transaksi'])
                ->where('id_pelanggan', $cid)
                ->find($activeBookingId)
            : null;

        return view('pelanggan.booking-cuci.index', compact('rows', 'packages', 'activeBooking', 'activeBookingId'));
    }

    public function create(Request $r, VehicleGalleryService $gallery, VerifiedCarOwnershipService $ownership)
    {
        $p = $this->customer($r);
        $vehicles = $p->kendaraan()
            ->where('status', 'aktif')
            ->with(['penjualan.mobil', 'pesanan'])
            ->orderBy('merk')
            ->orderBy('model')
            ->get();

        $selectedVehicleId = (int) $r->integer('kendaraan');
        $selectedPackageId = (int) $r->integer('paket');

        $vehicles->each(function ($vehicle) use ($gallery, $ownership, $p): void {
            $mobil = $vehicle->penjualan?->mobil;
            $purchasedColor = $vehicle->pesanan?->snapshot_warna ?: $vehicle->warna;
            $selected = $mobil ? $gallery->forColor($mobil, $purchasedColor) : [];
            if (!$selected && !$purchasedColor && $mobil) {
                $selected = $gallery->for($mobil)[$gallery->defaultColor($mobil)] ?? [];
            }
            $vehicle->setAttribute('booking_image', $selected['images'][0]['src'] ?? asset('assets/images/hero/yza-hero-reference-v3.png'));
            $vehicle->setAttribute('is_verified_ownership', $ownership->vehicleBelongsToVerifiedPurchase((int) $p->id_pelanggan, (int) $vehicle->id_kendaraan_pelanggan));
        });

        $packages = PaketCuci::where('status', 'aktif')->orderBy('harga')->get();

        $allVisiblePromos = PromoMember::visible()->orderBy('selesai_at')->get();
        $claimedPromoCodes = RiwayatPoin::where('id_pelanggan', $p->id_pelanggan)
            ->where('jenis', 'penukaran')
            ->pluck('keterangan')
            ->map(fn($ket) => preg_match('/\((YZA-[A-Z0-9]+)\)/i', (string) $ket, $m) ? strtoupper($m[1]) : null)
            ->filter()
            ->unique()
            ->values()
            ->all();
        $claimedPromoIds = Notifikasi::where('id_user', $p->id_user)
            ->where('referensi_tipe', 'promo')
            ->pluck('referensi_id')
            ->map(fn($id) => (int) $id)
            ->all();

        $isClaimed = fn(PromoMember $promo) => (int) ($promo->points_cost ?? 0) === 0
            || in_array(strtoupper((string) $promo->kode_promo), $claimedPromoCodes, true)
            || in_array((int) $promo->id_promo, $claimedPromoIds, true);

        $claimedPromos = $allVisiblePromos->filter($isClaimed)->values();
        $unclaimedPromos = $allVisiblePromos->reject($isClaimed)
            ->sortByDesc(fn(PromoMember $promo) => in_array($promo->berlaku_untuk, ['cuci_mobil', 'semua'], true) ? 1 : 0)
            ->values();
        $totalUnclaimedCount = $unclaimedPromos->count();

        return view('pelanggan.booking-cuci.create', compact(
            'vehicles',
            'packages',
            'selectedVehicleId',
            'selectedPackageId',
            'claimedPromos',
            'unclaimedPromos',
            'totalUnclaimedCount'
        ));
    }

    public function store(StoreBookingCuciRequest $req, BookingCuciService $svc)
    {
        try {
            $d = $req->validated();
            $b = $svc->create(
                $this->customer($req)->id_pelanggan,
                (int) $d['id_kendaraan_pelanggan'],
                (int) $d['id_paket'],
                $d['tanggal'],
                $d['jam'],
                $d['catatan'] ?? null,
                !empty($d['id_promo']) ? (int) $d['id_promo'] : null
            );
            return redirect()
                ->route('pelanggan.booking-cuci.index')
                ->with('success', 'Booking car wash berhasil diajukan.');
        } catch (Throwable $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }
    }

    public function show(Request $r, BookingCuci $booking_cuci)
    {
        $cid = $this->customer($r)->id_pelanggan;
        abort_unless((int) $booking_cuci->id_pelanggan === (int) $cid, 403);
        $booking_cuci->load(['kendaraan', 'paket', 'petugas', 'transaksi']);
        $rows = BookingCuci::with(['kendaraan', 'paket', 'petugas', 'transaksi'])
            ->where('id_pelanggan', $cid)
            ->latest('tanggal')
            ->latest('jam')
            ->paginate(15);
        $packages = PaketCuci::where('status', 'aktif')->orderBy('harga')->get();
        $activeBooking = $booking_cuci;
        $activeBookingId = $booking_cuci->getKey();

        return view('pelanggan.booking-cuci.index', compact('rows', 'packages', 'activeBooking', 'activeBookingId'));
    }

    public function cancel(Request $r, BookingCuci $booking_cuci, BookingCuciService $svc)
    {
        abort_unless((int) $booking_cuci->id_pelanggan === (int) $this->customer($r)->id_pelanggan, 403);
        try {
            $svc->cancel($booking_cuci->id_booking_cuci, $this->customer($r)->id_pelanggan);
            return back()->with('success', 'Booking car wash dibatalkan.');
        } catch (Throwable $e) {
            return back()->withErrors($e->getMessage());
        }
    }
}
