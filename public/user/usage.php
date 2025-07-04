<?php
/**
 * AlingAi Pro - 用户使用情况页面
 * 
 * 显示用户的API使用情况、配额和使用统计
 */

// 启动会话
session_start();

// 设置增强的安全头部
header("Content-Security-Policy: default-src \"self\"; script-src \"self\" \"unsafe-inline\" https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src \"self\" \"unsafe-inline\" https://fonts.googleapis.com; font-src \"self\" https://fonts.gstatic.com; img-src \"self\" data:; connect-src \"self\";");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// 检查是否已登录
if (!isset($_SESSION["user_id"])) {
    // 未登录，重定向到登录页面
    header("Location: /login");
    exit;
}

// 获取用户信息
$userId = $_SESSION["user_id"];
$userName = $_SESSION["user_name"] ?? "用户";
$userEmail = $_SESSION["user_email"] ?? "";

// 设置页面信息
$pageTitle = "使用情况 - AlingAi Pro";
$pageDescription = "查看您的API使用情况和配额";
$additionalCSS = [
    "/css/user-dashboard.css",
    "/css/usage.css"
];
$additionalJS = [
    ["src" => "https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js", "defer" => true],
    ["src" => "/js/usage.js", "defer" => true]
];

// 包含页面模板
require_once __DIR__ . "/../templates/page.php";

