<?php
/**
 * AlingAi Pro 系统配置管理页面
 * 提供完整的系统配置管理功能
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */

// 设置页面安全头
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com \'unsafe-inline\'; style-src \'self\' https://cdnjs.cloudflare.com https://fonts.googleapis.com \'unsafe-inline\'; font-src \'self\' https://fonts.gstatic.com; img-src \'self\' data:;');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// 启动会话
session_start();

// 检查用户是否已登录
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_user'])) {
    // 用户未登录，重定向到登录页面
    header('Location: /admin/login.php');
    exit;
}

// 获取用户角色信息
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$username = $_SESSION['username'] ?? $_SESSION['admin_user'] ?? 'Admin';

if (!$isAdmin) {
    // 非管理员用户，禁止访问
    header('Location: /admin/index.html');
    exit;
}

// 初始化配置
$configFile = dirname(dirname(__DIR__)) . '/config/config.php';
$configBackupDir = dirname(dirname(__DIR__)) . '/config/backups/';
$configError = '';
$configSuccess = '';
$configData = [];
$csrfToken = md5(uniqid(mt_rand(), true));
$_SESSION['csrf_token'] = $csrfToken;

// 确保备份目录存在
if (!is_dir($configBackupDir)) {
    mkdir($configBackupDir, 0755, true);
}

// 加载配置
if (file_exists($configFile)) {
    $configData = include $configFile;
} else {
    // 默认配置
    $configData = [
        'database' => [
            'type' => 'mysql',
            'host' => 'localhost',
            'port' => 3306,
            'database' => 'alingai_pro',
            'username' => 'root',
            'password' => ''
        ],
        'system' => [
            'name' => 'AlingAi Pro',
            'version' => '6.0.0',
            'debug' => false,
            'maintenance' => false,
            'timezone' => 'Asia/Shanghai',
            'language' => 'zh-CN'
        ],
        'security' => [
            'session_lifetime' => 1800,
            'password_min_length' => 8,
            'login_attempts' => 5,
            'lockout_time' => 30
        ],
        'api' => [
            'rate_limit' => 100,
            'token_expiry' => 30
        ],
        'mail' => [
            'driver' => 'smtp',
            'host' => '',
            'port' => 587,
            'username' => '',
            'password' => '',
            'encryption' => 'tls',
            'from_address' => '',
            'from_name' => 'AlingAi Pro'
        ]
    ];
    $configError = '配置文件不存在，已加载默认配置';
}

