<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Log;

class AlipayService implements PaymentServiceInterface
{
    /**
     * 支付宝配置
     *
     * @var array
     */
    protected $config;
    
    /**
     * 创建支付宝服务实例
     *
     * @return void
     */
    public function __construct()
    {
        $this->config = [
            "app_id" => config("payment.alipay.app_id"),
            "private_key" => config("payment.alipay.private_key"),
            "public_key" => config("payment.alipay.public_key"),
            "gateway" => config("payment.alipay.gateway"),
            "notify_url" => config("payment.alipay.notify_url"),
            "return_url" => config("payment.alipay.return_url"),
        ];
    }
    
    /**
     * 创建支付
     *
     * @param  array  $data  支付数据
     * @return array 支付结果
     */
    public function createPayment(array $data): array
    {
        try {
            // 实际项目中，这里应该调用支付宝SDK创建支付
            // 为了演示，我们返回模拟数据
            
            $orderNo = $data["order_no"];
            $amount = $data["amount"];
            $subject = $data["subject"];
            
            Log::info("创建支付宝支付", [
                "order_no" => $orderNo,
                "amount" => $amount,
                "subject" => $subject
            ]);
            
            // 模拟支付链接
            $paymentUrl = "https://openapi.alipay.com/gateway.do?app_id={$this->config["app_id"]}&method=alipay.trade.page.pay&format=JSON&return_url={$this->config["return_url"]}&notify_url={$this->config["notify_url"]}&timestamp=" . urlencode(date("Y-m-d H:i:s")) . "&version=1.0&sign_type=RSA2&sign=XXXXX&biz_content=" . urlencode(json_encode([
                "out_trade_no" => $orderNo,
                "product_code" => "FAST_INSTANT_TRADE_PAY",
                "total_amount" => $amount,
                "subject" => $subject
            ]));
            
            return [
                "success" => true,
                "payment_id" => "alipay_" . $orderNo,
                "payment_url" => $paymentUrl,
                "qr_code" => "https://qr.alipay.com/bax0339559lzxmceru0k00a7",
                "message" => "支付创建成功"
            ];
        } catch (\Exception $e) {
            Log::error("创建支付宝支付失败", [
                "error" => $e->getMessage(),
                "data" => $data
            ]);
            
            return [
                "success" => false,
                "message" => "创建支付失败：" . $e->getMessage()
            ];
        }
    }
    
    /**
     * 查询支付状态
     *
     * @param  string  $paymentId  支付ID
     * @return array 支付状态
     */
    public function queryPayment(string $paymentId): array
    {
        try {
            // 实际项目中，这里应该调用支付宝SDK查询支付状态
            // 为了演示，我们返回模拟数据
            
            $orderNo = str_replace("alipay_", "", $paymentId);
            
            Log::info("查询支付宝支付状态", [
                "payment_id" => $paymentId,
                "order_no" => $orderNo
            ]);
            
            // 模拟支付状态
            $status = rand(0, 10) > 3 ? "TRADE_SUCCESS" : "WAIT_BUYER_PAY";
            
            return [
                "success" => true,
                "payment_id" => $paymentId,
                "order_no" => $orderNo,
                "status" => $status,
                "paid_at" => $status === "TRADE_SUCCESS" ? date("Y-m-d H:i:s") : null,
                "message" => "查询成功"
            ];
        } catch (\Exception $e) {
            Log::error("查询支付宝支付状态失败", [
                "error" => $e->getMessage(),
                "payment_id" => $paymentId
            ]);
            
            return [
                "success" => false,
                "message" => "查询支付状态失败：" . $e->getMessage()
            ];
        }
    }
    
    /**
     * 取消支付
     *
     * @param  string  $paymentId  支付ID
     * @return array 取消结果
     */
    public function cancelPayment(string $paymentId): array
    {
        try {
            // 实际项目中，这里应该调用支付宝SDK取消支付
            // 为了演示，我们返回模拟数据
            
            $orderNo = str_replace("alipay_", "", $paymentId);
            
            Log::info("取消支付宝支付", [
                "payment_id" => $paymentId,
                "order_no" => $orderNo
            ]);
            
            return [
                "success" => true,
                "payment_id" => $paymentId,
                "order_no" => $orderNo,
                "message" => "支付取消成功"
            ];
        } catch (\Exception $e) {
            Log::error("取消支付宝支付失败", [
                "error" => $e->getMessage(),
                "payment_id" => $paymentId
            ]);
            
            return [
                "success" => false,
                "message" => "取消支付失败：" . $e->getMessage()
            ];
        }
    }
    
    /**
     * 退款
     *
     * @param  string  $paymentId  支付ID
     * @param  float  $amount  退款金额
     * @param  string  $reason  退款原因
     * @return array 退款结果
     */
    public function refund(string $paymentId, float $amount, string $reason = ""): array
    {
        try {
            // 实际项目中，这里应该调用支付宝SDK退款
            // 为了演示，我们返回模拟数据
            
            $orderNo = str_replace("alipay_", "", $paymentId);
            
            Log::info("支付宝退款", [
                "payment_id" => $paymentId,
                "order_no" => $orderNo,
                "amount" => $amount,
                "reason" => $reason
            ]);
            
            return [
                "success" => true,
                "payment_id" => $paymentId,
                "order_no" => $orderNo,
                "refund_id" => "refund_" . uniqid(),
                "amount" => $amount,
                "message" => "退款申请成功"
            ];
        } catch (\Exception $e) {
            Log::error("支付宝退款失败", [
                "error" => $e->getMessage(),
                "payment_id" => $paymentId,
                "amount" => $amount
            ]);
            
            return [
                "success" => false,
                "message" => "退款失败：" . $e->getMessage()
            ];
        }
    }
    
    /**
     * 验证支付通知
     *
     * @param  array  $data  通知数据
     * @return array 验证结果
     */
    public function verifyNotify(array $data): array
    {
        try {
            // 实际项目中，这里应该调用支付宝SDK验证通知
            // 为了演示，我们返回模拟数据
            
            Log::info("验证支付宝支付通知", [
                "data" => $data
            ]);
            
            // 假设验证成功
            return [
                "success" => true,
                "payment_id" => "alipay_" . ($data["out_trade_no"] ?? ""),
                "order_no" => $data["out_trade_no"] ?? "",
                "trade_no" => $data["trade_no"] ?? "",
                "amount" => $data["total_amount"] ?? 0,
                "status" => $data["trade_status"] ?? "",
                "paid_at" => date("Y-m-d H:i:s"),
                "message" => "通知验证成功"
            ];
        } catch (\Exception $e) {
            Log::error("验证支付宝支付通知失败", [
                "error" => $e->getMessage(),
                "data" => $data
            ]);
            
            return [
                "success" => false,
                "message" => "通知验证失败：" . $e->getMessage()
            ];
        }
    }
}
