<?php
/**
 * 构造函数多余括号修复工具
 * 
 * 此脚本用于修复项目中构造函数多余括号的问题
 */

// 设置基础配置
$projectRoot = __DIR__;
$backupDir = $projectRoot . '/backups/constructor_fix_' . date('Ymd_His');
$fixCount = 0;
$errorCount = 0;
$backupCount = 0;

// 日志文件
$logFile = "constructor_fix_" . date("Ymd_His") . ".log";
$reportFile = "CONSTRUCTOR_FIX_REPORT_" . date("Ymd_His") . ".md";

// 初始化日志
echo "=== 构造函数多余括号修复工具 ===\n";
echo "时间: " . date('Y-m-d H:i:s') . "\n\n";
file_put_contents($logFile, "=== 构造函数多余括号修复日志 - " . date("Y-m-d H:i:s") . " ===\n\n");

/**
 * 写入日志
 */
function log_message($message) {
    global $logFile;
    echo $message . "\n";
    file_put_contents($logFile, $message . "\n", FILE_APPEND);
}

/**
 * 创建备份目录
 */
function create_backup_dir() {
    global $backupDir;
    
    if (!is_dir($backupDir)) {
        if (mkdir($backupDir, 0777, true)) {
            log_message("已创建备份目录: $backupDir");
            return true;
        } else {
            log_message("无法创建备份目录: $backupDir");
            return false;
        }
    }
    
    return true;
}

/**
 * 创建文件备份
 */
function backup_file($file) {
    global $backupDir, $backupCount;
    
    $relativePath = $file;
    $backupPath = $backupDir . '/' . $relativePath;
    $backupDirPath = dirname($backupPath);
    
    if (!is_dir($backupDirPath)) {
        if (!mkdir($backupDirPath, 0777, true)) {
            log_message("无法创建备份子目录: $backupDirPath");
            return false;
        }
    }
    
    if (copy($file, $backupPath)) {
        log_message("已备份文件: $file -> $backupPath");
        $backupCount++;
        return true;
    } else {
        log_message("无法备份文件: $file");
        return false;
    }
}

/**
 * 扫描目录查找PHP文件
 */
function find_php_files($dir) {
    $files = [];
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    
    return $files;
}

/**
 * 修复构造函数多余括号
 */
function fix_constructor_brackets($file) {
    global $fixCount, $errorCount;
    
    if (!file_exists($file)) {
        log_message("文件不存在: $file");
        $errorCount++;
        return false;
    }
    
    // 读取文件内容
    $content = file_get_contents($file);
    if ($content === false) {
        log_message("无法读取文件: $file");
        $errorCount++;
        return false;
    }
    
    // 查找构造函数多余括号
    $pattern = '/public\s+function\s+__construct\s*\(\(([^)]*)\)\)/';
    if (preg_match($pattern, $content, $matches)) {
        // 备份文件
        if (!backup_file($file)) {
            return false;
        }
        
        log_message("处理文件: $file");
        log_message("  - 发现多余括号的构造函数");
        
        // 修复构造函数多余括号
        $fixedContent = preg_replace(
            $pattern,
            'public function __construct($1)',
            $content
        );
        
        // 写入修改后的内容
        if (file_put_contents($file, $fixedContent)) {
            log_message("  ✅ 已修复构造函数多余括号");
            $fixCount++;
            return true;
        } else {
            log_message("  ❌ 无法写入文件");
            $errorCount++;
            return false;
        }
    }
    
    return false;
}

/**
 * 生成报告
 */
function generate_report() {
    global $fixCount, $errorCount, $backupCount, $reportFile, $backupDir;
    
    $report = "# 构造函数多余括号修复报告\n\n";
    $report .= "## 执行摘要\n\n";
    $report .= "- 执行时间: " . date('Y-m-d H:i:s') . "\n";
    $report .= "- 修复的文件数: $fixCount\n";
    $report .= "- 备份的文件数: $backupCount\n";
    $report .= "- 错误数: $errorCount\n";
    $report .= "- 备份目录: $backupDir\n\n";
    
    $report .= "## 修复的问题\n\n";
    $report .= "本工具修复了以下类型的问题：\n\n";
    $report .= "```php\n";
    $report .= "// 修改前 - 构造函数有多余的括号\n";
    $report .= "public function __construct((array \$config = [])) {\n";
    $report .= "    // ...\n";
    $report .= "}\n\n";
    $report .= "// 修改后 - 移除多余的括号\n";
    $report .= "public function __construct(array \$config = []) {\n";
    $report .= "    // ...\n";
    $report .= "}\n";
    $report .= "```\n\n";
    
    $report .= "## 后续步骤\n\n";
    $report .= "1. 验证修复后的文件是否正常工作\n";
    $report .= "2. 运行PHP语法检查，确保没有引入新的错误\n\n";
    
    $report .= "## 预防措施\n\n";
    $report .= "1. 使用IDE功能自动检测语法问题\n";
    $report .= "2. 在CI/CD流程中加入PHP语法检查\n";
    $report .= "3. 实施严格的代码审查流程\n";
    $report .= "4. 使用PHPStan或Psalm等静态分析工具\n";
    
    file_put_contents($reportFile, $report);
    log_message("\n报告已生成: $reportFile");
}

// 创建备份目录
if (!create_backup_dir()) {
    log_message("无法继续，退出程序");
    exit(1);
}

// 扫描目录查找PHP文件
log_message("开始扫描PHP文件...");
$directories = [
    'apps/ai-platform/Services',
    'apps/blockchain/Services',
    'apps/enterprise/Services',
    'apps/government/Services',
    'apps/security/Services',
    'ai-engines/nlp'
];

$phpFiles = [];
foreach ($directories as $directory) {
    if (is_dir($directory)) {
        $phpFiles = array_merge($phpFiles, find_php_files($directory));
    }
}

log_message("找到 " . count($phpFiles) . " 个PHP文件");

// 修复构造函数多余括号
log_message("\n开始修复构造函数多余括号...");
foreach ($phpFiles as $file) {
    fix_constructor_brackets($file);
}

// 生成报告
generate_report();

// 输出结果摘要
echo "\n=== 修复结果摘要 ===\n";
echo "修复的文件数: $fixCount\n";
echo "备份的文件数: $backupCount\n";
echo "错误数: $errorCount\n";
echo "备份目录: $backupDir\n";
echo "详细报告: $reportFile\n"; 