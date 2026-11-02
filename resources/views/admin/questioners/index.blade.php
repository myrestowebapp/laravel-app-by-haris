@extends('layouts.admin')

@section('title', 'Questioner Directory')
@section('topbar-title', 'Questioner Directory')
@section('topbar-icon', 'bi-people-fill')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <span class="text-dark fw-semibold">Questioners</span>
@endsection

@push('styles')
<style>
    /* ── Metric Summary Tiles (Exact Reference Design) ── */
    .metric-card-box {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 1.15rem 1.35rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .metric-card-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }
    .metric-icon-sq {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .metric-val-title {
        font-size: 1.6rem;
        font-weight: 800;
        color: #0F172A;
        line-height: 1.1;
        margin: 0;
    }
    .metric-lbl-sub {
        font-size: 0.78rem;
        font-weight: 500;
        color: #64748B;
        margin-top: 3px;
    }
    .metric-pill-tag {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
    }

    /* ── Search & Filter Toolbar ── */
    .questioner-toolbar-wrap {
        margin-bottom: 1.25rem;
    }
    .questioner-search-box {
        position: relative;
        width: 100%;
    }
    .questioner-search-box .search-icon-left {
        position: absolute;
        top: 50%;
        left: 15px;
        transform: translateY(-50%);
        font-size: 0.92rem;
        color: #94A3B8;
        pointer-events: none;
    }
    .questioner-search-input {
        height: 44px;
        border-radius: 12px;
        border: 1.5px solid #2563EB;
        padding-left: 42px;
        padding-right: 42px;
        font-size: 0.88rem;
        color: #0F172A;
        background: #FFFFFF;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        transition: all 0.15s ease;
    }
    .questioner-search-input:focus {
        border-color: #1D4ED8;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        outline: none;
    }
    .questioner-search-box .search-spinner-icon {
        position: absolute;
        top: 50%;
        right: 42px;
        transform: translateY(-50%);
        display: none;
        width: 16px;
        height: 16px;
    }
    .questioner-search-box .clear-btn {
        position: absolute;
        top: 50%;
        right: 14px;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #64748B;
        font-size: 0.90rem;
        cursor: pointer;
        padding: 4px;
        display: none;
        align-items: center;
        justify-content: center;
        transition: color 0.15s ease;
    }
    .questioner-search-box .clear-btn:hover {
        color: #0F172A;
    }
    .search-live-feedback {
        display: none;
        align-items: center;
        gap: 6px;
        font-size: 0.80rem;
        color: #64748B;
        margin-top: 6px;
    }
    .spinner-rotate {
        animation: spin-rotate 1s linear infinite;
        display: inline-block;
    }
    @keyframes spin-rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* ── Action Toolbar Buttons ── */
    .per-page-select-custom {
        height: 44px;
        border-radius: 10px;
        border: 1px solid #E2E8F0;
        background-color: #FFFFFF;
        padding: 0 2rem 0 0.85rem;
        font-size: 0.86rem;
        font-weight: 500;
        color: #1E293B;
        cursor: pointer;
    }
    .per-page-select-custom:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }
    .btn-filter-action {
        height: 44px;
        background: #2563EB;
        color: #FFFFFF !important;
        border: 1px solid #2563EB;
        border-radius: 10px;
        padding: 0 1.25rem;
        font-size: 0.88rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 1px 2px rgba(37, 99, 235, 0.2);
        transition: all 0.15s ease;
    }
    .btn-filter-action:hover {
        background: #1D4ED8;
        border-color: #1D4ED8;
    }
    .btn-refresh-icon {
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        color: #64748B !important;
        font-size: 1.05rem;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .btn-refresh-icon:hover {
        background: #F8FAFC;
        color: #1E293B !important;
        border-color: #CBD5E1;
    }
    .btn-export-action {
        height: 44px;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        padding: 0 1.15rem;
        font-size: 0.88rem;
        font-weight: 500;
        color: #1E293B !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.15s ease;
    }
    .btn-export-action:hover {
        background: #F8FAFC;
        border-color: #CBD5E1;
        color: #0F172A !important;
    }

    /* ── Table Container & Styles ── */
    .questioner-table-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }
    .questioner-data-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    .questioner-data-table thead th {
        background: #F8FAFC;
        color: #1E293B;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.2px;
        padding: 0.90rem 1rem;
        border-bottom: 1px solid #E2E8F0;
        white-space: nowrap;
        vertical-align: middle;
    }
    .questioner-data-table tbody td {
        padding: 0.90rem 1rem;
        border-bottom: 1px solid #F1F5F9;
        font-size: 0.86rem;
        vertical-align: middle;
        background: #FFFFFF;
        transition: background-color 0.12s ease;
    }
    .questioner-data-table tbody tr:hover td {
        background-color: #F8FAFC;
    }
    .questioner-data-table tbody tr.row-selected td {
        background-color: #EFF6FF !important;
    }
    .questioner-data-table tbody tr:last-child td {
        border-bottom: none;
    }

    .sortable-th {
        cursor: pointer;
        user-select: none;
        transition: color 0.15s ease;
    }
    .sortable-th:hover {
        color: #2563EB !important;
    }

    /* ── Identity Avatar ── */
    .avatar-circle-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.80rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    /* ── Questioner Mobile Tag in Free Space ── */
    .questioner-mobile-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        color: #1E293B !important;
        border-radius: 8px;
        padding: 4px 10px;
        font-size: 0.78rem;
        font-weight: 600;
        white-space: nowrap;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .questioner-mobile-tag:hover:not(.is-empty) {
        background: #EFF6FF;
        border-color: #BFDBFE;
        color: #2563EB !important;
        box-shadow: 0 1px 2px rgba(37, 99, 235, 0.08);
    }
    .questioner-mobile-tag i {
        font-size: 0.74rem;
        color: #2563EB;
    }
    .questioner-mobile-tag.is-empty {
        background: transparent;
        border: 1px dashed #E2E8F0;
        color: #94A3B8 !important;
        font-weight: 500;
        font-size: 0.74rem;
        cursor: default;
    }
    .questioner-mobile-tag.is-empty i {
        color: #94A3B8;
        opacity: 0.6;
    }

    /* ── Country Flag SVGs ── */
    .country-flag-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .country-flag-svg {
        width: 20px;
        height: 14px;
        border-radius: 2px;
        box-shadow: 0 0 1px rgba(0, 0, 0, 0.25);
        flex-shrink: 0;
    }

    /* ── Badges for Total / Pending / Published ── */
    .stat-badge-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 28px;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 700;
        line-height: 1;
        padding: 0 6px;
    }
    .stat-badge-blue {
        background: #EFF6FF;
        color: #2563EB;
    }
    .stat-badge-amber {
        background: #FFFBEB;
        color: #D97706;
    }
    .stat-badge-green {
        background: #ECFDF5;
        color: #059669;
    }

    /* ── Action View Button ── */
    .btn-action-view {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: #FFFFFF;
        border: 1.5px solid #BFDBFE;
        color: #2563EB !important;
        border-radius: 8px;
        padding: 0.35rem 0.85rem;
        font-size: 0.80rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .btn-action-view:hover {
        background: #EFF6FF;
        border-color: #93C5FD;
        color: #1D4ED8 !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.12);
    }

    /* ── Action Delete Button (No Fatwas) ── */
    .btn-action-delete {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 31px;
        background: #FFFFFF;
        border: 1.5px solid #FECACA;
        color: #DC2626 !important;
        border-radius: 8px;
        font-size: 0.80rem;
        transition: all 0.15s ease;
        cursor: pointer;
    }
    .btn-action-delete:hover {
        background: #FEF2F2;
        border-color: #F87171;
        color: #B91C1C !important;
        box-shadow: 0 1px 3px rgba(220, 38, 38, 0.15);
    }

    /* ── Pagination Elements ── */
    .btn-pagination-nav {
        display: inline-flex;
        align-items: center;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        color: #64748B !important;
        border-radius: 8px;
        padding: 0.38rem 0.85rem;
        font-size: 0.82rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .btn-pagination-nav:hover:not(.disabled) {
        background: #F8FAFC;
        color: #1E293B !important;
        border-color: #CBD5E1;
    }
    .btn-pagination-nav.disabled {
        opacity: 0.45;
        cursor: not-allowed;
        pointer-events: none;
    }
    .btn-pagination-page {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        border-radius: 8px;
        font-size: 0.84rem;
        font-weight: 600;
        text-decoration: none;
        color: #475569 !important;
        border: 1px solid transparent;
        padding: 0 6px;
        transition: all 0.15s ease;
    }
    .btn-pagination-page:hover:not(.active) {
        background: #F1F5F9;
        color: #0F172A !important;
    }
    .btn-pagination-page.active {
        background: #2563EB;
        color: #FFFFFF !important;
        font-weight: 700;
    }

    /* ── Floating Bulk Action Toolbar ── */
    .bulk-action-bar {
        position: fixed;
        bottom: 56px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: #1E293B;
        color: #FFFFFF;
        padding: 0.7rem 1.4rem;
        border-radius: 50px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.2);
        display: flex;
        align-items: center;
        gap: 16px;
        z-index: 1050;
        opacity: 0;
        visibility: hidden;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .bulk-action-bar.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
        visibility: visible;
    }
</style>
@endpush

@section('content')

{{-- Helper for Crisp Mini Flag SVGs --}}
@php
    $renderFlag = function($country) {
        $c = strtolower(trim((string)$country));
        if (!$c) {
            return '<i class="bi bi-geo-alt text-muted" style="font-size: 0.85rem;"></i>';
        }

        // India 🇮🇳
        if (str_contains($c, 'india') || $c === 'in') {
            return '<svg class="country-flag-svg" viewBox="0 0 24 16"><rect width="24" height="5.33" fill="#FF9933"/><rect y="5.33" width="24" height="5.33" fill="#FFFFFF"/><rect y="10.66" width="24" height="5.33" fill="#128807"/><circle cx="12" cy="8" r="2.2" fill="none" stroke="#000080" stroke-width="0.6"/><circle cx="12" cy="8" r="0.4" fill="#000080"/></svg>';
        }
        // Saudi Arabia 🇸🇦
        if (str_contains($c, 'saudi') || $c === 'sa') {
            return '<svg class="country-flag-svg" viewBox="0 0 24 16"><rect width="24" height="16" fill="#006C35"/><path d="M5 9h14M16 7.5l3 1.5-3 1.5" stroke="#FFFFFF" stroke-width="0.8" fill="none"/><text x="12" y="6.8" font-size="3.5" fill="#FFFFFF" text-anchor="middle" font-family="sans-serif" font-weight="bold">لا اله الا الله</text></svg>';
        }
        // United Arab Emirates 🇦🇪
        if (str_contains($c, 'emirates') || str_contains($c, 'uae') || $c === 'ae') {
            return '<svg class="country-flag-svg" viewBox="0 0 24 16"><rect x="6" y="0" width="18" height="5.33" fill="#00732F"/><rect x="6" y="5.33" width="18" height="5.33" fill="#FFFFFF"/><rect x="6" y="10.66" width="18" height="5.33" fill="#000000"/><rect x="0" y="0" width="6" height="16" fill="#FF0000"/></svg>';
        }
        // Pakistan 🇵🇰
        if (str_contains($c, 'pakistan') || $c === 'pk') {
            return '<svg class="country-flag-svg" viewBox="0 0 24 16"><rect width="24" height="16" fill="#01411C"/><rect width="6" height="16" fill="#FFFFFF"/><circle cx="14.5" cy="8" r="3.4" fill="#FFFFFF"/><circle cx="15.5" cy="7.2" r="3" fill="#01411C"/><polygon points="16,6.2 16.5,7.7 15.2,6.8 16.8,6.8 15.5,7.7" fill="#FFFFFF"/></svg>';
        }
        // Qatar 🇶🇦
        if (str_contains($c, 'qatar') || $c === 'qa') {
            return '<svg class="country-flag-svg" viewBox="0 0 24 16"><rect width="24" height="16" fill="#8D1B3D"/><polygon points="0,0 8,0 8,16 0,16" fill="#FFFFFF"/><polygon points="8,0 11,2 8,4 11,6 8,8 11,10 8,12 11,14 8,16" fill="#FFFFFF"/></svg>';
        }
        // Kuwait 🇰🇼
        if (str_contains($c, 'kuwait') || $c === 'kw') {
            return '<svg class="country-flag-svg" viewBox="0 0 24 16"><rect width="24" height="5.33" fill="#007A3D"/><rect y="5.33" width="24" height="5.33" fill="#FFFFFF"/><rect y="10.66" width="24" height="5.33" fill="#CE1126"/><polygon points="0,0 6,5.33 6,10.66 0,16" fill="#000000"/></svg>';
        }
        // Oman 🇴🇲
        if (str_contains($c, 'oman') || $c === 'om') {
            return '<svg class="country-flag-svg" viewBox="0 0 24 16"><rect x="6" width="18" height="5.33" fill="#FFFFFF"/><rect x="6" y="5.33" width="18" height="5.33" fill="#DB161B"/><rect x="6" y="10.66" width="18" height="5.33" fill="#008000"/><rect width="6" height="16" fill="#DB161B"/></svg>';
        }
        // Bahrain 🇧🇭
        if (str_contains($c, 'bahrain') || $c === 'bh') {
            return '<svg class="country-flag-svg" viewBox="0 0 24 16"><rect width="24" height="16" fill="#CE1126"/><polygon points="0,0 7,0 7,16 0,16" fill="#FFFFFF"/><polygon points="7,0 9.5,2 7,4 9.5,6 7,8 9.5,10 7,12 9.5,14 7,16" fill="#FFFFFF"/></svg>';
        }
        // United Kingdom 🇬🇧
        if (str_contains($c, 'kingdom') || str_contains($c, 'uk') || $c === 'gb') {
            return '<svg class="country-flag-svg" viewBox="0 0 24 16"><rect width="24" height="16" fill="#012169"/><path d="M0 0 L24 16 M24 0 L0 16" stroke="#FFFFFF" stroke-width="2.5"/><path d="M0 0 L24 16 M24 0 L0 16" stroke="#C8102E" stroke-width="1.2"/><path d="M12 0 v16 M0 8 h24" stroke="#FFFFFF" stroke-width="4"/><path d="M12 0 v16 M0 8 h24" stroke="#C8102E" stroke-width="2.4"/></svg>';
        }
        // United States 🇺🇸
        if (str_contains($c, 'united states') || str_contains($c, 'usa') || $c === 'us') {
            return '<svg class="country-flag-svg" viewBox="0 0 24 16"><rect width="24" height="16" fill="#B22234"/><rect y="1.5" width="24" height="1.5" fill="#FFFFFF"/><rect y="4.5" width="24" height="1.5" fill="#FFFFFF"/><rect y="7.5" width="24" height="1.5" fill="#FFFFFF"/><rect y="10.5" width="24" height="1.5" fill="#FFFFFF"/><rect y="13.5" width="24" height="1.5" fill="#FFFFFF"/><rect width="10" height="8.5" fill="#3C3B6E"/></svg>';
        }
        // Malaysia 🇲🇾
        if (str_contains($c, 'malaysia') || $c === 'my') {
            return '<svg class="country-flag-svg" viewBox="0 0 24 16"><rect width="24" height="16" fill="#CC0000"/><rect y="1.5" width="24" height="1.5" fill="#FFFFFF"/><rect y="4.5" width="24" height="1.5" fill="#FFFFFF"/><rect y="7.5" width="24" height="1.5" fill="#FFFFFF"/><rect y="10.5" width="24" height="1.5" fill="#FFFFFF"/><rect y="13.5" width="24" height="1.5" fill="#FFFFFF"/><rect width="11" height="8.5" fill="#000066"/><circle cx="5" cy="4.2" r="2.8" fill="#FFCC00"/><circle cx="5.8" cy="4.2" r="2.4" fill="#000066"/><polygon points="8,4.2 8.7,4.6 8.6,3.8 9.3,3.5 8.5,3.4 8.3,2.6 8,3.4 7.2,3.5 7.9,3.8 7.8,4.6" fill="#FFCC00"/></svg>';
        }

        // Generic Globe Fallback
        return '<i class="bi bi-geo-alt text-muted" style="font-size: 0.85rem;"></i>';
    };
@endphp

{{-- 1. Metric Summary Cards Row --}}
<div class="row g-3 mb-4">
    {{-- Total Inquirers --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card-box d-flex align-items-center justify-content-between h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-sq" style="background: #EFF6FF; color: #2563EB;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <h3 class="metric-val-title" data-summary-key="total">{{ number_format($summaryStats['total'] ?? 0) }}</h3>
                    <div class="metric-lbl-sub">Total Inquirers</div>
                </div>
            </div>
            <span class="metric-pill-tag" style="background: #EFF6FF; color: #2563EB;">
                Active
            </span>
        </div>
    </div>

    {{-- In Progress Review --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card-box d-flex align-items-center justify-content-between h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-sq" style="background: #FFFBEB; color: #D97706;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <h3 class="metric-val-title" data-summary-key="active">{{ number_format($summaryStats['active'] ?? 0) }}</h3>
                    <div class="metric-lbl-sub">In Progress Review</div>
                </div>
            </div>
            <span class="metric-pill-tag" style="background: #FFFBEB; color: #D97706;">
                Pending
            </span>
        </div>
    </div>

    {{-- Published Answers --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card-box d-flex align-items-center justify-content-between h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-sq" style="background: #ECFDF5; color: #059669;">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <div>
                    <h3 class="metric-val-title" data-summary-key="published">{{ number_format($summaryStats['published'] ?? 0) }}</h3>
                    <div class="metric-lbl-sub">Published Answers</div>
                </div>
            </div>
            <span class="metric-pill-tag" style="background: #ECFDF5; color: #059669;">
                Archived
            </span>
        </div>
    </div>

    {{-- Countries Reached --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card-box d-flex align-items-center justify-content-between h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-sq" style="background: #FAF5FF; color: #9333EA;">
                    <i class="bi bi-globe2"></i>
                </div>
                <div>
                    <h3 class="metric-val-title" data-summary-key="countries">{{ number_format($summaryStats['countries'] ?? 0) }}</h3>
                    <div class="metric-lbl-sub">Countries Reached</div>
                </div>
            </div>
            <span class="metric-pill-tag" style="background: #FAF5FF; color: #9333EA;">
                Global
            </span>
        </div>
    </div>
</div>

{{-- 2. Search & Toolbar Bar (Seamless Modern SaaS Toolbar) --}}
<div class="questioner-toolbar-wrap">
    <form method="GET" action="{{ route('admin.questioners.index') }}" id="filterForm">
        {{-- Preserve Sort Parameters --}}
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="direction" value="{{ $direction }}">

        <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-start justify-content-between gap-3">
            {{-- Left Side: Search Input with Inside Spinner, Clear Button & Status Line --}}
            <div class="flex-grow-1" style="max-width: 580px;">
                <div class="questioner-search-box">
                    <i class="bi bi-search search-icon-left" id="liveSearchIcon"></i>
                    <input type="text" 
                           name="search" 
                           id="questionerLiveSearchInput"
                           class="form-control questioner-search-input" 
                           placeholder="Search..." 
                           value="{{ request('search') }}"
                           autocomplete="off">
                    
                    {{-- Spinner Inside Search Field --}}
                    <div class="spinner-border text-primary spinner-border-sm search-spinner-icon" id="liveSearchSpinner" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>

                    {{-- Instant Clear Button (x) --}}
                    <button type="button" class="clear-btn" id="clearSearchBtn" title="Clear Search">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                {{-- Auto Search Status Feedback directly below the input --}}
                <div class="search-live-feedback" id="liveSearchStatus" style="{{ request('search') ? 'display: flex;' : 'display: none;' }}">
                    <i class="bi bi-arrow-repeat text-primary spinner-rotate" id="liveStatusIcon" style="font-size: 0.90rem;"></i>
                    <span id="liveStatusText">Searching "{{ request('search') }}"... (auto search)</span>
                </div>
            </div>

            {{-- Right Side: Show 20 rows, Filter, Refresh, Export --}}
            <div class="d-flex align-items-center gap-2 justify-content-end flex-wrap">
                {{-- Density Select --}}
                <select name="per_page" id="perPageSelect" class="form-select per-page-select-custom">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>Show 10 rows</option>
                    <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>Show 20 rows</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>Show 50 rows</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>Show 100 rows</option>
                </select>

                {{-- Filter Button --}}
                <button type="submit" class="btn btn-filter-action" id="filterSubmitBtn">
                    <i class="bi bi-funnel"></i> Filter
                </button>

                {{-- Refresh Button --}}
                <a href="{{ route('admin.questioners.index') }}" class="btn btn-refresh-icon" id="resetFilterBtn" title="Reset Filters & Refresh">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>

                {{-- Export Dropdown --}}
                <div class="dropdown">
                    <button class="btn btn-export-action dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-box-arrow-up"></i> Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" style="border-radius: 12px; font-size: 0.85rem;">
                        <li>
                            <form method="POST" action="{{ route('admin.questioners.export-selected') }}">
                                @csrf
                                <input type="hidden" name="search" value="{{ request('search') }}">
                                <button type="submit" class="dropdown-item py-1.5">
                                    <i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>Export Current List (CSV)
                                </button>
                            </form>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-1.5" href="javascript:window.print()">
                                <i class="bi bi-printer text-muted me-2"></i>Print Table View
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- 3. Modern Data Table Card Container (Dynamically Swapped via Live Search) --}}
<div class="questioner-table-card" id="questionerTableContainer">
    <div class="table-responsive">
        <table class="table questioner-data-table align-middle">
            <thead>
                <tr>
                    {{-- Master Checkbox: Center --}}
                    <th style="width: 44px;" class="text-center">
                        <input type="checkbox" class="form-check-input" id="checkAllQuestioners" style="border-radius: 4px; cursor: pointer; width: 17px; height: 17px;">
                    </th>

                    {{-- Row Index: Center --}}
                    <th style="width: 50px;" class="text-center">#</th>

                    {{-- Questioner Identity: Left (Sortable) --}}
                    @php
                        $nameDir = ($sort === 'name' && $direction === 'asc') ? 'desc' : 'asc';
                        $nameIcon = ($sort === 'name') ? ($direction === 'asc' ? 'bi-arrow-up text-primary fw-bold' : 'bi-arrow-down text-primary fw-bold') : 'bi-arrow-down-up text-muted opacity-50';
                    @endphp
                    <th class="sortable-th text-start" data-sort="name" data-direction="{{ $nameDir }}">
                        <div class="d-inline-flex align-items-center gap-1.5">
                            <span>Questioner Identity</span>
                            <i class="bi {{ $nameIcon }}" style="font-size: 0.76rem;"></i>
                        </div>
                    </th>

                    {{-- Country: Left (Sortable) --}}
                    @php
                        $countryDir = ($sort === 'country' && $direction === 'asc') ? 'desc' : 'asc';
                        $countryIcon = ($sort === 'country') ? ($direction === 'asc' ? 'bi-arrow-up text-primary fw-bold' : 'bi-arrow-down text-primary fw-bold') : 'bi-arrow-down-up text-muted opacity-50';
                    @endphp
                    <th style="width: 190px;" class="sortable-th text-start" data-sort="country" data-direction="{{ $countryDir }}">
                        <div class="d-inline-flex align-items-center gap-1.5">
                            <span>Country</span>
                            <i class="bi {{ $countryIcon }}" style="font-size: 0.76rem;"></i>
                        </div>
                    </th>

                    {{-- Joined Date: Center (Sortable - Active sort in reference screenshot) --}}
                    @php
                        $dateDir = ($sort === 'created_at' && $direction === 'desc') ? 'asc' : 'desc';
                        $dateIcon = ($sort === 'created_at') ? ($direction === 'asc' ? 'bi-arrow-up text-primary fw-bold' : 'bi-arrow-down text-primary fw-bold') : 'bi-arrow-down-up text-muted opacity-50';
                    @endphp
                    <th style="width: 150px;" class="sortable-th text-center" data-sort="created_at" data-direction="{{ $dateDir }}">
                        <div class="d-inline-flex align-items-center justify-content-center gap-1.5">
                            <span>Joined Date</span>
                            <i class="bi {{ $dateIcon }}" style="font-size: 0.76rem;"></i>
                        </div>
                    </th>

                    {{-- Total Questions: Center (Sortable) --}}
                    @php
                        $totalDir = ($sort === 'total_questions' && $direction === 'desc') ? 'asc' : 'desc';
                        $totalIcon = ($sort === 'total_questions') ? ($direction === 'asc' ? 'bi-arrow-up text-primary fw-bold' : 'bi-arrow-down text-primary fw-bold') : 'bi-arrow-down-up text-muted opacity-50';
                    @endphp
                    <th style="width: 95px;" class="text-center sortable-th" data-sort="total_questions" data-direction="{{ $totalDir }}">
                        <div class="d-inline-flex align-items-center justify-content-center gap-1.5">
                            <span>Total</span>
                            <i class="bi {{ $totalIcon }}" style="font-size: 0.76rem;"></i>
                        </div>
                    </th>

                    {{-- In Progress: Center (Sortable) --}}
                    @php
                        $pendingDir = ($sort === 'pending_questions' && $direction === 'desc') ? 'asc' : 'desc';
                        $pendingIcon = ($sort === 'pending_questions') ? ($direction === 'asc' ? 'bi-arrow-up text-primary fw-bold' : 'bi-arrow-down text-primary fw-bold') : 'bi-arrow-down-up text-muted opacity-50';
                    @endphp
                    <th style="width: 115px;" class="text-center sortable-th" data-sort="pending_questions" data-direction="{{ $pendingDir }}">
                        <div class="d-inline-flex align-items-center justify-content-center gap-1.5">
                            <span>In Progress</span>
                            <i class="bi {{ $pendingIcon }}" style="font-size: 0.76rem;"></i>
                        </div>
                    </th>

                    {{-- Published: Center (Sortable) --}}
                    @php
                        $pubDir = ($sort === 'published_questions' && $direction === 'desc') ? 'asc' : 'desc';
                        $pubIcon = ($sort === 'published_questions') ? ($direction === 'asc' ? 'bi-arrow-up text-primary fw-bold' : 'bi-arrow-down text-primary fw-bold') : 'bi-arrow-down-up text-muted opacity-50';
                    @endphp
                    <th style="width: 115px;" class="text-center sortable-th" data-sort="published_questions" data-direction="{{ $pubDir }}">
                        <div class="d-inline-flex align-items-center justify-content-center gap-1.5">
                            <span>Published</span>
                            <i class="bi {{ $pubIcon }}" style="font-size: 0.76rem;"></i>
                        </div>
                    </th>

                    {{-- Action: Center --}}
                    <th style="width: 125px;" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($questioners as $user)
                @php
                    $displayName = $user->despl_name ?: ($user->name ?: 'Questioner');
                    $words = explode(' ', trim($displayName));
                    $initials = '';
                    foreach (array_slice($words, 0, 2) as $w) {
                        $initials .= mb_strtoupper(mb_substr($w, 0, 1, 'UTF-8'), 'UTF-8');
                    }
                    $initials = $initials ?: 'Q';

                    // Pastel Monogram Palette exactly matching the reference design
                    $colorPalette = [
                        ['bg' => '#FEF3C7', 'text' => '#D97706'], // warm amber (RM)
                        ['bg' => '#DBEAFE', 'text' => '#1D4ED8'], // soft blue (RK)
                        ['bg' => '#F3E8FF', 'text' => '#7E22CE'], // soft purple (RS)
                        ['bg' => '#DCFCE7', 'text' => '#15803D'], // soft green (AR)
                        ['bg' => '#FEE2E2', 'text' => '#DC2626'], // soft rose
                        ['bg' => '#E0E7FF', 'text' => '#4338CA'], // soft indigo
                    ];
                    $colorIdx = abs(crc32((string) $displayName)) % count($colorPalette);
                    $colorTheme = $colorPalette[$colorIdx];
                @endphp
                <tr id="row-{{ $user->id }}">
                    {{-- Row Checkbox: Center --}}
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input questioner-checkbox" value="{{ $user->id }}" style="border-radius: 4px; cursor: pointer; width: 17px; height: 17px;">
                    </td>

                    {{-- Row Index: Center --}}
                    <td class="text-center text-dark fw-semibold" style="font-size: 0.85rem;">
                        {{ ($questioners->firstItem() ?? 0) + $loop->index }}
                    </td>

                    {{-- Identity: Left (Avatar + Bold Name + Muted Email + Mobile Tag in Free Space) --}}
                    <td class="text-start">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                @if($user->avatar)
                                    <img src="{{ asset($user->avatar) }}" class="avatar-circle-icon shadow-xs" width="38" height="38" style="object-fit: cover;">
                                @else
                                    <div class="avatar-circle-icon shadow-xs" style="background: {{ $colorTheme['bg'] }}; color: {{ $colorTheme['text'] }};">
                                        {{ $initials }}
                                    </div>
                                @endif
                                <div class="overflow-hidden">
                                    <div class="fw-bold text-dark text-truncate" style="max-width: 250px; font-size: 0.88rem; line-height: 1.3;">
                                        {{ $displayName }}
                                    </div>
                                    <div class="text-muted text-truncate" style="font-size: 0.77rem; max-width: 250px; line-height: 1.2; margin-top: 2px;">
                                        <i class="bi bi-envelope text-slate-400 me-1" style="font-size: 0.72rem;"></i>{{ $user->email ?: 'No email recorded' }}
                                    </div>
                                </div>
                            </div>

                            {{-- Mobile Number Tag (Utilizing the free space beside profile) --}}
                            <div class="ms-auto pe-2">
                                @if($user->mobile)
                                    <a href="tel:{{ $user->mobile }}" class="questioner-mobile-tag" title="Call or copy: {{ $user->mobile }}">
                                        <i class="bi bi-telephone-fill"></i>
                                        <span>{{ $user->mobile }}</span>
                                    </a>
                                @else
                                    <span class="questioner-mobile-tag is-empty" title="No mobile number registered">
                                        <i class="bi bi-telephone"></i>
                                        <span>No mobile</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Country: Left (Flag SVG + Country Name) --}}
                    <td class="text-start">
                        <div class="country-flag-badge">
                            {!! $renderFlag($user->country) !!}
                            <span class="text-dark fw-medium" style="font-size: 0.85rem;">{{ $user->country ?: '—' }}</span>
                        </div>
                    </td>

                    {{-- Joined Date: Center --}}
                    <td class="text-center">
                        <div class="text-secondary d-inline-flex align-items-center justify-content-center gap-1.5" style="font-size: 0.84rem; white-space: nowrap;">
                            <i class="bi bi-calendar3 text-muted" style="font-size: 0.80rem;"></i>
                            <span>{{ $user->created_at ? $user->created_at->format('d M Y') : '—' }}</span>
                        </div>
                    </td>

                    {{-- Total Questions: Center --}}
                    <td class="text-center">
                        <span class="stat-badge-pill stat-badge-blue">
                            {{ $user->total_questions ?? 0 }}
                        </span>
                    </td>

                    {{-- In Progress: Center --}}
                    <td class="text-center">
                        <span class="stat-badge-pill stat-badge-amber">
                            {{ $user->pending_questions ?? 0 }}
                        </span>
                    </td>

                    {{-- Published: Center --}}
                    <td class="text-center">
                        <span class="stat-badge-pill stat-badge-green">
                            {{ $user->published_questions ?? 0 }}
                        </span>
                    </td>

                    {{-- Action: Center --}}
                    <td class="text-center">
                        <div class="d-inline-flex align-items-center gap-1.5 justify-content-center">
                            <a href="{{ route('admin.questioners.show', $user) }}" 
                               class="btn-action-view" 
                               title="View Questioner Dossier">
                                <i class="bi bi-eye"></i>
                                <span>View</span>
                            </a>
                            @if(($user->total_questions ?? 0) === 0)
                                <form method="POST" action="{{ route('admin.questioners.destroy', $user) }}" class="d-inline delete-user-form" onsubmit="return confirm('Are you sure you want to permanently delete questioner \'{{ addslashes($displayName) }}\'? This profile has no submitted fatwas.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn-action-delete" 
                                            title="Delete Questioner Profile (No Fatwas)">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <div class="py-4">
                            <i class="bi bi-search fs-1 text-slate-300 d-block mb-2"></i>
                            <h6 class="fw-bold text-dark mb-1">No Questioners Found</h6>
                            <p class="small text-muted mb-3">No matching accounts found for your current search and filters.</p>
                            <button type="button" class="btn btn-sm btn-filter-action" onclick="if(window.liveSearchEngine) window.liveSearchEngine.clearSearch(); else window.location.href='{{ route('admin.questioners.index') }}';">
                                <i class="bi bi-x-circle me-1"></i> Clear Search
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 4. Clean Aligned Pagination Footer (Matches Screenshot Exactly) --}}
    <div class="p-3 px-4 bg-white border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-3" id="paginationContainer">
        {{-- Result Count: Showing 1–5 of 5 questioners (filtered from 293 total) --}}
        <div class="small text-muted" style="font-size: 0.84rem;">
            Showing <strong class="text-dark fw-bold">{{ $questioners->firstItem() ?? 0 }}–{{ $questioners->lastItem() ?? 0 }}</strong> of <strong class="text-dark fw-bold">{{ number_format($questioners->total()) }}</strong> questioners
            @if(request('search') || $questioners->total() < ($summaryStats['total'] ?? 0))
                (filtered from <strong class="text-dark fw-bold">{{ number_format($summaryStats['total'] ?? 0) }}</strong> total)
            @endif
        </div>

        {{-- Custom Pagination Navigation (< Previous  1  Next >) --}}
        <div class="d-flex align-items-center gap-1.5" id="paginationControls">
            {{-- Previous Button --}}
            @if ($questioners->onFirstPage())
                <span class="btn-pagination-nav disabled">
                    <i class="bi bi-chevron-left me-1" style="font-size: 0.70rem;"></i> Previous
                </span>
            @else
                <a href="{{ $questioners->previousPageUrl() }}" class="btn-pagination-nav" data-pagination-link>
                    <i class="bi bi-chevron-left me-1" style="font-size: 0.70rem;"></i> Previous
                </a>
            @endif

            {{-- Page Numbers --}}
            @for ($p = 1; $p <= $questioners->lastPage(); $p++)
                @if ($p == $questioners->currentPage())
                    <span class="btn-pagination-page active">{{ $p }}</span>
                @elseif ($p <= 2 || $p > $questioners->lastPage() - 2 || abs($p - $questioners->currentPage()) <= 1)
                    <a href="{{ $questioners->url($p) }}" class="btn-pagination-page" data-pagination-link>{{ $p }}</a>
                @elseif ($p == 3 && $questioners->lastPage() > 5)
                    <span class="text-muted px-1">...</span>
                @endif
            @endfor

            {{-- Next Button --}}
            @if ($questioners->hasMorePages())
                <a href="{{ $questioners->nextPageUrl() }}" class="btn-pagination-nav" data-pagination-link>
                    Next <i class="bi bi-chevron-right ms-1" style="font-size: 0.70rem;"></i>
                </a>
            @else
                <span class="btn-pagination-nav disabled">
                    Next <i class="bi bi-chevron-right ms-1" style="font-size: 0.70rem;"></i>
                </span>
            @endif
        </div>
    </div>
</div>

{{-- 5. Floating Bulk Actions Bar --}}
<div id="bulkActionBar" class="bulk-action-bar">
    <div class="d-flex align-items-center gap-2">
        <span class="badge rounded-pill bg-primary px-2.5 py-1" id="bulkSelectedCount">0</span>
        <span class="small fw-semibold text-light">Questioners selected</span>
    </div>
    <div class="vr bg-secondary opacity-50" style="height: 20px;"></div>
    <div class="d-flex align-items-center gap-2">
        <form method="POST" action="{{ route('admin.questioners.export-selected') }}" id="bulkExportForm" class="d-inline">
            @csrf
            <input type="hidden" name="selected_ids" id="bulkExportIds" value="">
            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Selected CSV
            </button>
        </form>
        <form method="POST" action="{{ route('admin.questioners.bulk-delete') }}" id="bulkDeleteForm" class="d-inline" onsubmit="return confirm('Permanently delete selected questioner profile(s) with no fatwas? Accounts with existing fatwas will be safely skipped.');">
            @csrf
            @method('DELETE')
            <input type="hidden" name="selected_ids" id="bulkDeleteIds" value="">
            <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
                <i class="bi bi-trash3"></i> Delete Selected (No Fatwas)
            </button>
        </form>
        <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem;" onclick="clearSelection()">
            Deselect All
        </button>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin-live-search.js') }}"></script>
<script>
    // Global Checkbox Selection Handler
    function setupCheckboxHandlers() {
        const checkAll = document.getElementById('checkAllQuestioners');
        const checkboxes = document.querySelectorAll('.questioner-checkbox');
        const bulkBar = document.getElementById('bulkActionBar');
        const countBadge = document.getElementById('bulkSelectedCount');
        const exportIdsInput = document.getElementById('bulkExportIds');
        const bulkDeleteIdsInput = document.getElementById('bulkDeleteIds');

        if (!checkAll || !checkboxes.length) return;

        function updateSelectionState() {
            const checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);
            const count = checkedBoxes.length;
            const idList = checkedBoxes.map(cb => cb.value).join(',');

            if (countBadge) countBadge.textContent = count;
            if (exportIdsInput) exportIdsInput.value = idList;
            if (bulkDeleteIdsInput) bulkDeleteIdsInput.value = idList;

            checkboxes.forEach(cb => {
                const tr = document.getElementById(`row-${cb.value}`);
                if (tr) {
                    if (cb.checked) {
                        tr.classList.add('row-selected');
                    } else {
                        tr.classList.remove('row-selected');
                    }
                }
            });

            if (count === 0) {
                checkAll.checked = false;
                checkAll.indeterminate = false;
                if (bulkBar) bulkBar.classList.remove('show');
            } else if (count === checkboxes.length) {
                checkAll.checked = true;
                checkAll.indeterminate = false;
                if (bulkBar) bulkBar.classList.add('show');
            } else {
                checkAll.checked = false;
                checkAll.indeterminate = true;
                if (bulkBar) bulkBar.classList.add('show');
            }
        }

        checkAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = checkAll.checked);
            updateSelectionState();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateSelectionState);
        });

        window.clearSelection = function () {
            checkboxes.forEach(cb => cb.checked = false);
            checkAll.checked = false;
            checkAll.indeterminate = false;
            updateSelectionState();
        };
    }

    // Initialize Universal Live Search
    document.addEventListener('DOMContentLoaded', function () {
        setupCheckboxHandlers();

        window.liveSearchEngine = new AdminLiveSearch({
            form: '#filterForm',
            input: '#questionerLiveSearchInput',
            clearBtn: '#clearSearchBtn',
            spinner: '#liveSearchSpinner',
            searchIcon: '#liveSearchIcon',
            statusEl: '#liveSearchStatus',
            statusTextEl: '#liveStatusText',
            container: '#questionerTableContainer',
            debounceMs: 350,
            onUpdated: function () {
                setupCheckboxHandlers();
            }
        });

        // Per Page Density Change Listener
        const perPageSelect = document.getElementById('perPageSelect');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function () {
                if (window.liveSearchEngine) {
                    window.liveSearchEngine.executeSearch({ resetPage: true });
                } else {
                    document.getElementById('filterForm').submit();
                }
            });
        }
    });
</script>
@endpush

@endsection
