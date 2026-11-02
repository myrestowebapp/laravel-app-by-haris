@extends('layouts.admin')
@section('title', $user->name . ' — Questioner Detail')
@section('topbar-title', 'Questioner: ' . $user->name)
@section('topbar-icon', 'bi-person-lines-fill')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <a href="{{ route('admin.questioners.index') }}">Questioners</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <span class="text-dark fw-semibold">{{ $user->name }}</span>
@endsection
@section('content')

{{-- Profile Card --}}
<div class="row g-4 mb-4">
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm text-center p-4">
            <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=1a7a4a&color=fff&size=120' }}"
                 class="rounded-circle mx-auto mb-3 border border-3 border-success"
                 width="90" height="90">
            <h6 class="fw-bold mb-1">{{ $user->name }}</h6>
            <small class="text-muted d-block">{{ $user->email }}</small>
            @if($user->mobile)
            <small class="text-dark fw-semibold d-block mt-1"><i class="bi bi-telephone-fill text-primary me-1" style="font-size: 0.72rem;"></i>{{ $user->mobile }}</small>
            @endif
            @if($user->country)
            <small class="text-muted d-block mt-1"><i class="bi bi-geo-alt me-1"></i>{{ $user->country }}</small>
            @endif
            <small class="text-muted d-block mt-1"><i class="bi bi-calendar me-1"></i>Joined {{ $user->created_at->format('M Y') }}</small>
            <small class="text-muted d-block mt-1"><i class="bi bi-translate me-1"></i>{{ $user->mother_language ?? 'Not set' }}</small>

            @if(($stats['total'] ?? 0) === 0)
            <form method="POST" action="{{ route('admin.questioners.destroy', $user) }}" onsubmit="return confirm('Are you sure you want to permanently delete this questioner profile? This user has no submitted fatwas.');" class="mt-3">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger w-100 rounded-pill py-1.5 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5">
                    <i class="bi bi-trash3"></i> Delete Profile (No Fatwas)
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="col-lg-9">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center p-3 h-100 border-top border-4 border-secondary">
                    <div style="font-size:1.8rem; color:#6c757d;"><i class="bi bi-collection"></i></div>
                    <h3 class="fw-bold mt-1 mb-0">{{ $stats['total'] }}</h3>
                    <small class="text-muted fw-semibold">Total Questions</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center p-3 h-100 border-top border-4 border-warning">
                    <div style="font-size:1.8rem; color:#c9a84c;"><i class="bi bi-hourglass-split"></i></div>
                    <h3 class="fw-bold mt-1 mb-0">{{ $stats['pending'] }}</h3>
                    <small class="text-muted fw-semibold">In Progress</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center p-3 h-100 border-top border-4 border-success">
                    <div style="font-size:1.8rem; color:#1a7a4a;"><i class="bi bi-patch-check"></i></div>
                    <h3 class="fw-bold mt-1 mb-0">{{ $stats['published'] }}</h3>
                    <small class="text-muted fw-semibold">Published</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center p-3 h-100 border-top border-4 border-info">
                    <div style="font-size:1.8rem; color:#0dcaf0;"><i class="bi bi-eye"></i></div>
                    <h3 class="fw-bold mt-1 mb-0">{{ number_format($stats['total_reads']) }}</h3>
                    <small class="text-muted fw-semibold">Total Public Reads</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Questions Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-2"></i>All Questions</h6>
        <a href="{{ route('admin.questioners.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to List
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">QU-ID</th>
                        <th>Question Excerpt</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Published</th>
                        <th class="text-center" title="Days from submission to publishing">Days to Publish</th>
                        <th class="text-center" title="Times the questioner personally opened this">User Opens</th>
                        <th class="text-center" title="Total public reads on this fatwa">Public Reads</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($questions as $row)
                    @php $q = $row['question']; @endphp
                    <tr>
                        <td class="ps-4">
                            <span class="badge bg-light text-dark border fw-monospace small">{{ $q->question_id }}</span>
                        </td>
                        <td style="max-width: 260px;">
                            <div class="text-truncate fw-semibold small">
                                {{ Str::limit(strip_tags($q->title ?: $q->body), 70) }}
                            </div>
                            <small class="text-muted">{{ $q->created_at->format('d M Y') }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-status-{{ $q->status }}">{{ ucfirst($q->status) }}</span>
                        </td>
                        <td class="text-center">
                            <small>{{ $row['fatwa']?->published_at?->format('d M Y') ?? '—' }}</small>
                        </td>
                        <td class="text-center">
                            @if($row['days_to_publish'] !== null)
                                <span class="badge {{ $row['days_to_publish'] <= 3 ? 'bg-success' : ($row['days_to_publish'] <= 7 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                    {{ $row['days_to_publish'] }} days
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['user_views'] > 0)
                                <span class="badge bg-primary rounded-pill">{{ $row['user_views'] }}×</span>
                            @else
                                <span class="text-muted small">nil</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['public_reads'] > 0)
                                <span class="badge bg-info text-dark rounded-pill">{{ number_format($row['public_reads']) }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['fatwa'])
                                <a href="{{ route('fatwa.show', $row['fatwa']) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            @else
                                <a href="{{ route('admin.questions.show', $q) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-5">No questions found for this user.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Device & Access Tracking --}}
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white border-0 py-3">
        <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-device-ssd me-2"></i>Device & Access Tracking</h6>
        <p class="small text-muted mb-0">Analytics for user visits, specifically to the "Ask Question" page.</p>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small text-uppercase">
                        <th class="ps-4">Access Time</th>
                        <th>Page</th>
                        <th class="text-center">Device</th>
                        <th class="text-center">OS</th>
                        <th class="text-center">Browser</th>
                        <th class="text-center">IP Address</th>
                        <th class="ps-4">Location</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visitorLogs as $log)
                    <tr>
                        <td class="ps-4">
                            <small class="fw-semibold d-block">{{ $log->created_at->format('d M Y') }}</small>
                            <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                        </td>
                        <td>
                            @if(str_contains($log->page, 'ask-question'))
                                <span class="badge bg-warning text-dark px-2">Ask Question</span>
                            @else
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 150px;">
                                    /{{ collect(explode('/', $log->page))->last() }}
                                </small>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $deviceIcon = 'bi-display';
                                if($log->device_type === 'Mobile') $deviceIcon = 'bi-phone';
                                elseif($log->device_type === 'Tab') $deviceIcon = 'bi-tablet';
                            @endphp
                            <span class="badge bg-light text-dark border py-2 px-3">
                                <i class="bi {{ $deviceIcon }} me-2 text-primary"></i>{{ $log->device_type ?? 'Web' }}
                            </span>
                        </td>
                        <td class="text-center">
                            @php
                                $osIcon = 'bi-cpu';
                                if($log->operating_system === 'Windows') $osIcon = 'bi-windows';
                                elseif($log->operating_system === 'Android') $osIcon = 'bi-android';
                                elseif($log->operating_system === 'iOS') $osIcon = 'bi-apple';
                                elseif($log->operating_system === 'Linux' || $log->operating_system === 'Ubuntu') $osIcon = 'bi-ubuntu';
                            @endphp
                            <small class="fw-bold"><i class="bi {{ $osIcon }} me-1"></i>{{ $log->operating_system ?? 'Other' }}</small>
                        </td>
                        <td class="text-center">
                            <small class="text-muted">{{ $log->browser ?? 'Other' }}</small>
                        </td>
                        <td class="text-center">
                            <small class="font-monospace text-muted">{{ $log->ip_address }}</small>
                        </td>
                        <td class="ps-4">
                            <small class="d-block fw-semibold">{{ $log->city ?? 'Unknown' }}, {{ $log->country_code ?? '??' }}</small>
                            <small class="text-muted small">{{ $log->country ?? 'Unknown' }}</small>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-5 small italic">No access logs available for this session.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
