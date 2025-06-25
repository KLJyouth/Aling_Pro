<?php // Security Controller

namespace App\Controllers;

use App\Core\Controller;

/**
 * ��ȫ���������
 * ������ȫ�����������
 */
class SecurityController extends Controller
{
    /**
     * ��ʾ��ȫ������ҳ
     * @return void
     */
    public function index()
    {
        // ��ȡ��ȫ��������
        $securityOverview = $this->getSecurityOverview(];
        
        // ��ȡ����İ�ȫ�¼�
        $recentEvents = $this->getRecentSecurityEvents(];
        
        // ��ȡ��ȫ�����
        $securityChecks = $this->getSecurityChecks(];
        
        // ��Ⱦ��ͼ
        $this->view('security.index', [
            'securityOverview' => $securityOverview,
            'recentEvents' => $recentEvents,
            'securityChecks' => $securityChecks,
            'pageTitle' => 'IT��ά���� - ��ȫ����'
        ]];
    }
    
    /**
     * ��ʾȨ�޹���ҳ��
     * @return void
     */
    public function permissions()
    {
        // ��ȡ�ļ�Ȩ���б�
        $filePermissions = $this->getFilePermissions(];
        
        // ��ȡĿ¼Ȩ���б�
        $dirPermissions = $this->getDirectoryPermissions(];
        
        // ��Ⱦ��ͼ
        $this->view('security.permissions', [
            'filePermissions' => $filePermissions,
            'dirPermissions' => $dirPermissions,
            'pageTitle' => 'IT��ά���� - Ȩ�޹���'
        ]];
    }
    
    /**
     * ��ʾ���ݹ���ҳ��
     * @return void
     */
    public function backups()
    {
        // ��ȡ�����б�
        $backups = $this->getBackups(];
        
        // ��ȡ��������
        $backupConfig = $this->getBackupConfig(];
        
        // ��Ⱦ��ͼ
        $this->view('security.backups', [
            'backups' => $backups,
            'backupConfig' => $backupConfig,
            'pageTitle' => 'IT��ά���� - ���ݹ���'
        ]];
    }
    
    /**
     * ��ʾ�û�����ҳ��
     * @return void
     */
    public function users()
    {
        // ��ȡ�û��б�
        $users = $this->getUsers(];
        
        // ��Ⱦ��ͼ
        $this->view('security.users', [
            'users' => $users,
            'pageTitle' => 'IT��ά���� - �û�����'
        ]];
    }
    
    /**
     * ��ʾ��ɫ����ҳ��
     * @return void
     */
    public function roles()
    {
        // ��ȡ��ɫ�б�
        $roles = $this->getRoles(];
        
        // ��Ⱦ��ͼ
        $this->view('security.roles', [
            'roles' => $roles,
            'pageTitle' => 'IT��ά���� - ��ɫ����'
        ]];
    }
    
    /**
     * ��������
     * @return void
     */
    public function createBackup()
    {
        // ��ȡ��������
        $type = $this->input('type', 'full'];
        
        // ��ȡ��������
        $description = $this->input('description', ''];
        
        // ��������
        $result = $this->executeBackup($type, $description];
        
        // ����JSON���
        $this->json($result];
    }
    
    /**
     * �ָ�����
     * @return void
     */
    public function restoreBackup()
    {
        // ��ȡ����ID
        $backupId = $this->input('backup_id'];
        
        // ��֤����
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
        
        // �ָ�����
        $result = $this->executeRestore($backupId];
        
        // ����JSON���
        $this->json($result];
    }
    
    /**
     * ��ȡ��ȫ��������
     * @return array ��ȫ��������
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
     * ���㰲ȫ����
     * @return int ��ȫ����
     */
    private function calculateSecurityScore()
    {
        // ģ�ⰲȫ���ּ���
        return rand(60, 95];
    }
    
    /**
     * ͳ��©������
     * @return int ©������
     */
    private function countVulnerabilities()
    {
        // ģ��©��ͳ��
        return rand(0, 15];
    }
    
