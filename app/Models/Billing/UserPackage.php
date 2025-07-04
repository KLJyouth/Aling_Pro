<?php

namespace App\Models\Billing;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPackage extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'user_packages';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'user_id',
        'package_id',
        'order_id',
        'quota_total',
        'quota_used',
        'quota_remaining',
        'start_date',
        'end_date',
        'status',
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected \ = [
        'quota_total' => 'integer',
        'quota_used' => 'integer',
        'quota_remaining' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];
}
