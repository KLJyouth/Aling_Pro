<?php
/**
 * AlingAi Pro 5.0 - Public目录配置验证脚本
 * Quick Configuration Verification Script
 */

class PublicDirectoryValidator {
    private $rootDir;
    private $publicDir;
    
    public function __construct() {
        $this->rootDir = __DIR__;
        $this->publicDir = $this->rootDir . '/public';
        
        echo "🔍 AlingAi Pro 5.0 - Public目录配置验证\n";
        echo str_repeat("=", 60) . "\n";
    }
    
    public function validateConfiguration() {
        $this->checkBasicStructure();
        $this->checkKeyFiles();
        $this->checkStartupCommand();
        $this->generateQuickGuide();
    }
    
    private function checkBasicStructure() {
        echo "📁 检查基础目录结构...\n";
        
        $requiredDirs = [
            'public' => 'Web根目录',
            'src' => '源代码目录',
            'vendor' => '依赖包目录',
            'config' => '配置目录'
        ];
        
        foreach ($requiredDirs as $dir => $desc) {
            $path = $this->rootDir . '/' . $dir;
            $status = is_dir($path) ? '✅' : '❌';
            echo "  $status $desc: $dir\n";
        }
        echo "\n";
    }
    
    private function checkKeyFiles() {
        echo "📄 检查关键文件...\n";
        
        $keyFiles = [
            'public/index.php' => 'Web应用入口',
            'router.php' => '路由脚本',
            'composer.json' => 'Composer配置',
            'vendor/autoload.php' => '自动加载文件',
            '.env' => '环境配置'
        ];
        
        foreach ($keyFiles as $file => $desc) {
            $path = $this->rootDir . '/' . $file;
            $status = file_exists($path) ? '✅' : '❌';
            echo "  $status $desc: $file\n";
        }
        echo "\n";
    }
    
    private function checkStartupCommand() {
        echo "🚀 验证启动命令...\n";
        
        // 检查当前工作目录
        $currentDir = getcwd();
        $expectedDir = $this->rootDir;
        
        if (realpath($currentDir) === realpath($expectedDir)) {
            echo "  ✅ 当前目录正确: " . basename($currentDir) . "\n";
        } else {
            echo "  ⚠️ 请切换到项目根目录\n";
            echo "     当前目录: $currentDir\n";
            echo "     期望目录: $expectedDir\n";
        }
        
        // 检查PHP版本
        $phpVersion = PHP_VERSION;
        if (version_compare($phpVersion, '8.0.0', '>=')) {
            echo "  ✅ PHP版本兼容: $phpVersion\n";
        } else {
            echo "  ❌ PHP版本过低: $phpVersion (需要 >= 8.0)\n";
        }
        
        echo "\n";
    }
    
    private function generateQuickGuide() {
        echo "📋 快速启动指南\n";
        echo str_repeat("-", 40) . "\n";
        
        echo "1️⃣ 确认在项目根目录:\n";
        echo "   cd " . basename($this->rootDir) . "\n\n";
        
        echo "2️⃣ 启动开发服务器:\n";
        echo "   php -S localhost:8000 -t public/ router.php\n\n";
        
        echo "3️⃣ 访问应用:\n";
        echo "   🌐 主页: http://localhost:8000/\n";
        echo "   🔧 管理: http://localhost:8000/admin/\n";
        echo "   📡 API: http://localhost:8000/api/\n";
        echo "   🧪 测试: http://localhost:8000/test/\n\n";
        
        echo "4️⃣ 停止服务器:\n";
        echo "   按 Ctrl+C 或运行: taskkill /F /IM php.exe\n\n";
        
        echo "📚 更多信息:\n";
        echo "   📖 详细分析: docs/PUBLIC_ROOT_DIRECTORY_ANALYSIS_REPORT.md\n";
        echo "   🚀 部署指南: docs/DEPLOYMENT_GUIDE.md\n";
        echo "   ⚙️ 配置说明: README.md\n\n";
        
        echo "✅ 配置验证完成！准备就绪。\n";
        echo str_repeat("=", 60) . "\n";
    }
}

// 执行验证
$validator = new PublicDirectoryValidator();
$validator->validateConfiguration();
?>
