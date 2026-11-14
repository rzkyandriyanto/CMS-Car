@extends('layouts.app')

@section('title', 'Car Wash Saya · YZA Automotive')

@php
    $statusMeta = [
        'menunggu' => ['label' => 'Menunggu Konfirmasi YZA', 'short' => 'Menunggu', 'tone' => 'amber', 'group' => 'active', 'stage' => 1],
        'diterima' => ['label' => 'Jadwal Dikonfirmasi', 'short' => 'Dikonfirmasi', 'tone' => 'blue', 'group' => 'active', 'stage' => 2],
        'proses' => ['label' => 'Sedang Dicuci', 'short' => 'Sedang Dicuci', 'tone' => 'indigo', 'group' => 'active', 'stage' => 3],
        'selesai' => ['label' => 'Pencucian Selesai', 'short' => 'Selesai', 'tone' => 'emerald', 'group' => 'completed', 'stage' => 4],
        'dibatalkan' => ['label' => 'Booking Dibatalkan', 'short' => 'Dibatalkan', 'tone' => 'slate', 'group' => 'cancelled', 'stage' => 1],
    ];

    $mapWashItem = function ($b) use ($statusMeta) {
        $st = $b->status;
        $meta = $statusMeta[$st] ?? ['label' => ucfirst($st), 'short' => ucfirst($st), 'tone' => 'slate', 'group' => 'active', 'stage' => 1];
        $dur = max(15, (int) ($b->paket?->estimasi_menit ?? 45));
        $durLabel = $dur >= 60 ? '± ' . intdiv($dur, 60) . ' jam' . ($dur % 60 ? ' ' . ($dur % 60) . ' mnt' : '') : '± ' . $dur . ' menit';
        $timeRaw = substr((string) $b->jam, 0, 5);
        $basePrice = (float) ($b->harga ?? $b->paket?->harga ?? 0);
        $tx = $b->transaksi;
        $disc = (float) ($tx?->diskon ?? 0);
        $finalPrice = $tx ? (float) $tx->total : max(0, $basePrice - $disc);

        return [
            'id' => (string) $b->getKey(),
            'code' => $b->kode_booking,
            'status' => $st,
            'status_label' => $meta['label'],
            'status_short' => $meta['short'],
            'tone' => $meta['tone'],
            'group' => $meta['group'],
            'stage' => $meta['stage'],
            'vehicle' => trim(($b->kendaraan?->merk ?: '') . ' ' . ($b->kendaraan?->model ?: '')) ?: 'Belum diisi',
            'plate' => $b->kendaraan?->nomor_polisi ?: 'Nomor polisi belum diisi',
            'mileage' => number_format((int) ($b->kendaraan?->kilometer ?? 0), 0, ',', '.') . ' km',
            'package' => $b->paket?->nama_paket ?: 'Paket Car Wash',
            'package_desc' => $b->paket?->deskripsi ?: 'Perawatan eksterior & interior standar studio YZA Car Care.',
            'duration' => $durLabel,
            'date' => $b->tanggal?->translatedFormat('l, d F Y') ?: '—',
            'time' => $timeRaw . ' WIB',
            'submitted_at' => $b->created_at?->translatedFormat('d F Y, H:i') ?: 'Baru saja',
            'staff' => $b->petugas?->nama_lengkap ?: 'Ditentukan saat kedatangan',
            'note' => $b->catatan ?: 'Tidak ada catatan khusus',
            'price_base' => 'Rp ' . number_format($basePrice, 0, ',', '.'),
            'has_discount' => $disc > 0,
            'price_discount' => $disc > 0 ? '- Rp ' . number_format($disc, 0, ',', '.') : 'Tidak Ada',
            'price_total' => 'Rp ' . number_format($finalPrice, 0, ',', '.'),
            'price_strike' => $disc > 0 ? 'Rp ' . number_format($basePrice, 0, ',', '.') : '',
            'can_cancel' => in_array($st, ['menunggu', 'diterima'], true),
            'cancel_url' => route('pelanggan.booking-cuci.cancel', $b),
        ];
    };

    $collection = $rows->getCollection();
    $bookingsMap = $collection->mapWithKeys(fn($b) => [(string) $b->getKey() => $mapWashItem($b)]);
    $activeBookingData = isset($activeBooking) && $activeBooking ? $mapWashItem($activeBooking) : null;
    if ($activeBookingData && !$bookingsMap->has($activeBookingData['id'])) {
        $bookingsMap->put($activeBookingData['id'], $activeBookingData);
    }
    $initialModalItem = $activeBookingData ?: $bookingsMap->first();

    $countAll = $collection->count();
    $countActive = $collection->whereIn('status', ['menunggu', 'diterima', 'proses'])->count();
    $countDone = $collection->where('status', 'selesai')->count();
    $countCancelled = $collection->where('status', 'dibatalkan')->count();
    $packages = $packages ?? collect();
@endphp

