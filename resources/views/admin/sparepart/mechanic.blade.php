@extends('layouts.app')

@section('title', 'Riwayat Servis Kendaraan Member · Mekanik · YZA Automotive')

@section('content')
@php
    $indexRoute = route('admin.sparepart.index');
    $activeStatus = $status ?? '';
    $searchQuery = $search ?? '';
    $hasFilter = $activeStatus !== '' || $searchQuery !== '';
@endphp

<div class="mrh-page">
    <header class="mrh-header">
        <div>
            <div class="mrh-eyebrow">
                <span class="mrh-eyebrow-dot" aria-hidden="true"></span>
                <span>Mechanic · Riwayat Servis</span>
            </div>
            <h1 class="mrh-title">Riwayat Kendaraan Member</h1>
            <p class="mrh-subtitle">Daftar mobil milik member yang sedang diproses maupun telah selesai ditangani.</p>
        </div>
        <a href="{{ route('admin.servis.index') }}" class="mrh-btn mrh-btn-ghost">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
            <span>Pekerjaan Servis</span>
        </a>
    </header>

    <section class="mrh-kpis" aria-label="Ringkasan riwayat servis">
        <a href="{{ $indexRoute }}" class="mrh-kpi {{ $activeStatus === '' ? 'is-active' : '' }}">
            <div>
                <span class="mrh-kpi-label">Total Mobil Member</span>
                <strong class="mrh-kpi-value">{{ number_format($stats['total'] ?? 0) }}</strong>
            </div>
            <span class="mrh-kpi-icon is-neutral" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/>
                </svg>
            </span>
        </a>

        <a href="{{ $indexRoute }}?status=proses" class="mrh-kpi {{ $activeStatus === 'proses' ? 'is-active' : '' }}">
            <div>
                <span class="mrh-kpi-label">Dalam Proses</span>
                <strong class="mrh-kpi-value is-blue">{{ number_format($stats['proses'] ?? 0) }}</strong>
            </div>
            <span class="mrh-kpi-icon is-blue" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </span>
        </a>

        <a href="{{ $indexRoute }}?status=selesai" class="mrh-kpi {{ $activeStatus === 'selesai' ? 'is-active' : '' }}">
            <div>
                <span class="mrh-kpi-label">Selesai Servis</span>
                <strong class="mrh-kpi-value is-emerald">{{ number_format($stats['selesai'] ?? 0) }}</strong>
            </div>
            <span class="mrh-kpi-icon is-emerald" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6 9 17l-5-5"/>
                </svg>
            </span>
        </a>
    </section>

    <section class="mrh-panel">
        <div class="mrh-toolbar">
            <nav class="mrh-tabs" aria-label="Filter status riwayat">
                <a href="{{ $indexRoute }}{{ $searchQuery !== '' ? '?q='.urlencode($searchQuery) : '' }}" class="mrh-tab {{ $activeStatus === '' ? 'is-active' : '' }}">
                    Semua <small>{{ $stats['total'] ?? 0 }}</small>
                </a>
                <a href="{{ $indexRoute }}?status=proses{{ $searchQuery !== '' ? '&q='.urlencode($searchQuery) : '' }}" class="mrh-tab {{ $activeStatus === 'proses' ? 'is-active' : '' }}">
                    Proses <small>{{ $stats['proses'] ?? 0 }}</small>
                </a>
                <a href="{{ $indexRoute }}?status=selesai{{ $searchQuery !== '' ? '&q='.urlencode($searchQuery) : '' }}" class="mrh-tab {{ $activeStatus === 'selesai' ? 'is-active' : '' }}">
                    Selesai <small>{{ $stats['selesai'] ?? 0 }}</small>
                </a>
            </nav>

            <form method="get" action="{{ $indexRoute }}" class="mrh-search-form">
                @if($activeStatus !== '')
                    <input type="hidden" name="status" value="{{ $activeStatus }}">
                @endif
                <div class="mrh-search-box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ $searchQuery }}"
                        placeholder="Cari nama member, nopol, model mobil, atau kode WO..."
                        aria-label="Cari riwayat kendaraan"
                    >
                </div>
                <button type="submit" class="mrh-btn mrh-btn-primary">Cari</button>
                @if($hasFilter)
                    <a href="{{ $indexRoute }}" class="mrh-btn mrh-btn-ghost">Reset</a>
                @endif
            </form>
        </div>

        <div class="mrh-table-wrap">
            <table class="mrh-table">
                <thead>
                    <tr>
                        <th style="width:46px;">No</th>
                        <th>Kode Work Order</th>
                        <th>Pemilik (Member)</th>
                        <th>Mobil Member</th>
                        <th>Odometer</th>
                        <th>Layanan &amp; Keluhan</th>
                        <th>Status</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($workOrders as $wo)
                        @php
                            $qcState = $wo->qualityControl?->status;
                            $badge = match(true) {
                                $wo->status === 'selesai' => ['cls' => 'is-emerald', 'lbl' => 'Selesai'],
                                $qcState === 'passed' => ['cls' => 'is-emerald', 'lbl' => 'Selesai · Lulus QC'],
                                $qcState === 'in_progress' => ['cls' => 'is-emerald', 'lbl' => 'Selesai · Tahap QC'],
                                $qcState === 'failed' => ['cls' => 'is-amber', 'lbl' => 'Proses · Perbaikan QC'],
                                $wo->status === 'pemeriksaan' => ['cls' => 'is-amber', 'lbl' => 'Proses · Pemeriksaan'],
                                $wo->status === 'dikerjakan' => ['cls' => 'is-blue', 'lbl' => 'Proses · Pengerjaan'],
                                $wo->status === 'dibatalkan' => ['cls' => 'is-slate', 'lbl' => 'Dibatalkan'],
                                default => ['cls' => 'is-slate', 'lbl' => ucfirst($wo->status)],
                            };
                            $serviceName = $wo->bookingServis?->jenisServis?->nama_servis ?: 'Servis Bengkel';
                            $keluhan = $wo->keluhan ?: ($wo->bookingServis?->keluhan ?: 'Tidak ada keluhan khusus');
                        @endphp
                        <tr>
                            <td class="mrh-cell-num">{{ $loop->iteration + ($workOrders->currentPage() - 1) * $workOrders->perPage() }}</td>
                            <td>
                                <a href="{{ route('admin.servis.show', $wo) }}" class="mrh-wo-code">{{ $wo->kode_servis }}</a>
                                <span class="mrh-subtext">{{ $wo->tanggal_masuk?->translatedFormat('d M Y, H:i') ?? $wo->created_at?->format('d/m/Y H:i') }}</span>
                            </td>
                            <td>
                                <span class="mrh-primary-text">{{ $wo->pelanggan?->nama_lengkap ?: 'Member' }}</span>
                                <span class="mrh-subtext">{{ $wo->pelanggan?->no_telepon ?: '—' }}</span>
                            </td>
                            <td>
                                <span class="mrh-plate">{{ $wo->kendaraan?->nomor_polisi ?: 'Plat —' }}</span>
                                <span class="mrh-subtext">{{ trim(($wo->kendaraan?->merk ?? '').' '.($wo->kendaraan?->model ?? '')) ?: 'Mobil Member' }}</span>
                            </td>
                            <td>
                                <span class="mrh-mono">{{ number_format((int) ($wo->kilometer ?? 0)) }} km</span>
                            </td>
                            <td>
                                <span class="mrh-primary-text">{{ $serviceName }}</span>
                                <span class="mrh-subtext" title="{{ $keluhan }}">{{ Str::limit($keluhan, 42) }}</span>
                            </td>
                            <td>
                                <span class="mrh-status {{ $badge['cls'] }}">
                                    <i class="mrh-status-dot" aria-hidden="true"></i>
                                    {{ $badge['lbl'] }}
                                </span>
                            </td>
                            @php
                                $qcStatus = match($wo->qualityControl?->status) {
                                    'passed' => 'Lulus QC',
                                    'failed' => 'Perlu Perbaikan QC',
                                    'in_progress' => 'Sedang Diperiksa QC',
                                    default => 'Belum QC',
                                };
                                $detailPayload = [
                                    'kode' => $wo->kode_servis,
                                    'unit' => trim(($wo->kendaraan?->merk ?? '').' '.($wo->kendaraan?->model ?? '')) ?: 'Mobil Member',
                                    'plate' => $wo->kendaraan?->nomor_polisi ?: 'Plat —',
                                    'km' => number_format((int) ($wo->kilometer ?? 0)).' km',
                                    'date' => $wo->tanggal_masuk?->translatedFormat('d M Y, H:i').' WIB' ?? $wo->created_at?->format('d/m/Y H:i'),
                                    'customer' => $wo->pelanggan?->nama_lengkap ?: 'Member',
                                    'phone' => $wo->pelanggan?->no_telepon ?: '—',
                                    'service' => $serviceName,
                                    'status' => $badge['lbl'],
                                    'status_cls' => $badge['cls'],
                                    'qc' => $qcStatus,
                                    'keluhan' => $keluhan,
                                    'diagnosis' => $wo->hasil_pemeriksaan ?: 'Belum ada catatan diagnosis.',
                                    'details' => $wo->details->map(fn ($d) => $d->deskripsi_pekerjaan)->values()->all(),
                                    'parts' => $wo->sparepartItems->map(fn ($sp) => ($sp->sparepart?->nama_sparepart ?: 'Suku Cadang').' ('.$sp->jumlah.' '.($sp->sparepart?->satuan ?: 'pcs').')')->values()->all(),
                                ];
                            @endphp
                            <td style="text-align:right;">
                                <button type="button" class="mrh-action-link" data-mrh-detail='@json($detailPayload)'>
                                    <span>Detail</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="mrh-empty-cell">
                                <div class="mrh-empty">
                                    <span class="mrh-empty-icon" aria-hidden="true">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                        </svg>
                                    </span>
                                    <h3>{{ $hasFilter ? 'Riwayat tidak ditemukan' : 'Belum ada riwayat mobil member' }}</h3>
                                    <p>{{ $hasFilter ? 'Tidak ada data mobil yang sesuai dengan filter atau kata kunci pencarian.' : 'Mobil member yang sedang dalam proses maupun selesai servis akan tampil di sini.' }}</p>
                                    @if($hasFilter)
                                        <a href="{{ $indexRoute }}" class="mrh-btn mrh-btn-ghost">Reset Filter</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($workOrders->hasPages())
            <div class="mrh-pagination">
                {{ $workOrders->links() }}
            </div>
        @endif
    </section>
