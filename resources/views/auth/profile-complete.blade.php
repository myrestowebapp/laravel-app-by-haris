@extends('layouts.public')
@section('title', 'Complete Your Profile')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
<style>
    .ts-wrapper { padding: 0; }
    .ts-control {
        border: 1px solid #ced4da;
        border-radius: 0.5rem;
        min-height: calc(1.5em + 0.75rem + 2px);
        padding: 0.45rem 0.75rem;
        font-size: 0.95rem;
        line-height: 1.5;
        color: #212529;
        background-color: #fff;
        box-shadow: none;
        cursor: text;
    }
    .ts-control:focus-within {
        border-color: #1a7a4a;
        box-shadow: 0 0 0 0.25rem rgba(26, 122, 74, 0.2);
    }
    .ts-dropdown {
        z-index: 9999;
        border-radius: 0.5rem;
        border-color: #ced4da;
        box-shadow: 0 4px 16px rgba(0,0,0,.12);
    }
    .ts-dropdown .ts-dropdown-content {
        max-height: 200px;
    }
    .ts-dropdown .option.selected,
    .ts-dropdown .option:hover,
    .ts-dropdown .active {
        background-color: #1a7a4a !important;
        color: #fff !important;
    }
    .ts-wrapper.disabled .ts-control {
        background-color: #e9ecef;
        pointer-events: none;
        opacity: 0.7;
    }
    .text-green {
        color: #1a7a4a !important;
    }
    .btn-green {
        background-color: #1a7a4a;
        border-color: #1a7a4a;
        color: white;
    }
    .btn-green:hover {
        background-color: #15613a;
        border-color: #15613a;
        color: white;
    }

    /* ─── Modern Phone Input Component (Wazpp UI Specification) ─── */
    .phone-input-field-wrapper {
        position: relative;
    }
    .phone-input-box {
        position: relative;
        display: flex;
        align-items: center;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    }
    .phone-input-box:focus-within {
        border-color: #1a7a4a;
        box-shadow: 0 0 0 4px rgba(26, 122, 74, 0.15);
    }
    .phone-input-box.is-complete {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }
    .phone-input-box.is-invalid {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
    }

    /* Country Trigger Button */
    .btn-phone-country-trigger {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.68rem 0.85rem;
        background: #f8fafc;
        border: none;
        border-right: 1.5px solid #e2e8f0;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
        font-size: 0.88rem;
        font-weight: 700;
        color: #1e293b;
        cursor: pointer;
        transition: background-color 0.15s ease;
        user-select: none;
        flex-shrink: 0;
    }
    .btn-phone-country-trigger:hover {
        background: #f1f5f9;
    }
    .phone-country-flag {
        font-size: 1.15rem;
        line-height: 1;
    }
    .phone-country-code {
        font-weight: 800;
        font-size: 0.85rem;
        color: #0f172a;
    }
    .phone-dial-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.85rem;
        color: #475569;
        font-weight: 600;
    }
    .phone-chevron-icon {
        font-size: 0.70rem;
        color: #94a3b8;
        transition: transform 0.2s ease;
    }
    .phone-chevron-icon.is-open {
        transform: rotate(180deg);
    }

    /* Native Phone Number Input */
    .phone-number-native-input {
        flex: 1;
        width: 100%;
        border: none;
        outline: none;
        background: transparent;
        padding: 0.65rem 0.85rem;
        font-size: 1.05rem;
        font-weight: 600;
        color: #0f172a;
        letter-spacing: 0.5px;
    }
    .phone-number-native-input::placeholder {
        color: #94a3b8;
        font-weight: 400;
        letter-spacing: normal;
    }

    /* Digits Badge */
    .digits-pill-counter {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.22rem 0.65rem;
        border-radius: 9999px;
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }
    .digits-pill-counter.is-matched {
        background: #ecfdf5;
        color: #047857;
        border-color: #a7f3d0;
    }

    /* Verification indicator on the right of input */
    .phone-status-indicator {
        padding-right: 0.85rem;
        display: flex;
        align-items: center;
        pointer-events: none;
    }
    .check-circle-green {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background-color: #10b981;
        color: #ffffff;
        font-size: 11px;
        font-weight: bold;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.25);
    }

    /* Popover Country Dropdown */
    .phone-country-popover {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        width: 100%;
        max-width: 360px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 14px 38px rgba(15, 23, 42, 0.12), 0 4px 12px rgba(15, 23, 42, 0.06);
        z-index: 99999;
        overflow: hidden;
        animation: popoverDrop 0.15s ease-out;
    }
    @keyframes popoverDrop {
        from { opacity: 0; transform: translateY(-6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .phone-popover-search-wrap {
        padding: 0.65rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .phone-search-input {
        width: 100%;
        padding: 0.45rem 0.65rem 0.45rem 2rem;
        font-size: 0.82rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        outline: none;
        background-color: #ffffff;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: 8px center;
    }
    .phone-search-input:focus {
        border-color: #1a7a4a;
        box-shadow: 0 0 0 2px rgba(26, 122, 74, 0.15);
    }
    .phone-country-items-scroll {
        max-height: 240px;
        overflow-y: auto;
        padding: 4px;
    }
    .phone-country-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 0.55rem 0.75rem;
        border: none;
        background: transparent;
        border-radius: 8px;
        font-size: 0.84rem;
        color: #334155;
        cursor: pointer;
        text-align: left;
        transition: background-color 0.12s ease;
    }
    .phone-country-item:hover {
        background-color: #f0fdf4;
        color: #166534;
    }
    .phone-country-item.is-selected {
        background-color: #dcfce7;
        color: #15803d;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
<div class="container py-5" style="max-width: 650px;">
    @if($hasPendingQuestion)
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4" style="background-color: #eaf6ef; color: #1a7a4a;">
            <i class="bi bi-check-circle-fill me-3 fs-4 text-green"></i>
            <div>
                <strong class="d-block mb-0.5">Draft Question Saved!</strong> 
                Please provide your profile details below to complete your question submission.
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show mb-4 border-0 shadow-sm">
            <i class="bi bi-info-circle-fill me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow p-4 rounded-3">
        <div class="text-center mb-4">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:60px;height:60px;background:linear-gradient(135deg,#1a7a4a,#c9a84c);">
                <i class="bi bi-person-check text-white" style="font-size:1.6rem;"></i>
            </div>
            <h4 class="fw-bold text-green">Complete Your Profile</h4>
            <span class="badge bg-danger px-3 py-2 mt-2"><i class="bi bi-shield-lock me-1"></i> Mandatory Information</span>
            <p class="text-muted small mt-2">Mobile number and Country are strictly mandatory before submitting a question to ensure authentic, location-relevant rulings.</p>
        </div>

        <form method="POST" action="{{ route('profile.complete.store') }}" id="completeProfileForm">
            @csrf
            
            <div class="row g-3 mb-4">
                {{-- ── Wazpp-Style ISD Code + Dynamic Digits Mobile Input ── --}}
                <div class="col-12">
                    <div class="phone-input-field-wrapper" id="phoneWrapperContainer">
                        {{-- Top Header Row: Label & Digits Pill --}}
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <label class="form-label fw-bold text-uppercase small text-secondary mb-0" style="letter-spacing: 0.5px; font-size: 0.76rem;">
                                WhatsApp Mobile Number <span class="text-danger">*</span>
                            </label>
                            <div>
                                <span id="phoneDigitsPill" class="digits-pill-counter">
                                    <span id="phoneDigitsEntered">0</span> / <span id="phoneDigitsAllowed">10</span> digits
                                </span>
                            </div>
                        </div>

                        {{-- Input Group Box --}}
                        <div class="phone-input-box" id="phoneInputBox">
                            {{-- Country Code Trigger --}}
                            <button type="button" class="btn-phone-country-trigger" id="phoneCountryTrigger" title="Click to change country code">
                                <span id="phoneFlag" class="phone-country-flag">🇮🇳</span>
                                <span id="phoneIsoCode" class="phone-country-code">IN</span>
                                <span id="phoneDialCode" class="phone-dial-code">+91</span>
                                <i class="bi bi-chevron-down phone-chevron-icon ms-1" id="phoneChevron"></i>
                            </button>

                            {{-- Formatted Numeric Field --}}
                            <input type="tel"
                                   id="phoneNativeInput"
                                   class="phone-number-native-input"
                                   placeholder="98765 43210"
                                   inputmode="numeric"
                                   autocomplete="tel-national"
                                   required>

                            {{-- Right Status Indicator (WhatsApp or Check) --}}
                            <div class="phone-status-indicator">
                                <span id="phoneCheckCircle" class="check-circle-green d-none">
                                    <i class="bi bi-check-lg"></i>
                                </span>
                                <span id="phoneChatIcon" class="text-muted opacity-50" title="WhatsApp number required">
                                    <i class="bi bi-chat-dots-fill" style="font-size: 1.15rem;"></i>
                                </span>
                            </div>
                        </div>

                        {{-- Hidden inputs submitted with POST --}}
                        <input type="hidden" name="mobile" id="phoneHiddenMobile" value="{{ old('mobile', Auth::user()->mobile) }}" required>
                        <input type="hidden" name="country_code" id="phoneHiddenCountryCode" value="{{ old('country_code', Auth::user()->country_code ?? '+91') }}">

                        {{-- Searchable Country Popover Dropdown --}}
                        <div class="phone-country-popover d-none" id="phoneCountryPopover">
                            <div class="phone-popover-search-wrap">
                                <input type="text"
                                       id="phoneSearchInput"
                                       class="phone-search-input"
                                       placeholder="Search country or code..."
                                       autocomplete="off">
                            </div>
                            <div class="phone-country-items-scroll" id="phoneCountryItemsList">
                                {{-- Rendered dynamically --}}
                            </div>
                        </div>

                        {{-- Helper Subtitle Footer --}}
                        <div class="d-flex align-items-center justify-content-between mt-1 text-muted" style="font-size: 0.72rem;">
                            <span>Standard mobile number format for <strong id="phoneCountryLabel" class="text-dark">India</strong></span>
                            <span class="text-success fw-semibold d-inline-flex align-items-center gap-1">
                                <i class="bi bi-whatsapp"></i> WhatsApp enabled
                            </span>
                        </div>

                        @error('mobile')
                            <div class="text-danger small mt-1 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-muted">Gender <span class="text-danger">*</span></label>
                    <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                        <option value="">Select Gender</option>
                        <option value="Male" {{ old('gender', Auth::user()->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender', Auth::user()->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-muted">Madhab Preference <span class="text-danger">*</span></label>
                    <select name="madhab_id" class="form-select @error('madhab_id') is-invalid @enderror" required>
                        <option value="">Select Madhab...</option>
                        @foreach($madhabs as $m)
                            <option value="{{ $m->id }}" {{ old('madhab_id', $preferredMadhabId) == $m->id ? 'selected' : '' }}>
                                {{ $m->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('madhab_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-muted">Address Line 1 <span class="text-danger">*</span></label>
                    <input type="text" name="address_line_1" class="form-control @error('address_line_1') is-invalid @enderror" value="{{ old('address_line_1', Auth::user()->address_line_1) }}" placeholder="Street address, P.O. Box" required>
                    @error('address_line_1')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-muted">Address Line 2 <span class="text-muted">(Optional)</span></label>
                    <input type="text" name="address_line_2" class="form-control @error('address_line_2') is-invalid @enderror" value="{{ old('address_line_2', Auth::user()->address_line_2) }}" placeholder="Apartment, suite, unit, etc.">
                    @error('address_line_2')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small text-muted">Country <span class="text-danger">*</span></label>
                    <select name="country" id="profile_country" class="form-select @error('country') is-invalid @enderror" required>
                        <option value="">Select Country</option>
                        <option value="India" {{ old('country', Auth::user()->country) == 'India' ? 'selected' : '' }}>India</option>
                        <option value="Saudi Arabia" {{ old('country', Auth::user()->country) == 'Saudi Arabia' ? 'selected' : '' }}>Saudi Arabia</option>
                        <option value="United Arab Emirates" {{ old('country', Auth::user()->country) == 'United Arab Emirates' ? 'selected' : '' }}>United Arab Emirates</option>
                        <option value="Qatar" {{ old('country', Auth::user()->country) == 'Qatar' ? 'selected' : '' }}>Qatar</option>
                        <option value="Oman" {{ old('country', Auth::user()->country) == 'Oman' ? 'selected' : '' }}>Oman</option>
                        <option value="Kuwait" {{ old('country', Auth::user()->country) == 'Kuwait' ? 'selected' : '' }}>Kuwait</option>
                        <option value="Bahrain" {{ old('country', Auth::user()->country) == 'Bahrain' ? 'selected' : '' }}>Bahrain</option>
                        <option value="Pakistan" {{ old('country', Auth::user()->country) == 'Pakistan' ? 'selected' : '' }}>Pakistan</option>
                        <option value="United Kingdom" {{ old('country', Auth::user()->country) == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                        <option value="United States" {{ old('country', Auth::user()->country) == 'United States' ? 'selected' : '' }}>United States</option>
                        <option value="Malaysia" {{ old('country', Auth::user()->country) == 'Malaysia' ? 'selected' : '' }}>Malaysia</option>
                        @if(Auth::user()->country && !in_array(Auth::user()->country, ['India','Saudi Arabia','United Arab Emirates','Qatar','Oman','Kuwait','Bahrain','Pakistan','United Kingdom','United States','Malaysia','Not set']))
                            <option value="{{ Auth::user()->country }}" selected>{{ Auth::user()->country }}</option>
                        @endif
                    </select>
                    @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small text-muted">State <span class="text-danger">*</span></label>
                    <select name="state" id="profile_state" class="form-select @error('state') is-invalid @enderror" required disabled>
                        <option value="">Select State</option>
                    </select>
                    @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small text-muted">District <span class="text-danger">*</span></label>
                    <select name="district" id="profile_district" class="form-select @error('district') is-invalid @enderror" required disabled>
                        <option value="">Select District</option>
                    </select>
                    @error('district')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold small text-muted">Mother Language <span class="text-muted">(Optional)</span></label>
                    <input type="text" name="mother_language" class="form-control @error('mother_language') is-invalid @enderror" value="{{ old('mother_language', Auth::user()->mother_language) }}" placeholder="e.g. Urdu, Arabic, English, Malayalam">
                    @error('mother_language')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex flex-column gap-2">
                <button type="submit" class="btn btn-green w-100 py-2.5 fw-bold shadow-sm">
                    @if($hasPendingQuestion)
                        <i class="bi bi-check2-circle me-1"></i> Save Profile & Submit Question
                    @else
                        <i class="bi bi-save me-1"></i> Save Profile Details
                    @endif
                </button>

                <a href="{{ route('home') }}" class="btn btn-link text-muted small text-decoration-none text-center">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    // ─── Wazpp Country & Allowed Digits Definition (from Wazpp Client) ───
    const COUNTRIES_PHONE_DATA = [
        // Primary
        { code: 'IN', name: 'India', dialCode: '+91', flag: '🇮🇳', mobileDigits: 10, formatExample: '98765 43210' },

        // GCC / Middle East
        { code: 'AE', name: 'United Arab Emirates', dialCode: '+971', flag: '🇦🇪', mobileDigits: 9, formatExample: '50 123 4567' },
        { code: 'SA', name: 'Saudi Arabia', dialCode: '+966', flag: '🇸🇦', mobileDigits: 9, formatExample: '50 123 4567' },
        { code: 'QA', name: 'Qatar', dialCode: '+974', flag: '🇶🇦', mobileDigits: 8, formatExample: '3312 3456' },
        { code: 'KW', name: 'Kuwait', dialCode: '+965', flag: '🇰🇼', mobileDigits: 8, formatExample: '9123 4567' },
        { code: 'OM', name: 'Oman', dialCode: '+968', flag: '🇴🇲', mobileDigits: 8, formatExample: '9123 4567' },
        { code: 'BH', name: 'Bahrain', dialCode: '+973', flag: '🇧🇭', mobileDigits: 8, formatExample: '3612 3456' },

        // South Asia & Neighbours
        { code: 'PK', name: 'Pakistan', dialCode: '+92', flag: '🇵🇰', mobileDigits: 10, formatExample: '301 2345678' },
        { code: 'BD', name: 'Bangladesh', dialCode: '+880', flag: '🇧🇩', mobileDigits: 10, formatExample: '1712 345678' },
        { code: 'LK', name: 'Sri Lanka', dialCode: '+94', flag: '🇱🇰', mobileDigits: 9, formatExample: '71 234 5678' },
        { code: 'NP', name: 'Nepal', dialCode: '+977', flag: '🇳🇵', mobileDigits: 10, formatExample: '984 1234567' },
        { code: 'MV', name: 'Maldives', dialCode: '+960', flag: '🇲🇻', mobileDigits: 7, formatExample: '712 3456' },
        { code: 'BT', name: 'Bhutan', dialCode: '+975', flag: '🇧🇹', mobileDigits: 8, formatExample: '17 12 34 56' },
        { code: 'MM', name: 'Myanmar', dialCode: '+95', flag: '🇲🇲', mobileDigits: 10, formatExample: '9 1234 5678' },

        // Southeast Asia
        { code: 'MY', name: 'Malaysia', dialCode: '+60', flag: '🇲🇾', mobileDigits: 10, formatExample: '12 345 6789' },
        { code: 'SG', name: 'Singapore', dialCode: '+65', flag: '🇸🇬', mobileDigits: 8, formatExample: '8123 4567' },
        { code: 'ID', name: 'Indonesia', dialCode: '+62', flag: '🇮🇩', mobileDigits: 10, formatExample: '812 3456 7890' },
        { code: 'TH', name: 'Thailand', dialCode: '+66', flag: '🇹🇭', mobileDigits: 9, formatExample: '81 234 5678' },
        { code: 'PH', name: 'Philippines', dialCode: '+63', flag: '🇵🇭', mobileDigits: 10, formatExample: '917 123 4567' },

        // Global / Western
        { code: 'GB', name: 'United Kingdom', dialCode: '+44', flag: '🇬🇧', mobileDigits: 10, formatExample: '7911 123456' },
        { code: 'US', name: 'United States', dialCode: '+1', flag: '🇺🇸', mobileDigits: 10, formatExample: '555 123 4567' },
        { code: 'CA', name: 'Canada', dialCode: '+1', flag: '🇨🇦', mobileDigits: 10, formatExample: '555 123 4567' },
        { code: 'AU', name: 'Australia', dialCode: '+61', flag: '🇦🇺', mobileDigits: 9, formatExample: '412 345 678' },
        { code: 'NZ', name: 'New Zealand', dialCode: '+64', flag: '🇳🇿', mobileDigits: 9, formatExample: '21 123 4567' },
        { code: 'DE', name: 'Germany', dialCode: '+49', flag: '🇩🇪', mobileDigits: 10, formatExample: '151 23456789' },
        { code: 'FR', name: 'France', dialCode: '+33', flag: '🇫🇷', mobileDigits: 9, formatExample: '6 12 34 56 78' },
        { code: 'IE', name: 'Ireland', dialCode: '+353', flag: '🇮🇪', mobileDigits: 9, formatExample: '85 123 4567' },
        { code: 'IT', name: 'Italy', dialCode: '+39', flag: '🇮🇹', mobileDigits: 10, formatExample: '320 1234567' },
        { code: 'NL', name: 'Netherlands', dialCode: '+31', flag: '🇳🇱', mobileDigits: 9, formatExample: '6 12345678' },
        { code: 'ES', name: 'Spain', dialCode: '+34', flag: '🇪🇸', mobileDigits: 9, formatExample: '612 34 56 78' },
        { code: 'TR', name: 'Turkey', dialCode: '+90', flag: '🇹🇷', mobileDigits: 10, formatExample: '501 234 5678' },
        { code: 'EG', name: 'Egypt', dialCode: '+20', flag: '🇪🇬', mobileDigits: 10, formatExample: '10 1234 5678' },
        { code: 'ZA', name: 'South Africa', dialCode: '+27', flag: '🇿🇦', mobileDigits: 9, formatExample: '82 123 4567' },
        { code: 'NG', name: 'Nigeria', dialCode: '+234', flag: '🇳🇬', mobileDigits: 10, formatExample: '802 123 4567' },
        { code: 'KE', name: 'Kenya', dialCode: '+254', flag: '🇰🇪', mobileDigits: 9, formatExample: '712 345678' },
        { code: 'MA', name: 'Morocco', dialCode: '+212', flag: '🇲🇦', mobileDigits: 9, formatExample: '612 345678' },
        { code: 'JO', name: 'Jordan', dialCode: '+962', flag: '🇯🇴', mobileDigits: 9, formatExample: '7 9123 4567' },
        { code: 'LB', name: 'Lebanon', dialCode: '+961', flag: '🇱🇧', mobileDigits: 8, formatExample: '71 123 456' },
        { code: 'IQ', name: 'Iraq', dialCode: '+964', flag: '🇮🇶', mobileDigits: 10, formatExample: '790 123 4567' },
        { code: 'YE', name: 'Yemen', dialCode: '+967', flag: '🇾🇪', mobileDigits: 9, formatExample: '71 234 567' }
    ];

    document.addEventListener('DOMContentLoaded', function() {
        // ── 1. Elements for Phone Component ──
        const phoneTriggerBtn   = document.getElementById('phoneCountryTrigger');
        const phonePopover      = document.getElementById('phoneCountryPopover');
        const phoneSearchInput  = document.getElementById('phoneSearchInput');
        const phoneItemsList    = document.getElementById('phoneCountryItemsList');
        const phoneChevron      = document.getElementById('phoneChevron');
        const phoneFlag         = document.getElementById('phoneFlag');
        const phoneIsoCode      = document.getElementById('phoneIsoCode');
        const phoneDialCode     = document.getElementById('phoneDialCode');
        const phoneCountryLabel = document.getElementById('phoneCountryLabel');
        const phoneNativeInput  = document.getElementById('phoneNativeInput');
        const phoneInputBox     = document.getElementById('phoneInputBox');
        const phoneDigitsPill   = document.getElementById('phoneDigitsPill');
        const phoneDigitsEntered= document.getElementById('phoneDigitsEntered');
        const phoneDigitsAllowed= document.getElementById('phoneDigitsAllowed');
        const phoneCheckCircle  = document.getElementById('phoneCheckCircle');
        const phoneChatIcon     = document.getElementById('phoneChatIcon');
        const phoneHiddenMobile = document.getElementById('phoneHiddenMobile');
        const phoneHiddenCode   = document.getElementById('phoneHiddenCountryCode');

        let activeCountry = COUNTRIES_PHONE_DATA[0]; // Default India (+91, 10 digits)
        let isSyncingCountry = false;

        // ── 2. Render Country Popover List ──
        function renderCountryItems(filterQuery = '') {
            const query = filterQuery.trim().toLowerCase();
            const filtered = COUNTRIES_PHONE_DATA.filter(c => 
                c.name.toLowerCase().includes(query) ||
                c.dialCode.includes(query) ||
                c.code.toLowerCase().includes(query)
            );

            if (filtered.length === 0) {
                phoneItemsList.innerHTML = `<div class="p-3 text-center text-muted small">No countries found matching "${filterQuery}"</div>`;
                return;
            }

            phoneItemsList.innerHTML = filtered.map(c => `
                <button type="button" 
                        class="phone-country-item ${c.code === activeCountry.code ? 'is-selected' : ''}" 
                        data-code="${c.code}">
                    <div class="d-flex align-items-center gap-2 overflow-hidden pe-2">
                        <span class="fs-6">${c.flag}</span>
                        <span class="fw-bold text-dark">${c.code}</span>
                        <span class="text-truncate">${c.name}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <span class="text-muted" style="font-size: 10px;">(${c.mobileDigits} digits)</span>
                        <span class="font-monospace fw-semibold text-secondary">${c.dialCode}</span>
                    </div>
                </button>
            `).join('');

            phoneItemsList.querySelectorAll('.phone-country-item').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const code = this.getAttribute('data-code');
                    const country = COUNTRIES_PHONE_DATA.find(item => item.code === code);
                    if (country) {
                        selectCountry(country);
                    }
                });
            });
        }

        // ── 3. Select Country Routine ──
        function selectCountry(country, syncToTomSelect = true) {
            activeCountry = country;
            phoneFlag.textContent = country.flag;
            phoneIsoCode.textContent = country.code;
            phoneDialCode.textContent = country.dialCode;
            phoneCountryLabel.textContent = country.name;
            phoneHiddenCode.value = country.dialCode;

            // Restrict input
            phoneNativeInput.placeholder = country.formatExample;
            phoneNativeInput.maxLength = country.mobileDigits;
            phoneDigitsAllowed.textContent = country.mobileDigits;

            // Constrain existing digits if longer than new limit
            let digits = phoneNativeInput.value.replace(/\D/g, '');
            if (digits.length > country.mobileDigits) {
                digits = digits.slice(0, country.mobileDigits);
                phoneNativeInput.value = digits;
            }

            updatePhoneState();
            closePopover();

            // Bidirectional sync with Country dropdown
            if (syncToTomSelect && window.tsCountryInstance && !isSyncingCountry) {
                isSyncingCountry = true;
                const matchedVal = window.tsCountryInstance.options[country.name] ? country.name : null;
                if (matchedVal && window.tsCountryInstance.getValue() !== matchedVal) {
                    window.tsCountryInstance.setValue(matchedVal);
                }
                setTimeout(() => { isSyncingCountry = false; }, 100);
            }

            phoneNativeInput.focus();
        }

        // ── 4. Update Phone Validation & UI State ──
        function updatePhoneState() {
            // Strip any non-digit
            const digits = phoneNativeInput.value.replace(/\D/g, '');
            if (phoneNativeInput.value !== digits) {
                phoneNativeInput.value = digits;
            }

            const count = digits.length;
            const allowed = activeCountry.mobileDigits;
            phoneDigitsEntered.textContent = count;

            const isMatched = count === allowed;

            if (isMatched) {
                phoneDigitsPill.classList.add('is-matched');
                phoneDigitsPill.innerHTML = `<span>${count}</span> / <span>${allowed}</span> digits ✓`;
                phoneInputBox.classList.add('is-complete');
                phoneInputBox.classList.remove('is-invalid');
                phoneCheckCircle.classList.remove('d-none');
                phoneChatIcon.classList.add('d-none');
            } else {
                phoneDigitsPill.classList.remove('is-matched');
                phoneDigitsPill.innerHTML = `<span>${count}</span> / <span>${allowed}</span> digits`;
                phoneInputBox.classList.remove('is-complete');
                phoneCheckCircle.classList.add('d-none');
                phoneChatIcon.classList.remove('d-none');
            }

            // Keep hidden input synchronized with full international E.164 number (+919876543210)
            if (count > 0) {
                phoneHiddenMobile.value = `${activeCountry.dialCode}${digits}`;
            } else {
                phoneHiddenMobile.value = '';
            }
        }

        // ── 5. Open / Close Country Popover ──
        function openPopover() {
            phonePopover.classList.remove('d-none');
            phoneChevron.classList.add('is-open');
            phoneSearchInput.value = '';
            renderCountryItems('');
            setTimeout(() => phoneSearchInput.focus(), 60);
        }

        function closePopover() {
            phonePopover.classList.add('d-none');
            phoneChevron.classList.remove('is-open');
        }

        phoneTriggerBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (phonePopover.classList.contains('d-none')) {
                openPopover();
            } else {
                closePopover();
            }
        });

        // Search in Popover
        phoneSearchInput.addEventListener('input', function() {
            renderCountryItems(this.value);
        });

        // Close on Outside Click
        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('phoneWrapperContainer');
            if (wrapper && !wrapper.contains(e.target)) {
                closePopover();
            }
        });

        // Handle typing in phone input (Strict digits only, max digits enforcement)
        phoneNativeInput.addEventListener('input', updatePhoneState);
        phoneNativeInput.addEventListener('keydown', function(e) {
            // Allow control keys (backspace, tab, delete, arrows, copy/paste)
            if ([8, 9, 13, 27, 46].includes(e.keyCode) || 
                (e.ctrlKey || e.metaKey)) {
                return;
            }
            // Stop further typing if limit is reached
            const digits = this.value.replace(/\D/g, '');
            if (digits.length >= activeCountry.mobileDigits && !['Backspace','Delete','ArrowLeft','ArrowRight'].includes(e.key)) {
                e.preventDefault();
            }
        });

        // ── 6. Initial Data Parse & Pre-selection ──
        const rawExistingMobile = (phoneHiddenMobile.value || '').trim();
        const userProfileCountry = "{{ old('country', Auth::user()->country) }}";

        let detectedCountry = null;
        let localDigits = '';

        if (rawExistingMobile) {
            // Sort by longest dialCode first (+880 before +88, +971 before +97, etc.)
            const sortedByDial = [...COUNTRIES_PHONE_DATA].sort((a,b) => b.dialCode.length - a.dialCode.length);
            for (const c of sortedByDial) {
                const dial = c.dialCode;
                const cleanDial = dial.replace('+', '');
                if (rawExistingMobile.startsWith(dial)) {
                    detectedCountry = c;
                    localDigits = rawExistingMobile.slice(dial.length).replace(/\D/g, '');
                    break;
                } else if (rawExistingMobile.startsWith(cleanDial)) {
                    detectedCountry = c;
                    localDigits = rawExistingMobile.slice(cleanDial.length).replace(/\D/g, '');
                    break;
                }
            }
            if (!detectedCountry) {
                localDigits = rawExistingMobile.replace(/\D/g, '');
            }
        }

        if (!detectedCountry && userProfileCountry && userProfileCountry !== 'Not set') {
            detectedCountry = COUNTRIES_PHONE_DATA.find(c => c.name.toLowerCase() === userProfileCountry.toLowerCase());
        }

        if (detectedCountry) {
            selectCountry(detectedCountry, false);
        } else {
            selectCountry(COUNTRIES_PHONE_DATA[0], false); // Default India
        }

        if (localDigits) {
            phoneNativeInput.value = localDigits.slice(0, activeCountry.mobileDigits);
            updatePhoneState();
        }

        // ── 7. Setup TomSelect for Country, State, District ──
        const countryEl  = document.getElementById('profile_country');
        const stateEl    = document.getElementById('profile_state');
        const districtEl = document.getElementById('profile_district');
        
        const tsOpts = {
            allowEmptyOption: true,
            maxOptions: null,
            searchField: ['text'],
            sortField: { field: 'text', direction: 'asc' },
            create: false,
        };

        const tsCountry  = new TomSelect(countryEl,  Object.assign({}, tsOpts, { placeholder: 'Search or select country…'  }));
        const tsState    = new TomSelect(stateEl,    Object.assign({}, tsOpts, { placeholder: 'Select country first…'      }));
        const tsDistrict = new TomSelect(districtEl, Object.assign({}, tsOpts, { placeholder: 'Select state first…'        }));

        window.tsCountryInstance = tsCountry;

        tsState.disable();
        tsDistrict.disable();

        // Load remote countries with fallback
        fetch('https://countriesnow.space/api/v0.1/countries/positions')
            .then(r => r.json())
            .then(data => {
                if (data.error === false) {
                    const opts = data.data.map(c => ({ value: c.name, text: c.name }));
                    tsCountry.addOptions(opts);
                    
                    const uCountry = "{{ old('country', Auth::user()->country) }}";
                    if (uCountry && uCountry !== 'Not set') {
                        tsCountry.setValue(uCountry);
                    }
                }
            })
            .catch(() => {});

        // Preselect current country if set
        const initCountry = "{{ old('country', Auth::user()->country) }}";
        if (initCountry && initCountry !== 'Not set') {
            tsCountry.setValue(initCountry);
        }

        // Sync when user selects Country in TomSelect -> sync phone ISD code
        tsCountry.on('change', function(countryValue) {
            if (!isSyncingCountry && countryValue) {
                isSyncingCountry = true;
                const matchPhoneCountry = COUNTRIES_PHONE_DATA.find(c => c.name.toLowerCase() === countryValue.toLowerCase());
                if (matchPhoneCountry && matchPhoneCountry.code !== activeCountry.code) {
                    selectCountry(matchPhoneCountry, false);
                }
                setTimeout(() => { isSyncingCountry = false; }, 100);
            }

            tsState.clear(true);
            tsState.clearOptions();
            tsState.disable();
            tsDistrict.clear(true);
            tsDistrict.clearOptions();
            tsDistrict.disable();

            if (!countryValue) return;

            fetch('https://countriesnow.space/api/v0.1/countries/states', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ country: countryValue })
            })
            .then(res => res.json())
            .then(data => {
                if (data.error === false && data.data.states && data.data.states.length > 0) {
                    const opts = data.data.states.map(s => ({ value: s.name, text: s.name }));
                    tsState.addOptions(opts);
                    tsState.enable();
                    
                    const userState = "{{ old('state', Auth::user()->state) }}";
                    if (userState && userState !== 'Not set') {
                        tsState.setValue(userState);
                    }
                } else {
                    tsState.addOption({ value: 'N/A', text: 'N/A' });
                    tsState.setValue('N/A');
                    tsState.enable();
                    tsDistrict.addOption({ value: 'N/A', text: 'N/A' });
                    tsDistrict.setValue('N/A', true);
                    tsDistrict.enable();
                }
            })
            .catch(() => {
                tsState.addOption({ value: 'General', text: 'General' });
                tsState.setValue('General');
                tsState.enable();
            });
        });

        // State change -> load cities/districts
        tsState.on('change', function(stateValue) {
            tsDistrict.clear(true);
            tsDistrict.clearOptions();
            tsDistrict.disable();

            if (!stateValue || stateValue === 'N/A' || stateValue === 'Unknown' || stateValue === 'General') return;

            const countryValue = tsCountry.getValue();

            fetch('https://countriesnow.space/api/v0.1/countries/state/cities', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ country: countryValue, state: stateValue })
            })
            .then(res => res.json())
            .then(data => {
                if (data.error === false && data.data && data.data.length > 0) {
                    const opts = data.data.map(c => ({ value: c, text: c }));
                    tsDistrict.addOptions(opts);
                    tsDistrict.enable();
                    
                    const userDistrict = "{{ old('district', Auth::user()->district) }}";
                    if (userDistrict && userDistrict !== 'Not set') {
                        tsDistrict.setValue(userDistrict);
                    }
                } else {
                    tsDistrict.addOption({ value: stateValue, text: stateValue });
                    tsDistrict.setValue(stateValue);
                    tsDistrict.enable();
                }
            })
            .catch(() => {
                tsDistrict.addOption({ value: stateValue, text: stateValue });
                tsDistrict.setValue(stateValue);
                tsDistrict.enable();
            });
        });

        // ── 8. Form Submit Interceptor ──
        let isProfileSubmitting = false;
        const completeForm = document.getElementById('completeProfileForm');
        if (completeForm) {
            completeForm.addEventListener('submit', function(e) {
                // Ensure phone input has exact required length
                const enteredDigits = phoneNativeInput.value.replace(/\D/g, '');
                if (enteredDigits.length !== activeCountry.mobileDigits) {
                    e.preventDefault();
                    phoneInputBox.classList.add('is-invalid');
                    phoneNativeInput.focus();
                    alert(`Please enter a valid ${activeCountry.mobileDigits}-digit mobile number for ${activeCountry.name}. You entered ${enteredDigits.length} digits.`);
                    return false;
                }

                // Make sure full international number is populated
                phoneHiddenMobile.value = `${activeCountry.dialCode}${enteredDigits}`;
                phoneHiddenCode.value = activeCountry.dialCode;

                if (isProfileSubmitting) {
                    e.preventDefault();
                    return false;
                }
                isProfileSubmitting = true;
                const submitBtn = completeForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Saving & Submitting...`;
                }
            });
        }
    });
</script>
@endpush
@endsection
