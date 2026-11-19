@extends('layouts.app')

@section('title', 'Manajemen Mobil & Showroom - Admin YZA Automotive')
@section('admin-page-title', 'Manajemen Mobil & Showroom')

@section('content')
@php
    $resolveCarThumbnail = static function ($car) {
        $idStr = strtolower(trim($car->merk . ' ' . $car->model . ' ' . $car->tipe));
        if (str_contains($idStr, 'toyota gr supra')) {
            return asset('assets/images/catalog/toyota-gr-supra/front.png');
        }
        if (str_contains($idStr, 'supra mk4') || str_contains($idStr, 'supra 3.0l a/t')) {
            return asset('assets/images/catalog/toyota-supra-mk4/front.png');
        }
        $galleryPhoto = collect(app(\App\Services\VehicleGalleryService::class)->for($car))->flatMap(fn ($c) => $c['images'] ?? [])->first()['src'] ?? null;
        if ($galleryPhoto && !str_contains($galleryPhoto, 'yza-hero-reference-v3.png')) {
            return $galleryPhoto;
        }
        if ($car->foto && $car->foto->isNotEmpty()) {
            $first = $car->foto->first();
            if ($first && $first->existsOnDisk()) {
                return $first->url();
            }
        }
        return asset('assets/images/hero/yza-hero-black-v2.png');
    };

    $activeTab = $activeTab ?? request('tab', 'katalog');
    if (!in_array($activeTab, ['katalog', 'foto'], true)) {
        $activeTab = 'katalog';
    }

    $isOwner = auth()->user()?->roleKey() === 'owner';
@endphp

<div class="admin-vehicle-management-page">
    {{-- Header Terpadu Manajemen Mobil & Showroom --}}
    <header class="vehicle-header">
        <div class="vehicle-header-left">
            <nav class="vehicle-breadcrumb" aria-label="Breadcrumb">
                <span class="breadcrumb-back-arrow" aria-hidden="true">&lsaquo;</span>
                <span class="breadcrumb-item">Penjualan Mobil</span>
                <span class="breadcrumb-separator">&gt;</span>
                <span class="breadcrumb-current">Manajemen Mobil &amp; Showroom</span>
            </nav>

            <div class="vehicle-title-group">
                <div class="vehicle-title-icon" aria-hidden="true">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m4 10 2-6h12l2 6"></path>
                        <path d="M3 11h18v9h-3v-3H6v3H3v-9Z"></path>
                        <path d="M9 14h6"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="vehicle-heading">Manajemen Mobil &amp; Showroom</h1>
                    <p class="vehicle-subheading">Kelola kendaraan, stok, warna, galeri, dan tampilan showroom YZA Automotive dalam satu halaman.</p>
                </div>
            </div>
        </div>

        <div class="vehicle-header-right">
            @if(!$isOwner)
                <button type="button" class="btn-primary-action" onclick="openVehicleActionModal('new', 'add_unit')" title="Daftarkan Unit Mobil Baru">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Tambah Mobil</span>
                </button>
            @else
                <span class="owner-readonly-badge" title="Mode Baca Saja (Owner)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <span>Owner (Read-Only)</span>
                </span>
            @endif
        </div>
    </header>

    @if(session('success'))
        <div class="admin-alert is-success" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- 4 Kartu Statistik Gudang & Inventori Mobil --}}
    <section class="vehicle-kpi-grid" aria-label="Statistik Gudang &amp; Stok Mobil">
        {{-- Card 1: Total Model Mobil --}}
        <div class="vehicle-kpi-card is-red">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m4 10 2-6h12l2 6"></path>
                    <path d="M3 11h18v9h-3v-3H6v3H3v-9Z"></path>
                    <path d="M9 14h6"></path>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Total Model Mobil</span>
                <strong class="kpi-value" id="kpiTotalUnit">{{ number_format($stats['total_model_katalog'] ?? $stats['total_unit']) }}</strong>
                <div class="kpi-subtext">
                    <span>Model unik terdaftar di showroom</span>
                </div>
            </div>
        </div>

        {{-- Card 2: Total Unit Fisik --}}
        <div class="vehicle-kpi-card is-blue">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                    <path d="M3 9h18M3 15h18M9 3v18M15 3v18"></path>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Total Unit Fisik</span>
                <strong class="kpi-value" id="kpiTotalFisik">{{ number_format($stats['unit_fisik_tercatat'] ?? $stats['total_unit_fisik']) }}</strong>
                <div class="kpi-subtext">
                    <span>Unit aktif tercatat di gudang</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Unit Siap Jual (Ready) --}}
        <div class="vehicle-kpi-card is-green">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Unit Siap Jual</span>
                <strong class="kpi-value" id="kpiTersedia">{{ number_format($stats['unit_ready'] ?? $stats['tersedia']) }}</strong>
                <div class="kpi-subtext is-up">
                    <span>{{ number_format($stats['reservasi_aktif'] ?? 0) }} unit sedang direservasi</span>
                </div>
            </div>
        </div>

        {{-- Card 4: Unit Terjual --}}
        <div class="vehicle-kpi-card is-orange">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="8" cy="21" r="1"></circle>
                    <circle cx="19" cy="21" r="1"></circle>
                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Unit Terjual</span>
                <strong class="kpi-value" id="kpiTerjual">{{ number_format($stats['terjual'] ?? 0) }}</strong>
                <div class="kpi-subtext">
                    <span>Unit berhasil diserahterimakan</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Container Card --}}
    <main class="vehicle-main-card">
        {{-- ========================================================== --}}
        {{-- TAB 1: KELOLA MOBIL (KATALOG & PENGATURAN STOK)            --}}
        {{-- ========================================================== --}}
        <div id="tab-katalog" class="vehicle-tab-pane {{ $activeTab === 'katalog' ? 'is-active' : '' }}" role="tabpanel">
            <form class="vehicle-filter-toolbar" method="get" action="{{ route('admin.mobil.index') }}">
                <input type="hidden" name="tab" value="katalog">
                <div class="filter-wrap search-box">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" name="q" class="filter-input" value="{{ $q ?? '' }}" placeholder="Cari merek, model, varian, kode mobil...">
                </div>
                <select name="merk" class="filter-select">
                    <option value="">Semua Merek</option>
                    @foreach($brands ?? [] as $brand)
                        <option value="{{ $brand }}" {{ ($merk ?? '') === $brand ? 'selected' : '' }}>{{ $brand }}</option>
                    @endforeach
                </select>
                <select name="status" class="filter-select">
                    <option value="">Semua Status</option>
                    <option value="tersedia" {{ ($status ?? '') === 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                    <option value="dibooking" {{ ($status ?? '') === 'dibooking' ? 'selected' : '' }}>Dibooking</option>
                    <option value="terjual" {{ ($status ?? '') === 'terjual' ? 'selected' : '' }}>Terjual</option>
                    <option value="nonaktif" {{ ($status ?? '') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <select name="kondisi" class="filter-select">
                    <option value="">Semua Kondisi</option>
                    <option value="baru" {{ ($kondisi ?? '') === 'baru' ? 'selected' : '' }}>Baru</option>
                    <option value="bekas" {{ ($kondisi ?? '') === 'bekas' ? 'selected' : '' }}>Bekas</option>
                </select>
                <div class="filter-actions-group">
                    <button type="submit" class="btn-filter-apply">Terapkan</button>
                    @if(!empty($q) || !empty($merk) || !empty($status) || !empty($kondisi))
                        <a href="{{ route('admin.mobil.index', ['tab' => 'katalog']) }}" class="btn-filter-reset">Reset</a>
                    @endif
                </div>
            </form>

            {{-- Grid 3-Kolom Kartu Kelola Mobil --}}
            <div class="showroom-card-grid">
                @forelse($catalogCars as $car)
                    @php
                        $availability = $car->getAttribute('availability') ?? [];
                        $colorStocks = $car->exists ? array_values(app(\App\Services\VehicleColorStockService::class)->colors($car)) : [];
                        $colorColl = collect($colorStocks);
                        $stockTotal = $colorColl->isNotEmpty()
                            ? (int) $colorColl->sum('stock_total')
                            : ($availability['stock_total'] ?? (($availability['stock'] ?? null) !== null ? $availability['stock'] : 0));
                        $stockOrderable = $colorColl->isNotEmpty()
                            ? (int) $colorColl->sum('stock')
                            : ($availability['stock'] ?? 0);
                        $reservedUnits = $colorColl->isNotEmpty()
                            ? (int) $colorColl->sum('reserved_units')
                            : ($availability['reserved_units'] ?? ($availability['active_reservation_count'] ?? 0));
                        $soldUnits = $colorColl->isNotEmpty()
                            ? (int) $colorColl->sum('sold_units')
                            : ($availability['sold_units'] ?? 0);
                        $fotoUrl = $resolveCarThumbnail($car);
                        $cleanStatus = strtolower((string) $car->status);
                        $publicPreviewUrl = $car->showcase_slug
                            ? route('mobil.showcase', ['slug' => $car->showcase_slug, 'source' => 'showroom'])
                            : route('mobil.show', $car);

                        $carDataPayload = null;
                        if ($car->exists) {
                            $invHistory = isset($inventories[$car->id_mobil]) ? $inventories[$car->id_mobil]->histories : collect();
                            $activeOrdersList = $ordersByCar[$car->id_mobil] ?? collect();
                            $activeBookingsList = $bookingsByCar[$car->id_mobil] ?? collect();

                            $angles = [
                                'front' => ['label' => 'Depan (Front 3/4)', 'path' => null],
                                'side' => ['label' => 'Samping (Side Profile)', 'path' => null],
                                'rear' => ['label' => 'Belakang (Rear 3/4)', 'path' => null],
                                'interior' => ['label' => 'Kabin / Interior', 'path' => null],
                            ];
                            $defaultColor = $car->warnaPilihan->firstWhere('is_default', true) ?: $car->warnaPilihan->first();
                            if ($defaultColor && $defaultColor->relationLoaded('galeri')) {
                                foreach ($defaultColor->galeri as $galeriItem) {
                                    if (isset($angles[$galeriItem->sudut]) && is_file(public_path($galeriItem->path))) {
                                        $angles[$galeriItem->sudut]['path'] = asset($galeriItem->path);
                                    }
                                }
                            }
                            $slug = \Illuminate\Support\Str::slug($car->merk . ' ' . $car->model);
                            foreach (['front', 'side', 'rear', 'interior'] as $angleKey) {
                                if (!$angles[$angleKey]['path']) {
                                    $candidate = "assets/images/vehicles/{$slug}/{$angleKey}.png";
                                    if (file_exists(public_path($candidate))) {
                                        $angles[$angleKey]['path'] = asset($candidate);
                                    }
                                }
                            }
                            if (!$angles['front']['path'] && $car->foto->isNotEmpty()) {
                                $angles['front']['path'] = $car->foto->first()->url();
                            }

                            $identity = app(\App\Services\CatalogVehicleIdentityService::class);
                            $matchingPreorderModels = $identity->activePreordersList($car, $allActivePreorders ?? null);
                            $matchingPreorders = $matchingPreorderModels->map(function ($po) {
                                return [
                                    'id_preorder' => $po->getKey(),
                                    'kode_preorder' => 'PRE-' . str_pad((string) $po->getKey(), 5, '0', STR_PAD_LEFT),
                                    'customer_name' => $po->pelanggan?->nama_lengkap ?: 'Member YZA',
                                    'customer_code' => $po->pelanggan?->kode_pelanggan ?: 'MEM',
                                    'customer_phone' => $po->pelanggan?->nomor_telepon ?: ($po->pelanggan?->no_hp ?: '—'),
                                    'customer_email' => $po->pelanggan?->email ?: ($po->pelanggan?->user?->email ?: '—'),
                                    'vehicle' => $po->nama_mobil,
                                    'variant' => $po->varian ?: 'Standar',
                                    'year' => (int) ($po->tahun ?: date('Y')),
                                    'color' => $po->warna ?: 'Standar',
                                    'status' => $po->status,
                                    'status_label' => ucfirst(str_replace('_', ' ', $po->status)),
                                    'created_at' => optional($po->created_at)->format('d M Y, H:i') ?: '',
                                    'detail_url' => route('admin.preorder-mobil.detail', $po),
                                ];
                            })->values()->all();

                            $carDataPayload = [
                                'id' => $car->id_mobil,
                                'kode' => $car->kode_mobil,
                                'merk' => $car->merk,
                                'model' => $car->model,
                                'tipe' => $car->tipe ?: '',
                                'tahun' => (int) $car->tahun,
                                'warna' => $car->warna ?: '',
                                'transmisi' => $car->transmisi ?: '',
                                'bahan_bakar' => $car->bahan_bakar ?: '',
                                'nomor_rangka' => $car->nomor_rangka ?: '',
                                'nomor_mesin' => $car->nomor_mesin ?: '',
                                'kilometer' => (int) $car->kilometer,
                                'harga_beli' => (float) $car->harga_beli,
                                'harga_jual' => (float) $car->harga_jual,
                                'harga_jual_formatted' => 'Rp ' . number_format((float) $car->harga_jual, 0, ',', '.'),
                                'kondisi' => $car->kondisi,
                                'status' => $car->status,
                                'tampil_di' => $car->tampil_di ?: 'keduanya',
                                'is_limited_edition' => (bool) $car->is_limited_edition,
                                'showcase_slug' => $car->showcase_slug ?: '',
                                'deskripsi' => $car->deskripsi ?: '',
                                'tanggal_masuk' => optional($car->tanggal_masuk)->format('Y-m-d') ?: '',
                                'thumbnail' => $fotoUrl,
                                'color_stocks' => $colorStocks,
                                'stock_total' => $stockTotal,
                                'stock_orderable' => $stockOrderable,
                                'reserved_units' => $reservedUnits,
                                'sold_units' => $soldUnits,
                                'is_sold' => $car->status === 'terjual',
                                'photos' => $car->foto->map(fn($f) => [
                                    'id' => $f->id_foto,
                                    'url' => $f->url(),
                                    'is_primary' => (bool) $f->foto_utama,
                                    'primary_url' => route('admin.mobil.foto.primary', [$car, $f]),
                                    'destroy_url' => route('admin.mobil.foto.destroy', [$car, $f]),
                                ])->values(),
                                'colors' => $car->warnaPilihan->map(fn($w) => [
                                    'id' => $w->id_warna,
                                    'nama' => $w->nama,
                                    'hex' => $w->hex,
                                    'slug' => $w->slug,
                                    'is_default' => (bool) $w->is_default,
                                ])->values(),
                                'angles' => $angles,
                                'histories' => $invHistory->map(fn($h) => [
                                    'tanggal' => optional($h->created_at)->format('d M Y, H:i') ?: '—',
                                    'jenis' => ucwords(str_replace('_', ' ', $h->jenis)),
                                    'jumlah' => $h->jumlah_perubahan > 0 ? ('+' . $h->jumlah_perubahan) : $h->jumlah_perubahan,
                                    'before_after' => $h->stock_before . ' → ' . $h->stock_after,
                                    'catatan' => $h->catatan ?: 'Pergerakan sistem',
                                ])->values(),
                                'active_orders' => $activeOrdersList->map(fn($o) => [
                                    'nomor' => $o->nomor_pesanan,
                                    'nama' => $o->nama_pembeli,
                                    'status' => ucwords(str_replace('_', ' ', $o->status)),
                                ])->values(),
                                'active_bookings' => $activeBookingsList->map(fn($b) => [
                                    'kode' => $b->kode_booking,
                                    'status' => ucwords(str_replace('_', ' ', $b->status)),
                                ])->values(),
                                'urls' => [
                                    'show' => route('admin.mobil.show', $car),
                                    'stock_adjust' => route('admin.mobil.stock.adjust', $car),
                                    'update' => route('admin.mobil.update', $car),
                                    'deactivate' => route('admin.mobil.deactivate', $car),
                                    'photo_store' => route('admin.mobil.foto.store', $car),
                                    'public_preview' => $publicPreviewUrl,
                                ],
                                'preorders' => $matchingPreorders,
                            ];
                        }
                    @endphp
                    <article class="showroom-car-card"
                             id="{{ $car->exists ? 'row-car-' . $car->id_mobil : 'catalog-car-' . app(\App\Services\CatalogVehicleIdentityService::class)->key($car) }}"
                             data-default-thumb="{{ $fotoUrl }}"
                             data-default-color="{{ $car->warna ?: 'Warna —' }}"
                             data-default-stock="{{ (int) $stockOrderable }}"
                             data-exists="{{ $car->exists ? '1' : '0' }}"
                             data-can-purchase="{{ ($availability['can_purchase'] ?? false) ? '1' : '0' }}"
                             data-status="{{ $cleanStatus }}">
                        @if($car->exists && $carDataPayload)
                            <script type="application/json" id="car-data-{{ $car->id_mobil }}">@json($carDataPayload)</script>
                        @endif
                        <div class="showroom-media">
                            <img src="{{ $fotoUrl }}" alt="{{ $car->merk }} {{ $car->model }}" loading="lazy" data-role="card-image">
                            <div class="showroom-media-gradient"></div>
                            <div class="showroom-media-badges">
                                @if($car->status === 'tersedia' && $stockOrderable > 0 && ($availability['can_purchase'] ?? false))
                                    <span class="showroom-badge is-available" data-role="availability-badge">Tersedia</span>
                                @elseif($cleanStatus === 'dibooking')
                                    <span class="showroom-badge is-booked" data-role="availability-badge">Dipesan</span>
                                @elseif($cleanStatus === 'terjual')
                                    <span class="showroom-badge is-sold" data-role="availability-badge">Terjual</span>
                                @elseif(!$car->exists || $stockOrderable < 1)
                                    <span class="showroom-badge is-inactive" data-role="availability-badge">{{ $car->exists ? 'Stok Habis' : 'Belum Ada Unit Fisik' }}</span>
                                @else
                                    <span class="showroom-badge is-inactive" data-role="availability-badge">{{ ucfirst($cleanStatus) }}</span>
                                @endif

                                @if($car->is_limited_edition)
                                    <span class="showroom-badge is-highlight">Limited Edition</span>
                                @endif
                                <span class="showroom-badge is-condition">{{ ucfirst($car->kondisi) }}</span>
                            </div>
                            <div class="showroom-media-code">{{ $car->kode_mobil }}</div>
                        </div>

                        <div class="showroom-body">
                            <div class="showroom-top">
                                <div class="showroom-title-wrap">
                                    <h3 class="showroom-title" title="{{ $car->merk }} {{ $car->model }}">{{ $car->merk }} {{ $car->model }}</h3>
                                    <p class="showroom-sub">{{ $car->tipe ?: 'Koleksi YZA' }} &bull; {{ $car->tahun ?: '—' }}</p>
                                </div>
                            </div>

                            <div class="showroom-price-row">
                                <div class="showroom-price">Rp {{ number_format((float) $car->harga_jual, 0, ',', '.') }}</div>
                            </div>

                            @if(!empty($colorStocks))
                                <div class="showroom-color-panel">
                                    <div class="showroom-color-panel-head">
                                        <span class="color-panel-label">Pilihan Warna</span>
                                        <span class="color-panel-active" data-role="selected-color-summary">Semua ({{ (int) $stockOrderable }} unit)</span>
                                    </div>
                                    <div class="showroom-color-picker" aria-label="Pilih warna kendaraan">
                                        <button type="button"
                                                class="showroom-color-chip is-active"
                                                data-color-mode="all"
                                                data-color-label="Semua"
                                                data-color-stock="{{ (int) $stockOrderable }}"
                                                data-color-image="{{ $fotoUrl }}"
                                                onclick="selectShowroomCardColor(this)"
                                                title="Semua warna ({{ (int) $stockOrderable }} unit tersedia)">
                                            <span>Semua</span>
                                        </button>
                                        <div class="showroom-color-dots-group">
                                            @foreach($colorStocks as $cs)
                                                @php
                                                    $csImages = collect($cs['images'] ?? []);
                                                    $csFront = $csImages->firstWhere('type', 'front')['src'] ?? ($csImages->first()['src'] ?? '');
                                                    $csStock = (int) ($cs['stock'] ?? 0);
                                                @endphp
                                                <button type="button"
                                                        class="showroom-color-dot {{ $csStock < 1 ? 'is-empty' : '' }}"
                                                        data-color-mode="single"
                                                        data-color-label="{{ $cs['label'] }}"
                                                        data-color-stock="{{ $csStock }}"
                                                        data-color-image="{{ $csFront }}"
                                                        onclick="selectShowroomCardColor(this)"
                                                        title="{{ $cs['label'] }} — {{ $csStock > 0 ? $csStock.' unit tersedia' : 'Stok Kosong' }}">
                                                    <span class="dot-swatch" style="background: {{ $cs['swatch'] ?? '#cbd5e1' }};"></span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="showroom-spec-strip">
                                <span>{{ $car->tahun ?: 'Tahun —' }}</span>
                                <span class="spec-sep">&bull;</span>
                                <span>{{ ucfirst($car->transmisi ?: 'Otomatis') }}</span>
                                <span class="spec-sep">&bull;</span>
                                <span>{{ ucfirst($car->bahan_bakar ?: 'Bensin') }}</span>
                                @if(empty($colorStocks))
                                    <span class="spec-sep">&bull;</span>
                                    <span data-role="color-chip">{{ $car->warna ?: 'Warna —' }}</span>
                                @endif
                            </div>

                            @if($car->deskripsi)
                                <p class="showroom-description">{{ $car->deskripsi }}</p>
                            @endif

                            <div class="showroom-actions-row">
                                @if(!$car->exists)
                                <button type="button" class="btn-showroom-detail" onclick="openVehicleActionModal('{{ app(\App\Services\CatalogVehicleIdentityService::class)->key($car) }}', 'add_unit')">Kelola Model &amp; Stok</button>
                                @else
                                @if(!$isOwner)<button type="button" class="btn-showroom-detail" onclick="openVehicleActionModal({{ $car->id_mobil }}, 'add_unit', this.closest('.showroom-car-card')?.dataset.selectedColor || null)">Kelola Stok</button>@endif
                                <button type="button" class="btn-showroom-edit" onclick="openVehicleActionModal({{ $car->id_mobil }}, 'detail', this.closest('.showroom-car-card')?.dataset.selectedColor || null)">Detail</button>
                                @if(!$isOwner)<button type="button" class="btn-showroom-edit" onclick="openVehicleActionModal({{ $car->id_mobil }}, 'edit', this.closest('.showroom-car-card')?.dataset.selectedColor || null)">Edit</button>@endif
                                @endif
                                <a class="btn-showroom-preview" href="{{ $publicPreviewUrl }}" target="_blank" rel="noopener" title="Lihat preview halaman publik website">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                    <span>Preview</span>
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="catalog-empty-full">
                        <p>Tidak ada kendaraan yang sesuai dengan kriteria filter saat ini.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ========================================================== --}}
            </main>
</div>

{{-- ========================================================================= --}}
{{-- FLOATING / FIXED ACTION DROPDOWN PORTAL (PREVENTS TABLE CLIPPING)         --}}
{{-- ========================================================================= --}}
<div id="floatingActionDropdown" class="floating-action-dropdown-menu" hidden role="menu" tabindex="-1" aria-orientation="vertical">
    {{-- Initial template items for accessibility & test fixtures; overwritten dynamically per vehicle on click --}}
    <button type="button" class="dropdown-item" data-action="detail" role="menuitem">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
        <span>Detail Kendaraan</span>
    </button>
    <button type="button" class="dropdown-item" data-action="stock" role="menuitem">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
        <span>Kelola Stok</span>
    </button>
    <button type="button" class="dropdown-item" data-action="edit" role="menuitem">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
        <span>Edit Kendaraan</span>
    </button>
    <button type="button" class="dropdown-item" data-action="add_unit" role="menuitem" title="Tambahkan Unit ke Inventori">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        <span>Tambah Unit Fisik</span>
    </button>
    <button type="button" class="dropdown-item has-badge" data-action="preorder" role="menuitem">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 8v4l3 3"></path><circle cx="12" cy="12" r="9"></circle></svg>
        <span>Permintaan Pre-Order</span>
    </button>
    <button type="button" class="dropdown-item" data-action="gallery" role="menuitem">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path></svg>
        <span>Galeri &amp; Warna</span>
    </button>
    <div class="dropdown-divider"></div>
    <button type="button" class="dropdown-item is-danger" data-action="deactivate" role="menuitem">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
        <span>Nonaktifkan Kendaraan</span>
    </button>
</div>

{{-- ========================================================================= --}}
{{-- REUSABLE VEHICLE MANAGEMENT ACTION MODALS                                 --}}
{{-- ========================================================================= --}}
<div id="vehicleModalBackdrop" class="vehicle-modal-backdrop" hidden>
    <div class="vehicle-modal-dialog" id="vehicleModalDialog" role="dialog" aria-modal="true" aria-labelledby="vehicleModalTitle" tabindex="-1">
        {{-- Modal Header --}}
        <header class="vehicle-modal-header">
            <div class="modal-header-titles">
                <span class="modal-kicker" id="vehicleModalKicker">YZA AUTOMOTIVE SHOWROOM</span>
                <h2 class="modal-title" id="vehicleModalTitle">Detail Kendaraan</h2>
                <div class="modal-header-meta" id="vehicleModalMeta"></div>
            </div>
            <button type="button" class="vehicle-modal-close" onclick="closeVehicleModal()" aria-label="Tutup popup" title="Tutup (Esc)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </header>

        {{-- Modal Internal Tabs Bar (Optional) --}}
        <nav class="modal-nav-tabs" id="vehicleModalNavTabs" hidden></nav>

        {{-- Modal Body Content --}}
        <div class="vehicle-modal-body" id="vehicleModalBody">
            <div class="modal-loading-state" id="modalLoadingState">
                <div class="modal-spinner"></div>
                <span>Memuat data kendaraan...</span>
            </div>
        </div>

        {{-- Modal Footer --}}
        <footer class="vehicle-modal-footer" id="vehicleModalFooter">
            <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>
        </footer>
    </div>
</div>

@push('styles')
<style>
.admin-content:has(> .admin-vehicle-management-page) > .admin-page-heading {
    display: none !important;
}
/* ========================================================
   YZA AUTOMOTIVE — MANAJEMEN MOBIL & SHOWROOM STYLES
   Clean White Background, Navy Accents, YZA Red Highlights
   ======================================================== */
.admin-vehicle-management-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
    width: 100%;
}

/* Header */
.vehicle-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
    padding: 10px 0;
}
.vehicle-header-left {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.vehicle-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #64748b;
}
.vehicle-breadcrumb .breadcrumb-separator {
    color: #cbd5e1;
    font-size: 11px;
}
.vehicle-breadcrumb .breadcrumb-current {
    color: #0f172a;
    font-weight: 600;
}
.vehicle-title-group {
    display: flex;
    align-items: center;
    gap: 14px;
}
.vehicle-title-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #0f172a;
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.12);
}
.vehicle-heading {
    margin: 0;
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
}
.vehicle-subheading {
    margin: 4px 0 0;
    font-size: 13px;
    color: #64748b;
}
.btn-primary-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 44px;
    padding: 0 20px;
    border-radius: 10px;
    background: linear-gradient(135deg, #ef233c 0%, #d90429 100%);
    color: #ffffff;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    box-shadow: 0 4px 14px rgba(239, 35, 60, 0.25);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.btn-primary-action:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(239, 35, 60, 0.35);
    color: #ffffff;
}
.owner-readonly-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-size: 12px;
    font-weight: 700;
}

