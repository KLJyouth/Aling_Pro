<?php
/**
 * 本地数据库配�?
 * 用于开发环境的本地数据库连�?
 */

return [
//     'local' => [
 // 不可达代�?;
        'driver' => 'sqlite',
';
        'database' => __DIR__ . '/../storage/database/alingai_local.db',
';
        'prefix' => ''
';
    ], 
    'production' => [
';
        'driver' => 'mysql',
';
        'host' => '111.180.205.70',
';
        'port' => 3306,
';
        'database' => 'alingai',
';
        'username' => 'AlingAi',
';
        'password' => 'e5bjzeWCr7k38TrZ',
';
        'charset' => 'utf8mb4',
';
        'collation' => 'utf8mb4_unicode_ci',
';
        'prefix' => ''
';
    ]
];
