<?php

declare(strict_types=1];

namespace AlingAi\Pro\Database;

use Psr\Log\LoggerInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Exception;

/**
 * 数据库优化工�?
 * 
 * 提供数据库性能优化、索引管理和查询分析功能
 * 
 * @package AlingAi\Pro\Database
 */
/**
 * DatabaseOptimizer �?
 *
 * @package AlingAi\Pro\Database
 */
class DatabaseOptimizer
{
    private LoggerInterface $logger;
    private array $config;
    private array $optimizationResults = [];

    /**


     * __construct 方法


     *


     * @param LoggerInterface $logger


     * @param array $config


     * @return void


     */


    public function __construct(LoggerInterface $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = array_merge([
            'slow_query_threshold' => 1000, // 毫秒
            'max_table_scan_rows' => 10000,
            'index_selectivity_threshold' => 0.1,
            'auto_optimize_tables' => false,
            'backup_before_optimize' => true,
        ],  $config];
    }

    /**
     * 执行全面的数据库优化
     */
    /**

     * optimize 方法

     *

     * @return void

     */

    public function optimize(): array
    {
        $this->logger->info('Starting database optimization'];
        
        $this->optimizationResults = [
            'started_at' => time(),
            'tables_analyzed' => 0,
            'indexes_created' => 0,
            'indexes_dropped' => 0,
            'queries_optimized' => 0,
            'performance_improvement' => 0,
            'recommendations' => [], 
            'errors' => [], 
        ];

        try {
            // 1. 分析表结�?
            $this->analyzeTables(];
            
            // 2. 优化索引
            $this->optimizeIndexes(];
            
            // 3. 分析慢查�?
            $this->analyzeSlowQueries(];
            
            // 4. 优化表结�?
            if ($this->config['auto_optimize_tables']) {
                $this->optimizeTables(];
            }
            
            // 5. 生成优化建议
            $this->generateRecommendations(];
            
        } catch (Exception $e) {
            $this->optimizationResults['errors'][] = $e->getMessage(];
            $this->logger->error('Database optimization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]];
        }

        $this->optimizationResults['completed_at'] = time(];
        $this->optimizationResults['duration'] = 
            $this->optimizationResults['completed_at'] - $this->optimizationResults['started_at'];

        $this->logger->info('Database optimization completed', $this->optimizationResults];
        
        return $this->optimizationResults;
    }

    /**
     * 分析表结�?
     */
    /**

     * analyzeTables 方法

     *

     * @return void

     */

    private function analyzeTables(): void
    {
        $tables = $this->getTables(];
        
        foreach ($tables as $table) {
            try {
                $analysis = $this->analyzeTable($table];
                $this->optimizationResults['tables'][$table] = $analysis;
                $this->optimizationResults['tables_analyzed']++;
                
                $this->logger->debug("Analyzed table: {$table}", $analysis];
                
            } catch (Exception $e) {
                $this->optimizationResults['errors'][] = "Failed to analyze table {$table}: " . $e->getMessage(];
            }
        }
    }

    /**
     * 分析单个�?
     */
    /**

     * analyzeTable 方法

     *

     * @param string $table

     * @return void

     */

    private function analyzeTable(string $table): array
    {
        $analysis = [
            'row_count' => $this->getTableRowCount($table],
            'size_mb' => $this->getTableSize($table],
            'columns' => $this->getTableColumns($table],
            'indexes' => $this->getTableIndexes($table],
            'foreign_keys' => $this->getTableForeignKeys($table],
            'issues' => [], 
            'recommendations' => [], 
        ];

        // 检查潜在问�?
        $this->checkTableIssues($table, $analysis];
        
        return $analysis;
    }

    /**
     * 检查表的潜在问�?
     */
    /**

     * checkTableIssues 方法

     *

     * @param string $table

     * @param mixed $analysis

     * @return void

     */

    private function checkTableIssues(string $table, array &$analysis): void
    {
        // 检查是否缺少主�?
        $hasPrimaryKey = false;
        foreach ($analysis['indexes'] as $index) {
            if ($index['type'] === 'PRIMARY') {
                $hasPrimaryKey = true;
                break;
            }
        }
        
        if (!$hasPrimaryKey) {
            $analysis['issues'][] = 'Missing primary key';
            $analysis['recommendations'][] = 'Add a primary key to improve performance';
        }

        // 检查大表是否有适当的索�?
        if ($analysis['row_count'] > $this->config['max_table_scan_rows']) {
            $indexCount = count($analysis['indexes']];
            if ($indexCount < 2) { // 只有主键
                $analysis['issues'][] = 'Large table with insufficient indexes';
                $analysis['recommendations'][] = 'Consider adding indexes on frequently queried columns';
            }
        }

        // 检查过多的索引
        if (count($analysis['indexes']) > 10) {
            $analysis['issues'][] = 'Too many indexes';
            $analysis['recommendations'][] = 'Review and remove unused indexes';
        }
    }

    /**
     * 优化索引
     */
    /**

     * optimizeIndexes 方法

     *

     * @return void

     */

    private function optimizeIndexes(): void
    {
        $indexRecommendations = $this->analyzeIndexUsage(];
        
        foreach ($indexRecommendations as $recommendation) {
            try {
                switch ($recommendation['action']) {
                    case 'create':
                        $this->createIndex($recommendation];
                        $this->optimizationResults['indexes_created']++;
                        break;
                        
                    case 'drop':
                        $this->dropIndex($recommendation];
                        $this->optimizationResults['indexes_dropped']++;
                        break;
                }
            } catch (Exception $e) {
                $this->optimizationResults['errors'][] = "Index optimization failed: " . $e->getMessage(];
            }
        }
    }

    /**
     * 分析索引使用情况
     */
    /**

     * analyzeIndexUsage 方法

     *

     * @return void

     */

    private function analyzeIndexUsage(): array
    {
        $recommendations = [];
        
        // 获取索引使用统计
        $indexStats = $this->getIndexUsageStats(];
        
        foreach ($indexStats as $stat) {
            // 找出未使用的索引
            if ($stat['usage_count'] === 0 && $stat['type'] !== 'PRIMARY') {
                $recommendations[] = [
                    'action' => 'drop',
                    'table' => $stat['table'], 
                    'index' => $stat['index'], 
                    'reason' => 'Index not used',
                ];
            }
            
            // 找出选择性差的索�?
            if ($stat['selectivity'] < $this->config['index_selectivity_threshold']) {
                $recommendations[] = [
                    'action' => 'drop',
                    'table' => $stat['table'], 
                    'index' => $stat['index'], 
                    'reason' => 'Poor index selectivity',
                ];
            }
        }

        // 分析查询模式，建议新索引
        $queryPatterns = $this->analyzeQueryPatterns(];
        foreach ($queryPatterns as $pattern) {
            if ($pattern['scan_type'] === 'FULL' && $pattern['frequency'] > 10) {
                $recommendations[] = [
                    'action' => 'create',
                    'table' => $pattern['table'], 
                    'columns' => $pattern['where_columns'], 
                    'reason' => 'Frequent full table scan',
                ];
            }
        }

        return $recommendations;
    }

    /**
     * 创建索引
     */
    /**

     * createIndex 方法

     *

     * @param array $recommendation

     * @return void

     */

    private function createIndex(array $recommendation): void
    {
        $table = $recommendation['table'];
        $columns = is_[$recommendation['columns']) 
            ? $recommendation['columns'] 
            : [$recommendation['columns']];
        
        $indexName = $table . '_' . implode('_', $columns) . '_index';
        
        $columnsStr = implode(', ', array_map(fn($col) => "`{$col}`", $columns)];
        $sql = "CREATE INDEX `{$indexName}` ON `{$table}` ({$columnsStr})";
        
        DB::statement($sql];
        
        $this->logger->info("Created index: {$indexName} on table: {$table}"];
    }

    /**
     * 删除索引
     */
    /**

     * dropIndex 方法

     *

     * @param array $recommendation

     * @return void

     */

    private function dropIndex(array $recommendation): void
    {
        $table = $recommendation['table'];
        $index = $recommendation['index'];
        
        $sql = "DROP INDEX `{$index}` ON `{$table}`";
        DB::statement($sql];
        
        $this->logger->info("Dropped index: {$index} from table: {$table}"];
    }

    /**
     * 分析慢查�?
     */
    /**

     * analyzeSlowQueries 方法

     *

     * @return void

     */

    private function analyzeSlowQueries(): void
    {
        try {
            // 启用慢查询日�?
            DB::statement("SET GLOBAL slow_query_log = 'ON'"];
            DB::statement("SET GLOBAL long_query_time = " . ($this->config['slow_query_threshold'] / 1000)];
            
            // 分析现有慢查�?
            $slowQueries = $this->getSlowQueries(];
            
            foreach ($slowQueries as $query) {
                $this->optimizationResults['slow_queries'][] = [
                    'query' => $query['query'], 
                    'execution_time' => $query['execution_time'], 
                    'rows_examined' => $query['rows_examined'], 
                    'optimization_suggestion' => $this->suggestQueryOptimization($query],
                ];
            }
            
        } catch (Exception $e) {
            $this->optimizationResults['errors'][] = "Slow query analysis failed: " . $e->getMessage(];
        }
    }

    /**
     * 优化�?
     */
    /**

     * optimizeTables 方法

     *

     * @return void

     */

    private function optimizeTables(): void
    {
        $tables = $this->getTables(];
        
        foreach ($tables as $table) {
            try {
                if ($this->config['backup_before_optimize']) {
                    $this->backupTable($table];
                }
                
                DB::statement("OPTIMIZE TABLE `{$table}`"];
                $this->logger->info("Optimized table: {$table}"];
                
            } catch (Exception $e) {
                $this->optimizationResults['errors'][] = "Failed to optimize table {$table}: " . $e->getMessage(];
            }
        }
    }

    /**
     * 生成优化建议
     */
    /**

     * generateRecommendations 方法

     *

     * @return void

     */

    private function generateRecommendations(): void
    {
        $recommendations = [];
        
        // 基于分析结果生成建议
        foreach ($this->optimizationResults['tables'] ?? [] as $table => $analysis) {
            $recommendations = array_merge($recommendations, $analysis['recommendations']];
        }

        // 添加常规建议
        $recommendations[] = 'Regularly update table statistics';
        $recommendations[] = 'Monitor query performance';
        $recommendations[] = 'Consider partitioning for large tables';
        
        $this->optimizationResults['recommendations'] = array_unique($recommendations];
    }

    /**
     * 获取数据库中的所有表
     */
    /**

     * getTables 方法

     *

     * @return void

     */

    private function getTables(): array
    {
        $tables = [];
        $result = DB::select('SHOW TABLES'];
        
        foreach ($result as $row) {
            $tableName = array_values((array) $row)[0];
            $tables[] = $tableName;
        }
        
        return $tables;
    }

    /**
     * 获取表的行数
     */
    /**

     * getTableRowCount 方法

     *

     * @param string $table

     * @return void

     */

    private function getTableRowCount(string $table): int
    {
        $result = DB::select("SELECT COUNT(*) as count FROM `{$table}`"];
        return $result[0]->count;
    }

    /**
     * 获取表的大小（MB�?
     */
    /**

     * getTableSize 方法

     *

     * @param string $table

     * @return void

     */

    private function getTableSize(string $table): float
    {
        $result = DB::select("
            SELECT 
                ROUND(((data_length + index_length) / 1024 / 1024], 2) AS size_mb
            FROM information_schema.TABLES 
            WHERE table_schema = DATABASE() 
            AND table_name = ?
        ", [$table]];
        
        return $result[0]->size_mb ?? 0;
    }

    /**
     * 获取表的列信�?
     */
    /**

     * getTableColumns 方法

     *

     * @param string $table

     * @return void

     */

    private function getTableColumns(string $table): array
    {
        $columns = [];
        $result = DB::select("DESCRIBE `{$table}`"];
        
        foreach ($result as $column) {
            $columns[] = [
                'name' => $column->Field,
                'type' => $column->Type,
                'null' => $column->Null === 'YES',
                'key' => $column->Key,
                'default' => $column->Default,
                'extra' => $column->Extra,
            ];
        }
        
        return $columns;
    }

    /**
     * 获取表的索引信息
     */
    /**

     * getTableIndexes 方法

     *

     * @param string $table

     * @return void

     */

    private function getTableIndexes(string $table): array
    {
        $indexes = [];
        $result = DB::select("SHOW INDEX FROM `{$table}`"];
        
        foreach ($result as $index) {
            $indexes[] = [
                'name' => $index->Key_name,
                'column' => $index->Column_name,
                'type' => $index->Index_type,
                'unique' => $index->Non_unique == 0,
                'sequence' => $index->Seq_in_index,
            ];
        }
        
        return $indexes;
    }

    /**
     * 获取表的外键信息
     */
    /**

     * getTableForeignKeys 方法

     *

     * @param string $table

     * @return void

     */

    private function getTableForeignKeys(string $table): array
    {
        $foreignKeys = [];
        $result = DB::select("
            SELECT 
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME,
                CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ?
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$table]];
        
        foreach ($result as $fk) {
            $foreignKeys[] = [
                'column' => $fk->COLUMN_NAME,
                'references_table' => $fk->REFERENCED_TABLE_NAME,
                'references_column' => $fk->REFERENCED_COLUMN_NAME,
                'constraint' => $fk->CONSTRAINT_NAME,
            ];
        }
        
        return $foreignKeys;
    }

    /**
     * 获取索引使用统计
     */
    /**

     * getIndexUsageStats 方法

     *

     * @return void

     */

    private function getIndexUsageStats(): array
    {
        // 这里需要根据具体数据库实现
        // MySQL 8.0+ 可以使用 performance_schema
        return [];
    }

    /**
     * 分析查询模式
     */
    /**

     * analyzeQueryPatterns 方法

     *

     * @return void

     */

    private function analyzeQueryPatterns(): array
    {
        // 这里需要分析查询日志或使用性能监控数据
        return [];
    }

    /**
     * 获取慢查�?
     */
    /**

     * getSlowQueries 方法

     *

     * @return void

     */

    private function getSlowQueries(): array
    {
        // 这里需要从慢查询日志中读取数据
        return [];
    }

    /**
     * 建议查询优化
     */
    /**

     * suggestQueryOptimization 方法

     *

     * @param array $query

     * @return void

     */

    private function suggestQueryOptimization(array $query): string
    {
        // 基于查询分析提供优化建议
        return 'Consider adding appropriate indexes';
    }

    /**
     * 备份�?
     */
    /**

     * backupTable 方法

     *

     * @param string $table

     * @return void

     */

    private function backupTable(string $table): void
    {
        $backupTable = $table . '_backup_' . date('Ymd_His'];
        DB::statement("CREATE TABLE `{$backupTable}` LIKE `{$table}`"];
        DB::statement("INSERT INTO `{$backupTable}` SELECT * FROM `{$table}`"];
        
        $this->logger->info("Created backup table: {$backupTable}"];
    }
}

