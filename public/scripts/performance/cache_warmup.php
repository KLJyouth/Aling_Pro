<?php
/**
 * 缓存预热脚本
 */
require_once __DIR__ . "/vendor/autoload.php";

echo "开始缓存预�?..\n";

// 预热系统设置
$settings = ["app_name", "ai_provider", "max_upload_size"];
foreach ($settings as $key) {
    // 模拟缓存系统设置
    file_put_contents("storage/cache/setting_$key", "cached_value_$key"];
}

// 预热用户数据
for ($i = 1; $i <= 10; $i++) {
    file_put_contents("storage/cache/user_$i", json_encode(["id" => $i, "cached" => true])];
}

echo "缓存预热完成！\n";
