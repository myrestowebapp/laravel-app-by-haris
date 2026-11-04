@extends('layouts.admin')

@section('title', 'User Behavioral Logs & Diagnostics')

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

    {{-- Top Header & Breadcrumbs --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-xs">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><span class="text-muted">Intelligence &amp; Reports</span></li>
                    <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">User Behavior Logs</li>
                </ol>
            </nav>
            <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-person-lines-fill text-primary"></i>
                <span>User Behavioral Intelligence &amp; Auth Diagnostics</span>
            </h4>
            <p class="text-muted small mb-0 mt-0.5">Comprehensive audit trail of user logins, failed attempts, OTP verifications, questions &amp; session flows.</p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-2xs d-none d-sm-inline-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
                <i class="bi bi-clock-history text-primary"></i>
                <span id="live-clock">{{ now()->format('h:i:s A') }}</span> IST
            </span>

            <button type="button" class="btn btn-sm btn-white border rounded-pill px-3 py-1.5 fw-semibold shadow-2xs d-inline-flex align-items-center gap-1.5" onclick="refreshUserLogs()" title="Refresh Logs">
                <i class="bi bi-arrow-clockwise text-primary" id="refresh-icon"></i>
                <span>Refresh</span>
            </button>

            <a href="{{ route('admin.user-logs.export', request()->all()) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold shadow-2xs d-inline-flex align-items-center gap-1.5">
                <i class="bi bi-download"></i>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    {{-- 4 Summary Statistic Cards --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: Total Activity Logs --}}
        <div class="col-6 col-lg-3">
            <div class="modern-card p-3.5 p-md-4 rounded-4 bg-white border h-100 shadow-2xs">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Logs</span>
                    <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-journal-text fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1" id="stat-total-logs">{{ number_format($totalLogsAll) }}</h3>
                <div class="d-flex align-items-center text-muted" style="font-size: 0.74rem;">
                    <span class="text-primary fw-semibold me-1">+{{ number_format($todayLogs) }}</span> today
                    <span class="ms-1 text-muted">(All sources)</span>
                </div>
            </div>
        </div>

        {{-- Card 2: Successful Logins --}}
        <div class="col-6 col-lg-3">
            <div class="modern-card p-3.5 p-md-4 rounded-4 bg-white border h-100 shadow-2xs">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Successful Logins</span>
                    <div class="p-2 rounded-3 bg-success bg-opacity-10 text-success">
                        <i class="bi bi-shield-check fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1" id="stat-success-logins">{{ number_format($successLogins) }}</h3>
                <div class="d-flex align-items-center text-success" style="font-size: 0.74rem;">
                    <i class="bi bi-check-circle me-1"></i> Authorized sessions
                </div>
            </div>
        </div>

        {{-- Card 3: Failed Login Issues (Crucial for Admin Troubleshooting) --}}
        <div class="col-6 col-lg-3">
            <div class="modern-card p-3.5 p-md-4 rounded-4 bg-white border h-100 shadow-2xs position-relative overflow-hidden cursor-pointer" 
                 onclick="quickFilterFailed()" title="Click to view only failed login issues">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-danger text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Login &amp; Auth Issues</span>
                    <div class="p-2 rounded-3 bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-danger mb-1" id="stat-failed-logins">{{ number_format($failedLogins) }}</h3>
                <div class="d-flex align-items-center text-muted" style="font-size: 0.74rem;">
                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-0.5 text-2xs fw-bold me-1">Troubleshoot</span>
                    <span>Click to isolate</span>
                </div>
            </div>
        </div>

        {{-- Card 4: Active Users Today --}}
        <div class="col-6 col-lg-3">
            <div class="modern-card p-3.5 p-md-4 rounded-4 bg-white border h-100 shadow-2xs">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Active Users Today</span>
                    <div class="p-2 rounded-3 bg-info bg-opacity-10 text-info">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1" id="stat-active-users">{{ number_format($activeUsersToday) }}</h3>
                <div class="d-flex align-items-center text-muted" style="font-size: 0.74rem;">
                    <span class="text-info fw-semibold me-1">{{ number_format($uniqueVisitorsToday) }}</span> unique visitors today
                </div>
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="modern-card rounded-4 border bg-white overflow-hidden shadow-2xs mb-4">

        {{-- Toolbar: Search, Filters & Quick Pills --}}
        <div class="p-4 px-md-4.5 border-bottom bg-white">
            <div class="row g-3 align-items-center">
                
                {{-- Universal Live Search Input --}}
                <div class="col-12 col-md-5 col-xl-4">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="user-logs-search" class="form-control rounded-pill ps-5 pe-5 py-2" 
                               placeholder="Search name, email, mobile, IP or issue..." 
                               value="{{ $search }}" autocomplete="off" style="font-size: 0.85rem;">
                        <button type="button" id="clear-search-btn" class="btn btn-link position-absolute top-50 end-0 translate-middle-y me-2 text-muted p-0 text-decoration-none {{ $search ? '' : 'd-none' }}" 
                                onclick="clearSearch()" title="Clear Search">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                </div>

                {{-- Status Filter --}}
                <div class="col-6 col-md-3 col-xl-2">
                    <select id="status-filter" class="form-select rounded-pill py-2 text-xs" onchange="applyFilters()">
                        <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="failed" {{ ($status ?? '') === 'failed' ? 'selected' : '' }}>❌ Failed (Issues)</option>
                        <option value="success" {{ ($status ?? '') === 'success' ? 'selected' : '' }}>✅ Success Only</option>
                        <option value="warning" {{ ($status ?? '') === 'warning' ? 'selected' : '' }}>⚠️ Warning</option>
                        <option value="info" {{ ($status ?? '') === 'info' ? 'selected' : '' }}>ℹ️ Info</option>
                    </select>
                </div>

                {{-- Action Filter: added page_visit --}}
                <div class="col-6 col-md-4 col-xl-2">
                    <select id="action-filter" class="form-select rounded-pill py-2 text-xs" onchange="applyFilters()">
                        <option value="all" {{ ($action ?? 'all') === 'all' ? 'selected' : '' }}>All Event Types</option>
                        <option value="page_visit" {{ ($action ?? '') === 'page_visit' ? 'selected' : '' }}>👁️ Page Visits (Visitors)</option>
                        <option value="login_all" {{ ($action ?? '') === 'login_all' ? 'selected' : '' }}>🔑 All Login Attempts</option>
                        <option value="login_failed" {{ ($action ?? '') === 'login_failed' ? 'selected' : '' }}>🚫 Failed Password Login</option>
                        <option value="otp_all" {{ ($action ?? '') === 'otp_all' ? 'selected' : '' }}>📩 All OTP Events</option>
                        <option value="otp_failed" {{ ($action ?? '') === 'otp_failed' ? 'selected' : '' }}>⚠️ Failed OTP Verification</option>
                        <option value="question_submitted" {{ ($action ?? '') === 'question_submitted' ? 'selected' : '' }}>📝 Question Inquiries</option>
                        <option value="profile_completed" {{ ($action ?? '') === 'profile_completed' ? 'selected' : '' }}>👤 Profile Completed</option>
                        <option value="logout" {{ ($action ?? '') === 'logout' ? 'selected' : '' }}>🚪 Logouts</option>
                    </select>
                </div>

                {{-- Date Range Filter --}}
                <div class="col-6 col-md-3 col-xl-2">
                    <select id="date-range-filter" class="form-select rounded-pill py-2 text-xs" onchange="applyFilters()">
                        <option value="all" {{ ($dateRange ?? 'all') === 'all' ? 'selected' : '' }}>All Time</option>
                        <option value="today" {{ ($dateRange ?? '') === 'today' ? 'selected' : '' }}>📅 Today</option>
                        <option value="7d" {{ ($dateRange ?? '') === '7d' ? 'selected' : '' }}>🗓️ Last 7 Days</option>
                        <option value="30d" {{ ($dateRange ?? '') === '30d' ? 'selected' : '' }}>🗓️ Last 30 Days</option>
                    </select>
                </div>

                {{-- Rows Per Page: replaced selector with info badge (8 fixed) --}}
                <div class="col-6 col-md-3 col-xl-2 d-flex align-items-center">
                    <span class="badge bg-light text-muted border rounded-pill px-3 py-2 text-xs">
                        <i class="bi bi-layout-text-window-reverse me-1"></i>8 rows / page
                    </span>
                </div>
            </div>

            {{-- Quick Filter Pills --}}
            <div class="d-flex align-items-center gap-1.5 mt-3 pt-2 border-top flex-wrap" style="font-size: 0.74rem;">
                <span class="text-muted fw-semibold me-1">Quick Filters:</span>
                <button type="button" class="btn btn-xs rounded-pill px-2.5 py-1 quick-pill {{ ($sourceFilter ?? 'all') === 'all' && ($status ?? 'all') === 'all' && ($action ?? 'all') === 'all' ? 'btn-primary' : 'btn-light border text-muted' }}" onclick="resetAllFilters()">
                    All System Logs
                </button>
                <button type="button" class="btn btn-xs rounded-pill px-2.5 py-1 quick-pill {{ ($sourceFilter ?? '') === 'login' ? 'btn-success text-white' : 'btn-light border text-muted' }}" onclick="quickFilterSource('login')">
                    🔑 Logins ({{ number_format($successLogins) }})
                </button>
                <button type="button" class="btn btn-xs rounded-pill px-2.5 py-1 quick-pill {{ ($sourceFilter ?? '') === 'visitor' ? 'btn-info text-white' : 'btn-light border text-muted' }}" onclick="quickFilterSource('visitor')">
                    👁 Web Visitors
                </button>
                <button type="button" class="btn btn-xs rounded-pill px-2.5 py-1 quick-pill {{ ($sourceFilter ?? '') === 'fatwa' ? 'btn-warning text-dark' : 'btn-light border text-muted' }}" onclick="quickFilterSource('fatwa')">
                    📜 Fatwa &amp; Scholar Activities
                </button>
                <button type="button" class="btn btn-xs rounded-pill px-2.5 py-1 quick-pill {{ ($status ?? '') === 'failed' ? 'btn-danger text-white' : 'btn-light border text-danger' }}" onclick="quickFilterFailed()">
                    🚨 Issues &amp; Failures Only
                </button>
                <button type="button" class="btn btn-xs rounded-pill px-2.5 py-1 quick-pill {{ ($sourceFilter ?? '') === 'security' ? 'btn-dark' : 'btn-light border text-muted' }}" onclick="quickFilterSource('security')">
                    🛡 Security &amp; Audit Logs
                </button>
                <button type="button" class="btn btn-xs rounded-pill px-2.5 py-1 quick-pill {{ ($sourceFilter ?? '') === 'api' ? 'btn-secondary text-white' : 'btn-light border text-muted' }}" onclick="quickFilterSource('api')">
                    ⚡ API Telemetry
                </button>
                <button type="button" class="btn btn-xs rounded-pill px-2.5 py-1 quick-pill {{ ($dateRange ?? '') === 'today' ? 'btn-primary' : 'btn-light border text-muted' }}" onclick="quickFilterToday()">
                    📅 Today Only
                </button>
            </div>
        </div>

        {{-- Table Container --}}
        <div id="user-logs-table-container" class="position-relative">
            <div id="logs-loading-indicator" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center" style="z-index: 10; display: none !important;">
                <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            @include('admin.user-logs.partials.table-unified', ['logs' => $paginator])
        </div>
    </div>
</div>

{{-- Inspect Log Modal --}}
<div class="modal fade" id="logInspectModal" tabindex="-1" aria-labelledby="logInspectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4 bg-light bg-opacity-50">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 rounded-circle bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-shield-shaded fs-5"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0" id="logInspectModalLabel">Log Diagnostic Inspector</h6>
                        <span class="text-muted small" style="font-size: 0.74rem;">Detailed telemetry and user behavioral context</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="log-modal-body">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="text-muted small mt-2">Loading log context...</div>
                </div>
            </div>
            <div class="modal-footer border-top py-2.5 px-4 bg-light bg-opacity-30 d-flex justify-content-between">
                <div id="modal-footer-extra"></div>
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let searchDebounceTimer = null;
let activeAbortController = null;

// Clock updates
setInterval(() => {
    const clockEl = document.getElementById('live-clock');
    if (clockEl) {
        const now = new Date();
        clockEl.innerText = now.toLocaleTimeString('en-US', { hour12: true });
    }
}, 1000);

// Setup Live Debounced Search
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('user-logs-search');
    const clearBtn = document.getElementById('clear-search-btn');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const val = this.value.trim();
            if (clearBtn) {
                if (val.length > 0) clearBtn.classList.remove('d-none');
                else clearBtn.classList.add('d-none');
            }

            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => {
                fetchLogsAjax();
            }, 350);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchDebounceTimer);
                fetchLogsAjax();
            }
        });
    }
});

