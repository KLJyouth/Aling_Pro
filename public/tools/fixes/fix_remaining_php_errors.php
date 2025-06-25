<?php
/**
 * 全面的PHP错误检测和修复脚本 - 最终版
 * Comprehensive PHP Error Detection and Fix Script - Final Version
 * 
 * 此脚本将识别并修复AlingAi_pro项目中的所有剩余PHP错误
 */

// 设置基础配置
$projectRoot = __DIR__;
$errorCount = 0;
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

echo "=== AlingAi Pro PHP错误检测与修复工具 ===\n";
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
                
                // 检查是否在排除目录中
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
    global $errorCount, $errorLog;
    
    // 由于Windows环境，使用更安全的语法检查
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
 * 修复API控制器中的错误
 */
function fixApiControllers($file) {
    global $fixedCount, $errorLog;
    
    $content = file_get_contents($file];
    $original = $content;
    $fixed = false;
    
    // 1. 替换sendError为sendErrorResponse
    if (strpos($content, 'sendError(') !== false && 
        strpos($content, 'function sendError') === false) {
        $content = str_replace('sendError(', 'sendErrorResponse(', $content];
        
        $errorLog[] = [
            'file' => $file,
            'type' => 'method',
            'message' => 'Replaced sendError() with sendErrorResponse()'
        ];
        $fixedCount++;
        $fixed = true;
    }
    
    // 2. 添加缺失的方法
    if ((strpos($content, 'extends BaseApiController') !== false || 
         strpos($content, 'extends SimpleBaseApiController') !== false) &&
        strpos($content, 'abstract class') === false) {
        
        // 检查和添加validateAuth方法
        if (strpos($content, 'function validateAuth') === false) {
            $methodPosition = strrpos($content, '}'];
            if ($methodPosition !== false) {
                $validateAuthMethod = "\n\n    /**\n     * 验证API请求的认证信息\n     */\n    protected function validateAuth(\$request)\n    {\n        // 从请求中获取令牌\n        \$token = \$this->getBearerToken() ?? \$request->getQueryParams()[\"token\"] ?? null;\n        \n        if (!\$token) {\n            throw new InvalidArgumentException(\"缺少认证令牌\"];\n        }\n        \n        // 验证令牌\n        \$validation = \$this->security->validateJwtToken(\$token];\n        \n        if (!\$validation || !isset(\$validation[\"user_id\"])) {\n            throw new InvalidArgumentException(\"无效的认证令牌\"];\n        }\n        \n        return (int)\$validation[\"user_id\"];\n    }\n";
                $content = substr_replace($content, $validateAuthMethod, $methodPosition, 0];
                
                // 确保导入InvalidArgumentException类
                if (strpos($content, 'use InvalidArgumentException;') === false) {
                    $useStatementPos = strpos($content, 'use '];
                    if ($useStatementPos !== false) {
                        $nextLinePos = strpos($content, "\n", $useStatementPos];
                        if ($nextLinePos !== false) {
                            $content = substr_replace($content, "\nuse InvalidArgumentException;", $nextLinePos, 0];
                        }
                    } else {
                        // 如果没有use语句，在namespace之后添加
                        $namespacePos = strpos($content, 'namespace '];
                        if ($namespacePos !== false) {
                            $endNamespacePos = strpos($content, ';', $namespacePos) + 1;
                            $content = substr_replace($content, "\n\nuse InvalidArgumentException;", $endNamespacePos, 0];
                        }
                    }
                }
                
                $errorLog[] = [
                    'file' => $file,
                    'type' => 'method',
                    'message' => 'Added missing validateAuth() method'
                ];
                $fixedCount++;
                $fixed = true;
            }
        }
        
        // 检查和添加validateRequiredParams方法
        if (strpos($content, 'function validateRequiredParams') === false) {
            $methodPosition = strrpos($content, '}'];
            if ($methodPosition !== false) {
                $validateParamsMethod = "\n\n    /**\n     * 验证必需的请求参数\n     */\n    protected function validateRequiredParams(array \$data, array \$params)\n    {\n        \$missing = [];\n        \n        foreach (\$params as \$param) {\n            if (!isset(\$data[\$param]) || (is_string(\$data[\$param]) && trim(\$data[\$param]) === \"\")) {\n                \$missing[] = \$param;\n            }\n        }\n        \n        if (!empty(\$missing)) {\n            throw new InvalidArgumentException(\"缺少必需参数: \" . implode(\", \", \$missing)];\n        }\n    }\n";
                $content = substr_replace($content, $validateParamsMethod, $methodPosition, 0];
                
                $errorLog[] = [
                    'file' => $file,
                    'type' => 'method',
                    'message' => 'Added missing validateRequiredParams() method'
                ];
                $fixedCount++;
                $fixed = true;
            }
        }
    }
    
    // 3. 修复缺少的函数返回值
    if (preg_match_all('/sendErrorResponse\([^;]*\];(?!\s*return)/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
        foreach (array_reverse($matches[0]) as $match) {
            $pos = $match[1] + strlen($match[0]];
            $content = substr_replace($content, "\n        return \$response->withStatus(400];", $pos, 0];
            
            $errorLog[] = [
                'file' => $file,
                'type' => 'function',
                'message' => 'Added missing return statement after sendErrorResponse()'
            ];
            $fixedCount++;
            $fixed = true;
        }
    }
    
    // 保存修改
    if ($fixed) {
        file_put_contents($file, $content];
        return true;
    }
    
    return false;
}

/**
 * 修复缺少的引用和导入
 */
function fixMissingImports($file) {
    global $fixedCount, $errorLog;
    
    $content = file_get_contents($file];
    $original = $content;
    $fixed = false;
    
    // 检查模式
    $patterns = [
        'InvalidArgumentException' => 'use InvalidArgumentException;',
        'LoggerInterface' => 'use Psr\Log\LoggerInterface;',
        'ServerRequestInterface' => 'use Psr\Http\Message\ServerRequestInterface;',
        'ResponseInterface' => 'use Psr\Http\Message\ResponseInterface;',
        'Throwable' => 'use Throwable;',
        'Exception' => 'use Exception;'
    ];
    
    foreach ($patterns as $class => $import) {
        if (strpos($content, $class) !== false && 
            strpos($content, $import) === false && 
            strpos($content, "class $class") === false && 
            strpos($content, "interface $class") === false) {
            
            // 找到namespace语句后的位置
            $namespacePos = strpos($content, 'namespace '];
            if ($namespacePos !== false) {
                $insertPos = strpos($content, ';', $namespacePos) + 1;
                $content = substr_replace($content, "\n\n$import", $insertPos, 0];
                
                $errorLog[] = [
                    'file' => $file,
                    'type' => 'class',
                    'message' => "Added missing import: $import"
                ];
                $fixedCount++;
                $fixed = true;
            }
        }
    }
    
    // 保存修改
    if ($fixed) {
            file_put_contents($file, $content];
        return true;
    }
    
    return false;
}

/**
 * 修复数组访问安全问题
 */
function fixArrayAccess($file) {
    global $fixedCount, $errorLog;
    
    $content = file_get_contents($file];
    $original = $content;
    $fixed = false;
    
    // 查找所有潜在的不安全数组访问
    // 1. 查找类似 $data['key'] 的模式，但前面没有isset或array_key_exists检查
    if (preg_match_all('/\$([a-zA-Z0-9_]+)\[([\'"])(.*?)\\2\]/', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $full = $match[0];
            $var = $match[1];
            $key = $match[3];
            
            // 检查是否已有null合并运算符或isset检查
            if (strpos($content, "$full ?? ") === false && 
                !preg_match("/isset\\\(\\\$$var\[(['\"])$key\\1\]\\\)/", $content) &&
                !preg_match("/array_key_exists\\\((['\"])$key\\1, \\\$$var\\\)/", $content)) {
                
                // 替换为安全的访问
                $content = str_replace($full, "$full ?? null", $content];
                
                $errorLog[] = [
                    'file' => $file,
                    'type' => 'key',
                    'message' => "Added null coalescing operator to array access: $full ?? null"
                ];
                $fixedCount++;
                $fixed = true;
            }
        }
    }
    
    // 保存修改
    if ($fixed) {
        file_put_contents($file, $content];
        return true;
    }
    
    return false;
}

/**
 * 修复AuthMiddleware引用问题
 */
function fixAuthMiddlewareReferences($file) {
    global $fixedCount, $errorLog;
    
    $content = file_get_contents($file];
    $original = $content;
    
    // 检查AuthMiddleware引用
    if (strpos($content, 'AuthMiddleware') !== false && 
        strpos($content, 'AuthenticationMiddleware') === false && 
        basename($file) != 'AuthMiddleware.php') {
        
        // 添加引用
        $namespacePos = strpos($content, 'namespace '];
        if ($namespacePos !== false) {
            $insertPos = strpos($content, ';', $namespacePos) + 1;
            $content = substr_replace($content, "\n\nuse App\\Middleware\\AuthenticationMiddleware as AuthMiddleware;", $insertPos, 0];
            
            $errorLog[] = [
                'file' => $file,
                'type' => 'class',
                'message' => "Added alias for AuthenticationMiddleware as AuthMiddleware"
            ];
            $fixedCount++;
        }
    }
    
    // 保存修改
    if ($content !== $original) {
            file_put_contents($file, $content];
        return true;
    }
    
    return false;
}

/**
 * 修复未定义变量访问
 */
function fixUndefinedVariables($file) {
    global $fixedCount, $errorLog;
    
    $content = file_get_contents($file];
    $original = $content;
    $fixed = false;
    
    // 1. 检查常见的未初始化变量
    $commonVars = ['response', 'request', 'data', 'params', 'result', 'output', 'config'];
    
    foreach ($commonVars as $var) {
        // 查找函数体中的变量引用
        if (preg_match_all('/function\s+([a-zA-Z0-9_]+)\s*\([^)]*\)\s*{(?:[^{}]|(?R))*}/', $content, $functions, PREG_SET_ORDER)) {
            foreach ($functions as $function) {
                $functionBody = $function[0];
                $functionName = $function[1];
                
                // 检查变量使用但没有初始化
                if (preg_match("/\\$$var/", $functionBody) && 
                    !preg_match("/\\$$var\s*=|function\s+[a-zA-Z0-9_]+\s*\([^)]*\\$$var|foreach\s*\([^)]*\\$$var\s*\)/", $functionBody)) {
                    
                    // 在函数开头初始化变量
                    $openBracePos = strpos($functionBody, '{') + 1;
                    $initCode = "\n        \$$var = [];\n";
                    $newFunctionBody = substr_replace($functionBody, $initCode, $openBracePos, 0];
                    $content = str_replace($functionBody, $newFunctionBody, $content];
                    
                    $errorLog[] = [
                        'file' => $file,
                        'type' => 'var',
                        'message' => "Initialized undefined variable \$$var in function $functionName"
                    ];
                    $fixedCount++;
                    $fixed = true;
                }
            }
        }
    }
    
    // 保存修改
    if ($fixed) {
            file_put_contents($file, $content];
        return true;
    }
    
    return false;
}

/**
 * 创建缺失的AuthMiddleware
 */
function createAuthMiddleware() {
    global $fixedCount, $errorLog, $projectRoot;
    
    $middlewarePath = "$projectRoot/src/Middleware";
    $authMiddlewarePath = "$middlewarePath/AuthMiddleware.php";
    
    // 检查目录是否存在
    if (!is_dir($middlewarePath)) {
        if (!mkdir($middlewarePath, 0777, true)) {
            echo "无法创建目录: $middlewarePath\n";
            return false;
        }
    }
    
    // 检查文件是否已存在
    if (!file_exists($authMiddlewarePath)) {
        $content = "<?php\n\nnamespace App\\Middleware;\n\nuse Psr\\Http\\Message\\ServerRequestInterface;\nuse Psr\\Http\\Message\\ResponseInterface;\nuse Psr\\Http\\Server\\MiddlewareInterface;\nuse Psr\\Http\\Server\\RequestHandlerInterface;\nuse App\\Services\\SecurityService;\n\n/**\n * 认证中间件 - 兼容层\n * 继承自AuthenticationMiddleware以提供兼容性\n */\nclass AuthMiddleware extends AuthenticationMiddleware\n{\n    /**\n     * 构造函数\n     */\n    public function __construct(SecurityService \$security)\n    {\n        parent::__construct(\$security];\n    }\n\n    /**\n     * 处理请求\n     */\n    public function process(ServerRequestInterface \$request, RequestHandlerInterface \$handler): ResponseInterface\n    {\n        return parent::process(\$request, \$handler];\n    }\n}\n";
        file_put_contents($authMiddlewarePath, $content];
        
        $errorLog[] = [
            'file' => $authMiddlewarePath,
            'type' => 'file',
            'message' => "Created compatibility AuthMiddleware class"
        ];
        $fixedCount++;
        return true;
    }
    
    return false;
}

/**
 * 生成修复报告
 */
function generateReport() {
    global $errorLog, $fixedCount, $errorCount;
    
    $report = "# AlingAi Pro PHP错误修复报告\n\n";
    $report .= "日期: " . date('Y-m-d H:i:s') . "\n\n";
    $report .= "## 修复统计\n\n";
    $report .= "- 总计修复错误: $fixedCount\n";
    $report .= "- 剩余错误: $errorCount\n\n";
    
    if (!empty($errorLog)) {
        $report .= "## 修复详情\n\n";
        
        // 按文件分组
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
                $report .= "- " . $error['message'] . " [" . $error['type'] . "]\n";
            }
            
            $report .= "\n";
        }
    }
    
    if ($errorCount > 0) {
        $report .= "## 剩余错误\n\n";
        $report .= "仍有 $errorCount 个错误需要手动修复。\n";
    } else {
        $report .= "## 结论\n\n";
        $report .= "所有检测到的PHP错误已成功修复。\n";
    }
    
    // 写入报告文件
    file_put_contents("PHP_ERRORS_FIX_FINAL_REPORT.md", $report];
    echo "修复报告已生成: PHP_ERRORS_FIX_FINAL_REPORT.md\n";
}

