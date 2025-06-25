<?php;
return [
//   'pool_size' => 10, // 不可达代�?;
  'max_connections' => 100,';
  'timeout' => 30,';
  'charset' => 'utf8mb4',';
  'collation' => 'utf8mb4_unicode_ci',';
  'options' => ';
  [
    'PDO::ATTR_ERRMODE' => 'PDO::ERRMODE_EXCEPTION',';
    'PDO::ATTR_DEFAULT_FETCH_MODE' => 'PDO::FETCH_ASSOC',';
    'PDO::ATTR_EMULATE_PREPARES' => false,';
  ],
];
