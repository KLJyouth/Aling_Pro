@extends('admin.layouts.app')

@section('title', 'API监控仪表板')

@section('content')
<div class="container-fluid">
    <!-- 顶部信息卡片 -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($todayRequests) }}</h3>
                    <p>今日API请求</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <a href="{{ route('admin.security.api.dashboard.requests') }}" class="small-box-footer">
                    查看详情 <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($todayRiskEvents) }}</h3>
                    <p>今日风险事件</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <a href="{{ route('admin.security.api.risk-events.index', ['date_range' => now()->format('Y-m-d').' - '.now()->format('Y-m-d')]) }}" class="small-box-footer">
                    查看详情 <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($todayBlockedRequests) }}</h3>
                    <p>今日已阻止请求</p>
                </div>
                <div class="icon">
                    <i class="fas fa-ban"></i>
                </div>
                <a href="{{ route('admin.security.api.risk-events.index', ['date_range' => now()->format('Y-m-d').' - '.now()->format('Y-m-d'), 'action_taken' => 'blocked']) }}" class="small-box-footer">
                    查看详情 <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($activeInterfaces) }}</h3>
                    <p>活跃接口数</p>
                </div>
                <div class="icon">
                    <i class="fas fa-plug"></i>
                </div>
                <a href="{{ route('admin.security.api.interfaces.index') }}" class="small-box-footer">
                    查看详情 <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- 实时监控和系统状态 -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-1"></i>
                        API请求实时监控
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="realtimeChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-sm-3 col-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-info">
                                    <i class="fas fa-caret-{{ $requestTrend > 0 ? 'up' : 'down' }}"></i> {{ abs($requestTrend) }}%
                                </span>
                                <h5 class="description-header">{{ number_format($lastHourRequests) }}</h5>
                                <span class="description-text">过去1小时请求</span>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-warning">
                                    <i class="fas fa-caret-{{ $riskTrend > 0 ? 'up' : 'down' }}"></i> {{ abs($riskTrend) }}%
                                </span>
                                <h5 class="description-header">{{ number_format($lastHourRiskEvents) }}</h5>
                                <span class="description-text">过去1小时风险事件</span>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-success">
                                    <i class="fas fa-caret-{{ $successTrend > 0 ? 'up' : 'down' }}"></i> {{ abs($successTrend) }}%
                                </span>
                                <h5 class="description-header">{{ number_format($successRate, 2) }}%</h5>
                                <span class="description-text">请求成功率</span>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="description-block">
                                <span class="description-percentage text-danger">
                                    <i class="fas fa-caret-{{ $errorTrend < 0 ? 'down' : 'up' }}"></i> {{ abs($errorTrend) }}%
                                </span>
                                <h5 class="description-header">{{ number_format($errorRate, 2) }}%</h5>
                                <span class="description-text">请求错误率</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shield-alt mr-1"></i>
                        系统安全状态
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <input type="text" class="knob" value="{{ $securityScore }}" data-width="120" data-height="120" data-fgColor="{{ $securityScoreColor }}" data-readonly="true">
                        <div class="knob-label">安全评分</div>
                    </div>
                    
                    <div class="progress-group">
                        <span class="progress-text">活跃风控规则</span>
                        <span class="float-right"><b>{{ $activeRules }}</b>/{{ $totalRules }}</span>
                        <div class="progress">
                            <div class="progress-bar bg-primary" style="width: {{ ($activeRules / max(1, $totalRules)) * 100 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="progress-group">
                        <span class="progress-text">黑名单IP数量</span>
                        <span class="float-right"><b>{{ $blacklistCount }}</b></span>
                        <div class="progress">
                            <div class="progress-bar bg-dark" style="width: {{ min(100, ($blacklistCount / 100) * 100) }}%"></div>
                        </div>
                    </div>
                    
                    <div class="progress-group">
                        <span class="progress-text">今日拦截率</span>
                        <span class="float-right"><b>{{ number_format($blockRate, 2) }}%</b></span>
                        <div class="progress">
                            <div class="progress-bar bg-danger" style="width: {{ $blockRate }}%"></div>
                        </div>
                    </div>
                    
                    <div class="progress-group">
                        <span class="progress-text">接口覆盖率</span>
                        <span class="float-right"><b>{{ number_format($interfaceCoverage, 2) }}%</b></span>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: {{ $interfaceCoverage }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-sm-6">
                            <a href="{{ route('admin.security.api.risk-rules.index') }}" class="btn btn-block btn-outline-primary">
                                <i class="fas fa-cog"></i> 管理风控规则
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a href="{{ route('admin.security.api.dashboard.realtime') }}" class="btn btn-block btn-outline-danger">
                                <i class="fas fa-eye"></i> 实时监控
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-radiation mr-1"></i>
                        高风险告警
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="products-list product-list-in-card pl-2 pr-2">
                        @if(count($recentHighRiskEvents) > 0)
                            @foreach($recentHighRiskEvents as $event)
                            <li class="item">
                                <div class="product-img">
                                    <i class="fas fa-radiation text-danger fa-2x"></i>
                                </div>
                                <div class="product-info">
                                    <a href="{{ route('admin.security.api.risk-events.show', $event->id) }}" class="product-title">
                                        {{ Str::limit($event->risk_type, 30) }}
                                        <span class="badge badge-danger float-right">{{ $event->created_at->diffForHumans() }}</span>
                                    </a>
                                    <span class="product-description">
                                        IP: {{ $event->ip_address }} 
                                        @if($event->api_interface)
                                        | 接口: {{ $event->api_interface->name }}
                                        @endif
                                    </span>
                                </div>
                            </li>
                            @endforeach
                        @else
                            <li class="item">
                                <div class="text-center p-3">
                                    <i class="fas fa-check-circle text-success"></i> 暂无高风险告警
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('admin.security.api.risk-events.index', ['risk_level' => 'high']) }}" class="text-danger">查看所有高风险事件</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 接口调用统计和风险分布 -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-1"></i>
                        接口调用TOP 10
                    </h3>
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
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-1"></i>
                        风险类型分布
                    </h3>
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
    </div>
    
    <!-- 最近风险事件和响应时间 -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-1"></i>
                        最近风险事件
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>时间</th>
                                    <th>接口</th>
                                    <th>IP地址</th>
                                    <th>风险类型</th>
                                    <th>风险等级</th>
                                    <th>处理结果</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentRiskEvents as $event)
                                <tr>
                                    <td>{{ $event->created_at }}</td>
                                    <td>
                                        @if($event->api_interface)
                                        {{ $event->api_interface->name }}
                                        @else
                                        未知接口
                                        @endif
                                    </td>
                                    <td>{{ $event->ip_address }}</td>
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
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    <a href="{{ route('admin.security.api.risk-events.index') }}" class="btn btn-sm btn-primary float-right">
                        查看所有风险事件
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tachometer-alt mr-1"></i>
                        接口响应时间
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="responseTimeChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-sm-4 text-center">
                            <div class="text-warning">
                                <i class="fas fa-bolt"></i> 最快
                            </div>
                            <div class="text-value">{{ number_format($fastestResponseTime, 2) }} ms</div>
                        </div>
                        <div class="col-sm-4 text-center">
                            <div class="text-info">
                                <i class="fas fa-clock"></i> 平均
                            </div>
                            <div class="text-value">{{ number_format($avgResponseTime, 2) }} ms</div>
                        </div>
                        <div class="col-sm-4 text-center">
                            <div class="text-danger">
                                <i class="fas fa-exclamation-circle"></i> 最慢
                            </div>
                            <div class="text-value">{{ number_format($slowestResponseTime, 2) }} ms</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        请求地理分布
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="world-map" style="height: 200px;"></div>
                </div>
                <div class="card-footer p-0">
                    <div class="table-responsive">
                        <table class="table table-striped m-0">
                            <thead>
                                <tr>
                                    <th>国家/地区</th>
                                    <th>请求数</th>
                                    <th>占比</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topLocations as $location)
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
@endsection

