<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\User\Analytics\UserActivityStat;
use App\Models\User\Analytics\UserResourceStat;
use App\Models\User\Analytics\UserBehaviorAnalytic;
use App\Models\User\Analytics\UserGrowthStat;
use App\Models\User\UserFile;
use App\Models\User\UserMemory;
use App\Models\User\UserConversation;
use App\Models\User\ConversationMessage;
use App\Models\User\UserVerification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * 记录用户活动
     *
     * @param int \ 用户ID
     * @param string \ 活动类型
     * @param int \ 活动次数
     * @return bool
     */
    public function recordUserActivity(int \, string \, int \ = 1): bool
    {
        return UserActivityStat::incrementActivity(\, \, \);
    }
    
    /**
     * 记录用户行为
     *
     * @param int \ 用户ID
     * @param string \ 功能
     * @param string \ 操作
     * @param array \ 上下文
     * @param array \ 客户端信息
     * @return UserBehaviorAnalytic
     */
    public function recordUserBehavior(
        int \,
        string \,
        string \,
        array \ = [],
        array \ = []
    ): UserBehaviorAnalytic {
        return UserBehaviorAnalytic::recordBehavior(\, \, \, \, \);
    }
    
    /**
     * 更新用户资源使用统计
     *
     * @param int \ 用户ID
     * @return bool
     */
    public function updateUserResourceStats(int \): bool
    {
        // 计算用户的存储使用量
        \ = UserFile::where('user_id', \)->sum('size');
        
        // 统计文件数量
        \ = UserFile::where('user_id', \)->count();
        
        // 统计记忆数量
        \ = UserMemory::where('user_id', \)->count();
        
        // 统计对话数量
        \ = UserConversation::where('user_id', \)->count();
        
        // 获取令牌使用量（从当天的统计中获取，如果有的话）
        \ = now()->toDateString();
        \ = UserResourceStat::where('user_id', \)
            ->where('date', \)
            ->first();
        
        \ = \ ? \->tokens_used : 0;
        
        // 更新资源使用统计
        return UserResourceStat::updateResourceUsage(\, [
            'storage_used' => \,
            'files_count' => \,
            'memories_count' => \,
            'conversations_count' => \,
            'tokens_used' => \,
        ]);
    }
    
    /**
     * 增加用户令牌使用量
     *
     * @param int \ 用户ID
     * @param int \ 令牌数量
     * @return bool
     */
    public function incrementUserTokens(int \, int \): bool
    {
        return UserResourceStat::incrementTokensUsed(\, \);
    }
    
    /**
     * 生成每日用户增长统计
     *
     * @param string|null \ 日期，默认为昨天
     * @return UserGrowthStat
     */
    public function generateDailyGrowthStats(string \ = null): UserGrowthStat
    {
        \ = \ ?? now()->subDay()->toDateString();
        \ = Carbon::parse(\);
        \ = \->copy()->subDay()->toDateString();
        
        // 新用户数（昨天注册的用户）
        \ = User::whereDate('created_at', \)->count();
        
        // 活跃用户数（昨天有活动的用户）
        \ = UserActivityStat::where('date', \)->count();
        
        // 回访用户数（前天和昨天都活跃的用户）
        \ = UserActivityStat::where('date', \)
            ->whereIn('user_id', function(\) use (\) {
                \->select('user_id')
                    ->from('user_activity_stats')
                    ->where('date', \);
            })
            ->count();
        
        // 流失用户数（前天活跃但昨天不活跃的用户）
        \ = UserActivityStat::where('date', \)
            ->whereNotIn('user_id', function(\) use (\) {
                \->select('user_id')
                    ->from('user_activity_stats')
                    ->where('date', \);
            })
            ->count();
        
        // 已验证用户数
        \ = UserVerification::where('status', 'approved')
            ->whereDate('verified_at', '<=', \)
            ->count();
        
        // 计算留存率和流失率
        \ = \ > 0 ? (\ / \) * 100 : 0;
        \ = \ > 0 ? (\ / \) * 100 : 0;
        
        // 更新统计数据
        return UserGrowthStat::updateDailyStats(\, [
            'new_users' => \,
            'active_users' => \,
            'returning_users' => \,
            'churned_users' => \,
            'verified_users' => \,
            'retention_rate' => \,
            'churn_rate' => \,
        ]);
    }
    
    /**
     * 获取用户活跃度统计
     *
     * @param int \ 用户ID
     * @param int \ 天数
     * @return array
     */
    public function getUserActivityStats(int \, int \ = 30): array
    {
        \ = now()->subDays(\)->toDateString();
        \ = now()->toDateString();
        
        \ = UserActivityStat::where('user_id', \)
            ->whereBetween('date', [\, \])
            ->orderBy('date')
            ->get();
        
        \ = [];
        \ = [];
        \ = [];
        \ = [];
        \ = [];
        \ = [];
        \ = [];
        \ = [];
        
        foreach (\ as \) {
            \[] = \->date->format('Y-m-d');
            \[] = \->login_count;
            \[] = \->file_operations;
            \[] = \->memory_operations;
            \[] = \->conversation_count;
            \[] = \->message_count;
            \[] = \->api_calls;
            \[] = \->total_actions;
        }
        
        return [
            'dates' => \,
            'login_counts' => \,
            'file_operations' => \,
            'memory_operations' => \,
            'conversation_counts' => \,
            'message_counts' => \,
            'api_calls' => \,
            'total_actions' => \,
        ];
    }
    
    /**
     * 获取用户资源使用统计
     *
     * @param int \ 用户ID
     * @param int \ 天数
     * @return array
     */
    public function getUserResourceStats(int \, int \ = 30): array
    {
        \ = now()->subDays(\)->toDateString();
        \ = now()->toDateString();
        
        \ = UserResourceStat::where('user_id', \)
            ->whereBetween('date', [\, \])
            ->orderBy('date')
            ->get();
        
        \ = [];
        \ = [];
        \ = [];
        \ = [];
        \ = [];
        \ = [];
        
        foreach (\ as \) {
            \[] = \->date->format('Y-m-d');
            \[] = \->storage_used;
            \[] = \->files_count;
            \[] = \->memories_count;
            \[] = \->conversations_count;
            \[] = \->tokens_used;
        }
        
        return [
            'dates' => \,
            'storage_used' => \,
            'files_count' => \,
            'memories_count' => \,
            'conversations_count' => \,
            'tokens_used' => \,
        ];
    }
    
    /**
     * 获取用户行为模式分析
     *
     * @param int \ 用户ID
     * @param int \ 天数
     * @return array
     */
    public function getUserBehaviorAnalysis(int \, int \ = 30): array
    {
        return UserBehaviorAnalytic::getUserBehaviorPattern(\, \);
    }
    
    /**
     * 获取平台用户增长趋势
     *
     * @param int \ 天数
     * @return array
     */
    public function getPlatformGrowthTrend(int \ = 30): array
    {
        \ = now()->subDays(\)->toDateString();
        \ = now()->toDateString();
        
        return UserGrowthStat::getGrowthTrend(\, \);
    }
}
