<?php

/**
 * AlingAi Pro 5.0 - 高优先级文件清理脚本
 * 清理已过时、重复和临时的PHP文件
 * 
 * 安全特性:
 * 1. 创建备份
 * 2. 确认删除
 * 3. 生成详细报告
 * 4. 分批处理
 */

declare(strict_types=1);

class HighPriorityFilesCleanup
{
    private string $rootDir;
    private array $highPriorityFiles;
    private array $mediumPriorityFiles;
    private array $deletedFiles = [];
    private array $backedUpFiles = [];
    private string $backupDir;
    
    public function __construct()
    {
        $this->rootDir = __DIR__;
        $this->backupDir = $this->rootDir . '/backup/high_priority_cleanup_' . date('Y_m_d_H_i_s');
        
        // 高优先级清理文件
        $this->highPriorityFiles = [
            // 编译修复文件 (已过时)
            'compilation_fixes' => [
                'complete_three_compilation_fix.php',
                'final_three_complete_compilation_fix.php', 
                'compilation_fix_complete_report.php',
            ],
            
            // 临时验证文件 (开发阶段产物)
            'temp_validation' => [
                'extended_system_verification.php',
                'final_validation_fix.php',
                'websocket_system_validation.php',
            ],
            
            // 重复的错误处理修复 (保留最新版本)
            'old_error_fixes' => [
                'final_error_handling_fix.php',
                'fix_coordinator_complete.php',
                'fix_coordinator_syntax.php',
                'fix_environment.php',
                'fix_error_handling_config.php',
                'fix_three_compilation_validator.php',
            ],
        ];
        
        // 中等优先级清理文件
        $this->mediumPriorityFiles = [
            // 多余的WebSocket服务器
            'old_websocket' => [
                'websocket_simple.php',
                'websocket_simple_react.php',
                'simple_websocket_server.php',
                'start_websocket.php',
                'start_websocket_server.php',
            ],
            
            // 重复的迁移脚本
            'old_migrations' => [
                'run_ai_agent_migration.php',
                'run_enhancement_migration.php',
                'run_fixed_migration.php',
                'simple_security_migration.php',
                'sqlite_security_migration.php',
                'run_migration_009.php',
            ],
            
            // 临时系统文件
            'temp_system' => [
                'test_admin_system.php', // 已移动到 public/admin/
                'verify_syntax.php',
                'final_verification_report.php',
            ],
        ];
    }
    
    public function run(): void
    {
        echo "🧹 AlingAi Pro 5.0 - 高优先级文件清理工具\n";
        echo str_repeat("=", 70) . "\n\n";
        
        $this->createBackupDirectory();
        $this->analyzeFiles();
        $this->requestConfirmation();
        $this->performCleanup();
        $this->generateReport();
    }
    
    private function createBackupDirectory(): void
    {
        echo "📦 创建备份目录...\n";
        
        if (!is_dir(dirname($this->backupDir))) {
            mkdir(dirname($this->backupDir), 0755, true);
        }
        mkdir($this->backupDir, 0755, true);
        
        echo "✅ 备份目录创建: " . basename($this->backupDir) . "\n\n";
    }
    
    private function analyzeFiles(): void
    {
        echo "📋 分析待清理文件...\n";
        echo str_repeat("-", 70) . "\n";
        
        echo "🔥 **高优先级文件** (建议立即删除):\n\n";
        
        foreach ($this->highPriorityFiles as $category => $files) {
            $categoryName = $this->getCategoryName($category);
            echo "📁 {$categoryName}:\n";
            
            foreach ($files as $file) {
                $filePath = $this->rootDir . '/' . $file;
                if (file_exists($filePath)) {
                    $size = $this->formatBytes(filesize($filePath));
                    echo "   ✅ {$file} ({$size})\n";
                } else {
                    echo "   ❌ {$file} (文件不存在)\n";
                }
            }
            echo "\n";
        }
        
        echo "🟡 **中等优先级文件** (可选择删除):\n\n";
        
        foreach ($this->mediumPriorityFiles as $category => $files) {
            $categoryName = $this->getCategoryName($category);
            echo "📁 {$categoryName}:\n";
            
            foreach ($files as $file) {
                $filePath = $this->rootDir . '/' . $file;
                if (file_exists($filePath)) {
                    $size = $this->formatBytes(filesize($filePath));
                    echo "   ✅ {$file} ({$size})\n";
                } else {
                    echo "   ❌ {$file} (文件不存在)\n";
                }
            }
            echo "\n";
        }
    }
    
