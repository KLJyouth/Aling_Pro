<?php
/**
 * AlingAi Pro 管理后台首页
 * @version 1.0.0
 * @author AlingAi Team
 */

// 启动会话
session_start();

// 检查是否已登录
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // 未登录，重定向到登录页面
    header('Location: login.php');
    exit;
}

// 加载菜单
require_once 'admin_menu.php';

// 获取当前页面ID
$currentPage = 'home';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlingAi Pro - 管理后台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #3498db;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .dashboard-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin: 20px;
            padding: 30px;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .menu-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 20px;
            text-align: center;
            color: var(--dark-color);
            text-decoration: none;
            display: block;
            position: relative;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            color: var(--primary-color);
        }

        .menu-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .menu-title {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .menu-description {
            color: #666;
            margin-top: 10px;
            font-size: 0.9rem;
        }

        .security-menu {
            background: linear-gradient(135deg, #ff9966, #ff5e62);
            color: white;
        }

        .security-menu:hover {
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="dashboard-container">
            <div class="header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1><i class="bi bi-speedometer2"></i> AlingAi Pro 管理后台</h1>
                        <p class="mb-0">IT技术运维中心</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <span class="me-3 text-light">
                            <i class="bi bi-person-circle"></i> 
                            欢迎, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>
                        </span>
                        <a href="logout.php" class="btn btn-outline-light"><i class="bi bi-box-arrow-right"></i> 退出登录</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <?php echo renderAdminMenu($currentPage); ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 