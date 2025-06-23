<?php
/**
 * AlingAi Pro 5.0 - 路径依赖分析工具
 * Path Dependency Analysis Tool
 * 
 * 这个脚本分析将public设置为运行目录后的潜在问题
 */

class PathDependencyAnalyzer {
    private $rootDir;
    private $publicDir;
    private $issues = [];
    private $warnings = [];
    private $suggestions = [];
      public function __construct() {
        $this->rootDir = __DIR__;
        $this->publicDir = $this->rootDir . '/public';
        
        echo "🔍 AlingAi Pro 5.0 - 路径依赖分析\n";
        echo str_repeat("=", 80) . "\n";
        echo "根目录: {$this->rootDir}\n";
        echo "Public目录: {$this->publicDir}\n\n";
    }
    
    public function analyze() {
        $this->checkAutoloadPaths();
        $this->checkConfigPaths();
        $this->checkIndexPhpPaths();
        $this->checkCoreDependencies();
        $this->checkEnvFilePaths();
        $this->checkVendorPaths();
        $this->checkSrcPaths();
        $this->generateReport();
    }
    
    private function checkAutoloadPaths() {
        echo "📦 检查Composer自动加载路径...\n";
        echo str_repeat("-", 50) . "\n";
        
        $vendorAutoload = $this->rootDir . '/vendor/autoload.php';
        if (!file_exists($vendorAutoload)) {
            $this->issues[] = "❌ Vendor autoload文件不存在: $vendorAutoload";
            echo "❌ Vendor autoload文件不存在\n";
        } else {
            echo "✅ Vendor autoload文件存在\n";
            
            // 检查自动加载映射
            $classmap = $this->rootDir . '/vendor/composer/autoload_classmap.php';
            if (file_exists($classmap)) {
                $classes = include $classmap;
                $alingaiClasses = array_filter($classes, function($path, $class) {
                    return strpos($class, 'AlingAi\\') === 0;
                }, ARRAY_FILTER_USE_BOTH);
                
                echo "✅ 发现 " . count($alingaiClasses) . " 个AlingAi类\n";
                
                // 检查核心类是否正确映射
                $coreClass = 'AlingAi\\Core\\AlingAiProApplication';
                if (isset($alingaiClasses[$coreClass])) {
                    $classPath = $alingaiClasses[$coreClass];
                    if (file_exists($classPath)) {
                        echo "✅ 核心应用类路径正确: $classPath\n";
                    } else {
                        $this->issues[] = "❌ 核心应用类文件不存在: $classPath";
                        echo "❌ 核心应用类文件不存在: $classPath\n";
                    }
                } else {
                    $this->issues[] = "❌ 核心应用类未在自动加载中注册";
                    echo "❌ 核心应用类未在自动加载中注册\n";
                }
            }
        }
        echo "\n";
    }
    
    private function checkConfigPaths() {
        echo "⚙️ 检查配置文件路径...\n";
        echo str_repeat("-", 50) . "\n";
        
        $configDir = $this->rootDir . '/config';
        if (!is_dir($configDir)) {
            $this->issues[] = "❌ 配置目录不存在: $configDir";
            echo "❌ 配置目录不存在\n";
        } else {
            echo "✅ 配置目录存在\n";
            
            $configFiles = glob($configDir . '/*.php');
            echo "✅ 发现 " . count($configFiles) . " 个配置文件\n";
        }
        echo "\n";
    }
    
    private function checkIndexPhpPaths() {
        echo "🌐 检查index.php路径依赖...\n";
        echo str_repeat("-", 50) . "\n";
        
        $indexPath = $this->publicDir . '/index.php';
        if (!file_exists($indexPath)) {
            $this->issues[] = "❌ index.php文件不存在: $indexPath";
            echo "❌ index.php文件不存在\n";
            return;
        }
        
        echo "✅ index.php文件存在\n";
        
        // 分析index.php的路径依赖
        $content = file_get_contents($indexPath);
        
        // 检查APP_ROOT定义
        if (strpos($content, "define('APP_ROOT', dirname(__DIR__))") !== false) {
            echo "✅ APP_ROOT正确定义为父目录\n";
        } else {
            $this->warnings[] = "⚠️ APP_ROOT定义可能有问题";
            echo "⚠️ APP_ROOT定义可能有问题\n";
        }
        
        // 检查vendor autoload路径
        if (strpos($content, "APP_ROOT . '/vendor/autoload.php'") !== false) {
            echo "✅ Vendor autoload路径正确\n";
        } else {
            $this->warnings[] = "⚠️ Vendor autoload路径可能有问题";
            echo "⚠️ Vendor autoload路径可能有问题\n";
        }
        
        // 检查.env文件路径
        if (strpos($content, "APP_ROOT . '/.env'") !== false) {
            echo "✅ .env文件路径正确\n";
        } else {
            $this->warnings[] = "⚠️ .env文件路径可能有问题";
            echo "⚠️ .env文件路径可能有问题\n";
        }
        
        echo "\n";
    }
    
    private function checkCoreDependencies() {
        echo "🏗️ 检查核心依赖...\n";
        echo str_repeat("-", 50) . "\n";
        
        $coreFiles = [
            'src/Core/AlingAiProApplication.php' => '核心应用类',
            'src/Services/DatabaseService.php' => '数据库服务',
            'src/Services/CacheService.php' => '缓存服务',
            'src/Services/AuthService.php' => '认证服务'
        ];
        
        foreach ($coreFiles as $file => $description) {
            $fullPath = $this->rootDir . '/' . $file;
            if (file_exists($fullPath)) {
                echo "✅ $description: $file\n";
            } else {
                $this->warnings[] = "⚠️ $description 文件不存在: $file";
                echo "⚠️ $description 文件不存在: $file\n";
            }
        }
        echo "\n";
    }
    
