# 量子安全模块使用指南

## 安装步骤

1. 运行安装脚本：
```bash
php scripts/setup_quantum.php
```

2. 添加路由配置：
```php
// 在routes/web.php或routes/api.php中添加
require __DIR__.'/quantum.php';
```

## 配置说明

编辑 `config/quantum.php`：
```php
return [
    'algorithm' => 'KYBER1024', // 量子算法
    'key_rotation' => 90,      // 密钥轮换周期(天)
    'key_storage' => 'vault',  // 密钥存储方式
    'signed_routes' => [       // 需要量子签名的路由
        'api/contract/*',
        'api/evidence/*'
    ]
];
```

## 使用方法

1. 在控制器中使用：
```php
use Security\Quantum\QuantumCryptoService;

class SecureController {
    public function store(Request $request)
    {
        $crypto = app(QuantumCryptoService::class);
        // 使用量子加密
    }
}
```

2. 路由中间件：
```php
Route::post('/secure-data', function () {
    // 需要量子签名的路由
})->middleware('quantum.signature');
```

## 测试验证

1. 生成测试密钥：
```bash
php artisan quantum:generate-test-key
```

2. 验证签名：
```bash
php artisan quantum:verify-signature test-file.txt
```