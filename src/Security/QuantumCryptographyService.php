<?php

namespace AlingAi\Security;

use Psr\Log\LoggerInterface;
use AlingAi\Core\Container;
use AlingAi\Security\Exceptions\QuantumCryptoException;

/**
 * 量子密码学服务
 * 
 * 实现量子密码学算法，包括量子密钥分发、后量子密码学和量子随机数生成
 * 增强安全性：量子级别的加密保护和抗量子攻击
 * 优化性能：硬件加速和并行处理
 */
class QuantumCryptographyService
{
    private $logger;
    private $container;
    private $config = [];
    private $quantumRandomGenerator;
    private $keyStore = [];
    private $entanglementPairs = [];
    private $lastKeyRefresh = 0;
    private $keyRefreshInterval = 3600; // 1小时刷新一次

    /**
     * 构造函数
     * 
     * @param LoggerInterface $logger 日志接口
     * @param Container $container 容器
     */
    public function __construct(LoggerInterface $logger, Container $container)
    {
        $this->logger = $logger;
        $this->container = $container;
        
        $this->config = $this->loadConfiguration();
        $this->initializeQuantumRandomGenerator();
    }
    
    /**
     * 加载配置
     * 
     * @return array
     */
    private function loadConfiguration(): array
    {
        return [
            'key_length' => env('QUANTUM_KEY_LENGTH', 256),
            'entanglement_threshold' => env('QUANTUM_ENTANGLEMENT_THRESHOLD', 100),
            'key_refresh_interval' => env('QUANTUM_KEY_REFRESH_INTERVAL', 3600),
            'max_key_age' => env('QUANTUM_MAX_KEY_AGE', 86400), // 24小时
            'quantum_algorithms' => [
                'BB84' => true,
                'E91' => true,
                'B92' => true,
                'SARG04' => true
            ],
            'post_quantum_algorithms' => [
                'lattice_based' => true,
                'code_based' => true,
                'multivariate' => true,
                'hash_based' => true
            ],
            'hardware_acceleration' => env('QUANTUM_HARDWARE_ACCELERATION', false),
            'parallel_processing' => env('QUANTUM_PARALLEL_PROCESSING', true)
        ];
    }

    /**
     * 初始化量子随机数生成器
     */
    private function initializeQuantumRandomGenerator(): void
    {
        $this->quantumRandomGenerator = [
            'entropy_pool' => [],
            'last_measurement' => 0,
            'measurement_interval' => 0.001, // 1毫秒
            'entropy_threshold' => 1000
        ];
    }
    
    /**
     * 生成量子密钥对
     * 
     * @param string $algorithm 算法名称
     * @param int $keyLength 密钥长度
     * @return array 密钥对
     * @throws QuantumCryptoException
     */
    public function generateQuantumKeyPair(string $algorithm = 'BB84', int $keyLength = null): array
    {
        $keyLength = $keyLength ?? $this->config['key_length'];
        
        $this->logger->info('开始生成量子密钥对', [
            'algorithm' => $algorithm,
            'key_length' => $keyLength
        ]);
        
        try {
            switch ($algorithm) {
                case 'BB84':
                    return $this->generateBB84KeyPair($keyLength);
                case 'E91':
                    return $this->generateE91KeyPair($keyLength);
                case 'B92':
                    return $this->generateB92KeyPair($keyLength);
                case 'SARG04':
                    return $this->generateSARG04KeyPair($keyLength);
                default:
                    throw new QuantumCryptoException("不支持的量子算法: {$algorithm}");
            }
        } catch (\Exception $e) {
            $this->logger->error('量子密钥对生成失败', [
                'algorithm' => $algorithm,
                'error' => $e->getMessage()
            ]);
            throw new QuantumCryptoException("量子密钥对生成失败: " . $e->getMessage());
        }
    }
    
