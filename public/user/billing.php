<?php
/**
 * AlingAi Pro - 用户账单与订阅页面
 * 
 * 允许用户查看和管理订阅计划、查看账单历史
 */

// 启动会话
session_start();

// 设置增强的安全头部
header("Content-Security-Policy: default-src \"self\"; script-src \"self\" \"unsafe-inline\" https://cdn.jsdelivr.net; style-src \"self\" \"unsafe-inline\" https://fonts.googleapis.com; font-src \"self\" https://fonts.gstatic.com; img-src \"self\" data:; connect-src \"self\";");
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
$pageTitle = "账单与订阅 - AlingAi Pro";
$pageDescription = "管理您的订阅计划和查看账单历史";
$additionalCSS = [
    "/css/user-dashboard.css",
    "/css/billing.css"
];
$additionalJS = [
    ["src" => "/js/billing.js", "defer" => true]
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

// 获取账单历史
$invoices = [];
try {
    $stmt = $db->prepare("SELECT * FROM invoices WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$userId]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "获取账单历史失败: " . $e->getMessage();
}

// 获取支付方式
$paymentMethods = [];
try {
    $stmt = $db->prepare("SELECT * FROM payment_methods WHERE user_id = ? AND is_deleted = 0 ORDER BY is_default DESC, created_at DESC");
    $stmt->execute([$userId]);
    $paymentMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "获取支付方式失败: " . $e->getMessage();
}

// 处理表单提交
$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 取消订阅
    if (isset($_POST["cancel_subscription"]) && $subscription) {
        try {
            $stmt = $db->prepare("UPDATE subscriptions SET status = \"cancelled\", updated_at = NOW() WHERE id = ? AND user_id = ?");
            $result = $stmt->execute([$subscription["id"], $userId]);
            
            if ($result) {
                $message = "订阅已成功取消。您可以继续使用当前计划直到订阅期结束。";
                $messageType = "success";
                
                // 更新订阅信息
                $stmt = $db->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND status = \"active\" ORDER BY created_at DESC LIMIT 1");
                $stmt->execute([$userId]);
                $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $message = "取消订阅失败，请稍后再试。";
                $messageType = "error";
            }
        } catch (PDOException $e) {
            $message = "取消订阅时出错: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // 添加支付方式
    if (isset($_POST["add_payment_method"])) {
        $cardNumber = filter_input(INPUT_POST, "card_number", FILTER_SANITIZE_STRING);
        $cardHolder = filter_input(INPUT_POST, "card_holder", FILTER_SANITIZE_STRING);
        $expiryMonth = filter_input(INPUT_POST, "expiry_month", FILTER_SANITIZE_STRING);
        $expiryYear = filter_input(INPUT_POST, "expiry_year", FILTER_SANITIZE_STRING);
        $isDefault = isset($_POST["is_default"]) ? 1 : 0;
        
        // 简单验证
        if (empty($cardNumber) || empty($cardHolder) || empty($expiryMonth) || empty($expiryYear)) {
            $message = "请填写所有必填字段。";
            $messageType = "error";
        } else {
            try {
                // 如果设置为默认，先将所有卡设为非默认
                if ($isDefault) {
                    $stmt = $db->prepare("UPDATE payment_methods SET is_default = 0 WHERE user_id = ?");
                    $stmt->execute([$userId]);
                }
                
                // 模拟卡号加密和掩码处理
                $lastFour = substr($cardNumber, -4);
                $maskedNumber = "****" . $lastFour;
                
                $stmt = $db->prepare("INSERT INTO payment_methods (user_id, type, provider, last_four, holder_name, expiry_month, expiry_year, is_default, created_at) VALUES (?, \"card\", \"visa\", ?, ?, ?, ?, ?, NOW())");
                $result = $stmt->execute([$userId, $lastFour, $cardHolder, $expiryMonth, $expiryYear, $isDefault]);
                
                if ($result) {
                    $message = "支付方式已成功添加。";
                    $messageType = "success";
                    
                    // 更新支付方式列表
                    $stmt = $db->prepare("SELECT * FROM payment_methods WHERE user_id = ? AND is_deleted = 0 ORDER BY is_default DESC, created_at DESC");
                    $stmt->execute([$userId]);
                    $paymentMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $message = "添加支付方式失败，请稍后再试。";
                    $messageType = "error";
                }
            } catch (PDOException $e) {
                $message = "添加支付方式时出错: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
    
    // 删除支付方式
    if (isset($_POST["delete_payment_method"])) {
        $paymentMethodId = filter_input(INPUT_POST, "payment_method_id", FILTER_VALIDATE_INT);
        
        if ($paymentMethodId) {
            try {
                // 软删除支付方式
                $stmt = $db->prepare("UPDATE payment_methods SET is_deleted = 1, updated_at = NOW() WHERE id = ? AND user_id = ?");
                $result = $stmt->execute([$paymentMethodId, $userId]);
                
                if ($result) {
                    $message = "支付方式已成功删除。";
                    $messageType = "success";
                    
                    // 更新支付方式列表
                    $stmt = $db->prepare("SELECT * FROM payment_methods WHERE user_id = ? AND is_deleted = 0 ORDER BY is_default DESC, created_at DESC");
                    $stmt->execute([$userId]);
                    $paymentMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $message = "删除支付方式失败，请稍后再试。";
                    $messageType = "error";
                }
            } catch (PDOException $e) {
                $message = "删除支付方式时出错: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
    
    // 设置默认支付方式
    if (isset($_POST["set_default_payment"])) {
        $paymentMethodId = filter_input(INPUT_POST, "payment_method_id", FILTER_VALIDATE_INT);
        
        if ($paymentMethodId) {
            try {
                // 先将所有卡设为非默认
                $stmt = $db->prepare("UPDATE payment_methods SET is_default = 0 WHERE user_id = ?");
                $stmt->execute([$userId]);
                
                // 设置选定的卡为默认
                $stmt = $db->prepare("UPDATE payment_methods SET is_default = 1, updated_at = NOW() WHERE id = ? AND user_id = ?");
                $result = $stmt->execute([$paymentMethodId, $userId]);
                
                if ($result) {
                    $message = "默认支付方式已更新。";
                    $messageType = "success";
                    
                    // 更新支付方式列表
                    $stmt = $db->prepare("SELECT * FROM payment_methods WHERE user_id = ? AND is_deleted = 0 ORDER BY is_default DESC, created_at DESC");
                    $stmt->execute([$userId]);
                    $paymentMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $message = "更新默认支付方式失败，请稍后再试。";
                    $messageType = "error";
                }
            } catch (PDOException $e) {
                $message = "更新默认支付方式时出错: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
}

// 定义订阅计划
$plans = [
    "free" => [
        "name" => "免费计划",
        "price" => "0",
        "period" => "永久",
        "features" => [
            "每月100,000个Token",
            "基础模型访问",
            "标准API速率",
            "社区支持"
        ]
    ],
    "basic" => [
        "name" => "基础计划",
        "price" => "99",
        "period" => "每月",
        "features" => [
            "每月500,000个Token",
            "所有模型访问",
            "更高API速率",
            "优先电子邮件支持",
            "基础分析"
        ]
    ],
    "pro" => [
        "name" => "专业计划",
        "price" => "299",
        "period" => "每月",
        "features" => [
            "每月2,000,000个Token",
            "所有模型优先访问",
            "高级API速率",
            "优先技术支持",
            "高级分析",
            "自定义功能"
        ]
    ],
    "enterprise" => [
        "name" => "企业计划",
        "price" => "999",
        "period" => "每月",
        "features" => [
            "每月10,000,000个Token",
            "所有模型专属访问",
            "无限API速率",
            "专属客户经理",
            "企业级分析",
            "定制开发支持",
            "SLA保障"
        ]
    ]
];

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
                <li><a href="/user/billing" class="active"><i class="fas fa-credit-card"></i> 账单与订阅</a></li>
                <li><a href="/user/api-keys"><i class="fas fa-key"></i> API密钥</a></li>
                <li><a href="/user/usage"><i class="fas fa-chart-line"></i> 使用情况</a></li>
                <li><a href="/user/security"><i class="fas fa-shield-alt"></i> 安全</a></li>
                <li><a href="/logout"><i class="fas fa-sign-out-alt"></i> 退出登录</a></li>
            </ul>
        </nav>
    </div>

    <!-- 主要内容 -->
    <div class="dashboard-main">
        <div class="dashboard-header">
            <h1>账单与订阅</h1>
            <p>管理您的订阅计划和支付方式</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType === \"success\" ? \"success\" : \"danger\" ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <!-- 当前订阅 -->
        <div class="content-card">
            <div class="card-header">
                <h2>当前订阅</h2>
            </div>
            <div class="card-body">
                <?php if ($subscription): ?>
                    <div class="subscription-info">
                        <div class="plan-details">
                            <div>
                                <h3><?= ucfirst(htmlspecialchars($plans[$subscription[\"plan\"]][\"name\"])) ?></h3>
                                <p class="plan-price"><?= htmlspecialchars($plans[$subscription[\"plan\"]][\"price\"]) ?> / <?= htmlspecialchars($plans[$subscription[\"plan\"]][\"period\"]) ?></p>
                                <p class="plan-period">下次续费日期：<?= date(\"Y年m月d日\", strtotime($subscription[\"expires_at\"])) ?></p>
                            </div>
                            
                            <div class="plan-actions">
                                <?php if ($subscription[\"auto_renew\"]): ?>
                                    <span class="auto-renew-badge">自动续费</span>
                                <?php endif; ?>
                                
                                <form action="" method="post" class="d-inline">
                                    <button type="submit" name="cancel_subscription" class="btn btn-outline-danger" onclick="return confirm(\"确定要取消订阅吗？您可以继续使用当前计划直到订阅期结束。\")">取消订阅</button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="plan-features">
                            <h4>包含功能</h4>
                            <ul>
                                <?php foreach ($plans[$subscription[\"plan\"]][\"features\"] as $feature): ?>
                                    <li><i class="fas fa-check"></i> <?= htmlspecialchars($feature) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="subscription-info">
                        <div class="plan-details">
                            <div>
                                <h3><?= htmlspecialchars($plans[\"free\"][\"name\"]) ?></h3>
                                <p class="plan-price"><?= htmlspecialchars($plans[\"free\"][\"price\"]) ?> / <?= htmlspecialchars($plans[\"free\"][\"period\"]) ?></p>
                            </div>
                            
                            <div class="plan-actions">
                                <a href="/pricing" class="btn btn-primary">升级计划</a>
                            </div>
                        </div>
                        
                        <div class="plan-features">
                            <h4>包含功能</h4>
                            <ul>
                                <?php foreach ($plans[\"free\"][\"features\"] as $feature): ?>
                                    <li><i class="fas fa-check"></i> <?= htmlspecialchars($feature) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 支付方式 -->
        <div class="content-card">
            <div class="card-header">
                <h2>支付方式</h2>
            </div>
            <div class="card-body">
                <?php if (empty($paymentMethods)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <p>您还没有添加任何支付方式</p>
                    </div>
                <?php else: ?>
                    <div class="payment-methods">
                        <?php foreach ($paymentMethods as $method): ?>
                            <div class="payment-method-item">
                                <div class="payment-method-info">
                                    <div class="card-icon">
                                        <?php if ($method[\"provider\"] === \"visa\"): ?>
                                            <i class="fab fa-cc-visa"></i>
                                        <?php elseif ($method[\"provider\"] === \"mastercard\"): ?>
                                            <i class="fab fa-cc-mastercard"></i>
                                        <?php elseif ($method[\"provider\"] === \"alipay\"): ?>
                                            <i class="fab fa-alipay"></i>
                                        <?php elseif ($method[\"provider\"] === \"wechat\"): ?>
                                            <i class="fab fa-weixin"></i>
                                        <?php else: ?>
                                            <i class="fas fa-credit-card"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-details">
                                        <div class="card-number">**** **** **** <?= htmlspecialchars($method[\"last_four\"]) ?></div>
                                        <div class="card-meta">
                                            <span class="card-holder"><?= htmlspecialchars($method[\"holder_name\"]) ?></span>
                                            <span class="card-expiry">有效期至 <?= htmlspecialchars($method[\"expiry_month\"]) ?>/<?= htmlspecialchars($method[\"expiry_year\"]) ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="payment-method-actions">
                                    <?php if ($method[\"is_default\"]): ?>
                                        <span class="default-badge">默认</span>
                                    <?php else: ?>
                                        <form action="" method="post" class="d-inline">
                                            <input type="hidden" name="payment_method_id" value="<?= $method[\"id\"] ?>">
                                            <button type="submit" name="set_default_payment" class="btn btn-sm btn-outline">设为默认</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form action="" method="post" class="d-inline">
                                        <input type="hidden" name="payment_method_id" value="<?= $method[\"id\"] ?>">
                                        <button type="submit" name="delete_payment_method" class="btn btn-sm btn-danger" onclick="return confirm(\"确定要删除此支付方式吗？\")">删除</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="add-payment-section">
                    <button id="showAddPaymentForm" class="btn btn-outline">添加新支付方式</button>
                    
                    <div id="addPaymentForm" class="payment-form" style="display: none;">
                        <h3>添加新支付方式</h3>
                        <form action="" method="post">
                            <div class="form-group">
                                <label for="card_number">卡号 <span class="required">*</span></label>
                                <input type="text" id="card_number" name="card_number" required placeholder="1234 5678 9012 3456" pattern="[0-9\\s]{13,19}" maxlength="19">
                            </div>
                            
                            <div class="form-group">
                                <label for="card_holder">持卡人姓名 <span class="required">*</span></label>
                                <input type="text" id="card_holder" name="card_holder" required placeholder="持卡人姓名">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiry_month">到期月份 <span class="required">*</span></label>
                                    <select id="expiry_month" name="expiry_month" required>
                                        <?php for ($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?= sprintf(\"%02d\", $i) ?>"><?= sprintf(\"%02d\", $i) ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="expiry_year">到期年份 <span class="required">*</span></label>
                                    <select id="expiry_year" name="expiry_year" required>
                                        <?php $currentYear = (int)date(\"Y\"); ?>
                                        <?php for ($i = $currentYear; $i <= $currentYear + 10; $i++): ?>
                                            <option value="<?= substr($i, 2) ?>"><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="cvv">安全码 <span class="required">*</span></label>
                                    <input type="password" id="cvv" name="cvv" required placeholder="CVV" pattern="[0-9]{3,4}" maxlength="4">
                                </div>
                            </div>
                            
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="is_default" name="is_default" checked>
                                <label for="is_default">设为默认支付方式</label>
                            </div>
                            
                            <div class="form-actions">
                                <button type="button" id="cancelAddPayment" class="btn btn-outline">取消</button>
                                <button type="submit" name="add_payment_method" class="btn btn-primary">添加支付方式</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 账单历史 -->
        <div class="content-card">
            <div class="card-header">
                <h2>账单历史</h2>
            </div>
            <div class="card-body">
                <?php if (empty($invoices)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <p>您还没有任何账单记录</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="invoice-table">
                            <thead>
                                <tr>
                                    <th>账单号</th>
                                    <th>日期</th>
                                    <th>金额</th>
                                    <th>状态</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoices as $invoice): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($invoice[\"invoice_number\"]) ?></td>
                                        <td><?= date(\"Y-m-d\", strtotime($invoice[\"created_at\"])) ?></td>
                                        <td><?= number_format($invoice[\"amount\"], 2) ?></td>
                                        <td>
                                            <?php 
                                            $statusClass = "";
                                            switch ($invoice[\"status\"]) {
                                                case "paid":
                                                    $statusText = "已支付";
                                                    $statusClass = "status-success";
                                                    break;
                                                case "pending":
                                                    $statusText = "待支付";
                                                    $statusClass = "status-warning";
                                                    break;
                                                case "failed":
                                                    $statusText = "支付失败";
                                                    $statusClass = "status-error";
                                                    break;
                                                default:
                                                    $statusText = $invoice[\"status\"];
                                            }
                                            ?>
                                            <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                                        </td>
                                        <td>
                                            <a href="/user/invoice/<?= $invoice[\"id\"] ?>" class="btn btn-sm btn-outline" target="_blank">
                                                <i class="fas fa-download"></i> 下载
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<style>
    /* 账单与订阅页面特定样式 */
    .subscription-info {
        padding: var(--spacing-md) 0;
    }
    
    .plan-details {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: var(--spacing-lg);
    }
    
    .plan-details h3 {
        margin-bottom: 5px;
        color: var(--secondary-color);
    }
    
    .plan-price {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--accent-color);
        margin-bottom: 5px;
    }
    
    .plan-period {
        color: var(--text-color-light);
        font-size: 0.9rem;
    }
    
    .plan-features h4 {
        font-size: 1rem;
        margin-bottom: var(--spacing-sm);
        color: var(--text-color);
    }
    
    .plan-features ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: var(--spacing-sm);
    }
    
    .plan-features li {
        display: flex;
        align-items: center;
        margin-bottom: var(--spacing-xs);
    }
    
    .plan-features li i {
        color: var(--success-color);
        margin-right: var(--spacing-sm);
    }
    
    .auto-renew-badge {
        display: inline-block;
        background-color: rgba(48, 209, 88, 0.15);
        color: #30d158;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 500;
        margin-bottom: var(--spacing-sm);
    }
    
    .payment-methods {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-md);
    }
    
    .payment-method-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--glass-background);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
    }
    
    .payment-method-info {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
    }
    
    .card-icon {
        font-size: 1.8rem;
        color: var(--accent-color);
    }
    
    .card-details {
        display: flex;
        flex-direction: column;
    }
    
    .card-number {
        font-weight: 500;
        margin-bottom: 3px;
    }
    
    .card-meta {
        display: flex;
        gap: var(--spacing-md);
        font-size: 0.85rem;
        color: var(--text-color-light);
    }
    
    .payment-method-actions {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }
    
    .default-badge {
        display: inline-block;
        background-color: rgba(10, 132, 255, 0.15);
        color: var(--accent-color);
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .add-payment-section {
        margin-top: var(--spacing-lg);
    }
    
    .payment-form {
        margin-top: var(--spacing-md);
        background: var(--glass-background);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-lg);
    }
    
    .payment-form h3 {
        margin-bottom: var(--spacing-md);
        color: var(--secondary-color);
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: var(--spacing-md);
    }
    
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }
    
    .checkbox-group input[type="checkbox"] {
        margin: 0;
    }
    
    .invoice-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .invoice-table th,
    .invoice-table td {
        padding: var(--spacing-sm) var(--spacing-md);
        text-align: left;
        border-bottom: 1px solid var(--glass-border);
    }
    
    .invoice-table th {
        font-weight: 600;
        color: var(--secondary-color);
    }
    
    .status-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .status-success {
        background-color: rgba(48, 209, 88, 0.15);
        color: #30d158;
    }
    
    .status-warning {
        background-color: rgba(255, 159, 10, 0.15);
        color: #ff9f0a;
    }
    
    .status-error {
        background-color: rgba(255, 69, 58, 0.15);
        color: #ff453a;
    }
    
    .empty-state {
        text-align: center;
        padding: var(--spacing-xl) 0;
    }
    
    .empty-icon {
        font-size: 3rem;
        color: var(--text-color-light);
        margin-bottom: var(--spacing-md);
    }
    
    .required {
        color: var(--error-color);
    }
    
    .d-inline {
        display: inline-block;
    }
    
    @media (max-width: 768px) {
        .plan-details {
            flex-direction: column;
            gap: var(--spacing-md);
        }
        
        .payment-method-item {
            flex-direction: column;
            align-items: flex-start;
            gap: var(--spacing-md);
        }
        
        .payment-method-actions {
            width: 100%;
            justify-content: flex-end;
        }
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 支付方式表单显示/隐藏
        const showAddPaymentForm = document.getElementById("showAddPaymentForm");
        const addPaymentForm = document.getElementById("addPaymentForm");
        const cancelAddPayment = document.getElementById("cancelAddPayment");
        
        if (showAddPaymentForm && addPaymentForm) {
            showAddPaymentForm.addEventListener("click", function() {
                addPaymentForm.style.display = "block";
                showAddPaymentForm.style.display = "none";
            });
        }
        
        if (cancelAddPayment && addPaymentForm && showAddPaymentForm) {
            cancelAddPayment.addEventListener("click", function() {
                addPaymentForm.style.display = "none";
                showAddPaymentForm.style.display = "inline-block";
            });
        }
        
        // 信用卡号格式化
        const cardNumberInput = document.getElementById("card_number");
        if (cardNumberInput) {
            cardNumberInput.addEventListener("input", function(e) {
                // 移除所有非数字字符
                let value = e.target.value.replace(/\D/g, "");
                
                // 添加空格格式化
                if (value.length > 0) {
                    value = value.match(/.{1,4}/g).join(" ");
                }
                
                // 限制长度
                if (value.length > 19) {
                    value = value.substr(0, 19);
                }
                
                e.target.value = value;
            });
        }
    });
</script>

<?php
// 渲染页面页脚
renderPageFooter();
?>
