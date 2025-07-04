<?php

namespace App\Models\Security\ApiControl;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiAnomalyConfig extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'api_anomaly_configs';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'name',
        'interface_id',
        'metric_type',
        'detection_method',
        'parameters',
        'sensitivity',
        'action',
        'status',
        'description',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected \ = [
        'parameters' => 'array',
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
     * 获取关联的异常事件
     *
     * @return HasMany
     */
    public function anomalyEvents(): HasMany
    {
        return \->hasMany(ApiAnomalyEvent::class, 'config_id');
    }

    /**
     * 获取活跃配置的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(\)
    {
        return \->where('status', 'active');
    }

    /**
     * 获取指定指标类型的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @param string \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMetricType(\, string \)
    {
        return \->where('metric_type', \);
    }

    /**
     * 获取指定检测方法的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @param string \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDetectionMethod(\, string \)
    {
        return \->where('detection_method', \);
    }

    /**
     * 检查配置是否活跃
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return \->status === 'active';
    }

    /**
     * 检测异常
     *
     * @param float \
     * @param float|null \
     * @param array|null \
     * @return array|null 如果检测到异常，返回异常信息，否则返回null
     */
    public function detectAnomaly(float \, ?float \ = null, ?array \ = null): ?array
    {
        if (!->isActive()) {
            return null;
        }

        \ = false;
        \ = 0;
        \ = \->parameters['threshold'] ?? 0;
        \ = \->getSensitivityFactor();

        switch (\->detection_method) {
            case 'threshold':
                \ = \->detectThresholdAnomaly(\, \);
                \ = \ - \;
                break;
                
            case 'z_score':
                \ = \->parameters['mean'] ?? \ ?? 0;
                \ = \->parameters['std_dev'] ?? 1;
                \ = \ > 0 ? abs((\ - \) / \) : 0;
                \ = \ > \ * \;
                \ = \;
                break;
                
            case 'mad':
                \ = \->parameters['median'] ?? \ ?? 0;
                \ = \->parameters['mad'] ?? 1;
                \ = \ > 0 ? abs((\ - \) / \) : 0;
                \ = \ > \ * \;
                \ = \;
                break;
                
            case 'ewma':
                \ = \->parameters['ewma'] ?? \ ?? 0;
                \ = \->parameters['alpha'] ?? 0.3;
                \ = \ * \ + (1 - \) * \;
                \ = abs(\ - \) > \ * \;
                \ = abs(\ - \);
                break;
        }

        if (\) {
            \ = \->calculateSeverity(\, \, \);
            
            return [
                'is_anomaly' => true,
                'observed_value' => \,
                'expected_value' => \,
                'deviation' => \,
                'severity' => \,
                'action' => \->action,
            ];
        }

        return null;
    }

    /**
     * 检测阈值异常
     *
     * @param float \
     * @param float \
     * @return bool
     */
    protected function detectThresholdAnomaly(float \, float \): bool
    {
        \ = \->parameters['operator'] ?? '>';
        
        switch (\) {
            case '>':
                return \ > \;
            case '>=':
                return \ >= \;
            case '<':
                return \ < \;
            case '<=':
                return \ <= \;
            case '=':
            case '==':
                return \ == \;
            case '!=':
                return \ != \;
            default:
                return false;
        }
    }

    /**
     * 获取敏感度因子
     *
     * @return float
     */
    protected function getSensitivityFactor(): float
    {
        switch (\->sensitivity) {
            case 'low':
                return 1.5;
            case 'medium':
                return 1.0;
            case 'high':
                return 0.5;
            default:
                return 1.0;
        }
    }

    /**
     * 计算异常严重性
     *
     * @param float \
     * @param float \
     * @param float \
     * @return string
     */
    protected function calculateSeverity(float \, float \, float \): string
    {
        \ = \ > 0 ? \ / (\ * \) : 0;
        
        if (\ > 5) {
            return 'critical';
        } elseif (\ > 3) {
            return 'high';
        } elseif (\ > 1.5) {
            return 'medium';
        } else {
            return 'low';
        }
    }
}
