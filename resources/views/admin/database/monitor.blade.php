@extends("admin.layouts.app")

@section("title", "数据库性能监控")

@section("content")
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>数据库性能监控</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">首页</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.database.index') }}">数据库超级运维</a></li>
                <li class="breadcrumb-item active">性能监控</li>
            </ol>
        </div>
    </div>

    <!-- 性能概览 -->
    <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">运行时间</span>
                    <span class="info-box-number">{{ $performance['uptime'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-database"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">查询数</span>
                    <span class="info-box-number">{{ number_format($performance['questions']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">慢查询数</span>
                    <span class="info-box-number">{{ number_format($performance['slow_queries']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-plug"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">当前连接数</span>
                    <span class="info-box-number">{{ $connections['current_connections'] }} / {{ $connections['max_connections'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 连接状态 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">连接状态</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="chart-responsive">
                        <canvas id="connectionsChart" height="200"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th>最大连接数</th>
                            <td>{{ $connections['max_connections'] }}</td>
                        </tr>
                        <tr>
                            <th>已使用最大连接数</th>
                            <td>{{ $connections['max_used_connections'] }}</td>
                        </tr>
                        <tr>
                            <th>当前连接数</th>
                            <td>{{ $connections['current_connections'] }}</td>
                        </tr>
                        <tr>
                            <th>连接错误数</th>
                            <td>{{ $connections['connection_errors'] }}</td>
                        </tr>
                        <tr>
                            <th>中断的客户端连接</th>
                            <td>{{ $connections['aborted_clients'] }}</td>
                        </tr>
                        <tr>
                            <th>中断的连接尝试</th>
                            <td>{{ $connections['aborted_connects'] }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 查询性能 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">查询性能</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th>总查询数</th>
                            <td>{{ number_format($statusArray['Questions'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <th>每秒查询数</th>
                            <td>{{ number_format($statusArray['Questions'] / $statusArray['Uptime'], 2) }}</td>
                        </tr>
                        <tr>
                            <th>慢查询数</th>
                            <td>{{ number_format($statusArray['Slow_queries'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <th>SELECT查询数</th>
                            <td>{{ number_format($statusArray['Com_select'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <th>INSERT查询数</th>
                            <td>{{ number_format($statusArray['Com_insert'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <th>UPDATE查询数</th>
                            <td>{{ number_format($statusArray['Com_update'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <th>DELETE查询数</th>
                            <td>{{ number_format($statusArray['Com_delete'] ?? 0) }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <div class="chart-responsive">
                        <canvas id="queriesChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 缓存状态 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">缓存状态</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="chart-responsive">
                        <canvas id="cacheChart" height="200"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th>查询缓存大小</th>
                            <td>{{ number_format(($statusArray['Qcache_free_memory'] ?? 0) / 1024 / 1024, 2) }} MB</td>
                        </tr>
                        <tr>
                            <th>查询缓存命中数</th>
                            <td>{{ number_format($statusArray['Qcache_hits'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <th>查询缓存未命中数</th>
                            <td>{{ number_format($statusArray['Qcache_inserts'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <th>查询缓存命中率</th>
                            @php
                                $cacheHits = $statusArray['Qcache_hits'] ?? 0;
                                $cacheMisses = $statusArray['Qcache_inserts'] ?? 0;
                                $cacheTotal = $cacheHits + $cacheMisses;
                                $cacheHitRate = $cacheTotal > 0 ? round(($cacheHits / $cacheTotal) * 100, 2) : 0;
                            @endphp
                            <td>{{ $cacheHitRate }}%</td>
                        </tr>
                        <tr>
                            <th>表缓存命中率</th>
                            @php
                                $tableHits = $statusArray['Table_open_cache_hits'] ?? 0;
                                $tableMisses = $statusArray['Table_open_cache_misses'] ?? 0;
                                $tableTotal = $tableHits + $tableMisses;
                                $tableHitRate = $tableTotal > 0 ? round(($tableHits / $tableTotal) * 100, 2) : 0;
                            @endphp
                            <td>{{ $tableHitRate }}%</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 当前进程列表 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">当前进程列表</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
                <button type="button" class="btn btn-tool" id="refresh-processes">
                    <i class="fas fa-sync"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="processes-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>用户</th>
                            <th>主机</th>
                            <th>数据库</th>
                            <th>命令</th>
                            <th>时间</th>
                            <th>状态</th>
                            <th>查询</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($processes as $process)
                            <tr>
                                <td>{{ $process->Id }}</td>
                                <td>{{ $process->User }}</td>
                                <td>{{ $process->Host }}</td>
                                <td>{{ $process->db }}</td>
                                <td>{{ $process->Command }}</td>
                                <td>{{ $process->Time }}</td>
                                <td>{{ $process->State }}</td>
                                <td>
                                    <span title="{{ $process->Info }}">
                                        {{ \Illuminate\Support\Str::limit($process->Info, 50) }}
                                    </span>
                                </td>
                                <td>
                                    @if($process->Command != 'Sleep' && $process->Time > 0)
                                        <button type="button" class="btn btn-sm btn-danger kill-process" data-id="{{ $process->Id }}">
                                            <i class="fas fa-times"></i> 终止
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- InnoDB状态 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">InnoDB状态</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <pre style="max-height: 400px; overflow: auto;">{{ $innodbStatus }}</pre>
        </div>
    </div>
</div>

<!-- 进程详情模态框 -->
<div class="modal fade" id="process-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">进程详情</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="process-details"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<!-- 终止进程确认模态框 -->
<div class="modal fade" id="kill-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-danger">终止进程</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>您确定要终止进程 <strong id="kill-process-id"></strong> 吗？</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-1"></i> 警告：终止进程可能会导致正在执行的操作失败。
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" id="confirm-kill">确认终止</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section("styles")
<style>
    pre {
        background-color: #f8f9fa;
        padding: 10px;
        border-radius: 4px;
    }
</style>
@endsection

@section("scripts")
<script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
<script>
    $(function () {
        // 表格初始化
        $('#processes-table').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Chinese.json"
            }
        });
        
        // 连接状态图表
        var connectionsChartCanvas = document.getElementById('connectionsChart').getContext('2d');
        var connectionsChart = new Chart(connectionsChartCanvas, {
            type: 'doughnut',
            data: {
                labels: ['当前连接数', '剩余可用连接数'],
                datasets: [{
                    data: [
                        {{ $connections['current_connections'] }},
                        {{ $connections['max_connections'] - $connections['current_connections'] }}
                    ],
                    backgroundColor: ['#17a2b8', '#dee2e6'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'right'
                },
                title: {
                    display: true,
                    text: '数据库连接使用情况'
                }
            }
        });
        
        // 查询性能图表
        var queriesChartCanvas = document.getElementById('queriesChart').getContext('2d');
        var queriesChart = new Chart(queriesChartCanvas, {
            type: 'bar',
            data: {
                labels: ['SELECT', 'INSERT', 'UPDATE', 'DELETE'],
                datasets: [{
                    label: '查询数量',
                    data: [
                        {{ $statusArray['Com_select'] ?? 0 }},
                        {{ $statusArray['Com_insert'] ?? 0 }},
                        {{ $statusArray['Com_update'] ?? 0 }},
                        {{ $statusArray['Com_delete'] ?? 0 }}
                    ],
                    backgroundColor: [
                        'rgba(60, 141, 188, 0.8)',
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ],
                    borderColor: [
                        'rgba(60, 141, 188, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
        
        // 缓存状态图表
        var cacheChartCanvas = document.getElementById('cacheChart').getContext('2d');
        var cacheChart = new Chart(cacheChartCanvas, {
            type: 'pie',
            data: {
                labels: ['查询缓存命中', '查询缓存未命中'],
                datasets: [{
                    data: [
                        {{ $statusArray['Qcache_hits'] ?? 0 }},
                        {{ $statusArray['Qcache_inserts'] ?? 0 }}
                    ],
                    backgroundColor: ['#28a745', '#dc3545'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'right'
                },
                title: {
                    display: true,
                    text: '查询缓存命中率'
                }
            }
        });
        
        // 刷新进程列表
        $('#refresh-processes').click(function() {
            location.reload();
        });
        
        // 终止进程
        $('.kill-process').click(function() {
            var processId = $(this).data('id');
            $('#kill-process-id').text(processId);
            $('#kill-modal').modal('show');
        });
        
        // 确认终止进程
        $('#confirm-kill').click(function() {
            var processId = $('#kill-process-id').text();
            
            $.ajax({
                url: '{{ route("admin.database.kill-process") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    process_id: processId
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('进程已成功终止');
                        $('#kill-modal').modal('hide');
                        
                        // 刷新进程列表
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error('终止进程失败: ' + response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error('终止进程失败: ' + xhr.responseText);
                }
            });
        });
        
        // 自动刷新（每60秒）
        setTimeout(function() {
            location.reload();
        }, 60000);
    });
</script>
@endsection 