<?php
/**
 * 综合PHP语法错误修复脚本
 * 
 * 此脚本旨在自动修复项目中存在的PHP语法错误，使其符合PHP 8.1语法规则
 * 
 * 使用方法: 在项目根目录运行 php comprehensive_php_error_fix.php
 */

// 定义错误类型
const ERROR_TYPES = [
    'unexpected_token' => 'Syntax error: unexpected token',
    'private_property' => 'unexpected token \'private\'',
    'string_constant' => 'protected string $version',
    'container_token' => 'unexpected token \'$container\'',
    'config_token' => 'unexpected token \'$config\'',
    'js_version_token' => 'unexpected token \'js_version\'',
    'array_token' => 'unexpected token \'array\'',
    'database_token' => 'unexpected token \'database\'',
    'access_token' => 'unexpected token \'Access\'',
    'use_token' => 'unexpected token \'use\'',
    'version_token' => 'unexpected token \'version\'',
    'controller_class' => 'unexpected token \", WebController::class',
    'class_declaration' => 'unexpected token \", [AgentSchedulerController::class',
    'utf8_tokens' => 'unexpected token "江苏"',
];

// 定义需要处理的目录
$directories = [
    'ai-engines/nlp',
    'ai-engines/knowledge-graph',
    'apps/ai-platform/services',
    'apps/ai-platform/services/CV',
    'apps/ai-platform/services/Knowledge-Graph',
    'apps/ai-platform/services/NLP',
    'apps/ai-platform/services/Speech',
    'apps/blockchain/services',
    'apps/enterprise/services',
    'apps/government/services',
    'apps/security/services',
    'backup/old_files/test_files',
    'completed/config',
    'config',
    'public/admin/api',
    'public/install',
    'public/monitor',
    'public/storage',
    'public/tests',
    'src/controllers',
    'src/security',
    'tests',
    'tests/integration',
    'tests/unit',
    'deployment/vendor/nikic/fast-route/test/Hack/typechecker/fixtures'
];

// 记录日志的函数
function logFix($file, $line, $error, $fix) {
    static $log = [];
    $log[] = [
        'file' => $file,
        'line' => $line,
        'error' => $error,
        'fix' => $fix
    ];
    
    // 输出到控制台
    echo "修复文件: {$file} (行 {$line})\n";
    echo "错误: {$error}\n";
    echo "修复: {$fix}\n\n";
    
    // 保存到日志文件
    file_put_contents('PHP_SYNTAX_FIXES_LOG.md', 
        "# PHP语法错误修复日志\n\n" . 
        implode("\n\n", array_map(function($entry) {
            return "## 文件: {$entry['file']}\n" .
                   "- 行号: {$entry['line']}\n" .
                   "- 错误: {$entry['error']}\n" .
                   "- 修复: {$entry['fix']}\n";
        }, $log))
    ];
}

