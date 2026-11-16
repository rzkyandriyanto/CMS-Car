@php
    $isPerformance = ($variant ?? 'default') === 'performance';
    $catalogReturnUrl = request()->getRequestUri() ?: route('pelanggan.katalog-mobil.index');
    $detailQuery = ['source' => 'member-catalog', 'return_to' => $catalogReturnUrl];
    $detailUrl = $mobil->id_mobil
        ? route('pelanggan.katalog-mobil.show', array_merge(['mobil' => $mobil], $detailQuery))
        : ($mobil->showcase_slug
            ? route('pelanggan.katalog-mobil.showcase', ['slug' => $mobil->showcase_slug])
            : route('mobil.show', array_merge(['mobil' => $mobil], $detailQuery)));

    $galleryPhoto = collect(app(\App\Services\VehicleGalleryService::class)->for($mobil))->flatMap(fn ($c) => $c['images'] ?? [])->first()['src'] ?? null;
    $photo = $mobil->image ?? $galleryPhoto ?? optional($mobil->primaryPhoto())->url();
    $identity = strtolower(trim($mobil->merk.' '.$mobil->model.' '.$mobil->tipe));
    if (str_contains($identity, 'toyota gr supra')) {
        $photo = asset('assets/images/catalog/toyota-gr-supra/front.png');
    } elseif (str_contains($identity, 'supra mk4') || str_contains($identity, 'supra 3.0l a/t')) {
        $photo = asset('assets/images/catalog/toyota-supra-mk4/front.png');
    } elseif (str_contains($identity, 'fortuner')) {
        $photo = asset('assets/images/vehicles/fortuner/blue/front.png');
    } elseif (str_contains($identity, 'xl7')) {
        $photo = asset('assets/images/vehicles/xl7/maroon/front.png');
    } elseif (str_contains($identity, 'xpander')) {
        $photo = asset('assets/images/vehicles/xpander/gray/front.png');
    } elseif (str_contains($identity, 'hr-v') || str_contains($identity, 'hrv')) {
        $photo = asset('assets/images/vehicles/hrv/white/front.png');
    } elseif (str_contains($identity, 'brio')) {
        $photo = asset('assets/images/vehicles/brio/orange/front.png');
    } elseif (str_contains($identity, 'innova') && str_contains($identity, 'zenix')) {
        $photo = asset('assets/images/vehicles/innova-zenix/silver/front.png');
    } elseif (str_contains($identity, 'avanza')) {
        $photo = asset('assets/images/vehicles/avanza/white/front.png');
    } elseif (str_contains($identity, 'mobilio')) {
        $photo = asset('assets/images/vehicles/mobilio/blue/front.png');
    } elseif (str_contains($identity, 'xenia')) {
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

<article class="mc-card {{ $isPerformance ? 'is-performance' : '' }}">
    <div class="mc-card-media">
        <a class="mc-card-image-link" href="{{ $detailUrl }}" tabindex="-1" aria-hidden="true">
            <img src="{{ $photo }}" alt="{{ $title }}" loading="lazy">
        </a>

        <div class="mc-card-badges">
            @if($mobil->kondisi === 'baru')
                <span class="mc-badge mc-badge-new">Baru</span>
            @elseif($mobil->kondisi === 'bekas')
                <span class="mc-badge mc-badge-used">Bekas</span>
            @endif
            @if($isPerformance)
                <span class="mc-badge mc-badge-sport">Sport</span>
            @endif
            <span class="sr-only">{{ $availability['label'] ?? 'TERSEDIA' }}</span>
        </div>

        <button
            class="mc-fav-btn"
            type="button"
            data-favorite-id="{{ $mobil->id_mobil }}"
            data-favorite-name="{{ $title }}"
            aria-label="Tambahkan {{ $title }} ke favorit"
            aria-pressed="false"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572" />
            </svg>
        </button>
    </div>

    <div class="mc-card-body">
        <div class="mc-card-top">
            <span class="mc-brand-mark" title="{{ $brand }}">
                <img src="{{ $brandLogo }}" alt="{{ $brand }}" loading="lazy">
            </span>
            <span class="mc-spec-pill" aria-label="{{ $badgeParts['label'].' '.$badgeParts['value'] }}">
                <span class="mc-spec-pill-label">{{ $badgeParts['label'] }}</span>
                <span class="mc-spec-pill-dot" aria-hidden="true"></span>
                <span class="mc-spec-pill-val">{{ $badgeParts['value'] }}</span>
            </span>
        </div>

        <a href="{{ $detailUrl }}" class="mc-card-title-link">
            <h3 class="mc-card-title">{{ $displayTitle }}</h3>
        </a>

        <p class="mc-card-meta">
            <span>{{ $mobil->tahun ?: '-' }}</span>
            <i aria-hidden="true">·</i>
            <span>{{ $variantLabel ?: 'Collection' }}</span>
            <i aria-hidden="true">·</i>
            <span>{{ ucfirst($mobil->transmisi ?: 'Automatic') }}</span>
        </p>

        <div class="mc-card-footer">
            <div class="mc-price-block">
                <span class="mc-price-label">Harga Unit</span>
                <strong class="mc-price">{{ $price > 0 ? 'Rp '.number_format($price, 0, ',', '.') : 'Hubungi Kami' }}</strong>
            </div>
            <a class="mc-detail-btn" href="{{ $detailUrl }}" aria-label="Lihat detail {{ $title }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14"/>
                    <path d="m13 6 6 6-6 6"/>
                </svg>
            </a>
        </div>
    </div>
</article>
