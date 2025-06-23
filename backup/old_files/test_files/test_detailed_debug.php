<?php
echo "=== Detailed Debug ===\n";

try {
    echo "1. Loading autoload...\n";
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✓ Autoload loaded\n";
    
    echo "2. Loading env...\n";
    if (file_exists(__DIR__ . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
        echo "✓ Env loaded\n";
    }
    
    echo "3. Checking required interfaces...\n";
    if (interface_exists('Psr\Http\Server\RequestHandlerInterface')) {
        echo "✓ RequestHandlerInterface exists\n";
    } else {
        echo "✗ RequestHandlerInterface missing\n";
    }
    
    if (interface_exists('Psr\Http\Message\ResponseInterface')) {
        echo "✓ ResponseInterface exists\n";
    } else {
        echo "✗ ResponseInterface missing\n";
    }
    
    if (interface_exists('Psr\Http\Message\ServerRequestInterface')) {
        echo "✓ ServerRequestInterface exists\n";
    } else {
        echo "✗ ServerRequestInterface missing\n";
    }
    
    echo "4. Checking required classes...\n";
    if (class_exists('Slim\App')) {
        echo "✓ Slim\App exists\n";
    } else {
        echo "✗ Slim\App missing\n";
    }
    
    if (class_exists('DI\Container')) {
        echo "✓ DI\Container exists\n";
    } else {
        echo "✗ DI\Container missing\n";
    }
    
    if (class_exists('Monolog\Logger')) {
        echo "✓ Monolog\Logger exists\n";
    } else {
        echo "✗ Monolog\Logger missing\n";
    }
    
    echo "5. Loading Application file...\n";
    $appContent = file_get_contents(__DIR__ . '/src/Core/Application.php');
    echo "Application file size: " . strlen($appContent) . " bytes\n";
    
    // 尝试eval代码
    echo "6. Evaluating Application code...\n";
    $code = str_replace('<?php', '', $appContent);
    eval($code);
    
    echo "7. Checking if class exists now...\n";
    if (class_exists('AlingAi\Core\Application', false)) {
        echo "✓ Application class exists!\n";
        
        $app = new AlingAi\Core\Application();
        echo "✓ Application instance created!\n";
        
        if (method_exists($app, 'handle')) {
            echo "✓ handle method exists!\n";
        }
        
    } else {
        echo "✗ Application class still not found\n";
    }
    
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
