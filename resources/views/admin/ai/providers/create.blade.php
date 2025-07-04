@extends("admin.layouts.app")

@section("title", "添加AI模型提供商")

@section("content_header")
    <h1>添加AI模型提供商</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">添加新的AI模型提供商</h3>
        </div>
        <div class="card-body">
            <form action="{{ route("admin.ai.providers.store") }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">提供商名称</label>
                    <input type="text" class="form-control @error("name") is-invalid @enderror" id="name" name="name" value="{{ old("name") }}" required>
                    @error("name")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="identifier">提供商标识符</label>
                    <input type="text" class="form-control @error("identifier") is-invalid @enderror" id="identifier" name="identifier" value="{{ old("identifier") }}" required>
                    <small class="form-text text-muted">唯一标识符，用于系统内部识别，如：openai, anthropic, zhipu</small>
                    @error("identifier")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="base_url">API基础URL</label>
                    <input type="url" class="form-control @error("base_url") is-invalid @enderror" id="base_url" name="base_url" value="{{ old("base_url") }}" required>
                    <small class="form-text text-muted">API的基础URL，如：https://api.openai.com/v1</small>
                    @error("base_url")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="auth_header">认证头名称</label>
                    <input type="text" class="form-control @error("auth_header") is-invalid @enderror" id="auth_header" name="auth_header" value="{{ old("auth_header", "Authorization") }}">
                    <small class="form-text text-muted">认证头的名称，通常为Authorization</small>
                    @error("auth_header")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="auth_scheme">认证方案</label>
                    <input type="text" class="form-control @error("auth_scheme") is-invalid @enderror" id="auth_scheme" name="auth_scheme" value="{{ old("auth_scheme", "Bearer") }}">
                    <small class="form-text text-muted">认证方案，通常为Bearer</small>
                    @error("auth_scheme")
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
                <a href="{{ route("admin.ai.providers.index") }}" class="btn btn-default">取消</a>
            </form>
        </div>
    </div>
@stop
