@extends('admin.layouts.app')

@section('title', '类似错误日志')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">原始错误日志</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.management.logs.show', $originalLog->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> 查看详情
                        </a>
                        <a href="{{ route('admin.management.logs.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left"></i> 返回列表
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 150px;">日志ID</th>
                                    <td>{{ $originalLog->id }}</td>
                                </tr>
                                <tr>
                                    <th>日志级别</th>
                                    <td>
                                        <span class="badge badge-danger">错误</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>创建时间</th>
                                    <td>{{ $originalLog->created_at }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 150px;">错误类型</th>
                                    <td>{{ $errorType }}</td>
                                </tr>
                                <tr>
                                    <th>错误文件</th>
                                    <td style="word-break: break-all;">{{ $errorFile }}</td>
                                </tr>
                                <tr>
                                    <th>错误行号</th>
                                    <td>{{ $errorLine ?: '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">错误信息</h3>
                                </div>
                                <div class="card-body">
                                    <div class="p-3 bg-light">
                                        <pre style="white-space: pre-wrap;">{{ $originalLog->message }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">类似错误日志列表</h3>
                    <div class="card-tools">
                        <span class="badge badge-info">共找到 {{ $similarLogs->total() }} 条类似错误</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">ID</th>
                                    <th>错误信息</th>
                                    <th style="width: 150px;">用户</th>
                                    <th style="width: 120px;">IP地址</th>
                                    <th style="width: 170px;">时间</th>
                                    <th style="width: 80px;">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($similarLogs as $log)
                                <tr class="{{ $log->id == $originalLog->id ? 'table-primary' : '' }}">
                                    <td>{{ $log->id }}</td>
                                    <td>
                                        <span class="log-message">{{ \Illuminate\Support\Str::limit($log->message, 100) }}</span>
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
                <div class="card-footer clearfix">
                    {{ $similarLogs->links() }}
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">错误频率统计</h3>
                </div>
                <div class="card-body">
                    <canvas id="errorFrequencyChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">错误分布</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-calendar-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">首次出现</span>
                                    <span class="info-box-number">{{ $firstOccurrence }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-calendar-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">最近出现</span>
                                    <span class="info-box-number">{{ $lastOccurrence }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">总计出现</span>
                                    <span class="info-box-number">{{ $totalOccurrences }} 次</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">影响用户数</span>
                                    <span class="info-box-number">{{ $affectedUsers }} 人</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h5>受影响的URL</h5>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>URL</th>
                                    <th>出现次数</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($affectedUrls as $url => $count)
                                <tr>
                                    <td style="word-break: break-all;">{{ $url }}</td>
                                    <td>{{ $count }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @if($possibleSolutions)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">可能的解决方案</h3>
                </div>
                <div class="card-body">
                    <div class="callout callout-info">
                        <h5>系统分析</h5>
                        <p>基于错误类型和出现频率，系统提供以下可能的解决方案：</p>
                        <ul>
                            @foreach($possibleSolutions as $solution)
                            <li>{!! $solution !!}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(function() {
        // 错误频率图表
        var ctx = document.getElementById('errorFrequencyChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($frequencyData['labels']) !!},
                datasets: [{
                    label: '错误出现次数',
                    data: {!! json_encode($frequencyData['data']) !!},
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            },
                            label: function(context) {
                                return '出现次数: ' + context.raw + ' 次';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection 