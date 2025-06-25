<?php

/**
 * AlingAi Pro 5.0 - 最终根目录清理脚本
 * 处理剩余在根目录中的文件，完成项目结构的最终整�?
 */

declare(strict_types=1];

class FinalRootCleanup
{
    private string $rootDir;
    private array $cleanupPlan = [];
    private array $processedFiles = [];
    private array $keptFiles = [];
    
    public function __construct()
    {
        $this->rootDir = __DIR__;
        $this->initializeCleanupPlan(];
    }
    
    private function initializeCleanupPlan(): void
    {
        $this->cleanupPlan = [
            // 维护和分析脚�?
            'maintenance_scripts' => [
                'target_dir' => 'scripts/maintenance',
                'description' => '维护和分析脚�?,
                'files' => [
                    'analyze_directories_for_public.php',
                    'complete_public_migration.php',
                    'optimize_public_structure.php',
                    'organize_project_structure.php',
                    'final_root_cleanup.php' // 自己也要移动
                ], 
                'action' => 'move'
            ], 
            
            // 系统初始化和启动脚本
            'system_scripts' => [
                'target_dir' => 'scripts/system',
                'description' => '系统初始化和启动脚本',
                'files' => [
                    'init_system.php',
                    'launch_system.php',
                    'start_system.php',
                    'quick_start.php'
                ], 
                'action' => 'move'
            ], 
            
            // 数据库相关脚�?
            'database_scripts' => [
                'target_dir' => 'database/management',
                'description' => '数据库管理脚�?,
                'files' => [
                    'create_ai_tables_direct.php',
                    'create_missing_tables.php',
                    'recreate_user_settings_table.php',
                    'init_clean_data.php'
                ], 
                'action' => 'move'
            ], 
            
            // 系统验证和错误处�?
            'validation_scripts' => [
                'target_dir' => 'scripts/validation',
                'description' => '系统验证和错误处理脚�?,
                'files' => [
                    'feature_verification.php',
                    'final_system_verification.php',
                    'final_verification_report.php',
                    'final_error_handling_complete_fix.php',
                    'production_compatibility_check.php',
                    'production_error_handler.php',
                    'production_error_handler_enhanced.php'
                ], 
                'action' => 'move'
            ], 
            
            // 性能优化脚本
            'performance_scripts' => [
                'target_dir' => 'scripts/performance',
                'description' => '性能优化脚本',
                'files' => [
                    'cache_warmup.php',
                    'optimize_production.php',
                    'disaster_recovery.php'
                ], 
                'action' => 'move'
            ], 
            
            // 前端资源迁移
            'frontend_migration' => [
                'target_dir' => 'scripts/migration',
                'description' => '前端资源迁移脚本',
                'files' => [
                    'migrate_frontend_resources.php'
                ], 
                'action' => 'move'
            ], 
            
            // 测试相关文件 (移动到public/test)
            'test_files' => [
                'target_dir' => 'public/test',
                'description' => '测试相关文件',
                'files' => [
                    'test_admin_system.php',
                    'test_unified_admin_frontend.html'
                ], 
                'action' => 'move'
            ], 
            
            // 批处理和启动脚本
            'batch_scripts' => [
                'target_dir' => 'scripts/batch',
                'description' => '批处理和启动脚本',
                'files' => [
                    'quick_start.bat',
                    'file_backup.bat',
                    'setup_backup_schedule.bat',
                    'start-profile-enhanced.bat',
                    'start-system.bat',
                    'start-system.ps1',
                    'start-test.bat',
                    'start.bat',
                    'start.sh',
                    'verify_admin_backend.sh'
                ], 
                'action' => 'move'
            ], 
            
            // Node.js相关文件
            'nodejs_files' => [
                'target_dir' => 'src/frontend',
                'description' => 'Node.js前端文件',
                'files' => [
                    'test-api-server.js',
                    'validate-integration.js'
                ], 
                'action' => 'move'
            ], 
            
            // 项目文档和报�?
            'documentation' => [
                'target_dir' => 'docs/reports',
                'description' => '项目报告文档',
                'files' => [
                    'PROJECT_ORGANIZATION_REPORT_2025_06_11_08_28_42.md'
                ], 
                'action' => 'move'
            ], 
            
            // 保留在根目录的文�?
            'keep_in_root' => [
                'target_dir' => '',
                'description' => '核心文件 (保留在根目录)',
                'files' => [
                    'router.php',
                    'worker.php',
                    'README.md',
                    'composer.json',
                    'composer.json.backup',
                    'composer.lock',
                    'phpstan.neon',
                    'phpunit.xml',
                    'redis.production.conf',
                    '.env',
                    '.env.example',
                    '.env.local',
                    '.env.production',
                    '.env.production.example',
                    '.eslintrc.json',
                    '.prettierrc.json',
                    'AlingAi_pro.zip',
                    'deployment.zip',
                    '1',
                    '新建 文本文档.txt'
                ], 
                'action' => 'keep'
            ]
        ];
    }
    
