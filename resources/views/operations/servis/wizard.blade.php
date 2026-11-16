@extends('layouts.app')

@section('title', 'Work Order '.$servis->kode_servis.' · '.ucwords(str_replace('-', ' ', $stage)))

@section('content')
@php
    $vehicleName = trim(($servis->kendaraan?->merk ?: '').' '.($servis->kendaraan?->model ?: '')) ?: 'Kendaraan';
    $serviceLabel = $servis->bookingServis?->jenisServis?->nama_servis ?: 'Servis Berkala';
    $estimate = app(\App\Services\WorkOrderEstimateService::class)->calculate($servis);
    $laborTotal = $estimate['labor'];
    $partsTotal = $estimate['parts'];
    $otherTotal = $estimate['other'];
    $total = $estimate['total'];

    $servis->loadMissing(['approvalRequests.items.sparepart']);
    $currentStage = $stages->currentStage($servis);
    $stageList = [
        ['key' => 'pemeriksaan', 'label' => 'Pemeriksaan'],
        ['key' => 'persetujuan', 'label' => 'Konfirmasi Member'],
        ['key' => 'pengerjaan', 'label' => 'Pengerjaan'],
        ['key' => 'quality-control', 'label' => 'Pemeriksaan Mutu'],
        ['key' => 'finalisasi', 'label' => 'Finalisasi & Pembayaran'],
    ];
    $currentStageIndex = array_search($currentStage, array_column($stageList, 'key'), true);
    $pendingApprovals = $servis->approvalRequests->where('status', 'pending');
    $nextAction = $stages->nextAction($servis);
    $nextActionAllowed = $nextAction['stage'] && $stages->canAccess($servis, $nextAction['stage']);
@endphp

