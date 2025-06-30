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
     * ���ַ���
     *
     * @var PointService
     */
    protected $pointService;

    /**
     * ����һ���µķ���ʵ��
     *
     * @param PointService $pointService
     * @return void
     */
    public function __construct(PointService $pointService)
    {
        $this->pointService = $pointService;
    }

    /**
     * ��鲢�����û���Ա�ȼ�����
     *
     * @param User $user �û�
     * @return bool
     */
    public function checkAndUpgradeLevel(User $user): bool
    {
        // ��ȡ�û���ǰ��Ծ�Ļ�Ա����
        $subscription = $user->activeSubscription();
        if (!$subscription) {
            return false;
        }

        // ��ȡ���п������Ļ�Ա�ȼ�
        $currentLevel = $subscription->level;
        $upgradableLevels = $this->getUpgradableLevels($currentLevel);
        
        if ($upgradableLevels->isEmpty()) {
            return false;
        }

        // �����������
        foreach ($upgradableLevels as $level) {
            if ($this->checkUpgradeConditions($user, $currentLevel, $level)) {
                return $this->upgradeMembership($user, $subscription, $level);
            }
        }

        return false;
    }

    /**
     * ��ȡ�������Ļ�Ա�ȼ�
     *
     * @param MembershipLevel $currentLevel ��ǰ��Ա�ȼ�
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
     * �����������
     *
     * @param User $user �û�
     * @param MembershipLevel $currentLevel ��ǰ��Ա�ȼ�
     * @param MembershipLevel $targetLevel Ŀ���Ա�ȼ�
     * @return bool
     */
    protected function checkUpgradeConditions(User $user, MembershipLevel $currentLevel, MembershipLevel $targetLevel): bool
    {
        // ����������
        $requiredPoints = $targetLevel->upgrade_points ?? 0;
        $userPoints = $this->pointService->getCurrentPoints($user);
        
        // ������ѽ������
        $requiredSpending = $targetLevel->upgrade_spending ?? 0;
        $userSpending = $this->getUserTotalSpending($user);
        
        // �����������ʱ������
        $requiredMonths = $targetLevel->upgrade_months ?? 0;
        $userSubscriptionMonths = $this->getUserContinuousSubscriptionMonths($user);
        
        // ������������������
        return $userPoints >= $requiredPoints &&
               $userSpending >= $requiredSpending &&
               $userSubscriptionMonths >= $requiredMonths;
    }

    /**
     * �����û���Ա�ȼ�
     *
     * @param User $user �û�
     * @param MembershipSubscription $subscription ��ǰ����
     * @param MembershipLevel $newLevel �»�Ա�ȼ�
     * @return bool
     */
    protected function upgradeMembership(User $user, MembershipSubscription $subscription, MembershipLevel $newLevel): bool
    {
        DB::beginTransaction();
        try {
            // ���¶��ļ�¼
            $oldLevel = $subscription->level;
            $subscription->membership_level_id = $newLevel->id;
            $subscription->save();
            
            // ��¼������־
            $this->logUpgrade($user, $oldLevel, $newLevel);
            
            // ����֪ͨ
            $this->sendUpgradeNotification($user, $oldLevel, $newLevel);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("��Ա����ʧ��", [
                "error" => $e->getMessage(),
                "user_id" => $user->id,
                "old_level_id" => $subscription->membership_level_id,
                "new_level_id" => $newLevel->id,
            ]);
            return false;
        }
    }

    /**
     * ��ȡ�û������ѽ��
     *
     * @param User $user �û�
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
     * ��ȡ�û�������������
     *
     * @param User $user �û�
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
     * ��¼������־
     *
     * @param User $user �û�
     * @param MembershipLevel $oldLevel �ɻ�Ա�ȼ�
     * @param MembershipLevel $newLevel �»�Ա�ȼ�
     * @return void
     */
    protected function logUpgrade(User $user, MembershipLevel $oldLevel, MembershipLevel $newLevel): void
    {
        // ������Ը���ʵ������ʵ����־��¼
        Log::info("�û���Ա�ȼ�����", [
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
     * ��������֪ͨ
     *
     * @param User $user �û�
     * @param MembershipLevel $oldLevel �ɻ�Ա�ȼ�
     * @param MembershipLevel $newLevel �»�Ա�ȼ�
     * @return void
     */
    protected function sendUpgradeNotification(User $user, MembershipLevel $oldLevel, MembershipLevel $newLevel): void
    {
        // ������Ը���ʵ������ʵ��֪ͨ�߼������緢���ʼ������š�վ���ŵ�
        // ʾ����
        // $user->notify(new MembershipUpgradeNotification($oldLevel, $newLevel));
    }
}
