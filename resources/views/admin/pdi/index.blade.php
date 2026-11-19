@extends('layouts.app')

@section('title', 'PDI Kendaraan Baru · YZA Automotive')

@section('content')
@php
    $indexRoute = route('admin.pdi.index');
    $hasFilter = ($pdiFilter ?? '') !== '' || ($keyword ?? '') !== '';
    $totalChecklistItems = collect($checklistDefinition)->flatten()->count();
@endphp

<div class="pdi-page">
    <header class="pdi-header">
        <div>
            <div class="pdi-eyebrow">
                <span class="pdi-eyebrow-dot" aria-hidden="true"></span>
                <span>Quality Control · Pre-Delivery Inspection</span>
            </div>
            <h1 class="pdi-title">PDI Kendaraan Baru</h1>
            <p class="pdi-subtitle">Pemeriksaan menyeluruh unit mobil baru yang datang dari pabrik sebelum dikirim serah terima ke Finance / Kasir.</p>
        </div>
        <a href="{{ route('admin.servis.qc-history') }}" class="pdi-btn pdi-btn-ghost">
            <span>Riwayat QC Servis</span>
        </a>
    </header>

    @if(session('pdi_handover_success'))
        <div class="pdi-modal-backdrop" id="pdi-success-modal" role="dialog" aria-modal="true" aria-labelledby="pdi-success-title">
            <div class="pdi-modal-dialog" style="max-width: 440px; padding: 28px 24px; text-align: center;">
                <div style="width: 52px; height: 52px; margin: 0 auto 14px; border-radius: 999px; background: #ecfdf5; color: #059669; display: grid; place-items: center; font-size: 24px; font-weight: 800; border: 1px solid #a7f3d0;">✓</div>
                <h2 id="pdi-success-title" style="margin: 0 0 8px; font-size: 18px; font-weight: 800; color: #0f172a;">Serah Terima Berhasil Dikirim</h2>
                <p style="margin: 0 0 20px; font-size: 13.5px; color: #475569; line-height: 1.55;">{{ session('pdi_handover_success') }}</p>
                <button type="button" class="pdi-btn pdi-btn-primary" style="width: 100%; justify-content: center;" onclick="document.getElementById('pdi-success-modal').remove()">Tutup</button>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="pdi-alert is-success">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="pdi-alert is-warning">{{ session('warning') }}</div>
    @endif
    @if($errors->any())
        <div class="pdi-alert is-error">{{ $errors->first() }}</div>
    @endif

    <section class="pdi-kpis" aria-label="Ringkasan PDI Kendaraan Baru">
        <div class="pdi-kpi">
            <span class="pdi-kpi-label">Total Unit Masuk PDI</span>
            <strong class="pdi-kpi-value">{{ number_format($stats['total'] ?? 0) }}</strong>
        </div>
        <div class="pdi-kpi">
            <span class="pdi-kpi-label">Menunggu / Proses PDI</span>
            <strong class="pdi-kpi-value is-amber">{{ number_format($stats['pending'] ?? 0) }}</strong>
        </div>
        <div class="pdi-kpi">
            <span class="pdi-kpi-label">Lulus PDI</span>
            <strong class="pdi-kpi-value is-emerald">{{ number_format($stats['lulus'] ?? 0) }}</strong>
        </div>
        <div class="pdi-kpi">
            <span class="pdi-kpi-label">Perlu Perbaikan</span>
            <strong class="pdi-kpi-value is-crimson">{{ number_format($stats['perlu_perbaikan'] ?? 0) }}</strong>
        </div>
    </section>

    <section class="pdi-panel">
        <div class="pdi-toolbar">
            <div class="pdi-tabs" role="tablist" aria-label="Filter status PDI">
                <a href="{{ $indexRoute }}" class="pdi-tab {{ $pdiFilter === '' ? 'is-active' : '' }}">Semua</a>
                <a href="{{ $indexRoute }}?pdi_status=pending" class="pdi-tab {{ $pdiFilter === 'pending' ? 'is-active' : '' }}">Menunggu / Proses</a>
                <a href="{{ $indexRoute }}?pdi_status=lulus" class="pdi-tab {{ $pdiFilter === 'lulus' ? 'is-active' : '' }}">Lulus PDI</a>
                <a href="{{ $indexRoute }}?pdi_status=perlu_perbaikan" class="pdi-tab {{ $pdiFilter === 'perlu_perbaikan' ? 'is-active' : '' }}">Perlu Perbaikan</a>
            </div>

            <form method="GET" action="{{ $indexRoute }}" class="pdi-search-form">
                @if($pdiFilter !== '')
                    <input type="hidden" name="pdi_status" value="{{ $pdiFilter }}">
                @endif
                <div class="pdi-search-box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="search" name="q" value="{{ $keyword }}" placeholder="Cari kode unit, merk, model, atau no. rangka...">
                </div>
                <button type="submit" class="pdi-btn pdi-btn-primary">Cari</button>
                @if($hasFilter)
                    <a href="{{ $indexRoute }}" class="pdi-btn pdi-btn-ghost">Reset</a>
                @endif
            </form>
        </div>

        <div class="pdi-table-wrap">
            <table class="pdi-table">
                <thead>
                    <tr>
                        <th style="width: 52px;">No</th>
                        <th>Kode Unit / Order</th>
                        <th>Mobil Baru dari Pabrik</th>
                        <th>Identitas Pabrik</th>
                        <th>Pemesan</th>
                        <th>Status PDI</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $i => $order)
                        @php
                            $pdi = $order->pdi;
                            $pdiStatus = $pdi?->status ?? 'belum_mulai';
                            $badgeClass = match($pdiStatus) {
                                'lulus' => 'is-emerald',
                                'perlu_perbaikan' => 'is-crimson',
                                'sedang_diperiksa' => 'is-amber',
                                default => 'is-slate',
                            };
                            $badgeLabel = match($pdiStatus) {
                                'lulus' => 'Lulus PDI · Siap Finance',
                                'perlu_perbaikan' => 'Perlu Perbaikan',
                                'sedang_diperiksa' => 'Sedang Diperiksa',
                                default => 'Belum Mulai PDI',
                            };
                            $carName = trim(($order->snapshot_merk ?? $order->mobil?->merk ?? '') . ' ' . ($order->snapshot_model ?? $order->mobil?->model ?? 'Unit Baru'));
                            $carColor = $order->snapshot_warna ?? $order->mobil?->warna ?? 'Standar Pabrik';
                            $vin = $order->ordered_vehicle_vin ?: 'Belum tercatat';
                            $engineNo = $order->ordered_vehicle_engine_number ?: 'Belum tercatat';
                        @endphp
                        <tr>
                            <td class="pdi-num">{{ ($orders->firstItem() ?? 1) + $i }}</td>
                            <td>
                                <span class="pdi-mono">{{ $order->nomor_pesanan }}</span>
                                <span class="pdi-sub">{{ $order->created_at?->format('d M Y, H:i') }}</span>
                            </td>
                            <td>
                                <div class="pdi-car-cell">
                                    <img src="{{ $order->ordered_vehicle_image }}" alt="{{ $carName }}" class="pdi-car-thumb">
                                    <div>
                                        <strong>{{ $carName }}</strong>
                                        <span class="pdi-sub">Warna: {{ $carColor }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="pdi-sub" style="margin-top: 0; color: #0f172a; font-weight: 600;">VIN: {{ $vin }}</span>
                                <span class="pdi-sub">Mesin: {{ $engineNo }}</span>
                            </td>
                            <td>
                                <strong>{{ $order->nama_pembeli ?: ($order->pelanggan?->nama_lengkap ?? 'Member') }}</strong>
                                <span class="pdi-sub">{{ $order->pelanggan?->no_hp ?? $order->telepon_pembeli ?? '—' }}</span>
                            </td>
                            <td>
                                <span class="pdi-status {{ $badgeClass }}">
                                    <span class="pdi-dot" aria-hidden="true"></span>
                                    {{ $badgeLabel }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                @if(!$pdi)
                                    <form method="POST" action="{{ route('admin.pesanan-mobil.pdi.start', $order) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="pdi-action-btn">Mulai PDI</button>
                                    </form>
                                @elseif($pdiStatus === 'lulus')
                                    <button type="button" class="pdi-btn pdi-btn-ghost" data-pdi-open="pdi-modal-{{ $order->id_pesanan }}">Lihat Hasil PDI</button>
                                @else
                                    <button type="button" class="pdi-action-btn" data-pdi-open="pdi-modal-{{ $order->id_pesanan }}">Periksa PDI →</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="pdi-empty">Belum ada unit mobil baru yang masuk antrean PDI.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="pdi-pagination">{{ $orders->links() }}</div>
        @endif
    </section>
</div>

{{-- MODAL CHECKLIST PDI PER UNIT --}}
@foreach($orders as $order)
    @if($order->pdi)
        @php
            $pdi = $order->pdi;
            $pdiLocked = $pdi->status === 'lulus';
            $carName = trim(($order->snapshot_merk ?? $order->mobil?->merk ?? '') . ' ' . ($order->snapshot_model ?? $order->mobil?->model ?? 'Unit Baru'));
            $carColor = $order->snapshot_warna ?? $order->mobil?->warna ?? 'Standar Pabrik';
            $vin = $order->ordered_vehicle_vin ?: 'Belum tercatat';
            $engineNo = $order->ordered_vehicle_engine_number ?: 'Belum tercatat';
        @endphp
        <div class="pdi-modal-backdrop" id="pdi-modal-{{ $order->id_pesanan }}" data-pdi-modal hidden aria-hidden="true" role="dialog" aria-modal="true">
            <div class="pdi-modal-dialog pdi-modal-xl">
                <div class="pdi-modal-head">
                    <div>
                        <span class="pdi-modal-eyebrow">{{ $order->nomor_pesanan }} · PEMERIKSAAN MOBIL BARU DARI PABRIK</span>
                        <h2>{{ $carName }}</h2>
                        <p class="pdi-modal-sub">Warna: {{ $carColor }} · No. Rangka: {{ $vin }} · No. Mesin: {{ $engineNo }}</p>
                    </div>
                    <button type="button" class="pdi-modal-close" data-pdi-close aria-label="Tutup">&times;</button>
                </div>

                <form method="POST" action="{{ route('admin.pesanan-mobil.pdi', $order) }}" class="pdi-modal-form" data-pdi-form novalidate>
                    @csrf
                    <div class="pdi-modal-body">
                        <div class="pdi-progress-strip">
                            <span class="pdi-progress-label">Progres Pemeriksaan Pabrik</span>
                            <div class="pdi-progress-stats">
                                <strong data-pdi-count>0 / {{ $totalChecklistItems }} Item Diperiksa</strong>
                                <span class="pdi-mini-stat is-pass">Lulus: <strong data-pdi-pass>0</strong></span>
                                <span class="pdi-mini-stat is-fail">Temuan: <strong data-pdi-fail>0</strong></span>
                            </div>
                        </div>

                        @foreach($checklistDefinition as $sectionKey => $items)
                            @php
                                $meta = $sectionMeta[$sectionKey] ?? ['title' => ucwords(str_replace('_', ' ', $sectionKey)), 'desc' => 'Periksa seluruh item pada bagian ini.'];
                            @endphp
                            <div class="pdi-section-card">
                                <div class="pdi-section-head">
                                    <div>
                                        <h3>{{ $meta['title'] }}</h3>
                                        <p>{{ $meta['desc'] }}</p>
                                    </div>
                                    <span class="pdi-section-count">{{ count($items) }} Item</span>
                                </div>

                                <div class="pdi-check-list">
                                    @foreach($items as $itemKey)
                                        @php
                                            $existing = $pdi->checklist[$sectionKey][$itemKey] ?? [];
                                            $val = old("checklist.{$sectionKey}.{$itemKey}.result", $existing['result'] ?? '');
                                            $noteVal = old("checklist.{$sectionKey}.{$itemKey}.notes", $existing['notes'] ?? '');
                                            $isFail = $val === 'issue';
                                            $label = ucwords(str_replace('_', ' ', $itemKey));
                                        @endphp
                                        <div class="pdi-check-item {{ $val === 'pass' ? 'is-pass' : ($isFail ? 'is-fail' : ($val === 'na' ? 'is-na' : '')) }}" data-pdi-item>
                                            <div class="pdi-check-top">
                                                <strong>{{ $label }}</strong>
                                                <div class="pdi-toggle-group" role="radiogroup" aria-label="{{ $label }}">
                                                    <label class="pdi-pill is-pass">
                                                        <input type="radio" name="checklist[{{ $sectionKey }}][{{ $itemKey }}][result]" value="pass" @checked($val === 'pass') @disabled($pdiLocked) required>
                                                        <span>✓ Lulus</span>
                                                    </label>
                                                    <label class="pdi-pill is-fail">
                                                        <input type="radio" name="checklist[{{ $sectionKey }}][{{ $itemKey }}][result]" value="issue" @checked($isFail) @disabled($pdiLocked)>
                                                        <span>✕ Tidak Lulus</span>
                                                    </label>
                                                    <label class="pdi-pill is-na">
                                                        <input type="radio" name="checklist[{{ $sectionKey }}][{{ $itemKey }}][result]" value="na" @checked($val === 'na') @disabled($pdiLocked)>
                                                        <span>— N/A</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="pdi-note-wrap" data-pdi-note-wrap @if(!$isFail) hidden @endif>
                                                <label class="pdi-note-label">
                                                    Catatan Temuan Pabrik <span class="pdi-required-star">* Wajib diisi jika Tidak Lulus</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    class="pdi-note-input"
                                                    name="checklist[{{ $sectionKey }}][{{ $itemKey }}][notes]"
                                                    value="{{ $noteVal }}"
                                                    placeholder="Jelaskan temuan kerusakan/ketidaksesuaian unit baru..."
                                                    @required($isFail)
                                                    @disabled($pdiLocked)
                                                    data-pdi-note
                                                >
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <div class="pdi-final-notes">
                            <label for="pdi-catatan-{{ $order->id_pesanan }}">Catatan Umum PDI (Opsional)</label>
                            <textarea id="pdi-catatan-{{ $order->id_pesanan }}" name="catatan" rows="2" placeholder="Ringkasan kondisi unit mobil baru dari pabrik..." @disabled($pdiLocked)>{{ old('catatan', $pdi->catatan) }}</textarea>
                        </div>

                        <div class="pdi-client-error" data-pdi-client-error hidden></div>
                    </div>

                    <div class="pdi-modal-foot">
                        <button type="button" class="pdi-btn pdi-btn-ghost" data-pdi-close>Tutup</button>
                        @if(!$pdiLocked)
                            <button type="submit" class="pdi-btn pdi-btn-primary">Kirim Serah Terima ke Finance / Kasir →</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    @endif
@endforeach
@endsection

@push('styles')
<style>
.pdi-page {
    --pdi-ink: #0f172a;
    --pdi-muted: #64748b;
    --pdi-line: #e2e8f0;
    --pdi-line-soft: #f1f5f9;
    --pdi-surface: #ffffff;
    --pdi-bg-soft: #f8fafc;
    --pdi-accent: #ef233c;
    max-width: 1280px;
    margin: 0 auto;
    padding: 8px 4px 36px;
    color: var(--pdi-ink);
}

.pdi-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 22px; }
.pdi-eyebrow { display: inline-flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--pdi-muted); margin-bottom: 6px; }
.pdi-eyebrow-dot { width: 7px; height: 7px; border-radius: 999px; background: var(--pdi-accent); }
.pdi-title { margin: 0 0 4px; font-size: clamp(22px, 2.5vw, 28px); font-weight: 800; letter-spacing: -0.02em; }
.pdi-subtitle { margin: 0; font-size: 13.5px; color: var(--pdi-muted); }

