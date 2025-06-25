<?php // Logs Index View

include_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mt-4">日志管理中心</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">首页</a></li>
                <li class="breadcrumb-item active">日志管理</li>
            </ol>
            
            <!-- 日志概览 -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <h2><?= $logsOverview['totalLogFiles'] ?></h2>
                            <div>日志文件总数</div>
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
                            <h2><?= $logsOverview['totalSize'] ?></h2>
                            <div>日志总大小</div>
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
                            <h2><?= $logsOverview['todayLogs'] ?></h2>
                            <div>今日日志</div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">查看详情</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-danger text-white mb-4">
                        <div class="card-body">
                            <h2><?= $logsOverview['errorLogs'] ?></h2>
                            <div>错误日志</div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">查看详情</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 日志类型导航 -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-file-alt mr-1"></i>
                            日志类型
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/logs/system" class="btn btn-outline-primary btn-lg btn-block">
                                        <i class="fas fa-server fa-2x mb-2"></i><br>
                                        系统日志
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/logs/errors" class="btn btn-outline-danger btn-lg btn-block">
                                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>
                                        错误日志
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/logs/access" class="btn btn-outline-success btn-lg btn-block">
                                        <i class="fas fa-users fa-2x mb-2"></i><br>
                                        访问日志
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/logs/security" class="btn btn-outline-warning btn-lg btn-block">
                                        <i class="fas fa-shield-alt fa-2x mb-2"></i><br>
                                        安全日志
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 日志搜索 -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-search mr-1"></i>
                    日志搜索
                </div>
                <div class="card-body">
                    <form id="logSearchForm">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="keyword">关键词</label>
                                    <input type="text" class="form-control" id="keyword" name="keyword" placeholder="请输入关键词">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="logType">日志类型</label>
                                    <select class="form-control" id="logType" name="log_type">
                                        <option value="all">全部</option>
                                        <option value="system">系统日志</option>
                                        <option value="error">错误日志</option>
                                        <option value="access">访问日志</option>
                                        <option value="security">安全日志</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="startDate">开始日期</label>
                                    <input type="date" class="form-control" id="startDate" name="start_date" value="<?= date('Y-m-d', strtotime('-7 days')) ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="endDate">结束日期</label>
                                    <input type="date" class="form-control" id="endDate" name="end_date" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="searchBtn">
                            <i class="fas fa-search mr-1"></i> 搜索
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- 搜索结果 -->
            <div class="card mb-4" id="searchResultsCard" style="display: none;">
                <div class="card-header">
                    <i class="fas fa-table mr-1"></i>
                    搜索结果
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="searchResultsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>文件</th>
                                    <th>行号</th>
                                    <th>时间戳</th>
                                    <th>内容</th>
                                </tr>
                            </thead>
                            <tbody id="searchResultsBody">
                                <!-- 搜索结果将通过JavaScript动态添加 -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- 最近日志文件 -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-file-alt mr-1"></i>
                    最近日志文件
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="recentLogsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>文件名</th>
                                    <th>类型</th>
                                    <th>大小</th>
                                    <th>行数</th>
                                    <th>修改时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentLogs as $log): ?>
                                <tr>
                                    <td><?= $log['name'] ?></td>
                                    <td>
                                        <?php 
                                        switch ($log['type']) {
                                            case 'system':
                                                echo '<span class="badge badge-primary">系统日志</span>';
                                                break;
                                            case 'error':
                                                echo '<span class="badge badge-danger">错误日志</span>';
                                                break;
                                            case 'access':
                                                echo '<span class="badge badge-success">访问日志</span>';
                                                break;
                                            case 'security':
                                                echo '<span class="badge badge-warning">安全日志</span>';
                                                break;
                                            default:
                                                echo '<span class="badge badge-secondary">其他日志</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?= $log['size'] ?></td>
                                    <td><?= $log['lineCount'] ?></td>
                                    <td><?= $log['modifiedTime'] ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= BASE_URL ?>/logs/<?= $log['type'] ?>?file=<?= $log['name'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> 查看
                                            </a>
                                            <a href="<?= BASE_URL ?>/logs/download?file=<?= $log['name'] ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-download"></i> 下载
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger clear-log" data-file="<?= $log['name'] ?>">
                                                <i class="fas fa-trash"></i> 清空
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

<!-- 清空日志确认模态框 -->
<div class="modal fade" id="clearLogModal" tabindex="-1" role="dialog" aria-labelledby="clearLogModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clearLogModalLabel">清空日志确认</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>您确定要清空此日志文件吗？此操作无法撤销。</p>
                <p>文件名: <span id="clearLogFileName"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" id="confirmClearBtn">确认清空</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // 初始化数据表格
        $('#recentLogsTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Chinese.json"
            }
        }];
        
        // 日志搜索
        $('#logSearchForm').on('submit', function(e) {
            e.preventDefault(];
            
            var formData = $(this).serialize(];
            
            $('#searchBtn').html('<i class="fas fa-spinner fa-spin mr-1"></i> 搜索中...').attr('disabled', true];
            
            // 发送AJAX请求
            $.ajax({
                url: '<?= BASE_URL ?>/logs/search',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#searchBtn').html('<i class="fas fa-search mr-1"></i> 搜索').attr('disabled', false];
                    
                    if (response.success) {
                        // 显示搜索结果
                        displaySearchResults(response.results];
                    } else {
                        alert('搜索失败: ' + response.message];
                    }
                },
                error: function() {
                    $('#searchBtn').html('<i class="fas fa-search mr-1"></i> 搜索').attr('disabled', false];
                    alert('发生错误，请稍后重试'];
                }
            }];
        }];
        
        // 显示搜索结果
        function displaySearchResults(results) {
            var tbody = $('#searchResultsBody'];
            tbody.empty(];
            
            if (results.length === 0) {
                tbody.append('<tr><td colspan="4" class="text-center">没有找到匹配的结果</td></tr>'];
            } else {
                $.each(results, function(index, result) {
                    var row = '<tr>' +
                        '<td>' + result.file + '</td>' +
                        '<td>' + result.line + '</td>' +
                        '<td>' + (result.timestamp || '-') + '</td>' +
                        '<td>' + result.content + '</td>' +
                        '</tr>';
                    tbody.append(row];
                }];
            }
            
            // 显示结果卡片
            $('#searchResultsCard').show(];
            
            // 初始化搜索结果表格
            if ($.fn.dataTable.isDataTable('#searchResultsTable')) {
                $('#searchResultsTable').DataTable().destroy(];
            }
            
            $('#searchResultsTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Chinese.json"
                }
            }];
        }
        
        // 清空日志
        var fileToDelete;
        
        $('.clear-log').on('click', function() {
            fileToDelete = $(this).data('file'];
            $('#clearLogFileName').text(fileToDelete];
            $('#clearLogModal').modal('show'];
        }];
        
        $('#confirmClearBtn').on('click', function() {
            // 发送AJAX请求
            $.ajax({
                url: '<?= BASE_URL ?>/logs/clear',
                type: 'POST',
                data: {
                    file: fileToDelete
                },
                dataType: 'json',
                success: function(response) {
                    $('#clearLogModal').modal('hide'];
                    
                    if (response.success) {
                        // 显示成功消息
                        alert('日志文件已清空'];
                        // 刷新页面
                        window.location.reload(];
                    } else {
                        alert('清空日志文件失败: ' + response.message];
                    }
                },
                error: function() {
                    $('#clearLogModal').modal('hide'];
                    alert('发生错误，请稍后重试'];
                }
            }];
        }];
    }];
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
