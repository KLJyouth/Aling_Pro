<?php

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 工单分类模型
 * 
 * 用于管理工单的分类
 */
class TicketCategory extends Model
{
    use SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "ticket_categories";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "name",             // 分类名称
        "description",      // 分类描述
        "department_id",    // 所属部门ID
        "is_active",        // 是否激活
        "sort_order",       // 排序顺序
        "parent_id",        // 父分类ID
        "auto_assign_to",   // 自动分配给用户ID
        "settings",         // 分类设置 (JSON)
    ];

    /**
     * 应该被转换为日期的属性
     *
     * @var array
     */
    protected $dates = [
        "created_at",
        "updated_at",
        "deleted_at",
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "settings" => "array",
        "is_active" => "boolean",
    ];

    /**
     * 获取分类下的工单
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, "category_id");
    }

    /**
     * 获取分类所属的部门
     */
    public function department()
    {
        return $this->belongsTo(TicketDepartment::class, "department_id");
    }

    /**
     * 获取父分类
     */
    public function parent()
    {
        return $this->belongsTo(TicketCategory::class, "parent_id");
    }

    /**
     * 获取子分类
     */
    public function children()
    {
        return $this->hasMany(TicketCategory::class, "parent_id");
    }

    /**
     * 判断分类是否激活
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * 获取所有顶级分类
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull("parent_id")->orWhere("parent_id", 0);
    }

    /**
     * 获取激活的分类
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where("is_active", true);
    }

    /**
     * 按排序顺序排序
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSorted($query)
    {
        return $query->orderBy("sort_order", "asc")->orderBy("name", "asc");
    }

    /**
     * 获取指定部门的分类
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $departmentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where("department_id", $departmentId);
    }
}
