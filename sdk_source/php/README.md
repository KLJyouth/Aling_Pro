# AlingAI PHP SDK

量子安全API客户端库 - PHP版本

## 版本
2.0.0

## 安装

### 使用 Composer
```bash
composer install
```

### 手动安装
直接引入 `AlingAI.php` 文件：
```php
require_once 'AlingAI.php';
```

## 快速开始

```php
use AlingAI\Client;

// 初始化客户端
$client = new Client('your-api-key');

// 量子加密
$encrypted = $client->quantumEncrypt('Hello, Quantum World!');
echo json_encode($encrypted);

// AI对话
$response = $client->chat('你好，AlingAI');
echo $response['message'];

// 身份验证
$verification = $client->verifyIdentity('user-token');
```

## API参考

### 初始化客户端
```php
$client = new Client($apiKey, $baseUrl, $timeout);
```

### 量子加密/解密
```php
$encrypted = $client->quantumEncrypt($text);
$decrypted = $client->quantumDecrypt($encryptedData);
```

### AI对话
```php
$response = $client->chat($message, $context);
```

### 身份验证
```php
$result = $client->verifyIdentity($token);
```

### 量子密钥生成
```php
use AlingAI\QuantumCrypto;

$keyPair = QuantumCrypto::generateKeyPair();
$hash = QuantumCrypto::quantumHash('data');
```

## 系统要求
- PHP >= 7.4
- cURL 扩展
- JSON 扩展

## 许可证
MIT License

## 支持
- 文档：https://docs.alingai.com
- 邮箱：dev@alingai.com
