<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - 内部服务器错误 - IT运维中心</title>
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
        .error-details {
            margin-top: 20px;
            text-align: left;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #e9ecef;
            max-height: 300px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 14px;
            white-space: pre-wrap;
            word-break: break-all;
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
            <h1 class="error-code">500</h1>
            <h2 class="error-text">内部服务器错误</h2>
            <p class="error-description">很抱歉，服务器在处理您的请求时遇到了错误。</p>
            
            <a href="/admin/dashboard" class="btn btn-primary btn-lg">返回仪表盘</a>
            
            <?php 
            // 确保变量已定义
            $isDebug = $isDebug ?? false;
            ?>
            <?php if (isset($isDebug) && $isDebug === true && isset($exception)): ?>
                <div class="error-details mt-4">
                    <h5>错误详情：</h5>
                    <p><strong>错误消息：</strong> <?php echo htmlspecialchars($exception->getMessage()); ?></p>
                    <p><strong>文件：</strong> <?php echo htmlspecialchars($exception->getFile()); ?></p>
                    <p><strong>行号：</strong> <?php echo $exception->getLine(); ?></p>
                    <p><strong>跟踪信息：</strong></p>
                    <pre><?php echo htmlspecialchars($exception->getTraceAsString()); ?></pre>
                </div>
            <?php endif; ?>
            
            <div class="suggested-links">
                <h5>您可以尝试：</h5>
                <ul>
                    <li>刷新页面，有时这能解决临时性问题</li>
                    <li>稍后再试，问题可能是暂时的</li>
                    <li><a href="/admin/dashboard">返回仪表盘</a>并尝试其他操作</li>
                    <li>如果您是系统管理员，请检查服务器日志以获取更多信息</li>
                </ul>
                
                <h5>常见故障解决方法：</h5>
                <ul>
                    <li><a href="/admin/tools/system-info">检查系统信息</a>，确认服务器运行状态</li>
                    <li><a href="/admin/logs">查看错误日志</a>，了解详细错误信息</li>
                    <li><a href="/admin/tools/database-info">检查数据库状态</a>，确认数据库连接正常</li>
                    <li><a href="/admin/tools/cache-optimizer">清理系统缓存</a>，解决缓存相关问题</li>
                    <li>如果问题仍然存在，请联系技术支持</li>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 