<?php

namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Http\Request;

abstract class PaymentService
{
    /**
     * 创建支付订单
     *
     * @param Order $order 订单模型
     * @return array 返回支付所需的数据
     */
    abstract public function createPayment(Order $order): array;

    /**
     * 处理支付回调
     *
     * @param Request $request 请求对象
     * @return array 返回处理结果
     */
    abstract public function handleCallback(Request $request): array;

    /**
     * 查询支付状态
     *
     * @param Order $order 订单模型
     * @return array 返回查询结果
     */
    abstract public function queryPayment(Order $order): array;

    /**
     * 退款处理
     *
     * @param Order $order 订单模型
     * @param float $amount 退款金额
     * @param string $reason 退款原因
     * @return array 返回退款结果
     */
    abstract public function refund(Order $order, float $amount, string $reason = ""): array;

    /**
     * 验证签名
     *
     * @param array $data 待验证的数据
     * @param string $sign 签名
     * @return bool 验证结果
     */
    abstract public function verifySign(array $data, string $sign): bool;

    /**
     * 生成签名
     *
     * @param array $data 待签名的数据
     * @return string 签名结果
     */
    abstract public function generateSign(array $data): string;

    /**
     * 格式化金额为分
     *
     * @param float $amount 元为单位的金额
     * @return int 分为单位的金额
     */
    protected function formatAmount(float $amount): int
    {
        return (int)bcmul($amount, 100, 0);
    }

    /**
     * 将分转换为元
     *
     * @param int $amount 分为单位的金额
     * @return float 元为单位的金额
     */
    protected function convertToYuan(int $amount): float
    {
        return round($amount / 100, 2);
    }

    /**
     * 生成随机字符串
     *
     * @param int $length 字符串长度
     * @return string 随机字符串
     */
    protected function generateRandomString(int $length = 32): string
    {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $charactersLength = strlen($characters);
        $randomString = "";
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * 记录日志
     *
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    protected function log(string $message, array $context = []): void
    {
        \Log::channel("payment")->info($message, $context);
    }
}
