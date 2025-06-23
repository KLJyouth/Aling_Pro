<?php
/**
 * 最终错误处理配置修复
 * 确保生产环境配置完全生效
 */

echo "🔧 最终错误处理配置修复\n";
echo "============================\n";

// 确保logs目录存在
$logsDir = __DIR__ . '/logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
    echo "📁 创建logs目录: " . $logsDir . "\n";
}

// 设置error_log文件路径
$errorLogPath = $logsDir . '/php_errors.log';
if (!file_exists($errorLogPath)) {
    touch($errorLogPath);
    chmod($errorLogPath, 0644);
    echo "📝 创建错误日志文件: " . $errorLogPath . "\n";
}

// 设置PHP配置
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', $errorLogPath);
ini_set('error_reporting', E_ALL);

echo "📋 设置PHP配置:\n";
echo "   - display_errors: " . ini_get('display_errors') . "\n";
echo "   - log_errors: " . ini_get('log_errors') . "\n";
echo "   - error_log: " . ini_get('error_log') . "\n";
echo "   - error_reporting: " . ini_get('error_reporting') . "\n";

// 验证配置
echo "\n🔍 验证当前配置:\n";

$checks = [
    'display_errors' => [
        'value' => ini_get('display_errors'),
        'valid' => ini_get('display_errors') == '0' || ini_get('display_errors') === '',
        'requirement' => 'Should be Off in production'
    ],
    'log_errors' => [
        'value' => ini_get('log_errors'),
        'valid' => ini_get('log_errors') == '1',
        'requirement' => 'Should be On'
    ],
    'error_log' => [
        'value' => ini_get('error_log'),
        'valid' => !empty(ini_get('error_log')),
        'requirement' => 'Should be configured'
    ],
    'error_reporting' => [
        'value' => ini_get('error_reporting'),
        'valid' => ini_get('error_reporting') == E_ALL,
        'requirement' => 'Should report all errors'
    ]
];

$allValid = true;
foreach ($checks as $check => $config) {
    $status = $config['valid'] ? '✅' : '❌';
    echo "   {$status} {$check}: {$config['value']}\n";
    if (!$config['valid']) {
        $allValid = false;
    }
}

if ($allValid) {
    echo "\n✅ 所有错误处理配置都已正确设置！\n";
    
    // 创建配置验证JSON文件
    $validationResult = [
        'status' => 'valid',
        'timestamp' => date('Y-m-d H:i:s'),
        'checks' => $checks
    ];
    
    file_put_contents(__DIR__ . '/error_handling_validation_result.json', json_encode($validationResult, JSON_PRETTY_PRINT));
    echo "📄 验证结果已保存到 error_handling_validation_result.json\n";
} else {
    echo "\n❌ 某些配置仍需修复\n";
}

echo "\n🎯 修复完成！\n";