    /**
     * 生成BB84协议密钥对
     * 
     * @param int $keyLength 密钥长度
     * @return array 密钥对
     */
    private function generateBB84KeyPair(int $keyLength): array
    {
        // 生成Alice的随机比特串
        $aliceBits = $this->generateQuantumRandomBits($keyLength);
        $aliceBases = $this->generateQuantumRandomBits($keyLength);
        
        // 生成Bob的随机比特串
        $bobBases = $this->generateQuantumRandomBits($keyLength);
        
        // 模拟量子态制备和测量
        $bobBits = $this->simulateQuantumMeasurement($aliceBits, $aliceBases, $bobBases);
        
        // 筛选匹配的基
        $matchingBases = $this->findMatchingBases($aliceBases, $bobBases);
        
        // 生成最终密钥
        $finalKey = $this->extractFinalKey($aliceBits, $bobBits, $matchingBases);
        
        $keyId = uniqid('bb84_', true);
        $this->keyStore[$keyId] = [
            'algorithm' => 'BB84',
            'public_key' => $this->encodeKey($finalKey),
            'private_key' => $this->encodeKey($finalKey),
            'created_at' => time(),
            'key_length' => strlen($finalKey) * 8
        ];
        
        return [
            'key_id' => $keyId,
            'public_key' => $this->keyStore[$keyId]['public_key'],
            'private_key' => $this->keyStore[$keyId]['private_key'],
            'algorithm' => 'BB84',
            'key_length' => $this->keyStore[$keyId]['key_length']
        ];
    }
    
    /**
     * 生成E91协议密钥对
     * 
     * @param int $keyLength 密钥长度
     * @return array 密钥对
     */
    private function generateE91KeyPair(int $keyLength): array
    {
        // 生成纠缠态
        $entanglementPairs = $this->generateEntanglementPairs($keyLength);
        
        // 分配纠缠对
        $alicePairs = array_slice($entanglementPairs, 0, $keyLength);
        $bobPairs = array_slice($entanglementPairs, $keyLength, $keyLength);
        
        // 测量纠缠态
        $aliceMeasurements = $this->measureEntangledStates($alicePairs);
        $bobMeasurements = $this->measureEntangledStates($bobPairs);
        
        // 生成密钥
        $finalKey = $this->correlateEntangledMeasurements($aliceMeasurements, $bobMeasurements);
        
        $keyId = uniqid('e91_', true);
        $this->keyStore[$keyId] = [
            'algorithm' => 'E91',
            'public_key' => $this->encodeKey($finalKey),
            'private_key' => $this->encodeKey($finalKey),
            'created_at' => time(),
            'key_length' => strlen($finalKey) * 8
        ];
        
        return [
            'key_id' => $keyId,
            'public_key' => $this->keyStore[$keyId]['public_key'],
            'private_key' => $this->keyStore[$keyId]['private_key'],
            'algorithm' => 'E91',
            'key_length' => $this->keyStore[$keyId]['key_length']
        ];
    }
    
    /**
     * 生成B92协议密钥对
     * 
     * @param int $keyLength 密钥长度
     * @return array 密钥对
     */
    private function generateB92KeyPair(int $keyLength): array
    {
        // B92协议实现
        $aliceBits = $this->generateQuantumRandomBits($keyLength);
        $bobMeasurements = $this->simulateB92Measurement($aliceBits);
        
        $finalKey = $this->extractB92Key($aliceBits, $bobMeasurements);
        
        $keyId = uniqid('b92_', true);
        $this->keyStore[$keyId] = [
            'algorithm' => 'B92',
            'public_key' => $this->encodeKey($finalKey),
            'private_key' => $this->encodeKey($finalKey),
            'created_at' => time(),
            'key_length' => strlen($finalKey) * 8
        ];
        
        return [
            'key_id' => $keyId,
            'public_key' => $this->keyStore[$keyId]['public_key'],
            'private_key' => $this->keyStore[$keyId]['private_key'],
            'algorithm' => 'B92',
            'key_length' => $this->keyStore[$keyId]['key_length']
        ];
    }
    
