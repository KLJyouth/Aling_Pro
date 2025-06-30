<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "name",
        "email",
        "password",
        "phone_number",
        "has_mfa",
        "last_login_at",
        "last_login_ip",
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        "password",
        "remember_token",
        "mfa_recovery_codes",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "email_verified_at" => "datetime",
        "phone_verified_at" => "datetime",
        "password" => "hashed",
        "has_mfa" => "boolean",
        "mfa_recovery_codes" => "array",
        "last_login_at" => "datetime",
    ];
    
    /**
     * 获取用户的OAuth账号
     */
    public function oauthAccounts()
    {
        return $this->hasMany(\App\Models\OAuth\UserAccount::class);
    }
    
    /**
     * 获取用户的设备
     */
    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }
    
    /**
     * 获取用户的MFA方法
     */
    public function mfaMethods()
    {
        return $this->hasMany(UserMfaMethod::class);
    }
    
    /**
     * 获取用户的安全日志
     */
    public function securityLogs()
    {
        return $this->hasMany(SecurityLog::class);
    }
    
    /**
     * 获取用户的安全警报
     */
    public function securityAlerts()
    {
        return $this->hasMany(SecurityAlert::class);
    }
    
    /**
     * 获取当前设备
     * 
     * @return \App\Models\UserDevice|null
     */
    public function currentDevice()
    {
        $deviceId = request()->header("X-Device-ID");
        $deviceFingerprint = request()->header("X-Device-Fingerprint");
        
        if ($deviceId) {
            return $this->devices()->where("device_id", $deviceId)->first();
        } elseif ($deviceFingerprint) {
            return $this->devices()->where("device_fingerprint", $deviceFingerprint)->first();
        }
        
        return null;
    }
    
    /**
     * 检查用户是否有指定的设备
     * 
     * @param string $deviceId 设备ID
     * @return bool
     */
    public function hasDevice(string $deviceId): bool
    {
        return $this->devices()->where("device_id", $deviceId)->exists();
    }
    
    /**
     * 检查用户是否有指定的MFA方法
     * 
     * @param string $method MFA方法
     * @return bool
     */
    public function hasMfaMethod(string $method): bool
    {
        return $this->mfaMethods()->where("method", $method)->exists();
    }
    
    /**
     * 获取用户的主要MFA方法
     * 
     * @return \App\Models\UserMfaMethod|null
     */
    public function primaryMfaMethod()
    {
        return $this->mfaMethods()->where("is_primary", true)->first();
    }
    
    /**
     * 记录登录
     * 
     * @param string $ip IP地址
     * @return void
     */
    public function recordLogin(string $ip): void
    {
        $this->last_login_at = now();
        $this->last_login_ip = $ip;
        $this->save();
        
        // 创建安全日志
        $this->securityLogs()->create([
            "ip_address" => $ip,
            "user_agent" => request()->userAgent(),
            "event_type" => "login",
            "context" => "auth",
            "metadata" => [
                "timestamp" => now()->timestamp,
            ],
        ]);
    }
    
    /**
     * 检查用户是否需要MFA验证
     * 
     * @return bool
     */
    public function needsMfaVerification(): bool
    {
        return $this->has_mfa && !session("mfa_verified", false);
    }
    
    /**
     * 检查用户是否需要设备验证
     * 
     * @return bool
     */
    public function needsDeviceVerification(): bool
    {
        if (!config("zero_trust.device_binding.enabled", true)) {
            return false;
        }
        
        $deviceId = request()->header("X-Device-ID");
        $deviceFingerprint = request()->header("X-Device-Fingerprint");
        
        if (!$deviceId && !$deviceFingerprint) {
            return true;
        }
        
        $device = $this->currentDevice();
        
        return !$device || !$device->is_verified;
    }
    
    /**
     * 获取用户的会员订阅
     */
    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Membership\MembershipSubscription::class);
    }
    
    /**
     * 获取用户当前活跃的会员订阅
     * 
     * @return \App\Models\Membership\MembershipSubscription|null
     */
    public function activeSubscription()
    {
        return $this->subscriptions()
            ->where("status", "active")
            ->where("end_date", ">", now())
            ->orderBy("end_date", "desc")
            ->first();
    }
    
    /**
     * 检查用户是否是会员
     * 
     * @return bool
     */
    public function isMember(): bool
    {
        return $this->activeSubscription() !== null;
    }
    
    /**
     * 获取用户的额度使用记录
     */
    public function quotaUsages()
    {
        return $this->hasMany(QuotaUsage::class);
    }
}
