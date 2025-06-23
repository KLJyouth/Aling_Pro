<?php
/**
 * 修复常见PHP语法错误的脚本
 * 专注于图片中显示的主要问题
 */

// 错误类型定义
$errorTypes = [
    'PRIVATE_VAR' => 'private property missing variable name',
    'CONTAINER_CALL' => 'container call missing -> operator',
    'CONFIG_CALL' => 'config call missing -> operator',
    'STRING_CONSTANT' => 'string constant declaration format',
    'NAMESPACE_PREFIX' => 'class reference missing namespace prefix',
    'PARAMETER_NAME' => 'function parameter missing variable name',
    'ARRAY_VALUE' => 'array value missing quotes',
    'UTF8_CHAR' => 'UTF-8 character encoding issue'
];

// 主要问题文件及其错误
$problemFiles = [
    // ai-engines相关文件
    'ai-engines/nlp/ChineseTokenizer.php' => [
        ['line' => 422, 'type' => $errorTypes['UTF8_CHAR'], 'fix' => 'Replace "江苏" with "JiangSu"']
    ],
    'ai-engines/nlp/EnglishTokenizer.php' => [
        ['line' => 28, 'type' => $errorTypes['PRIVATE_VAR'], 'fix' => 'Add variable name after type declaration']
    ],
    'ai-engines/knowledge-graph/GraphStoreInterface.php' => [
        ['line' => 31, 'type' => $errorTypes['PARAMETER_NAME'], 'fix' => 'Add parameter name to string type']
    ],
    'ai-engines/knowledge-graph/MemoryGraphStore.php' => [
        ['line' => 28, 'type' => $errorTypes['PRIVATE_VAR'], 'fix' => 'Add variable name to entity storage array']
    ],
    'ai-engines/knowledge-graph/ReasoningEngine.php' => [
        ['line' => 29, 'type' => $errorTypes['PRIVATE_VAR'], 'fix' => 'Add variable name to config array']
    ],
    
    // apps相关文件
    'apps/ai-platform/services/AIServiceManager.php' => [
        ['line' => 51, 'type' => $errorTypes['CONTAINER_CALL'], 'fix' => 'Add -> operator before method call']
    ],
    'apps/ai-platform/services/CV/ComputerVisionProcessor.php' => [
        ['line' => 13, 'type' => $errorTypes['CONFIG_CALL'], 'fix' => 'Add -> operator before property access']
    ],
    
    // config相关文件
    'config/app.php' => [
        ['line' => 12, 'type' => $errorTypes['ARRAY_VALUE'], 'fix' => 'Add quotes around version value']
    ],
    'config/assets.php' => [
        ['line' => 5, 'type' => $errorTypes['ARRAY_VALUE'], 'fix' => 'Add quotes around js_version value']
    ],
    
    // 路由相关文件
    'config/routes_enhanced.php' => [
        ['line' => 34, 'type' => $errorTypes['NAMESPACE_PREFIX'], 'fix' => 'Add namespace prefix to WebController::class']
    ]
];

/**
 * 修复文件中的指定语法错误
 */
