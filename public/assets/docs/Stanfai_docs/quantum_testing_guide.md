# 量子安全模块测试指南

## 测试环境要求
- PHP 8.0+
- OpenSSL扩展
- Composer依赖已安装

## 测试方法

### 1. 手动测试
```bash
# 安装依赖
composer install

# 运行测试脚本
php scripts/test_quantum.php
```

### 2. Docker测试
```bash
docker run -v $(pwd):/app -w /app php:8.0-cli \
  php scripts/test_quantum.php
```

### 3. 预期输出
```
测试结果:
1. 量子签名验证: 通过
2. 无效签名检测: 通过 
3. HMAC回退验证: 通过
```

## 测试用例说明

| 测试用例 | 描述 | 验证点 |
|---------|------|-------|
| 量子签名验证 | 使用有效量子签名 | 验证通过 |
| 无效签名检测 | 使用错误签名 | 验证失败 |
| HMAC回退 | 非量子请求 | 回退到HMAC验证 |

## 问题排查

1. **签名验证失败**：
   - 检查QuantumCryptoService配置
   - 验证密钥管理器存储

2. **性能问题**：
   - 监控密钥生成时间
   - 检查算法实现
```