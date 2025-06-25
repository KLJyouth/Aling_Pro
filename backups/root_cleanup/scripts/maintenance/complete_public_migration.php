<?php

/**
 * AlingAi Pro 5.0 - 完成Public目录迁移脚本
 * 完成剩余目录和文件的迁移到public目录
 */

declare(strict_types=1);

class PublicMigrationCompleter
{
    private string $rootDir;
    private string $publicDir;
    private array $migrationTasks = [];
    private array $migrationLog = [];
    
    public function __construct()
    {
        $this->rootDir = __DIR__;
        $this->publicDir = $this->rootDir . '/public';
        
        $this->initializeMigrationTasks();
    }
    
    private function initializeMigrationTasks(): void
    {
        $this->migrationTasks = [
            'uploads' => [
                'source' => 'uploads',
                'target' => 'public/uploads',
                'type' => 'move',
                'priority' => 'high',
                'description' => '用户上传文件目录',
                'create_symlink' => true,
                'security' => [
                    'create_htaccess' => true,
                    'restrict_extensions' => true
                ]
            ],
            
            'docs_public' => [
                'source' => 'docs',
                'target' => 'public/docs',
                'type' => 'selective_copy',
                'priority' => 'medium',
                'description' => '在线文档访问',
                'include_patterns' => [
                    '*.md',
                    'deployment/*.md',
                    'api/*.md',
                    'user/*.md'
                ],
                'exclude_patterns' => [
                    '*REPORT*.md',
                    '*COMPLETION*.md',
                    '*CLEANUP*.md',
                    '*.json',
                    '*.txt',
                    '*.conf',
                    '*.ini'
                ]
            ],
            
            'resources_assets' => [
                'source' => 'resources',
                'target' => 'public/assets/resources',
                'type' => 'selective_copy',
                'priority' => 'medium',
                'description' => '前端资源文件',
                'include_patterns' => [
                    'views/*.css',
                    'views/*.js',
                    'views/assets/*',
                    'lang/*.json',
                    'lang/*.js'
                ]
            ],
            
            'tools_cleanup' => [
                'source' => '',
                'target' => 'public/maintenance',
                'type' => 'organize',
                'priority' => 'low',
                'description' => '整理public目录结构',
                'actions' => [
                    'create_subdirs',
                    'update_references',
                    'optimize_structure'
                ]
            ]
        ];
    }
    
    public function run(): void
    {
        echo "🚀 AlingAi Pro 5.0 - 完成Public目录迁移\n";
        echo str_repeat("=", 80) . "\n\n";
        
        $this->checkPrerequisites();
        $this->executeHighPriorityMigrations();
        $this->executeMediumPriorityMigrations();
        $this->organizeFinalStructure();
        $this->createSecurityConfiguration();
        $this->updatePathReferences();
        $this->generateFinalReport();
    }
    
    private function checkPrerequisites(): void
    {
        echo "📋 检查迁移前提条件...\n";
        echo str_repeat("-", 60) . "\n";
        
        // 检查public目录是否存在
        if (!is_dir($this->publicDir)) {
            throw new Exception("Public目录不存在: {$this->publicDir}");
        }
        
        // 检查写入权限
        if (!is_writable($this->publicDir)) {
            throw new Exception("Public目录没有写入权限");
        }
        
        // 备份当前配置
        $this->createBackup();
        
        echo "✅ 前提条件检查通过\n\n";
    }
    
    private function createBackup(): void
    {
        $backupDir = $this->rootDir . '/backup/migration_' . date('Y_m_d_H_i_s');
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        // 备份关键配置文件
        $filesToBackup = [
            '.htaccess',
            'public/.htaccess',
            'router.php',
            'composer.json'
        ];
        
        foreach ($filesToBackup as $file) {
            $source = $this->rootDir . '/' . $file;
            if (file_exists($source)) {
                copy($source, $backupDir . '/' . basename($file));
            }
        }
        
        echo "💾 已创建备份: {$backupDir}\n";
    }
    
    private function executeHighPriorityMigrations(): void
    {
        echo "🔥 执行高优先级迁移...\n";
        echo str_repeat("-", 60) . "\n";
        
        // 迁移uploads目录
        $this->migrateUploadsDirectory();
        
        echo "✅ 高优先级迁移完成\n\n";
    }
    
