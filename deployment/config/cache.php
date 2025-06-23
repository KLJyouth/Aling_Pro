<?php
/**
 * 高性能缓存配置
 */
return [
    "default" => "file",
    "stores" => [
        "file" => [
            "driver" => "file",
            "path" => __DIR__ . "/../storage/cache/data"
        ],
        "memory" => [
            "driver" => "array"
        ]
    ],
    "ttl" => [
        "default" => 3600,
        "api" => 300,
        "user_data" => 1800,
        "system_settings" => 86400
    ]
];