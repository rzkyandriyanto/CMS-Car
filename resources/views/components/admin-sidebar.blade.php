@php
    $adminSections = [
        ['title' => 'Overview', 'items' => [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
        ]],
        ['title' => 'Super Admin & Akses', 'items' => [
            ['label' => 'Role & Hak Akses', 'route' => 'admin.roles.index'],
            ['label' => 'Data Member', 'route' => 'admin.pelanggan.index'],
            ['label' => 'Data Karyawan', 'route' => 'admin.petugas.index'],
            ['label' => 'Profil Admin', 'route' => 'account.password'],
        ]],
        ['title' => 'Service Advisor & Mekanik', 'items' => [
            ['label' => 'Booking Servis', 'route' => 'admin.booking-servis.index'],
            ['label' => 'Work Order Servis', 'route' => 'admin.servis.index'],
            ['label' => 'Jadwal Bengkel', 'route' => 'admin.servis.schedule'],
            ['label' => 'Personel Servis', 'route' => 'admin.servis.personnel'],
        ]],
        ['title' => 'Quality Control', 'items' => [
            ['label' => 'Riwayat QC', 'route' => 'admin.servis.qc-history'],
            ['label' => 'PDI Kendaraan', 'route' => 'admin.pdi.index'],
        ]],
        ['title' => 'Finance', 'items' => [
            ['label' => 'Pembayaran', 'route' => 'admin.payments.index', 'badge' => 'payments'],
            ['label' => 'Refund & Reversal', 'route' => 'admin.financial-adjustments.index'],
            ['label' => 'Laporan Keuangan', 'route' => 'admin.finance.report'],
        ]],
        ['title' => 'Cuci Mobil', 'items' => [
            ['label' => 'Booking Cuci Mobil', 'route' => 'admin.booking-cuci.index'],
        ]],
        ['title' => 'Gudang & Inventori', 'items' => [
            ['label' => 'Sparepart & Inventori', 'route' => 'admin.sparepart.index', 'badge' => 'stock'],
            ['label' => 'Stok Masuk', 'route' => 'admin.stok-masuk.index'],
            ['label' => 'Stok Mobil', 'route' => 'admin.mobil.index'],
        ]],
    ];
    $pendingLabels = ['orders' => 'pesanan menunggu verifikasi', 'preorders' => 'pre-order menunggu stok', 'payments' => 'pembayaran perlu ditinjau', 'bookings' => 'booking menunggu', 'testDrives' => 'test drive menunggu', 'tradeIns' => 'trade-in perlu diproses', 'stock' => 'sparepart mencapai stok minimum'];
