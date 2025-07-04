@extends("admin.layouts.app")

@section("title", "编辑API密钥")

@section("content_header")
    <h1>编辑API密钥</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">编辑API密钥</h3>
        </div>
        <div class="card-body">
            <form action="{{ route("admin.ai.api-keys.update", $apiKey->id) }}" method="POST">
                @csrf
                @method("PUT")
                <div class="form-group">
                    <label for="name">密钥名称</label>
                    <input type="text" class="form-control @error("name") is-invalid @enderror" id="name" name="name" value="{{ old("name", $apiKey->name) }}" required>
                    <small class="form-text text-muted">用于标识密钥的名称，如：开发环境、生产环境等</small>
                    @error("name")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="provider_id">所属提供商</label>
                    <select class="form-control @error("provider_id") is-invalid @enderror" id="provider_id" name="provider_id" required>
                        <option value="">请选择提供商</option>
                        @foreach($providers as $provider)
                            <option value="{{ $provider->id }}" {{ old("provider_id", $apiKey->provider_id) == $provider->id ? "selected" : "" }}>
                                {{ $provider->name }}
                            </option>
                        @endforeach
                    </select>
                    @error("provider_id")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="api_key">API密钥</label>
                    <input type="text" class="form-control @error("api_key") is-invalid @enderror" id="api_key" name="api_key" value="{{ old("api_key") }}" placeholder="留空表示不修改密钥">
                    <small class="form-text text-muted">API密钥值，将被加密存储。留空表示不修改密钥。</small>
                    @error("api_key")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="quota_limit">使用配额限制</label>
                    <input type="number" class="form-control @error("quota_limit") is-invalid @enderror" id="quota_limit" name="quota_limit" value="{{ old("quota_limit", $apiKey->quota_limit) }}" min="0">
                    <small class="form-text text-muted">密钥使用次数限制，0表示无限制</small>
                    @error("quota_limit")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="description">描述</label>
                    <textarea class="form-control @error("description") is-invalid @enderror" id="description" name="description" rows="3">{{ old("description", $apiKey->description) }}</textarea>
                    @error("description")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old("is_active", $apiKey->is_active) ? "checked" : "" }}>
                        <label class="custom-control-label" for="is_active">启用</label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">保存</button>
                <a href="{{ route("admin.ai.api-keys.index") }}" class="btn btn-default">取消</a>
            </form>
        </div>
    </div>
@stop
