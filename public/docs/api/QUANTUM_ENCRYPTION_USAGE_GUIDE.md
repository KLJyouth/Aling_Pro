# AlingAi Pro 6.0 量子加密系统使用指南

## 🚀 快速开始

### 基本用法示例

```php
<?php
require_once 'vendor/autoload.php';

use AlingAI\Security\QuantumEncryption\QuantumEncryptionSystem;
use AlingAI\Security\QuantumEncryption\QuantumEncryptionInterface;

// 创建量子加密系统实例
$quantumSystem = new QuantumEncryptionSystem([
    'qkd_protocol' => 'BB84',
    'key_length' => 256,
    'encryption_algorithm' => 'SM4',
    'hash_algorithm' => 'SM3',
    'signature_algorithm' => 'SM2'
]);

// 加密数据
$data = "机密数据：用户账户信息";
$encrypted = $quantumSystem->encrypt($data);

// 解密数据
$decrypted = $quantumSystem->decrypt($encrypted);

echo "原始数据: $data\n";
echo "解密数据: $decrypted\n";
echo "验证结果: " . ($data === $decrypted ? "成功" : "失败") . "\n";
```

## 📋 系统配置

### 基础配置

```php
$config = [
    // QKD配置
    'qkd' => [
        'protocol' => 'BB84',
        'key_length' => 256,
        'error_threshold' => 0.11,
        'quantum_channel' => [
            'error_rate' => 0.05,
            'loss_rate' => 0.02
        ],
        'classical_channel' => [
            'error_rate' => 0.001,
            'authenticated' => true
        ]
    ],
    
    // 算法配置
    'algorithms' => [
        'symmetric' => 'SM4',
        'asymmetric' => 'SM2',
        'hash' => 'SM3',
        'signature' => 'SM2'
    ],
    
    // 量子随机数配置
    'quantum_rng' => [
        'entropy_sources' => ['quantum_vacuum', 'shot_noise', 'thermal_noise'],
        'post_processing' => ['von_neumann', 'toeplitz_hash'],
        'quality_tests' => ['nist_sp800_22']
    ]
];
```

### 高级配置

```php
$advancedConfig = [
    // 性能配置
    'performance' => [
        'cache_enabled' => true,
        'parallel_processing' => true,
        'memory_limit' => '256M'
    ],
    
    // 安全配置
    'security' => [
        'key_rotation_interval' => 3600, // 1小时
        'audit_logging' => true,
        'secure_memory' => true
    ],
    
    // 监控配置
    'monitoring' => [
        'health_check_interval' => 300, // 5分钟
        'performance_metrics' => true,
        'error_reporting' => true
    ]
];
```

## 🔐 加密API使用

### 1. 基础加密/解密

```php
// 使用接口
$encryptor = new QuantumEncryptionInterface();

// 简单加密
$plaintext = "敏感数据";
$ciphertext = $encryptor->encrypt($plaintext);
$decrypted = $encryptor->decrypt($ciphertext);
```

### 2. 批量数据处理

```php
// 批量加密
$dataArray = [
    "用户1的数据",
    "用户2的数据", 
    "用户3的数据"
];

$encryptedArray = [];
foreach ($dataArray as $data) {
    $encryptedArray[] = $encryptor->encrypt($data);
}

// 批量解密
$decryptedArray = [];
foreach ($encryptedArray as $encrypted) {
    $decryptedArray[] = $encryptor->decrypt($encrypted);
}
```

### 3. 文件加密

```php
// 文件加密
function encryptFile($inputFile, $outputFile, $encryptor) {
    $data = file_get_contents($inputFile);
    $encrypted = $encryptor->encrypt($data);
    file_put_contents($outputFile, $encrypted);
}

// 文件解密
function decryptFile($inputFile, $outputFile, $encryptor) {
    $encrypted = file_get_contents($inputFile);
    $decrypted = $encryptor->decrypt($encrypted);
    file_put_contents($outputFile, $decrypted);
}

// 使用示例
encryptFile('secret.txt', 'secret.encrypted', $encryptor);
decryptFile('secret.encrypted', 'secret_decrypted.txt', $encryptor);
```

## 🔑 密钥管理

### 1. QKD密钥生成

```php
use AlingAI\Security\QuantumEncryption\QKD\QuantumKeyDistribution;

$qkd = new QuantumKeyDistribution($config['qkd'], $logger);

// 生成量子密钥
$keyResult = $qkd->generateQuantumKey(256, 'BB84');

echo "会话ID: " . $keyResult['session_id'] . "\n";
echo "密钥长度: " . $keyResult['key_length'] . "位\n";
echo "错误率: " . ($keyResult['error_rate'] * 100) . "%\n";
echo "安全性: " . ($keyResult['secure'] ? "安全" : "不安全") . "\n";

$symmetricKey = $keyResult['symmetric_key'];
```

### 2. SM2密钥对管理

```php
use AlingAI\Security\QuantumEncryption\Algorithms\SM2Engine;

$sm2 = new SM2Engine([], $logger);

// 生成密钥对
$keyPair = $sm2->generateKeyPair();
$publicKey = $keyPair['public_key'];
$privateKey = $keyPair['private_key'];

// 保存密钥（注意安全存储）
file_put_contents('public_key.pem', $publicKey);
// 私钥应该加密存储
file_put_contents('private_key.pem', $privateKey);
```

### 3. 密钥轮换