    private function requestConfirmation(): void
    {
        echo str_repeat("-", 70) . "\n";
        echo "❓ 请选择清理级别:\n";
        echo "   1. 仅高优先级文件 (推荐)\n";
        echo "   2. 高优先级 + 中等优先级文件\n";
        echo "   3. 取消操作\n";
        echo "\n请输入选择 (1-3): ";
        
        $choice = trim(fgets(STDIN));
        
        switch ($choice) {
            case '1':
                echo "✅ 将清理高优先级文件\n\n";
                $this->cleanHighPriority();
                break;
                
            case '2':
                echo "✅ 将清理高优先级和中等优先级文件\n\n";
                $this->cleanAllFiles();
                break;
                
            case '3':
            default:
                echo "❌ 操作已取消\n";
                exit(0);
        }
    }
    
    private function cleanHighPriority(): void
    {
        $this->cleanupFileCategories($this->highPriorityFiles, '高优先级');
    }
    
    private function cleanAllFiles(): void
    {
        $this->cleanupFileCategories($this->highPriorityFiles, '高优先级');
        $this->cleanupFileCategories($this->mediumPriorityFiles, '中等优先级');
    }
    
    private function cleanupFileCategories(array $categories, string $priority): void
    {
        echo "🗑️  清理{$priority}文件...\n";
        
        foreach ($categories as $category => $files) {
            $categoryName = $this->getCategoryName($category);
            echo "\n📁 处理 {$categoryName}:\n";
            
            foreach ($files as $file) {
                $this->processFile($file, $category);
            }
        }
    }
    
    private function processFile(string $file, string $category): void
    {
        $filePath = $this->rootDir . '/' . $file;
        
        echo "   处理: {$file}... ";
        
        if (!file_exists($filePath)) {
            echo "⏭️  文件不存在\n";
            return;
        }
        
        try {
            // 创建备份
            $backupPath = $this->backupDir . '/' . $category . '_' . $file;
            $backupCategoryDir = dirname($backupPath);
            
            if (!is_dir($backupCategoryDir)) {
                mkdir($backupCategoryDir, 0755, true);
            }
            
            if (copy($filePath, $backupPath)) {
                $this->backedUpFiles[] = [
                    'original' => $file,
                    'backup' => $backupPath,
                    'category' => $category,
                    'size' => filesize($filePath)
                ];
                
                // 删除原文件
                if (unlink($filePath)) {
                    echo "✅ 删除成功\n";
                    $this->deletedFiles[] = [
                        'file' => $file,
                        'category' => $category,
                        'size' => filesize($backupPath)
                    ];
                } else {
                    echo "❌ 删除失败\n";
                }
            } else {
                echo "❌ 备份失败\n";
            }
            
        } catch (Exception $e) {
            echo "❌ 错误: " . $e->getMessage() . "\n";
        }
    }
    
    private function performCleanup(): void
    {
        // 清理方法在上面的流程中已经调用
        echo "\n📊 清理统计:\n";
        echo "   ✅ 成功删除: " . count($this->deletedFiles) . " 个文件\n";
        echo "   📦 创建备份: " . count($this->backedUpFiles) . " 个文件\n";
        
        if (!empty($this->deletedFiles)) {
            $totalSize = array_sum(array_column($this->deletedFiles, 'size'));
            echo "   💾 释放空间: " . $this->formatBytes($totalSize) . "\n";
        }
    }
    