    private function migrateUploadsDirectory(): void
    {
        $sourceDir = $this->rootDir . '/uploads';
        $targetDir = $this->publicDir . '/uploads';
        
        echo "📁 迁移uploads目录...\n";
        
        if (is_dir($sourceDir)) {
            // 如果目录为空，直接删除并在public中创建
            if ($this->isDirectoryEmpty($sourceDir)) {
                rmdir($sourceDir);
                echo "   ✓ 删除空的uploads目录\n";
            } else {
                // 移动内容到public/uploads
                $this->moveDirectory($sourceDir, $targetDir);
                echo "   ✓ 移动uploads内容到public/uploads\n";
            }
        }
        
        // 确保public/uploads存在
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
            echo "   ✓ 创建public/uploads目录\n";
        }
        
        // 创建安全配置
        $this->createUploadsSecurityConfig($targetDir);
        
        // 创建符号链接指向public/uploads
        if (!file_exists($sourceDir)) {
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows 使用 mklink
                $cmd = "mklink /D \"{$sourceDir}\" \"{$targetDir}\"";
                exec($cmd, $output, $returnCode);
                if ($returnCode === 0) {
                    echo "   ✓ 创建符号链接: uploads -> public/uploads\n";
                } else {
                    echo "   ⚠️  符号链接创建失败，请手动创建\n";
                }
            } else {
                // Unix/Linux 使用 ln -s
                symlink($targetDir, $sourceDir);
                echo "   ✓ 创建符号链接: uploads -> public/uploads\n";
            }
        }
        
        $this->migrationLog[] = [
            'action' => 'migrate_uploads',
            'source' => $sourceDir,
            'target' => $targetDir,
            'status' => 'completed'
        ];
    }
    
    private function createUploadsSecurityConfig(string $uploadsDir): void
    {
        $htaccessPath = $uploadsDir . '/.htaccess';
        $htaccessContent = <<<'HTACCESS'
# 上传文件安全配置
Options -Indexes
Options -ExecCGI

# 禁止执行PHP文件
<Files "*.php">
    Order Deny,Allow
    Deny from all
</Files>

# 禁止执行脚本文件
<FilesMatch "\.(php|phtml|php3|php4|php5|pl|py|jsp|asp|sh|cgi)$">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# 只允许特定文件类型
<FilesMatch "\.(jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|txt|zip|rar)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>

# 防止直接访问隐藏文件
<FilesMatch "^\.">
    Order Deny,Allow
    Deny from all
</FilesMatch>

HTACCESS;
        
        file_put_contents($htaccessPath, $htaccessContent);
        echo "   ✓ 创建uploads安全配置\n";
    }
    
    private function executeMediumPriorityMigrations(): void
    {
        echo "📖 执行中等优先级迁移...\n";
        echo str_repeat("-", 60) . "\n";
        
        // 选择性复制docs内容
        $this->migrateDocsSelectively();
        
        // 迁移resources中的前端资源
        $this->migrateResourcesAssets();
        
        echo "✅ 中等优先级迁移完成\n\n";
    }
    
    private function migrateDocsSelectively(): void
    {
        $sourceDir = $this->rootDir . '/docs';
        $targetDir = $this->publicDir . '/docs';
        
        echo "📚 选择性迁移文档...\n";
        
        if (!is_dir($sourceDir)) {
            echo "   ⚠️  源文档目录不存在\n";
            return;
        }
        
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        // 定义要复制的文档
        $docsToMigrate = [
            'USER_MANUAL.md',
            'QUICK_START_GUIDE.md',
            'SYSTEM_OPERATIONS_MANUAL.md',
            'DEPLOYMENT_GUIDE.md',
            'CODE_STANDARDS.md',
            'ARCHITECTURE_DIAGRAM.md',
            'api/',
            'deployment/',
            'user/'
        ];
        
        $migrated = 0;
        foreach ($docsToMigrate as $item) {
            $sourcePath = $sourceDir . '/' . $item;
            $targetPath = $targetDir . '/' . $item;
            
            if (file_exists($sourcePath)) {
                if (is_dir($sourcePath)) {
                    $this->copyDirectory($sourcePath, $targetPath);
                    echo "   ✓ 复制目录: {$item}\n";
                } else {
                    $targetDirPath = dirname($targetPath);
                    if (!is_dir($targetDirPath)) {
                        mkdir($targetDirPath, 0755, true);
                    }
                    copy($sourcePath, $targetPath);
                    echo "   ✓ 复制文件: {$item}\n";
                }
                $migrated++;
            }
        }
        
        echo "   📊 总计迁移: {$migrated} 项文档内容\n";
        
        $this->migrationLog[] = [
            'action' => 'migrate_docs_selective',
            'source' => $sourceDir,
            'target' => $targetDir,
            'items_migrated' => $migrated,
            'status' => 'completed'
        ];
    }
    
    private function migrateResourcesAssets(): void
    {
        $sourceDir = $this->rootDir . '/resources';
        $targetDir = $this->publicDir . '/assets/resources';
        
        echo "🎨 迁移前端资源文件...\n";
        
        if (!is_dir($sourceDir)) {
            echo "   ⚠️  resources目录不存在\n";
            return;
        }
        
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        // 复制语言文件
        $langSource = $sourceDir . '/lang';
        $langTarget = $targetDir . '/lang';
        if (is_dir($langSource)) {
            $this->copyDirectory($langSource, $langTarget);
            echo "   ✓ 复制语言文件\n";
        }
        
        // 复制视图资源
        $viewsSource = $sourceDir . '/views';
        $viewsTarget = $targetDir . '/views';
        if (is_dir($viewsSource)) {
            // 只复制前端资源，不复制模板
            $this->copyViewAssets($viewsSource, $viewsTarget);
            echo "   ✓ 复制视图资源文件\n";
        }
        
        $this->migrationLog[] = [
            'action' => 'migrate_resources_assets',
            'source' => $sourceDir,
            'target' => $targetDir,
            'status' => 'completed'
        ];
    }
    
    private function copyViewAssets(string $source, string $target): void
    {
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $targetPath = $target . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                // 只复制前端资源文件
                $extension = strtolower(pathinfo($item->getFilename(), PATHINFO_EXTENSION));
                $frontendExtensions = ['css', 'js', 'json', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico'];
                
                if (in_array($extension, $frontendExtensions)) {
                    $targetDirPath = dirname($targetPath);
                    if (!is_dir($targetDirPath)) {
                        mkdir($targetDirPath, 0755, true);
                    }
                    copy($item->getPathname(), $targetPath);
                }
            }
        }
    }
    
    private function organizeFinalStructure(): void
    {
        echo "🏗️  整理最终目录结构...\n";
        echo str_repeat("-", 60) . "\n";
        
        // 创建推荐的public子目录结构
        $this->createRecommendedDirectories();
        
        // 优化现有文件组织
        $this->optimizeExistingStructure();
        
        echo "✅ 目录结构整理完成\n\n";
    }
    
    private function createRecommendedDirectories(): void
    {
        $recommendedDirs = [
            'assets/css',
            'assets/js',
            'assets/images',
            'assets/fonts',
            'assets/resources',
            'downloads',
            'cache',
            'logs/public',
            'storage/temp',
            'maintenance'
        ];
        
        foreach ($recommendedDirs as $dir) {
            $fullPath = $this->publicDir . '/' . $dir;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
                echo "   ✓ 创建目录: {$dir}\n";
            }
        }
    }
    
    private function optimizeExistingStructure(): void
    {
        echo "   🔧 优化现有文件结构...\n";
        
        // 移动散落的CSS和JS文件到assets
        $this->moveAssetsToAssets();
        
        // 整理测试文件
        $this->organizeTestFiles();
        
        // 整理工具文件
        $this->organizeToolsFiles();
    }
    
    private function moveAssetsToAssets(): void
    {
        $cssFiles = glob($this->publicDir . '/*.css');
        $jsFiles = glob($this->publicDir . '/*.js');
        $imageFiles = array_merge(
            glob($this->publicDir . '/*.png'),
            glob($this->publicDir . '/*.jpg'),
            glob($this->publicDir . '/*.jpeg'),
            glob($this->publicDir . '/*.gif'),
            glob($this->publicDir . '/*.svg'),
            glob($this->publicDir . '/*.ico')
        );
        
        // 移动CSS文件
        foreach ($cssFiles as $file) {
            $newPath = $this->publicDir . '/assets/css/' . basename($file);
            if (!file_exists($newPath)) {
                rename($file, $newPath);
                echo "     ✓ 移动CSS: " . basename($file) . "\n";
            }
        }
        
        // 移动JS文件
        foreach ($jsFiles as $file) {
            $filename = basename($file);
            // 跳过某些重要的JS文件
            if (!in_array($filename, ['router.js', 'index.js', 'main.js'])) {
                $newPath = $this->publicDir . '/assets/js/' . $filename;
                if (!file_exists($newPath)) {
                    rename($file, $newPath);
                    echo "     ✓ 移动JS: " . $filename . "\n";
                }
            }
        }
        
        // 移动图片文件
        foreach ($imageFiles as $file) {
            $newPath = $this->publicDir . '/assets/images/' . basename($file);
            if (!file_exists($newPath)) {
                rename($file, $newPath);
                echo "     ✓ 移动图片: " . basename($file) . "\n";
            }
        }
    }
    
    private function organizeTestFiles(): void
    {
        // 确保test目录有合适的索引页面
        $testIndexPath = $this->publicDir . '/test/index.html';
        if (!file_exists($testIndexPath)) {
            $this->createTestIndex($testIndexPath);
            echo "     ✓ 创建测试索引页面\n";
        }
    }
    
    private function organizeToolsFiles(): void
    {
        // 确保tools目录有合适的索引页面
        $toolsIndexPath = $this->publicDir . '/tools/index.html';
        if (!file_exists($toolsIndexPath)) {
            $this->createToolsIndex($toolsIndexPath);
            echo "     ✓ 创建工具索引页面\n";
        }
    }
    
    private function createTestIndex(string $path): void
    {
        $content = <<<'HTML'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlingAi Pro - 测试工具</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/common.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>🧪 测试工具中心</h1>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>API测试</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><a href="api-comprehensive.php">综合API测试</a></li>
                            <li class="list-group-item"><a href="api-simple.php">简单API测试</a></li>
                            <li class="list-group-item"><a href="api-direct.php">直接API测试</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>系统测试</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><a href="system-comprehensive-v5.php">系统综合测试</a></li>
                            <li class="list-group-item"><a href="integration-final.php">集成测试</a></li>
                            <li class="list-group-item"><a href="performance.php">性能测试</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
        
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($path, $content);
    }
    
    private function createToolsIndex(string $path): void
    {
        $content = <<<'HTML'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlingAi Pro - 管理工具</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/common.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>🛠️ 管理工具中心</h1>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>数据库工具</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><a href="database-management.php">数据库管理</a></li>
                            <li class="list-group-item"><a href="cache-optimizer.php">缓存优化</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>性能工具</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><a href="performance-optimizer.php">性能优化</a></li>
                            <li class="list-group-item"><a href="cache-manager.php">缓存管理</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>系统工具</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><a href="system-info.php">系统信息</a></li>
                            <li class="list-group-item"><a href="log-viewer.php">日志查看</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
        
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($path, $content);
    }
    
    private function createSecurityConfiguration(): void
    {
        echo "🔒 创建安全配置...\n";
        echo str_repeat("-", 60) . "\n";
        
        // 更新主.htaccess文件
        $this->updateMainHtaccess();
        
        // 创建各子目录的安全配置
        $this->createSubdirectorySecurityConfigs();
        
        echo "✅ 安全配置创建完成\n\n";
    }
    
    private function updateMainHtaccess(): void
    {
        $htaccessPath = $this->publicDir . '/.htaccess';
        $htaccessContent = <<<'HTACCESS'
# AlingAi Pro 5.0 - 主安全配置
Options -Indexes

# 重写引擎
RewriteEngine On

# 阻止访问敏感文件
<Files ~ "^\.">
    Order allow,deny
    Deny from all
</Files>

# 阻止访问备份文件
<FilesMatch "\.(bak|backup|old|temp|tmp|log)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# API路由
RewriteRule ^api/(.*)$ /api/index.php [QSA,L]

# 管理员路由
RewriteRule ^admin/(.*)$ /admin/index.php [QSA,L]

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
</IfModule>

HTACCESS;
        
        file_put_contents($htaccessPath, $htaccessContent);
        echo "   ✓ 更新主.htaccess文件\n";
    }
    
    private function createSubdirectorySecurityConfigs(): void
    {
        $secureDirectories = [
            'logs' => 'deny_all',
            'storage' => 'deny_all', 
            'maintenance' => 'ip_restrict',
            'cache' => 'deny_all'
        ];
        
        foreach ($secureDirectories as $dir => $securityType) {
            $dirPath = $this->publicDir . '/' . $dir;
            
            if (is_dir($dirPath)) {
                $htaccessPath = $dirPath . '/.htaccess';
                
                switch ($securityType) {
                    case 'deny_all':
                        $content = "Order Deny,Allow\nDeny from all";
                        break;
                    case 'ip_restrict':
                        $content = "Order Deny,Allow\nDeny from all\nAllow from 127.0.0.1\nAllow from ::1";
                        break;
                    default:
                        $content = "Options -Indexes";
                }
                
                file_put_contents($htaccessPath, $content);
                echo "   ✓ 创建{$dir}安全配置\n";
            }
        }
    }
    
    private function updatePathReferences(): void
    {
        echo "🔗 更新路径引用...\n";
        echo str_repeat("-", 60) . "\n";
        
        // 更新根目录router.php中的路径引用
        $this->updateRouterReferences();
        
        // 更新HTML文件中的路径引用
        $this->updateHtmlReferences();
        
        echo "✅ 路径引用更新完成\n\n";
    }
    
    private function updateRouterReferences(): void
    {
        $routerPath = $this->rootDir . '/router.php';
        
        if (file_exists($routerPath)) {
            $content = file_get_contents($routerPath);
            
            // 更新uploads路径引用
            $content = str_replace('/uploads/', '/public/uploads/', $content);
            $content = str_replace('uploads/', 'public/uploads/', $content);
            
            file_put_contents($routerPath, $content);
            echo "   ✓ 更新router.php路径引用\n";
        }
    }
    
    private function updateHtmlReferences(): void
    {
        $htmlFiles = array_merge(
            glob($this->publicDir . '/*.html'),
            glob($this->publicDir . '/*/*.html')
        );
        
        $updated = 0;
        foreach ($htmlFiles as $file) {
            $content = file_get_contents($file);
            $originalContent = $content;
            
            // 更新资源路径
            $content = preg_replace('/href="([^"]*\.css)"/', 'href="/assets/css/$1"', $content);
            $content = preg_replace('/src="([^"]*\.js)"/', 'src="/assets/js/$1"', $content);
            $content = preg_replace('/src="([^"]*\.(png|jpg|jpeg|gif|svg))"/', 'src="/assets/images/$1"', $content);
            
            if ($content !== $originalContent) {
                file_put_contents($file, $content);
                $updated++;
            }
        }
        
        echo "   ✓ 更新了{$updated}个HTML文件的路径引用\n";
    }
    
    private function generateFinalReport(): void
    {
        echo "📊 生成最终报告...\n";
        echo str_repeat("-", 60) . "\n";
        
        $reportContent = $this->buildReportContent();
        $reportPath = $this->rootDir . '/docs/PUBLIC_MIGRATION_COMPLETE_REPORT_' . date('Y_m_d_H_i_s') . '.md';
        
        file_put_contents($reportPath, $reportContent);
        
        echo "📁 最终Public目录结构:\n";
        $this->displayPublicStructure();
        
        echo "\n✅ 迁移完成！报告已保存到: " . basename($reportPath) . "\n";
        echo "\n🚀 建议的后续步骤:\n";
        echo "   1. 测试所有功能是否正常工作\n";
        echo "   2. 检查文件权限是否正确设置\n";
        echo "   3. 验证安全配置是否生效\n";
        echo "   4. 更新部署脚本中的路径引用\n";
        echo "   5. 更新文档中的路径说明\n\n";
    }
    
    private function buildReportContent(): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $content = <<<MARKDOWN