```php
class KeyManager {
    private $currentKeys = [];
    private $keyHistory = [];
    
    public function rotateKeys() {
        // 备份当前密钥
        $this->keyHistory[] = $this->currentKeys;
        
        // 生成新密钥
        $qkd = new QuantumKeyDistribution($this->config, $this->logger);
        $newKey = $qkd->generateQuantumKey(256, 'BB84');
        
        $this->currentKeys = [
            'symmetric' => $newKey['symmetric_key'],
            'timestamp' => time(),
            'session_id' => $newKey['session_id']
        ];
        
        return $this->currentKeys;
    }
    
    public function getCurrentKey() {
        return $this->currentKeys['symmetric'];
    }
}
```

## 🔍 数据完整性验证

### 1. 哈希验证

```php
use AlingAI\Security\QuantumEncryption\Algorithms\SM3Engine;

$sm3 = new SM3Engine([], $logger);

// 计算数据哈希
$data = "重要数据";
$originalHash = $sm3->hash($data);

// 验证数据完整性
function verifyIntegrity($data, $expectedHash, $sm3) {
    $currentHash = $sm3->hash($data);
    return hash_equals($expectedHash, $currentHash);
}

$isValid = verifyIntegrity($data, $originalHash, $sm3);
echo "数据完整性: " . ($isValid ? "有效" : "已损坏") . "\n";
```

### 2. 数字签名

```php
// 创建数字签名
$signature = $sm2->sign($data, $privateKey);

// 验证数字签名
$isValidSignature = $sm2->verify($data, $signature, $publicKey);

echo "签名验证: " . ($isValidSignature ? "有效" : "无效") . "\n";
```

## 📊 监控和日志

### 1. 性能监控

```php
class QuantumSystemMonitor {
    private $metrics = [];
    
    public function recordOperation($operation, $duration, $success) {
        $this->metrics[] = [
            'operation' => $operation,
            'duration' => $duration,
            'success' => $success,
            'timestamp' => microtime(true)
        ];
    }
    
    public function getPerformanceReport() {
        $totalOps = count($this->metrics);
        $successfulOps = array_filter($this->metrics, fn($m) => $m['success']);
        $successRate = (count($successfulOps) / $totalOps) * 100;
        
        $avgDuration = array_sum(array_column($this->metrics, 'duration')) / $totalOps;
        
        return [
            'total_operations' => $totalOps,
            'success_rate' => $successRate,
            'average_duration' => $avgDuration,
            'uptime' => time() - $this->startTime
        ];
    }
}
```

### 2. 安全审计

```php
class SecurityAuditor {
    private $auditLog = [];
    
    public function logSecurityEvent($event, $severity, $details) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'severity' => $severity,
            'details' => $details,
            'source_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $this->auditLog[] = $logEntry;
        
        // 写入安全日志文件
        $logLine = json_encode($logEntry) . "\n";
        file_put_contents('security_audit.log', $logLine, FILE_APPEND | LOCK_EX);
    }
    
    public function checkSecurityThreats() {
        // 检查可疑活动
        $recentEvents = array_filter($this->auditLog, function($event) {
            return (time() - strtotime($event['timestamp'])) < 3600; // 最近1小时
        });
        
        $highSeverityEvents = array_filter($recentEvents, function($event) {
            return $event['severity'] === 'high';
        });
        
        if (count($highSeverityEvents) > 5) {
            $this->triggerSecurityAlert();
        }
    }
}
```

## 🔧 故障排除

### 常见问题解决

#### 1. 密钥生成失败
```php
try {
    $keyResult = $qkd->generateQuantumKey(256, 'BB84');
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'quantum channel') !== false) {
        // 量子信道问题
        echo "量子信道连接失败，请检查量子设备\n";
    } elseif (strpos($e->getMessage(), 'error rate') !== false) {
        // 错误率过高
        echo "量子信道错误率过高，请优化环境条件\n";
    }
}
```

#### 2. 加密失败处理
```php
try {
    $encrypted = $encryptor->encrypt($data);
} catch (InvalidArgumentException $e) {
    echo "输入数据格式错误: " . $e->getMessage() . "\n";
} catch (RuntimeException $e) {
    echo "加密系统运行时错误: " . $e->getMessage() . "\n";
}
```

#### 3. 性能优化
```php
// 启用缓存
$encryptor->enableCache(true);

// 批量处理
$encryptor->setBatchSize(100);

// 并行处理
$encryptor->enableParallelProcessing(true);
```

## 🚀 生产环境部署

### 1. 环境要求
- PHP 8.1+
- OpenSSL扩展
- GMP扩展 (大数运算)
- 足够的内存 (推荐2GB+)

### 2. 安全配置
```php
// 生产环境配置
$productionConfig = [
    'security' => [
        'ssl_verify_peer' => true,
        'secure_key_storage' => true,
        'memory_encryption' => true,
        'audit_level' => 'strict'
    ],
    'performance' => [
        'cache_size' => '512M',
        'worker_processes' => 4,
        'connection_pool_size' => 100
    ]
];
```

### 3. 监控集成
```php
// 集成监控系统
$monitor = new ProductionMonitor([
    'prometheus_endpoint' => 'http://monitoring:9090',
    'alert_webhook' => 'https://alerts.company.com/webhook',
    'log_level' => 'info'
]);

$quantumSystem->setMonitor($monitor);
```

## 📞 技术支持

### 联系方式
- **技术文档**: 查看项目文档目录
- **问题报告**: 通过项目问题追踪系统
- **技术咨询**: 联系AlingAi Pro技术团队

### 最佳实践
1. **定期密钥轮换** - 建议每小时轮换一次
2. **监控系统状态** - 实时监控量子信道质量
3. **备份恢复策略** - 制定完整的数据备份计划
4. **安全审计** - 定期进行安全评估
5. **性能调优** - 根据业务需求优化参数

---

**版本**: 6.0.0  
**更新时间**: 2025年6月12日  
**维护团队**: AlingAi Pro 量子安全团队
