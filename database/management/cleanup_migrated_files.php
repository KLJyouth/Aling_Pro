<?php

/**
 * AlingAi Pro 5.0 - 已迁移文件清理脚本
 * 检测并安全删除根目录中已经迁移到 public 文件夹的文件
 * 
 * 功能:
 * 1. 检测已迁移的文件
 * 2. 创建备份列表
 * 3. 验证目标文件存在
 * 4. 安全删除原文件
 * 5. 生成清理报告
 */

declare(strict_types=1);

class MigratedFilesCleanup
{
    private string $rootDir;
    private string $publicDir;
    private array $migratedFiles;
    private array $deletedFiles = [];
    private array $skippedFiles = [];
    private array $backupFiles = [];
    
    public function __construct()
    {
        $this->rootDir = __DIR__;
        $this->publicDir = $this->rootDir . '/public';
        
        // 根据迁移报告定义已迁移的文件映射
        $this->migratedFiles = [
            // API工具 (public/api/)
            'api_server.php' => 'public/api/server.php',
            'simple_api_server.php' => 'public/api/simple-server.php',
            'clean_api_server.php' => 'public/api/clean-server.php',
            'api_validation.php' => 'public/api/validation.php',
            'api_performance_validation.php' => 'public/api/performance-validation.php',
            
            // 测试工具 (public/test/)
            'comprehensive_api_test.php' => 'public/test/api-comprehensive.php',
            'simple_api_test.php' => 'public/test/api-simple.php',
            'direct_api_test.php' => 'public/test/api-direct.php',
            'http_api_test.php' => 'public/test/api-http.php',
            'integration_test.php' => 'public/test/integration.php',
            'performance_test.php' => 'public/test/performance.php',
            'simple_connection_test.php' => 'public/test/connection.php',
            'simple_route_test.php' => 'public/test/route.php',
            'comprehensive_system_test_v5.php' => 'public/test/system-comprehensive-v5.php',
            'complete_system_test.php' => 'public/test/system-complete.php',
            'final_integration_test.php' => 'public/test/integration-final.php',
            'frontend_integration_test.php' => 'public/test/frontend-integration.php',
            
            // 监控工具 (public/monitor/)
            'quick_health_check.php' => 'public/monitor/health.php',
            'ai_service_health_check.php' => 'public/monitor/ai-health.php',
            'performance_monitoring_health.php' => 'public/monitor/performance.php',
            'ai_service_integration_health.php' => 'public/monitor/ai-integration.php',
            
            // 系统工具 (public/tools/)
            'database_management.php' => 'public/tools/database-management.php',
            'cache_optimizer.php' => 'public/tools/cache-optimizer.php',
            'performance_optimizer.php' => 'public/tools/performance-optimizer.php',
            
            // 安装工具 (public/install/)
            'install/test_server.php' => 'public/install/test-server.php',
            'install/test_api_cli.php' => 'public/install/test-api-cli.php',
        ];
    }
    
    public function run(): void
    {
        echo "🧹 AlingAi Pro 5.0 - 已迁移文件清理工具\n";
        echo str_repeat("=", 60) . "\n";
        
        $this->analyzeFiles();
        $this->generateBackupList();
        $this->requestConfirmation();
        $this->cleanupFiles();
        $this->generateReport();
    }
    
    private function analyzeFiles(): void
    {
        echo "📋 分析需要清理的文件...\n";
        
        foreach ($this->migratedFiles as $originalPath => $newPath) {
            $originalFile = $this->rootDir . '/' . $originalPath;
            $newFile = $this->rootDir . '/' . $newPath;
            
            echo "  检查: {$originalPath}... ";
            
            if (!file_exists($originalFile)) {
                echo "❌ 原文件不存在\n";
                continue;
            }
            
            if (!file_exists($newFile)) {
                echo "⚠️  目标文件不存在，跳过删除\n";
                $this->skippedFiles[] = [
                    'original' => $originalPath,
                    'target' => $newPath,
                    'reason' => '目标文件不存在'
                ];
                continue;
            }
            
            // 验证文件内容相似性（简单检查文件大小）
            $originalSize = filesize($originalFile);
            $newSize = filesize($newFile);
            
            if (abs($originalSize - $newSize) > 1024) { // 允许1KB差异
                echo "⚠️  文件大小差异较大，跳过删除\n";
                $this->skippedFiles[] = [
                    'original' => $originalPath,
                    'target' => $newPath,
                    'reason' => '文件大小差异较大'
                ];
                continue;
            }
            
            echo "✅ 可安全删除\n";
            $this->backupFiles[] = [
                'original' => $originalPath,
                'target' => $newPath,
                'original_size' => $originalSize,
                'target_size' => $newSize
            ];
        }
        
        echo "\n📊 分析结果:\n";
        echo "  ✅ 可删除文件: " . count($this->backupFiles) . " 个\n";
        echo "  ⚠️  跳过文件: " . count($this->skippedFiles) . " 个\n";
    }
    
