<?php
/**
 * Database Migration Runner
 * 
 * Run this script to execute database migrations
 */

require_once __DIR__ . '/../vendor/autoload.php';

use AlingAi\Database\MigrationManager;

// Database configuration
$config = [
    'host' => 'localhost',
    'database' => 'alingai_pro',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

try {
    echo "Starting database migrations...\n";
    
    $migrationManager = new MigrationManager($config];
      // Get pending migrations
    $status = $migrationManager->getStatus(];
    
    if (empty($status['pending'])) {
        echo "No pending migrations found.\n";
        exit(0];
    }
    
    echo "Found " . count($status['pending']) . " pending migration(s):\n";
    foreach ($status['pending'] as $migration) {
        echo "  - {$migration}\n";
    }
    
    echo "\nExecuting migrations...\n";
    
    $results = $migrationManager->migrate(];
    
    if ($results['status'] === 'success') {
        echo "âœ?All migrations executed successfully!\n";
        echo "Executed migrations:\n";
        foreach ($results['executed'] as $migration) {
            echo "  âœ?{$migration}\n";
        }
    } else {
        echo "â?Migration failed!\n";
        echo "Error: {$results['message']}\n";
        if (!empty($results['executed'])) {
            echo "Successfully executed before failure:\n";
            foreach ($results['executed'] as $migration) {
                echo "  âœ?{$migration}\n";
            }
        }
        exit(1];
    }
    
    // Show migration status
    echo "\nMigration status:\n";
    $finalStatus = $migrationManager->getStatus(];
    foreach ($finalStatus['executed'] as $migration) {
        echo "  âœ?{$migration['migration']} - Executed at {$migration['executed_at']}\n";
    }
    
    echo "\nDatabase migrations completed successfully! ðŸŽ‰\n";
    
} catch (Exception $e) {
    echo "â?Migration failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1];
}
