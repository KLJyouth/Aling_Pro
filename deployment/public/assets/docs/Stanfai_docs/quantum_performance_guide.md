# 量子安全性能优化指南

## 核心配置

### 1. 路由缓存配置
```php
'routing' => [
    'high_frequency' => [
        'cache_ttl' => 3600,    // 缓存时间(秒)
        'driver' => 'redis',     // 缓存驱动
        'preload_keys' => true   // 密钥预加载
    ]
]
```

### 2. 批量处理配置
```php
'batch' => [
    'size' => 100,             // 批量大小
    'timeout' => 5000,         // 超时(毫秒)
    'queue' => [               // 队列配置
        'connection' => 'redis',
        'retries' => 3
    ]
]
```

## 监控指标

| 指标 | 类型 | 警告阈值 | 严重阈值 |
|------|------|---------|---------|
| keygen_time | 耗时(ms) | 500 | 1000 |
| sign_time | 耗时(ms) | 300 | 500 |
| cache_hit | 命中率(%) | 90 | 80 |
| batch_throughput | 处理量(个/秒) | - | - |

## 优化建议

1. **高频路由**：
   ```bash
   # 预加载密钥
   php artisan quantum:preload-keys
   ```

2. **批量处理**：
   ```php
   // 使用队列处理批量签名
   QuantumJob::dispatch($contracts)->onQueue('quantum');
   ```

3. **监控调整**：
   ```php
   // 根据业务调整阈值
   'keygen_time' => [
       'warning' => 800,  // 生产环境建议值
       'critical' => 1500
   ]
   ```