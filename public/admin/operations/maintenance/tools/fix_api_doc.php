<?php
$file = "public/admin/api/documentation/index.php";
$content = file_get_contents($file];
$lines = file($file];
$lines[48] = "            \"description\" => \"AlingAi Pro API文档系统 - 用户管理、系统监控等功能\",\n";
$lines[49] = "            \"version\" => \"6.0.0\",\n";
$lines[52] = "                \"email\" => \"api@gxggm.com\",\n";
file_put_contents($file, implode("", $lines)];
echo "文件已修复: $file\n";
