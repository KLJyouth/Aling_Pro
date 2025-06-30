<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipLevel extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * 可批量赋值的属性
     *
     * @var array<int, string>
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
        "upgrade_points",
        "upgrade_spending",
        "upgrade_months",
        "status",
    ];
    
    /**
     * 应该被转换的属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        "price_monthly" => "float",
        "price_yearly" => "float",
        "duration_days" => "integer",
        "api_quota" => "integer",
        "ai_quota" => "integer",
        "storage_quota" => "integer",
        "bandwidth_quota" => "integer",
        "discount_percent" => "integer",
        "priority_support" => "boolean",
        "is_featured" => "boolean",
        "sort_order" => "integer",
        "upgrade_points" => "integer",
        "upgrade_spending" => "float",
        "upgrade_months" => "integer",
        "benefits" => "array",
    ];
    
    /**
     * 获取会员等级的所有订阅
     */
    public function subscriptions()
    {
        return $this->hasMany(MembershipSubscription::class);
    }
    
    /**
     * 获取会员等级的所有特权
     */
    public function privileges()
    {
        return $this->belongsToMany(MemberPrivilege::class, "member_privilege_level", "level_id", "privilege_id")
            ->withPivot("value")
            ->withTimestamps();
    }
    
    /**
     * 获取会员等级的月度价格格式化
     *
     * @return string
     */
    public function getFormattedMonthlyPriceAttribute()
    {
        return number_format($this->price_monthly, 2);
    }
    
    /**
     * 获取会员等级的年度价格格式化
     *
     * @return string
     */
    public function getFormattedYearlyPriceAttribute()
    {
        return number_format($this->price_yearly, 2);
    }
    
    /**
     * 获取会员等级的年度节省金额
     *
     * @return float
     */
    public function getYearlySavingsAttribute()
    {
        return ($this->price_monthly * 12) - $this->price_yearly;
    }
    
    /**
     * 获取会员等级的年度节省百分比
     *
     * @return int
     */
    public function getYearlySavingsPercentAttribute()
    {
        $monthlyTotal = $this->price_monthly * 12;
        if ($monthlyTotal <= 0) {
            return 0;
        }
        
        return round(($this->getYearlySavingsAttribute() / $monthlyTotal) * 100);
    }
    
    /**
     * 获取特定特权的值
     *
     * @param string $code
     * @return string|null
     */
    public function getPrivilegeValue($code)
    {
        $privilege = $this->privileges()->where("code", $code)->first();
        
        return $privilege ? $privilege->pivot->value : null;
    }
    
    /**
     * 检查是否为免费会员等级
     *
     * @return bool
     */
    public function isFree()
    {
        return $this->price_monthly <= 0 && $this->price_yearly <= 0;
    }
}