// 处理保存
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
    // CSRF校验
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $configError = '安全验证失败，请重新操作';
    } else {
        try {
            // 备份当前配置
            $backupFile = $configBackupDir . 'config_' . date('YmdHis') . '.php.bak';
            if (file_exists($configFile)) {
                copy($configFile, $backupFile);
            }
            
            // 更新数据库配置
            $configData['database']['type'] = $_POST['db_type'] ?? 'mysql';
            $configData['database']['host'] = $_POST['db_host'] ?? 'localhost';
            $configData['database']['port'] = (int)($_POST['db_port'] ?? 3306);
            $configData['database']['database'] = $_POST['db_name'] ?? '';
            $configData['database']['username'] = $_POST['db_user'] ?? '';
            
            // 只有当提供了新密码时才更新密码
            if (!empty($_POST['db_pass'])) {
                $configData['database']['password'] = $_POST['db_pass'];
            }
            
            // 设置SQLite数据库路径
            if ($configData['database']['type'] === 'sqlite') {
                $configData['database']['path'] = $_POST['db_path'] ?? 'database/alingai.db';
            }
            
            // 更新系统配置
            $configData['system']['name'] = $_POST['system_name'] ?? 'AlingAi Pro';
            $configData['system']['version'] = $_POST['system_version'] ?? '6.0.0';
            $configData['system']['debug'] = isset($_POST['system_debug']) && $_POST['system_debug'] === 'on';
            $configData['system']['maintenance'] = isset($_POST['system_maintenance']) && $_POST['system_maintenance'] === 'on';
            $configData['system']['timezone'] = $_POST['system_timezone'] ?? 'Asia/Shanghai';
            $configData['system']['language'] = $_POST['system_language'] ?? 'zh-CN';
            
            // 更新安全配置
            $configData['security']['session_lifetime'] = (int)($_POST['security_session'] ?? 1800);
            $configData['security']['password_min_length'] = (int)($_POST['security_password_length'] ?? 8);
            $configData['security']['login_attempts'] = (int)($_POST['security_login_attempts'] ?? 5);
            $configData['security']['lockout_time'] = (int)($_POST['security_lockout_time'] ?? 30);
            
            // 更新API配置
            $configData['api']['rate_limit'] = (int)($_POST['api_rate_limit'] ?? 100);
            $configData['api']['token_expiry'] = (int)($_POST['api_token_expiry'] ?? 30);
            
            // 更新邮件配置
            $configData['mail']['driver'] = $_POST['mail_driver'] ?? 'smtp';
            $configData['mail']['host'] = $_POST['mail_host'] ?? '';
            $configData['mail']['port'] = (int)($_POST['mail_port'] ?? 587);
            $configData['mail']['username'] = $_POST['mail_user'] ?? '';
            
            // 只有当提供了新密码时才更新密码
            if (!empty($_POST['mail_pass'])) {
                $configData['mail']['password'] = $_POST['mail_pass'];
            }
            
            $configData['mail']['encryption'] = $_POST['mail_encryption'] ?? 'tls';
            $configData['mail']['from_address'] = $_POST['mail_from_address'] ?? '';
            $configData['mail']['from_name'] = $_POST['mail_from_name'] ?? 'AlingAi Pro';
            
            // 生成配置文件内容
            $configContent = "<?php\n\n";
            $configContent .= "/**\n";
            $configContent .= " * AlingAi Pro 系统配置文件\n";
            $configContent .= " * 由管理员 {$username} 于 " . date('Y-m-d H:i:s') . " 更新\n";
            $configContent .= " */\n\n";
            $configContent .= "return " . var_export($configData, true) . ";\n";
            
            // 写入配置文件
            if (file_put_contents($configFile, $configContent)) {
                $configSuccess = '配置已成功保存';
                
                // 记录配置更新事件
                $eventLogFile = dirname(dirname(__DIR__)) . '/logs/config_events.log';
                $eventLog = date('Y-m-d H:i:s') . " | 用户: {$username} | 操作: 更新系统配置\n";
                file_put_contents($eventLogFile, $eventLog, FILE_APPEND);
            } else {
                $configError = '配置文件无法写入，检查文件权限';
            }
        } catch (Exception $e) {
            $configError = '保存配置时发生错误: ' . $e->getMessage();
            error_log('Config save error: ' . $e->getMessage());
        }
    }
}

