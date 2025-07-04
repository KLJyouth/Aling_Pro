@extends("admin.layouts.app")

@section("title", "审计日志详情")

@section("content_header")
    <h1>审计日志详情</h1>
@stop

@section("content")
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">审计日志详情 #{{ $log->id }}</h3>
                        <div>
                            <a href="{{ route("admin.ai.logs.audit") }}" class="btn btn-default">
                                <i class="fas fa-arrow-left"></i> 返回列表
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">用户</th>
                                    <td>{{ $log->user ? $log->user->name : "系统" }}</td>
                                </tr>
                                <tr>
                                    <th>操作</th>
                                    <td>
                                        @if($log->action == "create")
                                            <span class="badge badge-success">创建</span>
                                        @elseif($log->action == "update")
                                            <span class="badge badge-info">更新</span>
                                        @elseif($log->action == "delete")
                                            <span class="badge badge-danger">删除</span>
                                        @elseif($log->action == "view")
                                            <span class="badge badge-secondary">查看</span>
                                        @else
                                            <span class="badge badge-primary">{{ $log->action }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>资源类型</th>
                                    <td>
                                        @if($log->resource_type == "provider")
                                            提供商
                                        @elseif($log->resource_type == "model")
                                            模型
                                        @elseif($log->resource_type == "agent")
                                            智能体
                                        @elseif($log->resource_type == "api_key")
                                            API密钥
                                        @elseif($log->resource_type == "setting")
                                            设置
                                        @elseif($log->resource_type == "provider_style")
                                            提供商样式
                                        @elseif($log->resource_type == "model_style")
                                            模型样式
                                        @elseif($log->resource_type == "agent_style")
                                            智能体样式
                                        @elseif($log->resource_type == "advanced_settings")
                                            高级设置
                                        @else
                                            {{ $log->resource_type }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>资源ID</th>
                                    <td>{{ $log->resource_id ?: "N/A" }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">IP地址</th>
                                    <td>{{ $log->ip_address }}</td>
                                </tr>
                                <tr>
                                    <th>用户代理</th>
                                    <td><small>{{ $log->user_agent }}</small></td>
                                </tr>
                                <tr>
                                    <th>操作时间</th>
                                    <td>{{ $log->created_at }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-warning">
                                    <h3 class="card-title">旧值</h3>
                                </div>
                                <div class="card-body">
                                    @if($log->old_values)
                                        <pre style="max-height: 400px; overflow-y: auto;"><code class="json">{{ json_encode(json_decode($log->old_values), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                    @else
                                        <p class="text-muted">无旧值数据</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-info">
                                    <h3 class="card-title">新值</h3>
                                </div>
                                <div class="card-body">
                                    @if($log->new_values)
                                        <pre style="max-height: 400px; overflow-y: auto;"><code class="json">{{ json_encode(json_decode($log->new_values), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                    @else
                                        <p class="text-muted">无新值数据</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($log->old_values && $log->new_values)
                        <div class="card mt-4">
                            <div class="card-header bg-success">
                                <h3 class="card-title">变更字段</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>字段</th>
                                            <th>旧值</th>
                                            <th>新值</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($log->changed_fields as $field => $values)
                                            <tr>
                                                <td>{{ $field }}</td>
                                                <td>
                                                    @if(is_array($values["old"]))
                                                        <pre><code class="json">{{ json_encode($values["old"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                    @else
                                                        {{ $values["old"] ?? "null" }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(is_array($values["new"]))
                                                        <pre><code class="json">{{ json_encode($values["new"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                    @else
                                                        {{ $values["new"] ?? "null" }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section("css")
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/default.min.css">
@stop

@section("js")
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    <script>
        $(function() {
            // 初始化代码高亮
            document.querySelectorAll("pre code").forEach(block => {
                hljs.highlightElement(block);
            });
        });
    </script>
@stop
