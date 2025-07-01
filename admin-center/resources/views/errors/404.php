<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - 页面未找到 - IT运维中心</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .error-container {
            max-width: 800px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #dc3545;
            margin: 0;
            line-height: 1;
            text-shadow: 3px 3px 0 rgba(0,0,0,0.1);
        }
        .error-text {
            font-size: 32px;
            font-weight: 500;
            color: #343a40;
            margin-bottom: 30px;
        }
        .error-description {
            color: #6c757d;
            font-size: 18px;
            margin-bottom: 30px;
        }
        .requested-path {
            font-family: monospace;
            background: #f8f9fa;
            padding: 10px 20px;
            border-radius: 5px;
            border: 1px solid #e9ecef;
            margin-bottom: 30px;
            word-break: break-all;
            font-size: 16px;
            text-align: left;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            padding: 10px 30px;
            font-size: 18px;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
        .suggested-links {
            margin-top: 30px;
            text-align: left;
            border-top: 1px solid #e9ecef;
            padding-top: 20px;
        }
        .suggested-links h5 {
            color: #343a40;
            margin-bottom: 15px;
        }
        .suggested-links ul {
            padding-left: 20px;
        }
        .suggested-links li {
            margin-bottom: 10px;
        }
        .bg-shape {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: linear-gradient(45deg, rgba(13, 110, 253, 0.05), rgba(220, 53, 69, 0.05));
            top: -250px;
            right: -250px;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <div class="bg-shape"></div>
            <h1 class="error-code">404</h1>
            <h2 class="error-text">页面未找到</h2>
            <p class="error-description">很抱歉，您请求的页面不存在或已被移动。</p>
            
            <?php if (isset($_SERVER['REQUEST_URI'])): ?>
                <div class="requested-path">
                    <strong>请求路径：</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>
                </div>
            <?php endif; ?>
            
            <a href="/admin/dashboard" class="btn btn-primary btn-lg">返回仪表盘</a>
            
            <div class="suggested-links">
                <h5>您可能想找：</h5>
                <ul>
                    <li><a href="/admin/dashboard">仪表盘</a> - 查看系统概况和关键指标</li>
                    <li><a href="/admin/tools">运维工具</a> - 访问系统维护和诊断工具</li>
                    <li><a href="/admin/monitoring">系统监控</a> - 查看服务器状态和资源使用情况</li>
                    <li><a href="/admin/reports">运维报告</a> - 生成和查看系统报告</li>
                    <li><a href="/admin/logs">日志管理</a> - 查看和分析系统日志</li>
                    <li><a href="/admin/settings">系统设置</a> - 配置应用程序和系统参数</li>
                </ul>
                
                <h5>常见问题：</h5>
                <ul>
                    <li>请检查URL拼写是否正确</li>
                    <li>如果您是通过书签访问，该页面可能已被移动或重命名</li>
                    <li>您可能没有访问此页面的权限，请联系系统管理员</li>
                    <li>如果问题仍然存在，请联系技术支持</li>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 