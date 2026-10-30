@extends('layouts.admin')
@section('title', 'Accept Question ' . $question->question_id)
@section('topbar-title', 'Accept & Triage Question')
@section('topbar-icon', 'bi-check2-circle')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <a href="{{ route('admin.questions.index') }}">Questions</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <span class="text-dark fw-semibold">Accept</span>
@endsection

@section('content')
@php
    // Time since submission
    $diffDays = floor($question->created_at->diffInDays(now()));
    $timeSince = $diffDays > 0 ? "{$diffDays} Days Ago" : $question->created_at->diffForHumans(null, true) . ' ago';

    // Language font classes
    $langClass = '';
    if($question->language == 'Urdu') $langClass = 'gulzar-font';
    elseif($question->language == 'Arabic') $langClass = 'alyamama-font';
    elseif($question->language == 'Malayalam') $langClass = 'noto-serif-malayalam-font';
@endphp

{{-- Top Row: Back link & 3 Metric KPI Cards (Matching Reference Image) --}}
<div class="d-flex justify-content-end mb-2">
    <a href="{{ route('admin.questions.show', $question) }}" class="btn btn-sm btn-link text-primary text-decoration-none fw-semibold d-inline-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i>
        <span>Back to Questions</span>
    </a>
</div>

<div class="row g-3 mb-4">
    {{-- Card 1: Question ID & Status --}}
    <div class="col-md-4">
        <div class="kpi-metric-card">
            <div class="kpi-icon-box bg-primary bg-opacity-10 text-primary flex-shrink-0">
                <i class="bi bi-file-earmark-text-fill fs-5"></i>
            </div>
            <div class="min-w-0">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fw-bold text-dark fs-5">{{ $question->question_id }}</span>
                    @if($question->status === 'pending')
                        <span class="badge status-pill-pending">
                            <span class="pulse-dot-amber"></span> Pending Review
                        </span>
                    @else
                        <span class="badge bg-light text-dark border">{{ ucfirst($question->status) }}</span>
                    @endif
                </div>
                <div class="text-muted text-xs mt-1">Current Status</div>
            </div>
        </div>
    </div>

    {{-- Card 2: Submitted On --}}
    <div class="col-md-4">
        <div class="kpi-metric-card">
            <div class="kpi-icon-box bg-primary bg-opacity-10 text-primary flex-shrink-0">
                <i class="bi bi-calendar3 fs-5"></i>
            </div>
            <div class="min-w-0">
                <div class="fw-bold text-dark fs-6 text-truncate" title="{{ $question->created_at->format('d M Y, H:i') }}">
                    {{ $question->created_at->format('d M Y, H:i') }}
                </div>
                <div class="text-muted text-xs mt-1">Submitted On</div>
            </div>
        </div>
    </div>

    {{-- Card 3: Time Since Submission --}}
    <div class="col-md-4">
        <div class="kpi-metric-card">
            <div class="kpi-icon-box bg-warning bg-opacity-10 text-warning flex-shrink-0">
                <i class="bi bi-hourglass-split fs-5"></i>
            </div>
            <div class="min-w-0">
                <div class="fw-bold text-dark fs-6">{{ $timeSince }}</div>
                <div class="text-muted text-xs mt-1">Time Since Submission</div>
            </div>
        </div>
    </div>
</div>

