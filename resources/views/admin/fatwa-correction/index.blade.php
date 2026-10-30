@extends('layouts.admin')

@section('title', 'Fatwa Correction & Restoration')

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">
    {{-- Header Section --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <i class="bi bi-shield-check fs-5"></i>
                </div>
                <h1 class="h4 fw-bold text-dark mb-0">Fatwa Data Integrity & Restoration</h1>
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1 small fw-semibold">
                    <i class="bi bi-lock-fill me-1"></i>Immutable Backup Preserved
                </span>
            </div>
            <p class="text-muted small mb-0">Audit, verify, and resolve historical question-answer mismatches with permanent cryptographic snapshots.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm px-3 rounded-pill">
                <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
            </a>
        </div>
    </div>

    {{-- Telemetry Metric Tiles --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-2">
            <div class="modern-card p-3 h-100 border">
                <div class="text-muted small fw-medium mb-1">Total Inspected</div>
                <div class="h3 fw-bold text-dark mb-0">{{ number_format($metrics['total_inspected']) }}</div>
                <div class="text-muted small mt-1" style="font-size: 0.72rem;">Active inquiries</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="modern-card p-3 h-100 border bg-danger bg-opacity-10 border-danger border-opacity-25">
                <div class="text-danger small fw-semibold mb-1">Confirmed Collisions</div>
                <div class="h3 fw-bold text-danger mb-0">{{ $metrics['confirmed_collisions'] }}</div>
                <div class="text-danger small mt-1" style="font-size: 0.72rem;">Historical ID overlaps</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="modern-card p-3 h-100 border bg-warning bg-opacity-10 border-warning border-opacity-25">
                <div class="text-warning-emphasis small fw-semibold mb-1">Live Published Mismatches</div>
                <div class="h3 fw-bold text-warning-emphasis mb-0">{{ $metrics['published_collisions'] }}</div>
                <div class="text-warning-emphasis small mt-1" style="font-size: 0.72rem;">Publicly visible</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="modern-card p-3 h-100 border">
                <div class="text-muted small fw-medium mb-1">Awaiting Review</div>
                <div class="h3 fw-bold text-primary mb-0">{{ $metrics['awaiting_review'] }}</div>
                <div class="text-muted small mt-1" style="font-size: 0.72rem;">Pending restoration</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="modern-card p-3 h-100 border bg-success bg-opacity-10 border-success border-opacity-25">
                <div class="text-success small fw-semibold mb-1">Restorations Completed</div>
                <div class="h3 fw-bold text-success mb-0">{{ $metrics['restorations_count'] }}</div>
                <div class="text-success small mt-1" style="font-size: 0.72rem;">Safely restored</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="modern-card p-3 h-100 border">
                <div class="text-muted small fw-medium mb-1">Snapshots Preserved</div>
                <div class="h3 fw-bold text-dark mb-0">{{ $metrics['total_backups'] }}</div>
                <div class="text-muted small mt-1" style="font-size: 0.72rem;">Permanent backups</div>
            </div>
        </div>
    </div>

    {{-- Filter & Search Toolbar --}}
    <div class="modern-card p-3 mb-4 border">
        <form method="GET" action="{{ route('admin.fatwa-correction.index') }}" class="row g-2 align-items-center">
            {{-- Filter Pills --}}
            <div class="col-12 col-md-auto d-flex flex-wrap gap-1">
                <a href="{{ route('admin.fatwa-correction.index', ['filter' => 'collisions']) }}"
                   class="btn btn-sm rounded-pill px-3 {{ $filterType === 'collisions' ? 'btn-danger' : 'btn-outline-secondary' }}">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>All Collisions (94)
                </a>
                <a href="{{ route('admin.fatwa-correction.index', ['filter' => 'published_collisions']) }}"
                   class="btn btn-sm rounded-pill px-3 {{ $filterType === 'published_collisions' ? 'btn-warning text-dark' : 'btn-outline-secondary' }}">
                    <i class="bi bi-globe me-1"></i>Published Only (59)
                </a>
                <a href="{{ route('admin.fatwa-correction.index', ['filter' => 'restored']) }}"
                   class="btn btn-sm rounded-pill px-3 {{ $filterType === 'restored' ? 'btn-success' : 'btn-outline-secondary' }}">
                    <i class="bi bi-check-circle-fill me-1"></i>Restored History
                </a>
                <a href="{{ route('admin.fatwa-correction.index', ['filter' => 'all']) }}"
                   class="btn btn-sm rounded-pill px-3 {{ $filterType === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    All Inquiries
                </a>
            </div>

            {{-- Search Input --}}
            <div class="col-12 col-md">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0" 
                           placeholder="Search by Question ID (e.g. Q0053), question text, or answer..." 
                           value="{{ request('search') }}">
                </div>
            </div>

            {{-- Madhab Select --}}
            <div class="col-6 col-md-auto">
                <select name="madhab_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Madhabs</option>
                    @foreach($madhabs as $m)
                        <option value="{{ $m->id }}" {{ request('madhab_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary px-3 rounded-pill">Filter</button>
                @if(request()->anyFilled(['search', 'madhab_id', 'status', 'filter']))
                    <a href="{{ route('admin.fatwa-correction.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Main Records Table --}}
    <div class="modern-card border overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase" style="letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-3" style="width: 100px;">Q-ID</th>
                        <th>Question Summary / Content Preview</th>
                        <th style="width: 130px;">Madhab</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 140px;">Audit Flag</th>
                        <th class="text-end pe-3" style="width: 150px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fatwas as $fatwa)
                        @php
                            $isCollision = in_array($fatwa->question_id, $collisionQIds);
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <span class="badge bg-dark bg-opacity-10 text-dark border font-monospace fw-bold px-2 py-1">
                                    {{ $fatwa->question_id }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark mb-0 text-truncate" style="max-width: 480px;">
                                    {{ $fatwa->title ?: Str::limit(strip_tags($fatwa->body), 75) }}
                                </div>
                                <div class="text-muted small text-truncate" style="max-width: 480px; font-size: 0.78rem;">
                                    {{ Str::limit(strip_tags($fatwa->body), 90) }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5">
                                    {{ $fatwa->madhab?->name ?? 'General' }}
                                </span>
                            </td>
                            <td>
                                @if($fatwa->status === 'published')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0.5">
                                        ● Published
                                    </span>
                                @elseif($fatwa->status === 'pending')
                                    <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25 rounded-pill px-2 py-0.5">
                                        ● Pending
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2 py-0.5">
                                        ● {{ ucfirst($fatwa->status) }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($isCollision)
                                    @if($fatwa->status === 'published')
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-0.5 small fw-semibold" title="MongoDB answer overwritten onto 2025 question">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Hybrid Mismatch
                                        </span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25 rounded-pill px-2 py-0.5 small">
                                            <i class="bi bi-intersect me-1"></i>ID Collision
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5 small">
                                        Standard
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.fatwa-correction.show', $fatwa->id) }}" class="btn btn-sm btn-primary px-3 rounded-pill">
                                    Inspect & Restore <i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-shield-check fs-2 text-muted d-block mb-2"></i>
                                No inquiries found matching the selected filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($fatwas->hasPages())
            <div class="p-3 border-top bg-light">
                {{ $fatwas->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

