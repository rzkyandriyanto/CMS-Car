@extends('layouts.app')

@section('title', 'Profil Member · YZA Automotive')

@section('content')
<style>
    .mp-wrap {
        max-width: 820px;
        margin: 28px auto 56px;
        padding: 0 20px;
        color: #0f172a;
    }
    .mp-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .mp-eyebrow {
        display: block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #ed172b;
        margin-bottom: 5px;
    }
    .mp-wrap .mp-title {
        margin: 0;
        font-size: 26px;
        font-weight: 800;
        letter-spacing: -0.025em;
        color: #0f172a;
        line-height: 1.2;
    }
    .mp-subtitle {
        margin: 4px 0 0;
        font-size: 13.5px;
        color: #64748b;
    }
    .mp-password-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 38px;
        padding: 0 15px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #334155;
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        transition: border-color 0.15s ease, background 0.15s ease, color 0.15s ease;
    }
    .mp-password-link:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        color: #0f172a;
    }
    .mp-alert {
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 18px;
    }
    .mp-alert.is-success {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }
    .mp-alert.is-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
    }
    .mp-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }
    .mp-identity {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
    }
    .mp-avatar {
        width: 48px;
        height: 48px;
        border-radius: 999px;
        background: linear-gradient(135deg, #ed172b, #be123c);
        color: #ffffff;
        display: grid;
        place-items: center;
        font-size: 18px;
        font-weight: 800;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(237, 23, 43, 0.22);
    }
    .mp-identity h2 {
        margin: 0;
        font-size: 16.5px;
        font-weight: 700;
        color: #0f172a;
    }
    .mp-identity p {
        margin: 2px 0 0;
        font-size: 12.5px;
        color: #64748b;
    }
    .mp-badge {
        margin-left: auto;
        padding: 5px 11px;
        border-radius: 999px;
        background: #fff1f2;
        border: 1px solid #fecdd3;
        color: #be123c;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.06em;
    }
    .mp-form {
        padding: 24px;
    }
    .mp-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    @media (max-width: 640px) {
        .mp-grid { grid-template-columns: 1fr; }
        .mp-badge { display: none; }
    }
    .mp-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .mp-field.is-full {
        grid-column: 1 / -1;
    }
    .mp-field label {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
    }
    .mp-card .mp-input,
    .mp-card .mp-select,
    .mp-card .mp-textarea {
        width: 100%;
        padding: 10px 13px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #0f172a;
        font-size: 13.5px;
        box-sizing: border-box;
        transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
    }
    .mp-card .mp-input:focus,
    .mp-card .mp-select:focus,
    .mp-card .mp-textarea:focus {
        outline: none;
        background: #ffffff;
        border-color: #ed172b;
        box-shadow: 0 0 0 3px rgba(237, 23, 43, 0.1);
    }
    .mp-card .mp-textarea {
        min-height: 90px;
        resize: vertical;
    }
    .mp-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid #f1f5f9;
    }
    .mp-submit {
        height: 40px;
        padding: 0 22px;
        border-radius: 10px;
        border: none;
        background: #ed172b;
        color: #ffffff;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(237, 23, 43, 0.22);
        transition: background 0.15s ease, transform 0.15s ease;
    }
    .mp-submit:hover {
        background: #d11124;
        transform: translateY(-1px);
    }
</style>

<div class="mp-wrap">
    <header class="mp-header">
        <div>
            <span class="mp-eyebrow">Akun Member</span>
            <h1 class="mp-title">Profil Saya</h1>
            <p class="mp-subtitle">Kelola informasi akun dan data diri YZA Member Anda.</p>
        </div>
        @if(Route::has('account.password'))
            <a href="{{ route('account.password') }}" class="mp-password-link">Ubah Password</a>
        @endif
    </header>

    @if(session('success'))
        <div class="mp-alert is-success" role="alert">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mp-alert is-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <section class="mp-card">
        <div class="mp-identity">
            <span class="mp-avatar">{{ strtoupper(substr($item->nama_lengkap, 0, 1)) }}</span>
            <div>
                <h2>{{ $item->nama_lengkap }}</h2>
                <p>{{ $item->user?->email ?: 'Email belum diisi' }}</p>
            </div>
            <span class="mp-badge">YZA MEMBER</span>
        </div>

        <form class="mp-form" method="post" action="{{ route('pelanggan.profile.update') }}">
            @csrf
            @method('PUT')
            <div class="mp-grid">
                <div class="mp-field">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input id="nama_lengkap" class="mp-input" name="nama_lengkap" value="{{ old('nama_lengkap', $item->nama_lengkap) }}" required>
                </div>
                <div class="mp-field">
                    <label for="no_hp">No. HP / WhatsApp</label>
                    <input id="no_hp" class="mp-input" name="no_hp" value="{{ old('no_hp', $item->no_hp) }}" required>
                </div>
                <div class="mp-field">
                    <label for="email">Email</label>
                    <input id="email" class="mp-input" type="email" name="email" value="{{ old('email', $item->user?->email) }}">
                </div>
                <div class="mp-field">
                    <label for="jenis_kelamin">Jenis Kelamin</label>
                    <select id="jenis_kelamin" class="mp-select" name="jenis_kelamin">
                        <option value="">-</option>
                        <option value="L" @selected(old('jenis_kelamin', $item->jenis_kelamin) === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('jenis_kelamin', $item->jenis_kelamin) === 'P')>Perempuan</option>
                    </select>
                </div>
                <div class="mp-field">
                    <label for="tanggal_lahir">Tanggal Lahir</label>
                    <input id="tanggal_lahir" class="mp-input" type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', optional($item->tanggal_lahir)->format('Y-m-d')) }}">
                </div>
                <div class="mp-field is-full">
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" class="mp-textarea" name="alamat" placeholder="Masukkan alamat lengkap Anda...">{{ old('alamat', $item->alamat) }}</textarea>
                </div>
            </div>
            <div class="mp-actions">
                <button type="submit" class="mp-submit">Simpan Perubahan</button>
            </div>
        </form>
    </section>
</div>
@endsection
