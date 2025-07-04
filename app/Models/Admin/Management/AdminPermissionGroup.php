<?php

namespace App\Models\Admin\Management;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AdminPermissionGroup extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'admin_permission_groups';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'name',
        'display_name',
        'description',
        'sort_order',
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
     * 获取关联的权限
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return \->belongsToMany(
            AdminPermission::class,
            'admin_permission_group_items',
            'group_id',
            'permission_id'
        );
    }

    /**
     * 按排序获取的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered(\)
    {
        return \->orderBy('sort_order')->orderBy('id');
    }

    /**
     * 获取权限组及其包含的权限
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getWithPermissions()
    {
        return self::with('permissions')->ordered()->get();
    }

    /**
     * 同步权限到权限组
     *
     * @param array \
     * @return array
     */
    public function syncPermissions(array \): array
    {
        return \->permissions()->sync(\);
    }
}
