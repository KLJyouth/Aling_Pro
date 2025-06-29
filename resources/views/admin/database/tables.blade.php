@extends("admin.layouts.app")

@section("title", "数据库表管理")

@section("content")
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>数据库表管理</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">首页</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.database.index') }}">数据库超级运维</a></li>
                <li class="breadcrumb-item active">数据库表管理</li>
            </ol>
        </div>
    </div>

    <!-- 表格过滤和搜索 -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">表格筛选</h3>
        </div>
        <div class="card-body">
            <form id="filter-form" class="form-inline">
                <div class="input-group mr-2">
                    <input type="text" class="form-control" id="table-search" placeholder="表名搜索...">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="table-search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                
                <select class="form-control mr-2" id="engine-filter">
                    <option value="">所有引擎</option>
                    <option value="InnoDB">InnoDB</option>
                    <option value="MyISAM">MyISAM</option>
                    <option value="MEMORY">MEMORY</option>
                </select>
                
                <select class="form-control mr-2" id="size-filter">
                    <option value="">所有大小</option>
                    <option value="small">小型表 (< 1MB)</option>
                    <option value="medium">中型表 (1-10MB)</option>
                    <option value="large">大型表 (> 10MB)</option>
                </select>
                
                <button type="button" class="btn btn-primary" id="apply-filter">应用筛选</button>
                <button type="button" class="btn btn-default ml-2" id="reset-filter">重置</button>
            </form>
        </div>
    </div>

    <!-- 表格列表 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">数据表列表</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-success btn-sm" id="optimize-selected">
                    <i class="fas fa-broom mr-1"></i> 优化选中表
                </button>
                <button type="button" class="btn btn-warning btn-sm ml-1" id="analyze-selected">
                    <i class="fas fa-chart-bar mr-1"></i> 分析选中表
                </button>
                <button type="button" class="btn btn-info btn-sm ml-1" id="backup-selected">
                    <i class="fas fa-download mr-1"></i> 备份选中表
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tables-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;">
                                <input type="checkbox" id="select-all">
                            </th>
                            <th>表名</th>
                            <th>引擎</th>
                            <th>行数</th>
                            <th>大小</th>
                            <th>数据长度</th>
                            <th>索引长度</th>
                            <th>自增值</th>
                            <th>校对规则</th>
                            <th>创建时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tables as $table)
                        <tr data-engine="{{ $table->Engine }}" data-size="{{ $table->size_mb }}">
                            <td>
                                <input type="checkbox" class="table-checkbox" value="{{ $table->Name }}">
                            </td>
                            <td>
                                <a href="{{ route('admin.database.table.detail', $table->Name) }}">
                                    {{ $table->Name }}
                                </a>
                                <small class="d-block text-muted">{{ $table->column_count }}个字段</small>
                            </td>
                            <td>{{ $table->Engine }}</td>
                            <td>{{ number_format($table->rows) }}</td>
                            <td>{{ number_format($table->size_mb, 2) }} MB</td>
                            <td>{{ number_format($table->Data_length / 1024 / 1024, 2) }} MB</td>
                            <td>{{ number_format($table->Index_length / 1024 / 1024, 2) }} MB</td>
                            <td>{{ $table->Auto_increment ?? '-' }}</td>
                            <td>{{ $table->Collation }}</td>
                            <td>{{ $table->Create_time ? date('Y-m-d H:i', strtotime($table->Create_time)) : '-' }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.database.table.detail', $table->Name) }}" class="btn btn-sm btn-info" title="查看详情">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-primary optimize-table" data-table="{{ $table->Name }}" title="优化表">
                                        <i class="fas fa-broom"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning analyze-table" data-table="{{ $table->Name }}" title="分析表">
                                        <i class="fas fa-chart-bar"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success backup-table" data-table="{{ $table->Name }}" title="备份表">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-md-6">
                    共 {{ count($tables) }} 个表，总大小：{{ number_format(array_sum(array_column($tables, 'size_mb')), 2) }} MB
                </div>
                <div class="col-md-6 text-right">
                    <button type="button" class="btn btn-danger" id="truncate-selected" disabled>
                        <i class="fas fa-trash mr-1"></i> 清空选中表
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 操作结果模态框 -->
<div class="modal fade" id="result-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">操作结果</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="result-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<!-- 备份表单模态框 -->
<div class="modal fade" id="backup-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">备份表</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="backup-form">
                    <div class="form-group">
                        <label for="backup-name">备份名称</label>
                        <input type="text" class="form-control" id="backup-name" name="backup_name" placeholder="例如：important_tables_backup">
                        <small class="form-text text-muted">如果不填写，将使用默认格式：backup_日期时间</small>
                    </div>
                    <div class="form-group">
                        <label>选中的表</label>
                        <div id="selected-tables-list" class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                            <div class="text-muted">未选择任何表</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="submit-backup">开始备份</button>
            </div>
        </div>
    </div>
</div>

<!-- 清空表确认模态框 -->
<div class="modal fade" id="truncate-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-danger">危险操作：清空表</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-1"></i> 警告：此操作将删除所选表中的所有数据，且无法恢复！
                </div>
                <p>您确定要清空以下表吗？</p>
                <div id="truncate-tables-list" class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                    <div class="text-muted">未选择任何表</div>
                </div>
                <div class="form-group mt-3">
                    <label for="truncate-confirm">请输入"CONFIRM"以确认操作</label>
                    <input type="text" class="form-control" id="truncate-confirm" placeholder="CONFIRM">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" id="submit-truncate" disabled>确认清空</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
    $(function () {
        // 表格排序和搜索初始化
        var tablesTable = $('#tables-table').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Chinese.json"
            }
        });
        
        // 搜索功能
        $('#table-search-btn').click(function() {
            tablesTable.search($('#table-search').val()).draw();
        });
        
        $('#table-search').keypress(function(e) {
            if (e.which === 13) {
                tablesTable.search($(this).val()).draw();
                e.preventDefault();
            }
        });
        
        // 筛选功能
        $('#apply-filter').click(function() {
            applyFilters();
        });
        
        $('#reset-filter').click(function() {
            $('#engine-filter').val('');
            $('#size-filter').val('');
            tablesTable.search('').draw();
        });
        
        function applyFilters() {
            var engineFilter = $('#engine-filter').val();
            var sizeFilter = $('#size-filter').val();
            
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var $row = $(tablesTable.row(dataIndex).node());
                var engine = $row.data('engine');
                var size = parseFloat($row.data('size'));
                
                var engineMatch = !engineFilter || engine === engineFilter;
                var sizeMatch = true;
                
                if (sizeFilter === 'small') {
                    sizeMatch = size < 1;
                } else if (sizeFilter === 'medium') {
                    sizeMatch = size >= 1 && size <= 10;
                } else if (sizeFilter === 'large') {
                    sizeMatch = size > 10;
                }
                
                return engineMatch && sizeMatch;
            });
            
            tablesTable.draw();
            
            // 清除自定义筛选器
            $.fn.dataTable.ext.search.pop();
        }
        
        // 全选/取消全选
        $('#select-all').click(function() {
            $('.table-checkbox').prop('checked', this.checked);
            updateSelectedButtons();
        });
        
        $(document).on('change', '.table-checkbox', function() {
            updateSelectedButtons();
            
            // 如果取消选中某个表，也取消全选框
            if (!this.checked) {
                $('#select-all').prop('checked', false);
            }
            
            // 如果所有表都被选中，勾选全选框
            if ($('.table-checkbox:checked').length === $('.table-checkbox').length) {
                $('#select-all').prop('checked', true);
            }
        });
        
        // 更新批量操作按钮状态
        function updateSelectedButtons() {
            var selectedCount = $('.table-checkbox:checked').length;
            $('#optimize-selected, #analyze-selected, #backup-selected').prop('disabled', selectedCount === 0);
            $('#truncate-selected').prop('disabled', selectedCount === 0);
        }
        
        // 优化单个表
        $('.optimize-table').click(function() {
            var tableName = $(this).data('table');
            optimizeTables([tableName]);
        });
        
        // 分析单个表
        $('.analyze-table').click(function() {
            var tableName = $(this).data('table');
            analyzeTables([tableName]);
        });
        
        // 备份单个表
        $('.backup-table').click(function() {
            var tableName = $(this).data('table');
            showBackupModal([tableName]);
        });
        
        // 优化选中表
        $('#optimize-selected').click(function() {
            var selectedTables = getSelectedTables();
            if (selectedTables.length > 0) {
                optimizeTables(selectedTables);
            }
        });
        
        // 分析选中表
        $('#analyze-selected').click(function() {
            var selectedTables = getSelectedTables();
            if (selectedTables.length > 0) {
                analyzeTables(selectedTables);
            }
        });
        
        // 备份选中表
        $('#backup-selected').click(function() {
            var selectedTables = getSelectedTables();
            if (selectedTables.length > 0) {
                showBackupModal(selectedTables);
            }
        });
        
        // 清空选中表
        $('#truncate-selected').click(function() {
            var selectedTables = getSelectedTables();
            if (selectedTables.length > 0) {
                showTruncateModal(selectedTables);
            }
        });
        
        // 获取选中的表
        function getSelectedTables() {
            var selectedTables = [];
            $('.table-checkbox:checked').each(function() {
                selectedTables.push($(this).val());
            });
            return selectedTables;
        }
        
        // 优化表
        function optimizeTables(tables) {
            $.ajax({
                url: '{{ route("admin.database.optimize") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    tables: tables
                },
                beforeSend: function() {
                    $('#result-content').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>优化中，请稍候...</p></div>');
                    $('#result-modal').modal('show');
                },
                success: function(response) {
                    if (response.success) {
                        var resultHtml = '<div class="alert alert-success">优化完成！</div>';
                        resultHtml += '<div class="mt-3"><h5>优化结果：</h5><ul>';
                        
                        for (var table in response.results) {
                            var result = response.results[table];
                            resultHtml += '<li>' + table + ': ' + 
                                (result.status === 'success' ? 
                                    '<span class="text-success">成功</span>' : 
                                    '<span class="text-danger">失败 - ' + result.message + '</span>') + 
                                '</li>';
                        }
                        
                        resultHtml += '</ul></div>';
                        $('#result-content').html(resultHtml);
                    } else {
                        $('#result-content').html('<div class="alert alert-danger">优化失败！</div>');
                    }
                },
                error: function(xhr) {
                    $('#result-content').html('<div class="alert alert-danger">优化失败：' + xhr.responseText + '</div>');
                }
            });
        }
        
        // 分析表
        function analyzeTables(tables) {
            $.ajax({
                url: '{{ route("admin.database.analyze") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    tables: tables
                },
                beforeSend: function() {
                    $('#result-content').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>分析中，请稍候...</p></div>');
                    $('#result-modal').modal('show');
                },
                success: function(response) {
                    if (response.success) {
                        var resultHtml = '<div class="alert alert-success">分析完成！</div>';
                        resultHtml += '<div class="mt-3"><h5>分析结果：</h5><ul>';
                        
                        for (var table in response.results) {
                            var result = response.results[table];
                            resultHtml += '<li>' + table + ': ' + 
                                (result.status === 'success' ? 
                                    '<span class="text-success">成功</span>' : 
                                    '<span class="text-danger">失败 - ' + result.message + '</span>') + 
                                '</li>';
                        }
                        
                        resultHtml += '</ul></div>';
                        $('#result-content').html(resultHtml);
                    } else {
                        $('#result-content').html('<div class="alert alert-danger">分析失败！</div>');
                    }
                },
                error: function(xhr) {
                    $('#result-content').html('<div class="alert alert-danger">分析失败：' + xhr.responseText + '</div>');
                }
            });
        }
        
        // 显示备份模态框
        function showBackupModal(tables) {
            // 更新选中的表列表
            var tableListHtml = '';
            for (var i = 0; i < tables.length; i++) {
                tableListHtml += '<div class="badge badge-info mr-1 mb-1">' + tables[i] + '</div>';
            }
            $('#selected-tables-list').html(tableListHtml);
            
            // 设置默认备份名称
            var now = new Date();
            var defaultName = 'backup_' + 
                now.getFullYear() + '_' + 
                padZero(now.getMonth() + 1) + '_' + 
                padZero(now.getDate()) + '_' + 
                padZero(now.getHours()) + 
                padZero(now.getMinutes()) + 
                padZero(now.getSeconds());
            $('#backup-name').val(defaultName);
            
            // 显示模态框
            $('#backup-modal').modal('show');
            
            // 备份表单提交
            $('#submit-backup').off('click').on('click', function() {
                var backupName = $('#backup-name').val() || defaultName;
                backupTables(tables, backupName);
                $('#backup-modal').modal('hide');
            });
        }
        
        // 显示清空表模态框
        function showTruncateModal(tables) {
            // 更新选中的表列表
            var tableListHtml = '';
            for (var i = 0; i < tables.length; i++) {
                tableListHtml += '<div class="badge badge-danger mr-1 mb-1">' + tables[i] + '</div>';
            }
            $('#truncate-tables-list').html(tableListHtml);
            
            // 重置确认输入
            $('#truncate-confirm').val('');
            $('#submit-truncate').prop('disabled', true);
            
            // 显示模态框
            $('#truncate-modal').modal('show');
            
            // 监听确认输入
            $('#truncate-confirm').on('input', function() {
                $('#submit-truncate').prop('disabled', $(this).val() !== 'CONFIRM');
            });
            
            // 清空表单提交
            $('#submit-truncate').off('click').on('click', function() {
                if ($('#truncate-confirm').val() === 'CONFIRM') {
                    truncateTables(tables);
                    $('#truncate-modal').modal('hide');
                }
            });
        }
        
        // 备份表
        function backupTables(tables, backupName) {
            $.ajax({
                url: '{{ route("admin.database.backup.create") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    backup_name: backupName,
                    tables: tables
                },
                beforeSend: function() {
                    $('#result-content').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>备份中，请稍候...</p></div>');
                    $('#result-modal').modal('show');
                },
                success: function(response) {
                    if (response.success) {
                        var resultHtml = '<div class="alert alert-success">备份完成！</div>';
                        resultHtml += '<p>备份文件：' + response.filename + '</p>';
                        resultHtml += '<p>备份大小：' + response.size + '</p>';
                        resultHtml += '<p>备份时间：' + response.created_at + '</p>';
                        resultHtml += '<div class="mt-3"><a href="' + response.download_url + '" class="btn btn-primary"><i class="fas fa-download mr-1"></i> 下载备份</a></div>';
                        $('#result-content').html(resultHtml);
                    } else {
                        $('#result-content').html('<div class="alert alert-danger">备份失败！</div><p>' + response.message + '</p>');
                    }
                },
                error: function(xhr) {
                    $('#result-content').html('<div class="alert alert-danger">备份失败：' + xhr.responseText + '</div>');
                }
            });
        }
        
        // 清空表
        function truncateTables(tables) {
            $.ajax({
                url: '{{ route("admin.database.truncate") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    tables: tables
                },
                beforeSend: function() {
                    $('#result-content').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>清空中，请稍候...</p></div>');
                    $('#result-modal').modal('show');
                },
                success: function(response) {
                    if (response.success) {
                        var resultHtml = '<div class="alert alert-success">表已成功清空！</div>';
                        resultHtml += '<div class="mt-3"><h5>清空结果：</h5><ul>';
                        
                        for (var table in response.results) {
                            var result = response.results[table];
                            resultHtml += '<li>' + table + ': ' + 
                                (result.status === 'success' ? 
                                    '<span class="text-success">成功</span>' : 
                                    '<span class="text-danger">失败 - ' + result.message + '</span>') + 
                                '</li>';
                        }
                        
                        resultHtml += '</ul></div>';
                        $('#result-content').html(resultHtml);
                        
                        // 刷新页面
                        setTimeout(function() {
                            location.reload();
                        }, 3000);
                    } else {
                        $('#result-content').html('<div class="alert alert-danger">清空失败！</div><p>' + response.message + '</p>');
                    }
                },
                error: function(xhr) {
                    $('#result-content').html('<div class="alert alert-danger">清空失败：' + xhr.responseText + '</div>');
                }
            });
        }
        
        // 辅助函数：补零
        function padZero(num) {
            return num < 10 ? '0' + num : num;
        }
    });
</script>
@endsection 