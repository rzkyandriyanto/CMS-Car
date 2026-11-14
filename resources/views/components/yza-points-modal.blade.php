@php
    $ypCustomer = auth()->user()?->pelanggan;
@endphp
@if($ypCustomer)
@php
    $ypService = app(\App\Services\YzaPointService::class);
    $ypAccount = $ypCustomer->poinMember;
    $ypHasVerifiedOwnership = app(\App\Services\VerifiedCarOwnershipService::class)->customerHasVerifiedPurchase((int) $ypCustomer->id_pelanggan);
    $ypHistory = \App\Models\RiwayatPoin::with('transaksi')
        ->where('id_pelanggan', $ypCustomer->id_pelanggan)
        ->latest('created_at')
        ->limit(30)
        ->get();
@endphp

<div id="yza-points-modal" class="ypm-backdrop" data-yza-points-modal hidden role="dialog" aria-modal="true" aria-labelledby="ypm-title">
    <div class="ypm-dialog">
        <header class="ypm-head">
            <div class="ypm-head-left">
                <span class="ypm-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v10"/><path d="M15 9.5c0-1.1-1.3-2-3-2s-3 .9-3 2 1.3 2 3 2 3 .9 3 2-1.3 2-3 2-3-.9-3-2"/></svg>
                </span>
                <div>
                    <span class="ypm-eyebrow">YZA POINTS</span>
                    <h2 id="ypm-title">Saldo &amp; Riwayat Poin Anda</h2>
                    <p class="ypm-sub">Rp {{ number_format($ypService->rate(), 0, ',', '.') }} transaksi = 1 YZA Point</p>
                </div>
            </div>
            <button type="button" class="ypm-close" data-close-yza-points-modal aria-label="Tutup modal YZA Points">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </header>

        <div class="ypm-stats">
            <div class="ypm-stat ypm-stat-primary">
                <span>Saldo Saat Ini</span>
                <strong id="ypm-saldo">{{ number_format((int) ($ypAccount?->saldo ?? 0), 0, ',', '.') }} Poin</strong>
            </div>
            <div class="ypm-stat">
                <span>Total Diperoleh</span>
                <strong>{{ number_format((int) ($ypAccount?->total_diperoleh ?? 0), 0, ',', '.') }} Poin</strong>
            </div>
        </div>

        @if(!$ypHasVerifiedOwnership)
            <div class="ypm-lock-note">
                <div>
                    <strong>Benefit owner terkunci</strong>
                    <p>Riwayat points tetap tersimpan. Points dan promo owner akan aktif setelah Anda membeli mobil melalui YZA Automotive.</p>
                </div>
                <a class="ypm-lock-cta" href="{{ route('mobil.index') }}">Lihat Koleksi Mobil</a>
            </div>
        @endif

        <div class="ypm-body">
            <div class="ypm-section-head">
                <h3>Riwayat Poin</h3>
                <span>{{ $ypHistory->count() }} aktivitas terbaru</span>
            </div>

            <div class="ypm-list">
                @forelse($ypHistory as $row)
                    @php
                        $isDebit = in_array($row->jenis, ['pembalikan', 'penukaran'], true);
                    @endphp
                    <div class="ypm-row">
                        <div class="ypm-row-main">
                            <strong>{{ $row->keterangan ?: 'Perolehan YZA Points' }}</strong>
                            <small>
                                {{ optional($row->created_at)->format('d M Y · H:i') }}
                                @if($row->transaksi)
                                    · {{ $ypService->label($row->transaksi->jenis_transaksi) }} · {{ $row->transaksi->kode_transaksi }}
                                @endif
                            </small>
                        </div>
                        <div class="ypm-row-val">
                            <strong class="{{ $isDebit ? 'is-minus' : 'is-plus' }}">
                                {{ $isDebit ? '-' : '+' }}{{ number_format((int) $row->perubahan_poin, 0, ',', '.') }} Poin
                            </strong>
                            <small>Saldo setelah {{ number_format((int) $row->saldo_setelah, 0, ',', '.') }}</small>
                        </div>
                    </div>
                @empty
                    <div class="ypm-empty">
                        <strong>Belum ada riwayat poin.</strong>
                        <span>Poin akan tercatat otomatis setelah transaksi layanan atau pembelian selesai.</span>
                    </div>
                @endforelse
            </div>
        </div>

        <footer class="ypm-foot">
            <button type="button" class="ypm-btn-close" data-close-yza-points-modal>Tutup</button>
        </footer>
    </div>
</div>