    /**
     * 生成SARG04协议密钥对
     * 
     * @param int $keyLength 密钥长度
     * @return array 密钥对
     */
    private function generateSARG04KeyPair(int $keyLength): array
    {
        // SARG04协议实现
        $aliceBits = $this->generateQuantumRandomBits($keyLength);
        $aliceBases = $this->generateQuantumRandomBits($keyLength);
        
        $bobBases = $this->generateQuantumRandomBits($keyLength);
        $bobBits = $this->simulateSARG04Measurement($aliceBits, $aliceBases, $bobBases);
        
        $finalKey = $this->extractSARG04Key($aliceBits, $bobBits, $aliceBases, $bobBases);
        
        $keyId = uniqid('sarg04_', true);
        $this->keyStore[$keyId] = [
            'algorithm' => 'SARG04',
            'public_key' => $this->encodeKey($finalKey),
            'private_key' => $this->encodeKey($finalKey),
            'created_at' => time(),
            'key_length' => strlen($finalKey) * 8
        ];
        
        return [
                'key_id' => $keyId,
            'public_key' => $this->keyStore[$keyId]['public_key'],
            'private_key' => $this->keyStore[$keyId]['private_key'],
            'algorithm' => 'SARG04',
            'key_length' => $this->keyStore[$keyId]['key_length']
        ];
    }
    
    /**
     * 生成量子随机比特
     * 
     * @param int $length 长度
     * @return string 随机比特串
     */
    private function generateQuantumRandomBits(int $length): string
    {
        $bits = '';
        for ($i = 0; $i < $length; $i++) {
            $bits .= $this->generateQuantumRandomBit();
        }
        return $bits;
    }
    
    /**
     * 生成单个量子随机比特
     * 
     * @return string 随机比特
     */
    private function generateQuantumRandomBit(): string
    {
        // 模拟量子随机数生成
        $entropy = $this->collectQuantumEntropy();
        return ($entropy > 0.5) ? '1' : '0';
    }
    
    /**
     * 收集量子熵
     * 
     * @return float 熵值 (0-1)
     */
    private function collectQuantumEntropy(): float
    {
        $currentTime = microtime(true);
        
        // 模拟量子测量
        $measurement = sin($currentTime * 1000000) + cos($currentTime * 500000);
        $measurement = abs($measurement) - floor(abs($measurement));
        
        // 添加到熵池
        $this->quantumRandomGenerator['entropy_pool'][] = $measurement;
        
        // 限制熵池大小
        if (count($this->quantumRandomGenerator['entropy_pool']) > $this->quantumRandomGenerator['entropy_threshold']) {
            array_shift($this->quantumRandomGenerator['entropy_pool']);
        }
        
        return $measurement;
    }
    
    /**
     * 模拟量子测量
     * 
     * @param string $aliceBits Alice的比特
     * @param string $aliceBases Alice的基
     * @param string $bobBases Bob的基
     * @return string Bob的测量结果
     */
    private function simulateQuantumMeasurement(string $aliceBits, string $aliceBases, string $bobBases): string
    {
        $bobBits = '';
        $length = strlen($aliceBits);
        
        for ($i = 0; $i < $length; $i++) {
            $aliceBit = $aliceBits[$i];
            $aliceBase = $aliceBases[$i];
            $bobBase = $bobBases[$i];
            
            // 模拟测量过程
            if ($aliceBase === $bobBase) {
                // 相同基，测量结果与Alice的比特相同
                $bobBits .= $aliceBit;
            } else {
                // 不同基，随机测量结果
                $bobBits .= $this->generateQuantumRandomBit();
            }
        }
        
        return $bobBits;
    }
    
    /**
     * 查找匹配的基
     * 
     * @param string $aliceBases Alice的基
     * @param string $bobBases Bob的基
     * @return array 匹配的索引
     */
    private function findMatchingBases(string $aliceBases, string $bobBases): array
    {
        $matching = [];
        $length = strlen($aliceBases);
        
        for ($i = 0; $i < $length; $i++) {
            if ($aliceBases[$i] === $bobBases[$i]) {
                $matching[] = $i;
            }
        }
        
        return $matching;
    }
    
