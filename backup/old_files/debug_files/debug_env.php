<?php
/**
 * 环境变量加载调试脚本
 */

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

echo "环境变量调试...\n";
echo str_repeat("=", 50) . "\n";

echo "当前工作目录: " . getcwd() . "\n";
echo ".env 文件是否存在: " . (file_exists(__DIR__ . '/.env') ? '是' : '否') . "\n";

if (file_exists(__DIR__ . '/.env')) {
    echo ".env 文件大小: " . filesize(__DIR__ . '/.env') . " 字节\n";
    
    echo "\n手动读取 .env 文件内容:\n";
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $i => $line) {
        if ($i < 20) { // 只显示前20行
            echo "行 " . ($i + 1) . ": " . $line . "\n";
        }
    }
}

echo "\n加载前的环境变量:\n";
echo "DB_CONNECTION: " . (getenv('DB_CONNECTION') ?: 'not set') . "\n";
echo "DB_DATABASE: " . (getenv('DB_DATABASE') ?: 'not set') . "\n";

try {
    echo "\n尝试加载 dotenv...\n";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    echo "✅ dotenv 加载完成\n";
} catch (Exception $e) {
    echo "❌ dotenv 加载失败: " . $e->getMessage() . "\n";
}

echo "\n加载后的环境变量:\n";
echo "DB_CONNECTION: " . (getenv('DB_CONNECTION') ?: 'not set') . "\n";
echo "DB_DATABASE: " . (getenv('DB_DATABASE') ?: 'not set') . "\n";
echo "APP_ENV: " . (getenv('APP_ENV') ?: 'not set') . "\n";

echo "\n检查 \$_ENV 数组:\n";
echo "DB_CONNECTION in \$_ENV: " . (isset($_ENV['DB_CONNECTION']) ? $_ENV['DB_CONNECTION'] : 'not set') . "\n";
echo "DB_DATABASE in \$_ENV: " . (isset($_ENV['DB_DATABASE']) ? $_ENV['DB_DATABASE'] : 'not set') . "\n";
