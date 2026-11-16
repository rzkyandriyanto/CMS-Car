@extends('layouts.app')

@section('title', 'Manajemen Pembayaran · YZA Automotive')

@section('content')
@php
    $indexRoute = route('admin.payments.index');
    $hasFilter = ($q ?? '') !== '' || ($status ?? '') !== '' || ($from ?? '') !== '' || ($to ?? '') !== '';
    $baseQuery = array_filter([
        'q' => $q ?: null,
        'status' => $status ?: null,
        'from' => $from ?: null,
        'to' => $to ?: null,
        'car_tab' => $carTab,
        'service_tab' => $serviceTab,
    ]);
@endphp

<div class="fin-page">
    <header class="fin-header">
        <div>
            <div class="fin-eyebrow">
                <span class="fin-eyebrow-dot" aria-hidden="true"></span>
                <span>Finance &amp; Kasir · Manajemen Pembayaran</span>
            </div>
            <h1 class="fin-title">Pembayaran Mobil &amp; Servis</h1>
            <p class="fin-subtitle">Kelola dan verifikasi pembayaran pembelian unit mobil serta tagihan servis bengkel dalam dua jalur terpisah.</p>
        </div>
    </header>

    @if(session('status'))
        <div class="fin-alert is-success" role="alert">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="fin-alert is-error" role="alert">{{ $errors->first() }}</div>
    @endif

    {{-- Global Search & Filter Bar --}}
    <form method="GET" action="{{ $indexRoute }}" class="fin-filter-bar">
        <input type="hidden" name="car_tab" value="{{ $carTab }}">
        <input type="hidden" name="service_tab" value="{{ $serviceTab }}">
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

    {{-- 2-BOX SPLIT: PEMBAYARAN MOBIL & PEMBAYARAN SERVIS --}}
    <div class="fin-split-grid">

        {{-- =====================================================
             BOX 1: PEMBAYARAN MOBIL
             ===================================================== --}}
        <section class="fin-box" aria-label="Pembayaran Mobil">
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
                    <span>Dana Terverifikasi</span>
                    <strong class="is-emerald">Rp {{ number_format($carStats['verified_total'] ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div class="fin-mini-kpi">
                    <span>Menunggu Verifikasi</span>
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
                                    <a href="{{ route('admin.car-sale.payment.create', $trx) }}" class="fin-btn-sm fin-btn-primary">Catat Bayar</a>
                                    <a href="{{ route('admin.invoice.car-sale', $trx) }}" class="fin-btn-sm fin-btn-ghost">Invoice</a>
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
                        <div class="fin-empty">Belum ada pembayaran offline untuk pembelian mobil.</div>
                    @endforelse
                    @if($carOffline->hasPages())
                        <div class="fin-pagination">{{ $carOffline->links() }}</div>
                    @endif
                @endif
            </div>
        </section>

        {{-- =====================================================
             BOX 2: PEMBAYARAN SERVIS
             ===================================================== --}}
        <section class="fin-box" aria-label="Pembayaran Servis">
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
                    <span>Dana Terverifikasi</span>
                    <strong class="is-emerald">Rp {{ number_format($serviceStats['verified_total'] ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div class="fin-mini-kpi">
                    <span>Menunggu Verifikasi</span>
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
                                    <button type="button" class="fin-btn-sm fin-btn-ghost" data-srv-detail='@json($buildServicePayload($srv, $trx))'>Detail</button>
                                    <a href="{{ route('admin.payment.create', $trx) }}" class="fin-btn-sm fin-btn-primary">Catat Bayar</a>
                                    <a href="{{ route('admin.invoice.servis', $trx) }}" class="fin-btn-sm fin-btn-ghost">Invoice</a>
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
@endsection

@push('styles')
<style>
.fin-page {
    --fin-ink: #0f172a;
    --fin-muted: #64748b;
    --fin-line: #e2e8f0;
    --fin-line-soft: #f1f5f9;
    --fin-surface: #ffffff;
    --fin-bg-soft: #f8fafc;
    --fin-accent: #ef233c;
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

.fin-split-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    align-items: start;
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
    display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px;
    padding: 12px 18px; border-bottom: 1px solid var(--fin-line-soft);
}
.fin-mini-kpi { padding: 10px 12px; border-radius: 11px; background: var(--fin-bg-soft); border: 1px solid var(--fin-line-soft); }
.fin-mini-kpi span { display: block; font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: var(--fin-muted); margin-bottom: 3px; }
.fin-mini-kpi strong { font-size: 14px; font-weight: 800; }
.fin-mini-kpi strong.is-emerald { color: #059669; }
.fin-mini-kpi strong.is-amber { color: #d97706; }
.fin-mini-kpi strong.is-crimson { color: #dc2626; }

.fin-subtabs {
    display: flex; gap: 4px; padding: 8px 18px; border-bottom: 1px solid var(--fin-line);
    background: #ffffff; flex-wrap: wrap;
}
.fin-subtab {
    padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;
    color: var(--fin-muted); text-decoration: none; background: var(--fin-bg-soft); border: 1px solid transparent;
}
.fin-subtab.is-active { background: var(--fin-ink); color: #ffffff; }

.fin-box-body { padding: 14px 18px; display: grid; gap: 10px; }

.fin-item {
    display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
    padding: 12px 14px; border-radius: 12px; border: 1px solid var(--fin-line); background: var(--fin-surface);
}
.fin-item-main { flex: 1 1 220px; min-width: 0; }
.fin-item-top { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 4px; }
.fin-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 12px; font-weight: 700; color: var(--fin-ink); }
.fin-item-main strong { display: block; font-size: 13px; color: var(--fin-ink); }
.fin-meta { display: block; margin-top: 2px; font-size: 11.5px; color: var(--fin-muted); }

.fin-pill { padding: 2px 8px; border-radius: 999px; font-size: 10.5px; font-weight: 700; }
.fin-pill.is-emerald { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.fin-pill.is-amber { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.fin-pill.is-crimson { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

.fin-item-side { display: flex; flex-direction: column; align-items: flex-end; gap: 7px; }
.fin-amount { font-size: 13.5px; font-weight: 800; color: var(--fin-ink); white-space: nowrap; }
.fin-item-actions { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; justify-content: flex-end; }

.fin-btn-sm {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 5px 10px; border-radius: 7px; font: inherit; font-size: 11.5px; font-weight: 600;
    text-decoration: none; cursor: pointer; border: 1px solid transparent;
}
.fin-btn-sm.fin-btn-primary { background: var(--fin-ink); color: #fff; }
.fin-btn-sm.fin-btn-ghost { background: var(--fin-bg-soft); border-color: var(--fin-line); color: var(--fin-ink); }
.fin-btn-sm.fin-btn-emerald { background: #059669; color: #fff; }
.fin-btn-sm.fin-btn-crimson { background: #fef2f2; border-color: #fecaca; color: #dc2626; }

.fin-empty { padding: 34px 16px; text-align: center; font-size: 13px; color: var(--fin-muted); }
.fin-pagination { padding-top: 8px; }

.fin-modal-backdrop[hidden] { display: none !important; }
.fin-modal-backdrop { position: fixed; inset: 0; z-index: 999; background: rgba(15,23,42,0.58); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 16px; }
.fin-modal-dialog { width: 100%; max-width: 460px; background: #fff; border: 1px solid var(--fin-line); border-radius: 16px; overflow: hidden; box-shadow: 0 24px 60px rgba(15,23,42,0.25); }
.fin-modal-head { display: flex; justify-content: space-between; align-items: flex-start; padding: 16px 20px; border-bottom: 1px solid var(--fin-line); background: var(--fin-bg-soft); }
.fin-modal-head h3 { margin: 2px 0 0; font-size: 16px; font-weight: 800; }
.fin-modal-close { width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--fin-line); background: #fff; font-size: 18px; cursor: pointer; }
.fin-modal-body { padding: 16px 20px; display: grid; gap: 8px; }
.fin-modal-body label { font-size: 12.5px; font-weight: 700; }
.fin-modal-body textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--fin-line); border-radius: 10px; font: inherit; font-size: 13px; resize: vertical; }
.fin-modal-foot { display: flex; justify-content: flex-end; gap: 8px; padding: 12px 20px; border-top: 1px solid var(--fin-line); background: var(--fin-bg-soft); }

.fin-sd-block { padding: 12px 14px; border-radius: 12px; background: var(--fin-bg-soft); border: 1px solid var(--fin-line-soft); }
.fin-sd-label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--fin-muted); margin-bottom: 8px; }
.fin-sd-list { display: grid; gap: 6px; }
.fin-sd-row { display: flex; justify-content: space-between; align-items: baseline; gap: 10px; padding: 7px 10px; border-radius: 8px; background: #fff; border: 1px solid var(--fin-line); font-size: 12.5px; }
.fin-sd-row small { display: block; font-size: 11px; color: var(--fin-muted); }
.fin-sd-totals { padding: 12px 14px; border-radius: 12px; background: #fff; border: 1px solid var(--fin-line); display: grid; gap: 6px; }
.fin-sd-total-row { display: flex; justify-content: space-between; font-size: 12.5px; color: var(--fin-muted); }
.fin-sd-total-row.is-grand { padding-top: 6px; border-top: 1px solid var(--fin-line); font-size: 14.5px; font-weight: 800; color: var(--fin-ink); }

@media (max-width: 1024px) {
    .fin-split-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .fin-mini-kpis { grid-template-columns: 1fr; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
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
});
</script>
@endpush