@endphp
<aside class="admin-sidebar" data-admin-sidebar aria-label="Navigasi admin">
    <div class="admin-sidebar-brand">
        <a class="admin-sidebar-brand-link" href="{{ $adminUser->roleKey() === 'sales' ? route('sales.dashboard') : ($adminUser->roleKey() === 'owner' ? route('owner.dashboard') : route('admin.dashboard')) }}" aria-label="Ke Dashboard YZA">
            <img src="{{ asset('images/branding/logo-yza.png') }}" alt="YZA Automotive">
            <span><strong><span>YZA</span> <em>AUTOMOTIVE</em></strong><small>Admin Management</small></span>
        </a>
        <button class="admin-sidebar-collapse" type="button" data-admin-sidebar-collapse aria-expanded="true" aria-label="Ciutkan sidebar" title="Ciutkan sidebar"><span aria-hidden="true">‹</span></button>
    </div>
    <div class="admin-sidebar-nav-scroll" data-admin-sidebar-scroll tabindex="0" role="region" aria-label="Daftar menu admin">
    @if($adminUser->roleKey() === 'super_admin')
    <nav class="admin-nav">
        @foreach($adminSections as $section)
            <div class="admin-nav-section">
                <div class="admin-nav-label">{{ $section['title'] }}</div>
                @foreach($section['items'] as $item)
                    @if(!empty($item['disabled']) || !Route::has($item['route']))
                        <span class="admin-nav-item is-disabled" title="{{ $item['label'] }}">
                            <i>
                                <x-order-icon :name="\App\Support\AdminNavigation::icon($item['route'])" />
                            </i>
                            <span class="admin-nav-label-text" title="{{ $item['label'] }}">{{ $item['label'] }}<x-notification-dot :menu="$item['route']" :count="$notificationState['menu_unread'][$item['route']] ?? 0" /></span>
                            <span class="admin-nav-badge-slot"></span>
                            <span class="admin-nav-chevron" aria-hidden="true">›</span>
                        </span>
                    @else
                        @php
                            $isAdminNavActive = \App\Support\AdminNavigation::active($item['route']);
                            $badgeKey = $item['badge'] ?? null;
                        @endphp
                        <a class="admin-nav-item {{ $isAdminNavActive ? 'is-active' : '' }}" href="{{ route($item['route']) }}" title="{{ $item['label'] }}" aria-label="{{ $item['label'] }}" @if($isAdminNavActive) aria-current="page" @endif>
                            <i>
                                <x-order-icon :name="\App\Support\AdminNavigation::icon($item['route'])" />
                            </i>
                            <span class="admin-nav-label-text" title="{{ $item['label'] }}">{{ $item['label'] }}<x-notification-dot :menu="$item['route']" :count="$notificationState['menu_unread'][$item['route']] ?? 0" /></span>
                            <span class="admin-nav-badge-slot">@if(isset($item['badge']))<b class="admin-nav-badge" @if($badgeKey === 'notifications') data-notification-global-count @else data-notification-pending="{{ $badgeKey }}" data-count-label="{{ $pendingLabels[$badgeKey] ?? 'pekerjaan tertunda' }}" @endif @if(($adminBadges[$badgeKey] ?? 0) < 1) hidden @endif aria-label="{{ $adminBadges[$badgeKey] ?? 0 }} {{ $badgeKey === 'notifications' ? 'notifikasi belum dibaca' : ($pendingLabels[$badgeKey] ?? 'pekerjaan tertunda') }}" title="{{ $badgeKey === 'notifications' ? 'Notifikasi belum dibaca' : ($pendingLabels[$badgeKey] ?? 'Pekerjaan tertunda') }}">{{ ($adminBadges[$badgeKey] ?? 0) > 99 ? '99+' : ($adminBadges[$badgeKey] ?? 0) }}</b>@endif</span>
                            <span class="admin-nav-chevron" aria-hidden="true">›</span>
                        </a>
                    @endif
                @endforeach
            </div>
        @endforeach
    </nav>
    @else
    <nav class="admin-nav" aria-label="Navigasi role">
        <div class="admin-nav-section"><div class="admin-nav-label">Ruang Kerja</div><a class="admin-nav-item {{ request()->routeIs('owner.dashboard', 'staff.dashboard') ? 'is-active' : '' }}" title="Dashboard" href="{{ $adminUser->roleKey() === 'owner' ? route('owner.dashboard') : route('staff.dashboard') }}"><i aria-hidden="true"><x-order-icon name="layers" /></i><span class="admin-nav-label-text">Dashboard</span><span class="admin-nav-badge-slot"></span><span class="admin-nav-chevron" aria-hidden="true">›</span></a>
            @php
                $roleLinks = [
                    'owner' => [['Executive dashboard','owner.dashboard','reports.view'],['Sales Excellence Awards','owner.sales-awards','reports.view'],['Rekap penjualan','owner.sales-report','reports.view'],['Performa Sales','owner.performance-report','reports.view'],['Laporan Servis','owner.service-report','reports.view'],['Pendapatan & Pembayaran','owner.payment-report','reports.view'],['Inventori Kendaraan','owner.inventory-report','reports.view']],
                    'sales' => [['Dashboard Sales','sales.dashboard','sales.view'],['Sales Excellence Awards','sales.awards','sales.view'],['Prospek pelanggan','sales.leads.index','sales.view'],['Jadwal test drive','sales.test-drives.index','sales.view'],['Pesanan mobil','sales.orders.index','sales.view'],['Penjualan saya','sales.sales.index','sales.view'],['Target penjualan','sales.targets.index','sales.view']],
                    'service_advisor' => [['Booking servis','admin.booking-servis.index','service.view'],['Work Order','admin.servis.index','service.manage']],
                    'mechanic' => [['Pekerjaan servis','admin.servis.index','service.assigned'],['Riwayat','admin.sparepart.index','inventory.view']],
                    'warehouse_staff' => [['Sparepart','admin.sparepart.index','inventory.view'],['Stok masuk','admin.stok-masuk.index','inventory.manage'],['Stok Mobil','admin.mobil.index','inventory.view']],
                    'finance' => [['Pembayaran','admin.payments.index','finance.view'],['Refund & Reversal','admin.financial-adjustments.index','finance.view'],['Laporan keuangan','admin.finance.report','reports.view']],
                    'operational_staff' => [['Booking mobil','admin.booking.index','sales.view'],['Test drive','admin.test-drive.index','sales.view']],
                    'quality_control' => [['QC Work Order','admin.servis.index','qc.manage'],['Riwayat QC','admin.servis.qc-history','qc.manage'],['PDI Kendaraan','admin.pdi.index','qc.manage']],
                    'car_wash_staff' => [['Booking Cuci Mobil','admin.booking-cuci.index','service.view']],
                ][$adminUser->roleKey()] ?? [];
            @endphp
            @php($roleLinks[] = ['Profil Admin', 'account.password', null])
            @foreach($roleLinks as [$label, $routeName, $permission])
                @php($rolePendingKey = match ($routeName) { 'sales.orders.index' => 'orders', 'admin.payments.index' => 'payments', 'admin.booking.index' => 'bookings', 'admin.test-drive.index' => 'testDrives', 'admin.sparepart.index' => 'stock', default => null })
                @if(Route::has($routeName) && ($permission === null || $adminUser->hasPermission($permission)))<a class="admin-nav-item {{ \App\Support\AdminNavigation::active($routeName) ? 'is-active' : '' }}" title="{{ $label }}" href="{{ route($routeName) }}"><i aria-hidden="true"><x-order-icon :name="\App\Support\AdminNavigation::icon($routeName)" /></i><span class="admin-nav-label-text">{{ $label }}<x-notification-dot :menu="$routeName" :count="$notificationState['menu_unread'][$routeName] ?? 0" /></span><span class="admin-nav-badge-slot">@if($rolePendingKey)<b class="admin-nav-badge" data-notification-pending="{{ $rolePendingKey }}" data-count-label="{{ $pendingLabels[$rolePendingKey] }}" @if(($adminBadges[$rolePendingKey] ?? 0) < 1) hidden @endif title="{{ $pendingLabels[$rolePendingKey] }}" aria-label="{{ $adminBadges[$rolePendingKey] ?? 0 }} {{ $pendingLabels[$rolePendingKey] }}">{{ ($adminBadges[$rolePendingKey] ?? 0) > 99 ? '99+' : ($adminBadges[$rolePendingKey] ?? 0) }}</b>@endif</span><span class="admin-nav-chevron" aria-hidden="true">›</span></a>@endif
            @endforeach
        </div>
    </nav>
    @endif
    </div>
</aside>