// 执行修复
echo "正在扫描PHP文件...\n";
$phpFiles = findPhpFiles($projectRoot, $excludeDirs];
$totalFiles = count($phpFiles];
echo "找到 $totalFiles 个PHP文件需要检查\n\n";

// 创建AuthMiddleware兼容层
echo "正在创建AuthMiddleware兼容层...\n";
createAuthMiddleware(];

// 处理所有PHP文件
echo "正在修复文件...\n";
$processedFiles = 0;

foreach ($phpFiles as $file) {
    $processedFiles++;
    echo "\r处理进度: $processedFiles/$totalFiles (" . round(($processedFiles/$totalFiles)*100) . "%)";
    
    // 检查语法
    if (!checkSyntax($file)) {
        continue;
    }
    
    // 应用修复
    $fixed = false;
    $fixed |= fixApiControllers($file];
    $fixed |= fixMissingImports($file];
    $fixed |= fixArrayAccess($file];
    $fixed |= fixAuthMiddlewareReferences($file];
    $fixed |= fixUndefinedVariables($file];
    
    if ($fixed) {
        // 再次检查语法，确保修复不会引入新错误
        checkSyntax($file];
    }
}

echo "\n\n修复完成!\n";
echo "修复了 $fixedCount 个错误\n";
echo "剩余 $errorCount 个需要手动处理的错误\n\n";

// 生成报告
generateReport(];

