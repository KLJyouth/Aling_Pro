<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Core\Logger;

/**
 * 数据库控制器
 * 负责处理数据库相关操作
 */
class DatabaseController extends Controller
{
    /**
     * 数据库信息页面
     * @return void
     */
    public function info()
    {
        // 获取数据库信息
        $dbInfo = $this->getDatabaseInfo();
        
        // 渲染视图
        View::display('tools.database-info', [
            'pageTitle' => '数据库信息 - IT运维中心',
            'pageHeader' => '数据库信息',
            'currentPage' => 'database-info',
            'breadcrumbs' => [
                '/admin' => '首页',
                '/admin/tools' => '系统工具',
                '/admin/tools/database-info' => '数据库信息'
            ],
            'dbInfo' => $dbInfo
        ]);
    }
    
    /**
     * 数据库管理页面
     * @return void
     */
    public function management()
    {
        try {
            // 获取数据库连接
            $db = Database::getInstance();
            
            // 获取所有表
            $stmt = $db->query("SHOW TABLES");
            $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            // 获取操作类型
            $action = $_GET['action'] ?? '';
            $table = $_GET['table'] ?? '';
            
            // 处理不同操作类型
            $result = [];
            if (!empty($action) && !empty($table)) {
                switch ($action) {
                    case 'structure':
                        $stmt = $db->query("DESCRIBE `{$table}`");
                        $result = [
                            'type' => 'structure',
                            'data' => $stmt->fetchAll(\PDO::FETCH_ASSOC)
                        ];
                        break;
                    case 'data':
                        $limit = 100; // 限制显示记录数
                        $stmt = $db->query("SELECT * FROM `{$table}` LIMIT {$limit}");
                        $result = [
                            'type' => 'data',
                            'data' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
                            'limit' => $limit
                        ];
                        break;
                    case 'optimize':
                        $db->query("OPTIMIZE TABLE `{$table}`");
                        $result = [
                            'type' => 'optimize',
                            'message' => "表 {$table} 优化完成"
                        ];
                        break;
                }
            }
            
            // 渲染视图
            View::display('tools.database-management', [
                'pageTitle' => '数据库管理 - IT运维中心',
                'pageHeader' => '数据库管理',
                'currentPage' => 'database-management',
                'breadcrumbs' => [
                    '/admin' => '首页',
                    '/admin/tools' => '系统工具',
                    '/admin/tools/database-management' => '数据库管理'
                ],
                'tables' => $tables,
                'currentTable' => $table,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Logger::error('访问数据库管理页面失败: ' . $e->getMessage());
            
            // 渲染错误视图
            View::display('tools.database-management', [
                'pageTitle' => '数据库管理 - IT运维中心',
                'pageHeader' => '数据库管理',
                'currentPage' => 'database-management',
                'breadcrumbs' => [
                    '/admin' => '首页',
                    '/admin/tools' => '系统工具',
                    '/admin/tools/database-management' => '数据库管理'
                ],
                'error' => '连接数据库失败: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 获取数据库信息
     * @return array 数据库信息
     */
    private function getDatabaseInfo()
    {
        try {
            $db = Database::getInstance();
            
            // 获取数据库版本
            $stmt = $db->query("SELECT VERSION() as version");
            $version = $stmt->fetch(\PDO::FETCH_ASSOC)['version'] ?? 'Unknown';
            
            // 获取数据库状态
            $stmt = $db->query("SHOW STATUS");
            $statusRows = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
            
            // 获取数据库变量
            $stmt = $db->query("SHOW VARIABLES");
            $variableRows = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
            
            // 获取所有表
            $stmt = $db->query("SHOW TABLES");
            $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            // 获取表信息
            $tableInfo = [];
            foreach ($tables as $table) {
                $stmt = $db->query("SHOW TABLE STATUS LIKE '{$table}'");
                $info = $stmt->fetch(\PDO::FETCH_ASSOC);
                $tableInfo[$table] = $info;
            }
            
            // 计算总大小
            $totalSize = 0;
            $totalRows = 0;
            foreach ($tableInfo as $info) {
                $totalSize += ($info['Data_length'] + $info['Index_length']);
                $totalRows += $info['Rows'];
            }
            
            return [
                'version' => $version,
                'status' => [
                    'uptime' => $statusRows['Uptime'] ?? 0,
                    'threads' => $statusRows['Threads_connected'] ?? 0,
                    'questions' => $statusRows['Questions'] ?? 0,
                    'slowQueries' => $statusRows['Slow_queries'] ?? 0,
                    'opens' => $statusRows['Opened_tables'] ?? 0,
                    'flushes' => $statusRows['Flush_commands'] ?? 0,
                    'openFiles' => $statusRows['Open_files'] ?? 0,
                    'queriesPerSecond' => $statusRows['Queries'] / $statusRows['Uptime'],
                ],
                'variables' => [
                    'charset' => $variableRows['character_set_database'] ?? 'Unknown',
                    'collation' => $variableRows['collation_database'] ?? 'Unknown',
                    'maxConnections' => $variableRows['max_connections'] ?? 0,
                    'bufferSize' => $variableRows['key_buffer_size'] ?? 0,
                    'maxPacket' => $variableRows['max_allowed_packet'] ?? 0,
                    'timeout' => $variableRows['wait_timeout'] ?? 0,
                ],
                'tables' => $tableInfo,
                'totalTables' => count($tables),
                'totalSize' => $totalSize,
                'totalRows' => $totalRows,
                'formattedSize' => $this->formatBytes($totalSize)
            ];
        } catch (\Exception $e) {
            Logger::error('获取数据库信息失败: ' . $e->getMessage());
            return [
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 格式化字节大小
     * @param int $bytes 字节数
     * @param int $precision 精度
     * @return string 格式化后的大小
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
} 