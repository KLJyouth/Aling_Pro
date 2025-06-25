<?php
/**
 * 接口实现检查工�?
 * 
 * 此脚本用于检查项目中的类是否完全实现了它们声明实现的接口
 * 特别关注TokenizerInterface接口的实�?
 */

// 设置基础配置
$projectRoot = __DIR__;
$errorCount = 0;
$classCount = 0;
$interfaceCount = 0;
$errorLog = [];
$detailedMode = in_['--detailed', $argv) || in_['-d', $argv];
$autoFixMode = in_['--auto-fix', $argv) || in_['-f', $argv];
$backupMode = in_['--backup', $argv) || in_['-b', $argv];

// 排除目录
$excludeDirs = [
    'vendor', 
    'backups', 
    'backup', 
    'tmp',
    'logs',
    'tests/fixtures'
];

// 接口缓存
$interfaceCache = [];

// 日志文件
$logFile = "interface_check_" . date("Ymd_His") . ".log";
$reportFile = "INTERFACE_CHECK_REPORT_" . date("Ymd_His") . ".md";

echo "=== AlingAi Pro 接口实现检查工�?===\n";
echo "时间: " . date('Y-m-d H:i:s') . "\n\n";

/**
 * 写入日志
 */
function log_message($message) {
    global $logFile;
    echo $message . "\n";
    file_put_contents($logFile, $message . "\n", FILE_APPEND];
}

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
 * 解析PHP文件，获取类和接口信�?
 */
