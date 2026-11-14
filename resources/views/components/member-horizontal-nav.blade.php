@php
    $memberOwnerLocked = !(auth()->user()?->pelanggan && app(\App\Services\VerifiedCarOwnershipService::class)->customerHasVerifiedPurchase((int) auth()->user()->pelanggan->id_pelanggan));
@endphp
@php
    $memberSummary = $notificationState ?? app(\App\Services\NotificationStateService::class)->summary(auth()->user());
    $memberCategoryUnread = $memberSummary['category_unread'] ?? [];
    $memberPendingCounts = $memberSummary['pending_counts'] ?? [];
    $memberOrdersCount = isset($memberPendingCounts['orders'])
        ? (int) $memberPendingCounts['orders']
        : (auth()->user()?->pelanggan
            ? \App\Models\PesananMobilMember::where('id_pelanggan', auth()->user()->pelanggan->id_pelanggan)
                ->whereNotIn('status', ['diserahkan', 'selesai', 'dibatalkan', 'ditolak'])
                ->count()
            : 0);
@endphp
<nav class="member-horizontal-nav" aria-label="Navigasi Member Center">
    <a class="member-horizontal-link {{ request()->routeIs('pelanggan.member','pelanggan.dashboard') ? 'is-active' : '' }}" href="{{ route('pelanggan.member') }}"><span class="member-menu-icon" aria-hidden="true"><x-member-nav-icon name="crown" /></span><span class="member-menu-label">Member Center</span></a>
    <a class="member-horizontal-link {{ request()->routeIs('pelanggan.kendaraan.*') ? 'is-active' : '' }}" href="{{ $memberOwnerLocked ? '#' : route('pelanggan.kendaraan.index') }}" @if($memberOwnerLocked) data-owner-locked aria-disabled="true" tabindex="-1" title="Terkunci: tersedia setelah Anda memiliki mobil dari YZA" @endif><span class="member-menu-icon" aria-hidden="true"><x-member-nav-icon name="garage" /></span><span class="member-menu-label">Kendaraan Saya<x-notification-dot category="garage" :count="$memberCategoryUnread['garage'] ?? 0" /></span></a>
    <details class="member-horizontal-menu {{ request()->routeIs('pelanggan.booking.*','pelanggan.servis.*') ? 'is-active' : '' }}"><summary><span class="member-menu-icon" aria-hidden="true"><x-member-nav-icon name="activity" /></span><span class="member-menu-label">Aktivitas Saya<x-notification-dot category="activity" :count="$memberCategoryUnread['activity'] ?? 0" /></span><span class="member-menu-chevron" aria-hidden="true"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m6 9 6 6 6-6"/></svg></span></summary><div class="member-horizontal-dropdown"><a href="{{ $memberOwnerLocked ? '#' : route('pelanggan.booking.index') }}" @if($memberOwnerLocked) data-owner-locked aria-disabled="true" tabindex="-1" title="Terkunci: tersedia setelah Anda memiliki mobil dari YZA" @endif>Booking Saya</a><a href="{{ $memberOwnerLocked ? '#' : route('pelanggan.servis.index') }}" @if($memberOwnerLocked) data-owner-locked aria-disabled="true" tabindex="-1" title="Terkunci: tersedia setelah Anda memiliki mobil dari YZA" @endif>Riwayat Servis</a><a href="{{ route('pelanggan.member') }}#transaksi">Transaksi &amp; Pembayaran</a></div></details>
    <a class="member-horizontal-link {{ request()->routeIs('pelanggan.pesanan-mobil.*') ? 'is-active' : '' }}" href="{{ route('pelanggan.pesanan-mobil.index') }}"><span class="member-menu-icon" aria-hidden="true"><x-member-nav-icon name="orders" /></span><span class="member-menu-label">Pesanan Saya<span class="member-nav-counter-badge" data-notification-pending="orders" data-count-label="pesanan aktif" @if($memberOrdersCount < 1) hidden @endif>{{ $memberOrdersCount > 99 ? '99+' : $memberOrdersCount }}</span></span></a>
    <details class="member-horizontal-menu {{ request()->routeIs('pelanggan.booking-servis.*','pelanggan.test-drive.*','pelanggan.booking-cuci.*','pelanggan.trade-in.*','pelanggan.pengajuan-mobil.*') ? 'is-active' : '' }}"><summary><span class="member-menu-icon" aria-hidden="true"><x-member-nav-icon name="tools" /></span><span class="member-menu-label">Layanan Otomotif<x-notification-dot category="services" :count="$memberCategoryUnread['services'] ?? 0" /></span><span class="member-menu-chevron" aria-hidden="true"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m6 9 6 6 6-6"/></svg></span></summary><div class="member-horizontal-dropdown"><a href="{{ $memberOwnerLocked ? '#' : route('pelanggan.booking-servis.index') }}" @if($memberOwnerLocked) data-owner-locked aria-disabled="true" tabindex="-1" title="Terkunci: tersedia setelah Anda memiliki mobil dari YZA" @endif>Booking Servis</a><a href="{{ route('pelanggan.test-drive.index') }}">Test Drive</a><a href="{{ $memberOwnerLocked ? '#' : route('pelanggan.booking-cuci.index') }}" @if($memberOwnerLocked) data-owner-locked aria-disabled="true" tabindex="-1" title="Terkunci: tersedia setelah Anda memiliki mobil dari YZA" @endif>Car Wash</a><a href="{{ $memberOwnerLocked ? '#' : route('pelanggan.trade-in.index') }}" @if($memberOwnerLocked) data-owner-locked aria-disabled="true" tabindex="-1" title="Terkunci: tersedia setelah Anda memiliki mobil dari YZA" @endif>Trade-In</a></div></details>
    <details class="member-horizontal-menu {{ request()->routeIs('pelanggan.katalog-mobil.*','pelanggan.preorder.*') ? 'is-active' : '' }}"><summary><span class="member-menu-icon" aria-hidden="true"><x-member-nav-icon name="car" /></span><span class="member-menu-label">Beli Mobil<x-notification-dot category="buy" :count="$memberCategoryUnread['buy'] ?? 0" /></span><span class="member-menu-chevron" aria-hidden="true"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m6 9 6 6 6-6"/></svg></span></summary><div class="member-horizontal-dropdown"><a href="{{ route('pelanggan.katalog-mobil.index') }}">Katalog Mobil</a><a href="{{ route('pelanggan.preorder.index') }}">Pre-Order</a></div></details>
