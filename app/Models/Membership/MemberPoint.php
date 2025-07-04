<?php

namespace App\Models\Membership;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberPoint extends Model
{
    use HasFactory;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'member_points';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'user_id',
        'points',
        'action',
        'description',
        'reference_id',
        'reference_type',
        'expires_at',
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected \ = [
        'points' => 'integer',
        'expires_at' => 'datetime',
    ];

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return \->belongsTo(User::class);
    }
}
