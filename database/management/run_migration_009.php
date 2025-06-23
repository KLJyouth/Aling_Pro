<?php
require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Services\DatabaseService;

try {
    $db = new DatabaseService();
    $sql = file_get_contents(__DIR__ . '/database/migrations/009_create_enhanced_agent_tables.sql');
    
    // 分割SQL语句
    $statements = preg_split('/;\s*$/m', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $db->execute($statement);
                echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
            } catch (Exception $e) {
                echo "✗ Error: " . $e->getMessage() . "\n";
                echo "Statement: " . substr($statement, 0, 100) . "...\n";
            }
        }
    }
    
    echo "\n✓ Migration 009 completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
}
