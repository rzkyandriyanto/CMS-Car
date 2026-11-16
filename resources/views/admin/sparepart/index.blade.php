@extends('layouts.app')

@section('title', 'Master Sparepart & Inventori · Admin YZA Automotive')
@section('admin-page-title', 'Sparepart & Inventori')

@section('content')
<div class="admin-sparepart-page">
    <style>
        .admin-content:has(> .admin-sparepart-page) > .admin-page-heading {
            display: none !important;
        }

        .admin-sparepart-page {
            max-width: 1480px;
            margin: 0 auto;
            padding: 4px 0 28px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        /* Header */
        .sparepart-header {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(340px, 420px);
            align-items: center;
            gap: 20px;
        }
        .sparepart-header-left {
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .sparepart-title-group {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .sparepart-title-icon {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            background: #ED172B;
            color: #FFFFFF;
            display: grid;
            place-items: center;
            box-shadow: 0 4px 12px rgba(237, 23, 43, 0.2);
            flex-shrink: 0;
        }
        .sparepart-heading {
            margin: 0;
            font-size: 25px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.025em;
            line-height: 1.2;
        }
        .sparepart-subheading {
            margin: 4px 0 0;
            font-size: 13px;
            color: #64748B;
            line-height: 1.45;
            max-width: 560px;
        }

        /* Header Banner */
        .sparepart-header-right {
            min-width: 0;
        }
        .sparepart-banner {
            position: relative;
            height: 104px;
            border-radius: 14px;
            overflow: hidden;
            background: #0B1018;
            border: 1px solid rgba(15, 23, 42, 0.08);
            display: flex;
            align-items: center;
        }
        .sparepart-banner-img {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 62%;
            object-fit: cover;
            object-position: right center;
            opacity: 0.85;
        }
        .sparepart-banner-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, #0B1018 38%, rgba(11, 16, 24, 0.72) 68%, transparent 100%);
        }
        .sparepart-banner-content {
            position: relative;
            z-index: 2;
            padding-left: 22px;
            display: flex;
            flex-direction: column;
        }
        .banner-tagline-top {
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: 0.12em;
            color: #FFFFFF;
        }
        .banner-tagline-bottom {
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: 0.08em;
            color: #CBD5E1;
            margin-top: 2px;
        }
        .banner-accent-line {
            width: 28px;
            height: 2.5px;
            background: #ED172B;
            border-radius: 2px;
            margin-top: 7px;
        }

        /* Alerts */
        .admin-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
        }
        .admin-alert.is-success {
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
            color: #065F46;
        }

        /* KPI Strip - Minimalist White Cards with Subtle Accent Icon */
        .sparepart-kpi-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 10px;
        }
        .sparepart-kpi-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 13px 14px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 10px;
            text-decoration: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;
            box-sizing: border-box;
        }
        .sparepart-kpi-card:hover {
            transform: translateY(-1px);
            border-color: #CBD5E1;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        }
        .kpi-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .kpi-icon-box {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .sparepart-kpi-card.is-red .kpi-icon-box { background: #FEF2F2; color: #E11D48; }
        .sparepart-kpi-card.is-green .kpi-icon-box { background: #ECFDF5; color: #059669; }
        .sparepart-kpi-card.is-amber .kpi-icon-box { background: #FFFBEB; color: #D97706; }
        .sparepart-kpi-card.is-blue .kpi-icon-box { background: #EFF6FF; color: #2563EB; }
        .sparepart-kpi-card.is-purple .kpi-icon-box { background: #FAF5FF; color: #9333EA; }
        .sparepart-kpi-card.is-dark .kpi-icon-box { background: #F1F5F9; color: #475569; }

        .kpi-content {
            display: flex;
            flex-direction: column;
        }
        .kpi-label {
            font-size: 11.5px;
            font-weight: 600;
            color: #64748B;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .kpi-value {
            font-size: 21px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.02em;
            line-height: 1.15;
            font-variant-numeric: tabular-nums;
        }
        .kpi-subtext {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 500;
            color: #94A3B8;
            margin-top: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Content Grid */
        .sparepart-content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 292px;
            gap: 16px;
            align-items: start;
        }

        /* Main Container Card */
        .sparepart-main-card {
            min-width: 0;
            background: #FFFFFF;
            border-radius: 16px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .sparepart-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }
        .card-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .card-header-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #FFF1F2;
            color: #ED172B;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .card-header-title {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            color: #0F172A;
            letter-spacing: -0.015em;
        }
        .card-header-subtitle {
            margin: 2px 0 0;
            font-size: 12.5px;
            color: #64748B;
        }
        .header-action-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .btn-action-primary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 14px;
            border-radius: 9px;
            background: #ED172B;
            color: #FFFFFF;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.15s ease;
            border: none;
            cursor: pointer;
        }
        .btn-action-primary:hover {
            background: #D91325;
            color: #FFFFFF;
        }
        .btn-action-secondary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 13px;
            border-radius: 9px;
            background: #FFFFFF;
            color: #334155;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid #E2E8F0;
            transition: all 0.15s ease;
        }
        .btn-action-secondary:hover {
            background: #F8FAFC;
            border-color: #CBD5E1;
            color: #0F172A;
        }

        /* Filter Toolbar - Responsive Wrap so labels never truncate */
        .sparepart-filter-toolbar {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            width: 100%;
            padding-top: 2px;
        }
        .filter-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .filter-wrap.search-box {
            flex: 1.8 1 220px;
            min-width: 200px;
        }
        .filter-wrap.search-box svg {
            position: absolute;
            left: 11px;
            color: #94A3B8;
            pointer-events: none;
        }
        .filter-wrap.search-box .filter-input {
            padding-left: 34px;
        }
        .filter-wrap.select-box {
            flex: 1 1 142px;
            min-width: 138px;
        }
        .select-chevron {
            position: absolute;
            right: 10px;
            color: #94A3B8;
            pointer-events: none;
        }
        .filter-input, .filter-select {
            width: 100%;
            height: 36px;
            padding: 0 28px 0 11px;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            background: #F8FAFC;
            color: #0F172A;
            font-size: 12.5px;
            box-sizing: border-box;
            appearance: none;
            transition: border-color 0.15s ease, background 0.15s ease;
        }
        .filter-input {
            padding-right: 11px;
        }
        .filter-input:focus, .filter-select:focus {
            outline: none;
            background: #FFFFFF;
            border-color: #94A3B8;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.05);
        }
        .btn-filter-submit {
            height: 36px;
            padding: 0 16px;
            border-radius: 8px;
            background: #0F172A;
            color: #FFFFFF;
            font-size: 12.5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            flex-shrink: 0;
            transition: background 0.15s ease;
        }
        .btn-filter-submit:hover { background: #1E293B; }
        .btn-filter-reset {
            height: 36px;
            padding: 0 13px;
            border-radius: 8px;
            background: #FFFFFF;
            color: #64748B;
            font-size: 12.5px;
            font-weight: 600;
            border: 1px solid #E2E8F0;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
        }
        .btn-filter-reset:hover { background: #F8FAFC; color: #0F172A; }

        /* Table Wrapper */
        .sparepart-table-wrapper {
            overflow-x: auto;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
        }
        .sparepart-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 12.5px;
            text-align: left;
        }
        .sparepart-table thead tr th {
            background: #F8FAFC;
            color: #64748B;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 11px 12px;
            border-bottom: 1px solid #E2E8F0;
            white-space: nowrap;
        }
        .sparepart-table tbody tr {
            transition: background 0.12s ease;
        }
        .sparepart-table tbody tr:hover {
            background: #F8FAFC;
        }
        .sparepart-table tbody tr td {
            padding: 11px 12px;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
            color: #1E293B;
        }
        .sparepart-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Thumb Image */
        .part-thumb-wrap {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            overflow: hidden;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .part-thumb-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Badges */
        .badge-sku {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 11.5px;
            font-weight: 600;
            color: #334155;
            background: #F1F5F9;
            padding: 3px 7px;
            border-radius: 6px;
            display: inline-block;
            white-space: nowrap;
        }
        .badge-category {
            font-size: 11.5px;
            font-weight: 600;
            color: #475569;
            background: #F1F5F9;
            padding: 3px 9px;
            border-radius: 999px;
            display: inline-block;
            white-space: nowrap;
        }
        .badge-status {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            padding: 3px 8px;
            border-radius: 999px;
            display: inline-block;
        }
        .badge-status.is-active { background: #ECFDF5; color: #047857; }
        .badge-status.is-inactive { background: #F1F5F9; color: #64748B; }

        .stock-indicator {
            display: flex;
            flex-direction: column;
            gap: 2px;
            white-space: nowrap;
        }
        .stock-num {
            font-weight: 700;
            font-size: 13px;
            color: #0F172A;
            font-variant-numeric: tabular-nums;
        }
        .stock-tag {
            font-size: 10.5px;
            font-weight: 600;
            padding: 1px 6px;
            border-radius: 4px;
            display: inline-block;
            width: fit-content;
        }
        .stock-tag.is-aman { background: #ECFDF5; color: #047857; }
        .stock-tag.is-menipis { background: #FFFBEB; color: #B45309; }
        .stock-tag.is-habis { background: #FEF2F2; color: #B91C1C; }

        /* Action Buttons */
        .table-actions {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .btn-table-action {
            padding: 4px 9px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: 1px solid #E2E8F0;
            background: #FFFFFF;
            color: #334155;
            transition: all 0.12s ease;
            cursor: pointer;
            white-space: nowrap;
        }
        .btn-table-action:hover {
            background: #F8FAFC;
            color: #0F172A;
            border-color: #CBD5E1;
        }
        .btn-table-action.is-danger {
            color: #DC2626;
            border-color: #FECACA;
        }
        .btn-table-action.is-danger:hover {
            background: #FEF2F2;
            color: #B91C1C;
        }

        /* Pagination */
        .sparepart-pagination {
            display: flex;
            justify-content: flex-end;
            min-height: 32px;
            margin-top: 4px;
        }
        .sparepart-pagination nav { display: flex; align-items: center; }
        .sparepart-pagination .pagination {
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .sparepart-pagination .page-item { margin: 0; }
        .sparepart-pagination .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
            height: 30px;
            padding: 0 8px;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            background: #FFFFFF;
            color: #475569;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            line-height: 1;
        }
        .sparepart-pagination .page-item.active .page-link {
            border-color: #ED172B;
            background: #ED172B;
            color: #FFFFFF;
        }
        .sparepart-pagination .page-item.disabled .page-link {
            background: #F8FAFC;
            color: #94A3B8;
            cursor: not-allowed;
        }
        .sparepart-pagination .page-link:hover {
            border-color: #CBD5E1;
            color: #0F172A;
        }
        .sparepart-pagination .page-item.active .page-link:hover { color: #FFFFFF; }
        .sparepart-pagination svg {
            width: 15px !important;
            height: 15px !important;
            display: block;
        }

        /* Analytics Sidebar */
        .sparepart-analytics {
            display: grid;
            gap: 16px;
            min-width: 0;
        }
        .sparepart-analytics-card {
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            background: #FFFFFF;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
            overflow: hidden;
        }
        .analytics-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 16px 13px;
            border-bottom: 1px solid #F1F5F9;
        }
        .analytics-icon {
            display: grid;
            width: 32px;
            height: 32px;
            place-items: center;
            border-radius: 9px;
            color: #ED172B;
            background: #FFF1F2;
            flex-shrink: 0;
        }
        .analytics-heading h2 {
            margin: 0;
            color: #0F172A;
            font-size: 14.5px;
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        .analytics-heading p {
            margin: 2px 0 0;
            color: #64748B;
            font-size: 11px;
        }
        .analytics-heading > span {
            margin-left: auto;
            color: #ED172B;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }
        .category-list {
            padding: 10px 16px 14px;
        }
        .category-row {
            padding: 8px 0;
            border-bottom: 1px solid #F1F5F9;
        }
        .category-row:last-child { border-bottom: 0; }
        .category-row-top {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            color: #1E293B;
            font-size: 12px;
            font-weight: 600;
        }
        .category-row-top strong {
            color: #475569;
            font-size: 11.5px;
            font-variant-numeric: tabular-nums;
        }
        .category-track {
            height: 5px;
            margin: 6px 0 4px;
            overflow: hidden;
            border-radius: 999px;
            background: #F1F5F9;
        }
        .category-track i {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: #ED172B;
        }
        .category-row small {
            color: #64748B;
            font-size: 11px;
        }
        .analytics-empty {
            padding: 20px 0;
            color: #64748B;
            font-size: 12px;
            text-align: center;
        }

        .operational-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 14px 16px 10px;
        }
        .operational-tile {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 10px;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            background: #FFFFFF;
        }
        .operational-tile > span {
            display: grid;
            width: 30px;
            height: 30px;
            place-items: center;
            border-radius: 8px;
            flex-shrink: 0;
        }
        .operational-tile strong, .operational-tile small { display: block; }
        .operational-tile strong {
            color: #0F172A;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.15;
            font-variant-numeric: tabular-nums;
        }
        .operational-tile small {
            margin-top: 2px;
            color: #64748B;
            font-size: 10.5px;
            line-height: 1.25;
        }
        .operational-tile.is-green > span { color: #059669; background: #ECFDF5; }
        .operational-tile.is-blue > span { color: #2563EB; background: #EFF6FF; }
        .operational-tile.is-red > span { color: #DC2626; background: #FEF2F2; }
        .operational-tile.is-purple > span { color: #7C3AED; background: #FAF5FF; }
        .analytics-note {
            margin: 0 16px 14px;
            padding: 9px 11px;
            border-radius: 8px;
            color: #64748B;
            background: #F8FAFC;
            font-size: 11px;
            line-height: 1.45;
        }

        /* Responsive */
        @media (max-width: 1360px) {
            .sparepart-kpi-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }
        @media (max-width: 1180px) {
            .sparepart-content-grid { grid-template-columns: 1fr; }
            .sparepart-analytics { grid-template-columns: 1fr 1fr; }
            .sparepart-header { grid-template-columns: 1fr; }
        }
        @media (max-width: 720px) {
            .sparepart-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .sparepart-analytics { grid-template-columns: 1fr; }
            .sparepart-heading { font-size: 22px; }
            .sparepart-main-card { padding: 16px; }
            .header-action-group { width: 100%; }
            .header-action-group a { flex: 1; justify-content: center; }
        }
    </style>

    {{-- Header --}}
    <header class="sparepart-header">
        <div class="sparepart-header-left">
            <div class="sparepart-title-group">
                <div class="sparepart-title-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                        <polyline points="2 17 12 22 22 17"></polyline>
                        <polyline points="2 12 12 17 22 12"></polyline>
                    </svg>
                </div>
                <div>
                    <h1 class="sparepart-heading">Master Sparepart & Inventori</h1>
                    <p class="sparepart-subheading">Kelola katalog suku cadang, pantau ketersediaan stok fisik, penerimaan barang, dan integrasi dengan work order bengkel.</p>
                </div>
            </div>
        </div>

        <div class="sparepart-header-right">
            <div class="sparepart-banner">
                <img src="{{ asset('assets/images/hero/yza-admin-sales-banner.jpg') }}" alt="YZA Workshop & Spareparts" class="sparepart-banner-img" loading="eager">
                <div class="sparepart-banner-overlay"></div>
                <div class="sparepart-banner-content">
                    <span class="banner-tagline-top">INVENTORI BENGKEL RESMI</span>
                    <span class="banner-tagline-bottom">SUKU CADANG & PERLENGKAPAN</span>
                    <div class="banner-accent-line"></div>
                </div>
            </div>
        </div>
    </header>

    @if(session('success'))
        <div class="admin-alert is-success" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- 6 KPI Cards --}}
    <section class="sparepart-kpi-grid" aria-label="Statistik Sparepart & Stok">
        {{-- Card 1: Total Jenis Sparepart --}}
        <a class="sparepart-kpi-card is-red" href="{{ route('admin.sparepart.index') }}">
            <div class="kpi-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Total Jenis Sparepart</span>
                <strong class="kpi-value">{{ number_format($stats['total_jenis'] ?? 0) }}</strong>
                <div class="kpi-subtext">
                    <span>Katalog suku cadang</span>
                </div>
            </div>
        </a>

        {{-- Card 2: Total Stok Tersedia --}}
        <a class="sparepart-kpi-card is-green" href="{{ route('admin.inventory.stock') }}">
            <div class="kpi-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Total Stok Fisik</span>
                <strong class="kpi-value">{{ number_format($stats['total_stok'] ?? 0) }}</strong>
                <div class="kpi-subtext">
                    <span>Unit barang siap pakai</span>
                </div>
            </div>
        </a>

        {{-- Card 3: Stok Menipis --}}
        <a class="sparepart-kpi-card is-amber" href="{{ route('admin.sparepart.index', ['stok' => 'menipis']) }}">
            <div class="kpi-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Stok Menipis</span>
                <strong class="kpi-value">{{ number_format($stats['stok_menipis'] ?? 0) }}</strong>
                <div class="kpi-subtext" style="color: #D97706;">
                    <span>&le; batas minimum stok</span>
                </div>
            </div>
        </a>

        {{-- Card 4: Stok Habis --}}
        <a class="sparepart-kpi-card is-red" href="{{ route('admin.sparepart.index', ['stok' => 'habis']) }}">
            <div class="kpi-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Stok Habis</span>
                <strong class="kpi-value">{{ number_format($stats['stok_habis'] ?? 0) }}</strong>
                <div class="kpi-subtext" style="color: #DC2626;">
                    <span>Stok = 0 (perlu restock)</span>
                </div>
            </div>
        </a>

        {{-- Card 5: Barang Masuk --}}
        <a class="sparepart-kpi-card is-blue" href="{{ route('admin.stok-masuk.index') }}">
            <div class="kpi-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Barang Masuk</span>
                <strong class="kpi-value">{{ number_format($stats['barang_masuk'] ?? 0) }}</strong>
                <div class="kpi-subtext">
                    <span>Total penerimaan stok</span>
                </div>
            </div>
        </a>

        {{-- Card 6: Barang Dipakai Servis --}}
        <a class="sparepart-kpi-card is-purple" href="{{ route('admin.inventory.history') }}">
            <div class="kpi-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Dipakai Servis</span>
                <strong class="kpi-value">{{ number_format($stats['barang_servis'] ?? 0) }}</strong>
                <div class="kpi-subtext">
                    <span>Suku cadang terpasang</span>
                </div>
            </div>
        </a>

        <a class="sparepart-kpi-card is-amber" href="{{ route('admin.sparepart.index') }}">
            <div class="kpi-icon-box">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Perlu Review Fitment</span>
                <strong class="kpi-value">{{ number_format($stats['fitment_review'] ?? 0) }}</strong>
                <div class="kpi-subtext" style="color:#D97706;"><span>Belum punya fit terverifikasi</span></div>
            </div>
        </a>
    </section>

    {{-- Main Container Card --}}
    <div class="sparepart-content-grid">
    <main class="sparepart-main-card">
        {{-- Card Header --}}
        <div class="sparepart-card-header">
            <div class="card-header-left">
                <div class="card-header-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16.5 9.4 7.55 4.24a1.79 1.79 0 0 0-2.5 1.48v12.56a1.79 1.79 0 0 0 2.5 1.48l8.95-5.16a1.8 1.8 0 0 0 0-3.16Z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="card-header-title">Daftar Suku Cadang & Perlengkapan</h2>
                    <p class="card-header-subtitle">Inventori resmi sparepart bengkel YZA Automotive</p>
                </div>
            </div>

            <div class="header-action-group">
                <a class="btn-action-primary" href="{{ route('admin.sparepart.create') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Tambah Sparepart</span>
                </a>
                <a class="btn-action-secondary" href="{{ route('admin.stok-masuk.create') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path></svg>
                    <span>Stok Masuk</span>
                </a>
                <a class="btn-action-secondary" href="{{ route('admin.inventory.adjustment') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    <span>Penyesuaian</span>
                </a>
                <a class="btn-action-secondary" href="{{ route('admin.inventory.history') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <span>Riwayat Stok</span>
                </a>
            </div>
        </div>

        {{-- Filter Toolbar --}}
        <form class="sparepart-filter-toolbar" method="get" action="{{ route('admin.sparepart.index') }}">
            <div class="filter-wrap search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" name="q" value="{{ request('q') }}" class="filter-input" placeholder="Cari kode SKU, nama barang, atau merk...">
            </div>

            <div class="filter-wrap select-box">
                <select name="kategori" class="filter-select">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id_kategori }}" @selected(request('kategori') == $c->id_kategori)>{{ $c->nama_kategori }}</option>
                    @endforeach
                </select>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="select-chevron"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>

            <div class="filter-wrap select-box">
                <select name="stok" class="filter-select">
                    <option value="">Semua Status Stok</option>
                    <option value="aman" @selected(request('stok') === 'aman')>Aman (> Minimum)</option>
                    <option value="menipis" @selected(request('stok') === 'menipis')>Menipis (&le; Minimum)</option>
                    <option value="habis" @selected(request('stok') === 'habis')>Habis (0)</option>
                </select>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="select-chevron"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>

            <div class="filter-wrap select-box">
                <select name="status" class="filter-select">
                    <option value="">Semua Status Master</option>
                    <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                </select>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="select-chevron"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>

            <div class="filter-wrap select-box">
                <select name="compatibility" class="filter-select">
                    <option value="">Semua Kompatibilitas</option>
                    <option value="with_fitment" @selected(request('compatibility') === 'with_fitment')>Memiliki Fitment</option>
                    <option value="without_fitment" @selected(request('compatibility') === 'without_fitment')>Belum Memiliki Fitment</option>
                </select>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="select-chevron"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>

            <button type="submit" class="btn-filter-submit">Filter</button>
            @if(request()->hasAny(['q', 'kategori', 'stok', 'status', 'compatibility']))
                <a href="{{ route('admin.sparepart.index') }}" class="btn-filter-reset">Reset</a>
            @endif
        </form>

        {{-- Table --}}
        <div class="sparepart-table-wrapper">
            <table class="sparepart-table">
                <thead>
                    <tr>
                        <th style="width: 55px;">Foto</th>
                        <th>Kode SKU</th>
                        <th>Nama Barang & Merk</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Stok Fisik</th>
                        <th>Minimum</th>
                        <th>Status</th>
                        <th style="text-align: right; width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>
                                <div class="part-thumb-wrap">
                                    @if($item->photoUrl())
                                        <img src="{{ $item->photoUrl() }}" alt="{{ $item->nama_sparepart }}" class="part-thumb-img">
                                    @else
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge-sku">{{ $item->kode_sparepart }}</span>
                            </td>
                            <td>
                                <div>
                                    <strong style="color: #0F172A; font-size: 13.5px; display: block;">{{ $item->nama_sparepart }}</strong>
                                    <span style="color: #64748B; font-size: 12px;">Merk: {{ $item->merk ?: '—' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge-category">{{ $item->kategori->nama_kategori }}</span>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: #475569;">{{ $item->satuan }}</span>
                            </td>
                            <td>
                                <span style="color: #64748B; font-size: 12.5px;">Rp {{ number_format((float)$item->harga_beli, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                <strong style="color: #0F172A; font-size: 13px;">Rp {{ number_format((float)$item->harga_jual, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                <div class="stock-indicator">
                                    <span class="stock-num">{{ $item->stok }} {{ $item->satuan }}</span>
                                    <span class="stock-tag is-{{ $item->stockLevel() }}">
                                        {{ ucfirst($item->stockLevel()) }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span style="color: #64748B; font-weight: 600;">{{ $item->stok_minimum }} {{ $item->satuan }}</span>
                            </td>
                            <td>
                                <span class="badge-status {{ $item->status === 'aktif' ? 'is-active' : 'is-inactive' }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                @php
                                    $itemPayload = [
                                        'id' => $item->id_sparepart,
                                        'kode_sparepart' => $item->kode_sparepart,
                                        'nama_sparepart' => $item->nama_sparepart,
                                        'merk' => $item->merk ?? '',
                                        'id_kategori' => $item->id_kategori,
                                        'kategori_nama' => $item->kategori?->nama_kategori ?? '—',
                                        'id_supplier' => $item->id_supplier,
                                        'supplier_nama' => $item->supplier?->nama_supplier ?? '—',
                                        'nomor_part_oem' => $item->nomor_part_oem ?? '',
                                        'nomor_part_aftermarket' => $item->nomor_part_aftermarket ?? '',
                                        'deskripsi_spesifikasi' => $item->deskripsi_spesifikasi ?? '',
                                        'harga_beli' => (float) $item->harga_beli,
                                        'harga_jual' => (float) $item->harga_jual,
                                        'stok' => (int) $item->stok,
                                        'stok_minimum' => (int) $item->stok_minimum,
                                        'satuan' => $item->satuan,
                                        'status' => $item->status,
                                        'stock_level' => $item->stockLevel(),
                                        'photo_url' => $item->photoUrl(),
                                        'update_url' => route('admin.sparepart.update', $item),
                                        'show_url' => route('admin.sparepart.show', $item),
                                    ];
                                @endphp
                                <script type="application/json" id="sparepart-data-{{ $item->id_sparepart }}">@json($itemPayload)</script>
                                <div class="table-actions" style="justify-content: flex-end;">
                                    <button type="button" class="btn-table-action" style="color:#059669; border-color:#A7F3D0; background:#ECFDF5;" onclick="openSparepartModal({{ $item->id_sparepart }}, 'stock')" title="Tambah Stok">
                                        + Stok
                                    </button>
                                    <button type="button" class="btn-table-action" onclick="openSparepartModal({{ $item->id_sparepart }}, 'detail')" title="Detail">
                                        Detail
                                    </button>
                                    <button type="button" class="btn-table-action" onclick="openSparepartModal({{ $item->id_sparepart }}, 'edit')" title="Edit">
                                        Edit
                                    </button>
                                    <form method="post" action="{{ route('admin.sparepart.toggle', $item) }}" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-table-action {{ $item->status === 'aktif' ? 'is-danger' : '' }}" onclick="return confirm('Apakah Anda yakin ingin mengubah status suku cadang ini?')">
                                            {{ $item->status === 'aktif' ? 'Nonaktif' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="text-align: center; padding: 40px 20px; color: #64748B;">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.5" style="margin-bottom: 8px;"><circle cx="12" cy="12" r="10"></circle><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                <p style="margin: 0; font-weight: 600;">Belum ada data suku cadang yang sesuai.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="sparepart-pagination">
            {{ $items->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
    </main>
    <aside class="sparepart-analytics">
        <section class="sparepart-analytics-card">
            <div class="analytics-heading">
                <div class="analytics-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                </div>
                <div>
                    <h2>Distribusi Kategori</h2>
                    <p>Jumlah SKU aktif berdasarkan kategori.</p>
                </div>
                <span>Lihat Semua</span>
            </div>
            <div class="category-list">
                @forelse($categoryDistribution as $category)
                    @php($categoryPercent = round(($category->active_sparepart_count / $categoryTotal) * 100))
                    <div class="category-row"><div class="category-row-top"><span>{{ $category->nama_kategori }}</span><strong>{{ $categoryPercent }}%</strong></div><div class="category-track"><i style="width:{{ $categoryPercent }}%"></i></div><small>{{ $category->active_sparepart_count }} SKU aktif</small></div>
                @empty
                    <div class="analytics-empty">Belum ada kategori aktif.</div>
                @endforelse
            </div>
        </section>
        <section class="sparepart-analytics-card">
            <div class="analytics-heading">
                <div class="analytics-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
                <div>
                    <h2>Ringkasan Operasional</h2>
                    <p>Informasi aktual dari aktivitas inventori.</p>
                </div>
            </div>
            <div class="operational-grid">
                <div class="operational-tile is-green">
                    <span><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 11 12 6 7 11"></polyline><line x1="12" y1="18" x2="12" y2="6"></line></svg></span>
                    <div><strong>{{ number_format($operational['restock_today']) }}</strong><small>Restock hari ini</small></div>
                </div>
                <div class="operational-tile is-blue">
                    <span><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="7 13 12 18 17 13"></polyline><line x1="12" y1="6" x2="12" y2="18"></line></svg></span>
                    <div><strong>{{ number_format($operational['goods_received']) }}</strong><small>Barang masuk</small></div>
                </div>
                <div class="operational-tile is-red">
                    <span><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg></span>
                    <div><strong>{{ number_format($operational['critical_stock']) }}</strong><small>Stok kritis</small></div>
                </div>
                <div class="operational-tile is-purple">
                    <span><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg></span>
                    <div><strong>{{ number_format($operational['work_order_usage']) }}</strong><small>WO memakai sparepart</small></div>
                </div>
            </div>
            <p class="analytics-note">Angka mengikuti riwayat stok dan status master aktual. Tidak ada perubahan stok yang dilakukan saat halaman dibuka.</p>
        </section>
    </aside>
    </div>
</div>

{{-- Modal Detail & Edit Sparepart --}}
<div id="sparepartModalBackdrop" class="sp-modal-backdrop" hidden onclick="if(event.target===this) closeSparepartModal()">
    <div class="sp-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="spModalTitle">
        <div class="sp-modal-header">
            <div>
                <div id="spModalKicker" class="sp-modal-kicker">MASTER SPAREPART</div>
                <h3 id="spModalTitle" class="sp-modal-title">Detail Sparepart</h3>
            </div>
            <button type="button" class="sp-modal-close" onclick="closeSparepartModal()" aria-label="Tutup">&times;</button>
        </div>
        <div id="spModalBody" class="sp-modal-body"></div>
        <div id="spModalFooter" class="sp-modal-footer"></div>
    </div>
</div>

<style>
    .sp-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 1050;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(2px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .sp-modal-backdrop[hidden] { display: none !important; }
    .sp-modal-dialog {
        width: 100%;
        max-width: 680px;
        max-height: 90vh;
        background: #FFFFFF;
        border-radius: 16px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.2);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .sp-modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 22px;
        border-bottom: 1px solid #F1F5F9;
    }
    .sp-modal-kicker {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        color: #ED172B;
        text-transform: uppercase;
    }
    .sp-modal-title {
        margin: 3px 0 0;
        font-size: 18px;
        font-weight: 800;
        color: #0F172A;
    }
    .sp-modal-close {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
        color: #64748B;
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
    }
    .sp-modal-close:hover { background: #F8FAFC; color: #0F172A; }
    .sp-modal-body {
        padding: 20px 22px;
        overflow-y: auto;
        flex: 1;
    }
    .sp-modal-footer {
        padding: 14px 22px;
        border-top: 1px solid #F1F5F9;
        background: #F8FAFC;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
    }
    .sp-detail-hero {
        display: flex;
        align-items: center;
        gap: 14px;
        padding-bottom: 16px;
        border-bottom: 1px solid #F1F5F9;
        margin-bottom: 16px;
    }
    .sp-detail-thumb {
        width: 64px;
        height: 64px;
        border-radius: 12px;
        border: 1px solid #E2E8F0;
        background: #F8FAFC;
        overflow: hidden;
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }
    .sp-detail-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .sp-detail-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }
    @media (max-width: 600px) { .sp-detail-stats { grid-template-columns: repeat(2, 1fr); } }
    .sp-stat-box {
        padding: 10px 12px;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        background: #F8FAFC;
    }
    .sp-stat-box small { display: block; font-size: 11px; color: #64748B; font-weight: 600; }
    .sp-stat-box strong { display: block; margin-top: 3px; font-size: 14.5px; color: #0F172A; font-weight: 800; }
    .sp-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px 18px;
        font-size: 13px;
    }
    .sp-detail-item small { display: block; font-size: 11px; color: #64748B; font-weight: 600; margin-bottom: 2px; }
    .sp-detail-item span { color: #0F172A; font-weight: 600; }
    .sp-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }
    @media (max-width: 600px) { .sp-form-grid { grid-template-columns: 1fr; } }
    .sp-form-group { display: flex; flex-direction: column; gap: 5px; }
    .sp-form-group.is-full { grid-column: 1 / -1; }
    .sp-form-group label { font-size: 11.5px; font-weight: 700; color: #475569; }
    .sp-form-input, .sp-form-select, .sp-form-textarea {
        width: 100%;
        padding: 8px 11px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        font-size: 13px;
        color: #0F172A;
        background: #FFFFFF;
        box-sizing: border-box;
    }
    .sp-form-input:focus, .sp-form-select:focus, .sp-form-textarea:focus {
        outline: none;
        border-color: #ED172B;
        box-shadow: 0 0 0 3px rgba(237, 23, 43, 0.1);
    }
    .sp-form-error {
        margin-top: 10px;
        padding: 10px 12px;
        border-radius: 8px;
        background: #FEF2F2;
        border: 1px solid #FECACA;
        color: #B91C1C;
        font-size: 12.5px;
        font-weight: 600;
    }
</style>

<script>
const spCategories = @json($categories->map(fn($c) => ['id' => $c->id_kategori, 'name' => $c->nama_kategori])->values());
const spSuppliers = @json($suppliers->map(fn($s) => ['id' => $s->id_supplier, 'name' => $s->nama_supplier])->values());

function spEsc(v) {
    const el = document.createElement('span');
    el.textContent = String(v ?? '');
    return el.innerHTML;
}

function spRp(v) {
    return 'Rp ' + Number(v || 0).toLocaleString('id-ID');
}

function updateSpStockPreview(currentStock, unitLabel) {
    const qtyEl = document.getElementById('sp_tambah_qty');
    const hiddenEl = document.getElementById('sp_stok_baru_hidden');
    const previewEl = document.getElementById('sp_stok_preview');
    const addQty = Math.max(0, parseInt(qtyEl?.value || '0', 10) || 0);
    const nextTotal = Number(currentStock) + addQty;
    if (hiddenEl) hiddenEl.value = nextTotal;
    if (previewEl) previewEl.textContent = nextTotal + ' ' + unitLabel;
}

function closeSparepartModal() {
    const backdrop = document.getElementById('sparepartModalBackdrop');
    if (backdrop) backdrop.setAttribute('hidden', '');
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeSparepartModal();
});

function openSparepartModal(id, mode) {
    const el = document.getElementById('sparepart-data-' + id);
    if (!el) return;
    const item = JSON.parse(el.textContent);
    const backdrop = document.getElementById('sparepartModalBackdrop');
    const kicker = document.getElementById('spModalKicker');
    const title = document.getElementById('spModalTitle');
    const body = document.getElementById('spModalBody');
    const footer = document.getElementById('spModalFooter');

    if (mode === 'detail') {
        kicker.textContent = 'DETAIL SPAREPART · ' + item.kode_sparepart;
        title.textContent = item.nama_sparepart;
        body.innerHTML = `
            <div class="sp-detail-hero">
                <div class="sp-detail-thumb">
                    ${item.photo_url
                        ? `<img src="${spEsc(item.photo_url)}" alt="${spEsc(item.nama_sparepart)}">`
                        : `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>`}
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <span class="badge-sku">${spEsc(item.kode_sparepart)}</span>
                        <span class="badge-status ${item.status === 'aktif' ? 'is-active' : 'is-inactive'}">${spEsc(item.status)}</span>
                    </div>
                    <div style="margin-top:6px; font-size:13px; color:#64748B;">Merk: <strong style="color:#0F172A;">${spEsc(item.merk || '—')}</strong> &bull; Kategori: <strong style="color:#0F172A;">${spEsc(item.kategori_nama)}</strong></div>
                </div>
            </div>
            <div class="sp-detail-stats">
                <div class="sp-stat-box"><small>Stok Fisik</small><strong>${Number(item.stok)} ${spEsc(item.satuan)}</strong></div>
                <div class="sp-stat-box"><small>Stok Minimum</small><strong>${Number(item.stok_minimum)} ${spEsc(item.satuan)}</strong></div>
                <div class="sp-stat-box"><small>Harga Beli</small><strong>${spRp(item.harga_beli)}</strong></div>
                <div class="sp-stat-box"><small>Harga Jual</small><strong>${spRp(item.harga_jual)}</strong></div>
            </div>
            <div class="sp-detail-grid">
                <div class="sp-detail-item"><small>Supplier</small><span>${spEsc(item.supplier_nama || '—')}</span></div>
                <div class="sp-detail-item"><small>Satuan</small><span>${spEsc(item.satuan)}</span></div>
                <div class="sp-detail-item"><small>Nomor Part OEM</small><span>${spEsc(item.nomor_part_oem || '—')}</span></div>
                <div class="sp-detail-item"><small>Nomor Part Aftermarket</small><span>${spEsc(item.nomor_part_aftermarket || '—')}</span></div>
                <div class="sp-detail-item" style="grid-column:1 / -1;"><small>Deskripsi &amp; Spesifikasi</small><span>${spEsc(item.deskripsi_spesifikasi || 'Tidak ada catatan spesifikasi.')}</span></div>
            </div>
        `;
        footer.innerHTML = `
            <button type="button" class="btn-action-secondary" onclick="closeSparepartModal()">Tutup</button>
            <a href="${spEsc(item.show_url)}" class="btn-action-secondary">Riwayat &amp; Fitment</a>
            <button type="button" class="btn-action-secondary" onclick="openSparepartModal(${item.id}, 'stock')">+ Tambah Stok</button>
            <button type="button" class="btn-action-primary" onclick="openSparepartModal(${item.id}, 'edit')">Edit Sparepart</button>
        `;
    } else if (mode === 'stock') {
        const adjustUrl = @json(route('admin.inventory.adjust'));
        kicker.textContent = 'TAMBAH STOK SPAREPART · ' + item.kode_sparepart;
        title.textContent = 'Tambah Stok — ' + item.nama_sparepart;
        body.innerHTML = `
            <form id="sparepartStockModalForm" method="POST" action="${spEsc(adjustUrl)}">
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                <input type="hidden" name="id_sparepart" value="${item.id}">
                <input type="hidden" id="sp_stok_baru_hidden" name="stok_baru" value="${Number(item.stok) + 1}">

                <div class="sp-detail-stats" style="grid-template-columns: repeat(2, 1fr);">
                    <div class="sp-stat-box">
                        <small>Stok Saat Ini</small>
                        <strong>${Number(item.stok)} ${spEsc(item.satuan)}</strong>
                    </div>
                    <div class="sp-stat-box" style="background:#ECFDF5; border-color:#A7F3D0;">
                        <small style="color:#047857;">Total Stok Setelah Tambah</small>
                        <strong id="sp_stok_preview" style="color:#047857;">${Number(item.stok) + 1} ${spEsc(item.satuan)}</strong>
                    </div>
                </div>

                <div class="sp-form-grid">
                    <div class="sp-form-group">
                        <label for="sp_tambah_qty">Jumlah Tambah Stok (${spEsc(item.satuan)}) *</label>
                        <input id="sp_tambah_qty" type="number" min="1" value="1" class="sp-form-input" required oninput="updateSpStockPreview(${Number(item.stok)}, '${spEsc(item.satuan)}')">
                    </div>
                    <div class="sp-form-group">
                        <label for="sp_alasan_stok">Alasan *</label>
                        <input id="sp_alasan_stok" name="alasan" class="sp-form-input" value="Penambahan stok gudang" required>
                    </div>
                    <div class="sp-form-group is-full">
                        <label for="sp_catatan_stok">Catatan *</label>
                        <input id="sp_catatan_stok" name="catatan" class="sp-form-input" value="Penambahan stok langsung dari master sparepart" required>
                    </div>
                </div>
            </form>
        `;
        footer.innerHTML = `
            <button type="button" class="btn-action-secondary" onclick="closeSparepartModal()">Batal</button>
            <button type="submit" form="sparepartStockModalForm" class="btn-action-primary" style="background:#059669;">+ Simpan Penambahan Stok</button>
        `;
    } else {
        kicker.textContent = 'EDIT MASTER SPAREPART · ' + item.kode_sparepart;
        title.textContent = 'Edit ' + item.nama_sparepart;
        body.innerHTML = `
            <form id="sparepartEditModalForm" onsubmit="submitSparepartEditModal(event, ${item.id})" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                <input type="hidden" name="_method" value="PUT">
                <div class="sp-form-grid">
                    <div class="sp-form-group">
                        <label for="sp_kode">Kode SKU *</label>
                        <input id="sp_kode" name="kode_sparepart" class="sp-form-input" value="${spEsc(item.kode_sparepart)}" required>
                    </div>
                    <div class="sp-form-group">
                        <label for="sp_nama">Nama Sparepart *</label>
                        <input id="sp_nama" name="nama_sparepart" class="sp-form-input" value="${spEsc(item.nama_sparepart)}" required>
                    </div>
                    <div class="sp-form-group">
                        <label for="sp_merk">Merk</label>
                        <input id="sp_merk" name="merk" class="sp-form-input" value="${spEsc(item.merk)}">
                    </div>
                    <div class="sp-form-group">
                        <label for="sp_kategori">Kategori *</label>
                        <select id="sp_kategori" name="id_kategori" class="sp-form-select" required>
                            ${spCategories.map(c => `<option value="${c.id}" ${Number(c.id) === Number(item.id_kategori) ? 'selected' : ''}>${spEsc(c.name)}</option>`).join('')}
                        </select>
                    </div>
                    <div class="sp-form-group">
                        <label for="sp_supplier">Supplier</label>
                        <select id="sp_supplier" name="id_supplier" class="sp-form-select">
                            <option value="">-- Pilih Supplier --</option>
                            ${spSuppliers.map(s => `<option value="${s.id}" ${Number(s.id) === Number(item.id_supplier) ? 'selected' : ''}>${spEsc(s.name)}</option>`).join('')}
                        </select>
                    </div>
                    <div class="sp-form-group">
                        <label for="sp_satuan">Satuan *</label>
                        <input id="sp_satuan" name="satuan" class="sp-form-input" value="${spEsc(item.satuan)}" required>
                    </div>
                    <div class="sp-form-group">
                        <label for="sp_beli">Harga Beli (Rp) *</label>
                        <input id="sp_beli" name="harga_beli" type="number" min="0" class="sp-form-input" value="${Number(item.harga_beli)}" required>
                    </div>
                    <div class="sp-form-group">
                        <label for="sp_jual">Harga Jual (Rp) *</label>
                        <input id="sp_jual" name="harga_jual" type="number" min="0" class="sp-form-input" value="${Number(item.harga_jual)}" required>
                    </div>
                    <div class="sp-form-group">
                        <label for="sp_min">Stok Minimum *</label>
                        <input id="sp_min" name="stok_minimum" type="number" min="0" class="sp-form-input" value="${Number(item.stok_minimum)}" required>
                    </div>
                    <div class="sp-form-group">
                        <label for="sp_foto">Ganti Foto (Opsional)</label>
                        <input id="sp_foto" name="foto" type="file" accept="image/*" class="sp-form-input">
                    </div>
                    <div class="sp-form-group">
                        <label for="sp_oem">Nomor Part OEM</label>
                        <input id="sp_oem" name="nomor_part_oem" class="sp-form-input" value="${spEsc(item.nomor_part_oem)}">
                    </div>
                    <div class="sp-form-group">
                        <label for="sp_aftermarket">Nomor Part Aftermarket</label>
                        <input id="sp_aftermarket" name="nomor_part_aftermarket" class="sp-form-input" value="${spEsc(item.nomor_part_aftermarket)}">
                    </div>
                    <div class="sp-form-group is-full">
                        <label for="sp_desc">Deskripsi Spesifikasi</label>
                        <textarea id="sp_desc" name="deskripsi_spesifikasi" rows="2" class="sp-form-textarea">${spEsc(item.deskripsi_spesifikasi)}</textarea>
                    </div>
                </div>
                <div id="spEditError" class="sp-form-error" style="display:none;"></div>
            </form>
        `;
        footer.innerHTML = `
            <button type="button" class="btn-action-secondary" onclick="closeSparepartModal()">Batal</button>
            <button type="submit" form="sparepartEditModalForm" id="spEditSubmitBtn" class="btn-action-primary">Simpan Perubahan</button>
        `;
    }

    backdrop.removeAttribute('hidden');
}

async function submitSparepartEditModal(e, id) {
    e.preventDefault();
    const form = e.target;
    const errBox = document.getElementById('spEditError');
    const btn = document.getElementById('spEditSubmitBtn');
    const el = document.getElementById('sparepart-data-' + id);
    if (!el) return;
    const item = JSON.parse(el.textContent);

    errBox.style.display = 'none';
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    try {
        const res = await fetch(item.update_url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new FormData(form),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            const firstErr = data.errors ? Object.values(data.errors).flat()[0] : null;
            errBox.textContent = firstErr || data.message || 'Gagal menyimpan perubahan sparepart.';
            errBox.style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Simpan Perubahan';
            return;
        }
        window.location.reload();
    } catch (err) {
        errBox.textContent = 'Terjadi kesalahan koneksi: ' + err.message;
        errBox.style.display = 'block';
        btn.disabled = false;
        btn.textContent = 'Simpan Perubahan';
    }
}
</script>
@endsection
