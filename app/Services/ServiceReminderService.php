<?php

namespace App\Services;

use App\Models\{BookingServis, KendaraanPelanggan, Servis};
use Illuminate\Support\Carbon;

class ServiceReminderService
{
    public function forCustomer(int $customerId): array
    {
        $vehicles = KendaraanPelanggan::where('id_pelanggan', $customerId)->where('status', 'aktif')->orderBy('merk')->get();
        $ids = $vehicles->pluck('id_kendaraan_pelanggan');
        $services = Servis::with(['details.jenisServis', 'petugas'])->whereIn('id_kendaraan_pelanggan', $ids)
            ->where('status', 'selesai')->whereNotNull('tanggal_selesai')
            ->orderByDesc('tanggal_selesai')->get()->groupBy('id_kendaraan_pelanggan');
        $mechanicServices = Servis::with('petugas')->whereIn('id_kendaraan_pelanggan', $ids)
            ->whereNotNull('kilometer')
            ->orderByDesc('id_servis')->get()->groupBy('id_kendaraan_pelanggan');
        $activeBookings = BookingServis::with('jenisServis')->whereIn('id_kendaraan_pelanggan', $ids)
            ->whereIn('status', ['menunggu', 'diterima', 'diproses'])
            ->orderByDesc('tanggal')->orderByDesc('id_booking_servis')
            ->get()->groupBy('id_kendaraan_pelanggan');
        $completedBookings = BookingServis::with('jenisServis')->whereIn('id_kendaraan_pelanggan', $ids)
            ->where('status', 'selesai')
            ->orderByDesc('tanggal')->orderByDesc('id_booking_servis')
            ->get()->groupBy('id_kendaraan_pelanggan');
        $rows = $vehicles->map(fn($vehicle) => $this->row(
            $vehicle,
            $services->get($vehicle->id_kendaraan_pelanggan)?->first(),
            $mechanicServices->get($vehicle->id_kendaraan_pelanggan)?->first(),
            $activeBookings->get($vehicle->id_kendaraan_pelanggan)?->first(),
            $completedBookings->get($vehicle->id_kendaraan_pelanggan)?->first()
        ))->values();
        $priority = [
            'overdue' => 0,
            'due_soon' => 1,
            'booked_pending' => 2,
            'booked_confirmed' => 3,
            'in_service' => 4,
            'upcoming' => 5,
            'no_history' => 6,
        ];
        return $rows->sortBy(fn($row) => [$priority[$row['status']] ?? 9, $row['nextDate']?->timestamp ?? PHP_INT_MAX])->values()->all();
    }

