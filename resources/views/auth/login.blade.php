<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · YZA Automotive</title>
    <link rel="icon" href="{{ asset('assets/images/branding/logo-yza.png') }}">
    @foreach (['variables.css','base.css','components.css','pages/login.css'] as $style)
        <link rel="stylesheet" href="{{ asset('assets/css/'.$style) }}">
    @endforeach
    @include('components.responsive-foundation')
</head>
<body>
<main class="login-page">
    <section class="login-brand">
        <div class="login-brand-inner">
            <a class="brand" href="{{ url('/') }}">
                <img src="{{ asset('assets/images/branding/logo-yza.png') }}" alt="YZA Automotive">
            </a>
            <div class="metal-line"></div>
            <h1>YZA AUTOMOTIVE</h1>
            <p>Akses eksklusif untuk seluruh layanan otomotif YZA dalam satu akun.</p>
        </div>
    </section>

    <section class="login-form-side">
        <form class="login-form" id="loginForm" method="post" action="{{ route('login.store') }}" autocomplete="off" novalidate>
            @csrf
            <a class="login-home-link" href="{{ url('/') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"></path></svg>
                <span>Kembali ke Beranda</span>
            </a>
            
            <span class="eyebrow">YZA Portal Access</span>
            <h2>Selamat Datang</h2>
            <p class="intro">Masuk untuk mengakses Member Center atau dashboard operasional dealer YZA Automotive.</p>

            <!-- Trigger Button Modal Demo Role -->
            <div class="demo-role-trigger-box">
                <button type="button" class="btn-demo-roles" id="btnOpenRoleModal" aria-haspopup="dialog" aria-controls="roleModalOverlay">
                    <span class="btn-demo-roles-badge">⚡ DEMO LOGIN</span>
                    <div class="btn-demo-roles-content">
                        <strong>Pilih Akun Demo & Cepat Masuk</strong>
                        <span>Klik untuk langsung login sebagai Member atau Admin.</span>
                    </div>
                    <svg class="btn-demo-roles-arrow" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </button>
            </div>

            @if (session('registered'))
                <div class="alert alert-success" role="status">{{ session('registered') }}</div>
            @endif 
            
            @if ($errors->any())
                <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
            @endif

            <div class="form-group">
                <label class="form-label" for="identity">Username atau Email</label>
                <div class="input-with-icon">
                    <svg class="field-icon" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.2"></circle><path d="M5.5 19c.7-3.1 2.9-4.7 6.5-4.7s5.8 1.6 6.5 4.7"></path></svg>
                    <input class="form-control" id="identity" name="identity" type="text" value="{{ old('identity') }}" autocomplete="username" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="password-field">
                    <svg class="field-icon" viewBox="0 0 24 24"><rect x="5.5" y="10.5" width="13" height="9" rx="1.8"></rect><path d="M8.5 10.5V8a3.5 3.5 0 0 1 7 0v2.5"></path></svg>
                    <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
                    <button class="password-toggle" type="button" aria-label="Tampilkan password">
                        <svg viewBox="0 0 24 24"><path d="M2.5 12s3.4-5 9.5-5 9.5 5 9.5 5-3.4 5-9.5 5-9.5-5-9.5-5Z"></path><circle cx="12" cy="12" r="2.5"></circle></svg>
                    </button>
                </div>
            </div>

            <div class="login-form-actions">
                <button class="button button-primary login-submit" type="submit" id="btnSubmitLogin">
                    <span>MASUK SISTEM</span>
                    <svg viewBox="0 0 24 24"><path d="M5 12h13M13 6l6 6-6 6"></path></svg>
                </button>
            </div>

            <p class="login-register">Belum menjadi YZA Member? <a href="{{ route('register') }}">Buat Member</a></p>
        </form>
    </section>
</main>

