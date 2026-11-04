@extends('layouts.admin')
@section('title', 'Admin Dashboard')
@section('topbar-title', 'Administrative Command Center')
@section('topbar-icon', 'bi-speedometer2')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <span class="text-dark fw-semibold">Command Center</span>
@endsection

@section('content')

{{-- Real-time Sentinel Bar with Sound Notification Toggle & User Logs Link --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-1 gap-2">
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-white text-dark border px-3 py-1.5 rounded-pill shadow-2xs d-inline-flex align-items-center gap-1.5" style="font-size: 0.76rem;">
            <span class="live-pulse-dot" style="width: 6px; height: 6px;"></span>
            <span>Real-time Sentinel Active</span>
        </span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-sm btn-white border rounded-pill px-3 py-1.5 fw-semibold shadow-2xs d-inline-flex align-items-center gap-1.5" id="btn-sound-toggle" onclick="toggleDashboardSound()" title="Toggle Real-Time Sound Notification">
            <i class="bi bi-volume-up-fill text-primary" id="sound-icon"></i>
            <span id="sound-label" style="font-size: 0.78rem;">Sound: ON</span>
        </button>

        <a href="{{ route('admin.user-logs.index') }}" class="btn btn-sm btn-white border rounded-pill px-3 py-1.5 fw-semibold shadow-2xs d-inline-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
            <i class="bi bi-person-lines-fill text-primary"></i>
            <span>User Behavior Logs</span>
        </a>
    </div>
</div>

{{-- 1. Top Modern Metric Cards (8 Core Signals) with Generous Spacing & Padding --}}
<div class="row g-3 mb-4 pb-1">
    @php
        $metricConfig = [
            ['Total Questions', $stats['total'], 'bi-journals', '#EFF6FF', '#2563EB', '+12% ↑', '#ECFDF5', '#059669'],
            ['Critical Pending', $stats['pending'], 'bi-clock-history', '#FFFBEB', '#D97706', ($stats['pending'] > 0 ? 'Requires Action' : 'Cleared'), '#FFFBEB', '#D97706'],
            ['Assigned Tasks', $stats['assigned'], 'bi-person-check', '#EFF6FF', '#0284C7', 'In Scholar Queue', '#F0F9FF', '#0284C7'],
            ['Completed Answers', $stats['answered'], 'bi-chat-check-fill', '#FAF5FF', '#9333EA', 'Awaiting Verification', '#FAF5FF', '#9333EA'],
            ['Published Fatwas', $stats['published'], 'bi-journal-check', '#ECFDF5', '#059669', 'Public Knowledge', '#ECFDF5', '#059669'],
            ['Knowledge Views', $stats['total_reads'], 'bi-eye', '#FDF2F8', '#DB2777', 'Total Impressions', '#FDF2F8', '#DB2777'],
            ['Active Users', $stats['questioners'], 'bi-people', '#F0FDF4', '#16A34A', 'Registered Inquirers', '#F0FDF4', '#16A34A'],
            ['Verified Signals', $stats['live_by_identity'], 'bi-fingerprint', '#F8FAFC', '#475569', 'Live Telemetry', '#F8FAFC', '#475569'],
        ];
    @endphp

    @foreach($metricConfig as [$label, $val, $icon, $iconBg, $iconColor, $badgeTxt, $badgeBg, $badgeColor])
    <div class="col-6 col-md-3 col-xl">
        <div class="metric-card-tile d-flex align-items-center justify-content-between h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-square flex-shrink-0" style="background: {{ $iconBg }}; color: {{ $iconColor }};">
                    <i class="bi {{ $icon }}"></i>
                </div>
                <div>
                    <h3 class="metric-val-num">{{ number_format($val) }}</h3>
                    <div class="metric-label-txt text-truncate" style="max-width: 110px;">{{ $label }}</div>
                </div>
            </div>
            <span class="metric-trend-pill d-none d-sm-inline-flex" style="background: {{ $badgeBg }}; color: {{ $badgeColor }}; font-size: 0.68rem;">
                {{ $badgeTxt }}
            </span>
        </div>
    </div>
    @endforeach
</div>

{{-- 2. Main Analytics Row: Growth Chart & Side Controls --}}
<div class="row g-4 mb-4 pb-2">
    {{-- Left Column: Growth Chart & Live Scholar Dashboard --}}
    <div class="col-lg-8 d-flex flex-column gap-4">
        
        {{-- Card A: Knowledge Growth & Fatwa Progression Chart (Styled exactly per reference image with generous padding) --}}
        <div class="modern-card overflow-hidden mb-0">
            <div class="p-4 px-4 px-md-4.5 border-bottom d-flex flex-wrap justify-content-between align-items-center bg-white gap-3">
                <div>
                    <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2.5">
                        <i class="bi bi-graph-up text-primary fs-5"></i> Knowledge Growth &amp; Fatwa Progression
                    </h5>
                    <div class="text-muted small mt-1" style="font-size: 0.8rem;">Lifelong institutional trajectory across all inquiries</div>
                </div>
                
                <div class="d-flex align-items-center gap-2.5">
                    <span class="badge d-none d-sm-inline-flex align-items-center gap-1" style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0; font-size: 11px; padding: 6px 13px; border-radius: 20px; font-weight: 600;">
                        <span style="font-size: 13px; line-height: 1;">&infin;</span> Lifelong ({{ $lifelongSpan }})
                    </span>

                    <div class="btn-group btn-group-sm p-1 bg-light rounded-pill border" role="group" style="border-color: #E2E8F0 !important;">
                        <button type="button" class="btn btn-sm rounded-pill fw-bold chart-toggle-btn active shadow-xs" id="btnViewYearly" onclick="switchChartView('yearly')">
                            Yearly
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill fw-bold chart-toggle-btn text-muted" id="btnViewMonthly" onclick="switchChartView('monthly')">
                            All Months
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill fw-bold chart-toggle-btn text-muted" id="btnViewCumulative" onclick="switchChartView('cumulative')">
                            Cumulative
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-4 px-4 px-md-4.5 pb-4 pb-md-4.5 bg-white">
                {{-- Chart Canvas Container with spacious height for comfortable breathing room --}}
                <div class="chart-canvas-wrapper position-relative w-100 mb-3">
                    <canvas id="lifelongChart"></canvas>
                </div>

                {{-- Chart Metric Ribbon with generous spacing and dedicated styled stat boxes --}}
                <div class="row g-3 pt-4 border-top mt-4">
                    <div class="col-sm-3 col-6">
                        <div class="chart-summary-stat-box text-center">
                            <div class="chart-summary-stat-label">TOTAL INQUIRIES</div>
                            <div class="fw-bold text-dark fs-4 mt-1">{{ number_format($stats['total']) }}</div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6">
                        <div class="chart-summary-stat-box text-center">
                            <div class="chart-summary-stat-label">PUBLISHED FATWAS</div>
                            <div class="fw-bold fs-4 mt-1" style="color: #2D5A43;">{{ number_format($stats['published']) }}</div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6">
                        <div class="chart-summary-stat-box text-center">
                            <div class="chart-summary-stat-label">LIFELONG SPAN</div>
                            <div class="fw-bold text-primary fs-4 mt-1">{{ $lifelongSpan }}</div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6">
                        <div class="chart-summary-stat-box text-center">
                            <div class="chart-summary-stat-label">PEAK INQUIRIES</div>
                            <div class="fw-bold fs-4 mt-1" style="color: #CCA43B;">{{ $peakYearLabel }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card B: Live Scholar Dashboard (Modern Rectangular Cards with generous padding) --}}
        @php
            $liveMuftis = collect($facultyMuftis ?? [])->filter(fn($m) => $m->isOnline(5));
            $liveQuestionersList = collect($liveQuestioners ?? []);
            $totalLiveCountVal = ($onlineMuftisCount ?? 0) + ($onlineQuestionersCount ?? 0) + ($onlineVisitorsCount ?? 0);
        @endphp
        <div class="modern-card overflow-hidden mb-0">
            <div class="p-4 px-4 px-md-4.5 border-bottom d-flex flex-wrap justify-content-between align-items-center bg-white gap-3">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="d-inline-flex p-2 rounded-3" style="background: #ECFDF5; color: #059669;">
                        <i class="bi bi-broadcast fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Live User Presence &amp; Live Scholar Dashboard</h6>
                        <div class="text-muted small mt-0.5" style="font-size: 0.74rem;">Real-time active faculty on duty, logged-in questioners &amp; website visitors across all domain pages</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge" id="live-scholars-count-badge" style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0; font-size: 0.74rem; padding: 6px 14px; border-radius: 20px;">
                        <span class="live-pulse-dot me-1"></span> <span id="live-scholars-count-num">{{ $totalLiveCountVal }}</span> Total Online
                    </span>
                    <a href="{{ route('admin.muftis.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 text-xs fw-semibold">
                        Faculty List <i class="bi bi-chevron-right ms-0.5" style="font-size: 0.65rem;"></i>
                    </a>
                </div>
            </div>

            {{-- Segmented User Role Filter Tabs --}}
            <div class="px-4 px-md-4.5 pt-3 pb-2 bg-light bg-opacity-40 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="btn-group btn-group-sm p-1 rounded-pill bg-white border shadow-2xs" role="tablist" id="live-role-filter-group">
                    <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold live-filter-btn active" data-filter="all" style="font-size: 0.75rem;">
                        All Live Users <span class="badge rounded-pill bg-primary ms-1" id="filter-count-all">{{ $totalLiveCountVal }}</span>
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold live-filter-btn text-muted" data-filter="mufti" style="font-size: 0.75rem;">
                        <i class="bi bi-person-badge me-1"></i> Scholars / Muftis <span class="badge rounded-pill bg-emerald-100 text-emerald-800 ms-1" id="filter-count-mufti">{{ $onlineMuftisCount ?? 0 }}</span>
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold live-filter-btn text-muted" data-filter="questioner" style="font-size: 0.75rem;">
                        <i class="bi bi-chat-left-text me-1"></i> Questioners <span class="badge rounded-pill bg-sky-100 text-sky-800 ms-1" id="filter-count-questioner">{{ $onlineQuestionersCount ?? 0 }}</span>
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold live-filter-btn text-muted" data-filter="visitor" style="font-size: 0.75rem;">
                        <i class="bi bi-globe2 me-1"></i> Visitors <span class="badge rounded-pill bg-slate-100 text-slate-700 ms-1" id="filter-count-visitor">{{ $onlineVisitorsCount ?? 0 }}</span>
                    </button>
                </div>
                <div class="text-muted small d-none d-md-flex align-items-center gap-1.5" style="font-size: 0.72rem;">
                    <i class="bi bi-arrow-repeat text-success"></i> Auto-synced every 5s domain-wide
                </div>
            </div>

            <div class="p-4 px-4 px-md-4.5 bg-white" id="live-scholars-grid-container">
                <div class="row g-3" id="live-scholars-grid">
                    @forelse($liveMuftis as $lMufti)
                    <div class="col-12 col-md-6 col-xl-4 live-user-card-col" data-role="mufti">
                        <div class="scholar-live-card p-3.5 rounded-3 border bg-white h-100 d-flex flex-column justify-content-between shadow-2xs">
                            <div class="d-flex align-items-center gap-3 mb-2.5">
                                <div class="position-relative flex-shrink-0">
                                    <img src="{{ $lMufti->avatar ? asset($lMufti->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($lMufti->name).'&background=102B43&color=fff&size=60' }}" 
                                         alt="{{ $lMufti->name }}" class="rounded-circle border" width="46" height="46" style="object-fit: cover;">
                                    <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle" style="width: 11px; height: 11px;" title="Online now"></span>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <a href="{{ route('admin.muftis.history', $lMufti) }}" class="fw-bold text-dark text-decoration-none text-truncate d-block scholar-name-link" style="font-size: 0.88rem;" title="{{ $lMufti->name }}">
                                            {{ $lMufti->name }}
                                        </a>
                                        <span class="badge rounded-pill px-2 py-0.5 text-2xs fw-bold" style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0;">
                                            Mufti
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0.5 d-inline-flex align-items-center gap-1" style="font-size: 0.68rem; font-weight: 700;">
                                            <span class="live-pulse-dot" style="width: 5px; height: 5px;"></span> LIVE
                                        </span>
                                        <span class="badge bg-light text-muted border rounded-pill px-2.5 py-0.5 dash-live-timer font-monospace" data-start-time="{{ ($lMufti->last_login_at ?? now())->timestamp * 1000 }}" style="font-size: 0.72rem;">
                                            00:00:00
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-2.5 border-top mt-auto d-flex align-items-center justify-content-between text-muted" style="font-size: 0.74rem;">
                                <span class="d-flex align-items-center gap-1">
                                    <i class="bi bi-inbox text-primary"></i>
                                    <span>Queue: <strong class="text-dark">{{ $lMufti->pending_count ?? 0 }}</strong> pending</span>
                                </span>
                                <span>
                                    <i class="bi bi-clock-history me-1"></i>{{ $lMufti->last_seen_at ? $lMufti->last_seen_at->diffForHumans(null, true) : 'Active' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    @endforelse

                    @forelse($liveQuestionersList as $lQ)
                    <div class="col-12 col-md-6 col-xl-4 live-user-card-col" data-role="questioner">
                        <div class="scholar-live-card p-3.5 rounded-3 border bg-white h-100 d-flex flex-column justify-content-between shadow-2xs">
                            <div class="d-flex align-items-center gap-3 mb-2.5">
                                <div class="position-relative flex-shrink-0">
                                    <img src="{{ $lQ->avatar ? asset($lQ->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($lQ->name).'&background=0284C7&color=fff&size=60' }}" 
                                         alt="{{ $lQ->name }}" class="rounded-circle border" width="46" height="46" style="object-fit: cover;">
                                    <span class="position-absolute bottom-0 end-0 p-1 bg-sky-500 border border-white rounded-circle" style="width: 11px; height: 11px;" title="Online now"></span>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <a href="{{ route('admin.questioners.show', $lQ->id) }}" class="fw-bold text-dark text-decoration-none text-truncate d-block scholar-name-link" style="font-size: 0.88rem;" title="{{ $lQ->name }}">
                                            {{ $lQ->name }}
                                        </a>
                                        <span class="badge rounded-pill px-2 py-0.5 text-2xs fw-bold" style="background: #F0F9FF; color: #0284C7; border: 1px solid #BAE6FD;">
                                            Questioner
                                        </span>
                                    </div>
                                    <div class="text-muted text-truncate mt-0.5" style="font-size: 0.72rem;">
                                        {{ $lQ->email }}
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                            <i class="bi bi-geo-alt me-0.5"></i>{{ $lQ->country ?? 'Global' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-2.5 border-top mt-auto d-flex align-items-center justify-content-between text-muted" style="font-size: 0.74rem;">
                                <span class="d-flex align-items-center gap-1">
                                    <i class="bi bi-question-circle text-info"></i>
                                    <span>Questions: <strong class="text-dark">{{ $lQ->total_questions ?? 0 }}</strong></span>
                                </span>
                                <span>
                                    <i class="bi bi-clock-history me-1"></i>{{ $lQ->last_seen_at ? $lQ->last_seen_at->diffForHumans(null, true) : 'Active' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    @endforelse

                    @if($totalLiveCountVal === 0)
                    <div class="col-12" id="live-scholars-empty-state">
                        <div class="text-center py-5 px-3 bg-light bg-opacity-50 rounded-3 border border-dashed">
                            <div class="d-inline-flex p-3 rounded-circle bg-white border shadow-xs text-muted mb-2.5">
                                <i class="bi bi-people fs-3 text-slate-400"></i>
                            </div>
                            <div class="fw-semibold text-dark">No live users or scholars currently active.</div>
                            <div class="text-muted small mt-1" style="font-size: 0.76rem;">
                                Active scholars, questioners, and website visitors across any page will automatically appear here in real-time.
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- Right Column: Live Visitors & System Quick Actions --}}
    <div class="col-lg-4 d-flex flex-column gap-4">
        {{-- Live Visitors Telemetry Card --}}
        <div class="modern-card p-4 px-4 px-md-4.5 position-relative overflow-hidden mb-0">
            <div class="d-flex justify-content-between align-items-center mb-3.5">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge" style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0; font-size: 0.74rem; font-weight: 700; border-radius: 20px; padding: 5px 12px;">
                        <span class="live-pulse-dot me-1.5" style="width: 7px; height: 7px;"></span> Live Visitors Telemetry
                    </span>
                    <span class="text-muted small fw-semibold">Global Traffic</span>
                </div>
                <div class="d-inline-flex p-2 rounded-3" style="background: #EFF6FF; color: #2563EB;">
                    <i class="bi bi-broadcast fs-5"></i>
                </div>
            </div>
            
            <div class="d-flex align-items-baseline gap-2.5 mb-1.5">
                <h2 class="fw-bold mb-0 text-dark" id="live-users-count" style="font-size: 2.5rem; letter-spacing: -0.5px;">
                    {{ number_format($stats['live_by_ip']) }}
                </h2>
                <span class="text-muted small fw-medium">Active visitor {{ $stats['live_by_ip'] == 1 ? 'connection' : 'connections' }}</span>
            </div>
            <p class="text-muted small mb-0 mt-1" style="font-size: 0.78rem;">
                <i class="bi bi-shield-check text-success me-1"></i> Real-time unique client IP signals &amp; session telemetry
            </p>
        </div>

        {{-- Fatwa Dossier PDF Export Card --}}
        <div class="modern-card p-4 px-4 px-md-4.5 mb-0">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="d-inline-flex p-2 rounded-3" style="background: #FEF2F2; color: #DC2626;">
                        <i class="bi bi-file-earmark-pdf-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Fatwa Dossier Export</h6>
                        <span class="text-muted" style="font-size: 0.74rem;">Printable A4 format with Google Noto</span>
                    </div>
                </div>
                <span class="badge" style="background: #EFF6FF; color: #2563EB; font-size: 10px; font-weight: 700; border-radius: 20px; padding: 5px 10px;">
                    Multi-lingual
                </span>
            </div>
            
            <p class="text-muted small mb-3.5" style="font-size: 0.8rem; line-height: 1.55;">
                Compile inquiries into high-resolution printable dossiers with automatic Arabic, Malayalam, and Urdu typography.
            </p>
            
            <div class="d-grid gap-2.5">
                <a href="{{ route('admin.fatwas.export.pdf', ['status' => 'pending,accepted']) }}" target="_blank" class="btn btn-modern-primary justify-content-between py-2.5 px-3">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-cloud-arrow-down fs-6"></i>
                        <span>Download Pending &amp; Accepted</span>
                    </span>
                    <i class="bi bi-box-arrow-up-right small"></i>
                </a>

                <button type="button" class="btn btn-modern-secondary justify-content-center py-2.5 px-3" data-bs-toggle="modal" data-bs-target="#exportPdfModal">
                    <i class="bi bi-sliders me-1.5"></i> Custom PDF Export Filters
                </button>
            </div>
        </div>

        {{-- Quick Maintenance Actions (System Shortcuts) --}}
        <div class="modern-card p-4 px-4 px-md-4.5 mb-0">
            <div class="d-flex align-items-center justify-content-between mb-3.5 pb-2.5 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-inline-flex p-1.5 rounded-2" style="background: #FFFBEB; color: #D97706;">
                        <i class="bi bi-lightning-charge-fill" style="font-size: 0.9rem;"></i>
                    </div>
                    <h6 class="fw-bold mb-0 text-dark small text-uppercase" style="letter-spacing: 0.6px;">System Shortcuts</h6>
                </div>
                <span class="badge" style="background: #F1F5F9; color: #64748B; font-size: 10px; font-weight: 700;">Utilities</span>
            </div>
            
            <div class="d-grid gap-2.5">
                <a href="{{ route('admin.maintenance.clear-cache') }}" class="modern-shortcut-tile d-flex align-items-center justify-content-between p-3 rounded-3 border text-decoration-none bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-inline-flex p-2 rounded-2" style="background: #FFFBEB; color: #D97706;">
                            <i class="bi bi-stars"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark small">Flush Cache</div>
                            <div class="text-muted" style="font-size: 0.74rem;">Purge application view &amp; route cache</div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted small"></i>
                </a>

                <a href="{{ route('admin.settings.system') }}" class="modern-shortcut-tile d-flex align-items-center justify-content-between p-3 rounded-3 border text-decoration-none bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-inline-flex p-2 rounded-2" style="background: #EFF6FF; color: #2563EB;">
                            <i class="bi bi-gear-wide-connected"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark small">System Configuration</div>
                            <div class="text-muted" style="font-size: 0.74rem;">Manage portal &amp; calendar parameters</div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted small"></i>
                </a>

                <a href="/system-health" target="_blank" class="modern-shortcut-tile d-flex align-items-center justify-content-between p-3 rounded-3 border text-decoration-none bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-inline-flex p-2 rounded-2" style="background: #ECFDF5; color: #059669;">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark small">Diagnostics Portal</div>
                            <div class="text-muted" style="font-size: 0.74rem;">Server, DB &amp; API health telemetry</div>
                        </div>
                    </div>
                    <i class="bi bi-box-arrow-up-right text-muted small"></i>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- 3. Feeds Row: Tabbed Inquiries Stream & Geographic Signals --}}
<div class="row g-4 mb-4 pb-2">
    {{-- Left Feeds: Stream of Recent Inquiries & Published Fatwas Tabbed Card --}}
    <div class="col-xl-9">
        <div class="modern-card overflow-hidden mb-0">
            <div class="p-4 px-4 px-md-4.5 border-bottom d-flex flex-wrap justify-content-between align-items-center bg-white gap-3">
                <div class="d-flex align-items-center gap-3">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-stack text-primary"></i> Stream of Inquiries
                    </h6>
                    <ul class="nav nav-pills modern-feed-tabs" id="inquiriesFeedTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active px-3.5 py-1.5 rounded-pill small fw-semibold" id="recent-inquiries-tab" data-bs-toggle="pill" data-bs-target="#tab-recent-inquiries" type="button" role="tab" style="font-size: 0.8rem;">
                                Recent Inquiries <span class="badge bg-primary bg-opacity-10 text-primary ms-1">{{ $recentQuestions->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link px-3.5 py-1.5 rounded-pill small fw-semibold text-muted" id="recent-published-tab" data-bs-toggle="pill" data-bs-target="#tab-recent-published" type="button" role="tab" style="font-size: 0.8rem;">
                                Published Fatwas <span class="badge bg-success bg-opacity-10 text-success ms-1">{{ $recentPublishedFatwas->count() }}</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div>
                    <a href="{{ route('admin.questions.index') }}" class="btn btn-sm btn-modern-secondary px-3 py-1.5" id="feedViewAllBtn">
                        View All Inquiries <i class="bi bi-chevron-right ms-0.5"></i>
                    </a>
                </div>
            </div>

            <div class="tab-content" id="inquiriesFeedTabsContent">
                {{-- Tab 1: Recent Inquiries Stream Table --}}
                <div class="tab-pane fade show active" id="tab-recent-inquiries" role="tabpanel">
                    <div class="table-responsive">
                        <table class="modern-table align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-4 px-3 py-3">Reference</th>
                                    <th class="px-3 py-3">Subject</th>
                                    <th class="px-3 py-3">Assigned Scholar</th>
                                    <th class="px-3 py-3">Status</th>
                                    <th class="text-end pe-4 px-3 py-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentQuestions as $q)
                                @php
                                    $textSample = ($q->title ?? '') . ' ' . strip_tags($q->body ?? '');
                                    $isMalayalam = (bool) preg_match('/[\x{0D00}-\x{0D7F}]/u', $textSample) || strtolower($q->language ?? '') === 'malayalam';
                                    $isArabic = (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $textSample) || in_array(strtolower($q->language ?? ''), ['arabic', 'ar']);
                                    $isUrdu = strtolower($q->language ?? '') === 'urdu';
                                    $itemFontClass = $isMalayalam ? 'font-malayalam' : ($isUrdu ? 'font-urdu' : ($isArabic ? 'font-arabic' : ''));
                                @endphp
                                <tr>
                                    <td class="ps-4 py-3.5">
                                        <span class="badge" style="background: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE; font-weight: 700; border-radius: 6px; padding: 5px 9px; font-size: 0.76rem;">
                                            {{ $q->question_id }}
                                        </span>
                                    </td>
                                    <td class="py-3.5">
                                        <div class="fw-bold text-dark {{ $itemFontClass }}" dir="auto" style="font-size: 0.88rem;">
                                            {{ Str::limit(strip_tags($q->title ?: $q->body), 55) }}
                                        </div>
                                        <div class="text-muted small mt-0.5" style="font-size: 0.74rem;">
                                            {{ $q->madhab?->name ?? 'Common Ground' }} · {{ $q->category?->name ?? 'General' }}
                                        </div>
                                    </td>
                                    <td class="py-3.5">
                                        @if($q->status !== 'pending' && $q->status !== 'rejected' && $q->assigned_mufti_id)
                                            <div class="d-flex align-items-center gap-1.5">
                                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 fw-semibold small">
                                                    {{ $q->assignedMufti?->name ?? 'Scholar' }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-muted small italic">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5">
                                        <span class="badge rounded-pill px-2.5 py-1 text-xs fw-semibold" style="{{ $q->status === 'published' ? 'background: #ECFDF5; color: #059669;' : ($q->status === 'pending' ? 'background: #FFFBEB; color: #D97706;' : 'background: #EFF6FF; color: #2563EB;') }}">
                                            {{ ucfirst(str_replace('_', ' ', $q->status)) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4 py-3.5">
                                        <a href="{{ route('admin.questions.show', $q) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem;">
                                            Manage <i class="bi bi-chevron-right ms-0.5"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">No recent inquiries found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tab 2: Recently Published Fatwas Stream Table --}}
                <div class="tab-pane fade" id="tab-recent-published" role="tabpanel">
                    <div class="table-responsive">
                        <table class="modern-table align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-4 px-3 py-3">Reference</th>
                                    <th class="px-3 py-3">Subject</th>
                                    <th class="px-3 py-3">Category / Madhab</th>
                                    <th class="text-center px-3 py-3">Views</th>
                                    <th class="text-end pe-4 px-3 py-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPublishedFatwas as $fatwa)
                                @php
                                    $textSamplePub = ($fatwa->title ?? '') . ' ' . strip_tags($fatwa->body ?? '');
                                    $isMalayalamPub = (bool) preg_match('/[\x{0D00}-\x{0D7F}]/u', $textSamplePub) || strtolower($fatwa->language ?? '') === 'malayalam';
                                    $isArabicPub = (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $textSamplePub) || in_array(strtolower($fatwa->language ?? ''), ['arabic', 'ar']);
                                    $isUrduPub = strtolower($fatwa->language ?? '') === 'urdu';
                                    $pubFontClass = $isMalayalamPub ? 'font-malayalam' : ($isUrduPub ? 'font-urdu' : ($isArabicPub ? 'font-arabic' : ''));
                                @endphp
                                <tr>
                                    <td class="ps-4 py-3.5">
                                        <span class="badge" style="background: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE; font-weight: 700; border-radius: 6px; padding: 5px 9px; font-size: 0.76rem;">
                                            {{ $fatwa->question_id }}
                                        </span>
                                    </td>
                                    <td class="py-3.5">
                                        <div class="fw-bold text-dark {{ $pubFontClass }}" dir="auto" style="font-size: 0.88rem;">
                                            {{ Str::limit(strip_tags($fatwa->title ?: $fatwa->body), 55) }}
                                        </div>
                                    </td>
                                    <td class="py-3.5">
                                        <div class="fw-semibold text-dark small">{{ $fatwa->category?->name ?? 'General' }}</div>
                                        <div class="text-muted" style="font-size: 0.74rem;">{{ $fatwa->madhab?->name ?? 'Common Ground' }}</div>
                                    </td>
                                    <td class="text-center py-3.5">
                                        <span class="small fw-semibold text-muted">
                                            <i class="bi bi-eye me-1"></i>{{ number_format($fatwa->views) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4 py-3.5">
                                        <a href="/fatwa-archive/{{ $fatwa->id }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem;">
                                            View Public <i class="bi bi-box-arrow-up-right ms-1"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">No published fatwas found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Geographic Signals Column --}}
    <div class="col-xl-3">
        <div class="modern-card p-4 px-4 px-md-4.5 h-100 mb-0 d-flex flex-column justify-content-between">
            <div>
                <div class="d-flex justify-content-between align-items-center mb-3.5">
                    <h6 class="fw-bold mb-0 small text-uppercase text-muted" style="letter-spacing: 0.5px;">Geographic Signals</h6>
                    <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1" style="font-size: 10px;">Top Regions</span>
                </div>
                
                @forelse($geoReach as $reach)
                @php
                    $rCountry = data_get($reach, 'country', 'Unknown');
                    $rTotal = (int) data_get($reach, 'total_count', 0);
                    $rTopRegion = data_get($reach, 'top_region', 'General');
                    $rRegionCount = (int) data_get($reach, 'region_count', 0);
                @endphp
                <div class="mb-3.5 pb-2.5 border-bottom">
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <span class="small fw-bold text-dark text-truncate" style="max-width: 140px;">{{ $rCountry }}</span>
                        <span class="small text-primary fw-bold">{{ number_format($rTotal) }}</span>
                    </div>
                    <div class="progress" style="height: 7px; border-radius: 10px; background: #E2E8F0; margin: 6px 0;">
                        <div class="progress-bar" style="background: var(--primary-blue); width: {{ $loop->first ? '100%' : rand(35, 80).'%' }}; border-radius: 10px;"></div>
                    </div>
                    <div class="mt-1 d-flex justify-content-between text-muted" style="font-size: 0.74rem;">
                        <span class="text-truncate" style="max-width: 130px;"><i class="bi bi-geo-alt me-1"></i>{{ $rTopRegion }}</span>
                        <span>{{ number_format($rRegionCount) }} hits</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-globe2 fs-2 text-slate-300"></i>
                    <p class="mt-2 small mb-0">Awaiting global signals</p>
                </div>
                @endforelse
            </div>

            <a href="{{ route('admin.visitor.insights') }}" class="btn btn-modern-secondary w-100 justify-content-center py-2.5 mt-3">
                Full Global Intel <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>

{{-- Custom PDF Export Configuration Modal --}}
<div class="modal fade" id="exportPdfModal" tabindex="-1" aria-labelledby="exportPdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden; border: 1px solid #E2E8F0;">
            <form action="{{ route('admin.fatwas.export.pdf') }}" method="GET" target="_blank">
                <div class="modal-header bg-white border-bottom p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-inline-flex p-2.5 rounded-3" style="background: #EFF6FF; color: #2563EB;">
                            <i class="bi bi-file-earmark-pdf-fill fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="exportPdfModalLabel">
                                Bulk PDF Dossier Export
                            </h5>
                            <p class="mb-0 text-muted small mt-0.5">Configure criteria, font engines, and print layouts (1 Question Per Page).</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4 bg-light bg-opacity-50">
                    {{-- Status Selection --}}
                    <div class="card border-0 shadow-xs mb-3 bg-white rounded-3 border">
                        <div class="card-body p-3.5">
                            <label class="form-label fw-bold text-dark small mb-2.5 d-flex justify-content-between align-items-center">
                                <span class="d-flex align-items-center gap-1.5">
                                    <i class="bi bi-check2-square text-primary"></i> Target Fatwa Statuses
                                </span>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small fw-bold" onclick="toggleAllStatuses(this)">Select All</button>
                            </label>
                            <div class="row g-2">
                                @foreach([
                                    ['pending', 'Pending', '#FFFBEB', '#D97706', true],
                                    ['accepted', 'Accepted', '#EFF6FF', '#2563EB', true],
                                    ['assigned', 'Assigned', '#F0F9FF', '#0284C7', false],
                                    ['answered', 'Answered', '#FAF5FF', '#9333EA', false],
                                    ['published', 'Published', '#ECFDF5', '#059669', false],
                                    ['rejected', 'Rejected', '#FEF2F2', '#DC2626', false],
                                ] as [$sVal, $sLbl, $sBg, $sColor, $checked])
                                <div class="col-sm-4 col-6">
                                    <label class="d-flex align-items-center gap-2 p-2.5 rounded-3 border cursor-pointer w-100 mb-0" style="background: #FAFBFD; border-color: #E2E8F0 !important;">
                                        <input class="form-check-input status-checkbox m-0" type="checkbox" name="status[]" value="{{ $sVal }}" id="st_{{ $sVal }}" {{ $checked ? 'checked' : '' }}>
                                        <span class="small fw-semibold" style="color: {{ $sColor }};">
                                            ● {{ $sLbl }}
                                        </span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        {{-- Language Selection --}}
                        <div class="col-md-6">
                            <div class="card border-0 shadow-xs h-100 bg-white rounded-3 border">
                                <div class="card-body p-3.5">
                                    <label class="form-label fw-bold text-dark small mb-1.5 d-flex align-items-center gap-1.5">
                                        <i class="bi bi-translate text-primary"></i> Language &amp; Font Engine
                                    </label>
                                    <select class="modern-select" name="language">
                                        <option value="all" selected>All Languages (Auto Noto Font)</option>
                                        <option value="Malayalam">Malayalam (Noto Serif Malayalam)</option>
                                        <option value="Arabic">Arabic (Noto Naskh Arabic &amp; Amiri)</option>
                                        <option value="Urdu">Urdu (Noto Nastaliq Urdu)</option>
                                        <option value="English">English / Latin (Noto Sans)</option>
                                    </select>
                                    <div class="text-muted mt-1.5" style="font-size: 0.72rem;">Font family applies dynamically to Malayalam, Urdu, and Arabic.</div>
                                </div>
                            </div>
                        </div>

                        {{-- Madhab & Category Filter --}}
                        <div class="col-md-6">
                            <div class="card border-0 shadow-xs h-100 bg-white rounded-3 border">
                                <div class="card-body p-3.5">
                                    <div class="mb-2.5">
                                        <label class="form-label fw-bold text-dark small mb-1.5 d-flex align-items-center gap-1.5">
                                            <i class="bi bi-book text-primary"></i> Jurisprudence School (Madhab)
                                        </label>
                                        <select class="modern-select" name="madhab_id">
                                            <option value="">All Madhabs</option>
                                            @foreach($madhabs ?? [] as $m)
                                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label fw-bold text-dark small mb-1.5 d-flex align-items-center gap-1.5">
                                            <i class="bi bi-folder2 text-primary"></i> Category Catalog
                                        </label>
                                        <select class="modern-select" name="category_id">
                                            <option value="">All Categories</option>
                                            @foreach($categories ?? [] as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Export Output Preferences --}}
                    <div class="card border-0 shadow-xs mt-3 bg-white rounded-3 border">
                        <div class="card-body p-3.5">
                            <div class="row align-items-center">
                                <div class="col-md-7">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="auto_print" value="1" id="autoPrintSwitch" checked>
                                        <label class="form-check-label small fw-semibold text-dark" for="autoPrintSwitch">
                                            Auto-open System Print / PDF Dialog
                                        </label>
                                    </div>
                                    <div class="text-muted ps-4" style="font-size: 0.72rem;">Opens printer dialog directly on page load</div>
                                </div>
                                <div class="col-md-5 text-md-end mt-2 mt-md-0">
                                    <span class="badge" style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0; padding: 5px 12px; border-radius: 20px; font-size: 0.74rem;">
                                        <i class="bi bi-file-break me-1"></i> 1 Question Per Page
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top p-3.5 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-modern-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-modern-primary px-4">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Generate &amp; Download PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* ── Chart Canvas Responsive Wrapper (Spacious, airy height) ── */
.chart-canvas-wrapper {
    height: 350px;
    min-height: 350px;
}
@media (max-width: 991px) {
    .chart-canvas-wrapper {
        height: 300px;
        min-height: 300px;
    }
}
@media (max-width: 576px) {
    .chart-canvas-wrapper {
        height: 240px;
        min-height: 240px;
    }
}

/* ── Chart Summary Stat Box (Reference Image Styling) ── */
.chart-summary-stat-box {
    background: #ffffff;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 1.2rem 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
}
.chart-summary-stat-box:hover {
    transform: translateY(-2px);
    border-color: #CBD5E1;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
}
.chart-summary-stat-label {
    font-size: 0.72rem;
    letter-spacing: 0.6px;
    color: #64748B;
    margin-bottom: 0.4rem;
}

/* ── Toggle Buttons in Chart Header ── */
.chart-toggle-btn {
    font-size: 11px;
    padding: 4px 12px;
    transition: all 0.15s ease;
}
.chart-toggle-btn.active {
    background: #ffffff !important;
    color: #0F172A !important;
    border: 1.5px solid #0F172A !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06) !important;
}

/* ── Scholar Live Rectangular Cards ── */
.scholar-live-card {
    background: #ffffff;
    border: 1px solid #E2E8F0 !important;
    border-radius: 14px;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}
.scholar-live-card:hover {
    border-color: #93C5FD !important;
    background-color: #FAFBFD !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.08);
}
.scholar-name-link {
    transition: color 0.15s ease;
}
.scholar-live-card:hover .scholar-name-link {
    color: #2563EB !important;
}

/* ── Animated Green Pulse Dot ── */
.live-pulse-dot {
    width: 6px;
    height: 6px;
    background-color: #10B981;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: pulse-green 2s infinite;
}
@keyframes pulse-green {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

/* ── Feed Tabs ── */
.modern-feed-tabs .nav-link {
    border: 1px solid transparent;
    color: #64748B;
    background: transparent;
    transition: all 0.15s ease;
}
.modern-feed-tabs .nav-link:hover {
    color: #1E293B;
    background: #F1F5F9;
}
.modern-feed-tabs .nav-link.active {
    background: #EFF6FF !important;
    color: #2563EB !important;
    border-color: #BFDBFE !important;
}

/* ── System Shortcuts Tile ── */
.modern-shortcut-tile {
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    border-color: #E2E8F0 !important;
}
.modern-shortcut-tile:hover {
    border-color: #2563EB !important;
    background-color: #F8FAFC !important;
    transform: translateX(3px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08);
}
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function toggleAllStatuses(btn) {
    const checkboxes = document.querySelectorAll('.status-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
    btn.innerText = allChecked ? 'Select All' : 'Deselect All';
}

// ── Tab Switch Handler for Feed "View All" Button ──
const feedTabs = document.querySelectorAll('#inquiriesFeedTabs button[data-bs-toggle="pill"]');
const feedViewAllBtn = document.getElementById('feedViewAllBtn');
if (feedTabs && feedViewAllBtn) {
    feedTabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (event) {
            if (event.target.id === 'recent-published-tab') {
                feedViewAllBtn.href = "{{ route('admin.questions.index', ['status' => 'published']) }}";
                feedViewAllBtn.innerHTML = 'See All Published <i class="bi bi-chevron-right ms-0.5"></i>';
            } else {
                feedViewAllBtn.href = "{{ route('admin.questions.index') }}";
                feedViewAllBtn.innerHTML = 'View All Inquiries <i class="bi bi-chevron-right ms-0.5"></i>';
            }
        });
    });
}

// ── Lifelong Analytics Multi-Mode Datasets (Exact Color Palette from Reference Image) ──
const lifelongData = {
    yearly: {
        labels: @json($lifelongYears),
        datasets: [
            {
                label: 'Total Inquiries',
                data: @json($lifelongTotalCounts),
                backgroundColor: '#2D5A43',
                borderColor: '#2D5A43',
                borderWidth: 1,
                borderRadius: { topLeft: 8, topRight: 8, bottomLeft: 0, bottomRight: 0 },
                maxBarThickness: 38,
                barPercentage: 0.62,
                categoryPercentage: 0.72
            },
            {
                label: 'Published Fatwas',
                data: @json($lifelongPublishedCounts),
                backgroundColor: '#CCA43B',
                borderColor: '#CCA43B',
                borderWidth: 1,
                borderRadius: { topLeft: 8, topRight: 8, bottomLeft: 0, bottomRight: 0 },
                maxBarThickness: 38,
                barPercentage: 0.62,
                categoryPercentage: 0.72
            }
        ],
        type: 'bar'
    },
    monthly: {
        labels: @json($lifelongMonthLabels),
        datasets: [
            {
                label: 'Monthly Inquiries',
                data: @json($lifelongMonthCounts),
                borderColor: '#2D5A43',
                backgroundColor: 'rgba(45, 90, 67, 0.12)',
                borderWidth: 2,
                fill: true,
                tension: 0.35,
                pointRadius: 2,
                pointHoverRadius: 6,
                pointBackgroundColor: '#2D5A43'
            }
        ],
        type: 'line'
    },
    cumulative: {
        labels: @json($lifelongMonthLabels),
        datasets: [
            {
                label: 'Cumulative Total Inquiries',
                data: @json($cumulativeCounts),
                borderColor: '#CCA43B',
                backgroundColor: 'rgba(204, 164, 59, 0.18)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.25,
                pointRadius: 1,
                pointHoverRadius: 6,
                pointBackgroundColor: '#CCA43B'
            }
        ],
        type: 'line'
    }
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    animation: { duration: 300 },
    interaction: {
        mode: 'index',
        intersect: false
    },
    plugins: {
        legend: {
            display: true,
            position: 'top',
            align: 'center',
            labels: {
                boxWidth: 11,
                boxHeight: 11,
                usePointStyle: true,
                pointStyle: 'circle',
                padding: 18,
                font: { size: 12, weight: '600', family: "'Plus Jakarta Sans', sans-serif" },
                color: '#334155'
            }
        },
        tooltip: {
            backgroundColor: '#0F172A',
            padding: 12,
            cornerRadius: 8,
            titleFont: { size: 12, weight: '700', family: "'Plus Jakarta Sans', sans-serif" },
            bodyFont: { size: 11, family: "'Plus Jakarta Sans', sans-serif" },
            boxPadding: 6,
            usePointStyle: false
        }
    },
    scales: {
        y: {
            grid: { color: '#F1F5F9', drawBorder: false },
            beginAtZero: true,
            ticks: {
                color: '#64748B',
                font: { size: 11, family: "'Plus Jakarta Sans', sans-serif" },
                precision: 0,
                padding: 8
            }
        },
        x: {
            grid: { display: false, drawBorder: false },
            ticks: {
                color: '#64748B',
                font: { size: 11, weight: '600', family: "'Plus Jakarta Sans', sans-serif" },
                maxRotation: 0,
                autoSkip: true,
                maxTicksLimit: 12,
                padding: 8
            }
        }
    }
};

const ctx = document.getElementById('lifelongChart').getContext('2d');
let currentChart = new Chart(ctx, {
    type: lifelongData.yearly.type,
    data: {
        labels: lifelongData.yearly.labels,
        datasets: lifelongData.yearly.datasets
    },
    options: chartOptions
});

function switchChartView(mode) {
    document.querySelectorAll('.chart-toggle-btn').forEach(btn => {
        btn.classList.remove('active', 'shadow-xs');
        btn.classList.add('text-muted');
    });

    const activeBtn = document.getElementById(mode === 'yearly' ? 'btnViewYearly' : (mode === 'monthly' ? 'btnViewMonthly' : 'btnViewCumulative'));
    if (activeBtn) {
        activeBtn.classList.add('active', 'shadow-xs');
        activeBtn.classList.remove('text-muted');
    }

    const cfg = lifelongData[mode];
    if (currentChart) {
        currentChart.destroy();
    }
    currentChart = new Chart(ctx, {
        type: cfg.type,
        data: {
            labels: cfg.labels,
            datasets: cfg.datasets
        },
        options: chartOptions
    });
}

// ── Live Scholar Cards Real-time Dynamic Renderer ──
function updateScholarCards(muftis) {
    const container = document.getElementById('live-scholars-grid-container');
    const countBadge = document.getElementById('live-scholars-count-badge');
    const countNum = document.getElementById('live-scholars-count-num');
    
    if (countNum) countNum.innerText = muftis.length;
    
    if (!container) return;

    window.__liveData = window.__liveData || { muftis: [], questioners: [], visitors: [] };
    window.__liveData.muftis = muftis || [];
    renderFilteredLiveCards();
}

let currentLiveFilter = 'all';

// Setup tab click events
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.live-filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.live-filter-btn').forEach(b => {
                b.classList.remove('active', 'bg-primary', 'text-white');
                b.classList.add('text-muted');
            });
            this.classList.add('active', 'bg-primary', 'text-white');
            this.classList.remove('text-muted');
            currentLiveFilter = this.getAttribute('data-filter') || 'all';
            renderFilteredLiveCards();
        });
    });
});

