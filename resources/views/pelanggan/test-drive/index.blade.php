@extends('layouts.app')
@section('title', 'Test Drive Saya · YZA Automotive')
@section('content')
@php
    $collection = $rows->getCollection();
    $totalCount = $rows->total();
    $activeCount = $collection->whereIn('status', ['menunggu', 'disetujui'])->count();
    $doneCount = $collection->where('status', 'selesai')->count();
    $statusLabels = [
        'menunggu' => 'Menunggu Konfirmasi',
        'disetujui' => 'Disetujui',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
        'ditolak' => 'Ditolak',
    ];
@endphp

<div class="tdx-page" data-tdx-page>
    <header class="tdx-hero">
        <div class="tdx-hero-left">
            <a class="tdx-back" href="{{ route('pelanggan.member') }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                <span>Kembali ke Member Center</span>
            </a>
            <span class="tdx-eyebrow">YZA TEST DRIVE</span>
            <h1>Test Drive Saya</h1>
            <p>Rasakan langsung performa dan kenyamanan kendaraan YZA pilihan Anda.</p>
        </div>
        <div class="tdx-hero-actions">
            <a class="tdx-btn tdx-btn-secondary" href="{{ route('pelanggan.booking.index') }}">Booking Saya</a>
            <a class="tdx-btn tdx-btn-primary" href="{{ route('pelanggan.katalog-mobil.index') }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                <span>Jadwalkan Test Drive</span>
            </a>
        </div>
    </header>

    @if(session('success'))
        <div class="tdx-alert is-success" role="status">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <section class="tdx-kpis" aria-label="Ringkasan test drive">
        <article class="tdx-kpi">
            <div class="tdx-kpi-head">
                <span class="tdx-kpi-icon is-slate" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                </span>
                <small>Total Test Drive</small>
            </div>
            <strong>{{ $totalCount }}</strong>
            <span>Seluruh pengajuan Anda</span>
        </article>
        <article class="tdx-kpi">
            <div class="tdx-kpi-head">
                <span class="tdx-kpi-icon is-crimson" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </span>
                <small>Jadwal Aktif</small>
            </div>
            <strong>{{ $activeCount }}</strong>
            <span>Menunggu &amp; disetujui</span>
        </article>
        <article class="tdx-kpi">
            <div class="tdx-kpi-head">
                <span class="tdx-kpi-icon is-emerald" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </span>
                <small>Selesai</small>
            </div>
            <strong>{{ $doneCount }}</strong>
            <span>Sesi telah terlaksana</span>
        </article>
    </section>

    <section class="tdx-toolbar" aria-label="Filter test drive">
        <div class="tdx-tabs" role="tablist">
            <button type="button" class="tdx-tab is-active" data-tdx-filter="all">Semua</button>
            <button type="button" class="tdx-tab" data-tdx-filter="active">Aktif</button>
            <button type="button" class="tdx-tab" data-tdx-filter="complete">Selesai</button>
            <button type="button" class="tdx-tab" data-tdx-filter="cancelled">Dibatalkan / Ditolak</button>
        </div>
        <div class="tdx-toolbar-right">
            <label class="tdx-search">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="search" data-tdx-search placeholder="Cari kode, mobil, atau lokasi..." aria-label="Cari test drive">
            </label>
            <span class="tdx-count" data-tdx-count>{{ $collection->count() }} jadwal</span>
        </div>
    </section>

    <section class="tdx-list" aria-label="Daftar test drive">
        @forelse($rows as $td)
            @php
                $st = strtolower((string) $td->status);
                $bucket = in_array($st, ['menunggu', 'disetujui'], true) ? 'active' : ($st === 'selesai' ? 'complete' : 'cancelled');
                $badgeClass = $st === 'selesai' ? 'is-complete' : ($st === 'disetujui' ? 'is-approved' : ($st === 'menunggu' ? 'is-pending' : 'is-cancelled'));
                $carTitle = trim(($td->mobil?->merk ?: '') . ' ' . ($td->mobil?->model ?: '')) ?: 'Unit Kendaraan YZA';
                $dateParsed = $td->tanggal ? \Carbon\Carbon::parse($td->tanggal) : null;
                $locText = $td->lokasi ?: 'Showroom YZA Automotive';
                $labelText = $statusLabels[$st] ?? ucfirst($st);
                $searchBlob = strtolower($td->kode_test_drive . ' ' . $carTitle . ' ' . $locText . ' ' . $labelText);
            @endphp
            <article class="tdx-item" data-tdx-item data-bucket="{{ $bucket }}" data-search="{{ $searchBlob }}">
                <div class="tdx-item-date">
                    <span class="tdx-date-day">{{ $dateParsed?->format('d') ?: '—' }}</span>
                    <div>
                        <strong>{{ $dateParsed?->format('M Y') ?: '—' }}</strong>
                        <small>{{ $td->jam ? substr((string) $td->jam, 0, 5) . ' WIB' : '—' }}</small>
                    </div>
                </div>

                <div class="tdx-item-main">
                    <div class="tdx-item-top">
                        <span class="tdx-code">{{ $td->kode_test_drive }}</span>
                        <h3>{{ $carTitle }}</h3>
                        <span class="tdx-status {{ $badgeClass }}">{{ $labelText }}</span>
                    </div>
                    <div class="tdx-meta">
                        <span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            {{ $locText }}
                        </span>
                        @if($td->catatan)
                            <span>· {{ $td->catatan }}</span>
                        @endif
                    </div>
                </div>

                <div class="tdx-item-actions">
                    <a class="tdx-detail-btn" href="{{ route('pelanggan.test-drive.show', $td) }}">
                        <span>Detail</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                    @if(in_array($td->status, ['menunggu', 'disetujui'], true))
                        <button type="button" class="tdx-cancel-btn" data-tdx-cancel data-cancel-url="{{ route('pelanggan.test-drive.cancel', $td) }}" data-cancel-code="{{ $td->kode_test_drive }}" data-cancel-car="{{ $carTitle }}">
                            Batalkan
                        </button>
                    @endif
                </div>
            </article>
        @empty
            <div class="tdx-empty">
                <span class="tdx-empty-icon" aria-hidden="true">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                </span>
                <strong>Belum ada jadwal test drive</strong>
                <p>Pilih unit kendaraan dari katalog YZA untuk menjadwalkan pengalaman berkendara langsung.</p>
                <a class="tdx-btn tdx-btn-primary" href="{{ route('pelanggan.katalog-mobil.index') }}">Jelajahi Katalog Mobil</a>
            </div>
        @endforelse

        <div class="tdx-empty" data-tdx-empty-filtered hidden>
            <strong>Tidak ada jadwal yang cocok</strong>
            <p>Coba ubah filter status atau kata kunci pencarian Anda.</p>
        </div>
    </section>

    @if($rows->hasPages())
        <div class="tdx-pagination">{{ $rows->links() }}</div>
    @endif
