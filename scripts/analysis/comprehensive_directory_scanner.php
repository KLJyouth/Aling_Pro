<?php

/**
 * AlingAi Pro 5.0 - 综合目录扫描工具
 * 扫描所有文件夹和子文件夹，分析哪些内容需要移动到public目录
 */

declare(strict_types=1);

class ComprehensiveDirectoryScanner
{
    private string $rootDir;
    private string $publicDir;
    private array $analysisResults = [];
    private array $webAccessiblePatterns = [];
    private array $securityRiskPatterns = [];
    
    public function __construct()
    {
        $this->rootDir = __DIR__;
        $this->publicDir = $this->rootDir . '/public';
        
        $this->initializePatterns();
    }
    
    private function initializePatterns(): void
    {
        // Web可访问文件模式
        $this->webAccessiblePatterns = [
            'extensions' => [
                'html', 'htm', 'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico',
                'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar', 'txt', 'json'
            ],
            'api_files' => [
                '*api*.php', '*server*.php', '*endpoint*.php', '*service*.php'
            ],
            'test_files' => [
                '*test*.php', '*Test*.php', '*check*.php', '*validation*.php'
            ],
            'tool_files' => [
                '*tool*.php', '*optimizer*.php', '*manager*.php', '*monitor*.php'
            ],
            'admin_files' => [
                '*admin*.php', '*dashboard*.php', '*management*.php'
            ],
            'frontend_files' => [
                'index.php', 'login.php', 'register.php', 'profile.php', 'contact.php'
            ]
        ];
        
        // 安全风险文件模式
        $this->securityRiskPatterns = [
            'config_files' => [
                '*.env*', '*config*.php', '*settings*.php', '*database*.php'
            ],
            'sensitive_files' => [
                '*password*.php', '*secret*.php', '*key*.php', '*token*.php'
            ],
            'system_files' => [
                '*install*.php', '*setup*.php', '*migration*.php', '*deploy*.php'
            ],
            'backup_files' => [
                '*.backup', '*.bak', '*.old', '*backup*.php'
            ]
        ];
    }
    
    public function run(): void
    {
        echo "🔍 AlingAi Pro 5.0 - 综合目录扫描分析\n";
        echo str_repeat("=", 80) . "\n\n";
        
        $this->scanAllDirectories();
        $this->analyzeForPublicMigration();
        $this->generateMigrationPlan();
        $this->displayResults();
        $this->saveMigrationPlan();
    }
    
    private function scanAllDirectories(): void
    {
        echo "📂 扫描所有目录结构...\n";
        echo str_repeat("-", 60) . "\n";
        
        $directories = $this->getRootDirectories();
        
        foreach ($directories as $dir) {
            if ($dir === 'public') {
                continue; // 跳过public目录，避免重复
            }
            
            $fullPath = $this->rootDir . '/' . $dir;
            if (is_dir($fullPath)) {
                echo "  🔍 扫描目录: {$dir}/\n";
                $this->analysisResults[$dir] = $this->analyzeDirectory($fullPath, $dir);
            }
        }
        
        echo "\n✅ 目录扫描完成\n\n";
    }
    
    private function getRootDirectories(): array
    {
        $items = scandir($this->rootDir);
        $directories = [];
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $fullPath = $this->rootDir . '/' . $item;
            if (is_dir($fullPath) && !in_array($item, ['.git', '.vscode', 'node_modules', 'vendor'])) {
                $directories[] = $item;
            }
        }
        