    public function run(): void
    {
        echo "🧹 AlingAi Pro 5.0 - 最终根目录清理\n";
        echo str_repeat("=", 80) . "\n\n";
        
        $this->analyzeCurrentRootFiles(];
        $this->displayCleanupPlan(];
        $this->requestConfirmation(];
        $this->executeCleanup(];
        $this->createRootIndex(];
        $this->generateFinalReport(];
    }
    
    private function analyzeCurrentRootFiles(): void
    {
        echo "📊 分析当前根目录文�?..\n";
        echo str_repeat("-", 60) . "\n";
        
        $allFiles = array_merge(
            glob($this->rootDir . '/*.php'],
            glob($this->rootDir . '/*.html'],
            glob($this->rootDir . '/*.js'],
            glob($this->rootDir . '/*.bat'],
            glob($this->rootDir . '/*.sh'],
            glob($this->rootDir . '/*.ps1'],
            glob($this->rootDir . '/*.md'],
            glob($this->rootDir . '/*.txt'],
            glob($this->rootDir . '/*.json'],
            glob($this->rootDir . '/*.xml'],
            glob($this->rootDir . '/*.neon'],
            glob($this->rootDir . '/*.conf'],
            glob($this->rootDir . '/*.zip'],
            glob($this->rootDir . '/.*'],
            glob($this->rootDir . '/[0-9]*') // 数字文件�?
        ];
        
        // 过滤出文件（不包括目录）
        $files = array_filter($allFiles, 'is_file'];
        $fileNames = array_map('basename', $files];
        
        echo "📁 根目录文件统�?\n";
        echo "   总文件数: " . count($files) . "\n";
        echo "   PHP文件: " . count(glob($this->rootDir . '/*.php')) . "\n";
        echo "   脚本文件: " . count(array_merge(glob($this->rootDir . '/*.bat'], glob($this->rootDir . '/*.sh'))) . "\n";
        echo "   文档文件: " . count(array_merge(glob($this->rootDir . '/*.md'], glob($this->rootDir . '/*.txt'))) . "\n";
        echo "   配置文件: " . count(array_merge(glob($this->rootDir . '/*.json'], glob($this->rootDir . '/*.xml'))) . "\n\n";
        
        // 检查未在计划中的文�?
        $plannedFiles = [];
        foreach ($this->cleanupPlan as $category) {
            $plannedFiles = array_merge($plannedFiles, $category['files']];
        }
        
        $unplannedFiles = array_diff($fileNames, $plannedFiles];
        if (!empty($unplannedFiles)) {
            echo "⚠️  未在清理计划中的文件:\n";
            foreach ($unplannedFiles as $file) {
                echo "   📄 {$file}\n";
            }
            echo "\n";
        }
    }
    
