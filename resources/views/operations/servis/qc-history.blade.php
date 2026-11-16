@extends('layouts.app')

@section('title', 'Riwayat Quality Control · YZA Automotive')

@section('content')
@php
    $indexRoute = route('admin.servis.qc-history');
    $hasFilter = ($qcStatus ?? '') !== '' || ($search ?? '') !== '';
@endphp

<div class="qch-page">
    <header class="qch-header">
        <div>
            <div class="qch-eyebrow">
                <span class="qch-eyebrow-dot" aria-hidden="true"></span>
                <span>Quality Control · Riwayat Pemeriksaan</span>
            </div>
            <h1 class="qch-title">Riwayat Quality Control</h1>
            <p class="qch-subtitle">Daftar seluruh kendaraan member yang telah masuk maupun selesai melewati pemeriksaan mutu bengkel.</p>
        </div>
        <a href="{{ route('admin.servis.index') }}" class="qch-btn qch-btn-ghost">
            <span>QC Work Order</span>
        </a>
    </header>

    @if(session('qc_handover_success'))
        <div class="qch-modal-backdrop" id="qch-success-modal" role="dialog" aria-modal="true" aria-labelledby="qch-success-title">
            <div class="qch-modal-dialog" style="max-width: 440px; padding: 28px 24px; text-align: center;">
                <div style="width: 52px; height: 52px; margin: 0 auto 14px; border-radius: 999px; background: #ecfdf5; color: #059669; display: grid; place-items: center; font-size: 24px; font-weight: 800; border: 1px solid #a7f3d0;">✓</div>
                <h2 id="qch-success-title" style="margin: 0 0 8px; font-size: 18px; font-weight: 800; color: #0f172a;">Serah Terima Berhasil Dikirim</h2>
                <p style="margin: 0 0 20px; font-size: 13.5px; color: #475569; line-height: 1.55;">{{ session('qc_handover_success') }}</p>
                <button type="button" class="qch-btn qch-btn-primary" style="width: 100%; justify-content: center;" onclick="document.getElementById('qch-success-modal').remove()">Tutup</button>
            </div>
        </div>
    @endif

    <section class="qch-kpis" aria-label="Ringkasan riwayat QC">
        <a href="{{ $indexRoute }}" class="qch-kpi {{ $qcStatus === '' ? 'is-active' : '' }}">
            <div>
                <span class="qch-kpi-label">Total Unit QC</span>
                <strong class="qch-kpi-value">{{ number_format($stats['total'] ?? 0) }}</strong>
            </div>
        </a>
        <a href="{{ $indexRoute }}?qc_status=in_progress" class="qch-kpi {{ $qcStatus === 'in_progress' ? 'is-active' : '' }}">
            <div>
                <span class="qch-kpi-label">Menunggu / Proses QC</span>
                <strong class="qch-kpi-value is-amber">{{ number_format($stats['in_progress'] ?? 0) }}</strong>
            </div>
        </a>
        <a href="{{ $indexRoute }}?qc_status=passed" class="qch-kpi {{ $qcStatus === 'passed' ? 'is-active' : '' }}">
            <div>
                <span class="qch-kpi-label">Lulus QC</span>
                <strong class="qch-kpi-value is-emerald">{{ number_format($stats['passed'] ?? 0) }}</strong>
            </div>
        </a>
        <a href="{{ $indexRoute }}?qc_status=failed" class="qch-kpi {{ $qcStatus === 'failed' ? 'is-active' : '' }}">
            <div>
                <span class="qch-kpi-label">Perlu Perbaikan</span>
                <strong class="qch-kpi-value is-crimson">{{ number_format($stats['failed'] ?? 0) }}</strong>
            </div>
        </a>
    </section>

    <section class="qch-panel">
        <div class="qch-toolbar">
            <nav class="qch-tabs" aria-label="Filter status QC">
                <a href="{{ $indexRoute }}{{ $search !== '' ? '?q='.urlencode($search) : '' }}" class="qch-tab {{ $qcStatus === '' ? 'is-active' : '' }}">Semua</a>
                <a href="{{ $indexRoute }}?qc_status=in_progress{{ $search !== '' ? '&q='.urlencode($search) : '' }}" class="qch-tab {{ $qcStatus === 'in_progress' ? 'is-active' : '' }}">Proses QC</a>
                <a href="{{ $indexRoute }}?qc_status=passed{{ $search !== '' ? '&q='.urlencode($search) : '' }}" class="qch-tab {{ $qcStatus === 'passed' ? 'is-active' : '' }}">Lulus</a>
                <a href="{{ $indexRoute }}?qc_status=failed{{ $search !== '' ? '&q='.urlencode($search) : '' }}" class="qch-tab {{ $qcStatus === 'failed' ? 'is-active' : '' }}">Perbaikan</a>
            </nav>

            <form method="get" action="{{ $indexRoute }}" class="qch-search-form">
                @if($qcStatus !== '')
                    <input type="hidden" name="qc_status" value="{{ $qcStatus }}">
                @endif
                <div class="qch-search-box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Cari kode WO, member, nopol, atau model..." aria-label="Cari riwayat QC">
                </div>
                <button type="submit" class="qch-btn qch-btn-primary">Cari</button>
                @if($hasFilter)
                    <a href="{{ $indexRoute }}" class="qch-btn qch-btn-ghost">Reset</a>
                @endif
            </form>
        </div>

        <div class="qch-table-wrap">
            <table class="qch-table">
                <thead>
                    <tr>
                        <th style="width:46px;">No</th>
                        <th>Kode Work Order</th>
                        <th>Member</th>
                        <th>Mobil Member</th>
                        <th>Mekanik</th>
                        <th>Status QC</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $wo)
                        @php
                            $qc = $wo->qualityControl;
                            $qcBadge = match($qc?->status) {
                                'passed' => ['cls' => 'is-emerald', 'lbl' => 'Lulus QC'],
                                'failed' => ['cls' => 'is-crimson', 'lbl' => 'Perlu Perbaikan'],
                                'in_progress' => ['cls' => 'is-amber', 'lbl' => 'Proses QC'],
                                default => ['cls' => 'is-slate', 'lbl' => 'Menunggu QC'],
                            };
                            $payload = [
                                'kode' => $wo->kode_servis,
                                'unit' => trim(($wo->kendaraan?->merk ?? '').' '.($wo->kendaraan?->model ?? '')) ?: 'Mobil Member',
                                'plate' => $wo->kendaraan?->nomor_polisi ?: 'Plat —',
                                'customer' => $wo->pelanggan?->nama_lengkap ?: 'Member',
                                'mechanic' => $wo->petugas?->nama_lengkap ?: '—',
                                'service' => $wo->bookingServis?->jenisServis?->nama_servis ?: 'Servis Bengkel',
                                'status' => $qcBadge['lbl'],
                                'status_cls' => $qcBadge['cls'],
                                'notes' => $qc?->notes ?: 'Tidak ada catatan QC.',
                                'items' => ($qc?->items ?? collect())->map(fn ($it) => [
                                    'label' => $it->label_snapshot,
                                    'result' => $it->result ?: 'BELUM DIISI',
                                    'note' => $it->note,
                                ])->values()->all(),
                                'wo_url' => route('admin.servis.quality-control', $wo),
                            ];
                        @endphp
                        <tr>
                            <td class="qch-num">{{ $loop->iteration + ($rows->currentPage() - 1) * $rows->perPage() }}</td>
                            <td>
                                <span class="qch-mono">{{ $wo->kode_servis }}</span>
                                <span class="qch-sub">{{ $wo->tanggal_masuk?->translatedFormat('d M Y, H:i') ?? $wo->created_at?->format('d/m/Y H:i') }}</span>
                            </td>
                            <td>
                                <strong>{{ $wo->pelanggan?->nama_lengkap ?: 'Member' }}</strong>
                                <span class="qch-sub">{{ $wo->bookingServis?->jenisServis?->nama_servis ?: 'Servis Bengkel' }}</span>
                            </td>
                            <td>
                                <span class="qch-plate">{{ $wo->kendaraan?->nomor_polisi ?: 'Plat —' }}</span>
                                <span class="qch-sub">{{ trim(($wo->kendaraan?->merk ?? '').' '.($wo->kendaraan?->model ?? '')) ?: 'Unit' }}</span>
                            </td>
                            <td>{{ $wo->petugas?->nama_lengkap ?: '—' }}</td>
                            <td>
                                <span class="qch-status {{ $qcBadge['cls'] }}">
                                    <i class="qch-dot" aria-hidden="true"></i>
                                    {{ $qcBadge['lbl'] }}
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <button type="button" class="qch-action-btn" data-qch-detail='@json($payload)'>
                                    <span>Detail</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="qch-empty">Belum ada riwayat pemeriksaan Quality Control.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rows->hasPages())
            <div class="qch-pagination">{{ $rows->links() }}</div>
        @endif
    </section>
