<?php
/**
 * �ۺ�PHP�﷨�����޸��ű�
 * 
 * �˽ű�ּ���Զ��޸���Ŀ�д��ڵ�PHP�﷨����ʹ�����PHP 8.1�﷨����
 * 
 * ʹ�÷���: ����Ŀ��Ŀ¼���� php comprehensive_php_error_fix.php
 */

// �����������
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
    'utf8_tokens' => 'unexpected token "����"',
];

// ������Ҫ�����Ŀ¼
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

// ��¼��־�ĺ���
function logFix($file, $line, $error, $fix) {
    static $log = [];
    $log[] = [
        'file' => $file,
        'line' => $line,
        'error' => $error,
        'fix' => $fix
    ];
    
    // ���������̨
    echo "�޸��ļ�: {$file} (�� {$line})\n";
    echo "����: {$error}\n";
    echo "�޸�: {$fix}\n\n";
    
    // ���浽��־�ļ�
    file_put_contents('PHP_SYNTAX_FIXES_LOG.md', 
        "# PHP�﷨�����޸���־\n\n" . 
        implode("\n\n", array_map(function($entry) {
            return "## �ļ�: {$entry['file']}\n" .
                   "- �к�: {$entry['line']}\n" .
                   "- ����: {$entry['error']}\n" .
                   "- �޸�: {$entry['fix']}\n";
        }, $log))
    ];
}

// �޸��ļ��е��﷨����
function fixSyntaxErrors($filePath) {
    if (!file_exists($filePath)) {
        echo "�ļ�������: {$filePath}\n";
        return false;
    }
    
    $content = file_get_contents($filePath];
    if ($content === false) {
        echo "�޷���ȡ�ļ�: {$filePath}\n";
        return false;
    }
    
    $lines = explode("\n", $content];
    $modified = false;
    
    foreach ($lines as $lineNumber => $line) {
        // �������﷨����
        
        // 1. ���� unexpected token 'private'
        if (preg_match('/private\s+([a-zA-Z]+)\s+(?!\$)/', $line, $matches)) {
            $newLine = str_replace("private {$matches[1]} ", "private {$matches[1]} \$", $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "˽������ȱ�ٱ�����", "����˱�����ǰ׺ \$"];
            $modified = true;
        }
        
        // 2. ���� protected string $version
        if (preg_match('/protected\s+string\s+\$version\s+=\s+[\'"](.*?)[\'"]/', $line, $matches)) {
            $newLine = str_replace("protected string \$version", "protected string \$version", $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "�ַ�������������ʽ����", "�������ַ�����������"];
            $modified = true;
        }
        
        // 3. ���� unexpected token '$container'
        if (preg_match('/\$container\s*\(/', $line)) {
            $newLine = preg_replace('/(\$container)\s*\(/', '$1->(', $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "���������﷨����", "�� \$container( �޸�Ϊ \$container->("];
            $modified = true;
        }
        
        // 4. ���� unexpected token '$config'
        if (preg_match('/\$config\s*\(/', $line)) {
            $newLine = preg_replace('/(\$config)\s*\(/', '$1->(', $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "���õ����﷨����", "�� \$config( �޸�Ϊ \$config->("];
            $modified = true;
        }
        
        // 5. ���� unexpected token 'js_version'
        if (preg_match('/[\'"]js_version[\'"]\s*=>\s*(?![\'"])/', $line)) {
            $newLine = preg_replace('/([\'"]js_version[\'"]\s*=>\s*)(?![\'"])([^,\s]+)/', '$1\'$2\'', $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "JS�汾ֵȱ������", "���������"];
            $modified = true;
        }
        
        // 6. ���� unexpected token 'array'
        if (preg_match('/([\'"].*?[\'"]\s*=>\s*)[?!\()/', $line)) {
            $newLine = preg_replace('/([\'"].*?[\'"]\s*=>\s*)[?!\()/', '$1[]', $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "���������﷨����", "�� array �滻Ϊ []"];
            $modified = true;
        }
        
        // 7. ���� unexpected token 'database'
        if (preg_match('/[\'"]database[\'"]\s*=>\s*(?![\'"])/', $line)) {
            $newLine = preg_replace('/([\'"]database[\'"]\s*=>\s*)(?![\'"])([^,\s]+)/', '$1\'$2\'', $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "���ݿ�����ֵȱ������", "���������"];
            $modified = true;
        }
        
        // 8. ���� unexpected token 'Access'
        if (preg_match('/Access\s*::\s*([A-Z_]+)/', $line)) {
            $newLine = str_replace("Access::", "\\Access::", $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "Access��ȱ�������ռ�", "����������ռ�ǰ׺"];
            $modified = true;
        }
        
        // 9. ���� unexpected token 'use'
        if (preg_match('/^use\s+(?![a-zA-Z\\\\])/', $line)) {
            $newLine = preg_replace('/^use\s+/', 'use \\', $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "use���ȱ�������ռ�", "����������ռ�ǰ׺"];
            $modified = true;
        }
        
        // 10. ���� unexpected token 'version'
        if (preg_match('/[\'"]version[\'"]\s*=>\s*(?![\'"])/', $line)) {
            $newLine = preg_replace('/([\'"]version[\'"]\s*=>\s*)(?![\'"])([^,\s]+)/', '$1\'$2\'', $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "�汾ֵȱ������", "���������"];
            $modified = true;
        }
        
        // 11. ���� unexpected token ", WebController::class"
        if (preg_match('/,\s*WebController::class/', $line)) {
            $newLine = str_replace("WebController::class", "\\WebController::class", $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "��������ȱ�������ռ�", "����������ռ�ǰ׺"];
            $modified = true;
        }
        
        // 12. ���� unexpected token ", [AgentSchedulerController::class"
        if (preg_match('/,\s*\[\s*AgentSchedulerController::class/', $line)) {
            $newLine = str_replace("AgentSchedulerController::class", "\\AgentSchedulerController::class", $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "��������ȱ�������ռ�", "����������ռ�ǰ׺"];
            $modified = true;
        }
        
        // 13. ����UTF-8�ַ����� (���� "����")
        if (preg_match('/["\'](����)["\']/', $line, $matches)) {
            $newLine = $line; // ����������Ҫȷ�Ͼ�������������ݲ��޸�
            logFix($filePath, $lineNumber + 1, "����UTF-8�ַ����ܵ��±�������", "���ֶ������е�UTF-8�ַ�����"];
            $modified = true;
        }
        
        // 14. ����ȱ�ٱ������Ĳ���
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
                logFix($filePath, $lineNumber + 1, "��������ȱ�ٱ�����", "�����Ĭ�ϱ�����"];
                $modified = true;
            }
        }
        
        // 15. ����PHP 8.1��ԭʼ������������
        if (preg_match('/private\s+([a-zA-Z_\\\\\[\]]+)(?!\s*\$)/', $line, $matches)) {
            $newLine = preg_replace('/private\s+([a-zA-Z_\\\\\[\]]+)(?!\s*\$)/', 'private $1 $', $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "˽����������ȱ�ٱ�����", "����˱�����"];
            $modified = true;
        }
        
        // 16. ����ȱ��->�������ĵ���
        if (preg_match('/\$([a-zA-Z0-9_]+)(?!\s*->|\s*=|\s*\()([a-zA-Z0-9_]+)/', $line, $matches)) {
            $newLine = str_replace("\${$matches[1]}{$matches[2]}", "\${$matches[1]}->{$matches[2]}", $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "�������Է���ȱ��->������", "�����->������"];
            $modified = true;
        }
        
        // 17. ���������ռ�����
        if (preg_match('/namespace\s+(?![a-zA-Z\\\\])/', $line)) {
            $newLine = preg_replace('/namespace\s+/', 'namespace \\', $line];
            $lines[$lineNumber] = $newLine;
            logFix($filePath, $lineNumber + 1, "�����ռ���������", "�����������ռ��ʽ"];
            $modified = true;
        }
    }
    
    if ($modified) {
        $newContent = implode("\n", $lines];
        file_put_contents($filePath, $newContent];
        echo "���޸��ļ�: {$filePath}\n";
        return true;
    }
    
    return false;
}