// 测试数据库连接
function testDatabaseConnection($config) {
    try {
        if ($config['database']['type'] === 'sqlite') {
            $dbPath = dirname(dirname(__DIR__)) . '/' . $config['database']['path'];
            new PDO("sqlite:{$dbPath}");
        } else {
            $host = $config['database']['host'];
            $port = $config['database']['port'] ?? 3306;
            $dbname = $config['database']['database'];
            $dbuser = $config['database']['username'];
            $dbpass = $config['database']['password'];
            
            new PDO("mysql:host={$host};port={$port};dbname={$dbname}", $dbuser, $dbpass);
        }
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// 检查数据库连接状态
$dbConnectionStatus = testDatabaseConnection($configData);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统配置管理 - AlingAi Pro</title>
    
    <!-- 加载资源 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        
        .nav-link {
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .nav-link.active {
            background-color: rgba(59, 130, 246, 0.8);
        }
        
        .form-section {
            transition: all 0.3s ease;
        }
        
        .form-section:hover {
            background-color: rgba(255, 255, 255, 0.8);
        }
        
        .password-field {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-100">
    <!-- 导航栏 -->
    <nav class="bg-gray-900 text-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-brain text-white"></i>
                    </div>
                    <span class="ml-2 font-semibold text-xl">AlingAi Pro 后台管理</span>
                </div>
                
                <div class="hidden md:flex items-center space-x-6">
                    <a href="index.php" class="nav-link px-3 py-2 rounded-lg">首页</a>
                    <a href="users.php" class="nav-link px-3 py-2 rounded-lg">用户管理</a>
                    <a href="config_manager.php" class="nav-link active px-3 py-2 rounded-lg">系统配置</a>
                    <a href="security.php" class="nav-link px-3 py-2 rounded-lg">安全设置</a>
                    <a href="logs.php" class="nav-link px-3 py-2 rounded-lg">系统日志</a>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <button id="userMenuBtn" class="flex items-center space-x-1">
                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                <?php echo strtoupper(substr($username, 0, 1)); ?>
                            </div>
                            <span class="hidden md:inline-block"><?php echo htmlspecialchars($username); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        
                        <div id="userMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden">
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>个人资料
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>退出登录
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- 主内容区域 -->
    <main class="container mx-auto px-4 py-8">
        <!-- 页面标题 -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">系统配置管理</h1>
            <div class="flex items-center">
                <span class="mr-2 text-sm text-gray-600">数据库状态:</span>
                <?php if ($dbConnectionStatus): ?>
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                        <i class="fas fa-check-circle mr-1"></i>连接成功
                    </span>
                <?php else: ?>
                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">
                        <i class="fas fa-exclamation-circle mr-1"></i>连接失败
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($configError)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-3"></i>
                <p><?php echo htmlspecialchars($configError); ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($configSuccess)): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                <p><?php echo htmlspecialchars($configSuccess); ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- 表单部分 -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="bg-white rounded-lg shadow-md p-6">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <!-- 选项卡部分 -->
            <div class="mb-6 border-b border-gray-200">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                    <li class="mr-2">
                        <a href="#database" class="inline-block p-4 border-b-2 border-blue-600 text-blue-600 active" id="database-tab">
                            <i class="fas fa-database mr-2"></i>数据库配置
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="#system" class="inline-block p-4 border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300" id="system-tab">
                            <i class="fas fa-cogs mr-2"></i>系统配置
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="#security" class="inline-block p-4 border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300" id="security-tab">
                            <i class="fas fa-shield-alt mr-2"></i>安全配置
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="#api" class="inline-block p-4 border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300" id="api-tab">
                            <i class="fas fa-plug mr-2"></i>API配置
                        </a>
                    </li>
                    <li>
                        <a href="#mail" class="inline-block p-4 border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300" id="mail-tab">
                            <i class="fas fa-envelope mr-2"></i>邮件配置
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- 数据库配置 -->
            <div id="database-section" class="form-section mb-8">
                <h2 class="text-xl font-semibold mb-4">数据库配置</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="db_type" class="block text-sm font-medium text-gray-700 mb-1">数据库类型</label>
                        <select id="db_type" name="db_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="mysql" <?php echo ($configData['database']['type'] ?? '') === 'mysql' ? 'selected' : ''; ?>>MySQL</option>
                            <option value="sqlite" <?php echo ($configData['database']['type'] ?? '') === 'sqlite' ? 'selected' : ''; ?>>SQLite</option>
                        </select>
                    </div>
                    
                    <div id="sqlite_path_container" class="<?php echo ($configData['database']['type'] ?? '') === 'sqlite' ? '' : 'hidden'; ?>">
                        <label for="db_path" class="block text-sm font-medium text-gray-700 mb-1">数据库文件路径</label>
                        <input type="text" id="db_path" name="db_path" 
                            value="<?php echo htmlspecialchars($configData['database']['path'] ?? 'database/alingai.db'); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div id="mysql_host_container" class="<?php echo ($configData['database']['type'] ?? '') === 'sqlite' ? 'hidden' : ''; ?>">
                        <label for="db_host" class="block text-sm font-medium text-gray-700 mb-1">数据库主机</label>
                        <input type="text" id="db_host" name="db_host" 
                            value="<?php echo htmlspecialchars($configData['database']['host'] ?? 'localhost'); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div id="mysql_port_container" class="<?php echo ($configData['database']['type'] ?? '') === 'sqlite' ? 'hidden' : ''; ?>">
                        <label for="db_port" class="block text-sm font-medium text-gray-700 mb-1">数据库端口</label>
                        <input type="number" id="db_port" name="db_port" 
                            value="<?php echo htmlspecialchars($configData['database']['port'] ?? '3306'); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div id="mysql_name_container" class="<?php echo ($configData['database']['type'] ?? '') === 'sqlite' ? 'hidden' : ''; ?>">
                        <label for="db_name" class="block text-sm font-medium text-gray-700 mb-1">数据库名称</label>
                        <input type="text" id="db_name" name="db_name" 
                            value="<?php echo htmlspecialchars($configData['database']['database'] ?? ''); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div id="mysql_user_container" class="<?php echo ($configData['database']['type'] ?? '') === 'sqlite' ? 'hidden' : ''; ?>">
                        <label for="db_user" class="block text-sm font-medium text-gray-700 mb-1">数据库用户名</label>
                        <input type="text" id="db_user" name="db_user" 
                            value="<?php echo htmlspecialchars($configData['database']['username'] ?? ''); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div id="mysql_pass_container" class="<?php echo ($configData['database']['type'] ?? '') === 'sqlite' ? 'hidden' : ''; ?> password-field">
                        <label for="db_pass" class="block text-sm font-medium text-gray-700 mb-1">数据库密码</label>
                        <input type="password" id="db_pass" name="db_pass" 
                            placeholder="<?php echo empty($configData['database']['password'] ?? '') ? '' : '请输入新密码'; ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="password-toggle" onclick="togglePassword('db_pass')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- 系统配置 -->
            <div id="system-section" class="form-section mb-8 hidden">
                <h2 class="text-xl font-semibold mb-4">系统配置</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="system_name" class="block text-sm font-medium text-gray-700 mb-1">系统名称</label>
                        <input type="text" id="system_name" name="system_name" 
                            value="<?php echo htmlspecialchars($configData['system']['name'] ?? 'AlingAi Pro'); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="system_version" class="block text-sm font-medium text-gray-700 mb-1">系统版本</label>
                        <input type="text" id="system_version" name="system_version" 
                            value="<?php echo htmlspecialchars($configData['system']['version'] ?? '6.0.0'); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="system_timezone" class="block text-sm font-medium text-gray-700 mb-1">时区</label>
                        <select id="system_timezone" name="system_timezone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php
                            $timezones = [
                                'Asia/Shanghai' => '北京时间 (UTC+8)',
                                'Asia/Hong_Kong' => '香港时间 (UTC+8)',
                                'Asia/Tokyo' => '东京时间 (UTC+9)',
                                'America/New_York' => '纽约时间 (UTC-5/-4)',
                                'America/Los_Angeles' => '洛杉矶时间 (UTC-8/-7)',
                                'Europe/London' => '伦敦时间 (UTC+0/+1)',
                                'Europe/Paris' => '巴黎时间 (UTC+1/+2)',
                                'UTC' => '世界标准时间 (UTC)'
                            ];
                            
                            $currentTimezone = $configData['system']['timezone'] ?? 'Asia/Shanghai';
                            foreach ($timezones as $tz => $label) {
                                echo '<option value="' . htmlspecialchars($tz) . '"' . 
                                    ($currentTimezone === $tz ? ' selected' : '') . '>' . 
                                    htmlspecialchars($label) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="system_language" class="block text-sm font-medium text-gray-700 mb-1">默认语言</label>
                        <select id="system_language" name="system_language" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="zh-CN" <?php echo ($configData['system']['language'] ?? '') === 'zh-CN' ? 'selected' : ''; ?>>中文</option>
                            <option value="en-US" <?php echo ($configData['system']['language'] ?? '') === 'en-US' ? 'selected' : ''; ?>>English (US)</option>
                            <option value="ja-JP" <?php echo ($configData['system']['language'] ?? '') === 'ja-JP' ? 'selected' : ''; ?>>日本语</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="system_debug" name="system_debug" 
                            <?php echo isset($configData['system']['debug']) && $configData['system']['debug'] ? 'checked' : ''; ?>
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="system_debug" class="ml-2 block text-sm text-gray-700">
                            启用调试模式
                        </label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="system_maintenance" name="system_maintenance" 
                            <?php echo isset($configData['system']['maintenance']) && $configData['system']['maintenance'] ? 'checked' : ''; ?>
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="system_maintenance" class="ml-2 block text-sm text-gray-700">
                            启用维护模式
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- 安全配置 -->
            <div id="security-section" class="form-section mb-8 hidden">
                <h2 class="text-xl font-semibold mb-4">安全配置</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="security_session" class="block text-sm font-medium text-gray-700 mb-1">会话超时时间</label>
                        <input type="number" id="security_session" name="security_session" 
                            value="<?php echo htmlspecialchars($configData['security']['session_lifetime'] ?? '1800'); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="security_password_length" class="block text-sm font-medium text-gray-700 mb-1">密码最小长度</label>
                        <input type="number" id="security_password_length" name="security_password_length" 
                            value="<?php echo htmlspecialchars($configData['security']['password_min_length'] ?? '8'); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="security_login_attempts" class="block text-sm font-medium text-gray-700 mb-1">登录尝试次数</label>
                        <input type="number" id="security_login_attempts" name="security_login_attempts" 
                            value="<?php echo htmlspecialchars($configData['security']['login_attempts'] ?? '5'); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="security_lockout_time" class="block text-sm font-medium text-gray-700 mb-1">锁定时间</label>
                        <input type="number" id="security_lockout_time" name="security_lockout_time" 
                            value="<?php echo htmlspecialchars($configData['security']['lockout_time'] ?? '30'); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            
            <!-- API配置 -->
            <div id="api-section" class="form-section mb-8 hidden">
                <h2 class="text-xl font-semibold mb-4">API配置</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="api_rate_limit" class="block text-sm font-medium text-gray-700 mb-1">请求限制</label>
                        <input type="number" id="api_rate_limit" name="api_rate_limit" 
                            value="<?php echo htmlspecialchars($configData['api']['rate_limit'] ?? '100'); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="api_token_expiry" class="block text-sm font-medium text-gray-700 mb-1">令牌有效期</label>
                        <input type="number" id="api_token_expiry" name="api_token_expiry" 
                            value="<?php echo htmlspecialchars($configData['api']['token_expiry'] ?? '30'); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            
            <!-- 邮件配置 -->
            <div id="mail-section" class="form-section mb-8 hidden">
                <h2 class="text-xl font-semibold mb-4">邮件配置</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="mail_driver" class="block text-sm font-medium text-gray-700 mb-1">邮件驱动</label>
                        <select id="mail_driver" name="mail_driver" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="smtp" <?php echo ($configData['mail']['driver'] ?? '') === 'smtp' ? 'selected' : ''; ?>>SMTP</option>
                            <option value="sendmail" <?php echo ($configData['mail']['driver'] ?? '') === 'sendmail' ? 'selected' : ''; ?>>Sendmail</option>
                            <option value="mail" <?php echo ($configData['mail']['driver'] ?? '') === 'mail' ? 'selected' : ''; ?>>PHP Mail</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="mail_host" class="block text-sm font-medium text-gray-700 mb-1">邮件主机</label>
                        <input type="text" id="mail_host" name="mail_host" 
                            value="<?php echo htmlspecialchars($configData['mail']['host'] ?? ''); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="mail_port" class="block text-sm font-medium text-gray-700 mb-1">邮件端口</label>
                        <input type="number" id="mail_port" name="mail_port" 
                            value="<?php echo htmlspecialchars($configData['mail']['port'] ?? '587'); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="mail_user" class="block text-sm font-medium text-gray-700 mb-1">邮件用户名</label>
                        <input type="text" id="mail_user" name="mail_user" 
                            value="<?php echo htmlspecialchars($configData['mail']['username'] ?? ''); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div id="mail_pass_container" class="password-field">
                        <label for="mail_pass" class="block text-sm font-medium text-gray-700 mb-1">邮件密码</label>
                        <input type="password" id="mail_pass" name="mail_pass" 
                            placeholder="<?php echo empty($configData['mail']['password'] ?? '') ? '' : '请输入新密码'; ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="password-toggle" onclick="togglePassword('mail_pass')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    
                    <div>
                        <label for="mail_encryption" class="block text-sm font-medium text-gray-700 mb-1">加密方式</label>
                        <select id="mail_encryption" name="mail_encryption" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="tls" <?php echo ($configData['mail']['encryption'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                            <option value="ssl" <?php echo ($configData['mail']['encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="mail_from_address" class="block text-sm font-medium text-gray-700 mb-1">发件人地址</label>
                        <input type="text" id="mail_from_address" name="mail_from_address" 
                            value="<?php echo htmlspecialchars($configData['mail']['from_address'] ?? ''); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="mail_from_name" class="block text-sm font-medium text-gray-700 mb-1">发件人名称</label>
                        <input type="text" id="mail_from_name" name="mail_from_name" 
                            value="<?php echo htmlspecialchars($configData['mail']['from_name'] ?? 'AlingAi Pro'); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            
            <div class="mt-6">
                <button type="submit" name="save_config" class="px-4 py-2 bg-blue-600 text-white rounded-md">保存配置</button>
            </div>
        </form>
    </main>
    
    <!-- JavaScript -->
    <script>
        // 用户菜单按钮点击事件
        document.getElementById('userMenuBtn').addEventListener('click', function() {
            document.getElementById('userMenu').classList.toggle('hidden');
        });
        
        // 点击外部区域隐藏用户菜单
        document.addEventListener('click', function(e) {
            const userMenu = document.getElementById('userMenu');
            const userMenuBtn = document.getElementById('userMenuBtn');
            
            if (!userMenuBtn.contains(e.target) && !userMenu.contains(e.target)) {
                userMenu.classList.add('hidden');
            }
        });
        
        // 用户切换密码显示
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.querySelector(`#${inputId}`).nextElementSibling.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // 用户切换数据库配置
        document.getElementById('db_type').addEventListener('change', function() {
            const isSqlite = this.value === 'sqlite';
            
            // 用户切换SQLite相关选项
            document.getElementById('sqlite_path_container').classList.toggle('hidden', !isSqlite);
            
            // 用户切换MySQL相关选项
            const mysqlFields = [
                'mysql_host_container',
                'mysql_port_container',
                'mysql_name_container',
                'mysql_user_container',
                'mysql_pass_container'
            ];
            
            mysqlFields.forEach(field => {
                document.getElementById(field).classList.toggle('hidden', isSqlite);
            });
        });
        
        // 用户切换选项卡
        const tabs = ['database', 'system', 'security', 'api', 'mail'];
        const tabEls = tabs.map(tab => document.getElementById(`${tab}-tab`));
        const sectionEls = tabs.map(tab => document.getElementById(`${tab}-section`));
        
        tabEls.forEach((tabEl, index) => {
            tabEl.addEventListener('click', function(e) {
                e.preventDefault();
                
                // 用户切换选项卡样式
                tabEls.forEach(el => {
                    el.classList.remove('border-blue-600', 'text-blue-600');
                    el.classList.add('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
                });
                
                tabEl.classList.remove('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
                tabEl.classList.add('border-blue-600', 'text-blue-600');
                
                // 用户切换内容区域
                sectionEls.forEach(section => section.classList.add('hidden'));
                sectionEls[index].classList.remove('hidden');
            });
        });
        
        // 自动保存数据库配置
        document.querySelectorAll('#db_type, #db_host, #db_port, #db_name, #db_user, #db_pass, #db_path').forEach(el => {
            el.addEventListener('change', function() {
                // 实际应用中需要通过AJAX请求后端保存配置
                console.log('数据库配置已更改，但此操作不会保存到后端');
            });
        });
    </script>
</body>
</html>
