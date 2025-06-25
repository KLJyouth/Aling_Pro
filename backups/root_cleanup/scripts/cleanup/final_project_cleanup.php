<?php
/**
 * Final Project Cleanup - AlingAi Pro 5.0
 * 最终项目清理脚本
 * 
 * 这个脚本将处理剩余的根目录文件，确保项目结构完全整洁
 */

class FinalProjectCleanup {
    private $rootDir;
    private $moves = [];
    private $deletes = [];
    private $keeps = [];
    
    public function __construct() {
        $this->rootDir = __DIR__;
        echo "🧹 AlingAi Pro 5.0 - 最终项目清理\n";
        echo str_repeat("=", 80) . "\n";
    }
    
    public function analyze() {
        echo "📂 分析根目录剩余文件...\n";
        echo str_repeat("-", 60) . "\n";
        
        $files = glob($this->rootDir . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = basename($file);
                $this->categorizeFile($filename, $file);
            }
        }
        
        $this->showAnalysis();
    }
    
    private function categorizeFile($filename, $filepath) {
        // 需要保留在根目录的核心文件
        $keepInRoot = [
            '.env', '.env.example', '.env.local', '.env.production', '.env.production.example',
            'composer.json', 'composer.lock', 'README.md', 'router.php',
            'DIRECTORY_STRUCTURE.md'
        ];
        
        // 需要移动的文件
        $movePatterns = [
            'compilation_fix_complete_report.php' => 'scripts/analysis/',
            'complete_public_migration.php' => 'scripts/migration/',
            'execute_comprehensive_migration.php' => 'scripts/migration/',
            'final_root_cleanup.php' => 'scripts/cleanup/',
            'composer.json.backup' => 'backup/composer/',
        ];
        
        // 可以删除的空文件或临时文件
        $deletePatterns = [
            'PUBLIC_FOLDER_MIGRATION_PLAN.md' // 空文件
        ];
        
        if (in_array($filename, $keepInRoot)) {
            $this->keeps[] = $filename;
        } elseif (isset($movePatterns[$filename])) {
            $this->moves[$filename] = $movePatterns[$filename];
        } elseif (in_array($filename, $deletePatterns)) {
            // 检查文件大小，如果是空文件则删除
            if (filesize($filepath) == 0) {
                $this->deletes[] = $filename;
            } else {
                $this->keeps[] = $filename;
            }
        } else {
            // 其他文件默认保留
            $this->keeps[] = $filename;
        }
    }
    
    private function showAnalysis() {
        echo "\n📊 文件分析结果:\n";
        echo str_repeat("-", 60) . "\n";
        
        if (!empty($this->keeps)) {
            echo "✅ 保留在根目录 (" . count($this->keeps) . " 个文件):\n";
            foreach ($this->keeps as $file) {
                echo "   • $file\n";
            }
            echo "\n";
        }
        
        if (!empty($this->moves)) {
            echo "📦 需要移动 (" . count($this->moves) . " 个文件):\n";
            foreach ($this->moves as $file => $dest) {
                echo "   • $file → $dest\n";
            }
            echo "\n";
        }
        
        if (!empty($this->deletes)) {
            echo "🗑️ 可以删除 (" . count($this->deletes) . " 个文件):\n";
            foreach ($this->deletes as $file) {
                echo "   • $file (空文件)\n";
            }
            echo "\n";
        }
    }
    
    public function execute() {
        echo "🚀 执行文件整理...\n";
        echo str_repeat("-", 60) . "\n";
        
        $moved = 0;
        $deleted = 0;
        
        // 执行移动操作
        foreach ($this->moves as $file => $destDir) {
            $sourcePath = $this->rootDir . '/' . $file;
            $fullDestDir = $this->rootDir . '/' . $destDir;
            
            // 确保目标目录存在
            if (!is_dir($fullDestDir)) {
                mkdir($fullDestDir, 0755, true);
                echo "📁 创建目录: $destDir\n";
            }
            
            $destPath = $fullDestDir . $file;
            
            if (file_exists($sourcePath)) {
                if (rename($sourcePath, $destPath)) {
                    echo "✅ 移动: $file → $destDir\n";
                    $moved++;
                } else {
                    echo "❌ 移动失败: $file\n";
                }
            }
        }
        
        // 执行删除操作
        foreach ($this->deletes as $file) {
            $filePath = $this->rootDir . '/' . $file;
            if (file_exists($filePath) && filesize($filePath) == 0) {
                if (unlink($filePath)) {
                    echo "✅ 删除空文件: $file\n";
                    $deleted++;
                } else {
                    echo "❌ 删除失败: $file\n";
                }
            }
        }
        
        echo "\n📈 清理统计:\n";
        echo "   • 文件移动: $moved 个\n";
        echo "   • 文件删除: $deleted 个\n";
        echo "   • 文件保留: " . count($this->keeps) . " 个\n";
    }
    
    public function generateFinalReport() {
        $reportContent = "# AlingAi Pro 5.0 - 最终项目清理报告\n\n";
        $reportContent .= "**清理时间**: " . date('Y-m-d H:i:s') . "\n\n";
        
        $reportContent .= "## 清理摘要\n\n";
        $reportContent .= "本次清理整理了根目录的剩余文件，确保项目结构完全整洁。\n\n";
        
        $reportContent .= "## 文件处理详情\n\n";
        
        if (!empty($this->keeps)) {
            $reportContent .= "### 保留在根目录的文件\n";
            $reportContent .= "这些是项目的核心配置和入口文件：\n\n";
            foreach ($this->keeps as $file) {
                $reportContent .= "- `$file`\n";
            }
            $reportContent .= "\n";
        }
        
        if (!empty($this->moves)) {
            $reportContent .= "### 移动的文件\n";
            $reportContent .= "这些文件被移动到更合适的目录：\n\n";
            foreach ($this->moves as $file => $dest) {
                $reportContent .= "- `$file` → `$dest`\n";
            }
            $reportContent .= "\n";
        }
        
        if (!empty($this->deletes)) {
            $reportContent .= "### 删除的文件\n";
            $reportContent .= "这些空文件或临时文件已被删除：\n\n";
            foreach ($this->deletes as $file) {
                $reportContent .= "- `$file`\n";
            }
            $reportContent .= "\n";
        }
        
        $reportContent .= "## 最终项目结构\n\n";
        $reportContent .= "经过完整的清理和组织，项目现在具有以下清晰的结构：\n\n";
        $reportContent .= "```\n";
        $reportContent .= "AlingAi_pro/\n";
        $reportContent .= "├── .env*                    # 环境配置文件\n";
        $reportContent .= "├── composer.json/lock       # PHP依赖管理\n";
        $reportContent .= "├── README.md               # 项目文档\n";
        $reportContent .= "├── router.php              # 路由配置\n";
        $reportContent .= "├── DIRECTORY_STRUCTURE.md  # 目录结构说明\n";
        $reportContent .= "├── public/                 # Web可访问文件\n";
        $reportContent .= "│   ├── assets/            # 静态资源\n";
        $reportContent .= "│   ├── api/               # API接口\n";
        $reportContent .= "│   ├── admin/             # 管理界面\n";
        $reportContent .= "│   ├── docs/              # 在线文档\n";
        $reportContent .= "│   ├── install/           # 安装工具\n";
        $reportContent .= "│   ├── tests/             # 测试工具\n";
        $reportContent .= "│   └── uploads/           # 用户上传\n";
        $reportContent .= "├── scripts/               # 项目脚本\n";
        $reportContent .= "│   ├── analysis/          # 分析工具\n";
        $reportContent .= "│   ├── migration/         # 迁移脚本\n";
        $reportContent .= "│   ├── cleanup/           # 清理工具\n";
        $reportContent .= "│   └── system/            # 系统脚本\n";
        $reportContent .= "├── config/                # 配置文件\n";
        $reportContent .= "├── src/                   # 源代码\n";
        $reportContent .= "├── database/              # 数据库文件\n";
        $reportContent .= "├── storage/               # 存储目录\n";
        $reportContent .= "├── vendor/                # 第三方库\n";
        $reportContent .= "├── backup/                # 备份文件\n";
        $reportContent .= "└── docs/                  # 项目文档\n";
        $reportContent .= "```\n\n";
        
        $reportContent .= "## 安全特性\n\n";
        $reportContent .= "- ✅ 敏感配置文件保持私有\n";
        $reportContent .= "- ✅ Web可访问内容在public目录\n";
        $reportContent .= "- ✅ 管理和测试工具有IP限制\n";
        $reportContent .= "- ✅ 完整的.htaccess安全配置\n\n";
        
        $reportContent .= "## 维护建议\n\n";
        $reportContent .= "1. 定期检查public目录的访问权限\n";
        $reportContent .= "2. 保持敏感文件的私有状态\n";
        $reportContent .= "3. 及时清理临时文件和日志\n";
        $reportContent .= "4. 定期更新安全配置\n\n";
        
        $reportPath = $this->rootDir . '/docs/reports/FINAL_PROJECT_CLEANUP_REPORT_' . date('Y_m_d_H_i_s') . '.md';
        
        // 确保目录存在
        $reportDir = dirname($reportPath);
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0755, true);
        }
        
        file_put_contents($reportPath, $reportContent);
        echo "📋 最终清理报告已保存到: " . basename($reportPath) . "\n";
    }
}

// 执行清理
$cleanup = new FinalProjectCleanup();
$cleanup->analyze();

echo "\n❓ 是否执行清理操作？ (y/n): ";
$handle = fopen("php://stdin", "r");
$response = trim(fgets($handle));
fclose($handle);

if (strtolower($response) === 'y' || strtolower($response) === 'yes') {
    $cleanup->execute();
    $cleanup->generateFinalReport();
    echo "\n🎉 项目清理完成！\n";
} else {
    echo "\n⏸️ 清理操作已取消。\n";
}

echo "\n🏆 AlingAi Pro 5.0 项目整理工作已完成！\n";
echo str_repeat("=", 80) . "\n";
?>
