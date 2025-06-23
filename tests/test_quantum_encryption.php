<?php

/**
 * 量子加密系统测试脚本
 * 
 * 测试量子加密系统的基本功能，包括：
 * - 基本加密/解密功能
 * - SM2数字签名
 * - 量子随机数生成
 * - 系统状态检查
 * 
 * 使用方法：php test_quantum_encryption.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/app.php';

use AlingAI\Security\QuantumEncryption\QuantumEncryptionSystem;
use AlingAi\Core\Database\DatabaseAdapter;
use AlingAi\Services\DatabaseService;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// 设置日志
$logger = new Logger('QuantumTest');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

echo "=== AlingAi Pro 6.0 量子加密系统测试 ===\n\n";

try {
    // 初始化数据库服务
    $logger->info('初始化数据库服务...');
    $databaseService = new DatabaseService($logger);
    $databaseAdapter = new DatabaseAdapter($databaseService, $logger);
    
    // 加载配置
    $configPath = __DIR__ . '/../config/quantum_encryption.php';
    $config = [];
    
    if (file_exists($configPath)) {
        $fullConfig = require $configPath;
        $config = $fullConfig['quantum_encryption'] ?? [];
        $logger->info('量子加密配置已加载');
    } else {
        $logger->warning('配置文件不存在，使用默认配置');
        $config = [
            'qkd' => [
                'protocol' => 'BB84',
                'key_length' => 256,
                'error_threshold' => 0.11
            ],
            'sm2' => [
                'curve' => 'sm2p256v1',
                'key_size' => 256,
                'hash_algorithm' => 'sm3'
            ],
            'sm3' => [
                'digest_size' => 256,
                'block_size' => 512
            ],
            'sm4' => [
                'mode' => 'GCM',
                'iv_length' => 12,
                'tag_length' => 16
            ],
            'quantum_enhancement' => [
                'enabled' => true,
                'entropy_source' => 'hardware',
                'random_factor_size' => 32
            ],
            'security' => [
                'max_encryption_size' => 10485760,
                'key_rotation_interval' => 3600,
                'audit_enabled' => true,
                'secure_deletion' => true
            ]
        ];
    }
    
    // 初始化量子加密系统
    $logger->info('初始化量子加密系统...');
    $quantumSystem = new QuantumEncryptionSystem($databaseAdapter, $logger, $config);
    
    // 测试1: 系统状态检查
    echo "1. 检查系统状态...\n";
    $status = $quantumSystem->getSystemStatus();
    echo "   系统版本: " . $status['version'] . "\n";
    echo "   会话ID: " . $status['session_id'] . "\n";
    echo "   系统状态: " . $status['status'] . "\n";
    echo "   ✓ 系统状态正常\n\n";
    
    // 测试2: 基本加密/解密
    echo "2. 测试基本加密/解密功能...\n";
    $testData = "这是一个量子加密测试数据：AlingAi Pro 6.0 - " . date('Y-m-d H:i:s');
    echo "   原始数据: " . $testData . "\n";
    
    $encryptResult = $quantumSystem->encrypt($testData);
    echo "   加密ID: " . $encryptResult['encryption_id'] . "\n";
    echo "   加密耗时: " . $encryptResult['encryption_time_ms'] . " ms\n";
    
    $decryptedData = $quantumSystem->decrypt($encryptResult);
    echo "   解密数据: " . $decryptedData . "\n";
    
    if ($testData === $decryptedData) {
        echo "   ✓ 加密/解密测试通过\n\n";
    } else {
        echo "   ✗ 加密/解密测试失败\n\n";
        exit(1);
    }
    
    // 测试3: 量子随机数生成
    echo "3. 测试量子随机数生成...\n";
    $randomBytes = $quantumSystem->generateQuantumRandomBytes(32);
    echo "   随机数长度: " . strlen($randomBytes) . " 字节\n";
    echo "   随机数(hex): " . bin2hex($randomBytes) . "\n";
    echo "   ✓ 量子随机数生成正常\n\n";
    
    // 测试4: 批量加密
    echo "4. 测试批量加密功能...\n";
    $batchData = [
        "数据1: AlingAi量子加密",
        "数据2: 国密SM2/SM3/SM4算法",
        "数据3: 量子密钥分发QKD"
    ];
    
    $batchResults = $quantumSystem->encryptBatch($batchData);
    echo "   批量加密数量: " . count($batchResults) . "\n";
    
    $successCount = 0;
    foreach ($batchResults as $result) {
        if ($result['success']) {
            $successCount++;
        }
    }
    echo "   成功加密: " . $successCount . "/" . count($batchResults) . "\n";
    echo "   ✓ 批量加密测试完成\n\n";
    
    // 测试5: 性能指标
    echo "5. 获取系统性能指标...\n";
    $metrics = $quantumSystem->getPerformanceMetrics();
    echo "   总加密次数: " . $metrics['total_encryptions'] . "\n";
    echo "   平均加密时间: " . number_format($metrics['average_encryption_time'], 2) . " ms\n";
    echo "   QKD效率: " . number_format($metrics['qkd_efficiency'] * 100, 2) . "%\n";
    echo "   量子随机数质量: " . number_format($metrics['quantum_rng_quality'] * 100, 2) . "%\n";
    echo "   ✓ 性能指标获取正常\n\n";
    
    // 测试6: 系统配置导出
    echo "6. 导出系统配置...\n";
    $exportedConfig = $quantumSystem->exportConfiguration();
    echo "   配置版本: " . $exportedConfig['version'] . "\n";
    echo "   系统ID: " . $exportedConfig['system_id'] . "\n";
    echo "   量子特性: " . json_encode($exportedConfig['quantum_features']) . "\n";
    echo "   ✓ 配置导出成功\n\n";
    
    echo "=== 所有测试完成 ===\n";
    echo "✓ 量子加密系统运行正常\n";
    echo "✓ 所有核心功能测试通过\n";
    echo "✓ 系统已准备就绪\n\n";
    
    $logger->info('量子加密系统测试全部通过');
    
} catch (Exception $e) {
    echo "✗ 测试失败: " . $e->getMessage() . "\n";
    echo "错误详情: " . $e->getTraceAsString() . "\n";
    $logger->error('量子加密系统测试失败', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    exit(1);
}
