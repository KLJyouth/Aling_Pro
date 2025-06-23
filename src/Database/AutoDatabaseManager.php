<?php

namespace AlingAi\Database;

use PDO;
use Exception;
use AlingAi\Core\Logger;

/**
 * 数据库自动管理系统
 * 实现智能数据库分类、优化和自动化管理
 */
class AutoDatabaseManager
{
    private $connections = [];
    private $config;
    private $logger;
    private $analysisResults = [];
    
    // 数据库类型配置
    private $dbTypes = [
        'mysql' => [
            'driver' => 'mysql',
            'port' => 3306,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci'
        ],
        'postgresql' => [
            'driver' => 'pgsql',
            'port' => 5432,
            'charset' => 'utf8'
        ],
        'sqlite' => [
            'driver' => 'sqlite',
            'file' => true
        ]
    ];
    
    // 表分类规则
    private $tableCategories = [
        'user_management' => ['users', 'user_profiles', 'user_sessions', 'user_permissions'],
        'content_management' => ['posts', 'articles', 'pages', 'media', 'comments'],
        'system_logs' => ['logs', 'audit_logs', 'error_logs', 'access_logs'],
        'analytics' => ['analytics', 'statistics', 'reports', 'metrics'],
        'cache' => ['cache', 'sessions', 'temp_data'],
        'configuration' => ['settings', 'config', 'options', 'preferences']
    ];

    public function __construct($config = [])
    {
        $this->config = array_merge([
            'auto_optimize' => true,
            'analysis_interval' => 3600, // 1小时
            'backup_retention' => 30, // 30天
            'performance_threshold' => 0.5, // 500ms
            'storage_warning_threshold' => 80, // 80%
        ], $config);
        
        $this->logger = new Logger('AutoDatabaseManager');
    }

    /**
     * 初始化数据库连接
     */
    public function initializeConnections($databases)
    {
        foreach ($databases as $name => $config) {
            try {
                $this->connections[$name] = $this->createConnection($config);
                $this->logger->info("数据库连接已建立: {$name}");
            } catch (Exception $e) {
                $this->logger->error("数据库连接失败: {$name} - " . $e->getMessage());
            }
        }
    }

    /**
     * 创建数据库连接
     */
    private function createConnection($config)
    {
        $dsn = $this->buildDsn($config);
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_TIMEOUT => 30
        ];

