<?php

/**
 * AlingAi Pro 5.0 - 根目录文件分析和清理建议脚本
 * 分析根目录中的PHP文件，识别可能需要整理的文件
 */

declare(strict_types=1);

class RootDirectoryAnalyzer
{
    private string $rootDir;
    private array $categories = [];
    
    public function __construct()
    {
        $this->rootDir = __DIR__;
        
        // 按功能分类根目录中的文件
        $this->categories = [
            'migration_scripts' => [
                'migrate.php',
                'migrate_database.php',
                'run_ai_agent_migration.php',
                'run_enhancement_migration.php', 
                'run_final_migration.php',
                'run_fixed_migration.php',
                'run_migration_009.php',
                'simple_security_migration.php',
                'sqlite_security_migration.php',
            ],
            
            'system_initialization' => [
                'init_system.php',
                'start_system.php',
                'launch_system.php',
                'quick_start.php',
                'setup_database_structure.php',
                'setup_file_database.php',
                'setup_local_database.php',
            ],
            
            'validation_and_verification' => [
                'websocket_system_validation.php',
                'extended_system_verification.php',
                'final_system_verification.php',
                'final_validation_fix.php',
                'feature_verification.php',
                'verify_syntax.php',
                'production_compatibility_check.php',
                'production_deployment_validator.php',
            ],
            
            'error_handling_fixes' => [
                'final_error_handling_complete_fix.php',
                'final_error_handling_fix.php',
                'fix_coordinator_complete.php',
                'fix_coordinator_syntax.php',
                'fix_environment.php',
                'fix_error_handling_config.php',
                'fix_three_compilation_validator.php',
                'production_error_handler.php',
                'production_error_handler_enhanced.php',
            ],
            
            'compilation_fixes' => [
                'complete_three_compilation_fix.php',
                'final_three_complete_compilation_fix.php',
                'compilation_fix_complete_report.php',
            ],
            
            'websocket_servers' => [
                'websocket_manager.php',
                'websocket_ratchet.php',
                'websocket_react.php',
                'websocket_server.php',
                'websocket_simple.php',
                'websocket_simple_react.php',
                'simple_websocket_server.php',
                'start_websocket.php',
                'start_websocket_server.php',
            ],
            
            'performance_and_optimization' => [
                'database_optimizer.php',
                'cache_warmup.php',
                'optimize_performance_monitoring.php',
                'optimize_production.php',
                'performance_monitoring_health_check.php',
            ],
            
            'monitoring_and_backup' => [
                'backup_monitor.php',
                'database_backup.php',
                'disaster_recovery.php',
                'intelligent_monitor.php',
                'start_security_monitoring.php',
                'setup_security_monitoring_db.php',
            ],
            
            'deployment' => [
                'deploy_alingai_pro_5.php',
                'deployment_readiness.php',
                'prepare-deployment.php',
            ],
            
            'testing_files' => [
                'test_admin_system.php',
                'api_integration_complete_test.php',
                'api_integration_test.php',
                'cache_performance_test.php',
                'complete_chat_test.php',
            ],
            
            'database_setup' => [
                'create_ai_tables_direct.php',
                'create_missing_tables.php',
                'recreate_user_settings_table.php',
                'init_clean_data.php',
            ],
            
            'services_and_utilities' => [
                'ServiceContainer.php',
                'ServiceContainerSimple.php',
                'router.php',
                'worker.php',
            ],
            
            'reports_and_verification' => [
                'final_verification_report.php',
            ],
            
            'configuration' => [
                '.php-cs-fixer.php',
            ],
            
            'temporary_or_development' => [
                'cleanup_migrated_files.php', // 这个是我们刚创建的清理脚本
            ]
        ];
    }
    
    public function analyze(): void
    {
        echo "📊 AlingAi Pro 5.0 - 根目录文件分析报告\n";
        echo str_repeat("=", 70) . "\n\n";
        
        $this->displayCategorizedFiles();
        $this->checkFileUsage();
        $this->generateRecommendations();
    }
    
    private function displayCategorizedFiles(): void
    {
        echo "📁 根目录文件分类:\n";
        echo str_repeat("-", 70) . "\n";
        
        $totalFiles = 0;
        
        foreach ($this->categories as $category => $files) {
            $categoryName = $this->getCategoryDisplayName($category);
            echo "\n🗂️  {$categoryName} ({count($files)} 个文件):\n";
            
            foreach ($files as $file) {
                $filePath = $this->rootDir . '/' . $file;
                if (file_exists($filePath)) {
                    $size = $this->formatBytes(filesize($filePath));
                    echo "   ✅ {$file} ({$size})\n";
                    $totalFiles++;
                } else {
                    echo "   ❌ {$file} (文件不存在)\n";
                }
            }
        }
        
        // 检查未分类的文件
        $allFiles = glob($this->rootDir . '/*.php');
        $categorizedFiles = array_merge(...array_values($this->categories));
        $uncategorizedFiles = [];
        
        foreach ($allFiles as $file) {
            $filename = basename($file);
            if (!in_array($filename, $categorizedFiles)) {
                $uncategorizedFiles[] = $filename;
            }
        }
        
        if (!empty($uncategorizedFiles)) {
            echo "\n❓ 未分类的文件 (" . count($uncategorizedFiles) . " 个):\n";
            foreach ($uncategorizedFiles as $file) {
                $size = $this->formatBytes(filesize($this->rootDir . '/' . $file));
                echo "   📄 {$file} ({$size})\n";
            }
        }
        
        echo "\n📊 统计: 共分析 " . (count($allFiles)) . " 个PHP文件\n";
    }
    