@push('styles')
<style>
.member-horizontal-shell .member-main-content:has(.cw-page){padding:0!important;max-width:none!important}
.cw-page{width:min(96vw,1640px);margin:0 auto;padding:0 0 64px;color:#0f172a}

/* Hero Banner */
.cw-hero{position:relative;min-height:224px;display:flex;justify-content:space-between;align-items:flex-end;gap:24px;padding:38px clamp(24px,4vw,56px) 32px;margin-bottom:22px;border-radius:0 0 22px 22px;overflow:hidden;color:#fff;background:linear-gradient(90deg,rgba(6,11,19,.97) 0%,rgba(10,16,26,.86) 48%,rgba(10,16,26,.28) 100%),url('/assets/images/member/member-benefit-carwash-v2.png') right center/cover;border-bottom:3px solid #e31d35;box-shadow:0 16px 36px -14px rgba(15,23,42,.24)}
.cw-hero-copy{position:relative;z-index:1;max-width:740px}
.cw-eyebrow{display:inline-flex;align-items:center;gap:7px;margin-bottom:8px;color:#ff2d46;font-size:11px;font-weight:900;letter-spacing:.18em;text-transform:uppercase}
.cw-eyebrow::before{content:'';width:6px;height:6px;border-radius:999px;background:#ff2d46;box-shadow:0 0 10px #ff2d46}
.cw-hero h1{margin:0;color:#fff;font-size:clamp(32px,3.6vw,52px);font-weight:900;letter-spacing:-.04em;line-height:1.05}
.cw-hero p{margin:8px 0 14px;color:#cbd5e1;font-size:14.5px;line-height:1.5}
.cw-hero-pills{display:flex;flex-wrap:wrap;gap:8px}
.cw-hero-pill{display:inline-flex;align-items:center;gap:6px;padding:5px 11px;border-radius:999px;border:1px solid rgba(255,255,255,.16);background:rgba(15,23,42,.55);backdrop-filter:blur(6px);color:#e2e8f0;font-size:11.5px;font-weight:700}
.cw-hero-pill svg{width:13px;height:13px;color:#ff4d63}
.cw-hero-actions{position:relative;z-index:1;display:flex;align-items:center;gap:10px;flex-shrink:0}
.cw-create-btn,.cw-create-btn:visited{display:inline-flex;align-items:center;gap:8px;min-height:46px;padding:0 22px;border:1px solid rgba(255,255,255,.22);border-radius:11px;background:#e31d35!important;color:#fff!important;font-size:13.5px;font-weight:800;text-decoration:none!important;box-shadow:0 10px 24px -6px rgba(0,0,0,.45),0 6px 16px rgba(227,29,53,.4);transition:transform .16s ease,background .16s ease,box-shadow .16s ease}
.cw-create-btn:hover,.cw-create-btn:focus-visible{background:#be123c!important;color:#fff!important;transform:translateY(-1px);box-shadow:0 14px 28px -6px rgba(0,0,0,.5),0 8px 20px rgba(227,29,53,.55)}

/* Alerts */
.cw-alert{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px;padding:13px 18px;border-radius:12px;font-size:13.5px;font-weight:700}
.cw-alert.success{background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d}
.cw-alert.error{background:#fff1f2;border:1px solid #fecdd3;color:#be123c}

/* Quick Package Showcase Strip */
.cw-packages-strip{margin-bottom:20px;padding:18px 20px;border:1px solid #e2e8f0;border-radius:16px;background:#fff;box-shadow:0 6px 20px -10px rgba(15,23,42,.04)}
.cw-strip-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px}
.cw-strip-title{display:flex;align-items:center;gap:10px}
.cw-strip-title h2{margin:0;color:#0f172a;font-size:15px;font-weight:800;letter-spacing:-.01em}
.cw-strip-badge{padding:3px 9px;border-radius:999px;background:#fff1f2;border:1px solid #fecdd3;color:#e31d35;font-size:10.5px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
.cw-strip-sub{margin:0;color:#64748b;font-size:12.5px}
.cw-pkg-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px}
.cw-pkg-card,.cw-pkg-card:visited{position:relative;display:flex;flex-direction:column;justify-content:space-between;gap:10px;padding:15px 16px 14px 18px;border:1px solid #e2e8f0;border-radius:13px;background:#fcfcfd;color:#0f172a!important;text-decoration:none!important;overflow:hidden;transition:transform .16s ease,border-color .16s ease,background .16s ease,box-shadow .16s ease}
.cw-pkg-card::before{content:'';position:absolute;left:0;top:12px;bottom:12px;width:3px;border-radius:0 3px 3px 0;background:#cbd5e1;transition:all .16s ease}
.cw-pkg-card:hover{border-color:#cbd5e1;background:#fff;transform:translateY(-2px);box-shadow:0 12px 24px -10px rgba(15,23,42,.08)}
.cw-pkg-card:hover::before{top:0;bottom:0;width:4px;border-radius:0;background:#e31d35}
.cw-pkg-top{display:flex;align-items:center;justify-content:space-between;gap:8px}
.cw-pkg-tier{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:10px;font-weight:800;letter-spacing:.05em;padding:2px 7px;border-radius:5px;background:#f1f5f9;color:#475569;text-transform:uppercase}
.cw-pkg-dur{font-size:11px;font-weight:700;color:#64748b}
.cw-pkg-name{display:block;margin-top:4px;color:#0f172a;font-size:15px;font-weight:800;letter-spacing:-.01em}
.cw-pkg-desc{margin:3px 0 0;color:#64748b;font-size:12px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.cw-pkg-foot{display:flex;align-items:center;justify-content:space-between;gap:8px;padding-top:10px;border-top:1px dashed #e2e8f0}
.cw-pkg-price{color:#e31d35;font-size:14.5px;font-weight:800;font-variant-numeric:tabular-nums}
.cw-pkg-cta{font-size:11.5px;font-weight:800;color:#0f172a;display:inline-flex;align-items:center;gap:4px;transition:color .15s ease,transform .15s ease}
.cw-pkg-card:hover .cw-pkg-cta{color:#e31d35;transform:translateX(2px)}

/* Toolbar */
.cw-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:16px;padding:12px 16px;border:1px solid #e2e8f0;border-radius:14px;background:#fff;box-shadow:0 4px 16px rgba(15,23,42,.03)}
.cw-tabs{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
.cw-tab{display:inline-flex;align-items:center;gap:7px;height:36px;padding:0 13px;border:1px solid transparent;border-radius:9px;background:transparent;color:#64748b;font:inherit;font-size:12.5px;font-weight:700;cursor:pointer;transition:all .15s ease}
.cw-tab:hover{color:#0f172a;background:#f8fafc}
.cw-tab.is-active{border-color:#e2e8f0;background:#0f172a;color:#fff}
.cw-tab-count{display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:19px;padding:0 6px;border-radius:999px;background:#f1f5f9;color:#475569;font-size:10.5px;font-weight:800}
.cw-tab.is-active .cw-tab-count{background:rgba(255,255,255,.18);color:#fff}
.cw-search{position:relative;min-width:260px;flex:0 1 320px}
.cw-search svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none}
.cw-search input{width:100%;height:38px;padding:0 12px 0 35px;border:1px solid #e2e8f0;border-radius:9px;background:#f8fafc;color:#0f172a;font:inherit;font-size:12.5px;outline:0;transition:border-color .15s ease,background .15s ease}
.cw-search input:focus{border-color:#cbd5e1;background:#fff}

/* Table Card */
.cw-card{border:1px solid #e2e8f0;border-radius:16px;background:#fff;box-shadow:0 10px 28px -12px rgba(15,23,42,.05);overflow:hidden}
.cw-table-wrap{width:100%;overflow-x:auto}
.cw-table{width:100%;border-collapse:collapse;text-align:left}
.cw-table th{padding:13px 20px;border-bottom:1px solid #f1f5f9;background:#f8fafc;color:#64748b;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;white-space:nowrap}
.cw-table td{padding:16px 20px;border-bottom:1px solid #f1f5f9;vertical-align:middle;font-size:13.5px;color:#0f172a}
.cw-table tbody tr{transition:background .14s ease}
.cw-table tbody tr:hover{background:#fcfdfe}
.cw-table tbody tr:last-child td{border-bottom:0}

.cw-code-cell{display:grid;gap:4px;justify-items:start}
.cw-code-badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:7px;border:1px solid #e2e8f0;background:#f8fafc;color:#0f172a;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11.5px;font-weight:800;letter-spacing:.03em}
.cw-subtext{color:#64748b;font-size:11.5px;line-height:1.35}

.cw-vehicle-cell,.cw-pkg-cell,.cw-schedule-cell{display:grid;gap:3px}
.cw-vehicle-cell strong,.cw-pkg-cell strong,.cw-schedule-cell strong{font-size:13.5px;font-weight:800;color:#0f172a}
.cw-price-cell{font-weight:800;color:#0f172a;font-variant-numeric:tabular-nums}

.cw-status{display:inline-flex;align-items:center;gap:6px;padding:5px 11px;border-radius:999px;font-size:11.5px;font-weight:800;white-space:nowrap;border:1px solid transparent}
.cw-status::before{content:'';width:6px;height:6px;border-radius:999px;background:currentColor}
.cw-status[data-tone="amber"]{background:#fffbeb;border-color:#fde68a;color:#b45309}
.cw-status[data-tone="blue"]{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8}
.cw-status[data-tone="indigo"]{background:#eef2ff;border-color:#c7d2fe;color:#4338ca}
.cw-status[data-tone="emerald"]{background:#ecfdf5;border-color:#bbf7d0;color:#047857}
.cw-status[data-tone="slate"]{background:#f8fafc;border-color:#e2e8f0;color:#64748b}

.cw-actions{display:flex;align-items:center;gap:8px;white-space:nowrap}
.cw-btn,.cw-btn:visited{display:inline-flex;align-items:center;justify-content:center;gap:6px;height:34px;padding:0 13px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;color:#0f172a!important;font:inherit;font-size:12px;font-weight:700;cursor:pointer;text-decoration:none!important;transition:all .15s ease}
.cw-btn:hover{border-color:#0f172a;background:#0f172a;color:#fff!important}
.cw-btn-danger,.cw-btn-danger:visited{border-color:#fecdd3;background:#fff;color:#be123c!important}
.cw-btn-danger:hover{border-color:#e31d35;background:#fff1f2;color:#be123c!important}

/* Editorial Empty State */
.cw-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:48px 24px;text-align:center}
.cw-empty-icon{display:grid;place-items:center;width:58px;height:58px;margin-bottom:14px;border-radius:16px;background:linear-gradient(135deg,#fff1f2 0%,#ffe4e6 100%);border:1px solid #fecdd3;color:#e31d35;box-shadow:0 10px 24px -8px rgba(227,29,53,.22)}
.cw-empty-icon svg{width:28px;height:28px}
.cw-empty h3{margin:0 0 6px;color:#0f172a;font-size:18px;font-weight:800;letter-spacing:-.02em}
.cw-empty p{margin:0 0 20px;max-width:460px;color:#64748b;font-size:13.5px;line-height:1.55}
.cw-empty-steps{display:flex;flex-wrap:wrap;justify-content:center;gap:10px;margin-bottom:22px}
.cw-empty-step{display:inline-flex;align-items:center;gap:8px;padding:7px 13px;border-radius:999px;border:1px solid #e2e8f0;background:#f8fafc;color:#334155;font-size:12px;font-weight:700}
.cw-empty-step b{display:inline-grid;place-items:center;width:20px;height:20px;border-radius:50%;background:#0f172a;color:#fff;font-size:10px;font-weight:800}

/* Detail Modal */
.cw-modal-overlay{position:fixed;inset:0;z-index:1200;display:grid;place-items:center;padding:20px;background:rgba(15,23,42,.58);backdrop-filter:blur(4px)}
.cw-modal-overlay[hidden]{display:none!important}
.cw-modal{width:min(680px,100%);max-height:90vh;display:flex;flex-direction:column;border:1px solid #e2e8f0;border-radius:20px;background:#fff;box-shadow:0 28px 64px -12px rgba(15,23,42,.28);overflow:hidden}
.cw-modal-hero{position:relative;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:22px 26px;color:#fff;background:linear-gradient(90deg,rgba(6,11,19,.97) 0%,rgba(12,18,28,.88) 52%,rgba(12,18,28,.35) 100%),url('/assets/images/member/member-benefit-carwash-v2.png') right center/cover;border-bottom:2px solid #e31d35}
.cw-modal-hero-left{display:flex;align-items:center;gap:16px;min-width:0}
.cw-modal-check{display:grid;place-items:center;width:48px;height:48px;flex-shrink:0;border-radius:50%;border:3px solid rgba(255,255,255,.32);background:#10b981;color:#fff;font-size:22px;font-weight:900;box-shadow:0 0 0 6px rgba(16,185,129,.16)}
.cw-modal-hero-copy h2{margin:0;color:#fff;font-size:21px;font-weight:900;letter-spacing:-.02em}
.cw-modal-hero-copy p{margin:3px 0 8px;color:#cbd5e1;font-size:12.5px}
.cw-modal-code-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.cw-modal-code{display:inline-flex;padding:4px 10px;border:1px solid #ff2d46;border-radius:7px;background:rgba(15,23,42,.65);color:#ff4d63;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11.5px;font-weight:800;letter-spacing:.06em}
.cw-modal-close{display:grid;place-items:center;width:34px;height:34px;flex-shrink:0;align-self:flex-start;border:1px solid rgba(255,255,255,.22);border-radius:9px;background:rgba(15,23,42,.45);color:#fff;font-size:20px;line-height:1;cursor:pointer}
.cw-modal-close:hover{border-color:#fff;background:rgba(255,255,255,.15)}

.cw-modal-body{padding:20px 24px;overflow-y:auto;display:grid;gap:16px}
.cw-timeline{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;padding:12px 14px;border:1px solid #e2e8f0;border-radius:13px;background:#f8fafc}
.cw-timeline-step{display:flex;align-items:center;gap:8px;min-width:0}
.cw-timeline-dot{display:grid;place-items:center;width:24px;height:24px;flex-shrink:0;border-radius:50%;border:1.5px solid #cbd5e1;background:#fff;color:#64748b;font-size:10.5px;font-weight:800}
.cw-timeline-step.is-done .cw-timeline-dot{border-color:#e31d35;background:#e31d35;color:#fff}
.cw-timeline-step.is-current .cw-timeline-dot{border-color:#f59e0b;background:#f59e0b;color:#fff}
.cw-timeline-copy{display:grid;min-width:0}
.cw-timeline-copy strong{color:#0f172a;font-size:11.5px;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cw-timeline-copy small{color:#64748b;font-size:10.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

.cw-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.cw-detail-box{padding:15px 16px;border:1px solid #e2e8f0;border-radius:13px;background:#fff}
.cw-detail-box h3{margin:0 0 10px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;color:#e31d35;font-size:11.5px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
.cw-dl{display:grid;gap:8px;margin:0}
.cw-dl div{display:flex;align-items:baseline;justify-content:space-between;gap:12px;font-size:12.5px}
.cw-dl dt{color:#64748b;font-weight:500}
.cw-dl dd{margin:0;color:#0f172a;font-weight:700;text-align:right;overflow-wrap:anywhere}

.cw-pricing-box{padding:14px 16px;border:1px solid #e2e8f0;border-radius:13px;background:#f8fafc;display:grid;gap:7px}
.cw-price-row{display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:12.5px}
.cw-price-row[hidden]{display:none!important}
.cw-price-row span{color:#64748b;font-weight:600}
.cw-price-row strong{color:#0f172a;font-weight:700}
.cw-price-row.is-discount span,.cw-price-row.is-discount strong{color:#059669;font-weight:800}
.cw-price-total{display:flex;align-items:baseline;justify-content:space-between;gap:10px;padding-top:8px;margin-top:2px;border-top:1px dashed #cbd5e1}
.cw-price-total>span{color:#0f172a;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
.cw-price-total-val{display:flex;align-items:baseline;gap:7px}
.cw-price-total-val del{color:#94a3b8;font-size:11.5px}
.cw-price-total-val strong{color:#e31d35;font-size:15.5px;font-weight:800}

.cw-modal-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 24px;border-top:1px solid #f1f5f9;background:#f8fafc}
.cw-modal-foot-right{display:flex;align-items:center;gap:8px;margin-left:auto}

@media(max-width:760px){
  .cw-hero{flex-direction:column;align-items:flex-start;padding:28px 20px 24px}
  .cw-detail-grid,.cw-timeline{grid-template-columns:repeat(2,1fr)}
  .cw-search{flex:1 1 100%}
}
@media(max-width:520px){
  .cw-detail-grid,.cw-timeline{grid-template-columns:1fr}
}
    .cxc-overlay{position:fixed;inset:0;z-index:1400;display:grid;place-items:center;padding:20px;background:rgba(15,23,42,.62);backdrop-filter:blur(6px)}
    .cxc-overlay[hidden]{display:none!important}
    .cxc-box{width:min(420px,100%);padding:28px 26px 22px;border:1px solid #e2e8f0;border-radius:20px;background:#fff;box-shadow:0 30px 70px rgba(15,23,42,.35);text-align:center;animation:cxc-in .18s ease-out}
    @keyframes cxc-in{from{opacity:0;transform:translateY(10px) scale(.97)}to{opacity:1;transform:none}}
    .cxc-icon{display:grid;place-items:center;width:56px;height:56px;margin:0 auto 14px;border-radius:50%;background:#fff1f2;color:#e31d35;font-size:26px;font-weight:900}
    .cxc-box h3{margin:0 0 6px;color:#0f172a;font-size:19px;font-weight:900;letter-spacing:-.02em}
    .cxc-box p{margin:0;color:#64748b;font-size:13.5px;line-height:1.55}
    .cxc-code{display:inline-block;margin-top:10px;padding:4px 10px;border:1px solid #e2e8f0;border-radius:7px;background:#f8fafc;color:#334155;font-family:ui-monospace,Consolas,monospace;font-size:12px;font-weight:800}
    .cxc-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:22px}
    .cxc-btn{display:inline-flex;align-items:center;justify-content:center;min-height:44px;border-radius:12px;font-size:13.5px;font-weight:800;cursor:pointer;font-family:inherit}
    .cxc-btn-ghost{border:1px solid #e2e8f0;background:#fff;color:#0f172a}
    .cxc-btn-ghost:hover{background:#f8fafc}
    .cxc-btn-danger{border:1px solid #e31d35;background:#e31d35;color:#fff;box-shadow:0 10px 22px rgba(227,29,53,.25)}
    .cxc-btn-danger:hover{background:#c81428}
</style>
@endpush

@section('content')
<div class="cw-page" data-cw-page>
    <header class="cw-hero">
        <div class="cw-hero-copy">
            <span class="cw-eyebrow">YZA CAR CARE STUDIO</span>
            <h1>Car Wash Saya</h1>
            <p>Perawatan eksterior &amp; interior berstandar studio detailing untuk menjaga kilau terbaik kendaraan Anda.</p>
            <div class="cw-hero-pills">
                <span class="cw-hero-pill">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                    pH-Neutral Snow Foam
                </span>
                <span class="cw-hero-pill">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    Paint-Safe Microfiber
                </span>
                <span class="cw-hero-pill">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Senin – Jumat · 08:00 – 17:00 WIB
                </span>
            </div>
        </div>
        <div class="cw-hero-actions">
            <a class="cw-create-btn" href="{{ route('pelanggan.booking-cuci.create') }}">+ Booking Car Wash</a>
        </div>
    </header>

    @if(session('success'))
        <div class="cw-alert success" role="status">
            <span>✓ {{ session('success') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="cw-alert error" role="alert">
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    @if($packages->isNotEmpty())
        <section class="cw-packages-strip" aria-label="Pilihan Paket Car Wash">
            <div class="cw-strip-head">
                <div class="cw-strip-title">
                    <h2>Paket Perawatan YZA Car Care</h2>
                    <span class="cw-strip-badge">Pilih Cepat</span>
                </div>
                <p class="cw-strip-sub">Klik paket di bawah untuk langsung menjadwalkan pencucian kendaraan Anda.</p>
            </div>
            <div class="cw-pkg-grid">
                @foreach($packages as $idx => $pkg)
                    @php
                        $tiers = ['EXPRESS CARE', 'SIGNATURE WASH', 'ULTIMATE DETAIL', 'CERAMIC GLOSS'];
                        $tierLabel = $tiers[$idx % count($tiers)];
                        $durMin = max(15, (int) ($pkg->estimasi_menit ?? 45));
                    @endphp
                    <a class="cw-pkg-card" href="{{ route('pelanggan.booking-cuci.create', ['paket' => $pkg->getKey()]) }}">
                        <div>
                            <div class="cw-pkg-top">
                                <span class="cw-pkg-tier">{{ $tierLabel }}</span>
                                <span class="cw-pkg-dur">± {{ $durMin }} menit</span>
                            </div>
                            <strong class="cw-pkg-name">{{ $pkg->nama_paket }}</strong>
                            <p class="cw-pkg-desc">{{ $pkg->deskripsi ?: 'Pencucian hidrolik menyeluruh, vakum interior, dan semir ban premium.' }}</p>
                        </div>
                        <div class="cw-pkg-foot">
                            <span class="cw-pkg-price">Rp {{ number_format((float) $pkg->harga, 0, ',', '.') }}</span>
                            <span class="cw-pkg-cta">Pilih Paket →</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <div class="cw-toolbar">
        <div class="cw-tabs" role="tablist" aria-label="Filter status booking car wash">
            <button type="button" class="cw-tab is-active" data-cw-filter="all">Semua <span class="cw-tab-count">{{ $countAll }}</span></button>
            <button type="button" class="cw-tab" data-cw-filter="active">Menunggu &amp; Proses <span class="cw-tab-count">{{ $countActive }}</span></button>
            <button type="button" class="cw-tab" data-cw-filter="completed">Selesai <span class="cw-tab-count">{{ $countDone }}</span></button>
            <button type="button" class="cw-tab" data-cw-filter="cancelled">Dibatalkan <span class="cw-tab-count">{{ $countCancelled }}</span></button>
        </div>
        <div class="cw-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="search" placeholder="Cari kode BCW, kendaraan, atau paket..." data-cw-search>
        </div>
    </div>

    <div class="cw-card">
        <div class="cw-table-wrap">
            <table class="cw-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Kendaraan</th>
                        <th>Paket</th>
                        <th>Jadwal</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody data-cw-tbody>
                    @forelse($rows as $b)
                        @php $item = $bookingsMap[(string) $b->getKey()]; @endphp
                        <tr data-cw-row data-group="{{ $item['group'] }}" data-search="{{ strtolower($item['code'] . ' ' . $item['vehicle'] . ' ' . $item['plate'] . ' ' . $item['package'] . ' ' . $item['status_label']) }}">
                            <td>
                                <div class="cw-code-cell">
                                    <span class="cw-code-badge">{{ $item['code'] }}</span>
                                    <span class="cw-subtext">Diajukan {{ $item['submitted_at'] }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="cw-vehicle-cell">
                                    <strong>{{ $item['vehicle'] }}</strong>
                                    <span class="cw-subtext">{{ $item['plate'] }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="cw-pkg-cell">
                                    <strong>{{ $item['package'] }}</strong>
                                    <span class="cw-subtext">{{ $item['duration'] }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="cw-schedule-cell">
                                    <strong>{{ $item['date'] }}</strong>
                                    <span class="cw-subtext">{{ $item['time'] }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="cw-price-cell">{{ $item['price_total'] }}</span>
                            </td>
                            <td>
                                <span class="cw-status" data-tone="{{ $item['tone'] }}">{{ $item['status_short'] }}</span>
                            </td>
                            <td>
                                <div class="cw-actions">
                                    <button type="button" class="cw-btn" data-open-cw-detail="{{ $item['id'] }}">Detail</button>
                                    @if($item['can_cancel'])
                                        <form method="post" action="{{ $item['cancel_url'] }}" data-cxc-form data-cxc-code="{{ $item['code'] }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="cw-btn cw-btn-danger">Batal</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="cw-empty">
                                    <span class="cw-empty-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/><path d="M9.5 14.5c.5 1.2 1.8 2 3.2 2"/></svg>
                                    </span>
                                    <h3>BELUM ADA BOOKING CAR WASH</h3>
                                    <p>Jadwalkan perawatan kendaraan Anda bersama YZA Car Care. Pilih paket pencucian di atas atau buat jadwal baru sesuai waktu luang Anda.</p>
                                    <div class="cw-empty-steps">
                                        <span class="cw-empty-step"><b>1</b> Pilih Kendaraan &amp; Paket</span>
                                        <span class="cw-empty-step"><b>2</b> Tentukan Tanggal &amp; Jam</span>
                                        <span class="cw-empty-step"><b>3</b> Konfirmasi Tanpa Antre</span>
                                    </div>
                                    <a class="cw-create-btn" href="{{ route('pelanggan.booking-cuci.create') }}">+ Booking Car Wash Sekarang</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rows->hasPages())
            <div style="padding:14px 20px;border-top:1px solid #f1f5f9;">
                {{ $rows->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Detail Booking Car Wash --}}
    <div class="cw-modal-overlay" data-cw-detail-modal {{ $activeBookingData ? '' : 'hidden' }} role="dialog" aria-modal="true" aria-labelledby="cw-modal-title">
        <div class="cw-modal">
            <header class="cw-modal-hero">
                <div class="cw-modal-hero-left">
                    <span class="cw-modal-check" aria-hidden="true">✓</span>
                    <div class="cw-modal-hero-copy">
                        <h2 id="cw-modal-title" data-cw-modal-heading>{{ session('success') && $activeBookingData ? 'Booking Car Wash Berhasil Diajukan' : 'Detail Booking Car Wash' }}</h2>
                        <p>Jadwal perawatan kendaraan Anda bersama YZA Car Care Studio.</p>
                        <div class="cw-modal-code-row">
                            <span class="cw-modal-code" data-cw-modal-code>{{ $initialModalItem['code'] ?? '—' }}</span>
                            <span class="cw-status" data-cw-modal-status data-tone="{{ $initialModalItem['tone'] ?? 'amber' }}">{{ $initialModalItem['status_label'] ?? 'Menunggu Konfirmasi YZA' }}</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="cw-modal-close" data-close-cw-detail aria-label="Tutup modal">&times;</button>
            </header>

            <div class="cw-modal-body">
                <div class="cw-timeline" aria-label="Tahapan status car wash">
                    <div class="cw-timeline-step is-done">
                        <span class="cw-timeline-dot">✓</span>
                        <div class="cw-timeline-copy">
                            <strong>Diajukan</strong>
                            <small data-cw-modal-submitted>{{ $initialModalItem['submitted_at'] ?? '—' }}</small>
                        </div>
                    </div>
                    <div class="cw-timeline-step {{ ($initialModalItem['stage'] ?? 1) === 1 ? 'is-current' : 'is-done' }}" data-cw-step-2>
                        <span class="cw-timeline-dot">02</span>
                        <div class="cw-timeline-copy">
                            <strong>Dikonfirmasi</strong>
                            <small>Tim YZA Car Care</small>
                        </div>
                    </div>
                    <div class="cw-timeline-step {{ ($initialModalItem['stage'] ?? 1) === 3 ? 'is-current' : (($initialModalItem['stage'] ?? 1) > 3 ? 'is-done' : '') }}" data-cw-step-3>
                        <span class="cw-timeline-dot">03</span>
                        <div class="cw-timeline-copy">
                            <strong>Pencucian</strong>
                            <small>Detailing bay</small>
                        </div>
                    </div>
                    <div class="cw-timeline-step {{ ($initialModalItem['stage'] ?? 1) >= 4 ? 'is-done' : '' }}" data-cw-step-4>
                        <span class="cw-timeline-dot">04</span>
                        <div class="cw-timeline-copy">
                            <strong>Selesai</strong>
                            <small>Siap diambil</small>
                        </div>
                    </div>
                </div>

                <div class="cw-detail-grid">
                    <section class="cw-detail-box">
                        <h3>Kendaraan</h3>
                        <dl class="cw-dl">
                            <div><dt>Kendaraan</dt><dd data-cw-modal-vehicle>{{ $initialModalItem['vehicle'] ?? '—' }}</dd></div>
                            <div><dt>No. Polisi</dt><dd data-cw-modal-plate>{{ $initialModalItem['plate'] ?? '—' }}</dd></div>
                            <div><dt>Kilometer</dt><dd data-cw-modal-mileage>{{ $initialModalItem['mileage'] ?? '—' }}</dd></div>
                        </dl>
                    </section>

                    <section class="cw-detail-box">
                        <h3>Jadwal &amp; Paket</h3>
                        <dl class="cw-dl">
                            <div><dt>Paket Cuci</dt><dd data-cw-modal-package>{{ $initialModalItem['package'] ?? '—' }}</dd></div>
                            <div><dt>Tanggal</dt><dd data-cw-modal-date>{{ $initialModalItem['date'] ?? '—' }}</dd></div>
                            <div><dt>Jam Kedatangan</dt><dd data-cw-modal-time>{{ $initialModalItem['time'] ?? '—' }}</dd></div>
                            <div><dt>Estimasi Durasi</dt><dd data-cw-modal-duration>{{ $initialModalItem['duration'] ?? '—' }}</dd></div>
                        </dl>
                    </section>
                </div>

                <div class="cw-detail-grid">
                    <section class="cw-detail-box">
                        <h3>Petugas Car Care</h3>
                        <p style="margin:0;color:#0f172a;font-size:13px;font-weight:700;" data-cw-modal-staff>{{ $initialModalItem['staff'] ?? 'Ditentukan saat kedatangan' }}</p>
                    </section>
                    <section class="cw-detail-box">
                        <h3>Catatan Pelanggan</h3>
                        <p style="margin:0;color:#334155;font-size:12.5px;line-height:1.5;" data-cw-modal-note>{{ $initialModalItem['note'] ?? 'Tidak ada catatan khusus' }}</p>
                    </section>
                </div>

                <div class="cw-pricing-box">
                    <div class="cw-price-row">
                        <span>Biaya Paket Car Wash</span>
                        <strong data-cw-modal-price-base>{{ $initialModalItem['price_base'] ?? '—' }}</strong>
                    </div>
                    <div class="cw-price-row is-discount" data-cw-modal-discount-row {{ !empty($initialModalItem['has_discount']) ? '' : 'hidden' }}>
                        <span>Potongan Promo</span>
                        <strong data-cw-modal-price-discount>{{ $initialModalItem['price_discount'] ?? 'Tidak Ada' }}</strong>
                    </div>
                    <div class="cw-price-total">
                        <span>Total Harga</span>
                        <div class="cw-price-total-val">
                            <del data-cw-modal-price-strike {{ !empty($initialModalItem['price_strike']) ? '' : 'hidden' }}>{{ $initialModalItem['price_strike'] ?? '' }}</del>
                            <strong data-cw-modal-price-total>{{ $initialModalItem['price_total'] ?? '—' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="cw-modal-foot">
                <form method="post" action="{{ $initialModalItem['cancel_url'] ?? '#' }}" data-cw-modal-cancel-form {{ !empty($initialModalItem['can_cancel']) ? '' : 'hidden' }} data-cxc-form>
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="cw-btn cw-btn-danger">Batalkan Booking</button>
                </form>
                <div class="cw-modal-foot-right">
                    <a class="cw-btn" href="{{ route('pelanggan.kendaraan.index') }}">Kendaraan Saya</a>
                    <button type="button" class="cw-btn" style="background:#0f172a;border-color:#0f172a;color:#fff!important;" data-close-cw-detail>Tutup</button>
                </div>
            </footer>
        </div>
    </div>
</div>
<div class="cxc-overlay" data-cxc-modal hidden role="alertdialog" aria-modal="true" aria-labelledby="cxc-title">
    <div class="cxc-box">
        <div class="cxc-icon" aria-hidden="true">!</div>
        <h3 id="cxc-title">Batalkan </h3>
        <p>Jadwal akan dibatalkan dan slot dilepas. Tindakan ini tidak dapat dikembalikan.</p>
        <span class="cxc-code" data-cxc-code hidden></span>
        <div class="cxc-actions">
            <button type="button" class="cxc-btn cxc-btn-ghost" data-cxc-no>Tidak, Kembali</button>
            <button type="button" class="cxc-btn cxc-btn-danger" data-cxc-yes>Ya, Batalkan</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const root = document.querySelector('[data-cw-page]');
    if (!root) return;
    const bookings = @json($bookingsMap);
    const modal = root.querySelector('[data-cw-detail-modal]');
    const setText = (sel, val) => {
        const el = root.querySelector(sel);
        if (el) el.textContent = val || '—';
    };

    const openDetail = (id) => {
        const item = bookings[String(id)];
        if (!item || !modal) return;
        setText('[data-cw-modal-heading]', 'Detail Booking Car Wash');
        setText('[data-cw-modal-code]', item.code);
        const badge = modal.querySelector('[data-cw-modal-status]');
        if (badge) {
            badge.textContent = item.status_label;
            badge.dataset.tone = item.tone;
        }
        setText('[data-cw-modal-submitted]', item.submitted_at);

        const s2 = modal.querySelector('[data-cw-step-2]');
        const s3 = modal.querySelector('[data-cw-step-3]');
        const s4 = modal.querySelector('[data-cw-step-4]');
        if (s2) { s2.classList.toggle('is-current', item.stage === 1 || item.stage === 2); s2.classList.toggle('is-done', item.stage > 2); }
        if (s3) { s3.classList.toggle('is-current', item.stage === 3); s3.classList.toggle('is-done', item.stage > 3); }
        if (s4) { s4.classList.toggle('is-done', item.stage >= 4); }

        setText('[data-cw-modal-vehicle]', item.vehicle);
        setText('[data-cw-modal-plate]', item.plate);
        setText('[data-cw-modal-mileage]', item.mileage);
        setText('[data-cw-modal-package]', item.package);
        setText('[data-cw-modal-date]', item.date);
        setText('[data-cw-modal-time]', item.time);
        setText('[data-cw-modal-duration]', item.duration);
        setText('[data-cw-modal-staff]', item.staff);
        setText('[data-cw-modal-note]', item.note);
        setText('[data-cw-modal-price-base]', item.price_base);
        setText('[data-cw-modal-price-discount]', item.price_discount);
        setText('[data-cw-modal-price-total]', item.price_total);

        const dRow = modal.querySelector('[data-cw-modal-discount-row]');
        if (dRow) dRow.hidden = !item.has_discount;
        const strike = modal.querySelector('[data-cw-modal-price-strike]');
        if (strike) {
            strike.hidden = !item.price_strike;
            strike.textContent = item.price_strike || '';
        }

        const cancelForm = modal.querySelector('[data-cw-modal-cancel-form]');
        if (cancelForm) {
            cancelForm.hidden = !item.can_cancel;
            cancelForm.action = item.cancel_url;
        }

        modal.hidden = false;
        document.body.style.overflow = 'hidden';
    };

    const closeDetail = () => {
        if (!modal) return;
        modal.hidden = true;
        document.body.style.overflow = '';
        if (location.search.includes('detail=')) {
            const url = new URL(location.href);
            url.searchParams.delete('detail');
            history.replaceState({}, '', url);
        }
    };

    root.querySelectorAll('[data-open-cw-detail]').forEach(btn => {
        btn.addEventListener('click', () => openDetail(btn.dataset.openCwDetail));
    });
    root.querySelectorAll('[data-close-cw-detail]').forEach(btn => {
        btn.addEventListener('click', closeDetail);
    });
    modal?.addEventListener('click', e => {
        if (e.target === modal) closeDetail();
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modal && !modal.hidden) closeDetail();
    });


    // Modal konfirmasi batal (pengganti confirm() bawaan browser)
    const cxc = document.querySelector('[data-cxc-modal]');
    let cxcForm = null;
    const cxcClose = () => { if (cxc) cxc.hidden = true; cxcForm = null; };
    document.addEventListener('submit', e => {
        const f = e.target.closest('form[data-cxc-form]');
        if (!f || !cxc || f.dataset.cxcOk === '1') return;
        e.preventDefault();
        cxcForm = f;
        const codeEl = cxc.querySelector('[data-cxc-code]');
        codeEl.textContent = f.dataset.cxcCode || '';
        codeEl.hidden = !f.dataset.cxcCode;
        cxc.hidden = false;
        cxc.querySelector('[data-cxc-no]').focus();
    });
    cxc?.querySelector('[data-cxc-no]').addEventListener('click', cxcClose);
    cxc?.addEventListener('click', e => { if (e.target === cxc) cxcClose(); });
    cxc?.querySelector('[data-cxc-yes]').addEventListener('click', () => {
        if (!cxcForm) return;
        const f = cxcForm;
        f.dataset.cxcOk = '1';
        cxc.querySelector('[data-cxc-yes]').disabled = true;
        f.submit();
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && cxc && !cxc.hidden) { e.stopImmediatePropagation(); cxcClose(); }
    }, true);
    let activeFilter = 'all';
    const searchInput = root.querySelector('[data-cw-search]');
    const rows = [...root.querySelectorAll('[data-cw-row]')];

    const applyFilter = () => {
        const q = (searchInput?.value || '').trim().toLowerCase();
        rows.forEach(row => {
            const matchTab = activeFilter === 'all' || row.dataset.group === activeFilter;
            const matchQuery = !q || (row.dataset.search || '').includes(q);
            row.hidden = !(matchTab && matchQuery);
        });
    };

    root.querySelectorAll('[data-cw-filter]').forEach(tab => {
        tab.addEventListener('click', () => {
            activeFilter = tab.dataset.cwFilter;
            root.querySelectorAll('[data-cw-filter]').forEach(t => t.classList.toggle('is-active', t === tab));
            applyFilter();
        });
    });
    searchInput?.addEventListener('input', applyFilter);
})();
</script>
@endpush
