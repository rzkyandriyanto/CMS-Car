@extends('layouts.app')

@section('title', 'Booking Car Wash · YZA Automotive')

@php
    $selectedVehicleId = $selectedVehicleId ?? 0;
    $selectedPackageId = $selectedPackageId ?? 0;
    $initialVehicleId = old('id_kendaraan_pelanggan', $selectedVehicleId ?: $vehicles->first()?->getKey());
    $initialPackageId = old('id_paket', $selectedPackageId ?: $packages->first()?->getKey());
    $initialDate = old('tanggal', '');
    $initialTime = old('jam', '');
    $initialPromoId = old('id_promo', '');

    $claimedPromos = $claimedPromos ?? collect();
    $unclaimedPromos = $unclaimedPromos ?? collect();
    $totalUnclaimedCount = $totalUnclaimedCount ?? $unclaimedPromos->count();

    $formatPromoBenefit = fn($p) => $p->jenis_benefit === 'persentase'
        ? 'Diskon ' . rtrim(rtrim(number_format((float) $p->nilai_benefit, 2, ',', '.'), '0'), ',') . '%'
        : ($p->jenis_benefit === 'nominal' ? 'Potongan Rp ' . number_format((float) $p->nilai_benefit, 0, ',', '.') : 'Benefit Member');

    $promoPayload = $claimedPromos->concat($unclaimedPromos)->mapWithKeys(fn($p) => [
        (string) $p->getKey() => [
            'id' => (string) $p->getKey(),
            'code' => $p->kode_promo,
            'name' => $p->nama,
            'scope' => $p->berlaku_untuk,
            'type' => $p->jenis_benefit,
            'value' => (float) ($p->nilai_benefit ?? 0),
            'max_discount' => $p->maksimum_benefit !== null && (float) $p->maksimum_benefit > 0 ? (float) $p->maksimum_benefit : null,
            'benefit' => $formatPromoBenefit($p),
        ],
    ]);

    $unclaimedPayload = $unclaimedPromos->map(fn($p) => [
        'id' => (string) $p->getKey(),
        'code' => $p->kode_promo,
        'name' => $p->nama,
        'scope' => $p->berlaku_untuk,
        'type' => $p->jenis_benefit,
        'value' => (float) ($p->nilai_benefit ?? 0),
        'max_discount' => $p->maksimum_benefit !== null && (float) $p->maksimum_benefit > 0 ? (float) $p->maksimum_benefit : null,
        'benefit' => $formatPromoBenefit($p),
        'points_cost' => (int) ($p->points_cost ?? 0),
        'claim_url' => route('pelanggan.promo.claim', $p),
    ])->values();

    $vehiclePayload = $vehicles->mapWithKeys(fn($v) => [
        (string) $v->getKey() => [
            'name' => trim($v->merk . ' ' . $v->model),
            'plate' => $v->nomor_polisi ?: 'Nomor polisi belum diisi',
            'year' => $v->tahun ?: 'Tahun belum diisi',
            'color' => $v->warna ?: 'Warna belum diisi',
            'mileage' => number_format((int) ($v->kilometer ?? 0), 0, ',', '.') . ' km',
            'image' => $v->booking_image ?? asset('assets/images/hero/yza-hero-reference-v3.png'),
            'verified' => (bool) ($v->is_verified_ownership ?? false),
        ],
    ]);

    $packagePayload = $packages->mapWithKeys(fn($p) => [
        (string) $p->getKey() => [
            'name' => $p->nama_paket,
            'desc' => $p->deskripsi ?: 'Perawatan eksterior & interior standar studio YZA Car Care.',
            'price' => 'Rp ' . number_format((float) $p->harga, 0, ',', '.'),
            'price_amount' => (float) $p->harga,
            'duration' => max(15, (int) ($p->estimasi_menit ?? 45)),
        ],
    ]);
@endphp

