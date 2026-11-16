@extends('layouts.app')

@section('title', 'Detail Booking '.$booking->kode_booking.' · Service Advisor · YZA Automotive')

@section('content')
@php
    $isAdminPrefix = request()->is('admin/*') || $role === 'admin';
    $indexRoute = route($isAdminPrefix ? 'admin.booking-servis.index' : 'petugas.booking-servis.index');
    $confirmRoute = route($isAdminPrefix ? 'admin.booking-servis.action' : 'petugas.booking-servis.action', [$booking, 'confirm']);
    $rejectRoute = route($isAdminPrefix ? 'admin.booking-servis.action' : 'petugas.booking-servis.action', [$booking, 'reject']);
    $fromBookingRoute = route($isAdminPrefix ? 'admin.servis.from-booking' : 'petugas.servis.from-booking', $booking);
    $servisShowRoute = $booking->servis ? route($isAdminPrefix ? 'admin.servis.show' : 'petugas.servis.show', $booking->servis) : null;

    $badge = match($booking->status) {
        'menunggu' => ['cls' => 'is-amber', 'lbl' => 'Pending (Menunggu)'],
        'diterima' => ['cls' => 'is-blue', 'lbl' => 'Dikonfirmasi (Siap Check-in)'],
        'diproses' => ['cls' => 'is-violet', 'lbl' => 'Work Order Aktif'],
        'selesai' => ['cls' => 'is-emerald', 'lbl' => 'Selesai'],
        'ditolak', 'dibatalkan' => ['cls' => 'is-crimson', 'lbl' => ucfirst($booking->status)],
        default => ['cls' => 'is-slate', 'lbl' => ucfirst($booking->status)],
    };
@endphp