// 连接数据库
try {
    $db = new PDO("mysql:host=localhost;dbname=alingai_pro;charset=utf8mb4", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// 获取用户订阅信息
$subscription = null;
try {
    $stmt = $db->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND status = \"active\" ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$userId]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "获取订阅信息失败: " . $e->getMessage();
}

// 获取用户使用情况
$usage = [];
try {
    // 获取当前月份的使用情况
    $startOfMonth = date("Y-m-01 00:00:00");
    $endOfMonth = date("Y-m-t 23:59:59");
    
    $stmt = $db->prepare("
        SELECT 
            SUM(CASE WHEN type = \"chat\" THEN tokens ELSE 0 END) as chat_tokens,
            SUM(CASE WHEN type = \"completion\" THEN tokens ELSE 0 END) as completion_tokens,
            SUM(CASE WHEN type = \"embedding\" THEN tokens ELSE 0 END) as embedding_tokens,
            SUM(tokens) as total_tokens,
            COUNT(*) as total_requests
        FROM api_usage
        WHERE user_id = ? AND created_at BETWEEN ? AND ?
    ");
    $stmt->execute([$userId, $startOfMonth, $endOfMonth]);
    $usage["current_month"] = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 获取过去30天每天的使用情况
    $stmt = $db->prepare("
        SELECT 
            DATE(created_at) as date,
            SUM(tokens) as tokens,
            COUNT(*) as requests
        FROM api_usage
        WHERE user_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $stmt->execute([$userId]);
    $usage["daily"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 获取过去6个月每月的使用情况
    $stmt = $db->prepare("
        SELECT 
            DATE_FORMAT(created_at, \"%Y-%m\") as month,
            SUM(tokens) as tokens,
            COUNT(*) as requests
        FROM api_usage
        WHERE user_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, \"%Y-%m\")
        ORDER BY month ASC
    ");
    $stmt->execute([$userId]);
    $usage["monthly"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 获取不同模型的使用情况
    $stmt = $db->prepare("
        SELECT 
            model,
            SUM(tokens) as tokens,
            COUNT(*) as requests
        FROM api_usage
        WHERE user_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY model
        ORDER BY tokens DESC
    ");
    $stmt->execute([$userId]);
    $usage["models"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "获取使用情况失败: " . $e->getMessage();
}

// 设置默认配额（如果没有订阅）
$quotas = [
    "chat_tokens" => 100000,
    "completion_tokens" => 50000,
    "embedding_tokens" => 200000,
    "total_tokens" => 350000,
    "requests_per_day" => 100
];

// 如果有订阅，更新配额
if ($subscription) {
    $plan = $subscription["plan"];
    
    switch ($plan) {
        case "basic":
            $quotas = [
                "chat_tokens" => 500000,
                "completion_tokens" => 250000,
                "embedding_tokens" => 1000000,
                "total_tokens" => 1750000,
                "requests_per_day" => 500
            ];
            break;
        case "pro":
            $quotas = [
                "chat_tokens" => 2000000,
                "completion_tokens" => 1000000,
                "embedding_tokens" => 4000000,
                "total_tokens" => 7000000,
                "requests_per_day" => 2000
            ];
            break;
        case "enterprise":
            $quotas = [
                "chat_tokens" => 10000000,
                "completion_tokens" => 5000000,
                "embedding_tokens" => 20000000,
                "total_tokens" => 35000000,
                "requests_per_day" => 10000
            ];
            break;
    }
}

// 计算使用百分比
$usagePercentages = [
    "chat_tokens" => 0,
    "completion_tokens" => 0,
    "embedding_tokens" => 0,
    "total_tokens" => 0
];

if (!empty($usage["current_month"])) {
    $usagePercentages["chat_tokens"] = ($usage["current_month"]["chat_tokens"] / $quotas["chat_tokens"]) * 100;
    $usagePercentages["completion_tokens"] = ($usage["current_month"]["completion_tokens"] / $quotas["completion_tokens"]) * 100;
    $usagePercentages["embedding_tokens"] = ($usage["current_month"]["embedding_tokens"] / $quotas["embedding_tokens"]) * 100;
    $usagePercentages["total_tokens"] = ($usage["current_month"]["total_tokens"] / $quotas["total_tokens"]) * 100;
}

// 准备图表数据
$chartData = [
    "daily" => [
        "labels" => [],
        "tokens" => [],
        "requests" => []
    ],
    "monthly" => [
        "labels" => [],
        "tokens" => [],
        "requests" => []
    ],
    "models" => [
        "labels" => [],
        "tokens" => []
    ]
];

if (!empty($usage["daily"])) {
    foreach ($usage["daily"] as $day) {
        $chartData["daily"]["labels"][] = date("m/d", strtotime($day["date"]));
        $chartData["daily"]["tokens"][] = (int)$day["tokens"];
        $chartData["daily"]["requests"][] = (int)$day["requests"];
    }
}

if (!empty($usage["monthly"])) {
    foreach ($usage["monthly"] as $month) {
        $chartData["monthly"]["labels"][] = date("Y年m月", strtotime($month["month"] . "-01"));
        $chartData["monthly"]["tokens"][] = (int)$month["tokens"];
        $chartData["monthly"]["requests"][] = (int)$month["requests"];
    }
}

if (!empty($usage["models"])) {
    foreach ($usage["models"] as $model) {
        $chartData["models"]["labels"][] = $model["model"];
        $chartData["models"]["tokens"][] = (int)$model["tokens"];
    }
}

// 渲染页面头部
renderPageHeader();
?>

<div class="user-dashboard-container">
    <!-- 侧边导航 -->
    <div class="dashboard-sidebar">
        <div class="user-info">
            <div class="avatar-container">
                <img src="<?= htmlspecialchars($userInfo[\"avatar\"] ?? \"/assets/images/default-avatar.png\") ?>" alt="用户头像" class="user-avatar">
            </div>
            <h3><?= htmlspecialchars($userName) ?></h3>
            <p class="user-email"><?= htmlspecialchars($userEmail) ?></p>
        </div>
        
        <nav class="dashboard-nav">
            <ul>
                <li><a href="/dashboard"><i class="fas fa-tachometer-alt"></i> 控制台</a></li>
                <li><a href="/user/profile"><i class="fas fa-user"></i> 个人资料</a></li>
                <li><a href="/user/account-settings"><i class="fas fa-cog"></i> 账号设置</a></li>
                <li><a href="/user/billing"><i class="fas fa-credit-card"></i> 账单与订阅</a></li>
                <li><a href="/user/api-keys"><i class="fas fa-key"></i> API密钥</a></li>
                <li><a href="/user/usage" class="active"><i class="fas fa-chart-line"></i> 使用情况</a></li>
                <li><a href="/user/security"><i class="fas fa-shield-alt"></i> 安全</a></li>
                <li><a href="/logout"><i class="fas fa-sign-out-alt"></i> 退出登录</a></li>
            </ul>
        </nav>
    </div>

    <!-- 主要内容 -->
    <div class="dashboard-main">
        <div class="dashboard-header">
            <h1>使用情况</h1>
            <p>查看您的API使用情况和配额</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <!-- 当前计划 -->
        <div class="content-card">
            <div class="card-header">
                <h2>当前订阅计划</h2>
            </div>
            <div class="card-body">
                <div class="subscription-info">
                    <div class="plan-details">
                        <div class="plan-name">
                            <?php if ($subscription): ?>
                                <h3><?= ucfirst(htmlspecialchars($subscription[\"plan\"])) ?> 计划</h3>
                                <p class="plan-period">有效期至：<?= date(\"Y年m月d日\", strtotime($subscription[\"expires_at\"])) ?></p>
                            <?php else: ?>
                                <h3>免费计划</h3>
                                <p class="plan-period">无到期日</p>
                            <?php endif; ?>
                        </div>
                        <div class="plan-actions">
                            <?php if ($subscription): ?>
                                <a href="/user/billing" class="btn btn-outline">管理订阅</a>
                            <?php else: ?>
                                <a href="/pricing" class="btn btn-primary">升级计划</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 本月使用情况 -->
        <div class="content-card">
            <div class="card-header">
                <h2>本月使用情况</h2>
                <p class="card-subtitle"><?= date(\"Y年m月\") ?></p>
            </div>
            <div class="card-body">
                <div class="usage-stats">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-header">
                                <h3>总Token用量</h3>
                                <div class="stat-value">
                                    <?= number_format($usage[\"current_month\"][\"total_tokens\"] ?? 0) ?> / <?= number_format($quotas[\"total_tokens\"]) ?>
                                </div>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= min(100, $usagePercentages[\"total_tokens\"]) ?>%"></div>
                            </div>
                            <div class="stat-footer">
                                已使用 <?= number_format($usagePercentages[\"total_tokens\"], 1) ?>%
                            </div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-header">
                                <h3>聊天Token</h3>
                                <div class="stat-value">
                                    <?= number_format($usage[\"current_month\"][\"chat_tokens\"] ?? 0) ?> / <?= number_format($quotas[\"chat_tokens\"]) ?>
                                </div>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= min(100, $usagePercentages[\"chat_tokens\"]) ?>%"></div>
                            </div>
                            <div class="stat-footer">
                                已使用 <?= number_format($usagePercentages[\"chat_tokens\"], 1) ?>%
                            </div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-header">
                                <h3>补全Token</h3>
                                <div class="stat-value">
                                    <?= number_format($usage[\"current_month\"][\"completion_tokens\"] ?? 0) ?> / <?= number_format($quotas[\"completion_tokens\"]) ?>
                                </div>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= min(100, $usagePercentages[\"completion_tokens\"]) ?>%"></div>
                            </div>
                            <div class="stat-footer">
                                已使用 <?= number_format($usagePercentages[\"completion_tokens\"], 1) ?>%
                            </div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-header">
                                <h3>嵌入Token</h3>
                                <div class="stat-value">
                                    <?= number_format($usage[\"current_month\"][\"embedding_tokens\"] ?? 0) ?> / <?= number_format($quotas[\"embedding_tokens\"]) ?>
                                </div>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= min(100, $usagePercentages[\"embedding_tokens\"]) ?>%"></div>
                            </div>
                            <div class="stat-footer">
                                已使用 <?= number_format($usagePercentages[\"embedding_tokens\"], 1) ?>%
                            </div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-header">
                                <h3>API请求数</h3>
                                <div class="stat-value">
                                    <?= number_format($usage[\"current_month\"][\"total_requests\"] ?? 0) ?> 次
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-header">
                                <h3>每日请求限制</h3>
                                <div class="stat-value">
                                    <?= number_format($quotas[\"requests_per_day\"]) ?> 次/天
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 使用趋势 -->
        <div class="content-card">
            <div class="card-header">
                <h2>使用趋势</h2>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <div class="chart-tabs">
                        <button class="chart-tab active" data-period="daily">过去30天</button>
                        <button class="chart-tab" data-period="monthly">过去6个月</button>
                    </div>
                    
                    <div class="chart-wrapper">
                        <canvas id="usageChart"></canvas>
                    </div>
                    
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: rgba(10, 132, 255, 0.8)"></div>
                            <div class="legend-label">Token用量</div>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: rgba(48, 209, 88, 0.8)"></div>
                            <div class="legend-label">请求数</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 模型使用分布 -->
        <div class="content-card">
            <div class="card-header">
                <h2>模型使用分布</h2>
                <p class="card-subtitle">过去30天</p>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <div class="chart-wrapper">
                        <canvas id="modelChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 使用建议 -->
        <div class="content-card">
            <div class="card-header">
                <h2>优化建议</h2>
            </div>
            <div class="card-body">
                <div class="tips-container">
                    <div class="tip-item">
                        <div class="tip-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div class="tip-content">
                            <h3>优化提示词长度</h3>
                            <p>简化您的提示词可以减少输入token的使用量。尝试使用更精确、更简洁的提示词来获得相同的结果。</p>
                        </div>
                    </div>
                    
                    <div class="tip-item">
                        <div class="tip-icon">
                            <i class="fas fa-compress-alt"></i>
                        </div>
                        <div class="tip-content">
                            <h3>使用嵌入式缓存</h3>
                            <p>对于重复的嵌入请求，考虑在客户端缓存嵌入结果，以减少API调用次数和token使用量。</p>
                        </div>
                    </div>
                    
                    <div class="tip-item">
                        <div class="tip-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="tip-content">
                            <h3>选择适合的模型</h3>
                            <p>根据任务复杂度选择合适的模型。较小的模型可能足以满足简单任务，同时消耗更少的token。</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<style>
    /* 使用情况页面特定样式 */
    .card-subtitle {
        font-size: 0.9rem;
        color: var(--text-color-light);
        margin-top: 5px;
    }
    
    .subscription-info {
        padding: var(--spacing-md) 0;
    }
    
    .plan-details {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .plan-name h3 {
        margin-bottom: 5px;
        color: var(--secondary-color);
    }
    
    .plan-period {
        color: var(--text-color-light);
        font-size: 0.9rem;
    }
    
    .usage-stats {
        margin-top: var(--spacing-md);
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: var(--spacing-lg);
    }
    
    .stat-item {
        background: var(--glass-background);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
    }
    
    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-sm);
    }
    
    .stat-header h3 {
        font-size: 1rem;
        margin: 0;
        color: var(--text-color);
    }
    
    .stat-value {
        font-weight: 600;
        color: var(--accent-color);
    }
    
    .progress-bar {
        height: 8px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: var(--spacing-sm);
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--accent-color), var(--secondary-color));
        border-radius: 4px;
        transition: width 0.3s ease;
    }
    
    .stat-footer {
        font-size: 0.85rem;
        color: var(--text-color-light);
        text-align: right;
    }
    
    .chart-container {
        margin-top: var(--spacing-md);
    }
    
    .chart-tabs {
        display: flex;
        margin-bottom: var(--spacing-md);
        border-bottom: 1px solid var(--glass-border);
    }
    
    .chart-tab {
        background: none;
        border: none;
        padding: var(--spacing-sm) var(--spacing-md);
        color: var(--text-color-light);
        cursor: pointer;
        font-size: 0.9rem;
        transition: all var(--transition-fast);
        position: relative;
    }
    
    .chart-tab:hover {
        color: var(--text-color);
    }
    
    .chart-tab.active {
        color: var(--accent-color);
    }
    
    .chart-tab.active::after {
        content: "";
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: var(--accent-color);
    }
    
    .chart-wrapper {
        height: 300px;
        position: relative;
    }
    
    .chart-legend {
        display: flex;
        justify-content: center;
        gap: var(--spacing-md);
        margin-top: var(--spacing-md);
    }
    
    .legend-item {
        display: flex;
        align-items: center;
    }
    
    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 2px;
        margin-right: 8px;
    }
    
    .legend-label {
        font-size: 0.9rem;
    }
    
    .tips-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: var(--spacing-lg);
    }
    
    .tip-item {
        display: flex;
        gap: var(--spacing-md);
    }
    
    .tip-icon {
        font-size: 1.5rem;
        color: var(--accent-color);
        flex-shrink: 0;
    }
    
    .tip-content h3 {
        font-size: 1.1rem;
        margin-bottom: var(--spacing-xs);
        color: var(--secondary-color);
    }
    
    .tip-content p {
        font-size: 0.9rem;
        color: var(--text-color-light);
    }
    
    @media (max-width: 768px) {
        .plan-details {
            flex-direction: column;
            align-items: flex-start;
            gap: var(--spacing-md);
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .tips-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 图表数据
        const chartData = {
            daily: {
                labels: <?= json_encode($chartData["daily"]["labels"]) ?>,
                tokens: <?= json_encode($chartData["daily"]["tokens"]) ?>,
                requests: <?= json_encode($chartData["daily"]["requests"]) ?>
            },
            monthly: {
                labels: <?= json_encode($chartData["monthly"]["labels"]) ?>,
                tokens: <?= json_encode($chartData["monthly"]["tokens"]) ?>,
                requests: <?= json_encode($chartData["monthly"]["requests"]) ?>
            },
            models: {
                labels: <?= json_encode($chartData["models"]["labels"]) ?>,
                tokens: <?= json_encode($chartData["models"]["tokens"]) ?>
            }
        };
        
        // 使用趋势图表
        const usageCtx = document.getElementById("usageChart").getContext("2d");
        const usageChart = new Chart(usageCtx, {
            type: "bar",
            data: {
                labels: chartData.daily.labels,
                datasets: [
                    {
                        label: "Token用量",
                        data: chartData.daily.tokens,
                        backgroundColor: "rgba(10, 132, 255, 0.8)",
                        borderColor: "rgba(10, 132, 255, 1)",
                        borderWidth: 1,
                        yAxisID: "y"
                    },
                    {
                        label: "请求数",
                        data: chartData.daily.requests,
                        backgroundColor: "rgba(48, 209, 88, 0.8)",
                        borderColor: "rgba(48, 209, 88, 1)",
                        borderWidth: 1,
                        yAxisID: "y1"
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: {
                            color: "rgba(255, 255, 255, 0.05)"
                        },
                        ticks: {
                            color: "rgba(255, 255, 255, 0.7)"
                        }
                    },
                    y: {
                        position: "left",
                        title: {
                            display: true,
                            text: "Token用量",
                            color: "rgba(10, 132, 255, 0.8)"
                        },
                        grid: {
                            color: "rgba(255, 255, 255, 0.05)"
                        },
                        ticks: {
                            color: "rgba(255, 255, 255, 0.7)"
                        }
                    },
                    y1: {
                        position: "right",
                        title: {
                            display: true,
                            text: "请求数",
                            color: "rgba(48, 209, 88, 0.8)"
                        },
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            color: "rgba(255, 255, 255, 0.7)"
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: "index",
                        intersect: false
                    }
                }
            }
        });
        
        // 模型使用分布图表
        const modelCtx = document.getElementById("modelChart").getContext("2d");
        const modelChart = new Chart(modelCtx, {
            type: "pie",
            data: {
                labels: chartData.models.labels,
                datasets: [
                    {
                        data: chartData.models.tokens,
                        backgroundColor: [
                            "rgba(10, 132, 255, 0.8)",
                            "rgba(48, 209, 88, 0.8)",
                            "rgba(255, 159, 10, 0.8)",
                            "rgba(191, 90, 242, 0.8)",
                            "rgba(94, 92, 230, 0.8)"
                        ],
                        borderColor: "rgba(255, 255, 255, 0.1)",
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "right",
                        labels: {
                            color: "rgba(255, 255, 255, 0.7)",
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: "circle"
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || "";
                                const value = context.raw;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value.toLocaleString()} tokens (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        
        // 图表切换
        const chartTabs = document.querySelectorAll(".chart-tab");
        chartTabs.forEach(tab => {
            tab.addEventListener("click", function() {
                const period = this.getAttribute("data-period");
                
                // 更新激活状态
                chartTabs.forEach(t => t.classList.remove("active"));
                this.classList.add("active");
                
                // 更新图表数据
                usageChart.data.labels = chartData[period].labels;
                usageChart.data.datasets[0].data = chartData[period].tokens;
                usageChart.data.datasets[1].data = chartData[period].requests;
                usageChart.update();
            });
        });
    });
</script>

<?php
// 渲染页面页脚
renderPageFooter();
?>
