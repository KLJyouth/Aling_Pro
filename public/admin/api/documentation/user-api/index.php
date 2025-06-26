<?php
// 初始化会话
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
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户API文档 - AlingAi Pro</title>
    <!-- 使用CDN加载资源 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/atom-one-dark.min.css">
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

        .nav-group-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.5);
            padding: 15px 20px 5px;
            margin-top: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .endpoint-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .endpoint-header {
            display: flex;
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            background-color: #f8f9fa;
        }

        .method-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            margin-right: 10px;
        }

        .method-get {
            background-color: #e3f2fd;
            color: #0d6efd;
        }

        .method-post {
            background-color: #d1e7dd;
            color: #198754;
        }

        .method-put {
            background-color: #fff3cd;
            color: #ffc107;
        }

        .method-delete {
            background-color: #f8d7da;
            color: #dc3545;
        }

        .endpoint-url {
            font-family: monospace;
            font-size: 16px;
            margin-bottom: 0;
            display: flex;
            align-items: center;
            flex-grow: 1;
        }

        .endpoint-body {
            padding: 20px;
        }

        .param-table th {
            background-color: #f8f9fa;
        }

        pre {
            border-radius: 8px;
            margin: 15px 0;
        }

        .response-example {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
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
                            <a class="nav-link" href="/admin/api/documentation">
                                <i class="bi bi-house me-2"></i> API文档首页
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/admin/api/documentation/user-api">
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
                        <h4>用户API文档</h4>
                        <div>
                            <span class="me-3">用户：<?php echo htmlspecialchars($username); ?></span>
                            <a href="/admin/logout.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-box-arrow-right"></i> 退出
                            </a>
                        </div>
                    </div>
                    
                    <div class="content-area">
                        <div class="row mb-4">
                            <div class="col-12">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/admin">仪表盘</a></li>
                                        <li class="breadcrumb-item"><a href="/admin/api/documentation">API文档</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">用户API</li>
                                    </ol>
                                </nav>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <h2 class="mb-3"><i class="bi bi-people"></i> 用户API</h2>
                                    <div>
                                        <a href="/admin/api/documentation/user-api/examples" class="btn btn-outline-primary me-2">
                                            <i class="bi bi-code-square"></i> 示例代码
                                        </a>
                                        <button class="btn btn-outline-secondary" id="toggleAllEndpoints">
                                            <i class="bi bi-arrows-expand"></i> 展开全部
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> 用户API用于管理系统中的用户信息，包括获取用户列表、创建用户、更新用户信息等功能。所有API端点都需要通过认证访问。
                                </div>
                            </div>
                        </div>
                        
                        <!-- API端点列表 -->
                        <div class="row">
                            <div class="col-12">
                                <div class="endpoint-card">
                                    <div class="endpoint-header">
                                        <span class="method-badge method-get">GET</span>
                                        <div class="endpoint-url">/api/v1/users</div>
                                        <button class="btn btn-sm btn-outline-secondary toggle-endpoint">
                                            <i class="bi bi-chevron-down"></i>
                                        </button>
                                    </div>
                                    <div class="endpoint-body" style="display: none;">
                                        <h5>获取用户列表</h5>
                                        <p>返回系统中的所有用户列表，支持分页和搜索。</p>
                                        
                                        <h6>请求参数</h6>
                                        <table class="table table-sm param-table">
                                            <thead>
                                                <tr>
                                                    <th>参数名</th>
                                                    <th>类型</th>
                                                    <th>是否必填</th>
                                                    <th>描述</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>page</td>
                                                    <td>integer</td>
                                                    <td>否</td>
                                                    <td>页码，默认为1</td>
                                                </tr>
                                                <tr>
                                                    <td>limit</td>
                                                    <td>integer</td>
                                                    <td>否</td>
                                                    <td>每页数量，默认为20，最大为100</td>
                                                </tr>
                                                <tr>
                                                    <td>search</td>
                                                    <td>string</td>
                                                    <td>否</td>
                                                    <td>搜索关键词，将在用户名、邮箱等字段中搜索</td>
                                                </tr>
                                                <tr>
                                                    <td>status</td>
                                                    <td>string</td>
                                                    <td>否</td>
                                                    <td>用户状态筛选，可选值：active, inactive, suspended</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        
                                        <h6>响应示例</h6>
                                        <div class="response-example">
                                            <pre><code class="language-json">{
  "success": true,
  "message": "获取用户列表成功",
  "data": {
    "total": 25,
    "page": 1,
    "limit": 10,
    "users": [
      {
        "id": 1,
        "username": "admin",
        "email": "admin@example.com",
        "name": "系统管理员",
        "role": "admin",
        "status": "active",
        "last_login": "2023-12-10 15:30:22",
        "created_at": "2023-01-01 00:00:00"
      },
      // ... 更多用户数据
    ]
  }
}</code></pre>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="endpoint-card">
                                    <div class="endpoint-header">
                                        <span class="method-badge method-get">GET</span>
                                        <div class="endpoint-url">/api/v1/users/{id}</div>
                                        <button class="btn btn-sm btn-outline-secondary toggle-endpoint">
                                            <i class="bi bi-chevron-down"></i>
                                        </button>
                                    </div>
                                    <div class="endpoint-body" style="display: none;">
                                        <h5>获取单个用户详情</h5>
                                        <p>根据用户ID获取用户的详细信息。</p>
                                        
                                        <h6>路径参数</h6>
                                        <table class="table table-sm param-table">
                                            <thead>
                                                <tr>
                                                    <th>参数名</th>
                                                    <th>类型</th>
                                                    <th>是否必填</th>
                                                    <th>描述</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>id</td>
                                                    <td>integer</td>
                                                    <td>是</td>
                                                    <td>用户ID</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        
                                        <h6>响应示例</h6>
                                        <div class="response-example">
                                            <pre><code class="language-json">{
  "success": true,
  "message": "获取用户详情成功",
  "data": {
    "user": {
      "id": 1,
      "username": "admin",
      "email": "admin@example.com",
      "name": "系统管理员",
      "role": "admin",
      "status": "active",
      "phone": "13800138000",
      "department": "技术部",
      "last_login": "2023-12-10 15:30:22",
      "created_at": "2023-01-01 00:00:00",
      "updated_at": "2023-12-01 10:15:30"
    }
  }
}</code></pre>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="endpoint-card">
                                    <div class="endpoint-header">
                                        <span class="method-badge method-post">POST</span>
                                        <div class="endpoint-url">/api/v1/users</div>
                                        <button class="btn btn-sm btn-outline-secondary toggle-endpoint">
                                            <i class="bi bi-chevron-down"></i>
                                        </button>
                                    </div>
                                    <div class="endpoint-body" style="display: none;">
                                        <h5>创建新用户</h5>
                                        <p>创建一个新的系统用户。</p>
                                        
                                        <h6>请求体参数</h6>
                                        <table class="table table-sm param-table">
                                            <thead>
                                                <tr>
                                                    <th>参数名</th>
                                                    <th>类型</th>
                                                    <th>是否必填</th>
                                                    <th>描述</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>username</td>
                                                    <td>string</td>
                                                    <td>是</td>
                                                    <td>用户名，必须唯一</td>
                                                </tr>
                                                <tr>
                                                    <td>email</td>
                                                    <td>string</td>
                                                    <td>是</td>
                                                    <td>电子邮箱，必须唯一</td>
                                                </tr>
                                                <tr>
                                                    <td>password</td>
                                                    <td>string</td>
                                                    <td>是</td>
                                                    <td>密码，至少8个字符</td>
                                                </tr>
                                                <tr>
                                                    <td>name</td>
                                                    <td>string</td>
                                                    <td>是</td>
                                                    <td>姓名</td>
                                                </tr>
                                                <tr>
                                                    <td>role</td>
                                                    <td>string</td>
                                                    <td>否</td>
                                                    <td>角色，可选值：admin, user, editor，默认为user</td>
                                                </tr>
                                                <tr>
                                                    <td>phone</td>
                                                    <td>string</td>
                                                    <td>否</td>
                                                    <td>电话号码</td>
                                                </tr>
                                                <tr>
                                                    <td>department</td>
                                                    <td>string</td>
                                                    <td>否</td>
                                                    <td>部门</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        
                                        <h6>请求示例</h6>
                                        <div class="response-example">
                                            <pre><code class="language-json">{
  "username": "newuser",
  "email": "newuser@example.com",
  "password": "secure_password123",
  "name": "新用户",
  "role": "user",
  "phone": "13900139000",
  "department": "市场部"
}</code></pre>
                                        </div>
                                        
                                        <h6>响应示例</h6>
                                        <div class="response-example">
                                            <pre><code class="language-json">{
  "success": true,
  "message": "用户创建成功",
  "data": {
    "user": {
      "id": 26,
      "username": "newuser",
      "email": "newuser@example.com",
      "name": "新用户",
      "role": "user",
      "status": "active",
      "phone": "13900139000",
      "department": "市场部",
      "created_at": "2023-12-15 09:45:12",
      "updated_at": "2023-12-15 09:45:12"
    }
  }
}</code></pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 使用CDN加载JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    <script>
        $(document).ready(function() {
            // 代码高亮
            hljs.highlightAll();
            
            // 展开/折叠端点详情
            $('.toggle-endpoint').on('click', function() {
                const body = $(this).closest('.endpoint-card').find('.endpoint-body');
                const icon = $(this).find('i');
                
                body.slideToggle(300, function() {
                    if (body.is(':visible')) {
                        icon.removeClass('bi-chevron-down').addClass('bi-chevron-up');
                    } else {
                        icon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
                    }
                });
            });
            
            // 展开/折叠所有端点
            $('#toggleAllEndpoints').on('click', function() {
                const button = $(this);
                const icon = button.find('i');
                const allBodies = $('.endpoint-body');
                const allIcons = $('.toggle-endpoint i');
                
                if (button.data('expanded')) {
                    // 折叠所有
                    allBodies.slideUp(300);
                    allIcons.removeClass('bi-chevron-up').addClass('bi-chevron-down');
                    icon.removeClass('bi-arrows-collapse').addClass('bi-arrows-expand');
                    button.html('<i class="bi bi-arrows-expand"></i> 展开全部');
                    button.data('expanded', false);
                } else {
                    // 展开所有
                    allBodies.slideDown(300);
                    allIcons.removeClass('bi-chevron-down').addClass('bi-chevron-up');
                    icon.removeClass('bi-arrows-expand').addClass('bi-arrows-collapse');
                    button.html('<i class="bi bi-arrows-collapse"></i> 折叠全部');
                    button.data('expanded', true);
                }
            });
        });
    </script>
</body>
</html> 