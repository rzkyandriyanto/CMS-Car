<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingMobilRequest;
use App\Models\{BookingCuci, BookingMobil, BookingServis, Mobil};
use App\Services\BookingMobilService;
use Illuminate\Http\Request;
use Throwable;

class BookingMobilController extends Controller
{
    private function customer(Request $r): int
    {
        return (int) $r->user()->pelanggan()->value('id_pelanggan');
    }

    public function index(Request $r)
    {
        $cid = $this->customer($r);
        $svc = app(BookingMobilService::class);
        $svc->expire();

        $rows = BookingMobil::with('mobil')->where('id_pelanggan', $cid)->latest()->paginate(15);

        $carBookings = BookingMobil::with('mobil')->where('id_pelanggan', $cid)->latest('tanggal_booking')->get();
        $serviceBookings = BookingServis::with(['kendaraan', 'jenisServis', 'promo'])
            ->where('id_pelanggan', $cid)->latest('tanggal')->latest('jam')->get();
        $washBookings = BookingCuci::with(['kendaraan', 'paket', 'transaksi'])
            ->where('id_pelanggan', $cid)->latest('tanggal')->latest('jam')->get();

        return view('pelanggan.booking.index', compact('rows', 'carBookings', 'serviceBookings', 'washBookings'));
    }

    public function create(Request $r)
    {
        $mobil = Mobil::where('status', 'tersedia')->findOrFail((int) $r->query('mobil_id'));
        return view('pelanggan.booking.create', compact('mobil'));
    }

    public function store(StoreBookingMobilRequest $req, BookingMobilService $svc)
    {
        try {
            $svc->create($this->customer($req), (int) $req->validated()['id_mobil'], $req->validated()['catatan'] ?? null);
            return redirect()->route('pelanggan.booking.index')->with('success', 'Booking berhasil diajukan.');
        } catch (Throwable $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }
    }

    public function show(Request $r, BookingMobil $booking_mobil)
    {
        abort_unless($booking_mobil->id_pelanggan === $this->customer($r), 403);
        $booking_mobil->load('mobil');
        return view('pelanggan.booking.show', compact('booking_mobil'));
    }

    public function cancel(Request $r, BookingMobil $booking_mobil, BookingMobilService $svc)
    {
        abort_unless($booking_mobil->id_pelanggan === $this->customer($r), 403);
        try {
            $svc->cancel($booking_mobil->id_booking, $this->customer($r));
            return back()->with('success', 'Booking dibatalkan.');
        } catch (Throwable $e) {
            return back()->withErrors($e->getMessage());
        }
    }
}
