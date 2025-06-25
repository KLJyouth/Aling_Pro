<?php

/**
 * AlingAi Pro 5.0 - 高级文件整理和项目结构优化脚�?
 * 
 * 功能:
 * 1. 分析整体项目结构
 * 2. 按功能分类整理根目录文件
 * 3. 将相关文件移动到适当的目�?
 * 4. 优化public文件夹结�?
 * 5. 生成项目结构报告
 */

declare(strict_types=1];

class AdvancedProjectOrganizer
{
    private string $rootDir;
    private array $fileCategories;
    private array $organizationPlan = [];
    private array $movedFiles = [];
    private array $createdDirectories = [];
    
    public function __construct()
    {
        $this->rootDir = __DIR__;
        $this->initializeFileCategories(];
    }
    
    private function initializeFileCategories(): void
    {
        $this->fileCategories = [
            // 文档和报�?
            'docs' => [
                'pattern' => ['*.md', '*.txt', '*.json'], 
                'target_dir' => 'docs',
                'description' => '项目文档和报�?,
                'keep_in_root' => ['README.md', 'CHANGELOG.md', 'LICENSE.md'], 
                'subfolders' => [
                    'reports' => ['*_REPORT*.md', '*_report*.json'], 
                    'guides' => ['*_GUIDE*.md', '*_guide*.md'], 
                    'plans' => ['*_PLAN*.md', '*_plan*.md'], 
                    'architecture' => ['ARCHITECTURE*.md', 'architecture*.md']
                ]
            ], 
            
            // 部署和配置脚�?
            'deployment' => [
                'pattern' => ['deploy*.php', 'deploy*.bat', 'deploy*.sh', '*deployment*.php'], 
                'target_dir' => 'deployment/scripts',
                'description' => '部署脚本和配�?,
                'web_accessible' => false
            ], 
            
            // 数据库相�?
            'database' => [
                'pattern' => ['*database*.php', '*migration*.php', 'migrate*.php', '*migrate*.php'], 
                'target_dir' => 'database/management',
                'description' => '数据库管理和迁移脚本',
                'web_accessible' => false,
                'exceptions' => ['migrate_frontend_resources.php'] // 这个可能需要web访问
            ], 
            
            // 测试文件
            'testing' => [
                'pattern' => ['*test*.php', '*Test*.php'], 
                'target_dir' => 'tests',
                'description' => '测试文件',
                'web_accessible' => true,
                'public_subdir' => 'test',
                'exceptions' => ['test_admin_system.php'] // 已在public/admin/
            ], 
            
            // 系统工具和脚�?
            'tools' => [
                'pattern' => ['*tool*.php', '*optimizer*.php', '*backup*.php', '*monitor*.php'], 
                'target_dir' => 'tools',
                'description' => '系统工具和实用脚�?,
                'web_accessible' => true,
                'public_subdir' => 'tools'
            ], 
            
            // WebSocket服务�?
            'websocket' => [
                'pattern' => ['websocket*.php', '*websocket*.php'], 
                'target_dir' => 'services/websocket',
                'description' => 'WebSocket服务器文�?,
                'web_accessible' => false
            ], 
            
            // 服务和容�?
            'services' => [
                'pattern' => ['Service*.php', '*Service*.php', '*Container*.php'], 
                'target_dir' => 'services',
                'description' => '服务类和容器',
                'web_accessible' => false
            ], 
            
            // 配置文件
            'config' => [
                'pattern' => ['*.yaml', '*.yml', '*.json', '.php-cs-fixer.php'], 
                'target_dir' => 'config',
                'description' => '配置文件',
                'web_accessible' => false,
                'exceptions' => ['composer.json', 'composer.lock', 'package.json']
            ], 
            
            // 临时和开发文�?
            'temp' => [
                'pattern' => ['cleanup*.php', 'analyze*.php', 'verify*.php'], 
                'target_dir' => 'scripts/maintenance',
                'description' => '临时和维护脚�?,
                'web_accessible' => false
            ], 
            
            // 核心系统文件 (保留在根目录)
            'core' => [
                'pattern' => ['router.php', 'worker.php', 'index.php'], 
                'target_dir' => '',
                'description' => '核心系统文件',
                'keep_in_root' => true
            ]
        ];
    }
    
    public function run(): void
    {
        echo "🗂�? AlingAi Pro 5.0 - 高级文件整理工具\n";
        echo str_repeat("=", 80) . "\n\n";
        
        $this->analyzeCurrentStructure(];
        $this->createOrganizationPlan(];
        $this->requestConfirmation(];
        $this->executeOrganization(];
        $this->optimizePublicStructure(];
        $this->generateReport(];
    }
    
