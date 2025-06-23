# 🔧 AlingAi Pro 6.0 - 开发者集成指南

## 📖 概述

本指南面向希望集成或扩展AlingAi Pro 6.0零信任量子加密系统的开发者。提供详细的API集成、SDK使用和自定义开发指导。

---

## 🚀 快速开始

### 环境准备

#### 系统要求
```bash
# 服务器环境
PHP >= 8.1
MySQL >= 8.0 或 MariaDB >= 10.6
Redis >= 6.0
Composer >= 2.0

# 开发工具
Git
Docker (可选)
Node.js >= 16 (前端开发)
```

#### 安装步骤
```bash
# 1. 克隆项目
git clone https://github.com/alingai/alingai-pro-6.0.git
cd alingai-pro-6.0

# 2. 安装PHP依赖
composer install

# 3. 环境配置
cp .env.example .env
# 编辑.env文件配置数据库连接

# 4. 数据库初始化
php database/migrate.php

# 5. 启动开发服务器
php -S localhost:8000 -t public/

# 6. 验证安装
curl http://localhost:8000/api/health
```

---

## 🔐 加密SDK集成

### 基础加密操作

#### 1. 初始化加密引擎
```php
use AlingAi\Security\QuantumEncryption\QuantumCryptoFactory;

// 创建SM4加密引擎
$sm4 = QuantumCryptoFactory::createEngine('sm4');

// 创建SM2签名引擎
$sm2 = QuantumCryptoFactory::createEngine('sm2');

// 创建SM3哈希引擎
$sm3 = QuantumCryptoFactory::createEngine('sm3');
```

#### 2. 数据加密解密
```php
// SM4对称加密
$key = $sm4->generateKey(); // 生成32字节密钥
$iv = random_bytes(16);     // 生成初始化向量

// CBC模式加密
$encrypted = $sm4->encrypt($data, $key, [
    'mode' => 'cbc',
    'iv' => $iv,
    'padding' => 'pkcs7'
]);

// 解密
$decrypted = $sm4->decrypt($encrypted, $key, [
    'mode' => 'cbc',
    'iv' => $iv,
    'padding' => 'pkcs7'
]);

// GCM模式（推荐用于API）
$encrypted = $sm4->encrypt($data, $key, [
    'mode' => 'gcm',
    'iv' => $iv,
    'aad' => 'additional_authenticated_data'
]);
```

#### 3. 数字签名
```php
// 生成SM2密钥对
$keyPair = $sm2->generateKeyPair();
$privateKey = $keyPair['private'];
$publicKey = $keyPair['public'];

// 数字签名
$signature = $sm2->sign($data, $privateKey);

// 验证签名
$isValid = $sm2->verify($data, $signature, $publicKey);
```

#### 4. 哈希计算
```php
// SM3哈希
$hash = $sm3->hash($data);

// HMAC消息认证码
$hmac = $sm3->hash($data, [
    'hmac' => true,
    'key' => $secretKey
]);
```

### 高级加密功能

#### 1. 大文件加密
```php
class LargeFileEncryption
{
    private $sm4;
    
    public function __construct()
    {
        $this->sm4 = QuantumCryptoFactory::createEngine('sm4');
    }
    
    public function encryptFile(string $inputFile, string $outputFile, string $key): bool
    {
        $input = fopen($inputFile, 'rb');
        $output = fopen($outputFile, 'wb');
        
        if (!$input || !$output) {
            return false;
        }
        
        $iv = random_bytes(16);
        fwrite($output, $iv); // 写入IV到文件头
        
        $chunkSize = 8192; // 8KB chunks
        while (!feof($input)) {
            $chunk = fread($input, $chunkSize);
            $encrypted = $this->sm4->encrypt($chunk, $key, [
                'mode' => 'cbc',
                'iv' => $iv,
                'padding' => feof($input) ? 'pkcs7' : 'none'
            ]);
            fwrite($output, $encrypted);
        }
        
        fclose($input);
        fclose($output);
        return true;
    }
}
```

