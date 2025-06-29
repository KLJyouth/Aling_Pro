<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 微信支付配置
    |--------------------------------------------------------------------------
    |
    | 此配置文件用于微信支付接口的相关设置
    |
    */

    // 商户号
    'mch_id' => env('WECHAT_PAY_MCH_ID', ''),

    // 商户API证书序列号
    'mch_serial_no' => env('WECHAT_PAY_SERIAL_NO', ''),

    // 商户私钥
    'mch_secret_key' => env('WECHAT_PAY_SECRET_KEY', ''),

    // API V3密钥
    'mch_secret_v3' => env('WECHAT_PAY_SECRET_V3', ''),

    // 微信公钥
    'wechat_public_cert_path' => env('WECHAT_PAY_PUBLIC_CERT_PATH', ''),

    // 平台证书路径
    'platform_cert_path' => env('WECHAT_PAY_PLATFORM_CERT_PATH', ''),

    // 应用ID
    'app_id' => env('WECHAT_PAY_APP_ID', ''),

    // 小程序ID
    'mini_app_id' => env('WECHAT_PAY_MINI_APP_ID', ''),

    // 公众号ID
    'mp_app_id' => env('WECHAT_PAY_MP_APP_ID', ''),

    // APP ID
    'mobile_app_id' => env('WECHAT_PAY_MOBILE_APP_ID', ''),

    // 异步通知地址
    'notify_url' => env('APP_URL') . '/payment/wechat/notify',

    // 微信支付日志
    'log' => [
        'file' => storage_path('logs/wechat_pay.log'),
        'level' => env('WECHAT_PAY_LOG_LEVEL', 'info'),
        'type' => env('WECHAT_PAY_LOG_TYPE', 'daily'),
        'max_file' => 30,
    ],

    // 可选，设置 HTTP 请求超时时间，默认为 5 秒
    'http' => [
        'timeout' => 5.0,
        'connect_timeout' => 5.0,
    ],

    // 可选，设置代理请求，默认为 false
    'proxy' => [
        'http' => env('WECHAT_PAY_HTTP_PROXY', null),
        'https' => env('WECHAT_PAY_HTTPS_PROXY', null),
    ],
];
