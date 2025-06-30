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
     * �����µĿ�����ʵ��
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware("auth");
    }

    /**
     * ��ʾ��Ա����ҳ��
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        // ��ȡ��ǰ����
        $currentSubscription = $user->currentSubscription;
        $currentLevel = $user->getCurrentMembershipLevel();
        
        // ��ȡ������ʷ
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
     * ��ʾ��Ա����ҳ��
     *
     * @return \Illuminate\View\View
     */
    public function showUpgrade()
    {
        $user = Auth::user();
        $currentLevel = $user->getCurrentMembershipLevel();
        
        // ��ȡ���п��õĻ�Ա�ȼ�
        $levels = MembershipLevel::where("status", "active")
            ->orderBy("sort_order")
            ->get();
        
        // ��ȡ֧����ʽ
        $paymentMethods = $user->paymentMethods ?? [];
        
        return view("membership.upgrade", compact(
            "user",
            "currentLevel",
            "levels",
            "paymentMethods"
        ));
    }

    /**
     * �����Ա��������
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
        
        // ��ȡ��Ա�ȼ�
        $level = MembershipLevel::findOrFail($levelId);
        
        // ����۸�
        $price = $subscriptionType === "monthly" ? $level->price_monthly : $level->price_yearly;
        
        // ���㶩�Ľ�������
        $durationDays = $subscriptionType === "monthly" ? $level->duration_days : ($level->duration_days * 12);
        $startDate = now();
        $endDate = $startDate->copy()->addDays($durationDays);
        
        try {
            // ��������
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
            
            // ��������
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
            
            // ����֧��
            // ����Ӧ�õ���֧������API����ʵ��֧������
            // Ϊ����ʾ�����Ǽ���֧���ɹ�
            $paymentSuccess = true;
            
            if ($paymentSuccess) {
                // ���¶����Ͷ���״̬
                $order->status = "completed";
                $order->payment_status = "paid";
                $order->save();
                
                $subscription->status = "active";
                $subscription->save();
                
                // ����û������ж��ģ�����ȡ��
                $currentSubscription = $user->currentSubscription;
                if ($currentSubscription && $currentSubscription->id !== $subscription->id) {
                    $currentSubscription->status = "cancelled";
                    $currentSubscription->cancelled_at = now();
                    $currentSubscription->cancellation_reason = "�������¶���";
                    $currentSubscription->save();
                }
                
                return redirect()->route("subscription")->with("success", "��ϲ�����ѳɹ������� {$level->name} ��Ա��");
            } else {
                // ֧��ʧ��
                $order->status = "failed";
                $order->save();
                
                $subscription->status = "failed";
                $subscription->save();
                
                return redirect()->back()->with("error", "֧������ʧ�ܣ����Ժ����Ի���ϵ�ͷ���");
            }
        } catch (\Exception $e) {
            Log::error("��Ա��������ʧ��", [
                "error" => $e->getMessage(),
                "user_id" => $user->id,
                "level_id" => $levelId
            ]);
            
            return redirect()->back()->with("error", "������������ʱ�������Ժ����ԡ�");
        }
    }

    /**
     * ȡ����Ա����
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
        
        // ��ȡ����
        $subscription = MembershipSubscription::where("id", $subscriptionId)
            ->where("user_id", $user->id)
            ->where("status", "active")
            ->first();
        
        if (!$subscription) {
            return redirect()->back()->with("error", "δ�ҵ���Ч�Ķ��ġ�");
        }
        
        try {
            // ȡ������
            $subscription->status = "cancelled";
            $subscription->cancelled_at = now();
            $subscription->cancellation_reason = $request->reason;
            $subscription->auto_renew = false;
            $subscription->save();
            
            return redirect()->route("subscription")->with("success", "���Ļ�Ա�����ѳɹ�ȡ����");
        } catch (\Exception $e) {
            Log::error("ȡ������ʧ��", [
                "error" => $e->getMessage(),
                "user_id" => $user->id,
                "subscription_id" => $subscriptionId
            ]);
            
            return redirect()->back()->with("error", "������������ʱ�������Ժ����ԡ�");
        }
    }

    /**
     * ��ʾ������ʷҳ��
     *
     * @return \Illuminate\View\View
     */
    public function history()
    {
        $user = Auth::user();
        
        // ��ȡ���ж�����ʷ
        $subscriptions = $user->subscriptions()
            ->with("membershipLevel")
            ->orderBy("created_at", "desc")
            ->paginate(10);
        
        return view("membership.history", compact("user", "subscriptions"));
    }
    
    /**
     * ����Ψһ������
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
