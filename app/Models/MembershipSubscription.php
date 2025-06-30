<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipSubscription extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * 可批量赋值的属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "user_id",
        "membership_level_id",
        "order_id",
        "subscription_no",
        "start_date",
        "end_date",
        "price_paid",
        "subscription_type",
        "auto_renew",
        "status",
        "cancelled_at",
        "cancellation_reason",
    ];
    
    /**
     * 应该被转换的属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        "start_date" => "datetime",
        "end_date" => "datetime",
        "price_paid" => "float",
        "auto_renew" => "boolean",
        "cancelled_at" => "datetime",
    ];
    
    /**
     * 获取订阅的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * 获取订阅的会员等级
     */
    public function membershipLevel()
    {
        return $this->belongsTo(MembershipLevel::class);
    }
    
    /**
     * 获取订阅的订单
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * 检查订阅是否有效
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === "active" && $this->end_date > now();
    }
    
    /**
     * 检查订阅是否即将过期（7天内）
     *
     * @return bool
     */
    public function isExpiringSoon()
    {
        return $this->isActive() && $this->end_date->diffInDays(now()) <= 7;
    }
    
    /**
     * 检查订阅是否已过期
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->end_date <= now();
    }
    
    /**
     * 取消订阅
     *
     * @param string|null $reason
     * @return bool
     */
    public function cancel($reason = null)
    {
        $this->status = "cancelled";
        $this->cancelled_at = now();
        $this->cancellation_reason = $reason;
        $this->auto_renew = false;
        
        return $this->save();
    }
    
    /**
     * 延长订阅期限
     *
     * @param int $days
     * @return bool
     */
    public function extend($days)
    {
        $this->end_date = $this->end_date->addDays($days);
        
        return $this->save();
    }
    
    /**
     * 生成唯一订阅编号
     *
     * @return string
     */
    public static function generateSubscriptionNumber()
    {
        $prefix = "SUB";
        $timestamp = now()->format("YmdHis");
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));
        
        return $prefix . $timestamp . $random;
    }
}
