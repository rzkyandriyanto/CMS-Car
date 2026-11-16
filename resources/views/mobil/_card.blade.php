@php
    $isPerformance = ($variant ?? 'default') === 'performance';
    $isMemberCatalog = request()->routeIs('pelanggan.katalog-mobil.*');
    $catalogReturnUrl = request()->getRequestUri() ?: ($isMemberCatalog ? route('pelanggan.katalog-mobil.index') : route('mobil.index'));
    $detailQuery = ['source' => $isMemberCatalog ? 'member-catalog' : 'catalog', 'return_to' => $catalogReturnUrl];
    $detailUrl = $isMemberCatalog && $mobil->id_mobil
        ? route('pelanggan.katalog-mobil.show', array_merge(['mobil' => $mobil], $detailQuery))
        : ($isMemberCatalog && $mobil->showcase_slug
            ? route('pelanggan.katalog-mobil.showcase', ['slug' => $mobil->showcase_slug])
        : ($mobil->showcase_slug
            ? route('mobil.showcase', array_merge(['slug' => $mobil->showcase_slug], $detailQuery))
            : route('mobil.show', array_merge(['mobil' => $mobil], $detailQuery))));
    $galleryPhoto = collect(app(\App\Services\VehicleGalleryService::class)->for($mobil))->flatMap(fn ($c) => $c['images'] ?? [])->first()['src'] ?? null;
    $photo = $mobil->image ?? $galleryPhoto ?? optional($mobil->primaryPhoto())->url();
    $identity = strtolower(trim($mobil->merk.' '.$mobil->model.' '.$mobil->tipe));
    if (str_contains($identity, 'toyota gr supra')) {
        $photo = asset('assets/images/catalog/toyota-gr-supra/front.png');
    }
    if (str_contains($identity, 'supra mk4') || str_contains($identity, 'supra 3.0l a/t')) {
        $photo = asset('assets/images/catalog/toyota-supra-mk4/front.png');
    }
    if (str_contains($identity, 'fortuner')) {
        $photo = asset('assets/images/vehicles/fortuner/blue/front.png');
    }
    if (str_contains($identity, 'xl7')) {
        $photo = asset('assets/images/vehicles/xl7/maroon/front.png');
    }
    if (str_contains($identity, 'xpander')) {
        $photo = asset('assets/images/vehicles/xpander/gray/front.png');
    }
    if (str_contains($identity, 'hr-v') || str_contains($identity, 'hrv')) {
        $photo = asset('assets/images/vehicles/hrv/white/front.png');
    }
    if (str_contains($identity, 'brio')) {
        $photo = asset('assets/images/vehicles/brio/orange/front.png');
    }
    if (str_contains($identity, 'innova') && str_contains($identity, 'zenix')) {
        $photo = asset('assets/images/vehicles/innova-zenix/silver/front.png');
    }
    if (str_contains($identity, 'avanza')) {
        $photo = asset('assets/images/vehicles/avanza/white/front.png');
    }
    if (str_contains($identity, 'mobilio')) {
        $photo = asset('assets/images/vehicles/mobilio/blue/front.png');
    }
    if (str_contains($identity, 'xenia')) {
        $photo = asset('assets/images/vehicles/xenia/silver/front.png');
    }
    $photo = $photo ?: asset('assets/images/lower-landing/showroom-premium.png');
    $price = (float) $mobil->harga_jual;
    $brand = trim((string) $mobil->merk) ?: 'YZA Automotive';
    $variantLabel = trim((string) ($mobil->tipe ?: ($isPerformance ? 'Performance' : 'Collection')));
    $badgeParts = \App\Support\VehicleBadge::parts($mobil);
    $title = trim($brand.' '.$mobil->model);
    $displayTitle = (strlen($title) < 22 && $variantLabel !== '' && !str_contains(strtolower($title), strtolower($variantLabel)) && !str_contains(strtolower($variantLabel), 'performance') && !str_contains(strtolower($variantLabel), 'collection'))
        ? $title.' · '.$variantLabel
        : $title;
    $brandLogo = \App\Support\BrandLogo::path($brand, $mobil->model);
    $availability = $mobil->getAttribute('availability') ?? app(\App\Services\VehicleAvailabilityService::class)->for($mobil);
@endphp
<article class="catalog-card {{ $isPerformance ? 'catalog-card-performance' : '' }}">
    <a class="catalog-card-image" href="{{ $detailUrl }}" tabindex="-1" aria-hidden="true">
        <img src="{{ $photo }}" alt="{{ $title }}" loading="lazy">
        <span class="catalog-card-badges">
            @if($mobil->kondisi === 'baru')<span class="catalog-condition">BARU</span>@endif
            @if($isPerformance)<span class="catalog-sport-badge">SPORT HIGHLIGHT</span>@endif
            <span class="sr-only">{{ $availability['label'] ?? 'TERSEDIA' }}</span>
        </span>
    </a>
    <button class="catalog-heart" type="button" data-favorite-id="{{ $mobil->id_mobil }}" data-favorite-name="{{ $title }}" aria-label="Tambahkan {{ $title }} ke favorit" aria-pressed="false">♡</button>
    <div class="catalog-card-body">
        <div class="catalog-brand-row">
            <span class="catalog-brand-logo" title="{{ $brand }}">
                <img src="{{ $brandLogo }}" alt="Logo {{ $brand }}" loading="lazy">
            </span>
            <span class="vehicle-tech-badge" aria-label="{{ $badgeParts['label'].' '.$badgeParts['value'] }}"><span class="vehicle-tech-badge__label">{{ strtoupper($badgeParts['label']) }}</span><span class="vehicle-tech-badge__value">{{ strtoupper($badgeParts['value']) }}</span></span>
        </div>
        <a href="{{ $detailUrl }}" class="catalog-card-title-link"><h3>{{ $displayTitle }}</h3></a>
        <p class="catalog-meta">{{ $mobil->tahun ?: '-' }} <i>•</i> {{ $variantLabel ?: 'Collection' }} <i>•</i> {{ ucfirst($mobil->transmisi ?: 'automatic') }}</p>
        <div class="catalog-card-footer">
            <strong class="catalog-price">{{ $price > 0 ? 'Rp '.number_format($price, 0, ',', '.') : 'Hubungi Kami' }}</strong>
            <a class="catalog-detail-button" href="{{ $detailUrl }}" aria-label="Lihat detail {{ $title }}"><b aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></b></a>
        </div>
    </div>
</article>
