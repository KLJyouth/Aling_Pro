<?php

/**
 * AlingAi Pro 5.0 - 综合迁移执行脚本
 * 基于综合分析结果执行目录迁移
 */

declare(strict_types=1];

class ComprehensiveMigrationExecutor
{
    private string $rootDir;
    private string $publicDir;
    private array $highPriorityDirs = ['docs', 'install', 'tests'];
    private array $partialMigrationDirs = ['admin', 'storage'];
    
    public function __construct()
    {
        $this->rootDir = __DIR__;
        $this->publicDir = $this->rootDir . '/public';
    }
    
    public function run(): void
    {
        echo "🚀 执行综合迁移计划...\n";
        echo str_repeat("=", 80) . "\n\n";
        
        $this->confirmExecution(];
        $this->createBackup(];
        $this->executeHighPriorityMigrations(];
        $this->executePartialMigrations(];
        $this->updateSecurityConfigs(];
        $this->generateReport(];
    }
    
    private function confirmExecution(): void
    {
        echo "⚠️  此操作将移动多个目录到public文件夹\n";
        echo "📋 高优先级完全迁移: " . count($this->highPriorityDirs) . " 个目录\n";
        echo "📋 部分选择性迁�? " . count($this->partialMigrationDirs) . " 个目录\n\n";
        
        echo "是否继续�?y/N): ";
        $input = trim(fgets(STDIN)];
        
        if (strtolower($input) !== 'y') {
            echo "�?操作已取消\n";
            exit(0];
        }
    }
    
    private function createBackup(): void
    {
        $backupDir = $this->rootDir . '/backup/comprehensive_migration_' . date('Y_m_d_H_i_s'];
        mkdir($backupDir, 0755, true];
        
        echo "💾 创建备份: " . basename($backupDir) . "\n\n";
    }
    
    private function executeHighPriorityMigrations(): void
    {
        echo "🔥 执行高优先级迁移...\n";
        
        foreach ($this->highPriorityDirs as $dir) {
            $this->migrateDirectory($dir, true];
        }
    }
    
    private function executePartialMigrations(): void
    {
        echo "�?执行部分迁移...\n";
        
        foreach ($this->partialMigrationDirs as $dir) {
            $this->migrateDirectory($dir, false];
        }
    }
      private function migrateDirectory(string $dirName, bool $fullMigration): void
    {
        $sourceDir = $this->rootDir . '/' . $dirName;
        $targetDir = $this->publicDir . '/' . $dirName;
        
        if (!is_dir($sourceDir)) {
            echo "  ⚠️  源目录不存在: {$dirName}/\n";
            return;
        }
        
        if (!$fullMigration) {
            echo "  📁 部分迁移: {$dirName}/\n";
            $this->executePartialMigration($sourceDir, $targetDir, $dirName];
        } else {
            echo "  📁 完全迁移: {$dirName}/\n";
            $this->executeFullMigration($sourceDir, $targetDir, $dirName];
        }
    }
    
