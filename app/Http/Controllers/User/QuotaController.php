<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\Membership\QuotaService;
use Illuminate\Http\Request;

class QuotaController extends Controller
{
    /**
     * 额度服务
     *
     * @var QuotaService
     */
    protected \;

    /**
     * 创建控制器实例
     *
     * @param QuotaService \
     * @return void
     */
    public function __construct(QuotaService \)
    {
        \->quotaService = \;
        \->middleware('auth');
    }

    /**
     * 显示用户额度使用情况
     *
     * @param Request \
     * @return \Illuminate\View\View
     */
    public function index(Request \)
    {
        \ = \->user();
        \ = \->quotaService->getCurrentUsage(\);
        
        return view('user.quota.index', [
            'currentUsage' => \,
        ]);
    }
    
    /**
     * 获取额度使用统计数据
     *
     * @param Request \
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats(Request \)
    {
        \->validate([
            'quota_type' => 'required|in:api,ai,storage,bandwidth',
            'period' => 'required|in:day,week,month,year',
        ]);
        
        \ = \->user();
        \ = \->input('quota_type');
        \ = \->input('period');
        
        \ = \->quotaService->getUsageStats(\, \, \);
        
        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => \,
        ]);
    }
    
    /**
     * 获取额度使用趋势数据
     *
     * @param Request \
     * @return \Illuminate\Http\JsonResponse
     */
    public function trend(Request \)
    {
        \->validate([
            'quota_type' => 'required|in:api,ai,storage,bandwidth',
            'days' => 'integer|min:7|max:90',
        ]);
        
        \ = \->user();
        \ = \->input('quota_type');
        \ = \->input('days', 30);
        
        \ = \->quotaService->getUsageTrend(\, \, \);
        
        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => \,
        ]);
    }
}
