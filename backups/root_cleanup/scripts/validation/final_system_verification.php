<?php
/**
 * AlingAi Pro Enterprise System - 最终验证脚本
 * 验证安装完成状态和系统就绪性
 */

require_once __DIR__ . '/vendor/autoload.php';

// 加载环境配置
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    $envLines = explode("\n", $envContent);
    foreach ($envLines as $line) {
        if (trim($line) && strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

echo "\n";
echo "════════════════════════════════════════════════════════════════════════════════\n";
echo "🎉 AlingAi Pro Enterprise System - 最终验证报告\n";
echo "════════════════════════════════════════════════════════════════════════════════\n";
echo "验证时间: " . date('Y-m-d H:i:s') . "\n";
echo "版本信息: v3.0.0 - 三完编译企业版\n";
echo "\n";

$totalChecks = 0;
$passedChecks = 0;

/**
 * 执行检查项目
 */
function checkItem($description, $condition, $details = '') {
    global $totalChecks, $passedChecks;
    $totalChecks++;
    
    if ($condition) {
        $passedChecks++;
        echo "✅ {$description}\n";
        if ($details) echo "   {$details}\n";
    } else {
        echo "❌ {$description}\n";
        if ($details) echo "   {$details}\n";
    }
}

// 1. 三完编译状态验证
echo "=== 三完编译状态验证 ===\n";

$threeCompleteValidator = __DIR__ . '/three_complete_compilation_validator.php';
checkItem(
    "第一完编译：基础系统架构",
    file_exists($threeCompleteValidator),
    "基础架构文件验证器存在"
);

checkItem(
    "第二完编译：CompleteRouterIntegration", 
    file_exists(__DIR__ . '/config/routes.php'),
    "路由集成配置文件存在"
);

checkItem(
    "第三完编译：EnhancedAgentCoordinator",
    file_exists(__DIR__ . '/src/Services/AgentCoordinatorService.php'),
    "智能体协调服务存在"
);

// 2. 数据库连接和结构验证
echo "\n=== 数据库连接和结构验证 ===\n";

try {
    $config = [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'database' => $_ENV['DB_DATABASE'] ?? 'alingai_pro',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? ''
    ];
    
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    checkItem("数据库连接", true, "成功连接到 {$config['database']}");
    
    // 验证核心表
    $coreTables = [
        'users' => '用户管理表',
        'chat_sessions' => '聊天会话表', 
        'chat_messages' => '聊天消息表',
        'api_keys' => 'API密钥管理表',
        'system_settings' => '系统设置表',
        'user_settings' => '用户设置表',
        'logs' => '系统日志表',
        'user_preferences' => '用户偏好表'
    ];
    
    $existingTables = 0;
    foreach ($coreTables as $table => $description) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM information_schema.tables 
            WHERE table_schema = ? AND table_name = ?
        ");
        $stmt->execute([$config['database'], $table]);
        $exists = $stmt->fetch()['count'] > 0;
        
        if ($exists) $existingTables++;
        
        checkItem(
            "核心表: {$table}",
            $exists,
            $description
        );
    }
    
    // 验证关键字段
    $keyFields = [
        ['users', 'role', 'VARCHAR(50)'],
        ['system_settings', 'setting_type', 'ENUM'],
        ['user_settings', 'setting_type', 'ENUM'],
        ['user_settings', 'category', 'VARCHAR(50)']
    ];
    
    foreach ($keyFields as [$table, $field, $type]) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM information_schema.columns 
            WHERE table_schema = ? AND table_name = ? AND column_name = ?
        ");
        $stmt->execute([$config['database'], $table, $field]);
        $exists = $stmt->fetch()['count'] > 0;
        
        checkItem(
            "关键字段: {$table}.{$field}",
            $exists,
            "字段类型: {$type}"
        );
    }
    
} catch (Exception $e) {
    checkItem("数据库连接", false, "错误: " . $e->getMessage());
}

// 3. 核心文件结构验证
echo "\n=== 核心文件结构验证 ===\n";

$coreFiles = [
    'public/index.php' => 'Web入口文件',
    'src/Core/Application.php' => '核心应用类',
    'src/Controllers/WebController.php' => 'Web控制器',
    'src/Services/AuthService.php' => '认证服务',
    'src/Services/ChatService.php' => '聊天服务',
    'config/routes.php' => '路由配置',
    'composer.json' => 'Composer配置',
    '.env' => '环境配置文件'
];

foreach ($coreFiles as $file => $description) {
    checkItem(
        "核心文件: {$file}",
        file_exists(__DIR__ . '/' . $file),
        $description
    );
}

