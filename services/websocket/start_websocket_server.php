<?php
require_once __DIR__ . "/vendor/autoload.php";

use AlingAi\Security\WebSocketSecurityServer;
use AlingAi\Security\RealTimeNetworkMonitor;
use AlingAi\Services\DatabaseService;
use AlingAi\Services\DeepSeekAIService;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger("WebSocket");
$logger->pushHandler(new StreamHandler(__DIR__ . "/logs/websocket.log", Logger::INFO));

try {
    // 创建数据库服务
    $database = new AlingAi\Services\DatabaseService();
    
    // 创建AI服务 - GlobalThreatIntelligence需要这个参数
    $apiKey = $_ENV['DEEPSEEK_API_KEY'] ?? 'sk-test-key-for-websocket';
    $aiService = new DeepSeekAIService($apiKey, $database, $logger);
    
    // 创建安全系统组件
    $securitySystem = new AlingAi\Security\IntelligentSecuritySystem($database, $logger);
    $threatIntel = new AlingAi\Security\GlobalThreatIntelligence($database, $aiService, $logger);
    $networkMonitor = new RealTimeNetworkMonitor($database, $logger, $securitySystem, $threatIntel);
    
    $server = new WebSocketSecurityServer($networkMonitor, $logger);
    $server->start();
} catch (Exception $e) {
    $logger->error("WebSocket服务器启动失败: " . $e->getMessage());
    echo "❌ WebSocket服务器启动失败: " . $e->getMessage() . "\n";
}
