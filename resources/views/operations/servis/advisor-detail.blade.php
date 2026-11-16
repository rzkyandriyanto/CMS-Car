@extends('layouts.app')

@section('title', 'Detail Work Order '.$servis->kode_servis.' · Service Advisor · YZA Automotive')

@section('content')
@php
    $isAdminPrefix = request()->is('admin/*');
    $indexRoute = route($isAdminPrefix ? 'admin.servis.index' : 'petugas.servis.index');
    $assignRoute = route($isAdminPrefix ? 'admin.servis.assignment.update' : 'petugas.servis.assignment.update', $servis);

    $hasPendingApproval = $servis->approvalRequests?->where('status', 'pending')->isNotEmpty() ?? false;
    $badge = $hasPendingApproval
        ? ['cls' => 'is-crimson', 'lbl' => 'Menunggu Konfirmasi']
        : match($servis->status) {
            'pemeriksaan' => ['cls' => 'is-amber', 'lbl' => 'Pemeriksaan'],
            'dikerjakan' => ['cls' => 'is-blue', 'lbl' => 'Sedang Dikerjakan'],
            'selesai' => ['cls' => 'is-emerald', 'lbl' => 'Selesai'],
            'dibatalkan' => ['cls' => 'is-crimson', 'lbl' => 'Dibatalkan'],
            default => ['cls' => 'is-slate', 'lbl' => ucfirst($servis->status)],
        };

    $laborTotal = (float) ($servis->details?->sum('biaya') ?? 0);
    $partTotal = (float) ($servis->sparepartItems?->sum('subtotal') ?? 0);
    $grandTotal = (float) ($servis->total_biaya > 0 ? $servis->total_biaya : ($laborTotal + $partTotal));
    $assistants = $servis->activeAssistants ?? collect();
    $assistantId = $assistants->first()?->id_petugas;
@endphp