#### 2. 密钥派生和管理
```php
class KeyManagement
{
    public function deriveKey(string $password, string $salt): string
    {
        // 使用PBKDF2派生密钥
        return hash_pbkdf2('sha256', $password, $salt, 10000, 32, true);
    }
    
    public function generateSalt(): string
    {
        return random_bytes(32);
    }
    
    public function secureKeyStorage(string $key, string $keyId): void
    {
        // 将密钥存储到安全位置（如硬件安全模块）
        $encryptedKey = $this->encryptKeyForStorage($key);
        $this->storeEncryptedKey($keyId, $encryptedKey);
    }
}
```

---

## 🌐 API集成指南

### 认证和授权

#### 1. 获取访问令牌
```http
POST /api/auth/token
Content-Type: application/json

{
    "grant_type": "client_credentials",
    "client_id": "your_client_id",
    "client_secret": "your_client_secret",
    "scope": "api:read api:write"
}
```

```php
// PHP示例
$client = new GuzzleHttp\Client();
$response = $client->post('https://api.alingai.com/auth/token', [
    'json' => [
        'grant_type' => 'client_credentials',
        'client_id' => 'your_client_id',
        'client_secret' => 'your_client_secret',
        'scope' => 'api:read api:write'
    ]
]);

$tokenData = json_decode($response->getBody(), true);
$accessToken = $tokenData['access_token'];
```

#### 2. 使用令牌访问API
```php
// 设置认证头
$headers = [
    'Authorization' => 'Bearer ' . $accessToken,
    'Content-Type' => 'application/json',
    'X-API-Version' => 'v1'
];

// 发起API请求
$response = $client->get('https://api.alingai.com/users', [
    'headers' => $headers
]);
```

### 核心API接口

#### 1. 用户管理API
```php
// 获取用户列表
GET /api/users?page=1&limit=20&search=admin

// 创建用户
POST /api/users
{
    "username": "newuser",
    "email": "user@example.com",
    "password": "encrypted_password",
    "role": "user"
}

// 更新用户
PUT /api/users/{id}
{
    "email": "newemail@example.com",
    "role": "admin"
}

// 删除用户
DELETE /api/users/{id}
```

#### 2. 加密服务API
```php
// 数据加密
POST /api/crypto/encrypt
{
    "algorithm": "sm4",
    "mode": "gcm",
    "data": "base64_encoded_data",
    "options": {
        "key_id": "encryption_key_id"
    }
}

// 数据解密
POST /api/crypto/decrypt
{
    "algorithm": "sm4",
    "mode": "gcm",
    "encrypted_data": "base64_encoded_encrypted_data",
    "iv": "base64_encoded_iv",
    "tag": "base64_encoded_tag",
    "options": {
        "key_id": "encryption_key_id"
    }
}
```

#### 3. 系统监控API
```php
// 获取系统指标
GET /api/monitoring/metrics?timeframe=1h

// 响应示例
{
    "success": true,
    "data": {
        "cpu_usage": 25.5,
        "memory_usage": 68.2,
        "disk_usage": 45.0,
        "network_io": {
            "bytes_sent": 1024000,
            "bytes_received": 2048000
        },
        "database": {
            "connections": 15,
            "queries_per_second": 125
        }
    },
    "timestamp": "2025-06-14T10:30:00Z"
}
```

### 错误处理

#### 标准错误响应格式
```json
{
    "success": false,
    "error": {
        "code": "INVALID_CREDENTIALS",
        "message": "提供的认证信息无效",
        "details": {
            "field": "password",
            "reason": "密码格式不正确"
        }
    },
    "request_id": "req_123456789",
    "timestamp": "2025-06-14T10:30:00Z"
}
```

#### 错误代码说明
```php
// 认证错误
const AUTH_INVALID_CREDENTIALS = 'AUTH_001';
const AUTH_TOKEN_EXPIRED = 'AUTH_002';
const AUTH_INSUFFICIENT_PERMISSIONS = 'AUTH_003';

// 加密错误
const CRYPTO_INVALID_KEY = 'CRYPTO_001';
const CRYPTO_DECRYPTION_FAILED = 'CRYPTO_002';
const CRYPTO_UNSUPPORTED_ALGORITHM = 'CRYPTO_003';

// 系统错误
const SYSTEM_DATABASE_ERROR = 'SYS_001';
const SYSTEM_CACHE_ERROR = 'SYS_002';
const SYSTEM_INTERNAL_ERROR = 'SYS_003';
```

---

## 🛠️ 自定义开发

### 扩展加密算法