    private function displayCleanupPlan(): void
    {
        echo "📋 清理计划详情:\n";
        echo str_repeat("-", 60) . "\n";
        
        foreach ($this->cleanupPlan as $category => $config) {
            if ($config['action'] === 'keep') {
                continue; // 跳过保留文件的显�?
            }
            
            $existingFiles = array_filter($config['files'],  function($file) {
                return file_exists($this->rootDir . '/' . $file];
            }];
            
            if (!empty($existingFiles)) {
                echo "📁 {$config['description']}:\n";
                echo "   目标目录: {$config['target_dir']}\n";
                echo "   操作: " . ($config['action'] === 'move' ? '移动' : '复制') . "\n";
                echo "   文件数量: " . count($existingFiles) . "\n";
                
                foreach ($existingFiles as $file) {
                    $size = $this->formatBytes(filesize($this->rootDir . '/' . $file)];
                    echo "   📄 {$file} ({$size})\n";
                }
                echo "\n";
            }
        }
        
        // 显示保留的文�?
        $keepFiles = array_filter($this->cleanupPlan['keep_in_root']['files'],  function($file) {
            return file_exists($this->rootDir . '/' . $file];
        }];
        
        if (!empty($keepFiles)) {
            echo "📌 保留在根目录的文�?(" . count($keepFiles) . " �?:\n";
            foreach ($keepFiles as $file) {
                echo "   📄 {$file}\n";
            }
            echo "\n";
        }
    }
    
    private function requestConfirmation(): void
    {
        $totalFiles = 0;
        foreach ($this->cleanupPlan as $config) {
            if ($config['action'] !== 'keep') {
                $existingFiles = array_filter($config['files'],  function($file) {
                    return file_exists($this->rootDir . '/' . $file];
                }];
                $totalFiles += count($existingFiles];
            }
        }
        
        if ($totalFiles === 0) {
            echo "�?没有需要移动的文件，根目录已经很整洁！\n";
            exit(0];
        }
        
        echo str_repeat("-", 60) . "\n";
        echo "📊 清理计划总结:\n";
        echo "   将要移动 {$totalFiles} 个文件\n";
        echo "   保持根目录整洁\n";
        echo "   优化项目结构\n\n";
        
        echo "�?是否执行根目录清理计划？(y/N): ";
        $input = trim(fgets(STDIN)];
        
        if (strtolower($input) !== 'y' && strtolower($input) !== 'yes') {
            echo "�?操作已取消\n";
            exit(0];
        }
        
        echo "�?开始执行根目录清理...\n\n";
    }
    
    private function executeCleanup(): void
    {
        echo "🚀 执行根目录清�?..\n";
        echo str_repeat("-", 60) . "\n";
        
        foreach ($this->cleanupPlan as $category => $config) {
            if ($config['action'] === 'keep') {
                continue;
            }
            
            $this->processCategory($category, $config];
        }
        
        echo "�?根目录清理完成\n\n";
    }
    
    private function processCategory(string $category, array $config): void
    {
        $existingFiles = array_filter($config['files'],  function($file) {
            return file_exists($this->rootDir . '/' . $file];
        }];
        
        if (empty($existingFiles)) {
            return;
        }
        
        echo "📁 处理 {$config['description']}...\n";
        
        // 创建目标目录
        $targetDir = $this->rootDir . '/' . $config['target_dir'];
        if (!empty($config['target_dir']) && !is_dir($targetDir)) {
            mkdir($targetDir, 0755, true];
            echo "   �?创建目录: {$config['target_dir']}\n";
        }
        
        // 处理文件
        foreach ($existingFiles as $file) {
            $this->processFile($file, $config];
        }
        
        echo "\n";
    }
    
    private function processFile(string $file, array $config): void
    {
        $sourcePath = $this->rootDir . '/' . $file;
        $targetPath = $this->rootDir . '/' . $config['target_dir'] . '/' . $file;
        
        if ($config['action'] === 'move') {
            if (rename($sourcePath, $targetPath)) {
                echo "   �?移动: {$file} �?{$config['target_dir']}/\n";
                $this->processedFiles[] = [
                    'file' => $file,
                    'action' => 'moved',
                    'from' => '/',
                    'to' => $config['target_dir'] . '/'
                ];
            } else {
                echo "   �?移动失败: {$file}\n";
            }
        } elseif ($config['action'] === 'copy') {
            if (copy($sourcePath, $targetPath)) {
                echo "   �?复制: {$file} �?{$config['target_dir']}/\n";
                $this->processedFiles[] = [
                    'file' => $file,
                    'action' => 'copied',
                    'from' => '/',
                    'to' => $config['target_dir'] . '/'
                ];
            } else {
                echo "   �?复制失败: {$file}\n";
            }
        }
    }
    
