<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Core\Logger;

/**
 * 系统工具控制器
 * 负责处理系统工具相关请求
 */
class ToolController extends Controller
{
    /**
     * 系统工具首页
     */
    public function index()
    {
        // 渲染视图
        View::display('tools.index', [
            'pageTitle' => '系统工具 - IT运维中心',
            'pageHeader' => '系统工具',
            'currentPage' => 'tools',
            'breadcrumbs' => [
                '/admin' => '首页',
                '/admin/tools' => '系统工具'
            ]
        ]);
    }
    
    /**
     * PHP信息页面
     */
    public function phpInfo()
    {
        // 渲染视图
        View::display('tools.phpinfo', [
            'pageTitle' => 'PHP信息 - IT运维中心',
            'pageHeader' => 'PHP信息',
            'currentPage' => 'phpinfo',
            'breadcrumbs' => [
                '/admin' => '首页',
                '/admin/tools' => '系统工具',
                '/admin/tools/phpinfo' => 'PHP信息'
            ]
        ]);
    }
    
    /**
     * 服务器信息页面
     */
    public function serverInfo()
    {
        // 获取服务器信息
        $serverInfo = $this->getServerInfo();
        
        // 渲染视图
        View::display('tools.server-info', [
            'pageTitle' => '服务器信息 - IT运维中心',
            'pageHeader' => '服务器信息',
            'currentPage' => 'server-info',
            'breadcrumbs' => [
                '/admin' => '首页',
                '/admin/tools' => '系统工具',
                '/admin/tools/server-info' => '服务器信息'
            ],
            'serverInfo' => $serverInfo
        ]);
    }
    
