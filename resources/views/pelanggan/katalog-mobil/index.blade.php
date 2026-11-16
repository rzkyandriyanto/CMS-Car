@extends('layouts.app')
@section('title', 'Katalog Mobil Member · YZA Automotive')
@section('body-class', 'member-catalog-page-shell')

@section('content')
@php
    $hasActiveFilter = request()->hasAny(['q', 'kondisi', 'merk', 'sort', 'sport'])
        && (filled(request('q')) || filled(request('kondisi')) || filled(request('merk')) || request('sport') || (request('sort', 'terbaru') !== 'terbaru'));

    $headerBg = asset('assets/images/hero/hero-slide-2.png');
@endphp

<div class="mc-page">
    <header class="mc-header" style="--mc-header-bg: url('{{ $headerBg }}');">
        <div class="mc-header-inner">
            <div class="mc-header-copy">
                <div class="mc-eyebrow">
                    <span class="mc-eyebrow-dot" aria-hidden="true"></span>
                    <span>YZA Automotive · Member Showroom</span>
                </div>
                <h1 class="mc-title">Katalog Kendaraan</h1>
                <p class="mc-subtitle">Koleksi unit baru, bekas tersertifikasi, dan lini performa pilihan khusus member YZA.</p>
            </div>
            <div class="mc-header-meta">
                <div class="mc-stat-pill">
                    <span class="mc-stat-value">{{ $allTotalCars }}</span>
                    <span class="mc-stat-label">Unit Tersedia</span>
                </div>
            </div>
        </div>
    </header>

    <section class="mc-controls" aria-label="Filter dan pencarian katalog mobil">
        <div class="mc-toolbar-top">
            <nav class="mc-tabs" aria-label="Kategori kendaraan">
                <a class="mc-tab {{ !request('kondisi') && !request('sport') ? 'is-active' : '' }}" href="{{ route('pelanggan.katalog-mobil.index') }}">
                    <span>Semua</span>
                    <small>{{ $allTotalCars }}</small>
                </a>
                <a class="mc-tab {{ request('kondisi') === 'baru' ? 'is-active' : '' }}" href="{{ route('pelanggan.katalog-mobil.index', ['kondisi' => 'baru']) }}">
                    <span>Baru</span>
                    <small>{{ $newCars }}</small>
                </a>
                <a class="mc-tab {{ request('kondisi') === 'bekas' ? 'is-active' : '' }}" href="{{ route('pelanggan.katalog-mobil.index', ['kondisi' => 'bekas']) }}">
                    <span>Bekas</span>
                    <small>{{ $usedCars }}</small>
                </a>
                <a class="mc-tab {{ request('sport') ? 'is-active' : '' }}" href="{{ route('pelanggan.katalog-mobil.index', ['sport' => 1]) }}">
                    <span>Sport</span>
                    <small>{{ $sportCars }}</small>
                </a>
            </nav>

            <div class="mc-results-info">
                <span class="mc-count">Menampilkan <strong>{{ $visibleCars }}</strong> dari <strong>{{ $totalCars }}</strong> unit</span>
                @if($hasActiveFilter)
                    <a class="mc-reset-link" href="{{ route('pelanggan.katalog-mobil.index') }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                        </svg>
                        <span>Reset</span>
                    </a>
                @endif
            </div>
        </div>

        <form method="get" action="{{ route('pelanggan.katalog-mobil.index') }}" class="mc-filter-bar" data-mc-filter-form>
            @if(request('sport'))
                <input type="hidden" name="sport" value="1">
            @endif

            <div class="mc-field mc-field-search">
                <svg class="mc-field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari merek, model, atau tipe..."
                    aria-label="Cari mobil"
                    autocomplete="off"
                >
            </div>

            <div class="mc-field mc-field-select">
                <select name="kondisi" aria-label="Filter kondisi" data-mc-auto-submit>
                    <option value="">Semua Kondisi</option>
                    <option value="baru" @selected(request('kondisi') === 'baru')>Baru</option>
                    <option value="bekas" @selected(request('kondisi') === 'bekas')>Bekas</option>
                </select>
                <svg class="mc-select-chevron" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </div>

            <div class="mc-field mc-field-select">
                <select name="merk" aria-label="Filter merek" data-mc-auto-submit>
                    <option value="">Semua Merek</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand }}" @selected(request('merk') === $brand)>{{ $brand }}</option>
                    @endforeach
                </select>
                <svg class="mc-select-chevron" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </div>

            <div class="mc-field mc-field-select">
                <select name="sort" aria-label="Urutkan" data-mc-auto-submit>
                    <option value="terbaru" @selected(request('sort', 'terbaru') === 'terbaru')>Urutkan: Terbaru</option>
                    <option value="harga_termurah" @selected(request('sort') === 'harga_termurah')>Harga Terendah</option>
                    <option value="harga_tertinggi" @selected(request('sort') === 'harga_tertinggi')>Harga Tertinggi</option>
                    <option value="tahun_terbaru" @selected(request('sort') === 'tahun_terbaru')>Tahun Terbaru</option>
                </select>
                <svg class="mc-select-chevron" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </div>

            <button type="submit" class="mc-submit-btn">Terapkan</button>
        </form>
    </section>

    <section class="mc-results" aria-label="Daftar kendaraan">
        @if($cars->isEmpty())
            <div class="mc-empty">
                <div class="mc-empty-icon" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                </div>
                <h2>Kendaraan tidak ditemukan</h2>
                <p>Tidak ada unit yang sesuai dengan kata kunci atau kombinasi filter yang Anda pilih.</p>
                <a class="mc-empty-btn" href="{{ route('pelanggan.katalog-mobil.index') }}">Tampilkan Semua Unit</a>
            </div>
        @else
            <div class="mc-grid">
                @foreach($cars as $mobil)
                    @include('pelanggan.katalog-mobil._card', [
                        'mobil' => $mobil,
                        'variant' => $mobil->showcase_slug ? 'performance' : 'default',
                    ])
                @endforeach
            </div>

            @if($lastPage > 1)
                <nav class="mc-pagination" aria-label="Navigasi halaman katalog">
                    @for($page = 1; $page <= $lastPage; $page++)
                        <a class="mc-page-link {{ $page === $currentPage ? 'is-active' : '' }}" href="{{ request()->fullUrlWithQuery(['page' => $page]) }}" @if($page === $currentPage) aria-current="page" @endif>
                            {{ $page }}
                        </a>
                    @endfor
                </nav>
            @endif
        @endif
    </section>
