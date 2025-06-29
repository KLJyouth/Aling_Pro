@extends('admin.layouts.app')

@section('title', '系统日志')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">系统日志列表</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-danger" id="clear-logs-btn">
                            <i class="fas fa-trash"></i> 清空日志
                        </button>
                        <button type="button" class="btn btn-sm btn-success" id="export-logs-btn">
                            <i class="fas fa-download"></i> 导出日志
                        </button>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <form action="{{ route('admin.management.logs.index') }}" method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select name="log_type" class="form-control">
                                        <option value="">所有日志类型</option>
                                        @foreach($logTypes as $type => $label)
                                        <option value="{{ $type }}" {{ request('log_type') == $type ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select name="level" class="form-control">
                                        <option value="">所有级别</option>
                                        @foreach($logLevels as $level => $label)
                                        <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <input type="text" name="user" class="form-control" placeholder="用户名/ID" value="{{ request('user') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <input type="text" name="message" class="form-control" placeholder="日志内容" value="{{ request('message') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="far fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control float-right" id="date-range" name="date_range" value="{{ request('date_range') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 搜索
                                </button>
                                <a href="{{ route('admin.management.logs.index') }}" class="btn btn-default">
                                    <i class="fas fa-redo"></i> 重置
                                </a>
                            </div>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">ID</th>
                                    <th style="width: 100px;">类型</th>
                                    <th style="width: 100px;">级别</th>
                                    <th>内容</th>
                                    <th style="width: 150px;">用户</th>
                                    <th style="width: 120px;">IP地址</th>
                                    <th style="width: 170px;">时间</th>
                                    <th style="width: 80px;">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>
                                        @if($log->log_type == 'system')
                                        <span class="badge badge-primary">系统</span>
                                        @elseif($log->log_type == 'login')
                                        <span class="badge badge-info">登录</span>
                                        @elseif($log->log_type == 'operation')
                                        <span class="badge badge-success">操作</span>
                                        @elseif($log->log_type == 'error')
                                        <span class="badge badge-danger">错误</span>
                                        @elseif($log->log_type == 'security')
                                        <span class="badge badge-warning">安全</span>
                                        @else
                                        <span class="badge badge-secondary">其他</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->level == 'info')
                                        <span class="badge badge-info">信息</span>
                                        @elseif($log->level == 'warning')
                                        <span class="badge badge-warning">警告</span>
                                        @elseif($log->level == 'error')
                                        <span class="badge badge-danger">错误</span>
                                        @elseif($log->level == 'critical')
                                        <span class="badge badge-dark">严重</span>
                                        @elseif($log->level == 'debug')
                                        <span class="badge badge-secondary">调试</span>
                                        @else
                                        <span class="badge badge-light">{{ $log->level }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="log-message">{{ \Illuminate\Support\Str::limit($log->message, 100) }}</span>
                                        @if(strlen($log->message) > 100)
                                        <a href="#" class="log-detail-link" data-toggle="modal" data-target="#logDetailModal" data-log-id="{{ $log->id }}">
                                            <i class="fas fa-search-plus"></i> 查看详情
                                        </a>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->user_id)
                                        <a href="{{ route('admin.management.users.show', $log->user_id) }}">
                                            {{ $log->user_name ?? 'ID: '.$log->user_id }}
                                        </a>
                                        @else
                                        <span class="text-muted">系统</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->ip_address }}</td>
                                    <td>{{ $log->created_at }}</td>
                                    <td>
                                        <a href="{{ route('admin.management.logs.show', $log->id) }}" class="btn btn-xs btn-info">
                                            <i class="fas fa-eye"></i> 详情
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{ $logs->appends(request()->except('page'))->links() }}
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>

<!-- 日志详情模态框 -->
<div class="modal fade" id="logDetailModal" tabindex="-1" role="dialog" aria-labelledby="logDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logDetailModalLabel">日志详情</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center py-3" id="log-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">加载中...</span>
                    </div>
                    <p class="mt-2">正在加载日志详情...</p>
                </div>
                <div id="log-detail-content" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> <span id="log-id"></span></p>
                            <p><strong>类型:</strong> <span id="log-type"></span></p>
                            <p><strong>级别:</strong> <span id="log-level"></span></p>
                            <p><strong>用户:</strong> <span id="log-user"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>IP地址:</strong> <span id="log-ip"></span></p>
                            <p><strong>用户代理:</strong> <span id="log-user-agent"></span></p>
                            <p><strong>URL:</strong> <span id="log-url"></span></p>
                            <p><strong>时间:</strong> <span id="log-time"></span></p>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label><strong>日志内容:</strong></label>
                        <div class="p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                            <pre id="log-message" style="white-space: pre-wrap;"></pre>
                        </div>
                    </div>
                    <div class="form-group" id="log-context-container">
                        <label><strong>上下文数据:</strong></label>
                        <div class="p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                            <pre id="log-context" style="white-space: pre-wrap;"></pre>
                        </div>
                    </div>
                </div>
                <div id="log-error" class="alert alert-danger" style="display: none;">
                    加载日志详情失败，请稍后再试。
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
                <a href="#" class="btn btn-primary" id="log-detail-link" target="_blank">查看完整详情</a>
            </div>
        </div>
    </div>
</div>

