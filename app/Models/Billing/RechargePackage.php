<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RechargePackage extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "recharge_packages";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "name",
        "description",
        "amount",
        "original_amount",
        "bonus_points",
        "icon",
        "is_popular",
        "is_limited_time",
        "limited_start_at",
        "limited_end_at",
        "sort_order",
        "status",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "amount" => "decimal:2",
        "original_amount" => "decimal:2",
        "bonus_points" => "integer",
        "is_popular" => "boolean",
        "is_limited_time" => "boolean",
        "limited_start_at" => "datetime",
        "limited_end_at" => "datetime",
        "sort_order" => "integer",
        "status" => "integer",
    ];
    
    /**
     * 获取折扣率
     *
     * @return float|null
     */
    public function getDiscountRateAttribute()
    {
        if ($this->original_amount && $this->original_amount > 0) {
            return round(($this->original_amount - $this->amount) / $this->original_amount * 100);
        }
        
        return null;
    }
    
    /**
     * 判断是否为限时优惠
     *
     * @return bool
     */
    public function isLimitedTimeOffer()
    {
        if (!$this->is_limited_time) {
            return false;
        }
        
        $now = now();
        
        return (!$this->limited_start_at || $now >= $this->limited_start_at) && 
               (!$this->limited_end_at || $now <= $this->limited_end_at);
    }
    
    /**
     * 获取剩余时间（秒）
     *
     * @return int|null
     */
    public function getRemainingTimeAttribute()
    {
        if (!$this->is_limited_time || !$this->limited_end_at) {
            return null;
        }
        
        $now = now();
        
        if ($now > $this->limited_end_at) {
            return 0;
        }
        
        return $now->diffInSeconds($this->limited_end_at);
    }
}
