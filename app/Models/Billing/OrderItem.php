<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'billing_order_items';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'order_id',
        'product_id',
        'product_name',
        'product_code',
        'quantity',
        'price',
        'total',
        'metadata',
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected \ = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'metadata' => 'json',
    ];
}
