@extends("admin.layouts.app")

@section("title", "API调用日志详情")

@section("content_header")
    <h1>API调用日志详情</h1>
@stop

@section("content")
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">API调用详情 #{{ $log->id }}</h3>
                        <div>
                            <a href="{{ route("admin.ai.logs.api") }}" class="btn btn-default">
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
                                    <th style="width: 30%">请求ID</th>
                                    <td>{{ $log->request_id ?: "未指定" }}</td>
                                </tr>
                                <tr>
                                    <th>提供商</th>
                                    <td>{!! $log->provider ? $log->provider->formatted_name : "未知" !!}</td>
                                </tr>
                                <tr>
                                    <th>模型</th>
                                    <td>{!! $log->model ? $log->model->formatted_name : "未知" !!}</td>
                                </tr>
                                <tr>
                                    <th>智能体</th>
                                    <td>{!! $log->agent ? $log->agent->formatted_name : "未使用" !!}</td>
                                </tr>
                                <tr>
                                    <th>API密钥</th>
                                    <td>{{ $log->apiKey ? $log->apiKey->name . " (" . $log->apiKey->key_mask . ")" : "未知" }}</td>
                                </tr>
                                <tr>
                                    <th>用户</th>
                                    <td>{{ $log->user ? $log->user->name : "系统" }}</td>
                                </tr>
                                <tr>
                                    <th>IP地址</th>
                                    <td>{{ $log->ip_address ?: "未知" }}</td>
                                </tr>
                                <tr>
                                    <th>请求端点</th>
                                    <td>{{ $log->endpoint ?: "未知" }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">请求时间</th>
                                    <td>{{ $log->created_at }}</td>
                                </tr>
                                <tr>
                                    <th>响应时间</th>
                                    <td>{{ $log->response_time }}ms</td>
                                </tr>
                                <tr>
                                    <th>输入标记数</th>
                                    <td>{{ $log->input_tokens }}</td>
                                </tr>
                                <tr>
                                    <th>输出标记数</th>
                                    <td>{{ $log->output_tokens }}</td>
                                </tr>
                                <tr>
                                    <th>总标记数</th>
                                    <td>{{ $log->input_tokens + $log->output_tokens }}</td>
                                </tr>
                                <tr>
                                    <th>成本</th>
                                    <td>${{ number_format($log->cost, 6) }}</td>
                                </tr>
                                <tr>
                                    <th>状态</th>
                                    <td>
                                        @if($log->status == "success")
                                            <span class="badge badge-success">成功</span>
                                        @elseif($log->status == "error")
                                            <span class="badge badge-danger">错误</span>
                                        @else
                                            <span class="badge badge-warning">处理中</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>会话ID</th>
                                    <td>{{ $log->session_id ?: "未指定" }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($log->error_message)
                        <div class="alert alert-danger mt-4">
                            <h5><i class="icon fas fa-exclamation-triangle"></i> 错误信息</h5>
                            <pre>{{ $log->error_message }}</pre>
                        </div>
                    @endif
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary">
                                    <h3 class="card-title">请求数据</h3>
                                </div>
                                <div class="card-body">
                                    <pre style="max-height: 400px; overflow-y: auto;"><code class="json">{{ $log->request_data ? json_encode(json_decode($log->request_data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : "无请求数据" }}</code></pre>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-success">
                                    <h3 class="card-title">响应数据</h3>
                                </div>
                                <div class="card-body">
                                    <pre style="max-height: 400px; overflow-y: auto;"><code class="json">{{ $log->response_data ? json_encode(json_decode($log->response_data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : "无响应数据" }}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
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
