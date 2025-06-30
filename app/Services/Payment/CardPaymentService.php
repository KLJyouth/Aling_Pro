<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Log;

class CardPaymentService implements PaymentServiceInterface
{
    /**
     * ���п�֧������
     *
     * @var array
     */
    protected $config;
    
    /**
     * �������п�֧������ʵ��
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
     * ����֧��
     *
     * @param  array  $data  ֧������
     * @return array ֧�����
     */
    public function createPayment(array $data): array
    {
        try {
            // ʵ����Ŀ�У�����Ӧ�õ������п�֧������API����֧��
            // Ϊ����ʾ�����Ƿ���ģ������
            
            $orderNo = $data["order_no"];
            $amount = $data["amount"];
            $subject = $data["subject"];
            
            Log::info("�������п�֧��", [
                "order_no" => $orderNo,
                "amount" => $amount,
                "subject" => $subject
            ]);
            
            // ģ��֧������
            $paymentUrl = "https://payment.example.com/card?order_no={$orderNo}&amount={$amount}&return_url=" . urlencode($this->config["return_url"]);
            
            return [
                "success" => true,
                "payment_id" => "card_" . $orderNo,
                "payment_url" => $paymentUrl,
                "message" => "֧�������ɹ�"
            ];
        } catch (\Exception $e) {
            Log::error("�������п�֧��ʧ��", [
                "error" => $e->getMessage(),
                "data" => $data
            ]);
            
            return [
                "success" => false,
                "message" => "����֧��ʧ�ܣ�" . $e->getMessage()
            ];
        }
    }
    
    /**
     * ��ѯ֧��״̬
     *
     * @param  string  $paymentId  ֧��ID
     * @return array ֧��״̬
     */
    public function queryPayment(string $paymentId): array
    {
        try {
            // ʵ����Ŀ�У�����Ӧ�õ������п�֧������API��ѯ֧��״̬
            // Ϊ����ʾ�����Ƿ���ģ������
            
            $orderNo = str_replace("card_", "", $paymentId);
            
            Log::info("��ѯ���п�֧��״̬", [
                "payment_id" => $paymentId,
                "order_no" => $orderNo
            ]);
            
            // ģ��֧��״̬
            $status = rand(0, 10) > 3 ? "PAID" : "PENDING";
            
            return [
                "success" => true,
                "payment_id" => $paymentId,
                "order_no" => $orderNo,
                "status" => $status,
                "paid_at" => $status === "PAID" ? date("Y-m-d H:i:s") : null,
                "message" => "��ѯ�ɹ�"
            ];
        } catch (\Exception $e) {
            Log::error("��ѯ���п�֧��״̬ʧ��", [
                "error" => $e->getMessage(),
                "payment_id" => $paymentId
            ]);
            
            return [
                "success" => false,
                "message" => "��ѯ֧��״̬ʧ�ܣ�" . $e->getMessage()
            ];
        }
    }
    
    /**
     * ȡ��֧��
     *
     * @param  string  $paymentId  ֧��ID
     * @return array ȡ�����
     */
    public function cancelPayment(string $paymentId): array
    {
        try {
            // ʵ����Ŀ�У�����Ӧ�õ������п�֧������APIȡ��֧��
            // Ϊ����ʾ�����Ƿ���ģ������
            
            $orderNo = str_replace("card_", "", $paymentId);
            
            Log::info("ȡ�����п�֧��", [
                "payment_id" => $paymentId,
                "order_no" => $orderNo
            ]);
            
            return [
                "success" => true,
                "payment_id" => $paymentId,
                "order_no" => $orderNo,
                "message" => "֧��ȡ���ɹ�"
            ];
        } catch (\Exception $e) {
            Log::error("ȡ�����п�֧��ʧ��", [
                "error" => $e->getMessage(),
                "payment_id" => $paymentId
            ]);
            
            return [
                "success" => false,
                "message" => "ȡ��֧��ʧ�ܣ�" . $e->getMessage()
            ];
        }
    }
    
    /**
     * �˿�
     *
     * @param  string  $paymentId  ֧��ID
     * @param  float  $amount  �˿���
     * @param  string  $reason  �˿�ԭ��
     * @return array �˿���
     */
    public function refund(string $paymentId, float $amount, string $reason = ""): array
    {
        try {
            // ʵ����Ŀ�У�����Ӧ�õ������п�֧������API�˿�
            // Ϊ����ʾ�����Ƿ���ģ������
            
            $orderNo = str_replace("card_", "", $paymentId);
            
            Log::info("���п�֧���˿�", [
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
                "message" => "�˿�����ɹ�"
            ];
        } catch (\Exception $e) {
            Log::error("���п�֧���˿�ʧ��", [
                "error" => $e->getMessage(),
                "payment_id" => $paymentId,
                "amount" => $amount
            ]);
            
            return [
                "success" => false,
                "message" => "�˿�ʧ�ܣ�" . $e->getMessage()
            ];
        }
    }
    
    /**
     * ��֤֧��֪ͨ
     *
     * @param  array  $data  ֪ͨ����
     * @return array ��֤���
     */
    public function verifyNotify(array $data): array
    {
        try {
            // ʵ����Ŀ�У�����Ӧ�õ������п�֧������API��֤֪ͨ
            // Ϊ����ʾ�����Ƿ���ģ������
            
            Log::info("��֤���п�֧��֪ͨ", [
                "data" => $data
            ]);
            
            // ������֤�ɹ�
            return [
                "success" => true,
                "payment_id" => "card_" . ($data["order_no"] ?? ""),
                "order_no" => $data["order_no"] ?? "",
                "transaction_id" => $data["transaction_id"] ?? "",
                "amount" => $data["amount"] ?? 0,
                "status" => $data["status"] ?? "",
                "paid_at" => date("Y-m-d H:i:s"),
                "message" => "֪ͨ��֤�ɹ�"
            ];
        } catch (\Exception $e) {
            Log::error("��֤���п�֧��֪ͨʧ��", [
                "error" => $e->getMessage(),
                "data" => $data
            ]);
            
            return [
                "success" => false,
                "message" => "֪ͨ��֤ʧ�ܣ�" . $e->getMessage()
            ];
        }
    }
}
