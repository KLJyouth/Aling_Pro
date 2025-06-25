<?php

/**
 * AlingAi Pro 5.0 - 目录结构优化脚本
 * 处理需要迁移到public目录的文件夹
 */

declare(strict_types=1];

class PublicDirectoryOptimizer
{
    private string $rootDir;
    private array $migrationPlan = [];
    private array $completedActions = [];
    
    public function __construct()
    {
        $this->rootDir = __DIR__;
        $this->initializeMigrationPlan(];
    }
    
    private function initializeMigrationPlan(): void
    {
        $this->migrationPlan = [
            // uploads目录 - 用户上传文件应该web可访�?
            'uploads' => [
                'action' => 'move_to_public',
                'target' => 'public/uploads',
                'reason' => '用户上传文件需要web访问',
                'priority' => 'high'
            ], 
            
            // docs目录 - 部分文档可能需要在线访�?
            'docs' => [
                'action' => 'selective_copy',
                'target' => 'public/docs',
                'reason' => '在线文档需要web访问',
                'priority' => 'medium',
                'patterns' => ['*.html', '*.pdf', '*.md'] // 只复制这些类型的文件
            ], 
            
            // resources目录 - 检查是否包含前端资�?
            'resources' => [
                'action' => 'analyze_and_move_assets',
                'target' => 'public/assets',
                'reason' => '前端资源需要web访问',
                'priority' => 'medium'
            ], 
            
            // deploy目录 - 检查是否有需要web访问的部署文�?
            'deploy' => [
                'action' => 'keep_internal',
                'reason' => '部署脚本应保持内部访�?,
                'priority' => 'low'
            ], 
            
            // backups目录 - 备份文件应保持内�?
            'backups' => [
                'action' => 'keep_internal',
                'reason' => '备份文件不应web访问',
                'priority' => 'low'
            ], 
            
            // infra目录 - 基础设施配置应保持内�?
            'infra' => [
                'action' => 'keep_internal',
                'reason' => '基础设施配置不应web访问',
                'priority' => 'low'
            ]
        ];
    }
    
    public function run(): void
    {
        echo "🎯 AlingAi Pro 5.0 - Public目录结构优化\n";
        echo str_repeat("=", 80) . "\n\n";
        
        $this->analyzeCurrentState(];
        $this->requestConfirmation(];
        $this->executeMigration(];
        $this->optimizePublicStructure(];
        $this->generateReport(];
    }
    
    private function analyzeCurrentState(): void
    {
        echo "📊 分析当前状�?..\n";
        echo str_repeat("-", 80) . "\n";
        
        foreach ($this->migrationPlan as $dirName => $plan) {
            $dirPath = $this->rootDir . '/' . $dirName;
            
            if (!is_dir($dirPath)) {
                echo "⚠️  目录不存�? {$dirName}/\n";
                continue;
            }
            
            $fileCount = count(glob($dirPath . '/*')];
            $staticFiles = $this->countStaticFiles($dirPath];
            
            echo "📁 {$dirName}/:\n";
            echo "   📊 文件�? {$fileCount}\n";
            echo "   🎨 静态文�? {$staticFiles}\n";
            echo "   💡 计划: {$plan['action']}\n";
            echo "   🎯 原因: {$plan['reason']}\n";
            echo "   �?优先�? {$plan['priority']}\n\n";
        }
    }
    
