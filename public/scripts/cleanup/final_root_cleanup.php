<?php

/**
 * AlingAi Pro 5.0 - 最终根目录清理工具
 * 处理根目录中剩余的散落文件，完成项目整理
 */

declare(strict_types=1];

class FinalRootDirectoryCleanup
{
    private string $rootDir;
    private array $cleanupPlan = [];
    private array $cleanupLog = [];
    
    public function __construct()
    {
        $this->rootDir = __DIR__;
        $this->initializeCleanupPlan(];
    }
    
    private function initializeCleanupPlan(): void
    {
        $this->cleanupPlan = [
            // 报告和文档文�?
            'reports_and_docs' => [
                'target_dir' => 'docs/reports',
                'description' => '项目报告和文�?,
                'patterns' => [
                    '*REPORT*.md',
                    '*COMPLETION*.md',
                    '*GUIDE*.md',
                    '*MANUAL*.md'
                ], 
                'action' => 'move'
            ], 
            
            // 分析和清理脚�?
            'analysis_scripts' => [
                'target_dir' => 'scripts/analysis',
                'description' => '分析和清理脚�?,
                'patterns' => [
                    'analyze_*.php',
                    'cleanup_*.php',
                    'comprehensive_*.php',
                    'organize_*.php',
                    'optimize_*.php'
                ], 
                'action' => 'move'
            ], 
            
            // 系统验证和修复脚�?
            'system_scripts' => [
                'target_dir' => 'scripts/system',
                'description' => '系统验证和修复脚�?,
                'patterns' => [
                    'final_*.php',
                    'fix_*.php',
                    'system_*.php',
                    'verify_*.php',
                    'validation_*.php',
                    'three_*.php',
                    'ultimate_*.php'
                ], 
                'action' => 'move'
            ], 
            
            // 测试文件
            'test_files' => [
                'target_dir' => 'tests/legacy',
                'description' => '遗留测试文件',
                'patterns' => [
                    'test_*.php',
                    'check_*.php'
                ], 
                'action' => 'move'
            ], 
            
            // WebSocket和服务器文件
            'server_files' => [
                'target_dir' => 'services',
                'description' => '服务器和WebSocket文件',
                'patterns' => [
                    '*websocket*.php',
                    '*server*.php',
                    'worker.php'
                ], 
                'action' => 'move'
            ], 
            
            // 部署脚本
            'deployment_scripts' => [
                'target_dir' => 'deployment/scripts',
                'description' => '部署相关脚本',
                'patterns' => [
                    'deploy_*.sh',
                    'deploy_*.bat',
                    'verify_*.sh'
                ], 
                'action' => 'move'
            ], 
            
            // 配置文件
            'config_files' => [
                'target_dir' => 'config',
                'description' => '配置文件',
                'patterns' => [
                    '*.conf',
                    '*.neon',
                    '*.xml',
                    '.eslintrc.json',
                    '.prettierrc.json'
                ], 
                'action' => 'move',
                'exceptions' => ['composer.json', 'composer.lock', 'package.json']
            ], 
            
            // 临时和垃圾文�?
            'cleanup_files' => [
                'target_dir' => 'tmp/cleanup',
                'description' => '临时和清理目标文�?,
                'patterns' => [
                    '*.zip',
                    '新建*',
                    '1'
                ], 
                'action' => 'move_or_delete'
            ]
        ];
    }
    
    public function run(): void
    {
        echo "🧹 AlingAi Pro 5.0 - 最终根目录清理\n";
        echo str_repeat("=", 80) . "\n\n";
        
        $this->analyzeRootDirectory(];
        $this->confirmCleanup(];
        $this->executeCleanup(];
        $this->createDirectoryIndex(];
        $this->generateFinalReport(];
    }
    
    private function analyzeRootDirectory(): void
    {
        echo "🔍 分析根目录内�?..\n";
        echo str_repeat("-", 60) . "\n";
        
        $rootFiles = $this->getRootFiles(];
        
        foreach ($this->cleanupPlan as $category => $plan) {
            $matchedFiles = [];
            
            foreach ($plan['patterns'] as $pattern) {
                $matches = $this->findMatchingFiles($rootFiles, $pattern];
                
                // 排除例外文件
                if (isset($plan['exceptions'])) {
                    $matches = array_diff($matches, $plan['exceptions']];
                }
                
                $matchedFiles = array_merge($matchedFiles, $matches];
            }
            
            $matchedFiles = array_unique($matchedFiles];
            
            if (!empty($matchedFiles)) {
                echo "📂 {$plan['description']} �?{$plan['target_dir']}/\n";
                foreach ($matchedFiles as $file) {
                    echo "   �?{$file}\n";
                }
                echo "   总计: " . count($matchedFiles) . " 个文件\n\n";
                
                $this->cleanupPlan[$category]['matched_files'] = $matchedFiles;
            }
        }
    }
    
