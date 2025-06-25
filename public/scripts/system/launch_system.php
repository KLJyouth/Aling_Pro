<?php
/**
 * AlingAi Pro Enterprise System - 系统启动脚本
 * 三完编译企业版一键启动和验证工具
 * 
 * @version 3.0.0
 * @date 2025-06-07
 */

echo "🚀 ===== AlingAi Pro Enterprise System Launcher ===== 🚀\n";
echo "           三完编译企业版系统启动器\n";
echo "====================================================\n";
echo "启动时间: " . date('Y-m-d H:i:s') . "\n";
echo "系统版本: v3.0.0 - 三完编译企业版\n";
echo "====================================================\n\n";

// 函数：检查系统状�?
function checkSystemStatus() {
    echo "🔍 系统状态检查中...\n";
    
    // 检查关键文�?
    $criticalFiles = [
        'public/index.php' => '主入口文�?,
        'src/Core/Application.php' => '核心应用',
        'config/routes.php' => '路由配置',
        '.env' => '环境配置',
        'composer.json' => 'Composer配置'
    ];
    
    $allExists = true;
    foreach ($criticalFiles as $file => $desc) {
        if (file_exists($file)) {
            echo "�?$desc: 存在\n";
        } else {
            echo "�?$desc: 缺失\n";
            $allExists = false;
        }
    }
    
    return $allExists;
}

// 函数：检查数据库连接
function checkDatabase() {
    echo "\n🗄�?数据库连接检查中...\n";
    
    try {
        if (!file_exists('.env')) {
            throw new Exception('环境配置文件不存�?];
        }
          // 解析.env文件
        $envContent = file_get_contents('.env'];
        preg_match('/DB_HOST=(.*)/', $envContent, $hostMatch];
        preg_match('/DB_DATABASE=(.*)/', $envContent, $nameMatch];
        preg_match('/DB_USERNAME=(.*)/', $envContent, $userMatch];
        preg_match('/DB_PASSWORD=(.*)/', $envContent, $passMatch];
        
        $host = trim($hostMatch[1] ?? 'localhost'];
        $dbname = trim($nameMatch[1] ?? 'alingai'];
        $username = trim($userMatch[1] ?? 'root'];
        $password = trim($passMatch[1] ?? ''];
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password];
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION];
        
        echo "�?数据库连�? 成功\n";
        echo "�?数据库名�? $dbname\n";
        
        // 检查核心表
        $tables = ['users', 'chat_sessions', 'chat_messages', 'api_keys', 'system_settings', 'user_settings', 'logs', 'user_preferences'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'"];
            if ($stmt->rowCount() > 0) {
                echo "�?数据�?$table: 存在\n";
            } else {
                echo "�?数据�?$table: 缺失\n";
                return false;
            }
        }
        
        return true;
    } catch (Exception $e) {
        echo "�?数据库连接失�? " . $e->getMessage() . "\n";
        return false;
    }
}

// 函数：检查PHP环境
function checkPHPEnvironment() {
    echo "\n🐘 PHP环境检查中...\n";
    
    $phpVersion = PHP_VERSION;
    echo "�?PHP版本: $phpVersion\n";
    
    if (version_compare($phpVersion, '8.0.0', '<')) {
        echo "�?PHP版本过低，需�?.0+\n";
        return false;
    }
    
    $requiredExtensions = ['pdo', 'pdo_mysql', 'curl', 'json', 'mbstring', 'openssl'];
    foreach ($requiredExtensions as $ext) {
        if (extension_loaded($ext)) {
            echo "�?扩展 $ext: 已加载\n";
        } else {
            echo "�?扩展 $ext: 缺失\n";
            return false;
        }
    }
    
    return true;
}

// 函数：检查权�?
function checkPermissions() {
    echo "\n🔐 文件权限检查中...\n";
    
    $writableDirs = ['storage/logs', 'storage/cache', 'storage/uploads', 'public/assets'];
    $allWritable = true;
    
    foreach ($writableDirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true];
        }
        
        if (is_writable($dir)) {
            echo "�?目录 $dir: 可写\n";
        } else {
            echo "�?目录 $dir: 不可写\n";
            $allWritable = false;
        }
    }
    
    return $allWritable;
}

// 函数：启动服务器
function startServer($port = 8000) {
    echo "\n🚀 启动开发服务器...\n";
    echo "端口: $port\n";
    echo "文档根目�? public/\n";
    echo "====================================================\n";
    echo "🌐 访问地址:\n";
    echo "   主页: http://localhost:$port/\n";
    echo "   管理: http://localhost:$port/admin\n";
    echo "   API: http://localhost:$port/api/docs\n";
    echo "====================================================\n";
    echo "💡 提示: �?Ctrl+C 停止服务器\n\n";
    
    // 启动PHP内置服务�?
    $command = "php -S localhost:$port -t public/";
    passthru($command];
}

// 主要执行流程
function main() {
    $checks = [
        '系统文件' => 'checkSystemStatus',
        'PHP环境' => 'checkPHPEnvironment', 
        '文件权限' => 'checkPermissions',
        '数据�? => 'checkDatabase'
    ];
    
    $allPassed = true;
    
    foreach ($checks as $name => $function) {
        if (!$function()) {
            echo "\n�?$name 检查失败！\n";
            $allPassed = false;
        }
    }
    
    echo "\n" . str_repeat("=", 52) . "\n";
    
    if ($allPassed) {
        echo "🎉 所有检查通过！系统已准备就绪！\n";
        echo str_repeat("=", 52) . "\n";
        
        // 询问是否启动服务�?
        echo "\n�?是否启动开发服务器�?y/n): ";
        $handle = fopen("php://stdin", "r"];
        $response = trim(fgets($handle)];
        fclose($handle];
        
        if (strtolower($response) === 'y' || strtolower($response) === 'yes') {
            startServer(];
        } else {
            echo "\n📝 手动启动命令:\n";
            echo "   php -S localhost:8000 -t public/ router.php\n\n";
            echo "📖 更多信息请查�? SYSTEM_READY_GUIDE.md\n";
        }
    } else {
        echo "�?系统检查未完全通过，请检查上述问题后重试。\n";
        echo "📋 故障排除指南: SYSTEM_READY_GUIDE.md\n";
    }
    
    echo str_repeat("=", 52) . "\n";
    echo "🏁 系统启动器执行完成\n";
    echo "📅 " . date('Y-m-d H:i:s') . "\n";
    echo str_repeat("=", 52) . "\n";
}

// 执行主程�?
main(];

?>