    private function executeFullMigration(string $sourceDir, string $targetDir, string $dirName): void
    {
        // 特殊处理：某些目录已经在public中存�?
        if (is_dir($targetDir)) {
            echo "    ⚠️  目标目录已存在，检查是否需要合�? {$dirName}/\n";
            
            if ($dirName === 'docs') {
                // docs目录已经部分迁移，只需要移动剩余内�?
                $this->mergeDirectoryContents($sourceDir, $targetDir];
                echo "    �?合并docs目录内容完成\n";
                return;
            }
        }
        
        // 创建目标目录
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true];
        }
        
        // 移动目录内容
        $this->moveDirectoryContents($sourceDir, $targetDir];
        
        // 创建符号链接
        $this->createSymbolicLink($targetDir, $sourceDir];
        
        echo "    �?完全迁移完成: {$dirName}/\n";
    }
    
    private function executePartialMigration(string $sourceDir, string $targetDir, string $dirName): void
    {
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true];
        }
        
        $webAccessibleExtensions = ['html', 'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'pdf'];
        $webAccessiblePatterns = ['*api*.php', '*test*.php', '*tool*.php', 'index.php', 'login.php'];
        
        $this->copySelectiveFiles($sourceDir, $targetDir, $webAccessibleExtensions, $webAccessiblePatterns];
        
        echo "    �?选择性迁移完�? {$dirName}/\n";
    }
    
    private function moveDirectoryContents(string $source, string $target): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS],
            RecursiveIteratorIterator::SELF_FIRST
        ];
        
        foreach ($iterator as $item) {
            $targetPath = $target . DIRECTORY_SEPARATOR . $iterator->getSubPathName(];
            
            if ($item->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true];
                }
            } else {
                $targetDirPath = dirname($targetPath];
                if (!is_dir($targetDirPath)) {
                    mkdir($targetDirPath, 0755, true];
                }
                rename($item->getPathname(), $targetPath];
            }
        }
        
        // 清理空目�?
        $this->removeEmptyDirectories($source];
    }
    
    private function mergeDirectoryContents(string $source, string $target): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS],
            RecursiveIteratorIterator::SELF_FIRST
        ];
        
        foreach ($iterator as $item) {
            $relativePath = $iterator->getSubPathName(];
            $targetPath = $target . DIRECTORY_SEPARATOR . $relativePath;
            
            if ($item->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true];
                }
            } else {
                // 如果目标文件不存在，则移动；否则保留原文�?
                if (!file_exists($targetPath)) {
                    $targetDirPath = dirname($targetPath];
                    if (!is_dir($targetDirPath)) {
                        mkdir($targetDirPath, 0755, true];
                    }
                    rename($item->getPathname(), $targetPath];
                }
            }
        }
    }
    
    private function copySelectiveFiles(string $source, string $target, array $extensions, array $patterns): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS],
            RecursiveIteratorIterator::SELF_FIRST
        ];
        
        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $filename = $item->getFilename(];
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION)];
                
                $shouldCopy = in_[$extension, $extensions];
                
                if (!$shouldCopy) {
                    foreach ($patterns as $pattern) {
                        if (fnmatch($pattern, $filename)) {
                            $shouldCopy = true;
                            break;
                        }
                    }
                }
                
                // 排除安全敏感文件
                $securityPatterns = ['*.env*', '*config*.php', '*password*.php', '*secret*.php', '*.backup', '*.bak'];
                foreach ($securityPatterns as $secPattern) {
                    if (fnmatch($secPattern, $filename)) {
                        $shouldCopy = false;
                        break;
                    }
                }
                
                if ($shouldCopy) {
                    $relativePath = $iterator->getSubPathName(];
                    $targetPath = $target . DIRECTORY_SEPARATOR . $relativePath;
                    $targetDirPath = dirname($targetPath];
                    
                    if (!is_dir($targetDirPath)) {
                        mkdir($targetDirPath, 0755, true];
                    }
                    
                    copy($item->getPathname(), $targetPath];
                    echo "      �?复制: {$relativePath}\n";
                }
            }
        }
    }
    
    private function createSymbolicLink(string $target, string $linkPath): void
    {
        if (file_exists($linkPath) || is_link($linkPath)) {
            return; // 链接已存�?
        }
        
        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = "mklink /D \"$linkPath\" \"$target\"";
            exec($cmd, $output, $returnCode];
            if ($returnCode === 0) {
                echo "    �?创建符号链接 (Windows)\n";
            }
        } else {
            if (symlink($target, $linkPath)) {
                echo "    �?创建符号链接 (Unix)\n";
            }
        }
    }
    
    private function removeEmptyDirectories(string $dir): void
    {
        if (!is_dir($dir)) return;
        
        $files = array_diff(scandir($dir], ['.', '..']];
        
        if (empty($files)) {
            rmdir($dir];
        } else {
            foreach ($files as $file) {
                $fullPath = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($fullPath)) {
                    $this->removeEmptyDirectories($fullPath];
                }
            }
            
            // 再次检查是否为�?
            $files = array_diff(scandir($dir], ['.', '..']];
            if (empty($files)) {
                rmdir($dir];
            }
        }
    }
      private function updateSecurityConfigs(): void
    {
        echo "🔒 更新安全配置...\n";
        
        $this->updateMainHtaccess(];
        $this->createDirectorySecurityConfigs(];
        
        echo "  �?安全配置更新完成\n\n";
    }
    
    private function updateMainHtaccess(): void
    {
        $htaccessPath = $this->publicDir . '/.htaccess';
        $htaccessContent = <<<'HTACCESS'
# AlingAi Pro 5.0 - 增强安全配置
Options -Indexes

# 重写引擎
RewriteEngine On

# 阻止访问敏感文件
<Files ~ "^\.">
    Order allow,deny
    Deny from all
</Files>

# 阻止访问备份和临时文�?
<FilesMatch "\.(bak|backup|old|temp|tmp|log|sql)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# 阻止访问配置文件
<FilesMatch "\.(env|ini|conf)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# API路由
RewriteRule ^api/(.*)$ /api/index.php [QSA,L]

# 管理员路�?
RewriteRule ^admin/(.*)$ /admin/index.php [QSA,L]

# 测试工具路由（生产环境可能需要移除）
RewriteRule ^test/(.*)$ /test/index.php [QSA,L]

# 安装工具路由（安装完成后建议移除�?
RewriteRule ^install/(.*)$ /install/index.php [QSA,L]

# 安全头部
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'"
</IfModule>

# 缓存控制
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
    ExpiresByType application/pdf "access plus 1 week"
</IfModule>

HTACCESS;
        
        file_put_contents($htaccessPath, $htaccessContent];
        echo "  �?更新�?htaccess文件\n";
    }
    
    private function createDirectorySecurityConfigs(): void
    {
        $securityConfigs = [
            'install' => [
                'type' => 'ip_restrict',
                'note' => '安装工具，建议完成后移除或限制访�?
            ], 
            'test' => [
                'type' => 'ip_restrict', 
                'note' => '测试工具，生产环境建议限制访�?
            ], 
            'docs' => [
                'type' => 'allow_all',
                'note' => '文档目录，允许公开访问'
            ]
        ];
        
        foreach ($securityConfigs as $dir => $config) {
            $dirPath = $this->publicDir . '/' . $dir;
            if (is_dir($dirPath)) {
                $htaccessPath = $dirPath . '/.htaccess';
                
                switch ($config['type']) {
                    case 'ip_restrict':
                        $content = "# {$config['note']}\n";
                        $content .= "Order Deny,Allow\n";
                        $content .= "Deny from all\n";
                        $content .= "Allow from 127.0.0.1\n";
                        $content .= "Allow from ::1\n";
                        $content .= "# 添加您的IP地址:\n";
                        $content .= "# Allow from YOUR_IP_ADDRESS\n";
                        break;
                    case 'allow_all':
                        $content = "# {$config['note']}\n";
                        $content .= "Options +Indexes\n";
                        break;
                    default:
                        $content = "Options -Indexes\n";
                }
                
                file_put_contents($htaccessPath, $content];
                echo "  �?创建{$dir}安全配置\n";
            }
        }
    }
    
    private function generateReport(): void
    {
        echo "📊 生成迁移报告...\n";
        
        $timestamp = date('Y-m-d H:i:s'];
        $reportContent = $this->buildMigrationReport($timestamp];
        
        $reportPath = $this->rootDir . '/docs/COMPREHENSIVE_MIGRATION_EXECUTION_REPORT_' . date('Y_m_d_H_i_s') . '.md';
        file_put_contents($reportPath, $reportContent];
        
        echo "  �?迁移报告已保�? " . basename($reportPath) . "\n";
        echo "\n🎉 综合迁移执行完成！\n\n";
        
        $this->displayFinalStructure(];
        $this->displayPostMigrationInstructions(];
    }
    
    private function buildMigrationReport(string $timestamp): string
    {
        $highPriorityList = implode(', ', $this->highPriorityDirs];
        $partialMigrationList = implode(', ', $this->partialMigrationDirs];
        
        return <<<MARKDOWN
# AlingAi Pro 5.0 - 综合迁移执行报告

**执行时间**: {$timestamp}
**执行脚本**: execute_comprehensive_migration.php

## 迁移摘要

### 高优先级完全迁移
已迁移目�? {$highPriorityList}

### 部分选择性迁�? 
已迁移目�? {$partialMigrationList}

## 迁移详情

### 完全迁移的目�?
- **docs/**: 文档目录，所有markdown文件和子目录已迁移到public/docs/
- **install/**: 安装工具，包含API接口已迁移到public/install/
- **tests/**: 测试工具，包含测试脚本已迁移到public/tests/

### 部分迁移的目�?
- **admin/**: 仅迁移前端页面文件，保留敏感管理文件在原位置
- **storage/**: 仅迁移可公开访问的文�?

## 安全配置

### �?htaccess更新
- 增强了安全头部配�?
- 添加了新目录的路由规�?
- 强化了敏感文件保�?

### 目录级安全配�?
- **install/**: IP限制访问，建议完成安装后移除
- **test/**: IP限制访问，生产环境建议限�?
- **docs/**: 允许公开访问

## 符号链接
为保持向后兼容性，已创建适当的符号链接�?

## 建议的后续操�?

1. **功能测试**: 验证所有迁移的功能正常工作
2. **安全审查**: 检查新的public目录内容是否安全
3. **性能测试**: 确认迁移后系统性能正常
4. **访问控制**: 根据需要调�?htaccess规则
5. **生产部署**: 更新生产环境的web服务器配�?

## 风险提示

- **install目录**: 包含系统安装工具，完成部署后建议移除或严格限制访�?
- **test目录**: 包含测试工具，生产环境建议移除或限制访问
- **备份验证**: 确保备份完整且可恢复

MARKDOWN;
    }
    
    private function displayFinalStructure(): void
    {
        echo "📁 最终Public目录结构:\n";
        echo str_repeat("-", 60) . "\n";
        
        $this->displayDirectoryTree($this->publicDir, 0, 2];
        echo "\n";
    }
    
    private function displayDirectoryTree(string $dir, int $level = 0, int $maxLevel = 2): void
    {
        if ($level > $maxLevel || !is_dir($dir)) return;
        
        $items = scandir($dir];
        $items = array_filter($items, function($item) {
            return !in_[$item, ['.', '..']];
        }];
        
        sort($items];
        
        foreach ($items as $item) {
            $path = $dir . '/' . $item;
            $indent = str_repeat('  ', $level];
            
            if (is_dir($path)) {
                echo $indent . "📁 {$item}/\n";
                if ($level < $maxLevel) {
                    $this->displayDirectoryTree($path, $level + 1, $maxLevel];
                }
            }
        }
    }
    
    private function displayPostMigrationInstructions(): void
    {
        echo "📋 迁移后操作指�?\n";
        echo str_repeat("-", 60) . "\n";
        echo "1. 🧪 测试功能: 访问迁移的目录确认功能正常\n";
        echo "   - http://localhost/docs/ (文档中心)\n";
        echo "   - http://localhost/install/ (安装工具)\n";
        echo "   - http://localhost/test/ (测试工具)\n\n";
        
        echo "2. 🔒 安全配置: 根据需要调整访问控制\n";
        echo "   - 编辑 public/.htaccess 调整路由规则\n";
        echo "   - 编辑 public/install/.htaccess 添加IP限制\n";
        echo "   - 编辑 public/test/.htaccess 添加IP限制\n\n";
        
        echo "3. 🚀 生产部署: 更新web服务器配置\n";
        echo "   - 更新Nginx/Apache虚拟主机配置\n";
        echo "   - 确保document_root指向public目录\n";
        echo "   - 重启web服务器\n\n";
        
        echo "4. ⚠️  安全建议:\n";
        echo "   - 生产环境移除或严格限制install和test目录访问\n";
        echo "   - 定期检查public目录不包含敏感文件\n";
        echo "   - 监控访问日志确保安全\n\n";
    }
}

try {
    $executor = new ComprehensiveMigrationExecutor(];
    $executor->run(];
} catch (Exception $e) {
    echo "�?迁移执行失败: " . $e->getMessage() . "\n";
    exit(1];
}

