<?php

namespace App\Models\Admin\Management;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminOperationLog extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'admin_operation_logs';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'admin_id',
        'module',
        'action',
        'method',
        'url',
        'request_data',
        'ip_address',
        'user_agent',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected \ = [
        'request_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取关联的管理员
     *
     * @return BelongsTo
     */
    public function admin(): BelongsTo
    {
        return \->belongsTo(AdminUser::class, 'admin_id');
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
     * 获取指定操作的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @param string \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAction(\, string \)
    {
        return \->where('action', \);
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
     * 记录管理员操作日志
     *
     * @param int \ 管理员ID
     * @param string \ 模块
     * @param string \ 操作
     * @param string \ 请求方法
     * @param string \ 请求URL
     * @param array \ 请求数据
     * @param string \ IP地址
     * @param string|null \ 用户代理
     * @return self
     */
    public static function record(
        int \,
        string \,
        string \,
        string \,
        string \,
        array \,
        string \,
        ?string \ = null
    ): self {
        // 过滤敏感数据
        \ = self::filterSensitiveData(\);
        
        return self::create([
            'admin_id' => \,
            'module' => \,
            'action' => \,
            'method' => \,
            'url' => \,
            'request_data' => \,
            'ip_address' => \,
            'user_agent' => \,
        ]);
    }

    /**
     * 过滤敏感数据
     *
     * @param array \
     * @return array
     */
    protected static function filterSensitiveData(array \): array
    {
        \ = ['password', 'password_confirmation', 'token', 'secret', 'credit_card'];
        
        foreach (\ as \ => \) {
            if (in_array(\, \)) {
                \[\] = '******';
            } elseif (is_array(\)) {
                \[\] = self::filterSensitiveData(\);
            }
        }
        
        return \;
    }
}