# AlingAi Pro 5.0 - Public目录迁移完成报告

**生成时间**: {$timestamp}
**迁移脚本**: complete_public_migration.php

## 迁移总结

### 已完成的迁移任务

MARKDOWN;
        
        foreach ($this->migrationLog as $entry) {
            $content .= "- **{$entry['action']}**: {$entry['status']}\n";
            if (isset($entry['source'])) {
                $content .= "  - 源: `{$entry['source']}`\n";
            }
            if (isset($entry['target'])) {
                $content .= "  - 目标: `{$entry['target']}`\n";
            }
            if (isset($entry['items_migrated'])) {
                $content .= "  - 迁移项目: {$entry['items_migrated']}\n";
            }
            $content .= "\n";
        }
        
        $content .= <<<MARKDOWN

### 最终Public目录结构

```
public/
├── admin/              # 管理界面 (已完成)
├── api/                # API接口 (已完成)
├── test/               # 测试工具 (已完成)
├── monitor/            # 监控工具 (已完成)
├── tools/              # 管理工具 (已完成)
├── install/            # 安装工具 (已完成)
├── assets/             # 静态资源
│   ├── css/           # 样式文件
│   ├── js/            # 脚本文件
│   ├── images/        # 图片文件
│   ├── fonts/         # 字体文件
│   └── resources/     # 前端资源
├── docs/               # 在线文档
├── uploads/            # 用户上传
├── downloads/          # 下载文件
├── storage/            # 临时存储
├── logs/               # 公开日志
├── cache/              # 缓存文件
└── maintenance/        # 维护工具
```

