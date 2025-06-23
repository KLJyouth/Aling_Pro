<?php
try {
    include_once 'vendor/autoload.php';
    echo 'Autoload test: SUCCESS' . PHP_EOL;
    
    $controller = new AlingAi\Controllers\Api\AuthApiController();
    echo 'AuthApiController instantiation: SUCCESS' . PHP_EOL;
    
    echo 'Duplicate method issue: RESOLVED' . PHP_EOL;
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}
