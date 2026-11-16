@extends('layouts.app')

@section('title', 'Daftar Work Order Servis · Mekanik & Bengkel · YZA Automotive')

@section('content')
@php
    $isAdminPrefix = request()->is('admin/*');
    $isServiceAdvisor = auth()->user()?->roleKey() === 'service_advisor';
    $indexRoute = route($isAdminPrefix ? 'admin.servis.index' : 'petugas.servis.index');
    $stageSvc = app(\App\Services\WorkOrderStageService::class);
    $activeStatus = (string) request('status', '');
    $searchQuery = trim((string) request('q', ''));
    $hasFilter = $activeStatus !== '' || $searchQuery !== '';

    $totalWo = (int) ($stats['total_wo'] ?? $rows->total());
    $inspectionCount = (int) ($stats['pemeriksaan'] ?? 0);
    $confirmCount = (int) ($stats['menunggu_konfirmasi'] ?? 0);
    $workingCount = (int) ($stats['dikerjakan'] ?? 0);
    $doneCount = (int) ($stats['selesai'] ?? 0);
@endphp

<div class="mwo-page">
    <header class="mwo-header">
        <div class="mwo-header-copy">
            <div class="mwo-eyebrow">
                <span class="mwo-eyebrow-dot" aria-hidden="true"></span>
                <span>Workshop &amp; Mechanic · Work Order</span>
            </div>
            <h1 class="mwo-title">Pekerjaan Servis</h1>
            <p class="mwo-subtitle">Kelola antrean diagnosis, pengerjaan unit di bay, suku cadang, dan finalisasi servis.</p>
        </div>
        <div class="mwo-header-actions">
            <a href="{{ route('staff.dashboard') }}" class="mwo-btn mwo-btn-ghost">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </header>

    @if(session('success'))
        <div class="mwo-alert is-success" role="status">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <section class="mwo-kpis" aria-label="Ringkasan work order">
        <a href="{{ $indexRoute }}" class="mwo-kpi {{ $activeStatus === '' ? 'is-active' : '' }}">
            <div class="mwo-kpi-body">
                <span class="mwo-kpi-label">Total Work Order</span>
                <strong class="mwo-kpi-value">{{ number_format($totalWo) }}</strong>
            </div>
            <span class="mwo-kpi-icon is-neutral" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>
                </svg>
            </span>
        </a>

        <a href="{{ $indexRoute }}?status=pemeriksaan" class="mwo-kpi {{ $activeStatus === 'pemeriksaan' ? 'is-active' : '' }}">
            <div class="mwo-kpi-body">
                <span class="mwo-kpi-label">Diagnosis &amp; Inspeksi</span>
                <strong class="mwo-kpi-value is-amber">{{ number_format($inspectionCount) }}</strong>
            </div>
            <span class="mwo-kpi-icon is-amber" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
            </span>
        </a>

        <a href="{{ $indexRoute }}?status=dikerjakan" class="mwo-kpi {{ $activeStatus === 'dikerjakan' ? 'is-active' : '' }}">
            <div class="mwo-kpi-body">
                <span class="mwo-kpi-label">Sedang Dikerjakan</span>
                <strong class="mwo-kpi-value is-blue">{{ number_format($workingCount) }}</strong>
            </div>
            <span class="mwo-kpi-icon is-blue" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
            </span>
        </a>

        <a href="{{ $indexRoute }}?status=selesai" class="mwo-kpi {{ $activeStatus === 'selesai' ? 'is-active' : '' }}">
            <div class="mwo-kpi-body">
                <span class="mwo-kpi-label">Selesai Servis</span>
                <strong class="mwo-kpi-value is-emerald">{{ number_format($doneCount) }}</strong>
            </div>
            <span class="mwo-kpi-icon is-emerald" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6 9 17l-5-5"/>
                </svg>
            </span>
        </a>
    </section>

    <section class="mwo-panel">
        <div class="mwo-toolbar">
            <nav class="mwo-tabs" aria-label="Filter status work order">
                <a href="{{ $indexRoute }}{{ $searchQuery !== '' ? '?q='.urlencode($searchQuery) : '' }}" class="mwo-tab {{ $activeStatus === '' ? 'is-active' : '' }}">
                    Semua <small>{{ $totalWo }}</small>
                </a>
                <a href="{{ $indexRoute }}?status=pemeriksaan{{ $searchQuery !== '' ? '&q='.urlencode($searchQuery) : '' }}" class="mwo-tab {{ $activeStatus === 'pemeriksaan' ? 'is-active' : '' }}">
                    Pemeriksaan <small>{{ $inspectionCount }}</small>
                </a>
                <a href="{{ $indexRoute }}?status=menunggu_konfirmasi{{ $searchQuery !== '' ? '&q='.urlencode($searchQuery) : '' }}" class="mwo-tab {{ $activeStatus === 'menunggu_konfirmasi' ? 'is-active' : '' }}">
                    Menunggu Konfirmasi <small>{{ $confirmCount }}</small>
                </a>
                <a href="{{ $indexRoute }}?status=dikerjakan{{ $searchQuery !== '' ? '&q='.urlencode($searchQuery) : '' }}" class="mwo-tab {{ $activeStatus === 'dikerjakan' ? 'is-active' : '' }}">
                    Dikerjakan <small>{{ $workingCount }}</small>
                </a>
                <a href="{{ $indexRoute }}?status=selesai{{ $searchQuery !== '' ? '&q='.urlencode($searchQuery) : '' }}" class="mwo-tab {{ $activeStatus === 'selesai' ? 'is-active' : '' }}">
                    Selesai <small>{{ $doneCount }}</small>
                </a>
            </nav>

            <form method="get" action="{{ $indexRoute }}" class="mwo-search-form">
                @if($activeStatus !== '')
                    <input type="hidden" name="status" value="{{ $activeStatus }}">
                @endif
                <div class="mwo-search-box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ $searchQuery }}"
                        placeholder="Cari kode WO, nopol, unit, atau pelanggan..."
                        aria-label="Cari work order"
                    >
                </div>
                <button type="submit" class="mwo-btn mwo-btn-primary">Cari</button>
                @if($hasFilter)
                    <a href="{{ $indexRoute }}" class="mwo-btn mwo-btn-ghost">Reset</a>
                @endif
            </form>
        </div>

        <div class="mwo-table-wrap">
            <table class="mwo-table">
                <thead>
                    <tr>
                        <th style="width:46px;">No</th>
                        <th>Kode Work Order</th>
                        <th>Pelanggan</th>
                        <th>Kendaraan Unit</th>
                        <th>Odometer</th>
                        <th>Masuk Bengkel</th>
                        <th>Status</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $s)
                        @php
                            $hasPendingApproval = $s->approvalRequests?->where('status', 'pending')->isNotEmpty() ?? false;
                            $badge = $hasPendingApproval
                                ? ['cls' => 'is-confirm', 'lbl' => 'Menunggu Konfirmasi']
                                : match($s->status) {
                                    'pemeriksaan' => ['cls' => 'is-amber', 'lbl' => 'Pemeriksaan'],
                                    'dikerjakan' => ['cls' => 'is-blue', 'lbl' => 'Sedang Dikerjakan'],
                                    'selesai' => ['cls' => 'is-emerald', 'lbl' => 'Selesai'],
                                    'dibatalkan' => ['cls' => 'is-slate', 'lbl' => 'Dibatalkan'],
                                    default => ['cls' => 'is-slate', 'lbl' => ucfirst($s->status)],
                                };
                            $showUrl = route($isAdminPrefix ? 'admin.servis.show' : 'petugas.servis.show', $s);
                        @endphp
                        <tr>
                            <td class="mwo-cell-num">{{ $loop->iteration + ($rows->currentPage() - 1) * $rows->perPage() }}</td>
                            <td>
                                <a href="{{ $showUrl }}" class="mwo-wo-code">{{ $s->kode_servis }}</a>
                                <span class="mwo-subtext">{{ $s->petugas?->nama_lengkap ?: 'Tim Bengkel' }}</span>
                            </td>
                            <td>
                                <span class="mwo-primary-text">{{ $s->pelanggan?->nama_lengkap ?: 'Customer' }}</span>
                                <span class="mwo-subtext">{{ $s->pelanggan?->no_telepon ?: '—' }}</span>
                            </td>
                            <td>
                                <span class="mwo-plate">{{ $s->kendaraan?->nomor_polisi ?: 'Plat —' }}</span>
                                <span class="mwo-subtext">{{ trim(($s->kendaraan?->merk ?? '').' '.($s->kendaraan?->model ?? '')) ?: 'Unit Kendaraan' }}</span>
                            </td>
                            <td>
                                <span class="mwo-mono">{{ number_format((int)($s->kilometer ?? 0)) }} km</span>
                            </td>
                            <td>
                                <span class="mwo-primary-text">{{ $s->tanggal_masuk?->translatedFormat('d M Y') ?? $s->created_at?->format('d/m/Y') }}</span>
                                <span class="mwo-subtext">{{ $s->tanggal_masuk?->format('H:i') ?? $s->created_at?->format('H:i') }} WIB</span>
                            </td>
                            <td>
                                <span class="mwo-status {{ $badge['cls'] }}">
                                    <i class="mwo-status-dot" aria-hidden="true"></i>
                                    {{ $badge['lbl'] }}
                                </span>
                            </td>
                            <td style="text-align:right;">
                                @if($isServiceAdvisor)
                                    @php
                                        $curStage = $stageSvc->currentStage($s);
                                        $laborSum = (float) ($s->details?->sum('biaya') ?? 0);
                                        $partSum = (float) ($s->sparepartItems?->sum('subtotal') ?? 0);
                                        $estTotal = (float) ($s->total_biaya > 0 ? $s->total_biaya : ($laborSum + $partSum));
                                    @endphp
                                    <button
                                        type="button"
                                        class="mwo-action-link"
                                        data-mwo-detail
                                        data-kode="{{ $s->kode_servis }}"
                                        data-unit="{{ trim(($s->kendaraan?->merk ?? '').' '.($s->kendaraan?->model ?? '')) ?: 'Unit Kendaraan' }}"
                                        data-plate="{{ $s->kendaraan?->nomor_polisi ?: 'Plat —' }}"
                                        data-km="{{ number_format((int)($s->kilometer ?? 0)) }} km"
                                        data-km-raw="{{ (int)($s->kilometer ?? 0) }}"
                                        data-customer="{{ $s->pelanggan?->nama_lengkap ?: 'Customer' }}"
                                        data-mechanic="{{ $s->petugas?->nama_lengkap ?: 'Belum ditentukan' }}"
                                        data-status="{{ $badge['lbl'] }}"
                                        data-status-cls="{{ $badge['cls'] }}"
                                        data-stage="{{ $curStage }}"
                                        data-keluhan="{{ $s->keluhan ?: ($s->bookingServis?->keluhan ?: '') }}"
                                        data-total="Rp {{ number_format($estTotal, 0, ',', '.') }}"
                                        data-url="{{ $showUrl }}"
                                        data-update-url="{{ route($isAdminPrefix ? 'admin.servis.intake.update' : 'petugas.servis.intake.update', $s) }}"
                                    >
                                        <span>Detail</span>
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>
                                        </svg>
                                    </button>
                                @else
                                    <a href="{{ $showUrl }}" class="mwo-action-link">
                                        <span>Buka WO</span>
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>
                                        </svg>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="mwo-empty-cell">
                                <div class="mwo-empty">
                                    <span class="mwo-empty-icon" aria-hidden="true">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                                        </svg>
                                    </span>
                                    <h3>{{ $hasFilter ? 'Work order tidak ditemukan' : 'Belum ada work order' }}</h3>
                                    <p>{{ $hasFilter ? 'Tidak ada data yang cocok dengan filter atau pencarian Anda.' : 'Belum ada kendaraan yang ditugaskan ke daftar pengerjaan servis.' }}</p>
                                    @if($hasFilter)
                                        <a href="{{ $indexRoute }}" class="mwo-btn mwo-btn-ghost">Reset Filter</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rows->hasPages())
            <div class="mwo-pagination">
                {{ $rows->links() }}
            </div>
        @endif
    </section>
