@extends('layouts.app')
@section('title', 'Trade-In Saya · YZA Automotive')
@section('content')
@php
    $collection = $rows->getCollection();
    $totalCount = $rows->total();
    $doneCount = $collection->where('status', 'selesai')->count();
    $activeCount = $collection->whereNotIn('status', ['selesai', 'dibatalkan', 'ditolak'])->count();
    $steps = ['Data Kendaraan', 'Pemeriksaan', 'Penilaian', 'Penawaran', 'Keputusan'];
@endphp

<div class="tix-page" data-tix-page>
    <header class="tix-hero">
        <div class="tix-hero-left">
            <a class="tix-back" href="{{ route('pelanggan.member') }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                <span>Kembali ke Member Center</span>
            </a>
            <span class="tix-eyebrow">YZA TRADE-IN</span>
            <h1>Trade-In Saya</h1>
            <p>Tukar kendaraan lama Anda dengan unit YZA terbaru melalui proses penilaian yang transparan.</p>
        </div>
        <div class="tix-hero-actions">
            <a class="tix-btn tix-btn-secondary" href="{{ route('pelanggan.katalog-mobil.index') }}">Lihat Katalog</a>
            <a class="tix-btn tix-btn-primary" href="{{ route('pelanggan.pengajuan-mobil.index') }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                <span>Ajukan Trade-In</span>
            </a>
        </div>
    </header>

    <section class="tix-kpis" aria-label="Ringkasan trade-in">
        <article class="tix-kpi">
            <div class="tix-kpi-head">
                <span class="tix-kpi-icon is-slate" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m17 2 4 4-4 4"/><path d="M3 11v-1a4 4 0 0 1 4-4h14"/><path d="m7 22-4-4 4-4"/><path d="M21 13v1a4 4 0 0 1-4 4H3"/></svg>
                </span>
                <small>Total Pengajuan</small>
            </div>
            <strong>{{ $totalCount }}</strong>
            <span>Riwayat tukar tambah</span>
        </article>
        <article class="tix-kpi">
            <div class="tix-kpi-head">
                <span class="tix-kpi-icon is-crimson" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </span>
                <small>Dalam Proses</small>
            </div>
            <strong>{{ $activeCount }}</strong>
            <span>Menunggu tahap lanjutan</span>
        </article>
        <article class="tix-kpi">
            <div class="tix-kpi-head">
                <span class="tix-kpi-icon is-emerald" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </span>
                <small>Selesai</small>
            </div>
            <strong>{{ $doneCount }}</strong>
            <span>Trade-in berhasil</span>
        </article>
    </section>

    <section class="tix-flow" aria-label="Alur proses trade-in">
        @foreach($steps as $idx => $stepName)
            <div class="tix-flow-step">
                <span class="tix-flow-num">{{ $idx + 1 }}</span>
                <strong>{{ $stepName }}</strong>
            </div>
        @endforeach
    </section>

    <section class="tix-toolbar" aria-label="Filter trade-in">
        <div class="tix-tabs" role="tablist">
            <button type="button" class="tix-tab is-active" data-tix-filter="all">Semua</button>
            <button type="button" class="tix-tab" data-tix-filter="progress">Dalam Proses</button>
            <button type="button" class="tix-tab" data-tix-filter="complete">Selesai</button>
            <button type="button" class="tix-tab" data-tix-filter="cancelled">Dibatalkan / Ditolak</button>
        </div>
        <div class="tix-toolbar-right">
            <label class="tix-search">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="search" data-tix-search placeholder="Cari kode trade-in atau kendaraan..." aria-label="Cari trade-in">
            </label>
            <span class="tix-count" data-tix-count>{{ $collection->count() }} pengajuan</span>
        </div>
    </section>

    <section class="tix-list" aria-label="Daftar pengajuan trade-in">
        @forelse($rows as $row)
            @php
                $st = strtolower((string) $row->status);
                $bucket = $st === 'selesai' ? 'complete' : (in_array($st, ['dibatalkan', 'ditolak'], true) ? 'cancelled' : 'progress');
                $badgeClass = $st === 'selesai' ? 'is-complete' : (in_array($st, ['dibatalkan', 'ditolak'], true) ? 'is-cancelled' : 'is-progress');
                $oldCar = trim(($row->pengajuan?->merk ?: '') . ' ' . ($row->pengajuan?->model ?: '')) ?: 'Kendaraan Lama';
                $targetCar = trim(($row->mobilDibeli?->merk ?: '') . ' ' . ($row->mobilDibeli?->model ?: ''));
                $statusLabel = ucwords(str_replace('_', ' ', $st));
                $searchBlob = strtolower($row->kode_trade_in . ' ' . $oldCar . ' ' . $targetCar . ' ' . $statusLabel);
            @endphp
            <article class="tix-item" data-tix-item data-bucket="{{ $bucket }}" data-search="{{ $searchBlob }}">
                <div class="tix-item-main">
                    <div class="tix-item-top">
                        <span class="tix-code">{{ $row->kode_trade_in }}</span>
                        <h3>{{ $oldCar }}@if($targetCar) <span class="tix-arrow">→</span> {{ $targetCar }}@endif</h3>
                        <span class="tix-status {{ $badgeClass }}">{{ $statusLabel }}</span>
                    </div>
                    <div class="tix-meta">
                        <span>Diajukan {{ optional($row->created_at)->format('d M Y') ?: '—' }}</span>
                        @if($row->pengajuan?->tahun)
                            <span>· Tahun {{ $row->pengajuan->tahun }}</span>
                        @endif
                    </div>
                </div>

                <div class="tix-item-aside">
                    <div class="tix-cost">
                        <small>Selisih Pembayaran</small>
                        <strong>{{ money($row->selisih_bayar) }}</strong>
                    </div>
                    <a class="tix-detail-btn" href="{{ route('pelanggan.trade-in.show', $row) }}">
                        <span>Lihat Detail</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>
            </article>
        @empty
            <div class="tix-empty">
                <span class="tix-empty-icon" aria-hidden="true">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m17 2 4 4-4 4"/><path d="M3 11v-1a4 4 0 0 1 4-4h14"/><path d="m7 22-4-4 4-4"/><path d="M21 13v1a4 4 0 0 1-4 4H3"/></svg>
                </span>
                <strong>Belum ada pengajuan trade-in</strong>
                <p>Mulai proses tukar kendaraan lama Anda dengan unit YZA Automotive pilihan Anda.</p>
                <a class="tix-btn tix-btn-primary" href="{{ route('pelanggan.pengajuan-mobil.index') }}">Mulai Trade-In</a>
            </div>
        @endforelse

        <div class="tix-empty" data-tix-empty-filtered hidden>
            <strong>Tidak ada pengajuan yang cocok</strong>
            <p>Coba ubah filter status atau kata kunci pencarian Anda.</p>
        </div>
    </section>

    @if($rows->hasPages())
        <div class="tix-pagination">{{ $rows->links() }}</div>
    @endif
