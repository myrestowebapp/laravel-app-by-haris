@extends('layouts.admin')
@section('title', 'System Settings & Control')
@section('topbar-title', 'System Settings & Control')
@section('topbar-icon', 'bi-gear-fill')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <span class="text-dark fw-semibold">Settings</span>
@endsection

@section('content')

{{-- Success / Error Flash Notifications --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-xs rounded-3 d-flex align-items-center gap-2 mb-4" role="alert" style="background: #ECFDF5; color: #065F46; border-left: 4px solid #10B981 !important;">
        <i class="bi bi-check-circle-fill fs-5 text-success"></i>
        <div class="fw-semibold">{{ session('success') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-xs rounded-3 mb-4" role="alert" style="background: #FEF2F2; color: #991B1B; border-left: 4px solid #EF4444 !important;">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
            <span class="fw-bold">Validation Attention Required</span>
        </div>
        <ul class="mb-0 small ps-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     ROW 1: INSTITUTIONAL CONFIGURATION OVERVIEW BANNER
═══════════════════════════════════════════════════════════════ --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="institutional-banner-card position-relative overflow-hidden">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 position-relative z-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="config-icon-box flex-shrink-0">
                        <i class="bi bi-gear-wide-connected fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">Institutional Configuration</h5>
                        <p class="text-muted small mb-0">Manage global system behavior, lunar calendar alignments, and public display preferences.</p>
                    </div>
                </div>

                {{-- Right Decorative Status Icon --}}
                <div class="d-none d-md-flex align-items-center justify-content-center config-gear-badge">
                    <i class="bi bi-gear-fill text-success fs-5"></i>
                </div>
            </div>

            {{-- Background Subtle Crescent & Mosque Vector Silhouette --}}
            <svg class="banner-vector-silhouette" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 120" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="islamicGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#10B981" stop-opacity="0.12" />
                        <stop offset="100%" stop-color="#059669" stop-opacity="0.04" />
                    </linearGradient>
                </defs>
                {{-- Crescent Moon --}}
                <path d="M 60 20 A 30 30 0 1 0 100 70 A 24 24 0 1 1 60 20 Z" fill="url(#islamicGrad)" />
                {{-- Stars --}}
                <circle cx="115" cy="22" r="2" fill="#10B981" fill-opacity="0.3" />
                <circle cx="130" cy="35" r="1.5" fill="#10B981" fill-opacity="0.25" />
                <circle cx="85" cy="15" r="1.5" fill="#10B981" fill-opacity="0.3" />
                {{-- Mosque Dome & Minarets Silhouette --}}
                <path d="M 170 120 L 170 85 Q 170 70 185 70 Q 200 70 200 85 L 200 120 Z" fill="url(#islamicGrad)" />
                <path d="M 205 120 L 205 60 Q 220 30 235 60 L 235 120 Z" fill="url(#islamicGrad)" />
                <path d="M 240 120 L 240 75 Q 240 65 255 65 Q 270 65 270 75 L 270 120 Z" fill="url(#islamicGrad)" />
                <path d="M 280 120 L 280 40 L 285 25 L 290 40 L 290 120 Z" fill="url(#islamicGrad)" />
                <path d="M 300 120 L 300 50 Q 325 15 350 50 L 350 120 Z" fill="url(#islamicGrad)" />
                <path d="M 360 120 L 360 35 L 365 20 L 370 35 L 370 120 Z" fill="url(#islamicGrad)" />
            </svg>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     ROW 2: MOON SIGHTING MANAGER & PUBLIC DISPLAY CONTROL
═══════════════════════════════════════════════════════════════ --}}
<div class="row g-4 mb-4">
    {{-- Left Card: Moon Sighting & Hijri Manager (60%) --}}
    <div class="col-lg-7">
        <div class="modern-card h-100 overflow-hidden d-flex flex-column">
            {{-- Header --}}
            <div class="settings-card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon-box bg-success bg-opacity-10 text-success">
                        <i class="bi bi-moon-stars-fill fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Moon Sighting &amp; Hijri Manager</h6>
                        <div class="text-muted text-xs mt-0.5">Configure Hijri date calculation and system alignment for public display.</div>
                    </div>
                </div>
                <span class="badge active-status-pill">
                    <span class="active-dot-green"></span> Active
                </span>
            </div>

            {{-- Body --}}
            <div class="settings-card-body flex-grow-1 d-flex flex-column justify-content-between">
                <div>
                    {{-- Highlight Emerald Container (Current Calculated Date) --}}
                    <div class="hijri-highlight-box mb-4">
                        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                            {{-- Main Date --}}
                            <div class="d-flex align-items-center gap-3">
                                <div class="calendar-icon-tile flex-shrink-0">
                                    <i class="bi bi-calendar3 fs-4 text-success"></i>
                                </div>
                                <div>
                                    <div class="text-secondary small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                        Current Calculated Date (Adjusted)
                                    </div>
                                    <div class="hijri-date-title">{{ $currentHijri }}</div>
                                    <div class="text-muted small mt-1">
                                        {{ now()->format('d F Y (l)') }}
                                    </div>
                                </div>
                            </div>

                            {{-- 3 Mini Metadata Tiles --}}
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                {{-- Lunar Month --}}
                                <div class="mini-meta-tile">
                                    <i class="bi bi-moon-stars text-primary fs-6"></i>
                                    <div>
                                        <div class="meta-label">Lunar Month</div>
                                        <div class="meta-val">{{ $lunarMonth ?? 'Jumada al-Awwal' }}</div>
                                    </div>
                                </div>
                                {{-- Gregorian Date --}}
                                <div class="mini-meta-tile">
                                    <i class="bi bi-calendar-event text-primary fs-6"></i>
                                    <div>
                                        <div class="meta-label">Gregorian Date</div>
                                        <div class="meta-val">{{ now()->format('d M Y') }}</div>
                                    </div>
                                </div>
                                {{-- Day --}}
                                <div class="mini-meta-tile">
                                    <i class="bi bi-calendar-day text-primary fs-6"></i>
                                    <div>
                                        <div class="meta-label">Day</div>
                                        <div class="meta-val">{{ now()->format('l') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Manual Date Adjustment Form --}}
                    <form action="{{ route('admin.settings.update') }}" method="POST" id="hijriAdjustmentForm">
                        @csrf
                        <label class="form-label fw-bold text-dark small mb-2">
                            Manual Date Adjustment (Days)
                        </label>
                        <div class="row g-2 align-items-center">
                            <div class="col-sm-8">
                                <div class="position-relative">
                                    <select name="hijri_adjustment" class="form-select adjustment-select">
                                        <option value="-2" {{ $hijriAdjustment == -2 ? 'selected' : '' }}>-2 Days (Earlier Moon Sighting)</option>
                                        <option value="-1" {{ $hijriAdjustment == -1 ? 'selected' : '' }}>-1 Day (Earlier Moon Sighting)</option>
                                        <option value="0"  {{ $hijriAdjustment == 0  ? 'selected' : '' }}>0 (Standard Calculation)</option>
                                        <option value="1"  {{ $hijriAdjustment == 1  ? 'selected' : '' }}>+1 Day (Later Moon Sighting)</option>
                                        <option value="2"  {{ $hijriAdjustment == 2  ? 'selected' : '' }}>+2 Days (Later Moon Sighting)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <button type="submit" class="btn btn-apply-shift w-100">
                                    <i class="bi bi-check2-circle fs-6"></i>
                                    <span>Apply Shift</span>
                                </button>
                            </div>
                        </div>
                        <div class="text-muted text-xs mt-2 d-flex align-items-center gap-1.5">
                            <i class="bi bi-info-circle text-primary"></i>
                            <span>This setting aligns the portal's calendar with local moon sightings for Ramzan and Eid announcements.</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Card: Public Display Control (40%) --}}
    <div class="col-lg-5">
        <div class="modern-card h-100 overflow-hidden d-flex flex-column">
            {{-- Header --}}
            <div class="settings-card-header d-flex align-items-center gap-3">
                <div class="header-icon-box bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-eye-fill fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Public Display Control</h6>
                    <div class="text-muted text-xs mt-0.5">Determine if the Hijri date should be visible to all visitors on the public header.</div>
                </div>
            </div>

            {{-- Body --}}
            <div class="settings-card-body flex-grow-1 d-flex flex-column justify-content-between">
                <div>
                    {{-- Toggle Switch --}}
                    <form action="{{ route('admin.settings.update') }}" method="POST" id="hijriToggleForm">
                        @csrf
                        <input type="hidden" name="show_hijri_public" value="0">
                        <div class="d-flex align-items-start gap-3 mb-4">
                            <div class="form-check form-switch pt-1 ps-0">
                                <input class="form-check-input ios-switch" type="checkbox" name="show_hijri_public" value="1" id="hijriSwitch" 
                                       {{ $showHijriPublic ? 'checked' : '' }}
                                       onchange="document.getElementById('hijriToggleForm').submit();">
                            </div>
                            <div>
                                <label class="fw-bold text-dark mb-1 cursor-pointer" for="hijriSwitch" style="font-size: 0.95rem;">
                                    Enable Arabic Calendar for Public
                                </label>
                                <div class="text-muted text-xs">
                                    When enabled, the Hijri date will be displayed in the public website header for all visitors.
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Live Preview Box (Public Header Simulation) --}}
                    <div class="public-preview-wrapper mt-3">
                        <div class="d-flex align-items-center gap-1.5 text-xs text-secondary fw-bold text-uppercase mb-2" style="letter-spacing: 0.5px;">
                            <i class="bi bi-eye text-primary"></i>
                            <span>Live Preview (Public Header)</span>
                        </div>
                        <div class="public-preview-pill-box">
                            <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">
                                {{-- Clock --}}
                                <div class="preview-item">
                                    <i class="bi bi-clock text-primary me-1"></i>
                                    <span id="livePreviewClock">{{ now()->format('h:i:s A \I\S\T') }}</span>
                                </div>
                                <span class="preview-divider"></span>
                                {{-- Gregorian Date --}}
                                <div class="preview-item">
                                    <i class="bi bi-calendar3 text-primary me-1"></i>
                                    <span>{{ now()->format('d M, Y') }}</span>
                                </div>
                                <span class="preview-divider"></span>
                                {{-- Hijri Date --}}
                                <div class="preview-item">
                                    <i class="bi bi-moon-stars text-warning me-1"></i>
                                    <span class="fw-bold text-dark">{{ $currentHijri }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-muted text-xs text-center mt-2">
                            This is how the date will appear on the public website header.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     ROW 3: ADMIN LOGIN CREDENTIALS (SECURITY CARD)
═══════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-12">
        <div class="security-card overflow-hidden">
            {{-- Header --}}
            <div class="settings-card-header d-flex align-items-center gap-3">
                <div class="header-icon-box bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-shield-lock-fill fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Admin Login Credentials</h6>
                    <div class="text-muted text-xs mt-0.5">Update the primary administrator account's email address and sign-in password.</div>
                </div>
            </div>

            {{-- Form Body --}}
            <div class="settings-card-body">
                <form action="{{ route('admin.settings.update') }}" method="POST" id="adminCredentialsForm">
                    @csrf
                    <div class="row g-3 mb-4">
                        {{-- Admin Login Email --}}
                        <div class="col-lg-4">
                            <label class="form-label fw-bold text-dark small mb-1.5 d-flex align-items-center gap-1.5">
                                <i class="bi bi-envelope text-muted"></i>
                                <span>Admin Login Email</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted px-3">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" name="admin_email" class="form-control border-start-0 py-2.5 text-sm fw-medium @error('admin_email') is-invalid @enderror"
                                       value="{{ old('admin_email', auth()->user()->email) }}" placeholder="admin@darulifta.com" required>
                            </div>
                        </div>

                        {{-- New Password --}}
                        <div class="col-lg-4">
                            <label class="form-label fw-bold text-dark small mb-1.5 d-flex align-items-center gap-1.5">
                                <i class="bi bi-key text-muted"></i>
                                <span>New Password (Min 8 chars)</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted px-3">
                                    <i class="bi bi-key"></i>
                                </span>
                                <input type="password" name="admin_password" id="inputNewPassword" class="form-control border-start-0 border-end-0 py-2.5 text-sm @error('admin_password') is-invalid @enderror"
                                       placeholder="••••••••" autocomplete="new-password">
                                <button type="button" class="btn btn-outline-secondary border-start-0 text-muted px-3" onclick="togglePasswordVisibility('inputNewPassword', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Confirm New Password --}}
                        <div class="col-lg-4">
                            <label class="form-label fw-bold text-dark small mb-1.5 d-flex align-items-center gap-1.5">
                                <i class="bi bi-lock text-muted"></i>
                                <span>Confirm New Password</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted px-3">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" name="admin_password_confirmation" id="inputConfirmPassword" class="form-control border-start-0 border-end-0 py-2.5 text-sm"
                                       placeholder="••••••••" autocomplete="new-password">
                                <button type="button" class="btn btn-outline-secondary border-start-0 text-muted px-3" onclick="togglePasswordVisibility('inputConfirmPassword', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Bottom Action Row: Security Warning & Submit Button --}}
                    <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-3 pt-2">
                        {{-- Security Notice --}}
                        <div class="security-notice-box flex-grow-1">
                            <i class="bi bi-shield-exclamation text-danger fs-5 flex-shrink-0"></i>
                            <span>For security reasons, use a strong password with at least 8 characters including letters, numbers, and special characters.</span>
                        </div>

                        {{-- Update Button --}}
                        <button type="submit" class="btn btn-danger-action flex-shrink-0">
                            <i class="bi bi-shield-check fs-5"></i>
                            <span>Update Admin Credentials</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* ── Banner Card ── */
    .institutional-banner-card {
        padding: 1.5rem 1.75rem !important;
        background: #ffffff !important;
        border-radius: 14px !important;
        border: 1px solid var(--card-border, #E2E8F0) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
    .config-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: #EFF6FF;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .config-gear-badge {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: #F0FDF4;
        border: 1.5px solid #DCFCE7;
    }
    .banner-vector-silhouette {
        position: absolute;
        right: 0;
        bottom: 0;
        height: 100%;
        width: 380px;
        pointer-events: none;
        z-index: 1;
    }

    /* ── Settings Card Headers & Bodies ── */
    .settings-card-header {
        padding: 1.25rem 1.75rem !important;
        background: #ffffff;
        border-bottom: 1px solid var(--card-border, #E2E8F0);
    }
    .settings-card-body {
        padding: 1.75rem !important;
        background: #ffffff;
    }
    .header-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* ── Active Status Pill ── */
    .active-status-pill {
        background: #ECFDF5;
        color: #059669;
        font-weight: 700;
        font-size: 0.76rem;
        border-radius: 20px;
        padding: 5px 12px;
        border: 1px solid #A7F3D0;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .active-dot-green {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #10B981;
        display: inline-block;
    }

    /* ── Emerald Hijri Date Highlight Box ── */
    .hijri-highlight-box {
        background: #F0FDF4;
        border: 1.5px solid #DCFCE7;
        border-radius: 14px;
        padding: 1.25rem 1.5rem !important;
    }
    .calendar-icon-tile {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid #DCFCE7;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .hijri-date-title {
        font-size: 1.45rem;
        font-weight: 800;
        color: #059669;
        line-height: 1.2;
    }

    /* ── Mini Metadata Tiles in Hijri Box ── */
    .mini-meta-tile {
        background: #ffffff;
        border: 1px solid #DCFCE7;
        border-radius: 10px;
        padding: 6px 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .mini-meta-tile .meta-label {
        font-size: 0.68rem;
        color: #64748B;
        text-transform: uppercase;
        font-weight: 600;
        line-height: 1;
    }
    .mini-meta-tile .meta-val {
        font-size: 0.78rem;
        color: #1E293B;
        font-weight: 700;
        margin-top: 2px;
        line-height: 1.2;
    }

    /* ── Form Controls & Buttons ── */
    .adjustment-select {
        border-color: #CBD5E1;
        border-radius: 10px;
        padding: 0.65rem 1rem;
        font-size: 0.88rem;
        font-weight: 600;
        color: #1E293B;
    }
    .adjustment-select:focus {
        border-color: #059669;
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12);
    }
    .btn-apply-shift {
        background: #059669;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.88rem;
        border-radius: 10px;
        padding: 0.65rem 1.25rem;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: background 0.15s ease, transform 0.15s ease;
    }
    .btn-apply-shift:hover {
        background: #047857;
        color: #ffffff;
        transform: translateY(-1px);
    }

    /* ── iOS Switch Toggle ── */
    .ios-switch {
        width: 48px !important;
        height: 26px !important;
        cursor: pointer;
        background-color: #CBD5E1;
        border-color: transparent;
        transition: background-color 0.2s ease, background-position 0.2s ease;
    }
    .ios-switch:checked {
        background-color: #2563EB !important;
        border-color: #2563EB !important;
    }

    /* ── Public Preview Box ── */
    .public-preview-wrapper {
        border-top: 1px dashed #E2E8F0;
        padding-top: 1.25rem;
    }
    .public-preview-pill-box {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 1rem 1.25rem;
    }
    .preview-item {
        font-size: 0.84rem;
        font-weight: 600;
        color: #334155;
        display: inline-flex;
        align-items: center;
    }
    .preview-divider {
        width: 1px;
        height: 16px;
        background: #CBD5E1;
        display: inline-block;
    }

    /* ── Security Card (Admin Login Credentials) ── */
    .security-card {
        background: #ffffff !important;
        border-radius: 14px !important;
        border: 1px solid var(--card-border, #E2E8F0) !important;
        border-left: 4px solid #EF4444 !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
    .security-notice-box {
        background: #FEF2F2;
        border: 1px solid #FEE2E2;
        color: #DC2626;
        font-size: 0.78rem;
        font-weight: 600;
        border-radius: 10px;
        padding: 0.75rem 1.15rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .btn-danger-action {
        background: #DC2626;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.88rem;
        border-radius: 10px;
        padding: 0.75rem 1.75rem;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background 0.15s ease, transform 0.15s ease;
    }
    .btn-danger-action:hover {
        background: #B91C1C;
        color: #ffffff;
        transform: translateY(-1px);
    }

    .cursor-pointer { cursor: pointer; }
    .text-xs { font-size: 0.75rem !important; }

    @media (max-width: 576px) {
        .settings-card-header, .settings-card-body {
            padding: 1.15rem !important;
        }
        .hijri-highlight-box {
            padding: 1rem !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Live Clock Update in Preview Box
    function updatePreviewClock() {
        const clockEl = document.getElementById('livePreviewClock');
        if (clockEl) {
            const now = new Date();
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? String(hours).padStart(2, '0') : '12';
            clockEl.textContent = `${hours}:${minutes}:${seconds} ${ampm} IST`;
        }
    }
    setInterval(updatePreviewClock, 1000);

    // Show/Hide Password Toggle
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
</script>
@endpush

@endsection
