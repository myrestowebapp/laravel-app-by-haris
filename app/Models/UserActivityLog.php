<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class UserActivityLog extends Model
{
    use HasFactory;

    protected $table = 'user_activity_logs';

    protected $fillable = [
        'user_id',
        'identifier',
        'user_name',
        'user_email',
        'user_mobile',
        'user_role',
        'action',
        'action_category',
        'status',
        'description',
        'failure_reason',
        'ip_address',
        'user_agent',
        'device',
        'platform',
        'browser',
        'location',
        'context_data',
    ];

    protected $casts = [
        'context_data' => 'array',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /**
     * Associated registered user, if available.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Primary factory method to record user behavioral and auth events.
     */
    public static function record(
        string $action,
        string $description,
        string $status = 'info',
        ?User $user = null,
        ?string $identifier = null,
        ?string $failureReason = null,
        string $actionCategory = 'general',
        array $contextData = [],
        ?Request $request = null
    ): self {
        $req = $request ?: request();
        $ip = $req ? ($req->ip() ?: '127.0.0.1') : '127.0.0.1';
        $ua = $req ? $req->userAgent() : null;

        $deviceInfo = UserLoginLog::parseUserAgent($ua);

        // Resolve user information
        $userId = $user?->id;
        $userName = $user?->name;
        $userEmail = $user?->email;
        $userMobile = $user?->mobile;
        $userRole = $user?->role ? strtolower($user->role->name) : ($user ? 'questioner' : 'guest');
        $location = $user?->state ? ($user->state . ', ' . ($user->country ?? 'Global')) : ($user?->country ?? null);

        // If user wasn't passed directly but identifier was provided (e.g. login email/mobile)
        if (!$userId && $identifier) {
            $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
            $foundUser = User::where($isEmail ? 'email' : 'mobile', $identifier)->first();
            if ($foundUser) {
                $userId = $foundUser->id;
                $userName = $foundUser->name;
                $userEmail = $foundUser->email;
                $userMobile = $foundUser->mobile;
                $userRole = $foundUser->role ? strtolower($foundUser->role->name) : 'questioner';
                $location = $foundUser->state ? ($foundUser->state . ', ' . ($foundUser->country ?? 'Global')) : ($foundUser->country ?? null);
            } else {
                if ($isEmail) {
                    $userEmail = $identifier;
                } else {
                    $userMobile = $identifier;
                }
            }
        }

        if (!$location && $ip) {
            $cachedGeo = \Illuminate\Support\Facades\Cache::get("ip_geo_{$ip}");
            if ($cachedGeo && isset($cachedGeo['country'])) {
                $location = ($cachedGeo['city'] ?? '') ? ($cachedGeo['city'] . ', ' . $cachedGeo['country']) : $cachedGeo['country'];
            }
        }

        return static::create([
            'user_id'         => $userId,
            'identifier'      => $identifier ?: ($userEmail ?: $userMobile),
            'user_name'       => $userName,
            'user_email'      => $userEmail,
            'user_mobile'     => $userMobile,
            'user_role'       => $userRole,
            'action'          => $action,
            'action_category' => $actionCategory,
            'status'          => $status,
            'description'     => $description,
            'failure_reason'  => $failureReason,
            'ip_address'      => $ip,
            'user_agent'      => $ua,
            'device'          => $deviceInfo['device'] ?? 'Desktop',
            'platform'        => $deviceInfo['platform'] ?? 'Unknown OS',
            'browser'         => $deviceInfo['browser'] ?? 'Browser',
            'location'        => $location ?: 'Global Location',
            'context_data'    => $contextData,
        ]);
    }

    /**
     * Backward-compatible alias for log()
     */
    public static function log(
        string $action,
        string $description,
        string $status = 'info',
        ?User $user = null,
        ?string $identifier = null,
        ?string $failureReason = null,
        string $actionCategory = 'general',
        array $contextData = [],
        ?Request $request = null
    ): self {
        return static::record($action, $description, $status, $user, $identifier, $failureReason, $actionCategory, $contextData, $request);
    }

    /**
     * Return human-friendly label for actions
     */
    public function getActionLabel(): string
    {
        return match ($this->action) {
            'login_success'          => 'Login Successful',
            'login_failed'           => 'Login Failed',
            'logout'                 => 'User Logged Out',
            'otp_requested'          => 'Login OTP Requested',
            'otp_verified'           => 'OTP Verified',
            'otp_failed'             => 'OTP Verification Failed',
            'password_reset_request' => 'Password Reset Requested',
            'password_reset_success' => 'Password Reset Successful',
            'password_changed'       => 'Password Changed',
            'profile_completed'      => 'Profile Completed',
            'profile_updated'        => 'Profile Updated',
            'question_submitted'     => 'Question Submitted',
            'account_registered'     => 'New Account Registered',
            default                  => ucwords(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Return Bootstrap icon class for this event action
     */
    public function getActionIcon(): string
    {
        return match ($this->action) {
            'login_success'          => 'bi-box-arrow-in-right text-success',
            'login_failed'           => 'bi-exclamation-triangle-fill text-danger',
            'logout'                 => 'bi-box-arrow-left text-secondary',
            'otp_requested'          => 'bi-envelope-check text-info',
            'otp_verified'           => 'bi-shield-check text-success',
            'otp_failed'             => 'bi-shield-x text-danger',
            'password_reset_request' => 'bi-key text-warning',
            'password_reset_success' => 'bi-key-fill text-success',
            'password_changed'       => 'bi-lock-fill text-primary',
            'profile_completed'      => 'bi-person-check-fill text-success',
            'profile_updated'        => 'bi-person-gear text-primary',
            'question_submitted'     => 'bi-chat-left-text-fill text-primary',
            'account_registered'     => 'bi-person-plus-fill text-success',
            default                  => 'bi-activity text-secondary',
        };
    }

    /**
     * Human readable diagnostic failure reason
     */
    public function getFormattedFailureReason(): string
    {
        if (empty($this->failure_reason)) {
            return '—';
        }

        return match ($this->failure_reason) {
            'invalid_password'  => 'Incorrect Password',
            'user_not_found'    => 'Account Not Found',
            'account_inactive'  => 'Account Deactivated',
            'invalid_otp'       => 'Invalid OTP Code',
            'expired_otp'       => 'Expired OTP Code',
            'mail_failed'       => 'Email Delivery Failed',
            'csrf_mismatch'     => 'Session / CSRF Expired',
            'throttle_exceeded' => 'Too Many Attempts (Rate Limited)',
            default             => ucwords(str_replace('_', ' ', $this->failure_reason)),
        };
    }

    /**
     * Backfill legacy logins from user_login_logs if needed
     */
    public static function syncFromLegacyLogs(int $limit = 500): int
    {
        $existingCount = static::count();
        if ($existingCount > 0) {
            return 0; // Already seeded/populated
        }

        $legacyLogs = UserLoginLog::with('user.role')
            ->orderBy('id', 'desc')
            ->take($limit)
            ->get();

        $synced = 0;
        foreach ($legacyLogs as $l) {
            $u = $l->user;
            static::create([
                'user_id'         => $l->user_id,
                'identifier'      => $u?->email ?: $u?->mobile,
                'user_name'       => $u?->name,
                'user_email'      => $u?->email,
                'user_mobile'     => $u?->mobile,
                'user_role'       => $u?->role ? strtolower($u->role->name) : 'questioner',
                'action'          => 'login_success',
                'action_category' => 'auth',
                'status'          => 'success',
                'description'     => 'Successful login session from ' . ($l->device ?? 'Device') . ' (' . ($l->platform ?: 'OS') . ' / ' . ($l->browser ?: 'Browser') . ')',
                'failure_reason'  => null,
                'ip_address'      => $l->ip_address,
                'user_agent'      => $l->user_agent,
                'device'          => $l->device ?: 'Desktop',
                'platform'        => $l->platform ?: 'Unknown OS',
                'browser'         => $l->browser ?: 'Browser',
                'location'        => $l->location ?: ($u?->country ?? 'Global'),
                'context_data'    => ['duration_seconds' => $l->duration_seconds],
                'created_at'      => $l->login_at ?: $l->created_at,
                'updated_at'      => $l->last_activity_at ?: $l->updated_at,
            ]);
            $synced++;
        }

        return $synced;
    }
}
