<?php
/**
 * AlingAi Pro - 用户安全设置页面
 * 
 * 允许用户管理密码、双因素认证和登录会话
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
$pageTitle = "安全设置 - AlingAi Pro";
$pageDescription = "管理您的账号安全设置";
$additionalCSS = [
    "/css/user-dashboard.css",
    "/css/security.css"
];
$additionalJS = [
    ["src" => "/js/security.js", "defer" => true]
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

// 获取用户安全设置
$securitySettings = [];
try {
    $stmt = $db->prepare("SELECT * FROM user_security WHERE user_id = ?");
    $stmt->execute([$userId]);
    $securitySettings = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    $error = "获取安全设置失败: " . $e->getMessage();
}

// 获取用户登录会话
$sessions = [];
try {
    $stmt = $db->prepare("
        SELECT * FROM user_sessions 
        WHERE user_id = ? 
        ORDER BY last_activity_at DESC
    ");
    $stmt->execute([$userId]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "获取登录会话失败: " . $e->getMessage();
}

// 获取用户活动日志
$activityLogs = [];
try {
    $stmt = $db->prepare("
        SELECT * FROM user_activity_logs 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $activityLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "获取活动日志失败: " . $e->getMessage();
}

// 处理表单提交
$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 修改密码
    if (isset($_POST["change_password"])) {
        $currentPassword = $_POST["current_password"] ?? "";
        $newPassword = $_POST["new_password"] ?? "";
        $confirmPassword = $_POST["confirm_password"] ?? "";
        
        // 验证输入
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $message = "所有密码字段都是必填的。";
            $messageType = "error";
        } elseif ($newPassword !== $confirmPassword) {
            $message = "新密码和确认密码不匹配。";
            $messageType = "error";
        } elseif (strlen($newPassword) < 8) {
            $message = "新密码必须至少包含8个字符。";
            $messageType = "error";
        } else {
            try {
                // 验证当前密码
                $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($currentPassword, $user["password"])) {
                    // 更新密码
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
                    $result = $stmt->execute([$hashedPassword, $userId]);
                    
                    if ($result) {
                        $message = "密码已成功更新。";
                        $messageType = "success";
                        
                        // 记录活动日志
                        $stmt = $db->prepare("INSERT INTO user_activity_logs (user_id, action, ip_address, user_agent, created_at) VALUES (?, \"password_changed\", ?, ?, NOW())");
                        $stmt->execute([$userId, $_SERVER["REMOTE_ADDR"], $_SERVER["HTTP_USER_AGENT"]]);
                    } else {
                        $message = "密码更新失败，请稍后再试。";
                        $messageType = "error";
                    }
                } else {
                    $message = "当前密码不正确。";
                    $messageType = "error";
                }
            } catch (PDOException $e) {
                $message = "更新密码时出错: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
    
    // 启用/禁用双因素认证
    if (isset($_POST["toggle_2fa"])) {
        $enable2FA = isset($_POST["enable_2fa"]) ? 1 : 0;
        
        try {
            // 检查是否已有安全设置记录
            $stmt = $db->prepare("SELECT id FROM user_security WHERE user_id = ?");
            $stmt->execute([$userId]);
            $existingSettings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingSettings) {
                // 更新现有记录
                $stmt = $db->prepare("UPDATE user_security SET two_factor_enabled = ?, updated_at = NOW() WHERE user_id = ?");
                $result = $stmt->execute([$enable2FA, $userId]);
            } else {
                // 创建新记录
                $stmt = $db->prepare("INSERT INTO user_security (user_id, two_factor_enabled, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                $result = $stmt->execute([$userId, $enable2FA]);
            }
            
            if ($result) {
                $message = $enable2FA ? "双因素认证已启用。" : "双因素认证已禁用。";
                $messageType = "success";
                
                // 更新安全设置
                $securitySettings["two_factor_enabled"] = $enable2FA;
                
                // 记录活动日志
                $action = $enable2FA ? "2fa_enabled" : "2fa_disabled";
                $stmt = $db->prepare("INSERT INTO user_activity_logs (user_id, action, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$userId, $action, $_SERVER["REMOTE_ADDR"], $_SERVER["HTTP_USER_AGENT"]]);
            } else {
                $message = "更新双因素认证设置失败，请稍后再试。";
                $messageType = "error";
            }
        } catch (PDOException $e) {
            $message = "更新双因素认证设置时出错: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // 结束其他会话
    if (isset($_POST["end_other_sessions"])) {
        try {
            $currentSessionId = session_id();
            $stmt = $db->prepare("DELETE FROM user_sessions WHERE user_id = ? AND session_id != ?");
            $result = $stmt->execute([$userId, $currentSessionId]);
            
            if ($result) {
                $message = "所有其他设备的会话已结束。";
                $messageType = "success";
                
                // 更新会话列表
                $stmt = $db->prepare("SELECT * FROM user_sessions WHERE user_id = ? ORDER BY last_activity_at DESC");
                $stmt->execute([$userId]);
                $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // 记录活动日志
                $stmt = $db->prepare("INSERT INTO user_activity_logs (user_id, action, ip_address, user_agent, created_at) VALUES (?, \"sessions_terminated\", ?, ?, NOW())");
                $stmt->execute([$userId, $_SERVER["REMOTE_ADDR"], $_SERVER["HTTP_USER_AGENT"]]);
            } else {
                $message = "结束其他会话失败，请稍后再试。";
                $messageType = "error";
            }
        } catch (PDOException $e) {
            $message = "结束其他会话时出错: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // 结束特定会话
    if (isset($_POST["end_session"])) {
        $sessionId = $_POST["session_id"] ?? "";
        
        if ($sessionId) {
            try {
                $stmt = $db->prepare("DELETE FROM user_sessions WHERE user_id = ? AND session_id = ?");
                $result = $stmt->execute([$userId, $sessionId]);
                
                if ($result) {
                    $message = "会话已成功结束。";
                    $messageType = "success";
                    
                    // 更新会话列表
                    $stmt = $db->prepare("SELECT * FROM user_sessions WHERE user_id = ? ORDER BY last_activity_at DESC");
                    $stmt->execute([$userId]);
                    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // 记录活动日志
                    $stmt = $db->prepare("INSERT INTO user_activity_logs (user_id, action, ip_address, user_agent, created_at) VALUES (?, \"session_terminated\", ?, ?, NOW())");
                    $stmt->execute([$userId, $_SERVER["REMOTE_ADDR"], $_SERVER["HTTP_USER_AGENT"]]);
                } else {
                    $message = "结束会话失败，请稍后再试。";
                    $messageType = "error";
                }
            } catch (PDOException $e) {
                $message = "结束会话时出错: " . $e->getMessage();
                $messageType = "error";
            }
        }
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
                <li><a href="/user/usage"><i class="fas fa-chart-line"></i> 使用情况</a></li>
                <li><a href="/user/security" class="active"><i class="fas fa-shield-alt"></i> 安全</a></li>
                <li><a href="/logout"><i class="fas fa-sign-out-alt"></i> 退出登录</a></li>
            </ul>
        </nav>
    </div>

    <!-- 主要内容 -->
    <div class="dashboard-main">
        <div class="dashboard-header">
            <h1>安全设置</h1>
            <p>管理您的账号安全选项</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType === \"success\" ? \"success\" : \"danger\" ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <!-- 密码管理 -->
        <div class="content-card">
            <div class="card-header">
                <h2>修改密码</h2>
            </div>
            <div class="card-body">
                <form action="" method="post" id="passwordForm">
                    <div class="form-group">
                        <label for="current_password">当前密码 <span class="required">*</span></label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">新密码 <span class="required">*</span></label>
                        <input type="password" id="new_password" name="new_password" required minlength="8">
                        <div class="password-strength">
                            <div class="strength-meter">
                                <div class="strength-meter-fill" id="passwordStrength"></div>
                            </div>
                            <div class="strength-text" id="passwordStrengthText">密码强度</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">确认新密码 <span class="required">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>
                    
                    <div class="password-requirements">
                        <h4>密码要求</h4>
                        <ul>
                            <li id="req-length"><i class="fas fa-circle"></i> 至少8个字符</li>
                            <li id="req-uppercase"><i class="fas fa-circle"></i> 至少一个大写字母</li>
                            <li id="req-lowercase"><i class="fas fa-circle"></i> 至少一个小写字母</li>
                            <li id="req-number"><i class="fas fa-circle"></i> 至少一个数字</li>
                            <li id="req-special"><i class="fas fa-circle"></i> 至少一个特殊字符</li>
                        </ul>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="change_password" class="btn btn-primary">更新密码</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- 双因素认证 -->
        <div class="content-card">
            <div class="card-header">
                <h2>双因素认证</h2>
            </div>
            <div class="card-body">
                <div class="two-factor-container">
                    <div class="two-factor-info">
                        <div class="two-factor-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="two-factor-content">
                            <h3>增强您的账号安全</h3>
                            <p>启用双因素认证后，除了密码外，您还需要输入手机验证码才能登录。这可以有效防止未经授权的访问。</p>
                            
                            <form action="" method="post" class="toggle-form">
                                <div class="toggle-switch">
                                    <input type="checkbox" id="enable_2fa" name="enable_2fa" class="toggle-input" <?= !empty($securitySettings["two_factor_enabled"]) ? "checked" : "" ?>>
                                    <label for="enable_2fa" class="toggle-label"></label>
                                    <span class="toggle-text"><?= !empty($securitySettings["two_factor_enabled"]) ? "已启用" : "已禁用" ?></span>
                                </div>
                                <button type="submit" name="toggle_2fa" class="btn btn-primary">保存设置</button>
                            </form>
                        </div>
                    </div>
                    
                    <?php if (!empty($securitySettings["two_factor_enabled"])): ?>
                        <div class="two-factor-setup">
                            <h4>已配置的验证方式</h4>
                            <div class="verification-method">
                                <div class="method-icon">
                                    <i class="fas fa-sms"></i>
                                </div>
                                <div class="method-details">
                                    <div class="method-name">短信验证</div>
                                    <div class="method-value">手机号: <?= substr($securitySettings["phone_number"] ?? "", 0, 3) . "****" . substr($securitySettings["phone_number"] ?? "", -4) ?></div>
                                </div>
                                <div class="method-actions">
                                    <button type="button" class="btn btn-sm btn-outline" id="changePhoneBtn">更换</button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- 登录会话 -->
        <div class="content-card">
            <div class="card-header">
                <h2>登录会话</h2>
            </div>
            <div class="card-body">
                <div class="sessions-container">
                    <div class="sessions-header">
                        <p>以下是您当前的登录会话列表。如果您发现可疑活动，可以结束相应的会话。</p>
                        <form action="" method="post">
                            <button type="submit" name="end_other_sessions" class="btn btn-danger" onclick="return confirm(\"确定要结束所有其他设备上的会话吗？\")">结束所有其他会话</button>
                        </form>
                    </div>
                    
                    <?php if (empty($sessions)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <p>没有找到活动会话</p>
                        </div>
                    <?php else: ?>
                        <div class="sessions-list">
                            <?php foreach ($sessions as $session): ?>
                                <?php
                                $isCurrentSession = $session["session_id"] === session_id();
                                $deviceIcon = "fas fa-laptop";
                                
                                if (strpos(strtolower($session["user_agent"]), "mobile") !== false) {
                                    $deviceIcon = "fas fa-mobile-alt";
                                } elseif (strpos(strtolower($session["user_agent"]), "tablet") !== false) {
                                    $deviceIcon = "fas fa-tablet-alt";
                                }
                                
                                // 简化用户代理字符串
                                $userAgent = $session["user_agent"];
                                $browser = "未知浏览器";
                                $os = "未知系统";
                                
                                if (strpos($userAgent, "Chrome") !== false) $browser = "Chrome";
                                elseif (strpos($userAgent, "Firefox") !== false) $browser = "Firefox";
                                elseif (strpos($userAgent, "Safari") !== false) $browser = "Safari";
                                elseif (strpos($userAgent, "Edge") !== false) $browser = "Edge";
                                elseif (strpos($userAgent, "MSIE") !== false || strpos($userAgent, "Trident") !== false) $browser = "Internet Explorer";
                                
                                if (strpos($userAgent, "Windows") !== false) $os = "Windows";
                                elseif (strpos($userAgent, "Mac") !== false) $os = "macOS";
                                elseif (strpos($userAgent, "iPhone") !== false) $os = "iOS";
                                elseif (strpos($userAgent, "iPad") !== false) $os = "iPadOS";
                                elseif (strpos($userAgent, "Android") !== false) $os = "Android";
                                elseif (strpos($userAgent, "Linux") !== false) $os = "Linux";
                                ?>
                                
                                <div class="session-item <?= $isCurrentSession ? "current-session" : "" ?>">
                                    <div class="session-icon">
                                        <i class="<?= $deviceIcon ?>"></i>
                                    </div>
                                    <div class="session-details">
                                        <div class="session-device"><?= $os ?> - <?= $browser ?></div>
                                        <div class="session-meta">
                                            <span class="session-ip"><?= htmlspecialchars($session["ip_address"]) ?></span>
                                            <span class="session-time">最后活动: <?= date("Y-m-d H:i", strtotime($session["last_activity_at"])) ?></span>
                                        </div>
                                        <?php if ($isCurrentSession): ?>
                                            <div class="session-current-badge">当前会话</div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!$isCurrentSession): ?>
                                        <div class="session-actions">
                                            <form action="" method="post">
                                                <input type="hidden" name="session_id" value="<?= $session["session_id"] ?>">
                                                <button type="submit" name="end_session" class="btn btn-sm btn-danger" onclick="return confirm(\"确定要结束此会话吗？\")">结束会话</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- 最近活动 -->
        <div class="content-card">
            <div class="card-header">
                <h2>最近活动</h2>
            </div>
            <div class="card-body">
                <?php if (empty($activityLogs)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <p>没有找到活动记录</p>
                    </div>
                <?php else: ?>
                    <div class="activity-list">
                        <?php foreach ($activityLogs as $log): ?>
                            <?php
                            $actionIcon = "fas fa-info-circle";
                            $actionText = "未知操作";
                            
                            switch ($log["action"]) {
                                case "login":
                                    $actionIcon = "fas fa-sign-in-alt";
                                    $actionText = "账号登录";
                                    break;
                                case "logout":
                                    $actionIcon = "fas fa-sign-out-alt";
                                    $actionText = "账号登出";
                                    break;
                                case "password_changed":
                                    $actionIcon = "fas fa-key";
                                    $actionText = "修改密码";
                                    break;
                                case "2fa_enabled":
                                    $actionIcon = "fas fa-lock";
                                    $actionText = "启用双因素认证";
                                    break;
                                case "2fa_disabled":
                                    $actionIcon = "fas fa-unlock";
                                    $actionText = "禁用双因素认证";
                                    break;
                                case "profile_updated":
                                    $actionIcon = "fas fa-user-edit";
                                    $actionText = "更新个人资料";
                                    break;
                                case "api_key_created":
                                    $actionIcon = "fas fa-plus-circle";
                                    $actionText = "创建API密钥";
                                    break;
                                case "api_key_deleted":
                                    $actionIcon = "fas fa-minus-circle";
                                    $actionText = "删除API密钥";
                                    break;
                                case "sessions_terminated":
                                    $actionIcon = "fas fa-power-off";
                                    $actionText = "结束所有其他会话";
                                    break;
                                case "session_terminated":
                                    $actionIcon = "fas fa-times-circle";
                                    $actionText = "结束单个会话";
                                    break;
                            }
                            ?>
                            
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="<?= $actionIcon ?>"></i>
                                </div>
                                <div class="activity-details">
                                    <div class="activity-action"><?= $actionText ?></div>
                                    <div class="activity-meta">
                                        <span class="activity-ip"><?= htmlspecialchars($log["ip_address"]) ?></span>
                                        <span class="activity-time"><?= date("Y-m-d H:i", strtotime($log["created_at"])) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<style>
    /* 安全设置页面特定样式 */
    .password-strength {
        margin-top: var(--spacing-xs);
    }
    
    .strength-meter {
        height: 5px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 5px;
    }
    
    .strength-meter-fill {
        height: 100%;
        width: 0;
        border-radius: 3px;
        transition: width 0.3s ease, background-color 0.3s ease;
    }
    
    .strength-text {
        font-size: 0.85rem;
        color: var(--text-color-light);
    }
    
    .password-requirements {
        margin-top: var(--spacing-md);
        margin-bottom: var(--spacing-md);
        background: var(--glass-background);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
    }
    
    .password-requirements h4 {
        font-size: 0.9rem;
        margin-bottom: var(--spacing-sm);
        color: var(--text-color);
    }
    
    .password-requirements ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .password-requirements li {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
        font-size: 0.85rem;
        color: var(--text-color-light);
    }
    
    .password-requirements li i {
        font-size: 0.7rem;
        margin-right: var(--spacing-sm);
    }
    
    .password-requirements li.valid {
        color: var(--success-color);
    }
    
    .password-requirements li.valid i {
        color: var(--success-color);
    }
    
    .two-factor-container {
        margin-top: var(--spacing-md);
    }
    
    .two-factor-info {
        display: flex;
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
    }
    
    .two-factor-icon {
        font-size: 2.5rem;
        color: var(--accent-color);
        flex-shrink: 0;
    }
    
    .two-factor-content h3 {
        margin-bottom: var(--spacing-sm);
        color: var(--secondary-color);
    }
    
    .two-factor-content p {
        margin-bottom: var(--spacing-md);
        color: var(--text-color-light);
    }
    
    .toggle-form {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
    }
    
    .toggle-switch {
        display: flex;
        align-items: center;
    }
    
    .toggle-input {
        display: none;
    }
    
    .toggle-label {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 13px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    
    .toggle-label::after {
        content: "";
        position: absolute;
        top: 3px;
        left: 3px;
        width: 20px;
        height: 20px;
        background-color: #fff;
        border-radius: 50%;
        transition: transform 0.3s ease;
    }
    
    .toggle-input:checked + .toggle-label {
        background-color: var(--accent-color);
    }
    
    .toggle-input:checked + .toggle-label::after {
        transform: translateX(24px);
    }
    
    .toggle-text {
        margin-left: var(--spacing-sm);
        font-size: 0.9rem;
    }
    
    .two-factor-setup {
        background: var(--glass-background);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
    }
    
    .two-factor-setup h4 {
        font-size: 1rem;
        margin-bottom: var(--spacing-md);
        color: var(--text-color);
    }
    
    .verification-method {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
    }
    
    .method-icon {
        font-size: 1.5rem;
        color: var(--accent-color);
    }
    
    .method-details {
        flex: 1;
    }
    
    .method-name {
        font-weight: 500;
        margin-bottom: 3px;
    }
    
    .method-value {
        font-size: 0.85rem;
        color: var(--text-color-light);
    }
    
    .sessions-container {
        margin-top: var(--spacing-md);
    }
    
    .sessions-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-md);
    }
    
    .sessions-header p {
        color: var(--text-color-light);
        margin: 0;
    }
    
    .sessions-list {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-md);
    }
    
    .session-item {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        background: var(--glass-background);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
    }
    
    .session-item.current-session {
        border-color: var(--accent-color);
    }
    
    .session-icon {
        font-size: 1.5rem;
        color: var(--accent-color);
    }
    
    .session-details {
        flex: 1;
    }
    
    .session-device {
        font-weight: 500;
        margin-bottom: 3px;
    }
    
    .session-meta {
        display: flex;
        gap: var(--spacing-md);
        font-size: 0.85rem;
        color: var(--text-color-light);
    }
    
    .session-current-badge {
        display: inline-block;
        background-color: rgba(10, 132, 255, 0.15);
        color: var(--accent-color);
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 500;
        margin-top: 5px;
    }
    
    .activity-list {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-sm);
    }
    
    .activity-item {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        padding: var(--spacing-sm) 0;
        border-bottom: 1px solid var(--glass-border);
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-icon {
        font-size: 1.2rem;
        color: var(--accent-color);
    }
    
    .activity-details {
        flex: 1;
    }
    
    .activity-action {
        font-weight: 500;
        margin-bottom: 3px;
    }
    
    .activity-meta {
        display: flex;
        gap: var(--spacing-md);
        font-size: 0.85rem;
        color: var(--text-color-light);
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
    
    @media (max-width: 768px) {
        .two-factor-info {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .sessions-header {
            flex-direction: column;
            gap: var(--spacing-md);
            align-items: flex-start;
        }
        
        .session-item {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .session-actions {
            width: 100%;
            margin-top: var(--spacing-sm);
            display: flex;
            justify-content: flex-end;
        }
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 密码强度检查
        const passwordInput = document.getElementById("new_password");
        const confirmInput = document.getElementById("confirm_password");
        const strengthMeter = document.getElementById("passwordStrength");
        const strengthText = document.getElementById("passwordStrengthText");
        
        // 密码要求检查项
        const reqLength = document.getElementById("req-length");
        const reqUppercase = document.getElementById("req-uppercase");
        const reqLowercase = document.getElementById("req-lowercase");
        const reqNumber = document.getElementById("req-number");
        const reqSpecial = document.getElementById("req-special");
        
        if (passwordInput) {
            passwordInput.addEventListener("input", function() {
                const password = this.value;
                let strength = 0;
                let strengthClass = "";
                
                // 检查长度
                if (password.length >= 8) {
                    strength += 20;
                    reqLength.classList.add("valid");
                    reqLength.querySelector("i").className = "fas fa-check-circle";
                } else {
                    reqLength.classList.remove("valid");
                    reqLength.querySelector("i").className = "fas fa-circle";
                }
                
                // 检查大写字母
                if (/[A-Z]/.test(password)) {
                    strength += 20;
                    reqUppercase.classList.add("valid");
                    reqUppercase.querySelector("i").className = "fas fa-check-circle";
                } else {
                    reqUppercase.classList.remove("valid");
                    reqUppercase.querySelector("i").className = "fas fa-circle";
                }
                
                // 检查小写字母
                if (/[a-z]/.test(password)) {
                    strength += 20;
                    reqLowercase.classList.add("valid");
                    reqLowercase.querySelector("i").className = "fas fa-check-circle";
                } else {
                    reqLowercase.classList.remove("valid");
                    reqLowercase.querySelector("i").className = "fas fa-circle";
                }
                
                // 检查数字
                if (/[0-9]/.test(password)) {
                    strength += 20;
                    reqNumber.classList.add("valid");
                    reqNumber.querySelector("i").className = "fas fa-check-circle";
                } else {
                    reqNumber.classList.remove("valid");
                    reqNumber.querySelector("i").className = "fas fa-circle";
                }
                
                // 检查特殊字符
                if (/[^A-Za-z0-9]/.test(password)) {
                    strength += 20;
                    reqSpecial.classList.add("valid");
                    reqSpecial.querySelector("i").className = "fas fa-check-circle";
                } else {
                    reqSpecial.classList.remove("valid");
                    reqSpecial.querySelector("i").className = "fas fa-circle";
                }
                
                // 设置强度文本和颜色
                if (strength === 0) {
                    strengthText.textContent = "密码强度";
                    strengthClass = "";
                } else if (strength <= 40) {
                    strengthText.textContent = "弱";
                    strengthClass = "weak";
                } else if (strength <= 80) {
                    strengthText.textContent = "中";
                    strengthClass = "medium";
                } else {
                    strengthText.textContent = "强";
                    strengthClass = "strong";
                }
                
                // 设置强度条样式
                strengthMeter.style.width = strength + "%";
                strengthMeter.className = "strength-meter-fill " + strengthClass;
                
                // 根据强度设置颜色
                if (strength <= 40) {
                    strengthMeter.style.backgroundColor = "#ff453a"; // 红色
                } else if (strength <= 80) {
                    strengthMeter.style.backgroundColor = "#ff9f0a"; // 橙色
                } else {
                    strengthMeter.style.backgroundColor = "#30d158"; // 绿色
                }
            });
        }
        
        // 确认密码匹配检查
        if (confirmInput && passwordInput) {
            confirmInput.addEventListener("input", function() {
                if (this.value === passwordInput.value) {
                    this.setCustomValidity("");
                } else {
                    this.setCustomValidity("密码不匹配");
                }
            });
            
            passwordInput.addEventListener("input", function() {
                if (confirmInput.value) {
                    if (confirmInput.value === this.value) {
                        confirmInput.setCustomValidity("");
                    } else {
                        confirmInput.setCustomValidity("密码不匹配");
                    }
                }
            });
        }
        
        // 双因素认证开关
        const toggle2FA = document.getElementById("enable_2fa");
        const toggleText = document.querySelector(".toggle-text");
        
        if (toggle2FA && toggleText) {
            toggle2FA.addEventListener("change", function() {
                toggleText.textContent = this.checked ? "已启用" : "已禁用";
            });
        }
        
        // 更换手机号按钮
        const changePhoneBtn = document.getElementById("changePhoneBtn");
        
        if (changePhoneBtn) {
            changePhoneBtn.addEventListener("click", function() {
                alert("此功能尚未实现。在实际应用中，这里会打开一个更换手机号的表单。");
            });
        }
    });
</script>

<?php
// 渲染页面页脚
renderPageFooter();
?>
