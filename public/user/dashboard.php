<?php
/**
 * AlingAi Pro - 用户控制台仪表盘
 * 
 * 显示用户仪表盘信息，包括用量统计、会员信息等
 */

// 启动会话
session_start();

// 设置增强的安全头部
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self';");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// 检查是否已登录
if (!isset($_SESSION['user_id'])) {
    // 未登录，重定向到登录页面
    header('Location: /login');
    exit;
}

// 获取用户信息
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? '用户';
$userEmail = $_SESSION['user_email'] ?? '';

// 连接数据库
$db = connectToDatabase();

// 获取用户详细信息
$userInfo = getUserInfo($db, $userId);

// 获取会员等级信息
$membershipInfo = getMembershipInfo($db, $userId);

// 获取API使用情况
$apiUsage = getApiUsage($db, $userId);

// 获取AI使用情况
$aiUsage = getAiUsage($db, $userId);

// 获取存储使用情况
$storageUsage = getStorageUsage($db, $userId);

// 获取最近活动
$recentActivities = getRecentActivities($db, $userId, 5);

// 获取推荐信息
$referralInfo = getReferralInfo($db, $userId);

/**
 * 连接到数据库
 * 
 * @return PDO 数据库连接
 */
function connectToDatabase() {
    $host = 'localhost';
    $dbname = 'alingai_pro';
    $username = 'root';
    $password = '';
    
    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        die('数据库连接失败: ' . $e->getMessage());
    }
}

/**
 * 获取用户信息
 * 
 * @param PDO $db 数据库连接
 * @param int $userId 用户ID
 * @return array 用户信息
 */
