<?php

namespace App\Models\User\Analytics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class UserActivityStat extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'user_activity_stats';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'user_id',
        'date',
        'login_count',
        'file_operations',
        'memory_operations',
        'conversation_count',
        'message_count',
        'api_calls',
        'total_actions',
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
     * 增加指定类型的操作计数
     *
     * @param int \ 用户ID
     * @param string \ 操作类型
     * @param int \ 增加的数量
     * @return bool
     */
    public static function incrementActivity(int \, string \, int \ = 1): bool
    {
        \ = now()->toDateString();
        
        // 查找或创建今天的记录
        \ = self::firstOrCreate(
            ['user_id' => \, 'date' => \],
            ['login_count' => 0, 'file_operations' => 0, 'memory_operations' => 0, 
             'conversation_count' => 0, 'message_count' => 0, 'api_calls' => 0, 'total_actions' => 0]
        );
        
        // 根据操作类型增加相应的计数
        switch (\) {
            case 'login':
                \->login_count += \;
                break;
            case 'file':
                \->file_operations += \;
                break;
            case 'memory':
                \->memory_operations += \;
                break;
            case 'conversation':
                \->conversation_count += \;
                break;
            case 'message':
                \->message_count += \;
                break;
            case 'api':
                \->api_calls += \;
                break;
        }
        
        // 更新总操作次数
        \->total_actions = \->login_count + \->file_operations + 
                              \->memory_operations + \->conversation_count + 
                              \->message_count + \->api_calls;
        
        return \->save();
    }
}
