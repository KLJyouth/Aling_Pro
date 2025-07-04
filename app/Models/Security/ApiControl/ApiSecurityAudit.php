<?php

namespace App\Models\Security\ApiControl;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin\Management\AdminUser;

class ApiSecurityAudit extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'api_security_audits';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'interface_id',
        'audit_type',
        'severity',
        'description',
        'details',
        'status',
        'assigned_to',
        'resolved_at',
        'resolution',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected \ = [
        'details' => 'array',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取关联的API接口
     *
     * @return BelongsTo
     */
    public function interface(): BelongsTo
    {
        return \->belongsTo(ApiInterface::class, 'interface_id');
    }

    /**
     * 获取分配处理该审计的管理员
     *
     * @return BelongsTo
     */
    public function assignee(): BelongsTo
    {
        return \->belongsTo(AdminUser::class, 'assigned_to');
    }

    /**
     * 获取未解决审计的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpen(\)
    {
        return \->where('status', 'open');
    }

    /**
     * 获取处理中审计的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInProgress(\)
    {
        return \->where('status', 'in_progress');
    }

    /**
     * 获取已解决审计的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeResolved(\)
    {
        return \->where('status', 'resolved');
    }

    /**
     * 获取已关闭审计的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClosed(\)
    {
        return \->where('status', 'closed');
    }

    /**
     * 获取指定严重性审计的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @param string \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSeverity(\, string \)
    {
        return \->where('severity', \);
    }

    /**
     * 获取高严重性审计的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighSeverity(\)
    {
        return \->whereIn('severity', ['high', 'critical']);
    }

    /**
     * 获取指定审计类型的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @param string \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAuditType(\, string \)
    {
        return \->where('audit_type', \);
    }

    /**
     * 检查审计是否未解决
     *
     * @return bool
     */
    public function isOpen(): bool
    {
        return \->status === 'open';
    }

    /**
     * 检查审计是否处理中
     *
     * @return bool
     */
    public function isInProgress(): bool
    {
        return \->status === 'in_progress';
    }

    /**
     * 检查审计是否已解决
     *
     * @return bool
     */
    public function isResolved(): bool
    {
        return \->status === 'resolved';
    }

    /**
     * 检查审计是否已关闭
     *
     * @return bool
     */
    public function isClosed(): bool
    {
        return \->status === 'closed';
    }

    /**
     * 检查审计是否为高严重性
     *
     * @return bool
     */
    public function isHighSeverity(): bool
    {
        return in_array(\->severity, ['high', 'critical']);
    }

    /**
     * 分配审计给管理员
     *
     * @param int \
     * @return bool
     */
    public function assignTo(int \): bool
    {
        \->assigned_to = \;
        \->status = 'in_progress';
        return \->save();
    }

    /**
     * 解决审计
     *
     * @param string \
     * @return bool
     */
    public function resolve(string \): bool
    {
        \->resolution = \;
        \->resolved_at = now();
        \->status = 'resolved';
        return \->save();
    }

    /**
     * 关闭审计
     *
     * @return bool
     */
    public function close(): bool
    {
        \->status = 'closed';
        return \->save();
    }

    /**
     * 记录安全审计
     *
     * @param int|null \
     * @param string \
     * @param string \
     * @param string \
     * @param array|null \
     * @param int|null \
     * @return self
     */
    public static function recordAudit(
        ?int \,
        string \,
        string \,
        string \,
        ?array \ = null,
        ?int \ = null
    ): self {
        return self::create([
            'interface_id' => \,
            'audit_type' => \,
            'severity' => \,
            'description' => \,
            'details' => \,
            'status' => \ ? 'in_progress' : 'open',
            'assigned_to' => \,
        ]);
    }
}