<!-- Modal Pilih Role & Cepat Masuk (Simpel & Rapi) -->
<div class="role-modal-overlay" id="roleModalOverlay" aria-hidden="true">
    <div class="role-modal-card" role="dialog" aria-modal="true" aria-labelledby="roleModalTitle" style="max-width: 680px;">
        <div class="role-modal-header">
            <div class="role-modal-title-group">
                <div class="role-modal-kicker"><span class="kicker-dot"></span> YZA DEMO ACCESS</div>
                <h3 id="roleModalTitle">Pilih Akun Demo</h3>
                <p>Pilih akun utama yang sudah memiliki halaman lengkap dan siap didemokan.</p>
            </div>
            <button type="button" class="role-modal-close" id="btnCloseRoleModal" aria-label="Tutup modal">
                <svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="role-modal-body">
            <!-- 2 Akun Utama Yang Sudah Siap & Lengkap -->
            <div style="display: flex; flex-direction: column; gap: 14px;">
                
                <!-- 1. YZA MEMBER / PELANGGAN -->
                <div class="role-card-item role-card-item--member" style="padding: 20px;" onclick="selectRoleAndLogin('member506', 'member506', this)">
                    <div>
                        <div class="role-card-top">
                            <div style="display:flex; gap:8px; align-items:center;">
                                <span class="role-badge role-badge--member">
                                    <span class="member-dot"></span> AKTIF MEMBER
                                </span>
                                <span class="role-badge" style="color:#ffffff; background:rgba(255,38,52,0.18); border-color:rgba(255,38,52,0.5);">
                                    <span style="color:#ff2634; font-weight:900; margin-right:3px;">/</span> YZA PREMIUM MEMBER
                                </span>
                            </div>
                            <span class="role-cred-tag">member506</span>
                        </div>
                        <h4 class="role-card-title" style="font-size: 17px;">Portal Pelanggan <em>(YZA Member)</em></h4>
                        <p class="role-card-desc" style="font-size: 13px;">Halaman lengkap Member Center: profil member aktif, kelola kendaraan saya, booking servis & cuci mobil, katalog promo, serta YZA Points.</p>
                    </div>
                    <div class="role-card-action">
                        <span class="cred-preview">Username: <b>member506</b> &bull; Pass: <b>member506</b></span>
                        <span class="role-login-cta">
                            <span>Masuk Member</span>
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                </div>

                <!-- 2. SUPER ADMIN -->
                <div class="role-card-item" style="padding: 20px;" onclick="selectRoleAndLogin('superadmin506', 'superadmin506', this)">
                    <div>
                        <div class="role-card-top">
                            <span class="role-badge role-badge--admin">SUPER ADMIN (AKSES LENGKAP)</span>
                            <span class="role-cred-tag">superadmin506</span>
                        </div>
                        <h4 class="role-card-title" style="font-size: 17px;">Administrator <em>Dealer</em></h4>
                        <p class="role-card-desc" style="font-size: 13px;">Pusat kendali seluruh operasional: manajemen stok mobil, booking servis bengkel, gudang sparepart, verifikasi pembayaran, trade-in, promo & user.</p>
                    </div>
                    <div class="role-card-action">
                        <span class="cred-preview">Username: <b>superadmin506</b> &bull; Pass: <b>superadmin506</b></span>
                        <span class="role-login-cta">
                            <span>Masuk Admin</span>
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                </div>

            </div>

            <!-- Bagian Opsional: Akun Staf Divisi (Collapsed/Bisa Dibuka jika Dibutuhkan) -->
            <div style="margin-top: 18px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 14px;">
                <button type="button" id="btnToggleStaffRoles" style="background:none; border:none; color:#a0a6b2; font-size:12px; cursor:pointer; display:flex; align-items:center; gap:6px; padding:0;">
                    <span id="staffToggleIcon" style="font-size:10px;">▶</span>
                    <span>Tampilkan Akun Staf Khusus / Uji Hak Akses (Owner, Sales, SA, Mekanik, Finance, Gudang)</span>
                </button>

                <div id="staffRolesContainer" style="display:none; margin-top:14px;">
                    <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:10px;">
                        
                        <div class="role-card-item" style="padding:12px 14px;" onclick="selectRoleAndLogin('owner506', 'owner506', this)">
                            <div class="role-card-top"><span class="role-badge role-badge--owner">OWNER</span><span class="role-cred-tag">owner506</span></div>
                            <strong style="color:#fff; font-size:13px;">Owner / Pimpinan</strong>
                            <small style="color:#9aa1ab; font-size:11px;">Dashboard eksekutif finansial</small>
                        </div>

                        <div class="role-card-item" style="padding:12px 14px;" onclick="selectRoleAndLogin('sales506', 'sales506', this)">
                            <div class="role-card-top"><span class="role-badge role-badge--sales">SALES</span><span class="role-cred-tag">sales506</span></div>
                            <strong style="color:#fff; font-size:13px;">Sales Consultant</strong>
                            <small style="color:#9aa1ab; font-size:11px;">Prospek & test drive mobil</small>
                        </div>

                        <div class="role-card-item" style="padding:12px 14px;" onclick="selectRoleAndLogin('advisor506', 'advisor506', this)">
                            <div class="role-card-top"><span class="role-badge role-badge--service">SA</span><span class="role-cred-tag">advisor506</span></div>
                            <strong style="color:#fff; font-size:13px;">Service Advisor</strong>
                            <small style="color:#9aa1ab; font-size:11px;">Work Order bengkel & servis</small>
                        </div>

                        <div class="role-card-item" style="padding:12px 14px;" onclick="selectRoleAndLogin('mekanik506', 'mekanik506', this)">
                            <div class="role-card-top"><span class="role-badge role-badge--service">MEKANIK</span><span class="role-cred-tag">mekanik506</span></div>
                            <strong style="color:#fff; font-size:13px;">Mekanik / Teknisi</strong>
                            <small style="color:#9aa1ab; font-size:11px;">Pengerjaan servis terassign</small>
                        </div>

                        <div class="role-card-item" style="padding:12px 14px;" onclick="selectRoleAndLogin('finance506', 'finance506', this)">
                            <div class="role-card-top"><span class="role-badge role-badge--finance">FINANCE</span><span class="role-cred-tag">finance506</span></div>
                            <strong style="color:#fff; font-size:13px;">Finance / Kasir</strong>
                            <small style="color:#9aa1ab; font-size:11px;">Verifikasi pembayaran transfer</small>
                        </div>

                        <div class="role-card-item" style="padding:12px 14px;" onclick="selectRoleAndLogin('gudang506', 'gudang506', this)">
                            <div class="role-card-top"><span class="role-badge">GUDANG</span><span class="role-cred-tag">gudang506</span></div>
                            <strong style="color:#fff; font-size:13px;">Petugas Gudang</strong>
                            <small style="color:#9aa1ab; font-size:11px;">Inventori suku cadang</small>
                        </div>

                        <div class="role-card-item" style="padding:12px 14px;" onclick="selectRoleAndLogin('mekanik.andi', 'mekanik123', this)">
                            <div class="role-card-top"><span class="role-badge role-badge--service">MEKANIK</span><span class="role-cred-tag">mekanik.andi</span></div>
                            <strong style="color:#fff; font-size:13px;">Andi Pratama</strong>
                            <small style="color:#9aa1ab; font-size:11px;">Senior Mekanik Mesin (Engine &amp; Transmisi)</small>
                        </div>

                        <div class="role-card-item" style="padding:12px 14px;" onclick="selectRoleAndLogin('mekanik.budi', 'mekanik123', this)">
                            <div class="role-card-top"><span class="role-badge role-badge--service">MEKANIK</span><span class="role-cred-tag">mekanik.budi</span></div>
                            <strong style="color:#fff; font-size:13px;">Budi Santoso</strong>
                            <small style="color:#9aa1ab; font-size:11px;">Spesialis Kelistrikan &amp; AC</small>
                        </div>

                        <div class="role-card-item" style="padding:12px 14px;" onclick="selectRoleAndLogin('mekanik.dedi', 'mekanik123', this)">
                            <div class="role-card-top"><span class="role-badge role-badge--service">MEKANIK</span><span class="role-cred-tag">mekanik.dedi</span></div>
                            <strong style="color:#fff; font-size:13px;">Dedi Kurniawan</strong>
                            <small style="color:#9aa1ab; font-size:11px;">Spesialis Rem &amp; Kaki-kaki</small>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <div class="role-modal-footer">
            <span>🛡️ <strong>Single-Click:</strong> Klik kartu untuk langsung masuk tanpa mengetik.</span>
            <span style="font-size: 11px; color:#a0a6b2;">Database: <strong>yza_automotive</strong></span>
        </div>
    </div>
