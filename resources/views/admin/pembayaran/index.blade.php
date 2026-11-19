@extends('layouts.app')

@section('title', 'Manajemen Pembayaran · YZA Automotive')

@section('content')
@php
    $indexRoute = route('admin.payments.index');
    $hasFilter = ($q ?? '') !== '' || ($status ?? '') !== '' || ($from ?? '') !== '' || ($to ?? '') !== '';
    $activeWorkflow = in_array(request('wf'), ['mobil', 'servis', 'carwash'], true) ? request('wf') : 'mobil';
    $carAlertCount = (int) ($carStats['waiting_confirmation'] ?? 0) + (int) ($carStats['unpaid_count'] ?? 0);
    $srvAlertCount = (int) ($serviceStats['waiting_confirmation'] ?? 0) + (int) ($serviceStats['unpaid_count'] ?? 0);
    $washAlertCount = (int) ($washStats['waiting_confirmation'] ?? 0) + (int) ($washStats['unpaid_count'] ?? 0);
    $baseQuery = array_filter([
        'q' => $q ?: null,
        'status' => $status ?: null,
        'from' => $from ?: null,
        'to' => $to ?: null,
        'wf' => $activeWorkflow,
        'car_tab' => $carTab,
        'service_tab' => $serviceTab,
        'wash_tab' => $washTab ?? 'offline',
    ]);
@endphp

