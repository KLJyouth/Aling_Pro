<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "payment_methods";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "name",
        "code",
        "description",
        "icon",
        "config",
        "fee_type",
        "fee_value",
        "min_amount",
        "max_amount",
        "sort_order",
        "status",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "config" => "json",
        "fee_value" => "decimal:2",
        "min_amount" => "decimal:2",
        "max_amount" => "decimal:2",
        "sort_order" => "integer",
        "status" => "integer",
    ];
    
    /**
     * 计算手续费
     *
     * @param float $amount
     * @return float
     */
    public function calculateFee($amount)
    {
        if ($this->fee_type === "fixed") {
            return $this->fee_value;
        } elseif ($this->fee_type === "percentage") {
            return $amount * ($this->fee_value / 100);
        }
        
        return 0;
    }
    
    /**
     * 检查金额是否在允许范围内
     *
     * @param float $amount
     * @return bool
     */
    public function isAmountInRange($amount)
    {
        if ($this->min_amount && $amount < $this->min_amount) {
            return false;
        }
        
        if ($this->max_amount && $amount > $this->max_amount) {
            return false;
        }
        
        return true;
    }
}
