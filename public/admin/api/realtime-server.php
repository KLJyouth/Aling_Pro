<?php
/**
 * AlingAi Pro 5.0 - 实时数据推送服务器 (无Socket依赖版本)
 * 使用HTTP长轮询技术实现实时数据推送
 * 
 * @package AlingAi\Pro\Admin
 * @version 1.0.0
 */

declare(strict_types=1);

/**
 * 数据存储类
 */
class DataStore
{
    private $data = [];
    private $lastUpdate;
    
    public function __construct() {
        $this->lastUpdate = time();
    }
    
    /**
     * 更新数据
     */
    public function updateData(array $data): void
    {
        $this->data = array_merge($this->data, $data);
        $this->lastUpdate = time();
    }
    
    /**
     * 获取数据
     */
    public function getData(): array
    {
        return $this->data;
    }
    
    /**
     * 获取最后更新时间
     */
    public function getLastUpdate(): int
    {
        return $this->lastUpdate;
    }
}

class RealtimeDataServer
{
    private $dataStore;
    private $lastUpdate;
    private $clients;
    
    public function __construct() {
        $this->dataStore = new DataStore();
        $this->lastUpdate = time();
        $this->clients = [];
        
        // 设置CORS头
        $this->setCorsHeaders();
    }
    
    /**
     * 设置CORS头
     */
    private function setCorsHeaders(): void
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Access-Control-Max-Age: 86400");
        
        if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
            http_response_code(200);
            exit;
        }
    }
    
    /**
     * 主要处理入口
     */
    public function handle(): void
    {
        $action = $_GET["action"] ?? "poll";
        
        switch ($action) {
            case "poll":
                $this->handleLongPolling();
                break;
                
            case "status":
                $this->handleStatus();
                break;
                
            case "push":
                $this->handleDataPush();
                break;
                
            default:
                $this->sendError("Invalid action");
                break;
        }
    }
    
    /**
     * 处理长轮询请求
     */
    private function handleLongPolling(): void
    {
        $timeout = (int)($_GET["timeout"] ?? 30);
        $lastClientUpdate = (int)($_GET["timestamp"] ?? 0);
        
        // 设置超时
        set_time_limit($timeout + 5);
        
        $startTime = time();
        $maxWaitTime = min($timeout, 30); // 最大等待30秒
        
        while ((time() - $startTime) < $maxWaitTime) {
            // 检查是否有新数据
            $currentData = $this->getCurrentData();
            $currentTimestamp = time();
            
            if ($currentTimestamp > $lastClientUpdate) {
                // 有新数据，立即返回
                $this->sendSuccess([
                    "data" => $currentData,
                    "timestamp" => $currentTimestamp,
                    "hasUpdate" => true
                ]);
                return;
            }
            
            // 没有新数据，等待1秒后重新检查
            sleep(1);
        }
        
        // 超时，返回当前数据
        $this->sendSuccess([
            "data" => $this->getCurrentData(),
            "timestamp" => time(),
            "hasUpdate" => false,
            "timeout" => true
        ]);
    }
    
    /**
     * 处理服务器状态请求
     */
    private function handleStatus(): void
    {
        $this->sendSuccess([
            "server" => "AlingAi Pro Realtime Server",
            "version" => "1.0.0",
            "timestamp" => time(),
            "uptime" => time() - $this->lastUpdate,
            "type" => "long-polling"
        ]);
    }
    
    /**
     * 处理数据推送
     */
    private function handleDataPush(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->sendError("Only POST method allowed for push");
            return;
        }
        
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!$input) {
            $this->sendError("Invalid JSON data");
            return;
        }
        
        // 更新数据存储
        $this->dataStore->updateData($input);
        $this->lastUpdate = time();
        
        $this->sendSuccess([
            "message" => "Data pushed successfully",
            "timestamp" => $this->lastUpdate
        ]);
    }
    
      /**
     * 获取当前实时数据
     */
    private function getCurrentData(): array
    {
        try {
            // 连接数据库获取实时数据
            $pdo = $this->connectDatabase();
            
            if (!$pdo) {
                // 数据库连接失败，返回模拟数据
                return $this->getMockData();
            }
            
            // 获取系统统计
            $systemStats = $this->getSystemStats($pdo);
            
            // 获取用户统计
            $userStats = $this->getUserStats($pdo);
            
            return [
                "system" => $systemStats,
                "users" => $userStats,
                "timestamp" => time()
            ];
        } catch (\Exception $e) {
            error_log("Error getting current data: " . $e->getMessage());
            return $this->getMockData();
        }
    }
    
      /**
     * 连接数据库
     */
    private function connectDatabase(): ?\PDO
    {
        try {
            $dsn = "sqlite:" . dirname(__DIR__) . "/database/admin.db";
            return new \PDO($dsn, null, null, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]);
        } catch (\PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * 获取系统统计信息
     */
    private function getSystemStats(\PDO $pdo): array
    {
        $stats = [
            "cpu_usage" => 0,
            "memory_usage" => 0,
            "disk_usage" => 0,
            "network_traffic" => 0
        ];
        
        try {
            $stmt = $pdo->query("SELECT * FROM system_stats ORDER BY id DESC LIMIT 1");
            if ($row = $stmt->fetch()) {
                $stats = $row;
            }
        } catch (\PDOException $e) {
            error_log("Error getting system stats: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * 获取用户统计信息
     */
    private function getUserStats(\PDO $pdo): array
    {
        $stats = [
            "total_users" => 0,
            "active_users" => 0,
            "new_users_today" => 0
        ];
        
        try {
            $stmt = $pdo->query("SELECT * FROM user_stats ORDER BY id DESC LIMIT 1");
            if ($row = $stmt->fetch()) {
                $stats = $row;
            }
        } catch (\PDOException $e) {
            error_log("Error getting user stats: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * 获取模拟数据
     */
    private function getMockData(): array
    {
            return [
            "system" => [
                "cpu_usage" => rand(10, 90),
                "memory_usage" => rand(20, 80),
                "disk_usage" => rand(30, 70),
                "network_traffic" => rand(1000, 5000)
            ],
            "users" => [
                "total_users" => rand(100, 1000),
                "active_users" => rand(50, 200),
                "new_users_today" => rand(5, 50)
            ],
            "timestamp" => time()
        ];
    }
    
    /**
     * 发送成功响应
     */
    private function sendSuccess($data): void
    {
        header("Content-Type: application/json");
        echo json_encode([
            "status" => "success",
            "data" => $data
        ]);
        exit;
    }
    
    /**
     * 发送错误响应
     */
    private function sendError($message): void
    {
        header("Content-Type: application/json");
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => $message
        ]);
        exit;
    }
}

// 创建并运行服务器
$server = new RealtimeDataServer();
    $server->handle();
