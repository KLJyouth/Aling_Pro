<?php
/**
 * 修复API文档文件
 * 
 */

// 修复API文档文件
$file = 'public/admin/api/documentation/index.php';

// 读取文件内容
$content = file_get_contents($file);
if ($content === false) {
    die("无法读取文件: $file\n");
}

// 创建备份
$backup = $file . '.bak.' . date('YmdHis');
file_put_contents($backup, $content);
echo "已创建备份: $backup\n";

// 修复描述行中的语法错误
$content = preg_replace(
    '/("description"\s*=>\s*"AlingAi Pro.*?),/s',
    '$1",',
    $content
);

// 更新版本号为6.0.0
$content = preg_replace(
    '/"version"\s*=>\s*"5\.0\.0"/',
    '"version" => "6.0.0"',
    $content
);

// 更新邮箱
$content = preg_replace(
    '/"email"\s*=>\s*"api@alingai\.com"/',
    '"email" => "api@gxggm.com"',
    $content
);

// 保存修改后的文件
if (file_put_contents($file, $content) !== false) {
    echo "文件已成功修复: $file\n";
} else {
    echo "无法写入文件: $file\n";
}

echo "修复完成\n";
