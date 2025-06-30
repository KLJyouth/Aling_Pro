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
     * �����µĿ�����ʵ��
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware("auth");
    }

    /**
     * ��ʾ�û��Ǳ���
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // ��ȡ��ǰ��Ա����
        $subscription = $user->currentSubscription;
        $membershipLevel = $user->getCurrentMembershipLevel();
        
        // ��ȡAPIʹ�����
        $apiUsageToday = $user->getQuotaUsage("api");
        $apiQuota = $membershipLevel ? $membershipLevel->api_quota : 0;
        $apiUsagePercent = $apiQuota > 0 ? min(100, round(($apiUsageToday / $apiQuota) * 100)) : 0;
        
        // ��ȡAIʹ�����
        $aiUsageToday = $user->getQuotaUsage("ai");
        $aiQuota = $membershipLevel ? $membershipLevel->ai_quota : 0;
        $aiUsagePercent = $aiQuota > 0 ? min(100, round(($aiUsageToday / $aiQuota) * 100)) : 0;
        
        // ��ȡ�洢ʹ�����
        $storageUsed = $user->getQuotaUsage("storage");
        $storageQuota = $membershipLevel ? $membershipLevel->storage_quota : 0;
        $storageUsagePercent = $storageQuota > 0 ? min(100, round(($storageUsed / $storageQuota) * 100)) : 0;
        
        // ��ȡ������Ϣ
        $pointService = app(PointService::class);
        $pointsStats = $pointService->getPointsStats($user);
        $recentPoints = $pointService->getPointsHistory($user, 5);
        
        // ��ȡ�Ƽ���Ϣ
        $referralService = app(ReferralService::class);
        $referralStats = $referralService->getReferralStats($user);
        $referralLink = $referralService->getReferralLink($user);
        
        // ��ȡ�����API���ü�¼
        $recentApiCalls = $user->apiKeys()
            ->with("logs")
            ->get()
            ->flatMap(function ($apiKey) {
                return $apiKey->logs;
            })
            ->sortByDesc("created_at")
            ->take(5);
        
        // ��ȡʹ����������
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
     * ��ȡʹ����������
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    protected function getUsageTrendData($user)
    {
        // ��ȡ��ȥ7�������
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();
        
        // ��ʼ�����ݽṹ
        $dates = [];
        $apiData = [];
        $aiData = [];
        
        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            $dateString = $date->format("Y-m-d");
            $dates[] = $date->format("m-d");
            
            // ��ѯAPIʹ����
            $apiUsage = QuotaUsage::where("user_id", $user->id)
                ->where("quota_type", "api")
                ->whereDate("created_at", $date)
                ->sum("amount");
            $apiData[] = $apiUsage;
            
            // ��ѯAIʹ����
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
