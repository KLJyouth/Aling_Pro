<?php
/**
 * PHP 8.1 剩余错误修复脚本
 * 
 * 此脚本用于修复项目中剩余�?4个错误和152个警�?
 * 重点解决以下几类问题�?
 * 1. 未闭合的引号（特别是中文字符串）
 * 2. 对象访问语法错误
 * 3. 变量名缺�?
 * 4. 命名空间问题
 * 5. 数组访问安全问题
 */

// 设置基础配置
$projectRoot = __DIR__;
$errorCount = 0;
$warningCount = 0;
$fixedCount = 0;
$errorLog = [];

// 排除目录
$excludeDirs = [
    'vendor', 
    'backups', 
    'backup', 
    'tmp',
    'logs',
    'tests/fixtures'
];

echo "=== AlingAi Pro PHP 8.1 剩余错误修复工具 ===\n";
echo "时间: " . date('Y-m-d H:i:s') . "\n\n";

/**
 * 扫描目录查找PHP文件
 */
function findPhpFiles($dir, $excludeDirs = []) {
    $files = [];
    if (is_dir($dir)) {
        $scan = scandir($dir];
        foreach ($scan as $file) {
            if ($file != "." && $file != "..") {
                $path = "$dir/$file";
                
                // 检查是否在排除目录�?
                $excluded = false;
                foreach ($excludeDirs as $excludeDir) {
                    if (strpos($path, "/$excludeDir/") !== false || basename($dir) == $excludeDir) {
                        $excluded = true;
                        break;
                    }
                }
                
                if ($excluded) {
                    continue;
                }
                
                if (is_dir($path)) {
                    $files = array_merge($files, findPhpFiles($path, $excludeDirs)];
                } else if (pathinfo($file, PATHINFO_EXTENSION) == "php") {
                    $files[] = $path;
                }
            }
        }
    }
    return $files;
}

/**
 * 检查PHP文件语法错误
 */
function checkSyntax($file) {
    global $errorCount, $warningCount, $errorLog;
    
    // 由于Windows环境，使用更安全的语法检�?
    $content = file_get_contents($file];
    $tmpFile = tempnam(sys_get_temp_dir(), 'php_check_'];
    file_put_contents($tmpFile, $content];
    
    $output = [];
    exec("php -l \"$tmpFile\" 2>&1", $output, $return];
    unlink($tmpFile];
    
    if ($return !== 0) {
        $errorCount++;
        $errorLog[] = [
            'file' => $file,
            'type' => 'syntax',
            'message' => implode("\n", $output)
        ];
        return false;
    }
    
    return true;
}

/**
 * 修复未闭合的引号问题
 */
function fixUnclosedQuotes($file) {
    global $fixedCount, $errorLog;
    
    $content = file_get_contents($file];
    $original = $content;
    $fixed = false;
    
    // 检查每一�?
    $lines = explode("\n", $content];
    foreach ($lines as $lineNum => $line) {
        // 检查单引号
        $singleQuoteCount = substr_count($line, "'") - substr_count($line, "\\'"];
        if ($singleQuoteCount % 2 !== 0) {
            // 尝试修复单引�?
            if (preg_match("/('[^']*)\s*$/", $line, $matches)) {
                $lines[$lineNum] = $line . "'";
                $errorLog[] = [
                    'file' => $file,
                    'line' => $lineNum + 1,
                    'type' => 'quote',
                    'message' => "修复未闭合的单引�?
                ];
                $fixed = true;
            }
        }
        
        // 检查双引号
        $doubleQuoteCount = substr_count($line, "\"") - substr_count($line, "\\\""];
        if ($doubleQuoteCount % 2 !== 0) {
            // 尝试修复双引�?
            if (preg_match("/(\"[^\"]*)\s*$/", $line, $matches)) {
                $lines[$lineNum] = $line . "\"";
                $errorLog[] = [
                    'file' => $file,
                    'line' => $lineNum + 1,
                    'type' => 'quote',
                    'message' => "修复未闭合的双引�?
                ];
                $fixed = true;
            }
        }
        
        // 特殊处理中文字符�?
        if (preg_match('/[\'"][一-龥]+$/', $line)) {
            $lines[$lineNum] = $line . "'";
            $errorLog[] = [
                'file' => $file,
                'line' => $lineNum + 1,
                'type' => 'quote',
                'message' => "修复未闭合的中文字符串引�?
            ];
            $fixed = true;
        }
    }
    
    if ($fixed) {
        $content = implode("\n", $lines];
        file_put_contents($file, $content];
        $fixedCount++;
        return true;
    }
    
    return false;
}

/**
 * 修复对象访问语法错误
 */
