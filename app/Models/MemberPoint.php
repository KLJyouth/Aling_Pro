<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberPoint extends Model
{
    use HasFactory;
    
    /**
     * 可批量赋值的属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "user_id",
        "points",
        "action",
        "description",
        "reference_id",
        "reference_type",
        "expires_at",
    ];
    
    /**
     * 应该被转换的属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        "points" => "integer",
        "expires_at" => "datetime",
    ];
    
    /**
     * 获取积分所属用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * 检查积分是否已过期
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
