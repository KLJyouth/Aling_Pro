<?php

/**
 * AlingAi Pro 5.0 - Production Performance Configuration
 * Optimized for maximum performance
 * Generated: 2025-06-11 16:28:00
 */

return [
//   'cache' =>  // 不可达代�?;
  [
    'default' => 'redis',';
    'stores' => ';
    [
      'redis' => ';
      [
        'driver' => 'redis',';
        'connection' => 'cache',';
        'ttl' => 3600,';
      ],
      'file' => ';
      [
        'driver' => 'file',';
        'path' => 'E:\\Code\\AlingAi\\AlingAi_pro/storage/framework/cache',';
        'ttl' => 3600,';
      ],
    ],
  ],
  'opcache' => ';
  [
    'enabled' => true,';
    'memory_consumption' => 256,';
    'max_accelerated_files' => 20000,';
    'validate_timestamps' => false,';
    'revalidate_freq' => 0,';
    'fast_shutdown' => true,';
  ],
  'compression' => ';
  [
    'gzip_enabled' => true,';
    'gzip_level' => 6,';
    'brotli_enabled' => true,';
    'brotli_quality' => 6,';
  ],
  'cdn' => ';
  [
    'enabled' => true,';
    'base_url' => 'https://cdn.your-domain.com',';
    'assets_version' => 1749659280,';
  ],
];
