<?php

namespace App\Models\Security\ApiControl;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Admin\Management\AdminUser;

class ApiRiskRule extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'api_risk_rules';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'name',
        'rule_type',
        'conditions',
        'threshold_value',
        'time_window',
        'action',
        'priority',
        'status',
        'description',
        'created_by',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected \ = [
        'conditions' => 'array',
        'threshold_value' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取创建该规则的管理员
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return \->belongsTo(AdminUser::class, 'created_by');
    }

    /**
     * 获取关联的风险事件
     *
     * @return HasMany
     */
    public function riskEvents(): HasMany
    {
        return \->hasMany(ApiRiskEvent::class, 'rule_id');
    }

    /**
     * 获取活跃规则的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(\)
    {
        return \->where('status', 'active');
    }

    /**
     * 获取指定类型规则的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @param string \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeType(\, string \)
    {
        return \->where('rule_type', \);
    }

    /**
     * 获取按优先级排序的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePrioritized(\)
    {
        return \->orderBy('priority', 'desc');
    }

    /**
     * 检查规则是否活跃
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return \->status === 'active';
    }

    /**
     * 评估数据是否触发规则
     *
     * @param array \
     * @return bool
     */
    public function evaluate(array \): bool
    {
        if (!->isActive()) {
            return false;
        }

        // 根据不同规则类型进行评估
        switch (\->rule_type) {
            case 'frequency':
                return \->evaluateFrequency(\);
            case 'amount':
                return \->evaluateAmount(\);
            case 'behavior':
                return \->evaluateBehavior(\);
            case 'ip':
                return \->evaluateIp(\);
            case 'device':
                return \->evaluateDevice(\);
            case 'geo':
                return \->evaluateGeo(\);
            default:
                return false;
        }
    }

    /**
     * 评估频率规则
     *
     * @param array \
     * @return bool
     */
    protected function evaluateFrequency(array \): bool
    {
        if (!isset(\['count']) || !isset(\->threshold_value)) {
            return false;
        }
        
        return \['count'] >= \->threshold_value;
    }

    /**
     * 评估金额规则
     *
     * @param array \
     * @return bool
     */
    protected function evaluateAmount(array \): bool
    {
        if (!isset(\['amount']) || !isset(\->threshold_value)) {
            return false;
        }
        
        return \['amount'] >= \->threshold_value;
    }

    /**
     * 评估行为规则
     *
     * @param array \
     * @return bool
     */
    protected function evaluateBehavior(array \): bool
    {
        if (empty(\->conditions) || !isset(\['behaviors'])) {
            return false;
        }
        
        \ = 0;
        foreach (\->conditions as \) {
            if (in_array(\, \['behaviors'])) {
                \++;
            }
        }
        
        return \ >= (\->threshold_value ?? count(\->conditions));
    }

    /**
     * 评估IP规则
     *
     * @param array \
     * @return bool
     */
    protected function evaluateIp(array \): bool
    {
        if (empty(\->conditions) || !isset(\['ip_address'])) {
            return false;
        }
        
        foreach (\->conditions as \) {
            if (\->matchIpPattern(\['ip_address'], \)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 评估设备规则
     *
     * @param array \
     * @return bool
     */
    protected function evaluateDevice(array \): bool
    {
        if (empty(\->conditions) || !isset(\['device_info'])) {
            return false;
        }
        
        foreach (\->conditions as \) {
            if (stripos(\['device_info'], \) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 评估地理位置规则
     *
     * @param array \
     * @return bool
     */
    protected function evaluateGeo(array \): bool
    {
        if (empty(\->conditions)) {
            return false;
        }
        
        foreach (\->conditions as \ => \) {
            if (!isset(\[\]) || \[\] !== \) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * 匹配IP模式
     *
     * @param string \
     * @param string \
     * @return bool
     */
    protected function matchIpPattern(string \, string \): bool
    {
        // 精确匹配
        if (\ === \) {
            return true;
        }
        
        // CIDR匹配
        if (strpos(\, '/') !== false) {
            return \->ipInCidr(\, \);
        }
        
        // 通配符匹配
        if (strpos(\, '*') !== false) {
            \ = '/^' . str_replace(['*', '.'], ['[0-9]+', '\\.'], \) . '$/';
            return preg_match(\, \) === 1;
        }
        
        return false;
    }

    /**
     * 检查IP是否在CIDR范围内
     *
     * @param string \
     * @param string \
     * @return bool
     */
    protected function ipInCidr(string \, string \): bool
    {
        list(\, \) = explode('/', \);
        \ = ip2long(\);
        \ = ip2long(\);
        \ = -1 << (32 - \);
        \ &= \;
        
        return (\ & \) === \;
    }
}