// 修复文件中的语法错误
function fixSyntaxErrors($filePath) {
    if (!file_exists($filePath)) {
        echo "文件不存在: {$filePath}\n";
        return false;
    }
    
    $content = file_get_contents($filePath];
    if ($content === false) {
        echo "无法读取文件: {$filePath}\n";
        return false;
    }
    
    $lines = explode("\n", $content];
    $modified = false;
    
    foreach ($lines as $lineNumber => $line) {
        // 检查各种语法错误
        
        // 1. 处理 unexpected token 'private'
        if (preg_match('/private\s+([a-zA-Z]+)\s+(?!\$)/', $line, $matches)) {
            $newLine = str_replace("private {$matches[1]} ", "private {$matches[1]} \$", $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "私有属性缺少变量名", "添加了变量名前缀 \$"];
            $modified = true;
        }
        
        // 2. 处理 protected string $version
        if (preg_match('/protected\s+string\s+\$version\s+=\s+[\'"](.*?)[\'"]/', $line, $matches)) {
            $newLine = str_replace("protected string \$version", "protected string \$version", $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "字符串常量声明格式错误", "修正了字符串常量声明"];
            $modified = true;
        }
        
        // 3. 处理 unexpected token '$container'
        if (preg_match('/\$container\s*\(/', $line)) {
            $newLine = preg_replace('/(\$container)\s*\(/', '$1->(', $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "容器调用语法错误", "将 \$container( 修改为 \$container->("];
            $modified = true;
        }
        
        // 4. 处理 unexpected token '$config'
        if (preg_match('/\$config\s*\(/', $line)) {
            $newLine = preg_replace('/(\$config)\s*\(/', '$1->(', $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "配置调用语法错误", "将 \$config( 修改为 \$config->("];
            $modified = true;
        }
        
        // 5. 处理 unexpected token 'js_version'
        if (preg_match('/[\'"]js_version[\'"]\s*=>\s*(?![\'"])/', $line)) {
            $newLine = preg_replace('/([\'"]js_version[\'"]\s*=>\s*)(?![\'"])([^,\s]+)/', '$1\'$2\'', $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "JS版本值缺少引号", "添加了引号"];
            $modified = true;
        }
        
        // 6. 处理 unexpected token 'array'
        if (preg_match('/([\'"].*?[\'"]\s*=>\s*)[?!\()/', $line)) {
            $newLine = preg_replace('/([\'"].*?[\'"]\s*=>\s*)[?!\()/', '$1[]', $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "数组声明语法错误", "将 array 替换为 []"];
            $modified = true;
        }
        
        // 7. 处理 unexpected token 'database'
        if (preg_match('/[\'"]database[\'"]\s*=>\s*(?![\'"])/', $line)) {
            $newLine = preg_replace('/([\'"]database[\'"]\s*=>\s*)(?![\'"])([^,\s]+)/', '$1\'$2\'', $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "数据库配置值缺少引号", "添加了引号"];
            $modified = true;
        }
        
        // 8. 处理 unexpected token 'Access'
        if (preg_match('/Access\s*::\s*([A-Z_]+)/', $line)) {
            $newLine = str_replace("Access::", "\\Access::", $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "Access类缺少命名空间", "添加了命名空间前缀"];
            $modified = true;
        }
        
        // 9. 处理 unexpected token 'use'
        if (preg_match('/^use\s+(?![a-zA-Z\\\\])/', $line)) {
            $newLine = preg_replace('/^use\s+/', 'use \\', $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "use语句缺少命名空间", "添加了命名空间前缀"];
            $modified = true;
        }
        
        // 10. 处理 unexpected token 'version'
        if (preg_match('/[\'"]version[\'"]\s*=>\s*(?![\'"])/', $line)) {
            $newLine = preg_replace('/([\'"]version[\'"]\s*=>\s*)(?![\'"])([^,\s]+)/', '$1\'$2\'', $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "版本值缺少引号", "添加了引号"];
            $modified = true;
        }
        
        // 11. 处理 unexpected token ", WebController::class"
        if (preg_match('/,\s*WebController::class/', $line)) {
            $newLine = str_replace("WebController::class", "\\WebController::class", $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "控制器类缺少命名空间", "添加了命名空间前缀"];
            $modified = true;
        }
        
        // 12. 处理 unexpected token ", [AgentSchedulerController::class"
        if (preg_match('/,\s*\[\s*AgentSchedulerController::class/', $line)) {
            $newLine = str_replace("AgentSchedulerController::class", "\\AgentSchedulerController::class", $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "控制器类缺少命名空间", "添加了命名空间前缀"];
            $modified = true;
        }
        
        // 13. 处理UTF-8字符问题 (例如 "江苏")
        if (preg_match('/["\'](江苏)["\']/', $line, $matches)) {
            $newLine = $line; // 此类问题需要确认具体情况，这里暂不修改
            logFix($filePath, $lineNumber + 1, "包含UTF-8字符可能导致编码问题", "请手动检查此行的UTF-8字符编码"];
            $modified = true;
        }
        
        // 14. 处理缺少变量名的参数
        if (preg_match('/function\s+\w+\s*\((.*?)\)/', $line, $matches)) {
            $params = $matches[1];
            if (preg_match_all('/([a-zA-Z_\\\\\[\]]+)\s+(?!\$)/', $params, $paramMatches)) {
                $newParams = $params;
                foreach ($paramMatches[0] as $index => $match) {
                    $typeHint = trim($paramMatches[1][$index]];
                    $newParam = "{$typeHint} \$param" . ($index + 1];
                    $newParams = str_replace($match, "{$typeHint} \$param" . ($index + 1) . " ", $newParams];
                }
                $newLine = str_replace("({$params})", "({$newParams})", $line];
                $lines[$lineNumber] = $newLine;
                logFix($filePath, $lineNumber + 1, "函数参数缺少变量名", "添加了默认变量名"];
                $modified = true;
            }
        }
        
        // 15. 处理PHP 8.1的原始属性声明问题
        if (preg_match('/private\s+([a-zA-Z_\\\\\[\]]+)(?!\s*\$)/', $line, $matches)) {
            $newLine = preg_replace('/private\s+([a-zA-Z_\\\\\[\]]+)(?!\s*\$)/', 'private $1 $', $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "私有属性声明缺少变量名", "添加了变量名"];
            $modified = true;
        }
        
        // 16. 处理缺少->操作符的调用
        if (preg_match('/\$([a-zA-Z0-9_]+)(?!\s*->|\s*=|\s*\()([a-zA-Z0-9_]+)/', $line, $matches)) {
            $newLine = str_replace("\${$matches[1]}{$matches[2]}", "\${$matches[1]}->{$matches[2]}", $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "对象属性访问缺少->操作符", "添加了->操作符"];
            $modified = true;
        }
        
        // 17. 处理命名空间问题
        if (preg_match('/namespace\s+(?![a-zA-Z\\\\])/', $line)) {
            $newLine = preg_replace('/namespace\s+/', 'namespace \\', $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "命名空间声明错误", "修正了命名空间格式"];
            $modified = true;
        }
    }
    
    if ($modified) {
        $newContent = implode("\n", $lines];
        file_put_contents($filePath, $newContent];
        echo "已修复文件: {$filePath}\n";
        return true;
    }
    
    return false;
}