<div class="fin-page">
    <header class="fin-header">
        <div>
            <div class="fin-eyebrow">
                <span class="fin-eyebrow-dot" aria-hidden="true"></span>
                <span>Finance &amp; Kasir · Manajemen Pembayaran</span>
            </div>
            <h1 class="fin-title">Pembayaran Mobil, Servis &amp; Car Wash</h1>
            <p class="fin-subtitle">Kelola dan verifikasi pembayaran pembelian unit mobil, tagihan servis bengkel, serta layanan car wash dalam tiga jalur terpisah.</p>
        </div>
    </header>

    @if(session('status') || session('success'))
        <div class="fin-alert is-success" role="alert">{{ session('status') ?? session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="fin-alert is-error" role="alert">{{ $errors->first() }}</div>
    @endif

    {{-- Global Search & Filter Bar --}}
    <form method="GET" action="{{ $indexRoute }}" class="fin-filter-bar">
        <input type="hidden" name="wf" id="fin-wf-input" value="{{ $activeWorkflow }}">
        <input type="hidden" name="car_tab" value="{{ $carTab }}">
        <input type="hidden" name="service_tab" value="{{ $serviceTab }}">
        <input type="hidden" name="wash_tab" value="{{ $washTab ?? 'offline' }}">
        <div class="fin-search-box">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="search" name="q" value="{{ $q }}" placeholder="Cari kode pembayaran, kode transaksi, nama member, nomor pesanan, atau kode WO...">
        </div>
        <select name="status" class="fin-select" aria-label="Filter status">
            <option value="">Semua Status</option>
            <option value="menunggu" @selected($status === 'menunggu')>Menunggu Verifikasi</option>
            <option value="terverifikasi" @selected($status === 'terverifikasi')>Terverifikasi</option>
            <option value="ditolak" @selected($status === 'ditolak')>Ditolak</option>
        </select>
        <input type="date" name="from" value="{{ $from }}" class="fin-date" aria-label="Dari tanggal">
        <input type="date" name="to" value="{{ $to }}" class="fin-date" aria-label="Sampai tanggal">
        <button type="submit" class="fin-btn fin-btn-primary">Terapkan</button>
        @if($hasFilter)
            <a href="{{ $indexRoute }}" class="fin-btn fin-btn-ghost">Reset</a>
        @endif
    </form>

    {{-- 3 WORKFLOW SWITCHER BUTTONS --}}
    <div class="fin-wf-switcher" role="tablist" aria-label="Pilih Workflow Pembayaran">
        <button type="button" class="fin-wf-btn {{ $activeWorkflow === 'mobil' ? 'is-active' : '' }}" data-wf-target="mobil" role="tab" aria-selected="{{ $activeWorkflow === 'mobil' ? 'true' : 'false' }}">
            @if($carAlertCount > 0)
                <span class="fin-wf-dot" title="{{ $carAlertCount }} pembayaran perlu diproses" aria-label="{{ $carAlertCount }} pembayaran aktif"></span>
            @endif
            <span class="fin-wf-icon is-car" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 3c-.1.3-.1.7-.1 1.1v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
            </span>
            <span class="fin-wf-text">
                <strong>Penjualan Mobil</strong>
                <small>{{ $carAlertCount > 0 ? $carAlertCount . ' tagihan / verifikasi' : 'Unit kendaraan' }}</small>
            </span>
        </button>

        <button type="button" class="fin-wf-btn {{ $activeWorkflow === 'servis' ? 'is-active' : '' }}" data-wf-target="servis" role="tab" aria-selected="{{ $activeWorkflow === 'servis' ? 'true' : 'false' }}">
            @if($srvAlertCount > 0)
                <span class="fin-wf-dot" title="{{ $srvAlertCount }} pembayaran perlu diproses" aria-label="{{ $srvAlertCount }} pembayaran aktif"></span>
            @endif
            <span class="fin-wf-icon is-service" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
            </span>
            <span class="fin-wf-text">
                <strong>Bengkel</strong>
                <small>{{ $srvAlertCount > 0 ? $srvAlertCount . ' tagihan / verifikasi' : 'Servis & work order' }}</small>
            </span>
        </button>

        <button type="button" class="fin-wf-btn {{ $activeWorkflow === 'carwash' ? 'is-active' : '' }}" data-wf-target="carwash" role="tab" aria-selected="{{ $activeWorkflow === 'carwash' ? 'true' : 'false' }}">
            @if($washAlertCount > 0)
                <span class="fin-wf-dot" title="{{ $washAlertCount }} pembayaran perlu diproses" aria-label="{{ $washAlertCount }} pembayaran aktif"></span>
            @endif
            <span class="fin-wf-icon is-wash" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
            </span>
            <span class="fin-wf-text">
                <strong>Car Wash</strong>
                <small>{{ $washAlertCount > 0 ? $washAlertCount . ' tagihan / verifikasi' : 'Layanan cuci mobil' }}</small>
            </span>
        </button>
    </div>

    {{-- SINGLE UNIFIED WORKFLOW BOX --}}
    <div class="fin-single-stage">

        {{-- =====================================================
             WORKFLOW 1: PEMBAYARAN MOBIL
             ===================================================== --}}
        <section class="fin-box" data-wf-panel="mobil" aria-label="Pembayaran Mobil" @if($activeWorkflow !== 'mobil') hidden @endif>
            <div class="fin-box-head">
                <div class="fin-box-title-wrap">
                    <div class="fin-box-icon is-car" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 3c-.1.3-.1.7-.1 1.1v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                    </div>
                    <div>
                        <span class="fin-box-kicker">Box 01 · Unit Kendaraan</span>
                        <h2>Pembayaran Mobil</h2>
                    </div>
                </div>
                @if(($carStats['waiting_confirmation'] ?? 0) > 0)
                    <span class="fin-badge is-amber">{{ $carStats['waiting_confirmation'] }} Perlu Verifikasi</span>
                @else
                    <span class="fin-badge is-slate">Penjualan Mobil</span>
                @endif
            </div>

            <div class="fin-mini-kpis">
                <div class="fin-mini-kpi">
                    <span>Terverifikasi</span>
                    <strong class="is-emerald">Rp {{ number_format($carStats['verified_total'] ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div class="fin-mini-kpi">
                    <span>Menunggu</span>
                    <strong class="is-amber">{{ number_format($carStats['waiting_confirmation'] ?? 0) }}</strong>
                </div>
                <div class="fin-mini-kpi">
                    <span>Belum Bayar</span>
                    <strong class="is-crimson">{{ number_format($carStats['unpaid_count'] ?? 0) }}</strong>
                </div>
            </div>

            <div class="fin-subtabs" role="tablist">
                <a href="{{ $indexRoute }}?{{ http_build_query(array_merge($baseQuery, ['car_tab' => 'offline'])) }}" class="fin-subtab {{ $carTab === 'offline' ? 'is-active' : '' }}">
                    Offline ({{ $carOffline->total() }})
                </a>
                <a href="{{ $indexRoute }}?{{ http_build_query(array_merge($baseQuery, ['car_tab' => 'online'])) }}" class="fin-subtab {{ $carTab === 'online' ? 'is-active' : '' }}">
                    Online ({{ $carOnline->total() }})
                </a>
                <a href="{{ $indexRoute }}?{{ http_build_query(array_merge($baseQuery, ['car_tab' => 'unpaid'])) }}" class="fin-subtab {{ $carTab === 'unpaid' ? 'is-active' : '' }}">
                    Belum Bayar ({{ $carUnpaid->total() }})
                </a>
            </div>

            <div class="fin-box-body">
                @if($carTab === 'online')
                    @forelse($carOnline as $row)
                        @php
                            $trx = $row->transaksi;
                            $car = $trx?->pesananMobilMember?->mobil ?? $trx?->penjualanMobil?->mobil;
                            $carName = $car ? trim($car->merk . ' ' . $car->model) : ($trx?->pesananMobilMember ? trim(($trx->pesananMobilMember->snapshot_merk ?? '') . ' ' . ($trx->pesananMobilMember->snapshot_model ?? '')) : 'Unit Mobil');
                            $st = $row->status_provider;
                            $stClass = in_array($st, ['settlement', 'capture'], true) ? 'is-emerald' : ($st === 'pending' ? 'is-amber' : 'is-crimson');
                        @endphp
                        <article class="fin-item">
                            <div class="fin-item-main">
                                <div class="fin-item-top">
                                    <span class="fin-mono">{{ $row->order_id }}</span>
                                    <span class="fin-pill {{ $stClass }}">{{ strtoupper($st) }}</span>
                                </div>
                                <strong>{{ $trx?->pelanggan?->nama_lengkap ?? 'Member' }} · {{ $carName }}</strong>
                                <span class="fin-meta">{{ $trx?->kode_transaksi ?? '—' }} · {{ $row->created_at?->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="fin-item-side">
                                <strong class="fin-amount">Rp {{ number_format((float) $row->gross_amount, 0, ',', '.') }}</strong>
                                @if($st === 'pending')
                                    <form method="POST" action="{{ route('admin.payments.online.sync', $row) }}">
                                        @csrf
                                        <button type="submit" class="fin-btn-sm fin-btn-ghost">Sync Midtrans</button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="fin-empty">Belum ada pembayaran online untuk pembelian mobil.</div>
                    @endforelse
                    @if($carOnline->hasPages())
                        <div class="fin-pagination">{{ $carOnline->links() }}</div>
                    @endif

                @elseif($carTab === 'unpaid')
                    @forelse($carUnpaid as $trx)
                        @php
                            $car = $trx->pesananMobilMember?->mobil ?? $trx->penjualanMobil?->mobil;
                            $carName = $car ? trim($car->merk . ' ' . $car->model) : ($trx->pesananMobilMember ? trim(($trx->pesananMobilMember->snapshot_merk ?? '') . ' ' . ($trx->pesananMobilMember->snapshot_model ?? '')) : 'Unit Mobil');
                            $carPayload = [
                                'kode' => $trx->kode_transaksi,
                                'ref' => $trx->pesananMobilMember?->nomor_pesanan ?? ($trx->penjualanMobil?->kode_penjualan ?? 'Penjualan Mobil'),
                                'customer' => $trx->pelanggan?->nama_lengkap ?? 'Member',
                                'vehicle' => $carName,
                                'item' => 'Pembelian Unit ' . $carName,
                                'date' => $trx->tanggal?->format('d M Y, H:i') ?? '—',
                                'subtotal' => (float) $trx->subtotal,
                                'discount' => (float) $trx->diskon,
                                'total' => (float) $trx->total,
                                'status' => 'Belum Bayar',
                                'pay_url' => route('admin.car-sale.payment.store', $trx),
                                'invoice_url' => route('admin.invoice.car-sale', $trx),
                            ];
                        @endphp
                        <article class="fin-item">
                            <div class="fin-item-main">
                                <div class="fin-item-top">
                                    <span class="fin-mono">{{ $trx->kode_transaksi }}</span>
                                    <span class="fin-pill is-crimson">Belum Bayar</span>
                                </div>
                                <strong>{{ $trx->pelanggan?->nama_lengkap ?? 'Member' }} · {{ $carName }}</strong>
                                <span class="fin-meta">{{ $trx->pesananMobilMember?->nomor_pesanan ?? ($trx->penjualanMobil?->kode_penjualan ?? '—') }} · {{ $trx->tanggal?->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="fin-item-side">
                                <strong class="fin-amount">Rp {{ number_format((float) $trx->total, 0, ',', '.') }}</strong>
                                <div class="fin-item-actions">
                                    <button type="button" class="fin-btn-sm fin-btn-ghost" data-fin-detail='@json($carPayload)'>Detail</button>
                                    <button type="button" class="fin-btn-sm fin-btn-primary" data-fin-pay='@json($carPayload)'>Catat Bayar</button>
                                    <button type="button" class="fin-btn-sm fin-btn-ghost" data-fin-invoice='@json($carPayload)'>Invoice</button>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="fin-empty">Tidak ada tagihan mobil yang belum dibayar.</div>
                    @endforelse
                    @if($carUnpaid->hasPages())
                        <div class="fin-pagination">{{ $carUnpaid->links() }}</div>
                    @endif

                @else
                    @forelse($carOffline as $row)
                        @php
                            $trx = $row->transaksi;
                            $car = $trx?->pesananMobilMember?->mobil ?? $trx?->penjualanMobil?->mobil;
                            $carName = $car ? trim($car->merk . ' ' . $car->model) : ($trx?->pesananMobilMember ? trim(($trx->pesananMobilMember->snapshot_merk ?? '') . ' ' . ($trx->pesananMobilMember->snapshot_model ?? '')) : 'Unit Mobil');
                            $stClass = $row->status === 'terverifikasi' ? 'is-emerald' : ($row->status === 'menunggu' ? 'is-amber' : 'is-crimson');
                        @endphp
                        <article class="fin-item">
                            <div class="fin-item-main">
                                <div class="fin-item-top">
                                    <span class="fin-mono">{{ $row->kode_pembayaran }}</span>
                                    <span class="fin-pill {{ $stClass }}">{{ ucfirst($row->status) }}</span>
                                </div>
                                <strong>{{ $trx?->pelanggan?->nama_lengkap ?? 'Member' }} · {{ $carName }}</strong>
                                <span class="fin-meta">{{ strtoupper($row->metode) }} · {{ $trx?->kode_transaksi ?? '—' }} · {{ $row->tanggal?->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="fin-item-side">
                                <strong class="fin-amount">Rp {{ number_format((float) $row->jumlah, 0, ',', '.') }}</strong>
                                <div class="fin-item-actions">
                                    @php
                                        $carOfflinePayload = [
                                            'kode' => $row->kode_pembayaran,
                                            'ref' => $trx?->kode_transaksi ?? '—',
                                            'customer' => $trx?->pelanggan?->nama_lengkap ?? 'Member',
                                            'vehicle' => $carName,
                                            'item' => 'Pembayaran ' . strtoupper($row->metode) . ' · ' . $carName,
                                            'date' => $row->tanggal?->format('d M Y, H:i') ?? '—',
                                            'subtotal' => (float) ($trx?->subtotal ?? $row->jumlah),
                                            'discount' => (float) ($trx?->diskon ?? 0),
                                            'total' => (float) $row->jumlah,
                                            'status' => ucfirst($row->status),
                                            'invoice_url' => $trx ? route('admin.invoice.car-sale', $trx) : null,
                                        ];
                                    @endphp
                                    <button type="button" class="fin-btn-sm fin-btn-ghost" data-fin-detail='@json($carOfflinePayload)'>Detail</button>
                                    @if($trx)
                                        <button type="button" class="fin-btn-sm fin-btn-ghost" data-fin-invoice='@json($carOfflinePayload)'>Invoice</button>
                                    @endif
                                    @if($row->status === 'menunggu')
                                        <form method="POST" action="{{ route('admin.payments.confirm', $row) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="fin-btn-sm fin-btn-emerald">Verifikasi</button>
                                        </form>
                                        <button type="button" class="fin-btn-sm fin-btn-crimson" data-reject-url="{{ route('admin.payments.reject', $row) }}" data-reject-code="{{ $row->kode_pembayaran }}">Tolak</button>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="fin-empty">Belum ada pembayaran offline untuk pembelian mobil.</div>
                    @endforelse
                    @if($carOffline->hasPages())
                        <div class="fin-pagination">{{ $carOffline->links() }}</div>
                    @endif
                @endif
            </div>
        </section>

        {{-- =====================================================
             WORKFLOW 2: PEMBAYARAN SERVIS
             ===================================================== --}}
        <section class="fin-box" data-wf-panel="servis" aria-label="Pembayaran Servis" @if($activeWorkflow !== 'servis') hidden @endif>
            <div class="fin-box-head">
                <div class="fin-box-title-wrap">
                    <div class="fin-box-icon is-service" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    </div>
                    <div>
                        <span class="fin-box-kicker">Box 02 · Bengkel &amp; Work Order</span>
                        <h2>Pembayaran Servis</h2>
                    </div>
                </div>
                @if(($serviceStats['waiting_confirmation'] ?? 0) > 0)
                    <span class="fin-badge is-amber">{{ $serviceStats['waiting_confirmation'] }} Perlu Verifikasi</span>
                @elseif(($serviceStats['unpaid_count'] ?? 0) > 0)
                    <span class="fin-badge is-crimson">{{ $serviceStats['unpaid_count'] }} Tagihan Aktif</span>
                @else
                    <span class="fin-badge is-slate">Servis Bengkel</span>
                @endif
            </div>

            <div class="fin-mini-kpis">
                <div class="fin-mini-kpi">
                    <span>Terverifikasi</span>
                    <strong class="is-emerald">Rp {{ number_format($serviceStats['verified_total'] ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div class="fin-mini-kpi">
                    <span>Menunggu</span>
                    <strong class="is-amber">{{ number_format($serviceStats['waiting_confirmation'] ?? 0) }}</strong>
                </div>
                <div class="fin-mini-kpi">
                    <span>Belum Bayar</span>
                    <strong class="is-crimson">{{ number_format($serviceStats['unpaid_count'] ?? 0) }}</strong>
                </div>
            </div>

            <div class="fin-subtabs" role="tablist">
                <a href="{{ $indexRoute }}?{{ http_build_query(array_merge($baseQuery, ['service_tab' => 'offline'])) }}" class="fin-subtab {{ $serviceTab === 'offline' ? 'is-active' : '' }}">
                    Offline ({{ $serviceOffline->total() }})
                </a>
                <a href="{{ $indexRoute }}?{{ http_build_query(array_merge($baseQuery, ['service_tab' => 'online'])) }}" class="fin-subtab {{ $serviceTab === 'online' ? 'is-active' : '' }}">
                    Online ({{ $serviceOnline->total() }})
                </a>
                <a href="{{ $indexRoute }}?{{ http_build_query(array_merge($baseQuery, ['service_tab' => 'unpaid'])) }}" class="fin-subtab {{ $serviceTab === 'unpaid' ? 'is-active' : '' }}">
                    Belum Bayar ({{ $serviceUnpaid->total() + $serviceReadyFromQc->count() }})
                </a>
            </div>

            <div class="fin-box-body">
                @php
                    $buildServicePayload = function ($srv, ?\App\Models\Transaksi $trx = null) {
                        $jasaList = ($srv?->details ?? collect())->map(fn ($d) => [
                            'name' => $d->deskripsi_pekerjaan,
                            'note' => $d->catatan ?: 'Pekerjaan bengkel',
                            'amount' => (float) $d->biaya,
                        ])->values()->all();
                        $partList = ($srv?->sparepartItems ?? collect())->map(fn ($p) => [
                            'name' => $p->sparepart?->nama_sparepart ?? 'Sparepart',
                            'qty' => (int) $p->jumlah,
                            'unit_price' => (float) $p->harga_satuan,
                            'subtotal' => (float) $p->subtotal,
                        ])->values()->all();
                        $jasaTotal = (float) collect($jasaList)->sum('amount');
                        $partTotal = (float) collect($partList)->sum('subtotal');
                        $discount = (float) ($trx?->diskon ?? 0);
                        $grandTotal = $trx ? (float) $trx->total : max(0, $jasaTotal + $partTotal - $discount);
                        return [
                            'kode_wo' => $srv?->kode_servis ?? 'WORK ORDER',
                            'kode_trx' => $trx?->kode_transaksi ?? 'Belum diterbitkan',
                            'customer' => $trx?->pelanggan?->nama_lengkap ?? ($srv?->pelanggan?->nama_lengkap ?? 'Member'),
                            'vehicle' => $srv?->kendaraan ? trim($srv->kendaraan->merk . ' ' . $srv->kendaraan->model) : 'Kendaraan Servis',
                            'plate' => $srv?->kendaraan?->nomor_polisi ?: 'Plat belum diisi',
                            'jasa' => $jasaList,
                            'parts' => $partList,
                            'jasa_total' => $jasaTotal,
                            'part_total' => $partTotal,
                            'discount' => $discount,
                            'grand_total' => $grandTotal,
                        ];
                    };
                @endphp

                @if($serviceTab === 'online')
                    @forelse($serviceOnline as $row)
                        @php
                            $trx = $row->transaksi;
                            $srv = $trx?->servis;
                            $veh = $srv?->kendaraan ? trim($srv->kendaraan->merk . ' ' . $srv->kendaraan->model) : 'Kendaraan Servis';
                            $st = $row->status_provider;
                            $stClass = in_array($st, ['settlement', 'capture'], true) ? 'is-emerald' : ($st === 'pending' ? 'is-amber' : 'is-crimson');
                        @endphp
                        <article class="fin-item">
                            <div class="fin-item-main">
                                <div class="fin-item-top">
                                    <span class="fin-mono">{{ $row->order_id }}</span>
                                    <span class="fin-pill {{ $stClass }}">{{ strtoupper($st) }}</span>
                                </div>
                                <strong>{{ $trx?->pelanggan?->nama_lengkap ?? 'Member' }} · {{ $veh }}</strong>
                                <span class="fin-meta">{{ $srv?->kode_servis ?? ($trx?->kode_transaksi ?? '—') }} · {{ $row->created_at?->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="fin-item-side">
                                <strong class="fin-amount">Rp {{ number_format((float) $row->gross_amount, 0, ',', '.') }}</strong>
                                <div class="fin-item-actions">
                                    <button type="button" class="fin-btn-sm fin-btn-ghost" data-srv-detail='@json($buildServicePayload($srv, $trx))'>Detail</button>
                                    @if($st === 'pending')
                                        <form method="POST" action="{{ route('admin.payments.online.sync', $row) }}">
                                            @csrf
                                            <button type="submit" class="fin-btn-sm fin-btn-ghost">Sync Midtrans</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="fin-empty">Belum ada pembayaran online untuk servis bengkel.</div>
                    @endforelse
                    @if($serviceOnline->hasPages())
                        <div class="fin-pagination">{{ $serviceOnline->links() }}</div>
                    @endif

                @elseif($serviceTab === 'unpaid')
                    @foreach($serviceReadyFromQc as $srv)
                        @php
                            $veh = $srv->kendaraan ? trim($srv->kendaraan->merk . ' ' . $srv->kendaraan->model) : 'Kendaraan Servis';
                            $estTotal = (float) $srv->details->sum('biaya') + (float) $srv->sparepartItems->sum('subtotal');
                        @endphp
                        <article class="fin-item">
                            <div class="fin-item-main">
                                <div class="fin-item-top">
                                    <span class="fin-mono">{{ $srv->kode_servis }}</span>
                                    <span class="fin-pill is-amber">Dari QC · Siap Tagih</span>
                                </div>
                                <strong>{{ $srv->pelanggan?->nama_lengkap ?? 'Member' }} · {{ $veh }}</strong>
                                <span class="fin-meta">{{ $srv->kendaraan?->nomor_polisi ?? 'Plat —' }} · {{ $srv->details->count() }} Jasa · {{ $srv->sparepartItems->count() }} Sparepart</span>
                            </div>
                            <div class="fin-item-side">
                                <strong class="fin-amount">Rp {{ number_format($estTotal, 0, ',', '.') }}</strong>
                                <div class="fin-item-actions">
                                    <button type="button" class="fin-btn-sm fin-btn-ghost" data-srv-detail='@json($buildServicePayload($srv, null))'>Detail</button>
                                    <form method="POST" action="{{ route('admin.payments.servis.checkout', $srv) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="fin-btn-sm fin-btn-primary">Proses Pembayaran →</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach

                    @forelse($serviceUnpaid as $trx)
                        @php
                            $srv = $trx->servis;
                            $veh = $srv?->kendaraan ? trim($srv->kendaraan->merk . ' ' . $srv->kendaraan->model) : 'Kendaraan Servis';
                        @endphp
                        <article class="fin-item">
                            <div class="fin-item-main">
                                <div class="fin-item-top">
                                    <span class="fin-mono">{{ $trx->kode_transaksi }}</span>
                                    <span class="fin-pill is-crimson">Belum Bayar</span>
                                </div>
                                <strong>{{ $trx->pelanggan?->nama_lengkap ?? 'Member' }} · {{ $veh }}</strong>
                                <span class="fin-meta">{{ $srv?->kode_servis ?? 'Work Order' }} · {{ $srv?->details?->count() ?? 0 }} Jasa · {{ $srv?->sparepartItems?->count() ?? 0 }} Sparepart</span>
                            </div>
                            <div class="fin-item-side">
                                <strong class="fin-amount">Rp {{ number_format((float) $trx->total, 0, ',', '.') }}</strong>
                                <div class="fin-item-actions">
                                    @php
                                        $srvPayPayload = [
                                            'kode' => $trx->kode_transaksi,
                                            'ref' => $srv?->kode_servis ?? 'Work Order',
                                            'customer' => $trx->pelanggan?->nama_lengkap ?? 'Member',
                                            'vehicle' => $veh,
                                            'item' => ($srv?->details?->count() ?? 0) . ' Jasa · ' . ($srv?->sparepartItems?->count() ?? 0) . ' Sparepart',
                                            'date' => $trx->tanggal?->format('d M Y, H:i') ?? '—',
                                            'subtotal' => (float) $trx->subtotal,
                                            'discount' => (float) $trx->diskon,
                                            'total' => (float) $trx->total,
                                            'status' => 'Belum Bayar',
                                            'pay_url' => route('admin.payment.store', $trx),
                                            'invoice_url' => route('admin.invoice.servis', $trx),
                                        ];
                                    @endphp
                                    <button type="button" class="fin-btn-sm fin-btn-ghost" data-srv-detail='@json($buildServicePayload($srv, $trx))'>Detail</button>
                                    <button type="button" class="fin-btn-sm fin-btn-primary" data-fin-pay='@json($srvPayPayload)'>Catat Bayar</button>
                                    <button type="button" class="fin-btn-sm fin-btn-ghost" data-fin-invoice='@json($srvPayPayload)'>Invoice</button>
                                </div>
                            </div>
                        </article>
                    @empty
                        @if($serviceReadyFromQc->isEmpty())
                            <div class="fin-empty">Tidak ada tagihan servis yang belum dibayar.</div>
                        @endif
                    @endforelse
                    @if($serviceUnpaid->hasPages())
                        <div class="fin-pagination">{{ $serviceUnpaid->links() }}</div>
                    @endif

                @else
                    @forelse($serviceOffline as $row)
                        @php
                            $trx = $row->transaksi;
                            $srv = $trx?->servis;
                            $veh = $srv?->kendaraan ? trim($srv->kendaraan->merk . ' ' . $srv->kendaraan->model) : 'Kendaraan Servis';
                            $stClass = $row->status === 'terverifikasi' ? 'is-emerald' : ($row->status === 'menunggu' ? 'is-amber' : 'is-crimson');
                        @endphp
                        <article class="fin-item">
                            <div class="fin-item-main">
                                <div class="fin-item-top">
                                    <span class="fin-mono">{{ $row->kode_pembayaran }}</span>
                                    <span class="fin-pill {{ $stClass }}">{{ ucfirst($row->status) }}</span>
                                </div>
                                <strong>{{ $trx?->pelanggan?->nama_lengkap ?? 'Member' }} · {{ $veh }}</strong>
                                <span class="fin-meta">{{ strtoupper($row->metode) }} · {{ $srv?->kode_servis ?? ($trx?->kode_transaksi ?? '—') }} · {{ $row->tanggal?->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="fin-item-side">
                                <strong class="fin-amount">Rp {{ number_format((float) $row->jumlah, 0, ',', '.') }}</strong>
                                <div class="fin-item-actions">
                                    <button type="button" class="fin-btn-sm fin-btn-ghost" data-srv-detail='@json($buildServicePayload($srv, $trx))'>Rincian</button>
                                    <a href="{{ route('admin.payments.show', $row) }}" class="fin-btn-sm fin-btn-ghost">Detail</a>
                                    @if($row->status === 'menunggu')
                                        <form method="POST" action="{{ route('admin.payments.confirm', $row) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="fin-btn-sm fin-btn-emerald">Verifikasi</button>
                                        </form>
                                        <button type="button" class="fin-btn-sm fin-btn-crimson" data-reject-url="{{ route('admin.payments.reject', $row) }}" data-reject-code="{{ $row->kode_pembayaran }}">Tolak</button>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="fin-empty">Belum ada pembayaran offline untuk servis bengkel.</div>
                    @endforelse
                    @if($serviceOffline->hasPages())
                        <div class="fin-pagination">{{ $serviceOffline->links() }}</div>
                    @endif
                @endif
            </div>
        </section>

        {{-- =====================================================
             BOX 3: PEMBAYARAN CAR WASH
             ===================================================== --}}
        {{-- =====================================================
             WORKFLOW 3: PEMBAYARAN CAR WASH
             ===================================================== --}}
        <section class="fin-box" data-wf-panel="carwash" aria-label="Pembayaran Car Wash" @if($activeWorkflow !== 'carwash') hidden @endif>
            <div class="fin-box-head">
                <div class="fin-box-title-wrap">
                    <div class="fin-box-icon is-wash" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                    </div>
                    <div>
                        <span class="fin-box-kicker">Box 03 · Layanan Car Wash</span>
                        <h2>Pembayaran Car Wash</h2>
                    </div>
                </div>
                @if(($washStats['waiting_confirmation'] ?? 0) > 0)
                    <span class="fin-badge is-amber">{{ $washStats['waiting_confirmation'] }} Perlu Verifikasi</span>
                @elseif(($washStats['unpaid_count'] ?? 0) > 0)
                    <span class="fin-badge is-crimson">{{ $washStats['unpaid_count'] }} Tagihan Aktif</span>
                @else
                    <span class="fin-badge is-slate">Car Wash</span>
                @endif
            </div>

            <div class="fin-mini-kpis">
                <div class="fin-mini-kpi">
                    <span>Terverifikasi</span>
                    <strong class="is-emerald">Rp {{ number_format($washStats['verified_total'] ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div class="fin-mini-kpi">
                    <span>Menunggu</span>
                    <strong class="is-amber">{{ number_format($washStats['waiting_confirmation'] ?? 0) }}</strong>
                </div>
                <div class="fin-mini-kpi">
                    <span>Belum Bayar</span>
                    <strong class="is-crimson">{{ number_format($washStats['unpaid_count'] ?? 0) }}</strong>
                </div>
            </div>

            <div class="fin-subtabs" role="tablist">
                <a href="{{ $indexRoute }}?{{ http_build_query(array_merge($baseQuery, ['wash_tab' => 'offline'])) }}" class="fin-subtab {{ ($washTab ?? 'offline') === 'offline' ? 'is-active' : '' }}">
                    Offline ({{ $washOffline->total() }})
                </a>
                <a href="{{ $indexRoute }}?{{ http_build_query(array_merge($baseQuery, ['wash_tab' => 'online'])) }}" class="fin-subtab {{ ($washTab ?? 'offline') === 'online' ? 'is-active' : '' }}">
                    Online ({{ $washOnline->total() }})
                </a>
                <a href="{{ $indexRoute }}?{{ http_build_query(array_merge($baseQuery, ['wash_tab' => 'unpaid'])) }}" class="fin-subtab {{ ($washTab ?? 'offline') === 'unpaid' ? 'is-active' : '' }}">
                    Belum Bayar ({{ $washUnpaid->total() }})
                </a>
            </div>

            <div class="fin-box-body">
                @if(($washTab ?? 'offline') === 'online')
                    @forelse($washOnline as $gw)
                        @php
                            $trx = $gw->transaksi;
                            $wash = $trx?->bookingCuci;
                            $veh = $wash?->kendaraan ? trim($wash->kendaraan->merk . ' ' . $wash->kendaraan->model) : 'Kendaraan Car Wash';
                            $st = strtolower((string) $gw->status_provider);
                            $stClass = in_array($st, ['settlement', 'capture'], true) ? 'is-emerald' : ($st === 'pending' ? 'is-amber' : 'is-crimson');
                        @endphp
                        <article class="fin-item">
                            <div class="fin-item-main">
                                <div class="fin-item-top">
                                    <span class="fin-mono">{{ $gw->order_id }}</span>
                                    <span class="fin-pill {{ $stClass }}">{{ strtoupper($gw->status_provider) }}</span>
                                </div>
                                <strong>{{ $trx?->pelanggan?->nama_lengkap ?? 'Member' }} · {{ $veh }}</strong>
                                <span class="fin-meta">{{ $wash?->kode_booking ?? ($trx?->kode_transaksi ?? '—') }} · {{ $wash?->paket?->nama_paket ?? 'Paket Cuci' }} · {{ $gw->created_at?->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="fin-item-side">
                                <strong class="fin-amount">Rp {{ number_format((float) $gw->gross_amount, 0, ',', '.') }}</strong>
                            </div>
                        </article>
                    @empty
                        <div class="fin-empty">Belum ada pembayaran online untuk car wash.</div>
                    @endforelse
                    @if($washOnline->hasPages())
                        <div class="fin-pagination">{{ $washOnline->links() }}</div>
                    @endif

                @elseif(($washTab ?? 'offline') === 'unpaid')
                    @forelse($washUnpaid as $trx)
                        @php
                            $wash = $trx->bookingCuci;
                            $veh = $wash?->kendaraan ? trim($wash->kendaraan->merk . ' ' . $wash->kendaraan->model) : 'Kendaraan Car Wash';
                            $washPayload = [
                                'kode' => $trx->kode_transaksi,
                                'ref' => $wash?->kode_booking ?? 'Car Wash',
                                'customer' => $trx->pelanggan?->nama_lengkap ?? 'Member',
                                'vehicle' => $veh,
                                'item' => $wash?->paket?->nama_paket ?? 'Paket Cuci Mobil',
                                'date' => $trx->tanggal?->format('d M Y, H:i') ?? '—',
                                'subtotal' => (float) $trx->subtotal,
                                'discount' => (float) $trx->diskon,
                                'total' => (float) $trx->total,
                                'status' => 'Belum Bayar',
                                'pay_url' => route('admin.payment.store', $trx),
                                'invoice_url' => route('admin.invoice.carwash', $trx),
                            ];
                        @endphp
                        <article class="fin-item">
                            <div class="fin-item-main">
                                <div class="fin-item-top">
                                    <span class="fin-mono">{{ $trx->kode_transaksi }}</span>
                                    <span class="fin-pill is-crimson">Belum Bayar</span>
                                </div>
                                <strong>{{ $trx->pelanggan?->nama_lengkap ?? 'Member' }} · {{ $veh }}</strong>
                                <span class="fin-meta">{{ $wash?->kode_booking ?? 'Car Wash' }} · {{ $wash?->paket?->nama_paket ?? 'Paket Cuci' }}</span>
                            </div>
                            <div class="fin-item-side">
                                <strong class="fin-amount">Rp {{ number_format((float) $trx->total, 0, ',', '.') }}</strong>
                                <div class="fin-item-actions">
                                    <button type="button" class="fin-btn-sm fin-btn-ghost" data-fin-detail='@json($washPayload)'>Detail</button>
                                    <button type="button" class="fin-btn-sm fin-btn-primary" data-fin-pay='@json($washPayload)'>Catat Bayar</button>
                                    <button type="button" class="fin-btn-sm fin-btn-ghost" data-fin-invoice='@json($washPayload)'>Invoice</button>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="fin-empty">Tidak ada tagihan car wash yang belum dibayar.</div>
                    @endforelse
                    @if($washUnpaid->hasPages())
                        <div class="fin-pagination">{{ $washUnpaid->links() }}</div>
                    @endif

                @else
                    @forelse($washOffline as $row)
                        @php
                            $trx = $row->transaksi;
                            $wash = $trx?->bookingCuci;
                            $veh = $wash?->kendaraan ? trim($wash->kendaraan->merk . ' ' . $wash->kendaraan->model) : 'Kendaraan Car Wash';
                            $stClass = $row->status === 'terverifikasi' ? 'is-emerald' : ($row->status === 'menunggu' ? 'is-amber' : 'is-crimson');
                        @endphp
                        <article class="fin-item">
                            <div class="fin-item-main">
                                <div class="fin-item-top">
                                    <span class="fin-mono">{{ $row->kode_pembayaran }}</span>
                                    <span class="fin-pill {{ $stClass }}">{{ ucfirst($row->status) }}</span>
                                </div>
                                <strong>{{ $trx?->pelanggan?->nama_lengkap ?? 'Member' }} · {{ $veh }}</strong>
                                <span class="fin-meta">{{ strtoupper($row->metode) }} · {{ $wash?->kode_booking ?? ($trx?->kode_transaksi ?? '—') }} · {{ $row->tanggal?->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="fin-item-side">
                                <strong class="fin-amount">Rp {{ number_format((float) $row->jumlah, 0, ',', '.') }}</strong>
                                <div class="fin-item-actions">
                                    @php
                                        $washOffPayload = [
                                            'kode' => $row->kode_pembayaran,
                                            'ref' => $wash?->kode_booking ?? ($trx?->kode_transaksi ?? '—'),
                                            'customer' => $trx?->pelanggan?->nama_lengkap ?? 'Member',
                                            'vehicle' => $veh,
                                            'item' => strtoupper($row->metode) . ' · ' . ($wash?->paket?->nama_paket ?? 'Paket Cuci'),
                                            'date' => $row->tanggal?->format('d M Y, H:i') ?? '—',
                                            'subtotal' => (float) ($trx?->subtotal ?? $row->jumlah),
                                            'discount' => (float) ($trx?->diskon ?? 0),
                                            'total' => (float) $row->jumlah,
                                            'status' => ucfirst($row->status),
                                            'invoice_url' => $trx ? route('admin.invoice.carwash', $trx) : null,
                                        ];
                                    @endphp
                                    <button type="button" class="fin-btn-sm fin-btn-ghost" data-fin-detail='@json($washOffPayload)'>Detail</button>
                                    @if($trx)
                                        <button type="button" class="fin-btn-sm fin-btn-ghost" data-fin-invoice='@json($washOffPayload)'>Invoice</button>
                                    @endif
                                    @if($row->status === 'menunggu')
                                        <form method="POST" action="{{ route('admin.payment.verify', $row) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="fin-btn-sm fin-btn-emerald">Verifikasi</button>
                                        </form>
                                        <button type="button" class="fin-btn-sm fin-btn-crimson" data-reject-url="{{ route('admin.payment.reject', $row) }}" data-reject-code="{{ $row->kode_pembayaran }}">Tolak</button>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="fin-empty">Belum ada pembayaran offline untuk car wash.</div>
                    @endforelse
                    @if($washOffline->hasPages())
                        <div class="fin-pagination">{{ $washOffline->links() }}</div>
                    @endif
                @endif
            </div>
        </section>

    </div>
</div>

{{-- Modal Tolak Pembayaran --}}
<div class="fin-modal-backdrop" id="fin-reject-modal" hidden aria-hidden="true" role="dialog" aria-modal="true">
    <div class="fin-modal-dialog">
        <div class="fin-modal-head">
            <div>
                <span class="fin-mono" id="fin-reject-code" style="font-size:11px; color:#ef233c;">PEMBAYARAN</span>
                <h3>Tolak Pembayaran</h3>
            </div>
            <button type="button" class="fin-modal-close" data-reject-close aria-label="Tutup">&times;</button>
        </div>
        <form method="POST" id="fin-reject-form">
            @csrf
            <div class="fin-modal-body">
                <label for="fin-reject-reason">Alasan Penolakan <span style="color:#dc2626;">*</span></label>
                <textarea id="fin-reject-reason" name="rejection_reason" rows="3" required placeholder="Tuliskan alasan penolakan pembayaran (mis. nominal tidak sesuai atau bukti transfer tidak valid)..."></textarea>
            </div>
            <div class="fin-modal-foot">
                <button type="button" class="fin-btn fin-btn-ghost" data-reject-close>Batal</button>
                <button type="submit" class="fin-btn fin-btn-crimson">Tolak Pembayaran</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Detail Jasa & Sparepart Servis --}}
<div class="fin-modal-backdrop" id="fin-service-detail-modal" hidden aria-hidden="true" role="dialog" aria-modal="true">
    <div class="fin-modal-dialog" style="max-width: 580px;">
        <div class="fin-modal-head">
            <div>
                <span class="fin-mono" id="fin-sd-kode" style="font-size:11px; color:#ef233c;">WORK ORDER</span>
                <h3 id="fin-sd-vehicle">Detail Servis</h3>
                <p id="fin-sd-sub" style="margin:2px 0 0; font-size:12px; color:#64748b;">—</p>
            </div>
            <button type="button" class="fin-modal-close" data-sd-close aria-label="Tutup">&times;</button>
        </div>
        <div class="fin-modal-body" style="max-height: 70vh; overflow-y: auto; gap: 14px;">
            <div class="fin-sd-block">
                <span class="fin-sd-label">Rincian Jasa / Pekerjaan</span>
                <div id="fin-sd-jasa" class="fin-sd-list"></div>
            </div>
            <div class="fin-sd-block">
                <span class="fin-sd-label">Sparepart yang Digunakan</span>
                <div id="fin-sd-parts" class="fin-sd-list"></div>
            </div>
            <div class="fin-sd-totals">
                <div class="fin-sd-total-row"><span>Total Jasa</span><strong id="fin-sd-jasa-total">Rp 0</strong></div>
                <div class="fin-sd-total-row"><span>Total Sparepart</span><strong id="fin-sd-part-total">Rp 0</strong></div>
                <div class="fin-sd-total-row" id="fin-sd-disc-row" hidden><span>Diskon</span><strong id="fin-sd-disc-total">- Rp 0</strong></div>
                <div class="fin-sd-total-row is-grand"><span>Total Tagihan</span><strong id="fin-sd-grand-total">Rp 0</strong></div>
            </div>
        </div>
        <div class="fin-modal-foot">
            <button type="button" class="fin-btn fin-btn-primary" data-sd-close>Tutup</button>
        </div>
    </div>
</div>

{{-- Modal Detail Pembayaran / Transaksi Universal --}}
<div class="fin-modal-backdrop" id="fin-universal-detail-modal" hidden aria-hidden="true" role="dialog" aria-modal="true">
    <div class="fin-modal-dialog">
        <div class="fin-modal-head">
            <div>
                <span class="fin-mono" id="fin-ud-kode" style="font-size:11px; color:#ef233c;">DETAIL TRANSAKSI</span>
                <h3 id="fin-ud-title">Detail Transaksi</h3>
            </div>
            <button type="button" class="fin-modal-close" data-ud-close aria-label="Tutup">&times;</button>
        </div>
        <div class="fin-modal-body" style="display:flex; flex-direction:column; gap:12px;">
            <div class="fin-sd-row">
                <div>
                    <small style="margin-bottom:2px;">Pelanggan</small>
                    <strong id="fin-ud-customer">—</strong>
                </div>
                <span class="fin-pill is-amber" id="fin-ud-status">—</span>
            </div>
            <div class="fin-sd-row">
                <div>
                    <small style="margin-bottom:2px;">Layanan / Unit</small>
                    <strong id="fin-ud-item">—</strong>
                </div>
                <small id="fin-ud-date" style="white-space:nowrap;">—</small>
            </div>
            <div class="fin-sd-totals">
                <div class="fin-sd-total-row"><span>Subtotal</span><strong id="fin-ud-subtotal">Rp 0</strong></div>
                <div class="fin-sd-total-row"><span>Diskon / Promo</span><strong id="fin-ud-discount">Rp 0</strong></div>
                <div class="fin-sd-total-row is-grand"><span>Total Tagihan</span><strong id="fin-ud-total">Rp 0</strong></div>
            </div>
        </div>
        <div class="fin-modal-foot">
            <button type="button" class="fin-btn fin-btn-primary" data-ud-close>Tutup</button>
        </div>
    </div>
</div>

{{-- Modal Catat Pembayaran --}}
<div class="fin-modal-backdrop" id="fin-pay-modal" hidden aria-hidden="true" role="dialog" aria-modal="true">
    <div class="fin-modal-dialog">
        <div class="fin-modal-head">
            <div>
                <span class="fin-mono" id="fin-pay-kode" style="font-size:11px; color:#ef233c;">CATAT PEMBAYARAN</span>
                <h3>Catat Pembayaran Baru</h3>
            </div>
            <button type="button" class="fin-modal-close" data-pay-close aria-label="Tutup">&times;</button>
        </div>
        <form method="POST" id="fin-pay-form" enctype="multipart/form-data">
            @csrf
            <div class="fin-modal-body" style="display:flex; flex-direction:column; gap:14px;">
                <div class="fin-sd-banner">
                    <div style="min-width:0;">
                        <small style="display:block; font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#64748b; margin-bottom:2px;">Tagihan Pelanggan</small>
                        <strong id="fin-pay-summary" style="display:block; font-size:13px; font-weight:700; color:#0f172a; line-height:1.35;">—</strong>
                    </div>
                    <span id="fin-pay-total-label" style="font-size:15px; font-weight:800; color:#ef233c; white-space:nowrap; font-variant-numeric:tabular-nums;">Rp 0</span>
                </div>
                <div class="fin-form-grid-2">
                    <label class="fin-field">
                        <span>Metode Pembayaran *</span>
                        <select name="metode" class="fin-select" style="width:100%;" required>
                            <option value="tunai">Tunai (Cash)</option>
                            <option value="transfer">Transfer Bank</option>
                        </select>
                    </label>
                    <label class="fin-field">
                        <span>Jumlah Pembayaran (Rp) *</span>
                        <input type="number" id="fin-pay-amount" name="jumlah" class="fin-select" style="width:100%;" min="1" step="1" required>
                    </label>
                </div>
                <label class="fin-field">
                    <span>Bukti Transfer (Opsional)</span>
                    <input type="file" name="bukti_transfer" accept="image/*" class="fin-file-input">
                </label>
                <label class="fin-field">
                    <span>Catatan Pembayaran</span>
                    <textarea name="catatan" rows="2" placeholder="Nomor referensi / catatan kasir (opsional)..."></textarea>
                </label>
            </div>
            <div class="fin-modal-foot">
                <button type="button" class="fin-btn fin-btn-ghost" data-pay-close>Batal</button>
                <button type="submit" class="fin-btn fin-btn-primary">Simpan Pembayaran</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Preview & Cetak Invoice Receipt --}}
<div class="fin-modal-backdrop" id="fin-invoice-modal" hidden aria-hidden="true" role="dialog" aria-modal="true">
    <div class="fin-modal-dialog" style="max-width:940px; height:88vh; display:flex; flex-direction:column;">
        <div class="fin-modal-head" style="flex-shrink:0;">
            <div>
                <span class="fin-mono" id="fin-inv-kode" style="font-size:11px; color:#ef233c;">INVOICE &amp; RECEIPT RESMI</span>
                <h3>Pratinjau &amp; Cetak Receipt</h3>
            </div>
            <button type="button" class="fin-modal-close" data-inv-close aria-label="Tutup">&times;</button>
        </div>
        <div class="fin-modal-body" style="padding:0; flex:1 1 auto; min-height:0; background:#f1f5f9; position:relative; display:flex; flex-direction:column;">
            <div id="fin-inv-loading" style="padding:36px 20px; text-align:center; font-size:13px; color:#64748b; font-weight:600;">
                Memuat dokumen receipt resmi...
            </div>
            <iframe id="fin-inv-frame" title="Pratinjau Invoice Receipt" style="width:100%; flex:1 1 auto; border:0; background:#f1f5f9; display:none;"></iframe>
        </div>
        <div class="fin-modal-foot" style="flex-shrink:0;">
            <button type="button" class="fin-btn fin-btn-ghost" data-inv-close>Tutup</button>
            <a id="fin-inv-open-link" href="#" target="_blank" rel="noopener" class="fin-btn fin-btn-ghost">Buka Tab Baru &nearr;</a>
            <button type="button" id="fin-inv-print-btn" class="fin-btn fin-btn-primary" style="background:#ef233c; color:#ffffff; border-color:#ef233c;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>Cetak Receipt</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.admin-page-heading { display: none; }
.fin-page, .fin-modal-backdrop {
    --fin-ink: #0f172a;
    --fin-muted: #64748b;
    --fin-line: #e2e8f0;
    --fin-line-soft: #f1f5f9;
    --fin-surface: #ffffff;
    --fin-bg-soft: #f8fafc;
    --fin-accent: #ef233c;
}
.fin-page {
    max-width: 1360px;
    margin: 0 auto;
    padding: 8px 4px 40px;
    color: var(--fin-ink);
}

.fin-header { margin-bottom: 18px; }
.fin-eyebrow { display: inline-flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--fin-muted); margin-bottom: 6px; }
.fin-eyebrow-dot { width: 7px; height: 7px; border-radius: 999px; background: var(--fin-accent); }
.fin-title { margin: 0 0 4px; font-size: clamp(22px, 2.4vw, 28px); font-weight: 800; letter-spacing: -0.02em; }
.fin-subtitle { margin: 0; font-size: 13.5px; color: var(--fin-muted); }

.fin-alert { padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-bottom: 16px; border: 1px solid transparent; }
.fin-alert.is-success { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
.fin-alert.is-error { background: #fef2f2; border-color: #fecaca; color: #991b1b; }

.fin-filter-bar {
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
    padding: 12px 14px; margin-bottom: 18px;
    background: var(--fin-surface); border: 1px solid var(--fin-line); border-radius: 14px;
}
.fin-search-box { position: relative; flex: 1 1 260px; display: flex; align-items: center; }
.fin-search-box svg { position: absolute; left: 12px; color: #94a3b8; pointer-events: none; }
.fin-search-box input { width: 100%; height: 38px; padding: 0 12px 0 36px; border: 1px solid var(--fin-line); border-radius: 10px; background: var(--fin-bg-soft); font: inherit; font-size: 13px; outline: none; }
.fin-select, .fin-date { height: 38px; padding: 0 10px; border: 1px solid var(--fin-line); border-radius: 10px; background: var(--fin-bg-soft); font: inherit; font-size: 12.5px; color: var(--fin-ink); }

.fin-btn { display: inline-flex; align-items: center; justify-content: center; height: 38px; padding: 0 14px; border-radius: 10px; font: inherit; font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; border: 1px solid transparent; white-space: nowrap; }
.fin-btn-primary { background: var(--fin-ink); color: #fff; }
.fin-btn-primary:hover { background: var(--fin-accent); }
.fin-btn-ghost { background: var(--fin-surface); border-color: var(--fin-line); color: var(--fin-ink); }
.fin-btn-crimson { background: #dc2626; color: #fff; }

.fin-wf-switcher {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}
.fin-wf-btn {
    position: relative;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px 16px;
    border-radius: 14px;
    border: 1px solid var(--fin-line);
    background: var(--fin-surface);
    color: var(--fin-ink);
    text-align: left;
    cursor: pointer;
    transition: all 0.16s ease;
}
.fin-wf-btn:hover {
    border-color: #cbd5e1;
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
}
.fin-wf-btn.is-active {
    background: var(--fin-ink);
    border-color: var(--fin-ink);
    color: #ffffff;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.14);
}
.fin-wf-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.fin-wf-icon.is-car { background: #fef2f2; color: #ef233c; }
.fin-wf-icon.is-service { background: #eff6ff; color: #2563eb; }
.fin-wf-icon.is-wash { background: #ecfeff; color: #0891b2; }
.fin-wf-btn.is-active .fin-wf-icon {
    background: rgba(255, 255, 255, 0.14);
    color: #ffffff;
}
.fin-wf-text { min-width: 0; }
.fin-wf-text strong { display: block; font-size: 13.5px; font-weight: 800; line-height: 1.25; }
.fin-wf-text small { display: block; font-size: 11.5px; color: var(--fin-muted); margin-top: 2px; }
.fin-wf-btn.is-active .fin-wf-text small { color: #cbd5e1; }
.fin-wf-dot {
    position: absolute;
    top: -4px;
    right: -4px;
    width: 12px;
    height: 12px;
    border-radius: 999px;
    background: #ef233c;
    border: 2px solid #ffffff;
    box-shadow: 0 2px 6px rgba(239, 35, 60, 0.45);
}
.fin-single-stage { display: block; }
.fin-single-stage .fin-box[hidden] { display: none !important; }
.fin-single-stage .fin-box { animation: finPanelIn 0.2s ease; }
@keyframes finPanelIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}
.fin-box-icon.is-wash { background: #ecfeff; color: #0891b2; }
@media (max-width: 760px) {
    .fin-wf-switcher { grid-template-columns: 1fr; }
}

.fin-box {
    background: var(--fin-surface);
    border: 1px solid var(--fin-line);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
}

.fin-box-head {
    display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
    padding: 16px 18px; border-bottom: 1px solid var(--fin-line); background: var(--fin-bg-soft);
}
.fin-box-title-wrap { display: flex; align-items: center; gap: 12px; }
.fin-box-icon { width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center; flex-shrink: 0; }
.fin-box-icon.is-car { background: #fef2f2; color: #ef233c; border: 1px solid #fecaca; }
.fin-box-icon.is-service { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
.fin-box-kicker { display: block; font-size: 10.5px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--fin-muted); }
.fin-box-head h2 { margin: 2px 0 0; font-size: 17px; font-weight: 800; }

.fin-badge { padding: 4px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700; }
.fin-badge.is-amber { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.fin-badge.is-crimson { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.fin-badge.is-slate { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

.fin-mini-kpis {
    display: grid;
    grid-template-columns: 1.45fr 0.78fr 0.78fr;
    gap: 8px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--fin-line-soft);
}
.fin-mini-kpi {
    padding: 9px 10px;
    border-radius: 10px;
    background: var(--fin-bg-soft);
    border: 1px solid var(--fin-line-soft);
    min-width: 0;
}
.fin-mini-kpi span {
    display: block;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: var(--fin-muted);
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.fin-mini-kpi strong {
    display: block;
    font-size: 13px;
    font-weight: 800;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-variant-numeric: tabular-nums;
}
.fin-mini-kpi strong.is-emerald { color: #059669; }
.fin-mini-kpi strong.is-amber { color: #d97706; }
.fin-mini-kpi strong.is-crimson { color: #dc2626; }

.fin-subtabs {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
    padding: 10px 16px;
    border-bottom: 1px solid var(--fin-line-soft);
    background: #ffffff;
}
.fin-subtab {
    padding: 7px 8px;
    border-radius: 8px;
    font-size: 11.5px;
    font-weight: 700;
    text-align: center;
    white-space: nowrap;
    color: var(--fin-muted);
    text-decoration: none;
    background: var(--fin-bg-soft);
    border: 1px solid transparent;
    transition: all 0.15s ease;
}
.fin-subtab:hover { color: var(--fin-ink); border-color: var(--fin-line); }
.fin-subtab.is-active { background: var(--fin-ink); color: #ffffff; }

.fin-box-body { padding: 14px 16px; display: grid; gap: 10px; }

.fin-item {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
    padding: 13px 14px;
    border-radius: 13px;
    border: 1px solid var(--fin-line);
    background: var(--fin-surface);
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.fin-item:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
}
.fin-item-main { min-width: 0; }
.fin-item-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 5px;
}
.fin-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 11.5px; font-weight: 700; color: var(--fin-ink); }
.fin-item-main strong { display: block; font-size: 13px; font-weight: 700; color: var(--fin-ink); line-height: 1.35; }
.fin-meta { display: block; margin-top: 2px; font-size: 11.5px; color: var(--fin-muted); }

.fin-pill { padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; white-space: nowrap; }
.fin-pill.is-emerald { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.fin-pill.is-amber { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.fin-pill.is-crimson { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

.fin-item-side {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding-top: 10px;
    border-top: 1px solid var(--fin-line-soft);
}
.fin-amount { font-size: 14px; font-weight: 800; color: var(--fin-ink); white-space: nowrap; font-variant-numeric: tabular-nums; }
.fin-item-actions { display: flex; align-items: center; gap: 5px; flex-wrap: nowrap; justify-content: flex-end; }

.fin-btn-sm {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 5px 10px; border-radius: 7px; font: inherit; font-size: 11.5px; font-weight: 600;
    white-space: nowrap; text-decoration: none; cursor: pointer; border: 1px solid transparent;
}
.fin-btn-sm.fin-btn-primary { background: var(--fin-ink); color: #fff; }
.fin-btn-sm.fin-btn-ghost { background: var(--fin-bg-soft); border-color: var(--fin-line); color: var(--fin-ink); }
.fin-btn-sm.fin-btn-emerald { background: #059669; color: #fff; }
.fin-btn-sm.fin-btn-crimson { background: #fef2f2; border-color: #fecaca; color: #dc2626; }

.fin-empty { padding: 34px 16px; text-align: center; font-size: 13px; color: var(--fin-muted); }
.fin-pagination { padding-top: 8px; }

.fin-modal-backdrop[hidden] { display: none !important; }
.fin-modal-backdrop { position: fixed; inset: 0; z-index: 999; background: rgba(15,23,42,0.58); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 16px; }
.fin-modal-dialog { width: 100%; max-width: 480px; background: #fff; border: 1px solid var(--fin-line); border-radius: 20px; overflow: hidden; box-shadow: 0 24px 60px rgba(15,23,42,0.22); }
.fin-modal-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 18px 22px; border-bottom: 1px solid var(--fin-line-soft); background: #ffffff; }
.fin-modal-head h3 { margin: 3px 0 0; font-size: 16.5px; font-weight: 800; color: var(--fin-ink); letter-spacing: -0.01em; }
.fin-modal-close { width: 34px; height: 34px; border-radius: 10px; border: 1px solid var(--fin-line); background: var(--fin-bg-soft); color: var(--fin-ink); font-size: 18px; line-height: 1; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: background 0.15s ease; }
.fin-modal-close:hover { background: #e2e8f0; }
.fin-modal-body { padding: 18px 22px; display: grid; gap: 10px; }
.fin-modal-body label.fin-field { display: flex; flex-direction: column; gap: 6px; font-size: 12px; font-weight: 700; color: #334155; }
.fin-modal-body textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--fin-line); border-radius: 10px; font: inherit; font-size: 13px; color: var(--fin-ink); background: #fff; resize: vertical; }
.fin-modal-foot { display: flex; justify-content: flex-end; gap: 8px; padding: 14px 22px; border-top: 1px solid var(--fin-line-soft); background: var(--fin-bg-soft); }

.fin-form-grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.fin-file-input { width: 100%; padding: 8px 10px; border: 1px dashed #cbd5e1; border-radius: 10px; background: var(--fin-bg-soft); font: inherit; font-size: 12.5px; color: var(--fin-muted); cursor: pointer; }
.fin-file-input::file-selector-button { margin-right: 10px; padding: 5px 10px; border-radius: 7px; border: 1px solid var(--fin-line); background: #ffffff; color: var(--fin-ink); font-weight: 700; font-size: 11.5px; cursor: pointer; }

.fin-sd-banner { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 12px; background: var(--fin-bg-soft); border: 1px solid var(--fin-line); }
.fin-sd-block { padding: 12px 14px; border-radius: 12px; background: var(--fin-bg-soft); border: 1px solid var(--fin-line-soft); }
.fin-sd-label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--fin-muted); margin-bottom: 8px; }
.fin-sd-list { display: grid; gap: 6px; }
.fin-sd-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 12px; background: var(--fin-bg-soft); border: 1px solid var(--fin-line-soft); font-size: 13px; }
.fin-sd-row strong { color: var(--fin-ink); font-weight: 700; }
.fin-sd-row small { display: block; font-size: 11px; color: var(--fin-muted); }
.fin-sd-totals { padding: 14px 16px; border-radius: 12px; background: #fff; border: 1px solid var(--fin-line); display: grid; gap: 8px; }
.fin-sd-total-row { display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: var(--fin-muted); }
.fin-sd-total-row strong { color: var(--fin-ink); font-variant-numeric: tabular-nums; }
.fin-sd-total-row.is-grand { padding-top: 9px; margin-top: 2px; border-top: 1px solid var(--fin-line); font-size: 14.5px; font-weight: 800; color: var(--fin-ink); }
.fin-sd-total-row.is-grand strong { font-size: 15.5px; color: #ef233c; }

@media (max-width: 1024px) {
    .fin-split-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .fin-mini-kpis { grid-template-columns: 1fr; }
    .fin-form-grid-2 { grid-template-columns: 1fr; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const wfBtns = Array.from(document.querySelectorAll('[data-wf-target]'));
    const wfPanels = Array.from(document.querySelectorAll('[data-wf-panel]'));
    const wfInput = document.getElementById('fin-wf-input');
    const subtabs = Array.from(document.querySelectorAll('.fin-subtab'));

    const activateWorkflow = (wf) => {
        wfBtns.forEach((btn) => {
            const isMatch = btn.dataset.wfTarget === wf;
            btn.classList.toggle('is-active', isMatch);
            btn.setAttribute('aria-selected', isMatch ? 'true' : 'false');
        });
        wfPanels.forEach((panel) => {
            panel.hidden = panel.dataset.wfPanel !== wf;
        });
        if (wfInput) wfInput.value = wf;
        subtabs.forEach((link) => {
            try {
                const url = new URL(link.href, window.location.origin);
                url.searchParams.set('wf', wf);
                link.href = url.toString();
            } catch (e) {}
        });
        try {
            const cur = new URL(window.location.href);
            cur.searchParams.set('wf', wf);
            window.history.replaceState({}, '', cur.toString());
        } catch (e) {}
    };

    wfBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.wfTarget;
            if (target) activateWorkflow(target);
        });
    });

    const modal = document.getElementById('fin-reject-modal');
    const form = document.getElementById('fin-reject-form');
    const codeEl = document.getElementById('fin-reject-code');
    const reasonEl = document.getElementById('fin-reject-reason');
    if (!modal || !form) return;

    const close = () => {
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    document.querySelectorAll('[data-reject-url]').forEach(btn => {
        btn.addEventListener('click', () => {
            form.action = btn.dataset.rejectUrl;
            codeEl.textContent = btn.dataset.rejectCode || 'PEMBAYARAN';
            if (reasonEl) reasonEl.value = '';
            modal.hidden = false;
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            reasonEl?.focus();
        });
    });

    modal.querySelectorAll('[data-reject-close]').forEach(btn => btn.addEventListener('click', close));
    modal.addEventListener('click', e => { if (e.target === modal) close(); });

    const sdModal = document.getElementById('fin-service-detail-modal');
    if (sdModal) {
        const fmt = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(Number(n || 0)));
        const closeSd = () => {
            sdModal.hidden = true;
            sdModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        };
        document.querySelectorAll('[data-srv-detail]').forEach(btn => {
            btn.addEventListener('click', () => {
                const d = JSON.parse(btn.dataset.srvDetail || '{}');
                document.getElementById('fin-sd-kode').textContent = `${d.kode_wo || 'WORK ORDER'} · ${d.kode_trx || ''}`;
                document.getElementById('fin-sd-vehicle').textContent = d.vehicle || 'Kendaraan Servis';
                document.getElementById('fin-sd-sub').textContent = `${d.customer || 'Member'} · ${d.plate || 'Plat —'}`;

                const jasaWrap = document.getElementById('fin-sd-jasa');
                jasaWrap.innerHTML = '';
                if (Array.isArray(d.jasa) && d.jasa.length) {
                    d.jasa.forEach(item => {
                        const row = document.createElement('div');
                        row.className = 'fin-sd-row';
                        row.innerHTML = `<div><strong>${item.name || '-'}</strong><small>${item.note || ''}</small></div><strong>${fmt(item.amount)}</strong>`;
                        jasaWrap.appendChild(row);
                    });
                } else {
                    jasaWrap.innerHTML = '<div style="font-size:12px; color:#64748b;">Tidak ada rincian jasa.</div>';
                }

                const partsWrap = document.getElementById('fin-sd-parts');
                partsWrap.innerHTML = '';
                if (Array.isArray(d.parts) && d.parts.length) {
                    d.parts.forEach(item => {
                        const row = document.createElement('div');
                        row.className = 'fin-sd-row';
                        row.innerHTML = `<div><strong>${item.name || '-'}</strong><small>${item.qty || 1} pcs × ${fmt(item.unit_price)}</small></div><strong>${fmt(item.subtotal)}</strong>`;
                        partsWrap.appendChild(row);
                    });
                } else {
                    partsWrap.innerHTML = '<div style="font-size:12px; color:#64748b;">Tidak ada sparepart yang digunakan.</div>';
                }

                document.getElementById('fin-sd-jasa-total').textContent = fmt(d.jasa_total);
                document.getElementById('fin-sd-part-total').textContent = fmt(d.part_total);
                const discRow = document.getElementById('fin-sd-disc-row');
                if (Number(d.discount || 0) > 0) {
                    discRow.hidden = false;
                    document.getElementById('fin-sd-disc-total').textContent = '- ' + fmt(d.discount);
                } else {
                    discRow.hidden = true;
                }
                document.getElementById('fin-sd-grand-total').textContent = fmt(d.grand_total);

                sdModal.hidden = false;
                sdModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            });
        });
        sdModal.querySelectorAll('[data-sd-close]').forEach(btn => btn.addEventListener('click', closeSd));
        sdModal.addEventListener('click', e => { if (e.target === sdModal) closeSd(); });
    }

    // Universal Detail, Catat Bayar, & Invoice Modals
    const fmtIdr = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(Number(n || 0)));
    const setupSimpleModal = (modalId, closeAttr) => {
        const el = document.getElementById(modalId);
        if (!el) return { open: () => {}, close: () => {}, el: null };
        const closeFn = () => { el.hidden = true; el.setAttribute('aria-hidden', 'true'); document.body.style.overflow = ''; };
        const openFn = () => { el.hidden = false; el.setAttribute('aria-hidden', 'false'); document.body.style.overflow = 'hidden'; };
        el.querySelectorAll(`[${closeAttr}]`).forEach(b => b.addEventListener('click', closeFn));
        el.addEventListener('click', e => { if (e.target === el) closeFn(); });
        return { open: openFn, close: closeFn, el };
    };

    const uDetail = setupSimpleModal('fin-universal-detail-modal', 'data-ud-close');
    document.querySelectorAll('[data-fin-detail]').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = JSON.parse(btn.dataset.finDetail || '{}');
            document.getElementById('fin-ud-kode').textContent = `${d.kode || ''} · ${d.ref || ''}`;
            document.getElementById('fin-ud-title').textContent = d.vehicle || 'Detail Transaksi';
            document.getElementById('fin-ud-customer').textContent = d.customer || 'Member';
            document.getElementById('fin-ud-item').textContent = d.item || '—';
            document.getElementById('fin-ud-date').textContent = d.date || '—';
            document.getElementById('fin-ud-status').textContent = d.status || '—';
            document.getElementById('fin-ud-subtotal').textContent = fmtIdr(d.subtotal);
            document.getElementById('fin-ud-discount').textContent = Number(d.discount || 0) > 0 ? ('- ' + fmtIdr(d.discount)) : 'Rp 0';
            document.getElementById('fin-ud-total').textContent = fmtIdr(d.total);
            uDetail.open();
        });
    });

    const payModal = setupSimpleModal('fin-pay-modal', 'data-pay-close');
    document.querySelectorAll('[data-fin-pay]').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = JSON.parse(btn.dataset.finPay || '{}');
            document.getElementById('fin-pay-form').action = d.pay_url || '#';
            document.getElementById('fin-pay-kode').textContent = `${d.kode || ''} · ${d.ref || ''}`;
            document.getElementById('fin-pay-summary').textContent = `${d.customer || 'Member'} · ${d.vehicle || ''}`;
            document.getElementById('fin-pay-amount').value = Math.round(Number(d.total || 0));
            document.getElementById('fin-pay-total-label').textContent = fmtIdr(d.total);
            payModal.open();
        });
    });

    const invModal = setupSimpleModal('fin-invoice-modal', 'data-inv-close');
    const invFrame = document.getElementById('fin-inv-frame');
    const invLoading = document.getElementById('fin-inv-loading');
    const invPrintBtn = document.getElementById('fin-inv-print-btn');

    if (invFrame) {
        invFrame.addEventListener('load', () => {
            try {
                const doc = invFrame.contentDocument || invFrame.contentWindow?.document;
                if (doc) {
                    const style = doc.createElement('style');
                    style.textContent = `
                        .admin-sidebar, .admin-topbar, .admin-footer, .admin-page-heading, .invoice-toolbar, .yza-alert { display: none !important; }
                        .admin-shell, .admin-main, .admin-content { margin: 0 !important; padding: 0 !important; max-width: 100% !important; background: #f1f5f9 !important; }
                        .invoice-container { padding: 16px !important; max-width: 100% !important; }
                    `;
                    doc.head.appendChild(style);
                }
            } catch (e) {}
            if (invLoading) invLoading.style.display = 'none';
            invFrame.style.display = 'block';
        });
    }

    if (invPrintBtn && invFrame) {
        invPrintBtn.addEventListener('click', () => {
            try {
                invFrame.contentWindow?.focus();
                invFrame.contentWindow?.print();
            } catch (e) {
                const openLink = document.getElementById('fin-inv-open-link');
                if (openLink?.href) window.open(openLink.href, '_blank');
            }
        });
    }

    document.querySelectorAll('[data-fin-invoice]').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = JSON.parse(btn.dataset.finInvoice || '{}');
            document.getElementById('fin-inv-kode').textContent = `${d.kode || 'INVOICE'} · ${d.ref || ''}`;
            const openLink = document.getElementById('fin-inv-open-link');
            if (openLink) {
                if (d.invoice_url) { openLink.href = d.invoice_url; openLink.hidden = false; }
                else { openLink.hidden = true; }
            }
            if (invFrame && d.invoice_url) {
                invFrame.style.display = 'none';
                if (invLoading) invLoading.style.display = 'block';
                invFrame.src = d.invoice_url;
            }
            invModal.open();
        });
    });
});
</script>
@endpush