/* Alert */
.admin-alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 18px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
}
.admin-alert.is-success {
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
}

/* 4 Warehouse KPI Cards */
.vehicle-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}
.vehicle-kpi-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 20px;
    border-radius: 14px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    cursor: default;
}
.vehicle-kpi-card:hover {
    transform: none;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    border-color: #e2e8f0;
}
.kpi-icon-box {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 10px;
    flex-shrink: 0;
}
.vehicle-kpi-card.is-red .kpi-icon-box { background: #ffe4e6; color: #e11d48; }
.vehicle-kpi-card.is-green .kpi-icon-box { background: #dcfce7; color: #16a34a; }
.vehicle-kpi-card.is-amber .kpi-icon-box { background: #fef3c7; color: #d97706; }
.vehicle-kpi-card.is-blue .kpi-icon-box { background: #e0e7ff; color: #4338ca; }
.vehicle-kpi-card.is-cyan .kpi-icon-box { background: #cffafe; color: #0891b2; }
.vehicle-kpi-card.is-orange .kpi-icon-box { background: #ffedd5; color: #ea580c; }

.kpi-content {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.kpi-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: 0.03em;
}
.kpi-value {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
}
.kpi-subtext {
    font-size: 10px;
    color: #94a3b8;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.kpi-subtext.is-up {
    color: #16a34a;
    display: flex;
    align-items: center;
    gap: 3px;
}

/* Tabs Navigation Bar */
.vehicle-tabs-nav {
    display: flex;
    gap: 8px;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 0;
    margin-top: 4px;
}
.vehicle-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    border: 0;
    border-bottom: 3px solid transparent;
    background: transparent;
    font-size: 14px;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    margin-bottom: -2px;
    transition: color 0.15s ease, border-color 0.15s ease;
}
.vehicle-tab-btn:hover {
    color: #0f172a;
}
.vehicle-tab-btn.is-active {
    color: #ef233c;
    border-bottom-color: #ef233c;
}
.tab-badge {
    padding: 2px 8px;
    border-radius: 999px;
    background: #f1f5f9;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
}
.vehicle-tab-btn.is-active .tab-badge {
    background: #ffe4e6;
    color: #e11d48;
}

/* Main Card Container */
.vehicle-main-card {
    border-radius: 14px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}
.vehicle-tab-pane {
    display: none;
}
.vehicle-tab-pane.is-active {
    display: block;
}

/* Filter Toolbar */
.vehicle-filter-toolbar {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr auto;
    gap: 10px;
    padding: 16px 20px;
    border-bottom: 1px solid #edf2f7;
    background: #fafbfc;
    align-items: center;
}
.filter-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.filter-input, .filter-select {
    width: 100%;
    height: 40px;
    padding: 0 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 12px;
    color: #1e293b;
    background: #ffffff;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.filter-input:focus, .filter-select:focus {
    border-color: #ef233c;
    box-shadow: 0 0 0 3px rgba(239, 35, 60, 0.1);
}
.search-box svg {
    position: absolute;
    left: 12px;
    color: #94a3b8;
    pointer-events: none;
}
.search-box .filter-input {
    padding-left: 36px;
}
.select-chevron {
    position: absolute;
    right: 12px;
    color: #94a3b8;
    pointer-events: none;
}
.filter-actions-group {
    display: flex;
    gap: 8px;
}
.btn-filter-apply, .btn-filter-reset {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 40px;
    padding: 0 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    border: 0;
}
.btn-filter-apply {
    background: #ef233c;
    color: #ffffff;
}
.btn-filter-reset {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}

/* ========================================================
   TABLE LAYOUT & STICKY ACTION COLUMN
   ======================================================== */
.vehicle-table-responsive {
    overflow-x: auto;
    width: 100%;
    position: relative;
    scroll-behavior: smooth;
}
.vehicle-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 12px;
}
.vehicle-table th {
    padding: 12px 14px;
    background: #f8fafc;
    color: #475569;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}
.vehicle-table td {
    padding: 12px 14px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    vertical-align: middle;
    background: #ffffff;
}
.vehicle-table tr:hover td {
    background: #fafbfc;
}

/* Sticky Action Column on the Right */
.vehicle-table th.sticky-col {
    position: sticky;
    right: 0;
    z-index: 12;
    background: #f8fafc;
    box-shadow: -4px 0 8px rgba(15, 23, 42, 0.05);
}
.vehicle-table td.sticky-col {
    position: sticky;
    right: 0;
    z-index: 10;
    background: #ffffff;
    box-shadow: -4px 0 8px rgba(15, 23, 42, 0.05);
}
.vehicle-table tr:hover td.sticky-col {
    background: #fafbfc;
}

/* Vehicle cell */
.car-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}
.car-thumb-box {
    width: 60px;
    height: 42px;
    border-radius: 6px;
    background: #0f172a;
    overflow: hidden;
    flex-shrink: 0;
}
.car-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.car-meta {
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.car-title-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.car-title-btn {
    background: transparent;
    border: 0;
    padding: 0;
    color: #0f172a;
    cursor: pointer;
    text-align: left;
    font-size: 13px;
    font-weight: 700;
    transition: color 0.15s ease;
}
.car-title-btn:hover { color: #ef233c; }
.car-code-pill {
    padding: 2px 6px;
    border-radius: 4px;
    background: #f1f5f9;
    color: #475569;
    font-family: ui-monospace, SFMono-Regular, monospace;
    font-size: 10px;
    font-weight: 700;
}
.car-subtext {
    font-size: 11px;
    color: #64748b;
}

.vin-cell {
    display: flex;
    flex-direction: column;
    gap: 3px;
    font-family: ui-monospace, SFMono-Regular, monospace;
    font-size: 11px;
}
.vin-badge { color: #0f172a; }
.engine-badge { color: #64748b; }

.showroom-slug-badge {
    padding: 3px 8px;
    border-radius: 6px;
    background: #f1f5f9;
    color: #475569;
    font-size: 10px;
    font-weight: 700;
}

.orderable-unit-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 11px;
}
.orderable-unit-badge.is-available {
    background: #dcfce7;
    color: #15803d;
}
.orderable-unit-badge.is-empty {
    background: #fee2e2;
    color: #b91c1c;
}

.badge-status {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
}
.badge-status.is-available { background: #ecfdf5; color: #047857; }
.badge-status.is-booked { background: #fffbeb; color: #b45309; }
.badge-status.is-sold { background: #ffe4e6; color: #be123c; }
.badge-status.is-inactive { background: #f1f5f9; color: #64748b; }

/* Status Katalog Badge */
.badge-catalog-status {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
.badge-catalog-status.is-published {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}
.badge-catalog-status.is-unpublished {
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #cbd5e1;
}
.catalog-slug-pill {
    display: block;
    padding: 2px 6px;
    font-size: 9px;
    font-family: ui-monospace, SFMono-Regular, monospace;
    background: #f8fafc;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    margin-top: 3px;
    max-width: 110px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Stock Ready & Reserved & Pre-Order Badges */
.stock-ready-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 11px;
}
.stock-ready-badge.is-ready {
    background: #dcfce7;
    color: #15803d;
}
.stock-ready-badge.is-zero {
    background: #fee2e2;
    color: #b91c1c;
}
.reserved-count-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 999px;
    font-weight: 700;
    font-size: 11px;
    background: #fef3c7;
    color: #b45309;
}
.preorder-count-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
}
.preorder-count-badge.is-active {
    padding: 3px 9px;
    border-radius: 999px;
    font-weight: 700;
    background: #cffafe;
    color: #0891b2;
    border: 1px solid #a5f3fc;
    text-decoration: none;
    transition: transform 0.12s ease, background 0.12s ease;
}
.preorder-count-badge.is-active:hover {
    transform: translateY(-1px);
    background: #a5f3fc;
}
.preorder-count-badge.is-zero {
    color: #94a3b8;
}

/* Ketersediaan Badge */
.badge-availability {
    display: inline-block;
    padding: 4px 9px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
}
.badge-availability.is-ready {
    background: #ecfdf5;
    color: #047857;
}
.badge-availability.is-exhausted {
    background: #fef3c7;
    color: #b45309;
}
.badge-availability.is-virtual {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}
.badge-availability.is-booked {
    background: #fffbeb;
    color: #b45309;
}
.badge-availability.is-sold {
    background: #ffe4e6;
    color: #be123c;
}
.badge-availability.is-inactive {
    background: #f1f5f9;
    color: #64748b;
}
.badge-availability.is-other {
    background: #f1f5f9;
    color: #64748b;
}

/* Context Banners */
.preorder-context-banner, .catalog-filter-context-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 16px;
    border-radius: 10px;
    margin-bottom: 14px;
    font-size: 12px;
    font-weight: 600;
}
.preorder-context-banner {
    background: #ecfeff;
    border: 1px solid #a5f3fc;
    color: #0e7490;
}
.catalog-filter-context-banner {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
}
.context-banner-text {
    display: flex;
    align-items: center;
    gap: 8px;
}
.btn-context-back {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 6px;
    background: #ffffff;
    color: #0e7490;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    border: 1px solid #a5f3fc;
    transition: all 0.12s ease;
}
.btn-context-back:hover {
    background: #0891b2;
    color: #ffffff;
}

/* Sublines & Hints */
.car-vin-subline {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 10px;
    color: #64748b;
    font-family: ui-monospace, SFMono-Regular, monospace;
    margin-top: 2px;
}
.car-vin-subline .vin-separator {
    color: #cbd5e1;
}
.car-virtual-hint {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    font-weight: 700;
    color: #2563eb;
    margin-top: 2px;
}
.is-virtual-model-row td {
    background: #fafbfc;
}
.is-virtual-model-row:hover td {
    background: #f1f5f9;
}
.btn-row-action.is-add-unit {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    text-decoration: none;
}
.btn-row-action.is-add-unit:hover {
    background: #dbeafe;
    color: #1e40af;
}
.btn-row-action.is-preorder {
    background: #cffafe;
    color: #0891b2;
    border: 1px solid #a5f3fc;
    text-decoration: none;
}
.btn-row-action.is-preorder:hover {
    background: #a5f3fc;
    color: #0e7490;
}

/* Single Three-Dot Action Button & Wrapper */
.action-single-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-more-options {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #ffffff;
    cursor: pointer;
    color: #475569;
    transition: all 0.15s ease;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
.btn-more-options:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #0f172a;
    box-shadow: 0 2px 4px rgba(15, 23, 42, 0.08);
    transform: translateY(-1px);
}
.btn-more-options[aria-expanded="true"] {
    background: #f1f5f9;
    border-color: #94a3b8;
    color: #0f172a;
}

/* Floating Fixed-Positioning Action Dropdown */
.floating-action-dropdown-menu {
    position: fixed;
    z-index: 9999;
    width: 230px;
    padding: 6px;
    border-radius: 12px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.12), 0 8px 10px -6px rgba(15, 23, 42, 0.08);
    display: flex;
    flex-direction: column;
    gap: 3px;
    opacity: 0;
    transform: translateY(-4px) scale(0.98);
    transition: opacity 150ms cubic-bezier(0.16, 1, 0.3, 1), transform 150ms cubic-bezier(0.16, 1, 0.3, 1);
    pointer-events: none;
}
.floating-action-dropdown-menu.is-open {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: auto;
}
.floating-action-dropdown-menu.is-flipped {
    transform: translateY(4px) scale(0.98);
}
.floating-action-dropdown-menu.is-flipped.is-open {
    transform: translateY(0) scale(1);
}
.floating-action-dropdown-menu[hidden] {
    display: none;
}
.floating-action-dropdown-menu .dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 9px 12px;
    border-radius: 8px;
    border: 0;
    background: transparent;
    color: #1e293b;
    font-size: 13px;
    font-weight: 600;
    text-align: left;
    cursor: pointer;
    transition: background 0.12s ease, color 0.12s ease;
}
.floating-action-dropdown-menu .dropdown-item svg {
    flex-shrink: 0;
    color: #64748b;
    transition: color 0.12s ease;
}
.floating-action-dropdown-menu .dropdown-item:hover,
.floating-action-dropdown-menu .dropdown-item:focus {
    background: #f8fafc;
    color: #0f172a;
    outline: none;
}
.floating-action-dropdown-menu .dropdown-item:hover svg,
.floating-action-dropdown-menu .dropdown-item:focus svg {
    color: #0f172a;
}
.floating-action-dropdown-menu .dropdown-item.has-badge {
    justify-content: flex-start;
}
.floating-action-dropdown-menu .dropdown-badge {
    margin-left: auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 2px 7px;
    border-radius: 999px;
    background: #e0f2fe;
    color: #0284c7;
    font-size: 11px;
    font-weight: 700;
}
.floating-action-dropdown-menu .dropdown-divider {
    height: 1px;
    background: #f1f5f9;
    margin: 4px 6px;
}
.floating-action-dropdown-menu .dropdown-item.is-danger {
    color: #dc2626;
}
.floating-action-dropdown-menu .dropdown-item.is-danger svg {
    color: #ef4444;
}
.floating-action-dropdown-menu .dropdown-item.is-danger:hover,
.floating-action-dropdown-menu .dropdown-item.is-danger:focus {
    background: #fef2f2;
    color: #b91c1c;
}
.floating-action-dropdown-menu .dropdown-item.is-danger:hover svg,
.floating-action-dropdown-menu .dropdown-item.is-danger:focus svg {
    color: #dc2626;
}

/* Table Footer */
.vehicle-table-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: #fafbfc;
    border-top: 1px solid #e2e8f0;
    font-size: 12px;
}
.footer-controls {
    display: flex;
    align-items: center;
    gap: 16px;
}
.per-page-select {
    height: 34px;
    padding: 0 10px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    font-size: 12px;
}

/* TAB 2: KATALOG & SHOWROOM */
.tab-section-intro {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 20px 24px;
    border-bottom: 1px solid #edf2f7;
    background: #fafbfc;
}
.intro-title {
    margin: 0;
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
}
.intro-desc {
    margin: 4px 0 0;
    font-size: 13px;
    color: #64748b;
}
.intro-badge {
    font-size: 11px;
    color: #475569;
    background: #f1f5f9;
    padding: 6px 12px;
    border-radius: 8px;
    white-space: nowrap;
}

.showroom-card-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    padding: 24px;
}
.showroom-car-card {
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
}
.showroom-car-card:hover {
    transform: translateY(-3px);
    border-color: #cbd5e1;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
}
.showroom-media {
    position: relative;
    height: 196px;
    background: #0d1420;
    overflow: hidden;
}
.showroom-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}
.showroom-car-card:hover .showroom-media img {
    transform: scale(1.04);
}
.showroom-media-gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(15, 23, 42, 0.65) 0%, rgba(15, 23, 42, 0) 48%);
    pointer-events: none;
    z-index: 1;
}
.showroom-media-badges {
    position: absolute;
    top: 12px;
    left: 12px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    z-index: 2;
}
.showroom-media-code {
    position: absolute;
    bottom: 10px;
    left: 12px;
    z-index: 2;
    padding: 3px 8px;
    border-radius: 6px;
    background: rgba(15, 23, 42, 0.72);
    backdrop-filter: blur(4px);
    color: #e2e8f0;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.03em;
}
.showroom-badge {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.28);
}
.showroom-badge.is-available { background: #10b981; color: #ffffff; }
.showroom-badge.is-booked { background: #f59e0b; color: #ffffff; }
.showroom-badge.is-sold { background: #ef233c; color: #ffffff; }
.showroom-badge.is-inactive { background: #64748b; color: #ffffff; }
.showroom-badge.is-highlight { background: #8b5cf6; color: #ffffff; }
.showroom-badge.is-condition { background: rgba(15, 23, 42, 0.85); color: #ffffff; }

.showroom-body {
    padding: 16px 18px 18px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.showroom-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 10px;
}
.showroom-title-wrap {
    min-width: 0;
    flex: 1;
}
.showroom-title {
    margin: 0;
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.showroom-sub {
    margin: 2px 0 0;
    font-size: 11px;
    font-weight: 500;
    color: #64748b;
}
.showroom-price-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin: 0 0 12px;
}
.showroom-price {
    font-size: 17px;
    font-weight: 800;
    color: #ef233c;
    font-variant-numeric: tabular-nums;
}
.showroom-stock-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 9px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}
.showroom-stock-pill .stock-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}
.showroom-stock-pill.is-stock { background: #ecfdf5; color: #047857; }
.showroom-stock-pill.is-nostock { background: #fef2f2; color: #b91c1c; }

.showroom-color-panel {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 12px;
    margin: 0 0 12px;
}
.showroom-color-panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 8px;
}
.color-panel-label {
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
}
.color-panel-active {
    font-size: 11px;
    font-weight: 700;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.showroom-color-picker {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin: 0;
}
.showroom-color-dots-group {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
}
.showroom-color-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 24px;
    padding: 0 10px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
    cursor: pointer;
    transition: all 0.15s ease;
}
.showroom-color-chip:hover {
    border-color: #94a3b8;
    color: #0f172a;
}
.showroom-color-chip.is-active {
    background: #0f172a;
    border-color: #0f172a;
    color: #ffffff;
}
.showroom-color-dot {
    width: 24px;
    height: 24px;
    padding: 2px;
    border-radius: 999px;
    border: 1.5px solid #cbd5e1;
    background: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    position: relative;
    transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
}
.showroom-color-dot:hover {
    transform: scale(1.1);
    border-color: #64748b;
}
.showroom-color-dot.is-active {
    border-color: #ef233c;
    box-shadow: 0 0 0 2px rgba(239, 35, 60, 0.2);
    transform: scale(1.06);
}
.showroom-color-dot.is-empty .dot-swatch {
    opacity: 0.35;
}
.showroom-color-dot .dot-swatch {
    width: 100%;
    height: 100%;
    border-radius: 999px;
    border: 1px solid rgba(15, 23, 42, 0.14);
    display: block;
}

.showroom-spec-strip {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 10px;
}
.showroom-spec-strip .spec-sep {
    color: #cbd5e1;
}
.showroom-description {
    font-size: 12px;
    color: #64748b;
    line-height: 1.45;
    margin: 0 0 14px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.showroom-actions-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: auto;
    padding-top: 12px;
    border-top: 1px solid #f1f5f9;
}
.btn-showroom-detail, .btn-showroom-edit, .btn-showroom-preview {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    text-align: center;
    white-space: nowrap;
    cursor: pointer;
    border: 0;
}
.btn-showroom-detail {
    flex: 2;
    background: #ef233c;
    color: #ffffff;
    box-shadow: 0 3px 10px rgba(239, 35, 60, 0.2);
}
.btn-showroom-detail:hover { color: #ffffff; background: #d90429; }
.btn-showroom-edit {
    flex: 1;
    border: 1px solid #cbd5e1;
    color: #334155;
    background: #ffffff;
}
.btn-showroom-edit:hover { background: #f8fafc; color: #0f172a; }
.btn-showroom-preview {
    border: 1px solid #e2e8f0;
    color: #64748b;
    background: #f8fafc;
}

/* TAB 3: FOTO & WARNA */
.gallery-management-container {
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 28px;
}
.car-gallery-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #ffffff;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
}
.car-gallery-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 18px;
    flex-wrap: wrap;
}
.gallery-car-meta {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.gallery-car-code {
    font-family: ui-monospace, SFMono-Regular, monospace;
    font-size: 11px;
    font-weight: 700;
    color: #ef233c;
}
.gallery-car-name {
    margin: 0;
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
}
.gallery-car-name small {
    color: #64748b;
    font-weight: 600;
}
.gallery-color-distinction {
    display: flex;
    gap: 8px;
    margin-top: 4px;
    flex-wrap: wrap;
}
.badge-physical-color {
    padding: 4px 8px;
    border-radius: 6px;
    background: #e0f2fe;
    color: #0369a1;
    font-size: 11px;
}
.badge-showroom-status {
    padding: 4px 8px;
    border-radius: 6px;
    background: #f1f5f9;
    color: #475569;
    font-size: 11px;
}
.gallery-car-actions {
    display: flex;
    gap: 8px;
}
.btn-sm-ghost {
    padding: 6px 12px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
    background: #ffffff;
    cursor: pointer;
}
.btn-sm-ghost:hover { background: #f8fafc; color: #0f172a; }

/* 4 Sudut Visual Grid */
.visual-angles-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 20px;
}
.angle-card {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    background: #fafbfc;
}
.angle-view-box {
    height: 120px;
    background: #0f172a;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.angle-view-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.angle-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 12px;
    text-align: center;
    color: #94a3b8;
    font-size: 10px;
}
.angle-caption {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 10px;
    font-size: 11px;
    font-weight: 700;
    color: #334155;
    background: #ffffff;
}
.badge-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}
.badge-dot.is-active { background: #10b981; }
.badge-dot.is-inactive { background: #cbd5e1; }

/* Colors Section */
.car-colors-section {
    padding: 14px 16px;
    border-radius: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    margin-bottom: 20px;
}
.section-micro-title {
    margin: 0 0 10px;
    font-size: 12px;
    font-weight: 800;
    color: #0f172a;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.color-swatches-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.color-chip {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
}
.color-chip.is-default {
    border-color: #ef233c;
    background: #fff5f5;
}
.color-circle {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 1px solid rgba(0, 0, 0, 0.15);
}
.color-chip-info {
    display: flex;
    flex-direction: column;
    font-size: 11px;
}
.color-chip-info strong { color: #0f172a; }
.color-chip-info small { color: #64748b; font-family: monospace; font-size: 9px; }
.chip-tag-default {
    font-size: 9px;
    font-weight: 800;
    color: #ef233c;
    text-transform: uppercase;
}
.color-empty-notice {
    font-size: 12px;
    color: #64748b;
}

/* Uploaded Photos Grid */
.car-uploaded-photos-section {
    padding: 14px 16px;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
}
.uploaded-photos-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.btn-upload-file {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    background: #0f172a;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
}
.uploaded-thumbnails-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 10px;
}
.uploaded-photo-card {
    position: relative;
    height: 90px;
    border-radius: 8px;
    overflow: hidden;
    background: #0f172a;
    border: 2px solid transparent;
}
.uploaded-photo-card.is-primary {
    border-color: #ef233c;
}
.uploaded-photo-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.uploaded-photo-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    opacity: 0;
    transition: opacity 0.15s ease;
}
.uploaded-photo-card:hover .uploaded-photo-overlay {
    opacity: 1;
}
.badge-primary-photo {
    padding: 3px 6px;
    border-radius: 4px;
    background: #ef233c;
    color: #ffffff;
    font-size: 10px;
    font-weight: 800;
}
.btn-action-photo {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
    border: 0;
    cursor: pointer;
}
.btn-action-photo.set-primary { background: #ffffff; color: #0f172a; }
.btn-action-photo.delete { background: #fee2e2; color: #dc2626; }
.no-uploaded-photos {
    grid-column: 1 / -1;
    font-size: 12px;
    color: #94a3b8;
    padding: 10px 0;
}

.catalog-empty-full {
    padding: 48px;
    text-align: center;
    color: #64748b;
    font-size: 13px;
}

/* ========================================================
   REUSABLE PREMIUM ACTION MODAL STYLES
   ======================================================== */
.vehicle-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 1;
    transition: opacity 200ms ease-out;
}
.vehicle-modal-backdrop[hidden] {
    display: none;
    opacity: 0;
}
.vehicle-modal-dialog {
    position: relative;
    width: 100%;
    max-width: 900px;
    max-height: 88dvh;
    border-radius: 16px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 24px 48px -12px rgba(15, 23, 42, 0.25);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: translateY(0);
    transition: transform 200ms ease-out;
}
.vehicle-modal-dialog.is-compact {
    max-width: 520px;
}
.vehicle-modal-dialog.is-medium {
    max-width: 680px;
}

@media (prefers-reduced-motion: no-preference) {
    .vehicle-modal-backdrop:not([hidden]) .vehicle-modal-dialog {
        animation: modalSlideUp 200ms ease-out;
    }
}
@keyframes modalSlideUp {
    from { transform: translateY(8px); opacity: 0.85; }
    to { transform: translateY(0); opacity: 1; }
}

@keyframes modalSpin {
    to { transform: rotate(360deg); }
}

.vehicle-modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    padding: 20px 24px 16px;
    border-bottom: 1px solid #edf2f7;
    background: #ffffff;
}
.modal-header-titles {
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.modal-kicker {
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    color: #ef233c;
    letter-spacing: 0.08em;
}
.modal-title {
    margin: 0;
    font-size: 19px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.01em;
}
.modal-header-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 4px;
    font-size: 12px;
    color: #64748b;
}
.vehicle-modal-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #64748b;
    cursor: pointer;
    transition: background 0.12s ease, color 0.12s ease;
}
.vehicle-modal-close:hover {
    background: #f1f5f9;
    color: #0f172a;
}

/* Modal Nav Tabs */
.modal-nav-tabs {
    display: flex;
    padding: 0 24px;
    gap: 6px;
    border-bottom: 1px solid #e2e8f0;
    background: #fafbfc;
}
.modal-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 14px;
    border: 0;
    border-bottom: 2px solid transparent;
    background: transparent;
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    margin-bottom: -1px;
    transition: color 0.12s ease, border-color 0.12s ease;
}
.modal-tab-btn:hover { color: #0f172a; }
.modal-tab-btn.is-active {
    color: #ef233c;
    border-bottom-color: #ef233c;
}

/* Modal Body */
.vehicle-modal-body {
    padding: 20px 24px;
    overflow-y: auto;
    flex: 1;
    font-size: 13px;
    color: #334155;
}

/* Modal Footer */
.vehicle-modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 14px 24px;
    border-top: 1px solid #edf2f7;
    background: #fafbfc;
}
.btn-modal-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 38px;
    padding: 0 18px;
    border-radius: 8px;
    background: #ef233c;
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    border: 0;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(239, 35, 60, 0.2);
}
.btn-modal-primary:hover { background: #d90429; color: #ffffff; }
.btn-modal-danger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 38px;
    padding: 0 18px;
    border-radius: 8px;
    background: #dc2626;
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    border: 0;
    cursor: pointer;
}
.btn-modal-danger:hover { background: #b91c1c; }
.btn-modal-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 38px;
    padding: 0 16px;
    border-radius: 8px;
    background: #ffffff;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid #cbd5e1;
    cursor: pointer;
}
.btn-modal-secondary:hover { background: #f8fafc; color: #0f172a; }

/* Modal Panes Visibility & Content Segregation */
.modal-pane {
    display: none;
    animation: fadeInModalPane 150ms cubic-bezier(0.16, 1, 0.3, 1);
}
.modal-pane.is-active,
.modal-pane:not(.hidden) {
    display: block;
}
.modal-pane.hidden {
    display: none !important;
}
@keyframes fadeInModalPane {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-strip-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 10px;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    font-size: 12px;
    color: #334155;
    margin-bottom: 14px;
}
.modal-strip-summary strong {
    color: #0f172a;
}
.color-chip.is-interactive {
    cursor: pointer;
    transition: transform 0.12s ease, border-color 0.12s ease, box-shadow 0.12s ease;
}
.color-chip.is-interactive:hover {
    transform: translateY(-1px);
    border-color: #94a3b8;
}
.color-chip.is-interactive.is-selected {
    border-color: #ef233c;
    background: #fff5f5;
    box-shadow: 0 0 0 2px rgba(239, 35, 60, 0.2);
}
.gallery-active-preview {
    width: 100%;
    height: 220px;
    border-radius: 12px;
    overflow: hidden;
    background: #0f172a;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    border: 1px solid #e2e8f0;
}
.gallery-active-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Modal Content Layouts */
.modal-grid-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.modal-detail-hero {
    display: flex;
    gap: 18px;
    align-items: center;
    padding: 14px 18px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    margin-bottom: 18px;
}
.modal-detail-thumb {
    width: 140px;
    height: 90px;
    border-radius: 8px;
    overflow: hidden;
    background: #0f172a;
    flex-shrink: 0;
}
.modal-detail-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.modal-hero-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.modal-hero-price {
    font-size: 20px;
    font-weight: 800;
    color: #ef233c;
}
.modal-spec-dl {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px 16px;
    margin: 0;
}
.modal-spec-row {
    display: flex;
    flex-direction: column;
    padding: 8px 12px;
    border-radius: 8px;
    background: #f8fafc;
    border: 1px solid #edf2f7;
}
.modal-spec-row.is-full {
    grid-column: 1 / -1;
}
.modal-spec-row dt {
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
}
.modal-spec-row dd {
    margin: 3px 0 0;
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
}

/* Modal Form Controls */
.modal-form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-bottom: 14px;
}
.modal-form-label {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
}
.modal-form-label .required-mark { color: #ef233c; }
.modal-form-input, .modal-form-select, .modal-form-textarea {
    width: 100%;
    height: 38px;
    padding: 0 12px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    font-size: 13px;
    color: #1e293b;
    background: #ffffff;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.modal-form-textarea {
    height: 70px;
    padding: 8px 12px;
    resize: vertical;
}
.modal-form-input:focus, .modal-form-select:focus, .modal-form-textarea:focus {
    border-color: #ef233c;
    box-shadow: 0 0 0 3px rgba(239, 35, 60, 0.1);
}
.modal-field-error {
    font-size: 11px;
    color: #dc2626;
    margin-top: 3px;
}

/* Modal Info Callouts */
.modal-info-callout {
    display: flex;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 10px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1e40af;
    font-size: 12px;
    margin-bottom: 16px;
    align-items: center;
}
.modal-warning-callout {
    display: flex;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 10px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
    font-size: 12px;
    margin-bottom: 16px;
    align-items: center;
}

/* Timeline / History Table */
.modal-history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
.modal-history-table th {
    padding: 10px 12px;
    background: #f8fafc;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}
.modal-history-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    vertical-align: middle;
}

/* Pre-Order & Add Unit Modal Components */
.vehicle-modal-dialog.is-wide {
    max-width: 860px;
}
body.modal-open {
    overflow: hidden !important;
}
.preorder-modal-summary-bar {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 16px;
}
.summary-stat-box {
    padding: 12px 14px;
    border-radius: 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.stat-label {
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: 0.04em;
}
.stat-number {
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
}
.stat-badge {
    font-size: 12px;
    font-weight: 700;
    color: #0284c7;
}
.stat-note {
    font-size: 13px;
    font-weight: 800;
    color: #ef233c;
}
.preorder-empty-box {
    padding: 36px 20px;
    text-align: center;
    color: #64748b;
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.preorder-empty-box h4 {
    margin: 8px 0 0;
    color: #0f172a;
    font-size: 15px;
    font-weight: 700;
}
.preorder-empty-box p {
    margin: 0;
    font-size: 12px;
    max-width: 440px;
}
.po-code-tag {
    font-family: ui-monospace, SFMono-Regular, monospace;
    font-size: 11px;
    color: #0284c7;
    background: #e0f2fe;
    padding: 2px 6px;
    border-radius: 4px;
}
.po-member-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.po-member-code {
    font-family: ui-monospace, SFMono-Regular, monospace;
    font-size: 10px;
    color: #64748b;
}
.po-color-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    border-radius: 999px;
    background: #f1f5f9;
    font-size: 11px;
    font-weight: 600;
    color: #334155;
}
.btn-po-review {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 10px;
    border-radius: 6px;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.12s ease;
}
.btn-po-review:hover {
    background: #dbeafe;
    color: #1e40af;
    transform: translateY(-1px);
}
.btn-preorder-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #cbd5e1;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.12s ease;
}
.btn-preorder-back:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.preorder-review-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e2e8f0;
}
.preorder-card-box {
    padding: 16px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}
.card-box-title {
    margin: 0 0 12px;
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 6px;
}
.preorder-stock-status-box {
    padding: 14px 16px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}
.preorder-stock-status-box.is-available {
    background: #ecfdf5;
    border-color: #a7f3d0;
    color: #065f46;
}
.preorder-stock-status-box.is-waiting {
    background: #fffbeb;
    border-color: #fde68a;
    color: #92400e;
}
.status-box-header {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 700;
}
.btn-action-inline {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    background: #059669;
    color: #ffffff;
    border: 0;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.12s ease;
}
.btn-action-inline:hover {
    background: #047857;
}
.modal-form-input.has-error,
.modal-form-select.has-error,
.modal-form-textarea.has-error {
    border-color: #ef233c !important;
    background: #fff5f5 !important;
}
.form-field-error {
    color: #dc2626;
    font-size: 11px;
    font-weight: 600;
    margin-top: 3px;
    display: block;
}

@media (prefers-reduced-motion: reduce) {
    .vehicle-modal-backdrop,
    .vehicle-modal-dialog,
    .btn-row-action,
    .btn-primary-action,
    .modal-pane {
        animation: none !important;
        transition: none !important;
    }
}

/* Responsive */
@media (max-width: 1280px) {
    .vehicle-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .showroom-card-grid { grid-template-columns: repeat(2, 1fr); }
    .visual-angles-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 900px) {
    .vehicle-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .vehicle-filter-toolbar { grid-template-columns: 1fr; }
    .showroom-card-grid { grid-template-columns: 1fr; }
    .visual-angles-grid { grid-template-columns: 1fr; }
    .uploaded-thumbnails-grid { grid-template-columns: repeat(3, 1fr); }
    .modal-grid-2col { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .vehicle-header { flex-direction: column; align-items: flex-start; }
    .vehicle-kpi-grid { grid-template-columns: 1fr; }
    .vehicle-tabs-nav { flex-wrap: wrap; }
    .vehicle-modal-dialog { max-height: 94dvh; border-radius: 12px; }
}
/* ========================================================
   MODAL TAMBAH MOBIL BARU (5-STEP STRUCTURED UI)
   ======================================================== */
.create-car-sections {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.create-car-section {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    background: #ffffff;
}
.create-car-section-head {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 14px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f1f5f9;
}
.create-car-section-head .section-step {
    width: 24px;
    height: 24px;
    border-radius: 8px;
    background: #ef233c;
    color: #ffffff;
    font-size: 12px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.create-car-section-head h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 800;
    color: #0f172a;
}
.create-car-section-head p {
    margin: 2px 0 0;
    font-size: 11.5px;
    color: #64748b;
}
.rgb-picker-box {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 10px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #f8fafc;
    min-height: 42px;
    flex-wrap: wrap;
}
.rgb-picker-box input[type="color"] {
    width: 34px;
    height: 30px;
    border: 0;
    padding: 0;
    background: transparent;
    cursor: pointer;
    border-radius: 6px;
}
.rgb-picker-meta {
    display: flex;
    flex-direction: column;
    line-height: 1.25;
}
.rgb-picker-meta strong {
    font-size: 11.5px;
    color: #0f172a;
    font-family: monospace;
}
.rgb-picker-meta span {
    font-size: 10px;
    color: #64748b;
    font-family: monospace;
}
.rgb-quick-swatches {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-left: auto;
}
.rgb-quick-dot {
    width: 18px;
    height: 18px;
    border-radius: 999px;
    border: 1.5px solid rgba(15, 23, 42, 0.2);
    cursor: pointer;
    padding: 0;
    transition: transform 0.15s ease;
}
.rgb-quick-dot:hover {
    transform: scale(1.15);
}
.stock-stepper-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.stock-stepper-btn {
    width: 36px;
    height: 38px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    color: #0f172a;
    font-size: 16px;
    font-weight: 800;
    cursor: pointer;
}
.stock-stepper-btn:hover {
    background: #e2e8f0;
}
.stock-stepper-input {
    width: 90px !important;
    text-align: center;
    font-weight: 800;
}
.stock-stepper-hint {
    font-size: 11.5px;
    color: #64748b;
}
.angle-upload-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}
.angle-upload-card {
    border: 1.5px dashed #cbd5e1;
    border-radius: 10px;
    background: #f8fafc;
    padding: 8px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: 6px;
    transition: border-color 0.15s ease, background 0.15s ease;
}
.angle-upload-card:hover {
    border-color: #ef233c;
    background: #fff5f6;
}
.angle-file-input {
    display: none;
}
.angle-upload-preview {
    height: 82px;
    border-radius: 7px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    color: #94a3b8;
    font-size: 11px;
    font-weight: 600;
    overflow: hidden;
}
.angle-upload-preview.has-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.angle-upload-label {
    font-size: 11px;
    font-weight: 700;
    color: #334155;
    text-align: center;
}
.visibility-option-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}
.visibility-option-card {
    position: relative;
    cursor: pointer;
    display: block;
}
.visibility-option-card input[type="radio"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.visibility-card-body {
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    gap: 4px;
    height: 100%;
    transition: all 0.15s ease;
}
.visibility-card-body strong {
    font-size: 12.5px;
    font-weight: 800;
    color: #0f172a;
}
.visibility-card-body span {
    font-size: 11px;
    color: #64748b;
    line-height: 1.4;
}
.visibility-option-card input[type="radio"]:checked + .visibility-card-body {
    border-color: #ef233c;
    background: #fff5f6;
    box-shadow: 0 0 0 2px rgba(239, 35, 60, 0.14);
}
@media (max-width: 720px) {
    .angle-upload-grid { grid-template-columns: repeat(2, 1fr); }
    .visibility-option-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------
    // TAB SWITCHING (INVENTORI / KATALOG / FOTO)
    // -------------------------------------------------------------
    const tabButtons = document.querySelectorAll('.vehicle-tab-btn');
    const tabPanes = document.querySelectorAll('.vehicle-tab-pane');

    function activateTab(tabId, updateUrl = true) {
        tabButtons.forEach(btn => {
            const isActive = btn.getAttribute('data-tab-target') === tabId;
            btn.classList.toggle('is-active', isActive);
            btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        tabPanes.forEach(pane => {
            const isActive = pane.id === tabId;
            pane.classList.toggle('is-active', isActive);
        });

        if (updateUrl) {
            const shortName = tabId.replace('tab-', '');
            const url = new URL(window.location.href);
            url.searchParams.set('tab', shortName);
            window.history.replaceState({}, '', url.toString());
        }
    }

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-tab-target');
            activateTab(targetId, true);
        });
    });

    const urlParams = new URLSearchParams(window.location.search);
    const preorderContext = Number(urlParams.get('preorder'));
    if (Number.isInteger(preorderContext) && preorderContext > 0) {
        const back = document.createElement('a');
        back.className = 'btn-modal-secondary'; back.textContent = '← Kembali ke Pre-Order';
        back.href = '{{ route('admin.preorder-mobil.index') }}?review=' + preorderContext;
        document.querySelector('.admin-content')?.prepend(back);
    }
    const stockTarget = Number(urlParams.get('stock_mobil'));
    const requestedAction = urlParams.get('vehicle_action');
    if (Number.isInteger(stockTarget) && stockTarget > 0 && getCarPayload(stockTarget)) openVehicleActionModal(stockTarget, ['detail','stock','edit'].includes(requestedAction) ? requestedAction : 'stock');
    const queryTab = urlParams.get('tab');
    if (queryTab && ['inventori', 'katalog', 'foto'].includes(queryTab)) {
        activateTab('tab-' + queryTab, false);
    }

    // -------------------------------------------------------------
    // FLOATING 3-DOTS ACTION DROPDOWN (SINGLE THREE-DOT ACTION MENU)
    // -------------------------------------------------------------
    const floatingMenu = document.getElementById('floatingActionDropdown');
    let currentDropdownCarId = null;
    let currentDropdownTriggerBtn = null;

    function renderDropdownItems(car) {
        if (!floatingMenu) return;
        const isVirtual = Boolean(car.is_virtual);
        const poCount = Array.isArray(car.preorders) ? car.preorders.length : 0;
        const canManage = !isUserOwner;

        let html = '';

        if (!isVirtual) {
            // A. MOBIL READY / MEMILIKI UNIT FISIK
            // 1. Detail Kendaraan
            html += `
                <button type="button" class="dropdown-item" data-action="detail" role="menuitem">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    <span>Detail Kendaraan</span>
                </button>
            `;

            // 2. Kelola Stok (Hanya Staf Berwenang)
            if (canManage) {
                html += `
                    <button type="button" class="dropdown-item" data-action="stock" role="menuitem">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                        <span>Kelola Stok</span>
                    </button>
                `;
            }

            // 3. Edit Kendaraan (Hanya Staf Berwenang)
            if (canManage) {
                html += `
                    <button type="button" class="dropdown-item" data-action="edit" role="menuitem">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                        <span>Edit Kendaraan</span>
                    </button>
                `;
            }

            // 4. Galeri & Warna
            html += `
                <button type="button" class="dropdown-item" data-action="gallery" role="menuitem">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path></svg>
                    <span>Galeri &amp; Warna</span>
                </button>
            `;

            // 5. Permintaan Pre-Order (Hanya bila jumlah aktif > 0)
            if (poCount > 0) {
                html += `
                    <button type="button" class="dropdown-item has-badge" data-action="preorder" role="menuitem">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 8v4l3 3"></path><circle cx="12" cy="12" r="9"></circle></svg>
                        <span>Permintaan Pre-Order</span>
                        <span class="dropdown-badge">${poCount}</span>
                    </button>
                `;
            }

            // 6. Nonaktifkan Mobil (Hanya jika diizinkan dan mobil belum terjual)
            if (canManage && car.status !== 'terjual') {
                html += `
                    <div class="dropdown-divider"></div>
                    <button type="button" class="dropdown-item is-danger" data-action="deactivate" role="menuitem">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        <span>Nonaktifkan Mobil</span>
                    </button>
                `;
            }
        } else {
            // B. MOBIL TANPA UNIT FISIK (KATALOG SHOWROOM)
            // 1. Detail Katalog
            html += `
                <button type="button" class="dropdown-item" data-action="detail" role="menuitem">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    <span>Detail Katalog</span>
                </button>
            `;

            // 2. Tambah Unit Fisik (Hanya Staf Berwenang)
            if (canManage) {
                html += `
                    <button type="button" class="dropdown-item" data-action="add_unit" role="menuitem">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Tambah Unit Fisik</span>
                    </button>
                `;
            }

            // 3. Permintaan Pre-Order (N) jika terdapat permintaan aktif
            if (poCount > 0) {
                html += `
                    <button type="button" class="dropdown-item has-badge" data-action="preorder" role="menuitem">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 8v4l3 3"></path><circle cx="12" cy="12" r="9"></circle></svg>
                        <span>Permintaan Pre-Order (${poCount})</span>
                        <span class="dropdown-badge">${poCount}</span>
                    </button>
                `;
            }

            // 4. Edit Katalog (Hanya Staf Berwenang)
            if (canManage) {
                html += `
                    <button type="button" class="dropdown-item" data-action="edit" role="menuitem">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                        <span>Edit Katalog</span>
                    </button>
                `;
            }

            // 5. Galeri & Warna
            html += `
                <button type="button" class="dropdown-item" data-action="gallery" role="menuitem">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path></svg>
                    <span>Galeri &amp; Warna</span>
                </button>
            `;

            // 6. Nonaktifkan Model (Hanya Staf Berwenang)
            if (canManage) {
                html += `
                    <div class="dropdown-divider"></div>
                    <button type="button" class="dropdown-item is-danger" data-action="deactivate" role="menuitem">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        <span>Nonaktifkan Model</span>
                    </button>
                `;
            }
        }

        floatingMenu.innerHTML = html;

        // Pasang event listener untuk membuka modal
        floatingMenu.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function (e) {
                e.stopPropagation();
                const action = this.getAttribute('data-action');
                const targetCarId = currentDropdownCarId;
                hideFloatingDropdown();
                if (targetCarId && action) {
                    openVehicleActionModal(targetCarId, action);
                }
            });
        });
    }

    window.toggleDropdownFixed = function (button, carId) {
        if (!floatingMenu) return;

        if (!floatingMenu.hasAttribute('hidden') && currentDropdownCarId === carId) {
            hideFloatingDropdown();
            return;
        }

        const car = getCarPayload(carId);
        if (!car) {
            console.warn('Vehicle payload not found for ID:', carId);
            return;
        }

        currentDropdownCarId = carId;
        currentDropdownTriggerBtn = button;

        renderDropdownItems(car);

        floatingMenu.removeAttribute('hidden');
        button.setAttribute('aria-expanded', 'true');

        const rect = button.getBoundingClientRect();
        const menuWidth = floatingMenu.offsetWidth || 230;
        const menuHeight = floatingMenu.offsetHeight || 220;

        let left = rect.right - menuWidth;
        if (left < 10) left = 10;
        if (left + menuWidth > window.innerWidth - 10) {
            left = window.innerWidth - menuWidth - 10;
        }

        let top = rect.bottom + 6;
        let isFlipped = false;
        if (top + menuHeight > window.innerHeight - 10) {
            top = rect.top - menuHeight - 6;
            isFlipped = true;
            if (top < 10) top = 10;
        }

        floatingMenu.style.top = top + 'px';
        floatingMenu.style.left = left + 'px';
        floatingMenu.classList.toggle('is-flipped', isFlipped);

        requestAnimationFrame(() => {
            floatingMenu.classList.add('is-open');
        });
    };

    function hideFloatingDropdown() {
        if (!floatingMenu) return;
        floatingMenu.classList.remove('is-open');
        floatingMenu.setAttribute('hidden', '');
        if (currentDropdownTriggerBtn) {
            currentDropdownTriggerBtn.setAttribute('aria-expanded', 'false');
            currentDropdownTriggerBtn = null;
        }
        currentDropdownCarId = null;
    }

    document.addEventListener('click', function (e) {
        if (floatingMenu && !floatingMenu.hasAttribute('hidden')) {
            if (!floatingMenu.contains(e.target) && !e.target.closest('.btn-more-options')) {
                hideFloatingDropdown();
            }
        }
    });

    document.addEventListener('keydown', function (e) {
        if (!floatingMenu || floatingMenu.hasAttribute('hidden')) return;

        if (e.key === 'Escape') {
            const btn = currentDropdownTriggerBtn;
            hideFloatingDropdown();
            if (btn) btn.focus();
            return;
        }

        const items = Array.from(floatingMenu.querySelectorAll('.dropdown-item'));
        if (!items.length) return;

        const currentIndex = items.indexOf(document.activeElement);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            const nextIndex = (currentIndex + 1) % items.length;
            items[nextIndex].focus();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const prevIndex = (currentIndex - 1 + items.length) % items.length;
            items[prevIndex].focus();
        }
    });

    window.addEventListener('resize', hideFloatingDropdown);
    window.addEventListener('scroll', hideFloatingDropdown, true);

    // Check All Checkbox
    const checkAll = document.getElementById('checkAllInventory');
    const rowChecks = document.querySelectorAll('.inventory-row-check');
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            rowChecks.forEach(cb => cb.checked = checkAll.checked);
        });
    }
});

