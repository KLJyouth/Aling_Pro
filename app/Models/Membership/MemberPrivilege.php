<?php

namespace App\Models\Membership;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberPrivilege extends Model
{
    use HasFactory;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'member_privileges';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'name',
        'code',
        'description',
        'icon',
        'status',
        'is_featured',
        'sort_order',
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected \ = [
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * 获取拥有此特权的所有会员等级
     */
    public function levels()
    {
        return \->belongsToMany(MembershipLevel::class, 'member_privilege_level', 'privilege_id', 'level_id')
            ->withPivot('value')
            ->withTimestamps();
    }
}
