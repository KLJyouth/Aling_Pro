<?php
return array (
  'default' => 'daily',
  'channels' => 
  array (
    'daily' => 
    array (
      'driver' => 'daily',
      'path' => 'E:\\Code\\AlingAi\\AlingAi_pro\\bin/../storage/logs/app.log',
      'level' => 'info',
      'days' => 14,
    ),
    'error' => 
    array (
      'driver' => 'single',
      'path' => 'E:\\Code\\AlingAi\\AlingAi_pro\\bin/../storage/logs/error.log',
      'level' => 'error',
    ),
    'performance' => 
    array (
      'driver' => 'single',
      'path' => 'E:\\Code\\AlingAi\\AlingAi_pro\\bin/../storage/logs/performance.log',
      'level' => 'info',
    ),
  ),
);