</div>

@if(session('qc_handover_success'))
    <div class="mrh-modal-backdrop" id="mrh-success-modal" role="dialog" aria-modal="true" aria-labelledby="mrh-modal-title">
        <div class="mrh-modal-dialog" role="document">
            <div class="mrh-modal-icon" aria-hidden="true">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6 9 17l-5-5"/>
                </svg>
            </div>
            <h2 id="mrh-modal-title">Berhasil Dikirim ke QC</h2>
            <p>{{ session('qc_handover_success') }}</p>
            <button type="button" class="mrh-btn mrh-btn-primary" id="mrh-modal-ok" style="width:100%;">Oke, Mengerti</button>
        </div>
    </div>
@endif

<!-- Modal Detail Riwayat Kendaraan Member -->
<div class="mrh-modal-backdrop" id="mrh-detail-modal" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="mrh-dt-title">
    <div class="mrh-dt-dialog" role="document">
        <div class="mrh-dt-head">
            <div>
                <span class="mrh-dt-eyebrow" id="mrh-dt-kode">WORK ORDER</span>
                <div class="mrh-dt-title-row">
                    <h2 id="mrh-dt-title">Mobil Member</h2>
                    <span class="mrh-status is-blue" id="mrh-dt-status">
                        <i class="mrh-status-dot" aria-hidden="true"></i>
                        <span>Status</span>
                    </span>
                </div>
                <p class="mrh-dt-sub" id="mrh-dt-sub">—</p>
            </div>
            <button type="button" class="mrh-dt-close" data-mrh-dt-close aria-label="Tutup modal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                </svg>
            </button>
        </div>

        <div class="mrh-dt-body">
            <div class="mrh-dt-specs">
                <div class="mrh-dt-spec">
                    <small>Pemilik (Member)</small>
                    <strong id="mrh-dt-customer">—</strong>
                    <span id="mrh-dt-phone">—</span>
                </div>
                <div class="mrh-dt-spec">
                    <small>Layanan &amp; Status QC</small>
                    <strong id="mrh-dt-service">—</strong>
                    <span id="mrh-dt-qc">—</span>
                </div>
            </div>

            <div class="mrh-dt-notes">
                <div class="mrh-dt-note">
                    <small>Keluhan Pelanggan</small>
                    <p id="mrh-dt-keluhan">—</p>
                </div>
                <div class="mrh-dt-note">
                    <small>Diagnosis Mekanik</small>
                    <p id="mrh-dt-diagnosis">—</p>
                </div>
            </div>

            <div class="mrh-dt-lists">
                <div class="mrh-dt-list-box">
                    <small>Pekerjaan / Tindakan</small>
                    <ul id="mrh-dt-details"></ul>
                </div>
                <div class="mrh-dt-list-box">
                    <small>Suku Cadang Terpakai</small>
                    <ul id="mrh-dt-parts"></ul>
                </div>
            </div>
        </div>

        <div class="mrh-dt-foot">
            <button type="button" class="mrh-btn mrh-btn-ghost" data-mrh-dt-close>Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.admin-main-content:has(.mrh-page),
