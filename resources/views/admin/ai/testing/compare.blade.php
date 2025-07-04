@extends("admin.layouts.app")

@section("title", "智能体比较")

@section("content_header")
    <h1>智能体比较</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">比较多个智能体的响应</h3>
                <div>
                    <a href="{{ route("admin.ai.testing.index") }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> 返回测试页面
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form id="compareForm">
                <div class="form-group">
                    <label>选择要比较的智能体（最多选择3个）</label>
                    <div class="select2-purple">
                        <select class="form-control select2" multiple="multiple" id="agent_ids" name="agent_ids[]" data-placeholder="选择智能体" style="width: 100%;" required>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}">
                                    {{ $agent->name }} ({{ $agent->provider->name }} / {{ $agent->model->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <small class="form-text text-muted">选择2-3个智能体进行比较</small>
                </div>
                
                <div class="form-group">
                    <label for="prompt">输入提示</label>
                    <textarea class="form-control" id="prompt" name="prompt" rows="5" placeholder="输入要测试的提示内容..." required></textarea>
                </div>
                
                <div class="text-center mb-4">
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                        <i class="fas fa-balance-scale"></i> 开始比较
                    </button>
                </div>
            </form>
            
            <div class="results-container mt-4" style="display: none;">
                <div class="card">
                    <div class="card-header bg-info">
                        <h3 class="card-title">比较结果</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width: 20%">智能体</th>
                                        <th style="width: 15%">提供商/模型</th>
                                        <th style="width: 45%">响应内容</th>
                                        <th style="width: 20%">性能指标</th>
                                    </tr>
                                </thead>
                                <tbody id="resultsTable">
                                    <!-- 结果将在这里动态添加 -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="error-container mt-4 alert alert-danger" style="display: none;">
                <h5><i class="icon fas fa-exclamation-triangle"></i> 比较失败</h5>
                <p id="errorMessage"></p>
            </div>
        </div>
    </div>
@stop

@section("css")
    <link rel="stylesheet" href="{{ asset("vendor/select2/css/select2.min.css") }}">
    <link rel="stylesheet" href="{{ asset("vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css") }}">
@stop

@section("js")
    <script src="{{ asset("vendor/select2/js/select2.full.min.js") }}"></script>
    <script>
        $(function() {
            // 初始化Select2
            $(".select2").select2({
                theme: "bootstrap4",
                language: "zh-CN",
                maximumSelectionLength: 3
            });
            
            $("#compareForm").on("submit", function(e) {
                e.preventDefault();
                
                const agentIds = $("#agent_ids").val();
                const prompt = $("#prompt").val();
                
                if (!agentIds || agentIds.length < 2 || !prompt) {
                    alert("请选择至少2个智能体并输入提示内容");
                    return;
                }
                
                if (agentIds.length > 3) {
                    alert("最多只能选择3个智能体进行比较");
                    return;
                }
                
                // 显示加载状态
                $("#submitBtn").prop("disabled", true).html("<i class=\"fas fa-spinner fa-spin\"></i> 比较中...");
                $(".results-container").hide();
                $(".error-container").hide();
                
                // 发送请求
                $.ajax({
                    url: "{{ route("admin.ai.testing.compare-agents") }}",
                    type: "POST",
                    data: {
                        agent_ids: agentIds,
                        prompt: prompt,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        // 恢复按钮状态
                        $("#submitBtn").prop("disabled", false).html("<i class=\"fas fa-balance-scale\"></i> 开始比较");
                        
                        // 清空结果表
                        $("#resultsTable").empty();
                        
                        // 添加结果行
                        response.results.forEach(function(result) {
                            let row = `
                                <tr>
                                    <td>${result.agent_name}</td>
                                    <td>${result.provider_name}<br>${result.model_name}</td>
                                    <td>
                                        <div style="max-height: 300px; overflow-y: auto; white-space: pre-wrap;">${result.content}</div>
                                    </td>
                                    <td>
                                        <ul class="list-unstyled">
                                            <li><strong>响应时间:</strong> ${result.response_time}ms</li>
                                            <li><strong>总标记数:</strong> ${result.total_tokens}</li>
                                            <li><strong>成本:</strong> $${result.cost.toFixed(6)}</li>
                                        </ul>
                                    </td>
                                </tr>
                            `;
                            $("#resultsTable").append(row);
                        });
                        
                        $(".results-container").show();
                    },
                    error: function(xhr) {
                        // 恢复按钮状态
                        $("#submitBtn").prop("disabled", false).html("<i class=\"fas fa-balance-scale\"></i> 开始比较");
                        
                        // 显示错误
                        let errorMsg = "未知错误";
                        
                        if (xhr.responseJSON) {
                            errorMsg = xhr.responseJSON.message || "请求失败";
                        }
                        
                        $("#errorMessage").text(errorMsg);
                        $(".error-container").show();
                    }
                });
            });
        });
    </script>
@stop
