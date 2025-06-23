<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Utils/EnvLoader.php';

use AlingAi\Utils\EnvLoader;
EnvLoader::load(__DIR__ . '/.env');

$pdo = new PDO(
    "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']}",
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD']
);

echo "=== Users表结构 ===\n";
$stmt = $pdo->query('DESCRIBE users');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . $row['Key'] . "\n";
}

echo "\n=== 现有表列表 ===\n";
$stmt = $pdo->query('SHOW TABLES');
while($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo $row[0] . "\n";
}