</div>

@if($isServiceAdvisor)
<!-- Work Order Detail Modal (Service Advisor) -->
<div class="mwo-modal-backdrop" id="mwo-detail-modal" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="mwo-modal-title">
    <div class="mwo-modal-dialog" role="document">
        <div class="mwo-modal-head">
            <div>
                <span class="mwo-modal-eyebrow" id="mwo-modal-kode">WORK ORDER</span>
                <div class="mwo-modal-title-row">
                    <h2 class="mwo-modal-title" id="mwo-modal-title">Unit Kendaraan</h2>
                    <span class="mwo-status is-blue" id="mwo-modal-status">
                        <i class="mwo-status-dot" aria-hidden="true"></i>
                        <span>Status</span>
                    </span>
                </div>
                <p class="mwo-modal-sub" id="mwo-modal-sub">—</p>
            </div>
            <button type="button" class="mwo-modal-close" data-mwo-modal-close aria-label="Tutup modal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                </svg>
            </button>
        </div>

        <div class="mwo-modal-body">
            <!-- UI Alur Pemrosesan -->
            <div class="mwo-stepper-card">
                <span class="mwo-modal-label">Alur Pemrosesan Work Order</span>
                <div class="mwo-stepper" id="mwo-modal-stepper">
                    @foreach([
                        'pemeriksaan' => ['01', 'Pemeriksaan'],
                        'persetujuan' => ['02', 'Persetujuan'],
                        'pengerjaan' => ['03', 'Pengerjaan'],
                        'quality-control' => ['04', 'Mutu / QC'],
                        'finalisasi' => ['05', 'Finalisasi'],
                    ] as $key => [$num, $lbl])
                        <div class="mwo-step" data-step-key="{{ $key }}">
                            <span class="mwo-step-dot">{{ $num }}</span>
                            <span class="mwo-step-lbl">{{ $lbl }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div id="mwo-modal-view-section">
                <div class="mwo-modal-specs">
                    <div class="mwo-modal-spec">
                        <small>Pelanggan</small>
                        <strong id="mwo-modal-customer">—</strong>
                    </div>
                    <div class="mwo-modal-spec">
                        <small>Mekanik Pelaksana</small>
                        <strong id="mwo-modal-mechanic">—</strong>
                    </div>
                    <div class="mwo-modal-spec">
                        <small>Estimasi Biaya</small>
                        <strong id="mwo-modal-total" style="color:#ef233c;">Rp 0</strong>
                    </div>
                </div>

                <div class="mwo-modal-complaint" style="margin-top: 14px;">
                    <span class="mwo-modal-label">Keluhan Pelanggan</span>
                    <p id="mwo-modal-keluhan">—</p>
                </div>
            </div>

            <!-- Form Edit KM & Keluhan -->
            <form method="POST" action="" id="mwo-modal-edit-form" class="mwo-modal-edit-form" hidden>
                @csrf
                @method('PUT')
                <div class="mwo-modal-field">
                    <label for="mwo-edit-km">Odometer / Kilometer Masuk (KM) <span style="color:#ef233c;">*</span></label>
                    <div class="mwo-km-wrap">
                        <input type="number" id="mwo-edit-km" name="kilometer" min="0" required placeholder="0">
                        <span>KM</span>
                    </div>
                </div>
                <div class="mwo-modal-field">
                    <label for="mwo-edit-keluhan">Box Keluhan Pelanggan</label>
                    <textarea id="mwo-edit-keluhan" name="keluhan" rows="3" maxlength="1000" placeholder="Tuliskan keluhan atau catatan masalah kendaraan..."></textarea>
                </div>
                <div class="mwo-edit-actions">
                    <button type="button" class="mwo-btn mwo-btn-ghost" id="mwo-modal-cancel-edit">Batal</button>
                    <button type="submit" class="mwo-btn mwo-btn-primary" style="width:auto;">Simpan Perubahan</button>
                </div>
            </form>
        </div>

        <div class="mwo-modal-foot" id="mwo-modal-foot">
            <button type="button" class="mwo-btn mwo-btn-ghost" data-mwo-modal-close>Tutup</button>
            <div class="mwo-modal-foot-right">
                <button type="button" class="mwo-btn mwo-btn-ghost" id="mwo-modal-edit-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                    </svg>
                    <span>Edit</span>
                </button>
                <a href="#" class="mwo-btn mwo-btn-primary" id="mwo-modal-more">
                    <span>Lihat Selengkapnya</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.admin-main-content:has(.mwo-page),
.admin-content:has(.mwo-page),
main:has(.mwo-page) {
    padding-top: 10px !important;
}

.mwo-page {
    --mwo-ink: #0f172a;
    --mwo-muted: #64748b;
    --mwo-subtle: #94a3b8;
    --mwo-line: #e2e8f0;
    --mwo-line-soft: #f1f5f9;
    --mwo-surface: #ffffff;
    --mwo-bg-soft: #f8fafc;
    --mwo-accent: #ef233c;

    max-width: 1480px;
    margin: 0 auto;
    padding: 0 clamp(14px, 2.5vw, 28px) 48px;
    color: var(--mwo-ink);
}

/* 1. EDITORIAL HEADER */
.mwo-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 18px;
    flex-wrap: wrap;
    padding-bottom: 16px;
    margin-bottom: 18px;
    border-bottom: 1px solid var(--mwo-line);
}