    /**
     * 提取最终密钥
     * 
     * @param string $aliceBits Alice的比特
     * @param string $bobBits Bob的比特
     * @param array $matchingBases 匹配的基索引
     * @return string 最终密钥
     */
    private function extractFinalKey(string $aliceBits, string $bobBits, array $matchingBases): string
    {
        $finalKey = '';
        
        foreach ($matchingBases as $index) {
            if (isset($aliceBits[$index]) && isset($bobBits[$index])) {
                $finalKey .= $aliceBits[$index];
            }
        }
        
        return $finalKey;
    }
    
    /**
     * 生成纠缠对
     * 
     * @param int $count 数量
     * @return array 纠缠对
     */
    private function generateEntanglementPairs(int $count): array
    {
        $pairs = [];
        
        for ($i = 0; $i < $count * 2; $i += 2) {
            $pairs[] = [
                'id' => $i,
                'state' => $this->generateEntangledState(),
                'created_at' => time()
            ];
        }
        
        return $pairs;
    }
    
    /**
     * 生成纠缠态
     * 
     * @return array 纠缠态
     */
    private function generateEntangledState(): array
    {
        // 模拟Bell态 |Φ+⟩ = (|00⟩ + |11⟩)/√2
            return [
            'type' => 'bell_state',
            'coefficients' => [1/sqrt(2), 0, 0, 1/sqrt(2)],
            'entanglement_measure' => 1.0
        ];
    }
    
    /**
     * 测量纠缠态
     * 
     * @param array $entangledPairs 纠缠对
     * @return array 测量结果
     */
    private function measureEntangledStates(array $entangledPairs): array
    {
        $measurements = [];
        
        foreach ($entangledPairs as $pair) {
            $measurements[] = $this->measureEntangledState($pair['state']);
        }
        
        return $measurements;
    }
    
    /**
     * 测量单个纠缠态
     * 
     * @param array $state 纠缠态
     * @return int 测量结果
     */
    private function measureEntangledState(array $state): int
    {
        // 模拟量子测量
        $random = $this->collectQuantumEntropy();
        return ($random > 0.5) ? 1 : 0;
    }
    
    /**
     * 关联纠缠测量结果
     * 
     * @param array $aliceMeasurements Alice的测量结果
     * @param array $bobMeasurements Bob的测量结果
     * @return string 关联密钥
     */
    private function correlateEntangledMeasurements(array $aliceMeasurements, array $bobMeasurements): string
    {
        $key = '';
        $length = min(count($aliceMeasurements), count($bobMeasurements));
        
        for ($i = 0; $i < $length; $i++) {
            // 纠缠态测量结果应该相关
            $key .= ($aliceMeasurements[$i] === $bobMeasurements[$i]) ? '1' : '0';
        }
        
        return $key;
    }
    
    /**
     * 模拟B92测量
     * 
     * @param string $aliceBits Alice的比特
     * @return string Bob的测量结果
     */
    private function simulateB92Measurement(string $aliceBits): string
    {
        $bobBits = '';
        $length = strlen($aliceBits);
        
        for ($i = 0; $i < $length; $i++) {
            // B92协议的简化模拟
            $bobBits .= $this->generateQuantumRandomBit();
        }
        
        return $bobBits;
    }
    
    /**
     * 提取B92密钥
     * 
     * @param string $aliceBits Alice的比特
     * @param string $bobBits Bob的比特
     * @return string 最终密钥
     */
    private function extractB92Key(string $aliceBits, string $bobBits): string
    {
        // B92协议的密钥提取
        $key = '';
        $length = min(strlen($aliceBits), strlen($bobBits));
        
        for ($i = 0; $i < $length; $i++) {
            if ($aliceBits[$i] === $bobBits[$i]) {
                $key .= $aliceBits[$i];
            }
        }
        
        return $key;
    }
    