// -------------------------------------------------------------
// REUSABLE VEHICLE MANAGEMENT ACTION MODALS ENGINE
// -------------------------------------------------------------
let activeModalTriggerElement = null;
const isUserOwner = @json($isOwner);
let isModalFormDirty = false;

function resetModalFormDirty() {
    isModalFormDirty = false;
}

function markModalFormDirty() {
    isModalFormDirty = true;
}

function setupFormDirtyTracking(form) {
    if (!form) return;
    form.addEventListener('input', () => { isModalFormDirty = true; });
    form.addEventListener('change', () => { isModalFormDirty = true; });
}

function getCarPayload(carId) {
    const scriptEl = document.getElementById('car-data-' + carId);
    if (scriptEl) {
        try {
            return JSON.parse(scriptEl.textContent);
        } catch (e) {
            console.error('Failed to parse car payload', e);
        }
    }
    return null;
}

function updateLocalCarPayload(carId, updatedData) {
    const scriptEl = document.getElementById('car-data-' + carId);
    if (scriptEl) {
        const current = getCarPayload(carId) || {};
        const merged = Object.assign({}, current, updatedData);
        scriptEl.textContent = JSON.stringify(merged);
    }
}

function selectShowroomCardColor(btn) {
    const card = btn.closest('.showroom-car-card');
    if (!card) return;

    card.querySelectorAll('.showroom-color-chip, .showroom-color-dot').forEach(el => {
        el.classList.remove('is-active');
    });
    btn.classList.add('is-active');

    const mode = btn.dataset.colorMode || 'all';
    const label = btn.dataset.colorLabel || card.dataset.defaultColor || 'Warna —';
    const stock = parseInt(btn.dataset.colorStock || '0', 10) || 0;
    const colorImage = btn.dataset.colorImage || '';
    const defaultThumb = card.dataset.defaultThumb || '';

    card.dataset.selectedColor = mode === 'single' ? label : '';

    const colorSummary = card.querySelector('[data-role="selected-color-summary"]');
    if (colorSummary) {
        colorSummary.textContent = mode === 'single'
            ? `${label} (${stock} unit)`
            : `Semua (${stock} unit)`;
    }

    const colorChip = card.querySelector('[data-role="color-chip"]');
    if (colorChip) {
        colorChip.textContent = mode === 'single' ? label : (card.dataset.defaultColor || 'Warna —');
    }

    const imgEl = card.querySelector('[data-role="card-image"]');
    if (imgEl) {
        const nextSrc = mode === 'single' ? (colorImage || defaultThumb) : defaultThumb;
        if (nextSrc && imgEl.getAttribute('src') !== nextSrc) {
            imgEl.setAttribute('src', nextSrc);
        }
    }

    const badge = card.querySelector('[data-role="availability-badge"]');
    const status = (card.dataset.status || '').toLowerCase();
    const exists = card.dataset.exists === '1';
    if (badge && status !== 'dibooking' && status !== 'terjual') {
        badge.classList.remove('is-available', 'is-inactive');
        if (status === 'tersedia' && stock > 0) {
            badge.classList.add('is-available');
            badge.textContent = 'Tersedia';
        } else if (!exists || stock < 1) {
            badge.classList.add('is-inactive');
            badge.textContent = exists ? 'Stok Habis' : 'Belum Ada Unit Fisik';
        } else {
            badge.classList.add('is-inactive');
            badge.textContent = status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Stok Habis';
        }
    }
}

