<?php
/**
 * AlingAi Pro 5.0 - 基于文件存储的Admin系统数据迁移�?
 * 用于演示和测试环境，不依赖数据库驱动
 */

class FileStorageAdminMigrator
{
    private $storageDir;
    private $dataStructure;
    
    public function __construct() {
        $this->storageDir = __DIR__ . '/../../../storage/admin';
        $this->initializeStorage(];
        $this->defineDataStructure(];
    }
    
    /**
     * 初始化存储目�?
     */
    private function initializeStorage() {
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true];
        }
        
        // 创建子目�?
        $subDirs = ['users', 'third-party', 'monitoring', 'risk-control', 'emails', 'chats', 'tokens', 'logs'];
        foreach ($subDirs as $dir) {
            $path = $this->storageDir . '/' . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true];
            }
        }
    }
    
    /**
     * 定义数据结构
     */
    private function defineDataStructure() {
        $this->dataStructure = [
            'admin_users' => [
                'id' => 'auto_increment',
                'username' => 'string',
                'email' => 'string',
                'password_hash' => 'string',
                'role' => 'string',
                'permissions' => 'json',
                'last_login' => 'datetime',
                'created_at' => 'datetime',
                'updated_at' => 'datetime',
                'status' => 'string'
            ], 
            'admin_permissions' => [
                'id' => 'auto_increment',
                'name' => 'string',
                'description' => 'string',
                'resource' => 'string',
                'action' => 'string',
                'created_at' => 'datetime'
            ], 
            'admin_roles' => [
                'id' => 'auto_increment',
                'name' => 'string',
                'description' => 'string',
                'permissions' => 'json',
                'created_at' => 'datetime'
            ], 
            'admin_user_sessions' => [
                'id' => 'auto_increment',
                'user_id' => 'integer',
                'session_token' => 'string',
                'ip_address' => 'string',
                'user_agent' => 'string',
                'created_at' => 'datetime',
                'expires_at' => 'datetime',
                'is_active' => 'boolean'
            ], 
            'admin_third_party_services' => [
                'id' => 'auto_increment',
                'service_name' => 'string',
                'service_type' => 'string',
                'api_key' => 'string',
                'api_secret' => 'string',
                'endpoint_url' => 'string',
                'configuration' => 'json',
                'status' => 'string',
                'last_check' => 'datetime',
                'created_at' => 'datetime',
                'updated_at' => 'datetime'
            ], 
            'admin_system_monitoring' => [
                'id' => 'auto_increment',
                'metric_name' => 'string',
                'metric_value' => 'string',
                'metric_type' => 'string',
                'category' => 'string',
                'timestamp' => 'datetime',
                'metadata' => 'json'
            ], 
            'admin_risk_events' => [
                'id' => 'auto_increment',
                'event_type' => 'string',
                'risk_level' => 'string',
                'source_ip' => 'string',
                'user_id' => 'integer',
                'description' => 'text',
                'metadata' => 'json',
                'status' => 'string',
                'created_at' => 'datetime',
                'resolved_at' => 'datetime'
            ], 
            'admin_email_logs' => [
                'id' => 'auto_increment',
                'recipient' => 'string',
                'subject' => 'string',
                'template' => 'string',
                'status' => 'string',
                'error_message' => 'text',
                'sent_at' => 'datetime',
                'metadata' => 'json'
            ], 
            'admin_chat_monitoring' => [
                'id' => 'auto_increment',
                'user_id' => 'integer',
                'message_id' => 'string',
                'message_content' => 'text',
                'risk_level' => 'string',
                'risk_score' => 'float',
                'flags' => 'json',
                'reviewed' => 'boolean',
                'reviewed_by' => 'integer',
                'reviewed_at' => 'datetime',
                'created_at' => 'datetime'
            ]
        ];
    }
    
    /**
     * 执行迁移
     */
    public function migrate() {
        echo "🚀 Starting File Storage Admin System Migration...\n";
        echo "====================================================\n";
        
        $results = [];
        
        foreach ($this->dataStructure as $tableName => $structure) {
            echo "📋 Creating structure for: {$tableName}... ";
            
            try {
                $this->createTableStructure($tableName, $structure];
                echo "�?Success\n";
                $results[$tableName] = ['status' => 'success', 'message' => 'Structure created'];
            } catch (Exception $e) {
                echo "�?Failed: {$e->getMessage()}\n";
                $results[$tableName] = ['status' => 'failed', 'message' => $e->getMessage()];
            }
        }
        
        // 创建示例数据
        echo "\n📝 Creating sample data...\n";
        $this->createSampleData(];
        
        // 创建配置文件
        echo "⚙️  Creating configuration...\n";
        $this->createConfiguration(];
        
        echo "\n�?File Storage Migration completed!\n";
        echo "📁 Storage location: {$this->storageDir}\n";
        
        return $results;
    }
    
    /**
     * 创建表结构文�?
     */
    private function createTableStructure($tableName, $structure) {
        $structureFile = $this->storageDir . "/structure_{$tableName}.json";
        file_put_contents($structureFile, json_encode($structure, JSON_PRETTY_PRINT)];
        
        // 创建数据文件
        $dataFile = $this->storageDir . "/{$tableName}.json";
        if (!file_exists($dataFile)) {
            file_put_contents($dataFile, json_encode([],  JSON_PRETTY_PRINT)];
        }
        
        // 创建索引文件
        $indexFile = $this->storageDir . "/index_{$tableName}.json";
        if (!file_exists($indexFile)) {
            file_put_contents($indexFile, json_encode(['next_id' => 1, 'count' => 0],  JSON_PRETTY_PRINT)];
        }
    }
    
    /**
     * 创建示例数据
     */
    private function createSampleData() {
        // 创建默认管理员用�?
        $adminUsers = [
            [
                'id' => 1,
                'username' => 'admin',
                'email' => 'admin@alingai.pro',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT],
                'role' => 'super_admin',
                'permissions' => json_encode(['*']],
                'last_login' => null,
                'created_at' => date('Y-m-d H:i:s'],
                'updated_at' => date('Y-m-d H:i:s'],
                'status' => 'active'
            ], 
            [
                'id' => 2,
                'username' => 'manager',
                'email' => 'manager@alingai.pro',
                'password_hash' => password_hash('manager123', PASSWORD_DEFAULT],
                'role' => 'admin',
                'permissions' => json_encode(['users.read', 'users.write', 'monitoring.read']],
                'last_login' => null,
                'created_at' => date('Y-m-d H:i:s'],
                'updated_at' => date('Y-m-d H:i:s'],
                'status' => 'active'
            ]
        ];
        
        file_put_contents($this->storageDir . '/admin_users.json', json_encode($adminUsers, JSON_PRETTY_PRINT)];
        file_put_contents($this->storageDir . '/index_admin_users.json', json_encode(['next_id' => 3, 'count' => 2],  JSON_PRETTY_PRINT)];
        
        // 创建默认权限
        $permissions = [
            ['id' => 1, 'name' => 'users.read', 'description' => '用户查看权限', 'resource' => 'users', 'action' => 'read', 'created_at' => date('Y-m-d H:i:s')], 
            ['id' => 2, 'name' => 'users.write', 'description' => '用户管理权限', 'resource' => 'users', 'action' => 'write', 'created_at' => date('Y-m-d H:i:s')], 
            ['id' => 3, 'name' => 'monitoring.read', 'description' => '监控查看权限', 'resource' => 'monitoring', 'action' => 'read', 'created_at' => date('Y-m-d H:i:s')], 
            ['id' => 4, 'name' => 'system.admin', 'description' => '系统管理权限', 'resource' => 'system', 'action' => 'admin', 'created_at' => date('Y-m-d H:i:s')]
        ];
        
        file_put_contents($this->storageDir . '/admin_permissions.json', json_encode($permissions, JSON_PRETTY_PRINT)];
        file_put_contents($this->storageDir . '/index_admin_permissions.json', json_encode(['next_id' => 5, 'count' => 4],  JSON_PRETTY_PRINT)];
        
        // 创建默认角色
        $roles = [
            ['id' => 1, 'name' => 'super_admin', 'description' => '超级管理�?, 'permissions' => json_encode(['*']], 'created_at' => date('Y-m-d H:i:s')], 
            ['id' => 2, 'name' => 'admin', 'description' => '管理�?, 'permissions' => json_encode(['users.read', 'users.write', 'monitoring.read']], 'created_at' => date('Y-m-d H:i:s')], 
            ['id' => 3, 'name' => 'viewer', 'description' => '只读用户', 'permissions' => json_encode(['users.read', 'monitoring.read']], 'created_at' => date('Y-m-d H:i:s')]
        ];
        
        file_put_contents($this->storageDir . '/admin_roles.json', json_encode($roles, JSON_PRETTY_PRINT)];
        file_put_contents($this->storageDir . '/index_admin_roles.json', json_encode(['next_id' => 4, 'count' => 3],  JSON_PRETTY_PRINT)];
        
        echo "   📝 Sample admin users created\n";
        echo "   🔐 Sample permissions created\n";
        echo "   👥 Sample roles created\n";
    }
    
    /**
     * 创建配置文件
     */
    private function createConfiguration() {
        $config = [
            'storage_type' => 'file',
            'storage_path' => $this->storageDir,
            'encryption_key' => bin2hex(random_bytes(32)],
            'session_lifetime' => 3600,
            'max_login_attempts' => 5,
            'password_policy' => [
                'min_length' => 8,
                'require_uppercase' => true,
                'require_lowercase' => true,
                'require_numbers' => true,
                'require_symbols' => false
            ], 
            'api_rate_limits' => [
                'default' => 100,
                'auth' => 10,
                'upload' => 5
            ], 
            'monitoring' => [
                'enabled' => true,
                'retention_days' => 30,
                'alert_thresholds' => [
                    'error_rate' => 5,
                    'response_time' => 2000,
                    'memory_usage' => 80
                ]
            ], 
            'created_at' => date('Y-m-d H:i:s'],
            'version' => '1.0.0'
        ];
        
        file_put_contents($this->storageDir . '/admin_config.json', json_encode($config, JSON_PRETTY_PRINT)];
        
        echo "   ⚙️  Configuration file created\n";
    }
    
    /**
     * 验证迁移结果
     */
    public function verify() {
        echo "\n🔍 Verifying migration...\n";
        
        $tables = array_keys($this->dataStructure];
        $allValid = true;
        
        foreach ($tables as $table) {
            $dataFile = $this->storageDir . "/{$table}.json";
            $structureFile = $this->storageDir . "/structure_{$table}.json";
            $indexFile = $this->storageDir . "/index_{$table}.json";
            
            if (file_exists($dataFile) && file_exists($structureFile) && file_exists($indexFile)) {
                echo "   �?{$table} - OK\n";
            } else {
                echo "   �?{$table} - Missing files\n";
                $allValid = false;
            }
        }
        
        // 检查配置文�?
        $configFile = $this->storageDir . '/admin_config.json';
        if (file_exists($configFile)) {
            echo "   �?Configuration - OK\n";
        } else {
            echo "   �?Configuration - Missing\n";
            $allValid = false;
        }
        
        if ($allValid) {
            echo "\n🎉 Migration verification passed!\n";
        } else {
            echo "\n�?Migration verification failed!\n";
        }
        
        return $allValid;
    }
}

