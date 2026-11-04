<div class="table-responsive">
    <table class="table table-hover align-middle mb-0 user-logs-table" style="font-size: 0.84rem;">
        <thead class="bg-light bg-opacity-75 text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
            <tr>
                <th class="text-center py-3 px-3" style="width: 45px;">#</th>
                <th class="py-3 px-3">User / Inquirer</th>
                <th class="py-3 px-3">Action &amp; Event</th>
                <th class="text-center py-3 px-3" style="width: 95px;">Status</th>
                <th class="py-3 px-3">Diagnostic Details / Reason</th>
                <th class="py-3 px-3">Device &amp; Location</th>
                <th class="text-center py-3 px-3" style="width: 140px;">Timestamp</th>
                <th class="text-center py-3 px-3" style="width: 90px;">Source</th>
                <th class="text-center py-3 px-3" style="width: 75px;">Inspect</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($logs as $index => $log)
                @php
                    $source      = $log->_source ?? 'activity';
                    $status      = $log->status ?? 'info';
                    $isFailed    = $status === 'failed';
                    $isWarning   = $status === 'warning';
                    $isSuccess   = $status === 'success';
                    $rowBg       = $isFailed ? 'background-color: rgba(254, 242, 242, 0.45);' : '';

                    $userName    = $log->user_name ?: 'Anonymous';
                    $userEmail   = $log->user_email ?: '';
                    $userMobile  = $log->user_mobile ?: '';
                    $userRole    = strtolower($log->user_role ?? 'guest');
                    $userId      = $log->user_id ?? null;
                    $userModel   = $log->_user_model ?? null;

                    $userInitials = strtoupper(mb_substr($userName, 0, 1) ?: 'U');

                    $roleBadgeClass = match($userRole) {
                        'admin'       => 'bg-rose-50 text-rose-700 border-rose-200',
                        'mufti', 'scholar' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'questioner'  => 'bg-sky-50 text-sky-700 border-sky-200',
                        default       => 'bg-slate-50 text-slate-600 border-slate-200',
                    };

                    $deviceIcon = match(strtolower($log->device ?? 'desktop')) {
                        'mobile device', 'mobile' => 'bi-phone',
                        'tablet', 'tab'           => 'bi-tablet',
                        default                   => 'bi-laptop',
                    };

                    // Action label & icon
                    $action = $log->action ?? 'page_visit';
                    $actionLabel = match($action) {
                        'login_success'          => 'Login Successful',
                        'login_failed'           => 'Login Failed',
                        'logout'                 => 'User Logged Out',
                        'otp_requested'          => 'OTP Requested',
                        'otp_verified'           => 'OTP Verified',
                        'otp_failed'             => 'OTP Failed',
                        'password_reset_request' => 'Password Reset',
                        'password_reset_success' => 'Password Reset OK',
                        'password_changed'       => 'Password Changed',
                        'profile_completed'      => 'Profile Completed',
                        'profile_updated'        => 'Profile Updated',
                        'question_submitted'     => 'Question Submitted',
                        'account_registered'     => 'Registered',
                        'page_visit'             => 'Page Visited',
                        'scholar_login'          => 'Scholar Login',
                        'scholar_logout'         => 'Scholar Logout',
                        'google_login'           => 'Google Login',
                        'otp_login'              => 'OTP Login',
                        'country_view'           => 'Fatwa Read',
                        'accepted'               => 'Question Accepted',
                        'assigned'               => 'Assigned to Mufti',
                        'answer_submitted'       => 'Fatwa Drafted',
                        'published'              => 'Fatwa Published',
                        'admin_opened'           => 'Admin Review',
                        'mufti_opened'           => 'Scholar Review',
                        default                  => ucwords(str_replace('_', ' ', $action)),
                    };

                    $actionIcon = match($action) {
                        'login_success', 'scholar_login', 'google_login', 'otp_login' => 'bi-box-arrow-in-right text-success',
                        'login_failed', 'otp_failed'                                  => 'bi-exclamation-triangle-fill text-danger',
                        'logout', 'scholar_logout'                                    => 'bi-box-arrow-left text-secondary',
                        'otp_requested', 'otp_verified'                               => 'bi-shield-check text-info',
                        'country_view'                                                => 'bi-book-half text-primary',
                        'accepted', 'assigned', 'published'                           => 'bi-check2-circle text-success',
                        'question_submitted'                                          => 'bi-chat-left-text-fill text-primary',
                        'page_visit'                                                  => 'bi-eye text-slate-400',
                        default                                                       => 'bi-activity text-secondary',
                    };

                    $sourceConfig = match($source) {
                        'login'    => ['label' => 'Login Log',   'class' => 'bg-success-subtle text-success border-success-subtle', 'icon' => 'bi-box-arrow-in-right'],
                        'visitor'  => ['label' => 'Visitor Log', 'class' => 'bg-info-subtle text-info border-info-subtle',          'icon' => 'bi-globe2'],
                        'fatwa'    => ['label' => 'Fatwa Log',   'class' => 'bg-warning-subtle text-dark border-warning-subtle',     'icon' => 'bi-journal-check'],
                        'audit'    => ['label' => 'Health Log',  'class' => 'bg-danger-subtle text-danger border-danger-subtle',     'icon' => 'bi-shield-check'],
                        'api'      => ['label' => 'API Log',     'class' => 'bg-secondary-subtle text-secondary border',             'icon' => 'bi-code-slash'],
                        'security' => ['label' => 'Audit Log',   'class' => 'bg-dark-subtle text-dark border',                       'icon' => 'bi-lock-fill'],
                        default    => ['label' => 'Activity',    'class' => 'bg-primary-subtle text-primary border-primary-subtle', 'icon' => 'bi-activity'],
                    };

                    $location = $log->location ?? '';
                    $createdAt = \Carbon\Carbon::parse($log->created_at);
                @endphp

                <tr style="{{ $rowBg }}" class="log-row">
                    {{-- Row Index --}}
                    <td class="text-center text-muted fw-semibold px-2 py-3" style="font-size: 0.78rem;">
                        {{ $logs->firstItem() + $index }}
                    </td>

                    {{-- User Identity --}}
                    <td class="px-3 py-3">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="position-relative flex-shrink-0">
                                @if($userModel && $userModel->avatar)
                                    <img src="{{ asset($userModel->avatar) }}" alt="{{ $userName }}" class="rounded-circle border" width="38" height="38" style="object-fit: cover;">
                                @elseif($source === 'visitor' && !$userId)
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-2xs"
                                         style="width: 38px; height: 38px; font-size: 0.85rem; background: #64748B;">
                                        <i class="bi bi-globe2" style="font-size: 1rem;"></i>
                                    </div>
                                @elseif($source === 'fatwa' && !$userId)
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-2xs"
                                         style="width: 38px; height: 38px; font-size: 0.85rem; background: #D97706;">
                                        <i class="bi bi-book" style="font-size: 1rem;"></i>
                                    </div>
                                @else
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-2xs"
                                         style="width: 38px; height: 38px; font-size: 0.85rem; background: {{ $isFailed ? '#EF4444' : ($userRole === 'mufti' || $userRole === 'scholar' ? '#059669' : ($userRole === 'admin' ? '#4F46E5' : '#0284C7')) }};">
                                        {{ $userInitials }}
                                    </div>
                                @endif
                                <span class="position-absolute bottom-0 end-0 p-1 border border-white rounded-circle {{ $isFailed ? 'bg-danger' : ($isSuccess ? 'bg-success' : ($source === 'visitor' ? 'bg-info' : 'bg-secondary')) }}"
                                      style="width: 9px; height: 9px;"></span>
                            </div>
                            <div class="min-w-0">
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    @if($userId)
                                        <a href="{{ route('admin.questioners.show', $userId) }}" class="fw-bold text-dark text-decoration-none text-truncate d-inline-block hover-underline" style="max-width: 170px;" title="{{ $userName }}">
                                            {{ $userName }}
                                        </a>
                                    @else
                                        <span class="fw-bold text-dark text-truncate d-inline-block" style="max-width: 170px;" title="{{ $userName }}">
                                            {{ $userName }}
                                        </span>
                                    @endif
                                    <span class="badge rounded-pill px-1.5 py-0.5 border text-2xs fw-bold {{ $roleBadgeClass }}">
                                        {{ ucfirst($userRole) }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-2 mt-0.5 flex-wrap text-muted" style="font-size: 0.74rem;">
                                    @if($userEmail)
                                        <span class="d-flex align-items-center gap-1 text-truncate" style="max-width: 180px;" onclick="quickFilterSearch('{{ $userEmail }}')" style="cursor: pointer;" title="Filter by this email">
                                            <i class="bi bi-envelope text-slate-400"></i>{{ $userEmail }}
                                        </span>
                                    @endif
                                    @if($userMobile)
                                        <span class="badge bg-light text-slate-600 border rounded-pill px-1.5 py-0.5" style="font-size: 0.68rem;" onclick="quickFilterSearch('{{ $userMobile }}')" style="cursor: pointer;" title="Filter by this mobile">
                                            <i class="bi bi-telephone text-slate-400 me-0.5"></i>{{ $userMobile }}
                                        </span>
                                    @elseif($source === 'visitor' && !$userId && !$userEmail)
                                        <span class="text-muted" style="font-size: 0.7rem;">
                                            <i class="bi bi-incognito me-0.5"></i>Web Visitor
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Action / Event --}}
                    <td class="px-3 py-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi {{ $actionIcon }} fs-5 flex-shrink-0"></i>
                            <div>
                                <span class="fw-bold text-dark d-block text-truncate" style="font-size: 0.82rem; max-width: 160px;">
                                    {{ $actionLabel }}
                                </span>
                                <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5 text-2xs">
                                    {{ ucfirst($log->action_category ?? $source) }}
                                </span>
                            </div>
                        </div>
                    </td>

                    {{-- Status Badge --}}
                    <td class="text-center px-3 py-3">
                        @if($isSuccess)
                            <span class="badge rounded-pill px-2.5 py-1 text-2xs fw-bold" style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0;">
                                <i class="bi bi-check-circle-fill me-1"></i>Success
                            </span>
                        @elseif($isFailed)
                            <span class="badge rounded-pill px-2.5 py-1 text-2xs fw-bold" style="background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA;">
                                <i class="bi bi-x-circle-fill me-1"></i>Failed
                            </span>
                        @elseif($isWarning)
                            <span class="badge rounded-pill px-2.5 py-1 text-2xs fw-bold" style="background: #FFFBEB; color: #D97706; border: 1px solid #FDE68A;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>Warning
                            </span>
                        @else
                            <span class="badge rounded-pill px-2.5 py-1 text-2xs fw-bold" style="background: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE;">
                                <i class="bi bi-info-circle-fill me-1"></i>Info
                            </span>
                        @endif
                    </td>

                    {{-- Description / Details --}}
                    <td class="px-3 py-3">
                        <div class="min-w-0" style="max-width: 320px;">
                            @if(!empty($log->failure_reason))
                                <div class="mb-1">
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.7rem;">
                                        <i class="bi bi-shield-x me-1"></i>{{ ucwords(str_replace('_', ' ', $log->failure_reason)) }}
                                    </span>
                                </div>
                            @endif
                            <div class="text-dark small text-truncate" style="font-size: 0.79rem;" title="{{ $log->description }}">
                                {{ $log->description }}
                            </div>
                        </div>
                    </td>

                    {{-- Device, IP & Location --}}
                    <td class="px-3 py-3">
                        <div class="d-flex align-items-center gap-1.5 text-muted mb-0.5" style="font-size: 0.74rem;">
                            <i class="bi {{ $deviceIcon }} text-primary"></i>
                            <span class="fw-semibold text-dark">{{ $log->browser ?? 'Browser' }}</span>
                            <span>&bull;</span>
                            <span>{{ $log->platform ?? $log->device ?? 'Web' }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-1.5 text-muted" style="font-size: 0.72rem;">
                            <span class="font-monospace text-slate-500">{{ $log->ip_address ?? '—' }}</span>
                            @if($location && $location !== 'Global Location')
                                <span>&bull;</span>
                                <span class="text-truncate" style="max-width: 120px;" title="{{ $location }}">
                                    <i class="bi bi-geo-alt text-slate-400"></i>{{ $location }}
                                </span>
                            @endif
                        </div>
                    </td>

                    {{-- Timestamp --}}
                    <td class="text-center px-3 py-3">
                        <div class="fw-bold text-dark" style="font-size: 0.78rem;">
                            {{ $createdAt->format('d M Y, h:i A') }}
                        </div>
                        <div class="text-muted small" style="font-size: 0.7rem;">
                            {{ $createdAt->diffForHumans() }}
                        </div>
                    </td>

                    {{-- Source Badge --}}
                    <td class="text-center px-2 py-3">
                        <span class="badge rounded-pill px-2 py-1 border text-2xs fw-bold {{ $sourceConfig['class'] }}">
                            <i class="bi {{ $sourceConfig['icon'] }} me-0.5"></i>{{ $sourceConfig['label'] }}
                        </span>
                    </td>

                    {{-- Inspect Modal Button --}}
                    <td class="text-center px-2 py-3">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 inspect-log-btn text-xs" 
                                onclick="openLogInspectModal({{ $log->id }}, '{{ $source }}')" title="Inspect Full Diagnostic Record">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <div class="py-4">
                            <div class="d-inline-flex p-3 rounded-circle bg-light border text-muted mb-3 shadow-2xs">
                                <i class="bi bi-journal-x fs-2 text-slate-400"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">No logs found</h6>
                            <p class="text-muted small mb-0" style="max-width: 380px; margin: 0 auto;">
                                No activity, login, fatwa or visitor logs match your search criteria.
                            </p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($logs->hasPages() || $logs->total() > 0)
    <div class="p-3.5 px-4 border-top d-flex flex-wrap justify-content-between align-items-center bg-white gap-3">
        <div class="text-muted small" style="font-size: 0.76rem;" id="pagination-info">
            Showing <strong class="text-dark">{{ $logs->firstItem() ?? 0 }}</strong>
            to <strong class="text-dark">{{ $logs->lastItem() ?? 0 }}</strong>
            of <strong class="text-dark">{{ number_format($logs->total()) }}</strong> logs across all system tables
        </div>
        <div>
            {{ $logs->links() }}
        </div>
    </div>
@endif
