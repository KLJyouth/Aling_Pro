<?php
/**
 * AlingAi Pro 用户管理页面
 * 提供安全的用户管理功能
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */

// 设置页面安全头
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com \'unsafe-inline\'; style-src \'self\' https://cdnjs.cloudflare.com https://fonts.googleapis.com \'unsafe-inline\'; font-src \'self\' https://fonts.gstatic.com; img-src \'self\' data:;');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// 引入用户安全类
require_once __DIR__ . '/../includes/UserSecurity.php';

use AlingAi\Security\UserSecurity;

// 验证管理员会话
$userData = UserSecurity::validateSession(true, '../login.php');

// 验证已登录用户是管理员
$userId = $userData['id'];
$username = $userData['username'];
$userRole = $userData['role'];

// 初始化变量
$userError = '';
$userSuccess = '';
$users = [];
$csrfToken = UserSecurity::generateCsrfToken('user_form');

// 每页显示的用户数
$perPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;

// 搜索条件
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterRole = isset($_GET['role']) ? trim($_GET['role']) : '';
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';

// 处理用户操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF检查
    if (!isset($_POST['csrf_token']) || !UserSecurity::validateCsrfToken($_POST['csrf_token'],  'user_form')) {
        $userError = '安全验证失败，请重新操作';
    } else {
        try {
            // 加载配置文件
            $configFile = dirname(dirname(__DIR__)) . '/config/config.php';
            if (file_exists($configFile)) {
                $config = require $configFile;
                
                // 连接数据库
                if ($config['database']['type'] === 'sqlite') {
                    $dbPath = dirname(dirname(__DIR__)) . '/' . $config['database']['path'];
                    $pdo = new PDO("sqlite:{$dbPath}");
                } else {
                    $host = $config['database']['host'];
                    $port = $config['database']['port'] ?? 3306;
                    $dbname = $config['database']['database'];
                    $dbuser = $config['database']['username'];
                    $dbpass = $config['database']['password'];
                    
                    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname}", $dbuser, $dbpass);
                }
                
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // 处理创建用户
                if (isset($_POST['create_user'])) {
                    $newUsername = trim($_POST['username'] ?? '');
                    $newEmail = trim($_POST['email'] ?? '');
                    $newPassword = $_POST['password'] ?? '';
                    $newRole = $_POST['role'] ?? 'user';
                    
                    // 验证输入
                    if (empty($newUsername) || empty($newEmail) || empty($newPassword)) {
                        $userError = '所有字段都是必填项';
                    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                        $userError = '请输入有效的电子邮件地址';
                    } else {
                        // 检查用户名和邮箱是否已存在
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
                        $stmt->execute([$newUsername, $newEmail]);
                        
                        if ($stmt->fetchColumn() > 0) {
                            $userError = '用户名或邮箱已被使用';
                        } else {
                            // 创建用户
                            $hashedPassword = UserSecurity::hashPassword($newPassword);
                            
                            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, status, created_at) 
                                               VALUES (?, ?, ?, ?, 'active', NOW())");
                            $stmt->execute([$newUsername, $newEmail, $hashedPassword, $newRole]);
                            
                            $newUserId = $pdo->lastInsertId();
                            
                            // 创建默认用户配额
                            $stmt = $pdo->prepare("INSERT INTO user_usage_quota (user_id, quota_type, limit_value, reset_period, next_reset) 
                                               VALUES (?, 'tokens', 100000, 'monthly', DATE_ADD(CURRENT_DATE(), INTERVAL 1 MONTH))");
                            $stmt->execute([$newUserId]);
                            
                            $stmt = $pdo->prepare("INSERT INTO user_usage_quota (user_id, quota_type, limit_value, reset_period, next_reset) 
                                               VALUES (?, 'requests', 1000, 'monthly', DATE_ADD(CURRENT_DATE(), INTERVAL 1 MONTH))");
                            $stmt->execute([$newUserId]);
                            
                            // 记录创建用户事件
                            UserSecurity::logSecurityEvent($userId, 'user_create', "管理员创建了新用户: {$newUsername}", 'info', 'success');
                            
                            $userSuccess = '用户创建成功';
                        }
                    }
                }
                
                // 处理用户状态
                if (isset($_POST['update_status'])) {
                    $targetUserId = (int)($_POST['user_id'] ?? 0);
                    $newStatus = $_POST['status'] ?? '';
                    
                    if ($targetUserId <= 0) {
                        $userError = '无效的用户ID';
                    } elseif (!in_array($newStatus, ['active', 'inactive', 'suspended'])) {
                        $userError = '无效的用户状态';
                    } elseif ($targetUserId === $userId) {
                        $userError = '不能修改自己的状态';
                    } else {
                        // 更新用户状态
                        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
                        $stmt->execute([$newStatus, $targetUserId]);
                        
                        // 记录用户状态事件
                        UserSecurity::logSecurityEvent($userId, 'user_status_update', 
                            "管理员更新了用户ID {$targetUserId} 的状态为 {$newStatus}", 'info', 'success');
                        
                        $userSuccess = '用户状态更新成功';
                    }
                }
                
                // 处理用户角色
                if (isset($_POST['update_role'])) {
                    $targetUserId = (int)($_POST['user_id'] ?? 0);
                    $newRole = $_POST['role'] ?? '';
                    
                    if ($targetUserId <= 0) {
                        $userError = '无效的用户ID';
                    } elseif (!in_array($newRole, ['user', 'admin', 'moderator'])) {
                        $userError = '无效的用户角色';
                    } elseif ($targetUserId === $userId) {
                        $userError = '不能修改自己的角色';
                    } else {
                        // 更新用户角色
                        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                        $stmt->execute([$newRole, $targetUserId]);
                        
                        // 记录用户角色事件
                        UserSecurity::logSecurityEvent($userId, 'user_role_update', 
                            "管理员更新了用户ID {$targetUserId} 的角色为 {$newRole}", 'info', 'success');
                        
                        $userSuccess = '用户角色更新成功';
                    }
                }
                
                // 处理重置密码
                if (isset($_POST['reset_password'])) {
                    $targetUserId = (int)($_POST['user_id'] ?? 0);
                    $newPassword = $_POST['password'] ?? '';
                    
                    if ($targetUserId <= 0) {
                        $userError = '无效的用户ID';
                    } elseif (empty($newPassword)) {
                        $userError = '密码不能为空';
                    } else {
                        // 检查密码强度
                        $passwordStrength = UserSecurity::checkPasswordStrength($newPassword);
                        if ($passwordStrength['strength'] === 'weak') {
                            $userError = '密码强度不足: ' . implode(', ', $passwordStrength['feedback']);
                        } else {
                            // 更新密码
                            $hashedPassword = UserSecurity::hashPassword($newPassword);
                            
                            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                            $stmt->execute([$hashedPassword, $targetUserId]);
                            
                            // 记录重置密码事件
                            UserSecurity::logSecurityEvent($userId, 'user_password_reset', 
                                "管理员重置了用户ID {$targetUserId} 的密码", 'warning', 'success');
                            
                            $userSuccess = '用户密码重置成功';
                        }
                    }
                }
            } else {
                $userError = '系统错误，请联系管理员';
            }
        } catch (Exception $e) {
            $userError = '处理用户操作时发生错误: ' . $e->getMessage();
            error_log('User management error: ' . $e->getMessage());
        }
    }
}