    private function checkEnvFilePaths() {
        echo "🔐 检查环境文件...\n";
        echo str_repeat("-", 50) . "\n";
        
        $envFiles = ['.env', '.env.example', '.env.production'];
        foreach ($envFiles as $envFile) {
            $fullPath = $this->rootDir . '/' . $envFile;
            if (file_exists($fullPath)) {
                echo "✅ 环境文件存在: $envFile\n";
            } else {
                echo "⚠️ 环境文件不存在: $envFile\n";
            }
        }
        echo "\n";
    }
    
    private function checkVendorPaths() {
        echo "📚 检查Vendor目录...\n";
        echo str_repeat("-", 50) . "\n";
        
        $vendorDir = $this->rootDir . '/vendor';
        if (!is_dir($vendorDir)) {
            $this->issues[] = "❌ Vendor目录不存在，需要运行 composer install";
            echo "❌ Vendor目录不存在，需要运行 composer install\n";
        } else {
            echo "✅ Vendor目录存在\n";
            
            // 检查关键包
            $packages = [
                'slim/slim' => 'Slim框架',
                'php-di/php-di' => 'DI容器',
                'monolog/monolog' => '日志系统',
                'vlucas/phpdotenv' => '环境变量'
            ];
            
            foreach ($packages as $package => $description) {
                $packageDir = $vendorDir . '/' . $package;
                if (is_dir($packageDir)) {
                    echo "✅ $description: $package\n";
                } else {
                    $this->warnings[] = "⚠️ $description 包不存在: $package";
                    echo "⚠️ $description 包不存在: $package\n";
                }
            }
        }
        echo "\n";
    }
    
    private function checkSrcPaths() {
        echo "💻 检查源码目录结构...\n";
        echo str_repeat("-", 50) . "\n";
        
        $srcDir = $this->rootDir . '/src';
        if (!is_dir($srcDir)) {
            $this->issues[] = "❌ src目录不存在";
            echo "❌ src目录不存在\n";
        } else {
            echo "✅ src目录存在\n";
            
            $coreDirectories = [
                'Core' => '核心框架',
                'Controllers' => '控制器',
                'Services' => '服务层',
                'AI' => 'AI服务'
            ];
            
            foreach ($coreDirectories as $dir => $description) {
                $fullPath = $srcDir . '/' . $dir;
                if (is_dir($fullPath)) {
                    echo "✅ $description 目录: src/$dir\n";
                } else {
                    $this->warnings[] = "⚠️ $description 目录不存在: src/$dir";
                    echo "⚠️ $description 目录不存在: src/$dir\n";
                }
            }
        }
        echo "\n";
    }
    
    private function generateReport() {
        echo "📋 分析报告\n";
        echo str_repeat("=", 80) . "\n";
        
        $totalIssues = count($this->issues);
        $totalWarnings = count($this->warnings);
        
        echo "🔍 分析结果汇总:\n";
        echo "   • 严重问题: $totalIssues 个\n";
        echo "   • 警告问题: $totalWarnings 个\n\n";
        
        if (!empty($this->issues)) {
            echo "❌ 严重问题 (阻止运行):\n";
            foreach ($this->issues as $issue) {
                echo "   $issue\n";
            }
            echo "\n";
        }
        
        if (!empty($this->warnings)) {
            echo "⚠️ 警告问题 (可能影响功能):\n";
            foreach ($this->warnings as $warning) {
                echo "   $warning\n";
            }
            echo "\n";
        }
        
        // 生成建议
        $this->generateSuggestions();
        
        if (!empty($this->suggestions)) {
            echo "💡 解决建议:\n";
            foreach ($this->suggestions as $suggestion) {
                echo "   $suggestion\n";
            }
            echo "\n";
        }
        
        // 总结
        if ($totalIssues == 0) {
            echo "🎉 恭喜！没有发现严重问题，public目录可以作为web根目录运行。\n";
            echo "🚀 建议执行命令: php -S localhost:8000 -t public/ router.php\n";
        } else {
            echo "🔧 需要解决严重问题后才能正常运行。\n";
        }
        
        echo "\n" . str_repeat("=", 80) . "\n";
    }
    
    private function generateSuggestions() {
        if (!empty($this->issues)) {
            foreach ($this->issues as $issue) {
                if (strpos($issue, 'Vendor') !== false) {
                    $this->suggestions[] = "🔧 运行 'composer install' 安装依赖包";
                }
                if (strpos($issue, '核心应用类') !== false) {
                    $this->suggestions[] = "🔧 检查 src/Core/AlingAiProApplication.php 文件是否存在";
                    $this->suggestions[] = "🔧 运行 'composer dump-autoload' 重新生成自动加载文件";
                }
            }
        }
        
        if (!empty($this->warnings)) {
            $this->suggestions[] = "📝 建议创建缺失的源码文件和目录";
            $this->suggestions[] = "⚙️ 检查composer.json配置是否正确";
            $this->suggestions[] = "🔄 确保所有路径使用相对于APP_ROOT的正确引用";
        }
        
        // 通用建议
        $this->suggestions[] = "🧪 使用 php -t public/ -S localhost:8000 测试服务器运行";
        $this->suggestions[] = "📋 检查PHP错误日志定位具体问题";
        $this->suggestions[] = "🔒 确保.env文件在根目录且配置正确";
    }
}

// 执行分析
$analyzer = new PathDependencyAnalyzer();
$analyzer->analyze();
?>
