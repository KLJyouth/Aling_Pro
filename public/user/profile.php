<?php
/**
 * AlingAi Pro - 用户个人资料页面
 * 
 * 允许用户查看和编辑个人资料信息
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
if (!isset($_SESSION[\"user_id\"])) {
    // 未登录，重定向到登录页面
    header(\"Location: /login\");
    exit;
}

// 获取用户信息
$userId = $_SESSION[\"user_id\"];
$userName = $_SESSION[\"user_name\"] ?? \"用户\";
$userEmail = $_SESSION[\"user_email\"] ?? \"\";

// 设置页面信息
$pageTitle = "个人资料 - AlingAi Pro";
$pageDescription = "管理您的个人资料信息";
$additionalCSS = [
    \"/css/user-dashboard.css\",
    \"/css/user-profile.css\"
];
$additionalJS = [
    [\"src\" => \"/js/user-profile.js\", \"defer\" => true]
];

// 包含页面模板
require_once __DIR__ . \"/../templates/page.php\";

// 连接数据库
try {
    $db = new PDO(\"mysql:host=localhost;dbname=alingai_pro;charset=utf8mb4\", \"root\", \"\");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(\"数据库连接失败: \" . $e->getMessage());
}

// 获取用户详细信息
$stmt = $db->prepare(\"SELECT * FROM users WHERE id = ?\");
$stmt->execute([$userId]);
$userInfo = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// 处理表单提交
$message = \"\";
$messageType = \"\";

if ($_SERVER[\"REQUEST_METHOD\"] === \"POST\") {
    if (isset($_POST[\"update_profile\"])) {
        // 获取表单数据
        $displayName = filter_input(INPUT_POST, \"display_name\", FILTER_SANITIZE_STRING);
        $bio = filter_input(INPUT_POST, \"bio\", FILTER_SANITIZE_STRING);
        $company = filter_input(INPUT_POST, \"company\", FILTER_SANITIZE_STRING);
        $position = filter_input(INPUT_POST, \"position\", FILTER_SANITIZE_STRING);
        $website = filter_input(INPUT_POST, \"website\", FILTER_SANITIZE_URL);
        $location = filter_input(INPUT_POST, \"location\", FILTER_SANITIZE_STRING);
        
        // 更新数据库
        try {
            $stmt = $db->prepare(\"UPDATE users SET display_name = ?, bio = ?, company = ?, position = ?, website = ?, location = ?, updated_at = NOW() WHERE id = ?\");
            $result = $stmt->execute([$displayName, $bio, $company, $position, $website, $location, $userId]);
            
            if ($result) {
                $message = \"个人资料更新成功！\";
                $messageType = \"success\";
                
                // 更新会话中的用户名
                $_SESSION[\"user_name\"] = $displayName;
                
                // 重新获取用户信息
                $stmt = $db->prepare(\"SELECT * FROM users WHERE id = ?\");
                $stmt->execute([$userId]);
                $userInfo = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            } else {
                $message = \"更新失败，请稍后再试。\";
                $messageType = \"error\";
            }
        } catch (PDOException $e) {
            $message = \"数据库错误: \" . $e->getMessage();
            $messageType = \"error\";
        }
    }
    
    // 处理头像上传
    if (isset($_FILES[\"avatar\"]) && $_FILES[\"avatar\"][\"error\"] === UPLOAD_ERR_OK) {
        $allowedTypes = [\"image/jpeg\", \"image/png\", \"image/gif\"];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($_FILES[\"avatar\"][\"type\"], $allowedTypes)) {
            $message = \"只允许上传JPG、PNG或GIF图片。\";
            $messageType = \"error\";
        } elseif ($_FILES[\"avatar\"][\"size\"] > $maxSize) {
            $message = \"图片大小不能超过2MB。\";
            $messageType = \"error\";
        } else {
            $uploadDir = __DIR__ . \"/../uploads/avatars/\";
            
            // 确保上传目录存在
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = $userId . \"_\" . time() . \"_\" . basename($_FILES[\"avatar\"][\"name\"]);
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES[\"avatar\"][\"tmp_name\"], $targetFile)) {
                // 更新数据库中的头像路径
                $avatarPath = \"/uploads/avatars/\" . $fileName;
                
                $stmt = $db->prepare(\"UPDATE users SET avatar = ? WHERE id = ?\");
                $result = $stmt->execute([$avatarPath, $userId]);
                
                if ($result) {
                    $message = \"头像上传成功！\";
                    $messageType = \"success\";
                    
                    // 更新用户信息
                    $userInfo[\"avatar\"] = $avatarPath;
                } else {
                    $message = \"头像路径更新失败。\";
                    $messageType = \"error\";
                }
            } else {
                $message = \"文件上传失败，请稍后再试。\";
                $messageType = \"error\";
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
            <h3><?= htmlspecialchars($userInfo[\"display_name\"] ?? $userName) ?></h3>
            <p class="user-email"><?= htmlspecialchars($userEmail) ?></p>
        </div>
        
        <nav class="dashboard-nav">
            <ul>
                <li><a href="/dashboard"><i class="fas fa-tachometer-alt"></i> 控制台</a></li>
                <li><a href="/user/profile" class="active"><i class="fas fa-user"></i> 个人资料</a></li>
                <li><a href="/user/account-settings"><i class="fas fa-cog"></i> 账号设置</a></li>
                <li><a href="/user/billing"><i class="fas fa-credit-card"></i> 账单与订阅</a></li>
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
            <h1>个人资料</h1>
            <p>管理您的个人信息和公开资料</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType === \"success\" ? \"success\" : \"danger\" ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <div class="content-card">
            <div class="card-header">
                <h2>个人信息</h2>
            </div>
            <div class="card-body">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-section">
                        <h3>头像</h3>
                        <div class="avatar-upload">
                            <div class="current-avatar">
                                <img src="<?= htmlspecialchars($userInfo[\"avatar\"] ?? \"/assets/images/default-avatar.png\") ?>" alt="当前头像" id="avatarPreview">
                            </div>
                            <div class="avatar-controls">
                                <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/gif" class="hidden-input">
                                <label for="avatarInput" class="btn btn-outline">选择新头像</label>
                                <p class="hint">支持JPG、PNG和GIF格式，最大2MB</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>基本信息</h3>
                        <div class="form-group">
                            <label for="display_name">显示名称</label>
                            <input type="text" id="display_name" name="display_name" value="<?= htmlspecialchars($userInfo[\"display_name\"] ?? $userName) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">电子邮箱</label>
                            <input type="email" id="email" value="<?= htmlspecialchars($userEmail) ?>" disabled>
                            <p class="hint">如需更改邮箱，请前往<a href="/user/account-settings">账号设置</a></p>
                        </div>
                        
                        <div class="form-group">
                            <label for="bio">个人简介</label>
                            <textarea id="bio" name="bio" rows="4"><?= htmlspecialchars($userInfo[\"bio\"] ?? \"\") ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>职业信息</h3>
                        <div class="form-group">
                            <label for="company">公司/组织</label>
                            <input type="text" id="company" name="company" value="<?= htmlspecialchars($userInfo[\"company\"] ?? \"\") ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="position">职位</label>
                            <input type="text" id="position" name="position" value="<?= htmlspecialchars($userInfo[\"position\"] ?? \"\") ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="website">网站</label>
                            <input type="url" id="website" name="website" value="<?= htmlspecialchars($userInfo[\"website\"] ?? \"\") ?>" placeholder="https://example.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="location">所在地</label>
                            <input type="text" id="location" name="location" value="<?= htmlspecialchars($userInfo[\"location\"] ?? \"\") ?>">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="update_profile" class="btn btn-primary">保存更改</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<style>
    /* 个人资料页面特定样式 */
    .avatar-upload {
        display: flex;
        align-items: center;
        margin-bottom: var(--spacing-lg);
    }
    
    .current-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        overflow: hidden;
        margin-right: var(--spacing-lg);
        border: 2px solid var(--glass-border);
    }
    
    .current-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .avatar-controls {
        flex: 1;
    }
    
    .hidden-input {
        display: none;
    }
    
    .hint {
        font-size: 0.9rem;
        color: var(--text-color-light);
        margin-top: var(--spacing-xs);
    }
    
    .form-section {
        margin-bottom: var(--spacing-xl);
        padding-bottom: var(--spacing-lg);
        border-bottom: 1px solid var(--glass-border);
    }
    
    .form-section:last-child {
        border-bottom: none;
    }
    
    .form-section h3 {
        margin-bottom: var(--spacing-md);
        color: var(--secondary-color);
    }
    
    .form-group {
        margin-bottom: var(--spacing-md);
    }
    
    .form-group label {
        display: block;
        margin-bottom: var(--spacing-xs);
        font-weight: 500;
    }
    
    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: var(--spacing-sm);
        background: var(--glass-background);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
        color: var(--text-color);
        transition: border-color var(--transition-fast);
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
        border-color: var(--accent-color);
        outline: none;
        box-shadow: 0 0 0 2px var(--accent-glow);
    }
    
    .form-group input:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: var(--spacing-lg);
    }
</style>

<script>
    // 头像预览功能
    document.addEventListener(\"DOMContentLoaded\", function() {
        const avatarInput = document.getElementById(\"avatarInput\");
        const avatarPreview = document.getElementById(\"avatarPreview\");
        
        if (avatarInput && avatarPreview) {
            avatarInput.addEventListener(\"change\", function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        avatarPreview.src = e.target.result;
                    };
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    });
</script>

<?php
// 渲染页面页脚
renderPageFooter();
?>
