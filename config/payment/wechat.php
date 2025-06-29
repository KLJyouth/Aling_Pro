<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ΢��֧������
    |--------------------------------------------------------------------------
    |
    | �������ļ�����΢��֧���ӿڵ��������
    |
    */

    // �̻���
    'mch_id' => env('WECHAT_PAY_MCH_ID', ''),

    // �̻�API֤�����к�
    'mch_serial_no' => env('WECHAT_PAY_SERIAL_NO', ''),

    // �̻�˽Կ
    'mch_secret_key' => env('WECHAT_PAY_SECRET_KEY', ''),

    // API V3��Կ
    'mch_secret_v3' => env('WECHAT_PAY_SECRET_V3', ''),

    // ΢�Ź�Կ
    'wechat_public_cert_path' => env('WECHAT_PAY_PUBLIC_CERT_PATH', ''),

    // ƽ̨֤��·��
    'platform_cert_path' => env('WECHAT_PAY_PLATFORM_CERT_PATH', ''),

    // Ӧ��ID
    'app_id' => env('WECHAT_PAY_APP_ID', ''),

    // С����ID
    'mini_app_id' => env('WECHAT_PAY_MINI_APP_ID', ''),

    // ���ں�ID
    'mp_app_id' => env('WECHAT_PAY_MP_APP_ID', ''),

    // APP ID
    'mobile_app_id' => env('WECHAT_PAY_MOBILE_APP_ID', ''),

    // �첽֪ͨ��ַ
    'notify_url' => env('APP_URL') . '/payment/wechat/notify',

    // ΢��֧����־
    'log' => [
        'file' => storage_path('logs/wechat_pay.log'),
        'level' => env('WECHAT_PAY_LOG_LEVEL', 'info'),
        'type' => env('WECHAT_PAY_LOG_TYPE', 'daily'),
        'max_file' => 30,
    ],

    // ��ѡ������ HTTP ����ʱʱ�䣬Ĭ��Ϊ 5 ��
    'http' => [
        'timeout' => 5.0,
        'connect_timeout' => 5.0,
    ],

    // ��ѡ�����ô�������Ĭ��Ϊ false
    'proxy' => [
        'http' => env('WECHAT_PAY_HTTP_PROXY', null),
        'https' => env('WECHAT_PAY_HTTPS_PROXY', null),
    ],
];
