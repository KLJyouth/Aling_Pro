<?php
/**
 * 模拟生产环境函数禁用测试
 * 用于测试系统在受限环境中的兼容性
 */

// 模拟禁用exec函数
if (!function_exists('exec_disabled_test')) {
    function exec_disabled_test() {
        throw new Error("Call to undefined function exec()");
    }
}

// 重新定义exec函数以模拟被禁用
function exec() {
    throw new Error("Call to undefined function exec()");
}

echo "🧪 模拟生产环境函数禁用测试\n";
echo "========================================\n";

// 测试安装程序的兼容性
echo "📋 测试install.php的兼容性...\n";

try {
    // 模拟调用安装程序的关键部分
    require_once 'install/install.php';
    echo "✅ install.php加载成功，兼容性良好\n";
} catch (Exception $e) {
    echo "❌ install.php兼容性测试失败: " . $e->getMessage() . "\n";
} catch (Error $e) {
    if (strpos($e->getMessage(), 'exec') !== false) {
        echo "❌ 仍存在未修复的exec()调用: " . $e->getMessage() . "\n";
    } else {
        echo "❌ 其他错误: " . $e->getMessage() . "\n";
    }
}

echo "\n🔍 检查关键文件的函数调用保护...\n";

$files = [
    'three_complete_compilation_validator.php',
    'scripts/system_monitor.php',
    'bin/health-check.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // 检查是否有受保护的函数调用
        $protected = true;
        $issues = [];
        
        // 检查exec调用
        if (preg_match('/\bexec\s*\(/', $content)) {
            if (!preg_match('/function_exists\s*\(\s*[\'"]exec[\'"]\s*\)/', $content)) {
                $protected = false;
                $issues[] = 'exec()未受保护';
            }
        }
        
        // 检查shell_exec调用
        if (preg_match('/\bshell_exec\s*\(/', $content)) {
            if (!preg_match('/function_exists\s*\(\s*[\'"]shell_exec[\'"]\s*\)/', $content)) {
                $protected = false;
                $issues[] = 'shell_exec()未受保护';
            }
        }
        
        // 检查putenv调用
        if (preg_match('/\bputenv\s*\(/', $content)) {
            if (!preg_match('/function_exists\s*\(\s*[\'"]putenv[\'"]\s*\)/', $content)) {
                $protected = false;
                $issues[] = 'putenv()未受保护';
            }
        }
        
        if ($protected) {
            echo "✅ $file - 所有函数调用已受保护\n";
        } else {
            echo "❌ $file - 发现问题: " . implode(', ', $issues) . "\n";
        }
    } else {
        echo "⚠️ $file - 文件不存在\n";
    }
}

echo "\n🎯 测试总结:\n";
echo "========================================\n";
echo "模拟生产环境测试完成。\n";
echo "请确保所有显示❌的问题都已修复。\n";
?>
