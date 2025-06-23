<?php
// 直接测试 SystemApiController
require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Controllers\Api\SystemApiController;

try {
    echo "Testing SystemApiController directly...\n";
    
    $controller = new SystemApiController();
    echo "Controller created successfully\n";
    
    $status = $controller->getStatus();
    echo "Status check result:\n";
    echo json_encode($status, JSON_PRETTY_PRINT);
    
    echo "\n\nTesting diagnostics...\n";
    $diagnostics = $controller->runDiagnostics();
    echo "Diagnostics result:\n";
    echo json_encode($diagnostics, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
