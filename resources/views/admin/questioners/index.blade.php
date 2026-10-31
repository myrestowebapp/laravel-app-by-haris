@extends('layouts.admin')

@section('title', 'Questioners')
@section('topbar-title', 'Questioner Directory')
@section('topbar-icon', 'bi-people-fill')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <span class="text-dark fw-semibold">Questioners</span>
@endsection

@push('styles')
<style>
    /* Questioner Directory Specific Compact Styling */
    .questioner-table thead th {
        background: #F8FAFC;
        color: #64748B;
        font-size: 0.73rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.65rem 0.85rem;
        border-bottom: 1px solid #E2E8F0;
        white-space: nowrap;
        vertical-align: middle;
    }
    .questioner-table tbody td {
        padding: 0.62rem 0.85rem;
        border-bottom: 1px solid #F1F5F9;
        font-size: 0.84rem;
        vertical-align: middle;
        background: #ffffff;
        transition: background-color 0.12s ease;
    }
    .questioner-table tbody tr:hover td {
        background-color: #F8FAFC;
    }
    .questioner-table tbody tr.row-selected td {
        background-color: #EFF6FF !important;
    }
    .sortable-th {
        cursor: pointer;
        user-select: none;
        transition: color 0.15s ease;
    }
    .sortable-th:hover {
        color: #1E293B !important;
    }
    .avatar-compact {
        width: 32px;
        height: 32px;
        font-size: 0.74rem;
        font-weight: 700;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    /* Floating Bulk Action Toolbar */
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

{{-- 1. Metric Summary Cards Row --}}
<div class="row g-3 mb-4">
    {{-- Total Registered Questioners --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card-tile d-flex align-items-center justify-content-between h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-square" style="background: #EFF6FF; color: #2563EB;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <h3 class="metric-val-num">{{ number_format($summaryStats['total'] ?? 0) }}</h3>
                    <div class="metric-label-txt">Total Inquirers</div>
                </div>
            </div>
            <span class="metric-trend-pill" style="background: #EFF6FF; color: #2563EB;">
                Active
            </span>
        </div>
    </div>

    {{-- In Progress Inquirers --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card-tile d-flex align-items-center justify-content-between h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-square" style="background: #FFFBEB; color: #D97706;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <h3 class="metric-val-num">{{ number_format($summaryStats['active'] ?? 0) }}</h3>
                    <div class="metric-label-txt">In Progress Review</div>
                </div>
            </div>
            <span class="metric-trend-pill" style="background: #FFFBEB; color: #D97706;">
                Pending
            </span>
        </div>
    </div>

    {{-- Published Fatwas Inquirers --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card-tile d-flex align-items-center justify-content-between h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-square" style="background: #ECFDF5; color: #059669;">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <div>
                    <h3 class="metric-val-num">{{ number_format($summaryStats['published'] ?? 0) }}</h3>
                    <div class="metric-label-txt">Published Answers</div>
                </div>
            </div>
            <span class="metric-trend-pill" style="background: #ECFDF5; color: #059669;">
                Archived
            </span>
        </div>
    </div>

    {{-- Countries Represented --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card-tile d-flex align-items-center justify-content-between h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-square" style="background: #FAF5FF; color: #9333EA;">
                    <i class="bi bi-globe2"></i>
                </div>
                <div>
                    <h3 class="metric-val-num">{{ number_format($summaryStats['countries'] ?? 0) }}</h3>
                    <div class="metric-label-txt">Countries Reached</div>
                </div>
            </div>
            <span class="metric-trend-pill" style="background: #FAF5FF; color: #9333EA;">
                Global
            </span>
        </div>
    </div>
</div>

{{-- 2. Modern Filter & Action Bar Card --}}
<div class="modern-card p-3 mb-3">
    <form method="GET" action="{{ route('admin.questioners.index') }}" id="filterForm" class="row g-2 align-items-center">
        {{-- Preserve Sorting --}}
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="direction" value="{{ $direction }}">

        {{-- Search Input --}}
        <div class="col-12 col-md-5 col-lg-6">
            <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.85rem;"></i>
                <input type="text" 
                       name="search" 
                       class="form-control modern-input ps-5" 
                       placeholder="Search by name, email or country..." 
                       value="{{ request('search') }}">
            </div>
        </div>

        {{-- Per Page Density Selector --}}
        <div class="col-6 col-md-3 col-lg-2">
            <select name="per_page" class="form-select modern-select" onchange="document.getElementById('filterForm').submit()">
                <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>Show 10 rows</option>
                <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>Show 20 rows</option>
                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>Show 50 rows</option>
                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>Show 100 rows</option>
            </select>
        </div>

        {{-- Buttons: Filter, Reset, Export --}}
        <div class="col-6 col-md-4 col-lg-4 d-flex gap-2 justify-content-end">
            <button type="submit" class="btn btn-modern-primary flex-grow-1 flex-md-grow-0">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
            @if(request('search') || request('sort') || request('per_page') != 20)
                <a href="{{ route('admin.questioners.index') }}" class="btn btn-modern-secondary" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            @endif

            {{-- Export Dropdown --}}
            <div class="dropdown">
                <button class="btn btn-modern-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-box-arrow-up me-1"></i> Export
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
    </form>
</div>

{{-- 3. Modern Data Table Card --}}
<div class="modern-card overflow-hidden">
    <div class="table-responsive">
        <table class="table modern-table questioner-table align-middle mb-0">
            <thead>
                <tr>
                    {{-- Master Checkbox --}}
                    <th style="width: 40px;" class="ps-3 text-center">
                        <input type="checkbox" class="form-check-input" id="checkAllQuestioners" style="border-radius: 4px; cursor: pointer;">
                    </th>
                    {{-- Index --}}
                    <th style="width: 50px;">#</th>

                    {{-- Questioner Identity (Sortable) --}}
                    @php
                        $nameDir = ($sort === 'name' && $direction === 'asc') ? 'desc' : 'asc';
                        $nameIcon = ($sort === 'name') ? ($direction === 'asc' ? 'bi-arrow-up text-primary' : 'bi-arrow-down text-primary') : 'bi-arrow-down-up text-slate-400';
                    @endphp
                    <th class="sortable-th" onclick="sortBy('name', '{{ $nameDir }}')">
                        <div class="d-inline-flex align-items-center gap-1.5">
                            <span>Questioner Identity</span>
                            <i class="bi {{ $nameIcon }}" style="font-size: 0.72rem;"></i>
                        </div>
                    </th>

                    {{-- Country (Sortable) --}}
                    @php
                        $countryDir = ($sort === 'country' && $direction === 'asc') ? 'desc' : 'asc';
                        $countryIcon = ($sort === 'country') ? ($direction === 'asc' ? 'bi-arrow-up text-primary' : 'bi-arrow-down text-primary') : 'bi-arrow-down-up text-slate-400';
                    @endphp
                    <th style="width: 150px;" class="sortable-th" onclick="sortBy('country', '{{ $countryDir }}')">
                        <div class="d-inline-flex align-items-center gap-1.5">
                            <span>Country</span>
                            <i class="bi {{ $countryIcon }}" style="font-size: 0.72rem;"></i>
                        </div>
                    </th>

                    {{-- Joined Date (Sortable) --}}
                    @php
                        $dateDir = ($sort === 'created_at' && $direction === 'asc') ? 'desc' : 'asc';
                        $dateIcon = ($sort === 'created_at') ? ($direction === 'asc' ? 'bi-arrow-up text-primary' : 'bi-arrow-down text-primary') : 'bi-arrow-down-up text-slate-400';
                    @endphp
                    <th style="width: 140px;" class="sortable-th" onclick="sortBy('created_at', '{{ $dateDir }}')">
                        <div class="d-inline-flex align-items-center gap-1.5">
                            <span>Joined Date</span>
                            <i class="bi {{ $dateIcon }}" style="font-size: 0.72rem;"></i>
                        </div>
                    </th>

                    {{-- Total Questions (Sortable) --}}
                    @php
                        $totalDir = ($sort === 'total_questions' && $direction === 'desc') ? 'asc' : 'desc';
                        $totalIcon = ($sort === 'total_questions') ? ($direction === 'asc' ? 'bi-arrow-up text-primary' : 'bi-arrow-down text-primary') : 'bi-arrow-down-up text-slate-400';
                    @endphp
                    <th style="width: 100px;" class="text-center sortable-th" onclick="sortBy('total_questions', '{{ $totalDir }}')">
                        <div class="d-inline-flex align-items-center justify-content-center gap-1.5">
                            <span>Total</span>
                            <i class="bi {{ $totalIcon }}" style="font-size: 0.72rem;"></i>
                        </div>
                    </th>

                    {{-- In Progress (Sortable) --}}
                    @php
                        $pendingDir = ($sort === 'pending_questions' && $direction === 'desc') ? 'asc' : 'desc';
                        $pendingIcon = ($sort === 'pending_questions') ? ($direction === 'asc' ? 'bi-arrow-up text-primary' : 'bi-arrow-down text-primary') : 'bi-arrow-down-up text-slate-400';
                    @endphp
                    <th style="width: 120px;" class="text-center sortable-th" onclick="sortBy('pending_questions', '{{ $pendingDir }}')">
                        <div class="d-inline-flex align-items-center justify-content-center gap-1.5">
                            <span>In Progress</span>
                            <i class="bi {{ $pendingIcon }}" style="font-size: 0.72rem;"></i>
                        </div>
                    </th>

                    {{-- Published (Sortable) --}}
                    @php
                        $pubDir = ($sort === 'published_questions' && $direction === 'desc') ? 'asc' : 'desc';
                        $pubIcon = ($sort === 'published_questions') ? ($direction === 'asc' ? 'bi-arrow-up text-primary' : 'bi-arrow-down text-primary') : 'bi-arrow-down-up text-slate-400';
                    @endphp
                    <th style="width: 120px;" class="text-center sortable-th" onclick="sortBy('published_questions', '{{ $pubDir }}')">
                        <div class="d-inline-flex align-items-center justify-content-center gap-1.5">
                            <span>Published</span>
                            <i class="bi {{ $pubIcon }}" style="font-size: 0.72rem;"></i>
                        </div>
                    </th>

                    {{-- Action --}}
                    <th style="width: 100px;" class="text-end pe-3">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($questioners as $user)
                @php
                    $qName = $user->name ?? 'User';
                    $words = explode(' ', trim($qName));
                    $initials = '';
                    foreach (array_slice($words, 0, 2) as $w) {
                        $initials .= strtoupper(substr($w, 0, 1));
                    }
                    $initials = $initials ?: 'U';

                    $colorPalette = ['#EFF6FF;#2563EB', '#FAF5FF;#9333EA', '#ECFDF5;#059669', '#FFFBEB;#D97706', '#F0FDF4;#16A34A'];
                    $colorIdx = crc32($qName) % count($colorPalette);
                    [$avatarBg, $avatarText] = explode(';', $colorPalette[$colorIdx]);
                @endphp
                <tr id="row-{{ $user->id }}">
                    {{-- Row Checkbox --}}
                    <td class="ps-3 text-center">
                        <input type="checkbox" class="form-check-input questioner-checkbox" value="{{ $user->id }}" style="border-radius: 4px; cursor: pointer;">
                    </td>

                    {{-- Row Index --}}
                    <td class="text-muted small fw-semibold">{{ $questioners->firstItem() + $loop->index }}</td>

                    {{-- Identity: 32px avatar + name + email --}}
                    <td>
                        <div class="d-flex align-items-center gap-2.5">
                            @if($user->avatar)
                                <img src="{{ asset($user->avatar) }}" class="rounded-circle border" width="32" height="32" style="object-fit: cover;">
                            @else
                                <div class="avatar-compact" style="background: {{ $avatarBg }}; color: {{ $avatarText }};">
                                    {{ $initials }}
                                </div>
                            @endif
                            <div class="overflow-hidden">
                                <div class="fw-bold text-dark text-truncate" style="max-width: 240px; font-size: 0.86rem; line-height: 1.25;">
                                    {{ $user->name }}
                                </div>
                                <div class="text-muted text-truncate" style="font-size: 0.74rem; max-width: 240px; line-height: 1.2; margin-top: 2px;">
                                    {{ $user->email }}
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Country --}}
                    <td>
                        <span class="small text-dark fw-medium d-inline-flex align-items-center gap-1.5">
                            <i class="bi bi-geo-alt text-muted" style="font-size: 0.78rem;"></i>
                            <span>{{ $user->country ?: '—' }}</span>
                        </span>
                    </td>

                    {{-- Joined Date --}}
                    <td>
                        <div class="text-muted small d-inline-flex align-items-center gap-1.5" style="font-size: 0.8rem; white-space: nowrap;">
                            <i class="bi bi-calendar3 text-slate-400" style="font-size: 0.76rem;"></i>
                            <span>{{ $user->created_at ? $user->created_at->format('d M Y') : '—' }}</span>
                        </div>
                    </td>

                    {{-- Total Questions --}}
                    <td class="text-center">
                        <span class="badge" style="background: #EFF6FF; color: #2563EB; font-weight: 700; border-radius: 20px; padding: 4px 10px; font-size: 0.76rem;">
                            {{ $user->total_questions }}
                        </span>
                    </td>

                    {{-- In Progress --}}
                    <td class="text-center">
                        @if($user->pending_questions > 0)
                            <span class="badge" style="background: #FFFBEB; color: #D97706; font-weight: 700; border-radius: 20px; padding: 4px 10px; font-size: 0.76rem;">
                                {{ $user->pending_questions }}
                            </span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>

                    {{-- Published --}}
                    <td class="text-center">
                        @if($user->published_questions > 0)
                            <span class="badge" style="background: #ECFDF5; color: #059669; font-weight: 700; border-radius: 20px; padding: 4px 10px; font-size: 0.76rem;">
                                {{ $user->published_questions }}
                            </span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>

                    {{-- Action --}}
                    <td class="text-end pe-3">
                        <a href="{{ route('admin.questioners.show', $user) }}" 
                           class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 px-2.5 py-1" 
                           style="font-size: 0.78rem; font-weight: 600; border-radius: 8px;">
                            <i class="bi bi-eye"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="bi bi-people fs-1 text-slate-300 d-block mb-2"></i>
                        <h6 class="fw-bold text-dark mb-1">No Questioners Found</h6>
                        <p class="small text-muted mb-3">No user accounts matched your search criteria.</p>
                        <a href="{{ route('admin.questioners.index') }}" class="btn btn-sm btn-modern-primary">
                            Clear Filter
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 4. Aligned Pagination Footer --}}
    <div class="p-3 bg-white border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <div class="small text-muted">
            Showing <strong class="text-dark">{{ $questioners->firstItem() ?? 0 }}</strong> to <strong class="text-dark">{{ $questioners->lastItem() ?? 0 }}</strong> of <strong class="text-dark">{{ number_format($questioners->total()) }}</strong> questioners
        </div>
        <div>
            {{ $questioners->withQueryString()->links() }}
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
        <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem;" onclick="clearSelection()">
            Deselect All
        </button>
    </div>
</div>

@push('scripts')
<script>
    // Dynamic Sorting Helper
    function sortBy(column, direction) {
        const form = document.getElementById('filterForm');
        form.querySelector('input[name="sort"]').value = column;
        form.querySelector('input[name="direction"]').value = direction;
        form.submit();
    }

    // Checkbox Selection & Bulk Actions
    document.addEventListener('DOMContentLoaded', function () {
        const checkAll = document.getElementById('checkAllQuestioners');
        const checkboxes = document.querySelectorAll('.questioner-checkbox');
        const bulkBar = document.getElementById('bulkActionBar');
        const countBadge = document.getElementById('bulkSelectedCount');
        const exportIdsInput = document.getElementById('bulkExportIds');

        function updateSelectionState() {
            const checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);
            const count = checkedBoxes.length;

            countBadge.textContent = count;
            exportIdsInput.value = checkedBoxes.map(cb => cb.value).join(',');

            // Highlight selected rows
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

            // Master checkbox visual indeterminate state
            if (count === 0) {
                checkAll.checked = false;
                checkAll.indeterminate = false;
                bulkBar.classList.remove('show');
            } else if (count === checkboxes.length) {
                checkAll.checked = true;
                checkAll.indeterminate = false;
                bulkBar.classList.add('show');
            } else {
                checkAll.checked = false;
                checkAll.indeterminate = true;
                bulkBar.classList.add('show');
            }
        }

        if (checkAll) {
            checkAll.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = checkAll.checked);
                updateSelectionState();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateSelectionState);
        });

        window.clearSelection = function () {
            checkboxes.forEach(cb => cb.checked = false);
            if (checkAll) {
                checkAll.checked = false;
                checkAll.indeterminate = false;
            }
            updateSelectionState();
        };
    });
</script>
@endpush

@endsection