function renderFilteredLiveCards() {
    const container = document.getElementById('live-scholars-grid-container');
    if (!container) return;
    
    const countBadge = document.getElementById('live-scholars-count-badge');
    const data = window.__liveData || { muftis: [], questioners: [], visitors: [] };
    
    const muftis = data.muftis || [];
    const questioners = data.questioners || [];
    const visitors = data.visitors || [];
    
    const totalCount = muftis.length + questioners.length + visitors.length;
    
    // Update badge counts
    const countNum = document.getElementById('live-scholars-count-num');
    if (countNum) countNum.innerText = totalCount;
    
    const fAll = document.getElementById('filter-count-all');
    if (fAll) fAll.innerText = totalCount;
    const fMufti = document.getElementById('filter-count-mufti');
    if (fMufti) fMufti.innerText = muftis.length;
    const fQ = document.getElementById('filter-count-questioner');
    if (fQ) fQ.innerText = questioners.length;
    const fV = document.getElementById('filter-count-visitor');
    if (fV) fV.innerText = visitors.length;
    
    // Filter items
    let cards = [];
    if (currentLiveFilter === 'all' || currentLiveFilter === 'mufti') {
        muftis.forEach(m => cards.push({ ...m, itemType: 'mufti' }));
    }
    if (currentLiveFilter === 'all' || currentLiveFilter === 'questioner') {
        questioners.forEach(q => cards.push({ ...q, itemType: 'questioner' }));
    }
    if (currentLiveFilter === 'all' || currentLiveFilter === 'visitor') {
        visitors.forEach(v => cards.push({ ...v, itemType: 'visitor' }));
    }
    
    if (cards.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5 px-3 bg-light bg-opacity-50 rounded-3 border border-dashed" id="live-scholars-empty-state">
                <div class="d-inline-flex p-3 rounded-circle bg-white border shadow-xs text-muted mb-2.5">
                    <i class="bi bi-people fs-3 text-slate-400"></i>
                </div>
                <div class="fw-semibold text-dark">No active live users matching this category.</div>
                <div class="text-muted small mt-1" style="font-size: 0.76rem;">
                    Active users will automatically stream in real-time across the entire platform.
                </div>
            </div>
        `;
        return;
    }
    
    let html = '<div class="row g-3" id="live-scholars-grid">';
    cards.forEach(item => {
        if (item.itemType === 'mufti') {
            html += `
                <div class="col-12 col-md-6 col-xl-4 live-user-card-col" data-role="mufti">
                    <div class="scholar-live-card p-3.5 rounded-3 border bg-white h-100 d-flex flex-column justify-content-between shadow-2xs">
                        <div class="d-flex align-items-center gap-3 mb-2.5">
                            <div class="position-relative flex-shrink-0">
                                <img src="${item.avatar}" alt="${item.name}" class="rounded-circle border" width="46" height="46" style="object-fit: cover;">
                                <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle" style="width: 11px; height: 11px;" title="Online now"></span>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center justify-content-between">
                                    <a href="${item.history_url}" class="fw-bold text-dark text-decoration-none text-truncate d-block scholar-name-link" style="font-size: 0.88rem;" title="${item.name}">
                                        ${item.name}
                                    </a>
                                    <span class="badge rounded-pill px-2 py-0.5 text-2xs fw-bold" style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0;">
                                        Scholar
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0.5 d-inline-flex align-items-center gap-1" style="font-size: 0.68rem; font-weight: 700;">
                                        <span class="live-pulse-dot" style="width: 5px; height: 5px;"></span> LIVE
                                    </span>
                                    <span class="badge bg-light text-muted border rounded-pill px-2.5 py-0.5 dash-live-timer font-monospace" data-start-time="${item.start_time}" style="font-size: 0.72rem;">
                                        00:00:00
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="pt-2.5 border-top mt-auto d-flex align-items-center justify-content-between text-muted" style="font-size: 0.74rem;">
                            <span class="d-flex align-items-center gap-1">
                                <i class="bi bi-inbox text-primary"></i>
                                <span>Queue: <strong class="text-dark">${item.pending_count || 0}</strong> pending</span>
                            </span>
                            <span><i class="bi bi-clock-history me-1"></i>${item.last_seen || 'Active'}</span>
                        </div>
                    </div>
                </div>
            `;
        } else if (item.itemType === 'questioner') {
            html += `
                <div class="col-12 col-md-6 col-xl-4 live-user-card-col" data-role="questioner">
                    <div class="scholar-live-card p-3.5 rounded-3 border bg-white h-100 d-flex flex-column justify-content-between shadow-2xs">
                        <div class="d-flex align-items-center gap-3 mb-2.5">
                            <div class="position-relative flex-shrink-0">
                                <img src="${item.avatar}" alt="${item.name}" class="rounded-circle border" width="46" height="46" style="object-fit: cover;">
                                <span class="position-absolute bottom-0 end-0 p-1 bg-sky-500 border border-white rounded-circle" style="width: 11px; height: 11px;" title="Online now"></span>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center justify-content-between">
                                    <a href="${item.profile_url}" class="fw-bold text-dark text-decoration-none text-truncate d-block scholar-name-link" style="font-size: 0.88rem;" title="${item.name}">
                                        ${item.name}
                                    </a>
                                    <span class="badge rounded-pill px-2 py-0.5 text-2xs fw-bold" style="background: #F0F9FF; color: #0284C7; border: 1px solid #BAE6FD;">
                                        Questioner
                                    </span>
                                </div>
                                <div class="text-muted text-truncate mt-0.5" style="font-size: 0.72rem;">
                                    ${item.email}
                                </div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                        <i class="bi bi-geo-alt me-0.5"></i>${item.country || 'Global'}
                                    </span>
                                    <span class="badge bg-slate-50 text-slate-600 border rounded-pill px-2 py-0.5 text-truncate" style="font-size: 0.68rem; max-width: 120px;" title="${item.page}">
                                        ${item.page}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="pt-2.5 border-top mt-auto d-flex align-items-center justify-content-between text-muted" style="font-size: 0.74rem;">
                            <span class="d-flex align-items-center gap-1">
                                <i class="bi bi-question-circle text-info"></i>
                                <span>Questions: <strong class="text-dark">${item.total_q || 0}</strong></span>
                            </span>
                            <span><i class="bi bi-clock-history me-1"></i>${item.last_seen || 'Active'}</span>
                        </div>
                    </div>
                </div>
            `;
        } else if (item.itemType === 'visitor') {
            const deviceIcon = item.device === 'Mobile' ? 'bi-phone' : (item.device === 'Tablet' ? 'bi-tablet' : 'bi-laptop');
            html += `
                <div class="col-12 col-md-6 col-xl-4 live-user-card-col" data-role="visitor">
                    <div class="scholar-live-card p-3.5 rounded-3 border bg-white h-100 d-flex flex-column justify-content-between shadow-2xs">
                        <div class="d-flex align-items-center gap-3 mb-2.5">
                            <div class="position-relative flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle border bg-slate-50 text-slate-600" style="width: 46px; height: 46px;">
                                <i class="bi ${deviceIcon} fs-5"></i>
                                <span class="position-absolute bottom-0 end-0 p-1 bg-emerald-500 border border-white rounded-circle" style="width: 11px; height: 11px;" title="Active"></span>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fw-bold text-dark text-truncate d-block" style="font-size: 0.88rem;">
                                        ${item.name}
                                    </span>
                                    <span class="badge rounded-pill px-2 py-0.5 text-2xs fw-bold" style="background: #F8FAFC; color: #475569; border: 1px solid #E2E8F0;">
                                        Visitor
                                    </span>
                                </div>
                                <div class="text-muted text-truncate mt-0.5" style="font-size: 0.72rem;">
                                    <i class="bi bi-geo-alt me-0.5 text-slate-400"></i>${item.country || 'Global Visitor'}
                                </div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5 text-truncate" style="font-size: 0.68rem; max-width: 140px;" title="${item.page}">
                                        ${item.page}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="pt-2.5 border-top mt-auto d-flex align-items-center justify-content-between text-muted" style="font-size: 0.74rem;">
                            <span class="d-flex align-items-center gap-1">
                                <i class="bi bi-browser-chrome text-secondary"></i>
                                <span>${item.browser} &bull; ${item.device}</span>
                            </span>
                            <span><i class="bi bi-clock-history me-1"></i>${item.last_seen || 'Active'}</span>
                        </div>
                    </div>
                </div>
            `;
        }
    });
    html += '</div>';
    container.innerHTML = html;
    updateDashLiveTimers();
}

// ── Polling: Live Visitors, Scholars, Questioners ──
async function fetchLiveUsers() {
    if (document.hidden) return;
    try {
        const response = await fetch('{{ route("admin.live-stats.api") }}');
        const data = await response.json();
        if (data) {
            // Update live visitors telemetry count
            const countEl = document.getElementById('live-users-count');
            if (countEl && countEl.innerText !== String(data.byIp)) {
                countEl.innerText = data.byIp;
            }
            
            window.__liveData = {
                muftis: data.onlineMuftis || [],
                questioners: data.onlineQuestioners || [],
                visitors: data.onlineVisitors || []
            };
            renderFilteredLiveCards();

            // Check for new real-time activity events
            if (data.latestEvent && data.latestEvent.id) {
                if (window.__lastSeenEventId !== undefined && window.__lastSeenEventId !== null && data.latestEvent.id > window.__lastSeenEventId) {
                    playNotificationSound(data.latestEvent.status);
                    showDashboardToast(data.latestEvent);
                }
                window.__lastSeenEventId = data.latestEvent.id;
            }
        }
    } catch (error) {}
}

// ── Audio Notification Synthesizer (Web Audio API - No external mp3 files required) ──
window.__dashSoundMuted = localStorage.getItem('dash_sound_muted') === 'true';

function updateSoundToggleButton() {
    const icon = document.getElementById('sound-icon');
    const label = document.getElementById('sound-label');
    const btn = document.getElementById('btn-sound-toggle');
    if (!icon || !label) return;

    if (window.__dashSoundMuted) {
        icon.className = 'bi bi-volume-mute-fill text-muted';
        label.innerText = 'Sound: OFF';
        label.className = 'text-muted';
        if (btn) btn.classList.add('bg-light');
    } else {
        icon.className = 'bi bi-volume-up-fill text-primary';
        label.innerText = 'Sound: ON';
        label.className = 'text-dark';
        if (btn) btn.classList.remove('bg-light');
    }
}

function toggleDashboardSound() {
    window.__dashSoundMuted = !window.__dashSoundMuted;
    localStorage.setItem('dash_sound_muted', window.__dashSoundMuted ? 'true' : 'false');
    updateSoundToggleButton();
    if (!window.__dashSoundMuted) {
        playNotificationSound('chime');
    }
}
document.addEventListener('DOMContentLoaded', updateSoundToggleButton);

function playNotificationSound(type = 'chime') {
    if (window.__dashSoundMuted) return;
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const now = ctx.currentTime;

        if (type === 'failed') {
            // Soft Alert Double Tone (descending: 440Hz -> 330Hz)
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(440, now);
            osc.frequency.setValueAtTime(329.63, now + 0.12);
            gain.gain.setValueAtTime(0.18, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.38);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now);
            osc.stop(now + 0.38);
        } else {
            // Pleasant Crystal Chime (ascending: 587.33Hz (D5) -> 880Hz (A5))
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, now);
            osc.frequency.exponentialRampToValueAtTime(880.00, now + 0.15);
            gain.gain.setValueAtTime(0.16, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.55);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now);
            osc.stop(now + 0.55);
        }
    } catch (e) {
        // AudioContext blocked before first user gesture
    }
}

// ── Floating Toast Popup Display ──
let toastAutoDismissTimer = null;

function showDashboardToast(event) {
    const toastEl = document.getElementById('dash-live-notification-toast');
    const toastCard = document.getElementById('dash-toast-card');
    const iconWrapper = document.getElementById('dash-toast-icon-wrapper');
    const icon = document.getElementById('dash-toast-icon');
    const badge = document.getElementById('dash-toast-badge');
    const title = document.getElementById('dash-toast-title');
    const body = document.getElementById('dash-toast-body');
    const time = document.getElementById('dash-toast-time');
    const actionBtn = document.getElementById('dash-toast-action-btn');

    if (!toastEl) return;

    const isFailed = event.status === 'failed';
    const isWarning = event.status === 'warning';

    // Set colors & border
    if (isFailed) {
        toastCard.style.borderLeft = '5px solid #DC2626';
        iconWrapper.className = 'p-2 rounded-circle bg-danger bg-opacity-10 text-danger flex-shrink-0';
        icon.className = 'bi bi-exclamation-octagon-fill fs-5';
        badge.className = 'badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 text-2xs fw-bold px-2 py-0.5 rounded-pill';
        badge.innerText = event.failure ? 'Issue: ' + event.failure.replace('_', ' ') : 'Failed Event';
    } else if (isWarning) {
        toastCard.style.borderLeft = '5px solid #D97706';
        iconWrapper.className = 'p-2 rounded-circle bg-warning bg-opacity-10 text-warning flex-shrink-0';
        icon.className = 'bi bi-exclamation-triangle-fill fs-5';
        badge.className = 'badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 text-2xs fw-bold px-2 py-0.5 rounded-pill';
        badge.innerText = 'Warning Alert';
    } else {
        toastCard.style.borderLeft = '5px solid #059669';
        iconWrapper.className = 'p-2 rounded-circle bg-success bg-opacity-10 text-success flex-shrink-0';
        icon.className = 'bi bi-check-circle-fill fs-5';
        badge.className = 'badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 text-2xs fw-bold px-2 py-0.5 rounded-pill';
        badge.innerText = event.action_label || 'Activity Event';
    }

    title.innerText = (event.action_label ? event.action_label + ': ' : '') + (event.user_name || 'User');
    body.innerText = event.description || 'Recent activity detected on the portal.';
    time.innerText = event.time || 'Just now';

    if (actionBtn && event.url) {
        actionBtn.href = event.url;
    }

    toastEl.style.display = 'block';
    toastEl.classList.add('animate__animated', 'animate__fadeInUp');

    clearTimeout(toastAutoDismissTimer);
    toastAutoDismissTimer = setTimeout(() => {
        dismissDashboardToast();
    }, 7000);
}

function dismissDashboardToast() {
    const toastEl = document.getElementById('dash-live-notification-toast');
    if (toastEl) {
        toastEl.style.display = 'none';
    }
}
fetchLiveUsers();
setInterval(fetchLiveUsers, 5000);

// ── Live Running Session Timers ──
function updateDashLiveTimers() {
    document.querySelectorAll('.dash-live-timer').forEach(elem => {
        const startTime = parseInt(elem.getAttribute('data-start-time'), 10);
        if (isNaN(startTime)) return;
        const now = Date.now();
        const diffSecs = Math.max(0, Math.floor((now - startTime) / 1000));
        
        const hours = Math.floor(diffSecs / 3600);
        const minutes = Math.floor((diffSecs % 3600) / 60);
        const seconds = diffSecs % 60;
        
        const formatted = 
            String(hours).padStart(2, '0') + ':' +
            String(minutes).padStart(2, '0') + ':' +
            String(seconds).padStart(2, '0');
        
        elem.innerHTML = formatted;
    });
}
updateDashLiveTimers();
setInterval(updateDashLiveTimers, 1000);
</script>
{{-- Floating Real-time Notification Toast Popup --}}
<div id="dash-live-notification-toast" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999; display: none; max-width: 380px;">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden" id="dash-toast-card" style="background: #ffffff; border-left: 5px solid #2563EB !important; transition: all 0.3s ease;">
        <div class="card-body p-3.5">
            <div class="d-flex align-items-start gap-2.5">
                <div id="dash-toast-icon-wrapper" class="p-2 rounded-circle bg-primary bg-opacity-10 text-primary flex-shrink-0">
                    <i class="bi bi-bell-fill fs-5" id="dash-toast-icon"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex align-items-center justify-content-between mb-0.5">
                        <span class="badge bg-light text-muted border text-2xs fw-bold px-2 py-0.5 rounded-pill" id="dash-toast-badge">Activity Alert</span>
                        <small class="text-muted text-2xs" id="dash-toast-time">Just now</small>
                    </div>
                    <h6 class="fw-bold mb-1 text-dark text-truncate" id="dash-toast-title" style="font-size: 0.88rem;">New Activity</h6>
                    <p class="text-muted mb-2 small text-truncate-2" id="dash-toast-body" style="font-size: 0.76rem; line-height: 1.35;">Details...</p>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('admin.user-logs.index') }}" class="btn btn-xs btn-primary rounded-pill px-2.5 py-1 text-2xs fw-semibold" id="dash-toast-action-btn">
                            Inspect Logs <i class="bi bi-arrow-right ms-0.5"></i>
                        </a>
                        <button type="button" class="btn btn-xs btn-light border rounded-pill px-2 py-1 text-2xs text-muted" onclick="dismissDashboardToast()">
                            Dismiss
                        </button>
                    </div>
                </div>
                <button type="button" class="btn-close text-2xs p-1" onclick="dismissDashboardToast()" aria-label="Close"></button>
            </div>
        </div>
    </div>
</div>

@endpush

@endsection