</div>
@endsection

@push('styles')
<style>
/* =========================================================
   MEMBER CATALOG — DARK MINIMALIST SHOWROOM
   ========================================================= */
body.member-catalog-page-shell .member-main-content {
    background: #090a0d !important;
    padding-bottom: 72px !important;
}

.mc-page {
    --mc-bg: #090a0d;
    --mc-surface: #111318;
    --mc-surface-soft: #181b22;
    --mc-surface-hover: #1e222b;
    --mc-ink: #f4f5f7;
    --mc-ink-muted: #98a0ad;
    --mc-ink-subtle: #666e7a;
    --mc-line: rgba(255, 255, 255, 0.08);
    --mc-line-strong: rgba(255, 255, 255, 0.16);
    --mc-accent: #ef233c;
    --mc-accent-hover: #d81b32;

    max-width: 1440px;
    margin: 0 auto;
    padding: clamp(22px, 3vw, 36px) clamp(18px, 3.5vw, 44px) 0;
    color: var(--mc-ink);
    font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* 1. DARK HEADER WITH RANDOM BACKGROUND IMAGE */
.mc-header {
    position: relative;
    padding: clamp(28px, 4vw, 46px) clamp(22px, 3.5vw, 40px);
    margin-bottom: 24px;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.09);
    background-image:
        linear-gradient(105deg, rgba(9, 10, 13, 0.92) 0%, rgba(9, 10, 13, 0.72) 56%, rgba(9, 10, 13, 0.42) 100%),
        var(--mc-header-bg);
    background-size: cover;
    background-position: center;
    overflow: hidden;
}

.mc-header::after {
    content: "";
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 88% 18%, rgba(239, 35, 60, 0.14), transparent 42%);
    pointer-events: none;
}

.mc-header-inner {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
}

.mc-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.72);
    margin-bottom: 10px;
}

.mc-eyebrow-dot {
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: var(--mc-accent);
    box-shadow: 0 0 10px rgba(239, 35, 60, 0.7);
}

.mc-title {
    margin: 0 0 8px;
    font-size: clamp(1.8rem, 2.9vw, 2.55rem);
    font-weight: 800;
    letter-spacing: -0.035em;
    line-height: 1.08;
    color: #ffffff;
}

.mc-subtitle {
    margin: 0;
    max-width: 580px;
    font-size: 14.5px;
    line-height: 1.55;
    color: rgba(255, 255, 255, 0.74);
}

.mc-header-meta {
    display: flex;
    align-items: center;
    gap: 12px;
}

