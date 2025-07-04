<?php

namespace App\Models\Admin\Management;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AdminPermission extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'admin_permissions';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'name',
        'display_name',
        'module',
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
     * 获取关联的权限组
     *
     * @return BelongsToMany
     */
    public function groups(): BelongsToMany
    {
        return \->belongsToMany(
            AdminPermissionGroup::class,
            'admin_permission_group_items',
            'permission_id',
            'group_id'
        );
    }

    /**
     * 获取指定模块的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @param string \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeModule(\, string \)
    {
        return \->where('module', \);
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
     * 按模块分组获取所有权限
     *
     * @return array
     */
    public static function getGroupedByModule(): array
    {
        \ = self::orderBy('module')
            ->orderBy('sort_order')
            ->get();
        
        \ = [];
        foreach (\ as \) {
            \[\->module][] = \;
        }
        
        return \;
    }
}