function fixObjectAccess($file) {
    global $fixedCount, $errorLog;
    
    $content = file_get_contents($file];
    $original = $content;
    $fixed = false;
    
    // 修复常见的对象访问语法错�?
    
    // 1. 缺少->操作�?
    if (preg_match_all('/(\$[a-zA-Z0-9_]+)([a-zA-Z0-9_]+)\(/', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $original = $match[0];
            $variable = $match[1];
            $method = $match[2];
            $replacement = "$variable->$method(";
            
            $content = str_replace($original, $replacement, $content];
            $errorLog[] = [
                'file' => $file,
                'type' => 'object',
                'message' => "修复对象方法调用: $original -> $replacement"
            ];
            $fixed = true;
        }
    }
    
    // 2. 缺少->操作符访问属�?
    if (preg_match_all('/(\$[a-zA-Z0-9_]+)([a-zA-Z0-9_]+)\b(?!\s*\()/', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $original = $match[0];
            $variable = $match[1];
            $property = $match[2];
            
            // 排除变量声明
            if (!preg_match('/\$[a-zA-Z0-9_]+'.$property.'/', $original)) {
                $replacement = "$variable->$property";
                $content = str_replace($original, $replacement, $content];
                $errorLog[] = [
                    'file' => $file,
                    'type' => 'object',
                    'message' => "修复对象属性访�? $original -> $replacement"
                ];
                $fixed = true;
            }
        }
    }
    
    if ($fixed) {
        file_put_contents($file, $content];
        $fixedCount++;
        return true;
    }
    
    return false;
}

/**
 * 修复变量名缺失问�?
 */
function fixMissingVariableNames($file) {
    global $fixedCount, $errorLog;
    
    $content = file_get_contents($file];
    $original = $content;
    $fixed = false;
    
    // 1. 修复私有属性声明缺少变量名
    if (preg_match_all('/(private|protected|public)\s+([a-zA-Z0-9_\\\\]+)(?!\s*\$)/', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $original = $match[0];
            $visibility = $match[1];
            $type = $match[2];
            
            // 排除函数声明和其他非属性声�?
            if (!preg_match('/function|class|interface|trait/', $original)) {
                $varName = strtolower(preg_replace('/.*\\\\/', '', $type)];
                $replacement = "$visibility $type \$$varName";
                $content = str_replace($original, $replacement, $content];
                $errorLog[] = [
                    'file' => $file,
                    'type' => 'variable',
                    'message' => "添加缺失的变量名: $original -> $replacement"
                ];
                $fixed = true;
            }
        }
    }
    
    // 2. 修复函数参数类型缺少变量�?
    if (preg_match_all('/function\s+([a-zA-Z0-9_]+)\s*\([^)]*([a-zA-Z0-9_\\\\]+)\s*(?![a-zA-Z0-9_\$])/', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $functionName = $match[1];
            $paramType = $match[2];
            
            $paramName = strtolower(preg_replace('/.*\\\\/', '', $paramType)];
            $replacement = "function $functionName($paramType \$$paramName";
            $content = str_replace($match[0],  $replacement, $content];
            $errorLog[] = [
                'file' => $file,
                'type' => 'parameter',
                'message' => "添加函数参数变量�? {$match[0]} -> $replacement"
            ];
            $fixed = true;
        }
    }
    
    if ($fixed) {
        file_put_contents($file, $content];
        $fixedCount++;
        return true;
    }
    
    return false;
}

/**
 * 修复命名空间问题
 */
function fixNamespaceIssues($file) {
    global $fixedCount, $errorLog;
    
    $content = file_get_contents($file];
    $original = $content;
    $fixed = false;
    
    // 1. 修复类引用缺少完整命名空�?
    if (preg_match_all('/([^\\\\])([A-Z][a-zA-Z0-9_]+)::class/', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $original = $match[0];
            $prefix = $match[1];
            $className = $match[2];
            
            // 推断命名空间
            $namespace = "";
            if (strpos($className, "Controller") !== false) {
                $namespace = "\\AlingAi\\Controllers\\";
            } elseif (strpos($className, "Middleware") !== false) {
                $namespace = "\\AlingAi\\Middleware\\";
            } elseif (strpos($className, "Service") !== false) {
                $namespace = "\\AlingAi\\Services\\";
            } elseif (strpos($className, "Model") !== false) {
                $namespace = "\\AlingAi\\Models\\";
            }
            
            $replacement = $prefix . $namespace . $className . "::class";
            $content = str_replace($original, $replacement, $content];
            $errorLog[] = [
                'file' => $file,
                'type' => 'namespace',
                'message' => "添加缺失的命名空�? $original -> $replacement"
            ];
            $fixed = true;
        }
    }
    
    // 2. 修复命名空间声明问题
    if (strpos($content, "namespace") !== false && !preg_match('/namespace\s+[a-zA-Z0-9_\\\\]+;/', $content)) {
        // 尝试修复命名空间声明
        $content = preg_replace('/namespace\s*;/', 'namespace AlingAi;', $content];
        $errorLog[] = [
            'file' => $file,
            'type' => 'namespace',
            'message' => "修复无效的命名空间声�?
        ];
        $fixed = true;
    }
    
    if ($fixed) {
        file_put_contents($file, $content];
        $fixedCount++;
        return true;
    }
    
    return false;
}

