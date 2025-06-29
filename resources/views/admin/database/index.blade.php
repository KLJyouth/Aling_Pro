@extends("admin.layouts.app")

@section("title", "数据库超级运维")

@section("content")
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>数据库超级运维</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">首页</a></li>
                <li class="breadcrumb-item active">数据库超级运维</li>
            </ol>
        </div>
    </div>

    <!-- 数据库概览 -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $tableCount }}</h3>
                    <p>数据表</p>
                </div>
                <div class="icon">
                    <i class="fas fa-table"></i>
                </div>
                <a href="{{ route('admin.database.tables') }}" class="small-box-footer">
                    查看详情 <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($dbSize, 2) }} <sup style="font-size: 20px">MB</sup></h3>
                    <p>数据库大小</p>
                </div>
                <div class="icon">
                    <i class="fas fa-database"></i>
                </div>
                <a href="{{ route('admin.database.structure') }}" class="small-box-footer">
                    查看结构 <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ count($backups) }}</h3>
                    <p>备份数量</p>
                </div>
                <div class="icon">
                    <i class="fas fa-save"></i>
                </div>
                <a href="{{ route('admin.database.backup.index') }}" class="small-box-footer">
                    管理备份 <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $systemStatus['connection_usage']['current'] }}/{{ $systemStatus['connection_usage']['max'] }}</h3>
                    <p>当前连接数</p>
                </div>
                <div class="icon">
                    <i class="fas fa-plug"></i>
                </div>
                <a href="{{ route('admin.database.monitor') }}" class="small-box-footer">
                    查看监控 <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 数据库信息 -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-1"></i>
                        数据库信息
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%">数据库版本</th>
                            <td>{{ $databaseInfo['version'] }}</td>
                        </tr>
                        <tr>
                            <th>数据库名称</th>
                            <td>{{ $databaseInfo['database'] }}</td>
                        </tr>
                        <tr>
                            <th>字符集</th>
                            <td>{{ $databaseInfo['charset'] }}</td>
                        </tr>
                        <tr>
                            <th>排序规则</th>
                            <td>{{ $databaseInfo['collation'] }}</td>
                        </tr>
                        <tr>
                            <th>连接信息</th>
                            <td>{{ $databaseInfo['connection']['host'] }}:{{ $databaseInfo['connection']['port'] }} ({{ $databaseInfo['connection']['username'] }})</td>
                        </tr>
                        <tr>
                            <th>运行时间</th>
                            <td>{{ $systemStatus['uptime'] }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- 系统状态 -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tachometer-alt mr-1"></i>
                        系统状态
                    </h3>
                </div>
                <div class="card-body">
                    <!-- 内存使用 -->
                    <div class="progress-group">
                        <span class="progress-text">内存使用</span>
                        <span class="float-right">{{ $systemStatus['memory_usage']['used'] }} MB / {{ $systemStatus['memory_usage']['total'] }} MB</span>
                        <div class="progress">
                            @php
                                $memoryPercent = $systemStatus['memory_usage']['total'] > 0 ? 
                                    min(100, round(($systemStatus['memory_usage']['used'] / $systemStatus['memory_usage']['total']) * 100)) : 0;
                            @endphp
                            <div class="progress-bar bg-primary" style="width: {{ $memoryPercent }}%"></div>
                        </div>
                    </div>
                    
                    <!-- 连接使用 -->
                    <div class="progress-group">
                        <span class="progress-text">连接使用</span>
                        <span class="float-right">{{ $systemStatus['connection_usage']['current'] }} / {{ $systemStatus['connection_usage']['max'] }}</span>
                        <div class="progress">
                            @php
                                $connectionPercent = $systemStatus['connection_usage']['max'] > 0 ? 
                                    min(100, round(($systemStatus['connection_usage']['current'] / $systemStatus['connection_usage']['max']) * 100)) : 0;
                            @endphp
                            <div class="progress-bar bg-warning" style="width: {{ $connectionPercent }}%"></div>
                        </div>
                    </div>
                    
                    <!-- 缓存命中率 -->
                    <div class="progress-group">
                        <span class="progress-text">缓存命中率</span>
                        @php
                            $cacheHits = $systemStatus['cache_usage']['query_cache_hits'];
                            $cacheMisses = $systemStatus['cache_usage']['query_cache_misses'];
                            $cacheTotal = $cacheHits + $cacheMisses;
                            $cacheHitRate = $cacheTotal > 0 ? round(($cacheHits / $cacheTotal) * 100) : 0;
                        @endphp
                        <span class="float-right">{{ $cacheHitRate }}%</span>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: {{ $cacheHitRate }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 快捷操作 -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-1"></i>
                        快捷操作
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('admin.database.backup.index') }}" class="btn btn-primary btn-block mb-3">
                                <i class="fas fa-download mr-1"></i> 备份管理
                            </a>
                            <a href="{{ route('admin.database.tables') }}" class="btn btn-info btn-block mb-3">
                                <i class="fas fa-table mr-1"></i> 表管理
                            </a>
                            <a href="{{ route('admin.database.structure') }}" class="btn btn-success btn-block mb-3">
                                <i class="fas fa-project-diagram mr-1"></i> 数据库结构
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('admin.database.monitor') }}" class="btn btn-warning btn-block mb-3">
                                <i class="fas fa-tachometer-alt mr-1"></i> 性能监控
                            </a>
                            <a href="{{ route('admin.database.slow-queries') }}" class="btn btn-danger btn-block mb-3">
                                <i class="fas fa-exclamation-triangle mr-1"></i> 慢查询分析
                            </a>
                            <button type="button" class="btn btn-secondary btn-block mb-3" id="optimize-db">
                                <i class="fas fa-broom mr-1"></i> 一键优化
                            </button>
                        </div>
                    </div>
                    <div class="mt-3">
                        <form id="sql-form">
                            <div class="form-group">
                                <label for="sql-query">快速执行SQL</label>
                                <textarea class="form-control" id="sql-query" rows="3" placeholder="输入SQL语句..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">执行</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 最近查询 -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-1"></i>
                        最近查询
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>查询语句</th>
                                <th>执行时间</th>
                                <th>影响行数</th>
                                <th>时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentQueries as $query)
                                <tr>
                                    <td>
                                        <span class="text-{{ $query->is_select ? 'info' : 'warning' }}">
                                            {{ \Illuminate\Support\Str::limit($query->query, 50) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($query->execution_time, 4) }}s</td>
                                    <td>{{ $query->affected_rows }}</td>
                                    <td>{{ \Carbon\Carbon::parse($query->created_at)->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">暂无查询记录</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- 最近备份 -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-save mr-1"></i>
                        最近备份
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>文件名</th>
                                <th>大小</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($backups as $backup)
                                <tr>
                                    <td>{{ $backup['filename'] }}</td>
                                    <td>{{ $backup['size'] }}</td>
                                    <td>{{ $backup['created_at'] }}</td>
                                    <td>
                                        <a href="{{ route('admin.database.backup.download', $backup['filename']) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">暂无备份记录</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SQL结果模态框 -->
<div class="modal fade" id="sql-result-modal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">SQL执行结果</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="sql-result-info mb-3">
                    <div class="alert alert-info">
                        <span id="sql-result-message"></span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="sql-result-table">
                        <thead></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<!-- 优化进度模态框 -->
<div class="modal fade" id="optimize-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">数据库优化</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                </div>
                <div class="mt-3" id="optimize-status">
                    <p>准备开始优化...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
    $(function () {
        // SQL执行
        $('#sql-form').submit(function(e) {
            e.preventDefault();
            
            var query = $('#sql-query').val();
            if (!query) {
                toastr.error('请输入SQL语句');
                return;
            }
            
            // 发送请求
            $.ajax({
                url: '{{ route("admin.database.execute-query") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    query: query
                },
                beforeSend: function() {
                    // 显示加载中
                    $('#sql-result-table thead').html('');
                    $('#sql-result-table tbody').html('<tr><td class="text-center">执行中...</td></tr>');
                    $('#sql-result-modal').modal('show');
                },
                success: function(response) {
                    if (response.success) {
                        if (response.is_select) {
                            // 显示查询结果
                            var results = response.results;
                            
                            if (results.length > 0) {
                                // 生成表头
                                var thead = '<tr>';
                                for (var key in results[0]) {
                                    thead += '<th>' + key + '</th>';
                                }
                                thead += '</tr>';
                                $('#sql-result-table thead').html(thead);
                                
                                // 生成表体
                                var tbody = '';
                                for (var i = 0; i < results.length; i++) {
                                    tbody += '<tr>';
                                    for (var key in results[i]) {
                                        tbody += '<td>' + (results[i][key] !== null ? results[i][key] : '<em>NULL</em>') + '</td>';
                                    }
                                    tbody += '</tr>';
                                }
                                $('#sql-result-table tbody').html(tbody);
                                
                                $('#sql-result-message').html('查询成功，返回 ' + results.length + ' 条记录，耗时 ' + response.execution_time + ' 秒');
                            } else {
                                $('#sql-result-table thead').html('');
                                $('#sql-result-table tbody').html('<tr><td class="text-center">查询结果为空</td></tr>');
                                $('#sql-result-message').html('查询成功，返回 0 条记录，耗时 ' + response.execution_time + ' 秒');
                            }
                        } else {
                            // 显示执行结果
                            $('#sql-result-table thead').html('');
                            $('#sql-result-table tbody').html('<tr><td class="text-center">执行成功</td></tr>');
                            $('#sql-result-message').html('执行成功，影响 ' + response.affected_rows + ' 条记录，耗时 ' + response.execution_time + ' 秒');
                        }
                    } else {
                        $('#sql-result-table thead').html('');
                        $('#sql-result-table tbody').html('<tr><td class="text-center text-danger">执行失败</td></tr>');
                        $('#sql-result-message').html('执行失败：' + response.error);
                    }
                },
                error: function(xhr) {
                    $('#sql-result-table thead').html('');
                    $('#sql-result-table tbody').html('<tr><td class="text-center text-danger">执行失败</td></tr>');
                    $('#sql-result-message').html('执行失败：' + xhr.responseJSON.error);
                }
            });
        });
        
        // 一键优化
        $('#optimize-db').click(function() {
            if (!confirm('确定要优化数据库吗？优化过程可能需要一些时间，并可能暂时锁定表。')) {
                return;
            }
            
            $('#optimize-modal').modal('show');
            var progress = 0;
            var progressBar = $('#optimize-modal .progress-bar');
            var statusText = $('#optimize-status');
            
            // 发送请求
            $.ajax({
                url: '{{ route("admin.database.optimize") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        progressBar.css('width', '100%');
                        statusText.html('<div class="alert alert-success">优化完成！</div>');
                        
                        // 显示详细结果
                        var resultHtml = '<div class="mt-3"><h5>优化结果：</h5><ul>';
                        for (var table in response.results) {
                            var result = response.results[table];
                            resultHtml += '<li>' + table + ': ' + 
                                (result.status === 'success' ? 
                                    '<span class="text-success">成功</span>' : 
                                    '<span class="text-danger">失败 - ' + result.message + '</span>') + 
                                '</li>';
                        }
                        resultHtml += '</ul></div>';
                        statusText.append(resultHtml);
                        
                        // 刷新页面
                        setTimeout(function() {
                            location.reload();
                        }, 3000);
                    } else {
                        progressBar.css('width', '100%').removeClass('bg-primary').addClass('bg-danger');
                        statusText.html('<div class="alert alert-danger">优化失败！</div>');
                    }
                },
                error: function(xhr) {
                    progressBar.css('width', '100%').removeClass('bg-primary').addClass('bg-danger');
                    statusText.html('<div class="alert alert-danger">优化失败：' + xhr.responseText + '</div>');
                },
                beforeSend: function() {
                    // 模拟进度
                    var interval = setInterval(function() {
                        progress += 5;
                        if (progress >= 90) {
                            clearInterval(interval);
                        }
                        progressBar.css('width', progress + '%');
                        statusText.html('<p>优化中，请稍候... ' + progress + '%</p>');
                    }, 300);
                }
            });
        });
    });
</script>
@endsection 