    private function analyzeCurrentStructure(): void
    {
        echo "📊 分析当前项目结构...\n";
        echo str_repeat("-", 80) . "\n";
        
        // 分析根目录文�?
        $rootFiles = glob($this->rootDir . '/*'];
        $phpFiles = glob($this->rootDir . '/*.php'];
        $docFiles = array_merge(
            glob($this->rootDir . '/*.md'],
            glob($this->rootDir . '/*.txt'],
            glob($this->rootDir . '/*.json')
        ];
        $scriptFiles = array_merge(
            glob($this->rootDir . '/*.bat'],
            glob($this->rootDir . '/*.sh')
        ];
        
        echo "📁 根目录统�?\n";
        echo "  📄 总文件数: " . count($rootFiles) . "\n";
        echo "  🐘 PHP文件: " . count($phpFiles) . "\n";
        echo "  📋 文档文件: " . count($docFiles) . "\n";
        echo "  🔧 脚本文件: " . count($scriptFiles) . "\n\n";
        
        // 分析子目�?
        $subdirs = array_filter($rootFiles, 'is_dir'];
        echo "📂 子目录结�?\n";
        foreach ($subdirs as $dir) {
            $dirName = basename($dir];
            if (!in_[$dirName, ['.git', 'node_modules', 'vendor'])) {
                $fileCount = count(glob($dir . '/*')];
                echo "  📁 {$dirName}/: {$fileCount} 个文件\n";
            }
        }
        echo "\n";
    }
    
    private function createOrganizationPlan(): void
    {
        echo "📋 创建文件整理计划...\n";
        echo str_repeat("-", 80) . "\n";
        
        foreach ($this->fileCategories as $category => $config) {
            $this->organizationPlan[$category] = [
                'files' => [], 
                'target_dir' => $config['target_dir'], 
                'description' => $config['description'], 
                'web_accessible' => $config['web_accessible'] ?? false
            ];
            
            foreach ($config['pattern'] as $pattern) {
                $files = glob($this->rootDir . '/' . $pattern];
                foreach ($files as $file) {
                    $filename = basename($file];
                    
                    // 检查是否在例外列表�?
                    if (isset($config['exceptions']) && in_[$filename, $config['exceptions'])) {
                        continue;
                    }
                    
                    // 检查是否应该保留在根目�?
                    if (isset($config['keep_in_root']) && 
                        (is_[$config['keep_in_root']) && in_[$filename, $config['keep_in_root']) ||
                         $config['keep_in_root'] === true)) {
                        continue;
                    }
                    
                    $this->organizationPlan[$category]['files'][] = $filename;
                }
            }
            
            // 显示分类结果
            if (!empty($this->organizationPlan[$category]['files'])) {
                echo "📁 {$config['description']} ({$category}):\n";
                echo "   目标目录: {$config['target_dir']}\n";
                echo "   文件数量: " . count($this->organizationPlan[$category]['files']) . "\n";
                foreach ($this->organizationPlan[$category]['files'] as $file) {
                    echo "   📄 {$file}\n";
                }
                echo "\n";
            }
        }
    }
    
    private function requestConfirmation(): void
    {
        $totalFiles = 0;
        foreach ($this->organizationPlan as $plan) {
            $totalFiles += count($plan['files']];
        }
        
        if ($totalFiles === 0) {
            echo "�?没有需要整理的文件，项目结构已经很好！\n";
            exit(0];
        }
        
        echo str_repeat("-", 80) . "\n";
        echo "📊 整理计划总结:\n";
        echo "   将要移动 {$totalFiles} 个文件\n";
        echo "   创建新的目录结构\n";
        echo "   优化public文件夹组织\n\n";
        
        echo "�?是否执行文件整理计划�?y/N): ";
        $input = trim(fgets(STDIN)];
        
        if (strtolower($input) !== 'y' && strtolower($input) !== 'yes') {
            echo "�?操作已取消\n";
            exit(0];
        }
        
        echo "�?开始执行文件整�?..\n\n";
    }
    
    private function executeOrganization(): void
    {
        echo "🚀 执行文件整理...\n";
        echo str_repeat("-", 80) . "\n";
        
        foreach ($this->organizationPlan as $category => $plan) {
            if (empty($plan['files'])) continue;
            
            echo "📁 处理 {$plan['description']}...\n";
            
            // 创建目标目录
            if (!empty($plan['target_dir'])) {
                $targetDir = $this->rootDir . '/' . $plan['target_dir'];
                $this->createDirectory($targetDir];
                
                // 如果是web可访问的，也在public中创建对应目�?
                if ($plan['web_accessible'] && isset($this->fileCategories[$category]['public_subdir'])) {
                    $publicDir = $this->rootDir . '/public/' . $this->fileCategories[$category]['public_subdir'];
                    $this->createDirectory($publicDir];
                }
            }
            
            // 移动文件
            foreach ($plan['files'] as $filename) {
                $this->moveFile($filename, $plan, $category];
            }
            
            echo "   �?完成\n\n";
        }
    }
    
