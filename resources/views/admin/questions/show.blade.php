@extends('layouts.admin')
@section('title', 'Question ' . $question->question_id)
@section('topbar-title', 'Question ' . $question->question_id)
@section('topbar-icon', 'bi-file-earmark-check-fill')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <a href="{{ route('admin.questions.index') }}">Questions</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <span class="text-dark fw-semibold">{{ $question->question_id }}</span>
@endsection

@section('content')
@php
    // Time since submission
    $diffDays = floor($question->created_at->diffInDays(now()));
    $timeSince = $diffDays > 0 ? "{$diffDays} Days Ago" : $question->created_at->diffForHumans(null, true) . ' ago';

    // Text metrics
    $rawBody = strip_tags($question->body ?? '');
    $wordCount = str_word_count($rawBody) ?: '—';
    $letterCount = mb_strlen($rawBody) ?: '—';
    $sentenceCount = preg_match_all('/[^\s.!?]+[.!?]+/u', $rawBody, $sm) ? count($sm[0]) : ($rawBody ? 1 : '—');

    // Questioner initials
    $nameParts = explode(' ', trim($question->user->name ?? 'Questioner'));
    $initials = strtoupper(substr($nameParts[0] ?? 'Q', 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));

    // Language font classes
    $langClass = '';
    if($question->language == 'Urdu') $langClass = 'gulzar-font';
    elseif($question->language == 'Arabic') $langClass = 'alyamama-font';
    elseif($question->language == 'Malayalam') $langClass = 'noto-serif-malayalam-font';

    // Activity logs for timeline
    $displayLogs = isset($logs) && $logs->isNotEmpty() 
        ? $logs->sortByDesc(fn($l) => $l->event_at ?? $l->created_at) 
        : \App\Models\FatwaActivityLog::getComprehensiveTimeline($question)->sortByDesc(fn($l) => $l->event_at ?? $l->created_at);
@endphp

{{-- 1. Top KPI Summary Cards Row (Spacious & Generously Padded) --}}
<div class="row g-3 mb-4">
    {{-- Card 1: Question ID & Current Status --}}
    <div class="col-6 col-md-3">
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
                    @elseif($question->status === 'accepted')
                        <span class="badge status-pill-accepted">Accepted</span>
                    @elseif($question->status === 'assigned')
                        <span class="badge status-pill-assigned">Assigned</span>
                    @elseif($question->status === 'answered')
                        <span class="badge status-pill-answered">Answered</span>
                    @elseif($question->status === 'under_review')
                        <span class="badge status-pill-review">Under Review</span>
                    @elseif($question->status === 'published')
                        <span class="badge status-pill-published">Published</span>
                    @elseif($question->status === 'rejected')
                        <span class="badge status-pill-rejected">Rejected</span>
                    @else
                        <span class="badge bg-light text-dark border">{{ ucfirst($question->status) }}</span>
                    @endif
                </div>
                <div class="text-muted text-xs mt-1">Current Status</div>
            </div>
        </div>
    </div>

    {{-- Card 2: Submitted On --}}
    <div class="col-6 col-md-3">
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
    <div class="col-6 col-md-3">
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

    {{-- Card 4: Madhab --}}
    <div class="col-6 col-md-3">
        <div class="kpi-metric-card">
            <div class="kpi-icon-box bg-success bg-opacity-10 text-success flex-shrink-0">
                <i class="bi bi-book-half fs-5"></i>
            </div>
            <div class="min-w-0">
                <div class="fw-bold text-dark fs-6 text-truncate">{{ $question->madhab?->name ?? 'Shafi\'i' }}</div>
                <div class="text-muted text-xs mt-1">Madhab</div>
            </div>
        </div>
    </div>
</div>

