@extends("admin.layouts.app")

@section("title", "数据库备份管理")

@section("content")
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>数据库备份管理</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">首页</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.database.index') }}">数据库超级运维</a></li>
                <li class="breadcrumb-item active">备份管理</li>
            </ol>
        </div>
    </div>

    <!-- 创建备份 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">创建新备份</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.database.backup.create') }}" method="POST" id="backup-form">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="backup-name">备份名称</label>
                            <input type="text" class="form-control" id="backup-name" name="backup_name" placeholder="例如：full_backup_20250630">
                            <small class="form-text text-muted">如果不填写，将使用默认格式：backup_日期时间</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>备份选项</label>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="backup-structure" name="options[structure]" value="1" checked>
                                <label class="custom-control-label" for="backup-structure">包含表结构</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="backup-data" name="options[data]" value="1" checked>
                                <label class="custom-control-label" for="backup-data">包含数据</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="backup-routines" name="options[routines]" value="1">
                                <label class="custom-control-label" for="backup-routines">包含存储过程和函数</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="backup-events" name="options[events]" value="1">
                                <label class="custom-control-label" for="backup-events">包含事件</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="backup-triggers" name="options[triggers]" value="1">
                                <label class="custom-control-label" for="backup-triggers">包含触发器</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>压缩选项</label>
                            <div class="custom-control custom-radio">
                                <input type="radio" class="custom-control-input" id="compress-none" name="options[compress]" value="none" checked>
                                <label class="custom-control-label" for="compress-none">不压缩</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" class="custom-control-input" id="compress-gzip" name="options[compress]" value="gzip">
                                <label class="custom-control-label" for="compress-gzip">GZIP压缩</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group mt-3">
                    <label>选择要备份的表</label>
                    <div class="mb-2">
                        <button type="button" class="btn btn-sm btn-default" id="select-all-tables">全选</button>
                        <button type="button" class="btn btn-sm btn-default" id="deselect-all-tables">取消全选</button>
                    </div>
                    <div class="table-list border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                        <div class="row">
                            @php
                                $tables = DB::select('SHOW TABLES');
                                $tableColumn = 'Tables_in_' . env('DB_DATABASE');
                            @endphp
                            
                            @foreach($tables as $table)
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input table-checkbox" id="table-{{ $table->$tableColumn }}" name="tables[]" value="{{ $table->$tableColumn }}">
                                        <label class="custom-control-label" for="table-{{ $table->$tableColumn }}">{{ $table->$tableColumn }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary" id="create-backup">
                        <i class="fas fa-download mr-1"></i> 创建备份
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 备份列表 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">备份列表</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="backups-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>文件名</th>
                            <th>描述</th>
                            <th>大小</th>
                            <th>创建时间</th>
                            <th>创建者</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $backup)
                            <tr>
                                <td>{{ $backup['id'] }}</td>
                                <td>{{ $backup['filename'] }}</td>
                                <td>{{ $backup['description'] }}</td>
                                <td>{{ $backup['size'] }}</td>
                                <td>{{ $backup['created_at'] }}</td>
                                <td>{{ $backup['created_by'] }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.database.backup.download', $backup['filename']) }}" class="btn btn-sm btn-info" title="下载">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-success restore-backup" data-filename="{{ $backup['filename'] }}" title="恢复">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-backup" data-filename="{{ $backup['filename'] }}" title="删除">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">暂无备份记录</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 备份进度模态框 -->
<div class="modal fade" id="backup-progress-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">备份进度</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                </div>
                <div class="mt-3" id="backup-status">
                    <p>准备开始备份...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<!-- 恢复确认模态框 -->
<div class="modal fade" id="restore-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-warning">恢复备份</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-1"></i> 警告：恢复备份将覆盖当前数据库中的数据，此操作不可撤销！
                </div>
                <p>您确定要恢复以下备份吗？</p>
                <p><strong id="restore-filename"></strong></p>
                <div class="form-group mt-3">
                    <label for="restore-confirm">请输入"RESTORE"以确认操作</label>
                    <input type="text" class="form-control" id="restore-confirm" placeholder="RESTORE">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-warning" id="submit-restore" disabled>确认恢复</button>
            </div>
        </div>
    </div>
</div>

<!-- 删除确认模态框 -->
<div class="modal fade" id="delete-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-danger">删除备份</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>您确定要删除以下备份吗？此操作不可撤销！</p>
                <p><strong id="delete-filename"></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <form id="delete-form" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">确认删除</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
    $(function () {
        // 表格排序和搜索初始化
        $('#backups-table').DataTable({
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
        
        // 全选/取消全选表
        $('#select-all-tables').click(function() {
            $('.table-checkbox').prop('checked', true);
        });
        
        $('#deselect-all-tables').click(function() {
            $('.table-checkbox').prop('checked', false);
        });
        
        // 创建备份表单提交
        $('#backup-form').submit(function(e) {
            e.preventDefault();
            
            // 检查是否选择了表
            var selectedTables = $('.table-checkbox:checked').length;
            if (selectedTables === 0) {
                alert('请至少选择一个表进行备份');
                return false;
            }
            
            // 显示进度模态框
            $('#backup-progress-modal').modal('show');
            var progress = 0;
            var progressBar = $('#backup-progress-modal .progress-bar');
            var statusText = $('#backup-status');
            
            // 提交表单
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        progressBar.css('width', '100%');
                        statusText.html('<div class="alert alert-success">备份完成！</div>');
                        statusText.append('<p>备份文件：' + response.filename + '</p>');
                        statusText.append('<p>备份大小：' + response.size + '</p>');
                        statusText.append('<div class="mt-3"><a href="' + response.download_url + '" class="btn btn-primary"><i class="fas fa-download mr-1"></i> 下载备份</a></div>');
                        
                        // 刷新页面
                        setTimeout(function() {
                            location.reload();
                        }, 3000);
                    } else {
                        progressBar.css('width', '100%').removeClass('bg-primary').addClass('bg-danger');
                        statusText.html('<div class="alert alert-danger">备份失败！</div><p>' + response.message + '</p>');
                    }
                },
                error: function(xhr) {
                    progressBar.css('width', '100%').removeClass('bg-primary').addClass('bg-danger');
                    statusText.html('<div class="alert alert-danger">备份失败：' + xhr.responseText + '</div>');
                },
                beforeSend: function() {
                    // 模拟进度
                    var interval = setInterval(function() {
                        progress += 5;
                        if (progress >= 90) {
                            clearInterval(interval);
                        }
                        progressBar.css('width', progress + '%');
                        statusText.html('<p>备份中，请稍候... ' + progress + '%</p>');
                    }, 300);
                }
            });
        });
        
        // 恢复备份
        $('.restore-backup').click(function() {
            var filename = $(this).data('filename');
            $('#restore-filename').text(filename);
            $('#restore-confirm').val('');
            $('#submit-restore').prop('disabled', true);
            $('#restore-modal').modal('show');
        });
        
        // 监听恢复确认输入
        $('#restore-confirm').on('input', function() {
            $('#submit-restore').prop('disabled', $(this).val() !== 'RESTORE');
        });
        
        // 恢复备份提交
        $('#submit-restore').click(function() {
            if ($('#restore-confirm').val() === 'RESTORE') {
                var filename = $('#restore-filename').text();
                
                // 提交恢复请求
                $.ajax({
                    url: '{{ route("admin.database.backup.restore", "") }}/' + filename,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function() {
                        $('#restore-modal').modal('hide');
                        $('#backup-status').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>恢复中，请稍候...</p></div>');
                        $('#backup-progress-modal').modal('show');
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#backup-status').html('<div class="alert alert-success">恢复成功！</div>');
                            
                            // 刷新页面
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            $('#backup-status').html('<div class="alert alert-danger">恢复失败！</div><p>' + response.message + '</p>');
                        }
                    },
                    error: function(xhr) {
                        $('#backup-status').html('<div class="alert alert-danger">恢复失败：' + xhr.responseText + '</div>');
                    }
                });
            }
        });
        
        // 删除备份
        $('.delete-backup').click(function() {
            var filename = $(this).data('filename');
            $('#delete-filename').text(filename);
            $('#delete-form').attr('action', '{{ route("admin.database.backup.delete", "") }}/' + filename);
            $('#delete-modal').modal('show');
        });
    });
</script>
@endsection 