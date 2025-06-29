@extends('admin.layouts.app')

@section('title', '风险事件管理')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">风险事件列表</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <form action="{{ route('admin.security.api.risk-events.index') }}" method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <input type="text" name="ip_address" class="form-control" placeholder="IP地址" value="{{ request('ip_address') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <input type="text" name="user_id" class="form-control" placeholder="用户ID" value="{{ request('user_id') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select name="risk_level" class="form-control">
                                        <option value="">所有风险等级</option>
                                        <option value="low" {{ request('risk_level') == 'low' ? 'selected' : '' }}>低</option>
                                        <option value="medium" {{ request('risk_level') == 'medium' ? 'selected' : '' }}>中</option>
                                        <option value="high" {{ request('risk_level') == 'high' ? 'selected' : '' }}>高</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select name="risk_type" class="form-control">
                                        <option value="">所有风险类型</option>
                                        @foreach($riskTypes as $type)
                                        <option value="{{ $type }}" {{ request('risk_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select name="action_taken" class="form-control">
                                        <option value="">所有处理结果</option>
                                        <option value="logged" {{ request('action_taken') == 'logged' ? 'selected' : '' }}>已记录</option>
                                        <option value="blocked" {{ request('action_taken') == 'blocked' ? 'selected' : '' }}>已阻止</option>
                                        <option value="blacklisted" {{ request('action_taken') == 'blacklisted' ? 'selected' : '' }}>已加入黑名单</option>
                                        <option value="captcha" {{ request('action_taken') == 'captcha' ? 'selected' : '' }}>要求验证码</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select name="interface_id" class="form-control">
                                        <option value="">所有接口</option>
                                        @foreach($interfaces as $interface)
                                        <option value="{{ $interface->id }}" {{ request('interface_id') == $interface->id ? 'selected' : '' }}>{{ $interface->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="far fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control float-right" id="date-range" name="date_range" value="{{ request('date_range') }}" placeholder="选择日期范围">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select name="rule_id" class="form-control">
                                        <option value="">所有规则</option>
                                        @foreach($rules as $rule)
                                        <option value="{{ $rule->id }}" {{ request('rule_id') == $rule->id ? 'selected' : '' }}>{{ $rule->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 搜索
                                </button>
                                <a href="{{ route('admin.security.api.risk-events.index') }}" class="btn btn-default">
                                    <i class="fas fa-redo"></i> 重置
                                </a>
                                <button type="submit" name="export" value="1" class="btn btn-success">
                                    <i class="fas fa-download"></i> 导出
                                </button>
                                <a href="{{ route('admin.security.api.risk-events.analysis') }}" class="btn btn-info">
                                    <i class="fas fa-chart-bar"></i> 风险分析
                                </a>
                            </div>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>时间</th>
                                    <th>接口</th>
                                    <th>IP地址</th>
                                    <th>用户ID</th>
                                    <th>风险类型</th>
                                    <th>风险等级</th>
                                    <th>触发规则</th>
                                    <th>处理结果</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($riskEvents as $event)
                                <tr>
                                    <td>{{ $event->id }}</td>
                                    <td>{{ $event->created_at }}</td>
                                    <td>
                                        @if($event->api_interface)
                                        <a href="{{ route('admin.security.api.interfaces.show', $event->api_interface->id) }}">
                                            {{ $event->api_interface->name }}
                                        </a>
                                        @else
                                        未知接口
                                        @endif
                                    </td>
                                    <td>
                                        {{ $event->ip_address }}
                                        @if($event->ip_location)
                                        <br><small class="text-muted">{{ $event->ip_location }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $event->user_id ?: '-' }}</td>
                                    <td>{{ $event->risk_type }}</td>
                                    <td>
                                        @if($event->risk_level == 'high')
                                        <span class="badge badge-danger">高</span>
                                        @elseif($event->risk_level == 'medium')
                                        <span class="badge badge-warning">中</span>
                                        @else
                                        <span class="badge badge-info">低</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($event->risk_rule)
                                        <a href="{{ route('admin.security.api.risk-rules.show', $event->risk_rule->id) }}">
                                            {{ $event->risk_rule->name }}
                                        </a>
                                        @else
                                        未知规则
                                        @endif
                                    </td>
                                    <td>
                                        @if($event->action_taken == 'blocked')
                                        <span class="badge badge-danger">已阻止</span>
                                        @elseif($event->action_taken == 'blacklisted')
                                        <span class="badge badge-dark">已加入黑名单</span>
                                        @elseif($event->action_taken == 'captcha')
                                        <span class="badge badge-warning">要求验证码</span>
                                        @elseif($event->action_taken == 'logged')
                                        <span class="badge badge-info">已记录</span>
                                        @else
                                        <span class="badge badge-secondary">{{ $event->action_taken }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.security.api.risk-events.show', $event->id) }}" class="btn btn-xs btn-info">
                                            <i class="fas fa-eye"></i> 查看
                                        </a>
                                        @if(!$event->is_handled)
                                        <div class="btn-group mt-1">
                                            <button type="button" class="btn btn-xs btn-warning dropdown-toggle" data-toggle="dropdown">
                                                <i class="fas fa-cog"></i> 处理
                                            </button>
                                            <div class="dropdown-menu">
                                                <form action="{{ route('admin.security.api.risk-events.handle', $event->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="action" value="ignore">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-check"></i> 忽略
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.security.api.risk-events.handle', $event->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="action" value="block">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-ban"></i> 阻止
                                                    </button>
                                                </form>
                                                <div class="dropdown-divider"></div>
                                                <a href="#" class="dropdown-item add-to-blacklist" data-id="{{ $event->id }}" data-ip="{{ $event->ip_address }}">
                                                    <i class="fas fa-user-slash"></i> 加入黑名单
                                                </a>
                                            </div>
                                        </div>
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
                    {{ $riskEvents->appends(request()->except('page'))->links() }}
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>

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
                <input type="hidden" name="event_id" id="blacklist-event-id">
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
        
        // 添加到黑名单
        $('.add-to-blacklist').click(function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var ip = $(this).data('ip');
            $('#blacklist-event-id').val(id);
            $('#blacklist-ip').val(ip);
            $('#blacklist-reason').val('风险事件ID: ' + id);
            $('#modal-blacklist').modal('show');
        });
    });
</script>
@endsection 