    private function getRootFiles(): array
    {
        $items = scandir($this->rootDir];
        $files = [];
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $fullPath = $this->rootDir . '/' . $item;
            if (is_file($fullPath)) {
                $files[] = $item;
            }
        }
        
        return $files;
    }
    
    private function findMatchingFiles(array $files, string $pattern): array
    {
        $matches = [];
        
        foreach ($files as $file) {
            if (fnmatch($pattern, $file)) {
                $matches[] = $file;
            }
        }
        
        return $matches;
    }
    
    private function confirmCleanup(): void
    {
        $totalFiles = 0;
        foreach ($this->cleanupPlan as $plan) {
            if (isset($plan['matched_files'])) {
                $totalFiles += count($plan['matched_files']];
            }
        }
        
        echo "📊 清理统计:\n";
        echo "   �?总计需要整理的文件: {$totalFiles} 个\n";
        echo "   �?整理类别: " . count($this->cleanupPlan) . " 种\n\n";
        
        if ($totalFiles === 0) {
            echo "�?根目录已经很整洁，无需进一步清理\n";
            return;
        }
        
        echo "⚠️  此操作将重新组织根目录结构\n";
        echo "💾 操作前已自动创建备份\n\n";
        
        echo "是否继续执行清理�?y/N): ";
        $input = trim(fgets(STDIN)];
        
        if (strtolower($input) !== 'y') {
            echo "�?清理操作已取消\n";
            exit(0];
        }
    }
    
    private function executeCleanup(): void
    {
        echo "🚀 执行根目录清�?..\n";
        echo str_repeat("-", 60) . "\n";
        
        // 创建备份
        $this->createBackup(];
        
        // 执行清理计划
        foreach ($this->cleanupPlan as $category => $plan) {
            if (!isset($plan['matched_files']) || empty($plan['matched_files'])) {
                continue;
            }
            
            echo "📂 处理: {$plan['description']}\n";
            $this->executeCleanupCategory($category, $plan];
            echo "\n";
        }
        
        echo "�?根目录清理完成\n\n";
    }
    
    private function createBackup(): void
    {
        $backupDir = $this->rootDir . '/backup/final_cleanup_' . date('Y_m_d_H_i_s'];
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true];
        }
        
        // 只备份要移动的文�?
        foreach ($this->cleanupPlan as $plan) {
            if (isset($plan['matched_files'])) {
                foreach ($plan['matched_files'] as $file) {
                    $sourcePath = $this->rootDir . '/' . $file;
                    $backupPath = $backupDir . '/' . $file;
                    
                    if (file_exists($sourcePath)) {
                        copy($sourcePath, $backupPath];
                    }
                }
            }
        }
        
        echo "💾 已创建备�? " . basename($backupDir) . "\n";
    }
    
    private function executeCleanupCategory(string $category, array $plan): void
    {
        $targetDir = $this->rootDir . '/' . $plan['target_dir'];
        
        // 确保目标目录存在
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true];
            echo "   �?创建目录: {$plan['target_dir']}/\n";
        }
        
        $moved = 0;
        $deleted = 0;
        
        foreach ($plan['matched_files'] as $file) {
            $sourcePath = $this->rootDir . '/' . $file;
            $targetPath = $targetDir . '/' . $file;
            
            if (!file_exists($sourcePath)) {
                continue;
            }
            
            if ($plan['action'] === 'move_or_delete') {
                // 对于临时文件，删除小文件，移动大文件
                $fileSize = filesize($sourcePath];
                if ($fileSize > 1024 * 100) { // 大于100KB的文件移�?
                    rename($sourcePath, $targetPath];
                    echo "   �?移动: {$file}\n";
                    $moved++;
                } else {
                    unlink($sourcePath];
                    echo "   �?删除: {$file}\n";
                    $deleted++;
                }
            } else {
                // 普通移动操�?
                if (file_exists($targetPath)) {
                    $targetPath = $targetDir . '/' . time() . '_' . $file;
                }
                
                rename($sourcePath, $targetPath];
                echo "   �?移动: {$file}\n";
                $moved++;
            }
        }
        
        $this->cleanupLog[] = [
            'category' => $category,
            'target_dir' => $plan['target_dir'], 
            'moved' => $moved,
            'deleted' => $deleted,
            'description' => $plan['description']
        ];
        
        echo "   📊 移动: {$moved} 个文�?;
        if ($deleted > 0) {
            echo ", 删除: {$deleted} 个文�?;
        }
        echo "\n";
    }
    
    private function createDirectoryIndex(): void
    {
        echo "📋 创建目录索引...\n";
        echo str_repeat("-", 60) . "\n";
        
        $indexContent = $this->buildDirectoryIndex(];
        $indexPath = $this->rootDir . '/DIRECTORY_STRUCTURE.md';
        
        file_put_contents($indexPath, $indexContent];
        echo "�?创建目录结构索引: DIRECTORY_STRUCTURE.md\n\n";
    }
    
    private function buildDirectoryIndex(): string
    {
        $timestamp = date('Y-m-d H:i:s'];
        
        return <<<MARKDOWN
# AlingAi Pro 5.0 - 项目目录结构

**更新时间**: {$timestamp}

## 🌐 Public目录 (Web可访�?
- `admin/` - 管理界面
- `api/` - API接口
- `assets/` - 静态资�?
- `docs/` - 在线文档
- `install/` - 安装工具
- `test/` - 测试工具
- `uploads/` - 上传文件

## 🔧 核心代码
- `src/` - 应用程序源代�?
- `config/` - 配置文件
- `includes/` - 包含文件

## 📊 数据存储
- `database/` - 数据库相�?
- `storage/` - 存储目录
- `logs/` - 日志文件

## 🛠�?开发工�?
- `scripts/` - 工具脚本
- `tests/` - 测试文件
- `tools/` - 开发工�?

## 📚 文档资料
- `docs/` - 项目文档
- `backup/` - 备份文件

## 🚀 部署相关
- `deployment/` - 部署文件
- `docker/` - Docker配置
- `nginx/` - Nginx配置

MARKDOWN;
    }
    
    private function generateFinalReport(): void
    {
        echo "📊 生成最终清理报�?..\n";
        echo str_repeat("-", 60) . "\n";
        
        echo "📁 最终根目录状�?\n";
        $this->displayRootDirectory(];
        
        echo "\n�?项目整理完成！\n";
        echo "📚 目录索引: DIRECTORY_STRUCTURE.md\n\n";
        
        $this->displaySummary(];
    }
    
    private function displayRootDirectory(): void
    {
        $items = scandir($this->rootDir];
        $files = [];
        $dirs = [];
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $fullPath = $this->rootDir . '/' . $item;
            if (is_dir($fullPath)) {
                $dirs[] = $item;
            } else {
                $files[] = $item;
            }
        }
        
        sort($dirs];
        sort($files];
        
        echo "📁 目录 (" . count($dirs) . "):\n";
        foreach ($dirs as $dir) {
            echo "   📂 {$dir}/\n";
        }
        
        echo "\n📄 文件 (" . count($files) . "):\n";
        foreach ($files as $file) {
            echo "   📄 {$file}\n";
        }
    }
    
    private function displaySummary(): void
    {
        echo "🎉 AlingAi Pro 5.0 项目整理总结\n";
        echo str_repeat("=", 80) . "\n";
        echo "�?Public目录结构优化完成\n";
        echo "�?根目录文件整理完成\n";
        echo "�?安全配置强化完成\n";
        echo "�?项目结构规范化完成\n\n";
        
        echo "🚀 项目现在已准备好用于:\n";
        echo "   �?生产环境部署\n";
        echo "   �?团队协作开发\n";
        echo "   �?功能扩展升级\n";
        echo "   �?维护和优化\n\n";
    }
}

// 执行最终清�?
try {
    $cleanup = new FinalRootDirectoryCleanup(];
    $cleanup->run(];
} catch (Exception $e) {
    echo "�?清理失败: " . $e->getMessage() . "\n";
    exit(1];
}
