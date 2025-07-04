<?php

namespace App\Http\Controllers\User\Analytics;

use App\Http\Controllers\Controller;
use App\Services\User\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAnalyticsController extends Controller
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
     * 显示用户活跃度统计
     *
     * @param Request \
     * @return \Illuminate\Http\JsonResponse
     */
    public function activity(Request \)
    {
        \ = \->input('days', 30);
        \ = Auth::id();
        
        \ = \->analyticsService->getUserActivityStats(\, \);
        
        return response()->json([
            'success' => true,
            'data' => \
        ]);
    }

    /**
     * 显示用户资源使用统计
     *
     * @param Request \
     * @return \Illuminate\Http\JsonResponse
     */
    public function resources(Request \)
    {
        \ = \->input('days', 30);
        \ = Auth::id();
        
        \ = \->analyticsService->getUserResourceStats(\, \);
        
        return response()->json([
            'success' => true,
            'data' => \
        ]);
    }

    /**
     * 显示用户行为分析
     *
     * @param Request \
     * @return \Illuminate\Http\JsonResponse
     */
    public function behavior(Request \)
    {
        \ = \->input('days', 30);
        \ = Auth::id();
        
        \ = \->analyticsService->getUserBehaviorAnalysis(\, \);
        
        return response()->json([
            'success' => true,
            'data' => \
        ]);
    }

    /**
     * 显示用户统计仪表板
     *
     * @param Request \
     * @return \Illuminate\View\View
     */
    public function dashboard(Request \)
    {
        \ = \->input('days', 30);
        \ = Auth::id();
        
        \ = \->analyticsService->getUserActivityStats(\, \);
        \ = \->analyticsService->getUserResourceStats(\, \);
        \ = \->analyticsService->getUserBehaviorAnalysis(\, \);
        
        // 更新用户资源统计
        \->analyticsService->updateUserResourceStats(\);
        
        return view('user.analytics.dashboard', [
            'activityStats' => \,
            'resourceStats' => \,
            'behaviorAnalysis' => \,
            'days' => \
        ]);
    }
}
