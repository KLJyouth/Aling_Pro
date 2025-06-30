<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ö§¸¶±¦ÅäÖÃ
    |--------------------------------------------------------------------------
    */
    "alipay" => [
        "app_id" => env("ALIPAY_APP_ID", ""),
        "private_key" => env("ALIPAY_PRIVATE_KEY", ""),
        "public_key" => env("ALIPAY_PUBLIC_KEY", ""),
        "gateway" => env("ALIPAY_GATEWAY", "https://openapi.alipay.com/gateway.do"),
        "notify_url" => env("ALIPAY_NOTIFY_URL", "/payment/notify/alipay"),
        "return_url" => env("ALIPAY_RETURN_URL", "/payment/return/alipay"),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Î¢ÐÅÖ§¸¶ÅäÖÃ
    |--------------------------------------------------------------------------
    */
    "wechat" => [
        "app_id" => env("WECHAT_APP_ID", ""),
        "mch_id" => env("WECHAT_MCH_ID", ""),
        "key" => env("WECHAT_KEY", ""),
        "cert_path" => env("WECHAT_CERT_PATH", ""),
        "key_path" => env("WECHAT_KEY_PATH", ""),
        "notify_url" => env("WECHAT_NOTIFY_URL", "/payment/notify/wechat"),
        "return_url" => env("WECHAT_RETURN_URL", "/payment/return/wechat"),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | ÒøÐÐ¿¨Ö§¸¶ÅäÖÃ
    |--------------------------------------------------------------------------
    */
    "card" => [
        "api_key" => env("CARD_API_KEY", ""),
        "api_secret" => env("CARD_API_SECRET", ""),
        "gateway" => env("CARD_GATEWAY", "https://payment.example.com/api"),
        "notify_url" => env("CARD_NOTIFY_URL", "/payment/notify/card"),
        "return_url" => env("CARD_RETURN_URL", "/payment/return/card"),
    ],
];
