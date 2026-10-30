@extends('layouts.admin')
@section('title', 'Mufti Management')
@section('topbar-title', 'Mufti User Profiles')
@section('topbar-icon', 'bi-person-badge-fill')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <span class="text-dark fw-semibold">Muftis</span>
@endsection

@section('content')

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1 fs-4 text-dark">Scholar Faculty &amp; Dashboard Signals</h4>
        <p class="text-muted small mb-0">Monitor live activity, login counts, and scholarly workload</p>
    </div>
    <div class="d-flex align-items-center gap-2 w-100 w-sm-auto">
        <a href="{{ route('admin.muftis.create') }}" class="btn btn-modern-primary w-100 w-sm-auto">
            <i class="bi bi-person-plus-fill"></i> Add New Mufti
        </a>
    </div>
</div>

{{-- Top Faculty Pulse Metrics --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-4">
        <div class="p-3 bg-white rounded-4 border shadow-xs d-flex align-items-center justify-content-between h-100">
            <div>
                <div class="text-muted text-uppercase x-small fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Registered Scholars</div>
                <h3 class="fw-800 text-dark mb-0 mt-1">{{ number_format($totalMuftis ?? $muftis->total()) }}</h3>
            </div>
            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="p-3 bg-white rounded-4 border shadow-xs d-flex align-items-center justify-content-between h-100" style="border-left: 4px solid #10b981 !important;">
            <div>
                <div class="text-success text-uppercase x-small fw-bold d-flex align-items-center gap-1" style="font-size: 10px; letter-spacing: 0.5px;">
                    <span class="live-pulse-dot" style="width: 7px; height: 7px;"></span> Live Online Now
                </div>
                <h3 class="fw-800 text-success mb-0 mt-1">{{ number_format($onlineCount ?? 0) }} <span class="text-muted fw-normal fs-6">Scholars</span></h3>
            </div>
            <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="bi bi-broadcast"></i>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="p-3 bg-white rounded-4 border shadow-xs d-flex align-items-center justify-content-between h-100">
            <div>
                <div class="text-muted text-uppercase x-small fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Faculty Total Logins</div>
                <h3 class="fw-800 text-dark mb-0 mt-1">{{ number_format($totalLogins ?? 0) }}</h3>
            </div>
            <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="bi bi-box-arrow-in-right"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 g-sm-3 g-md-4">
    @forelse($muftis as $mufti)
    @php
        $isMuftiOnline = $mufti->isOnline(5);
        $lastSeenFormatted = $mufti->last_seen_at ? $mufti->last_seen_at->diffForHumans() : 'Never active';
        $lastLoginFormatted = $mufti->last_login_at ? $mufti->last_login_at->diffForHumans() : 'Never';
        $isKingScholar = $loop->first && $muftis->currentPage() === 1 && ($mufti->published_count ?? 0) > 0;
    @endphp
    <div class="col-6 col-md-4 col-lg-3">
        <div class="scholar-full-card {{ $isKingScholar ? 'scholar-king-card' : '' }}">
            {{-- Background/Main Image --}}
            <div class="scholar-image-container">
                <img src="{{ $mufti->avatar ? asset($mufti->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($mufti->name).'&background=f1f5f9&color=1a7a4a&size=512' }}" 
                     alt="{{ $mufti->name }}"
                     class="scholar-main-img">
                
                {{-- Quick Actions Overlay --}}
                <div class="scholar-overlay-actions">
                    <div class="dropdown">
                        <button class="btn-glass-action" data-bs-toggle="dropdown" aria-label="Scholar Options">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2">
                            <li><a class="dropdown-item rounded-2" href="{{ route('admin.muftis.history', $mufti) }}"><i class="bi bi-clock-history me-2 text-primary"></i>View Work History</a></li>
                            <li><a class="dropdown-item rounded-2" href="{{ route('admin.muftis.activity', $mufti) }}"><i class="bi bi-speedometer2 me-2"></i>Performance Stats</a></li>
                            <li><a class="dropdown-item rounded-2" href="{{ route('admin.muftis.edit', $mufti) }}"><i class="bi bi-pencil-fill me-2"></i>Edit Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('admin.muftis.toggle-status', $mufti) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="dropdown-item rounded-2 {{ $mufti->is_active ? 'text-danger' : 'text-success' }}">
                                        <i class="bi bi-power me-2"></i>{{ $mufti->is_active ? 'Deactivate Account' : 'Activate Account' }} 
                                    </button>
                                </form>
                            </li>
                            @if(!$mufti->assignedQuestions()->exists())
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('admin.muftis.destroy', $mufti) }}" onsubmit="return confirm('Delete Scholar profile?');">
                                    @csrf @method('DELETE')
                                    <button class="dropdown-item rounded-2 text-danger"><i class="bi bi-trash-fill me-2"></i>Delete Scholar</button>
                                </form>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Top Contributing Scholar (King Status) --}}
            @if($isKingScholar)
                <div class="scholar-king-tag" title="Leading Scholar — Most Published Fatwas ({{ $mufti->published_count }})">
                    <i class="bi bi-crown-fill me-1"></i> TOP SCHOLAR
                </div>
            @endif

            {{-- Live Online Status Badge (Top-Left) --}}
            <div class="scholar-live-tag {{ $isKingScholar ? 'under-king-tag' : '' }} {{ $isMuftiOnline ? 'tag-online' : 'tag-offline' }}" title="{{ $isMuftiOnline ? 'Currently Live on Mufti Dashboard' : 'Last seen: ' . $lastSeenFormatted }}">
                @if($isMuftiOnline)
                    <span class="live-pulse-dot"></span>
                    <span class="tag-label">ONLINE</span>
                @else
                    <span class="offline-dot"></span>
                    <span class="tag-label">{{ $mufti->last_seen_at ? $mufti->last_seen_at->diffForHumans(null, true) : 'OFFLINE' }}</span>
                @endif
            </div>

            {{-- Info Content --}}
            <div class="scholar-info-overlay">
                <div class="info-content">
                    <div class="d-flex align-items-center gap-1.5 mb-0.5">
                        <h5 class="scholar-name mb-0" title="{{ $mufti->name }}">{{ $mufti->name }}</h5>
                        <i class="bi bi-patch-check-fill verified-badge-icon flex-shrink-0"></i>
                    </div>
                    <p class="scholar-description text-muted small mb-1.5 text-truncate">
                        Scholar at Darul Ifta • {{ $mufti->country ?? 'India' }}
                    </p>

                    <div class="scholar-footer">
                        <div class="mini-metrics">
                            <div class="metric-pill" title="Dashboard Total Logins">
                                <i class="bi bi-box-arrow-in-right text-warning"></i> {{ number_format($mufti->login_count ?? 0) }}
                            </div>
                            <div class="metric-pill" title="Assigned Questions">
                                <i class="bi bi-people"></i> {{ number_format($mufti->total_assigned ?? 0) }}
                            </div>
                            <div class="metric-pill" title="Published Answers">
                                <i class="bi bi-journal-check text-success"></i> {{ number_format($mufti->published_count ?? 0) }}
                            </div>
                        </div>
                        <div class="scholar-action-buttons">
                            <a href="{{ route('admin.muftis.history', $mufti) }}" class="btn btn-xs btn-primary rounded-pill px-2 py-1 fw-bold text-white d-inline-flex align-items-center justify-content-center gap-1 shadow-xs" title="Work History & Live Logs">
                                <i class="bi bi-clock-history"></i> <span>History</span>
                            </a>
                            <a href="{{ route('admin.muftis.activity', $mufti) }}" class="btn-scholar-view shadow-sm d-inline-flex align-items-center justify-content-center" title="View Full Performance & Login Logs">
                                <span>Stats</span> <i class="bi bi-chevron-right ms-0.5"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Account Status Dot --}}
            <div class="status-indicator-dot {{ $mufti->is_active ? 'active' : 'inactive' }}" title="Account Status: {{ $mufti->is_active ? 'Active' : 'Disabled' }}"></div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5 mt-4">
        <div class="mb-3"><i class="bi bi-people text-muted" style="font-size:3rem; opacity:.2;"></i></div>
        <h5 class="text-muted">No Scholar Profiles Registered</h5>
        <p class="text-muted small">Scholar accounts will appear here once they are added to the system.</p>
    </div>
    @endforelse
