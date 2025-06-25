<?php // Security Controller

namespace App\Controllers;

use App\Core\Controller;

/**
 * 安全管理控制器
 * 负责处理安全管理相关请求
 */
class SecurityController extends Controller
{
    /**
     * 显示安全管理首页
     * @return void
     */
    public function index()
    {
        // 获取安全概览数据
        $securityOverview = $this->getSecurityOverview(];
        
        // 获取最近的安全事件
        $recentEvents = $this->getRecentSecurityEvents(];
        
        // 获取安全检查结果
        $securityChecks = $this->getSecurityChecks(];
        
        // 渲染视图
        $this->view('security.index', [
            'securityOverview' => $securityOverview,
            'recentEvents' => $recentEvents,
            'securityChecks' => $securityChecks,
            'pageTitle' => 'IT运维中心 - 安全管理'
        ]];
    }
    
    /**
     * 显示权限管理页面
     * @return void
     */
    public function permissions()
    {
        // 获取文件权限列表
        $filePermissions = $this->getFilePermissions(];
        
        // 获取目录权限列表
        $dirPermissions = $this->getDirectoryPermissions(];
        
        // 渲染视图
        $this->view('security.permissions', [
            'filePermissions' => $filePermissions,
            'dirPermissions' => $dirPermissions,
            'pageTitle' => 'IT运维中心 - 权限管理'
        ]];
    }
    
    /**
     * 显示备份管理页面
     * @return void
     */
    public function backups()
    {
        // 获取备份列表
        $backups = $this->getBackups(];
        
        // 获取备份配置
        $backupConfig = $this->getBackupConfig(];
        
        // 渲染视图
        $this->view('security.backups', [
            'backups' => $backups,
            'backupConfig' => $backupConfig,
            'pageTitle' => 'IT运维中心 - 备份管理'
        ]];
    }
    
    /**
     * 显示用户管理页面
     * @return void
     */
    public function users()
    {
        // 获取用户列表
        $users = $this->getUsers(];
        
        // 渲染视图
        $this->view('security.users', [
            'users' => $users,
            'pageTitle' => 'IT运维中心 - 用户管理'
        ]];
    }
    
    /**
     * 显示角色管理页面
     * @return void
     */
    public function roles()
    {
        // 获取角色列表
        $roles = $this->getRoles(];
        
        // 渲染视图
        $this->view('security.roles', [
            'roles' => $roles,
            'pageTitle' => 'IT运维中心 - 角色管理'
        ]];
    }
    
    /**
     * 创建备份
     * @return void
     */
    public function createBackup()
    {
        // 获取备份类型
        $type = $this->input('type', 'full'];
        
        // 获取备份描述
        $description = $this->input('description', ''];
        
        // 创建备份
        $result = $this->executeBackup($type, $description];
        
        // 返回JSON结果
        $this->json($result];
    }
    
