<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberPrivilege extends Model
{
    use HasFactory;
    
    /**
     * 可批量赋值的属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "name",
        "code",
        "description",
        "icon",
        "status",
        "is_featured",
        "sort_order",
    ];
    
    /**
     * 应该被转换的属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        "is_featured" => "boolean",
        "sort_order" => "integer",
    ];
    
    /**
     * 获取拥有此特权的会员等级
     */
    public function membershipLevels()
    {
        return $this->belongsToMany(MembershipLevel::class, "member_privilege_level", "privilege_id", "level_id")
            ->withPivot("value")
            ->withTimestamps();
    }
}
