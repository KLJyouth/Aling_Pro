<?php
/**
 * AlingAI Pro v4.0 系统状态检查脚本
 */

echo "🚀 AlingAI Pro v4.0 系统状态检查..." . PHP_EOL;
echo "✅ PHP版本: " . PHP_VERSION . PHP_EOL;
echo "✅ 当前时间: " . date('Y-m-d H:i:s') . PHP_EOL;
echo "✅ 项目路径: " . __DIR__ . PHP_EOL;
echo "✅ 核心文件检查:" . PHP_EOL;

$files = [
    'public/login.html',
    'public/register.html', 
    'public/forgot-password.html',
    'public/api-docs.html',
    'public/docs-center.html',
    'public/install-wizard.html',
    'public/profile.html',
    'public/dashboard.html',
    'public/chat.html',
    'README.md',
    'PROJECT_COMPLETION_REPORT.md',
    'SYSTEM_READY_GUIDE.md'
];

$existCount = 0;
foreach($files as $file) {
    $exists = file_exists($file);
    echo ($exists ? "  ✅ " : "  ❌ ") . $file . PHP_EOL;
    if ($exists) $existCount++;
}

echo PHP_EOL;
echo "📊 文件完整性统计:" . PHP_EOL;
echo "  总文件数: " . count($files) . PHP_EOL;
echo "  存在文件: " . $existCount . PHP_EOL;
echo "  完整度: " . round(($existCount / count($files)) * 100, 1) . "%" . PHP_EOL;

echo PHP_EOL;
echo "🎉 AlingAI Pro v4.0 系统检查完成！" . PHP_EOL;

if ($existCount == count($files)) {
    echo "✅ 所有核心文件完整，系统可以正常使用！" . PHP_EOL;
} else {
    echo "⚠️ 部分文件缺失，请检查安装完整性。" . PHP_EOL;
}