<div class="bsd-page">
    <header class="bsd-header">
        <div class="bsd-header-copy">
            <div class="bsd-eyebrow">
                <span class="bsd-eyebrow-dot" aria-hidden="true"></span>
                <span>Service Advisor · Detail Reservasi</span>
            </div>
            <div class="bsd-title-row">
                <h1 class="bsd-title">Booking {{ $booking->kode_booking }}</h1>
                <span class="bsd-status {{ $badge['cls'] }}">
                    <i class="bsd-status-dot" aria-hidden="true"></i>
                    {{ $badge['lbl'] }}
                </span>
            </div>
            <p class="bsd-subtitle">
                {{ $booking->kendaraan?->merk }} {{ $booking->kendaraan?->model }}
                <i aria-hidden="true">·</i>
                {{ $booking->kendaraan?->nomor_polisi ?: 'Plat Belum Diisi' }}
            </p>
        </div>
        <div class="bsd-header-actions">
            <a href="{{ $indexRoute }}" class="bsd-btn bsd-btn-ghost">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                <span>Kembali ke Antrean</span>
            </a>
        </div>
    </header>

    @if(session('success'))
        <div class="bsd-alert is-success" role="status">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="bsd-alert is-error" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="bsd-grid">
        <div class="bsd-main">
            <!-- Card 1: Identitas Kendaraan & Pelanggan -->
            <section class="bsd-card">
                <div class="bsd-card-head">
                    <h2>Identitas Kendaraan &amp; Pelanggan</h2>
                    <span class="bsd-plate">{{ $booking->kendaraan?->nomor_polisi ?: 'PLAT —' }}</span>
                </div>

                <div class="bsd-spec-grid">
                    <div class="bsd-spec-item">
                        <small>Nama Pelanggan</small>
                        <strong>{{ $booking->pelanggan?->nama_lengkap ?: 'Customer Umum' }}</strong>
                        <span>{{ $booking->pelanggan?->no_telepon ?: '—' }}</span>
                    </div>
                    <div class="bsd-spec-item">
                        <small>Unit Kendaraan</small>
                        <strong>{{ $booking->kendaraan?->merk }} {{ $booking->kendaraan?->model }}</strong>
                        <span>Tahun {{ $booking->kendaraan?->tahun ?: '—' }} · {{ number_format((int)($booking->kendaraan?->kilometer ?? 0)) }} km</span>
                    </div>
                    <div class="bsd-spec-item">
                        <small>Paket Layanan Servis</small>
                        <strong>{{ $booking->jenisServis?->nama_servis ?: 'General Service' }}</strong>
                        <span>Estimasi durasi ± 1–2 jam</span>
                    </div>
                    <div class="bsd-spec-item">
                        <small>Jadwal Kedatangan</small>
                        <strong>{{ \Carbon\Carbon::parse($booking->tanggal)->translatedFormat('d M Y') }}</strong>
                        <span>Pukul {{ substr((string)$booking->jam, 0, 5) }} WIB</span>
                    </div>
                </div>

                @if($booking->preferredPetugas)
                    <div class="bsd-pref-note">
                        <span>Preferensi Teknisi dari Pelanggan:</span>
                        <strong>{{ $booking->preferredPetugas->nama_lengkap }}</strong>
                    </div>
                @endif

                <div class="bsd-complaint-wrap">
                    <span class="bsd-field-label">Keluhan &amp; Catatan Masuk</span>
                    <div class="bsd-complaint-box">
                        {{ $booking->keluhan ?: 'Tidak ada catatan keluhan khusus yang dicantumkan saat reservasi online.' }}
                    </div>
                </div>
            </section>

            <!-- Card 2: Aksi Konfirmasi / Intake Check-in / WO Terbit -->
            <section class="bsd-card">
                <div class="bsd-card-head">
                    <h2>Serah Terima Fisik (Intake Check-in)</h2>
                    <span class="bsd-card-hint">Langkah Service Advisor</span>
                </div>

                @if($booking->status === 'menunggu')
                    <div class="bsd-notice is-amber">
                        <strong>Reservasi Masih Berstatus Pending</strong>
                        <p>Konfirmasi penerimaan reservasi terlebih dahulu sebelum melakukan check-in fisik kendaraan.</p>
                    </div>
                    <div class="bsd-action-row">
                        <form id="bsd-confirm-form" method="POST" action="{{ $confirmRoute }}">
                            @csrf
                            <button type="button" class="bsd-btn bsd-btn-success" onclick="openBookingConfirmModal('bsd-confirm-form', 'Konfirmasi Reservasi Booking', 'Apakah Anda yakin ingin mengonfirmasi dan menerima reservasi booking servis ini?', 'Ya, Konfirmasi & Terima', 'is-success')">Konfirmasi &amp; Terima Booking</button>
                        </form>
                        <form id="bsd-reject-form" method="POST" action="{{ $rejectRoute }}">
                            @csrf
                            <button type="button" class="bsd-btn bsd-btn-danger-outline" onclick="openBookingConfirmModal('bsd-reject-form', 'Tolak Reservasi Booking', 'Apakah Anda yakin ingin menolak reservasi booking servis ini?', 'Ya, Tolak Reservasi', 'is-danger')">Tolak Reservasi</button>
                        </form>
                    </div>

                @elseif($booking->status === 'diterima' && !$booking->servis)
                    <form method="POST" action="{{ $fromBookingRoute }}" id="intake-form" class="bsd-form">
                        @csrf

                        <div class="bsd-field">
                            <label for="kilometer">Odometer Saat Masuk Bengkel (KM) <span style="color:#ef233c;">*</span></label>
                            <div class="bsd-km-wrap">
                                <input type="number" id="kilometer" name="kilometer" min="{{ (int)($booking->kendaraan?->kilometer ?? 0) }}" value="{{ old('kilometer', $booking->kendaraan?->kilometer) }}" required placeholder="Contoh: 45000">
                                <span>KM</span>
                            </div>
                            <small class="bsd-hint">Odometer tercatat sebelumnya: <strong>{{ number_format((int)($booking->kendaraan?->kilometer ?? 0)) }} km</strong>.</small>
                        </div>

                        <div class="bsd-field">
                            <span class="bsd-field-label">Checklist Kondisi Awal Unit</span>
                            <div class="bsd-checklist-grid">
                                @foreach([
                                    'Bahan bakar 1/2 tangki atau lebih' => 'Bahan Bakar Aman (≥ 1/2)',
                                    'Ban serep & toolkit lengkap' => 'Ban Serep & Toolkit Lengkap',
                                    'Barang berharga telah dikosongkan dari kabin' => 'Barang Berharga Dikosongkan',
                                    'STNK asli terlampir bersama kunci' => 'STNK Terlampir',
                                    'Kondisi bodi eksterior mulus tanpa baret signifikan' => 'Bodi Eksterior Mulus',
                                    'Terdapat baret/lecet pemakaian pada bodi kendaraan' => 'Ada Baret / Lecet Halus',
                                ] as $val => $label)
                                    <label class="bsd-check-pill">
                                        <input type="checkbox" onchange="toggleChecklistItem(this, @js($val))">
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="bsd-field">
                            <label for="catatan">Catatan Tambahan / Hasil Pemeriksaan Serah Terima</label>
                            <textarea id="catatan" name="catatan" rows="4" placeholder="Catat kondisi fisik unit saat diterima dari customer...">{{ old('catatan', $booking->catatan) }}</textarea>
                        </div>

                        <button type="submit" class="bsd-btn bsd-btn-primary bsd-btn-full">
                            Simpan Check-in &amp; Terbitkan Work Order
                        </button>
                    </form>

                @elseif($booking->servis)
                    <div class="bsd-wo-issued">
                        <div>
                            <span class="bsd-field-label" style="margin-bottom:4px;">Work Order Telah Terbit</span>
                            <div class="bsd-wo-code">{{ $booking->servis->kode_servis }}</div>
                            <span class="bsd-wo-meta">Status: {{ \App\Services\ServiceStatusPresenter::label($booking->servis->status) }}</span>
                        </div>
                        <a href="{{ $servisShowRoute }}" class="bsd-btn bsd-btn-dark">
                            <span>Buka Work Order</span>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>
                            </svg>
                        </a>
                    </div>
                @else
                    <p class="bsd-closed-note">Status reservasi ini sudah ditutup.</p>
                @endif
            </section>
        </div>

        <aside class="bsd-sidebar">
            <section class="bsd-card">
                <div class="bsd-card-head">
                    <h2>Alur Reservasi</h2>
                    <span class="bsd-mono-muted">{{ $booking->kode_booking }}</span>
                </div>

                <div class="bsd-timeline">
                    <div class="bsd-step is-done">
                        <span class="bsd-step-dot" aria-hidden="true"></span>
                        <strong>1. Reservasi Booking Masuk</strong>
                        <small>{{ $booking->created_at?->format('d M Y, H:i') }}</small>
                    </div>
                    <div class="bsd-step {{ in_array($booking->status, ['diterima', 'diproses', 'selesai']) ? 'is-done' : ($booking->status === 'menunggu' ? 'is-active' : '') }}">
                        <span class="bsd-step-dot" aria-hidden="true"></span>
                        <strong>2. Konfirmasi Service Advisor</strong>
                        <small>{{ $booking->status === 'menunggu' ? 'Menunggu konfirmasi slot' : 'Terkonfirmasi siap datang' }}</small>
                    </div>
                    <div class="bsd-step {{ $booking->servis ? 'is-done' : ($booking->status === 'diterima' ? 'is-active' : '') }}">
                        <span class="bsd-step-dot" aria-hidden="true"></span>
                        <strong>3. Intake Check-in &amp; Terbit WO</strong>
                        <small>{{ $booking->servis ? 'WO: '.$booking->servis->kode_servis : 'Serah terima fisik unit di bengkel' }}</small>
                    </div>
                    <div class="bsd-step {{ $booking->servis && in_array($booking->servis->status, ['dikerjakan', 'selesai']) ? 'is-done' : '' }}">
                        <span class="bsd-step-dot" aria-hidden="true"></span>
                        <strong>4. Diagnosis &amp; Pengerjaan Mekanik</strong>
                        <small>Inspeksi &amp; alokasi suku cadang</small>
                    </div>
                    <div class="bsd-step {{ $booking->status === 'selesai' ? 'is-done' : '' }}">
                        <span class="bsd-step-dot" aria-hidden="true"></span>
                        <strong>5. Selesai &amp; Kasir Finance</strong>
                        <small>Quality check &amp; pembayaran</small>
                    </div>
                </div>

                <div class="bsd-advisor-box">
                    <span class="bsd-field-label" style="margin-bottom:8px;">Petugas Bertugas</span>
                    <div class="bsd-advisor-row">
                        <span class="bsd-avatar">{{ strtoupper(substr(auth()->user()->displayName(), 0, 1)) }}</span>
                        <div>
                            <strong>{{ auth()->user()->displayName() }}</strong>
                            <small>Service Advisor On Duty</small>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>