function clearSearch() {
    const searchInput = document.getElementById('user-logs-search');
    const clearBtn = document.getElementById('clear-search-btn');
    if (searchInput) {
        searchInput.value = '';
        if (clearBtn) clearBtn.classList.add('d-none');
        fetchLogsAjax();
    }
}

function quickFilterSearch(query) {
    const searchInput = document.getElementById('user-logs-search');
    const clearBtn = document.getElementById('clear-search-btn');
    if (searchInput) {
        searchInput.value = query;
        if (clearBtn) clearBtn.classList.remove('d-none');
        document.getElementById('status-filter').value = 'all';
        document.getElementById('action-filter').value = 'all';
        fetchLogsAjax();
    }
}

let currentLogSource = '{{ $sourceFilter ?? 'all' }}';

function quickFilterSource(src) {
    currentLogSource = src;
    fetchLogsAjax();
}

function quickFilterFailed() {
    currentLogSource = 'all';
    document.getElementById('status-filter').value = 'failed';
    document.getElementById('action-filter').value = 'all';
    fetchLogsAjax();
}

function quickFilterAction(actionVal) {
    currentLogSource = 'all';
    document.getElementById('action-filter').value = actionVal;
    document.getElementById('status-filter').value = 'all';
    fetchLogsAjax();
}