.mwo-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--mwo-muted);
    margin-bottom: 6px;
}

.mwo-eyebrow-dot {
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: var(--mwo-accent);
}

.mwo-title {
    margin: 0 0 6px;
    font-size: clamp(1.55rem, 2.5vw, 2rem);
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.1;
    color: var(--mwo-ink);
}

.mwo-subtitle {
    margin: 0;
    font-size: 13.5px;
    color: var(--mwo-muted);
    line-height: 1.5;
}

/* 2. BUTTONS & ALERT */
.mwo-btn {
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

.mwo-btn-primary {
    background: var(--mwo-ink);
    color: #ffffff;
}

.mwo-btn-primary:hover {
    background: var(--mwo-accent);
}

.mwo-btn-ghost {
    background: var(--mwo-surface);
    border-color: var(--mwo-line);
    color: var(--mwo-ink);
}

.mwo-btn-ghost:hover {
    background: var(--mwo-bg-soft);
    border-color: #cbd5e1;
}

.mwo-alert.is-success {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    margin-bottom: 18px;
    border-radius: 12px;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
    font-size: 13.5px;
    font-weight: 600;
}

/* 3. KPI STRIP */
.mwo-kpis {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 20px;
}

.mwo-kpi {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 18px;
    background: var(--mwo-surface);
    border: 1px solid var(--mwo-line);
    border-radius: 14px;
    text-decoration: none;
    color: inherit;
    transition: border-color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
}

.mwo-kpi:hover {
    transform: translateY(-2px);
    border-color: #cbd5e1;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
}

.mwo-kpi.is-active {
    border-color: var(--mwo-ink);
    box-shadow: 0 0 0 1px var(--mwo-ink);
}

.mwo-kpi-body {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 0;
}

.mwo-kpi-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--mwo-muted);
}

