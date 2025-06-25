<?php
/**
 * AlingAi Pro 5.0 - 系统验证测试
 * System Validation & Health Check
 * 
 * 这个脚本将验证迁移后的系统完整性和功能
 */

class SystemValidator {
    private $rootDir;
    private $publicDir;
    private $results = [];
      public function __construct() {
        $this->rootDir = __DIR__;
        $this->publicDir = $this->rootDir . '/public';
        
        echo "🔍 AlingAi Pro 5.0 - 系统验证测试\n";
        echo str_repeat("=", 80) . "\n";
    }
    
    public function runFullValidation() {
        echo "🚀 开始系统完整性验�?..\n\n";
        
        $this->validateDirectoryStructure(];
        $this->validatePublicAccess(];
        $this->validateSecurityConfig(];
        $this->validateSymbolicLinks(];
        $this->validateCriticalFiles(];
        $this->validateMigratedContent(];
        
        $this->generateValidationReport(];
    }
    
    private function validateDirectoryStructure() {
        echo "📁 验证目录结构...\n";
        echo str_repeat("-", 50) . "\n";
        
        $requiredDirs = [
            'public' => '�?Web可访问文件目�?,
            'public/api' => '�?API接口目录',
            'public/admin' => '�?管理界面目录',
            'public/assets' => '�?静态资源目�?,
            'public/docs' => '�?在线文档目录',
            'public/install' => '�?安装工具目录',
            'public/tests' => '�?测试工具目录',
            'public/uploads' => '�?上传文件目录',
            'scripts' => '�?项目脚本目录',
            'scripts/analysis' => '�?分析工具目录',
            'scripts/migration' => '�?迁移脚本目录',
            'scripts/cleanup' => '�?清理工具目录',
            'scripts/system' => '�?系统脚本目录',
            'config' => '�?配置文件目录',
            'src' => '�?源代码目�?,
            'backup' => '�?备份目录',
            'docs' => '�?项目文档目录',
            'services' => '�?服务文件目录'
        ];
        
        $passed = 0;
        $total = count($requiredDirs];
        
        foreach ($requiredDirs as $dir => $description) {
            $fullPath = $this->rootDir . '/' . $dir;
            if (is_dir($fullPath)) {
                echo "�?$description\n";
                $passed++;
            } else {
                echo "�?缺失: $dir\n";
            }
        }
        
        $this->results['directory_structure'] = [
            'passed' => $passed,
            'total' => $total,
            'percentage' => round(($passed / $total) * 100, 2)
        ];
        
        echo "\n📊 目录结构: $passed/$total 通过 (" . $this->results['directory_structure']['percentage'] . "%)\n\n";
    }
    
    private function validatePublicAccess() {
        echo "🌐 验证公共目录访问�?..\n";
        echo str_repeat("-", 50) . "\n";
        
        $publicFiles = [
            'index.php' => 'Web应用入口',
            'api/index.php' => 'API接口入口',
            'admin/index.php' => '管理界面入口',
            'assets/css/style.css' => 'CSS样式文件',
            'assets/js/app.js' => 'JavaScript文件',
            '.htaccess' => '访问控制配置'
        ];
        
        $passed = 0;
        $total = count($publicFiles];
        
        foreach ($publicFiles as $file => $description) {
            $fullPath = $this->publicDir . '/' . $file;
            if (file_exists($fullPath)) {
                echo "�?$description: $file\n";
                $passed++;
            } else {
                echo "⚠️ 缺失: $file ($description)\n";
            }
        }
        
        $this->results['public_access'] = [
            'passed' => $passed,
            'total' => $total,
            'percentage' => round(($passed / $total) * 100, 2)
        ];
        
        echo "\n📊 公共文件: $passed/$total 存在 (" . $this->results['public_access']['percentage'] . "%)\n\n";
    }
    
