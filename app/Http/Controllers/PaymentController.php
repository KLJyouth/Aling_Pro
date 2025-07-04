<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Payment\PaymentFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * 显示支付页面
     *
     * @param  string  $orderNo  订单号
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($orderNo)
    {
        $user = Auth::user();
        
        // 获取订单
        $order = Order::where("order_number", $orderNo)
            ->where("user_id", $user->id)
            ->first();
        
        if (!$order) {
            return redirect()->route("dashboard")->with("error", "订单不存在");
        }
        
        // 如果订单已支付，跳转到订单详情页
        if ($order->payment_status === "paid") {
            return redirect()->route("order.show", $order->id)->with("success", "订单已支付");
        }
        
        return view("payment.show", compact("order"));
    }
    
    /**
     * 处理支付
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $orderNo  订单号
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function process(Request $request, $orderNo)
    {
        $user = Auth::user();
        
        // 获取订单
        $order = Order::where("order_number", $orderNo)
            ->where("user_id", $user->id)
            ->first();
        
        if (!$order) {
            return response()->json([
                "success" => false,
                "message" => "订单不存在"
            ]);
        }
        
        // 如果订单已支付，返回成功
        if ($order->payment_status === "paid") {
            return response()->json([
                "success" => true,
                "message" => "订单已支付",
                "redirect" => route("order.show", $order->id)
            ]);
        }
        
        // 获取支付方式
        $paymentMethod = $request->input("payment_method", $order->payment_method);
        
        try {
            // 创建支付服务
            $paymentService = PaymentFactory::create($paymentMethod);
            
            // 创建支付
            $paymentResult = $paymentService->createPayment([
                "order_no" => $order->order_number,
                "amount" => $order->total_amount,
                "subject" => "订单支付 #" . $order->order_number,
                "body" => "订单支付 #" . $order->order_number,
                "user_id" => $user->id
            ]);
            
            if (!$paymentResult["success"]) {
                return response()->json([
                    "success" => false,
                    "message" => $paymentResult["message"]
                ]);
            }
            
            // 更新订单支付方式
            $order->payment_method = $paymentMethod;
            $order->save();
            
            return response()->json([
                "success" => true,
                "message" => "支付创建成功",
                "payment_id" => $paymentResult["payment_id"],
                "payment_url" => $paymentResult["payment_url"] ?? null,
                "qr_code" => $paymentResult["qr_code"] ?? null,
                "code_url" => $paymentResult["code_url"] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error("支付处理失败", [
                "error" => $e->getMessage(),
                "order_no" => $orderNo,
                "payment_method" => $paymentMethod
            ]);
            
            return response()->json([
                "success" => false,
                "message" => "支付处理失败：" . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 查询支付状态
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $orderNo  订单号
     * @return \Illuminate\Http\JsonResponse
     */
    public function query(Request $request, $orderNo)
    {
        $user = Auth::user();
        
        // 获取订单
        $order = Order::where("order_number", $orderNo)
            ->where("user_id", $user->id)
            ->first();
        
        if (!$order) {
            return response()->json([
                "success" => false,
                "message" => "订单不存在"
            ]);
        }
        
        // 如果订单已支付，返回成功
        if ($order->payment_status === "paid") {
            return response()->json([
                "success" => true,
                "status" => "paid",
                "message" => "订单已支付",
                "redirect" => route("order.show", $order->id)
            ]);
        }
        
        // 获取支付方式
        $paymentMethod = $order->payment_method;
        
        if (!$paymentMethod) {
            return response()->json([
                "success" => false,
                "message" => "未选择支付方式"
            ]);
        }
        
        try {
            // 创建支付服务
            $paymentService = PaymentFactory::create($paymentMethod);
            
            // 查询支付状态
            $paymentResult = $paymentService->queryPayment($paymentMethod . "_" . $order->order_number);
            
            if (!$paymentResult["success"]) {
                return response()->json([
                    "success" => false,
                    "message" => $paymentResult["message"]
                ]);
            }
            
            $status = $paymentResult["status"];
            
            // 检查支付状态
            $isPaid = false;
            
            switch ($paymentMethod) {
                case "alipay":
                    $isPaid = $status === "TRADE_SUCCESS" || $status === "TRADE_FINISHED";
                    break;
                case "wechat":
                    $isPaid = $status === "SUCCESS";
                    break;
                case "card":
                    $isPaid = $status === "PAID";
                    break;
                default:
                    $isPaid = false;
            }
            
            if ($isPaid) {
                // 更新订单状态
                $order->status = "completed";
                $order->payment_status = "paid";
                $order->paid_at = now();
                $order->save();
                
                // 处理订单完成后的业务逻辑
                $this->handleOrderCompleted($order);
                
                return response()->json([
                    "success" => true,
                    "status" => "paid",
                    "message" => "支付成功",
                    "redirect" => route("order.show", $order->id)
                ]);
            } else {
                return response()->json([
                    "success" => true,
                    "status" => "pending",
                    "message" => "支付处理中"
                ]);
            }
        } catch (\Exception $e) {
            Log::error("查询支付状态失败", [
                "error" => $e->getMessage(),
                "order_no" => $orderNo,
                "payment_method" => $paymentMethod
            ]);
            
            return response()->json([
                "success" => false,
                "message" => "查询支付状态失败：" . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 支付宝支付回调
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function alipayNotify(Request $request)
    {
        Log::info("支付宝支付回调", $request->all());
        
        try {
            // 创建支付服务
            $paymentService = PaymentFactory::create("alipay");
            
            // 验证通知
            $result = $paymentService->verifyNotify($request->all());
            
            if (!$result["success"]) {
                Log::error("支付宝支付回调验证失败", [
                    "message" => $result["message"],
                    "data" => $request->all()
                ]);
                
                return response("fail");
            }
            
            // 获取订单号
            $orderNo = $result["order_no"];
            
            // 获取订单
            $order = Order::where("order_number", $orderNo)->first();
            
            if (!$order) {
                Log::error("支付宝支付回调订单不存在", [
                    "order_no" => $orderNo,
                    "data" => $request->all()
                ]);
                
                return response("fail");
            }
            
            // 如果订单已支付，直接返回成功
            if ($order->payment_status === "paid") {
                return response("success");
            }
            
            // 检查支付状态
            $status = $result["status"];
            $isPaid = $status === "TRADE_SUCCESS" || $status === "TRADE_FINISHED";
            
            if ($isPaid) {
                // 更新订单状态
                $order->status = "completed";
                $order->payment_status = "paid";
                $order->paid_at = now();
                $order->save();
                
                // 处理订单完成后的业务逻辑
                $this->handleOrderCompleted($order);
                
                Log::info("支付宝支付成功", [
                    "order_no" => $orderNo,
                    "data" => $request->all()
                ]);
                
                return response("success");
            }
            
            return response("success");
        } catch (\Exception $e) {
            Log::error("支付宝支付回调处理失败", [
                "error" => $e->getMessage(),
                "data" => $request->all()
            ]);
            
            return response("fail");
        }
    }
    
    /**
     * 微信支付回调
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function wechatNotify(Request $request)
    {
        Log::info("微信支付回调", $request->all());
        
        try {
            // 创建支付服务
            $paymentService = PaymentFactory::create("wechat");
            
            // 验证通知
            $result = $paymentService->verifyNotify($request->all());
            
            if (!$result["success"]) {
                Log::error("微信支付回调验证失败", [
                    "message" => $result["message"],
                    "data" => $request->all()
                ]);
                
                return response("<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[验证失败]]></return_msg></xml>");
            }
            
            // 获取订单号
            $orderNo = $result["order_no"];
            
            // 获取订单
            $order = Order::where("order_number", $orderNo)->first();
            
            if (!$order) {
                Log::error("微信支付回调订单不存在", [
                    "order_no" => $orderNo,
                    "data" => $request->all()
                ]);
                
                return response("<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[订单不存在]]></return_msg></xml>");
            }
            
            // 如果订单已支付，直接返回成功
            if ($order->payment_status === "paid") {
                return response("<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>");
            }
            
            // 检查支付状态
            $status = $result["status"];
            $isPaid = $status === "SUCCESS";
            
            if ($isPaid) {
                // 更新订单状态
                $order->status = "completed";
                $order->payment_status = "paid";
                $order->paid_at = now();
                $order->save();
                
                // 处理订单完成后的业务逻辑
                $this->handleOrderCompleted($order);
                
                Log::info("微信支付成功", [
                    "order_no" => $orderNo,
                    "data" => $request->all()
                ]);
                
                return response("<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>");
            }
            
            return response("<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>");
        } catch (\Exception $e) {
            Log::error("微信支付回调处理失败", [
                "error" => $e->getMessage(),
                "data" => $request->all()
            ]);
            
            return response("<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[处理失败]]></return_msg></xml>");
        }
    }
    
    /**
     * 银行卡支付回调
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function cardNotify(Request $request)
    {
        Log::info("银行卡支付回调", $request->all());
        
        try {
            // 创建支付服务
            $paymentService = PaymentFactory::create("card");
            
            // 验证通知
            $result = $paymentService->verifyNotify($request->all());
            
            if (!$result["success"]) {
                Log::error("银行卡支付回调验证失败", [
                    "message" => $result["message"],
                    "data" => $request->all()
                ]);
                
                return response()->json(["status" => "error", "message" => "验证失败"]);
            }
            
            // 获取订单号
            $orderNo = $result["order_no"];
            
            // 获取订单
            $order = Order::where("order_number", $orderNo)->first();
            
            if (!$order) {
                Log::error("银行卡支付回调订单不存在", [
                    "order_no" => $orderNo,
                    "data" => $request->all()
                ]);
                
                return response()->json(["status" => "error", "message" => "订单不存在"]);
            }
            
            // 如果订单已支付，直接返回成功
            if ($order->payment_status === "paid") {
                return response()->json(["status" => "success", "message" => "OK"]);
            }
            
            // 检查支付状态
            $status = $result["status"];
            $isPaid = $status === "PAID";
            
            if ($isPaid) {
                // 更新订单状态
                $order->status = "completed";
                $order->payment_status = "paid";
                $order->paid_at = now();
                $order->save();
                
                // 处理订单完成后的业务逻辑
                $this->handleOrderCompleted($order);
                
                Log::info("银行卡支付成功", [
                    "order_no" => $orderNo,
                    "data" => $request->all()
                ]);
                
                return response()->json(["status" => "success", "message" => "OK"]);
            }
            
            return response()->json(["status" => "success", "message" => "OK"]);
        } catch (\Exception $e) {
            Log::error("银行卡支付回调处理失败", [
                "error" => $e->getMessage(),
                "data" => $request->all()
            ]);
            
            return response()->json(["status" => "error", "message" => "处理失败"]);
        }
    }
    
    /**
     * 支付宝支付同步回调
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function alipayReturn(Request $request)
    {
        Log::info("支付宝支付同步回调", $request->all());
        
        // 获取订单号
        $orderNo = $request->input("out_trade_no");
        
        // 获取订单
        $order = Order::where("order_number", $orderNo)->first();
        
        if (!$order) {
            return redirect()->route("dashboard")->with("error", "订单不存在");
        }
        
        // 如果订单已支付，直接跳转到订单详情页
        if ($order->payment_status === "paid") {
            return redirect()->route("order.show", $order->id)->with("success", "支付成功");
        }
        
        // 查询支付状态
        try {
            // 创建支付服务
            $paymentService = PaymentFactory::create("alipay");
            
            // 查询支付状态
            $paymentResult = $paymentService->queryPayment("alipay_" . $orderNo);
            
            if (!$paymentResult["success"]) {
                return redirect()->route("payment.show", $orderNo)->with("error", $paymentResult["message"]);
            }
            
            $status = $paymentResult["status"];
            $isPaid = $status === "TRADE_SUCCESS" || $status === "TRADE_FINISHED";
            
            if ($isPaid) {
                // 更新订单状态
                $order->status = "completed";
                $order->payment_status = "paid";
                $order->paid_at = now();
                $order->save();
                
                // 处理订单完成后的业务逻辑
                $this->handleOrderCompleted($order);
                
                return redirect()->route("order.show", $order->id)->with("success", "支付成功");
            } else {
                return redirect()->route("payment.show", $orderNo)->with("info", "支付处理中，请稍后刷新页面查看支付结果");
            }
        } catch (\Exception $e) {
            Log::error("支付宝支付同步回调处理失败", [
                "error" => $e->getMessage(),
                "data" => $request->all()
            ]);
            
            return redirect()->route("payment.show", $orderNo)->with("error", "支付处理失败，请稍后刷新页面查看支付结果");
        }
    }
    
    /**
     * 微信支付同步回调
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function wechatReturn(Request $request)
    {
        Log::info("微信支付同步回调", $request->all());
        
        // 获取订单号
        $orderNo = $request->input("out_trade_no");
        
        // 获取订单
        $order = Order::where("order_number", $orderNo)->first();
        
        if (!$order) {
            return redirect()->route("dashboard")->with("error", "订单不存在");
        }
        
        // 如果订单已支付，直接跳转到订单详情页
        if ($order->payment_status === "paid") {
            return redirect()->route("order.show", $order->id)->with("success", "支付成功");
        }
        
        // 查询支付状态
        try {
            // 创建支付服务
            $paymentService = PaymentFactory::create("wechat");
            
            // 查询支付状态
            $paymentResult = $paymentService->queryPayment("wechat_" . $orderNo);
            
            if (!$paymentResult["success"]) {
                return redirect()->route("payment.show", $orderNo)->with("error", $paymentResult["message"]);
            }
            
            $status = $paymentResult["status"];
            $isPaid = $status === "SUCCESS";
            
            if ($isPaid) {
                // 更新订单状态
                $order->status = "completed";
                $order->payment_status = "paid";
                $order->paid_at = now();
                $order->save();
                
                // 处理订单完成后的业务逻辑
                $this->handleOrderCompleted($order);
                
                return redirect()->route("order.show", $order->id)->with("success", "支付成功");
            } else {
                return redirect()->route("payment.show", $orderNo)->with("info", "支付处理中，请稍后刷新页面查看支付结果");
            }
        } catch (\Exception $e) {
            Log::error("微信支付同步回调处理失败", [
                "error" => $e->getMessage(),
                "data" => $request->all()
            ]);
            
            return redirect()->route("payment.show", $orderNo)->with("error", "支付处理失败，请稍后刷新页面查看支付结果");
        }
    }
    
    /**
     * 银行卡支付同步回调
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cardReturn(Request $request)
    {
        Log::info("银行卡支付同步回调", $request->all());
        
        // 获取订单号
        $orderNo = $request->input("order_no");
        
        // 获取订单
        $order = Order::where("order_number", $orderNo)->first();
        
        if (!$order) {
            return redirect()->route("dashboard")->with("error", "订单不存在");
        }
        
        // 如果订单已支付，直接跳转到订单详情页
        if ($order->payment_status === "paid") {
            return redirect()->route("order.show", $order->id)->with("success", "支付成功");
        }
        
        // 查询支付状态
        try {
            // 创建支付服务
            $paymentService = PaymentFactory::create("card");
            
            // 查询支付状态
            $paymentResult = $paymentService->queryPayment("card_" . $orderNo);
            
            if (!$paymentResult["success"]) {
                return redirect()->route("payment.show", $orderNo)->with("error", $paymentResult["message"]);
            }
            
            $status = $paymentResult["status"];
            $isPaid = $status === "PAID";
            
            if ($isPaid) {
                // 更新订单状态
                $order->status = "completed";
                $order->payment_status = "paid";
                $order->paid_at = now();
                $order->save();
                
                // 处理订单完成后的业务逻辑
                $this->handleOrderCompleted($order);
                
                return redirect()->route("order.show", $order->id)->with("success", "支付成功");
            } else {
                return redirect()->route("payment.show", $orderNo)->with("info", "支付处理中，请稍后刷新页面查看支付结果");
            }
        } catch (\Exception $e) {
            Log::error("银行卡支付同步回调处理失败", [
                "error" => $e->getMessage(),
                "data" => $request->all()
            ]);
            
            return redirect()->route("payment.show", $orderNo)->with("error", "支付处理失败，请稍后刷新页面查看支付结果");
        }
    }
    
    /**
     * 处理订单完成后的业务逻辑
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    protected function handleOrderCompleted($order)
    {
        // 根据订单类型处理不同的业务逻辑
        switch ($order->order_type) {
            case "subscription":
                // 处理会员订阅
                $this->handleSubscriptionOrder($order);
                break;
            
            case "point":
                // 处理积分购买
                $this->handlePointOrder($order);
                break;
            
            case "product":
                // 处理产品购买
                $this->handleProductOrder($order);
                break;
            
            default:
                // 默认处理
                break;
        }
    }
    
    /**
     * 处理会员订阅订单
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    protected function handleSubscriptionOrder($order)
    {
        // 获取订阅
        $subscription = $order->subscription;
        
        if ($subscription) {
            // 更新订阅状态
            $subscription->status = "active";
            $subscription->save();
            
            // 如果用户有现有订阅，将其取消
            $user = $order->user;
            $currentSubscription = $user->currentSubscription;
            
            if ($currentSubscription && $currentSubscription->id !== $subscription->id) {
                $currentSubscription->status = "cancelled";
                $currentSubscription->cancelled_at = now();
                $currentSubscription->cancellation_reason = "升级到新订阅";
                $currentSubscription->save();
            }
        }
    }
    
    /**
     * 处理积分购买订单
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    protected function handlePointOrder($order)
    {
        // 获取用户
        $user = $order->user;
        
        // 获取积分数量
        $points = $order->meta["points"] ?? 0;
        
        if ($points > 0) {
            // 添加积分
            $pointService = app(\App\Services\Membership\PointService::class);
            $pointService->addPoints($user, $points, "points_purchase", "购买积分");
        }
    }
    
    /**
     * 处理产品购买订单
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    protected function handleProductOrder($order)
    {
        // 处理产品购买逻辑
    }
}
