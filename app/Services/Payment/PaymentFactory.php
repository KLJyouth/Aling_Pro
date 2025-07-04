<?php

namespace App\Services\Payment;

use InvalidArgumentException;

class PaymentFactory
{
    /**
     * 创建支付服务实例
     *
     * @param  string  $driver  支付驱动
     * @return \App\Services\Payment\PaymentServiceInterface
     *
     * @throws \InvalidArgumentException
     */
    public static function create(string $driver): PaymentServiceInterface
    {
        switch ($driver) {
            case "alipay":
                return new AlipayService();
            case "wechat":
                return new WechatPayService();
            case "card":
                return new CardPaymentService();
            default:
                throw new InvalidArgumentException("不支持的支付驱动: {$driver}");
        }
    }
}
