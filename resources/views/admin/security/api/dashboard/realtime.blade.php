@extends('admin.layouts.app')

@section('title', 'API实时监控')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-1"></i>
                        API请求实时监控
                    </h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-tool dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-clock"></i> <span id="refresh-rate-text">5秒</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a href="#" class="dropdown-item refresh-rate" data-rate="1">1秒</a>
                                <a href="#" class="dropdown-item refresh-rate" data-rate="5">5秒</a>
                                <a href="#" class="dropdown-item refresh-rate" data-rate="10">10秒</a>
                                <a href="#" class="dropdown-item refresh-rate" data-rate="30">30秒</a>
                                <a href="#" class="dropdown-item refresh-rate" data-rate="60">1分钟</a>
                            </div>
                        </div>
                        <button type="button" class="btn btn-tool" id="toggle-pause">
                            <i class="fas fa-pause" id="pause-icon"></i>
                        </button>
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
                                <h5 class="description-header" id="current-requests">0</h5>
                                <span class="description-text">当前请求数/秒</span>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="description-block border-right">
                                <h5 class="description-header" id="current-risks">0</h5>
                                <span class="description-text">当前风险事件/秒</span>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="description-block border-right">
                                <h5 class="description-header" id="current-blocked">0</h5>
                                <span class="description-text">当前阻止请求/秒</span>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="description-block">
                                <h5 class="description-header" id="current-errors">0</h5>
                                <span class="description-text">当前错误请求/秒</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-1"></i>
                        实时请求日志
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" id="clear-logs">
                            <i class="fas fa-trash"></i> 清空
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped" id="request-logs-table">
                            <thead>
                                <tr>
                                    <th>时间</th>
                                    <th>接口</th>
                                    <th>方法</th>
                                    <th>IP</th>
                                    <th>状态</th>
                                    <th>响应时间</th>
                                </tr>
                            </thead>
                            <tbody id="request-logs">
                                <!-- 实时请求日志将在这里动态添加 -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        实时风险事件
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" id="clear-events">
                            <i class="fas fa-trash"></i> 清空
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped" id="risk-events-table">
                            <thead>
                                <tr>
                                    <th>时间</th>
                                    <th>接口</th>
                                    <th>IP</th>
                                    <th>风险类型</th>
                                    <th>风险等级</th>
                                    <th>处理结果</th>
                                </tr>
                            </thead>
                            <tbody id="risk-events">
                                <!-- 实时风险事件将在这里动态添加 -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-1"></i>
                        实时接口调用统计
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="interfaceChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tachometer-alt mr-1"></i>
                        实时响应时间监控
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
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-globe mr-1"></i>
                        实时地理分布
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="world-map" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // 初始化变量
        var refreshRate = 5; // 默认5秒刷新一次
        var isPaused = false;
        var refreshInterval;
        var maxDataPoints = 60; // 最多显示60个数据点
        
        // 初始化图表数据
        var chartData = {
            labels: Array(maxDataPoints).fill(''),
            requests: Array(maxDataPoints).fill(0),
            risks: Array(maxDataPoints).fill(0),
            blocked: Array(maxDataPoints).fill(0),
            errors: Array(maxDataPoints).fill(0)
        };
        
        // 接口调用统计数据
        var interfaceData = {
            labels: [],
            data: []
        };
        
        // 响应时间监控数据
        var responseTimeData = {
            labels: [],
            data: []
        };
        
        // 地理分布数据
        var mapData = {};
        
        // 初始化实时监控图表
        var realtimeCtx = document.getElementById('realtimeChart').getContext('2d');
        var realtimeChart = new Chart(realtimeCtx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: '请求数',
                    data: chartData.requests,
                    borderColor: '#3490dc',
                    backgroundColor: 'rgba(52, 144, 220, 0.1)',
                    borderWidth: 2,
                    fill: true
                }, {
                    label: '风险事件',
                    data: chartData.risks,
                    borderColor: '#f6993f',
                    backgroundColor: 'rgba(246, 153, 63, 0.1)',
                    borderWidth: 2,
                    fill: true
                }, {
                    label: '已阻止',
                    data: chartData.blocked,
                    borderColor: '#e3342f',
                    backgroundColor: 'rgba(227, 52, 47, 0.1)',
                    borderWidth: 2,
                    fill: true
                }, {
                    label: '错误请求',
                    data: chartData.errors,
                    borderColor: '#6c757d',
                    backgroundColor: 'rgba(108, 117, 125, 0.1)',
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
                },
                animation: {
                    duration: 0
                }
            }
        });
        
        // 初始化接口调用统计图表
        var interfaceCtx = document.getElementById('interfaceChart').getContext('2d');
        var interfaceChart = new Chart(interfaceCtx, {
            type: 'bar',
            data: {
                labels: interfaceData.labels,
                datasets: [{
                    label: '请求数',
                    data: interfaceData.data,
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
                animation: {
                    duration: 0
                }
            }
        });
        
        // 初始化响应时间监控图表
        var responseTimeCtx = document.getElementById('responseTimeChart').getContext('2d');
        var responseTimeChart = new Chart(responseTimeCtx, {
            type: 'line',
            data: {
                labels: responseTimeData.labels,
                datasets: [{
                    label: '平均响应时间(ms)',
                    data: responseTimeData.data,
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
                },
                animation: {
                    duration: 0
                }
            }
        });
        
        // 初始化地理分布图
        var worldMap = $('#world-map').vectorMap({
            map: 'world_mill',
            backgroundColor: 'transparent',
            series: {
                regions: [{
                    values: mapData,
                    scale: ['#C8EEFF', '#0071A4'],
                    normalizeFunction: 'polynomial'
                }]
            },
            onRegionTipShow: function(e, el, code) {
                var count = mapData[code] || 0;
                el.html(el.html() + ': ' + count + ' 个请求');
            }
        });
        
        // 更新数据函数
        function updateData() {
            if (isPaused) return;
            
            $.ajax({
                url: '{{ route("admin.security.api.dashboard.realtime-data") }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    // 更新图表数据
                    chartData.labels.shift();
                    chartData.requests.shift();
                    chartData.risks.shift();
                    chartData.blocked.shift();
                    chartData.errors.shift();
                    
                    var now = new Date();
                    var timeString = now.getHours() + ':' + (now.getMinutes() < 10 ? '0' : '') + now.getMinutes() + ':' + (now.getSeconds() < 10 ? '0' : '') + now.getSeconds();
                    
                    chartData.labels.push(timeString);
                    chartData.requests.push(data.current.requests);
                    chartData.risks.push(data.current.risks);
                    chartData.blocked.push(data.current.blocked);
                    chartData.errors.push(data.current.errors);
                    
                    realtimeChart.data.labels = chartData.labels;
                    realtimeChart.data.datasets[0].data = chartData.requests;
                    realtimeChart.data.datasets[1].data = chartData.risks;
                    realtimeChart.data.datasets[2].data = chartData.blocked;
                    realtimeChart.data.datasets[3].data = chartData.errors;
                    realtimeChart.update();
                    
                    // 更新当前数值
                    $('#current-requests').text(data.current.requests);
                    $('#current-risks').text(data.current.risks);
                    $('#current-blocked').text(data.current.blocked);
                    $('#current-errors').text(data.current.errors);
                    
                    // 更新接口调用统计
                    interfaceData.labels = data.interfaces.labels;
                    interfaceData.data = data.interfaces.data;
                    interfaceChart.data.labels = interfaceData.labels;
                    interfaceChart.data.datasets[0].data = interfaceData.data;
                    interfaceChart.update();
                    
                    // 更新响应时间监控
                    responseTimeData.labels = data.responseTimes.labels;
                    responseTimeData.data = data.responseTimes.data;
                    responseTimeChart.data.labels = responseTimeData.labels;
                    responseTimeChart.data.datasets[0].data = responseTimeData.data;
                    responseTimeChart.update();
                    
                    // 更新地理分布
                    mapData = data.geoData;
                    var map = $('#world-map').vectorMap('get', 'mapObject');
                    map.series.regions[0].setValues(mapData);
                    
                    // 添加新的请求日志
                    if (data.recentRequests && data.recentRequests.length > 0) {
                        var requestLogs = $('#request-logs');
                        data.recentRequests.forEach(function(req) {
                            var statusClass = '';
                            if (req.status >= 200 && req.status < 300) {
                                statusClass = 'success';
                            } else if (req.status >= 400 && req.status < 500) {
                                statusClass = 'warning';
                            } else if (req.status >= 500) {
                                statusClass = 'danger';
                            }
                            
                            var row = '<tr>' +
                                '<td>' + req.time + '</td>' +
                                '<td>' + req.interface + '</td>' +
                                '<td>' + req.method + '</td>' +
                                '<td>' + req.ip + '</td>' +
                                '<td><span class="badge badge-' + statusClass + '">' + req.status + '</span></td>' +
                                '<td>' + req.response_time + ' ms</td>' +
                                '</tr>';
                            
                            requestLogs.prepend(row);
                            
                            // 限制最多显示50条记录
                            if (requestLogs.children().length > 50) {
                                requestLogs.children().last().remove();
                            }
                        });
                    }
                    
                    // 添加新的风险事件
                    if (data.recentRiskEvents && data.recentRiskEvents.length > 0) {
                        var riskEvents = $('#risk-events');
                        data.recentRiskEvents.forEach(function(event) {
                            var levelClass = '';
                            if (event.risk_level === 'high') {
                                levelClass = 'danger';
                            } else if (event.risk_level === 'medium') {
                                levelClass = 'warning';
                            } else {
                                levelClass = 'info';
                            }
                            
                            var actionClass = '';
                            var actionText = '';
                            if (event.action_taken === 'blocked') {
                                actionClass = 'danger';
                                actionText = '已阻止';
                            } else if (event.action_taken === 'blacklisted') {
                                actionClass = 'dark';
                                actionText = '已加入黑名单';
                            } else if (event.action_taken === 'captcha') {
                                actionClass = 'warning';
                                actionText = '要求验证码';
                            } else if (event.action_taken === 'logged') {
                                actionClass = 'info';
                                actionText = '已记录';
                            } else {
                                actionClass = 'secondary';
                                actionText = event.action_taken;
                            }
                            
                            var row = '<tr>' +
                                '<td>' + event.time + '</td>' +
                                '<td>' + event.interface + '</td>' +
                                '<td>' + event.ip + '</td>' +
                                '<td>' + event.risk_type + '</td>' +
                                '<td><span class="badge badge-' + levelClass + '">' + event.risk_level + '</span></td>' +
                                '<td><span class="badge badge-' + actionClass + '">' + actionText + '</span></td>' +
                                '</tr>';
                            
                            riskEvents.prepend(row);
                            
                            // 限制最多显示50条记录
                            if (riskEvents.children().length > 50) {
                                riskEvents.children().last().remove();
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching realtime data:', error);
                }
            });
        }
        
        // 设置刷新间隔
        function setRefreshInterval(rate) {
            clearInterval(refreshInterval);
            refreshRate = rate;
            $('#refresh-rate-text').text(rate + (rate === 1 ? '秒' : '秒'));
            
            if (!isPaused) {
                refreshInterval = setInterval(updateData, rate * 1000);
            }
        }
        
        // 初始化刷新间隔
        setRefreshInterval(refreshRate);
        
        // 立即获取一次数据
        updateData();
        
        // 刷新率切换
        $('.refresh-rate').click(function(e) {
            e.preventDefault();
            var rate = parseInt($(this).data('rate'));
            setRefreshInterval(rate);
        });
        
        // 暂停/继续按钮
        $('#toggle-pause').click(function() {
            isPaused = !isPaused;
            
            if (isPaused) {
                clearInterval(refreshInterval);
                $('#pause-icon').removeClass('fa-pause').addClass('fa-play');
            } else {
                refreshInterval = setInterval(updateData, refreshRate * 1000);
                $('#pause-icon').removeClass('fa-play').addClass('fa-pause');
                // 立即获取一次数据
                updateData();
            }
        });
        
        // 清空请求日志
        $('#clear-logs').click(function() {
            $('#request-logs').empty();
        });
        
        // 清空风险事件
        $('#clear-events').click(function() {
            $('#risk-events').empty();
        });
    });
</script>
@endsection 