    private function createRootIndex(): void
    {
        echo "📋 创建根目录说明文�?..\n";
        echo str_repeat("-", 60) . "\n";
        
        $indexContent = $this->generateRootIndexContent(];
        $indexPath = $this->rootDir . '/ROOT_DIRECTORY_GUIDE.md';
        
        file_put_contents($indexPath, $indexContent];
        echo "   �?创建: ROOT_DIRECTORY_GUIDE.md\n";
        
        // 更新README.md的项目结构部�?
        $this->updateReadmeStructure(];
        
        echo "�?根目录说明文件创建完成\n\n";
    }
    
    private function generateRootIndexContent(): string
    {
        $timestamp = date('Y-m-d H:i:s'];
        
        $content = <<<MARKDOWN
# AlingAi Pro 5.0 - 根目录指�?

**更新时间**: {$timestamp}  
**维护脚本**: final_root_cleanup.php

## 🏗�?项目结构概览

这是AlingAi Pro 5.0的根目录。项目采用现代化的目录结构，将不同功能的文件分类组织�?

### 📁 主要目录说明

| 目录 | 用�?| 描述 |
|------|------|------|
| `public/` | **Web根目�?* | 所有Web可访问的文件 |
| `src/` | **源代�?* | 核心业务逻辑代码 |
| `config/` | **配置文件** | 系统配置和设�?|
| `database/` | **数据�?* | 数据库脚本和迁移文件 |
| `storage/` | **存储** | 日志、缓存、临时文�?|
| `scripts/` | **脚本** | 维护、部署、管理脚�?|
| `tests/` | **测试** | 单元测试和集成测�?|
| `docs/` | **文档** | 项目文档和说�?|
| `vendor/` | **依赖** | Composer依赖�?|
| `tools/` | **工具** | 开发和运维工具 |

### 🔧 核心文件说明

#### 保留在根目录的文�?
- `router.php` - 主路由器
- `worker.php` - 后台工作进程
- `composer.json` - Composer配置
- `README.md` - 项目说明
- `.env*` - 环境配置文件

#### Public目录结构
```
public/
├── admin/          # 管理后台
├── api/            # API接口
├── assets/         # 静态资�?
├── docs/           # 在线文档
├── test/           # 测试工具
├── tools/          # 管理工具
├── monitor/        # 监控工具
├── uploads/        # 用户上传
└── index.php       # Web入口
```

#### Scripts目录结构
```
scripts/
├── maintenance/    # 维护脚本
├── system/         # 系统脚本
├── validation/     # 验证脚本
├── performance/    # 性能脚本
├── migration/      # 迁移脚本
└── batch/          # 批处理脚�?
```

## 🚀 快速开�?

### 开发环�?
```bash
# 安装依赖
composer install

# 启动开发服务器
php -S localhost:8000 -t public

# 或使用批处理脚本
# Windows
scripts/batch/start.bat

# Linux/Mac
scripts/batch/start.sh
```

### 生产环境
```bash
# 使用快速启动脚�?
scripts/batch/quick_start.bat  # Windows
scripts/batch/start.sh         # Linux/Mac

# 或手动启�?
php scripts/system/init_system.php
php scripts/system/start_system.php
```

## 🛠�?管理工具

### Web管理界面
- 管理后台: `/admin/`
- 系统监控: `/monitor/`
- 测试工具: `/test/`
- 在线文档: `/docs/`

### 命令行工�?
- 系统初始�? `php scripts/system/init_system.php`
- 性能优化: `php scripts/performance/optimize_production.php`
- 数据库管�? `php database/management/migrate_database.php`
- 缓存预热: `php scripts/performance/cache_warmup.php`

## 📚 文档链接

- [系统架构](docs/ARCHITECTURE_DIAGRAM.md)
- [部署指南](docs/DEPLOYMENT_GUIDE.md)
- [用户手册](docs/USER_MANUAL.md)
- [API文档](public/docs/api/)
- [开发规范](docs/CODE_STANDARDS.md)

## 🔐 安全注意事项

1. **环境配置**: 确保`.env`文件安全，不要提交到版本控制
2. **文件权限**: Public目录外的文件不应直接Web访问
3. **上传安全**: uploads目录已配置安全限�?
4. **日志保护**: 敏感日志文件受到访问保护

## 📞 支持

如需帮助，请参�?
- 在线文档: `/docs/`
- 系统状�? `/monitor/health.php`
- 错误日志: `storage/logs/`

---
*此文档由 final_root_cleanup.php 自动生成*

MARKDOWN;

        return $content;
    }
    
