<?php
/**
 * AlingAi Pro 生产环境兼容性检查
 * 检查可能被禁用的PHP函数，确保系统在受限环境中正常运行
 */

class ProductionCompatibilityChecker {
    
    private $restrictedFunctions = [
        'exec', 'shell_exec', 'system', 'passthru', 'popen', 'proc_open',
        'putenv', 'file_get_contents', 'file_put_contents', 'fopen', 'fwrite',
        'curl_exec', 'curl_init', 'mail', 'ini_set', 'set_time_limit'
    ];
    
    private $criticalFunctions = [
        'exec', 'shell_exec', 'putenv', 'system'
    ];
    
    private $results = [];
    
    public function run() {
        echo "=== AlingAi Pro 生产环境兼容性检查 ===\n\n";
        
        $this->checkDisabledFunctions();
        $this->checkCriticalFiles();
        $this->generateReport();
        
        return $this->results;
    }
    
    /**
     * 检查被禁用的函数
     */
    private function checkDisabledFunctions() {
        echo "🔍 检查被禁用的PHP函数...\n";
        
        $disabledFunctions = explode(',', strtolower(ini_get('disable_functions')));
        $disabledFunctions = array_map('trim', $disabledFunctions);
        
        $this->results['disabled_functions'] = [];
        $this->results['available_functions'] = [];
        
        foreach ($this->restrictedFunctions as $function) {
            if (in_array($function, $disabledFunctions) || !function_exists($function)) {
                $this->results['disabled_functions'][] = $function;
                $isCritical = in_array($function, $this->criticalFunctions);
                echo sprintf("  ❌ %s - 被禁用%s\n", $function, $isCritical ? ' (关键函数)' : '');
            } else {
                $this->results['available_functions'][] = $function;
                echo sprintf("  ✅ %s - 可用\n", $function);
            }
        }
        
        echo "\n";
    }
      /**
     * 检查关键文件的兼容性修复
     */
    private function checkCriticalFiles() {
        echo "🔍 检查关键文件的兼容性修复...\n";
        
        $criticalFiles = [
            'three_complete_compilation_validator.php' => ['putenv'],
            'install/install.php' => ['exec'],
            'scripts/system_monitor.php' => ['shell_exec'],
            'bin/health-check.php' => ['shell_exec'],
            'install/api/services.php' => ['shell_exec']
        ];
        
        $this->results['file_compatibility'] = [];
        
        foreach ($criticalFiles as $file => $functions) {
            $filePath = __DIR__ . '/' . $file;
            if (!file_exists($filePath)) {
                echo "  ⚠️  $file - 文件不存在\n";
                continue;
            }
            
            $content = file_get_contents($filePath);
            $isFixed = true;
            $issues = [];
            
            foreach ($functions as $function) {
                // 检查是否有function_exists检查
                if (strpos($content, "function_exists('$function')") === false) {
                    $isFixed = false;
                    $issues[] = "缺少 $function 函数存在性检查";
                }
                
                // 检查是否有未保护的函数调用
                $pattern = '/(?<!function_exists\(\'' . $function . '\'\)\s*\&\&\s*|if\s*\(\s*function_exists\(\'' . $function . '\'\)\s*\)\s*\{[^}]*)\b' . $function . '\s*\(/';
                if (preg_match($pattern, $content)) {
                    // 进一步检查：确保所有调用都有保护
                    $lines = explode("\n", $content);
                    foreach ($lines as $lineNum => $line) {
                        if (strpos($line, $function . '(') !== false && 
                            strpos($line, 'function_exists') === false) {
                            // 检查前几行是否有function_exists检查
                            $hasProtection = false;
                            for ($i = max(0, $lineNum - 5); $i < $lineNum; $i++) {
                                if (strpos($lines[$i], "function_exists('$function')") !== false) {
                                    $hasProtection = true;
                                    break;
                                }
                            }
                            if (!$hasProtection) {
                                $isFixed = false;
                                $issues[] = "第" . ($lineNum + 1) . "行: 未保护的 $function 调用";
                            }
                        }
                    }
                }
            }
            
            $this->results['file_compatibility'][$file] = [
                'fixed' => $isFixed,
                'issues' => $issues
            ];
            
            if ($isFixed) {
                echo "  ✅ $file - 已修复兼容性\n";
            } else {
                echo "  ❌ $file - 需要修复: " . implode(', ', $issues) . "\n";
            }
        }
        
        echo "\n";
    }
    
    /**
     * 生成兼容性报告
     */
    private function generateReport() {
        echo "📊 兼容性报告:\n";
        
        $disabledCount = count($this->results['disabled_functions']);
        $availableCount = count($this->results['available_functions']);
        $totalCount = $disabledCount + $availableCount;
        
        echo sprintf("  • 检查函数总数: %d\n", $totalCount);
        echo sprintf("  • 可用函数: %d (%.1f%%)\n", $availableCount, ($availableCount / $totalCount) * 100);
        echo sprintf("  • 被禁用函数: %d (%.1f%%)\n", $disabledCount, ($disabledCount / $totalCount) * 100);
        
        // 检查关键函数状态
        $criticalDisabled = array_intersect($this->results['disabled_functions'], $this->criticalFunctions);
        if (!empty($criticalDisabled)) {
            echo "\n⚠️  关键函数被禁用: " . implode(', ', $criticalDisabled) . "\n";
            echo "   系统已实现兼容性修复，但部分功能可能受限。\n";
        }
        
        // 文件兼容性统计
        $fixedFiles = 0;
        $totalFiles = count($this->results['file_compatibility']);
        
        foreach ($this->results['file_compatibility'] as $fileInfo) {
            if ($fileInfo['fixed']) {
                $fixedFiles++;
            }
        }
        
        echo sprintf("\n  • 关键文件兼容性: %d/%d (%.1f%%) 已修复\n", 
                    $fixedFiles, $totalFiles, ($fixedFiles / $totalFiles) * 100);
        
        // 总体评估
        $overallScore = (($availableCount / $totalCount) * 0.6 + ($fixedFiles / $totalFiles) * 0.4) * 100;
        echo sprintf("\n🎯 生产环境兼容性评分: %.1f%%\n", $overallScore);
        
        if ($overallScore >= 90) {
            echo "✅ 系统具有优秀的生产环境兼容性\n";
        } elseif ($overallScore >= 80) {
            echo "✅ 系统具有良好的生产环境兼容性\n";
        } elseif ($overallScore >= 70) {
            echo "⚠️  系统兼容性一般，建议进一步优化\n";
        } else {
            echo "❌ 系统兼容性较差，需要重点优化\n";
        }
        
        echo "\n";
    }
    
    /**
     * 保存报告到文件
     */
    public function saveReport() {
        $reportData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'os' => PHP_OS,
            'sapi' => php_sapi_name(),
            'results' => $this->results
        ];
        
        $reportFile = __DIR__ . '/storage/logs/production_compatibility_report.json';
        $dir = dirname($reportFile);
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($reportFile, json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "📄 兼容性报告已保存到: $reportFile\n";
    }
}

// 运行检查
if (php_sapi_name() === 'cli') {
    $checker = new ProductionCompatibilityChecker();
    $checker->run();
    $checker->saveReport();
}