async function openVehicleActionModal(carId, action, preselectedColor = null) {
    activeModalTriggerElement = document.activeElement;
    let car = null;
    if (carId === 'new') {
        car = {
            id: 'new',
            kode: 'YZA-UNIT-' + Math.floor(1000 + Math.random() * 9000),
            catalog_key: '',
            merk: '',
            model: '',
            tipe: '',
            tahun: new Date().getFullYear(),
            warna: '',
            transmisi: 'automatic',
            bahan_bakar: 'bensin',
            nomor_rangka: '',
            nomor_mesin: '',
            kilometer: 0,
            harga_beli: 0,
            harga_jual: 0,
            harga_jual_formatted: 'Rp 0',
            kondisi: 'baru',
            status: 'tersedia',
            is_virtual: false,
            preorders: [],
            urls: {
                store: @json(route('admin.mobil.store'))
            }
        };
    } else {
        car = getCarPayload(carId);
    }
    if (!car) {
        alert('Data kendaraan tidak ditemukan.');
        return;
    }

    if (car.urls?.show && ['stock', 'gallery', 'history', 'colors'].includes(action)) {
        try {
            const response = await fetch(car.urls.show, {headers:{Accept:'application/json'}, cache:'no-store', credentials:'same-origin'});
            if (!response.ok) throw new Error('Stok terbaru belum dapat dimuat.');
            const data = await response.json(), state = data.availability || {}, colorList = data.color_stocks || [];
            const sumTotal = colorList.length ? colorList.reduce((acc, c) => acc + Number(c.stock_total || 0), 0) : Number(state.stock_total || 0);
            const sumReady = colorList.length ? colorList.reduce((acc, c) => acc + Number(c.stock || 0), 0) : Number(state.stock || 0);
            const sumReserved = colorList.length ? colorList.reduce((acc, c) => acc + Number(c.reserved_units || 0), 0) : Number(state.reserved_units || 0);
            const sumSold = colorList.length ? colorList.reduce((acc, c) => acc + Number(c.sold_units || 0), 0) : Number(state.sold_units || 0);
            Object.assign(car, {color_stocks:colorList, stock_total:sumTotal, stock_orderable:sumReady, reserved_units:sumReserved, sold_units:sumSold});
            car.histories = (data.histories || []).map(h => ({tanggal:h.created_at,jenis:h.jenis,jumlah:h.jumlah_perubahan,before_after:h.stock_before+' → '+h.stock_after,catatan:h.catatan}));
        } catch (error) { showNotificationToast(error.message, 'error'); return; }
    }

    const backdrop = document.getElementById('vehicleModalBackdrop');
    const dialog = document.getElementById('vehicleModalDialog');
    const kicker = document.getElementById('vehicleModalKicker');
    const title = document.getElementById('vehicleModalTitle');
    const meta = document.getElementById('vehicleModalMeta');
    const navTabs = document.getElementById('vehicleModalNavTabs');
    const body = document.getElementById('vehicleModalBody');
    const footer = document.getElementById('vehicleModalFooter');

    // Reset Dialog Classes
    dialog.classList.remove('is-compact', 'is-medium', 'is-wide');
    navTabs.setAttribute('hidden', '');
    navTabs.innerHTML = '';
    document.body.classList.add('modal-open');
    resetModalFormDirty();

    kicker.textContent = 'YZA AUTOMOTIVE · SHOWROOM MANAGEMENT';
    const statusText = car.status_label || (car.status ? car.status.toUpperCase() : 'AKTIF');
    meta.innerHTML = `<span><strong>${car.kode || 'UNIT BARU'}</strong></span> &bull; <span>${car.merk || 'Mobil'} ${car.model || ''} (${car.tahun || ''})</span> &bull; <span class="badge-status is-${car.status || 'available'}">${statusText}</span>`;

    switch (action) {
        case 'detail':
        case 'specs':
            renderDetailAndSpecsModal(car, action, { title, kicker, meta, navTabs, body, footer, dialog }, preselectedColor);
            break;
        case 'stock':
            renderStockManagementModal(car, { title, kicker, meta, navTabs, body, footer, dialog });
            break;
        case 'edit':
            renderEditVehicleModal(car, { title, kicker, meta, body, footer, dialog }, preselectedColor);
            break;
        case 'add_unit':
            renderAddUnitModal(car, { title, kicker, meta, body, footer, dialog }, preselectedColor);
            break;
        case 'preorder':
            renderPreorderModal(car, { title, kicker, meta, body, footer, dialog });
            break;
        case 'history':
            renderStockHistoryModal(car, { title, kicker, meta, body, footer, dialog });
            break;
        case 'gallery':
            renderGalleryAndColorsModal(car, { title, kicker, meta, navTabs, body, footer, dialog });
            break;
        case 'deactivate':
            renderDeactivateConfirmationModal(car, { title, kicker, meta, body, footer, dialog });
            break;
        default:
            console.warn('Unknown action:', action);
            return;
    }

    backdrop.removeAttribute('hidden');
    dialog.focus();
}

function closeVehicleModal(force = false) {
    if (!force && isModalFormDirty) {
        if (!confirm('Terdapat perubahan formulir yang belum disimpan. Yakin ingin menutup popup?')) {
            return false;
        }
    }
    resetModalFormDirty();
    const backdrop = document.getElementById('vehicleModalBackdrop');
    if (backdrop) {
        backdrop.setAttribute('hidden', '');
    }
    document.body.classList.remove('modal-open');
    if (activeModalTriggerElement) {
        activeModalTriggerElement.focus();
        activeModalTriggerElement = null;
    }
    return true;
}

// Global Escape Key Listener for Modals
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const backdrop = document.getElementById('vehicleModalBackdrop');
        if (backdrop && !backdrop.hasAttribute('hidden')) {
            closeVehicleModal();
        }
    }
});

// Close when clicking directly on backdrop
document.getElementById('vehicleModalBackdrop')?.addEventListener('click', function (e) {
    if (e.target === this) {
        closeVehicleModal();
    }
});

// -------------------------------------------------------------
// 1 & 4. DETAIL & SPESIFIKASI MODAL (PHASE 3 & 6)
// -------------------------------------------------------------
function resolveCarColorContext(car, preselectedColor) {
    if (!preselectedColor) return null;
    const needle = String(preselectedColor).trim().toLowerCase();
    return (car.color_stocks || []).find(c =>
        String(c.label || '').trim().toLowerCase() === needle ||
        String(c.slug || '').trim().toLowerCase() === needle
    ) || null;
}

function renderDetailAndSpecsModal(car, initialTab, dom, preselectedColor = null) {
    const selectedColorObj = resolveCarColorContext(car, preselectedColor);
    const displayColor = selectedColorObj ? selectedColorObj.label : (preselectedColor || car.warna || 'Standar');
    const selectedColorImages = (selectedColorObj && Array.isArray(selectedColorObj.images)) ? selectedColorObj.images : [];
    const selectedFrontImg = (selectedColorImages.find(i => i.type === 'front') || selectedColorImages[0] || {}).src || '';
    const displayThumb = selectedFrontImg || car.thumbnail;
    const displayStockTotal = selectedColorObj ? Number(selectedColorObj.stock_total ?? 0) : car.stock_total;
    const displayStockOrderable = selectedColorObj ? Number(selectedColorObj.stock ?? 0) : car.stock_orderable;
    const displayReservedUnits = selectedColorObj ? Number(selectedColorObj.reserved_units ?? 0) : car.reserved_units;
    const displaySoldUnits = selectedColorObj ? Number(selectedColorObj.sold_units ?? 0) : car.sold_units;
    const displayStatus = selectedColorObj
        ? (displayStockOrderable > 0 ? 'tersedia' : 'nonaktif')
        : car.status;
    const displayStatusLabel = selectedColorObj
        ? (displayStockOrderable > 0 ? 'TERSEDIA' : 'STOK HABIS')
        : (car.status_label ? car.status_label.toUpperCase() : car.status.toUpperCase());

    dom.title.textContent = 'Detail & Spesifikasi Kendaraan';
    dom.navTabs.removeAttribute('hidden');
    dom.navTabs.innerHTML = `
        <button type="button" class="modal-tab-btn ${initialTab === 'detail' ? 'is-active' : ''}" data-target="pane-detail-overview" aria-selected="${initialTab === 'detail' ? 'true' : 'false'}">Ringkasan</button>
        <button type="button" class="modal-tab-btn ${initialTab === 'specs' ? 'is-active' : ''}" data-target="pane-detail-specs" aria-selected="${initialTab === 'specs' ? 'true' : 'false'}">Spesifikasi Lengkap</button>
        <button type="button" class="modal-tab-btn" data-target="pane-detail-stock" aria-selected="false">Ketersediaan Stok</button>
    `;

    const vinDisplay = car.is_virtual
        ? '<span class="text-muted">Belum ada unit fisik terdaftar (Model Showcase Katalog)</span>'
        : (car.nomor_rangka || '<span class="text-muted">Identitas unit fisik belum terverifikasi</span>');
    const engineDisplay = car.is_virtual
        ? '<span class="text-muted">Belum ada unit fisik terdaftar</span>'
        : (car.nomor_mesin || '<span class="text-muted">Data nomor mesin belum dilengkapi</span>');

    dom.body.innerHTML = `
        {{-- Pane 1: Ringkasan --}}
        <div id="pane-detail-overview" class="modal-pane ${initialTab === 'detail' ? 'is-active' : 'hidden'}" style="${initialTab === 'detail' ? 'display:block;' : 'display:none;'}">
            <div class="modal-grid-2col" style="margin-bottom:16px;">
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px; display:flex; flex-direction:column; gap:12px;">
                    <div style="width:100%; height:180px; border-radius:10px; overflow:hidden; background:#0f172a;">
                        <img src="${displayThumb}" alt="${car.merk} ${car.model}" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                        <div class="modal-hero-price" style="font-size:18px;">${car.harga_jual_formatted}</div>
                        <div style="display:flex; gap:6px;">
                            <span class="showroom-badge is-${displayStatus}">${displayStatusLabel}</span>
                            <span class="showroom-badge is-condition">${car.kondisi.toUpperCase()}</span>
                        </div>
                    </div>
                </div>

                <div style="display:flex; flex-direction:column; gap:10px;">
                    <div class="modal-section-box" style="margin-bottom:0;">
                        <h4 style="margin:0 0 8px; font-size:16px; font-weight:800; color:#0f172a;">${car.merk} ${car.model}</h4>
                        <div style="color:#64748b; font-size:12px; margin-bottom:10px;">
                            Varian: <strong>${car.tipe || 'Varian Standar'}</strong> &bull; Tahun: <strong>${car.tahun}</strong> &bull; Warna: <strong>${colorStockEscape(displayColor)}</strong>
                        </div>
                        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                            <span class="car-code-pill" style="font-size:11px;">KODE: ${car.kode}</span>
                            ${car.showcase_slug ? `<span class="showroom-slug-badge" style="font-size:11px;">${car.showcase_slug}</span>` : ''}
                            ${car.is_limited_edition ? '<span class="showroom-badge is-highlight">LIMITED EDITION</span>' : ''}
                        </div>
                    </div>

                    <div class="modal-section-box" style="margin-bottom:0; flex:1;">
                        <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:6px;">Catatan Ringkas Kendaraan</div>
                        <p style="margin:0; font-size:12px; color:#334155; line-height:1.6;">
                            ${car.deskripsi || '<em class="text-muted">Tidak ada catatan deskripsi tambahan untuk unit kendaraan ini.</em>'}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pane 2: Spesifikasi Lengkap --}}
        <div id="pane-detail-specs" class="modal-pane ${initialTab === 'specs' ? 'is-active' : 'hidden'}" style="${initialTab === 'specs' ? 'display:block;' : 'display:none;'}">
            <h4 class="section-micro-title" style="margin-top:0;">Parameter Teknis Kendaraan</h4>
            <dl class="modal-spec-dl" style="margin-bottom:18px;">
                <div class="modal-spec-row">
                    <dt>Merek &amp; Model</dt>
                    <dd>${car.merk} ${car.model}</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Tipe / Varian</dt>
                    <dd>${car.tipe || 'Belum dilengkapi'}</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Tahun Pembuatan</dt>
                    <dd>${car.tahun || 'Belum dilengkapi'}</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Mesin / Konfigurasi</dt>
                    <dd>${car.tipe ? 'Spesifikasi Standar ' + car.tipe : 'Belum dilengkapi'}</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Kapasitas Mesin</dt>
                    <dd>Belum dilengkapi</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Transmisi</dt>
                    <dd>${car.transmisi ? car.transmisi.toUpperCase() : 'Belum dilengkapi'}</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Tenaga / Daya Kuda</dt>
                    <dd>Belum dilengkapi</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Tipe Bahan Bakar</dt>
                    <dd>${car.bahan_bakar ? car.bahan_bakar.toUpperCase() : 'Belum dilengkapi'}</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Jarak Tempuh (Kilometer)</dt>
                    <dd>${Number(car.kilometer).toLocaleString('id-ID')} km</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Warna Fisik Unit</dt>
                    <dd>${colorStockEscape(displayColor) || 'Belum dilengkapi'}</dd>
                </div>
            </dl>

            <h4 class="section-micro-title">Identitas Registrasi Unit (Dealer Tracking)</h4>
            <dl class="modal-spec-dl">
                <div class="modal-spec-row">
                    <dt>Nomor Rangka (VIN)</dt>
                    <dd style="font-family:monospace;">${vinDisplay}</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Nomor Mesin</dt>
                    <dd style="font-family:monospace;">${engineDisplay}</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Tanggal Masuk Dealer</dt>
                    <dd>${car.tanggal_masuk || '—'}</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Sumber Kendaraan</dt>
                    <dd>Dealer YZA Automotive</dd>
                </div>
            </dl>
        </div>

        {{-- Pane 3: Ketersediaan Stok --}}
        <div id="pane-detail-stock" class="modal-pane hidden" style="display:none;">
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:16px;">
                <div style="padding:14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; text-align:center;">
                    <div style="font-size:11px; color:#64748b; font-weight:700;">TOTAL STOK</div>
                    <div style="font-size:22px; font-weight:800; color:#0f172a;">${displayStockTotal}</div>
                </div>
                <div style="padding:14px; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px; text-align:center;">
                    <div style="font-size:11px; color:#047857; font-weight:700;">DAPAT DIBELI</div>
                    <div style="font-size:22px; font-weight:800; color:#047857;">${displayStockOrderable}</div>
                </div>
                <div style="padding:14px; background:#fef3c7; border:1px solid #fde68a; border-radius:10px; text-align:center;">
                    <div style="font-size:11px; color:#b45309; font-weight:700;">RESERVASI</div>
                    <div style="font-size:22px; font-weight:800; color:#b45309;">${displayReservedUnits}</div>
                </div>
                <div style="padding:14px; background:#f1f5f9; border:1px solid #cbd5e1; border-radius:10px; text-align:center;">
                    <div style="font-size:11px; color:#475569; font-weight:700;">TERJUAL</div>
                    <div style="font-size:22px; font-weight:800; color:#0f172a;">${displaySoldUnits}</div>
                </div>
            </div>

            <div class="modal-info-callout" style="margin-bottom:16px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <span>${car.is_virtual ? 'Model ini merupakan showcase resmi katalog APM digital dan saat ini belum memiliki unit fisik tercatat di showroom. Anda dapat mendaftarkan unit fisik baru melalui tombol Tambah Unit.' : `Menampilkan informasi stok untuk warna: <strong>${colorStockEscape(displayColor)}</strong> (${displayStockTotal} unit fisik tercatat).`}</span>
            </div>

            <h4 class="section-micro-title">Pesanan Member &amp; Reservasi Aktif</h4>
            ${car.active_orders && car.active_orders.length > 0
                ? `<div style="overflow-x:auto;"><table class="modal-history-table"><thead><tr><th>Nomor Pesanan</th><th>Nama Pembeli</th><th>Status Pesanan</th></tr></thead><tbody>${car.active_orders.map(o => `<tr><td><strong>${o.nomor}</strong></td><td>${o.nama}</td><td><span class="badge-status is-active">${o.status}</span></td></tr>`).join('')}</tbody></table></div>`
                : '<div style="padding:18px; background:#f8fafc; border:1px solid #edf2f7; border-radius:8px; text-align:center; color:#64748b;">Tidak ada pesanan member aktif yang mengikat unit ini saat ini.</div>'}
        </div>
    `;

    const preselectedColorAttr = preselectedColor ? `, '${colorStockEscape(preselectedColor).replace(/'/g, "\\'")}'` : '';
    function updateDetailFooter(activePaneId) {
        if (activePaneId === 'pane-detail-overview') {
            dom.footer.innerHTML = `
                <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>
                ${!isUserOwner && !car.is_virtual ? `<button type="button" class="btn-modal-secondary" onclick="openVehicleActionModal('${car.id}', 'gallery')">Kelola Galeri</button>` : ''}
                ${!isUserOwner ? (car.is_virtual
                    ? `<button type="button" class="btn-modal-primary" onclick="openVehicleActionModal('${car.id}', 'add_unit'${preselectedColorAttr})" style="background:#059669;">+ Tambah Unit ke Inventori</button>`
                    : `<button type="button" class="btn-modal-primary" onclick="openVehicleActionModal('${car.id}', 'edit'${preselectedColorAttr})">Edit Data Kendaraan</button>`
                ) : ''}
            `;
        } else if (activePaneId === 'pane-detail-specs') {
            dom.footer.innerHTML = `
                <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>
                <button type="button" class="btn-modal-secondary" onclick="switchDetailTab('pane-detail-overview')">Lihat Ringkasan</button>
            `;
        } else if (activePaneId === 'pane-detail-stock') {
            dom.footer.innerHTML = `
                <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>
                ${!isUserOwner ? `<button type="button" class="btn-modal-primary" onclick="openVehicleActionModal('${car.id}', 'add_unit'${preselectedColorAttr})">${car.is_virtual ? '+ Tambah Unit ke Inventori' : 'Kelola Stok Kendaraan'}</button>` : ''}
            `;
        }
    }

    window.switchDetailTab = function(targetId) {
        const btn = dom.navTabs.querySelector(`.modal-tab-btn[data-target="${targetId}"]`);
        if (btn) btn.click();
    };

    setupModalPaneTabs(dom.navTabs, dom.body, updateDetailFooter);
    updateDetailFooter(initialTab === 'specs' ? 'pane-detail-specs' : 'pane-detail-overview');
}

