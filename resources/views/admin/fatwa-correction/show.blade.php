@extends('layouts.admin')

@section('title', 'Inspect & Restore Fatwa #' . $fatwa->question_id)

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">
    {{-- Breadcrumb & Navigation Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.fatwa-correction.index') }}" class="text-decoration-none">Fatwa Data Integrity</a></li>
                    <li class="breadcrumb-item active" aria-current="page">#{{ $fatwa->question_id }}</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-2">
                <h1 class="h4 fw-bold text-dark mb-0">Fatwa Restoration Workspace: <span class="font-monospace text-primary">#{{ $fatwa->question_id }}</span></h1>
                @if($isCollision)
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1 small fw-semibold">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Confirmed Migration Collision
                    </span>
                @endif
                <span class="badge bg-{{ $fatwa->status === 'published' ? 'success' : 'warning' }} bg-opacity-10 text-{{ $fatwa->status === 'published' ? 'success' : 'warning-emphasis' }} border rounded-pill px-2.5 py-1 small">
                    ● {{ ucfirst($fatwa->status) }}
                </span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($fatwa->status === 'published')
                <a href="{{ route('fatwa.show', $fatwa) }}" target="_blank" class="btn btn-outline-primary btn-sm px-3 rounded-pill">
                    <i class="bi bi-box-arrow-up-right me-1"></i>View on Live Public Portal
                </a>
            @endif
            <a href="{{ route('admin.fatwa-correction.index') }}" class="btn btn-outline-secondary btn-sm px-3 rounded-pill">
                <i class="bi bi-arrow-left me-1"></i>Back to Overview
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-exclamation-octagon-fill fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Top Comparison Grid: Current Production Record vs. Historical Mongo Source --}}
    <div class="row g-4 mb-4">
        {{-- Panel 1: Current Production Record --}}
        <div class="col-lg-6">
            <div class="modern-card p-4 border h-100 position-relative">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 small fw-semibold">
                            Current Production Record
                        </span>
                        <span class="text-muted small">Before Correction</span>
                    </div>
                    <span class="text-muted small font-monospace">Record ID: #{{ $fatwa->id }}</span>
                </div>

                <div class="mb-3">
                    <label class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem;">Question Title</label>
                    <div class="p-2.5 bg-light rounded border text-dark fw-semibold">
                        {{ $fatwa->title ?: '— (None / Null)' }}
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem;">Question Body (Submitted Inquiry)</label>
                    <div class="p-3 bg-light rounded border text-dark" style="max-height: 180px; overflow-y: auto; font-size: 0.95rem; line-height: 1.6;">
                        {!! nl2br(e($fatwa->body)) !!}
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem;">Published Answer Text</label>
                    <div class="p-3 bg-light rounded border text-dark" style="max-height: 220px; overflow-y: auto; font-size: 0.95rem; line-height: 1.7;">
                        @if($fatwa->answer)
                            {!! $fatwa->answer !!}
                        @else
                            <em class="text-muted">No published answer recorded yet.</em>
                        @endif
                    </div>
                </div>

                <div class="row g-2 pt-2 border-top text-muted small" style="font-size: 0.8rem;">
                    <div class="col-6"><strong>Submitter:</strong> {{ $fatwa->user?->name ?? 'Guest/Anonymous' }} (ID: {{ $fatwa->user_id }})</div>
                    <div class="col-6"><strong>Madhab:</strong> {{ $fatwa->madhab?->name ?? 'General/Other' }}</div>
                    <div class="col-6"><strong>Language:</strong> {{ $fatwa->language }}</div>
                    <div class="col-6"><strong>Created At:</strong> {{ $fatwa->created_at?->format('d M Y, h:i A') }}</div>
                </div>
            </div>
        </div>

        {{-- Panel 2: Authoritative Historical Provenance (MongoDB Record) --}}
        <div class="col-lg-6">
            <div class="modern-card p-4 border h-100 position-relative bg-primary bg-opacity-10 border-primary border-opacity-25">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 small fw-semibold">
                            Authoritative Historical Source
                        </span>
                        <span class="text-primary small fw-semibold">MongoDB Archive (2019–2024)</span>
                    </div>
                    @if($mongoRecord)
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" onclick="loadMongoRecordIntoForm()">
                            <i class="bi bi-magic me-1"></i>1-Click Load into Form
                        </button>
                    @endif
                </div>

                @if($mongoRecord)
                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem;">Original Historical Title</label>
                        <div class="p-2.5 bg-white rounded border text-dark fw-semibold" id="mongoSourceTitle">
                            {{ $mongoRecord['title'] }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem;">Original Question Body (From MongoDB)</label>
                        <div class="p-3 bg-white rounded border text-dark" id="mongoSourceBody" style="max-height: 180px; overflow-y: auto; font-size: 0.95rem; line-height: 1.6;">
                            {{ $mongoRecord['body'] }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem;">Original Scholarly Answer</label>
                        <div class="p-3 bg-white rounded border text-dark" id="mongoSourceAnswer" style="max-height: 220px; overflow-y: auto; font-size: 0.95rem; line-height: 1.7;">
                            {!! $mongoRecord['answer'] !!}
                        </div>
                    </div>

                    <div class="row g-2 pt-2 border-top text-muted small" style="font-size: 0.8rem;">
                        <div class="col-6"><strong>Historical ID:</strong> {{ $mongoRecord['question_id'] }}</div>
                        <div class="col-6"><strong>Language:</strong> <span id="mongoSourceLang">{{ $mongoRecord['language'] }}</span></div>
                        <div class="col-6"><strong>Historical Status:</strong> {{ ucfirst($mongoRecord['status']) }}</div>
                        <div class="col-6"><strong>Source:</strong> MongoDB Dump Archive</div>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-archive fs-1 text-muted opacity-50 d-block mb-2"></i>
                        No matching historical record found in the MongoDB migration archive for <strong>#{{ $fatwa->question_id }}</strong>.
                        <div class="small mt-1">You can search candidate answers from other records in the Candidate Search panel below.</div>
                    </div>
                @endif

                {{-- Displaced 2025 Inquiry Notice (if this is a collision) --}}
                @if($mysqlInquiry)
                    <div class="mt-3 p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-person-fill-exclamation text-warning-emphasis fs-5"></i>
                            <span class="fw-semibold text-warning-emphasis small">Displaced 2025 Community Inquiry Detected</span>
                        </div>
                        <p class="small text-muted mb-1">
                            User #{{ $mysqlInquiry['user_id'] }} submitted: <em>"{{ $mysqlInquiry['question'] }}"</em> on the early MySQL portal.
                        </p>
                        <div class="small text-dark fw-medium">
                            <i class="bi bi-arrow-return-right me-1 text-primary"></i>Use <strong>Workflow C</strong> below to restore the historical fatwa and fork this inquiry into a new question ID!
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Panel 3: Preserved Immutable Backup Telemetry --}}
    <div class="modern-card p-4 border mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-dark bg-opacity-10 text-dark rounded-3 p-2 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                    <i class="bi bi-safe fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-0">Immutable Original Backup</h5>
                    <div class="text-muted small">Cryptographic snapshot verification and recovery assets</div>
                </div>
            </div>
            @if($latestBackup)
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1.5 small fw-semibold">
                        <i class="bi bi-shield-lock-fill me-1"></i>Snapshot SHA-256 Verified
                    </span>
                    <span class="badge bg-secondary rounded-pill px-2.5 py-1.5 small">
                        Version {{ $latestBackup->snapshot_version }}
                    </span>
                </div>
            @endif
        </div>

        @if($latestBackup)
            <div class="p-3 bg-light rounded-3 border mb-3">
                <div class="row g-3 small">
                    <div class="col-md-3">
                        <span class="text-muted d-block">Snapshot ID:</span>
                        <strong class="font-monospace">#{{ $latestBackup->id }}</strong>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted d-block">Captured On:</span>
                        <strong>{{ $latestBackup->created_at?->format('d M Y, h:i:s A') }}</strong>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted d-block">Captured By:</span>
                        <strong>{{ $latestBackup->creator?->name ?? 'Admin #' . $latestBackup->created_by_admin_id }}</strong>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted d-block">SHA-256 Hash:</span>
                        <span class="font-monospace text-truncate d-block" style="max-width: 180px;" title="{{ $latestBackup->content_sha256 }}">
                            {{ $latestBackup->content_sha256 }}
                        </span>
                    </div>
                </div>
                <div class="mt-2 pt-2 border-top text-muted small">
                    <strong>Capture Reason:</strong> {{ $latestBackup->snapshot_reason }}
                </div>
            </div>
        @else
            <div class="p-3 bg-light rounded-3 border text-muted d-flex align-items-center gap-3">
                <i class="bi bi-info-circle-fill text-primary fs-4"></i>
                <div class="small">
                    <strong>No pre-correction snapshot exists yet for this record.</strong>
                    <div class="text-secondary mt-0.5">When you submit an approved restoration below, the system will automatically create an immutable snapshot with SHA-256 verification before making any change to production.</div>
                </div>
            </div>
        @endif
    </div>

    {{-- Candidate Answer Search Drawer --}}
    <div class="modern-card p-4 border mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                    <i class="bi bi-search fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-0">Search Candidate Answers (Database & Historical Archives)</h5>
                    <div class="text-muted small">Search across existing fatwas to attach a verified answer to this question</div>
                </div>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" id="candidateSearchInput" class="form-control border-start-0" 
                           placeholder="Type keywords in Malayalam, Arabic, or English to search candidate fatwas..." 
                           onkeyup="if(event.key === 'Enter') performCandidateSearch()">
                </div>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-primary px-4 rounded-pill" onclick="performCandidateSearch()">
                    <i class="bi bi-search me-1"></i>Search Answers
                </button>
            </div>
        </div>

        {{-- Candidates Results Container --}}
        <div id="candidatesResultsContainer" style="display: none;">
            <div class="table-responsive border rounded bg-white" style="max-height: 280px; overflow-y: auto;">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead class="bg-light text-muted small">
                        <tr>
                            <th style="width: 100px;">Q-ID</th>
                            <th>Candidate Summary</th>
                            <th style="width: 110px;">Source</th>
                            <th style="width: 120px;">Madhab</th>
                            <th class="text-end" style="width: 130px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="candidatesTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Restoration Execution Center (Form) --}}
    <div class="modern-card p-4 border mb-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="bg-success bg-opacity-10 text-success rounded-3 p-2 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                <i class="bi bi-arrow-repeat fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold text-dark mb-0">Proposed Correction & Restoration Action Center</h5>
                <div class="text-muted small">Review the proposed changes, select the workflow, and confirm restoration</div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.fatwa-correction.restore') }}" id="restorationForm">
            @csrf
            <input type="hidden" name="target_id" value="{{ $fatwa->id }}">
            <input type="hidden" name="source_origin" id="inputSourceOrigin" value="mongodb_archive">
            <input type="hidden" name="source_qid" id="inputSourceQid" value="{{ $fatwa->question_id }}">

            {{-- Workflow Selector --}}
            <div class="mb-4 p-3 bg-light rounded-3 border">
                <label class="fw-bold text-dark mb-2 d-block small text-uppercase">1. Select Correction Workflow</label>
                <div class="d-flex flex-column gap-2">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="action_type" id="wfC" value="restore_and_fork" checked onchange="toggleForkSection()">
                        <label class="form-check-label text-dark fw-semibold" for="wfC">
                            Workflow C (Recommended): Restore Historical MongoDB Fatwa & Fork Displaced Community Inquiry
                            <div class="text-muted small fw-normal">Restores the authentic Quranic/historical question and answer to #{{ $fatwa->question_id }}, while creating a new inquiry in the scholar queue for the user's inquiry.</div>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="action_type" id="wfA" value="restore_historical_fatwa" onchange="toggleForkSection()">
                        <label class="form-check-label text-dark fw-semibold" for="wfA">
                            Workflow A: Restore Historical Fatwa Only
                            <div class="text-muted small fw-normal">Overwrites the question body with the authentic historical question text without forking.</div>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="action_type" id="wfB" value="select_verified_answer" onchange="toggleForkSection()">
                        <label class="form-check-label text-dark fw-semibold" for="wfB">
                            Workflow B: Attach Selected Verified Answer to Current Question
                            <div class="text-muted small fw-normal">Keeps the current question text and replaces only the answer with a verified ruling selected from Candidate Search.</div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Proposed Field Values --}}
            <div class="mb-4">
                <label class="fw-bold text-dark mb-2 d-block small text-uppercase">2. Proposed Target Record Values</label>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Proposed Question Title</label>
                        <input type="text" name="title" id="formTitle" class="form-control" 
                               value="{{ old('title', $mongoRecord['title'] ?? $fatwa->title) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Language</label>
                        <select name="language" id="formLanguage" class="form-select">
                            <option value="Malayalam" {{ ($mongoRecord['language'] ?? $fatwa->language) == 'Malayalam' ? 'selected' : '' }}>Malayalam</option>
                            <option value="Arabic" {{ ($mongoRecord['language'] ?? $fatwa->language) == 'Arabic' ? 'selected' : '' }}>Arabic</option>
                            <option value="English" {{ ($mongoRecord['language'] ?? $fatwa->language) == 'English' ? 'selected' : '' }}>English</option>
                            <option value="Urdu" {{ ($mongoRecord['language'] ?? $fatwa->language) == 'Urdu' ? 'selected' : '' }}>Urdu</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Proposed Question Body</label>
                        <textarea name="body" id="formBody" class="form-control" rows="4" required>{{ old('body', $mongoRecord['body'] ?? $fatwa->body) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Proposed Answer Content</label>
                        <textarea name="answer" id="formAnswer" class="form-control" rows="6">{{ old('answer', $mongoRecord['answer'] ?? $fatwa->answer) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Publication Status</label>
                        <select name="status" class="form-select">
                            <option value="published" selected>Published (Public Archive)</option>
                            <option value="answered">Answered (Internal)</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_public" value="1" id="formIsPublic" checked>
                            <label class="form-check-label text-dark fw-semibold small" for="formIsPublic">
                                Public Archive Visibility Enabled
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Fork Displaced Question Section --}}
            <div id="forkSection" class="mb-4 p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="fork_displaced" value="1" id="checkForkDisplaced" checked>
                    <label class="form-check-label text-dark fw-bold small" for="checkForkDisplaced">
                        Fork current inquiry into a new question ID in the scholar drafting queue
                    </label>
                </div>
                <div class="mt-2">
                    <label class="form-label small text-muted">Inquiry Content to be Preserved & Forked</label>
                    <textarea name="displaced_question_body" class="form-control form-control-sm bg-white" rows="2">{{ $mysqlInquiry['question'] ?? $fatwa->body }}</textarea>
                    <input type="hidden" name="displaced_question_user_id" value="{{ $mysqlInquiry['user_id'] ?? $fatwa->user_id }}">
                </div>
            </div>

            {{-- Rationale & Execution --}}
            <div class="mb-4">
                <label class="form-label small fw-bold text-uppercase">3. Scholarly & Administrative Rationale for Correction <span class="text-danger">*</span></label>
                <input type="text" name="reason" class="form-control" 
                       placeholder="e.g. Resolving historical MongoDB Q0053 migration collision; restored Baqarah Khatm fatwa and forked alcohol spray inquiry." 
                       value="{{ old('reason', 'Resolving historical migration collision for #' . $fatwa->question_id . '; restored authentic question text and preserved immutable snapshot.') }}" required>
            </div>

            <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                <div class="text-muted small">
                    <i class="bi bi-shield-check text-success me-1"></i>Action runs inside an atomic database transaction with pessimistic row lock.
                </div>
                <button type="button" class="btn btn-success px-4 rounded-pill fw-semibold shadow-sm" onclick="showConfirmationModal()">
                    <i class="bi bi-check2-circle me-1"></i>Preview & Execute Restoration
                </button>
            </div>
        </form>
    </div>

    {{-- Audit & Rollback History --}}
    @if($auditLogs->count())
        <div class="modern-card p-4 border">
            <h5 class="fw-bold text-dark mb-3">Audit Trail & Rollback History</h5>
            <div class="table-responsive border rounded bg-white">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead class="bg-light text-muted small">
                        <tr>
                            <th>Operation</th>
                            <th>Action Type</th>
                            <th>Admin</th>
                            <th>Changed Fields</th>
                            <th>Forked Q-ID</th>
                            <th>Timestamp</th>
                            <th class="text-end">Rollback</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($auditLogs as $log)
                            <tr>
                                <td class="font-monospace small">#{{ $log->id }}</td>
                                <td>
                                    <span class="badge bg-{{ str_contains($log->action_type, 'rollback') ? 'warning' : 'primary' }} bg-opacity-10 text-{{ str_contains($log->action_type, 'rollback') ? 'warning-emphasis' : 'primary' }} rounded-pill px-2 py-0.5">
                                        {{ $log->action_type }}
                                    </span>
                                </td>
                                <td>{{ $log->admin?->name ?? 'Admin #' . $log->admin_id }}</td>
                                <td class="small">{{ implode(', ', $log->changed_fields) }}</td>
                                <td>
                                    @if($log->forked_question_id)
                                        <span class="badge bg-light text-dark font-monospace border">{{ $log->forked_question_id }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $log->created_at?->format('d M Y, h:i A') }}</td>
                                <td class="text-end">
                                    @if($log->action_type !== 'rollback_restoration')
                                        <form method="POST" action="{{ route('admin.fatwa-correction.rollback') }}" onsubmit="return confirm('Are you sure you want to rollback this restoration? This will restore the pre-correction snapshot values.');" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="audit_log_id" value="{{ $log->id }}">
                                            <input type="hidden" name="reason" value="Reverting restoration operation #{{ $log->id }} to previous approved snapshot state.">
                                            <button type="submit" class="btn btn-outline-danger btn-xs px-2.5 rounded-pill">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>Revert
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">Reverted</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

{{-- Confirmation Modal --}}
<div class="modal fade" id="restorationConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom bg-light">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check text-success fs-4"></i>
                    Confirm Permanent Restoration
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-dark mb-3">You are about to restore fatwa record <strong class="font-monospace">#{{ $fatwa->question_id }}</strong>.</p>
                <div class="p-3 bg-light rounded-3 border small mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Target Record ID:</span>
                        <strong>#{{ $fatwa->id }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Pre-Correction Snapshot:</span>
                        <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Will be captured atomically</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Concurrency Check:</span>
                        <strong class="text-success"><i class="bi bi-lock-fill me-1"></i>Pessimistic Row Lock</strong>
                    </div>
                </div>
                <div class="alert alert-warning small py-2 px-3 mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>The original snapshot is permanent and immutable. You can rollback this action at any time from the audit history.
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success rounded-pill px-4 fw-semibold" onclick="document.getElementById('restorationForm').submit()">
                    Yes, Apply Restoration
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function loadMongoRecordIntoForm() {
    const title = document.getElementById('mongoSourceTitle')?.innerText?.trim();
    const body = document.getElementById('mongoSourceBody')?.innerText?.trim();
    const answer = document.getElementById('mongoSourceAnswer')?.innerHTML?.trim();
    const lang = document.getElementById('mongoSourceLang')?.innerText?.trim();

    if (title) document.getElementById('formTitle').value = title;
    if (body) document.getElementById('formBody').value = body;
    if (answer) document.getElementById('formAnswer').value = answer;
    if (lang) document.getElementById('formLanguage').value = lang;

    document.getElementById('inputSourceOrigin').value = 'mongodb_archive';
    alert('✅ Authoritative MongoDB title, question body, answer, and language have been loaded into the Proposed Correction form below!');
}

function toggleForkSection() {
    const isFork = document.getElementById('wfC').checked;
    const forkSec = document.getElementById('forkSection');
    const checkFork = document.getElementById('checkForkDisplaced');
    if (isFork) {
        forkSec.style.display = 'block';
        checkFork.checked = true;
    } else {
        forkSec.style.display = 'none';
        checkFork.checked = false;
    }
}

function performCandidateSearch() {
    const q = document.getElementById('candidateSearchInput').value.trim();
    if (!q) return;

    fetch(`{{ route('admin.fatwa-correction.search-candidates') }}?q=${encodeURIComponent(q)}&exclude_id={{ $fatwa->id }}`)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('candidatesResultsContainer');
            const tbody = document.getElementById('candidatesTableBody');
            tbody.innerHTML = '';

            if (!data.candidates || data.candidates.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-3 text-muted">No candidate answers found matching query.</td></tr>';
                container.style.display = 'block';
                return;
            }

            data.candidates.forEach(c => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="font-monospace fw-bold text-dark">${c.question_id || '—'}</td>
                    <td>
                        <div class="fw-semibold text-dark text-truncate" style="max-width: 320px;">${c.title || 'Untitled'}</div>
                        <div class="text-muted small text-truncate" style="max-width: 320px; font-size: 0.76rem;">${c.answer ? c.answer.replace(/<[^>]*>/g, '').substring(0, 80) : ''}</div>
                    </td>
                    <td><span class="badge bg-light text-secondary border rounded-pill">${c.source}</span></td>
                    <td><span class="small text-muted">${c.madhab_name}</span></td>
                    <td class="text-end">
                        <button type="button" class="btn btn-outline-primary btn-xs px-2.5 rounded-pill" onclick='selectCandidateAnswer(${JSON.stringify(c)})'>
                            Select Answer
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            container.style.display = 'block';
        });
}

function selectCandidateAnswer(c) {
    if (c.answer) {
        document.getElementById('formAnswer').value = c.answer;
        document.getElementById('wfB').checked = true;
        toggleForkSection();
        document.getElementById('inputSourceOrigin').value = c.source;
        document.getElementById('inputSourceQid').value = c.question_id || '';
        alert(`✅ Answer from #${c.question_id} has been attached to the form. Review and execute below!`);
    }
}

function showConfirmationModal() {
    const modal = new bootstrap.Modal(document.getElementById('restorationConfirmModal'));
    modal.show();
}
</script>
@endsection

