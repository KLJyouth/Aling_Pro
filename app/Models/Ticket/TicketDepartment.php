<?php

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 工单部门模型
 * 
 * 用于管理工单的部门分类
 */
class TicketDepartment extends Model
{
    use SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'ticket_departments';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'name',             // 部门名称
        'description',      // 部门描述
        'is_active',        // 是否激活
        'sort_order',       // 排序顺序
        'parent_id',        // 父部门ID
        'auto_assign_to',   // 自动分配给用户ID
        'settings',         // 部门设置 (JSON)
    ];

    /**
     * 应该被转换为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * 获取部门下的工单
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'department_id');
    }

    /**
     * 获取父部门
     */
    public function parent()
    {
        return $this->belongsTo(TicketDepartment::class, 'parent_id');
    }

    /**
     * 获取子部门
     */
    public function children()
    {
        return $this->hasMany(TicketDepartment::class, 'parent_id');
    }

    /**
     * 获取部门下的分类
     */
    public function categories()
    {
        return $this->hasMany(TicketCategory::class, 'department_id');
    }

    /**
     * 判断部门是否激活
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * 获取所有顶级部门
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id')->orWhere('parent_id', 0);
    }

    /**
     * 获取激活的部门
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 按排序顺序排序
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSorted($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }
} 