function quickFilterToday() {
    document.getElementById('date-range-filter').value = 'today';
    fetchLogsAjax();
}

function resetAllFilters() {
    currentLogSource = 'all';
    document.getElementById('user-logs-search').value = '';
    document.getElementById('clear-search-btn')?.classList.add('d-none');
    document.getElementById('status-filter').value = 'all';
    document.getElementById('action-filter').value = 'all';
    document.getElementById('date-range-filter').value = 'all';
    fetchLogsAjax();
}

function applyFilters() {
    fetchLogsAjax();
}

function refreshUserLogs() {
    const icon = document.getElementById('refresh-icon');
    if (icon) icon.classList.add('bi-spin');
    fetchLogsAjax(() => {
        if (icon) icon.classList.remove('bi-spin');
    });
}

// Fetch Logs via AJAX
async function fetchLogsAjax(callback) {
    const search = document.getElementById('user-logs-search')?.value || '';
    const status = document.getElementById('status-filter')?.value || 'all';
    const action = document.getElementById('action-filter')?.value || 'all';
    const dateRange = document.getElementById('date-range-filter')?.value || 'all';

    const params = new URLSearchParams({
        search: search,
        status: status,
        action: action,
        source: currentLogSource,
        date_range: dateRange,
        per_page: '8'
    });

    // Sync URL without reload
    const newUrl = `${window.location.pathname}?${params.toString()}`;
    window.history.replaceState({}, '', newUrl);

    // Abort prior pending request
    if (activeAbortController) {
        activeAbortController.abort();
    }
    activeAbortController = new AbortController();

    const loader = document.getElementById('logs-loading-indicator');
    if (loader) loader.style.setProperty('display', 'flex', 'important');

    try {
        const response = await fetch(newUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-Live-Search': 'true',
                'Accept': 'application/json'
            },
            signal: activeAbortController.signal
        });

        if (response.ok) {
            const data = await response.json();
            const container = document.getElementById('user-logs-table-container');
            if (container && data.html) {
                container.innerHTML = `
                    <div id="logs-loading-indicator" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center" style="z-index: 10; display: none !important;">
                        <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;"></div>
                    </div>
                    ${data.html}
                `;
            }

            // Update metric counts if returned
            if (data.stats) {
                if (document.getElementById('stat-total-logs')) document.getElementById('stat-total-logs').innerText = data.stats.total;
                if (document.getElementById('stat-success-logins')) document.getElementById('stat-success-logins').innerText = data.stats.successLogins;
                if (document.getElementById('stat-failed-logins')) document.getElementById('stat-failed-logins').innerText = data.stats.failedLogins;
                if (document.getElementById('stat-active-users')) document.getElementById('stat-active-users').innerText = data.stats.activeToday;
            }
        }
    } catch (e) {
        if (e.name !== 'AbortError') {
            console.error('Error fetching user logs:', e);
        }
    } finally {
        if (loader) loader.style.setProperty('display', 'none', 'important');
        if (typeof callback === 'function') callback();
    }
}

