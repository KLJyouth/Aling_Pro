<?php

namespace App\Models\User\Analytics;

use Illuminate\Database\Eloquent\Model;

class UserGrowthStat extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'user_growth_stats';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'date',
        'new_users',
        'active_users',
        'returning_users',
        'churned_users',
        'verified_users',
        'retention_rate',
        'churn_rate',
    ];

    /**
     * 应该被转换为日期的属性
     *
     * @var array
     */
    protected \ = [
        'date',
        'created_at',
        'updated_at',
    ];

    /**
     * 更新指定日期的用户增长统计
     *
     * @param string \ 日期
     * @param array \ 统计数据
     * @return UserGrowthStat
     */
    public static function updateDailyStats(string \, array \): UserGrowthStat
    {
        \ = self::firstOrCreate(['date' => \], [
            'new_users' => 0,
            'active_users' => 0,
            'returning_users' => 0,
            'churned_users' => 0,
            'verified_users' => 0,
            'retention_rate' => null,
            'churn_rate' => null,
        ]);
        
        foreach (\ as \ => \) {
            if (in_array(\, ['new_users', 'active_users', 'returning_users', 'churned_users', 'verified_users', 'retention_rate', 'churn_rate'])) {
                \->\ = \;
            }
        }
        
        // 计算留存率和流失率（如果未提供）
        if (!isset(\['retention_rate']) && \->active_users > 0 && isset(\['returning_users'])) {
            \->retention_rate = (\->returning_users / \->active_users) * 100;
        }
        
        if (!isset(\['churn_rate']) && \->active_users > 0 && isset(\['churned_users'])) {
            \->churn_rate = (\->churned_users / \->active_users) * 100;
        }
        
        \->save();
        
        return \;
    }

    /**
     * 获取指定时间段的用户增长趋势
     *
     * @param string \ 开始日期
     * @param string \ 结束日期
     * @return array
     */
    public static function getGrowthTrend(string \, string \): array
    {
        \ = self::whereBetween('date', [\, \])
            ->orderBy('date')
            ->get();
        
        \ = [];
        \ = [];
        \ = [];
        \ = [];
        \ = [];
        
        foreach (\ as \) {
            \[] = \->date->format('Y-m-d');
            \[] = \->new_users;
            \[] = \->active_users;
            \[] = \->retention_rate;
            \[] = \->churn_rate;
        }
        
        return [
            'dates' => \,
            'new_users' => \,
            'active_users' => \,
            'retention_rates' => \,
            'churn_rates' => \,
        ];
    }
}