</div>

<script>
    // Password toggle
    const p = document.getElementById('password');
    const t = document.querySelector('.password-toggle');
    t?.addEventListener('click', () => {
        p.type = p.type === 'password' ? 'text' : 'password';
        t.setAttribute('aria-label', p.type === 'password' ? 'Tampilkan password' : 'Sembunyikan password');
    });

    // Modal Control
    const modal = document.getElementById('roleModalOverlay');
    const btnOpen = document.getElementById('btnOpenRoleModal');
    const btnClose = document.getElementById('btnCloseRoleModal');

    function openRoleModal() {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeRoleModal() {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    btnOpen?.addEventListener('click', openRoleModal);
    btnClose?.addEventListener('click', closeRoleModal);

    modal?.addEventListener('click', (e) => {
        if (e.target === modal) closeRoleModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal?.classList.contains('is-open')) {
            closeRoleModal();
        }
    });

    // Toggle Staff Roles Accordion
    const btnToggleStaff = document.getElementById('btnToggleStaffRoles');
    const staffContainer = document.getElementById('staffRolesContainer');
    const staffIcon = document.getElementById('staffToggleIcon');

    btnToggleStaff?.addEventListener('click', () => {
        const isHidden = staffContainer.style.display === 'none';
        staffContainer.style.display = isHidden ? 'block' : 'none';
        staffIcon.textContent = isHidden ? '▼' : '▶';
    });

    // 1-Click Role Login Action
    function selectRoleAndLogin(username, password, cardElement) {
        const idInput = document.getElementById('identity');
        const passInput = document.getElementById('password');
        const form = document.getElementById('loginForm');

        if (!idInput || !passInput || !form) return;

        // Visual selection
        document.querySelectorAll('.role-card-item').forEach(c => c.classList.remove('is-selected'));
        cardElement.classList.add('is-selected');

        const ctaBtn = cardElement.querySelector('.role-login-cta span');
        if (ctaBtn) ctaBtn.textContent = 'Memproses...';

        idInput.value = username;
        passInput.value = password;

        // Auto submit form
        setTimeout(() => {
            closeRoleModal();
            const submitBtn = document.getElementById('btnSubmitLogin');
            if (submitBtn) {
                submitBtn.innerHTML = '<span>MENGALIHKAN...</span>';
                submitBtn.disabled = true;
            }
            form.submit();
        }, 200);
    }
</script>
</body>
</html>