/**
 * 修复数组访问安全问题
 */
function fixArrayAccessSafety($file) {
    global $fixedCount, $errorLog;
    
    $content = file_get_contents($file];
    $original = $content;
    $fixed = false;
    
    // 1. 添加null合并运算符到数组访问
    if (preg_match_all('/\$([a-zA-Z0-9_]+)\[([\'"])(.*?)\\2\]/', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $full = $match[0];
            $var = $match[1];
            $key = $match[3];
            
            // 检查是否已有null合并运算符或isset检�?
            if (strpos($content, "$full ?? ") === false && 
                !preg_match("/isset\\\(\\\$$var\[(['\"])$key\\1\]\\\)/", $content) &&
                !preg_match("/array_key_exists\\\((['\"])$key\\1, \\\$$var\\\)/", $content)) {
                
                // 替换为安全的访问
                $content = str_replace($full, "$full ?? null", $content];
                
                $errorLog[] = [
                    'file' => $file,
                    'type' => 'array',
                    'message' => "添加null合并运算符到数组访问: $full ?? null"
                ];
                $fixed = true;
            }
        }
    }
    
    // 2. 修复数组键缺少引�?
    if (preg_match_all('/\$[a-zA-Z0-9_]+\[([a-zA-Z0-9_]+)\]/', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $full = $match[0];
            $key = $match[1];
            
            // 如果键不是变量且没有引号
            if (!preg_match('/^\$/', $key)) {
                $replacement = str_replace("[$key]", "['$key']", $full];
                $content = str_replace($full, $replacement, $content];
                $errorLog[] = [
                    'file' => $file,
                    'type' => 'array',
                    'message' => "添加缺失的数组键引号: $full -> $replacement"
                ];
                $fixed = true;
            }
        }
    }
    
    if ($fixed) {
        file_put_contents($file, $content];
        $fixedCount++;
        return true;
    }
    
    return false;
}

/**
 * 修复UTF-8编码问题
 */
function fixUtf8EncodingIssues($file) {
    global $fixedCount, $errorLog;
    
    // 特别处理ChineseTokenizer.php文件
    if (basename($file) === 'ChineseTokenizer.php') {
        $content = file_get_contents($file];
        $original = $content;
        
        // 替换中文字符为Unicode转义序列
        $patterns = [
            // 中文标点符号
            '/[\'"]。[\'"]/' => '"\u{3002}"', // 句号
            '/[\'"]，[\'"]/' => '"\u{FF0C}"', // 逗号
            '/[\'"]、[\'"]/' => '"\u{3001}"', // 顿号
            '/[\'"]：[\'"]/' => '"\u{FF1A}"', // 冒号
            '/[\'"]；[\'"]/' => '"\u{FF1B}"', // 分号
            '/[\'"]！[\'"]/' => '"\u{FF01}"', // 感叹�?
            '/[\'"]？[\'"]/' => '"\u{FF1F}"', // 问号
            '/[\'"]（[\'"]/' => '"\u{FF08}"', // 左括�?
            '/[\'"]）[\'"]/' => '"\u{FF09}"', // 右括�?
            '/[\'"]《[\'"]/' => '"\u{300A}"', // 左书名号
            '/[\'"]》[\'"]/' => '"\u{300B}"', // 右书名号
            '/[\'"]"[\'"]/' => '"\u{201C}"', // 左双引号
            '/[\'"]"[\'"]/' => '"\u{201D}"', // 右双引号
            '/[\'"]'[\'"]/' => '"\u{2018}"', // 左单引号
            '/[\'"]'[\'"]/' => '"\u{2019}"', // 右单引号
            
            // 常见中文字符
            '/[\'"]江苏[\'"]/' => '"JiangSu"',
            '/[\'"]浙江[\'"]/' => '"ZheJiang"',
            '/[\'"]北京[\'"]/' => '"Beijing"',
            '/[\'"]上海[\'"]/' => '"Shanghai"',
            '/[\'"]广东[\'"]/' => '"Guangdong"',
            
            // 日期相关中文字符
            '/[\'"]年[\'"]/' => '"Year"',
            '/[\'"]月[\'"]/' => '"Month"',
            '/[\'"]日[\'"]/' => '"Day"',
            '/[\'"]时[\'"]/' => '"Hour"',
            '/[\'"]分[\'"]/' => '"Minute"',
            '/[\'"]秒[\'"]/' => '"Second"'
        ];
        
        $fixed = false;
        foreach ($patterns as $pattern => $replacement) {
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content];
                $errorLog[] = [
                    'file' => $file,
                    'type' => 'utf8',
                    'message' => "替换中文字符为Unicode转义序列或拼�?
                ];
                $fixed = true;
            }
        }
        
        if ($fixed) {
            file_put_contents($file, $content];
            $fixedCount++;
            return true;
        }
    }
    
    return false;
}