.mc-stat-pill {
    display: inline-flex;
    align-items: baseline;
    gap: 8px;
    padding: 10px 18px;
    background: rgba(17, 19, 24, 0.68);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 999px;
}

.mc-stat-value {
    font-size: 17px;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #ffffff;
}

.mc-stat-label {
    font-size: 12.5px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.7);
}

/* 2. CONTROLS & FILTER BAR */
.mc-controls {
    margin-bottom: 28px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.mc-toolbar-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.mc-tabs {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px;
    background: var(--mc-surface);
    border: 1px solid var(--mc-line);
    border-radius: 12px;
    flex-wrap: wrap;
}

.mc-tab {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--mc-ink-muted);
    text-decoration: none;
    transition: background 0.18s ease, color 0.18s ease, border-color 0.18s ease;
    border: 1px solid transparent;
}

.mc-tab small {
    font-size: 11px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.06);
    color: var(--mc-ink-muted);
}

.mc-tab:hover {
    color: #ffffff;
}

.mc-tab.is-active {
    background: var(--mc-surface-hover);
    border-color: rgba(255, 255, 255, 0.12);
    color: #ffffff;
}

.mc-tab.is-active small {
    background: var(--mc-accent);
    color: #ffffff;
}

.mc-results-info {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    margin-left: auto;
}

.mc-count {
    font-size: 13px;
    color: var(--mc-ink-muted);
}

.mc-count strong {
    color: #ffffff;
    font-weight: 700;
}

.mc-reset-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: 999px;
    border: 1px solid rgba(239, 35, 60, 0.35);
    background: rgba(239, 35, 60, 0.1);
    color: #ff5c6f;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.18s ease, color 0.18s ease, border-color 0.18s ease;
}

.mc-reset-link:hover {
    background: var(--mc-accent);
    border-color: var(--mc-accent);
    color: #ffffff;
}

.mc-filter-bar {
    display: grid;
    grid-template-columns: minmax(240px, 2.2fr) repeat(3, minmax(145px, 1fr)) auto;
    align-items: center;
    gap: 0;
    padding: 6px;
    background: var(--mc-surface);
    border: 1px solid var(--mc-line);
    border-radius: 14px;
}

.mc-field {
    position: relative;
    display: flex;
    align-items: center;
    min-width: 0;
}

.mc-field + .mc-field::before {
    content: "";
    position: absolute;
    left: 0;
    top: 22%;
    bottom: 22%;
    width: 1px;
    background: var(--mc-line);
}

.mc-field-icon {
    position: absolute;
    left: 14px;
    color: var(--mc-ink-subtle);
    pointer-events: none;
}

.mc-field-search input {
    width: 100%;
    height: 44px;
    padding: 0 14px 0 42px;
    border: 0;
    background: transparent;
    color: var(--mc-ink);
    font: inherit;
    font-size: 13.5px;
    font-weight: 500;
    outline: none;
}

.mc-field-search input::placeholder {
    color: var(--mc-ink-subtle);
}

.mc-field-select select {
    width: 100%;
    height: 44px;
    padding: 0 36px 0 14px;
    border: 0;
    background: transparent;
    color: var(--mc-ink);
    color-scheme: dark;
    font: inherit;
    font-size: 13.5px;
    font-weight: 600;
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    outline: none;
    text-overflow: ellipsis;
}

.mc-field-select select option {
    background: #14171e;
    color: #f4f5f7;
}

.mc-select-chevron {
    position: absolute;
    right: 12px;
    color: var(--mc-ink-muted);
    pointer-events: none;
}

.mc-submit-btn {
    height: 44px;
    padding: 0 22px;
    margin-left: 6px;
    border: 0;
    border-radius: 10px;
    background: #ffffff;
    color: #0b0d10;
    font: inherit;
    font-size: 13.5px;
    font-weight: 700;
    letter-spacing: -0.01em;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.18s ease, color 0.18s ease, transform 0.14s ease;
}

.mc-submit-btn:hover {
    background: var(--mc-accent);
    color: #ffffff;
}

.mc-submit-btn:active {
    transform: scale(0.98);
}

/* 3. VEHICLE GRID */
.mc-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 22px;
    align-items: stretch;
}

/* 4. MODEL CARD (DARK MINIMALIST) */
.mc-card {
    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 0;
    background: var(--mc-surface);
    border: 1px solid var(--mc-line);
    border-radius: 16px;
    overflow: hidden;
    transition: transform 0.22s ease, border-color 0.22s ease, box-shadow 0.22s ease;
}