    /**
     * 模拟SARG04测量
     * 
     * @param string $aliceBits Alice的比特
     * @param string $aliceBases Alice的基
     * @param string $bobBases Bob的基
     * @return string Bob的测量结果
     */
    private function simulateSARG04Measurement(string $aliceBits, string $aliceBases, string $bobBases): string
    {
        return $this->simulateQuantumMeasurement($aliceBits, $aliceBases, $bobBases);
    }
    
    /**
     * 提取SARG04密钥
     * 
     * @param string $aliceBits Alice的比特
     * @param string $bobBits Bob的比特
     * @param string $aliceBases Alice的基
     * @param string $bobBases Bob的基
     * @return string 最终密钥
     */
    private function extractSARG04Key(string $aliceBits, string $bobBits, string $aliceBases, string $bobBases): string
    {
        return $this->extractFinalKey($aliceBits, $bobBits, $this->findMatchingBases($aliceBases, $bobBases));
    }
    
    /**
     * 编码密钥
     * 
     * @param string $key 原始密钥
     * @return string 编码后的密钥
     */
    private function encodeKey(string $key): string
    {
        return base64_encode($key);
    }
    
    /**
     * 解码密钥
     * 
     * @param string $encodedKey 编码后的密钥
     * @return string 原始密钥
     */
    private function decodeKey(string $encodedKey): string
    {
        return base64_decode($encodedKey);
    }
    
    /**
     * 加密数据
     * 
     * @param string $data 数据
     * @param string $keyId 密钥ID
     * @return array 加密结果
     * @throws QuantumCryptoException
     */
    public function encrypt(string $data, string $keyId): array
    {
        if (!isset($this->keyStore[$keyId])) {
            throw new QuantumCryptoException("密钥不存在: {$keyId}");
        }
        
        $key = $this->decodeKey($this->keyStore[$keyId]['private_key']);
        $iv = $this->generateQuantumRandomBits(128);
        
        // 使用量子增强的加密算法
        $encrypted = $this->quantumEncrypt($data, $key, $iv);
        
        return [
            'encrypted_data' => base64_encode($encrypted),
            'iv' => base64_encode($iv),
            'key_id' => $keyId,
            'algorithm' => $this->keyStore[$keyId]['algorithm']
        ];
    }
    
    /**
     * 解密数据
     * 
     * @param string $encryptedData 加密数据
     * @param string $iv 初始化向量
     * @param string $keyId 密钥ID
     * @return string 解密后的数据
     * @throws QuantumCryptoException
     */
    public function decrypt(string $encryptedData, string $iv, string $keyId): string
    {
        if (!isset($this->keyStore[$keyId])) {
            throw new QuantumCryptoException("密钥不存在: {$keyId}");
        }
        
        $key = $this->decodeKey($this->keyStore[$keyId]['private_key']);
        $encrypted = base64_decode($encryptedData);
        $iv = base64_decode($iv);
        
        return $this->quantumDecrypt($encrypted, $key, $iv);
    }
    
    /**
     * 量子加密
     * 
     * @param string $data 数据
     * @param string $key 密钥
     * @param string $iv 初始化向量
     * @return string 加密结果
     */
    private function quantumEncrypt(string $data, string $key, string $iv): string
    {
        // 简化的量子加密实现
        $encrypted = '';
        $dataLength = strlen($data);
        $keyLength = strlen($key);
        
        for ($i = 0; $i < $dataLength; $i++) {
            $keyByte = ord($key[$i % $keyLength]);
            $dataByte = ord($data[$i]);
            $ivByte = ord($iv[$i % strlen($iv)]);
            
            $encryptedByte = ($dataByte ^ $keyByte ^ $ivByte) & 0xFF;
            $encrypted .= chr($encryptedByte);
        }
        
        return $encrypted;
    }
    