    /**
     * 数据库信息页面
     */
    public function databaseInfo()
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
     */
    public function databaseManagement()
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
            $result = null;
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
     * 获取服务器信息
     * 
     * @return array 服务器信息
     */
    private function getServerInfo()
    {
        // 基本信息
        $info = [
            'os' => [
                'name' => PHP_OS,
                'version' => php_uname('r'),
                'architecture' => php_uname('m'),
                'hostname' => php_uname('n')
            ],
            'server' => [
                'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown',
                'addr' => $_SERVER['SERVER_ADDR'] ?? 'Unknown',
                'name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
                'port' => $_SERVER['SERVER_PORT'] ?? 'Unknown',
                'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
                'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ],
            'php' => [
                'version' => PHP_VERSION,
                'sapi' => php_sapi_name(),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time') . ' 秒',
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'display_errors' => ini_get('display_errors') ? '开启' : '关闭',
                'max_input_vars' => ini_get('max_input_vars'),
                'default_charset' => ini_get('default_charset'),
                'extensions' => implode(', ', get_loaded_extensions())
            ],
            'time' => [
                'server_time' => date('Y-m-d H:i:s'),
                'timezone' => date_default_timezone_get(),
                'uptime' => $this->getServerUptime()
            ],
            'disk' => [
                'total' => $this->formatBytes(disk_total_space('/')),
                'free' => $this->formatBytes(disk_free_space('/')),
                'used' => $this->formatBytes(disk_total_space('/') - disk_free_space('/')),
                'usage' => round((disk_total_space('/') - disk_free_space('/')) / disk_total_space('/') * 100, 2) . '%'
            ]
        ];
        
        // 尝试获取内存信息
        $memInfo = $this->getMemoryInfo();
        if ($memInfo) {
            $info['memory'] = $memInfo;
        }
        
        // 尝试获取CPU信息
        $cpuInfo = $this->getCpuInfo();
        if ($cpuInfo) {
            $info['cpu'] = $cpuInfo;
        }
        
        return $info;
    }
    
    /**
     * 获取数据库信息
     * 
     * @return array 数据库信息
     */
    private function getDatabaseInfo()
    {
        try {
            $db = Database::getInstance();
            
            // 基本信息
            $info = [
                'version' => $db->query("SELECT VERSION() as version")->fetchColumn(),
                'connection' => $db->getAttribute(\PDO::ATTR_CONNECTION_STATUS),
                'server_info' => $db->getAttribute(\PDO::ATTR_SERVER_INFO),
                'client_version' => $db->getAttribute(\PDO::ATTR_CLIENT_VERSION),
                'driver_name' => $db->getAttribute(\PDO::ATTR_DRIVER_NAME)
            ];
            
            // 数据库大小
            $stmt = $db->query("SELECT table_schema AS 'database', 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb' 
                FROM information_schema.TABLES 
                WHERE table_schema = DATABASE() 
                GROUP BY table_schema");
            $size = $stmt->fetch(\PDO::FETCH_ASSOC);
            $info['size'] = $size['size_mb'] ?? 0;
            
            // 表信息
            $stmt = $db->query("SELECT 
                COUNT(*) AS 'table_count',
                SUM(data_length) / 1024 / 1024 AS 'data_size_mb',
                SUM(index_length) / 1024 / 1024 AS 'index_size_mb'
                FROM information_schema.TABLES 
                WHERE table_schema = DATABASE()");
            $tableInfo = $stmt->fetch(\PDO::FETCH_ASSOC);
            $info['tables'] = $tableInfo;
            
            // 获取所有表
            $stmt = $db->query("SELECT 
                table_name, 
                engine, 
                table_rows, 
                data_length / 1024 / 1024 AS 'data_size_mb',
                index_length / 1024 / 1024 AS 'index_size_mb',
                create_time,
                update_time
                FROM information_schema.TABLES 
                WHERE table_schema = DATABASE()
                ORDER BY data_length DESC");
            $info['table_list'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // 数据库变量
            $stmt = $db->query("SHOW VARIABLES WHERE Variable_name IN (
                'max_connections', 'connect_timeout', 'wait_timeout',
                'max_allowed_packet', 'innodb_buffer_pool_size', 'innodb_log_file_size',
                'query_cache_size', 'key_buffer_size', 'max_heap_table_size',
                'character_set_server', 'collation_server'
            )");
            $variables = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $variables[$row['Variable_name']] = $row['Value'];
            }
            $info['variables'] = $variables;
            
            // 数据库状态
            $stmt = $db->query("SHOW STATUS WHERE Variable_name IN (
                'Uptime', 'Threads_connected', 'Threads_running', 'Queries',
                'Slow_queries', 'Opened_tables', 'Created_tmp_tables',
                'Handler_read_first', 'Handler_read_key', 'Handler_read_next',
                'Handler_read_rnd', 'Handler_read_rnd_next', 'Com_select',
                'Com_insert', 'Com_update', 'Com_delete'
            )");
            $status = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $status[$row['Variable_name']] = $row['Value'];
            }
            $info['status'] = $status;
            
            return $info;
        } catch (\Exception $e) {
            Logger::error('获取数据库信息失败: ' . $e->getMessage());
            return [
                'error' => '获取数据库信息失败: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 获取内存信息
     * 
     * @return array|null 内存信息
     */
    private function getMemoryInfo()
    {
        if (function_exists('shell_exec')) {
            if (PHP_OS === 'Linux') {
                $memInfo = shell_exec('free -b');
                if ($memInfo) {
                    $lines = explode("\n", $memInfo);
                    $parts = preg_split('/\s+/', $lines[1]);
                    
                    return [
                        'total' => $this->formatBytes($parts[1]),
                        'used' => $this->formatBytes($parts[2]),
                        'free' => $this->formatBytes($parts[3]),
                        'usage' => round($parts[2] / $parts[1] * 100, 2) . '%'
                    ];
                }
            } elseif (PHP_OS === 'WINNT' || PHP_OS === 'WIN32' || PHP_OS === 'Windows') {
                $memInfo = shell_exec('wmic OS get TotalVisibleMemorySize,FreePhysicalMemory /Value');
                if ($memInfo) {
                    preg_match('/TotalVisibleMemorySize=(\d+)/i', $memInfo, $total);
                    preg_match('/FreePhysicalMemory=(\d+)/i', $memInfo, $free);
                    
                    if (isset($total[1]) && isset($free[1])) {
                        $total = $total[1] * 1024;
                        $free = $free[1] * 1024;
                        $used = $total - $free;
                        
                        return [
                            'total' => $this->formatBytes($total),
                            'used' => $this->formatBytes($used),
                            'free' => $this->formatBytes($free),
                            'usage' => round($used / $total * 100, 2) . '%'
                        ];
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * 获取CPU信息
     * 
     * @return array|null CPU信息
     */
    private function getCpuInfo()
    {
        if (function_exists('shell_exec')) {
            if (PHP_OS === 'Linux') {
                $cpuInfo = shell_exec('cat /proc/cpuinfo | grep "model name" | head -1');
                $cores = shell_exec('nproc');
                
                if ($cpuInfo && $cores) {
                    preg_match('/model name\s*:\s*(.+)/', $cpuInfo, $model);
                    
                    return [
                        'model' => $model[1] ?? 'Unknown',
                        'cores' => trim($cores)
                    ];
                }
            } elseif (PHP_OS === 'WINNT' || PHP_OS === 'WIN32' || PHP_OS === 'Windows') {
                $cpuInfo = shell_exec('wmic cpu get Name,NumberOfCores /Value');
                
                if ($cpuInfo) {
                    preg_match('/Name=(.+)/i', $cpuInfo, $model);
                    preg_match('/NumberOfCores=(\d+)/i', $cpuInfo, $cores);
                    
                    return [
                        'model' => $model[1] ?? 'Unknown',
                        'cores' => $cores[1] ?? 'Unknown'
                    ];
                }
            }
        }
        
        return null;
    }
    
    /**
     * 获取服务器运行时间
     * 
     * @return string 运行时间
     */
    private function getServerUptime()
    {
        if (function_exists('shell_exec')) {
            if (PHP_OS === 'Linux') {
                $uptime = shell_exec('uptime -p');
                return $uptime ? trim($uptime) : 'Unknown';
            } elseif (PHP_OS === 'WINNT' || PHP_OS === 'WIN32' || PHP_OS === 'Windows') {
                $uptime = shell_exec('net stats srv | find "Statistics since"');
                return $uptime ? trim(str_replace('Statistics since', '', $uptime)) : 'Unknown';
            }
        }
        
        return 'Unknown';
    }
    
    /**
     * 格式化字节数为人类可读格式
     * 
     * @param int $bytes 字节数
     * @param int $precision 精度
     * @return string 格式化后的字符串
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
} 