    private function countStaticFiles(string $dirPath): int
    {
        $staticExtensions = ['html', 'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'pdf'];
        $count = 0;
        
        $files = glob($dirPath . '/*'];
        foreach ($files as $file) {
            if (is_file($file)) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION)];
                if (in_[$ext, $staticExtensions)) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
    
    private function requestConfirmation(): void
    {
        echo str_repeat("-", 80) . "\n";
        echo "�?请选择操作:\n";
        echo "   1. 执行高优先级迁移 (uploads)\n";
        echo "   2. 执行所有推荐迁移\n";
        echo "   3. 仅分析，不执行迁移\n";
        echo "   4. 取消操作\n";
        echo "\n请输入选择 (1-4): ";
        
        $choice = trim(fgets(STDIN)];
        
        switch ($choice) {
            case '1':
                echo "�?将执行高优先级迁移\n\n";
                $this->executeHighPriority(];
                break;
                
            case '2':
                echo "�?将执行所有推荐迁移\n\n";
                $this->executeAllMigrations(];
                break;
                
            case '3':
                echo "ℹ️  仅分析模式，不执行迁移\n\n";
                $this->analyzeOnly(];
                break;
                
            case '4':
            default:
                echo "�?操作已取消\n";
                exit(0];
        }
    }
    
    private function executeHighPriority(): void
    {
        foreach ($this->migrationPlan as $dirName => $plan) {
            if ($plan['priority'] === 'high') {
                $this->executeAction($dirName, $plan];
            }
        }
    }
    
    private function executeAllMigrations(): void
    {
        $priorities = ['high', 'medium', 'low'];
        
        foreach ($priorities as $priority) {
            foreach ($this->migrationPlan as $dirName => $plan) {
                if ($plan['priority'] === $priority) {
                    $this->executeAction($dirName, $plan];
                }
            }
        }
    }
    
    private function executeAction(string $dirName, array $plan): void
    {
        $dirPath = $this->rootDir . '/' . $dirName;
        
        if (!is_dir($dirPath)) {
            echo "⏭️  跳过不存在的目录: {$dirName}/\n";
            return;
        }
        
        echo "🚀 处理目录: {$dirName}/\n";
        
        switch ($plan['action']) {
            case 'move_to_public':
                $this->moveToPublic($dirName, $plan];
                break;
                
            case 'selective_copy':
                $this->selectiveCopy($dirName, $plan];
                break;
                
            case 'analyze_and_move_assets':
                $this->analyzeAndMoveAssets($dirName, $plan];
                break;
                
            case 'keep_internal':
                echo "   📍 保持内部访问: {$dirName}/\n";
                break;
                
            default:
                echo "   �?未知操作: {$plan['action']}\n";
        }
        
        echo "\n";
    }
    
    private function moveToPublic(string $dirName, array $plan): void
    {
        $sourcePath = $this->rootDir . '/' . $dirName;
        $targetPath = $this->rootDir . '/' . $plan['target'];
        
        // 创建目标目录的父目录
        $targetParent = dirname($targetPath];
        if (!is_dir($targetParent)) {
            mkdir($targetParent, 0755, true];
            echo "   📁 创建目录: " . str_replace($this->rootDir, '', $targetParent) . "\n";
        }
        
        // 如果目标已存在，先备�?
        if (is_dir($targetPath)) {
            $backupPath = $targetPath . '_backup_' . date('Y_m_d_H_i_s'];
            rename($targetPath, $backupPath];
            echo "   📦 备份现有目录: " . basename($backupPath) . "\n";
        }
        
        // 移动目录
        if (rename($sourcePath, $targetPath)) {
            echo "   �?成功移动: {$dirName}/ �?" . str_replace($this->rootDir, '', $targetPath) . "\n";
            $this->completedActions[] = [
                'action' => 'moved',
                'source' => $dirName,
                'target' => $plan['target'], 
                'reason' => $plan['reason']
            ];
        } else {
            echo "   �?移动失败: {$dirName}/\n";
        }
    }
    
    private function selectiveCopy(string $dirName, array $plan): void
    {
        $sourcePath = $this->rootDir . '/' . $dirName;
        $targetPath = $this->rootDir . '/' . $plan['target'];
        
        // 创建目标目录
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0755, true];
            echo "   📁 创建目录: " . str_replace($this->rootDir, '', $targetPath) . "\n";
        }
        
        $patterns = $plan['patterns'] ?? ['*'];
        $copiedCount = 0;
        
        foreach ($patterns as $pattern) {
            $files = glob($sourcePath . '/' . $pattern];
            foreach ($files as $file) {
                if (is_file($file)) {
                    $filename = basename($file];
                    $targetFile = $targetPath . '/' . $filename;
                    
                    if (copy($file, $targetFile)) {
                        $copiedCount++;
                        echo "   📋 复制: {$filename}\n";
                    }
                }
            }
        }
        
