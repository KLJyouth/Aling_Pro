@extends('admin.layouts.app')

@section('title', '风险事件分析')

@section('content')
<div class="container-fluid">
    <!-- 筛选条件 -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">筛选条件</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.security.api.risk-events.analysis') }}" method="GET" id="filter-form">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>日期范围</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="far fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control float-right" id="date-range" name="date_range" value="{{ request('date_range', now()->subDays(30)->format('Y-m-d').' - '.now()->format('Y-m-d')) }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>接口</label>
                                    <select name="interface_id" class="form-control select2">
                                        <option value="">所有接口</option>
                                        @foreach($interfaces as $interface)
                                        <option value="{{ $interface->id }}" {{ request('interface_id') == $interface->id ? 'selected' : '' }}>{{ $interface->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>风险等级</label>
                                    <select name="risk_level" class="form-control">
                                        <option value="">所有等级</option>
                                        <option value="low" {{ request('risk_level') == 'low' ? 'selected' : '' }}>低</option>
                                        <option value="medium" {{ request('risk_level') == 'medium' ? 'selected' : '' }}>中</option>
                                        <option value="high" {{ request('risk_level') == 'high' ? 'selected' : '' }}>高</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>风险类型</label>
                                    <select name="risk_type" class="form-control">
                                        <option value="">所有类型</option>
                                        @foreach($riskTypes as $type)
                                        <option value="{{ $type }}" {{ request('risk_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>处理结果</label>
                                    <select name="action_taken" class="form-control">
                                        <option value="">所有结果</option>
                                        <option value="logged" {{ request('action_taken') == 'logged' ? 'selected' : '' }}>已记录</option>
                                        <option value="blocked" {{ request('action_taken') == 'blocked' ? 'selected' : '' }}>已阻止</option>
                                        <option value="blacklisted" {{ request('action_taken') == 'blacklisted' ? 'selected' : '' }}>已加入黑名单</option>
                                        <option value="captcha" {{ request('action_taken') == 'captcha' ? 'selected' : '' }}>要求验证码</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> 应用筛选
                                </button>
                                <a href="{{ route('admin.security.api.risk-events.analysis') }}" class="btn btn-default">
                                    <i class="fas fa-redo"></i> 重置
                                </a>
                                <button type="button" class="btn btn-success" id="btn-export">
                                    <i class="fas fa-download"></i> 导出报告
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 风险概览 -->
    <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-shield-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">总风险事件</span>
                    <span class="info-box-number">{{ number_format($totalEvents) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-radiation"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">高风险事件</span>
                    <span class="info-box-number">{{ number_format($highRiskEvents) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-ban"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">已阻止请求</span>
                    <span class="info-box-number">{{ number_format($blockedEvents) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-dark"><i class="fas fa-user-slash"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">加入黑名单</span>
                    <span class="info-box-number">{{ number_format($blacklistedEvents) }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 时间趋势图 -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">风险事件趋势</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="trendChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 风险类型和等级分布 -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">风险类型分布</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="riskTypeChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">风险等级分布</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="riskLevelChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 接口风险分布和处理结果分布 -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">接口风险分布（Top 10）</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="interfaceChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">处理结果分布</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="actionChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- IP地址和规则触发 -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">高风险IP地址（Top 10）</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>IP地址</th>
                                    <th>位置</th>
                                    <th>风险事件数</th>
                                    <th>高风险事件数</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topRiskIps as $ip)
                                <tr>
                                    <td>{{ $ip->ip_address }}</td>
                                    <td>{{ $ip->ip_location ?: '未知' }}</td>
                                    <td>{{ number_format($ip->event_count) }}</td>
                                    <td>{{ number_format($ip->high_risk_count) }}</td>
                                    <td>
                                        <a href="{{ route('admin.security.api.risk-events.index', ['ip_address' => $ip->ip_address]) }}" class="btn btn-xs btn-info">
                                            <i class="fas fa-eye"></i> 查看事件
                                        </a>
                                        @if(!$ip->is_blacklisted)
                                        <button type="button" class="btn btn-xs btn-warning add-to-blacklist" data-ip="{{ $ip->ip_address }}">
                                            <i class="fas fa-user-slash"></i> 加入黑名单
                                        </button>
                                        @else
                                        <span class="badge badge-dark">已在黑名单</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">最常触发的规则（Top 10）</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>规则名称</th>
                                    <th>风险类型</th>
                                    <th>风险等级</th>
                                    <th>触发次数</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topRules as $rule)
                                <tr>
                                    <td>{{ $rule->name }}</td>
                                    <td>{{ $rule->risk_type }}</td>
                                    <td>
                                        @if($rule->risk_level == 'high')
                                        <span class="badge badge-danger">高</span>
                                        @elseif($rule->risk_level == 'medium')
                                        <span class="badge badge-warning">中</span>
                                        @else
                                        <span class="badge badge-info">低</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($rule->trigger_count) }}</td>
                                    <td>
                                        <a href="{{ route('admin.security.api.risk-rules.show', $rule->id) }}" class="btn btn-xs btn-info">
                                            <i class="fas fa-eye"></i> 查看规则
                                        </a>
                                        <a href="{{ route('admin.security.api.risk-events.index', ['rule_id' => $rule->id]) }}" class="btn btn-xs btn-primary">
                                            <i class="fas fa-list"></i> 查看事件
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 地理位置分布 -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">地理位置分布</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div id="world-map" style="height: 400px;"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>国家/地区</th>
                                            <th>事件数</th>
                                            <th>占比</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($locationStats as $location)
                                        <tr>
                                            <td>{{ $location->country ?: '未知' }}</td>
                                            <td>{{ number_format($location->count) }}</td>
                                            <td>{{ number_format($location->percentage, 2) }}%</td>
                                        </tr>
                                        @endforeach
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

<!-- 添加黑名单模态框 -->
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
                        <textarea class="form-control" id="blacklist-reason" name="reason" rows="3" placeholder="请输入加入黑名单的原因" required>风险分析检测到的高风险IP</textarea>
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
            }
        });
        
        // 添加到黑名单
        $('.add-to-blacklist').click(function() {
            var ip = $(this).data('ip');
            $('#blacklist-ip').val(ip);
            $('#modal-blacklist').modal('show');
        });
        
        // 导出报告
        $('#btn-export').click(function() {
            var form = $('#filter-form');
            var exportUrl = form.attr('action') + '?export=1';
            
            // 添加所有筛选条件到URL
            var params = form.serialize();
            if(params) {
                exportUrl += '&' + params;
            }
            
            window.location.href = exportUrl;
        });
        
        // 风险事件趋势图
        var trendCtx = document.getElementById('trendChart').getContext('2d');
        var trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($trendData['labels']) !!},
                datasets: [{
                    label: '总风险事件',
                    data: {!! json_encode($trendData['all']) !!},
                    borderColor: '#3490dc',
                    backgroundColor: 'rgba(52, 144, 220, 0.1)',
                    borderWidth: 2,
                    fill: true
                }, {
                    label: '高风险事件',
                    data: {!! json_encode($trendData['high']) !!},
                    borderColor: '#e3342f',
                    backgroundColor: 'rgba(227, 52, 47, 0.1)',
                    borderWidth: 2,
                    fill: true
                }, {
                    label: '已阻止请求',
                    data: {!! json_encode($trendData['blocked']) !!},
                    borderColor: '#f6993f',
                    backgroundColor: 'rgba(246, 153, 63, 0.1)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // 风险类型分布图
        var riskTypeCtx = document.getElementById('riskTypeChart').getContext('2d');
        var riskTypeChart = new Chart(riskTypeCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($riskTypeData['labels']) !!},
                datasets: [{
                    data: {!! json_encode($riskTypeData['data']) !!},
                    backgroundColor: [
                        '#3490dc', '#e3342f', '#f6993f', '#38c172', '#6574cd', 
                        '#9561e2', '#f66d9b', '#ffed4a', '#4dc0b5', '#adb5bd'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
        
        // 风险等级分布图
        var riskLevelCtx = document.getElementById('riskLevelChart').getContext('2d');
        var riskLevelChart = new Chart(riskLevelCtx, {
            type: 'pie',
            data: {
                labels: ['高风险', '中风险', '低风险'],
                datasets: [{
                    data: [
                        {{ $riskLevelData['high'] }},
                        {{ $riskLevelData['medium'] }},
                        {{ $riskLevelData['low'] }}
                    ],
                    backgroundColor: [
                        '#e3342f', '#f6993f', '#3490dc'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
        
        // 接口风险分布图
        var interfaceCtx = document.getElementById('interfaceChart').getContext('2d');
        var interfaceChart = new Chart(interfaceCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($interfaceData['labels']) !!},
                datasets: [{
                    label: '风险事件数',
                    data: {!! json_encode($interfaceData['data']) !!},
                    backgroundColor: '#3490dc'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                indexAxis: 'y'
            }
        });
        
        // 处理结果分布图
        var actionCtx = document.getElementById('actionChart').getContext('2d');
        var actionChart = new Chart(actionCtx, {
            type: 'pie',
            data: {
                labels: ['已记录', '已阻止', '已加入黑名单', '要求验证码', '其他'],
                datasets: [{
                    data: [
                        {{ $actionData['logged'] }},
                        {{ $actionData['blocked'] }},
                        {{ $actionData['blacklisted'] }},
                        {{ $actionData['captcha'] }},
                        {{ $actionData['other'] }}
                    ],
                    backgroundColor: [
                        '#3490dc', '#e3342f', '#343a40', '#f6993f', '#6c757d'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
        
        // 地理位置分布图
        $('#world-map').vectorMap({
            map: 'world_mill',
            backgroundColor: 'transparent',
            series: {
                regions: [{
                    values: {!! json_encode($mapData) !!},
                    scale: ['#C8EEFF', '#0071A4'],
                    normalizeFunction: 'polynomial'
                }]
            },
            onRegionTipShow: function(e, el, code) {
                var count = {!! json_encode($mapData) !!}[code] || 0;
                el.html(el.html() + ': ' + count + ' 个风险事件');
            }
        });
    });
</script>
@endsection 