    private function generateBackupList(): void
    {
        if (empty($this->backupFiles)) {
            return;
        }
        
        $backupDir = $this->rootDir . '/backup/migrated_files_' . date('Y_m_d_H_i_s');
        
        echo "\n📦 创建备份目录: {$backupDir}\n";
        
        if (!is_dir(dirname($backupDir))) {
            mkdir(dirname($backupDir), 0755, true);
        }
        mkdir($backupDir, 0755, true);
        
        // 创建备份清单
        $manifest = [
            'backup_time' => date('Y-m-d H:i:s'),
            'backup_reason' => 'Public folder migration cleanup',
            'files' => $this->backupFiles
        ];
        
        file_put_contents(
            $backupDir . '/backup_manifest.json',
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        
        echo "✅ 备份清单已创建\n";
    }
    
    private function requestConfirmation(): void
    {
        if (empty($this->backupFiles)) {
            echo "\n🎉 没有需要清理的文件，退出。\n";
            exit(0);
        }
        
        echo "\n" . str_repeat("-", 60) . "\n";
        echo "🗂️  将要删除的文件列表:\n";
        echo str_repeat("-", 60) . "\n";
        
        foreach ($this->backupFiles as $file) {
            echo "  📄 {$file['original']}\n";
            echo "      → 已迁移到: {$file['target']}\n";
            echo "      → 文件大小: " . $this->formatBytes($file['original_size']) . 
                 " → " . $this->formatBytes($file['target_size']) . "\n\n";
        }
        
        if (!empty($this->skippedFiles)) {
            echo "⚠️  跳过的文件:\n";
            foreach ($this->skippedFiles as $file) {
                echo "  📄 {$file['original']} - {$file['reason']}\n";
            }
            echo "\n";
        }
        
        echo str_repeat("-", 60) . "\n";
        echo "❓ 确认删除上述 " . count($this->backupFiles) . " 个文件吗？\n";
        echo "   输入 'yes' 确认删除，其他任何输入将取消操作: ";
        
        $input = trim(fgets(STDIN));
        
        if (strtolower($input) !== 'yes') {
            echo "❌ 操作已取消\n";
            exit(0);
        }
        
        echo "✅ 确认删除，开始清理...\n\n";
    }
    
    private function cleanupFiles(): void
    {
        echo "🗑️  开始删除文件...\n";
        
        foreach ($this->backupFiles as $file) {
            $originalFile = $this->rootDir . '/' . $file['original'];
            
            echo "  删除: {$file['original']}... ";
            
            try {
                if (unlink($originalFile)) {
                    echo "✅ 成功\n";
                    $this->deletedFiles[] = $file;
                } else {
                    echo "❌ 失败\n";
                }
            } catch (Exception $e) {
                echo "❌ 错误: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n📊 删除结果:\n";
        echo "  ✅ 成功删除: " . count($this->deletedFiles) . " 个文件\n";
        echo "  ❌ 删除失败: " . (count($this->backupFiles) - count($this->deletedFiles)) . " 个文件\n";
    }
    
    private function generateReport(): void
    {
        $reportFile = $this->rootDir . '/CLEANUP_MIGRATED_FILES_REPORT_' . date('Y_m_d_H_i_s') . '.md';
        
        $report = "# AlingAi Pro 5.0 - 已迁移文件清理报告\n\n";
        $report .= "## 清理概览\n";
        $report .= "- **清理时间**: " . date('Y年m月d日 H:i:s') . "\n";
        $report .= "- **成功删除**: " . count($this->deletedFiles) . " 个文件\n";
        $report .= "- **跳过文件**: " . count($this->skippedFiles) . " 个文件\n";
        $report .= "- **总处理**: " . count($this->migratedFiles) . " 个文件\n\n";
        
        if (!empty($this->deletedFiles)) {
            $report .= "## ✅ 成功删除的文件\n\n";
            foreach ($this->deletedFiles as $file) {
                $report .= "- `{$file['original']}` → `{$file['target']}`\n";
            }
            $report .= "\n";
        }
        
        if (!empty($this->skippedFiles)) {
            $report .= "## ⚠️ 跳过的文件\n\n";
            foreach ($this->skippedFiles as $file) {
                $report .= "- `{$file['original']}` - {$file['reason']}\n";
            }
            $report .= "\n";
        }
        
        $report .= "## 📂 清理后的目录结构\n\n";
        $report .= "所有web可访问的文件现在都位于 `public/` 目录中：\n\n";
        $report .= "```\n";
        $report .= "public/\n";
        $report .= "├── admin/          # 管理后台系统\n";
        $report .= "├── api/            # API服务器和工具\n";
        $report .= "├── test/           # 测试工具\n";
        $report .= "├── monitor/        # 监控工具\n";
        $report .= "├── tools/          # 系统管理工具\n";
        $report .= "└── install/        # 安装工具\n";
        $report .= "```\n\n";
        
        $report .= "## 🔧 后续建议\n\n";
        $report .= "1. **验证功能**: 访问 `http://localhost:8000/tools-index.html` 验证所有工具正常工作\n";
        $report .= "2. **更新文档**: 更新项目文档中的文件路径引用\n";
        $report .= "3. **清理空目录**: 检查并删除可能的空目录\n";
        $report .= "4. **配置优化**: 考虑更新web服务器配置文件\n\n";
        
        $report .= "---\n";
        $report .= "*报告生成时间: " . date('Y年m月d日 H:i:s') . "*\n";
        $report .= "*AlingAi Pro 5.0 政企融合智能办公系统*\n";
        
        file_put_contents($reportFile, $report);
        
        echo "\n📋 清理报告已生成: " . basename($reportFile) . "\n";
        echo "🎉 文件清理完成！\n\n";
        
        echo "🔗 快速访问链接:\n";
        echo "  - 工具目录: http://localhost:8000/tools-index.html\n";
        echo "  - 管理后台: http://localhost:8000/admin/\n";
        echo "  - 健康检查: http://localhost:8000/monitor/health.php\n";
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
    $cleanup = new MigratedFilesCleanup();
    $cleanup->run();
} catch (Exception $e) {
    echo "❌ 清理过程中发生错误: " . $e->getMessage() . "\n";
    exit(1);
}
