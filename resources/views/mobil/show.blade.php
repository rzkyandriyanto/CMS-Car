@php
    // Presentation gallery is resolved by VehicleGalleryService; the view only renders it.
    $identity = strtolower($mobil->merk.' '.$mobil->model);
    $catalogFallbacks = [
        'raize' => asset('assets/images/hero/yza-detail-red-bright-generated.png'),
        'civic' => asset('assets/images/catalog/inventory/honda-civic.png'),
        'fortuner' => asset('assets/images/catalog/inventory/toyota-fortuner.png'),
        'xl7' => asset('assets/images/catalog/inventory/suzuki-xl7.png'),
    ];
    $landingVehicleImage = $showcaseImage ?? asset('assets/images/hero/yza-hero-reference-v3.png');
    foreach ($catalogFallbacks as $keyword => $catalogImage) {
        if (str_contains($identity, $keyword)) {
            $landingVehicleImage = $catalogImage;
            break;
        }
    }
    $galleryData = $galleryData ?? ['default' => ['label' => $mobil->warna ?: 'Pilihan warna tersedia', 'swatch' => '#20a9c7', 'images' => [['type' => 'front', 'label' => 'Front view', 'src' => $landingVehicleImage]]]];
    $galleryData = app(\App\Services\VehicleColorStockService::class)->colors($mobil);
    $defaultColorKey = array_key_exists($defaultColorKey ?? '', $galleryData) ? $defaultColorKey : array_key_first($galleryData);
    foreach ($galleryData as $key => $color) if (mb_strtolower((string) request('warna')) === mb_strtolower($key)) $defaultColorKey = $key;
    $defaultGallery = $galleryData[$defaultColorKey];
    $primaryImage = $defaultGallery['images'][0]['src'] ?? asset('assets/images/vehicle-photo-unavailable.svg');
    $brand = strtoupper((string) ($mobil->merk ?: 'YZA AUTOMOTIVE'));
    $brandLogo = \App\Support\BrandLogo::path($mobil->merk, $mobil->model);
    $uiIcon = static function (string $name): string {
        if ($name === 'whatsapp') {
            return '<img class="detail-inline-icon detail-whatsapp-icon" src="'.asset('assets/images/social/whatsapp.svg').'" alt="" aria-hidden="true">';
        }
        $paths = [
            'calendar' => '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16M8 14h2M12 14h2M8 17h2"/>',
            'trade' => '<path d="M4 8h15M15 4l4 4-4 4M20 16H5M9 12l-4 4 4 4"/>',
            'engine' => '<path d="M4 10h3l2-3h5l2 3h2a2 2 0 0 1 2 2v4h-3v2H8v-2H5v-4a2 2 0 0 1 2-2Z"/><path d="M13 7V4h3M3 13H1M20 10V7"/>',
            'year' => '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16M8 14h2M12 14h2M8 17h2"/>',
            'gear' => '<circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1-1.8 3.1-.2-.1a2 2 0 0 1-2.5.2l-.2-.1a2 2 0 0 1-1.1 1.1H10a2 2 0 0 1-1.1-1.1l-.2.1a2 2 0 0 1-2.5-.2l-.2.1-1.8-3.1.1-.1a2 2 0 0 1 .2-2.5l.1-.2a2 2 0 0 1 0-.6l-.1-.2A2 2 0 0 1 4.3 9l-.1-.1L6 5.8l.2.1a2 2 0 0 1 2.5-.2l.2.1A2 2 0 0 1 10 4.7V4h4v.7a2 2 0 0 1 1.1 1.1l.2-.1a2 2 0 0 1 2.5.2l.2-.1 1.8 3.1-.1.1a2 2 0 0 1-.2 2.5l-.1.2a2 2 0 0 1 0 .6l.1.2a2 2 0 0 1-.1 2.5Z"/>',
            'fuel' => '<path d="M6 20V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v15M6 20h11M9 7h5v4H9zM16 7h2l2 3v7a1 1 0 0 1-2 0v-5h-2"/>',
            'color' => '<path d="M12 4a8 8 0 1 0 0 16h1.2a1.8 1.8 0 0 0 1.2-3.1 1.8 1.8 0 0 1 1.2-3.1H18a2 2 0 0 0 2-2A8 8 0 0 0 12 4Z"/><circle cx="7.5" cy="11" r=".8"/><circle cx="10" cy="7.5" r=".8"/><circle cx="14" cy="7.5" r=".8"/>',
            'condition' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="m8 13 2.5 2.5L16 10M8 8h8"/>',
            'status' => '<path d="m12 3 7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3Z"/><path d="m9 12 2 2 4-4"/>',
        ];
        $path = $paths[$name] ?? '';
        $iconClass = $name === 'whatsapp' ? 'detail-inline-icon detail-whatsapp-icon' : 'detail-inline-icon';
        return '<svg class="'.$iconClass.'" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'.$path.'</svg>';
    };
    $modelName = (string) ($mobil->model ?: 'Premium Vehicle');
    $condition = ucfirst((string) ($mobil->kondisi ?: 'Bekas'));
    $price = 'Rp '.number_format((float) $mobil->harga_jual, 0, ',', '.');
    $whatsappText = 'Halo YZA Automotive, saya ingin konsultasi mengenai '.$mobil->merk.' '.$mobil->model.'.';
    $colorName = $defaultGallery['label'];
    $tradeInEntryUrl = route('trade-in.entry', array_filter([
        'mobil' => $mobil->id_mobil,
        'slug' => request()->route('slug'),
    ], fn ($value) => filled($value)));
    $isActiveTradeInMember = auth()->check()
        && auth()->user()->roleName() === 'pelanggan'
        && auth()->user()->pelanggan?->membership?->isActive();
    $tradeInMembershipUrl = route('trade-in.membership-entry', array_filter([
        'mobil' => $mobil->id_mobil,
        'slug' => request()->route('slug'),
    ], fn ($value) => filled($value)));
    $tradeInCtaUrl = $isActiveTradeInMember ? $tradeInEntryUrl : $tradeInMembershipUrl;
    $availability = $mobil->getAttribute('availability') ?? app(\App\Services\VehicleAvailabilityService::class)->for($mobil);
    $availabilityLabel = $availability['label'] ?? 'TERSEDIA';
    $availabilityClass = $availability['status_class'] ?? 'is-neutral';
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Detail {{ $mobil->merk }} {{ $mobil->model }} dari YZA Automotive.">
    <title>{{ $mobil->merk }} {{ $mobil->model }} · YZA Automotive</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/public-navbar.css') }}?v=public-navbar-20261004">
    <link rel="stylesheet" href="{{ asset('assets/css/mobil-detail.css') }}?v=detail-wide-20261011b">
    @include('components.responsive-foundation')