.pdi-alert { padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-bottom: 16px; border: 1px solid transparent; }
.pdi-alert.is-success { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
.pdi-alert.is-warning { background: #fffbeb; border-color: #fde68a; color: #92400e; }
.pdi-alert.is-error { background: #fef2f2; border-color: #fecaca; color: #991b1b; }

.pdi-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 7px; height: 38px; padding: 0 14px;
    border-radius: 10px; font: inherit; font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer;
    border: 1px solid transparent; white-space: nowrap;
}
.pdi-btn-primary { background: var(--pdi-ink); color: #ffffff; }
.pdi-btn-primary:hover { background: var(--pdi-accent); }
.pdi-btn-ghost { background: var(--pdi-surface); border-color: var(--pdi-line); color: var(--pdi-ink); }
.pdi-btn-ghost:hover { background: var(--pdi-bg-soft); }

.pdi-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
.pdi-kpi { padding: 15px 18px; background: var(--pdi-surface); border: 1px solid var(--pdi-line); border-radius: 14px; text-decoration: none; color: inherit; }
.pdi-kpi.is-active { border-color: var(--pdi-ink); box-shadow: 0 0 0 1px var(--pdi-ink); }
.pdi-kpi-label { display: block; font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--pdi-muted); margin-bottom: 5px; }
.pdi-kpi-value { font-size: 25px; font-weight: 800; letter-spacing: -0.03em; line-height: 1; }
.pdi-kpi-value.is-amber { color: #d97706; }
.pdi-kpi-value.is-emerald { color: #059669; }
.pdi-kpi-value.is-crimson { color: #ef233c; }

.pdi-panel { background: var(--pdi-surface); border: 1px solid var(--pdi-line); border-radius: 16px; overflow: hidden; }
.pdi-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; padding: 14px 18px; border-bottom: 1px solid var(--pdi-line); }
.pdi-tabs { display: inline-flex; gap: 4px; padding: 4px; background: var(--pdi-line-soft); border-radius: 10px; flex-wrap: wrap; }
.pdi-tab { padding: 6px 12px; border-radius: 7px; font-size: 12.5px; font-weight: 600; color: var(--pdi-muted); text-decoration: none; }
.pdi-tab.is-active { background: var(--pdi-surface); color: var(--pdi-ink); box-shadow: 0 1px 2px rgba(15,23,42,0.08); }

.pdi-search-form { display: flex; align-items: center; gap: 8px; flex: 1 1 280px; max-width: 440px; justify-content: flex-end; }
.pdi-search-box { position: relative; flex: 1 1 auto; display: flex; align-items: center; }
.pdi-search-box svg { position: absolute; left: 12px; color: #94a3b8; pointer-events: none; }
.pdi-search-box input { width: 100%; height: 38px; padding: 0 12px 0 36px; border: 1px solid var(--pdi-line); border-radius: 10px; background: var(--pdi-bg-soft); font: inherit; font-size: 13px; outline: none; }

.pdi-table-wrap { overflow-x: auto; }
.pdi-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
.pdi-table th { padding: 12px 16px; background: var(--pdi-bg-soft); border-bottom: 1px solid var(--pdi-line); font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--pdi-muted); }
.pdi-table td { padding: 14px 16px; border-bottom: 1px solid var(--pdi-line-soft); vertical-align: middle; }
.pdi-num { color: #94a3b8; font-size: 12px; font-weight: 600; }
.pdi-mono { display: block; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-weight: 700; }
.pdi-sub { display: block; margin-top: 2px; font-size: 11.5px; color: var(--pdi-muted); }

.pdi-car-cell { display: flex; align-items: center; gap: 10px; }
.pdi-car-thumb { width: 52px; height: 36px; border-radius: 8px; object-fit: cover; border: 1px solid var(--pdi-line); background: var(--pdi-bg-soft); flex-shrink: 0; }

.pdi-status { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700; white-space: nowrap; }
.pdi-dot { width: 6px; height: 6px; border-radius: 999px; }
.pdi-status.is-amber { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.pdi-status.is-amber .pdi-dot { background: #f59e0b; }
.pdi-status.is-emerald { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.pdi-status.is-emerald .pdi-dot { background: #10b981; }
.pdi-status.is-crimson { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.pdi-status.is-crimson .pdi-dot { background: #ef4444; }
.pdi-status.is-slate { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.pdi-status.is-slate .pdi-dot { background: #94a3b8; }

.pdi-action-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 13px; border-radius: 9px; border: 0; background: var(--pdi-ink); color: #fff; font: inherit; font-size: 12px; font-weight: 600; cursor: pointer; }
.pdi-action-btn:hover { background: var(--pdi-accent); }
.pdi-empty { padding: 44px 20px !important; text-align: center; color: var(--pdi-muted); }
.pdi-pagination { padding: 14px 18px; border-top: 1px solid var(--pdi-line-soft); }

.pdi-modal-backdrop[hidden] { display: none !important; }
.pdi-modal-backdrop { position: fixed; inset: 0; z-index: 999; background: rgba(15,23,42,0.58); backdrop-filter: blur(5px); display: flex; align-items: center; justify-content: center; padding: 16px; }
.pdi-modal-dialog { width: 100%; max-height: calc(100dvh - 32px); display: flex; flex-direction: column; overflow: hidden; background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; box-shadow: 0 24px 60px rgba(15,23,42,0.25); }
.pdi-modal-xl { max-width: 820px; }
.pdi-modal-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; padding: 18px 22px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
.pdi-modal-eyebrow { display: inline-block; font-family: ui-monospace, monospace; font-size: 11px; font-weight: 700; color: #ef233c; margin-bottom: 4px; }
.pdi-modal-head h2 { margin: 0 0 3px; font-size: 18px; font-weight: 800; }
.pdi-modal-sub { margin: 0; font-size: 12.5px; color: #64748b; }
.pdi-modal-close { width: 34px; height: 34px; border-radius: 9px; border: 1px solid #e2e8f0; background: #fff; color: #64748b; font-size: 20px; cursor: pointer; flex-shrink: 0; }

.pdi-modal-form { display: flex; flex-direction: column; min-height: 0; flex: 1; }
.pdi-modal-body { padding: 18px 22px; overflow-y: auto; display: grid; gap: 14px; }

.pdi-progress-strip { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; padding: 11px 14px; border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0; }
.pdi-progress-label { font-size: 12px; font-weight: 700; color: #64748b; }
.pdi-progress-stats { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; font-size: 12.5px; }
.pdi-mini-stat { padding: 3px 9px; border-radius: 999px; background: #fff; border: 1px solid #e2e8f0; font-size: 12px; font-weight: 600; }
.pdi-mini-stat.is-pass strong { color: #059669; }
.pdi-mini-stat.is-fail strong { color: #dc2626; }

.pdi-section-card { padding: 14px 16px; border-radius: 14px; border: 1px solid #e2e8f0; background: #fff; display: grid; gap: 10px; }
.pdi-section-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9; }
.pdi-section-head h3 { margin: 0 0 2px; font-size: 14px; font-weight: 700; }
.pdi-section-head p { margin: 0; font-size: 12px; color: #64748b; }
.pdi-section-count { font-size: 11.5px; font-weight: 600; color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; padding: 3px 9px; border-radius: 999px; white-space: nowrap; }

.pdi-check-list { display: grid; gap: 8px; }
.pdi-check-item { padding: 10px 12px; border-radius: 10px; background: #f8fafc; border: 1px solid #f1f5f9; }
.pdi-check-item.is-pass { background: #f0fdf4; border-color: #bbf7d0; }
.pdi-check-item.is-fail { background: #fef2f2; border-color: #fecaca; }
.pdi-check-item.is-na { background: #f8fafc; border-color: #cbd5e1; }
.pdi-check-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.pdi-check-top strong { font-size: 13px; color: #0f172a; }

.pdi-toggle-group { display: inline-flex; gap: 6px; flex-wrap: wrap; }
.pdi-pill { position: relative; cursor: pointer; user-select: none; }
.pdi-pill input { position: absolute; opacity: 0; pointer-events: none; }
.pdi-pill span { display: inline-flex; align-items: center; padding: 6px 11px; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff; font-size: 12px; font-weight: 600; color: #0f172a; }
.pdi-pill.is-pass input:checked + span { background: #059669; border-color: #059669; color: #fff; }
.pdi-pill.is-fail input:checked + span { background: #dc2626; border-color: #dc2626; color: #fff; }
.pdi-pill.is-na input:checked + span { background: #475569; border-color: #475569; color: #fff; }

.pdi-note-wrap { margin-top: 9px; padding-top: 9px; border-top: 1px dashed #fca5a5; }
.pdi-note-wrap[hidden] { display: none !important; }
.pdi-note-label { display: flex; justify-content: space-between; gap: 8px; font-size: 11.5px; font-weight: 700; color: #991b1b; margin-bottom: 5px; }
.pdi-required-star { color: #dc2626; font-weight: 600; }
.pdi-note-input { width: 100%; height: 36px; padding: 0 10px; border: 1px solid #fca5a5; border-radius: 8px; background: #fff; font: inherit; font-size: 12.5px; }

.pdi-final-notes label { display: block; font-size: 12.5px; font-weight: 700; margin-bottom: 6px; }
.pdi-final-notes textarea { width: 100%; padding: 9px 12px; border: 1px solid #e2e8f0; border-radius: 10px; font: inherit; font-size: 12.5px; resize: vertical; }

.pdi-client-error { padding: 10px 12px; border-radius: 10px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-size: 12.5px; font-weight: 600; }
.pdi-client-error[hidden] { display: none !important; }

.pdi-modal-foot { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 14px 22px; border-top: 1px solid #e2e8f0; background: #f8fafc; }

@media (max-width: 900px) { .pdi-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 640px) { .pdi-kpis { grid-template-columns: 1fr; } }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const openModal = (modal) => {
        if (!modal) return;
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = (modal) => {
        if (!modal) return;
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    document.querySelectorAll('[data-pdi-open]').forEach(btn => {
        btn.addEventListener('click', () => {
            openModal(document.getElementById(btn.dataset.pdiOpen));
        });
    });

    document.querySelectorAll('[data-pdi-modal]').forEach(modal => {
        modal.querySelectorAll('[data-pdi-close]').forEach(btn => {
            btn.addEventListener('click', () => closeModal(modal));
        });
        modal.addEventListener('click', e => {
            if (e.target === modal) closeModal(modal);
        });

        const form = modal.querySelector('[data-pdi-form]');
        if (!form) return;

        const items = Array.from(form.querySelectorAll('[data-pdi-item]'));
        const countEl = form.querySelector('[data-pdi-count]');
        const passEl = form.querySelector('[data-pdi-pass]');
        const failEl = form.querySelector('[data-pdi-fail]');
        const errorBox = form.querySelector('[data-pdi-client-error]');

        const sync = () => {
            let checked = 0;
            let pass = 0;
            let fail = 0;

            items.forEach(item => {
                const selected = item.querySelector('input[type="radio"]:checked');
                const noteWrap = item.querySelector('[data-pdi-note-wrap]');
                const noteInput = item.querySelector('[data-pdi-note]');
                const val = selected ? selected.value : '';

                item.classList.remove('is-pass', 'is-fail', 'is-na');
                if (val === 'pass') {
                    item.classList.add('is-pass');
                    checked++;
                    pass++;
                } else if (val === 'issue') {
                    item.classList.add('is-fail');
                    checked++;
                    fail++;
                } else if (val === 'na') {
                    item.classList.add('is-na');
                    checked++;
                }

                if (noteWrap && noteInput) {
                    const isFail = val === 'issue';
                    noteWrap.hidden = !isFail;
                    noteInput.required = isFail;
                }
            });

            if (countEl) countEl.textContent = `${checked} / ${items.length} Item Diperiksa`;
            if (passEl) passEl.textContent = String(pass);
            if (failEl) failEl.textContent = String(fail);
        };

        items.forEach(item => {
            item.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', () => {
                    if (errorBox) errorBox.hidden = true;
                    sync();
                    if (radio.value === 'issue') {
                        item.querySelector('[data-pdi-note]')?.focus();
                    }
                });
            });
        });

        form.addEventListener('submit', e => {
            if (errorBox) errorBox.hidden = true;
            for (const item of items) {
                const selected = item.querySelector('input[type="radio"]:checked');
                if (!selected) {
                    e.preventDefault();
                    if (errorBox) {
                        errorBox.textContent = 'Semua item pemeriksaan PDI mobil baru wajib dipilih hasilnya (Lulus / Tidak Lulus / N/A).';
                        errorBox.hidden = false;
                    }
                    item.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
                if (selected.value === 'issue') {
                    const noteInput = item.querySelector('[data-pdi-note]');
                    if (noteInput && noteInput.value.trim() === '') {
                        e.preventDefault();
                        if (errorBox) {
                            errorBox.textContent = 'Item PDI yang dinyatakan Tidak Lulus wajib dilengkapi catatan temuan pabrik.';
                            errorBox.hidden = false;
                        }
                        noteInput.focus();
                        noteInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }
                }
            }
        });

        sync();
    });

    @if(session('open_pdi'))
        openModal(document.getElementById('pdi-modal-{{ session('open_pdi') }}'));
    @endif
});
</script>
@endpush
