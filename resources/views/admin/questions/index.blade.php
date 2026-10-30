@extends('layouts.admin')

@php
    $statusMap = [
        'pending'      => 'Pending (For Review)',
        'accepted'     => 'Accepted',
        'assigned'     => 'Assigned to Mufti',
        'drafted'      => 'Mufti Drafted',
        'answered'     => 'Answered by Mufti',
        'under_review' => 'Under Review (Returned)',
        'published'    => 'Published to Public',
        'rejected'     => 'Rejected',
    ];
    $selectedStatus = request('status');
    $statusLabel = $statusMap[$selectedStatus] ?? 'All Questions';
@endphp

@section('title', 'Question Management')
@section('topbar-title', 'Question Management')
@section('topbar-icon', 'bi-file-earmark-text-fill')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <a href="{{ route('admin.questions.index') }}">Questions</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <span class="text-dark fw-semibold">{{ $statusLabel }}</span>
@endsection

@section('content')

{{-- 1. Metric Summary Cards Row (Reference Design) --}}
<div class="row g-3 mb-4">
    {{-- Total Questions --}}
    <div class="col-12 col-sm-6 col-xl">
        <div class="metric-card-tile d-flex align-items-center justify-content-between h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-square" style="background: #EFF6FF; color: #2563EB;">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div>
                    <h3 class="metric-val-num">{{ number_format($summaryStats['total'] ?? 0) }}</h3>
                    <div class="metric-label-txt">Total Questions</div>
                </div>
            </div>
            <span class="metric-trend-pill" style="background: #ECFDF5; color: #059669;">
                +12% <i class="bi bi-arrow-up-short"></i>
            </span>
        </div>
    </div>

    {{-- Published to Public --}}
    <div class="col-12 col-sm-6 col-xl">
        <div class="metric-card-tile d-flex align-items-center justify-content-between h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-square" style="background: #ECFDF5; color: #059669;">
                    <i class="bi bi-journal-check"></i>
                </div>
                <div>
                    <h3 class="metric-val-num">{{ number_format($summaryStats['published'] ?? 0) }}</h3>
                    <div class="metric-label-txt">Published to Public</div>
                </div>
            </div>
            <span class="metric-trend-pill" style="background: #ECFDF5; color: #059669;">
                +8% <i class="bi bi-arrow-up-short"></i>
            </span>
        </div>
    </div>

    {{-- Pending Review --}}
    <div class="col-12 col-sm-6 col-xl">
        <div class="metric-card-tile d-flex align-items-center justify-content-between h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-square" style="background: #FFFBEB; color: #D97706;">
                    <i class="bi bi-cup-hot"></i>
                </div>
                <div>
                    <h3 class="metric-val-num">{{ number_format($summaryStats['pending'] ?? 0) }}</h3>
                    <div class="metric-label-txt">Pending Review</div>
                </div>
            </div>
            <span class="metric-trend-pill" style="background: #FFF1F2; color: #E11D48;">
                -3% <i class="bi bi-arrow-down-short"></i>
            </span>
        </div>
    </div>

    {{-- Under Review --}}
    <div class="col-12 col-sm-6 col-xl">
        <div class="metric-card-tile d-flex align-items-center justify-content-between h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-square" style="background: #FAF5FF; color: #9333EA;">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <div>
                    <h3 class="metric-val-num">{{ number_format($summaryStats['under_review'] ?? 0) }}</h3>
                    <div class="metric-label-txt">Under Review</div>
                </div>
            </div>
            <span class="metric-trend-pill" style="background: #ECFDF5; color: #059669;">
                +5% <i class="bi bi-arrow-up-short"></i>
            </span>
        </div>
    </div>

    {{-- Rejected --}}
    <div class="col-12 col-sm-6 col-xl">
        <div class="metric-card-tile d-flex align-items-center justify-content-between h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-square" style="background: #FFF1F2; color: #E11D48;">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div>
                    <h3 class="metric-val-num">{{ number_format($summaryStats['rejected'] ?? 0) }}</h3>
                    <div class="metric-label-txt">Rejected</div>
                </div>
            </div>
            <span class="metric-trend-pill" style="background: #FFF1F2; color: #E11D48;">
                -2% <i class="bi bi-arrow-down-short"></i>
            </span>
        </div>
    </div>
</div>