<div class="mwz-page" data-tahap="{{ $stage }}" data-tahap-aktif="{{ $currentStage }}">
    <!-- Header -->
    <header class="mwz-header">
        <div class="mwz-header-left">
            <div class="mwz-thumb">
                <img src="{{ $vehicleImage }}" alt="{{ $vehicleName }}">
            </div>
            <div>
                <div class="mwz-eyebrow">
                    <span class="mwz-eyebrow-dot" aria-hidden="true"></span>
                    <span>Perintah Kerja Servis · {{ $servis->kode_servis }}</span>
                </div>
                <h1 class="mwz-title">{{ $vehicleName }}</h1>
                <p class="mwz-subtitle">
                    <span>{{ $servis->kendaraan?->nomor_polisi ?: 'Plat belum diisi' }}</span>
                    <i aria-hidden="true">·</i>
                    <span>{{ number_format((int) $servis->kilometer, 0, ',', '.') }} km</span>
                    <i aria-hidden="true">·</i>
                    <span>Layanan Awal: <strong>{{ $serviceLabel }}</strong></span>
                </p>
            </div>
        </div>
        <a class="mwz-btn mwz-btn-ghost" href="{{ route($prefix.'.servis.index') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Kembali ke Daftar</span>
        </a>
    </header>

    @if(session('success'))
        <div class="mwz-alert is-success" role="status">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('warning'))
        <div class="mwz-alert is-warning" role="status">{{ session('warning') }}</div>
    @endif
    @if($errors->any())
        <div class="mwz-alert is-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <!-- Stepper -->
    <nav class="mwz-stepper" aria-label="Tahap Perintah Kerja">
        @foreach($stageList as $index => $item)
            @php
                $accessible = $stages->canAccess($servis, $item['key']);
            @endphp
            <div class="mwz-step {{ $index < $currentStageIndex ? 'is-done' : '' }} {{ $item['key'] === $stage ? 'is-current' : '' }} {{ !$accessible ? 'is-locked' : '' }}">
                <span class="mwz-step-no">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                @if($accessible)
                    <a href="{{ route($prefix.'.servis.'.$item['key'], $servis) }}">{{ $item['label'] }}</a>
                @else
                    <span>{{ $item['label'] }}</span>
                @endif
            </div>
        @endforeach
    </nav>

    <div class="mwz-layout">
        <main class="mwz-main">
            {{-- =================================================
                 TAHAP 01: PEMERIKSAAN AWAL
                 ================================================= --}}
            @if($stage === 'pemeriksaan')
                <section class="mwz-card">
                    <div class="mwz-card-head">
                        <div>
                            <span class="mwz-section-kicker">Tahap 01 · Diagnosis Awal</span>
                            <h2>Pemeriksaan Kendaraan</h2>
                            <p>Periksa kondisi unit sesuai layanan awal dan keluhan pelanggan sebelum pengerjaan dimulai.</p>
                        </div>
                    </div>

                    <div class="mwz-info-grid">
                        <div class="mwz-info-box">
                            <small>Layanan Terdaftar</small>
                            <strong>{{ $serviceLabel }}</strong>
                        </div>
                        <div class="mwz-info-box">
                            <small>Keluhan Pelanggan</small>
                            <strong>{{ $servis->keluhan ?: ($servis->bookingServis?->keluhan ?: 'Tidak ada keluhan khusus') }}</strong>
                        </div>
                    </div>

                    @if($servis->status === 'pemeriksaan')
                        <form class="mwz-form" method="post" action="{{ route($prefix.'.servis.inspection', $servis) }}" style="margin-top: 16px;">
                            @csrf
                            <div class="mwz-field">
                                <label>Hasil Pemeriksaan Fisik &amp; Teknis <span style="color:#ef233c;">*</span></label>
                                <textarea name="hasil_pemeriksaan" rows="3" required placeholder="Contoh: Oli mesin sudah hitam dan perlu diganti sesuai jadwal, kondisi komponen lain aman...">{{ old('hasil_pemeriksaan', $servis->hasil_pemeriksaan) }}</textarea>
                            </div>
                            <div class="mwz-field">
                                <label>Catatan Teknisi (Opsional)</label>
                                <textarea name="catatan" rows="2" placeholder="Catatan tambahan saat pemeriksaan...">{{ old('catatan', $servis->catatan) }}</textarea>
                            </div>
                            <div class="mwz-actions">
                                <button class="mwz-btn mwz-btn-primary" type="submit">Simpan Pemeriksaan &amp; Lanjut Pengerjaan →</button>
                            </div>
                        </form>
                    @else
                        <div class="mwz-info-box" style="margin-top: 14px;">
                            <small>Hasil Pemeriksaan</small>
                            <strong>{{ $servis->hasil_pemeriksaan ?: 'Sudah diperiksa' }}</strong>
                        </div>
                    @endif
                </section>
            @endif

            {{-- =================================================
                 TAHAP 02 & 03: KONFIRMASI MEMBER & PENGERJAAN
                 ================================================= --}}
            @if(in_array($stage, ['persetujuan', 'pengerjaan'], true))
                @if($pendingApprovals->isNotEmpty())
                    <div class="mwz-banner is-amber">
                        <span class="mwz-banner-dot" aria-hidden="true"></span>
                        <div>
                            <strong>Menunggu Konfirmasi Member</strong>
                            <p>Ada laporan temuan masalah yang sedang menunggu persetujuan pelanggan pada halaman Booking Servis member.</p>
                        </div>
                    </div>
                @endif

                <!-- 1. Ringkasan Tugas Servis (Tanpa Harga) -->
                <section class="mwz-card">
                    <div class="mwz-card-head">
                        <div>
                            <span class="mwz-section-kicker">Daftar Tugas</span>
                            <h2>Pekerjaan Servis Kendaraan</h2>
                            <p>Layanan awal sudah ditentukan dari pendaftaran booking. Item perbaikan tambahan akan otomatis masuk setelah disetujui member.</p>
                        </div>
                    </div>

                    <div class="mwz-info-grid" style="margin-bottom: 16px;">
                        <div class="mwz-info-box">
                            <small>Layanan Booking Awal</small>
                            <strong>{{ $serviceLabel }}</strong>
                        </div>
                        <div class="mwz-info-box">
                            <small>Keluhan Pelanggan</small>
                            <strong>{{ $servis->keluhan ?: ($servis->bookingServis?->keluhan ?: 'Tidak ada keluhan khusus') }}</strong>
                        </div>
                        <div class="mwz-info-box" style="grid-column: 1 / -1;">
                            <small>Hasil Diagnosis Pemeriksaan</small>
                            <strong>{{ $servis->hasil_pemeriksaan ?: 'Pemeriksaan standar selesai' }}</strong>
                        </div>
                    </div>

                    <div class="mwz-table-wrap">
                        <table class="mwz-table">
                            <thead>
                                <tr>
                                    <th>Nama Pekerjaan / Tindakan</th>
                                    <th>Keterangan Sumber</th>
                                    <th style="width: 130px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($servis->details as $detail)
                                    <tr>
                                        <td><strong>{{ $detail->deskripsi_pekerjaan }}</strong></td>
                                        <td class="mwz-muted">{{ $detail->catatan ?: 'Layanan awal sesuai reservasi booking' }}</td>
                                        <td><span class="mwz-pill is-emerald">Terjadwal</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td><strong>{{ $serviceLabel }}</strong></td>
                                        <td class="mwz-muted">Layanan awal sesuai reservasi booking</td>
                                        <td><span class="mwz-pill is-emerald">Terjadwal</span></td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- 2. Penggunaan Suku Cadang (Tanpa Harga) -->
                <section class="mwz-card">
                    <div class="mwz-card-head mwz-card-head-row">
                        <div>
                            <span class="mwz-section-kicker">Suku Cadang Bengkel</span>
                            <h2>Penggunaan Suku Cadang</h2>
                            <p>Pilih suku cadang (oli, filter, kampas rem, dll.) yang digunakan pada pengerjaan servis ini.</p>
                        </div>
                        @if($servis->status === 'dikerjakan')
                            <button type="button" class="mwz-btn mwz-btn-primary" data-mwz-open-sparepart>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 5v14"/><path d="M5 12h14"/>
                                </svg>
                                <span>Tambah Suku Cadang</span>
                            </button>
                        @endif
                    </div>

                    <div class="mwz-table-wrap">
                        <table class="mwz-table">
                            <thead>
                                <tr>
                                    <th>Nama Suku Cadang</th>
                                    <th>Kode SKU</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    @if($servis->status === 'dikerjakan')
                                        <th style="text-align:right;">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($servis->sparepartItems as $item)
                                    <tr>
                                        <td><strong>{{ $item->sparepart?->nama_sparepart ?: 'Suku Cadang' }}</strong></td>
                                        <td class="mwz-mono">{{ $item->sparepart?->kode_sparepart ?: '#'.$item->id_sparepart }}</td>
                                        <td><strong>{{ $item->jumlah }} {{ $item->sparepart?->satuan ?: 'pcs' }}</strong></td>
                                        <td><span class="mwz-pill is-blue">Tercatat</span></td>
                                        @if($servis->status === 'dikerjakan')
                                            <td style="text-align:right;">
                                                <form method="post" action="{{ route($prefix.'.servis.sparepart.destroy', [$servis, $item]) }}" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="mwz-del-btn" type="submit">Hapus</button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $servis->status === 'dikerjakan' ? 5 : 4 }}" class="mwz-empty-cell">
                                            Belum ada suku cadang yang dicatat pada pengerjaan ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- 3. Lapor Temuan Masalah & Konfirmasi ke Member (Tanpa Harga di UI Mekanik) -->
                <section class="mwz-card">
                    <div class="mwz-card-head">
                        <div>
                            <span class="mwz-section-kicker">Temuan Masalah Tambahan</span>
                            <h2>Laporan Temuan &amp; Konfirmasi Member</h2>
                            <p>Jika ditemukan masalah tambahan pada kendaraan, laporkan di sini untuk meminta konfirmasi member. Jika disetujui member, perbaikan dan biayanya otomatis masuk ke tagihan akhir.</p>
                        </div>
                    </div>

                    @if($servis->approvalRequests->isNotEmpty())
                        <div class="mwz-approval-list">
                            @foreach($servis->approvalRequests as $approval)
                                @php
                                    $apMeta = match($approval->status) {
                                        'approved' => ['cls' => 'is-emerald', 'lbl' => 'Disetujui Member · Masuk Pengerjaan & Tagihan Akhir'],
                                        'rejected' => ['cls' => 'is-crimson', 'lbl' => 'Ditolak Member'],
                                        default => ['cls' => 'is-amber', 'lbl' => 'Menunggu Konfirmasi Member'],
                                    };
                                @endphp
                                <article class="mwz-approval-card">
                                    <div class="mwz-approval-top">
                                        <strong>{{ $approval->title }}</strong>
                                        <span class="mwz-pill {{ $apMeta['cls'] }}">{{ $apMeta['lbl'] }}</span>
                                    </div>
                                    <p class="mwz-approval-reason">{{ $approval->reason }}</p>
                                    <div class="mwz-approval-items">
                                        @foreach($approval->items as $it)
                                            <span class="mwz-item-chip">
                                                {{ $it->item_type === 'sparepart' ? 'Part: '.$it->description : 'Tindakan: '.$it->description }} ({{ $it->quantity }}x)
                                            </span>
                                        @endforeach
                                    </div>
                                    @if($approval->customer_note)
                                        <small class="mwz-cust-note">Catatan Member: “{{ $approval->customer_note }}”</small>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @endif

                    @if($pendingApprovals->isEmpty() && in_array($servis->status, ['pemeriksaan', 'dikerjakan'], true))
                        <details class="mwz-report-drawer" {{ $stage === 'persetujuan' ? 'open' : '' }}>
                            <summary class="mwz-btn mwz-btn-ghost">+ Laporkan Temuan Masalah ke Member</summary>
                            <form class="mwz-form" method="post" action="{{ route($prefix.'.servis.approvals.store', $servis) }}" style="margin-top: 14px;">
                                @csrf
                                <div class="mwz-form-grid">
                                    <div class="mwz-field mwz-full">
                                        <label>Masalah / Temuan pada Kendaraan <span style="color:#ef233c;">*</span></label>
                                        <input type="text" name="title" required maxlength="160" placeholder="Contoh: Kampas rem depan sudah tipis & aus">
                                    </div>
                                    <div class="mwz-field mwz-full">
                                        <label>Penjelasan &amp; Rekomendasi Perbaikan untuk Member <span style="color:#ef233c;">*</span></label>
                                        <textarea name="reason" rows="2" required placeholder="Jelaskan kondisi temuan agar member dapat memutuskan apakah ingin diperbaiki sekalian..."></textarea>
                                    </div>
                                    <div class="mwz-field">
                                        <label>Tindakan / Layanan Perbaikan</label>
                                        <select name="id_jenis_servis">
                                            <option value="">— Pilih tindakan perbaikan (opsional) —</option>
                                            @foreach($serviceTypes as $type)
                                                <option value="{{ $type->id_jenis_servis }}">{{ $type->nama_servis }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mwz-field">
                                        <label>Suku Cadang yang Diperlukan</label>
                                        <div style="display:flex; gap:8px;">
                                            <select name="id_sparepart" style="flex:1;">
                                                <option value="">— Pilih suku cadang (opsional) —</option>
                                                @foreach($spareparts as $part)
                                                    <option value="{{ $part->id_sparepart }}">{{ $part->nama_sparepart }} (Stok: {{ $part->stok }})</option>
                                                @endforeach
                                            </select>
                                            <input type="number" name="sparepart_qty" value="1" min="1" style="width:76px;" title="Jumlah part">
                                        </div>
                                    </div>
                                </div>
                                <small class="mwz-hint">Pilih minimal salah satu tindakan perbaikan atau suku cadang. Sistem akan otomatis mengirimkan estimasi dan rincian konfirmasi ke akun member.</small>
                                <div class="mwz-actions">
                                    <button type="submit" class="mwz-btn mwz-btn-primary">Kirim Konfirmasi ke Member →</button>
                                </div>
                            </form>
                        </details>
                    @endif
                </section>
            @endif

            {{-- =================================================
                 TAHAP 04: PEMERIKSAAN MUTU (QUALITY CONTROL)
                 ================================================= --}}
            @if($stage === 'quality-control')
                @php
                    $qc = $servis->qualityControl;
                    $qcItems = $qc ? $qc->items : collect();
                    $standardQcItems = $qcItems->where('item_key', '!=', 'work_order')->values();
                    $woQcItems = $qcItems->where('item_key', 'work_order')->values();
                    $qcTotal = $qcItems->count();
                    $qcChecked = $qcItems->filter(fn ($i) => filled(old("items.{$i->id_service_quality_control_item}.result", $i->result)))->count();
                    $qcPassedCount = $qcItems->filter(fn ($i) => old("items.{$i->id_service_quality_control_item}.result", $i->result) === 'PASS')->count();
                    $qcFailedCount = $qcItems->filter(fn ($i) => old("items.{$i->id_service_quality_control_item}.result", $i->result) === 'FAIL')->count();
                    $qcLocked = $qc && $qc->status === 'passed';
                @endphp
                <section class="mwz-card mwz-qc-card">
                    <div class="mwz-card-head mwz-qc-head">
                        <div>
                            <span class="mwz-section-kicker">Tahap 04 · Pemeriksaan Mutu</span>
                            <h2>Quality Control Bengkel</h2>
                            <p>Periksa standar kualitas bengkel dan kesesuaian hasil pengerjaan terhadap Work Order sebelum lanjut ke finalisasi.</p>
                        </div>
                        @if($qc)
                            <div class="mwz-qc-status-pill is-{{ $qc->status === 'passed' ? 'passed' : ($qc->status === 'failed' ? 'failed' : 'progress') }}">
                                <span class="mwz-qc-status-dot" aria-hidden="true"></span>
                                <span>
                                    {{ $qc->status === 'passed' ? 'Lulus Quality Control' : ($qc->status === 'failed' ? 'Perlu Perbaikan Ulang' : 'Sedang Diperiksa') }}
                                </span>
                            </div>
                        @endif
                    </div>

                    @if(auth()->user()?->roleKey() === 'mechanic')
                        <div class="mwz-banner is-amber" style="margin-bottom: 0;">
                            <span class="mwz-banner-dot" aria-hidden="true"></span>
                            <div>
                                <strong>{{ $qc?->status === 'passed' ? 'Quality Control Lulus' : ($qc?->status === 'failed' ? 'QC Meminta Perbaikan Ulang' : 'Menunggu Pemeriksaan Tim Quality Control (QC)') }}</strong>
                                <p>{{ $qc?->status === 'passed' ? 'Unit telah lulus pemeriksaan mutu oleh tim QC.' : ($qc?->status === 'failed' ? 'Silakan perbaiki pekerjaan sesuai catatan QC, lalu kirim ulang ke QC.' : 'Unit telah diserahkan ke tim Quality Control untuk pemeriksaan mutu.') }}</p>
                            </div>
                        </div>
                    @elseif(!$qc)
                        <div class="mwz-qc-empty">
                            <div>
                                <strong>Pemeriksaan Quality Control Belum Dimulai</strong>
                                <p>Mulai sesi QC untuk membuka checklist pemeriksaan standar dan verifikasi kesesuaian Work Order.</p>
                            </div>
                            <form method="post" action="{{ route($prefix.'.servis.qc.start', $servis) }}">
                                @csrf
                                <button class="mwz-btn mwz-btn-primary" type="submit">Mulai Pemeriksaan Quality Control</button>
                            </form>
                        </div>
                    @else
                        @if($qcLocked)
                            <div class="mwz-banner is-emerald" style="margin-bottom: 18px;">
                                <span class="mwz-banner-dot is-emerald" aria-hidden="true"></span>
                                <div>
                                    <strong>Quality Control Lulus</strong>
                                    <p>Seluruh checklist pemeriksaan standar dan penyesuaian Work Order telah dinyatakan lulus. Unit siap dilanjutkan ke tahap finalisasi &amp; pembayaran.</p>
                                </div>
                            </div>
                        @elseif($qc->status === 'failed')
                            <div class="mwz-banner is-rose" style="margin-bottom: 18px;">
                                <span class="mwz-banner-dot is-rose" aria-hidden="true"></span>
                                <div>
                                    <strong>QC Meminta Perbaikan Ulang</strong>
                                    <p>Terdapat item yang dinyatakan Tidak Lulus. Perbaiki catatan di bawah atau perbarui hasil setelah mekanik menyelesaikan perbaikan.</p>
                                </div>
                            </div>
                        @endif

                        {{-- Progress Strip --}}
                        <div class="mwz-qc-progress-strip" data-qc-progress>
                            <div class="mwz-qc-progress-meta">
                                <span class="mwz-qc-progress-label">Progres Checklist</span>
                                <strong data-qc-count>{{ $qcChecked }} / {{ $qcTotal }} Item Diperiksa</strong>
                            </div>
                            <div class="mwz-qc-progress-pills">
                                <span class="mwz-qc-mini-stat is-pass">Lulus: <strong data-qc-pass-count>{{ $qcPassedCount }}</strong></span>
                                <span class="mwz-qc-mini-stat is-fail">Tidak Lulus: <strong data-qc-fail-count>{{ $qcFailedCount }}</strong></span>
                            </div>
                        </div>

                        <form class="mwz-form mwz-qc-form" method="post" action="{{ route($prefix.'.servis.qc.update', $servis) }}" data-qc-form novalidate>
                            @csrf
                            @method('PUT')

                            {{-- BAGIAN 1: CHECKLIST PEMERIKSAAN STANDAR --}}
                            <div class="mwz-qc-section">
                                <div class="mwz-qc-section-head">
                                    <div>
                                        <span class="mwz-qc-section-badge">01 · Pemeriksaan Standar</span>
                                        <h3>Checklist Pemeriksaan Standar Bengkel</h3>
                                        <p>Pastikan kebersihan area kerja, indikator instrumen, fluida, sistem keselamatan, dan uji fungsi kendaraan.</p>
                                    </div>
                                    <span class="mwz-qc-section-count">{{ $standardQcItems->count() }} Kriteria</span>
                                </div>

                                <div class="mwz-qc-grid">
                                    @foreach($standardQcItems as $item)
                                        @php
                                            $itemId = $item->id_service_quality_control_item;
                                            $currentResult = old("items.{$itemId}.result", $item->result);
                                            $currentNote = old("items.{$itemId}.note", $item->note);
                                            $isFail = $currentResult === 'FAIL';
                                        @endphp
                                        <div class="mwz-qc-item {{ $currentResult === 'PASS' ? 'is-pass' : ($isFail ? 'is-fail' : ($currentResult === 'N/A' ? 'is-na' : '')) }}" data-qc-item>
                                            <div class="mwz-qc-item-top">
                                                <div class="mwz-qc-item-info">
                                                    <div class="mwz-qc-item-title-row">
                                                        <strong>{{ $item->label_snapshot }}</strong>
                                                        <span class="mwz-qc-req-tag">{{ $item->is_mandatory ? 'Wajib Lulus' : 'Opsional' }}</span>
                                                    </div>
                                                    <small>Pilih status hasil pemeriksaan untuk kriteria standar ini.</small>
                                                </div>

                                                <div class="mwz-qc-toggle-group" role="radiogroup" aria-label="{{ $item->label_snapshot }}">
                                                    <label class="mwz-qc-pill is-pass">
                                                        <input type="radio" name="items[{{ $itemId }}][result]" value="PASS" @checked($currentResult === 'PASS') @disabled($qcLocked) required>
                                                        <span>✓ Lulus</span>
                                                    </label>
                                                    <label class="mwz-qc-pill is-fail">
                                                        <input type="radio" name="items[{{ $itemId }}][result]" value="FAIL" @checked($isFail) @disabled($qcLocked)>
                                                        <span>✕ Tidak Lulus</span>
                                                    </label>
                                                    <label class="mwz-qc-pill is-na">
                                                        <input type="radio" name="items[{{ $itemId }}][result]" value="N/A" @checked($currentResult === 'N/A') @disabled($qcLocked)>
                                                        <span>— N/A</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="mwz-qc-note-wrap {{ $isFail ? 'is-visible' : '' }}" data-qc-note-wrap @if(!$isFail) hidden @endif>
                                                <label class="mwz-qc-note-label" for="qc-note-{{ $itemId }}">
                                                    Catatan Perbaikan <span class="mwz-qc-required-star">* Wajib diisi jika Tidak Lulus</span>
                                                </label>
                                                <textarea
                                                    id="qc-note-{{ $itemId }}"
                                                    class="mwz-qc-note-input"
                                                    name="items[{{ $itemId }}][note]"
                                                    rows="2"
                                                    placeholder="Jelaskan temuan ketidaksesuaian dan perbaikan yang harus dilakukan mekanik..."
                                                    @required($isFail)
                                                    @disabled($qcLocked)
                                                    data-qc-note
                                                >{{ $currentNote }}</textarea>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- BAGIAN 2: PEMERIKSAAN PENYESUAIAN WORK ORDER --}}
                            <div class="mwz-qc-section">
                                <div class="mwz-qc-section-head">
                                    <div>
                                        <span class="mwz-qc-section-badge is-wo">02 · Penyesuaian Work Order</span>
                                        <h3>Pemeriksaan Penyesuaian Work Order</h3>
                                        <p>Verifikasi bahwa seluruh pekerjaan jasa, suku cadang terpasang, dan keluhan pelanggan telah dikerjakan sesuai Work Order.</p>
                                    </div>
                                    <span class="mwz-qc-section-count">{{ $servis->details->count() }} Jasa · {{ $servis->sparepartItems->count() }} Part</span>
                                </div>

                                {{-- Ringkasan Referensi Work Order --}}
                                <div class="mwz-qc-wo-ref">
                                    <div class="mwz-qc-wo-ref-grid">
                                        <div class="mwz-qc-wo-box">
                                            <span class="mwz-qc-wo-kicker">Layanan Booking &amp; Keluhan</span>
                                            <strong>{{ $servis->bookingServis?->jenisServis?->nama_servis ?? 'Servis Umum' }}</strong>
                                            <p>{{ $servis->keluhan ?: 'Tidak ada keluhan khusus tercatat.' }}</p>
                                            @if($servis->hasil_pemeriksaan)
                                                <div class="mwz-qc-wo-diag">
                                                    <span>Diagnosis Mekanik:</span> {{ $servis->hasil_pemeriksaan }}
                                                </div>
                                            @endif
                                        </div>

                                        <div class="mwz-qc-wo-box">
                                            <span class="mwz-qc-wo-kicker">Pekerjaan &amp; Sparepart Terpasang</span>
                                            <ul class="mwz-qc-wo-list">
                                                @forelse($servis->details as $detail)
                                                    <li>
                                                        <span>{{ $detail->deskripsi_pekerjaan }}</span>
                                                        <small>{{ $detail->catatan ?: 'Jasa Work Order' }}</small>
                                                    </li>
                                                @empty
                                                    <li class="is-muted">Belum ada rincian jasa tercatat.</li>
                                                @endforelse
                                                @foreach($servis->sparepartItems as $part)
                                                    <li>
                                                        <span>{{ $part->sparepart?->nama_sparepart ?? 'Sparepart' }}</span>
                                                        <small>{{ $part->jumlah }} pcs</small>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="mwz-qc-grid">
                                    @foreach($woQcItems as $item)
                                        @php
                                            $itemId = $item->id_service_quality_control_item;
                                            $currentResult = old("items.{$itemId}.result", $item->result);
                                            $currentNote = old("items.{$itemId}.note", $item->note);
                                            $isFail = $currentResult === 'FAIL';
                                        @endphp
                                        <div class="mwz-qc-item is-wo-card {{ $currentResult === 'PASS' ? 'is-pass' : ($isFail ? 'is-fail' : ($currentResult === 'N/A' ? 'is-na' : '')) }}" data-qc-item>
                                            <div class="mwz-qc-item-top">
                                                <div class="mwz-qc-item-info">
                                                    <div class="mwz-qc-item-title-row">
                                                        <strong>{{ $item->label_snapshot }}</strong>
                                                        <span class="mwz-qc-req-tag">{{ $item->is_mandatory ? 'Wajib Lulus' : 'Opsional' }}</span>
                                                    </div>
                                                    <small>Apakah hasil pengerjaan mekanik &amp; sparepart sudah 100% sesuai dengan daftar Work Order di atas?</small>
                                                </div>

                                                <div class="mwz-qc-toggle-group" role="radiogroup" aria-label="{{ $item->label_snapshot }}">
                                                    <label class="mwz-qc-pill is-pass">
                                                        <input type="radio" name="items[{{ $itemId }}][result]" value="PASS" @checked($currentResult === 'PASS') @disabled($qcLocked) required>
                                                        <span>✓ Lulus</span>
                                                    </label>
                                                    <label class="mwz-qc-pill is-fail">
                                                        <input type="radio" name="items[{{ $itemId }}][result]" value="FAIL" @checked($isFail) @disabled($qcLocked)>
                                                        <span>✕ Tidak Lulus</span>
                                                    </label>
                                                    <label class="mwz-qc-pill is-na">
                                                        <input type="radio" name="items[{{ $itemId }}][result]" value="N/A" @checked($currentResult === 'N/A') @disabled($qcLocked)>
                                                        <span>— N/A</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="mwz-qc-note-wrap {{ $isFail ? 'is-visible' : '' }}" data-qc-note-wrap @if(!$isFail) hidden @endif>
                                                <label class="mwz-qc-note-label" for="qc-note-{{ $itemId }}">
                                                    Catatan Ketidaksesuaian Work Order <span class="mwz-qc-required-star">* Wajib diisi jika Tidak Lulus</span>
                                                </label>
                                                <textarea
                                                    id="qc-note-{{ $itemId }}"
                                                    class="mwz-qc-note-input"
                                                    name="items[{{ $itemId }}][note]"
                                                    rows="2"
                                                    placeholder="Tuliskan item Work Order yang belum sesuai atau perlu dikerjakan ulang..."
                                                    @required($isFail)
                                                    @disabled($qcLocked)
                                                    data-qc-note
                                                >{{ $currentNote }}</textarea>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Catatan Akhir QC --}}
                            <div class="mwz-field mwz-qc-final-notes">
                                <label for="qc-final-notes">Catatan Umum Quality Control (Opsional)</label>
                                <textarea id="qc-final-notes" name="notes" rows="2" placeholder="Ringkasan kondisi kendaraan atau catatan tambahan untuk Service Advisor / Mekanik..." @disabled($qcLocked)>{{ old('notes', $qc->notes) }}</textarea>
                            </div>

                            <div class="mwz-qc-client-alert" data-qc-client-error hidden></div>

                            <div class="mwz-actions mwz-qc-actions">
                                @if(!$qcLocked)
                                    <button class="mwz-btn mwz-btn-primary" type="submit">Kirim Serah Terima ke Finance / Kasir →</button>
                                @endif
                                @if($qc->status === 'passed')
                                    <a class="mwz-btn mwz-btn-dark" href="{{ route($prefix.'.servis.finalisasi', $servis) }}">Lanjut ke Finalisasi &amp; Pembayaran →</a>
                                @endif
                            </div>
                        </form>
                    @endif
                </section>
            @endif

            {{-- =================================================
                 TAHAP 05: FINALISASI & PEMBAYARAN (RINCIAN BIAYA AKHIR)
                 ================================================= --}}
            @if($stage === 'finalisasi')
                <section class="mwz-card">
                    <div class="mwz-card-head">
                        <div>
                            <span class="mwz-section-kicker">Tahap 05 · Alur Akhir Pembayaran</span>
                            <h2>Rincian Biaya Servis &amp; Permasalahan Tambahan</h2>
                            <p>Biaya layanan awal beserta biaya perbaikan temuan masalah yang telah dikonfirmasi member dijumlahkan secara otomatis.</p>
                        </div>
                    </div>

                    <div class="mwz-table-wrap" style="margin-bottom: 18px;">
                        <table class="mwz-table">
                            <thead>
                                <tr>
                                    <th>Deskripsi Layanan / Suku Cadang</th>
                                    <th>Keterangan / Detail Masalah</th>
                                    <th>Qty</th>
                                    <th style="text-align:right;">Biaya (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($servis->details as $d)
                                    <tr>
                                        <td><strong>{{ $d->deskripsi_pekerjaan }}</strong></td>
                                        <td class="mwz-muted">{{ $d->catatan ?: 'Layanan awal sesuai reservasi booking' }}</td>
                                        <td>1</td>
                                        <td style="text-align:right; font-weight:700;">Rp {{ number_format((float) $d->biaya, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                                @foreach($servis->sparepartItems as $sp)
                                    <tr>
                                        <td><strong>{{ $sp->sparepart?->nama_sparepart ?: 'Suku Cadang' }}</strong></td>
                                        <td class="mwz-muted">Suku cadang servis ({{ $sp->sparepart?->kode_sparepart ?: '#'.$sp->id_sparepart }})</td>
                                        <td>{{ $sp->jumlah }}</td>
                                        <td style="text-align:right; font-weight:700;">Rp {{ number_format((float) $sp->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mwz-final-totals">
                        <div class="mwz-total-row">
                            <span>Subtotal Layanan &amp; Perbaikan</span>
                            <strong>Rp {{ number_format($laborTotal, 0, ',', '.') }}</strong>
                        </div>
                        <div class="mwz-total-row">
                            <span>Subtotal Suku Cadang</span>
                            <strong>Rp {{ number_format($partsTotal, 0, ',', '.') }}</strong>
                        </div>
                        <div class="mwz-total-row is-grand">
                            <span>Total Akhir Tagihan</span>
                            <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    @if($servis->transaksi)
                        <div class="mwz-actions" style="margin-top: 18px;">
                            <a class="mwz-btn mwz-btn-ghost" href="{{ route($prefix.'.invoice.servis', $servis->transaksi) }}">Lihat Invoice Resmi</a>
                            @if(in_array($servis->transaksi->status, ['belum_bayar', 'dp'], true))
                                <a class="mwz-btn mwz-btn-primary" href="{{ route($prefix.'.payment.create', $servis->transaksi) }}">Catat Pembayaran</a>
                            @endif
                        </div>
                    @elseif($servis->status === 'dikerjakan')
                        <form method="post" action="{{ route($prefix.'.servis.finalize', $servis) }}" style="margin-top: 18px;">
                            @csrf
                            <x-promo-selector :promos="$promos" scope="servis" :subtotal="$total"/>
                            <div class="mwz-actions" style="margin-top: 14px;">
                                <button class="mwz-btn mwz-btn-primary" type="submit">Proses Finalisasi &amp; Terbitkan Tagihan</button>
                            </div>
                        </form>
                    @endif
                </section>
            @endif
        </main>

        <!-- Sidebar Ringkasan (Tanpa Harga untuk Mekanik di Tahap 01-04) -->
        <aside class="mwz-sidebar">
            <section class="mwz-card">
                <h3 class="mwz-side-title">Ringkasan Perintah Kerja</h3>
                <dl class="mwz-side-dl">
                    <div>
                        <dt>Status</dt>
                        <dd>{{ $pendingApprovals->isNotEmpty() ? 'Menunggu Konfirmasi' : \App\Services\ServiceStatusPresenter::label($servis->status) }}</dd>
                    </div>
                    <div>
                        <dt>Pelanggan</dt>
                        <dd>{{ $servis->pelanggan?->nama_lengkap ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt>Kendaraan</dt>
                        <dd>{{ $vehicleName }}</dd>
                    </div>
                    <div>
                        <dt>Layanan Awal</dt>
                        <dd>{{ $serviceLabel }}</dd>
                    </div>
                    <div>
                        <dt>Mekanik Pelaksana</dt>
                        <dd>{{ $servis->petugas?->nama_lengkap ?: 'Belum ditugaskan' }}</dd>
                    </div>
                    @if($stage === 'finalisasi')
                        <div class="mwz-side-total">
                            <dt>Total Tagihan</dt>
                            <dd>Rp {{ number_format($total, 0, ',', '.') }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            <section class="mwz-card">
                <h3 class="mwz-side-title">Tahap Selanjutnya</h3>
                @if($pendingApprovals->isNotEmpty())
                    <p class="mwz-side-note">Menunggu konfirmasi dari member atas laporan temuan masalah yang diajukan.</p>
                @elseif($currentStage === 'pengerjaan' && $stage === 'pengerjaan')
                    <p class="mwz-side-note">Jika pengerjaan reparasi dan alokasi suku cadang sudah selesai, kirim serah terima unit ke tim Quality Control (QC).</p>
                    <form method="post" action="{{ route($prefix.'.servis.qc.start', $servis) }}">
                        @csrf
                        <button class="mwz-btn mwz-btn-primary" style="width:100%;" type="submit">Kirim Serah Terima ke QC →</button>
                    </form>
                @elseif($nextAction['stage'] && $nextActionAllowed)
                    <p class="mwz-side-note">{{ $nextAction['description'] }}</p>
                    <a class="mwz-btn mwz-btn-primary" style="width:100%;" href="{{ route($prefix.'.servis.'.$nextAction['stage'], $servis) }}">{{ $nextAction['label'] }} →</a>
                @else
                    <p class="mwz-side-note">{{ $nextAction['description'] }}</p>
                @endif
            </section>
        </aside>
    </div>
</div>

@if($servis->status === 'dikerjakan' && in_array($stage, ['persetujuan', 'pengerjaan'], true))
<!-- Modal Pilih Suku Cadang -->
<div class="mwz-modal-backdrop" id="mwz-sparepart-modal" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="mwz-sp-modal-title">
    <div class="mwz-modal-dialog" role="document">
        <div class="mwz-modal-head">
            <div>
                <span class="mwz-modal-eyebrow">SUKU CADANG BENGKEL</span>
                <h2 class="mwz-modal-title" id="mwz-sp-modal-title">Pilih Suku Cadang</h2>
                <p class="mwz-modal-sub">Cari dan pilih suku cadang yang digunakan untuk Work Order {{ $servis->kode_servis }}.</p>
            </div>
            <button type="button" class="mwz-modal-close" data-mwz-modal-close aria-label="Tutup modal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                </svg>
            </button>
        </div>

        <div class="mwz-modal-search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
            </svg>
            <input type="search" id="mwz-sp-search" placeholder="Cari nama sparepart, merk, atau kode SKU..." autocomplete="off">
        </div>

        <form method="post" action="{{ route($prefix.'.servis.sparepart.store', $servis) }}" id="mwz-sp-submit-form" hidden>
            @csrf
            <input type="hidden" name="id_sparepart" id="mwz-sp-input-id">
            <input type="hidden" name="jumlah" id="mwz-sp-input-qty">
        </form>

        <div class="mwz-modal-list" id="mwz-sp-list">
            @forelse($spareparts as $part)
                <div class="mwz-sp-row" data-sp-item data-sp-search="{{ strtolower(trim($part->nama_sparepart.' '.$part->merk.' '.$part->kode_sparepart)) }}">
                    <div class="mwz-sp-info">
                        <strong>{{ $part->nama_sparepart }}</strong>
                        <small>
                            <span>SKU: {{ $part->kode_sparepart }}</span>
                            @if($part->merk)
                                <i>·</i>
                                <span>{{ $part->merk }}</span>
                            @endif
                            <i>·</i>
                            <span>Stok: {{ $part->stok }} {{ $part->satuan ?: 'pcs' }}</span>
                        </small>
                    </div>
                    <div class="mwz-sp-actions">
                        <input type="number" class="mwz-sp-qty" value="1" min="1" max="{{ $part->stok }}" aria-label="Jumlah {{ $part->nama_sparepart }}">
                        <button type="button" class="mwz-btn mwz-btn-primary mwz-sp-pick-btn" data-sp-pick="{{ $part->id_sparepart }}">Pilih</button>
                    </div>
                </div>
            @empty
                <div class="mwz-sp-empty">Belum ada data sparepart aktif yang memiliki stok.</div>
            @endforelse
            <div class="mwz-sp-empty" id="mwz-sp-no-match" hidden>Tidak ada sparepart yang cocok dengan pencarian Anda.</div>
        </div>

        <div class="mwz-modal-foot">
            <button type="button" class="mwz-btn mwz-btn-ghost" data-mwz-modal-close>Tutup</button>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.admin-main-content:has(.mwz-page),
.admin-content:has(.mwz-page),
main:has(.mwz-page) {
    padding-top: 10px !important;
}

.mwz-page {
    --mwz-ink: #0f172a;
    --mwz-muted: #64748b;
    --mwz-subtle: #94a3b8;
    --mwz-line: #e2e8f0;
    --mwz-line-soft: #f1f5f9;
    --mwz-surface: #ffffff;
    --mwz-bg-soft: #f8fafc;
    --mwz-accent: #ef233c;

    max-width: 1480px;
    margin: 0 auto;
    padding: 0 clamp(14px, 2.5vw, 28px) 48px;
    color: var(--mwz-ink);
}

.mwz-page * { box-sizing: border-box; }

/* 1. HEADER */
.mwz-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 18px;
    flex-wrap: wrap;
    padding-bottom: 16px;
    margin-bottom: 16px;
    border-bottom: 1px solid var(--mwz-line);
}

.mwz-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.mwz-thumb {
    width: 84px;
    height: 56px;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--mwz-line);
    background: var(--mwz-bg-soft);
    flex: 0 0 auto;
}

.mwz-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.mwz-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--mwz-muted);
    margin-bottom: 4px;
}

.mwz-eyebrow-dot {
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: var(--mwz-accent);
}

.mwz-title {
    margin: 0 0 4px;
    font-size: clamp(1.4rem, 2.3vw, 1.85rem);
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.1;
}

.mwz-subtitle {
    margin: 0;
    font-size: 13px;
    color: var(--mwz-muted);
}

.mwz-subtitle i {
    font-style: normal;
    margin: 0 5px;
    color: var(--mwz-subtle);
}

.mwz-subtitle strong {
    color: var(--mwz-ink);
}

/* 2. STEPPER */
.mwz-stepper {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 8px;
    padding: 14px 16px;
    margin-bottom: 20px;
    background: var(--mwz-surface);
    border: 1px solid var(--mwz-line);
    border-radius: 14px;
}

.mwz-step {
    position: relative;
    display: flex;
    align-items: center;
    gap: 9px;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--mwz-muted);
}

.mwz-step-no {
    width: 28px;
    height: 28px;
    border-radius: 999px;
    border: 1.5px solid #cbd5e1;
    background: #ffffff;
    color: var(--mwz-muted);
    display: grid;
    place-items: center;
    font-size: 11px;
    font-weight: 800;
    flex: 0 0 auto;
}

.mwz-step a {
    color: inherit;
    text-decoration: none;
}

.mwz-step.is-done {
    color: #059669;
}

.mwz-step.is-done .mwz-step-no {
    background: #10b981;
    border-color: #10b981;
    color: #ffffff;
}

.mwz-step.is-current {
    color: var(--mwz-ink);
}

.mwz-step.is-current .mwz-step-no {
    background: var(--mwz-accent);
    border-color: var(--mwz-accent);
    color: #ffffff;
    box-shadow: 0 0 0 3px rgba(239, 35, 60, 0.16);
}

.mwz-step.is-locked {
    opacity: 0.55;
}

/* 3. LAYOUT & CARDS */
.mwz-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 320px;
    gap: 20px;
    align-items: flex-start;
}

.mwz-main,
.mwz-sidebar {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.mwz-sidebar {
    position: sticky;
    top: 20px;
}

.mwz-card {
    background: var(--mwz-surface);
    border: 1px solid var(--mwz-line);
    border-radius: 16px;
    padding: 20px 22px;
}

.mwz-card-head {
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--mwz-line-soft);
}

.mwz-section-kicker {
    display: block;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--mwz-accent);
    margin-bottom: 4px;
}

.mwz-card-head h2 {
    margin: 0 0 4px;
    font-size: 17px;
    font-weight: 800;
    letter-spacing: -0.02em;
}

.mwz-card-head p {
    margin: 0;
    font-size: 13px;
    color: var(--mwz-muted);
    line-height: 1.5;
}

/* 4. INFO BOXES & TABLES */
.mwz-info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.mwz-info-box {
    padding: 12px 14px;
    border-radius: 12px;
    background: var(--mwz-bg-soft);
    border: 1px solid var(--mwz-line-soft);
}

.mwz-info-box small {
    display: block;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--mwz-muted);
    margin-bottom: 4px;
}

.mwz-info-box strong {
    display: block;
    font-size: 13.5px;
    color: var(--mwz-ink);
    line-height: 1.45;
}

.mwz-table-wrap {
    overflow-x: auto;
    border: 1px solid var(--mwz-line);
    border-radius: 12px;
}

.mwz-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}

.mwz-table th {
    padding: 11px 14px;
    background: var(--mwz-bg-soft);
    border-bottom: 1px solid var(--mwz-line);
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--mwz-muted);
}

