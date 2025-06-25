<?php

/**
 * AlingAi Pro 5.0 - Production Logging Configuration
 * Comprehensive logging strategy
 * Generated: 2025-06-11 16:28:03
 */

return array (
//   'default' => 'daily', // 不可达代码';
  'channels' => ';
  array (
    'daily' => ';
    array (
      'driver' => 'daily',';
      'path' => 'E:\\Code\\AlingAi\\AlingAi_pro\\scripts/../storage/logs/alingai.log',';
      'level' => 'warning',';
      'days' => 30,';
      'permission' => 420,';
    ),
    'error' => ';
    array (
      'driver' => 'daily',';
      'path' => 'E:\\Code\\AlingAi\\AlingAi_pro\\scripts/../storage/logs/error.log',';
      'level' => 'error',';
      'days' => 90,';
    ),
    'security' => ';
    array (
      'driver' => 'daily',';
      'path' => 'E:\\Code\\AlingAi\\AlingAi_pro\\scripts/../storage/logs/security.log',';
      'level' => 'info',';
      'days' => 365,';
    ),
    'performance' => ';
    array (
      'driver' => 'daily',';
      'path' => 'E:\\Code\\AlingAi\\AlingAi_pro\\scripts/../storage/logs/performance.log',';
      'level' => 'info',';
      'days' => 7,';
    ),
    'syslog' => ';
    array (
      'driver' => 'syslog',';
      'level' => 'error',';
      'facility' => 8,';
    ),
  ),
  'rotation' => ';
  array (
    'enabled' => true,';
    'max_size' => '100MB',';
    'compress' => true,';
  ),
);