</div>

<!-- Detail Modal -->
<div class="qch-modal-backdrop" id="qch-detail-modal" hidden aria-hidden="true" role="dialog" aria-modal="true">
    <div class="qch-modal-dialog" role="document">
        <div class="qch-modal-head">
            <div>
                <span class="qch-modal-eyebrow" id="qch-dt-kode">WORK ORDER</span>
                <div class="qch-modal-title-row">
                    <h2 id="qch-dt-title">Mobil Member</h2>
                    <span class="qch-status is-amber" id="qch-dt-status"><i class="qch-dot"></i><span>Status</span></span>
                </div>
                <p class="qch-modal-sub" id="qch-dt-sub">—</p>
            </div>
            <button type="button" class="qch-modal-close" data-qch-close aria-label="Tutup modal">×</button>
        </div>

        <div class="qch-modal-body">
            <div class="qch-dt-specs">
                <div class="qch-dt-spec"><small>Member</small><strong id="qch-dt-customer">—</strong></div>
                <div class="qch-dt-spec"><small>Mekanik</small><strong id="qch-dt-mechanic">—</strong></div>
                <div class="qch-dt-spec"><small>Layanan</small><strong id="qch-dt-service">—</strong></div>
            </div>

            <div class="qch-dt-checklist">
                <small class="qch-dt-label">Hasil Checklist Mutu</small>
                <div id="qch-dt-items" class="qch-dt-items"></div>
            </div>

            <div class="qch-dt-note">
                <small class="qch-dt-label">Catatan Akhir QC</small>
                <p id="qch-dt-notes">—</p>
            </div>
        </div>

        <div class="qch-modal-foot">
            <button type="button" class="qch-btn qch-btn-ghost" data-qch-close>Tutup</button>
            <a href="#" class="qch-btn qch-btn-primary" id="qch-dt-link">Buka Halaman QC →</a>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.admin-main-content:has(.qch-page),
