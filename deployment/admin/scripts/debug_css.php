<?php
$content = file_get_contents('E:\Code\AlingAi\AlingAi_pro\public\assets\css\quantum-animations.css');
echo "Content length: " . strlen($content) . PHP_EOL;
echo "First 500 chars: " . substr($content, 0, 500) . PHP_EOL;

// 查找第一个 { 的位置
$firstBrace = strpos($content, '{');
echo "First brace at position: " . $firstBrace . PHP_EOL;
if ($firstBrace !== false) {
    echo "Context around first brace: " . substr($content, $firstBrace - 20, 40) . PHP_EOL;
}

// 检查具体的模式
$patterns = [
    '/\.[\w-]+\s*{/' => 'class selector',
    '/#[\w-]+\s*{/' => 'id selector', 
    '/[.#][\w-]+\s*{/' => 'any selector',
    '/\.quantum-background\s*{/' => 'specific quantum-background'
];

foreach ($patterns as $pattern => $desc) {
    $match = preg_match($pattern, $content);
    echo "$desc: " . ($match ? 'YES' : 'NO') . PHP_EOL;
}
?>