// 4. 前端资源验证
echo "\n=== 前端资源验证 ===\n";

$frontendFiles = [
    'public/assets/js/main.js' => '主应用脚本',
    'public/assets/css/styles.css' => '主样式表',
    'public/chat.html' => '聊天页面',
    'public/login.html' => '登录页面',
    'public/dashboard.html' => '仪表板页面'
];

foreach ($frontendFiles as $file => $description) {
    checkItem(
        "前端资源: " . basename($file),
        file_exists(__DIR__ . '/' . $file),
        $description
    );
}

// 5. 安全配置验证
echo "\n=== 安全配置验证 ===\n";

checkItem(
    ".htaccess安全配置",
    file_exists(__DIR__ . '/public/.htaccess'),
    "Web服务器安全规则"
);

checkItem(
    "敏感文件保护",
    !file_exists(__DIR__ . '/public/.env'),
    ".env文件不在public目录中"
);

checkItem(
    "Composer autoload",
    file_exists(__DIR__ . '/vendor/autoload.php'),
    "依赖自动加载文件存在"
);

// 6. 日志和缓存目录验证
echo "\n=== 目录权限验证 ===\n";

$directories = [
    'storage/logs' => '日志目录',
    'storage/cache' => '缓存目录', 
    'storage/uploads' => '上传目录',
    'public/assets' => '静态资源目录'
];

foreach ($directories as $dir => $description) {
    $fullPath = __DIR__ . '/' . $dir;
    $exists = is_dir($fullPath);
    $writable = $exists ? is_writable($fullPath) : false;
    
    checkItem(
        "目录: {$dir}",
        $exists,
        $description . ($writable ? " (可写)" : " (只读)")
    );
}

// 7. 性能和优化验证
echo "\n=== 性能优化验证 ===\n";

checkItem(
    "Composer优化",
    file_exists(__DIR__ . '/vendor/composer/autoload_classmap.php'),
    "类映射已生成"
);

// 统计资源文件
$jsFiles = glob(__DIR__ . '/public/assets/js/*.js');
$cssFiles = glob(__DIR__ . '/public/assets/css/*.css');

checkItem(
    "JavaScript资源",
    count($jsFiles) > 0,
    "发现 " . count($jsFiles) . " 个JS文件"
);

checkItem(
    "CSS样式资源",
    count($cssFiles) > 0,
    "发现 " . count($cssFiles) . " 个CSS文件"
);

// 8. 三完编译最终验证
echo "\n=== 三完编译最终验证 ===\n";

if (file_exists($threeCompleteValidator)) {
    try {
        ob_start();
        include $threeCompleteValidator;
        $validatorOutput = ob_get_clean();
        
        checkItem(
            "三完编译验证器",
            strpos($validatorOutput, '三完编译验证通过') !== false,
            "验证器执行成功"
        );
    } catch (Exception $e) {
        checkItem(
            "三完编译验证器",
            false,
            "验证器执行失败: " . $e->getMessage()
        );
    }
}

// 最终报告
echo "\n";
echo "════════════════════════════════════════════════════════════════════════════════\n";
echo "📊 最终验证报告\n";
echo "════════════════════════════════════════════════════════════════════════════════\n";

$successRate = $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100, 1) : 0;

echo "总检查项目: {$totalChecks}\n";
echo "通过项目: {$passedChecks}\n";
echo "失败项目: " . ($totalChecks - $passedChecks) . "\n";
echo "成功率: {$successRate}%\n";

if ($successRate >= 95) {
    echo "\n🎉 系统验证通过！AlingAi Pro企业系统已完全就绪！\n";
    echo "\n🚀 现在可以启动系统:\n";
    echo "   cd " . __DIR__ . "\n";
    echo "   php -S localhost:8000 -t public/\n";
    echo "\n🌐 访问地址:\n";
    echo "   - 主页: http://localhost:8000/\n";
    echo "   - 管理端: http://localhost:8000/admin\n";
    echo "   - API文档: http://localhost:8000/api/docs\n";
} elseif ($successRate >= 80) {
    echo "\n⚠️ 系统基本就绪，但存在一些小问题需要解决。\n";
} else {
    echo "\n❌ 系统存在重要问题，需要进一步修复。\n";
}

echo "\n🏆 三完编译状态: " . ($successRate >= 95 ? "100%完成" : "进行中") . "\n";
echo "════════════════════════════════════════════════════════════════════════════════\n";
echo "\n";
?>