#### 1. 实现自定义加密引擎
```php
use AlingAi\Security\Interfaces\QuantumCryptoInterface;
use AlingAi\Security\Exceptions\CryptoException;

class CustomCryptoEngine implements QuantumCryptoInterface
{
    private $logger;
    
    public function __construct($logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }
    
    public function encrypt(string $data, string $key, array $options = []): string
    {
        try {
            // 验证密钥长度
            if (strlen($key) !== 32) {
                throw new InvalidKeyException('密钥长度必须为32字节');
            }
            
            // 实现自定义加密逻辑
            $encrypted = $this->performCustomEncryption($data, $key, $options);
            
            $this->logger->info('数据加密成功', [
                'data_length' => strlen($data),
                'algorithm' => 'custom'
            ]);
            
            return base64_encode($encrypted);
            
        } catch (\Exception $e) {
            $this->logger->error('加密失败', ['error' => $e->getMessage()]);
            throw new CryptoException('加密操作失败: ' . $e->getMessage());
        }
    }
    
    public function decrypt(string $encryptedData, string $key, array $options = []): string
    {
        try {
            $encrypted = base64_decode($encryptedData);
            $decrypted = $this->performCustomDecryption($encrypted, $key, $options);
            
            $this->logger->info('数据解密成功');
            return $decrypted;
            
        } catch (\Exception $e) {
            $this->logger->error('解密失败', ['error' => $e->getMessage()]);
            throw new CryptoException('解密操作失败: ' . $e->getMessage());
        }
    }
    
    public function generateKey(int $length = 32): string
    {
        return random_bytes($length);
    }
    
    // 实现其他接口方法...
    
    private function performCustomEncryption(string $data, string $key, array $options): string
    {
        // 自定义加密算法实现
        // 这里可以集成第三方加密库或实现自定义算法
        return $data; // 示例返回
    }
}
```

#### 2. 注册自定义引擎
```php
// 在应用启动时注册
QuantumCryptoFactory::registerEngine('custom', CustomCryptoEngine::class);

// 使用自定义引擎
$customEngine = QuantumCryptoFactory::createEngine('custom');
$encrypted = $customEngine->encrypt($data, $key);
```

### 扩展中间件

#### 1. 创建自定义安全中间件
```php
use AlingAi\Http\Middleware\BaseMiddleware;

class CustomSecurityMiddleware extends BaseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. 自定义安全检查
        $this->performCustomSecurityChecks($request);
        
        // 2. IP白名单检查
        if (!$this->isIPAllowed($request->getClientIp())) {
            return $this->errorResponse('IP地址不在允许列表中', 403);
        }
        
        // 3. 地理位置检查
        if (!$this->isLocationAllowed($request)) {
            return $this->errorResponse('访问地理位置受限', 403);
        }
        
        // 4. 设备指纹验证
        if (!$this->verifyDeviceFingerprint($request)) {
            return $this->errorResponse('设备验证失败', 403);
        }
        
        // 继续请求处理
        $response = $next($request);
        
        // 5. 响应后处理
        $this->logSecurityEvent($request, $response);
        
        return $response;
    }
    
    private function performCustomSecurityChecks(Request $request): void
    {
        // 实现自定义安全检查逻辑
        $userAgent = $request->header('User-Agent');
        $acceptLanguage = $request->header('Accept-Language');
        
        // 检查可疑的User-Agent
        if ($this->isSuspiciousUserAgent($userAgent)) {
            throw new SecurityException('检测到可疑的User-Agent');
        }
        
        // 检查请求频率
        if ($this->isRequestRateTooHigh($request)) {
            throw new SecurityException('请求频率过高');
        }
    }
    
    private function isIPAllowed(string $ip): bool
    {
        // 实现IP白名单检查
        $allowedIPs = config('security.allowed_ips', []);
        return in_array($ip, $allowedIPs) || $this->isIPInRange($ip, $allowedIPs);
    }
}
```

#### 2. 注册中间件
```php
// 在路由中使用
$app->addMiddleware(new CustomSecurityMiddleware());

// 或在特定路由组中使用
$app->group('/api/sensitive', function (Group $group) {
    $group->get('/data', DataController::class . ':getData');
})->add(new CustomSecurityMiddleware());
```

### 扩展监控功能

