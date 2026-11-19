@extends('layouts.app')

@section('title', 'Invoice Resmi ' . $transaksi->kode_transaksi . ' · YZA Automotive')

@section('content')
@php
    $wash = $transaksi->bookingCuci;
    $veh = $wash?->kendaraan ? trim($wash->kendaraan->merk . ' ' . $wash->kendaraan->model) : 'Kendaraan Car Wash';
    $remaining = max(0, (float) $transaksi->total - (float) $paid);
    $awardedPoints = (int) \App\Models\RiwayatPoin::where('id_transaksi', $transaksi->id_transaksi)->where('perubahan_poin', '>', 0)->sum('perubahan_poin');
    $estimatedPoints = app(\App\Services\YzaPointService::class)->calculate((string) $transaksi->total, $transaksi->jenis_transaksi);
@endphp
<div class="invoice-container">
    <style>
        .invoice-container { max-width: 960px; margin: 0 auto; padding: 16px 20px 80px; font-family: inherit; color: #0f172a; }
        .invoice-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
        .inv-btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; border: 1px solid #cbd5e1; background: #fff; color: #0f172a; cursor: pointer; }
        .inv-btn-primary { background: #0f172a; border-color: #0f172a; color: #fff; }
        .invoice-paper { background: #ffffff; border: 1px solid rgba(226, 232, 240, 0.9); border-radius: 20px; padding: 48px 52px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05); position: relative; }
        .inv-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0f172a; padding-bottom: 24px; margin-bottom: 24px; flex-wrap: nowrap; gap: 16px; }
        .inv-brand { display: flex; align-items: center; gap: 14px; min-width: 0; flex: 1 1 auto; }
        .inv-brand-logo { width: 56px; height: 56px; object-fit: contain; flex-shrink: 0; }
        .inv-brand h1 { font-size: 22px; font-weight: 900; letter-spacing: -0.02em; margin: 0; color: #0f172a; text-transform: uppercase; }
        .inv-brand span { color: #ef1b2d; }
        .inv-brand p { font-size: 11.5px; color: #64748b; margin: 4px 0 0; line-height: 1.5; }
        .inv-meta-right { text-align: right; flex-shrink: 0; }
        .inv-badge-title { font-size: 18px; font-weight: 800; color: #0f172a; letter-spacing: 0.04em; text-transform: uppercase; white-space: nowrap; }
        .inv-code { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 15px; font-weight: 800; color: #ef1b2d; margin-top: 4px; }
        .inv-grid-info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 28px; margin-bottom: 32px; padding-bottom: 28px; border-bottom: 1px dashed #e2e8f0; font-size: 13px; }
        .inv-info-block small { display: block; font-size: 10.5px; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.08em; margin-bottom: 6px; }
        .inv-info-block strong { display: block; font-size: 15px; color: #0f172a; }
        .inv-info-block span { display: block; color: #475569; margin-top: 3px; line-height: 1.5; }
        .inv-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 28px; }
        .inv-table thead th { background: #0f172a; color: #ffffff; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; padding: 12px 16px; text-align: left; }
        .inv-table tbody td { padding: 13px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .inv-summary-wrap { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 24px; margin-bottom: 28px; }
        .inv-summary-table { width: 360px; border-collapse: collapse; font-size: 13px; }
        .inv-summary-table td { padding: 7px 0; }
        .inv-summary-table tr.grand-total { border-top: 2px solid #0f172a; border-bottom: 2px solid #0f172a; font-size: 16px; font-weight: 800; }
        .inv-summary-table tr.grand-total td { padding: 12px 0; color: #ef1b2d; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 18px; }
        .inv-footer { margin-top: 40px; padding-top: 24px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: flex-end; font-size: 12px; color: #64748b; }
        @media print {
            body { background: #ffffff !important; }
            .invoice-toolbar, .admin-sidebar, .admin-topbar, .admin-footer, .admin-page-heading, .yza-header, .member-topbar, .yza-sidebar { display: none !important; }
            .invoice-container { padding: 0 !important; max-width: 100% !important; }
            .invoice-paper { border: none !important; box-shadow: none !important; padding: 20px 0 !important; }
        }
        @media (max-width: 640px) {
            .invoice-paper { padding: 26px 20px; }
            .inv-header { flex-wrap: wrap; }
            .inv-grid-info { grid-template-columns: 1fr; }
            .inv-meta-right { text-align: left; }
            .inv-summary-table { width: 100%; }
        }
    </style>

    <div class="invoice-toolbar">
        <a href="{{ url()->previous() }}" class="inv-btn">&larr; Kembali</a>
        <button type="button" onclick="window.print()" class="inv-btn inv-btn-primary">Cetak Receipt</button>
    </div>

    <article class="invoice-paper">
        <header class="inv-header">
            <div class="inv-brand">
                <img src="{{ asset('assets/images/branding/logo-yza.png') }}" alt="YZA Automotive" class="inv-brand-logo">
                <div>
                    <h1>YZA <span>AUTOMOTIVE</span></h1>
                    <p>
                        Authorized Workshop &amp; Certified Pre-Owned Dealership<br>
                        Jl. Veteran Otomotif No. 506, Jakarta Selatan<br>
                        Layanan Kasir: (021) 8899-7700 &middot; Email: finance@yza-automotive.com
                    </p>
                </div>
            </div>
            <div class="inv-meta-right">
                <div class="inv-badge-title">INVOICE FAKTUR CAR WASH</div>
                <div class="inv-code">{{ $transaksi->kode_transaksi }}</div>
                <div style="font-size:12px; color:#64748b; margin-top:4px;">
                    Tanggal Faktur: <strong>{{ $transaksi->tanggal?->translatedFormat('d F Y') }}</strong>
                </div>
            </div>
        </header>

        <div class="inv-grid-info">
            <div class="inv-info-block">
                <small>DITAGIHKAN KEPADA PELANGGAN:</small>
                <strong>{{ $transaksi->pelanggan?->nama_lengkap ?: 'Walk-in' }}</strong>
                <span>No. Kontak: {{ $transaksi->pelanggan?->no_telepon ?: '—' }}</span>
                <span>Kendaraan: {{ $veh }}</span>
            </div>
            <div class="inv-info-block">
                <small>RINCIAN BOOKING CAR WASH:</small>
                <strong>{{ $wash?->kode_booking ?: $transaksi->kode_transaksi }}</strong>
                <span>Nomor Polisi: <strong>{{ $wash?->kendaraan?->nomor_polisi ?: '—' }}</strong></span>
                <span>Jadwal: {{ optional($wash?->tanggal)->format('d M Y') }} {{ $wash?->jam ? substr((string) $wash->jam, 0, 5) . ' WIB' : '' }}</span>
            </div>
        </div>

        <table class="inv-table">
            <thead>
                <tr>
                    <th style="width:45px; text-align:center;">No</th>
                    <th>Deskripsi Paket Layanan Car Wash</th>
                    <th style="width:70px; text-align:center;">Qty</th>
                    <th style="width:155px; text-align:right;">Harga Satuan (Rp)</th>
                    <th style="width:155px; text-align:right;">Subtotal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align:center; font-weight:700; color:#64748b;">1</td>
                    <td>
                        <strong style="color:#0f172a;">{{ $wash?->paket?->nama_paket ?: 'Layanan Car Wash' }}</strong>
                        <small style="color:#64748b; display:block;">Paket Perawatan &amp; Cuci Kendaraan ({{ $veh }})</small>
                        @if($transaksi->id_promo)
                            <small style="color:#047857; display:block; margin-top:2px;">Promo: {{ $transaksi->promo_code_snapshot }} — {{ $transaksi->promo_name_snapshot }}</small>
                        @endif
                    </td>
                    <td style="text-align:center; font-weight:700;">1</td>
                    <td style="text-align:right; font-family:ui-monospace, monospace;">{{ number_format((float) $transaksi->subtotal, 0, ',', '.') }}</td>
                    <td style="text-align:right; font-family:ui-monospace, monospace; font-weight:800; color:#0f172a;">{{ number_format((float) $transaksi->subtotal, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="inv-summary-wrap">
            <div>
                @if($awardedPoints > 0 || $estimatedPoints > 0)
                    <div style="padding:12px 14px; border-radius:10px; background:#f8fafc; border:1px solid #e2e8f0; max-width:340px; font-size:12px;">
                        @if($awardedPoints > 0)
                            <strong style="color:#047857;">YZA Points Didapat: +{{ number_format($awardedPoints, 0, ',', '.') }} Poin</strong><br>
                            <small style="color:#475569;">Poin telah dikreditkan ke saldo YZA Points member.</small>
                        @else
                            <strong style="color:#0f172a;">Estimasi YZA Points: +{{ number_format($estimatedPoints, 0, ',', '.') }} Poin</strong><br>
                            <small style="color:#64748b;">Poin akan dikreditkan setelah transaksi berstatus Lunas.</small>
                        @endif
                    </div>
                @endif
            </div>

            <div>
                <table class="inv-summary-table">
                    <tr>
                        <td style="color:#64748b;">Subtotal Paket Car Wash:</td>
                        <td style="text-align:right; font-family:ui-monospace, monospace; font-weight:700;">Rp {{ number_format((float) $transaksi->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if((float) $transaksi->diskon > 0)
                        <tr>
                            <td style="color:#059669;">Diskon Member / Promo:</td>
                            <td style="text-align:right; color:#059669; font-family:ui-monospace, monospace; font-weight:700;">- Rp {{ number_format((float) $transaksi->diskon, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr class="grand-total">
                        <td>TOTAL TAGIHAN:</td>
                        <td style="text-align:right;">Rp {{ number_format((float) $transaksi->total, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="color:#64748b; padding-top:10px;">Total Pembayaran Terverifikasi:</td>
                        <td style="text-align:right; color:#059669; font-family:ui-monospace, monospace; font-weight:700; padding-top:10px;">Rp {{ number_format((float) $paid, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="color:#64748b;">Sisa Yang Harus Dibayar:</td>
                        <td style="text-align:right; font-family:ui-monospace, monospace; font-weight:800; color:{{ $remaining > 0 ? '#ef1b2d' : '#059669' }};">
                            Rp {{ number_format((float) $remaining, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <footer class="inv-footer">
            <div>
                <div>Waktu Cetak Dokumen: {{ now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB</div>
                <div>Petugas Kasir / Finance: <strong>{{ $transaksi->petugas?->nama_lengkap ?: (auth()->user()?->displayName() ?? 'Finance YZA') }}</strong></div>
            </div>
            <div style="text-align:center; min-width:180px;">
                <div style="height:60px;"></div>
                <div style="border-top:1px solid #0f172a; padding-top:6px; font-weight:800; font-size:12px; color:#0f172a; text-transform:uppercase; letter-spacing:0.04em;">
                    YZA Finance &amp; Cashier Dept.
                </div>
            </div>
        </footer>
    </article>
</div>
@endsection