{{-- 2. Horizontal Filter Bar Card (Reference Design) --}}
<div class="modern-card p-3 mb-4">
    <form method="GET" action="{{ route('admin.questions.index') }}" class="row g-2 align-items-center">
        {{-- Search Input --}}
        <div class="col-12 col-lg">
            <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.85rem;"></i>
                <input type="text" 
                       name="search" 
                       class="form-control modern-input ps-5" 
                       placeholder="Search by Question ID, questioner name, email or keywords..."
                       value="{{ request('search') }}">
            </div>
        </div>

        {{-- Status Filter --}}
        <div class="col-6 col-md-3 col-lg-2">
            <select name="status" class="form-select modern-select">
                <option value="">All Statuses</option>
                @foreach($statusMap as $val => $lbl)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>

        {{-- Madhab Filter --}}
        <div class="col-6 col-md-3 col-lg-2">
            <select name="madhab_id" class="form-select modern-select">
                <option value="">All Madhabs</option>
                @foreach(\App\Models\Madhab::all() as $m)
                    <option value="{{ $m->id }}" {{ request('madhab_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Assigned Scholar Filter --}}
        <div class="col-6 col-md-3 col-lg-2">
            <select name="assigned_to" class="form-select modern-select">
                <option value="">All Assignments</option>
                @foreach($muftis as $mufti)
                    <option value="{{ $mufti->id }}" {{ request('assigned_to') == $mufti->id ? 'selected' : '' }}>
                        {{ $mufti->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Action Buttons --}}
        <div class="col-6 col-md-3 col-lg-auto d-flex gap-2">
            <button type="submit" class="btn btn-modern-primary flex-grow-1 flex-md-grow-0">
                <i class="bi bi-funnel"></i> Filter
            </button>
            <a href="{{ route('admin.questions.index') }}" class="btn btn-modern-secondary flex-grow-1 flex-md-grow-0" title="Reset Filters">
                <i class="bi bi-arrow-counterclockwise"></i> Reset
            </a>
        </div>
    </form>
</div>

{{-- 3. Top Action Bar: Export & Add Question --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="small text-muted fw-semibold">
        Showing <span class="text-dark fw-bold">{{ $questions->firstItem() ?? 0 }} - {{ $questions->lastItem() ?? 0 }}</span> of <span class="text-dark fw-bold">{{ number_format($questions->total()) }}</span> inquiries
    </div>
    <div class="d-flex align-items-center gap-2">
        {{-- Export Dropdown --}}
        <div class="dropdown">
            <button class="btn btn-modern-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-box-arrow-up"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" style="border-radius: 12px; font-size: 0.85rem;">
                <li><a class="dropdown-item py-1.5" href="{{ route('admin.fatwas.export.pdf') }}"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Export as PDF Document</a></li>
                <li><a class="dropdown-item py-1.5" href="{{ route('admin.reports.export') }}"><i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>Export Analytics CSV</a></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li><a class="dropdown-item py-1.5" href="javascript:window.print()"><i class="bi bi-printer text-muted me-2"></i>Print Table View</a></li>
            </ul>
        </div>

        {{-- Add Question Button --}}
        <a href="/ask-question" target="_blank" class="btn btn-modern-primary">
            <i class="bi bi-plus-lg"></i> Add Question
        </a>
    </div>
</div>

{{-- 4. Question Management Table Card (Reference Design) --}}
<div class="modern-card overflow-hidden">
    <div class="table-responsive">
        <table class="modern-table align-middle">
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">
                        <input type="checkbox" class="form-check-input" id="checkAll" style="border-radius: 4px;">
                    </th>
                    <th style="width: 90px;">#</th>
                    <th style="width: 220px;">Questioner</th>
                    <th>Question Summary</th>
                    <th style="width: 110px;">Madhab</th>
                    <th style="width: 190px;">Assigned To</th>
                    <th style="width: 120px;">
                        Date <i class="bi bi-arrow-down-up ms-1" style="font-size: 0.65rem;"></i>
                    </th>
                    <th style="width: 140px;">Status</th>
                    <th style="width: 140px;" class="text-end pe-3">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($questions as $q)
                @php
                    $textSample = ($q->title ?? '') . ' ' . strip_tags($q->body ?? '');
                    $isMalayalam = (bool) preg_match('/[\x{0D00}-\x{0D7F}]/u', $textSample) || strtolower($q->language ?? '') === 'malayalam';
                    $isArabic = (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $textSample) || in_array(strtolower($q->language ?? ''), ['arabic', 'ar']);
                    $isUrdu = strtolower($q->language ?? '') === 'urdu';
                    $fontClass = $isMalayalam ? 'font-malayalam' : ($isUrdu ? 'font-urdu' : ($isArabic ? 'font-arabic' : ''));

                    // Questioner Monogram Initials
                    $qName = $q->user?->name ?? 'User';
                    $words = explode(' ', trim($qName));
                    $initials = '';
                    foreach (array_slice($words, 0, 2) as $w) {
                        $initials .= strtoupper(substr($w, 0, 1));
                    }
                    $initials = $initials ?: 'U';

                    // Scholar Monogram Initials
                    $isScholarVisible = $q->assignedMufti && !in_array($q->status, ['pending', 'rejected']);
                    $sName = $isScholarVisible ? ($q->assignedMufti?->name ?? '') : '';
                    $sInitials = '';
                    if ($sName) {
                        $sWords = explode(' ', trim($sName));
                        foreach (array_slice($sWords, 0, 2) as $sw) {
                            $sInitials .= strtoupper(substr($sw, 0, 1));
                        }
                    }

                    // Avatar color variants
                    $colorPalette = ['#EFF6FF;#2563EB', '#FAF5FF;#9333EA', '#ECFDF5;#059669', '#FFFBEB;#D97706', '#F0FDF4;#16A34A'];
                    $colorIdx = crc32($qName) % count($colorPalette);
                    [$avatarBg, $avatarText] = explode(';', $colorPalette[$colorIdx]);
                @endphp
                <tr>
                    {{-- Row Checkbox --}}
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $q->id }}" style="border-radius: 4px;">
                    </td>

                    {{-- Question ID Badge --}}
                    <td>
                        @if($q->status === 'published')
                            <span class="badge" style="background: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE; font-weight: 700; border-radius: 6px; padding: 4px 8px; font-size: 0.76rem;">
                                {{ $q->question_id }}
                            </span>
                        @elseif($q->status === 'pending')
                            <span class="badge" style="background: #FFFBEB; color: #D97706; border: 1px solid #FDE68A; font-weight: 700; border-radius: 6px; padding: 4px 8px; font-size: 0.76rem;">
                                {{ $q->question_id }}
                            </span>
                        @elseif($q->status === 'under_review')
                            <span class="badge" style="background: #FAF5FF; color: #9333EA; border: 1px solid #E9D5FF; font-weight: 700; border-radius: 6px; padding: 4px 8px; font-size: 0.76rem;">
                                {{ $q->question_id }}
                            </span>
                        @elseif($q->status === 'rejected')
                            <span class="badge" style="background: #FFF1F2; color: #E11D48; border: 1px solid #FECDD3; font-weight: 700; border-radius: 6px; padding: 4px 8px; font-size: 0.76rem;">
                                {{ $q->question_id }}
                            </span>
                        @else
                            <span class="badge" style="background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0; font-weight: 700; border-radius: 6px; padding: 4px 8px; font-size: 0.76rem;">
                                {{ $q->question_id }}
                            </span>
                        @endif
                    </td>

                    {{-- Questioner Info with Monogram Avatar --}}
                    <td>
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="avatar-monogram" style="background: {{ $avatarBg }}; color: {{ $avatarText }};">
                                {{ $initials }}
                            </div>
                            <div class="overflow-hidden">
                                <div class="fw-bold text-dark text-truncate" style="max-width: 170px; font-size: 0.86rem;">
                                    {{ $q->user?->name ?? 'Guest User' }}
                                </div>
                                <div class="text-muted text-truncate" style="font-size: 0.74rem; max-width: 170px;">
                                    {{ $q->user?->email ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Question Summary (Multilingual & RTL support) --}}
                    <td>
                        <div class="fw-bold text-dark {{ $fontClass }}" dir="auto" style="font-size: 0.88rem; line-height: 1.45;">
                            {{ $q->title ?: 'No Summary Provided' }}
                        </div>
                        @if($q->body)
                            <div class="text-muted text-truncate {{ $fontClass }}" dir="auto" style="font-size: 0.78rem; max-width: 420px; line-height: 1.4; margin-top: 2px;">
                                {{ Str::limit(strip_tags($q->body), 85) }}
                            </div>
                        @endif
                    </td>

                    {{-- Madhab --}}
                    <td>
                        @php
                            $mName = $q->madhab?->name ?? '';
                        @endphp
                        @if($mName === 'Shafii')
                            <span class="badge" style="background: #EFF6FF; color: #1D4ED8; border: 1px solid #DBEAFE; font-weight: 600; border-radius: 6px; padding: 4px 9px; font-size: 0.76rem;">
                                Shafi'i
                            </span>
                        @elseif($mName === 'Hanafi')
                            <span class="badge" style="background: #FFFBEB; color: #B45309; border: 1px solid #FEF3C7; font-weight: 600; border-radius: 6px; padding: 4px 9px; font-size: 0.76rem;">
                                Hanafi
                            </span>
                        @elseif($mName)
                            <span class="badge" style="background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0; font-weight: 600; border-radius: 6px; padding: 4px 9px; font-size: 0.76rem;">
                                {{ $mName }}
                            </span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>

                    {{-- Assigned Scholar --}}
                    <td>
                        @if($isScholarVisible)
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-monogram" style="background: #FEF3C7; color: #92400E; width: 28px; height: 28px; font-size: 0.72rem;">
                                    {{ $sInitials ?: 'M' }}
                                </div>
                                <div class="text-truncate fw-semibold text-dark" style="max-width: 140px; font-size: 0.82rem;">
                                    {{ $q->assignedMufti->name }}
                                </div>
                            </div>
                        @else
                            <span class="badge" style="background: #F8FAFC; color: #94A3B8; border: 1px dashed #CBD5E1; font-weight: 500; font-size: 0.74rem;">
                                Unassigned
                            </span>
                        @endif
                    </td>

                    {{-- Date --}}
                    <td>
                        <div class="d-flex align-items-center text-muted" style="font-size: 0.82rem; white-space: nowrap;">
                            <i class="bi bi-calendar3 me-1.5" style="color: #64748B;"></i>
                            <span>{{ $q->created_at ? $q->created_at->format('d M Y') : '—' }}</span>
                        </div>
                    </td>

                    {{-- Status Badge --}}
                    <td>
                        @if($q->status === 'published')
                            <div>
                                <span class="badge d-inline-flex align-items-center gap-1.5" style="background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; font-weight: 700; border-radius: 20px; padding: 4px 9px; font-size: 0.74rem;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #10B981;"></span> Published
                                </span>
                            </div>
                            <div class="mt-1">
                                @if($q->is_public)
                                    <span class="badge d-inline-flex align-items-center gap-1" style="background: #F0FDF4; color: #166534; font-size: 0.68rem; font-weight: 600; padding: 2px 7px; border-radius: 12px;" title="Public Archive">
                                        <i class="bi bi-globe2"></i> Public
                                    </span>
                                @else
                                    <span class="badge d-inline-flex align-items-center gap-1" style="background: #FFFBEB; color: #92400E; font-size: 0.68rem; font-weight: 600; padding: 2px 7px; border-radius: 12px;" title="Private Link Only">
                                        <i class="bi bi-shield-lock"></i> Private
                                    </span>
                                @endif
                            </div>
                        @elseif($q->status === 'pending')
                            <span class="badge d-inline-flex align-items-center gap-1.5" style="background: #FFFBEB; color: #92400E; border: 1px solid #FDE68A; font-weight: 700; border-radius: 20px; padding: 4px 9px; font-size: 0.74rem;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #F59E0B;"></span> Pending
                            </span>
                        @elseif($q->status === 'under_review')
                            <span class="badge d-inline-flex align-items-center gap-1.5" style="background: #FAF5FF; color: #6B21A8; border: 1px solid #E9D5FF; font-weight: 700; border-radius: 20px; padding: 4px 9px; font-size: 0.74rem;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #A855F7;"></span> Under Review
                            </span>
                        @elseif($q->status === 'answered')
                            <span class="badge d-inline-flex align-items-center gap-1.5" style="background: #EFF6FF; color: #1E40AF; border: 1px solid #BFDBFE; font-weight: 700; border-radius: 20px; padding: 4px 9px; font-size: 0.74rem;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #3B82F6;"></span> Answered
                            </span>
                        @elseif($q->status === 'accepted')
                            <span class="badge d-inline-flex align-items-center gap-1.5" style="background: #F5F3FF; color: #5B21B6; border: 1px solid #DDD6FE; font-weight: 700; border-radius: 20px; padding: 4px 9px; font-size: 0.74rem;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #8B5CF6;"></span> Accepted
                            </span>
                        @elseif($q->status === 'assigned')
                            <span class="badge d-inline-flex align-items-center gap-1.5" style="background: #ECFEFF; color: #155E75; border: 1px solid #A5F3FC; font-weight: 700; border-radius: 20px; padding: 4px 9px; font-size: 0.74rem;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #06B6D4;"></span> Assigned
                            </span>
                        @elseif($q->status === 'drafted')
                            <span class="badge d-inline-flex align-items-center gap-1.5" style="background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; font-weight: 700; border-radius: 20px; padding: 4px 9px; font-size: 0.74rem;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #22C55E;"></span> Drafted
                            </span>
                        @elseif($q->status === 'rejected')
                            <span class="badge d-inline-flex align-items-center gap-1.5" style="background: #FFF1F2; color: #9F1239; border: 1px solid #FECDD3; font-weight: 700; border-radius: 20px; padding: 4px 9px; font-size: 0.74rem;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #F43F5E;"></span> Rejected
                            </span>
                        @else
                            <span class="badge" style="background: #F1F5F9; color: #475569; font-weight: 600; border-radius: 20px; padding: 4px 9px; font-size: 0.74rem;">
                                {{ ucfirst(str_replace('_', ' ', $q->status)) }}
                            </span>
                        @endif
                    </td>

                    {{-- Actions (Reference Design: Outline View, Edit, Red Delete) --}}
                    <td class="text-end pe-3">
                        <div class="d-inline-flex align-items-center gap-1.5">
                            {{-- View Button --}}
                            <a href="{{ route('admin.questions.show', $q) }}" 
                               class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 px-2.5 py-1" 
                               style="font-size: 0.78rem; font-weight: 600; border-radius: 8px;"
                               title="View Details">
                                <i class="bi bi-eye"></i> View
                            </a>

                            {{-- Direct Public/Archive Link --}}
                            @if($q->status === 'published')
                                <a href="/fatwa-archive/{{ $q->id }}" 
                                   target="_blank" 
                                   class="btn btn-sm btn-light border p-1 d-inline-flex align-items-center justify-content-center text-muted" 
                                   style="width: 30px; height: 30px; border-radius: 8px;"
                                   title="Open Published Fatwa in New Tab">
                                    <i class="bi bi-box-arrow-up-right" style="font-size: 0.78rem;"></i>
                                </a>
                            @else
                                <a href="{{ route('admin.questions.show', $q) }}" 
                                   class="btn btn-sm btn-light border p-1 d-inline-flex align-items-center justify-content-center text-muted" 
                                   style="width: 30px; height: 30px; border-radius: 8px;"
                                   title="Manage Question">
                                    <i class="bi bi-pencil" style="font-size: 0.78rem;"></i>
                                </a>
                            @endif

                            {{-- High Security Delete --}}
                            <button type="button" 
                                    class="btn btn-sm btn-outline-danger p-1 d-inline-flex align-items-center justify-content-center" 
                                    style="width: 30px; height: 30px; border-radius: 8px; border-color: #FECDD3; background: #FFF1F2;"
                                    title="High-Security Delete"
                                    onclick="openSecurityDeleteModal({{ $q->id }}, '{{ $q->question_id }}', '{{ addslashes(strip_tags($q->title ?: $q->body)) }}')">
                                <i class="bi bi-trash3" style="font-size: 0.82rem;"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <div class="py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-slate-300"></i>
                            <h6 class="fw-bold text-dark mb-1">No Inquiries Found</h6>
                            <p class="small text-muted mb-3">No questions match the current filter criteria.</p>
                            <a href="{{ route('admin.questions.index') }}" class="btn btn-sm btn-modern-primary">
                                Clear Filters
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 5. Modern Pagination Card Footer --}}
    <div class="p-3 bg-white border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <div class="small text-muted">
            Showing <strong>{{ $questions->firstItem() ?? 0 }}</strong> to <strong>{{ $questions->lastItem() ?? 0 }}</strong> of <strong>{{ number_format($questions->total()) }}</strong> questions
        </div>
        <div>
            {{ $questions->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('checkAll');
        const checkboxes = document.querySelectorAll('.row-checkbox');
        if (checkAll) {
            checkAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = checkAll.checked);
            });
        }
    });
</script>
@endpush

@endsection
