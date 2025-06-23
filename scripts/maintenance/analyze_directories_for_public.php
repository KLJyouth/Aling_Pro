<?php

/**
 * AlingAi Pro 5.0 - 目录分析工具
 * 分析根目录下的文件夹，确定哪些需要放到public目录内
 */

declare(strict_types=1);

class DirectoryAnalyzer
{
    private string $rootDir;
    private array $webAccessibleDirs = [];
    private array $internalDirs = [];
    private array $recommendations = [];
    
    public function __construct()
    {
        $this->rootDir = __DIR__;
    }
    
    public function run(): void
    {
        echo "📂 AlingAi Pro 5.0 - 目录结构分析工具\n";
        echo str_repeat("=", 80) . "\n\n";
        
        $this->analyzeDirectories();
        $this->categorizeDirectories();
        $this->generateRecommendations();
        $this->displayResults();
    }
    
    private function analyzeDirectories(): void
    {
        echo "🔍 分析根目录下的文件夹...\n";
        echo str_repeat("-", 80) . "\n";
        
        $directories = glob($this->rootDir . '/*', GLOB_ONLYDIR);
        
        foreach ($directories as $dir) {
            $dirName = basename($dir);
            
            // 跳过系统目录
            if (in_array($dirName, ['.git', '.vscode', 'node_modules', 'vendor'])) {
                continue;
            }
            
            $this->analyzeDirectory($dirName, $dir);
        }
    }
    
    private function analyzeDirectory(string $dirName, string $dirPath): void
    {
        $analysis = [
            'name' => $dirName,
            'path' => $dirPath,
            'file_count' => count(glob($dirPath . '/*')),
            'web_files' => [],
            'php_files' => [],
            'static_files' => [],
            'has_index' => false,
            'contains_sensitive' => false,
            'recommendation' => '',
            'reason' => ''
        ];
        
        // 分析文件类型
        $files = glob($dirPath . '/*');
        foreach ($files as $file) {
            $filename = basename($file);
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, ['html', 'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico'])) {
                $analysis['static_files'][] = $filename;
            }
            
            if ($ext === 'php') {
                $analysis['php_files'][] = $filename;
                
                // 检查是否是入口文件
                if (in_array($filename, ['index.php', 'login.php', 'admin.php'])) {
                    $analysis['has_index'] = true;
                }
            }
            