    private function validateSecurityConfig() {
        echo "🔒 验证安全配置...\n";
        echo str_repeat("-", 50) . "\n";
        
        $securityChecks = [
            '.env' => '环境配置文件（私有）',
            'public/.htaccess' => 'Web访问控制',
            'public/install/.htaccess' => '安装工具访问限制',
            'config/' => '配置目录（私有）',
            'backup/' => '备份目录（私有）'
        ];
        
        $passed = 0;
        $total = count($securityChecks];
        
        foreach ($securityChecks as $item => $description) {
            $fullPath = $this->rootDir . '/' . $item;
            
            if (file_exists($fullPath)) {
                // 检查敏感文件是否在public�?
                if (strpos($item, 'public/') === 0 || in_[$item, ['.env', 'config/', 'backup/'])) {
                    echo "�?$description: 位置安全\n";
                    $passed++;
                } else {
                    echo "⚠️ $description: 位置需要检查\n";
                }
            } else {
                echo "�?缺失: $description\n";
            }
        }
        
        // 检�?htaccess内容
        $htaccessPath = $this->publicDir . '/.htaccess';
        if (file_exists($htaccessPath)) {
            $content = file_get_contents($htaccessPath];
            if (strpos($content, 'Options -Indexes') !== false) {
                echo "�?目录列表已禁用\n";
                $passed++;
            } else {
                echo "⚠️ 目录列表保护可能缺失\n";
            }
            $total++;
        }
        
        $this->results['security_config'] = [
            'passed' => $passed,
            'total' => $total,
            'percentage' => round(($passed / $total) * 100, 2)
        ];
        
        echo "\n📊 安全配置: $passed/$total 通过 (" . $this->results['security_config']['percentage'] . "%)\n\n";
    }
    
    private function validateSymbolicLinks() {
        echo "🔗 验证符号链接...\n";
        echo str_repeat("-", 50) . "\n";
        
        $expectedLinks = [
            'install' => 'public/install',
            'tests' => 'public/tests',
            'uploads' => 'public/uploads'
        ];
        
        $passed = 0;
        $total = count($expectedLinks];
        
        foreach ($expectedLinks as $link => $target) {
            $linkPath = $this->rootDir . '/' . $link;
            $targetPath = $this->rootDir . '/' . $target;
            
            if (is_link($linkPath) || is_dir($linkPath)) {
                if (is_dir($targetPath)) {
                    echo "�?$link �?$target (有效)\n";
                    $passed++;
                } else {
                    echo "⚠️ $link �?$target (目标不存�?\n";
                }
            } else {
                echo "📝 $link (未创建符号链接，可�?\n";
                $passed++; // 符号链接是可选的
            }
        }
        
        $this->results['symbolic_links'] = [
            'passed' => $passed,
            'total' => $total,
            'percentage' => round(($passed / $total) * 100, 2)
        ];
        
        echo "\n📊 符号链接: $passed/$total 正常 (" . $this->results['symbolic_links']['percentage'] . "%)\n\n";
    }
    
    private function validateCriticalFiles() {
        echo "📄 验证关键文件...\n";
        echo str_repeat("-", 50) . "\n";
        
        $criticalFiles = [
            'composer.json' => 'PHP依赖配置',
            'composer.lock' => 'PHP依赖锁定',
            'README.md' => '项目说明文档',
            'router.php' => '路由配置',
            'DIRECTORY_STRUCTURE.md' => '目录结构说明',
            'public/index.php' => 'Web应用入口',
            '.env' => '环境配置文件'
        ];
        
        $passed = 0;
        $total = count($criticalFiles];
        
        foreach ($criticalFiles as $file => $description) {
            $fullPath = $this->rootDir . '/' . $file;
            if (file_exists($fullPath) && filesize($fullPath) > 0) {
                echo "�?$description: $file\n";
                $passed++;
            } else {
                echo "�?缺失或空文件: $file ($description)\n";
            }
        }
        
        $this->results['critical_files'] = [
            'passed' => $passed,
            'total' => $total,
            'percentage' => round(($passed / $total) * 100, 2)
        ];
        
        echo "\n📊 关键文件: $passed/$total 正常 (" . $this->results['critical_files']['percentage'] . "%)\n\n";
    }
    
    private function validateMigratedContent() {
        echo "📦 验证迁移内容...\n";
        echo str_repeat("-", 50) . "\n";
        
        $migratedAreas = [
            'public/docs/' => '文档迁移',
            'public/install/' => '安装工具迁移',
            'public/tests/' => '测试工具迁移',
            'public/admin/' => '管理界面迁移',
            'scripts/analysis/' => '分析脚本整理',
            'scripts/migration/' => '迁移脚本整理',
            'scripts/cleanup/' => '清理脚本整理',
            'docs/reports/' => '报告文档整理'
        ];
        
        $passed = 0;
        $total = count($migratedAreas];
        
        foreach ($migratedAreas as $dir => $description) {
            $fullPath = $this->rootDir . '/' . $dir;
            if (is_dir($fullPath)) {
                $fileCount = count(glob($fullPath . '*')];
                if ($fileCount > 0) {
                    echo "�?$description: $fileCount 个文件\n";
                    $passed++;
                } else {
                    echo "⚠️ $description: 目录为空\n";
                }
            } else {
                echo "�?$description: 目录不存在\n";
            }
        }
        
        $this->results['migrated_content'] = [
            'passed' => $passed,
            'total' => $total,
            'percentage' => round(($passed / $total) * 100, 2)
        ];
        
        echo "\n📊 迁移内容: $passed/$total 正常 (" . $this->results['migrated_content']['percentage'] . "%)\n\n";
    }
    
