<?php
/**
 * 安全管理器类 - 提供全面的安全防护与攻击检测功能
 * @version 1.0.0
 * @author AlingAi Team
 */

namespace AlingAi\Admin\Security;

use PDO;
use Exception;
use DateTime;

class SecurityManager
{
    private $db;
    private $logger;
    private $config;
    private $securityScore = 0;
    private $threatLevel = 'low';
    private $activeThreats = [];
    private $securityEvents = [];
    
    /**
     * 构造函数
     */
    public function __construct($db = null, $logger = null)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->loadConfig();
        $this->initializeSecuritySystem();
    }
    
    /**
     * 加载安全配置
     */
    private function loadConfig(): void
    {
        try {
            $configFile = dirname(dirname(dirname(dirname(__DIR__)))) . '/config/security_config.php';
            if (file_exists($configFile)) {
                $this->config = require $configFile;
            } else {
                // 使用默认配置
                $this->config = [
                    'security_level' => 'high',
                    'max_login_attempts' => 5,
                    'lockout_time' => 15, // 分钟
                    'password_policy' => [
                        'min_length' => 12,
                        'require_uppercase' => true,
                        'require_lowercase' => true,
                        'require_number' => true,
                        'require_special' => true
                    ],
                    'session_timeout' => 30, // 分钟
                    'ip_whitelist' => [],
                    'ip_blacklist' => [],
                    'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'],
                    'max_file_size' => 10 * 1024 * 1024, // 10MB
                    'csrf_protection' => true,
                    'xss_protection' => true,
                    'sql_injection_protection' => true,
                    'rate_limiting' => [
                        'enabled' => true,
                        'requests_per_minute' => 60
                    ]
                ];
            }
        } catch (Exception $e) {
            $this->logError('加载安全配置失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 初始化安全系统
     */
    private function initializeSecuritySystem(): void
    {
        try {
            // 创建必要的数据库表
            $this->createSecurityTables();
            
            // 加载活跃威胁
            $this->loadActiveThreats();
            
            // 加载最近安全事件
            $this->loadRecentSecurityEvents();
            
            // 计算安全评分
            $this->calculateSecurityScore();
        } catch (Exception $e) {
            $this->logError('初始化安全系统失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 创建安全相关数据库表
     */
    private function createSecurityTables(): void
    {
        if (!$this->db) {
            return;
        }
        
        try {
            // 安全事件表
            $this->db->exec("CREATE TABLE IF NOT EXISTS security_events (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                event_type VARCHAR(50) NOT NULL,
                severity VARCHAR(20) NOT NULL,
                description TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                user_id INTEGER,
                request_data TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            
            // IP黑名单表
            $this->db->exec("CREATE TABLE IF NOT EXISTS ip_blacklist (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ip_address VARCHAR(45) NOT NULL,
                reason TEXT,
                block_until DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            
            // 登录尝试表
            $this->db->exec("CREATE TABLE IF NOT EXISTS login_attempts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(255) NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT,
                success BOOLEAN NOT NULL,
                attempt_time DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            
            // 安全审计日志表
            $this->db->exec("CREATE TABLE IF NOT EXISTS security_audit_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                action VARCHAR(100) NOT NULL,
                description TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                severity VARCHAR(20) DEFAULT 'info',
                status VARCHAR(20),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            
            // 文件上传日志表
            $this->db->exec("CREATE TABLE IF NOT EXISTS file_upload_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                filename VARCHAR(255) NOT NULL,
                original_filename VARCHAR(255) NOT NULL,
                file_size INTEGER NOT NULL,
                file_type VARCHAR(100) NOT NULL,
                file_hash VARCHAR(64) NOT NULL,
                ip_address VARCHAR(45),
                status VARCHAR(20) NOT NULL,
                scan_result VARCHAR(20),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
        } catch (Exception $e) {
            $this->logError('创建安全数据库表失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 加载活跃威胁
     */
    private function loadActiveThreats(): void
    {
        if (!$this->db) {
            return;
        }
        
        try {
            $stmt = $this->db->query("
                SELECT event_type, severity, COUNT(*) as count, MAX(created_at) as latest
                FROM security_events
                WHERE created_at > datetime('now', '-24 hours')
                GROUP BY event_type, severity
                ORDER BY severity DESC, count DESC
                LIMIT 10
            ");
            
            $this->activeThreats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 确定威胁级别
            $criticalCount = 0;
            $highCount = 0;
            
            foreach ($this->activeThreats as $threat) {
                if ($threat['severity'] === 'critical') {
                    $criticalCount += $threat['count'];
                } else if ($threat['severity'] === 'high') {
                    $highCount += $threat['count'];
                }
            }
            
            if ($criticalCount > 0) {
                $this->threatLevel = 'critical';
            } else if ($highCount > 5) {
                $this->threatLevel = 'high';
            } else if ($highCount > 0) {
                $this->threatLevel = 'medium';
            } else {
                $this->threatLevel = 'low';
            }
        } catch (Exception $e) {
            $this->logError('加载活跃威胁失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 加载最近安全事件
     */
    private function loadRecentSecurityEvents(): void
    {
        if (!$this->db) {
            return;
        }
        
        try {
            $stmt = $this->db->query("
                SELECT * FROM security_events
                ORDER BY created_at DESC
                LIMIT 50
            ");
            
            $this->securityEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logError('加载最近安全事件失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 计算安全评分
     */
    private function calculateSecurityScore(): void
    {
        $score = 100;
        
        // 根据活跃威胁扣分
        foreach ($this->activeThreats as $threat) {
            if ($threat['severity'] === 'critical') {
                $score -= 10 * min($threat['count'], 5);
            } else if ($threat['severity'] === 'high') {
                $score -= 5 * min($threat['count'], 10);
            } else if ($threat['severity'] === 'medium') {
                $score -= 2 * min($threat['count'], 15);
            } else {
                $score -= 0.5 * min($threat['count'], 20);
            }
        }
        
        // 检查安全配置
        if (!$this->config['csrf_protection']) {
            $score -= 15;
        }
        
        if (!$this->config['xss_protection']) {
            $score -= 15;
        }
        
        if (!$this->config['sql_injection_protection']) {
            $score -= 15;
        }
        
        if (!$this->config['rate_limiting']['enabled']) {
            $score -= 10;
        }
        
        if ($this->config['password_policy']['min_length'] < 8) {
            $score -= 10;
        }
        
        if ($this->config['session_timeout'] > 60) {
            $score -= 5;
        }
        
        // 确保分数在0-100之间
        $this->securityScore = max(0, min(100, $score));
    }
    
    /**
     * 获取安全评分
     */
    public function getSecurityScore(): int
    {
        return $this->securityScore;
    }
    
    /**
     * 获取威胁级别
     */
    public function getThreatLevel(): string
    {
        return $this->threatLevel;
    }
    
    /**
     * 获取活跃威胁
     */
    public function getActiveThreats(): array
    {
        return $this->activeThreats;
    }
    
    /**
     * 获取最近安全事件
     */
    public function getSecurityEvents(): array
    {
        return $this->securityEvents;
    }
    
    /**
     * 记录安全事件
     */
    public function logSecurityEvent(string $eventType, string $severity, string $description, int $userId = null): void
    {
        if (!$this->db) {
            return;
        }
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO security_events 
                (event_type, severity, description, ip_address, user_agent, user_id, request_data)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $requestData = [
                'get' => $_GET ?? [],
                'post' => $_POST ?? [],
                'server' => [
                    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? '',
                    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? '',
                    'HTTP_REFERER' => $_SERVER['HTTP_REFERER'] ?? ''
                ]
            ];
            
            $stmt->execute([
                $eventType,
                $severity,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                $userId,
                json_encode($requestData)
            ]);
            
            // 如果是严重或高级别事件，重新计算威胁级别
            if ($severity === 'critical' || $severity === 'high') {
                $this->loadActiveThreats();
            }
        } catch (Exception $e) {
            $this->logError('记录安全事件失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 检查IP是否在黑名单中
     */
    public function isIpBlacklisted(string $ip = null): bool
    {
        if (!$this->db) {
            return false;
        }
        
        $ip = $ip ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM ip_blacklist
                WHERE ip_address = ?
                AND (block_until IS NULL OR block_until > CURRENT_TIMESTAMP)
            ");
            
            $stmt->execute([$ip]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            $this->logError('检查IP黑名单失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 将IP添加到黑名单
     */
    public function blacklistIp(string $ip, string $reason, int $blockMinutes = 0): bool
    {
        if (!$this->db) {
            return false;
        }
        
        try {
            $blockUntil = null;
            if ($blockMinutes > 0) {
                $date = new DateTime();
                $date->modify("+{$blockMinutes} minutes");
                $blockUntil = $date->format('Y-m-d H:i:s');
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO ip_blacklist (ip_address, reason, block_until)
                VALUES (?, ?, ?)
            ");
            
            return $stmt->execute([$ip, $reason, $blockUntil]);
        } catch (Exception $e) {
            $this->logError('添加IP到黑名单失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 从黑名单中移除IP
     */
    public function removeFromBlacklist(string $ip): bool
    {
        if (!$this->db) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("
                DELETE FROM ip_blacklist
                WHERE ip_address = ?
            ");
            
            return $stmt->execute([$ip]);
        } catch (Exception $e) {
            $this->logError('从黑名单移除IP失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取黑名单IP列表
     */
    public function getBlacklistedIps(): array
    {
        if (!$this->db) {
            return [];
        }
        
        try {
            $stmt = $this->db->query("
                SELECT * FROM ip_blacklist
                ORDER BY created_at DESC
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logError('获取黑名单IP列表失败: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 记录登录尝试
     */
    public function logLoginAttempt(string $username, bool $success): void
    {
        if (!$this->db) {
            return;
        }
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO login_attempts 
                (username, ip_address, user_agent, success)
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $username,
                $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                $success ? 1 : 0
            ]);
            
            // 检查是否需要锁定账户或IP
            if (!$success) {
                $this->checkLoginAttempts($username);
            }
        } catch (Exception $e) {
            $this->logError('记录登录尝试失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 检查登录尝试次数
     */
    private function checkLoginAttempts(string $username): void
    {
        if (!$this->db) {
            return;
        }
        
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $maxAttempts = $this->config['max_login_attempts'] ?? 5;
            $lockoutTime = $this->config['lockout_time'] ?? 15;
            
            // 检查最近的失败尝试
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as attempts
                FROM login_attempts
                WHERE username = ?
                AND ip_address = ?
                AND success = 0
                AND attempt_time > datetime('now', '-15 minutes')
            ");
            
            $stmt->execute([$username, $ip]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['attempts'] >= $maxAttempts) {
                // 超过最大尝试次数，锁定IP
                $this->blacklistIp($ip, "多次登录失败 ({$username})", $lockoutTime);
                
                // 记录安全事件
                $this->logSecurityEvent(
                    'login_brute_force',
                    'high',
                    "检测到可能的暴力破解尝试，用户名: {$username}, IP: {$ip}，已临时封禁"
                );
            }
        } catch (Exception $e) {
            $this->logError('检查登录尝试失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 验证CSRF令牌
     */
    public function validateCsrfToken(string $token): bool
    {
        if (!$this->config['csrf_protection']) {
            return true;
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * 生成CSRF令牌
     */
    public function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
    
    /**
     * 验证文件上传
     */
    public function validateFileUpload(array $file): array
    {
        $result = [
            'valid' => false,
            'error' => '',
            'safe_filename' => ''
        ];
        
        // 检查文件是否成功上传
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $result['error'] = '文件上传失败';
            return $result;
        }
        
        // 检查文件大小
        $maxSize = $this->config['max_file_size'] ?? (10 * 1024 * 1024);
        if ($file['size'] > $maxSize) {
            $result['error'] = '文件大小超过限制';
            return $result;
        }
        
        // 检查文件类型
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = $this->config['allowed_file_types'] ?? ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        
        if (!in_array($extension, $allowedTypes)) {
            $result['error'] = '不允许的文件类型';
            return $result;
        }
        
        // 检查文件内容
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        $allowedMimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'txt' => 'text/plain'
        ];
        
        if (!isset($allowedMimeTypes[$extension]) || $allowedMimeTypes[$extension] !== $mimeType) {
            $result['error'] = '文件内容与扩展名不匹配';
            return $result;
        }
        
        // 生成安全的文件名
        $safeFilename = bin2hex(random_bytes(16)) . '.' . $extension;
        
        $result['valid'] = true;
        $result['safe_filename'] = $safeFilename;
        
        return $result;
    }
    
    /**
     * 记录文件上传
     */
    public function logFileUpload(array $file, string $safeFilename, int $userId = null, string $status = 'success'): void
    {
        if (!$this->db) {
            return;
        }
        
        try {
            $fileHash = hash_file('sha256', $file['tmp_name']);
            
            $stmt = $this->db->prepare("
                INSERT INTO file_upload_log 
                (user_id, filename, original_filename, file_size, file_type, file_hash, ip_address, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $userId,
                $safeFilename,
                $file['name'],
                $file['size'],
                $file['type'],
                $fileHash,
                $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                $status
            ]);
        } catch (Exception $e) {
            $this->logError('记录文件上传失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 过滤输入
     */
    public function filterInput($input)
    {
        if (is_array($input)) {
            return array_map([$this, 'filterInput'], $input);
        }
        
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * 记录错误
     */
    private function logError(string $message): void
    {
        if ($this->logger) {
            $this->logger->error($message);
        } else {
            error_log($message);
        }
    }
    
    /**
     * 获取安全配置
     */
    public function getSecurityConfig(): array
    {
        return $this->config;
    }
    
    /**
     * 更新安全配置
     */
    public function updateSecurityConfig(array $config): bool
    {
        try {
            $this->config = array_merge($this->config, $config);
            
            $configFile = dirname(dirname(dirname(dirname(__DIR__)))) . '/config/security_config.php';
            $configDir = dirname($configFile);
            
            if (!is_dir($configDir)) {
                mkdir($configDir, 0755, true);
            }
            
            $content = "<?php\n\nreturn " . var_export($this->config, true) . ";\n";
            file_put_contents($configFile, $content);
            
            return true;
        } catch (Exception $e) {
            $this->logError('更新安全配置失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 执行安全扫描
     */
    public function performSecurityScan(): array
    {
        $results = [
            'scan_time' => date('Y-m-d H:i:s'),
            'vulnerabilities' => [],
            'recommendations' => []
        ];
        
        // 检查文件权限
        $this->scanFilePermissions($results);
        
        // 检查配置安全性
        $this->scanConfigSecurity($results);
        
        // 检查数据库安全性
        $this->scanDatabaseSecurity($results);
        
        // 检查已知漏洞
        $this->scanKnownVulnerabilities($results);
        
        return $results;
    }
    
    /**
     * 扫描文件权限
     */
    private function scanFilePermissions(array &$results): void
    {
        $webRoot = dirname(dirname(dirname(dirname(__DIR__))));
        $criticalFiles = [
            '/config',
            '/.env',
            '/vendor',
            '/composer.json',
            '/composer.lock'
        ];
        
        foreach ($criticalFiles as $file) {
            $fullPath = $webRoot . $file;
            if (file_exists($fullPath)) {
                $perms = fileperms($fullPath);
                $worldWritable = ($perms & 0x0002) !== 0;
                
                if ($worldWritable) {
                    $results['vulnerabilities'][] = [
                        'type' => 'file_permission',
                        'severity' => 'high',
                        'description' => "文件或目录 {$file} 对所有用户可写，存在安全风险"
                    ];
                    
                    $results['recommendations'][] = "修改 {$file} 的权限，移除全局写入权限";
                }
            }
        }
    }
    
    /**
     * 扫描配置安全性
     */
    private function scanConfigSecurity(array &$results): void
    {
        if (!$this->config['csrf_protection']) {
            $results['vulnerabilities'][] = [
                'type' => 'config',
                'severity' => 'high',
                'description' => "CSRF保护未启用，网站容易受到跨站请求伪造攻击"
            ];
            
            $results['recommendations'][] = "启用CSRF保护机制";
        }
        
        if (!$this->config['xss_protection']) {
            $results['vulnerabilities'][] = [
                'type' => 'config',
                'severity' => 'high',
                'description' => "XSS保护未启用，网站容易受到跨站脚本攻击"
            ];
            
            $results['recommendations'][] = "启用XSS保护机制，对所有输出进行适当转义";
        }
        
        if (!$this->config['sql_injection_protection']) {
            $results['vulnerabilities'][] = [
                'type' => 'config',
                'severity' => 'critical',
                'description' => "SQL注入保护未启用，数据库容易受到SQL注入攻击"
            ];
            
            $results['recommendations'][] = "启用SQL注入保护，使用参数化查询和预处理语句";
        }
        
        if ($this->config['password_policy']['min_length'] < 8) {
            $results['vulnerabilities'][] = [
                'type' => 'config',
                'severity' => 'medium',
                'description' => "密码策略过于宽松，最小长度应至少为8个字符"
            ];
            
            $results['recommendations'][] = "提高密码策略要求，设置最小长度为12个字符，并要求包含大小写字母、数字和特殊字符";
        }
        
        if ($this->config['session_timeout'] > 60) {
            $results['vulnerabilities'][] = [
                'type' => 'config',
                'severity' => 'low',
                'description' => "会话超时时间过长，增加会话劫持风险"
            ];
            
            $results['recommendations'][] = "减少会话超时时间，建议设置为30分钟或更短";
        }
    }
    
    /**
     * 扫描数据库安全性
     */
    private function scanDatabaseSecurity(array &$results): void
    {
        // 这里需要根据实际情况实现数据库安全扫描
        // 例如检查是否使用了预处理语句、检查数据库用户权限等
    }
    
    /**
     * 扫描已知漏洞
     */
    private function scanKnownVulnerabilities(array &$results): void
    {
        // 这里可以实现对已知漏洞的扫描
        // 例如检查是否使用了存在已知漏洞的依赖库等
    }
}
