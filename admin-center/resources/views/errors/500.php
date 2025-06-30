<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - 系统错误 | IT运维中心</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Microsoft YaHei', 'PingFang SC', sans-serif;
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            max-width: 500px;
            width: 100%;
            text-align: center;
            padding: 2rem;
        }
        .error-icon {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 1.5rem;
        }
        .error-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .error-message {
            color: #6c757d;
            margin-bottom: 2rem;
        }
        .btn-return {
            padding: 0.75rem 2rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <h1 class="error-title">500 - 系统错误</h1>
        <p class="error-message">
            很抱歉，系统遇到了意外错误。我们的技术团队已经收到通知并正在处理此问题。
        </p>
        <p class="error-message">
            请稍后再试或联系系统管理员获取帮助。
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="/admin" class="btn btn-primary btn-return">
                <i class="bi bi-house"></i> 返回首页
            </a>
            <button onclick="window.location.reload()" class="btn btn-outline-secondary btn-return">
                <i class="bi bi-arrow-clockwise"></i> 刷新页面
            </button>
        </div>
        <div class="mt-4 text-muted small">
            <p>错误ID: <?= uniqid('err_') ?></p>
            <p>时间: <?= date('Y-m-d H:i:s') ?></p>
        </div>
    </div>
</body>
</html> 