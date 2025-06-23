#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Console\Commands\MigrateCommand;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * AlingAI Pro 5.0 数据库迁移工具
 * 命令行数据库管理工具
 */

// 加载环境变量
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// 创建日志器
$logger = new Logger('migration');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

// 数据库配置
$config = [
    'driver' => $_ENV['DB_DRIVER'] ?? 'mysql',
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'database' => $_ENV['DB_DATABASE'] ?? 'alingai_pro_v5',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];

try {
    // 创建数据库连接
    $dsn = sprintf('%s:host=%s;port=%d;dbname=%s;charset=%s',
        $config['driver'],
        $config['host'],
        $config['port'],
        $config['database'],
        $config['charset']
    );
    
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    
    // 创建迁移命令实例
    $migrateCommand = new MigrateCommand($pdo, $logger);
    
    // 执行命令
    $args = array_slice($argv, 1);
    $exitCode = $migrateCommand->execute($args);
    
    exit($exitCode);
    
} catch (PDOException $e) {
    echo "\033[31m❌ Database connection failed: {$e->getMessage()}\033[0m\n";
    echo "\033[33m💡 Please check your database configuration in .env file\033[0m\n";
    exit(1);
    
} catch (\Exception $e) {
    echo "\033[31m❌ Command failed: {$e->getMessage()}\033[0m\n";
    exit(1);
}