<!-- 清空日志确认模态框 -->
<div class="modal fade" id="clearLogsModal" tabindex="-1" role="dialog" aria-labelledby="clearLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clearLogsModalLabel">确认清空日志</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> 警告：此操作将清空所有系统日志，且无法恢复！
                </div>
                <form id="clear-logs-form">
                    <div class="form-group">
                        <label for="clear-logs-type">选择要清空的日志类型</label>
                        <select class="form-control" id="clear-logs-type" name="log_type">
                            <option value="all">所有日志</option>
                            @foreach($logTypes as $type => $label)
                            <option value="{{ $type }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="clear-logs-before">清空此日期之前的日志</label>
                        <input type="text" class="form-control" id="clear-logs-before" name="before_date" placeholder="留空表示清空所有">
                    </div>
                    <div class="form-group">
                        <label for="clear-logs-confirm">输入"CONFIRM"以确认</label>
                        <input type="text" class="form-control" id="clear-logs-confirm" name="confirm" placeholder="请输入CONFIRM" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" id="confirm-clear-logs">确认清空</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // 日期范围选择器
        $('#date-range').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD',
                applyLabel: '确定',
                cancelLabel: '取消',
                fromLabel: '从',
                toLabel: '至',
                customRangeLabel: '自定义',
                weekLabel: 'W',
                daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'],
                monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
                firstDay: 1
            },
            ranges: {
               '今天': [moment(), moment()],
               '昨天': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
               '最近7天': [moment().subtract(6, 'days'), moment()],
               '最近30天': [moment().subtract(29, 'days'), moment()],
               '本月': [moment().startOf('month'), moment().endOf('month')],
               '上月': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            autoUpdateInput: false
        });
        
        $('#date-range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });
        
        $('#date-range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
        
        // 日志详情模态框
        $('#logDetailModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var logId = button.data('log-id');
            
            $('#log-loading').show();
            $('#log-detail-content').hide();
            $('#log-error').hide();
            
            $.ajax({
                url: '{{ route("admin.management.logs.get_detail") }}',
                type: 'GET',
                data: {
                    id: logId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var log = response.data;
                        
                        $('#log-id').text(log.id);
                        $('#log-type').html(getLogTypeBadge(log.log_type));
                        $('#log-level').html(getLogLevelBadge(log.level));
                        $('#log-user').html(log.user_id ? '<a href="{{ url("admin/management/users") }}/' + log.user_id + '">' + (log.user_name || 'ID: ' + log.user_id) + '</a>' : '<span class="text-muted">系统</span>');
                        $('#log-ip').text(log.ip_address || '-');
                        $('#log-user-agent').text(log.user_agent || '-');
                        $('#log-url').text(log.url || '-');
                        $('#log-time').text(log.created_at);
                        $('#log-message').text(log.message);
                        
                        if (log.context) {
                            try {
                                var contextObj = JSON.parse(log.context);
                                $('#log-context').text(JSON.stringify(contextObj, null, 2));
                                $('#log-context-container').show();
                            } catch (e) {
                                $('#log-context').text(log.context);
                                $('#log-context-container').show();
                            }
                        } else {
                            $('#log-context-container').hide();
                        }
                        
                        $('#log-detail-link').attr('href', '{{ url("admin/management/logs") }}/' + log.id);
                        
                        $('#log-loading').hide();
                        $('#log-detail-content').show();
                    } else {
                        $('#log-loading').hide();
                        $('#log-error').show();
                    }
                },
                error: function() {
                    $('#log-loading').hide();
                    $('#log-error').show();
                }
            });
        });
        
        // 清空日志按钮
        $('#clear-logs-btn').click(function() {
            $('#clearLogsModal').modal('show');
        });
        
        // 清空日志确认
        $('#confirm-clear-logs').click(function() {
            var logType = $('#clear-logs-type').val();
            var beforeDate = $('#clear-logs-before').val();
            var confirmText = $('#clear-logs-confirm').val();
            
            if (confirmText !== 'CONFIRM') {
                toastr.error('请输入"CONFIRM"以确认清空操作');
                return;
            }
            
            $.ajax({
                url: '{{ route("admin.management.logs.clear") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    log_type: logType,
                    before_date: beforeDate
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#clearLogsModal').modal('hide');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('清空日志失败，请稍后再试');
                }
            });
        });
        
        // 导出日志按钮
        $('#export-logs-btn').click(function() {
            var queryString = window.location.search;
            window.location.href = '{{ route("admin.management.logs.export") }}' + queryString;
        });
        
        // 清空日志日期选择器
        $('#clear-logs-before').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'YYYY-MM-DD',
                applyLabel: '确定',
                cancelLabel: '取消',
                daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'],
                monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
                firstDay: 1
            },
            autoUpdateInput: false
        });
        
        $('#clear-logs-before').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });
        
        $('#clear-logs-before').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
        
        // 辅助函数：获取日志类型的徽章HTML
        function getLogTypeBadge(type) {
            switch (type) {
                case 'system':
                    return '<span class="badge badge-primary">系统</span>';
                case 'login':
                    return '<span class="badge badge-info">登录</span>';
                case 'operation':
                    return '<span class="badge badge-success">操作</span>';
                case 'error':
                    return '<span class="badge badge-danger">错误</span>';
                case 'security':
                    return '<span class="badge badge-warning">安全</span>';
                default:
                    return '<span class="badge badge-secondary">其他</span>';
            }
        }
        
        // 辅助函数：获取日志级别的徽章HTML
        function getLogLevelBadge(level) {
            switch (level) {
                case 'info':
                    return '<span class="badge badge-info">信息</span>';
                case 'warning':
                    return '<span class="badge badge-warning">警告</span>';
                case 'error':
                    return '<span class="badge badge-danger">错误</span>';
                case 'critical':
                    return '<span class="badge badge-dark">严重</span>';
                case 'debug':
                    return '<span class="badge badge-secondary">调试</span>';
                default:
                    return '<span class="badge badge-light">' + level + '</span>';
            }
        }
    });
</script>
@endsection 