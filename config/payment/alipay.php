<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ֧����֧������
    |--------------------------------------------------------------------------
    |
    | �������ļ�����֧����֧���ӿڵ��������
    |
    */

    // Ӧ��ID,����APPID
    'app_id' => env('ALIPAY_APP_ID', ''),

    // �̻�˽Կ, ������ɵ�˽Կ�ļ����ַ��������ڴ�
    'app_secret_cert' => env('ALIPAY_APP_SECRET_CERT', ''),

    // ֧������Կ,�鿴��ַ��https://openhome.alipay.com/platform/keyManage.htm ��ӦAPPID�µ�֧������Կ
    'alipay_public_cert' => env('ALIPAY_PUBLIC_CERT', ''),

    // ֧������֤��
    'alipay_root_cert' => env('ALIPAY_ROOT_CERT', ''),

    // Ӧ�ù�Կ֤��
    'app_public_cert' => env('ALIPAY_APP_PUBLIC_CERT', ''),

    // ǩ����ʽ
    'sign_type' => env('ALIPAY_SIGN_TYPE', 'RSA2'),

    // ֧��������
    'gateway_url' => env('ALIPAY_GATEWAY_URL', 'https://openapi.alipay.com/gateway.do'),

    // �첽֪ͨ��ַ
    'notify_url' => env('APP_URL') . '/payment/alipay/notify',

    // ͬ����ת
    'return_url' => env('APP_URL') . '/user/billing/orders',

    // ��־����
    'log' => [
        'file' => storage_path('logs/alipay.log'),
        'level' => env('ALIPAY_LOG_LEVEL', 'info'),
        'type' => env('ALIPAY_LOG_TYPE', 'daily'),
        'max_file' => 30,
    ],

    // ��ѡ�������Ƿ�ʹ��ɳ�价����Ĭ��Ϊfalse
    'sandbox' => env('ALIPAY_SANDBOX', false),

    // ��ѡ������ HTTP ����ʱʱ�䣬Ĭ��Ϊ 5 ��
    'http' => [
        'timeout' => 5.0,
        'connect_timeout' => 5.0,
    ],

    // ��ѡ�����ô�������Ĭ��Ϊ false
    'proxy' => [
        'http' => env('ALIPAY_HTTP_PROXY', null),
        'https' => env('ALIPAY_HTTPS_PROXY', null),
    ],
];
