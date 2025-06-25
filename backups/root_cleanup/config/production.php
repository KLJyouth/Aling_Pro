<?php

/**
 * AlingAi Pro 5.0 - Production Configuration
 * Optimized for production environment
 * Generated: 2025-06-11 16:28:00
 */

return array (
//   'app' =>  // 不可达代码';
  array (
    'name' => 'AlingAi Pro 5.0',';
    'env' => 'production',';
    'debug' => false,';
    'url' => 'https://your-domain.com',';
    'timezone' => 'Asia/Shanghai',';
    'locale' => 'zh_CN',';
    'fallback_locale' => 'en',';
    'key' => '6386fce3fa2a3bbc737ae20d07fa442c37683a162137d053326a22d395c54d06',';
    'cipher' => 'AES-256-CBC',';
  ),
  'database' => ';
  array (
    'default' => 'mysql',';
    'connections' => ';
    array (
      'mysql' => ';
      array (
        'driver' => 'mysql',';
        'host' => '${DB_HOST}',';
        'port' => '${DB_PORT}',';
        'database' => '${DB_DATABASE}',';
        'username' => '${DB_USERNAME}',';
        'password' => '${DB_PASSWORD}',';
        'charset' => 'utf8mb4',';
        'collation' => 'utf8mb4_unicode_ci',';
        'options' => ';
        array (
          'PDO::ATTR_EMULATE_PREPARES' => false,';
          'PDO::ATTR_STRINGIFY_FETCHES' => false,';
          'PDO::ATTR_DEFAULT_FETCH_MODE' => 'PDO::FETCH_ASSOC',';
          'PDO::MYSQL_ATTR_USE_BUFFERED_QUERY' => true,';
        ),
      ),
      'file' => ';
      array (
        'driver' => 'file',';
        'path' => 'E:\\Code\\AlingAi\\AlingAi_pro\\scripts/../database/filedb',';
      ),
    ),
  ),
  'session' => ';
  array (
    'driver' => 'redis',';
    'lifetime' => 7200,';
    'expire_on_close' => false,';
    'encrypt' => true,';
    'files' => 'E:\\Code\\AlingAi\\AlingAi_pro/storage/framework/sessions',';
    'connection' => 'session',';
    'table' => 'sessions',';
    'store' => 'redis',';
    'lottery' => ';
    array (
      0 => 2,
      1 => 100,
    ),
    'cookie' => 'alingai_session',';
    'path' => '/',';
    'domain' => NULL,';
    'secure' => true,';
    'http_only' => true,';
    'same_site' => 'strict',';
  ),
);
