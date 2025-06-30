<?php

namespace App\Services\Payment;

interface PaymentServiceInterface
{
    /**
     * ����֧��
     *
     * @param  array  $data  ֧������
     * @return array ֧�����
     */
    public function createPayment(array $data): array;
    
    /**
     * ��ѯ֧��״̬
     *
     * @param  string  $paymentId  ֧��ID
     * @return array ֧��״̬
     */
    public function queryPayment(string $paymentId): array;
    
    /**
     * ȡ��֧��
     *
     * @param  string  $paymentId  ֧��ID
     * @return array ȡ�����
     */
    public function cancelPayment(string $paymentId): array;
    
    /**
     * �˿�
     *
     * @param  string  $paymentId  ֧��ID
     * @param  float  $amount  �˿���
     * @param  string  $reason  �˿�ԭ��
     * @return array �˿���
     */
    public function refund(string $paymentId, float $amount, string $reason = ""): array;
    
    /**
     * ��֤֧��֪ͨ
     *
     * @param  array  $data  ֪ͨ����
     * @return array ��֤���
     */
    public function verifyNotify(array $data): array;
}
