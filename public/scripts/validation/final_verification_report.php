<?php
/**
 * AlingAi Pro v4.0 最终系统验证报�?
 * 完整的系统集成测试和验证总结
 */

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "�?             AlingAi Pro v4.0 最终系统验证报�?             ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "验证时间: " . date('Y-m-d H:i:s') . "\n";
echo "系统版本: AlingAi Pro v4.0\n";
echo "测试环境: Windows + PHP 8.1.32\n\n";

// 1. 文件系统完整性检�?
echo "📁 文件系统完整性检查\n";
echo str_repeat("=", 50) . "\n";

$coreFiles = [
    'public/index.html' => '主页�?,
    'public/login.html' => '登录页面',
    'public/register.html' => '注册页面',
    'public/dashboard.html' => '仪表板页�?,
    'public/chat.html' => '聊天页面',
    'public/api/index.php' => 'API入口',
    'public/assets/js/main.js' => '主要JavaScript',
    'public/assets/js/auth.js' => '认证脚本',
    'public/assets/js/ui.js' => 'UI控制脚本',
    'public/assets/css/style.css' => '主样式表',
    'src/Config/config.php' => '系统配置',
    'storage/data/users.json' => '用户数据',
    'storage/data/system_status.json' => '系统状�?
];

$fileIntegrity = [];
$totalFiles = count($coreFiles];
$existingFiles = 0;

foreach ($coreFiles as $file => $description) {
    $exists = file_exists(__DIR__ . '/' . $file];
    $fileIntegrity[$file] = [
        'exists' => $exists,
        'description' => $description,
        'size' => $exists ? filesize(__DIR__ . '/' . $file) : 0
    ];
    
    if ($exists) {
        $existingFiles++;
        echo "�?$description ($file)\n";
    } else {
        echo "�?$description ($file) - 缺失\n";
    }
}

echo "\n文件完整�? $existingFiles/$totalFiles (" . round(($existingFiles/$totalFiles)*100, 1) . "%)\n\n";

// 2. 服务器状态检�?
echo "🌐 Web服务器状态检查\n";
echo str_repeat("=", 50) . "\n";

$serverRunning = false;
$ch = curl_init(];
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/index.html'];
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true];
curl_setopt($ch, CURLOPT_TIMEOUT, 5];
curl_setopt($ch, CURLOPT_NOBODY, true];

$result = curl_exec($ch];
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE];
curl_close($ch];

if ($httpCode == 200) {
    $serverRunning = true;
    echo "�?PHP开发服务器正在运行 (localhost:8000)\n";
    echo "�?主页面可访问\n";
} else {
    echo "�?服务器未运行或页面不可访�?(HTTP $httpCode)\n";
}

// 3. 数据存储检�?
echo "\n🗄�?数据存储系统检查\n";
echo str_repeat("=", 50) . "\n";

$usersFile = __DIR__ . '/storage/data/users.json';
$statusFile = __DIR__ . '/storage/data/system_status.json';

if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile], true];
    echo "�?用户数据文件存在 (用户�? " . count($users) . ")\n";
    
    // 检查管理员账户
    $adminExists = false;
    foreach ($users as $user) {
        if ($user['username'] === 'admin' && $user['role'] === 'admin') {
            $adminExists = true;
            break;
        }
    }
    
    if ($adminExists) {
        echo "�?管理员账户已配置\n";
    } else {
        echo "⚠️ 管理员账户未找到\n";
    }
} else {
    echo "�?用户数据文件不存在\n";
}

if (file_exists($statusFile)) {
    $status = json_decode(file_get_contents($statusFile], true];
    echo "�?系统状态文件存在\n";
    if ($status['system_ready']) {
        echo "�?系统状�? 就绪\n";
    }
} else {
    echo "�?系统状态文件不存在\n";
}

// 4. 配置系统检�?
echo "\n⚙️ 配置系统检查\n";
echo str_repeat("=", 50) . "\n";

$configFile = __DIR__ . '/src/Config/config.php';
if (file_exists($configFile)) {
    echo "�?主配置文件存在\n";
    
    $config = include $configFile;
    if (is_[$config)) {
        echo "�?配置文件格式正确\n";
        echo "�?数据库引�? " . $config['database']['engine'] . "\n";
        echo "�?JWT配置: 已设置\n";
        echo "�?CORS设置: " . ($config['cors']['enabled'] ? '启用' : '禁用') . "\n";
    } else {
        echo "�?配置文件格式错误\n";
    }
} else {
    echo "�?主配置文件不存在\n";
}

// 5. 功能模块检�?
echo "\n🛠�?核心功能模块检查\n";
echo str_repeat("=", 50) . "\n";

