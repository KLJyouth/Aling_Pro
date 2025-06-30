<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipSubscription extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * ��������ֵ������
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
     * Ӧ�ñ�ת��������
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
     * ��ȡ���ĵ��û�
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * ��ȡ���ĵĻ�Ա�ȼ�
     */
    public function membershipLevel()
    {
        return $this->belongsTo(MembershipLevel::class);
    }
    
    /**
     * ��ȡ���ĵĶ���
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * ��鶩���Ƿ���Ч
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === "active" && $this->end_date > now();
    }
    
    /**
     * ��鶩���Ƿ񼴽����ڣ�7���ڣ�
     *
     * @return bool
     */
    public function isExpiringSoon()
    {
        return $this->isActive() && $this->end_date->diffInDays(now()) <= 7;
    }
    
    /**
     * ��鶩���Ƿ��ѹ���
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->end_date <= now();
    }
    
    /**
     * ȡ������
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
     * �ӳ���������
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
     * ����Ψһ���ı��
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
