<?php

namespace App\Models\Billing;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'billing_packages';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'name',
        'code',
        'description',
        'type',
        'quota',
        'price',
        'original_price',
        'duration_days',
        'features',
        'is_popular',
        'is_recommended',
        'sort_order',
        'status',
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected \ = [
        'quota' => 'integer',
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'duration_days' => 'integer',
        'features' => 'json',
        'is_popular' => 'boolean',
        'is_recommended' => 'boolean',
        'sort_order' => 'integer',
    ];
}