function parsePhpFile($file) {
    $content = file_get_contents($file];
    $tokens = token_get_all($content];
    
    $namespace = '';
    $className = null;
    $implementsList = [];
    $inClass = false;
    $classStartLine = 0;
    $methods = [];
    $currentMethod = null;
    $currentMethodStartLine = 0;
    $currentMethodEndLine = 0;
    $braceLevel = 0;
    
    $lineNumber = 1;
    
    foreach ($tokens as $i => $token) {
        if (is_[$token)) {
            list($id, $text) = $token;
            $lineNumber += substr_count($text, "\n"];
            
            switch ($id) {
                case T_NAMESPACE:
                    // 获取命名空间
                    $namespace = '';
                    for ($j = $i + 1; $j < count($tokens]; $j++) {
                        if (is_[$tokens[$j])) {
                            if ($tokens[$j][0] === T_STRING || $tokens[$j][0] === T_NS_SEPARATOR) {
                                $namespace .= $tokens[$j][1];
                            }
                        } else if ($tokens[$j] === ';') {
                            break;
                        }
                    }
                    break;
                    
                case T_CLASS:
                case T_INTERFACE:
                    // 获取类名或接口名
                    for ($j = $i + 1; $j < count($tokens]; $j++) {
                        if (is_[$tokens[$j]) && $tokens[$j][0] === T_STRING) {
                            $className = $tokens[$j][1];
                            $classStartLine = $lineNumber;
                            $inClass = true;
                            break;
                        }
                    }
                    break;
                    
                case T_IMPLEMENTS:
                    // 获取实现的接口列�?
                    for ($j = $i + 1; $j < count($tokens]; $j++) {
                        if (is_[$tokens[$j])) {
                            if ($tokens[$j][0] === T_STRING || $tokens[$j][0] === T_NS_SEPARATOR) {
                                $implementsList[] = $tokens[$j][1];
                            }
                        } else if ($tokens[$j] === '{') {
                            break;
                        }
                    }
                    break;
                    
                case T_FUNCTION:
                    // 获取方法信息
                    if ($inClass) {
                        for ($j = $i + 1; $j < count($tokens]; $j++) {
                            if (is_[$tokens[$j]) && $tokens[$j][0] === T_STRING) {
                                $currentMethod = $tokens[$j][1];
                                $currentMethodStartLine = $lineNumber;
                                
                                // 获取方法参数
                                $params = [];
                                $returnType = null;
                                
                                // 查找参数列表
                                $inParams = false;
                                $paramName = '';
                                $paramType = '';
                                $paramDefault = null;
                                
                                for ($k = $j + 1; $k < count($tokens]; $k++) {
                                    if ($tokens[$k] === '(') {
                                        $inParams = true;
                                    } else if ($tokens[$k] === ')') {
                                        if ($paramName) {
                                            $params[] = [
                                                'name' => $paramName,
                                                'type' => $paramType,
                                                'default' => $paramDefault
                                            ];
                                        }
                                        $inParams = false;
                                    } else if ($inParams) {
                                        if (is_[$tokens[$k])) {
                                            switch ($tokens[$k][0]) {
                                                case T_VARIABLE:
                                                    if ($paramName && $paramName !== $tokens[$k][1]) {
                                                        $params[] = [
                                                            'name' => $paramName,
                                                            'type' => $paramType,
                                                            'default' => $paramDefault
                                                        ];
                                                        $paramType = '';
                                                        $paramDefault = null;
                                                    }
                                                    $paramName = $tokens[$k][1];
                                                    break;
                                                case T_STRING:
                                                case T_ARRAY:
                                                case T_NS_SEPARATOR:
                                                    if (!$paramName) {
                                                        $paramType .= $tokens[$k][1];
                                                    }
                                                    break;
                                                case T_CONST:
                                                    $paramDefault = 'const';
                                                    break;
                                            }
                                        } else if ($tokens[$k] === ',') {
                                            if ($paramName) {
                                                $params[] = [
                                                    'name' => $paramName,
                                                    'type' => $paramType,
                                                    'default' => $paramDefault
                                                ];
                                                $paramName = '';
                                                $paramType = '';
                                                $paramDefault = null;
                                            }
                                        } else if ($tokens[$k] === '=') {
                                            // 有默认�?
                                            $paramDefault = 'has_default';
                                        }
                                    }
                                    
                                    // 查找返回类型
                                    if (!$inParams && is_[$tokens[$k]) && $tokens[$k][0] === T_COLON) {
                                        $returnTypeStr = '';
                                        for ($l = $k + 1; $l < count($tokens]; $l++) {
                                            if (is_[$tokens[$l])) {
                                                if ($tokens[$l][0] === T_STRING || $tokens[$l][0] === T_NS_SEPARATOR || 
                                                    $tokens[$l][0] === T_ARRAY) {
                                                    $returnTypeStr .= $tokens[$l][1];
                                                } else if ($tokens[$l][0] === T_WHITESPACE) {
                                                    continue;
                                                } else {
                                                    break;
                                                }
                                            } else {
                                                break;
                                            }
                                        }
                                        $returnType = $returnTypeStr;
                                        break;
                                    }
                                    
                                    if ($tokens[$k] === '{' || $tokens[$k] === ';') {
                                        break;
                                    }
                                }
                                
                                $methods[$currentMethod] = [
                                    'name' => $currentMethod,
                                    'params' => $params,
                                    'return_type' => $returnType,
                                    'start_line' => $currentMethodStartLine
                                ];
                                break;
                            }
                        }
                    }
                    break;
            }
        } else if ($token === '{') {
            $braceLevel++;
        } else if ($token === '}') {
            $braceLevel--;
            if ($braceLevel === 0 && $inClass) {
                $inClass = false;
            }
        }
    }
    
    return [
        'namespace' => $namespace,
        'className' => $className,
        'implements' => $implementsList,
        'methods' => $methods,
        'start_line' => $classStartLine
    ];
}

/**
 * 检查类是否完全实现了接�?
 */
