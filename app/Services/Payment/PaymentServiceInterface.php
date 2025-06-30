<?php

namespace App\Services\Payment;

interface PaymentServiceInterface
{
    /**
     * 创建支付
     *
     * @param  array  $data  支付数据
     * @return array 支付结果
     */
    public function createPayment(array $data): array;
    
    /**
     * 查询支付状态
     *
     * @param  string  $paymentId  支付ID
     * @return array 支付状态
     */
    public function queryPayment(string $paymentId): array;
    
    /**
     * 取消支付
     *
     * @param  string  $paymentId  支付ID
     * @return array 取消结果
     */
    public function cancelPayment(string $paymentId): array;
    
    /**
     * 退款
     *
     * @param  string  $paymentId  支付ID
     * @param  float  $amount  退款金额
     * @param  string  $reason  退款原因
     * @return array 退款结果
     */
    public function refund(string $paymentId, float $amount, string $reason = ""): array;
    
    /**
     * 验证支付通知
     *
     * @param  array  $data  通知数据
     * @return array 验证结果
     */
    public function verifyNotify(array $data): array;
}
