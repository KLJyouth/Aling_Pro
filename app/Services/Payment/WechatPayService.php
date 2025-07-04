<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Log;

class WechatPayService implements PaymentServiceInterface
{
    /**
     * 微信支付配置
     *
     * @var array
     */
    protected $config;
    
    /**
     * 创建微信支付服务实例
     *
     * @return void
     */
    public function __construct()
    {
        $this->config = [
            "app_id" => config("payment.wechat.app_id"),
            "mch_id" => config("payment.wechat.mch_id"),
            "key" => config("payment.wechat.key"),
            "cert_path" => config("payment.wechat.cert_path"),
            "key_path" => config("payment.wechat.key_path"),
            "notify_url" => config("payment.wechat.notify_url"),
            "return_url" => config("payment.wechat.return_url"),
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
            // 实际项目中，这里应该调用微信支付SDK创建支付
            // 为了演示，我们返回模拟数据
            
            $orderNo = $data["order_no"];
            $amount = $data["amount"];
            $subject = $data["subject"];
            
            Log::info("创建微信支付", [
                "order_no" => $orderNo,
                "amount" => $amount,
                "subject" => $subject
            ]);
            
            // 模拟支付链接
            $codeUrl = "weixin://wxpay/bizpayurl?pr=XXXXXXX";
            
            return [
                "success" => true,
                "payment_id" => "wechat_" . $orderNo,
                "code_url" => $codeUrl,
                "qr_code" => "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($codeUrl),
                "message" => "支付创建成功"
            ];
        } catch (\Exception $e) {
            Log::error("创建微信支付失败", [
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
            // 实际项目中，这里应该调用微信支付SDK查询支付状态
            // 为了演示，我们返回模拟数据
            
            $orderNo = str_replace("wechat_", "", $paymentId);
            
            Log::info("查询微信支付状态", [
                "payment_id" => $paymentId,
                "order_no" => $orderNo
            ]);
            
            // 模拟支付状态
            $status = rand(0, 10) > 3 ? "SUCCESS" : "NOTPAY";
            
            return [
                "success" => true,
                "payment_id" => $paymentId,
                "order_no" => $orderNo,
                "status" => $status,
                "paid_at" => $status === "SUCCESS" ? date("Y-m-d H:i:s") : null,
                "message" => "查询成功"
            ];
        } catch (\Exception $e) {
            Log::error("查询微信支付状态失败", [
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
            // 实际项目中，这里应该调用微信支付SDK取消支付
            // 为了演示，我们返回模拟数据
            
            $orderNo = str_replace("wechat_", "", $paymentId);
            
            Log::info("取消微信支付", [
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
            Log::error("取消微信支付失败", [
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
            // 实际项目中，这里应该调用微信支付SDK退款
            // 为了演示，我们返回模拟数据
            
            $orderNo = str_replace("wechat_", "", $paymentId);
            
            Log::info("微信支付退款", [
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
            Log::error("微信支付退款失败", [
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
            // 实际项目中，这里应该调用微信支付SDK验证通知
            // 为了演示，我们返回模拟数据
            
            Log::info("验证微信支付通知", [
                "data" => $data
            ]);
            
            // 假设验证成功
            return [
                "success" => true,
                "payment_id" => "wechat_" . ($data["out_trade_no"] ?? ""),
                "order_no" => $data["out_trade_no"] ?? "",
                "transaction_id" => $data["transaction_id"] ?? "",
                "amount" => isset($data["total_fee"]) ? $data["total_fee"] / 100 : 0,
                "status" => $data["result_code"] ?? "",
                "paid_at" => date("Y-m-d H:i:s"),
                "message" => "通知验证成功"
            ];
        } catch (\Exception $e) {
            Log::error("验证微信支付通知失败", [
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
