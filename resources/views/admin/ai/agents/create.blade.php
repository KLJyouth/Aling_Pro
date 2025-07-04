@extends("admin.layouts.app")

@section("title", "添加AI智能体")

@section("content_header")
    <h1>添加AI智能体</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">添加新的AI智能体</h3>
        </div>
        <div class="card-body">
            <form action="{{ route("admin.ai.agents.store") }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">智能体名称</label>
                    <input type="text" class="form-control @error("name") is-invalid @enderror" id="name" name="name" value="{{ old("name") }}" required>
                    @error("name")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="provider_id">所属提供商</label>
                    <select class="form-control @error("provider_id") is-invalid @enderror" id="provider_id" name="provider_id" required>
                        <option value="">请选择提供商</option>
                        @foreach($providers as $provider)
                            <option value="{{ $provider->id }}" {{ old("provider_id") == $provider->id ? "selected" : "" }}>
                                {{ $provider->name }}
                            </option>
                        @endforeach
                    </select>
                    @error("provider_id")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="type">智能体类型</label>
                    <select class="form-control @error("type") is-invalid @enderror" id="type" name="type" required>
                        <option value="">请选择类型</option>
                        <option value="chat" {{ old("type") == "chat" ? "selected" : "" }}>对话型</option>
                        <option value="completion" {{ old("type") == "completion" ? "selected" : "" }}>补全型</option>
                        <option value="function" {{ old("type") == "function" ? "selected" : "" }}>函数调用型</option>
                        <option value="assistant" {{ old("type") == "assistant" ? "selected" : "" }}>助手型</option>
                    </select>
                    @error("type")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="endpoint">API端点</label>
                    <input type="text" class="form-control @error("endpoint") is-invalid @enderror" id="endpoint" name="endpoint" value="{{ old("endpoint") }}" required>
                    <small class="form-text text-muted">相对于提供商基础URL的API端点，如：/chat/completions</small>
                    @error("endpoint")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="model_id">使用模型</label>
                    <select class="form-control @error("model_id") is-invalid @enderror" id="model_id" name="model_id">
                        <option value="">请先选择提供商</option>
                    </select>
                    <small class="form-text text-muted">选择该智能体使用的模型</small>
                    @error("model_id")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="parameters">默认参数</label>
                    <textarea class="form-control @error("parameters") is-invalid @enderror" id="parameters" name="parameters" rows="5">{{ old("parameters", "{\n  \"temperature\": 0.7,\n  \"max_tokens\": 1024,\n  \"top_p\": 1\n}") }}</textarea>
                    <small class="form-text text-muted">JSON格式的默认参数</small>
                    @error("parameters")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="description">描述</label>
                    <textarea class="form-control @error("description") is-invalid @enderror" id="description" name="description" rows="3">{{ old("description") }}</textarea>
                    @error("description")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old("is_active") ? "checked" : "" }}>
                        <label class="custom-control-label" for="is_active">启用</label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">保存</button>
                <a href="{{ route("admin.ai.agents.index") }}" class="btn btn-default">取消</a>
            </form>
        </div>
    </div>
@stop

@section("js")
<script>
    $(function() {
        // 当提供商选择变化时，加载对应的模型列表
        $("#provider_id").change(function() {
            const providerId = $(this).val();
            if (providerId) {
                $.get(`/admin/api/providers/${providerId}/models`, function(data) {
                    let options = "<option value=\"\">请选择模型</option>";
                    data.forEach(function(model) {
                        options += `<option value="${model.id}">${model.name} (${model.identifier})</option>`;
                    });
                    $("#model_id").html(options);
                });
            } else {
                $("#model_id").html("<option value=\"\">请先选择提供商</option>");
            }
        });
        
        // 如果已经选择了提供商，加载对应的模型列表
        const providerId = $("#provider_id").val();
        if (providerId) {
            $.get(`/admin/api/providers/${providerId}/models`, function(data) {
                let options = "<option value=\"\">请选择模型</option>";
                data.forEach(function(model) {
                    const selected = model.id == "{{ old("model_id") }}" ? "selected" : "";
                    options += `<option value="${model.id}" ${selected}>${model.name} (${model.identifier})</option>`;
                });
                $("#model_id").html(options);
            });
        }
    });
</script>
@stop
