<?php
/**
 * 高级系统错误检测和修复工具
 * 处理语法错误、抽象方法、构造函数参数等高级问题
 */

class ComprehensiveAdvancedFixer
{
    private $errorTypes = [
        'syntax_errors' => [], 
        'abstract_methods' => [], 
        'constructor_issues' => [], 
        'type_declarations' => [], 
        'namespace_issues' => [], 
        'interface_implementations' => [], 
        'trait_conflicts' => [], 
        'visibility_issues' => []
    ];

    private $fixedCount = 0;
    private $logFile;

    public function __construct()
    {
        $this->logFile = __DIR__ . '/advanced_fix_report_' . date('Y_m_d_H_i_s') . '.json';
    }

    public function scanAndFix()
    {
        echo "🔍 开始高级错误检测和修复...\n";
        
        $directories = [
            'src',
            'apps',
            'config',
            'bootstrap',
            'public'
        ];

        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                $this->scanDirectory($dir];
            }
        }

        $this->generateReport(];
        echo "🎉 高级错误修复完成！总计修复 {$this->fixedCount} 个问题\n";
    }

    private function scanDirectory($directory)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        ];

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $this->analyzeAndFixFile($file->getPathname()];
            }
        }
    }

    private function analyzeAndFixFile($filePath)
    {
        try {
            $content = file_get_contents($filePath];
            if ($content === false) {
                return;
            }

            $originalContent = $content;
            
            // 检查并修复各种错误类型
            $content = $this->fixSyntaxErrors($content, $filePath];
            $content = $this->fixAbstractMethods($content, $filePath];
            $content = $this->fixConstructorIssues($content, $filePath];
            $content = $this->fixTypeDeclarations($content, $filePath];
            $content = $this->fixNamespaceIssues($content, $filePath];
            $content = $this->fixInterfaceImplementations($content, $filePath];
            $content = $this->fixTraitConflicts($content, $filePath];
            $content = $this->fixVisibilityIssues($content, $filePath];

            // 如果内容有变化，写入文件
            if ($content !== $originalContent) {
                file_put_contents($filePath, $content];
                echo "�?修复文件: $filePath\n";
            }

        } catch (Exception $e) {
            echo "�?处理文件失败: $filePath - {$e->getMessage()}\n";
        }
    }

    private function fixSyntaxErrors($content, $filePath)
    {
        // 修复常见的语法错�?
        $patterns = [
            // 修复缺失的分�?
            '/([a-zA-Z0-9_\]\)\'"])(\s*\n\s*)(public|private|protected|function|class|if|for|while|return)/m' => '$1;$2$3',
            
            // 修复多余的逗号
            '/,(\s*[\]\}])/m' => '$1',
            
            // 修复缺失的逗号
            '/(\$[a-zA-Z0-9_]+)(\s+)(\$[a-zA-Z0-9_]+)/' => '$1,$2$3',
            
            // 修复错误的字符串连接
            '/(\$[a-zA-Z0-9_]+)\s+\.\s+/' => '$1 . ',
            
            // 修复未闭合的字符�?
            '/(["\'])([^"\']*?)(\n)/' => '$1$2$1;$3',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $newContent = preg_replace($pattern, $replacement, $content];
            if ($newContent !== $content) {
                $content = $newContent;
                $this->errorTypes['syntax_errors'][] = [
                    'file' => $filePath,
                    'pattern' => $pattern,
                    'fixed' => true
                ];
                $this->fixedCount++;
            }
        }

        return $content;
    }

    private function fixAbstractMethods($content, $filePath)
    {
        // 查找抽象类和抽象方法
        if (preg_match_all('/abstract\s+class\s+([a-zA-Z0-9_]+)/i', $content, $abstractClasses)) {
            foreach ($abstractClasses[1] as $className) {
                // 查找该类的抽象方�?
                if (preg_match_all('/abstract\s+(?:public|protected|private)?\s*function\s+([a-zA-Z0-9_]+)\s*\([^)]*\)\s*(?::\s*[^;]+)?\s*;/i', $content, $abstractMethods)) {
                    
                    // 查找继承该抽象类的子�?
                    if (preg_match_all('/class\s+([a-zA-Z0-9_]+)\s+extends\s+' . $className . '/i', $content, $childClasses)) {
                        foreach ($childClasses[1] as $childClass) {
                            foreach ($abstractMethods[1] as $methodName) {
                                // 检查子类是否实现了抽象方法
                                if (!preg_match('/function\s+' . $methodName . '\s*\(/i', $content)) {
                                    // 添加空的方法实现
                                    $methodImplementation = "\n    public function {$methodName}()\n    {\n        // TODO: Implement {$methodName} method\n        throw new \\Exception('Method {$methodName} not implemented'];\n    }\n";
                                    
                                    // 在类的结束大括号前插入方�?
                                    $content = preg_replace('/(\n\s*}\s*$)/m', $methodImplementation . '$1', $content, 1];
                                    
                                    $this->errorTypes['abstract_methods'][] = [
                                        'file' => $filePath,
                                        'class' => $childClass,
                                        'method' => $methodName,
                                        'fixed' => true
                                    ];
                                    $this->fixedCount++;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $content;
    }

    private function fixConstructorIssues($content, $filePath)
    {
        // 修复构造函数参数类型不匹配
        $patterns = [
            // 修复缺失的父类构造函数调�?
            '/(class\s+[a-zA-Z0-9_]+\s+extends\s+[a-zA-Z0-9_]+.*?function\s+__construct\s*\([^}]*?\{)(?!.*parent::__construct)/s' => '$1
        parent::__construct(];',
            
            // 修复构造函数参数类�?
            '/function\s+__construct\s*\(\s*([^)]+)\s*\)/' => function($matches) {
                $params = explode(',', $matches[1]];
                $fixedParams = [];
                foreach ($params as $param) {
                    $param = trim($param];
                    if (!empty($param) && !preg_match('/^(string|int|float|bool|array|object|\?[a-zA-Z0-9_\\\\]+|[a-zA-Z0-9_\\\\]+)\s/', $param)) {
                        // 添加默认类型声明
                        if (strpos($param, '$') === 0) {
                            $param = 'mixed ' . $param;
                        }
                    }
                    $fixedParams[] = $param;
                }
                return 'function __construct(' . implode(', ', $fixedParams) . ')';
            }
        ];

        foreach ($patterns as $pattern => $replacement) {
            $newContent = preg_replace_callback($pattern, function($matches) use ($replacement) {
                if (is_callable($replacement)) {
                    return $replacement($matches];
                }
                return $replacement;
            }, $content];
            
            if ($newContent !== $content) {
                $content = $newContent;
                $this->errorTypes['constructor_issues'][] = [
                    'file' => $filePath,
                    'pattern' => $pattern,
                    'fixed' => true
                ];
                $this->fixedCount++;
            }
        }

        return $content;
    }

    private function fixTypeDeclarations($content, $filePath)
    {
        // 修复类型声明问题
        $patterns = [
            // 修复返回类型声明
            '/function\s+([a-zA-Z0-9_]+)\s*\([^)]*\)\s*\{/' => function($matches) {
                $functionName = $matches[1];
                // 为常见的方法添加返回类型
                $returnTypes = [
                    'getId' => ': int',
                    'getName' => ': string',
                    'getTitle' => ': string',
                    'getEmail' => ': string',
                    'getPassword' => ': string',
                    'isActive' => ': bool',
                    'isEnabled' => ': bool',
                    'hasPermission' => ': bool',
                    'getCreatedAt' => ': \\DateTime',
                    'getUpdatedAt' => ': \\DateTime',
                    'toArray' => ': array',
                    'jsonSerialize' => ': array'
                ];
                
                $returnType = $returnTypes[$functionName] ?? '';
                return "function {$functionName}(" . substr($matches[0],  strpos($matches[0],  '('], strpos($matches[0],  ')') - strpos($matches[0],  '(') + 1) . ")" . $returnType . " {";
            },
            
            // 修复属性类型声�?
            '/(private|protected|public)\s+(\$[a-zA-Z0-9_]+)/' => function($matches) {
                $visibility = $matches[1];
                $property = $matches[2];
                
                // 为常见属性添加类�?
                $propertyTypes = [
                    '$id' => 'int',
                    '$name' => 'string',
                    '$title' => 'string',
                    '$email' => 'string',
                    '$password' => 'string',
                    '$active' => 'bool',
                    '$enabled' => 'bool',
                    '$createdAt' => '\\DateTime',
                    '$updatedAt' => '\\DateTime',
                    '$data' => 'array',
                    '$config' => 'array',
                    '$options' => 'array'
                ];
                
                $type = $propertyTypes[$property] ?? '';
                if ($type) {
                    return "{$visibility} {$type} {$property}";
                }
                return $matches[0];
            }
        ];

        foreach ($patterns as $pattern => $replacement) {
            $newContent = preg_replace_callback($pattern, $replacement, $content];
            if ($newContent !== $content) {
                $content = $newContent;
                $this->errorTypes['type_declarations'][] = [
                    'file' => $filePath,
                    'pattern' => $pattern,
                    'fixed' => true
                ];
                $this->fixedCount++;
            }
        }

        return $content;
    }

    private function fixNamespaceIssues($content, $filePath)
    {
        // 修复命名空间问题
        if (!preg_match('/^<\?php\s*namespace/m', $content) && !preg_match('/namespace\s+[a-zA-Z0-9_\\\\]+\s*;/m', $content)) {
            // 根据文件路径生成命名空间
            $relativePath = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $filePath];
            $pathParts = explode(DIRECTORY_SEPARATOR, dirname($relativePath)];
            
            if ($pathParts[0] === 'src') {
                array_shift($pathParts]; // 移除 'src'
                if (!empty($pathParts)) {
                    $namespace = 'AlingAi\\' . implode('\\', array_map('ucfirst', $pathParts)];
                    
                    // �?<?php 后添加命名空�?
                    $content = preg_replace('/^(<\?php)\s*/', "$1\n\nnamespace {$namespace};\n\n", $content];
                    
                    $this->errorTypes['namespace_issues'][] = [
                        'file' => $filePath,
                        'namespace' => $namespace,
                        'fixed' => true
                    ];
                    $this->fixedCount++;
                }
            }
        }

        return $content;
    }

    private function fixInterfaceImplementations($content, $filePath)
    {
        // 修复接口实现问题
        if (preg_match_all('/class\s+([a-zA-Z0-9_]+)\s+implements\s+([a-zA-Z0-9_\\\\,\s]+)/i', $content, $implementations)) {
            foreach ($implementations[1] as $index => $className) {
                $interfaces = explode(',', $implementations[2][$index]];
                foreach ($interfaces as $interface) {
                    $interface = trim($interface];
                    
                    // 检查是否有常见的接口方法未实现
                    $commonInterfaceMethods = [
                        'JsonSerializable' => ['jsonSerialize'], 
                        'Serializable' => ['serialize', 'unserialize'], 
                        'Countable' => ['count'], 
                        'Iterator' => ['current', 'key', 'next', 'rewind', 'valid'], 
                        'ArrayAccess' => ['offsetExists', 'offsetGet', 'offsetSet', 'offsetUnset']
                    ];
                    
                    if (isset($commonInterfaceMethods[$interface])) {
                        foreach ($commonInterfaceMethods[$interface] as $method) {
                            if (!preg_match('/function\s+' . $method . '\s*\(/i', $content)) {
                                // 添加默认实现
                                $methodImplementation = $this->generateDefaultMethodImplementation($method, $interface];
                                $content = preg_replace('/(\n\s*}\s*$)/m', $methodImplementation . '$1', $content, 1];
                                
                                $this->errorTypes['interface_implementations'][] = [
                                    'file' => $filePath,
                                    'class' => $className,
                                    'interface' => $interface,
                                    'method' => $method,
                                    'fixed' => true
                                ];
                                $this->fixedCount++;
                            }
                        }
                    }
                }
            }
        }

        return $content;
    }

    private function fixTraitConflicts($content, $filePath)
    {
        // 修复 trait 冲突
        if (preg_match_all('/use\s+([a-zA-Z0-9_\\\\,\s]+)\s*;/i', $content, $traits)) {
            foreach ($traits[1] as $traitList) {
                $traitNames = explode(',', $traitList];
                if (count($traitNames) > 1) {
                    // 检查是否需要解决冲�?
                    $conflicts = [];
                    foreach ($traitNames as $trait) {
                        $trait = trim($trait];
                        // 这里可以添加更复杂的冲突检测逻辑
                    }
                    
                    if (!empty($conflicts)) {
                        // 添加冲突解决方案
                        $conflictResolution = "\n    // Resolve trait conflicts\n";
                        foreach ($conflicts as $conflict) {
                            $conflictResolution .= "    use {$conflict['trait']}::{$conflict['method']} { {$conflict['trait']}::{$conflict['method']} as {$conflict['alias']}; }\n";
                        }
                        
                        $content = str_replace($traits[0],  $traits[0] . $conflictResolution, $content];
                        
                        $this->errorTypes['trait_conflicts'][] = [
                            'file' => $filePath,
                            'conflicts' => $conflicts,
                            'fixed' => true
                        ];
                        $this->fixedCount++;
                    }
                }
            }
        }

        return $content;
    }

    private function fixVisibilityIssues($content, $filePath)
    {
        // 修复可见性问�?
        $patterns = [
            // 为没有可见性修饰符的方法添�?public
            '/^(\s*)(function\s+(?!__)[a-zA-Z0-9_]+\s*\()/m' => '$1public $2',
            
            // 为没有可见性修饰符的属性添�?private
            '/^(\s*)(\$[a-zA-Z0-9_]+\s*[=;])/m' => '$1private $2',
            
            // 修复重复的可见性修饰符
            '/(public|private|protected)\s+(public|private|protected)/' => '$1',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $newContent = preg_replace($pattern, $replacement, $content];
            if ($newContent !== $content) {
                $content = $newContent;
                $this->errorTypes['visibility_issues'][] = [
                    'file' => $filePath,
                    'pattern' => $pattern,
                    'fixed' => true
                ];
                $this->fixedCount++;
            }
        }

        return $content;
    }

    private function generateDefaultMethodImplementation($method, $interface)
    {
        $implementations = [
            'jsonSerialize' => "\n    public function jsonSerialize(): mixed\n    {\n        return get_object_vars(\$this];\n    }\n",
            'serialize' => "\n    public function serialize(): string\n    {\n        return serialize(get_object_vars(\$this)];\n    }\n",
            'unserialize' => "\n    public function unserialize(string \$data): void\n    {\n        \$vars = unserialize(\$data];\n        foreach (\$vars as \$key => \$value) {\n            \$this->\$key = \$value;\n        }\n    }\n",
            'count' => "\n    public function count(): int\n    {\n        return count(get_object_vars(\$this)];\n    }\n",
            'current' => "\n    public function current(): mixed\n    {\n        return current(\$this->data ?? []];\n    }\n",
            'key' => "\n    public function key(): mixed\n    {\n        return key(\$this->data ?? []];\n    }\n",
            'next' => "\n    public function next(): void\n    {\n        next(\$this->data ?? []];\n    }\n",
            'rewind' => "\n    public function rewind(): void\n    {\n        reset(\$this->data ?? []];\n    }\n",
            'valid' => "\n    public function valid(): bool\n    {\n        return key(\$this->data ?? []) !== null;\n    }\n",
            'offsetExists' => "\n    public function offsetExists(mixed \$offset): bool\n    {\n        return isset(\$this->data[\$offset]];\n    }\n",
            'offsetGet' => "\n    public function offsetGet(mixed \$offset): mixed\n    {\n        return \$this->data[\$offset] ?? null;\n    }\n",
            'offsetSet' => "\n    public function offsetSet(mixed \$offset, mixed \$value): void\n    {\n        if (\$offset === null) {\n            \$this->data[] = \$value;\n        } else {\n            \$this->data[\$offset] = \$value;\n        }\n    }\n",
            'offsetUnset' => "\n    public function offsetUnset(mixed \$offset): void\n    {\n        unset(\$this->data[\$offset]];\n    }\n"
        ];

        return $implementations[$method] ?? "\n    public function {$method}()\n    {\n        // TODO: Implement {$method} method for {$interface}\n        throw new \\Exception('Method {$method} not implemented'];\n    }\n";
    }

    private function generateReport()
    {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'],
            'total_fixes' => $this->fixedCount,
            'error_types' => $this->errorTypes,
            'summary' => [
                'syntax_errors' => count($this->errorTypes['syntax_errors']],
                'abstract_methods' => count($this->errorTypes['abstract_methods']],
                'constructor_issues' => count($this->errorTypes['constructor_issues']],
                'type_declarations' => count($this->errorTypes['type_declarations']],
                'namespace_issues' => count($this->errorTypes['namespace_issues']],
                'interface_implementations' => count($this->errorTypes['interface_implementations']],
                'trait_conflicts' => count($this->errorTypes['trait_conflicts']],
                'visibility_issues' => count($this->errorTypes['visibility_issues'])
            ]
        ];

        file_put_contents($this->logFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)];
        echo "📄 高级修复报告已保存到: {$this->logFile}\n";
    }
}

// 运行高级修复�?
$fixer = new ComprehensiveAdvancedFixer(];
$fixer->scanAndFix(];