            // 检查敏感文件
            if (in_array($ext, ['env', 'conf', 'ini', 'log']) || 
                strpos($filename, 'config') !== false ||
                strpos($filename, 'secret') !== false ||
                strpos($filename, 'key') !== false) {
                $analysis['contains_sensitive'] = true;
            }
        }
        
        // 分析子目录
        $subdirs = glob($dirPath . '/*', GLOB_ONLYDIR);
        $analysis['subdirs'] = array_map('basename', $subdirs);
        
        $this->categorizeDirectory($analysis);
    }
    
    private function categorizeDirectory(array $analysis): void
    {
        $dirName = $analysis['name'];
        
        // 已知的web可访问目录
        $knownWebDirs = [
            'public' => '已经是public目录',
            'assets' => '静态资源文件',
            'css' => 'CSS样式文件',
            'js' => 'JavaScript脚本文件',
            'images' => '图片文件',
            'uploads' => '用户上传文件',
            'docs' => '文档文件（可能需要web访问）',
            'admin' => '管理界面（已迁移到public/admin/）'
        ];
        
        // 已知的内部目录
        $knownInternalDirs = [
            'src' => '源代码目录',
            'vendor' => 'Composer依赖包',
            'config' => '配置文件',
            'storage' => '存储文件',
            'logs' => '日志文件',
            'cache' => '缓存文件',
            'database' => '数据库相关文件',
            'migrations' => '数据库迁移文件',
            'tests' => '测试文件',
            'backup' => '备份文件',
            'scripts' => '脚本文件',
            'services' => '服务类文件',
            'includes' => '包含文件',
            'tools' => '工具脚本（部分可能需要web访问）',
            'deployment' => '部署相关文件',
            'docker' => 'Docker配置文件',
            'nginx' => 'Nginx配置文件',
            'tmp' => '临时文件',
            'bin' => '二进制文件/脚本'
        ];
        
        if (isset($knownWebDirs[$dirName])) {
            $analysis['recommendation'] = 'web_accessible';
            $analysis['reason'] = $knownWebDirs[$dirName];
            $this->webAccessibleDirs[] = $analysis;
        } elseif (isset($knownInternalDirs[$dirName])) {
            $analysis['recommendation'] = 'internal';
            $analysis['reason'] = $knownInternalDirs[$dirName];
            $this->internalDirs[] = $analysis;
        } else {
            // 动态分析
            $this->dynamicAnalysis($analysis);
        }
    }
    
    private function dynamicAnalysis(array &$analysis): void
    {
        $dirName = $analysis['name'];
        $staticFileCount = count($analysis['static_files']);
        $phpFileCount = count($analysis['php_files']);
        
        // 如果包含敏感文件，标记为内部
        if ($analysis['contains_sensitive']) {
            $analysis['recommendation'] = 'internal';
            $analysis['reason'] = '包含敏感配置文件';
            $this->internalDirs[] = $analysis;
            return;
        }
        
        // 如果主要是静态文件，建议放到public
        if ($staticFileCount > 0 && $staticFileCount >= $phpFileCount) {
            $analysis['recommendation'] = 'web_accessible';
            $analysis['reason'] = "包含{$staticFileCount}个静态文件，可能需要web访问";
            $this->webAccessibleDirs[] = $analysis;
            return;
        }
        
        // 如果有入口文件，建议放到public
        if ($analysis['has_index']) {
            $analysis['recommendation'] = 'web_accessible';
            $analysis['reason'] = '包含入口文件，需要web访问';
            $this->webAccessibleDirs[] = $analysis;
            return;
        }
        
        // 根据目录名判断
        $webIndicators = ['public', 'web', 'www', 'html', 'frontend', 'ui', 'interface'];
        $internalIndicators = ['lib', 'library', 'framework', 'core', 'system', 'private'];
        
        foreach ($webIndicators as $indicator) {
            if (strpos(strtolower($dirName), $indicator) !== false) {
                $analysis['recommendation'] = 'web_accessible';
                $analysis['reason'] = "目录名包含'{$indicator}'，暗示需要web访问";
                $this->webAccessibleDirs[] = $analysis;
                return;
            }
        }
        
        foreach ($internalIndicators as $indicator) {
            if (strpos(strtolower($dirName), $indicator) !== false) {
                $analysis['recommendation'] = 'internal';
                $analysis['reason'] = "目录名包含'{$indicator}'，暗示为内部目录";
                $this->internalDirs[] = $analysis;
                return;
            }
        }
        
        // 默认标记为需要进一步分析
        $analysis['recommendation'] = 'analyze';
        $analysis['reason'] = '需要进一步手动分析';
        $this->recommendations[] = $analysis;
    }
    
    private function categorizeDirectories(): void
    {
        // 已在上面的分析过程中完成
    }
    
    private function generateRecommendations(): void
    {
        echo "\n📋 生成迁移建议...\n";
        echo str_repeat("-", 80) . "\n";
        
        // 特殊情况处理
        foreach ($this->webAccessibleDirs as &$dir) {
            switch ($dir['name']) {
                case 'admin':
                    $dir['action'] = '已迁移到 public/admin/';
                    $dir['status'] = 'completed';
                    break;
                    
                case 'resources':
                    // 检查是否包含前端资源
                    if (count($dir['static_files']) > 0) {
                        $dir['action'] = '将静态资源迁移到 public/assets/';
                        $dir['status'] = 'pending';
                    } else {
                        $dir['action'] = '保留在原位置（模板文件）';
                        $dir['status'] = 'keep';
                    }
                    break;
                    
                case 'uploads':
                    $dir['action'] = '迁移到 public/uploads/';
                    $dir['status'] = 'pending';
                    break;
                    
                case 'docs':
                    $dir['action'] = '选择性迁移到 public/docs/（在线文档）';
                    $dir['status'] = 'optional';
                    break;
                    
                default:
                    $dir['action'] = "迁移到 public/{$dir['name']}/";
                    $dir['status'] = 'pending';
            }
        }
    }
    
    private function displayResults(): void
    {
        echo "\n🎯 分析结果\n";
        echo str_repeat("=", 80) . "\n\n";
        
        // 显示需要迁移到public的目录
        if (!empty($this->webAccessibleDirs)) {
            echo "📁 **需要Web访问的目录** (建议迁移到public/):\n";
            echo str_repeat("-", 60) . "\n";
            
            foreach ($this->webAccessibleDirs as $dir) {
                $status = $this->getStatusIcon($dir['status'] ?? 'pending');
                echo "  {$status} **{$dir['name']}/**: {$dir['reason']}\n";
                echo "     📊 文件统计: {$dir['file_count']} 个文件\n";
                if (!empty($dir['static_files'])) {
                    echo "     🎨 静态文件: " . count($dir['static_files']) . " 个\n";
                }
                if (!empty($dir['php_files'])) {
                    echo "     🐘 PHP文件: " . count($dir['php_files']) . " 个\n";
                }
                echo "     💡 建议行动: " . ($dir['action'] ?? "迁移到 public/{$dir['name']}/") . "\n\n";
            }
        }
        
        // 显示应保持内部的目录
        if (!empty($this->internalDirs)) {
            echo "🔒 **内部目录** (应保持在根目录):\n";
            echo str_repeat("-", 60) . "\n";
            
            foreach ($this->internalDirs as $dir) {
                echo "  ✅ **{$dir['name']}/**: {$dir['reason']}\n";
                echo "     📊 文件统计: {$dir['file_count']} 个文件\n\n";
            }
        }
        
        // 显示需要进一步分析的目录
        if (!empty($this->recommendations)) {
            echo "❓ **需要进一步分析的目录**:\n";
            echo str_repeat("-", 60) . "\n";
            
            foreach ($this->recommendations as $dir) {
                echo "  🔍 **{$dir['name']}/**: {$dir['reason']}\n";
                echo "     📊 文件统计: {$dir['file_count']} 个文件\n";
                if (!empty($dir['static_files'])) {
                    echo "     🎨 静态文件: " . count($dir['static_files']) . " 个 -> " . 
                         implode(', ', array_slice($dir['static_files'], 0, 3)) . 
                         (count($dir['static_files']) > 3 ? '...' : '') . "\n";
                }
                if (!empty($dir['php_files'])) {
                    echo "     🐘 PHP文件: " . count($dir['php_files']) . " 个 -> " . 
                         implode(', ', array_slice($dir['php_files'], 0, 3)) . 
                         (count($dir['php_files']) > 3 ? '...' : '') . "\n";
                }
                echo "\n";
            }
        }
        
        $this->displayMigrationPlan();
    }
    
    private function displayMigrationPlan(): void
    {
        echo "\n🚀 **迁移执行计划**\n";
        echo str_repeat("=", 80) . "\n";
        
        $pendingDirs = array_filter($this->webAccessibleDirs, function($dir) {
            return ($dir['status'] ?? 'pending') === 'pending';
        });
        
        if (!empty($pendingDirs)) {
            echo "📋 **立即执行的迁移**:\n\n";
            
            foreach ($pendingDirs as $dir) {
                echo "1. **{$dir['name']}/** 目录:\n";
                echo "   - 源位置: `{$dir['name']}/`\n";
                echo "   - 目标位置: `public/{$dir['name']}/`\n";
                echo "   - 迁移原因: {$dir['reason']}\n";
                echo "   - 文件数量: {$dir['file_count']} 个\n";
                echo "   - 执行命令: `mv {$dir['name']} public/`\n\n";
            }
        }
        
        $optionalDirs = array_filter($this->webAccessibleDirs, function($dir) {
            return ($dir['status'] ?? 'pending') === 'optional';
        });
        
        if (!empty($optionalDirs)) {
            echo "📋 **可选的迁移** (根据需要执行):\n\n";
            
            foreach ($optionalDirs as $dir) {
                echo "• **{$dir['name']}/** 目录:\n";
                echo "  - 建议: {$dir['action']}\n";
                echo "  - 原因: {$dir['reason']}\n\n";
            }
        }
        
        echo "⚠️  **注意事项**:\n";
        echo "1. 迁移前请备份重要文件\n";
        echo "2. 更新相关的路径引用\n";
        echo "3. 测试web访问功能\n";
        echo "4. 检查文件权限设置\n";
        echo "5. 更新web服务器配置\n\n";
        
        echo "🔗 **推荐的public目录结构**:\n";
        echo "```\n";
        echo "public/\n";
        echo "├── admin/              # 管理后台 (已完成)\n";
        echo "├── api/                # API工具 (已完成)\n";
        echo "├── test/               # 测试工具 (已完成)\n";
        echo "├── monitor/            # 监控工具 (已完成)\n";
        echo "├── tools/              # 系统工具 (已完成)\n";
        echo "├── install/            # 安装工具 (已完成)\n";
        echo "├── assets/             # 静态资源\n";
        echo "├── uploads/            # 用户上传文件\n";
        echo "├── docs/               # 在线文档\n";
        echo "└── downloads/          # 下载文件\n";
        echo "```\n";
    }
    
    private function getStatusIcon(string $status): string
    {
        return match($status) {
            'completed' => '✅',
            'pending' => '📋',
            'optional' => '🔶',
            'keep' => '📍',
            default => '❓'
        };
    }
}

// 执行分析
try {
    $analyzer = new DirectoryAnalyzer();
    $analyzer->run();
} catch (Exception $e) {
    echo "❌ 分析过程中发生错误: " . $e->getMessage() . "\n";
    exit(1);
}
