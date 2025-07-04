<?php

namespace App\Models\Admin\Management;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminUser extends Authenticatable
{
    use Notifiable, SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'admin_users';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'username',
        'email',
        'phone',
        'password',
        'avatar',
        'status',
        'role_id',
        'last_login_at',
        'last_login_ip',
        'login_count',
        'risk_level',
        'metadata',
    ];

    /**
     * 应该被隐藏的属性
     *
     * @var array
     */
    protected \ = [
        'password',
        'remember_token',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected \ = [
        'metadata' => 'array',
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 获取管理员关联的角色
     *
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return \->belongsTo(AdminRole::class, 'role_id');
    }

    /**
     * 获取管理员的登录日志
     *
     * @return HasMany
     */
    public function loginLogs(): HasMany
    {
        return \->hasMany(AdminLoginLog::class, 'admin_id');
    }

    /**
     * 获取管理员的操作日志
     *
     * @return HasMany
     */
    public function operationLogs(): HasMany
    {
        return \->hasMany(AdminOperationLog::class, 'admin_id');
    }

    /**
     * 检查管理员是否有指定权限
     *
     * @param string \
     * @return bool
     */
    public function hasPermission(string \): bool
    {
        if (\->isSuperAdmin()) {
            return true;
        }

        return \->role && \->role->hasPermission(\);
    }

    /**
     * 检查管理员是否有指定权限组中的任一权限
     *
     * @param array \
     * @return bool
     */
    public function hasAnyPermission(array \): bool
    {
        if (\->isSuperAdmin()) {
            return true;
        }

        return \->role && \->role->hasAnyPermission(\);
    }

    /**
     * 检查管理员是否有指定权限组中的所有权限
     *
     * @param array \
     * @return bool
     */
    public function hasAllPermissions(array \): bool
    {
        if (\->isSuperAdmin()) {
            return true;
        }

        return \->role && \->role->hasAllPermissions(\);
    }

    /**
     * 检查管理员是否是超级管理员
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return \->role && \->role->name === 'super_admin';
    }

    /**
     * 检查管理员是否处于活跃状态
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return \->status === 'active';
    }

    /**
     * 记录登录信息
     *
     * @param string \
     * @param string|null \
     * @param string|null \
     * @param string|null \
     * @return void
     */
    public function recordLogin(string \, ?string \ = null, ?string \ = null, ?string \ = null): void
    {
        \->last_login_at = now();
        \->last_login_ip = \;
        \->login_count += 1;
        \->save();

        \->loginLogs()->create([
            'ip_address' => \,
            'user_agent' => \,
            'device_type' => \,
            'location' => \,
            'status' => 'success',
        ]);
    }

    /**
     * 记录登录失败
     *
     * @param string \
     * @param string|null \
     * @param string|null \
     * @param string|null \
     * @param string|null \
     * @return void
     */
    public function recordLoginFailure(string \, ?string \ = null, ?string \ = null, ?string \ = null, ?string \ = null): void
    {
        \->loginLogs()->create([
            'ip_address' => \,
            'user_agent' => \,
            'device_type' => \,
            'location' => \,
            'status' => 'failed',
            'failure_reason' => \,
        ]);
    }

    /**
     * 获取活跃管理员的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(\)
    {
        return \->where('status', 'active');
    }
}
