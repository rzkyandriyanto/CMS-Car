@extends('layouts.app')
@section('title', 'Booking Cuci Mobil · YZA Automotive')
@section('content')
@php
    $isPetugasRoute = request()->routeIs('petugas.*');
    $indexRoute = $isPetugasRoute ? 'petugas.booking-cuci.index' : 'admin.booking-cuci.index';
    $detailRoute = $isPetugasRoute ? 'petugas.booking-cuci.show' : 'admin.booking-cuci.show';
    $actionRoute = $isPetugasRoute ? 'petugas.booking-cuci.action' : 'admin.booking-cuci.action';

    $statusLabels = [
        'menunggu' => 'Menunggu',
        'diterima' => 'Dikonfirmasi',
        'proses' => 'Sedang Dicuci',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];
    $statusTone = [
        'menunggu' => 'amber',
        'diterima' => 'blue',
        'proses' => 'indigo',
        'selesai' => 'emerald',
        'dibatalkan' => 'rose',
    ];
    $money = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $galleryService = app(\App\Services\VehicleGalleryService::class);
    $vehicleImage = function ($vehicle) use ($galleryService) {
        if (!$vehicle) {
            return asset('assets/images/hero/yza-hero-reference-v3.png');
        }
        $mobil = $vehicle->penjualan?->mobil;
        $purchasedColor = $vehicle->pesanan?->snapshot_warna ?: $vehicle->warna;

        if (!$mobil && ($vehicle->merk || $vehicle->model)) {
            $mobil = new \App\Models\Mobil([
                'merk' => $vehicle->merk,
                'model' => $vehicle->model,
                'warna' => $purchasedColor,
            ]);
        }

        if ($mobil) {
            $selected = $purchasedColor ? $galleryService->forColor($mobil, $purchasedColor) : [];
            if (empty($selected['images'])) {
                $gallery = $galleryService->for($mobil);
                $defaultKey = $galleryService->defaultColor($mobil);
                $selected = $gallery[$defaultKey] ?? (reset($gallery) ?: []);
            }
            if (!empty($selected['images'][0]['src'])) {
                return $selected['images'][0]['src'];
            }
        }

        foreach (array_filter([
            \Illuminate\Support\Str::slug(trim(($vehicle->merk ?? '') . ' ' . ($vehicle->model ?? ''))),
            \Illuminate\Support\Str::slug((string) ($vehicle->model ?? '')),
        ]) as $slug) {
            $matches = glob(public_path('assets/images/vehicles/' . $slug . '/*/front.png')) ?: [];
            if ($matches) {
                return asset(str_replace('\\', '/', substr($matches[0], strlen(public_path()) + 1)));
            }
            if (is_file(public_path('assets/images/catalog/' . $slug . '/front.png'))) {
                return asset('assets/images/catalog/' . $slug . '/front.png');
            }
        }
        return asset('assets/images/hero/yza-hero-reference-v3.png');
    };
    $promosList = ($promos ?? collect())->map(fn ($p) => [
        'id' => $p->id_promo,
        'kode' => $p->kode_promo,
        'nama' => $p->nama ?: $p->kode_promo,
        'tipe' => $p->jenis_benefit ?: 'nominal',
        'nilai' => (float) ($p->nilai_benefit ?? 0),
    ])->values();
@endphp

