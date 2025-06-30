<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberReferral extends Model
{
    use HasFactory;
    
    /**
     * 可批量赋值的属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "referrer_id",
        "referred_id",
        "code",
        "status",
        "points_awarded",
        "reward_type",
        "reward_amount",
        "reward_description",
    ];
    
    /**
     * 应该被转换的属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        "points_awarded" => "integer",
        "reward_amount" => "float",
    ];
    
    /**
     * 获取推荐人
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, "referrer_id");
    }
    
    /**
     * 获取被推荐人
     */
    public function referred()
    {
        return $this->belongsTo(User::class, "referred_id");
    }
    
    /**
     * 检查推荐是否已完成
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->status === "completed";
    }
    
    /**
     * 检查推荐是否待处理
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status === "pending";
    }
}