        return new PDO($dsn, $config['username'], $config['password'], $options);
    }

    /**
     * 构建DSN字符串
     */
    private function buildDsn($config)
    {
        $driver = $config['driver'] ?? 'mysql';
        
        switch ($driver) {
            case 'mysql':
                return "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
            case 'pgsql':
                return "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
            case 'sqlite':
                return "sqlite:{$config['database']}";
            default:
                throw new Exception("不支持的数据库驱动: {$driver}");
        }
    }

    /**
     * 自动分析所有数据库
     */
    public function analyzeAllDatabases()
    {
        $this->logger->info("开始数据库自动分析");
        
        foreach ($this->connections as $name => $connection) {
            $this->analysisResults[$name] = $this->analyzeDatabaseStructure($connection, $name);
        }
        
        $this->generateAnalysisReport();
        return $this->analysisResults;
    }

    /**
     * 分析数据库结构
     */
    private function analyzeDatabaseStructure($connection, $dbName)
    {
        $analysis = [
            'database_name' => $dbName,
            'tables' => [],
            'indexes' => [],
            'performance_issues' => [],
            'optimization_suggestions' => [],
            'storage_info' => [],
            'categorization' => []
        ];

        try {
            // 获取所有表信息
            $tables = $this->getTables($connection);
            
            foreach ($tables as $table) {
                $tableAnalysis = $this->analyzeTable($connection, $table);
                $analysis['tables'][$table] = $tableAnalysis;
                
                // 分类表
                $category = $this->categorizeTable($table);
                $analysis['categorization'][$category][] = $table;
                
                // 检查性能问题
                $performanceIssues = $this->checkTablePerformance($connection, $table);
                if (!empty($performanceIssues)) {
                    $analysis['performance_issues'][$table] = $performanceIssues;
                }
            }
            
            // 获取索引信息
            $analysis['indexes'] = $this->analyzeIndexes($connection);
            
            // 存储空间分析
            $analysis['storage_info'] = $this->analyzeStorage($connection);
            
            // 生成优化建议
            $analysis['optimization_suggestions'] = $this->generateOptimizationSuggestions($analysis);
            
        } catch (Exception $e) {
            $this->logger->error("数据库分析失败: {$dbName} - " . $e->getMessage());
            $analysis['error'] = $e->getMessage();
        }

        return $analysis;
    }

    /**
     * 获取所有表名
     */
    private function getTables($connection)
    {
        $stmt = $connection->query("SHOW TABLES");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * 分析单个表
     */
    private function analyzeTable($connection, $tableName)
    {
        $analysis = [
            'name' => $tableName,
            'columns' => [],
            'row_count' => 0,
            'size_mb' => 0,
            'indexes' => [],
            'foreign_keys' => [],
            'issues' => []
        ];

        try {
            // 获取表结构
            $stmt = $connection->query("DESCRIBE {$tableName}");
            $analysis['columns'] = $stmt->fetchAll();
            
            // 获取行数
            $stmt = $connection->query("SELECT COUNT(*) FROM {$tableName}");
            $analysis['row_count'] = $stmt->fetchColumn();
            
            // 获取表大小
            $stmt = $connection->query("
                SELECT 
                    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS size_mb
                FROM information_schema.TABLES 
                WHERE TABLE_NAME = '{$tableName}'
            ");
            $result = $stmt->fetch();
            $analysis['size_mb'] = $result['size_mb'] ?? 0;
            
            // 获取索引信息
            $stmt = $connection->query("SHOW INDEX FROM {$tableName}");
            $analysis['indexes'] = $stmt->fetchAll();
            
            // 检查表问题
            $analysis['issues'] = $this->checkTableIssues($connection, $tableName, $analysis);
            
        } catch (Exception $e) {
            $this->logger->warning("表分析失败: {$tableName} - " . $e->getMessage());
            $analysis['error'] = $e->getMessage();
        }

        return $analysis;
    }

    /**
     * 表分类
     */
    private function categorizeTable($tableName)
    {
        $tableName = strtolower($tableName);
        
        foreach ($this->tableCategories as $category => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($tableName, $pattern) !== false) {
                    return $category;
                }
            }
        }
        
        return 'other';
    }

    /**
     * 检查表性能问题
     */
    private function checkTablePerformance($connection, $tableName)
    {
        $issues = [];
        
        try {
            // 检查慢查询
            $stmt = $connection->query("
                SELECT COUNT(*) as slow_queries 
                FROM information_schema.PROCESSLIST 
                WHERE DB = DATABASE() AND COMMAND = 'Query' AND TIME > 1
            ");
            $result = $stmt->fetch();
            
            if ($result['slow_queries'] > 0) {
                $issues[] = [
                    'type' => 'slow_queries',
                    'description' => "发现 {$result['slow_queries']} 个慢查询",
                    'severity' => 'warning'
                ];
            }
            
            // 检查缺失索引
            $missingIndexes = $this->checkMissingIndexes($connection, $tableName);
            if (!empty($missingIndexes)) {
                $issues[] = [
                    'type' => 'missing_indexes',
                    'description' => '缺少推荐索引',
                    'details' => $missingIndexes,
                    'severity' => 'warning'
                ];
            }
            
        } catch (Exception $e) {
            $this->logger->warning("性能检查失败: {$tableName} - " . $e->getMessage());
        }
        
        return $issues;
    }

    /**
     * 检查缺失的索引
     */
    private function checkMissingIndexes($connection, $tableName)
    {
        $suggestions = [];
        
        try {
            // 检查外键列是否有索引
            $stmt = $connection->query("
                SELECT COLUMN_NAME 
                FROM information_schema.COLUMNS 
                WHERE TABLE_NAME = '{$tableName}' 
                AND COLUMN_NAME LIKE '%_id'
            ");
            $foreignKeyColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // 检查现有索引
            $stmt = $connection->query("SHOW INDEX FROM {$tableName}");
            $existingIndexes = array_column($stmt->fetchAll(), 'Column_name');
            
            // 找出缺失的索引
            foreach ($foreignKeyColumns as $column) {
                if (!in_array($column, $existingIndexes)) {
                    $suggestions[] = "建议为 {$column} 列添加索引";
                }
            }
            
        } catch (Exception $e) {
            $this->logger->warning("索引检查失败: {$tableName} - " . $e->getMessage());
        }
        
        return $suggestions;
    }

    /**
     * 检查表问题
     */
    private function checkTableIssues($connection, $tableName, $analysis)
    {
        $issues = [];
        
        // 检查大表
        if ($analysis['row_count'] > 1000000) {
            $issues[] = [
                'type' => 'large_table',
                'description' => '表数据量过大，建议考虑分区或分表',
                'severity' => 'warning'
            ];
        }
        
        // 检查无主键表
        $hasPrimaryKey = false;
        foreach ($analysis['columns'] as $column) {
            if ($column['Key'] === 'PRI') {
                $hasPrimaryKey = true;
                break;
            }
        }
        
        if (!$hasPrimaryKey) {
            $issues[] = [
                'type' => 'no_primary_key',
                'description' => '表缺少主键，影响复制和性能',
                'severity' => 'error'
            ];
        }
        
        // 检查字符集问题
        // 这里可以添加更多检查逻辑
        
        return $issues;
    }

    /**
     * 分析索引
     */
    private function analyzeIndexes($connection)
    {
        $indexAnalysis = [];
        
        try {
            $stmt = $connection->query("
                SELECT 
                    TABLE_NAME,
                    INDEX_NAME,
                    COLUMN_NAME,
                    CARDINALITY,
                    INDEX_TYPE
                FROM information_schema.STATISTICS 
                WHERE TABLE_SCHEMA = DATABASE()
                ORDER BY TABLE_NAME, INDEX_NAME
            ");
            
            $indexes = $stmt->fetchAll();
            
            foreach ($indexes as $index) {
                $tableName = $index['TABLE_NAME'];
                $indexName = $index['INDEX_NAME'];
                
                if (!isset($indexAnalysis[$tableName])) {
                    $indexAnalysis[$tableName] = [];
                }
                
                if (!isset($indexAnalysis[$tableName][$indexName])) {
                    $indexAnalysis[$tableName][$indexName] = [
                        'columns' => [],
                        'cardinality' => 0,
                        'type' => $index['INDEX_TYPE']
                    ];
                }
                
                $indexAnalysis[$tableName][$indexName]['columns'][] = $index['COLUMN_NAME'];
                $indexAnalysis[$tableName][$indexName]['cardinality'] += $index['CARDINALITY'];
            }
            
        } catch (Exception $e) {
            $this->logger->warning("索引分析失败: " . $e->getMessage());
        }
        
        return $indexAnalysis;
    }

    /**
     * 分析存储空间
     */
    private function analyzeStorage($connection)
    {
        $storageInfo = [];
        
        try {
            $stmt = $connection->query("
                SELECT 
                    TABLE_NAME,
                    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS size_mb,
                    ROUND((DATA_LENGTH / 1024 / 1024), 2) AS data_mb,
                    ROUND((INDEX_LENGTH / 1024 / 1024), 2) AS index_mb,
                    TABLE_ROWS
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = DATABASE()
                ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC
            ");
            
            $storageInfo['tables'] = $stmt->fetchAll();
            
            // 计算总大小
            $totalSize = array_sum(array_column($storageInfo['tables'], 'size_mb'));
            $storageInfo['total_size_mb'] = $totalSize;
            
            // 检查存储告警
            if ($totalSize > 1000) { // 1GB
                $storageInfo['warnings'][] = '数据库大小超过1GB，建议关注存储空间';
            }
            
        } catch (Exception $e) {
            $this->logger->warning("存储分析失败: " . $e->getMessage());
        }
        
        return $storageInfo;
    }

    /**
     * 生成优化建议
     */
    private function generateOptimizationSuggestions($analysis)
    {
        $suggestions = [];
        
        // 基于表分析生成建议
        foreach ($analysis['tables'] as $tableName => $tableInfo) {
            if (!empty($tableInfo['issues'])) {
                foreach ($tableInfo['issues'] as $issue) {
                    $suggestions[] = [
                        'table' => $tableName,
                        'type' => $issue['type'],
                        'suggestion' => $this->getOptimizationSuggestion($issue['type']),
                        'priority' => $issue['severity']
                    ];
                }
            }
        }
        
        // 基于存储分析生成建议
        if (isset($analysis['storage_info']['total_size_mb']) && 
            $analysis['storage_info']['total_size_mb'] > 500) {
            $suggestions[] = [
                'type' => 'storage_optimization',
                'suggestion' => '考虑实施数据归档策略，清理历史数据',
                'priority' => 'medium'
            ];
        }
        
        return $suggestions;
    }

    /**
     * 获取优化建议文本
     */
    private function getOptimizationSuggestion($issueType)
    {
        $suggestions = [
            'large_table' => '考虑分区、分表或数据归档',
            'no_primary_key' => '添加主键以提高性能和复制效率',
            'missing_indexes' => '为频繁查询的列添加适当索引',
            'slow_queries' => '优化查询语句，添加必要索引',
            'storage_optimization' => '清理不必要数据，实施归档策略'
        ];
        
        return $suggestions[$issueType] ?? '需要进一步分析';
    }

    /**
     * 自动优化数据库
     */
    public function autoOptimize($dbName = null)
    {
        $this->logger->info("开始自动优化");
        
        $databases = $dbName ? [$dbName => $this->connections[$dbName]] : $this->connections;
        
        foreach ($databases as $name => $connection) {
            $this->optimizeDatabase($connection, $name);
        }
    }

    /**
     * 优化单个数据库
     */
    private function optimizeDatabase($connection, $dbName)
    {
        try {
            // 分析表并优化
            $stmt = $connection->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($tables as $table) {
                // 优化表
                $connection->exec("OPTIMIZE TABLE {$table}");
                $this->logger->info("已优化表: {$table}");
                
                // 分析表统计信息
                $connection->exec("ANALYZE TABLE {$table}");
            }
            
            $this->logger->info("数据库优化完成: {$dbName}");
            
        } catch (Exception $e) {
            $this->logger->error("数据库优化失败: {$dbName} - " . $e->getMessage());
        }
    }

    /**
     * 自动备份
     */
    public function autoBackup($dbName = null)
    {
        $this->logger->info("开始自动备份");
        
        $databases = $dbName ? [$dbName] : array_keys($this->connections);
        
        foreach ($databases as $name) {
            $this->backupDatabase($name);
        }
    }

    /**
     * 备份单个数据库
     */
    private function backupDatabase($dbName)
    {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $backupFile = "backup/{$dbName}_{$timestamp}.sql";
            
            // 确保备份目录存在
            $backupDir = dirname($backupFile);
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            // 执行mysqldump命令
            $command = sprintf(
                'mysqldump -h %s -u %s -p%s %s > %s',
                $this->config['databases'][$dbName]['host'],
                $this->config['databases'][$dbName]['username'],
                $this->config['databases'][$dbName]['password'],
                $dbName,
                $backupFile
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0) {
                $this->logger->info("数据库备份成功: {$dbName} -> {$backupFile}");
                
                // 清理旧备份
                $this->cleanupOldBackups($dbName);
            } else {
                $this->logger->error("数据库备份失败: {$dbName}");
            }
            
        } catch (Exception $e) {
            $this->logger->error("备份过程异常: {$dbName} - " . $e->getMessage());
        }
    }

    /**
     * 清理旧备份文件
     */
    private function cleanupOldBackups($dbName)
    {
        $backupDir = "backup";
        $retentionDays = $this->config['backup_retention'];
        
        if (is_dir($backupDir)) {
            $files = glob("{$backupDir}/{$dbName}_*.sql");
            
            foreach ($files as $file) {
                if (filemtime($file) < time() - ($retentionDays * 24 * 3600)) {
                    unlink($file);
                    $this->logger->info("已删除过期备份: " . basename($file));
                }
            }
        }
    }

    /**
     * 生成分析报告
     */
    private function generateAnalysisReport()
    {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'summary' => [],
            'databases' => $this->analysisResults
        ];
        
        // 生成摘要
        $totalTables = 0;
        $totalIssues = 0;
        $totalSize = 0;
        
        foreach ($this->analysisResults as $dbName => $analysis) {
            $totalTables += count($analysis['tables']);
            $totalIssues += count($analysis['performance_issues']);
            if (isset($analysis['storage_info']['total_size_mb'])) {
                $totalSize += $analysis['storage_info']['total_size_mb'];
            }
        }
        
        $report['summary'] = [
            'total_databases' => count($this->analysisResults),
            'total_tables' => $totalTables,
            'total_issues' => $totalIssues,
            'total_size_mb' => $totalSize
        ];
        
        // 保存报告
        $reportFile = "reports/database_analysis_" . date('Y-m-d_H-i-s') . ".json";
        $reportDir = dirname($reportFile);
        
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0755, true);
        }
        
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
        $this->logger->info("分析报告已保存: {$reportFile}");
    }

    /**
     * 获取连接
     */
    public function getConnection($name)
    {
        return $this->connections[$name] ?? null;
    }

    /**
     * 获取分析结果
     */
    public function getAnalysisResults()
    {
        return $this->analysisResults;
    }

    /**
     * 监控数据库性能
     */
    public function monitorPerformance()
    {
        $metrics = [];
        
        foreach ($this->connections as $name => $connection) {
            $metrics[$name] = $this->collectPerformanceMetrics($connection);
        }
        
        return $metrics;
    }

    /**
     * 收集性能指标
     */
    private function collectPerformanceMetrics($connection)
    {
        $metrics = [];
        
        try {
            // 查询性能指标
            $stmt = $connection->query("SHOW STATUS LIKE 'Queries'");
            $result = $stmt->fetch();
            $metrics['total_queries'] = $result['Value'];
            
            $stmt = $connection->query("SHOW STATUS LIKE 'Slow_queries'");
            $result = $stmt->fetch();
            $metrics['slow_queries'] = $result['Value'];
            
            $stmt = $connection->query("SHOW STATUS LIKE 'Connections'");
            $result = $stmt->fetch();
            $metrics['total_connections'] = $result['Value'];
            
            // 计算查询性能比率
            if ($metrics['total_queries'] > 0) {
                $metrics['slow_query_ratio'] = ($metrics['slow_queries'] / $metrics['total_queries']) * 100;
            }
            
        } catch (Exception $e) {
            $this->logger->warning("性能指标收集失败: " . $e->getMessage());
        }
        
        return $metrics;
    }
}
