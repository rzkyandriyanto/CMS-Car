@extends('layouts.app')

@section('title', 'Member Center · YZA Automotive')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/member-home.css') }}?v={{ filemtime(public_path('assets/css/member-home.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/showroom.css') }}?v=garage-showcase-20261010">
    <link rel="stylesheet" href="{{ asset('assets/css/member-home-reference.css') }}?v={{ filemtime(public_path('assets/css/member-home-reference.css')) }}">
@endpush

@section('content')
@php
    $isOwner = (bool) ($hasVerifiedOwnership ?? false);
    $membership = $customer->membership;
    $memberStatus = strtoupper((string) ($membership?->status ?: 'aktif'));
    $memberStatusLabel = $memberStatus === 'ACTIVE' ? 'AKTIF' : str_replace('_', ' ', $memberStatus);
    $coupons = [
        [
            'id' => 4,
            'code' => 'YZA-SRV50',
            'title' => 'Promo Servis 50%',
            'copy' => 'Diskon 50% biaya jasa servis berkala resmi YZA untuk member.',
            'points' => 250,
            'icon' => 'tools',
            'image' => 'assets/images/member/reference-service-bengkel.jpg',
        ],
        [
            'id' => 8,
            'code' => 'YZA-TUNEUP',
            'title' => 'Promo Tune Up Mesin',
            'copy' => 'Paket tune up mesin komprehensif, ganti oli & filter oli resmi YZA.',
            'points' => 250,
            'icon' => 'tools',
            'image' => 'assets/images/member/member-benefit-service-v2.png',
        ],
        [
            'id' => 5,
            'code' => 'YZA-WASHVIP',
            'title' => 'Car Wash Express',
            'copy' => 'Voucher gratis cuci hidrolik premium & detailing wax exterior.',
            'points' => 100,
            'icon' => 'wash',
            'image' => 'assets/images/member/reference-service-car-wash.jpg',
        ],
        [
            'id' => 9,
            'code' => 'YZA-BRAKES',
            'title' => 'Ganti Kampas Rem',
            'copy' => 'Diskon 30% penggantian kampas rem depan/belakang & cek sistem rem.',
            'points' => 200,
            'icon' => 'wheel',
            'image' => 'assets/images/member/member-benefit-brakes-v2.png',
        ],
    ];
    $ownerCards = collect($ownerGarageCards ?? []);
    $purchaseItem = $activeOrder ?? $activePreorder ?? null;
@endphp

