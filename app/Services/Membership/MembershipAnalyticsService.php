<?php

namespace App\Services\Membership;

use App\Models\Membership\MembershipLevel;
use App\Models\Membership\MembershipSubscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MembershipAnalyticsService
{
    /**
     * ��ȡ��Աͳ�Ƹ���
     *
     * @return array
     */
    public function getOverview(): array
    {
        // �ܻ�Ա��
        $totalMembers = MembershipSubscription::where("status", "active")
            ->where("end_date", ">", now())
            ->count("DISTINCT user_id");
            
        // ����������Ա��
        $newMembersToday = MembershipSubscription::where("status", "active")
            ->where("start_date", ">=", Carbon::today())
            ->count("DISTINCT user_id");
            
        // ����������Ա��
        $newMembersThisMonth = MembershipSubscription::where("status", "active")
            ->where("start_date", ">=", Carbon::now()->startOfMonth())
            ->count("DISTINCT user_id");
            
        // ��Ծ��Առ��
        $totalUsers = User::count();
        $activeMemberPercentage = $totalUsers > 0 ? round(($totalMembers / $totalUsers) * 100, 2) : 0;
        
        // �������ڻ�Ա��
        $expiringMembers = MembershipSubscription::where("status", "active")
            ->where("end_date", ">", now())
            ->where("end_date", "<=", now()->addDays(7))
            ->count("DISTINCT user_id");
            
        // �Զ����ѻ�Առ��
        $autoRenewMembers = MembershipSubscription::where("status", "active")
            ->where("auto_renew", true)
            ->count("DISTINCT user_id");
        $autoRenewPercentage = $totalMembers > 0 ? round(($autoRenewMembers / $totalMembers) * 100, 2) : 0;
            
        return [
            "total_members" => $totalMembers,
            "new_members_today" => $newMembersToday,
            "new_members_this_month" => $newMembersThisMonth,
            "active_member_percentage" => $activeMemberPercentage,
            "expiring_members" => $expiringMembers,
            "auto_renew_members" => $autoRenewMembers,
            "auto_renew_percentage" => $autoRenewPercentage,
        ];
    }
    
    /**
     * ��ȡ��Ա�ȼ��ֲ�
     *
     * @return array
     */
    public function getLevelDistribution(): array
    {
        $result = DB::table("membership_subscriptions as ms")
            ->join("membership_levels as ml", "ms.membership_level_id", "=", "ml.id")
            ->where("ms.status", "active")
            ->where("ms.end_date", ">", now())
            ->select("ml.id", "ml.name", DB::raw("COUNT(DISTINCT ms.user_id) as count"))
            ->groupBy("ml.id", "ml.name")
            ->orderBy("ml.sort_order")
            ->get();
            
        $levels = [];
        $counts = [];
        
        foreach ($result as $row) {
            $levels[] = $row->name;
            $counts[] = $row->count;
        }
        
        return [
            "labels" => $levels,
            "data" => $counts,
        ];
    }
    
    /**
     * ��ȡ��Ա��������
     *
     * @param int $months ����
     * @return array
     */
    public function getMemberGrowthTrend(int $months = 12): array
    {
        $result = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $startDate = Carbon::now()->subMonths($i)->startOfMonth();
            $endDate = Carbon::now()->subMonths($i)->endOfMonth();
            
            // ����������Ա��
            $newMembers = MembershipSubscription::where("start_date", ">=", $startDate)
                ->where("start_date", "<=", $endDate)
                ->count("DISTINCT user_id");
                
            // ���½���ʱ���ܻ�Ա��
            $totalMembers = MembershipSubscription::where("start_date", "<=", $endDate)
                ->where(function ($query) use ($endDate) {
                    $query->where("end_date", ">", $endDate)
                          ->orWhereNull("end_date");
                })
                ->where("status", "active")
                ->count("DISTINCT user_id");
                
            $result[] = [
                "month" => $startDate->format("Y-m"),
                "new_members" => $newMembers,
                "total_members" => $totalMembers,
            ];
        }
        
        return $result;
    }
    
    /**
     * ��ȡ��Ա������
     *
     * @param int $months ����
     * @return array
     */
    public function getMemberRetentionRate(int $months = 6): array
    {
        $result = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $startMonth = Carbon::now()->subMonths($i)->startOfMonth();
            $endMonth = Carbon::now()->subMonths($i)->endOfMonth();
            $nextMonth = Carbon::now()->subMonths($i-1)->endOfMonth();
            
            // ����������Ա
            $newMembers = MembershipSubscription::where("start_date", ">=", $startMonth)
                ->where("start_date", "<=", $endMonth)
                ->count("DISTINCT user_id");
                
            if ($newMembers == 0) {
                $result[] = [
                    "month" => $startMonth->format("Y-m"),
                    "retention_rate" => 0,
                ];
                continue;
            }
            
            // �¸�����Ȼ��Ծ�Ļ�Ա��
            $retainedMembers = MembershipSubscription::where("start_date", ">=", $startMonth)
                ->where("start_date", "<=", $endMonth)
                ->where(function ($query) use ($nextMonth) {
                    $query->where("end_date", ">", $nextMonth)
                          ->orWhereNull("end_date");
                })
                ->where("status", "active")
                ->count("DISTINCT user_id");
                
            // ����������
            $retentionRate = round(($retainedMembers / $newMembers) * 100, 2);
            
            $result[] = [
                "month" => $startMonth->format("Y-m"),
                "retention_rate" => $retentionRate,
            ];
        }
        
        return $result;
    }
    
    /**
     * ��ȡ��Ա�������
     *
     * @param int $months ����
     * @return array
     */
    public function getMemberRevenue(int $months = 12): array
    {
        $result = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $startDate = Carbon::now()->subMonths($i)->startOfMonth();
            $endDate = Carbon::now()->subMonths($i)->endOfMonth();
            
            // ���»�Ա��������
            $revenue = DB::table("orders")
                ->where("order_type", "membership")
                ->where("status", "completed")
                ->where("created_at", ">=", $startDate)
                ->where("created_at", "<=", $endDate)
                ->sum("total_amount");
                
            $result[] = [
                "month" => $startDate->format("Y-m"),
                "revenue" => $revenue,
            ];
        }
        
        return $result;
    }
    
    /**
     * ��ȡ��Ա��������
     *
     * @return array
     */
    public function getMemberUpgradeAnalysis(): array
    {
        $levels = MembershipLevel::where("status", "active")
            ->orderBy("sort_order")
            ->get();
            
        $upgradeMatrix = [];
        $labels = [];
        
        foreach ($levels as $fromLevel) {
            $labels[] = $fromLevel->name;
            $row = [];
            
            foreach ($levels as $toLevel) {
                // ͳ�ƴ� fromLevel ������ toLevel ���û���
                if ($fromLevel->id == $toLevel->id) {
                    // ͬһ�ȼ�����ʾ���ָõȼ����û���
                    $count = MembershipSubscription::where("membership_level_id", $fromLevel->id)
                        ->where("status", "active")
                        ->where("end_date", ">", now())
                        ->count("DISTINCT user_id");
                } else {
                    // ��ͬ�ȼ�����ʾ�����򽵼����û���
                    $count = DB::table("membership_subscriptions as curr")
                        ->join("membership_subscriptions as prev", function ($join) use ($fromLevel) {
                            $join->on("curr.user_id", "=", "prev.user_id")
                                ->where("prev.membership_level_id", "=", $fromLevel->id);
                        })
                        ->where("curr.membership_level_id", $toLevel->id)
                        ->where("curr.created_at", ">", DB::raw("prev.created_at"))
                        ->where("curr.status", "active")
                        ->where("curr.end_date", ">", now())
                        ->count(DB::raw("DISTINCT curr.user_id"));
                }
                
                $row[] = $count;
            }
            
            $upgradeMatrix[] = $row;
        }
        
        return [
            "labels" => $labels,
            "data" => $upgradeMatrix,
        ];
    }
}