.mwz-table td {
    padding: 12px 14px;
    border-bottom: 1px solid var(--mwz-line-soft);
    vertical-align: middle;
}

.mwz-table tbody tr:last-child td {
    border-bottom: 0;
}

.mwz-muted { color: var(--mwz-muted); }
.mwz-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 12px; color: var(--mwz-muted); }
.mwz-empty-cell { text-align: center; padding: 24px !important; color: var(--mwz-muted); }

/* 5. FORMS, INPUTS & SPAREPART PICKER */
.mwz-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.mwz-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.mwz-full { grid-column: 1 / -1; }

.mwz-field label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: var(--mwz-ink);
    margin-bottom: 6px;
}

.mwz-field input,
.mwz-field select,
.mwz-field textarea {
    width: 100%;
    height: 40px;
    padding: 0 12px;
    border: 1px solid var(--mwz-line);
    border-radius: 10px;
    background: var(--mwz-bg-soft);
    color: var(--mwz-ink);
    font: inherit;
    font-size: 13px;
    outline: none;
}

.mwz-field textarea {
    height: auto;
    padding: 10px 12px;
    resize: vertical;
}

.mwz-field input:focus,
.mwz-field select:focus,
.mwz-field textarea:focus {
    background: #ffffff;
    border-color: var(--mwz-ink);
}

