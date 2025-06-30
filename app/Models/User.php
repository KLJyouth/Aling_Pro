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
     * 可批量赋值的属性
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
     * 应该为序列化隐藏的属性
     *
     * @var array<int, string>
     */
    protected $hidden = [
        "password",
        "remember_token",
    ];

    /**
     * 应该被转换的属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        "email_verified_at" => "datetime",
    ];

    /**
     * 模型启动时注册事件
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // 生成唯一推荐码
            if (empty($user->referral_code)) {
                $user->referral_code = self::generateUniqueReferralCode();
            }
        });
    }

    /**
     * 生成唯一推荐码
     */
    protected static function generateUniqueReferralCode()
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where("referral_code", $code)->exists());

        return $code;
    }

    /**
     * 记录用户登录信息
     *
     * @param string $ip
     * @return void
     */
    public function recordLogin($ip)
    {
        // 如果有UserLogin模型，则记录登录信息
        if (class_exists("\\App\\Models\\UserLogin")) {
            \App\Models\UserLogin::create([
                "user_id" => $this->id,
                "ip_address" => $ip,
                "user_agent" => request()->userAgent(),
            ]);
        }
    }

    /**
     * 获取用户当前订阅
     */
    public function currentSubscription()
    {
        return $this->hasOne(MembershipSubscription::class)
            ->where("status", "active")
            ->where("end_date", ">", now())
            ->latest();
    }

    /**
     * 获取用户所有订阅
     */
    public function subscriptions()
    {
        return $this->hasMany(MembershipSubscription::class);
    }

    /**
     * 获取用户积分记录
     */
    public function points()
    {
        return $this->hasMany(MemberPoint::class);
    }

    /**
     * 获取用户作为推荐人的推荐记录
     */
    public function referrals()
    {
        return $this->hasMany(MemberReferral::class, "referrer_id");
    }

    /**
     * 获取用户被推荐的记录
     */
    public function referredBy()
    {
        return $this->hasOne(MemberReferral::class, "referred_id");
    }

    /**
     * 获取用户API密钥
     */
    public function apiKeys()
    {
        return $this->hasMany(ApiKey::class);
    }

    /**
     * 获取用户订单
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * 获取用户配额使用记录
     */
    public function quotaUsages()
    {
        return $this->hasMany(QuotaUsage::class);
    }

    /**
     * 检查用户是否为管理员
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === "admin";
    }

    /**
     * 获取用户当前会员等级
     *
     * @return \App\Models\MembershipLevel|null
     */
    public function getCurrentMembershipLevel()
    {
        $subscription = $this->currentSubscription;
        
        if ($subscription) {
            return $subscription->membershipLevel;
        }
        
        // 返回免费会员等级
        return MembershipLevel::where("code", "free")->first();
    }

    /**
     * 获取用户总积分
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
     * 检查用户是否有特定特权
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
     * 获取用户特定配额的使用情况
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
     * 检查用户是否可以使用特定配额
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
        
        // 无限配额
        if ($quota < 0) {
            return true;
        }
        
        $used = $this->getQuotaUsage($quotaType);
        
        return ($used + $amount) <= $quota;
    }

    /**
     * 记录配额使用
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