// ����PHP�ļ����޸��﷨����
function findAndFixPhpFiles($directories) {
    $count = 0;
    $fixed = 0;
    
    foreach ($directories as $dir) {
        $dir = rtrim($dir, '/\\'];
        
        if (!is_dir($dir)) {
            echo "Ŀ¼������: {$dir}\n";
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

// ��ʼ����
echo "��ʼ�޸�PHP�﷨����...\n";
$startTime = microtime(true];

$result = findAndFixPhpFiles($directories];

$endTime = microtime(true];
$executionTime = round($endTime - $startTime, 2];

echo "\n����޸�!\n";
echo "�ܼƴ���: {$result['total']} ��PHP�ļ�\n";
echo "�ɹ��޸�: {$result['fixed']} ���ļ�\n";
echo "ִ��ʱ��: {$executionTime} ��\n";

// �����޸�����
$reportContent = <<<REPORT
# PHP�﷨�����޸�����

## �޸���Ҫ
- ִ��ʱ��: {$executionTime} ��
- �ܼƴ���: {$result['total']} ��PHP�ļ�
- �ɹ��޸�: {$result['fixed']} ���ļ�

## �޸��Ĵ�������
1. ˽������ȱ�ٱ����������磺`private string` ��Ϊ `private string $name`��
2. ���󷽷�����ȱ��->�����������磺`$container(` ��Ϊ `$container->(` ��
3. ������ֵȱ�����ţ����磺`'version' => 1.0` ��Ϊ `'version' => '1.0'`��
4. ���������﷨�������磺`array` ��Ϊ `[]`��
5. ������ȱ�������ռ䣨���磺`WebController::class` ��Ϊ `\\WebController::class`��
6. ��������ȱ�ٱ����������磺`function test(string)` ��Ϊ `function test(string $param)`��
7. UTF-8�ַ���������
8. �����ռ���������

## ��������
- �ڿ���������ʹ��PHP���뾲̬�������ߣ���PHPStan��Psalm����ʱ�����﷨����
- ����IDE�Զ����PHP�﷨����
- �ƶ�����ѭ��Ŀ��PHP����淶���ر���ע��PHP 8.1�����﷨����
- ��������������̣�ȷ���ύ�Ĵ�������﷨�淶

## �ο�����
- [PHP 8.1�ٷ��ĵ�](https://www.php.net/releases/8.1/en.php)
- [PHP���������ĵ�](https://www.php.net/manual/en/language.types.declarations.php)

REPORT;

file_put_contents('PHP_SYNTAX_FIX_REPORT.md', $reportContent];
echo "�������޸�����: PHP_SYNTAX_FIX_REPORT.md\n";