{{-- Main Two-Column Layout --}}
<div class="row g-4">
    {{-- Left Column: Question Information (60%) --}}
    <div class="col-lg-7">
        <div class="modern-card overflow-hidden h-100 mb-0">
            {{-- Header --}}
            <div class="detail-card-header d-flex align-items-center gap-2">
                <div class="d-inline-flex p-2 rounded-2 bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-file-earmark-text-fill fs-5"></i>
                </div>
                <h5 class="fw-bold mb-0 text-dark">Question Information</h5>
            </div>

            {{-- Body --}}
            <div class="detail-card-body">
                {{-- Green-tinted container holding Madhab and Original Question Box --}}
                <div class="question-info-highlight-box mb-4">
                    {{-- Selected Madhab --}}
                    <div class="mb-3">
                        <div class="text-secondary small fw-bold text-uppercase mb-2" style="letter-spacing: 0.5px; font-size: 0.76rem;">
                            Selected Madhab
                        </div>
                        <span class="badge px-3 py-2 text-white d-inline-flex align-items-center gap-1.5" style="background: #059669; font-size: 0.86rem; border-radius: 20px;">
                            <i class="bi bi-book-half"></i>
                            <span>{{ $question->madhab?->name ?? 'Shafi\'i' }}</span>
                        </span>
                    </div>

                    {{-- Original Question Text --}}
                    <div>
                        <div class="text-secondary small fw-bold text-uppercase mb-2" style="letter-spacing: 0.5px; font-size: 0.76rem;">
                            Original Question Text
                        </div>
                        <div class="question-original-text-box bg-white {{ $langClass }}">
                            {!! $question->body !!}
                        </div>
                    </div>
                </div>

                {{-- 4 Mini-Metadata Items in 2x2 Grid --}}
                <div class="row g-3">
                    {{-- Item 1: Submitted by --}}
                    <div class="col-sm-6">
                        <div class="meta-mini-card">
                            <div class="meta-mini-icon bg-primary bg-opacity-10 text-primary rounded-circle flex-shrink-0">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-muted text-xs">Submitted by</div>
                                <div class="fw-bold text-dark text-xs text-truncate" title="{{ $question->user->name }}">
                                    {{ $question->user->name }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Item 2: Email --}}
                    <div class="col-sm-6">
                        <div class="meta-mini-card">
                            <div class="meta-mini-icon bg-primary bg-opacity-10 text-primary rounded-circle flex-shrink-0">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-muted text-xs">Email</div>
                                <div class="fw-bold text-dark text-xs text-truncate" title="{{ $question->user->email }}">
                                    {{ $question->user->email }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Item 3: Mobile --}}
                    <div class="col-sm-6">
                        <div class="meta-mini-card">
                            <div class="meta-mini-icon bg-primary bg-opacity-10 text-primary rounded-circle flex-shrink-0">
                                <i class="bi bi-telephone-fill"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-muted text-xs">Mobile</div>
                                <div class="fw-bold text-dark text-xs text-truncate">
                                    {{ $question->user->mobile ?: ($question->user->country_code . ' ' . $question->user->mobile ?: 'Not provided') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Item 4: Submitted On --}}
                    <div class="col-sm-6">
                        <div class="meta-mini-card">
                            <div class="meta-mini-icon bg-primary bg-opacity-10 text-primary rounded-circle flex-shrink-0">
                                <i class="bi bi-calendar-event-fill"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-muted text-xs">Submitted On</div>
                                <div class="fw-bold text-dark text-xs text-truncate">
                                    {{ $question->created_at->format('d M Y, H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column: Question Categorization (40%) --}}
    <div class="col-lg-5">
        <div class="modern-card overflow-hidden h-100 mb-0">
            {{-- Header --}}
            <div class="detail-card-header d-flex align-items-center gap-2">
                <i class="bi bi-check2-square text-primary fs-5"></i>
                <h5 class="fw-bold mb-0 text-dark">Question Categorization</h5>
            </div>

            {{-- Form Body --}}
            <form method="POST" action="{{ route('admin.questions.accept', $question) }}" id="acceptQuestionForm">
                @csrf
                <div class="detail-card-body d-flex flex-column justify-content-between h-100">
                    <div>
                        {{-- Field 1: Question Summary (Short Question) --}}
                        <div class="mb-3.5">
                            <label class="form-label fw-bold text-dark small mb-1.5">
                                Question Summary (Short Question) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" class="form-control rounded-3 py-2.5 px-3 text-sm fw-semibold"
                                   value="{{ old('title', $question->title) }}" maxlength="200" required
                                   placeholder="Enter concise question summary...">
                            <div class="text-muted text-xs mt-1">A clear, brief summary (max 200 chars) for archive headings.</div>
                        </div>

                        {{-- Field 2: Question Language --}}
                        <div class="mb-3.5">
                            <label class="form-label fw-bold text-dark small mb-1.5">
                                Question Language <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-primary px-3">
                                    <i class="bi bi-globe"></i>
                                </span>
                                <select name="language" class="form-select border-start-0 py-2.5 text-sm fw-medium" required>
                                    <option value="English" {{ old('language', $question->language) == 'English' || $question->user->mother_language == 'English' ? 'selected' : '' }}>English</option>
                                    <option value="Malayalam" {{ old('language', $question->language) == 'Malayalam' || $question->user->mother_language == 'Malayalam' ? 'selected' : '' }}>Malayalam</option>
                                    <option value="Arabic" {{ old('language', $question->language) == 'Arabic' || $question->user->mother_language == 'Arabic' ? 'selected' : '' }}>Arabic</option>
                                    <option value="Urdu" {{ old('language', $question->language) == 'Urdu' || $question->user->mother_language == 'Urdu' ? 'selected' : '' }}>Urdu</option>
                                    <option value="Hindi" {{ old('language', $question->language) == 'Hindi' || $question->user->mother_language == 'Hindi' ? 'selected' : '' }}>Hindi</option>
                                </select>
                            </div>
                        </div>

                        {{-- Field 3: Main Category --}}
                        <div class="mb-3.5">
                            <label class="form-label fw-bold text-dark small mb-1.5">
                                Main Category <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-primary px-3">
                                    <i class="bi bi-stack"></i>
                                </span>
                                <select id="main_category" class="form-select border-start-0 py-2.5 text-sm fw-medium" required>
                                    <option value="">Select Main Category...</option>
                                    @foreach($mainCategories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}{{ $cat->name_ar ? ' (' . $cat->name_ar . ')' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Dynamic Sub-Category (Displayed if subcategories exist) --}}
                        <div class="mb-4" id="sub_category_container" style="display:none;">
                            <label class="form-label fw-bold text-dark small mb-1.5">
                                Sub Category <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-primary px-3">
                                    <i class="bi bi-diagram-2"></i>
                                </span>
                                <select name="category_id" id="sub_category" class="form-select border-start-0 py-2.5 text-sm fw-medium" required>
                                    <option value="">Select Sub Category...</option>
                                    @foreach($subCategories as $sub)
                                        <option value="{{ $sub->id }}" data-parent="{{ $sub->parent_id }}">{{ $sub->name }}{{ $sub->name_ar ? ' (' . $sub->name_ar . ')' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons Row (Cancel vs Accept & Save) --}}
                    <div class="row g-3 pt-3 mt-auto">
                        <div class="col-6">
                            <a href="{{ route('admin.questions.show', $question) }}" class="btn btn-outline-secondary rounded-pill py-2.5 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5 w-100">
                                <i class="bi bi-x-lg"></i>
                                <span>Cancel</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary rounded-pill py-2.5 fw-bold d-inline-flex align-items-center justify-content-center gap-1.5 w-100" style="background: #2563EB;">
                                <i class="bi bi-check2-circle fs-5"></i>
                                <span>Accept &amp; Save</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* ── Metric KPI Summary Card ── */
    .kpi-metric-card {
        padding: 1.25rem 1.35rem !important;
        border-radius: 14px !important;
        background: #ffffff !important;
        border: 1px solid var(--card-border, #E2E8F0) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        height: 100%;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .kpi-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* ── Main Detail Card Header & Body ── */
    .detail-card-header {
        padding: 1.25rem 1.75rem !important;
        background: #ffffff;
        border-bottom: 1px solid var(--card-border, #E2E8F0);
    }
    .detail-card-body {
        padding: 1.75rem 2rem !important;
        background: #ffffff;
    }

    /* ── Green Highlight Container (Original Question) ── */
    .question-info-highlight-box {
        background: #F0FDF4;
        border: 1.5px solid #DCFCE7;
        border-radius: 14px;
        padding: 1.5rem;
    }
    .question-original-text-box {
        padding: 1.25rem 1.5rem !important;
        border-radius: 12px !important;
        border: 1px solid #E2E8F0 !important;
        line-height: 1.85;
        font-size: 1.05rem;
        color: #1e293b;
        min-height: 100px;
    }

    /* ── Mini Meta Horizontal Cards ── */
    .meta-mini-card {
        padding: 0.85rem 1rem !important;
        border-radius: 12px !important;
        background: #F8FAFC !important;
        border: 1px solid #E2E8F0 !important;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-height: 62px;
        transition: all 0.2s ease;
    }
    .meta-mini-card:hover {
        background: #ffffff !important;
        border-color: #cbd5e1 !important;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }
    .meta-mini-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    /* ── Form Inputs & Selects ── */
    .form-control, .form-select {
        border-color: #CBD5E1;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .form-control:focus, .form-select:focus {
        border-color: #3B82F6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }
    .input-group-text {
        border-color: #CBD5E1;
    }

    /* ── Status Pills ── */
    .status-pill-pending {
        background: #FFFBEB;
        color: #D97706;
        border: 1px solid #FDE68A;
        font-weight: 700;
        border-radius: 20px;
        padding: 4px 10px;
        font-size: 0.72rem;
    }
    .pulse-dot-amber {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #D97706;
        margin-right: 4px;
        animation: pulseAmber 1.8s infinite;
    }
    @keyframes pulseAmber {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(217, 119, 6, 0.6); }
        70% { transform: scale(1); box-shadow: 0 0 0 5px rgba(217, 119, 6, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(217, 119, 6, 0); }
    }

    .text-xs { font-size: 0.75rem !important; }

    @media (max-width: 576px) {
        .detail-card-header {
            padding: 1rem 1.15rem !important;
        }
        .detail-card-body {
            padding: 1.15rem 1.15rem !important;
        }
        .question-info-highlight-box {
            padding: 1rem;
        }
        .kpi-metric-card {
            padding: 1rem !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Sub-Category dynamic population based on Main Category selection
    const allSubOptions = Array.from(document.querySelectorAll('#sub_category option[data-parent]'));

    document.getElementById('main_category').addEventListener('change', function() {
        const parentId = this.value;
        const subContainer = document.getElementById('sub_category_container');
        const subSelect = document.getElementById('sub_category');

        // Reset subcategory select
        subSelect.innerHTML = '<option value="">Select Sub Category...</option>';

        if (parentId) {
            const matches = allSubOptions.filter(opt => opt.getAttribute('data-parent') === parentId);

            if (matches.length > 0) {
                subContainer.style.display = 'block';
                subSelect.setAttribute('required', 'required');
                subSelect.name = 'category_id';
                this.removeAttribute('name');

                matches.forEach(opt => {
                    subSelect.appendChild(opt.cloneNode(true));
                });
            } else {
                // Main category itself acts as category_id
                subContainer.style.display = 'none';
                subSelect.removeAttribute('required');
                subSelect.removeAttribute('name');
                this.name = 'category_id';
            }
        } else {
            subContainer.style.display = 'none';
            subSelect.removeAttribute('required');
        }
        
        subSelect.value = '';
    });
</script>
@endpush

@endsection