<div id="bsdConfirmModal" class="bsd-modal-backdrop" hidden onclick="if(event.target===this) closeBookingConfirmModal()">
    <div class="bsd-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="bsdConfirmTitle">
        <div class="bsd-modal-header">
            <h3 id="bsdConfirmTitle" class="bsd-modal-title">Konfirmasi</h3>
            <button type="button" class="bsd-modal-close" onclick="closeBookingConfirmModal()" aria-label="Tutup">&times;</button>
        </div>
        <div class="bsd-modal-body">
            <p id="bsdConfirmMessage" class="bsd-modal-message"></p>
        </div>
        <div class="bsd-modal-footer">
            <button type="button" class="bsd-btn bsd-btn-secondary" onclick="closeBookingConfirmModal()">Batal</button>
            <button type="button" id="bsdConfirmSubmitBtn" class="bsd-btn bsd-btn-success" onclick="submitBookingConfirmModal()">Ya, Lanjutkan</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.admin-main-content:has(.bsd-page),
.admin-content:has(.bsd-page),
main:has(.bsd-page) {
    padding-top: 10px !important;
}

.bsd-page {
    --bsd-ink: #0f172a;
    --bsd-muted: #64748b;
    --bsd-subtle: #94a3b8;
    --bsd-line: #e2e8f0;
    --bsd-line-soft: #f1f5f9;
    --bsd-surface: #ffffff;
    --bsd-bg-soft: #f8fafc;
    --bsd-accent: #ef233c;

    max-width: 1480px;
    margin: 0 auto;
    padding: 0 clamp(14px, 2.5vw, 28px) 48px;
    color: var(--bsd-ink);
}

