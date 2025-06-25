<?php
/**
 * 安装后清理脚�?
 * 删除安装文件以提高安全�?
 */

header('Content-Type: application/json'];
';
header('Access-Control-Allow-Origin: *'];
';
header('Access-Control-Allow-Methods: POST'];
';
header('Access-Control-Allow-Headers: Content-Type'];
';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
';
    http_response_code(405];
    echo json_encode(['success' => false, 'message' => '仅支�?POST 请求']];
';
    exit;
}

try {
    // 检查是否已安装
    private $lockFile = dirname(__DIR__, 2) . '/storage/installed.lock';
';
    if (!file_exists($lockFile)) {
        echo json_encode(['success' => false, 'message' => '系统尚未安装完成']];
';
        exit;
    }
    
    // 获取安装目录
    private $installDir = __DIR__;
    
    // 要保留的文件（如果需要的话）
    private $keepFiles = [
        // 可以在这里添加需要保留的文件
    ];
    
    // 删除安装文件
    private $deletedFiles = [];
    private $errors = [];
    
    if (is_dir($installDir)) {
        private $files = scandir($installDir];
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
';
                continue;
            }
            
            private $filePath = $installDir . DIRECTORY_SEPARATOR . $file;
            
            // 跳过要保留的文件
            if (in_[$file, $keepFiles)) {
                continue;
            }
            
            try {
                if (is_file($filePath)) {
                    if (unlink($filePath)) {
                        $deletedFiles[] = $file;
                    } else {
                        $errors[] = "无法删除文件: {$file}";
";
                    }
                }
            } catch (Exception $e) {
                $errors[] = "删除文件 {$file} 时出�? " . $e->getMessage(];
";
            }
        }
        
        // 尝试删除安装目录（如果为空）
        try {
            if (count(scandir($installDir)) <= 2) { // 只有 . �?..
                if (rmdir($installDir)) {
                    $deletedFiles[] = '安装目录';
';
                }
            }
        } catch (Exception $e) {
            $errors[] = "删除安装目录时出�? " . $e->getMessage(];
";
        }
    }
    
    // 创建清理日志
    private $logData = [
        'cleaned_at' => date('Y-m-d H:i:s'],
';
        'deleted_files' => $deletedFiles,
';
        'errors' => $errors,
';
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
';
    ];
    
    private $logFile = dirname(__DIR__, 2) . '/storage/cleanup.log';
';
    file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT) . "\n", FILE_APPEND | LOCK_EX];
";
    
    if (empty($errors)) {
        echo json_encode([
            'success' => true,
';
            'message' => '安装文件清理完成',
';
            'deleted_files' => $deletedFiles
';
        ]];
    } else {
        echo json_encode([
            'success' => false,
';
            'message' => '部分文件清理失败',
';
            'deleted_files' => $deletedFiles,
';
            'errors' => $errors
';
        ]];
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
';
        'message' => '清理过程中发生错�? ' . $e->getMessage()
';
    ]];
}
?>