<div class="yza-home yza-home-reference yza-mc-modern" data-yza-home>
    <section class="yza-home-hero" aria-labelledby="yza-home-title">
        <div class="yza-home-wall-brand" aria-hidden="true"><strong>YZA</strong><small>AUTOMOTIVE</small></div>
        <div class="yza-home-hero-copy">
            <h1 id="yza-home-title">Selamat Datang Kembali,<br><em>{{ $customer->nama_lengkap }}</em></h1>
            <p>
                @if($isOwner)
                    Pantau kondisi kendaraan, odometer bengkel, dan jadwal perawatan unit YZA Anda dalam satu tempat.
                @else
                    Jelajahi katalog unit pilihan YZA, jadwalkan test drive, dan pantau pesanan kendaraan Anda.
                @endif
            </p>
            <div class="yza-home-hero-actions">
                <a class="yza-home-primary" href="{{ route('pelanggan.katalog-mobil.index') }}"><x-member-home-icon name="car" />Beli Mobil</a>
                @if($isOwner)
                    <a class="yza-home-secondary" href="{{ route('pelanggan.booking-servis.create') }}"><x-member-home-icon name="tools" />Booking Servis</a>
                    <a class="yza-home-secondary" href="{{ route('pelanggan.booking-cuci.create') }}"><x-member-home-icon name="wash" />Car Wash</a>
                @else
                    <a class="yza-home-secondary" href="{{ route('pelanggan.test-drive.index') }}">Test Drive</a>
                    <a class="yza-home-secondary" href="{{ route('pelanggan.pesanan-mobil.index') }}">Pesanan Saya</a>
                @endif
            </div>
        </div>
        <article class="yza-home-member-card" aria-label="Kartu membership digital">
            <div class="yza-member-card-top">
                <div class="yza-member-card-brand">
                    <img src="{{ asset('assets/images/branding/logo-yza.png') }}" alt="YZA Automotive">
                    <span class="yza-brand-text">YZA AUTOMOTIVE</span>
                </div>
                <span class="yza-card-status-minimal">{{ $memberStatusLabel }}</span>
            </div>

            <div class="yza-member-card-security">
                <div class="yza-card-emv-chip" aria-hidden="true">
                    <span class="chip-line"></span>
                    <span class="chip-line"></span>
                    <span class="chip-core"></span>
                </div>
                <div class="yza-card-contactless" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                        <path d="M8.5 16.5a5 5 0 0 1 0-9"></path>
                        <path d="M12 19a8.5 8.5 0 0 0 0-14"></path>
                        <path d="M15.5 21.5a12 12 0 0 0 0-19"></path>
                    </svg>
                </div>
            </div>

            <div class="yza-member-card-number">
                <span class="yza-card-caption">MEMBERSHIP NO.</span>
                <strong class="yza-card-digits">{{ $memberId }}</strong>
            </div>

            <div class="yza-member-card-bottom">
                <div class="yza-member-card-holder">
                    <span class="yza-card-caption">CARDHOLDER</span>
                    <strong class="yza-card-val-name">{{ strtoupper($customer->nama_lengkap) }}</strong>
                </div>
                <div class="yza-member-card-dates">
                    <div class="yza-card-date-col">
                        <span class="yza-card-caption">SINCE</span>
                        <strong class="yza-card-val">{{ optional($memberSince)->format('m/y') ?: '11/26' }}</strong>
                    </div>
                </div>
            </div>
        </article>
    </section>

    <section class="yza-home-stats" aria-label="Ringkasan member">
        <a href="{{ $isOwner ? route('pelanggan.kendaraan.index') : '#' }}" @if(!$isOwner) data-owner-locked aria-disabled="true" tabindex="-1" title="Tersedia setelah pembelian mobil YZA terverifikasi" @endif>
            <span class="yza-home-stat-icon"><x-member-home-icon name="car" /></span>
            <small>KENDARAAN YZA</small>
            <strong>{{ $ownerCards->count() }}</strong>
            <em>{{ $isOwner ? 'Unit terdaftar di garasi' : 'Terkunci hingga pembelian unit' }}</em>
            <b class="yza-home-stat-arrow" aria-hidden="true">›</b>
        </a>
        <a href="{{ $isOwner ? route('pelanggan.booking.index') : '#' }}" @if(!$isOwner) data-owner-locked aria-disabled="true" tabindex="-1" title="Tersedia setelah pembelian mobil YZA terverifikasi" @endif>
            <span class="yza-home-stat-icon"><x-member-home-icon name="calendar" /></span>
            <small>BOOKING AKTIF</small>
            <strong>{{ $activeBookingCount }}</strong>
            <em>{{ $isOwner ? 'Dalam proses layanan' : 'Terkunci hingga pembelian unit' }}</em>
            <b class="yza-home-stat-arrow" aria-hidden="true">›</b>
        </a>
        <a href="#yza-points" data-open-yza-points-modal>
            <span class="yza-home-stat-icon"><x-member-home-icon name="coins" /></span>
            <small>YZA POINTS</small>
            <strong id="yza-points-display">{{ number_format((int) ($pointAccount?->saldo ?? 0), 0, ',', '.') }}</strong>
            <em>Points tersedia</em>
            <b class="yza-home-stat-arrow" aria-hidden="true">›</b>
        </a>
        <a href="{{ route('pelanggan.promo.index') }}">
            <span class="yza-home-stat-icon"><x-member-home-icon name="tag" /></span>
            <small>PROMO MEMBER</small>
            <strong>{{ $activePromoCount }}</strong>
            <em>{{ $isOwner ? 'Promo aktif untuk Anda' : 'Lihat katalog promo owner' }}</em>
            <b class="yza-home-stat-arrow" aria-hidden="true">›</b>
        </a>
    </section>

    {{-- Adaptive Section: Owner Garage & Specs + Odometer vs Non-Owner Purchase Journey --}}
    @if($isOwner)
        <section class="mc-owner-section" aria-labelledby="mc-owner-heading">
            <div class="mc-section-top">
                <div>
                    <h2 id="mc-owner-heading">Garasi &amp; Spesifikasi Unit</h2>
                    <p>Detail kendaraan Anda beserta pencatatan odometer terbaru dari Anda dan mekanik bengkel.</p>
                </div>
                <a class="mc-link-pill" href="{{ route('pelanggan.kendaraan.index') }}">Semua Kendaraan →</a>
            </div>

            <div class="mc-owner-grid">
                @foreach($ownerCards as $card)
                    @php
                        $v = $card['vehicle'];
                        $m = $card['mobil'];
                        $unitName = trim(($v->merk ?: $m?->merk ?: '') . ' ' . ($v->model ?: $m?->model ?: ''));
                        $variantText = $v->tipe ?: $m?->tipe ?: '';
                        $plateText = $v->nomor_polisi ?: 'No. Polisi Belum Diisi';
                        $specs = [
                            ['label' => 'Tahun', 'value' => $v->tahun ?: $m?->tahun ?: '—'],
                            ['label' => 'Warna', 'value' => $card['color'] ?: '—'],
                            ['label' => 'Transmisi', 'value' => ($v->transmisi ?: $m?->transmisi) ? strtoupper($v->transmisi ?: $m?->transmisi) : '—'],
                            ['label' => 'Bahan Bakar', 'value' => ($v->bahan_bakar ?: $m?->bahan_bakar) ? ucfirst($v->bahan_bakar ?: $m?->bahan_bakar) : '—'],
                            ['label' => 'No. Rangka', 'value' => $v->nomor_rangka ?: $m?->nomor_rangka ?: '—', 'mono' => true],
                            ['label' => 'No. Mesin', 'value' => $v->nomor_mesin ?: $m?->nomor_mesin ?: '—', 'mono' => true],
                        ];
                    @endphp
                    <article class="mc-car-card">
                        <div class="mc-car-left">
                            <div class="mc-car-media">
                                <img src="{{ $card['image'] }}" alt="{{ $unitName }}" loading="lazy">
                            </div>
                            <div class="mc-car-identity">
                                <div class="mc-car-title-row">
                                    <div>
                                        <h3>{{ $unitName }}</h3>
                                        @if($variantText)
                                            <span class="mc-car-variant">{{ $variantText }}</span>
                                        @endif
                                    </div>
                                    <span class="mc-car-plate">{{ $plateText }}</span>
                                </div>
                                <div class="mc-car-actions">
                                    <a class="mc-btn mc-btn-primary" href="{{ route('pelanggan.booking-servis.create', ['kendaraan' => $v->id_kendaraan_pelanggan]) }}">Booking Servis</a>
                                    <a class="mc-btn mc-btn-secondary" href="{{ route('pelanggan.servis.index', ['kendaraan' => $v->id_kendaraan_pelanggan, 'from' => 'garage']) }}">Riwayat Servis</a>
                                    <a class="mc-btn mc-btn-ghost" href="{{ route('pelanggan.kendaraan.show', $v) }}">Detail Unit</a>
                                </div>
                            </div>
                        </div>

                        <div class="mc-car-right">
                            <div class="mc-block">
                                <div class="mc-block-head">
                                    <span class="mc-block-label">Spesifikasi Teknis</span>
                                </div>
                                <div class="mc-spec-grid" aria-label="Spesifikasi kendaraan">
                                    @foreach($specs as $spec)
                                        <div class="mc-spec-cell">
                                            <span>{{ $spec['label'] }}</span>
                                            <strong class="{{ !empty($spec['mono']) ? 'is-mono' : '' }}" title="{{ $spec['value'] }}">{{ $spec['value'] }}</strong>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mc-block">
                                <div class="mc-block-head">
                                    <span class="mc-block-label">Odometer &amp; Target Servis</span>
                                    <button type="button" class="mc-guide-link" data-open-service-guide>Panduan Interval KM →</button>
                                </div>
                                <div class="mc-km-strip" aria-label="Pencatatan kilometer kendaraan">
                                    <div class="mc-km-box {{ $card['kmSource'] === 'pengguna' ? 'is-primary' : '' }}">
                                        <div class="mc-km-top">
                                            <span>KM Input Pengguna</span>
                                            <a href="{{ route('pelanggan.kendaraan.edit', $v) }}">Ubah →</a>
                                        </div>
                                        <strong>{{ number_format($card['userKilometer'], 0, ',', '.') }} km</strong>
                                        <small>Tercatat di garasi Anda</small>
                                    </div>

                                    <div class="mc-km-box {{ $card['kmSource'] === 'mekanik' ? 'is-primary' : '' }}">
                                        <div class="mc-km-top">
                                            <span>KM Input Mekanik</span>
                                        </div>
                                        <strong>{{ $card['mechanicKilometer'] !== null ? number_format($card['mechanicKilometer'], 0, ',', '.') . ' km' : 'Belum tercatat' }}</strong>
                                        <small>{{ $card['mechanicName'] ? 'Mekanik: ' . $card['mechanicName'] : ($card['lastDate'] ? 'Servis ' . $card['lastDate']->format('d M Y') : 'Dicatat saat servis bengkel') }}</small>
                                    </div>

                                    <button type="button" class="mc-km-box mc-km-target" data-open-service-guide title="Lihat panduan interval servis">
                                        <div class="mc-km-top">
                                            <span>Target Servis Berikutnya</span>
                                        </div>
                                        <strong>{{ $card['nextMilestoneLabel'] }}</strong>
                                        <small>Acuan: {{ number_format($card['currentKilometer'], 0, ',', '.') }} km ({{ $card['kmSourceLabel'] }})</small>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @else
        <section class="mc-onboard-section" aria-labelledby="mc-onboard-heading">
            <div class="mc-section-top">
                <div>
                    <h2 id="mc-onboard-heading">Mulai Kepemilikan YZA Anda</h2>
                    <p>Selesaikan pembelian unit pertama Anda untuk membuka garasi digital, pengingat servis otomatis, dan seluruh kupon owner.</p>
                </div>
                <a class="mc-link-pill" href="{{ route('pelanggan.katalog-mobil.index') }}">Jelajahi Katalog →</a>
            </div>

            @if($purchaseItem)
                @php
                    $isOrder = $purchaseItem instanceof \App\Models\PesananMobilMember;
                    $itemCode = $isOrder ? ($purchaseItem->nomor_pesanan ?: 'PESANAN') : ($purchaseItem->kode_preorder ?: 'PRE-ORDER');
                    $itemCar = $isOrder
                        ? trim(($purchaseItem->snapshot_merk ?: $purchaseItem->mobil?->merk ?: '') . ' ' . ($purchaseItem->snapshot_model ?: $purchaseItem->mobil?->model ?: ''))
                        : trim(($purchaseItem->mobil?->merk ?: '') . ' ' . ($purchaseItem->mobil?->model ?: ''));
                    $itemStatus = ucwords(str_replace('_', ' ', (string) $purchaseItem->status));
                    $itemUrl = $isOrder ? route('pelanggan.pesanan-mobil.show', $purchaseItem) : route('pelanggan.preorder.index');
                @endphp
                <div class="mc-active-order">
                    <div>
                        <span class="mc-order-code">{{ $itemCode }}</span>
                        <strong>{{ $itemCar ?: 'Unit Pilihan Anda' }}</strong>
                        <small>Status saat ini: {{ $itemStatus }}</small>
                    </div>
                    <a class="mc-btn mc-btn-primary" href="{{ $itemUrl }}">Pantau Proses →</a>
                </div>
            @endif

            <div class="mc-steps-grid">
                <a class="mc-step-card" href="{{ route('pelanggan.katalog-mobil.index') }}">
                    <span class="mc-step-num">01</span>
                    <div>
                        <strong>Pilih Unit di Katalog</strong>
                        <p>Bandingkan spesifikasi, varian warna, dan harga OTR unit tersedia.</p>
                    </div>
                </a>
                <a class="mc-step-card" href="{{ route('pelanggan.test-drive.index') }}">
                    <span class="mc-step-num">02</span>
                    <div>
                        <strong>Test Drive atau Pre-Order</strong>
                        <p>Rasakan langsung performa kendaraan atau ajukan pemesanan unit khusus.</p>
                    </div>
                </a>
                <a class="mc-step-card" href="{{ route('pelanggan.pesanan-mobil.index') }}">
                    <span class="mc-step-num">03</span>
                    <div>
                        <strong>Verifikasi &amp; Akses Owner</strong>
                        <p>Setelah serah terima selesai, garasi, servis, car wash, dan kupon langsung terbuka.</p>
                    </div>
                </a>
            </div>
        </section>
    @endif

    <section class="yza-home-lower-grid {{ !$isOwner ? 'is-single-col' : '' }}" aria-label="Aktivitas dan pengingat member">
        <article class="yza-home-panel yza-home-image-panel yza-home-activity-panel">
            <div class="yza-home-section-head">
                <div><h2>Aktivitas &amp; Jadwal Anda</h2></div>
                @if($isOwner)
                    <a href="{{ route('pelanggan.booking.index') }}">Lihat Semua <b>→</b></a>
                @endif
            </div>
            @forelse($upcomingActivities->take(3) as $activity)
                <div class="yza-home-activity">
                    <span>{{ strtoupper($activity['type']) }}</span>
                    <div>
                        <strong>{{ $activity['vehicle'] ?: $activity['type'] }}</strong>
                        <small>{{ $activity['code'] }} · {{ optional($activity['date'])->format('d M Y') }}</small>
                    </div>
                    <b>{{ $activity['statusLabel'] }}</b>
                </div>
            @empty
                <div class="yza-home-panel-empty">
                    <strong>Belum ada aktivitas mendatang.</strong>
                    <span>{{ $isOwner ? 'Booking dan jadwal layanan Anda akan tampil di sini.' : 'Jadwal test drive dan pesanan unit Anda akan tampil di sini.' }}</span>
                </div>
            @endforelse
            @if($isOwner)
                <a class="yza-home-panel-button" href="{{ route('pelanggan.booking.index') }}">Lihat Semua <b>→</b></a>
            @endif
        </article>

        @if($isOwner)
            <article class="yza-home-panel yza-home-image-panel yza-home-reminder-panel">
                <button type="button" class="yza-guide-btn" data-open-service-guide aria-label="Panduan servis berkala" title="Panduan Servis Berkala (KM)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10" style="display:none"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                </button>
                <div class="yza-home-section-head">
                    <div><h2>Pengingat Servis</h2></div>
                    <a href="{{ route('pelanggan.pengingat-servis') }}">Lihat Semua <b>→</b></a>
                </div>
                @forelse(collect($reminders)->take(3) as $reminder)
                    @php
                        $remVehicle = $reminder['vehicle'];
                        $remStatus = $reminder['status'] ?? 'no_history';
                        $remActive = $reminder['activeBooking'] ?? null;
                        $remFulfilled = !empty($reminder['milestoneFulfilled']);
                        $remTone = match (true) {
                            $remStatus === 'overdue' => 'is-urgent',
                            $remStatus === 'due_soon' || $remStatus === 'booked_pending' => 'is-soon',
                            $remStatus === 'booked_confirmed' || $remStatus === 'in_service' || $remFulfilled => 'is-fulfilled',
                            default => 'is-normal',
                        };
                        $remFulfilledKm = $reminder['fulfilledKm'] ?? null;
                        $remSubtext = match (true) {
                            $remActive !== null => trim(implode(' · ', array_filter([
                                $reminder['bookingServiceName'] ?? 'Servis Berkala',
                                !empty($reminder['bookingDate']) ? $reminder['bookingDate']->format('d M Y') : null,
                                $reminder['bookingCode'] ?? null,
                            ]))),
                            $reminder['nextDate'] !== null => 'Jadwal berikutnya · ' . $reminder['nextDate']->format('d M Y'),
                            default => $reminder['nextMilestoneLabel'] ?? 'Jadwalkan pemeriksaan berkala',
                        };
                        $remActionUrl = $remActive
                            ? ($reminder['bookingUrl'] ?? route('pelanggan.booking-servis.index'))
                            : route('pelanggan.booking-servis.create', ['kendaraan' => $remVehicle->id_kendaraan_pelanggan]);
                        $remActionLabel = $remActive ? 'Lihat Booking →' : 'Jadwalkan →';
                    @endphp
                    <div class="yza-home-reminder yza-rem-clean">
                        <div class="yza-rem-clean-main">
                            <div class="yza-rem-clean-head">
                                <strong>{{ $remVehicle->merk }} {{ $remVehicle->model }}</strong>
                                @if($remFulfilled && $remFulfilledKm)
                                    <span class="yza-rem-solid-badge">{{ number_format($remFulfilledKm, 0, ',', '.') }} KM TERPENUHI</span>
                                @endif
                            </div>
                            <small>{{ $remSubtext }}</small>
                        </div>
                        <div class="yza-rem-clean-right">
                            <span class="yza-rem-clean-status {{ $remTone }}">{{ $reminder['statusLabel'] }}</span>
                            <a class="yza-rem-clean-cta" href="{{ $remActionUrl }}">{{ $remActionLabel }}</a>
                        </div>
                    </div>
                @empty
                    <div class="yza-home-panel-empty"><strong>Belum ada pengingat servis.</strong><span>Pengingat muncul setelah riwayat servis tercatat.</span></div>
                @endforelse
                <a class="yza-home-panel-button" href="{{ route('pelanggan.pengingat-servis') }}">Lihat Semua <b>→</b></a>
            </article>
        @endif
    </section>

    <section class="yza-home-section yza-home-services-section mc-coupons-section {{ !$isOwner ? 'is-coupon-locked' : '' }}" aria-labelledby="yza-home-services-title">
        <div class="mc-coupons-shell">
        @if(!$isOwner)
            <div class="mc-coupon-overlay" role="alert">
                <div class="mc-coupon-overlay-card">
                    <strong>Kupon Owner Terkunci</strong>
                    <p>Penukaran poin untuk kupon servis dan car wash tersedia setelah pembelian mobil YZA Anda terverifikasi.</p>
                    <a href="{{ route('pelanggan.katalog-mobil.index') }}">Lihat Koleksi Mobil →</a>
                </div>
            </div>
        @endif
        <div class="mc-coupons-head">
            <div>
                <h2 id="yza-home-services-title" aria-label="Kupon Promo Eksklusif Member">Kupon Promo Poin YZA</h2>
                <p>Tukarkan saldo YZA Points Anda dengan voucher servis dan car wash resmi.</p>
            </div>
            <div class="mc-coupons-head-right">
                <span class="mc-coupons-balance">
                    <x-member-home-icon name="coins" />
                    Saldo: <strong>{{ number_format($customer->yza_points ?? 0, 0, ',', '.') }} Poin</strong>
                </span>
                <a class="mc-coupons-all" href="{{ route('pelanggan.promo.index') }}">Semua Promo →</a>
            </div>
        </div>
        <div class="mc-coupons-grid">
            @foreach($coupons as $coupon)
                <article class="mc-voucher-card">
                    <div class="mc-voucher-media">
                        <img src="{{ asset($coupon['image']) }}" alt="{{ $coupon['title'] }}" loading="lazy">
                        <div class="mc-voucher-media-top">
                            <span class="mc-voucher-code">{{ $coupon['code'] }}</span>
                            <span class="mc-voucher-pts">
                                <x-member-home-icon name="coins" />
                                {{ number_format($coupon['points'], 0, ',', '.') }} Poin
                            </span>
                        </div>
                    </div>
                    <div class="mc-voucher-divider" aria-hidden="true"></div>
                    <div class="mc-voucher-body">
                        <div>
                            <h3>{{ $coupon['title'] }}</h3>
                            <p>{{ $coupon['copy'] }}</p>
                        </div>
                        <button type="button"
                                class="mc-voucher-btn"
                                @if(!$isOwner) disabled tabindex="-1" @else
                                data-claim-coupon
                                data-promo-id="{{ $coupon['id'] }}"
                                data-promo-code="{{ $coupon['code'] }}"
                                data-promo-title="{{ $coupon['title'] }}"
                                data-promo-points="{{ $coupon['points'] }}"
                                data-claim-url="{{ route('pelanggan.promo.claim', $coupon['id']) }}" @endif>
                            <span>Tukar {{ number_format($coupon['points'], 0, ',', '.') }} Poin</span>
                            <span aria-hidden="true">→</span>
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
        </div>
    </section>

    @php
        $catalogCars = app(\App\Http\Controllers\Public\MobilController::class)->sportShowcases()->take(10)->values();
    @endphp
    <section class="yza-bks-showcase" aria-labelledby="yza-home-garage-title">
        <div class="yza-bks-container">
            <div class="yza-bks-head">
                <div>
                    <h2 id="yza-home-garage-title">Barangkali Kamu Suka</h2>
                    <p>Kurasi unit pilihan yang selaras dengan gaya berkendara Anda.</p>
                </div>
                <div class="yza-bks-actions">
                    <div class="yza-bks-nav" aria-label="Navigasi daftar mobil">
                        <button type="button" class="yza-bks-nav-btn" data-bks-prev aria-label="Sebelumnya">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        </button>
                        <button type="button" class="yza-bks-nav-btn" data-bks-next aria-label="Berikutnya">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                    </div>
                    <a class="yza-bks-all" aria-label="Lihat Semua Mobil di Koleksi Mobil YZA" href="{{ route('pelanggan.katalog-mobil.index') }}">
                        <span>Lihat Semua</span>
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </div>

            <div class="yza-bks-carousel" data-member-sport-carousel data-carousel-count="{{ ($catalogCars ?? collect())->count() }}">
                <div class="yza-bks-viewport" data-member-carousel-viewport>
                    <div class="yza-bks-track" data-member-carousel-track>
                        @forelse(($catalogCars ?? collect()) as $car)
                            @php
                                $garageTitle = trim($car->merk.' '.$car->model);
                                $garageUrl = $car->showcase_slug
                                    ? route('pelanggan.katalog-mobil.showcase', ['slug' => $car->showcase_slug, 'source' => 'member-home', 'return_to' => request()->getRequestUri()])
                                    : route('pelanggan.katalog-mobil.show', ['mobil' => $car, 'source' => 'member-home', 'return_to' => request()->getRequestUri()]);
                                $garagePhoto = $car->image ?? optional($car->primaryPhoto())->url();
                                $garageSpec = trim(($car->tahun ?: '-').' · '.($car->tipe ?: 'Performance').' · '.ucfirst($car->transmisi ?: 'Automatic'));
                            @endphp
                            <article class="yza-bks-card featured-card">
                                <div class="yza-bks-media">
                                    <a href="{{ $garageUrl }}" class="yza-bks-media-link" tabindex="-1">
                                        <img src="{{ $garagePhoto }}" alt="{{ $garageTitle }}" loading="lazy" draggable="false">
                                    </a>
                                    <button class="yza-bks-fav" type="button" data-member-favorite="{{ $garageUrl }}" data-member-favorite-name="{{ $garageTitle }}" aria-label="Tambahkan {{ $garageTitle }} ke favorit" aria-pressed="false">♡</button>
                                </div>
                                <div class="yza-bks-body">
                                    <a class="yza-bks-info" href="{{ $garageUrl }}">
                                        <span class="yza-bks-meta">{{ $garageSpec }}</span>
                                        <h3>{{ $garageTitle }}</h3>
                                    </a>
                                    <div class="yza-bks-foot">
                                        <a class="yza-bks-price" href="{{ $garageUrl }}">
                                            <span>Harga OTR</span>
                                            <strong>{{ (float) $car->harga_jual > 0 ? 'Rp '.number_format((float) $car->harga_jual, 0, ',', '.') : 'Hubungi Kami' }}</strong>
                                        </a>
                                        <a class="yza-bks-go" href="{{ $garageUrl }}" aria-label="Lihat detail {{ $garageTitle }}">→</a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="yza-bks-empty">Belum ada mobil rekomendasi yang tersedia saat ini.</div>
                        @endforelse
                    </div>
                </div>
                <div class="yza-bks-footer">
                    <div class="yza-bks-progress" data-member-carousel-progress></div>
                    <span class="yza-bks-counter" data-member-carousel-counter>01 / {{ str_pad((string) max(1, ($catalogCars ?? collect())->count()), 2, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Kupon Claim Modal -->
    <div id="yza-claim-modal" class="yza-claim-modal-backdrop" hidden>
        <div class="yza-claim-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="yza-claim-modal-title">
            <button type="button" class="yza-claim-modal-close" data-close-claim-modal aria-label="Tutup modal">✕</button>

            <div id="yza-claim-step-confirm">
                <div class="yza-claim-modal-icon">🎟️</div>
                <h3 id="yza-claim-modal-title">Tukarkan Kupon Promo?</h3>
                <p>Gunakan YZA Points Anda untuk mendapatkan kupon eksklusif ini.</p>

                <div class="yza-claim-modal-box">
                    <div class="yza-claim-modal-row">
                        <span>Nama Kupon</span>
                        <strong id="modal-coupon-title">-</strong>
                    </div>
                    <div class="yza-claim-modal-row">
                        <span>Kode Promo</span>
                        <strong id="modal-coupon-code" style="color: #ff6874; font-family: monospace;">-</strong>
                    </div>
                    <div class="yza-claim-modal-row">
                        <span>Biaya Poin</span>
                        <strong id="modal-coupon-cost" class="highlight-cost">-</strong>
                    </div>
                    <div class="yza-claim-modal-row">
                        <span>Saldo Poin Anda</span>
                        <strong id="modal-current-points" class="highlight-balance">{{ number_format((int) ($pointAccount?->saldo ?? 0), 0, ',', '.') }} Poin</strong>
                    </div>
                </div>

                <div id="modal-claim-error" style="display: none; color: #f87171; font-size: 13px; margin-bottom: 16px; font-weight: 600;"></div>

                <div class="yza-claim-modal-actions">
                    <button type="button" class="btn-cancel" data-close-claim-modal>Batal</button>
                    <button type="button" class="btn-confirm" id="btn-submit-claim">Konfirmasi Tukar</button>
                </div>
            </div>

            <div id="yza-claim-step-success" style="display: none;">
                <div class="yza-claim-modal-icon" style="background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #16a34a;">✓</div>
                <h3>Kupon Berhasil Diklaim!</h3>
                <p id="modal-success-msg">Kupon promo siap digunakan saat checkout layanan atau pembelian mobil.</p>

                <div class="yza-claim-modal-code-box">
                    <strong id="modal-success-code">KODE</strong>
                    <button type="button" class="yza-claim-copy-btn" id="btn-copy-code">Salin Kode</button>
                </div>

                <div class="yza-claim-modal-box" style="margin-bottom: 20px;">
                    <div class="yza-claim-modal-row">
                        <span>Poin Digunakan</span>
                        <strong id="modal-success-deducted" class="highlight-cost">-</strong>
                    </div>
                    <div class="yza-claim-modal-row">
                        <span>Sisa Poin Anda</span>
                        <strong id="modal-success-balance" class="highlight-balance">-</strong>
                    </div>
                </div>

                <div class="yza-claim-modal-actions">
                    <a href="{{ route('pelanggan.promo.index') }}" class="btn-confirm" style="width: 100%;">Lihat Semua Kupon Saya</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Minimalist Modern Overrides & Adaptive Owner/Non-Owner Cards */
.yza-home.yza-home-reference.yza-mc-modern{--member-gutter:clamp(16px,2.8vw,48px);display:flex!important;flex-direction:column!important;gap:clamp(24px,2.6vw,34px)!important;padding:0 0 clamp(48px,5vw,72px) 0!important}
.yza-mc-modern .yza-home-hero{padding:clamp(30px,3.6vw,50px) var(--member-gutter) clamp(56px,5vw,74px)!important;grid-template-columns:minmax(0,1fr) minmax(290px,380px)!important;gap:clamp(20px,3vw,40px)!important;margin:0!important}
.yza-mc-modern .yza-home-stats{margin-top:calc(clamp(24px,2.6vw,34px) * -1 - 28px)!important;margin-bottom:0!important;padding:0 var(--member-gutter)!important;gap:14px!important}
.yza-mc-modern .yza-home-lower-grid{margin:0!important;padding:0 var(--member-gutter)!important;gap:18px!important}
.yza-mc-modern .yza-bks-showcase{margin:0!important;padding:0 var(--member-gutter)!important}
.yza-mc-modern .yza-bks-container{width:100%!important;max-width:100%!important;margin:0!important;padding:24px 26px!important;border-radius:22px!important;box-sizing:border-box!important}
.yza-card-status-minimal{padding:4px 10px;border-radius:999px;background:rgba(16,185,129,.16);color:#6ee7b7;font-size:10.5px;font-weight:800;letter-spacing:.08em}
.yza-home-stats>a[data-owner-locked]::after{display:none!important}
.yza-home-stats>a[data-owner-locked] .yza-home-stat-arrow{opacity:.35;font-size:16px}

.mc-owner-section,.mc-onboard-section{width:100%!important;margin:0!important;padding:0 var(--member-gutter)!important;box-sizing:border-box!important;display:flex!important;flex-direction:column!important;gap:16px!important}
.mc-section-top{display:flex!important;align-items:flex-end!important;justify-content:space-between!important;gap:16px!important;flex-wrap:wrap!important}
.mc-section-top h2{margin:0!important;color:#0f172a!important;font-size:clamp(22px,2.1vw,28px)!important;font-weight:800!important;letter-spacing:-.025em!important;line-height:1.2!important}
.mc-section-top p{margin:4px 0 0!important;color:#64748b!important;font-size:13.5px!important;line-height:1.45!important}
.mc-link-pill,.mc-link-pill:visited{display:inline-flex!important;align-items:center!important;height:38px!important;padding:0 16px!important;border-radius:999px!important;border:1px solid #cbd5e1!important;background:#fff!important;color:#0f172a!important;font-size:12.5px!important;font-weight:700!important;text-decoration:none!important;transition:border-color .15s,background .15s!important}
.mc-link-pill:hover{border-color:#0f172a!important;background:#f8fafc!important}

/* Clean Modern Member Home Refinement */
.yza-mc-modern .yza-home-panel{border-radius:20px!important}
.yza-mc-modern .yza-bks-showcase{border-radius:22px;margin-top:8px}
.mc-owner-grid{display:flex!important;flex-direction:column!important;gap:18px!important}
.mc-car-card{display:grid!important;grid-template-columns:360px minmax(0,1fr)!important;gap:24px!important;padding:22px!important;border-radius:22px!important;background:#fff!important;border:1px solid #e2e8f0!important;box-shadow:0 12px 32px rgba(15,23,42,.05)!important;align-items:start!important;box-sizing:border-box!important;overflow:hidden!important}
.mc-car-left{display:flex!important;flex-direction:column!important;gap:14px!important;min-width:0!important;width:100%!important}
.mc-car-media{width:100%!important;height:206px!important;border-radius:16px!important;overflow:hidden!important;background:#0f172a!important;border:1px solid #e2e8f0!important;position:relative!important;flex-shrink:0!important}
.mc-car-media img{width:100%!important;height:100%!important;max-width:100%!important;object-fit:cover!important;display:block!important}
.mc-car-identity{display:flex!important;flex-direction:column!important;gap:12px!important}
.mc-car-title-row{display:flex!important;align-items:flex-start!important;justify-content:space-between!important;gap:10px!important}
.mc-car-title-row h3{margin:0!important;color:#0f172a!important;font-size:20px!important;font-weight:800!important;letter-spacing:-.02em!important;line-height:1.25!important}
.mc-car-variant{display:block!important;margin-top:3px!important;color:#64748b!important;font-size:12.5px!important;font-weight:600!important}
.mc-car-plate{flex-shrink:0!important;padding:5px 10px!important;border-radius:8px!important;background:#f1f5f9!important;border:1px solid #cbd5e1!important;color:#0f172a!important;font-family:ui-monospace,SFMono-Regular,Menlo,monospace!important;font-size:11.5px!important;font-weight:700!important;letter-spacing:.03em!important}

.mc-car-right{display:flex!important;flex-direction:column!important;gap:16px!important;min-width:0!important;width:100%!important}
.mc-block{display:flex!important;flex-direction:column!important;gap:8px!important}
.mc-block-head{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:10px!important}
.mc-block-label{font-size:11px!important;font-weight:800!important;letter-spacing:.08em!important;text-transform:uppercase!important;color:#64748b!important}
.mc-guide-link{border:0!important;background:transparent!important;padding:0!important;color:#e11d48!important;font-size:12px!important;font-weight:700!important;cursor:pointer!important}
.mc-guide-link:hover{text-decoration:underline!important}

.mc-spec-grid{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:10px!important}
.mc-spec-cell{padding:11px 14px!important;border-radius:12px!important;background:#f8fafc!important;border:1px solid #e2e8f0!important;min-width:0!important}
.mc-spec-cell span{display:block!important;font-size:11px!important;font-weight:600!important;color:#64748b!important;margin-bottom:3px!important;line-height:1.2!important}
.mc-spec-cell strong{display:block!important;font-size:13.5px!important;font-weight:800!important;color:#0f172a!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;line-height:1.3!important}
.mc-spec-cell strong.is-mono{font-family:ui-monospace,SFMono-Regular,Menlo,monospace!important;font-size:12.5px!important}

.mc-km-strip{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:10px!important}
.mc-km-box{padding:13px 15px!important;border-radius:14px!important;border:1px solid #e2e8f0!important;background:#f8fafc!important;text-align:left!important;display:flex!important;flex-direction:column!important;justify-content:space-between!important;gap:4px!important;min-width:0!important;box-sizing:border-box!important}
.mc-km-box.is-primary{border-color:#cbd5e1!important;background:#ffffff!important;box-shadow:0 4px 12px rgba(15,23,42,.04)!important}
.mc-km-box.mc-km-target{cursor:pointer!important;background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%)!important;border-color:#0f172a!important;color:#fff!important;transition:transform .15s!important}
.mc-km-box.mc-km-target:hover{transform:translateY(-1px)!important}
.mc-km-top{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:8px!important;width:100%!important}
.mc-km-top span{font-size:11px!important;font-weight:700!important;color:#64748b!important;line-height:1.2!important}
.mc-km-target .mc-km-top span{color:#cbd5e1!important}
.mc-km-top a,.mc-km-top a:visited{font-size:11.5px!important;font-weight:800!important;color:#e11d48!important;text-decoration:none!important}
.mc-km-top a:hover{text-decoration:underline!important}
.mc-km-box strong{font-size:17px!important;font-weight:800!important;color:#0f172a!important;line-height:1.25!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important}
.mc-km-target strong{color:#fff!important;font-size:15px!important}
.mc-km-box small{font-size:11px!important;color:#64748b!important;line-height:1.35!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important}
.mc-km-target small{color:#fda4af!important}

.mc-car-actions{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:8px!important}
.mc-btn{display:inline-flex!important;align-items:center!important;justify-content:center!important;height:38px!important;padding:0 10px!important;border-radius:10px!important;font-size:12.5px!important;font-weight:700!important;text-decoration:none!important;white-space:nowrap!important;transition:transform .15s,background .15s!important}
.mc-btn:hover{transform:translateY(-1px)!important}
.mc-btn-primary,.mc-btn-primary:visited{background:#e11d48!important;color:#fff!important;border:1px solid #e11d48!important}
.mc-btn-primary:hover{background:#be123c!important;border-color:#be123c!important}
.mc-btn-secondary,.mc-btn-secondary:visited{background:#0f172a!important;color:#fff!important;border:1px solid #0f172a!important}
.mc-btn-secondary:hover{background:#1e293b!important}
.mc-btn-ghost,.mc-btn-ghost:visited{background:#fff!important;border:1px solid #cbd5e1!important;color:#0f172a!important}
.mc-btn-ghost:hover{background:#f8fafc!important}

/* Non-owner Onboarding Section */
.mc-active-order{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;padding:18px 22px;border-radius:18px;background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);color:#fff}
.mc-order-code{display:inline-block;padding:3px 9px;border-radius:7px;background:rgba(255,255,255,.12);font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11.5px;font-weight:700;color:#fda4af;margin-bottom:4px}
.mc-active-order strong{display:block;font-size:17px;font-weight:800;color:#fff}
.mc-active-order small{display:block;font-size:12.5px;color:#cbd5e1;margin-top:2px}

.mc-steps-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
.mc-step-card{display:flex;align-items:flex-start;gap:14px;padding:20px;border-radius:18px;background:#fff;border:1px solid #e2e8f0;text-decoration:none;transition:border-color .15s,box-shadow .15s,transform .15s}
.mc-step-card:hover{border-color:#0f172a;transform:translateY(-2px);box-shadow:0 12px 26px rgba(15,23,42,.06)}
.mc-step-num{width:34px;height:34px;border-radius:10px;background:#f1f5f9;color:#0f172a;font-size:13px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0}
.mc-step-card:hover .mc-step-num{background:#e11d48;color:#fff}
.mc-step-card strong{display:block;font-size:15px;font-weight:800;color:#0f172a}
.mc-step-card p{margin:4px 0 0;font-size:13px;color:#64748b;line-height:1.45}

/* Clean Pengingat Servis (No KM Clutter) */
.yza-home-lower-grid.is-single-col{grid-template-columns:1fr!important}
.yza-home-reminder-panel{position:relative!important}
.yza-guide-btn{position:absolute!important;top:20px!important;right:20px!important;z-index:5!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;width:36px!important;height:36px!important;min-width:36px!important;min-height:36px!important;max-width:36px!important;max-height:36px!important;padding:0!important;margin:0!important;aspect-ratio:1/1!important;border:1px solid rgba(255,255,255,.32)!important;border-radius:999px!important;background:rgba(15,23,42,.68)!important;backdrop-filter:blur(8px);color:#fff!important;cursor:pointer;transition:transform .16s ease,background .16s ease,border-color .16s ease,box-shadow .16s ease}
.yza-guide-btn:hover{border-color:#ff2d46!important;background:#e31d35!important;transform:scale(1.06);box-shadow:0 8px 20px rgba(227,29,53,.35)}
.yza-home-reminder.yza-rem-clean{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:14px!important;padding:14px 0!important;border-top:1px solid rgba(255,255,255,.12)!important}
.yza-rem-clean-main{min-width:0}
.yza-rem-clean-head{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.yza-rem-clean-main strong{display:block;color:#fff!important;font-size:15px!important;font-weight:800!important}
.yza-rem-solid-badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:999px;background:#10b981;color:#052e16;font-size:10.5px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;line-height:1.2}
.yza-rem-clean-main small{display:block;margin-top:4px;color:#94a3b8!important;font-size:12px!important}
.yza-rem-clean-right{display:flex;align-items:center;gap:10px;flex-shrink:0}
.yza-rem-clean-status{font-size:11.5px;font-weight:700;padding:4px 10px;border-radius:999px}
.yza-rem-clean-status.is-normal{background:rgba(255,255,255,.1);color:#e2e8f0}
.yza-rem-clean-status.is-soon{background:rgba(245,158,11,.2);color:#fcd34d}
.yza-rem-clean-status.is-fulfilled{background:rgba(16,185,129,.22);color:#6ee7b7}
.yza-rem-clean-status.is-urgent{background:rgba(225,29,72,.25);color:#fda4af}
.yza-rem-clean-cta,.yza-rem-clean-cta:visited{color:#fff!important;font-size:12px!important;font-weight:700!important;text-decoration:none!important;padding:6px 10px;border-radius:8px;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.06)}
.yza-rem-clean-cta:hover{background:#e11d48;border-color:#e11d48}

/* Modern Coupon Voucher Section */
.yza-mc-modern .mc-coupons-section{margin:0!important;padding:0 var(--member-gutter)!important;border:0!important;border-radius:0!important;background:transparent!important;box-shadow:none!important}
.mc-coupons-shell{position:relative;padding:24px 26px;border:1px solid #e2e8f0;border-radius:22px;background:#fff;box-shadow:0 12px 32px rgba(15,23,42,.05);box-sizing:border-box}
.mc-coupons-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px}
.mc-coupons-head h2{margin:0;font-size:22px;font-weight:800;color:#0f172a;letter-spacing:-.02em}
.mc-coupons-head p{margin:4px 0 0;font-size:13.5px;color:#64748b}
.mc-coupons-head-right{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.mc-coupons-balance{display:inline-flex;align-items:center;gap:6px;padding:8px 13px;border-radius:999px;background:#f8fafc;border:1px solid #e2e8f0;color:#334155;font-size:12.5px;font-weight:600}
.mc-coupons-balance svg{width:15px;height:15px;color:#e11d48}
.mc-coupons-balance strong{color:#0f172a;font-weight:800}
.mc-coupons-all,.mc-coupons-all:visited{display:inline-flex;align-items:center;padding:8px 14px;border-radius:999px;border:1px solid #e2e8f0;background:#fff;color:#0f172a!important;font-size:12.5px;font-weight:700;text-decoration:none;transition:border-color .16s ease,background .16s ease}
.mc-coupons-all:hover{border-color:#0f172a;background:#f8fafc}

.mc-coupons-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
.mc-voucher-card{display:flex;flex-direction:column;border-radius:18px;border:1px solid #e2e8f0;background:#fff;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease}
.mc-voucher-card:hover{transform:translateY(-3px);border-color:#cbd5e1;box-shadow:0 16px 34px rgba(15,23,42,.09)}
.mc-voucher-media{position:relative;height:132px;background:#0f172a;overflow:hidden}
.mc-voucher-media img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .35s ease}
.mc-voucher-card:hover .mc-voucher-media img{transform:scale(1.04)}
.mc-voucher-media::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(15,23,42,.58) 0%,rgba(15,23,42,.08) 55%,rgba(15,23,42,.42) 100%)}
.mc-voucher-media-top{position:absolute;top:11px;left:11px;right:11px;z-index:2;display:flex;align-items:center;justify-content:space-between;gap:8px}
.mc-voucher-code{padding:4px 9px;border-radius:7px;background:rgba(15,23,42,.78);backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,.16);color:#f8fafc;font-size:10.5px;font-weight:800;letter-spacing:.06em;font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
.mc-voucher-pts{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:999px;background:#fff;color:#0f172a;font-size:11px;font-weight:800;box-shadow:0 4px 12px rgba(0,0,0,.2)}
.mc-voucher-pts svg{width:12px;height:12px;color:#e11d48}

.mc-voucher-divider{position:relative;height:0;border-top:1.5px dashed #e2e8f0;margin:0 12px}
.mc-voucher-divider::before,.mc-voucher-divider::after{content:"";position:absolute;top:-7px;width:13px;height:13px;border-radius:50%;background:#fff;border:1px solid #e2e8f0}
.mc-voucher-divider::before{left:-19px}
.mc-voucher-divider::after{right:-19px}

.mc-voucher-body{flex:1;display:flex;flex-direction:column;justify-content:space-between;gap:14px;padding:15px 16px 16px}
.mc-voucher-body h3{margin:0;font-size:15.5px;font-weight:800;color:#0f172a;line-height:1.3;letter-spacing:-.01em}
.mc-voucher-body p{margin:5px 0 0;font-size:12.5px;color:#64748b;line-height:1.45;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.mc-voucher-btn{display:flex;align-items:center;justify-content:space-between;width:100%;padding:10px 14px;border:0;border-radius:11px;background:#0f172a;color:#fff;font-size:12.5px;font-weight:700;cursor:pointer;transition:background .16s ease,transform .16s ease}
.mc-voucher-btn:hover:not(:disabled){background:#e11d48}
.mc-voucher-btn:active:not(:disabled){transform:scale(.98)}
.mc-voucher-btn:disabled{opacity:.45;cursor:not-allowed}

/* Locked Coupon Section for Non-Owner */
.yza-home-services-section.is-coupon-locked{position:relative!important}
.yza-home-services-section.is-coupon-locked .mc-coupons-head,
.yza-home-services-section.is-coupon-locked .mc-coupons-grid{opacity:.25;pointer-events:none;user-select:none;filter:grayscale(.35)}
.mc-coupon-overlay{position:absolute;inset:0;z-index:20;display:flex;align-items:center;justify-content:center;padding:24px;pointer-events:auto}
.mc-coupon-overlay-card{width:min(100%,440px);padding:24px 26px;border-radius:20px;background:#fff;border:1px solid #e2e8f0;box-shadow:0 22px 54px rgba(15,23,42,.22);text-align:center}
.mc-coupon-overlay-card strong{display:block;font-size:17px;font-weight:800;color:#0f172a}
.mc-coupon-overlay-card p{margin:6px 0 16px;font-size:13px;color:#475569;line-height:1.5}
.mc-coupon-overlay-card a,.mc-coupon-overlay-card a:visited{display:inline-flex;align-items:center;justify-content:center;padding:10px 18px;border-radius:11px;background:#0f172a;color:#fff!important;font-size:13px;font-weight:700;text-decoration:none}
.mc-coupon-overlay-card a:hover{background:#e11d48}

@media(max-width:1100px){
    .mc-coupons-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}
    .yza-mc-modern .yza-home-stats{grid-template-columns:repeat(2,minmax(0,1fr))!important}
}
@media(max-width:980px){
    .yza-mc-modern .yza-home-hero{grid-template-columns:1fr!important;padding:26px var(--member-gutter) 52px!important;gap:22px!important}
    .yza-mc-modern .yza-home-member-card{max-width:420px;width:100%}
    .yza-mc-modern .yza-home-lower-grid{grid-template-columns:1fr!important}
    .mc-car-card{grid-template-columns:1fr!important;gap:18px!important}
    .mc-steps-grid{grid-template-columns:1fr!important}
}
@media(max-width:640px){
    .yza-home.yza-home-reference.yza-mc-modern{--member-gutter:14px;gap:18px!important;padding-bottom:16px!important}
    .yza-mc-modern .yza-home-hero{padding:20px var(--member-gutter) 40px!important;gap:16px!important}
    .yza-mc-modern .yza-home-hero-actions{gap:8px!important}
    .yza-mc-modern .yza-home-hero-actions a{flex:1 1 auto;min-height:40px;padding:8px 12px;font-size:12.5px}
    .yza-mc-modern .yza-home-member-card{min-height:175px!important;padding:16px!important}
    .yza-mc-modern .yza-home-stats{grid-template-columns:repeat(2,minmax(0,1fr))!important;margin-top:-28px!important;gap:10px!important}
    .yza-mc-modern .yza-home-stats>a{grid-template-columns:38px minmax(0,1fr)!important;column-gap:10px!important;min-height:76px!important;padding:11px 12px!important;border-radius:14px!important}
    .yza-mc-modern .yza-home-stat-icon{width:38px!important;height:38px!important;border-radius:10px!important}
    .yza-mc-modern .yza-home-stat-icon svg{width:20px!important;height:20px!important}
    .yza-mc-modern .yza-home-stats small{font-size:9.5px!important}
    .yza-mc-modern .yza-home-stats strong{font-size:20px!important}
    .yza-mc-modern .yza-home-stats em{font-size:10.5px!important;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .yza-mc-modern .yza-home-stat-arrow{display:none!important}
    .yza-mc-modern .yza-home-lower-grid .yza-home-panel{padding:18px 16px!important}
    .mc-car-card,.mc-coupons-shell,.yza-mc-modern .yza-bks-container{padding:16px!important;border-radius:18px!important}
    .mc-car-media{height:168px!important}
    .mc-coupons-head,.mc-section-top{align-items:flex-start!important;gap:10px!important}
    .mc-spec-grid,.mc-km-strip,.mc-car-actions{grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:8px!important}
    .mc-km-target,.mc-car-actions .mc-btn:first-child{grid-column:1 / -1!important}
    .mc-spec-cell{padding:9px 11px!important}
    .mc-coupons-grid{display:flex!important;overflow-x:auto!important;scroll-snap-type:x mandatory!important;gap:12px!important;padding-bottom:4px!important;-webkit-overflow-scrolling:touch;scrollbar-width:none}
    .mc-coupons-grid::-webkit-scrollbar{display:none}
    .mc-voucher-card{flex:0 0 78%!important;max-width:280px!important;scroll-snap-align:start!important}
    .yza-home-reminder.yza-rem-clean{flex-direction:column!important;align-items:flex-start!important}
}

/* Service Guide Modal — Fully Isolated from .member-main-content */
.sg-overlay{position:fixed!important;inset:0!important;z-index:9999!important;display:flex!important;align-items:center!important;justify-content:center!important;padding:18px!important;background:rgba(7,10,16,.76)!important;backdrop-filter:blur(8px)!important;box-sizing:border-box!important}
.sg-overlay[hidden]{display:none!important}
.sg-modal{width:min(720px,100%)!important;max-height:min(88vh,780px)!important;display:flex!important;flex-direction:column!important;border:1px solid #e2e8f0!important;border-radius:22px!important;background:#ffffff!important;color:#0f172a!important;box-shadow:0 32px 90px rgba(0,0,0,.55)!important;overflow:hidden!important;box-sizing:border-box!important;font-family:"Manrope","Inter","Segoe UI",sans-serif!important;animation:sg-in .2s cubic-bezier(.16,1,.3,1)}
@keyframes sg-in{from{opacity:0;transform:translateY(10px) scale(.98)}to{opacity:1;transform:none}}
.sg-head{flex:0 0 auto!important;display:flex!important;align-items:flex-start!important;justify-content:space-between!important;gap:16px!important;padding:20px 24px 18px!important;background:linear-gradient(135deg,#0b0f17 0%,#161f2e 100%)!important;color:#fff!important}
.sg-kicker{display:inline-flex!important;align-items:center!important;gap:6px!important;padding:4px 10px!important;border-radius:999px!important;background:rgba(227,29,53,.18)!important;border:1px solid rgba(227,29,53,.4)!important;color:#ff808f!important;font-size:10px!important;font-weight:800!important;letter-spacing:.14em!important;text-transform:uppercase!important;line-height:1.2!important}
.sg-head h3{margin:8px 0 4px!important;color:#fff!important;font-size:20px!important;font-weight:800!important;letter-spacing:-.025em!important;line-height:1.2!important}
.sg-head p{margin:0!important;color:#94a3b8!important;font-size:12.5px!important;font-weight:500!important;line-height:1.45!important}
.sg-close{flex:0 0 34px!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;width:34px!important;height:34px!important;min-width:34px!important;min-height:34px!important;max-width:34px!important;max-height:34px!important;padding:0!important;border:1px solid rgba(255,255,255,.18)!important;border-radius:999px!important;background:rgba(255,255,255,.08)!important;color:#fff!important;font-size:18px!important;line-height:1!important;cursor:pointer!important;transition:background .15s,border-color .15s!important}
.sg-close:hover{background:#e31d35!important;border-color:#e31d35!important;color:#fff!important}
.sg-odom-bar{flex:0 0 auto!important;padding:14px 24px!important;background:#f8fafc!important;border-bottom:1px solid #e2e8f0!important}
.sg-odom-card{display:flex!important;flex-direction:column!important;gap:10px!important;padding:12px 14px!important;border:1px solid #e2e8f0!important;border-radius:14px!important;background:#fff!important}
.sg-odom-card+.sg-odom-card{margin-top:8px!important}
.sg-odom-top{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:10px!important;flex-wrap:wrap!important}
.sg-odom-car{display:flex!important;align-items:center!important;gap:8px!important;flex-wrap:wrap!important}
.sg-odom-car strong{color:#0f172a!important;font-size:14px!important;font-weight:800!important}
.sg-odom-plate{padding:2px 8px!important;border-radius:6px!important;background:#f1f5f9!important;color:#475569!important;font-size:11px!important;font-weight:700!important}
.sg-odom-edit,.sg-odom-edit:visited{color:#e31d35!important;font-size:11.5px!important;font-weight:800!important;text-decoration:none!important}
.sg-odom-edit:hover{text-decoration:underline!important}
.sg-odom-grid{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:8px!important}
.sg-odom-box{padding:8px 10px!important;border:1px solid #f1f5f9!important;border-radius:10px!important;background:#f8fafc!important}
.sg-odom-box.is-active{border-color:#fecdd3!important;background:#fff1f2!important}
.sg-odom-box span{display:block!important;color:#64748b!important;font-size:10px!important;font-weight:800!important;letter-spacing:.06em!important;text-transform:uppercase!important;line-height:1.2!important}
.sg-odom-box strong{display:block!important;margin-top:3px!important;color:#0f172a!important;font-size:13.5px!important;font-weight:800!important;line-height:1.2!important}
.sg-odom-box.is-active strong{color:#be123c!important}
.sg-odom-box em{display:block!important;margin-top:2px!important;color:#64748b!important;font-style:normal!important;font-size:10.5px!important;line-height:1.2!important}
.sg-tabs{flex:0 0 auto!important;display:flex!important;align-items:center!important;gap:6px!important;padding:10px 24px!important;border-bottom:1px solid #f1f5f9!important;background:#fff!important;overflow-x:auto!important;scrollbar-width:none!important}
.sg-tabs::-webkit-scrollbar{display:none!important}
.sg-tab{flex:0 0 auto!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;height:32px!important;min-height:32px!important;max-height:32px!important;padding:0 13px!important;border:1px solid #e2e8f0!important;border-radius:999px!important;background:#f8fafc!important;color:#475569!important;font-size:12px!important;font-weight:700!important;line-height:1!important;cursor:pointer!important;transition:all .15s!important}
.sg-tab:hover{border-color:#cbd5e1!important;color:#0f172a!important;background:#f1f5f9!important}
.sg-tab.is-active{border-color:#0f172a!important;background:#0f172a!important;color:#fff!important}
.sg-body{flex:1 1 auto!important;min-height:0!important;padding:16px 24px!important;overflow-y:auto!important;display:block!important;background:#fff!important}
.sg-stack{display:flex!important;flex-direction:column!important;gap:10px!important}
.sg-item{display:grid!important;grid-template-columns:108px minmax(0,1fr)!important;gap:14px!important;padding:14px 16px!important;border:1px solid #e2e8f0!important;border-radius:16px!important;background:#fff!important;transition:border-color .16s,box-shadow .16s!important}
.sg-item.is-recommended{border-color:#fda4af!important;background:linear-gradient(90deg,#fff9fa 0%,#ffffff 100%)!important;box-shadow:0 6px 20px -10px rgba(227,29,53,.18)!important}
.sg-item[hidden]{display:none!important}
.sg-km{display:flex!important;flex-direction:column!important;align-items:center!important;justify-content:center!important;text-align:center!important;border-radius:12px!important;background:#0f172a!important;color:#fff!important;padding:10px 8px!important}
.sg-item.is-recommended .sg-km{background:linear-gradient(135deg,#e31d35,#be123c)!important}
.sg-km b{font-size:16px!important;font-weight:900!important;letter-spacing:-.02em!important;line-height:1.1!important;color:#fff!important}
.sg-km small{margin-top:4px!important;color:rgba(255,255,255,.78)!important;font-size:9.5px!important;font-weight:800!important;letter-spacing:.08em!important;text-transform:uppercase!important}
.sg-item-head{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:8px!important;margin-bottom:6px!important;flex-wrap:wrap!important}
.sg-item h4{margin:0!important;color:#0f172a!important;font-size:15px!important;font-weight:800!important;letter-spacing:-.01em!important;line-height:1.25!important}
.sg-badges{display:inline-flex!important;align-items:center!important;gap:6px!important;flex-wrap:wrap!important}
.sg-tier{padding:3px 9px!important;border-radius:999px!important;background:#f1f5f9!important;color:#475569!important;font-size:10.5px!important;font-weight:700!important;line-height:1.2!important}
.sg-tier.is-major{background:#fff1f2!important;color:#be123c!important}
.sg-rec-badge{padding:3px 9px!important;border-radius:999px!important;background:#e31d35!important;color:#fff!important;font-size:10px!important;font-weight:800!important;letter-spacing:.03em!important;line-height:1.2!important}
.sg-list{margin:0!important;padding:0!important;list-style:none!important;display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:4px 12px!important}
.sg-list li{position:relative!important;padding-left:14px!important;color:#475569!important;font-size:12.5px!important;font-weight:500!important;line-height:1.45!important}
.sg-list li::before{content:""!important;position:absolute!important;left:0!important;top:6px!important;width:5px!important;height:5px!important;border-radius:50%!important;background:#e31d35!important}
.sg-note{padding:12px 14px!important;border:1px solid #e2e8f0!important;border-radius:12px!important;background:#f8fafc!important;color:#475569!important;font-size:12px!important;line-height:1.5!important}
.sg-note strong{color:#0f172a!important}
.sg-foot{flex:0 0 auto!important;display:flex!important;align-items:center!important;justify-content:space-between!important;gap:12px!important;padding:12px 24px!important;border-top:1px solid #e2e8f0!important;background:#f8fafc!important}
.sg-foot-hint{color:#64748b!important;font-size:12px!important;font-weight:500!important}
.sg-foot-actions{display:flex!important;align-items:center!important;gap:8px!important;margin-left:auto!important}
.sg-btn{display:inline-flex!important;align-items:center!important;justify-content:center!important;height:38px!important;min-height:38px!important;max-height:38px!important;padding:0 16px!important;border-radius:999px!important;font-size:12.5px!important;font-weight:800!important;cursor:pointer!important;text-decoration:none!important;transition:all .15s!important}
.sg-btn-ghost{border:1px solid #cbd5e1!important;background:#fff!important;color:#0f172a!important}
.sg-btn-ghost:hover{background:#f1f5f9!important}
.sg-btn-primary,.sg-btn-primary:visited{border:1px solid #e31d35!important;background:#e31d35!important;color:#fff!important}
.sg-btn-primary:hover{background:#c81428!important;border-color:#c81428!important;color:#fff!important}
@media(max-width:600px){.sg-head,.sg-odom-bar,.sg-tabs,.sg-body,.sg-foot{padding-left:16px!important;padding-right:16px!important}.sg-odom-grid,.sg-list,.sg-item{grid-template-columns:1fr!important}.sg-km{flex-direction:row!important;justify-content:space-between!important;padding:8px 12px!important}.sg-foot-hint{display:none!important}}

/* Barangkali Kamu Suka — Minimalist Modern Showcase */
.yza-bks-showcase{width:100%;padding:56px 0 48px;background:#f8fafc;border-top:1px solid #e2e8f0}
.yza-bks-container{width:min(94vw,1480px);margin:0 auto}
.yza-bks-head{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:26px;flex-wrap:wrap}
.yza-bks-head h2{margin:0;color:#0f172a;font-size:clamp(24px,2.4vw,30px);font-weight:800;letter-spacing:-.03em;line-height:1.15}
.yza-bks-head p{margin:6px 0 0;color:#64748b;font-size:14px;line-height:1.5}
.yza-bks-actions{display:flex;align-items:center;gap:12px}
.yza-bks-nav{display:flex;align-items:center;gap:6px}
.yza-bks-nav-btn{display:grid;place-items:center;width:38px;height:38px;border:1px solid #e2e8f0;border-radius:999px;background:#fff;color:#0f172a;cursor:pointer;transition:border-color .15s,background .15s,transform .15s}
.yza-bks-nav-btn:hover{border-color:#cbd5e1;background:#f1f5f9;transform:translateY(-1px)}
.yza-bks-all,.yza-bks-all:visited{display:inline-flex;align-items:center;gap:8px;height:38px;padding:0 16px;border:1px solid #0f172a;border-radius:999px;background:#0f172a;color:#fff!important;font-size:13px;font-weight:700;text-decoration:none;transition:background .15s,border-color .15s,transform .15s}
.yza-bks-all:hover{background:#e31d35;border-color:#e31d35;color:#fff!important;transform:translateY(-1px)}
.yza-bks-viewport{overflow-x:auto;overflow-y:hidden;scroll-snap-type:x mandatory;scroll-behavior:smooth;scrollbar-width:none;-ms-overflow-style:none;padding:4px 2px 10px}
.yza-bks-viewport::-webkit-scrollbar{display:none}
.yza-bks-track{display:flex;gap:18px}
.yza-bks-card{flex:0 0 clamp(270px,23.5vw,328px);scroll-snap-align:start;display:flex;flex-direction:column;border:1px solid #e2e8f0;border-radius:18px;background:#fff;overflow:hidden;transition:transform .22s ease,box-shadow .22s ease,border-color .22s ease}
.yza-bks-card:hover{transform:translateY(-4px);border-color:#cbd5e1;box-shadow:0 18px 36px -14px rgba(15,23,42,.12)}
.yza-bks-media{position:relative;aspect-ratio:16/10;background:#f1f5f9;overflow:hidden}
.yza-bks-media-link{display:block;width:100%;height:100%}
.yza-bks-media img{display:block;width:100%;height:100%;object-fit:cover;transition:transform .45s cubic-bezier(.22,1,.36,1)}
.yza-bks-card:hover .yza-bks-media img{transform:scale(1.04)}
.yza-bks-fav{position:absolute;top:10px;right:10px;display:grid;place-items:center;width:34px;height:34px;border:1px solid rgba(255,255,255,.5);border-radius:999px;background:rgba(255,255,255,.88);backdrop-filter:blur(6px);color:#0f172a;font-size:15px;line-height:1;cursor:pointer;transition:transform .15s,background .15s,color .15s}
.yza-bks-fav:hover{transform:scale(1.06);background:#fff}
.yza-bks-fav.is-favorited{background:#e31d35;border-color:#e31d35;color:#fff}
.yza-bks-body{display:flex;flex:1;flex-direction:column;justify-content:space-between;padding:16px 18px 18px;gap:14px}
.yza-bks-info{display:block;text-decoration:none}
.yza-bks-meta{display:block;color:#64748b;font-size:11.5px;font-weight:600;letter-spacing:.02em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.yza-bks-info h3{margin:5px 0 0;color:#0f172a;font-size:17px;font-weight:800;letter-spacing:-.02em;line-height:1.25}
.yza-bks-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;padding-top:12px;border-top:1px solid #f1f5f9}
.yza-bks-price{display:flex;flex-direction:column;text-decoration:none}
.yza-bks-price span{color:#94a3b8;font-size:10.5px;font-weight:600;text-transform:uppercase;letter-spacing:.06em}
.yza-bks-price strong{color:#0f172a;font-size:16px;font-weight:800;letter-spacing:-.02em;margin-top:1px}
.yza-bks-go,.yza-bks-go:visited{display:grid;place-items:center;width:36px;height:36px;flex-shrink:0;border:1px solid #e2e8f0;border-radius:999px;background:#fff;color:#0f172a!important;font-size:15px;font-weight:700;text-decoration:none;transition:background .15s,border-color .15s,color .15s,transform .15s}
.yza-bks-card:hover .yza-bks-go{background:#0f172a;border-color:#0f172a;color:#fff!important;transform:translateX(2px)}
.yza-bks-footer{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-top:14px}
.yza-bks-progress{position:relative;flex:1;max-width:240px;height:3px;border-radius:999px;background:#e2e8f0;overflow:hidden}
.yza-bks-progress::before{content:"";position:absolute;inset:0 auto 0 0;width:var(--garage-progress,20%);border-radius:999px;background:#0f172a;transition:width .25s ease}
.yza-bks-counter{color:#64748b;font-size:12px;font-weight:700;font-variant-numeric:tabular-nums}
.yza-bks-empty{padding:32px;border:1px dashed #cbd5e1;border-radius:16px;color:#64748b;font-size:14px}
@media(max-width:640px){.yza-bks-showcase{padding:40px 0 36px}.yza-bks-nav{display:none}.yza-bks-card{flex:0 0 82vw}}
</style>

@php
    $primaryReminder = collect($reminders)->first();
    $recommendedKm = (int) ($primaryReminder['nextMilestoneKm'] ?? 5000);
@endphp
<div class="sg-overlay" data-service-guide hidden role="dialog" aria-modal="true" aria-labelledby="sg-title">
    <div class="sg-modal">
        <header class="sg-head">
            <div>
                <span class="sg-kicker">Interval Perawatan &amp; Odometer</span>
                <h3 id="sg-title">Panduan Servis Berkala</h3>
                <p>Status odometer kendaraan Anda (input pengguna &amp; mekanik) beserta acuan servis berkala.</p>
            </div>
            <button type="button" class="sg-close" data-close-service-guide aria-label="Tutup">&times;</button>
        </header>

        @if(collect($reminders)->isNotEmpty())
            <div class="sg-odom-bar">
                @foreach(collect($reminders)->take(2) as $rem)
                    @php
                        $rv = $rem['vehicle'];
                        $uKm = (int) ($rem['userKilometer'] ?? $rv->kilometer ?? 0);
                        $mKm = $rem['mechanicKilometer'] ?? $rem['kilometer'] ?? null;
                        $src = $rem['kmSource'] ?? ($mKm !== null ? 'mekanik' : 'pengguna');
                        $targetLabel = $rem['nextMilestoneLabel'] ?? '5.000 km · Servis Ringan';
                    @endphp
                    <div class="sg-odom-card">
                        <div class="sg-odom-top">
                            <div class="sg-odom-car">
                                <strong>{{ $rv->merk }} {{ $rv->model }}</strong>
                                <span class="sg-odom-plate">{{ $rv->nomor_polisi ?: 'Tanpa No. Polisi' }}</span>
                            </div>
                            <a class="sg-odom-edit" href="{{ route('pelanggan.kendaraan.edit', $rv) }}">Perbarui KM Pengguna →</a>
                        </div>
                        <div class="sg-odom-grid">
                            <div class="sg-odom-box {{ $src === 'pengguna' ? 'is-active' : '' }}">
                                <span>KM Input Pengguna</span>
                                <strong>{{ number_format($uKm, 0, ',', '.') }} km</strong>
                                <em>Data garasi member</em>
                            </div>
                            <div class="sg-odom-box {{ $src === 'mekanik' ? 'is-active' : '' }}">
                                <span>KM Input Mekanik</span>
                                <strong>{{ $mKm !== null ? number_format((int) $mKm, 0, ',', '.') . ' km' : 'Belum tercatat' }}</strong>
                                <em>{{ !empty($rem['mechanicName']) ? 'Mekanik: '.$rem['mechanicName'] : ($rem['lastDate'] ? 'Servis '.$rem['lastDate']->format('d M Y') : 'Dicatat saat check-in bengkel') }}</em>
                            </div>
                            <div class="sg-odom-box is-active">
                                <span>Target Servis Berikutnya</span>
                                <strong>{{ $targetLabel }}</strong>
                                <em>{{ $rem['nextDate'] ? 'Jadwal: '.$rem['nextDate']->format('d M Y') : $rem['statusLabel'] }}</em>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="sg-tabs" role="tablist" aria-label="Filter interval kilometer">
            <button type="button" class="sg-tab is-active" data-sg-filter="all">Semua Interval</button>
            <button type="button" class="sg-tab" data-sg-filter="5000">5.000 km</button>
            <button type="button" class="sg-tab" data-sg-filter="10000">10.000 km</button>
            <button type="button" class="sg-tab" data-sg-filter="20000">20.000 km</button>
            <button type="button" class="sg-tab" data-sg-filter="40000">40.000 km</button>
            <button type="button" class="sg-tab" data-sg-filter="60000">60.000+ km</button>
        </div>
        <div class="sg-body">
            <div class="sg-stack">
                <article class="sg-item {{ $recommendedKm === 5000 ? 'is-recommended' : '' }}" data-sg-km="5000">
                    <div class="sg-km"><b>5.000</b><small>KM · 6 BLN</small></div>
                    <div>
                        <div class="sg-item-head">
                            <h4>Servis Ringan</h4>
                            <div class="sg-badges">
                                @if($recommendedKm === 5000)<span class="sg-rec-badge">Target Kendaraan Anda</span>@endif
                                <span class="sg-tier">Rutin</span>
                            </div>
                        </div>
                        <ul class="sg-list"><li>Ganti oli mesin &amp; filter oli</li><li>Cek cairan radiator, rem &amp; wiper</li><li>Cek tekanan &amp; kondisi ban</li><li>Inspeksi visual komponen dasar</li></ul>
                    </div>
                </article>
                <article class="sg-item {{ $recommendedKm === 10000 ? 'is-recommended' : '' }}" data-sg-km="10000">
                    <div class="sg-km"><b>10.000</b><small>KM · 12 BLN</small></div>
                    <div>
                        <div class="sg-item-head">
                            <h4>Servis Berkala</h4>
                            <div class="sg-badges">
                                @if($recommendedKm === 10000)<span class="sg-rec-badge">Target Kendaraan Anda</span>@endif
                                <span class="sg-tier">Berkala</span>
                            </div>
                        </div>
                        <ul class="sg-list"><li>Ganti oli mesin &amp; filter oli</li><li>Bersihkan / ganti filter udara &amp; AC</li><li>Cek sistem rem, aki, dan busi</li><li>Rotasi ban untuk keausan merata</li></ul>
                    </div>
                </article>
                <article class="sg-item {{ $recommendedKm === 20000 ? 'is-recommended' : '' }}" data-sg-km="20000">
                    <div class="sg-km"><b>20.000</b><small>KM · 24 BLN</small></div>
                    <div>
                        <div class="sg-item-head">
                            <h4>Servis Menengah</h4>
                            <div class="sg-badges">
                                @if($recommendedKm === 20000)<span class="sg-rec-badge">Target Kendaraan Anda</span>@endif
                                <span class="sg-tier">Menengah</span>
                            </div>
                        </div>
                        <ul class="sg-list"><li>Seluruh item servis 10.000 km</li><li>Ganti minyak rem &amp; cek kampas rem</li><li>Cek sistem kemudi &amp; suspensi</li><li>Spooring &amp; balancing roda</li></ul>
                    </div>
                </article>
                <article class="sg-item {{ $recommendedKm === 40000 ? 'is-recommended' : '' }}" data-sg-km="40000">
                    <div class="sg-km"><b>40.000</b><small>KM · 48 BLN</small></div>
                    <div>
                        <div class="sg-item-head">
                            <h4>Servis Besar</h4>
                            <div class="sg-badges">
                                @if($recommendedKm === 40000)<span class="sg-rec-badge">Target Kendaraan Anda</span>@endif
                                <span class="sg-tier is-major">Major</span>
                            </div>
                        </div>
                        <ul class="sg-list"><li>Ganti busi, filter bensin, &amp; coolant</li><li>Ganti oli transmisi &amp; oli gardan</li><li>Bersihkan injektor &amp; throttle body</li><li>Cek timing belt / rantai &amp; V-belt</li></ul>
                    </div>
                </article>
                <article class="sg-item {{ $recommendedKm === 60000 ? 'is-recommended' : '' }}" data-sg-km="60000">
                    <div class="sg-km"><b>60.000+</b><small>KM</small></div>
                    <div>
                        <div class="sg-item-head">
                            <h4>Overhaul Ringan</h4>
                            <div class="sg-badges">
                                @if($recommendedKm === 60000)<span class="sg-rec-badge">Target Kendaraan Anda</span>@endif
                                <span class="sg-tier is-major">Komprehensif</span>
                            </div>
                        </div>
                        <ul class="sg-list"><li>Ganti timing belt, water pump &amp; tensioner</li><li>Cek shock absorber, bushing &amp; bearing</li><li>Evaluasi penggantian aki &amp; ban</li><li>Kalibrasi menyeluruh sistem mesin</li></ul>
                    </div>
                </article>
                <div class="sg-note"><strong>Catatan:</strong> Odometer diambil dari nilai tertinggi antara <b>input pengguna</b> di garasi dan <b>input mekanik</b> saat servis terakhir. Pilih mana yang tercapai lebih dulu: jarak tempuh (km) atau waktu (bulan).</div>
            </div>
        </div>
        <footer class="sg-foot">
            <span class="sg-foot-hint">Pilih mana yang tercapai lebih dulu: km atau bulan.</span>
            <div class="sg-foot-actions">
                <button type="button" class="sg-btn sg-btn-ghost" data-close-service-guide>Tutup</button>
                <a class="sg-btn sg-btn-primary" href="{{ route('pelanggan.booking-servis.create') }}">Booking Servis →</a>
            </div>
        </footer>
    </div>
</div>
<script>
(() => {
    const m = document.querySelector('[data-service-guide]');
    if (!m) return;
    if (m.parentElement !== document.body) document.body.appendChild(m);
    const close = () => { m.hidden = true; document.body.style.overflow = ''; };
    document.querySelectorAll('[data-open-service-guide]').forEach(b => b.addEventListener('click', () => { m.hidden = false; document.body.style.overflow = 'hidden'; }));
    m.querySelectorAll('[data-close-service-guide]').forEach(b => b.addEventListener('click', close));
    m.addEventListener('click', e => { if (e.target === m) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !m.hidden) close(); });
    const tabs = [...m.querySelectorAll('[data-sg-filter]')];
    const items = [...m.querySelectorAll('[data-sg-km]')];
    tabs.forEach(tab => tab.addEventListener('click', () => {
        const f = tab.dataset.sgFilter;
        tabs.forEach(t => t.classList.toggle('is-active', t === tab));
        items.forEach(it => { it.hidden = f !== 'all' && it.dataset.sgKm !== f; });
    }));
})();
</script>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.yza-home a, .yza-home button').forEach(function (element) {
    if (!/lihat\s+semua/i.test(element.textContent || '')) return;

    let container = element;
    for (let i = 0; i < 5 && container; i += 1) {
      const text = (container.textContent || '').replace(/\s+/g, ' ');
      if (/Aktivitas\s*&\s*Jadwal|Pengingat\s*Servis/i.test(text)) {
        element.remove();
        container.classList.add('yza-clear-panel');
        break;
      }
      container = container.parentElement;
    }
  });
});
</script>
<script>
(() => {
    const modal = document.getElementById('yza-claim-modal');
    if (!modal) return;

    const stepConfirm = document.getElementById('yza-claim-step-confirm');
    const stepSuccess = document.getElementById('yza-claim-step-success');
    const titleEl = document.getElementById('modal-coupon-title');
    const codeEl = document.getElementById('modal-coupon-code');
    const costEl = document.getElementById('modal-coupon-cost');
    const errorEl = document.getElementById('modal-claim-error');
    const btnSubmit = document.getElementById('btn-submit-claim');
    const btnCopy = document.getElementById('btn-copy-code');
    const successCode = document.getElementById('modal-success-code');
    const successDeducted = document.getElementById('modal-success-deducted');
    const successBalance = document.getElementById('modal-success-balance');
    const pointsDisplay = document.getElementById('yza-points-display');
    const modalBalance = document.getElementById('modal-current-points');

    let currentAction = null;
    let activeTriggerBtn = null;

    const closeModal = () => {
        modal.hidden = true;
        currentAction = null;
        if (errorEl) {
            errorEl.style.display = 'none';
            errorEl.textContent = '';
        }
    };

    modal.addEventListener('click', (e) => {
        if (e.target === modal || e.target.closest('[data-close-claim-modal]')) {
            closeModal();
        }
    });

    document.querySelectorAll('[data-claim-coupon]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            activeTriggerBtn = btn;
            currentAction = {
                id: btn.dataset.promoId,
                code: btn.dataset.promoCode,
                title: btn.dataset.promoTitle,
                points: parseInt(btn.dataset.promoPoints, 10) || 0,
                url: btn.dataset.claimUrl
            };

            titleEl.textContent = currentAction.title;
            codeEl.textContent = currentAction.code;
            costEl.textContent = currentAction.points.toLocaleString('id-ID') + ' Poin';

            stepConfirm.style.display = '';
            stepSuccess.style.display = 'none';
            if (errorEl) {
                errorEl.style.display = 'none';
                errorEl.textContent = '';
            }

            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Konfirmasi Tukar';
            modal.hidden = false;
        });
    });

    btnSubmit.addEventListener('click', async () => {
        if (!currentAction) return;

        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Memproses...';
        if (errorEl) {
            errorEl.style.display = 'none';
            errorEl.textContent = '';
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        try {
            const res = await fetch(currentAction.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken || ''
                }
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Gagal menukarkan poin untuk kupon.');
            }

            if (pointsDisplay && typeof data.balance !== 'undefined') {
                pointsDisplay.textContent = Number(data.balance).toLocaleString('id-ID');
            }
            if (modalBalance && typeof data.balance !== 'undefined') {
                modalBalance.textContent = Number(data.balance).toLocaleString('id-ID') + ' Poin';
            }

            successCode.textContent = data.promo_code || currentAction.code;
            successDeducted.textContent = '-' + Number(data.cost || currentAction.points).toLocaleString('id-ID') + ' Poin';
            successBalance.textContent = Number(data.balance).toLocaleString('id-ID') + ' Poin';

            if (activeTriggerBtn) {
                activeTriggerBtn.classList.add('is-claimed');
                activeTriggerBtn.innerHTML = '<span>✓ Tersimpan</span>';
                activeTriggerBtn.disabled = true;
            }

            stepConfirm.style.display = 'none';
            stepSuccess.style.display = '';
        } catch (err) {
            if (errorEl) {
                errorEl.textContent = err.message;
                errorEl.style.display = 'block';
            }
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Coba Lagi';
        }
    });

    if (btnCopy) {
        btnCopy.addEventListener('click', () => {
            const code = successCode.textContent.trim();
            if (!code) return;
            navigator.clipboard.writeText(code).then(() => {
                const original = btnCopy.textContent;
                btnCopy.textContent = 'Tersalin!';
                setTimeout(() => {
                    btnCopy.textContent = original;
                }, 2000);
            });
        });
    }
})();
</script>
<script>
(() => {
    const carousel = document.querySelector('[data-member-sport-carousel]');
    if (!carousel) return;
    const viewport = carousel.querySelector('[data-member-carousel-viewport]');
    const track = carousel.querySelector('[data-member-carousel-track]');
    const cards = [...track.querySelectorAll('.featured-card')];
    const counter = carousel.querySelector('[data-member-carousel-counter]');
    const progress = carousel.querySelector('[data-member-carousel-progress]');
    const update = () => {
        const first = cards[0];
        if (!first) return;
        const step = first.getBoundingClientRect().width + parseFloat(getComputedStyle(track).gap || '0');
        const index = Math.min(cards.length, Math.max(1, Math.round(viewport.scrollLeft / step) + 1));
        if (counter) counter.textContent = `${String(index).padStart(2, '0')} / ${String(cards.length).padStart(2, '0')}`;
        if (progress) progress.style.setProperty('--garage-progress', `${(index / Math.max(1, cards.length)) * 100}%`);
    };
    viewport.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update, { passive: true });
    update();
    const scrollByStep = (dir) => {
        const first = cards[0];
        if (!first) return;
        const step = first.getBoundingClientRect().width + parseFloat(getComputedStyle(track).gap || '0');
        viewport.scrollBy({ left: dir * step, behavior: 'smooth' });
    };
    document.querySelector('[data-bks-prev]')?.addEventListener('click', () => scrollByStep(-1));
    document.querySelector('[data-bks-next]')?.addEventListener('click', () => scrollByStep(1));
    carousel.querySelectorAll('[data-member-favorite]').forEach((button) => {
        const key = button.dataset.memberFavorite;
        const name = button.dataset.memberFavoriteName || 'mobil';
        const read = () => { try { return JSON.parse(localStorage.getItem('yza_favorite_cars') || '[]'); } catch { return []; } };
        const render = () => { const active = read().includes(key); button.textContent = active ? '♥' : '♡'; button.classList.toggle('is-favorited', active); button.setAttribute('aria-pressed', String(active)); };
        render();
        button.addEventListener('click', (event) => { event.preventDefault(); event.stopPropagation(); const values = read(); const active = !values.includes(key); localStorage.setItem('yza_favorite_cars', JSON.stringify(active ? [...values, key] : values.filter((value) => value !== key))); render(); button.setAttribute('aria-label', `${active ? 'Hapus' : 'Tambahkan'} ${name} ${active ? 'dari' : 'ke'} favorit`); });
    });
})();
</script>
@endpush
