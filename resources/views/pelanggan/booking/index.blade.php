@extends('layouts.app')

@section('title', 'Booking Saya · YZA Automotive')

@section('content')
@php
    $statusMap = [
        'menunggu' => ['label' => 'Menunggu Konfirmasi', 'tone' => 'amber', 'group' => 'active'],
        'diterima' => ['label' => 'Dikonfirmasi', 'tone' => 'sky', 'group' => 'active'],
        'dikonfirmasi' => ['label' => 'Dikonfirmasi', 'tone' => 'sky', 'group' => 'active'],
        'diproses' => ['label' => 'Sedang Diproses', 'tone' => 'indigo', 'group' => 'active'],
        'proses' => ['label' => 'Sedang Dikerjakan', 'tone' => 'indigo', 'group' => 'active'],
        'selesai' => ['label' => 'Selesai', 'tone' => 'emerald', 'group' => 'done'],
        'dibatalkan' => ['label' => 'Dibatalkan', 'tone' => 'slate', 'group' => 'cancelled'],
        'ditolak' => ['label' => 'Ditolak', 'tone' => 'rose', 'group' => 'cancelled'],
        'kadaluarsa' => ['label' => 'Kadaluarsa', 'tone' => 'slate', 'group' => 'cancelled'],
        'expired' => ['label' => 'Kadaluarsa', 'tone' => 'slate', 'group' => 'cancelled'],
    ];

    $unified = collect();

    foreach (($serviceBookings ?? collect()) as $s) {
        $st = strtolower((string) $s->status);
        $meta = $statusMap[$st] ?? ['label' => ucfirst($st), 'tone' => 'slate', 'group' => 'active'];
        $base = (float) ($s->jenisServis?->estimasi_harga ?? 0);
        $promo = $s->promo;
        $disc = 0;
        if ($promo && $base > 0) {
            $disc = $promo->jenis_benefit === 'persentase'
                ? min($base, $base * ((float) $promo->nilai_benefit / 100))
                : min($base, (float) $promo->nilai_benefit);
        }
        $finalPrice = max(0, $base - $disc);
        $dateObj = $s->tanggal ? \Illuminate\Support\Carbon::parse($s->tanggal) : null;
        $jamStr = $s->jam ? substr((string) $s->jam, 0, 5) . ' WIB' : '';

        $unified->push([
            'category' => 'servis',
            'category_label' => 'Booking Servis',
            'code' => $s->kode_booking,
            'title' => trim(($s->kendaraan?->merk ?? '') . ' ' . ($s->kendaraan?->model ?? 'Kendaraan')) ?: 'Kendaraan Member',
            'plate' => $s->kendaraan?->nomor_polisi ?: 'Tanpa No. Polisi',
            'subtitle' => $s->jenisServis?->nama_servis ?? 'Servis Berkala',
            'date_label' => $dateObj ? $dateObj->translatedFormat('d M Y') . ($jamStr ? ' · ' . $jamStr : '') : '—',
            'sort_ts' => $dateObj?->timestamp ?? ($s->created_at?->timestamp ?? 0),
            'price_label' => $base > 0 ? 'Rp ' . number_format($finalPrice, 0, ',', '.') : 'Estimasi di bengkel',
            'promo_code' => $promo?->kode_promo,
            'status' => $meta['label'],
            'tone' => $meta['tone'],
            'group' => $meta['group'],
            'detail_url' => route('pelanggan.booking-servis.index', ['detail' => $s->getKey()]),
            'can_cancel' => in_array($st, ['menunggu', 'diterima'], true),
            'cancel_url' => route('pelanggan.booking-servis.cancel', $s),
        ]);
    }

    foreach (($washBookings ?? collect()) as $w) {
        $st = strtolower((string) $w->status);
        $meta = $statusMap[$st] ?? ['label' => ucfirst($st), 'tone' => 'slate', 'group' => 'active'];
        $pkgPrice = (float) ($w->paket?->harga ?? 0);
        $txTotal = $w->transaksi?->total !== null ? (float) $w->transaksi->total : $pkgPrice;
        $dateObj = $w->tanggal ? \Illuminate\Support\Carbon::parse($w->tanggal) : null;
        $jamStr = $w->jam ? substr((string) $w->jam, 0, 5) . ' WIB' : '';

        $unified->push([
            'category' => 'cuci',
            'category_label' => 'Car Wash',
            'code' => $w->kode_booking,
            'title' => trim(($w->kendaraan?->merk ?? '') . ' ' . ($w->kendaraan?->model ?? 'Kendaraan')) ?: 'Kendaraan Member',
            'plate' => $w->kendaraan?->nomor_polisi ?: 'Tanpa No. Polisi',
            'subtitle' => $w->paket?->nama_paket ?? 'Paket Car Wash',
            'date_label' => $dateObj ? $dateObj->translatedFormat('d M Y') . ($jamStr ? ' · ' . $jamStr : '') : '—',
            'sort_ts' => $dateObj?->timestamp ?? ($w->created_at?->timestamp ?? 0),
            'price_label' => $txTotal > 0 ? 'Rp ' . number_format($txTotal, 0, ',', '.') : '—',
            'promo_code' => null,
            'status' => $meta['label'],
            'tone' => $meta['tone'],
            'group' => $meta['group'],
            'detail_url' => route('pelanggan.booking-cuci.index', ['detail' => $w->getKey()]),
            'can_cancel' => in_array($st, ['menunggu', 'diterima'], true),
            'cancel_url' => route('pelanggan.booking-cuci.cancel', $w),
        ]);
    }

    foreach (($carBookings ?? collect()) as $c) {
        $st = strtolower((string) $c->status);
        $meta = $statusMap[$st] ?? ['label' => ucfirst($st), 'tone' => 'slate', 'group' => 'active'];
        $dateObj = $c->tanggal_booking ? \Illuminate\Support\Carbon::parse($c->tanggal_booking) : null;
        $expObj = $c->tanggal_expired ? \Illuminate\Support\Carbon::parse($c->tanggal_expired) : null;

        $unified->push([
            'category' => 'mobil',
            'category_label' => 'Booking Mobil',
            'code' => $c->kode_booking,
            'title' => trim(($c->mobil?->merk ?? '') . ' ' . ($c->mobil?->model ?? 'Unit Mobil')) ?: 'Unit Showroom YZA',
            'plate' => $c->mobil?->tahun ? 'Tahun ' . $c->mobil->tahun : 'Unit Showroom',
            'subtitle' => 'Reservasi Unit Kendaraan',
            'date_label' => ($dateObj ? $dateObj->translatedFormat('d M Y') : '—') . ($expObj ? ' · Exp ' . $expObj->translatedFormat('d M Y') : ''),
            'sort_ts' => $dateObj?->timestamp ?? ($c->created_at?->timestamp ?? 0),
            'price_label' => (float) ($c->mobil?->harga_jual ?? 0) > 0 ? 'Rp ' . number_format((float) $c->mobil->harga_jual, 0, ',', '.') : 'Unit Showroom',
            'promo_code' => null,
            'status' => $meta['label'],
            'tone' => $meta['tone'],
            'group' => $meta['group'],
            'detail_url' => route('pelanggan.booking.show', $c),
            'can_cancel' => in_array($st, ['menunggu', 'dikonfirmasi'], true),
            'cancel_url' => route('pelanggan.booking.cancel', $c),
        ]);
    }

    $unified = $unified->sortByDesc('sort_ts')->values();

    $countAll = $unified->count();
    $countServis = $unified->where('category', 'servis')->count();
    $countCuci = $unified->where('category', 'cuci')->count();
    $countMobil = $unified->where('category', 'mobil')->count();
    $countActive = $unified->where('group', 'active')->count();
