<?php

namespace App\Services\Membership;

use App\Models\MemberPoint;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PointService
{
    /**
     * 为用户添加积分
     *
     * @param \App\Models\User $user 用户
     * @param int $points 积分数量
     * @param string $action 操作类型
     * @param string $description 描述
     * @param string|null $referenceId 关联ID
     * @param string|null $referenceType 关联类型
     * @param \DateTime|null $expiresAt 过期时间
     * @return \App\Models\MemberPoint|null
     */
    public function addPoints(
        User $user, 
        int $points, 
        string $action, 
        string $description, 
        ?string $referenceId = null, 
        ?string $referenceType = null,
        ?\DateTime $expiresAt = null
    ) {
        try {
            if ($points <= 0) {
                Log::warning("尝试添加非正数积分", [
                    "user_id" => $user->id,
                    "points" => $points,
                    "action" => $action
                ]);
                return null;
            }
            
            $pointRecord = MemberPoint::create([
                "user_id" => $user->id,
                "points" => $points,
                "action" => $action,
                "description" => $description,
                "reference_id" => $referenceId,
                "reference_type" => $referenceType,
                "expires_at" => $expiresAt
            ]);
            
            Log::info("积分添加成功", [
                "user_id" => $user->id,
                "points" => $points,
                "action" => $action,
                "point_id" => $pointRecord->id
            ]);
            
            return $pointRecord;
        } catch (\Exception $e) {
            Log::error("添加积分失败", [
                "error" => $e->getMessage(),
                "user_id" => $user->id,
                "points" => $points,
                "action" => $action
            ]);
            
            return null;
        }
    }
    
    /**
     * 消费用户积分
     *
     * @param \App\Models\User $user 用户
     * @param int $points 消费的积分数量
     * @param string $action 操作类型
     * @param string $description 描述
     * @param string|null $referenceId 关联ID
     * @param string|null $referenceType 关联类型
     * @return bool 是否成功
     */
    public function consumePoints(
        User $user, 
        int $points, 
        string $action, 
        string $description, 
        ?string $referenceId = null, 
        ?string $referenceType = null
    ) {
        try {
            if ($points <= 0) {
                Log::warning("尝试消费非正数积分", [
                    "user_id" => $user->id,
                    "points" => $points,
                    "action" => $action
                ]);
                return false;
            }
            
            // 检查用户是否有足够的积分
            $availablePoints = $this->getAvailablePoints($user);
            
            if ($availablePoints < $points) {
                Log::warning("用户积分不足", [
                    "user_id" => $user->id,
                    "required" => $points,
                    "available" => $availablePoints,
                    "action" => $action
                ]);
                return false;
            }
            
            // 记录积分消费
            MemberPoint::create([
                "user_id" => $user->id,
                "points" => -$points, // 负数表示消费
                "action" => $action,
                "description" => $description,
                "reference_id" => $referenceId,
                "reference_type" => $referenceType
            ]);
            
            Log::info("积分消费成功", [
                "user_id" => $user->id,
                "points" => $points,
                "action" => $action
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("消费积分失败", [
                "error" => $e->getMessage(),
                "user_id" => $user->id,
                "points" => $points,
                "action" => $action
            ]);
            
            return false;
        }
    }
    
    /**
     * 获取用户可用积分
     *
     * @param \App\Models\User $user
     * @return int
     */
    public function getAvailablePoints(User $user)
    {
        return $user->points()
            ->where(function ($query) {
                $query->whereNull("expires_at")
                      ->orWhere("expires_at", ">", now());
            })
            ->sum("points");
    }
    
    /**
     * 获取用户积分历史
     *
     * @param \App\Models\User $user
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPointsHistory(User $user, int $limit = 10)
    {
        return $user->points()
            ->orderBy("created_at", "desc")
            ->limit($limit)
            ->get();
    }
    
    /**
     * 获取用户积分统计
     *
     * @param \App\Models\User $user
     * @return array
     */
    public function getPointsStats(User $user)
    {
        $totalEarned = $user->points()
            ->where("points", ">", 0)
            ->sum("points");
            
        $totalConsumed = $user->points()
            ->where("points", "<", 0)
            ->sum("points");
            
        $expired = $user->points()
            ->where("expires_at", "<", now())
            ->where("points", ">", 0)
            ->sum("points");
            
        $available = $this->getAvailablePoints($user);
        
        // 按类型统计
        $byType = $user->points()
            ->selectRaw("action, SUM(points) as total")
            ->groupBy("action")
            ->get()
            ->pluck("total", "action")
            ->toArray();
        
        return [
            "total_earned" => $totalEarned,
            "total_consumed" => abs($totalConsumed),
            "expired" => $expired,
            "available" => $available,
            "by_type" => $byType
        ];
    }
    
    /**
     * 检查并处理过期积分
     *
     * @return int 处理的过期积分记录数
     */
    public function processExpiredPoints()
    {
        $expiredPoints = MemberPoint::where("expires_at", "<", now())
            ->where("points", ">", 0)
            ->get();
            
        $count = 0;
        
        foreach ($expiredPoints as $point) {
            // 记录积分过期
            MemberPoint::create([
                "user_id" => $point->user_id,
                "points" => -$point->points,
                "action" => "points_expired",
                "description" => "积分过期",
                "reference_id" => (string) $point->id,
                "reference_type" => "point"
            ]);
            
            $count++;
            
            Log::info("积分过期", [
                "user_id" => $point->user_id,
                "points" => $point->points,
                "point_id" => $point->id
            ]);
        }
        
        return $count;
    }
}
