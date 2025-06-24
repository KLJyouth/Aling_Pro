<?php
/**
 * 修复API文档中的引用问题
 * 将
 */

// 定义要处理的文件
$file = " public/admin/api/documentation/index.php\;

// 读取文件内容
$content = file_get_contents($file);
if ($content === false) {
 die(\无法读取文件: $file\n\);
}

// 创建备份
$backup = $file . \.bak\;
if (!file_exists($backup)) {
 file_put_contents($backup, $content);
 echo \已创建备份: $backup\n\;
}

// 修复
$content = str_replace(\[\\\\\\]\, \[\\\ref\\\]\, $content);

// 保存修复后的文件
if (file_put_contents($file, $content)) {
 echo \已修复文件: $file\n\;
} else {
 echo \无法写入文件: $file\n\;
}

echo \修复完成\n\;
