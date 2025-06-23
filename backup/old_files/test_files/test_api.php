<?php
// 简单的 API 测试脚本
$response = file_get_contents('http://localhost:8000/api/system/status');
echo "API Response:\n";
echo $response;
echo "\n";