    private function row(
        KendaraanPelanggan $vehicle,
        ?Servis $service,
        ?Servis $latestMechanicService = null,
        ?BookingServis $activeBooking = null,
        ?BookingServis $completedBooking = null
    ): array {
        $userKm = $vehicle->kilometer !== null ? (int) $vehicle->kilometer : 0;
        $mechRecord = $service ?? $latestMechanicService;
        $mechanicKm = $mechRecord?->kilometer !== null ? (int) $mechRecord->kilometer : null;
        $currentKm = max($userKm, (int) ($mechanicKm ?? 0));
        $fromMechanic = $mechanicKm !== null && ($service !== null || $mechanicKm > 0) && $mechanicKm >= $userKm;
        $kmSource = $fromMechanic ? 'mekanik' : 'pengguna';
        $kmSourceLabel = $fromMechanic ? 'Input Mekanik' : 'Input Pengguna';

        $milestones = [
            5000 => '5.000 km · Servis Ringan',
            10000 => '10.000 km · Servis Berkala',
            20000 => '20.000 km · Servis Menengah',
            40000 => '40.000 km · Servis Besar',
            60000 => '60.000+ km · Overhaul Ringan',
        ];

        $nextMilestoneKm = match (true) {
            $currentKm < 5000 => 5000,
            $currentKm < 10000 => 10000,
            $currentKm < 20000 => 20000,
            $currentKm < 40000 => 40000,
            default => 60000,
        };
        $nextMilestoneLabel = $milestones[$nextMilestoneKm];

        $kmMeta = [
            'userKilometer' => $userKm,
            'mechanicKilometer' => $mechanicKm,
            'currentKilometer' => $currentKm,
            'kmSource' => $kmSource,
            'kmSourceLabel' => $kmSourceLabel,
            'mechanicName' => $mechRecord?->petugas?->nama_petugas,
            'nextMilestoneKm' => $nextMilestoneKm,
            'nextMilestoneLabel' => $nextMilestoneLabel,
            'activeBooking' => $activeBooking,
            'bookingCode' => $activeBooking?->kode_booking,
            'bookingServiceName' => $activeBooking?->jenisServis?->nama_servis,
            'bookingDate' => $activeBooking?->tanggal,
            'bookingStatus' => $activeBooking?->status,
            'milestoneFulfilled' => false,
            'fulfilledKm' => null,
        ];

        if ($activeBooking) {
            [$bStatus, $bLabel] = match ($activeBooking->status) {
                'diterima' => ['booked_confirmed', 'Booking Diterima'],
                'diproses' => ['in_service', 'Sedang Diproses'],
                default => ['booked_pending', 'Menunggu Konfirmasi'],
            };

            return [
                'vehicle' => $vehicle,
                'service' => $service,
                'status' => $bStatus,
                'statusLabel' => $bLabel,
                'lastDate' => $service?->tanggal_selesai ? Carbon::instance($service->tanggal_selesai) : null,
                'nextDate' => $activeBooking->tanggal,
                'days' => null,
                'kilometer' => $service?->kilometer,
                'serviceType' => $activeBooking->jenisServis?->nama_servis ?: 'Servis Kendaraan',
                'bookingUrl' => route('pelanggan.booking-servis.index', ['detail' => $activeBooking->id_booking_servis]),
            ] + array_merge($kmMeta, [
                'milestoneFulfilled' => true,
                'fulfilledKm' => $nextMilestoneKm,
            ]);
        }

        if (!$service && $completedBooking) {
            $last = $completedBooking->tanggal ? Carbon::instance($completedBooking->tanggal) : Carbon::today('Asia/Jakarta');
            $next = $last->copy()->addMonths((int) config('yza.service_reminder.interval_months', 6));
            $days = Carbon::today('Asia/Jakarta')->diffInDays($next, false);
            $nextTierKm = match ($nextMilestoneKm) {
                5000 => 10000,
                10000 => 20000,
                20000 => 40000,
                default => 60000,
            };

            return [
                'vehicle' => $vehicle,
                'service' => null,
                'status' => $days < 0 ? 'overdue' : ($days <= (int) config('yza.service_reminder.due_soon_days', 30) ? 'due_soon' : 'upcoming'),
                'statusLabel' => $days < 0 ? 'Terlambat Servis' : ($days <= (int) config('yza.service_reminder.due_soon_days', 30) ? 'Segera Servis' : 'Servis Terpenuhi'),
                'lastDate' => $last,
                'nextDate' => $next,
                'days' => $days,
                'kilometer' => $userKm,
                'serviceType' => $completedBooking->jenisServis?->nama_servis,
                'bookingUrl' => route('pelanggan.booking-servis.create'),
            ] + array_merge($kmMeta, [
                'milestoneFulfilled' => $days >= 0,
                'fulfilledKm' => $nextMilestoneKm,
                'nextMilestoneKm' => $nextTierKm,
                'nextMilestoneLabel' => $milestones[$nextTierKm],
            ]);
        }

        if (!$service) {
            return [
                'vehicle' => $vehicle,
                'service' => null,
                'status' => 'no_history',
                'statusLabel' => 'Belum Ada Riwayat Servis',
                'lastDate' => null,
                'nextDate' => null,
                'days' => null,
                'kilometer' => null,
                'serviceType' => null,
                'bookingUrl' => route('pelanggan.booking-servis.create'),
            ] + $kmMeta;
        }

        $last = Carbon::instance($service->tanggal_selesai);
        $next = $last->copy()->addMonths((int) config('yza.service_reminder.interval_months', 6));
        $today = Carbon::today('Asia/Jakarta');
        $days = $today->diffInDays($next, false);
        if ($days < 0) [$status, $label] = ['overdue', 'Terlambat Servis'];
        elseif ($days <= (int) config('yza.service_reminder.due_soon_days', 30)) [$status, $label] = ['due_soon', 'Segera Servis'];
        else [$status, $label] = ['upcoming', 'Akan Datang'];

        $fulfilled = $status === 'upcoming';
        $nextTierKm = $fulfilled ? match ($nextMilestoneKm) {
            5000 => 10000,
            10000 => 20000,
            20000 => 40000,
            default => 60000,
        } : $nextMilestoneKm;

        return [
            'vehicle' => $vehicle,
            'service' => $service,
            'status' => $status,
            'statusLabel' => $label,
            'lastDate' => $last,
            'nextDate' => $next,
            'days' => $days,
            'kilometer' => $service->kilometer,
            'serviceType' => $service->details->first()?->jenisServis?->nama_servis,
            'bookingUrl' => route('pelanggan.booking-servis.create'),
        ] + array_merge($kmMeta, [
            'milestoneFulfilled' => $fulfilled,
            'fulfilledKm' => $fulfilled ? $nextMilestoneKm : null,
            'nextMilestoneKm' => $nextTierKm,
            'nextMilestoneLabel' => $milestones[$nextTierKm],
        ]);
    }
}