<div class="cw-page">
    {{-- Header Terpadu Minimalis --}}
    <header class="cw-header">
        <div class="cw-header-left">
            <div class="cw-header-icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
            </div>
            <div>
                <p class="cw-eyebrow">Layanan &amp; Operasional · Car Wash</p>
                <h1 class="cw-title">Booking Cuci Mobil</h1>
                <p class="cw-subtitle">Kelola antrean pencucian, konfirmasi jadwal, proses cuci, dan penerbitan tagihan ke Finance.</p>
            </div>
        </div>
        <div class="cw-header-actions">
            <button type="button" class="cw-btn cw-btn-ghost" onclick="openCwPackagesModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                <span>Paket Cuci ({{ ($allPackages ?? collect())->count() }})</span>
            </button>
        </div>
    </header>

    @if(session('success'))
        <div id="cwSuccessModal" class="cw-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="cwSuccessTitle">
            <div class="cw-modal-dialog is-compact" style="max-width:400px; text-align:center;">
                <div class="cw-modal-body" style="padding:28px 24px 22px; align-items:center;">
                    <div style="width:54px; height:54px; border-radius:16px; background:#ecfdf5; color:#059669; display:flex; align-items:center; justify-content:center; margin-bottom:4px;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <span class="cw-modal-kicker" style="color:#059669; margin:0;">BERHASIL</span>
                    <h3 id="cwSuccessTitle" style="margin:0; font-size:18px; font-weight:800; color:#0f172a;">Proses Berhasil</h3>
                    <p style="margin:0; font-size:13.5px; color:#475569; line-height:1.55;">{{ session('success') }}</p>
                    <button type="button" class="cw-btn cw-btn-primary" style="width:100%; margin-top:6px;" onclick="closeCwModal('cwSuccessModal')">Tutup</button>
                </div>
            </div>
        </div>
    @endif

    {{-- 4 KPI Statis Minimalis --}}
    <section class="cw-kpis" aria-label="Ringkasan booking cuci mobil">
        <div class="cw-kpi">
            <div class="cw-kpi-icon is-slate">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="cw-kpi-body">
                <span>Total Booking</span>
                <strong>{{ number_format($bookingStats['total'] ?? 0) }}</strong>
                <small>Seluruh pesanan car wash</small>
            </div>
        </div>

        <div class="cw-kpi">
            <div class="cw-kpi-icon is-amber">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="cw-kpi-body">
                <span>Menunggu Konfirmasi</span>
                <strong>{{ number_format($bookingStats['waiting'] ?? 0) }}</strong>
                <small>Perlu dikonfirmasi petugas</small>
            </div>
        </div>

        <div class="cw-kpi">
            <div class="cw-kpi-icon is-indigo">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
            </div>
            <div class="cw-kpi-body">
                <span>Sedang Dicuci</span>
                <strong>{{ number_format($bookingStats['processing'] ?? 0) }}</strong>
                <small>Unit sedang dalam proses</small>
            </div>
        </div>

        <div class="cw-kpi">
            <div class="cw-kpi-icon is-emerald">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="cw-kpi-body">
                <span>Selesai</span>
                <strong>{{ number_format($bookingStats['completed'] ?? $bookingStats['completedToday'] ?? 0) }}</strong>
                <small>Layanan cuci mobil selesai</small>
            </div>
        </div>
    </section>

    {{-- Panel Utama Tabel Booking --}}
    <section class="cw-surface">
        <div class="cw-toolbar">
            <form class="cw-filter-form" method="get" action="{{ route($indexRoute) }}">
                <div class="cw-search-wrap">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari kode booking, pelanggan, atau nomor polisi...">
                </div>

                <select name="status" class="cw-select" aria-label="Filter status">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $option)
                        <option value="{{ $option }}" @selected($status === $option)>{{ $statusLabels[$option] ?? ucfirst($option) }}</option>
                    @endforeach
                </select>

                <input type="date" name="date" value="{{ $date }}" class="cw-date-input" aria-label="Filter tanggal">
                <input type="hidden" name="perPage" value="{{ $perPage }}">

                <button type="submit" class="cw-btn cw-btn-primary">Terapkan</button>
                @if($search !== '' || $status !== '' || $date !== '')
                    <a href="{{ route($indexRoute) }}" class="cw-btn cw-btn-ghost">Reset</a>
                @endif
            </form>

            <div class="cw-total-pill">{{ number_format($rows->total()) }} booking</div>
        </div>

        <div class="cw-table-wrap">
            <table class="cw-table">
                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>Pelanggan</th>
                        <th>Kendaraan</th>
                        <th>Paket</th>
                        <th>Jadwal</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $booking)
                        @php
                            $thumbUrl = $vehicleImage($booking->kendaraan);
                            $appliedPromo = $booking->promo;
                            $txPromoCode = $booking->transaksi?->promo_code_snapshot;
                            $txPromoName = $booking->transaksi?->promo_name_snapshot;
                            $txPromoType = $booking->transaksi?->promo_type_snapshot;
                            $txPromoVal = $booking->transaksi?->promo_value_snapshot;
                            $promoCode = $appliedPromo?->kode_promo ?: $txPromoCode;
                            $promoName = $appliedPromo?->nama ?: $txPromoName;
                            $promoType = $appliedPromo?->jenis_benefit ?: $txPromoType;
                            $promoVal = $appliedPromo ? (float) $appliedPromo->nilai_benefit : (float) ($txPromoVal ?? 0);
                            $promoBenefitText = $promoCode
                                ? ($promoType === 'persentase'
                                    ? 'Diskon ' . rtrim(rtrim(number_format($promoVal, 2, ',', '.'), '0'), ',') . '%'
                                    : 'Potongan ' . $money($promoVal))
                                : null;
                            $promoDiscountAmount = $promoCode
                                ? ($promoType === 'persentase'
                                    ? min((float) $booking->harga, ((float) $booking->harga) * ($promoVal / 100))
                                    : min((float) $booking->harga, $promoVal))
                                : 0.0;
                            $estimatedTotal = max(0.0, (float) $booking->harga - $promoDiscountAmount);
                            $bookingPayload = [
                                'id' => $booking->id_booking_cuci,
                                'kode' => $booking->kode_booking,
                                'status' => $booking->status,
                                'status_label' => $statusLabels[$booking->status] ?? ucfirst($booking->status),
                                'status_tone' => $statusTone[$booking->status] ?? 'slate',
                                'pelanggan' => $booking->pelanggan?->nama_lengkap ?: 'Pelanggan',
                                'pelanggan_id' => $booking->id_pelanggan,
                                'pelanggan_hp' => $booking->pelanggan?->no_hp ?: '—',
                                'kendaraan' => trim(($booking->kendaraan?->merk ?: '') . ' ' . ($booking->kendaraan?->model ?: 'Kendaraan')),
                                'plat' => $booking->kendaraan?->nomor_polisi ?: 'Tanpa plat',
                                'thumb' => $thumbUrl,
                                'paket' => $booking->paket?->nama_paket ?: 'Paket Cuci',
                                'tanggal' => optional($booking->tanggal)->format('d M Y') ?: '—',
                                'jam' => substr((string) $booking->jam, 0, 5) . ' WIB',
                                'harga' => (float) $booking->harga,
                                'harga_formatted' => $money($booking->harga),
                                'petugas' => $booking->petugas?->nama_lengkap ?: 'Belum ditugaskan',
                                'catatan' => $booking->catatan ?: '',
                                'foto_sebelum_cuci_url' => ($booking->foto_sebelum_cuci && is_file(public_path($booking->foto_sebelum_cuci))) ? asset($booking->foto_sebelum_cuci) : null,
                                'foto_sesudah_cuci_url' => ($booking->foto_sesudah_cuci && is_file(public_path($booking->foto_sesudah_cuci))) ? asset($booking->foto_sesudah_cuci) : null,
                                'has_promo' => (bool) $promoCode,
                                'promo_id' => $appliedPromo?->id_promo ?: ($booking->transaksi?->id_promo ?: null),
                                'promo_code' => $promoCode,
                                'promo_name' => $promoName,
                                'promo_benefit' => $promoBenefitText,
                                'estimated_total_formatted' => $money($estimatedTotal),
                                'has_transaksi' => (bool) $booking->id_transaksi,
                                'kode_transaksi' => $booking->transaksi?->kode_transaksi ?: null,
                                'status_transaksi' => $booking->transaksi?->status ?: null,
                                'detail_url' => route($detailRoute, $booking),
                                'confirm_url' => route($actionRoute, [$booking, 'confirm']),
                                'start_url' => route($actionRoute, [$booking, 'start']),
                                'finalize_url' => route($actionRoute, [$booking, 'finalize']),
                            ];
                        @endphp
                        <tr>
                            <td>
                                <script type="application/json" id="cw-data-{{ $booking->id_booking_cuci }}">@json($bookingPayload)</script>
                                <div class="cw-cell-primary">{{ $booking->kode_booking }}</div>
                                <div class="cw-cell-sub">#{{ $booking->id_booking_cuci }}</div>
                            </td>
                            <td>
                                <div class="cw-cell-primary">{{ $booking->pelanggan?->nama_lengkap ?: 'Pelanggan' }}</div>
                                <div class="cw-cell-sub">Member #{{ $booking->id_pelanggan }}</div>
                            </td>
                            <td>
                                <div class="cw-vehicle-cell">
                                    <img src="{{ $thumbUrl }}" alt="Kendaraan" loading="lazy">
                                    <div>
                                        <div class="cw-cell-primary">{{ trim(($booking->kendaraan?->merk ?: '') . ' ' . ($booking->kendaraan?->model ?: 'Kendaraan')) }}</div>
                                        <div class="cw-cell-sub">{{ $booking->kendaraan?->nomor_polisi ?: 'Tanpa plat' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="cw-package-pill">{{ $booking->paket?->nama_paket ?: 'Paket layanan' }}</span>
                            </td>
                            <td>
                                <div class="cw-cell-primary">{{ optional($booking->tanggal)->format('d M Y') }}</div>
                                <div class="cw-cell-sub">{{ substr((string) $booking->jam, 0, 5) }} WIB</div>
                            </td>
                            <td>
                                <div class="cw-price">{{ $money($booking->harga) }}</div>
                            </td>
                            <td>
                                <span class="cw-status is-{{ $statusTone[$booking->status] ?? 'slate' }}">
                                    <i></i>{{ $statusLabels[$booking->status] ?? ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="cw-row-actions">
                                    <button type="button" class="cw-btn cw-btn-sm cw-btn-ghost" onclick="openCwDetailModal({{ $booking->id_booking_cuci }})">
                                        Detail
                                    </button>
                                    @if($booking->status === 'menunggu')
                                        <button type="button" class="cw-btn cw-btn-sm cw-btn-primary" onclick="openCwActionModal({{ $booking->id_booking_cuci }}, 'confirm')">
                                            Konfirmasi
                                        </button>
                                    @elseif($booking->status === 'diterima')
                                        <button type="button" class="cw-btn cw-btn-sm cw-btn-dark" onclick="openCwActionModal({{ $booking->id_booking_cuci }}, 'start')">
                                            Mulai Cuci
                                        </button>
                                    @elseif($booking->status === 'proses' && !$booking->id_transaksi)
                                        <button type="button" class="cw-btn cw-btn-sm cw-btn-emerald" onclick="openCwActionModal({{ $booking->id_booking_cuci }}, 'finalize')">
                                            Finalisasi
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="cw-empty">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.6"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                                <strong>{{ ($search || $status || $date) ? 'Tidak ada booking yang sesuai filter' : 'Belum ada booking cuci mobil' }}</strong>
                                <span>Antrean booking cuci mobil pelanggan akan tampil otomatis di sini.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer & Pagination --}}
        <div class="cw-footer">
            <span class="cw-footer-meta">
                Menampilkan <strong>{{ $rows->firstItem() ?? 0 }}–{{ $rows->lastItem() ?? 0 }}</strong> dari <strong>{{ $rows->total() }}</strong> booking
            </span>
            <div class="cw-footer-right">
                <label class="cw-perpage">
                    <span>Tampilkan</span>
                    <select onchange="window.location=this.value">
                        <option value="{{ request()->fullUrlWithQuery(['perPage' => 10, 'page' => 1]) }}" @selected($perPage === 10)>10</option>
                        <option value="{{ request()->fullUrlWithQuery(['perPage' => 20, 'page' => 1]) }}" @selected($perPage === 20)>20</option>
                        <option value="{{ request()->fullUrlWithQuery(['perPage' => 50, 'page' => 1]) }}" @selected($perPage === 50)>50</option>
                    </select>
                </label>
                <div class="cw-pagination">
                    {{ $rows->onEachSide(1)->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </section>
</div>

{{-- MODAL 1: DETAIL BOOKING CUCI --}}
<div id="cwDetailModal" class="cw-modal-backdrop" hidden role="dialog" aria-modal="true" aria-labelledby="cwDetailTitle">
    <div class="cw-modal-dialog">
        <div class="cw-modal-head">
            <div>
                <span class="cw-modal-kicker">CAR WASH · DETAIL BOOKING</span>
                <h3 id="cwDetailTitle">Detail Booking</h3>
            </div>
            <button type="button" class="cw-modal-close" onclick="closeCwModal('cwDetailModal')" aria-label="Tutup">&times;</button>
        </div>
        <div class="cw-modal-body" id="cwDetailBody"></div>
        <div class="cw-modal-foot" id="cwDetailFoot"></div>
    </div>
</div>

{{-- MODAL 2: KONFIRMASI AKSI / FINALISASI + PROMO --}}
<div id="cwActionModal" class="cw-modal-backdrop" hidden role="dialog" aria-modal="true" aria-labelledby="cwActionTitle">
    <div class="cw-modal-dialog is-compact">
        <div class="cw-modal-head">
            <div>
                <span class="cw-modal-kicker" id="cwActionKicker">OPERASIONAL CAR WASH</span>
                <h3 id="cwActionTitle">Konfirmasi Aksi</h3>
            </div>
            <button type="button" class="cw-modal-close" onclick="closeCwModal('cwActionModal')" aria-label="Tutup">&times;</button>
        </div>
        <form id="cwActionForm" method="post" action="" enctype="multipart/form-data">
            @csrf
            <div class="cw-modal-body" id="cwActionBody"></div>
            <div class="cw-modal-foot">
                <button type="button" class="cw-btn cw-btn-ghost" onclick="closeCwModal('cwActionModal')">Batal</button>
                <button type="submit" class="cw-btn cw-btn-primary" id="cwActionSubmitBtn">Lanjutkan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: DAFTAR PAKET CUCI --}}
<div id="cwPackagesModal" class="cw-modal-backdrop" hidden role="dialog" aria-modal="true" aria-labelledby="cwPackagesTitle">
    <div class="cw-modal-dialog">
        <div class="cw-modal-head">
            <div>
                <span class="cw-modal-kicker">KATALOG LAYANAN</span>
                <h3 id="cwPackagesTitle">Daftar Paket Cuci Mobil</h3>
            </div>
            <button type="button" class="cw-modal-close" onclick="closeCwModal('cwPackagesModal')" aria-label="Tutup">&times;</button>
        </div>
        <div class="cw-modal-body">
            @forelse($allPackages ?? [] as $pkg)
                <div class="cw-pkg-row">
                    <div>
                        <strong>{{ $pkg->nama_paket }}</strong>
                        <small>{{ $pkg->deskripsi ?: 'Layanan cuci mobil standar showroom YZA.' }}</small>
                    </div>
                    <div class="cw-pkg-meta">
                        <strong>{{ $money($pkg->harga) }}</strong>
                        <span>{{ (int) ($pkg->booking_cuci_count ?? 0) }} booking</span>
                    </div>
                </div>
            @empty
                <div class="cw-empty" style="padding:28px 12px;">
                    <strong>Belum ada paket cuci terdaftar</strong>
                </div>
            @endforelse
        </div>
        <div class="cw-modal-foot">
            <button type="button" class="cw-btn cw-btn-ghost" onclick="closeCwModal('cwPackagesModal')">Tutup</button>
        </div>
    </div>
</div>

<style>
.admin-page-heading {
    display: none;
}
.cw-page {
    max-width: 1320px;
    margin: 0 auto;
    padding: 4px 0 32px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.cw-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}
.cw-header-left {
    display: flex;
    align-items: center;
    gap: 14px;
}
.cw-header-icon {
    width: 46px;
    height: 46px;
    border-radius: 13px;
    background: #0f172a;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.14);
}
.cw-eyebrow {
    margin: 0 0 4px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #ef233c;
}
.cw-title {
    margin: 0;
    font-size: 24px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
}
.cw-subtitle {
    margin: 4px 0 0;
    font-size: 13px;
    color: #64748b;
}
.cw-header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.cw-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
}
.cw-alert.is-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
.cw-alert.is-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

/* KPI Minimalis */
.cw-kpis {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}
.cw-kpi {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 1px 4px rgba(15, 23, 42, 0.03);
}
.cw-kpi-icon {
    width: 42px;
    height: 42px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.cw-kpi-icon.is-slate { background: #f1f5f9; color: #334155; }
.cw-kpi-icon.is-amber { background: #fef3c7; color: #d97706; }
.cw-kpi-icon.is-indigo { background: #e0e7ff; color: #4338ca; }
.cw-kpi-icon.is-emerald { background: #dcfce7; color: #059669; }
.cw-kpi-body {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.cw-kpi-body span {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #64748b;
}
.cw-kpi-body strong {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
    font-variant-numeric: tabular-nums;
}
.cw-kpi-body small {
    font-size: 11.5px;
    color: #94a3b8;
    margin-top: 2px;
}

/* Main Surface */
.cw-surface {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
    overflow: hidden;
}
.cw-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    flex-wrap: wrap;
}
.cw-filter-form {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    flex-wrap: wrap;
}
.cw-search-wrap {
    position: relative;
    flex: 1 1 240px;
    max-width: 340px;
}
.cw-search-wrap svg {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
}
.cw-search-wrap input,
.cw-select,
.cw-date-input {
    height: 38px;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    background: #ffffff;
    color: #0f172a;
    font-size: 12.5px;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.cw-search-wrap input {
    width: 100%;
    padding: 0 12px 0 36px;
}
.cw-select,
.cw-date-input {
    padding: 0 12px;
}
.cw-search-wrap input:focus,
.cw-select:focus,
.cw-date-input:focus {
    border-color: #ef233c;
    box-shadow: 0 0 0 3px rgba(239, 35, 60, 0.1);
}
.cw-total-pill {
    padding: 6px 12px;
    border-radius: 999px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    font-size: 12px;
    font-weight: 700;
    color: #475569;
    white-space: nowrap;
}

/* Buttons */
.cw-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 38px;
    padding: 0 14px;
    border-radius: 9px;
    font-size: 12.5px;
    font-weight: 700;
    border: 1px solid transparent;
    text-decoration: none;
    white-space: nowrap;
    cursor: pointer;
    transition: all 0.15s ease;
}
.cw-btn-sm {
    height: 32px;
    padding: 0 11px;
    font-size: 12px;
    border-radius: 8px;
}
.cw-btn-primary {
    background: #ef233c;
    color: #ffffff;
}
.cw-btn-primary:hover { background: #d90429; color: #ffffff; }
.cw-btn-dark {
    background: #0f172a;
    color: #ffffff;
}
.cw-btn-dark:hover { background: #1e293b; color: #ffffff; }
.cw-btn-emerald {
    background: #059669;
    color: #ffffff;
}
.cw-btn-emerald:hover { background: #047857; color: #ffffff; }
.cw-btn-ghost {
    background: #ffffff;
    border-color: #cbd5e1;
    color: #334155;
}
.cw-btn-ghost:hover { background: #f8fafc; color: #0f172a; }
.cw-btn:disabled { opacity: 0.45; cursor: not-allowed; }

/* Table */
.cw-table-wrap {
    overflow-x: auto;
}
.cw-table {
    width: 100%;
    border-collapse: collapse;
}
.cw-table th {
    padding: 12px 16px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #64748b;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
    white-space: nowrap;
}
.cw-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.cw-table tbody tr:hover {
    background: #fafbfc;
}
.cw-cell-primary {
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
}
.cw-cell-sub {
    font-size: 11.5px;
    color: #64748b;
    margin-top: 2px;
}
.cw-vehicle-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}
.cw-vehicle-cell img {
    width: 46px;
    height: 34px;
    border-radius: 7px;
    object-fit: cover;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
}
.cw-package-pill {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 7px;
    background: #f1f5f9;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
}
.cw-price {
    font-size: 13px;
    font-weight: 800;
    color: #0f172a;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
}
.cw-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 700;
    white-space: nowrap;
}
.cw-status i {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}
.cw-status.is-amber { background: #fef3c7; color: #b45309; }
.cw-status.is-blue { background: #dbeafe; color: #1d4ed8; }
.cw-status.is-indigo { background: #e0e7ff; color: #4338ca; }
.cw-status.is-emerald { background: #dcfce7; color: #047857; }
.cw-status.is-rose { background: #fee2e2; color: #b91c1c; }
.cw-status.is-slate { background: #f1f5f9; color: #475569; }

.cw-row-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
}
.cw-empty {
    text-align: center;
    padding: 52px 20px !important;
    color: #64748b;
}
.cw-empty strong {
    display: block;
    margin-top: 8px;
    font-size: 14px;
    color: #0f172a;
}
.cw-empty span {
    display: block;
    margin-top: 3px;
    font-size: 12.5px;
}

/* Footer & Pagination */
.cw-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 20px;
    border-top: 1px solid #f1f5f9;
    flex-wrap: wrap;
}
.cw-footer-meta {
    font-size: 12.5px;
    color: #64748b;
}
.cw-footer-right {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}
.cw-perpage {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #64748b;
}
.cw-perpage select {
    height: 32px;
    padding: 0 8px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    font-size: 12px;
    color: #0f172a;
    background: #ffffff;
}
.cw-pagination .pagination {
    display: flex;
    align-items: center;
    gap: 4px;
    list-style: none;
    margin: 0;
    padding: 0;
}
.cw-pagination .page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0 10px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
}
.cw-pagination .page-item.active .page-link {
    background: #ef233c;
    border-color: #ef233c;
    color: #ffffff;
}
.cw-pagination .page-item.disabled .page-link {
    opacity: 0.45;
    pointer-events: none;
}

/* Modern Modal Box */
.cw-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1200;
    background: rgba(15, 23, 42, 0.52);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.cw-modal-backdrop[hidden] {
    display: none;
}
.cw-modal-dialog {
    width: 100%;
    max-width: 560px;
    max-height: 90vh;
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.cw-modal-dialog.is-compact {
    max-width: 480px;
}
.cw-modal-dialog > form {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    min-height: 0;
    overflow: hidden;
}
.cw-modal-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    padding: 18px 22px;
    border-bottom: 1px solid #f1f5f9;
    flex-shrink: 0;
}
.cw-modal-kicker {
    display: block;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.06em;
    color: #ef233c;
    margin-bottom: 2px;
}
.cw-modal-head h3 {
    margin: 0;
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
}
.cw-modal-close {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    font-size: 20px;
    line-height: 1;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.cw-modal-close:hover { background: #f8fafc; color: #0f172a; }
.cw-modal-body {
    padding: 20px 22px;
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.cw-modal-foot {
    padding: 14px 22px;
    border-top: 1px solid #f1f5f9;
    background: #fafbfc;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    flex-shrink: 0;
}
.cw-detail-hero {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}
.cw-detail-hero img {
    width: 78px;
    height: 54px;
    border-radius: 9px;
    object-fit: cover;
    border: 1px solid #cbd5e1;
}
.cw-detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}
.cw-detail-item {
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #f1f5f9;
    background: #ffffff;
}
.cw-detail-item span {
    display: block;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    color: #94a3b8;
    margin-bottom: 3px;
}
.cw-detail-item strong {
    font-size: 13px;
    color: #0f172a;
}
.cw-pkg-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
}
.cw-pkg-row strong {
    display: block;
    font-size: 13.5px;
    color: #0f172a;
}
.cw-pkg-row small {
    display: block;
    font-size: 11.5px;
    color: #64748b;
    margin-top: 2px;
}
.cw-pkg-meta {
    text-align: right;
    flex-shrink: 0;
}
.cw-pkg-meta strong {
    color: #ef233c;
    font-variant-numeric: tabular-nums;
}
.cw-pkg-meta span {
    font-size: 11px;
    color: #64748b;
}
.cw-field-group label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 6px;
}
.cw-field-group select {
    width: 100%;
    height: 40px;
    padding: 0 12px;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    font-size: 13px;
    color: #0f172a;
    background: #ffffff;
}
.cw-summary-strip {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    border-radius: 10px;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    font-size: 13px;
    color: #065f46;
}
@media (max-width: 1080px) {
    .cw-kpis { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .cw-kpis { grid-template-columns: 1fr; }
    .cw-detail-grid { grid-template-columns: 1fr; }
}
</style>
@endsection

@push('scripts')
<script>
const CW_PROMOS = @json($promosList);

function cwEsc(v) {
    const d = document.createElement('div');
    d.textContent = String(v ?? '');
    return d.innerHTML;
}

function cwFormatRp(n) {
    return 'Rp ' + Math.max(0, Math.round(Number(n) || 0)).toLocaleString('id-ID');
}

function getCwBooking(id) {
    const el = document.getElementById('cw-data-' + id);
    if (!el) return null;
    try { return JSON.parse(el.textContent); } catch (e) { return null; }
}

function openCwModal(id) {
    const m = document.getElementById(id);
    if (m) m.removeAttribute('hidden');
}

let cwActiveCameraStream = null;

function stopCwCamera() {
    if (cwActiveCameraStream) {
        cwActiveCameraStream.getTracks().forEach(t => t.stop());
        cwActiveCameraStream = null;
    }
    const camBox = document.getElementById('cwCameraLiveBox');
    if (camBox) camBox.hidden = true;
}

function closeCwModal(id) {
    stopCwCamera();
    const m = document.getElementById(id);
    if (m) m.setAttribute('hidden', 'hidden');
}

function openCwPackagesModal() {
    openCwModal('cwPackagesModal');
}

function openCwDetailModal(id) {
    const b = getCwBooking(id);
    if (!b) return;

    document.getElementById('cwDetailTitle').textContent = b.kode + ' · ' + b.pelanggan;
    document.getElementById('cwDetailBody').innerHTML = `
        <div class="cw-detail-hero">
            <img src="${b.thumb}" alt="${cwEsc(b.kendaraan)}">
            <div style="flex:1; min-width:0;">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap;">
                    <strong style="font-size:15px; color:#0f172a;">${cwEsc(b.kendaraan)}</strong>
                    <span class="cw-status is-${cwEsc(b.status_tone)}"><i></i>${cwEsc(b.status_label)}</span>
                </div>
                <div style="font-size:12px; color:#64748b; margin-top:3px;">${cwEsc(b.plat)} &bull; ${cwEsc(b.paket)}</div>
            </div>
        </div>
        <div class="cw-detail-grid">
            <div class="cw-detail-item"><span>Pelanggan</span><strong>${cwEsc(b.pelanggan)}</strong></div>
            <div class="cw-detail-item"><span>No. Telepon</span><strong>${cwEsc(b.pelanggan_hp)}</strong></div>
            <div class="cw-detail-item"><span>Jadwal Cuci</span><strong>${cwEsc(b.tanggal)} · ${cwEsc(b.jam)}</strong></div>
            <div class="cw-detail-item"><span>Harga Paket</span><strong style="color:#ef233c;">${cwEsc(b.harga_formatted)}</strong></div>
            <div class="cw-detail-item"><span>Petugas</span><strong>${cwEsc(b.petugas)}</strong></div>
            <div class="cw-detail-item"><span>Tagihan</span><strong>${b.kode_transaksi ? cwEsc(b.kode_transaksi) + ' (' + cwEsc(b.status_transaksi) + ')' : 'Belum diterbitkan'}</strong></div>
        </div>
        <div class="cw-detail-item" style="${b.has_promo ? 'background:#ecfdf5; border-color:#a7f3d0;' : 'background:#f8fafc; border-color:#e2e8f0;'}">
            <span>Status Kupon / Promo Member</span>
            ${b.has_promo
                ? `<div style="display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; margin-top:4px;">
                        <div>
                            <strong style="color:#065f46;">Menggunakan Kupon · ${cwEsc(b.promo_code)}</strong>
                            <div style="font-size:12px; color:#047857; margin-top:2px;">${cwEsc(b.promo_name || 'Promo Member')}${b.promo_benefit ? ' (' + cwEsc(b.promo_benefit) + ')' : ''}</div>
                        </div>
                        <span class="cw-status is-emerald"><i></i>Aktif</span>
                   </div>`
                : `<div style="margin-top:2px;">
                        <strong style="color:#475569;">Tidak Menggunakan Kupon</strong>
                        <div style="font-size:12px; color:#64748b; margin-top:2px;">Member memesan dengan tarif standar paket tanpa potongan kupon.</div>
                   </div>`
            }
        </div>
        ${(b.foto_sebelum_cuci_url || b.foto_sesudah_cuci_url) ? `
            <div style="display:grid; grid-template-columns:repeat(${(b.foto_sebelum_cuci_url && b.foto_sesudah_cuci_url) ? 2 : 1}, 1fr); gap:10px;">
                ${b.foto_sebelum_cuci_url ? `
                    <div class="cw-detail-item">
                        <span>Foto Sebelum Cuci</span>
                        <div style="margin-top:6px;">
                            <img src="${b.foto_sebelum_cuci_url}" alt="Foto sebelum cuci" style="width:100%; max-height:170px; object-fit:cover; border-radius:9px; border:1px solid #e2e8f0;">
                        </div>
                    </div>
                ` : ''}
                ${b.foto_sesudah_cuci_url ? `
                    <div class="cw-detail-item">
                        <span>Foto Hasil Cucian</span>
                        <div style="margin-top:6px;">
                            <img src="${b.foto_sesudah_cuci_url}" alt="Foto hasil cucian" style="width:100%; max-height:170px; object-fit:cover; border-radius:9px; border:1px solid #e2e8f0;">
                        </div>
                    </div>
                ` : ''}
            </div>
        ` : ''}
        ${b.catatan ? `<div class="cw-detail-item"><span>Catatan Pelanggan</span><strong style="font-weight:500;">${cwEsc(b.catatan)}</strong></div>` : ''}
    `;

    let nextActionHtml = '';
    if (b.status === 'menunggu') {
        nextActionHtml = `<button type="button" class="cw-btn cw-btn-primary" onclick="closeCwModal('cwDetailModal'); openCwActionModal(${b.id}, 'confirm');">Konfirmasi Booking</button>`;
    } else if (b.status === 'diterima') {
        nextActionHtml = `<button type="button" class="cw-btn cw-btn-dark" onclick="closeCwModal('cwDetailModal'); openCwActionModal(${b.id}, 'start');">Mulai Proses Cuci</button>`;
    } else if (b.status === 'proses' && !b.has_transaksi) {
        nextActionHtml = `<button type="button" class="cw-btn cw-btn-emerald" onclick="closeCwModal('cwDetailModal'); openCwActionModal(${b.id}, 'finalize');">Finalisasi &amp; Tagihan</button>`;
    }

    document.getElementById('cwDetailFoot').innerHTML = `
        <button type="button" class="cw-btn cw-btn-ghost" onclick="closeCwModal('cwDetailModal')">Tutup</button>
        ${nextActionHtml}
    `;
    openCwModal('cwDetailModal');
}

function buildCwPhotoCaptureBox(fieldName, heading, hint) {
    return `
        <div style="border:1.5px dashed #cbd5e1; border-radius:12px; padding:14px; background:#f8fafc;">
            <div style="margin-bottom:8px;">
                <strong style="display:block; font-size:12.5px; color:#0f172a;">${cwEsc(heading)} <span style="color:#dc2626;">* Wajib</span></strong>
                <small style="font-size:11.5px; color:#64748b;">${cwEsc(hint)} (JPG/PNG/WEBP, maks. 5 MB)</small>
            </div>
            <input type="hidden" name="${cwEsc(fieldName)}_camera" id="cwBeforeCameraInput" value="">
            <input type="file" name="${cwEsc(fieldName)}" id="cwBeforeFileInput" accept="image/*" capture="environment" style="display:none;" onchange="handleCwBeforeFileChange(this)">
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <button type="button" class="cw-btn cw-btn-sm cw-btn-primary" onclick="startCwCamera()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    <span>Buka Kamera</span>
                </button>
                <button type="button" class="cw-btn cw-btn-sm cw-btn-ghost" onclick="document.getElementById('cwBeforeFileInput').click()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span>Unggah Foto</span>
                </button>
            </div>
            <div id="cwCameraError" style="display:none; margin-top:8px; font-size:11.5px; color:#dc2626; font-weight:600;"></div>
            <div id="cwCameraLiveBox" hidden style="margin-top:10px;">
                <video id="cwCameraVideo" autoplay playsinline style="width:100%; max-height:220px; border-radius:9px; background:#0f172a; object-fit:cover;"></video>
                <div style="display:flex; gap:8px; margin-top:8px;">
                    <button type="button" class="cw-btn cw-btn-sm cw-btn-emerald" style="flex:1;" onclick="captureCwCameraPhoto()">Ambil Foto</button>
                    <button type="button" class="cw-btn cw-btn-sm cw-btn-ghost" onclick="stopCwCamera()">Tutup Kamera</button>
                </div>
            </div>
            <div id="cwBeforePreviewBox" hidden style="margin-top:10px; position:relative;">
                <img id="cwBeforePreviewImg" src="" alt="Preview foto" style="width:100%; max-height:190px; object-fit:cover; border-radius:9px; border:1px solid #cbd5e1;">
                <button type="button" class="cw-btn cw-btn-sm cw-btn-ghost" style="margin-top:6px;" onclick="clearCwBeforePhoto()">Hapus / Ulangi Foto</button>
            </div>
        </div>
    `;
}

function openCwActionModal(id, action) {
    const b = getCwBooking(id);
    if (!b) return;

    const form = document.getElementById('cwActionForm');
    const title = document.getElementById('cwActionTitle');
    const kicker = document.getElementById('cwActionKicker');
    const body = document.getElementById('cwActionBody');
    const submitBtn = document.getElementById('cwActionSubmitBtn');

    submitBtn.className = 'cw-btn';
    submitBtn.disabled = false;
    form.dataset.requirePhoto = '0';

    if (action === 'confirm') {
        form.action = b.confirm_url;
        kicker.textContent = 'KONFIRMASI ANTREAN';
        title.textContent = 'Konfirmasi Booking ' + b.kode;
        submitBtn.classList.add('cw-btn-primary');
        submitBtn.textContent = 'Ya, Konfirmasi Booking';
        body.innerHTML = `
            <p style="margin:0; font-size:13.5px; color:#334155; line-height:1.55;">
                Konfirmasi booking cuci mobil <strong>${cwEsc(b.paket)}</strong> untuk <strong>${cwEsc(b.pelanggan)}</strong> (${cwEsc(b.kendaraan)} · ${cwEsc(b.plat)}) pada jadwal <strong>${cwEsc(b.tanggal)} ${cwEsc(b.jam)}</strong>?
            </p>
        `;
    } else if (action === 'start') {
        form.action = b.start_url;
        form.dataset.requirePhoto = '1';
        kicker.textContent = 'PROSES PENCUCIAN';
        title.textContent = 'Mulai Cuci ' + b.kode;
        submitBtn.classList.add('cw-btn-dark');
        submitBtn.textContent = 'Mulai Proses Cuci';
        submitBtn.disabled = true;
        body.innerHTML = `
            <p style="margin:0; font-size:13px; color:#334155; line-height:1.55;">
                Tandai kendaraan <strong>${cwEsc(b.kendaraan)}</strong> (${cwEsc(b.plat)}) milik <strong>${cwEsc(b.pelanggan)}</strong> masuk tahap pencucian sekarang.
            </p>
            ${buildCwPhotoCaptureBox('foto_sebelum_cuci', 'Dokumentasi Sebelum Cuci', 'Wajib foto kondisi mobil sebelum dicuci melalui kamera atau unggah file.')}
        `;
    } else if (action === 'finalize') {
        form.action = b.finalize_url;
        form.dataset.requirePhoto = '1';
        kicker.textContent = 'FINALISASI & TERUSKAN KE FINANCE';
        title.textContent = 'Finalisasi ' + b.kode;
        submitBtn.classList.add('cw-btn-emerald');
        submitBtn.textContent = 'Selesaikan & Kirim ke Finance';
        submitBtn.disabled = true;
        body.innerHTML = `
            <p style="margin:0; font-size:13px; color:#475569; line-height:1.55;">
                Selesaikan pengerjaan paket <strong>${cwEsc(b.paket)}</strong> untuk <strong>${cwEsc(b.pelanggan)}</strong> (${cwEsc(b.kendaraan)}). Tagihan akan otomatis diterbitkan dan diteruskan ke <strong>Finance / Kasir</strong> untuk proses pembayaran.
            </p>
            ${buildCwPhotoCaptureBox('foto_sesudah_cuci', 'Dokumentasi Hasil Cucian', 'Wajib foto hasil akhir cucian mobil melalui kamera atau unggah file sebelum dikirim ke Finance.')}
            <div class="cw-detail-item" style="${b.has_promo ? 'background:#ecfdf5; border-color:#a7f3d0;' : 'background:#f8fafc; border-color:#e2e8f0;'}">
                <span>Kupon Member</span>
                ${b.has_promo
                    ? `<strong style="color:#065f46;">${cwEsc(b.promo_code)} — ${cwEsc(b.promo_name || 'Promo Member')}${b.promo_benefit ? ' (' + cwEsc(b.promo_benefit) + ')' : ''}</strong>`
                    : `<strong style="color:#475569;">Tidak menggunakan kupon</strong>`
                }
            </div>
            <div class="cw-summary-strip">
                <span>Total Tagihan ke Finance</span>
                <strong>${cwEsc(b.estimated_total_formatted || b.harga_formatted)}</strong>
            </div>
        `;
    }

    openCwModal('cwActionModal');
}

function updateCwFinalizePreview(basePrice) {
    const sel = document.getElementById('cwPromoSelect');
    const out = document.getElementById('cwFinalizeTotal');
    if (!sel || !out) return;
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) {
        out.textContent = cwFormatRp(basePrice);
        return;
    }
    const tipe = opt.dataset.tipe;
    const nilai = Number(opt.dataset.nilai || 0);
    const discount = tipe === 'persentase' ? (basePrice * (nilai / 100)) : nilai;
    out.textContent = cwFormatRp(Math.max(0, basePrice - discount));
}

async function startCwCamera() {
    const errEl = document.getElementById('cwCameraError');
    const liveBox = document.getElementById('cwCameraLiveBox');
    const video = document.getElementById('cwCameraVideo');
    if (errEl) { errEl.style.display = 'none'; errEl.textContent = ''; }
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        if (errEl) { errEl.textContent = 'Browser tidak mendukung akses kamera langsung. Silakan gunakan tombol Unggah Foto.'; errEl.style.display = 'block'; }
        return;
    }
    try {
        stopCwCamera();
        cwActiveCameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
        if (video) video.srcObject = cwActiveCameraStream;
        if (liveBox) liveBox.hidden = false;
    } catch (err) {
        if (errEl) { errEl.textContent = 'Izin kamera ditolak atau kamera tidak ditemukan. Anda tetap bisa mengunggah foto dari perangkat.'; errEl.style.display = 'block'; }
    }
}

function syncCwStartSubmitState() {
    const form = document.getElementById('cwActionForm');
    const submitBtn = document.getElementById('cwActionSubmitBtn');
    if (!form || !submitBtn || form.dataset.requirePhoto !== '1') return;
    const camVal = (document.getElementById('cwBeforeCameraInput')?.value || '').trim();
    const hasFile = Boolean(document.getElementById('cwBeforeFileInput')?.files?.length);
    submitBtn.disabled = !(camVal !== '' || hasFile);
}

function captureCwCameraPhoto() {
    const video = document.getElementById('cwCameraVideo');
    const camInput = document.getElementById('cwBeforeCameraInput');
    const fileInput = document.getElementById('cwBeforeFileInput');
    const previewBox = document.getElementById('cwBeforePreviewBox');
    const previewImg = document.getElementById('cwBeforePreviewImg');
    if (!video || !video.videoWidth) return;
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
    if (camInput) camInput.value = dataUrl;
    if (fileInput) fileInput.value = '';
    if (previewImg) previewImg.src = dataUrl;
    if (previewBox) previewBox.hidden = false;
    stopCwCamera();
    syncCwStartSubmitState();
}

function handleCwBeforeFileChange(input) {
    const camInput = document.getElementById('cwBeforeCameraInput');
    const previewBox = document.getElementById('cwBeforePreviewBox');
    const previewImg = document.getElementById('cwBeforePreviewImg');
    const errEl = document.getElementById('cwCameraError');
    if (errEl) { errEl.style.display = 'none'; errEl.textContent = ''; }
    if (!input.files || !input.files[0]) { syncCwStartSubmitState(); return; }
    stopCwCamera();
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
        clearCwBeforePhoto();
        if (errEl) {
            errEl.textContent = 'Ukuran foto melebihi batas maksimal 5 MB. Silakan pilih foto lain yang lebih kecil.';
            errEl.style.display = 'block';
        }
        return;
    }
    const reader = new FileReader();
    reader.onload = function (ev) {
        const img = new Image();
        img.onload = function () {
            const maxDim = 1600;
            let w = img.width;
            let h = img.height;
            if (w > maxDim || h > maxDim) {
                const scale = Math.min(maxDim / w, maxDim / h);
                w = Math.round(w * scale);
                h = Math.round(h * scale);
            }
            const canvas = document.createElement('canvas');
            canvas.width = w;
            canvas.height = h;
            canvas.getContext('2d').drawImage(img, 0, 0, w, h);
            const compressedDataUrl = canvas.toDataURL('image/jpeg', 0.82);
            if (camInput) camInput.value = compressedDataUrl;
            input.value = '';
            if (previewImg) previewImg.src = compressedDataUrl;
            if (previewBox) previewBox.hidden = false;
            syncCwStartSubmitState();
        };
        img.onerror = function () {
            if (camInput) camInput.value = '';
            if (previewImg) previewImg.src = ev.target.result;
            if (previewBox) previewBox.hidden = false;
            syncCwStartSubmitState();
        };
        img.src = ev.target.result;
    };
    reader.readAsDataURL(file);
}

function clearCwBeforePhoto() {
    const camInput = document.getElementById('cwBeforeCameraInput');
    const fileInput = document.getElementById('cwBeforeFileInput');
    const previewBox = document.getElementById('cwBeforePreviewBox');
    const previewImg = document.getElementById('cwBeforePreviewImg');
    if (camInput) camInput.value = '';
    if (fileInput) fileInput.value = '';
    if (previewImg) previewImg.src = '';
    if (previewBox) previewBox.hidden = true;
    syncCwStartSubmitState();
}

document.querySelectorAll('.cw-modal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', e => {
        if (e.target === backdrop) { stopCwCamera(); backdrop.setAttribute('hidden', 'hidden'); }
    });
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        stopCwCamera();
        document.querySelectorAll('.cw-modal-backdrop:not([hidden])').forEach(m => m.setAttribute('hidden', 'hidden'));
    }
});
document.getElementById('cwActionForm')?.addEventListener('submit', function (e) {
    if (this.dataset.requirePhoto === '1') {
        const camVal = (document.getElementById('cwBeforeCameraInput')?.value || '').trim();
        const hasFile = Boolean(document.getElementById('cwBeforeFileInput')?.files?.length);
        if (camVal === '' && !hasFile) {
            e.preventDefault();
            const errEl = document.getElementById('cwCameraError');
            if (errEl) { errEl.textContent = 'Foto mobil sebelum dicuci wajib diambil atau diunggah terlebih dahulu.'; errEl.style.display = 'block'; }
            return;
        }
    }
    const btn = document.getElementById('cwActionSubmitBtn');
    if (btn) { btn.disabled = true; btn.textContent = 'Memproses...'; }
});
</script>
@endpush