function checkInterfaceImplementation($classInfo, $interfaceInfo) {
    $missingMethods = [];
    $incompatibleMethods = [];
    
    foreach ($interfaceInfo['methods'] as $methodName => $interfaceMethod) {
        if (!isset($classInfo['methods'][$methodName])) {
            $missingMethods[$methodName] = $interfaceMethod;
        } else {
            $classMethod = $classInfo['methods'][$methodName];
            
            // 检查参数数�?
            $interfaceParamCount = count($interfaceMethod['params']];
            $classParamCount = count($classMethod['params']];
            
            if ($classParamCount < $interfaceParamCount) {
                $incompatibleMethods[$methodName] = [
                    'reason' => "参数数量不匹�?(接口: $interfaceParamCount, �? $classParamCount)",
                    'interface_method' => $interfaceMethod,
                    'class_method' => $classMethod
                ];
                continue;
            }
            
            // 检查参数类�?
            $incompatible = false;
            for ($i = 0; $i < $interfaceParamCount; $i++) {
                $interfaceParam = $interfaceMethod['params'][$i];
                $classParam = $classMethod['params'][$i];
                
                // 如果接口参数有类型但类参数没有类型，则不兼容
                if ($interfaceParam['type'] && !$classParam['type']) {
                    $incompatible = true;
                    $incompatibleMethods[$methodName] = [
                        'reason' => "参数 {$interfaceParam['name']} 类型不兼�?(接口: {$interfaceParam['type']}, �? �?",
                        'interface_method' => $interfaceMethod,
                        'class_method' => $classMethod
                    ];
                    break;
                }
                
                // 如果接口参数有默认值但类参数没有默认值，则不兼容
                if ($interfaceParam['default'] && !$classParam['default']) {
                    $incompatible = true;
                    $incompatibleMethods[$methodName] = [
                        'reason' => "参数 {$interfaceParam['name']} 默认值不兼容 (接口: 有默认�? �? 无默认�?",
                        'interface_method' => $interfaceMethod,
                        'class_method' => $classMethod
                    ];
                    break;
                }
            }
            
            if ($incompatible) {
                continue;
            }
            
            // 检查返回类�?
            if ($interfaceMethod['return_type'] && $classMethod['return_type'] !== $interfaceMethod['return_type']) {
                $incompatibleMethods[$methodName] = [
                    'reason' => "返回类型不兼�?(接口: {$interfaceMethod['return_type']}, �? {$classMethod['return_type']})",
                    'interface_method' => $interfaceMethod,
                    'class_method' => $classMethod
                ];
            }
        }
    }
    
    return [
        'missing' => $missingMethods,
        'incompatible' => $incompatibleMethods
    ];
}

/**
 * 生成方法实现模板
 */
function generateMethodImplementation($methodInfo) {
    $params = [];
    foreach ($methodInfo['params'] as $param) {
        $paramStr = '';
        if ($param['type']) {
            $paramStr .= $param['type'] . ' ';
        }
        $paramStr .= $param['name'];
        if ($param['default']) {
            $paramStr .= ' = ' . ($param['default'] === 'const' ? 'null' : 'null'];
        }
        $params[] = $paramStr;
    }
    
    $returnType = $methodInfo['return_type'] ? ': ' . $methodInfo['return_type'] : '';
    
    $implementation = "    public function {$methodInfo['name']}(" . implode(', ', $params) . ")$returnType\n";
    $implementation .= "    {\n";
    $implementation .= "        // TODO: 实现 {$methodInfo['name']} 方法\n";
    
    // 根据返回类型添加默认返回�?
    if ($methodInfo['return_type']) {
        switch ($methodInfo['return_type']) {
            case 'array':
                $implementation .= "        return [];\n";
                break;
            case 'string':
                $implementation .= "        return '';\n";
                break;
            case 'int':
                $implementation .= "        return 0;\n";
                break;
            case 'float':
                $implementation .= "        return 0.0;\n";
                break;
            case 'bool':
            case 'boolean':
                $implementation .= "        return false;\n";
                break;
            case 'void':
                // 不需要返回�?
                break;
            default:
                if (strpos($methodInfo['return_type'],  '?') === 0) {
                    $implementation .= "        return null;\n";
                } else {
                    $implementation .= "        return null; // 注意: 返回类型 {$methodInfo['return_type']} 可能需要一个有效的对象\n";
                }
        }
    }
    
    $implementation .= "    }\n";
    
    return $implementation;
}

/**
 * 修复类的接口实现
 */
