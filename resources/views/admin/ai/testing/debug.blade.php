@extends("admin.layouts.app")

@section("title", "智能体调试")

@section("content_header")
    <h1>智能体调试工具</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">调试智能体: {!! $agent->formatted_name !!}</h3>
                <div>
                    <a href="{{ route("admin.ai.testing.index") }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> 返回测试页面
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-server"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">提供商</span>
                            <span class="info-box-number">{{ $agent->provider->name }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-brain"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">模型</span>
                            <span class="info-box-number">{{ $agent->model->name }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-primary">
                            <h3 class="card-title">端点信息</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>API端点</label>
                                <input type="text" class="form-control" value="{{ $agent->endpoint }}" readonly>
                            </div>
                            <div class="form-group">
                                <label>完整URL</label>
                                <input type="text" class="form-control" value="{{ Str::startsWith($agent->endpoint, "http") ? $agent->endpoint : $agent->provider->base_url . $agent->endpoint }}" readonly>
                            </div>
                            <div class="form-group">
                                <label>认证头</label>
                                <input type="text" class="form-control" value="{{ $agent->provider->auth_header }}: {{ $agent->provider->auth_scheme }} [API_KEY]" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-warning">
                            <h3 class="card-title">默认参数</h3>
                        </div>
                        <div class="card-body">
                            <pre><code class="json">{{ json_encode($parameters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h3 class="card-title">高级调试</h3>
                        </div>
                        <div class="card-body">
                            <form id="advancedTestForm">
                                <div class="form-group">
                                    <label for="prompt">输入提示</label>
                                    <textarea class="form-control" id="prompt" name="prompt" rows="5" placeholder="输入要测试的提示内容..." required></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="parameters">自定义参数 (JSON格式)</label>
                                    <textarea class="form-control" id="parameters" name="parameters" rows="5" placeholder="{&quot;temperature&quot;: 0.7, &quot;max_tokens&quot;: 1000}"></textarea>
                                    <small class="form-text text-muted">这些参数将覆盖默认参数</small>
                                </div>
                                
                                <div class="text-center mb-4">
                                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                        <i class="fas fa-paper-plane"></i> 发送测试
                                    </button>
                                </div>
                            </form>
                            
                            <div class="response-container mt-4" style="display: none;">
                                <div class="card">
                                    <div class="card-header bg-success">
                                        <h3 class="card-title">响应结果</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="response-info mb-3">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="info-box">
                                                        <span class="info-box-icon bg-info"><i class="fas fa-clock"></i></span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">响应时间</span>
                                                            <span class="info-box-number" id="responseTime">0</span>
                                                            <span class="info-box-text">毫秒</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="info-box">
                                                        <span class="info-box-icon bg-success"><i class="fas fa-file-alt"></i></span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">输入标记数</span>
                                                            <span class="info-box-number" id="inputTokens">0</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="info-box">
                                                        <span class="info-box-icon bg-warning"><i class="fas fa-file-alt"></i></span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">输出标记数</span>
                                                            <span class="info-box-number" id="outputTokens">0</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="info-box">
                                                        <span class="info-box-icon bg-danger"><i class="fas fa-dollar-sign"></i></span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">成本</span>
                                                            <span class="info-box-number" id="cost">$0.00</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="response-content">
                                            <div class="form-group">
                                                <label>响应内容</label>
                                                <div class="p-3 bg-light rounded" id="responseContent" style="white-space: pre-wrap; max-height: 400px; overflow-y: auto;"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#rawResponse">
                                                <i class="fas fa-code"></i> 查看原始响应
                                            </button>
                                            <button type="button" class="btn btn-secondary" id="copyBtn">
                                                <i class="fas fa-copy"></i> 复制响应
                                            </button>
                                        </div>
                                        
                                        <div class="collapse mt-3" id="rawResponse">
                                            <div class="card card-body">
                                                <pre><code id="rawResponseContent" class="json"></code></pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="error-container mt-4 alert alert-danger" style="display: none;">
                                <h5><i class="icon fas fa-exclamation-triangle"></i> 测试失败</h5>
                                <p id="errorMessage"></p>
                                <div class="collapse" id="errorDetails">
                                    <pre><code id="errorDetailsContent"></code></pre>
                                </div>
                                <button class="btn btn-outline-danger btn-sm mt-2" data-toggle="collapse" data-target="#errorDetails">
                                    <i class="fas fa-info-circle"></i> 查看详情
                                </button>
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
            
            $("#advancedTestForm").on("submit", function(e) {
                e.preventDefault();
                
                const prompt = $("#prompt").val();
                const parametersStr = $("#parameters").val();
                
                if (!prompt) {
                    alert("请输入提示内容");
                    return;
                }
                
                let parameters = null;
                if (parametersStr) {
                    try {
                        parameters = JSON.parse(parametersStr);
                    } catch (error) {
                        alert("自定义参数格式不正确，请输入有效的JSON格式");
                        return;
                    }
                }
                
                // 显示加载状态
                $("#submitBtn").prop("disabled", true).html("<i class=\"fas fa-spinner fa-spin\"></i> 处理中...");
                $(".response-container").hide();
                $(".error-container").hide();
                
                // 发送请求
                $.ajax({
                    url: "{{ route("admin.ai.testing.test") }}",
                    type: "POST",
                    data: {
                        agent_id: "{{ $agent->id }}",
                        prompt: prompt,
                        parameters: parameters,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        // 恢复按钮状态
                        $("#submitBtn").prop("disabled", false).html("<i class=\"fas fa-paper-plane\"></i> 发送测试");
                        
                        // 显示响应
                        $("#responseTime").text(response.data.response_time);
                        $("#inputTokens").text(response.data.input_tokens);
                        $("#outputTokens").text(response.data.output_tokens);
                        $("#cost").text("$" + response.data.cost.toFixed(6));
                        $("#responseContent").text(response.data.content);
                        $("#rawResponseContent").text(JSON.stringify(response.data.raw_response, null, 2));
                        hljs.highlightElement(document.getElementById("rawResponseContent"));
                        
                        $(".response-container").show();
                    },
                    error: function(xhr) {
                        // 恢复按钮状态
                        $("#submitBtn").prop("disabled", false).html("<i class=\"fas fa-paper-plane\"></i> 发送测试");
                        
                        // 显示错误
                        let errorMsg = "未知错误";
                        let errorDetails = "";
                        
                        if (xhr.responseJSON) {
                            errorMsg = xhr.responseJSON.message || xhr.responseJSON.error || "请求失败";
                            errorDetails = JSON.stringify(xhr.responseJSON, null, 2);
                        }
                        
                        $("#errorMessage").text(errorMsg);
                        $("#errorDetailsContent").text(errorDetails);
                        hljs.highlightElement(document.getElementById("errorDetailsContent"));
                        $(".error-container").show();
                    }
                });
            });
            
            // 复制响应
            $("#copyBtn").on("click", function() {
                const content = $("#responseContent").text();
                navigator.clipboard.writeText(content).then(() => {
                    alert("响应内容已复制到剪贴板");
                });
            });
        });
    </script>
@stop
