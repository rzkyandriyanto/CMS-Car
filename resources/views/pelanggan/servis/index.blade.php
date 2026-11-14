@extends('layouts.app')
@section('title', 'Riwayat Servis · YZA Automotive')
@section('content')
@php
    $returnToGarage = request('from') === 'garage';
    $backRoute = $returnToGarage ? route('pelanggan.kendaraan.index') : route('pelanggan.member');
    $backLabel = $returnToGarage ? 'Kendaraan Saya' : 'Member Center';
    $vehicleName = trim(($selectedVehicle?->merk ?: '') . ' ' . ($selectedVehicle?->model ?: '')) ?: 'Semua Kendaraan';
    $vehiclePlate = $selectedVehicle?->nomor_polisi ?: 'Nomor polisi belum diisi';
    $lastServiceDate = $lastService?->tanggal_masuk?->format('d M Y') ?: 'Belum ada';
    $nextServiceLabel = $nextBooking?->tanggal?->format('d M Y') ?: 'Belum dijadwalkan';
    $statusLabel = fn ($status) => \App\Services\ServiceStatusPresenter::label($status);
@endphp

<div class="shx-page" data-shx-page>
    <header class="shx-hero">
        <div class="shx-hero-left">
            <a class="shx-back" href="{{ $backRoute }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                <span>Kembali ke {{ $backLabel }}</span>
            </a>
            <span class="shx-eyebrow">LAYANAN &amp; PERAWATAN</span>
            <h1>Riwayat Servis</h1>
            <p>Pantau catatan perawatan, detail mekanik, dan rincian biaya servis kendaraan Anda.</p>
        </div>
        <div class="shx-hero-actions">
            <a class="shx-btn shx-btn-secondary" href="{{ route('pelanggan.booking.index') }}">Booking Saya</a>
            <a class="shx-btn shx-btn-primary" href="{{ route('pelanggan.booking-servis.create', ['kendaraan' => $selectedVehicleId]) }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                <span>Jadwalkan Servis</span>
            </a>
        </div>
    </header>

    <section class="shx-overview" aria-label="Ringkasan kendaraan dan servis">
        <div class="shx-vehicle-card">
            <div class="shx-vehicle-thumb">
                @if($vehicleImage)
                    <img src="{{ $vehicleImage }}" alt="{{ $vehicleName }}">
                @else
                    <span class="shx-vehicle-fallback" aria-hidden="true">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                    </span>
                @endif
            </div>
            <div class="shx-vehicle-info">
                <span class="shx-kicker">KENDARAAN AKTIF</span>
                <h2>{{ $vehicleName }}</h2>
                <div class="shx-vehicle-meta">
                    <span class="shx-plate">{{ $vehiclePlate }}</span>
                    @if($selectedVehicle?->tahun)
                        <span>· {{ $selectedVehicle->tahun }}</span>
                    @endif
                    @if($selectedVehicle?->kilometer !== null)
                        <span>· {{ number_format((int) $selectedVehicle->kilometer, 0, ',', '.') }} km</span>
                    @endif
                </div>
            </div>
            @if($vehicles->isNotEmpty())
                <form class="shx-vehicle-switch" method="get">
                    @if($returnToGarage)
                        <input type="hidden" name="from" value="garage">
                    @endif
                    <label for="service-vehicle">Ganti Kendaraan</label>
                    <select id="service-vehicle" name="kendaraan" onchange="this.form.submit()">
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id_kendaraan_pelanggan }}" @selected((int) $selectedVehicleId === (int) $vehicle->id_kendaraan_pelanggan)>
                                {{ $vehicle->merk }} {{ $vehicle->model }}{{ $vehicle->nomor_polisi ? ' · ' . $vehicle->nomor_polisi : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>

        <div class="shx-kpis">
            <article class="shx-kpi">
                <div class="shx-kpi-head">
                    <span class="shx-kpi-icon is-crimson" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    </span>
                    <small>Total Servis</small>
                </div>
                <strong>{{ $serviceCount }}</strong>
                <span>Kali perawatan tercatat</span>
            </article>

            <article class="shx-kpi">
                <div class="shx-kpi-head">
                    <span class="shx-kpi-icon is-emerald" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </span>
                    <small>Servis Terakhir</small>
                </div>
                <strong>{{ $lastServiceDate }}</strong>
                <span>{{ $lastService ? $statusLabel($lastService->status) : 'Belum ada riwayat' }}</span>
            </article>

            <article class="shx-kpi">
                <div class="shx-kpi-head">
                    <span class="shx-kpi-icon is-slate" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </span>
                    <small>Jadwal Berikutnya</small>
                </div>
                <strong>{{ $nextServiceLabel }}</strong>
                <span>{{ $nextBooking?->jenisServis?->nama_servis ?: 'Jadwalkan servis rutin' }}</span>
            </article>
        </div>
    </section>

    <section class="shx-toolbar" aria-label="Filter riwayat servis">
        <div class="shx-tabs" role="tablist">
            <button type="button" class="shx-tab is-active" data-shx-filter="all">Semua</button>
            <button type="button" class="shx-tab" data-shx-filter="progress">Sedang Diproses</button>
            <button type="button" class="shx-tab" data-shx-filter="complete">Selesai</button>
        </div>

        <div class="shx-toolbar-right">
            <label class="shx-search">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="search" data-shx-search placeholder="Cari kode servis, jenis layanan, atau mekanik..." aria-label="Cari riwayat servis">
            </label>
            <span class="shx-count" data-shx-count>{{ $rows->count() }} riwayat</span>
        </div>
    </section>

    <section class="shx-list" aria-label="Daftar riwayat servis" data-shx-list>
        @forelse($rows as $service)
            @php
                $serviceStatus = strtolower((string) $service->status);
                $isComplete = str_contains($serviceStatus, 'selesai');
                $isReview = str_contains($serviceStatus, 'periksa') || str_contains($serviceStatus, 'menunggu');
                $statusClass = $isComplete ? 'is-complete' : ($isReview ? 'is-review' : 'is-progress');
                $filterBucket = $isComplete ? 'complete' : 'progress';
                $serviceType = $service->bookingServis?->jenisServis?->nama_servis
                    ?: ($service->details->first()?->jenisServis?->nama_servis ?: 'Servis Kendaraan');
                $serviceTotal = $service->transaksi?->total ?? $service->total_biaya ?? 0;
                $primaryAssignment = $service->assignments->where('assignment_role', 'primary')->sortByDesc('id_service_work_order_assignment')->first();
                $assistantNames = $service->assignments->where('assignment_role', 'assistant')->sortBy('assigned_at')->pluck('petugas.nama_lengkap')->filter()->unique()->values();
                $mechanicName = $primaryAssignment?->petugas?->nama_lengkap ?: $service->petugas?->nama_lengkap ?: 'Belum ditugaskan';
                $complaintText = $service->keluhan ?: 'Perawatan dan pemeriksaan berkala kendaraan di bengkel resmi YZA Automotive.';
                $searchBlob = strtolower($service->kode_servis . ' ' . $serviceType . ' ' . $mechanicName . ' ' . $complaintText . ' ' . $statusLabel($service->status));
            @endphp
            <article class="shx-item" data-shx-item data-status-group="{{ $filterBucket }}" data-search="{{ $searchBlob }}">
                <div class="shx-item-date">
                    <span class="shx-date-day">{{ $service->tanggal_masuk?->format('d') ?: '—' }}</span>
                    <div class="shx-date-meta">
                        <strong>{{ $service->tanggal_masuk?->format('M Y') ?: '—' }}</strong>
                        <small>{{ $service->tanggal_masuk?->format('H:i') ? $service->tanggal_masuk->format('H:i') . ' WIB' : '—' }}</small>
                    </div>
                </div>

                <div class="shx-item-body">
                    <div class="shx-item-top">
                        <span class="shx-code">{{ $service->kode_servis }}</span>
                        <h3 class="shx-item-title">{{ $serviceType }}</h3>
                        <span class="shx-status {{ $statusClass }}">{{ $statusLabel($service->status) }}</span>
                    </div>

                    <p class="shx-complaint">{{ $complaintText }}</p>

                    <div class="shx-meta-row">
                        <span class="shx-meta-chip">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Mekanik: <strong>{{ $mechanicName }}</strong>@if($assistantNames->isNotEmpty()) · Pendamping: {{ $assistantNames->join(', ') }}@endif
                        </span>
                        <span class="shx-meta-chip">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            {{ $service->tanggal_selesai ? 'Selesai ' . $service->tanggal_selesai->format('d M Y · H:i') : 'Dalam penanganan bengkel' }}
                        </span>
                    </div>
                </div>

                <div class="shx-item-aside">
                    <div class="shx-cost">
                        <small>Total Biaya</small>
                        <strong>Rp {{ number_format((float) $serviceTotal, 0, ',', '.') }}</strong>
                    </div>
                    <a class="shx-detail-btn" href="{{ route('pelanggan.servis.show', ['servis' => $service, 'from' => $returnToGarage ? 'garage' : null]) }}">
                        <span>Lihat Detail</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>
            </article>
        @empty
            <div class="shx-empty">
                <span class="shx-empty-icon" aria-hidden="true">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                </span>
                <strong>Belum ada riwayat servis</strong>
                <p>Catatan perawatan untuk {{ $vehicleName }} akan muncul secara otomatis setelah servis berjalan di bengkel.</p>
                <a class="shx-btn shx-btn-primary" href="{{ route('pelanggan.booking-servis.create', ['kendaraan' => $selectedVehicleId]) }}">Jadwalkan Servis Pertama</a>
            </div>
        @endforelse

        <div class="shx-empty" data-shx-empty-filtered hidden>
            <span class="shx-empty-icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </span>
            <strong>Tidak ada riwayat yang cocok</strong>
            <p>Coba ubah filter status atau kata kunci pencarian Anda.</p>
        </div>
    </section>

    @if($rows->hasPages())
        <div class="shx-pagination">{{ $rows->links() }}</div>
    @endif
</div>

<style>
.shx-page {
    width: min(94vw, 1340px);
    margin: 26px auto 64px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    color: #0f172a;
}
.shx-hero {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
    padding: 28px 30px;
    border-radius: 22px;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
}
.shx-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #64748b !important;
    text-decoration: none;
    margin-bottom: 12px;
    transition: color 0.15s ease;
}
.shx-back:hover {
    color: #0f172a !important;
}
.shx-eyebrow {
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.1em;
    color: #e11d48;
    margin-bottom: 4px;
}
.shx-hero h1 {
    margin: 0;
    font-size: clamp(26px, 3vw, 34px);
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #0f172a;
}
.shx-hero p {
    margin: 6px 0 0;
    font-size: 14px;
    color: #64748b;
    max-width: 560px;
}
.shx-hero-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.shx-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 18px;
    border-radius: 12px;
    font-size: 13.5px;
    font-weight: 700;
    text-decoration: none;
    transition: transform 0.15s ease, background 0.15s ease, border-color 0.15s ease;
}
.shx-btn:hover {
    transform: translateY(-1px);
}
.shx-btn-primary {
    background: #e11d48;
    color: #ffffff !important;
    box-shadow: 0 8px 20px rgba(225, 29, 72, 0.22);
}
.shx-btn-primary:hover {
    background: #be123c;
}
.shx-btn-secondary {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #0f172a !important;
}
.shx-btn-secondary:hover {
    background: #f8fafc;
    border-color: #94a3b8;
}
.shx-overview {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(0, 1.65fr);
    gap: 16px;
}
.shx-vehicle-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 20px;
    display: grid;
    grid-template-columns: 96px minmax(0, 1fr);
    gap: 16px;
    align-items: center;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.03);
}
.shx-vehicle-thumb {
    width: 96px;
    height: 76px;
    border-radius: 14px;
    overflow: hidden;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e2e8f0;
}
.shx-vehicle-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.shx-vehicle-fallback {
    color: #94a3b8;
    display: flex;
}
.shx-kicker {
    display: block;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.08em;
    color: #94a3b8;
}
.shx-vehicle-info h2 {
    margin: 2px 0 6px;
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
}
.shx-vehicle-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    font-size: 12.5px;
    color: #64748b;
}
.shx-plate {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    font-weight: 700;
    color: #0f172a;
    font-size: 12px;
}
.shx-vehicle-switch {
    grid-column: 1 / -1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
}
.shx-vehicle-switch label {
    font-size: 12.5px;
    font-weight: 700;
    color: #475569;
    white-space: nowrap;
}
.shx-vehicle-switch select {
    flex: 1;
    min-width: 0;
    padding: 9px 12px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    color: #0f172a;
    font-size: 13px;
    font-weight: 600;
}
.shx-kpis {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}
.shx-kpi {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 18px 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 8px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.03);
}
.shx-kpi-head {
    display: flex;
    align-items: center;
    gap: 10px;
}
.shx-kpi-head small {
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
}
.shx-kpi-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.shx-kpi-icon.is-crimson { background: #fff1f2; color: #e11d48; }
.shx-kpi-icon.is-emerald { background: #ecfdf5; color: #059669; }
.shx-kpi-icon.is-slate { background: #f1f5f9; color: #0f172a; }
.shx-kpi strong {
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.25;
}
.shx-kpi > span {
    font-size: 12px;
    color: #64748b;
}
.shx-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
}
.shx-tabs {
    display: inline-flex;
    padding: 4px;
    border-radius: 12px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    gap: 4px;
}
.shx-tab {
    border: 0;
    background: transparent;
    padding: 8px 14px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
}
.shx-tab.is-active {
    background: #ffffff;
    color: #0f172a;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
}
.shx-toolbar-right {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    justify-content: flex-end;
    min-width: 260px;
}
.shx-search {
    position: relative;
    flex: 1;
    max-width: 360px;
    display: flex;
    align-items: center;
}
.shx-search svg {
    position: absolute;
    left: 13px;
    color: #94a3b8;
    pointer-events: none;
}
.shx-search input {
    width: 100%;
    padding: 9px 14px 9px 38px;
    border-radius: 11px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    font-size: 13px;
    color: #0f172a;
}
.shx-search input:focus {
    outline: none;
    border-color: #0f172a;
}
.shx-count {
    font-size: 12.5px;
    font-weight: 600;
    color: #64748b;
    white-space: nowrap;
}
.shx-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.shx-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 20px 22px;
    display: grid;
    grid-template-columns: 126px minmax(0, 1fr) auto;
    gap: 20px;
    align-items: center;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.shx-item:hover {
    border-color: #cbd5e1;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
}
.shx-item[hidden] {
    display: none !important;
}
.shx-item-date {
    display: flex;
    align-items: center;
    gap: 10px;
    padding-right: 16px;
    border-right: 1px solid #f1f5f9;
}
.shx-date-day {
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1;
}
.shx-date-meta strong {
    display: block;
    font-size: 12.5px;
    font-weight: 700;
    color: #334155;
}
.shx-date-meta small {
    display: block;
    font-size: 11.5px;
    color: #94a3b8;
    margin-top: 2px;
}
.shx-item-body {
    min-width: 0;
}
.shx-item-top {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 6px;
}
.shx-code {
    padding: 3px 9px;
    border-radius: 7px;
    background: #f1f5f9;
    color: #0f172a;
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 12px;
    font-weight: 700;
}
.shx-item-title {
    margin: 0;
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
}
.shx-status {
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 700;
}
.shx-status.is-complete { background: #ecfdf5; color: #047857; }
.shx-status.is-review { background: #fffbeb; color: #b45309; }
.shx-status.is-progress { background: #eff6ff; color: #1d4ed8; }
.shx-complaint {
    margin: 0 0 10px;
    font-size: 13.5px;
    color: #475569;
    line-height: 1.5;
}
.shx-meta-row {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}
.shx-meta-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #64748b;
}
.shx-meta-chip strong {
    color: #0f172a;
    font-weight: 600;
}
.shx-item-aside {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 12px;
    padding-left: 16px;
    border-left: 1px solid #f1f5f9;
}
.shx-cost {
    text-align: right;
}
.shx-cost small {
    display: block;
    font-size: 11.5px;
    color: #64748b;
}
.shx-cost strong {
    font-size: 16.5px;
    font-weight: 800;
    color: #0f172a;
}
.shx-detail-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 15px;
    border-radius: 10px;
    background: #0f172a;
    color: #ffffff !important;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.15s ease;
}
.shx-detail-btn:hover {
    background: #e11d48;
}
.shx-empty {
    padding: 46px 24px;
    text-align: center;
    border-radius: 20px;
    border: 1px dashed #cbd5e1;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}
.shx-empty[hidden] {
    display: none !important;
}
.shx-empty-icon {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    background: #fff1f2;
    color: #e11d48;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.shx-empty strong {
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
}
.shx-empty p {
    margin: 0 0 8px;
    font-size: 13.5px;
    color: #64748b;
    max-width: 420px;
}
@media (max-width: 1024px) {
    .shx-overview {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 780px) {
    .shx-item {
        grid-template-columns: 1fr;
        gap: 14px;
    }
    .shx-item-date {
        border-right: 0;
        border-bottom: 1px solid #f1f5f9;
        padding-right: 0;
        padding-bottom: 10px;
    }
    .shx-item-aside {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        border-left: 0;
        border-top: 1px solid #f1f5f9;
        padding-left: 0;
        padding-top: 12px;
    }
    .shx-cost {
        text-align: left;
    }
    .shx-kpis {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
(() => {
    const page = document.querySelector('[data-shx-page]');
    if (!page) return;
    const tabs = [...page.querySelectorAll('[data-shx-filter]')];
    const items = [...page.querySelectorAll('[data-shx-item]')];
    const searchInput = page.querySelector('[data-shx-search]');
    const countEl = page.querySelector('[data-shx-count]');
    const emptyFiltered = page.querySelector('[data-shx-empty-filtered]');

    if (!items.length) return;

    let activeFilter = 'all';

    const applyFilter = () => {
        const query = (searchInput?.value || '').trim().toLowerCase();
        let visible = 0;

        items.forEach((item) => {
            const matchTab = activeFilter === 'all' || item.dataset.statusGroup === activeFilter;
            const matchSearch = !query || (item.dataset.search || '').includes(query);
            const show = matchTab && matchSearch;
            item.hidden = !show;
            if (show) visible++;
        });

        if (countEl) countEl.textContent = `${visible} riwayat`;
        if (emptyFiltered) emptyFiltered.hidden = visible > 0;
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            activeFilter = tab.dataset.shxFilter || 'all';
            tabs.forEach((t) => t.classList.toggle('is-active', t === tab));
            applyFilter();
        });
    });

    searchInput?.addEventListener('input', applyFilter);
})();
</script>
@endsection
