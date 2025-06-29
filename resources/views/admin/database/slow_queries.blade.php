@extends("admin.layouts.app")

@section("title", "慢查询分析")

@section("content")
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>慢查询分析</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">首页</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.database.index') }}">数据库超级运维</a></li>
                <li class="breadcrumb-item active">慢查询分析</li>
            </ol>
        </div>
    </div>

    <!-- 慢查询配置 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">慢查询配置</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-{{ $slowQueryEnabled ? 'success' : 'danger' }}">
                            <i class="fas fa-{{ $slowQueryEnabled ? 'check' : 'times' }}"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">慢查询日志状态</span>
                            <span class="info-box-number">{{ $slowQueryEnabled ? '已启用' : '未启用' }}</span>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-{{ $slowQueryEnabled ? 'danger' : 'success' }}" id="toggle-slow-query">
                                    {{ $slowQueryEnabled ? '禁用' : '启用' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-clock"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">慢查询阈值</span>
                            <span class="info-box-number">{{ $longQueryTime }} 秒</span>
                            <div class="mt-2">
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" id="long-query-time" min="0" step="0.1" value="{{ $longQueryTime }}">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-info" id="set-long-query-time">设置</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>慢查询日志文件位置</label>
                        <input type="text" class="form-control" value="{{ $slowQueryLogFile }}" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 慢查询列表 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">慢查询列表</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
                <button type="button" class="btn btn-tool" id="refresh-slow-queries">
                    <i class="fas fa-sync"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($slowQueryEnabled)
                @if(count($slowQueries) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="slow-queries-table">
                            <thead>
                                <tr>
                                    <th>执行时间</th>
                                    <th>用户@主机</th>
                                    <th>查询时间</th>
                                    <th>锁定时间</th>
                                    <th>发送行数</th>
                                    <th>扫描行数</th>
                                    <th>查询语句</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($slowQueries as $query)
                                    <tr>
                                        <td>{{ $query['time'] }}</td>
                                        <td>{{ $query['user_host'] }}</td>
                                        <td>{{ $query['query_time'] }}</td>
                                        <td>{{ $query['lock_time'] }}</td>
                                        <td>{{ $query['rows_sent'] }}</td>
                                        <td>{{ $query['rows_examined'] }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info view-query" data-query="{{ htmlspecialchars($query['query']) }}">
                                                查看
                                            </button>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary analyze-query" data-query="{{ htmlspecialchars($query['query']) }}">
                                                <i class="fas fa-search"></i> 分析
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i> 暂无慢查询记录。
                    </div>
                @endif
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-1"></i> 慢查询日志未启用，请先启用慢查询日志。
                </div>
            @endif
        </div>
    </div>

    <!-- 慢查询优化建议 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">慢查询优化建议</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <h5><i class="icon fas fa-info"></i> 优化建议</h5>
                <p>以下是一些常见的慢查询优化建议：</p>
                <ol>
                    <li>检查并优化查询语句，避免使用 SELECT * 语句</li>
                    <li>为经常查询的字段添加索引</li>
                    <li>避免在 WHERE 子句中使用函数或表达式</li>
                    <li>避免使用 NOT IN 和 OR 操作符</li>
                    <li>使用 EXPLAIN 分析查询执行计划</li>
                    <li>优化表结构，选择合适的字段类型</li>
                    <li>分解复杂查询为多个简单查询</li>
                    <li>定期优化和分析表</li>
                </ol>
            </div>
            <div class="mt-3">
                <h5>常见慢查询类型</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>类型</th>
                                <th>特征</th>
                                <th>优化方法</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>全表扫描</td>
                                <td>查询没有使用索引，需要扫描整个表</td>
                                <td>添加适当的索引，优化 WHERE 条件</td>
                            </tr>
                            <tr>
                                <td>临时表</td>
                                <td>查询需要创建临时表来存储中间结果</td>
                                <td>优化查询逻辑，减少排序和分组操作</td>
                            </tr>
                            <tr>
                                <td>文件排序</td>
                                <td>查询需要对结果进行排序，但无法使用索引排序</td>
                                <td>为排序字段创建索引，减少排序的数据量</td>
                            </tr>
                            <tr>
                                <td>JOIN 优化</td>
                                <td>多表连接查询效率低下</td>
                                <td>为连接字段创建索引，优化连接顺序，减少连接表数量</td>
                            </tr>
                            <tr>
                                <td>子查询</td>
                                <td>使用了效率低下的子查询</td>
                                <td>将子查询改为 JOIN 操作，或使用临时表存储子查询结果</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 查询详情模态框 -->
<div class="modal fade" id="query-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">查询详情</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <pre><code class="sql" id="query-content"></code></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" id="copy-query">复制</button>
            </div>
        </div>
    </div>
</div>

<!-- 查询分析模态框 -->
<div class="modal fade" id="analyze-modal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">查询分析</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>查询语句</label>
                    <pre><code class="sql" id="analyze-query-content"></code></pre>
                </div>
                <div class="mb-3">
                    <label>执行计划</label>
                    <div id="explain-result" class="p-2 border rounded" style="max-height: 300px; overflow: auto;">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin"></i> 分析中...
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>优化建议</label>
                    <div id="optimization-suggestions" class="p-2 border rounded">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin"></i> 生成建议中...
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section("styles")
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/10.5.0/styles/default.min.css">
<style>
    pre {
        max-height: 300px;
        overflow: auto;
    }
</style>
@endsection

@section("scripts")
<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/10.5.0/highlight.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/10.5.0/languages/sql.min.js"></script>
<script>
    $(function () {
        // 语法高亮
        hljs.initHighlightingOnLoad();
        
        // 表格初始化
        $('#slow-queries-table').DataTable({
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
        
        // 刷新慢查询
        $('#refresh-slow-queries').click(function() {
            location.reload();
        });
        
        // 切换慢查询日志状态
        $('#toggle-slow-query').click(function() {
            var enable = $(this).text() === '启用';
            
            $.ajax({
                url: '{{ route("admin.database.toggle-slow-query") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    enable: enable
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('慢查询日志已' + (enable ? '启用' : '禁用'));
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error('操作失败: ' + response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error('操作失败: ' + xhr.responseText);
                }
            });
        });
        
        // 设置慢查询阈值
        $('#set-long-query-time').click(function() {
            var time = $('#long-query-time').val();
            
            $.ajax({
                url: '{{ route("admin.database.set-long-query-time") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    time: time
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('慢查询阈值已设置为 ' + time + ' 秒');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error('设置失败: ' + response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error('设置失败: ' + xhr.responseText);
                }
            });
        });
        
        // 查看查询语句
        $('.view-query').click(function() {
            var query = $(this).data('query');
            $('#query-content').text(query);
            hljs.highlightBlock(document.getElementById('query-content'));
            $('#query-modal').modal('show');
        });
        
        // 复制查询语句
        $('#copy-query').click(function() {
            var queryText = $('#query-content').text();
            
            // 创建临时textarea
            var textarea = document.createElement('textarea');
            textarea.value = queryText;
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                document.execCommand('copy');
                toastr.success('查询语句已复制到剪贴板');
            } catch (err) {
                toastr.error('复制失败: ' + err);
            }
            
            document.body.removeChild(textarea);
        });
        
        // 分析查询
        $('.analyze-query').click(function() {
            var query = $(this).data('query');
            $('#analyze-query-content').text(query);
            hljs.highlightBlock(document.getElementById('analyze-query-content'));
            
            $('#explain-result').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> 分析中...</div>');
            $('#optimization-suggestions').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> 生成建议中...</div>');
            
            $('#analyze-modal').modal('show');
            
            // 获取执行计划
            $.ajax({
                url: '{{ route("admin.database.explain-query") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    query: query
                },
                success: function(response) {
                    if (response.success) {
                        var html = '<table class="table table-bordered table-striped">';
                        html += '<thead><tr>';
                        
                        // 表头
                        for (var key in response.explain[0]) {
                            html += '<th>' + key + '</th>';
                        }
                        
                        html += '</tr></thead><tbody>';
                        
                        // 表体
                        for (var i = 0; i < response.explain.length; i++) {
                            html += '<tr>';
                            for (var key in response.explain[i]) {
                                html += '<td>' + response.explain[i][key] + '</td>';
                            }
                            html += '</tr>';
                        }
                        
                        html += '</tbody></table>';
                        $('#explain-result').html(html);
                        
                        // 生成优化建议
                        var suggestions = '<div class="alert alert-info">';
                        suggestions += '<h5><i class="icon fas fa-info"></i> 分析结果</h5>';
                        suggestions += '<ul>';
                        
                        for (var i = 0; i < response.suggestions.length; i++) {
                            suggestions += '<li>' + response.suggestions[i] + '</li>';
                        }
                        
                        suggestions += '</ul></div>';
                        
                        if (response.optimized_query) {
                            suggestions += '<div class="mt-3"><label>优化后的查询</label>';
                            suggestions += '<pre><code class="sql">' + response.optimized_query + '</code></pre></div>';
                        }
                        
                        $('#optimization-suggestions').html(suggestions);
                        
                        // 高亮优化后的查询
                        if (response.optimized_query) {
                            hljs.highlightBlock(document.querySelector('#optimization-suggestions code'));
                        }
                    } else {
                        $('#explain-result').html('<div class="alert alert-danger">' + response.message + '</div>');
                        $('#optimization-suggestions').html('<div class="alert alert-danger">无法生成优化建议</div>');
                    }
                },
                error: function(xhr) {
                    $('#explain-result').html('<div class="alert alert-danger">分析失败: ' + xhr.responseText + '</div>');
                    $('#optimization-suggestions').html('<div class="alert alert-danger">无法生成优化建议</div>');
                }
            });
        });
    });
</script>
@endsection 