.mc-card:hover {
    transform: translateY(-3px);
    border-color: var(--mc-line-strong);
    box-shadow: 0 18px 40px rgba(0, 0, 0, 0.45);
}

.mc-card-media {
    position: relative;
    aspect-ratio: 16 / 10;
    background: #161920;
    overflow: hidden;
}

.mc-card-image-link {
    display: block;
    width: 100%;
    height: 100%;
}

.mc-card-image-link img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    transition: transform 0.4s cubic-bezier(0.22, 1, 0.36, 1);
}

.mc-card:hover .mc-card-image-link img {
    transform: scale(1.035);
}

.mc-card-badges {
    position: absolute;
    top: 12px;
    left: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    z-index: 2;
    pointer-events: none;
}

.mc-badge {
    display: inline-flex;
    align-items: center;
    height: 24px;
    padding: 0 9px;
    border-radius: 6px;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.mc-badge-new {
    background: var(--mc-accent);
    color: #ffffff;
}

.mc-badge-used {
    background: rgba(17, 19, 24, 0.82);
    color: #f4f5f7;
    border: 1px solid rgba(255, 255, 255, 0.14);
}

.mc-badge-sport {
    background: rgba(255, 255, 255, 0.92);
    color: #0b0d10;
}

.mc-fav-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 3;
    width: 36px;
    height: 36px;
    padding: 0;
    display: grid;
    place-items: center;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.18);
    background: rgba(9, 10, 13, 0.65);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: #ffffff;
    cursor: pointer;
    transition: transform 0.16s ease, background 0.16s ease, color 0.16s ease, border-color 0.16s ease;
}

.mc-fav-btn svg {
    width: 17px;
    height: 17px;
}

.mc-fav-btn:hover {
    transform: scale(1.06);
    background: rgba(9, 10, 13, 0.88);
    border-color: rgba(255, 255, 255, 0.34);
}

.mc-fav-btn.is-favorited {
    background: var(--mc-accent);
    border-color: var(--mc-accent);
    color: #ffffff;
}

.mc-fav-btn.is-favorited svg {
    fill: currentColor;
}

.mc-card-body {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    padding: 16px 18px 18px;
}

.mc-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    min-height: 26px;
    margin-bottom: 12px;
}

.mc-brand-mark {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 26px;
    padding: 3px 8px;
    border-radius: 7px;
    background: rgba(255, 255, 255, 0.94);
    max-width: 76px;
}

.mc-brand-mark img {
    display: block;
    height: 100%;
    max-height: 18px;
    width: auto;
    max-width: 60px;
    object-fit: contain;
    object-position: center;
}

.mc-spec-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    height: 24px;
    padding: 0 9px;
    border-radius: 6px;
    background: var(--mc-surface-soft);
    border: 1px solid var(--mc-line);
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    white-space: nowrap;
}

.mc-spec-pill-label {
    color: var(--mc-ink-muted);
}

.mc-spec-pill-dot {
    width: 3px;
    height: 3px;
    border-radius: 999px;
    background: var(--mc-ink-subtle);
}

.mc-spec-pill-val {
    color: var(--mc-ink);
}

.mc-card-title-link {
    text-decoration: none;
    color: inherit;
}

.mc-card-title {
    margin: 0 0 6px;
    font-size: 17.5px;
    font-weight: 750;
    line-height: 1.3;
    letter-spacing: -0.02em;
    color: var(--mc-ink);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 45px;
    transition: color 0.16s ease;
}

.mc-card-title-link:hover .mc-card-title {
    color: var(--mc-accent);
}