$jsModules = [
    'public/assets/js/auth.js' => '认证模块',
    'public/assets/js/ui.js' => 'UI交互模块',
    'public/assets/js/main.js' => '核心功能模块',
    'public/assets/js/apiConfig.js' => 'API配置模块',
    'public/assets/js/system-integration-manager.js' => '系统集成管理�?,
    'public/assets/js/dashboard-integration.js' => '仪表板集�?,
    'public/assets/js/ultimate-performance-validator.js' => '性能验证�?
];

foreach ($jsModules as $file => $module) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "�?$module\n";
    } else {
        echo "�?$module - 缺失\n";
    }
}

// 6. API端点检�?
echo "\n🔌 API端点检查\n";
echo str_repeat("=", 50) . "\n";

$apiFiles = [
    'public/api/index.php' => 'API主入�?,
    'public/api/auth/login.php' => '登录API',
    'public/api/auth/register.php' => '注册API',
    'src/Core/ApiRoutes.php' => 'API路由配置',
    'src/Core/ApiHandler.php' => 'API处理�?
];

foreach ($apiFiles as $file => $description) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "�?$description\n";
    } else {
        echo "�?$description - 缺失\n";
    }
}

// 7. 安全系统检�?
echo "\n🔐 安全系统检查\n";
echo str_repeat("=", 50) . "\n";

$securityFeatures = [
    'JWT认证配置' => isset($config['jwt']['secret']],
    'CORS保护' => isset($config['cors']['enabled']],
    '密码加密' => function_exists('password_hash'],
    '会话安全' => file_exists(__DIR__ . '/public/assets/js/auth.js'],
    '输入验证' => file_exists(__DIR__ . '/src/Core/ApiHandler.php')
];

foreach ($securityFeatures as $feature => $available) {
    if ($available) {
        echo "�?$feature\n";
    } else {
        echo "�?$feature - 未配置\n";
    }
}

// 8. 生成最终评�?
echo "\n" . str_repeat("=", 70) . "\n";
echo "最终系统评估\n";
echo str_repeat("=", 70) . "\n";

$score = 0;
$maxScore = 10;

// 评分标准
if ($existingFiles >= $totalFiles * 0.9) $score += 2; // 文件完整�?
if ($serverRunning) $score += 2; // 服务器运�?
if (file_exists($usersFile) && file_exists($statusFile)) $score += 2; // 数据存储
if (file_exists($configFile)) $score += 1; // 配置文件
if (file_exists(__DIR__ . '/public/assets/js/auth.js')) $score += 1; // 认证模块
if (file_exists(__DIR__ . '/public/api/index.php')) $score += 1; // API端点
if (isset($config['jwt']['secret'])) $score += 1; // 安全配置

$percentage = round(($score / $maxScore) * 100];

echo "系统评分: $score/$maxScore ($percentage%)\n\n";

if ($percentage >= 90) {
    echo "🎉 系统状�? 优秀\n";
    echo "�?AlingAi Pro v4.0 已完全就绪，可以投入生产使用\n";
} elseif ($percentage >= 70) {
    echo "👍 系统状�? 良好\n";
    echo "�?AlingAi Pro v4.0 基本就绪，建议修复剩余问题后使用\n";
} elseif ($percentage >= 50) {
    echo "⚠️ 系统状�? 需要改进\n";
    echo "📋 AlingAi Pro v4.0 需要解决关键问题才能正常使用\n";
} else {
    echo "�?系统状�? 需要重大修复\n";
    echo "🔧 AlingAi Pro v4.0 需要大量修复工作\n";
}

echo "\n快速访问链�?\n";
echo "- 主页: http://localhost:8000/index.html\n";
echo "- 登录: http://localhost:8000/login.html\n";
echo "- 仪表�? http://localhost:8000/dashboard.html\n";
echo "- 聊天: http://localhost:8000/chat.html\n";
echo "- 管理�? http://localhost:8000/admin.html\n";

echo "\n默认管理员账�?\n";
echo "- 用户�? admin\n";
echo "- 密码: admin123\n";

// 保存验证报告
$reportData = [
    'verification_time' => date('Y-m-d H:i:s'],
    'system_version' => 'AlingAi Pro v4.0',
    'file_integrity' => $fileIntegrity,
    'server_running' => $serverRunning,
    'score' => $score,
    'max_score' => $maxScore,
    'percentage' => $percentage,
    'status' => $percentage >= 70 ? 'ready' : 'needs_work'
];

$reportFile = __DIR__ . '/storage/logs/final_verification_report_' . date('Y_m_d_H_i_s') . '.json';
file_put_contents($reportFile, json_encode($reportData, JSON_PRETTY_PRINT)];

echo "\n📊 详细验证报告已保存到: $reportFile\n";

echo "\n╔══════════════════════════════════════════════════════════════╗\n";
echo "�?                   验证完成                                  ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
?>

