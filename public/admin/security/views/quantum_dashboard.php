<?php
/**
 * 量子加密监控仪表盘视图
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
    <title>量子加密监控仪表盘 - AlingAi Pro</title>
    <link rel="stylesheet" href="/admin/css/bootstrap.min.css">
    <link rel="stylesheet" href="/admin/css/admin.css">
    <style>
        .status-normal { color: #28a745; }
        .status-warning { color: #ffc107; }
        .status-danger { color: #dc3545; }
        .card-quantum { border-left: 4px solid #6f42c1; }
        .quantum-header { background-color: #f8f9fa; border-bottom: 1px solid #eee; }
        .component-card { height: 100%; }
        .alert-item { border-left: 3px solid #dc3545; padding-left: 10px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 mb-4">
                <h2>量子加密系统监控</h2>
                <p class="text-muted">监控stanfai量子加密系统的安全状态</p>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card card-quantum">
                    <div class="card-header quantum-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">系统状态概览</h5>
                        <button id="scan-button" class="btn btn-sm btn-primary">执行扫描</button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($data['encryption_status'] as $status): ?>
                                <div class="col-md-3 mb-3">
                                    <div class="card component-card">
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status['component']))) ?></h6>
                                            <p class="card-text">
                                                <span class="status-<?= $status['status'] === '正常' ? 'normal' : ($status['status'] === '警告' ? 'warning' : 'danger') ?>">
                                                    <i class="fas fa-circle"></i> <?= htmlspecialchars($status['status']) ?>
                                                </span>
                                            </p>
                                            <small class="text-muted"><?= htmlspecialchars($status['details']) ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card card-quantum h-100">
                    <div class="card-header quantum-header">
                        <h5 class="mb-0">量子密钥状态</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($data['key_status'])): ?>
                            <p class="text-muted">暂无密钥分发记录</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>会话ID</th>
                                            <th>密钥大小</th>
                                            <th>协议</th>
                                            <th>状态</th>
                                            <th>错误率</th>
                                            <th>入侵检测</th>
                                            <th>创建时间</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['key_status'] as $key): ?>
                                            <tr>
                                                <td><?= htmlspecialchars(substr($key['session_id'], 0, 8) . '...') ?></td>
                                                <td><?= htmlspecialchars($key['key_size']) ?></td>
                                                <td><?= htmlspecialchars($key['protocol']) ?></td>
                                                <td>
                                                    <span class="status-<?= $key['status'] === '成功' ? 'normal' : 'danger' ?>">
                                                        <?= htmlspecialchars($key['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($key['error_rate'] ?? 'N/A') ?></td>
                                                <td>
                                                    <?php if ($key['intrusion_detected']): ?>
                                                        <span class="status-danger">检测到</span>
                                                    <?php else: ?>
                                                        <span class="status-normal">无</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($key['created_at']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card card-quantum h-100">
                    <div class="card-header quantum-header">
                        <h5 class="mb-0">最近警报</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($data['recent_alerts'])): ?>
                            <p class="text-muted">暂无警报记录</p>
                        <?php else: ?>
                            <div class="alert-list">
                                <?php foreach ($data['recent_alerts'] as $alert): ?>
                                    <div class="alert-item">
                                        <h6><?= htmlspecialchars($alert['alert_type']) ?> 
                                            <span class="badge badge-<?= $alert['severity'] === '高' ? 'danger' : ($alert['severity'] === '中' ? 'warning' : 'info') ?>">
                                                <?= htmlspecialchars($alert['severity']) ?>
                                            </span>
                                        </h6>
                                        <p><?= htmlspecialchars($alert['description']) ?></p>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($alert['created_at']) ?>
                                            <?php if ($alert['resolved']): ?>
                                                <span class="badge badge-success">已解决</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">未解决</span>
                                            <?php endif; ?>
                                        </small>
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
                <div class="card card-quantum">
                    <div class="card-header quantum-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">量子加密使用统计</h5>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-secondary period-selector" data-period="day">今日</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary period-selector" data-period="week">本周</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary period-selector" data-period="month">本月</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($data['usage_stats'])): ?>
                            <p class="text-muted">暂无使用数据</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>服务</th>
                                            <th>操作</th>
                                            <th>算法</th>
                                            <th>调用次数</th>
                                            <th>总数据大小</th>
                                            <th>平均执行时间</th>
                                        </tr>
                                    </thead>
                                    <tbody id="usage-stats-body">
                                        <?php foreach ($data['usage_stats'] as $stat): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($stat['service']) ?></td>
                                                <td><?= htmlspecialchars($stat['operation']) ?></td>
                                                <td><?= htmlspecialchars($stat['algorithm']) ?></td>
                                                <td><?= htmlspecialchars($stat['count']) ?></td>
                                                <td><?= htmlspecialchars(number_format($stat['total_data_size'] / 1024, 2)) ?> KB</td>
                                                <td><?= htmlspecialchars(number_format($stat['avg_execution_time'] * 1000, 2)) ?> ms</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/admin/js/jquery.min.js"></script>
    <script src="/admin/js/bootstrap.bundle.min.js"></script>
    <script src="/admin/js/fontawesome.min.js"></script>
    <script>
        // 执行扫描
        $('#scan-button').on('click', function() {
            $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 扫描中...');
            
            $.ajax({
                url: '?action=scan',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    alert('扫描完成！发现 ' + response.components.length + ' 个组件状态。');
                    location.reload();
                },
                error: function() {
                    alert('扫描失败，请稍后再试。');
                },
                complete: function() {
                    $('#scan-button').prop('disabled', false).text('执行扫描');
                }
            });
        });
        
        // 切换时间段
        $('.period-selector').on('click', function() {
            var period = $(this).data('period');
            $('.period-selector').removeClass('active');
            $(this).addClass('active');
            
            $.ajax({
                url: '?action=stats',
                method: 'GET',
                data: { period: period },
                dataType: 'json',
                success: function(response) {
                    var html = '';
                    if (response.stats.length === 0) {
                        html = '<tr><td colspan="6" class="text-center">该时间段内无数据</td></tr>';
                    } else {
                        response.stats.forEach(function(stat) {
                            html += '<tr>' +
                                '<td>' + stat.service + '</td>' +
                                '<td>' + stat.operation + '</td>' +
                                '<td>' + stat.algorithm + '</td>' +
                                '<td>' + stat.count + '</td>' +
                                '<td>' + (stat.total_data_size / 1024).toFixed(2) + ' KB</td>' +
                                '<td>' + (stat.avg_execution_time * 1000).toFixed(2) + ' ms</td>' +
                                '</tr>';
                        });
                    }
                    $('#usage-stats-body').html(html);
                },
                error: function() {
                    alert('获取统计数据失败，请稍后再试。');
                }
            });
        });
        
        // 默认选中今日
        $('.period-selector[data-period="day"]').addClass('active');
    </script>
</body>
</html>
