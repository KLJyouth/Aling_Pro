<?php
// 简单的PHP文件修复脚本

if ($argc < 2) {
    echo "用法: php fix_php_simple.php [PHP文件路径]\n";
    exit(1);
}

$file_path = $argv[1];

if (!file_exists($file_path)) {
    echo "错误: 文件不存在 - $file_path\n";
    exit(1);
}

// 创建备份
$backup_path = $file_path . '.bak';
copy($file_path, $backup_path);
echo "创建备份: $backup_path\n";

// 读取文件内容
$content = file_get_contents($file_path);

// 检查BOM标记
if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
    $content = substr($content, 3);
    echo "移除BOM标记\n";
}

// 修复PHP开头标签
if (preg_match('/^<\?(?!php)/', $content)) {
    $content = preg_replace('/^<\?(?!php)/', '<?php', $content);
    echo "修复PHP开头标签 <? -> <?php\n";
}
if (preg_match('/^<\?hp/', $content)) {
    $content = preg_replace('/^<\?hp/', '<?php', $content);
    echo "修复PHP开头标签 <?hp -> <?php\n";
}
if (preg_match('/^<\?php;/', $content)) {
    $content = preg_replace('/^<\?php;/', '<?php', $content);
    echo "修复PHP开头标签 <?php; -> <?php\n";
}

// 修复行末多余的引号和分号
$content = preg_replace('/(["\']);\s*$/m', '$1,', $content);
echo "修复行末多余的引号和分号\n";

// 修复数组定义中的问题
$content = preg_replace('/([\'"])\s*=>\s*([^,\s\n\r\]]+)([\'"]);\s*$/m', '$1 => $2$3,', $content);
echo "修复数组定义中的问题\n";

// 修复注释格式
$content = preg_replace('/\/\/\s*不可达代码\s*;/', '// 不可达代码', $content);
echo "修复注释格式\n";

// 保存修改后的内容
file_put_contents($file_path, $content);
echo "文件处理完成: $file_path\n"; 