    private function checkFileUsage(): void
    {
        echo "\n" . str_repeat("-", 70) . "\n";
        echo "🔍 文件使用情况分析:\n\n";
        
        // 检查可能的重复功能
        echo "🔄 可能重复的功能文件:\n";
        
        $duplicates = [
            'WebSocket服务器' => $this->categories['websocket_servers'],
            '迁移脚本' => $this->categories['migration_scripts'],
            '系统初始化' => $this->categories['system_initialization'],
            '错误处理修复' => $this->categories['error_handling_fixes'],
            '验证脚本' => $this->categories['validation_and_verification']
        ];
        
        foreach ($duplicates as $type => $files) {
            if (count($files) > 3) {
                echo "   ⚠️  {$type}: " . count($files) . " 个类似文件\n";
                foreach (array_slice($files, 0, 3) as $file) {
                    echo "      - {$file}\n";
                }
                if (count($files) > 3) {
                    echo "      - ... 还有 " . (count($files) - 3) . " 个\n";
                }
                echo "\n";
            }
        }
    }
    
    private function generateRecommendations(): void
    {
        echo str_repeat("-", 70) . "\n";
        echo "💡 清理建议:\n\n";
        
        // 高优先级清理建议
        echo "🔥 **高优先级** - 建议立即处理:\n\n";
        
        echo "1. **编译修复文件** (已过时):\n";
        foreach ($this->categories['compilation_fixes'] as $file) {
            echo "   📄 {$file} - 编译问题已解决，可以删除\n";
        }
        
        echo "\n2. **临时验证文件** (开发阶段产物):\n";
        $tempValidation = [
            'extended_system_verification.php',
            'final_validation_fix.php',
            'websocket_system_validation.php'
        ];
        foreach ($tempValidation as $file) {
            echo "   📄 {$file} - 验证功能已整合到管理后台\n";
        }
        
        echo "\n3. **重复的错误处理修复** (保留最新版本):\n";
        $oldErrorFixes = [
            'final_error_handling_fix.php',
            'fix_coordinator_complete.php',
            'fix_coordinator_syntax.php',
            'fix_environment.php'
        ];
        foreach ($oldErrorFixes as $file) {
            echo "   📄 {$file} - 功能已整合到新版本\n";
        }
        
        // 中等优先级建议
        echo "\n🟡 **中等优先级** - 可以考虑整理:\n\n";
        
        echo "1. **多余的WebSocket服务器** (保留主要版本):\n";
        $oldWebsocket = [
            'websocket_simple.php',
            'websocket_simple_react.php',
            'simple_websocket_server.php'
        ];
        foreach ($oldWebsocket as $file) {
            echo "   📄 {$file} - 建议保留 websocket_server.php 和 websocket_react.php\n";
        }
        
        echo "\n2. **重复的迁移脚本** (已完成迁移):\n";
        $oldMigrations = [
            'run_ai_agent_migration.php',
            'run_enhancement_migration.php',
            'run_fixed_migration.php',
            'simple_security_migration.php'
        ];
        foreach ($oldMigrations as $file) {
            echo "   📄 {$file} - 迁移已完成，可以归档\n";
        }
        
        // 低优先级建议
        echo "\n🟢 **低优先级** - 长期整理:\n\n";
        
        echo "1. **测试文件** - 移动到测试目录:\n";
        foreach ($this->categories['testing_files'] as $file) {
            if ($file !== 'test_admin_system.php') { // 管理后台测试保留
                echo "   📄 {$file} - 可移动到 tests/ 目录\n";
            }
        }
        
        echo "\n2. **数据库设置脚本** - 整合到安装程序:\n";
        foreach ($this->categories['database_setup'] as $file) {
            echo "   📄 {$file} - 可整合到统一的安装脚本\n";
        }
        
        // 建议保留的重要文件
        echo "\n✅ **建议保留的重要文件**:\n";
        $keepFiles = [
            'router.php' => '路由核心文件',
            'migrate_database.php' => '主要数据库迁移工具',
            'websocket_server.php' => '主要WebSocket服务器',
            'ServiceContainer.php' => '服务容器核心',
            'start_system.php' => '系统启动脚本',
            'backup_monitor.php' => '备份监控工具',
            'database_optimizer.php' => '数据库优化工具',
            'disaster_recovery.php' => '灾难恢复工具',
            'production_error_handler_enhanced.php' => '生产环境错误处理',
            '.php-cs-fixer.php' => 'PHP代码风格配置'
        ];
        
        foreach ($keepFiles as $file => $reason) {
            echo "   📄 {$file} - {$reason}\n";
        }
    }
    
    private function getCategoryDisplayName(string $category): string
    {
        $names = [
            'migration_scripts' => '数据库迁移脚本',
            'system_initialization' => '系统初始化脚本',
            'validation_and_verification' => '验证和确认脚本',
            'error_handling_fixes' => '错误处理修复脚本',
            'compilation_fixes' => '编译修复脚本',
            'websocket_servers' => 'WebSocket服务器',
            'performance_and_optimization' => '性能优化工具',
            'monitoring_and_backup' => '监控和备份工具',
            'deployment' => '部署脚本',
            'testing_files' => '测试文件',
            'database_setup' => '数据库设置脚本',
            'services_and_utilities' => '服务和实用工具',
            'reports_and_verification' => '报告和验证',
            'configuration' => '配置文件',
            'temporary_or_development' => '临时或开发文件'
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

// 执行分析
try {
    $analyzer = new RootDirectoryAnalyzer();
    $analyzer->analyze();
    
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "📋 分析完成！建议根据优先级逐步清理文件。\n";
    echo "💡 提示：可以创建专门的清理脚本来自动化这个过程。\n";
    
} catch (Exception $e) {
    echo "❌ 分析过程中发生错误: " . $e->getMessage() . "\n";
    exit(1);
}
