<?php

// 加载vendor
require_once __DIR__ . '/../vendor/autoload.php';

// 直接引用测试
require_once __DIR__ . '/../src/Security/QuantumEncryption/Algorithms/SM3Engine.php';

use AlingAI\Security\QuantumEncryption\Algorithms\SM3Engine;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

echo "=== 简单SM3测试 ===\n";

$logger = new Logger('Test');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

try {
    $sm3 = new SM3Engine([], $logger);
    $result = $sm3->hash("Hello World");
    echo "测试成功！哈希结果: " . bin2hex($result) . "\n";
    echo "哈希长度: " . (strlen($result) * 8) . "位\n";
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