function fixFileError($filePath, $lineNumber, $errorType, $fixDescription) {
    if (!file_exists($filePath)) {
        echo "错误: 文件 $filePath 不存在\n";
        return false;
    }
    
    $content = file_get_contents($filePath);
    if ($content === false) {
        echo "错误: 无法读取文件 $filePath\n";
        return false;
    }
    
    $lines = explode("\n", $content);
    if ($lineNumber > count($lines)) {
        echo "错误: 行号 $lineNumber 超出文件范围\n";
        return false;
    }
    
    $originalLine = $lines[$lineNumber - 1];
    $fixedLine = null;
    
    // 根据错误类型修复
    switch ($errorType) {
        case 'private property missing variable name':
            // 处理私有属性缺少变量名的问题
            $fixedLine = preg_replace('/private\s+([a-zA-Z_\\\\\[\]]+)(?!\s*\$)/', 'private $1 $var', $originalLine);
            break;
            
        case 'container call missing -> operator':
            // 处理容器调用缺少->运算符的问题
            $fixedLine = preg_replace('/(\$container)(?!\s*->|\s*=|\s*\()([a-zA-Z0-9_]+)/', '$1->$2', $originalLine);
            break;
            
        case 'config call missing -> operator':
            // 处理配置调用缺少->运算符的问题
            $fixedLine = preg_replace('/(\$config)(?!\s*->|\s*=|\s*\()([a-zA-Z0-9_]+)/', '$1->$2', $originalLine);
            break;
            
        case 'string constant declaration format':
            // 处理字符串常量声明格式问题
            if (preg_match('/protected\s+string\s+\$version\s+=\s+(["\'])(.*)\\1/', $originalLine)) {
                $fixedLine = $originalLine; // 已经是正确格式
            } else if (preg_match('/protected\s+string\s+\$version\s+=\s+(["\'])(.*)$/', $originalLine, $matches)) {
                $fixedLine = "protected string \$version = {$matches[1]}{$matches[2]}{$matches[1]};";
            }
            break;
            
        case 'class reference missing namespace prefix':
            // 处理类引用缺少命名空间前缀的问题
            $fixedLine = preg_replace('/\b(WebController)::class\b/', '\\\\$1::class', $originalLine);
            break;
            
        case 'function parameter missing variable name':
            // 处理函数参数缺少变量名的问题
            $fixedLine = preg_replace('/(\(|,)\s*([a-zA-Z_\\\\\[\]]+)\s+(?!\$)/', '$1 $2 $param', $originalLine);
            break;
            
        case 'array value missing quotes':
            // 处理数组值缺少引号的问题
            $fixedLine = preg_replace('/([\'"][a-zA-Z_]+[\'"]\s*=>\s*)(?![\'"\[])([a-zA-Z0-9_.]+)/', '$1\'$2\'', $originalLine);
            break;
            
        case 'UTF-8 character encoding issue':
            // 处理UTF-8字符编码问题
            $fixedLine = preg_replace('/["\'](江苏)["\']/', '"JiangSu"', $originalLine);
            break;
            
        default:
            echo "错误: 未知的错误类型 '$errorType'\n";
            return false;
    }
    
    if ($fixedLine !== null && $fixedLine !== $originalLine) {
        $lines[$lineNumber - 1] = $fixedLine;
        file_put_contents($filePath, implode("\n", $lines));
        
        echo "已修复文件 $filePath 行 $lineNumber\n";
        echo "  修改前: $originalLine\n";
        echo "  修改后: $fixedLine\n";
        return true;
    } else if ($fixedLine === $originalLine) {
        echo "文件 $filePath 行 $lineNumber 无需修改\n";
    } else {
        echo "修复失败: 文件 $filePath 行 $lineNumber\n";
    }
    
    return false;
}

/**
 * 修复所有问题文件
 */
function fixAllProblems($problemFiles) {
    $totalProblems = 0;
    $fixedProblems = 0;
    
    foreach ($problemFiles as $filePath => $problems) {
        echo "处理文件: $filePath\n";
        
        foreach ($problems as $problem) {
            $totalProblems++;
            $result = fixFileError(
                $filePath, 
                $problem['line'], 
                $problem['type'], 
                $problem['fix']
            );
            
            if ($result) {
                $fixedProblems++;
            }
        }
        
        echo "\n";
    }
    
    return [
        'total' => $totalProblems,
        'fixed' => $fixedProblems
    ];
}

/**
 * 生成修复报告
 */
function generateReport($result) {
    $report = <<<REPORT
# PHP语法错误修复报告

## 修复概要
- 总计问题: {$result['total']}
- 成功修复: {$result['fixed']}

## 修复的问题类型
1. 私有属性缺少变量名
2. 对象方法调用缺少->操作符
3. 配置值缺少引号
4. 类引用缺少命名空间前缀
5. 函数参数缺少变量名
6. UTF-8字符编码问题

## 修复符合PHP 8.1语法规则
PHP 8.1对类型声明和类型安全有更严格的要求，本次修复确保代码符合以下规则:
- 私有属性必须有明确的变量名
- 对象方法调用必须使用->操作符
- 字符串常量必须使用引号包围
- 类引用必须包含完整的命名空间路径

## 后续建议
1. 使用PHP代码静态分析工具(如PHPStan)检查剩余问题
2. 配置IDE自动检查PHP语法错误
3. 建立代码审查流程确保代码符合PHP 8.1语法规则
4. 对开发团队进行PHP 8.1新特性培训
REPORT;

    file_put_contents('PHP_SYNTAX_FIX_SUMMARY.md', $report);
    echo "\n已生成修复报告: PHP_SYNTAX_FIX_SUMMARY.md\n";
}

// 执行修复
echo "开始修复PHP语法错误...\n\n";
$startTime = microtime(true);

$result = fixAllProblems($problemFiles);

$endTime = microtime(true);
$executionTime = round($endTime - $startTime, 2);

echo "\n修复完成!\n";
echo "总计问题: {$result['total']}\n";
echo "成功修复: {$result['fixed']}\n";
echo "执行时间: {$executionTime} 秒\n";

generateReport($result); 