<div class="wod-page">
    <header class="wod-header">
        <div class="wod-header-left">
            <div class="wod-thumb">
                <img src="{{ $vehicleImage }}" alt="{{ $servis->kendaraan?->merk }} {{ $servis->kendaraan?->model }}">
            </div>
            <div>
                <div class="wod-eyebrow">
                    <span class="wod-eyebrow-dot" aria-hidden="true"></span>
                    <span>Detail Work Order · {{ $servis->kode_servis }}</span>
                </div>
                <div class="wod-title-row">
                    <h1 class="wod-title">{{ trim(($servis->kendaraan?->merk ?? '').' '.($servis->kendaraan?->model ?? '')) ?: 'Unit Kendaraan' }}</h1>
                    <span class="wod-status {{ $badge['cls'] }}">
                        <i class="wod-status-dot" aria-hidden="true"></i>
                        {{ $badge['lbl'] }}
                    </span>
                </div>
                <p class="wod-subtitle">
                    <span>{{ $servis->kendaraan?->nomor_polisi ?: 'Plat Belum Diisi' }}</span>
                    <i aria-hidden="true">·</i>
                    <span>{{ number_format((int) ($servis->kilometer ?? 0)) }} km</span>
                    <i aria-hidden="true">·</i>
                    <span>Masuk {{ $servis->tanggal_masuk?->translatedFormat('d M Y, H:i') ?? $servis->created_at?->format('d/m/Y H:i') }} WIB</span>
                </p>
            </div>
        </div>

        <div class="wod-header-actions">
            <a href="{{ $indexRoute }}" class="wod-btn wod-btn-ghost">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </header>

    @if(session('success'))
        <div class="wod-alert is-success" role="status">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="wod-alert is-error" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="wod-grid">
        <div class="wod-main">
            <!-- Informasi Pelanggan & Serah Terima -->
            <section class="wod-card">
                <div class="wod-card-head">
                    <h2>Informasi Pelanggan &amp; Serah Terima</h2>
                    <span class="wod-plate">{{ $servis->kendaraan?->nomor_polisi ?: 'PLAT —' }}</span>
                </div>

                <div class="wod-spec-grid">
                    <div class="wod-spec-item">
                        <small>Pelanggan</small>
                        <strong>{{ $servis->pelanggan?->nama_lengkap ?: 'Customer' }}</strong>
                        <span>{{ $servis->pelanggan?->no_telepon ?: '—' }}</span>
                    </div>
                    <div class="wod-spec-item">
                        <small>Layanan Booking</small>
                        <strong>{{ $servis->bookingServis?->jenisServis?->nama_servis ?: 'Servis Bengkel' }}</strong>
                        <span>{{ $servis->bookingServis?->kode_booking ?: 'Direct Work Order' }}</span>
                    </div>
                </div>

                <div class="wod-note-grid">
                    <div class="wod-note-box">
                        <span class="wod-label">Keluhan Pelanggan</span>
                        <p>{{ $servis->keluhan ?: ($servis->bookingServis?->keluhan ?: 'Tidak ada catatan keluhan.') }}</p>
                    </div>
                    <div class="wod-note-box">
                        <span class="wod-label">Catatan Check-in / Serah Terima</span>
                        <p>{{ $servis->catatan ?: 'Tidak ada catatan serah terima.' }}</p>
                    </div>
                </div>
            </section>

            <!-- Hasil Pemeriksaan & Persetujuan Pelanggan -->
            <section class="wod-card">
                <div class="wod-card-head">
                    <h2>Pemeriksaan &amp; Konfirmasi Pelanggan</h2>
                </div>

                @if($servis->hasil_pemeriksaan)
                    <div class="wod-diagnosis">
                        <span class="wod-label">Hasil Diagnosis</span>
                        <p>{{ $servis->hasil_pemeriksaan }}</p>
                    </div>
                @endif

                @if($servis->findings && $servis->findings->isNotEmpty())
                    <div style="margin-top: 14px;">
                        <span class="wod-label">Temuan Pemeriksaan</span>
                        <ul class="wod-findings">
                            @foreach($servis->findings as $f)
                                <li>
                                    <span class="wod-dot" aria-hidden="true"></span>
                                    <span>{{ $f->judul ?? $f->deskripsi ?? 'Temuan' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($servis->approvalRequests && $servis->approvalRequests->isNotEmpty())
                    <div style="margin-top: 16px;">
                        <span class="wod-label">Riwayat Persetujuan Pelanggan</span>
                        <div class="wod-approval-list">
                            @foreach($servis->approvalRequests as $ap)
                                <div class="wod-approval-item">
                                    <div>
                                        <strong>{{ $ap->title }}</strong>
                                        <small>{{ $ap->reason }}</small>
                                    </div>
                                    <span class="wod-status {{ $ap->status === 'approved' ? 'is-emerald' : ($ap->status === 'rejected' ? 'is-crimson' : 'is-amber') }}">
                                        {{ $ap->status === 'approved' ? 'Disetujui' : ($ap->status === 'rejected' ? 'Ditolak' : 'Menunggu Member') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif(!$servis->hasil_pemeriksaan && (!$servis->findings || $servis->findings->isEmpty()))
                    <p class="wod-muted-empty">Belum ada hasil pemeriksaan teknis atau permintaan konfirmasi pelanggan.</p>
                @endif
            </section>

            <!-- Rincian Jasa & Suku Cadang -->
            <section class="wod-card">
                <div class="wod-card-head">
                    <h2>Rincian Jasa &amp; Suku Cadang</h2>
                    <span class="wod-total-pill">Total: Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                </div>

                @if(($servis->details && $servis->details->isNotEmpty()) || ($servis->sparepartItems && $servis->sparepartItems->isNotEmpty()))
                    <div class="wod-table-wrap">
                        <table class="wod-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Kategori</th>
                                    <th>Qty</th>
                                    <th style="text-align:right;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($servis->details ?? [] as $d)
                                    <tr>
                                        <td>
                                            <strong>{{ $d->deskripsi_pekerjaan }}</strong>
                                            @if($d->catatan)
                                                <small class="wod-subtext">{{ $d->catatan }}</small>
                                            @endif
                                        </td>
                                        <td><span class="wod-tag">Jasa</span></td>
                                        <td>1</td>
                                        <td style="text-align:right; font-weight:700;">Rp {{ number_format((float) $d->biaya, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                                @foreach($servis->sparepartItems ?? [] as $sp)
                                    <tr>
                                        <td>
                                            <strong>{{ $sp->sparepart?->nama_sparepart ?: 'Suku Cadang' }}</strong>
                                            @if($sp->sparepart?->kode_sparepart)
                                                <small class="wod-subtext">{{ $sp->sparepart->kode_sparepart }}</small>
                                            @endif
                                        </td>
                                        <td><span class="wod-tag">Sparepart</span></td>
                                        <td>{{ $sp->jumlah }}</td>
                                        <td style="text-align:right; font-weight:700;">Rp {{ number_format((float) $sp->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="wod-muted-empty">Belum ada jasa atau suku cadang yang dicatat pada Work Order ini.</p>
                @endif

                @if($servis->customSparepartRequests && $servis->customSparepartRequests->isNotEmpty())
                    <div style="margin-top: 16px;">
                        <span class="wod-label">Permintaan Sparepart Kustom Mekanik</span>
                        <div class="wod-approval-list">
                            @foreach($servis->customSparepartRequests as $req)
                                <div class="wod-approval-item">
                                    <div>
                                        <strong>{{ $req->nama_sparepart }} @if($req->spesifikasi) <small>({{ $req->spesifikasi }})</small> @endif</strong>
                                        @if($req->catatan_kebutuhan)
                                            <small>{{ $req->catatan_kebutuhan }}</small>
                                        @endif
                                    </div>
                                    <span class="wod-tag">{{ $req->jumlah }} {{ $req->satuan }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        </div>

        <aside class="wod-sidebar">
            <section class="wod-card">
                <div class="wod-card-head">
                    <h2>Ringkasan &amp; Teknisi</h2>
                </div>

                <dl class="wod-dl">
                    <div>
                        <dt>Status</dt>
                        <dd>{{ $badge['lbl'] }}</dd>
                    </div>
                    <div>
                        <dt>Mekanik Utama</dt>
                        <dd>{{ $servis->petugas?->nama_lengkap ?: 'Belum ditentukan' }}</dd>
                    </div>
                    <div>
                        <dt>Preferensi Member</dt>
                        <dd>{{ $servis->bookingServis?->preferredPetugas?->nama_lengkap ?: 'Tidak ada preferensi' }}</dd>
                    </div>
                    <div>
                        <dt>Estimasi Biaya</dt>
                        <dd style="color:#ef233c;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</dd>
                    </div>
                </dl>

                @if(!in_array($servis->status, ['selesai', 'dibatalkan'], true) && !$servis->id_transaksi)
                    <form method="POST" action="{{ $assignRoute }}" class="wod-assign-form">
                        @csrf
                        @method('PUT')
                        <span class="wod-label" style="margin-top: 16px;">Ubah Penugasan Mekanik</span>
                        <select name="id_petugas" required aria-label="Pilih Mekanik Utama">
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id_petugas }}" @selected((int) $servis->id_petugas === (int) $tech->id_petugas)>
                                    {{ $tech->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                        <select name="assistant_id" aria-label="Pilih Teknisi Pendamping">
                            <option value="">Tanpa Teknisi Pendamping</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id_petugas }}" @selected((int) $assistantId === (int) $tech->id_petugas)>
                                    Pendamping: {{ $tech->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="wod-btn wod-btn-primary">Simpan Penugasan</button>
                    </form>
                @endif
            </section>
        </aside>
    </div>
</div>
@endsection

@push('styles')
<style>
.admin-main-content:has(.wod-page),
.admin-content:has(.wod-page),
main:has(.wod-page) {
    padding-top: 10px !important;
}

.wod-page {
    --wod-ink: #0f172a;
    --wod-muted: #64748b;
    --wod-subtle: #94a3b8;
    --wod-line: #e2e8f0;
    --wod-line-soft: #f1f5f9;
    --wod-surface: #ffffff;
    --wod-bg-soft: #f8fafc;
    --wod-accent: #ef233c;

    max-width: 1480px;
    margin: 0 auto;
    padding: 0 clamp(14px, 2.5vw, 28px) 48px;
    color: var(--wod-ink);
}

.wod-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 18px;
    flex-wrap: wrap;
    padding-bottom: 16px;
    margin-bottom: 20px;
    border-bottom: 1px solid var(--wod-line);
}

.wod-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.wod-thumb {
    width: 84px;
    height: 58px;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--wod-line);
    background: var(--wod-bg-soft);
    flex: 0 0 auto;
}

.wod-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.wod-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--wod-muted);
    margin-bottom: 5px;
}

.wod-eyebrow-dot {
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: var(--wod-accent);
}

.wod-title-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 4px;
}

.wod-title {
    margin: 0;
    font-size: clamp(1.4rem, 2.3vw, 1.85rem);
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.1;
}

.wod-subtitle {
    margin: 0;
    font-size: 13px;
    color: var(--wod-muted);
}

.wod-subtitle i {
    font-style: normal;
    margin: 0 4px;
    color: var(--wod-subtle);
}

.wod-btn {
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
    white-space: nowrap;
}

.wod-btn-primary { background: var(--wod-ink); color: #ffffff; width: 100%; }
.wod-btn-primary:hover { background: var(--wod-accent); }
.wod-btn-ghost { background: var(--wod-surface); border-color: var(--wod-line); color: var(--wod-ink); }
.wod-btn-ghost:hover { background: var(--wod-bg-soft); border-color: #cbd5e1; }

.wod-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    margin-bottom: 18px;
    border-radius: 12px;
    font-size: 13.5px;
    font-weight: 600;
}

.wod-alert.is-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
.wod-alert.is-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

.wod-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 700;
}

.wod-status-dot { width: 6px; height: 6px; border-radius: 999px; }
.wod-status.is-amber { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.wod-status.is-amber .wod-status-dot { background: #f59e0b; }
.wod-status.is-blue { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.wod-status.is-blue .wod-status-dot { background: #3b82f6; }
.wod-status.is-emerald { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.wod-status.is-emerald .wod-status-dot { background: #10b981; }
.wod-status.is-crimson { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.wod-status.is-crimson .wod-status-dot { background: #ef4444; }
.wod-status.is-slate { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

.wod-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) minmax(300px, 1fr);
    gap: 20px;
    align-items: flex-start;
}

.wod-main {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.wod-sidebar {
    position: sticky;
    top: 20px;
}

.wod-card {
    background: var(--wod-surface);
    border: 1px solid var(--wod-line);
    border-radius: 16px;
    padding: 20px 22px;
}

.wod-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding-bottom: 12px;
    margin-bottom: 16px;
    border-bottom: 1px solid var(--wod-line-soft);
}

.wod-card-head h2 {
    margin: 0;
    font-size: 15.5px;
    font-weight: 800;
    letter-spacing: -0.02em;
}

.wod-plate {
    padding: 3px 9px;
    border-radius: 6px;
    background: var(--wod-ink);
    color: #ffffff;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 11.5px;
    font-weight: 700;
}

.wod-total-pill {
    font-size: 13px;
    font-weight: 800;
    color: var(--wod-accent);
}

.wod-spec-grid,
.wod-note-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.wod-note-grid {
    margin-top: 14px;
}

.wod-spec-item,
.wod-note-box,
.wod-diagnosis {
    padding: 12px 14px;
    border-radius: 12px;
    background: var(--wod-bg-soft);
    border: 1px solid var(--wod-line-soft);
}

.wod-spec-item small,
.wod-label {
    display: block;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--wod-muted);
    margin-bottom: 5px;
}

.wod-spec-item strong {
    display: block;
    font-size: 14px;
    color: var(--wod-ink);
}

.wod-spec-item span {
    display: block;
    margin-top: 2px;
    font-size: 12px;
    color: var(--wod-muted);
}

.wod-note-box p,
.wod-diagnosis p {
    margin: 0;
    font-size: 13px;
    line-height: 1.5;
    color: #334155;
}

.wod-findings {
    margin: 0;
    padding: 0;
    list-style: none;
    display: grid;
    gap: 6px;
}

.wod-findings li {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
}

.wod-dot {
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: var(--wod-accent);
}

.wod-approval-list {
    display: grid;
    gap: 8px;
}

.wod-approval-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 10px;
    background: var(--wod-bg-soft);
    border: 1px solid var(--wod-line-soft);
}

.wod-approval-item strong {
    display: block;
    font-size: 13px;
}

.wod-approval-item small {
    display: block;
    font-size: 12px;
    color: var(--wod-muted);
}

.wod-table-wrap {
    overflow-x: auto;
}

.wod-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}

.wod-table th {
    padding: 10px 12px;
    background: var(--wod-bg-soft);
    border-bottom: 1px solid var(--wod-line);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--wod-muted);
}

.wod-table td {
    padding: 11px 12px;
    border-bottom: 1px solid var(--wod-line-soft);
}

.wod-subtext {
    display: block;
    font-size: 11.5px;
    color: var(--wod-muted);
}

.wod-tag {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    background: var(--wod-line-soft);
    font-size: 11px;
    font-weight: 700;
    color: var(--wod-ink);
}

.wod-muted-empty {
    margin: 0;
    font-size: 13px;
    color: var(--wod-muted);
}

.wod-dl {
    margin: 0;
    display: grid;
    gap: 10px;
}

.wod-dl div {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    font-size: 13px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--wod-line-soft);
}

.wod-dl dt { color: var(--wod-muted); }
.wod-dl dd { margin: 0; font-weight: 700; text-align: right; }

.wod-assign-form {
    display: grid;
    gap: 8px;
}

.wod-assign-form select {
    width: 100%;
    height: 38px;
    padding: 0 10px;
    border: 1px solid var(--wod-line);
    border-radius: 9px;
    background: var(--wod-bg-soft);
    font: inherit;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--wod-ink);
}

@media (max-width: 1024px) {
    .wod-grid { grid-template-columns: 1fr; }
    .wod-sidebar { position: static; }
}

@media (max-width: 640px) {
    .wod-spec-grid,
    .wod-note-grid { grid-template-columns: 1fr; }
}
</style>
@endpush
