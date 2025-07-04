<?php
/**
 * AlingAi Pro 安装向导 - 删除安装目录
 */

// 设置响应头
header("Content-Type: application/json");

// 删除安装目录
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return true;
    }
    
    $files = array_diff(scandir($dir), [".", ".."]);
    
    foreach ($files as $file) {
        $path = $dir . "/" . $file;
        
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    
    return rmdir($dir);
}

try {
    // 删除安装目录
    $installDir = __DIR__;
    
    if (deleteDirectory($installDir)) {
        echo json_encode([
            "success" => true,
            "message" => "安装目录已成功删除"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "无法删除安装目录"
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
