# 量子加密(PQC)与国密算法指南

## 1. 概述
Stanfai PHP支持以下加密标准：
- **量子安全加密(PQC)**：基于NIST PQC标准的KYBER和NTRU算法
- **国密算法**：SM2, SM3, SM4
- **传统算法**：AES-256, RSA-4096
- **混合加密**：量子安全与传统算法的组合方案

## 2. 国密算法集成

### 2.1 SM2非对称加密
```php
use Security\ChineseSM\SM2;

// 初始化
$sm2 = new SM2();
$keypair = $sm2->generateKeyPair();

// 加密/解密
$encrypted = $sm2->encrypt($data, $keypair['public_key']);
$decrypted = $sm2->decrypt($encrypted, $keypair['private_key']);

// 签名/验证
$signature = $sm2->sign($data, $keypair['private_key']);
$isValid = $sm2->verify($data, $signature, $keypair['public_key']);
```

### 2.2 SM3哈希算法
```php
use Security\ChineseSM\SM3;

$hasher = new SM3();
$hash = $hasher->hash($data); // 返回64位十六进制字符串
```

### 2.3 SM4对称加密
```php
use Security\ChineseSM\SM4;

$sm4 = new SM4();
$encrypted = $sm4->encrypt($data, $key);
$decrypted = $sm4->decrypt($encrypted, $key);
```

## 3. 量子安全加密(PQC)

### 3.1 量子加密服务
```php
use Security\Quantum\QuantumCryptoService;

// 初始化服务
$cryptoService = new QuantumCryptoService();

// 生成量子安全密钥对
$keyPair = $cryptoService->generateKeyPair();

// 加密数据
$encrypted = $cryptoService->encrypt($data, $keyPair['public_key']);

// 解密数据
$decrypted = $cryptoService->decrypt($encrypted, $keyPair['private_key']);

// 密钥封装机制(KEM)
$encapsulated = $cryptoService->encapsulate($keyPair['public_key']);
$sharedSecret = $cryptoService->decapsulate($encapsulated, $keyPair['private_key']);
```

### 3.2 密钥管理
```php
use Security\Quantum\QuantumKeyManager;

$keyManager = new QuantumKeyManager();

// 存储密钥
$keyId = $keyManager->storeKey($keyPair['private_key'], 'user123');

// 检索密钥
$privateKey = $keyManager->retrieveKey($keyId);

// 密钥轮换
$newKeyPair = $keyManager->rotateKey($keyId);
```

## 4. 混合加密方案

### 4.1 国密+量子加密
```php
// 使用SM2交换密钥，KYBER1024加密数据
$sm2 = new SM2();
$kyber = new KYBER1024();

// 密钥交换
$sessionKey = $sm2->keyExchange($remotePublicKey, $localPrivateKey);

// 数据加密
$encryptedData = $kyber->encrypt($data, $sessionKey);
```

## 5. 性能优化

### 5.1 硬件加速
```php
// 启用AES-NI和SHA指令集
$crypto->useHardwareAcceleration(true);

// 专用加密卡支持
$crypto->useHSM('nfast://hsm-server');
```

### 5.2 批量操作
```php
// 批量加密(减少上下文切换)
$batch = new CryptoBatch();
$batch->add($data1)->add($data2);
$results = $batch->encrypt();

// 并行处理
$pool = new CryptoThreadPool(4);
$pool->submit($encryptJob);
```

[返回术语规范](../terminology.md) | [查看API参考](../api/encryption.md)