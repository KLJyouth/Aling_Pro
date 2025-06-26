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

// 模拟系统状态数据
$systemStatus = [
    'cpu' => [
        'usage' => 28,
        'cores' => 8,
        'temperature' => 45
    ],
    'memory' => [
        'total' => 16384, // MB
        'used' => 5120,   // MB
        'free' => 11264   // MB
    ],
    'disk' => [
        'total' => 500,   // GB
        'used' => 175,    // GB
        'free' => 325     // GB
    ],
    'network' => [
        'incoming' => 4.5, // MB/s
        'outgoing' => 2.1  // MB/s
    ],
    'services' => [
        [
            'name' => 'Web服务器',
            'status' => 'running',
            'uptime' => '15 天 7 小时',
            'memory' => 256 // MB
        ],
        [
            'name' => 'API网关',
            'status' => 'running',
            'uptime' => '15 天 7 小时',
            'memory' => 312 // MB
        ],
        [
            'name' => '量子加密服务',
            'status' => 'running',
            'uptime' => '15 天 6 小时',
            'memory' => 480 // MB
        ],
        [
            'name' => '数据库服务',
            'status' => 'running',
            'uptime' => '15 天 7 小时',
            'memory' => 720 // MB
        ],
        [
            'name' => '缓存服务',
            'status' => 'running',
            'uptime' => '15 天 7 小时',
            'memory' => 215 // MB
        ]
    ],
    'logs' => [
        [
            'time' => '2023-12-15 09:45:12',
            'level' => 'info',
            'message' => '系统启动完成，所有服务正常运行'
        ],
        [
            'time' => '2023-12-15 10:12:25',
            'level' => 'warning',
            'message' => 'CPU负载短暂超过80%，已自动调整资源分配'
        ],
        [
            'time' => '2023-12-15 11:30:45',
            'level' => 'info',
            'message' => '自动备份任务完成，数据已安全存储'
        ],
        [
            'time' => '2023-12-15 13:15:22',
            'level' => 'error',
            'message' => '检测到异常登录尝试，IP已被临时封禁'
        ],
        [
            'time' => '2023-12-15 14:05:37',
            'level' => 'info',
            'message' => '系统更新检查完成，当前版本已是最新'
        ]
    ]
];

