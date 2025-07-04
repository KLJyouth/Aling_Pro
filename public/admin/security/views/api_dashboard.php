<?php
/**
 * API安全监控仪表盘视图
 * @version 1.0.0
 * @author AlingAi Team
 */

// 确保已经通过控制器调用
if (!isset($data)) {
    die("禁止直接访问");
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API安全监控仪表盘 - AlingAi Pro</title>
    <link rel="stylesheet" href="/admin/css/bootstrap.min.css">
    <link rel="stylesheet" href="/admin/css/admin.css">
    <style>
        .status-normal { color: #28a745; }
        .status-warning { color: #ffc107; }
        .status-danger { color: #dc3545; }
        .card-api { border-left: 4px solid #007bff; }
        .api-header { background-color: #f8f9fa; border-bottom: 1px solid #eee; }
        .threat-item { border-left: 3px solid #dc3545; padding-left: 10px; margin-bottom: 10px; }
        .vulnerability-item { border-left: 3px solid #ffc107; padding-left: 10px; margin-bottom: 10px; }
        .endpoint-badge {
            font-size: 0.7rem;
            padding: 3px 6px;
            margin-right: 5px;
        }
        .badge-get { background-color: #28a745; color: white; }
        .badge-post { background-color: #007bff; color: white; }
        .badge-put { background-color: #fd7e14; color: white; }
        .badge-delete { background-color: #dc3545; color: white; }
        .badge-patch { background-color: #6f42c1; color: white; }
        .badge-system { background-color: #6c757d; color: white; }
        .badge-local { background-color: #17a2b8; color: white; }
        .badge-user { background-color: #28a745; color: white; }
        .badge-external { background-color: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 mb-4">
                <h2>API安全监控</h2>
                <p class="text-muted">监控系统、本地和用户生成的API的安全状态</p>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card card-api">
                    <div class="card-header api-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">API端点概览</h5>
                        <div>
                            <button id="add-endpoint-btn" class="btn btn-sm btn-outline-primary me-2">添加端点</button>
                            <button id="scan-button" class="btn btn-sm btn-primary">执行扫描</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary filter-btn active" data-category="all">全部</button>
                                <button type="button" class="btn btn-outline-secondary filter-btn" data-category="system">系统</button>
                                <button type="button" class="btn btn-outline-secondary filter-btn" data-category="local">本地</button>
                                <button type="button" class="btn btn-outline-secondary filter-btn" data-category="user">用户</button>
                                <button type="button" class="btn btn-outline-secondary filter-btn" data-category="external">外部</button>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>端点</th>
                                        <th>方法</th>
                                        <th>类别</th>
                                        <th>描述</th>
                                        <th>认证</th>
                                        <th>速率限制</th>
                                        <th>最后检查</th>
                                    </tr>
                                </thead>
                                <tbody id="endpoints-table">
                                    <?php foreach ($data['endpoints'] as $endpoint): ?>
                                        <tr data-category="<?= htmlspecialchars($endpoint['category']) ?>">
                                            <td><?= htmlspecialchars($endpoint['endpoint']) ?></td>
                                            <td>
                                                <span class="badge endpoint-badge badge-<?= strtolower(htmlspecialchars($endpoint['method'])) ?>">
                                                    <?= htmlspecialchars($endpoint['method']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge endpoint-badge badge-<?= htmlspecialchars($endpoint['category']) ?>">
                                                    <?= htmlspecialchars($endpoint['category']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($endpoint['description']) ?></td>
                                            <td>
                                                <?php if ($endpoint['authentication_required']): ?>
                                                    <i class="fas fa-check text-success"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times text-danger"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($endpoint['rate_limited']): ?>
                                                    <i class="fas fa-check text-success"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times text-danger"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($endpoint['last_checked'] ?? '未检查') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card card-api h-100">
                    <div class="card-header api-header">
                        <h5 class="mb-0">最近威胁</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($data['threats'])): ?>
                            <p class="text-muted">暂无威胁记录</p>
                        <?php else: ?>
                            <div class="threat-list">
                                <?php foreach ($data['threats'] as $threat): ?>
                                    <div class="threat-item">
                                        <h6><?= htmlspecialchars($threat['threat_type']) ?> 
                                            <span class="badge badge-<?= $threat['severity'] === 'high' ? 'danger' : ($threat['severity'] === 'medium' ? 'warning' : 'info') ?>">
                                                <?= htmlspecialchars($threat['severity']) ?>
                                            </span>
                                        </h6>
                                        <p><?= htmlspecialchars($threat['description']) ?></p>
                                        <small class="text-muted">
                                            IP: <?= htmlspecialchars($threat['ip_address']) ?> | 
                                            时间: <?= htmlspecialchars($threat['created_at']) ?> |
                                            <?php if ($threat['blocked']): ?>
                                                <span class="badge badge-danger">已阻止</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">未阻止</span>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card card-api h-100">
                    <div class="card-header api-header">
                        <h5 class="mb-0">API漏洞</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($data['vulnerabilities'])): ?>
                            <p class="text-muted">暂无漏洞记录</p>
                        <?php else: ?>
                            <div class="vulnerability-list">
                                <?php foreach ($data['vulnerabilities'] as $vuln): ?>
                                    <div class="vulnerability-item">
                                        <h6><?= htmlspecialchars($vuln['vulnerability_type']) ?> 
                                            <span class="badge badge-<?= $vuln['severity'] === 'high' ? 'danger' : ($vuln['severity'] === 'medium' ? 'warning' : 'info') ?>">
                                                <?= htmlspecialchars($vuln['severity']) ?>
                                            </span>
                                        </h6>
                                        <p><?= htmlspecialchars($vuln['description']) ?></p>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">
                                                发现时间: <?= htmlspecialchars($vuln['discovered_at']) ?> |
                                                状态: 
                                                <span class="badge badge-<?= $vuln['status'] === 'open' ? 'danger' : ($vuln['status'] === 'in_progress' ? 'warning' : 'success') ?>">
                                                    <?= $vuln['status'] === 'open' ? '未修复' : ($vuln['status'] === 'in_progress' ? '修复中' : '已修复') ?>
                                                </span>
                                            </small>
                                            <?php if ($vuln['status'] === 'open'): ?>
                                                <button class="btn btn-sm btn-outline-success fix-vulnerability-btn" data-vuln-id="<?= $vuln['id'] ?>">标记为已修复</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card card-api">
                    <div class="card-header api-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">API访问统计</h5>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-secondary period-selector" data-period="day">今日</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary period-selector" data-period="week">本周</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary period-selector" data-period="month">本月</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <canvas id="api-access-chart" height="250"></canvas>
                            </div>
                            <div class="col-md-4">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>端点</th>
                                                <th>调用次数</th>
                                                <th>平均响应时间</th>
                                            </tr>
                                        </thead>
                                        <tbody id="api-stats-body">
                                            <?php if (!empty($data['stats'])): ?>
                                                <?php foreach ($data['stats'] as $stat): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($stat['endpoint']) ?></td>
                                                        <td><?= htmlspecialchars($stat['count']) ?></td>
                                                        <td><?= htmlspecialchars(number_format($stat['avg_response_time'] * 1000, 2)) ?> ms</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3" class="text-center">暂无数据</td>
                                                </tr>
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

    <!-- 添加API端点模态框 -->
    <div class="modal fade" id="add-endpoint-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">添加API端点</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
                </div>
                <div class="modal-body">
                    <form id="add-endpoint-form">
                        <div class="mb-3">
                            <label for="endpoint" class="form-label">端点路径</label>
                            <input type="text" class="form-control" id="endpoint" name="endpoint" required>
                            <div class="form-text">例如: /api/v1/users</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="method" class="form-label">HTTP方法</label>
                            <select class="form-select" id="method" name="method" required>
                                <option value="GET">GET</option>
                                <option value="POST">POST</option>
                                <option value="PUT">PUT</option>
                                <option value="DELETE">DELETE</option>
                                <option value="PATCH">PATCH</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category" class="form-label">API类别</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="system">系统</option>
                                <option value="local">本地</option>
                                <option value="user" selected>用户</option>
                                <option value="external">外部</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">描述</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="auth_required" name="auth_required" checked>
                            <label class="form-check-label" for="auth_required">需要认证</label>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="rate_limited" name="rate_limited" checked>
                            <label class="form-check-label" for="rate_limited">启用速率限制</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="save-endpoint-btn">保存</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/admin/js/jquery.min.js"></script>
    <script src="/admin/js/bootstrap.bundle.min.js"></script>
    <script src="/admin/js/chart.min.js"></script>
    <script>
        $(document).ready(function() {
            // 端点过滤
            $('.filter-btn').click(function() {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                
                const category = $(this).data('category');
                if (category === 'all') {
                    $('#endpoints-table tr').show();
                } else {
                    $('#endpoints-table tr').hide();
                    $('#endpoints-table tr[data-category="' + category + '"]').show();
                }
            });
            
            // 执行扫描
            $('#scan-button').click(function() {
                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 扫描中...');
                
                $.ajax({
                    url: '?module=api&action=scan',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        alert('扫描完成！发现 ' + data.vulnerabilities_found + ' 个漏洞，' + data.threats_detected + ' 个威胁。');
                        location.reload();
                    },
                    error: function() {
                        alert('扫描失败，请稍后重试。');
                    },
                    complete: function() {
                        $('#scan-button').prop('disabled', false).text('执行扫描');
                    }
                });
            });
            
            // 添加端点
            $('#add-endpoint-btn').click(function() {
                $('#add-endpoint-modal').modal('show');
            });
            
            // 保存端点
            $('#save-endpoint-btn').click(function() {
                const form = $('#add-endpoint-form');
                
                $.ajax({
                    url: '?module=api&action=add_endpoint',
                    method: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(data) {
                        if (data.success) {
                            alert('API端点添加成功！');
                            location.reload();
                        } else {
                            alert('添加失败: ' + data.message);
                        }
                    },
                    error: function() {
                        alert('请求失败，请稍后重试。');
                    }
                });
            });
            
            // 修复漏洞
            $('.fix-vulnerability-btn').click(function() {
                const vulnId = $(this).data('vuln-id');
                
                $.ajax({
                    url: '?module=api&action=fix_vulnerability',
                    method: 'POST',
                    data: { vulnerability_id: vulnId },
                    dataType: 'json',
                    success: function(data) {
                        if (data.success) {
                            alert('漏洞已标记为已修复！');
                            location.reload();
                        } else {
                            alert('操作失败: ' + data.message);
                        }
                    },
                    error: function() {
                        alert('请求失败，请稍后重试。');
                    }
                });
            });
            
            // 切换统计周期
            $('.period-selector').click(function() {
                $('.period-selector').removeClass('active');
                $(this).addClass('active');
                
                const period = $(this).data('period');
                loadStats(period);
            });
            
            // 加载统计数据
            function loadStats(period) {
                $.ajax({
                    url: '?module=api&action=stats',
                    method: 'GET',
                    data: { period: period },
                    dataType: 'json',
                    success: function(data) {
                        updateStatsTable(data.stats);
                        updateStatsChart(data.stats);
                    },
                    error: function() {
                        console.error('加载统计数据失败');
                    }
                });
            }
            
            // 更新统计表格
            function updateStatsTable(stats) {
                const tbody = $('#api-stats-body');
                tbody.empty();
                
                if (stats.length === 0) {
                    tbody.append('<tr><td colspan="3" class="text-center">暂无数据</td></tr>');
                    return;
                }
                
                stats.forEach(function(stat) {
                    tbody.append(`
                        <tr>
                            <td>${stat.endpoint}</td>
                            <td>${stat.count}</td>
                            <td>${(stat.avg_response_time * 1000).toFixed(2)} ms</td>
                        </tr>
                    `);
                });
            }
            
            // 更新统计图表
            function updateStatsChart(stats) {
                const ctx = document.getElementById('api-access-chart').getContext('2d');
                
                if (window.apiChart) {
                    window.apiChart.destroy();
                }
                
                const labels = stats.map(s => s.endpoint);
                const counts = stats.map(s => s.count);
                const times = stats.map(s => s.avg_response_time * 1000);
                
                window.apiChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'API调用次数',
                                data: counts,
                                backgroundColor: 'rgba(0, 123, 255, 0.5)',
                                borderColor: 'rgba(0, 123, 255, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
            // 初始化图表
            if ($('#api-access-chart').length) {
                updateStatsChart(<?= json_encode($data['stats'] ?? []) ?>);
            }
        });
    </script>
</body>
</html>