/* 1. EDITORIAL HEADER */
.bsd-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 18px;
    flex-wrap: wrap;
    padding-bottom: 16px;
    margin-bottom: 20px;
    border-bottom: 1px solid var(--bsd-line);
}

.bsd-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--bsd-muted);
    margin-bottom: 6px;
}

.bsd-eyebrow-dot {
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: var(--bsd-accent);
}

.bsd-title-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 6px;
}

.bsd-title {
    margin: 0;
    font-size: clamp(1.5rem, 2.4vw, 1.95rem);
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.1;
}

.bsd-subtitle {
    margin: 0;
    font-size: 13.5px;
    color: var(--bsd-muted);
}

.bsd-subtitle i {
    font-style: normal;
    margin: 0 4px;
    color: var(--bsd-subtle);
}

/* 2. BUTTONS, STATUS, ALERTS */
.bsd-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    height: 38px;
    padding: 0 15px;
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

.bsd-btn-primary { background: var(--bsd-accent); color: #ffffff; height: 42px; font-weight: 700; }
.bsd-btn-primary:hover { background: #d81b32; }
.bsd-btn-dark { background: var(--bsd-ink); color: #ffffff; }
.bsd-btn-dark:hover { background: #1e293b; }
.bsd-btn-success { background: #059669; color: #ffffff; }
.bsd-btn-success:hover { background: #047857; }
.bsd-btn-ghost { background: var(--bsd-surface); border-color: var(--bsd-line); color: var(--bsd-ink); }
.bsd-btn-ghost:hover { background: var(--bsd-bg-soft); border-color: #cbd5e1; }
.bsd-btn-danger-outline { background: #ffffff; border-color: #fecaca; color: #dc2626; }
.bsd-btn-danger-outline:hover { background: #fef2f2; }
.bsd-btn-full { width: 100%; }

.bsd-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 11px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 700;
}

.bsd-status-dot { width: 6px; height: 6px; border-radius: 999px; }
.bsd-status.is-amber { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.bsd-status.is-amber .bsd-status-dot { background: #f59e0b; }
.bsd-status.is-blue { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.bsd-status.is-blue .bsd-status-dot { background: #3b82f6; }
.bsd-status.is-violet { background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }
.bsd-status.is-violet .bsd-status-dot { background: #8b5cf6; }
.bsd-status.is-emerald { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.bsd-status.is-emerald .bsd-status-dot { background: #10b981; }
.bsd-status.is-crimson { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.bsd-status.is-crimson .bsd-status-dot { background: #ef4444; }
.bsd-status.is-slate { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.bsd-status.is-slate .bsd-status-dot { background: #94a3b8; }

.bsd-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    margin-bottom: 18px;
    border-radius: 12px;
    font-size: 13.5px;
    font-weight: 600;
}

.bsd-alert.is-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
.bsd-alert.is-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

/* 3. LAYOUT & CARDS */
.bsd-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.65fr) minmax(320px, 1fr);
    gap: 20px;
    align-items: flex-start;
}

.bsd-main {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.bsd-sidebar {
    position: sticky;
    top: 20px;
}

.bsd-card {
    background: var(--bsd-surface);
    border: 1px solid var(--bsd-line);
    border-radius: 16px;
    padding: 20px 22px;
}

.bsd-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding-bottom: 14px;
    margin-bottom: 18px;
    border-bottom: 1px solid var(--bsd-line-soft);
}

.bsd-card-head h2 {
    margin: 0;
    font-size: 15.5px;
    font-weight: 800;
    letter-spacing: -0.02em;
}

.bsd-card-hint {
    font-size: 12px;
    color: var(--bsd-muted);
    font-weight: 600;
}

.bsd-mono-muted {
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 11.5px;
    color: var(--bsd-muted);
    font-weight: 700;
}

.bsd-plate {
    display: inline-block;
    padding: 3px 9px;
    border-radius: 6px;
    background: var(--bsd-ink);
    color: #ffffff;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.04em;
}

/* 4. SPEC GRID & COMPLAINT */
.bsd-spec-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.bsd-spec-item {
    padding: 13px 15px;
    border-radius: 12px;
    background: var(--bsd-bg-soft);
    border: 1px solid var(--bsd-line-soft);
}

.bsd-spec-item small {
    display: block;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--bsd-muted);
    margin-bottom: 4px;
}

.bsd-spec-item strong {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: var(--bsd-ink);
}

.bsd-spec-item span {
    display: block;
    margin-top: 2px;
    font-size: 12px;
    color: var(--bsd-muted);
}

.bsd-pref-note {
    margin-top: 14px;
    padding: 10px 14px;
    border-radius: 10px;
    background: var(--bsd-bg-soft);
    border: 1px solid var(--bsd-line);
    font-size: 12.5px;
    color: var(--bsd-muted);
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

.bsd-pref-note strong {
    color: var(--bsd-ink);
}

.bsd-complaint-wrap {
    margin-top: 16px;
}

.bsd-field-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--bsd-muted);
    margin-bottom: 7px;
}

.bsd-complaint-box {
    padding: 12px 14px;
    border-radius: 10px;
    background: var(--bsd-bg-soft);
    border-left: 3px solid var(--bsd-accent);
    font-size: 13.5px;
    line-height: 1.55;
    color: #334155;
}

/* 5. INTAKE FORM */
.bsd-notice.is-amber {
    padding: 14px 16px;
    border-radius: 12px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    margin-bottom: 16px;
}

.bsd-notice.is-amber strong {
    display: block;
    font-size: 13.5px;
    color: #92400e;
    margin-bottom: 3px;
}

.bsd-notice.is-amber p {
    margin: 0;
    font-size: 12.5px;
    color: #b45309;
}

.bsd-action-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.bsd-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.bsd-field label {
    display: block;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--bsd-ink);
    margin-bottom: 7px;
}

.bsd-km-wrap {
    display: flex;
    align-items: center;
    border: 1px solid var(--bsd-line);
    border-radius: 10px;
    background: var(--bsd-bg-soft);
    overflow: hidden;
}

.bsd-km-wrap:focus-within {
    background: #ffffff;
    border-color: var(--bsd-ink);
}

.bsd-km-wrap input {
    flex: 1;
    height: 44px;
    padding: 0 14px;
    border: 0;
    background: transparent;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 16px;
    font-weight: 700;
    color: var(--bsd-ink);
    outline: none;
}

.bsd-km-wrap span {
    padding: 0 14px;
    font-size: 12px;
    font-weight: 700;
    color: var(--bsd-muted);
}

.bsd-hint {
    display: block;
    margin-top: 5px;
    font-size: 11.5px;
    color: var(--bsd-muted);
}

.bsd-checklist-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
}

.bsd-check-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 11px;
    border: 1px solid var(--bsd-line);
    border-radius: 9px;
    background: var(--bsd-bg-soft);
    font-size: 12px;
    font-weight: 600;
    color: var(--bsd-ink);
    cursor: pointer;
    margin: 0 !important;
    transition: border-color 0.15s ease, background 0.15s ease;
}

.bsd-check-pill:has(input:checked) {
    background: #fff1f2;
    border-color: rgba(239, 35, 60, 0.35);
    color: #9f1239;
}

.bsd-check-pill input {
    accent-color: var(--bsd-accent);
    margin: 0;
}

.bsd-field textarea {
    width: 100%;
    padding: 11px 13px;
    border: 1px solid var(--bsd-line);
    border-radius: 10px;
    background: var(--bsd-bg-soft);
    color: var(--bsd-ink);
    font: inherit;
    font-size: 13px;
    resize: vertical;
    outline: none;
}

.bsd-field textarea:focus {
    background: #ffffff;
    border-color: var(--bsd-ink);
}

.bsd-wo-issued {
    padding: 16px 18px;
    border-radius: 12px;
    background: var(--bsd-bg-soft);
    border: 1px solid var(--bsd-line);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
}

.bsd-wo-code {
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 17px;
    font-weight: 800;
    color: var(--bsd-ink);
}

.bsd-wo-meta {
    display: block;
    margin-top: 2px;
    font-size: 12px;
    color: var(--bsd-muted);
}

.bsd-closed-note {
    margin: 0;
    font-size: 13px;
    color: var(--bsd-muted);
}

/* 6. TIMELINE */
.bsd-timeline {
    position: relative;
    padding-left: 22px;
}

.bsd-timeline::before {
    content: "";
    position: absolute;
    left: 6px;
    top: 6px;
    bottom: 6px;
    width: 2px;
    background: var(--bsd-line);
}

.bsd-step {
    position: relative;
    margin-bottom: 16px;
}

.bsd-step:last-child {
    margin-bottom: 0;
}

.bsd-step-dot {
    position: absolute;
    left: -22px;
    top: 3px;
    width: 14px;
    height: 14px;
    border-radius: 999px;
    background: #ffffff;
    border: 3px solid #cbd5e1;
}

.bsd-step.is-done .bsd-step-dot {
    background: #10b981;
    border-color: #10b981;
}

.bsd-step.is-active .bsd-step-dot {
    border-color: var(--bsd-accent);
    box-shadow: 0 0 0 3px rgba(239, 35, 60, 0.18);
}

.bsd-step strong {
    display: block;
    font-size: 13px;
    color: var(--bsd-ink);
}

.bsd-step small {
    display: block;
    margin-top: 1px;
    font-size: 11.5px;
    color: var(--bsd-muted);
}

.bsd-advisor-box {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid var(--bsd-line-soft);
}

.bsd-advisor-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.bsd-avatar {
    width: 34px;
    height: 34px;
    border-radius: 999px;
    background: var(--bsd-ink);
    color: #ffffff;
    display: grid;
    place-items: center;
    font-size: 13px;
    font-weight: 800;
}

.bsd-advisor-row strong {
    display: block;
    font-size: 13px;
    color: var(--bsd-ink);
}

.bsd-advisor-row small {
    display: block;
    font-size: 11.5px;
    color: var(--bsd-muted);
}

@media (max-width: 1024px) {
    .bsd-grid {
        grid-template-columns: 1fr;
    }

    .bsd-sidebar {
        position: static;
    }
}

@media (max-width: 640px) {
    .bsd-spec-grid,
    .bsd-checklist-grid {
        grid-template-columns: 1fr;
    }
}
.bsd-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1050;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.bsd-modal-backdrop[hidden] { display: none !important; }
.bsd-modal-dialog {
    width: 100%;
    max-width: 440px;
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.2);
    overflow: hidden;
}
.bsd-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
}
.bsd-modal-title {
    margin: 0;
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
}
.bsd-modal-close {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #64748b;
    font-size: 18px;
    line-height: 1;
    cursor: pointer;
}
.bsd-modal-close:hover { background: #f8fafc; color: #0f172a; }
.bsd-modal-body {
    padding: 18px 20px;
}
.bsd-modal-message {
    margin: 0;
    font-size: 13.5px;
    line-height: 1.55;
    color: #475569;
}
.bsd-modal-footer {
    padding: 14px 20px;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
}
.bsd-btn-secondary {
    background: #ffffff;
    color: #334155;
    border: 1px solid #cbd5e1;
}
.bsd-btn-secondary:hover { background: #f1f5f9; }
.bsd-btn-danger-solid {
    background: #dc2626;
    color: #ffffff;
    border: 1px solid #dc2626;
}
.bsd-btn-danger-solid:hover { background: #b91c1c; }
</style>
@endpush

@push('scripts')
<script>
let pendingBookingFormId = null;

function openBookingConfirmModal(formId, title, message, confirmLabel, tone) {
    pendingBookingFormId = formId;
    const modal = document.getElementById('bsdConfirmModal');
    const titleEl = document.getElementById('bsdConfirmTitle');
    const msgEl = document.getElementById('bsdConfirmMessage');
    const submitBtn = document.getElementById('bsdConfirmSubmitBtn');
    if (!modal) return;
    titleEl.textContent = title;
    msgEl.textContent = message;
    submitBtn.textContent = confirmLabel;
    submitBtn.className = 'bsd-btn ' + (tone === 'is-danger' ? 'bsd-btn-danger-solid' : 'bsd-btn-success');
    modal.removeAttribute('hidden');
}

function closeBookingConfirmModal() {
    pendingBookingFormId = null;
    document.getElementById('bsdConfirmModal')?.setAttribute('hidden', '');
}

function submitBookingConfirmModal() {
    if (!pendingBookingFormId) return;
    const form = document.getElementById(pendingBookingFormId);
    if (form) form.submit();
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeBookingConfirmModal();
});

function toggleChecklistItem(checkbox, text) {
    const textarea = document.getElementById('catatan');
    if (!textarea) return;

    let lines = textarea.value.split('\n').map(l => l.trim()).filter(Boolean);
    const item = '- ' + text;

    if (checkbox.checked) {
        if (!lines.includes(item)) lines.push(item);
    } else {
        lines = lines.filter(l => l !== item);
    }

    textarea.value = lines.join('\n');
}
</script>
@endpush