.admin-content:has(.qch-page),
main:has(.qch-page) {
    padding-top: 10px !important;
}

.qch-page {
    --qch-ink: #0f172a;
    --qch-muted: #64748b;
    --qch-line: #e2e8f0;
    --qch-line-soft: #f1f5f9;
    --qch-surface: #ffffff;
    --qch-bg-soft: #f8fafc;
    --qch-accent: #ef233c;

    max-width: 1480px;
    margin: 0 auto;
    padding: 0 clamp(14px, 2.5vw, 28px) 48px;
    color: var(--qch-ink);
}

.qch-header {
    display: flex; align-items: flex-end; justify-content: space-between; gap: 18px; flex-wrap: wrap;
    padding-bottom: 16px; margin-bottom: 18px; border-bottom: 1px solid var(--qch-line);
}
.qch-eyebrow {
    display: inline-flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700;
    letter-spacing: 0.14em; text-transform: uppercase; color: var(--qch-muted); margin-bottom: 6px;
}
.qch-eyebrow-dot { width: 6px; height: 6px; border-radius: 999px; background: var(--qch-accent); }
.qch-title { margin: 0 0 6px; font-size: clamp(1.5rem, 2.4vw, 1.95rem); font-weight: 800; letter-spacing: -0.03em; line-height: 1.1; }
.qch-subtitle { margin: 0; font-size: 13.5px; color: var(--qch-muted); }

