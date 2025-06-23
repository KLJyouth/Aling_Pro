<?php
/**
 * WebSocket服务器管理脚本
 * 提供启动、停止、重启WebSocket服务器的功能
 */

require_once __DIR__ . '/vendor/autoload.php';

// 读取环境配置
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

$websocketEnabled = $_ENV['WEBSOCKET_ENABLED'] ?? 'false';
$websocketHost = $_ENV['WEBSOCKET_HOST'] ?? '127.0.0.1';
$websocketPort = $_ENV['WEBSOCKET_PORT'] ?? '8080';

class WebSocketManager {
    private $host;
    private $port;
    private $pidFile;
    
    public function __construct($host = '127.0.0.1', $port = 8080) {
        $this->host = $host;
        $this->port = $port;
        $this->pidFile = __DIR__ . '/storage/websocket.pid';
        
        // 确保storage目录存在
        if (!is_dir(__DIR__ . '/storage')) {
            mkdir(__DIR__ . '/storage', 0755, true);
        }
    }
    
    public function start() {
        if ($this->isRunning()) {
            echo "WebSocket服务器已在运行中 (PID: " . $this->getPid() . ")\n";
            return false;
        }
        
        echo "正在启动WebSocket服务器...\n";
        echo "地址: ws://{$this->host}:{$this->port}\n";        // 在Windows上启动后台进程
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = "start /B php " . __DIR__ . "/websocket_simple_react.php > storage/websocket.log 2>&1";
            popen($command, 'r');        } else {
            $command = "php " . __DIR__ . "/websocket_simple_react.php > storage/websocket.log 2>&1 & echo $! > " . $this->pidFile;
            exec($command);
        }
        
        // 等待一秒钟检查是否启动成功
        sleep(1);
        
        if ($this->checkConnection()) {
            echo "✅ WebSocket服务器启动成功!\n";
            echo "📡 前端连接地址: ws://{$this->host}:{$this->port}/ws\n";
            return true;
        } else {
            echo "❌ WebSocket服务器启动失败\n";
            return false;
        }
    }
    
    public function stop() {
        if (!$this->isRunning()) {
            echo "WebSocket服务器未运行\n";
            return false;
        }
        
        $pid = $this->getPid();
        echo "正在停止WebSocket服务器 (PID: $pid)...\n";
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec("taskkill /F /PID $pid 2>nul");
        } else {
            exec("kill $pid");
        }
        
        if (file_exists($this->pidFile)) {
            unlink($this->pidFile);
        }
        
        echo "✅ WebSocket服务器已停止\n";
        return true;
    }
    
    public function restart() {
        echo "正在重启WebSocket服务器...\n";
        $this->stop();
        sleep(1);
        return $this->start();
    }
    
    public function status() {
        echo "==================================================\n";
        echo "WebSocket服务器状态\n";
        echo "==================================================\n";
        echo "地址: ws://{$this->host}:{$this->port}\n";
        echo "状态: " . ($this->isRunning() ? "运行中" : "已停止") . "\n";
        
        if ($this->isRunning()) {
            echo "PID: " . $this->getPid() . "\n";
            echo "连接测试: " . ($this->checkConnection() ? "成功" : "失败") . "\n";
        }
        
        echo "配置文件: " . (__DIR__ . '/.env') . "\n";
        echo "日志文件: " . (__DIR__ . '/storage/websocket.log') . "\n";
        echo "==================================================\n";
    }
    
    private function isRunning() {
        if (!file_exists($this->pidFile)) {
            return false;
        }
        
        $pid = file_get_contents($this->pidFile);
        if (!$pid) {
            return false;
        }
        
        // 检查进程是否存在
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $result = shell_exec("tasklist /FI \"PID eq $pid\" 2>nul");
            return strpos($result, $pid) !== false;
        } else {
            return file_exists("/proc/$pid");
        }
    }
    
    private function getPid() {
        if (file_exists($this->pidFile)) {
            return trim(file_get_contents($this->pidFile));
        }
        return null;
    }
    
    private function checkConnection() {
        // 简单的端口检查
        $connection = @fsockopen($this->host, $this->port, $errno, $errstr, 1);
        if ($connection) {
            fclose($connection);
            return true;
        }
        return false;
    }
}

// 主程序
$manager = new WebSocketManager($websocketHost, $websocketPort);

if ($argc < 2) {
    echo "WebSocket服务器管理工具\n";
    echo "使用方法: php websocket_manager.php [命令]\n\n";
    echo "可用命令:\n";
    echo "  start   - 启动WebSocket服务器\n";
    echo "  stop    - 停止WebSocket服务器\n";
    echo "  restart - 重启WebSocket服务器\n";
    echo "  status  - 查看服务器状态\n";
    echo "\n";
    $manager->status();
    exit(1);
}

$command = $argv[1];

switch ($command) {
    case 'start':
        $manager->start();
        break;
        
    case 'stop':
        $manager->stop();
        break;
        
    case 'restart':
        $manager->restart();
        break;
        
    case 'status':
        $manager->status();
        break;
        
    default:
        echo "未知命令: $command\n";
        echo "使用 'php websocket_manager.php' 查看帮助\n";
        exit(1);
}
