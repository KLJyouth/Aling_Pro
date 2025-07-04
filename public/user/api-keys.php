<?php
/**
 * AlingAi Pro - 用户API密钥管理页面
 * 
 * 允许用户创建、查看、编辑和删除API密钥
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
$pageTitle = "API密钥管理 - AlingAi Pro";
$pageDescription = "创建和管理您的API密钥，用于集成AlingAi Pro的API服务";
$additionalCSS = [
    "/css/user-dashboard.css",
    "/css/api-keys.css"
];
$additionalJS = [
    ["src" => "/js/api-keys.js", "defer" => true]
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

// 获取用户的API密钥列表
$apiKeys = [];
try {
    $stmt = $db->prepare("SELECT * FROM api_keys WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $apiKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "获取API密钥失败: " . $e->getMessage();
}

// 处理表单提交
$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 创建新API密钥
    if (isset($_POST["create_key"])) {
        $keyName = filter_input(INPUT_POST, "key_name", FILTER_SANITIZE_STRING);
        $keyDescription = filter_input(INPUT_POST, "key_description", FILTER_SANITIZE_STRING);
        $expiryDays = filter_input(INPUT_POST, "expiry_days", FILTER_VALIDATE_INT);
        
        if (empty($keyName)) {
            $message = "密钥名称不能为空";
            $messageType = "error";
        } else {
            try {
                // 生成API密钥
                $apiKey = bin2hex(random_bytes(16)); // 32字符的随机密钥
                $prefix = substr($apiKey, 0, 8);
                $hashedKey = password_hash($apiKey, PASSWORD_DEFAULT);
                
                // 计算过期日期
                $expiryDate = null;
                if ($expiryDays > 0) {
                    $expiryDate = date("Y-m-d H:i:s", strtotime("+{$expiryDays} days"));
                }
                
                // 插入数据库
                $stmt = $db->prepare("INSERT INTO api_keys (user_id, name, description, key_prefix, hashed_key, expiry_date, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $result = $stmt->execute([$userId, $keyName, $keyDescription, $prefix, $hashedKey, $expiryDate]);
                
                if ($result) {
                    $message = "API密钥创建成功！请保存您的密钥，它只会显示一次: {$prefix}...{$apiKey}";
                    $messageType = "success";
                    
                    // 重新获取API密钥列表
                    $stmt = $db->prepare("SELECT * FROM api_keys WHERE user_id = ? ORDER BY created_at DESC");
                    $stmt->execute([$userId]);
                    $apiKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $message = "创建API密钥失败，请稍后再试";
                    $messageType = "error";
                }
            } catch (Exception $e) {
                $message = "创建API密钥时出错: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
    
    // 删除API密钥
    if (isset($_POST["delete_key"])) {
        $keyId = filter_input(INPUT_POST, "key_id", FILTER_VALIDATE_INT);
        
        if ($keyId) {
            try {
                // 确保密钥属于当前用户
                $stmt = $db->prepare("DELETE FROM api_keys WHERE id = ? AND user_id = ?");
                $result = $stmt->execute([$keyId, $userId]);
                
                if ($result && $stmt->rowCount() > 0) {
                    $message = "API密钥已成功删除";
                    $messageType = "success";
                    
                    // 重新获取API密钥列表
                    $stmt = $db->prepare("SELECT * FROM api_keys WHERE user_id = ? ORDER BY created_at DESC");
                    $stmt->execute([$userId]);
                    $apiKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $message = "删除API密钥失败，密钥可能不存在或不属于您";
                    $messageType = "error";
                }
            } catch (PDOException $e) {
                $message = "删除API密钥时出错: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
    
    // 更新API密钥
    if (isset($_POST["update_key"])) {
        $keyId = filter_input(INPUT_POST, "key_id", FILTER_VALIDATE_INT);
        $keyName = filter_input(INPUT_POST, "key_name", FILTER_SANITIZE_STRING);
        $keyDescription = filter_input(INPUT_POST, "key_description", FILTER_SANITIZE_STRING);
        
        if ($keyId && !empty($keyName)) {
            try {
                // 确保密钥属于当前用户
                $stmt = $db->prepare("UPDATE api_keys SET name = ?, description = ? WHERE id = ? AND user_id = ?");
                $result = $stmt->execute([$keyName, $keyDescription, $keyId, $userId]);
                
                if ($result && $stmt->rowCount() > 0) {
                    $message = "API密钥信息已成功更新";
                    $messageType = "success";
                    
                    // 重新获取API密钥列表
                    $stmt = $db->prepare("SELECT * FROM api_keys WHERE user_id = ? ORDER BY created_at DESC");
                    $stmt->execute([$userId]);
                    $apiKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $message = "更新API密钥失败，密钥可能不存在或不属于您";
                    $messageType = "error";
                }
            } catch (PDOException $e) {
                $message = "更新API密钥时出错: " . $e->getMessage();
                $messageType = "error";
            }
        } else {
            $message = "密钥ID或名称无效";
            $messageType = "error";
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
                <li><a href="/user/api-keys" class="active"><i class="fas fa-key"></i> API密钥</a></li>
                <li><a href="/user/usage"><i class="fas fa-chart-line"></i> 使用情况</a></li>
                <li><a href="/user/security"><i class="fas fa-shield-alt"></i> 安全</a></li>
                <li><a href="/logout"><i class="fas fa-sign-out-alt"></i> 退出登录</a></li>
            </ul>
        </nav>
    </div>

    <!-- 主要内容 -->
    <div class="dashboard-main">
        <div class="dashboard-header">
            <h1>API密钥管理</h1>
            <p>创建和管理您的API密钥，用于集成AlingAi Pro的API服务</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType === \"success\" ? \"success\" : \"danger\" ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <!-- 创建新密钥 -->
        <div class="content-card">
            <div class="card-header">
                <h2>创建新API密钥</h2>
            </div>
            <div class="card-body">
                <form action="" method="post">
                    <div class="form-group">
                        <label for="key_name">密钥名称 <span class="required">*</span></label>
                        <input type="text" id="key_name" name="key_name" required placeholder="例如：生产环境、测试环境">
                    </div>
                    
                    <div class="form-group">
                        <label for="key_description">描述（可选）</label>
                        <textarea id="key_description" name="key_description" rows="2" placeholder="描述此密钥的用途"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="expiry_days">过期时间</label>
                        <select id="expiry_days" name="expiry_days">
                            <option value="0">永不过期</option>
                            <option value="30">30天</option>
                            <option value="60">60天</option>
                            <option value="90">90天</option>
                            <option value="180">180天</option>
                            <option value="365">1年</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="create_key" class="btn btn-primary">创建API密钥</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- API密钥列表 -->
        <div class="content-card">
            <div class="card-header">
                <h2>您的API密钥</h2>
            </div>
            <div class="card-body">
                <?php if (empty($apiKeys)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <p>您还没有创建任何API密钥</p>
                        <p class="hint">创建API密钥后，您可以使用它来访问AlingAi Pro的API服务</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="api-keys-table">
                            <thead>
                                <tr>
                                    <th>名称</th>
                                    <th>密钥前缀</th>
                                    <th>创建时间</th>
                                    <th>过期时间</th>
                                    <th>状态</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($apiKeys as $key): ?>
                                    <tr>
                                        <td>
                                            <div class="key-name"><?= htmlspecialchars($key[\"name\"]) ?></div>
                                            <?php if (!empty($key[\"description\"])): ?>
                                                <div class="key-description"><?= htmlspecialchars($key[\"description\"]) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><code><?= htmlspecialchars($key[\"key_prefix\"]) ?></code></td>
                                        <td><?= date(\"Y-m-d H:i\", strtotime($key[\"created_at\"])) ?></td>
                                        <td>
                                            <?php if ($key[\"expiry_date\"]): ?>
                                                <?= date(\"Y-m-d\", strtotime($key[\"expiry_date\"])) ?>
                                            <?php else: ?>
                                                永不过期
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $isExpired = $key[\"expiry_date\"] && strtotime($key[\"expiry_date\"]) < time();
                                            $status = $key[\"is_active\"] ? ($isExpired ? \"已过期\" : \"有效\") : \"已禁用\";
                                            $statusClass = $key[\"is_active\"] ? ($isExpired ? \"expired\" : \"active\") : \"disabled\";
                                            ?>
                                            <span class="key-status <?= $statusClass ?>"><?= $status ?></span>
                                        </td>
                                        <td class="actions">
                                            <button type="button" class="btn btn-sm btn-outline edit-key" data-key-id="<?= $key[\"id\"] ?>" data-key-name="<?= htmlspecialchars($key[\"name\"]) ?>" data-key-description="<?= htmlspecialchars($key[\"description\"]) ?>">
                                                <i class="fas fa-edit"></i> 编辑
                                            </button>
                                            <form action="" method="post" class="d-inline delete-key-form">
                                                <input type="hidden" name="key_id" value="<?= $key[\"id\"] ?>">
                                                <button type="submit" name="delete_key" class="btn btn-sm btn-danger" onclick="return confirm(\"确定要删除此API密钥吗？此操作不可撤销。\")">
                                                    <i class="fas fa-trash-alt"></i> 删除
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- API使用指南 -->
        <div class="content-card">
            <div class="card-header">
                <h2>API使用指南</h2>
            </div>
            <div class="card-body">
                <div class="api-guide">
                    <h3>身份验证</h3>
                    <p>在所有API请求中，您需要在请求头中包含您的API密钥：</p>
                    <pre><code>Authorization: Bearer YOUR_API_KEY</code></pre>
                    
                    <h3>示例请求</h3>
                    <p>使用cURL发送请求：</p>
                    <pre><code>curl -X POST \\
  https://api.alingai.pro/v1/chat/completions \\
  -H \"Content-Type: application/json\" \\
  -H \"Authorization: Bearer YOUR_API_KEY\" \\
  -d \"{
    \\\"model\\\": \\\"alingai-pro\\\",
    \\\"messages\\\": [
      {\\\"role\\\": \\\"user\\\", \\\"content\\\": \\\"你好，请介绍一下你自己。\\\"}
    ]
  }\"</code></pre>
                    
                    <h3>速率限制</h3>
                    <p>根据您的订阅计划，API请求可能受到速率限制。请查阅<a href="/docs/api-rate-limits">API文档</a>了解详情。</p>
                    
                    <div class="cta-container">
                        <a href="/docs/api" class="btn btn-primary">查看完整API文档</a>
                        <a href="/docs/api-examples" class="btn btn-outline">查看更多示例</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 编辑密钥模态框 -->
<div class="modal" id="editKeyModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>编辑API密钥</h3>
                <button type="button" class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form action="" method="post" id="editKeyForm">
                    <input type="hidden" name="key_id" id="edit_key_id">
                    
                    <div class="form-group">
                        <label for="edit_key_name">密钥名称 <span class="required">*</span></label>
                        <input type="text" id="edit_key_name" name="key_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_key_description">描述（可选）</label>
                        <textarea id="edit_key_description" name="key_description" rows="2"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline cancel-edit">取消</button>
                        <button type="submit" name="update_key" class="btn btn-primary">保存更改</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<style>
    /* API密钥页面特定样式 */
    .api-keys-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .api-keys-table th,
    .api-keys-table td {
        padding: var(--spacing-sm) var(--spacing-md);
        text-align: left;
        border-bottom: 1px solid var(--glass-border);
    }
    
    .api-keys-table th {
        font-weight: 600;
        color: var(--secondary-color);
    }
    
    .key-name {
        font-weight: 500;
    }
    
    .key-description {
        font-size: 0.85rem;
        color: var(--text-color-light);
        margin-top: 3px;
    }
    
    .key-status {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .key-status.active {
        background-color: rgba(48, 209, 88, 0.15);
        color: #30d158;
    }
    
    .key-status.expired {
        background-color: rgba(255, 159, 10, 0.15);
        color: #ff9f0a;
    }
    
    .key-status.disabled {
        background-color: rgba(142, 142, 147, 0.15);
        color: #8e8e93;
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
    
    .empty-state p {
        margin-bottom: var(--spacing-xs);
    }
    
    .empty-state .hint {
        color: var(--text-color-light);
        font-size: 0.9rem;
    }
    
    .api-guide {
        margin-top: var(--spacing-md);
    }
    
    .api-guide h3 {
        margin-top: var(--spacing-lg);
        margin-bottom: var(--spacing-sm);
        color: var(--secondary-color);
    }
    
    .api-guide pre {
        background: var(--surface-color);
        padding: var(--spacing-md);
        border-radius: var(--border-radius-md);
        overflow-x: auto;
        margin: var(--spacing-md) 0;
    }
    
    .api-guide code {
        font-family: monospace;
        color: var(--accent-color);
    }
    
    .cta-container {
        margin-top: var(--spacing-lg);
        display: flex;
        gap: var(--spacing-md);
    }
    
    .required {
        color: var(--error-color);
    }
    
    /* 模态框样式 */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        overflow-y: auto;
        padding: var(--spacing-lg);
    }
    
    .modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-dialog {
        width: 100%;
        max-width: 500px;
        margin: auto;
    }
    
    .modal-content {
        background: var(--glass-background);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-lg);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--spacing-md) var(--spacing-lg);
        border-bottom: 1px solid var(--glass-border);
    }
    
    .modal-header h3 {
        margin: 0;
        color: var(--secondary-color);
    }
    
    .close-modal {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--text-color-light);
        cursor: pointer;
        padding: 0;
    }
    
    .modal-body {
        padding: var(--spacing-lg);
    }
    
    .d-inline {
        display: inline-block;
    }
    
    .actions {
        display: flex;
        gap: var(--spacing-sm);
    }
    
    @media (max-width: 768px) {
        .actions {
            flex-direction: column;
        }
        
        .cta-container {
            flex-direction: column;
        }
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 编辑密钥模态框
        const modal = document.getElementById("editKeyModal");
        const editButtons = document.querySelectorAll(".edit-key");
        const closeModal = document.querySelector(".close-modal");
        const cancelEdit = document.querySelector(".cancel-edit");
        
        // 打开模态框
        editButtons.forEach(button => {
            button.addEventListener("click", function() {
                const keyId = this.getAttribute("data-key-id");
                const keyName = this.getAttribute("data-key-name");
                const keyDescription = this.getAttribute("data-key-description");
                
                document.getElementById("edit_key_id").value = keyId;
                document.getElementById("edit_key_name").value = keyName;
                document.getElementById("edit_key_description").value = keyDescription;
                
                modal.classList.add("show");
            });
        });
        
        // 关闭模态框
        function closeModalFunc() {
            modal.classList.remove("show");
        }
        
        if (closeModal) closeModal.addEventListener("click", closeModalFunc);
        if (cancelEdit) cancelEdit.addEventListener("click", closeModalFunc);
        
        // 点击模态框外部关闭
        window.addEventListener("click", function(event) {
            if (event.target === modal) {
                closeModalFunc();
            }
        });
    });
</script>

<?php
// 渲染页面页脚
renderPageFooter();
?>