#### 1. 自定义监控指标
```php
use AlingAi\Monitoring\MetricCollector;

class CustomMetricCollector implements MetricCollector
{
    public function collect(): array
    {
        return [
            'custom_business_metric' => $this->getBusinessMetric(),
            'custom_security_score' => $this->calculateSecurityScore(),
            'custom_performance_index' => $this->getPerformanceIndex()
        ];
    }
    
    private function getBusinessMetric(): float
    {
        // 实现业务指标收集
        // 例如：活跃用户数、交易量等
        return 123.45;
    }
    
    private function calculateSecurityScore(): int
    {
        // 计算安全评分
        $factors = [
            'failed_login_attempts' => $this->getFailedLoginAttempts(),
            'suspicious_activities' => $this->getSuspiciousActivities(),
            'security_events' => $this->getSecurityEvents()
        ];
        
        return $this->computeSecurityScore($factors);
    }
}
```

#### 2. 注册自定义监控
```php
// 在监控服务中注册
$monitoringService = app(MonitoringService::class);
$monitoringService->addCollector(new CustomMetricCollector());
```

---

## 🧪 测试和调试

### 单元测试

#### 1. 加密功能测试
```php
use PHPUnit\Framework\TestCase;
use AlingAi\Security\QuantumEncryption\QuantumCryptoFactory;

class CustomCryptoEngineTest extends TestCase
{
    private $engine;
    
    protected function setUp(): void
    {
        $this->engine = QuantumCryptoFactory::createEngine('custom');
    }
    
    public function testEncryptionDecryption(): void
    {
        $data = 'Hello, AlingAi Pro 6.0!';
        $key = $this->engine->generateKey();
        
        $encrypted = $this->engine->encrypt($data, $key);
        $decrypted = $this->engine->decrypt($encrypted, $key);
        
        $this->assertEquals($data, $decrypted);
    }
    
    public function testInvalidKeyLength(): void
    {
        $this->expectException(InvalidKeyException::class);
        
        $data = 'test data';
        $shortKey = 'short'; // 无效的短密钥
        
        $this->engine->encrypt($data, $shortKey);
    }
    
    public function testLargeDataEncryption(): void
    {
        $largeData = str_repeat('A', 1024 * 1024); // 1MB数据
        $key = $this->engine->generateKey();
        
        $startTime = microtime(true);
        $encrypted = $this->engine->encrypt($largeData, $key);
        $encryptTime = microtime(true) - $startTime;
        
        $startTime = microtime(true);
        $decrypted = $this->engine->decrypt($encrypted, $key);
        $decryptTime = microtime(true) - $startTime;
        
        $this->assertEquals($largeData, $decrypted);
        $this->assertLessThan(1.0, $encryptTime); // 加密时间小于1秒
        $this->assertLessThan(1.0, $decryptTime); // 解密时间小于1秒
    }
}
```

#### 2. API测试
```php
class APIIntegrationTest extends TestCase
{
    private $client;
    private $baseUrl = 'http://localhost:8000';
    
    protected function setUp(): void
    {
        $this->client = new GuzzleHttp\Client();
    }
    
    public function testAPIAuthentication(): void
    {
        $response = $this->client->post($this->baseUrl . '/api/auth/token', [
            'json' => [
                'grant_type' => 'client_credentials',
                'client_id' => 'test_client',
                'client_secret' => 'test_secret'
            ]
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('token_type', $data);
        $this->assertEquals('Bearer', $data['token_type']);
    }
    
    public function testEncryptionAPI(): void
    {
        // 先获取访问令牌
        $token = $this->getAccessToken();
        
        $response = $this->client->post($this->baseUrl . '/api/crypto/encrypt', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'algorithm' => 'sm4',
                'mode' => 'gcm',
                'data' => base64_encode('test data')
            ]
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('encrypted_data', $data);
        $this->assertArrayHasKey('iv', $data);
        $this->assertArrayHasKey('tag', $data);
    }
}
```

### 性能测试

