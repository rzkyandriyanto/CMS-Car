@extends('layouts.app')

@section('title', 'Promo Member · YZA Automotive')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/member-home.css') }}?v={{ filemtime(public_path('assets/css/member-home.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/member-home-reference.css') }}?v={{ filemtime(public_path('assets/css/member-home-reference.css')) }}">
@endpush

@section('content')
<section class="member-hero yza-promo-hero" style="--promo-hero-bg: url('{{ asset('assets/images/member/reference-service-trade-in.jpg') }}')">
    <div class="yza-promo-hero-inner">
        <span class="yza-eyebrow">YZA Member</span>
        <h1>Promo Khusus Untuk Anda</h1>
        <p>Lihat benefit yang bisa digunakan sesuai layanan dan status membership Anda.</p>
    </div>
</section>

<style>
.yza-promo-body-container.is-owner-locked{position:relative;min-height:520px}
.yza-promo-body-container.is-owner-locked>*:not(.yza-owner-lock-overlay){opacity:.28;pointer-events:none;user-select:none;filter:grayscale(.4)}
.yza-owner-lock-overlay{position:absolute;inset:0;z-index:30;display:flex;align-items:flex-start;justify-content:center;padding:96px 16px 0;pointer-events:auto;background:linear-gradient(180deg,rgba(255,255,255,.05),rgba(255,255,255,.4))}
.yza-owner-lock-card{position:sticky;top:140px;width:min(100%,460px);padding:26px 28px;border-radius:20px;background:#fff;border:1px solid #e2e8f0;box-shadow:0 22px 60px rgba(15,23,42,.22);text-align:center;color:#0f172a}
.yza-owner-lock-icon{width:48px;height:48px;margin:0 auto 14px;border-radius:14px;background:#fff1f2;color:#e11d48;display:flex;align-items:center;justify-content:center}
.yza-owner-lock-card strong{display:block;font-size:18px;font-weight:800;color:#0f172a}
.yza-owner-lock-card p{margin:8px 0 18px;font-size:13.5px;line-height:1.55;color:#475569}
.yza-owner-lock-card a.yza-owner-lock-cta{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:11px 20px;border-radius:12px;background:#0f172a;color:#fff !important;font-size:13px;font-weight:700;text-decoration:none}
.yza-owner-lock-card a.yza-owner-lock-cta:hover{background:#e11d48}
</style>
<div class="yza-promo-body-container @if(!$hasVerifiedOwnership) is-owner-locked @endif">
    @if(!$hasVerifiedOwnership)
        <div class="yza-owner-lock-overlay" role="alert">
            <div class="yza-owner-lock-card">
                <span class="yza-owner-lock-icon" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg></span>
                <strong>Promo Owner Terkunci</strong>
                <p>Kupon dan benefit promo aktif setelah Anda membeli mobil melalui YZA Automotive dan pembelian terverifikasi.</p>
                <a class="yza-owner-lock-cta" href="{{ route('pelanggan.katalog-mobil.index') }}">Lihat Koleksi Mobil <span aria-hidden="true">→</span></a>
            </div>
        </div>
    @endif
    @if(!$customer->membership?->isActive())
        <div class="yza-alert">Promo khusus YZA Member aktif. <a href="{{ route('pelanggan.membership.activation') }}">Aktifkan membership</a></div>
    @endif

    <!-- Filter Bar di Bawah Box Header -->
    <div class="yza-promo-filter-wrapper">
        <!-- Row 1: Search Box full width -->
        <div class="yza-promo-search-row">
            <div class="yza-promo-search-box">
                <span class="search-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
                <input type="text" 
                       id="yza-promo-custom-search" 
                       class="yza-promo-search-input" 
                       style="padding-left: 48px !important;"
                       placeholder="Cari promo kustom (contoh: 50%, free, tune up, car wash...)" 
                       aria-label="Cari promo">
                <button type="button" id="yza-promo-search-clear" class="search-clear-btn" style="display: none;" title="Hapus pencarian">✕</button>
            </div>
        </div>

        <!-- Row 2: Tombol Filter Kategori (Pill Chips) + Dropdown -->
        <div class="yza-promo-filter-row-bottom">
            <div class="yza-promo-tab-group" role="tablist">
                <button type="button" class="yza-promo-tab is-active" data-filter-tab="owned">
                    <span>Sudah Punya / Klaim</span>
                    <span class="tab-count" id="count-owned">0</span>
                </button>
                <button type="button" class="yza-promo-tab" data-filter-tab="all">
                    <span>Semua Promo</span>
                    <span class="tab-count" id="count-all">0</span>
                </button>
                <button type="button" class="yza-promo-tab" data-filter-tab="carwash">
                    <span>Promo Car Wash</span>
                    <span class="tab-count" id="count-carwash">0</span>
                </button>
                <button type="button" class="yza-promo-tab" data-filter-tab="servis">
                    <span>Promo Servis</span>
                    <span class="tab-count" id="count-servis">0</span>
                </button>
                <button type="button" class="yza-promo-tab" data-filter-tab="sparepart">
                    <span>Spare Part</span>
                    <span class="tab-count" id="count-sparepart">0</span>
                </button>
                <button type="button" class="yza-promo-tab" data-filter-tab="other">
                    <span>Layanan Lainnya</span>
                    <span class="tab-count" id="count-other">0</span>
                </button>
            </div>

            <!-- Dropdown Filter Cepat di kanan sejajar tab -->
            <div class="yza-promo-dropdown-box">
                <select id="yza-promo-discount-dropdown" class="yza-promo-dropdown-select" aria-label="Filter Cepat Diskon">
                    <option value="all">Filter Diskon (Semua)</option>
                    <option value="free">FREE</option>
                    <option value="50%">Diskon 50%</option>
                    <option value="30%">Diskon 30% - 40%</option>
                    <option value="trade">Subsidi Trade-In</option>
                </select>
            </div>
        </div>
    </div>

    <div class="yza-home yza-home-reference yza-promo-page">
        <div class="yza-home-service-grid yza-promo-catalog-grid">
        @forelse($items as $item)
            @php
                $code = strtoupper((string) $item->kode_promo);
                $name = strtolower((string) $item->nama);
                $deskripsi = strtolower((string) ($item->deskripsi ?? ''));
                $pointsCost = (int) ($item->points_cost ?? 0);
                $benefitType = $item->jenis_benefit;
                $benefitVal = (float) $item->nilai_benefit;
                
                // Specific overrides for iconic automotive services
                if (str_contains($code, 'CARWASH') || str_contains($code, 'WASH') || str_contains($code, 'DYNOTEST') || str_contains($code, 'TEST')) {
                    $discountBadge = 'FREE';
                } elseif ($benefitType === 'persentase' && $benefitVal > 0) {
                    $discountBadge = '-' . (int)$benefitVal . '%';
                } elseif ($benefitType === 'nominal' && $benefitVal >= 1000000) {
                    $discountBadge = 'Rp ' . (int)($benefitVal / 1000000) . ' JT';
                } elseif ($benefitType === 'nominal' && $benefitVal >= 1000) {
                    $discountBadge = '-' . (int)($benefitVal / 1000) . ' RB';
                } elseif ($pointsCost === 0) {
                    $discountBadge = 'FREE';
                } else {
                    $discountBadge = 'PROMO';
                }

                // Color and icon mappings matching DailyUI vibrant header style
                if (str_contains($code, 'SRV50') || str_contains($code, 'SERVICE10')) {
                    $accentColor = '#c51624'; // Crimson red (Miele)
                    $cardImage = 'assets/images/member/reference-service-bengkel.jpg';
                    $cardIcon = 'tools';
                    $brandSubtitle = 'YZA OFFICIAL WORKSHOP';
                } elseif (str_contains($code, 'TUNE') || str_contains($name, 'tune up')) {
                    $accentColor = '#d9381e'; // Fiery red
                    $cardImage = 'assets/images/member/member-benefit-service-v2.png';
                    $cardIcon = 'tools';
                    $brandSubtitle = 'YZA PERFORMANCE TUNING';
                } elseif (str_contains($code, 'WASH') || str_contains($name, 'cuci')) {
                    $accentColor = '#1b74e4'; // Royal blue (Nike)
                    $cardImage = 'assets/images/member/member-benefit-carwash-v2.png';
                    $cardIcon = 'wash';
                    $brandSubtitle = 'YZA HYDRAULIC CAR WASH';
                } elseif (str_contains($code, 'REM') || str_contains($code, 'BRAKE') || str_contains($name, 'rem') || str_contains($name, 'kampas')) {
                    $accentColor = '#a81822'; // Brembo red
                    $cardImage = 'assets/images/member/member-benefit-brakes-v2.png';
                    $cardIcon = 'wheel';
                    $brandSubtitle = 'YZA BRAKE & CALIPER';
                } elseif (str_contains($code, 'COAT') || str_contains($name, 'coating')) {
                    $accentColor = '#4a2c59'; // Deep purple (Apple)
                    $cardImage = 'assets/images/member/reference-service-test-drive.jpg';
                    $cardIcon = 'wheel';
                    $brandSubtitle = 'YZA CERAMIC COATING';
                } elseif (str_contains($code, 'TRADE') || str_contains($name, 'trade') || str_contains($name, 'tukar')) {
                    $accentColor = '#0f766e'; // Emerald teal
                    $cardImage = 'assets/images/member/reference-service-trade-in.jpg';
                    $cardIcon = 'trade';
                    $brandSubtitle = 'YZA TRADE-IN PROGRAM';
                } elseif (str_contains($code, 'PART') || str_contains($name, 'spare part')) {
                    $accentColor = '#c59b34'; // Golden mustard (Sean John)
                    $cardImage = 'assets/images/member/member-benefit-brakes-v2.png';
                    $cardIcon = 'tools';
                    $brandSubtitle = 'YZA GENUINE PARTS';
                } elseif (str_contains($code, 'SPOORING')) {
                    $accentColor = '#0d9488';
                    $cardImage = 'assets/images/member/reference-service-bengkel.jpg';
                    $cardIcon = 'wheel';
                    $brandSubtitle = 'YZA 3D SPOORING';
                } elseif (str_contains($code, 'ACCARE')) {
                    $accentColor = '#0284c7';
                    $cardImage = 'assets/images/member/reference-service-bengkel.jpg';
                    $cardIcon = 'wash';
                    $brandSubtitle = 'YZA CLIMATE CONTROL';
                } elseif (str_contains($code, 'TIRES')) {
                    $accentColor = '#ea580c';
                    $cardImage = 'assets/images/member/member-benefit-brakes-v2.png';
                    $cardIcon = 'wheel';
                    $brandSubtitle = 'YZA TIRE CENTER';
                } elseif (str_contains($code, 'TEST') || str_contains($code, 'DRIVE') || str_contains($name, 'test drive')) {
                    $accentColor = '#ef233c'; // Luxury red
                    $cardImage = 'assets/images/member/reference-service-test-drive.jpg';
                    $cardIcon = 'car';
                    $brandSubtitle = 'YZA VIP TEST DRIVE';
                } elseif (str_contains($code, 'INTERIOR')) {
                    $accentColor = '#7c3aed';
                    $cardImage = 'assets/images/member/reference-service-car-wash.jpg';
                    $cardIcon = 'wash';
                    $brandSubtitle = 'YZA INTERIOR CARE';
                } elseif (str_contains($code, 'DYNO')) {
                    $accentColor = '#1e293b';
                    $cardImage = 'assets/images/member/reference-service-trade-in.jpg';
                    $cardIcon = 'tools';
                    $brandSubtitle = 'YZA DYNO LAB';
                } elseif (str_contains($code, 'VKM')) {
                    $accentColor = '#334155';
                    $cardImage = 'assets/images/member/reference-service-test-drive.jpg';
                    $cardIcon = 'car';
                    $brandSubtitle = 'YZA WINDOW FILM';
                } else {
                    $accentColor = '#c51624';
                    $cardImage = 'assets/images/member/reference-service-bengkel.jpg';
                    $cardIcon = 'tools';
                    $brandSubtitle = 'YZA AUTOMOTIVE';
                }

                $expiryDate = optional($item->selesai_at)->format('d M Y') ?: '13 Nov 2027';

                // Determine Category Group
                $categoryKey = 'other';
                if (str_contains($code, 'CARWASH') || str_contains($code, 'WASH') || str_contains($name, 'cuci') || str_contains($code, 'INTERIOR')) {
                    $categoryKey = 'carwash';
                } elseif (str_contains($code, 'SRV') || str_contains($code, 'SERVICE') || str_contains($code, 'TUNE') || str_contains($code, 'REM') || str_contains($code, 'BRAKE') || str_contains($code, 'SPOORING') || str_contains($code, 'ACCARE') || str_contains($code, 'DYNO') || str_contains($name, 'servis') || str_contains($name, 'tune up') || str_contains($name, 'kampas')) {
                    $categoryKey = 'servis';
                } elseif (str_contains($code, 'PART') || str_contains($code, 'TIRES') || str_contains($code, 'VKM') || str_contains($name, 'spare part') || str_contains($name, 'ban') || str_contains($name, 'kaca film')) {
                    $categoryKey = 'sparepart';
                }

                $claimedPromoCodes = $claimedPromoCodes ?? [];
                $claimedPromoIds = $claimedPromoIds ?? [];
                $isClaimedWithPoints = in_array((int)$item->id_promo, $claimedPromoIds) || in_array($code, $claimedPromoCodes);
                $isOwned = ($pointsCost === 0) || $isClaimedWithPoints;
                $isFree = ($discountBadge === 'FREE') || ($pointsCost === 0);
                $is50Pct = ($benefitType === 'persentase' && (int)$benefitVal === 50) || str_contains($name, '50%') || ($discountBadge === '-50%');
                $is30PlusPct = ($benefitType === 'persentase' && (int)$benefitVal >= 30 && (int)$benefitVal <= 45) || str_contains($discountBadge, '30%') || str_contains($discountBadge, '35%') || str_contains($discountBadge, '40%');
                $isTrade = str_contains($code, 'TRADE') || str_contains($name, 'trade');

                // Search keywords string for real-time custom filtering
                $searchKeywords = strtolower(implode(' ', [
                    $item->nama,
                    $code,
                    $item->deskripsi ?? '',
                    $discountBadge,
                    $brandSubtitle,
                    $categoryKey,
                    $isOwned ? 'sudah diklaim punya milik' : 'tersedia klaim tukar',
                    $isFree ? 'free gratis 0 poin' : '',
                    $is50Pct ? '50% diskon 50' : '',
                    $is30PlusPct ? '30% 35% 40% diskon' : '',
                    $isTrade ? 'trade in subsidi 5 jt' : '',
                    $pointsCost > 0 ? $pointsCost . ' poin' : 'gratis member',
                ]));
            @endphp
            <article class="yza-daily-coupon-card" 
                     data-promo-card
                     data-promo-id="{{ $item->id_promo }}"
                     data-category="{{ $categoryKey }}"
                     data-is-owned="{{ $isOwned ? 'true' : 'false' }}"
                     data-is-claimed="{{ $isClaimedWithPoints ? 'true' : 'false' }}"
                     data-points="{{ $pointsCost }}"
                     data-badge="{{ $discountBadge }}"
                     data-is-free="{{ $isFree ? 'true' : 'false' }}"
                     data-is-50="{{ $is50Pct ? 'true' : 'false' }}"
                     data-is-30="{{ $is30PlusPct ? 'true' : 'false' }}"
                     data-is-trade="{{ $isTrade ? 'true' : 'false' }}"
                     data-search="{{ $searchKeywords }}"
                     style="--coupon-accent: {{ $accentColor }};">
                <div class="yza-daily-coupon-banner">
                    <img src="{{ asset($cardImage) }}" alt="{{ $item->nama }}" class="yza-daily-coupon-img" loading="lazy">
                    <span class="yza-daily-coupon-overlap-badge">{{ $discountBadge }}</span>
                </div>

                <div class="yza-daily-coupon-body">
                    <h3 class="yza-daily-coupon-title">{{ $item->nama }}</h3>
                    <p class="yza-daily-coupon-desc">{{ $item->deskripsi ?: 'Benefit promo eksklusif untuk member YZA Automotive.' }}</p>

                    <div class="yza-daily-coupon-footer">
                        <div class="yza-daily-coupon-expire">
                            <span class="expire-lbl">Expires:</span>
                            <strong class="expire-val">{{ $expiryDate }}</strong>
                        </div>

                        <div class="yza-daily-coupon-action">
                            @if($isClaimedWithPoints)
                                <button type="button" 
                                        class="yza-daily-claim-btn is-claimed"
                                        data-view-coupon
                                        data-promo-id="{{ $item->id_promo }}"
                                        data-promo-code="{{ $item->kode_promo }}"
                                        data-promo-title="{{ $item->nama }}"
                                        data-promo-desc="{{ $item->deskripsi ?: 'Kupon promo telah diklaim dan siap digunakan.' }}"
                                        data-promo-expiry="{{ $expiryDate }}"
                                        data-promo-points="{{ $pointsCost }}">
                                    <span>Lihat Kode</span>
                                </button>
                            @elseif($pointsCost > 0)
                                <button type="button" 
                                        class="yza-daily-claim-btn"
                                        data-claim-coupon
                                        data-promo-id="{{ $item->id_promo }}"
                                        data-promo-code="{{ $item->kode_promo }}"
                                        data-promo-title="{{ $item->nama }}"
                                        data-promo-points="{{ $pointsCost }}"
                                        data-claim-url="{{ route('pelanggan.promo.claim', $item->id_promo) }}">
                                    <span>Tukar {{ number_format($pointsCost, 0, ',', '.') }} Poin</span>
                                </button>
                            @else
                                <button type="button" 
                                        class="yza-daily-claim-btn is-claimed"
                                        data-view-coupon
                                        data-promo-id="{{ $item->id_promo }}"
                                        data-promo-code="{{ $item->kode_promo }}"
                                        data-promo-title="{{ $item->nama }}"
                                        data-promo-desc="{{ $item->deskripsi ?: 'Promo benefit eksklusif untuk member YZA.' }}"
                                        data-promo-expiry="{{ $expiryDate }}"
                                        data-promo-points="0">
                                    <span>Lihat Kode</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <p class="member-empty" style="grid-column: 1 / -1; color: #94a3b8; text-align: center; padding: 40px 0;">Belum ada promo aktif untuk Anda.</p>
        @endforelse

        <div id="yza-promo-empty-filter" style="display: none; grid-column: 1 / -1; text-align: center; padding: 48px 20px; background: #ffffff; border-radius: 16px; border: 1px dashed #cbd5e1; margin-top: 10px;">
            <div style="margin-bottom: 12px; color: #94a3b8; display: flex; justify-content: center;">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>
            <h3 style="font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Tidak ada kupon yang cocok</h3>
            <p style="font-size: 13.5px; color: #64748b; margin-bottom: 16px;">Coba ubah kata kunci pencarian atau reset filter untuk melihat promo lainnya.</p>
            <button type="button" class="yza-filter-reset-btn-pill" id="yza-btn-empty-reset">Tampilkan Semua Promo</button>
        </div>
    </div>

    @if($items->hasPages())
        <div style="margin-top: 36px; display: flex; justify-content: center;">
            {{ $items->links() }}
        </div>
    @endif
</div>
</div>

<!-- Kupon Claim Modal -->
<div id="yza-claim-modal" class="yza-claim-modal-backdrop" hidden>
    <div class="yza-claim-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="yza-claim-modal-title">
        <button type="button" class="yza-claim-modal-close" data-close-claim-modal aria-label="Tutup modal">✕</button>
        
        <!-- Step 1: Confirmation View -->
        <div id="yza-claim-step-confirm">
            <div class="yza-claim-modal-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="10" rx="2"></rect>
                    <path d="M12 7v10M7 12h.01M17 12h.01"></path>
                </svg>
            </div>
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
                    <strong id="modal-current-points" class="highlight-balance">{{ number_format((int) ($customer->pointAccount?->saldo ?? 0), 0, ',', '.') }} Poin</strong>
                </div>
            </div>

            <div id="modal-claim-error" style="display: none; color: #f87171; font-size: 13px; margin-bottom: 16px; font-weight: 600;"></div>

            <div class="yza-claim-modal-actions">
                <button type="button" class="btn-cancel" data-close-claim-modal>Batal</button>
                <button type="button" class="btn-confirm" id="btn-submit-claim">Konfirmasi Tukar</button>
            </div>
        </div>

        <!-- Step 2: Success View -->
        <div id="yza-claim-step-success" style="display: none;">
            <div class="yza-claim-modal-icon" style="background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #16a34a;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
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
                <button type="button" class="btn-confirm" data-close-claim-modal style="width: 100%;">Selesai</button>
            </div>
        </div>

        <!-- Step 3: View Code View (Free / Member Benefit) -->
        <div id="yza-claim-step-view" style="display: none;">
            <div class="yza-claim-modal-icon" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #2563eb;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="10" rx="2"></rect>
                    <path d="M12 7v10M7 12h.01M17 12h.01"></path>
                </svg>
            </div>
            <h3 id="modal-view-title">Detail Kupon Promo</h3>
            <p id="modal-view-desc">Gunakan kode kupon ini saat checkout layanan atau pembelian mobil.</p>
            
            <div class="yza-claim-modal-code-box">
                <strong id="modal-view-code">KODE</strong>
                <button type="button" class="yza-claim-copy-btn" id="btn-copy-view-code">Salin Kode</button>
            </div>

            <div class="yza-claim-modal-box" style="margin-bottom: 20px;">
                <div class="yza-claim-modal-row">
                    <span>Tipe Benefit</span>
                    <strong id="modal-view-benefit-type" style="color: #4ade80;">Benefit Eksklusif Member</strong>
                </div>
                <div class="yza-claim-modal-row">
                    <span>Biaya Poin</span>
                    <strong id="modal-view-benefit-cost" style="color: #fff;">0 Poin (Gratis)</strong>
                </div>
                <div class="yza-claim-modal-row">
                    <span>Masa Berlaku</span>
                    <strong id="modal-view-expiry" style="color: #cbd5e1;">-</strong>
                </div>
            </div>

            <div class="yza-claim-modal-actions">
                <button type="button" class="btn-confirm" data-close-claim-modal style="width: 100%;">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const modal = document.getElementById('yza-claim-modal');
    if (!modal) return;

    const stepConfirm = document.getElementById('yza-claim-step-confirm');
    const stepSuccess = document.getElementById('yza-claim-step-success');
    const stepView = document.getElementById('yza-claim-step-view');
    const titleEl = document.getElementById('modal-coupon-title');
    const codeEl = document.getElementById('modal-coupon-code');
    const costEl = document.getElementById('modal-coupon-cost');
    const errorEl = document.getElementById('modal-claim-error');
    const btnSubmit = document.getElementById('btn-submit-claim');
    const btnCopy = document.getElementById('btn-copy-code');
    const successCode = document.getElementById('modal-success-code');
    const successDeducted = document.getElementById('modal-success-deducted');
    const successBalance = document.getElementById('modal-success-balance');
    const modalBalance = document.getElementById('modal-current-points');

    const modalViewTitle = document.getElementById('modal-view-title');
    const modalViewDesc = document.getElementById('modal-view-desc');
    const modalViewCode = document.getElementById('modal-view-code');
    const modalViewExpiry = document.getElementById('modal-view-expiry');
    const btnCopyView = document.getElementById('btn-copy-view-code');

    let currentAction = null;
    let activeTriggerBtn = null;

    const closeModal = () => {
        modal.hidden = true;
        currentAction = null;
        if (errorEl) {
            errorEl.style.display = 'none';
            errorEl.textContent = '';
        }
        if (stepConfirm) stepConfirm.style.display = 'none';
        if (stepSuccess) stepSuccess.style.display = 'none';
        if (stepView) stepView.style.display = 'none';
    };

    modal.addEventListener('click', (e) => {
        if (e.target === modal || e.target.closest('[data-close-claim-modal]')) {
            closeModal();
        }
    });

    // Handle Tukar Poin Button
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
            
            if (stepConfirm) stepConfirm.style.display = '';
            if (stepSuccess) stepSuccess.style.display = 'none';
            if (stepView) stepView.style.display = 'none';
            if (errorEl) {
                errorEl.style.display = 'none';
                errorEl.textContent = '';
            }

            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Konfirmasi Tukar';
            modal.hidden = false;
        });
    });

    // Handle Lihat Kode Button (without page navigation, supports dynamic click delegation)
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-view-coupon]');
        if (!btn) return;
        e.preventDefault();
        if (errorEl) {
            errorEl.style.display = 'none';
            errorEl.textContent = '';
        }
        if (stepConfirm) stepConfirm.style.display = 'none';
        if (stepSuccess) stepSuccess.style.display = 'none';
        if (stepView) stepView.style.display = '';

        if (modalViewTitle) modalViewTitle.textContent = btn.dataset.promoTitle || 'Detail Kupon Promo';
        if (modalViewDesc) modalViewDesc.textContent = btn.dataset.promoDesc || 'Gunakan kode kupon ini saat checkout layanan atau pembelian mobil.';
        if (modalViewCode) modalViewCode.textContent = btn.dataset.promoCode || '-';
        if (modalViewExpiry) modalViewExpiry.textContent = btn.dataset.promoExpiry || '-';

        const viewPoints = parseInt(btn.dataset.promoPoints || '0', 10);
        const benefitTypeEl = document.getElementById('modal-view-benefit-type');
        const benefitCostEl = document.getElementById('modal-view-benefit-cost');
        if (benefitTypeEl) {
            if (viewPoints > 0) {
                benefitTypeEl.textContent = 'Klaim Poin';
                benefitTypeEl.style.color = '#fb923c';
            } else {
                benefitTypeEl.textContent = 'Benefit Eksklusif Member';
                benefitTypeEl.style.color = '#4ade80';
            }
        }
        if (benefitCostEl) {
            if (viewPoints > 0) {
                benefitCostEl.textContent = viewPoints.toLocaleString('id-ID') + ' Poin';
                benefitCostEl.style.color = '#fb923c';
            } else {
                benefitCostEl.textContent = '0 Poin (Gratis)';
                benefitCostEl.style.color = '#fff';
            }
        }

        modal.hidden = false;
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

            if (modalBalance && typeof data.balance !== 'undefined') {
                modalBalance.textContent = Number(data.balance).toLocaleString('id-ID') + ' Poin';
            }

            successCode.textContent = data.promo_code || currentAction.code;
            successDeducted.textContent = '-' + Number(data.cost || currentAction.points).toLocaleString('id-ID') + ' Poin';
            successBalance.textContent = Number(data.balance).toLocaleString('id-ID') + ' Poin';

            if (activeTriggerBtn) {
                activeTriggerBtn.classList.add('is-claimed');
                activeTriggerBtn.innerHTML = '<span>Lihat Kode</span>';
                activeTriggerBtn.disabled = false;
                activeTriggerBtn.removeAttribute('data-claim-coupon');
                activeTriggerBtn.setAttribute('data-view-coupon', '');
                activeTriggerBtn.dataset.promoCode = data.promo_code || currentAction.code;
                activeTriggerBtn.dataset.promoTitle = currentAction.title;
                activeTriggerBtn.dataset.promoDesc = 'Kupon promo telah berhasil ditukar dengan ' + (currentAction.points || 0) + ' poin.';
                activeTriggerBtn.dataset.promoExpiry = '13 Nov 2027';
                activeTriggerBtn.dataset.promoPoints = currentAction.points || 0;
                const parentCard = activeTriggerBtn.closest('[data-promo-card]');
                if (parentCard) {
                    parentCard.dataset.isOwned = 'true';
                    parentCard.dataset.isClaimed = 'true';
                    parentCard.dataset.search += ' sudah diklaim punya milik';
                }
                if (typeof updateCounts === 'function') updateCounts();
                if (typeof applyFilters === 'function') applyFilters();
            }

            if (stepConfirm) stepConfirm.style.display = 'none';
            if (stepSuccess) stepSuccess.style.display = '';
            if (stepView) stepView.style.display = 'none';
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

    if (btnCopyView) {
        btnCopyView.addEventListener('click', () => {
            const code = modalViewCode ? modalViewCode.textContent.trim() : '';
            if (!code) return;
            navigator.clipboard.writeText(code).then(() => {
                const original = btnCopyView.textContent;
                btnCopyView.textContent = 'Tersalin!';
                setTimeout(() => {
                    btnCopyView.textContent = original;
                }, 2000);
            });
        });
    }

    // -------------------------------------------------------------------------
    // Interactive Filter Bar Logic (Category Tabs, Discount Dropdown, Real-time Search)
    // -------------------------------------------------------------------------
    const promoCards = Array.from(document.querySelectorAll('[data-promo-card]'));
    const searchInput = document.getElementById('yza-promo-custom-search');
    const clearSearchBtn = document.getElementById('yza-promo-search-clear');
    const discountDropdown = document.getElementById('yza-promo-discount-dropdown');
    const tabButtons = Array.from(document.querySelectorAll('[data-filter-tab]'));
    const countLabel = document.getElementById('yza-filter-count-label');
    const resetBtn = document.getElementById('yza-filter-reset');
    const emptyFilterState = document.getElementById('yza-promo-empty-filter');
    const emptyResetBtn = document.getElementById('yza-btn-empty-reset');

    let currentTab = 'owned';
    let currentSearch = '';

    const updateCounts = () => {
        const counts = {
            all: promoCards.length,
            owned: promoCards.filter(c => c.dataset.isOwned === 'true').length,
            carwash: promoCards.filter(c => c.dataset.category === 'carwash').length,
            servis: promoCards.filter(c => c.dataset.category === 'servis').length,
            sparepart: promoCards.filter(c => c.dataset.category === 'sparepart').length,
            other: promoCards.filter(c => c.dataset.category === 'other').length,
        };

        const countAllEl = document.getElementById('count-all');
        const countOwnedEl = document.getElementById('count-owned');
        const countCarwashEl = document.getElementById('count-carwash');
        const countServisEl = document.getElementById('count-servis');
        const countSparepartEl = document.getElementById('count-sparepart');
        const countOtherEl = document.getElementById('count-other');

        if (countAllEl) countAllEl.textContent = counts.all;
        if (countOwnedEl) countOwnedEl.textContent = counts.owned;
        if (countCarwashEl) countCarwashEl.textContent = counts.carwash;
        if (countServisEl) countServisEl.textContent = counts.servis;
        if (countSparepartEl) countSparepartEl.textContent = counts.sparepart;
        if (countOtherEl) countOtherEl.textContent = counts.other;
    };

    const applyFilters = () => {
        let visibleCount = 0;
        const query = (currentSearch || '').trim().toLowerCase();
        const selectedDiscount = discountDropdown ? discountDropdown.value : 'all';

        if (discountDropdown) {
            discountDropdown.classList.toggle('is-active', selectedDiscount !== 'all');
        }

        promoCards.forEach(card => {
            let matchTab = true;
            if (currentTab === 'owned') {
                matchTab = card.dataset.isOwned === 'true';
            } else if (currentTab !== 'all') {
                matchTab = card.dataset.category === currentTab;
            }

            let matchDiscount = true;
            if (selectedDiscount === 'free') {
                matchDiscount = card.dataset.isFree === 'true';
            } else if (selectedDiscount === '50%') {
                matchDiscount = card.dataset.is50 === 'true';
            } else if (selectedDiscount === '30%') {
                matchDiscount = card.dataset.is30 === 'true';
            } else if (selectedDiscount === 'trade') {
                matchDiscount = card.dataset.isTrade === 'true';
            }

            let matchSearch = true;
            if (query) {
                const searchData = card.dataset.search || '';
                const terms = query.split(/\s+/).filter(Boolean);
                matchSearch = terms.every(term => searchData.includes(term));
            }

            const isVisible = matchTab && matchDiscount && matchSearch;
            card.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });

        if (countLabel) {
            countLabel.innerHTML = `Menampilkan <strong>${visibleCount}</strong> dari ${promoCards.length} promo`;
        }

        const hasActiveFilters = currentTab !== 'owned' || selectedDiscount !== 'all' || query !== '';
        if (resetBtn) {
            resetBtn.style.display = hasActiveFilters ? 'inline-block' : 'none';
        }

        if (emptyFilterState) {
            emptyFilterState.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    };

    const resetAllFilters = () => {
        currentTab = 'owned';
        currentSearch = '';
        if (discountDropdown) {
            discountDropdown.value = 'all';
            discountDropdown.classList.remove('is-active');
        }
        if (searchInput) {
            searchInput.value = '';
            if (clearSearchBtn) clearSearchBtn.style.display = 'none';
        }
        tabButtons.forEach(btn => btn.classList.toggle('is-active', btn.dataset.filterTab === 'owned'));
        applyFilters();
    };

    // Category Tabs click
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            tabButtons.forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            currentTab = btn.dataset.filterTab || 'all';
            applyFilters();
        });
    });

    // Discount Dropdown change
    if (discountDropdown) {
        discountDropdown.addEventListener('change', () => {
            applyFilters();
        });
    }

    // Custom Search input
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            currentSearch = e.target.value;
            if (clearSearchBtn) {
                clearSearchBtn.style.display = currentSearch ? 'inline-flex' : 'none';
            }
            applyFilters();
        });
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', () => {
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
            }
            currentSearch = '';
            clearSearchBtn.style.display = 'none';
            applyFilters();
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', resetAllFilters);
    }

    if (emptyResetBtn) {
        emptyResetBtn.addEventListener('click', resetAllFilters);
    }

    // Initialize counts and default display on load
    updateCounts();
    applyFilters();
})();
</script>
@endpush
