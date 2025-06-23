<?php
/**
 * 修复 ErrorTracker.php 中的 Cache 调用
 */

$filePath = __DIR__ . '/src/Monitoring/ErrorTracker.php';
$content = file_get_contents($filePath);

// 替换所有 Cache:: 调用
$replacements = [
    'Cache::get(' => '$this->cacheGet(',
    'Cache::put(' => '$this->cachePut(',
    'Cache::forget(' => '$this->cacheForget(',
    'Cache::increment(' => '$this->cacheIncrement(',
    'Http::timeout(5)->post(' => '$this->sendHttpRequest('
];

foreach ($replacements as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

file_put_contents($filePath, $content);
echo "ErrorTracker.php 修复完成\n";
