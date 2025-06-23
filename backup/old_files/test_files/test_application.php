<?php
echo "Testing Application.php step by step...\n";

// Step 1: Test basic file inclusion
echo "\n1. Testing basic file inclusion:\n";
$path = __DIR__ . '/src/Core/Application.php';
echo "Path: $path\n";
echo "File exists: " . (file_exists($path) ? 'YES' : 'NO') . "\n";

// Step 2: Test file contents
echo "\n2. Testing file contents:\n";
$content = file_get_contents($path);
echo "File size: " . strlen($content) . " bytes\n";
echo "Contains 'class Application': " . (strpos($content, 'class Application') !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'namespace AlingAi\\Core': " . (strpos($content, 'namespace AlingAi\\Core') !== false ? 'YES' : 'NO') . "\n";

// Step 3: Test manual inclusion
echo "\n3. Testing manual inclusion:\n";
try {
    $result = include_once $path;
    echo "Include result: ";
    var_dump($result);
    
    echo "get_declared_classes() contains Application: ";
    $classes = get_declared_classes();
    $found = false;
    foreach ($classes as $class) {
        if (strpos($class, 'Application') !== false) {
            echo "Found: $class\n";
            $found = true;
        }
    }
    if (!$found) {
        echo "NO\n";
    }
    
} catch (Throwable $e) {
    echo "Include failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Step 4: Test dependencies
echo "\n4. Testing dependencies:\n";
echo "Checking required classes:\n";
$requiredClasses = [
    'Slim\\App',
    'Slim\\Factory\\AppFactory',
    'Dotenv\\Dotenv',
    'Monolog\\Logger'
];

require_once __DIR__ . '/vendor/autoload.php';

foreach ($requiredClasses as $class) {
    echo "  $class: " . (class_exists($class) ? 'OK' : 'MISSING') . "\n";
}
