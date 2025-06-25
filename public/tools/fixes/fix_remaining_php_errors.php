<?php
/**
 * ȫ���PHP��������޸��ű� - ���հ�
 * Comprehensive PHP Error Detection and Fix Script - Final Version
 * 
 * �˽ű���ʶ���޸�AlingAi_pro��Ŀ�е�����ʣ��PHP����
 */

// ���û�������
$projectRoot = __DIR__;
$errorCount = 0;
$fixedCount = 0;
$errorLog = [];

// �ų�Ŀ¼
$excludeDirs = [
    'vendor', 
    'backups', 
    'backup', 
    'tmp',
    'logs',
    'tests/fixtures'
];

echo "=== AlingAi Pro PHP���������޸����� ===\n";
echo "ʱ��: " . date('Y-m-d H:i:s') . "\n\n";

/**
 * ɨ��Ŀ¼����PHP�ļ�
 */
function findPhpFiles($dir, $excludeDirs = []) {
    $files = [];
    if (is_dir($dir)) {
        $scan = scandir($dir];
        foreach ($scan as $file) {
            if ($file != "." && $file != "..") {
                $path = "$dir/$file";
                
                // ����Ƿ����ų�Ŀ¼��
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
 * ���PHP�ļ��﷨����
 */
function checkSyntax($file) {
    global $errorCount, $errorLog;
    
    // ����Windows������ʹ�ø���ȫ���﷨���
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
 * �޸�API�������еĴ���
 */
function fixApiControllers($file) {
    global $fixedCount, $errorLog;
    
    $content = file_get_contents($file];
    $original = $content;
    $fixed = false;
    
    // 1. �滻sendErrorΪsendErrorResponse
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
    
    // 2. ���ȱʧ�ķ���
    if ((strpos($content, 'extends BaseApiController') !== false || 
         strpos($content, 'extends SimpleBaseApiController') !== false) &&
        strpos($content, 'abstract class') === false) {
        
        // �������validateAuth����
        if (strpos($content, 'function validateAuth') === false) {
            $methodPosition = strrpos($content, '}'];
            if ($methodPosition !== false) {
                $validateAuthMethod = "\n\n    /**\n     * ��֤API�������֤��Ϣ\n     */\n    protected function validateAuth(\$request)\n    {\n        // �������л�ȡ����\n        \$token = \$this->getBearerToken() ?? \$request->getQueryParams()[\"token\"] ?? null;\n        \n        if (!\$token) {\n            throw new InvalidArgumentException(\"ȱ����֤����\"];\n        }\n        \n        // ��֤����\n        \$validation = \$this->security->validateJwtToken(\$token];\n        \n        if (!\$validation || !isset(\$validation[\"user_id\"])) {\n            throw new InvalidArgumentException(\"��Ч����֤����\"];\n        }\n        \n        return (int)\$validation[\"user_id\"];\n    }\n";
                $content = substr_replace($content, $validateAuthMethod, $methodPosition, 0];
                
                // ȷ������InvalidArgumentException��
                if (strpos($content, 'use InvalidArgumentException;') === false) {
                    $useStatementPos = strpos($content, 'use '];
                    if ($useStatementPos !== false) {
                        $nextLinePos = strpos($content, "\n", $useStatementPos];
                        if ($nextLinePos !== false) {
                            $content = substr_replace($content, "\nuse InvalidArgumentException;", $nextLinePos, 0];
                        }
                    } else {
                        // ���û��use��䣬��namespace֮�����
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
        
        // �������validateRequiredParams����
        if (strpos($content, 'function validateRequiredParams') === false) {
            $methodPosition = strrpos($content, '}'];
            if ($methodPosition !== false) {
                $validateParamsMethod = "\n\n    /**\n     * ��֤������������\n     */\n    protected function validateRequiredParams(array \$data, array \$params)\n    {\n        \$missing = [];\n        \n        foreach (\$params as \$param) {\n            if (!isset(\$data[\$param]) || (is_string(\$data[\$param]) && trim(\$data[\$param]) === \"\")) {\n                \$missing[] = \$param;\n            }\n        }\n        \n        if (!empty(\$missing)) {\n            throw new InvalidArgumentException(\"ȱ�ٱ������: \" . implode(\", \", \$missing)];\n        }\n    }\n";
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
    
    // 3. �޸�ȱ�ٵĺ�������ֵ
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
    
    // �����޸�
    if ($fixed) {
        file_put_contents($file, $content];
        return true;
    }
    
    return false;
}

/**
 * �޸�ȱ�ٵ����ú͵���
 */
function fixMissingImports($file) {
    global $fixedCount, $errorLog;
    
    $content = file_get_contents($file];
    $original = $content;
    $fixed = false;
    
    // ���ģʽ
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
            
            // �ҵ�namespace�����λ��
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
    
    // �����޸�
    if ($fixed) {
            file_put_contents($file, $content];
        return true;
    }
    
    return false;
}

/**
 * �޸�������ʰ�ȫ����
 */
function fixArrayAccess($file) {
    global $fixedCount, $errorLog;
    
    $content = file_get_contents($file];
    $original = $content;
    $fixed = false;
    
    // ��������Ǳ�ڵĲ���ȫ�������
    // 1. �������� $data['key'] ��ģʽ����ǰ��û��isset��array_key_exists���
    if (preg_match_all('/\$([a-zA-Z0-9_]+)\[([\'"])(.*?)\\2\]/', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $full = $match[0];
            $var = $match[1];
            $key = $match[3];
            
            // ����Ƿ�����null�ϲ��������isset���
            if (strpos($content, "$full ?? ") === false && 
                !preg_match("/isset\\\(\\\$$var\[(['\"])$key\\1\]\\\)/", $content) &&
                !preg_match("/array_key_exists\\\((['\"])$key\\1, \\\$$var\\\)/", $content)) {
                
                // �滻Ϊ��ȫ�ķ���
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
    
    // �����޸�
    if ($fixed) {
        file_put_contents($file, $content];
        return true;
    }
    
    return false;
}

/**
 * �޸�AuthMiddleware��������
 */
function fixAuthMiddlewareReferences($file) {
    global $fixedCount, $errorLog;
    
    $content = file_get_contents($file];
    $original = $content;
    
    // ���AuthMiddleware����
    if (strpos($content, 'AuthMiddleware') !== false && 
        strpos($content, 'AuthenticationMiddleware') === false && 
        basename($file) != 'AuthMiddleware.php') {
        
        // �������
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
    
    // �����޸�
    if ($content !== $original) {
            file_put_contents($file, $content];
        return true;
    }
    
    return false;
}

/**
 * �޸�δ�����������
 */
function fixUndefinedVariables($file) {
    global $fixedCount, $errorLog;
    
    $content = file_get_contents($file];
    $original = $content;
    $fixed = false;
    
    // 1. ��鳣����δ��ʼ������
    $commonVars = ['response', 'request', 'data', 'params', 'result', 'output', 'config'];
    
    foreach ($commonVars as $var) {
        // ���Һ������еı�������
        if (preg_match_all('/function\s+([a-zA-Z0-9_]+)\s*\([^)]*\)\s*{(?:[^{}]|(?R))*}/', $content, $functions, PREG_SET_ORDER)) {
            foreach ($functions as $function) {
                $functionBody = $function[0];
                $functionName = $function[1];
                
                // ������ʹ�õ�û�г�ʼ��
                if (preg_match("/\\$$var/", $functionBody) && 
                    !preg_match("/\\$$var\s*=|function\s+[a-zA-Z0-9_]+\s*\([^)]*\\$$var|foreach\s*\([^)]*\\$$var\s*\)/", $functionBody)) {
                    
                    // �ں�����ͷ��ʼ������
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
    
    // �����޸�
    if ($fixed) {
            file_put_contents($file, $content];
        return true;
    }
    
    return false;
}

/**
 * ����ȱʧ��AuthMiddleware
 */
function createAuthMiddleware() {
    global $fixedCount, $errorLog, $projectRoot;
    
    $middlewarePath = "$projectRoot/src/Middleware";
    $authMiddlewarePath = "$middlewarePath/AuthMiddleware.php";
    
    // ���Ŀ¼�Ƿ����
    if (!is_dir($middlewarePath)) {
        if (!mkdir($middlewarePath, 0777, true)) {
            echo "�޷�����Ŀ¼: $middlewarePath\n";
            return false;
        }
    }
    
    // ����ļ��Ƿ��Ѵ���
    if (!file_exists($authMiddlewarePath)) {
        $content = "<?php\n\nnamespace App\\Middleware;\n\nuse Psr\\Http\\Message\\ServerRequestInterface;\nuse Psr\\Http\\Message\\ResponseInterface;\nuse Psr\\Http\\Server\\MiddlewareInterface;\nuse Psr\\Http\\Server\\RequestHandlerInterface;\nuse App\\Services\\SecurityService;\n\n/**\n * ��֤�м�� - ���ݲ�\n * �̳���AuthenticationMiddleware���ṩ������\n */\nclass AuthMiddleware extends AuthenticationMiddleware\n{\n    /**\n     * ���캯��\n     */\n    public function __construct(SecurityService \$security)\n    {\n        parent::__construct(\$security];\n    }\n\n    /**\n     * ��������\n     */\n    public function process(ServerRequestInterface \$request, RequestHandlerInterface \$handler): ResponseInterface\n    {\n        return parent::process(\$request, \$handler];\n    }\n}\n";
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
 * �����޸�����
 */
function generateReport() {
    global $errorLog, $fixedCount, $errorCount;
    
    $report = "# AlingAi Pro PHP�����޸�����\n\n";
    $report .= "����: " . date('Y-m-d H:i:s') . "\n\n";
    $report .= "## �޸�ͳ��\n\n";
    $report .= "- �ܼ��޸�����: $fixedCount\n";
    $report .= "- ʣ�����: $errorCount\n\n";
    
    if (!empty($errorLog)) {
        $report .= "## �޸�����\n\n";
        
        // ���ļ�����
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
        $report .= "## ʣ�����\n\n";
        $report .= "���� $errorCount ��������Ҫ�ֶ��޸���\n";
    } else {
        $report .= "## ����\n\n";
        $report .= "���м�⵽��PHP�����ѳɹ��޸���\n";
    }
    
    // д�뱨���ļ�
    file_put_contents("PHP_ERRORS_FIX_FINAL_REPORT.md", $report];
    echo "�޸�����������: PHP_ERRORS_FIX_FINAL_REPORT.md\n";
}

// ִ���޸�
echo "����ɨ��PHP�ļ�...\n";
$phpFiles = findPhpFiles($projectRoot, $excludeDirs];
$totalFiles = count($phpFiles];
echo "�ҵ� $totalFiles ��PHP�ļ���Ҫ���\n\n";

// ����AuthMiddleware���ݲ�
echo "���ڴ���AuthMiddleware���ݲ�...\n";
createAuthMiddleware(];

// ��������PHP�ļ�
echo "�����޸��ļ�...\n";
$processedFiles = 0;

foreach ($phpFiles as $file) {
    $processedFiles++;
    echo "\r�������: $processedFiles/$totalFiles (" . round(($processedFiles/$totalFiles)*100) . "%)";
    
    // ����﷨
    if (!checkSyntax($file)) {
        continue;
    }
    
    // Ӧ���޸�
    $fixed = false;
    $fixed |= fixApiControllers($file];
    $fixed |= fixMissingImports($file];
    $fixed |= fixArrayAccess($file];
    $fixed |= fixAuthMiddlewareReferences($file];
    $fixed |= fixUndefinedVariables($file];
    
    if ($fixed) {
        // �ٴμ���﷨��ȷ���޸����������´���
        checkSyntax($file];
    }
}

echo "\n\n�޸����!\n";
echo "�޸��� $fixedCount ������\n";
echo "ʣ�� $errorCount ����Ҫ�ֶ�����Ĵ���\n\n";

// ���ɱ���
generateReport(];