    /**
     * ��ȡ����İ�ȫ�¼�
     * @param int $limit �¼���������
     * @return array ��ȫ�¼��б�
     */
    private function getRecentSecurityEvents($limit = 10)
    {
        // ģ�ⰲȫ�¼�����
        $eventTypes = ['��¼ʧ��', 'Ȩ�ޱ��', '�ļ��޸�', '���ø���', '���ݴ���', '���ݻָ�'];
        $severities = ['��', '��', '��', '��Ϣ'];
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
                'description' => '��ȫ�¼�' . ($i + 1)
            ];
        }
        
        // ��ʱ�������
        usort($events, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']];
        }];
        
        return $events;
    }
    
    /**
     * ��ȡ��ȫ�����
     * @return array ��ȫ�����
     */
    private function getSecurityChecks()
    {
        // ģ�ⰲȫ�����
        return [
            [
                'name' => '�ļ�Ȩ�޼��',
                'status' => rand(0, 1) ? 'pass' : 'fail',
                'description' => '���ؼ��ļ���Ȩ�������Ƿ�ȫ',
                'lastCheck' => date('Y-m-d H:i:s', time() - rand(0, 86400))
            ], 
            [
                'name' => '����ǿ�ȼ��',
                'status' => rand(0, 1) ? 'pass' : 'fail',
                'description' => '����û������Ƿ����ǿ��Ҫ��',
                'lastCheck' => date('Y-m-d H:i:s', time() - rand(0, 86400))
            ], 
            [
                'name' => '���������Լ��',
                'status' => rand(0, 1) ? 'pass' : 'fail',
                'description' => '��鱸���ļ���������',
                'lastCheck' => date('Y-m-d H:i:s', time() - rand(0, 86400))
            ], 
            [
                'name' => '���ð�ȫ���',
                'status' => rand(0, 1) ? 'pass' : 'fail',
                'description' => '���ϵͳ�����Ƿ�ȫ',
                'lastCheck' => date('Y-m-d H:i:s', time() - rand(0, 86400))
            ], 
            [
                'name' => '��־��Ƽ��',
                'status' => rand(0, 1) ? 'pass' : 'fail',
                'description' => '���ϵͳ��־�Ƿ�������¼',
                'lastCheck' => date('Y-m-d H:i:s', time() - rand(0, 86400))
            ]
        ];
    }
    
    /**
     * ��ȡ�ļ�Ȩ���б�
     * @return array �ļ�Ȩ���б�
     */
    private function getFilePermissions()
    {
        $baseDir = BASE_PATH;
        $files = [];
        
        // ��ȡ��Ҫ�ļ�
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
     * ��ȡĿ¼Ȩ���б�
     * @return array Ŀ¼Ȩ���б�
     */
    private function getDirectoryPermissions()
    {
        $baseDir = BASE_PATH;
        $directories = [];
        
        // ��ȡ��ҪĿ¼
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
     * ��ʽ��Ȩ��Ϊ�ɶ��ַ���
     * @param int $perms Ȩ��ֵ
     * @return string ��ʽ�����Ȩ���ַ���
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
     * ��ȡ�ļ�������
     * @param string $file �ļ�·��
     * @return string �ļ�������
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
     * ��ȡ�ļ�������
     * @param string $file �ļ�·��
     * @return string �ļ�������
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
     * ���Ȩ���Ƿ�ȫ
     * @param int $perms ʵ��Ȩ��ֵ
     * @param int $recommended �Ƽ�Ȩ��ֵ
     * @return bool �Ƿ�ȫ
     */
    private function checkPermissionSecurity($perms, $recommended)
    {
        // �򵥼���Ƿ��ж����Ȩ��
        return ($perms & ~$recommended) == 0;
    }
    
    /**
     * ��ȡ�����б�
     * @return array �����б�
     */
    private function getBackups()
    {
        // ģ�ⱸ������
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
        
        // ����������
        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']];
        }];
        
        return $backups;
    }
    
    /**
     * ��ȡ��������
     * @return array ��������
     */
    private function getBackupConfig()
    {
        // ģ�ⱸ������
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
     * ִ�б���
     * @param string $type ��������
     * @param string $description ��������
     * @return array ִ�н��
     */
    private function executeBackup($type, $description)
    {
        // ģ�ⱸ�ݹ���
        sleep(1];
        
        // ��¼��־
        $this->logSecurityEvent('���ݴ���', '������' . $type . '���͵ı���', 'info'];
        
        return [
            'success' => true,
            'message' => '���ݴ����ɹ�',
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
     * ִ�лָ�
     * @param string $backupId ����ID
     * @return array ִ�н��
     */
    private function executeRestore($backupId)
    {
        // ģ��ָ�����
        sleep(2];
        
        // ��¼��־
        $this->logSecurityEvent('���ݻָ�', '�ָ��˱���: ' . $backupId, 'warning'];
        
        return [
            'success' => true,
            'message' => '���ݻָ��ɹ�',
            'details' => [
                'restored_at' => date('Y-m-d H:i:s'],
                'backup_id' => $backupId
            ]
        ];
    }
    
    /**
     * ��ȡ�û��б�
     * @return array �û��б�
     */
    private function getUsers()
    {
        // ģ���û�����
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
     * ��ȡ��ɫ�б�
     * @return array ��ɫ�б�
     */
    private function getRoles()
    {
        // ģ���ɫ����
        return [
            [
                'id' => 1,
                'name' => 'administrator',
                'description' => 'ϵͳ����Ա��ӵ������Ȩ��',
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
                'description' => '����Ա��ӵ�д󲿷ֹ���Ȩ��',
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
                'description' => '������Ա��ӵ�й��ߺͼ��Ȩ��',
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
                'description' => '�ÿͣ�ֻ�в鿴Ȩ��',
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
     * ��¼��ȫ�¼�
     * @param string $type �¼�����
     * @param string $description �¼�����
     * @param string $severity �¼����س̶�
     * @return void
     */
    private function logSecurityEvent($type, $description, $severity = 'info')
    {
        $logsDir = BASE_PATH . '/storage/logs';
        
        // ȷ����־Ŀ¼����
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true];
        }
        
        $logFile = $logsDir . '/security_events.log';
        
        // ׼����־����
        $timestamp = date('Y-m-d H:i:s'];
        $user = 'system'; // ��ʵ��Ӧ���У���Ӧ���ǵ�ǰ��¼���û�
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        $logContent = "[{$timestamp}] [{$severity}] [{$type}] [{$user}] [{$ip}] {$description}\n";
        
        // д����־
        file_put_contents($logFile, $logContent, FILE_APPEND];
    }
}

