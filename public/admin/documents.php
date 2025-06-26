<?php
// 初始化会话
session_start();

// 检查用户是否已登录
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    // 用户未登录，重定向到登录页面
    header('Location: login.php');
    exit;
}

// 获取用户角色信息
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$username = $_SESSION['username'];

// 模拟文档数据
$documents = [
    [
        'id' => 1,
        'title' => '系统用户手册',
        'category' => '用户文档',
        'created_at' => '2023-09-15',
        'updated_at' => '2023-11-20',
        'status' => '已发布',
        'owner' => 'Admin'
    ],
    [
        'id' => 2,
        'title' => 'AlingAi Pro API文档',
        'category' => '开发文档',
        'created_at' => '2023-10-01',
        'updated_at' => '2023-12-05',
        'status' => '已发布',
        'owner' => 'Admin'
    ],
    [
        'id' => 3,
        'title' => '量子加密技术白皮书',
        'category' => '技术文档',
        'created_at' => '2023-11-10',
        'updated_at' => '2023-12-01',
        'status' => '草稿',
        'owner' => 'Admin'
    ],
    [
        'id' => 4,
        'title' => '安全基线配置指南',
        'category' => '安全文档',
        'created_at' => '2023-10-25',
        'updated_at' => '2023-11-15',
        'status' => '已发布',
        'owner' => 'Admin'
    ],
    [
        'id' => 5,
        'title' => '威胁情报分析报告',
        'category' => '安全文档',
        'created_at' => '2023-12-01',
        'updated_at' => '2023-12-10',
        'status' => '内部',
        'owner' => 'Admin'
    ]
];

// 获取文档统计
$totalDocs = count($documents);
$publishedDocs = count(array_filter($documents, function($doc) { return $doc['status'] === '已发布'; }));
$draftDocs = count(array_filter($documents, function($doc) { return $doc['status'] === '草稿'; }));
$internalDocs = count(array_filter($documents, function($doc) { return $doc['status'] === '内部'; }));

