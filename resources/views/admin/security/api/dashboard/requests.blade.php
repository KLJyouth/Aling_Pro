@extends('admin.layouts.app')

@section('title', 'API请求统计')

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
                    <form action="{{ route('admin.security.api.dashboard.requests') }}" method="GET" id="filter-form">
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
                                        <input type="text" class="form-control float-right" id="date-range" name="date_range" value="{{ request('date_range', now()->subDays(7)->format('Y-m-d').' - '.now()->format('Y-m-d')) }}">
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
                                    <label>状态码</label>
                                    <select name="status_code" class="form-control">
                                        <option value="">所有状态码</option>
                                        <option value="2xx" {{ request('status_code') == '2xx' ? 'selected' : '' }}>2xx (成功)</option>
                                        <option value="3xx" {{ request('status_code') == '3xx' ? 'selected' : '' }}>3xx (重定向)</option>
                                        <option value="4xx" {{ request('status_code') == '4xx' ? 'selected' : '' }}>4xx (客户端错误)</option>
                                        <option value="5xx" {{ request('status_code') == '5xx' ? 'selected' : '' }}>5xx (服务器错误)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>请求方法</label>
                                    <select name="method" class="form-control">
                                        <option value="">所有方法</option>
                                        <option value="GET" {{ request('method') == 'GET' ? 'selected' : '' }}>GET</option>
                                        <option value="POST" {{ request('method') == 'POST' ? 'selected' : '' }}>POST</option>
                                        <option value="PUT" {{ request('method') == 'PUT' ? 'selected' : '' }}>PUT</option>
                                        <option value="DELETE" {{ request('method') == 'DELETE' ? 'selected' : '' }}>DELETE</option>
                                        <option value="PATCH" {{ request('method') == 'PATCH' ? 'selected' : '' }}>PATCH</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>时间间隔</label>
                                    <select name="interval" class="form-control">
                                        <option value="hour" {{ request('interval', 'hour') == 'hour' ? 'selected' : '' }}>小时</option>
                                        <option value="day" {{ request('interval') == 'day' ? 'selected' : '' }}>天</option>
                                        <option value="week" {{ request('interval') == 'week' ? 'selected' : '' }}>周</option>
                                        <option value="month" {{ request('interval') == 'month' ? 'selected' : '' }}>月</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> 应用筛选
                                </button>
                                <a href="{{ route('admin.security.api.dashboard.requests') }}" class="btn btn-default">
                                    <i class="fas fa-redo"></i> 重置
                                </a>
                                <button type="button" class="btn btn-success" id="btn-export">
                                    <i class="fas fa-download"></i> 导出数据
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 请求统计图表 -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-1"></i>
                        API请求统计
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="requestChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-sm-3 col-6">
                            <div class="description-block border-right">
                                <h5 class="description-header">{{ number_format($totalRequests) }}</h5>
                                <span class="description-text">总请求数</span>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="description-block border-right">
                                <h5 class="description-header">{{ number_format($successRequests) }}</h5>
                                <span class="description-text">成功请求数</span>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="description-block border-right">
                                <h5 class="description-header">{{ number_format($errorRequests) }}</h5>
                                <span class="description-text">错误请求数</span>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="description-block">
                                <h5 class="description-header">{{ number_format($avgResponseTime, 2) }} ms</h5>
                                <span class="description-text">平均响应时间</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 请求状态和方法分布 -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-1"></i>
                        状态码分布
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="statusChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-1"></i>
                        请求方法分布
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="methodChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 接口调用统计和响应时间 -->
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
                        <i class="fas fa-tachometer-alt mr-1"></i>
                        接口响应时间TOP 10
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="responseTimeChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 详细数据表格 -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table mr-1"></i>
                        接口请求详情
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
                                    <th>接口名称</th>
                                    <th>请求数</th>
                                    <th>成功率</th>
                                    <th>平均响应时间</th>
                                    <th>错误数</th>
                                    <th>风险事件数</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($interfaceStats as $stat)
                                <tr>
                                    <td>{{ $stat->name }}</td>
                                    <td>{{ number_format($stat->request_count) }}</td>
                                    <td>
                                        <div class="progress progress-xs">
                                            <div class="progress-bar bg-success" style="width: {{ $stat->success_rate }}%"></div>
                                        </div>
                                        <span class="badge bg-success">{{ number_format($stat->success_rate, 2) }}%</span>
                                    </td>
                                    <td>
                                        {{ number_format($stat->avg_response_time, 2) }} ms
                                        @if($stat->avg_response_time > 500)
                                        <span class="badge badge-danger">慢</span>
                                        @elseif($stat->avg_response_time > 200)
                                        <span class="badge badge-warning">中</span>
                                        @else
                                        <span class="badge badge-success">快</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($stat->error_count) }}</td>
                                    <td>{{ number_format($stat->risk_count) }}</td>
                                    <td>
                                        <a href="{{ route('admin.security.api.interfaces.show', $stat->id) }}" class="btn btn-xs btn-info">
                                            <i class="fas fa-eye"></i> 查看
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
        
        // 导出数据
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
        
        // 请求统计图表
        var requestCtx = document.getElementById('requestChart').getContext('2d');
        var requestChart = new Chart(requestCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: [{
                    label: '总请求数',
                    data: {!! json_encode($chartData['total']) !!},
                    borderColor: '#3490dc',
                    backgroundColor: 'rgba(52, 144, 220, 0.1)',
                    borderWidth: 2,
                    fill: true
                }, {
                    label: '成功请求',
                    data: {!! json_encode($chartData['success']) !!},
                    borderColor: '#38c172',
                    backgroundColor: 'rgba(56, 193, 114, 0.1)',
                    borderWidth: 2,
                    fill: true
                }, {
                    label: '错误请求',
                    data: {!! json_encode($chartData['error']) !!},
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
        
        // 状态码分布图表
        var statusCtx = document.getElementById('statusChart').getContext('2d');
        var statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['2xx (成功)', '3xx (重定向)', '4xx (客户端错误)', '5xx (服务器错误)'],
                datasets: [{
                    data: [
                        {{ $statusData['2xx'] }},
                        {{ $statusData['3xx'] }},
                        {{ $statusData['4xx'] }},
                        {{ $statusData['5xx'] }}
                    ],
                    backgroundColor: [
                        '#38c172', // 绿色 - 成功
                        '#3490dc', // 蓝色 - 重定向
                        '#f6993f', // 橙色 - 客户端错误
                        '#e3342f'  // 红色 - 服务器错误
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
        
        // 请求方法分布图表
        var methodCtx = document.getElementById('methodChart').getContext('2d');
        var methodChart = new Chart(methodCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode(array_keys($methodData)) !!},
                datasets: [{
                    data: {!! json_encode(array_values($methodData)) !!},
                    backgroundColor: [
                        '#3490dc', // GET
                        '#38c172', // POST
                        '#f6993f', // PUT
                        '#e3342f', // DELETE
                        '#6c757d'  // 其他
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
        
        // 接口响应时间图表
        var responseTimeCtx = document.getElementById('responseTimeChart').getContext('2d');
        var responseTimeChart = new Chart(responseTimeCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($responseTimeData['labels']) !!},
                datasets: [{
                    label: '平均响应时间(ms)',
                    data: {!! json_encode($responseTimeData['data']) !!},
                    backgroundColor: '#f6993f'
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
    });
</script>
@endsection 