### 安全配置

1. **主.htaccess**: 已更新安全头部和路由规则
2. **uploads/.htaccess**: 禁止执行脚本文件
3. **logs/.htaccess**: 完全拒绝访问
4. **storage/.htaccess**: 完全拒绝访问
5. **maintenance/.htaccess**: IP限制访问

### 符号链接

- `uploads/` → `public/uploads/` (保持向后兼容)

### 建议的后续操作

1. **测试验证**:
   - [ ] 测试所有web功能
   - [ ] 验证上传功能
   - [ ] 检查文档访问
   - [ ] 确认API正常

2. **部署更新**:
   - [ ] 更新Nginx配置
   - [ ] 更新Apache配置
   - [ ] 修改部署脚本
   - [ ] 更新CI/CD流程

3. **文档更新**:
   - [ ] 更新README.md
   - [ ] 修改部署指南
   - [ ] 更新架构文档

4. **监控配置**:
   - [ ] 设置文件监控
   - [ ] 配置安全监控
   - [ ] 验证日志记录

MARKDOWN;
        
        return $content;
    }
    
    private function displayPublicStructure(): void
    {
        $this->displayDirectoryTree($this->publicDir, 0, 2);
    }
    
    private function displayDirectoryTree(string $dir, int $level = 0, int $maxLevel = 2): void
    {
        if ($level > $maxLevel) return;
        
        $items = scandir($dir);
        $items = array_filter($items, function($item) {
            return !in_array($item, ['.', '..']);
        });
        
        sort($items);
        
        foreach ($items as $item) {
            $path = $dir . '/' . $item;
            $indent = str_repeat('  ', $level);
            
            if (is_dir($path)) {
                echo $indent . "📁 {$item}/\n";
                if ($level < $maxLevel) {
                    $this->displayDirectoryTree($path, $level + 1, $maxLevel);
                }
            } else {
                // 只显示重要文件
                if (in_array(pathinfo($item, PATHINFO_EXTENSION), ['php', 'html', 'htaccess']) || 
                    in_array($item, ['index.php', 'router.php', '.htaccess'])) {
                    echo $indent . "📄 {$item}\n";
                }
            }
        }
    }
    
    // 辅助方法
    private function isDirectoryEmpty(string $dir): bool
    {
        return count(scandir($dir)) === 2; // 只有 . 和 ..
    }
    
    private function moveDirectory(string $source, string $target): void
    {
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $targetPath = $target . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                $targetDirPath = dirname($targetPath);
                if (!is_dir($targetDirPath)) {
                    mkdir($targetDirPath, 0755, true);
                }
                rename($item->getPathname(), $targetPath);
            }
        }
        
        // 删除空的源目录
        $this->removeEmptyDirectories($source);
        if (is_dir($source) && $this->isDirectoryEmpty($source)) {
            rmdir($source);
        }
    }
    
    private function copyDirectory(string $source, string $target): void
    {
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $targetPath = $target . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                $targetDirPath = dirname($targetPath);
                if (!is_dir($targetDirPath)) {
                    mkdir($targetDirPath, 0755, true);
                }
                copy($item->getPathname(), $targetPath);
            }
        }
    }
    
    private function removeEmptyDirectories(string $dir): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $item) {
            if ($item->isDir() && $this->isDirectoryEmpty($item->getPathname())) {
                rmdir($item->getPathname());
            }
        }
    }
}

// 执行迁移
try {
    $migrator = new PublicMigrationCompleter();
    $migrator->run();
} catch (Exception $e) {
    echo "❌ 迁移失败: " . $e->getMessage() . "\n";
    echo "📋 错误详情: " . $e->getTraceAsString() . "\n";
    exit(1);
}
