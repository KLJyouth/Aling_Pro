<?php

namespace App\Models\Security\ApiControl;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Admin\Management\AdminUser;

class ApiRiskEvent extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'api_risk_events';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'user_id',
        'rule_id',
        'event_type',
        'risk_level',
        'risk_score',
        'description',
        'trigger_data',
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
        'trigger_data' => 'array',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取关联的用户
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return \->belongsTo(User::class, 'user_id');
    }

    /**
     * 获取关联的风险规则
     *
     * @return BelongsTo
     */
    public function rule(): BelongsTo
    {
        return \->belongsTo(ApiRiskRule::class, 'rule_id');
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
     * 获取待处理事件的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending(\)
    {
        return \->where('status', 'pending');
    }

    /**
     * 获取已处理事件的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProcessed(\)
    {
        return \->where('status', 'processed');
    }

    /**
     * 获取指定风险等级的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @param string \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRiskLevel(\, string \)
    {
        return \->where('risk_level', \);
    }

    /**
     * 获取高风险事件的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighRisk(\)
    {
        return \->whereIn('risk_level', ['high', 'critical']);
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
     * 检查事件是否待处理
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return \->status === 'pending';
    }

    /**
     * 检查事件是否已处理
     *
     * @return bool
     */
    public function isProcessed(): bool
    {
        return \->status === 'processed';
    }

    /**
     * 检查事件是否被忽略
     *
     * @return bool
     */
    public function isIgnored(): bool
    {
        return \->status === 'ignored';
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
     * 检查事件是否为高风险
     *
     * @return bool
     */
    public function isHighRisk(): bool
    {
        return in_array(\->risk_level, ['high', 'critical']);
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
     * 记录风险事件
     *
     * @param int|null \
     * @param int \
     * @param string \
     * @param string \
     * @param array \
     * @param string|null \
     * @param string|null \
     * @return self
     */
    public static function recordEvent(
        ?int \,
        int \,
        string \,
        string \,
        array \,
        ?string \ = null,
        ?string \ = null
    ): self {
        // 计算风险分数
        \ = self::calculateRiskScore(\);
        
        return self::create([
            'user_id' => \,
            'rule_id' => \,
            'event_type' => \,
            'risk_level' => \,
            'risk_score' => \,
            'description' => \,
            'trigger_data' => \,
            'action_taken' => \,
            'status' => 'pending',
        ]);
    }

    /**
     * 根据风险等级计算风险分数
     *
     * @param string \
     * @return int
     */
    protected static function calculateRiskScore(string \): int
    {
        switch (\) {
            case 'critical':
                return 90 + rand(0, 10);
            case 'high':
                return 70 + rand(0, 20);
            case 'medium':
                return 40 + rand(0, 30);
            case 'low':
                return 10 + rand(0, 30);
            default:
                return 0;
        }
    }
}
