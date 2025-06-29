<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class PaymentGatewayService
{
    /**
     * 获取所有支付网关
     *
     * @param bool $onlyActive 是否只返回激活的网关
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllGateways($onlyActive = false)
    {
        $query = DB::table("payment_gateways")
            ->whereNull("deleted_at")
            ->orderBy("sort_order", "asc");

        if ($onlyActive) {
            $query->where("is_active", true);
        }

        return $query->get();
    }

    /**
     * 获取单个支付网关
     *
     * @param int|string $id 支付网关ID或代码
     * @return object|null
     */
    public function getGateway($id)
    {
        if (is_numeric($id)) {
            return DB::table("payment_gateways")
                ->where("id", $id)
                ->whereNull("deleted_at")
                ->first();
        } else {
            return DB::table("payment_gateways")
                ->where("code", $id)
                ->whereNull("deleted_at")
                ->first();
        }
    }

    /**
     * 创建支付网关
     *
     * @param array $data 支付网关数据
     * @return int 新创建的支付网关ID
     */
    public function createGateway(array $data)
    {
        // 确保配置是JSON格式
        if (is_array($data["config"])) {
            $data["config"] = json_encode($data["config"]);
        }

        $data["created_at"] = now();
        $data["updated_at"] = now();

        return DB::table("payment_gateways")->insertGetId($data);
    }

    /**
     * 更新支付网关
     *
     * @param int $id 支付网关ID
     * @param array $data 更新数据
     * @return bool
     */
    public function updateGateway($id, array $data)
    {
        // 确保配置是JSON格式
        if (isset($data["config"]) && is_array($data["config"])) {
            $data["config"] = json_encode($data["config"]);
        }

        $data["updated_at"] = now();

        return DB::table("payment_gateways")
            ->where("id", $id)
            ->update($data);
    }

    /**
     * 删除支付网关（软删除）
     *
     * @param int $id 支付网关ID
     * @return bool
     */
    public function deleteGateway($id)
    {
        return DB::table("payment_gateways")
            ->where("id", $id)
            ->update([
                "deleted_at" => now(),
                "updated_at" => now()
            ]);
    }

    /**
     * 激活/停用支付网关
     *
     * @param int $id 支付网关ID
     * @param bool $active 是否激活
     * @return bool
     */
    public function toggleGateway($id, $active = true)
    {
        return DB::table("payment_gateways")
            ->where("id", $id)
            ->update([
                "is_active" => $active,
                "updated_at" => now()
            ]);
    }

    /**
     * 切换测试模式
     *
     * @param int $id 支付网关ID
     * @param bool $testMode 是否为测试模式
     * @return bool
     */
    public function toggleTestMode($id, $testMode = true)
    {
        return DB::table("payment_gateways")
            ->where("id", $id)
            ->update([
                "is_test_mode" => $testMode,
                "updated_at" => now()
            ]);
    }

    /**
     * 创建支付交易
     *
     * @param array $data 交易数据
     * @return string 交易ID
     */
    public function createTransaction(array $data)
    {
        // 生成唯一交易ID
        $data["transaction_id"] = $this->generateTransactionId();
        
        // 设置默认状态为pending
        if (!isset($data["status"])) {
            $data["status"] = "pending";
        }
        
        // 记录客户端IP
        if (!isset($data["client_ip"])) {
            $data["client_ip"] = request()->ip();
        }
        
        $data["created_at"] = now();
        $data["updated_at"] = now();
        
        DB::table("payment_transactions")->insert($data);
        
        return $data["transaction_id"];
    }

    /**
     * 生成唯一交易ID
     *
     * @return string
     */
    protected function generateTransactionId()
    {
        $prefix = "TRX";
        $timestamp = date("YmdHis");
        $random = Str::random(6);
        
        return $prefix . $timestamp . $random;
    }

    /**
     * 更新交易状态
     *
     * @param string $transactionId 交易ID
     * @param string $status 新状态
     * @param array $additionalData 额外数据
     * @return bool
     */
    public function updateTransactionStatus($transactionId, $status, array $additionalData = [])
    {
        $updateData = array_merge($additionalData, [
            "status" => $status,
            "updated_at" => now()
        ]);
        
        // 如果状态为completed，设置支付时间
        if ($status === "completed" && !isset($additionalData["paid_at"])) {
            $updateData["paid_at"] = now();
        }
        
        return DB::table("payment_transactions")
            ->where("transaction_id", $transactionId)
            ->update($updateData);
    }

    /**
     * 获取交易详情
     *
     * @param string $transactionId 交易ID
     * @return object|null
     */
    public function getTransaction($transactionId)
    {
        return DB::table("payment_transactions")
            ->where("transaction_id", $transactionId)
            ->whereNull("deleted_at")
            ->first();
    }

    /**
     * 获取订单的所有交易
     *
     * @param string $orderId 订单ID
     * @return \Illuminate\Support\Collection
     */
    public function getTransactionsByOrderId($orderId)
    {
        return DB::table("payment_transactions")
            ->where("order_id", $orderId)
            ->whereNull("deleted_at")
            ->orderBy("created_at", "desc")
            ->get();
    }

    /**
     * 创建退款
     *
     * @param array $data 退款数据
     * @return string 退款ID
     */
    public function createRefund(array $data)
    {
        // 生成唯一退款ID
        $data["refund_id"] = $this->generateRefundId();
        
        // 设置默认状态为pending
        if (!isset($data["status"])) {
            $data["status"] = "pending";
        }
        
        $data["created_at"] = now();
        $data["updated_at"] = now();
        
        DB::table("payment_refunds")->insert($data);
        
        return $data["refund_id"];
    }

    /**
     * 生成唯一退款ID
     *
     * @return string
     */
    protected function generateRefundId()
    {
        $prefix = "REF";
        $timestamp = date("YmdHis");
        $random = Str::random(6);
        
        return $prefix . $timestamp . $random;
    }

    /**
     * 更新退款状态
     *
     * @param string $refundId 退款ID
     * @param string $status 新状态
     * @param array $additionalData 额外数据
     * @return bool
     */
    public function updateRefundStatus($refundId, $status, array $additionalData = [])
    {
        $updateData = array_merge($additionalData, [
            "status" => $status,
            "updated_at" => now()
        ]);
        
        // 如果状态为completed，设置退款时间
        if ($status === "completed" && !isset($additionalData["refunded_at"])) {
            $updateData["refunded_at"] = now();
        }
        
        return DB::table("payment_refunds")
            ->where("refund_id", $refundId)
            ->update($updateData);
    }

    /**
     * 获取退款详情
     *
     * @param string $refundId 退款ID
     * @return object|null
     */
    public function getRefund($refundId)
    {
        return DB::table("payment_refunds")
            ->where("refund_id", $refundId)
            ->whereNull("deleted_at")
            ->first();
    }

    /**
     * 获取交易的所有退款
     *
     * @param string $transactionId 交易ID
     * @return \Illuminate\Support\Collection
     */
    public function getRefundsByTransactionId($transactionId)
    {
        return DB::table("payment_refunds")
            ->where("transaction_id", $transactionId)
            ->whereNull("deleted_at")
            ->orderBy("created_at", "desc")
            ->get();
    }

    /**
     * 记录支付网关日志
     *
     * @param int $gatewayId 支付网关ID
     * @param string $action 操作类型
     * @param array $request 请求数据
     * @param array $response 响应数据
     * @param bool $isSuccess 是否成功
     * @param string $errorMessage 错误信息
     * @param string|null $transactionId 交易ID
     * @return int 日志ID
     */
    public function logGatewayAction($gatewayId, $action, $request = [], $response = [], $isSuccess = true, $errorMessage = null, $transactionId = null)
    {
        $logData = [
            "gateway_id" => $gatewayId,
            "transaction_id" => $transactionId,
            "action" => $action,
            "request" => is_array($request) ? json_encode($request) : $request,
            "response" => is_array($response) ? json_encode($response) : $response,
            "ip_address" => request()->ip(),
            "user_agent" => request()->userAgent(),
            "is_success" => $isSuccess,
            "error_message" => $errorMessage,
            "created_at" => now(),
            "updated_at" => now()
        ];
        
        return DB::table("payment_gateway_logs")->insertGetId($logData);
    }

    /**
     * 获取支付网关日志
     *
     * @param int $gatewayId 支付网关ID
     * @param int $limit 限制数量
     * @param int $offset 偏移量
     * @return \Illuminate\Support\Collection
     */
    public function getGatewayLogs($gatewayId, $limit = 50, $offset = 0)
    {
        return DB::table("payment_gateway_logs")
            ->where("gateway_id", $gatewayId)
            ->orderBy("created_at", "desc")
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    /**
     * 获取交易日志
     *
     * @param string $transactionId 交易ID
     * @return \Illuminate\Support\Collection
     */
    public function getTransactionLogs($transactionId)
    {
        return DB::table("payment_gateway_logs")
            ->where("transaction_id", $transactionId)
            ->orderBy("created_at", "desc")
            ->get();
    }

    /**
     * 获取支付设置
     *
     * @param string $key 设置键
     * @param mixed $default 默认值
     * @return mixed
     */
    public function getSetting($key, $default = null)
    {
        $cacheKey = "payment_setting:{$key}";
        
        // 尝试从缓存获取
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // 从数据库获取
        $setting = DB::table("payment_settings")
            ->where("key", $key)
            ->first();
        
        $value = $setting ? $setting->value : $default;
        
        // 缓存结果
        Cache::put($cacheKey, $value, 3600); // 缓存1小时
        
        return $value;
    }

    /**
     * 更新支付设置
     *
     * @param string $key 设置键
     * @param mixed $value 设置值
     * @param string $group 分组
     * @param string|null $description 描述
     * @return bool
     */
    public function updateSetting($key, $value, $group = "general", $description = null)
    {
        // 检查设置是否存在
        $exists = DB::table("payment_settings")
            ->where("key", $key)
            ->exists();
        
        if ($exists) {
            // 更新设置
            $result = DB::table("payment_settings")
                ->where("key", $key)
                ->update([
                    "value" => $value,
                    "updated_at" => now()
                ]);
        } else {
            // 创建设置
            $result = DB::table("payment_settings")->insert([
                "key" => $key,
                "value" => $value,
                "group" => $group,
                "description" => $description,
                "is_system" => false,
                "created_at" => now(),
                "updated_at" => now()
            ]);
        }
        
        // 更新缓存
        Cache::put("payment_setting:{$key}", $value, 3600);
        
        return $result;
    }

    /**
     * 获取分组设置
     *
     * @param string $group 分组名称
     * @return \Illuminate\Support\Collection
     */
    public function getSettingsByGroup($group)
    {
        return DB::table("payment_settings")
            ->where("group", $group)
            ->get();
    }

    /**
     * 获取所有设置
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllSettings()
    {
        return DB::table("payment_settings")
            ->orderBy("group")
            ->orderBy("key")
            ->get();
    }

    /**
     * 清除设置缓存
     *
     * @param string|null $key 特定设置键，为null则清除所有
     * @return void
     */
    public function clearSettingCache($key = null)
    {
        if ($key) {
            Cache::forget("payment_setting:{$key}");
        } else {
            // 获取所有设置键
            $keys = DB::table("payment_settings")
                ->pluck("key")
                ->toArray();
                
            foreach ($keys as $k) {
                Cache::forget("payment_setting:{$k}");
            }
        }
    }

    /**
     * 检查交易是否过期
     *
     * @param object $transaction 交易对象
     * @return bool
     */
    public function isTransactionExpired($transaction)
    {
        // 如果交易已完成或已退款，则不会过期
        if (in_array($transaction->status, ["completed", "refunded"])) {
            return false;
        }
        
        // 获取支付过期时间（分钟）
        $expireTime = (int)$this->getSetting("payment_expire_time", 30);
        
        // 计算过期时间
        $expireAt = Carbon::parse($transaction->created_at)->addMinutes($expireTime);
        
        return $expireAt->isPast();
    }

    /**
     * 处理过期交易
     *
     * @return int 处理的交易数量
     */
    public function handleExpiredTransactions()
    {
        // 获取支付过期时间（分钟）
        $expireTime = (int)$this->getSetting("payment_expire_time", 30);
        
        // 计算过期时间点
        $expireTime = now()->subMinutes($expireTime);
        
        // 查找所有过期的pending交易
        $expiredTransactions = DB::table("payment_transactions")
            ->where("status", "pending")
            ->where("created_at", "<", $expireTime)
            ->whereNull("deleted_at")
            ->get();
        
        $count = 0;
        foreach ($expiredTransactions as $transaction) {
            // 更新交易状态为失败
            $this->updateTransactionStatus($transaction->transaction_id, "failed", [
                "error_message" => "支付超时"
            ]);
            
            // 记录日志
            $this->logGatewayAction(
                $transaction->gateway_id,
                "transaction_expired",
                [],
                ["transaction_id" => $transaction->transaction_id],
                false,
                "支付超时",
                $transaction->transaction_id
            );
            
            $count++;
        }
        
        return $count;
    }

    /**
     * 获取支持的支付网关列表
     *
     * @return array
     */
    public function getSupportedGateways()
    {
        return [
            "alipay" => [
                "name" => "支付宝",
                "description" => "支付宝支付接口",
                "fields" => [
                    "app_id" => "应用ID",
                    "private_key" => "应用私钥",
                    "alipay_public_key" => "支付宝公钥",
                    "notify_url" => "异步通知地址",
                    "return_url" => "同步返回地址"
                ]
            ],
            "wechat" => [
                "name" => "微信支付",
                "description" => "微信支付接口",
                "fields" => [
                    "app_id" => "应用ID",
                    "mch_id" => "商户号",
                    "key" => "API密钥",
                    "cert_path" => "证书路径",
                    "key_path" => "密钥路径",
                    "notify_url" => "通知地址"
                ]
            ],
            "paypal" => [
                "name" => "PayPal",
                "description" => "PayPal支付接口",
                "fields" => [
                    "client_id" => "客户端ID",
                    "client_secret" => "客户端密钥",
                    "mode" => "模式(sandbox/live)",
                    "currency" => "货币代码",
                    "notify_url" => "通知地址",
                    "return_url" => "返回地址",
                    "cancel_url" => "取消地址"
                ]
            ],
            "stripe" => [
                "name" => "Stripe",
                "description" => "Stripe支付接口",
                "fields" => [
                    "api_key" => "API密钥",
                    "secret_key" => "密钥",
                    "webhook_secret" => "Webhook密钥",
                    "currency" => "货币代码"
                ]
            ],
            "unionpay" => [
                "name" => "银联支付",
                "description" => "银联支付接口",
                "fields" => [
                    "mch_id" => "商户号",
                    "cert_path" => "证书路径",
                    "cert_password" => "证书密码",
                    "notify_url" => "通知地址",
                    "return_url" => "返回地址"
                ]
            ]
        ];
    }

    /**
     * 获取支付网关配置字段
     *
     * @param string $gatewayCode 支付网关代码
     * @return array|null
     */
    public function getGatewayConfigFields($gatewayCode)
    {
        $supportedGateways = $this->getSupportedGateways();
        
        return $supportedGateways[$gatewayCode]["fields"] ?? null;
    }
}
