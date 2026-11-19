@extends('layouts.app')
@php
    $roleLabel = \App\Support\AdminNavigation::roleLabel($role);
@endphp
@section('title', 'Dashboard ' . $roleLabel . ' · YZA Automotive')
@section('content')
<style>
.admin-page-heading { display: none; }
</style>
<section class="admin-command-center">
    <section class="admin-hero">
        <div class="admin-hero-copy">
            <span class="admin-eyebrow">YZA AUTOMOTIVE · {{ strtoupper($roleLabel) }}</span>
            <h1>Selamat Datang, <em>{{ $user->displayName() }}</em></h1>
            <p>Dashboard {{ $roleLabel }} dengan data operasional aktual sesuai tanggung jawab Anda.</p>
        </div>
        <div class="admin-hero-art" aria-hidden="true">
            <img src="{{ asset('assets/images/hero/yza-admin-sales-banner.jpg') }}" alt="">
            <span></span>
        </div>
    </section>

    <section class="admin-service-grid" aria-label="Ringkasan metrik">
        @foreach($metrics as $idx => $metric)
            <article class="admin-service-card">
                <div class="admin-service-card-top">
                    <span class="admin-metric-icon tone-red" aria-hidden="true">
                        @if($idx === 0)
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        @elseif($idx === 1)
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                        @else
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>
                        @endif
                    </span>
                    <span class="admin-metric-kicker">DATA AKTUAL</span>
                </div>
                <strong>{{ $metric['value'] }}</strong>
                <div>
                    <b>{{ $metric['label'] }}</b>
                    <span>{{ $metric['hint'] }}</span>
                </div>
            </article>
        @endforeach
    </section>

    <section class="admin-panel">
        <div class="admin-panel-heading">
            <div>
                <span class="admin-section-kicker">ROLE ACCESS</span>
                <h2>Ruang Kerja {{ $roleLabel }}</h2>
                <p>Menu yang tampil mengikuti hak akses akun Anda. Data operasional ditampilkan sesuai kondisi aktual.</p>
            </div>
            @if($role === 'car_wash_staff' && Route::has('admin.booking-cuci.index'))
                <a href="{{ route('admin.booking-cuci.index') }}" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:6px; padding:9px 16px; border-radius:10px; background:#ef233c; color:#fff; font-size:12.5px; font-weight:700; text-decoration:none;">
                    <span>Buka Antrean Cuci</span>
                    <span aria-hidden="true">&rarr;</span>
                </a>
            @endif
        </div>
        <div class="admin-empty-state">
            <span aria-hidden="true">✓</span>
            <div>
                <strong>Dashboard role-aware aktif</strong>
                <small>Gunakan navigasi di samping untuk membuka modul yang diizinkan.</small>
            </div>
        </div>
    </section>
</section>
@endsection
