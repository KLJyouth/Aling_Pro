@extends("admin.layouts.app")

@section("title", "AI使用统计")

@section("content_header")
    <h1>AI使用统计</h1>
@stop

@section("content")
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalRequests }}</h3>
                    <p>总请求次数</p>
                </div>
                <div class="icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $successRate }}%</h3>
                    <p>成功率</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totalTokens }}</h3>
                    <p>总标记数</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $avgResponseTime }}ms</h3>
                    <p>平均响应时间</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">提供商使用分布</h3>
                </div>
                <div class="card-body">
                    <canvas id="providerChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">模型使用分布</h3>
                </div>
                <div class="card-body">
                    <canvas id="modelChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">每日请求量</h3>
                </div>
                <div class="card-body">
                    <canvas id="requestsChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">最近使用记录</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>提供商</th>
                        <th>智能体</th>
                        <th>用户</th>
                        <th>请求时间</th>
                        <th>响应时间</th>
                        <th>输入标记数</th>
                        <th>输出标记数</th>
                        <th>状态</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentRecords as $record)
                        <tr>
                            <td>{{ $record->id }}</td>
                            <td>{{ $record->provider->name }}</td>
                            <td>{{ $record->agent ? $record->agent->name : "未知" }}</td>
                            <td>{{ $record->user ? $record->user->name : "系统" }}</td>
                            <td>{{ $record->created_at }}</td>
                            <td>{{ $record->response_time }}ms</td>
                            <td>{{ $record->input_tokens }}</td>
                            <td>{{ $record->output_tokens }}</td>
                            <td>
                                @if($record->status == "success")
                                    <span class="badge badge-success">成功</span>
                                @elseif($record->status == "error")
                                    <span class="badge badge-danger">错误</span>
                                @else
                                    <span class="badge badge-warning">处理中</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            {{ $recentRecords->links() }}
        </div>
    </div>
@stop

@section("js")
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
    $(function() {
        // 提供商使用分布图
        const providerCtx = document.getElementById("providerChart").getContext("2d");
        new Chart(providerCtx, {
            type: "pie",
            data: {
                labels: {!! json_encode($providerStats->pluck("name")) !!},
                datasets: [{
                    data: {!! json_encode($providerStats->pluck("count")) !!},
                    backgroundColor: [
                        "#3498db", "#2ecc71", "#e74c3c", "#f39c12", "#9b59b6",
                        "#1abc9c", "#d35400", "#34495e", "#16a085", "#27ae60"
                    ]
                }]
            },
            options: {
                responsive: true,
                legend: {
                    position: "right"
                }
            }
        });
        
        // 模型使用分布图
        const modelCtx = document.getElementById("modelChart").getContext("2d");
        new Chart(modelCtx, {
            type: "pie",
            data: {
                labels: {!! json_encode($modelStats->pluck("name")) !!},
                datasets: [{
                    data: {!! json_encode($modelStats->pluck("count")) !!},
                    backgroundColor: [
                        "#3498db", "#2ecc71", "#e74c3c", "#f39c12", "#9b59b6",
                        "#1abc9c", "#d35400", "#34495e", "#16a085", "#27ae60"
                    ]
                }]
            },
            options: {
                responsive: true,
                legend: {
                    position: "right"
                }
            }
        });
        
        // 每日请求量图
        const requestsCtx = document.getElementById("requestsChart").getContext("2d");
        new Chart(requestsCtx, {
            type: "line",
            data: {
                labels: {!! json_encode($dailyStats->pluck("date")) !!},
                datasets: [{
                    label: "请求次数",
                    data: {!! json_encode($dailyStats->pluck("count")) !!},
                    borderColor: "#3498db",
                    backgroundColor: "rgba(52, 152, 219, 0.1)",
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: "日期"
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: "请求次数"
                        },
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
    });
</script>
@stop
