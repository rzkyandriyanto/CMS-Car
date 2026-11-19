@extends('layouts.app')

@section('title', 'Laporan Keuangan · YZA Automotive')

@section('content')
@php
    $reportRoute = route('admin.finance.report');
    $hasFilter = ($from ?? '') !== '' || ($to ?? '') !== '' || ($jenis ?? 'semua') !== 'semua';
    $exportUrl = $reportRoute . '?' . http_build_query(array_filter([
        'from' => $from ?: null,
        'to' => $to ?: null,
        'jenis' => $jenis !== 'semua' ? $jenis : null,
        'export' => 'csv',
    ]));
@endphp

<div class="finrep-page">
    <header class="finrep-header">
        <div>
            <div class="finrep-eyebrow">
                <span class="finrep-eyebrow-dot" aria-hidden="true"></span>
                <span>Finance &amp; Kasir · Rekapitulasi Keuangan</span>
            </div>
            <h1 class="finrep-title">Laporan Keuangan</h1>
            <p class="finrep-subtitle">Rekap data uang masuk terverifikasi dari pembayaran pembelian unit mobil, servis bengkel, dan layanan car wash.</p>
        </div>
        <div class="finrep-header-actions">
            <a href="{{ $exportUrl }}" class="finrep-btn finrep-btn-ghost">Export CSV</a>
            <a href="{{ route('admin.payments.index') }}" class="finrep-btn finrep-btn-primary">Manajemen Pembayaran →</a>
        </div>
    </header>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ $reportRoute }}" class="finrep-filter-bar">
        <div class="finrep-filter-group">
            <label for="finrep-from">Dari Tanggal</label>
            <input id="finrep-from" type="date" name="from" value="{{ $from }}" class="finrep-input">
        </div>
        <div class="finrep-filter-group">
            <label for="finrep-to">Sampai Tanggal</label>
            <input id="finrep-to" type="date" name="to" value="{{ $to }}" class="finrep-input">
        </div>
        <div class="finrep-filter-group">
            <label for="finrep-jenis">Sumber Pemasukan</label>
            <select id="finrep-jenis" name="jenis" class="finrep-input">
                <option value="semua" @selected($jenis === 'semua')>Semua (Mobil, Servis &amp; Car Wash)</option>
                <option value="penjualan_mobil" @selected($jenis === 'penjualan_mobil')>Pembelian Mobil</option>
                <option value="servis" @selected($jenis === 'servis')>Servis Bengkel</option>
                <option value="cuci_mobil" @selected($jenis === 'cuci_mobil')>Car Wash</option>
            </select>
        </div>
        <div class="finrep-filter-actions">
            <button type="submit" class="finrep-btn finrep-btn-primary">Terapkan Filter</button>
            @if($hasFilter)
                <a href="{{ $reportRoute }}" class="finrep-btn finrep-btn-ghost">Reset</a>
            @endif
        </div>
    </form>

    {{-- 5 KPI Ringkasan --}}
    <section class="finrep-kpis" aria-label="Ringkasan Laporan Keuangan">
        <article class="finrep-kpi">
            <span class="finrep-kpi-label">Total Uang Masuk</span>
            <strong class="finrep-kpi-value">Rp {{ number_format($summary['total_inflow'] ?? 0, 0, ',', '.') }}</strong>
            <span class="finrep-kpi-sub">{{ ($summary['car_count'] ?? 0) + ($summary['service_count'] ?? 0) + ($summary['wash_count'] ?? 0) }} pembayaran terverifikasi</span>
        </article>

        <article class="finrep-kpi">
            <span class="finrep-kpi-label">Uang Masuk Pembelian Mobil</span>
            <strong class="finrep-kpi-value is-crimson">Rp {{ number_format($summary['car_total'] ?? 0, 0, ',', '.') }}</strong>
            <span class="finrep-kpi-sub">
                {{ $summary['car_count'] ?? 0 }} transaksi · Offline Rp {{ number_format($summary['car_offline_total'] ?? 0, 0, ',', '.') }} · Online Rp {{ number_format($summary['car_online_total'] ?? 0, 0, ',', '.') }}
            </span>
        </article>

        <article class="finrep-kpi">
            <span class="finrep-kpi-label">Uang Masuk Servis Bengkel</span>
            <strong class="finrep-kpi-value is-blue">Rp {{ number_format($summary['service_total'] ?? 0, 0, ',', '.') }}</strong>
            <span class="finrep-kpi-sub">
                {{ $summary['service_count'] ?? 0 }} transaksi · Diskon Kupon Rp {{ number_format($summary['service_discount_total'] ?? 0, 0, ',', '.') }}
            </span>
        </article>

        <article class="finrep-kpi">
            <span class="finrep-kpi-label">Uang Masuk Car Wash</span>
            <strong class="finrep-kpi-value is-teal">Rp {{ number_format($summary['wash_total'] ?? 0, 0, ',', '.') }}</strong>
            <span class="finrep-kpi-sub">
                {{ $summary['wash_count'] ?? 0 }} transaksi · Offline Rp {{ number_format($summary['wash_offline_total'] ?? 0, 0, ',', '.') }} · Online Rp {{ number_format($summary['wash_online_total'] ?? 0, 0, ',', '.') }}
            </span>
        </article>

        <article class="finrep-kpi">
            <span class="finrep-kpi-label">Pendapatan Bersih (Net)</span>
            <strong class="finrep-kpi-value is-emerald">Rp {{ number_format($summary['net_inflow'] ?? 0, 0, ',', '.') }}</strong>
            <span class="finrep-kpi-sub">Refund keluar: Rp {{ number_format($summary['refund_total'] ?? 0, 0, ',', '.') }}</span>
        </article>
    </section>

    {{-- GRAFIK TREND LINE: MOBIL, SERVIS, CAR WASH & KESELURUHAN --}}
    <section class="finrep-chart-card" aria-label="Grafik Tren Uang Masuk">
        <div class="finrep-chart-head">
            <div>
                <span class="finrep-panel-kicker" id="finrep-range-title" style="color:#0f172a;">{{ $trendByRange['1w']['title'] ?? 'Tren Pemasukan · 1 Minggu Terakhir' }}</span>
                <h2>Grafik Tren Uang Masuk (Mobil, Servis, Car Wash &amp; Keseluruhan)</h2>
            </div>
            <div class="finrep-chart-controls">
                <div class="finrep-range-tabs" role="tablist" aria-label="Rentang Waktu Grafik">
                    <button type="button" class="finrep-range-btn" data-range="1d" role="tab" aria-selected="false">1 Hari</button>
                    <button type="button" class="finrep-range-btn is-active" data-range="1w" role="tab" aria-selected="true">1 Minggu</button>
                    <button type="button" class="finrep-range-btn" data-range="1m" role="tab" aria-selected="false">1 Bulan</button>
                    <button type="button" class="finrep-range-btn" data-range="1y" role="tab" aria-selected="false">1 Tahun</button>
                </div>
                <div class="finrep-legend">
                    <span class="finrep-legend-item"><i class="dot-car"></i> Pembelian Mobil</span>
                    <span class="finrep-legend-item"><i class="dot-service"></i> Servis Bengkel</span>
                    <span class="finrep-legend-item"><i class="dot-wash"></i> Car Wash</span>
                    <span class="finrep-legend-item"><i class="dot-total"></i> Keseluruhan</span>
                </div>
            </div>
        </div>
        <div class="finrep-chart-wrap" id="finrep-chart-wrap">
            <canvas id="finrep-trend-canvas" data-ranges='@json($trendByRange)'></canvas>
            <div id="finrep-chart-tooltip" class="finrep-tooltip" hidden></div>
        </div>
    </section>

    <div class="finrep-sections {{ $jenis === 'semua' ? 'is-two-col' : '' }}">
        {{-- REKAP 01: UANG MASUK PEMBELIAN MOBIL --}}
        @if($jenis === 'semua' || $jenis === 'penjualan_mobil')
            <section class="finrep-panel" aria-label="Rekap Uang Masuk Pembelian Mobil">
                <div class="finrep-panel-head">
                    <div>
                        <span class="finrep-panel-kicker">Rekap 01 · Penjualan Unit</span>
                        <h2>Uang Masuk Pembelian Mobil</h2>
                    </div>
                    <div class="finrep-panel-total">
                        <span>Total Mobil</span>
                        <strong>Rp {{ number_format($summary['car_total'] ?? 0, 0, ',', '.') }}</strong>
                    </div>
                </div>

                <div class="finrep-table-wrap">
                    <table class="finrep-table">
                        <thead>
                            <tr>
                                <th>Kode Bayar</th>
                                <th>Tanggal</th>
                                <th>Member / Pembeli</th>
                                <th>Unit Mobil</th>
                                <th>Metode</th>
                                <th style="text-align: right;">Nominal Masuk</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($carInflows as $row)
                                <tr>
                                    <td>
                                        <span class="finrep-mono">{{ $row['id'] }}</span>
                                        <span class="finrep-sub">{{ $row['ref_code'] }} · {{ $row['trx_code'] }}</span>
                                    </td>
                                    <td>{{ $row['date']?->format('d M Y, H:i') ?? '—' }}</td>
                                    <td><strong>{{ $row['customer'] }}</strong></td>
                                    <td>{{ $row['unit'] }}</td>
                                    <td><span class="finrep-pill {{ $row['source'] === 'online' ? 'is-blue' : 'is-slate' }}">{{ $row['channel'] }}</span></td>
                                    <td style="text-align: right;"><strong>Rp {{ number_format($row['amount'], 0, ',', '.') }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="finrep-empty">Belum ada uang masuk pembelian mobil pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        {{-- REKAP 02: UANG MASUK SERVIS BENGKEL --}}
        @if($jenis === 'semua' || $jenis === 'servis')
            <section class="finrep-panel" aria-label="Rekap Uang Masuk Servis Bengkel">
                <div class="finrep-panel-head">
                    <div>
                        <span class="finrep-panel-kicker is-blue">Rekap 02 · Layanan Bengkel</span>
                        <h2>Uang Masuk Servis Bengkel</h2>
                    </div>
                    <div class="finrep-panel-total">
                        <span>Total Servis</span>
                        <strong>Rp {{ number_format($summary['service_total'] ?? 0, 0, ',', '.') }}</strong>
                    </div>
                </div>

                <div class="finrep-table-wrap">
                    <table class="finrep-table">
                        <thead>
                            <tr>
                                <th>Kode Bayar</th>
                                <th>Tanggal</th>
                                <th>Member</th>
                                <th>Work Order &amp; Kendaraan</th>
                                <th>Metode</th>
                                <th style="text-align: right;">Diskon Kupon</th>
                                <th style="text-align: right;">Nominal Masuk</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($serviceInflows as $row)
                                <tr>
                                    <td>
                                        <span class="finrep-mono">{{ $row['id'] }}</span>
                                        <span class="finrep-sub">{{ $row['trx_code'] }}</span>
                                    </td>
                                    <td>{{ $row['date']?->format('d M Y, H:i') ?? '—' }}</td>
                                    <td><strong>{{ $row['customer'] }}</strong></td>
                                    <td>
                                        <strong>{{ $row['unit'] }}</strong>
                                        <span class="finrep-sub">{{ $row['ref_code'] }}</span>
                                    </td>
                                    <td><span class="finrep-pill {{ $row['source'] === 'online' ? 'is-blue' : 'is-slate' }}">{{ $row['channel'] }}</span></td>
                                    <td style="text-align: right; color: #64748b;">
                                        {{ $row['discount'] > 0 ? '- Rp ' . number_format($row['discount'], 0, ',', '.') : '—' }}
                                    </td>
                                    <td style="text-align: right;"><strong>Rp {{ number_format($row['amount'], 0, ',', '.') }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="finrep-empty">Belum ada uang masuk servis bengkel pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        {{-- REKAP 03: UANG MASUK CAR WASH --}}
        @if($jenis === 'semua' || $jenis === 'cuci_mobil')
            <section class="finrep-panel {{ $jenis === 'semua' ? 'finrep-panel-wide' : '' }}" aria-label="Rekap Uang Masuk Car Wash">
                <div class="finrep-panel-head">
                    <div>
                        <span class="finrep-panel-kicker is-teal">Rekap 03 · Layanan Car Wash</span>
                        <h2>Uang Masuk Car Wash</h2>
                    </div>
                    <div class="finrep-panel-total">
                        <span>Total Car Wash</span>
                        <strong>Rp {{ number_format($summary['wash_total'] ?? 0, 0, ',', '.') }}</strong>
                    </div>
                </div>

                <div class="finrep-table-wrap">
                    <table class="finrep-table">
                        <thead>
                            <tr>
                                <th>Kode Bayar</th>
                                <th>Tanggal</th>
                                <th>Member</th>
                                <th>Paket &amp; Kendaraan</th>
                                <th>Metode</th>
                                <th style="text-align: right;">Diskon Kupon</th>
                                <th style="text-align: right;">Nominal Masuk</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($washInflows as $row)
                                <tr>
                                    <td>
                                        <span class="finrep-mono">{{ $row['id'] }}</span>
                                        <span class="finrep-sub">{{ $row['trx_code'] }}</span>
                                    </td>
                                    <td>{{ $row['date']?->format('d M Y, H:i') ?? '—' }}</td>
                                    <td><strong>{{ $row['customer'] }}</strong></td>
                                    <td>
                                        <strong>{{ $row['unit'] }}</strong>
                                        <span class="finrep-sub">{{ $row['ref_code'] }}</span>
                                    </td>
                                    <td><span class="finrep-pill {{ $row['source'] === 'online' ? 'is-blue' : 'is-slate' }}">{{ $row['channel'] }}</span></td>
                                    <td style="text-align: right; color: #64748b;">
                                        {{ $row['discount'] > 0 ? '- Rp ' . number_format($row['discount'], 0, ',', '.') : '—' }}
                                    </td>
                                    <td style="text-align: right;"><strong>Rp {{ number_format($row['amount'], 0, ',', '.') }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="finrep-empty">Belum ada uang masuk car wash pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.finrep-page {
    --fr-ink: #0f172a;
    --fr-muted: #64748b;
    --fr-line: #e2e8f0;
    --fr-line-soft: #f1f5f9;
    --fr-surface: #ffffff;
    --fr-bg-soft: #f8fafc;
    --fr-accent: #ef233c;
    max-width: 1320px;
    margin: 0 auto;
    padding: 8px 4px 40px;
    color: var(--fr-ink);
}

.finrep-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 18px; }
.finrep-eyebrow { display: inline-flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--fr-muted); margin-bottom: 6px; }
.finrep-eyebrow-dot { width: 7px; height: 7px; border-radius: 999px; background: var(--fr-accent); }
.finrep-title { margin: 0 0 4px; font-size: clamp(22px, 2.4vw, 28px); font-weight: 800; letter-spacing: -0.02em; }
.finrep-subtitle { margin: 0; font-size: 13.5px; color: var(--fr-muted); }
.finrep-header-actions { display: flex; gap: 8px; flex-wrap: wrap; }

.finrep-btn { display: inline-flex; align-items: center; justify-content: center; height: 38px; padding: 0 14px; border-radius: 10px; font: inherit; font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; border: 1px solid transparent; white-space: nowrap; }
.finrep-btn-primary { background: var(--fr-ink); color: #fff; }
.finrep-btn-primary:hover { background: var(--fr-accent); }
.finrep-btn-ghost { background: var(--fr-surface); border-color: var(--fr-line); color: var(--fr-ink); }
.finrep-btn-ghost:hover { background: var(--fr-bg-soft); }

.finrep-filter-bar {
    display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap;
    padding: 14px 16px; margin-bottom: 18px;
    background: var(--fr-surface); border: 1px solid var(--fr-line); border-radius: 14px;
}
.finrep-filter-group { display: grid; gap: 5px; flex: 1 1 180px; }
.finrep-filter-group label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--fr-muted); }
.finrep-input { height: 38px; padding: 0 10px; border: 1px solid var(--fr-line); border-radius: 10px; background: var(--fr-bg-soft); font: inherit; font-size: 13px; color: var(--fr-ink); }
.finrep-filter-actions { display: flex; gap: 8px; }