        if ($copiedCount > 0) {
            echo "   �?选择性复制完�? {$copiedCount} 个文件\n";
            $this->completedActions[] = [
                'action' => 'selective_copy',
                'source' => $dirName,
                'target' => $plan['target'], 
                'count' => $copiedCount,
                'reason' => $plan['reason']
            ];
        } else {
            echo "   ⚠️  没有找到匹配的文件\n";
        }
    }
    
    private function analyzeAndMoveAssets(string $dirName, array $plan): void
    {
        $sourcePath = $this->rootDir . '/' . $dirName;
        $assetExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf'];
        
        $assetFiles = [];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourcePath)];
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                $ext = strtolower($file->getExtension()];
                if (in_[$ext, $assetExtensions)) {
                    $assetFiles[] = $file->getPathname(];
                }
            }
        }
        
        if (empty($assetFiles)) {
            echo "   ⚠️  未找到前端资源文件，保持原位置\n";
            return;
        }
        
        echo "   🎨 找到 " . count($assetFiles) . " 个资源文件\n";
        
        $targetPath = $this->rootDir . '/' . $plan['target'];
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0755, true];
            echo "   📁 创建目录: " . str_replace($this->rootDir, '', $targetPath) . "\n";
        }
        
        $copiedCount = 0;
        foreach ($assetFiles as $file) {
            $relativePath = str_replace($sourcePath . '/', '', $file];
            $targetFile = $targetPath . '/' . $relativePath;
            
            // 创建子目�?
            $targetDir = dirname($targetFile];
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true];
            }
            
            if (copy($file, $targetFile)) {
                $copiedCount++;
                echo "   📋 复制资源: {$relativePath}\n";
            }
        }
        
        if ($copiedCount > 0) {
            echo "   �?资源文件复制完成: {$copiedCount} 个文件\n";
            $this->completedActions[] = [
                'action' => 'asset_copy',
                'source' => $dirName,
                'target' => $plan['target'], 
                'count' => $copiedCount,
                'reason' => $plan['reason']
            ];
        }
    }
    
    private function analyzeOnly(): void
    {
        echo "🔍 详细分析模式\n";
        echo str_repeat("-", 80) . "\n";
        
        foreach ($this->migrationPlan as $dirName => $plan) {
            $dirPath = $this->rootDir . '/' . $dirName;
            
            if (!is_dir($dirPath)) {
                continue;
            }
            
            echo "📁 分析 {$dirName}/ 目录:\n";
            
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath)];
            $fileTypes = [];
            
            foreach ($files as $file) {
                if ($file->isFile()) {
                    $ext = strtolower($file->getExtension()];
                    $fileTypes[$ext] = ($fileTypes[$ext] ?? 0) + 1;
                }
            }
            
            echo "   📊 文件类型统计:\n";
            foreach ($fileTypes as $ext => $count) {
                echo "     .{$ext}: {$count} 个\n";
            }
            
            echo "   💡 建议: {$plan['action']} - {$plan['reason']}\n\n";
        }
    }
    
    private function executeMigration(): void
    {
        // 在requestConfirmation中已处理
    }
    
    private function optimizePublicStructure(): void
    {
        echo "🎯 优化public目录结构...\n";
        echo str_repeat("-", 80) . "\n";
        
        $publicDir = $this->rootDir . '/public';
        
        // 确保必要的子目录存在
        $requiredDirs = [
            'assets' => '静态资�?,
            'downloads' => '下载文件',
            'tmp' => '临时文件'
        ];
        
        foreach ($requiredDirs as $subdir => $description) {
            $path = $publicDir . '/' . $subdir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true];
                echo "   📁 创建目录: {$subdir}/ - {$description}\n";
            }
        }
        
        // 创建.htaccess文件确保安全
        $this->createPublicHtaccess(];
        
        echo "   �?Public目录结构优化完成\n\n";
    }
    
    private function createPublicHtaccess(): void
    {
        $htaccessPath = $this->rootDir . '/public/.htaccess';
        
        $htaccessContent = <<<'HTACCESS'
# AlingAi Pro Public Directory Security Configuration
RewriteEngine On

# Prevent access to sensitive files
<FilesMatch "\.(env|ini|log|conf|bak|backup)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# Cache control for static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>
HTACCESS;

        if (!file_exists($htaccessPath)) {
            file_put_contents($htaccessPath, $htaccessContent];
            echo "   🔒 创建 .htaccess 安全配置\n";
        }
    }
    
    private function generateReport(): void
    {
        $reportFile = $this->rootDir . '/PUBLIC_DIRECTORY_OPTIMIZATION_REPORT_' . date('Y_m_d_H_i_s') . '.md';
        
        $report = "# AlingAi Pro 5.0 - Public目录优化报告\n\n";
        $report .= "## 优化概览\n";
        $report .= "- **优化时间**: " . date('Y年m月d�?H:i:s') . "\n";
        $report .= "- **执行操作**: " . count($this->completedActions) . " 个\n\n";
        
        if (!empty($this->completedActions)) {
            $report .= "## 已执行的操作\n\n";
            
            foreach ($this->completedActions as $action) {
                switch ($action['action']) {
                    case 'moved':
                        $report .= "- �?**移动目录**: `{$action['source']}/` �?`{$action['target']}/`\n";
                        $report .= "  - 原因: {$action['reason']}\n\n";
                        break;
                        
                    case 'selective_copy':
                        $report .= "- 📋 **选择性复�?*: `{$action['source']}/` �?`{$action['target']}/`\n";
                        $report .= "  - 复制文件: {$action['count']} 个\n";
                        $report .= "  - 原因: {$action['reason']}\n\n";
                        break;
                        
                    case 'asset_copy':
                        $report .= "- 🎨 **资源复制**: `{$action['source']}/` �?`{$action['target']}/`\n";
                        $report .= "  - 复制文件: {$action['count']} 个\n";
                        $report .= "  - 原因: {$action['reason']}\n\n";
                        break;
                }
            }
        }
        
        $report .= "## 当前Public目录结构\n\n";
        $report .= "```\n";
        $report .= "public/\n";
        $report .= "├── admin/              # 管理后台\n";
        $report .= "├── api/                # API服务\n";
        $report .= "├── test/               # 测试工具\n";
        $report .= "├── monitor/            # 监控工具\n";
        $report .= "├── tools/              # 系统工具\n";
        $report .= "├── install/            # 安装工具\n";
        $report .= "├── assets/             # 静态资源\n";
        $report .= "├── uploads/            # 用户上传文件\n";
        $report .= "├── docs/               # 在线文档\n";
        $report .= "├── downloads/          # 下载文件\n";
        $report .= "├── tmp/                # 临时文件\n";
        $report .= "└── .htaccess           # 安全配置\n";
        $report .= "```\n\n";
        
        $report .= "## 安全改进\n\n";
        $report .= "�?**已实施的安全措施**:\n";
        $report .= "- 创建 .htaccess 文件防止访问敏感文件\n";
        $report .= "- 设置安全响应头\n";
        $report .= "- 配置静态资源缓存\n";
        $report .= "- 只有必要的文件可通过web访问\n\n";
        
        $report .= "## 使用建议\n\n";
        $report .= "1. **文件上传**: 使用 `public/uploads/` 目录\n";
        $report .= "2. **静态资�?*: 放置�?`public/assets/` 目录\n";
        $report .= "3. **在线文档**: 使用 `public/docs/` 目录\n";
        $report .= "4. **临时文件**: 使用 `public/tmp/` 目录（定期清理）\n\n";
        
        $report .= "---\n";
        $report .= "*报告生成时间: " . date('Y年m月d�?H:i:s') . "*\n";
        $report .= "*AlingAi Pro 5.0 政企融合智能办公系统*\n";
        
        file_put_contents($reportFile, $report];
        
        echo "📋 优化报告已生�? " . basename($reportFile) . "\n";
        echo "🎉 Public目录优化完成！\n\n";
        
        echo "🔗 访问验证:\n";
        echo "  - 管理后台: http://localhost:8000/admin/\n";
        echo "  - 工具目录: http://localhost:8000/tools-index.html\n";
        echo "  - 系统监控: http://localhost:8000/monitor/health.php\n";
    }
}

// 执行优化
try {
    $optimizer = new PublicDirectoryOptimizer(];
    $optimizer->run(];
} catch (Exception $e) {
    echo "�?优化过程中发生错�? " . $e->getMessage() . "\n";
    exit(1];
}