<style>
.ypm-backdrop {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(15, 23, 42, 0.56);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}
.ypm-backdrop[hidden] {
    display: none !important;
}
.ypm-dialog {
    width: 100%;
    max-width: 660px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 40px);
}
.ypm-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    padding: 20px 24px 16px;
    border-bottom: 1px solid #f1f5f9;
}
.ypm-head-left {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}
.ypm-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: #fff1f2;
    color: #e11d48;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.ypm-eyebrow {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: #e11d48;
    margin-bottom: 2px;
}
.ypm-head h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
}
.ypm-sub {
    margin: 4px 0 0;
    font-size: 13px;
    color: #64748b;
}
.ypm-close {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.15s ease, color 0.15s ease;
}
.ypm-close:hover {
    background: #f8fafc;
    color: #0f172a;
}
.ypm-stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    padding: 16px 24px 0;
}
.ypm-stat {
    padding: 14px 16px;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.ypm-stat-primary {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    border-color: #0f172a;
    color: #ffffff;
}
.ypm-stat span {
    font-size: 12px;
    font-weight: 500;
    color: #64748b;
}
.ypm-stat-primary span {
    color: #cbd5e1;
}
.ypm-stat strong {
    font-size: 20px;
    font-weight: 700;
    color: #0f172a;
}
.ypm-stat-primary strong {
    color: #ffffff;
}
.ypm-lock-note {
    margin: 14px 24px 0;
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid #fde68a;
    background: #fffbeb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.ypm-lock-note strong {
    display: block;
    font-size: 13px;
    color: #92400e;
}
.ypm-lock-note p {
    margin: 2px 0 0;
    font-size: 12px;
    color: #b45309;
    line-height: 1.45;
}
.ypm-lock-cta {
    padding: 7px 12px;
    border-radius: 9px;
    background: #0f172a;
    color: #ffffff !important;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
}
.ypm-body {
    padding: 16px 24px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-height: 0;
}
.ypm-section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}
.ypm-section-head h3 {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
}
.ypm-section-head span {
    font-size: 12px;
    color: #64748b;
}
.ypm-list {
    overflow-y: auto;
    max-height: 310px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding-right: 4px;
}
.ypm-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid #f1f5f9;
    background: #ffffff;
}
.ypm-row:hover {
    border-color: #e2e8f0;
    background: #f8fafc;
}
.ypm-row-main {
    min-width: 0;
}
.ypm-row-main strong {
    display: block;
    font-size: 13.5px;
    font-weight: 600;
    color: #0f172a;
}
.ypm-row-main small {
    display: block;
    margin-top: 2px;
    font-size: 12px;
    color: #64748b;
}
.ypm-row-val {
    text-align: right;
    flex-shrink: 0;
}
.ypm-row-val strong {
    display: block;
    font-size: 13.5px;
    font-weight: 700;
}
.ypm-row-val strong.is-plus {
    color: #059669;
}
.ypm-row-val strong.is-minus {
    color: #e11d48;
}
.ypm-row-val small {
    display: block;
    margin-top: 2px;
    font-size: 11.5px;
    color: #64748b;
}
.ypm-empty {
    padding: 28px 16px;
    text-align: center;
    border: 1px dashed #cbd5e1;
    border-radius: 14px;
    background: #f8fafc;
}
.ypm-empty strong {
    display: block;
    font-size: 14px;
    color: #0f172a;
}
.ypm-empty span {
    display: block;
    margin-top: 4px;
    font-size: 12.5px;
    color: #64748b;
}
.ypm-foot {
    padding: 14px 24px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: flex-end;
    background: #fafafa;
}
.ypm-btn-close {
    padding: 9px 18px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #0f172a;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}
.ypm-btn-close:hover {
    background: #f1f5f9;
}
@media (max-width: 560px) {
    .ypm-stats {
        grid-template-columns: 1fr;
    }
    .ypm-row {
        flex-direction: column;
        align-items: flex-start;
    }
    .ypm-row-val {
        text-align: left;
    }
}
</style>

<script>
(() => {
    const modal = document.getElementById('yza-points-modal');
    if (!modal) return;

    const openModal = () => {
        const profileMenu = document.querySelector('[data-member-profile-menu]');
        if (profileMenu) profileMenu.hidden = true;
        const mobileDrawer = document.querySelector('[data-member-mobile-drawer]');
        if (mobileDrawer && mobileDrawer.classList.contains('is-open')) {
            mobileDrawer.classList.remove('is-open');
            mobileDrawer.setAttribute('aria-hidden', 'true');
            mobileDrawer.setAttribute('inert', '');
        }
        const liveDisplay = document.getElementById('yza-points-display');
        const modalSaldo = document.getElementById('ypm-saldo');
        if (liveDisplay && modalSaldo && liveDisplay.textContent.trim() !== '') {
            modalSaldo.textContent = liveDisplay.textContent.trim() + ' Poin';
        }
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
        modal.hidden = true;
        document.body.style.overflow = '';
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-open-yza-points-modal]');
        if (trigger) {
            event.preventDefault();
            openModal();
            return;
        }
        if (event.target === modal || event.target.closest('[data-close-yza-points-modal]')) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });
})();
</script>
@endif
