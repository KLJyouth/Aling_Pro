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
            color: rgba(255, 255, 255, 0.75);
            padding: 0.75rem 1rem;
        }
        .sidebar .nav-link:hover {
            color: #fff;
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }
        .card-header {
            font-weight: 600;
            background-color: rgba(0, 0, 0, 0.03);
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
            <!-- 侧边栏 -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5>AlingAi_pro</h5>
                        <p class="text-muted">IT运维中心</p>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="/dashboard">
                                <i class="bi bi-speedometer2"></i> 仪表盘
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
                            <i class="bi bi-box-arrow-right"></i> 退出
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- 主内容区 -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">系统仪表盘</h1>
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
                
                <!-- 系统状态卡片 -->
                <div class="row">
                    <!-- CPU使用率 -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">CPU使用率</h6>
                                        <h4 class="mb-0"><?= $metrics['cpu']['usage'] ?>%</h4>
                                    </div>
                                    <div>
                                        <?php if ($metrics['cpu']['status'] === 'good'): ?>
                                            <i class="bi bi-cpu fs-1 text-success"></i>
                                        <?php elseif ($metrics['cpu']['status'] === 'warning'): ?>
                                            <i class="bi bi-cpu fs-1 text-warning"></i>
                                        <?php else: ?>
                                            <i class="bi bi-cpu fs-1 text-danger"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="progress mt-3" style="height: 8px;">
                                    <?php if ($metrics['cpu']['status'] === 'good'): ?>
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $metrics['cpu']['usage'] ?>%" aria-valuenow="<?= $metrics['cpu']['usage'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    <?php elseif ($metrics['cpu']['status'] === 'warning'): ?>
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $metrics['cpu']['usage'] ?>%" aria-valuenow="<?= $metrics['cpu']['usage'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    <?php else: ?>
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $metrics['cpu']['usage'] ?>%" aria-valuenow="<?= $metrics['cpu']['usage'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted mt-1 d-block">最近更新: <?= date('H:i:s') ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 内存使用率 -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">内存使用率</h6>
                                        <h4 class="mb-0"><?= $metrics['memory']['usage'] ?>%</h4>
                                    </div>
                                    <div>
                                        <?php if ($metrics['memory']['status'] === 'good'): ?>
                                            <i class="bi bi-memory fs-1 text-success"></i>
                                        <?php elseif ($metrics['memory']['status'] === 'warning'): ?>
                                            <i class="bi bi-memory fs-1 text-warning"></i>
                                        <?php else: ?>
                                            <i class="bi bi-memory fs-1 text-danger"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="progress mt-3" style="height: 8px;">
                                    <?php if ($metrics['memory']['status'] === 'good'): ?>
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $metrics['memory']['usage'] ?>%" aria-valuenow="<?= $metrics['memory']['usage'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    <?php elseif ($metrics['memory']['status'] === 'warning'): ?>
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $metrics['memory']['usage'] ?>%" aria-valuenow="<?= $metrics['memory']['usage'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    <?php else: ?>
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $metrics['memory']['usage'] ?>%" aria-valuenow="<?= $metrics['memory']['usage'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted mt-1 d-block">使用: <?= $metrics['memory']['used'] ?> / 总计: <?= $metrics['memory']['total'] ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 磁盘使用率 -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">磁盘使用率</h6>
                                        <h4 class="mb-0"><?= $metrics['disk']['usage'] ?>%</h4>
                                    </div>
                                    <div>
                                        <?php if ($metrics['disk']['status'] === 'good'): ?>
                                            <i class="bi bi-hdd fs-1 text-success"></i>
                                        <?php elseif ($metrics['disk']['status'] === 'warning'): ?>
                                            <i class="bi bi-hdd fs-1 text-warning"></i>
                                        <?php else: ?>
                                            <i class="bi bi-hdd fs-1 text-danger"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="progress mt-3" style="height: 8px;">
                                    <?php if ($metrics['disk']['status'] === 'good'): ?>
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $metrics['disk']['usage'] ?>%" aria-valuenow="<?= $metrics['disk']['usage'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    <?php elseif ($metrics['disk']['status'] === 'warning'): ?>
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $metrics['disk']['usage'] ?>%" aria-valuenow="<?= $metrics['disk']['usage'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    <?php else: ?>
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $metrics['disk']['usage'] ?>%" aria-valuenow="<?= $metrics['disk']['usage'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted mt-1 d-block">可用: <?= $metrics['disk']['free'] ?> / 总计: <?= $metrics['disk']['total'] ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 数据库连接数 -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">数据库连接</h6>
                                        <h4 class="mb-0"><?= $metrics['database']['connections'] ?></h4>
                                    </div>
                                    <div>
                                        <?php if ($metrics['database']['status'] === 'good'): ?>
                                            <i class="bi bi-database fs-1 text-success"></i>
                                        <?php elseif ($metrics['database']['status'] === 'warning'): ?>
                                            <i class="bi bi-database fs-1 text-warning"></i>
                                        <?php else: ?>
                                            <i class="bi bi-database fs-1 text-danger"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="progress mt-3" style="height: 8px;">
                                    <?php if ($metrics['database']['status'] === 'good'): ?>
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= ($metrics['database']['connections'] / 100) * 100 ?>%" aria-valuenow="<?= ($metrics['database']['connections'] / 100) * 100 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    <?php elseif ($metrics['database']['status'] === 'warning'): ?>
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= ($metrics['database']['connections'] / 100) * 100 ?>%" aria-valuenow="<?= ($metrics['database']['connections'] / 100) * 100 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    <?php else: ?>
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?= ($metrics['database']['connections'] / 100) * 100 ?>%" aria-valuenow="<?= ($metrics['database']['connections'] / 100) * 100 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted mt-1 d-block">最大连接数: 100</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- 系统信息 -->
                    <div class="col-xl-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">系统信息</h5>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="刷新信息">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="system-info-item">
                                    <strong>服务器软件：</strong> <?= $systemInfo['serverSoftware'] ?>
                                </div>
                                <div class="system-info-item">
                                    <strong>PHP版本：</strong> <?= $systemInfo['phpVersion'] ?>
                                </div>
                                <div class="system-info-item">
                                    <strong>MySQL版本：</strong> <?= $systemInfo['mysqlVersion'] ?>
                                </div>
                                <div class="system-info-item">
                                    <strong>操作系统：</strong> <?= $systemInfo['operatingSystem'] ?>
                                </div>
                                <div class="system-info-item">
                                    <strong>内存使用：</strong> <?= $systemInfo['memoryUsage'] ?>
                                </div>
                                <div class="system-info-item">
                                    <strong>磁盘空间：</strong> 已用 <?= $metrics['disk']['used'] ?> / 可用 <?= $systemInfo['diskFreeSpace'] ?> / 总计 <?= $systemInfo['diskTotalSpace'] ?>
                                </div>
                                <div class="system-info-item">
                                    <strong>服务器时间：</strong> <?= $systemInfo['serverTime'] ?>
                                </div>
                                <div class="system-info-item">
                                    <strong>时区：</strong> <?= $systemInfo['timeZone'] ?>
                                </div>
                            </div>
                            <div class="card-footer text-muted">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>系统环境配置</span>
                                    <a href="/admin/system" class="btn btn-sm btn-primary">查看详情</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 最近的日志 -->
                    <div class="col-xl-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">最近日志</h5>
                                <a href="/admin/logs" class="btn btn-sm btn-outline-primary">查看所有</a>
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    <?php if (empty($recentLogs)): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                            <span class="text-muted">暂无日志记录</span>
                                        </li>
                                    <?php else: ?>
                                        <?php foreach ($recentLogs as $log): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                                <div>
                                                    <?php if ($log['type'] === 'error'): ?>
                                                        <i class="bi bi-exclamation-circle-fill text-danger me-2"></i>
                                                    <?php elseif ($log['type'] === 'warning'): ?>
                                                        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-info-circle-fill text-info me-2"></i>
                                                    <?php endif; ?>
                                                    <span><?= $log['name'] ?></span>
                                                    <small class="text-muted ms-2">(<?= $log['size'] ?>)</small>
                                                    <br>
                                                    <small class="text-muted"><?= $log['modified'] ?></small>
                                                </div>
                                                <button class="btn btn-sm btn-outline-secondary view-log-btn" data-bs-toggle="modal" data-bs-target="#logModal" data-log-content="<?= htmlspecialchars($log['content']) ?>" data-log-name="<?= htmlspecialchars($log['name']) ?>">查看</button>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- 工具统计 -->
                    <div class="col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">运维工具</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <h6>工具分类</h6>
                                    <canvas id="toolsChart" width="100%" height="150"></canvas>
                                </div>
                                
                                <div class="mb-3">
                                    <h6>最近使用的工具</h6>
                                    <ul class="list-group list-group-flush">
                                        <?php if (empty($toolsStats['recentlyUsed'])): ?>
                                            <li class="list-group-item px-0">
                                                <span class="text-muted">暂无工具使用记录</span>
                                            </li>
                                        <?php else: ?>
                                            <?php foreach ($toolsStats['recentlyUsed'] as $tool): ?>
                                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-tools me-2"></i>
                                                        <?= $tool['name'] ?>
                                                    </div>
                                                    <small class="text-muted"><?= $tool['lastUsed'] ?></small>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-footer text-muted">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>总共 <?= $toolsStats['totalTools'] ?> 个工具</span>
                                    <a href="/admin/tools" class="btn btn-sm btn-primary">查看工具</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 最近登录用户 -->
                    <div class="col-lg-8 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">最近登录</h5>
                                <a href="/admin/users" class="btn btn-sm btn-outline-primary">管理用户</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>用户名</th>
                                                <th>角色</th>
                                                <th>IP地址</th>
                                                <th>登录时间</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($recentUsers)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-3">暂无登录记录</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($recentUsers as $user): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name'] ?? $user['username']) ?>&background=random" alt="用户头像" width="32" height="32" class="rounded-circle me-2">
                                                                <div>
                                                                    <div><?= $user['name'] ?? $user['username'] ?></div>
                                                                    <small class="text-muted"><?= $user['email'] ?></small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php if ($user['role'] === 'admin'): ?>
                                                                <span class="badge bg-danger">管理员</span>
                                                            <?php elseif ($user['role'] === 'operator'): ?>
                                                                <span class="badge bg-primary">运维</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">用户</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= $user['ip_address'] ?></td>
                                                        <td><?= $user['created_at'] ?></td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="/admin/users/edit/<?= $user['id'] ?>" class="btn btn-outline-primary btn-sm">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                                <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="tooltip" title="查看详情">
                                                                    <i class="bi bi-eye"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
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
    
    <!-- 日志详情模态框 -->
    <div class="modal fade" id="logModal" tabindex="-1" aria-labelledby="logModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logModalLabel">日志内容</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
                </div>
                <div class="modal-body">
                    <pre class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code id="logContent"></code></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                    <button type="button" class="btn btn-primary" id="downloadLog">下载日志</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 工具分类图表
            const toolsChart = new Chart(
                document.getElementById('toolsChart'),
                {
                    type: 'doughnut',
                    data: {
                        labels: ['修复工具', '检查工具', '验证工具', '其他工具'],
                        datasets: [{
                            data: [
                                <?= $toolsStats['categories']['fix'] ?? 0 ?>,
                                <?= $toolsStats['categories']['check'] ?? 0 ?>,
                                <?= $toolsStats['categories']['validate'] ?? 0 ?>,
                                <?= $toolsStats['categories']['other'] ?? 0 ?>
                            ],
                            backgroundColor: ['#0d6efd', '#ffc107', '#20c997', '#6c757d']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12
                                }
                            }
                        },
                        cutout: '70%'
                    }
                }
            );
            
            // 日志查看功能
            const logModal = document.getElementById('logModal');
            const logContent = document.getElementById('logContent');
            const logModalLabel = document.getElementById('logModalLabel');
            const downloadLogBtn = document.getElementById('downloadLog');
            let currentLogName = '';
            
            document.querySelectorAll('.view-log-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const content = this.getAttribute('data-log-content');
                    currentLogName = this.getAttribute('data-log-name');
                    
                    logModalLabel.textContent = `日志内容: ${currentLogName}`;
                    logContent.textContent = content || '日志内容为空';
                });
            });
            
            // 下载日志
            downloadLogBtn.addEventListener('click', function() {
                const content = logContent.textContent;
                const blob = new Blob([content], { type: 'text/plain' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = currentLogName;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            });
            
            // 初始化工具提示
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html> 