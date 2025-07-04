<?php

namespace App\Services\Membership;

use App\Models\Membership\MembershipSubscription;
use App\Models\User;
use App\Models\QuotaUsage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QuotaService
{
    /**
     * 记录额度使用
     *
     * @param User \ 用户
     * @param string \ 额度类型 (api, ai, storage, bandwidth)
     * @param int \ 使用量
     * @param string|null \ 描述
     * @return bool
     */
    public function recordUsage(User \, string \, int \, ?string \ = null): bool
    {
        try {
            \ = new QuotaUsage();
            \->user_id = \->id;
            \->quota_type = \;
            \->amount = \;
            \->description = \;
            \->used_at = now();
            \->save();
            
            return true;
        } catch (\Exception \) {
            \Illuminate\Support\Facades\Log::error('记录额度使用失败', [
                'error' => \->getMessage(),
                'user_id' => \->id,
                'quota_type' => \,
                'amount' => \,
            ]);
            
            return false;
        }
    }
    
    /**
     * 获取用户当前额度使用情况
     *
     * @param User \ 用户
     * @return array
     */
    public function getCurrentUsage(User \): array
    {
        // 获取用户当前有效的会员订阅
        \ = \->activeSubscription();
        
        // 如果没有有效订阅，返回基础额度
        if (!\) {
            return [
                'api' => [
                    'used' => \->getUsedQuota(\, 'api'),
                    'total' => config('membership.free_tier.api_quota', 100),
                    'percent' => 0,
                ],
                'ai' => [
                    'used' => \->getUsedQuota(\, 'ai'),
                    'total' => config('membership.free_tier.ai_quota', 50),
                    'percent' => 0,
                ],
                'storage' => [
                    'used' => \->getUsedQuota(\, 'storage'),
                    'total' => config('membership.free_tier.storage_quota', 100),
                    'percent' => 0,
                ],
                'bandwidth' => [
                    'used' => \->getUsedQuota(\, 'bandwidth'),
                    'total' => config('membership.free_tier.bandwidth_quota', 1024),
                    'percent' => 0,
                ],
            ];
        }
        
        // 获取会员等级的额度
        \ = \->level;
        
        // 计算各类额度的使用情况
        \ = \->getUsedQuota(\, 'api');
        \ = \->getUsedQuota(\, 'ai');
        \ = \->getUsedQuota(\, 'storage');
        \ = \->getUsedQuota(\, 'bandwidth');
        
        // 计算使用百分比
        \ = \->api_quota > 0 ? round((\ / \->api_quota) * 100, 2) : 0;
        \ = \->ai_quota > 0 ? round((\ / \->ai_quota) * 100, 2) : 0;
        \ = \->storage_quota > 0 ? round((\ / \->storage_quota) * 100, 2) : 0;
        \ = \->bandwidth_quota > 0 ? round((\ / \->bandwidth_quota) * 100, 2) : 0;
        
        return [
            'api' => [
                'used' => \,
                'total' => \->api_quota,
                'percent' => \,
            ],
            'ai' => [
                'used' => \,
                'total' => \->ai_quota,
                'percent' => \,
            ],
            'storage' => [
                'used' => \,
                'total' => \->storage_quota,
                'percent' => \,
            ],
            'bandwidth' => [
                'used' => \,
                'total' => \->bandwidth_quota,
                'percent' => \,
            ],
        ];
    }
    
    /**
     * 获取用户已使用的额度
     *
     * @param User \ 用户
     * @param string \ 额度类型
     * @param Carbon|null \ 开始日期
     * @param Carbon|null \ 结束日期
     * @return int
     */
    protected function getUsedQuota(User \, string \, ?Carbon \ = null, ?Carbon \ = null): int
    {
        \ = QuotaUsage::where('user_id', \->id)
            ->where('quota_type', \);
            
        if (\) {
            \->where('used_at', '>=', \);
        }
        
        if (\) {
            \->where('used_at', '<=', \);
        }
        
        return \->sum('amount');
    }
    
    /**
     * 检查用户是否有足够的额度
     *
     * @param User \ 用户
     * @param string \ 额度类型
     * @param int \ 需要的额度
     * @return bool
     */
    public function hasEnoughQuota(User \, string \, int \): bool
    {
        // 获取用户当前有效的会员订阅
        \ = \->activeSubscription();
        
        // 如果没有有效订阅，使用基础额度
        if (!\) {
            \ = config('membership.free_tier.' . \ . '_quota', 0);
        } else {
            // 获取会员等级的额度
            \ = \->level;
            \ = \->{\ . '_quota'};
        }
        
        // 获取已使用的额度
        \ = \->getUsedQuota(\, \);
        
        // 检查剩余额度是否足够
        return (\ - \) >= \;
    }
    
    /**
     * 获取用户额度使用统计
     *
     * @param User \ 用户
     * @param string \ 额度类型
     * @param string \ 时间周期 (day, week, month, year)
     * @return array
     */
    public function getUsageStats(User \, string \, string \ = 'month'): array
    {
        \ = Carbon::now();
        
        switch (\) {
            case 'day':
                \ = \->copy()->startOfDay();
                \ = 'hour';
                \ = 'H:00';
                break;
            case 'week':
                \ = \->copy()->startOfWeek();
                \ = 'day';
                \ = 'Y-m-d';
                break;
            case 'year':
                \ = \->copy()->startOfYear();
                \ = 'month';
                \ = 'Y-m';
                break;
            case 'month':
            default:
                \ = \->copy()->startOfMonth();
                \ = 'day';
                \ = 'Y-m-d';
                break;
        }
        // 查询数据
        $data = QuotaUsage::where("user_id", $user->id)
            ->where("quota_type", $quotaType)
            ->where("used_at", ">=", $startDate)
            ->select(
                DB::raw("SUM(amount) as total"),
                DB::raw("DATE_FORMAT(used_at, \"" . $format . "\") as date")
            )
            ->groupBy("date")
            ->orderBy("date")
            ->get()
            ->pluck("total", "date")
            ->toArray();
            
        // 生成完整的日期范围
        $dateRange = [];
        $current = $startDate->copy();
        
        while ($current <= $now) {
            $key = $current->format($format);
            $dateRange[$key] = $data[$key] ?? 0;
            
            switch ($groupBy) {
                case "hour":
                    $current->addHour();
                    break;
                case "day":
                    $current->addDay();
                    break;
                case "month":
                    $current->addMonth();
                    break;
                default:
                    $current->addDay();
                    break;
            }
        }
        
        return [
            "labels" => array_keys($dateRange),
            "data" => array_values($dateRange),
        ];
    }
    
    /**
     * 获取用户额度使用趋势
     *
     * @param User $user 用户
     * @param string $quotaType 额度类型
     * @param int $days 天数
     * @return array
     */
    public function getUsageTrend(User $user, string $quotaType, int $days = 30): array
    {
        $now = Carbon::now();
        $startDate = $now->copy()->subDays($days);
        
        // 查询每日使用量
        $dailyUsage = QuotaUsage::where("user_id", $user->id)
            ->where("quota_type", $quotaType)
            ->where("used_at", ">=", $startDate)
            ->select(
                DB::raw("SUM(amount) as total"),
                DB::raw("DATE(used_at) as date")
            )
            ->groupBy("date")
            ->orderBy("date")
            ->get();
            
        // 计算移动平均值
        $movingAverage = [];
        $windowSize = min(7, $days);
        
        for ($i = 0; $i < count($dailyUsage) - $windowSize + 1; $i++) {
            $sum = 0;
            for ($j = $i; $j < $i + $windowSize; $j++) {
                $sum += $dailyUsage[$j]->total;
            }
            $movingAverage[] = $sum / $windowSize;
        }
        
        return [
            "daily" => $dailyUsage->pluck("total")->toArray(),
            "dates" => $dailyUsage->pluck("date")->toArray(),
            "trend" => $movingAverage,
        ];
    }
}
