@extends('layouts.admin')
@section('title', 'Featured Fatwa Rules')
@section('topbar-title', 'Smart Featured Fatwa Rules')
@section('topbar-icon', 'bi-stars')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <span class="text-dark fw-semibold">Featured Fatwas</span>
@endsection

@section('content')

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h5 class="fw-bold mb-1 text-dark">Smart Rule Automation</h5>
        <p class="text-muted small mb-0">Automate curated fatwas on the portal homepage based on Hijri calendar dates & geographic signals.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <form method="GET" action="{{ route('admin.featured-rules.index') }}" id="featuredRulesFilterForm" class="d-flex align-items-center">
            <div class="position-relative" style="min-width: 260px;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" id="featuredSearchIcon" style="font-size: 0.85rem;"></i>
                <input type="text" 
                       name="search" 
                       id="featuredSearchInput"
                       class="form-control modern-input ps-5 pe-5 py-1.5" 
                       style="font-size: 0.84rem;"
                       placeholder="Search rules, categories, titles..." 
                       value="{{ request('search') }}"
                       autocomplete="off">
                
                {{-- Live Spinner Indicator --}}
                <div class="spinner-border text-primary spinner-border-sm position-absolute top-50 translate-middle-y" id="featuredSearchSpinner" style="right: 34px; display: none; width: 14px; height: 14px;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>

                {{-- Clear Button --}}
                <button type="button" class="btn btn-sm text-muted position-absolute top-50 end-0 translate-middle-y me-2 p-0 d-flex align-items-center justify-content-center" id="featuredClearBtn" style="display: none; width: 18px; height: 18px; border-radius: 50%; background: #E2E8F0;" title="Clear Search">
                    <i class="bi bi-x" style="font-size: 0.75rem;"></i>
                </button>
            </div>
        </form>

        <a href="{{ route('admin.featured-rules.create') }}" class="btn btn-modern-primary text-nowrap">
            <i class="bi bi-plus-lg"></i> Create New Rule
        </a>
    </div>
</div>

<div class="modern-card overflow-hidden" id="featuredRulesTableContainer">
    <div class="p-3.5 px-4 bg-white border-bottom d-flex justify-content-between align-items-center">
        <span class="small text-muted fw-semibold"><i class="bi bi-info-circle me-1.5 text-primary"></i>Rules are evaluated in strict priority order (highest first).</span>
    </div>
    <div class="table-responsive">
        <table class="modern-table align-middle">
            <thead>
                <tr>
                    <th class="ps-4" style="width: 50px;">#</th>
                    <th>Rule Identifier</th>
                    <th>Evaluation Criteria</th>
                    <th>Homepage Section Title</th>
                    <th class="text-center" style="width: 90px;">Limit</th>
                    <th class="text-center" style="width: 100px;">Priority</th>
                    <th class="text-center" style="width: 130px;">Status</th>
                    <th class="text-end pe-4" style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                <tr>
                    <td class="ps-4 text-muted small fw-semibold">{{ $rule->id }}</td>
                    <td>
                        <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $rule->name }}</div>
                        <div class="d-flex align-items-center gap-1.5 mt-1">
                            @if($rule->category)
                                <span class="badge" style="background: #EFF6FF; color: #2563EB; font-size: 0.72rem; padding: 2px 7px;">
                                    <i class="bi bi-folder2 me-1"></i>{{ $rule->category->name }}
                                </span>
                            @endif
                            @if($rule->madhab)
                                <span class="badge" style="background: #FFFBEB; color: #D97706; font-size: 0.72rem; padding: 2px 7px;">
                                    <i class="bi bi-bookmark me-1"></i>{{ $rule->madhab->name }}
                                </span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="text-muted small">{{ $rule->conditionSummary() }}</div>
                    </td>
                    <td>
                        <span class="fw-semibold text-dark small">{{ Str::limit($rule->section_label, 35) }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 fw-bold">{{ $rule->limit }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge" style="background: #EFF6FF; color: #1D4ED8; font-weight: 700; border-radius: 6px; padding: 4px 8px;">
                            {{ $rule->priority }}
                        </span>
                    </td>
                    <td class="text-center">
                        <form action="{{ route('admin.featured-rules.toggle', $rule) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1"
                                style="font-size: 0.75rem; {{ $rule->is_active ? 'background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0;' : 'background: #F1F5F9; color: #64748B; border: 1px solid #CBD5E1;' }}">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $rule->is_active ? '#10B981' : '#94A3B8' }};"></span>
                                {{ $rule->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-inline-flex align-items-center gap-1.5">
                            <a href="{{ route('admin.featured-rules.edit', $rule) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.78rem;">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </a>
                            <form action="{{ route('admin.featured-rules.destroy', $rule) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this featured rule?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger p-1 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; border-radius: 6px; border-color: #FECDD3; background: #FFF1F2;" title="Delete">
                                    <i class="bi bi-trash3" style="font-size: 0.78rem;"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-stars fs-1 text-slate-300 d-block mb-2"></i>
                        <h6 class="fw-bold text-dark mb-1">No Featured Rules Configured</h6>
                        <p class="small text-muted mb-0">Create rule logic to automatically highlight relevant fatwas.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modern-card p-4 mt-4" style="background: #F8FAFC;">
    <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
        <i class="bi bi-lightbulb text-warning fs-5"></i> Architecture of Smart Rules
    </h6>
    <div class="row g-3 small text-muted">
        <div class="col-md-4">
            <div class="p-3 bg-white border rounded-3 h-100">
                <div class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <i class="bi bi-calendar3 text-primary"></i> Hijri Calendar Dates
                </div>
                <div>Match questions with Islamic holy months (e.g. Ramadan, Dhul Hijjah) with optional date ranges.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-white border rounded-3 h-100">
                <div class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <i class="bi bi-geo-alt text-success"></i> Geographic Origin
                </div>
                <div>Highlight regional fatwas relevant to the visitor's country (e.g. India, UAE, UK, Malaysia).</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-white border rounded-3 h-100">
                <div class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <i class="bi bi-diagram-3 text-info"></i> Composite Logic
                </div>
                <div>Combine date, category, madhab, and geography for targeted contextual home feeds.</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin-live-search.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new AdminLiveSearch({
            form: '#featuredRulesFilterForm',
            input: '#featuredSearchInput',
            clearBtn: '#featuredClearBtn',
            spinner: '#featuredSearchSpinner',
            searchIcon: '#featuredSearchIcon',
            container: '#featuredRulesTableContainer',
            debounceMs: 350
        });
    });
</script>
@endpush

@endsection
