@extends('layouts.app')

@section('title', 'Booking Servis Saya · YZA Automotive')

@php
    $statusMeta = [
        'menunggu' => ['label' => 'Menunggu Konfirmasi YZA', 'short' => 'Menunggu Konfirmasi', 'tone' => 'amber', 'group' => 'active', 'stage' => 2],
        'diterima' => ['label' => 'Booking Dikonfirmasi', 'short' => 'Dikonfirmasi', 'tone' => 'emerald', 'group' => 'active', 'stage' => 3],
        'diproses' => ['label' => 'Servis Sedang Diproses', 'short' => 'Sedang Diproses', 'tone' => 'blue', 'group' => 'active', 'stage' => 3],
        'selesai' => ['label' => 'Servis Selesai', 'short' => 'Selesai', 'tone' => 'emerald', 'group' => 'completed', 'stage' => 3],
        'ditolak' => ['label' => 'Booking Ditolak', 'short' => 'Ditolak', 'tone' => 'rose', 'group' => 'cancelled', 'stage' => 2],
        'dibatalkan' => ['label' => 'Dibatalkan', 'short' => 'Dibatalkan', 'tone' => 'slate', 'group' => 'cancelled', 'stage' => 2],
    ];

    $formatBookingPricing = function ($b) {
        $base = (float) ($b->jenisServis?->estimasi_harga ?? 0);
        $baseLabel = $base > 0 ? 'Rp ' . number_format($base, 0, ',', '.') : 'Sesuai Hasil Cek';
        $p = $b->promo;
        if (!$p) {
            return [
                'base' => $baseLabel,
                'has_promo' => false,
                'promo_code' => '',
                'promo_label' => 'Tidak Pakai Kupon',
                'discount' => 'Tidak Ada',
                'total' => $baseLabel,
                'strike' => '',
            ];
        }
        $benefitLabel = $p->jenis_benefit === 'persentase'
            ? 'Diskon ' . rtrim(rtrim(number_format((float) $p->nilai_benefit, 2, ',', '.'), '0'), ',') . '%'
            : ($p->jenis_benefit === 'nominal' ? 'Potongan Rp ' . number_format((float) $p->nilai_benefit, 0, ',', '.') : 'Benefit Member');
        $promoFull = $p->kode_promo . ' · ' . $benefitLabel;
        if ($base <= 0) {
            return [
                'base' => $baseLabel,
                'has_promo' => true,
                'promo_code' => $p->kode_promo,
                'promo_label' => $promoFull,
                'discount' => $benefitLabel,
                'total' => $baseLabel . ' (' . $benefitLabel . ')',
                'strike' => '',
            ];
        }
        $disc = $p->jenis_benefit === 'persentase'
            ? floor($base * ((float) $p->nilai_benefit / 100))
            : ($p->jenis_benefit === 'nominal' ? round((float) $p->nilai_benefit) : 0);
        if ($p->maksimum_benefit !== null && (float) $p->maksimum_benefit > 0) {
            $disc = min($disc, round((float) $p->maksimum_benefit));
        }
        $disc = max(0, min($base, $disc));
        $final = max(0, $base - $disc);
        return [
            'base' => $baseLabel,
            'has_promo' => true,
            'promo_code' => $p->kode_promo,
            'promo_label' => $promoFull,
            'discount' => $disc > 0 ? '- Rp ' . number_format($disc, 0, ',', '.') : $benefitLabel,
            'total' => 'Rp ' . number_format($final, 0, ',', '.'),
            'strike' => $disc > 0 ? $baseLabel : '',
        ];
    };

    $mapBookingItem = function ($b) use ($statusMeta, $formatBookingPricing) {
        $st = $b->status;
        $meta = $statusMeta[$st] ?? ['label' => ucfirst($st), 'short' => ucfirst($st), 'tone' => 'slate', 'group' => 'active', 'stage' => 2];
        $pricing = $formatBookingPricing($b);
        $dur = max(1, (int) ($b->jenisServis?->duration_minutes ?? 60));
        $durLabel = $dur >= 60 ? '± ' . intdiv($dur, 60) . ' jam' . ($dur % 60 ? ' ' . ($dur % 60) . ' menit' : '') : '± ' . $dur . ' menit';
        $timeRaw = substr((string) $b->jam, 0, 5);
        $assignedTech = $b->servis?->petugas;
        $techName = $assignedTech?->nama_lengkap ?: ($b->preferredPetugas?->nama_lengkap ?: 'Tidak Ada Preferensi');
        $techDesc = $assignedTech
            ? 'Teknisi pelaksana yang ditugaskan oleh Service Advisor untuk menangani kendaraan Anda.'
            : ($b->preferredPetugas
                ? 'Preferensi teknisi member. Penugasan teknisi final dilakukan oleh YZA Automotive.'
                : 'YZA Automotive akan menentukan teknisi yang sesuai.');

        $pendingApprovalModel = $b->servis?->approvalRequests?->firstWhere('status', 'pending');
        $pendingApproval = null;
        if ($pendingApprovalModel && $b->servis) {
            $approvalItems = $pendingApprovalModel->items->map(fn ($it) => [
                'description' => $it->description,
                'type' => $it->item_type === 'sparepart' ? 'Sparepart' : 'Jasa',
                'quantity' => (int) $it->quantity,
                'total' => 'Rp ' . number_format((float) $it->total_snapshot, 0, ',', '.'),
            ])->values()->all();
            $approvalTotal = (float) $pendingApprovalModel->items->sum('total_snapshot');
            $pendingApproval = [
                'title' => $pendingApprovalModel->title,
                'reason' => $pendingApprovalModel->reason,
                'items' => $approvalItems,
                'total' => 'Rp ' . number_format($approvalTotal, 0, ',', '.'),
                'decision_url' => route('pelanggan.servis.approvals.decision', [$b->servis, $pendingApprovalModel]),
            ];
        }

        return [
            'id' => (string) $b->getKey(),
            'code' => $b->kode_booking,
            'status' => $st,
            'status_label' => $pendingApproval ? 'Menunggu Konfirmasi Anda' : $meta['label'],
            'status_short' => $pendingApproval ? 'Perlu Konfirmasi' : $meta['short'],
            'tone' => $pendingApproval ? 'amber' : $meta['tone'],
            'group' => $meta['group'],
            'stage' => $meta['stage'],
            'vehicle' => trim(($b->kendaraan?->merk ?: '') . ' ' . ($b->kendaraan?->model ?: '')) ?: 'Belum diisi',
            'plate' => $b->kendaraan?->nomor_polisi ?: 'Belum diisi',
            'mileage' => number_format((int) ($b->kendaraan?->kilometer ?? 0), 0, ',', '.') . ' km',
            'service' => $b->jenisServis?->nama_servis ?: '—',
            'duration' => $durLabel,
            'date' => $b->tanggal?->translatedFormat('l, d F Y') ?: '—',
            'date_short' => $b->tanggal?->translatedFormat('d M Y') ?: '—',
            'time' => $timeRaw . ' WIB',
            'submitted_at' => $b->created_at?->translatedFormat('d F Y, H:i') ?: 'Baru saja',
            'technician' => $techName,
            'technician_desc' => $techDesc,
            'complaint' => $b->keluhan ?: 'Tidak ada keluhan khusus',
            'note' => $b->catatan ?: 'Tidak ada catatan tambahan',
            'can_cancel' => in_array($st, ['menunggu', 'diterima'], true),
            'cancel_url' => route('pelanggan.booking-servis.cancel', $b),
            'pricing' => $pricing,
            'pending_approval' => $pendingApproval,
        ];
    };

    $collection = $rows->getCollection();
    $bookingsMap = $collection->mapWithKeys(fn($b) => [(string) $b->getKey() => $mapBookingItem($b)]);
    $activeBookingData = isset($activeBooking) && $activeBooking ? $mapBookingItem($activeBooking) : null;
    if ($activeBookingData && !$bookingsMap->has($activeBookingData['id'])) {
        $bookingsMap->put($activeBookingData['id'], $activeBookingData);
    }
    $initialModalItem = $activeBookingData ?: $bookingsMap->first();

    $countAll = $collection->count();
    $countActive = $collection->whereIn('status', ['menunggu', 'diterima', 'diproses'])->count();
    $countDone = $collection->where('status', 'selesai')->count();
    $countCancelled = $collection->whereIn('status', ['dibatalkan', 'ditolak'])->count();
