@extends('layouts.admin')

@section('title', 'Write Answer — ' . $question->question_id)
@section('topbar-title', 'Fatwa Authoring & Drafting Workspace')
@section('topbar-icon', 'bi-pencil-square')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <a href="{{ route('admin.questions.index') }}">Questions</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <a href="{{ route('admin.questions.show', $question) }}">{{ $question->question_id }}</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <span class="text-dark fw-semibold">Draft Answer</span>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    /* ── Fatwa Drafting Workspace Scoped Styles ── */
    .answer-workspace-container {
        font-family: inherit;
    }

    .modern-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: box-shadow 0.2s ease;
    }
    .modern-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    /* ── Quick Info Context Strip ── */
    .context-info-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 1.15rem 1.4rem;
    }

    .user-avatar-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #EFF6FF;
        color: #2563EB;
        font-weight: 700;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #DBEAFE;
    }

    /* ── Form Inputs Polish ── */
    .fatwa-form-input {
        border: 1px solid #CBD5E1;
        border-radius: 10px;
        padding: 0.65rem 0.95rem;
        font-size: 0.9rem;
        transition: all 0.15s ease;
    }
    .fatwa-form-input:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* ── Arabic Typography ── */
    .cat-arabic-text {
        font-family: 'Amiri', 'Noto Naskh Arabic', serif !important;
        font-size: 1.15rem;
        line-height: 1.6;
        direction: rtl;
        border-radius: 10px;
        border: 1px solid #CBD5E1;
    }
    .cat-arabic-text:focus {
        border-color: #059669;
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12);
        outline: none;
    }

    /* ── Reference Row Card ── */
    .reference-row-card {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 1.1rem;
        margin-bottom: 1rem;
        transition: all 0.15s ease;
    }
    .reference-row-card:hover {
        border-color: #CBD5E1;
        background: #FFFFFF;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }

    /* ── Sticky Sidebar ── */
    .sticky-authoring-sidebar {
        position: sticky;
        top: 78px;
        z-index: 10;
    }

    /* ── Summernote Custom Framing ── */
    .note-editor.note-frame {
        border: 1px solid #CBD5E1 !important;
        border-radius: 12px !important;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    }
    .note-toolbar {
        background: #F8FAFC !important;
        border-bottom: 1px solid #E2E8F0 !important;
        padding: 8px 10px !important;
    }
    .note-editor.note-frame.fullscreen {
        z-index: 1070 !important;
    }

    /* ── Live Character Progress Counter ── */
    .char-meter-badge {
        font-size: 0.82rem;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }
    .char-meter-warning {
        background: #FEF3C7;
        color: #92400E;
        border: 1px solid #FDE68A;
    }
    .char-meter-success {
        background: #ECFDF5;
        color: #065F46;
        border: 1px solid #A7F3D0;
    }

    /* ── Pill Status Badges ── */
    .status-pill-badge {
        font-size: 0.78rem;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* ── AI ChatGPT Button Styles ── */
    .btn-ai-chatgpt {
        background: linear-gradient(135deg, #10A37F 0%, #059669 100%);
        color: #FFFFFF !important;
        border: none;
        border-radius: 24px;
        padding: 6px 16px;
        font-size: 0.84rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        box-shadow: 0 2px 8px rgba(16, 163, 127, 0.28);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        cursor: pointer;
        text-decoration: none;
    }
    .btn-ai-chatgpt:hover {
        background: linear-gradient(135deg, #0D8C6C 0%, #047857 100%);
        box-shadow: 0 4px 14px rgba(16, 163, 127, 0.42);
        transform: translateY(-1px);
        color: #FFFFFF !important;
    }
    .btn-ai-chatgpt:active {
        transform: translateY(0);
        box-shadow: 0 2px 6px rgba(16, 163, 127, 0.25);
    }
    .btn-ai-chatgpt .ai-sparkle-svg {
        animation: ai-sparkle-spin 4s infinite linear;
    }
    @keyframes ai-sparkle-spin {
        0%, 100% { transform: scale(1) rotate(0deg); opacity: 1; }
        50% { transform: scale(1.15) rotate(15deg); opacity: 0.9; }
    }
</style>
@endpush

@section('content')
@php
    $isPublished = $question->status === 'published';

    // Questioner initials
    $nameParts = explode(' ', trim($question->user->name ?? 'Questioner'));
    $initials = strtoupper(substr($nameParts[0] ?? 'Q', 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));

    // Existing or draft references
    $refs = $question->references->isNotEmpty() ? $question->references : $question->draftReferences;
@endphp

<div class="answer-workspace-container">
    
    {{-- ── 1. Page Header Bar ── --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -0.3px;">
                    Write Fatwa Answer
                </h4>
                <span class="badge rounded-pill" style="background: #EFF6FF; color: #2563EB; font-weight: 700; font-size: 0.85rem; border: 1px solid #DBEAFE; padding: 4px 12px;">
                    #{{ $question->question_id }}
                </span>
                @if($question->status === 'published')
                    <span class="status-pill-badge" style="background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0;">
                        <i class="bi bi-check-circle-fill"></i> Published Fatwa
                    </span>
                @elseif($question->status === 'answered')
                    <span class="status-pill-badge" style="background: #EFF6FF; color: #1E40AF; border: 1px solid #BFDBFE;">
                        <i class="bi bi-pencil-fill"></i> Answer Drafted
                    </span>
                @elseif($question->status === 'under_review')
                    <span class="status-pill-badge" style="background: #FAF5FF; color: #6B21A8; border: 1px solid #E9D5FF;">
                        <i class="bi bi-hourglass-split"></i> Under Review
                    </span>
                @else
                    <span class="status-pill-badge" style="background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A;">
                        <i class="bi bi-clock-history"></i> {{ ucfirst($question->status) }}
                    </span>
                @endif
            </div>
            <p class="text-muted small mb-0">
                Author and calibrate legal scholarly ruling with verified classical references.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.questions.show', $question) }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 py-2 px-3 fw-semibold" style="border-radius: 10px; font-size: 0.85rem;">
                <i class="bi bi-arrow-left"></i> Back to Question
            </a>
        </div>
    </div>

    {{-- ── 2. Quick Context Info Strip ── --}}
    <div class="context-info-card mb-4 shadow-sm">
        <div class="row g-3 align-items-center">
            {{-- Questioner Profile --}}
            <div class="col-12 col-md-4 border-end-md">
                <div class="d-flex align-items-center gap-3">
                    <div class="user-avatar-circle flex-shrink-0">
                        {{ $initials }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="fw-bold text-dark text-truncate" style="font-size: 0.95rem;">
                            {{ $question->user->name ?? 'Questioner' }}
                        </div>
                        <div class="text-muted small text-truncate" style="font-size: 0.8rem;">
                            {{ $question->user->email ?? 'No email registered' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Category & Madhab --}}
            <div class="col-6 col-md-3 border-end-md">
                <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">
                    Taxonomy & School
                </div>
                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                    @if($question->category)
                        <span class="badge" style="background: #F1F5F9; color: #334155; font-weight: 600; font-size: 0.78rem; border: 1px solid #E2E8F0;">
                            <i class="bi bi-tag me-1"></i>{{ $question->category->name }}
                        </span>
                    @endif
                    @if($question->madhab)
                        <span class="badge" style="background: #ECFDF5; color: #065F46; font-weight: 600; font-size: 0.78rem; border: 1px solid #A7F3D0;">
                            <i class="bi bi-bookmark-check me-1"></i>{{ $question->madhab->name }}
                        </span>
                    @else
                        <span class="badge bg-light text-muted border" style="font-size: 0.76rem;">General / Not Specified</span>
                    @endif
                </div>
            </div>

            {{-- Submitted Date --}}
            <div class="col-6 col-md-2 border-end-md">
                <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">
                    Inquiry Date
                </div>
                <div class="fw-bold text-dark" style="font-size: 0.88rem;">
                    {{ $question->created_at->format('d M Y') }}
                </div>
                <div class="text-muted small" style="font-size: 0.76rem;">
                    {{ $question->created_at->diffForHumans() }}
                </div>
            </div>

            {{-- Current Assignment --}}
            <div class="col-12 col-md-3">
                <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">
                    Assigned Scholar
                </div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-badge text-primary fs-5"></i>
                    <span class="fw-semibold text-dark text-truncate" style="font-size: 0.9rem;">
                        {{ $question->assignedMufti->name ?? 'Unassigned / Open' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. Main Form: 2-Column Responsive Workspace ── --}}
    <form method="POST" action="{{ route('admin.questions.answer.store', $question) }}" id="fatwaAnswerForm">
        @csrf

        <div class="row g-4">
            
            {{-- ══════════════════════════════════════════════════════════════════════ --}}
            {{-- LEFT COLUMN: Authoring Canvas (Col 8)                                --}}
            {{-- ══════════════════════════════════════════════════════════════════════ --}}
            <div class="col-12 col-xl-8 col-lg-8">

                {{-- Card 1: Question Inquiry & Summary (Review & Edit) --}}
                <div class="modern-card p-4 mb-4">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-3" 
                                 style="width: 40px; height: 40px; background: #EFF6FF; color: #2563EB;">
                                <i class="bi bi-chat-left-text-fill fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark" style="font-size: 1.02rem;">Question Inquiry &amp; Summary</h6>
                                <span class="text-muted small" style="font-size: 0.8rem;">Review and refine the question details submitted by the questioner</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            {{-- ChatGPT AI Assistant Trigger Button --}}
                            <button type="button" 
                                    id="btnAiQuestionSummary" 
                                    class="btn-ai-chatgpt" 
                                    onclick="sendQuestionSummaryToChatGPT()"
                                    title="Send prompt to ChatGPT with specific Question Inquiry & Summary guidelines">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" class="ai-sparkle-svg" aria-hidden="true">
                                    <path d="M12 2L14.4 9.6L22 12L14.4 14.4L12 22L9.6 14.4L2 12L9.6 9.6L12 2Z"/>
                                </svg>
                                <i class="bi bi-stars"></i>
                                <span>Question Inquiry &amp; Summary</span>
                            </button>
                            <span class="badge bg-light text-secondary border px-2 py-1 d-none d-md-inline-block" style="font-size: 0.72rem;">
                                Original Submission
                            </span>
                        </div>
                    </div>

                    {{-- Question Summary / Title --}}
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">
                            Question Summary / Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="question_title" 
                               id="question_title" 
                               class="form-control fatwa-form-input @error('question_title') is-invalid @enderror" 
                               value="{{ old('question_title', $question->title) }}" 
                               placeholder="e.g. Concise summary of the question..." 
                               required>
                        @error('question_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Question Body (Rich HTML Editor) --}}
                    <div>
                        <label class="form-label small fw-bold text-dark mb-1">
                            Question Body (Full Inquiry Details — Rich HTML Editor)
                        </label>
                        <textarea name="question_body" 
                                  id="summernote-question" 
                                  class="form-control" 
                                  placeholder="Original question text...">{{ old('question_body', $question->body) }}</textarea>
                    </div>
                </div>

                {{-- Card 2: Full Scholarly Fatwa Answer --}}
                <div class="modern-card p-4 mb-4">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-3" 
                                 style="width: 40px; height: 40px; background: #ECFDF5; color: #059669;">
                                <i class="bi bi-pen-fill fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark" style="font-size: 1.02rem;">
                                    Scholarly Fatwa Answer (الفتوى الشرعية) <span class="text-danger">*</span>
                                </h6>
                                <span class="text-muted small" style="font-size: 0.8rem;">
                                    Authoritative Fiqh verdict and detailed explanation
                                </span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            {{-- Character Counter Badge --}}
                            <div id="charCountBadge" class="char-meter-badge char-meter-warning">
                                <i class="bi bi-fonts"></i>
                                <span><strong id="charCountNum">0</strong> / 30 min chars</span>
                            </div>

                            {{-- Reference Published Fatwa Trigger --}}
                            <button type="button" 
                                    class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5"
                                    onclick="window.openFatwaMentionPicker($('#summernote'), '', true)"
                                    style="font-size: 0.8rem;">
                                <i class="bi bi-at fs-6"></i> Reference Fatwa
                            </button>
                        </div>
                    </div>

                    {{-- Summernote Main Answer Editor --}}
                    <div class="mb-2">
                        <textarea name="full_answer" 
                                  id="summernote" 
                                  rows="10" 
                                  class="form-control @error('full_answer') is-invalid @enderror"
                                  placeholder="Write the full scholarly fatwa answer here... (Type @ to search and reference published fatwas)" 
                                  required>{{ old('full_answer', $question->draft_answer ?? $question->answer) }}</textarea>
                        @error('full_answer')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 text-muted small" style="font-size: 0.78rem;">
                        <div>
                            <span class="badge bg-success-subtle text-success me-1"><i class="bi bi-tag-fill me-1"></i>Cross-reference:</span>
                            Type <code>@{Question-ID}</code> (e.g. <code>@{A0001}</code>) or press <code>@</code> to search and link an existing published fatwa.
                        </div>
                        <div class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>Supports Rich Formatting, Lists, Quotes & Arabic Fonts.
                        </div>
                    </div>
                </div>

                {{-- Card 3: Supporting Islamic References --}}
                <div class="modern-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-3" 
                                 style="width: 40px; height: 40px; background: #FAF5FF; color: #9333EA;">
                                <i class="bi bi-book-half fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark" style="font-size: 1.02rem;">
                                    Supporting Islamic References (مراجع فقهية وأدلة)
                                </h6>
                                <span class="text-muted small" style="font-size: 0.8rem;">
                                    Attach primary Quranic, Hadith, or classical fiqh texts
                                </span>
                            </div>
                        </div>

                        <button type="button" id="add-reference" class="btn btn-sm btn-outline-primary fw-semibold d-inline-flex align-items-center gap-1.5 py-1.5 px-3" style="border-radius: 8px; font-size: 0.82rem;">
                            <i class="bi bi-plus-lg"></i> Add Reference
                        </button>
                    </div>

                    <div id="references-container">
                        @if($refs && $refs->count() > 0)
                            @foreach($refs as $index => $ref)
                                <div class="reference-row-card reference-row">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-white text-dark border fw-bold" style="font-size: 0.75rem;">
                                            <i class="bi bi-bookmark-fill text-primary me-1"></i>Reference #<span class="ref-index">{{ $index + 1 }}</span>
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-ref py-0.5 px-2" title="Remove reference" style="border-radius: 6px; font-size: 0.78rem;">
                                            <i class="bi bi-trash3 me-1"></i>Remove
                                        </button>
                                    </div>

                                    {{-- Arabic Citation Textarea --}}
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold text-dark mb-1">
                                            Arabic Text / Citation (النص العربي والدليل)
                                        </label>
                                        <textarea name="arabic_text[]" 
                                                  rows="3" 
                                                  class="form-control cat-arabic-text" 
                                                  placeholder="اكتب النص العربي أو الدليل الفقهي هنا...">{{ $ref->arabic_text }}</textarea>
                                    </div>

                                    {{-- Book Reference & Language --}}
                                    <div class="row g-2">
                                        <div class="col-md-7">
                                            <label class="form-label small fw-semibold text-dark mb-1">
                                                Book Reference & Volume/Page
                                            </label>
                                            <div class="position-relative">
                                                <input type="text" 
                                                       name="book_name[]" 
                                                       class="form-control fatwa-form-input" 
                                                       value="{{ $ref->book_name }}" 
                                                       placeholder="e.g. Radd al-Muhtar 1/250, Sahih al-Bukhari #1234">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label small fw-semibold text-dark mb-1">
                                                Language of Reference
                                            </label>
                                            <select name="ref_language[]" class="form-select fatwa-form-input">
                                                <option value="Arabic" {{ $ref->language == 'Arabic' ? 'selected' : '' }}>Arabic (العربية)</option>
                                                <option value="Urdu" {{ $ref->language == 'Urdu' ? 'selected' : '' }}>Urdu (اردو)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            {{-- Fresh Default Reference Row --}}
                            <div class="reference-row-card reference-row">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-white text-dark border fw-bold" style="font-size: 0.75rem;">
                                        <i class="bi bi-bookmark-fill text-primary me-1"></i>Reference #<span class="ref-index">1</span>
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-ref py-0.5 px-2" title="Remove reference" style="border-radius: 6px; font-size: 0.78rem;">
                                        <i class="bi bi-trash3 me-1"></i>Remove
                                    </button>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-dark mb-1">
                                        Arabic Text / Citation (النص العربي والدليل)
                                    </label>
                                    <textarea name="arabic_text[]" 
                                              rows="3" 
                                              class="form-control cat-arabic-text" 
                                              placeholder="اكتب النص العربي أو الدليل الفقهي هنا..."></textarea>
                                </div>

                                <div class="row g-2">
                                    <div class="col-md-7">
                                        <label class="form-label small fw-semibold text-dark mb-1">
                                            Book Reference & Volume/Page
                                        </label>
                                        <input type="text" 
                                               name="book_name[]" 
                                               class="form-control fatwa-form-input" 
                                               placeholder="e.g. Radd al-Muhtar 1/250, Sahih al-Bukhari #1234">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small fw-semibold text-dark mb-1">
                                            Language of Reference
                                        </label>
                                        <select name="ref_language[]" class="form-select fatwa-form-input">
                                            <option value="Arabic">Arabic (العربية)</option>
                                            <option value="Urdu">Urdu (اردو)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- ══════════════════════════════════════════════════════════════════════ --}}
            {{-- RIGHT COLUMN: Sticky Sidebar Controls (Col 4)                        --}}
            {{-- ══════════════════════════════════════════════════════════════════════ --}}
            <div class="col-12 col-xl-4 col-lg-4">
                <div class="sticky-authoring-sidebar">

                    {{-- Card A: Action & Submission Controls --}}
                    <div class="modern-card p-4 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <i class="bi bi-send-check text-primary fs-5"></i>
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.98rem;">Publishing Actions</h6>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" 
                                    class="btn btn-primary py-2.5 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm" 
                                    id="btnSubmitAnswer" 
                                    style="border-radius: 12px; font-size: 0.95rem;">
                                <i class="bi bi-send-check-fill"></i>
                                <span>Save &amp; Proceed</span>
                            </button>
                            <a href="{{ route('admin.questions.show', $question) }}" 
                               class="btn btn-outline-secondary py-2 fw-semibold" 
                               style="border-radius: 10px; font-size: 0.88rem;">
                                Cancel & Return
                            </a>
                            
                            {{-- Quick AI Sidebar Trigger --}}
                            <button type="button" 
                                    class="btn btn-sm btn-outline-success py-2 fw-bold d-flex align-items-center justify-content-center gap-2 mt-1" 
                                    onclick="sendQuestionSummaryToChatGPT()"
                                    style="border-radius: 10px; font-size: 0.82rem; border-color: #10A37F; color: #0D8C6C;">
                                <i class="bi bi-stars"></i>
                                <span>Question Inquiry &amp; Summary (AI)</span>
                            </button>
                        </div>

                        <div class="p-2.5 rounded-3" style="background: #F8FAFC; border: 1px solid #E2E8F0; font-size: 0.78rem;">
                            @if($isPublished)
                                <div class="text-success fw-bold mb-1">
                                    <i class="bi bi-shield-check me-1"></i> Live Fatwa Update Mode
                                </div>
                                <span class="text-muted">Changes saved here will immediately update the public fatwa archive.</span>
                            @else
                                <div class="text-primary fw-bold mb-1">
                                    <i class="bi bi-arrow-right-circle me-1"></i> Workflow Progression
                                </div>
                                <span class="text-muted">Saving will mark status as <strong>Answered</strong> and advance to the verification step.</span>
                            @endif
                        </div>
                    </div>

                    {{-- Card B: Answering Mufti & Language --}}
                    <div class="modern-card p-4 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <i class="bi bi-person-check text-primary fs-5"></i>
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.98rem;">Scholar &amp; Language</h6>
                        </div>

                        {{-- Answering Mufti Selection --}}
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark mb-1">
                                Answering Mufti <span class="text-danger">*</span>
                            </label>
                            <select name="mufti_id" class="form-select fatwa-form-input @error('mufti_id') is-invalid @enderror" required>
                                <option value="">Select Mufti...</option>
                                @forelse($muftis as $m)
                                    @php
                                        $isMatch = $question->madhab_id && $m->madhab_id === $question->madhab_id;
                                        $madhabSuffix = $m->madhab ? ' (' . $m->madhab->name . ($isMatch ? ' — Recommended' : '') . ')' : '';
                                    @endphp
                                    <option value="{{ $m->id }}" {{ old('mufti_id', $question->assigned_mufti_id) == $m->id ? 'selected' : '' }}>
                                        {{ $m->name }}{{ $madhabSuffix }}
                                    </option>
                                @empty
                                    <option value="" disabled>No active Muftis registered</option>
                                @endforelse
                            </select>
                            @error('mufti_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="text-muted small mt-1" style="font-size: 0.76rem;">
                                Select the scholar delivering or approving this verdict.
                            </div>
                        </div>

                        {{-- Fatwa Language --}}
                        <div>
                            <label class="form-label small fw-bold text-dark mb-1">
                                Fatwa Language
                            </label>
                            <select name="language" class="form-select fatwa-form-input">
                                <option value="Malayalam" {{ old('language', $question->language) == 'Malayalam' ? 'selected' : '' }}>Malayalam (മലയാളം)</option>
                                <option value="English" {{ old('language', $question->language) == 'English' ? 'selected' : '' }}>English</option>
                                <option value="Arabic" {{ old('language', $question->language) == 'Arabic' ? 'selected' : '' }}>Arabic (العربية)</option>
                                <option value="Urdu" {{ old('language', $question->language) == 'Urdu' ? 'selected' : '' }}>Urdu (اردو)</option>
                                <option value="Hindi" {{ old('language', $question->language) == 'Hindi' ? 'selected' : '' }}>Hindi (हिन्दी)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Card C: Archive Featured Image (16:9 Thumbnail) --}}
                    <div class="modern-card p-4 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <i class="bi bi-image text-primary fs-5"></i>
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.98rem;">Archive Thumbnail</h6>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark mb-1">
                                Featured Image (16:9)
                            </label>
                            <input type="file" id="image-input" class="form-control form-control-sm fatwa-form-input mb-1" accept="image/*">
                            <input type="hidden" name="featured_image_cropped" id="featured_image_cropped">
                            <span class="text-muted small d-block" style="font-size: 0.74rem;">
                                Recommended: 800x450 (16:9). Uploaded image opens crop tool.
                            </span>
                        </div>

                        <div id="image-preview-container" class="{{ $question->featured_image || $question->fatwa?->featured_image ? '' : 'd-none' }}">
                            <div class="position-relative rounded-3 overflow-hidden border shadow-sm" style="aspect-ratio: 16/9; background: #F1F5F9;">
                                @php
                                    $imgSrc = $question->featured_image 
                                        ? asset($question->featured_image) 
                                        : ($question->fatwa?->featured_image ? asset($question->fatwa->featured_image) : '');
                                @endphp
                                <img id="image-preview" 
                                     src="{{ $imgSrc }}" 
                                     class="w-100 h-100" 
                                     style="object-fit: cover;" 
                                     alt="Fatwa Featured Image">
                            </div>
                        </div>
                    </div>

                    {{-- Card D: Scholarly Editorial Checklist --}}
                    <div class="modern-card p-4">
                        <div class="d-flex align-items-center gap-2 mb-2 text-primary">
                            <i class="bi bi-shield-check fs-5"></i>
                            <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">Editorial Checklist</h6>
                        </div>
                        <ul class="text-muted ps-3 mb-0" style="line-height: 1.8; font-size: 0.8rem;">
                            <li>Minimum <strong>30 characters</strong> required in answer.</li>
                            <li>Ensure text is grammatically clear and respectful.</li>
                            <li>Cite classical references with Arabic text and book titles.</li>
                            <li>Type <code>@</code> to cross-reference existing fatwas.</li>
                            <li>Align with the questioner's requested Madhab school.</li>
                        </ul>
                    </div>

                </div>
            </div>

        </div>
    </form>
</div>

{{-- ── Cropper Modal ── --}}
<div class="modal fade" id="cropModal" tabindex="-1" data-bs-backdrop="static" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header py-3 px-4 bg-light border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-crop text-primary fs-5"></i>
                    <h6 class="modal-title fw-bold text-dark mb-0">Crop Featured Image (16:9 Ratio)</h6>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center overflow-hidden" style="background: #0F172A;">
                <div class="img-container" style="max-height: 480px;">
                    <img id="cropper-image" src="" style="max-width: 100%; display: block;" alt="Image to crop">
                </div>
            </div>
            <div class="modal-footer py-2.5 px-4 bg-light border-top d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-1.5" data-bs-dismiss="modal" style="border-radius: 8px;">
                    Cancel
                </button>
                <button type="button" class="btn btn-sm btn-primary px-4 py-1.5 fw-semibold" id="crop-save-btn" style="border-radius: 8px;">
                    <i class="bi bi-check2 me-1"></i> Apply & Use Cropped Image
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── ChatGPT AI Prompt Modal (Confirmation & Fallback) ── --}}
<div class="modal fade" id="chatGptPromptModal" tabindex="-1" aria-labelledby="chatGptPromptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header py-3 px-4 bg-light border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-circle p-2" style="background: #10A37F; color: #fff;">
                        <i class="bi bi-stars fs-6"></i>
                    </span>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0" id="chatGptPromptModalLabel">ChatGPT AI Editorial Prompt</h6>
                        <span class="text-muted small" style="font-size: 0.78rem;">Question Inquiry &amp; Summary Generation</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-success d-flex align-items-center gap-2 py-2.5 px-3 mb-3" style="border-radius: 10px; font-size: 0.85rem;">
                    <i class="bi bi-check-circle-fill text-success fs-5 flex-shrink-0"></i>
                    <div>
                        <strong>Prompt generated &amp; copied to your clipboard!</strong> We have requested your browser to open ChatGPT in a new tab.
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-bold text-dark mb-0">Generated Message Sent to ChatGPT:</label>
                    <button type="button" class="btn btn-xs btn-outline-secondary py-1 px-2.5" onclick="copyAiPromptAgain()" style="font-size: 0.76rem; border-radius: 6px;">
                        <i class="bi bi-clipboard me-1"></i> Copy Prompt
                    </button>
                </div>
                <textarea id="aiPromptTextarea" class="form-control" rows="10" readonly style="font-family: monospace; font-size: 0.82rem; background: #F8FAFC; border-radius: 10px; line-height: 1.5;"></textarea>
            </div>
            <div class="modal-footer py-2.5 px-4 bg-light border-top d-flex justify-content-between align-items-center">
                <span class="text-muted small" style="font-size: 0.78rem;">
                    <i class="bi bi-info-circle me-1"></i>If your browser blocked the new tab, click "Open in ChatGPT" below.
                </span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Close</button>
                    <a id="btnOpenChatGptDirect" href="https://chatgpt.com" target="_blank" class="btn btn-sm btn-success fw-bold d-inline-flex align-items-center gap-1.5" style="background: #10A37F; border-color: #10A37F; border-radius: 8px;">
                        <i class="bi bi-box-arrow-up-right"></i> Open in ChatGPT
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
$(document).ready(function() {
    // Mention Button for Summernote
    const MentionButton = function (context) {
        const ui = $.summernote.ui;
        const button = ui.button({
            contents: '<i class="bi bi-at text-success fw-bold"></i> Ref Fatwa',
            tooltip: 'Insert Published Fatwa Reference (@ID)',
            click: function () {
                window.openFatwaMentionPicker($('#summernote'), '', true);
            }
        });
        return button.render();
    };

    // 1. Question Body HTML Editor
    $('#summernote-question').summernote({
        height: 180,
        placeholder: 'Original question text with rich HTML formatting...',
        dialogsInBody: true,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'hr']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        styleTags: ['p', 'h4', 'h5', 'h6', 'blockquote']
    });

    // 2. Full Fatwa Answer HTML Editor
    $('#summernote').summernote({
        height: 400,
        placeholder: 'Write the full scholarly fatwa answer here... (Type @ to search and reference published fatwas)',
        dialogsInBody: true,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'hr']],
            ['custom', ['mentionRef']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        styleTags: ['p', 'h3', 'h4', 'h5', 'h6', 'blockquote'],
        buttons: {
            mentionRef: MentionButton
        },
        callbacks: {
            onChange: function(contents, $editable) {
                updateCharacterCount(contents);
            },
            onKeyup: function(e) {
                updateCharacterCount($('#summernote').summernote('code'));
            }
        },
        hint: {
            match: /@([a-zA-Z0-9_-]*)$/,
            search: function (keyword, callback) {
                $.ajax({
                    url: '{{ route("fatwa.mention.search") }}',
                    data: { q: keyword },
                    success: function (data) {
                        callback(data);
                    }
                });
            },
            template: function (item) {
                return '<div class="d-flex justify-content-between align-items-center gap-2 p-1">' +
                       '<div><span class="badge bg-success me-1">' + item.question_id + '</span> <strong class="text-dark">' + item.title + '</strong></div>' +
                       '<small class="text-muted text-nowrap ms-2"><i class="bi bi-calendar3 me-1"></i>' + item.published_date + '</small>' +
                       '</div>';
            },
            content: function (item) {
                return '@{' + item.question_id + '} ';
            }
        }
    });

    // Live Character Counter Function
    function updateCharacterCount(htmlContent) {
        const text = $('<div>').html(htmlContent || '').text().trim();
        const len = text.length;
        const numEl = document.getElementById('charCountNum');
        const badgeEl = document.getElementById('charCountBadge');
        if (numEl) numEl.textContent = len;

        if (badgeEl) {
            if (len >= 30) {
                badgeEl.className = 'char-meter-badge char-meter-success';
            } else {
                badgeEl.className = 'char-meter-badge char-meter-warning';
            }
        }
    }

    // Initialize character counter with current content
    updateCharacterCount($('#summernote').val());

    // Sync Summernote code before form submit
    $('#fatwaAnswerForm').on('submit', function() {
        if ($('#summernote-question').length && $('#summernote-question').summernote) {
            $('#summernote-question').val($('#summernote-question').summernote('code'));
        }
        if ($('#summernote').length && $('#summernote').summernote) {
            $('#summernote').val($('#summernote').summernote('code'));
        }
    });
});

// Dynamic Supporting References Handler
function reindexReferences() {
    document.querySelectorAll('.reference-row').forEach((row, idx) => {
        const indexEl = row.querySelector('.ref-index');
        if (indexEl) indexEl.textContent = idx + 1;
    });
}

document.getElementById('add-reference').addEventListener('click', function() {
    const container = document.getElementById('references-container');
    const firstRow = container.querySelector('.reference-row');
    if (!firstRow) return;

    const template = firstRow.cloneNode(true);
    template.querySelectorAll('textarea, input').forEach(el => el.value = '');
    template.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
    container.appendChild(template);
    reindexReferences();
});

// Remove Reference Row Delegation
document.addEventListener('click', function(e) {
    const removeBtn = e.target.closest('.remove-ref');
    if (removeBtn) {
        const rows = document.querySelectorAll('.reference-row');
        if (rows.length > 1) {
            removeBtn.closest('.reference-row').remove();
            reindexReferences();
        } else {
            removeBtn.closest('.reference-row').querySelectorAll('textarea, input').forEach(el => el.value = '');
        }
    }
});

// --- Cropper.js Logic ---
let cropper;
const imageInput = document.getElementById('image-input');
const cropperImage = document.getElementById('cropper-image');
const cropModal = new bootstrap.Modal(document.getElementById('cropModal'));
const previewImg = document.getElementById('image-preview');
const previewContainer = document.getElementById('image-preview-container');
const hiddenInput = document.getElementById('featured_image_cropped');

if (imageInput) {
    imageInput.addEventListener('change', function (e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            const reader = new FileReader();
            reader.onload = function (event) {
                cropperImage.src = event.target.result;
                cropModal.show();
            };
            reader.readAsDataURL(files[0]);
        }
    });

    document.getElementById('cropModal').addEventListener('shown.bs.modal', function () {
        cropper = new Cropper(cropperImage, {
            aspectRatio: 16 / 9,
            viewMode: 1,
            autoCropArea: 1,
        });
    });

    document.getElementById('cropModal').addEventListener('hidden.bs.modal', function () {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        imageInput.value = ''; // Reset input to allow re-selection
    });

    document.getElementById('crop-save-btn').addEventListener('click', function () {
        if (!cropper) return;
        
        // Get optimized canvas (800x450 is standard 16:9 for high quality)
        const canvas = cropper.getCroppedCanvas({
            width: 800,
            height: 450,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        // Convert to base64 with quality optimization (0.85)
        const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
        
        previewImg.src = dataUrl;
        hiddenInput.value = dataUrl;
        previewContainer.classList.remove('d-none');
        cropModal.hide();
    });
}

// ── Send Question Inquiry & Summary to ChatGPT ──
function sendQuestionSummaryToChatGPT() {
    let questionTitle = $('#question_title').val() ? $('#question_title').val().trim() : '';
    
    let questionBody = '';
    if ($('#summernote-question').length && typeof $('#summernote-question').summernote === 'function') {
        const rawCode = $('#summernote-question').summernote('code');
        questionBody = $('<div>').html(rawCode || '').text().trim();
    }
    if (!questionBody) {
        questionBody = $('#summernote-question').val() ? $('#summernote-question').val().trim() : '';
    }
    if (!questionBody) {
        questionBody = questionTitle;
    }

    let answerBody = '';
    if ($('#summernote').length && typeof $('#summernote').summernote === 'function') {
        const rawAnsCode = $('#summernote').summernote('code');
        answerBody = $('<div>').html(rawAnsCode || '').text().trim();
    }
    if (!answerBody) {
        answerBody = $('#summernote').val() ? $('#summernote').val().trim() : '';
    }

    const category = '{{ addslashes($question->category?->name ?? "General Islamic Jurisprudence") }}';
    const madhab = '{{ addslashes($question->madhab?->name ?? "General / Not Specified") }}';
    const language = $('select[name="language"]').val() || '{{ addslashes($question->language ?? "English") }}';
    const questionId = '{{ $question->question_id }}';

    const promptText = 
`You are the official Islamic Jurisprudence (Fiqh) editorial expert for the Darul Ifta Kauzariyya portal.

Generate a refined "Question Inquiry & Summary" for the following fatwa inquiry, adhering strictly to the guidelines and specifications below:

---
FATWA INQUIRY DETAILS:
• Question ID: ${questionId}
• Category: ${category}
• Madhab (School of Thought): ${madhab}
• Requested Language: ${language}

• Original Question Inquiry:
${questionBody || 'Ruling on playing dice games'}

• Scholarly Fatwa Answer:
${answerBody || 'Playing games that utilize dice (such as Backgammon / Nard) is strictly impermissible (Haram) or severely disliked (Makruh Tahrimi) according to the consensus of classical Islamic jurists, based on explicit authentic prophetic ahadith.'}
---

STRICT GUIDELINES & REQUIREMENTS:
1. Incorporate the original question inquiry, the scholarly answer, the category (${category}), and ensure the language strictly matches "${language}".
2. Provide a single, definitive response. Do NOT offer multiple options, drafts, or alternatives for the user to choose from. Deliver only one finalized, polished text ready for immediate publication.
3. Include a final, conclusive ruling labeled as "1" (e.g., "1. Conclusive Ruling: ...") stating the definitive fiqh verdict clearly and decisively.
4. Conclude your response by explicitly asking whether an "Archive Thumbnail" should be created for this fatwa.
5. Include explicit instructions and rules regarding the creation of the "Archive Thumbnail" (including: 16:9 aspect ratio, 800x450 px dimension, dignified Islamic design aesthetic, absence of prohibited human/animal facial imagery, legible Arabic and English typography, and theme-aligned color palette).

Please provide the output now following all instructions above in ${language}.`;

    // 1. Copy to clipboard
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(promptText).catch(e => console.warn('Clipboard write error:', e));
    }

    // 2. Open ChatGPT with prompt pre-filled
    const chatGptUrl = 'https://chatgpt.com/?q=' + encodeURIComponent(promptText);
    try {
        window.open(chatGptUrl, '_blank');
    } catch (e) {
        console.warn('Window open error:', e);
    }

    // 3. Populate and show fallback / confirmation modal
    $('#aiPromptTextarea').val(promptText);
    $('#btnOpenChatGptDirect').attr('href', chatGptUrl);

    const promptModalEl = document.getElementById('chatGptPromptModal');
    if (promptModalEl) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(promptModalEl);
        modalInstance.show();
    }
}

function copyAiPromptAgain() {
    const text = $('#aiPromptTextarea').val();
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Prompt copied to clipboard successfully!');
        }).catch(() => {
            $('#aiPromptTextarea').select();
            document.execCommand('copy');
            alert('Prompt copied to clipboard!');
        });
    } else {
        $('#aiPromptTextarea').select();
        document.execCommand('copy');
        alert('Prompt copied to clipboard!');
    }
}
</script>
@include('partials.fatwa-mention-modal')
@endpush
@endsection
