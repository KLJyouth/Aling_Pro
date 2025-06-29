@extends('admin.layouts.app')

@section('title', '登录日志')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">登录日志</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <form action="{{ route('admin.logs.login') }}" method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <input type="text" name="keyword" class="form-control" placeholder="用户名/邮箱/IP" value="{{ request('keyword') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select name="status" class="form-control">
                                        <option value="">所有状态</option>
                                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>成功</option>
                                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>失败</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
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
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 搜索
                                </button>
                                <a href="{{ route('admin.logs.login') }}" class="btn btn-default">
                                    <i class="fas fa-redo"></i> 重置
                                </a>
                                <button type="submit" name="export" value="1" class="btn btn-success">
                                    <i class="fas fa-download"></i> 导出
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>管理员</th>
                                    <th>IP地址</th>
                                    <th>登录时间</th>
                                    <th>状态</th>
                                    <th>设备信息</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>
                                        @if($log->admin)
                                        <a href="{{ route('admin.users.show', $log->admin_id) }}">{{ $log->admin->username }}</a>
                                        @else
                                        {{ $log->username ?? '未知用户' }}
                                        @endif
                                    </td>
                                    <td>
                                        {{ $log->ip_address }}
                                        @if($log->ip_location)
                                        <br><small class="text-muted">{{ $log->ip_location }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $log->created_at }}</td>
                                    <td>
                                        @if($log->status == 'success')
                                        <span class="badge badge-success">成功</span>
                                        @else
                                        <span class="badge badge-danger">失败</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $log->user_agent }}</small>
                                        @if($log->device_info)
                                        <br><small class="text-muted">{{ $log->device_info }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-info view-details" data-toggle="modal" data-target="#modal-details" data-log="{{ json_encode($log) }}">
                                            <i class="fas fa-eye"></i> 详情
                                        </button>
                                        @if($log->status == 'failed')
                                        <button type="button" class="btn btn-xs btn-warning add-to-blacklist" data-ip="{{ $log->ip_address }}">
                                            <i class="fas fa-ban"></i> 加入黑名单
                                        </button>
                                        @endif
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

<!-- 详情模态框 -->
<div class="modal fade" id="modal-details">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">登录详情</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>管理员</label>
                            <p id="detail-username"></p>
                        </div>
                        <div class="form-group">
                            <label>IP地址</label>
                            <p id="detail-ip"></p>
                        </div>
                        <div class="form-group">
                            <label>登录时间</label>
                            <p id="detail-time"></p>
                        </div>
                        <div class="form-group">
                            <label>状态</label>
                            <p id="detail-status"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>设备信息</label>
                            <p id="detail-device"></p>
                        </div>
                        <div class="form-group">
                            <label>浏览器</label>
                            <p id="detail-browser"></p>
                        </div>
                        <div class="form-group">
                            <label>操作系统</label>
                            <p id="detail-os"></p>
                        </div>
                        <div class="form-group">
                            <label>失败原因</label>
                            <p id="detail-reason"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- 黑名单模态框 -->
<div class="modal fade" id="modal-blacklist">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">添加到黑名单</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.security.api.blacklists.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="blacklist-ip">IP地址</label>
                        <input type="text" class="form-control" id="blacklist-ip" name="ip_address" readonly>
                    </div>
                    <div class="form-group">
                        <label for="blacklist-reason">原因</label>
                        <textarea class="form-control" id="blacklist-reason" name="reason" rows="3" placeholder="请输入加入黑名单的原因" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="blacklist-expire">过期时间</label>
                        <select class="form-control" id="blacklist-expire" name="expire_time">
                            <option value="1">1小时</option>
                            <option value="24">24小时</option>
                            <option value="168">7天</option>
                            <option value="720">30天</option>
                            <option value="0" selected>永不过期</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-warning">添加到黑名单</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
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
                toLabel: '到',
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
               '上个月': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            autoUpdateInput: false
        });
        
        $('#date-range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });
        
        $('#date-range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
        
        // 查看详情
        $('.view-details').click(function() {
            var log = $(this).data('log');
            
            $('#detail-username').text(log.admin ? log.admin.username : (log.username || '未知用户'));
            $('#detail-ip').text(log.ip_address + (log.ip_location ? ' (' + log.ip_location + ')' : ''));
            $('#detail-time').text(log.created_at);
            
            if (log.status === 'success') {
                $('#detail-status').html('<span class="badge badge-success">成功</span>');
            } else {
                $('#detail-status').html('<span class="badge badge-danger">失败</span>');
            }
            
            $('#detail-device').text(log.device_info || '未知');
            $('#detail-browser').text(log.browser || '未知');
            $('#detail-os').text(log.operating_system || '未知');
            $('#detail-reason').text(log.fail_reason || '无');
        });
        
        // 添加到黑名单
        $('.add-to-blacklist').click(function() {
            var ip = $(this).data('ip');
            $('#blacklist-ip').val(ip);
            $('#modal-blacklist').modal('show');
        });
    });
</script>
@endsection 