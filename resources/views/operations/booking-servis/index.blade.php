@extends('layouts.app')

@section('title', 'Antrean Booking Servis · Service Advisor · YZA Automotive')

@section('content')
@php
    $isAdminPrefix = request()->is('admin/*');
    $indexRoute = route($isAdminPrefix ? 'admin.booking-servis.index' : 'petugas.booking-servis.index');
    $activeStatus = (string) request('status', '');
    $searchQuery = trim((string) request('q', ''));
    $hasFilter = $activeStatus !== '' || $searchQuery !== '';

    $totalAll = (int) ($stats['total'] ?? $rows->total());
    $pendingCount = (int) ($stats['menunggu'] ?? 0);
    $confirmedCount = (int) ($stats['diterima'] ?? 0);
    $activeWoCount = (int) ($stats['diproses'] ?? 0);
@endphp

<div class="bsa-page">
    <header class="bsa-header">
        <div class="bsa-header-copy">
            <div class="bsa-eyebrow">
                <span class="bsa-eyebrow-dot" aria-hidden="true"></span>
                <span>Service Advisor · Antrean Booking</span>
            </div>
            <h1 class="bsa-title">Antrean Booking Servis</h1>
            <p class="bsa-subtitle">Kelola reservasi servis berkala, konfirmasi slot kedatangan pelanggan, dan lakukan check-in unit.</p>
        </div>
        <div class="bsa-header-actions">
            <a href="{{ route('staff.dashboard') }}" class="bsa-btn bsa-btn-ghost">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </header>

    @if(session('success'))
        <div class="bsa-alert is-success" role="status">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bsa-alert is-error" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <section class="bsa-kpis" aria-label="Ringkasan antrean booking servis">
        <a href="{{ $indexRoute }}" class="bsa-kpi {{ $activeStatus === '' ? 'is-active' : '' }}">
            <div class="bsa-kpi-body">
                <span class="bsa-kpi-label">Total Booking Masuk</span>
                <strong class="bsa-kpi-value">{{ number_format($totalAll) }}</strong>
            </div>
            <span class="bsa-kpi-icon is-neutral" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>
                </svg>
            </span>
        </a>

        <a href="{{ $indexRoute }}?status=menunggu" class="bsa-kpi {{ $activeStatus === 'menunggu' ? 'is-active' : '' }}">
            <div class="bsa-kpi-body">
                <span class="bsa-kpi-label">Menunggu Konfirmasi</span>
                <strong class="bsa-kpi-value is-amber">{{ number_format($pendingCount) }}</strong>
            </div>
            <span class="bsa-kpi-icon is-amber" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </span>
        </a>

        <a href="{{ $indexRoute }}?status=diterima" class="bsa-kpi {{ $activeStatus === 'diterima' ? 'is-active' : '' }}">
            <div class="bsa-kpi-body">
                <span class="bsa-kpi-label">Siap Check-in</span>
                <strong class="bsa-kpi-value is-blue">{{ number_format($confirmedCount) }}</strong>
            </div>
            <span class="bsa-kpi-icon is-blue" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </span>
        </a>

        <a href="{{ $indexRoute }}?status=diproses" class="bsa-kpi {{ $activeStatus === 'diproses' ? 'is-active' : '' }}">
            <div class="bsa-kpi-body">
                <span class="bsa-kpi-label">Work Order Aktif</span>
                <strong class="bsa-kpi-value is-violet">{{ number_format($activeWoCount) }}</strong>
            </div>
            <span class="bsa-kpi-icon is-violet" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
            </span>
        </a>
    </section>

    <section class="bsa-panel">
        <div class="bsa-toolbar">
            <form method="GET" action="{{ $indexRoute }}" class="bsa-filter-form" data-bsa-filter-form>
                <div class="bsa-search-box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ $searchQuery }}"
                        placeholder="Cari kode booking, customer, nopol, atau model..."
                        aria-label="Cari booking servis"
                    >
                </div>

                <div class="bsa-select-wrap">
                    <select name="status" aria-label="Filter status reservasi" data-bsa-auto-submit>
                        <option value="">Semua Status</option>
                        @foreach(['menunggu' => 'Menunggu', 'diterima' => 'Dikonfirmasi (Siap Check-in)', 'diproses' => 'WO Aktif', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan'] as $val => $lbl)
                            <option value="{{ $val }}" @selected($activeStatus === $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    <svg class="bsa-select-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </div>

                <button type="submit" class="bsa-btn bsa-btn-primary">Filter</button>
                @if($hasFilter)
                    <a href="{{ $indexRoute }}" class="bsa-btn bsa-btn-ghost">Reset</a>
                @endif
            </form>

            <div class="bsa-view-switcher" role="tablist" aria-label="Mode tampilan">
                <button type="button" class="bsa-view-btn is-active" id="btn-view-table" onclick="setViewMode('table')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                    </svg>
                    <span>Tabel</span>
                </button>
                <button type="button" class="bsa-view-btn" id="btn-view-cards" onclick="setViewMode('cards')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    <span>Kartu</span>
                </button>
            </div>
        </div>

        <!-- 1. Table View -->
        <div id="view-table-container">
            <div class="bsa-table-wrap">
                <table class="bsa-table">
                    <thead>
                        <tr>
                            <th style="width:42px;">No</th>
                            <th>Booking</th>
                            <th>Customer</th>
                            <th>Unit</th>
                            <th>Layanan &amp; Keluhan</th>
                            <th>Jadwal</th>
                            <th>Status</th>
                            <th style="text-align:right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $b)
                            @php
                                $badge = match($b->status) {
                                    'menunggu' => ['cls' => 'is-amber', 'lbl' => 'Pending'],
                                    'diterima' => ['cls' => 'is-blue', 'lbl' => 'Dikonfirmasi'],
                                    'diproses' => ['cls' => 'is-violet', 'lbl' => 'WO Aktif'],
                                    'selesai' => ['cls' => 'is-emerald', 'lbl' => 'Selesai'],
                                    'ditolak' => ['cls' => 'is-crimson', 'lbl' => 'Ditolak'],
                                    'dibatalkan' => ['cls' => 'is-crimson', 'lbl' => 'Dibatalkan'],
                                    default => ['cls' => 'is-slate', 'lbl' => ucfirst($b->status)],
                                };
                                $detailRoute = route($isAdminPrefix ? 'admin.booking-servis.show' : 'petugas.booking-servis.show', $b);
                                $actionRoute = route($isAdminPrefix ? 'admin.booking-servis.action' : 'petugas.booking-servis.action', [$b, 'confirm']);
                            @endphp
                            <tr>
                                <td class="bsa-cell-num">{{ $loop->iteration + ($rows->currentPage() - 1) * $rows->perPage() }}</td>
                                <td>
                                    <a href="{{ $detailRoute }}" class="bsa-mono-code">{{ $b->kode_booking }}</a>
                                    <span class="bsa-subtext">{{ $b->created_at?->format('d/m/Y H:i') }}</span>
                                </td>
                                <td>
                                    <span class="bsa-primary-text">{{ $b->pelanggan?->nama_lengkap ?: 'Customer Umum' }}</span>
                                    <span class="bsa-subtext">{{ $b->pelanggan?->no_telepon ?: '—' }}</span>
                                </td>
                                <td>
                                    <span class="bsa-plate">{{ $b->kendaraan?->nomor_polisi ?: 'Plat —' }}</span>
                                    <span class="bsa-subtext">{{ trim(($b->kendaraan?->merk ?? '').' '.($b->kendaraan?->model ?? '')) ?: 'Unit' }} · {{ number_format((int)($b->kendaraan?->kilometer ?? 0)) }} km</span>
                                </td>
                                <td>
                                    <span class="bsa-primary-text">{{ $b->jenisServis?->nama_servis ?: 'General Service' }}</span>
                                    <span class="bsa-subtext" title="{{ $b->keluhan }}">{{ Str::limit($b->keluhan ?: 'Tidak ada keluhan khusus', 38) }}</span>
                                    <span class="bsa-mech-tag {{ $b->preferredPetugas ? 'is-chosen' : 'is-unassigned' }}">
                                        @if($b->servis?->petugas)
                                            Mekanik WO: {{ $b->servis->petugas->nama_lengkap }}
                                        @elseif($b->preferredPetugas)
                                            Pilihan Member: {{ $b->preferredPetugas->nama_lengkap }}
                                        @else
                                            Mekanik: Belum dipilih (SA tentukan)
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <span class="bsa-primary-text">{{ \Carbon\Carbon::parse($b->tanggal)->translatedFormat('d M Y') }}</span>
                                    <span class="bsa-subtext">{{ substr((string)$b->jam, 0, 5) }} WIB</span>
                                </td>
                                <td>
                                    <span class="bsa-status {{ $badge['cls'] }}">
                                        <i class="bsa-status-dot" aria-hidden="true"></i>
                                        {{ $badge['lbl'] }}
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <div class="bsa-row-actions">
                                        @if($b->status === 'menunggu')
                                            <form method="POST" action="{{ $actionRoute }}" onsubmit="return confirm('Konfirmasi booking {{ $b->kode_booking }}?')">
                                                @csrf
                                                <button type="submit" class="bsa-btn-sm is-success" title="Terima Reservasi">Terima</button>
                                            </form>
                                        @elseif($b->status === 'diterima' && !$b->servis)
                                            @php
                                                $checkinUrl = route($isAdminPrefix ? 'admin.servis.from-booking' : 'petugas.servis.from-booking', $b);
                                                $unitLabel = trim(($b->kendaraan?->nomor_polisi ?: 'Plat —').' · '.($b->kendaraan?->merk ?? '').' '.($b->kendaraan?->model ?? ''));
                                            @endphp
                                            <button
                                                type="button"
                                                class="bsa-btn-sm is-accent"
                                                title="Mobil Tiba - Check-in Sekarang"
                                                data-bsa-checkin
                                                data-action="{{ $checkinUrl }}"
                                                data-kode="{{ $b->kode_booking }}"
                                                data-customer="{{ $b->pelanggan?->nama_lengkap ?: 'Customer Umum' }}"
                                                data-unit="{{ $unitLabel }}"
                                                data-km="{{ (int)($b->kendaraan?->kilometer ?? 0) }}"
                                                data-catatan="{{ $b->catatan }}"
                                                data-preferred-mechanic="{{ $b->preferred_id_petugas }}"
                                                data-preferred-mechanic-name="{{ $b->preferredPetugas?->nama_lengkap }}"
                                            >Check-in</button>
                                        @endif
                                        <a class="bsa-btn-sm is-outline" href="{{ $detailRoute }}">
                                            <span>Detail</span>
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="bsa-empty-cell">
                                    <div class="bsa-empty">
                                        <span class="bsa-empty-icon" aria-hidden="true">
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                            </svg>
                                        </span>
                                        <h3>Tidak Ada Antrean Booking</h3>
                                        <p>Tidak ditemukan jadwal reservasi servis dengan kriteria filter saat ini.</p>
                                        @if($hasFilter)
                                            <a href="{{ $indexRoute }}" class="bsa-btn bsa-btn-ghost">Reset Filter</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Queue Cards View -->
        <div id="view-cards-container" style="display:none; padding: 18px;">
            <div class="bsa-queue-grid">
                @forelse($rows as $b)
                    @php
                        $badge = match($b->status) {
                            'menunggu' => ['cls' => 'is-amber', 'lbl' => 'Pending'],
                            'diterima' => ['cls' => 'is-blue', 'lbl' => 'Siap Check-in'],
                            'diproses' => ['cls' => 'is-violet', 'lbl' => 'WO Aktif'],
                            'selesai' => ['cls' => 'is-emerald', 'lbl' => 'Selesai'],
                            'ditolak' => ['cls' => 'is-crimson', 'lbl' => 'Ditolak'],
                            'dibatalkan' => ['cls' => 'is-crimson', 'lbl' => 'Dibatalkan'],
                            default => ['cls' => 'is-slate', 'lbl' => ucfirst($b->status)],
                        };
                        $detailRoute = route($isAdminPrefix ? 'admin.booking-servis.show' : 'petugas.booking-servis.show', $b);
                        $actionRoute = route($isAdminPrefix ? 'admin.booking-servis.action' : 'petugas.booking-servis.action', [$b, 'confirm']);
                    @endphp
                    <article class="bsa-queue-card">
                        <div>
                            <div class="bsa-queue-head">
                                <div>
                                    <a href="{{ $detailRoute }}" class="bsa-mono-code">{{ $b->kode_booking }}</a>
                                    <span class="bsa-subtext">{{ \Carbon\Carbon::parse($b->tanggal)->translatedFormat('d M Y') }} · {{ substr((string)$b->jam, 0, 5) }} WIB</span>
                                </div>
                                <span class="bsa-status {{ $badge['cls'] }}">
                                    <i class="bsa-status-dot" aria-hidden="true"></i>
                                    {{ $badge['lbl'] }}
                                </span>
                            </div>

                            <div class="bsa-queue-body">
                                <div class="bsa-queue-unit">
                                    <span class="bsa-plate">{{ $b->kendaraan?->nomor_polisi ?: 'Plat —' }}</span>
                                    <strong>{{ $b->kendaraan?->merk }} {{ $b->kendaraan?->model }}</strong>
                                </div>

                                <div class="bsa-queue-row">
                                    <span>Customer</span>
                                    <strong>{{ $b->pelanggan?->nama_lengkap ?: 'Customer Umum' }}</strong>
                                </div>
                                <div class="bsa-queue-row">
                                    <span>Layanan</span>
                                    <strong>{{ $b->jenisServis?->nama_servis ?: 'General Service' }}</strong>
                                </div>
                                <div class="bsa-queue-row">
                                    <span>Odometer</span>
                                    <span>{{ number_format((int)($b->kendaraan?->kilometer ?? 0)) }} km</span>
                                </div>
                                <div class="bsa-queue-row">
                                    <span>Mekanik</span>
                                    <strong>
                                        @if($b->servis?->petugas)
                                            {{ $b->servis->petugas->nama_lengkap }}
                                        @elseif($b->preferredPetugas)
                                            {{ $b->preferredPetugas->nama_lengkap }} (Pilihan Member)
                                        @else
                                            Belum dipilih (SA tentukan)
                                        @endif
                                    </strong>
                                </div>

                                @if($b->keluhan)
                                    <div class="bsa-queue-complaint">
                                        <strong>Keluhan:</strong> {{ Str::limit($b->keluhan, 90) }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="bsa-queue-footer">
                            @if($b->status === 'menunggu')
                                <form method="POST" action="{{ $actionRoute }}">
                                    @csrf
                                    <button type="submit" class="bsa-btn-sm is-success">Terima</button>
                                </form>
                            @elseif($b->status === 'diterima' && !$b->servis)
                                @php
                                    $checkinUrl = route($isAdminPrefix ? 'admin.servis.from-booking' : 'petugas.servis.from-booking', $b);
                                    $unitLabel = trim(($b->kendaraan?->nomor_polisi ?: 'Plat —').' · '.($b->kendaraan?->merk ?? '').' '.($b->kendaraan?->model ?? ''));
                                @endphp
                                <button
                                    type="button"
                                    class="bsa-btn-sm is-accent"
                                    data-bsa-checkin
                                    data-action="{{ $checkinUrl }}"
                                    data-kode="{{ $b->kode_booking }}"
                                    data-customer="{{ $b->pelanggan?->nama_lengkap ?: 'Customer Umum' }}"
                                    data-unit="{{ $unitLabel }}"
                                    data-km="{{ (int)($b->kendaraan?->kilometer ?? 0) }}"
                                    data-catatan="{{ $b->catatan }}"
                                    data-preferred-mechanic="{{ $b->preferred_id_petugas }}"
                                    data-preferred-mechanic-name="{{ $b->preferredPetugas?->nama_lengkap }}"
                                >Check-in Fisik</button>
                            @endif
                            <a href="{{ $detailRoute }}" class="bsa-btn-sm is-outline">
                                <span>Detail</span>
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>
                                </svg>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="bsa-empty" style="grid-column: 1 / -1; padding: 36px 20px;">
                        <h3>Tidak Ada Antrean Booking</h3>
                        <p>Tidak ditemukan jadwal reservasi servis dengan kriteria filter saat ini.</p>
                    </div>
                @endforelse
            </div>
        </div>

        @if($rows->hasPages())
            <div class="bsa-pagination">
                {{ $rows->links() }}
            </div>
        @endif
    </section>
</div>

<!-- Check-in Modal -->
<div class="bsa-modal-backdrop" id="bsa-checkin-modal" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="bsa-modal-title">
    <div class="bsa-modal-dialog" role="document">
        <div class="bsa-modal-head">
            <div>
                <span class="bsa-modal-eyebrow" id="bsa-modal-kode">BOOKING</span>
                <h2 class="bsa-modal-title" id="bsa-modal-title">Check-in Kendaraan</h2>
                <p class="bsa-modal-sub" id="bsa-modal-meta">—</p>
            </div>
            <button type="button" class="bsa-modal-close" data-bsa-modal-close aria-label="Tutup modal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="" id="bsa-checkin-form" class="bsa-modal-body">
            @csrf

            <div class="bsa-modal-field">
                <label for="bsa-modal-km">Odometer Saat Masuk Bengkel (KM) <span style="color:#ef233c;">*</span></label>
                <div class="bsa-km-input-wrap">
                    <input type="number" id="bsa-modal-km" name="kilometer" min="0" required placeholder="Contoh: 45000">
                    <span>KM</span>
                </div>
                <small class="bsa-modal-hint">Odometer tercatat sebelumnya: <strong id="bsa-modal-prev-km">0 km</strong></small>
            </div>

            <div class="bsa-modal-field">
                <label for="bsa-modal-mechanic">Mekanik Pelaksana <span style="color:#ef233c;">*</span></label>
                <div class="bsa-modal-select-wrap">
                    <select id="bsa-modal-mechanic" name="id_petugas" required>
                        <option value="">— Pilih Mekanik Pelaksana —</option>
                        @foreach($technicians ?? [] as $tech)
                            <option value="{{ $tech->id_petugas }}">{{ $tech->nama_lengkap }}{{ $tech->jabatan ? ' · '.$tech->jabatan : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <small class="bsa-modal-hint" id="bsa-modal-mechanic-hint">Tentukan mekanik yang akan mengerjakan unit ini.</small>
            </div>

            <div class="bsa-modal-field">
                <span class="bsa-modal-label">Checklist Kondisi Awal Unit</span>
                <div class="bsa-checklist-grid">
                    @foreach([
                        'Bahan bakar 1/2 tangki atau lebih' => 'Bahan Bakar Aman (≥ 1/2)',
                        'Ban serep & toolkit lengkap' => 'Ban Serep & Toolkit Lengkap',
                        'Barang berharga telah dikosongkan dari kabin' => 'Barang Berharga Dikosongkan',
                        'STNK asli terlampir bersama kunci' => 'STNK Terlampir',
                        'Kondisi bodi eksterior mulus tanpa baret signifikan' => 'Bodi Eksterior Mulus',
                        'Terdapat baret/lecet pemakaian pada bodi kendaraan' => 'Ada Baret / Lecet Halus',
                    ] as $val => $label)
                        <label class="bsa-check-pill">
                            <input type="checkbox" value="{{ $val }}" data-bsa-checklist>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="bsa-modal-field">
                <label for="bsa-modal-catatan">Catatan Serah Terima</label>
                <textarea id="bsa-modal-catatan" name="catatan" rows="3" placeholder="Catat kondisi fisik unit saat diterima dari pelanggan..."></textarea>
            </div>

            <div class="bsa-modal-foot">
                <button type="button" class="bsa-btn bsa-btn-ghost" data-bsa-modal-close>Batal</button>
                <button type="submit" class="bsa-btn bsa-btn-primary">Simpan Check-in &amp; Terbitkan WO</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.admin-main-content:has(.bsa-page),
.admin-content:has(.bsa-page),
main:has(.bsa-page) {
    padding-top: 10px !important;
}

.bsa-page {
    --bsa-ink: #0f172a;
    --bsa-muted: #64748b;
    --bsa-subtle: #94a3b8;
    --bsa-line: #e2e8f0;
    --bsa-line-soft: #f1f5f9;
    --bsa-surface: #ffffff;
    --bsa-bg-soft: #f8fafc;
    --bsa-accent: #ef233c;

    max-width: 1480px;
    margin: 0 auto;
    padding: 0 clamp(14px, 2.5vw, 28px) 48px;
    color: var(--bsa-ink);
}

/* 1. EDITORIAL HEADER */
.bsa-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 18px;
    flex-wrap: wrap;
    padding-bottom: 16px;
    margin-bottom: 18px;
    border-bottom: 1px solid var(--bsa-line);
}

.bsa-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--bsa-muted);
    margin-bottom: 6px;
}

.bsa-eyebrow-dot {
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: var(--bsa-accent);
}

.bsa-title {
    margin: 0 0 6px;
    font-size: clamp(1.5rem, 2.4vw, 1.95rem);
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.1;
}

.bsa-subtitle {
    margin: 0;
    font-size: 13.5px;
    color: var(--bsa-muted);
    line-height: 1.5;
}

/* 2. BUTTONS & ALERTS */
.bsa-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    height: 38px;
    padding: 0 14px;
    border-radius: 10px;
    font: inherit;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
    transition: background 0.16s ease, border-color 0.16s ease, color 0.16s ease;
    white-space: nowrap;
}

.bsa-btn-primary {
    background: var(--bsa-ink);
    color: #ffffff;
}

.bsa-btn-primary:hover {
    background: var(--bsa-accent);
}

.bsa-btn-ghost {
    background: var(--bsa-surface);
    border-color: var(--bsa-line);
    color: var(--bsa-ink);
}

.bsa-btn-ghost:hover {
    background: var(--bsa-bg-soft);
    border-color: #cbd5e1;
}

.bsa-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    margin-bottom: 18px;
    border-radius: 12px;
    font-size: 13.5px;
    font-weight: 600;
}

.bsa-alert.is-success {
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
}

.bsa-alert.is-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
}

