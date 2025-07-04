<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;
use App\Models\Membership\MembershipLevel;
use App\Models\Membership\MembershipSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MembershipSubscriptionController extends Controller
{
    /**
     * 显示会员订阅列表页面
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view("admin.membership.subscriptions");
    }

    /**
     * 获取会员订阅数据（用于DataTables）
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        $draw = $request->input("draw");
        $start = $request->input("start");
        $length = $request->input("length");
        $search = $request->input("search.value");
        $orderColumn = $request->input("order.0.column");
        $orderDir = $request->input("order.0.dir");
        
        $columns = [
            "membership_subscriptions.id", 
            "users.name", 
            "users.email", 
            "membership_levels.name", 
            "membership_subscriptions.start_date", 
            "membership_subscriptions.end_date", 
            "membership_subscriptions.price_paid", 
            "membership_subscriptions.status"
        ];
        
        $query = MembershipSubscription::query()
            ->join("users", "membership_subscriptions.user_id", "=", "users.id")
            ->join("membership_levels", "membership_subscriptions.membership_level_id", "=", "membership_levels.id")
            ->select(
                "membership_subscriptions.*",
                "users.name as user_name",
                "users.email as user_email",
                "membership_levels.name as level_name"
            );
        
        // 搜索
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where("users.name", "like", "%{$search}%")
                  ->orWhere("users.email", "like", "%{$search}%")
                  ->orWhere("membership_levels.name", "like", "%{$search}%")
                  ->orWhere("membership_subscriptions.subscription_no", "like", "%{$search}%");
            });
        }
        
        // 排序
        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        } else {
            $query->orderBy("membership_subscriptions.created_at", "desc");
        }
        
        // 总记录数
        $total = $query->count();
        
        // 分页
        $subscriptions = $query->skip($start)->take($length)->get();
        
        $data = [];
        foreach ($subscriptions as $subscription) {
            $data[] = [
                "id" => $subscription->id,
                "user_name" => $subscription->user_name,
                "user_email" => $subscription->user_email,
                "level_name" => $subscription->level_name,
                "start_date" => $subscription->start_date->format("Y-m-d"),
                "end_date" => $subscription->end_date->format("Y-m-d"),
                "price_paid" => $subscription->price_paid,
                "auto_renew" => $subscription->auto_renew,
                "status" => $subscription->status,
                "days_remaining" => $subscription->daysRemaining()
            ];
        }
        
        return response()->json([
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $data
        ]);
    }

    
    /**
     * 显示会员订阅编辑页面
     *
     * @param int|null $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id = null)
    {
        if ($id) {
            $subscription = MembershipSubscription::with(["user", "level"])->findOrFail($id);
        } else {
            $subscription = null;
        }
        
        $users = User::orderBy("name")->get();
        $levels = MembershipLevel::where("status", 1)->orderBy("sort_order")->orderBy("name")->get();
        
        return view("admin.membership.subscription_edit", compact("subscription", "users", "levels"));
    }
    
    /**
     * 保存会员订阅
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        // 验证输入
        $validator = Validator::make($request->all(), [
            "user_id" => "required|exists:users,id",
            "membership_level_id" => "required|exists:membership_levels,id",
            "start_date" => "required|date",
            "end_date" => "required|date|after:start_date",
            "price_paid" => "required|numeric|min:0",
            "status" => "required|in:active,pending,cancelled,expired",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 获取或创建会员订阅
        if ($request->has("id")) {
            $subscription = MembershipSubscription::findOrFail($request->input("id"));
        } else {
            $subscription = new MembershipSubscription();
            $subscription->subscription_no = "SUB" . date("Ymd") . strtoupper(Str::random(6));
        }
        
        // 设置基本属性
        $subscription->user_id = $request->input("user_id");
        $subscription->membership_level_id = $request->input("membership_level_id");
        $subscription->start_date = $request->input("start_date");
        $subscription->end_date = $request->input("end_date");
        $subscription->price_paid = $request->input("price_paid");
        $subscription->auto_renew = $request->has("auto_renew");
        $subscription->status = $request->input("status");
        
        // 如果状态是已取消，记录取消时间和原因
        if ($subscription->status == "cancelled" && !$subscription->cancelled_at) {
            $subscription->cancelled_at = now();
            $subscription->cancellation_reason = $request->input("cancellation_reason");
        }
        
        // 保存会员订阅
        $subscription->save();
        
        // 设置成功消息
        session()->flash("admin_message", "会员订阅已保存");
        session()->flash("admin_message_type", "success");
        
        return redirect()->route("admin.membership.subscriptions");
    }
    
    /**
     * 取消会员订阅
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request)
    {
        // 验证CSRF令牌
        if (!$request->has("csrf_token") || $request->input("csrf_token") !== session("csrf_token")) {
            return response()->json([
                "success" => false,
                "message" => "无效的安全令牌"
            ]);
        }
        
        // 获取会员订阅
        $id = $request->input("id");
        $subscription = MembershipSubscription::find($id);
        
        if (!$subscription) {
            return response()->json([
                "success" => false,
                "message" => "会员订阅不存在"
            ]);
        }
        
        // 取消会员订阅
        $subscription->status = "cancelled";
        $subscription->cancelled_at = now();
        $subscription->cancellation_reason = $request->input("reason", "管理员取消");
        $subscription->auto_renew = false;
        $subscription->save();
        
        return response()->json([
            "success" => true,
            "message" => "会员订阅已取消"
        ]);
    }
}
