@php
    $memberOwnerLocked = !(auth()->user()?->pelanggan && app(\App\Services\VerifiedCarOwnershipService::class)->customerHasVerifiedPurchase((int) auth()->user()->pelanggan->id_pelanggan));
@endphp
@php
    $memberCustomer = auth()->user()?->pelanggan;
    $servicesActive = request()->routeIs('pelanggan.booking-servis.*', 'pelanggan.test-drive.*', 'pelanggan.booking-cuci.*', 'pelanggan.trade-in.*');
    $activityActive = request()->routeIs('pelanggan.booking.*', 'pelanggan.booking.index', 'pelanggan.servis.*');
    $benefitActive = request()->routeIs('pelanggan.promo.*');
    $memberOrdersCount = $memberCustomer
        ? \App\Models\PesananMobilMember::where('id_pelanggan', $memberCustomer->id_pelanggan)
            ->whereNotIn('status', ['diserahkan', 'selesai', 'dibatalkan', 'ditolak'])
            ->count()
        : 0;
@endphp
<aside class="member-sidebar" data-member-sidebar aria-label="Navigasi YZA Member">
    <div class="member-sidebar-brand">
        <a href="{{ route('pelanggan.member') }}" class="member-brand-link">
            <img src="{{ asset('images/branding/logo-yza.png') }}" alt="YZA Automotive">
            <span><strong>YZA AUTOMOTIVE</strong><small>Premium Member Portal</small></span>
        </a>
    </div>

    <nav class="member-sidebar-nav" aria-label="Menu member">
        <div class="member-nav-group {{ request()->routeIs('pelanggan.katalog-mobil.*','pelanggan.preorder.*') ? 'is-open' : '' }}" data-member-nav-group><button class="member-nav-link member-nav-button {{ request()->routeIs('pelanggan.katalog-mobil.*','pelanggan.preorder.*') ? 'is-active' : '' }}" type="button" aria-expanded="{{ request()->routeIs('pelanggan.katalog-mobil.*','pelanggan.preorder.*') ? 'true' : 'false' }}"><span class="member-nav-icon">▱</span><span>Beli Mobil</span><b>⌄</b></button><div class="member-subnav" {{ request()->routeIs('pelanggan.katalog-mobil.*','pelanggan.preorder.*') ? '' : 'hidden' }}><a class="{{ request()->routeIs('pelanggan.katalog-mobil.*') ? 'is-active' : '' }}" href="{{ route('pelanggan.katalog-mobil.index') }}">Katalog Mobil</a><a class="{{ request()->routeIs('pelanggan.preorder.*') ? 'is-active' : '' }}" href="{{ route('pelanggan.preorder.index') }}">Pre-Order</a></div></div>
        <a class="member-nav-link {{ request()->routeIs('pelanggan.pesanan-mobil.*') ? 'is-active' : '' }}" href="{{ route('pelanggan.pesanan-mobil.index') }}"><span class="member-nav-icon">•</span><span>Pesanan Saya</span><span class="member-nav-counter-badge" data-notification-pending="orders" data-count-label="pesanan aktif" @if($memberOrdersCount < 1) hidden @endif>{{ $memberOrdersCount > 99 ? '99+' : $memberOrdersCount }}</span></a>
        <a class="member-nav-link {{ request()->routeIs('pelanggan.kendaraan.*') ? 'is-active' : '' }}" href="{{ $memberOwnerLocked ? '#' : route('pelanggan.kendaraan.index') }}" @if($memberOwnerLocked) data-owner-locked aria-disabled="true" tabindex="-1" title="Terkunci: tersedia setelah Anda memiliki mobil dari YZA" @endif><span class="member-nav-icon">▣</span><span>Kendaraan Saya</span></a>

        <div class="member-nav-group {{ $servicesActive ? 'is-open' : '' }}" data-member-nav-group>
            <button class="member-nav-link member-nav-button {{ $servicesActive ? 'is-active' : '' }}" type="button" aria-expanded="{{ $servicesActive ? 'true' : 'false' }}"><span class="member-nav-icon">⚙</span><span>Layanan Otomotif</span><b>⌄</b></button>
            <div class="member-subnav" {{ $servicesActive ? '' : 'hidden' }}>
                <a class="{{ request()->routeIs('pelanggan.booking-servis.*') ? 'is-active' : '' }}" href="{{ $memberOwnerLocked ? '#' : route('pelanggan.booking-servis.index') }}" @if($memberOwnerLocked) data-owner-locked aria-disabled="true" tabindex="-1" title="Terkunci: tersedia setelah Anda memiliki mobil dari YZA" @endif>Booking Servis</a>
                <a class="{{ request()->routeIs('pelanggan.test-drive.*') ? 'is-active' : '' }}" href="{{ route('pelanggan.test-drive.index') }}">Test Drive</a>
                <a class="{{ request()->routeIs('pelanggan.booking-cuci.*') ? 'is-active' : '' }}" href="{{ $memberOwnerLocked ? '#' : route('pelanggan.booking-cuci.index') }}" @if($memberOwnerLocked) data-owner-locked aria-disabled="true" tabindex="-1" title="Terkunci: tersedia setelah Anda memiliki mobil dari YZA" @endif>Car Wash</a>
                <a class="{{ request()->routeIs('pelanggan.trade-in.*') ? 'is-active' : '' }}" href="{{ $memberOwnerLocked ? '#' : route('pelanggan.trade-in.index') }}" @if($memberOwnerLocked) data-owner-locked aria-disabled="true" tabindex="-1" title="Terkunci: tersedia setelah Anda memiliki mobil dari YZA" @endif>Trade-In</a>
            </div>
        </div>

        <div class="member-nav-group {{ $activityActive ? 'is-open' : '' }}" data-member-nav-group>
            <button class="member-nav-link member-nav-button {{ $activityActive ? 'is-active' : '' }}" type="button" aria-expanded="{{ $activityActive ? 'true' : 'false' }}"><span class="member-nav-icon">◷</span><span>Aktivitas Saya</span><b>⌄</b></button>
            <div class="member-subnav" {{ $activityActive ? '' : 'hidden' }}>
                <a class="{{ request()->routeIs('pelanggan.booking.*') ? 'is-active' : '' }}" href="{{ $memberOwnerLocked ? '#' : route('pelanggan.booking.index') }}" @if($memberOwnerLocked) data-owner-locked aria-disabled="true" tabindex="-1" title="Terkunci: tersedia setelah Anda memiliki mobil dari YZA" @endif>Booking Saya</a>
                <a href="{{ route('pelanggan.member') }}#transaksi">Transaksi &amp; Pembayaran</a>
            </div>
        </div>

        <div class="member-nav-group {{ $benefitActive ? 'is-open' : '' }}" data-member-nav-group>
            <button class="member-nav-link member-nav-button {{ $benefitActive ? 'is-active' : '' }}" type="button" aria-expanded="{{ $benefitActive ? 'true' : 'false' }}"><span class="member-nav-icon">♕</span><span>Benefit Member</span><b>⌄</b></button>
            <div class="member-subnav" {{ $benefitActive ? '' : 'hidden' }}>
                <a class="{{ request()->routeIs('pelanggan.promo.*') ? 'is-active' : '' }}" href="{{ route('pelanggan.promo.index') }}">Promo Member</a>
                <a href="#yza-points" data-open-yza-points-modal>YZA Points</a>
            </div>
        </div>
    </nav>

    <div class="member-sidebar-footer">
        <div class="member-access-card">
            <span class="member-access-icon">♕</span>
            <strong>EXCLUSIVE MEMBER ACCESS</strong>
            <p>Dapatkan pengalaman otomotif premium bersama YZA Automotive.</p>
        </div>
        <div class="member-sidebar-status"><span>Status Membership</span><strong>{{ strtoupper($memberCustomer?->membership?->status ?? 'guest') }}</strong></div>
    </div>
</aside>