// Open Diagnostic Modal
async function openLogInspectModal(logId, source = 'activity') {
    const modalEl = document.getElementById('logInspectModal');
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    const modalBody = document.getElementById('log-modal-body');
    const modalFooterExtra = document.getElementById('modal-footer-extra');
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="text-muted small mt-2">Loading log diagnostic context...</div>
        </div>
    `;

    try {
        const response = await fetch(`/admin/user-logs/${logId}?source=${encodeURIComponent(source)}`);
        const log = await response.json();

        const isFailed = log.status === 'failed';
        const statusBadge = isFailed
            ? `<span class="badge rounded-pill px-3 py-1 fw-bold" style="background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA;"><i class="bi bi-x-circle-fill me-1"></i> Failed</span>`
            : `<span class="badge rounded-pill px-3 py-1 fw-bold" style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0;"><i class="bi bi-check-circle-fill me-1"></i> Success</span>`;

        let contextHtml = '—';
        if (log.context_data && Object.keys(log.context_data).length > 0) {
            contextHtml = `<pre class="bg-light p-3 rounded-3 text-2xs mb-0 font-monospace border" style="max-height: 180px; overflow-y: auto;">${JSON.stringify(log.context_data, null, 2)}</pre>`;
        }

        modalBody.innerHTML = `
            <div class="p-3.5 rounded-3 mb-3 border ${isFailed ? 'bg-danger bg-opacity-10 border-danger border-opacity-25' : 'bg-light bg-opacity-50'}">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-dark fs-6">${log.action_label}</span>
                    ${statusBadge}
                </div>
                <div class="text-dark small mb-2">${log.description}</div>
                ${log.failure_raw ? `
                    <div class="alert alert-danger py-2 px-3 mb-0 rounded-3 d-flex align-items-center gap-2" style="font-size: 0.8rem;">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                        <div><strong>Diagnosis:</strong> ${log.failure_reason} (Code: <code>${log.failure_raw}</code>)</div>
                    </div>
                ` : ''}
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="p-3 rounded-3 border bg-white h-100">
                        <div class="text-muted text-uppercase fw-bold text-2xs mb-2">User / Inquirer Identity</div>
                        <div class="fw-bold text-dark mb-0.5">${log.user_name}</div>
                        <div class="text-muted small mb-1"><i class="bi bi-envelope me-1"></i>${log.user_email || '—'}</div>
                        <div class="text-muted small mb-1"><i class="bi bi-telephone me-1"></i>${log.user_mobile}</div>
                        <div class="mt-2">
                            <span class="badge bg-light text-muted border rounded-pill px-2.5 py-0.5 text-2xs">Role: ${log.user_role}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 rounded-3 border bg-white h-100">
                        <div class="text-muted text-uppercase fw-bold text-2xs mb-2">Network &amp; Device Environment</div>
                        <div class="d-flex align-items-center gap-1.5 text-dark small mb-1">
                            <i class="bi bi-laptop text-primary"></i> <strong>${log.device}</strong> &bull; ${log.browser} on ${log.platform}
                        </div>
                        <div class="text-muted small mb-1 font-monospace"><i class="bi bi-broadcast me-1"></i>${log.ip_address}</div>
                        <div class="text-muted small mb-1"><i class="bi bi-geo-alt me-1"></i>${log.location}</div>
                        <div class="text-muted text-2xs mt-2"><i class="bi bi-clock me-1"></i>${log.created_at} (${log.time_ago})</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-3 rounded-3 border bg-white">
                        <div class="text-muted text-uppercase fw-bold text-2xs mb-1.5">Context Payload &amp; Technical Attributes</div>
                        ${contextHtml}
                        <div class="mt-2 text-muted text-2xs text-truncate" title="${log.user_agent}">
                            <strong>User Agent:</strong> ${log.user_agent || '—'}
                        </div>
                    </div>
                </div>
            </div>
        `;

        if (modalFooterExtra) {
            if (log.user_profile) {
                modalFooterExtra.innerHTML = `
                    <a href="${log.user_profile}" class="btn btn-sm btn-primary rounded-pill px-3 text-xs">
                        <i class="bi bi-person me-1"></i> View Questioner Account
                    </a>
                `;
            } else {
                modalFooterExtra.innerHTML = `
                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 text-xs" onclick="quickFilterSearch('${log.user_email || log.ip_address}'); bootstrap.Modal.getInstance(document.getElementById('logInspectModal')).hide();">
                        <i class="bi bi-search me-1"></i> Search Related Logs
                    </button>
                `;
            }
        }
    } catch (e) {
        modalBody.innerHTML = `<div class="alert alert-danger">Error retrieving diagnostic details: ${e.message}</div>`;
    }
}
</script>
@endpush