// 处理POST请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 这里添加处理文档操作的代码
    // 实际项目中应该连接到数据库进行CRUD操作
    $response = ['success' => true, 'message' => '操作成功'];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文档管理 - AlingAi Pro</title>
    <!-- 使用CDN加载资源 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .admin-container {
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

        .metric-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-5px);
        }

        .metric-icon {
            font-size: 24px;
            margin-bottom: 10px;
            color: #3498db;
        }

        .metric-value {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .metric-label {
            color: #7f8c8d;
            font-size: 14px;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-success {
            background-color: #d4edda;
            color: #155724;
        }

        .status-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-info {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .btn-custom {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            border: none;
            transition: all 0.3s;
        }

        .btn-primary-custom {
            background: #3498db;
            color: white;
        }

        .btn-primary-custom:hover {
            background: #2980b9;
            transform: translateY(-2px);
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

        .table th {
            font-weight: 600;
            color: #2c3e50;
        }

        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .document-filter {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="admin-container">
            <div class="row g-0">
                <!-- 侧边栏 -->
                <div class="col-md-3 col-lg-2 sidebar">
                    <div class="logo-area">
                        <h3>AlingAi Pro</h3>
                        <p class="mb-0">量子安全管理系统</p>
                    </div>
                    <ul class="nav flex-column">
                        <!-- 核心管理 -->
                        <div class="nav-group-title">核心管理</div>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin">
                                <i class="bi bi-speedometer2 me-2"></i> 仪表盘
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/users.php">
                                <i class="bi bi-people me-2"></i> 用户管理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/admin/documents.php">
                                <i class="bi bi-file-earmark-text me-2"></i> 文档管理
                            </a>
                        </li>
                        
                        <!-- 安全管理 -->
                        <div class="nav-group-title">安全管理</div>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/security-dashboard.html">
                                <i class="bi bi-shield-lock me-2"></i> 安全总览
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/quantum-security.html">
                                <i class="bi bi-radioactive me-2"></i> 量子加密
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/threat-intelligence-dashboard.html">
                                <i class="bi bi-binoculars me-2"></i> 威胁情报
                            </a>
                        </li>
                        
                        <!-- API与监控 -->
                        <div class="nav-group-title">API与监控</div>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/api_monitor_dashboard.html">
                                <i class="bi bi-graph-up me-2"></i> API监控
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/api/documentation">
                                <i class="bi bi-code-slash me-2"></i> API文档
                            </a>
                        </li>
                        
                        <!-- 系统设置 -->
                        <div class="nav-group-title">系统设置</div>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/config_manager.php">
                                <i class="bi bi-gear me-2"></i> 系统配置
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/baseline_manager.php">
                                <i class="bi bi-diagram-3 me-2"></i> 基线管理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i> 安全退出
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- 内容区域 -->
                <div class="col-md-9 col-lg-10">
                    <div class="header-area d-flex justify-content-between align-items-center">
                        <h4>文档管理</h4>
                        <div>
                            <span class="me-3">管理员：<?php echo htmlspecialchars($username); ?></span>
                            <a href="/admin/logout.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-box-arrow-right"></i> 退出
                            </a>
                        </div>
                    </div>
                    
                    <div class="content-area">
                        <div class="row">
                            <div class="col-12">
                                <h2 class="mb-4"><i class="bi bi-file-earmark-text"></i> 文档管理中心</h2>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> 
                                    <strong>管理提示：</strong> 
                                    在此页面可以管理系统中的所有文档，包括用户手册、API文档、技术白皮书等。
                                </div>
                            </div>
                        </div>
                        
                        <!-- 文档统计 -->
                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <div class="metric-card">
                                    <div class="metric-icon">
                                        <i class="bi bi-files"></i>
                                    </div>
                                    <div class="metric-value"><?php echo $totalDocs; ?></div>
                                    <div class="metric-label">总文档数</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="metric-card">
                                    <div class="metric-icon">
                                        <i class="bi bi-file-earmark-check"></i>
                                    </div>
                                    <div class="metric-value"><?php echo $publishedDocs; ?></div>
                                    <div class="metric-label">已发布文档</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="metric-card">
                                    <div class="metric-icon">
                                        <i class="bi bi-file-earmark-diff"></i>
                                    </div>
                                    <div class="metric-value"><?php echo $draftDocs; ?></div>
                                    <div class="metric-label">草稿文档</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="metric-card">
                                    <div class="metric-icon">
                                        <i class="bi bi-file-earmark-lock"></i>
                                    </div>
                                    <div class="metric-value"><?php echo $internalDocs; ?></div>
                                    <div class="metric-label">内部文档</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 文档操作区 -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="metric-card">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5><i class="bi bi-file-earmark-plus"></i> 文档管理</h5>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newDocumentModal">
                                            <i class="bi bi-plus-circle"></i> 新建文档
                                        </button>
                                    </div>
                                    
                                    <!-- 文档筛选 -->
                                    <div class="document-filter">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <select class="form-select form-select-sm" id="categoryFilter">
                                                    <option value="">所有分类</option>
                                                    <option value="用户文档">用户文档</option>
                                                    <option value="开发文档">开发文档</option>
                                                    <option value="技术文档">技术文档</option>
                                                    <option value="安全文档">安全文档</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <select class="form-select form-select-sm" id="statusFilter">
                                                    <option value="">所有状态</option>
                                                    <option value="已发布">已发布</option>
                                                    <option value="草稿">草稿</option>
                                                    <option value="内部">内部</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" class="form-control form-control-sm" id="searchDocument" placeholder="搜索文档...">
                                            </div>
                                            <div class="col-md-2">
                                                <button class="btn btn-sm btn-secondary w-100" id="resetFilters">重置筛选</button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- 文档表格 -->
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>标题</th>
                                                    <th>分类</th>
                                                    <th>创建日期</th>
                                                    <th>更新日期</th>
                                                    <th>状态</th>
                                                    <th>所有者</th>
                                                    <th>操作</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($documents as $doc): ?>
                                                <tr>
                                                    <td><?php echo $doc['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($doc['title']); ?></td>
                                                    <td><?php echo htmlspecialchars($doc['category']); ?></td>
                                                    <td><?php echo $doc['created_at']; ?></td>
                                                    <td><?php echo $doc['updated_at']; ?></td>
                                                    <td>
                                                        <?php
                                                        $statusClass = '';
                                                        switch ($doc['status']) {
                                                            case '已发布':
                                                                $statusClass = 'status-success';
                                                                break;
                                                            case '草稿':
                                                                $statusClass = 'status-warning';
                                                                break;
                                                            case '内部':
                                                                $statusClass = 'status-info';
                                                                break;
                                                            default:
                                                                $statusClass = 'status-secondary';
                                                        }
                                                        ?>
                                                        <span class="status-badge <?php echo $statusClass; ?>">
                                                            <?php echo htmlspecialchars($doc['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($doc['owner']); ?></td>
                                                    <td class="action-buttons">
                                                        <button class="btn btn-sm btn-outline-primary" onclick="viewDocument(<?php echo $doc['id']; ?>)">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-secondary" onclick="editDocument(<?php echo $doc['id']; ?>)">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <?php if ($isAdmin): ?>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteDocument(<?php echo $doc['id']; ?>)">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 新建文档模态框 -->
    <div class="modal fade" id="newDocumentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">新建文档</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="documentForm">
                        <div class="mb-3">
                            <label for="docTitle" class="form-label">文档标题</label>
                            <input type="text" class="form-control" id="docTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="docCategory" class="form-label">分类</label>
                            <select class="form-select" id="docCategory" required>
                                <option value="">请选择分类</option>
                                <option value="用户文档">用户文档</option>
                                <option value="开发文档">开发文档</option>
                                <option value="技术文档">技术文档</option>
                                <option value="安全文档">安全文档</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="docStatus" class="form-label">状态</label>
                            <select class="form-select" id="docStatus" required>
                                <option value="草稿">草稿</option>
                                <option value="已发布">已发布</option>
                                <option value="内部">内部</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="docContent" class="form-label">文档内容</label>
                            <textarea class="form-control" id="docContent" rows="10" placeholder="在此输入文档内容..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="saveDocument">保存文档</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 使用CDN加载JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 文档管理相关功能
        function viewDocument(id) {
            alert('查看文档 ID: ' + id);
            // 实际项目中应跳转到文档查看页面
            // window.location.href = '/admin/document-view.php?id=' + id;
        }
        
        function editDocument(id) {
            alert('编辑文档 ID: ' + id);
            // 实际项目中应打开编辑模态框并加载数据
        }
        
        function deleteDocument(id) {
            if (confirm('确定要删除此文档吗？此操作不可恢复。')) {
                alert('删除文档 ID: ' + id);
                // 实际项目中应发送Ajax请求删除文档
            }
        }
        
        // 初始化
        $(document).ready(function() {
            // 保存文档
            $('#saveDocument').click(function() {
                const title = $('#docTitle').val();
                const category = $('#docCategory').val();
                const status = $('#docStatus').val();
                const content = $('#docContent').val();
                
                if (!title || !category) {
                    alert('请填写必填字段！');
                    return;
                }
                
                // 简单验证，实际项目中应发送Ajax请求保存数据
                alert('文档已保存：' + title);
                $('#newDocumentModal').modal('hide');
                
                // 模拟刷新页面以显示新数据
                // 实际项目中应该添加到表格而不是刷新页面
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            });
            
            // 重置筛选
            $('#resetFilters').click(function() {
                $('#categoryFilter').val('');
                $('#statusFilter').val('');
                $('#searchDocument').val('');
                // 实际项目中应重新加载数据
            });
            
            // 监听筛选变化
            $('#categoryFilter, #statusFilter, #searchDocument').on('change keyup', function() {
                // 实际项目中应根据筛选条件过滤数据
                console.log('筛选条件变化，应当重新加载数据');
            });
        });
    </script>
</body>
</html> 