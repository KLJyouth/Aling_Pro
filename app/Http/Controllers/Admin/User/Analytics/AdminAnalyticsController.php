<?php

namespace App\Http\Controllers\Admin\User\Analytics;

use App\Http\Controllers\Controller;
use App\Services\User\AnalyticsService;
use App\Models\User\Analytics\UserActivityStat;
use App\Models\User\Analytics\UserResourceStat;
use App\Models\User\Analytics\UserGrowthStat;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminAnalyticsController extends Controller
{
    /**
     * 分析服务
     *
     * @var AnalyticsService
     */
    protected \;

    /**
     * 创建新的控制器实例
     *
     * @param AnalyticsService \
     * @return void
     */
    public function __construct(AnalyticsService \)
    {
        \->analyticsService = \;
    }

    /**
     * 显示用户统计仪表板
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // 获取平台用户增长趋势（最近30天）
        \ = \->analyticsService->getPlatformGrowthTrend(30);
        
        // 获取今日统计
        \ = now()->toDateString();
        \ = UserGrowthStat::where('date', \)->first();
        
        // 如果今日统计不存在，则生成
        if (!) {
            \ = \->analyticsService->generateDailyGrowthStats(\);
        }
        
        // 获取总用户数
        \ = User::count();
        
        // 获取总存储使用量
        \ = UserResourceStat::where('date', \)->sum('storage_used');
        
        // 获取今日活跃用户数
        \ = UserActivityStat::where('date', \)->count();
        
        return view('admin.analytics.dashboard', [
            'growthTrend' => \,
            'todayGrowth' => \,
            'totalUsers' => \,
            'totalStorage' => \,
            'todayActiveUsers' => \
        ]);
    }

    /**
     * 显示特定用户的统计数据
     *
     * @param Request \
     * @param int \
     * @return \Illuminate\View\View
     */
    public function userStats(Request \, int \)
    {
        \ = \->input('days', 30);
        \ = User::findOrFail(\);
        
        \ = \->analyticsService->getUserActivityStats(\, \);
        \ = \->analyticsService->getUserResourceStats(\, \);
        \ = \->analyticsService->getUserBehaviorAnalysis(\, \);
        
        return view('admin.analytics.user_stats', [
            'user' => \,
            'activityStats' => \,
            'resourceStats' => \,
            'behaviorAnalysis' => \,
            'days' => \
        ]);
    }

    /**
     * 显示平台活跃度统计
     *
     * @param Request \
     * @return \Illuminate\View\View
     */
    public function platformActivity(Request \)
    {
        \ = \->input('days', 30);
        \ = now()->subDays(\)->toDateString();
        \ = now()->toDateString();
        
        // 获取每日活跃用户数
        \ = UserActivityStat::whereBetween('date', [\, \])
            ->selectRaw('date, COUNT(DISTINCT user_id) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // 获取每日操作总数
        \ = UserActivityStat::whereBetween('date', [\, \])
            ->selectRaw('date, SUM(total_actions) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // 获取功能使用分布
        \ = UserActivityStat::whereBetween('date', [\, \])
            ->selectRaw('SUM(login_count) as login, SUM(file_operations) as file, 
                         SUM(memory_operations) as memory, SUM(conversation_count) as conversation, 
                         SUM(message_count) as message, SUM(api_calls) as api')
            ->first();
        
        return view('admin.analytics.platform_activity', [
            'dailyActiveUsers' => \,
            'dailyOperations' => \,
            'featureUsage' => \,
            'days' => \
        ]);
    }

    /**
     * 显示平台资源使用统计
     *
     * @param Request \
     * @return \Illuminate\View\View
     */
    public function platformResources(Request \)
    {
        \ = \->input('days', 30);
        \ = now()->subDays(\)->toDateString();
        \ = now()->toDateString();
        
        // 获取每日存储使用量
        \ = UserResourceStat::whereBetween('date', [\, \])
            ->selectRaw('date, SUM(storage_used) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // 获取每日令牌使用量
        \ = UserResourceStat::whereBetween('date', [\, \])
            ->selectRaw('date, SUM(tokens_used) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // 获取存储使用量最高的用户
        \ = UserResourceStat::where('date', \)
            ->join('users', 'user_resource_stats.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'user_resource_stats.storage_used')
            ->orderBy('user_resource_stats.storage_used', 'desc')
            ->limit(10)
            ->get();
        
        // 获取令牌使用量最高的用户
        \ = UserResourceStat::where('date', \)
            ->join('users', 'user_resource_stats.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'user_resource_stats.tokens_used')
            ->orderBy('user_resource_stats.tokens_used', 'desc')
            ->limit(10)
            ->get();
        
        return view('admin.analytics.platform_resources', [
            'dailyStorage' => \,
            'dailyTokens' => \,
            'topStorageUsers' => \,
            'topTokenUsers' => \,
            'days' => \
        ]);
    }

    /**
     * 显示用户增长统计
     *
     * @param Request \
     * @return \Illuminate\View\View
     */
    public function userGrowth(Request \)
    {
        \ = \->input('days', 30);
        
        // 获取用户增长趋势
        \ = \->analyticsService->getPlatformGrowthTrend(\);
        
        return view('admin.analytics.user_growth', [
            'growthTrend' => \,
            'days' => \
        ]);
    }

    /**
     * 手动生成指定日期的用户增长统计
     *
     * @param Request \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateGrowthStats(Request \)
    {
        \ = \->input('date', now()->subDay()->toDateString());
        
        \->analyticsService->generateDailyGrowthStats(\);
        
        return redirect()->back()->with('success', \
已成功生成
\$date
的用户增长统计\);
    }
}
