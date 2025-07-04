<?php

namespace App\Models\Membership;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipSubscription extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "membership_subscriptions";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "user_id",
        "membership_level_id",
        "order_id",
        "subscription_no",
        "start_date",
        "end_date",
        "price_paid",
        "auto_renew",
        "status",
        "cancelled_at",
        "cancellation_reason",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "start_date" => "datetime",
        "end_date" => "datetime",
        "price_paid" => "decimal:2",
        "auto_renew" => "boolean",
        "cancelled_at" => "datetime",
    ];
    
    /**
     * 获取关联的会员等级
     */
    public function level()
    {
        return $this->belongsTo(MembershipLevel::class, "membership_level_id");
    }
    
    /**
     * 获取关联的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * 判断订阅是否有效
     * 
     * @return bool
     */
    public function isActive()
    {
        return $this->status == "active" && $this->end_date > now();
    }
    
    /**
     * 判断订阅是否已过期
     * 
     * @return bool
     */
    public function isExpired()
    {
        return $this->end_date <= now();
    }
    
    /**
     * 判断订阅是否即将过期（7天内）
     * 
     * @return bool
     */
    public function isExpiringSoon()
    {
        return $this->status == "active" && 
               $this->end_date > now() && 
               $this->end_date <= now()->addDays(7);
    }
    
    /**
     * 计算剩余天数
     * 
     * @return int
     */
    public function daysRemaining()
    {
        if ($this->isExpired()) {
            return 0;
        }
        
        return now()->diffInDays($this->end_date);
    }
}
