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
     * ��Ա��������
     *
     * @var MembershipAnalyticsService
     */
    protected $analyticsService;

    /**
     * ����������ʵ��
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
     * ��ʾ��Ա������ҳ
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // ��ȡ��Աͳ�Ƹ���
        $overview = $this->analyticsService->getOverview();
        
        // ��ȡ��Ա�ȼ��ֲ�
        $levelDistribution = $this->analyticsService->getLevelDistribution();
        
        return view("admin.membership.analytics.index", [
            "overview" => $overview,
            "levelDistribution" => $levelDistribution,
        ]);
    }

    /**
     * ��ʾ��Ա��������ҳ��
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function growth(Request $request)
    {
        $months = $request->input("months", 12);
        
        // ��ȡ��Ա��������
        $growthTrend = $this->analyticsService->getMemberGrowthTrend($months);
        
        return view("admin.membership.analytics.growth", [
            "growthTrend" => $growthTrend,
            "selectedMonths" => $months,
        ]);
    }

    /**
     * ��ʾ��Ա������ҳ��
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function retention(Request $request)
    {
        $months = $request->input("months", 6);
        
        // ��ȡ��Ա������
        $retentionRate = $this->analyticsService->getMemberRetentionRate($months);
        
        return view("admin.membership.analytics.retention", [
            "retentionRate" => $retentionRate,
            "selectedMonths" => $months,
        ]);
    }

    /**
     * ��ʾ��Ա�������ҳ��
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function revenue(Request $request)
    {
        $months = $request->input("months", 12);
        
        // ��ȡ��Ա�������
        $revenueData = $this->analyticsService->getMemberRevenue($months);
        
        return view("admin.membership.analytics.revenue", [
            "revenueData" => $revenueData,
            "selectedMonths" => $months,
        ]);
    }

    /**
     * ��ʾ��Ա��������ҳ��
     *
     * @return \Illuminate\View\View
     */
    public function upgrades()
    {
        // ��ȡ��Ա��������
        $upgradeData = $this->analyticsService->getMemberUpgradeAnalysis();
        
        return view("admin.membership.analytics.upgrades", [
            "upgradeData" => $upgradeData,
        ]);
    }

    /**
     * ������Ա��������
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
                    // ������Ա��������
                    fputcsv($file, ["�·�", "������Ա", "�ܻ�Ա��"]);
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
                    // ��������������
                    fputcsv($file, ["�·�", "������(%)"]);
                    $retentionRate = $this->analyticsService->getMemberRetentionRate($months);
                    foreach ($retentionRate as $data) {
                        fputcsv($file, [
                            $data["month"],
                            $data["retention_rate"]
                        ]);
                    }
                    break;
                    
                case "revenue":
                    // ������������
                    fputcsv($file, ["�·�", "����(Ԫ)"]);
                    $revenueData = $this->analyticsService->getMemberRevenue($months);
                    foreach ($revenueData as $data) {
                        fputcsv($file, [
                            $data["month"],
                            $data["revenue"]
                        ]);
                    }
                    break;
                    
                default:
                    // Ĭ�ϵ�����������
                    $overview = $this->analyticsService->getOverview();
                    fputcsv($file, ["ָ��", "��ֵ"]);
                    fputcsv($file, ["�ܻ�Ա��", $overview["total_members"]]);
                    fputcsv($file, ["����������Ա", $overview["new_members_today"]]);
                    fputcsv($file, ["����������Ա", $overview["new_members_this_month"]]);
                    fputcsv($file, ["��Ծ��Առ��(%)", $overview["active_member_percentage"]]);
                    fputcsv($file, ["�������ڻ�Ա��", $overview["expiring_members"]]);
                    fputcsv($file, ["�Զ����ѻ�Ա��", $overview["auto_renew_members"]]);
                    fputcsv($file, ["�Զ�����ռ��(%)", $overview["auto_renew_percentage"]]);
                    break;
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }
}
