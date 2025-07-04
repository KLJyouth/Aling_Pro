<?php

namespace App\Models\Membership;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipLevel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "membership_levels";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "name",
        "code",
        "description",
        "price_monthly",
        "price_yearly",
        "duration_days",
        "icon",
        "color",
        "benefits",
        "api_quota",
        "ai_quota",
        "storage_quota",
        "bandwidth_quota",
        "discount_percent",
        "priority_support",
        "is_featured",
        "sort_order",
        "status",
        "upgrade_points",
        "upgrade_spending",
        "upgrade_months",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "price_monthly" => "decimal:2",
        "price_yearly" => "decimal:2",
        "duration_days" => "integer",
        "benefits" => "json",
        "api_quota" => "integer",
        "ai_quota" => "integer",
        "storage_quota" => "integer",
        "bandwidth_quota" => "integer",
        "discount_percent" => "integer",
        "priority_support" => "boolean",
        "is_featured" => "boolean",
        "sort_order" => "integer",
        "upgrade_points" => "integer",
        "upgrade_spending" => "decimal:2",
        "upgrade_months" => "integer",
    ];
    
    /**
     * 获取使用此等级的所有订阅
     */
    public function subscriptions()
    {
        return $this->hasMany(MembershipSubscription::class, "membership_level_id");
    }
    
    /**
     * 获取使用此等级的所有用户
     */
    public function users()
    {
        return $this->hasManyThrough(
            User::class,
            MembershipSubscription::class,
            "membership_level_id", // MembershipSubscription表的外键
            "id", // User表的主键
            "id", // MembershipLevel表的主键
            "user_id" // MembershipSubscription表的本地键
        );
    }
    
    /**
     * 获取此会员等级拥有的所有特权
     */
    public function privileges()
    {
        return $this->belongsToMany(MemberPrivilege::class, "member_privilege_level", "level_id", "privilege_id")
            ->withPivot("value")
            ->withTimestamps();
    }
}
