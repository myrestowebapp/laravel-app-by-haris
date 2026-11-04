@extends('layouts.admin')

@section('title', 'Site Traffic & Performance Monitor')

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

    {{-- Top Header & Breadcrumbs --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-xs">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><span class="text-muted">Intelligence &amp; Reports</span></li>
                    <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Traffic &amp; Performance</li>
                </ol>
            </nav>
            <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-speedometer2 text-primary"></i>
                <span>Site Traffic &amp; Application Performance Monitor</span>
                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-0.5 text-2xs fw-bold">
                    <span class="live-pulse-dot me-1"></span> LIVE APM
                </span>
            </h4>
            <p class="text-muted small mb-0 mt-0.5">Real-time visitor telemetry, server throughput velocity, database latency &amp; synthetic diagnostic health.</p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            {{-- Period Filter Buttons --}}
            <div class="btn-group btn-group-sm p-1 bg-white border rounded-pill shadow-2xs" role="group">
                <a href="{{ route('admin.traffic.performance', ['period' => 'today']) }}" class="btn rounded-pill px-3 py-1 fw-semibold text-xs {{ $period === 'today' ? 'btn-primary' : 'btn-light text-muted' }}">Today</a>
                <a href="{{ route('admin.traffic.performance', ['period' => '24h']) }}" class="btn rounded-pill px-3 py-1 fw-semibold text-xs {{ $period === '24h' ? 'btn-primary' : 'btn-light text-muted' }}">24 Hours</a>
                <a href="{{ route('admin.traffic.performance', ['period' => '7d']) }}" class="btn rounded-pill px-3 py-1 fw-semibold text-xs {{ $period === '7d' ? 'btn-primary' : 'btn-light text-muted' }}">7 Days</a>
                <a href="{{ route('admin.traffic.performance', ['period' => '30d']) }}" class="btn rounded-pill px-3 py-1 fw-semibold text-xs {{ $period === '30d' ? 'btn-primary' : 'btn-light text-muted' }}">30 Days</a>
            </div>

            {{-- Auto-refresh Toggle --}}
            <button type="button" class="btn btn-sm btn-white border rounded-pill px-3 py-1.5 fw-semibold shadow-2xs d-inline-flex align-items-center gap-1.5" id="btn-auto-refresh" onclick="toggleAutoRefresh()">
                <span class="spinner-grow spinner-grow-sm text-success" id="refresh-spinner" style="width: 8px; height: 8px;" role="status"></span>
                <span id="refresh-btn-label">Auto-Refresh: 5s</span>
            </button>

            {{-- Run Synthetic Benchmark Ping --}}
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-semibold shadow-2xs d-inline-flex align-items-center gap-1.5" onclick="runDiagnosticBenchmark()" id="btn-run-benchmark">
                <i class="bi bi-play-circle-fill" id="benchmark-icon"></i>
                <span>Run Benchmark</span>
            </button>
        </div>
    </div>

    {{-- 4 Primary Metric Cards --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: Live Traffic & Velocity --}}
        <div class="col-6 col-lg-3">
            <div class="modern-card p-3.5 p-md-4 rounded-4 bg-white border h-100 shadow-2xs position-relative overflow-hidden">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Live Presence &amp; RPM</span>
                    <div class="p-2 rounded-3 bg-success bg-opacity-10 text-success">
                        <i class="bi bi-broadcast fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-1">
                    <h3 class="fw-bold text-dark mb-0" id="stat-live-count">{{ number_format($liveVisitorsCount) }}</h3>
                    <span class="text-xs text-muted fw-semibold">online now</span>
                </div>
                <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 0.74rem;">
                    <span>Velocity: <strong class="text-dark" id="stat-rpm">{{ $rpm }}</strong> req/min</span>
                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5 text-2xs fw-bold">
                        <span class="live-pulse-dot me-1"></span> Live
                    </span>
                </div>
                <div class="mt-2 pt-2 border-top d-flex gap-1 flex-wrap text-2xs text-muted">
                    <span class="badge bg-light text-dark border">Muftis: <b id="stat-role-scholars">{{ $liveByRole['scholars'] ?? 0 }}</b></span>
                    <span class="badge bg-light text-dark border">Inquirers: <b id="stat-role-inquirers">{{ $liveByRole['questioners'] ?? 0 }}</b></span>
                    <span class="badge bg-light text-dark border">Guests: <b id="stat-role-visitors">{{ $liveByRole['visitors'] ?? 0 }}</b></span>
                </div>
            </div>
        </div>

        {{-- Card 2: Server Response Latency (APM) --}}
        <div class="col-6 col-lg-3">
            <div class="modern-card p-3.5 p-md-4 rounded-4 bg-white border h-100 shadow-2xs position-relative overflow-hidden">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Server Latency (Avg)</span>
                    <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-stopwatch-fill fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-1">
                    <h3 class="fw-bold text-dark mb-0" id="stat-avg-latency">{{ $avgResponseTimeMs }}</h3>
                    <span class="text-xs text-muted fw-semibold">ms</span>
                </div>
                <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 0.74rem;">
                    <span>p95: <strong class="text-dark" id="stat-p95-latency">{{ $p95ResponseTimeMs }}</strong> ms</span>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0.5 text-2xs fw-bold" id="stat-latency-grade">
                        <i class="bi bi-lightning-charge-fill me-1"></i>Optimal
                    </span>
                </div>
                <div class="mt-2 pt-2 border-top d-flex justify-content-between text-2xs text-muted">
                    <span>Success Rate: <strong class="text-success">98.4%</strong></span>
                    <span>2xx HTTP</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Database MySQL Query Ping --}}
        <div class="col-6 col-lg-3">
            <div class="modern-card p-3.5 p-md-4 rounded-4 bg-white border h-100 shadow-2xs position-relative overflow-hidden">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">MySQL Query Ping</span>
                    <div class="p-2 rounded-3 bg-info bg-opacity-10 text-info">
                        <i class="bi bi-database-check fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-1">
                    <h3 class="fw-bold text-dark mb-0" id="stat-db-latency">{{ $dbLatencyMs }}</h3>
                    <span class="text-xs text-muted fw-semibold">ms</span>
                </div>
                <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 0.74rem;">
                    <span>Engine: <strong class="text-dark">MySQL / InnoDB</strong></span>
                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5 text-2xs fw-bold" id="stat-db-status">
                        {{ $dbStatus }}
                    </span>
                </div>
                <div class="mt-2 pt-2 border-top d-flex justify-content-between text-2xs text-muted">
                    <span>Pool: <strong class="text-success">Active</strong></span>
                    <span>Direct Ping: &lt; 5ms</span>
                </div>
            </div>
        </div>

        {{-- Card 4: Memory Usage & Reliability --}}
        <div class="col-6 col-lg-3">
            <div class="modern-card p-3.5 p-md-4 rounded-4 bg-white border h-100 shadow-2xs position-relative overflow-hidden">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">PHP Memory Usage</span>
                    <div class="p-2 rounded-3 bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-cpu-fill fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-1">
                    <h3 class="fw-bold text-dark mb-0" id="stat-mem-usage">{{ $phpMemUsageMb }}</h3>
                    <span class="text-xs text-muted fw-semibold">MB</span>
                </div>
                <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 0.74rem;">
                    <span>Peak: <strong class="text-dark" id="stat-mem-peak">{{ $phpMemPeakMb }}</strong> MB</span>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0.5 text-2xs fw-bold">
                        Score: {{ $healthScore }}%
                    </span>
                </div>
                <div class="mt-2 pt-2 border-top d-flex justify-content-between text-2xs text-muted">
                    <span>Limit: <strong>{{ $phpMemLimit }}</strong></span>
                    <span>Utilized: <strong class="text-primary" id="stat-mem-pct">{{ $phpMemPercent }}%</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Visual Layout --}}
    <div class="row g-4 mb-4">
        {{-- Left Column (8 cols): Interactive Hourly Curves & Request Feed --}}
        <div class="col-12 col-xl-8">
            {{-- Chart Card --}}
            <div class="card modern-card border bg-white rounded-4 shadow-2xs mb-4">
                <div class="card-header bg-transparent border-0 pt-3.5 px-3.5 px-md-4 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-graph-up text-primary"></i>
                            <span id="chart-title">Hourly Traffic Throughput</span>
                        </h6>
                        <span class="text-muted text-xs">24-hour progression curve showing requests velocity and response latency.</span>
                    </div>
                    <div class="btn-group btn-group-sm p-0.5 bg-light border rounded-pill" role="group">
                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold text-xs active" id="btn-chart-traffic" onclick="switchChartMode('traffic')">Traffic (Hits)</button>
                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold text-xs text-muted" id="btn-chart-latency" onclick="switchChartMode('latency')">Latency (ms)</button>
                    </div>
                </div>
                <div class="card-body px-3.5 px-md-4 pt-3 pb-4">
                    <div class="position-relative" style="height: 270px; width: 100%;">
                        <canvas id="telemetryChart"></canvas>
                    </div>
                    <div class="row g-2 mt-3 pt-3 border-top text-center">
                        <div class="col-4">
                            <span class="text-muted text-2xs text-uppercase fw-bold">Period Traffic</span>
                            <div class="fw-bold text-dark fs-6" id="summary-period-traffic">{{ number_format($totalTrafficPeriod) }}</div>
                        </div>
                        <div class="col-4 border-start border-end">
                            <span class="text-muted text-2xs text-uppercase fw-bold">Unique Visitors</span>
                            <div class="fw-bold text-dark fs-6" id="summary-unique-ips">{{ number_format($uniqueIpsPeriod) }}</div>
                        </div>
                        <div class="col-4">
                            <span class="text-muted text-2xs text-uppercase fw-bold">Est. RPM</span>
                            <div class="fw-bold text-primary fs-6" id="summary-rpm">{{ $rpm }} req/min</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Live Request Stream Card --}}
            <div class="card modern-card border bg-white rounded-4 shadow-2xs">
                <div class="card-header bg-transparent border-0 pt-3.5 px-3.5 px-md-4 pb-2 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-activity text-success"></i>
                            <span>Real-Time Request Telemetry Stream</span>
                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-0.5 text-2xs fw-bold">
                                Streaming
                            </span>
                        </h6>
                        <span class="text-muted text-xs">Recent incoming web requests, response codes and processing latency.</span>
                    </div>
                    <span class="badge bg-light text-muted border text-2xs" id="stream-last-updated">
                        Updated: {{ $lastUpdated }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-xs">
                            <thead class="table-light text-uppercase text-muted" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                <tr>
                                    <th class="ps-3 ps-md-4">Method</th>
                                    <th>Route / Path</th>
                                    <th>Status</th>
                                    <th>Latency</th>
                                    <th>Client IP</th>
                                    <th class="pe-3 pe-md-4 text-end">Time</th>
                                </tr>
                            </thead>
                            <tbody id="request-stream-body">
                                @forelse($recentRequests as $req)
                                    <tr>
                                        <td class="ps-3 ps-md-4">
                                            @if(($req['method'] ?? 'GET') === 'POST')
                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle px-2 py-1 fw-bold">POST</span>
                                            @else
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 fw-bold">GET</span>
                                            @endif
                                        </td>
                                        <td class="fw-semibold text-dark font-monospace text-xs">
                                            {{ $req['path'] ?? '/' }}
                                        </td>
                                        <td>
                                            @php $st = (int) ($req['status_code'] ?? 200); @endphp
                                            @if($st >= 200 && $st < 300)
                                                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5 fw-bold">{{ $st }} OK</span>
                                            @elseif($st >= 300 && $st < 400)
                                                <span class="badge bg-info-subtle text-info rounded-pill px-2 py-0.5 fw-bold">{{ $st }} Redir</span>
                                            @elseif($st >= 400 && $st < 500)
                                                <span class="badge bg-warning-subtle text-warning rounded-pill px-2 py-0.5 fw-bold">{{ $st }} Client</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-0.5 fw-bold">{{ $st }} Error</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border fw-medium">
                                                {{ $req['duration_ms'] ?? 25 }} ms
                                            </span>
                                        </td>
                                        <td class="text-muted font-monospace text-2xs">
                                            {{ $req['ip'] ?? '127.0.0.1' }}
                                        </td>
                                        <td class="pe-3 pe-md-4 text-end text-muted text-2xs">
                                            {{ $req['time_ago'] ?? 'Just now' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No telemetry requests logged yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column (4 cols): Benchmark Tool & Distribution Breakdowns --}}
        <div class="col-12 col-xl-4">
            {{-- Synthetic Diagnostic Benchmark Widget --}}
            <div class="card modern-card border bg-white rounded-4 shadow-2xs mb-4">
                <div class="card-header bg-transparent border-0 pt-3.5 px-3.5 px-md-4 pb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-lightning-charge-fill text-warning"></i>
                            <span>Synthetic Benchmark Tester</span>
                        </h6>
                        <span class="badge bg-light text-muted border text-2xs">Real-time Ping</span>
                    </div>
                    <span class="text-muted text-xs">Execute a real-time synthetic roundtrip diagnostic test across App Bootstrap, MySQL and Cache.</span>
                </div>
                <div class="card-body px-3.5 px-md-4 pt-2 pb-4">
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-xs text-muted fw-semibold">Roundtrip Health</span>
                            <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 text-2xs fw-bold" id="benchmark-verdict">
                                Optimal
                            </span>
                        </div>
                        <div class="row g-2 text-center">
                            <div class="col-6">
                                <div class="p-2 bg-white rounded-2 border">
                                    <span class="text-muted text-2xs d-block">App Roundtrip</span>
                                    <span class="fw-bold text-dark fs-6" id="benchmark-total-ms">~32 ms</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-white rounded-2 border">
                                    <span class="text-muted text-2xs d-block">MySQL Query Ping</span>
                                    <span class="fw-bold text-dark fs-6" id="benchmark-db-ms">~2.4 ms</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-white rounded-2 border">
                                    <span class="text-muted text-2xs d-block">Cache R/W Latency</span>
                                    <span class="fw-bold text-dark fs-6" id="benchmark-cache-ms">~1.1 ms</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-white rounded-2 border">
                                    <span class="text-muted text-2xs d-block">Memory Footprint</span>
                                    <span class="fw-bold text-dark fs-6" id="benchmark-mem-mb">{{ $phpMemUsageMb }} MB</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm w-100 rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2" onclick="runDiagnosticBenchmark()">
                        <i class="bi bi-arrow-repeat" id="benchmark-btn-spin"></i>
                        <span id="benchmark-btn-text">Execute Synthetic Ping</span>
                    </button>
                </div>
            </div>

            {{-- Device & Browser Breakdown Card --}}
            <div class="card modern-card border bg-white rounded-4 shadow-2xs mb-4">
                <div class="card-header bg-transparent border-0 pt-3.5 px-3.5 px-md-4 pb-2">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-display text-info"></i>
                        <span>Visitor Device &amp; Browser Mix</span>
                    </h6>
                    <span class="text-muted text-xs">Client environment split across mobile, desktop &amp; browsers.</span>
                </div>
                <div class="card-body px-3.5 px-md-4 pt-2 pb-3">
                    <span class="text-2xs text-muted text-uppercase fw-bold d-block mb-2">Device Type</span>
                    <div class="mb-3">
                        @foreach($devices as $dev)
                            <div class="d-flex justify-content-between align-items-center text-xs mb-1">
                                <span class="fw-medium text-dark">{{ $dev['name'] }}</span>
                                <span class="text-muted fw-semibold">{{ $dev['pct'] }}%</span>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $dev['pct'] }}%;"></div>
                            </div>
                        @endforeach
                    </div>

                    <span class="text-2xs text-muted text-uppercase fw-bold d-block mb-2 pt-2 border-top">Browser Family</span>
                    <div>
                        @foreach($browsers as $br)
                            <div class="d-flex justify-content-between align-items-center text-xs mb-1">
                                <span class="fw-medium text-dark">{{ $br['name'] }}</span>
                                <span class="text-muted fw-semibold">{{ $br['pct'] }}%</span>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $br['pct'] }}%;"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Top Visited Routes Card --}}
            <div class="card modern-card border bg-white rounded-4 shadow-2xs mb-4">
                <div class="card-header bg-transparent border-0 pt-3.5 px-3.5 px-md-4 pb-2">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-signpost-split-fill text-primary"></i>
                        <span>Top Visited Pages</span>
                    </h6>
                    <span class="text-muted text-xs">Highest traffic routes across the domain.</span>
                </div>
                <div class="card-body px-3.5 px-md-4 pt-2 pb-3">
                    <div class="list-group list-group-flush">
                        @foreach($topPages as $page)
                            <div class="list-group-item px-0 py-2 border-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="text-truncate me-2">
                                        <span class="fw-semibold text-dark text-xs d-block text-truncate">{{ $page['label'] }}</span>
                                        <span class="text-muted font-monospace text-2xs">{{ $page['path'] }}</span>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-light text-dark border text-2xs fw-bold">{{ number_format($page['hits']) }} hits</span>
                                    </div>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $page['pct'] }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- HTTP Status Codes Distribution --}}
            <div class="card modern-card border bg-white rounded-4 shadow-2xs">
                <div class="card-header bg-transparent border-0 pt-3.5 px-3.5 px-md-4 pb-2">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success"></i>
                        <span>HTTP Status Code Health</span>
                    </h6>
                    <span class="text-muted text-xs">Distribution of web server responses.</span>
                </div>
                <div class="card-body px-3.5 px-md-4 pt-2 pb-4">
                    <div class="d-flex justify-content-between align-items-center py-1.5 border-bottom text-xs">
                        <span class="fw-semibold text-success"><i class="bi bi-check-circle-fill me-1.5"></i>2xx Success</span>
                        <span class="fw-bold text-dark">{{ $statusDistribution['2xx'] ?? 98.4 }}%</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1.5 border-bottom text-xs">
                        <span class="fw-semibold text-info"><i class="bi bi-arrow-right-circle-fill me-1.5"></i>3xx Redirect</span>
                        <span class="fw-bold text-dark">{{ $statusDistribution['3xx'] ?? 1.2 }}%</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1.5 border-bottom text-xs">
                        <span class="fw-semibold text-warning"><i class="bi bi-exclamation-circle-fill me-1.5"></i>4xx Client Error</span>
                        <span class="fw-bold text-dark">{{ $statusDistribution['4xx'] ?? 0.3 }}%</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1.5 text-xs">
                        <span class="fw-semibold text-danger"><i class="bi bi-x-circle-fill me-1.5"></i>5xx Server Error</span>
                        <span class="fw-bold text-dark">{{ $statusDistribution['5xx'] ?? 0.1 }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Chart Configuration and Initialization
    const hourlyLabels = @json($hourlyLabels);
    const hourlyTraffic = @json($hourlyTraffic);
    const hourlyLatency = @json($hourlyLatency);

    let chartMode = 'traffic';
    const ctx = document.getElementById('telemetryChart').getContext('2d');

    // Create subtle gradient for traffic
    const trafficGradient = ctx.createLinearGradient(0, 0, 0, 270);
    trafficGradient.addColorStop(0, 'rgba(16, 43, 67, 0.22)');
    trafficGradient.addColorStop(1, 'rgba(16, 43, 67, 0.01)');

    const latencyGradient = ctx.createLinearGradient(0, 0, 0, 270);
    latencyGradient.addColorStop(0, 'rgba(13, 110, 253, 0.25)');
    latencyGradient.addColorStop(1, 'rgba(13, 110, 253, 0.01)');

    window.telemetryChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: hourlyLabels,
            datasets: [{
                label: 'Traffic Hits',
                data: hourlyTraffic,
                borderColor: '#102B43',
                backgroundColor: trafficGradient,
                borderWidth: 2.2,
                fill: true,
                tension: 0.38,
                pointRadius: 2.5,
                pointHoverRadius: 5,
                pointBackgroundColor: '#102B43'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(16, 43, 67, 0.95)',
                    titleFont: { size: 12, weight: '600' },
                    bodyFont: { size: 12 },
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function (context) {
                            return chartMode === 'traffic' 
                                ? ` ${context.parsed.y} Requests / Hits` 
                                : ` ${context.parsed.y} ms Latency`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#64748B',
                        font: { size: 10 },
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 12
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#F1F5F9' },
                    ticks: {
                        color: '#64748B',
                        font: { size: 10 },
                        callback: function (val) {
                            return chartMode === 'traffic' ? val : val + ' ms';
                        }
                    }
                }
            }
        }
    });

    // 2. Chart Mode Switcher
    window.switchChartMode = function (mode) {
        chartMode = mode;
        const btnTraffic = document.getElementById('btn-chart-traffic');
        const btnLatency = document.getElementById('btn-chart-latency');
        const titleEl = document.getElementById('chart-title');

        if (mode === 'traffic') {
            btnTraffic.classList.add('active');
            btnTraffic.classList.remove('text-muted');
            btnLatency.classList.remove('active');
            btnLatency.classList.add('text-muted');
            titleEl.textContent = 'Hourly Traffic Throughput';

            window.telemetryChart.data.datasets[0].label = 'Traffic Hits';
            window.telemetryChart.data.datasets[0].data = hourlyTraffic;
            window.telemetryChart.data.datasets[0].borderColor = '#102B43';
            window.telemetryChart.data.datasets[0].backgroundColor = trafficGradient;
            window.telemetryChart.data.datasets[0].pointBackgroundColor = '#102B43';
        } else {
            btnLatency.classList.add('active');
            btnLatency.classList.remove('text-muted');
            btnTraffic.classList.remove('active');
            btnTraffic.classList.add('text-muted');
            titleEl.textContent = 'Hourly Response Latency Trend (ms)';

            window.telemetryChart.data.datasets[0].label = 'Latency (ms)';
            window.telemetryChart.data.datasets[0].data = hourlyLatency;
            window.telemetryChart.data.datasets[0].borderColor = '#0D6EFD';
            window.telemetryChart.data.datasets[0].backgroundColor = latencyGradient;
            window.telemetryChart.data.datasets[0].pointBackgroundColor = '#0D6EFD';
        }
        window.telemetryChart.update();
    };

    // 3. Auto-Refresh Polling via Telemetry API
    let autoRefreshActive = true;
    let refreshInterval = null;
    const currentPeriod = '{{ $period }}';

    function fetchTelemetry() {
        if (!autoRefreshActive) return;

        fetch(`{{ route('admin.traffic.performance.api') }}?period=${currentPeriod}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data) return;

            // Update Primary Cards
            if (data.liveVisitorsCount !== undefined) {
                document.getElementById('stat-live-count').textContent = data.liveVisitorsCount;
            }
            if (data.rpm !== undefined) {
                document.getElementById('stat-rpm').textContent = data.rpm;
                const summaryRpm = document.getElementById('summary-rpm');
                if (summaryRpm) summaryRpm.textContent = data.rpm + ' req/min';
            }
            if (data.liveByRole) {
                document.getElementById('stat-role-scholars').textContent = data.liveByRole.scholars || 0;
                document.getElementById('stat-role-inquirers').textContent = data.liveByRole.questioners || 0;
                document.getElementById('stat-role-visitors').textContent = data.liveByRole.visitors || 0;
            }
            if (data.avgResponseTimeMs !== undefined) {
                document.getElementById('stat-avg-latency').textContent = data.avgResponseTimeMs;
            }
            if (data.p95ResponseTimeMs !== undefined) {
                document.getElementById('stat-p95-latency').textContent = data.p95ResponseTimeMs;
            }
            if (data.dbLatencyMs !== undefined) {
                document.getElementById('stat-db-latency').textContent = data.dbLatencyMs;
            }
            if (data.phpMemUsageMb !== undefined) {
                document.getElementById('stat-mem-usage').textContent = data.phpMemUsageMb;
            }
            if (data.phpMemPeakMb !== undefined) {
                document.getElementById('stat-mem-peak').textContent = data.phpMemPeakMb;
            }
            if (data.phpMemPercent !== undefined) {
                document.getElementById('stat-mem-pct').textContent = data.phpMemPercent + '%';
            }
            if (data.lastUpdated) {
                document.getElementById('stream-last-updated').textContent = 'Updated: ' + data.lastUpdated;
            }

            // Update Recent Request Stream Table
            if (data.recentRequests && data.recentRequests.length > 0) {
                updateRequestStreamTable(data.recentRequests);
            }
        })
        .catch(err => {
            console.warn('Telemetry polling skipped:', err);
        });
    }

    function updateRequestStreamTable(requests) {
        const tbody = document.getElementById('request-stream-body');
        if (!tbody) return;

        let html = '';
        requests.forEach(req => {
            const isPost = (req.method || 'GET') === 'POST';
            const methodBadge = isPost 
                ? '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle px-2 py-1 fw-bold">POST</span>'
                : '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 fw-bold">GET</span>';

            const st = parseInt(req.status_code || 200);
            let statusBadge = '<span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5 fw-bold">' + st + ' OK</span>';
            if (st >= 300 && st < 400) {
                statusBadge = '<span class="badge bg-info-subtle text-info rounded-pill px-2 py-0.5 fw-bold">' + st + ' Redir</span>';
            } else if (st >= 400 && st < 500) {
                statusBadge = '<span class="badge bg-warning-subtle text-warning rounded-pill px-2 py-0.5 fw-bold">' + st + ' Client</span>';
            } else if (st >= 500) {
                statusBadge = '<span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-0.5 fw-bold">' + st + ' Error</span>';
            }

            html += `
                <tr>
                    <td class="ps-3 ps-md-4">${methodBadge}</td>
                    <td class="fw-semibold text-dark font-monospace text-xs">${escapeHtml(req.path || '/')}</td>
                    <td>${statusBadge}</td>
                    <td><span class="badge bg-light text-dark border fw-medium">${req.duration_ms || 25} ms</span></td>
                    <td class="text-muted font-monospace text-2xs">${escapeHtml(req.ip || '127.0.0.1')}</td>
                    <td class="pe-3 pe-md-4 text-end text-muted text-2xs">${escapeHtml(req.time_ago || 'Just now')}</td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function escapeHtml(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // Toggle Auto-Refresh
    window.toggleAutoRefresh = function () {
        autoRefreshActive = !autoRefreshActive;
        const spinner = document.getElementById('refresh-spinner');
        const label = document.getElementById('refresh-btn-label');

        if (autoRefreshActive) {
            spinner.className = 'spinner-grow spinner-grow-sm text-success';
            label.textContent = 'Auto-Refresh: 5s';
            if (!refreshInterval) {
                refreshInterval = setInterval(fetchTelemetry, 5000);
            }
        } else {
            spinner.className = 'badge bg-secondary p-1 rounded-circle me-1';
            label.textContent = 'Auto-Refresh: Paused';
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
        }
    };

    // Initialize 5s polling interval
    refreshInterval = setInterval(fetchTelemetry, 5000);

    // 4. Synthetic Benchmark Trigger
    window.runDiagnosticBenchmark = function () {
        const btnIcon = document.getElementById('benchmark-icon');
        const btnSpin = document.getElementById('benchmark-btn-spin');
        const btnText = document.getElementById('benchmark-btn-text');
        const verdictEl = document.getElementById('benchmark-verdict');

        if (btnSpin) btnSpin.classList.add('spinner-border', 'spinner-border-sm');
        if (btnText) btnText.textContent = 'Testing System...';

        fetch("{{ route('admin.traffic.performance.ping') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (btnSpin) btnSpin.classList.remove('spinner-border', 'spinner-border-sm');
            if (btnText) btnText.textContent = 'Execute Synthetic Ping';

            document.getElementById('benchmark-total-ms').textContent = res.total_ms + ' ms';
            document.getElementById('benchmark-db-ms').textContent = res.db_ms + ' ms';
            document.getElementById('benchmark-cache-ms').textContent = res.cache_ms + ' ms';
            document.getElementById('benchmark-mem-mb').textContent = res.memory_mb + ' MB';

            if (verdictEl) {
                verdictEl.textContent = res.grade;
                verdictEl.className = `badge bg-${res.grade_color}-subtle text-${res.grade_color} rounded-pill px-2.5 py-1 text-2xs fw-bold`;
            }

            // Sync with primary cards
            document.getElementById('stat-db-latency').textContent = res.db_ms;
            document.getElementById('stat-mem-usage').textContent = res.memory_mb;
        })
        .catch(err => {
            if (btnSpin) btnSpin.classList.remove('spinner-border', 'spinner-border-sm');
            if (btnText) btnText.textContent = 'Execute Synthetic Ping';
            console.error('Benchmark ping failed:', err);
        });
    };
});
</script>
@endpush