#### 1. 加密性能基准测试
```php
class PerformanceBenchmark
{
    public function benchmarkEncryption(): void
    {
        $engine = QuantumCryptoFactory::createEngine('sm4');
        $key = $engine->generateKey();
        
        $dataSizes = [1024, 10240, 102400, 1048576]; // 1KB, 10KB, 100KB, 1MB
        
        foreach ($dataSizes as $size) {
            $data = random_bytes($size);
            
            // 加密性能测试
            $startTime = microtime(true);
            for ($i = 0; $i < 100; $i++) {
                $encrypted = $engine->encrypt($data, $key);
            }
            $encryptTime = (microtime(true) - $startTime) / 100;
            
            // 解密性能测试
            $startTime = microtime(true);
            for ($i = 0; $i < 100; $i++) {
                $decrypted = $engine->decrypt($encrypted, $key);
            }
            $decryptTime = (microtime(true) - $startTime) / 100;
            
            printf("数据大小: %d字节, 加密时间: %.4f秒, 解密时间: %.4f秒\n", 
                   $size, $encryptTime, $decryptTime);
        }
    }
}
```

### 调试工具

#### 1. 日志记录
```php
use Psr\Log\LoggerInterface;

class DebugLogger
{
    private LoggerInterface $logger;
    
    public function logCryptoOperation(string $operation, array $context): void
    {
        $this->logger->debug('加密操作', [
            'operation' => $operation,
            'timestamp' => microtime(true),
            'memory_usage' => memory_get_usage(true),
            'context' => $context
        ]);
    }
    
    public function logAPIRequest(Request $request, Response $response): void
    {
        $this->logger->info('API请求', [
            'method' => $request->getMethod(),
            'uri' => $request->getUri(),
            'status' => $response->getStatusCode(),
            'response_time' => $this->getResponseTime(),
            'user_agent' => $request->header('User-Agent'),
            'ip' => $request->getClientIp()
        ]);
    }
}
```

#### 2. 性能分析
```php
class PerformanceProfiler
{
    private array $timers = [];
    private array $memoryUsage = [];
    
    public function startTimer(string $name): void
    {
        $this->timers[$name] = microtime(true);
        $this->memoryUsage[$name]['start'] = memory_get_usage(true);
    }
    
    public function endTimer(string $name): float
    {
        if (!isset($this->timers[$name])) {
            throw new \InvalidArgumentException("Timer '$name' not found");
        }
        
        $duration = microtime(true) - $this->timers[$name];
        $this->memoryUsage[$name]['end'] = memory_get_usage(true);
        $this->memoryUsage[$name]['peak'] = memory_get_peak_usage(true);
        
        unset($this->timers[$name]);
        
        return $duration;
    }
    
    public function getReport(): array
    {
        return [
            'memory_usage' => $this->memoryUsage,
            'active_timers' => array_keys($this->timers)
        ];
    }
}
```

---

## 📚 最佳实践

### 安全最佳实践

#### 1. 密钥管理
```php
// ✅ 正确的密钥管理
class SecureKeyManager
{
    private $keyStore;
    
    public function storeKey(string $keyId, string $key): void
    {
        // 加密存储密钥
        $encryptedKey = $this->encryptKeyForStorage($key);
        $this->keyStore->store($keyId, $encryptedKey);
        
        // 清理内存中的明文密钥
        sodium_memzero($key);
    }
    
    public function rotateKey(string $keyId): string
    {
        $newKey = random_bytes(32);
        $this->storeKey($keyId, $newKey);
        
        // 记录密钥轮换日志
        $this->auditLogger->logKeyRotation($keyId);
        
        return $newKey;
    }
}

// ❌ 错误的密钥管理
class InsecureKeyManager
{
    public function storeKey(string $keyId, string $key): void
    {
        // 直接存储明文密钥（不安全）
        file_put_contents("/tmp/{$keyId}.key", $key);
    }
}
```

#### 2. 错误处理
```php
// ✅ 安全的错误处理
try {
    $decrypted = $cryptoEngine->decrypt($encryptedData, $key);
} catch (AuthenticationFailedException $e) {
    // 不暴露具体的加密错误信息
    $this->logger->warning('解密验证失败', ['user_id' => $userId]);
    throw new APIException('数据验证失败', 400);
} catch (CryptoException $e) {
    $this->logger->error('加密操作失败', ['error' => $e->getMessage()]);
    throw new APIException('服务暂时不可用', 503);
}

// ❌ 不安全的错误处理
try {
    $decrypted = $cryptoEngine->decrypt($encryptedData, $key);
} catch (\Exception $e) {
    // 直接暴露错误信息（不安全）
    throw new APIException($e->getMessage(), 500);
}
```

