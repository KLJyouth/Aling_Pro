@extends("admin.layouts.app")

@section("title", "表详情 - {$table}")

@section("content")
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>表详情：{{ $table }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">首页</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.database.index') }}">数据库超级运维</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.database.tables') }}">数据库表管理</a></li>
                <li class="breadcrumb-item active">{{ $table }}</li>
            </ol>
        </div>
    </div>

    <!-- 表概览 -->
    <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-database"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">表大小</span>
                    <span class="info-box-number">{{ number_format($stats->size_mb, 2) }} MB</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-list"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">记录数</span>
                    <span class="info-box-number">{{ number_format($stats->total_rows) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-columns"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">字段数</span>
                    <span class="info-box-number">{{ count($columns) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-key"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">索引数</span>
                    <span class="info-box-number">{{ count($indexes) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 操作按钮 -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="btn-group">
                <button type="button" class="btn btn-primary" id="optimize-table">
                    <i class="fas fa-broom mr-1"></i> 优化表
                </button>
                <button type="button" class="btn btn-info" id="analyze-table">
                    <i class="fas fa-chart-bar mr-1"></i> 分析表
                </button>
                <button type="button" class="btn btn-success" id="backup-table">
                    <i class="fas fa-download mr-1"></i> 备份表
                </button>
                <button type="button" class="btn btn-warning" id="repair-table">
                    <i class="fas fa-wrench mr-1"></i> 修复表
                </button>
                <button type="button" class="btn btn-danger" id="truncate-table">
                    <i class="fas fa-trash mr-1"></i> 清空表
                </button>
            </div>
        </div>
    </div>

    <!-- 表基本信息 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">基本信息</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%">表名</th>
                            <td>{{ $table }}</td>
                        </tr>
                        <tr>
                            <th>存储引擎</th>
                            <td>{{ $stats->engine }}</td>
                        </tr>
                        <tr>
                            <th>校对规则</th>
                            <td>{{ $stats->collation }}</td>
                        </tr>
                        <tr>
                            <th>记录数</th>
                            <td>{{ number_format($stats->total_rows) }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%">数据大小</th>
                            <td>{{ number_format($stats->size_mb, 2) }} MB</td>
                        </tr>
                        <tr>
                            <th>创建时间</th>
                            <td>{{ $stats->last_update ?? '未知' }}</td>
                        </tr>
                        <tr>
                            <th>字段数</th>
                            <td>{{ count($columns) }}</td>
                        </tr>
                        <tr>
                            <th>索引数</th>
                            <td>{{ count($indexes) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 表结构 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">表结构</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>字段名</th>
                            <th>类型</th>
                            <th>允许空</th>
                            <th>键</th>
                            <th>默认值</th>
                            <th>额外</th>
                            <th>排序规则</th>
                            <th>注释</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($columns as $column)
                        <tr>
                            <td>{{ $column->Field }}</td>
                            <td>{{ $column->Type }}</td>
                            <td>{{ $column->Null === 'YES' ? '是' : '否' }}</td>
                            <td>{{ $column->Key }}</td>
                            <td>{{ $column->Default ?? 'NULL' }}</td>
                            <td>{{ $column->Extra }}</td>
                            <td>{{ $column->Collation ?? '-' }}</td>
                            <td>{{ $column->Comment }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 索引 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">索引</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>键名</th>
                            <th>类型</th>
                            <th>唯一</th>
                            <th>字段</th>
                            <th>基数</th>
                            <th>排序规则</th>
                            <th>注释</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $indexGroups = [];
                            foreach ($indexes as $index) {
                                $indexGroups[$index->Key_name][] = $index;
                            }
                        @endphp
                        
                        @foreach($indexGroups as $keyName => $indexGroup)
                            @php
                                $firstIndex = $indexGroup[0];
                                $columns = array_column($indexGroup, 'Column_name');
                            @endphp
                            <tr>
                                <td>{{ $keyName }}</td>
                                <td>{{ $firstIndex->Index_type }}</td>
                                <td>{{ $firstIndex->Non_unique ? '否' : '是' }}</td>
                                <td>{{ implode(', ', $columns) }}</td>
                                <td>{{ $firstIndex->Cardinality }}</td>
                                <td>{{ $firstIndex->Collation }}</td>
                                <td>{{ $firstIndex->Comment }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 外键 -->
    @if(count($foreignKeys) > 0)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">外键</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>约束名</th>
                            <th>本表字段</th>
                            <th>引用表</th>
                            <th>引用字段</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($foreignKeys as $fk)
                        <tr>
                            <td>{{ $fk->CONSTRAINT_NAME }}</td>
                            <td>{{ $fk->COLUMN_NAME }}</td>
                            <td>
                                <a href="{{ route('admin.database.table.detail', $fk->REFERENCED_TABLE_NAME) }}">
                                    {{ $fk->REFERENCED_TABLE_NAME }}
                                </a>
                            </td>
                            <td>{{ $fk->REFERENCED_COLUMN_NAME }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- 创建表语句 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">创建表语句</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
                <button type="button" class="btn btn-tool" id="copy-create-table">
                    <i class="fas fa-copy"></i> 复制
                </button>
            </div>
        </div>
        <div class="card-body">
            <pre><code class="sql">{{ $createTable }}</code></pre>
        </div>
    </div>

    <!-- 数据预览 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">数据预览</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            @foreach($columns as $column)
                                <th>{{ $column->Field }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                @foreach($columns as $column)
                                    <td>
                                        @if(is_null($record->{$column->Field}))
                                            <em class="text-muted">NULL</em>
                                        @elseif(is_object($record->{$column->Field}) || is_array($record->{$column->Field}))
                                            <pre>{{ json_encode($record->{$column->Field}, JSON_PRETTY_PRINT) }}</pre>
                                        @else
                                            {{ \Illuminate\Support\Str::limit($record->{$column->Field}, 100) }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) }}" class="text-center">表中没有数据</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <a href="{{ route('admin.database.table.browse', $table) }}" class="btn btn-primary">
                    <i class="fas fa-table mr-1"></i> 浏览更多数据
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 操作结果模态框 -->
<div class="modal fade" id="result-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">操作结果</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="result-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<!-- 备份表单模态框 -->
<div class="modal fade" id="backup-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">备份表</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="backup-form">
                    <div class="form-group">
                        <label for="backup-name">备份名称</label>
                        <input type="text" class="form-control" id="backup-name" name="backup_name" placeholder="例如：{{ $table }}_backup">
                        <small class="form-text text-muted">如果不填写，将使用默认格式：{{ $table }}_backup_日期时间</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="submit-backup">开始备份</button>
            </div>
        </div>
    </div>
</div>

<!-- 清空表确认模态框 -->
<div class="modal fade" id="truncate-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-danger">危险操作：清空表</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-1"></i> 警告：此操作将删除表 <strong>{{ $table }}</strong> 中的所有数据，且无法恢复！
                </div>
                <div class="form-group mt-3">
                    <label for="truncate-confirm">请输入"{{ $table }}"以确认操作</label>
                    <input type="text" class="form-control" id="truncate-confirm" placeholder="{{ $table }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" id="submit-truncate" disabled>确认清空</button>
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
<script>
    $(function () {
        // 语法高亮
        hljs.initHighlightingOnLoad();
        
        // 复制创建表语句
        $('#copy-create-table').click(function() {
            var createTableText = $('pre code').text();
            
            // 创建临时textarea
            var textarea = document.createElement('textarea');
            textarea.value = createTableText;
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                document.execCommand('copy');
                toastr.success('创建表语句已复制到剪贴板');
            } catch (err) {
                toastr.error('复制失败: ' + err);
            }
            
            document.body.removeChild(textarea);
        });
        
        // 优化表
        $('#optimize-table').click(function() {
            $.ajax({
                url: '{{ route("admin.database.optimize") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    tables: ['{{ $table }}']
                },
                beforeSend: function() {
                    $('#result-content').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>优化中，请稍候...</p></div>');
                    $('#result-modal').modal('show');
                },
                success: function(response) {
                    if (response.success) {
                        var result = response.results['{{ $table }}'];
                        if (result.status === 'success') {
                            $('#result-content').html('<div class="alert alert-success">表 {{ $table }} 优化成功！</div>');
                        } else {
                            $('#result-content').html('<div class="alert alert-danger">表 {{ $table }} 优化失败：' + result.message + '</div>');
                        }
                    } else {
                        $('#result-content').html('<div class="alert alert-danger">优化失败！</div>');
                    }
                },
                error: function(xhr) {
                    $('#result-content').html('<div class="alert alert-danger">优化失败：' + xhr.responseText + '</div>');
                }
            });
        });
        
        // 分析表
        $('#analyze-table').click(function() {
            $.ajax({
                url: '{{ route("admin.database.analyze") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    tables: ['{{ $table }}']
                },
                beforeSend: function() {
                    $('#result-content').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>分析中，请稍候...</p></div>');
                    $('#result-modal').modal('show');
                },
                success: function(response) {
                    if (response.success) {
                        var result = response.results['{{ $table }}'];
                        if (result.status === 'success') {
                            $('#result-content').html('<div class="alert alert-success">表 {{ $table }} 分析成功！</div>');
                        } else {
                            $('#result-content').html('<div class="alert alert-danger">表 {{ $table }} 分析失败：' + result.message + '</div>');
                        }
                    } else {
                        $('#result-content').html('<div class="alert alert-danger">分析失败！</div>');
                    }
                },
                error: function(xhr) {
                    $('#result-content').html('<div class="alert alert-danger">分析失败：' + xhr.responseText + '</div>');
                }
            });
        });
        
        // 修复表
        $('#repair-table').click(function() {
            $.ajax({
                url: '{{ route("admin.database.repair") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    tables: ['{{ $table }}']
                },
                beforeSend: function() {
                    $('#result-content').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>修复中，请稍候...</p></div>');
                    $('#result-modal').modal('show');
                },
                success: function(response) {
                    if (response.success) {
                        var result = response.results['{{ $table }}'];
                        if (result.status === 'success') {
                            $('#result-content').html('<div class="alert alert-success">表 {{ $table }} 修复成功！</div>');
                        } else {
                            $('#result-content').html('<div class="alert alert-danger">表 {{ $table }} 修复失败：' + result.message + '</div>');
                        }
                    } else {
                        $('#result-content').html('<div class="alert alert-danger">修复失败！</div>');
                    }
                },
                error: function(xhr) {
                    $('#result-content').html('<div class="alert alert-danger">修复失败：' + xhr.responseText + '</div>');
                }
            });
        });
        
        // 备份表
        $('#backup-table').click(function() {
            // 设置默认备份名称
            var now = new Date();
            var defaultName = '{{ $table }}_backup_' + 
                now.getFullYear() + '_' + 
                padZero(now.getMonth() + 1) + '_' + 
                padZero(now.getDate()) + '_' + 
                padZero(now.getHours()) + 
                padZero(now.getMinutes()) + 
                padZero(now.getSeconds());
            $('#backup-name').val(defaultName);
            
            // 显示模态框
            $('#backup-modal').modal('show');
        });
        
        // 备份表单提交
        $('#submit-backup').click(function() {
            var backupName = $('#backup-name').val() || '{{ $table }}_backup';
            
            $.ajax({
                url: '{{ route("admin.database.backup.create") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    backup_name: backupName,
                    tables: ['{{ $table }}']
                },
                beforeSend: function() {
                    $('#backup-modal').modal('hide');
                    $('#result-content').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>备份中，请稍候...</p></div>');
                    $('#result-modal').modal('show');
                },
                success: function(response) {
                    if (response.success) {
                        var resultHtml = '<div class="alert alert-success">备份完成！</div>';
                        resultHtml += '<p>备份文件：' + response.filename + '</p>';
                        resultHtml += '<p>备份大小：' + response.size + '</p>';
                        resultHtml += '<p>备份时间：' + response.created_at + '</p>';
                        resultHtml += '<div class="mt-3"><a href="' + response.download_url + '" class="btn btn-primary"><i class="fas fa-download mr-1"></i> 下载备份</a></div>';
                        $('#result-content').html(resultHtml);
                    } else {
                        $('#result-content').html('<div class="alert alert-danger">备份失败！</div><p>' + response.message + '</p>');
                    }
                },
                error: function(xhr) {
                    $('#result-content').html('<div class="alert alert-danger">备份失败：' + xhr.responseText + '</div>');
                }
            });
        });
        
        // 清空表
        $('#truncate-table').click(function() {
            $('#truncate-confirm').val('');
            $('#submit-truncate').prop('disabled', true);
            $('#truncate-modal').modal('show');
        });
        
        // 监听确认输入
        $('#truncate-confirm').on('input', function() {
            $('#submit-truncate').prop('disabled', $(this).val() !== '{{ $table }}');
        });
        
        // 清空表单提交
        $('#submit-truncate').click(function() {
            if ($('#truncate-confirm').val() === '{{ $table }}') {
                $.ajax({
                    url: '{{ route("admin.database.truncate") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        tables: ['{{ $table }}']
                    },
                    beforeSend: function() {
                        $('#truncate-modal').modal('hide');
                        $('#result-content').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>清空中，请稍候...</p></div>');
                        $('#result-modal').modal('show');
                    },
                    success: function(response) {
                        if (response.success) {
                            var result = response.results['{{ $table }}'];
                            if (result.status === 'success') {
                                $('#result-content').html('<div class="alert alert-success">表 {{ $table }} 已成功清空！</div>');
                                
                                // 刷新页面
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            } else {
                                $('#result-content').html('<div class="alert alert-danger">表 {{ $table }} 清空失败：' + result.message + '</div>');
                            }
                        } else {
                            $('#result-content').html('<div class="alert alert-danger">清空失败！</div>');
                        }
                    },
                    error: function(xhr) {
                        $('#result-content').html('<div class="alert alert-danger">清空失败：' + xhr.responseText + '</div>');
                    }
                });
            }
        });
        
        // 辅助函数：补零
        function padZero(num) {
            return num < 10 ? '0' + num : num;
        }
    });
</script>
@endsection 