</div>

<div class="tdx-modal-backdrop" data-tdx-modal hidden role="dialog" aria-modal="true" aria-labelledby="tdx-modal-title">
    <div class="tdx-modal">
        <div class="tdx-modal-icon" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <h2 id="tdx-modal-title">Batalkan Test Drive?</h2>
        <p>Jadwal test drive <strong data-tdx-modal-code></strong> (<span data-tdx-modal-car></span>) akan dibatalkan dan tidak dapat dikembalikan.</p>
        <form method="post" data-tdx-modal-form class="tdx-modal-actions">
            @csrf
            @method('PATCH')
            <button type="button" class="tdx-btn tdx-btn-secondary" data-tdx-modal-close>Kembali</button>
            <button type="submit" class="tdx-btn tdx-btn-primary">Ya, Batalkan</button>
        </form>
    </div>
</div>

<style>
.tdx-page{width:min(94vw,1340px);margin:26px auto 64px;display:flex;flex-direction:column;gap:20px;color:#0f172a}
.tdx-hero{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:28px 30px;border-radius:22px;background:linear-gradient(135deg,#fff 0%,#f8fafc 100%);border:1px solid #e2e8f0;box-shadow:0 10px 30px rgba(15,23,42,.04)}
.tdx-back{display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:#64748b !important;text-decoration:none;margin-bottom:12px}
.tdx-back:hover{color:#0f172a !important}
.tdx-eyebrow{display:block;font-size:11px;font-weight:800;letter-spacing:.1em;color:#e11d48;margin-bottom:4px}
.tdx-hero h1{margin:0;font-size:clamp(26px,3vw,34px);font-weight:800;letter-spacing:-.02em;color:#0f172a}
.tdx-hero p{margin:6px 0 0;font-size:14px;color:#64748b;max-width:560px}
.tdx-hero-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.tdx-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 18px;border-radius:12px;font-size:13.5px;font-weight:700;text-decoration:none;border:0;cursor:pointer;transition:transform .15s ease,background .15s ease}
.tdx-btn:hover{transform:translateY(-1px)}
.tdx-btn-primary{background:#e11d48;color:#fff !important;box-shadow:0 8px 20px rgba(225,29,72,.22)}
.tdx-btn-primary:hover{background:#be123c}
.tdx-btn-secondary{background:#fff;border:1px solid #cbd5e1;color:#0f172a !important}
.tdx-btn-secondary:hover{background:#f8fafc}
.tdx-alert{display:flex;align-items:center;gap:10px;padding:13px 18px;border-radius:14px;font-size:13.5px;font-weight:600}
.tdx-alert.is-success{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}
.tdx-kpis{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
.tdx-kpi{background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:18px 20px;display:flex;flex-direction:column;gap:6px;box-shadow:0 8px 24px rgba(15,23,42,.03)}
.tdx-kpi-head{display:flex;align-items:center;gap:10px}
.tdx-kpi-head small{font-size:12px;font-weight:700;color:#64748b}
.tdx-kpi-icon{width:34px;height:34px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center}
.tdx-kpi-icon.is-crimson{background:#fff1f2;color:#e11d48}
.tdx-kpi-icon.is-emerald{background:#ecfdf5;color:#059669}
.tdx-kpi-icon.is-slate{background:#f1f5f9;color:#0f172a}
.tdx-kpi strong{font-size:24px;font-weight:800;color:#0f172a}
.tdx-kpi>span{font-size:12px;color:#64748b}
.tdx-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap}
.tdx-tabs{display:inline-flex;padding:4px;border-radius:12px;background:#f1f5f9;border:1px solid #e2e8f0;gap:4px;flex-wrap:wrap}
.tdx-tab{border:0;background:transparent;padding:8px 14px;border-radius:9px;font-size:13px;font-weight:600;color:#475569;cursor:pointer}
.tdx-tab.is-active{background:#fff;color:#0f172a;box-shadow:0 2px 6px rgba(15,23,42,.08)}
.tdx-toolbar-right{display:flex;align-items:center;gap:12px;flex:1;justify-content:flex-end;min-width:260px}
.tdx-search{position:relative;flex:1;max-width:360px;display:flex;align-items:center}
.tdx-search svg{position:absolute;left:13px;color:#94a3b8;pointer-events:none}
.tdx-search input{width:100%;padding:9px 14px 9px 38px;border-radius:11px;border:1px solid #cbd5e1;background:#fff;font-size:13px;color:#0f172a}
.tdx-count{font-size:12.5px;font-weight:600;color:#64748b;white-space:nowrap}
.tdx-list{display:flex;flex-direction:column;gap:12px}
.tdx-item{background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:18px 22px;display:grid;grid-template-columns:124px minmax(0,1fr) auto;gap:20px;align-items:center}
.tdx-item[hidden]{display:none !important}
.tdx-item-date{display:flex;align-items:center;gap:10px;padding-right:16px;border-right:1px solid #f1f5f9}
.tdx-date-day{font-size:26px;font-weight:800;color:#0f172a;line-height:1}
.tdx-item-date strong{display:block;font-size:12.5px;font-weight:700;color:#334155}
.tdx-item-date small{display:block;font-size:11.5px;color:#94a3b8;margin-top:2px}
.tdx-item-main{min-width:0}
.tdx-item-top{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px}
.tdx-code{padding:3px 9px;border-radius:7px;background:#f1f5f9;color:#0f172a;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12px;font-weight:700}
.tdx-item-top h3{margin:0;font-size:16px;font-weight:800;color:#0f172a}
.tdx-status{padding:4px 10px;border-radius:999px;font-size:11.5px;font-weight:700}
.tdx-status.is-complete{background:#ecfdf5;color:#047857}
.tdx-status.is-approved{background:#eff6ff;color:#1d4ed8}
.tdx-status.is-pending{background:#fffbeb;color:#b45309}
.tdx-status.is-cancelled{background:#fff1f2;color:#be123c}
.tdx-meta{display:flex;align-items:center;gap:10px;flex-wrap:wrap;font-size:13px;color:#64748b}
.tdx-meta span{display:inline-flex;align-items:center;gap:6px}
.tdx-item-actions{display:flex;align-items:center;gap:8px;padding-left:16px;border-left:1px solid #f1f5f9}
.tdx-detail-btn{display:inline-flex;align-items:center;gap:6px;padding:9px 15px;border-radius:10px;background:#0f172a;color:#fff !important;font-size:13px;font-weight:700;text-decoration:none}
.tdx-detail-btn:hover{background:#e11d48}
.tdx-cancel-btn{padding:9px 14px;border-radius:10px;border:1px solid #fecdd3;background:#fff1f2;color:#be123c;font-size:13px;font-weight:700;cursor:pointer}
.tdx-cancel-btn:hover{background:#ffe4e6}
.tdx-empty{padding:46px 24px;text-align:center;border-radius:20px;border:1px dashed #cbd5e1;background:#fff;display:flex;flex-direction:column;align-items:center;gap:10px}
.tdx-empty[hidden]{display:none !important}
.tdx-empty-icon{width:54px;height:54px;border-radius:16px;background:#fff1f2;color:#e11d48;display:inline-flex;align-items:center;justify-content:center}
.tdx-empty strong{font-size:17px;font-weight:800;color:#0f172a}
.tdx-empty p{margin:0 0 8px;font-size:13.5px;color:#64748b;max-width:420px}
.tdx-modal-backdrop{position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;padding:20px;background:rgba(15,23,42,.56);backdrop-filter:blur(6px)}
.tdx-modal-backdrop[hidden]{display:none !important}
.tdx-modal{width:min(100%,420px);background:#fff;border-radius:20px;padding:26px;border:1px solid #e2e8f0;box-shadow:0 24px 60px rgba(15,23,42,.22);text-align:center}
.tdx-modal-icon{width:46px;height:46px;margin:0 auto 12px;border-radius:14px;background:#fff1f2;color:#e11d48;display:flex;align-items:center;justify-content:center}
.tdx-modal h2{margin:0 0 8px;font-size:18px;font-weight:800;color:#0f172a}
.tdx-modal p{margin:0 0 20px;font-size:13.5px;color:#64748b;line-height:1.5}
.tdx-modal-actions{display:flex;justify-content:center;gap:10px}
@media (max-width:780px){
    .tdx-kpis{grid-template-columns:1fr}
    .tdx-item{grid-template-columns:1fr;gap:14px}
    .tdx-item-date{border-right:0;border-bottom:1px solid #f1f5f9;padding-right:0;padding-bottom:10px}
    .tdx-item-actions{border-left:0;border-top:1px solid #f1f5f9;padding-left:0;padding-top:12px;justify-content:flex-end}
}
</style>

<script>
(() => {
    const page = document.querySelector('[data-tdx-page]');
    if (!page) return;
    const tabs = [...page.querySelectorAll('[data-tdx-filter]')];
    const items = [...page.querySelectorAll('[data-tdx-item]')];
    const searchInput = page.querySelector('[data-tdx-search]');
    const countEl = page.querySelector('[data-tdx-count]');
    const emptyFiltered = page.querySelector('[data-tdx-empty-filtered]');
    let activeFilter = 'all';

    const applyFilter = () => {
        const q = (searchInput?.value || '').trim().toLowerCase();
        let visible = 0;
        items.forEach((item) => {
            const ok = (activeFilter === 'all' || item.dataset.bucket === activeFilter) && (!q || (item.dataset.search || '').includes(q));
            item.hidden = !ok;
            if (ok) visible++;
        });
        if (countEl) countEl.textContent = `${visible} jadwal`;
        if (emptyFiltered) emptyFiltered.hidden = !items.length || visible > 0;
    };

    tabs.forEach((t) => t.addEventListener('click', () => {
        activeFilter = t.dataset.tdxFilter || 'all';
        tabs.forEach((x) => x.classList.toggle('is-active', x === t));
        applyFilter();
    }));
    searchInput?.addEventListener('input', applyFilter);

    const modal = document.querySelector('[data-tdx-modal]');
    const modalForm = modal?.querySelector('[data-tdx-modal-form]');
    const modalCode = modal?.querySelector('[data-tdx-modal-code]');
    const modalCar = modal?.querySelector('[data-tdx-modal-car]');
    const closeModal = () => { if (modal) { modal.hidden = true; document.body.style.overflow = ''; } };

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-tdx-cancel]');
        if (btn && modal && modalForm) {
            modalForm.action = btn.dataset.cancelUrl;
            if (modalCode) modalCode.textContent = btn.dataset.cancelCode || '';
            if (modalCar) modalCar.textContent = btn.dataset.cancelCar || '';
            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            return;
        }
        if (e.target === modal || e.target.closest('[data-tdx-modal-close]')) closeModal();
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && modal && !modal.hidden) closeModal(); });
})();
</script>
@endsection
