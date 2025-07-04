@extends("admin.layouts.app")

@section("title", "AI接口设置")

@section("content_header")
    <h1>AI接口设置</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">AI接口全局设置</h3>
        </div>
        <div class="card-body">
            <form action="{{ route("admin.ai.settings.update") }}" method="POST">
                @csrf
                @method("PUT")
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="default_provider">默认AI提供商</label>
                            <select class="form-control @error("default_provider") is-invalid @enderror" id="default_provider" name="default_provider">
                                <option value="">无默认提供商</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->id }}" {{ old("default_provider", $settings["default_provider"] ?? "") == $provider->id ? "selected" : "" }}>
                                        {{ $provider->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">未指定提供商时使用的默认提供商</small>
                            @error("default_provider")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="default_chat_model">默认对话模型</label>
                            <select class="form-control @error("default_chat_model") is-invalid @enderror" id="default_chat_model" name="default_chat_model">
                                <option value="">无默认模型</option>
                                @foreach($models as $model)
                                    @if($model->type == "chat")
                                        <option value="{{ $model->id }}" {{ old("default_chat_model", $settings["default_chat_model"] ?? "") == $model->id ? "selected" : "" }}>
                                            {{ $model->name }} ({{ $model->provider->name }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="form-text text-muted">未指定模型时使用的默认对话模型</small>
                            @error("default_chat_model")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="default_completion_model">默认补全模型</label>
                            <select class="form-control @error("default_completion_model") is-invalid @enderror" id="default_completion_model" name="default_completion_model">
                                <option value="">无默认模型</option>
                                @foreach($models as $model)
                                    @if($model->type == "completion")
                                        <option value="{{ $model->id }}" {{ old("default_completion_model", $settings["default_completion_model"] ?? "") == $model->id ? "selected" : "" }}>
                                            {{ $model->name }} ({{ $model->provider->name }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="form-text text-muted">未指定模型时使用的默认补全模型</small>
                            @error("default_completion_model")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="default_embedding_model">默认嵌入模型</label>
                            <select class="form-control @error("default_embedding_model") is-invalid @enderror" id="default_embedding_model" name="default_embedding_model">
                                <option value="">无默认模型</option>
                                @foreach($models as $model)
                                    @if($model->type == "embedding")
                                        <option value="{{ $model->id }}" {{ old("default_embedding_model", $settings["default_embedding_model"] ?? "") == $model->id ? "selected" : "" }}>
                                            {{ $model->name }} ({{ $model->provider->name }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="form-text text-muted">未指定模型时使用的默认嵌入模型</small>
                            @error("default_embedding_model")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="enable_api_key_rotation">启用API密钥轮换</label>
                            <select class="form-control @error("enable_api_key_rotation") is-invalid @enderror" id="enable_api_key_rotation" name="enable_api_key_rotation">
                                <option value="0" {{ old("enable_api_key_rotation", $settings["enable_api_key_rotation"] ?? "0") == "0" ? "selected" : "" }}>禁用</option>
                                <option value="1" {{ old("enable_api_key_rotation", $settings["enable_api_key_rotation"] ?? "0") == "1" ? "selected" : "" }}>启用</option>
                            </select>
                            <small class="form-text text-muted">启用后，系统会自动轮换使用同一提供商的多个API密钥</small>
                            @error("enable_api_key_rotation")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="enable_request_caching">启用请求缓存</label>
                            <select class="form-control @error("enable_request_caching") is-invalid @enderror" id="enable_request_caching" name="enable_request_caching">
                                <option value="0" {{ old("enable_request_caching", $settings["enable_request_caching"] ?? "0") == "0" ? "selected" : "" }}>禁用</option>
                                <option value="1" {{ old("enable_request_caching", $settings["enable_request_caching"] ?? "0") == "1" ? "selected" : "" }}>启用</option>
                            </select>
                            <small class="form-text text-muted">启用后，系统会缓存相同请求的响应，减少API调用次数</small>
                            @error("enable_request_caching")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="request_cache_ttl">请求缓存有效期（分钟）</label>
                            <input type="number" class="form-control @error("request_cache_ttl") is-invalid @enderror" id="request_cache_ttl" name="request_cache_ttl" value="{{ old("request_cache_ttl", $settings["request_cache_ttl"] ?? "60") }}" min="1">
                            <small class="form-text text-muted">缓存的有效期，单位为分钟</small>
                            @error("request_cache_ttl")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="enable_fallback">启用故障转移</label>
                            <select class="form-control @error("enable_fallback") is-invalid @enderror" id="enable_fallback" name="enable_fallback">
                                <option value="0" {{ old("enable_fallback", $settings["enable_fallback"] ?? "0") == "0" ? "selected" : "" }}>禁用</option>
                                <option value="1" {{ old("enable_fallback", $settings["enable_fallback"] ?? "0") == "1" ? "selected" : "" }}>启用</option>
                            </select>
                            <small class="form-text text-muted">启用后，当主要提供商API调用失败时，会自动尝试使用备用提供商</small>
                            @error("enable_fallback")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="fallback_provider">备用AI提供商</label>
                            <select class="form-control @error("fallback_provider") is-invalid @enderror" id="fallback_provider" name="fallback_provider">
                                <option value="">无备用提供商</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->id }}" {{ old("fallback_provider", $settings["fallback_provider"] ?? "") == $provider->id ? "selected" : "" }}>
                                        {{ $provider->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">主要提供商不可用时使用的备用提供商</small>
                            @error("fallback_provider")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="default_parameters">默认请求参数</label>
                    <textarea class="form-control @error("default_parameters") is-invalid @enderror" id="default_parameters" name="default_parameters" rows="5">{{ old("default_parameters", $settings["default_parameters"] ?? "{\n  \"temperature\": 0.7,\n  \"max_tokens\": 1024,\n  \"top_p\": 1\n}") }}</textarea>
                    <small class="form-text text-muted">JSON格式的默认请求参数</small>
                    @error("default_parameters")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <button type="submit" class="btn btn-primary">保存设置</button>
                <button type="button" class="btn btn-danger" id="clear-cache-btn">清除缓存</button>
            </form>
        </div>
    </div>
@stop

@section("js")
<script>
    $(function() {
        $("#clear-cache-btn").click(function() {
            if (confirm("确定要清除所有AI接口缓存吗？")) {
                $.post("{{ route("admin.ai.settings.clear-cache") }}", {
                    _token: "{{ csrf_token() }}"
                }).done(function(response) {
                    alert("缓存已清除");
                }).fail(function(xhr) {
                    alert("清除缓存失败: " + xhr.responseJSON.message);
                });
            }
        });
    });
</script>
@stop