@push('styles')
<style>
.member-horizontal-shell .member-main-content:has(.cwc-page){padding:0!important;max-width:none!important}
.cwc-page{width:min(96vw,1640px);margin:0 auto;padding:0 0 64px;color:#0f172a}

/* Hero */
.cwc-hero{position:relative;min-height:200px;display:flex;justify-content:space-between;align-items:flex-end;gap:24px;padding:34px clamp(24px,4vw,56px) 28px;margin-bottom:22px;border-radius:0 0 22px 22px;overflow:hidden;color:#fff;background:linear-gradient(90deg,rgba(6,11,19,.97) 0%,rgba(10,16,26,.86) 48%,rgba(10,16,26,.28) 100%),url('/assets/images/member/member-benefit-carwash-v2.png') right center/cover;border-bottom:3px solid #e31d35;box-shadow:0 16px 36px -14px rgba(15,23,42,.22)}
.cwc-hero-copy{position:relative;z-index:1;max-width:700px}
.cwc-eyebrow{display:inline-flex;align-items:center;gap:7px;margin-bottom:8px;color:#ff2d46;font-size:11px;font-weight:900;letter-spacing:.18em;text-transform:uppercase}
.cwc-eyebrow::before{content:'';width:6px;height:6px;border-radius:999px;background:#ff2d46}
.cwc-hero h1{margin:0;color:#fff;font-size:clamp(30px,3.4vw,48px);font-weight:900;letter-spacing:-.04em;line-height:1.05}
.cwc-hero p{margin:8px 0 0;color:#cbd5e1;font-size:14.5px}
.cwc-back,.cwc-back:visited{position:relative;z-index:1;display:inline-flex;align-items:center;gap:6px;padding:9px 15px;border:1px solid rgba(255,255,255,.22);border-radius:10px;background:rgba(15,23,42,.55);backdrop-filter:blur(6px);color:#fff!important;font-size:12.5px;font-weight:800;text-decoration:none!important;transition:all .15s ease}
.cwc-back:hover{border-color:#fff;background:rgba(255,255,255,.15);color:#fff!important}

/* Layout */
.cwc-layout{display:grid;grid-template-columns:minmax(0,2.2fr) minmax(350px,1fr);gap:20px;align-items:start}
.cwc-main{display:grid;gap:18px;min-width:0}
.cwc-block{padding:24px 26px;border:1px solid #e2e8f0;border-radius:16px;background:#fff;box-shadow:0 8px 24px -12px rgba(15,23,42,.04)}
.cwc-block-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #f1f5f9}
.cwc-block-title{display:flex;align-items:center;gap:10px}
.cwc-step-num{display:inline-grid;place-items:center;width:26px;height:26px;border-radius:8px;background:#0f172a;color:#fff;font-size:11px;font-weight:800}
.cwc-block-title h2{margin:0;color:#0f172a;font-size:17px;font-weight:800;letter-spacing:-.02em}
.cwc-block-badge{padding:3px 9px;border-radius:999px;background:#f1f5f9;color:#64748b;font-size:10.5px;font-weight:700}

.cwc-error{margin-bottom:16px;padding:13px 16px;border:1px solid #fecdd3;border-radius:12px;background:#fff1f2;color:#be123c;font-size:13px;font-weight:700}

/* Field & Vehicle Select */
.cwc-field{display:grid;gap:7px;margin-bottom:18px}
.cwc-field:last-child{margin-bottom:0}
.cwc-field label{color:#0f172a;font-size:13px;font-weight:800}
.cwc-field select,.cwc-field textarea{box-sizing:border-box;width:100%;border:1px solid #cbd5e1;border-radius:11px;background:#fff;color:#0f172a;font:inherit;font-size:14px;outline:0;transition:border-color .15s ease,box-shadow .15s ease}
.cwc-field select{min-height:46px;padding:0 14px}
.cwc-field textarea{min-height:88px;padding:12px 14px;resize:vertical;line-height:1.5}
.cwc-field select:focus,.cwc-field textarea:focus{border-color:#e31d35;box-shadow:0 0 0 3px rgba(227,29,53,.08)}

/* Package Grid */
.cwc-pkg-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.cwc-pkg-card{position:relative;display:flex;flex-direction:column;align-items:stretch;gap:8px;min-height:136px;padding:16px 16px 14px 18px;border:1px solid #e2e8f0;border-radius:14px;background:#fff;color:#0f172a;text-align:left;cursor:pointer;overflow:hidden;transition:all .16s ease}
.cwc-pkg-card::before{content:'';position:absolute;left:0;top:12px;bottom:12px;width:3px;border-radius:0 3px 3px 0;background:#e2e8f0;transition:all .16s ease}
.cwc-pkg-card:hover{border-color:#cbd5e1;background:#fcfdfe;transform:translateY(-1px);box-shadow:0 10px 24px -10px rgba(15,23,42,.08)}
.cwc-pkg-card.is-selected{border-color:#e31d35;background:linear-gradient(180deg,#fff9fa 0%,#fff 75%);box-shadow:0 0 0 1px #e31d35,0 12px 26px -12px rgba(227,29,53,.2)}
.cwc-pkg-card.is-selected::before{top:0;bottom:0;width:4px;border-radius:0;background:#e31d35}
.cwc-pkg-top{display:flex;align-items:center;justify-content:space-between;gap:8px}
.cwc-pkg-tier{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:10px;font-weight:800;padding:2px 7px;border-radius:5px;background:#f1f5f9;color:#475569;text-transform:uppercase}
.cwc-pkg-card.is-selected .cwc-pkg-tier{background:#fff1f2;color:#be123c;border:1px solid #fecdd3}
.cwc-pkg-dur{font-size:11px;font-weight:700;color:#64748b}
.cwc-pkg-name{font-size:15.5px;font-weight:800;color:#0f172a;letter-spacing:-.01em}
.cwc-pkg-desc{font-size:12px;color:#64748b;line-height:1.4}
.cwc-pkg-foot{margin-top:auto;padding-top:10px;border-top:1px dashed #e2e8f0;display:flex;align-items:center;justify-content:space-between;gap:8px}
.cwc-pkg-card.is-selected .cwc-pkg-foot{border-top-color:#fecdd3}
.cwc-pkg-price{font-size:14.5px;font-weight:800;color:#e31d35;font-variant-numeric:tabular-nums}
.cwc-pkg-check{font-size:11px;font-weight:700;color:#64748b}
.cwc-pkg-card.is-selected .cwc-pkg-check{color:#e31d35;font-weight:800}

/* Calendar & Slots */
.cwc-cal-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}
.cwc-cal-head h3{margin:0;color:#0f172a;font-size:18px;font-weight:800;letter-spacing:-.02em}
.cwc-cal-navs{display:flex;align-items:center;gap:6px}
.cwc-cal-btn{height:34px;padding:0 11px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;color:#0f172a;font:inherit;font-size:12px;font-weight:700;cursor:pointer;transition:all .15s ease}
.cwc-cal-btn:hover:not(:disabled){border-color:#0f172a;background:#0f172a;color:#fff}
.cwc-cal-btn:disabled{opacity:.35;cursor:not-allowed}
.cwc-hours-bar{display:flex;align-items:center;gap:8px;margin-bottom:14px;padding:9px 12px;border:1px solid #f1f5f9;border-radius:10px;background:#f8fafc;color:#475569;font-size:12px;font-weight:600}
.cwc-hours-bar svg{width:15px;height:15px;color:#e31d35;flex-shrink:0}
.cwc-week,.cwc-cal-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:7px}
.cwc-week{margin-bottom:6px;color:#64748b;font-size:10.5px;font-weight:800;text-align:center;text-transform:uppercase;letter-spacing:.06em}
.cwc-day{position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;min-height:60px;padding:6px 4px;border:1px solid #e2e8f0;border-radius:11px;background:#fff;color:#0f172a;font:inherit;cursor:pointer;transition:all .15s ease}
.cwc-day strong{font-size:15.5px;font-weight:800;line-height:1;font-variant-numeric:tabular-nums}
.cwc-day span{font-size:9.5px;font-weight:700}
.cwc-day[data-status="available"]{border-color:#bbf7d0;background:#f0fdf4;color:#14532d}
.cwc-day[data-status="available"] span{color:#15803d}
.cwc-day[data-status="available"]:hover:not(:disabled){border-color:#16a34a;background:#dcfce7;transform:translateY(-1px)}
.cwc-day[data-status="closed"],.cwc-day[data-status="past"]{border-color:#f1f5f9;background:#f8fafc;color:#cbd5e1;cursor:not-allowed}
.cwc-day.is-selected{border-color:#e31d35!important;background:#e31d35!important;color:#fff!important;box-shadow:0 8px 18px rgba(227,29,53,.26)}
.cwc-day.is-selected span{color:rgba(255,255,255,.9)!important}
.cwc-day.is-empty{border:0;background:transparent;cursor:default;min-height:0}

.cwc-slots-wrap{margin-top:18px;padding-top:16px;border-top:1px solid #f1f5f9}
.cwc-slots-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px}
.cwc-slots-head h4{margin:0;color:#0f172a;font-size:14.5px;font-weight:800}
.cwc-date-pill{padding:4px 11px;border-radius:999px;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;font-size:11.5px;font-weight:800}
.cwc-slot-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
.cwc-slot{display:flex;flex-direction:column;align-items:flex-start;justify-content:center;gap:4px;min-height:60px;padding:11px 13px;border:1px solid #e2e8f0;border-radius:11px;background:#fff;color:#0f172a;font:inherit;text-align:left;cursor:pointer;transition:all .15s ease}
.cwc-slot:hover:not(:disabled){border-color:#cbd5e1;background:#f8fafc;transform:translateY(-1px)}
.cwc-slot strong{font-size:13.5px;font-weight:800;font-variant-numeric:tabular-nums}
.cwc-slot span{font-size:11px;font-weight:600;color:#059669}
.cwc-slot:disabled{opacity:.42;cursor:not-allowed;background:#f8fafc}
.cwc-slot:disabled span{color:#94a3b8}
.cwc-slot.is-selected{border-color:#e31d35;background:#fff5f6;box-shadow:0 0 0 1px #e31d35}
.cwc-slot.is-selected span{color:#e31d35;font-weight:700}

/* Coupon Cards (matching Booking Servis) */
.coupon-group-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:14px 0 10px}
.coupon-sublabel{display:inline-flex;align-items:center;gap:7px;margin:0;color:#334155;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
.coupon-sublabel::before{content:'';width:6px;height:6px;border-radius:999px;background:#e31d35}
.coupon-sublabel.is-muted::before{background:#94a3b8}
.coupon-grid,.coupon-unclaimed-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.coupon-card{position:relative;display:flex;flex-direction:column;align-items:stretch;gap:6px;min-height:142px;padding:14px 15px 12px 17px;border:1px solid #e2e8f0;border-radius:14px;background:#fff;color:#0f172a;text-align:left;cursor:pointer;overflow:hidden;transition:transform .16s ease,border-color .16s ease,background .16s ease,box-shadow .16s ease}
.coupon-card::before{content:'';position:absolute;left:0;top:12px;bottom:12px;width:3px;border-radius:0 3px 3px 0;background:#e2e8f0;transition:all .16s ease}
.coupon-card:hover:not(:disabled){border-color:#cbd5e1;background:#fcfdfe;transform:translateY(-1px);box-shadow:0 10px 24px -10px rgba(15,23,42,.08)}
.coupon-card.is-selected{border-color:#e31d35;background:linear-gradient(180deg,#fff9fa 0%,#fff 70%);box-shadow:0 0 0 1px #e31d35,0 12px 26px -12px rgba(227,29,53,.22)}
.coupon-card.is-selected::before{top:0;bottom:0;width:4px;border-radius:0;background:#e31d35}
.coupon-card:disabled,.coupon-card.is-disabled{opacity:.46;cursor:not-allowed;background:#f8fafc;border-color:#e2e8f0;transform:none;box-shadow:none}
.coupon-card-unclaimed{cursor:default;background:#fcfcfd}
.coupon-card-top{display:flex;align-items:center;justify-content:space-between;gap:8px;width:100%}
.coupon-code{display:inline-flex;align-items:center;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:10.5px;font-weight:700;padding:3px 8px;border-radius:6px;border:1px solid #e2e8f0;background:#f8fafc;color:#334155}
.coupon-card.is-selected .coupon-code{border-color:#fecdd3;background:#fff1f2;color:#be123c}
.coupon-badge{display:inline-flex;align-items:center;padding:2px 8px;border-radius:999px;font-size:10px;font-weight:700;background:#ecfdf5;color:#059669;border:1px solid #bbf7d0;white-space:nowrap}
.coupon-badge:empty{display:none}
.coupon-card.is-selected .coupon-badge{background:#e31d35;border-color:#e31d35;color:#fff}
.coupon-card.is-disabled .coupon-badge{background:#f1f5f9;border-color:#e2e8f0;color:#64748b}
.coupon-badge-points{background:#f8fafc;border-color:#e2e8f0;color:#475569}
.coupon-benefit{font-size:15px;font-weight:800;letter-spacing:-.02em;color:#0f172a;line-height:1.25;margin-top:2px}
.coupon-name{font-size:12px;color:#64748b;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.coupon-card-foot{margin-top:auto;padding-top:10px;border-top:1px dashed #e2e8f0;display:flex;align-items:center;justify-content:space-between;gap:8px;width:100%}
.coupon-card.is-selected .coupon-card-foot{border-top-color:#fecdd3}
.coupon-cta-text{font-size:11px;font-weight:700;color:#64748b}
.coupon-card.is-selected .coupon-cta-text{color:#e31d35}
.coupon-radio{width:16px;height:16px;border-radius:999px;border:1.5px solid #cbd5e1;background:#fff;display:inline-grid;place-items:center;flex-shrink:0}
.coupon-radio::after{content:'';width:6px;height:6px;border-radius:999px;background:#fff;transform:scale(0);transition:transform .15s ease}
.coupon-card.is-selected .coupon-radio{border-color:#e31d35;background:#e31d35}
.coupon-card.is-selected .coupon-radio::after{transform:scale(1)}
.coupon-claim-btn{width:100%;display:inline-flex;align-items:center;justify-content:center;padding:7px 12px;border:1px solid #0f172a;border-radius:8px;background:#0f172a;color:#fff;font:inherit;font-size:11.5px;font-weight:700;cursor:pointer;transition:all .15s ease}
.coupon-claim-btn:hover:not(:disabled){border-color:#e31d35;background:#e31d35;color:#fff}
.coupon-all-wrap{margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.coupon-all-btn,.coupon-all-btn:visited{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1px solid #e2e8f0;border-radius:9px;background:#f8fafc;color:#0f172a!important;font-size:12px;font-weight:700;text-decoration:none!important}
.coupon-all-btn:hover{border-color:#0f172a;background:#0f172a;color:#fff!important}
.coupon-empty{margin:0;color:#64748b;font-size:12px}
.coupon-feedback{margin-top:10px;padding:9px 12px;border-radius:9px;background:#f0fdf4;border:1px solid #bbf7d0;font-size:12px;font-weight:600;color:#15803d}.coupon-feedback.error{background:#fff1f2;border-color:#fecdd3;color:#be123c}

/* Sticky Summary Sidebar */
.cwc-summary{position:sticky;top:92px;border:1px solid #e2e8f0;border-radius:16px;background:#fff;box-shadow:0 12px 30px -10px rgba(15,23,42,.07);overflow:hidden}
.cwc-sum-head{padding:18px 20px 14px;border-bottom:1px solid #f1f5f9}
.cwc-sum-eyebrow{display:inline-flex;align-items:center;gap:6px;margin-bottom:4px;color:#e31d35;font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
.cwc-sum-eyebrow::before{content:'';width:6px;height:6px;border-radius:999px;background:#e31d35}
.cwc-sum-head h2{margin:0;color:#0f172a;font-size:19px;font-weight:800;letter-spacing:-.02em}
.cwc-sum-head p{margin:4px 0 0;color:#64748b;font-size:12px}
.cwc-sum-vehicle{display:grid;grid-template-columns:88px minmax(0,1fr);gap:12px;align-items:center;margin:14px 20px 6px;padding:11px 12px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc}
.cwc-sum-vehicle img{width:88px;height:62px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0;background:#fff}
.cwc-sum-vehicle h3{margin:0 0 3px;color:#0f172a;font-size:15px;font-weight:800;line-height:1.2}
.cwc-sum-vehicle p{margin:0;color:#64748b;font-size:11.5px;line-height:1.35}
.cwc-verified{display:inline-flex;align-items:center;gap:4px;margin-top:6px;padding:3px 8px;border-radius:999px;background:#ecfdf5;border:1px solid #bbf7d0;color:#059669;font-size:10px;font-weight:700}
.cwc-sum-facts{display:grid;padding:6px 20px 8px}
.cwc-sum-fact{display:flex;align-items:baseline;justify-content:space-between;gap:12px;padding:8px 0;border-bottom:1px dashed #f1f5f9;font-size:12.5px}
.cwc-sum-fact:last-child{border-bottom:0}
.cwc-sum-fact span{color:#64748b;font-weight:500}
.cwc-sum-fact strong{max-width:62%;color:#0f172a;font-weight:700;text-align:right;overflow-wrap:anywhere}
.cwc-sum-pricing{margin:6px 20px 14px;padding:13px 15px;border:1px solid #e2e8f0;border-radius:12px;background:#fcfcfd;display:grid;gap:8px}
.cwc-price-row{display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:12.5px}
.cwc-price-row[hidden]{display:none!important}
.cwc-price-row span{color:#64748b;font-weight:600}
.cwc-price-row strong{color:#0f172a;font-weight:700;font-variant-numeric:tabular-nums}
.cwc-price-row.is-discount span,.cwc-price-row.is-discount strong{color:#059669;font-weight:800}
.cwc-promo-pill{display:inline-flex;padding:1px 6px;border-radius:5px;background:#ecfdf5;border:1px solid #bbf7d0;color:#047857;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:9.5px;font-weight:700}
.cwc-total-row{display:flex;align-items:baseline;justify-content:space-between;gap:10px;padding-top:9px;margin-top:2px;border-top:1px dashed #e2e8f0}
.cwc-total-row>span{color:#0f172a;font-size:12.5px;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
.cwc-total-val{display:flex;align-items:baseline;gap:7px}
.cwc-total-val del{color:#94a3b8;font-size:11.5px;font-weight:600}
.cwc-total-val strong{color:#e31d35;font-size:16px;font-weight:800;letter-spacing:-.02em}
.cwc-sum-actions{padding:0 20px 18px;display:grid;gap:10px}
.cwc-submit-btn{width:100%;min-height:46px;display:inline-flex;align-items:center;justify-content:center;gap:8px;border:0;border-radius:11px;background:#e31d35;color:#fff;font:inherit;font-size:13.5px;font-weight:800;cursor:pointer;box-shadow:0 10px 20px -6px rgba(227,29,53,.35);transition:all .15s ease}
.cwc-submit-btn:hover:not(:disabled){background:#be123c;transform:translateY(-1px)}
.cwc-submit-btn:disabled{background:#e2e8f0;color:#94a3b8;cursor:not-allowed;box-shadow:none;transform:none}
.cwc-sum-note{margin:0;padding:10px 12px;border-radius:9px;background:#f8fafc;border:1px solid #f1f5f9;color:#64748b;font-size:11.5px;line-height:1.45}

/* Confirmation Modal */
.cwc-modal-overlay{position:fixed;inset:0;z-index:1200;display:grid;place-items:center;padding:20px;background:rgba(15,23,42,.56);backdrop-filter:blur(3px)}
.cwc-modal-overlay[hidden]{display:none!important}
.cwc-modal{width:min(520px,100%);border:1px solid #e2e8f0;border-radius:18px;background:#fff;box-shadow:0 24px 60px rgba(15,23,42,.24);overflow:hidden}
.cwc-modal-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 22px;border-bottom:1px solid #f1f5f9}
.cwc-modal-head h3{margin:0;color:#0f172a;font-size:18px;font-weight:800}
.cwc-modal-head p{margin:3px 0 0;color:#64748b;font-size:12.5px}
.cwc-modal-close{display:grid;place-items:center;width:34px;height:34px;border:1px solid #e2e8f0;border-radius:9px;background:#fff;color:#475569;font-size:20px;cursor:pointer}
.cwc-modal-body{padding:18px 22px;display:grid;gap:14px}
.cwc-modal-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;padding:14px 16px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc}
.cwc-modal-grid div{display:grid;gap:3px}
.cwc-modal-grid div.is-full{grid-column:1/-1}
.cwc-modal-grid span{color:#64748b;font-size:11px;font-weight:600}
.cwc-modal-grid strong{color:#0f172a;font-size:13px;font-weight:700}
.cwc-modal-foot{display:flex;align-items:center;justify-content:flex-end;gap:10px;padding:14px 22px;border-top:1px solid #f1f5f9;background:#f8fafc}
.cwc-btn-sec{min-height:42px;padding:0 16px;border:1px solid #cbd5e1;border-radius:9px;background:#fff;color:#0f172a;font:inherit;font-size:12.5px;font-weight:700;cursor:pointer}
.cwc-btn-pri{min-height:42px;padding:0 18px;border:0;border-radius:9px;background:#e31d35;color:#fff;font:inherit;font-size:12.5px;font-weight:800;cursor:pointer;box-shadow:0 8px 16px rgba(227,29,53,.28)}

@media(max-width:1024px){.cwc-layout{grid-template-columns:1fr}.cwc-summary{position:static}.coupon-grid,.coupon-unclaimed-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:640px){.cwc-hero{flex-direction:column;align-items:flex-start}.cwc-pkg-grid,.cwc-slot-grid,.coupon-grid,.coupon-unclaimed-grid,.cwc-modal-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="cwc-page" data-cwc-app data-current-month="{{ now()->format('Y-m') }}" data-initial-vehicle="{{ $initialVehicleId }}" data-initial-package="{{ $initialPackageId }}" data-initial-date="{{ $initialDate }}" data-initial-time="{{ $initialTime }}">
    <header class="cwc-hero">
        <div class="cwc-hero-copy">
            <span class="cwc-eyebrow">YZA CAR CARE STUDIO</span>
            <h1>Booking Car Wash</h1>
            <p>Pilih kendaraan, paket perawatan studio, jadwal kedatangan, dan kupon benefit member Anda.</p>
        </div>
        <a class="cwc-back" href="{{ route('pelanggan.booking-cuci.index') }}">← Lihat Car Wash Saya</a>
    </header>

    @if($errors->any())
        <div class="cwc-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="cwc-layout">
        <main class="cwc-main">
            <form id="cwc-booking-form" method="post" action="{{ route('pelanggan.booking-cuci.store') }}" data-cwc-form>
                @csrf
                <input type="hidden" name="id_kendaraan_pelanggan" value="{{ $initialVehicleId }}" data-cwc-vehicle-input>
                <input type="hidden" name="id_paket" value="{{ $initialPackageId }}" data-cwc-package-input>
                <input type="hidden" name="tanggal" value="{{ $initialDate }}" data-cwc-date-input>
                <input type="hidden" name="jam" value="{{ $initialTime }}" data-cwc-time-input>
                <input type="hidden" name="id_promo" value="{{ $initialPromoId }}" data-cwc-promo-input>

                {{-- 01 Kendaraan & Paket --}}
                <section class="cwc-block" style="margin-bottom:18px;">
                    <div class="cwc-block-head">
                        <div class="cwc-block-title">
                            <span class="cwc-step-num">01</span>
                            <h2>Kendaraan &amp; Paket Car Wash</h2>
                        </div>
                        <span class="cwc-block-badge">Wajib</span>
                    </div>

                    <div class="cwc-field">
                        <label for="cwc-vehicle-select">Kendaraan yang akan dicuci</label>
                        <select id="cwc-vehicle-select" required data-cwc-vehicle-select>
                            <option value="">Pilih kendaraan</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->getKey() }}" @selected((string) $initialVehicleId === (string) $v->getKey())>
                                    {{ trim($v->merk . ' ' . $v->model) }} · {{ $v->nomor_polisi ?: 'Tanpa nomor polisi' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="cwc-field">
                        <label>Pilih Paket Perawatan</label>
                        <div class="cwc-pkg-grid">
                            @foreach($packages as $idx => $p)
                                @php
                                    $tiers = ['EXPRESS CARE', 'SIGNATURE WASH', 'ULTIMATE DETAIL', 'CERAMIC GLOSS'];
                                    $tier = $tiers[$idx % count($tiers)];
                                    $isPicked = (string) $initialPackageId === (string) $p->getKey();
                                    $durMin = max(15, (int) ($p->estimasi_menit ?? 45));
                                @endphp
                                <button type="button" class="cwc-pkg-card {{ $isPicked ? 'is-selected' : '' }}" data-cwc-pkg-choice="{{ $p->getKey() }}">
                                    <div class="cwc-pkg-top">
                                        <span class="cwc-pkg-tier">{{ $tier }}</span>
                                        <span class="cwc-pkg-dur">± {{ $durMin }} menit</span>
                                    </div>
                                    <strong class="cwc-pkg-name">{{ $p->nama_paket }}</strong>
                                    <span class="cwc-pkg-desc">{{ $p->deskripsi ?: 'Pencucian hidrolik menyeluruh, vakum kabin interior, dan semir ban premium.' }}</span>
                                    <div class="cwc-pkg-foot">
                                        <span class="cwc-pkg-price">Rp {{ number_format((float) $p->harga, 0, ',', '.') }}</span>
                                        <span class="cwc-pkg-check" data-cwc-pkg-badge>{{ $isPicked ? '✓ Paket Dipilih' : 'Pilih Paket' }}</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- 02 Tanggal & Jam Kedatangan --}}
                <section class="cwc-block" style="margin-bottom:18px;">
                    <div class="cwc-block-head">
                        <div class="cwc-block-title">
                            <span class="cwc-step-num">02</span>
                            <h2>Jadwal Kedatangan</h2>
                        </div>
                        <span class="cwc-block-badge">Wajib</span>
                    </div>

                    <div class="cwc-cal-head">
                        <h3 data-cwc-month-label>{{ ucfirst(now()->translatedFormat('F Y')) }}</h3>
                        <div class="cwc-cal-navs">
                            <button type="button" class="cwc-cal-btn" data-cwc-month-prev aria-label="Bulan sebelumnya">‹</button>
                            <button type="button" class="cwc-cal-btn" data-cwc-month-next aria-label="Bulan berikutnya">›</button>
                        </div>
                    </div>

                    <div class="cwc-hours-bar">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>Jam Operasional YZA Car Care: <strong>Senin – Jumat</strong> · 08:00 – 17:00 WIB (Istirahat 12:00 – 13:00 WIB)</span>
                    </div>

                    <div class="cwc-week" aria-hidden="true">
                        <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
                    </div>
                    <div class="cwc-cal-grid" data-cwc-cal-grid></div>

                    <div class="cwc-slots-wrap">
                        <div class="cwc-slots-head">
                            <h4>Pilih Jam Kedatangan</h4>
                            <span class="cwc-date-pill" data-cwc-date-pill hidden></span>
                        </div>
                        <div class="cwc-slot-grid" data-cwc-slot-grid></div>
                    </div>
                </section>

                {{-- 03 Catatan & Kupon Promo --}}
                <section class="cwc-block">
                    <div class="cwc-block-head">
                        <div class="cwc-block-title">
                            <span class="cwc-step-num">03</span>
                            <h2>Catatan &amp; Kupon Promo</h2>
                        </div>
                        <span class="cwc-block-badge">Opsional</span>
                    </div>

                    <div class="cwc-field">
                        <label for="cwc-note">Catatan Perawatan <span style="font-weight:500;color:#94a3b8">(Opsional)</span></label>
                        <textarea id="cwc-note" name="catatan" placeholder="Contoh: Fokus pembersihan noda karpet baris kedua atau hindari semir silikon pada bagian doff.">{{ old('catatan') }}</textarea>
                    </div>

                    <div class="coupon-group-head" style="margin-top:20px;">
                        <p class="coupon-sublabel">Kupon Sudah Terklaim</p>
                    </div>
                    <div class="coupon-grid" data-claimed-coupon-grid>
                        <button type="button" class="coupon-card {{ !$initialPromoId ? 'is-selected' : '' }}" data-promo-choice="">
                            <div class="coupon-card-top"><span class="coupon-code">TANPA KUPON</span><span class="coupon-badge" data-promo-badge>{{ !$initialPromoId ? '✓ Dipilih' : '' }}</span></div>
                            <strong class="coupon-benefit">Tidak Pakai Kupon</strong>
                            <span class="coupon-name">Lanjutkan booking dengan tarif standar studio.</span>
                            <div class="coupon-card-foot"><span class="coupon-cta-text" data-promo-cta>{{ !$initialPromoId ? 'Tarif standar aktif' : 'Gunakan tarif standar' }}</span><span class="coupon-radio" aria-hidden="true"></span></div>
                        </button>
                        @foreach($claimedPromos as $promo)
                            @php $isPicked = (string) $initialPromoId === (string) $promo->getKey(); @endphp
                            <button type="button" class="coupon-card {{ $isPicked ? 'is-selected' : '' }}" data-promo-choice="{{ $promo->getKey() }}">
                                <div class="coupon-card-top"><span class="coupon-code">{{ $promo->kode_promo }}</span><span class="coupon-badge" data-promo-badge>{{ $isPicked ? '✓ Dipilih' : 'Terklaim' }}</span></div>
                                <strong class="coupon-benefit">{{ $formatPromoBenefit($promo) }}</strong>
                                <span class="coupon-name">{{ $promo->nama }}</span>
                                <div class="coupon-card-foot"><span class="coupon-cta-text" data-promo-cta>{{ $isPicked ? 'Kupon digunakan' : 'Pakai kupon ini' }}</span><span class="coupon-radio" aria-hidden="true"></span></div>
                            </button>
                        @endforeach
                    </div>

                    @if($unclaimedPromos->isNotEmpty())
                        <div data-unclaimed-section>
                            <div class="coupon-group-head" style="margin-top:20px;">
                                <p class="coupon-sublabel is-muted">Kupon Belum Terklaim</p>
                            </div>
                            <div class="coupon-unclaimed-grid" data-unclaimed-coupon-grid></div>
                            <div class="coupon-all-wrap">
                                <span class="coupon-empty" data-unclaimed-summary>Menampilkan kupon promo member.</span>
                                <a class="coupon-all-btn" href="{{ route('pelanggan.promo.index') }}" target="_blank" rel="noopener">Lihat Semua Kupon →</a>
                            </div>
                        </div>
                    @endif
                    <div class="coupon-feedback" data-coupon-feedback hidden></div>
                </section>
            </form>
        </main>

        {{-- Sticky Ringkasan Car Wash --}}
        <aside class="cwc-summary" aria-live="polite">
            <div class="cwc-sum-head">
                <span class="cwc-sum-eyebrow">YZA Car Care Summary</span>
                <h2>Ringkasan Car Wash</h2>
                <p>Detail jadwal &amp; rincian biaya mengikuti pilihan Anda.</p>
            </div>
            <div class="cwc-sum-vehicle">
                <img src="{{ asset('assets/images/hero/yza-hero-reference-v3.png') }}" alt="" data-cwc-sum-img>
                <div>
                    <h3 data-cwc-sum-vehicle>Belum memilih kendaraan</h3>
                    <p data-cwc-sum-meta>Pilih kendaraan untuk melihat detailnya.</p>
                    <span class="cwc-verified" data-cwc-sum-verified hidden>✓ Verified YZA Ownership</span>
                </div>
            </div>
            <div class="cwc-sum-facts">
                <div class="cwc-sum-fact"><span>Paket Cuci</span><strong data-cwc-sum-pkg>Belum dipilih</strong></div>
                <div class="cwc-sum-fact"><span>Tanggal</span><strong data-cwc-sum-date>Belum dipilih</strong></div>
                <div class="cwc-sum-fact"><span>Waktu Kedatangan</span><strong data-cwc-sum-time>Belum dipilih</strong></div>
                <div class="cwc-sum-fact"><span>Estimasi Durasi</span><strong data-cwc-sum-dur>Belum dipilih</strong></div>
                <div class="cwc-sum-fact"><span>Estimasi Selesai</span><strong data-cwc-sum-finish>Belum dipilih</strong></div>
                <div class="cwc-sum-fact"><span>Kupon Promo</span><strong data-cwc-sum-promo>Tidak Pakai Kupon</strong></div>
            </div>
            <div class="cwc-sum-pricing">
                <div class="cwc-price-row">
                    <span>Biaya Paket</span>
                    <strong data-cwc-sum-price>Belum dipilih</strong>
                </div>
                <div class="cwc-price-row is-discount" data-cwc-sum-disc-row hidden>
                    <span>Potongan Promo <em class="cwc-promo-pill" data-cwc-sum-disc-code></em></span>
                    <strong data-cwc-sum-disc>- Rp 0</strong>
                </div>
                <div class="cwc-total-row">
                    <span>Total Harga</span>
                    <div class="cwc-total-val">
                        <del data-cwc-sum-strike hidden></del>
                        <strong data-cwc-sum-total>Belum dipilih</strong>
                    </div>
                </div>
            </div>
            <div class="cwc-sum-actions">
                <button type="button" class="cwc-submit-btn" data-cwc-submit disabled>Ajukan Booking Car Wash →</button>
                <p class="cwc-sum-note">Jadwal pencucian akan langsung diproses oleh tim YZA Car Care setelah dikonfirmasi.</p>
            </div>
        </aside>
    </div>

    {{-- Modal Konfirmasi Pengajuan --}}
    <div class="cwc-modal-overlay" data-cwc-confirm-modal hidden role="dialog" aria-modal="true" aria-labelledby="cwc-confirm-title">
        <div class="cwc-modal">
            <div class="cwc-modal-head">
                <div>
                    <span class="cwc-sum-eyebrow" style="margin-bottom:2px;">Langkah Konfirmasi</span>
                    <h3 id="cwc-confirm-title">Ajukan Booking Car Wash?</h3>
                    <p>Pastikan kendaraan, paket, dan jadwal kedatangan sudah sesuai.</p>
                </div>
                <button type="button" class="cwc-modal-close" data-cwc-modal-close aria-label="Tutup modal">&times;</button>
            </div>
            <div class="cwc-modal-body">
                <div class="cwc-modal-grid">
                    <div class="is-full"><span>Kendaraan</span><strong data-cwc-m-vehicle>—</strong></div>
                    <div><span>Paket Cuci</span><strong data-cwc-m-pkg>—</strong></div>
                    <div><span>Estimasi Durasi</span><strong data-cwc-m-dur>—</strong></div>
                    <div class="is-full"><span>Jadwal Kedatangan</span><strong data-cwc-m-schedule>—</strong></div>
                    <div class="is-full"><span>Kupon Promo</span><strong data-cwc-m-promo>Tidak Pakai Kupon</strong></div>
                </div>
                <div class="cwc-sum-pricing" style="margin:0;">
                    <div class="cwc-price-row">
                        <span>Biaya Paket</span>
                        <strong data-cwc-m-price>—</strong>
                    </div>
                    <div class="cwc-price-row is-discount" data-cwc-m-disc-row hidden>
                        <span>Potongan Promo</span>
                        <strong data-cwc-m-disc>- Rp 0</strong>
                    </div>
                    <div class="cwc-total-row">
                        <span>Total Harga</span>
                        <div class="cwc-total-val">
                            <del data-cwc-m-strike hidden></del>
                            <strong data-cwc-m-total>—</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cwc-modal-foot">
                <button type="button" class="cwc-btn-sec" data-cwc-modal-close>Periksa Kembali</button>
                <button type="button" class="cwc-btn-pri" data-cwc-modal-submit>Ya, Ajukan Car Wash →</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const root = document.querySelector('[data-cwc-app]');
    if (!root) return;
    const $ = s => root.querySelector(s);
    const $$ = s => [...root.querySelectorAll(s)];

    const vehicles = @json($vehiclePayload);
    const packages = @json($packagePayload);
    const promos = @json($promoPayload);
    let unclaimedPool = @json($unclaimedPayload);

    const now = new Date();
    const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    let month = root.dataset.currentMonth;
    let selectedPkg = String(root.dataset.initialPackage || '');
    let selectedDate = String(root.dataset.initialDate || '');
    let selectedTime = String(root.dataset.initialTime || '');
    let selectedPromo = String(@json((string) $initialPromoId));

    const vehicleSelect = $('[data-cwc-vehicle-select]');
    const vehicleInput = $('[data-cwc-vehicle-input]');
    const pkgInput = $('[data-cwc-package-input]');
    const dateInput = $('[data-cwc-date-input]');
    const timeInput = $('[data-cwc-time-input]');
    const promoInput = $('[data-cwc-promo-input]');

    const slotHours = ['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00'];

    const vehicle = () => vehicles[vehicleSelect?.value] || null;
    const pkg = () => packages[selectedPkg] || null;
    const promo = () => promos[selectedPromo] || null;
    const setText = (s, v) => { const n = $(s); if (n) n.textContent = v || 'Belum dipilih'; };

    const formatDate = v => {
        if (!v) return 'Belum dipilih';
        const [y, m, d] = v.split('-').map(Number);
        return new Date(y, m - 1, d).toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' });
    };
    const monthLabel = v => {
        const [y, m] = v.split('-').map(Number);
        const l = new Date(y, m - 1, 1).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
        return l.charAt(0).toUpperCase() + l.slice(1);
    };
    const formatDur = m => {
        m = +m || 45;
        return m >= 60 ? `± ${Math.floor(m / 60)} jam${m % 60 ? ` ${m % 60} menit` : ''}` : `± ${m} menit`;
    };
    const finishTime = () => {
        const p = pkg();
        if (!selectedTime || !p) return '';
        const [h, m] = selectedTime.split(':').map(Number);
        const d = new Date(2000, 0, 1, h, m + (p.duration || 45));
        return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
    };
    const formatRp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');

    const calcPricing = (p, pr) => {
        if (!p) return { base: 'Belum dipilih', hasPromo: false, code: '', disc: 'Tidak Ada', total: 'Belum dipilih', strike: '' };
        const base = Number(p.price_amount || 0);
        const baseLabel = formatRp(base);
        if (!pr) return { base: baseLabel, hasPromo: false, code: '', disc: 'Tidak Ada', total: baseLabel, strike: '' };
        let d = pr.type === 'persentase' ? Math.floor(base * (Number(pr.value || 0) / 100)) : (pr.type === 'nominal' ? Math.round(Number(pr.value || 0)) : 0);
        if (pr.max_discount) d = Math.min(d, Math.round(Number(pr.max_discount)));
        d = Math.max(0, Math.min(base, d));
        const finalTotal = Math.max(0, base - d);
        return {
            base: baseLabel,
            hasPromo: true,
            code: pr.code,
            disc: d > 0 ? `- ${formatRp(d)}` : pr.benefit,
            total: formatRp(finalTotal),
            strike: d > 0 ? baseLabel : '',
        };
    };

    const scopeLabels = { servis: 'Khusus Servis', penjualan_sparepart: 'Khusus Spare Part', trade_in: 'Khusus Trade-In', penjualan_mobil: 'Khusus Mobil' };
    const promoEligibility = pr => {
        if (!pr) return { ok: true, reason: '' };
        if (pr.scope !== 'cuci_mobil' && pr.scope !== 'semua') {
            return { ok: false, reason: scopeLabels[pr.scope] || 'Tidak sesuai layanan' };
        }
        return { ok: true, reason: 'Terklaim' };
    };

    const valid = () => Boolean(vehicleSelect?.value && selectedPkg && selectedDate && selectedTime);

    const updateSummary = () => {
        const v = vehicle(), p = pkg(), pr = promo(), f = finishTime(), pInfo = calcPricing(p, pr);
        const img = $('[data-cwc-sum-img]');
        if (img) img.src = v?.image || '{{ asset('assets/images/hero/yza-hero-reference-v3.png') }}';
        setText('[data-cwc-sum-vehicle]', v?.name || 'Belum memilih kendaraan');
        setText('[data-cwc-sum-meta]', v ? `${v.plate} · ${v.year} · ${v.color}` : 'Pilih kendaraan untuk melihat detailnya.');
        if ($('[data-cwc-sum-verified]')) $('[data-cwc-sum-verified]').hidden = !v?.verified;
        setText('[data-cwc-sum-pkg]', p?.name);
        setText('[data-cwc-sum-date]', formatDate(selectedDate));
        setText('[data-cwc-sum-time]', selectedTime ? `${selectedTime} WIB` : 'Belum dipilih');
        setText('[data-cwc-sum-dur]', p ? formatDur(p.duration) : 'Belum dipilih');
        setText('[data-cwc-sum-finish]', f ? `${f} WIB` : 'Belum dipilih');
        setText('[data-cwc-sum-promo]', pr ? `${pr.code} · ${pr.benefit}` : 'Tidak Pakai Kupon');
        setText('[data-cwc-sum-price]', pInfo.base);
        const dRow = $('[data-cwc-sum-disc-row]'), dCode = $('[data-cwc-sum-disc-code]'), strike = $('[data-cwc-sum-strike]');
        if (dRow) dRow.hidden = !pInfo.hasPromo;
        if (dCode) dCode.textContent = pInfo.code;
        setText('[data-cwc-sum-disc]', pInfo.disc);
        if (strike) { strike.hidden = !pInfo.strike; strike.textContent = pInfo.strike; }
        setText('[data-cwc-sum-total]', pInfo.total);
        const sub = $('[data-cwc-submit]');
        if (sub) sub.disabled = !valid();
    };

    const selectPromo = id => {
        const next = String(id || '');
        if (next && !promoEligibility(promos[next]).ok) return;
        selectedPromo = next;
        if (promoInput) promoInput.value = selectedPromo;
        $$('[data-promo-choice]').forEach(c => {
            const cid = c.dataset.promoChoice;
            const active = cid === selectedPromo;
            c.classList.toggle('is-selected', active);
            const badge = c.querySelector('[data-promo-badge]'), cta = c.querySelector('[data-promo-cta]');
            if (!cid) {
                if (badge) badge.textContent = active ? '✓ Dipilih' : '';
                if (cta) cta.textContent = active ? 'Tarif standar aktif' : 'Gunakan tarif standar';
                return;
            }
            const elig = promoEligibility(promos[cid]);
            if (badge) badge.textContent = active ? '✓ Dipilih' : (elig.ok ? 'Terklaim' : elig.reason);
            if (cta) cta.textContent = active ? 'Kupon digunakan' : (elig.ok ? 'Pakai kupon ini' : 'Tidak berlaku di Car Wash');
        });
        updateSummary();
    };

    const syncPromoEligibility = () => {
        if (selectedPromo && !promoEligibility(promos[selectedPromo]).ok) selectedPromo = '';
        $$('[data-promo-choice]').forEach(c => {
            const cid = c.dataset.promoChoice;
            if (!cid) return;
            const elig = promoEligibility(promos[cid]);
            c.disabled = !elig.ok;
            c.classList.toggle('is-disabled', !elig.ok);
        });
        selectPromo(selectedPromo);
    };

    const shuffleArr = arr => {
        const a = [...arr];
        for (let i = a.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [a[i], a[j]] = [a[j], a[i]];
        }
        return a;
    };

    const renderUnclaimedCoupons = () => {
        const g = $('[data-unclaimed-coupon-grid]'), sec = $('[data-unclaimed-section]');
        if (!g || !sec) return;
        if (!unclaimedPool.length) { sec.hidden = true; return; }
        sec.hidden = false;
        const relevant = [], other = [];
        unclaimedPool.forEach(p => {
            if (promoEligibility(p).ok) relevant.push(p);
            else other.push(p);
        });
        const picked = [...relevant, ...shuffleArr(other)].slice(0, 3);
        g.innerHTML = picked.map(p => `<div class="coupon-card coupon-card-unclaimed" data-unclaimed-card="${p.id}"><div class="coupon-card-top"><span class="coupon-code">${p.code}</span><span class="coupon-badge coupon-badge-points">${p.points_cost > 0 ? `${Number(p.points_cost).toLocaleString('id-ID')} Poin` : 'Gratis'}</span></div><strong class="coupon-benefit">${p.benefit}</strong><span class="coupon-name">${p.name}</span><div class="coupon-card-foot"><button type="button" class="coupon-claim-btn" data-claim-promo="${p.id}" data-claim-url="${p.claim_url}">Klaim &amp; Pakai</button></div></div>`).join('');
        const sum = $('[data-unclaimed-summary]');
        if (sum) {
            sum.textContent = relevant.length
                ? `Menampilkan ${picked.length} kupon relevan (${unclaimedPool.length} belum diklaim).`
                : `Menampilkan ${picked.length} kupon acak dari ${unclaimedPool.length} kupon belum diklaim.`;
        }
    };

    const renderSlots = () => {
        const g = $('[data-cwc-slot-grid]'), pill = $('[data-cwc-date-pill]');
        if (!g) return;
        if (!selectedDate) {
            if (pill) pill.hidden = true;
            g.innerHTML = `<div style="grid-column:1/-1;padding:20px;border:1px dashed #cbd5e1;border-radius:12px;background:#f8fafc;text-align:center;color:#64748b;font-size:12.5px;">Klik salah satu tanggal hijau (Tersedia) pada kalender di atas untuk memilih jam kedatangan.</div>`;
            return;
        }
        if (pill) { pill.hidden = false; pill.textContent = formatDate(selectedDate); }
        const nowMinutes = now.getHours() * 60 + now.getMinutes();
        g.innerHTML = '';
        slotHours.forEach(t => {
            const [sh, sm] = t.split(':').map(Number);
            const isPastToday = selectedDate === todayStr && (sh * 60 + sm) <= nowMinutes;
            const b = document.createElement('button');
            b.type = 'button';
            b.className = `cwc-slot ${selectedTime === t ? 'is-selected' : ''}`;
            b.disabled = isPastToday;
            const p = pkg();
            let endLabel = '';
            if (p) {
                const ed = new Date(2000, 0, 1, sh, sm + (p.duration || 45));
                endLabel = ` – ${String(ed.getHours()).padStart(2, '0')}:${String(ed.getMinutes()).padStart(2, '0')}`;
            }
            b.innerHTML = `<strong>${t}${endLabel} WIB</strong><span>${selectedTime === t ? '✓ Jam dipilih' : (isPastToday ? 'Lewat jam' : 'Tersedia')}</span>`;
            if (!isPastToday) {
                b.addEventListener('click', () => {
                    selectedTime = t;
                    if (timeInput) timeInput.value = t;
                    renderSlots();
                    updateSummary();
                });
            }
            g.append(b);
        });
    };

    const renderCalendar = () => {
        const g = $('[data-cwc-cal-grid]');
        if (!g) return;
        g.innerHTML = '';
        const [y, m] = month.split('-').map(Number);
        const first = (new Date(y, m - 1, 1).getDay() + 6) % 7;
        const total = new Date(y, m, 0).getDate();
        for (let i = 0; i < first; i++) {
            const e = document.createElement('span');
            e.className = 'cwc-day is-empty';
            g.append(e);
        }
        for (let d = 1; d <= total; d++) {
            const val = `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            const dow = new Date(y, m - 1, d).getDay();
            const isWeekend = dow === 0 || dow === 6;
            const isPast = val < todayStr;
            const status = isPast ? 'past' : (isWeekend ? 'closed' : 'available');
            const b = document.createElement('button');
            b.type = 'button';
            b.className = `cwc-day ${selectedDate === val ? 'is-selected' : ''}`;
            b.dataset.status = status;
            b.disabled = status !== 'available';
            b.innerHTML = `<strong>${d}</strong><span>${status === 'available' ? 'Tersedia' : (status === 'past' ? 'Lewat' : 'Tutup')}</span>`;
            if (status === 'available') {
                b.addEventListener('click', () => {
                    selectedDate = val;
                    if (dateInput) dateInput.value = val;
                    if (selectedDate === todayStr && selectedTime) {
                        const [sh, sm] = selectedTime.split(':').map(Number);
                        if ((sh * 60 + sm) <= (now.getHours() * 60 + now.getMinutes())) {
                            selectedTime = '';
                            if (timeInput) timeInput.value = '';
                        }
                    }
                    renderCalendar();
                    renderSlots();
                    updateSummary();
                });
            }
            g.append(b);
        }
        setText('[data-cwc-month-label]', monthLabel(month));
        if ($('[data-cwc-month-prev]')) $('[data-cwc-month-prev]').disabled = month <= root.dataset.currentMonth;
    };

    vehicleSelect?.addEventListener('change', e => {
        if (vehicleInput) vehicleInput.value = e.target.value;
        updateSummary();
    });

    $$('[data-cwc-pkg-choice]').forEach(btn => {
        btn.addEventListener('click', () => {
            selectedPkg = String(btn.dataset.cwcPkgChoice || '');
            if (pkgInput) pkgInput.value = selectedPkg;
            $$('[data-cwc-pkg-choice]').forEach(c => {
                const active = c === btn;
                c.classList.toggle('is-selected', active);
                const b = c.querySelector('[data-cwc-pkg-badge]');
                if (b) b.textContent = active ? '✓ Paket Dipilih' : 'Pilih Paket';
            });
            renderSlots();
            updateSummary();
        });
    });

    $('[data-cwc-month-prev]')?.addEventListener('click', () => {
        if (month <= root.dataset.currentMonth) return;
        const [y, m] = month.split('-').map(Number);
        const d = new Date(y, m - 2, 1);
        month = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
        renderCalendar();
    });

    $('[data-cwc-month-next]')?.addEventListener('click', () => {
        const [y, m] = month.split('-').map(Number);
        const d = new Date(y, m, 1);
        month = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
        renderCalendar();
    });

    $('[data-claimed-coupon-grid]')?.addEventListener('click', e => {
        const btn = e.target.closest('[data-promo-choice]');
        if (btn && !btn.disabled) selectPromo(btn.dataset.promoChoice);
    });

    $('[data-unclaimed-coupon-grid]')?.addEventListener('click', async e => {
        const btn = e.target.closest('[data-claim-promo]');
        if (!btn) return;
        const id = btn.dataset.claimPromo, url = btn.dataset.claimUrl, fb = $('[data-coupon-feedback]'), csrf = document.querySelector('input[name="_token"]')?.value || '';
        btn.disabled = true;
        btn.textContent = 'Mengklaim...';
        if (fb) { fb.hidden = true; fb.classList.remove('error'); }
        try {
            const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            if (!res.ok || data.success === false) throw new Error(data.message || 'Gagal mengklaim kupon.');
            const p = promos[id], claimedGrid = $('[data-claimed-coupon-grid]');
            if (p && claimedGrid && !$(`[data-promo-choice="${id}"]`)) {
                const card = document.createElement('button');
                card.type = 'button';
                card.className = 'coupon-card';
                card.dataset.promoChoice = id;
                card.innerHTML = `<div class="coupon-card-top"><span class="coupon-code">${p.code}</span><span class="coupon-badge" data-promo-badge>Terklaim</span></div><strong class="coupon-benefit">${p.benefit}</strong><span class="coupon-name">${p.name}</span><div class="coupon-card-foot"><span class="coupon-cta-text" data-promo-cta>Pakai kupon ini</span><span class="coupon-radio" aria-hidden="true"></span></div>`;
                claimedGrid.append(card);
            }
            unclaimedPool = unclaimedPool.filter(x => x.id !== id);
            renderUnclaimedCoupons();
            syncPromoEligibility();
            if (promoEligibility(p).ok) selectPromo(id);
            if (fb) { fb.textContent = data.message || 'Kupon berhasil diklaim.'; fb.hidden = false; }
        } catch (err) {
            btn.disabled = false;
            btn.textContent = 'Klaim & Pakai';
            if (fb) { fb.textContent = err.message || 'Gagal mengklaim kupon.'; fb.classList.add('error'); fb.hidden = false; }
        }
    });

    const confirmModal = $('[data-cwc-confirm-modal]');
    const form = $('[data-cwc-form]');
    const closeConfirm = () => { if (!confirmModal) return; confirmModal.hidden = true; document.body.style.overflow = ''; };
    const openConfirm = () => {
        if (!confirmModal || !valid()) return;
        const v = vehicle(), p = pkg(), pr = promo(), f = finishTime(), pInfo = calcPricing(p, pr);
        setText('[data-cwc-m-vehicle]', v ? `${v.name} · ${v.plate}` : '—');
        setText('[data-cwc-m-pkg]', p?.name);
        setText('[data-cwc-m-dur]', p ? formatDur(p.duration) : '—');
        setText('[data-cwc-m-schedule]', `${formatDate(selectedDate)} · ${selectedTime}${f ? ` – ${f}` : ''} WIB`);
        setText('[data-cwc-m-promo]', pr ? `${pr.code} · ${pr.benefit}` : 'Tidak Pakai Kupon');
        setText('[data-cwc-m-price]', pInfo.base);
        const dRow = $('[data-cwc-m-disc-row]'), strike = $('[data-cwc-m-strike]');
        if (dRow) dRow.hidden = !pInfo.hasPromo;
        setText('[data-cwc-m-disc]', pInfo.disc);
        if (strike) { strike.hidden = !pInfo.strike; strike.textContent = pInfo.strike; }
        setText('[data-cwc-m-total]', pInfo.total);
        confirmModal.hidden = false;
        document.body.style.overflow = 'hidden';
    };

    $('[data-cwc-submit]')?.addEventListener('click', openConfirm);
    $$('[data-cwc-modal-close]').forEach(b => b.addEventListener('click', closeConfirm));
    confirmModal?.addEventListener('click', e => { if (e.target === confirmModal) closeConfirm(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && confirmModal && !confirmModal.hidden) closeConfirm(); });
    $('[data-cwc-modal-submit]')?.addEventListener('click', e => {
        const b = e.currentTarget;
        b.disabled = true;
        b.textContent = 'Mengirim...';
        form?.submit();
    });

    syncPromoEligibility();
    renderUnclaimedCoupons();
    renderCalendar();
    renderSlots();
    updateSummary();
})();
</script>
@endpush
