<?php
/**
 * AlingAI Pro 5.0 快速启动脚�?
 * 版本: 5.0.0-Final
 * 日期: 2024-12-19
 */

declare(strict_types=1];

echo "🚀 AlingAI Pro 5.0 快速启动\n";
echo str_repeat("=", 50) . "\n";

// 检查操作系�?
$isWindows = PHP_OS_FAMILY === 'Windows';
$deployScript = $isWindows ? 'deploy\\complete_deployment.bat' : 'deploy/complete_deployment.sh';

echo "检测到操作系统: " . PHP_OS_FAMILY . "\n";
echo "使用部署脚本: $deployScript\n\n";

// 检查部署脚本是否存�?
if (!file_exists($deployScript)) {
    echo "�?部署脚本不存�? $deployScript\n";
    echo "请确保部署脚本文件存在。\n";
    exit(1];
}

// 设置脚本为可执行（Linux/Mac�?
if (!$isWindows) {
    chmod($deployScript, 0755];
    echo "�?设置部署脚本为可执行\n";
}

echo "🎯 即将执行完整系统部署...\n";
echo "这将包括：\n";
echo "  �?环境检查和依赖安装\n";
echo "  �?数据库初始化\n";
echo "  �?安全系统配置\n";
echo "  �?WebSocket服务器启动\n";
echo "  �?实时监控系统启动\n";
echo "  �?Web服务器启动\n";
echo "  �?系统健康检查\n\n";

// 等待用户确认
if ($isWindows) {
    echo "按任意键继续部署，或 Ctrl+C 取消...\n";
    $handle = fopen("php://stdin", "r"];
    fgetc($handle];
    fclose($handle];
} else {
    echo "�?Enter 继续部署，或 Ctrl+C 取消...\n";
    fgets(STDIN];
}

echo "\n开始部�?..\n";
echo str_repeat("-", 50) . "\n";

// 执行部署脚本
$command = $isWindows ? $deployScript : "bash $deployScript";
$output = [];
$returnCode = 0;

// 实时输出部署过程
if ($isWindows) {
    // Windows下使�?proc_open 实现实时输出
    $descriptorspec = [
        0 => ["pipe", "r"],   // stdin
        1 => ["pipe", "w"],   // stdout
        2 => ["pipe", "w"]   // stderr
    ];
    
    $process = proc_open($command, $descriptorspec, $pipes];
    
    if (is_resource($process)) {
        fclose($pipes[0]];
        
        // 读取输出
        while (($line = fgets($pipes[1])) !== false) {
            echo $line;
        }
        
        while (($line = fgets($pipes[2])) !== false) {
            echo $line;
        }
        
        fclose($pipes[1]];
        fclose($pipes[2]];
        
        $returnCode = proc_close($process];
    }
} else {
    // Linux/Mac下直接执�?
    passthru($command, $returnCode];
}

echo "\n" . str_repeat("-", 50) . "\n";

if ($returnCode === 0) {
    echo "�?部署完成！\n\n";
    
    // 显示访问信息
    echo "🌐 系统已启动，访问地址：\n";
    echo "  主应�? http://localhost:8000\n";
    echo "  安全监控: http://localhost:8000/security/monitoring\n";
    echo "  3D威胁可视�? http://localhost:8000/security/visualization\n";
    echo "  管理后台: http://localhost:8000/admin\n\n";
    
    // 运行系统健康检�?
    echo "🔍 执行系统健康检�?..\n";
    if (file_exists('system_health_check.php')) {
        include 'system_health_check.php';
    } else {
        echo "⚠️ 系统健康检查脚本不存在\n";
    }
    
    echo "\n🎉 AlingAI Pro 5.0 启动完成！\n";
    echo "系统现在可以使用了。\n\n";
    
    echo "💡 快速提示：\n";
    echo "  �?查看服务状�? php deploy/check_status.php\n";
    echo "  �?停止所有服�? php deploy/stop_services.php\n";
    echo "  �?重启服务: php deploy/restart_services.php\n";
    echo "  �?查看日志: tail -f logs/system/webserver.log\n\n";
    
} else {
    echo "�?部署失败！\n";
    echo "返回代码: $returnCode\n";
    echo "请检查错误信息并重试。\n\n";
    
    echo "🛠�?故障排除建议：\n";
    echo "  1. 确保PHP 8.1+已安装\n";
    echo "  2. 确保Composer已安装\n";
    echo "  3. 检查文件权限\n";
    echo "  4. 确保端口8000�?080未被占用\n";
    echo "  5. 检查网络连接\n\n";
    
    exit(1];
}
