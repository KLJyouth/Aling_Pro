<?php

namespace App\Http\Controllers;

use App\Models\MembershipLevel;
use App\Models\MembershipSubscription;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MembershipController extends Controller
{
    /**
     * 创建新的控制器实例
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware("auth");
    }

    /**
     * 显示会员订阅页面
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        // 获取当前订阅
        $currentSubscription = $user->currentSubscription;
        $currentLevel = $user->getCurrentMembershipLevel();
        
        // 获取订阅历史
        $subscriptionHistory = $user->subscriptions()
            ->with("membershipLevel")
            ->latest()
            ->take(5)
            ->get();
        
        return view("membership.index", compact(
            "user",
            "currentSubscription",
            "currentLevel",
            "subscriptionHistory"
        ));
    }

    /**
     * 显示会员升级页面
     *
     * @return \Illuminate\View\View
     */
    public function showUpgrade()
    {
        $user = Auth::user();
        $currentLevel = $user->getCurrentMembershipLevel();
        
        // 获取所有可用的会员等级
        $levels = MembershipLevel::where("status", "active")
            ->orderBy("sort_order")
            ->get();
        
        // 获取支付方式
        $paymentMethods = $user->paymentMethods ?? [];
        
        return view("membership.upgrade", compact(
            "user",
            "currentLevel",
            "levels",
            "paymentMethods"
        ));
    }

    /**
     * 处理会员升级请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processUpgrade(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "level_id" => "required|exists:membership_levels,id",
            "subscription_type" => "required|in:monthly,yearly",
            "payment_method" => "required|string",
            "agree_terms" => "required|accepted",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        $levelId = $request->level_id;
        $subscriptionType = $request->subscription_type;
        $paymentMethod = $request->payment_method;
        
        // 获取会员等级
        $level = MembershipLevel::findOrFail($levelId);
        
        // 计算价格
        $price = $subscriptionType === "monthly" ? $level->price_monthly : $level->price_yearly;
        
        // 计算订阅结束日期
        $durationDays = $subscriptionType === "monthly" ? $level->duration_days : ($level->duration_days * 12);
        $startDate = now();
        $endDate = $startDate->copy()->addDays($durationDays);
        
        try {
            // 创建订单
            $order = new Order();
            $order->user_id = $user->id;
            $order->order_number = $this->generateOrderNumber();
            $order->order_type = "subscription";
            $order->subtotal_amount = $price;
            $order->discount_amount = 0;
            $order->total_amount = $price;
            $order->status = "pending";
            $order->payment_method = $paymentMethod;
            $order->payment_status = "pending";
            $order->save();
            
            // 创建订阅
            $subscription = new MembershipSubscription();
            $subscription->user_id = $user->id;
            $subscription->membership_level_id = $level->id;
            $subscription->order_id = $order->id;
            $subscription->subscription_no = MembershipSubscription::generateSubscriptionNumber();
            $subscription->start_date = $startDate;
            $subscription->end_date = $endDate;
            $subscription->price_paid = $price;
            $subscription->subscription_type = $subscriptionType;
            $subscription->auto_renew = $request->has("auto_renew");
            $subscription->status = "pending";
            $subscription->save();
            
            // 处理支付
            // 这里应该调用支付网关API进行实际支付处理
            // 为了演示，我们假设支付成功
            $paymentSuccess = true;
            
            if ($paymentSuccess) {
                // 更新订单和订阅状态
                $order->status = "completed";
                $order->payment_status = "paid";
                $order->save();
                
                $subscription->status = "active";
                $subscription->save();
                
                // 如果用户有现有订阅，将其取消
                $currentSubscription = $user->currentSubscription;
                if ($currentSubscription && $currentSubscription->id !== $subscription->id) {
                    $currentSubscription->status = "cancelled";
                    $currentSubscription->cancelled_at = now();
                    $currentSubscription->cancellation_reason = "升级到新订阅";
                    $currentSubscription->save();
                }
                
                return redirect()->route("subscription")->with("success", "恭喜！您已成功升级到 {$level->name} 会员。");
            } else {
                // 支付失败
                $order->status = "failed";
                $order->save();
                
                $subscription->status = "failed";
                $subscription->save();
                
                return redirect()->back()->with("error", "支付处理失败，请稍后再试或联系客服。");
            }
        } catch (\Exception $e) {
            Log::error("会员升级处理失败", [
                "error" => $e->getMessage(),
                "user_id" => $user->id,
                "level_id" => $levelId
            ]);
            
            return redirect()->back()->with("error", "处理您的请求时出错，请稍后再试。");
        }
    }

    /**
     * 取消会员订阅
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "subscription_id" => "required|exists:membership_subscriptions,id",
            "reason" => "nullable|string|max:255",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        $subscriptionId = $request->subscription_id;
        
        // 获取订阅
        $subscription = MembershipSubscription::where("id", $subscriptionId)
            ->where("user_id", $user->id)
            ->where("status", "active")
            ->first();
        
        if (!$subscription) {
            return redirect()->back()->with("error", "未找到有效的订阅。");
        }
        
        try {
            // 取消订阅
            $subscription->status = "cancelled";
            $subscription->cancelled_at = now();
            $subscription->cancellation_reason = $request->reason;
            $subscription->auto_renew = false;
            $subscription->save();
            
            return redirect()->route("subscription")->with("success", "您的会员订阅已成功取消。");
        } catch (\Exception $e) {
            Log::error("取消订阅失败", [
                "error" => $e->getMessage(),
                "user_id" => $user->id,
                "subscription_id" => $subscriptionId
            ]);
            
            return redirect()->back()->with("error", "处理您的请求时出错，请稍后再试。");
        }
    }

    /**
     * 显示订阅历史页面
     *
     * @return \Illuminate\View\View
     */
    public function history()
    {
        $user = Auth::user();
        
        // 获取所有订阅历史
        $subscriptions = $user->subscriptions()
            ->with("membershipLevel")
            ->orderBy("created_at", "desc")
            ->paginate(10);
        
        return view("membership.history", compact("user", "subscriptions"));
    }
    
    /**
     * 生成唯一订单号
     *
     * @return string
     */
    protected function generateOrderNumber()
    {
        $prefix = "ORD";
        $timestamp = now()->format("YmdHis");
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));
        
        return $prefix . $timestamp . $random;
    }
}