// 处理AJAX请求 - 获取实时数据
if (isset($_GET['action']) && $_GET['action'] === 'get_data') {
    header('Content-Type: application/json');
    
    // 模拟数据变化
    $systemStatus['cpu']['usage'] = rand(15, 35);
    $systemStatus['memory']['used'] = rand(4500, 5500);
    $systemStatus['memory']['free'] = 16384 - $systemStatus['memory']['used'];
    $systemStatus['network']['incoming'] = round(rand(35, 55) / 10, 1);
    $systemStatus['network']['outgoing'] = round(rand(15, 25) / 10, 1);
    
    echo json_encode($systemStatus);
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统监控 - AlingAi Pro</title>
    <!-- 使用CDN加载资源 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
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

        .resource-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .resource-title i {
            margin-right: 10px;
            color: #3498db;
        }

        .progress {
            height: 10px;
            margin-bottom: 15px;
        }

        .progress-slim {
            height: 6px;
        }

        .resource-detail {
            display: flex;
            justify-content: space-between;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 50rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-running {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .status-warning {
            background-color: #fff3cd;
            color: #664d03;
        }

        .status-error {
            background-color: #f8d7da;
            color: #842029;
        }

        .log-level-info {
            background-color: #cfe2ff;
            color: #084298;
        }

        .log-level-warning {
            background-color: #fff3cd;
            color: #664d03;
        }

        .log-level-error {
            background-color: #f8d7da;
            color: #842029;
        }

        .service-item {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .service-item:last-child {
            border-bottom: none;
        }

        .service-name {
            font-weight: 500;
        }

        .service-memory {
            font-size: 0.85rem;
            color: #6c757d;
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

        .refresh-button {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.3s ease;
            color: #6c757d;
        }

        .refresh-button:hover {
            background: #e9ecef;
            color: #212529;
            transform: rotate(45deg);
        }

        .refresh-button i {
            font-size: 16px;
        }

        .chart-container {
            position: relative;
            height: 200px;
            width: 100%;
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
                            <a class="nav-link" href="/admin/documents.php">
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
                        <li class="nav-item">
                            <a class="nav-link active" href="/admin/system_monitor.php">
                                <i class="bi bi-cpu me-2"></i> 系统监控
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
                        <h4>系统监控</h4>
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
                                <h2 class="mb-4"><i class="bi bi-cpu"></i> 系统监控中心</h2>
                                <div class="alert alert-success d-flex align-items-center" role="alert">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <div>所有系统服务正常运行中，性能指标在正常范围内。</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 资源使用概览 -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="metric-card position-relative">
                                    <button class="refresh-button" onclick="refreshData()">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                    <div class="resource-title">
                                        <i class="bi bi-cpu"></i> CPU
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" id="cpu-progress" style="width: <?php echo $systemStatus['cpu']['usage']; ?>%"></div>
                                    </div>
                                    <div class="resource-detail">
                                        <span>使用率: <strong id="cpu-usage"><?php echo $systemStatus['cpu']['usage']; ?>%</strong></span>
                                        <span>核心数: <?php echo $systemStatus['cpu']['cores']; ?></span>
                                        <span>温度: <?php echo $systemStatus['cpu']['temperature']; ?>°C</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card">
                                    <div class="resource-title">
                                        <i class="bi bi-memory"></i> 内存
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" role="progressbar" id="memory-progress" style="width: <?php echo ($systemStatus['memory']['used'] / $systemStatus['memory']['total']) * 100; ?>%"></div>
                                    </div>
                                    <div class="resource-detail">
                                        <span>总内存: <?php echo $systemStatus['memory']['total']; ?> MB</span>
                                        <span>已用: <strong id="memory-used"><?php echo $systemStatus['memory']['used']; ?> MB</strong></span>
                                        <span>可用: <strong id="memory-free"><?php echo $systemStatus['memory']['free']; ?> MB</strong></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card">
                                    <div class="resource-title">
                                        <i class="bi bi-hdd"></i> 磁盘
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo ($systemStatus['disk']['used'] / $systemStatus['disk']['total']) * 100; ?>%"></div>
                                    </div>
                                    <div class="resource-detail">
                                        <span>总容量: <?php echo $systemStatus['disk']['total']; ?> GB</span>
                                        <span>已用: <?php echo $systemStatus['disk']['used']; ?> GB</span>
                                        <span>可用: <?php echo $systemStatus['disk']['free']; ?> GB</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card">
                                    <div class="resource-title">
                                        <i class="bi bi-diagram-3"></i> 网络
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="mb-1">下载: <strong id="network-in"><?php echo $systemStatus['network']['incoming']; ?> MB/s</strong></span>
                                        <div class="progress progress-slim mb-2">
                                            <div class="progress-bar bg-success" role="progressbar" id="network-in-progress" style="width: <?php echo $systemStatus['network']['incoming'] * 10; ?>%"></div>
                                        </div>
                                        <span class="mb-1">上传: <strong id="network-out"><?php echo $systemStatus['network']['outgoing']; ?> MB/s</strong></span>
                                        <div class="progress progress-slim">
                                            <div class="progress-bar bg-primary" role="progressbar" id="network-out-progress" style="width: <?php echo $systemStatus['network']['outgoing'] * 10; ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 性能图表和服务状态 -->
                        <div class="row mt-4">
                            <div class="col-md-8">
                                <div class="metric-card">
                                    <div class="resource-title">
                                        <i class="bi bi-graph-up"></i> 性能监控
                                    </div>
                                    <div class="chart-container">
                                        <canvas id="performanceChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="metric-card">
                                    <div class="resource-title">
                                        <i class="bi bi-layers"></i> 服务状态
                                    </div>
                                    <div class="service-list">
                                        <?php foreach ($systemStatus['services'] as $service): ?>
                                        <div class="service-item">
                                            <div>
                                                <div class="service-name"><?php echo $service['name']; ?></div>
                                                <div class="service-memory">内存: <?php echo $service['memory']; ?> MB | 运行时间: <?php echo $service['uptime']; ?></div>
                                            </div>
                                            <div>
                                                <span class="status-badge status-<?php echo $service['status']; ?>">
                                                    <?php echo $service['status'] === 'running' ? '运行中' : $service['status']; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 系统日志 -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="metric-card">
                                    <div class="resource-title">
                                        <i class="bi bi-journal-text"></i> 系统日志
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>时间</th>
                                                    <th>级别</th>
                                                    <th>消息</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($systemStatus['logs'] as $log): ?>
                                                <tr>
                                                    <td><?php echo $log['time']; ?></td>
                                                    <td>
                                                        <span class="status-badge log-level-<?php echo $log['level']; ?>">
                                                            <?php echo $log['level']; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $log['message']; ?></td>
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

    <!-- 使用CDN加载JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 性能图表初始化
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: Array.from({length: 10}, (_, i) => `${i+1}分钟前`).reverse(),
                datasets: [
                    {
                        label: 'CPU使用率 (%)',
                        data: [22, 25, 28, 30, 25, 24, 26, 28, 30, 28],
                        borderColor: 'rgba(40, 167, 69, 1)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: '内存使用率 (%)',
                        data: [30, 31, 32, 33, 34, 35, 32, 31, 30, 31],
                        borderColor: 'rgba(0, 123, 255, 1)',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });

        // 更新图表数据
        function updateChart(cpuData) {
            const newCpuData = [...performanceChart.data.datasets[0].data.slice(1), cpuData];
            performanceChart.data.datasets[0].data = newCpuData;
            
            const memoryUsed = <?php echo $systemStatus['memory']['used']; ?>;
            const memoryTotal = <?php echo $systemStatus['memory']['total']; ?>;
            const memoryPercentage = Math.round((memoryUsed / memoryTotal) * 100);
            
            const newMemoryData = [...performanceChart.data.datasets[1].data.slice(1), memoryPercentage];
            performanceChart.data.datasets[1].data = newMemoryData;
            
            performanceChart.update();
        }
        
        // 刷新数据
        function refreshData() {
            fetch('system_monitor.php?action=get_data')
                .then(response => response.json())
                .then(data => {
                    // 更新CPU数据
                    document.getElementById('cpu-usage').textContent = data.cpu.usage + '%';
                    document.getElementById('cpu-progress').style.width = data.cpu.usage + '%';
                    
                    // 更新内存数据
                    document.getElementById('memory-used').textContent = data.memory.used + ' MB';
                    document.getElementById('memory-free').textContent = data.memory.free + ' MB';
                    document.getElementById('memory-progress').style.width = (data.memory.used / data.memory.total) * 100 + '%';
                    
                    // 更新网络数据
                    document.getElementById('network-in').textContent = data.network.incoming + ' MB/s';
                    document.getElementById('network-out').textContent = data.network.outgoing + ' MB/s';
                    document.getElementById('network-in-progress').style.width = data.network.incoming * 10 + '%';
                    document.getElementById('network-out-progress').style.width = data.network.outgoing * 10 + '%';
                    
                    // 更新图表
                    updateChart(data.cpu.usage);
                    
                    // 添加动画效果
                    const refreshButton = document.querySelector('.refresh-button');
                    refreshButton.classList.add('refreshing');
                    
                    setTimeout(() => {
                        refreshButton.classList.remove('refreshing');
                    }, 500);
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                });
        }
        
        // 自动刷新
        setInterval(refreshData, 10000);
        
        // 页面加载完成后初始化
        document.addEventListener('DOMContentLoaded', function() {
            // 初始刷新
            setTimeout(refreshData, 2000);
        });
    </script>
</body>
</html> 