<?php
/**
 * AlingAi Pro 系统错误自动检测和修复工具
 * 检测并修复语法错误、抽象方法、构造参数类型、不可达代码、命名空间等问题
 */

class SystemFixer {
    private $errors = [];
    private $fixes = [];
    private $scanDirs = [
        'src',
        'apps',
        'bootstrap',
        'config',
        'public'
    ];
    
    public function run() {
        echo "🔍 开始系统错误检测和修复...\n\n";
        
        // 1. 检测所�?PHP 文件
        $phpFiles = $this->getAllPhpFiles(];
        echo "发现 " . count($phpFiles) . " �?PHP 文件需要检查\n\n";
        
        // 2. 语法检�?
        $this->checkSyntaxErrors($phpFiles];
        
        // 3. 检查抽象方�?
        $this->checkAbstractMethods($phpFiles];
        
        // 4. 检查构造函数参�?
        $this->checkConstructorParameters($phpFiles];
        
        // 5. 检查不可达代码
        $this->checkUnreachableCode($phpFiles];
        
        // 6. 检查命名空�?
        $this->checkNamespaces($phpFiles];
        
        // 7. 生成修复方案
        $this->generateFixPlan(];
        
        // 8. 执行修复
        $this->executeFixes(];
        
        // 9. 生成报告
        $this->generateReport(];
    }
    
    private function getAllPhpFiles() {
        $files = [];
        foreach ($this->scanDirs as $dir) {
            if (is_dir($dir)) {
                $files = array_merge($files, $this->scanDirectory($dir)];
            }
        }
        return $files;
    }
    