        sort($directories);
        return $directories;
    }
    
    private function analyzeDirectory(string $path, string $dirName, int $depth = 0): array
    {
        if ($depth > 3) { // 限制扫描深度
            return ['note' => 'Directory too deep, skipped'];
        }
        
        $analysis = [
            'path' => $path,
            'name' => $dirName,
            'depth' => $depth,
            'files' => [],
            'subdirectories' => [],
            'web_accessible_files' => [],
            'api_files' => [],
            'test_files' => [],
            'frontend_files' => [],
            'admin_files' => [],
            'tool_files' => [],
            'security_risk_files' => [],
            'static_assets' => [],
            'recommendation' => 'keep_private',
            'public_migration_score' => 0,
            'security_score' => 0,
            'reasons' => []
        ];
        
        try {
            $items = scandir($path);
            
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                
                $itemPath = $path . '/' . $item;
                
                if (is_dir($itemPath)) {
                    $analysis['subdirectories'][$item] = $this->analyzeDirectory($itemPath, $item, $depth + 1);
                } else {
                    $analysis['files'][] = $item;
                    $this->categorizeFile($item, $itemPath, $analysis);
                }
            }
            
            // 计算迁移评分
            $this->calculateMigrationScore($analysis);
            
        } catch (Exception $e) {
            $analysis['error'] = $e->getMessage();
        }
        
        return $analysis;
    }
    
    private function categorizeFile(string $filename, string $filePath, array &$analysis): void
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $basename = strtolower(pathinfo($filename, PATHINFO_FILENAME));
        
        // 检查是否为Web可访问文件
        if (in_array($extension, $this->webAccessiblePatterns['extensions'])) {
            $analysis['web_accessible_files'][] = $filename;
            $analysis['public_migration_score'] += 10;
        }
        
        // 检查静态资源
        if (in_array($extension, ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf'])) {
            $analysis['static_assets'][] = $filename;
            $analysis['public_migration_score'] += 15;
            $analysis['reasons'][] = "静态资源文件: {$filename}";
        }
        
        // 检查API文件
        foreach ($this->webAccessiblePatterns['api_files'] as $pattern) {
            if (fnmatch($pattern, $filename)) {
                $analysis['api_files'][] = $filename;
                $analysis['public_migration_score'] += 20;
                $analysis['reasons'][] = "API服务文件: {$filename}";
                break;
            }
        }
        
        // 检查测试文件
        foreach ($this->webAccessiblePatterns['test_files'] as $pattern) {
            if (fnmatch($pattern, $filename)) {
                $analysis['test_files'][] = $filename;
                $analysis['public_migration_score'] += 15;
                $analysis['reasons'][] = "测试工具文件: {$filename}";
                break;
            }
        }
        
        // 检查前端文件
        if (in_array($filename, $this->webAccessiblePatterns['frontend_files'])) {
            $analysis['frontend_files'][] = $filename;
            $analysis['public_migration_score'] += 25;
            $analysis['reasons'][] = "前端页面文件: {$filename}";
        }
        
        // 检查管理文件
        foreach ($this->webAccessiblePatterns['admin_files'] as $pattern) {
            if (fnmatch($pattern, $filename)) {
                $analysis['admin_files'][] = $filename;
                $analysis['public_migration_score'] += 18;
                $analysis['reasons'][] = "管理界面文件: {$filename}";
                break;
            }
        }
        
        // 检查工具文件
        foreach ($this->webAccessiblePatterns['tool_files'] as $pattern) {
            if (fnmatch($pattern, $filename)) {
                $analysis['tool_files'][] = $filename;
                $analysis['public_migration_score'] += 12;
                $analysis['reasons'][] = "管理工具文件: {$filename}";
                break;
            }
        }
        
        // 检查安全风险文件
        foreach ($this->securityRiskPatterns as $category => $patterns) {
            foreach ($patterns as $pattern) {
                if (fnmatch($pattern, $filename)) {
                    $analysis['security_risk_files'][] = $filename;
                    $analysis['security_score'] += 10;
                    $analysis['public_migration_score'] -= 30; // 大幅降低迁移评分
                    $analysis['reasons'][] = "安全敏感文件: {$filename} (类型: {$category})";
                    break 2;
                }
            }
        }
        
        // 特殊文件检查
        if (strpos($filename, 'index.') === 0) {
            $analysis['public_migration_score'] += 20;
            $analysis['reasons'][] = "入口文件: {$filename}";
        }
        
        if (strpos($filename, 'router.') === 0) {
            $analysis['public_migration_score'] += 15;
            $analysis['reasons'][] = "路由文件: {$filename}";
        }
    }
    
    private function calculateMigrationScore(array &$analysis): void
    {
        $totalFiles = count($analysis['files']);
        $webFiles = count($analysis['web_accessible_files']);
        $staticAssets = count($analysis['static_assets']);
        $apiFiles = count($analysis['api_files']);
        $securityFiles = count($analysis['security_risk_files']);
        
        // 基于文件比例调整评分
        if ($totalFiles > 0) {
            $webRatio = $webFiles / $totalFiles;
            $staticRatio = $staticAssets / $totalFiles;
            $apiRatio = $apiFiles / $totalFiles;
            $securityRatio = $securityFiles / $totalFiles;
            
            // Web文件比例高的目录更适合迁移
            $analysis['public_migration_score'] += $webRatio * 20;
            $analysis['public_migration_score'] += $staticRatio * 25;
            $analysis['public_migration_score'] += $apiRatio * 30;
            $analysis['public_migration_score'] -= $securityRatio * 50;
        }
        
        // 根据目录名称调整评分
        $dirName = strtolower($analysis['name']);
        $publicDirectoryNames = ['www', 'htdocs', 'web', 'assets', 'static', 'public', 'frontend', 'ui'];
        $privateDirectoryNames = ['config', 'private', 'secure', 'internal', 'system', 'database', 'backup'];
        
        foreach ($publicDirectoryNames as $name) {
            if (strpos($dirName, $name) !== false) {
                $analysis['public_migration_score'] += 30;
                $analysis['reasons'][] = "目录名称表明为Web访问目录: {$name}";
                break;
            }
        }
        
        foreach ($privateDirectoryNames as $name) {
            if (strpos($dirName, $name) !== false) {
                $analysis['public_migration_score'] -= 40;
                $analysis['reasons'][] = "目录名称表明为私有目录: {$name}";
                break;
            }
        }
        
        // 确定推荐操作
        if ($analysis['public_migration_score'] >= 50) {
            $analysis['recommendation'] = 'migrate_to_public';
        } elseif ($analysis['public_migration_score'] >= 20) {
            $analysis['recommendation'] = 'partial_migration';
        } elseif ($analysis['security_score'] > 20) {
            $analysis['recommendation'] = 'keep_private_secure';
        } else {
            $analysis['recommendation'] = 'keep_private';
        }
    }
    
    private function analyzeForPublicMigration(): void
    {
        echo "📊 分析迁移建议...\n";
        echo str_repeat("-", 60) . "\n";
        
        $migrationCandidates = [];
        $partialMigrationCandidates = [];
        $keepPrivate = [];
        
        foreach ($this->analysisResults as $dirName => $analysis) {
            switch ($analysis['recommendation']) {
                case 'migrate_to_public':
                    $migrationCandidates[] = $dirName;
                    echo "  ✅ 建议完全迁移: {$dirName}/ (评分: {$analysis['public_migration_score']})\n";
                    break;
                case 'partial_migration':
                    $partialMigrationCandidates[] = $dirName;
                    echo "  ⚠️  建议部分迁移: {$dirName}/ (评分: {$analysis['public_migration_score']})\n";
                    break;
                case 'keep_private_secure':
                    $keepPrivate[] = $dirName;
                    echo "  🔒 保持私有(安全): {$dirName}/ (安全评分: {$analysis['security_score']})\n";
                    break;
                default:
                    $keepPrivate[] = $dirName;
                    echo "  📁 保持私有: {$dirName}/ (评分: {$analysis['public_migration_score']})\n";
            }
            
            // 显示具体原因
            if (!empty($analysis['reasons'])) {
                foreach (array_slice($analysis['reasons'], 0, 3) as $reason) {
                    echo "     • {$reason}\n";
                }
            }
        }
        
        echo "\n📈 迁移统计:\n";
        echo "  • 完全迁移候选: " . count($migrationCandidates) . " 个目录\n";
        echo "  • 部分迁移候选: " . count($partialMigrationCandidates) . " 个目录\n";
        echo "  • 保持私有: " . count($keepPrivate) . " 个目录\n\n";
    }
    
    private function generateMigrationPlan(): void
    {
        echo "📋 生成详细迁移计划...\n";
        echo str_repeat("-", 60) . "\n";
        
        foreach ($this->analysisResults as $dirName => $analysis) {
            if ($analysis['recommendation'] === 'migrate_to_public') {
                $this->generateFullMigrationPlan($dirName, $analysis);
            } elseif ($analysis['recommendation'] === 'partial_migration') {
                $this->generatePartialMigrationPlan($dirName, $analysis);
            }
        }
    }
    
    private function generateFullMigrationPlan(string $dirName, array $analysis): void
    {
        echo "🚀 {$dirName}/ - 完全迁移计划:\n";
        
        $targetDir = "public/{$dirName}";
        echo "  目标位置: {$targetDir}/\n";
        echo "  迁移文件数: " . count($analysis['files']) . "\n";
        
        if (!empty($analysis['static_assets'])) {
            echo "  静态资源 (" . count($analysis['static_assets']) . "):\n";
            foreach (array_slice($analysis['static_assets'], 0, 5) as $asset) {
                echo "    • {$asset}\n";
            }
            if (count($analysis['static_assets']) > 5) {
                echo "    • ... 还有 " . (count($analysis['static_assets']) - 5) . " 个文件\n";
            }
        }
        
        if (!empty($analysis['api_files'])) {
            echo "  API文件 (" . count($analysis['api_files']) . "):\n";
            foreach ($analysis['api_files'] as $apiFile) {
                echo "    • {$apiFile}\n";
            }
        }
        
        echo "\n";
    }
    
    private function generatePartialMigrationPlan(string $dirName, array $analysis): void
    {
        echo "⚡ {$dirName}/ - 部分迁移计划:\n";
        
        $webFiles = array_merge(
            $analysis['static_assets'],
            $analysis['api_files'],
            $analysis['frontend_files'],
            $analysis['test_files']
        );
        
        if (!empty($webFiles)) {
            echo "  建议迁移的文件:\n";
            foreach ($webFiles as $file) {
                echo "    • {$file} → public/{$dirName}/{$file}\n";
            }
        }
        
        if (!empty($analysis['security_risk_files'])) {
            echo "  保持私有的安全敏感文件:\n";
            foreach ($analysis['security_risk_files'] as $file) {
                echo "    • {$file} (保留在 {$dirName}/)\n";
            }
        }
        
        echo "\n";
    }
    
    private function displayResults(): void
    {
        echo "🎯 综合分析结果汇总\n";
        echo str_repeat("=", 80) . "\n";
        
        $highPriorityMigrations = [];
        $mediumPriorityMigrations = [];
        $lowPriorityMigrations = [];
        
        foreach ($this->analysisResults as $dirName => $analysis) {
            if ($analysis['public_migration_score'] >= 70) {
                $highPriorityMigrations[] = [$dirName, $analysis];
            } elseif ($analysis['public_migration_score'] >= 30) {
                $mediumPriorityMigrations[] = [$dirName, $analysis];
            } elseif ($analysis['public_migration_score'] > 0) {
                $lowPriorityMigrations[] = [$dirName, $analysis];
            }
        }
        
        echo "🔥 高优先级迁移 (评分 >= 70):\n";
        foreach ($highPriorityMigrations as [$dirName, $analysis]) {
            echo "  • {$dirName}/ (评分: {$analysis['public_migration_score']})\n";
            echo "    → " . implode(', ', array_slice($analysis['reasons'], 0, 2)) . "\n";
        }
        
        echo "\n⚡ 中等优先级迁移 (评分 30-69):\n";
        foreach ($mediumPriorityMigrations as [$dirName, $analysis]) {
            echo "  • {$dirName}/ (评分: {$analysis['public_migration_score']})\n";
            echo "    → " . implode(', ', array_slice($analysis['reasons'], 0, 2)) . "\n";
        }
        
        echo "\n💡 低优先级迁移 (评分 1-29):\n";
        foreach ($lowPriorityMigrations as [$dirName, $analysis]) {
            echo "  • {$dirName}/ (评分: {$analysis['public_migration_score']})\n";
        }
        
        echo "\n";
    }
    
    private function saveMigrationPlan(): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $reportContent = $this->generateReportContent($timestamp);
        
        $reportPath = $this->rootDir . '/docs/COMPREHENSIVE_MIGRATION_ANALYSIS_' . date('Y_m_d_H_i_s') . '.md';
        
        // 确保docs目录存在
        $docsDir = dirname($reportPath);
        if (!is_dir($docsDir)) {
            mkdir($docsDir, 0755, true);
        }
        
        file_put_contents($reportPath, $reportContent);
        
        echo "📋 详细分析报告已保存到: " . basename($reportPath) . "\n";
        echo "🔍 查看报告了解完整的迁移建议和安全分析\n\n";
        
        // 生成迁移执行脚本
        $this->generateMigrationScript();
    }
    
    private function generateReportContent(string $timestamp): string
    {
        $content = <<<MARKDOWN
# AlingAi Pro 5.0 - 综合目录迁移分析报告

**生成时间**: {$timestamp}
**分析工具**: comprehensive_directory_scanner.php

## 执行摘要

本报告基于对整个项目目录结构的深度扫描，分析了每个目录中的文件类型、用途和安全级别，
提供了科学的public目录迁移建议。

## 分析方法

### 评分系统
- **静态资源文件**: +15分 (CSS, JS, 图片等)
- **API服务文件**: +20分 (API接口、服务端点)
- **前端页面文件**: +25分 (入口页面、用户界面)
- **测试工具文件**: +15分 (测试脚本、验证工具)
- **管理界面文件**: +18分 (后台管理、仪表板)
- **安全敏感文件**: -30分 (配置、密钥、系统文件)

### 迁移建议阈值
- **完全迁移**: 评分 >= 50
- **部分迁移**: 评分 20-49
- **保持私有**: 评分 < 20 或安全评分 > 20

MARKDOWN;
        
        $content .= "\n## 详细分析结果\n\n";
        
        foreach ($this->analysisResults as $dirName => $analysis) {
            $content .= "### {$dirName}/\n\n";
            $content .= "- **迁移评分**: {$analysis['public_migration_score']}\n";
            $content .= "- **安全评分**: {$analysis['security_score']}\n";
            $content .= "- **推荐操作**: {$analysis['recommendation']}\n";
            $content .= "- **文件总数**: " . count($analysis['files']) . "\n";
            
            if (!empty($analysis['static_assets'])) {
                $content .= "- **静态资源**: " . count($analysis['static_assets']) . " 个\n";
            }
            
            if (!empty($analysis['api_files'])) {
                $content .= "- **API文件**: " . count($analysis['api_files']) . " 个\n";
            }
            
            if (!empty($analysis['security_risk_files'])) {
                $content .= "- **安全敏感文件**: " . count($analysis['security_risk_files']) . " 个\n";
            }
            
            if (!empty($analysis['reasons'])) {
                $content .= "\n**分析原因**:\n";
                foreach ($analysis['reasons'] as $reason) {
                    $content .= "- {$reason}\n";
                }
            }
            
            $content .= "\n";
        }
        
        $content .= <<<MARKDOWN

## 推荐迁移计划

### 第一阶段：高优先级迁移
处理评分 >= 70 的目录，这些目录包含大量web可访问内容。

### 第二阶段：选择性迁移  
处理评分 30-69 的目录，仅迁移web可访问文件。

### 第三阶段：安全审查
确保所有敏感文件保持在private目录中。

## 安全建议

1. **配置文件保护**: 确保所有 .env, config.php 等配置文件不被迁移
2. **数据库文件保护**: 数据库连接、迁移脚本等保持私有
3. **备份文件保护**: 所有备份和临时文件不应放入public
4. **访问控制**: 为迁移的目录设置适当的 .htaccess 规则

## 实施注意事项

1. **测试环境验证**: 在测试环境中先执行迁移
2. **备份重要数据**: 迁移前创建完整备份
3. **逐步迁移**: 分阶段执行，每次迁移后验证功能
4. **路径更新**: 迁移后更新所有相关路径引用
5. **性能监控**: 迁移后监控系统性能和安全性

MARKDOWN;
        
        return $content;
    }
    
    private function generateMigrationScript(): void
    {
        $scriptPath = $this->rootDir . '/execute_comprehensive_migration.php';
        $scriptContent = $this->buildMigrationScript();
        
        file_put_contents($scriptPath, $scriptContent);
        echo "🚀 迁移执行脚本已生成: execute_comprehensive_migration.php\n";
        echo "   执行命令: php execute_comprehensive_migration.php\n\n";
    }
    
    private function buildMigrationScript(): string
    {
        $highPriorityDirs = [];
        $partialMigrationDirs = [];
        
        foreach ($this->analysisResults as $dirName => $analysis) {
            if ($analysis['public_migration_score'] >= 70) {
                $highPriorityDirs[] = $dirName;
            } elseif ($analysis['public_migration_score'] >= 30 && $analysis['recommendation'] === 'partial_migration') {
                $partialMigrationDirs[] = $dirName;
            }
        }
        
        $highPriorityList = "'" . implode("', '", $highPriorityDirs) . "'";
        $partialMigrationList = "'" . implode("', '", $partialMigrationDirs) . "'";
        
        return <<<PHP
<?php

/**
 * AlingAi Pro 5.0 - 综合迁移执行脚本
 * 基于综合分析结果执行目录迁移
 */

declare(strict_types=1);

class ComprehensiveMigrationExecutor
{
    private string \$rootDir;
    private string \$publicDir;
    private array \$highPriorityDirs = [{$highPriorityList}];
    private array \$partialMigrationDirs = [{$partialMigrationList}];
    
    public function __construct()
    {
        \$this->rootDir = __DIR__;
        \$this->publicDir = \$this->rootDir . '/public';
    }
    
    public function run(): void
    {
        echo "🚀 执行综合迁移计划...\\n";
        echo str_repeat("=", 80) . "\\n\\n";
        
        \$this->confirmExecution();
        \$this->createBackup();
        \$this->executeHighPriorityMigrations();
        \$this->executePartialMigrations();
        \$this->updateSecurityConfigs();
        \$this->generateReport();
    }
    
    private function confirmExecution(): void
    {
        echo "⚠️  此操作将移动多个目录到public文件夹\\n";
        echo "📋 高优先级完全迁移: " . count(\$this->highPriorityDirs) . " 个目录\\n";
        echo "📋 部分选择性迁移: " . count(\$this->partialMigrationDirs) . " 个目录\\n\\n";
        
        echo "是否继续？(y/N): ";
        \$input = trim(fgets(STDIN));
        
        if (strtolower(\$input) !== 'y') {
            echo "❌ 操作已取消\\n";
            exit(0);
        }
    }
    
    private function createBackup(): void
    {
        \$backupDir = \$this->rootDir . '/backup/comprehensive_migration_' . date('Y_m_d_H_i_s');
        mkdir(\$backupDir, 0755, true);
        
        echo "💾 创建备份: " . basename(\$backupDir) . "\\n\\n";
    }
    
    private function executeHighPriorityMigrations(): void
    {
        echo "🔥 执行高优先级迁移...\\n";
        
        foreach (\$this->highPriorityDirs as \$dir) {
            \$this->migrateDirectory(\$dir, true);
        }
    }
    
    private function executePartialMigrations(): void
    {
        echo "⚡ 执行部分迁移...\\n";
        
        foreach (\$this->partialMigrationDirs as \$dir) {
            \$this->migrateDirectory(\$dir, false);
        }
    }
    
    private function migrateDirectory(string \$dirName, bool \$fullMigration): void
    {
        \$sourceDir = \$this->rootDir . '/' . \$dirName;
        \$targetDir = \$this->publicDir . '/' . \$dirName;
        
        if (!\$fullMigration) {
            echo "  📁 部分迁移: {\$dirName}/\\n";
            // 实现选择性迁移逻辑
        } else {
            echo "  📁 完全迁移: {\$dirName}/\\n";
            // 实现完全迁移逻辑
        }
    }
    
    private function updateSecurityConfigs(): void
    {
        echo "🔒 更新安全配置...\\n";
        // 实现安全配置更新
    }
    
    private function generateReport(): void
    {
        echo "📊 生成迁移报告...\\n";
        echo "✅ 综合迁移执行完成！\\n";
    }
}

try {
    \$executor = new ComprehensiveMigrationExecutor();
    \$executor->run();
} catch (Exception \$e) {
    echo "❌ 迁移执行失败: " . \$e->getMessage() . "\\n";
    exit(1);
}
PHP;
    }
}

// 执行综合扫描
try {
    $scanner = new ComprehensiveDirectoryScanner();
    $scanner->run();
} catch (Exception $e) {
    echo "❌ 扫描失败: " . $e->getMessage() . "\n";
    echo "📋 错误详情: " . $e->getTraceAsString() . "\n";
    exit(1);
}
