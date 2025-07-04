<?php

namespace App\Models\Billing;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWallet extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "user_wallets";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "user_id",
        "balance",
        "points",
        "frozen_balance",
        "total_recharge",
        "total_consume",
        "last_recharge_at",
        "last_consume_at",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "balance" => "decimal:2",
        "points" => "integer",
        "frozen_balance" => "decimal:2",
        "total_recharge" => "decimal:2",
        "total_consume" => "decimal:2",
        "last_recharge_at" => "datetime",
        "last_consume_at" => "datetime",
    ];

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取钱包交易记录
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, "user_id", "user_id");
    }
    
    /**
     * 增加余额
     *
     * @param float $amount
     * @param string $reason
     * @param array $metadata
     * @return bool
     */
    public function addBalance($amount, $reason = null, $metadata = [])
    {
        if ($amount <= 0) {
            return false;
        }
        
        $this->balance += $amount;
        $this->total_recharge += $amount;
        $this->last_recharge_at = now();
        
        return $this->save();
    }
    
    /**
     * 扣减余额
     *
     * @param float $amount
     * @param string $reason
     * @param array $metadata
     * @return bool
     */
    public function subtractBalance($amount, $reason = null, $metadata = [])
    {
        if ($amount <= 0 || $this->balance < $amount) {
            return false;
        }
        
        $this->balance -= $amount;
        $this->total_consume += $amount;
        $this->last_consume_at = now();
        
        return $this->save();
    }
    
    /**
     * 增加积分
     *
     * @param int $points
     * @param string $reason
     * @param array $metadata
     * @return bool
     */
    public function addPoints($points, $reason = null, $metadata = [])
    {
        if ($points <= 0) {
            return false;
        }
        
        $this->points += $points;
        
        return $this->save();
    }
    
    /**
     * 扣减积分
     *
     * @param int $points
     * @param string $reason
     * @param array $metadata
     * @return bool
     */
    public function subtractPoints($points, $reason = null, $metadata = [])
    {
        if ($points <= 0 || $this->points < $points) {
            return false;
        }
        
        $this->points -= $points;
        
        return $this->save();
    }
}
