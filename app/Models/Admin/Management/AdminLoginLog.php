<?php

namespace App\Models\Admin\Management;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminLoginLog extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'admin_login_logs';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'admin_id',
        'ip_address',
        'user_agent',
        'device_type',
        'location',
        'status',
        'failure_reason',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected \ = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取关联的管理员
     *
     * @return BelongsTo
     */
    public function admin(): BelongsTo
    {
        return \->belongsTo(AdminUser::class, 'admin_id');
    }

    /**
     * 获取成功登录的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuccess(\)
    {
        return \->where('status', 'success');
    }

    /**
     * 获取失败登录的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed(\)
    {
        return \->where('status', 'failed');
    }

    /**
     * 获取指定时间段内的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @param string \
     * @param string|null \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates(\, string \, ?string \ = null)
    {
        if (\) {
            return \->whereBetween('created_at', [\, \]);
        }
        
        return \->where('created_at', '>=', \);
    }
}