.admin-content:has(.mrh-page),
main:has(.mrh-page) {
    padding-top: 10px !important;
}

.mrh-page {
    --mrh-ink: #0f172a;
    --mrh-muted: #64748b;
    --mrh-subtle: #94a3b8;
    --mrh-line: #e2e8f0;
    --mrh-line-soft: #f1f5f9;
    --mrh-surface: #ffffff;
    --mrh-bg-soft: #f8fafc;
    --mrh-accent: #ef233c;

    max-width: 1480px;
    margin: 0 auto;
    padding: 0 clamp(14px, 2.5vw, 28px) 48px;
    color: var(--mrh-ink);
}

.mrh-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 18px;
    flex-wrap: wrap;
    padding-bottom: 16px;
    margin-bottom: 18px;
    border-bottom: 1px solid var(--mrh-line);
}

.mrh-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--mrh-muted);
    margin-bottom: 6px;
}

.mrh-eyebrow-dot {
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: var(--mrh-accent);
}

.mrh-title {
    margin: 0 0 6px;
    font-size: clamp(1.5rem, 2.4vw, 1.95rem);
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.1;
}

.mrh-subtitle {
    margin: 0;
    font-size: 13.5px;
    color: var(--mrh-muted);
}

.mrh-btn {
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

.mrh-btn-primary { background: var(--mrh-ink); color: #ffffff; }
.mrh-btn-primary:hover { background: var(--mrh-accent); }
.mrh-btn-ghost { background: var(--mrh-surface); border-color: var(--mrh-line); color: var(--mrh-ink); }
.mrh-btn-ghost:hover { background: var(--mrh-bg-soft); border-color: #cbd5e1; }

.mrh-kpis {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 18px;
}

.mrh-kpi {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 15px 18px;
    background: var(--mrh-surface);
    border: 1px solid var(--mrh-line);
    border-radius: 14px;
    text-decoration: none;
    color: inherit;
    transition: border-color 0.16s ease, transform 0.16s ease;
}

.mrh-kpi:hover {
    transform: translateY(-2px);
    border-color: #cbd5e1;
}

.mrh-kpi.is-active {
    border-color: var(--mrh-ink);
    box-shadow: 0 0 0 1px var(--mrh-ink);
}

.mrh-kpi-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--mrh-muted);
    margin-bottom: 5px;
}

.mrh-kpi-value {
    font-size: 25px;
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1;
}

.mrh-kpi-value.is-blue { color: #2563eb; }
.mrh-kpi-value.is-emerald { color: #059669; }

.mrh-kpi-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: grid;
    place-items: center;
}

.mrh-kpi-icon.is-neutral { background: #f1f5f9; color: #334155; }
.mrh-kpi-icon.is-blue { background: #eff6ff; color: #2563eb; }
.mrh-kpi-icon.is-emerald { background: #ecfdf5; color: #059669; }

.mrh-panel {
    background: var(--mrh-surface);
    border: 1px solid var(--mrh-line);
    border-radius: 16px;
    overflow: hidden;
}

.mrh-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
    padding: 14px 18px;
    border-bottom: 1px solid var(--mrh-line);
}

.mrh-tabs {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px;
    background: var(--mrh-line-soft);
    border-radius: 10px;
}

.mrh-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 7px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--mrh-muted);
    text-decoration: none;
}

.mrh-tab small {
    font-size: 11px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 999px;
    background: rgba(15, 23, 42, 0.06);
}

.mrh-tab.is-active {
    background: var(--mrh-surface);
    color: var(--mrh-ink);
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
}

.mrh-tab.is-active small {
    background: var(--mrh-ink);
    color: #ffffff;
}

.mrh-search-form {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1 1 280px;
    max-width: 440px;
    justify-content: flex-end;
}

.mrh-search-box {
    position: relative;
    flex: 1 1 auto;
    display: flex;
    align-items: center;
}

.mrh-search-box svg {
    position: absolute;
    left: 12px;
    color: var(--mrh-subtle);
    pointer-events: none;
}

.mrh-search-box input {
    width: 100%;
    height: 38px;
    padding: 0 12px 0 36px;
    border: 1px solid var(--mrh-line);
    border-radius: 10px;
    background: var(--mrh-bg-soft);
    color: var(--mrh-ink);
    font: inherit;
    font-size: 13px;
    outline: none;
}

.mrh-search-box input:focus {
    background: var(--mrh-surface);
    border-color: var(--mrh-ink);
}

.mrh-table-wrap { overflow-x: auto; }

.mrh-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    font-size: 13px;
}

.mrh-table th {
    padding: 12px 16px;
    background: var(--mrh-bg-soft);
    border-bottom: 1px solid var(--mrh-line);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--mrh-muted);
    white-space: nowrap;
}

