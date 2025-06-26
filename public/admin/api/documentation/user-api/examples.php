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
    <title>用户API示例代码 - AlingAi Pro</title>
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

        pre {
            border-radius: 8px;
            margin: 15px 0;
        }

        .code-tab-content {
            padding: 20px;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 8px 8px;
            background-color: #282c34;
        }

        .nav-tabs .nav-link {
            border-radius: 8px 8px 0 0;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            margin-right: 4px;
        }

        .nav-tabs .nav-link.active {
            background-color: #282c34;
            color: white;
            border-color: #dee2e6 #dee2e6 #282c34;
        }

        .example-card {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .example-header {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .example-body {
            padding: 0;
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
                        <h4>用户API示例代码</h4>
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
                                        <li class="breadcrumb-item"><a href="/admin/api/documentation/user-api">用户API</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">示例代码</li>
                                    </ol>
                                </nav>
                                
                                <h2 class="mb-3"><i class="bi bi-code-square"></i> 用户API示例代码</h2>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> 
                                    本页面提供了多种编程语言的用户API调用示例，包括身份验证、获取用户列表、创建用户等操作。
                                    您可以直接复制这些示例代码并根据自己的需求进行修改。
                                </div>
                            </div>
                        </div>
                        
                        <!-- 身份验证示例 -->
                        <div class="row">
                            <div class="col-12">
                                <div class="example-card">
                                    <div class="example-header">
                                        <h4>身份验证</h4>
                                        <p class="mb-0">所有API请求都需要包含身份验证信息。以下示例展示了如何获取身份验证令牌并在后续请求中使用。</p>
                                    </div>
                                    <div class="example-body">
                                        <ul class="nav nav-tabs" id="authTabs" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active" id="curl-tab" data-bs-toggle="tab" data-bs-target="#curl-auth" type="button" role="tab" aria-selected="true">cURL</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="php-tab" data-bs-toggle="tab" data-bs-target="#php-auth" type="button" role="tab" aria-selected="false">PHP</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="python-tab" data-bs-toggle="tab" data-bs-target="#python-auth" type="button" role="tab" aria-selected="false">Python</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="js-tab" data-bs-toggle="tab" data-bs-target="#js-auth" type="button" role="tab" aria-selected="false">JavaScript</button>
                                            </li>
                                        </ul>
                                        <div class="tab-content code-tab-content" id="authTabContent">
                                            <div class="tab-pane fade show active" id="curl-auth" role="tabpanel">
                                                <pre><code class="language-bash">curl -X POST "https://api.alingai.pro/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "your_username",
    "password": "your_password"
  }'

# 响应示例
# {
#   "success": true,
#   "message": "登录成功",
#   "data": {
#     "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
#     "expires_at": "2023-12-20T15:30:00Z"
#   }
# }

# 使用获取到的令牌访问API
curl -X GET "https://api.alingai.pro/api/v1/users" \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."</code></pre>
                                            </div>
                                            <div class="tab-pane fade" id="php-auth" role="tabpanel">
                                                <pre><code class="language-php">&lt;?php
// 获取身份验证令牌
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.alingai.pro/api/auth/login");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "username" => "your_username",
    "password" => "your_password"
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$token = $data['data']['token'];

// 使用令牌访问API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.alingai.pro/api/v1/users");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $token
]);

$response = curl_exec($ch);
curl_close($ch);

$users = json_decode($response, true);
print_r($users);
?></code></pre>
                                            </div>
                                            <div class="tab-pane fade" id="python-auth" role="tabpanel">
                                                <pre><code class="language-python">import requests
import json

# 获取身份验证令牌
login_url = "https://api.alingai.pro/api/auth/login"
login_data = {
    "username": "your_username",
    "password": "your_password"
}
response = requests.post(login_url, json=login_data)
data = response.json()

if data["success"]:
    token = data["data"]["token"]
    
    # 使用令牌访问API
    headers = {
        "Authorization": f"Bearer {token}"
    }
    users_url = "https://api.alingai.pro/api/v1/users"
    users_response = requests.get(users_url, headers=headers)
    users_data = users_response.json()
    
    print(json.dumps(users_data, indent=2))
else:
    print("Authentication failed:", data["message"])</code></pre>
                                            </div>
                                            <div class="tab-pane fade" id="js-auth" role="tabpanel">
                                                <pre><code class="language-javascript">// 获取身份验证令牌
async function authenticate() {
  try {
    const response = await fetch('https://api.alingai.pro/api/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        username: 'your_username',
        password: 'your_password'
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      const token = data.data.token;
      
      // 使用令牌访问API
      const usersResponse = await fetch('https://api.alingai.pro/api/v1/users', {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      
      const usersData = await usersResponse.json();
      console.log(usersData);
    } else {
      console.error('Authentication failed:', data.message);
    }
  } catch (error) {
    console.error('Error:', error);
  }
}

authenticate();</code></pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 获取用户列表示例 -->
                        <div class="row">
                            <div class="col-12">
                                <div class="example-card">
                                    <div class="example-header">
                                        <h4>获取用户列表</h4>
                                        <p class="mb-0">以下示例展示了如何获取用户列表，包括分页、搜索和过滤功能。</p>
                                    </div>
                                    <div class="example-body">
                                        <ul class="nav nav-tabs" id="getUsersTabs" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active" id="curl-get-tab" data-bs-toggle="tab" data-bs-target="#curl-get" type="button" role="tab" aria-selected="true">cURL</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="php-get-tab" data-bs-toggle="tab" data-bs-target="#php-get" type="button" role="tab" aria-selected="false">PHP</button>
                                            </li>
                                        </ul>
                                        <div class="tab-content code-tab-content" id="getUsersTabContent">
                                            <div class="tab-pane fade show active" id="curl-get" role="tabpanel">
                                                <pre><code class="language-bash"># 基本用户列表获取
curl -X GET "https://api.alingai.pro/api/v1/users" \
  -H "Authorization: Bearer YOUR_TOKEN"

# 分页和限制
curl -X GET "https://api.alingai.pro/api/v1/users?page=2&limit=10" \
  -H "Authorization: Bearer YOUR_TOKEN"

# 搜索和过滤
curl -X GET "https://api.alingai.pro/api/v1/users?search=admin&status=active" \
  -H "Authorization: Bearer YOUR_TOKEN"</code></pre>
                                            </div>
                                            <div class="tab-pane fade" id="php-get" role="tabpanel">
                                                <pre><code class="language-php">&lt;?php
$token = "YOUR_TOKEN";

// 基本用户列表获取
function getUsers($token, $params = []) {
    $query = http_build_query($params);
    $url = "https://api.alingai.pro/api/v1/users";
    if (!empty($query)) {
        $url .= "?" . $query;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $token
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// 获取第一页用户，每页10条
$users = getUsers($token, [
    'page' => 1,
    'limit' => 10
]);

// 搜索和过滤
$filteredUsers = getUsers($token, [
    'search' => 'admin',
    'status' => 'active'
]);

print_r($users);
?></code></pre>
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
    </div>

    <!-- 使用CDN加载JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    <script>
        $(document).ready(function() {
            // 代码高亮
            hljs.highlightAll();
        });
    </script>
</body>
</html> 