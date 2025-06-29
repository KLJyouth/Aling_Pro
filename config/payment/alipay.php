<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 支付宝支付配置
    |--------------------------------------------------------------------------
    |
    | 此配置文件用于支付宝支付接口的相关设置
    |
    */

    // 应用ID,您的APPID
    'app_id' => env('ALIPAY_APP_ID', ''),

    // 商户私钥, 请把生成的私钥文件中字符串拷贝在此
    'app_secret_cert' => env('ALIPAY_APP_SECRET_CERT', ''),

    // 支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥
    'alipay_public_cert' => env('ALIPAY_PUBLIC_CERT', ''),

    // 支付宝根证书
    'alipay_root_cert' => env('ALIPAY_ROOT_CERT', ''),

    // 应用公钥证书
    'app_public_cert' => env('ALIPAY_APP_PUBLIC_CERT', ''),

    // 签名方式
    'sign_type' => env('ALIPAY_SIGN_TYPE', 'RSA2'),

    // 支付宝网关
    'gateway_url' => env('ALIPAY_GATEWAY_URL', 'https://openapi.alipay.com/gateway.do'),

    // 异步通知地址
    'notify_url' => env('APP_URL') . '/payment/alipay/notify',

    // 同步跳转
    'return_url' => env('APP_URL') . '/user/billing/orders',

    // 日志配置
    'log' => [
        'file' => storage_path('logs/alipay.log'),
        'level' => env('ALIPAY_LOG_LEVEL', 'info'),
        'type' => env('ALIPAY_LOG_TYPE', 'daily'),
        'max_file' => 30,
    ],

    // 可选，设置是否使用沙箱环境，默认为false
    'sandbox' => env('ALIPAY_SANDBOX', false),

    // 可选，设置 HTTP 请求超时时间，默认为 5 秒
    'http' => [
        'timeout' => 5.0,
        'connect_timeout' => 5.0,
    ],

    // 可选，设置代理请求，默认为 false
    'proxy' => [
        'http' => env('ALIPAY_HTTP_PROXY', null),
        'https' => env('ALIPAY_HTTPS_PROXY', null),
    ],
];