</nav>
<aside class="member-mobile-drawer" id="member-mobile-menu" data-member-mobile-drawer aria-label="Menu Member Center" aria-hidden="true" inert>
    <div class="member-mobile-drawer-head"><div><small>NAVIGASI AKUN</small><strong>Menu Member</strong></div><button type="button" data-member-mobile-close aria-label="Tutup menu">×</button></div>
    <nav class="member-mobile-drawer-links" aria-label="Pilihan menu member">
        <section class="member-mobile-group"><h3>Member Hub</h3><div class="member-mobile-tags">
            <a href="{{ route('pelanggan.member') }}" @if(request()->routeIs('pelanggan.member','pelanggan.dashboard')) aria-current="page" @endif><span>Member Center</span><span class="member-mobile-tag-arrow" aria-hidden="true">›</span></a>
        </div></section>
        <section class="member-mobile-group"><h3>Garasi Saya</h3><div class="member-mobile-tags">
            <a href="{{ $memberOwnerLocked ? '#' : route('pelanggan.kendaraan.index') }}" @if($memberOwnerLocked) data-owner-locked aria-disabled="true" tabindex="-1" title="Terkunci: tersedia setelah Anda memiliki mobil dari YZA" @endif @if(request()->routeIs('pelanggan.kendaraan.index')) aria-current="page" @endif><span class="member-menu-label">Kendaraan Saya<x-notification-dot category="garage" :count="$memberCategoryUnread['garage'] ?? 0" /></span><span class="member-mobile-tag-arrow" aria-hidden="true">›</span></a>
        </div></section>
        <section class="member-mobile-group"><h3><span class="member-menu-label">Aktivitas Saya<x-notification-dot category="activity" :count="$memberCategoryUnread['activity'] ?? 0" /></span></h3><div class="member-mobile-tags">
            <a href="{{ $memberOwnerLocked ? '#' : route('pelanggan.booking.index') }}" @if($memberOwnerLocked) data-owner-locked aria-disabled="true" tabindex="-1" title="Terkunci: tersedia setelah Anda memiliki mobil dari YZA" @endif @if(request()->routeIs('pelanggan.booking.index')) aria-current="page" @endif><span>Booking Saya</span><span class="member-mobile-tag-arrow" aria-hidden="true">›</span></a>
            <a href="{{ $memberOwnerLocked ? '#' : route('pelanggan.servis.index') }}" @if($memberOwnerLocked) data-owner-locked aria-disabled="true" tabindex="-1" title="Terkunci: tersedia setelah Anda memiliki mobil dari YZA" @endif @if(request()->routeIs('pelanggan.servis.index')) aria-current="page" @endif><span>Riwayat Servis</span><span class="member-mobile-tag-arrow" aria-hidden="true">›</span></a>
            <a href="{{ route('pelanggan.member') }}#transaksi"><span>Transaksi &amp; Pembayaran</span><span class="member-mobile-tag-arrow" aria-hidden="true">›</span></a>
        </div></section>
        <section class="member-mobile-group"><h3>Pesanan Saya</h3><div class="member-mobile-tags">
            <a href="{{ route('pelanggan.pesanan-mobil.index') }}" @if(request()->routeIs('pelanggan.pesanan-mobil.index')) aria-current="page" @endif><span class="member-menu-label">Pesanan Mobil<span class="member-nav-counter-badge" data-notification-pending="orders" data-count-label="pesanan aktif" @if($memberOrdersCount < 1) hidden @endif>{{ $memberOrdersCount > 99 ? '99+' : $memberOrdersCount }}</span></span><span class="member-mobile-tag-arrow" aria-hidden="true">›</span></a>
        </div></section>
        <section class="member-mobile-group"><h3><span class="member-menu-label">Layanan Otomotif<x-notification-dot category="services" :count="$memberCategoryUnread['services'] ?? 0" /></span></h3><div class="member-mobile-tags">
            <a href="{{ $memberOwnerLocked ? '#' : route('pelanggan.booking-servis.index') }}" @if($memberOwnerLocked) data-owner-locked aria-disabled="true" tabindex="-1" title="Terkunci: tersedia setelah Anda memiliki mobil dari YZA" @endif @if(request()->routeIs('pelanggan.booking-servis.index')) aria-current="page" @endif><span>Booking Servis</span><span class="member-mobile-tag-arrow" aria-hidden="true">›</span></a>
            <a href="{{ route('pelanggan.test-drive.index') }}" @if(request()->routeIs('pelanggan.test-drive.index')) aria-current="page" @endif><span>Test Drive</span><span class="member-mobile-tag-arrow" aria-hidden="true">›</span></a>
            <a href="{{ $memberOwnerLocked ? '#' : route('pelanggan.booking-cuci.index') }}" @if($memberOwnerLocked) data-owner-locked aria-disabled="true" tabindex="-1" title="Terkunci: tersedia setelah Anda memiliki mobil dari YZA" @endif @if(request()->routeIs('pelanggan.booking-cuci.index')) aria-current="page" @endif><span>Car Wash</span><span class="member-mobile-tag-arrow" aria-hidden="true">›</span></a>
            <a href="{{ $memberOwnerLocked ? '#' : route('pelanggan.trade-in.index') }}" @if($memberOwnerLocked) data-owner-locked aria-disabled="true" tabindex="-1" title="Terkunci: tersedia setelah Anda memiliki mobil dari YZA" @endif @if(request()->routeIs('pelanggan.trade-in.index')) aria-current="page" @endif><span>Trade-In</span><span class="member-mobile-tag-arrow" aria-hidden="true">›</span></a>
        </div></section>
        <section class="member-mobile-group"><h3><span class="member-menu-label">Beli Mobil<x-notification-dot category="buy" :count="$memberCategoryUnread['buy'] ?? 0" /></span></h3><div class="member-mobile-tags">
            <a href="{{ route('pelanggan.katalog-mobil.index') }}" @if(request()->routeIs('pelanggan.katalog-mobil.index')) aria-current="page" @endif><span>Katalog</span><span class="member-mobile-tag-arrow" aria-hidden="true">›</span></a>
            <a href="{{ route('pelanggan.preorder.index') }}" @if(request()->routeIs('pelanggan.preorder.index')) aria-current="page" @endif><span>Pre-Order</span><span class="member-mobile-tag-arrow" aria-hidden="true">›</span></a>
        </div></section>
        <section class="member-mobile-group"><h3><span class="member-menu-label">Akun &amp; Benefit<x-notification-dot category="benefits" :count="$memberCategoryUnread['benefits'] ?? 0" /></span></h3><div class="member-mobile-tags">
            <a href="{{ route('pelanggan.promo.index') }}" @if(request()->routeIs('pelanggan.promo.index')) aria-current="page" @endif><span>Benefit Member</span><span class="member-mobile-tag-arrow" aria-hidden="true">›</span></a>
            <a href="#yza-points" data-open-yza-points-modal><span>YZA Points</span><span class="member-mobile-tag-arrow" aria-hidden="true">›</span></a>
            <a href="{{ route('pelanggan.profile') }}" @if(request()->routeIs('pelanggan.profile')) aria-current="page" @endif><span>Profil</span><span class="member-mobile-tag-arrow" aria-hidden="true">›</span></a>
        </div></section>
    </nav>