    private function createDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true];
            $this->createdDirectories[] = $dir;
            echo "   📁 创建目录: " . str_replace($this->rootDir, '', $dir) . "\n";
        }
    }
    
    private function moveFile(string $filename, array $plan, string $category): void
    {
        $sourcePath = $this->rootDir . '/' . $filename;
        
        if (!file_exists($sourcePath)) {
            echo "   ⚠️  文件不存�? {$filename}\n";
            return;
        }
        
        // 确定目标路径
        if (empty($plan['target_dir'])) {
            echo "   ⏭️  保留在根目录: {$filename}\n";
            return;
        }
        
        $targetPath = $this->rootDir . '/' . $plan['target_dir'] . '/' . $filename;
        
        // 检查文件是否需要同时复制到public目录
        if ($plan['web_accessible'] && isset($this->fileCategories[$category]['public_subdir'])) {
            $publicPath = $this->rootDir . '/public/' . $this->fileCategories[$category]['public_subdir'] . '/' . $filename;
            
            // 复制到public目录
            if (copy($sourcePath, $publicPath)) {
                $this->updatePathsInFile($publicPath];
                echo "   📋 复制到public: {$filename}\n";
            }
        }
        
        // 移动到目标目�?
        if (rename($sourcePath, $targetPath)) {
            $this->movedFiles[] = [
                'source' => $filename,
                'target' => $plan['target_dir'] . '/' . $filename,
                'category' => $category
            ];
            echo "   �?移动: {$filename} �?{$plan['target_dir']}/\n";
        } else {
            echo "   �?移动失败: {$filename}\n";
        }
    }
    
    private function updatePathsInFile(string $filePath): void
    {
        if (!str_ends_with($filePath, '.php')) return;
        
        $content = file_get_contents($filePath];
        $originalContent = $content;
        
        // 更新常见的路径引�?
        $pathUpdates = [
            "require_once __DIR__ . '/vendor/autoload.php'" => "require_once __DIR__ . '/../../vendor/autoload.php'",
            "require_once __DIR__ . '/src/" => "require_once __DIR__ . '/../../src/",
            "require_once __DIR__ . '/includes/" => "require_once __DIR__ . '/../../includes/",
            "__DIR__ . '/storage/" => "__DIR__ . '/../../storage/",
            "__DIR__ . '/config/" => "__DIR__ . '/../../config/",
            "__DIR__ . '/logs/" => "__DIR__ . '/../../logs/",
        ];
        
        foreach ($pathUpdates as $old => $new) {
            $content = str_replace($old, $new, $content];
        }
        
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content];
        }
    }
    
    private function optimizePublicStructure(): void
    {
        echo "🎯 优化public文件夹结�?..\n";
        echo str_repeat("-", 80) . "\n";
        
        $publicDir = $this->rootDir . '/public';
        
        // 检查是否需要创建额外的public子目�?
        $publicSubdirs = [
            'docs' => '文档和指�?,
            'downloads' => '下载文件',
            'assets' => '静态资�?,
            'uploads' => '上传文件',
        ];
        
        foreach ($publicSubdirs as $subdir => $description) {
            $path = $publicDir . '/' . $subdir;
            if (!is_dir($path)) {
                $this->createDirectory($path];
                echo "   📁 创建public子目�? {$subdir}/ - {$description}\n";
            }
        }
        
        // 更新工具索引页面
        $this->updateToolsIndex(];
        
        echo "   �?Public结构优化完成\n\n";
    }
    
    private function updateToolsIndex(): void
    {
        $indexPath = $this->rootDir . '/public/tools-index.html';
        
        if (file_exists($indexPath)) {
            echo "   📝 更新工具索引页面\n";
            
            // 这里可以添加更新逻辑，比如扫描新的工具文件并更新链接
        }
    }
    
    private function generateReport(): void
    {
        $reportFile = $this->rootDir . '/PROJECT_ORGANIZATION_REPORT_' . date('Y_m_d_H_i_s') . '.md';
        
        $report = "# AlingAi Pro 5.0 - 项目文件整理报告\n\n";
        $report .= "## 整理概览\n";
        $report .= "- **整理时间**: " . date('Y年m月d�?H:i:s') . "\n";
        $report .= "- **移动文件**: " . count($this->movedFiles) . " 个\n";
        $report .= "- **创建目录**: " . count($this->createdDirectories) . " 个\n\n";
        
        $report .= "## 新的项目结构\n\n";
        $report .= "```\n";
        $report .= "AlingAi_pro/\n";
        $report .= "├── public/                 # Web可访问文件\n";
        $report .= "�?  ├── admin/              # 管理后台\n";
        $report .= "�?  ├── api/                # API服务\n";
        $report .= "�?  ├── test/               # Web测试工具\n";
        $report .= "�?  ├── monitor/            # 监控工具\n";
        $report .= "�?  ├── tools/              # 系统工具\n";
        $report .= "�?  ├── docs/               # 在线文档\n";
        $report .= "�?  └── assets/             # 静态资源\n";
        $report .= "├── src/                    # 核心源代码\n";
        $report .= "├── database/               # 数据库相关\n";
        $report .= "�?  ├── migrations/         # 数据库迁移\n";
        $report .= "�?  └── management/         # 数据库管理脚本\n";
        $report .= "├── services/               # 后台服务\n";
        $report .= "�?  ├── websocket/          # WebSocket服务器\n";
        $report .= "�?  └── ...                 # 其他服务\n";
        $report .= "├── tests/                  # 测试文件\n";
        $report .= "├── tools/                  # 系统工具\n";
        $report .= "├── deployment/             # 部署脚本\n";
        $report .= "├── docs/                   # 项目文档\n";
        $report .= "�?  ├── reports/            # 报告文档\n";
        $report .= "�?  ├── guides/             # 操作指南\n";
        $report .= "�?  └── architecture/       # 架构文档\n";
        $report .= "├── scripts/                # 维护脚本\n";
        $report .= "├── config/                 # 配置文件\n";
        $report .= "└── [核心文件]              # 核心系统文件\n";
        $report .= "```\n\n";
        
        if (!empty($this->movedFiles)) {
            $report .= "## 文件移动记录\n\n";
            
            $byCategory = [];
            foreach ($this->movedFiles as $file) {
                $byCategory[$file['category']][] = $file;
            }
            
            foreach ($byCategory as $category => $files) {
                $categoryName = $this->fileCategories[$category]['description'] ?? $category;
                $report .= "### {$categoryName}\n\n";
                
                foreach ($files as $file) {
                    $report .= "- `{$file['source']}` �?`{$file['target']}`\n";
                }
                $report .= "\n";
            }
        }
        
        $report .= "## 结构优化效果\n\n";
        $report .= "�?**改进成果**:\n";
        $report .= "- 根目录更加整洁，只保留核心文件\n";
        $report .= "- 文件按功能分类，便于维护和查找\n";
        $report .= "- Web可访问文件统一管理在public目录\n";
        $report .= "- 测试、文档、工具等有专门的目录\n";
        $report .= "- 符合现代PHP项目的目录结构标准\n\n";
        
        $report .= "## 使用建议\n\n";
        $report .= "1. **开�?*: 核心代码�?`src/` 目录，遵循PSR-4自动加载\n";
        $report .= "2. **测试**: 使用 `tests/` 目录进行单元测试，`public/test/` 进行集成测试\n";
        $report .= "3. **部署**: 使用 `deployment/` 目录的脚本进行部署\n";
        $report .= "4. **文档**: 查看 `docs/` 目录获取项目文档\n";
        $report .= "5. **工具**: 访问 `http://localhost:8000/tools-index.html` 使用在线工具\n\n";
        
        $report .= "---\n";
        $report .= "*报告生成时间: " . date('Y年m月d�?H:i:s') . "*\n";
        $report .= "*AlingAi Pro 5.0 政企融合智能办公系统*\n";
        
        file_put_contents($reportFile, $report];
        
        echo "📋 整理报告已生�? " . basename($reportFile) . "\n";
        echo "🎉 项目文件整理完成！\n\n";
        
        echo "🔗 项目访问方式:\n";
        echo "  - 管理后台: http://localhost:8000/admin/\n";
        echo "  - 工具目录: http://localhost:8000/tools-index.html\n";
        echo "  - 系统监控: http://localhost:8000/monitor/health.php\n";
    }
}

// 执行整理
try {
    $organizer = new AdvancedProjectOrganizer(];
    $organizer->run(];
} catch (Exception $e) {
    echo "�?整理过程中发生错�? " . $e->getMessage() . "\n";
    exit(1];
}