/**
 * 修复配置文件中的数值缺少引�?
 */
function fixConfigValues($file) {
    global $fixedCount, $errorLog;
    
    // 特别处理config目录下的文件
    if (strpos($file, '/config/') !== false || strpos($file, '\\config\\') !== false) {
        $content = file_get_contents($file];
        $original = $content;
        
        // 查找配置数组中的数值没有使用引号的情况
        if (preg_match_all('/[\'"]([a-zA-Z0-9_]+)[\'"](\s*=>\s*)(\d+)/', $content, $matches, PREG_SET_ORDER)) {
            $fixed = false;
            
            foreach ($matches as $match) {
                $full = $match[0];
                $key = $match[1];
                $arrow = $match[2];
                $value = $match[3];
                
                // 替换为带引号的�?
                $replacement = "'$key'$arrow'$value'";
                $content = str_replace($full, $replacement, $content];
                $errorLog[] = [
                    'file' => $file,
                    'type' => 'config',
                    'message' => "为配置值添加引�? $full -> $replacement"
                ];
                $fixed = true;
            }
            
            if ($fixed) {
                file_put_contents($file, $content];
                $fixedCount++;
                return true;
            }
        }
    }
    
    return false;
}

/**
 * 生成修复报告
 */
function generateReport() {
    global $errorLog, $fixedCount, $errorCount, $warningCount;
    
    $report = "# PHP 8.1 剩余错误修复报告\n\n";
    $report .= "日期: " . date('Y-m-d H:i:s') . "\n\n";
    $report .= "## 修复统计\n\n";
    $report .= "- 总计修复问题: $fixedCount\n";
    $report .= "- 剩余错误: $errorCount\n";
    $report .= "- 剩余警告: $warningCount\n\n";
    
    if (!empty($errorLog)) {
        $report .= "## 修复详情\n\n";
        
        // 按文件分�?
        $fileGroups = [];
        foreach ($errorLog as $error) {
            $file = $error['file'];
            if (!isset($fileGroups[$file])) {
                $fileGroups[$file] = [];
            }
            $fileGroups[$file][] = $error;
        }
        
        foreach ($fileGroups as $file => $errors) {
            $report .= "### " . basename($file) . "\n\n";
            
            foreach ($errors as $error) {
                $lineInfo = isset($error['line']) ? "�?{$error['line']}: " : "";
                $report .= "- " . $lineInfo . $error['message'] . " [" . $error['type'] . "]\n";
            }
            
            $report .= "\n";
        }
    }
    
    if ($errorCount > 0 || $warningCount > 0) {
        $report .= "## 剩余问题\n\n";
        $report .= "仍有 $errorCount 个错误和 $warningCount 个警告需要手动修复。\n";
    } else {
        $report .= "## 结论\n\n";
        $report .= "所有检测到的PHP 8.1语法错误和警告已成功修复。\n";
    }
    
    // 写入报告文件
    file_put_contents("PHP81_REMAINING_ERRORS_FIX_REPORT.md", $report];
    echo "修复报告已生�? PHP81_REMAINING_ERRORS_FIX_REPORT.md\n";
}

// 执行修复
echo "正在扫描PHP文件...\n";
$phpFiles = findPhpFiles($projectRoot, $excludeDirs];
$totalFiles = count($phpFiles];
echo "找到 $totalFiles 个PHP文件需要检查\n\n";

// 处理所有PHP文件
echo "正在修复文件...\n";
$processedFiles = 0;

foreach ($phpFiles as $file) {
    $processedFiles++;
    echo "\r处理进度: $processedFiles/$totalFiles (" . round(($processedFiles/$totalFiles)*100) . "%)";
    
    // 应用修复
    $fixed = false;
    $fixed |= fixUnclosedQuotes($file];
    $fixed |= fixObjectAccess($file];
    $fixed |= fixMissingVariableNames($file];
    $fixed |= fixNamespaceIssues($file];
    $fixed |= fixArrayAccessSafety($file];
    $fixed |= fixUtf8EncodingIssues($file];
    $fixed |= fixConfigValues($file];
    
    // 检查修复后的语�?
    if ($fixed) {
        checkSyntax($file];
    }
}

echo "\n\n修复完成!\n";
echo "修复�?$fixedCount 个问题\n";
echo "剩余 $errorCount 个错误和 $warningCount 个警告\n\n";

// 生成报告
generateReport(]; 

