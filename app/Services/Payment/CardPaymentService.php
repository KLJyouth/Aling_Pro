<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Log;

class CardPaymentService implements PaymentServiceInterface
{
    /**
     * 银行卡支付配置
     *
     * @var array
     */
    protected $config;
    
    /**
     * 创建银行卡支付服务实例
     *
     * @return void
     */
    public function __construct()
    {
        $this->config = [
            "api_key" => config("payment.card.api_key"),
            "api_secret" => config("payment.card.api_secret"),
            "gateway" => config("payment.card.gateway"),
            "notify_url" => config("payment.card.notify_url"),
            "return_url" => config("payment.card.return_url"),
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
            // 实际项目中，这里应该调用银行卡支付网关API创建支付
            // 为了演示，我们返回模拟数据
            
            $orderNo = $data["order_no"];
            $amount = $data["amount"];
            $subject = $data["subject"];
            
            Log::info("创建银行卡支付", [
                "order_no" => $orderNo,
                "amount" => $amount,
                "subject" => $subject
            ]);
            
            // 模拟支付链接
            $paymentUrl = "https://payment.example.com/card?order_no={$orderNo}&amount={$amount}&return_url=" . urlencode($this->config["return_url"]);
            
            return [
                "success" => true,
                "payment_id" => "card_" . $orderNo,
                "payment_url" => $paymentUrl,
                "message" => "支付创建成功"
            ];
        } catch (\Exception $e) {
            Log::error("创建银行卡支付失败", [
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
            // 实际项目中，这里应该调用银行卡支付网关API查询支付状态
            // 为了演示，我们返回模拟数据
            
            $orderNo = str_replace("card_", "", $paymentId);
            
            Log::info("查询银行卡支付状态", [
                "payment_id" => $paymentId,
                "order_no" => $orderNo
            ]);
            
            // 模拟支付状态
            $status = rand(0, 10) > 3 ? "PAID" : "PENDING";
            
            return [
                "success" => true,
                "payment_id" => $paymentId,
                "order_no" => $orderNo,
                "status" => $status,
                "paid_at" => $status === "PAID" ? date("Y-m-d H:i:s") : null,
                "message" => "查询成功"
            ];
        } catch (\Exception $e) {
            Log::error("查询银行卡支付状态失败", [
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
            // 实际项目中，这里应该调用银行卡支付网关API取消支付
            // 为了演示，我们返回模拟数据
            
            $orderNo = str_replace("card_", "", $paymentId);
            
            Log::info("取消银行卡支付", [
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
            Log::error("取消银行卡支付失败", [
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
            // 实际项目中，这里应该调用银行卡支付网关API退款
            // 为了演示，我们返回模拟数据
            
            $orderNo = str_replace("card_", "", $paymentId);
            
            Log::info("银行卡支付退款", [
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
            Log::error("银行卡支付退款失败", [
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
            // 实际项目中，这里应该调用银行卡支付网关API验证通知
            // 为了演示，我们返回模拟数据
            
            Log::info("验证银行卡支付通知", [
                "data" => $data
            ]);
            
            // 假设验证成功
            return [
                "success" => true,
                "payment_id" => "card_" . ($data["order_no"] ?? ""),
                "order_no" => $data["order_no"] ?? "",
                "transaction_id" => $data["transaction_id"] ?? "",
                "amount" => $data["amount"] ?? 0,
                "status" => $data["status"] ?? "",
                "paid_at" => date("Y-m-d H:i:s"),
                "message" => "通知验证成功"
            ];
        } catch (\Exception $e) {
            Log::error("验证银行卡支付通知失败", [
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