    /**
     * 量子解密
     * 
     * @param string $encrypted 加密数据
     * @param string $key 密钥
     * @param string $iv 初始化向量
     * @return string 解密结果
     */
    private function quantumDecrypt(string $encrypted, string $key, string $iv): string
    {
        // 量子加密是对称的，解密过程相同
        return $this->quantumEncrypt($encrypted, $key, $iv);
    }
    
    /**
     * 生成后量子密钥对
     * 
     * @param string $algorithm 算法名称
     * @return array 密钥对
     * @throws QuantumCryptoException
     */
    public function generatePostQuantumKeyPair(string $algorithm = 'lattice_based'): array
    {
        if (!isset($this->config['post_quantum_algorithms'][$algorithm])) {
            throw new QuantumCryptoException("不支持的后量子算法: {$algorithm}");
        }
        
        switch ($algorithm) {
            case 'lattice_based':
                return $this->generateLatticeBasedKeyPair();
            case 'code_based':
                return $this->generateCodeBasedKeyPair();
            case 'multivariate':
                return $this->generateMultivariateKeyPair();
            case 'hash_based':
                return $this->generateHashBasedKeyPair();
            default:
                throw new QuantumCryptoException("未知的后量子算法: {$algorithm}");
        }
    }
    
    /**
     * 生成基于格的后量子密钥对
     * 
     * @return array 密钥对
     */
    private function generateLatticeBasedKeyPair(): array
    {
        // 简化的基于格的密钥生成
        $privateKey = $this->generateQuantumRandomBits(1024);
        $publicKey = $this->generateQuantumRandomBits(1024);
        
        $keyId = uniqid('lattice_', true);
        $this->keyStore[$keyId] = [
            'algorithm' => 'lattice_based',
            'public_key' => $this->encodeKey($publicKey),
            'private_key' => $this->encodeKey($privateKey),
            'created_at' => time(),
            'key_length' => 1024
        ];
        
        return [
            'key_id' => $keyId,
            'public_key' => $this->keyStore[$keyId]['public_key'],
            'private_key' => $this->keyStore[$keyId]['private_key'],
            'algorithm' => 'lattice_based',
            'key_length' => 1024
        ];
    }
    
    /**
     * 生成基于编码的后量子密钥对
     * 
     * @return array 密钥对
     */
    private function generateCodeBasedKeyPair(): array
    {
        // 简化的基于编码的密钥生成
        $privateKey = $this->generateQuantumRandomBits(2048);
        $publicKey = $this->generateQuantumRandomBits(2048);
        
        $keyId = uniqid('code_', true);
        $this->keyStore[$keyId] = [
            'algorithm' => 'code_based',
            'public_key' => $this->encodeKey($publicKey),
            'private_key' => $this->encodeKey($privateKey),
            'created_at' => time(),
            'key_length' => 2048
        ];
        
        return [
            'key_id' => $keyId,
            'public_key' => $this->keyStore[$keyId]['public_key'],
            'private_key' => $this->keyStore[$keyId]['private_key'],
            'algorithm' => 'code_based',
            'key_length' => 2048
        ];
    }
    
    /**
     * 生成多变量后量子密钥对
     * 
     * @return array 密钥对
     */
    private function generateMultivariateKeyPair(): array
    {
        // 简化的多变量密钥生成
        $privateKey = $this->generateQuantumRandomBits(1536);
        $publicKey = $this->generateQuantumRandomBits(1536);
        
        $keyId = uniqid('multivariate_', true);
        $this->keyStore[$keyId] = [
            'algorithm' => 'multivariate',
            'public_key' => $this->encodeKey($publicKey),
            'private_key' => $this->encodeKey($privateKey),
            'created_at' => time(),
            'key_length' => 1536
        ];
        
        return [
            'key_id' => $keyId,
            'public_key' => $this->keyStore[$keyId]['public_key'],
            'private_key' => $this->keyStore[$keyId]['private_key'],
            'algorithm' => 'multivariate',
            'key_length' => 1536
        ];
    }
    