.mc-card-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0 0 16px;
    font-size: 13px;
    font-weight: 500;
    color: var(--mc-ink-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.mc-card-meta i {
    font-style: normal;
    color: var(--mc-ink-subtle);
}

.mc-card-footer {
    margin-top: auto;
    padding-top: 14px;
    border-top: 1px solid var(--mc-line);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.mc-price-block {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.mc-price-label {
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--mc-ink-subtle);
}

.mc-price {
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -0.025em;
    color: #ffffff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.mc-detail-btn {
    flex: 0 0 40px;
    width: 40px;
    height: 40px;
    border-radius: 999px;
    background: var(--mc-surface-soft);
    border: 1px solid var(--mc-line);
    color: #ffffff;
    display: grid;
    place-items: center;
    text-decoration: none;
    transition: background 0.18s ease, border-color 0.18s ease, transform 0.18s ease;
}

.mc-card:hover .mc-detail-btn {
    background: #ffffff;
    border-color: #ffffff;
    color: #0b0d10;
}

.mc-detail-btn:hover {
    background: var(--mc-accent) !important;
    border-color: var(--mc-accent) !important;
    color: #ffffff !important;
    transform: translateX(2px);
}

/* 5. EMPTY STATE & PAGINATION */
.mc-empty {
    padding: 56px 24px;
    text-align: center;
    background: var(--mc-surface);
    border: 1px solid var(--mc-line);
    border-radius: 16px;
}

.mc-empty-icon {
    width: 56px;
    height: 56px;
    margin: 0 auto 16px;
    border-radius: 999px;
    background: var(--mc-surface-soft);
    border: 1px solid var(--mc-line);
    color: var(--mc-ink-muted);
    display: grid;
    place-items: center;
}

.mc-empty h2 {
    margin: 0 0 6px;
    font-size: 18px;
    font-weight: 700;
    color: var(--mc-ink);
}

.mc-empty p {
    margin: 0 auto 20px;
    max-width: 420px;
    font-size: 14px;
    color: var(--mc-ink-muted);
}

.mc-empty-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 40px;
    padding: 0 18px;
    border-radius: 10px;
    background: #ffffff;
    color: #0b0d10;
    font-size: 13.5px;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.18s ease, color 0.18s ease;
}

.mc-empty-btn:hover {
    background: var(--mc-accent);
    color: #ffffff;
}

.mc-pagination {
    margin-top: 36px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.mc-page-link {
    display: grid;
    place-items: center;
    min-width: 38px;
    height: 38px;
    padding: 0 12px;
    border-radius: 10px;
    border: 1px solid var(--mc-line);
    background: var(--mc-surface);
    color: var(--mc-ink-muted);
    font-size: 13.5px;
    font-weight: 700;
    text-decoration: none;
    transition: border-color 0.18s ease, color 0.18s ease, background 0.18s ease;
}

.mc-page-link:hover {
    border-color: var(--mc-line-strong);
    color: #ffffff;
}

.mc-page-link.is-active {
    background: var(--mc-accent);
    border-color: var(--mc-accent);
    color: #ffffff;
}

/* 6. RESPONSIVE BREAKPOINTS */
@media (max-width: 1240px) {
    .mc-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 960px) {
    .mc-filter-bar {
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        padding: 10px;
    }

    .mc-field + .mc-field::before {
        display: none;
    }

    .mc-field-search {
        grid-column: 1 / -1;
        border-bottom: 1px solid var(--mc-line);
        padding-bottom: 4px;
    }

    .mc-field-select {
        border: 1px solid var(--mc-line);
        border-radius: 10px;
        background: var(--mc-bg);
    }

    .mc-submit-btn {
        grid-column: 1 / -1;
        margin-left: 0;
        width: 100%;
    }

    .mc-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
}

@media (max-width: 580px) {
    .mc-header {
        padding: 22px 18px;
        margin-bottom: 18px;
        border-radius: 16px;
    }

    .mc-tabs {
        width: 100%;
        justify-content: space-between;
    }

    .mc-tab {
        flex: 1 1 auto;
        justify-content: center;
        padding: 8px 10px;
        font-size: 12.5px;
    }

    .mc-results-info {
        width: 100%;
        justify-content: space-between;
        margin-left: 0;
    }

    .mc-filter-bar {
        grid-template-columns: 1fr;
    }

    .mc-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('scripts')
<script>
(() => {
    const key = 'yza_favorite_cars';
    const read = () => {
        try {
            const value = JSON.parse(localStorage.getItem(key) || '[]');
            return Array.isArray(value) ? value.map(String) : [];
        } catch (error) {
            return [];
        }
    };
    const write = value => {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (error) {}
    };

    document.querySelectorAll('[data-favorite-id]').forEach(button => {
        const id = String(button.dataset.favoriteId);
        const render = active => {
            button.classList.toggle('is-favorited', active);
            button.setAttribute('aria-pressed', String(active));
        };
        render(read().includes(id));
        button.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            const favorites = read();
            const active = !favorites.includes(id);
            write(active ? [...favorites, id] : favorites.filter(item => item !== id));
            render(active);
        });
    });

    const filterForm = document.querySelector('[data-mc-filter-form]');
    if (filterForm) {
        filterForm.querySelectorAll('[data-mc-auto-submit]').forEach(select => {
            select.addEventListener('change', () => {
                filterForm.submit();
            });
        });
    }
})();
</script>
@endpush