@section('scripts')
<script>
    $(function() {
        // 实时监控图表
        var realtimeCtx = document.getElementById('realtimeChart').getContext('2d');
        var realtimeChart = new Chart(realtimeCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($realtimeData['labels']) !!},
                datasets: [{
                    label: '请求数',
                    data: {!! json_encode($realtimeData['requests']) !!},
                    borderColor: '#3490dc',
                    backgroundColor: 'rgba(52, 144, 220, 0.1)',
                    borderWidth: 2,
                    fill: true
                }, {
                    label: '风险事件',
                    data: {!! json_encode($realtimeData['risks']) !!},
                    borderColor: '#f6993f',
                    backgroundColor: 'rgba(246, 153, 63, 0.1)',
                    borderWidth: 2,
                    fill: true
                }, {
                    label: '已阻止',
                    data: {!! json_encode($realtimeData['blocked']) !!},
                    borderColor: '#e3342f',
                    backgroundColor: 'rgba(227, 52, 47, 0.1)',
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
        
        // 接口调用统计图表
        var interfaceCtx = document.getElementById('interfaceChart').getContext('2d');
        var interfaceChart = new Chart(interfaceCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($interfaceData['labels']) !!},
                datasets: [{
                    label: '请求数',
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
        
        // 风险类型分布图表
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
        
        // 接口响应时间图表
        var responseTimeCtx = document.getElementById('responseTimeChart').getContext('2d');
        var responseTimeChart = new Chart(responseTimeCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($responseTimeData['labels']) !!},
                datasets: [{
                    label: '平均响应时间(ms)',
                    data: {!! json_encode($responseTimeData['data']) !!},
                    borderColor: '#38c172',
                    backgroundColor: 'rgba(56, 193, 114, 0.1)',
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
        
        // 安全评分表盘
        $('.knob').knob({
            'readOnly': true,
            'width': 120,
            'height': 120,
            'thickness': 0.2,
            'angleArc': 250,
            'angleOffset': -125,
            'min': 0,
            'max': 100,
            'format': function(value) {
                return value + '%';
            }
        });
        
        // 地理分布图
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
                el.html(el.html() + ': ' + count + ' 个请求');
            }
        });
        
        // 自动刷新
        setTimeout(function() {
            location.reload();
        }, 300000); // 5分钟刷新一次
    });
</script>
@endsection 