@endphp

@push('styles')
<style>
.member-horizontal-shell .member-main-content:has(.bks-page){padding:0!important;max-width:none!important}
.bks-page{width:min(96vw,1640px);margin:0 auto;padding:0 0 64px;color:#0f172a}
.bks-hero{position:relative;min-height:210px;display:flex;justify-content:space-between;align-items:flex-end;gap:24px;padding:36px clamp(24px,4vw,56px) 30px;margin-bottom:22px;border-radius:0 0 22px 22px;overflow:hidden;color:#fff;background:linear-gradient(90deg,rgba(7,13,21,.97) 0%,rgba(15,17,23,.86) 46%,rgba(19,17,23,.28) 100%),url('/assets/images/hero/yza-detail-red-generated.png') right center/cover;border-bottom:3px solid #e31d35;box-shadow:0 16px 36px -14px rgba(15,23,42,.22)}
.bks-hero-copy{position:relative;z-index:1;max-width:720px}
.bks-eyebrow{display:inline-flex;align-items:center;gap:7px;margin-bottom:8px;color:#ff2d46;font-size:11px;font-weight:900;letter-spacing:.18em;text-transform:uppercase}
.bks-eyebrow::before{content:'';width:6px;height:6px;border-radius:999px;background:#ff2d46}
.bks-hero h1{margin:0;color:#fff;font-size:clamp(32px,3.5vw,50px);font-weight:900;letter-spacing:-.04em;line-height:1.05}
.bks-hero p{margin:8px 0 0;color:#cbd5e1;font-size:14.5px;line-height:1.5}
.bks-hero-actions{position:relative;z-index:1;display:flex;align-items:center;gap:10px;flex-shrink:0}
.bks-create-btn,.bks-create-btn:visited{display:inline-flex;align-items:center;gap:8px;min-height:46px;padding:0 22px;border:1px solid rgba(255,255,255,.22);border-radius:11px;background:#e31d35!important;color:#fff!important;font-size:13.5px;font-weight:800;text-decoration:none!important;box-shadow:0 10px 24px -6px rgba(0,0,0,.45),0 6px 16px rgba(227,29,53,.4);transition:transform .16s ease,background .16s ease,box-shadow .16s ease}
.bks-create-btn:hover,.bks-create-btn:focus-visible{background:#be123c!important;color:#fff!important;transform:translateY(-1px);box-shadow:0 14px 28px -6px rgba(0,0,0,.5),0 8px 20px rgba(227,29,53,.55)}

.bks-alert{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px;padding:13px 18px;border-radius:12px;font-size:13.5px;font-weight:700}
.bks-alert.success{background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d}
.bks-alert.error{background:#fff1f2;border:1px solid #fecdd3;color:#be123c}

.bks-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:16px;padding:12px 16px;border:1px solid #e2e8f0;border-radius:14px;background:#fff;box-shadow:0 4px 16px rgba(15,23,42,.03)}
.bks-tabs{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
.bks-tab{display:inline-flex;align-items:center;gap:7px;height:36px;padding:0 13px;border:1px solid transparent;border-radius:9px;background:transparent;color:#64748b;font:inherit;font-size:12.5px;font-weight:700;cursor:pointer;transition:all .15s ease}
.bks-tab:hover{color:#0f172a;background:#f8fafc}
.bks-tab.is-active{border-color:#e2e8f0;background:#0f172a;color:#fff}
.bks-tab-count{display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:19px;padding:0 6px;border-radius:999px;background:#f1f5f9;color:#475569;font-size:10.5px;font-weight:800}
.bks-tab.is-active .bks-tab-count{background:rgba(255,255,255,.18);color:#fff}
.bks-search{position:relative;min-width:260px;flex:0 1 320px}
.bks-search svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none}
.bks-search input{width:100%;height:38px;padding:0 12px 0 35px;border:1px solid #e2e8f0;border-radius:9px;background:#f8fafc;color:#0f172a;font:inherit;font-size:12.5px;outline:0;transition:border-color .15s ease,background .15s ease}
.bks-search input:focus{border-color:#cbd5e1;background:#fff}

.bks-card{border:1px solid #e2e8f0;border-radius:16px;background:#fff;box-shadow:0 10px 28px -12px rgba(15,23,42,.05);overflow:hidden}
.bks-table-wrap{width:100%;overflow-x:auto}
.bks-table{width:100%;border-collapse:collapse;text-align:left}
.bks-table th{padding:13px 20px;border-bottom:1px solid #f1f5f9;background:#f8fafc;color:#64748b;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;white-space:nowrap}
.bks-table td{padding:16px 20px;border-bottom:1px solid #f1f5f9;vertical-align:middle;font-size:13.5px;color:#0f172a}
.bks-table tbody tr{transition:background .14s ease}
.bks-table tbody tr:hover{background:#fcfdfe}
.bks-table tbody tr:last-child td{border-bottom:0}

.bks-code-cell{display:grid;gap:4px;justify-items:start}
.bks-code-badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:7px;border:1px solid #e2e8f0;background:#f8fafc;color:#0f172a;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11.5px;font-weight:800;letter-spacing:.03em}
.bks-subtext{color:#64748b;font-size:11.5px;line-height:1.35}

.bks-vehicle-cell{display:grid;gap:3px}
.bks-vehicle-cell strong{font-size:14px;font-weight:800;color:#0f172a}

.bks-service-cell{display:grid;gap:3px;justify-items:start}
.bks-service-cell strong{font-size:13.5px;font-weight:700;color:#0f172a}
.bks-service-meta{display:inline-flex;align-items:center;gap:6px;flex-wrap:wrap;font-size:11.5px;color:#64748b}
.bks-promo-tag{display:inline-flex;align-items:center;padding:1px 6px;border-radius:5px;background:#ecfdf5;border:1px solid #bbf7d0;color:#059669;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:10px;font-weight:700}

.bks-schedule-cell{display:grid;gap:3px}
.bks-schedule-cell strong{font-size:13.5px;font-weight:700;color:#0f172a}
.bks-time-pill{display:inline-flex;align-items:center;gap:5px;color:#475569;font-size:12px;font-weight:600}

.bks-status{display:inline-flex;align-items:center;gap:6px;padding:5px 11px;border-radius:999px;font-size:11.5px;font-weight:800;white-space:nowrap;border:1px solid transparent}
.bks-status::before{content:'';width:6px;height:6px;border-radius:999px;background:currentColor}
.bks-status[data-tone="amber"]{background:#fffbeb;border-color:#fde68a;color:#b45309}
.bks-status[data-tone="emerald"]{background:#ecfdf5;border-color:#bbf7d0;color:#047857}
.bks-status[data-tone="blue"]{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8}
.bks-status[data-tone="rose"]{background:#fff1f2;border-color:#fecdd3;color:#be123c}
.bks-status[data-tone="slate"]{background:#f8fafc;border-color:#e2e8f0;color:#64748b}

.bks-actions{display:flex;align-items:center;gap:8px;white-space:nowrap}
.bks-btn,.bks-btn:visited{display:inline-flex;align-items:center;justify-content:center;gap:6px;height:34px;padding:0 13px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;color:#0f172a!important;font:inherit;font-size:12px;font-weight:700;cursor:pointer;text-decoration:none!important;transition:all .15s ease}
.bks-btn:hover{border-color:#0f172a;background:#0f172a;color:#fff!important}
.bks-btn-danger,.bks-btn-danger:visited{border-color:#fecdd3;background:#fff;color:#be123c!important}
.bks-btn-danger:hover{border-color:#e31d35;background:#fff1f2;color:#be123c!important}

.bks-empty{padding:48px 24px;text-align:center;color:#64748b}
.bks-empty strong{display:block;margin-bottom:6px;color:#0f172a;font-size:16px;font-weight:800}
.bks-empty p{margin:0 0 18px;font-size:13px}

/* Detail Booking Servis Modal */
.bks-modal-overlay{position:fixed;inset:0;z-index:1200;display:grid;place-items:center;padding:20px;background:rgba(15,23,42,.58);backdrop-filter:blur(4px)}
.bks-modal-overlay[hidden]{display:none!important}
.bks-modal{width:min(680px,100%);max-height:90vh;display:flex;flex-direction:column;border:1px solid #e2e8f0;border-radius:20px;background:#fff;box-shadow:0 28px 64px -12px rgba(15,23,42,.28);overflow:hidden}
.bks-modal-hero{position:relative;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:22px 26px;color:#fff;background:linear-gradient(90deg,rgba(7,13,21,.97) 0%,rgba(15,17,23,.88) 52%,rgba(19,17,23,.38) 100%),url('/assets/images/hero/yza-detail-red-generated.png') right center/cover;border-bottom:2px solid #e31d35}
.bks-modal-hero-left{display:flex;align-items:center;gap:16px;min-width:0}
.bks-modal-check{display:grid;place-items:center;width:48px;height:48px;flex-shrink:0;border-radius:50%;border:3px solid rgba(255,255,255,.32);background:#10b981;color:#fff;font-size:22px;font-weight:900;box-shadow:0 0 0 6px rgba(16,185,129,.16)}
.bks-modal-hero-copy{min-width:0}
.bks-modal-hero-copy h2{margin:0;color:#fff;font-size:21px;font-weight:900;letter-spacing:-.02em}
.bks-modal-hero-copy p{margin:3px 0 8px;color:#cbd5e1;font-size:12.5px}
.bks-modal-code-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.bks-modal-code{display:inline-flex;padding:4px 10px;border:1px solid #ff2d46;border-radius:7px;background:rgba(15,23,42,.65);color:#ff4d63;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11.5px;font-weight:800;letter-spacing:.06em}
.bks-modal-close{display:grid;place-items:center;width:34px;height:34px;flex-shrink:0;align-self:flex-start;border:1px solid rgba(255,255,255,.22);border-radius:9px;background:rgba(15,23,42,.45);color:#fff;font-size:20px;line-height:1;cursor:pointer;transition:all .15s ease}
.bks-modal-close:hover{border-color:#fff;background:rgba(255,255,255,.15)}

.bks-modal-body{padding:20px 24px;overflow-y:auto;display:grid;gap:16px}
.bks-timeline{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;padding:13px 16px;border:1px solid #e2e8f0;border-radius:13px;background:#f8fafc}
.bks-timeline-step{display:flex;align-items:center;gap:10px;min-width:0}
.bks-timeline-dot{display:grid;place-items:center;width:26px;height:26px;flex-shrink:0;border-radius:50%;border:1.5px solid #cbd5e1;background:#fff;color:#64748b;font-size:11px;font-weight:800}
.bks-timeline-step.is-done .bks-timeline-dot{border-color:#e31d35;background:#e31d35;color:#fff}
.bks-timeline-step.is-current .bks-timeline-dot{border-color:#f59e0b;background:#f59e0b;color:#fff}
.bks-timeline-copy{display:grid;min-width:0}
.bks-timeline-copy strong{color:#0f172a;font-size:12px;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.bks-timeline-copy small{color:#64748b;font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

.bks-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.bks-detail-box{padding:15px 16px;border:1px solid #e2e8f0;border-radius:13px;background:#fff}
.bks-detail-box h3{margin:0 0 10px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;color:#e31d35;font-size:11.5px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
.bks-dl{display:grid;gap:8px;margin:0}
.bks-dl div{display:flex;align-items:baseline;justify-content:space-between;gap:12px;font-size:12.5px}
.bks-dl dt{color:#64748b;font-weight:500}
.bks-dl dd{margin:0;color:#0f172a;font-weight:700;text-align:right;overflow-wrap:anywhere}

.bks-pref-box{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:13px 16px;border:1px solid #e2e8f0;border-radius:13px;background:#fcfcfd}
.bks-pref-box h4{margin:0 0 3px;color:#0f172a;font-size:13px;font-weight:800}
.bks-pref-box p{margin:0;color:#64748b;font-size:11.5px;line-height:1.4}
.bks-pref-val{flex-shrink:0;padding:5px 10px;border-radius:8px;background:#fff1f2;border:1px solid #fecdd3;color:#be123c;font-size:12px;font-weight:800}

.bks-pricing-box{padding:14px 16px;border:1px solid #e2e8f0;border-radius:13px;background:#f8fafc;display:grid;gap:7px}
.bks-price-row{display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:12.5px}
.bks-price-row[hidden]{display:none!important}
.bks-price-row span{color:#64748b;font-weight:600}
.bks-price-row strong{color:#0f172a;font-weight:700}
.bks-price-row.is-discount span,.bks-price-row.is-discount strong{color:#059669;font-weight:800}
.bks-price-total{display:flex;align-items:baseline;justify-content:space-between;gap:10px;padding-top:8px;margin-top:2px;border-top:1px dashed #cbd5e1}
.bks-price-total>span{color:#0f172a;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
.bks-price-total-val{display:flex;align-items:baseline;gap:7px}
.bks-price-total-val del{color:#94a3b8;font-size:11.5px}
.bks-price-total-val strong{color:#e31d35;font-size:15.5px;font-weight:800}

.bks-modal-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 24px;border-top:1px solid #f1f5f9;background:#f8fafc}
.bks-approval-box{padding:16px 18px;border:1px solid #fecdd3;border-radius:14px;background:#fff1f2;display:grid;gap:10px}
.bks-approval-box[hidden]{display:none!important}
.bks-approval-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
.bks-approval-tag{display:inline-block;padding:2px 8px;border-radius:999px;background:#e31d35;color:#fff;font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px}
.bks-approval-head h3{margin:0;font-size:15px;font-weight:800;color:#0f172a}
.bks-approval-total{font-size:15px;font-weight:900;color:#be123c;white-space:nowrap}
.bks-approval-reason{margin:0;font-size:12.5px;color:#334155;line-height:1.45}
.bks-approval-items{display:grid;gap:6px;padding:10px 12px;border-radius:10px;background:#fff;border:1px solid #ffe4e6}
.bks-approval-item{display:flex;justify-content:space-between;gap:10px;font-size:12.5px;color:#0f172a}
.bks-approval-item small{color:#64748b}
.bks-approval-form{display:grid;gap:10px}
.bks-approval-form input{height:38px;padding:0 12px;border:1px solid #fecdd3;border-radius:9px;background:#fff;font:inherit;font-size:12.5px;color:#0f172a;outline:0}
.bks-approval-actions{display:flex;justify-content:flex-end;gap:8px;flex-wrap:wrap}
.bks-modal-foot-right{display:flex;align-items:center;gap:8px;margin-left:auto}

@media(max-width:760px){
  .bks-hero{flex-direction:column;align-items:flex-start;padding:28px 20px 24px}
  .bks-detail-grid,.bks-timeline{grid-template-columns:1fr}
  .bks-pref-box{flex-direction:column;align-items:flex-start}
  .bks-search{flex:1 1 100%}
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
<div class="bks-page" data-bks-page>
    <header class="bks-hero">
        <div class="bks-hero-copy">
            <span class="bks-eyebrow">YZA SERVICE</span>
            <h1>Booking Servis Saya</h1>
            <p>Atur jadwal kedatangan, pantau konfirmasi bengkel, dan periksa detail servis kendaraan Anda.</p>
        </div>
        <div class="bks-hero-actions">
            <a class="bks-create-btn" href="{{ route('pelanggan.booking-servis.create') }}">+ Booking Servis</a>
        </div>
    </header>

    @if(session('success'))
        <div class="bks-alert success" role="status">
            <span>✓ {{ session('success') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="bks-alert error" role="alert">
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="bks-toolbar">
        <div class="bks-tabs" role="tablist" aria-label="Filter status booking">
            <button type="button" class="bks-tab is-active" data-bks-filter="all">Semua <span class="bks-tab-count">{{ $countAll }}</span></button>
            <button type="button" class="bks-tab" data-bks-filter="active">Aktif &amp; Menunggu <span class="bks-tab-count">{{ $countActive }}</span></button>
            <button type="button" class="bks-tab" data-bks-filter="completed">Selesai <span class="bks-tab-count">{{ $countDone }}</span></button>
            <button type="button" class="bks-tab" data-bks-filter="cancelled">Dibatalkan <span class="bks-tab-count">{{ $countCancelled }}</span></button>
        </div>
        <div class="bks-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="search" placeholder="Cari kode booking, kendaraan, servis..." data-bks-search>
        </div>
    </div>

    <div class="bks-card">
        <div class="bks-table-wrap">
            <table class="bks-table">
                <thead>
                    <tr>
                        <th>Kode Booking</th>
                        <th>Kendaraan</th>
                        <th>Jenis Servis</th>
                        <th>Jadwal Kedatangan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody data-bks-tbody>
                    @forelse($rows as $b)
                        @php $item = $bookingsMap[(string) $b->getKey()]; @endphp
                        <tr data-bks-row data-group="{{ $item['group'] }}" data-search="{{ strtolower($item['code'] . ' ' . $item['vehicle'] . ' ' . $item['plate'] . ' ' . $item['service'] . ' ' . $item['status_label']) }}">
                            <td>
                                <div class="bks-code-cell">
                                    <span class="bks-code-badge">{{ $item['code'] }}</span>
                                    <span class="bks-subtext">Diajukan {{ $item['submitted_at'] }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="bks-vehicle-cell">
                                    <strong>{{ $item['vehicle'] }}</strong>
                                    <span class="bks-subtext">{{ $item['plate'] }} · {{ $item['mileage'] }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="bks-service-cell">
                                    <strong>{{ $item['service'] }}</strong>
                                    <span class="bks-service-meta">
                                        <span>{{ $item['pricing']['total'] }}</span>
                                        @if($item['pricing']['has_promo'])
                                            <span class="bks-promo-tag">{{ $item['pricing']['promo_code'] }}</span>
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="bks-schedule-cell">
                                    <strong>{{ $item['date'] }}</strong>
                                    <span class="bks-time-pill">{{ $item['time'] }} · {{ $item['duration'] }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="bks-status" data-tone="{{ $item['tone'] }}">{{ $item['status_short'] }}</span>
                            </td>
                            <td>
                                <div class="bks-actions">
                                    @if(!empty($item['pending_approval']))
                                        <button type="button" class="bks-btn" style="background:#e31d35;border-color:#e31d35;color:#fff;" data-open-detail="{{ $item['id'] }}">Konfirmasi</button>
                                    @else
                                        <button type="button" class="bks-btn" data-open-detail="{{ $item['id'] }}">Detail</button>
                                    @endif
                                    @if($item['can_cancel'])
                                        <form method="post" action="{{ $item['cancel_url'] }}" data-cxc-form data-cxc-code="{{ $item['code'] }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="bks-btn bks-btn-danger">Batal</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="bks-empty">
                                    <strong>Belum Ada Booking Servis</strong>
                                    <p>Jadwalkan servis berkala untuk menjaga performa kendaraan Anda tetap prima.</p>
                                    <a class="bks-create-btn" href="{{ route('pelanggan.booking-servis.create') }}">+ Buat Booking Servis</a>
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

    {{-- Modal Detail Booking Servis --}}
    <div class="bks-modal-overlay" data-booking-detail-modal {{ $activeBookingData ? '' : 'hidden' }} role="dialog" aria-modal="true" aria-labelledby="bks-modal-title">
        <div class="bks-modal">
            <header class="bks-modal-hero">
                <div class="bks-modal-hero-left">
                    <span class="bks-modal-check" aria-hidden="true">✓</span>
                    <div class="bks-modal-hero-copy">
                        <h2 id="bks-modal-title" data-modal-heading>{{ session('success') && $activeBookingData ? 'Booking Servis Berhasil Diajukan' : 'Detail Booking Servis' }}</h2>
                        <p>Booking Anda tercatat dan akan diproses oleh tim YZA Automotive.</p>
                        <div class="bks-modal-code-row">
                            <span class="bks-modal-code" data-modal-code>{{ $initialModalItem['code'] ?? '—' }}</span>
                            <span class="bks-status" data-modal-status-badge data-tone="{{ $initialModalItem['tone'] ?? 'amber' }}">{{ $initialModalItem['status_label'] ?? 'Menunggu Konfirmasi YZA' }}</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="bks-modal-close" data-close-detail aria-label="Tutup modal">&times;</button>
            </header>

            <div class="bks-modal-body">
                <div class="bks-timeline" aria-label="Tahapan status booking">
                    <div class="bks-timeline-step is-done">
                        <span class="bks-timeline-dot">✓</span>
                        <div class="bks-timeline-copy">
                            <strong>Diajukan</strong>
                            <small data-modal-submitted>{{ $initialModalItem['submitted_at'] ?? '—' }}</small>
                        </div>
                    </div>
                    <div class="bks-timeline-step {{ ($initialModalItem['stage'] ?? 2) === 2 ? 'is-current' : 'is-done' }}" data-modal-step-2>
                        <span class="bks-timeline-dot">02</span>
                        <div class="bks-timeline-copy">
                            <strong>Konfirmasi Bengkel</strong>
                            <small>Tim YZA Automotive</small>
                        </div>
                    </div>
                    <div class="bks-timeline-step {{ ($initialModalItem['stage'] ?? 2) >= 3 ? 'is-current' : '' }}" data-modal-step-3>
                        <span class="bks-timeline-dot">03</span>
                        <div class="bks-timeline-copy">
                            <strong>Dijadwalkan</strong>
                            <small>Sesuai slot kedatangan</small>
                        </div>
                    </div>
                </div>

                <div class="bks-detail-grid">
                    <section class="bks-detail-box">
                        <h3>Kendaraan</h3>
                        <dl class="bks-dl">
                            <div><dt>Kendaraan</dt><dd data-modal-vehicle>{{ $initialModalItem['vehicle'] ?? '—' }}</dd></div>
                            <div><dt>No. Polisi</dt><dd data-modal-plate>{{ $initialModalItem['plate'] ?? '—' }}</dd></div>
                            <div><dt>Kilometer Terakhir</dt><dd data-modal-mileage>{{ $initialModalItem['mileage'] ?? '—' }}</dd></div>
                        </dl>
                    </section>

                    <section class="bks-detail-box">
                        <h3>Jadwal &amp; Servis</h3>
                        <dl class="bks-dl">
                            <div><dt>Jenis Servis</dt><dd data-modal-service>{{ $initialModalItem['service'] ?? '—' }}</dd></div>
                            <div><dt>Tanggal</dt><dd data-modal-date>{{ $initialModalItem['date'] ?? '—' }}</dd></div>
                            <div><dt>Waktu Kedatangan</dt><dd data-modal-time>{{ $initialModalItem['time'] ?? '—' }}</dd></div>
                            <div><dt>Estimasi Durasi</dt><dd data-modal-duration>{{ $initialModalItem['duration'] ?? '—' }}</dd></div>
                        </dl>
                    </section>
                </div>

                <section class="bks-pref-box">
                    <div>
                        <h4>Preferensi Teknisi</h4>
                        <p data-modal-tech-desc>{{ $initialModalItem['technician_desc'] ?? 'Preferensi teknisi member. Penugasan teknisi final dilakukan oleh YZA Automotive.' }}</p>
                    </div>
                    <span class="bks-pref-val" data-modal-tech>{{ $initialModalItem['technician'] ?? 'Tidak Ada Preferensi' }}</span>
                </section>

                <div class="bks-detail-grid">
                    <section class="bks-detail-box">
                        <h3>Keluhan Kendaraan</h3>
                        <p style="margin:0;color:#334155;font-size:12.5px;line-height:1.5;" data-modal-complaint>{{ $initialModalItem['complaint'] ?? 'Tidak ada keluhan khusus' }}</p>
                    </section>
                    <section class="bks-detail-box">
                        <h3>Catatan Tambahan</h3>
                        <p style="margin:0;color:#334155;font-size:12.5px;line-height:1.5;" data-modal-note>{{ $initialModalItem['note'] ?? 'Tidak ada catatan tambahan' }}</p>
                    </section>
                </div>

                <section class="bks-approval-box" data-modal-approval-box {{ empty($initialModalItem['pending_approval']) ? 'hidden' : '' }}>
                    <div class="bks-approval-head">
                        <div>
                            <span class="bks-approval-tag">Butuh Konfirmasi Anda</span>
                            <h3 data-modal-approval-title>{{ $initialModalItem['pending_approval']['title'] ?? 'Temuan Pemeriksaan Tambahan' }}</h3>
                        </div>
                        <strong class="bks-approval-total" data-modal-approval-total>{{ $initialModalItem['pending_approval']['total'] ?? 'Rp 0' }}</strong>
                    </div>
                    <p class="bks-approval-reason" data-modal-approval-reason>{{ $initialModalItem['pending_approval']['reason'] ?? '' }}</p>
                    <div class="bks-approval-items" data-modal-approval-items>
                        @foreach(($initialModalItem['pending_approval']['items'] ?? []) as $apItem)
                            <div class="bks-approval-item">
                                <span>{{ $apItem['description'] }} <small>({{ $apItem['type'] }} · {{ $apItem['quantity'] }}x)</small></span>
                                <strong>{{ $apItem['total'] }}</strong>
                            </div>
                        @endforeach
                    </div>
                    <form method="post" action="{{ $initialModalItem['pending_approval']['decision_url'] ?? '#' }}" data-modal-approval-form class="bks-approval-form">
                        @csrf
                        <input type="text" name="note" placeholder="Catatan opsional untuk bengkel..." maxlength="500">
                        <div class="bks-approval-actions">
                            <button type="submit" name="decision" value="reject" class="bks-btn bks-btn-danger">Tolak</button>
                            <button type="submit" name="decision" value="approve" class="bks-btn" style="background:#059669;border-color:#059669;color:#fff;">Setujui &amp; Lanjut Pengerjaan</button>
                        </div>
                    </form>
                </section>

                <div class="bks-pricing-box">
                    <div class="bks-price-row">
                        <span>Biaya Servis</span>
                        <strong data-modal-price-base>{{ $initialModalItem['pricing']['base'] ?? '—' }}</strong>
                    </div>
                    <div class="bks-price-row">
                        <span>Kupon Promo</span>
                        <strong data-modal-promo-label>{{ $initialModalItem['pricing']['promo_label'] ?? 'Tidak Pakai Kupon' }}</strong>
                    </div>
                    <div class="bks-price-row is-discount" data-modal-discount-row {{ !empty($initialModalItem['pricing']['has_promo']) ? '' : 'hidden' }}>
                        <span>Potongan Promo</span>
                        <strong data-modal-price-discount>{{ $initialModalItem['pricing']['discount'] ?? 'Tidak Ada' }}</strong>
                    </div>
                    <div class="bks-price-total">
                        <span>Total Harga</span>
                        <div class="bks-price-total-val">
                            <del data-modal-price-strike {{ !empty($initialModalItem['pricing']['strike']) ? '' : 'hidden' }}>{{ $initialModalItem['pricing']['strike'] ?? '' }}</del>
                            <strong data-modal-price-total>{{ $initialModalItem['pricing']['total'] ?? '—' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="bks-modal-foot">
                <form method="post" action="{{ $initialModalItem['cancel_url'] ?? '#' }}" data-modal-cancel-form {{ !empty($initialModalItem['can_cancel']) ? '' : 'hidden' }} data-cxc-form>
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="bks-btn bks-btn-danger">Batalkan Booking</button>
                </form>
                <div class="bks-modal-foot-right">
                    <a class="bks-btn" href="{{ route('pelanggan.kendaraan.index') }}">Kendaraan Saya</a>
                    <button type="button" class="bks-btn" style="background:#0f172a;border-color:#0f172a;color:#fff;" data-close-detail>Tutup</button>
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
    const root = document.querySelector('[data-bks-page]');
    if (!root) return;
    const bookings = @json($bookingsMap);
    const modal = root.querySelector('[data-booking-detail-modal]');
    const setText = (sel, val) => {
        const el = root.querySelector(sel);
        if (el) el.textContent = val || '—';
    };


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
    const openDetail = (id) => {
        const item = bookings[String(id)];
        if (!item || !modal) return;
        setText('[data-modal-heading]', 'Detail Booking Servis');
        setText('[data-modal-code]', item.code);
        const badge = modal.querySelector('[data-modal-status-badge]');
        if (badge) {
            badge.textContent = item.status_label;
            badge.dataset.tone = item.tone;
        }
        setText('[data-modal-submitted]', item.submitted_at);
        const step2 = modal.querySelector('[data-modal-step-2]');
        const step3 = modal.querySelector('[data-modal-step-3]');
        if (step2) {
            step2.classList.toggle('is-current', item.stage === 2);
            step2.classList.toggle('is-done', item.stage > 2);
        }
        if (step3) {
            step3.classList.toggle('is-current', item.stage >= 3);
        }
        setText('[data-modal-vehicle]', item.vehicle);
        setText('[data-modal-plate]', item.plate);
        setText('[data-modal-mileage]', item.mileage);
        setText('[data-modal-service]', item.service);
        setText('[data-modal-date]', item.date);
        setText('[data-modal-time]', item.time);
        setText('[data-modal-duration]', item.duration);
        setText('[data-modal-tech]', item.technician);
        setText('[data-modal-tech-desc]', item.technician_desc);
        setText('[data-modal-complaint]', item.complaint);
        setText('[data-modal-note]', item.note);

        const p = item.pricing || {};
        setText('[data-modal-price-base]', p.base);
        setText('[data-modal-promo-label]', p.promo_label);
        setText('[data-modal-price-discount]', p.discount);
        setText('[data-modal-price-total]', p.total);

        const discRow = modal.querySelector('[data-modal-discount-row]');
        if (discRow) discRow.hidden = !p.has_promo;
        const strike = modal.querySelector('[data-modal-price-strike]');
        if (strike) {
            strike.hidden = !p.strike;
            strike.textContent = p.strike || '';
        }

        const cancelForm = modal.querySelector('[data-modal-cancel-form]');
        if (cancelForm) {
            cancelForm.hidden = !item.can_cancel;
            cancelForm.action = item.cancel_url;
        }

        const approvalBox = modal.querySelector('[data-modal-approval-box]');
        if (approvalBox) {
            const ap = item.pending_approval;
            approvalBox.hidden = !ap;
            if (ap) {
                setText('[data-modal-approval-title]', ap.title);
                setText('[data-modal-approval-reason]', ap.reason);
                setText('[data-modal-approval-total]', ap.total);
                const itemsWrap = modal.querySelector('[data-modal-approval-items]');
                if (itemsWrap) {
                    itemsWrap.innerHTML = (ap.items || []).map(it =>
                        `<div class="bks-approval-item"><span>${it.description} <small>(${it.type} · ${it.quantity}x)</small></span><strong>${it.total}</strong></div>`
                    ).join('');
                }
                const apForm = modal.querySelector('[data-modal-approval-form]');
                if (apForm) apForm.action = ap.decision_url;
            }
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

    root.querySelectorAll('[data-open-detail]').forEach(btn => {
        btn.addEventListener('click', () => openDetail(btn.dataset.openDetail));
    });
    root.querySelectorAll('[data-close-detail]').forEach(btn => {
        btn.addEventListener('click', closeDetail);
    });
    modal?.addEventListener('click', e => {
        if (e.target === modal) closeDetail();
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modal && !modal.hidden) closeDetail();
    });

    // Filter tabs & search
    let activeFilter = 'all';
    const searchInput = root.querySelector('[data-bks-search]');
    const rows = [...root.querySelectorAll('[data-bks-row]')];

    const applyFilter = () => {
        const q = (searchInput?.value || '').trim().toLowerCase();
        rows.forEach(row => {
            const matchTab = activeFilter === 'all' || row.dataset.group === activeFilter;
            const matchQuery = !q || (row.dataset.search || '').includes(q);
            row.hidden = !(matchTab && matchQuery);
        });
    };

    root.querySelectorAll('[data-bks-filter]').forEach(tab => {
        tab.addEventListener('click', () => {
            activeFilter = tab.dataset.bksFilter;
            root.querySelectorAll('[data-bks-filter]').forEach(t => t.classList.toggle('is-active', t === tab));
            applyFilter();
        });
    });
    searchInput?.addEventListener('input', applyFilter);
})();
</script>
@endpush