@endphp

<style>
.bk-hub{max-width:1320px;margin:0 auto;padding:12px clamp(16px,2.5vw,32px) 60px;display:grid;gap:20px;color:#0f172a;font-family:"Manrope","Inter","Segoe UI",sans-serif}
.bk-hero{position:relative;display:flex;align-items:flex-end;justify-content:space-between;gap:24px;flex-wrap:wrap;padding:28px 32px;border-radius:20px;background:linear-gradient(115deg,#0b0f17 0%,#141a24 58%,#1d2534 100%);color:#fff;overflow:hidden;box-shadow:0 18px 42px -18px rgba(15,23,42,.45)}
.bk-hero::after{content:"";position:absolute;right:-60px;top:-60px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(227,29,53,.24),transparent 70%);pointer-events:none}
.bk-hero-copy{position:relative;z-index:1;max-width:620px}
.bk-kicker{display:inline-flex;align-items:center;gap:6px;color:#ff6b7d;font-size:11px;font-weight:800;letter-spacing:.18em;text-transform:uppercase}
.bk-hero h1{margin:8px 0 6px;color:#fff!important;font-size:clamp(26px,3vw,36px)!important;font-weight:850!important;letter-spacing:-.03em!important;line-height:1.1!important}
.bk-hero p{margin:0;color:#cbd5e1!important;font-size:14px!important;line-height:1.55!important}
.bk-hero-actions{position:relative;z-index:1;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.bk-cta-primary,.bk-cta-primary:visited{display:inline-flex!important;align-items:center!important;justify-content:center!important;gap:8px!important;height:42px!important;min-height:42px!important;padding:0 18px!important;border:1px solid #e31d35!important;border-radius:11px!important;background:#e31d35!important;color:#fff!important;font-size:13px!important;font-weight:800!important;text-decoration:none!important;white-space:nowrap;transition:transform .15s,background .15s,box-shadow .15s}
.bk-cta-primary:hover{background:#c81428!important;border-color:#c81428!important;color:#fff!important;transform:translateY(-1px);box-shadow:0 10px 22px rgba(227,29,53,.32)}
.bk-cta-ghost,.bk-cta-ghost:visited{display:inline-flex!important;align-items:center!important;justify-content:center!important;gap:8px!important;height:42px!important;min-height:42px!important;padding:0 16px!important;border:1px solid rgba(255,255,255,.22)!important;border-radius:11px!important;background:rgba(255,255,255,.08)!important;color:#fff!important;font-size:13px!important;font-weight:700!important;text-decoration:none!important;white-space:nowrap;transition:background .15s,border-color .15s,transform .15s}
.bk-cta-ghost:hover{background:rgba(255,255,255,.16)!important;border-color:rgba(255,255,255,.45)!important;color:#fff!important;transform:translateY(-1px)}

/* Alert */
.bk-alert{display:flex;align-items:center;gap:10px;padding:13px 18px;border:1px solid #bbf7d0;border-radius:14px;background:#f0fdf4;color:#166534;font-size:13.5px;font-weight:700}

/* KPI Summary Strip */
.bk-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
.bk-kpi{display:flex;flex-direction:column;align-items:flex-start;gap:4px;padding:18px 20px;border:1px solid #e2e8f0;border-radius:16px;background:#fff;text-align:left;cursor:pointer;transition:border-color .15s,box-shadow .15s,transform .15s}
.bk-kpi:hover{border-color:#cbd5e1;transform:translateY(-2px);box-shadow:0 12px 24px -14px rgba(15,23,42,.12)}
.bk-kpi.is-active{border-color:#0f172a;box-shadow:0 0 0 1px #0f172a}
.bk-kpi-top{display:flex;align-items:center;justify-content:space-between;width:100%;gap:8px}
.bk-kpi span{color:#64748b;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.bk-kpi-dot{width:8px;height:8px;border-radius:50%;background:#e31d35}
.bk-kpi-dot.is-sky{background:#0284c7}
.bk-kpi-dot.is-slate{background:#0f172a}
.bk-kpi-dot.is-amber{background:#d97706}
.bk-kpi strong{color:#0f172a;font-size:28px;font-weight:900;letter-spacing:-.03em;line-height:1.1;margin-top:2px}
.bk-kpi small{color:#64748b;font-size:12px;font-weight:600;text-transform:none!important;letter-spacing:0!important}

/* Filter & Search Bar */
.bk-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;padding:12px 16px;border:1px solid #e2e8f0;border-radius:16px;background:#fff}
.bk-tabs{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
.bk-tab{display:inline-flex;align-items:center;gap:7px;height:36px;min-height:36px!important;padding:0 14px;border:1px solid #e2e8f0;border-radius:999px;background:#f8fafc;color:#475569;font-size:12.5px!important;font-weight:700!important;cursor:pointer;transition:all .15s}
.bk-tab:hover{border-color:#cbd5e1;color:#0f172a}
.bk-tab.is-active{border-color:#0f172a;background:#0f172a;color:#fff}
.bk-tab-cnt{display:inline-grid;place-items:center;min-width:20px;height:20px;padding:0 6px;border-radius:999px;background:rgba(15,23,42,.08);color:inherit;font-size:11px;font-weight:800}
.bk-tab.is-active .bk-tab-cnt{background:rgba(255,255,255,.2)}
.bk-tools-right{display:flex;align-items:center;gap:10px;flex:1 1 280px;justify-content:flex-end;flex-wrap:wrap}
.bk-select{height:38px!important;min-height:38px!important;width:auto!important;padding:0 14px!important;border:1px solid #e2e8f0!important;border-radius:10px!important;background:#f8fafc!important;color:#0f172a!important;font-size:12.5px!important;font-weight:700!important;cursor:pointer}
.bk-search-wrap{position:relative;flex:1 1 220px;max-width:300px}
.bk-search{width:100%!important;height:38px!important;min-height:38px!important;padding:0 14px 0 36px!important;border:1px solid #e2e8f0!important;border-radius:10px!important;background:#f8fafc!important;color:#0f172a!important;font-size:13px!important;font-weight:500!important}
.bk-search:focus{background:#fff!important;border-color:#0f172a!important;outline:none}
.bk-search-ic{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;pointer-events:none}

/* Booking List */
.bk-list{display:grid;gap:12px}
.bk-row{display:grid;grid-template-columns:150px minmax(0,1.4fr) minmax(0,1fr) 175px 206px;align-items:center;gap:16px;padding:18px 20px;border:1px solid #e2e8f0;border-radius:16px;background:#fff;transition:border-color .15s,box-shadow .15s}
.bk-row:hover{border-color:#cbd5e1;box-shadow:0 10px 26px -16px rgba(15,23,42,.16)}
.bk-row[hidden]{display:none!important}
.bk-col-code{display:flex;flex-direction:column;gap:6px;align-items:flex-start}
.bk-cat-pill{display:inline-flex;align-items:center;padding:3px 9px;border-radius:999px;font-size:10.5px;font-weight:800;letter-spacing:.05em;text-transform:uppercase}
.bk-cat-pill[data-cat="servis"]{background:#fff1f2;color:#be123c}
.bk-cat-pill[data-cat="cuci"]{background:#e0f2fe;color:#0369a1}
.bk-cat-pill[data-cat="mobil"]{background:#f1f5f9;color:#0f172a}
.bk-code{font-family:ui-monospace,Consolas,monospace;font-size:12.5px;font-weight:800;color:#334155}
.bk-col-main{min-width:0}
.bk-title{margin:0;color:#0f172a!important;font-size:15.5px!important;font-weight:800!important;line-height:1.25!important}
.bk-sub{margin-top:3px;color:#64748b;font-size:12.5px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.bk-col-meta{display:flex;flex-direction:column;gap:4px;min-width:0}
.bk-date{color:#0f172a;font-size:13px;font-weight:700}
.bk-price{color:#64748b;font-size:12px;font-weight:600}
.bk-promo{display:inline-block;margin-left:6px;padding:1px 6px;border-radius:5px;background:#fef2f2;color:#dc2626;font-size:10.5px;font-weight:800}
.bk-col-status{display:flex;align-items:center;justify-content:flex-start}
.bk-status{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:6px 12px;border-radius:999px;font-size:11.5px;font-weight:800;white-space:nowrap}
.bk-status[data-tone="amber"]{background:#fef3c7;color:#92400e}
.bk-status[data-tone="sky"]{background:#e0f2fe;color:#075985}
.bk-status[data-tone="indigo"]{background:#e0e7ff;color:#3730a3}
.bk-status[data-tone="emerald"]{background:#dcfce7;color:#166534}
.bk-status[data-tone="rose"]{background:#ffe4e6;color:#be123c}
.bk-status[data-tone="slate"]{background:#f1f5f9;color:#475569}
.bk-col-actions{display:flex;align-items:center;gap:8px;justify-content:flex-end}
.bk-btn,.bk-btn:visited{display:inline-flex!important;align-items:center!important;justify-content:center!important;height:36px!important;min-height:36px!important;padding:0 14px!important;border:1px solid #e2e8f0!important;border-radius:10px!important;background:#fff!important;color:#0f172a!important;font-size:12.5px!important;font-weight:800!important;text-decoration:none!important;cursor:pointer;white-space:nowrap;transition:all .15s}
.bk-btn:hover{border-color:#0f172a!important;background:#0f172a!important;color:#fff!important}
.bk-btn-danger,.bk-btn-danger:visited{border-color:#fecdd3!important;background:#fff!important;color:#be123c!important}
.bk-btn-danger:hover{border-color:#e31d35!important;background:#e31d35!important;color:#fff!important}

/* Empty State */
.bk-empty{display:grid;place-items:center;text-align:center;padding:54px 24px;border:1px dashed #cbd5e1;border-radius:18px;background:#fff}
.bk-empty[hidden]{display:none!important}
.bk-empty-icon{display:grid;place-items:center;width:54px;height:54px;margin-bottom:14px;border-radius:14px;background:#fff1f2;color:#e31d35;font-size:24px}
.bk-empty h3{margin:0 0 6px;color:#0f172a!important;font-size:18px!important;font-weight:800!important}
.bk-empty p{margin:0 0 18px;color:#64748b!important;font-size:13.5px!important;max-width:420px}
.bk-empty-actions{display:flex;gap:10px;flex-wrap:wrap;justify-content:center}
.bk-empty-btn,.bk-empty-btn:visited{display:inline-flex!important;align-items:center!important;justify-content:center!important;height:40px!important;min-height:40px!important;padding:0 18px!important;border-radius:10px!important;background:#0f172a!important;color:#fff!important;font-size:13px!important;font-weight:800!important;text-decoration:none!important}
.bk-empty-btn.is-red{background:#e31d35!important}

/* Custom Cancel Confirmation Modal */
.bkc-overlay{position:fixed;inset:0;z-index:9999;display:grid;place-items:center;padding:20px;background:rgba(15,23,42,.62);backdrop-filter:blur(6px)}
.bkc-overlay[hidden]{display:none!important}
.bkc-box{width:min(420px,100%);padding:26px 24px 22px;border:1px solid #e2e8f0;border-radius:20px;background:#fff;box-shadow:0 30px 70px rgba(15,23,42,.35);text-align:center}
.bkc-icon{display:grid;place-items:center;width:52px;height:52px;margin:0 auto 12px;border-radius:50%;background:#fff1f2;color:#e31d35;font-size:24px;font-weight:900}
.bkc-box h3{margin:0 0 6px;color:#0f172a!important;font-size:18px!important;font-weight:800!important}
.bkc-box p{margin:0;color:#64748b!important;font-size:13px!important;line-height:1.5!important}
.bkc-code{display:inline-block;margin-top:10px;padding:4px 10px;border:1px solid #e2e8f0;border-radius:7px;background:#f8fafc;color:#334155;font-family:ui-monospace,Consolas,monospace;font-size:12px;font-weight:800}
.bkc-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:20px}
.bkc-btn{display:inline-flex;align-items:center;justify-content:center;height:42px!important;min-height:42px!important;border-radius:11px;font-size:13px;font-weight:800;cursor:pointer}
.bkc-btn-ghost{border:1px solid #e2e8f0;background:#fff;color:#0f172a}
.bkc-btn-danger{border:1px solid #e31d35;background:#e31d35;color:#fff}

@media(max-width:1024px){
    .bk-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}
    .bk-row{grid-template-columns:1fr 1fr;gap:12px}
    .bk-col-actions{grid-column:1/-1;justify-content:flex-start;padding-top:8px;border-top:1px solid #f1f5f9}
}
@media(max-width:640px){
    .bk-hero{padding:22px 18px}
    .bk-kpis{grid-template-columns:1fr 1fr;gap:10px}
    .bk-row{grid-template-columns:1fr;padding:16px}
    .bk-tools-right{justify-content:stretch}
    .bk-search-wrap{max-width:none}
}
</style>

<div class="bk-hub" data-bk-hub>
    <section class="bk-hero">
        <div class="bk-hero-copy">
            <span class="bk-kicker">Aktivitas Member</span>
            <h1>Booking Saya</h1>
            <p>Pantau seluruh jadwal servis berkala, perawatan car wash, dan reservasi unit mobil Anda dalam satu tampilan terpadu.</p>
        </div>
        <div class="bk-hero-actions">
            <a class="bk-cta-primary" href="{{ route('pelanggan.booking-servis.create') }}">+ Booking Servis</a>
            <a class="bk-cta-ghost" href="{{ route('pelanggan.booking-cuci.create') }}">+ Car Wash</a>
            <a class="bk-cta-ghost" href="{{ route('pelanggan.katalog-mobil.index') }}">Cari Mobil</a>
        </div>
    </section>

    @if(session('success'))
        <div class="bk-alert" role="status">
            <span>✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <section class="bk-kpis" aria-label="Ringkasan total booking">
        <button type="button" class="bk-kpi is-active" data-bk-kpi="all">
            <div class="bk-kpi-top"><span>Total Semua Booking</span><i class="bk-kpi-dot is-slate"></i></div>
            <strong>{{ $countAll }}</strong>
            <small>{{ $countActive }} jadwal sedang aktif</small>
        </button>
        <button type="button" class="bk-kpi" data-bk-kpi="servis">
            <div class="bk-kpi-top"><span>Booking Servis</span><i class="bk-kpi-dot"></i></div>
            <strong>{{ $countServis }}</strong>
            <small>Servis &amp; perawatan bengkel</small>
        </button>
        <button type="button" class="bk-kpi" data-bk-kpi="cuci">
            <div class="bk-kpi-top"><span>Car Wash</span><i class="bk-kpi-dot is-sky"></i></div>
            <strong>{{ $countCuci }}</strong>
            <small>Cuci hidrolik &amp; detailing</small>
        </button>
        <button type="button" class="bk-kpi" data-bk-kpi="mobil">
            <div class="bk-kpi-top"><span>Booking Mobil</span><i class="bk-kpi-dot is-amber"></i></div>
            <strong>{{ $countMobil }}</strong>
            <small>Reservasi unit showroom</small>
        </button>
    </section>

    <div class="bk-toolbar">
        <div class="bk-tabs" role="tablist" aria-label="Filter kategori booking">
            <button type="button" class="bk-tab is-active" data-bk-cat="all">Semua <span class="bk-tab-cnt">{{ $countAll }}</span></button>
            <button type="button" class="bk-tab" data-bk-cat="servis">Servis <span class="bk-tab-cnt">{{ $countServis }}</span></button>
            <button type="button" class="bk-tab" data-bk-cat="cuci">Car Wash <span class="bk-tab-cnt">{{ $countCuci }}</span></button>
            <button type="button" class="bk-tab" data-bk-cat="mobil">Booking Mobil <span class="bk-tab-cnt">{{ $countMobil }}</span></button>
        </div>
        <div class="bk-tools-right">
            <select class="bk-select" data-bk-status aria-label="Filter status">
                <option value="all">Semua Status</option>
                <option value="active">Aktif / Berjalan</option>
                <option value="done">Selesai</option>
                <option value="cancelled">Dibatalkan / Kadaluarsa</option>
            </select>
            <div class="bk-search-wrap">
                <svg class="bk-search-ic" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="search" class="bk-search" data-bk-search placeholder="Cari kode, kendaraan, layanan..." aria-label="Cari booking">
            </div>
        </div>
    </div>

    <div class="bk-list" data-bk-list>
        @foreach($unified as $item)
            <article class="bk-row"
                     data-bk-row
                     data-cat="{{ $item['category'] }}"
                     data-group="{{ $item['group'] }}"
                     data-search="{{ strtolower($item['code'].' '.$item['title'].' '.$item['subtitle'].' '.$item['plate'].' '.$item['category_label']) }}">
                <div class="bk-col-code">
                    <span class="bk-cat-pill" data-cat="{{ $item['category'] }}">{{ $item['category_label'] }}</span>
                    <span class="bk-code">{{ $item['code'] }}</span>
                </div>
                <div class="bk-col-main">
                    <h3 class="bk-title">{{ $item['title'] }}</h3>
                    <div class="bk-sub">{{ $item['subtitle'] }} · {{ $item['plate'] }}</div>
                </div>
                <div class="bk-col-meta">
                    <span class="bk-date">{{ $item['date_label'] }}</span>
                    <span class="bk-price">
                        {{ $item['price_label'] }}
                        @if($item['promo_code'])<span class="bk-promo">{{ $item['promo_code'] }}</span>@endif
                    </span>
                </div>
                <div class="bk-col-status">
                    <span class="bk-status" data-tone="{{ $item['tone'] }}">{{ $item['status'] }}</span>
                </div>
                <div class="bk-col-actions">
                    <a class="bk-btn" href="{{ $item['detail_url'] }}">Lihat Detail</a>
                    @if($item['can_cancel'])
                        <form method="post" action="{{ $item['cancel_url'] }}" data-bkc-form data-bkc-code="{{ $item['code'] }}" data-bkc-type="{{ $item['category_label'] }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="bk-btn bk-btn-danger">Batalkan</button>
                        </form>
                    @endif
                </div>
            </article>
        @endforeach

        <div class="bk-empty" data-bk-empty @if($unified->isNotEmpty()) hidden @endif>
            <div class="bk-empty-icon" aria-hidden="true">📅</div>
            <h3>Belum ada booking yang sesuai</h3>
            <p>Mulai jadwalkan servis berkala, car wash premium, atau reservasi mobil pilihan Anda di YZA Automotive.</p>
            <div class="bk-empty-actions">
                <a class="bk-empty-btn is-red" href="{{ route('pelanggan.booking-servis.create') }}">+ Booking Servis</a>
                <a class="bk-empty-btn" href="{{ route('pelanggan.booking-cuci.create') }}">+ Booking Car Wash</a>
            </div>
        </div>
    </div>
</div>

<div class="bkc-overlay" data-bkc-modal hidden role="alertdialog" aria-modal="true" aria-labelledby="bkc-title">
    <div class="bkc-box">
        <div class="bkc-icon" aria-hidden="true">!</div>
        <h3 id="bkc-title">Batalkan Booking?</h3>
        <p data-bkc-desc>Jadwal booking akan dibatalkan dan slot dilepas. Tindakan ini tidak dapat dikembalikan.</p>
        <span class="bkc-code" data-bkc-code hidden></span>
        <div class="bkc-actions">
            <button type="button" class="bkc-btn bkc-btn-ghost" data-bkc-no>Tidak, Kembali</button>
            <button type="button" class="bkc-btn bkc-btn-danger" data-bkc-yes>Ya, Batalkan</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const hub = document.querySelector('[data-bk-hub]');
    if (!hub) return;

    let activeCat = 'all';
    let activeGroup = 'all';
    const rows = [...hub.querySelectorAll('[data-bk-row]')];
    const emptyEl = hub.querySelector('[data-bk-empty]');
    const searchEl = hub.querySelector('[data-bk-search]');
    const statusEl = hub.querySelector('[data-bk-status]');
    const catTabs = [...hub.querySelectorAll('[data-bk-cat]')];
    const kpiCards = [...hub.querySelectorAll('[data-bk-kpi]')];

    const applyFilter = () => {
        const q = (searchEl?.value || '').trim().toLowerCase();
        let visible = 0;
        rows.forEach(row => {
            const matchCat = activeCat === 'all' || row.dataset.cat === activeCat;
            const matchGroup = activeGroup === 'all' || row.dataset.group === activeGroup;
            const matchQ = !q || (row.dataset.search || '').includes(q);
            const show = matchCat && matchGroup && matchQ;
            row.hidden = !show;
            if (show) visible += 1;
        });
        if (emptyEl) emptyEl.hidden = visible > 0;
    };

    const setCat = (cat) => {
        activeCat = cat;
        catTabs.forEach(t => t.classList.toggle('is-active', t.dataset.bkCat === cat));
        kpiCards.forEach(k => k.classList.toggle('is-active', k.dataset.bkKpi === cat));
        applyFilter();
    };

    catTabs.forEach(t => t.addEventListener('click', () => setCat(t.dataset.bkCat)));
    kpiCards.forEach(k => k.addEventListener('click', () => setCat(k.dataset.bkKpi)));
    statusEl?.addEventListener('change', () => { activeGroup = statusEl.value; applyFilter(); });
    searchEl?.addEventListener('input', applyFilter);

    // Custom Cancel Confirmation Modal
    const modal = document.querySelector('[data-bkc-modal]');
    if (modal && modal.parentElement !== document.body) document.body.appendChild(modal);
    let targetForm = null;
    const closeModal = () => { if (modal) modal.hidden = true; targetForm = null; };
    document.addEventListener('submit', e => {
        const form = e.target.closest('form[data-bkc-form]');
        if (!form || !modal || form.dataset.bkcOk === '1') return;
        e.preventDefault();
        targetForm = form;
        const codeEl = modal.querySelector('[data-bkc-code]');
        const descEl = modal.querySelector('[data-bkc-desc]');
        if (codeEl) {
            codeEl.textContent = form.dataset.bkcCode || '';
            codeEl.hidden = !form.dataset.bkcCode;
        }
        if (descEl) {
            const kind = form.dataset.bkcType || 'booking';
            descEl.textContent = `${kind} akan dibatalkan dan slot dilepas. Tindakan ini tidak dapat dikembalikan.`;
        }
        modal.hidden = false;
        modal.querySelector('[data-bkc-no]')?.focus();
    });
    modal?.querySelector('[data-bkc-no]')?.addEventListener('click', closeModal);
    modal?.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    modal?.querySelector('[data-bkc-yes]')?.addEventListener('click', () => {
        if (!targetForm) return;
        targetForm.dataset.bkcOk = '1';
        modal.querySelector('[data-bkc-yes]').disabled = true;
        targetForm.submit();
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modal && !modal.hidden) closeModal();
    });
})();
</script>
@endpush
