<div class="table-responsive">
    <table class="table table-hover align-middle mb-0 user-logs-table" style="font-size: 0.84rem;">
        <thead class="bg-light bg-opacity-75 text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
            <tr>
                <th class="text-center py-3 px-3" style="width: 50px;">#</th>
                <th class="py-3 px-3">User / Inquirer</th>
                <th class="py-3 px-3">Action & Event</th>
                <th class="text-center py-3 px-3" style="width: 100px;">Status</th>
                <th class="py-3 px-3">Diagnostic Details / Reason</th>
                <th class="py-3 px-3">Device & Location</th>
                <th class="text-center py-3 px-3" style="width: 150px;">Timestamp</th>
                <th class="text-center py-3 px-3" style="width: 90px;">Inspect</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($logs as $index => $log)
                @php
                    $isFailed = $log->status === 'failed';
                    $isWarning = $log->status === 'warning';
                    $isSuccess = $log->status === 'success';

                    $rowBg = $isFailed ? 'background-color: rgba(254, 242, 242, 0.45);' : '';
                    $userInitials = strtoupper(mb_substr($log->user_name ?: ($log->user?->name ?: ($log->identifier ?: 'U')), 0, 1));
                    $userDisplayName = $log->user_name ?: ($log->user?->name ?: ($log->identifier ? 'Identifier: ' . $log->identifier : 'Anonymous Guest'));
                    $displayEmail = $log->user_email ?: ($log->user?->email ?: ($log->identifier && str_contains($log->identifier, '@') ? $log->identifier : null));
                    $displayMobile = $log->user_mobile ?: ($log->user?->mobile ?: ($log->identifier && !str_contains($log->identifier, '@') ? $log->identifier : null));
                    
                    $roleName = strtolower($log->user_role ?: ($log->user?->role?->name ?? 'guest'));
                    $roleBadgeClass = match($roleName) {
                        'admin'      => 'bg-rose-50 text-rose-700 border-rose-200',
                        'mufti'      => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'questioner' => 'bg-sky-50 text-sky-700 border-sky-200',
                        default      => 'bg-slate-50 text-slate-600 border-slate-200',
                    };

                    $deviceIcon = match(strtolower($log->device ?? 'desktop')) {
                        'mobile device', 'mobile' => 'bi-phone',
                        'tablet' => 'bi-tablet',
                        default => 'bi-laptop',
                    };
                @endphp
                <tr style="{{ $rowBg }}" class="log-row">
                    {{-- Row Index / ID --}}
                    <td class="text-center text-muted fw-semibold px-3 py-3" style="font-size: 0.78rem;">
                        {{ $logs->firstItem() + $index }}
                    </td>

                    {{-- User Identity --}}
                    <td class="px-3 py-3">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="position-relative flex-shrink-0">
                                @if($log->user && $log->user->avatar)
                                    <img src="{{ asset($log->user->avatar) }}" alt="{{ $userDisplayName }}" class="rounded-circle border" width="38" height="38" style="object-fit: cover;">
                                @else
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-2xs" 
                                         style="width: 38px; height: 38px; font-size: 0.85rem; background: {{ $isFailed ? '#EF4444' : ($roleName === 'mufti' ? '#059669' : ($roleName === 'admin' ? '#4F46E5' : '#0284C7')) }};">
                                        {{ $userInitials }}
                                    </div>
                                @endif
                                <span class="position-absolute bottom-0 end-0 p-1 border border-white rounded-circle {{ $isFailed ? 'bg-danger' : ($isSuccess ? 'bg-success' : 'bg-secondary') }}" style="width: 9px; height: 9px;"></span>
                            </div>
                            <div class="min-w-0">
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    @if($log->user_id)
                                        <a href="{{ route('admin.questioners.show', $log->user_id) }}" class="fw-bold text-dark text-decoration-none text-truncate d-inline-block hover-underline" style="max-width: 170px;" title="{{ $userDisplayName }}">
                                            {{ $userDisplayName }}
                                        </a>
                                    @else
                                        <span class="fw-bold text-dark text-truncate d-inline-block" style="max-width: 170px;" title="{{ $userDisplayName }}">
                                            {{ $userDisplayName }}
                                        </span>
                                    @endif
                                    <span class="badge rounded-pill px-1.5 py-0.5 border text-2xs fw-bold {{ $roleBadgeClass }}">
                                        {{ ucfirst($roleName) }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-2 mt-0.5 flex-wrap text-muted" style="font-size: 0.74rem;">
                                    @if($displayEmail)
                                        <span class="d-flex align-items-center gap-1 text-truncate" title="Search this email" onclick="quickFilterSearch('{{ $displayEmail }}')" style="cursor: pointer;">
                                            <i class="bi bi-envelope text-slate-400"></i>{{ $displayEmail }}
                                        </span>
                                    @endif
                                    @if($displayMobile)
                                        <span class="badge bg-light text-slate-600 border rounded-pill px-1.5 py-0.5" style="font-size: 0.68rem;" onclick="quickFilterSearch('{{ $displayMobile }}')" title="Search this mobile">
                                            <i class="bi bi-telephone text-slate-400 me-0.5"></i>{{ $displayMobile }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Event Action --}}
                    <td class="px-3 py-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi {{ $log->getActionIcon() }} fs-5 flex-shrink-0"></i>
                            <div>
                                <span class="fw-bold text-dark d-block text-truncate" style="font-size: 0.82rem; max-width: 160px;">
                                    {{ $log->getActionLabel() }}
                                </span>
                                <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5 text-2xs">
                                    {{ ucfirst($log->action_category) }}
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

                    {{-- Diagnostic Details / Failure Reason --}}
                    <td class="px-3 py-3">
                        <div class="min-w-0" style="max-width: 320px;">
                            @if($log->failure_reason)
                                <div class="mb-1">
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.7rem;">
                                        <i class="bi bi-shield-x me-1"></i>{{ $log->getFormattedFailureReason() }}
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
                            <span class="fw-semibold text-dark">{{ $log->browser }}</span>
                            <span>&bull;</span>
                            <span>{{ $log->platform }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-1.5 text-muted" style="font-size: 0.72rem;">
                            <span class="font-monospace text-slate-500">{{ $log->ip_address }}</span>
                            @if($log->location && $log->location !== 'Global Location')
                                <span>&bull;</span>
                                <span class="text-truncate" style="max-width: 120px;" title="{{ $log->location }}">
                                    <i class="bi bi-geo-alt text-slate-400"></i>{{ $log->location }}
                                </span>
                            @endif
                        </div>
                    </td>

                    {{-- Timestamp --}}
                    <td class="text-center px-3 py-3">
                        <div class="fw-bold text-dark" style="font-size: 0.78rem;">
                            {{ $log->created_at->format('d M Y, h:i A') }}
                        </div>
                        <div class="text-muted small" style="font-size: 0.7rem;">
                            {{ $log->created_at->diffForHumans() }}
                        </div>
                    </td>

                    {{-- Inspect Action Button --}}
                    <td class="text-center px-3 py-3">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 inspect-log-btn" 
                                onclick="openLogInspectModal({{ $log->id }})" title="Inspect Diagnostic Details">
                            <i class="bi bi-eye"></i> <span class="d-none d-xl-inline ms-0.5">Inspect</span>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="py-4">
                            <div class="d-inline-flex p-3 rounded-circle bg-light border text-muted mb-3 shadow-2xs">
                                <i class="bi bi-journal-x fs-2 text-slate-400"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">No behavioral logs found</h6>
                            <p class="text-muted small mb-0" style="max-width: 380px; margin: 0 auto;">
                                There are no activity or behavioral log records matching your active search and filter criteria.
                            </p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination Links Footer --}}
@if($logs->hasPages() || $logs->total() > 0)
    <div class="p-3.5 px-4 border-top d-flex flex-wrap justify-content-between align-items-center bg-white gap-3">
        <div class="text-muted small" style="font-size: 0.76rem;" id="pagination-info">
            Showing <strong class="text-dark">{{ $logs->firstItem() ?? 0 }}</strong> to <strong class="text-dark">{{ $logs->lastItem() ?? 0 }}</strong> of <strong class="text-dark">{{ number_format($logs->total()) }}</strong> logs
        </div>
        <div>
            {{ $logs->links() }}
        </div>
    </div>
@endif
