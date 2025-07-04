<?php

namespace App\Models\Billing;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'billing_products';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'package_id',
        'name',
        'code',
        'description',
        'price',
        'original_price',
        'image',
        'stock',
        'sales_count',
        'is_virtual',
        'is_featured',
        'is_limited',
        'limited_stock',
        'start_time',
        'end_time',
        'sort_order',
        'status',
        'metadata',
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected \ = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'stock' => 'integer',
        'sales_count' => 'integer',
        'is_virtual' => 'boolean',
        'is_featured' => 'boolean',
        'is_limited' => 'boolean',
        'limited_stock' => 'integer',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'sort_order' => 'integer',
        'metadata' => 'json',
    ];
}
