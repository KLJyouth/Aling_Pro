<?php

namespace App\Services\Membership;

use App\Models\MemberPoint;
use App\Models\MemberReferral;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    /**
     * 处理用户推荐
     *
     * @param \App\Models\User $referredUser 被推荐的用户
     * @param string $referralCode 推荐码
     * @return bool
     */
    public function processReferral(User $referredUser, string $referralCode)
    {
        try {
            // 查找推荐人
            $referrer = User::where("referral_code", $referralCode)->first();
            
            if (!$referrer) {
                Log::warning("无效的推荐码", ["referral_code" => $referralCode]);
                return false;
            }
            
            // 防止自我推荐
            if ($referrer->id === $referredUser->id) {
                Log::warning("用户尝试自我推荐", ["user_id" => $referredUser->id]);
                return false;
            }
            
            // 检查是否已经被推荐过
            $existingReferral = MemberReferral::where("referred_id", $referredUser->id)->first();
            
            if ($existingReferral) {
                Log::warning("用户已被推荐", ["user_id" => $referredUser->id, "referrer_id" => $existingReferral->referrer_id]);
                return false;
            }
            
            // 创建推荐记录
            $referral = new MemberReferral();
            $referral->referrer_id = $referrer->id;
            $referral->referred_id = $referredUser->id;
            $referral->code = $referralCode;
            $referral->status = "pending"; // 初始状态为待处理
            $referral->save();
            
            // 更新推荐人的推荐统计
            $referrer->total_referrals += 1;
            $referrer->save();
            
            Log::info("推荐成功创建", [
                "referral_id" => $referral->id,
                "referrer_id" => $referrer->id,
                "referred_id" => $referredUser->id
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("处理推荐失败", [
                "error" => $e->getMessage(),
                "referral_code" => $referralCode,
                "referred_id" => $referredUser->id
            ]);
            
            return false;
        }
    }
    
    /**
     * 完成推荐流程（当被推荐用户完成特定操作后）
     *
     * @param \App\Models\MemberReferral $referral 推荐记录
     * @param string $action 完成的操作（例如：subscription, purchase）
     * @param array $data 额外数据
     * @return bool
     */
    public function completeReferral(MemberReferral $referral, string $action, array $data = [])
    {
        try {
            // 检查推荐状态
            if ($referral->status !== "pending") {
                Log::warning("推荐已处理，无法再次完成", ["referral_id" => $referral->id, "status" => $referral->status]);
                return false;
            }
            
            // 获取推荐人和被推荐人
            $referrer = User::find($referral->referrer_id);
            $referred = User::find($referral->referred_id);
            
            if (!$referrer || !$referred) {
                Log::error("推荐人或被推荐人不存在", ["referral_id" => $referral->id]);
                return false;
            }
            
            // 根据不同操作给予不同奖励
            $points = 0;
            $description = "";
            
            switch ($action) {
                case "subscription":
                    // 被推荐用户订阅会员
                    $points = 100;
                    $description = "推荐用户 {$referred->name} 成功订阅会员";
                    break;
                    
                case "purchase":
                    // 被推荐用户购买产品
                    $amount = $data["amount"] ?? 0;
                    $points = floor($amount * 0.1); // 10%的积分奖励
                    $description = "推荐用户 {$referred->name} 成功购买产品";
                    break;
                    
                default:
                    $points = 10;
                    $description = "推荐用户 {$referred->name} 完成注册";
                    break;
            }
            
            // 给推荐人奖励积分
            if ($points > 0) {
                MemberPoint::create([
                    "user_id" => $referrer->id,
                    "points" => $points,
                    "action" => "referral_reward",
                    "description" => $description,
                    "reference_id" => (string) $referral->id,
                    "reference_type" => "referral"
                ]);
                
                // 更新推荐人的总推荐积分
                $referrer->total_referral_points += $points;
                $referrer->save();
                
                // 更新推荐记录
                $referral->status = "completed";
                $referral->points_awarded = $points;
                $referral->reward_type = "points";
                $referral->reward_amount = $points;
                $referral->reward_description = $description;
                $referral->save();
                
                Log::info("推荐奖励已发放", [
                    "referral_id" => $referral->id,
                    "referrer_id" => $referrer->id,
                    "points" => $points
                ]);
                
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error("完成推荐失败", [
                "error" => $e->getMessage(),
                "referral_id" => $referral->id,
                "action" => $action
            ]);
            
            return false;
        }
    }
    
    /**
     * 获取用户的推荐统计
     *
     * @param \App\Models\User $user
     * @return array
     */
    public function getReferralStats(User $user)
    {
        $totalReferrals = $user->total_referrals;
        $totalPoints = $user->total_referral_points;
        
        $pendingReferrals = MemberReferral::where("referrer_id", $user->id)
            ->where("status", "pending")
            ->count();
            
        $completedReferrals = MemberReferral::where("referrer_id", $user->id)
            ->where("status", "completed")
            ->count();
            
        return [
            "total" => $totalReferrals,
            "pending" => $pendingReferrals,
            "completed" => $completedReferrals,
            "points" => $totalPoints
        ];
    }
    
    /**
     * 获取用户的推荐链接
     *
     * @param \App\Models\User $user
     * @return string
     */
    public function getReferralLink(User $user)
    {
        return url("/register/referral?ref=" . $user->referral_code);
    }
}