.mwo-kpi-value {
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1;
    color: var(--mwo-ink);
}

.mwo-kpi-value.is-amber { color: #d97706; }
.mwo-kpi-value.is-blue { color: #2563eb; }
.mwo-kpi-value.is-emerald { color: #059669; }

.mwo-kpi-icon {
    flex: 0 0 40px;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: grid;
    place-items: center;
}

.mwo-kpi-icon.is-neutral { background: #f1f5f9; color: #334155; }
.mwo-kpi-icon.is-amber { background: #fffbeb; color: #d97706; }
.mwo-kpi-icon.is-blue { background: #eff6ff; color: #2563eb; }
.mwo-kpi-icon.is-emerald { background: #ecfdf5; color: #059669; }

/* 4. MAIN WORKSPACE PANEL */
.mwo-panel {
    background: var(--mwo-surface);
    border: 1px solid var(--mwo-line);
    border-radius: 16px;
    overflow: hidden;
}

.mwo-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
    padding: 14px 18px;
    border-bottom: 1px solid var(--mwo-line);
    background: var(--mwo-surface);
}

.mwo-tabs {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px;
    background: var(--mwo-line-soft);
    border-radius: 10px;
    flex-wrap: wrap;
}

.mwo-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 7px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--mwo-muted);
    text-decoration: none;
    transition: background 0.16s ease, color 0.16s ease, box-shadow 0.16s ease;
}

.mwo-tab small {
    font-size: 11px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 999px;
    background: rgba(15, 23, 42, 0.06);
    color: var(--mwo-muted);
}

.mwo-tab:hover {
    color: var(--mwo-ink);
}

.mwo-tab.is-active {
    background: var(--mwo-surface);
    color: var(--mwo-ink);
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
}

.mwo-tab.is-active small {
    background: var(--mwo-ink);
    color: #ffffff;
}

.mwo-search-form {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1 1 300px;
    max-width: 440px;
    justify-content: flex-end;
}

.mwo-search-box {
    position: relative;
    flex: 1 1 auto;
    display: flex;
    align-items: center;
}

.mwo-search-box svg {
    position: absolute;
    left: 12px;
    color: var(--mwo-subtle);
    pointer-events: none;
}

.mwo-search-box input {
    width: 100%;
    height: 38px;
    padding: 0 12px 0 36px;
    border: 1px solid var(--mwo-line);
    border-radius: 10px;
    background: var(--mwo-bg-soft);
    color: var(--mwo-ink);
    font: inherit;
    font-size: 13px;
    outline: none;
    transition: border-color 0.16s ease, background 0.16s ease;
}

.mwo-search-box input:focus {
    background: var(--mwo-surface);
    border-color: var(--mwo-ink);
}

/* 5. TABLE */
.mwo-table-wrap {
    overflow-x: auto;
}

.mwo-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    font-size: 13px;
    min-width: 880px;
}

.mwo-table th {
    padding: 12px 16px;
    background: var(--mwo-bg-soft);
    border-bottom: 1px solid var(--mwo-line);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--mwo-muted);
    white-space: nowrap;
}

.mwo-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--mwo-line-soft);
    vertical-align: middle;
}