{{-- 2. Main Responsive Two-Column Layout --}}
<div class="row g-4">
    {{-- Left Column: Primary Question Workspace (68%) --}}
    <div class="col-lg-8">
        {{-- Question Details Main Card --}}
        <div class="modern-card mb-4 overflow-hidden">
            {{-- Card Header --}}
            <div class="detail-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-inline-flex p-2 rounded-2 bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-file-earmark-text-fill fs-5"></i>
                    </div>
                    <h5 class="fw-bold mb-0 text-dark">Question Details</h5>
                </div>
                <div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 text-xs fw-semibold dropdown-toggle d-inline-flex align-items-center gap-1"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-translate"></i>
                            <span>Translate</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 text-xs">
                            <li><h6 class="dropdown-header text-uppercase text-xs fw-bold">Translate With Google</h6></li>
                            <li><a class="dropdown-item py-2" href="https://translate.google.com/?sl=auto&tl=en&text={{ urlencode(strip_tags($question->title . ' ' . $question->body)) }}" target="_blank"><i class="bi bi-translate me-2 text-primary"></i>Translate to English</a></li>
                            <li><a class="dropdown-item py-2" href="https://translate.google.com/?sl=auto&tl=ml&text={{ urlencode(strip_tags($question->title . ' ' . $question->body)) }}" target="_blank"><i class="bi bi-translate me-2 text-success"></i>Translate to Malayalam</a></li>
                            <li><a class="dropdown-item py-2" href="https://translate.google.com/?sl=auto&tl=ar&text={{ urlencode(strip_tags($question->title . ' ' . $question->body)) }}" target="_blank"><i class="bi bi-translate me-2 text-warning"></i>Translate to Arabic</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Card Body with Generous Internal Padding --}}
            <div class="detail-card-body">
                {{-- Short Question Summary --}}
                <div class="mb-4">
                    <div class="text-muted small fw-bold text-uppercase d-flex align-items-center gap-1 mb-2" style="letter-spacing: 0.5px; font-size: 0.74rem;">
                        <i class="bi bi-chat-left-quote text-primary"></i> Short Question:
                    </div>
                    <h4 class="fw-bold text-dark mb-0 {{ $langClass }}" style="line-height: 1.45;">
                        {{ $question->title ?: 'No short title provided' }}
                    </h4>
                </div>

                {{-- Full Question Body with comfortable internal spacing --}}
                <div class="mb-4">
                    <div class="text-muted small fw-bold text-uppercase d-flex align-items-center gap-1 mb-2" style="letter-spacing: 0.5px; font-size: 0.74rem;">
                        <i class="bi bi-card-text text-primary"></i> Full Question:
                    </div>
                    <div class="question-body-box {{ $langClass }}" style="font-size: 1.05rem; line-height: 1.9; color: #1e293b;">
                        {!! $question->body !!}
                    </div>
                </div>

                {{-- Meta Information Horizontal Strip with Dedicated Comfortable Card Padding --}}
                <div class="row g-2 mb-4">
                    <div class="col-sm-6 col-md-3">
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
                    <div class="col-sm-6 col-md-3">
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
                    <div class="col-sm-6 col-md-3">
                        <div class="meta-mini-card">
                            <div class="meta-mini-icon bg-primary bg-opacity-10 text-primary rounded-circle flex-shrink-0">
                                <i class="bi bi-calendar-event-fill"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-muted text-xs">Submission Date</div>
                                <div class="fw-bold text-dark text-xs text-truncate">
                                    {{ $question->created_at->format('d M Y, H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="meta-mini-card">
                            <div class="meta-mini-icon bg-primary bg-opacity-10 text-primary rounded-circle flex-shrink-0">
                                <i class="bi bi-globe-americas"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-muted text-xs">Submission IP</div>
                                <div class="fw-bold text-dark text-xs text-truncate">
                                    {{ Auth::user()->hasRole('admin') ? ($question->ip_address ?? 'Not visible') : 'Not visible' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Question Actions Section (Spacious & Cleanly Structured) --}}
                <div class="border-top pt-4 mt-3">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                        <h6 class="fw-bold mb-0 text-dark">Question Actions</h6>
                    </div>

                    @if($question->status === 'pending')
                    {{-- Two Prominent Modern Cards: Reject vs Accept (Available ONLY Before Acceptance) --}}
                    <div class="row g-3 mb-3">
                        {{-- Reject Card Button --}}
                        <div class="col-12 col-sm-6">
                            <div class="action-card-reject cursor-pointer"
                                 data-bs-toggle="modal" data-bs-target="#rejectQuestionModal"
                                 title="Reject this question with a predefined reason">
                                <div class="action-card-icon-reject rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0">
                                    <i class="bi bi-x-lg fs-5"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="fw-bold fs-5 text-danger" style="color: #E11D48 !important;">Reject</div>
                                    <div class="text-muted small mt-0.5" style="font-size: 0.78rem;">Reject this question with a reason</div>
                                </div>
                            </div>
                        </div>

                        {{-- Accept Card Button --}}
                        <div class="col-12 col-sm-6">
                            <a href="{{ route('admin.questions.accept.form', $question) }}" class="text-decoration-none d-block">
                                <div class="action-card-accept cursor-pointer"
                                     title="Accept and categorise this question">
                                    <div class="action-card-icon-accept rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0">
                                        <i class="bi bi-check-lg fs-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="fw-bold fs-5" style="color: #059669 !important;">Accept</div>
                                        <div class="text-muted small mt-0.5" style="font-size: 0.78rem;">Accept and categorise this question</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    @endif

                    {{-- Secondary Action Toolbar --}}
                    <div class="action-secondary-toolbar no-print">
                        <div class="d-flex flex-wrap gap-2">
                            @if($question->status === 'published')
                                {{-- View Published Fatwa --}}
                                <a href="{{ route('fatwa.show', $question->slug ?: $question->id) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                    <span>View Fatwa</span>
                                </a>
                            @endif
                            {{-- Copy Link --}}
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5"
                                    id="copyShortUrlBtn"
                                    onclick="copyAdminShortUrl('{{ route('fatwa.short', $question->id) }}')" 
                                    title="Copy Short URL">
                                <i class="bi bi-link-45deg fs-6" id="copyShortUrlIcon"></i>
                                <span id="copyShortUrlText">Copy Link</span>
                            </button>

                            {{-- History --}}
                            <a href="{{ route('admin.questions.history', $question) }}" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5">
                                <i class="bi bi-clock-history"></i>
                                <span>History</span>
                            </a>

                            {{-- Print --}}
                            <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5">
                                <i class="bi bi-printer"></i>
                                <span>Print</span>
                            </button>

                            {{-- Delete (Security OTP) --}}
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5"
                                    onclick="openSecurityDeleteModal({{ $question->id }}, '{{ $question->question_id }}', '{{ addslashes(strip_tags($question->title ?: $question->body)) }}')">
                                <i class="bi bi-trash"></i>
                                <span>Delete</span>
                            </button>
                        </div>

                        <div>
                            {{-- Back to List --}}
                            <a href="{{ route('admin.questions.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5">
                                <i class="bi bi-arrow-left"></i>
                                <span>Back to List</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Additional Information Section (Generously Padded Rows) --}}
                <div class="border-top pt-4 mt-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-info-circle-fill text-primary fs-5"></i>
                        <h6 class="fw-bold mb-0 text-dark">Additional Information</h6>
                    </div>

                    <div class="row g-3">
                        {{-- Left Column Key-Values --}}
                        <div class="col-md-6">
                            <div class="info-grid-row">
                                <span class="text-muted small"><i class="bi bi-translate me-2 text-primary"></i>Language</span>
                                <span class="fw-semibold text-dark small">{{ $question->language ?? 'Not set' }}</span>
                            </div>
                            <div class="info-grid-row">
                                <span class="text-muted small"><i class="bi bi-tags me-2 text-primary"></i>Category</span>
                                <span class="fw-semibold text-dark small">{{ $question->category?->name ?? '—' }}</span>
                            </div>
                            <div class="info-grid-row">
                                <span class="text-muted small"><i class="bi bi-book me-2 text-primary"></i>Madhab Preference</span>
                                <span class="fw-semibold text-dark small">{{ $question->madhab?->name ?? 'Shafi\'i' }}</span>
                            </div>
                            <div class="info-grid-row border-bottom-0">
                                <span class="text-muted small"><i class="bi bi-person-badge me-2 text-primary"></i>Assigned Mufti</span>
                                <span class="fw-semibold text-dark small">{{ (!in_array($question->status, ['pending', 'rejected']) && $question->assignedMufti) ? $question->assignedMufti->name : '—' }}</span>
                            </div>
                        </div>

                        {{-- Right Column Key-Values --}}
                        <div class="col-md-6">
                            <div class="info-grid-row">
                                <span class="text-muted small"><i class="bi bi-card-list me-2 text-info"></i>Word Count</span>
                                <span class="fw-semibold text-dark small">{{ $wordCount }}</span>
                            </div>
                            <div class="info-grid-row">
                                <span class="text-muted small"><i class="bi bi-fonts me-2 text-info"></i>Letter Count</span>
                                <span class="fw-semibold text-dark small">{{ $letterCount }}</span>
                            </div>
                            <div class="info-grid-row">
                                <span class="text-muted small"><i class="bi bi-paragraph me-2 text-info"></i>Sentences</span>
                                <span class="fw-semibold text-dark small">{{ $sentenceCount }}</span>
                            </div>
                            <div class="info-grid-row border-bottom-0">
                                <span class="text-muted small"><i class="bi bi-paperclip me-2 text-info"></i>Attachments</span>
                                <span class="fw-semibold text-dark small">No attachments</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mufti Answer Card (If exists) --}}
        @if($question->answer || $question->draft_answer)
        <div class="modern-card mb-4 overflow-hidden">
            <div class="detail-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-chat-text-fill text-success fs-5"></i>
                    <h6 class="mb-0 fw-bold text-dark">Mufti Answer</h6>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_',' ',$question->status)) }}</span>
                    @if(in_array($question->status, ['published', 'answered']))
                    <a href="{{ route('admin.questions.answer', $question) }}" class="btn btn-sm btn-outline-primary rounded-pill py-0.5 px-2.5 text-xs fw-semibold">
                        <i class="bi bi-pencil me-1"></i>Edit Answer
                    </a>
                    @endif
                </div>
            </div>
            <div class="detail-card-body">
                @if($question->title)
                    <div class="text-muted small fw-semibold mb-1">Title:</div>
                    <h6 class="fw-bold mb-3">{!! $question->title !!}</h6>
                @endif
                <div class="question-body-box mufti-answer-body {{ $langClass }}" style="line-height: 1.85; color: #1e293b; font-size: 1rem;">
                    {!! $question->draft_answer ?? $question->answer !!}
                </div>

                {{-- Supporting References --}}
                @php
                    $refs = $question->references->isNotEmpty() ? $question->references : $question->draftReferences;
                @endphp
                @if($refs && $refs->count())
                <div class="mt-4 p-3 bg-light rounded-3 border">
                    <h6 class="fw-bold small text-muted text-uppercase mb-3 d-flex align-items-center gap-1.5" style="letter-spacing: 0.5px; font-size: 0.74rem;">
                        <i class="bi bi-journal-text text-primary"></i> Supporting References
                    </h6>
                    @foreach($refs as $ref)
                        <div class="mb-3 pb-3 border-bottom last-child-border-0 last-child-mb-0">
                            <div class="arabic-text mb-2 text-dark {{ $ref->language == 'Urdu' ? 'gulzar-font' : 'alyamama-font' }}" style="font-size: 1.15rem; line-height: 1.6;">
                                {{ $ref->arabic_text }}
                            </div>
                            <div class="small text-muted d-flex align-items-center flex-wrap gap-2">
                                <span><i class="bi bi-book me-1 text-primary"></i><strong>{{ $ref->book_name }}</strong></span>
                                <span class="opacity-50">•</span>
                                <span>{{ $ref->language }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif

                @if(in_array($question->status, ['answered', 'for_review', 'under_review']))
                <div class="mt-4 pt-3 border-top d-flex gap-2">
                    <a href="{{ route('admin.questions.verify', $question) }}" class="btn btn-warning rounded-pill px-4 fw-bold btn-sm">
                        <i class="bi bi-shield-check me-1.5"></i>Verify &amp; Publish
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Right Column: Information & Activity Panels (32%) --}}
    <div class="col-lg-4 no-print">
        {{-- Card 1: Fatwa Access & URL --}}
        <div class="modern-card mb-4 overflow-hidden">
            <div class="sidebar-info-card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-link-45deg text-primary fs-5"></i>
                    <h6 class="mb-0 fw-bold text-dark">Fatwa Access &amp; URL</h6>
                </div>
                <div>
                    @if($question->status === 'published')
                        @if($question->is_public)
                            <span class="badge" style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0; font-size: 0.7rem; border-radius: 20px;">Public</span>
                        @else
                            <span class="badge" style="background: #FFFBEB; color: #D97706; border: 1px solid #FDE68A; font-size: 0.7rem; border-radius: 20px;">Private Link</span>
                        @endif
                    @else
                        <span class="badge bg-light text-muted border" style="font-size: 0.7rem; border-radius: 20px;">{{ ucfirst($question->status) }}</span>
                    @endif
                </div>
            </div>
            <div class="sidebar-info-card-body">
                <label class="form-label text-muted fw-semibold small mb-2" style="font-size: 0.76rem;">Fatwa Direct Link:</label>
                <div class="input-group input-group-sm mb-3">
                    <input type="text" class="form-control bg-light font-monospace text-xs px-2.5"
                           value="{{ route('fatwa.short', $question->id) }}" id="sidebarShortUrlInput" readonly onclick="this.select()">
                    <button class="btn btn-success fw-bold px-3 d-inline-flex align-items-center gap-1" type="button" onclick="copySidebarShortUrl()">
                        <i class="bi bi-clipboard" id="sidebarCopyIcon"></i>
                        <span id="sidebarCopyText">Copy</span>
                    </button>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <a href="{{ route('fatwa.short', $question->id) }}" target="_blank" class="text-decoration-none small text-primary fw-semibold d-inline-flex align-items-center gap-1">
                        <i class="bi bi-box-arrow-up-right" style="font-size: 0.72rem;"></i>
                        <span>Test Short Link</span>
                    </a>
                    @if($question->status === 'published')
                    <a href="{{ route('fatwa.show', $question) }}" target="_blank" class="text-decoration-none small text-success fw-semibold d-inline-flex align-items-center gap-1">
                        <i class="bi bi-eye" style="font-size: 0.72rem;"></i>
                        <span>Open Full Page</span>
                    </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Card 2: Questioner Details --}}
        <div class="modern-card mb-4 overflow-hidden">
            <div class="sidebar-info-card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-circle text-primary fs-5"></i>
                    <h6 class="mb-0 fw-bold text-dark">Questioner Details</h6>
                </div>
                <a href="{{ route('admin.questioners.show', $question->user) }}" class="text-primary small fw-semibold text-decoration-none d-inline-flex align-items-center gap-1">
                    <i class="bi bi-box-arrow-up-right" style="font-size: 0.68rem;"></i>
                    <span>View Profile</span>
                </a>
            </div>
            <div class="sidebar-info-card-body">
                <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                    @if($question->user->avatar)
                        <img src="{{ asset($question->user->avatar) }}" class="rounded-circle border flex-shrink-0" width="46" height="46" style="object-fit: cover;">
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                             style="width: 46px; height: 46px; background: #059669; font-size: 1.1rem;">
                            {{ $initials }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <div class="fw-bold text-dark text-truncate" style="font-size: 0.95rem;">
                            {{ $question->user->name }}
                        </div>
                        @if($question->user->profile_completed)
                            <span class="badge" style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0; font-size: 0.68rem; padding: 3px 8px; border-radius: 20px;">
                                Profile Completed
                            </span>
                        @else
                            <span class="badge" style="background: #FFFBEB; color: #D97706; border: 1px solid #FDE68A; font-size: 0.68rem; padding: 3px 8px; border-radius: 20px;">
                                Profile Incomplete
                            </span>
                        @endif
                    </div>
                </div>

                <div class="d-flex flex-column text-xs">
                    <div class="questioner-detail-row">
                        <span class="text-muted d-flex align-items-center gap-2"><i class="bi bi-envelope text-primary"></i>Email</span>
                        <a href="mailto:{{ $question->user->email }}" class="text-decoration-none text-primary fw-semibold text-truncate" style="max-width: 170px;" title="{{ $question->user->email }}">
                            {{ $question->user->email }}
                        </a>
                    </div>
                    <div class="questioner-detail-row">
                        <span class="text-muted d-flex align-items-center gap-2"><i class="bi bi-telephone text-primary"></i>Mobile</span>
                        @if($question->user->mobile)
                            <a href="tel:{{ $question->user->mobile }}" class="text-decoration-none text-primary fw-semibold">
                                {{ $question->user->country_code }}{{ $question->user->mobile }}
                            </a>
                        @else
                            <span class="text-muted">Not set</span>
                        @endif
                    </div>
                    <div class="questioner-detail-row">
                        <span class="text-muted d-flex align-items-center gap-2"><i class="bi bi-person text-primary"></i>Gender</span>
                        <span class="text-dark fw-medium">{{ $question->user->gender ?? 'Not set' }}</span>
                    </div>
                    <div class="questioner-detail-row">
                        <span class="text-muted d-flex align-items-center gap-2"><i class="bi bi-book text-primary"></i>Madhab Preference</span>
                        <span class="text-dark fw-medium">{{ $question->user->madhab?->name ?? 'None' }}</span>
                    </div>
                    <div class="questioner-detail-row border-bottom-0">
                        <span class="text-muted d-flex align-items-center gap-2"><i class="bi bi-geo-alt text-primary"></i>Address</span>
                        <span class="text-muted text-end text-truncate" style="max-width: 170px;" title="{{ $question->user->district ?? 'Not provided' }}">
                            {{ $question->user->district ?? 'Not provided' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Fatwa Log History --}}
        <div class="modern-card mb-4 overflow-hidden">
            <div class="sidebar-info-card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-primary fs-5"></i>
                    <h6 class="mb-0 fw-bold text-dark">Fatwa Log History</h6>
                    <span class="badge bg-light text-primary border rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">
                        {{ $displayLogs->count() }}
                    </span>
                </div>
                <a href="{{ route('admin.questions.history', $question) }}" class="btn btn-sm btn-outline-primary rounded-pill py-0.5 px-2.5 text-xs fw-semibold d-inline-flex align-items-center gap-1">
                    <i class="bi bi-plus" style="font-size: 0.75rem;"></i>
                    <span>Full Timeline</span>
                </a>
            </div>
            <div class="sidebar-info-card-body p-0">
                <div class="timeline-container">
                    <ul class="list-unstyled mb-0">
                        @forelse($displayLogs as $log)
                        <li class="timeline-item last-child-border-0 last-child-mb-0">
                            @if(!$loop->last)
                            <div class="position-absolute h-100 border-start border-2" style="left: 0.45rem; top: 1.1rem; border-color: #E2E8F0 !important;"></div>
                            @endif
                            <div class="position-absolute rounded-circle bg-{{ $log->color }} d-flex align-items-center justify-content-center shadow-xs"
                                 style="left: 0; width: 1.15rem; height: 1.15rem; top: 0.15rem; z-index: 2;">
                                <i class="bi {{ $log->icon }}" style="font-size: 0.55rem; color: #fff;"></i>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between align-items-start gap-1">
                                    <div class="fw-bold text-dark text-xs" style="line-height: 1.35;">
                                        {{ $log->event_label }}
                                    </div>
                                    <span class="text-muted text-[10px] ms-auto font-monospace text-nowrap">
                                        {{ $log->event_at?->format('M d, H:i') }}
                                    </span>
                                </div>
                                @if($log->notes)
                                <p class="text-muted mb-1 text-[11px] line-height-sm mt-1 bg-light p-2 rounded border border-light">
                                    {{ Str::limit($log->notes, 120) }}
                                </p>
                                @endif
                                <div class="d-flex justify-content-between align-items-center text-[10px] text-muted mt-1">
                                    <span>
                                        <i class="bi bi-person me-1"></i>{{ $log->actor->name ?? ($question->user?->name ?? 'System') }}
                                    </span>
                                    <span>
                                        {{ $log->event_at?->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </li>
                        @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-clock-history d-block fs-3 mb-2 opacity-25"></i>
                            No activity logs found.
                        </div>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Visibility & Publishing Controls (For Published Fatwas) --}}
        @if($question->status === 'published')
        <div class="modern-card mb-4 overflow-hidden">
            <div class="sidebar-info-card-header">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-sliders text-primary"></i> Visibility &amp; Publishing
                </h6>
            </div>
            <div class="sidebar-info-card-body small">
                {{-- Toggle Public / Private --}}
                <form method="POST" action="{{ route('admin.questions.toggle-public', $question) }}" class="mb-2">
                    @csrf
                    @if($question->is_public)
                        <button type="submit" class="btn btn-sm btn-outline-warning w-100 text-start d-flex align-items-center justify-content-between rounded-pill py-2 px-3"
                                onclick="return confirm('Make this fatwa PRIVATE? It will be hidden from the public archive and search, but accessible via shared link (requires login).')">
                            <span><i class="bi bi-eye-slash me-2"></i>Hide from Public Archive</span>
                            <span class="badge bg-warning text-dark">Make Private</span>
                        </button>
                    @else
                        <button type="submit" class="btn btn-sm btn-outline-success w-100 text-start d-flex align-items-center justify-content-between rounded-pill py-2 px-3"
                                onclick="return confirm('Make this fatwa PUBLIC? It will appear in public archives, searches, and the home page.')">
                            <span><i class="bi bi-globe2 me-2"></i>Publish to Public Archive</span>
                            <span class="badge bg-success">Make Public</span>
                        </button>
                    @endif
                </form>

                {{-- Unpublish Entirely --}}
                <form method="POST" action="{{ route('admin.questions.unpublish', $question) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100 text-start d-flex align-items-center justify-content-between rounded-pill py-2 px-3"
                            onclick="return confirm('Are you sure you want to UNPUBLISH this fatwa? Its status will revert to Answered / Draft and it will no longer be accessible as published.')">
                        <span><i class="bi bi-arrow-counterclockwise me-2"></i>Unpublish Fatwa</span>
                        <span class="badge bg-danger">Revert</span>
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Assign Mufti Card (if accepted or assigned) --}}
        @if(in_array($question->status, ['accepted','assigned']))
        <div class="modern-card mb-4 overflow-hidden">
            <div class="sidebar-info-card-header">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-person-badge text-primary"></i> Assign to Mufti
                </h6>
            </div>
            <div class="sidebar-info-card-body">
                <form method="POST" action="{{ route('admin.questions.assign', $question) }}">
                    @csrf
                    <select name="mufti_id" class="form-select form-select-sm mb-3 rounded-3 py-2" required>
                        <option value="">Select Mufti...</option>
                        @forelse($muftis as $m)
                            @php
                                $isMatch = $question->madhab_id && $m->madhab_id === $question->madhab_id;
                                $madhabSuffix = $m->madhab ? ' (' . $m->madhab->name . ($isMatch ? ' — Recommended' : '') . ')' : '';
                            @endphp
                            <option value="{{ $m->id }}" {{ $question->assigned_mufti_id == $m->id ? 'selected' : '' }}>
                                {{ $m->name }}{{ $madhabSuffix }}
                            </option>
                        @empty
                            <option value="" disabled>No active Muftis registered</option>
                        @endforelse
                    </select>
                    <button class="btn btn-primary btn-sm w-100 rounded-pill fw-semibold py-2">
                        <i class="bi bi-person-check me-1"></i> Assign Mufti
                    </button>
                </form>

                <div class="border-top pt-3 mt-3 text-center">
                    <p class="text-muted text-xs mb-2">Or write the answer yourself (on behalf of a Mufti)</p>
                    <a href="{{ route('admin.questions.answer', $question) }}" class="btn btn-outline-success btn-sm w-100 rounded-pill fw-semibold py-2">
                        <i class="bi bi-pencil me-1"></i> Write Answer
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@if($question->status === 'pending')
{{-- 3. Modern Rejection Reason Selection Modal (Pending Inquiries Only) --}}
<div class="modal fade" id="rejectQuestionModal" tabindex="-1" aria-labelledby="rejectQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form method="POST" action="{{ route('admin.questions.reject', $question) }}" id="rejectQuestionForm" onsubmit="return validateRejectionForm()">
                @csrf
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0"
                             style="width: 44px; height: 44px; background: #EF4444;">
                            <i class="bi bi-x-octagon-fill fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark" id="rejectQuestionModalLabel">
                                Reject Question — {{ $question->question_id }}
                            </h5>
                            <p class="text-muted small mb-0 mt-0.5" style="font-size: 0.8rem;">
                                Please select a reason for rejecting this question. The selected reason will be recorded in the question history.
                            </p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    {{-- A. Predefined Rejection Reasons --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small mb-2">
                            Select Rejection Reason <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex flex-column gap-2" id="rejectionReasonsList">
                            @php
                                $reasons = [
                                    ['key' => 'Incomplete Question', 'desc' => 'The submitted question does not contain sufficient information.'],
                                    ['key' => 'Duplicate Question', 'desc' => 'A similar or identical question has already been submitted or answered.'],
                                    ['key' => 'Irrelevant to Islamic Queries', 'desc' => 'The question is unrelated to the institution\'s scope.'],
                                    ['key' => 'Insufficient Information', 'desc' => 'Additional clarification is required before the question can be reviewed.'],
                                    ['key' => 'Inappropriate Content', 'desc' => 'The submission contains inappropriate, offensive, or unacceptable content.'],
                                    ['key' => 'Spam or Promotional Content', 'desc' => 'The submission contains advertisements, spam, or promotional material.'],
                                    ['key' => 'Invalid Submission', 'desc' => 'The submitted content is invalid or cannot be processed.'],
                                    ['key' => 'Other', 'desc' => 'A reason outside the predefined categories (Explanation required).'],
                                ];
                            @endphp
                            @foreach($reasons as $r)
                            <label class="rejection-reason-option p-2.5 px-3 rounded-3 border d-flex align-items-start gap-2.5 cursor-pointer">
                                <input type="radio" name="rejection_reason" value="{{ $r['key'] }}" class="form-check-input mt-1 flex-shrink-0" onchange="handleReasonChange(this.value)">
                                <div class="min-w-0">
                                    <div class="fw-bold text-dark small">{{ $r['key'] }}</div>
                                    <div class="text-muted text-xs">{{ $r['desc'] }}</div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- B. Additional Public Explanation --}}
                    <div class="mb-3" id="explanationGroup">
                        <label class="form-label fw-bold text-dark small mb-1" id="explanationLabel">
                            Additional Explanation <span class="text-muted fw-normal">(Optional)</span>
                        </label>
                        <textarea name="explanation" id="rejectionExplanation" rows="3" class="form-control text-sm p-2.5"
                                  placeholder="Provide specific feedback or guidance for the questioner..."></textarea>
                        <div class="form-text text-xs text-muted">This explanation will be visible in the rejection details and in the email notification if enabled.</div>
                    </div>

                    {{-- C. Private Internal Administrative Note --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small mb-1 d-flex align-items-center justify-content-between">
                            <span>Internal Note <span class="text-muted fw-normal">(Optional)</span></span>
                            <span class="badge" style="background: #FFFBEB; color: #D97706; border: 1px solid #FDE68A; font-size: 0.68rem;">
                                <i class="bi bi-lock-fill me-1"></i>Private Admin Note
                            </span>
                        </label>
                        <textarea name="internal_note" id="rejectionInternalNote" rows="2" class="form-control text-sm p-2.5"
                                  placeholder="Internal notes for administrators and scholars only (never visible to questioner)..."></textarea>
                    </div>

                    {{-- D. Notify Questioner Option --}}
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notify_questioner" value="1" id="notifyQuestionerCheck" checked>
                            <label class="form-check-label fw-semibold text-dark small" for="notifyQuestionerCheck">
                                Send Rejection Notification Email to Questioner ({{ $question->user->email }})
                            </label>
                            <div class="text-muted text-xs mt-0.5">
                                Dispatches a formal notification with the selected reason and public explanation. Internal notes are strictly excluded.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 px-4 pb-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold btn-sm" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold btn-sm d-inline-flex align-items-center gap-1.5" id="confirmRejectBtn" disabled>
                        <i class="bi bi-x-circle-fill"></i>
                        <span>Confirm Rejection</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- 4. High-Security Delete OTP Modal --}}
