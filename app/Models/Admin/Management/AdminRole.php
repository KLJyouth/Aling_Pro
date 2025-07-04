<?php

namespace App\Models\Admin\Management;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminRole extends Model
{
    use SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'admin_roles';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'name',
        'display_name',
        'permissions',
        'status',
        'description',
        'sort_order',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected \ = [
        'permissions' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 获取该角色下的所有管理员
     *
     * @return HasMany
     */
    public function adminUsers(): HasMany
    {
        return \->hasMany(AdminUser::class, 'role_id');
    }

    /**
     * 检查角色是否有指定权限
     *
     * @param string \
     * @return bool
     */
    public function hasPermission(string \): bool
    {
        if (empty(\->permissions)) {
            return false;
        }

        return in_array(\, \->permissions) || in_array('*', \->permissions);
    }

    /**
     * 检查角色是否有指定权限组中的任一权限
     *
     * @param array \
     * @return bool
     */
    public function hasAnyPermission(array \): bool
    {
        if (empty(\->permissions)) {
            return false;
        }

        if (in_array('*', \->permissions)) {
            return true;
        }

        foreach (\ as \) {
            if (in_array(\, \->permissions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 检查角色是否有指定权限组中的所有权限
     *
     * @param array \
     * @return bool
     */
    public function hasAllPermissions(array \): bool
    {
        if (empty(\->permissions)) {
            return false;
        }

        if (in_array('*', \->permissions)) {
            return true;
        }

        foreach (\ as \) {
            if (!in_array(\, \->permissions)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 获取活跃角色的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(\)
    {
        return \->where('status', 'active');
    }
}