.mrh-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--mrh-line-soft);
    vertical-align: middle;
}

.mrh-table tbody tr:hover { background: #fafbfc; }
.mrh-table tbody tr:last-child td { border-bottom: 0; }

.mrh-cell-num { color: var(--mrh-subtle); font-size: 12px; font-weight: 600; }

.mrh-wo-code {
    display: inline-block;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 13px;
    font-weight: 700;
    color: var(--mrh-ink);
    text-decoration: none;
}

.mrh-wo-code:hover { color: var(--mrh-accent); }

.mrh-primary-text { display: block; font-weight: 600; color: var(--mrh-ink); }
.mrh-subtext { display: block; margin-top: 2px; font-size: 11.5px; color: var(--mrh-muted); }

.mrh-plate {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    background: var(--mrh-ink);
    color: #ffffff;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 11px;
    font-weight: 700;
}

.mrh-mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-weight: 600;
}

.mrh-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 700;
    white-space: nowrap;
}

.mrh-status-dot { width: 6px; height: 6px; border-radius: 999px; }
.mrh-status.is-amber { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.mrh-status.is-amber .mrh-status-dot { background: #f59e0b; }
.mrh-status.is-blue { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.mrh-status.is-blue .mrh-status-dot { background: #3b82f6; }
.mrh-status.is-emerald { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.mrh-status.is-emerald .mrh-status-dot { background: #10b981; }
.mrh-status.is-slate { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.mrh-status.is-slate .mrh-status-dot { background: #94a3b8; }

.mrh-action-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 12px;
    border-radius: 9px;
    background: var(--mrh-ink);
    color: #ffffff;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
}

.mrh-action-link:hover { background: var(--mrh-accent); }

.mrh-empty-cell { padding: 52px 20px !important; text-align: center; }
.mrh-empty { max-width: 380px; margin: 0 auto; }
.mrh-empty-icon {
    width: 48px; height: 48px; margin: 0 auto 14px; border-radius: 999px;
    background: var(--mrh-line-soft); color: var(--mrh-muted); display: grid; place-items: center;
}
.mrh-empty h3 { margin: 0 0 6px; font-size: 15.5px; font-weight: 700; }
.mrh-empty p { margin: 0 0 16px; font-size: 13px; color: var(--mrh-muted); }

.mrh-pagination { padding: 14px 18px; border-top: 1px solid var(--mrh-line-soft); }

@media (max-width: 900px) {
    .mrh-kpis { grid-template-columns: 1fr; }
    .mrh-search-form { max-width: none; width: 100%; }
}

.mrh-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 999;
    background: rgba(15, 23, 42, 0.56);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.mrh-modal-dialog {
    width: 100%;
    max-width: 400px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 28px 24px 24px;
    text-align: center;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.24);
}

.mrh-modal-icon {
    width: 54px;
    height: 54px;
    margin: 0 auto 14px;
    border-radius: 999px;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #059669;
    display: grid;
    place-items: center;
}

.mrh-modal-dialog h2 {
    margin: 0 0 8px;
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #0f172a;
}

.mrh-modal-dialog p {
    margin: 0 0 20px;
    font-size: 13.5px;
    color: #64748b;
    line-height: 1.5;
}

button.mrh-action-link {
    border: 0;
    font: inherit;
    cursor: pointer;
}

.mrh-modal-backdrop[hidden] {
    display: none !important;
}

.mrh-dt-dialog {
    width: 100%;
    max-width: 620px;
    max-height: calc(100dvh - 32px);
    overflow-y: auto;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25);
    color: #0f172a;
}

.mrh-dt-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    padding: 18px 22px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.mrh-dt-eyebrow {
    display: inline-block;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 11px;
    font-weight: 700;
    color: #ef233c;
    margin-bottom: 4px;
}

.mrh-dt-title-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 3px;
}

.mrh-dt-title-row h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -0.02em;
}

.mrh-dt-sub {
    margin: 0;
    font-size: 12.5px;
    color: #64748b;
}

.mrh-dt-close {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #64748b;
    display: grid;
    place-items: center;
    cursor: pointer;
    padding: 0;
    flex: 0 0 auto;
}

.mrh-dt-body {
    padding: 20px 22px;
    display: grid;
    gap: 14px;
}

.mrh-dt-specs,
.mrh-dt-notes,
.mrh-dt-lists {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.mrh-dt-spec,
.mrh-dt-note,
.mrh-dt-list-box {
    padding: 12px 14px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
}

.mrh-dt-spec small,
.mrh-dt-note small,
.mrh-dt-list-box small {
    display: block;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 5px;
}

.mrh-dt-spec strong { display: block; font-size: 13.5px; color: #0f172a; }
.mrh-dt-spec span { display: block; margin-top: 2px; font-size: 12px; color: #64748b; }
.mrh-dt-note p { margin: 0; font-size: 13px; color: #334155; line-height: 1.5; }

.mrh-dt-list-box ul {
    margin: 0;
    padding-left: 16px;
    font-size: 12.5px;
    color: #0f172a;
    display: grid;
    gap: 4px;
}

.mrh-dt-foot {
    display: flex;
    justify-content: flex-start;
    padding: 14px 22px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}

@media (max-width: 640px) {
    .mrh-dt-specs,
    .mrh-dt-notes,
    .mrh-dt-lists {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const successModal = document.getElementById('mrh-success-modal');
    const okBtn = document.getElementById('mrh-modal-ok');
    if (successModal) {
        const closeSuccess = () => successModal.remove();
        okBtn?.addEventListener('click', closeSuccess);
        successModal.addEventListener('click', e => { if (e.target === successModal) closeSuccess(); });
        okBtn?.focus();
    }

    const dtModal = document.getElementById('mrh-detail-modal');
    if (!dtModal) return;

    const closeDt = () => {
        dtModal.hidden = true;
        dtModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    const fillList = (id, items, emptyText) => {
        const ul = document.getElementById(id);
        if (!ul) return;
        ul.replaceChildren();
        const list = items && items.length ? items : [emptyText];
        list.forEach(txt => {
            const li = document.createElement('li');
            li.textContent = txt;
            ul.append(li);
        });
    };

    document.querySelectorAll('[data-mrh-detail]').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = JSON.parse(btn.dataset.mrhDetail || '{}');
            document.getElementById('mrh-dt-kode').textContent = d.kode || 'WORK ORDER';
            document.getElementById('mrh-dt-title').textContent = d.unit || 'Mobil Member';
            document.getElementById('mrh-dt-sub').textContent = `${d.plate || 'Plat —'} · ${d.km || '0 km'} · ${d.date || '—'}`;
            document.getElementById('mrh-dt-customer').textContent = d.customer || '—';
            document.getElementById('mrh-dt-phone').textContent = d.phone || '—';
            document.getElementById('mrh-dt-service').textContent = d.service || '—';
            document.getElementById('mrh-dt-qc').textContent = d.qc || '—';
            document.getElementById('mrh-dt-keluhan').textContent = d.keluhan || '—';
            document.getElementById('mrh-dt-diagnosis').textContent = d.diagnosis || '—';

            const st = document.getElementById('mrh-dt-status');
            if (st) {
                st.className = 'mrh-status ' + (d.status_cls || 'is-slate');
                st.querySelector('span').textContent = d.status || '—';
            }

            fillList('mrh-dt-details', d.details, 'Belum ada tindakan dicatat');
            fillList('mrh-dt-parts', d.parts, 'Tidak ada suku cadang terpakai');

            dtModal.hidden = false;
            dtModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        });
    });

    dtModal.querySelectorAll('[data-mrh-dt-close]').forEach(btn => btn.addEventListener('click', closeDt));
    dtModal.addEventListener('click', e => { if (e.target === dtModal) closeDt(); });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !dtModal.hidden) closeDt();
    });
});
</script>
@endpush
