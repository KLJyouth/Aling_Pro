<?php
/**
 * AlingAi Pro 6.0 - API文档管理系统
 * 自动扫描并生成API文档，支持OpenAPI/Swagger格式
 */

declare(strict_types=1);

header("Content-Type: text/html; charset=utf-8");
session_start();

// 检查用户是否已登录
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_user'])) {
    // 用户未登录，重定向到登录页面
    header('Location: /admin/login.php');
    exit;
}

// 获取用户角色信息
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$username = $_SESSION['username'] ?? $_SESSION['admin_user'] ?? 'Admin';

// 加载API文档数据
$apiDocs = [
    [
        'id' => 'user-api',
        'title' => '用户API',
        'version' => 'v1.0',
        'description' => '用户管理相关API，包括获取用户列表、用户详情、创建用户等功能。',
        'endpoints' => 8,
        'updated' => '2023-12-10'
    ],
    [
        'id' => 'auth-api',
        'title' => '认证API',
        'version' => 'v1.0',
        'description' => '认证相关API，包括登录、注册、找回密码、令牌刷新等功能。',
        'endpoints' => 6,
        'updated' => '2023-12-15'
    ],
    [
        'id' => 'document-api',
        'title' => '文档API',
        'version' => 'v1.0',
        'description' => '文档管理相关API，包括创建、查询、更新和删除文档等功能。',
        'endpoints' => 10,
        'updated' => '2023-12-18'
    ],
    [
        'id' => 'security-api',
        'title' => '安全API',
        'version' => 'v1.0',
        'description' => '安全相关API，包括量子加密、威胁检测、风险评估等功能。',
        'endpoints' => 12,
        'updated' => '2023-12-20'
    ],
    [
        'id' => 'system-api',
        'title' => '系统API',
        'version' => 'v1.0',
        'description' => '系统管理相关API，包括配置、监控、基线管理等功能。',
        'endpoints' => 15,
        'updated' => '2023-12-25'
    ]
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlingAi Pro - API文档中心</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .api-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            margin: 20px;
            overflow: hidden;
            min-height: 90vh;
        }
        .sidebar {
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            min-height: 90vh;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            border-radius: 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        .logo-area {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .logo-area h3 {
            margin: 0;
            color: white;
            font-size: 24px;
        }
        .content-area {
            padding: 30px;
        }
        .header-area {
            padding: 20px 30px;
            background: white;
            border-bottom: 1px solid #eee;
        }
        .api-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
            border-left: 5px solid #3498db;
        }
        .api-card:hover {
            transform: translateY(-5px);
        }
        .nav-group-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.5);
            padding: 15px 20px 5px;
            margin-top: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .badge-api {
            background-color: #3498db;
            color: white;
            font-weight: normal;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .badge-endpoints {
            background-color: #2ecc71;
            color: white;
            font-weight: normal;
            padding: 5px 10px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="api-container">
            <div class="row g-0">
                <!-- 侧边栏 -->
                <div class="col-md-3 col-lg-2 sidebar">
                    <div class="logo-area">
                        <h3>AlingAi Pro</h3>
                        <p class="mb-0">API文档中心</p>
                    </div>
                    <ul class="nav flex-column">
                        <!-- 返回管理控制台 -->
                        <li class="nav-item">
                            <a class="nav-link" href="/admin">
                                <i class="bi bi-arrow-left me-2"></i> 返回管理控制台
                            </a>
                        </li>
                        
                        <!-- API分类 -->
                        <div class="nav-group-title">API分类</div>
                        <li class="nav-item">
                            <a class="nav-link active" href="/admin/api/documentation">
                                <i class="bi bi-house me-2"></i> API文档首页
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/api/documentation/user-api">
                                <i class="bi bi-people me-2"></i> 用户API
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/api/documentation/auth-api">
                                <i class="bi bi-shield-lock me-2"></i> 认证API
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/api/documentation/document-api">
                                <i class="bi bi-file-earmark-text me-2"></i> 文档API
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/api/documentation/security-api">
                                <i class="bi bi-shield me-2"></i> 安全API
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/api/documentation/system-api">
                                <i class="bi bi-gear me-2"></i> 系统API
                            </a>
                        </li>
                        
                        <!-- 工具 -->
                        <div class="nav-group-title">开发工具</div>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/api/documentation/swagger">
                                <i class="bi bi-code-slash me-2"></i> Swagger UI
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/api/documentation/postman">
                                <i class="bi bi-cloud-download me-2"></i> Postman Collection
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- 内容区域 -->
                <div class="col-md-9 col-lg-10">
                    <div class="header-area d-flex justify-content-between align-items-center">
                        <h4>API文档中心</h4>
                        <div>
                            <span class="me-3">用户：<?php echo htmlspecialchars($username); ?></span>
                            <a href="/admin/logout.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-box-arrow-right"></i> 退出
                            </a>
                        </div>
                    </div>
                    
                    <div class="content-area">
                        <div class="row">
                            <div class="col-12">
                                <h2 class="mb-4"><i class="bi bi-code-slash"></i> AlingAi Pro API文档</h2>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> 
                                    <strong>开发者提示：</strong> 
                                    所有API都需要认证，请在请求头中包含有效的API Key或Bearer Token。
                                </div>
                            </div>
                        </div>
                        
                        <!-- API列表 -->
                        <div class="row mt-4">
                            <?php foreach ($apiDocs as $api): ?>
                            <div class="col-md-6 mb-4">
                                <div class="api-card h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5><?php echo htmlspecialchars($api['title']); ?></h5>
                                        <span class="badge badge-api"><?php echo htmlspecialchars($api['version']); ?></span>
                                    </div>
                                    <p><?php echo htmlspecialchars($api['description']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center mt-4">
                                        <span class="badge badge-endpoints"><?php echo htmlspecialchars($api['endpoints']); ?> 个接口</span>
                                        <a href="/admin/api/documentation/<?php echo htmlspecialchars($api['id']); ?>" class="btn btn-sm btn-primary">
                                            查看文档 <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- 开发工具 -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h4 class="mb-3"><i class="bi bi-tools"></i> 开发工具</h4>
                            </div>
                            <div class="col-md-6">
                                <div class="api-card">
                                    <h5><i class="bi bi-code-slash"></i> Swagger UI</h5>
                                    <p>使用交互式Swagger UI浏览并测试API端点。支持在线请求发送和响应查看。</p>
                                    <div class="text-end mt-3">
                                        <a href="/admin/api/documentation/swagger" class="btn btn-sm btn-outline-primary">
                                            打开Swagger UI <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="api-card">
                                    <h5><i class="bi bi-cloud-download"></i> Postman Collection</h5>
                                    <p>下载Postman Collection以便在您自己的环境中测试API。包含所有端点和示例请求。</p>
                                    <div class="text-end mt-3">
                                        <a href="/admin/api/documentation/postman" class="btn btn-sm btn-outline-primary">
                                            下载Collection <i class="bi bi-download"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>









