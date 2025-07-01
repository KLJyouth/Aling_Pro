<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'IT运维中心' ?></title>
    <!-- 基础样式 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- 自定义样式 -->
    <link rel="stylesheet" href="/admin-center/public/assets/css/admin.css">
    <style>
        body {
            font-family: 'Microsoft YaHei', 'PingFang SC', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .sidebar {
            background-color: #343a40;
            min-height: 100vh;
            color: #fff;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 0;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 0.75rem 1rem;
            margin-bottom: 0.25rem;
            border-radius: 0.25rem;
            transition: all 0.2s ease-in-out;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.15);
        }
        .sidebar .nav-link i {
            margin-right: 0.5rem;
            font-size: 1rem;
            width: 1.5rem;
            text-align: center;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
            border: none;
            border-radius: 0.5rem;
        }
        .card-header {
            font-weight: 600;
            background-color: rgba(0, 0, 0, 0.03);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem 1.25rem;
        }
        .main-content {
            margin-left: 240px;
            padding: 2rem;
        }
        .navbar {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1rem;
            background-color: #fff;
        }
        .navbar-brand {
            font-weight: 600;
        }
        .breadcrumb {
            margin-bottom: 1.5rem;
            padding: 0.75rem 1rem;
            background-color: #fff;
            border-radius: 0.25rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
        }
        .user-dropdown .dropdown-toggle::after {
            display: none;
        }
        .user-dropdown .dropdown-menu {
            min-width: 12rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 0.5rem;
        }
        .user-dropdown .dropdown-item {
            padding: 0.5rem 1rem;
        }
        .user-dropdown .dropdown-item i {
            margin-right: 0.5rem;
            width: 1rem;
            text-align: center;
        }
        .system-info-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        .system-info-item:last-child {
            border-bottom: none;
        }
        .logo {
            padding: 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .logo h5 {
            margin: 0;
            color: #fff;
        }
        .logo p {
            margin: 0;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.8rem;
        }
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1rem;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.6);
        }
        .alert-indicator {
            position: relative;
            display: inline-block;
        }
        .alert-indicator .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            font-size: 0.65rem;
        }
    </style>
    <?php if (isset($extraStyles)): ?>
        <?= $extraStyles ?>
    <?php endif; ?>
</head>
<body>
    <!-- 侧边栏 -->
    <?php include VIEWS_PATH . '/layouts/sidebar.php'; ?>

    <!-- 主内容 -->
    <div class="main-content">
        <!-- 导航栏 -->
        <nav class="navbar navbar-expand-lg navbar-light shadow-sm mb-4 rounded">
            <div class="container-fluid">
                <button class="btn btn-sm btn-link text-dark" id="sidebarToggle">
                    <i class="bi bi-list fs-5"></i>
                </button>
                
                <?php if (isset($breadcrumbs)): ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 ms-3">
                        <?php foreach ($breadcrumbs as $key => $value): ?>
                            <?php if ($key === array_key_last($breadcrumbs)): ?>
                                <li class="breadcrumb-item active" aria-current="page"><?= $value ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item"><a href="<?= $key ?>"><?= $value ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
                <?php endif; ?>
                
                <div class="d-flex">
                    <!-- 通知图标 -->
                    <div class="dropdown me-3">
                        <a href="#" class="btn btn-link text-dark position-relative" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="alert-indicator">
                                <i class="bi bi-bell fs-5"></i>
                                <span class="badge bg-danger rounded-pill">2</span>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                            <li><h6 class="dropdown-header">通知</h6></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-exclamation-triangle text-warning"></i> 系统更新可用</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-info-circle text-info"></i> 数据库备份完成</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center" href="#">查看所有通知</a></li>
                        </ul>
                    </div>
                    
                    <!-- 用户菜单 -->
                    <div class="dropdown user-dropdown">
                        <a href="#" class="d-flex align-items-center link-dark text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="me-2 d-none d-lg-inline text-gray-600"><?= $_SESSION['admin_username'] ?? '管理员' ?></span>
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['admin_username'] ?? 'Admin') ?>&background=random" alt="用户头像" width="32" height="32" class="rounded-circle">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><h6 class="dropdown-header">账户管理</h6></li>
                            <li><a class="dropdown-item" href="/admin/profile"><i class="bi bi-person"></i> 个人资料</a></li>
                            <li><a class="dropdown-item" href="/admin/settings"><i class="bi bi-gear"></i> 设置</a></li>
                            <li><a class="dropdown-item" href="/admin/activity"><i class="bi bi-activity"></i> 活动日志</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/admin/logout"><i class="bi bi-box-arrow-right"></i> 退出</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- 内容区域 -->
        <div class="content">
            <?php if (isset($pageHeader)): ?>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?= $pageHeader ?></h1>
                    <?php if (isset($pageActions)): ?>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <?= $pageActions ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?= $_SESSION['flash_message_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
                    <?= $_SESSION['flash_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php 
                    unset($_SESSION['flash_message']);
                    unset($_SESSION['flash_message_type']);
                ?>
            <?php endif; ?>
            
            <?php $content(); ?>
        </div>
        
        <!-- 页脚 -->
        <footer class="mt-5 pt-3 pb-3 text-muted text-center">
            <p>© <?= date('Y') ?> AlingAi Pro IT运维中心 - 版本 <?= \App\Core\Config::get('app.version') ?></p>
        </footer>
    </div>

    <!-- 基础脚本 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/admin-center/public/assets/js/admin.js"></script>
    <?php if (isset($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
</body>
</html> 