.mwo-table tbody tr:hover {
    background: #fafbfc;
}

.mwo-table tbody tr:last-child td {
    border-bottom: none;
}

.mwo-cell-num {
    color: var(--mwo-subtle);
    font-size: 12px;
    font-weight: 600;
}

.mwo-wo-code {
    display: inline-block;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 13px;
    font-weight: 700;
    color: var(--mwo-ink);
    text-decoration: none;
}

.mwo-wo-code:hover {
    color: var(--mwo-accent);
}

.mwo-primary-text {
    display: block;
    font-weight: 600;
    color: var(--mwo-ink);
}

.mwo-subtext {
    display: block;
    margin-top: 2px;
    font-size: 11.5px;
    color: var(--mwo-muted);
}

.mwo-plate {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    background: var(--mwo-ink);
    color: #ffffff;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.04em;
}

.mwo-mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-weight: 600;
    color: var(--mwo-ink);
}

.mwo-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 700;
    white-space: nowrap;
}

.mwo-status-dot {
    width: 6px;
    height: 6px;
    border-radius: 999px;
}

.mwo-status.is-amber { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.mwo-status.is-amber .mwo-status-dot { background: #f59e0b; }
.mwo-status.is-confirm { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }
.mwo-status.is-confirm .mwo-status-dot { background: #ef233c; box-shadow: 0 0 0 3px rgba(239, 35, 60, 0.2); }
.mwo-status.is-blue { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.mwo-status.is-blue .mwo-status-dot { background: #3b82f6; }
.mwo-status.is-emerald { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.mwo-status.is-emerald .mwo-status-dot { background: #10b981; }
.mwo-status.is-slate { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.mwo-status.is-slate .mwo-status-dot { background: #94a3b8; }

.mwo-action-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 12px;
    border-radius: 9px;
    background: var(--mwo-ink);
    color: #ffffff;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.16s ease, transform 0.14s ease;
}

.mwo-action-link:hover {
    background: var(--mwo-accent);
    transform: translateX(1px);
}

/* 6. EMPTY STATE & PAGINATION */
.mwo-empty-cell {
    padding: 52px 20px !important;
    text-align: center;
}

.mwo-empty {
    max-width: 380px;
    margin: 0 auto;
}

.mwo-empty-icon {
    width: 48px;
    height: 48px;
    margin: 0 auto 14px;
    border-radius: 999px;
    background: var(--mwo-line-soft);
    color: var(--mwo-muted);
    display: grid;
    place-items: center;
}

.mwo-empty h3 {
    margin: 0 0 6px;
    font-size: 15.5px;
    font-weight: 700;
    color: var(--mwo-ink);
}

.mwo-empty p {
    margin: 0 0 16px;
    font-size: 13px;
    color: var(--mwo-muted);
    line-height: 1.5;
}

.mwo-pagination {
    padding: 14px 18px;
    border-top: 1px solid var(--mwo-line-soft);
}

@media (max-width: 1024px) {
    .mwo-kpis {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .mwo-kpis {
        grid-template-columns: 1fr;
    }

    .mwo-search-form {
        max-width: none;
        width: 100%;
    }

    .mwo-modal-specs {
        grid-template-columns: 1fr;
    }

    .mwo-stepper {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .mwo-step::after {
        display: none;
    }
}

/* 7. DETAIL MODAL (SERVICE ADVISOR) */
.mwo-modal-backdrop[hidden] {
    display: none !important;
}

.mwo-modal-backdrop {
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

.mwo-modal-dialog {
    width: 100%;
    max-width: 640px;
    max-height: calc(100dvh - 32px);
    overflow-y: auto;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25);
    color: #0f172a;
}

.mwo-modal-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    padding: 18px 22px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.mwo-modal-eyebrow {
    display: inline-block;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 11px;
    font-weight: 700;
    color: #ef233c;
    letter-spacing: 0.04em;
    margin-bottom: 4px;
}

.mwo-modal-title-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 3px;
}

.mwo-modal-title {
    margin: 0;
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #0f172a;
}

.mwo-modal-sub {
    margin: 0;
    font-size: 12.5px;
    color: #64748b;
}

.mwo-modal-close {
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

.mwo-modal-close:hover {
    color: #0f172a;
    border-color: #cbd5e1;
}

.mwo-modal-body {
    padding: 20px 22px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.mwo-modal-label {
    display: block;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 10px;
}

.mwo-stepper-card {
    padding: 14px 16px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}

.mwo-stepper {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 6px;
}

.mwo-step {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 6px;
}

.mwo-step:not(:last-child)::after {
    content: "";
    position: absolute;
    top: 14px;
    left: calc(50% + 16px);
    right: calc(-50% + 16px);
    height: 2px;
    background: #e2e8f0;
}

.mwo-step.is-done:not(:last-child)::after {
    background: #10b981;
}

.mwo-step.is-active:not(:last-child)::after {
    background: linear-gradient(90deg, #ef233c, #e2e8f0);
}

.mwo-step-dot {
    position: relative;
    z-index: 1;
    width: 28px;
    height: 28px;
    border-radius: 999px;
    display: grid;
    place-items: center;
    font-size: 11px;
    font-weight: 800;
    background: #ffffff;
    border: 2px solid #cbd5e1;
    color: #64748b;
}

.mwo-step.is-done .mwo-step-dot {
    background: #10b981;
    border-color: #10b981;
    color: #ffffff;
}

.mwo-step.is-active .mwo-step-dot {
    background: #ef233c;
    border-color: #ef233c;
    color: #ffffff;
    box-shadow: 0 0 0 4px rgba(239, 35, 60, 0.16);
}

.mwo-step-lbl {
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    line-height: 1.2;
}

.mwo-step.is-done .mwo-step-lbl,
.mwo-step.is-active .mwo-step-lbl {
    color: #0f172a;
}

.mwo-modal-specs {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
}

.mwo-modal-spec {
    padding: 11px 13px;
    border-radius: 10px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
}

.mwo-modal-spec small {
    display: block;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    margin-bottom: 3px;
}

.mwo-modal-spec strong {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
}

.mwo-modal-complaint {
    padding: 12px 14px;
    border-radius: 10px;
    background: #f8fafc;
    border-left: 3px solid #ef233c;
}

.mwo-modal-complaint .mwo-modal-label {
    margin-bottom: 4px;
}

.mwo-modal-complaint p {
    margin: 0;
    font-size: 13px;
    color: #334155;
    line-height: 1.5;
}

.mwo-modal-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 22px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}

.mwo-modal-foot-right {
    display: flex;
    align-items: center;
    gap: 10px;
}

.mwo-modal-foot .mwo-btn-primary {
    background: #0f172a;
    color: #ffffff;
}

.mwo-modal-foot .mwo-btn-primary:hover {
    background: #ef233c;
}

button.mwo-action-link {
    border: 0;
    font: inherit;
    cursor: pointer;
}

.mwo-modal-edit-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 16px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}

.mwo-modal-edit-form[hidden] {
    display: none !important;
}

.mwo-modal-field label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 6px;
}

.mwo-km-wrap {
    display: flex;
    align-items: center;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    background: #ffffff;
    overflow: hidden;
}

.mwo-km-wrap:focus-within {
    border-color: #0f172a;
}

.mwo-km-wrap input {
    flex: 1;
    height: 40px;
    padding: 0 12px;
    border: 0;
    background: transparent;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    outline: none;
}

.mwo-km-wrap span {
    padding: 0 12px;
    font-size: 11.5px;
    font-weight: 700;
    color: #64748b;
}

.mwo-modal-field textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    background: #ffffff;
    color: #0f172a;
    font: inherit;
    font-size: 13px;
    resize: vertical;
    outline: none;
}

.mwo-modal-field textarea:focus {
    border-color: #0f172a;
}

.mwo-edit-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.mwo-edit-actions .mwo-btn-primary {
    background: #0f172a;
    color: #ffffff;
}

.mwo-edit-actions .mwo-btn-primary:hover {
    background: #ef233c;
}
</style>
@endpush

@if($isServiceAdvisor)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('mwo-detail-modal');
    if (!modal) return;

    const stagesOrder = ['pemeriksaan', 'persetujuan', 'pengerjaan', 'quality-control', 'finalisasi'];
    const kodeEl = document.getElementById('mwo-modal-kode');
    const titleEl = document.getElementById('mwo-modal-title');
    const subEl = document.getElementById('mwo-modal-sub');
    const statusEl = document.getElementById('mwo-modal-status');
    const customerEl = document.getElementById('mwo-modal-customer');
    const mechanicEl = document.getElementById('mwo-modal-mechanic');
    const totalEl = document.getElementById('mwo-modal-total');
    const keluhanEl = document.getElementById('mwo-modal-keluhan');
    const moreBtn = document.getElementById('mwo-modal-more');
    const viewSection = document.getElementById('mwo-modal-view-section');
    const editForm = document.getElementById('mwo-modal-edit-form');
    const editBtn = document.getElementById('mwo-modal-edit-btn');
    const cancelEditBtn = document.getElementById('mwo-modal-cancel-edit');
    const footEl = document.getElementById('mwo-modal-foot');
    const editKmInput = document.getElementById('mwo-edit-km');
    const editKeluhanInput = document.getElementById('mwo-edit-keluhan');
    const stepEls = modal.querySelectorAll('[data-step-key]');

    const setEditMode = editing => {
        if (viewSection) viewSection.hidden = editing;
        if (editForm) editForm.hidden = !editing;
        if (footEl) footEl.style.display = editing ? 'none' : 'flex';
        if (editing && editKmInput) {
            setTimeout(() => editKmInput.focus(), 40);
        }
    };

    const closeModal = () => {
        setEditMode(false);
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    const openModal = trigger => {
        setEditMode(false);
        kodeEl.textContent = trigger.dataset.kode || 'WORK ORDER';
        titleEl.textContent = trigger.dataset.unit || 'Unit Kendaraan';
        subEl.textContent = (trigger.dataset.plate || 'Plat —') + ' · ' + (trigger.dataset.km || '0 km');
        customerEl.textContent = trigger.dataset.customer || '—';
        mechanicEl.textContent = trigger.dataset.mechanic || '—';
        totalEl.textContent = trigger.dataset.total || 'Rp 0';
        const rawKeluhan = trigger.dataset.keluhan || '';
        keluhanEl.textContent = rawKeluhan || 'Tidak ada keluhan khusus.';
        moreBtn.href = trigger.dataset.url || '#';

        if (editForm) editForm.action = trigger.dataset.updateUrl || '#';
        if (editKmInput) editKmInput.value = trigger.dataset.kmRaw || '0';
        if (editKeluhanInput) editKeluhanInput.value = rawKeluhan;

        statusEl.className = 'mwo-status ' + (trigger.dataset.statusCls || 'is-slate');
        statusEl.querySelector('span').textContent = trigger.dataset.status || '—';

        const activeIdx = Math.max(0, stagesOrder.indexOf(trigger.dataset.stage || 'pemeriksaan'));
        stepEls.forEach(step => {
            const idx = stagesOrder.indexOf(step.dataset.stepKey);
            step.classList.toggle('is-done', idx < activeIdx);
            step.classList.toggle('is-active', idx === activeIdx);
        });

        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    if (editBtn) editBtn.addEventListener('click', () => setEditMode(true));
    if (cancelEditBtn) cancelEditBtn.addEventListener('click', () => setEditMode(false));

    document.querySelectorAll('[data-mwo-detail]').forEach(btn => {
        btn.addEventListener('click', () => openModal(btn));
    });
    modal.querySelectorAll('[data-mwo-modal-close]').forEach(btn => {
        btn.addEventListener('click', closeModal);
    });
    modal.addEventListener('click', e => {
        if (e.target === modal) closeModal();
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !modal.hidden) closeModal();
    });
});
</script>
@endpush
@endif
