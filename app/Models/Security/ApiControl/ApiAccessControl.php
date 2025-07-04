<?php

namespace App\Models\Security\ApiControl;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin\Management\AdminUser;

class ApiAccessControl extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'api_access_controls';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'interface_id',
        'control_type',
        'control_config',
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
        'control_config' => 'array',
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
     * 获取创建该访问控制的管理员
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return \->belongsTo(AdminUser::class, 'created_by');
    }

    /**
     * 获取活跃访问控制的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(\)
    {
        return \->where('status', 'active');
    }

    /**
     * 获取指定控制类型的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @param string \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeControlType(\, string \)
    {
        return \->where('control_type', \);
    }

    /**
     * 检查访问控制是否活跃
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return \->status === 'active';
    }

    /**
     * 评估请求是否符合访问控制规则
     *
     * @param array \
     * @return bool
     */
    public function evaluateAccess(array \): bool
    {
        if (!->isActive()) {
            return true;
        }

        switch (\->control_type) {
            case 'rate_limit':
                return \->evaluateRateLimit(\);
            case 'ip_restriction':
                return \->evaluateIpRestriction(\);
            case 'time_restriction':
                return \->evaluateTimeRestriction(\);
            case 'geo_restriction':
                return \->evaluateGeoRestriction(\);
            default:
                return true;
        }
    }

    /**
     * 评估速率限制
     *
     * @param array \
     * @return bool
     */
    protected function evaluateRateLimit(array \): bool
    {
        if (empty(\->control_config) || !isset(\['count']) || !isset(\->control_config['limit'])) {
            return true;
        }
        
        return \['count'] <= \->control_config['limit'];
    }

    /**
     * 评估IP限制
     *
     * @param array \
     * @return bool
     */
    protected function evaluateIpRestriction(array \): bool
    {
        if (empty(\->control_config) || !isset(\['ip_address'])) {
            return true;
        }
        
        \ = \->control_config['mode'] ?? 'blacklist';
        \ = \->control_config['ip_list'] ?? [];
        
        if (empty(\)) {
            return true;
        }
        
        \ = false;
        foreach (\ as \) {
            if (\->matchIpPattern(\['ip_address'], \)) {
                \ = true;
                break;
            }
        }
        
        return \ === 'whitelist' ? \ : !\;
    }

    /**
     * 评估时间限制
     *
     * @param array \
     * @return bool
     */
    protected function evaluateTimeRestriction(array \): bool
    {
        if (empty(\->control_config)) {
            return true;
        }
        
        \ = \['time'] ?? now();
        if (is_string(\)) {
            \ = new \DateTime(\);
        }
        
        \ = (int) \->format('H');
        \ = (int) \->format('N'); // 1 (Mon) to 7 (Sun)
        
        \ = \->control_config['hours'] ?? [];
        \ = \->control_config['days'] ?? [];
        
        // 如果未指定允许的小时或天，则默认全部允许
        if (empty(\) && empty(\)) {
            return true;
        }
        
        \ = empty(\) || in_array(\, \);
        \ = empty(\) || in_array(\, \);
        
        return \ && \;
    }

    /**
     * 评估地理位置限制
     *
     * @param array \
     * @return bool
     */
    protected function evaluateGeoRestriction(array \): bool
    {
        if (empty(\->control_config)) {
            return true;
        }
        
        \ = \->control_config['mode'] ?? 'blacklist';
        \ = \->control_config['countries'] ?? [];
        
        if (empty(\) || !isset(\['country'])) {
            return true;
        }
        
        \ = in_array(\['country'], \);
        
        return \ === 'whitelist' ? \ : !\;
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
