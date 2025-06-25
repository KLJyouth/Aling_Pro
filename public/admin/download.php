<?php
/**
 * 文件下载处理�?
 */

session_start(];

// 安全检�?
if (!isset($_SESSION['admin_authorized'])) {
    http_response_code(403];
    exit('Forbidden'];
}

$file = $_GET['file'] ?? '';
$exportDir = __DIR__ . '/../storage/exports';
$filePath = $exportDir . '/' . basename($file];

// 安全检查：确保文件在允许的目录�?
if (!file_exists($filePath) || strpos(realpath($filePath], realpath($exportDir)) !== 0) {
    http_response_code(404];
    exit('File not found'];
}

// 设置下载�?
header('Content-Type: application/octet-stream'];
header('Content-Disposition: attachment; filename="' . basename($file) . '"'];
header('Content-Length: ' . filesize($filePath)];

// 输出文件
readfile($filePath];

// 下载后删除文�?
unlink($filePath];
exit;