function getUserInfo($db, $userId) {
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

/**
 * 获取会员等级信息
 * 
 * @param PDO $db 数据库连接
 * @param int $userId 用户ID
 * @return array 会员等级信息
 */
function getMembershipInfo($db, $userId) {
    $stmt = $db->prepare('
        SELECT ms.*, ml.name, ml.description, ml.monthly_price, ml.api_quota, ml.ai_quota, ml.storage_quota 
        FROM membership_subscriptions ms
        JOIN membership_levels ml ON ms.level_id = ml.id
        WHERE ms.user_id = ? AND ms.status = "active"
        ORDER BY ms.created_at DESC
        LIMIT 1
    ');
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
        'name' => '免费会员',
        'description' => '基础功能',
        'monthly_price' => 0,
        'api_quota' => 100,
        'ai_quota' => 50,
        'storage_quota' => 100 * 1024 * 1024, // 100MB
        'expires_at' => null
    ];
}

/**
 * 获取API使用情况
 * 
 * @param PDO $db 数据库连接
 * @param int $userId 用户ID
 * @return array API使用情况
 */
function getApiUsage($db, $userId) {
    // 获取今日使用量
    $stmt = $db->prepare('
        SELECT COALESCE(SUM(amount), 0) as today_usage
        FROM quota_usage
        WHERE user_id = ? AND quota_type = "api" AND DATE(created_at) = CURDATE()
    ');
    $stmt->execute([$userId]);
    $todayUsage = $stmt->fetchColumn() ?: 0;
    
    // 获取本月使用量
    $stmt = $db->prepare('
        SELECT COALESCE(SUM(amount), 0) as month_usage
        FROM quota_usage
        WHERE user_id = ? AND quota_type = "api" 
        AND YEAR(created_at) = YEAR(CURDATE()) 
        AND MONTH(created_at) = MONTH(CURDATE())
    ');
    $stmt->execute([$userId]);
    $monthUsage = $stmt->fetchColumn() ?: 0;
    
    // 获取配额
    $stmt = $db->prepare('
        SELECT ml.api_quota
        FROM membership_subscriptions ms
        JOIN membership_levels ml ON ms.level_id = ml.id
        WHERE ms.user_id = ? AND ms.status = "active"
        ORDER BY ms.created_at DESC
        LIMIT 1
    ');
    $stmt->execute([$userId]);
    $quota = $stmt->fetchColumn() ?: 100; // 默认为100
    
    return [
        'today' => $todayUsage,
        'month' => $monthUsage,
        'quota' => $quota,
        'percent' => $quota > 0 ? min(100, round(($monthUsage / $quota) * 100)) : 0
    ];
}

/**
 * 获取AI使用情况
 * 
 * @param PDO $db 数据库连接
 * @param int $userId 用户ID
 * @return array AI使用情况
 */
function getAiUsage($db, $userId) {
    // 获取今日使用量
    $stmt = $db->prepare('
        SELECT COALESCE(SUM(amount), 0) as today_usage
        FROM quota_usage
        WHERE user_id = ? AND quota_type = "ai" AND DATE(created_at) = CURDATE()
    ');
    $stmt->execute([$userId]);
    $todayUsage = $stmt->fetchColumn() ?: 0;
    
    // 获取本月使用量
    $stmt = $db->prepare('
        SELECT COALESCE(SUM(amount), 0) as month_usage
        FROM quota_usage
        WHERE user_id = ? AND quota_type = "ai" 
        AND YEAR(created_at) = YEAR(CURDATE()) 
        AND MONTH(created_at) = MONTH(CURDATE())
    ');
    $stmt->execute([$userId]);
    $monthUsage = $stmt->fetchColumn() ?: 0;
    
    // 获取配额
    $stmt = $db->prepare('
        SELECT ml.ai_quota
        FROM membership_subscriptions ms
        JOIN membership_levels ml ON ms.level_id = ml.id
        WHERE ms.user_id = ? AND ms.status = "active"
        ORDER BY ms.created_at DESC
        LIMIT 1
    ');
    $stmt->execute([$userId]);
    $quota = $stmt->fetchColumn() ?: 50; // 默认为50
    
    return [
        'today' => $todayUsage,
        'month' => $monthUsage,
        'quota' => $quota,
        'percent' => $quota > 0 ? min(100, round(($monthUsage / $quota) * 100)) : 0
    ];
}

/**
 * 获取存储使用情况
 * 
 * @param PDO $db 数据库连接
 * @param int $userId 用户ID
 * @return array 存储使用情况
 */
function getStorageUsage($db, $userId) {
    // 获取使用量
    $stmt = $db->prepare('
        SELECT COALESCE(SUM(size), 0) as usage
        FROM user_files
        WHERE user_id = ?
    ');
    $stmt->execute([$userId]);
    $usage = $stmt->fetchColumn() ?: 0;
    
    // 获取配额
    $stmt = $db->prepare('
        SELECT ml.storage_quota
        FROM membership_subscriptions ms
        JOIN membership_levels ml ON ms.level_id = ml.id
        WHERE ms.user_id = ? AND ms.status = "active"
        ORDER BY ms.created_at DESC
        LIMIT 1
    ');
    $stmt->execute([$userId]);
    $quota = $stmt->fetchColumn() ?: 100 * 1024 * 1024; // 默认为100MB
    
    return [
        'usage' => $usage,
        'usage_formatted' => formatBytes($usage),
        'quota' => $quota,
        'quota_formatted' => formatBytes($quota),
        'percent' => $quota > 0 ? min(100, round(($usage / $quota) * 100)) : 0
    ];
}

/**
 * 格式化字节数
 * 
 * @param int $bytes 字节数
 * @return string 格式化后的字符串
 */
function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * 获取最近活动
 * 
 * @param PDO $db 数据库连接
 * @param int $userId 用户ID
 * @param int $limit 限制数量
 * @return array 最近活动
 */
function getRecentActivities($db, $userId, $limit) {
    $stmt = $db->prepare('
        SELECT * FROM user_activities
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ');
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * 获取推荐信息
 * 
 * @param PDO $db 数据库连接
 * @param int $userId 用户ID
 * @return array 推荐信息
 */
function getReferralInfo($db, $userId) {
    // 获取推荐码
    $stmt = $db->prepare('SELECT referral_code FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $referralCode = $stmt->fetchColumn() ?: '';
    
    // 获取推荐人数
    $stmt = $db->prepare('
        SELECT COUNT(*) FROM referrals
        WHERE referrer_id = ?
    ');
    $stmt->execute([$userId]);
    $referralCount = $stmt->fetchColumn() ?: 0;
    
    // 获取推荐奖励
    $stmt = $db->prepare('
        SELECT COALESCE(SUM(amount), 0) FROM points
        WHERE user_id = ? AND type = "referral"
    ');
    $stmt->execute([$userId]);
    $referralRewards = $stmt->fetchColumn() ?: 0;
    
    return [
        'code' => $referralCode,
        'link' => 'https://' . $_SERVER['HTTP_HOST'] . '/register?ref=' . $referralCode,
        'count' => $referralCount,
        'rewards' => $referralRewards
    ];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户仪表盘 - AlingAi Pro</title>
    
    <!-- 核心资源 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, #6B46C1 0%, #3B82F6 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            padding: 1.5rem;
            height: 100%;
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 3rem;
            height: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .bg-purple-soft {
            background-color: rgba(107, 70, 193, 0.1);
            color: #6B46C1;
        }
        
        .bg-blue-soft {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3B82F6;
        }
        
        .bg-green-soft {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10B981;
        }
        
        .bg-orange-soft {
            background-color: rgba(245, 158, 11, 0.1);
            color: #F59E0B;
        }
        
        .progress {
            height: 0.5rem;
        }
        
        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .referral-card {
            background: linear-gradient(135deg, #6B46C1 0%, #3B82F6 100%);
            color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
        }
        
        .referral-link {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 0.25rem;
            padding: 0.5rem;
            font-family: monospace;
            margin-bottom: 1rem;
            position: relative;
        }
        
        .copy-btn {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: white;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">AlingAi Pro</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="/user/dashboard">仪表盘</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/user/api-keys">API密钥</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/user/subscription">会员订阅</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/user/orders">订单记录</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/user/referrals">推荐计划</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($userName); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/user/profile"><i class="fas fa-user me-2"></i>个人资料</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout"><i class="fas fa-sign-out-alt me-2"></i>退出登录</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- 仪表盘头部 -->
    <header class="dashboard-header">
        <div class="container">
            <h1 class="mb-2">欢迎回来，<?php echo htmlspecialchars($userName); ?></h1>
            <p class="mb-0">当前会员等级：<?php echo htmlspecialchars($membershipInfo['name']); ?></p>
        </div>
    </header>

    <!-- 主要内容 -->
    <main class="container mb-5">
        <!-- 统计卡片 -->
        <div class="row g-4 mb-5">
            <!-- API使用情况 -->
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-purple-soft">
                        <i class="fas fa-code"></i>
                    </div>
                    <h5>API调用</h5>
                    <div class="d-flex justify-content-between mb-1">
                        <span>本月使用</span>
                        <span><?php echo number_format($apiUsage['month']); ?> / <?php echo number_format($apiUsage['quota']); ?></span>
                    </div>
                    <div class="progress mb-2">
                        <div class="progress-bar bg-purple" role="progressbar" style="width: <?php echo $apiUsage['percent']; ?>%"></div>
                    </div>
                    <small class="text-muted">今日已使用 <?php echo number_format($apiUsage['today']); ?> 次</small>
                </div>
            </div>
            
            <!-- AI使用情况 -->
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-blue-soft">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h5>AI使用</h5>
                    <div class="d-flex justify-content-between mb-1">
                        <span>本月使用</span>
                        <span><?php echo number_format($aiUsage['month']); ?> / <?php echo number_format($aiUsage['quota']); ?></span>
                    </div>
                    <div class="progress mb-2">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $aiUsage['percent']; ?>%"></div>
                    </div>
                    <small class="text-muted">今日已使用 <?php echo number_format($aiUsage['today']); ?> 次</small>
                </div>
            </div>
            
            <!-- 存储使用情况 -->
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-green-soft">
                        <i class="fas fa-hdd"></i>
                    </div>
                    <h5>存储空间</h5>
                    <div class="d-flex justify-content-between mb-1">
                        <span>已使用</span>
                        <span><?php echo $storageUsage['usage_formatted']; ?> / <?php echo $storageUsage['quota_formatted']; ?></span>
                    </div>
                    <div class="progress mb-2">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $storageUsage['percent']; ?>%"></div>
                    </div>
                    <small class="text-muted">剩余 <?php echo formatBytes($storageUsage['quota'] - $storageUsage['usage']); ?></small>
                </div>
            </div>
            
            <!-- 会员信息 -->
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-orange-soft">
                        <i class="fas fa-crown"></i>
                    </div>
                    <h5>会员信息</h5>
                    <p class="mb-1"><?php echo htmlspecialchars($membershipInfo['name']); ?></p>
                    <p class="mb-1"><?php echo htmlspecialchars($membershipInfo['description']); ?></p>
                    <?php if (!empty($membershipInfo['expires_at'])): ?>
                        <small class="text-muted">到期时间: <?php echo date('Y-m-d', strtotime($membershipInfo['expires_at'])); ?></small>
                    <?php else: ?>
                        <small class="text-muted">永久有效</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- 最近活动 -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">最近活动</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentActivities)): ?>
                            <div class="p-4 text-center">
                                <p class="text-muted mb-0">暂无活动记录</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recentActivities as $activity): ?>
                                <div class="activity-item">
                                    <div class="d-flex align-items-center">
                                        <?php
                                        $iconClass = 'fa-circle-info';
                                        $iconColor = 'text-primary';
                                        
                                        switch ($activity['type']) {
                                            case 'login':
                                                $iconClass = 'fa-sign-in-alt';
                                                $iconColor = 'text-success';
                                                break;
                                            case 'api_call':
                                                $iconClass = 'fa-code';
                                                $iconColor = 'text-primary';
                                                break;
                                            case 'payment':
                                                $iconClass = 'fa-credit-card';
                                                $iconColor = 'text-info';
                                                break;
                                            case 'error':
                                                $iconClass = 'fa-exclamation-circle';
                                                $iconColor = 'text-danger';
                                                break;
                                        }
                                        ?>
                                        <div class="me-3">
                                            <i class="fas <?php echo $iconClass; ?> fa-lg <?php echo $iconColor; ?>"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0"><?php echo htmlspecialchars($activity['description']); ?></p>
                                            <small class="text-muted"><?php echo date('Y-m-d H:i:s', strtotime($activity['created_at'])); ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- 推荐计划 -->
            <div class="col-lg-4">
                <div class="referral-card">
                    <h5 class="mb-3">推荐好友，共享奖励</h5>
                    <p>您已成功推荐 <strong><?php echo $referralInfo['count']; ?></strong> 位好友，获得 <strong><?php echo $referralInfo['rewards']; ?></strong> 积分奖励</p>
                    
                    <div class="mb-3">
                        <label class="form-label">您的专属推荐链接</label>
                        <div class="referral-link">
                            <?php echo htmlspecialchars($referralInfo['link']); ?>
                            <button class="copy-btn" onclick="copyToClipboard('<?php echo htmlspecialchars($referralInfo['link']); ?>')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">推荐码</label>
                        <div class="referral-link">
                            <?php echo htmlspecialchars($referralInfo['code']); ?>
                            <button class="copy-btn" onclick="copyToClipboard('<?php echo htmlspecialchars($referralInfo['code']); ?>')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    
                    <a href="/user/referrals" class="btn btn-light">查看详情</a>
                </div>
            </div>
        </div>
    </main>

    <!-- 页脚 -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; <?php echo date('Y'); ?> AlingAi Pro. 保留所有权利。</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="/terms" class="text-white me-3">服务条款</a>
                    <a href="/privacy" class="text-white me-3">隐私政策</a>
                    <a href="/support" class="text-white">联系支持</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('已复制到剪贴板');
            }, function() {
                alert('复制失败，请手动复制');
            });
        }
    </script>
</body>
</html> 