// -------------------------------------------------------------
// 2. MODAL MANAJEMEN STOK KENDARAAN (PHASE 4)
// -------------------------------------------------------------
function colorStockEscape(value) { const el = document.createElement('span'); el.textContent = String(value ?? ''); return el.innerHTML; }
function renderColorStockCards(car) {
    return '<section class="color-stock-summary"><h4>Stok per warna fisik</h4><div class="color-stock-grid">' + (car.color_stocks || []).map(c => '<article class="color-stock-card"><strong><i style="background:' + (/^#[0-9a-f]{6}$/i.test(c.swatch) ? c.swatch : '#cbd5e1') + '"></i>' + colorStockEscape(c.label) + '</strong><span class="color-stock-status">' + (c.stock > 0 ? 'Tersedia' : '× Stok Habis') + '</span><dl><div><dt>Fisik</dt><dd>' + Number(c.stock_total) + '</dd></div><div><dt>Reservasi</dt><dd>' + Number(c.reserved_units) + '</dd></div><div><dt>Ready</dt><dd>' + Number(c.stock) + '</dd></div></dl>' + (c.unverified_units > 0 ? '<small>' + Number(c.unverified_units) + ' stok agregat lama belum terverifikasi</small>' : '') + (!isUserOwner ? '<button type="button" class="btn-modal-secondary" style="margin-top:8px; width:100%; padding:6px 8px; font-size:11.5px;" data-car-id="' + colorStockEscape(car.id) + '" data-add-color="' + colorStockEscape(c.label) + '" onclick="openVehicleActionModal(this.dataset.carId, \'add_unit\', this.dataset.addColor)">+ Tambah Stok Warna Ini</button>' : '') + '</article>').join('') + '</div></section>';
}
function renderStockManagementModal(car, dom) {
    dom.title.textContent = 'Manajemen Stok Kendaraan';
    dom.dialog.classList.add('is-medium');
    dom.navTabs.removeAttribute('hidden');
    dom.navTabs.innerHTML = `
        <button type="button" class="modal-tab-btn is-active" data-target="pane-stock-overview" aria-selected="true">Ikhtisar Stok</button>
        <button type="button" class="modal-tab-btn" data-target="pane-stock-adjust" aria-selected="false">Penyesuaian Stok</button>
    `;

    const recentHistories = (car.histories || []).slice(0, 4);

    dom.body.innerHTML = `
        {{-- Pane 1: Ikhtisar Stok (Monitoring / Read-only) --}}
        <div id="pane-stock-overview" class="modal-pane is-active" style="display:block;">
            <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:12px; margin-bottom:16px;">
                <div style="padding:14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px;">
                    <div style="font-size:11px; color:#64748b; font-weight:700;">TOTAL STOK FISIK</div>
                    <div style="font-size:24px; font-weight:800; color:#0f172a;">${car.stock_total} <small style="font-size:12px; font-weight:500;">unit</small></div>
                </div>
                <div style="padding:14px; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px;">
                    <div style="font-size:11px; color:#047857; font-weight:700;">STOK DAPAT DIBELI</div>
                    <div style="font-size:24px; font-weight:800; color:#047857;">${car.stock_orderable} <small style="font-size:12px; font-weight:500;">unit</small></div>
                </div>
                <div style="padding:14px; background:#fef3c7; border:1px solid #fde68a; border-radius:10px;">
                    <div style="font-size:11px; color:#b45309; font-weight:700;">RESERVASI AKTIF (SPK/BOOKING)</div>
                    <div style="font-size:24px; font-weight:800; color:#b45309;">${car.reserved_units} <small style="font-size:12px; font-weight:500;">unit</small></div>
                </div>
                <div style="padding:14px; background:#f1f5f9; border:1px solid #cbd5e1; border-radius:10px;">
                    <div style="font-size:11px; color:#475569; font-weight:700;">UNIT TELAH TERJUAL</div>
                    <div style="font-size:24px; font-weight:800; color:#0f172a;">${car.sold_units} <small style="font-size:12px; font-weight:500;">unit</small></div>
                </div>
            </div>

            ${renderColorStockCards(car)}

            <div class="modal-info-callout" style="margin-bottom:16px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <span>Warna fisik unit terdaftar di inventori: <strong>${car.warna || 'Standar'}</strong>. Pengurangan stok tidak diizinkan melebihi ${car.reserved_units} unit yang sedang direservasi.</span>
            </div>

            <h4 class="section-micro-title">Riwayat Pergerakan Stok Terbaru</h4>
            ${recentHistories.length > 0 ? `
                <div style="overflow-x:auto;">
                    <table class="modal-history-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Jenis</th>
                                <th style="text-align:center;">Mutasi</th>
                                <th>Sebelum &rarr; Sesudah</th>
                                <th>Alasan / Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${recentHistories.map(h => `
                                <tr>
                                    <td style="font-size:11px; color:#64748b; white-space:nowrap;">${h.tanggal}</td>
                                    <td><strong>${h.jenis}</strong></td>
                                    <td style="text-align:center;"><span style="font-weight:700; color:${h.jumlah.toString().startsWith('-') ? '#dc2626' : '#16a34a'};">${h.jumlah} unit</span></td>
                                    <td style="font-family:monospace; font-size:11px;">${h.before_after}</td>
                                    <td style="color:#64748b;">${colorStockEscape(h.catatan)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            ` : `
                <div style="padding:18px; background:#f8fafc; border:1px solid #edf2f7; border-radius:8px; text-align:center; color:#64748b;">
                    Belum ada riwayat pergerakan stok tambahan selain inisialisasi awal.
                </div>
            `}
        </div>

        {{-- Pane 2: Form Penyesuaian Stok --}}
        <div id="pane-stock-adjust" class="modal-pane hidden" style="display:none;">
            ${isUserOwner ? `
                <div class="modal-warning-callout">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span>Role Owner hanya memiliki hak akses baca (read-only). Penyesuaian stok dinonaktifkan.</span>
                </div>
            ` : `
                <div class="modal-strip-summary">
                    <span>Stok Fisik Saat Ini: <strong>${car.stock_total} unit</strong></span>
                    <span>Reservasi Terikat: <strong>${car.reserved_units} unit</strong></span>
                    <span>Batas Minimum Stok Baru: <strong>${car.reserved_units} unit</strong></span>
                </div>

                <form id="stockAdjustForm" onsubmit="submitStockAdjust(event, ${car.id})">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content}">

                    <div class="modal-form-group">
                        <label class="modal-form-label" for="stok_baru">Total Stok Baru <span class="required-mark">*</span></label>
                        <input type="number" id="stok_baru" name="stok_baru" class="modal-form-input" min="${car.reserved_units}" value="${Number(car.stock_total) + Number(car.sold_units)}" readonly required>
                        <small style="color:#64748b; font-size:11px;">Rekonsiliasi mengikuti unit beridentitas nyata. Tambah stok melalui Tambah Unit Fisik; koreksi warna/status melalui Edit Unit. Unit terikat transaksi dilindungi.</small>
                    </div>

                    <div id="stockDeltaBox" style="padding:10px 14px; border-radius:8px; background:#f8fafc; border:1px solid #e2e8f0; margin-bottom:14px; font-size:12px; display:flex; justify-content:space-between; align-items:center;">
                        <span>Dampak Kuantitas: <strong id="stockDeltaValue" style="color:#64748b;">0 unit (Tidak ada perubahan)</strong></span>
                        <span>Estimasi Siap Jual: <strong id="stockImpactPreview" style="color:#047857;">${car.stock_orderable} unit</strong></span>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-form-label" for="alasan_preset">Pilihan Alasan Penyesuaian</label>
                        <select id="alasan_preset" class="modal-form-select" onchange="onStockReasonPresetChange(this)">
                            <option value="">-- Pilih Alasan Penyesuaian Standar --</option>
                            <option value="Restock unit baru dari dealer">Restock unit baru dari dealer</option>
                            <option value="Rekonsiliasi hasil stock opname fisik gudang">Rekonsiliasi hasil stock opname fisik gudang</option>
                            <option value="Pengurangan unit rusak / cacat pengiriman">Pengurangan unit rusak / cacat pengiriman</option>
                            <option value="Retur unit ke distributor / leasing">Retur unit ke distributor / leasing</option>
                            <option value="custom">Alasan khusus lainnya...</option>
                        </select>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-form-label" for="alasan">Keterangan Alasan Resmi <span class="required-mark">*</span></label>
                        <input type="text" id="alasan" name="alasan" class="modal-form-input" placeholder="Wajib diisi: Penjelasan penyesuaian inventori" required>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-form-label" for="catatan">Catatan Audit / Referensi Dokumen</label>
                        <textarea id="catatan" name="catatan" class="modal-form-textarea" placeholder="Opsional: Nomor Surat Jalan, Berita Acara, No PO, dll..."></textarea>
                    </div>

                    <div class="modal-form-group"><label class="modal-form-label">Bukti / nomor dokumen *</label><input name="bukti" class="modal-form-input" minlength="5" maxlength="500" required></div><div id="stockAdjustError" class="modal-field-error" style="display:none; margin-bottom:12px;"></div>

                    <div style="display:none;">
                        <button type="submit" id="btnSubmitStockAdjust">Submit</button>
                    </div>
                </form>
            `}
        </div>
    `;

    // Setup live calculation on stock input
    const stockInput = dom.body.querySelector('#stok_baru');
    const deltaDisplay = dom.body.querySelector('#stockDeltaValue');
    const impactPreview = dom.body.querySelector('#stockImpactPreview');
    if (stockInput && deltaDisplay) {
        const updateDelta = () => {
            const val = parseInt(stockInput.value, 10);
            if (isNaN(val)) return;
            const delta = val - car.stock_total;
            const newOrderable = Math.max(0, val - car.reserved_units);
            if (delta > 0) {
                deltaDisplay.innerHTML = `<span style="color:#16a34a; font-weight:700;">+${delta} unit (Penambahan Stok)</span>`;
            } else if (delta < 0) {
                deltaDisplay.innerHTML = `<span style="color:#dc2626; font-weight:700;">${delta} unit (Pengurangan Stok)</span>`;
            } else {
                deltaDisplay.innerHTML = `<span style="color:#64748b; font-weight:600;">0 unit (Tidak ada perubahan)</span>`;
            }
            if (impactPreview) {
                impactPreview.textContent = `${newOrderable} unit`;
            }
        };
        stockInput.addEventListener('input', updateDelta);
    }

    function updateStockFooter(activePaneId) {
        if (activePaneId === 'pane-stock-overview') {
            dom.footer.innerHTML = `
                <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>
                <button type="button" class="btn-modal-secondary" onclick="openVehicleActionModal(${car.id}, 'history')">Lihat Riwayat</button>
                ${!isUserOwner ? `<button type="button" class="btn-modal-primary" onclick="switchStockTab('pane-stock-adjust')">Sesuaikan Stok</button>` : ''}
            `;
        } else {
            dom.footer.innerHTML = `
                <button type="button" class="btn-modal-secondary" onclick="switchStockTab('pane-stock-overview')">Kembali ke Ikhtisar</button>
                ${!isUserOwner ? `<button type="submit" form="stockAdjustForm" class="btn-modal-primary" id="btnFooterStockAdjust">Simpan Penyesuaian</button>` : ''}
            `;
        }
    }

    window.switchStockTab = function(targetId) {
        const btn = dom.navTabs.querySelector(`.modal-tab-btn[data-target="${targetId}"]`);
        if (btn) btn.click();
    };

    window.onStockReasonPresetChange = function(selectEl) {
        const input = document.getElementById('alasan');
        if (!input) return;
        if (selectEl.value && selectEl.value !== 'custom') {
            input.value = selectEl.value;
        } else if (selectEl.value === 'custom') {
            input.value = '';
            input.focus();
        }
    };

    setupModalPaneTabs(dom.navTabs, dom.body, updateStockFooter);
    updateStockFooter('pane-stock-overview');
}

// Handler Submit Penyesuaian Stok
async function submitStockAdjust(e, carId) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('btnSubmitStockAdjust');
    const errBox = document.getElementById('stockAdjustError');
    errBox.style.display = 'none';

    const car = getCarPayload(carId);
    if (!car) return;

    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    const formData = new FormData(form);

    try {
        const response = await fetch(car.urls.stock_adjust, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        const data = await response.json();
        if (response.ok && data.success) {
            // Update local payload
            const newAvailability = data.availability || {};
            updateLocalCarPayload(carId, {
                stock_total: newAvailability.stock_total ?? car.stock_total,
                stock_orderable: newAvailability.stock ?? car.stock_orderable,
            });

            // Update row in table
            const row = document.getElementById('row-car-' + carId);
            if (row) {
                const stockCell = row.querySelector('.td-stock-physical strong') || row.querySelector('.td-stock strong');
                if (stockCell) stockCell.textContent = (newAvailability.stock_total ?? car.stock_total);
                const orderableCell = row.querySelector('.td-stock-ready .stock-ready-badge') || row.querySelector('.td-orderable .orderable-unit-badge');
                if (orderableCell) {
                    const count = Number(newAvailability.stock || 0);
                    orderableCell.textContent = count + ' unit';
                    orderableCell.classList.toggle('is-ready', count > 0);
                    orderableCell.classList.toggle('is-available', count > 0);
                    orderableCell.classList.toggle('is-zero', count <= 0);
                    orderableCell.classList.toggle('is-empty', count <= 0);
                }
                const availBadge = row.querySelector('.td-availability .badge-availability');
                if (availBadge) {
                    const count = Number(newAvailability.stock || 0);
                    if (count > 0) {
                        availBadge.className = 'badge-availability is-ready';
                        availBadge.textContent = 'Ready';
                    } else {
                        availBadge.className = 'badge-availability is-exhausted';
                        availBadge.textContent = 'Stok Habis';
                    }
                }
            }

            closeVehicleModal();
            showNotificationToast('Sukses: ' + data.message, 'success');
        } else {
            errBox.textContent = data.message || 'Terjadi kesalahan saat menyimpan stok.';
            errBox.style.display = 'block';
        }
    } catch (err) {
        errBox.textContent = 'Gagal menghubungi server: ' + err.message;
        errBox.style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Simpan Penyesuaian Stok';
    }
}

// -------------------------------------------------------------
// 3. MODAL EDIT DATA KENDARAAN (PHASE 5)
// -------------------------------------------------------------
function renderEditVehicleModal(car, dom, preselectedColor = null) {
    const selectedColorObj = resolveCarColorContext(car, preselectedColor);
    const displayColor = selectedColorObj ? selectedColorObj.label : (preselectedColor || car.warna || '');
    const initialHex = (selectedColorObj && /^#[0-9a-f]{6}$/i.test(selectedColorObj.swatch))
        ? selectedColorObj.swatch
        : '#ef233c';
    const r0 = parseInt(initialHex.slice(1, 3), 16);
    const g0 = parseInt(initialHex.slice(3, 5), 16);
    const b0 = parseInt(initialHex.slice(5, 7), 16);
    const displayTampilDi = car.tampil_di || 'keduanya';
    const brandOptions = @json($brands ?? []);
    const colorImages = (selectedColorObj && Array.isArray(selectedColorObj.images)) ? selectedColorObj.images : [];
    const getAngleSrc = (key) => {
        const fromColor = colorImages.find(i => i.type === key)?.src;
        if (fromColor) return fromColor;
        return car.angles?.[key]?.path || '';
    };

    dom.title.textContent = 'Edit Data Kendaraan';
    dom.dialog.classList.add('is-wide');

    if (isUserOwner) {
        dom.body.innerHTML = `
            <div class="modal-warning-callout">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>Role Owner hanya memiliki hak akses baca (read-only). Edit data kendaraan dinonaktifkan.</span>
            </div>
        `;
        dom.footer.innerHTML = `<button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>`;
        return;
    }

    const todayStr = new Date().toISOString().split('T')[0];

    dom.body.innerHTML = `
        <form id="editVehicleForm" onsubmit="submitEditVehicle(event, ${car.id})" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content}">
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="kode_mobil" value="${colorStockEscape(car.kode || '')}">
            <input type="hidden" name="nomor_rangka" value="${colorStockEscape(car.nomor_rangka || '')}">
            <input type="hidden" name="nomor_mesin" value="${colorStockEscape(car.nomor_mesin || '')}">
            <input type="hidden" name="alasan_stok" value="Pembaruan data kendaraan melalui Kelola Mobil">
            <input type="hidden" name="bukti_stok" value="UPD-${todayStr}">

            <div class="create-car-sections">
                {{-- 1. Brand & Identitas Mobil --}}
                <section class="create-car-section">
                    <div class="create-car-section-head">
                        <span class="section-step">1</span>
                        <div>
                            <h4>Brand &amp; Identitas Mobil</h4>
                            <p>Perbarui merek, nama model, tahun, dan harga showroom.</p>
                        </div>
                    </div>
                    <div class="modal-form-grid">
                        <div class="modal-form-group">
                            <label class="modal-form-label" for="edit_merk">Brand / Merek <span class="required-mark">*</span></label>
                            <input type="text" id="edit_merk" name="merk" list="editBrandSuggestionsList" class="modal-form-input" value="${colorStockEscape(car.merk || '')}" required>
                            <datalist id="editBrandSuggestionsList">
                                ${brandOptions.map(b => `<option value="${colorStockEscape(b)}"></option>`).join('')}
                            </datalist>
                            <div class="modal-field-error" data-error-field="merk"></div>
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-form-label" for="edit_model">Nama Mobil / Model <span class="required-mark">*</span></label>
                            <input type="text" id="edit_model" name="model" class="modal-form-input" value="${colorStockEscape(car.model || '')}" required>
                            <div class="modal-field-error" data-error-field="model"></div>
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-form-label" for="edit_tipe">Tipe / Varian</label>
                            <input type="text" id="edit_tipe" name="tipe" class="modal-form-input" value="${colorStockEscape(car.tipe || '')}">
                            <div class="modal-field-error" data-error-field="tipe"></div>
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-form-label" for="edit_tahun">Tahun <span class="required-mark">*</span></label>
                            <input type="number" id="edit_tahun" name="tahun" class="modal-form-input" value="${car.tahun || new Date().getFullYear()}" min="1980" max="${new Date().getFullYear() + 1}" required>
                            <div class="modal-field-error" data-error-field="tahun"></div>
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-form-label" for="edit_harga_beli">Harga Beli / Modal (Rp) <span class="required-mark">*</span></label>
                            <input type="number" step="0.01" id="edit_harga_beli" name="harga_beli" class="modal-form-input" value="${car.harga_beli || 0}" min="0" required>
                            <div class="modal-field-error" data-error-field="harga_beli"></div>
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-form-label" for="edit_harga_jual">Harga Jual Showroom (Rp) <span class="required-mark">*</span></label>
                            <input type="number" step="0.01" id="edit_harga_jual" name="harga_jual" class="modal-form-input" value="${car.harga_jual || 0}" min="0" required>
                            <div class="modal-field-error" data-error-field="harga_jual"></div>
                        </div>
                    </div>
                </section>

                {{-- 2. Spesifikasi Mobil --}}
                <section class="create-car-section">
                    <div class="create-car-section-head">
                        <span class="section-step">2</span>
                        <div>
                            <h4>Spesifikasi &amp; Status Mobil</h4>
                            <p>Atur kondisi, transmisi, bahan bakar, status, dan deskripsi.</p>
                        </div>
                    </div>
                    <div class="modal-form-grid">
                        <div class="modal-form-group">
                            <label class="modal-form-label" for="edit_kondisi">Kondisi <span class="required-mark">*</span></label>
                            <select id="edit_kondisi" name="kondisi" class="modal-form-select" required>
                                <option value="baru" ${car.kondisi === 'baru' ? 'selected' : ''}>Baru</option>
                                <option value="bekas" ${car.kondisi === 'bekas' ? 'selected' : ''}>Bekas</option>
                            </select>
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-form-label" for="edit_transmisi">Transmisi</label>
                            <select id="edit_transmisi" name="transmisi" class="modal-form-select">
                                <option value="automatic" ${car.transmisi === 'automatic' ? 'selected' : ''}>Automatic</option>
                                <option value="manual" ${car.transmisi === 'manual' ? 'selected' : ''}>Manual</option>
                                <option value="cvt" ${car.transmisi === 'cvt' ? 'selected' : ''}>CVT</option>
                                <option value="dct" ${car.transmisi === 'dct' ? 'selected' : ''}>DCT</option>
                                <option value="lainnya" ${car.transmisi === 'lainnya' ? 'selected' : ''}>Lainnya</option>
                            </select>
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-form-label" for="edit_bahan_bakar">Bahan Bakar</label>
                            <select id="edit_bahan_bakar" name="bahan_bakar" class="modal-form-select">
                                <option value="bensin" ${car.bahan_bakar === 'bensin' ? 'selected' : ''}>Bensin</option>
                                <option value="diesel" ${car.bahan_bakar === 'diesel' ? 'selected' : ''}>Diesel</option>
                                <option value="hybrid" ${car.bahan_bakar === 'hybrid' ? 'selected' : ''}>Hybrid</option>
                                <option value="listrik" ${car.bahan_bakar === 'listrik' ? 'selected' : ''}>Listrik (EV)</option>
                                <option value="lainnya" ${car.bahan_bakar === 'lainnya' ? 'selected' : ''}>Lainnya</option>
                            </select>
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-form-label" for="edit_kilometer">Kilometer (Odometer) <span class="required-mark">*</span></label>
                            <input type="number" id="edit_kilometer" name="kilometer" class="modal-form-input" value="${car.kilometer || 0}" min="0" required>
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-form-label" for="edit_status">Status Ketersediaan <span class="required-mark">*</span></label>
                            <select id="edit_status" name="status" class="modal-form-select" ${car.is_sold ? 'disabled' : ''} required>
                                <option value="tersedia" ${car.status === 'tersedia' ? 'selected' : ''}>Tersedia</option>
                                <option value="dibooking" ${car.status === 'dibooking' ? 'selected' : ''}>Dipesan</option>
                                <option value="dalam_pemeriksaan" ${car.status === 'dalam_pemeriksaan' ? 'selected' : ''}>Dalam Pemeriksaan</option>
                                <option value="terjual" ${car.status === 'terjual' ? 'selected' : ''}>Terjual</option>
                                <option value="nonaktif" ${car.status === 'nonaktif' ? 'selected' : ''}>Nonaktif</option>
                            </select>
                            ${car.is_sold ? '<small style="color:#64748b; font-size:10px;">Unit terjual statusnya terkunci.</small>' : ''}
                        </div>

                        <div class="modal-form-group" style="justify-content:center;">
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; font-weight:700; margin-top:16px;">
                                <input type="hidden" name="is_limited_edition" value="0">
                                <input type="checkbox" name="is_limited_edition" value="1" ${car.is_limited_edition ? 'checked' : ''} class="custom-checkbox">
                                <span>Tandai Sebagai Limited Edition</span>
                            </label>
                        </div>

                        <div class="modal-form-group" style="grid-column: 1 / -1;">
                            <label class="modal-form-label" for="edit_deskripsi">Deskripsi &amp; Catatan Spesifikasi</label>
                            <textarea id="edit_deskripsi" name="deskripsi" class="modal-form-textarea" rows="2" placeholder="Spesifikasi tambahan, catatan unit, riwayat perawatan...">${colorStockEscape(car.deskripsi || '')}</textarea>
                        </div>
                    </div>
                </section>

                {{-- 3. Pemilihan Warna (RGB) & Info Stok --}}
                <section class="create-car-section">
                    <div class="create-car-section-head">
                        <span class="section-step">3</span>
                        <div>
                            <h4>Pemilihan Warna (RGB) &amp; Stok</h4>
                            <p>Sesuaikan nama warna, kode RGB, atau tambah stok unit untuk warna ini.</p>
                        </div>
                    </div>
                    <div class="modal-form-grid">
                        <div class="modal-form-group">
                            <label class="modal-form-label" for="edit_warna">Nama Warna <span class="required-mark">*</span></label>
                            <input type="text" id="edit_warna" name="warna" class="modal-form-input" value="${colorStockEscape(displayColor)}" required>
                            <div class="modal-field-error" data-error-field="warna"></div>
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-form-label" for="editCarColorHex">Warna RGB <span class="required-mark">*</span></label>
                            <div class="rgb-picker-box">
                                <input type="color" id="editCarColorHex" name="warna_hex" value="${initialHex}" oninput="syncEditCarRgbPicker(this.value)">
                                <div class="rgb-picker-meta">
                                    <strong id="editCarRgbLabel">rgb(${r0}, ${g0}, ${b0})</strong>
                                    <span id="editCarHexLabel">${initialHex.toUpperCase()}</span>
                                </div>
                                <div class="rgb-quick-swatches">
                                    <button type="button" class="rgb-quick-dot" style="background:#ef233c;" title="Crimson Red" onclick="applyEditCarPresetColor('#ef233c', 'Crimson Red')"></button>
                                    <button type="button" class="rgb-quick-dot" style="background:#111827;" title="Obsidian Black" onclick="applyEditCarPresetColor('#111827', 'Obsidian Black')"></button>
                                    <button type="button" class="rgb-quick-dot" style="background:#f8fafc;" title="Pearl White" onclick="applyEditCarPresetColor('#f8fafc', 'Pearl White')"></button>
                                    <button type="button" class="rgb-quick-dot" style="background:#facc15;" title="Racing Yellow" onclick="applyEditCarPresetColor('#facc15', 'Racing Yellow')"></button>
                                    <button type="button" class="rgb-quick-dot" style="background:#1d4ed8;" title="Sapphire Blue" onclick="applyEditCarPresetColor('#1d4ed8', 'Sapphire Blue')"></button>
                                    <button type="button" class="rgb-quick-dot" style="background:#64748b;" title="Titanium Grey" onclick="applyEditCarPresetColor('#64748b', 'Titanium Grey')"></button>
                                </div>
                            </div>
                        </div>

                        <div class="modal-form-group" style="grid-column: 1 / -1;">
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px;">
                                <div style="font-size:12px; color:#334155;">
                                    Stok warna <strong>${colorStockEscape(displayColor || 'Standar')}</strong> saat ini:
                                    <strong style="color:#047857;">${selectedColorObj ? Number(selectedColorObj.stock ?? 0) : car.stock_orderable} unit tersedia</strong>
                                </div>
                                <button type="button" class="btn-modal-secondary" style="padding:6px 12px; font-size:11.5px;" onclick="openVehicleActionModal(${car.id}, 'add_unit', '${colorStockEscape(displayColor).replace(/'/g, "\\'")}')">+ Tambah Stok Warna Ini</button>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 4. Foto Sudut Kendaraan --}}
                <section class="create-car-section">
                    <div class="create-car-section-head">
                        <span class="section-step">4</span>
                        <div>
                            <h4>Foto Sudut Kendaraan</h4>
                            <p>Klik salah satu sudut untuk mengganti atau mengunggah foto baru.</p>
                        </div>
                    </div>
                    <div class="angle-upload-grid">
                        ${[
                            { key: 'front', label: 'Depan (Front 3/4)' },
                            { key: 'side', label: 'Samping (Side Profile)' },
                            { key: 'rear', label: 'Belakang (Rear 3/4)' },
                            { key: 'interior', label: 'Kabin / Interior' }
                        ].map(item => {
                            const existingSrc = getAngleSrc(item.key);
                            return `
                                <label class="angle-upload-card" for="edit_angle_file_${item.key}">
                                    <input type="file" id="edit_angle_file_${item.key}" name="foto_sudut[${item.key}]" accept="image/png,image/jpeg,image/webp" class="angle-file-input" onchange="previewEditCarAnglePhoto(this, '${item.key}')">
                                    <div class="angle-upload-preview ${existingSrc ? 'has-image' : ''}" id="edit_angle_preview_${item.key}">
                                        ${existingSrc
                                            ? `<img src="${existingSrc}" alt="${item.label}">`
                                            : `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg><span>Pilih Foto</span>`
                                        }
                                    </div>
                                    <div class="angle-upload-label">${item.label}</div>
                                </label>
                            `;
                        }).join('')}
                    </div>
                </section>

                {{-- 5. Opsi Penampilan --}}
                <section class="create-car-section">
                    <div class="create-car-section-head">
                        <span class="section-step">5</span>
                        <div>
                            <h4>Opsi Penampilan</h4>
                            <p>Tentukan di mana mobil ini akan ditampilkan kepada pengunjung atau member.</p>
                        </div>
                    </div>
                    <div class="visibility-option-grid">
                        <label class="visibility-option-card">
                            <input type="radio" name="tampil_di" value="keduanya" ${displayTampilDi === 'keduanya' ? 'checked' : ''}>
                            <div class="visibility-card-body">
                                <strong>Landing Page &amp; Katalog Member</strong>
                                <span>Tampil di halaman utama publik maupun katalog khusus member.</span>
                            </div>
                        </label>
                        <label class="visibility-option-card">
                            <input type="radio" name="tampil_di" value="landing_only" ${displayTampilDi === 'landing_only' ? 'checked' : ''}>
                            <div class="visibility-card-body">
                                <strong>Landing Page Saja</strong>
                                <span>Hanya ditampilkan di landing page / etalase publik website.</span>
                            </div>
                        </label>
                        <label class="visibility-option-card">
                            <input type="radio" name="tampil_di" value="member_only" ${displayTampilDi === 'member_only' ? 'checked' : ''}>
                            <div class="visibility-card-body">
                                <strong>Katalog Khusus Member</strong>
                                <span>Eksklusif hanya tampil di halaman katalog member yang login.</span>
                            </div>
                        </label>
                    </div>
                </section>
            </div>

            <div id="editVehicleGlobalError" class="modal-field-error" style="display:none; margin-top:12px; margin-bottom:8px;"></div>
        </form>
    `;

    dom.footer.innerHTML = `
        <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Batal</button>
        <button type="submit" form="editVehicleForm" class="btn-modal-primary" id="btnSubmitEditVehicle">Simpan Perubahan</button>
    `;

    setupFormDirtyTracking(document.getElementById('editVehicleForm'));
}

function syncEditCarRgbPicker(hex) {
    const clean = /^#[0-9a-f]{6}$/i.test(hex) ? hex : '#ef233c';
    const r = parseInt(clean.slice(1, 3), 16);
    const g = parseInt(clean.slice(3, 5), 16);
    const b = parseInt(clean.slice(5, 7), 16);
    const rgbEl = document.getElementById('editCarRgbLabel');
    const hexEl = document.getElementById('editCarHexLabel');
    if (rgbEl) rgbEl.textContent = `rgb(${r}, ${g}, ${b})`;
    if (hexEl) hexEl.textContent = clean.toUpperCase();
}

function applyEditCarPresetColor(hex, label) {
    const colorInput = document.getElementById('editCarColorHex');
    const nameInput = document.getElementById('edit_warna');
    if (colorInput) colorInput.value = hex;
    if (nameInput) nameInput.value = label;
    syncEditCarRgbPicker(hex);
}

function previewEditCarAnglePhoto(input, angleKey) {
    const box = document.getElementById(`edit_angle_preview_${angleKey}`);
    if (!box || !input.files || !input.files[0]) return;
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = function (ev) {
        box.innerHTML = `<img src="${ev.target.result}" alt="${angleKey}">`;
        box.classList.add('has-image');
    };
    reader.readAsDataURL(file);
}
// Handler Submit Edit Kendaraan
async function submitEditVehicle(e, carId) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('btnSubmitEditVehicle');
    const globalErr = document.getElementById('editVehicleGlobalError');
    globalErr.style.display = 'none';

    // Clear individual field errors
    form.querySelectorAll('[data-error-field]').forEach(el => el.textContent = '');

    const car = getCarPayload(carId);
    if (!car) return;

    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    const formData = new FormData(form);

    try {
        const response = await fetch(car.urls.update, {
            method: 'POST', // with _method=PUT
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        const data = await response.json();
        if (response.ok && data.success) {
            const updated = data.mobil || {};
            updateLocalCarPayload(carId, {
                merk: updated.merk || car.merk,
                model: updated.model || car.model,
                tipe: updated.tipe || car.tipe,
                tahun: updated.tahun || car.tahun,
                warna: updated.warna || car.warna,
                kondisi: updated.kondisi || car.kondisi,
                status: updated.status || car.status,
                harga_jual: updated.harga_jual || car.harga_jual,
                harga_jual_formatted: 'Rp ' + Number(updated.harga_jual).toLocaleString('id-ID'),
                showcase_slug: updated.showcase_slug || car.showcase_slug,
                deskripsi: updated.deskripsi || car.deskripsi,
            });

            // Update row in table
            const row = document.getElementById('row-car-' + carId);
            if (row) {
                const titleEl = row.querySelector('.car-title');
                if (titleEl) titleEl.textContent = (updated.merk || car.merk) + ' ' + (updated.model || car.model);
                const subtextEl = row.querySelector('.car-subtext');
                if (subtextEl) subtextEl.innerHTML = `${updated.tipe || 'Varian Standar'} &bull; ${updated.tahun || car.tahun} &bull; ${updated.warna || car.warna}`;
                const priceEl = row.querySelector('.td-price strong');
                if (priceEl) priceEl.textContent = 'Rp ' + Number(updated.harga_jual).toLocaleString('id-ID');
                const statusBadge = row.querySelector('.td-status .badge-status');
                if (statusBadge) {
                    const st = (updated.status || car.status).toLowerCase();
                    statusBadge.className = 'badge-status is-' + st;
                    statusBadge.textContent = st.charAt(0).toUpperCase() + st.slice(1);
                }
            }

            closeVehicleModal();
            showNotificationToast('Sukses: ' + data.message, 'success');
        } else if (response.status === 422 && data.errors) {
            // Validation errors
            for (const [field, messages] of Object.entries(data.errors)) {
                const errEl = form.querySelector(`[data-error-field="${field}"]`);
                if (errEl) {
                    errEl.textContent = Array.isArray(messages) ? messages[0] : messages;
                }
            }
            globalErr.textContent = 'Periksa kembali isian formulir yang ditandai merah.';
            globalErr.style.display = 'block';
        } else {
            globalErr.textContent = data.message || 'Gagal menyimpan perubahan kendaraan.';
            globalErr.style.display = 'block';
        }
    } catch (err) {
        globalErr.textContent = 'Gagal menghubungi server: ' + err.message;
        globalErr.style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Simpan Perubahan';
    }
}

// -------------------------------------------------------------
// 3B. MODAL TAMBAH UNIT FISIK KE INVENTORI (CODEX REFINEMENT)
// -------------------------------------------------------------
function renderAddUnitModal(car, dom, preselectedColor = null) {
    const isNew = car.id === 'new';
    dom.title.textContent = isNew ? 'Daftarkan Unit Mobil Baru ke Inventori' : `Tambah Unit Fisik — ${car.merk} ${car.model}`;
    dom.kicker.textContent = 'INVENTORI FISIK DEALER · REGISTRASI UNIT';
    dom.dialog.classList.add('is-wide');

    if (isUserOwner) {
        dom.body.innerHTML = `
            <div class="modal-warning-callout">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>Role Owner hanya memiliki hak akses baca (read-only). Pendaftaran unit baru dinonaktifkan.</span>
            </div>
        `;
        dom.footer.innerHTML = `<button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>`;
        return;
    }

    const uniqueSuffix = Date.now().toString().slice(-5) + Math.floor(10 + Math.random() * 90);
    const defaultCode = (isNew || !car.kode || car.is_virtual)
        ? ('YZA-' + uniqueSuffix)
        : (car.kode.slice(0, 18) + '-U' + uniqueSuffix.slice(-4));
    const defaultVin = 'YZA-VIN-' + uniqueSuffix;
    const todayStr = new Date().toISOString().split('T')[0];
    const defaultBuyPrice = car.harga_beli || Math.round((car.harga_jual || 1000000000) * 0.85);

    if (!isNew) {
        dom.body.innerHTML = `
            <form id="addUnitVehicleForm" onsubmit="submitAddUnit(event, '${car.id}')" autocomplete="off">
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content}">
                <input type="hidden" name="catalog_identity" value="${colorStockEscape(car.catalog_key || ('mobil:' + car.id))}">
                <input type="hidden" name="merk" value="${colorStockEscape(car.merk || '')}">
                <input type="hidden" name="model" value="${colorStockEscape(car.model || '')}">
                <input type="hidden" name="tipe" value="${colorStockEscape(car.tipe || '')}">
                <input type="hidden" name="tahun" value="${car.tahun || new Date().getFullYear()}">
                <input type="hidden" name="kode_mobil" value="${colorStockEscape(defaultCode)}">
                <input type="hidden" name="nomor_rangka" value="${colorStockEscape(defaultVin)}">
                <input type="hidden" name="kilometer" value="0">
                <input type="hidden" name="transmisi" value="${colorStockEscape(car.transmisi || 'automatic')}">
                <input type="hidden" name="bahan_bakar" value="${colorStockEscape(car.bahan_bakar || 'bensin')}">
                <input type="hidden" name="status" value="tersedia">
                <input type="hidden" name="tanggal_masuk" value="${todayStr}">
                <input type="hidden" name="alasan_stok" value="Penambahan stok unit gudang">
                <input type="hidden" name="bukti_stok" value="Input cepat stok mobil gudang (${todayStr})">

                <div class="modal-form-grid">
                    <div class="modal-form-group" style="grid-column: 1 / -1;">
                        <label class="modal-form-label" for="add_unit_warna">Warna Fisik Unit <span class="required-mark">*</span></label>
                        <input type="text" id="add_unit_warna" name="warna" list="addUnitColorOptions" class="modal-form-input" value="${colorStockEscape(preselectedColor || car.warna || '')}" placeholder="Pilih atau ketik warna (contoh: Pearl White)" required>
                        <datalist id="addUnitColorOptions">
                            ${(car.color_stocks || []).map(c => `<option value="${colorStockEscape(c.label)}">Stok ready: ${Number(c.stock || 0)} unit</option>`).join('')}
                        </datalist>
                        <div class="modal-field-error" data-error-field="warna" style="display:none;"></div>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-form-label" for="add_unit_kondisi">Kondisi Unit <span class="required-mark">*</span></label>
                        <select id="add_unit_kondisi" name="kondisi" class="modal-form-select" required>
                            <option value="baru" ${car.kondisi === 'baru' ? 'selected' : ''}>Baru</option>
                            <option value="bekas" ${car.kondisi === 'bekas' ? 'selected' : ''}>Bekas</option>
                        </select>
                        <div class="modal-field-error" data-error-field="kondisi" style="display:none;"></div>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-form-label" for="add_unit_mesin">Nomor Mesin <span class="required-mark">*</span></label>
                        <input type="text" id="add_unit_mesin" name="nomor_mesin" class="modal-form-input" style="font-family:monospace; text-transform:uppercase;" placeholder="Contoh: VR38DETT-9823140" required>
                        <div class="modal-field-error" data-error-field="nomor_mesin" style="display:none;"></div>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-form-label" for="add_unit_harga_beli">Harga Beli (Rp) <span class="required-mark">*</span></label>
                        <input type="number" id="add_unit_harga_beli" name="harga_beli" class="modal-form-input" value="${defaultBuyPrice}" min="0" required>
                        <div class="modal-field-error" data-error-field="harga_beli" style="display:none;"></div>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-form-label" for="add_unit_harga_jual">Harga Jual (Rp) <span class="required-mark">*</span></label>
                        <input type="number" id="add_unit_harga_jual" name="harga_jual" class="modal-form-input" value="${car.harga_jual || ''}" min="0" required>
                        <div class="modal-field-error" data-error-field="harga_jual" style="display:none;"></div>
                    </div>
                </div>

                <div id="addUnitGlobalError" class="modal-field-error" style="display:none; margin-top:12px; margin-bottom:8px;"></div>
            </form>
        `;

        dom.footer.innerHTML = `
            <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Batal</button>
            <button type="submit" form="addUnitVehicleForm" class="btn-modal-primary" id="btnSubmitAddUnit" style="background:#059669; border-color:#059669;">+ Simpan Unit ke Inventori</button>
        `;
        setupFormDirtyTracking(document.getElementById('addUnitVehicleForm'));
        return;
    }

    dom.title.textContent = 'Tambah Mobil Baru';
    dom.kicker.textContent = 'YZA AUTOMOTIVE · KELOLA MOBIL & SHOWROOM';

    const defaultEngine = 'ENG-' + uniqueSuffix;
    const brandOptions = @json($brands ?? []);

    dom.body.innerHTML = `
        <form id="addUnitVehicleForm" onsubmit="submitAddUnit(event, 'new')" autocomplete="off" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content}">
            <input type="hidden" name="kode_mobil" value="${colorStockEscape(defaultCode)}">
            <input type="hidden" name="nomor_rangka" value="${colorStockEscape(defaultVin)}">
            <input type="hidden" name="nomor_mesin" value="${colorStockEscape(defaultEngine)}">
            <input type="hidden" name="status" value="tersedia">
            <input type="hidden" name="tanggal_masuk" value="${todayStr}">
            <input type="hidden" name="alasan_stok" value="Penambahan mobil baru melalui Kelola Mobil">
            <input type="hidden" name="bukti_stok" value="REG-${uniqueSuffix}">

            <div class="create-car-sections">
                <section class="create-car-section">
                    <div class="create-car-section-head">
                        <span class="section-step">1</span>
                        <div>
                            <h4>Brand &amp; Identitas Mobil</h4>
                            <p>Masukkan merek, nama model, kondisi, dan harga showroom.</p>
                        </div>
                    </div>
                    <div class="modal-form-grid">
                        <div class="modal-form-group">
                            <label class="modal-form-label" for="add_unit_merk">Brand / Merek <span class="required-mark">*</span></label>
                            <input type="text" id="add_unit_merk" name="merk" list="brandSuggestionsList" class="modal-form-input" placeholder="contoh: Toyota, Porsche, Ferrari" required>
                            <datalist id="brandSuggestionsList">
                                ${brandOptions.map(b => `<option value="${colorStockEscape(b)}"></option>`).join('')}
                            </datalist>
                            <div class="modal-field-error" data-error-field="merk" style="display:none;"></div>
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-form-label" for="add_unit_model">Nama Mobil / Model <span class="required-mark">*</span></label>
                            <input type="text" id="add_unit_model" name="model" class="modal-form-input" placeholder="contoh: GR Supra, 911 GT3 RS" required>
                            <div class="modal-field-error" data-error-field="model" style="display:none;"></div>
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-form-label" for="add_unit_tipe">Tipe / Varian</label>
                            <input type="text" id="add_unit_tipe" name="tipe" class="modal-form-input" placeholder="contoh: 3.0L Performance, Coupe">
                            <div class="modal-field-error" data-error-field="tipe" style="display:none;"></div>
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-form-label" for="add_unit_tahun">Tahun <span class="required-mark">*</span></label>
                            <input type="number" id="add_unit_tahun" name="tahun" class="modal-form-input" value="${new Date().getFullYear()}" min="1980" max="${new Date().getFullYear() + 1}" required>
                            <div class="modal-field-error" data-error-field="tahun" style="display:none;"></div>
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-form-label" for="add_unit_harga_beli">Harga Beli / Modal (Rp) <span class="required-mark">*</span></label>
                            <input type="number" id="add_unit_harga_beli" name="harga_beli" class="modal-form-input" placeholder="contoh: 1850000000" min="0" required>
                            <div class="modal-field-error" data-error-field="harga_beli" style="display:none;"></div>
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-form-label" for="add_unit_harga_jual">Harga Jual Showroom (Rp) <span class="required-mark">*</span></label>
                            <input type="number" id="add_unit_harga_jual" name="harga_jual" class="modal-form-input" placeholder="contoh: 2350000000" min="0" required>
                            <div class="modal-field-error" data-error-field="harga_jual" style="display:none;"></div>
                        </div>
                    </div>
                </section>

                <section class="create-car-section">
                    <div class="create-car-section-head">
                        <span class="section-step">2</span>
                        <div>
                            <h4>Spesifikasi Mobil</h4>
                            <p>Lengkapi detail kondisi, transmisi, bahan bakar, dan catatan.</p>
                        </div>
                    </div>
                    <div class="modal-form-grid">
                        <div class="modal-form-group">
                            <label class="modal-form-label" for="add_unit_kondisi">Kondisi <span class="required-mark">*</span></label>
                            <select id="add_unit_kondisi" name="kondisi" class="modal-form-select" required>
                                <option value="baru" selected>Baru</option>
                                <option value="bekas">Bekas</option>
                            </select>
                            <div class="modal-field-error" data-error-field="kondisi" style="display:none;"></div>
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-form-label" for="add_unit_transmisi">Transmisi <span class="required-mark">*</span></label>
                            <select id="add_unit_transmisi" name="transmisi" class="modal-form-select" required>
                                <option value="automatic" selected>Automatic</option>
                                <option value="manual">Manual</option>
                                <option value="cvt">CVT</option>
                                <option value="dct">DCT</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-form-label" for="add_unit_bahan_bakar">Bahan Bakar <span class="required-mark">*</span></label>
                            <select id="add_unit_bahan_bakar" name="bahan_bakar" class="modal-form-select" required>
                                <option value="bensin" selected>Bensin</option>
                                <option value="diesel">Diesel</option>
                                <option value="hybrid">Hybrid</option>
                                <option value="listrik">Listrik (EV)</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-form-label" for="add_unit_kilometer">Kilometer (Odometer) <span class="required-mark">*</span></label>
                            <input type="number" id="add_unit_kilometer" name="kilometer" class="modal-form-input" value="0" min="0" required>
                            <div class="modal-field-error" data-error-field="kilometer" style="display:none;"></div>
                        </div>

                        <div class="modal-form-group" style="grid-column: 1 / -1;">
                            <label class="modal-form-label" for="add_unit_deskripsi">Deskripsi &amp; Catatan Spesifikasi</label>
                            <textarea id="add_unit_deskripsi" name="deskripsi" class="modal-form-textarea" rows="2" placeholder="Contoh: Mesin 3.0L Twin-Turbo, fitur keselamatan aktif, interior Alcantara..."></textarea>
                        </div>
                    </div>
                </section>

                <section class="create-car-section">
                    <div class="create-car-section-head">
                        <span class="section-step">3</span>
                        <div>
                            <h4>Pemilihan Warna (RGB) &amp; Atur Stok</h4>
                            <p>Pilih warna beserta kode RGB dan tentukan jumlah stok awal unit.</p>
                        </div>
                    </div>
                    <div class="modal-form-grid">
                        <div class="modal-form-group">
                            <label class="modal-form-label" for="add_unit_warna">Nama Warna <span class="required-mark">*</span></label>
                            <input type="text" id="add_unit_warna" name="warna" class="modal-form-input" value="Crimson Red" placeholder="contoh: Pearl White, Racing Yellow" required>
                            <div class="modal-field-error" data-error-field="warna" style="display:none;"></div>
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-form-label" for="newCarColorHex">Warna RGB <span class="required-mark">*</span></label>
                            <div class="rgb-picker-box">
                                <input type="color" id="newCarColorHex" name="warna_hex" value="#ef233c" oninput="syncNewCarRgbPicker(this.value)">
                                <div class="rgb-picker-meta">
                                    <strong id="newCarRgbLabel">rgb(239, 35, 60)</strong>
                                    <span id="newCarHexLabel">#EF233C</span>
                                </div>
                                <div class="rgb-quick-swatches">
                                    <button type="button" class="rgb-quick-dot" style="background:#ef233c;" title="Crimson Red" onclick="applyNewCarPresetColor('#ef233c', 'Crimson Red')"></button>
                                    <button type="button" class="rgb-quick-dot" style="background:#111827;" title="Obsidian Black" onclick="applyNewCarPresetColor('#111827', 'Obsidian Black')"></button>
                                    <button type="button" class="rgb-quick-dot" style="background:#f8fafc;" title="Pearl White" onclick="applyNewCarPresetColor('#f8fafc', 'Pearl White')"></button>
                                    <button type="button" class="rgb-quick-dot" style="background:#facc15;" title="Racing Yellow" onclick="applyNewCarPresetColor('#facc15', 'Racing Yellow')"></button>
                                    <button type="button" class="rgb-quick-dot" style="background:#1d4ed8;" title="Sapphire Blue" onclick="applyNewCarPresetColor('#1d4ed8', 'Sapphire Blue')"></button>
                                    <button type="button" class="rgb-quick-dot" style="background:#64748b;" title="Titanium Grey" onclick="applyNewCarPresetColor('#64748b', 'Titanium Grey')"></button>
                                </div>
                            </div>
                        </div>

                        <div class="modal-form-group" style="grid-column: 1 / -1;">
                            <label class="modal-form-label" for="add_unit_stok_awal">Jumlah Stok Awal (Unit) <span class="required-mark">*</span></label>
                            <div class="stock-stepper-wrap">
                                <button type="button" class="stock-stepper-btn" onclick="stepNewCarStock(-1)">&minus;</button>
                                <input type="number" id="add_unit_stok_awal" name="stok_awal" class="modal-form-input stock-stepper-input" value="1" min="1" max="50" required>
                                <button type="button" class="stock-stepper-btn" onclick="stepNewCarStock(1)">+</button>
                                <span class="stock-stepper-hint">Setiap unit stok akan langsung tercatat di inventori warna ini.</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="create-car-section">
                    <div class="create-car-section-head">
                        <span class="section-step">4</span>
                        <div>
                            <h4>Foto Sudut Kendaraan</h4>
                            <p>Unggah foto dari 4 sudut utama untuk tampilan showroom &amp; galeri warna.</p>
                        </div>
                    </div>
                    <div class="angle-upload-grid">
                        ${[
                            { key: 'front', label: 'Depan (Front 3/4)' },
                            { key: 'side', label: 'Samping (Side Profile)' },
                            { key: 'rear', label: 'Belakang (Rear 3/4)' },
                            { key: 'interior', label: 'Kabin / Interior' }
                        ].map(item => `
                            <label class="angle-upload-card" for="angle_file_${item.key}">
                                <input type="file" id="angle_file_${item.key}" name="foto_sudut[${item.key}]" accept="image/png,image/jpeg,image/webp" class="angle-file-input" onchange="previewNewCarAnglePhoto(this, '${item.key}')">
                                <div class="angle-upload-preview" id="angle_preview_${item.key}">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    <span>Pilih Foto</span>
                                </div>
                                <div class="angle-upload-label">${item.label}</div>
                            </label>
                        `).join('')}
                    </div>
                </section>

                <section class="create-car-section">
                    <div class="create-car-section-head">
                        <span class="section-step">5</span>
                        <div>
                            <h4>Opsi Penampilan</h4>
                            <p>Tentukan di mana mobil ini akan ditampilkan kepada pengunjung atau member.</p>
                        </div>
                    </div>
                    <div class="visibility-option-grid">
                        <label class="visibility-option-card">
                            <input type="radio" name="tampil_di" value="keduanya" checked>
                            <div class="visibility-card-body">
                                <strong>Landing Page &amp; Katalog Member</strong>
                                <span>Tampil di halaman utama publik maupun katalog khusus member.</span>
                            </div>
                        </label>
                        <label class="visibility-option-card">
                            <input type="radio" name="tampil_di" value="landing_only">
                            <div class="visibility-card-body">
                                <strong>Landing Page Saja</strong>
                                <span>Hanya ditampilkan di landing page / etalase publik website.</span>
                            </div>
                        </label>
                        <label class="visibility-option-card">
                            <input type="radio" name="tampil_di" value="member_only">
                            <div class="visibility-card-body">
                                <strong>Katalog Khusus Member</strong>
                                <span>Eksklusif hanya tampil di halaman katalog member yang login.</span>
                            </div>
                        </label>
                    </div>
                </section>
            </div>

            <div id="addUnitGlobalError" class="modal-field-error" style="display:none; margin-top:12px; margin-bottom:8px;"></div>
        </form>
    `;

    dom.footer.innerHTML = `
        <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Batal</button>
        <button type="submit" form="addUnitVehicleForm" class="btn-modal-primary" id="btnSubmitAddUnit">+ Simpan &amp; Publikasikan Mobil</button>
    `;

    setupFormDirtyTracking(document.getElementById('addUnitVehicleForm'));
}

function syncNewCarRgbPicker(hex) {
    const clean = /^#[0-9a-f]{6}$/i.test(hex) ? hex : '#ef233c';
    const r = parseInt(clean.slice(1, 3), 16);
    const g = parseInt(clean.slice(3, 5), 16);
    const b = parseInt(clean.slice(5, 7), 16);
    const rgbEl = document.getElementById('newCarRgbLabel');
    const hexEl = document.getElementById('newCarHexLabel');
    if (rgbEl) rgbEl.textContent = `rgb(${r}, ${g}, ${b})`;
    if (hexEl) hexEl.textContent = clean.toUpperCase();
}

function applyNewCarPresetColor(hex, label) {
    const colorInput = document.getElementById('newCarColorHex');
    const nameInput = document.getElementById('add_unit_warna');
    if (colorInput) colorInput.value = hex;
    if (nameInput) nameInput.value = label;
    syncNewCarRgbPicker(hex);
}

function stepNewCarStock(delta) {
    const input = document.getElementById('add_unit_stok_awal');
    if (!input) return;
    const current = parseInt(input.value || '1', 10) || 1;
    input.value = Math.min(50, Math.max(1, current + delta));
}

function previewNewCarAnglePhoto(input, angleKey) {
    const box = document.getElementById(`angle_preview_${angleKey}`);
    if (!box || !input.files || !input.files[0]) return;
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = function (ev) {
        box.innerHTML = `<img src="${ev.target.result}" alt="${angleKey}">`;
        box.classList.add('has-image');
    };
    reader.readAsDataURL(file);
}
async function submitAddUnit(e, carId) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('btnSubmitAddUnit');
    const globalErr = document.getElementById('addUnitGlobalError');
    if (globalErr) {
        globalErr.style.display = 'none';
        globalErr.textContent = '';
    }

    // Reset inline field errors
    form.querySelectorAll('[data-error-field]').forEach(el => {
        el.textContent = '';
        el.style.display = 'none';
    });

    btn.disabled = true;
    btn.textContent = 'Menyimpan Unit Fisik...';

    const formData = new FormData(form);

    try {
        const response = await fetch(@json(route('admin.mobil.store')), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const data = await response.json();

        if (response.ok && data.success) {
            const newCar = data.mobil || {};
            resetModalFormDirty();
            closeVehicleModal(true);
            showNotificationToast(data.message || 'Unit kendaraan berhasil didaftarkan ke inventori.', 'success');

            window.location.reload();
        } else if (response.status === 422 && data.errors) {
            for (const [field, messages] of Object.entries(data.errors)) {
                const errEl = form.querySelector(`[data-error-field="${field}"]`);
                if (errEl) {
                    errEl.textContent = Array.isArray(messages) ? messages[0] : messages;
                    errEl.style.display = 'block';
                }
            }
            if (globalErr) {
                globalErr.textContent = 'Terdapat data formulir yang belum lengkap atau sudah terdaftar.';
                globalErr.style.display = 'block';
            }
        } else {
            if (globalErr) {
                globalErr.textContent = data.message || 'Gagal menyimpan unit ke inventori.';
                globalErr.style.display = 'block';
            }
        }
    } catch (err) {
        if (globalErr) {
            globalErr.textContent = 'Gagal menghubungi server: ' + err.message;
            globalErr.style.display = 'block';
        }
    } finally {
        btn.disabled = false;
        btn.textContent = '+ Simpan Unit ke Inventori';
    }
}

// -------------------------------------------------------------
// 3C. MODAL PERMINTAAN PRE-ORDER (CODEX REFINEMENT)
// -------------------------------------------------------------
function renderPreorderModal(car, dom) {
    dom.title.textContent = `Permintaan Pre-Order — ${car.merk} ${car.model}`;
    dom.kicker.textContent = 'PRE-ORDER MEMBER · MANAJEMEN ANTREAN UNIT';
    dom.dialog.classList.add('is-wide');

    const preorders = car.preorders || [];
    const stockOrderable = Number(car.stock_orderable || 0);

    dom.body.innerHTML = `
        <div id="preorderListView">
            <div class="preorder-modal-summary-bar">
                <div class="summary-stat-box">
                    <span class="label">Total Pre-Order Aktif</span>
                    <span class="val highlight">${preorders.length} Permintaan</span>
                </div>
                <div class="summary-stat-box">
                    <span class="label">Stok Fisik Siap Jual</span>
                    <span class="val ${stockOrderable > 0 ? 'text-green' : 'text-amber'}">
                        ${stockOrderable > 0 ? stockOrderable + ' Unit Siap Jual' : '0 Unit (Stok Habis / Belum Ada)'}
                    </span>
                </div>
                <div class="summary-stat-box">
                    <span class="label">Model Kendaraan</span>
                    <span class="val">${car.merk} ${car.model} (${car.tahun || ''})</span>
                </div>
            </div>

            ${preorders.length === 0 ? `
                <div class="preorder-empty-box">
                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin:0 auto 12px; display:block;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <h4 style="margin:0 0 6px; font-weight:700; color:#0f172a; font-size:15px;">Tidak Ada Permintaan Pre-Order Aktif</h4>
                    <p style="margin:0 0 16px; font-size:12px; color:#64748b; max-width:440px; margin-inline:auto;">
                        Belum ada member yang mengajukan pre-order untuk model <strong>${car.merk} ${car.model}</strong> saat ini.
                    </p>
                    ${!isUserOwner ? `
                        <button type="button" class="btn-action-inline is-primary" onclick="openVehicleActionModal('${car.id}', 'add_unit')">
                            + Daftarkan Unit Baru ke Inventori
                        </button>
                    ` : ''}
                </div>
            ` : `
                <div style="overflow-x:auto;">
                    <table class="modal-history-table">
                        <thead>
                            <tr>
                                <th>Kode &amp; Tanggal</th>
                                <th>Member &amp; Kontak</th>
                                <th>Varian &amp; Warna</th>
                                <th style="text-align:center;">Status Pre-Order</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${preorders.map(po => `
                                <tr>
                                    <td>
                                        <strong style="color:#0f172a;">${po.kode_preorder}</strong>
                                        <div style="font-size:11px; color:#64748b;">${po.created_at}</div>
                                    </td>
                                    <td>
                                        <strong style="color:#0f172a;">${po.customer_name}</strong>
                                        <div style="font-size:11px; color:#64748b;">${po.customer_code} &bull; ${po.customer_phone}</div>
                                    </td>
                                    <td>
                                        <div style="color:#0f172a; font-weight:600;">${po.variant || 'Standar'} (${po.year})</div>
                                        <div style="font-size:11px; color:#64748b;">Warna: <strong style="color:#334155;">${po.color}</strong></div>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="badge-status is-active" style="text-transform:capitalize;">${(po.status_label || po.status).replace('_', ' ')}</span>
                                    </td>
                                    <td style="text-align:center;">
                                        <button type="button" class="btn-po-review" onclick="viewPreorderDetail(${po.id_preorder}, '${car.id}')" title="Tinjau Permintaan Pre-Order">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                            <span>Tinjau</span>
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `}
        </div>

        <div id="preorderReviewView" style="display:none;">
            <div style="margin-bottom:14px;">
                <button type="button" class="btn-preorder-back" onclick="switchPreorderView('list')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    <span>&larr; Kembali ke Daftar Pre-Order</span>
                </button>
            </div>
            <div id="preorderReviewContent"></div>
        </div>
    `;

    dom.footer.innerHTML = `
        <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>
        ${!isUserOwner ? `
            <button type="button" class="btn-modal-primary" onclick="openVehicleActionModal('${car.id}', 'add_unit')" style="background:#059669; border-color:#059669;">
                + Tambah Unit Fisik untuk Model Ini
            </button>
        ` : ''}
    `;
}