</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $muftis->links() }}
</div>

@push('styles')
<style>
    /* Responsive Grid for XL 5 columns */
    @media (min-width: 1400px) {
        .col-xl-5th {
            flex: 0 0 20%;
            max-width: 20%;
        }
    }

    .scholar-full-card {
        aspect-ratio: 95 / 138;
        min-height: 290px;
        background: #111827;
        border-radius: 1.5rem;
        position: relative;
        overflow: hidden;
        border: 2px solid #fff;
        box-shadow: 0 6px 20px rgba(0,0,0,0.06);
        transition: transform 0.35s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.35s ease;
        display: flex;
        flex-direction: column;
    }

    @media (max-width: 575.98px) {
        .scholar-full-card {
            aspect-ratio: 95 / 142;
            min-height: 240px;
            border-radius: 1.15rem;
            border-width: 1.5px;
        }
    }

    .scholar-full-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 16px 36px rgba(0,0,0,0.12);
    }

    .scholar-image-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        background: #1e293b;
    }
    .scholar-main-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center top;
        transition: transform 0.5s ease;
    }
    .scholar-full-card:hover .scholar-main-img {
        transform: scale(1.04);
    }

    .scholar-overlay-actions {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        z-index: 5;
    }
    @media (max-width: 575.98px) {
        .scholar-overlay-actions {
            top: 0.5rem;
            right: 0.5rem;
        }
    }
    .btn-glass-action {
        width: 32px;
        height: 32px;
        background: rgba(255,255,255,0.9);
        backdrop-filter: blur(10px);
        border: none;
        border-radius: 0.65rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1a202c;
        box-shadow: 0 3px 10px rgba(0,0,0,0.12);
        transition: all 0.2s;
        font-size: 0.85rem;
    }
    @media (max-width: 575.98px) {
        .btn-glass-action {
            width: 26px;
            height: 26px;
            border-radius: 0.5rem;
            font-size: 0.75rem;
        }
    }
    .btn-glass-action:hover {
        background: #fff;
        transform: scale(1.06);
    }

    /* ── Top Contributing Scholar (King Status) ── */
    .scholar-king-card {
        border: 2.5px solid #F59E0B !important;
        box-shadow: 0 8px 24px rgba(245, 158, 11, 0.35) !important;
    }
    .scholar-king-tag {
        position: absolute;
        top: 0.75rem;
        left: 0.75rem;
        z-index: 6;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.4px;
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        color: #FFFFFF;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.45);
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    .scholar-live-tag.under-king-tag {
        top: 2.35rem !important;
    }
    @media (max-width: 575.98px) {
        .scholar-king-tag {
            top: 0.5rem;
            left: 0.5rem;
            padding: 2.5px 7px;
            font-size: 0.55rem;
        }
        .scholar-live-tag.under-king-tag {
            top: 1.85rem !important;
        }
    }

    /* ── Live Online Tag ── */
    .scholar-live-tag {
        position: absolute;
        top: 0.75rem;
        left: 0.75rem;
        z-index: 5;
        padding: 4px 9px;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.4px;
        display: flex;
        align-items: center;
        gap: 4px;
        backdrop-filter: blur(8px);
        box-shadow: 0 3px 10px rgba(0,0,0,0.18);
        max-width: calc(100% - 3.5rem);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    @media (max-width: 575.98px) {
        .scholar-live-tag {
            top: 0.5rem;
            left: 0.5rem;
            padding: 2.5px 6px;
            font-size: 0.55rem;
            letter-spacing: 0.2px;
            border-radius: 12px;
        }
    }
    .scholar-live-tag.tag-online {
        background: rgba(16, 185, 129, 0.95);
        color: #ffffff;
        border: 1px solid rgba(255,255,255,0.4);
    }
    .scholar-live-tag.tag-offline {
        background: rgba(15, 23, 42, 0.8);
        color: rgba(255,255,255,0.9);
        border: 1px solid rgba(255,255,255,0.15);
    }

    .live-pulse-dot {
        display: inline-block;
        width: 7px;
        height: 7px;
        background: #ffffff;
        border-radius: 50%;
        box-shadow: 0 0 0 rgba(255, 255, 255, 0.6);
        animation: pulse-live 1.8s infinite;
        flex-shrink: 0;
    }
    @media (max-width: 575.98px) {
        .live-pulse-dot {
            width: 5px;
            height: 5px;
        }
    }
    @keyframes pulse-live {
        0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.8); }
        70% { box-shadow: 0 0 0 5px rgba(255, 255, 255, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
    }

    .offline-dot {
        display: inline-block;
        width: 5px;
        height: 5px;
        background: rgba(255,255,255,0.5);
        border-radius: 50%;
        flex-shrink: 0;
    }

    .scholar-info-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        padding: 2rem 0.85rem 0.85rem;
        background: linear-gradient(to top, 
            rgba(0,0,0,0.98) 0%, 
            rgba(0,0,0,0.90) 50%, 
            rgba(0,0,0,0.45) 80%, 
            rgba(0,0,0,0) 100%);
        z-index: 3;
    }
    
    @media (max-width: 575.98px) {
        .scholar-info-overlay {
            padding: 1.5rem 0.55rem 0.55rem;
        }
    }

    .scholar-name {
        font-weight: 800;
        font-size: 0.98rem;
        color: #ffffff;
        letter-spacing: -0.3px;
        line-height: 1.2;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    @media (max-width: 575.98px) {
        .scholar-name {
            font-size: 0.8rem;
        }
    }
    .verified-badge-icon {
        color: #10b981;
        font-size: 0.95rem;
        filter: drop-shadow(0 0 4px rgba(16, 185, 129, 0.4));
    }
    @media (max-width: 575.98px) {
        .verified-badge-icon {
            font-size: 0.75rem;
        }
    }
    .scholar-description {
        font-weight: 600;
        color: rgba(255,255,255,0.75) !important;
        margin-top: 1px;
        font-size: 0.68rem;
    }
    @media (max-width: 575.98px) {
        .scholar-description {
            font-size: 0.58rem;
        }
    }

    .scholar-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 0.5rem;
        gap: 4px;
        flex-wrap: wrap;
    }
    @media (max-width: 575.98px) {
        .scholar-footer {
            flex-direction: column;
            align-items: stretch;
            margin-top: 0.35rem;
            gap: 4px;
        }
    }

    .mini-metrics {
        display: flex;
        gap: 0.25rem;
        align-items: center;
        flex-wrap: nowrap;
    }
    @media (max-width: 575.98px) {
        .mini-metrics {
            justify-content: space-between;
            gap: 2px;
        }
    }
    .metric-pill {
        font-size: 0.65rem;
        font-weight: 800;
        background: rgba(255,255,255,0.18);
        backdrop-filter: blur(6px);
        color: #ffffff;
        padding: 0.25rem 0.4rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        gap: 2px;
        border: 1px solid rgba(255,255,255,0.15);
        white-space: nowrap;
    }
    @media (max-width: 575.98px) {
        .metric-pill {
            font-size: 0.55rem;
            padding: 1.5px 3px;
            border-radius: 0.35rem;
        }
    }
    
    .scholar-action-buttons {
        display: flex;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
    }
    @media (max-width: 575.98px) {
        .scholar-action-buttons {
            width: 100%;
            gap: 3px;
        }
        .scholar-action-buttons .btn,
        .scholar-action-buttons .btn-scholar-view {
            flex: 1;
            padding: 2px 4px;
            font-size: 0.6rem;
            border-radius: 50px;
            text-align: center;
        }
    }

    .btn-scholar-view {
        background: #ffffff;
        border-radius: 100px;
        font-size: 0.7rem;
        font-weight: 800;
        color: #1a202c;
        text-decoration: none;
        padding: 0.3rem 0.65rem;
        border: 1px solid rgba(0,0,0,0.08);
        transition: all 0.25s ease;
        white-space: nowrap;
    }
    .btn-scholar-view:hover {
        background: #1a202c;
        color: #fff;
        border-color: #1a202c;
    }

    .status-indicator-dot {
        display: none;
    }
</style>
@endpush

@endsection
