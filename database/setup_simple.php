<?php
/**
 * 简化版本数据库设置
 * 使用文件存储代替数据库
 */

declare(strict_types=1);

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('APP_ROOT', dirname(__DIR__));

class SimpleDatabaseSetup
{
    private string $storageDir;
    private string $dataDir;
    
    public function __construct()
    {
        $this->storageDir = APP_ROOT . '/storage';
        $this->dataDir = $this->storageDir . '/data';
        $this->createDirectories();
    }
    
    private function createDirectories(): void
    {
        $dirs = [
            $this->storageDir,
            $this->dataDir,
            $this->storageDir . '/cache',
            $this->storageDir . '/sessions',
            $this->storageDir . '/logs',
            $this->storageDir . '/mail',
            $this->storageDir . '/uploads',
        ];
        
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                echo "✓ 创建目录: $dir\n";
            }
        }
    }
    
    public function setup(): void
    {
        echo "开始简化版本数据库设置...\n\n";
        
        $this->createUserData();
        $this->createSystemSettings();
        $this->createSampleData();
        
        echo "\n✓ 简化版本数据库设置完成!\n";
    }
    
    private function createUserData(): void
    {
        $userData = [
            'admin' => [
                'id' => 1,
                'username' => 'admin',
                'email' => 'admin@alingai.local',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ];
        
        file_put_contents(
            $this->dataDir . '/users.json',
            json_encode($userData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        
        echo "✓ 创建用户数据文件\n";
    }
    
    private function createSystemSettings(): void
    {
        $settings = [
            'app_name' => 'AlingAi Pro',
            'app_version' => '2.0.0',
            'max_chat_history' => 1000,
            'enable_registration' => true,
            'maintenance_mode' => false,
            'default_ai_model' => 'deepseek-chat',
            'system_initialized' => true,
            'last_updated' => date('Y-m-d H:i:s'),
        ];
        
        file_put_contents(
            $this->dataDir . '/settings.json',
            json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        
        echo "✓ 创建系统设置文件\n";
    }
    
    private function createSampleData(): void
    {
        // 聊天会话示例
        $chatSessions = [
            [
                'id' => 1,
                'user_id' => 1,
                'title' => '欢迎使用 AlingAi Pro',
                'model' => 'deepseek-chat',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ];
        
        file_put_contents(
            $this->dataDir . '/chat_sessions.json',
            json_encode($chatSessions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        
        // 聊天消息示例
        $chatMessages = [
            [
                'id' => 1,
                'session_id' => 1,
                'user_id' => 1,
                'role' => 'system',
                'content' => '欢迎使用 AlingAi Pro！我是您的AI助手，可以帮助您解答问题、处理任务。',
                'model' => 'deepseek-chat',
                'created_at' => date('Y-m-d H:i:s'),
            ]
        ];
        
        file_put_contents(
            $this->dataDir . '/chat_messages.json',
            json_encode($chatMessages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        
        echo "✓ 创建示例数据文件\n";
    }
    
    public function getStatus(): void
    {
        echo "\n=== 文件系统数据库状态 ===\n";
        echo "存储目录: {$this->storageDir}\n";
        echo "数据目录: {$this->dataDir}\n";
        
        $dataFiles = glob($this->dataDir . '/*.json');
        echo "数据文件数量: " . count($dataFiles) . "\n";
        
        foreach ($dataFiles as $file) {
            $fileName = basename($file);
            $fileSize = $this->formatBytes(filesize($file));
            echo "  - {$fileName}: {$fileSize}\n";
        }
        
        echo "\n";
    }
    
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}

// 执行设置
try {
    $setup = new SimpleDatabaseSetup();
    $setup->setup();
    $setup->getStatus();
    
    echo "🎉 文件系统数据库设置完成！\n";
    echo "管理员账户: admin / admin123\n";
    echo "现在可以启动应用程序进行测试。\n";
    
} catch (Exception $e) {
    echo "✗ 设置失败: " . $e->getMessage() . "\n";
    exit(1);
}
