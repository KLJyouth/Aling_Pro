<?php

namespace App\Models\Billing;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'billing_orders';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'user_id',
        'order_no',
        'total_amount',
        'payment_method',
        'payment_status',
        'transaction_id',
        'paid_at',
        'status',
        'remark',
        'client_ip',
        'user_agent',
        'metadata',
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected \ = [
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'metadata' => 'json',
    ];
}