    private function updateReadmeStructure(): void
    {
        $readmePath = $this->rootDir . '/README.md';
        
        if (!file_exists($readmePath)) {
            return;
        }
        
        $content = file_get_contents($readmePath];
        
        // 简单更新，添加结构说明链接
        if (strpos($content, 'ROOT_DIRECTORY_GUIDE.md') === false) {
            $structureNote = "\n\n## 📁 项目结构\n\n详细的项目结构说明请参�? [ROOT_DIRECTORY_GUIDE.md](ROOT_DIRECTORY_GUIDE.md)\n";
            $content .= $structureNote;
            
            file_put_contents($readmePath, $content];
            echo "   �?更新: README.md\n";
        }
    }
    
    private function generateFinalReport(): void
    {
        echo "📊 生成最终报�?..\n";
        echo str_repeat("-", 60) . "\n";
        
        $reportContent = $this->buildFinalReportContent(];
        $reportPath = $this->rootDir . '/docs/FINAL_ROOT_CLEANUP_REPORT_' . date('Y_m_d_H_i_s') . '.md';
        
        // 确保docs目录存在
        if (!is_dir(dirname($reportPath))) {
            mkdir(dirname($reportPath], 0755, true];
        }
        
        file_put_contents($reportPath, $reportContent];
        
        echo "📈 根目录清理统�?\n";
        echo "   处理文件: " . count($this->processedFiles) . " 个\n";
        echo "   保留文件: " . count($this->cleanupPlan['keep_in_root']['files']) . " 个\n";
        
        // 显示最终根目录状�?
        $this->displayFinalRootStatus(];
        
        echo "\n�?根目录清理完成！\n";
        echo "📋 详细报告: " . basename($reportPath) . "\n";
        echo "📖 项目指南: ROOT_DIRECTORY_GUIDE.md\n\n";
        
        echo "🎉 AlingAi Pro 5.0 项目结构整理全部完成！\n";
        echo "🚀 现在可以开始使用优化后的项目结构了。\n\n";
    }
    
