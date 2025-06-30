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
     * 获取会员统计概览
     *
     * @return array
     */
    public function getOverview(): array
    {
        // 总会员数
        $totalMembers = MembershipSubscription::where("status", "active")
            ->where("end_date", ">", now())
            ->count("DISTINCT user_id");
            
        // 今日新增会员数
        $newMembersToday = MembershipSubscription::where("status", "active")
            ->where("start_date", ">=", Carbon::today())
            ->count("DISTINCT user_id");
            
        // 本月新增会员数
        $newMembersThisMonth = MembershipSubscription::where("status", "active")
            ->where("start_date", ">=", Carbon::now()->startOfMonth())
            ->count("DISTINCT user_id");
            
        // 活跃会员占比
        $totalUsers = User::count();
        $activeMemberPercentage = $totalUsers > 0 ? round(($totalMembers / $totalUsers) * 100, 2) : 0;
        
        // 即将过期会员数
        $expiringMembers = MembershipSubscription::where("status", "active")
            ->where("end_date", ">", now())
            ->where("end_date", "<=", now()->addDays(7))
            ->count("DISTINCT user_id");
            
        // 自动续费会员占比
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
     * 获取会员等级分布
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
     * 获取会员增长趋势
     *
     * @param int $months 月数
     * @return array
     */
    public function getMemberGrowthTrend(int $months = 12): array
    {
        $result = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $startDate = Carbon::now()->subMonths($i)->startOfMonth();
            $endDate = Carbon::now()->subMonths($i)->endOfMonth();
            
            // 该月新增会员数
            $newMembers = MembershipSubscription::where("start_date", ">=", $startDate)
                ->where("start_date", "<=", $endDate)
                ->count("DISTINCT user_id");
                
            // 该月结束时的总会员数
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
     * 获取会员留存率
     *
     * @param int $months 月数
     * @return array
     */
    public function getMemberRetentionRate(int $months = 6): array
    {
        $result = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $startMonth = Carbon::now()->subMonths($i)->startOfMonth();
            $endMonth = Carbon::now()->subMonths($i)->endOfMonth();
            $nextMonth = Carbon::now()->subMonths($i-1)->endOfMonth();
            
            // 当月新增会员
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
            
            // 下个月仍然活跃的会员数
            $retainedMembers = MembershipSubscription::where("start_date", ">=", $startMonth)
                ->where("start_date", "<=", $endMonth)
                ->where(function ($query) use ($nextMonth) {
                    $query->where("end_date", ">", $nextMonth)
                          ->orWhereNull("end_date");
                })
                ->where("status", "active")
                ->count("DISTINCT user_id");
                
            // 计算留存率
            $retentionRate = round(($retainedMembers / $newMembers) * 100, 2);
            
            $result[] = [
                "month" => $startMonth->format("Y-m"),
                "retention_rate" => $retentionRate,
            ];
        }
        
        return $result;
    }
    
    /**
     * 获取会员收入分析
     *
     * @param int $months 月数
     * @return array
     */
    public function getMemberRevenue(int $months = 12): array
    {
        $result = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $startDate = Carbon::now()->subMonths($i)->startOfMonth();
            $endDate = Carbon::now()->subMonths($i)->endOfMonth();
            
            // 该月会员订阅收入
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
     * 获取会员升级分析
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
                // 统计从 fromLevel 升级到 toLevel 的用户数
                if ($fromLevel->id == $toLevel->id) {
                    // 同一等级，显示保持该等级的用户数
                    $count = MembershipSubscription::where("membership_level_id", $fromLevel->id)
                        ->where("status", "active")
                        ->where("end_date", ">", now())
                        ->count("DISTINCT user_id");
                } else {
                    // 不同等级，显示升级或降级的用户数
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
