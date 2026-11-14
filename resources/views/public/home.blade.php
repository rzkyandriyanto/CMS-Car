<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>YZA Automotive · Showroom Premium</title>
    <link rel="icon" href="{{ asset('assets/images/branding/logo-yza.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/variables.css') }}"><link rel="stylesheet" href="{{ asset('assets/css/base.css') }}"><link rel="stylesheet" href="{{ asset('assets/css/components.css') }}"><link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}"><link rel="stylesheet" href="{{ asset('assets/css/pages/appointment-final.css') }}"><link rel="stylesheet" href="{{ asset('assets/css/showroom.css?v=services-membership-promo-20261004') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/showroom.css?v=favorite-circle-20261106') }}">
    @include('components.responsive-foundation')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/landing-original-restore.css?v=full-screen-20261106') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pages/landing-intro-compact.css') }}?v={{ filemtime(public_path('assets/css/pages/landing-intro-compact.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pages/landing-hero-slider.css') }}?v={{ filemtime(public_path('assets/css/pages/landing-hero-slider.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pages/landing-manifest-scroll.css') }}?v={{ filemtime(public_path('assets/css/pages/landing-manifest-scroll.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pages/landing-unified-slider.css') }}?v={{ filemtime(public_path('assets/css/pages/landing-unified-slider.css')) }}">
    <script src="{{ asset('assets/js/landing-viewport.js?v=20261106') }}" defer></script>
    <script src="{{ asset('assets/js/hero-slider.js') }}?v={{ filemtime(public_path('assets/js/hero-slider.js')) }}" defer></script>
    <script src="{{ asset('assets/js/landing-manifest-scroll.js') }}?v={{ filemtime(public_path('assets/js/landing-manifest-scroll.js')) }}" defer></script>
    <script src="{{ asset('assets/js/landing-unified-slider.js') }}?v={{ filemtime(public_path('assets/js/landing-unified-slider.js')) }}" defer></script>
</head>
<body class="showroom landing opening-active">
@if(true)
<div id="yza-intro" class="yza-intro" aria-label="Welcome to YZA Automotive" role="dialog">
    <div class="yza-intro-backdrop" aria-hidden="true"><img src="{{ asset('assets/images/hero/yza-intro-reference-match.png') }}" alt=""></div>
    <div class="yza-intro-vignette" aria-hidden="true"></div>
    <div class="yza-intro-premium">PREMIUM<br><span>CARS</span><i></i></div>
    <div class="yza-intro-content">
        <div class="yza-intro-brand"><img src="{{ asset('assets/images/branding/logo-yza.png') }}" alt="YZA Automotive"><div class="yza-intro-welcome"><i></i><span>SELAMAT DATANG DI</span><i></i></div><h1><span>YZA</span> <em>AUTOMOTIVE</em></h1><small>DRIVE YOUR EXPERIENCE</small></div>
        <div class="yza-intro-services" aria-label="Layanan YZA Automotive">
            <div class="yza-intro-service"><b aria-hidden="true"><svg class="yza-service-svg" viewBox="0 0 72 72" focusable="false"><path class="yza-service-frame" d="M18 4h36l14 12v40L54 68H18L4 56V16Z"/><path class="yza-service-symbol" d="M14 43h44l-4-15H18l-4 15Zm5-15 5-8h24l5 8M14 43v9h44v-9M22 52v5m28-5v5M22 37h.1m28 0h.1"/><circle class="yza-service-symbol" cx="23" cy="43" r="3"/><circle class="yza-service-symbol" cx="49" cy="43" r="3"/></svg></b><strong>JUAL BELI<br><span>MOBIL BERKUALITAS</span></strong></div>
            <div class="yza-intro-service"><b aria-hidden="true"><svg class="yza-service-svg" viewBox="0 0 72 72" focusable="false"><path class="yza-service-frame" d="M18 4h36l14 12v40L54 68H18L4 56V16Z"/><path class="yza-service-symbol" transform="translate(-1.5 0)" d="M51 16a10 10 0 0 0-12 12L19 48l5 5 20-20a10 10 0 0 0 12-12l-7 7-5-5 7-7Z"/></svg></b><strong>SERVIS &amp;<br><span>PERAWATAN</span></strong></div>
            <div class="yza-intro-service"><b aria-hidden="true"><svg class="yza-service-svg" viewBox="0 0 72 72" focusable="false"><path class="yza-service-frame" d="M18 4h36l14 12v40L54 68H18L4 56V16Z"/><path class="yza-service-symbol" transform="translate(5 0)" d="m34 16 2 6a15 15 0 0 1 5 2l5-4 4 4-4 5a15 15 0 0 1 2 5l6 2v6l-6 2a15 15 0 0 1-2 5l4 5-4 4-5-4a15 15 0 0 1-5 2l-2 6h-6l-2-6a15 15 0 0 1-5-2l-5 4-4-4 4-5a15 15 0 0 1-2-5l-6-2v-6l6-2a15 15 0 0 1 2-5l-4-5 4-4 5 4a15 15 0 0 1 5-2l2-6h6Zm-3 14a7 7 0 1 0 0 14 7 7 0 0 0 0-14Z"/></svg></b><strong>MODIFIKASI<br><span>&amp; AKSESORIS</span></strong></div>
        </div>
        <button class="yza-intro-enter" type="button"><span class="yza-intro-car-icon" aria-hidden="true"><svg class="yza-button-svg" viewBox="0 0 72 72" focusable="false"><path d="M14 43h44l-4-15H18l-4 15Zm5-15 5-8h24l5 8M14 43v9h44v-9M22 52v5m28-5v5"/><circle cx="23" cy="43" r="3"/><circle cx="49" cy="43" r="3"/></svg></span><b>MASUK KE SHOWROOM</b><span class="yza-intro-arrow" aria-hidden="true"><svg viewBox="0 0 32 20"><path d="M1 10h27M20 2l9 8-9 8"/></svg></span></button>
        <div class="yza-intro-bottom"><span></span><p>TEMUKAN MOBIL IMPIAN ANDA, DENGAN PENGALAMAN YANG LEBIH BERMAKNA</p><span></span></div>
    </div>
    <div class="yza-intro-door yza-intro-door--left" aria-hidden="true"></div><div class="yza-intro-door yza-intro-door--right" aria-hidden="true"></div>
</div>
<noscript><style>#yza-intro{display:none!important}</style></noscript>
@endif
<x-public-navbar :member-state="$landingMemberState ?? 'guest'" />
<main id="beranda">
<section class="show-hero hero-slider-container" id="hero-slider" aria-label="Showroom Hero Showcase" data-hero-slider>
    <img class="hero-showroom-mark" src="{{ asset('assets/images/branding/logo-yza.png') }}" alt="YZA Automotive showroom" aria-hidden="true">

    <!-- Background Slides Viewport -->
    <div class="hero-slides-viewport" aria-live="off">
        <!-- Slide 1: Alfa Romeo 33 Stradale (Rotated 90 deg CCW) -->
        <div class="hero-slide is-active" data-slide-index="0" data-car-name="Alfa Romeo 33 Stradale">
            <div class="hero-slide-bg" style="background-image: url('{{ asset('assets/images/hero/hero-slide-1.png') }}?v={{ filemtime(public_path('assets/images/hero/hero-slide-1.png')) }}');"></div>
            <div class="hero-slide-overlay"></div>
        </div>
        <!-- Slide 2: Lamborghini Huracán (Rotated 90 deg CW) -->
        <div class="hero-slide" data-slide-index="1" data-car-name="Lamborghini Huracán">
            <div class="hero-slide-bg" style="background-image: url('{{ asset('assets/images/hero/hero-slide-2.png') }}?v={{ filemtime(public_path('assets/images/hero/hero-slide-2.png')) }}');"></div>
            <div class="hero-slide-overlay"></div>
        </div>
        <!-- Slide 3: McLaren P1 Track Edition (Rotated 90 deg CCW) -->
        <div class="hero-slide" data-slide-index="2" data-car-name="McLaren P1 Track Edition">
            <div class="hero-slide-bg" style="background-image: url('{{ asset('assets/images/hero/hero-slide-3.png') }}?v={{ filemtime(public_path('assets/images/hero/hero-slide-3.png')) }}');"></div>
            <div class="hero-slide-overlay"></div>
        </div>
    </div>

    <!-- Hero Content Overlay (Text and Vehicle Card synced per slide) -->
    <div class="show-hero-inner">
        <div class="hero-copy-premium">
            <!-- Slide 1: Keunggulan YZA - Standar Kualitas & Kurasi Mutlak -->
            <div class="hero-slide-text-pane is-active" data-text-index="0">
                <h1>Kualitas Mutlak<br><em>Inspeksi 150+ Titik</em></h1>
                <div class="hero-buttons">
                    <a class="show-cta" href="#mobil">Jelajahi Koleksi Mobil</a>
                    <a class="show-ghost" href="#janji-temu">Konsultasi Showroom</a>
                </div>
            </div>

            <!-- Slide 2: Keunggulan YZA - Trade-In Transparan & Valuasi Pasar Terbaik -->
            <div class="hero-slide-text-pane" data-text-index="1">
                <h1>Tukar Tambah Cepat<br><em>Valuasi Terbaik</em></h1>
                <div class="hero-buttons">
                    <a class="show-cta" href="#jual-mobil">Nilai Mobil Sekarang</a>
                    <a class="show-ghost" href="{{ route('pelanggan.test-drive.create') }}">Jadwalkan Test Drive</a>
                </div>
            </div>

            <!-- Slide 3: Keunggulan YZA - Purna Jual Terpadu & Eksklusivitas Member -->
            <div class="hero-slide-text-pane" data-text-index="2">
                <h1>Perawatan Prima<br><em>Keuntungan Eksklusif</em></h1>
                <div class="hero-buttons">
                    <a class="show-cta" href="#yza-member">Gabung YZA Member</a>
                    <a class="show-ghost" href="#servis">Booking Servis Berkala</a>
                </div>
            </div>
        </div>

        <!-- Floating Vehicle Info Card on Right Stage (Minimalist, Positioned at Bottom Right) -->
        <div class="hero-car-stage">
            <div class="hero-vehicle-info hero-vehicle-slider-info is-active" data-card-index="0">
                <small>ALFA ROMEO</small>
                <h2>33 Stradale</h2>
                <p>1967 · V8 Mid-Engine</p>
                <strong>Rp 125.000.000.000</strong>
                <a href="{{ route('mobil.index', ['q' => 'Alfa Romeo']) }}">Lihat Detail</a>
            </div>
            <div class="hero-vehicle-info hero-vehicle-slider-info" data-card-index="1">
                <small>LAMBORGHINI</small>
                <h2>Huracán LP610-4</h2>
                <p>2023 · LDF Dual-Clutch</p>
                <strong>Rp 8.900.000.000</strong>
                <a href="{{ route('mobil.index', ['q' => 'Huracan']) }}">Lihat Detail</a>
            </div>
            <div class="hero-vehicle-info hero-vehicle-slider-info" data-card-index="2">
                <small>MCLAREN</small>
                <h2>P1 Track Edition</h2>
                <p>2024 · 7-Speed SSG</p>
                <strong>Rp 35.000.000.000</strong>
                <a href="{{ route('mobil.index', ['q' => 'McLaren']) }}">Lihat Detail</a>
            </div>
        </div>
    </div>

    <!-- Hyundai-Style Slider Controls Bar (Image 2 Reference - Labels above each individual bar) -->
    <div class="hero-slider-bar" role="toolbar" aria-label="Kontrol Slider Showroom">
        <div class="hero-slider-controls">
            <div class="hero-ctrl-actions">
                <button type="button" class="hero-ctrl-btn hero-pause-btn" data-slider-pause aria-label="Jeda tayangan otomatis" title="Jeda tayangan">
                    <span class="icon-pause" aria-hidden="true">❚❚</span>
                    <span class="icon-play" aria-hidden="true" style="display:none;">▶</span>
                </button>
                <button type="button" class="hero-ctrl-btn hero-prev-btn" data-slider-prev aria-label="Slide sebelumnya" title="Sebelumnya">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" class="hero-ctrl-btn hero-next-btn" data-slider-next aria-label="Slide berikutnya" title="Berikutnya">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>
            </div>

            <!-- Segmented Progress Bars (Labels positioned directly above each individual bar) -->
            <div class="hero-segmented-bars" role="tablist" aria-label="Pilih Slide">
                <button type="button" class="hero-segment-item is-active" data-slide-target="0" role="tab" aria-selected="true" aria-label="Slide 1: Alfa Romeo 33 Stradale">
                    <span class="hero-segment-label">Alfa Romeo 33 Stradale</span>
                    <span class="hero-segment-bar">
                        <span class="hero-segment-fill"></span>
                    </span>
                </button>
                <button type="button" class="hero-segment-item" data-slide-target="1" role="tab" aria-selected="false" aria-label="Slide 2: Lamborghini Huracán">
                    <span class="hero-segment-label">Lamborghini Huracán</span>
                    <span class="hero-segment-bar">
                        <span class="hero-segment-fill"></span>
                    </span>
                </button>
                <button type="button" class="hero-segment-item" data-slide-target="2" role="tab" aria-selected="false" aria-label="Slide 3: McLaren P1 Track Edition">
                    <span class="hero-segment-label">McLaren P1 Track Edition</span>
                    <span class="hero-segment-bar">
                        <span class="hero-segment-fill"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     Section 2: McLaren Reference Manifesto with Scroll-Scrubbed Text Reveal
     ========================================================================== -->
<section class="mclaren-manifest-section" id="filosofi" aria-label="Filosofi YZA Automotive">
    <div class="mclaren-manifest-container">
        <span class="mclaren-manifest-kicker">YZA AUTOMOTIVE</span>
        <div class="mclaren-manifest-quote-wrap">
            <p class="mclaren-manifest-quote" data-scroll-reveal>
                YZA Automotive tidak pernah berhenti melangkah. Rekayasa presisi, inovasi tanpa kompromi. Setiap kendaraan disempurnakan tanpa henti. Kami selalu bertanya bagaimana melangkah lebih jauh, dan komitmen itulah yang terus membentuk setiap standar kami hari ini.
            </p>
        </div>
    </div>
</section>

@php($memberPrimaryHref = match ($landingMemberState) { 'active' => route('pelanggan.member'), 'pending_payment' => route('pelanggan.membership.activation'), 'cancelled' => route('pelanggan.membership.activation'), default => route('register', ['from' => 'membership']) })
@php($memberPrimaryLabel = match ($landingMemberState) { 'active' => 'BUKA MEMBER CENTER', 'pending_payment' => 'AKTIFKAN YZA MEMBER', 'cancelled' => 'LIHAT STATUS MEMBERSHIP', default => 'BERGABUNG JADI MEMBER' })
<div class="yza-member-transition" aria-hidden="true"></div>
<section class="yza-member-section" id="yza-member" data-member-section><div class="show-container yza-member-layout"><div class="yza-member-copy"><h2>LEBIH DARI SEKADAR<br>PELANGGAN,<br><em>JADILAH YZA MEMBER.</em></h2><p class="yza-member-description">Bergabung dengan YZA Member dan nikmati pengalaman otomotif yang lebih lengkap melalui akses layanan eksklusif, promo spesial, YZA Points, service reminder, serta berbagai keuntungan lainnya.</p>@if($landingMemberState === 'pending_payment')<p class="yza-member-state">Membership Anda belum aktif. Selesaikan pembayaran aktivasi untuk menikmati benefit YZA Member.</p>@elseif($landingMemberState === 'active')<p class="yza-member-state is-active">✓ YZA Member Active</p>@endif<div class="yza-member-price"><span>AKTIVASI MEMBER</span><strong>Rp {{ number_format((float) ($membershipFee ?? 249000), 0, ',', '.') }}</strong><small>Sekali aktivasi • Tidak ada biaya bulanan.</small></div><div class="yza-member-actions"><a class="show-cta" href="{{ $memberPrimaryHref }}">{{ $memberPrimaryLabel }}</a></div></div><div class="yza-member-visual">
    <div class="yza-card-flipper-wrap" tabindex="0" role="button" aria-label="Kartu YZA Member. Arahkan kursor untuk melihat benefit eksklusif.">
        <div class="yza-card-flipper-inner">
            
            <!-- SISI DEPAN (FRONT FACE - IMAGE 1) -->
            <div class="yza-card-face yza-card-face--front yza-member-card">
                <div class="yza-card-front-top">
                    <img src="{{ asset('assets/images/branding/logo-yza.png') }}" alt="YZA Automotive">
                    <span class="yza-member-card-status">MEMBER EXCLUSIVE</span>
                </div>
                <span class="yza-member-card-line"></span>
                <div class="yza-card-front-body">
                    <small>YZA AUTOMOTIVE</small>
                    <strong>YZA <em>MEMBER</em></strong>
                </div>
                <div class="yza-card-front-bottom">
                    <div class="yza-card-front-numbers">
                        <b>YZA-MBR-000001</b>
                        <em>PREMIUM AUTOMOTIVE EXPERIENCE</em>
                    </div>
                    <span class="yza-member-chip" aria-hidden="true"></span>
                </div>
            </div>

            <!-- SISI BELAKANG (BACK FACE - IMAGE 2) -->
            <div class="yza-card-face yza-card-face--back">
                <div class="yza-card-back-grid">
                    @foreach ([
                        ['⌑','PROMO EKSKLUSIF','Nikmati promo khusus member pada layanan dan transaksi pilihan.'],
                        ['✦','YZA POINTS','Kumpulkan Points dari setiap transaksi eligible yang berhasil.'],
                        ['⚙','LAYANAN LEBIH LENGKAP','Gunakan berbagai fasilitas YZA Automotive dalam satu akun.'],
                        ['▣','TEST DRIVE & MOBIL','Kelola kebutuhan kendaraan dan test drive dengan lebih mudah.'],
                        ['↔','TRADE-IN','Dapatkan akses proses trade-in melalui sistem YZA.'],
                        ['⌁','SERVICE REMINDER','Pantau riwayat kendaraan dan pengingat servis Anda.']
                    ] as $benefit)
                        <div class="yza-back-benefit-item">
                            <i aria-hidden="true">{{ $benefit[0] }}</i>
                            <div class="benefit-content">
                                <strong>{{ $benefit[1] }}</strong>
                                <small>{{ $benefit[2] }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div></div></section>

@php($serviceMembershipHref = match ($landingMemberState) { 'active' => route('pelanggan.member'), 'pending_payment' => route('pelanggan.membership.activation'), 'cancelled' => route('pelanggan.membership.activation'), default => route('register', ['from' => 'membership']) })
@php($serviceMembershipLabel = match ($landingMemberState) { 'active' => 'Buka Membership Saya', 'pending_payment' => 'Aktifkan Membership', 'cancelled' => 'Aktifkan Membership', default => 'Lihat YZA Membership' })
<section class="show-section services-section" id="layanan"><div class="show-container"><div class="services-heading"><h2>Layanan Kami</h2><p>Solusi lengkap untuk semua kebutuhan otomotif Anda.</p></div><div class="services-grid">@foreach ([['⚙','Servis Berkala','Perawatan rutin untuk performa maksimal.'],['▦','Perbaikan Mesin','Diagnosa dan perbaikan oleh teknisi ahli.'],['◉','Ganti Oli','Oli berkualitas untuk performa optimal.'],['◎','Rem & Suspensi','Keamanan berkendara lebih terjamin.'],['▣','AC Mobil','Dingin dan nyaman di setiap perjalanan.'],['▤','Diagnosa Komputer','Teknologi terbaru untuk hasil akurat.']] as $service)<article class="service-card-premium"><i aria-hidden="true">{{ $service[0] }}</i><h3>{{ $service[1] }}</h3><p>{{ $service[2] }}</p></article>@endforeach</div><div class="services-membership-promo"><div class="services-membership-copy"><span class="services-membership-kicker">YZA MEMBERSHIP</span><h3>Rawat Mobil Lebih Maksimal Bersama YZA Member</h3><p>Gabung sebagai YZA Member untuk menikmati pengalaman layanan yang lebih praktis, prioritas, dan benefit eksklusif untuk kendaraan Anda.</p><div class="services-membership-benefits"><span>✓ Promo eksklusif</span><span>✓ Layanan lebih lengkap</span><span>✓ Service reminder</span></div></div><div class="services-membership-actions"><a class="show-cta" href="{{ $serviceMembershipHref }}">{{ $serviceMembershipLabel }}</a></div></div></div></section>

<section class="service-appointment" id="servis">
    <div class="service-layout">
        <div class="appointment-wrap" id="janji-temu">
            <form class="appointment-form" id="appointment-form">
                <div class="appointment-form-heading">
                    <div>
                        <strong>Buat Janji Temu</strong>
                        <span>Isi detail kunjungan Anda. Pesan akan diarahkan ke WhatsApp YZA.</span>
                    </div>
                </div>
                <div class="appointment-fields">
                    <div class="form-group">
                        <label class="form-label" for="appointment-nama">Nama Lengkap</label>
                        <input class="form-control" id="appointment-nama" name="nama" required placeholder="Nama lengkap Anda">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="appointment-hp">Nomor WhatsApp</label>
                        <input class="form-control" id="appointment-hp" name="no_hp" type="tel" required placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="appointment-layanan">Jenis Kebutuhan</label>
                        <select class="form-control" id="appointment-layanan" name="layanan" required>
                            <option value="">Pilih layanan</option>
                            @foreach (['Konsultasi Mobil','Test Drive','Service','Spare Part','Car Wash','Used Car','Trade-In','Lainnya'] as $service)
                                <option value="{{ $service }}">{{ $service }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="appointment-tanggal">Tanggal Janji</label>
                        <input class="form-control" id="appointment-tanggal" name="tanggal" type="date" min="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="appointment-jam">Jam Janji</label>
                        <input class="form-control" id="appointment-jam" name="jam" type="time" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label" for="appointment-catatan">Catatan Tambahan <span>(opsional)</span></label>
                        <textarea class="form-control" id="appointment-catatan" name="catatan" rows="3" placeholder="Contoh: ingin konsultasi trade-in dan cek kondisi kendaraan."></textarea>
                    </div>
                </div>
                <button class="show-cta appointment-submit" type="submit">Lanjut ke WhatsApp</button>
                <p class="appointment-microcopy">Pesan tidak dikirim otomatis. Anda tetap menekan Kirim di WhatsApp.</p>
            </form>
        </div>
        <div class="service-visual">
            <div class="service-visual-copy">
                <h2>Janjikan Servis<br><em>dengan mudah</em></h2>
                <p>Pilih tanggal dan waktu yang sesuai, dan tim kami akan siap melayani Anda.</p>
            </div>
        </div>
    </div>
</section>

@php($featuredCars = $featuredCars ?? collect())
<section class="unified-trade-showcase" id="trade-catalog-showcase" data-unified-slider>
    <div class="unified-slider-viewport">
        <div class="unified-slider-track" data-unified-track>
            
            <!-- SLIDE 0: Jual Mobil Anda / Trade-In (Image 2) -->
            <div class="unified-slide unified-slide--trade" id="jual-mobil" data-slide-index="0" role="tabpanel" aria-label="Jual Mobil / Trade-In">
                <div class="trade-banner trade-banner--centered">
                    <div class="show-container trade-layout trade-layout--centered">
                        <div class="trade-copy trade-copy--centered">
                            <h2>Jual Mobil Anda<br><em>atau Trade-In</em></h2>
                            <p>Dapatkan estimasi harga terbaik dengan proses cepat, aman, dan transparan.</p>
                            
                            <!-- Tombol > Trigger Geser ke Katalog Mobil (Image 1) -->
                            <div class="trade-slide-action">
                                <button type="button" class="trade-to-catalog-btn" data-unified-next aria-label="Lihat Kurasi Mobil Pilihan">
                                    <span>Lihat Pilihan Mobil</span>
                                    <span class="trade-btn-arrow-bubble" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="9 18 15 12 9 6"></polyline>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SLIDE 1: Kurasi Mobil Pilihan (Image 1) -->
            <div class="unified-slide unified-slide--catalog" id="mobil" data-slide-index="1" role="tabpanel" aria-label="Kurasi Mobil Pilihan" aria-hidden="true">
                <div class="show-section featured-catalog">
                    <div class="show-container">
                        <div class="show-section-heading">
                            <div>
                                <span class="section-kicker">Mobil pilihan</span>
                                <h2>Kurasi terbaik <em>dari YZA</em></h2>
                            </div>
                            <div class="featured-carousel-actions">
                                <a class="show-link" href="{{ route('mobil.index') }}">
                                    <span>Lihat Semua Mobil</span>
                                </a>
                            </div>
                        </div>

                        <!-- Layout dengan Celah di Sebelah Kiri khusus untuk Tombol < -->
                        <div class="catalog-carousel-with-gutter">
                            <div class="catalog-gutter-nav" aria-label="Navigasi kembali">
                                <button type="button" class="catalog-gutter-prev-btn" data-unified-prev aria-label="Kembali ke Jual Mobil / Trade-In" title="Kembali ke Jual Mobil / Trade-In">
                                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <polyline points="15 18 9 12 15 6"></polyline>
                                    </svg>
                                    <span class="catalog-gutter-tooltip">‹ Jual Mobil / Trade-In</span>
                                </button>
                            </div>

                            <div class="featured-carousel" data-sport-carousel data-carousel-count="{{ $featuredCars->count() }}">
                                <div class="featured-carousel-viewport">
                                    <div class="featured-carousel-track" data-carousel-track>
                                        @forelse ($featuredCars as $vehicle)
                                            <article class="featured-card{{ $vehicle['isSport'] ? ' is-sport' : '' }}">
                                                <a class="featured-media" href="{{ $vehicle['url'] }}">
                                                    @if($vehicle['photo'])
                                                        <img src="{{ $vehicle['photo'] }}" alt="{{ $vehicle['name'] }}" draggable="false">
                                                    @else
                                                        <span class="vehicle-photo-placeholder"><b>{{ $vehicle['brand'] }}</b></span>
                                                    @endif
                                                    <span class="condition-pill">{{ $vehicle['condition'] }}</span>
                                                    <span class="sport-pill">SPORT HIGHLIGHT</span>
                                                    <span class="featured-fav" aria-label="Favorit">♡</span>
                                                </a>
                                                <div class="featured-body">
                                                    <a class="featured-body-link" href="{{ $vehicle['url'] }}">
                                                        <h3>{{ $vehicle['name'] }}</h3>
                                                        <small>{{ $vehicle['specification'] }}</small>
                                                    </a>
                                                    <div class="featured-bottom">
                                                        <a class="featured-price-link" href="{{ $vehicle['url'] }}">
                                                            <strong>Rp {{ number_format($vehicle['price'],0,',','.') }}</strong>
                                                        </a>
                                                        <a class="round-arrow" href="{{ $vehicle['url'] }}" aria-label="Lihat detail">→</a>
                                                    </div>
                                                </div>
                                            </article>
                                        @empty
                                            <div class="featured-card">
                                                <div class="featured-body">
                                                    <h3>Belum ada mobil tersedia</h3>
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="featured-carousel-footer" aria-label="Progress mobil pilihan">
                                    <div class="featured-carousel-progress" data-carousel-progress role="progressbar" aria-label="Progress mobil pilihan" aria-valuemin="1" aria-valuemax="{{ max(1, $featuredCars->count()) }}" aria-valuenow="1">
                                        <span class="is-active"></span>
                                    </div>
                                    <span class="featured-carousel-counter" data-carousel-counter>01 / {{ str_pad((string) max(1, $featuredCars->count()), 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="featured-carousel-hint">Geser horizontal untuk melihat mobil lainnya</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
<section class="show-section experience" id="tentang"><div class="show-container experience-layout"><div class="experience-copy"><h2>Rasakan Sensasi<br><em>Berkendara Langsung.</em></h2><p>Rasakan pengalaman berada di balik kemudi mobil impian Anda. Jelajahi performa, kenyamanan, dan karakter kendaraan melalui layanan Test Drive YZA Automotive.</p><a class="show-cta" href="{{ route('pelanggan.test-drive.create') }}">Jadwalkan Test Drive</a></div><div class="video-tile" data-video-tile><video class="video-tile-media" data-video-player muted playsinline preload="metadata" aria-label="Video pengalaman Test Drive YZA Automotive"><source src="{{ asset('assets/videos/test-drive.mp4') }}" type="video/mp4"></video><button class="video-play" data-video-open type="button" aria-label="Putar video Test Drive YZA Automotive">▶</button><button class="video-mute" data-video-mute type="button" aria-label="Aktifkan suara video" aria-pressed="false"><svg class="video-mute-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9v6h4l5 4V5L8 9H4Z"></path><path class="video-mute-wave" d="M16 9.5c1.5 1.4 1.5 3.6 0 5"></path><path class="video-mute-wave" d="M18.5 7c3 2.8 3 7.2 0 10"></path><path class="video-mute-slash" d="m4 4 16 16"></path></svg></button><span class="video-tile-label"><b>Pengalaman Test Drive</b><small>TEST DRIVE HIGHLIGHT</small></span></div></div></section>
<section class="show-section testimonials" id="testimoni"><div class="show-container"><div class="show-section-heading"><div><span class="section-kicker">Kata mereka</span><h2>Tentang YZA Automotive</h2></div><a class="show-link" href="#testimoni">Lihat Semua Testimoni</a></div><div class="testimonial-grid">@foreach ([['testimonial-andi.png','Andi Pratama','Pelayanan di YZA Automotive sangat profesional dan ramah. Prosesnya cepat, transparan, dan mobilnya berkualitas. Sangat puas!'],['testimonial-siti.png','Siti Rahmawati','Saya melakukan trade-in di sini dan prosesnya sangat mudah. Harga yang ditawarkan juga sangat kompetitif. Recommended banget!'],['testimonial-budi.png','Budi Santoso','Servis mobil di YZA selalu memuaskan. Teknisi ahli, hasil rapi, dan selalu diberi penjelasan yang jelas. Mobil jadi terasa seperti baru lagi!']] as $testimonial)<article class="testimonial-card"><div class="testimonial-person"><img class="testimonial-photo" src="{{ asset('assets/images/lower-landing/'.$testimonial[0]) }}" alt="{{ $testimonial[1] }}"><span><b>{{ $testimonial[1] }}</b><small>Pelanggan</small></span></div><div class="stars" aria-label="5 dari 5 bintang">★★★★★</div><div class="testimonial-quote"><span class="quote-open" aria-hidden="true">“</span><p>{{ $testimonial[2] }}</p><span class="quote-close" aria-hidden="true">”</span></div></article>@endforeach</div></div></section>
<section class="show-section location" id="kontak"><div class="show-container location-layout"><div class="location-copy"><span class="section-kicker">Kunjungi showroom kami</span><h2>Kunjungi Showroom Kami<br><em>dan rasakan langsung</em></h2><p>Lihat langsung koleksi mobil berkualitas kami, dapatkan konsultasi dari tim profesional, dan rasakan pengalaman otomotif terbaik hanya di YZA Automotive.</p><div class="location-points"><span><b aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg></b><strong>Lokasi Strategis</strong><small>Mudah diakses dari berbagai area Jakarta.</small></span><span><b aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 14l2-5h14l2 5v5H3zM5 9l2-4h10l2 4M7 18h.01M17 18h.01"/><path d="M3 14h18"/></svg></b><strong>Koleksi Lengkap</strong><small>Ratusan pilihan mobil berkualitas di showroom kami.</small></span><span><b aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="10" r="2.5"/><path d="M3 20c.4-3.5 2.4-5.2 6-5.2s5.6 1.7 6 5.2M14 15.5c2.8 0 4.4 1.4 4.9 4.5"/></svg></b><strong>Tim Profesional</strong><small>Siap memberikan konsultasi terbaik untuk Anda.</small></span></div><a class="show-cta map-button" href="https://www.google.com/maps/search/YZA+Automotive" target="_blank" rel="noopener">Lihat Lokasi di Google Maps</a></div></div></section>
</main>
<footer class="show-footer"><div class="show-container footer-grid"><div class="footer-brand"><img class="footer-logo" src="{{ asset('assets/images/branding/logo-yza.png') }}" alt="YZA Automotive"><strong>YZA <em>AUTOMOTIVE</em></strong><p>Solusi lengkap untuk semua kebutuhan otomotif Anda. Mobil berkualitas, layanan profesional, dan pengalaman berkendara yang tak terlupakan.</p><div class="social-links"><a href="#" aria-label="Instagram">◎</a><a href="#" aria-label="Facebook">f</a><a href="#" aria-label="YouTube">▶</a><a href="#" aria-label="TikTok">♪</a></div></div><div><h3>Menu</h3><a href="#beranda">Beranda</a><a href="#mobil">Mobil</a><a href="#jual-mobil">Jual Mobil</a><a href="#jual-mobil">Trade-In</a><a href="#servis">Servis</a><a href="#tentang">Tentang Kami</a><a href="#kontak">Kontak</a></div><div><h3>Layanan Kami</h3><a href="#servis">Servis Berkala</a><a href="#servis">Perbaikan Mesin</a><a href="#janji-temu">Ganti Oli</a><a href="#janji-temu">Rem &amp; Suspensi</a><a href="#janji-temu">AC Mobil</a><a href="#janji-temu">Diagnosa Komputer</a><a href="#janji-temu">Janji Temu</a></div><div class="footer-contact"><h3>Kontak</h3><a href="#kontak"><span>⌖</span><span>Jakarta, Indonesia</span></a><a href="{{ yza_whatsapp_url() }}" target="_blank" rel="noopener"><span>⌕</span><span>0877 8430 4417</span></a><a href="#janji-temu"><span>✉</span><span>Hubungi melalui form janji temu</span></a><a href="#janji-temu"><span>◷</span><span>Jadwal melalui janji temu</span></a></div><a class="footer-map-card" href="https://www.google.com/maps/search/YZA+Automotive" target="_blank" rel="noopener" aria-label="Buka YZA Automotive di Google Maps"><span class="map-grid-lines" aria-hidden="true"></span><span class="map-pin">●</span><strong>YZA Automotive</strong><small>Jakarta</small></a></div><div class="show-container footer-bottom"><span>© {{ date('Y') }} YZA Automotive. Semua Hak Dilindungi.</span><span><a href="#">Kebijakan Privasi</a><i></i><a href="#">Syarat &amp; Ketentuan</a></span></div></footer>
<script>document.querySelector('.hero-3d-canvas')?.remove();document.querySelector('.hero-car-stage')?.setAttribute('data-viewer','static-featured-vehicle');</script>
<script>
(() => {
    const nav = document.querySelector('[data-public-navbar]');
    const navLinks = [...nav.querySelectorAll('.show-nav-links > a[href^="#"]')];
    const sections = navLinks.map((link) => ({
        link,
        section: document.getElementById(link.getAttribute('href').slice(1)),
    })).filter(({ section }) => section);
    if (!sections.length) return;

    const setActive = (activeLink) => {
        navLinks.forEach((link) => {
            link.classList.toggle('active', link === activeLink);
            if (link === activeLink) link.setAttribute('aria-current', 'location');
            else link.removeAttribute('aria-current');
        });
    };

    navLinks.forEach((link) => link.addEventListener('click', () => {
        setActive(link);
        document.getElementById('show-nav-links')?.classList.remove('open');
    }));

    // Track section starts rather than intersection ratios: tall sections may
    // never reach an observer's ratio threshold, especially on a phone.
    let scheduled = false;
    const updateActive = () => {
        scheduled = false;
        const headerBottom = nav.getBoundingClientRect().bottom;
        const marker = headerBottom + Math.max(1, (window.innerHeight - headerBottom) * 0.25);
        const ordered = sections.map((entry) => ({ ...entry, top: entry.section.getBoundingClientRect().top }))
            .sort((a, b) => a.top - b.top);
        const current = ordered.filter(({ top }) => top <= marker).at(-1);
        const atBottom = window.scrollY + window.innerHeight >= document.documentElement.scrollHeight - 2;
        setActive(atBottom ? ordered.at(-1).link : current?.link);
    };
    const scheduleUpdate = () => {
        if (scheduled) return;
        scheduled = true;
        requestAnimationFrame(updateActive);
    };
    window.addEventListener('scroll', scheduleUpdate, { passive: true });
    window.addEventListener('resize', scheduleUpdate);
    window.addEventListener('pageshow', scheduleUpdate);
    window.addEventListener('load', scheduleUpdate);
    updateActive();
})();

document.getElementById('appointment-form')?.addEventListener('submit',function(e){e.preventDefault();if(!this.reportValidity())return;const d=new FormData(this);const message=['Halo YZA Automotive,','','Saya ingin membuat janji temu.','',`Nama: ${d.get('nama')}`,`No. WhatsApp: ${d.get('no_hp')}`,`Kebutuhan: ${d.get('layanan')}`,`Tanggal Janji: ${d.get('tanggal')}`,`Jam: ${d.get('jam')}`,'','Catatan:',d.get('catatan')||'-','','Mohon konfirmasi jadwal saya.','','Terima kasih.'].join('\n');window.location.href=@json(yza_whatsapp_url()).replace(/\/$/,'')+'?text='+encodeURIComponent(message)});
</script>
<script>
(() => {
    const tile = document.querySelector('[data-video-tile]');
    const player = tile?.querySelector('[data-video-player]');
    const playButton = tile?.querySelector('[data-video-open]');
    const muteButton = tile?.querySelector('[data-video-mute]');
    if (!tile || !player || !playButton) return;

    let isVisible = false;
    let manuallyPaused = false;
    let pausedByVisibility = false;
    const prefersReducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
    const saveData = Boolean(navigator.connection?.saveData);

    const syncState = () => {
        const playing = !player.paused && !player.ended;
        tile.classList.toggle('is-playing', playing);
        tile.classList.toggle('is-autoplay-blocked', !playing && isVisible && !manuallyPaused);
        playButton.textContent = playing ? 'Ⅱ' : '▶';
        playButton.setAttribute('aria-label', playing ? 'Jeda video test drive YZA Automotive' : 'Putar video test drive YZA Automotive');
        if (muteButton) {
            muteButton.classList.toggle('is-unmuted', !player.muted);
            muteButton.setAttribute('aria-label', player.muted ? 'Aktifkan suara video' : 'Matikan suara video');
            muteButton.setAttribute('aria-pressed', player.muted ? 'false' : 'true');
        }
    };

    const play = (autoplay = false) => {
        if (player.ended) player.currentTime = 0;
        player.muted = true;
        player.play().then(() => {
            if (!autoplay) manuallyPaused = false;
            syncState();
        }).catch(() => {
            tile.classList.add('is-autoplay-blocked');
            syncState();
        });
    };

    const togglePlayback = () => {
        if (player.paused || player.ended) play(false);
        else { manuallyPaused = true; player.pause(); syncState(); }
    };

    playButton.addEventListener('click', togglePlayback);
    tile.addEventListener('click', event => {
        if (event.target === player) { event.preventDefault(); event.stopPropagation(); togglePlayback(); }
    }, true);
    muteButton?.addEventListener('click', event => {
        event.stopPropagation();
        player.muted = !player.muted;
        syncState();
    });
    player.addEventListener('play', syncState);
    player.addEventListener('pause', syncState);
    player.addEventListener('ended', syncState);

    const observer = 'IntersectionObserver' in window ? new IntersectionObserver(entries => {
        const entry = entries[0];
        isVisible = entry.isIntersecting && entry.intersectionRatio >= 0.5;
        if (isVisible && !manuallyPaused && !prefersReducedMotion && !saveData && document.visibilityState === 'visible') play(true);
        else if (!isVisible && !player.paused) { pausedByVisibility = true; player.pause(); syncState(); }
    }, { threshold: [0, 0.5, 1] }) : null;
    observer?.observe(tile);

    const handleVisibility = () => {
        if (document.visibilityState === 'hidden') { if (!player.paused) pausedByVisibility = true; player.pause(); }
        else if (pausedByVisibility && isVisible && !manuallyPaused && !prefersReducedMotion && !saveData) { pausedByVisibility = false; play(true); }
        syncState();
    };
    document.addEventListener('visibilitychange', handleVisibility);
    window.addEventListener('pagehide', () => observer?.disconnect(), { once: true });
    syncState();
})();
</script>
<script>
(() => {
    const card = document.querySelector('.hero-vehicle-info[data-vehicle-id]');
    const heart = card?.querySelector('[data-featured-favorite]');
    if (!card || !heart) return;

    const vehicleId = String(card.dataset.vehicleId || '');
    const storageKey = 'yza_favorite_cars';
    const readFavorites = () => {
        try {
            const value = JSON.parse(localStorage.getItem(storageKey) || '[]');
            return Array.isArray(value) ? value.map(String) : [];
        } catch (error) { return []; }
    };
    const writeFavorites = (favorites) => {
        try { localStorage.setItem(storageKey, JSON.stringify(favorites)); } catch (error) {}
    };
    const setFavoriteState = (active) => {
        heart.textContent = active ? '♥' : '♡';
        heart.setAttribute('aria-pressed', String(active));
        heart.setAttribute('aria-label', `${active ? 'Hapus' : 'Tambahkan'} ${card.querySelector('h2')?.textContent.trim() || 'mobil'} ${active ? 'dari' : 'ke'} favorit`);
        heart.classList.toggle('is-favorited', active);
    };

    setFavoriteState(readFavorites().includes(vehicleId));
    heart.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        const favorites = readFavorites();
        const active = !favorites.includes(vehicleId);
        writeFavorites(active ? [...favorites, vehicleId] : favorites.filter((id) => id !== vehicleId));
        setFavoriteState(active);
        heart.classList.remove('is-popping');
        window.requestAnimationFrame(() => heart.classList.add('is-popping'));
    });
})();

(() => {
    const storageKey = 'yza_favorite_cars';
    const readFavorites = () => {
        try { const value = JSON.parse(localStorage.getItem(storageKey) || '[]'); return Array.isArray(value) ? value.map(String) : []; } catch (error) { return []; }
    };
    const writeFavorites = (favorites) => { try { localStorage.setItem(storageKey, JSON.stringify(favorites)); } catch (error) {} };
    const setState = (heart, active, name) => {
        heart.textContent = active ? '♥' : '♡';
        heart.classList.toggle('is-favorited', active);
        heart.setAttribute('role', 'button');
        heart.setAttribute('tabindex', '0');
        heart.setAttribute('aria-pressed', String(active));
        heart.setAttribute('aria-label', `${active ? 'Hapus' : 'Tambahkan'} ${name} ${active ? 'dari' : 'ke'} favorit`);
    };
    const toggle = (heart, event) => {
        event.preventDefault();
        event.stopPropagation();
        const card = heart.closest('.featured-card');
        const vehicleId = (() => {
            const href = card?.querySelector('.featured-media')?.href || '';
            try { return new URL(href, window.location.href).pathname; } catch (error) { return href; }
        })();
        const name = card?.querySelector('h3')?.textContent.trim() || 'mobil';
        const favorites = readFavorites();
        const active = !favorites.includes(vehicleId);
        writeFavorites(active ? [...favorites, vehicleId] : favorites.filter((favorite) => favorite !== vehicleId));
        setState(heart, active, name);
        heart.classList.remove('is-popping');
        window.requestAnimationFrame(() => heart.classList.add('is-popping'));
    };
    document.querySelectorAll('.featured-card .featured-fav').forEach((heart) => {
        const media = heart.closest('.featured-media');
        if (!media) return;
        const button = document.createElement('button');
        button.type = 'button';
        button.className = heart.className;
        button.innerHTML = heart.innerHTML;
        [...heart.attributes].forEach((attribute) => {
            if (attribute.name !== 'class') button.setAttribute(attribute.name, attribute.value);
        });
        media.after(button);
        heart.remove();
    });
    document.querySelectorAll('.featured-card .featured-fav').forEach((heart) => {
        const card = heart.closest('.featured-card');
        const link = card?.querySelector('.featured-media');
        const vehicleId = (() => {
            const href = link?.href || '';
            try { return new URL(href, window.location.href).pathname; } catch (error) { return href; }
        })();
        const name = card?.querySelector('h3')?.textContent.trim() || 'mobil';
        setState(heart, readFavorites().includes(vehicleId), name);
    });
    document.addEventListener('click', (event) => { const heart = event.target.closest?.('.featured-card .featured-fav'); if (heart) toggle(heart, event); });
    document.addEventListener('keydown', (event) => { const heart = event.target.closest?.('.featured-card .featured-fav'); if (heart && (event.key === 'Enter' || event.key === ' ')) toggle(heart, event); });
})();

(function initSportCarousel() {
    const carousel = document.querySelector('[data-sport-carousel]');
    if (!carousel) return;
    const track = carousel.querySelector('[data-carousel-track]');
    const viewport = carousel.querySelector('.featured-carousel-viewport');
    const progress = carousel.querySelector('[data-carousel-progress]');
    const counter = carousel.querySelector('[data-carousel-counter]');
    const section = carousel.closest('.featured-catalog');
    const controls = section?.querySelector('.featured-carousel-controls');
    const previousButton = section?.querySelector('[data-carousel-prev]');
    const nextButton = section?.querySelector('[data-carousel-next]');
    // Use the browser's horizontal scrolling for the catalog. Pointer dragging
    // adds the same interaction on desktop while preserving normal card links.
    const cards = Array.from(track.children);
    if (!cards.length) return;
    viewport.classList.add('native-carousel');
    track.classList.add('native-carousel-track');
    const updateNativeMeta = () => {
        const card = cards[0];
        const gap = parseFloat(getComputedStyle(track).gap || '0');
        const step = (card?.getBoundingClientRect().width || viewport.clientWidth) + gap;
        const position = Math.min(cards.length, Math.max(1, Math.round(viewport.scrollLeft / step) + 1));
        progress?.setAttribute('aria-valuenow', String(position));
        counter && (counter.textContent = `${String(position).padStart(2, '0')} / ${String(cards.length).padStart(2, '0')}`);
    };
    viewport.addEventListener('scroll', updateNativeMeta, { passive: true });
    updateNativeMeta();
    return;
    // Make navigation a real sibling of the card viewport so overflow clipping
    // can never place the controls on top of a vehicle card.
    if (controls && controls.parentElement !== carousel) carousel.prepend(controls);
    const originalCards = Array.from(track.children);
    const total = originalCards.length;
    if (!total) return;
    const reducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
    let visible = 1;
    let step = 0;
    let current = total;
    let dragging = false;
    let pointerStart = null;
    let dragStartCurrent = 0;
    let pointerLastX = 0;
    let animationFrame = 0;
    let lastFrameTime = 0;
    let userInteracting = false;
    let isHovered = false;
    let resumeTimer = 0;
    const dragActivationThreshold = 8;
    const swipeThreshold = 32;
    const manualSnapDuration = 420;

    const getVisible = () => window.innerWidth >= 1451 ? 4 : window.innerWidth >= 1001 ? 3 : window.innerWidth >= 651 ? 2 : 1;
    const getPages = () => Math.max(1, Math.ceil(total / visible));
    const setCloneAccessibility = (card) => {
        card.setAttribute('aria-hidden', 'true');
        card.querySelectorAll('a,button,[tabindex]').forEach((element) => element.setAttribute('tabindex', '-1'));
        return card;
    };
    const rebuild = (instant = false) => {
        visible = getVisible();
        const before = originalCards.map((card) => setCloneAccessibility(card.cloneNode(true)));
        const after = originalCards.map((card) => setCloneAccessibility(card.cloneNode(true)));
        track.replaceChildren(...before, ...originalCards, ...after);
        const card = track.querySelector('.featured-card');
        const gap = parseFloat(getComputedStyle(track).gap || '0');
        step = card ? card.getBoundingClientRect().width + gap : 0;
        current = total;
        track.style.transition = 'none';
        track.style.transform = `translate3d(${-current * step}px, 0, 0)`;
        updateMeta();
    };
    const logicalIndex = () => Math.floor(((current - total) % total + total) % total);
    const updateMeta = () => {
        const index = logicalIndex();
        const position = index + 1;
        if (progress) {
            progress.setAttribute('aria-valuenow', String(position));
            progress.style.setProperty('--carousel-progress', `${(position / total) * 100}%`);
        }
        if (counter) counter.textContent = `${String(position).padStart(2, '0')} / ${String(total).padStart(2, '0')}`;
    };
    const render = (withSnap = false) => {
        track.style.transition = withSnap ? `transform ${manualSnapDuration}ms cubic-bezier(.22,.61,.36,1)` : 'none';
        track.style.transform = `translate3d(${-current * step}px, 0, 0)`;
        updateMeta();
    };
    const normalizeLoopPosition = () => {
        if (current >= total * 2) current -= total;
        if (current <= 0) current += total;
    };
    const resumeAutoplay = () => {
        window.clearTimeout(resumeTimer);
        resumeTimer = window.setTimeout(() => {
            normalizeLoopPosition();
            track.style.transition = 'none';
            render();
            userInteracting = false;
        }, manualSnapDuration);
    };
    const move = (direction) => {
        if (!step || dragging || userInteracting) return;
        userInteracting = true;
        current = Math.round(current) + direction;
        render(true);
        resumeAutoplay();
    };
    const animate = (timestamp) => {
        if (!lastFrameTime) lastFrameTime = timestamp;
        const elapsed = Math.min(80, timestamp - lastFrameTime);
        lastFrameTime = timestamp;
        if (!reducedMotion && total > 1 && !isHovered && !userInteracting && step) {
            current += elapsed / 11000;
            if (current >= total * 2) current -= total;
            track.style.transform = `translate3d(${-current * step}px, 0, 0)`;
            const position = logicalIndex() + 1;
            if (progress?.getAttribute('aria-valuenow') !== String(position)) updateMeta();
        }
        animationFrame = window.requestAnimationFrame(animate);
    };
    previousButton?.addEventListener('click', () => move(-1));
    nextButton?.addEventListener('click', () => move(1));
    viewport.addEventListener('pointerdown', (event) => {
        if (event.button !== undefined && event.button !== 0) return;
        pointerStart = { x: event.clientX, y: event.clientY };
        dragStartCurrent = current;
        pointerLastX = event.clientX;
        dragging = false;
        userInteracting = true;
        window.clearTimeout(resumeTimer);
        track.style.transition = 'none';
    });
    // Prevent the browser's native link/image drag from winning over the
    // horizontal carousel gesture. Normal click navigation remains untouched.
    viewport.addEventListener('dragstart', (event) => event.preventDefault());
    // Track movement on the window instead of the card itself. This keeps the
    // swipe alive when the cursor leaves the anchor, without capturing clicks.
    window.addEventListener('pointermove', (event) => {
        if (!pointerStart) return;
        const dx = event.clientX - pointerStart.x;
        const dy = event.clientY - pointerStart.y;
        if (!dragging && Math.abs(dx) < dragActivationThreshold && Math.abs(dy) < dragActivationThreshold) return;
        if (!dragging && Math.abs(dy) > Math.abs(dx) * 1.25) {
            pointerStart = null;
            userInteracting = false;
            return;
        }
        dragging = true;
        userInteracting = true;
        event.preventDefault();
        current = dragStartCurrent - (event.clientX - pointerStart.x) / step;
        pointerLastX = event.clientX;
        track.style.transform = `translate3d(${-current * step}px, 0, 0)`;
    });
    const finishDrag = (event) => {
        if (!pointerStart) {
            dragging = false;
            pointerLastX = 0;
            dragStartCurrent = current;
            userInteracting = false;
            return;
        }
        const wasDragging = dragging;
        const distance = pointerLastX - pointerStart.x;
        const cancelled = event?.type === 'pointercancel';
        pointerStart = null;
        pointerLastX = 0;
        current = wasDragging && Math.abs(distance) >= swipeThreshold
            ? dragStartCurrent + (distance < 0 ? 1 : -1)
            : dragStartCurrent;
        dragging = false;
        const committedSwipe = !cancelled && wasDragging && Math.abs(distance) >= swipeThreshold;
        render(committedSwipe);
        if (committedSwipe) resumeAutoplay();
        else {
            track.style.transition = 'none';
            userInteracting = false;
        }
        dragStartCurrent = current;
    };
    viewport.addEventListener('pointerup', (event) => finishDrag(event));
    viewport.addEventListener('pointercancel', (event) => finishDrag(event));
    viewport.addEventListener('lostpointercapture', (event) => finishDrag(event));
    window.addEventListener('pointerup', (event) => finishDrag(event));
    window.addEventListener('pointercancel', (event) => finishDrag(event));
    viewport.addEventListener('mouseenter', () => { isHovered = true; });
    viewport.addEventListener('mouseleave', () => { isHovered = false; });
    // Do not intercept clicks here. Vehicle cards are real anchors and must keep
    // native navigation, including Ctrl/Cmd-click and middle-click behavior.
    let resizeFrame = 0;
    window.addEventListener('resize', () => { window.cancelAnimationFrame(resizeFrame); resizeFrame = window.requestAnimationFrame(() => rebuild(true)); });
    rebuild(true);
    animationFrame = window.requestAnimationFrame(animate);
})();
</script>
<script>
(() => {
    const drawServiceIcons = () => {
        document.querySelectorAll('.yza-service-canvas, .yza-button-canvas').forEach((canvas) => {
            const context = canvas.getContext('2d');
            if (!context) return;
            const ratio = window.devicePixelRatio || 1;
            const size = 72;
            const isButton = canvas.classList.contains('yza-button-canvas');
            canvas.width = size * ratio;
            canvas.height = size * ratio;
            context.setTransform(ratio, 0, 0, ratio, 0, 0);
            context.clearRect(0, 0, size, size);
            if (isButton) {
                context.translate(36, 36);
                context.scale(1.6, 1.6);
                context.translate(-36, -36);
            }
            context.lineCap = 'round';
            context.lineJoin = 'round';
            if (!isButton) {
                context.strokeStyle = '#f22631';
                context.lineWidth = 1.8;
                context.beginPath();
                context.moveTo(17, 9); context.lineTo(7, 17); context.lineTo(7, 55); context.lineTo(17, 63);
                context.moveTo(55, 9); context.lineTo(65, 17); context.lineTo(65, 55); context.lineTo(55, 63);
                context.stroke();
            }
            context.strokeStyle = '#fff';
            context.lineWidth = 2.2;
            const icon = canvas.dataset.serviceIcon;
            if (icon === 'car') {
                context.beginPath();
                context.moveTo(22, 41); context.lineTo(24.5, 29); context.lineTo(29, 24); context.lineTo(43, 24); context.lineTo(48, 29); context.lineTo(50, 41); context.lineTo(50, 47); context.lineTo(22, 47); context.closePath();
                context.moveTo(25, 29); context.lineTo(47, 29);
                context.moveTo(22, 38); context.lineTo(50, 38);
                context.moveTo(28, 47); context.lineTo(28, 51); context.moveTo(44, 47); context.lineTo(44, 51);
                context.stroke();
                context.beginPath(); context.arc(28, 41, 1.2, 0, Math.PI * 2); context.arc(44, 41, 1.2, 0, Math.PI * 2); context.stroke();
            } else if (icon === 'wrench') {
                context.beginPath();
                context.moveTo(52, 15);
                context.bezierCurveTo(47, 11, 39, 13, 37, 18);
                context.lineTo(43, 24);
                context.lineTo(25, 42);
                context.lineTo(20, 40);
                context.lineTo(16, 45);
                context.lineTo(22, 51);
                context.lineTo(27, 47);
                context.lineTo(25, 42);
                context.lineTo(43, 24);
                context.lineTo(49, 30);
                context.bezierCurveTo(54, 28, 56, 20, 52, 15);
                context.lineTo(46, 20);
                context.lineTo(41, 15);
                context.closePath();
                context.stroke();
            } else {
                context.beginPath();
                for (let i = 0; i < 24; i += 1) {
                    const angle = -Math.PI / 2 + i * Math.PI / 12;
                    const radius = i % 3 === 1 ? 19 : i % 3 === 2 ? 15 : 18;
                    const x = 36 + Math.cos(angle) * radius;
                    const y = 36 + Math.sin(angle) * radius;
                    if (i === 0) context.moveTo(x, y); else context.lineTo(x, y);
                }
                context.closePath();
                context.moveTo(41, 36); context.arc(36, 36, 5, 0, Math.PI * 2);
                context.stroke();
            }
        });
    };

    const initYzaIntro = () => {
        const intro = document.getElementById('yza-intro');
        if (!intro) return;
        drawServiceIcons();

        let finished = false;
        const finishIntro = () => {
            if (finished) return;
            finished = true;
            document.documentElement.classList.remove('yza-intro-playing');
            document.body.classList.remove('yza-intro-playing');
            document.body.classList.remove('opening-active');
            try { window.sessionStorage.setItem('yza_intro_seen', '1'); } catch (error) {}
            intro.classList.add('is-opening');
            intro.setAttribute('aria-hidden', 'true');
            intro.addEventListener('transitionend', (event) => {
                if (event.target !== intro || event.propertyName !== 'opacity') return;
                intro.remove();
            }, { once: true });
        };

        try {
            if (window.sessionStorage.getItem('yza_intro_seen') === '1') {
                document.body.classList.remove('opening-active');
                intro.remove();
                return;
            }
        } catch (error) {}

        document.documentElement.classList.add('yza-intro-playing');
        document.body.classList.add('yza-intro-playing');
        intro.querySelector('.yza-intro-enter')?.addEventListener('click', (event) => {
            event.currentTarget.classList.add('is-activating');
            finishIntro(false);
        }, { once: true });

        if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
            return;
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initYzaIntro, { once: true });
    } else {
        initYzaIntro();
    }
})();
</script>
</body></html>
