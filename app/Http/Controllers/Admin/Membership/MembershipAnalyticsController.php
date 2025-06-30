<?php

namespace App\Http\Controllers\Admin\Membership;

use App\Http\Controllers\Controller;
use App\Services\Membership\MembershipAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class MembershipAnalyticsController extends Controller
{
    /**
     * 会员分析服务
     *
     * @var MembershipAnalyticsService
     */
    protected $analyticsService;

    /**
     * 创建控制器实例
     *
     * @param MembershipAnalyticsService $analyticsService
     * @return void
     */
    public function __construct(MembershipAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
        $this->middleware(["auth", "role:admin"]);
    }

    /**
     * 显示会员分析首页
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // 获取会员统计概览
        $overview = $this->analyticsService->getOverview();
        
        // 获取会员等级分布
        $levelDistribution = $this->analyticsService->getLevelDistribution();
        
        return view("admin.membership.analytics.index", [
            "overview" => $overview,
            "levelDistribution" => $levelDistribution,
        ]);
    }

    /**
     * 显示会员增长趋势页面
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function growth(Request $request)
    {
        $months = $request->input("months", 12);
        
        // 获取会员增长趋势
        $growthTrend = $this->analyticsService->getMemberGrowthTrend($months);
        
        return view("admin.membership.analytics.growth", [
            "growthTrend" => $growthTrend,
            "selectedMonths" => $months,
        ]);
    }

    /**
     * 显示会员留存率页面
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function retention(Request $request)
    {
        $months = $request->input("months", 6);
        
        // 获取会员留存率
        $retentionRate = $this->analyticsService->getMemberRetentionRate($months);
        
        return view("admin.membership.analytics.retention", [
            "retentionRate" => $retentionRate,
            "selectedMonths" => $months,
        ]);
    }

    /**
     * 显示会员收入分析页面
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function revenue(Request $request)
    {
        $months = $request->input("months", 12);
        
        // 获取会员收入分析
        $revenueData = $this->analyticsService->getMemberRevenue($months);
        
        return view("admin.membership.analytics.revenue", [
            "revenueData" => $revenueData,
            "selectedMonths" => $months,
        ]);
    }

    /**
     * 显示会员升级分析页面
     *
     * @return \Illuminate\View\View
     */
    public function upgrades()
    {
        // 获取会员升级分析
        $upgradeData = $this->analyticsService->getMemberUpgradeAnalysis();
        
        return view("admin.membership.analytics.upgrades", [
            "upgradeData" => $upgradeData,
        ]);
    }

    /**
     * 导出会员分析数据
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $type = $request->input("type", "overview");
        $months = $request->input("months", 12);
        
        $filename = "member_analytics_{$type}_" . Carbon::now()->format("Ymd_His") . ".csv";
        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        
        $callback = function() use ($type, $months) {
            $file = fopen("php://output", "w");
            
            switch ($type) {
                case "growth":
                    // 导出会员增长数据
                    fputcsv($file, ["月份", "新增会员", "总会员数"]);
                    $growthTrend = $this->analyticsService->getMemberGrowthTrend($months);
                    foreach ($growthTrend as $data) {
                        fputcsv($file, [
                            $data["month"],
                            $data["new_members"],
                            $data["total_members"]
                        ]);
                    }
                    break;
                    
                case "retention":
                    // 导出留存率数据
                    fputcsv($file, ["月份", "留存率(%)"]);
                    $retentionRate = $this->analyticsService->getMemberRetentionRate($months);
                    foreach ($retentionRate as $data) {
                        fputcsv($file, [
                            $data["month"],
                            $data["retention_rate"]
                        ]);
                    }
                    break;
                    
                case "revenue":
                    // 导出收入数据
                    fputcsv($file, ["月份", "收入(元)"]);
                    $revenueData = $this->analyticsService->getMemberRevenue($months);
                    foreach ($revenueData as $data) {
                        fputcsv($file, [
                            $data["month"],
                            $data["revenue"]
                        ]);
                    }
                    break;
                    
                default:
                    // 默认导出概览数据
                    $overview = $this->analyticsService->getOverview();
                    fputcsv($file, ["指标", "数值"]);
                    fputcsv($file, ["总会员数", $overview["total_members"]]);
                    fputcsv($file, ["今日新增会员", $overview["new_members_today"]]);
                    fputcsv($file, ["本月新增会员", $overview["new_members_this_month"]]);
                    fputcsv($file, ["活跃会员占比(%)", $overview["active_member_percentage"]]);
                    fputcsv($file, ["即将过期会员数", $overview["expiring_members"]]);
                    fputcsv($file, ["自动续费会员数", $overview["auto_renew_members"]]);
                    fputcsv($file, ["自动续费占比(%)", $overview["auto_renew_percentage"]]);
                    break;
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }
}