    private function scanDirectory($dir) {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        ];
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname(];
            }
        }
        return $files;
    }
    
    private function checkSyntaxErrors($files) {
        echo "🔧 检查语法错�?..\n";
        
        foreach ($files as $file) {
            $output = shell_exec("php -l \"$file\" 2>&1"];
            if (strpos($output, 'No syntax errors detected') === false) {
                $this->errors[] = [
                    'type' => 'syntax',
                    'file' => $file,
                    'message' => trim($output],
                    'severity' => 'critical'
                ];
                echo "�?语法错误: $file\n";
            }
        }
    }
    
    private function checkAbstractMethods($files) {
        echo "🔧 检查抽象方法实�?..\n";
        
        foreach ($files as $file) {
            $content = file_get_contents($file];
            
            // 查找抽象�?
            if (preg_match('/abstract\s+class\s+(\w+)/', $content, $matches)) {
                $className = $matches[1];
                
                // 查找抽象方法
                preg_match_all('/abstract\s+(?:public|protected|private)?\s*function\s+(\w+)\s*\([^)]*\)/', $content, $abstractMethods];
                
                if (!empty($abstractMethods[1])) {
                    $this->errors[] = [
                        'type' => 'abstract_methods',
                        'file' => $file,
                        'class' => $className,
                        'methods' => $abstractMethods[1], 
                        'severity' => 'high'
                    ];
                    echo "⚠️ 抽象方法需要实�? $className in $file\n";
                }
            }
            
            // 查找继承但未实现抽象方法的类
            if (preg_match('/class\s+(\w+)\s+extends\s+(\w+)/', $content, $matches)) {
                $className = $matches[1];
                $parentClass = $matches[2];
                
                // 这里需要更深入的分析来检查是否实现了父类的抽象方�?
                // 简化处理：标记需要检�?
                $this->checkClassImplementsAbstractMethods($file, $className, $parentClass, $content];
            }
        }
    }
    
    private function checkClassImplementsAbstractMethods($file, $className, $parentClass, $content) {
        // 检查是否实现了特定的已知抽象方�?
        $knownAbstractMethods = [
            'ServiceManagerInterface' => ['executeService', 'validateService', 'getServiceConfig'], 
            'SecurityServiceInterface' => ['validateRequest', 'processSecurityCheck'], 
            'CacheInterface' => ['get', 'set', 'delete', 'clear']
        ];
        
        foreach ($knownAbstractMethods as $interface => $methods) {
            if (strpos($parentClass, $interface) !== false || strpos($content, "implements $interface") !== false) {
                foreach ($methods as $method) {
                    if (strpos($content, "function $method") === false) {
                        $this->errors[] = [
                            'type' => 'missing_method',
                            'file' => $file,
                            'class' => $className,
                            'method' => $method,
                            'interface' => $interface,
                            'severity' => 'high'
                        ];
                        echo "�?缺少接口方法: $className::$method() in $file\n";
                    }
                }
            }
        }
    }
    
    private function checkConstructorParameters($files) {
        echo "🔧 检查构造函数参数类�?..\n";
        
        foreach ($files as $file) {
            $content = file_get_contents($file];
            
            // 查找构造函�?
            if (preg_match('/function\s+__construct\s*\([^)]*\)/', $content, $matches)) {
                $constructor = $matches[0];
                
                // 检查参数类型声�?
                if (preg_match_all('/\$(\w+)(?:\s*=\s*[^,)]+)?/', $constructor, $params)) {
                    foreach ($params[1] as $param) {
                        // 检查是否有类型声明
                        if (!preg_match('/(?:string|int|bool|array|object|\w+)\s+\$' . $param . '/', $constructor)) {
                            $this->errors[] = [
                                'type' => 'constructor_type',
                                'file' => $file,
                                'parameter' => $param,
                                'severity' => 'medium'
                            ];
                        }
                    }
                }
            }
        }
    }
    
    private function checkUnreachableCode($files) {
        echo "🔧 检查不可达代码...\n";
        
        foreach ($files as $file) {
            $content = file_get_contents($file];
            $lines = explode("\n", $content];
            
            $returnFound = false;
            for ($i = 0; $i < count($lines]; $i++) {
                $line = trim($lines[$i]];
                
                // 检�?return 语句后的代码
                if (preg_match('/^\s*return\s/', $line) && !preg_match('/\/\/|\/\*/', $line)) {
                    $returnFound = $i;
                } elseif ($returnFound !== false && !empty($line) && !preg_match('/^\s*[}\s]*$/', $line) && !preg_match('/\/\/|\/\*/', $line)) {
                    $this->errors[] = [
                        'type' => 'unreachable_code',
                        'file' => $file,
                        'line' => $i + 1,
                        'after_return' => $returnFound + 1,
                        'severity' => 'low'
                    ];
                    $returnFound = false;
                }
                
                // 重置检查状�?
                if (preg_match('/^\s*[{}]\s*$/', $line)) {
                    $returnFound = false;
                }
            }
        }
    }
    
    private function checkNamespaces($files) {
        echo "🔧 检查命名空�?..\n";
        
        foreach ($files as $file) {
            $content = file_get_contents($file];
            
            // 检查是否有 namespace 声明
            if (!preg_match('/^namespace\s+/', $content)) {
                // 检查是否在 src 目录�?
                if (strpos($file, 'src' . DIRECTORY_SEPARATOR) !== false) {
                    $this->errors[] = [
                        'type' => 'missing_namespace',
                        'file' => $file,
                        'severity' => 'medium'
                    ];
                    echo "⚠️ 缺少命名空间: $file\n";
                }
            }
            
            // 检�?use 语句优化
            $uses = [];
            preg_match_all('/^use\s+([^;]+];/m', $content, $useMatches];
            if (!empty($useMatches[1])) {
                foreach ($useMatches[1] as $use) {
                    if (strpos($content, basename($use)) === false) {
                        $this->errors[] = [
                            'type' => 'unused_use',
                            'file' => $file,
                            'use' => trim($use],
                            'severity' => 'low'
                        ];
                    }
                }
            }
        }
    }
    
    private function generateFixPlan() {
        echo "\n📋 生成修复方案...\n";
        
        foreach ($this->errors as $error) {
            switch ($error['type']) {
                case 'syntax':
                    $this->fixes[] = $this->createSyntaxFix($error];
                    break;
                case 'abstract_methods':
                case 'missing_method':
                    $this->fixes[] = $this->createMethodImplementationFix($error];
                    break;
                case 'constructor_type':
                    $this->fixes[] = $this->createConstructorTypeFix($error];
                    break;
                case 'unreachable_code':
                    $this->fixes[] = $this->createUnreachableCodeFix($error];
                    break;
                case 'missing_namespace':
                    $this->fixes[] = $this->createNamespaceFix($error];
                    break;
                case 'unused_use':
                    $this->fixes[] = $this->createUnusedUseFix($error];
                    break;
            }
        }
        
        echo "生成�?" . count($this->fixes) . " 个修复方案\n";
    }
    
    private function createSyntaxFix($error) {
        return [
            'type' => 'syntax_fix',
            'file' => $error['file'], 
            'action' => 'manual_review',
            'message' => $error['message'], 
            'priority' => 1
        ];
    }
    
    private function createMethodImplementationFix($error) {
        $content = '';
        if (isset($error['methods'])) {
            foreach ($error['methods'] as $method) {
                $content .= "\n    public function $method() {\n";
                $content .= "        // TODO: 实现 $method 方法\n";
                $content .= "        throw new \\Exception('Method $method not implemented'];\n";
                $content .= "    }\n";
            }
        } else {
            $method = $error['method'];
            $content = "\n    public function $method() {\n";
            $content .= "        // TODO: 实现 $method 方法\n";
            $content .= "        throw new \\Exception('Method $method not implemented'];\n";
            $content .= "    }\n";
        }
        
        return [
            'type' => 'method_implementation',
            'file' => $error['file'], 
            'action' => 'add_methods',
            'content' => $content,
            'class' => $error['class'], 
            'priority' => 2
        ];
    }
    
    private function createConstructorTypeFix($error) {
        return [
            'type' => 'constructor_type',
            'file' => $error['file'], 
            'action' => 'add_type_hint',
            'parameter' => $error['parameter'], 
            'priority' => 3
        ];
    }
    
    private function createUnreachableCodeFix($error) {
        return [
            'type' => 'unreachable_code',
            'file' => $error['file'], 
            'action' => 'remove_code',
            'line' => $error['line'], 
            'priority' => 4
        ];
    }
    
    private function createNamespaceFix($error) {
        $namespace = $this->generateNamespaceFromPath($error['file']];
        return [
            'type' => 'namespace',
            'file' => $error['file'], 
            'action' => 'add_namespace',
            'namespace' => $namespace,
            'priority' => 3
        ];
    }
    
    private function createUnusedUseFix($error) {
        return [
            'type' => 'unused_use',
            'file' => $error['file'], 
            'action' => 'remove_use',
            'use' => $error['use'], 
            'priority' => 5
        ];
    }
    
    private function generateNamespaceFromPath($file) {
        $path = str_replace(['/', '\\'],  DIRECTORY_SEPARATOR, $file];
        if (strpos($path, 'src' . DIRECTORY_SEPARATOR) !== false) {
            $parts = explode('src' . DIRECTORY_SEPARATOR, $path];
            $relativePath = $parts[1];
            $namespace = str_replace(DIRECTORY_SEPARATOR, '\\', dirname($relativePath)];
            return 'AlingAi\\' . $namespace;
        }
        return 'AlingAi';
    }
    
    private function executeFixes() {
        echo "\n🛠�?执行修复...\n";
          // 按优先级排序
        usort($this->fixes, function(array $a, array $b): int {
            return $a['priority'] - $b['priority'];
        }];
        
        $fixedFiles = [];
        
        foreach ($this->fixes as $fix) {
            try {
                switch ($fix['action']) {
                    case 'add_methods':
                        $this->addMethodsToClass($fix];
                        $fixedFiles[] = $fix['file'];
                        break;
                    case 'add_namespace':
                        $this->addNamespaceToFile($fix];
                        $fixedFiles[] = $fix['file'];
                        break;
                    case 'remove_use':
                        $this->removeUnusedUse($fix];
                        $fixedFiles[] = $fix['file'];
                        break;
                    case 'remove_code':
                        $this->removeUnreachableCode($fix];
                        $fixedFiles[] = $fix['file'];
                        break;
                    case 'manual_review':
                        echo "⚠️ 需要手动检�? {$fix['file']} - {$fix['message']}\n";
                        break;
                }
            } catch (Exception $e) {
                echo "�?修复失败: {$fix['file']} - {$e->getMessage()}\n";
            }
        }
        
        echo "\n�?已修�?" . count(array_unique($fixedFiles)) . " 个文件\n";
    }
    
    private function addMethodsToClass($fix) {
        $content = file_get_contents($fix['file']];
        
        // 找到类的结束位置
        $className = $fix['class'];
        $pattern = '/class\s+' . preg_quote($className) . '.*?{/s';
        
        if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            $classStart = $matches[0][1] + strlen($matches[0][0]];
            
            // 找到类的最后一个闭括号
            $braceCount = 1;
            $pos = $classStart;
            $lastBrace = strlen($content) - 1;
            
            while ($pos < strlen($content) && $braceCount > 0) {
                if ($content[$pos] === '{') {
                    $braceCount++;
                } elseif ($content[$pos] === '}') {
                    $braceCount--;
                    if ($braceCount === 0) {
                        $lastBrace = $pos;
                    }
                }
                $pos++;
            }
            
            // 插入方法
            $newContent = substr($content, 0, $lastBrace) . $fix['content'] . substr($content, $lastBrace];
            file_put_contents($fix['file'],  $newContent];
            
            echo "�?添加方法�?{$fix['class']} in {$fix['file']}\n";
        }
    }
    
    private function addNamespaceToFile($fix) {
        $content = file_get_contents($fix['file']];
        
        // 检查是否已�?namespace
        if (strpos($content, 'namespace ') === false) {
            $lines = explode("\n", $content];
            $insertAt = 0;
            
            // 找到 <?php 标签后的位置
            for ($i = 0; $i < count($lines]; $i++) {
                if (strpos($lines[$i],  '<?php') !== false) {
                    $insertAt = $i + 1;
                    break;
                }
            }
            
            // 插入 namespace
            array_splice($lines, $insertAt, 0, [
                '',
                'namespace ' . $fix['namespace'] . ';',
                ''
            ]];
            
            file_put_contents($fix['file'],  implode("\n", $lines)];
            echo "�?添加命名空间�?{$fix['file']}\n";
        }
    }
    
    private function removeUnusedUse($fix) {
        $content = file_get_contents($fix['file']];
        $useStatement = 'use ' . $fix['use'] . ';';
        $newContent = str_replace($useStatement, '', $content];
        
        if ($newContent !== $content) {
            file_put_contents($fix['file'],  $newContent];
            echo "�?移除未使用的 use: {$fix['use']} from {$fix['file']}\n";
        }
    }
    
    private function removeUnreachableCode($fix) {
        $content = file_get_contents($fix['file']];
        $lines = explode("\n", $content];
        
        // 简单处理：添加注释而不是删�?
        if (isset($lines[$fix['line'] - 1])) {
            $lines[$fix['line'] - 1] = '// ' . $lines[$fix['line'] - 1] . ' // 不可达代�?;
            file_put_contents($fix['file'],  implode("\n", $lines)];
            echo "�?注释不可达代�? line {$fix['line']} in {$fix['file']}\n";
        }
    }
    
    private function generateReport() {
        echo "\n📄 生成修复报告...\n";
        
        $report = "# AlingAi Pro 系统错误修复报告\n\n";
        $report .= "生成时间: " . date('Y-m-d H:i:s') . "\n\n";
        
        $report .= "## 检测到的问题\n\n";
        $errorsByType = [];
        foreach ($this->errors as $error) {
            $errorsByType[$error['type']][] = $error;
        }
        
        foreach ($errorsByType as $type => $errors) {
            $report .= "### " . ucfirst(str_replace('_', ' ', $type)) . " (" . count($errors) . ")\n\n";
            foreach ($errors as $error) {
                $report .= "- **文件**: {$error['file']}\n";
                if (isset($error['message'])) {
                    $report .= "  **错误**: {$error['message']}\n";
                }
                if (isset($error['class'])) {
                    $report .= "  **�?*: {$error['class']}\n";
                }
                if (isset($error['method'])) {
                    $report .= "  **方法**: {$error['method']}\n";
                }
                $report .= "  **严重程度**: {$error['severity']}\n\n";
            }
        }
        
        $report .= "## 执行的修复\n\n";
        $fixesByType = [];
        foreach ($this->fixes as $fix) {
            $fixesByType[$fix['type']][] = $fix;
        }
        
        foreach ($fixesByType as $type => $fixes) {
            $report .= "### " . ucfirst(str_replace('_', ' ', $type)) . " (" . count($fixes) . ")\n\n";
            foreach ($fixes as $fix) {
                $report .= "- **文件**: {$fix['file']}\n";
                $report .= "  **操作**: {$fix['action']}\n";
                if (isset($fix['content'])) {
                    $report .= "  **添加内容**: 是\n";
                }
                $report .= "\n";
            }
        }
        
        $report .= "## 总结\n\n";
        $report .= "- 检测到 " . count($this->errors) . " 个问题\n";
        $report .= "- 生成�?" . count($this->fixes) . " 个修复方案\n";
        $report .= "- 需要手动检查的问题: " . count(array_filter($this->fixes, function($f) { return $f['action'] === 'manual_review'; })) . "\n";
        
        file_put_contents('COMPREHENSIVE_FIX_REPORT_' . date('Y_m_d_H_i_s') . '.md', $report];
        
        echo "�?修复报告已保存\n";
        echo "\n🎉 系统错误检测和修复完成！\n";
        echo "总计处理 " . count($this->errors) . " 个问题\n";
    }
}

// 执行修复
try {
    $fixer = new SystemFixer(];
    $fixer->run(];
} catch (Exception $e) {
    echo "�?系统修复失败: " . $e->getMessage() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
}
