@extends("admin.layouts.app")

@section("title", "AI智能体详情")

@section("content_header")
    <h1>AI智能体详情</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">{{ $agent->name }} 详情</h3>
                <div>
                    <a href="{{ route("admin.ai.agents.edit", $agent->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> 编辑
                    </a>
                    <a href="{{ route("admin.ai.agents.index") }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> 返回列表
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table">
                        <tr>
                            <th style="width: 30%">ID</th>
                            <td>{{ $agent->id }}</td>
                        </tr>
                        <tr>
                            <th>名称</th>
                            <td>{{ $agent->name }}</td>
                        </tr>
                        <tr>
                            <th>提供商</th>
                            <td>{{ $agent->provider->name }}</td>
                        </tr>
                        <tr>
                            <th>类型</th>
                            <td>{{ $agent->type }}</td>
                        </tr>
                        <tr>
                            <th>API端点</th>
                            <td>{{ $agent->endpoint }}</td>
                        </tr>
                        <tr>
                            <th>使用模型</th>
                            <td>{{ $agent->model ? $agent->model->name : "未指定" }}</td>
                        </tr>
                        <tr>
                            <th>状态</th>
                            <td>
                                @if($agent->is_active)
                                    <span class="badge badge-success">启用</span>
                                @else
                                    <span class="badge badge-danger">禁用</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>创建时间</th>
                            <td>{{ $agent->created_at }}</td>
                        </tr>
                        <tr>
                            <th>更新时间</th>
                            <td>{{ $agent->updated_at }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">描述</h4>
                        </div>
                        <div class="card-body">
                            {{ $agent->description ?: "暂无描述" }}
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h4 class="card-title">默认参数</h4>
                        </div>
                        <div class="card-body">
                            <pre><code>{{ json_encode(json_decode($agent->parameters), JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">智能体测试</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="test-input">输入测试内容</label>
                                <textarea class="form-control" id="test-input" rows="3" placeholder="输入要发送给智能体的内容..."></textarea>
                            </div>
                            <button type="button" class="btn btn-primary" id="test-button">
                                <i class="fas fa-paper-plane"></i> 发送测试
                            </button>
                            
                            <div class="mt-4" id="test-result" style="display: none;">
                                <h5>测试结果</h5>
                                <div class="p-3 bg-light" style="border-radius: 5px;">
                                    <pre id="test-output" style="white-space: pre-wrap;"></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">使用记录</h4>
                        </div>
                        <div class="card-body">
                            @if($usageRecords->count() > 0)
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>用户</th>
                                            <th>请求时间</th>
                                            <th>响应时间</th>
                                            <th>输入标记数</th>
                                            <th>输出标记数</th>
                                            <th>状态</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($usageRecords as $record)
                                            <tr>
                                                <td>{{ $record->id }}</td>
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
                                
                                {{ $usageRecords->links() }}
                            @else
                                <div class="alert alert-info">
                                    暂无使用记录
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section("js")
<script>
    $(function() {
        $("#test-button").click(function() {
            const input = $("#test-input").val();
            if (!input) {
                alert("请输入测试内容");
                return;
            }
            
            $(this).prop("disabled", true).html("<i class=\"fas fa-spinner fa-spin\"></i> 处理中...");
            
            $.ajax({
                url: "{{ route("admin.ai.agents.chat") }}",
                type: "POST",
                data: {
                    agent_id: {{ $agent->id }},
                    message: input,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    $("#test-output").text(response.message || JSON.stringify(response, null, 2));
                    $("#test-result").show();
                },
                error: function(xhr) {
                    let errorMessage = "测试失败";
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {}
                    
                    $("#test-output").html(`<span class="text-danger">${errorMessage}</span>`);
                    $("#test-result").show();
                },
                complete: function() {
                    $("#test-button").prop("disabled", false).html("<i class=\"fas fa-paper-plane\"></i> 发送测试");
                }
            });
        });
    });
</script>
@stop
