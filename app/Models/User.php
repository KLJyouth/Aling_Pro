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
     * ��ȡ�û���OAuth�˺�
     */
    public function oauthAccounts()
    {
        return $this->hasMany(\App\Models\OAuth\UserAccount::class);
    }
    
    /**
     * ��ȡ�û����豸
     */
    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }
    
    /**
     * ��ȡ�û���MFA����
     */
    public function mfaMethods()
    {
        return $this->hasMany(UserMfaMethod::class);
    }
    
    /**
     * ��ȡ�û��İ�ȫ��־
     */
    public function securityLogs()
    {
        return $this->hasMany(SecurityLog::class);
    }
    
    /**
     * ��ȡ�û��İ�ȫ����
     */
    public function securityAlerts()
    {
        return $this->hasMany(SecurityAlert::class);
    }
    
    /**
     * ��ȡ��ǰ�豸
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
     * ����û��Ƿ���ָ�����豸
     * 
     * @param string $deviceId �豸ID
     * @return bool
     */
    public function hasDevice(string $deviceId): bool
    {
        return $this->devices()->where("device_id", $deviceId)->exists();
    }
    
    /**
     * ����û��Ƿ���ָ����MFA����
     * 
     * @param string $method MFA����
     * @return bool
     */
    public function hasMfaMethod(string $method): bool
    {
        return $this->mfaMethods()->where("method", $method)->exists();
    }
    
    /**
     * ��ȡ�û�����ҪMFA����
     * 
     * @return \App\Models\UserMfaMethod|null
     */
    public function primaryMfaMethod()
    {
        return $this->mfaMethods()->where("is_primary", true)->first();
    }
    
    /**
     * ��¼��¼
     * 
     * @param string $ip IP��ַ
     * @return void
     */
    public function recordLogin(string $ip): void
    {
        $this->last_login_at = now();
        $this->last_login_ip = $ip;
        $this->save();
        
        // ������ȫ��־
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
     * ����û��Ƿ���ҪMFA��֤
     * 
     * @return bool
     */
    public function needsMfaVerification(): bool
    {
        return $this->has_mfa && !session("mfa_verified", false);
    }
    
    /**
     * ����û��Ƿ���Ҫ�豸��֤
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
     * ��ȡ�û��Ļ�Ա����
     */
    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Membership\MembershipSubscription::class);
    }
    
    /**
     * ��ȡ�û���ǰ��Ծ�Ļ�Ա����
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
     * ����û��Ƿ��ǻ�Ա
     * 
     * @return bool
     */
    public function isMember(): bool
    {
        return $this->activeSubscription() !== null;
    }
    
    /**
     * ��ȡ�û��Ķ��ʹ�ü�¼
     */
    public function quotaUsages()
    {
        return $this->hasMany(QuotaUsage::class);
    }
}