</aside>
<nav class="member-bottom-nav" aria-label="Navigasi cepat mobile">
    <a class="member-bottom-nav-item {{ request()->routeIs('pelanggan.member','pelanggan.dashboard') ? 'is-active' : '' }}" href="{{ route('pelanggan.member') }}">
        <span class="member-bottom-nav-icon" aria-hidden="true"><x-member-nav-icon name="crown" /></span>
        <span>Beranda</span>
    </a>
    <a class="member-bottom-nav-item {{ request()->routeIs('pelanggan.kendaraan.*') ? 'is-active' : '' }}" href="{{ $memberOwnerLocked ? '#' : route('pelanggan.kendaraan.index') }}" @if($memberOwnerLocked) data-owner-locked aria-disabled="true" tabindex="-1" title="Terkunci: tersedia setelah Anda memiliki mobil dari YZA" @endif>
        <span class="member-bottom-nav-icon" aria-hidden="true"><x-member-nav-icon name="garage" /></span>
        <span>Garasi</span>
    </a>
    <a class="member-bottom-nav-item {{ request()->routeIs('pelanggan.booking-servis.*') ? 'is-active' : '' }}" href="{{ $memberOwnerLocked ? '#' : route('pelanggan.booking-servis.index') }}" @if($memberOwnerLocked) data-owner-locked aria-disabled="true" tabindex="-1" title="Terkunci: tersedia setelah Anda memiliki mobil dari YZA" @endif>
        <span class="member-bottom-nav-icon" aria-hidden="true"><x-member-nav-icon name="tools" /></span>
        <span>Servis</span>
    </a>
    <a class="member-bottom-nav-item {{ request()->routeIs('pelanggan.katalog-mobil.*','pelanggan.preorder.*') ? 'is-active' : '' }}" href="{{ route('pelanggan.katalog-mobil.index') }}">
        <span class="member-bottom-nav-icon" aria-hidden="true"><x-member-nav-icon name="car" /></span>
        <span>Beli Mobil</span>
    </a>
    <button type="button" class="member-bottom-nav-item" onclick="document.querySelector('[data-member-mobile-toggle]')?.click()" aria-label="Buka semua menu">
        <span class="member-bottom-nav-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
        </span>
        <span>Menu</span>
    </button>
</nav>