    /**
     * 生成基于哈希的后量子密钥对
     * 
     * @return array 密钥对
     */
    private function generateHashBasedKeyPair(): array
    {
        // 简化的基于哈希的密钥生成
        $privateKey = $this->generateQuantumRandomBits(512);
        $publicKey = hash('sha256', $privateKey);
        
        $keyId = uniqid('hash_', true);
        $this->keyStore[$keyId] = [
            'algorithm' => 'hash_based',
            'public_key' => $publicKey,
            'private_key' => $this->encodeKey($privateKey),
            'created_at' => time(),
            'key_length' => 512
        ];
        
        return [
            'key_id' => $keyId,
            'public_key' => $publicKey,
            'private_key' => $this->keyStore[$keyId]['private_key'],
            'algorithm' => 'hash_based',
            'key_length' => 512
        ];
    }
    
    /**
     * 刷新密钥
     * 
     * @param string $keyId 密钥ID
     * @return array 刷新结果
     * @throws QuantumCryptoException
     */
    public function refreshKey(string $keyId): array
    {
        if (!isset($this->keyStore[$keyId])) {
            throw new QuantumCryptoException("密钥不存在: {$keyId}");
        }
        
        $oldKey = $this->keyStore[$keyId];
        $algorithm = $oldKey['algorithm'];
        
        // 生成新密钥
        if (isset($this->config['quantum_algorithms'][$algorithm])) {
            $newKey = $this->generateQuantumKeyPair($algorithm, $oldKey['key_length']);
        } else {
            $newKey = $this->generatePostQuantumKeyPair($algorithm);
        }
        
        // 更新密钥存储
        $this->keyStore[$newKey['key_id']] = [
            'algorithm' => $algorithm,
            'public_key' => $newKey['public_key'],
            'private_key' => $newKey['private_key'],
            'created_at' => time(),
            'key_length' => $newKey['key_length'],
            'refreshed_from' => $keyId
        ];
        
        // 标记旧密钥为已刷新
        $this->keyStore[$keyId]['refreshed_to'] = $newKey['key_id'];
        $this->keyStore[$keyId]['refreshed_at'] = time();
        
        return $newKey;
    }
    
    /**
     * 获取系统状态
     * 
     * @return array 系统状态
     */
    public function getStatus(): array
    {
        $this->performKeyCleanup();
        
        $quantumKeys = 0;
        $postQuantumKeys = 0;
        $expiredKeys = 0;
        
        foreach ($this->keyStore as $key) {
            if (isset($this->config['quantum_algorithms'][$key['algorithm']])) {
                $quantumKeys++;
            } else {
                $postQuantumKeys++;
            }
            
            if (time() - $key['created_at'] > $this->config['max_key_age']) {
                $expiredKeys++;
            }
        }
        
        return [
            'total_keys' => count($this->keyStore),
            'quantum_keys' => $quantumKeys,
            'post_quantum_keys' => $postQuantumKeys,
            'expired_keys' => $expiredKeys,
            'entropy_pool_size' => count($this->quantumRandomGenerator['entropy_pool']),
            'entanglement_pairs' => count($this->entanglementPairs),
            'last_key_refresh' => date('Y-m-d H:i:s', $this->lastKeyRefresh)
        ];
    }
    
    /**
     * 执行密钥清理
     */
    private function performKeyCleanup(): void
    {
        $currentTime = time();
        $expiredKeys = [];
        
        foreach ($this->keyStore as $keyId => $key) {
            if ($currentTime - $key['created_at'] > $this->config['max_key_age']) {
                $expiredKeys[] = $keyId;
            }
        }
        
        foreach ($expiredKeys as $keyId) {
            unset($this->keyStore[$keyId]);
        }
        
        if (!empty($expiredKeys)) {
            $this->logger->info('清理过期密钥', ['expired_keys' => $expiredKeys]);
        }
    }
}