.qch-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 7px; height: 38px; padding: 0 14px;
    border-radius: 10px; font: inherit; font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer;
    border: 1px solid transparent; white-space: nowrap;
}
.qch-btn-primary { background: var(--qch-ink); color: #ffffff; }
.qch-btn-primary:hover { background: var(--qch-accent); }
.qch-btn-ghost { background: var(--qch-surface); border-color: var(--qch-line); color: var(--qch-ink); }
.qch-btn-ghost:hover { background: var(--qch-bg-soft); }

.qch-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
.qch-kpi {
    padding: 15px 18px; background: var(--qch-surface); border: 1px solid var(--qch-line);
    border-radius: 14px; text-decoration: none; color: inherit;
}
.qch-kpi.is-active { border-color: var(--qch-ink); box-shadow: 0 0 0 1px var(--qch-ink); }
.qch-kpi-label { display: block; font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--qch-muted); margin-bottom: 5px; }
.qch-kpi-value { font-size: 25px; font-weight: 800; letter-spacing: -0.03em; line-height: 1; }
.qch-kpi-value.is-amber { color: #d97706; }
.qch-kpi-value.is-emerald { color: #059669; }
.qch-kpi-value.is-crimson { color: #ef233c; }

.qch-panel { background: var(--qch-surface); border: 1px solid var(--qch-line); border-radius: 16px; overflow: hidden; }
.qch-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; padding: 14px 18px; border-bottom: 1px solid var(--qch-line); }
.qch-tabs { display: inline-flex; gap: 4px; padding: 4px; background: var(--qch-line-soft); border-radius: 10px; }
.qch-tab { padding: 6px 12px; border-radius: 7px; font-size: 12.5px; font-weight: 600; color: var(--qch-muted); text-decoration: none; }
.qch-tab.is-active { background: var(--qch-surface); color: var(--qch-ink); box-shadow: 0 1px 2px rgba(15,23,42,0.08); }

.qch-search-form { display: flex; align-items: center; gap: 8px; flex: 1 1 280px; max-width: 440px; justify-content: flex-end; }
.qch-search-box { position: relative; flex: 1 1 auto; display: flex; align-items: center; }
.qch-search-box svg { position: absolute; left: 12px; color: #94a3b8; pointer-events: none; }
.qch-search-box input { width: 100%; height: 38px; padding: 0 12px 0 36px; border: 1px solid var(--qch-line); border-radius: 10px; background: var(--qch-bg-soft); font: inherit; font-size: 13px; outline: none; }

.qch-table-wrap { overflow-x: auto; }
.qch-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
.qch-table th { padding: 12px 16px; background: var(--qch-bg-soft); border-bottom: 1px solid var(--qch-line); font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--qch-muted); }
.qch-table td { padding: 14px 16px; border-bottom: 1px solid var(--qch-line-soft); vertical-align: middle; }
.qch-num { color: #94a3b8; font-size: 12px; font-weight: 600; }
.qch-mono { display: block; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-weight: 700; }
.qch-sub { display: block; margin-top: 2px; font-size: 11.5px; color: var(--qch-muted); }
.qch-plate { display: inline-block; padding: 2px 8px; border-radius: 6px; background: var(--qch-ink); color: #fff; font-family: ui-monospace, monospace; font-size: 11px; font-weight: 700; }

.qch-status { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700; white-space: nowrap; }
.qch-dot { width: 6px; height: 6px; border-radius: 999px; }
.qch-status.is-amber { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.qch-status.is-amber .qch-dot { background: #f59e0b; }
.qch-status.is-emerald { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.qch-status.is-emerald .qch-dot { background: #10b981; }
.qch-status.is-crimson { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.qch-status.is-crimson .qch-dot { background: #ef4444; }
.qch-status.is-slate { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

.qch-action-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; border-radius: 9px; border: 0; background: var(--qch-ink); color: #fff; font: inherit; font-size: 12px; font-weight: 600; cursor: pointer; }
.qch-action-btn:hover { background: var(--qch-accent); }
.qch-empty { padding: 44px 20px !important; text-align: center; color: var(--qch-muted); }
.qch-pagination { padding: 14px 18px; border-top: 1px solid var(--qch-line-soft); }

.qch-modal-backdrop[hidden] { display: none !important; }
.qch-modal-backdrop { position: fixed; inset: 0; z-index: 999; background: rgba(15,23,42,0.58); backdrop-filter: blur(5px); display: flex; align-items: center; justify-content: center; padding: 16px; }
.qch-modal-dialog { width: 100%; max-width: 620px; max-height: calc(100dvh - 32px); overflow-y: auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; box-shadow: 0 24px 60px rgba(15,23,42,0.25); }
.qch-modal-head { display: flex; justify-content: space-between; gap: 14px; padding: 18px 22px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
.qch-modal-eyebrow { display: inline-block; font-family: ui-monospace, monospace; font-size: 11px; font-weight: 700; color: #ef233c; margin-bottom: 4px; }
.qch-modal-title-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 3px; }
.qch-modal-title-row h2 { margin: 0; font-size: 18px; font-weight: 800; }
.qch-modal-sub { margin: 0; font-size: 12.5px; color: #64748b; }
.qch-modal-close { width: 34px; height: 34px; border-radius: 9px; border: 1px solid #e2e8f0; background: #fff; color: #64748b; font-size: 20px; cursor: pointer; }
.qch-modal-body { padding: 20px 22px; display: grid; gap: 14px; }
.qch-dt-specs { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
.qch-dt-spec, .qch-dt-note, .qch-dt-checklist { padding: 12px 14px; border-radius: 12px; background: #f8fafc; border: 1px solid #f1f5f9; }
.qch-dt-spec small, .qch-dt-label { display: block; font-size: 10.5px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #64748b; margin-bottom: 5px; }
.qch-dt-spec strong { font-size: 13px; }
.qch-dt-items { display: grid; gap: 6px; }
.qch-dt-item { display: flex; justify-content: space-between; gap: 10px; font-size: 12.5px; padding: 6px 10px; background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; }
.qch-dt-note p { margin: 0; font-size: 13px; color: #334155; }
.qch-modal-foot { display: flex; justify-content: space-between; gap: 10px; padding: 14px 22px; border-top: 1px solid #e2e8f0; background: #f8fafc; }

@media (max-width: 900px) { .qch-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 640px) { .qch-kpis, .qch-dt-specs { grid-template-columns: 1fr; } }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('qch-detail-modal');
    if (!modal) return;
    const close = () => { modal.hidden = true; modal.setAttribute('aria-hidden', 'true'); document.body.style.overflow = ''; };

    document.querySelectorAll('[data-qch-detail]').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = JSON.parse(btn.dataset.qchDetail || '{}');
            document.getElementById('qch-dt-kode').textContent = d.kode || 'WORK ORDER';
            document.getElementById('qch-dt-title').textContent = d.unit || 'Mobil Member';
            document.getElementById('qch-dt-sub').textContent = d.plate || 'Plat —';
            document.getElementById('qch-dt-customer').textContent = d.customer || '—';
            document.getElementById('qch-dt-mechanic').textContent = d.mechanic || '—';
            document.getElementById('qch-dt-service').textContent = d.service || '—';
            document.getElementById('qch-dt-notes').textContent = d.notes || '—';
            document.getElementById('qch-dt-link').href = d.wo_url || '#';

            const st = document.getElementById('qch-dt-status');
            if (st) {
                st.className = 'qch-status ' + (d.status_cls || 'is-slate');
                st.querySelector('span').textContent = d.status || '—';
            }

            const wrap = document.getElementById('qch-dt-items');
            wrap.replaceChildren();
            (d.items || []).forEach(it => {
                const row = document.createElement('div');
                row.className = 'qch-dt-item';
                const left = document.createElement('span');
                left.textContent = it.label + (it.note ? ' · ' + it.note : '');
                const right = document.createElement('strong');
                right.textContent = it.result;
                row.append(left, right);
                wrap.append(row);
            });

            modal.hidden = false;
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        });
    });

    modal.querySelectorAll('[data-qch-close]').forEach(btn => btn.addEventListener('click', close));
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !modal.hidden) close(); });
});
</script>
@endpush
