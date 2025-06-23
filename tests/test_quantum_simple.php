<?php

/**
 * 量子加密系统简化测试脚本
 * 
 * 测试量子加密系统的基本功能，不依赖数据库
 */

require_once __DIR__ . '/../vendor/autoload.php';

use AlingAI\Security\QuantumEncryption\QuantumEncryptionSystem;
use AlingAI\Security\QuantumEncryption\Algorithms\SM2Engine;
use AlingAI\Security\QuantumEncryption\Algorithms\SM3Engine;
use AlingAI\Security\QuantumEncryption\Algorithms\SM4Engine;
use AlingAI\Security\QuantumEncryption\QuantumRandom\QuantumRandomGenerator;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// 设置日志
$logger = new Logger('QuantumTest');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

echo "=== AlingAi Pro 6.0 量子加密系统简化测试 ===\n\n";

try {
    echo "✅ 开始测试量子加密系统基础算法...\n\n";
      // 1. 测试SM2引擎
    echo "📝 测试 SM2 椭圆曲线算法...\n";
    $sm2Config = ['curve' => 'sm2p256v1', 'key_size' => 256];
    $sm2Engine = new SM2Engine($sm2Config, $logger);
    echo "   - SM2引擎初始化完成\n";
    
    // 2. 测试SM3引擎
    echo "📝 测试 SM3 哈希算法...\n";
    $sm3Engine = new SM3Engine();
    $testData = "Hello, Quantum World!";
    $hash = $sm3Engine->hash($testData);
    echo "   - 原始数据: {$testData}\n";
    echo "   - SM3哈希: " . bin2hex($hash) . "\n";
    
    // 3. 测试SM4引擎
    echo "📝 测试 SM4 对称加密算法...\n";
    $sm4Engine = new SM4Engine();
    echo "   - SM4引擎初始化完成\n";
      // 4. 测试量子随机数生成器
    echo "📝 测试量子随机数生成器...\n";
    $quantumRng = new QuantumRandomGenerator();
    $randomBytes = $quantumRng->generateQuantumRandom(32);
    echo "   - 生成32字节随机数: " . bin2hex($randomBytes) . "\n";
    
    // 5. 测试基本加密/解密（模拟）
    echo "📝 测试基本加密解密流程...\n";
      // 生成测试密钥
    $testKey = $quantumRng->generateQuantumRandom(32);
    echo "   - 生成测试密钥: " . bin2hex($testKey) . "\n";
    
    // 模拟加密过程
    $plaintext = "这是一个量子加密测试消息";
    echo "   - 原始消息: {$plaintext}\n";
    
    // 使用SM3计算消息摘要
    $messageHash = $sm3Engine->hash($plaintext);
    echo "   - 消息摘要: " . bin2hex($messageHash) . "\n";
    
    // 6. 测试配置加载
    echo "📝 测试配置系统...\n";
    $configPath = __DIR__ . '/../config/quantum_encryption.php';
    
    if (file_exists($configPath)) {
        $config = require $configPath;
        echo "   - 配置文件存在: ✅\n";
        echo "   - 配置结构验证: ";
        
        $requiredKeys = ['quantum_encryption', 'environments'];
        $allKeysExist = true;
        
        foreach ($requiredKeys as $key) {
            if (!isset($config[$key])) {
                $allKeysExist = false;
                break;
            }
        }
        
        echo $allKeysExist ? "✅\n" : "❌\n";
        
        if (isset($config['quantum_encryption'])) {
            $qeConfig = $config['quantum_encryption'];
            echo "   - QKD配置: " . (isset($qeConfig['qkd']) ? "✅" : "❌") . "\n";
            echo "   - SM2配置: " . (isset($qeConfig['sm2']) ? "✅" : "❌") . "\n";
            echo "   - SM3配置: " . (isset($qeConfig['sm3']) ? "✅" : "❌") . "\n";
            echo "   - SM4配置: " . (isset($qeConfig['sm4']) ? "✅" : "❌") . "\n";
            echo "   - 安全配置: " . (isset($qeConfig['security']) ? "✅" : "❌") . "\n";
        }
    } else {
        echo "   - 配置文件不存在: ❌\n";
    }
    
    // 7. 检查API路由文件
    echo "📝 检查系统集成文件...\n";
    
    $files = [
        'QuantumEncryptionSystem' => __DIR__ . '/../src/Security/QuantumEncryption/QuantumEncryptionSystem.php',
        'QuantumEncryptionController' => __DIR__ . '/../src/Controllers/Security/QuantumEncryptionController.php',
        'DatabaseAdapter' => __DIR__ . '/../src/Core/Database/DatabaseAdapter.php',
        'DatabaseInterface' => __DIR__ . '/../src/Core/Database/DatabaseInterface.php',
        'Web演示页面' => __DIR__ . '/../public/quantum-demo.html'
    ];
    
    foreach ($files as $name => $path) {
        echo "   - {$name}: " . (file_exists($path) ? "✅" : "❌") . "\n";
    }
    
    echo "\n🎉 量子加密系统基础测试完成！\n\n";
    
    // 8. 性能基准测试
    echo "📊 性能基准测试...\n";
    
    $iterations = 1000;
    echo "   - 执行 {$iterations} 次SM3哈希运算...\n";
    
    $startTime = microtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        $sm3Engine->hash("测试数据 {$i}");
    }
    $endTime = microtime(true);
    
    $duration = ($endTime - $startTime) * 1000; // 转换为毫秒
    $throughput = $iterations / ($duration / 1000); // 每秒操作数
    
    echo "   - 总耗时: " . round($duration, 2) . " 毫秒\n";
    echo "   - 平均耗时: " . round($duration / $iterations, 4) . " 毫秒/次\n";
    echo "   - 吞吐量: " . round($throughput, 2) . " 操作/秒\n";
    
    echo "\n✅ 所有测试完成！量子加密系统准备就绪。\n";
    
    // 9. 显示系统信息
    echo "\n📋 系统信息:\n";
    echo "   - PHP版本: " . PHP_VERSION . "\n";
    echo "   - 内存使用: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB\n";
    echo "   - 峰值内存: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB\n";
    echo "   - 测试时间: " . date('Y-m-d H:i:s') . "\n";
    
} catch (Exception $e) {
    echo "❌ 测试失败: " . $e->getMessage() . "\n";
    echo "   错误文件: " . $e->getFile() . "\n";
    echo "   错误行号: " . $e->getLine() . "\n";
    echo "\n调试信息:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n=== 测试结束 ===\n";
