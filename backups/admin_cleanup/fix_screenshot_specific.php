<?php

/**
 * 针对截图中显示的特定PHP语法错误的修复脚本
 * 修复类型包括：
 * 1. 构造函数的多余括号: __construct((array $config = [])) -> __construct(array $config = [])
 * 2. 行尾多余的分号和引号: 'key' => 'value', -> 'key' => 'value',
 * 3. 私有变量声明错误: private $var = ... -> $var = ...
 * 4. 配置值缺少引号: 'driver' => mysql, -> 'driver' => 'mysql',
 * 5. 对象方法调用语法错误: $containersomething() -> $container->something()
 * 6. 命名空间问题: WebController::class -> \AlingAi\Controllers\WebController::class
 */

// 设置执行时间，防止超时
set_time_limit(0);
ini_set("memory_limit", "1024M");

// 日志文件
$log_file = "screenshot_errors_fix_log_" . date("Ymd_His") . ".txt";

// 要修复的文件列表
$target_files = [
    'apps/ai-platform/Services/CV/ComputerVisionProcessor.php',
    'apps/ai-platform/Services/KnowledgeGraph/KnowledgeGraphProcessor.php',
    'apps/ai-platform/Services/Speech/SpeechProcessor.php',
    'apps/blockchain/Services/BlockchainServiceManager.php',
    'apps/blockchain/Services/SmartContractManager.php',
    'apps/blockchain/Services/WalletManager.php',
    'completed/Config/database.php',
    'config/database.php'
];

// 初始化日志
function init_log() {
    global $log_file;
    file_put_contents($log_file, "=== 截图错误修复日志 - " . date("Y-m-d H:i:s") . " ===\n\n");
}

// 写入日志
function log_message($message) {
    global $log_file;
    file_put_contents($log_file, $message . "\n", FILE_APPEND);
    echo $message . "\n";
}

// 修复文件
function fix_file($file) {
    if (!file_exists($file)) {
        log_message("文件不存在: {$file}");
        return false;
    }

    // 备份文件
    $backup_file = $file . ".bak_" . date("Ymd_His");
    copy($file, $backup_file);
    log_message("已创建备份: {$backup_file}");

    // 读取文件内容
    $content = file_get_contents($file);
    $original_content = $content;

    // 1. 修复构造函数的多余括号
    $content = preg_replace('/public function __construct\(\((.*?)\)\)/', 'public function __construct($1)', $content);

    // 2. 修复行尾多余的分号和引号
    $content = preg_replace('/\'(\w+)\'\s*=>\s*([^,\s]+),?\s*\';\'/', '\'$1\' => $2,', $content);
    $content = preg_replace('/\"(\w+)\"\s*=>\s*([^,\s]+),?\s*\";\"/', '"$1" => $2,', $content);

    // 3. 修复私有变量声明错误
    $content = preg_replace('/private\s+\$([\w]+)\s*=/', '$$$1 =', $content);

    // 4. 修复配置值缺少引号
    $content = preg_replace('/\'(\w+)\'\s*=>\s*(\w+),/', '\'$1\' => \'$2\',', $content);
    
    // 5. 修复对象方法调用语法错误
    $content = preg_replace('/\$(\w+)(\w+)\(/', '\$$1->$2(', $content);

    // 6. 修复命名空间问题
    $content = preg_replace('/(\w+)Controller::class/', '\\AlingAi\\Controllers\\$1Controller::class', $content);

    // 检查是否有修改
    if ($content !== $original_content) {
        // 写入修复后的内容
        file_put_contents($file, $content);
        log_message("已修复文件: {$file}");
        return true;
    } else {
        log_message("文件无需修复: {$file}");
        return false;
    }
}

// 主函数
function main() {
    global $target_files;
    
    init_log();
    log_message("开始修复截图中显示的PHP语法错误...");
    
    $fixed_count = 0;
    
    foreach ($target_files as $file) {
        log_message("\n处理文件: {$file}");
        if (fix_file($file)) {
            $fixed_count++;
        }
    }
    
    log_message("\n修复完成! 已修复 {$fixed_count} 个文件。");
}

// 执行主函数
main();