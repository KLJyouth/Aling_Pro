<?php return array (
  'app' => 
  array (
    'app' => 
    array (
      'name' => 'AlingAi Pro',
      'version' => '2.0.0',
      'env' => 'production',
      'debug' => false,
      'url' => 'http://localhost',
      'timezone' => 'Asia/Shanghai',
      'locale' => 'zh_CN',
      'fallback_locale' => 'en_US',
      'key' => 'base64:dr2OuvNpghMU+P2/vsL2i6anrAjL0Hb/kvbnJ59cEbg=',
    ),
    'database' => 
    array (
      'default' => 'mysql',
      'connections' => 
      array (
        'mysql' => 
        array (
          'driver' => 'mysql',
          'host' => '127.0.0.1',
          'port' => 3306,
          'database' => 'alingai_pro',
          'username' => 'root',
          'password' => '',
          'charset' => 'utf8mb4',
          'collation' => 'utf8mb4_unicode_ci',
          'prefix' => '',
          'strict' => true,
          'engine' => 'InnoDB',
          'options' => 
          array (
            2 => 30,
            3 => 2,
            19 => 2,
            1002 => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
          ),
        ),
        'sqlite' => 
        array (
          'driver' => 'sqlite',
          'database' => 'E:\\Code\\AlingAi\\AlingAi_pro\\config/../storage/database.sqlite',
          'prefix' => '',
          'foreign_key_constraints' => true,
          'options' => 
          array (
            2 => 30,
            3 => 2,
            19 => 2,
          ),
        ),
      ),
    ),
    'cache' => 
    array (
      'default' => 'redis',
      'stores' => 
      array (
        'redis' => 
        array (
          'driver' => 'redis',
          'host' => '127.0.0.1',
          'port' => 6379,
          'password' => NULL,
          'database' => 0,
          'prefix' => 'alingai_pro:',
        ),
        'file' => 
        array (
          'driver' => 'file',
          'path' => '/storage/framework/cache',
        ),
      ),
    ),
    'session' => 
    array (
      'driver' => 'redis',
      'lifetime' => 7200,
      'expire_on_close' => false,
      'encrypt' => true,
      'files' => '/storage/framework/sessions',
      'connection' => NULL,
      'table' => 'sessions',
      'store' => NULL,
      'lottery' => 
      array (
        0 => 2,
        1 => 100,
      ),
      'cookie' => 'alingai_session',
      'path' => '/',
      'domain' => false,
      'secure' => false,
      'http_only' => true,
      'same_site' => 'lax',
    ),
    'jwt' => 
    array (
      'secret' => 'your-jwt-secret-key',
      'algorithm' => 'HS256',
      'ttl' => 3600,
      'refresh_ttl' => 604800,
      'leeway' => 60,
      'issuer' => 'alingai-pro',
      'audience' => 'alingai-pro-users',
    ),
    'mail' => 
    array (
      'driver' => 'smtp',
      'host' => 'smtp.gmail.com',
      'port' => 587,
      'username' => false,
      'password' => false,
      'encryption' => 'tls',
      'from' => 
      array (
        'address' => 'noreply@alingai.com',
        'name' => 'AlingAi Pro',
      ),
      'reply_to' => 
      array (
        'address' => 'support@alingai.com',
        'name' => 'AlingAi Support',
      ),
    ),
    'filesystems' => 
    array (
      'default' => 'local',
      'disks' => 
      array (
        'local' => 
        array (
          'driver' => 'local',
          'root' => '/storage/app',
        ),
        'public' => 
        array (
          'driver' => 'local',
          'root' => '/storage/app/public',
          'url' => '/storage',
          'visibility' => 'public',
        ),
        'uploads' => 
        array (
          'driver' => 'local',
          'root' => '/public/uploads',
          'url' => '/uploads',
          'visibility' => 'public',
        ),
      ),
    ),
    'logging' => 
    array (
      'default' => 'stack',
      'channels' => 
      array (
        'stack' => 
        array (
          'driver' => 'stack',
          'channels' => 
          array (
            0 => 'daily',
          ),
        ),
        'daily' => 
        array (
          'driver' => 'daily',
          'path' => '/storage/logs/alingai.log',
          'level' => 'info',
          'days' => 14,
        ),
        'syslog' => 
        array (
          'driver' => 'syslog',
          'level' => 'debug',
        ),
      ),
    ),
    'security' => 
    array (
      'csrf_protection' => true,
      'xss_protection' => true,
      'content_type_nosniff' => true,
      'frame_deny' => true,
      'https_redirect' => false,
      'hsts_max_age' => 31536000,
      'rate_limiting' => 
      array (
        'enabled' => true,
        'requests_per_minute' => 60,
        'requests_per_hour' => 1000,
      ),
    ),
    'upload' => 
    array (
      'max_file_size' => 10485760,
      'allowed_types' => 
      array (
        'image' => 
        array (
          0 => 'jpg',
          1 => 'jpeg',
          2 => 'png',
          3 => 'gif',
          4 => 'webp',
        ),
        'document' => 
        array (
          0 => 'pdf',
          1 => 'doc',
          2 => 'docx',
          3 => 'txt',
          4 => 'md',
        ),
        'archive' => 
        array (
          0 => 'zip',
          1 => 'rar',
          2 => '7z',
        ),
      ),
      'image_max_width' => 2048,
      'image_max_height' => 2048,
      'thumbnail_size' => 300,
    ),
    'api' => 
    array (
      'version' => 'v1',
      'rate_limit' => 
      array (
        'requests_per_minute' => 100,
        'requests_per_hour' => 2000,
      ),
      'pagination' => 
      array (
        'default_limit' => 20,
        'max_limit' => 100,
      ),
    ),
    'services' => 
    array (
      'openai' => 
      array (
        'api_key' => false,
        'api_url' => 'https://api.openai.com/v1',
        'model' => 'gpt-3.5-turbo',
        'max_tokens' => 2048,
        'temperature' => 0.7,
      ),
      'google' => 
      array (
        'analytics_id' => false,
        'recaptcha_site_key' => false,
        'recaptcha_secret_key' => false,
      ),
    ),
    'features' => 
    array (
      'registration' => false,
      'email_verification' => false,
      'password_reset' => false,
      'admin_panel' => false,
      'chat' => false,
      'documents' => false,
    ),
  ),
  'database' => false,
);