    private function generateReport(): void
    {
        $reportFile = $this->rootDir . '/HIGH_PRIORITY_CLEANUP_REPORT_' . date('Y_m_d_H_i_s') . '.md';
        
        $report = "# AlingAi Pro 5.0 - 高优先级文件清理报告\n\n";
        $report .= "## 清理概览\n";
        $report .= "- **清理时间**: " . date('Y年m月d日 H:i:s') . "\n";
        $report .= "- **成功删除**: " . count($this->deletedFiles) . " 个文件\n";
        $report .= "- **备份文件**: " . count($this->backedUpFiles) . " 个文件\n";
        $report .= "- **备份位置**: `" . basename($this->backupDir) . "`\n\n";
        
        if (!empty($this->deletedFiles)) {
            $totalSize = array_sum(array_column($this->deletedFiles, 'size'));
            $report .= "- **释放空间**: " . $this->formatBytes($totalSize) . "\n\n";
            
            $report .= "## ✅ 已删除文件\n\n";
            
            $byCategory = [];
            foreach ($this->deletedFiles as $file) {
                $byCategory[$file['category']][] = $file;
            }
            
            foreach ($byCategory as $category => $files) {
                $categoryName = $this->getCategoryName($category);
                $report .= "### {$categoryName}\n\n";
                
                foreach ($files as $file) {
                    $report .= "- `{$file['file']}` ({$this->formatBytes($file['size'])})\n";
                }
                $report .= "\n";
            }
        }
        
        if (!empty($this->backedUpFiles)) {
            $report .= "## 📦 备份文件位置\n\n";
            $report .= "所有删除的文件都已备份到: `{$this->backupDir}`\n\n";
            $report .= "如需恢复，可以从备份目录复制文件。\n\n";
        }
        
        $report .= "## 🔧 清理效果\n\n";
        $report .= "✅ **已解决的问题**:\n";
        $report .= "- 移除了过时的编译修复文件\n";
        $report .= "- 清理了临时验证脚本\n";
        $report .= "- 删除了重复的错误处理文件\n";
        if (count($this->deletedFiles) > count($this->getAllHighPriorityFiles())) {
            $report .= "- 整理了多余的WebSocket服务器\n";
            $report .= "- 归档了已完成的迁移脚本\n";
        }
        $report .= "\n";
        
        $report .= "✅ **当前状态**:\n";
        $report .= "- 根目录更加整洁\n";
        $report .= "- 减少了文件混淆\n";
        $report .= "- 提高了项目可维护性\n\n";
        
        $report .= "## 📋 后续建议\n\n";
        $report .= "1. **验证功能**: 确认系统所有功能正常工作\n";
        $report .= "2. **测试访问**: 访问 `http://localhost:8000/tools-index.html` 验证工具可用性\n";
        $report .= "3. **清理测试**: 考虑将测试文件移动到专门的 `tests/` 目录\n";
        $report .= "4. **文档更新**: 更新项目文档，移除对已删除文件的引用\n\n";
        
        $report .= "---\n";
        $report .= "*报告生成时间: " . date('Y年m月d日 H:i:s') . "*\n";
        $report .= "*AlingAi Pro 5.0 政企融合智能办公系统*\n";
        
        file_put_contents($reportFile, $report);
        
        echo "\n📋 详细报告已生成: " . basename($reportFile) . "\n";
        echo "📦 备份位置: " . basename($this->backupDir) . "\n";
        echo "🎉 高优先级文件清理完成！\n\n";
        
        echo "🔗 验证链接:\n";
        echo "  - 管理后台: http://localhost:8000/admin/\n";
        echo "  - 工具目录: http://localhost:8000/tools-index.html\n";
        echo "  - 健康检查: http://localhost:8000/monitor/health.php\n";
    }
    
    private function getAllHighPriorityFiles(): array
    {
        return array_merge(...array_values($this->highPriorityFiles));
    }
    
    private function getCategoryName(string $category): string
    {
        $names = [
            'compilation_fixes' => '编译修复文件',
            'temp_validation' => '临时验证文件',
            'old_error_fixes' => '过时错误修复',
            'old_websocket' => '多余WebSocket服务器',
            'old_migrations' => '已完成迁移脚本',
            'temp_system' => '临时系统文件',
        ];
        
        return $names[$category] ?? $category;
    }
    
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

// 执行清理
try {
    $cleanup = new HighPriorityFilesCleanup();
    $cleanup->run();
} catch (Exception $e) {
    echo "❌ 清理过程中发生错误: " . $e->getMessage() . "\n";
    exit(1);
}
