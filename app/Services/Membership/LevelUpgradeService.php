<?php

namespace App\Services\Membership;

use App\Models\Membership\MembershipLevel;
use App\Models\Membership\MembershipSubscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LevelUpgradeService
{
    /**
     * 积分服务
     *
     * @var PointService
     */
    protected $pointService;

    /**
     * 创建一个新的服务实例
     *
     * @param PointService $pointService
     * @return void
     */
    public function __construct(PointService $pointService)
    {
        $this->pointService = $pointService;
    }

    /**
     * 检查并处理用户会员等级升级
     *
     * @param User $user 用户
     * @return bool
     */
    public function checkAndUpgradeLevel(User $user): bool
    {
        // 获取用户当前活跃的会员订阅
        $subscription = $user->activeSubscription();
        if (!$subscription) {
            return false;
        }

        // 获取所有可升级的会员等级
        $currentLevel = $subscription->level;
        $upgradableLevels = $this->getUpgradableLevels($currentLevel);
        
        if ($upgradableLevels->isEmpty()) {
            return false;
        }

        // 检查升级条件
        foreach ($upgradableLevels as $level) {
            if ($this->checkUpgradeConditions($user, $currentLevel, $level)) {
                return $this->upgradeMembership($user, $subscription, $level);
            }
        }

        return false;
    }

    /**
     * 获取可升级的会员等级
     *
     * @param MembershipLevel $currentLevel 当前会员等级
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getUpgradableLevels(MembershipLevel $currentLevel)
    {
        return MembershipLevel::where("status", "active")
            ->where("sort_order", ">", $currentLevel->sort_order)
            ->orderBy("sort_order")
            ->get();
    }

    /**
     * 检查升级条件
     *
     * @param User $user 用户
     * @param MembershipLevel $currentLevel 当前会员等级
     * @param MembershipLevel $targetLevel 目标会员等级
     * @return bool
     */
    protected function checkUpgradeConditions(User $user, MembershipLevel $currentLevel, MembershipLevel $targetLevel): bool
    {
        // 检查积分条件
        $requiredPoints = $targetLevel->upgrade_points ?? 0;
        $userPoints = $this->pointService->getCurrentPoints($user);
        
        // 检查消费金额条件
        $requiredSpending = $targetLevel->upgrade_spending ?? 0;
        $userSpending = $this->getUserTotalSpending($user);
        
        // 检查连续订阅时间条件
        $requiredMonths = $targetLevel->upgrade_months ?? 0;
        $userSubscriptionMonths = $this->getUserContinuousSubscriptionMonths($user);
        
        // 所有条件都必须满足
        return $userPoints >= $requiredPoints &&
               $userSpending >= $requiredSpending &&
               $userSubscriptionMonths >= $requiredMonths;
    }

    /**
     * 升级用户会员等级
     *
     * @param User $user 用户
     * @param MembershipSubscription $subscription 当前订阅
     * @param MembershipLevel $newLevel 新会员等级
     * @return bool
     */
    protected function upgradeMembership(User $user, MembershipSubscription $subscription, MembershipLevel $newLevel): bool
    {
        DB::beginTransaction();
        try {
            // 更新订阅记录
            $oldLevel = $subscription->level;
            $subscription->membership_level_id = $newLevel->id;
            $subscription->save();
            
            // 记录升级日志
            $this->logUpgrade($user, $oldLevel, $newLevel);
            
            // 发送通知
            $this->sendUpgradeNotification($user, $oldLevel, $newLevel);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("会员升级失败", [
                "error" => $e->getMessage(),
                "user_id" => $user->id,
                "old_level_id" => $subscription->membership_level_id,
                "new_level_id" => $newLevel->id,
            ]);
            return false;
        }
    }

    /**
     * 获取用户总消费金额
     *
     * @param User $user 用户
     * @return float
     */
    protected function getUserTotalSpending(User $user): float
    {
        return DB::table("orders")
            ->where("user_id", $user->id)
            ->where("status", "completed")
            ->sum("total_amount");
    }

    /**
     * 获取用户连续订阅月数
     *
     * @param User $user 用户
     * @return int
     */
    protected function getUserContinuousSubscriptionMonths(User $user): int
    {
        $subscription = $user->activeSubscription();
        if (!$subscription) {
            return 0;
        }
        
        $startDate = $subscription->start_date;
        return $startDate->diffInMonths(Carbon::now());
    }

    /**
     * 记录升级日志
     *
     * @param User $user 用户
     * @param MembershipLevel $oldLevel 旧会员等级
     * @param MembershipLevel $newLevel 新会员等级
     * @return void
     */
    protected function logUpgrade(User $user, MembershipLevel $oldLevel, MembershipLevel $newLevel): void
    {
        // 这里可以根据实际需求实现日志记录
        Log::info("用户会员等级升级", [
            "user_id" => $user->id,
            "old_level" => [
                "id" => $oldLevel->id,
                "name" => $oldLevel->name,
                "code" => $oldLevel->code,
            ],
            "new_level" => [
                "id" => $newLevel->id,
                "name" => $newLevel->name,
                "code" => $newLevel->code,
            ],
            "upgrade_time" => Carbon::now()->toDateTimeString(),
        ]);
    }

    /**
     * 发送升级通知
     *
     * @param User $user 用户
     * @param MembershipLevel $oldLevel 旧会员等级
     * @param MembershipLevel $newLevel 新会员等级
     * @return void
     */
    protected function sendUpgradeNotification(User $user, MembershipLevel $oldLevel, MembershipLevel $newLevel): void
    {
        // 这里可以根据实际需求实现通知逻辑，比如发送邮件、短信、站内信等
        // 示例：
        // $user->notify(new MembershipUpgradeNotification($oldLevel, $newLevel));
    }
}
