@extends('layouts.admin')

@section('title', 'Academic Impact — ' . $user->name)
@section('topbar-title', 'Scholar Performance Insights')
@section('topbar-icon', 'bi-mortarboard-fill')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <a href="{{ route('admin.muftis.index') }}">Muftis</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <span class="text-dark fw-semibold">{{ $user->name }}</span>
@endsection

@section('content')
<div class="activity-wrapper pb-5">
    {{-- Profile Header Card --}}
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-lg profile-hero-card">
                <div class="card-body p-0 position-relative">
                    <div class="profile-mesh-bg"></div>
                    <div class="d-md-flex align-items-center p-4 p-lg-5 position-relative z-index-1">
                        <div class="profile-avatar-wrapper me-md-5 mb-4 mb-md-0">
                            <div class="avatar-ring"></div>
                            <img src="{{ $user->avatar ? asset($user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=1a7a4a&color=fff&size=120' }}" 
                                 class="rounded-circle shadow-lg main-avatar" width="120" height="120">
                            @php
                                $isLive = $user->isOnline(5);
                            @endphp
                            <div class="status-pulse {{ $isLive ? 'bg-success shadow-success' : 'bg-secondary opacity-50' }}" title="{{ $isLive ? 'Currently Online in Mufti Dashboard' : 'Offline' }}"></div>
                        </div>
                        <div class="profile-info-text text-white">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h2 class="fw-black mb-0 display-6">{{ $user->name }}</h2>
                                @if($isLive)
                                    <span class="badge bg-success bg-opacity-75 text-white border border-white border-opacity-25 rounded-pill px-3 py-1 small fw-bold">
                                        <i class="bi bi-broadcast me-1"></i>LIVE ONLINE
                                    </span>
                                @else
                                    <span class="badge bg-dark bg-opacity-50 text-white border border-white border-opacity-25 rounded-pill px-3 py-1 small fw-normal">
                                        {{ $user->last_seen_at ? 'Last seen ' . $user->last_seen_at->diffForHumans() : 'Offline' }}
                                    </span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center opacity-75 mb-3">
                                <i class="bi bi-geo-alt-fill me-2"></i>
                                <span class="fw-medium">{{ $user->country ?? 'Global Scholar' }}</span>
                                <span class="mx-2">|</span>
                                <span class="fw-medium font-inter">{{ $user->email }}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="glass-pill gold-border" style="background: rgba(255, 215, 0, 0.18); border: 1px solid rgba(255, 215, 0, 0.5);">
                                    <i class="bi bi-award-fill text-warning me-1"></i>Ifta Work Score: <strong class="text-white font-inter">{{ $work_score }}%</strong>
                                    <span class="badge rounded-pill ms-1" style="background: {{ $score_color }}; font-size: 0.68rem;">{{ $score_tier }}</span>
                                </span>
                                <span class="glass-pill"><i class="bi bi-box-arrow-in-right me-1 text-warning"></i>{{ number_format($user->login_count ?? 0) }} Total Logins</span>
                                <span class="glass-pill"><i class="bi bi-clock-history me-1"></i>Last Login: {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</span>
                                <span class="glass-pill"><i class="bi bi-book me-1"></i>{{ $user->madhab?->name ? $user->madhab->name . ' Madhab' : 'Islamic Jurisprudence' }}</span>
                                @if($user->can_check_approve)
                                    <span class="glass-pill gold-border"><i class="bi bi-shield-check me-1"></i>Senior Checker</span>
                                @endif
                            </div>
                        </div>
                        <div class="ms-auto mt-4 mt-md-0 d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.muftis.history', $user) }}" class="btn btn-warning text-dark fw-bold rounded-pill px-4 py-2 shadow-sm">
                                <i class="bi bi-clock-history me-2"></i>Work History
                            </a>
                            <a href="{{ route('admin.muftis.edit', $user) }}" class="btn glass-btn rounded-pill px-4 py-2">
                                <i class="bi bi-pencil-square me-2"></i>Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Main Activity Column --}}
        <div class="col-lg-8">
            {{-- Prominent Ifta Work Score Hero Banner --}}
            <div class="card border-0 shadow-sm mb-4 ifta-score-hero-card" style="border-radius: 20px; background: #FFFFFF;">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-4">
                        <div class="d-flex align-items-center gap-3.5">
                            {{-- Circular Progress Ring Gauge --}}
                            <div class="position-relative flex-shrink-0" style="width: 90px; height: 90px;">
                                <svg class="w-100 h-100" viewBox="0 0 36 36" style="transform: rotate(-90deg);">
                                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#E2E8F0" stroke-width="3.5"></path>
                                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="{{ $score_color }}" stroke-dasharray="{{ $work_score }}, 100" stroke-linecap="round" stroke-width="3.5"></path>
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle text-center">
                                    <span class="fw-black text-dark font-inter" style="font-size: 1.35rem; line-height: 1;">{{ $work_score }}%</span>
                                </div>
                            </div>

                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h5 class="fw-black text-dark mb-0 font-inter">Ifta Work Score</h5>
                                    <span class="badge rounded-pill fw-bold" style="background: {{ $score_color }}; color: #fff; font-size: 0.72rem; padding: 4px 10px;">
                                        <i class="bi bi-stars me-1"></i>{{ $score_tier }}
                                    </span>
                                </div>
                                <p class="text-muted small mb-0" style="max-width: 440px; font-size: 0.8rem; line-height: 1.5;">
                                    Comprehensive scholarly benchmark evaluating fatwa publication volume, answered inquiries, turnaround speed, and platform engagement.
                                </p>
                            </div>
                        </div>

                        {{-- Quick 3-Metric Score Breakdown --}}
                        <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap">
                            <div class="px-3 py-2 bg-light rounded-3 text-center border" style="min-width: 85px;">
                                <div class="text-muted small" style="font-size: 0.68rem; text-transform: uppercase; font-weight: 700;">Published</div>
                                <span class="fw-bold font-inter fs-6 text-dark">{{ $stats['published'] }}</span>
                                <span class="d-block text-muted" style="font-size: 0.65rem;">{{ $scoreData['pub_points'] ?? 0 }}/35 pts</span>
                            </div>
                            <div class="px-3 py-2 bg-light rounded-3 text-center border" style="min-width: 85px;">
                                <div class="text-muted small" style="font-size: 0.68rem; text-transform: uppercase; font-weight: 700;">Answered</div>
                                <span class="fw-bold font-inter fs-6 text-dark">{{ $stats['answered'] }}</span>
                                <span class="d-block text-muted" style="font-size: 0.65rem;">{{ $scoreData['ans_points'] ?? 0 }}/30 pts</span>
                            </div>
                            <div class="px-3 py-2 bg-light rounded-3 text-center border" style="min-width: 85px;">
                                <div class="text-muted small" style="font-size: 0.68rem; text-transform: uppercase; font-weight: 700;">Avg Speed</div>
                                <span class="fw-bold font-inter fs-6 text-dark">{{ round($avg_days, 1) }}d</span>
                                <span class="d-block text-muted" style="font-size: 0.65rem;">Turnaround</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Question Metrics: Workflow Redesign --}}
            <div class="card border-0 shadow-sm mb-4 metric-card-main">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <div>
                            <h4 class="fw-black text-dark mb-1">Impact Workflow</h4>
                            <p class="text-muted small mb-0">Visualizing the scholarly contribution pipeline</p>
                        </div>
                        <div class="workflow-badge">
                            <i class="bi bi-lightning-fill me-1 text-warning"></i>Live Pipeline
                        </div>
                    </div>

                    <div class="workflow-container">
                        <div class="workflow-item" data-label="Assigned">
                            <div class="workflow-icon bg-soft-primary">
                                <i class="bi bi-journal-plus"></i>
                            </div>
                            <div class="workflow-stats">
                                <span class="workflow-num text-primary">{{ $stats['assigned'] }}</span>
                                <span class="workflow-text">Incoming</span>
                            </div>
                            <div class="workflow-connector"></div>
                        </div>

                        <div class="workflow-item" data-label="Answered">
                            <div class="workflow-icon bg-soft-info">
                                <i class="bi bi-chat-right-text"></i>
                            </div>
                            <div class="workflow-stats">
                                <span class="workflow-num text-info">{{ $stats['answered'] }}</span>
                                <span class="workflow-text">Answered</span>
                            </div>
                            <div class="workflow-connector"></div>
                        </div>

                        <div class="workflow-item" data-label="Published">
                            <div class="workflow-icon bg-soft-success">
                                <i class="bi bi-globe-central-south-asia"></i>
                            </div>
                            <div class="workflow-stats">
                                <span class="workflow-num text-success">{{ $stats['published'] }}</span>
                                <span class="workflow-text">Published</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 p-4 rounded-4 bg-light border border-dashed border-primary border-opacity-25 d-flex align-items-center">
                        <div class="me-3">
                            <div class="icon-circle bg-white shadow-sm">
                                <i class="bi bi-box-seam text-secondary"></i>
                            </div>
                        </div>
                        <div>
                            <span class="text-muted small d-block">System-wide Unassigned Inquiries</span>
                            <span class="fw-bold fs-5 text-dark font-inter">{{ $stats['unassigned'] }} <span class="text-muted fw-normal small">available in pool</span></span>
                        </div>
                        <div class="ms-auto">
                            <div class="pulse-ring bg-secondary opacity-25"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                {{-- Topics Covered --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100 topic-card overflow-hidden">
                        <div class="card-body p-4 position-relative">
                            <div class="topic-bg-icon">
                                <i class="bi bi-tags"></i>
                            </div>
                            <h6 class="fw-bold text-uppercase tracking-widest text-muted small mb-4">Breadth of Knowledge</h6>
                            <div class="d-flex align-items-end mb-3">
                                <h1 class="fw-black mb-0 display-4 text-dark font-inter">{{ $topics_count }}</h1>
                                <span class="h4 mb-2 ms-2 text-warning opacity-75">/ Categories</span>
                            </div>
                            <p class="text-muted small lh-lg">Unique subject areas and jurisprudential categories covered through scholarly responses.</p>
                            <div class="category-pills-mock mt-3 d-flex flex-wrap gap-1 opacity-50">
                                <span class="badge rounded-pill bg-light text-dark border">Nikah</span>
                                <span class="badge rounded-pill bg-light text-dark border">Zakat</span>
                                <span class="badge rounded-pill bg-light text-dark border">Talaq</span>
                                <span class="badge rounded-pill bg-light text-dark border">++</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Appreciation/Likes --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100 engagement-card glass-effect overflow-hidden">
                        <div class="card-body p-4 position-relative">
                            <div class="heart-animation position-absolute top-0 end-0 p-3 opacity-25">
                                <i class="bi bi-heart-fill fs-1 text-danger"></i>
                            </div>
                            <h6 class="fw-bold text-uppercase tracking-widest text-muted small mb-4 text-danger">Community Impact</h6>
                            <h1 class="fw-black mb-0 display-4 text-dark font-inter">{{ number_format($likes) }}</h1>
                            <p class="text-muted small mt-2">Total user endorsements & likes received on published fatwas.</p>
                            <div class="mt-4 pt-3 border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small fw-semibold text-muted">Impact Velocity</span>
                                    <span class="badge bg-danger rounded-pill px-3">+High</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Public Reads --}}
            <div class="card border-0 shadow-lg impact-gradient-card overflow-hidden">
                <div class="card-body p-4 p-md-5 text-white position-relative">
                    <div class="impact-mesh"></div>
                    <div class="row align-items-center position-relative z-index-1">
                        <div class="col-md-7">
                            <h6 class="text-uppercase tracking-widest fw-bold opacity-75 mb-3">Global Scholarly Reach</h6>
                            <h1 class="fw-black display-3 mb-2 font-inter">{{ number_format($views) }}</h1>
                            <p class="mb-0 opacity-75 lead">Total digital consultation interactions facilitated across the Darul Ifta platform.</p>
                        </div>
                        <div class="col-md-5 text-center mt-4 mt-md-0">
                            <div class="reach-icon-box">
                                <i class="bi bi-globe2 display-1 text-info opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar Stats Column --}}
        <div class="col-lg-4">
            {{-- Active Track --}}
            <div class="card border-0 shadow-sm mb-4 overflow-hidden active-track-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold text-uppercase tracking-widest text-muted small mb-0">Assigned Workload</h6>
                        <div class="live-indicator">
                            <span class="pulse-dot"></span>
                            ACTIVE
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <h1 class="display-3 fw-black mb-0 text-primary font-inter">{{ $active_track }}</h1>
                        <p class="ms-3 text-muted small mb-0 mt-2">Currently assigned pending inquiries</p>
                    </div>
                    <div class="mt-4 pt-3">
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-muted">Concurrency Load</span>
                            <span class="fw-bold">{{ $active_track > 5 ? 'High' : 'Optimal' }}</span>
                        </div>
                        <div class="progress rounded-pill bg-light" style="height: 10px;">
                            <div class="progress-bar bg-gradient-primary rounded-pill h-100" role="progressbar" style="width: {{ min(100, $active_track * 15) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ifta Work Score --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; background: #FFFFFF;">
                <div class="card-body p-4">
                    <div class="text-center mb-4 mt-1">
                        <div class="badge-premium mb-2" style="background: rgba(255, 193, 7, 0.1); border-color: rgba(255, 193, 7, 0.25); color: #856404;">
                            <i class="bi bi-stars me-1 text-warning"></i>Performance Benchmark
                        </div>
                        <h6 class="fw-bold text-dark text-uppercase tracking-widest small mb-1">Ifta Work Score</h6>
                        <span class="badge rounded-pill fw-bold" style="background: {{ $score_color }}; color: #fff; font-size: 0.72rem; padding: 4px 12px;">
                            {{ $score_tier }}
                        </span>
                    </div>
                    
                    <div class="score-display-radial mx-auto mb-4" style="background: conic-gradient({{ $score_color }} {{ min(100, $work_score) }}%, #F1F5F9 0);">
                        <div class="score-outer-ring">
                            <div class="score-inner-content">
                                <span class="score-number fw-black text-dark display-4 font-inter">{{ $work_score }}%</span>
                                <span class="score-label fw-bold" style="color: {{ $score_color }}; letter-spacing: 1px;">INDEX SCORE</span>
                            </div>
                        </div>
                    </div>

                    <div class="stats-grid mt-4">
                        <div class="stat-item p-3 rounded-4 bg-light mb-3 border">
                            <div class="d-flex align-items-center mb-2">
                                <div class="stat-icon bg-white shadow-sm rounded-3 me-3">
                                    <i class="bi bi-clock-history text-success"></i>
                                </div>
                                <div>
                                    <span class="text-muted small d-block">Submission Speed</span>
                                    <span class="fw-bold text-dark font-inter">{{ round($avg_days, 1) }} <span class="small fw-normal">Days Avg</span></span>
                                </div>
                            </div>
                            <div class="progress" style="height: 5px; border-radius: 10px;">
                                <div class="progress-bar bg-success rounded-pill" style="width: {{ max(10, min(100, 100 - ($avg_days * 5))) }}%"></div>
                            </div>
                        </div>

                        <div class="stat-item p-3 rounded-4 bg-light border">
                            <div class="d-flex align-items-center mb-2">
                                <div class="stat-icon bg-white shadow-sm rounded-3 me-3">
                                    <i class="bi bi-box-arrow-up text-primary"></i>
                                </div>
                                <div>
                                    <span class="text-muted small d-block">Answer Volume</span>
                                    <span class="fw-bold text-dark font-inter">{{ $stats['answered'] }} <span class="small fw-normal">Answers ({{ $stats['published'] }} Published)</span></span>
                                </div>
                            </div>
                            <div class="progress" style="height: 5px; border-radius: 10px;">
                                <div class="progress-bar bg-primary rounded-pill" style="width: {{ min(100, $stats['answered'] * 5) }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 rounded-4 bg-light border text-center">
                        <p class="text-muted small mb-0" style="font-size: 0.78rem;">
                            Composite index of fatwa publication, answer velocity, workload resolution, and platform engagement.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --brand-accent: #1a7a4a;
        --soft-primary: rgba(26, 122, 74, 0.1);
        --soft-info: rgba(13, 202, 240, 0.1);
        --soft-success: rgba(40, 167, 69, 0.1);
        --glass-bg: rgba(255, 255, 255, 0.85);
    }

    .fw-black { font-weight: 900; }
    .font-inter { font-family: 'Inter', sans-serif; }
    .tracking-widest { letter-spacing: 0.15em; }
    .z-index-1 { z-index: 1; }

    /* Profile Card */
    .profile-hero-card {
        border-radius: 25px;
        background: #1a1a1a;
        overflow: hidden;
    }
    .profile-mesh-bg {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: radial-gradient(at 0% 0%, hsla(153, 58%, 35%, 1) 0, transparent 50%),
                    radial-gradient(at 100% 100%, hsla(200, 80%, 40%, 1) 0, transparent 50%),
                    radial-gradient(at 100% 0%, hsla(140, 50%, 45%, 1) 0, transparent 50%);
        opacity: 0.8;
    }
    .main-avatar { border: 4px solid rgba(255, 255, 255, 0.2); }
    .avatar-ring {
        position: absolute;
        top: -10px; left: -10px; right: -10px; bottom: -10px;
        border: 1px dashed rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        animation: rotate 20s linear infinite;
    }
    @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .status-pulse {
        position: absolute;
        bottom: 5px; right: 15px;
        width: 15px; height: 15px;
        border-radius: 50%;
        border: 2px solid #fff;
        animation: pulse 2s infinite;
    }
    @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); } 100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); } }

    .glass-pill {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(5px);
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .gold-border { border-color: rgba(255, 193, 7, 0.4); }
    .glass-btn {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: 0.3s;
    }
    .glass-btn:hover { background: rgba(255, 255, 255, 0.25); color: #fff; transform: translateY(-2px); }

    /* Workflow Styles */
    .metric-card-main { border-radius: 20px; }
    .workflow-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 0;
        position: relative;
    }
    .workflow-item {
        flex: 1;
        display: flex;
        flex-column;
        align-items: center;
        text-align: center;
        position: relative;
    }
    .workflow-icon {
        width: 65px; height: 65px;
        border-radius: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
        transition: 0.3s;
        box-shadow: 0 10px 20px rgba(0,0,0,0.02);
    }
    .workflow-item:hover .workflow-icon { transform: translateY(-5px); }
    .workflow-stats { display: flex; flex-direction: column; }
    .workflow-num { font-size: 1.75rem; font-weight: 900; line-height: 1; margin-bottom: 5px; font-family: 'Inter'; }
    .workflow-text { font-size: 0.75rem; color: #8898aa; font-weight: 700; text-uppercase; letter-spacing: 0.5px; }
    .workflow-connector {
        position: absolute;
        top: 32px; right: -50%;
        width: 100%; height: 2px;
        background: linear-gradient(to right, transparent, #e9ecef 50%, transparent);
        z-index: 0;
    }
    .bg-soft-primary { background: rgba(13, 110, 253, 0.08); }
    .bg-soft-info { background: rgba(13, 202, 240, 0.08); }
    .bg-soft-success { background: rgba(40, 167, 69, 0.08); }

    .workflow-badge {
        padding: 5px 12px;
        background: rgba(255, 193, 7, 0.08);
        color: #856404;
        border-radius: 50px;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        border: 1px solid rgba(255, 193, 7, 0.2);
    }

    /* Cards */
    .topic-card, .engagement-card { border-radius: 20px; transition: 0.3s; }
    .topic-card:hover, .engagement-card:hover { transform: translateY(-5px); }
    .topic-bg-icon {
        position: absolute;
        bottom: -20px; right: -20px;
        font-size: 8rem;
        opacity: 0.03;
        transform: rotate(-15deg);
    }
    .glass-effect {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .impact-gradient-card {
        border-radius: 25px;
        background: #121212;
    }
    .impact-mesh {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: radial-gradient(at 100% 0%, rgba(32, 201, 151, 0.3) 0, transparent 70%),
                    radial-gradient(at 0% 100%, rgba(13, 202, 240, 0.2) 0, transparent 70%);
    }

    /* Sidebar Score */
    .score-display-radial {
        width: 190px; height: 190px;
        padding: 10px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    }
    .score-outer-ring {
        width: 100%; height: 100%;
        background: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: inset 0 0 15px rgba(0,0,0,0.05);
    }
    .score-inner-content { text-align: center; }
    .score-number { line-height: 1; display: block; }
    .score-label { font-size: 0.65rem; letter-spacing: 2px; }

    .badge-premium {
        display: inline-block;
        padding: 4px 15px;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 50px;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #495057;
    }

    .stat-icon {
        width: 40px; height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .live-indicator {
        font-size: 0.65rem;
        font-weight: 800;
        color: var(--brand-accent);
        display: flex;
        align-items: center;
        background: var(--soft-primary);
        padding: 4px 10px;
        border-radius: 50px;
    }
    .pulse-dot {
        width: 6px; height: 6px;
        background: var(--brand-accent);
        border-radius: 50%;
        margin-right: 6px;
        animation: active-pulse 1.5s infinite;
    }
    @keyframes active-pulse { 0% { opacity: 1; } 50% { opacity: 0.3; } 100% { opacity: 1; } }

    .bg-gradient-primary {
        background: linear-gradient(to right, var(--brand-accent), #3ce48c);
    }

    @media (max-width: 767.98px) {
        .profile-avatar-wrapper {
            margin: 0 auto 1.25rem !important;
            text-align: center;
            display: flex;
            justify-content: center;
        }
        .profile-info-text {
            text-align: center;
        }
        .profile-info-text .d-flex {
            justify-content: center;
        }
        .profile-hero-card .ms-auto {
            margin: 1.25rem 0 0 !important;
            justify-content: center;
            width: 100%;
        }
        .profile-hero-card .ms-auto .btn {
            flex: 1;
            text-align: center;
        }
    }
</style>
@endsection
