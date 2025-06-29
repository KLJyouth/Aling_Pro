<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class DatabaseManagerController extends Controller
{
    /**
     * 显示数据库管理主页
     */
    public function index()
    {
        // 获取数据库基本信息
        $databaseInfo = $this->getDatabaseInfo();
        
        // 获取表数量
        $tableCount = count(DB::select('SHOW TABLES'));
        
        // 获取数据库大小
        $dbSize = DB::select('SELECT SUM(data_length + index_length) / 1024 / 1024 AS size FROM information_schema.TABLES WHERE table_schema = ?', [env('DB_DATABASE')])[0]->size;
        
        // 获取最新的5个备份
        $backups = $this->getBackups(5);
        
        // 获取系统状态
        $systemStatus = $this->getSystemStatus();
        
        // 获取最近的查询日志
        $recentQueries = $this->getRecentQueries(10);
        
        return view('admin.database.index', compact(
            'databaseInfo',
            'tableCount',
            'dbSize',
            'backups',
            'systemStatus',
            'recentQueries'
        ));
    }
    
    /**
     * 显示所有数据表
     */
    public function tables()
    {
        // 获取所有数据表
        $tables = DB::select('SHOW TABLE STATUS');
        
        // 获取每个表的列数
        foreach ($tables as $table) {
            $columns = DB::select('SHOW COLUMNS FROM ' . $table->Name);
            $table->column_count = count($columns);
            
            // 计算表大小（MB）
            $table->size_mb = ($table->Data_length + $table->Index_length) / 1024 / 1024;
            
            // 获取记录数
            $table->rows = $table->Rows;
        }
        
        return view('admin.database.tables', compact('tables'));
    }
    
    /**
     * 显示表详情
     */
    public function tableDetail($table)
    {
        // 检查表是否存在
        if (!Schema::hasTable($table)) {
            return redirect()->route('admin.database.tables')->with('error', '表不存在');
        }
        
        // 获取表结构
        $columns = DB::select("SHOW FULL COLUMNS FROM `{$table}`");
        
        // 获取表索引
        $indexes = DB::select("SHOW INDEX FROM `{$table}`");
        
        // 获取表的外键
        $foreignKeys = DB::select("
            SELECT 
                CONSTRAINT_NAME, 
                COLUMN_NAME, 
                REFERENCED_TABLE_NAME, 
                REFERENCED_COLUMN_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE 
                TABLE_SCHEMA = ? AND 
                TABLE_NAME = ? AND 
                REFERENCED_TABLE_NAME IS NOT NULL
        ", [env('DB_DATABASE'), $table]);
        
        // 获取表的创建语句
        $createTable = DB::select("SHOW CREATE TABLE `{$table}`")[0]->{'Create Table'};
        
        // 获取表的前10条记录
        $records = DB::table($table)->limit(10)->get();
        
        // 获取表的统计信息
        $stats = DB::select("
            SELECT 
                COUNT(*) as total_rows,
                SUM(DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024 as size_mb,
                UPDATE_TIME as last_update,
                ENGINE as engine,
                TABLE_COLLATION as collation
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
        ", [env('DB_DATABASE'), $table])[0];
        
        return view('admin.database.table_detail', compact(
            'table',
            'columns',
            'indexes',
            'foreignKeys',
            'createTable',
            'records',
            'stats'
        ));
    }
    
    /**
     * 执行SQL查询
     */
    public function executeQuery(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
        ]);
        
        $query = $request->input('query');
        $startTime = microtime(true);
        
        try {
            // 判断查询类型
            $queryType = strtoupper(strtok(trim($query), ' '));
            
            if (in_array($queryType, ['SELECT', 'SHOW', 'DESCRIBE', 'DESC', 'EXPLAIN'])) {
                // 读取操作，返回结果集
                $results = DB::select($query);
                $affectedRows = count($results);
                $isSelect = true;
            } else {
                // 写入操作，返回影响行数
                $affectedRows = DB::statement($query);
                $results = [];
                $isSelect = false;
            }
            
            $executionTime = microtime(true) - $startTime;
            
            // 记录查询历史
            $this->logQuery($query, $executionTime, $affectedRows, $isSelect);
            
            return response()->json([
                'success' => true,
                'results' => $results,
                'affected_rows' => $affectedRows,
                'execution_time' => round($executionTime, 4),
                'is_select' => $isSelect,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
    
    /**
     * 优化数据库
     */
    public function optimize(Request $request)
    {
        $tables = $request->input('tables', []);
        
        if (empty($tables)) {
            // 如果没有指定表，则优化所有表
            $tablesList = DB::select('SHOW TABLES');
            foreach ($tablesList as $table) {
                $tables[] = reset($table);
            }
        }
        
        $results = [];
        $success = true;
        
        foreach ($tables as $table) {
            try {
                // 检查表
                DB::statement("CHECK TABLE `{$table}`");
                
                // 优化表
                DB::statement("OPTIMIZE TABLE `{$table}`");
                
                // 分析表
                DB::statement("ANALYZE TABLE `{$table}`");
                
                $results[$table] = [
                    'status' => 'success',
                    'message' => '优化成功',
                ];
            } catch (\Exception $e) {
                $success = false;
                $results[$table] = [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }
        
        return response()->json([
            'success' => $success,
            'results' => $results,
        ]);
    }
    
    /**
     * 显示备份管理页面
     */
    public function backupIndex()
    {
        $backups = $this->getBackups();
        return view('admin.database.backup', compact('backups'));
    }
    
    /**
     * 创建数据库备份
     */
    public function createBackup(Request $request)
    {
        $request->validate([
            'backup_name' => 'nullable|string|max:255',
            'tables' => 'nullable|array',
        ]);
        
        $backupName = $request->input('backup_name') ?: 'backup_' . date('Y_m_d_His');
        $tables = $request->input('tables', []);
        
        try {
            // 创建备份目录
            $backupDir = storage_path('app/backups');
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }
            
            // 备份文件名
            $filename = $backupName . '.sql';
            $backupPath = $backupDir . '/' . $filename;
            
            // 构建 mysqldump 命令
            $command = sprintf(
                'mysqldump -h %s -u %s %s %s %s > %s',
                escapeshellarg(env('DB_HOST')),
                escapeshellarg(env('DB_USERNAME')),
                env('DB_PASSWORD') ? '-p' . escapeshellarg(env('DB_PASSWORD')) : '',
                escapeshellarg(env('DB_DATABASE')),
                !empty($tables) ? implode(' ', array_map('escapeshellarg', $tables)) : '',
                escapeshellarg($backupPath)
            );
            
            // 执行备份命令
            exec($command, $output, $returnVar);
            
            if ($returnVar !== 0) {
                throw new \Exception('备份创建失败: ' . implode("\n", $output));
            }
            
            // 记录备份信息
            $this->logBackup($filename, !empty($tables) ? implode(',', $tables) : '全库备份');
            
            return redirect()->route('admin.database.backup.index')
                ->with('success', '数据库备份创建成功');
        } catch (\Exception $e) {
            return redirect()->route('admin.database.backup.index')
                ->with('error', '备份创建失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 下载备份文件
     */
    public function downloadBackup($filename)
    {
        $path = storage_path('app/backups/' . $filename);
        
        if (!File::exists($path)) {
            return redirect()->route('admin.database.backup.index')
                ->with('error', '备份文件不存在');
        }
        
        return response()->download($path);
    }
    
    /**
     * 删除备份文件
     */
    public function deleteBackup($filename)
    {
        $path = storage_path('app/backups/' . $filename);
        
        if (!File::exists($path)) {
            return redirect()->route('admin.database.backup.index')
                ->with('error', '备份文件不存在');
        }
        
        File::delete($path);
        
        // 删除备份记录
        DB::table('database_backups')->where('filename', $filename)->delete();
        
        return redirect()->route('admin.database.backup.index')
            ->with('success', '备份文件删除成功');
    }
    
    /**
     * 恢复数据库备份
     */
    public function restoreBackup($filename)
    {
        $path = storage_path('app/backups/' . $filename);
        
        if (!File::exists($path)) {
            return redirect()->route('admin.database.backup.index')
                ->with('error', '备份文件不存在');
        }
        
        try {
            // 构建恢复命令
            $command = sprintf(
                'mysql -h %s -u %s %s %s < %s',
                escapeshellarg(env('DB_HOST')),
                escapeshellarg(env('DB_USERNAME')),
                env('DB_PASSWORD') ? '-p' . escapeshellarg(env('DB_PASSWORD')) : '',
                escapeshellarg(env('DB_DATABASE')),
                escapeshellarg($path)
            );
            
            // 执行恢复命令
            exec($command, $output, $returnVar);
            
            if ($returnVar !== 0) {
                throw new \Exception('备份恢复失败: ' . implode("\n", $output));
            }
            
            return redirect()->route('admin.database.backup.index')
                ->with('success', '数据库备份恢复成功');
        } catch (\Exception $e) {
            return redirect()->route('admin.database.backup.index')
                ->with('error', '备份恢复失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 数据库监控页面
     */
    public function monitor()
    {
        // 获取数据库状态
        $status = DB::select('SHOW STATUS');
        $statusArray = [];
        foreach ($status as $item) {
            $statusArray[$item->Variable_name] = $item->Value;
        }
        
        // 获取数据库变量
        $variables = DB::select('SHOW VARIABLES');
        $variablesArray = [];
        foreach ($variables as $item) {
            $variablesArray[$item->Variable_name] = $item->Value;
        }
        
        // 获取进程列表
        $processes = DB::select('SHOW PROCESSLIST');
        
        // 获取InnoDB状态
        $innodbStatus = DB::select("SHOW ENGINE INNODB STATUS")[0]->Status;
        
        // 获取性能统计
        $performance = [
            'questions' => $statusArray['Questions'] ?? 0,
            'queries_per_second' => $statusArray['Queries'] ?? 0,
            'slow_queries' => $statusArray['Slow_queries'] ?? 0,
            'opens' => $statusArray['Opened_tables'] ?? 0,
            'flush_commands' => $statusArray['Flush_commands'] ?? 0,
            'open_tables' => $statusArray['Open_tables'] ?? 0,
            'uptime' => $this->formatUptime($statusArray['Uptime'] ?? 0),
        ];
        
        // 获取连接统计
        $connections = [
            'max_connections' => $variablesArray['max_connections'] ?? 0,
            'max_used_connections' => $statusArray['Max_used_connections'] ?? 0,
            'current_connections' => $statusArray['Threads_connected'] ?? 0,
            'connection_errors' => $statusArray['Connection_errors_max_connections'] ?? 0,
            'aborted_clients' => $statusArray['Aborted_clients'] ?? 0,
            'aborted_connects' => $statusArray['Aborted_connects'] ?? 0,
        ];
        
        return view('admin.database.monitor', compact(
            'statusArray',
            'variablesArray',
            'processes',
            'innodbStatus',
            'performance',
            'connections'
        ));
    }
    
    /**
     * 显示慢查询日志
     */
    public function slowQueries()
    {
        // 检查慢查询日志是否启用
        $slowQueryEnabled = DB::select("SHOW VARIABLES LIKE 'slow_query_log'")[0]->Value == 'ON';
        $slowQueryLogFile = DB::select("SHOW VARIABLES LIKE 'slow_query_log_file'")[0]->Value;
        $longQueryTime = DB::select("SHOW VARIABLES LIKE 'long_query_time'")[0]->Value;
        
        $slowQueries = [];
        
        if ($slowQueryEnabled && file_exists($slowQueryLogFile)) {
            // 解析慢查询日志文件
            $content = file_get_contents($slowQueryLogFile);
            preg_match_all('/# Time: (.*)\n# User@Host: (.*)\n# Query_time: (.*) Lock_time: (.*) Rows_sent: (.*) Rows_examined: (.*)\n(.*?);/s', $content, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $slowQueries[] = [
                    'time' => $match[1],
                    'user_host' => $match[2],
                    'query_time' => $match[3],
                    'lock_time' => $match[4],
                    'rows_sent' => $match[5],
                    'rows_examined' => $match[6],
                    'query' => $match[7],
                ];
            }
        }
        
        return view('admin.database.slow_queries', compact(
            'slowQueryEnabled',
            'slowQueryLogFile',
            'longQueryTime',
            'slowQueries'
        ));
    }
    
    /**
     * 显示数据库结构
     */
    public function structure()
    {
        // 获取所有表
        $tables = DB::select('SHOW TABLES');
        $tableNames = [];
        foreach ($tables as $table) {
            $tableNames[] = reset($table);
        }
        
        // 获取表之间的关系
        $relationships = [];
        foreach ($tableNames as $table) {
            $foreignKeys = DB::select("
                SELECT 
                    CONSTRAINT_NAME, 
                    COLUMN_NAME, 
                    REFERENCED_TABLE_NAME, 
                    REFERENCED_COLUMN_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE 
                    TABLE_SCHEMA = ? AND 
                    TABLE_NAME = ? AND 
                    REFERENCED_TABLE_NAME IS NOT NULL
            ", [env('DB_DATABASE'), $table]);
            
            foreach ($foreignKeys as $fk) {
                $relationships[] = [
                    'source' => $table,
                    'target' => $fk->REFERENCED_TABLE_NAME,
                    'source_column' => $fk->COLUMN_NAME,
                    'target_column' => $fk->REFERENCED_COLUMN_NAME,
                ];
            }
        }
        
        // 获取每个表的列
        $tableColumns = [];
        foreach ($tableNames as $table) {
            $columns = DB::select("SHOW COLUMNS FROM `{$table}`");
            $columnList = [];
            foreach ($columns as $column) {
                $columnList[] = [
                    'name' => $column->Field,
                    'type' => $column->Type,
                    'nullable' => $column->Null === 'YES',
                    'key' => $column->Key,
                    'default' => $column->Default,
                    'extra' => $column->Extra,
                ];
            }
            $tableColumns[$table] = $columnList;
        }
        
        return view('admin.database.structure', compact(
            'tableNames',
            'relationships',
            'tableColumns'
        ));
    }
    
    /**
     * 获取数据库信息
     */
    private function getDatabaseInfo()
    {
        // 获取MySQL版本
        $version = DB::select('SELECT VERSION() as version')[0]->version;
        
        // 获取数据库名称
        $database = env('DB_DATABASE');
        
        // 获取数据库编码
        $charset = DB::select('SELECT @@character_set_database as charset')[0]->charset;
        
        // 获取数据库排序规则
        $collation = DB::select('SELECT @@collation_database as collation')[0]->collation;
        
        // 获取数据库连接信息
        $connection = [
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'username' => env('DB_USERNAME'),
        ];
        
        return [
            'version' => $version,
            'database' => $database,
            'charset' => $charset,
            'collation' => $collation,
            'connection' => $connection,
        ];
    }
    
    /**
     * 获取系统状态
     */
    private function getSystemStatus()
    {
        // 获取MySQL状态
        $status = DB::select('SHOW STATUS');
        $statusArray = [];
        foreach ($status as $item) {
            $statusArray[$item->Variable_name] = $item->Value;
        }
        
        // 计算内存使用情况
        $memoryUsage = [
            'used' => round(($statusArray['Global_memory_used'] ?? 0) / 1024 / 1024, 2),
            'total' => round(($statusArray['Global_memory_limit'] ?? 0) / 1024 / 1024, 2),
        ];
        
        // 计算连接使用情况
        $connectionUsage = [
            'current' => $statusArray['Threads_connected'] ?? 0,
            'max' => DB::select('SHOW VARIABLES LIKE "max_connections"')[0]->Value,
        ];
        
        // 计算缓存使用情况
        $cacheUsage = [
            'query_cache_size' => round(($statusArray['Qcache_free_memory'] ?? 0) / 1024 / 1024, 2),
            'query_cache_hits' => $statusArray['Qcache_hits'] ?? 0,
            'query_cache_misses' => $statusArray['Qcache_inserts'] ?? 0,
        ];
        
        return [
            'memory_usage' => $memoryUsage,
            'connection_usage' => $connectionUsage,
            'cache_usage' => $cacheUsage,
            'uptime' => $this->formatUptime($statusArray['Uptime'] ?? 0),
        ];
    }
    
    /**
     * 格式化运行时间
     */
    private function formatUptime($seconds)
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
        
        return sprintf('%d天 %d小时 %d分钟 %d秒', $days, $hours, $minutes, $seconds);
    }
    
    /**
     * 获取最近的查询日志
     */
    private function getRecentQueries($limit = 10)
    {
        // 从查询日志表获取最近的查询
        if (Schema::hasTable('database_query_log')) {
            return DB::table('database_query_log')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        }
        
        return [];
    }
    
    /**
     * 记录SQL查询
     */
    private function logQuery($query, $executionTime, $affectedRows, $isSelect)
    {
        // 确保查询日志表存在
        if (!Schema::hasTable('database_query_log')) {
            Schema::create('database_query_log', function ($table) {
                $table->id();
                $table->text('query');
                $table->float('execution_time')->default(0);
                $table->integer('affected_rows')->default(0);
                $table->boolean('is_select')->default(true);
                $table->string('user_id')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }
        
        // 记录查询
        DB::table('database_query_log')->insert([
            'query' => $query,
            'execution_time' => $executionTime,
            'affected_rows' => $affectedRows,
            'is_select' => $isSelect,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    /**
     * 获取备份列表
     */
    private function getBackups($limit = null)
    {
        // 确保备份记录表存在
        if (!Schema::hasTable('database_backups')) {
            Schema::create('database_backups', function ($table) {
                $table->id();
                $table->string('filename');
                $table->string('description')->nullable();
                $table->integer('size')->default(0);
                $table->string('created_by')->nullable();
                $table->timestamps();
            });
        }
        
        // 从备份目录获取文件
        $backupDir = storage_path('app/backups');
        $files = [];
        
        if (File::exists($backupDir)) {
            $files = File::files($backupDir);
        }
        
        $backups = [];
        foreach ($files as $file) {
            $filename = $file->getFilename();
            
            // 检查数据库中是否有记录
            $record = DB::table('database_backups')->where('filename', $filename)->first();
            
            if ($record) {
                $backups[] = [
                    'id' => $record->id,
                    'filename' => $filename,
                    'description' => $record->description,
                    'size' => $this->formatFileSize($file->getSize()),
                    'created_at' => Carbon::parse($record->created_at)->format('Y-m-d H:i:s'),
                    'created_by' => $record->created_by,
                ];
            } else {
                // 如果没有记录，创建一个
                $id = DB::table('database_backups')->insertGetId([
                    'filename' => $filename,
                    'description' => '自动导入的备份',
                    'size' => $file->getSize(),
                    'created_at' => $file->getMTime(),
                    'updated_at' => now(),
                ]);
                
                $backups[] = [
                    'id' => $id,
                    'filename' => $filename,
                    'description' => '自动导入的备份',
                    'size' => $this->formatFileSize($file->getSize()),
                    'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                    'created_by' => '系统',
                ];
            }
        }
        
        // 按创建时间排序
        usort($backups, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        // 限制返回数量
        if ($limit && count($backups) > $limit) {
            $backups = array_slice($backups, 0, $limit);
        }
        
        return $backups;
    }
    
    /**
     * 记录备份信息
     */
    private function logBackup($filename, $description)
    {
        // 确保备份记录表存在
        if (!Schema::hasTable('database_backups')) {
            Schema::create('database_backups', function ($table) {
                $table->id();
                $table->string('filename');
                $table->string('description')->nullable();
                $table->integer('size')->default(0);
                $table->string('created_by')->nullable();
                $table->timestamps();
            });
        }
        
        $path = storage_path('app/backups/' . $filename);
        $size = File::exists($path) ? File::size($path) : 0;
        
        // 记录备份
        DB::table('database_backups')->insert([
            'filename' => $filename,
            'description' => $description,
            'size' => $size,
            'created_by' => auth()->user()->name ?? '系统',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    /**
     * 格式化文件大小
     */
    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
} 