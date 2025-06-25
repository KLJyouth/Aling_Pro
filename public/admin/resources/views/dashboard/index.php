<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'IT运维中心' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Microsoft YaHei', 'PingFang SC', sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: #343a40;
            min-height: 100vh;
            color: #fff;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75];
            padding: 0.75rem 1rem;
        }
        .sidebar .nav-link:hover {
            color: #fff;
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1];
        }
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075];
            margin-bottom: 1.5rem;
        }
        .card-header {
            font-weight: 600;
            background-color: rgba(0, 0, 0, 0.03];
        }
        .system-info-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        .system-info-item:last-child {
            border-bottom: none;
        }
        .log-item {
            background-color: #f8f9fa;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            border-radius: 0.25rem;
            font-family: monospace;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- 侧边�?-->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5>AlingAi_pro</h5>
                        <p class="text-muted">IT运维中心</p>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="/dashboard">
                                <i class="bi bi-speedometer2"></i> 仪表�?
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/tools">
                                <i class="bi bi-tools"></i> 维护工具
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/monitoring">
                                <i class="bi bi-graph-up"></i> 系统监控
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/security">
                                <i class="bi bi-shield-lock"></i> 安全管理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/reports">
                                <i class="bi bi-file-earmark-text"></i> 运维报告
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/logs">
                                <i class="bi bi-journal-text"></i> 日志管理
                            </a>
                        </li>
                    </ul>
                    
                    <hr>
                    
                    <div class="text-center mb-3">
                        <a href="/logout" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-box-arrow-right"></i> 退�?
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- 主内容区 -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">系统仪表�?/h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-repeat"></i> 刷新
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-download"></i> 导出
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- 状态卡�?-->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">工具总数</h6>
                                        <h3 class="card-text"><?= $toolsStats['totalTools'] ?? 0 ?></h3>
                                    </div>
                                    <i class="bi bi-tools fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">系统状�?/h6>
                                        <h3 class="card-text">正常</h3>
                                    </div>
                                    <i class="bi bi-check-circle fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">磁盘使用�?/h6>
                                        <h3 class="card-text">
                                            <?php 
                                                $diskUsed = disk_total_space('/') - disk_free_space('/'];
                                                $diskTotal = disk_total_space('/'];
                                                $diskUsagePercent = ($diskUsed / $diskTotal) * 100;
                                                echo round($diskUsagePercent) . '%';
                                            ?>
                                        </h3>
                                    </div>
                                    <i class="bi bi-hdd fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">PHP版本</h6>
                                        <h3 class="card-text"><?= $systemInfo['phpVersion'] ?? '未知' ?></h3>
                                    </div>
                                    <i class="bi bi-filetype-php fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- 系统信息 -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-info-circle"></i> 系统信息
                            </div>
                            <div class="card-body">
                                <div class="system-info-item d-flex justify-content-between">
                                    <span>操作系统:</span>
                                    <span class="text-muted"><?= $systemInfo['operatingSystem'] ?? '未知' ?></span>
                                </div>
                                <div class="system-info-item d-flex justify-content-between">
                                    <span>服务器软�?</span>
                                    <span class="text-muted"><?= $systemInfo['serverSoftware'] ?? '未知' ?></span>
                                </div>
                                <div class="system-info-item d-flex justify-content-between">
                                    <span>内存使用:</span>
                                    <span class="text-muted"><?= $systemInfo['memoryUsage'] ?? '未知' ?></span>
                                </div>
                                <div class="system-info-item d-flex justify-content-between">
                                    <span>可用磁盘空间:</span>
                                    <span class="text-muted"><?= $systemInfo['diskFreeSpace'] ?? '未知' ?></span>
                                </div>
                                <div class="system-info-item d-flex justify-content-between">
                                    <span>总磁盘空�?</span>
                                    <span class="text-muted"><?= $systemInfo['diskTotalSpace'] ?? '未知' ?></span>
                                </div>
                                <div class="system-info-item d-flex justify-content-between">
                                    <span>服务器时�?</span>
                                    <span class="text-muted"><?= $systemInfo['serverTime'] ?? '未知' ?></span>
                                </div>
                                <div class="system-info-item d-flex justify-content-between">
                                    <span>时区:</span>
                                    <span class="text-muted"><?= $systemInfo['timeZone'] ?? '未知' ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 工具统计 -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-bar-chart"></i> 工具统计
                            </div>
                            <div class="card-body">
                                <canvas id="toolsChart" height="200"></canvas>
                                
                                <hr>
                                
                                <h6>最近使用的工具</h6>
                                <ul class="list-group">
                                    <?php foreach ($toolsStats['recentlyUsed'] ?? [] as $tool): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= $tool['name'] ?>
                                        <span class="badge bg-primary rounded-pill"><?= $tool['lastUsed'] ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($toolsStats['recentlyUsed'])): ?>
                                    <li class="list-group-item">暂无工具使用记录</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 最近日�?-->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-journal-text"></i> 最近日�?
                            </div>
                            <div class="card-body">
                                <?php if (!empty($recentLogs)): ?>
                                    <?php foreach ($recentLogs as $log): ?>
                                    <div class="mb-3">
                                        <h6><?= $log['name'] ?> <small class="text-muted">(<?= $log['modified'] ?>, <?= $log['size'] ?>)</small></h6>
                                        <div class="log-item">
                                            <pre class="mb-0"><?= htmlspecialchars($log['content']) ?></pre>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">暂无日志记录</p>
                                <?php endif; ?>
                                
                                <div class="text-end mt-3">
                                    <a href="/logs" class="btn btn-sm btn-primary">查看所有日�?/a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script>
        // 工具统计图表
        const toolsChart = document.getElementById('toolsChart'];
        if (toolsChart) {
            const categories = <?= json_encode($toolsStats['categories'] ?? []) ?>;
            
            new Chart(toolsChart, {
                type: 'pie',
                data: {
                    labels: ['修复工具', '检查工�?, '验证工具', '其他工具'], 
                    datasets: [{
                        data: [
                            categories.fix || 0,
                            categories.check || 0,
                            categories.validate || 0,
                            categories.other || 0
                        ], 
                        backgroundColor: [
                            '#0d6efd',
                            '#198754',
                            '#ffc107',
                            '#6c757d'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            }];
        }
    </script>
</body>
</html> 
