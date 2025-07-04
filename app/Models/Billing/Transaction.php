<?php

namespace App\Models\Billing;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "transactions";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "user_id",
        "type",
        "amount",
        "points",
        "status",
        "payment_method",
        "transaction_no",
        "reference_id",
        "metadata",
        "completed_at",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "amount" => "decimal:2",
        "points" => "integer",
        "metadata" => "json",
        "completed_at" => "datetime",
    ];

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * 获取关联的支付方式
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, "payment_method", "code");
    }
    
    /**
     * 获取格式化的状态
     *
     * @return string
     */
    public function getStatusTextAttribute()
    {
        $statusMap = [
            "pending" => "待处理",
            "processing" => "处理中",
            "success" => "成功",
            "failed" => "失败",
            "cancelled" => "已取消",
            "refunded" => "已退款",
        ];
        
        return $statusMap[$this->status] ?? $this->status;
    }
    
    /**
     * 获取格式化的类型
     *
     * @return string
     */
    public function getTypeTextAttribute()
    {
        $typeMap = [
            "recharge" => "充值",
            "consume" => "消费",
            "refund" => "退款",
            "transfer" => "转账",
            "reward" => "奖励",
            "adjustment" => "调整",
        ];
        
        return $typeMap[$this->type] ?? $this->type;
    }
    
    /**
     * 标记交易为成功
     *
     * @param array $metadata
     * @return bool
     */
    public function markAsSuccess($metadata = [])
    {
        if ($this->status === "success") {
            return true;
        }
        
        $this->status = "success";
        $this->completed_at = now();
        
        if (!empty($metadata)) {
            $this->metadata = array_merge($this->metadata ?? [], $metadata);
        }
        
        return $this->save();
    }
    
    /**
     * 标记交易为失败
     *
     * @param string $reason
     * @param array $metadata
     * @return bool
     */
    public function markAsFailed($reason = null, $metadata = [])
    {
        if ($this->status === "failed") {
            return true;
        }
        
        $this->status = "failed";
        
        $meta = $this->metadata ?? [];
        $meta["failure_reason"] = $reason;
        
        if (!empty($metadata)) {
            $meta = array_merge($meta, $metadata);
        }
        
        $this->metadata = $meta;
        
        return $this->save();
    }
}