<style>
.detail-button[hidden],
[data-whatsapp-cta][hidden]{display:none !important}
.detail-swatch.is-out-of-stock{position:relative;opacity:.38 !important;filter:grayscale(.45);cursor:pointer !important}
.detail-swatch.is-out-of-stock::after{content:'Stok Habis';position:absolute;bottom:calc(100% + 9px);left:50%;transform:translateX(-50%) translateY(5px);padding:4px 9px;border-radius:7px;background:#0f172a;border:1px solid rgba(255,255,255,.16);color:#f8fafc;font-size:10.5px;font-weight:700;white-space:nowrap;opacity:0;pointer-events:none;box-shadow:0 8px 20px rgba(0,0,0,.4);transition:opacity .18s ease,transform .18s ease;z-index:20}
.detail-swatch.is-out-of-stock:hover::after,.detail-swatch.is-out-of-stock:focus-visible::after{opacity:1;transform:translateX(-50%) translateY(0)}
[data-public-color-stock]{color:#94a3b8;font-size:13px;margin:0}
[data-public-color-action]{margin:0}
.detail-heading-brand{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin:10px 0 12px}
.detail-heading-brand .detail-brand-logo{width:44px;height:44px;border-radius:11px}
.detail-heading-brand-name{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:800;letter-spacing:.1em;color:#e2e8f0}
.detail-spec-status .detail-status-tag{border:0 !important;background:transparent !important;padding:0 !important;border-radius:0 !important;min-width:0 !important;font-weight:700 !important}
.detail-unified-box{display:grid;gap:20px}
.detail-unified-divider{height:1px;background:rgba(255,255,255,.08);margin:0}
.detail-color-row{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap}
.detail-gallery-cta-box{margin-top:16px;padding:18px;border-radius:18px;background:rgba(17,19,24,.92);border:1px solid rgba(255,255,255,.09);display:grid;gap:12px}
.detail-gallery-cta-box .detail-action-row{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.detail-color-overlay{position:absolute;right:16px;bottom:16px;z-index:5;display:flex;align-items:center;gap:10px;flex-wrap:wrap;max-width:calc(100% - 32px);padding:8px 12px;border-radius:14px;background:rgba(13,15,20,.78);border:1px solid rgba(255,255,255,.16);backdrop-filter:blur(12px);box-shadow:0 12px 30px rgba(0,0,0,.35)}
.detail-color-overlay-label{display:grid;gap:1px;padding-right:6px;border-right:1px solid rgba(255,255,255,.14)}
.detail-color-overlay-label small{font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8}
.detail-color-overlay-label span{font-size:12px;font-weight:700;color:#fff;white-space:nowrap}
.detail-color-overlay .detail-swatches{margin:0;gap:8px;display:flex;align-items:center}
.detail-color-overlay .detail-swatch{width:28px !important;height:28px !important;min-width:28px !important;min-height:28px !important;padding:0 !important;flex:0 0 28px !important;aspect-ratio:1/1 !important;border-radius:999px !important}
.detail-main-image{transition:opacity .35s ease,transform .35s ease}
.detail-main-image.is-sliding-out{opacity:0;transform:translateX(-28px);transition:opacity .2s ease,transform .2s ease}
.detail-main-image.is-sliding-in{opacity:0;transform:translateX(28px);transition:none !important}
.detail-rec-section{margin-top:38px}
.detail-rec-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px;margin-top:18px}
@media(max-width:1024px){.detail-rec-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:640px){.detail-rec-grid{grid-template-columns:1fr}.detail-gallery-cta-box .detail-action-row{grid-template-columns:1fr}.detail-color-overlay{left:12px;right:12px;bottom:12px;justify-content:space-between}}
</style></head>
<body class="yza-detail-page">
    <div class="detail-orb detail-orb-one"></div><div class="detail-orb detail-orb-two"></div>
    <x-public-navbar />
    <main class="detail-page">
        <section class="detail-heading">
            <x-vehicle-back-button :url="$backUrl ?? route('mobil.index')" :label="$backLabel ?? 'Kembali ke Semua Mobil'" />
            <div class="detail-heading-grid">
                <div class="detail-heading-copy">
                    <div class="detail-heading-brand">
                        <span class="detail-brand-logo"><img src="{{ $brandLogo }}" alt="Logo {{ $brand }}"></span>
                        <span class="detail-heading-brand-name">{{ $brand }}</span>
                        @if($availability['is_limited_edition'] ?? false)<span class="detail-limited-badge">LIMITED EDITION</span>@endif
                    </div>
                    <h1>{{ $mobil->merk }} {{ $modelName }}</h1>
                    <p class="detail-subtitle">{{ $mobil->deskripsi ?: 'Unit pilihan dengan pemeriksaan dealer dan pengalaman berkendara yang lebih percaya diri.' }}</p>
                </div>
                <div class="detail-heading-price"><span>Harga terbaik</span><strong>{{ $price }}</strong></div>
            </div>
        </section>
        <section class="detail-showcase" aria-label="Detail kendaraan">
            <div class="detail-info-column">
                <div class="detail-panel detail-summary-panel detail-unified-box" data-detail-vehicle-id="{{ $mobil->id_mobil }}">
                    <div class="detail-spec-block">
                        <div class="detail-section-head">
                            <div><p class="detail-kicker">SPESIFIKASI KENDARAAN</p><h2>Detail kendaraan</h2></div>
                            <span class="detail-code">{{ $mobil->kode_mobil ?: 'YZA-UNIT' }}</span>
                        </div>
                        <div class="detail-spec-grid detail-spec-grid-reference">
                            <div><span class="detail-spec-icon">{!! $uiIcon('engine') !!}</span><span>Tipe</span><strong>{{ $mobil->tipe ?: '-' }}</strong></div>
                            <div><span class="detail-spec-icon">{!! $uiIcon('year') !!}</span><span>Tahun</span><strong>{{ $mobil->tahun ?: '-' }}</strong></div>
                            <div><span class="detail-spec-icon">{!! $uiIcon('gear') !!}</span><span>Transmisi</span><strong>{{ strtoupper($mobil->transmisi ?: '-') }}</strong></div>
                            <div><span class="detail-spec-icon">{!! $uiIcon('fuel') !!}</span><span>Bahan bakar</span><strong>{{ ucfirst($mobil->bahan_bakar ?: '-') }}</strong></div>
                            <div><span class="detail-spec-icon">{!! $uiIcon('color') !!}</span><span>Warna</span><strong id="detail-spec-color">{{ $colorName }}</strong></div>
                            <div><span class="detail-spec-icon">{!! $uiIcon('condition') !!}</span><span>Kondisi</span><strong>{{ $condition }}</strong></div>
                            <div class="detail-spec-status"><span class="detail-spec-icon">{!! $uiIcon('status') !!}</span><span>Status</span><strong class="detail-status-tag {{ $availabilityClass }}">{{ $availabilityLabel }}</strong></div>
                        </div>
                    </div>

                    <div class="detail-unified-divider"></div>

                    <div class="detail-color-row">
                        <p data-public-color-stock aria-live="polite">{{ $defaultGallery['stock'] > 0 ? $defaultGallery['stock'].' unit siap jual' : 'Stok Habis' }}</p>
                        <a class="detail-button detail-button-primary" data-public-color-action href="{{ $defaultGallery['purchase_url'] ?: route('pelanggan.preorder.create', array_filter(['mobil'=>$mobil->id_mobil,'slug'=>$mobil->showcase_slug,'warna'=>$defaultGallery['label']])) }}">{{ $defaultGallery['stock'] > 0 ? 'Lanjutkan Pembelian' : 'Ajukan Pre-Order' }}</a>
                    </div>

                    <div class="detail-unified-divider"></div>

                    <div class="detail-gallery-cta-box" data-whatsapp-cta style="margin-top:0;padding:0;background:transparent;border:0;">
                        <a class="detail-button detail-button-primary" href="{{ yza_whatsapp_url($whatsappText) }}" target="_blank" rel="noopener">{!! $uiIcon('whatsapp') !!}<span class="detail-button-text">Tanya via WhatsApp</span><span>→</span></a>
                    </div>
                </div>
            </div>
            <div class="detail-gallery-column">
                <div class="detail-gallery-frame">
                    <div class="detail-gallery-topline">
                        <span class="detail-gallery-counter" id="detail-gallery-counter">01 / {{ str_pad((string) count($defaultGallery['images']), 2, '0', STR_PAD_LEFT) }}</span>
                        <div class="detail-gallery-badges">
                            <span class="detail-gallery-mark"><b>YZA</b> SHOWROOM VIEW</span>
                        </div>
                    </div><img id="detail-main-image" src="{{ $primaryImage }}" alt="{{ $mobil->merk }} {{ $modelName }}" class="detail-main-image"><div class="detail-image-shade"></div><div class="detail-gallery-caption"><span>{{ $mobil->merk }} {{ $modelName }}</span><small id="detail-gallery-caption">{{ $defaultGallery['images'][0]['label'] ?? 'Foto kendaraan belum tersedia' }} · YZA Automotive</small></div>
                    <div class="detail-color-overlay" aria-label="Pilihan warna kendaraan">
                        <div class="detail-color-overlay-label">
                            <small>Warna</small>
                            <span id="selected-color">{{ $colorName }}</span>
                        </div>
                        <div class="detail-swatches" role="group" aria-label="Pilihan warna kendaraan">
                            @foreach($galleryData as $colorKey => $color)
                                <button class="detail-swatch {{ $color['stock'] === 0 ? 'is-out-of-stock' : '' }} {{ $colorKey === $defaultColorKey ? 'is-active' : '' }}" type="button" data-color-key="{{ $colorKey }}" data-color="{{ $color['label'] }}" style="--swatch:{{ $color['swatch'] }}" title="{{ $color['label'] }}: {{ $color['stock'] > 0 ? $color['stock'].' ready' : 'Stok Habis' }}" aria-label="{{ $color['label'] }}: {{ $color['stock'] > 0 ? $color['stock'].' unit siap jual' : 'Stok Habis' }}" aria-pressed="{{ $colorKey === $defaultColorKey ? 'true' : 'false' }}" aria-disabled="{{ $color['stock'] === 0 ? 'true' : 'false' }}"></button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div id="detail-thumbnails" class="detail-thumbnails" role="list" aria-label="Galeri foto kendaraan">@foreach($defaultGallery['images'] as $index => $image)<button class="detail-thumbnail {{ $index === 0 ? 'is-active' : '' }}" type="button" data-index="{{ $index }}" role="listitem" aria-label="Lihat {{ $image['label'] }}"><img src="{{ $image['src'] }}" alt="{{ $image['label'] }} {{ $mobil->model }}"><span>0{{ $index + 1 }}</span></button>@endforeach</div>
                <div class="detail-gallery-note"><span>✦</span> Pilih warna dan sudut kendaraan untuk melihat detail showroom.</div>
            </div>
        </section>
        @if(($sameBrandCars ?? collect())->isNotEmpty() || ($otherBrandCars ?? collect())->isNotEmpty())
            <section class="detail-recommendations detail-rec-section" aria-labelledby="detail-recommendations-title" data-detail-recommendations>
                @if(($sameBrandCars ?? collect())->isNotEmpty())
                    <div class="detail-recommendations-head">
                        <div><p class="detail-kicker">MOBIL LAINNYA · {{ strtoupper((string) $mobil->merk) }}</p><h2 id="detail-same-brand-title">Koleksi {{ $mobil->merk }} Lainnya</h2><p>Pilihan unit {{ $mobil->merk }} lainnya yang tersedia di YZA Automotive.</p></div>
                    </div>
                    <div class="detail-rec-grid">
                        @foreach($sameBrandCars as $recCar)
                            @php($recTitle = trim(($recCar->merk ?: 'YZA').' '.($recCar->model ?: 'Vehicle')))
                            <article class="detail-recommendation-card">
                                <a class="detail-recommendation-media" href="{{ route('mobil.show', ['mobil' => $recCar]) }}"><img src="{{ $recCar->recommendation_image }}" alt="{{ $recTitle }}" loading="lazy"><span>{{ strtoupper($recCar->kondisi ?: 'KOLEKSI') }}</span></a>
                                <div class="detail-recommendation-body"><small>{{ $recCar->merk ?: 'YZA AUTOMOTIVE' }}</small><h3>{{ $recTitle }}</h3><p>{{ $recCar->tipe ?: 'PERFORMANCE' }} <i>•</i> {{ $recCar->tahun ?: '—' }} <i>•</i> {{ ucfirst($recCar->transmisi ?: 'Automatic') }}</p><div><strong>{{ (float) $recCar->harga_jual > 0 ? 'Rp '.number_format((float) $recCar->harga_jual, 0, ',', '.') : 'Hubungi Kami' }}</strong><a href="{{ route('mobil.show', ['mobil' => $recCar]) }}" aria-label="Lihat detail {{ $recTitle }}"><span aria-hidden="true">→</span></a></div></div>
                            </article>
                        @endforeach
                    </div>
                @endif

                @if(($sameBrandCars ?? collect())->isNotEmpty() && ($otherBrandCars ?? collect())->isNotEmpty())
                    <div class="detail-unified-divider" style="margin: 28px 0;"></div>
                @endif

                @if(($otherBrandCars ?? collect())->isNotEmpty())
                    <div class="detail-recommendations-head">
                        <div><p class="detail-kicker">REKOMENDASI MEREK LAIN</p><h2 id="detail-recommendations-title">Barangkali Kamu Suka</h2><p>Temukan pilihan mobil dari merek lain di koleksi YZA Automotive.</p></div>
                    </div>
                    <div class="detail-rec-grid">
                        @foreach($otherBrandCars as $recommendation)
                            @php($recommendationTitle = trim(($recommendation->merk ?: 'YZA').' '.($recommendation->model ?: 'Vehicle')))
                            <article class="detail-recommendation-card">
                                <a class="detail-recommendation-media" href="{{ route('mobil.show', ['mobil' => $recommendation]) }}"><img src="{{ $recommendation->recommendation_image }}" alt="{{ $recommendationTitle }}" loading="lazy"><span>{{ strtoupper($recommendation->kondisi ?: 'KOLEKSI') }}</span></a>
                                <div class="detail-recommendation-body"><small>{{ $recommendation->merk ?: 'YZA AUTOMOTIVE' }}</small><h3>{{ $recommendationTitle }}</h3><p>{{ $recommendation->tipe ?: 'SPORT COUPE' }} <i>•</i> {{ $recommendation->tahun ?: '—' }} <i>•</i> {{ ucfirst($recommendation->transmisi ?: 'Automatic') }}</p><div><strong>{{ (float) $recommendation->harga_jual > 0 ? 'Rp '.number_format((float) $recommendation->harga_jual, 0, ',', '.') : 'Hubungi Kami' }}</strong><a href="{{ route('mobil.show', ['mobil' => $recommendation]) }}" aria-label="Lihat detail {{ $recommendationTitle }}"><span aria-hidden="true">→</span></a></div></div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif
        <section class="detail-bottom-cta"><div><p class="detail-kicker">READY TO DRIVE?</p><h2>Jadikan kendaraan ini milik Anda.</h2><p>Tim YZA Automotive siap membantu menjawab pertanyaan, menjadwalkan kunjungan, dan memproses pembelian.</p></div><div class="detail-bottom-actions"><a class="detail-button detail-button-primary" href="{{ yza_whatsapp_url($whatsappText) }}" target="_blank" rel="noopener">Hubungi showroom <span>↗</span></a><a class="detail-button detail-button-ghost" href="{{ $tradeInCtaUrl }}" data-trade-in-trigger>Tukar tambah <span>→</span></a></div></section>
    </main>
    <x-trade-in-membership-modal :entry-url="$tradeInEntryUrl" :membership-url="$tradeInMembershipUrl" :is-active-member="$isActiveTradeInMember" />
    <script>
        (() => {
            const panel = document.querySelector('[data-detail-vehicle-id]');
            const heart = document.querySelector('[data-detail-favorite-top]') || panel?.querySelector('[data-detail-favorite]');
            if (panel && heart) {
                const ids = [...new Set([String(panel.dataset.detailVehicleId || ''), window.location.pathname].filter(Boolean))];
                const key = 'yza_favorite_cars';
                const read = () => { try { const value = JSON.parse(localStorage.getItem(key) || '[]'); return Array.isArray(value) ? value.map(String) : []; } catch (error) { return []; } };
                const write = (value) => { try { localStorage.setItem(key, JSON.stringify(value)); } catch (error) {} };
                const render = (active) => { heart.textContent = active ? '♥' : '♡'; heart.classList.toggle('is-favorited', active); heart.setAttribute('aria-pressed', String(active)); heart.setAttribute('aria-label', `${active ? 'Hapus' : 'Tambahkan'} {{ $mobil->merk }} {{ $modelName }} ${active ? 'dari' : 'ke'} favorit`); };
                render(read().some((favorite) => ids.includes(favorite)));
                heart.addEventListener('click', () => {
                    const favorites = read();
                    const active = !favorites.some((favorite) => ids.includes(favorite));
                    const next = active
                        ? [...new Set([...favorites, ...ids])]
                        : favorites.filter((favorite) => !ids.includes(favorite));
                    write(next);
                    render(active);
                });
            }

            const galleryData = @json($galleryData);
            const fallbackImage = @json($landingVehicleImage);
            // Keep the selected angle stable while the user previews another paint.
            const state = { color: @json($defaultColorKey), index: 0, selectedColor: @json($defaultColorKey), selectedAngle: 'front' };
            const mainImage = document.getElementById('detail-main-image');
            const thumbnailsRoot = document.getElementById('detail-thumbnails');
            const colorLabel = document.getElementById('selected-color');
            const specColor = document.getElementById('detail-spec-color');
            const counter = document.getElementById('detail-gallery-counter');
            const caption = document.getElementById('detail-gallery-caption');
            const swatches = [...document.querySelectorAll('.detail-swatch')];
            let isInitialRender = true;
            const transitionImage = (src, alt) => {
                const nextSrc = src || fallbackImage;
                if (isInitialRender) {
                    isInitialRender = false;
                    mainImage.src = nextSrc;
                    mainImage.alt = alt;
                    return;
                }
                mainImage.classList.remove('is-sliding-in');
                mainImage.classList.add('is-sliding-out');
                window.setTimeout(() => {
                    mainImage.classList.remove('is-sliding-out');
                    mainImage.classList.add('is-sliding-in');
                    mainImage.src = nextSrc;
                    mainImage.alt = alt;
                    void mainImage.offsetWidth;
                    window.requestAnimationFrame(() => mainImage.classList.remove('is-sliding-in'));
                }, 180);
            };
            const renderGallery = () => {
                const gallery = galleryData[state.color] || galleryData.default;
                const images = gallery.images || [];
                if (!images.length) return;
                state.index = Math.min(state.index, images.length - 1);
                const image = images[state.index];
                state.selectedColor = state.color;
                state.selectedAngle = image.type;
                transitionImage(image.src, `${image.label} {{ $mobil->model }}`);
                counter.textContent = `${String(state.index + 1).padStart(2, '0')} / ${String(images.length).padStart(2, '0')}`;
                caption.textContent = `${image.label} · YZA Automotive`;
                thumbnailsRoot.innerHTML = images.map((item, index) => `<button class="detail-thumbnail ${index === state.index ? 'is-active' : ''}" type="button" data-index="${index}" role="listitem" aria-label="Lihat ${item.label}"><img src="${item.src}" alt="${item.label} {{ $mobil->model }}"><span>0${index + 1}</span></button>`).join('');
            };
            const changeColor = (swatch) => {
                state.color = swatch.dataset.colorKey;
                // A color change starts at the first image of the selected variant.
                // This prevents the previous variant's active angle from leaking into the new set.
                state.index = 0;
                swatches.forEach((item) => { const active = item === swatch; item.classList.toggle('is-active', active); item.setAttribute('aria-pressed', active ? 'true' : 'false'); });
                colorLabel.textContent = swatch.dataset.color;
                if (specColor) specColor.textContent = swatch.dataset.color;
                const color = galleryData[state.color];
                document.querySelector('[data-public-color-stock]').textContent = color.stock > 0 ? color.stock + ' unit siap jual' : 'Stok Habis';
                document.querySelectorAll('.detail-showcase .detail-status-tag,.detail-showcase .detail-status-badge').forEach(badge => { badge.textContent = color.stock > 0 ? 'TERSEDIA · ' + color.stock + ' UNIT' : 'STOK HABIS'; badge.classList.remove('is-last','is-low','is-available','is-empty'); badge.classList.add(color.stock > 0 ? 'is-available' : 'is-empty'); });
                const action = document.querySelector('[data-public-color-action]');
                const preorder = new URL(@json(route('pelanggan.preorder.create')), location.origin);
                preorder.searchParams.set(@json($mobil->showcase_slug ? 'slug' : 'mobil'), @json((string) ($mobil->showcase_slug ?: $mobil->id_mobil)));
                preorder.searchParams.set('warna', color.label);
                action.href = color.purchase_url || preorder.toString(); action.textContent = color.stock > 0 ? 'Lanjutkan Pembelian' : 'Ajukan Pre-Order';
                renderGallery();
                restartAutoSlide();
            };
            swatches.forEach((swatch) => swatch.addEventListener('click', () => {
                changeColor(swatch);
            }));
            thumbnailsRoot.addEventListener('click', (event) => {
                const thumbnail = event.target.closest('.detail-thumbnail');
                if (!thumbnail) return;
                state.index = Number(thumbnail.dataset.index);
                renderGallery();
                restartAutoSlide();
            });
            let autoSlideTimer = null;
            const stopAutoSlide = () => {
                if (autoSlideTimer) {
                    window.clearInterval(autoSlideTimer);
                    autoSlideTimer = null;
                }
            };
            const startAutoSlide = () => {
                stopAutoSlide();
                const count = (galleryData[state.color]?.images || []).length;
                if (count <= 1) return;
                autoSlideTimer = window.setInterval(() => {
                    const currentCount = (galleryData[state.color]?.images || []).length;
                    if (currentCount <= 1) return;
                    state.index = (state.index + 1) % currentCount;
                    renderGallery();
                }, 3200);
            };
            const restartAutoSlide = () => startAutoSlide();
            mainImage.addEventListener('error', () => { if (mainImage.src !== fallbackImage) { mainImage.src = fallbackImage; } });
            renderGallery();
            startAutoSlide();
            const recommendationRoot = document.querySelector('[data-detail-recommendations]');
            const recommendationViewport = recommendationRoot?.querySelector('[data-recommendation-viewport]');
            const recommendationStep = () => recommendationViewport ? Math.max(280, recommendationViewport.clientWidth * (window.innerWidth <= 600 ? .84 : window.innerWidth <= 1000 ? .52 : .27)) : 0;
            recommendationRoot?.querySelector('[data-recommendation-prev]')?.addEventListener('click', () => recommendationViewport?.scrollBy({ left: -recommendationStep(), behavior: 'smooth' }));
            recommendationRoot?.querySelector('[data-recommendation-next]')?.addEventListener('click', () => recommendationViewport?.scrollBy({ left: recommendationStep(), behavior: 'smooth' }));
        })();
    </script>
</body>
</html>