</div>

<style>
.tix-page{width:min(94vw,1340px);margin:26px auto 64px;display:flex;flex-direction:column;gap:20px;color:#0f172a}
.tix-hero{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:28px 30px;border-radius:22px;background:linear-gradient(135deg,#fff 0%,#f8fafc 100%);border:1px solid #e2e8f0;box-shadow:0 10px 30px rgba(15,23,42,.04)}
.tix-back{display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:#64748b !important;text-decoration:none;margin-bottom:12px}
.tix-back:hover{color:#0f172a !important}
.tix-eyebrow{display:block;font-size:11px;font-weight:800;letter-spacing:.1em;color:#e11d48;margin-bottom:4px}
.tix-hero h1{margin:0;font-size:clamp(26px,3vw,34px);font-weight:800;letter-spacing:-.02em;color:#0f172a}
.tix-hero p{margin:6px 0 0;font-size:14px;color:#64748b;max-width:560px}
.tix-hero-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.tix-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 18px;border-radius:12px;font-size:13.5px;font-weight:700;text-decoration:none;transition:transform .15s ease,background .15s ease}
.tix-btn:hover{transform:translateY(-1px)}
.tix-btn-primary{background:#e11d48;color:#fff !important;box-shadow:0 8px 20px rgba(225,29,72,.22)}
.tix-btn-primary:hover{background:#be123c}
.tix-btn-secondary{background:#fff;border:1px solid #cbd5e1;color:#0f172a !important}
.tix-btn-secondary:hover{background:#f8fafc}
.tix-kpis{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
.tix-kpi{background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:18px 20px;display:flex;flex-direction:column;gap:6px;box-shadow:0 8px 24px rgba(15,23,42,.03)}
.tix-kpi-head{display:flex;align-items:center;gap:10px}
.tix-kpi-head small{font-size:12px;font-weight:700;color:#64748b}
.tix-kpi-icon{width:34px;height:34px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center}
.tix-kpi-icon.is-crimson{background:#fff1f2;color:#e11d48}
.tix-kpi-icon.is-emerald{background:#ecfdf5;color:#059669}
.tix-kpi-icon.is-slate{background:#f1f5f9;color:#0f172a}
.tix-kpi strong{font-size:24px;font-weight:800;color:#0f172a}
.tix-kpi>span{font-size:12px;color:#64748b}
.tix-flow{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:14px 18px}
.tix-flow-step{display:flex;align-items:center;gap:10px;padding:6px 8px}
.tix-flow-num{width:26px;height:26px;border-radius:999px;background:#f1f5f9;color:#0f172a;font-size:12px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0}
.tix-flow-step strong{font-size:13px;font-weight:700;color:#334155}
.tix-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap}
.tix-tabs{display:inline-flex;padding:4px;border-radius:12px;background:#f1f5f9;border:1px solid #e2e8f0;gap:4px;flex-wrap:wrap}
.tix-tab{border:0;background:transparent;padding:8px 14px;border-radius:9px;font-size:13px;font-weight:600;color:#475569;cursor:pointer}
.tix-tab.is-active{background:#fff;color:#0f172a;box-shadow:0 2px 6px rgba(15,23,42,.08)}
.tix-toolbar-right{display:flex;align-items:center;gap:12px;flex:1;justify-content:flex-end;min-width:260px}
.tix-search{position:relative;flex:1;max-width:360px;display:flex;align-items:center}
.tix-search svg{position:absolute;left:13px;color:#94a3b8;pointer-events:none}
.tix-search input{width:100%;padding:9px 14px 9px 38px;border-radius:11px;border:1px solid #cbd5e1;background:#fff;font-size:13px;color:#0f172a}
.tix-count{font-size:12.5px;font-weight:600;color:#64748b;white-space:nowrap}
.tix-list{display:flex;flex-direction:column;gap:12px}
.tix-item{background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:20px 22px;display:flex;align-items:center;justify-content:space-between;gap:20px}
.tix-item[hidden]{display:none !important}
.tix-item-main{min-width:0}
.tix-item-top{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px}
.tix-code{padding:3px 9px;border-radius:7px;background:#f1f5f9;color:#0f172a;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12px;font-weight:700}
.tix-item-top h3{margin:0;font-size:16px;font-weight:800;color:#0f172a}
.tix-arrow{color:#e11d48;margin:0 4px}
.tix-status{padding:4px 10px;border-radius:999px;font-size:11.5px;font-weight:700}
.tix-status.is-complete{background:#ecfdf5;color:#047857}
.tix-status.is-progress{background:#eff6ff;color:#1d4ed8}
.tix-status.is-cancelled{background:#fff1f2;color:#be123c}
.tix-meta{font-size:13px;color:#64748b;display:flex;gap:8px;flex-wrap:wrap}
.tix-item-aside{display:flex;align-items:center;gap:18px;padding-left:18px;border-left:1px solid #f1f5f9;flex-shrink:0}
.tix-cost{text-align:right}
.tix-cost small{display:block;font-size:11.5px;color:#64748b}
.tix-cost strong{font-size:16px;font-weight:800;color:#0f172a}
.tix-detail-btn{display:inline-flex;align-items:center;gap:6px;padding:9px 15px;border-radius:10px;background:#0f172a;color:#fff !important;font-size:13px;font-weight:700;text-decoration:none;white-space:nowrap}
.tix-detail-btn:hover{background:#e11d48}
.tix-empty{padding:46px 24px;text-align:center;border-radius:20px;border:1px dashed #cbd5e1;background:#fff;display:flex;flex-direction:column;align-items:center;gap:10px}
.tix-empty[hidden]{display:none !important}
.tix-empty-icon{width:54px;height:54px;border-radius:16px;background:#fff1f2;color:#e11d48;display:inline-flex;align-items:center;justify-content:center}
.tix-empty strong{font-size:17px;font-weight:800;color:#0f172a}
.tix-empty p{margin:0 0 8px;font-size:13.5px;color:#64748b;max-width:420px}
@media (max-width:900px){
    .tix-flow{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media (max-width:780px){
    .tix-kpis{grid-template-columns:1fr}
    .tix-item{flex-direction:column;align-items:stretch}
    .tix-item-aside{justify-content:space-between;border-left:0;border-top:1px solid #f1f5f9;padding-left:0;padding-top:12px}
    .tix-cost{text-align:left}
}
</style>

<script>
(() => {
    const page = document.querySelector('[data-tix-page]');
    if (!page) return;
    const tabs = [...page.querySelectorAll('[data-tix-filter]')];
    const items = [...page.querySelectorAll('[data-tix-item]')];
    const searchInput = page.querySelector('[data-tix-search]');
    const countEl = page.querySelector('[data-tix-count]');
    const emptyFiltered = page.querySelector('[data-tix-empty-filtered]');
    let activeFilter = 'all';

    const applyFilter = () => {
        const q = (searchInput?.value || '').trim().toLowerCase();
        let visible = 0;
        items.forEach((item) => {
            const ok = (activeFilter === 'all' || item.dataset.bucket === activeFilter) && (!q || (item.dataset.search || '').includes(q));
            item.hidden = !ok;
            if (ok) visible++;
        });
        if (countEl) countEl.textContent = `${visible} pengajuan`;
        if (emptyFiltered) emptyFiltered.hidden = !items.length || visible > 0;
    };

    tabs.forEach((t) => t.addEventListener('click', () => {
        activeFilter = t.dataset.tixFilter || 'all';
        tabs.forEach((x) => x.classList.toggle('is-active', x === t));
        applyFilter();
    }));
    searchInput?.addEventListener('input', applyFilter);
})();
</script>
@endsection
