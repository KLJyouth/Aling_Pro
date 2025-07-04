<?php

namespace App\Models\User\Analytics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class UserBehaviorAnalytic extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'user_behavior_analytics';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'user_id',
        'feature_used',
        'action_type',
        'context',
        'user_agent',
        'ip_address',
        'device_type',
    ];

    /**
     * 应该被转换为JSON的属性
     *
     * @var array
     */
    protected \ = [
        'context' => 'array',
    ];

    /**
     * 获取关联的用户
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return \->belongsTo(User::class);
    }

    /**
     * 记录用户行为
     *
     * @param int \ 用户ID
     * @param string \ 使用的功能
     * @param string \ 操作类型
     * @param array \ 上下文信息
     * @param array \ 客户端信息
     * @return UserBehaviorAnalytic
     */
    public static function recordBehavior(
        int \,
        string \,
        string \,
        array \ = [],
        array \ = []
    ): UserBehaviorAnalytic {
        return self::create([
            'user_id' => \,
            'feature_used' => \,
            'action_type' => \,
            'context' => \,
            'user_agent' => \['user_agent'] ?? null,
            'ip_address' => \['ip_address'] ?? null,
            'device_type' => \['device_type'] ?? null,
        ]);
    }

    /**
     * 获取用户行为模式
     *
     * @param int \ 用户ID
     * @param int \ 天数
     * @return array
     */
    public static function getUserBehaviorPattern(int \, int \ = 30): array
    {
        \ = now()->subDays(\);
        
        \ = self::where('user_id', \)
            ->where('created_at', '>=', \)
            ->get();
        
        \ = [];
        \ = [];
        \ = [];
        
        foreach (\ as \) {
            // 统计功能使用情况
            if (!isset(\[\->feature_used])) {
                \[\->feature_used] = 0;
            }
            \[\->feature_used]++;
            
            // 统计操作类型
            if (!isset(\[\->action_type])) {
                \[\->action_type] = 0;
            }
            \[\->action_type]++;
            
            // 统计时间分布
            \ = \->created_at->format('H');
            if (!isset(\[\])) {
                \[\] = 0;
            }
            \[\]++;
        }
        
        return [
            'feature_usage' => \,
            'action_types' => \,
            'time_distribution' => \,
            'total_behaviors' => \->count(),
        ];
    }
}
