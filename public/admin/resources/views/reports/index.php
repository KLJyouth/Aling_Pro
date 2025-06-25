<?php // Reports Index View

include_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mt-4">运维报告中心</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">首页</a></li>
                <li class="breadcrumb-item active">运维报告</li>
            </ol>
            
            <!-- 报告概览 -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <h2><?= $reportsOverview['totalReports'] ?></h2>
                            <div>总报告数</div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">查看详情</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">
                            <h2><?= $reportsOverview['generatedToday'] ?></h2>
                            <div>今日生成</div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">查看详情</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-white mb-4">
                        <div class="card-body">
                            <h2><?= $reportsOverview['scheduledReports'] ?></h2>
                            <div>计划报告</div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">查看详情</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-info text-white mb-4">
                        <div class="card-body">
                            <h2><?= $reportsOverview['customReports'] ?></h2>
                            <div>自定义报告</div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">查看详情</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 报告类型导航 -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-chart-bar mr-1"></i>
                            报告类型
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/reports/performance" class="btn btn-outline-primary btn-lg btn-block">
                                        <i class="fas fa-tachometer-alt fa-2x mb-2"></i><br>
                                        系统性能报告
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/reports/security" class="btn btn-outline-success btn-lg btn-block">
                                        <i class="fas fa-shield-alt fa-2x mb-2"></i><br>
                                        安全审计报告
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/reports/errors" class="btn btn-outline-danger btn-lg btn-block">
                                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>
                                        错误统计报告
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button type="button" class="btn btn-outline-info btn-lg btn-block" data-toggle="modal" data-target="#customReportModal">
                                        <i class="fas fa-file-alt fa-2x mb-2"></i><br>
                                        生成自定义报告
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 最近报告 -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table mr-1"></i>
                    最近报告
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="reportsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>名称</th>
                                    <th>类型</th>
                                    <th>大小</th>
                                    <th>创建时间</th>
                                    <th>状态</th>
                                    <th>创建者</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentReports as $report): ?>
                                <tr>
                                    <td><?= $report['id'] ?></td>
                                    <td><?= $report['name'] ?></td>
                                    <td>
                                        <?php 
                                        switch ($report['type']) {
                                            case 'performance':
                                                echo '<span class="badge badge-primary">性能报告</span>';
                                                break;
                                            case 'security':
                                                echo '<span class="badge badge-success">安全报告</span>';
                                                break;
                                            case 'errors':
                                                echo '<span class="badge badge-danger">错误报告</span>';
                                                break;
                                            case 'usage':
                                                echo '<span class="badge badge-info">使用情况报告</span>';
                                                break;
                                            case 'custom':
                                                echo '<span class="badge badge-warning">自定义报告</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td><?= $report['size'] ?></td>
                                    <td><?= $report['date'] ?></td>
                                    <td>
                                        <?php 
                                        switch ($report['status']) {
                                            case 'completed':
                                                echo '<span class="badge badge-success">完成</span>';
                                                break;
                                            case 'scheduled':
                                                echo '<span class="badge badge-warning">计划中</span>';
                                                break;
                                            case 'failed':
                                                echo '<span class="badge badge-danger">失败</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td><?= $report['creator'] ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-primary view-report" data-id="<?= $report['id'] ?>">
                                                <i class="fas fa-eye"></i> 查看
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success export-report" data-id="<?= $report['id'] ?>">
                                                <i class="fas fa-file-export"></i> 导出
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> 删除
                                            </button>
                                        </div>
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

<!-- 自定义报告模态框 -->
<div class="modal fade" id="customReportModal" tabindex="-1" role="dialog" aria-labelledby="customReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customReportModalLabel">生成自定义报告</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="customReportForm">
                    <div class="form-group">
                        <label for="reportType">报告类型</label>
                        <select class="form-control" id="reportType" name="report_type">
                            <option value="performance">系统性能报告</option>
                            <option value="security">安全审计报告</option>
                            <option value="errors">错误统计报告</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="startDate">开始日期</label>
                                <input type="date" class="form-control" id="startDate" name="start_date" value="<?= date('Y-m-d', strtotime('-30 days')) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="endDate">结束日期</label>
                                <input type="date" class="form-control" id="endDate" name="end_date" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>附加选项</label>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="includeCharts" name="filters[include_charts]" checked>
                            <label class="custom-control-label" for="includeCharts">包含图表</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="includeDetails" name="filters[include_details]" checked>
                            <label class="custom-control-label" for="includeDetails">包含详细数据</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="includeRecommendations" name="filters[include_recommendations]">
                            <label class="custom-control-label" for="includeRecommendations">包含优化建议</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="generateReportBtn">生成报告</button>
            </div>
        </div>
    </div>
</div>

<!-- 导出报告模态框 -->
<div class="modal fade" id="exportReportModal" tabindex="-1" role="dialog" aria-labelledby="exportReportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportReportModalLabel">导出报告</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="exportReportForm">
                    <input type="hidden" id="exportReportId" name="report_id">
                    <div class="form-group">
                        <label for="exportFormat">导出格式</label>
                        <select class="form-control" id="exportFormat" name="format">
                            <option value="pdf">PDF</option>
                            <option value="xlsx">Excel</option>
                            <option value="csv">CSV</option>
                            <option value="html">HTML</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="confirmExportBtn">导出</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // 初始化数据表格
        $('#reportsTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Chinese.json"
            }
        }];
        
        // 生成自定义报告
        $('#generateReportBtn').on('click', function() {
            var formData = $('#customReportForm').serialize(];
            
            $('#generateReportBtn').html('<i class="fas fa-spinner fa-spin mr-1"></i> 生成中...').attr('disabled', true];
            
            // 发送AJAX请求
            $.ajax({
                url: '<?= BASE_URL ?>/reports/generate',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#customReportModal').modal('hide'];
                    $('#generateReportBtn').html('生成报告').attr('disabled', false];
                    
                    if (response.success) {
                        // 显示成功消息
                        alert('报告生成成功'];
                        // 刷新页面
                        window.location.reload(];
                    } else {
                        alert('报告生成失败: ' + response.message];
                    }
                },
                error: function() {
                    $('#customReportModal').modal('hide'];
                    $('#generateReportBtn').html('生成报告').attr('disabled', false];
                    alert('发生错误，请稍后重试'];
                }
            }];
        }];
        
        // 导出报告
        $('.export-report').on('click', function() {
            var reportId = $(this).data('id'];
            $('#exportReportId').val(reportId];
            $('#exportReportModal').modal('show'];
        }];
        
        $('#confirmExportBtn').on('click', function() {
            var formData = $('#exportReportForm').serialize(];
            
            $('#confirmExportBtn').html('<i class="fas fa-spinner fa-spin mr-1"></i> 导出中...').attr('disabled', true];
            
            // 发送AJAX请求
            $.ajax({
                url: '<?= BASE_URL ?>/reports/export',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#exportReportModal').modal('hide'];
                    $('#confirmExportBtn').html('导出').attr('disabled', false];
                    
                    if (response.success) {
                        // 显示成功消息
                        alert('报告导出成功'];
                        // 打开下载链接
                        window.open(response.details.downloadUrl, '_blank'];
                    } else {
                        alert('报告导出失败: ' + response.message];
                    }
                },
                error: function() {
                    $('#exportReportModal').modal('hide'];
                    $('#confirmExportBtn').html('导出').attr('disabled', false];
                    alert('发生错误，请稍后重试'];
                }
            }];
        }];
        
        // 查看报告
        $('.view-report').on('click', function() {
            var reportId = $(this).data('id'];
            var reportType = $(this).closest('tr').find('td:eq(2)').text().trim(];
            
            // 根据报告类型跳转到不同页面
            if (reportType.includes('性能')) {
                window.location.href = '<?= BASE_URL ?>/reports/performance?id=' + reportId;
            } else if (reportType.includes('安全')) {
                window.location.href = '<?= BASE_URL ?>/reports/security?id=' + reportId;
            } else if (reportType.includes('错误')) {
                window.location.href = '<?= BASE_URL ?>/reports/errors?id=' + reportId;
            } else {
                alert('无法查看此类型报告'];
            }
        }];
    }];
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