function switchPreorderView(view) {
    const listView = document.getElementById('preorderListView');
    const reviewView = document.getElementById('preorderReviewView');
    if (!listView || !reviewView) return;

    if (view === 'review') {
        listView.style.display = 'none';
        reviewView.style.display = 'block';
    } else {
        reviewView.style.display = 'none';
        listView.style.display = 'block';
    }
}

async function viewPreorderDetail(preorderId, carId) {
    switchPreorderView('review');
    const content = document.getElementById('preorderReviewContent');
    if (!content) return;

    content.innerHTML = `
        <div style="padding:40px 20px; text-align:center; color:#64748b;">
            <div style="display:inline-block; width:28px; height:28px; border:3px solid #e2e8f0; border-top-color:#dc2626; border-radius:50%; animation:modalSpin 0.8s linear infinite;"></div>
            <p style="margin:12px 0 0; font-size:13px; font-weight:600; color:#334155;">Memuat rincian permintaan pre-order #${preorderId}...</p>
        </div>
    `;

    const car = getCarPayload(carId) || {};
    const localPo = (car.preorders || []).find(p => p.id_preorder == preorderId) || {};

    try {
        const response = await fetch('/admin/pre-order-mobil/' + preorderId + '/detail', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (response.ok) {
            const data = await response.json();
            renderPreorderDetailContent(data, car, preorderId, carId);
            return;
        }
    } catch (e) {
        console.warn('Could not fetch server detail, using local preorder payload', e);
    }

    // Fallback
    renderPreorderDetailContent({
        preorder: localPo,
        customer: {
            name: localPo.customer_name,
            phone: localPo.customer_phone,
            email: localPo.customer_email,
            code: localPo.customer_code
        }
    }, car, preorderId, carId);
}

function renderPreorderDetailContent(data, car, preorderId, carId) {
    const content = document.getElementById('preorderReviewContent');
    if (!content) return;

    const po = data.preorder || data || {};
    const cust = data.customer || po.customer || {};
    const stockOrderable = Number(car.stock_orderable || 0);
    const code = data.code || po.kode_preorder || ('PRE-' + String(preorderId).padStart(5, '0'));
    const createdAt = data.created_at || po.created_at || '—';
    const variantName = data.variant || po.varian || po.variant || 'Standar';
    const yearVal = data.year || po.tahun || po.year || car.tahun || '—';
    const colorVal = data.color || po.warna || po.color || 'Standar';
    const statusVal = data.status || po.status || 'Aktif';
    const statusLabel = (po.status_label || statusVal).replace('_', ' ');

    content.innerHTML = `
        <div class="preorder-review-container">
            {{-- Review Header Info --}}
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:14px; padding:12px 16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px;">
                <div>
                    <span style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase;">KODE PRE-ORDER</span>
                    <div style="font-size:16px; font-weight:800; color:#0f172a;">${code}</div>
                </div>
                <div style="text-align:right;">
                    <span style="font-size:11px; color:#64748b;">Tanggal Pengajuan:</span>
                    <div style="font-weight:700; color:#334155; font-size:13px;">${createdAt}</div>
                </div>
            </div>

            {{-- 2 Cards: Profil Member & Spesifikasi Permintaan --}}
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:12px; margin-bottom:14px;">
                <div class="preorder-card-box">
                    <h5 style="margin:0 0 10px; font-size:13px; font-weight:800; color:#0f172a; text-transform:uppercase;">Profil Member Pemesan</h5>
                    <dl class="modal-spec-dl" style="margin:0;">
                        <div class="modal-spec-row"><dt>Nama Member</dt><dd><strong>${cust.name || po.customer_name || 'Member YZA'}</strong></dd></div>
                        <div class="modal-spec-row"><dt>Kode Member</dt><dd>${cust.code || po.customer_code || '—'}</dd></div>
                        <div class="modal-spec-row"><dt>No. Telepon / WA</dt><dd>${cust.phone || po.customer_phone || '—'}</dd></div>
                        <div class="modal-spec-row"><dt>Email</dt><dd>${cust.email || po.customer_email || '—'}</dd></div>
                        <div class="modal-spec-row"><dt>Status Keanggotaan</dt><dd><span class="badge-status is-available">Member Aktif</span></dd></div>
                    </dl>
                </div>

                <div class="preorder-card-box">
                    <h5 style="margin:0 0 10px; font-size:13px; font-weight:800; color:#0f172a; text-transform:uppercase;">Spesifikasi Kendaraan Diminta</h5>
                    <dl class="modal-spec-dl" style="margin:0;">
                        <div class="modal-spec-row"><dt>Model Kendaraan</dt><dd><strong>${car.merk || ''} ${car.model || ''}</strong></dd></div>
                        <div class="modal-spec-row"><dt>Varian / Tipe</dt><dd>${variantName}</dd></div>
                        <div class="modal-spec-row"><dt>Tahun Pembuatan</dt><dd>${yearVal}</dd></div>
                        <div class="modal-spec-row"><dt>Preferensi Warna</dt><dd><strong style="color:#0f172a;">${colorVal}</strong></dd></div>
                        <div class="modal-spec-row"><dt>Status Pre-Order</dt><dd><span class="badge-status is-active" style="text-transform:capitalize;">${statusLabel}</span></dd></div>
                    </dl>
                </div>
            </div>

            {{-- Evaluasi Stok Fisik Dealer --}}
            <div class="preorder-stock-status-box ${stockOrderable > 0 ? 'is-ready' : 'is-empty'}" style="margin-bottom:14px; padding:16px; border-radius:10px; border:1px solid ${stockOrderable > 0 ? '#a7f3d0' : '#fde68a'}; background:${stockOrderable > 0 ? '#ecfdf5' : '#fffbeb'};">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
                    <div>
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                            <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:${stockOrderable > 0 ? '#10b981' : '#f59e0b'};"></span>
                            <strong style="font-size:14px; color:${stockOrderable > 0 ? '#065f46' : '#92400e'};">
                                ${stockOrderable > 0 ? `Unit Fisik Tersedia (${stockOrderable} Unit Siap Jual di Showroom)` : 'Unit Fisik Belum Tersedia di Inventori Operasional'}
                            </strong>
                        </div>
                        <p style="margin:0; font-size:12px; color:${stockOrderable > 0 ? '#047857' : '#b45309'}; line-height:1.5;">
                            ${stockOrderable > 0
                                ? 'Stok fisik kendaraan telah tersedia di dealer. Anda dapat melanjutkan proses ini untuk menerbitkan SPK atau mengabari member bahwa unit telah siap.'
                                : 'Saat ini belum ada unit fisik yang siap jual untuk memenuhi pesanan pre-order ini. Daftarkan unit baru ke inventori untuk segera memproses antrean member ini.'}
                        </p>
                    </div>

                    <div style="display:flex; gap:8px; align-items:center;">
                        ${!isUserOwner ? (stockOrderable > 0
                            ? `<button type="button" class="btn-action-inline is-success" onclick="closeVehicleModal(); showNotificationToast('Unit siap dikaitkan. Silakan buat SPK melalui Pesanan Mobil.', 'success');">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>Lanjutkan ke SPK</span>
                               </button>`
                            : `<button type="button" class="btn-action-inline is-primary" onclick="openVehicleActionModal('${carId}', 'add_unit')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                <span>+ Tambah Unit Fisik</span>
                               </button>`
                        ) : ''}
                    </div>
                </div>
            </div>

            {{-- Catatan / Log Tambahan --}}
            ${po.catatan ? `
                <div style="padding:12px 14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; font-size:12px; color:#475569;">
                    <strong>Catatan Khusus dari Member:</strong>
                    <p style="margin:4px 0 0; font-style:italic;">"${po.catatan}"</p>
                </div>
            ` : ''}
        </div>
    `;
}

// -------------------------------------------------------------
// 5. MODAL RIWAYAT PERGERAKAN STOK (PHASE 7)
// -------------------------------------------------------------
function renderStockHistoryModal(car, dom) {
    dom.title.textContent = 'Riwayat Pergerakan Stok';
    dom.dialog.classList.add('is-medium');

    const histories = car.histories || [];

    if (histories.length === 0) {
        dom.body.innerHTML = `
            <div style="padding:40px 20px; text-align:center; color:#64748b;">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin-bottom:10px;"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                <h4 style="margin:0 0 6px; font-weight:700; color:#0f172a;">Belum Ada Riwayat Pergerakan Stok</h4>
                <p style="margin:0; font-size:12px;">Unit kendaraan ini belum memiliki catatan aktivitas inventori tambahan selain inisialisasi awal.</p>
            </div>
        `;
    } else {
        dom.body.innerHTML = `
            <div style="overflow-x:auto;">
                <table class="modal-history-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Aktivitas</th>
                            <th style="text-align:center;">Perubahan</th>
                            <th>Stok Sebelum &rarr; Sesudah</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${histories.map(h => `
                            <tr>
                                <td style="font-size:11px; color:#64748b; white-space:nowrap;">${h.tanggal}</td>
                                <td><strong style="color:#0f172a;">${h.jenis}</strong></td>
                                <td style="text-align:center;"><span style="font-weight:700; color:${h.jumlah.toString().startsWith('-') ? '#dc2626' : '#16a34a'};">${h.jumlah} unit</span></td>
                                <td style="font-family:monospace; font-size:11px;">${h.before_after}</td>
                                <td style="color:#64748b;">${colorStockEscape(h.catatan)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    dom.footer.innerHTML = `
        <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>
        <button type="button" class="btn-modal-secondary" onclick="openVehicleActionModal(${car.id}, 'stock')">Kelola Stok</button>
    `;
}

// -------------------------------------------------------------
// 6. MODAL GALERI FOTO & WARNA KENDARAAN (PHASE 8)
// -------------------------------------------------------------
function renderGalleryAndColorsModal(car, dom) {
    dom.title.textContent = 'Galeri Foto & Master Warna';
    dom.navTabs.removeAttribute('hidden');
    dom.navTabs.innerHTML = `
        <button type="button" class="modal-tab-btn is-active" data-target="pane-gallery-photos" aria-selected="true">Foto Kendaraan</button>
        <button type="button" class="modal-tab-btn" data-target="pane-gallery-catalog-colors" aria-selected="false">Warna Katalog</button>
        <button type="button" class="modal-tab-btn" data-target="pane-gallery-inventory-color" aria-selected="false">Warna Unit Inventori</button>
    `;

    const angles = car.angles || {};
    const photos = car.photos || [];
    const colors = car.colors || [];
    const uploadedAnglesCount = Object.values(angles).filter(a => a.path).length;

    dom.body.innerHTML = `
        {{-- Pane A: Foto Kendaraan & Multi-Sudut Visual --}}
        <div id="pane-gallery-photos" class="modal-pane is-active" style="display:block;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:14px; padding:10px 14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; font-size:12px;">
                <span>Kelengkapan Foto Visual 4-Sudut: <strong>${uploadedAnglesCount} dari 4 sudut terunggah</strong></span>
                <span class="badge-status ${uploadedAnglesCount === 4 ? 'is-available' : 'is-warning'}">
                    ${uploadedAnglesCount === 4 ? 'Visual Lengkap' : 'Perlu Dilengkapi'}
                </span>
            </div>

            {{-- Active Image Preview Container --}}
            <div class="gallery-active-preview" id="galleryActivePreview">
                <img src="${car.thumbnail}" alt="Pratinjau Foto Utama ${car.merk} ${car.model}">
            </div>

            <h4 class="section-micro-title">Pratinjau Multi-Sudut Visual (Klik untuk Memperbesar)</h4>
            <div class="visual-angles-grid" style="margin-bottom:20px;">
                ${Object.entries(angles).map(([key, item]) => `
                    <div class="angle-card" style="cursor:pointer;" onclick="setGalleryActivePreview('${item.path || car.thumbnail}')" title="Klik untuk menampilkan pratinjau ${item.label}">
                        <div class="angle-view-box">
                            ${item.path
                                ? `<img src="${item.path}" alt="${item.label}">`
                                : `<div class="angle-placeholder"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.6"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg><span>${item.label} belum diunggah</span></div>`}
                        </div>
                        <div class="angle-caption">
                            <span>${item.label}</span>
                            <span class="badge-dot ${item.path ? 'is-active' : 'is-inactive'}"></span>
                        </div>
                    </div>
                `).join('')}
            </div>

            <div class="uploaded-photos-header">
                <h4 class="section-micro-title" style="margin:0;">Galeri Foto Tambahan (${photos.length})</h4>
                ${!isUserOwner ? `
                    <form action="${car.urls.photo_store}" method="post" enctype="multipart/form-data" class="quick-upload-form">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content}">
                        <label class="btn-upload-file">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            <span>Unggah Foto Baru</span>
                            <input type="file" name="foto" accept="image/*" required onchange="this.form.submit()" hidden>
                        </label>
                    </form>
                ` : ''}
            </div>

            <div class="uploaded-thumbnails-grid">
                ${photos.length > 0
                    ? photos.map(f => `
                        <div class="uploaded-photo-card ${f.is_primary ? 'is-primary' : ''}">
                            <img src="${f.url}" alt="Foto Tambahan" style="cursor:pointer;" onclick="setGalleryActivePreview('${f.url}')" title="Klik untuk pratinjau">
                            <div class="uploaded-photo-overlay">
                                ${f.is_primary
                                    ? '<span class="badge-primary-photo">Utama</span>'
                                    : (!isUserOwner ? `
                                        <form action="${f.primary_url}" method="post">
                                            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content}">
                                            <input type="hidden" name="_method" value="PATCH">
                                            <button type="submit" class="btn-action-photo set-primary" title="Jadikan Foto Utama">Utama</button>
                                        </form>
                                    ` : '')}
                                ${!isUserOwner ? `
                                    <form action="${f.destroy_url}" method="post" onsubmit="return confirm('Hapus foto ini?')">
                                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn-action-photo delete" title="Hapus Foto">Hapus</button>
                                    </form>
                                ` : ''}
                            </div>
                        </div>
                    `).join('')
                    : '<div class="no-uploaded-photos"><span>Belum ada foto tambahan yang diunggah untuk unit ini. Gunakan tombol Unggah Foto Baru di atas.</span></div>'}
            </div>
        </div>

        {{-- Pane B: Warna Katalog Showroom --}}
        <div id="pane-gallery-catalog-colors" class="modal-pane hidden" style="display:none;">
            <div class="modal-info-callout" style="margin-bottom:16px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <span>Warna katalog merupakan pilihan varian tampilan showroom yang dapat dikonfigurasi member, BUKAN bukti ketersediaan stok fisik di dealer.</span>
            </div>

            <h4 class="section-micro-title" style="margin-top:0;">Daftar Pilihan Warna Showroom (Klik untuk Rincian)</h4>
            <div class="color-swatches-list" style="margin-bottom:16px;">
                ${colors.length > 0
                    ? colors.map((w, idx) => `
                        <div class="color-chip is-interactive ${w.is_default ? 'is-default' : ''}" data-color-idx="${idx}">
                            <span class="color-circle" style="background-color: ${w.hex || '#1e293b'};"></span>
                            <div class="color-chip-info">
                                <strong>${w.nama}</strong>
                                <small>${w.hex || '—'}</small>
                            </div>
                            ${w.is_default ? '<span class="chip-tag-default">Default</span>' : ''}
                        </div>
                    `).join('')
                    : '<div style="padding:16px; background:#f8fafc; border:1px solid #edf2f7; border-radius:8px; color:#64748b; font-size:12px;">Tidak ada master warna katalog showroom terpisah. Model menggunakan warna dasar bawaan.</div>'}
            </div>

            {{-- Detail Panel for Selected Catalog Color --}}
            <div id="catalogColorDetailPanel" style="margin-top:12px;"></div>
        </div>

        {{-- Pane C: Warna Unit Inventori Aktual --}}
        <div id="pane-gallery-inventory-color" class="modal-pane hidden" style="display:none;">
            <div class="modal-info-callout" style="background:#ecfdf5; border-color:#a7f3d0; color:#065f46; margin-bottom:16px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>Warna Fisik Unit yang tercatat pada inventori operasional saat ini adalah: <strong style="font-size:14px; text-transform:uppercase;">${car.warna || 'Tidak Ditentukan'}</strong></span>
            </div>

            <div class="modal-section-box">
                <h4 style="margin:0 0 10px; font-size:14px; font-weight:800; color:#0f172a;">Data Alokasi Stok Fisik Berdasarkan Warna</h4>
                <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:14px;">
                    <div style="padding:10px 12px; background:#ffffff; border:1px solid #e2e8f0; border-radius:8px; text-align:center;">
                        <div style="font-size:11px; color:#64748b;">TOTAL FISIK</div>
                        <div style="font-size:18px; font-weight:800; color:#0f172a;">${car.stock_total}</div>
                    </div>
                    <div style="padding:10px 12px; background:#ffffff; border:1px solid #a7f3d0; border-radius:8px; text-align:center;">
                        <div style="font-size:11px; color:#047857;">SIAP JUAL</div>
                        <div style="font-size:18px; font-weight:800; color:#047857;">${car.stock_orderable}</div>
                    </div>
                    <div style="padding:10px 12px; background:#ffffff; border:1px solid #fde68a; border-radius:8px; text-align:center;">
                        <div style="font-size:11px; color:#b45309;">RESERVASI</div>
                        <div style="font-size:18px; font-weight:800; color:#b45309;">${car.reserved_units}</div>
                    </div>
                    <div style="padding:10px 12px; background:#ffffff; border:1px solid #cbd5e1; border-radius:8px; text-align:center;">
                        <div style="font-size:11px; color:#475569;">TERJUAL</div>
                        <div style="font-size:18px; font-weight:800; color:#0f172a;">${car.sold_units}</div>
                    </div>
                </div>

                <div style="display:flex; flex-direction:column; gap:6px; font-size:12px; color:#475569;">
                    <div>Kode Mobil: <strong>${car.kode}</strong></div>
                    <div>Nomor Rangka (VIN): <strong style="font-family:monospace;">${car.nomor_rangka || 'Tercatat dalam batch inventori dealer'}</strong></div>
                    <div>Tipe Inventori: <strong>${car.reserved_units > 0 ? 'Batch Fisik dengan Reservasi Mengikat' : 'Stok Fisik Tersedia'}</strong></div>
                </div>
            </div>

            <div style="padding:12px 16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; font-size:12px; color:#64748b; line-height:1.6;">
                <strong>Kebijakan Alokasi Dealer:</strong> Seluruh transaksi pesanan SPK dan reservasi unit secara otomatis mengikat unit fisik dengan warna <strong>${car.warna}</strong>. Permintaan warna showroom lain yang belum memiliki stok fisik dialokasikan melalui proses pemesanan ulang (pre-order).
            </div>
        </div>
    `;

    // Interactive helper: Set gallery preview image
    window.setGalleryActivePreview = function(url) {
        const preview = dom.body.querySelector('#galleryActivePreview img');
        if (preview && url) {
            preview.src = url;
        }
    };

    // Interactive helper: Swatch selection in Tab B
    const swatchChips = dom.body.querySelectorAll('.color-chip.is-interactive');
    const detailPanel = dom.body.querySelector('#catalogColorDetailPanel');
    if (swatchChips.length > 0 && detailPanel) {
        const selectColor = (idx) => {
            swatchChips.forEach((c, i) => c.classList.toggle('is-selected', i === idx));
            const w = colors[idx];
            if (!w) return;
            detailPanel.innerHTML = `
                <div class="modal-section-box" style="margin-top:10px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <div>
                            <strong style="font-size:14px; color:#0f172a;">${w.nama}</strong>
                            <div style="font-family:monospace; font-size:11px; color:#64748b;">HEX: ${w.hex || '—'} &bull; Slug: ${w.slug || '—'}</div>
                        </div>
                        <div>
                            ${w.is_default ? '<span class="badge-primary-photo">Warna Utama Katalog</span>' : '<span style="font-size:11px; color:#64748b; background:#f1f5f9; padding:4px 8px; border-radius:6px;">Pilihan Tambahan</span>'}
                        </div>
                    </div>
                    <div style="font-size:12px; color:#475569; margin-bottom:10px;">
                        Status Showroom: <strong style="color:#16a34a;">Aktif pada Konfigurator Member</strong>
                    </div>
                    <div style="padding:10px 14px; background:#ffffff; border:1px solid #e2e8f0; border-radius:8px; font-size:11px; color:#64748b;">
                        <em>Informasi: Pilihan warna katalog memungkinkan member memilih tampilan showroom yang diinginkan. Ketersediaan stok fisik sebenarnya ditampilkan pada tab <strong>Warna Unit Inventori</strong>.</em>
                    </div>
                </div>
            `;
        };

        swatchChips.forEach((chip, i) => {
            chip.addEventListener('click', () => selectColor(i));
        });
        selectColor(0);
    }

    dom.body.insertAdjacentHTML('afterbegin', renderColorStockCards(car));
    function updateGalleryFooter(activePaneId) {
        if (activePaneId === 'pane-gallery-photos' || activePaneId === 'pane-gallery-catalog-colors') {
            dom.footer.innerHTML = `<button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>`;
        } else if (activePaneId === 'pane-gallery-inventory-color') {
            dom.footer.innerHTML = `
                <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>
                ${!isUserOwner ? `<button type="button" class="btn-modal-primary" onclick="openVehicleActionModal(${car.id}, 'edit')">Edit Data Kendaraan</button>` : ''}
            `;
        }
    }

    setupModalPaneTabs(dom.navTabs, dom.body, updateGalleryFooter);
    updateGalleryFooter('pane-gallery-photos');
}

// -------------------------------------------------------------
// 7. MODAL KONFIRMASI NONAKTIFKAN KENDARAAN (PHASE 9)
// -------------------------------------------------------------
function renderDeactivateConfirmationModal(car, dom) {
    dom.title.textContent = 'Konfirmasi Nonaktifkan Kendaraan';
    dom.dialog.classList.add('is-compact');

    if (isUserOwner) {
        dom.body.innerHTML = `
            <div class="modal-warning-callout">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>Role Owner hanya memiliki hak akses baca (read-only). Nonaktifkan kendaraan dinonaktifkan.</span>
            </div>
        `;
        dom.footer.innerHTML = `<button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>`;
        return;
    }

    if (car.is_sold) {
        dom.body.innerHTML = `
            <div class="modal-warning-callout">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>Kendaraan dengan status <strong>Terjual</strong> tidak dapat dinonaktifkan karena telah selesai diserahterimakan ke pelanggan.</span>
            </div>
        `;
        dom.footer.innerHTML = `<button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>`;
        return;
    }

    dom.body.innerHTML = `
        <div class="modal-warning-callout">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            <div>
                <strong>Perhatian: Tindakan Penonaktifan</strong>
                <p style="margin:4px 0 0; font-size:11px;">Kendaraan <strong>${car.merk} ${car.model} (${car.kode})</strong> akan disembunyikan dari katalog showroom publik.</p>
            </div>
        </div>

        ${car.reserved_units > 0 ? `
            <div style="padding:10px 14px; background:#fffbeb; border:1px solid #fde68a; border-radius:8px; color:#92400e; font-size:12px; margin-bottom:14px;">
                <strong>Peringatan Reservasi:</strong> Terdapat ${car.reserved_units} reservasi/pesanan aktif pada kendaraan ini. Data pesanan member tetap aman dan tidak akan terhapus.
            </div>
        ` : ''}

        <p style="font-size:12px; color:#475569; line-height:1.5;">
            Menonaktifkan kendaraan tidak akan menghapus data historis, riwayat stok, atau transaksi sebelumnya. Status dapat diaktifkan kembali sewaktu-waktu melalui menu Edit.
        </p>

        <form id="deactivateVehicleForm" onsubmit="submitDeactivateVehicle(event, ${car.id})">
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content}">
            <input type="hidden" name="_method" value="PATCH">
            <div id="deactivateVehicleError" class="modal-field-error" style="display:none; margin-top:8px;"></div>
        <div class="modal-form-group" style="grid-column:1/-1"><label class="modal-form-label">Alasan perubahan stok *</label><input name="alasan_stok" class="modal-form-input" minlength="5" maxlength="160" required></div><div class="modal-form-group" style="grid-column:1/-1"><label class="modal-form-label">Bukti / nomor dokumen verifikasi *</label><input name="bukti_stok" class="modal-form-input" minlength="5" maxlength="500" placeholder="Nomor surat jalan / berita acara pemeriksaan" required></div></form>
    `;

    dom.footer.innerHTML = `
        <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Batal</button>
        <button type="submit" form="deactivateVehicleForm" class="btn-modal-danger" id="btnSubmitDeactivate">Konfirmasi Nonaktifkan</button>
    `;
}

// Handler Submit Nonaktifkan Kendaraan
async function submitDeactivateVehicle(e, carId) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitDeactivate');
    const errBox = document.getElementById('deactivateVehicleError');
    errBox.style.display = 'none';

    const car = getCarPayload(carId);
    if (!car) return;

    btn.disabled = true;
    btn.textContent = 'Memproses...';

    const formData = new FormData(e.target);

    try {
        const response = await fetch(car.urls.deactivate, {
            method: 'POST', // with _method=PATCH
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        const data = await response.json();
        if (response.ok && data.success) {
            updateLocalCarPayload(carId, { status: 'nonaktif' });

            // Update row in table
            const row = document.getElementById('row-car-' + carId);
            if (row) {
                const statusBadge = row.querySelector('.td-status .badge-status');
                if (statusBadge) {
                    statusBadge.className = 'badge-status is-inactive';
                    statusBadge.textContent = 'Nonaktif';
                }
            }

            closeVehicleModal();
            showNotificationToast('Kendaraan berhasil dinonaktifkan.', 'success');
        } else {
            errBox.textContent = data.message || 'Gagal menonaktifkan kendaraan.';
            errBox.style.display = 'block';
        }
    } catch (err) {
        errBox.textContent = 'Gagal menghubungi server: ' + err.message;
        errBox.style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Konfirmasi Nonaktifkan';
    }
}

// Helper: Setup Tab Switching inside Modal
function setupModalPaneTabs(navTabs, body, onTabChange = null) {
    const tabBtns = navTabs.querySelectorAll('.modal-tab-btn');
    const panes = body.querySelectorAll('.modal-pane');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            tabBtns.forEach(b => {
                b.classList.remove('is-active');
                b.setAttribute('aria-selected', 'false');
            });
            this.classList.add('is-active');
            this.setAttribute('aria-selected', 'true');

            panes.forEach(p => {
                if (p.id === targetId) {
                    p.classList.remove('hidden');
                    p.classList.add('is-active');
                    p.style.display = 'block';
                } else {
                    p.classList.add('hidden');
                    p.classList.remove('is-active');
                    p.style.display = 'none';
                }
            });

            // Reset scroll position to top on tab switch
            body.scrollTop = 0;

            if (typeof onTabChange === 'function') {
                onTabChange(targetId);
            }
        });
    });
}

// Helper: Simple Notification Toast
function showNotificationToast(msg, type = 'success') {
    let container = document.getElementById('vehicleActionToastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'vehicleActionToastContainer';
        container.style.cssText = 'position:fixed; bottom:24px; right:24px; z-index:100000; display:flex; flex-direction:column; gap:8px;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.style.cssText = 'padding:12px 18px; border-radius:10px; background:#0f172a; color:#ffffff; font-size:13px; font-weight:600; box-shadow:0 8px 24px rgba(0,0,0,0.25); display:flex; align-items:center; gap:8px; animation:modalSlideUp 180ms ease-out;';
    toast.innerHTML = `<span style="color:#10b981; font-size:16px;">✓</span> <span>${msg}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 200ms ease';
        setTimeout(() => toast.remove(), 250);
    }, 3200);
}
</script>
@endpush
@endsection
