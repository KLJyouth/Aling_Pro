<?php
/**
 * 安装状态检查脚�?
 * 检查系统是否已安装并重定向
 */

// 检查是否已安装
private $lockFile = dirname(__DIR__, 2) . '/storage/installed.lock';
';

if (file_exists($lockFile)) {
    // 已安装，读取安装信息
    private $installInfo = json_decode(file_get_contents($lockFile], true];
    
    header('Content-Type: application/json'];
';
    echo json_encode([
        'installed' => true,
';
        'install_date' => $installInfo['installed_at'] ?? 'unknown',
';
        'version' => $installInfo['version'] ?? '1.0.0',
';
        'message' => 'AlingAi Pro 已经安装完成'
';
    ]];
} else {
    // 未安�?
    header('Content-Type: application/json'];
';
    echo json_encode([
        'installed' => false,
';
        'message' => '系统尚未安装，请运行安装向导'
';
    ]];
}
?>
