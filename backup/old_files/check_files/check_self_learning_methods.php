<?php
require_once 'vendor/autoload.php';

echo "检查 SelfLearningFramework 方法:\n";
$reflection = new ReflectionClass('AlingAi\\AI\\SelfLearningFramework');
$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

foreach ($methods as $method) {
    echo "- " . $method->getName() . "()\n";
}

// 特别检查我们需要的方法
$requiredMethods = [
    'executeSpecificLearning',
    'getLearningProgress', 
    'getStatus'
];

echo "\n特别检查必需方法:\n";
foreach ($requiredMethods as $methodName) {
    $exists = $reflection->hasMethod($methodName);
    echo "- $methodName: " . ($exists ? "✅ 存在" : "❌ 不存在") . "\n";
}
?>