/* 3. KPI STRIP */
.bsa-kpis {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 18px;
}

.bsa-kpi {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 15px 18px;
    background: var(--bsa-surface);
    border: 1px solid var(--bsa-line);
    border-radius: 14px;
    text-decoration: none;
    color: inherit;
    transition: border-color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
}

.bsa-kpi:hover {
    transform: translateY(-2px);
    border-color: #cbd5e1;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
}

.bsa-kpi.is-active {
    border-color: var(--bsa-ink);
    box-shadow: 0 0 0 1px var(--bsa-ink);
}

.bsa-kpi-body {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 0;
}

.bsa-kpi-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--bsa-muted);
}

.bsa-kpi-value {
    font-size: 25px;
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1;
}

.bsa-kpi-value.is-amber { color: #d97706; }
.bsa-kpi-value.is-blue { color: #2563eb; }
.bsa-kpi-value.is-violet { color: #7c3aed; }

.bsa-kpi-icon {
    flex: 0 0 40px;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: grid;
    place-items: center;
}

.bsa-kpi-icon.is-neutral { background: #f1f5f9; color: #334155; }
.bsa-kpi-icon.is-amber { background: #fffbeb; color: #d97706; }
.bsa-kpi-icon.is-blue { background: #eff6ff; color: #2563eb; }
.bsa-kpi-icon.is-violet { background: #f5f3ff; color: #7c3aed; }

/* 4. MAIN WORKSPACE PANEL */
.bsa-panel {
    background: var(--bsa-surface);
    border: 1px solid var(--bsa-line);
    border-radius: 16px;
    overflow: hidden;
}

.bsa-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    padding: 14px 18px;
    border-bottom: 1px solid var(--bsa-line);
}

.bsa-filter-form {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1 1 420px;
    flex-wrap: wrap;
}

.bsa-search-box {
    position: relative;
    flex: 1 1 240px;
    display: flex;
    align-items: center;
}

.bsa-search-box svg {
    position: absolute;
    left: 12px;
    color: var(--bsa-subtle);
    pointer-events: none;
}

.bsa-search-box input {
    width: 100%;
    height: 38px;
    padding: 0 12px 0 36px;
    border: 1px solid var(--bsa-line);
    border-radius: 10px;
    background: var(--bsa-bg-soft);
    color: var(--bsa-ink);
    font: inherit;
    font-size: 13px;
    outline: none;
}

.bsa-search-box input:focus {
    background: var(--bsa-surface);
    border-color: var(--bsa-ink);
}

.bsa-select-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.bsa-select-wrap select {
    height: 38px;
    padding: 0 32px 0 12px;
    border: 1px solid var(--bsa-line);
    border-radius: 10px;
    background: var(--bsa-bg-soft);
    color: var(--bsa-ink);
    font: inherit;
    font-size: 13px;
    font-weight: 600;
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    outline: none;
}

.bsa-select-wrap select:focus {
    background: var(--bsa-surface);
    border-color: var(--bsa-ink);
}

.bsa-select-chevron {
    position: absolute;
    right: 10px;
    color: var(--bsa-muted);
    pointer-events: none;
}

.bsa-view-switcher {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px;
    background: var(--bsa-line-soft);
    border-radius: 10px;
}

.bsa-view-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 11px;
    border: 0;
    border-radius: 7px;
    background: transparent;
    color: var(--bsa-muted);
    font: inherit;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
}

.bsa-view-btn.is-active {
    background: var(--bsa-surface);
    color: var(--bsa-ink);
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
}

/* 5. TABLE (COMPACT, NO CLIPPING) */
.bsa-table-wrap {
    overflow-x: auto;
}

.bsa-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    font-size: 13px;
}

.bsa-table th {
    padding: 12px 14px;
    background: var(--bsa-bg-soft);
    border-bottom: 1px solid var(--bsa-line);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--bsa-muted);
    white-space: nowrap;
}

.bsa-table td {
    padding: 13px 14px;
    border-bottom: 1px solid var(--bsa-line-soft);
    vertical-align: middle;
}

.bsa-table tbody tr:hover {
    background: #fafbfc;
}

.bsa-table tbody tr:last-child td {
    border-bottom: none;
}

.bsa-cell-num {
    color: var(--bsa-subtle);
    font-size: 12px;
    font-weight: 600;
}

.bsa-mono-code {
    display: inline-block;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--bsa-ink);
    text-decoration: none;
}

.bsa-mono-code:hover {
    color: var(--bsa-accent);
}

.bsa-primary-text {
    display: block;
    font-weight: 600;
    color: var(--bsa-ink);
}

.bsa-subtext {
    display: block;
    margin-top: 2px;
    font-size: 11.5px;
    color: var(--bsa-muted);
}

.bsa-plate {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    background: var(--bsa-ink);
    color: #ffffff;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
}

.bsa-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 9px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

.bsa-status-dot { width: 6px; height: 6px; border-radius: 999px; }
.bsa-status.is-amber { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.bsa-status.is-amber .bsa-status-dot { background: #f59e0b; }
.bsa-status.is-blue { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.bsa-status.is-blue .bsa-status-dot { background: #3b82f6; }
.bsa-status.is-violet { background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }
.bsa-status.is-violet .bsa-status-dot { background: #8b5cf6; }
.bsa-status.is-emerald { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.bsa-status.is-emerald .bsa-status-dot { background: #10b981; }
.bsa-status.is-crimson { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.bsa-status.is-crimson .bsa-status-dot { background: #ef4444; }
.bsa-status.is-slate { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.bsa-status.is-slate .bsa-status-dot { background: #94a3b8; }

.bsa-row-actions {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
}

.bsa-btn-sm {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    height: 32px;
    padding: 0 11px;
    border-radius: 8px;
    font: inherit;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
    white-space: nowrap;
    transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}

.bsa-btn-sm.is-accent {
    background: var(--bsa-accent);
    color: #ffffff;
}

.bsa-btn-sm.is-accent:hover {
    background: #d81b32;
}

.bsa-btn-sm.is-success {
    background: #059669;
    color: #ffffff;
}

.bsa-btn-sm.is-success:hover {
    background: #047857;
}

.bsa-btn-sm.is-outline {
    background: #ffffff;
    border-color: var(--bsa-line);
    color: var(--bsa-ink);
}

.bsa-btn-sm.is-outline:hover {
    background: var(--bsa-bg-soft);
    border-color: #cbd5e1;
}

/* 6. QUEUE CARDS VIEW */
.bsa-queue-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
    gap: 16px;
}

.bsa-queue-card {
    background: var(--bsa-surface);
    border: 1px solid var(--bsa-line);
    border-radius: 14px;
    padding: 16px 18px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 14px;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.bsa-queue-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
}

.bsa-queue-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--bsa-line-soft);
    margin-bottom: 12px;
}

.bsa-queue-unit {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 10px;
    font-size: 13px;
}

.bsa-queue-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12.5px;
    padding: 3px 0;
    color: var(--bsa-muted);
}

.bsa-queue-row strong {
    color: var(--bsa-ink);
}

.bsa-queue-complaint {
    margin-top: 10px;
    padding: 9px 11px;
    background: var(--bsa-bg-soft);
    border-left: 3px solid var(--bsa-accent);
    border-radius: 0 8px 8px 0;
    font-size: 12px;
    color: #334155;
    line-height: 1.45;
}

.bsa-queue-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    padding-top: 12px;
    border-top: 1px solid var(--bsa-line-soft);
}

/* 7. EMPTY STATE & PAGINATION */
.bsa-empty-cell {
    padding: 52px 20px !important;
    text-align: center;
}

.bsa-empty {
    max-width: 380px;
    margin: 0 auto;
}

.bsa-empty-icon {
    width: 48px;
    height: 48px;
    margin: 0 auto 14px;
    border-radius: 999px;
    background: var(--bsa-line-soft);
    color: var(--bsa-muted);
    display: grid;
    place-items: center;
}

.bsa-empty h3 {
    margin: 0 0 6px;
    font-size: 15.5px;
    font-weight: 700;
}

.bsa-empty p {
    margin: 0 0 16px;
    font-size: 13px;
    color: var(--bsa-muted);
    line-height: 1.5;
}

.bsa-pagination {
    padding: 14px 18px;
    border-top: 1px solid var(--bsa-line-soft);
}

@media (max-width: 1024px) {
    .bsa-kpis {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .bsa-kpis,
    .bsa-queue-grid {
        grid-template-columns: 1fr;
    }

    .bsa-checklist-grid {
        grid-template-columns: 1fr;
    }
}

/* 8. CHECK-IN MODAL */
.bsa-modal-backdrop[hidden] {
    display: none !important;
}

.bsa-modal-backdrop {
    --bsa-ink: #0f172a;
    --bsa-muted: #64748b;
    --bsa-subtle: #94a3b8;
    --bsa-line: #e2e8f0;
    --bsa-line-soft: #f1f5f9;
    --bsa-surface: #ffffff;
    --bsa-bg-soft: #f8fafc;
    --bsa-accent: #ef233c;

    position: fixed;
    inset: 0;
    z-index: 999;
    background: rgba(15, 23, 42, 0.58);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.bsa-modal-dialog {
    width: 100%;
    max-width: 560px;
    max-height: calc(100dvh - 32px);
    overflow-y: auto;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25);
    color: #0f172a;
}

.bsa-modal-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    padding: 18px 22px;
    border-bottom: 1px solid var(--bsa-line);
    background: var(--bsa-bg-soft);
}

.bsa-modal-eyebrow {
    display: inline-block;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 11px;
    font-weight: 700;
    color: var(--bsa-accent);
    letter-spacing: 0.04em;
    margin-bottom: 4px;
}

.bsa-modal-title {
    margin: 0 0 3px;
    font-size: 17.5px;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--bsa-ink);
}

.bsa-modal-sub {
    margin: 0;
    font-size: 12.5px;
    color: var(--bsa-muted);
}

.bsa-modal-close {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    border: 1px solid var(--bsa-line);
    background: #ffffff;
    color: var(--bsa-muted);
    display: grid;
    place-items: center;
    cursor: pointer;
    padding: 0;
    flex: 0 0 auto;
}

.bsa-modal-close:hover {
    color: var(--bsa-ink);
    border-color: #cbd5e1;
}

.bsa-modal-body {
    padding: 20px 22px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.bsa-modal-field label,
.bsa-modal-label {
    display: block;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--bsa-ink);
    margin-bottom: 7px;
}

.bsa-km-input-wrap {
    display: flex;
    align-items: center;
    border: 1px solid var(--bsa-line);
    border-radius: 10px;
    background: var(--bsa-bg-soft);
    overflow: hidden;
}

.bsa-km-input-wrap:focus-within {
    background: #ffffff;
    border-color: var(--bsa-ink);
}

.bsa-km-input-wrap input {
    flex: 1;
    height: 42px;
    padding: 0 14px;
    border: 0;
    background: transparent;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 15px;
    font-weight: 700;
    color: var(--bsa-ink);
    outline: none;
}

.bsa-km-input-wrap span {
    padding: 0 14px;
    font-size: 12px;
    font-weight: 700;
    color: var(--bsa-muted);
}

.bsa-modal-hint {
    display: block;
    margin-top: 5px;
    font-size: 11.5px;
    color: var(--bsa-muted);
}

.bsa-checklist-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
}

.bsa-check-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border: 1px solid var(--bsa-line);
    border-radius: 9px;
    background: var(--bsa-bg-soft);
    font-size: 12px;
    font-weight: 600;
    color: var(--bsa-ink);
    cursor: pointer;
    margin: 0 !important;
    transition: border-color 0.15s ease, background 0.15s ease;
}

.bsa-check-pill:has(input:checked) {
    background: #fff1f2;
    border-color: rgba(239, 35, 60, 0.35);
    color: #9f1239;
}

.bsa-check-pill input {
    accent-color: var(--bsa-accent);
    margin: 0;
}

.bsa-modal-field textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--bsa-line);
    border-radius: 10px;
    background: var(--bsa-bg-soft);
    color: var(--bsa-ink);
    font: inherit;
    font-size: 13px;
    resize: vertical;
    outline: none;
}

.bsa-modal-field textarea:focus {
    background: #ffffff;
    border-color: var(--bsa-ink);
}

.bsa-modal-foot {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 14px;
    margin-top: 4px;
    border-top: 1px solid #e2e8f0;
}

.bsa-modal-foot .bsa-btn-primary {
    background: #0f172a;
    color: #ffffff;
    padding: 0 18px;
}

.bsa-modal-foot .bsa-btn-primary:hover {
    background: #ef233c;
    color: #ffffff;
}

.bsa-mech-tag {
    display: inline-block;
    margin-top: 4px;
    padding: 2px 7px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
}

.bsa-mech-tag.is-chosen {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}

.bsa-mech-tag.is-unassigned {
    background: #f8fafc;
    color: #64748b;
    border: 1px solid #e2e8f0;
}

.bsa-modal-select-wrap select {
    width: 100%;
    height: 42px;
    padding: 0 12px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    background: #f8fafc;
    color: #0f172a;
    font: inherit;
    font-size: 13px;
    font-weight: 600;
    outline: none;
}

.bsa-modal-select-wrap select:focus {
    background: #ffffff;
    border-color: #0f172a;
}
</style>
@endpush

@push('scripts')
<script>
function setViewMode(mode) {
    const table = document.getElementById('view-table-container');
    const cards = document.getElementById('view-cards-container');
    const btnTable = document.getElementById('btn-view-table');
    const btnCards = document.getElementById('btn-view-cards');
    if (!table || !cards) return;

    if (mode === 'cards') {
        table.style.display = 'none';
        cards.style.display = 'block';
        btnCards.classList.add('is-active');
        btnTable.classList.remove('is-active');
        localStorage.setItem('yza_adv_booking_view', 'cards');
    } else {
        table.style.display = 'block';
        cards.style.display = 'none';
        btnTable.classList.add('is-active');
        btnCards.classList.remove('is-active');
        localStorage.setItem('yza_adv_booking_view', 'table');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem('yza_adv_booking_view') === 'cards') {
        setViewMode('cards');
    }

    const filterForm = document.querySelector('[data-bsa-filter-form]');
    if (filterForm) {
        filterForm.querySelectorAll('[data-bsa-auto-submit]').forEach(select => {
            select.addEventListener('change', () => filterForm.submit());
        });
    }

    const modal = document.getElementById('bsa-checkin-modal');
    const form = document.getElementById('bsa-checkin-form');
    const kodeEl = document.getElementById('bsa-modal-kode');
    const metaEl = document.getElementById('bsa-modal-meta');
    const kmInput = document.getElementById('bsa-modal-km');
    const prevKmEl = document.getElementById('bsa-modal-prev-km');
    const mechanicSelect = document.getElementById('bsa-modal-mechanic');
    const mechanicHint = document.getElementById('bsa-modal-mechanic-hint');
    const catatanInput = document.getElementById('bsa-modal-catatan');
    const checklistInputs = modal ? modal.querySelectorAll('[data-bsa-checklist]') : [];

    const closeModal = () => {
        if (!modal) return;
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    const openModal = trigger => {
        if (!modal || !form) return;
        const km = parseInt(trigger.dataset.km || '0', 10);
        const prefId = trigger.dataset.preferredMechanic || '';
        const prefName = trigger.dataset.preferredMechanicName || '';
        form.action = trigger.dataset.action || '';
        kodeEl.textContent = trigger.dataset.kode || 'BOOKING';
        metaEl.textContent = (trigger.dataset.customer || 'Pelanggan') + ' · ' + (trigger.dataset.unit || 'Unit');
        kmInput.min = String(km);
        kmInput.value = String(km);
        prevKmEl.textContent = km.toLocaleString('id-ID') + ' km';
        if (mechanicSelect) {
            mechanicSelect.value = prefId;
        }
        if (mechanicHint) {
            mechanicHint.textContent = prefName
                ? 'Pilihan member: ' + prefName + '. Anda tetap dapat menggantinya jika mekanik sedang sibuk.'
                : 'Member belum memilih mekanik. Silakan tentukan mekanik pelaksana.';
        }
        catatanInput.value = trigger.dataset.catatan || '';
        checklistInputs.forEach(cb => { cb.checked = false; });

        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        setTimeout(() => kmInput.focus(), 50);
    };

    document.querySelectorAll('[data-bsa-checkin]').forEach(btn => {
        btn.addEventListener('click', () => openModal(btn));
    });

    if (modal) {
        modal.querySelectorAll('[data-bsa-modal-close]').forEach(btn => {
            btn.addEventListener('click', closeModal);
        });
        modal.addEventListener('click', event => {
            if (event.target === modal) closeModal();
        });
    }

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && modal && !modal.hidden) {
            closeModal();
        }
    });

    checklistInputs.forEach(cb => {
        cb.addEventListener('change', () => {
            const text = cb.value;
            let lines = catatanInput.value.split('\n').map(l => l.trim()).filter(Boolean);
            const item = '- ' + text;
            if (cb.checked) {
                if (!lines.includes(item)) lines.push(item);
            } else {
                lines = lines.filter(l => l !== item);
            }
            catatanInput.value = lines.join('\n');
        });
    });
});
</script>
@endpush