### 性能优化最佳实践

#### 1. 缓存策略
```php
class OptimizedCryptoService
{
    private $cache;
    private $keyCache;
    
    public function encrypt(string $data, string $keyId): string
    {
        // 缓存密钥以避免重复查询
        $key = $this->keyCache->remember($keyId, 300, function() use ($keyId) {
            return $this->keyManager->getKey($keyId);
        });
        
        // 对于小数据使用缓存结果
        if (strlen($data) < 1024) {
            $cacheKey = 'encrypt:' . hash('sha256', $data . $keyId);
            return $this->cache->remember($cacheKey, 60, function() use ($data, $key) {
                return $this->cryptoEngine->encrypt($data, $key);
            });
        }
        
        return $this->cryptoEngine->encrypt($data, $key);
    }
}
```

#### 2. 批量操作优化
```php
class BatchCryptoOperations
{
    public function encryptBatch(array $dataItems, string $keyId): array
    {
        $key = $this->keyManager->getKey($keyId);
        $results = [];
        
        // 批量处理以减少函数调用开销
        foreach (array_chunk($dataItems, 100) as $chunk) {
            foreach ($chunk as $item) {
                $results[] = $this->cryptoEngine->encrypt($item, $key);
            }
            
            // 定期释放内存
            if (memory_get_usage() > 100 * 1024 * 1024) { // 100MB
                gc_collect_cycles();
            }
        }
        
        return $results;
    }
}
```

---

## 🔧 故障排除

### 常见问题解决

#### 1. 加密相关问题
```bash
# 问题：SM4加密失败
# 原因：密钥长度不正确
# 解决：确保密钥长度为32字节（256位）

# 检查密钥长度
php -r "echo strlen(base64_decode('your_base64_key'));"

# 生成正确长度的密钥
php -r "echo base64_encode(random_bytes(32));"
```

```bash
# 问题：GCM模式认证失败
# 原因：IV重复使用或数据被篡改
# 解决：确保每次加密使用唯一的IV

# 生成随机IV
php -r "echo base64_encode(random_bytes(16));"
```

#### 2. 性能问题
```bash
# 问题：加密速度慢
# 原因：频繁的密钥派生
# 解决：启用密钥缓存

# 检查PHP扩展
php -m | grep -E "(openssl|sodium|gmp)"

# 启用OPcache
echo "opcache.enable=1" >> /etc/php/8.1/cli/php.ini
```

#### 3. 内存问题
```bash
# 问题：处理大文件时内存不足
# 原因：一次性读取整个文件
# 解决：使用流式处理

# 增加PHP内存限制（临时方案）
php -d memory_limit=1G your_script.php

# 检查内存使用
php -r "echo memory_get_peak_usage(true) / 1024 / 1024 . ' MB';"
```

### 调试命令

#### 1. 系统诊断
```bash
# 检查系统状态
curl -X GET http://localhost:8000/api/health

# 检查数据库连接
php artisan db:check

# 检查缓存状态
php artisan cache:status

# 检查日志
tail -f storage/logs/app.log
```

#### 2. 性能分析
```bash
# 启用Xdebug分析
php -d xdebug.mode=profile your_script.php

# 使用内置分析器
php -d auto_prepend_file=profiler_start.php \
    -d auto_append_file=profiler_end.php \
    your_script.php
```

---

## 📞 技术支持

### 获取帮助

1. **文档资源**
   - 官方文档：https://docs.alingai.com
   - API参考：https://api-docs.alingai.com
   - 示例代码：https://github.com/alingai/examples

2. **社区支持**
   - 开发者论坛：https://forum.alingai.com
   - GitHub Issues：https://github.com/alingai/alingai-pro-6.0/issues
   - Stack Overflow：标签 `alingai-pro`

3. **专业支持**
   - 技术支持邮箱：tech-support@alingai.com
   - 紧急热线：400-xxx-xxxx
   - 企业服务：enterprise@alingai.com

### 提交Bug报告

请包含以下信息：
- 操作系统和PHP版本
- AlingAi Pro版本号
- 完整的错误信息和堆栈跟踪
- 重现步骤
- 相关的配置文件（移除敏感信息）

---

**© 2025 AlingAi Pro 6.0 - 开发者集成指南**