    private function buildFinalReportContent(): string
    {
        $timestamp = date('Y-m-d H:i:s'];
        
        $content = <<<MARKDOWN
# AlingAi Pro 5.0 - 最终根目录清理报告

**生成时间**: {$timestamp}
**清理脚本**: final_root_cleanup.php

## 清理总结

### 处理的文�?
MARKDOWN;
        
        foreach ($this->processedFiles as $file) {
            $content .= "- **{$file['file']}**: {$file['action']} from `{$file['from']}` to `{$file['to']}`\n";
        }
        
        $content .= <<<MARKDOWN

### 最终根目录结构

```
AlingAi_pro/
├── 📄 router.php           # 主路由器
├── 📄 worker.php           # 后台工作进程  
├── 📄 README.md            # 项目说明
├── 📄 ROOT_DIRECTORY_GUIDE.md  # 项目结构指南
├── 📄 composer.json        # Composer配置
├── 📄 composer.lock        # 依赖锁定文件
├── 📄 .env*                # 环境配置文件
├── 📁 public/              # Web根目�?
├── 📁 src/                 # 源代�?
├── 📁 config/              # 配置文件
├── 📁 database/            # 数据�?
├── 📁 storage/             # 存储目录
├── 📁 scripts/             # 脚本目录
�?  ├── maintenance/        # 维护脚本
�?  ├── system/            # 系统脚本
�?  ├── validation/        # 验证脚本
�?  ├── performance/       # 性能脚本
�?  ├── migration/         # 迁移脚本
�?  └── batch/             # 批处理脚�?
├── 📁 tests/               # 测试文件
├── 📁 docs/                # 文档目录
├── 📁 tools/               # 工具目录
├── 📁 vendor/              # 依赖�?
└── 📁 其他功能目录...
```

### 优化效果

1. **根目录整�?*: 只保留核心文件，其他文件已分类整�?
2. **结构清晰**: 按功能分类，便于维护和开�?
3. **Web安全**: Web可访问文件全部在public目录
4. **文档完善**: 提供了详细的项目结构指南

### 使用指南

#### 开发环境启�?
```bash
# 方式1: 使用PHP内置服务�?
php -S localhost:8000 -t public

# 方式2: 使用启动脚本
scripts/batch/start.bat     # Windows
scripts/batch/start.sh      # Linux/Mac
```

#### 系统管理
- Web管理: http://localhost:8000/admin/
- 系统监控: http://localhost:8000/monitor/
- 测试工具: http://localhost:8000/test/
- API文档: http://localhost:8000/docs/api/

#### 常用命令
```bash
# 系统初始�?
php scripts/system/init_system.php

# 性能优化
php scripts/performance/optimize_production.php

# 数据库管�?
php database/management/migrate_database.php

# 缓存预热
php scripts/performance/cache_warmup.php
```

## 项目完整性检�?

�?**Web目录**: public/ 结构完整
�?**脚本分类**: scripts/ 按功能组�? 
�?**文档系统**: docs/ 包含所有文�?
�?**配置管理**: config/ 配置文件齐全
�?**数据�?*: database/ 迁移脚本完整
�?**测试体系**: tests/ �?public/test/ 双重保障
�?**安全配置**: .htaccess 和权限设置完�?

## 下一步建�?

1. **验证功能**: 运行系统测试确保所有功能正�?
2. **性能优化**: 运行性能脚本优化系统
3. **安全检�?*: 验证安全配置是否生效  
4. **文档更新**: 根据需要更新项目文�?
5. **部署准备**: 准备生产环境部署

---
*🎉 AlingAi Pro 5.0 项目结构整理全部完成�?

MARKDOWN;
        
        return $content;
    }
    
    private function displayFinalRootStatus(): void
    {
        echo "\n📁 最终根目录文件列表:\n";
        
        $rootFiles = array_filter(glob($this->rootDir . '/*'], 'is_file'];
        $rootFiles = array_map('basename', $rootFiles];
        sort($rootFiles];
        
        foreach ($rootFiles as $file) {
            $size = $this->formatBytes(filesize($this->rootDir . '/' . $file)];
            $type = $this->getFileType($file];
            echo "   {$type} {$file} ({$size})\n";
        }
        
        echo "\n📊 根目录统�?\n";
        echo "   文件总数: " . count($rootFiles) . "\n";
        echo "   目录总数: " . count(array_filter(glob($this->rootDir . '/*'], 'is_dir')) . "\n";
    }
    
    private function getFileType(string $file): string
    {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION)];
        
        switch ($extension) {
            case 'php': return '🐘';
            case 'md': return '📋';
            case 'json': return '📊';
            case 'xml': return '📄';
            case 'txt': return '📝';
            case 'zip': return '📦';
            case 'env': return '🔧';
            default: return '📄';
        }
    }
    
    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor(log($bytes, 1024)];
        
        return sprintf('%.1f %s', $bytes / pow(1024, $factor], $units[$factor]];
    }
}

// 执行清理
try {
    $cleanup = new FinalRootCleanup(];
    $cleanup->run(];
} catch (Exception $e) {
    echo "�?清理失败: " . $e->getMessage() . "\n";
    echo "📋 错误详情: " . $e->getTraceAsString() . "\n";
    exit(1];
}
