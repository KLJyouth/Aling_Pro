<?php

namespace App\Models\User\Analytics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class UserResourceStat extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'user_resource_stats';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'user_id',
        'date',
        'storage_used',
        'files_count',
        'memories_count',
        'conversations_count',
        'tokens_used',
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
     * 获取关联的用户
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return \->belongsTo(User::class);
    }

    /**
     * 更新用户资源使用统计
     *
     * @param int \ 用户ID
     * @param array \ 资源使用数据
     * @return bool
     */
    public static function updateResourceUsage(int \, array \): bool
    {
        \ = now()->toDateString();
        
        // 查找或创建今天的记录
        \ = self::firstOrCreate(
            ['user_id' => \, 'date' => \],
            [
                'storage_used' => 0,
                'files_count' => 0,
                'memories_count' => 0,
                'conversations_count' => 0,
                'tokens_used' => 0,
            ]
        );
        
        // 更新资源使用情况
        foreach (\ as \ => \) {
            if (in_array(\, ['storage_used', 'files_count', 'memories_count', 'conversations_count', 'tokens_used'])) {
                \->\ = \;
            }
        }
        
        return \->save();
    }

    /**
     * 增加令牌使用量
     *
     * @param int \ 用户ID
     * @param int \ 使用的令牌数量
     * @return bool
     */
    public static function incrementTokensUsed(int \, int \): bool
    {
        \ = now()->toDateString();
        
        // 查找或创建今天的记录
        \ = self::firstOrCreate(
            ['user_id' => \, 'date' => \],
            [
                'storage_used' => 0,
                'files_count' => 0,
                'memories_count' => 0,
                'conversations_count' => 0,
                'tokens_used' => 0,
            ]
        );
        
        // 增加令牌使用量
        \->tokens_used += \;
        
        return \->save();
    }
}
