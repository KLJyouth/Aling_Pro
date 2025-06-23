<?php
/**
 * AlingAi Pro - Background Worker Process
 * 三完编译 (Three Complete Compilation) Worker System
 * 
 * Handles AI agent task processing, maintenance, and system monitoring
 * 
 * @package AlingAi\Pro
 * @version 3.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\AI\EnhancedAgentCoordinator;
use AlingAi\Services\DatabaseService;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Dotenv\Dotenv;

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

class AlingAiWorker
{
    private EnhancedAgentCoordinator $agentCoordinator;
    private DatabaseService $db;
    private Logger $logger;
    private bool $running = true;
    private int $processedTasks = 0;
    private float $startTime;
    
    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->initializeLogger();
        $this->initializeServices();
        $this->setupSignalHandlers();
        
        $this->logger->info('AlingAi Pro Worker started', [
            'pid' => getmypid(),
            'memory_limit' => ini_get('memory_limit'),
            'time_limit' => ini_get('max_execution_time')
        ]);
    }
    
    private function initializeLogger(): void
    {
        $this->logger = new Logger('AlingAiWorker');
        
        // Console output for systemd
        $this->logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
        
        // File logging
        $logPath = __DIR__ . '/storage/logs';
        if (!is_dir($logPath)) {
            mkdir($logPath, 0755, true);
        }
        
        $this->logger->pushHandler(new RotatingFileHandler(
            $logPath . '/worker.log',
            0,
            Logger::DEBUG
        ));
    }
    
    private function initializeServices(): void
    {
        try {
            $this->db = new DatabaseService();
            $this->agentCoordinator = new EnhancedAgentCoordinator($this->db);
            
            // Initialize agent system
            $this->agentCoordinator->initializeAgentSystem();
            
            $this->logger->info('Worker services initialized successfully');
            
        } catch (Exception $e) {
            $this->logger->error('Failed to initialize worker services: ' . $e->getMessage());
            exit(1);
        }
    }
    
    private function setupSignalHandlers(): void
    {
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'handleShutdown']);
            pcntl_signal(SIGINT, [$this, 'handleShutdown']);
            pcntl_signal(SIGHUP, [$this, 'handleReload']);
        }
    }
    
    public function handleShutdown(): void
    {
        $this->logger->info('Shutdown signal received, stopping worker gracefully...');
        $this->running = false;
    }
    
    public function handleReload(): void
    {
        $this->logger->info('Reload signal received, reloading configuration...');
        // Reload configuration if needed
    }
    
    public function run(): void
    {
        $this->logger->info('Worker main loop started');
        
        $lastMaintenanceTime = time();
        $lastHealthCheck = time();
        
        while ($this->running) {
            try {
                // Process pending tasks
                $this->processPendingTasks();
                
                // Perform maintenance tasks every 5 minutes
                if (time() - $lastMaintenanceTime > 300) {
                    $this->performMaintenance();
                    $lastMaintenanceTime = time();
                }
                
                // Health check every minute
                if (time() - $lastHealthCheck > 60) {
                    $this->performHealthCheck();
                    $lastHealthCheck = time();
                }
                
                // Process signals if available
                if (function_exists('pcntl_signal_dispatch')) {
                    pcntl_signal_dispatch();
                }
                
                // Short sleep to prevent CPU spinning
                usleep(100000); // 0.1 seconds
                
            } catch (Exception $e) {
                $this->logger->error('Worker error: ' . $e->getMessage(), [
                    'exception' => $e,
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Sleep longer on error to prevent rapid failure loops
                sleep(5);
            }
            
            // Check memory usage
            $memoryUsage = memory_get_usage(true);
            $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
            
            if ($memoryUsage > ($memoryLimit * 0.9)) {
                $this->logger->warning('High memory usage detected', [
                    'usage' => $memoryUsage,
                    'limit' => $memoryLimit,
                    'percentage' => round(($memoryUsage / $memoryLimit) * 100, 2)
                ]);
            }
        }
        
        $this->shutdown();
    }
    
    private function processPendingTasks(): void
    {
        try {
            // Get pending tasks from database
            $stmt = $this->db->getPdo()->prepare(
                "SELECT * FROM ai_tasks 
                 WHERE status = 'pending' 
                 ORDER BY priority DESC, created_at ASC 
                 LIMIT 10"
            );
            $stmt->execute();
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($tasks as $task) {
                try {
                    $this->processTask($task);
                    $this->processedTasks++;
                    
                } catch (Exception $e) {
                    $this->logger->error('Task processing failed', [
                        'task_id' => $task['task_id'],
                        'error' => $e->getMessage()
                    ]);
                    
                    // Update task status to failed
                    $this->updateTaskStatus($task['id'], 'failed', null, $e->getMessage());
                }
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error fetching pending tasks: ' . $e->getMessage());
        }
    }
    
    private function processTask(array $task): void
    {
        $this->logger->info('Processing task', [
            'task_id' => $task['task_id'],
            'type' => $task['task_type'],
            'agent_id' => $task['agent_id']
        ]);
        
        // Update task status to processing
        $this->updateTaskStatus($task['id'], 'processing');
        
        // Process the task through agent coordinator
        $taskData = json_decode($task['task_data'], true);
        $result = $this->agentCoordinator->processTask($task['task_type'], $taskData);
        
        // Update task with result
        $this->updateTaskStatus($task['id'], 'completed', $result);
        
        $this->logger->info('Task completed successfully', [
            'task_id' => $task['task_id']
        ]);
    }
    
    private function updateTaskStatus(int $taskId, string $status, ?array $result = null, ?string $errorMessage = null): void
    {
        $sql = "UPDATE ai_tasks SET status = ?, updated_at = NOW()";
        $params = [$status];
        
        if ($result !== null) {
            $sql .= ", result = ?";
            $params[] = json_encode($result);
        }
        
        if ($errorMessage !== null) {
            $sql .= ", error_message = ?";
            $params[] = $errorMessage;
        }
        
        if ($status === 'processing') {
            $sql .= ", started_at = NOW()";
        } elseif (in_array($status, ['completed', 'failed'])) {
            $sql .= ", completed_at = NOW()";
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $taskId;
        
        $stmt = $this->db->getPdo()->prepare($sql);
        $stmt->execute($params);
    }
    
    private function performMaintenance(): void
    {
        $this->logger->info('Performing maintenance tasks...');
        
        try {
            // Clean up old completed tasks (older than 7 days)
            $stmt = $this->db->getPdo()->prepare(
                "DELETE FROM ai_tasks 
                 WHERE status IN ('completed', 'failed') 
                 AND completed_at < DATE_SUB(NOW(), INTERVAL 7 DAY)"
            );
            $stmt->execute();
            $deletedTasks = $stmt->rowCount();
            
            if ($deletedTasks > 0) {
                $this->logger->info("Cleaned up {$deletedTasks} old tasks");
            }
            
            // Clean up old conversation history (older than 30 days)
            $stmt = $this->db->getPdo()->prepare(
                "DELETE FROM conversation_history 
                 WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            $stmt->execute();
            $deletedConversations = $stmt->rowCount();
            
            if ($deletedConversations > 0) {
                $this->logger->info("Cleaned up {$deletedConversations} old conversations");
            }
            
            // Update agent heartbeats
            $this->agentCoordinator->updateAgentHeartbeats();
            
            $this->logger->info('Maintenance tasks completed');
            
        } catch (Exception $e) {
            $this->logger->error('Maintenance error: ' . $e->getMessage());
        }
    }
    
    private function performHealthCheck(): void
    {
        try {
            // Check database connection
            $this->db->getPdo()->query('SELECT 1');
            
            // Check agent system status
            $activeAgents = $this->agentCoordinator->getActiveAgentCount();
            
            // Log health status
            $uptime = time() - (int)$this->startTime;
            $this->logger->debug('Health check passed', [
                'uptime_seconds' => $uptime,
                'processed_tasks' => $this->processedTasks,
                'active_agents' => $activeAgents,
                'memory_usage' => memory_get_usage(true),
                'peak_memory' => memory_get_peak_usage(true)
            ]);
            
        } catch (Exception $e) {
            $this->logger->error('Health check failed: ' . $e->getMessage());
        }
    }
    
    private function parseMemoryLimit(string $memoryLimit): int
    {
        if ($memoryLimit === '-1') {
            return PHP_INT_MAX;
        }
        
        $value = (int) $memoryLimit;
        $unit = strtolower(substr($memoryLimit, -1));
        
        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return $value;
        }
    }
    
    private function shutdown(): void
    {
        $runtime = microtime(true) - $this->startTime;
        
        $this->logger->info('Worker shutting down', [
            'runtime_seconds' => round($runtime, 2),
            'processed_tasks' => $this->processedTasks,
            'average_tasks_per_minute' => round(($this->processedTasks / ($runtime / 60)), 2)
        ]);
        
        // Perform final cleanup
        try {
            $this->agentCoordinator->shutdownAgentSystem();
        } catch (Exception $e) {
            $this->logger->error('Error during shutdown: ' . $e->getMessage());
        }
        
        $this->logger->info('Worker shutdown complete');
    }
}

// Create and run worker
try {
    $worker = new AlingAiWorker();
    $worker->run();
} catch (Exception $e) {
    error_log('Worker failed to start: ' . $e->getMessage());
    exit(1);
}