.finrep-kpis { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; margin-bottom: 20px; }
.finrep-kpi { padding: 15px 16px; background: var(--fr-surface); border: 1px solid var(--fr-line); border-radius: 14px; min-width: 0; }
.finrep-kpi-label { display: block; font-size: 10.5px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--fr-muted); margin-bottom: 6px; }
.finrep-kpi-value { display: block; font-size: 20px; font-weight: 800; letter-spacing: -0.02em; line-height: 1.15; margin-bottom: 6px; font-variant-numeric: tabular-nums; }
.finrep-kpi-value.is-crimson { color: #dc2626; }
.finrep-kpi-value.is-blue { color: #2563eb; }
.finrep-kpi-value.is-teal { color: #0891b2; }
.finrep-kpi-value.is-emerald { color: #059669; }
.finrep-kpi-sub { display: block; font-size: 11px; color: var(--fr-muted); line-height: 1.4; }

.finrep-sections { display: grid; gap: 18px; align-items: start; }
.finrep-sections.is-two-col { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.finrep-panel-wide { grid-column: 1 / -1; }
.finrep-chart-card { background: var(--fr-surface); border: 1px solid var(--fr-line); border-radius: 16px; overflow: hidden; margin-bottom: 18px; }
.finrep-chart-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; padding: 15px 18px; border-bottom: 1px solid var(--fr-line); background: var(--fr-bg-soft); }
.finrep-chart-head h2 { margin: 0; font-size: 16px; font-weight: 800; }
.finrep-chart-controls { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; justify-content: flex-end; }
.finrep-range-tabs { display: inline-flex; padding: 3px; border-radius: 10px; background: #e2e8f0; gap: 2px; }
.finrep-range-btn { border: 0; background: transparent; color: #475569; font: inherit; font-size: 11.5px; font-weight: 700; padding: 5px 11px; border-radius: 8px; cursor: pointer; transition: all 0.18s ease; }
.finrep-range-btn:hover { color: #0f172a; }
.finrep-range-btn.is-active { background: #0f172a; color: #ffffff; box-shadow: 0 2px 6px rgba(15, 23, 42, 0.18); }
.finrep-legend { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; font-size: 12px; font-weight: 600; color: var(--fr-ink); }
.finrep-legend-item { display: inline-flex; align-items: center; gap: 6px; }
.finrep-legend-item i { width: 12px; height: 3px; border-radius: 999px; display: inline-block; }
.finrep-legend-item i.dot-car { background: #ef233c; }
.finrep-legend-item i.dot-service { background: #2563eb; }
.finrep-legend-item i.dot-wash { background: #0891b2; }
.finrep-legend-item i.dot-total { background: #0f172a; border-top: 2px dashed #0f172a; height: 0; }
.finrep-chart-wrap { position: relative; padding: 18px 20px 14px; min-height: 280px; }
#finrep-trend-canvas { width: 100%; height: 270px; display: block; }
.finrep-tooltip {
    position: absolute; pointer-events: none; z-index: 20;
    min-width: 210px; padding: 10px 12px; border-radius: 10px;
    background: #0f172a; color: #fff; font-size: 12px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.25);
}
.finrep-tooltip[hidden] { display: none !important; }
.finrep-tooltip-date { font-weight: 700; margin-bottom: 6px; padding-bottom: 5px; border-bottom: 1px solid rgba(255,255,255,0.14); font-size: 11.5px; color: #cbd5e1; }
.finrep-tooltip-row { display: flex; justify-content: space-between; gap: 12px; margin-top: 3px; }
.finrep-panel { background: var(--fr-surface); border: 1px solid var(--fr-line); border-radius: 16px; overflow: hidden; }
.finrep-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; padding: 15px 18px; border-bottom: 1px solid var(--fr-line); background: var(--fr-bg-soft); }
.finrep-panel-kicker { display: block; font-size: 10.5px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #dc2626; margin-bottom: 2px; }
.finrep-panel-kicker.is-blue { color: #2563eb; }
.finrep-panel-kicker.is-teal { color: #0891b2; }
.finrep-panel-head h2 { margin: 0; font-size: 16px; font-weight: 800; }
.finrep-panel-total { text-align: right; }
.finrep-panel-total span { display: block; font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: var(--fr-muted); }
.finrep-panel-total strong { font-size: 16px; font-weight: 800; color: var(--fr-ink); }

.finrep-table-wrap { overflow-x: auto; }
.finrep-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
.finrep-table th { padding: 11px 16px; background: var(--fr-bg-soft); border-bottom: 1px solid var(--fr-line); font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--fr-muted); }
.finrep-table td { padding: 13px 16px; border-bottom: 1px solid var(--fr-line-soft); vertical-align: middle; }
.finrep-mono { display: block; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-weight: 700; font-size: 12px; }
.finrep-sub { display: block; margin-top: 2px; font-size: 11.5px; color: var(--fr-muted); }
.finrep-pill { display: inline-block; padding: 3px 9px; border-radius: 999px; font-size: 11px; font-weight: 700; }
.finrep-pill.is-blue { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.finrep-pill.is-slate { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; }
.finrep-empty { padding: 36px 16px !important; text-align: center; color: var(--fr-muted); }

@media (max-width: 1120px) {
    .finrep-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .finrep-sections.is-two-col { grid-template-columns: 1fr; }
}
@media (max-width: 640px) { .finrep-kpis { grid-template-columns: 1fr; } }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('finrep-trend-canvas');
    const tooltip = document.getElementById('finrep-chart-tooltip');
    const wrap = document.getElementById('finrep-chart-wrap');
    const titleEl = document.getElementById('finrep-range-title');
    const rangeBtns = Array.from(document.querySelectorAll('.finrep-range-btn'));
    if (!canvas || !wrap) return;

    const allRanges = JSON.parse(canvas.dataset.ranges || '{}');
    let activeKey = '1w';
    if (!allRanges[activeKey]) {
        activeKey = Object.keys(allRanges)[0] || '';
    }
    if (!activeKey || !allRanges[activeKey]) return;

    const fmtRp = (v) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(v));
    const fmtAxis = (v) => {
        if (v >= 1000000000) return 'Rp ' + (v / 1000000000).toFixed(1).replace('.0', '') + ' M';
        if (v >= 1000000) return 'Rp ' + (v / 1000000).toFixed(1).replace('.0', '') + ' Jt';
        if (v >= 1000) return 'Rp ' + (v / 1000).toFixed(0) + ' Rb';
        return 'Rp ' + v;
    };

    let hoverIdx = -1;
    let animProgress = 1;
    let animFrame = null;
    let currentMaxVal = 100000;
    let startMaxVal = 100000;
    let targetMaxVal = 100000;

    const getSeries = () => allRanges[activeKey] || { labels: [], car: [], service: [], wash: [], total: [], title: '' };
    const computeMax = (s) => Math.max(100000, ...(s.total || []), ...(s.car || []), ...(s.service || []), ...(s.wash || [])) * 1.12;
    targetMaxVal = computeMax(getSeries());
    currentMaxVal = targetMaxVal;

    const easeOutCubic = (t) => 1 - Math.pow(1 - t, 3);

    const draw = () => {
        const s = getSeries();
        const labels = s.labels || [];
        const car = s.car || [];
        const service = s.service || [];
        const wash = s.wash || [];
        const total = s.total || [];
        if (!labels.length) return;

        const rect = canvas.getBoundingClientRect();
        const dpr = window.devicePixelRatio || 1;
        const w = Math.max(300, rect.width);
        const h = 270;
        canvas.width = w * dpr;
        canvas.height = h * dpr;
        const ctx = canvas.getContext('2d');
        ctx.scale(dpr, dpr);

        const pad = { top: 18, right: 32, bottom: 34, left: 74 };
        const cw = w - pad.left - pad.right;
        const ch = h - pad.top - pad.bottom;

        const maxVal = Math.max(100000, currentMaxVal);
        const stepX = labels.length > 1 ? cw / (labels.length - 1) : cw;
        const xAt = (i) => labels.length > 1 ? pad.left + i * stepX : pad.left + cw / 2;
        const yAt = (v) => pad.top + ch - (v / maxVal) * ch;

        ctx.clearRect(0, 0, w, h);

        // Horizontal grid & Y labels
        ctx.font = '600 11px system-ui, -apple-system, sans-serif';
        ctx.textAlign = 'right';
        ctx.textBaseline = 'middle';
        const ticks = 4;
        for (let i = 0; i <= ticks; i++) {
            const val = (maxVal / ticks) * i;
            const y = yAt(val);
            ctx.strokeStyle = '#f1f5f9';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(pad.left, y);
            ctx.lineTo(w - pad.right, y);
            ctx.stroke();

            ctx.fillStyle = '#64748b';
            ctx.fillText(fmtAxis(val), pad.left - 10, y);
        }

        // X labels (sparse when many points)
        const labelStep = labels.length > 10 ? Math.ceil(labels.length / 8) : 1;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'top';
        labels.forEach((lbl, i) => {
            if (i % labelStep !== 0 && i !== labels.length - 1 && i !== hoverIdx) return;
            ctx.fillStyle = i === hoverIdx ? '#0f172a' : '#64748b';
            ctx.fillText(lbl, xAt(i), pad.top + ch + 10);
        });

        // Hover guide
        if (hoverIdx >= 0 && hoverIdx < labels.length) {
            const hx = xAt(hoverIdx);
            ctx.strokeStyle = '#cbd5e1';
            ctx.lineWidth = 1;
            ctx.setLineDash([4, 4]);
            ctx.beginPath();
            ctx.moveTo(hx, pad.top);
            ctx.lineTo(hx, pad.top + ch);
            ctx.stroke();
            ctx.setLineDash([]);
        }

        // Animated reveal clip + vertical rise
        const eased = easeOutCubic(animProgress);
        ctx.save();
        ctx.beginPath();
        ctx.rect(pad.left - 10, 0, (cw + 20) * eased + 10, h);
        ctx.clip();

        const drawLine = (arr, color, dashed = false, width = 2.5) => {
            ctx.strokeStyle = color;
            ctx.lineWidth = width;
            ctx.setLineDash(dashed ? [6, 4] : []);
            ctx.beginPath();
            arr.forEach((v, i) => {
                const x = xAt(i);
                const y = yAt(v * eased);
                if (i === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            });
            ctx.stroke();
            ctx.setLineDash([]);

            const showAllDots = labels.length <= 14;
            arr.forEach((v, i) => {
                if (!showAllDots && i !== hoverIdx && v === 0) return;
                const x = xAt(i);
                const y = yAt(v * eased);
                ctx.fillStyle = '#ffffff';
                ctx.beginPath();
                ctx.arc(x, y, i === hoverIdx ? 5.5 : 3.8, 0, Math.PI * 2);
                ctx.fill();
                ctx.strokeStyle = color;
                ctx.lineWidth = 2.3;
                ctx.stroke();
            });
        };

        drawLine(total, '#0f172a', true, 2.2);
        drawLine(car, '#ef233c', false, 2.5);
        drawLine(service, '#2563eb', false, 2.5);
        drawLine(wash, '#0891b2', false, 2.5);

        ctx.restore();
    };

    const startAnimation = () => {
        if (animFrame) cancelAnimationFrame(animFrame);
        startMaxVal = currentMaxVal;
        targetMaxVal = computeMax(getSeries());
        animProgress = 0;
        const duration = 420;
        const t0 = performance.now();

        const tick = (now) => {
            const raw = Math.min(1, (now - t0) / duration);
            animProgress = raw;
            const eased = easeOutCubic(raw);
            currentMaxVal = startMaxVal + (targetMaxVal - startMaxVal) * eased;
            draw();
            if (raw < 1) {
                animFrame = requestAnimationFrame(tick);
            } else {
                animProgress = 1;
                currentMaxVal = targetMaxVal;
                animFrame = null;
            }
        };
        animFrame = requestAnimationFrame(tick);
    };

    rangeBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            const nextKey = btn.dataset.range;
            if (!nextKey || !allRanges[nextKey] || nextKey === activeKey) return;
            activeKey = nextKey;
            hoverIdx = -1;
            if (tooltip) tooltip.hidden = true;
            rangeBtns.forEach((b) => {
                const isAct = b === btn;
                b.classList.toggle('is-active', isAct);
                b.setAttribute('aria-selected', isAct ? 'true' : 'false');
            });
            if (titleEl && allRanges[activeKey].title) {
                titleEl.textContent = allRanges[activeKey].title;
            }
            startAnimation();
        });
    });

    canvas.addEventListener('mousemove', (e) => {
        const s = getSeries();
        const labels = s.labels || [];
        const car = s.car || [];
        const service = s.service || [];
        const wash = s.wash || [];
        const total = s.total || [];
        if (!labels.length) return;

        const rect = canvas.getBoundingClientRect();
        const mx = e.clientX - rect.left;
        const padLeft = 74;
        const padRight = 32;
        const cw = Math.max(1, rect.width - padLeft - padRight);
        const stepX = labels.length > 1 ? cw / (labels.length - 1) : cw;

        let best = -1;
        let bestDist = Infinity;
        labels.forEach((_, i) => {
            const px = labels.length > 1 ? padLeft + i * stepX : padLeft + cw / 2;
            const d = Math.abs(mx - px);
            if (d < bestDist) {
                bestDist = d;
                best = i;
            }
        });

        if (best !== -1 && bestDist <= 60) {
            hoverIdx = best;
            draw();
            if (tooltip) {
                tooltip.innerHTML = `
                    <div class="finrep-tooltip-date">${labels[best]}</div>
                    <div class="finrep-tooltip-row"><span style="color:#fca5a5;">Pembelian Mobil</span><strong>${fmtRp(car[best] || 0)}</strong></div>
                    <div class="finrep-tooltip-row"><span style="color:#93c5fd;">Servis Bengkel</span><strong>${fmtRp(service[best] || 0)}</strong></div>
                    <div class="finrep-tooltip-row"><span style="color:#67e8f9;">Car Wash</span><strong>${fmtRp(wash[best] || 0)}</strong></div>
                    <div class="finrep-tooltip-row" style="margin-top:5px;padding-top:5px;border-top:1px solid rgba(255,255,255,0.14);"><span>Keseluruhan</span><strong>${fmtRp(total[best] || 0)}</strong></div>
                `;
                tooltip.hidden = false;
                const tx = Math.min(rect.width - 220, Math.max(12, mx + 14));
                tooltip.style.left = tx + 'px';
                tooltip.style.top = '18px';
            }
        } else {
            hoverIdx = -1;
            draw();
            if (tooltip) tooltip.hidden = true;
        }
    });

    canvas.addEventListener('mouseleave', () => {
        hoverIdx = -1;
        draw();
        if (tooltip) tooltip.hidden = true;
    });

    window.addEventListener('resize', draw);
    startAnimation();
});
</script>
@endpush
