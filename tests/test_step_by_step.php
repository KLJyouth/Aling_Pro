<?php

echo "开始测试...\n";

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "autoload加载成功\n";
    
    require_once __DIR__ . '/../src/Security/QuantumEncryption/Algorithms/SM3Engine.php';
    echo "SM3Engine文件加载成功\n";
    
    echo "创建Logger...\n";
    $logger = new Monolog\Logger('Test');
    echo "Logger创建成功\n";
    
    $logger->pushHandler(new Monolog\Handler\StreamHandler('php://stdout', Monolog\Logger::INFO));
    echo "Handler添加成功\n";
    
    echo "创建SM3Engine...\n";
    $sm3 = new AlingAI\Security\QuantumEncryption\Algorithms\SM3Engine([], $logger);
    echo "SM3Engine创建成功\n";
    
    echo "执行哈希计算...\n";
    $result = $sm3->hash("Hello");
    echo "哈希计算成功: " . bin2hex($result) . "\n";
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
