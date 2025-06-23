<?php
/**
 * AlingAi Pro - Database Migration Runner
 * 三完编译 (Three Complete Compilation) Database Setup
 * 
 * Ensures all required database tables are created for the enhanced system
 * 
 * @package AlingAi\Pro
 * @version 3.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Services\DatabaseService;
use Dotenv\Dotenv;

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

class DatabaseMigrationRunner
{
    private DatabaseService $db;    private array $migrationFiles = [
        '001_create_users_table.sql',
        '002_create_ai_content_tables.sql', 
        '003_create_system_tables.sql',
        '004_create_enhanced_tables.sql',
        '005_create_enhanced_user_management.sql',
        '008_create_ai_agent_system_tables_fixed_v2.sql',
        '009_create_conversation_history_table_v2.sql'
    ];
    
    public function __construct()
    {
        $this->db = new DatabaseService();
    }
    
    public function runMigrations(): void
    {
        echo "🚀 Starting AlingAi Pro Database Migration...\n";
        echo "====================================================\n";
        
        $migrationPath = __DIR__ . '/database/migrations/';
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($this->migrationFiles as $filename) {
            $filePath = $migrationPath . $filename;
            
            if (!file_exists($filePath)) {
                echo "⚠️  Migration file not found: {$filename}\n";
                continue;
            }
            
            echo "📋 Running migration: {$filename}... ";
            
            try {
                $sql = file_get_contents($filePath);
                
                // Split by semicolons and execute each statement
                $statements = array_filter(
                    array_map('trim', explode(';', $sql)),
                    function($stmt) {
                        return !empty($stmt) && !str_starts_with($stmt, '--');
                    }
                );
                
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        $this->db->getPdo()->exec($statement);
                    }
                }
                
                echo "✅ SUCCESS\n";
                $successCount++;
                
            } catch (Exception $e) {
                echo "❌ FAILED: " . $e->getMessage() . "\n";
                $errorCount++;
            }
        }
        
        echo "====================================================\n";
        echo "📊 Migration Summary:\n";
        echo "   ✅ Successful: {$successCount}\n";
        echo "   ❌ Failed: {$errorCount}\n";
        echo "   📁 Total: " . count($this->migrationFiles) . "\n";
        
        if ($errorCount === 0) {
            echo "🎉 All database migrations completed successfully!\n";
            $this->verifyTables();
        } else {
            echo "⚠️  Some migrations failed. Please check the errors above.\n";
            exit(1);
        }
    }
    
    private function verifyTables(): void
    {
        echo "\n🔍 Verifying required tables...\n";        $requiredTables = [
            'users',
            'chat_sessions',
            'chat_messages',
            'ai_conversations',
            'agents',
            'system_settings',
            'user_settings',
            'api_keys',
            'access_logs',
            'system_logs',
            'performance_metrics',
            'security_scans',
            'migrations'
        ];
        
        $existingTables = [];
        $stmt = $this->db->getPdo()->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $existingTables[] = $row[0];
        }
        
        $missingTables = array_diff($requiredTables, $existingTables);
        
        if (empty($missingTables)) {
            echo "✅ All required tables are present!\n";
            
            // Show table statistics
            echo "\n📊 Table Statistics:\n";
            foreach ($requiredTables as $table) {
                try {
                    $stmt = $this->db->getPdo()->query("SELECT COUNT(*) FROM `{$table}`");
                    $count = $stmt->fetchColumn();
                    echo "   📋 {$table}: {$count} records\n";
                } catch (Exception $e) {
                    echo "   ⚠️  {$table}: Error reading count\n";
                }
            }
            
        } else {
            echo "❌ Missing tables: " . implode(', ', $missingTables) . "\n";
            exit(1);
        }
    }
    
    public function createSystemConfigs(): void
    {
        echo "\n⚙️  Creating default system configurations...\n";
        
        $defaultConfigs = [
            // Application settings
            ['app_name', 'AlingAi Pro Enhanced', 'string', 'application', 'Application name', 1, 1],
            ['app_version', '3.0.0', 'string', 'application', 'Application version', 1, 2],
            ['app_environment', 'production', 'string', 'application', 'Application environment', 0, 3],
            
            // AI Agent settings
            ['agent_max_concurrent', '10', 'integer', 'ai_agents', 'Maximum concurrent agents', 0, 10],
            ['agent_timeout_seconds', '300', 'integer', 'ai_agents', 'Agent task timeout in seconds', 0, 11],
            ['deepseek_api_enabled', '1', 'boolean', 'ai_agents', 'Enable DeepSeek API integration', 0, 12],
            
            // Threat visualization
            ['threat_visualization_enabled', '1', 'boolean', 'threat_intel', 'Enable 3D threat visualization', 1, 20],
            ['threat_refresh_interval', '30', 'integer', 'threat_intel', 'Threat data refresh interval (seconds)', 0, 21],
            
            // Security settings
            ['security_max_login_attempts', '5', 'integer', 'security', 'Maximum login attempts before lockout', 0, 30],
            ['security_session_timeout', '3600', 'integer', 'security', 'Session timeout in seconds', 0, 31],
            
            // Performance settings
            ['cache_enabled', '1', 'boolean', 'performance', 'Enable application caching', 0, 40],
            ['cache_ttl', '3600', 'integer', 'performance', 'Default cache TTL in seconds', 0, 41],
        ];
        
        try {
            $stmt = $this->db->getPdo()->prepare(
                "INSERT IGNORE INTO system_configs (config_key, config_value, config_type, group_name, description, is_public, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            
            $insertedCount = 0;
            foreach ($defaultConfigs as $config) {
                $result = $stmt->execute($config);
                if ($result && $stmt->rowCount() > 0) {
                    $insertedCount++;
                }
            }
            
            echo "✅ Created {$insertedCount} default system configurations\n";
            
        } catch (Exception $e) {
            echo "❌ Error creating system configurations: " . $e->getMessage() . "\n";
        }
    }
}

// Run migrations
try {
    $runner = new DatabaseMigrationRunner();
    $runner->runMigrations();
    $runner->createSystemConfigs();
    
    echo "\n🎉 AlingAi Pro Enhanced Database Setup Complete!\n";
    echo "🚀 The system is now ready for the Three Complete Compilation architecture.\n";
    
} catch (Exception $e) {
    echo "💥 Migration failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
