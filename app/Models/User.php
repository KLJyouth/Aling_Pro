<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * ��������ֵ������
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "name",
        "email",
        "password",
        "role",
        "status",
        "referral_code",
    ];

    /**
     * Ӧ��Ϊ���л����ص�����
     *
     * @var array<int, string>
     */
    protected $hidden = [
        "password",
        "remember_token",
    ];

    /**
     * Ӧ�ñ�ת��������
     *
     * @var array<string, string>
     */
    protected $casts = [
        "email_verified_at" => "datetime",
    ];

    /**
     * ģ������ʱע���¼�
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // ����Ψһ�Ƽ���
            if (empty($user->referral_code)) {
                $user->referral_code = self::generateUniqueReferralCode();
            }
        });
    }

    /**
     * ����Ψһ�Ƽ���
     */
    protected static function generateUniqueReferralCode()
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where("referral_code", $code)->exists());

        return $code;
    }

    /**
     * ��¼�û���¼��Ϣ
     *
     * @param string $ip
     * @return void
     */
    public function recordLogin($ip)
    {
        // �����UserLoginģ�ͣ����¼��¼��Ϣ
        if (class_exists("\\App\\Models\\UserLogin")) {
            \App\Models\UserLogin::create([
                "user_id" => $this->id,
                "ip_address" => $ip,
                "user_agent" => request()->userAgent(),
            ]);
        }
    }

    /**
     * ��ȡ�û���ǰ����
     */
    public function currentSubscription()
    {
        return $this->hasOne(MembershipSubscription::class)
            ->where("status", "active")
            ->where("end_date", ">", now())
            ->latest();
    }

    /**
     * ��ȡ�û����ж���
     */
    public function subscriptions()
    {
        return $this->hasMany(MembershipSubscription::class);
    }

    /**
     * ��ȡ�û����ּ�¼
     */
    public function points()
    {
        return $this->hasMany(MemberPoint::class);
    }

    /**
     * ��ȡ�û���Ϊ�Ƽ��˵��Ƽ���¼
     */
    public function referrals()
    {
        return $this->hasMany(MemberReferral::class, "referrer_id");
    }

    /**
     * ��ȡ�û����Ƽ��ļ�¼
     */
    public function referredBy()
    {
        return $this->hasOne(MemberReferral::class, "referred_id");
    }

    /**
     * ��ȡ�û�API��Կ
     */
    public function apiKeys()
    {
        return $this->hasMany(ApiKey::class);
    }

    /**
     * ��ȡ�û�����
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * ��ȡ�û����ʹ�ü�¼
     */
    public function quotaUsages()
    {
        return $this->hasMany(QuotaUsage::class);
    }

    /**
     * ����û��Ƿ�Ϊ����Ա
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === "admin";
    }

    /**
     * ��ȡ�û���ǰ��Ա�ȼ�
     *
     * @return \App\Models\MembershipLevel|null
     */
    public function getCurrentMembershipLevel()
    {
        $subscription = $this->currentSubscription;
        
        if ($subscription) {
            return $subscription->membershipLevel;
        }
        
        // ������ѻ�Ա�ȼ�
        return MembershipLevel::where("code", "free")->first();
    }

    /**
     * ��ȡ�û��ܻ���
     *
     * @return int
     */
    public function getTotalPoints()
    {
        return $this->points()
            ->where(function ($query) {
                $query->whereNull("expires_at")
                      ->orWhere("expires_at", ">", now());
            })
            ->sum("points");
    }

    /**
     * ����û��Ƿ����ض���Ȩ
     *
     * @param string $privilegeCode
     * @return bool
     */
    public function hasPrivilege($privilegeCode)
    {
        $level = $this->getCurrentMembershipLevel();
        
        if (!$level) {
            return false;
        }
        
        return $level->privileges()
            ->where("code", $privilegeCode)
            ->exists();
    }

    /**
     * ��ȡ�û��ض�����ʹ�����
     *
     * @param string $quotaType
     * @return int
     */
    public function getQuotaUsage($quotaType)
    {
        return $this->quotaUsages()
            ->where("quota_type", $quotaType)
            ->whereDate("created_at", today())
            ->sum("amount");
    }

    /**
     * ����û��Ƿ����ʹ���ض����
     *
     * @param string $quotaType
     * @param int $amount
     * @return bool
     */
    public function canUseQuota($quotaType, $amount = 1)
    {
        $level = $this->getCurrentMembershipLevel();
        
        if (!$level) {
            return false;
        }
        
        $quota = $level->{"${quotaType}_quota"} ?? 0;
        
        // �������
        if ($quota < 0) {
            return true;
        }
        
        $used = $this->getQuotaUsage($quotaType);
        
        return ($used + $amount) <= $quota;
    }

    /**
     * ��¼���ʹ��
     *
     * @param string $quotaType
     * @param int $amount
     * @param string|null $description
     * @return bool
     */
    public function recordQuotaUsage($quotaType, $amount = 1, $description = null)
    {
        if (!$this->canUseQuota($quotaType, $amount)) {
            return false;
        }
        
        $this->quotaUsages()->create([
            "quota_type" => $quotaType,
            "amount" => $amount,
            "description" => $description,
        ]);
        
        return true;
    }
}
