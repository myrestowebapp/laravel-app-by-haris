@extends('layouts.admin')
@section('title', 'Global Visitor Intelligence & 3D Earth Map')
@section('topbar-title', 'Global Visitor Intelligence')
@section('topbar-icon', 'bi-globe-americas')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <span class="text-dark fw-semibold">Visitor Insights</span>
@endsection

@section('content')
{{-- ═══════════════════════════════════════════════════════════════
     TIER 1: PAGE HEADER & TIME HORIZON FILTERS
═══════════════════════════════════════════════════════════════ --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="visitor-header-card">
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="globe-title-icon-box flex-shrink-0">
                        <i class="bi bi-globe2 fs-3 text-primary"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h4 class="fw-bold mb-0 text-dark">Global Visitor Intelligence &amp; 3D Earth Map</h4>
                            <span class="badge live-telemetry-badge">
                                <span class="pulse-dot-green"></span> Live Telemetry Active
                            </span>
                        </div>
                        <p class="mb-0 text-muted small mt-1">
                            Geographic telemetry breakdown across sovereign nations, administrative regions, states, and reading hubs worldwide.
                        </p>
                    </div>
                </div>

                {{-- Horizon Filter Toggle Buttons --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="horizon-filter-group p-1 bg-white rounded-pill border d-inline-flex shadow-xs" id="horizonFilterGroup">
                        <button type="button" class="btn btn-sm rounded-pill horizon-btn {{ $period === 'today' ? 'active' : '' }}" data-period="today">Today</button>
                        <button type="button" class="btn btn-sm rounded-pill horizon-btn {{ $period === '7d' ? 'active' : '' }}" data-period="7d">Last 7 Days</button>
                        <button type="button" class="btn btn-sm rounded-pill horizon-btn {{ $period === '30d' ? 'active' : '' }}" data-period="30d">Last 30 Days</button>
                        <button type="button" class="btn btn-sm rounded-pill horizon-btn {{ $period === 'all' ? 'active' : '' }}" data-period="all">All-Time</button>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center gap-1.5 shadow-xs" id="btnRefreshTelemetry" title="Refresh Telemetry">
                        <i class="bi bi-arrow-clockwise" id="refreshSpinner"></i>
                        <span>Sync</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     TIER 2: REAL-TIME TELEMETRY METRIC KPI CARDS (4 Cards)
═══════════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    {{-- Card 1: Total Platform Reach --}}
    <div class="col-xl-3 col-sm-6">
        <div class="telemetry-kpi-card">
            <div class="kpi-icon-wrapper bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-people-fill fs-4"></i>
            </div>
            <div class="min-w-0">
                <div class="kpi-value text-dark" id="kpiTotalVisitors">{{ number_format($summary['total_visitors']) }} <span class="text-xs fw-normal text-muted">Visitors</span></div>
                <div class="kpi-label">Total Platform Reach</div>
                <div class="kpi-subtext text-muted">100% of global inquiry readership</div>
            </div>
        </div>
    </div>

    {{-- Card 2: Worldwide Coverage (Countries & States) --}}
    <div class="col-xl-3 col-sm-6">
        <div class="telemetry-kpi-card">
            <div class="kpi-icon-wrapper bg-indigo bg-opacity-10 text-indigo" style="color: #6366F1; background: rgba(99, 102, 241, 0.1);">
                <i class="bi bi-flag-fill fs-4"></i>
            </div>
            <div class="min-w-0">
                <div class="kpi-value text-dark" id="kpiCountriesCount">{{ $summary['unique_countries'] }} Nations</div>
                <div class="kpi-label">Worldwide Coverage</div>
                <div class="kpi-subtext text-muted" id="kpiRegionsCount">{{ $summary['unique_regions'] ?? 17 }} Active States &amp; Regions</div>
            </div>
        </div>
    </div>

    {{-- Card 3: Primary Geographic Hub (Country & Top State) --}}
    <div class="col-xl-3 col-sm-6">
        <div class="telemetry-kpi-card">
            <div class="kpi-icon-wrapper bg-success bg-opacity-10 text-success">
                <i class="bi bi-geo-alt-fill fs-4"></i>
            </div>
            <div class="min-w-0">
                <div class="kpi-value text-dark text-truncate" id="kpiTopCountry">
                    {{ $summary['top_country'] }}: {{ number_format($summary['top_country_count']) }} ({{ $summary['top_country_percentage'] }}%)
                </div>
                <div class="kpi-label">Primary Geographic Hub</div>
                <div class="kpi-subtext text-muted text-truncate" id="kpiTopRegion">
                    Top State: {{ $summary['top_region'] }} ({{ number_format($summary['top_region_count']) }} • {{ $summary['top_region_percentage'] }}%)
                </div>
            </div>
        </div>
    </div>

    {{-- Card 4: Active Readers Right Now --}}
    <div class="col-xl-3 col-sm-6">
        <div class="telemetry-kpi-card">
            <div class="kpi-icon-wrapper bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-broadcast fs-4"></i>
            </div>
            <div class="min-w-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="kpi-value text-dark" id="kpiLiveVisitors">{{ $summary['live_visitors'] }} Active</span>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-1 px-2 text-xs">Live</span>
                </div>
                <div class="kpi-label">Active Readers Right Now</div>
                <div class="kpi-subtext text-muted">Real-time sliding 5-min window</div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     TIER 3: THE INTERACTIVE 3D EARTH GLOBE SHOWCASE
═══════════════════════════════════════════════════════════════ --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="globe-cosmos-card position-relative overflow-hidden">
            {{-- Top Control Overlay Bar --}}
            <div class="globe-top-bar d-flex flex-wrap align-items-center justify-content-between p-3 px-4 position-absolute top-0 start-0 w-100 z-3">
                {{-- HUD Indicators & Layer Selector --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    {{-- Map View Layer Toggle: States/Regions vs Countries vs All --}}
                    <div class="btn-group btn-group-sm map-layer-toggle p-0.5 rounded-pill" style="background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(148, 163, 184, 0.3);">
                        <button type="button" class="btn btn-xs rounded-pill layer-btn active" data-layer="regions" title="View individual State and Regional hubs">
                            <i class="bi bi-geo-fill me-1 text-success"></i>States &amp; Regions
                        </button>
                        <button type="button" class="btn btn-xs rounded-pill layer-btn" data-layer="countries" title="View aggregated Sovereign Nation totals">
                            <i class="bi bi-flag-fill me-1 text-primary"></i>Countries
                        </button>
                        <button type="button" class="btn btn-xs rounded-pill layer-btn" data-layer="all" title="View both Country and State markers">
                            <i class="bi bi-layers-fill me-1 text-warning"></i>Combined
                        </button>
                    </div>

                    <span class="badge globe-hud-pill d-none d-md-inline-flex">
                        <i class="bi bi-compass me-1 text-info"></i>
                        <span id="globeCoordinates">Focus: 10.9°N, 76.3°E (Kerala)</span>
                    </span>
                    <span class="badge globe-hud-pill d-none d-lg-inline-flex">
                        <i class="bi bi-zoom-in me-1 text-info"></i>
                        <span id="globeZoomLevel">Zoom: 1.0x</span>
                    </span>
                </div>

                {{-- Action Controls: Auto-spin, Reset, Zoom --}}
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm globe-btn-control" id="btnToggleSpin" title="Toggle Auto-Spin">
                        <i class="bi bi-arrow-repeat me-1"></i>
                        <span id="spinLabel">Auto-Spin: ON</span>
                    </button>
                    <button type="button" class="btn btn-sm globe-btn-control" id="btnResetView" title="Reset Camera to Kerala &amp; Middle East">
                        <i class="bi bi-crosshair"></i>
                        <span class="d-none d-md-inline ms-1">Reset View</span>
                    </button>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn globe-btn-control" id="btnZoomIn" title="Zoom In">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                        <button type="button" class="btn globe-btn-control" id="btnZoomOut" title="Zoom Out">
                            <i class="bi bi-dash-lg"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- 3D Globe Canvas / SVG Container --}}
            <div class="globe-stage-wrapper position-relative" id="globeStageWrapper">
                <div id="globeContainer" class="d-flex align-items-center justify-content-center"></div>

                {{-- Interactive Floating Rich Tooltip --}}
                <div id="globeTooltip" class="globe-floating-tooltip" style="display: none;">
                    <div class="tooltip-header d-flex align-items-center gap-2">
                        <span class="tooltip-flag fs-4" id="ttFlag">🌍</span>
                        <div>
                            <div class="tooltip-country fw-bold text-white fs-6" id="ttLocation">Kerala, India</div>
                            <div class="tooltip-type-badge d-inline-block text-xs font-mono" id="ttTypeBadge">State / Region</div>
                        </div>
                    </div>
                    <div class="tooltip-body mt-2 pt-2 border-top border-secondary border-opacity-25">
                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                            <span class="text-secondary small fw-semibold">Number of Visitors:</span>
                            <span class="fw-bold text-warning fs-6" id="ttVisits">12,650</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-secondary small">Global Platform Share:</span>
                            <span class="fw-bold text-info" id="ttPercent">45.0%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1" id="ttCountryShareRow">
                            <span class="text-secondary small">Country Share:</span>
                            <span class="fw-bold text-success" id="ttCountryPercent">77.6% of India</span>
                        </div>
                        <div class="tooltip-regions mt-2 pt-1.5 border-top border-secondary border-opacity-25" id="ttCitiesRow">
                            <div class="text-secondary text-xs mb-1">Top Reading Urban Centres:</div>
                            <div class="text-light small text-truncate" id="ttCities">Calicut, Malappuram, Kochi</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom Control Bar: Legend & Quick Jumps --}}
            <div class="globe-bottom-bar d-flex flex-wrap align-items-center justify-content-between p-3 px-4 position-absolute bottom-0 start-0 w-100 z-3">
                {{-- Legend matching reference mockup --}}
                <div class="globe-legend d-flex align-items-center gap-3">
                    <span class="text-secondary text-xs fw-semibold text-uppercase d-none d-md-inline" style="letter-spacing: 0.5px;">Visitor Scale:</span>
                    <div class="d-inline-flex align-items-center gap-1.5 text-xs text-light">
                        <span class="legend-bubble bubble-high"></span>
                        <span>High Volume (>5,000)</span>
                    </div>
                    <div class="d-inline-flex align-items-center gap-1.5 text-xs text-light">
                        <span class="legend-bubble bubble-med"></span>
                        <span>Medium Volume (500–5,000)</span>
                    </div>
                    <div class="d-inline-flex align-items-center gap-1.5 text-xs text-light">
                        <span class="legend-bubble bubble-low"></span>
                        <span>Emerging (<500)</span>
                    </div>
                </div>

                {{-- Quick Region Jump Buttons --}}
                <div class="globe-quick-jumps d-flex align-items-center gap-1 flex-wrap">
                    <span class="text-secondary text-xs me-1 d-none d-lg-inline">Quick View:</span>
                    <button type="button" class="btn btn-xs globe-jump-btn" data-center="76.3,10.9">Kerala &amp; India</button>
                    <button type="button" class="btn btn-xs globe-jump-btn" data-center="55.3,25.2">Gulf &amp; Middle East</button>
                    <button type="button" class="btn btn-xs globe-jump-btn" data-center="10.0,50.0">Europe &amp; UK</button>
                    <button type="button" class="btn btn-xs globe-jump-btn" data-center="101.7,3.1">Southeast Asia</button>
                    <button type="button" class="btn btn-xs globe-jump-btn" data-center="-98.0,39.0">Americas</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     TIER 4: SYNCHRONIZED REGIONAL DRILLDOWN TABLES
═══════════════════════════════════════════════════════════════ --}}
<div class="row g-4">
    {{-- Top Countries Table --}}
    <div class="col-xl-4 col-lg-6">
        <div class="drilldown-table-card h-100">
            <div class="drilldown-card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-flag-fill text-primary"></i>
                    <h6 class="fw-bold mb-0 text-dark">Top Sovereign Nations</h6>
                </div>
                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 text-xs" id="tableCountryCount">
                    {{ $countries->total() }} Nations
                </span>
            </div>
            <div class="table-responsive">
                <table class="table modern-table align-middle mb-0" id="countriesTable">
                    <thead>
                        <tr>
                            <th class="ps-4">Nation</th>
                            <th>Code</th>
                            <th class="text-end pe-4">Visitors (Count &amp; % Share)</th>
                        </tr>
                    </thead>
                    <tbody id="countriesTableBody">
                        @foreach($countries as $c)
                        <tr class="country-row" data-code="{{ $c->country_code }}" data-lng="{{ $c->lng ?? 0 }}" data-lat="{{ $c->lat ?? 0 }}" style="cursor: pointer;" title="Click to center 3D Earth globe on {{ $c->country }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="country-flag-icon fs-6">{{ $c->flag ?? '🌍' }}</span>
                                    <span class="fw-bold text-dark country-name-link">{{ $c->country }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary font-mono border text-xs">{{ $c->country_code }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex align-items-baseline justify-content-end gap-1.5">
                                    <span class="fw-bold text-primary">{{ number_format($c->count) }}</span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary py-0.5 px-1.5 text-xs">{{ $c->percentage }}%</span>
                                </div>
                                <div class="progress mt-1" style="height: 4px; background: #E2E8F0; width: 95px; margin-left: auto;">
                                    <div class="progress-bar bg-primary" style="width: {{ min(100, $c->percentage ?? 5) }}%;"></div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="drilldown-card-footer border-top bg-white p-3">
                <div class="d-flex justify-content-center custom-pagination">
                    {{ $countries->appends(request()->except('country_page'))->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Major Regions & States Table (Showing Number of Visitors & Percentages) --}}
    <div class="col-xl-4 col-lg-6">
        <div class="drilldown-table-card h-100">
            <div class="drilldown-card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-geo-fill text-success"></i>
                    <h6 class="fw-bold mb-0 text-dark">Major Regions &amp; States</h6>
                </div>
                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 text-xs" id="tableRegionCount">
                    {{ $regions->total() }} Regions / States
                </span>
            </div>
            <div class="table-responsive">
                <table class="table modern-table align-middle mb-0" id="regionsTable">
                    <thead>
                        <tr>
                            <th class="ps-4">Region / State</th>
                            <th>Country</th>
                            <th class="text-end pe-4">Visitors (Count &amp; % Share)</th>
                        </tr>
                    </thead>
                    <tbody id="regionsTableBody">
                        @foreach($regions as $r)
                        <tr class="region-row" data-lng="{{ $r->lng ?? 0 }}" data-lat="{{ $r->lat ?? 0 }}" style="cursor: pointer;" title="Click to center 3D Earth globe on {{ $r->region }}">
                            <td class="ps-4">
                                <div class="fw-bold text-dark region-name-link">{{ $r->region }}</div>
                                <div class="text-muted text-xs">{{ $r->sub_label ?? 'Regional State' }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1.5">
                                    <span>{{ $r->flag ?? '🌍' }}</span>
                                    <span class="text-muted small">{{ $r->country }}</span>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex align-items-baseline justify-content-end gap-1.5">
                                    <span class="fw-bold text-success">{{ number_format($r->count) }}</span>
                                    <span class="badge bg-success bg-opacity-10 text-success py-0.5 px-1.5 text-xs">{{ $r->percentage }}%</span>
                                </div>
                                <div class="text-muted text-xs mt-0.5">
                                    {{ $r->country_percentage ?? $r->percentage }}% of {{ $r->country }}
                                </div>
                                <div class="progress mt-1" style="height: 4px; background: #E2E8F0; width: 95px; margin-left: auto;">
                                    <div class="progress-bar bg-success" style="width: {{ min(100, $r->percentage * 2) }}%;"></div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="drilldown-card-footer border-top bg-white p-3">
                <div class="d-flex justify-content-center custom-pagination">
                    {{ $regions->appends(request()->except('region_page'))->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Urban Centres (Cities) Table --}}
    <div class="col-xl-4 col-lg-12">
        <div class="drilldown-table-card h-100">
            <div class="drilldown-card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-buildings-fill text-indigo" style="color: #6366F1;"></i>
                    <h6 class="fw-bold mb-0 text-dark">Urban Centres &amp; Cities</h6>
                </div>
                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 text-xs" id="tableCityCount">
                    {{ $cities->total() }} Cities
                </span>
            </div>
            <div class="table-responsive">
                <table class="table modern-table align-middle mb-0" id="citiesTable">
                    <thead>
                        <tr>
                            <th class="ps-4">City</th>
                            <th>State / Country</th>
                            <th class="text-end pe-4">Visitors (Count &amp; % Share)</th>
                        </tr>
                    </thead>
                    <tbody id="citiesTableBody">
                        @foreach($cities as $ct)
                        <tr class="city-row" data-lng="{{ $ct->lng ?? 0 }}" data-lat="{{ $ct->lat ?? 0 }}" style="cursor: pointer;" title="Click to center 3D Earth globe on {{ $ct->city }}">
                            <td class="ps-4">
                                <div class="fw-bold text-dark city-name-link">{{ $ct->city }}</div>
                            </td>
                            <td>
                                <div class="text-muted small text-truncate" style="max-width: 140px;">
                                    {{ $ct->region ?? '' }}{{ (!empty($ct->region) && !empty($ct->country)) ? ', ' : '' }}{{ $ct->country ?? ($ct->location ?? '') }}
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex align-items-baseline justify-content-end gap-1.5">
                                    <span class="fw-bold text-indigo" style="color: #6366F1;">{{ number_format($ct->count) }}</span>
                                    <span class="badge bg-indigo bg-opacity-10 text-indigo py-0.5 px-1.5 text-xs" style="color: #6366F1; background: rgba(99, 102, 241, 0.1);">{{ $ct->percentage ?? '' }}%</span>
                                </div>
                                <div class="progress mt-1" style="height: 4px; background: #E2E8F0; width: 95px; margin-left: auto;">
                                    <div class="progress-bar" style="background: #6366F1; width: {{ min(100, ($ct->percentage ?? 5) * 3) }}%;"></div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="drilldown-card-footer border-top bg-white p-3">
                <div class="d-flex justify-content-center custom-pagination">
                    {{ $cities->appends(request()->except('city_page'))->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* ── Visitor Header Card ── */
    .visitor-header-card {
        padding: 1.5rem 1.75rem !important;
        background: #ffffff !important;
        border-radius: 14px !important;
        border: 1px solid var(--card-border, #E2E8F0) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
    .globe-title-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: #EFF6FF;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .live-telemetry-badge {
        background: #ECFDF5;
        color: #059669;
        font-weight: 700;
        font-size: 0.76rem;
        border-radius: 20px;
        padding: 5px 12px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid #A7F3D0;
    }
    .pulse-dot-green {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #10B981;
        display: inline-block;
        animation: pulseGreen 1.8s infinite;
    }
    @keyframes pulseGreen {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    /* ── Horizon Filter Pill Group ── */
    .horizon-btn {
        font-size: 0.78rem;
        font-weight: 600;
        padding: 5px 14px;
        color: #64748B;
        border: none;
        transition: all 0.15s ease;
    }
    .horizon-btn:hover {
        color: #1E293B;
        background: #F1F5F9;
    }
    .horizon-btn.active {
        background: #2563EB !important;
        color: #ffffff !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.3);
    }

    /* ── Telemetry KPI Cards ── */
    .telemetry-kpi-card {
        padding: 1.25rem 1.35rem !important;
        border-radius: 14px !important;
        background: #ffffff !important;
        border: 1px solid var(--card-border, #E2E8F0) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        display: flex;
        align-items: center;
        gap: 1rem;
        height: 100%;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .telemetry-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.04);
    }
    .kpi-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .kpi-value {
        font-size: 1.26rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .kpi-label {
        font-size: 0.82rem;
        font-weight: 700;
        color: #334155;
        margin-top: 2px;
    }
    .kpi-subtext {
        font-size: 0.72rem;
        margin-top: 1px;
    }

    /* ── Deep Cosmos 3D Globe Card ── */
    .globe-cosmos-card {
        border-radius: 16px !important;
        background: radial-gradient(circle at 50% 50%, #0F172A 0%, #080D1A 100%) !important;
        border: 1px solid #1E293B !important;
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.45);
        min-height: 600px;
    }
    .globe-stage-wrapper {
        min-height: 600px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: grab;
    }
    .globe-stage-wrapper:active {
        cursor: grabbing;
    }

    /* ── Layer Selector & Controls ── */
    .map-layer-toggle .layer-btn {
        color: #94A3B8;
        font-weight: 600;
        padding: 4px 12px;
        border: none;
        background: transparent;
        transition: all 0.15s ease;
    }
    .map-layer-toggle .layer-btn:hover {
        color: #F8FAFC;
    }
    .map-layer-toggle .layer-btn.active {
        background: #2563EB !important;
        color: #ffffff !important;
        box-shadow: 0 1px 4px rgba(37, 99, 235, 0.4);
    }

    .globe-hud-pill {
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(8px);
        color: #E2E8F0;
        border: 1px solid rgba(148, 163, 184, 0.2);
        padding: 6px 12px;
        font-size: 0.76rem;
        border-radius: 20px;
        font-weight: 600;
    }
    .globe-btn-control {
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(8px);
        color: #E2E8F0;
        border: 1px solid rgba(148, 163, 184, 0.2);
        border-radius: 8px;
        padding: 5px 11px;
        font-size: 0.76rem;
        font-weight: 600;
        transition: all 0.15s ease;
    }
    .globe-btn-control:hover {
        background: rgba(30, 41, 59, 0.95);
        color: #ffffff;
        border-color: rgba(56, 189, 248, 0.4);
    }
    .globe-btn-control.active {
        background: #2563EB;
        color: #ffffff;
        border-color: #3B82F6;
    }

    /* ── Legend Bubbles ── */
    .legend-bubble {
        display: inline-block;
        border-radius: 50%;
        border: 1.5px solid rgba(20, 20, 20, 0.7);
        flex-shrink: 0;
    }
    .bubble-high {
        width: 14px;
        height: 14px;
        background: #E55A42;
    }
    .bubble-med {
        width: 11px;
        height: 11px;
        background: #F97316;
    }
    .bubble-low {
        width: 8px;
        height: 8px;
        background: #FB923C;
    }

    .globe-jump-btn {
        background: rgba(30, 41, 59, 0.6);
        color: #CBD5E1;
        border: 1px solid rgba(148, 163, 184, 0.2);
        border-radius: 6px;
        font-size: 0.7rem;
        padding: 3px 8px;
        font-weight: 600;
        transition: all 0.15s ease;
    }
    .globe-jump-btn:hover {
        background: #2563EB;
        color: #ffffff;
        border-color: #3B82F6;
    }

    /* ── Floating Tooltip on Globe ── */
    .globe-floating-tooltip {
        position: absolute;
        z-index: 1000;
        pointer-events: none;
        background: rgba(15, 23, 42, 0.95);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(56, 189, 248, 0.35);
        border-radius: 12px;
        padding: 14px 18px;
        min-width: 240px;
        box-shadow: 0 12px 30px -5px rgba(0, 0, 0, 0.7);
        transform: translate(-50%, -125%);
        transition: opacity 0.12s ease;
    }
    .tooltip-type-badge {
        color: #94A3B8;
        font-size: 0.72rem;
        background: rgba(255, 255, 255, 0.08);
        padding: 2px 8px;
        border-radius: 10px;
    }

    /* ── Drilldown Tables ── */
    .drilldown-table-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid var(--card-border, #E2E8F0);
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
    .drilldown-card-header {
        padding: 1.15rem 1.5rem !important;
        background: #ffffff;
        border-bottom: 1px solid #E2E8F0;
    }
    .country-row:hover, .region-row:hover, .city-row:hover {
        background: #F8FAFC !important;
    }
    .country-row.highlighted, .region-row.highlighted {
        background: #EFF6FF !important;
        border-left: 3px solid #2563EB;
    }
    .country-name-link, .region-name-link, .city-name-link {
        transition: color 0.15s ease;
    }
    .country-row:hover .country-name-link, .region-row:hover .region-name-link, .city-row:hover .city-name-link {
        color: #2563EB !important;
    }

    /* ── Typography & Pagination ── */
    .text-xs { font-size: 0.74rem !important; }
    .font-mono { font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace; }
    .custom-pagination .pagination { margin-bottom: 0; }
    .custom-pagination .page-link {
        border-radius: 8px !important;
        margin: 0 2px;
        border: none;
        background: #F8FAFC;
        color: #334155;
        font-weight: 600;
        font-size: 0.75rem;
    }
    .custom-pagination .page-item.active .page-link {
        background: #2563EB;
        color: #ffffff;
    }

    /* ── SVG Globe Styles ── */
    .globe-ocean {
        fill: url(#oceanGradient);
        filter: drop-shadow(0 0 35px rgba(56, 189, 248, 0.3));
    }
    .globe-graticule {
        fill: none;
        stroke: rgba(255, 255, 255, 0.15);
        stroke-width: 0.6px;
    }
    .globe-land {
        fill: #E5EDE1;
        stroke: #A8BCA8;
        stroke-width: 0.7px;
        transition: fill 0.2s ease;
    }
    .globe-borders {
        fill: none;
        stroke: #9FB39F;
        stroke-width: 0.5px;
    }
    .globe-shade {
        fill: url(#sphereShade);
        pointer-events: none;
    }
    .geo-text-label {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 9.5px;
        font-weight: 600;
        fill: #475569;
        text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8), 0 -1px 2px rgba(255, 255, 255, 0.8);
        pointer-events: none;
        user-select: none;
    }
    .geo-water-label {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 9px;
        font-style: italic;
        font-weight: 500;
        fill: rgba(30, 64, 110, 0.65);
        pointer-events: none;
        user-select: none;
    }

    /* ── Circular Bubble Markers (Matching reference screenshot) ── */
    .bubble-circle {
        stroke: rgba(20, 20, 20, 0.85);
        stroke-width: 1.4px;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.35));
        transition: transform 0.15s ease, stroke-width 0.15s ease;
        cursor: pointer;
    }
    .bubble-circle:hover {
        transform: scale(1.18);
        stroke-width: 2.2px;
    }
    .bubble-text {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-weight: 800;
        fill: #ffffff;
        text-anchor: middle;
        dominant-baseline: central;
        pointer-events: none;
        user-select: none;
    }
    .pulse-ring-anchor {
        animation: pulseBubbleRing 2.2s infinite ease-out;
        transform-origin: center;
        pointer-events: none;
    }
    @keyframes pulseBubbleRing {
        0% { r: 18; opacity: 0.9; stroke: #F59E0B; }
        100% { r: 38; opacity: 0; stroke: #F59E0B; }
    }
</style>
@endpush

@push('scripts')
{{-- Load D3.js and TopoJSON locally from /assets/js/ with CDN fallback --}}
<script src="{{ asset('assets/js/d3.v7.min.js') }}"></script>
<script src="{{ asset('assets/js/topojson-client.min.js') }}"></script>

<script>
    // Initial server telemetry payload
    let currentTelemetry = {!! $initialGlobeData !!};
    let currentPeriod = '{{ $period }}';
    let currentLayer = 'regions'; // 'regions' (States & Regions), 'countries', or 'all'

    // Geographic prominent labels matching reference screenshot
    const geographicLabels = [
        { name: "Kerala", lng: 76.27, lat: 10.85, type: 'land' },
        { name: "India", lng: 78.96, lat: 20.59, type: 'land' },
        { name: "Arabian Sea", lng: 65.0, lat: 15.5, type: 'water' },
        { name: "Indian Ocean", lng: 76.0, lat: -6.0, type: 'water' },
        { name: "Dubai", lng: 55.27, lat: 25.20, type: 'land' },
        { name: "Riyadh", lng: 46.67, lat: 24.71, type: 'land' },
        { name: "Saudi Arabia", lng: 45.07, lat: 24.5, type: 'land' },
        { name: "Egypt", lng: 30.80, lat: 26.8, type: 'land' },
        { name: "Kazakhstan", lng: 66.92, lat: 48.0, type: 'land' },
        { name: "Mongolia", lng: 103.8, lat: 46.8, type: 'land' },
        { name: "China", lng: 104.19, lat: 35.8, type: 'land' },
        { name: "Russia", lng: 90.0, lat: 60.0, type: 'land' },
        { name: "Sweden", lng: 18.64, lat: 60.1, type: 'land' },
        { name: "United Kingdom", lng: -1.5, lat: 54.0, type: 'land' },
        { name: "France", lng: 2.21, lat: 46.2, type: 'land' },
        { name: "Italy", lng: 12.56, lat: 41.8, type: 'land' },
        { name: "Turkey", lng: 35.24, lat: 39.0, type: 'land' },
        { name: "Iran", lng: 53.68, lat: 32.4, type: 'land' },
        { name: "Pakistan", lng: 69.34, lat: 30.3, type: 'land' },
    ];

    // Globe State Variables
    let width = 760;
    let height = 560;
    let initialScale = 250;
    let scale = initialScale;
    let rotation = [-76, -12, 0]; // Centered directly on Kerala & South Asia / Middle East
    let isAutoSpinning = true;
    let spinSpeed = 0.12;
    let animationTimer = null;
    let worldTopology = null;

    // Elements
    const container = d3.select("#globeContainer");
    const tooltip = d3.select("#globeTooltip");
    const coordDisplay = document.getElementById("globeCoordinates");
    const zoomDisplay = document.getElementById("globeZoomLevel");

    // Responsive dimensions
    function updateDimensions() {
        const wrapper = document.getElementById("globeStageWrapper");
        if (wrapper) {
            width = Math.min(wrapper.clientWidth, 920);
            height = Math.max(520, Math.min(620, Math.round(width * 0.72)));
            initialScale = Math.round(height * 0.44);
            if (scale === 250) scale = initialScale;
        }
    }
    updateDimensions();

    // 1. Setup SVG & Definitions
    const svg = container.append("svg")
        .attr("width", width)
        .attr("height", height)
        .attr("viewBox", `0 0 ${width} ${height}`)
        .style("overflow", "visible");

    const defs = svg.append("defs");

    // Atmosphere Glow Filter
    const filter = defs.append("filter")
        .attr("id", "globeGlow")
        .attr("x", "-20%")
        .attr("y", "-20%")
        .attr("width", "140%")
        .attr("height", "140%");
    filter.append("feGaussianBlur")
        .attr("stdDeviation", "18")
        .attr("result", "blur");

    // Ocean Gradient (Cyan Sky Blue like reference mockup)
    const oceanGrad = defs.append("radialGradient")
        .attr("id", "oceanGradient")
        .attr("cx", "42%")
        .attr("cy", "38%")
        .attr("r", "65%");
    oceanGrad.append("stop").attr("offset", "0%").attr("stop-color", "#A7DBF7");
    oceanGrad.append("stop").attr("offset", "70%").attr("stop-color", "#78BFE6");
    oceanGrad.append("stop").attr("offset", "100%").attr("stop-color", "#4B94C2");

    // Spherical Shading Gradient (gives depth)
    const shadeGrad = defs.append("radialGradient")
        .attr("id", "sphereShade")
        .attr("cx", "70%")
        .attr("cy", "70%")
        .attr("r", "75%");
    shadeGrad.append("stop").attr("offset", "0%").attr("stop-color", "rgba(0,0,0,0)");
    shadeGrad.append("stop").attr("offset", "65%").attr("stop-color", "rgba(5, 15, 35, 0.15)");
    shadeGrad.append("stop").attr("offset", "100%").attr("stop-color", "rgba(2, 6, 18, 0.65)");

    // Atmosphere Rim Glow Ring
    const rimGrad = defs.append("radialGradient")
        .attr("id", "atmosphereRim")
        .attr("cx", "50%")
        .attr("cy", "50%")
        .attr("r", "50%");
    rimGrad.append("stop").attr("offset", "84%").attr("stop-color", "#38BDF8").attr("stop-opacity", "0");
    rimGrad.append("stop").attr("offset", "96%").attr("stop-color", "#38BDF8").attr("stop-opacity", "0.28");
    rimGrad.append("stop").attr("offset", "100%").attr("stop-color", "#7DD3FC").attr("stop-opacity", "0.4");

    // 2. Projection & Path Generator
    const projection = d3.geoOrthographic()
        .scale(scale)
        .translate([width / 2, height / 2])
        .rotate(rotation)
        .clipAngle(90);

    const path = d3.geoPath().projection(projection);
    const graticule = d3.geoGraticule10();

    // Layers (Order matters!)
    const atmosphereHalo = svg.append("circle")
        .attr("class", "atmosphere-halo")
        .attr("cx", width / 2)
        .attr("cy", height / 2)
        .attr("r", scale + 14)
        .attr("fill", "url(#atmosphereRim)")
        .attr("filter", "url(#globeGlow)")
        .attr("pointer-events", "none");

    const oceanSphere = svg.append("circle")
        .attr("class", "globe-ocean")
        .attr("cx", width / 2)
        .attr("cy", height / 2)
        .attr("r", scale);

    const graticulePath = svg.append("path")
        .datum(graticule)
        .attr("class", "globe-graticule")
        .attr("d", path);

    const landGroup = svg.append("g").attr("class", "land-group");
    const borderGroup = svg.append("g").attr("class", "borders-group");
    const labelsGroup = svg.append("g").attr("class", "labels-group");
    const markersGroup = svg.append("g").attr("class", "markers-group");

    const shadeOverlay = svg.append("circle")
        .attr("class", "globe-shade")
        .attr("cx", width / 2)
        .attr("cy", height / 2)
        .attr("r", scale);

    // 3. Load World Topology Data
    fetch("{{ asset('assets/data/world-110m.json') }}")
        .then(res => res.json())
        .then(world => {
            worldTopology = world;
            renderWorld();
            renderMarkers();
            startSpin();
        })
        .catch(err => {
            console.warn("Local world-110m fetch failed, loading CDN backup...", err);
            d3.json("https://cdn.jsdelivr.net/npm/world-atlas@2/countries-110m.json")
                .then(world => {
                    worldTopology = world;
                    renderWorld();
                    renderMarkers();
                    startSpin();
                });
        });

    function renderWorld() {
        if (!worldTopology) return;

        const land = topojson.feature(worldTopology, worldTopology.objects.land);
        const borders = topojson.mesh(worldTopology, worldTopology.objects.countries, (a, b) => a !== b);

        landGroup.selectAll("path")
            .data([land])
            .join("path")
            .attr("class", "globe-land")
            .attr("d", path);

        borderGroup.selectAll("path")
            .data([borders])
            .join("path")
            .attr("class", "globe-borders")
            .attr("d", path);

        renderLabels();
    }

    function renderLabels() {
        // Visible Geographic Labels
        const visibleLabels = geographicLabels.filter(lbl => {
            const center = [-rotation[0], -rotation[1]];
            const dist = d3.geoDistance([lbl.lng, lbl.lat], center);
            return dist < (Math.PI / 2) * 0.92;
        });

        const labels = labelsGroup.selectAll("text")
            .data(visibleLabels, d => d.name)
            .join("text")
            .attr("class", d => d.type === 'water' ? 'geo-water-label' : 'geo-text-label')
            .attr("x", d => projection([d.lng, d.lat])[0])
            .attr("y", d => projection([d.lng, d.lat])[1])
            .attr("text-anchor", "middle")
            .text(d => d.name);
    }

    // 4. Render Circular Count Bubbles for Countries, Regions & States
    function renderMarkers() {
        if (!currentTelemetry) return;

        let itemsToRender = [];
        if (currentLayer === 'regions') {
            itemsToRender = currentTelemetry.regions || [];
        } else if (currentLayer === 'countries') {
            itemsToRender = currentTelemetry.countries || [];
        } else {
            // 'all': combine both
            itemsToRender = [
                ...(currentTelemetry.regions || []),
                ...(currentTelemetry.countries || [])
            ];
        }

        const center = [-rotation[0], -rotation[1]];

        // Filter items on visible hemisphere
        const visibleItems = itemsToRender.filter(item => {
            if (item.lng === undefined || item.lat === undefined) return false;
            const dist = d3.geoDistance([item.lng, item.lat], center);
            return dist < (Math.PI / 2) * 0.96; // Within front hemisphere
        });

        const keyFn = d => (d.type || 'loc') + '_' + (d.region || d.country_code || d.country);

        const markerSelection = markersGroup.selectAll("g.globe-marker")
            .data(visibleItems, keyFn);

        markerSelection.exit().remove();

        const enterMarkers = markerSelection.enter()
            .append("g")
            .attr("class", "globe-marker")
            .on("mouseenter", function(event, d) {
                showTooltip(event, d);
                if (d.country_code) highlightTableRow(d.country_code);
            })
            .on("mousemove", function(event) {
                moveTooltip(event);
            })
            .on("mouseleave", function(event, d) {
                hideTooltip();
                clearTableRowHighlight();
            })
            .on("click", function(event, d) {
                focusOnCoordinates(d.lng, d.lat);
            });

        // Add circle
        enterMarkers.append("circle")
            .attr("class", "bubble-circle");

        // Add text inside (Visitor Number)
        enterMarkers.append("text")
            .attr("class", "bubble-text");

        // Pulse ring on Kerala / Top anchor
        enterMarkers.filter(d => (d.region === 'Kerala' || d.country_code === 'IN'))
            .append("circle")
            .attr("class", "pulse-ring-anchor")
            .attr("fill", "none")
            .attr("stroke-width", "2");

        const allMarkers = enterMarkers.merge(markerSelection);

        allMarkers.each(function(d) {
            const coords = projection([d.lng, d.lat]);
            if (!coords) return;

            const g = d3.select(this);
            g.attr("transform", `translate(${coords[0]}, ${coords[1]})`);

            const radius = d.marker_radius || 14;

            // Differentiate Region vs Country bubbles
            let fillColor = '#E55A42';
            if (d.type === 'region') {
                fillColor = d.count > 5000 ? '#E55A42' : (d.count > 800 ? '#F97316' : '#FB923C');
            } else {
                fillColor = d.count > 5000 ? '#DC2626' : (d.count > 1000 ? '#EA580C' : '#D97706');
            }

            g.select(".bubble-circle")
                .attr("r", radius)
                .attr("fill", fillColor);

            // Format Visitor Number for clear display (e.g. 12.6k, 3.1k, 843, 562)
            let displayCount = d.count;
            if (d.count >= 10000) {
                displayCount = (d.count / 1000).toFixed(1) + 'k';
            } else if (d.count >= 1000) {
                displayCount = (d.count / 1000).toFixed(1) + 'k';
            }

            const fontSize = radius >= 22 ? '11px' : (radius >= 16 ? '9.5px' : '8px');

            g.select(".bubble-text")
                .attr("font-size", fontSize)
                .text(displayCount);
        });
    }

    // 5. Update Projection on Rotation/Zoom
    function updateGlobe() {
        projection.scale(scale).rotate(rotation);

        oceanSphere.attr("r", scale);
        shadeOverlay.attr("r", scale);
        atmosphereHalo.attr("r", scale + 14);

        graticulePath.attr("d", path);

        if (worldTopology) {
            landGroup.selectAll("path").attr("d", path);
            borderGroup.selectAll("path").attr("d", path);
        }

        renderLabels();
        renderMarkers();

        // Update HUD
        const curLng = ((-rotation[0] % 360) + 360) % 360;
        const curLat = -rotation[1];
        if (coordDisplay) {
            coordDisplay.innerText = `Focus: ${curLat.toFixed(1)}°N, ${curLng.toFixed(1)}°E`;
        }
        if (zoomDisplay) {
            zoomDisplay.innerText = `Zoom: ${(scale / initialScale).toFixed(1)}x`;
        }
    }

    // 6. Smooth Auto-Spin Loop
    function startSpin() {
        if (animationTimer) return;
        animationTimer = d3.timer(() => {
            if (isAutoSpinning) {
                rotation[0] += spinSpeed;
                updateGlobe();
            }
        });
    }

    function stopSpin() {
        if (animationTimer) {
            animationTimer.stop();
            animationTimer = null;
        }
    }

    // 7. Interactive Drag to Rotate
    const drag = d3.drag()
        .on("start", () => {
            isAutoSpinning = false;
            updateSpinButton();
        })
        .on("drag", (event) => {
            const sensitivity = 0.35 / (scale / initialScale);
            rotation[0] += event.dx * sensitivity;
            rotation[1] -= event.dy * sensitivity;
            rotation[1] = Math.max(-80, Math.min(80, rotation[1]));
            updateGlobe();
        });

    svg.call(drag);

    // 8. Mouse Wheel Zoom
    svg.on("wheel", (event) => {
        event.preventDefault();
        const factor = event.deltaY < 0 ? 1.08 : 0.92;
        zoomBy(factor);
    });

    function zoomBy(factor) {
        scale = Math.max(initialScale * 0.7, Math.min(initialScale * 2.8, scale * factor));
        updateGlobe();
    }

    // 9. Camera Focus on Given Coordinates (Smooth Tween Transition)
    function focusOnCoordinates(targetLng, targetLat) {
        isAutoSpinning = false;
        updateSpinButton();

        const currentRot = projection.rotate();
        const targetRot = [-targetLng, -targetLat, 0];

        d3.transition()
            .duration(1100)
            .ease(d3.easeCubicOut)
            .tween("rotate", () => {
                const rInterpolator = d3.interpolate(currentRot, targetRot);
                return (t) => {
                    rotation = rInterpolator(t);
                    updateGlobe();
                };
            });
    }

    // 10. Tooltip Logic Showing Both Number of Visitors & Percentages
    function showTooltip(event, d) {
        const flag = d.flag || '🌍';
        const isRegion = d.type === 'region' || !!d.region;

        document.getElementById("ttFlag").innerText = flag;
        document.getElementById("ttLocation").innerText = isRegion ? `${d.region}, ${d.country}` : d.country;
        document.getElementById("ttTypeBadge").innerText = isRegion ? (d.sub_label || 'State / Region') : 'Sovereign Nation';

        // Exact Visitor Count & Percentages
        document.getElementById("ttVisits").innerText = `${Number(d.count).toLocaleString()} Visitors`;
        document.getElementById("ttPercent").innerText = `${d.percentage || 0}% of Global Traffic`;

        const countryShareRow = document.getElementById("ttCountryShareRow");
        if (isRegion && d.country_percentage) {
            countryShareRow.style.display = "flex";
            document.getElementById("ttCountryPercent").innerText = `${d.country_percentage}% of ${d.country}`;
        } else {
            countryShareRow.style.display = "none";
        }

        const citiesRow = document.getElementById("ttCitiesRow");
        if (isRegion && d.top_cities && d.top_cities.length > 0) {
            citiesRow.style.display = "block";
            document.getElementById("ttCities").innerText = d.top_cities.join(', ');
        } else if (!isRegion && d.top_regions && d.top_regions.length > 0) {
            citiesRow.style.display = "block";
            document.getElementById("ttCities").innerText = d.top_regions.join(' • ');
        } else {
            citiesRow.style.display = "none";
        }

        moveTooltip(event);
        tooltip.style("display", "block").style("opacity", "1");
    }

    function moveTooltip(event) {
        const wrapper = document.getElementById("globeStageWrapper");
        const rect = wrapper.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;

        tooltip
            .style("left", `${x}px`)
            .style("top", `${y - 12}px`);
    }

    function hideTooltip() {
        tooltip.style("opacity", "0").style("display", "none");
    }

    // 11. Synchronized Table Highlighting
    function highlightTableRow(countryCode) {
        document.querySelectorAll(`.country-row[data-code="${countryCode}"]`).forEach(row => {
            row.classList.add("highlighted");
        });
    }

    function clearTableRowHighlight() {
        document.querySelectorAll(`.country-row.highlighted, .region-row.highlighted`).forEach(row => {
            row.classList.remove("highlighted");
        });
    }

    // 12. Controls Event Listeners
    const btnToggleSpin = document.getElementById("btnToggleSpin");
    if (btnToggleSpin) {
        btnToggleSpin.addEventListener("click", () => {
            isAutoSpinning = !isAutoSpinning;
            if (isAutoSpinning && !animationTimer) startSpin();
            updateSpinButton();
        });
    }

    function updateSpinButton() {
        if (!btnToggleSpin) return;
        const label = document.getElementById("spinLabel");
        if (isAutoSpinning) {
            btnToggleSpin.classList.add("active");
            if (label) label.innerText = "Auto-Spin: ON";
        } else {
            btnToggleSpin.classList.remove("active");
            if (label) label.innerText = "Auto-Spin: OFF";
        }
    }
    updateSpinButton();

    document.getElementById("btnResetView")?.addEventListener("click", () => {
        scale = initialScale;
        focusOnCoordinates(76.3, 10.9); // Kerala focus
    });

    document.getElementById("btnZoomIn")?.addEventListener("click", () => zoomBy(1.2));
    document.getElementById("btnZoomOut")?.addEventListener("click", () => zoomBy(0.8));

    // Layer Switcher (Regions vs Countries vs Combined)
    document.querySelectorAll(".layer-btn").forEach(btn => {
        btn.addEventListener("click", function() {
            document.querySelectorAll(".layer-btn").forEach(b => b.classList.remove("active"));
            this.classList.add("active");
            currentLayer = this.getAttribute("data-layer");
            renderMarkers();
        });
    });

    // Quick Continent Jump Buttons
    document.querySelectorAll(".globe-jump-btn").forEach(btn => {
        btn.addEventListener("click", function() {
            const [lng, lat] = this.getAttribute("data-center").split(",").map(Number);
            focusOnCoordinates(lng, lat);
        });
    });

    // Country Row Click -> Focus on Globe
    document.querySelectorAll(".country-row").forEach(row => {
        row.addEventListener("click", function() {
            const lng = parseFloat(this.getAttribute("data-lng"));
            const lat = parseFloat(this.getAttribute("data-lat"));
            if (!isNaN(lng) && !isNaN(lat) && (lng !== 0 || lat !== 0)) {
                focusOnCoordinates(lng, lat);
            }
        });
    });

    // Region Row Click -> Focus on Globe
    document.querySelectorAll(".region-row").forEach(row => {
        row.addEventListener("click", function() {
            const lng = parseFloat(this.getAttribute("data-lng"));
            const lat = parseFloat(this.getAttribute("data-lat"));
            if (!isNaN(lng) && !isNaN(lat) && (lng !== 0 || lat !== 0)) {
                focusOnCoordinates(lng, lat);
            }
        });
    });

    // City Row Click -> Focus on Globe
    document.querySelectorAll(".city-row").forEach(row => {
        row.addEventListener("click", function() {
            const lng = parseFloat(this.getAttribute("data-lng"));
            const lat = parseFloat(this.getAttribute("data-lat"));
            if (!isNaN(lng) && !isNaN(lat) && (lng !== 0 || lat !== 0)) {
                focusOnCoordinates(lng, lat);
            }
        });
    });

    // 13. Time Horizon Filtering API Call
    document.querySelectorAll(".horizon-btn").forEach(btn => {
        btn.addEventListener("click", function() {
            const period = this.getAttribute("data-period");
            if (period === currentPeriod) return;

            document.querySelectorAll(".horizon-btn").forEach(b => b.classList.remove("active"));
            this.classList.add("active");

            fetchTelemetry(period);
        });
    });

    document.getElementById("btnRefreshTelemetry")?.addEventListener("click", function() {
        fetchTelemetry(currentPeriod);
    });

    function fetchTelemetry(period) {
        const spinner = document.getElementById("refreshSpinner");
        if (spinner) spinner.classList.add("spin-animation");

        fetch(`{{ route('admin.visitor.insights.api') }}?period=${period}`)
            .then(res => res.json())
            .then(data => {
                currentTelemetry = data;
                currentPeriod = period;

                // 1. Update KPI Cards (Showing both Numbers and Percentages)
                document.getElementById("kpiTotalVisitors").innerHTML = `${Number(data.summary.total_visitors).toLocaleString()} <span class="text-xs fw-normal text-muted">Visitors</span>`;
                document.getElementById("kpiCountriesCount").innerText = `${data.summary.unique_countries} Nations`;
                document.getElementById("kpiRegionsCount").innerText = `${data.summary.unique_regions || data.regions.length} Active States & Regions`;
                
                document.getElementById("kpiTopCountry").innerText = `${data.summary.top_country}: ${Number(data.summary.top_country_count).toLocaleString()} (${data.summary.top_country_percentage}%)`;
                document.getElementById("kpiTopRegion").innerText = `Top State: ${data.summary.top_region} (${Number(data.summary.top_region_count).toLocaleString()} • ${data.summary.top_region_percentage}%)`;
                
                document.getElementById("kpiLiveVisitors").innerText = `${data.summary.live_visitors} Active`;

                // 2. Update Table Badges & Rows
                document.getElementById("tableCountryCount").innerText = `${data.countries.length} Nations`;
                document.getElementById("tableRegionCount").innerText = `${data.regions.length} Regions / States`;
                document.getElementById("tableCityCount").innerText = `${data.cities.length} Cities`;

                // 3. Re-render Globe Markers
                renderMarkers();

                // 4. Update Tables dynamically (Showing both Visitor Counts & %)
                updateCountriesTable(data.countries);
                updateRegionsTable(data.regions);
                updateCitiesTable(data.cities);
            })
            .catch(err => console.error("Telemetry sync error:", err))
            .finally(() => {
                if (spinner) spinner.classList.remove("spin-animation");
            });
    }

    function updateCountriesTable(countries) {
        const tbody = document.getElementById("countriesTableBody");
        if (!tbody) return;

        tbody.innerHTML = countries.slice(0, 10).map(c => `
            <tr class="country-row" data-code="${c.country_code}" data-lng="${c.lng || 0}" data-lat="${c.lat || 0}" style="cursor: pointer;" title="Click to center 3D Earth globe on ${c.country}">
                <td class="ps-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="country-flag-icon fs-6">${c.flag || '🌍'}</span>
                        <span class="fw-bold text-dark country-name-link">${c.country}</span>
                    </div>
                </td>
                <td>
                    <span class="badge bg-light text-secondary font-mono border text-xs">${c.country_code}</span>
                </td>
                <td class="text-end pe-4">
                    <div class="d-flex align-items-baseline justify-content-end gap-1.5">
                        <span class="fw-bold text-primary">${Number(c.count).toLocaleString()}</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary py-0.5 px-1.5 text-xs">${c.percentage}%</span>
                    </div>
                    <div class="progress mt-1" style="height: 4px; background: #E2E8F0; width: 95px; margin-left: auto;">
                        <div class="progress-bar bg-primary" style="width: ${Math.min(100, c.percentage || 5)}%;"></div>
                    </div>
                </td>
            </tr>
        `).join("");

        tbody.querySelectorAll(".country-row").forEach(row => {
            row.addEventListener("click", function() {
                const lng = parseFloat(this.getAttribute("data-lng"));
                const lat = parseFloat(this.getAttribute("data-lat"));
                if (!isNaN(lng) && !isNaN(lat)) focusOnCoordinates(lng, lat);
            });
        });
    }

    function updateRegionsTable(regions) {
        const tbody = document.getElementById("regionsTableBody");
        if (!tbody) return;

        tbody.innerHTML = regions.slice(0, 10).map(r => `
            <tr class="region-row" data-lng="${r.lng || 0}" data-lat="${r.lat || 0}" style="cursor: pointer;" title="Click to center 3D Earth globe on ${r.region}">
                <td class="ps-4">
                    <div class="fw-bold text-dark region-name-link">${r.region}</div>
                    <div class="text-muted text-xs">${r.sub_label || 'Regional State'}</div>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-1.5">
                        <span>${r.flag || '🌍'}</span>
                        <span class="text-muted small">${r.country}</span>
                    </div>
                </td>
                <td class="text-end pe-4">
                    <div class="d-flex align-items-baseline justify-content-end gap-1.5">
                        <span class="fw-bold text-success">${Number(r.count).toLocaleString()}</span>
                        <span class="badge bg-success bg-opacity-10 text-success py-0.5 px-1.5 text-xs">${r.percentage}%</span>
                    </div>
                    <div class="text-muted text-xs mt-0.5">${r.country_percentage || r.percentage}% of ${r.country}</div>
                    <div class="progress mt-1" style="height: 4px; background: #E2E8F0; width: 95px; margin-left: auto;">
                        <div class="progress-bar bg-success" style="width: ${Math.min(100, r.percentage * 2)}%;"></div>
                    </div>
                </td>
            </tr>
        `).join("");

        tbody.querySelectorAll(".region-row").forEach(row => {
            row.addEventListener("click", function() {
                const lng = parseFloat(this.getAttribute("data-lng"));
                const lat = parseFloat(this.getAttribute("data-lat"));
                if (!isNaN(lng) && !isNaN(lat)) focusOnCoordinates(lng, lat);
            });
        });
    }

    function updateCitiesTable(cities) {
        const tbody = document.getElementById("citiesTableBody");
        if (!tbody) return;

        tbody.innerHTML = cities.slice(0, 10).map(ct => `
            <tr class="city-row" data-lng="${ct.lng || 0}" data-lat="${ct.lat || 0}" style="cursor: pointer;" title="Click to center 3D Earth globe on ${ct.city}">
                <td class="ps-4">
                    <div class="fw-bold text-dark city-name-link">${ct.city}</div>
                </td>
                <td>
                    <div class="text-muted small text-truncate" style="max-width: 140px;">
                        ${ct.region || ''}${ct.region && ct.country ? ', ' : ''}${ct.country || ct.location || ''}
                    </div>
                </td>
                <td class="text-end pe-4">
                    <div class="d-flex align-items-baseline justify-content-end gap-1.5">
                        <span class="fw-bold text-indigo" style="color: #6366F1;">${Number(ct.count).toLocaleString()}</span>
                        <span class="badge bg-indigo bg-opacity-10 text-indigo py-0.5 px-1.5 text-xs" style="color: #6366F1; background: rgba(99, 102, 241, 0.1);">${ct.percentage || ''}%</span>
                    </div>
                    <div class="progress mt-1" style="height: 4px; background: #E2E8F0; width: 95px; margin-left: auto;">
                        <div class="progress-bar" style="background: #6366F1; width: ${Math.min(100, (ct.percentage || 5) * 3)}%;"></div>
                    </div>
                </td>
            </tr>
        `).join("");

        tbody.querySelectorAll(".city-row").forEach(row => {
            row.addEventListener("click", function() {
                const lng = parseFloat(this.getAttribute("data-lng"));
                const lat = parseFloat(this.getAttribute("data-lat"));
                if (!isNaN(lng) && !isNaN(lat)) focusOnCoordinates(lng, lat);
            });
        });
    }

    // Window Resize Handler
    window.addEventListener("resize", () => {
        updateDimensions();
        svg.attr("width", width).attr("height", height).attr("viewBox", `0 0 ${width} ${height}`);
        oceanSphere.attr("cx", width / 2).attr("cy", height / 2);
        shadeOverlay.attr("cx", width / 2).attr("cy", height / 2);
        atmosphereHalo.attr("cx", width / 2).attr("cy", height / 2);
        projection.translate([width / 2, height / 2]);
        updateGlobe();
    });
</script>
@endpush

@endsection
