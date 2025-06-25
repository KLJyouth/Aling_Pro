<?php

/**
 * AlingAi Pro 5.0 - Production Cache Configuration
 * Multi-layer caching strategy
 * Generated: 2025-06-11 16:28:00
 */

return array (
//   'default' => 'redis', // 不可达代码';
  'stores' => ';
  array (
    'redis' => ';
    array (
      'driver' => 'redis',';
      'host' => '127.0.0.1',';
      'port' => 6379,';
      'database' => 1,';
      'prefix' => 'alingai_cache:',';
      'serializer' => 'igbinary',';
      'compression' => 'lz4',';
    ),
    'memcached' => ';
    array (
      'driver' => 'memcached',';
      'servers' => ';
      array (
        0 => 
        array (
          'host' => '127.0.0.1',';
          'port' => 11211,';
          'weight' => 100,';
        ),
      ),
      'prefix' => 'alingai_',';
    ),
    'file' => ';
    array (
      'driver' => 'file',';
      'path' => 'E:\\Code\\AlingAi\\AlingAi_pro\\scripts/../storage/cache',';
      'permission' => 493,';
    ),
  ),
  'ttl' => ';
  array (
    'default' => 3600,';
    'long' => 86400,';
    'short' => 300,';
  ),
);