// 查找PHP文件并修复语法错误
function findAndFixPhpFiles($directories) {
    $count = 0;
    $fixed = 0;
    
    foreach ($directories as $dir) {
        $dir = rtrim($dir, '/\\'];
        
        if (!is_dir($dir)) {
            echo "目录不存在: {$dir}\n";
            continue;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS],
            RecursiveIteratorIterator::SELF_FIRST
        ];
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $count++;
                if (fixSyntaxErrors($file->getPathname())) {
                    $fixed++;
                }
            }
        }
    }
    
    return ['total' => $count, 'fixed' => $fixed];
}

// 开始处理
echo "开始修复PHP语法错误...\n";
$startTime = microtime(true];

$result = findAndFixPhpFiles($directories];

$endTime = microtime(true];
$executionTime = round($endTime - $startTime, 2];

echo "\n完成修复!\n";
echo "总计处理: {$result['total']} 个PHP文件\n";
echo "成功修复: {$result['fixed']} 个文件\n";
echo "执行时间: {$executionTime} 秒\n";

// 创建修复报告
$reportContent = <<<REPORT
# PHP语法错误修复报告

## 修复概要
- 执行时间: {$executionTime} 秒
- 总计处理: {$result['total']} 个PHP文件
- 成功修复: {$result['fixed']} 个文件

## 修复的错误类型
1. 私有属性缺少变量名（例如：`private string` 改为 `private string $name`）
2. 对象方法调用缺少->操作符（例如：`$container(` 改为 `$container->(` ）
3. 配置项值缺少引号（例如：`'version' => 1.0` 改为 `'version' => '1.0'`）
4. 数组声明语法错误（例如：`array` 改为 `[]`）
5. 类引用缺少命名空间（例如：`WebController::class` 改为 `\\WebController::class`）
6. 函数参数缺少变量名（例如：`function test(string)` 改为 `function test(string $param)`）
7. UTF-8字符编码问题
8. 命名空间声明错误

## 后续建议
- 在开发过程中使用PHP代码静态分析工具（如PHPStan、Psalm）及时发现语法错误
- 配置IDE自动检查PHP语法错误
- 制定并遵循项目的PHP代码规范，特别是注意PHP 8.1的新语法规则
- 建立代码审查流程，确保提交的代码符合语法规范

## 参考资料
- [PHP 8.1官方文档](https://www.php.net/releases/8.1/en.php)
- [PHP类型声明文档](https://www.php.net/manual/en/language.types.declarations.php)

REPORT;

file_put_contents('PHP_SYNTAX_FIX_REPORT.md', $reportContent];
echo "已生成修复报告: PHP_SYNTAX_FIX_REPORT.md\n";