// 获取用户列表
try {
    // 加载配置文件
    $configFile = dirname(dirname(__DIR__)) . '/config/config.php';
    if (file_exists($configFile)) {
        $config = require $configFile;
        
        // 连接数据库
        if ($config['database']['type'] === 'sqlite') {
            $dbPath = dirname(dirname(__DIR__)) . '/' . $config['database']['path'];
            $pdo = new PDO("sqlite:{$dbPath}");
        } else {
            $host = $config['database']['host'];
            $port = $config['database']['port'] ?? 3306;
            $dbname = $config['database']['database'];
            $dbuser = $config['database']['username'];
            $dbpass = $config['database']['password'];
            
            $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname}", $dbuser, $dbpass);
        }
        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 构建查询
        $query = "SELECT * FROM users WHERE 1=1";
        $params = [];
        
        // 添加搜索条件
        if (!empty($searchTerm)) {
            $query .= " AND (username LIKE ? OR email LIKE ?)";
            $params[] = "%{$searchTerm}%";
            $params[] = "%{$searchTerm}%";
        }
        
        // 添加角色条件
        if (!empty($filterRole)) {
            $query .= " AND role = ?";
            $params[] = $filterRole;
        }
        
        // 添加状态条件
        if (!empty($filterStatus)) {
            $query .= " AND status = ?";
            $params[] = $filterStatus;
        }
        
        // 获取总记录数
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM ({$query}) as count_query");
        $countStmt->execute($params);
        $totalUsers = $countStmt->fetchColumn();
        
        // 计算总页数
        $totalPages = ceil($totalUsers / $perPage);
        if ($currentPage > $totalPages && $totalPages > 0) {
            $currentPage = $totalPages;
        }
        
        // 添加分页
        $offset = ($currentPage - 1) * $perPage;
        $query .= " ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}";
        
        // 执行查询
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $userError = '系统错误，请联系管理员';
    }
} catch (Exception $e) {
    $userError = '获取用户列表时发生错误: ' . $e->getMessage();
    error_log('User list error: ' . $e->getMessage());
    $users = [];
    $totalUsers = 0;
    $totalPages = 0;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户管理 - AlingAi Pro</title>
    
    <!-- 引入资源 -->
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
        
        .user-status {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        
        .status-active {
            background-color: #10B981;
        }
        
        .status-inactive {
            background-color: #9CA3AF;
        }
        
        .status-suspended {
            background-color: #EF4444;
        }
        
        .role-badge {
            font-size: 0.75rem;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
        }
        
        .role-admin {
            background-color: #FEF3C7;
            color: #92400E;
        }
        
        .role-moderator {
            background-color: #DBEAFE;
            color: #1E40AF;
        }
        
        .role-user {
            background-color: #E0E7FF;
            color: #3730A3;
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
                    <a href="users.php" class="nav-link active px-3 py-2 rounded-lg">用户管理</a>
                    <a href="config_manager.php" class="nav-link px-3 py-2 rounded-lg">系统配置</a>
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
            <h1 class="text-2xl font-bold text-gray-800">用户管理</h1>
            <button id="createUserBtn" class="px-4 py-2 bg-blue-600 text-white rounded-md">
                <i class="fas fa-user-plus mr-2"></i>创建用户
            </button>
        </div>
        
        <?php if (!empty($userError)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-3"></i>
                <p><?php echo htmlspecialchars($userError); ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($userSuccess)): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                <p><?php echo htmlspecialchars($userSuccess); ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- 搜索表单 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="get" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">用户名</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" 
                        placeholder="用户名或邮箱"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">角色</label>
                    <select id="role" name="role" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">全角色</option>
                        <option value="admin" <?php echo $filterRole === 'admin' ? 'selected' : ''; ?>>管理员</option>
                        <option value="moderator" <?php echo $filterRole === 'moderator' ? 'selected' : ''; ?>>版主</option>
                        <option value="user" <?php echo $filterRole === 'user' ? 'selected' : ''; ?>>普通用户</option>
                    </select>
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                    <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">全状态</option>
                        <option value="active" <?php echo $filterStatus === 'active' ? 'selected' : ''; ?>>活跃</option>
                        <option value="inactive" <?php echo $filterStatus === 'inactive' ? 'selected' : ''; ?>>不活跃</option>
                        <option value="suspended" <?php echo $filterStatus === 'suspended' ? 'selected' : ''; ?>>已暂停</option>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">
                        <i class="fas fa-search mr-2"></i>搜索
                    </button>
                    <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-md">
                        <i class="fas fa-times mr-2"></i>重置
                    </a>
                </div>
            </form>
        </div>
        
        <!-- 用户列表 -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <?php if (empty($users)): ?>
                <div class="p-6 text-center text-gray-500">
                    <i class="fas fa-users text-4xl mb-4"></i>
                    <p>没有找到用户记录</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">用户名</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">邮箱</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">角色</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">注册时间</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($user['id']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-blue-600 rounded-full flex items-center justify-center text-white">
                                                <?php echo strtoupper(substr($user['username'],  0, 1)); ?>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($user['username']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php 
                                        $roleClass = 'role-user';
                                        if ($user['role'] === 'admin') {
                                            $roleClass = 'role-admin';
                                        } elseif ($user['role'] === 'moderator') {
                                            $roleClass = 'role-moderator';
                                        }
                                        ?>
                                        <span class="role-badge <?php echo $roleClass; ?>">
                                            <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php 
                                        $statusClass = 'status-active';
                                        if ($user['status'] === 'inactive') {
                                            $statusClass = 'status-inactive';
                                        } elseif ($user['status'] === 'suspended') {
                                            $statusClass = 'status-suspended';
                                        }
                                        ?>
                                        <div class="flex items-center">
                                            <div class="user-status <?php echo $statusClass; ?>"></div>
                                            <span class="text-sm text-gray-500">
                                                <?php 
                                                $statusText = '活跃';
                                                if ($user['status'] === 'inactive') {
                                                    $statusText = '不活跃';
                                                } elseif ($user['status'] === 'suspended') {
                                                    $statusText = '已暂停';
                                                }
                                                echo $statusText;
                                                ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars(date('Y-m-d', strtotime($user['created_at']))); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <?php if ($user['id'] != $userId): ?>
                                            <button type="button" class="text-blue-600 hover:text-blue-900 mr-3 edit-user-btn" 
                                                data-id="<?php echo htmlspecialchars($user['id']); ?>"
                                                data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                                data-role="<?php echo htmlspecialchars($user['role']); ?>"
                                                data-status="<?php echo htmlspecialchars($user['status']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="text-red-600 hover:text-red-900 reset-password-btn"
                                                data-id="<?php echo htmlspecialchars($user['id']); ?>"
                                                data-username="<?php echo htmlspecialchars($user['username']); ?>">
                                                <i class="fas fa-key"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-gray-400">当前用户</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- 分页 -->
                <?php if ($totalPages > 1): ?>
                    <div class="px-6 py-4 bg-gray-50">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-700">
                                显示 <?php echo ($currentPage - 1) * $perPage + 1; ?> 到 
                                <?php echo min($currentPage * $perPage, $totalUsers); ?> 
                                共 <?php echo $totalUsers; ?> 条记录
                            </div>
                            <div class="flex space-x-1">
                                <?php if ($currentPage > 1): ?>
                                    <a href="?page=<?php echo $currentPage - 1; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?><?php echo !empty($filterRole) ? '&role=' . urlencode($filterRole) : ''; ?><?php echo !empty($filterStatus) ? '&status=' . urlencode($filterStatus) : ''; ?>" 
                                        class="px-3 py-1 border border-gray-300 rounded-md bg-white text-gray-700">
                                        上一页
                                    </a>
                                <?php endif; ?>
                                
                                <?php 
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($totalPages, $startPage + 4);
                                if ($endPage - $startPage < 4) {
                                    $startPage = max(1, $endPage - 4);
                                }
                                
                                for ($i = $startPage; $i <= $endPage; $i++): 
                                ?>
                                    <a href="?page=<?php echo $i; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?><?php echo !empty($filterRole) ? '&role=' . urlencode($filterRole) : ''; ?><?php echo !empty($filterStatus) ? '&status=' . urlencode($filterStatus) : ''; ?>" 
                                        class="px-3 py-1 border border-gray-300 rounded-md <?php echo $i === $currentPage ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($currentPage < $totalPages): ?>
                                    <a href="?page=<?php echo $currentPage + 1; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?><?php echo !empty($filterRole) ? '&role=' . urlencode($filterRole) : ''; ?><?php echo !empty($filterStatus) ? '&status=' . urlencode($filterStatus) : ''; ?>" 
                                        class="px-3 py-1 border border-gray-300 rounded-md bg-white text-gray-700">
                                        下一页
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- 创建用户模态框 -->
    <div id="createUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">创建新用户</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 close-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="px-6 py-4">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">用户名</label>
                    <input type="text" id="username" name="username" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">邮箱</label>
                    <input type="email" id="email" name="email" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-4 password-field">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">密码</label>
                    <input type="password" id="password" name="password" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <span class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                
                <div class="mb-4">
                    <label for="create_role" class="block text-sm font-medium text-gray-700 mb-1">角色</label>
                    <select id="create_role" name="role" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="user">普通用户</option>
                        <option value="moderator">版主</option>
                        <option value="admin">管理员</option>
                    </select>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md mr-2 close-modal">取消</button>
                    <button type="submit" name="create_user" class="px-4 py-2 bg-blue-600 text-white rounded-md">创建用户</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- 编辑用户模态框 -->
    <div id="editUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">编辑用户</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 close-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="px-6 py-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">用户名</label>
                    <div id="edit_username" class="text-gray-800 font-medium"></div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">邮箱</label>
                    <div id="edit_email" class="text-gray-800"></div>
                </div>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="mb-4 border-t border-gray-200 pt-4 mt-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    
                    <div class="mb-4">
                        <label for="edit_role" class="block text-sm font-medium text-gray-700 mb-1">角色</label>
                        <select id="edit_role" name="role" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="user">普通用户</option>
                            <option value="moderator">版主</option>
                            <option value="admin">管理员</option>
                        </select>
                    </div>
                    
                    <div class="mt-4 flex justify-end">
                        <button type="submit" name="update_role" class="px-4 py-2 bg-blue-600 text-white rounded-md">更新角色</button>
                    </div>
                </form>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="mb-4 border-t border-gray-200 pt-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="user_id" class="edit_user_id_input">
                    
                    <div class="mb-4">
                        <label for="edit_status" class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                        <select id="edit_status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="active">活跃</option>
                            <option value="inactive">不活跃</option>
                            <option value="suspended">已暂停</option>
                        </select>
                    </div>
                    
                    <div class="mt-4 flex justify-end">
                        <button type="submit" name="update_status" class="px-4 py-2 bg-blue-600 text-white rounded-md">更新状态</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- 重置密码模态框 -->
    <div id="resetPasswordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">重置密码</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 close-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="px-6 py-4">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" id="reset_user_id" name="user_id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">用户名</label>
                    <div id="reset_username" class="text-gray-800 font-medium"></div>
                </div>
                
                <div class="mb-4 password-field">
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">新密码</label>
                    <input type="password" id="new_password" name="password" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <span class="password-toggle" onclick="togglePassword('new_password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md mr-2 close-modal">取消</button>
                    <button type="submit" name="reset_password" class="px-4 py-2 bg-red-600 text-white rounded-md">重置密码</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script>
        // 用户菜单按钮点击事件
        document.getElementById('userMenuBtn').addEventListener('click', function() {
            document.getElementById('userMenu').classList.toggle('hidden');
        });
        
        // 外部点击关闭用户菜单
        document.addEventListener('click', function(e) {
            const userMenu = document.getElementById('userMenu');
            const userMenuBtn = document.getElementById('userMenuBtn');
            
            if (!userMenuBtn.contains(e.target) && !userMenu.contains(e.target)) {
                userMenu.classList.add('hidden');
            }
        });
        
        // 用户密码切换事件
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
        
        // 创建用户按钮点击事件
        const createUserBtn = document.getElementById('createUserBtn');
        const createUserModal = document.getElementById('createUserModal');
        const editUserModal = document.getElementById('editUserModal');
        const resetPasswordModal = document.getElementById('resetPasswordModal');
        const closeModalButtons = document.querySelectorAll('.close-modal');
        
        // 打开创建用户模态框
        createUserBtn.addEventListener('click', function() {
            createUserModal.classList.remove('hidden');
        });
        
        // 编辑用户按钮点击事件
        document.querySelectorAll('.edit-user-btn').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const username = this.getAttribute('data-username');
                const email = this.getAttribute('data-email');
                const role = this.getAttribute('data-role');
                const status = this.getAttribute('data-status');
                
                document.getElementById('edit_user_id').value = userId;
                document.querySelectorAll('.edit_user_id_input').forEach(input => {
                    input.value = userId;
                });
                
                document.getElementById('edit_username').textContent = username;
                document.getElementById('edit_email').textContent = email;
                document.getElementById('edit_role').value = role;
                document.getElementById('edit_status').value = status;
                
                editUserModal.classList.remove('hidden');
            });
        });
        
        // 重置密码按钮点击事件
        document.querySelectorAll('.reset-password-btn').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const username = this.getAttribute('data-username');
                
                document.getElementById('reset_user_id').value = userId;
                document.getElementById('reset_username').textContent = username;
                
                resetPasswordModal.classList.remove('hidden');
            });
        });
        
        // 关闭模态框
        closeModalButtons.forEach(button => {
            button.addEventListener('click', function() {
                createUserModal.classList.add('hidden');
                editUserModal.classList.add('hidden');
                resetPasswordModal.classList.add('hidden');
            });
        });
        
        // 外部点击关闭模态框
        window.addEventListener('click', function(e) {
            if (e.target === createUserModal) {
                createUserModal.classList.add('hidden');
            }
            
            if (e.target === editUserModal) {
                editUserModal.classList.add('hidden');
            }
            
            if (e.target === resetPasswordModal) {
                resetPasswordModal.classList.add('hidden');
            }
        });
    </script>
</body>
</html>

