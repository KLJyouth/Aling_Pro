<?php

namespace App\Http\Controllers;

use App\Models\MembershipSubscription;
use App\Models\QuotaUsage;
use App\Services\Membership\PointService;
use App\Services\Membership\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * 创建新的控制器实例
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware("auth");
    }

    /**
     * 显示用户仪表盘
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // 获取当前会员订阅
        $subscription = $user->currentSubscription;
        $membershipLevel = $user->getCurrentMembershipLevel();
        
        // 获取API使用情况
        $apiUsageToday = $user->getQuotaUsage("api");
        $apiQuota = $membershipLevel ? $membershipLevel->api_quota : 0;
        $apiUsagePercent = $apiQuota > 0 ? min(100, round(($apiUsageToday / $apiQuota) * 100)) : 0;
        
        // 获取AI使用情况
        $aiUsageToday = $user->getQuotaUsage("ai");
        $aiQuota = $membershipLevel ? $membershipLevel->ai_quota : 0;
        $aiUsagePercent = $aiQuota > 0 ? min(100, round(($aiUsageToday / $aiQuota) * 100)) : 0;
        
        // 获取存储使用情况
        $storageUsed = $user->getQuotaUsage("storage");
        $storageQuota = $membershipLevel ? $membershipLevel->storage_quota : 0;
        $storageUsagePercent = $storageQuota > 0 ? min(100, round(($storageUsed / $storageQuota) * 100)) : 0;
        
        // 获取积分信息
        $pointService = app(PointService::class);
        $pointsStats = $pointService->getPointsStats($user);
        $recentPoints = $pointService->getPointsHistory($user, 5);
        
        // 获取推荐信息
        $referralService = app(ReferralService::class);
        $referralStats = $referralService->getReferralStats($user);
        $referralLink = $referralService->getReferralLink($user);
        
        // 获取最近的API调用记录
        $recentApiCalls = $user->apiKeys()
            ->with("logs")
            ->get()
            ->flatMap(function ($apiKey) {
                return $apiKey->logs;
            })
            ->sortByDesc("created_at")
            ->take(5);
        
        // 获取使用趋势数据
        $usageTrend = $this->getUsageTrendData($user);
        
        return view("dashboard.index", compact(
            "user",
            "subscription",
            "membershipLevel",
            "apiUsageToday",
            "apiQuota",
            "apiUsagePercent",
            "aiUsageToday",
            "aiQuota",
            "aiUsagePercent",
            "storageUsed",
            "storageQuota",
            "storageUsagePercent",
            "pointsStats",
            "recentPoints",
            "referralStats",
            "referralLink",
            "recentApiCalls",
            "usageTrend"
        ));
    }
    
    /**
     * 获取使用趋势数据
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    protected function getUsageTrendData($user)
    {
        // 获取过去7天的数据
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();
        
        // 初始化数据结构
        $dates = [];
        $apiData = [];
        $aiData = [];
        
        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            $dateString = $date->format("Y-m-d");
            $dates[] = $date->format("m-d");
            
            // 查询API使用量
            $apiUsage = QuotaUsage::where("user_id", $user->id)
                ->where("quota_type", "api")
                ->whereDate("created_at", $date)
                ->sum("amount");
            $apiData[] = $apiUsage;
            
            // 查询AI使用量
            $aiUsage = QuotaUsage::where("user_id", $user->id)
                ->where("quota_type", "ai")
                ->whereDate("created_at", $date)
                ->sum("amount");
            $aiData[] = $aiUsage;
        }
        
        return [
            "dates" => $dates,
            "api" => $apiData,
            "ai" => $aiData
        ];
    }
}
