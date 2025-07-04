<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotaUsage extends Model
{
    use HasFactory;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'quota_usages';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'user_id',
        'quota_type',
        'amount',
        'description',
        'used_at',
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected \ = [
        'amount' => 'integer',
        'used_at' => 'datetime',
    ];

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return \->belongsTo(User::class);
    }
}
