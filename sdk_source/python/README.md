# AlingAI Python SDK

量子安全API客户端库 - Python版本

## 版本
2.0.0

## 安装

### 使用 pip
```bash
pip install requests
```

### 手动安装
直接使用 `alingai.py` 文件：
```python
from alingai import AlingAIClient, QuantumCrypto
```

## 快速开始

```python
from alingai import AlingAIClient, QuantumCrypto

# 初始化客户端
client = AlingAIClient('your-api-key')

# 量子加密
encrypted = client.quantum_encrypt('Hello, Quantum World!')
print(encrypted)

# AI对话
response = client.chat('你好，AlingAI')
print(response['message'])

# 身份验证
verification = client.verify_identity('user-token')
print(verification)

# 量子密钥生成
key_pair = QuantumCrypto.generate_key_pair()
hash_value = QuantumCrypto.quantum_hash('sensitive data')
```

## API参考

### 初始化客户端
```python
client = AlingAIClient(api_key, base_url='https://api.alingai.com', timeout=30)
```

### 量子加密/解密
```python
encrypted = client.quantum_encrypt(text)
decrypted = client.quantum_decrypt(encrypted_data)
```

### AI对话
```python
response = client.chat(message, context=None)
```

### 身份验证
```python
result = client.verify_identity(token)
```

### 量子加密工具
```python
# 生成密钥对
key_pair = QuantumCrypto.generate_key_pair()

# 量子安全哈希
hash_value = QuantumCrypto.quantum_hash(data)
```

## 异常处理

```python
try:
    response = client.chat('Hello')
except Exception as e:
    print(f"API错误: {e}")
```

## 系统要求
- Python >= 3.7
- requests >= 2.25.0

## 开发依赖
- pytest >= 6.0
- pytest-cov >= 2.0
- black >= 21.0
- flake8 >= 3.8

## 许可证
MIT License

## 支持
- 文档：https://docs.alingai.com
- 邮箱：dev@alingai.com
- GitHub：https://github.com/alingai/python-sdk