<x-fatwa-delete-security-modal />

@endsection

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

    /* ── Question Body Text Box ── */
    .question-body-box {
        padding: 1.35rem 1.65rem !important;
        border-radius: 12px !important;
        background: #F8FAFC !important;
        border: 1px solid #E2E8F0 !important;
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

    /* ── Prominent Action Cards (Reject vs Accept) ── */
    .action-card-reject {
        background: #FFF1F2 !important;
        border: 1.5px solid #FECDD3 !important;
        border-radius: 14px !important;
        padding: 1.25rem 1.5rem !important;
        display: flex;
        align-items: center;
        gap: 1.15rem;
        transition: all 0.22s ease-in-out;
    }
    .action-card-reject:hover {
        background: #FFE4E6 !important;
        border-color: #FDA4AF !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(225, 29, 72, 0.12);
    }
    .action-card-icon-reject {
        width: 48px;
        height: 48px;
        background: #EF4444;
        box-shadow: 0 3px 8px rgba(239, 68, 68, 0.3);
    }

    .action-card-accept {
        background: #ECFDF5 !important;
        border: 1.5px solid #A7F3D0 !important;
        border-radius: 14px !important;
        padding: 1.25rem 1.5rem !important;
        display: flex;
        align-items: center;
        gap: 1.15rem;
        transition: all 0.22s ease-in-out;
    }
    .action-card-accept:hover {
        background: #D1FAE5 !important;
        border-color: #6EE7B7 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.12);
    }
    .action-card-icon-accept {
        width: 48px;
        height: 48px;
        background: #10B981;
        box-shadow: 0 3px 8px rgba(16, 185, 129, 0.3);
    }

    /* ── Secondary Action Toolbar ── */
    .action-secondary-toolbar {
        padding-top: 1.25rem !important;
        margin-top: 1.25rem !important;
        border-top: 1px solid #E2E8F0;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
    }

    /* ── Additional Information 2-Column Grid ── */
    .info-grid-row {
        padding: 0.75rem 0.5rem !important;
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.82rem;
    }

    /* ── Right-Side Information Cards ── */
    .sidebar-info-card-header {
        padding: 1.15rem 1.5rem !important;
        background: #ffffff;
        border-bottom: 1px solid var(--card-border, #E2E8F0);
    }
    .sidebar-info-card-body {
        padding: 1.5rem 1.5rem !important;
        background: #ffffff;
    }
    .questioner-detail-row {
        padding: 0.75rem 0.25rem !important;
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.8rem;
    }

    /* ── Timeline ── */
    .timeline-container {
        padding: 1.25rem 1.5rem !important;
        max-height: 480px;
        overflow-y: auto;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 1.15rem !important;
        padding-left: 2rem !important;
        border-bottom: 1px solid #F8FAFC;
        margin-bottom: 0.85rem;
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
    .status-pill-accepted {
        background: #F5F3FF;
        color: #6D28D9;
        border: 1px solid #DDD6FE;
        font-weight: 700;
        border-radius: 20px;
        padding: 4px 10px;
        font-size: 0.72rem;
    }
    .status-pill-assigned {
        background: #ECFEFF;
        color: #0E7490;
        border: 1px solid #A5F3FC;
        font-weight: 700;
        border-radius: 20px;
        padding: 4px 10px;
        font-size: 0.72rem;
    }
    .status-pill-answered {
        background: #EFF6FF;
        color: #1D4ED8;
        border: 1px solid #BFDBFE;
        font-weight: 700;
        border-radius: 20px;
        padding: 4px 10px;
        font-size: 0.72rem;
    }
    .status-pill-review {
        background: #FEF3C7;
        color: #B45309;
        border: 1px solid #FCD34D;
        font-weight: 700;
        border-radius: 20px;
        padding: 4px 10px;
        font-size: 0.72rem;
    }
    .status-pill-published {
        background: #ECFDF5;
        color: #047857;
        border: 1px solid #A7F3D0;
        font-weight: 700;
        border-radius: 20px;
        padding: 4px 10px;
        font-size: 0.72rem;
    }
    .status-pill-rejected {
        background: #FFF1F2;
        color: #BE123C;
        border: 1px solid #FECDD3;
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

    /* ── Rejection Reason Radio Options in Modal ── */
    .rejection-reason-option {
        transition: all 0.18s ease;
        background: #f8fafc;
    }
    .rejection-reason-option:hover {
        background: #fff;
        border-color: #94a3b8 !important;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }
    .rejection-reason-option:has(input:checked) {
        background: #FFF1F2 !important;
        border-color: #FDA4AF !important;
        box-shadow: 0 0 0 1px #E11D48 inset;
    }

    .cursor-pointer { cursor: pointer; }
    .text-xs { font-size: 0.75rem !important; }
    .text-[10px] { font-size: 10px !important; }
    .text-[11px] { font-size: 11px !important; }
    .last-child-border-0:last-child { border-bottom: none !important; }
    .last-child-mb-0:last-child { margin-bottom: 0 !important; }

    /* ── Mobile Responsive Padding Adjustments ── */
    @media (max-width: 576px) {
        .detail-card-header,
        .sidebar-info-card-header {
            padding: 1rem 1.15rem !important;
        }
        .detail-card-body,
        .sidebar-info-card-body {
            padding: 1.15rem 1.15rem !important;
        }
        .question-body-box {
            padding: 1rem 1.15rem !important;
        }
        .action-card-reject,
        .action-card-accept {
            padding: 1rem 1.15rem !important;
        }
        .kpi-metric-card {
            padding: 1rem !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Copy Admin Short URL
    function copyAdminShortUrl(url) {
        navigator.clipboard.writeText(url).then(() => {
            const btnText = document.getElementById('copyShortUrlText');
            const btnIcon = document.getElementById('copyShortUrlIcon');
            const btn = document.getElementById('copyShortUrlBtn');
            if (btnText && btnIcon) {
                btnText.innerText = 'Copied!';
                btnIcon.className = 'bi bi-check2-circle';
                btn.classList.remove('btn-outline-success');
                btn.classList.add('btn-success', 'text-white');
                setTimeout(() => {
                    btnText.innerText = 'Copy Link';
                    btnIcon.className = 'bi bi-link-45deg fs-6';
                    btn.classList.add('btn-outline-success');
                    btn.classList.remove('btn-success', 'text-white');
                }, 2000);
            }
        }).catch(() => {
            prompt('Public Short URL:', url);
        });
    }

    // Copy Sidebar Short URL
    function copySidebarShortUrl() {
        const input = document.getElementById('sidebarShortUrlInput');
        if (!input) return;
        navigator.clipboard.writeText(input.value).then(() => {
            const text = document.getElementById('sidebarCopyText');
            const icon = document.getElementById('sidebarCopyIcon');
            if (text && icon) {
                text.innerText = 'Copied!';
                icon.className = 'bi bi-check2-circle';
                setTimeout(() => {
                    text.innerText = 'Copy';
                    icon.className = 'bi bi-clipboard me-1';
                }, 2000);
            }
        }).catch(() => {
            input.select();
            document.execCommand('copy');
        });
    }

    // Handle Reason Selection in Rejection Modal
    let selectedReasonValue = '';
    function handleReasonChange(value) {
        selectedReasonValue = value;
        const confirmBtn = document.getElementById('confirmRejectBtn');
        const expLabel = document.getElementById('explanationLabel');
        const expInput = document.getElementById('rejectionExplanation');

        if (confirmBtn) {
            confirmBtn.removeAttribute('disabled');
        }

        if (value.toLowerCase() === 'other') {
            if (expLabel) {
                expLabel.innerHTML = 'Additional Explanation <span class="text-danger">* (Required for "Other")</span>';
            }
            if (expInput) {
                expInput.setAttribute('required', 'required');
                expInput.focus();
            }
        } else {
            if (expLabel) {
                expLabel.innerHTML = 'Additional Explanation <span class="text-muted fw-normal">(Optional)</span>';
            }
            if (expInput) {
                expInput.removeAttribute('required');
            }
        }
    }

    // Validate Rejection Form before submission
    function validateRejectionForm() {
        if (!selectedReasonValue) {
            alert('Please select a rejection reason before confirming.');
            return false;
        }

        const expInput = document.getElementById('rejectionExplanation');
        if (selectedReasonValue.toLowerCase() === 'other' && (!expInput || !expInput.value.trim())) {
            alert('An explanation is required when "Other" is selected.');
            expInput?.focus();
            return false;
        }

        return confirm('Are you sure you want to REJECT Question {{ $question->question_id }}?\n\nSelected Reason: ' + selectedReasonValue);
    }
</script>
@endpush
