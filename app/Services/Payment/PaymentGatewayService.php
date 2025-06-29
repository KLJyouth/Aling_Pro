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
     * ��ȡ����֧������
     *
     * @param bool $onlyActive �Ƿ�ֻ���ؼ��������
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
     * ��ȡ����֧������
     *
     * @param int|string $id ֧������ID�����
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
     * ����֧������
     *
     * @param array $data ֧����������
     * @return int �´�����֧������ID
     */
    public function createGateway(array $data)
    {
        // ȷ��������JSON��ʽ
        if (is_array($data["config"])) {
            $data["config"] = json_encode($data["config"]);
        }

        $data["created_at"] = now();
        $data["updated_at"] = now();

        return DB::table("payment_gateways")->insertGetId($data);
    }

    /**
     * ����֧������
     *
     * @param int $id ֧������ID
     * @param array $data ��������
     * @return bool
     */
    public function updateGateway($id, array $data)
    {
        // ȷ��������JSON��ʽ
        if (isset($data["config"]) && is_array($data["config"])) {
            $data["config"] = json_encode($data["config"]);
        }

        $data["updated_at"] = now();

        return DB::table("payment_gateways")
            ->where("id", $id)
            ->update($data);
    }

    /**
     * ɾ��֧�����أ���ɾ����
     *
     * @param int $id ֧������ID
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
     * ����/ͣ��֧������
     *
     * @param int $id ֧������ID
     * @param bool $active �Ƿ񼤻�
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
     * �л�����ģʽ
     *
     * @param int $id ֧������ID
     * @param bool $testMode �Ƿ�Ϊ����ģʽ
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
     * ����֧������
     *
     * @param array $data ��������
     * @return string ����ID
     */
    public function createTransaction(array $data)
    {
        // ����Ψһ����ID
        $data["transaction_id"] = $this->generateTransactionId();
        
        // ����Ĭ��״̬Ϊpending
        if (!isset($data["status"])) {
            $data["status"] = "pending";
        }
        
        // ��¼�ͻ���IP
        if (!isset($data["client_ip"])) {
            $data["client_ip"] = request()->ip();
        }
        
        $data["created_at"] = now();
        $data["updated_at"] = now();
        
        DB::table("payment_transactions")->insert($data);
        
        return $data["transaction_id"];
    }

    /**
     * ����Ψһ����ID
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
     * ���½���״̬
     *
     * @param string $transactionId ����ID
     * @param string $status ��״̬
     * @param array $additionalData ��������
     * @return bool
     */
    public function updateTransactionStatus($transactionId, $status, array $additionalData = [])
    {
        $updateData = array_merge($additionalData, [
            "status" => $status,
            "updated_at" => now()
        ]);
        
        // ���״̬Ϊcompleted������֧��ʱ��
        if ($status === "completed" && !isset($additionalData["paid_at"])) {
            $updateData["paid_at"] = now();
        }
        
        return DB::table("payment_transactions")
            ->where("transaction_id", $transactionId)
            ->update($updateData);
    }

    /**
     * ��ȡ��������
     *
     * @param string $transactionId ����ID
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
     * ��ȡ���������н���
     *
     * @param string $orderId ����ID
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
     * �����˿�
     *
     * @param array $data �˿�����
     * @return string �˿�ID
     */
    public function createRefund(array $data)
    {
        // ����Ψһ�˿�ID
        $data["refund_id"] = $this->generateRefundId();
        
        // ����Ĭ��״̬Ϊpending
        if (!isset($data["status"])) {
            $data["status"] = "pending";
        }
        
        $data["created_at"] = now();
        $data["updated_at"] = now();
        
        DB::table("payment_refunds")->insert($data);
        
        return $data["refund_id"];
    }

    /**
     * ����Ψһ�˿�ID
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
     * �����˿�״̬
     *
     * @param string $refundId �˿�ID
     * @param string $status ��״̬
     * @param array $additionalData ��������
     * @return bool
     */
    public function updateRefundStatus($refundId, $status, array $additionalData = [])
    {
        $updateData = array_merge($additionalData, [
            "status" => $status,
            "updated_at" => now()
        ]);
        
        // ���״̬Ϊcompleted�������˿�ʱ��
        if ($status === "completed" && !isset($additionalData["refunded_at"])) {
            $updateData["refunded_at"] = now();
        }
        
        return DB::table("payment_refunds")
            ->where("refund_id", $refundId)
            ->update($updateData);
    }

    /**
     * ��ȡ�˿�����
     *
     * @param string $refundId �˿�ID
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
     * ��ȡ���׵������˿�
     *
     * @param string $transactionId ����ID
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
     * ��¼֧��������־
     *
     * @param int $gatewayId ֧������ID
     * @param string $action ��������
     * @param array $request ��������
     * @param array $response ��Ӧ����
     * @param bool $isSuccess �Ƿ�ɹ�
     * @param string $errorMessage ������Ϣ
     * @param string|null $transactionId ����ID
     * @return int ��־ID
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
     * ��ȡ֧��������־
     *
     * @param int $gatewayId ֧������ID
     * @param int $limit ��������
     * @param int $offset ƫ����
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
     * ��ȡ������־
     *
     * @param string $transactionId ����ID
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
     * ��ȡ֧������
     *
     * @param string $key ���ü�
     * @param mixed $default Ĭ��ֵ
     * @return mixed
     */
    public function getSetting($key, $default = null)
    {
        $cacheKey = "payment_setting:{$key}";
        
        // ���Դӻ����ȡ
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // �����ݿ��ȡ
        $setting = DB::table("payment_settings")
            ->where("key", $key)
            ->first();
        
        $value = $setting ? $setting->value : $default;
        
        // ������
        Cache::put($cacheKey, $value, 3600); // ����1Сʱ
        
        return $value;
    }

    /**
     * ����֧������
     *
     * @param string $key ���ü�
     * @param mixed $value ����ֵ
     * @param string $group ����
     * @param string|null $description ����
     * @return bool
     */
    public function updateSetting($key, $value, $group = "general", $description = null)
    {
        // ��������Ƿ����
        $exists = DB::table("payment_settings")
            ->where("key", $key)
            ->exists();
        
        if ($exists) {
            // ��������
            $result = DB::table("payment_settings")
                ->where("key", $key)
                ->update([
                    "value" => $value,
                    "updated_at" => now()
                ]);
        } else {
            // ��������
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
        
        // ���»���
        Cache::put("payment_setting:{$key}", $value, 3600);
        
        return $result;
    }

    /**
     * ��ȡ��������
     *
     * @param string $group ��������
     * @return \Illuminate\Support\Collection
     */
    public function getSettingsByGroup($group)
    {
        return DB::table("payment_settings")
            ->where("group", $group)
            ->get();
    }

    /**
     * ��ȡ��������
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
     * ������û���
     *
     * @param string|null $key �ض����ü���Ϊnull���������
     * @return void
     */
    public function clearSettingCache($key = null)
    {
        if ($key) {
            Cache::forget("payment_setting:{$key}");
        } else {
            // ��ȡ�������ü�
            $keys = DB::table("payment_settings")
                ->pluck("key")
                ->toArray();
                
            foreach ($keys as $k) {
                Cache::forget("payment_setting:{$k}");
            }
        }
    }

    /**
     * ��齻���Ƿ����
     *
     * @param object $transaction ���׶���
     * @return bool
     */
    public function isTransactionExpired($transaction)
    {
        // �����������ɻ����˿�򲻻����
        if (in_array($transaction->status, ["completed", "refunded"])) {
            return false;
        }
        
        // ��ȡ֧������ʱ�䣨���ӣ�
        $expireTime = (int)$this->getSetting("payment_expire_time", 30);
        
        // �������ʱ��
        $expireAt = Carbon::parse($transaction->created_at)->addMinutes($expireTime);
        
        return $expireAt->isPast();
    }

    /**
     * ������ڽ���
     *
     * @return int ����Ľ�������
     */
    public function handleExpiredTransactions()
    {
        // ��ȡ֧������ʱ�䣨���ӣ�
        $expireTime = (int)$this->getSetting("payment_expire_time", 30);
        
        // �������ʱ���
        $expireTime = now()->subMinutes($expireTime);
        
        // �������й��ڵ�pending����
        $expiredTransactions = DB::table("payment_transactions")
            ->where("status", "pending")
            ->where("created_at", "<", $expireTime)
            ->whereNull("deleted_at")
            ->get();
        
        $count = 0;
        foreach ($expiredTransactions as $transaction) {
            // ���½���״̬Ϊʧ��
            $this->updateTransactionStatus($transaction->transaction_id, "failed", [
                "error_message" => "֧����ʱ"
            ]);
            
            // ��¼��־
            $this->logGatewayAction(
                $transaction->gateway_id,
                "transaction_expired",
                [],
                ["transaction_id" => $transaction->transaction_id],
                false,
                "֧����ʱ",
                $transaction->transaction_id
            );
            
            $count++;
        }
        
        return $count;
    }

    /**
     * ��ȡ֧�ֵ�֧�������б�
     *
     * @return array
     */
    public function getSupportedGateways()
    {
        return [
            "alipay" => [
                "name" => "֧����",
                "description" => "֧����֧���ӿ�",
                "fields" => [
                    "app_id" => "Ӧ��ID",
                    "private_key" => "Ӧ��˽Կ",
                    "alipay_public_key" => "֧������Կ",
                    "notify_url" => "�첽֪ͨ��ַ",
                    "return_url" => "ͬ�����ص�ַ"
                ]
            ],
            "wechat" => [
                "name" => "΢��֧��",
                "description" => "΢��֧���ӿ�",
                "fields" => [
                    "app_id" => "Ӧ��ID",
                    "mch_id" => "�̻���",
                    "key" => "API��Կ",
                    "cert_path" => "֤��·��",
                    "key_path" => "��Կ·��",
                    "notify_url" => "֪ͨ��ַ"
                ]
            ],
            "paypal" => [
                "name" => "PayPal",
                "description" => "PayPal֧���ӿ�",
                "fields" => [
                    "client_id" => "�ͻ���ID",
                    "client_secret" => "�ͻ�����Կ",
                    "mode" => "ģʽ(sandbox/live)",
                    "currency" => "���Ҵ���",
                    "notify_url" => "֪ͨ��ַ",
                    "return_url" => "���ص�ַ",
                    "cancel_url" => "ȡ����ַ"
                ]
            ],
            "stripe" => [
                "name" => "Stripe",
                "description" => "Stripe֧���ӿ�",
                "fields" => [
                    "api_key" => "API��Կ",
                    "secret_key" => "��Կ",
                    "webhook_secret" => "Webhook��Կ",
                    "currency" => "���Ҵ���"
                ]
            ],
            "unionpay" => [
                "name" => "����֧��",
                "description" => "����֧���ӿ�",
                "fields" => [
                    "mch_id" => "�̻���",
                    "cert_path" => "֤��·��",
                    "cert_password" => "֤������",
                    "notify_url" => "֪ͨ��ַ",
                    "return_url" => "���ص�ַ"
                ]
            ]
        ];
    }

    /**
     * ��ȡ֧�����������ֶ�
     *
     * @param string $gatewayCode ֧�����ش���
     * @return array|null
     */
    public function getGatewayConfigFields($gatewayCode)
    {
        $supportedGateways = $this->getSupportedGateways();
        
        return $supportedGateways[$gatewayCode]["fields"] ?? null;
    }
}