.mwz-part-form-row {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    flex-wrap: wrap;
}

.mwz-search-results {
    margin-top: 10px;
    display: grid;
    gap: 8px;
}

.mwz-search-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 10px;
    background: var(--mwz-bg-soft);
    border: 1px solid var(--mwz-line);
}

.mwz-search-item strong {
    display: block;
    font-size: 13px;
}

.mwz-search-item small {
    color: var(--mwz-muted);
    font-size: 11.5px;
}

/* 6. APPROVAL CARDS & DRAWER */
.mwz-approval-list {
    display: grid;
    gap: 10px;
    margin-bottom: 14px;
}

.mwz-approval-card {
    padding: 14px 16px;
    border-radius: 12px;
    background: var(--mwz-bg-soft);
    border: 1px solid var(--mwz-line);
}

.mwz-approval-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 6px;
}

.mwz-approval-reason {
    margin: 0 0 10px;
    font-size: 13px;
    color: #334155;
    line-height: 1.5;
}

.mwz-approval-items {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.mwz-item-chip {
    padding: 4px 10px;
    border-radius: 7px;
    background: #ffffff;
    border: 1px solid var(--mwz-line);
    font-size: 12px;
    font-weight: 600;
}

.mwz-cust-note {
    display: block;
    margin-top: 8px;
    color: var(--mwz-muted);
    font-size: 12px;
}

.mwz-report-drawer summary {
    list-style: none;
}

.mwz-report-drawer summary::-webkit-details-marker {
    display: none;
}

.mwz-hint {
    color: var(--mwz-muted);
    font-size: 12px;
}

/* 7. BADGES, BUTTONS, ALERTS */
.mwz-pill {
    display: inline-flex;
    align-items: center;
    padding: 3px 9px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

.mwz-pill.is-emerald { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.mwz-pill.is-blue { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.mwz-pill.is-amber { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.mwz-pill.is-crimson { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

.mwz-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    height: 40px;
    padding: 0 16px;
    border-radius: 10px;
    font: inherit;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
    white-space: nowrap;
    transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}

.mwz-btn-primary { background: var(--mwz-ink); color: #ffffff; }
.mwz-btn-primary:hover { background: var(--mwz-accent); }
.mwz-btn-dark { background: #334155; color: #ffffff; }
.mwz-btn-ghost { background: #ffffff; border-color: var(--mwz-line); color: var(--mwz-ink); }
.mwz-btn-ghost:hover { background: var(--mwz-bg-soft); border-color: #cbd5e1; }

.mwz-del-btn {
    padding: 4px 10px;
    border-radius: 7px;
    border: 1px solid var(--mwz-line);
    background: #ffffff;
    color: var(--mwz-muted);
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
}

.mwz-del-btn:hover {
    border-color: #fecaca;
    background: #fef2f2;
    color: var(--mwz-accent);
}

.mwz-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    flex-wrap: wrap;
}

.mwz-alert,
.mwz-banner {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px 16px;
    margin-bottom: 16px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
}

.mwz-alert.is-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
.mwz-alert.is-warning,
.mwz-banner.is-amber { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
.mwz-alert.is-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

.mwz-banner p { margin: 2px 0 0; font-weight: 500; font-size: 12.5px; }
.mwz-banner-dot { width: 8px; height: 8px; border-radius: 999px; background: #f59e0b; flex: 0 0 auto; }

/* 8. QC & FINALISASI */
.mwz-qc-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.mwz-qc-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    border: 1px solid var(--mwz-line);
    background: var(--mwz-bg-soft);
    color: var(--mwz-text);
}

.mwz-qc-status-pill.is-passed { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
.mwz-qc-status-pill.is-failed { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
.mwz-qc-status-pill.is-progress { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }

.mwz-qc-status-dot { width: 8px; height: 8px; border-radius: 999px; background: currentColor; }

.mwz-banner.is-emerald { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
.mwz-banner.is-rose { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
.mwz-banner-dot.is-emerald { background: #10b981; }
.mwz-banner-dot.is-rose { background: #ef4444; }

.mwz-qc-empty {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    padding: 18px 20px;
    border-radius: 12px;
    background: var(--mwz-bg-soft);
    border: 1px dashed var(--mwz-line);
}

.mwz-qc-empty strong { display: block; font-size: 14px; margin-bottom: 4px; }
.mwz-qc-empty p { margin: 0; font-size: 12.5px; color: var(--mwz-muted); }

.mwz-qc-progress-strip {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    padding: 12px 16px;
    margin-bottom: 18px;
    border-radius: 12px;
    background: var(--mwz-bg-soft);
    border: 1px solid var(--mwz-line-soft);
}

.mwz-qc-progress-meta { display: flex; align-items: center; gap: 10px; }
.mwz-qc-progress-label { font-size: 12px; color: var(--mwz-muted); font-weight: 600; }
.mwz-qc-progress-meta strong { font-size: 13px; color: var(--mwz-text); }

.mwz-qc-progress-pills { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.mwz-qc-mini-stat {
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    background: #ffffff;
    border: 1px solid var(--mwz-line-soft);
}
.mwz-qc-mini-stat.is-pass strong { color: #059669; }
.mwz-qc-mini-stat.is-fail strong { color: #dc2626; }

.mwz-qc-section {
    padding: 18px;
    border-radius: 14px;
    border: 1px solid var(--mwz-line);
    background: #ffffff;
    display: grid;
    gap: 14px;
}

.mwz-qc-section-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--mwz-line-soft);
}

.mwz-qc-section-badge {
    display: inline-block;
    padding: 3px 9px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    background: #f1f5f9;
    color: #334155;
    margin-bottom: 6px;
}

.mwz-qc-section-badge.is-wo {
    background: #fef2f2;
    color: #b91c1c;
}

.mwz-qc-section-head h3 { margin: 0 0 3px; font-size: 15px; font-weight: 700; color: var(--mwz-text); }
.mwz-qc-section-head p { margin: 0; font-size: 12.5px; color: var(--mwz-muted); }

.mwz-qc-section-count {
    font-size: 12px;
    font-weight: 600;
    color: var(--mwz-muted);
    background: var(--mwz-bg-soft);
    border: 1px solid var(--mwz-line-soft);
    padding: 5px 11px;
    border-radius: 999px;
}

.mwz-qc-wo-ref {
    padding: 14px;
    border-radius: 12px;
    background: var(--mwz-bg-soft);
    border: 1px solid var(--mwz-line-soft);
}

.mwz-qc-wo-ref-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.mwz-qc-wo-box {
    background: #ffffff;
    border: 1px solid var(--mwz-line-soft);
    border-radius: 10px;
    padding: 12px 14px;
}

.mwz-qc-wo-kicker {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--mwz-muted);
    margin-bottom: 6px;
}

.mwz-qc-wo-box strong { display: block; font-size: 13.5px; margin-bottom: 4px; }
.mwz-qc-wo-box p { margin: 0; font-size: 12.5px; color: var(--mwz-muted); line-height: 1.45; }

.mwz-qc-wo-diag {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px dashed var(--mwz-line);
    font-size: 12px;
    color: var(--mwz-text);
}
.mwz-qc-wo-diag span { font-weight: 700; color: var(--mwz-muted); }

.mwz-qc-wo-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 6px;
}

.mwz-qc-wo-list li {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 10px;
    font-size: 12.5px;
    padding-bottom: 5px;
    border-bottom: 1px solid var(--mwz-line-soft);
}
.mwz-qc-wo-list li:last-child { border-bottom: 0; padding-bottom: 0; }
.mwz-qc-wo-list li small { color: var(--mwz-muted); font-size: 11.5px; flex-shrink: 0; }
.mwz-qc-wo-list li.is-muted { color: var(--mwz-muted); }

.mwz-qc-grid {
    display: grid;
    gap: 10px;
}

.mwz-qc-item {
    padding: 14px 16px;
    border-radius: 12px;
    background: var(--mwz-bg-soft);
    border: 1px solid var(--mwz-line-soft);
    transition: border-color 0.16s ease, background-color 0.16s ease;
}

.mwz-qc-item.is-pass {
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.mwz-qc-item.is-fail {
    background: #fef2f2;
    border-color: #fecaca;
}

.mwz-qc-item.is-na {
    background: #f8fafc;
    border-color: #cbd5e1;
}

.mwz-qc-item-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
}

.mwz-qc-item-info { flex: 1 1 280px; min-width: 0; }
.mwz-qc-item-title-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 2px; }
.mwz-qc-item-info strong { font-size: 13.5px; color: var(--mwz-text); }
.mwz-qc-item-info small { display: block; color: var(--mwz-muted); font-size: 12px; }

.mwz-qc-req-tag {
    font-size: 10.5px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 999px;
    background: #ffffff;
    border: 1px solid var(--mwz-line);
    color: var(--mwz-muted);
}

.mwz-qc-toggle-group {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.mwz-qc-pill {
    position: relative;
    cursor: pointer;
    user-select: none;
}

.mwz-qc-pill input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.mwz-qc-pill span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 7px 12px;
    border-radius: 8px;
    border: 1px solid var(--mwz-line);
    background: #ffffff;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--mwz-text);
    transition: all 0.15s ease;
}

.mwz-qc-pill.is-pass input:checked + span {
    background: #059669;
    border-color: #059669;
    color: #ffffff;
}

.mwz-qc-pill.is-fail input:checked + span {
    background: #dc2626;
    border-color: #dc2626;
    color: #ffffff;
}

.mwz-qc-pill.is-na input:checked + span {
    background: #475569;
    border-color: #475569;
    color: #ffffff;
}

.mwz-qc-pill input:disabled + span {
    opacity: 0.78;
    cursor: default;
}

.mwz-qc-note-wrap {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px dashed #fca5a5;
}

.mwz-qc-note-wrap[hidden] {
    display: none !important;
}

.mwz-qc-note-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-wrap: wrap;
    font-size: 12px;
    font-weight: 700;
    color: #991b1b;
    margin-bottom: 6px;
}

.mwz-qc-required-star {
    font-size: 11px;
    font-weight: 600;
    color: #dc2626;
}

.mwz-qc-note-input {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #fca5a5;
    border-radius: 9px;
    background: #ffffff;
    font: inherit;
    font-size: 12.5px;
    color: var(--mwz-text);
    resize: vertical;
}

.mwz-qc-note-input:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
}

.mwz-qc-client-alert {
    padding: 11px 14px;
    border-radius: 10px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
    font-size: 12.5px;
    font-weight: 600;
}

.mwz-qc-client-alert[hidden] {
    display: none !important;
}

.mwz-final-totals {
    padding: 14px 16px;
    border-radius: 12px;
    background: var(--mwz-bg-soft);
    border: 1px solid var(--mwz-line);
    display: grid;
    gap: 8px;
}

.mwz-total-row {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    color: var(--mwz-muted);
}

.mwz-total-row strong { color: var(--mwz-ink); }
.mwz-total-row.is-grand {
    padding-top: 8px;
    border-top: 1px solid var(--mwz-line);
    font-size: 15px;
    font-weight: 800;
    color: var(--mwz-ink);
}

.mwz-total-row.is-grand strong { color: var(--mwz-accent); font-size: 18px; }

/* 9. SIDEBAR */
.mwz-side-title {
    margin: 0 0 12px;
    font-size: 14.5px;
    font-weight: 800;
}

.mwz-side-dl {
    margin: 0;
    display: grid;
    gap: 10px;
}

.mwz-side-dl div {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    font-size: 12.5px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--mwz-line-soft);
}

.mwz-side-dl div:last-child { border-bottom: 0; padding-bottom: 0; }
.mwz-side-dl dt { color: var(--mwz-muted); }
.mwz-side-dl dd { margin: 0; font-weight: 700; text-align: right; }
.mwz-side-total dd { color: var(--mwz-accent); font-size: 15px; }
.mwz-side-note { margin: 0 0 12px; font-size: 12.5px; color: var(--mwz-muted); line-height: 1.5; }

@media (max-width: 1024px) {
    .mwz-layout { grid-template-columns: 1fr; }
    .mwz-sidebar { position: static; }
    .mwz-stepper { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 640px) {
    .mwz-info-grid,
    .mwz-form-grid,
    .mwz-qc-wo-ref-grid { grid-template-columns: 1fr; }
}

.mwz-card-head-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
}

/* 10. MODAL SPAREPART */
.mwz-modal-backdrop[hidden] {
    display: none !important;
}

.mwz-modal-backdrop {
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

.mwz-modal-dialog {
    width: 100%;
    max-width: 660px;
    max-height: calc(100dvh - 36px);
    display: flex;
    flex-direction: column;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25);
    overflow: hidden;
    color: #0f172a;
}

.mwz-modal-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    padding: 18px 22px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.mwz-modal-eyebrow {
    display: inline-block;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.12em;
    color: #ef233c;
    margin-bottom: 4px;
}

.mwz-modal-title {
    margin: 0 0 3px;
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -0.02em;
}

.mwz-modal-sub {
    margin: 0;
    font-size: 12.5px;
    color: #64748b;
}

.mwz-modal-close {
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

.mwz-modal-close:hover {
    color: #0f172a;
    border-color: #cbd5e1;
}

.mwz-modal-search {
    position: relative;
    padding: 14px 22px;
    border-bottom: 1px solid #f1f5f9;
    background: #ffffff;
}

.mwz-modal-search svg {
    position: absolute;
    left: 34px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
}

.mwz-modal-search input {
    width: 100%;
    height: 42px;
    padding: 0 14px 0 38px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    background: #f8fafc;
    color: #0f172a;
    font: inherit;
    font-size: 13.5px;
    outline: none;
}

.mwz-modal-search input:focus {
    background: #ffffff;
    border-color: #0f172a;
}

.mwz-modal-list {
    padding: 12px 22px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 1 1 auto;
}

.mwz-sp-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    transition: border-color 0.15s ease, background 0.15s ease;
}

.mwz-sp-row:hover {
    background: #ffffff;
    border-color: #cbd5e1;
}

.mwz-sp-row[hidden] {
    display: none !important;
}

.mwz-sp-info {
    min-width: 0;
}

.mwz-sp-info strong {
    display: block;
    font-size: 13.5px;
    color: #0f172a;
    margin-bottom: 2px;
}

.mwz-sp-info small {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    font-size: 11.5px;
    color: #64748b;
}

.mwz-sp-info i {
    font-style: normal;
    color: #94a3b8;
}

.mwz-sp-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.mwz-sp-qty {
    width: 64px;
    height: 34px;
    padding: 0 8px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #ffffff;
    text-align: center;
    font: inherit;
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
}

.mwz-sp-pick-btn {
    height: 34px !important;
    padding: 0 14px !important;
    font-size: 12px !important;
    background: #0f172a !important;
    color: #ffffff !important;
}

.mwz-sp-pick-btn:hover {
    background: #ef233c !important;
}

.mwz-sp-empty {
    padding: 32px 16px;
    text-align: center;
    font-size: 13px;
    color: #64748b;
}

.mwz-modal-foot {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding: 12px 22px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}
</style>
@endpush

@push('scripts')
<script>
(() => {
    const modal = document.getElementById('mwz-sparepart-modal');
    if (!modal) return;

    const searchInput = document.getElementById('mwz-sp-search');
    const items = [...modal.querySelectorAll('[data-sp-item]')];
    const noMatch = document.getElementById('mwz-sp-no-match');
    const submitForm = document.getElementById('mwz-sp-submit-form');
    const inputId = document.getElementById('mwz-sp-input-id');
    const inputQty = document.getElementById('mwz-sp-input-qty');

    const closeModal = () => {
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    const openModal = () => {
        if (searchInput) searchInput.value = '';
        items.forEach(el => { el.hidden = false; });
        if (noMatch) noMatch.hidden = items.length > 0;
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        setTimeout(() => searchInput?.focus(), 50);
    };

    document.querySelectorAll('[data-mwz-open-sparepart]').forEach(btn => {
        btn.addEventListener('click', openModal);
    });

    modal.querySelectorAll('[data-mwz-modal-close]').forEach(btn => {
        btn.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', e => {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !modal.hidden) closeModal();
    });

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.trim().toLowerCase();
            let visible = 0;
            items.forEach(el => {
                const hay = el.dataset.spSearch || '';
                const match = q === '' || hay.includes(q);
                el.hidden = !match;
                if (match) visible++;
            });
            if (noMatch) noMatch.hidden = visible > 0;
        });
    }

    modal.querySelectorAll('[data-sp-pick]').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('[data-sp-item]');
            const qtyEl = row?.querySelector('.mwz-sp-qty');
            const qty = Math.max(1, parseInt(qtyEl?.value || '1', 10));
            if (inputId) inputId.value = btn.dataset.spPick || '';
            if (inputQty) inputQty.value = String(qty);
            btn.disabled = true;
            btn.textContent = 'Menyimpan...';
            submitForm?.submit();
        });
    });
})();

(() => {
    const qcForm = document.querySelector('[data-qc-form]');
    if (!qcForm) return;

    const items = Array.from(qcForm.querySelectorAll('[data-qc-item]'));
    const countEl = document.querySelector('[data-qc-count]');
    const passEl = document.querySelector('[data-qc-pass-count]');
    const failEl = document.querySelector('[data-qc-fail-count]');
    const errorBox = qcForm.querySelector('[data-qc-client-error]');

    const syncState = () => {
        let checked = 0;
        let pass = 0;
        let fail = 0;

        items.forEach(item => {
            const selected = item.querySelector('input[type="radio"]:checked');
            const noteWrap = item.querySelector('[data-qc-note-wrap]');
            const noteInput = item.querySelector('[data-qc-note]');
            const val = selected ? selected.value : '';

            item.classList.remove('is-pass', 'is-fail', 'is-na');
            if (val === 'PASS') {
                item.classList.add('is-pass');
                checked++;
                pass++;
            } else if (val === 'FAIL') {
                item.classList.add('is-fail');
                checked++;
                fail++;
            } else if (val === 'N/A') {
                item.classList.add('is-na');
                checked++;
            }

            if (noteWrap && noteInput) {
                const isFail = val === 'FAIL';
                noteWrap.hidden = !isFail;
                noteWrap.classList.toggle('is-visible', isFail);
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
                syncState();
                if (radio.value === 'FAIL') {
                    const noteInput = item.querySelector('[data-qc-note]');
                    noteInput?.focus();
                }
            });
        });
    });

    qcForm.addEventListener('submit', e => {
        if (errorBox) errorBox.hidden = true;

        for (const item of items) {
            const selected = item.querySelector('input[type="radio"]:checked');
            if (!selected) {
                e.preventDefault();
                if (errorBox) {
                    errorBox.textContent = 'Semua item checklist pemeriksaan standar dan penyesuaian Work Order wajib dipilih hasilnya (Lulus / Tidak Lulus / N/A).';
                    errorBox.hidden = false;
                }
                item.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            if (selected.value === 'FAIL') {
                const noteInput = item.querySelector('[data-qc-note]');
                if (noteInput && noteInput.value.trim() === '') {
                    e.preventDefault();
                    if (errorBox) {
                        errorBox.textContent = 'Item yang dinyatakan Tidak Lulus wajib dilengkapi catatan perbaikan.';
                        errorBox.hidden = false;
                    }
                    noteInput.focus();
                    noteInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
            }
        }
    });

    syncState();
})();
</script>
@endpush
