# AlingAI JavaScript SDK

量子安全API客户端库 - JavaScript版本

## 版本
2.0.0

## 安装

### 使用 npm
```bash
npm install
```

### 浏览器引入
```html
<script src="alingai.js"></script>
```

### Node.js
```javascript
const { AlingAIClient, QuantumCrypto } = require('./alingai');
```

## 快速开始

### 浏览器环境
```javascript
// 初始化客户端
const client = new AlingAIClient('your-api-key');

// 量子加密
client.quantumEncrypt('Hello, Quantum World!')
    .then(encrypted => console.log(encrypted));

// AI对话
client.chat('你好，AlingAI')
    .then(response => console.log(response.message));

// 身份验证
client.verifyIdentity('user-token')
    .then(result => console.log(result));
```

### Node.js环境
```javascript
const { AlingAIClient } = require('./alingai');

async function example() {
    const client = new AlingAIClient('your-api-key');
    
    try {
        const encrypted = await client.quantumEncrypt('Hello!');
        console.log(encrypted);
        
        const response = await client.chat('你好');
        console.log(response.message);
    } catch (error) {
        console.error(error.message);
    }
}

example();
```

## API参考

### 初始化客户端
```javascript
const client = new AlingAIClient(apiKey, baseUrl, timeout);
```

### 量子加密/解密
```javascript
const encrypted = await client.quantumEncrypt(text);
const decrypted = await client.quantumDecrypt(encryptedData);
```

### AI对话
```javascript
const response = await client.chat(message, context);
```

### 身份验证
```javascript
const result = await client.verifyIdentity(token);
```

### 量子密钥生成
```javascript
const keyPair = QuantumCrypto.generateKeyPair();
const hash = await QuantumCrypto.quantumHash('data');
```

## 浏览器兼容性
- Chrome >= 60
- Firefox >= 55
- Safari >= 11
- Edge >= 79

## Node.js版本要求
- Node.js >= 14.0.0

## 许可证
MIT License

## 支持
- 文档：https://docs.alingai.com
- 邮箱：dev@alingai.com
