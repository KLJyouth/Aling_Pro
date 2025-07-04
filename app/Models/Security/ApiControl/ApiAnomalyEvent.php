<?php

namespace App\Models\Security\ApiControl;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin\Management\AdminUser;

class ApiAnomalyEvent extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'api_anomaly_events';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'config_id',
        'interface_id',
        'event_type',
        'observed_value',
        'expected_value',
        'deviation',
        'severity',
        'context_data',
        'action_taken',
        'status',
        'processed_by',
        'processed_at',
        'notes',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected \ = [
        'observed_value' => 'decimal:4',
        'expected_value' => 'decimal:4',
        'deviation' => 'decimal:4',
        'context_data' => 'array',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取关联的异常配置
     *
     * @return BelongsTo
     */
    public function config(): BelongsTo
    {
        return \->belongsTo(ApiAnomalyConfig::class, 'config_id');
    }

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
     * 获取处理该事件的管理员
     *
     * @return BelongsTo
     */
    public function processor(): BelongsTo
    {
        return \->belongsTo(AdminUser::class, 'processed_by');
    }

    /**
     * 获取未处理事件的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpen(\)
    {
        return \->where('status', 'open');
    }

    /**
     * 获取调查中事件的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInvestigating(\)
    {
        return \->where('status', 'investigating');
    }

    /**
     * 获取已解决事件的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeResolved(\)
    {
        return \->where('status', 'resolved');
    }

    /**
     * 获取误报事件的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFalsePositive(\)
    {
        return \->where('status', 'false_positive');
    }

    /**
     * 获取指定严重性事件的查询作用域
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
     * 获取高严重性事件的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighSeverity(\)
    {
        return \->whereIn('severity', ['high', 'critical']);
    }

    /**
     * 获取指定事件类型的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @param string \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEventType(\, string \)
    {
        return \->where('event_type', \);
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

    /**
     * 检查事件是否未处理
     *
     * @return bool
     */
    public function isOpen(): bool
    {
        return \->status === 'open';
    }

    /**
     * 检查事件是否调查中
     *
     * @return bool
     */
    public function isInvestigating(): bool
    {
        return \->status === 'investigating';
    }

    /**
     * 检查事件是否已解决
     *
     * @return bool
     */
    public function isResolved(): bool
    {
        return \->status === 'resolved';
    }

    /**
     * 检查事件是否为误报
     *
     * @return bool
     */
    public function isFalsePositive(): bool
    {
        return \->status === 'false_positive';
    }

    /**
     * 检查事件是否为高严重性
     *
     * @return bool
     */
    public function isHighSeverity(): bool
    {
        return in_array(\->severity, ['high', 'critical']);
    }

    /**
     * 处理事件
     *
     * @param int \
     * @param string \
     * @param string|null \
     * @return bool
     */
    public function process(int \, string \, ?string \ = null): bool
    {
        \->processed_by = \;
        \->processed_at = now();
        \->status = \;
        
        if (\) {
            \->notes = \;
        }
        
        return \->save();
    }

    /**
     * 记录异常事件
     *
     * @param int \
     * @param int|null \
     * @param string \
     * @param float \
     * @param float|null \
     * @param float|null \
     * @param string \
     * @param array|null \
     * @param string|null \
     * @return self
     */
    public static function recordEvent(
        int \,
        ?int \,
        string \,
        float \,
        ?float \ = null,
        ?float \ = null,
        string \ = 'medium',
        ?array \ = null,
        ?string \ = null
    ): self {
        return self::create([
            'config_id' => \,
            'interface_id' => \,
            'event_type' => \,
            'observed_value' => \,
            'expected_value' => \,
            'deviation' => \,
            'severity' => \,
            'context_data' => \,
            'action_taken' => \,
            'status' => 'open',
        ]);
    }
}
