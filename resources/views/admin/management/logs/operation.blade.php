@extends('admin.layouts.app')

@section('title', '操作日志')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">操作日志</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <form action="{{ route('admin.logs.operation') }}" method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <input type="text" name="admin" class="form-control" placeholder="管理员用户名" value="{{ request('admin') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select name="action" class="form-control">
                                        <option value="">所有操作</option>
                                        @foreach($actions as $action)
                                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ $action }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select name="target_type" class="form-control">
                                        <option value="">所有对象类型</option>
                                        @foreach($targetTypes as $type)
                                        <option value="{{ $type }}" {{ request('target_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
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
                                <a href="{{ route('admin.logs.operation') }}" class="btn btn-default">
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
                                    <th>操作</th>
                                    <th>对象类型</th>
                                    <th>对象ID</th>
                                    <th>IP地址</th>
                                    <th>操作时间</th>
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
                                        未知用户
                                        @endif
                                    </td>
                                    <td>{{ $log->action }}</td>
                                    <td>{{ $log->target_type }}</td>
                                    <td>{{ $log->target_id }}</td>
                                    <td>
                                        {{ $log->ip_address }}
                                        @if($log->ip_location)
                                        <br><small class="text-muted">{{ $log->ip_location }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $log->created_at }}</td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-info view-details" data-toggle="modal" data-target="#modal-details" data-log="{{ json_encode($log) }}">
                                            <i class="fas fa-eye"></i> 详情
                                        </button>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">操作详情</h4>
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
                            <label>操作</label>
                            <p id="detail-action"></p>
                        </div>
                        <div class="form-group">
                            <label>对象类型</label>
                            <p id="detail-target-type"></p>
                        </div>
                        <div class="form-group">
                            <label>对象ID</label>
                            <p id="detail-target-id"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>IP地址</label>
                            <p id="detail-ip"></p>
                        </div>
                        <div class="form-group">
                            <label>操作时间</label>
                            <p id="detail-time"></p>
                        </div>
                        <div class="form-group">
                            <label>设备信息</label>
                            <p id="detail-device"></p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">操作数据</h5>
                            </div>
                            <div class="card-body">
                                <div class="nav-tabs-custom">
                                    <ul class="nav nav-tabs">
                                        <li class="nav-item">
                                            <a class="nav-link active" href="#tab-before" data-toggle="tab">修改前</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#tab-after" data-toggle="tab">修改后</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#tab-diff" data-toggle="tab">差异</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="tab-before">
                                            <pre id="detail-before" class="mt-3"></pre>
                                        </div>
                                        <div class="tab-pane" id="tab-after">
                                            <pre id="detail-after" class="mt-3"></pre>
                                        </div>
                                        <div class="tab-pane" id="tab-diff">
                                            <pre id="detail-diff" class="mt-3"></pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
            
            $('#detail-username').text(log.admin ? log.admin.username : '未知用户');
            $('#detail-action').text(log.action);
            $('#detail-target-type').text(log.target_type);
            $('#detail-target-id').text(log.target_id);
            $('#detail-ip').text(log.ip_address + (log.ip_location ? ' (' + log.ip_location + ')' : ''));
            $('#detail-time').text(log.created_at);
            $('#detail-device').text(log.user_agent || '未知');
            
            // 格式化JSON数据
            var beforeData = log.before_data ? JSON.stringify(JSON.parse(log.before_data), null, 2) : '无数据';
            var afterData = log.after_data ? JSON.stringify(JSON.parse(log.after_data), null, 2) : '无数据';
            
            $('#detail-before').text(beforeData);
            $('#detail-after').text(afterData);
            
            // 计算差异
            if (log.before_data && log.after_data) {
                var diff = '';
                try {
                    var before = JSON.parse(log.before_data);
                    var after = JSON.parse(log.after_data);
                    
                    // 简单差异计算
                    for (var key in after) {
                        if (before.hasOwnProperty(key)) {
                            if (JSON.stringify(before[key]) !== JSON.stringify(after[key])) {
                                diff += key + ':\n';
                                diff += '  - ' + JSON.stringify(before[key]) + '\n';
                                diff += '  + ' + JSON.stringify(after[key]) + '\n';
                            }
                        } else {
                            diff += key + ':\n';
                            diff += '  + ' + JSON.stringify(after[key]) + '\n';
                        }
                    }
                    
                    for (var key in before) {
                        if (!after.hasOwnProperty(key)) {
                            diff += key + ':\n';
                            diff += '  - ' + JSON.stringify(before[key]) + '\n';
                        }
                    }
                    
                    if (!diff) {
                        diff = '无差异';
                    }
                } catch (e) {
                    diff = '无法计算差异';
                }
                
                $('#detail-diff').text(diff);
            } else {
                $('#detail-diff').text('无法计算差异');
            }
        });
    });
</script>
@endsection 