// 执行迁移
echo "PHP SAPI: " . php_sapi_name() . "\n";

if (php_sapi_name() === 'cli') {
    try {
        $migrator = new FileStorageAdminMigrator(];
        $results = $migrator->migrate(];
        $migrator->verify(];
        
        echo "\n📊 Migration Summary:\n";
        foreach ($results as $table => $result) {
            $status = $result['status'] === 'success' ? '�? : '�?;
            echo "   {$status} {$table}: {$result['message']}\n";
        }
        
        echo "\n🔐 Default Admin Credentials:\n";
        echo "   Username: admin\n";
        echo "   Password: admin123\n";
        echo "   Email: admin@alingai.pro\n";
        
        echo "\n📝 Manager Credentials:\n";
        echo "   Username: manager\n";
        echo "   Password: manager123\n";
        echo "   Email: manager@alingai.pro\n";
        
    } catch (Exception $e) {
        echo "�?Migration failed: {$e->getMessage()}\n";
        exit(1];
    }
} else {
    // Web interface
    header('Content-Type: application/json'];
    try {
        $migrator = new FileStorageAdminMigrator(];
        $results = $migrator->migrate(];
        $verified = $migrator->verify(];
        
        echo json_encode([
            'success' => true,
            'message' => 'Migration completed successfully',
            'results' => $results,
            'verified' => $verified,
            'credentials' => [
                'admin' => ['username' => 'admin', 'password' => 'admin123'], 
                'manager' => ['username' => 'manager', 'password' => 'manager123']
            ]
        ],  JSON_PRETTY_PRINT];
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ],  JSON_PRETTY_PRINT];
    }
}