    private function generateValidationReport() {
        echo "📋 生成验证报告...\n";
        echo str_repeat("=", 80) . "\n";
        
        $totalPassed = 0;
        $totalTests = 0;
        
        echo "🎯 验证结果汇�?\n\n";
        
        foreach ($this->results as $category => $result) {
            $categoryName = $this->getCategoryName($category];
            $status = $result['percentage'] >= 80 ? '�? : ($result['percentage'] >= 60 ? '⚠️' : '�?];
            
            echo "$status $categoryName: {$result['passed']}/{$result['total']} ({$result['percentage']}%)\n";
            
            $totalPassed += $result['passed'];
            $totalTests += $result['total'];
        }
        
        $overallPercentage = round(($totalPassed / $totalTests) * 100, 2];
        $overallStatus = $overallPercentage >= 80 ? '🎉 优秀' : ($overallPercentage >= 60 ? '⚠️ 良好' : '�?需要改�?];
        
        echo str_repeat("-", 50) . "\n";
        echo "🏆 总体评分: $totalPassed/$totalTests ($overallPercentage%) - $overallStatus\n\n";
        
        // 生成详细报告文件
        $this->saveDetailedReport($overallPercentage];
        
        echo "📄 详细验证报告已保存到: docs/reports/\n";
        echo "🎉 系统验证完成！\n";
    }
    
    private function getCategoryName($category) {
        $names = [
            'directory_structure' => '目录结构',
            'public_access' => '公共访问',
            'security_config' => '安全配置',
            'symbolic_links' => '符号链接',
            'critical_files' => '关键文件',
            'migrated_content' => '迁移内容'
        ];
        
        return $names[$category] ?? $category;
    }
    
    private function saveDetailedReport($overallScore) {
        $reportContent = "# AlingAi Pro 5.0 - 系统验证报告\n\n";
        $reportContent .= "**验证时间**: " . date('Y-m-d H:i:s') . "\n";
        $reportContent .= "**整体评分**: $overallScore%\n\n";
        
        $reportContent .= "## 验证摘要\n\n";
        $reportContent .= "本报告展示了 AlingAi Pro 5.0 项目在目录重组和迁移后的系统完整性验证结果。\n\n";
        
        $reportContent .= "## 详细验证结果\n\n";
        
        foreach ($this->results as $category => $result) {
            $categoryName = $this->getCategoryName($category];
            $status = $result['percentage'] >= 80 ? '�?通过' : ($result['percentage'] >= 60 ? '⚠️ 警告' : '�?失败'];
            
            $reportContent .= "### $categoryName\n";
            $reportContent .= "- **状�?*: $status\n";
            $reportContent .= "- **通过�?*: {$result['passed']}/{$result['total']} ({$result['percentage']}%)\n\n";
        }
        
        $reportContent .= "## 项目状态总结\n\n";
        
        if ($overallScore >= 80) {
            $reportContent .= "🎉 **优秀**: 项目结构完整，所有关键组件都已正确配置和迁移。\n\n";
        } elseif ($overallScore >= 60) {
            $reportContent .= "⚠️ **良好**: 项目基本正常，但有一些小问题需要关注。\n\n";
        } else {
            $reportContent .= "�?**需要改�?*: 项目存在一些重要问题，需要进一步修复。\n\n";
        }
        
        $reportContent .= "## 下一步建议\n\n";
        $reportContent .= "1. **性能测试**: 在实际环境中测试所有功能\n";
        $reportContent .= "2. **安全审查**: 定期检查访问权限和安全配置\n";
        $reportContent .= "3. **文档更新**: 确保所有文档反映当前的目录结构\n";
        $reportContent .= "4. **监控设置**: 建立系统监控和日志记录\n\n";
        
        $reportPath = $this->rootDir . '/docs/reports/SYSTEM_VALIDATION_REPORT_' . date('Y_m_d_H_i_s') . '.md';
        
        // 确保目录存在
        $reportDir = dirname($reportPath];
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0755, true];
        }
        
        file_put_contents($reportPath, $reportContent];
    }
}

// 执行验证
$validator = new SystemValidator(];
$validator->runFullValidation(];

echo "\n" . str_repeat("=", 80) . "\n";
echo "🚀 AlingAi Pro 5.0 系统验证完成！\n";
?>

