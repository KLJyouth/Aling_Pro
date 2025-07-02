<?php
/**
 * AlingAi Pro 用户仪表盘
 * 提供用户核心功能访问和数据概览
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */

// 设置页面安全性
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com \'unsafe-inline\'; style-src \'self\' https://cdnjs.cloudflare.com https://fonts.googleapis.com \'unsafe-inline\'; font-src \'self\' https://fonts.gstatic.com; img-src \'self\' data:;');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// 引入用户安全类
require_once __DIR__ . '/includes/UserSecurity.php';

use AlingAi\Security\UserSecurity;

// 验证用户会话
$userData = UserSecurity::validateSession(false, 'login.php');

// 到这里说明用户已登录
$userId = $userData['id'];
$username = $userData['username'];
$userEmail = $userData['email'];
$userRole = $userData['role'];

// 获取用户使用数据
$userStats = getUserStats($userId);
$apiTokens = getUserApiTokens($userId);
$recentChats = getRecentChats($userId);

/**
 * 获取用户统计数据
 */
function getUserStats($userId) {
    try {
        // 加载配置文件
        $configFile = dirname(__DIR__) . '/config/config.php';
        if (!file_exists($configFile)) {
            return [
                'tokens_used' => 0,
                'chat_count' => 0,
                'api_calls' => 0,
                'last_active' => '未知'
            ];
        }
        
        $config = require $configFile;
        
        // 连接数据库
        if ($config['database']['type'] === 'sqlite') {
            $dbPath = dirname(__DIR__) . '/' . $config['database']['path'];
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
        
        // 获取Token使用量
        $stmt = $pdo->prepare("SELECT SUM(tokens_input) + SUM(tokens_output) as tokens_used 
                             FROM ai_engine_usage 
                             WHERE user_id = ?");
        $stmt->execute([$userId]);
        $tokensUsed = $stmt->fetchColumn() ?: 0;
        
        // 获取会话数量
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM chat_sessions WHERE user_id = ?");
        $stmt->execute([$userId]);
        $chatCount = $stmt->fetchColumn() ?: 0;
        
        // 获取API调用次数
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM api_access_log WHERE user_id = ?");
        $stmt->execute([$userId]);
        $apiCalls = $stmt->fetchColumn() ?: 0;
        
        // 获取最后活跃时间
        $stmt = $pdo->prepare("SELECT last_login FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $lastActive = $stmt->fetchColumn();
        
        // 返回统计数据
        return [
            'tokens_used' => $tokensUsed,
            'chat_count' => $chatCount,
            'api_calls' => $apiCalls,
            'last_active' => $lastActive ? date('Y-m-d H:i:s', strtotime($lastActive)) : '未知'
        ];
    } catch (Exception $e) {
        error_log('Error getting user stats: ' . $e->getMessage());
        return [
            'tokens_used' => 0,
            'chat_count' => 0,
            'api_calls' => 0,
            'last_active' => '未知'
        ];
    }
}

/**
 * 获取用户API令牌
 */
function getUserApiTokens($userId) {
    try {
        // 加载配置文件
        $configFile = dirname(__DIR__) . '/config/config.php';
        if (!file_exists($configFile)) {
            return [];
        }
        
        $config = require $configFile;
        
        // 连接数据库
        if ($config['database']['type'] === 'sqlite') {
            $dbPath = dirname(__DIR__) . '/' . $config['database']['path'];
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
        
        // 获取用户API令牌
        $stmt = $pdo->prepare("SELECT * FROM api_keys WHERE user_id = ? AND status = 'active'");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
        error_log('Error getting user API tokens: ' . $e->getMessage());
        return [];
    }
}

/**
 * 获取用户最近对话
 */
function getRecentChats($userId) {
    try {
        // 加载配置文件
        $configFile = dirname(__DIR__) . '/config/config.php';
        if (!file_exists($configFile)) {
            return [];
        }
        
        $config = require $configFile;
        
        // 连接数据库
        if ($config['database']['type'] === 'sqlite') {
            $dbPath = dirname(__DIR__) . '/' . $config['database']['path'];
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
        
        // 获取最近的会话
        $stmt = $pdo->prepare("SELECT cs.*, ae.name as engine_name 
                             FROM chat_sessions cs
                             JOIN ai_engines ae ON cs.engine_id = ae.id
                             WHERE cs.user_id = ? AND cs.status = 'active'
                             ORDER BY cs.last_message_at DESC
                             LIMIT 5");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
        error_log('Error getting recent chats: ' . $e->getMessage());
        return [];
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户仪表盘- AlingAi Pro</title>
    
    <!-- 核心资源 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
    
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
        
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #4338ca, #3b82f6);
        }
        
        .api-token {
            font-family: 'Consolas', monospace;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-100">
    <!-- 导航栏-->
    <nav class="bg-gray-900 text-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-brain text-white"></i>
                    </div>
                    <span class="ml-2 font-semibold text-xl">AlingAi Pro</span>
                </div>
                
                <div class="hidden md:flex items-center space-x-6">
                    <a href="dashboard.php" class="nav-link active px-3 py-2 rounded-lg">仪表盘</a>
                    <a href="chat.php" class="nav-link px-3 py-2 rounded-lg">对话</a>
                    <a href="documents.php" class="nav-link px-3 py-2 rounded-lg">文档</a>
                    <a href="api.php" class="nav-link px-3 py-2 rounded-lg">API</a>
                    <a href="settings.php" class="nav-link px-3 py-2 rounded-lg">设置</a>
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
                            <a href="settings.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i>账户设置
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
    
    <!-- 主内容区域-->
    <main class="container mx-auto px-4 py-8">
        <!-- 欢迎区域 -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">欢迎回来，<?php echo htmlspecialchars($username); ?>!</h1>
            <p class="text-gray-600">上次登录: <?php echo htmlspecialchars($userStats['last_active']); ?></p>
        </div>
        
        <!-- 统计数据卡片 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Tokens使用量-->
            <div class="bg-white rounded-lg shadow-md p-6 card">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-sm">Token使用量</p>
                        <p class="text-2xl font-semibold mt-1"><?php echo number_format($userStats['tokens_used']); ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-chart-line text-blue-600"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="h-2 bg-gray-200 rounded-full">
                        <?php $usagePercent = min(100, ($userStats['tokens_used'] / 1000000) * 100); ?>
                        <div class="h-full bg-blue-600 rounded-full" style="width: <?php echo $usagePercent; ?>%"></div>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="text-xs text-gray-500">本月限额</span>
                        <span class="text-xs font-medium"><?php echo $usagePercent; ?>%</span>
                    </div>
                </div>
            </div>
            
            <!-- 对话数量 -->
            <div class="bg-white rounded-lg shadow-md p-6 card">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-sm">对话数量</p>
                        <p class="text-2xl font-semibold mt-1"><?php echo number_format($userStats['chat_count']); ?></p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-comments text-purple-600"></i>
                    </div>
                </div>
                <div class="mt-4 text-sm">
                    <a href="chat.php" class="text-purple-600 hover:text-purple-700 flex items-center">
                        <span>开始新对话</span>
                        <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            
            <!-- API调用 -->
            <div class="bg-white rounded-lg shadow-md p-6 card">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-sm">API调用</p>
                        <p class="text-2xl font-semibold mt-1"><?php echo number_format($userStats['api_calls']); ?></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-plug text-green-600"></i>
                    </div>
                </div>
                <div class="mt-4 text-sm">
                    <a href="api.php" class="text-green-600 hover:text-green-700 flex items-center">
                        <span>管理API密钥</span>
                        <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- 最近对话和API令牌 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- 最近对话-->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold mb-4">最近对话</h2>
                <?php if (empty($recentChats)): ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-comment-slash text-4xl mb-2"></i>
                    <p>您还没有任何对话</p>
                    <a href="chat.php" class="mt-2 inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        开始对话
                    </a>
                </div>
                <?php else: ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($recentChats as $chat): ?>
                    <li class="py-3">
                        <a href="chat.php?id=<?php echo htmlspecialchars($chat['id']); ?>" class="flex justify-between items-center hover:bg-gray-50 p-2 rounded">
                            <div>
                                <p class="font-medium"><?php echo htmlspecialchars($chat['title'] ?? '未命名对话'); ?></p>
                                <p class="text-xs text-gray-500">
                                    <span class="mr-2"><?php echo htmlspecialchars($chat['engine_name']); ?></span>
                                    <span><?php echo date('Y-m-d H:i', strtotime($chat['last_message_at'])); ?></span>
                                </p>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400"></i>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="mt-4 text-center">
                    <a href="chat.php" class="text-blue-600 hover:text-blue-700">查看全部对话</a>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- API令牌 -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold mb-4">API令牌</h2>
                <?php if (empty($apiTokens)): ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-key text-4xl mb-2"></i>
                    <p>您还没有创建任何API令牌</p>
                    <a href="api.php" class="mt-2 inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        创建新令牌
                    </a>
                </div>
                <?php else: ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($apiTokens as $token): ?>
                    <li class="py-3">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-medium"><?php echo htmlspecialchars($token['name']); ?></p>
                                <div class="flex items-center mt-1">
                                    <div class="api-token bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs">
                                        <?php echo substr($token['api_key'],  0, 8) . '...' . substr($token['api_key'],  -4); ?>
                                    </div>
                                    <button class="ml-2 text-gray-500 hover:text-blue-600 text-xs copy-btn" data-token="<?php echo htmlspecialchars($token['api_key']); ?>">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <span class="text-xs py-1 px-2 bg-green-100 text-green-800 rounded-full">
                                活跃
                            </span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="mt-4 text-center">
                    <a href="api.php" class="text-blue-600 hover:text-blue-700">管理API令牌</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- 页脚 -->
    <footer class="bg-gray-50 border-t border-gray-200 py-4 mt-8">
        <div class="container mx-auto px-4 text-center text-gray-500 text-sm">
            <p>&copy; <?php echo date('Y'); ?> AlingAi Pro. 保留所有权利。</p>
            <div class="mt-2 flex justify-center space-x-4">
                <a href="terms.php" class="hover:text-gray-700">服务条款</a>
                <a href="privacy.php" class="hover:text-gray-700">隐私政策</a>
                <a href="support.php" class="hover:text-gray-700">联系支持</a>
            </div>
        </div>
    </footer>
    
    <script>
        // 用户菜单切换
        document.getElementById('userMenuBtn').addEventListener('click', function() {
            document.getElementById('userMenu').classList.toggle('hidden');
        });
        
        // 点击外部关闭菜单
        document.addEventListener('click', function(e) {
            const userMenu = document.getElementById('userMenu');
            const userMenuBtn = document.getElementById('userMenuBtn');
            
            if (!userMenuBtn.contains(e.target) && !userMenu.contains(e.target)) {
                userMenu.classList.add('hidden');
            }
        });
        
        // 复制API令牌
        document.querySelectorAll('.copy-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const token = this.getAttribute('data-token');
                navigator.clipboard.writeText(token).then(() => {
                    // 更改按钮样式表示复制成功
                    const icon = this.querySelector('i');
                    icon.classList.remove('fa-copy');
                    icon.classList.add('fa-check');
                    
                    // 2秒后恢复原样
                    setTimeout(() => {
                        icon.classList.remove('fa-check');
                        icon.classList.add('fa-copy');
                    }, 2000);
                });
            });
        });
    </script>
</body>
</html> 