function fixInterfaceImplementation($file, $classInfo, $interfaceInfo, $implementationProblems) {
    global $backupMode;
    
    if ($backupMode) {
        $backupFile = $file . '.bak.' . date('YmdHis'];
        copy($file, $backupFile];
        log_message("已创建备�? $backupFile"];
    }
    
    $content = file_get_contents($file];
    $lines = explode("\n", $content];
    
    // 找到类的结束括号
    $classEndLine = 0;
    $braceLevel = 0;
    $inClass = false;
    
    for ($i = $classInfo['start_line']; $i < count($lines]; $i++) {
        $line = $lines[$i];
        
        if (strpos($line, '{') !== false) {
            $braceLevel++;
            $inClass = true;
        }
        
        if (strpos($line, '}') !== false) {
            $braceLevel--;
            if ($braceLevel === 0 && $inClass) {
                $classEndLine = $i;
                break;
            }
        }
    }
    
    if ($classEndLine === 0) {
        log_message("无法找到�?{$classInfo['className']} 的结束括�?];
        return false;
    }
    
    // 生成缺失的方法实�?
    $methodsToAdd = [];
    foreach ($implementationProblems['missing'] as $methodName => $methodInfo) {
        $methodsToAdd[] = generateMethodImplementation($methodInfo];
    }
    
    // 插入缺失的方�?
    if (!empty($methodsToAdd)) {
        $newContent = implode("\n", array_slice($lines, 0, $classEndLine)];
        $newContent .= "\n\n" . implode("\n", $methodsToAdd];
        $newContent .= "\n" . $lines[$classEndLine];
        $newContent .= "\n" . implode("\n", array_slice($lines, $classEndLine + 1)];
        
        file_put_contents($file, $newContent];
        log_message("已添加缺失的方法�?$file"];
        return true;
    }
    
    return false;
}

/**
 * 主函数：检查接口实�?
 */
function checkInterfaces() {
    global $projectRoot, $excludeDirs, $interfaceCache, $errorCount, $classCount, $interfaceCount, $errorLog, $detailedMode, $autoFixMode;
    
    // 查找所有PHP文件
    log_message("扫描项目中的PHP文件..."];
    $files = findPhpFiles($projectRoot, $excludeDirs];
    log_message("找到 " . count($files) . " 个PHP文件"];
    
    // 首先解析所有接�?
    log_message("解析接口定义..."];
    foreach ($files as $file) {
        $fileInfo = parsePhpFile($file];
        if ($fileInfo['className'] && strpos($fileInfo['className'],  'Interface') !== false) {
            $fullName = $fileInfo['namespace'] ? $fileInfo['namespace'] . '\\' . $fileInfo['className'] : $fileInfo['className'];
            $interfaceCache[$fileInfo['className']] = [
                'file' => $file,
                'namespace' => $fileInfo['namespace'], 
                'className' => $fileInfo['className'], 
                'methods' => $fileInfo['methods']
            ];
            $interfaceCount++;
        }
    }
    log_message("找到 $interfaceCount 个接�?];
    
    // 检查所有类的接口实�?
    log_message("检查类的接口实�?.."];
    foreach ($files as $file) {
        $fileInfo = parsePhpFile($file];
        if ($fileInfo['className'] && !empty($fileInfo['implements'])) {
            $classCount++;
            $className = $fileInfo['className'];
            $namespace = $fileInfo['namespace'];
            $fullClassName = $namespace ? $namespace . '\\' . $className : $className;
            
            log_message("检查类 $fullClassName 的接口实�?.."];
            
            foreach ($fileInfo['implements'] as $interface) {
                // 查找接口定义
                $interfaceInfo = null;
                foreach ($interfaceCache as $cachedInterface) {
                    if ($cachedInterface['className'] === $interface) {
                        $interfaceInfo = $cachedInterface;
                        break;
                    }
                }
                
                if ($interfaceInfo) {
                    $problems = checkInterfaceImplementation($fileInfo, $interfaceInfo];
                    
                    if (!empty($problems['missing']) || !empty($problems['incompatible'])) {
                        $errorCount++;
                        $errorLog[$file] = [
                            'class' => $fullClassName,
                            'interface' => $interface,
                            'problems' => $problems
                        ];
                        
                        log_message("  �?�?$fullClassName 没有完全实现接口 $interface"];
                        
                        if ($detailedMode) {
                            if (!empty($problems['missing'])) {
                                log_message("    缺失的方�?"];
                                foreach ($problems['missing'] as $methodName => $methodInfo) {
                                    log_message("      - $methodName"];
                                }
                            }
                            
                            if (!empty($problems['incompatible'])) {
                                log_message("    不兼容的方法:"];
                                foreach ($problems['incompatible'] as $methodName => $info) {
                                    log_message("      - $methodName: {$info['reason']}"];
                                }
                            }
                        }
                        
                        // 自动修复
                        if ($autoFixMode && !empty($problems['missing'])) {
                            if (fixInterfaceImplementation($file, $fileInfo, $interfaceInfo, $problems)) {
                                log_message("  �?已自动修复类 $fullClassName 的接口实�?];
                            } else {
                                log_message("  ⚠️ 无法自动修复�?$fullClassName 的接口实�?];
                            }
                        }
                    } else {
                        log_message("  �?�?$fullClassName 完全实现了接�?$interface"];
                    }
                } else {
                    log_message("  ⚠️ 找不到接�?$interface 的定�?];
                }
            }
        }
    }
}

/**
 * 生成报告
 */
function generateReport() {
    global $errorLog, $errorCount, $classCount, $interfaceCount, $reportFile;
    
    $report = "# 接口实现检查报告\n\n";
    $report .= "## 摘要\n\n";
    $report .= "- 检查时�? " . date('Y-m-d H:i:s') . "\n";
    $report .= "- 发现的接�? $interfaceCount\n";
    $report .= "- 检查的�? $classCount\n";
    $report .= "- 发现的问�? $errorCount\n\n";
    
    if ($errorCount > 0) {
        $report .= "## 详细问题\n\n";
        
        foreach ($errorLog as $file => $info) {
            $report .= "### �? {$info['class']}\n\n";
            $report .= "- 文件: `$file`\n";
            $report .= "- 未完全实现接�? `{$info['interface']}`\n\n";
            
            if (!empty($info['problems']['missing'])) {
                $report .= "#### 缺失的方法\n\n";
                foreach ($info['problems']['missing'] as $methodName => $methodInfo) {
                    $params = [];
                    foreach ($methodInfo['params'] as $param) {
                        $paramStr = '';
                        if ($param['type']) {
                            $paramStr .= $param['type'] . ' ';
                        }
                        $paramStr .= $param['name'];
                        if ($param['default']) {
                            $paramStr .= ' = default';
                        }
                        $params[] = $paramStr;
                    }
                    
                    $returnType = $methodInfo['return_type'] ? ': ' . $methodInfo['return_type'] : '';
                    
                    $report .= "- `$methodName(" . implode(', ', $params) . ")$returnType`\n";
                }
                $report .= "\n";
            }
            
            if (!empty($info['problems']['incompatible'])) {
                $report .= "#### 不兼容的方法\n\n";
                foreach ($info['problems']['incompatible'] as $methodName => $incompatibleInfo) {
                    $report .= "- `$methodName`: {$incompatibleInfo['reason']}\n";
                }
                $report .= "\n";
            }
        }
    } else {
        $report .= "## 恭喜！\n\n";
        $report .= "所有类都正确实现了它们声明的接口。\n\n";
    }
    
    $report .= "## 建议\n\n";
    
    if ($errorCount > 0) {
        $report .= "1. 为所有缺失的方法添加实现\n";
        $report .= "2. 修复不兼容的方法签名\n";
        $report .= "3. 使用IDE的接口实现功能来自动生成缺失的方法\n";
    }
    
    $report .= "4. 在开发过程中定期运行接口检查\n";
    $report .= "5. 考虑使用PHPStan或Psalm等静态分析工具来检测接口实现问题\n";
    
    file_put_contents($reportFile, $report];
    log_message("\n报告已生�? $reportFile"];
}

// 执行检�?
checkInterfaces(];

// 生成报告
generateReport(];

// 输出结果摘要
echo "\n=== 检查结果摘�?===\n";
echo "检查的接口: $interfaceCount\n";
echo "检查的�? $classCount\n";
echo "发现的问�? $errorCount\n";
echo "详细报告: $reportFile\n"; 