    /**
     * 恢复备份
     * @return void
     */
    public function restoreBackup()
    {
        // 获取备份ID
        $backupId = $this->input('backup_id'];
        
        // 验证参数
        $errors = $this->validate([
            'backup_id' => 'required'
        ]];
        
        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'errors' => $errors
            ],  400];
            return;
        }
        
        // 恢复备份
        $result = $this->executeRestore($backupId];
        
        // 返回JSON结果
        $this->json($result];
    }
    
    /**
     * 获取安全概览数据
     * @return array 安全概览数据
     */
    private function getSecurityOverview()
    {
        return [
            'securityScore' => $this->calculateSecurityScore(),
            'vulnerabilities' => $this->countVulnerabilities(),
            'lastScan' => date('Y-m-d H:i:s', time() - rand(0, 86400)],
            'criticalIssues' => rand(0, 5],
            'warningIssues' => rand(1, 10],
            'infoIssues' => rand(5, 20)
        ];
    }
    
    /**
     * 计算安全评分
     * @return int 安全评分
     */
    private function calculateSecurityScore()
    {
        // 模拟安全评分计算
        return rand(60, 95];
    }
    
    /**
     * 统计漏洞数量
     * @return int 漏洞数量
     */
    private function countVulnerabilities()
    {
        // 模拟漏洞统计
        return rand(0, 15];
    }
    
    /**
     * 获取最近的安全事件
     * @param int $limit 事件数量限制
     * @return array 安全事件列表
     */
    private function getRecentSecurityEvents($limit = 10)
    {
        // 模拟安全事件数据
        $eventTypes = ['登录失败', '权限变更', '文件修改', '配置更改', '备份创建', '备份恢复'];
        $severities = ['高', '中', '低', '信息'];
        $users = ['admin', 'system', 'user1', 'user2', 'guest'];
        
        $events = [];
        for ($i = 0; $i < $limit; $i++) {
            $events[] = [
                'id' => uniqid(),
                'type' => $eventTypes[array_rand($eventTypes)], 
                'severity' => $severities[array_rand($severities)], 
                'user' => $users[array_rand($users)], 
                'ip' => '192.168.' . rand(1, 254) . '.' . rand(1, 254],
                'timestamp' => date('Y-m-d H:i:s', time() - rand(0, 86400 * 7)],
                'description' => '安全事件' . ($i + 1)
            ];
        }
        
        // 按时间戳排序
        usort($events, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']];
        }];
        
        return $events;
    }
    
    /**
     * 获取安全检查结果
     * @return array 安全检查结果
     */
    private function getSecurityChecks()
    {
        // 模拟安全检查结果
        return [
            [
                'name' => '文件权限检查',
                'status' => rand(0, 1) ? 'pass' : 'fail',
                'description' => '检查关键文件的权限设置是否安全',
                'lastCheck' => date('Y-m-d H:i:s', time() - rand(0, 86400))
            ], 
            [
                'name' => '密码强度检查',
                'status' => rand(0, 1) ? 'pass' : 'fail',
                'description' => '检查用户密码是否符合强度要求',
                'lastCheck' => date('Y-m-d H:i:s', time() - rand(0, 86400))
            ], 
            [
                'name' => '备份完整性检查',
                'status' => rand(0, 1) ? 'pass' : 'fail',
                'description' => '检查备份文件的完整性',
                'lastCheck' => date('Y-m-d H:i:s', time() - rand(0, 86400))
            ], 
            [
                'name' => '配置安全检查',
                'status' => rand(0, 1) ? 'pass' : 'fail',
                'description' => '检查系统配置是否安全',
                'lastCheck' => date('Y-m-d H:i:s', time() - rand(0, 86400))
            ], 
            [
                'name' => '日志审计检查',
                'status' => rand(0, 1) ? 'pass' : 'fail',
                'description' => '检查系统日志是否正常记录',
                'lastCheck' => date('Y-m-d H:i:s', time() - rand(0, 86400))
            ]
        ];
    }
    
    /**
     * 获取文件权限列表
     * @return array 文件权限列表
     */
    private function getFilePermissions()
    {
        $baseDir = BASE_PATH;
        $files = [];
        
        // 获取重要文件
        $importantFiles = [
            '/index.php',
            '/config/app.php',
            '/public/index.php',
            '/app/Core/Controller.php',
            '/app/Core/Router.php'
        ];
        
        foreach ($importantFiles as $file) {
            $fullPath = $baseDir . $file;
            if (file_exists($fullPath)) {
                $perms = fileperms($fullPath];
                $files[] = [
                    'path' => $file,
                    'permissions' => $this->formatPermissions($perms],
                    'owner' => $this->getFileOwner($fullPath],
                    'group' => $this->getFileGroup($fullPath],
                    'recommended' => '644',
                    'status' => $this->checkPermissionSecurity($perms, 0644) ? 'secure' : 'insecure'
                ];
            }
        }
        
        return $files;
    }
    
    /**
     * 获取目录权限列表
     * @return array 目录权限列表
     */
    private function getDirectoryPermissions()
    {
        $baseDir = BASE_PATH;
        $directories = [];
        
        // 获取重要目录
        $importantDirs = [
            '/app',
            '/config',
            '/public',
            '/resources',
            '/storage'
        ];
        
        foreach ($importantDirs as $dir) {
            $fullPath = $baseDir . $dir;
            if (is_dir($fullPath)) {
                $perms = fileperms($fullPath];
                $directories[] = [
                    'path' => $dir,
                    'permissions' => $this->formatPermissions($perms],
                    'owner' => $this->getFileOwner($fullPath],
                    'group' => $this->getFileGroup($fullPath],
                    'recommended' => '755',
                    'status' => $this->checkPermissionSecurity($perms, 0755) ? 'secure' : 'insecure'
                ];
            }
        }
        
        return $directories;
    }
    
    /**
     * 格式化权限为可读字符串
     * @param int $perms 权限值
     * @return string 格式化后的权限字符串
     */
    private function formatPermissions($perms)
    {
        if (($perms & 0xC000) == 0xC000) {
            // Socket
            $info = 's';
        } elseif (($perms & 0xA000) == 0xA000) {
            // Symbolic Link
            $info = 'l';
        } elseif (($perms & 0x8000) == 0x8000) {
            // Regular
            $info = '-';
        } elseif (($perms & 0x6000) == 0x6000) {
            // Block special
            $info = 'b';
        } elseif (($perms & 0x4000) == 0x4000) {
            // Directory
            $info = 'd';
        } elseif (($perms & 0x2000) == 0x2000) {
            // Character special
            $info = 'c';
        } elseif (($perms & 0x1000) == 0x1000) {
            // FIFO pipe
            $info = 'p';
        } else {
            // Unknown
            $info = 'u';
        }
        
        // Owner
        $info .= (($perms & 0x0100) ? 'r' : '-'];
        $info .= (($perms & 0x0080) ? 'w' : '-'];
        $info .= (($perms & 0x0040) ?
                    (($perms & 0x0800) ? 's' : 'x' ) :
                    (($perms & 0x0800) ? 'S' : '-')];
        
        // Group
        $info .= (($perms & 0x0020) ? 'r' : '-'];
        $info .= (($perms & 0x0010) ? 'w' : '-'];
        $info .= (($perms & 0x0008) ?
                    (($perms & 0x0400) ? 's' : 'x' ) :
                    (($perms & 0x0400) ? 'S' : '-')];
        
        // World
        $info .= (($perms & 0x0004) ? 'r' : '-'];
        $info .= (($perms & 0x0002) ? 'w' : '-'];
        $info .= (($perms & 0x0001) ?
                    (($perms & 0x0200) ? 't' : 'x' ) :
                    (($perms & 0x0200) ? 'T' : '-')];
        
        return $info;
    }
    
    /**
     * 获取文件所有者
     * @param string $file 文件路径
     * @return string 文件所有者
     */
    private function getFileOwner($file)
    {
        if (function_exists('posix_getpwuid')) {
            $owner = posix_getpwuid(fileowner($file)];
            return $owner['name'] ?? 'unknown';
        }
        
        return 'unknown';
    }
    
    /**
     * 获取文件所属组
     * @param string $file 文件路径
     * @return string 文件所属组
     */
    private function getFileGroup($file)
    {
        if (function_exists('posix_getgrgid')) {
            $group = posix_getgrgid(filegroup($file)];
            return $group['name'] ?? 'unknown';
        }
        
        return 'unknown';
    }
    
    /**
     * 检查权限是否安全
     * @param int $perms 实际权限值
     * @param int $recommended 推荐权限值
     * @return bool 是否安全
     */
    private function checkPermissionSecurity($perms, $recommended)
    {
        // 简单检查是否有额外的权限
        return ($perms & ~$recommended) == 0;
    }
    
    /**
     * 获取备份列表
     * @return array 备份列表
     */
    private function getBackups()
    {
        // 模拟备份数据
        $backups = [];
        $types = ['full', 'database', 'files', 'config'];
        $statuses = ['completed', 'in_progress', 'failed'];
        
        for ($i = 0; $i < 10; $i++) {
            $date = date('Y-m-d H:i:s', time() - rand(0, 86400 * 30)];
            $size = rand(10, 500];
            $type = $types[array_rand($types)];
            $status = $statuses[array_rand($statuses)];
            
            $backups[] = [
                'id' => 'backup_' . ($i + 1],
                'name' => 'Backup_' . date('Ymd_His', strtotime($date)],
                'type' => $type,
                'date' => $date,
                'size' => $size . ' MB',
                'status' => $status,
                'description' => $type . ' backup created on ' . $date
            ];
        }
        
        // 按日期排序
        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']];
        }];
        
        return $backups;
    }
    
    /**
     * 获取备份配置
     * @return array 备份配置
     */
    private function getBackupConfig()
    {
        // 模拟备份配置
        return [
            'backup_path' => BASE_PATH . '/storage/backups',
            'auto_backup' => true,
            'backup_frequency' => 'daily',
            'backup_time' => '03:00',
            'keep_backups' => 10,
            'compress_backups' => true,
            'backup_types' => [
                'database' => true,
                'files' => true,
                'config' => true
            ]
        ];
    }
    
    /**
     * 执行备份
     * @param string $type 备份类型
     * @param string $description 备份描述
     * @return array 执行结果
     */
    private function executeBackup($type, $description)
    {
        // 模拟备份过程
        sleep(1];
        
        // 记录日志
        $this->logSecurityEvent('备份创建', '创建了' . $type . '类型的备份', 'info'];
        
        return [
            'success' => true,
            'message' => '备份创建成功',
            'backup' => [
                'id' => 'backup_' . uniqid(),
                'name' => 'Backup_' . date('Ymd_His'],
                'type' => $type,
                'date' => date('Y-m-d H:i:s'],
                'size' => rand(10, 500) . ' MB',
                'status' => 'completed',
                'description' => $description
            ]
        ];
    }
    
    /**
     * 执行恢复
     * @param string $backupId 备份ID
     * @return array 执行结果
     */
    private function executeRestore($backupId)
    {
        // 模拟恢复过程
        sleep(2];
        
        // 记录日志
        $this->logSecurityEvent('备份恢复', '恢复了备份: ' . $backupId, 'warning'];
        
        return [
            'success' => true,
            'message' => '备份恢复成功',
            'details' => [
                'restored_at' => date('Y-m-d H:i:s'],
                'backup_id' => $backupId
            ]
        ];
    }
    
    /**
     * 获取用户列表
     * @return array 用户列表
     */
    private function getUsers()
    {
        // 模拟用户数据
        return [
            [
                'id' => 1,
                'username' => 'admin',
                'email' => 'admin@example.com',
                'role' => 'administrator',
                'status' => 'active',
                'last_login' => date('Y-m-d H:i:s', time() - rand(0, 86400))
            ], 
            [
                'id' => 2,
                'username' => 'manager',
                'email' => 'manager@example.com',
                'role' => 'manager',
                'status' => 'active',
                'last_login' => date('Y-m-d H:i:s', time() - rand(0, 86400 * 2))
            ], 
            [
                'id' => 3,
                'username' => 'developer',
                'email' => 'developer@example.com',
                'role' => 'developer',
                'status' => 'active',
                'last_login' => date('Y-m-d H:i:s', time() - rand(0, 86400 * 3))
            ], 
            [
                'id' => 4,
                'username' => 'guest',
                'email' => 'guest@example.com',
                'role' => 'guest',
                'status' => 'inactive',
                'last_login' => date('Y-m-d H:i:s', time() - rand(0, 86400 * 10))
            ]
        ];
    }
    
    /**
     * 获取角色列表
     * @return array 角色列表
     */
    private function getRoles()
    {
        // 模拟角色数据
        return [
            [
                'id' => 1,
                'name' => 'administrator',
                'description' => '系统管理员，拥有所有权限',
                'permissions' => [
                    'dashboard' => ['view', 'manage'], 
                    'tools' => ['view', 'run', 'manage'], 
                    'monitoring' => ['view', 'manage'], 
                    'security' => ['view', 'manage'], 
                    'reports' => ['view', 'generate', 'manage'], 
                    'logs' => ['view', 'manage'], 
                    'users' => ['view', 'create', 'edit', 'delete'], 
                    'roles' => ['view', 'create', 'edit', 'delete'], 
                    'backups' => ['view', 'create', 'restore', 'delete']
                ]
            ], 
            [
                'id' => 2,
                'name' => 'manager',
                'description' => '管理员，拥有大部分管理权限',
                'permissions' => [
                    'dashboard' => ['view', 'manage'], 
                    'tools' => ['view', 'run'], 
                    'monitoring' => ['view', 'manage'], 
                    'security' => ['view'], 
                    'reports' => ['view', 'generate'], 
                    'logs' => ['view'], 
                    'users' => ['view'], 
                    'roles' => ['view'], 
                    'backups' => ['view', 'create']
                ]
            ], 
            [
                'id' => 3,
                'name' => 'developer',
                'description' => '开发人员，拥有工具和监控权限',
                'permissions' => [
                    'dashboard' => ['view'], 
                    'tools' => ['view', 'run'], 
                    'monitoring' => ['view'], 
                    'security' => ['view'], 
                    'reports' => ['view'], 
                    'logs' => ['view'], 
                    'users' => [], 
                    'roles' => [], 
                    'backups' => ['view']
                ]
            ], 
            [
                'id' => 4,
                'name' => 'guest',
                'description' => '访客，只有查看权限',
                'permissions' => [
                    'dashboard' => ['view'], 
                    'tools' => ['view'], 
                    'monitoring' => ['view'], 
                    'security' => [], 
                    'reports' => ['view'], 
                    'logs' => [], 
                    'users' => [], 
                    'roles' => [], 
                    'backups' => []
                ]
            ]
        ];
    }
    
    /**
     * 记录安全事件
     * @param string $type 事件类型
     * @param string $description 事件描述
     * @param string $severity 事件严重程度
     * @return void
     */
    private function logSecurityEvent($type, $description, $severity = 'info')
    {
        $logsDir = BASE_PATH . '/storage/logs';
        
        // 确保日志目录存在
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true];
        }
        
        $logFile = $logsDir . '/security_events.log';
        
        // 准备日志内容
        $timestamp = date('Y-m-d H:i:s'];
        $user = 'system'; // 在实际应用中，这应该是当前登录的用户
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        $logContent = "[{$timestamp}] [{$severity}] [{$type}] [{$user}] [{$ip}] {$description}\n";
        
        // 写入日志
        file_put_contents($logFile, $logContent, FILE_APPEND];
    }
}

