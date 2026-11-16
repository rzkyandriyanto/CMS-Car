<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\MemberDashboardService;
use App\Services\VerifiedCarOwnershipService;
use App\Services\VehicleGalleryService;
use App\Services\ServiceOperationsDashboardService;
use App\Models\{BookingMobil, MemberVehiclePreorder, Mobil, Notifikasi, Pembayaran, PesananMobilMember, Pelanggan, TestDrive, Transaksi};

class DashboardController extends Controller
{
    public function admin(Request $request, ServiceOperationsDashboardService $operations): View
    {
        $serviceData = $operations->data();
        $metrics = [
            ['label' => 'Pesanan Mobil', 'value' => PesananMobilMember::count(), 'hint' => 'Seluruh order member', 'tone' => 'red'],
            ['label' => 'Transaksi Aktif', 'value' => Transaksi::whereIn('status', ['draft', 'belum_bayar', 'dp'])->count(), 'hint' => 'Belum ditutup', 'tone' => 'amber'],
            ['label' => 'Booking Hari Ini', 'value' => BookingMobil::whereDate('tanggal_booking', now('Asia/Jakarta')->toDateString())->whereNotIn('status', ['dibatalkan', 'kadaluarsa'])->count(), 'hint' => 'Agenda showroom', 'tone' => 'dark'],
            ['label' => 'Mobil Aktif', 'value' => Mobil::whereIn('status', ['tersedia', 'dibooking', 'dalam_pemeriksaan', 'terjual'])->count(), 'hint' => 'Unit di katalog', 'tone' => 'green'],
            ['label' => 'Pelanggan', 'value' => Pelanggan::count(), 'hint' => 'Data pelanggan terdaftar', 'tone' => 'red'],
            ['label' => 'Test Drive', 'value' => TestDrive::whereNotIn('status', ['selesai', 'dibatalkan'])->count(), 'hint' => 'Agenda yang berjalan', 'tone' => 'amber'],
        ];
        $pendingOrders = PesananMobilMember::with(['pelanggan', 'mobil'])->latest('created_at')->limit(6)->get();
        $pendingPayments = Pembayaran::where('status', 'menunggu')->count();
        return view('admin.dashboard', compact('serviceData', 'metrics', 'pendingOrders', 'pendingPayments') + ['user' => $request->user(), 'area' => 'Super Admin']);
    }

    public function petugas(Request $request, ServiceOperationsDashboardService $operations): View
    {
        return view('petugas.dashboard', ['user' => $request->user(), 'area' => 'Petugas', ...$operations->data()]);
    }

    public function pelanggan(Request $request, MemberDashboardService $members, VerifiedCarOwnershipService $ownership, VehicleGalleryService $gallery)
    {
        $customer = $request->user()->pelanggan;
        abort_unless($customer, 403);
        if ($customer->membership && !$customer->membership->isActive()) {
            return redirect()->route('pelanggan.membership.activation')->with('warning', 'Aktifkan YZA Member terlebih dahulu untuk menggunakan layanan ini.');
        }

        $data = $members->for($customer, $request->user());
        $verifiedVehicles = $ownership->verifiedVehiclesFor((int) $customer->id_pelanggan);
        if ($verifiedVehicles->isNotEmpty()) {
            $verifiedVehicles->load(['penjualan.mobil', 'pesanan']);
        }

        $remindersByVehicle = collect($data['reminders'] ?? [])->keyBy(fn ($r) => (int) ($r['vehicle']?->id_kendaraan_pelanggan ?? 0));

        $ownerGarageCards = $verifiedVehicles->map(function ($vehicle) use ($gallery, $remindersByVehicle): array {
            $mobil = $vehicle->penjualan?->mobil;
            $preferredColor = $vehicle->pesanan?->snapshot_warna ?: $vehicle->warna;
            $selected = $mobil ? $gallery->forColor($mobil, $preferredColor) : [];
            if (!$selected && !$preferredColor && $mobil) {
                $selected = $gallery->for($mobil)[$gallery->defaultColor($mobil)] ?? [];
            }
            $image = $selected['images'][0]['src'] ?? asset('assets/images/hero/yza-hero-reference-v3.png');
            $rem = $remindersByVehicle->get((int) $vehicle->id_kendaraan_pelanggan);

            $userKm = (int) ($rem['userKilometer'] ?? $vehicle->kilometer ?? 0);
            $mechKm = $rem['mechanicKilometer'] ?? $rem['kilometer'] ?? null;
            $currentKm = (int) ($rem['currentKilometer'] ?? max($userKm, (int) ($mechKm ?? 0)));

            return [
                'vehicle' => $vehicle,
                'mobil' => $mobil,
                'image' => $image,
                'color' => $vehicle->pesanan?->snapshot_warna ?: $vehicle->warna ?: $mobil?->warna,
                'userKilometer' => $userKm,
                'mechanicKilometer' => $mechKm !== null ? (int) $mechKm : null,
                'currentKilometer' => $currentKm,
                'kmSource' => $rem['kmSource'] ?? ($mechKm !== null ? 'mekanik' : 'pengguna'),
                'kmSourceLabel' => $rem['kmSourceLabel'] ?? ($mechKm !== null ? 'Input Mekanik' : 'Input Pengguna'),
                'mechanicName' => $rem['mechanicName'] ?? null,
                'nextMilestoneLabel' => $rem['nextMilestoneLabel'] ?? '5.000 km · Servis Ringan',
                'lastDate' => $rem['lastDate'] ?? null,
                'nextDate' => $rem['nextDate'] ?? null,
                'statusLabel' => $rem['statusLabel'] ?? 'Belum Ada Riwayat Servis',
            ];
        })->values();

        $hasVerifiedOwnership = $verifiedVehicles->isNotEmpty();

        $activeOrder = !$hasVerifiedOwnership
            ? PesananMobilMember::with('mobil')
                ->where('id_pelanggan', $customer->id_pelanggan)
                ->whereNotIn('status', ['diserahkan', 'selesai', 'dibatalkan', 'ditolak'])
                ->latest('created_at')
                ->first()
            : null;

        $activePreorder = (!$hasVerifiedOwnership && !$activeOrder)
            ? MemberVehiclePreorder::with('mobil')
                ->where('id_pelanggan', $customer->id_pelanggan)
                ->whereNotIn('status', ['selesai', 'dibatalkan', 'ditolak'])
                ->latest('created_at')
                ->first()
            : null;

        $data['verifiedVehicles'] = $verifiedVehicles;
        $data['hasVerifiedOwnership'] = $hasVerifiedOwnership;
        $data['ownerGarageCards'] = $ownerGarageCards;
        $data['activeOrder'] = $activeOrder;
        $data['activePreorder'] = $activePreorder;
        $data['catalogCars'] = app(\App\Http\Controllers\Public\MobilController::class)->sportShowcases()->take(4)->values();
        $data['notifications'] = Notifikasi::where('id_user', $request->user()->id_user)->latest('created_at')->limit(5)->get();
        $data['notificationUnreadCount'] = Notifikasi::where('id_user', $request->user()->id_user)->where('is_read', false)->count();

        return view('pelanggan.member-center-empty', $data);
    }
}
