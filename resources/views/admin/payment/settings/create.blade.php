@extends("admin.layouts.app")

@section("title", "添加支付设置")

@section("content")
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">添加支付设置</h3>
                    <div class="card-tools">
                        <a href="{{ route("admin.payment.settings.index") }}" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> 返回设置
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session("error"))
                        <div class="alert alert-danger">
                            {{ session("error") }}
                        </div>
                    @endif
                    
                    <form action="{{ route("admin.payment.settings.store") }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label for="key">设置键 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error("key") is-invalid @enderror" id="key" name="key" value="{{ old("key") }}" required>
                            <small class="form-text text-muted">设置键只能包含字母、数字和下划线</small>
                            @error("key")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="value">设置值 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error("value") is-invalid @enderror" id="value" name="value" value="{{ old("value") }}" required>
                            @error("value")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="group">分组 <span class="text-danger">*</span></label>
                            <select class="form-control @error("group") is-invalid @enderror" id="group" name="group" required>
                                <option value="general" {{ old("group") === "general" ? "selected" : "" }}>基本设置 (general)</option>
                                <option value="notification" {{ old("group") === "notification" ? "selected" : "" }}>通知设置 (notification)</option>
                                <option value="security" {{ old("group") === "security" ? "selected" : "" }}>安全设置 (security)</option>
                                <option value="custom" {{ old("group") === "custom" ? "selected" : "" }}>自定义 (custom)</option>
                            </select>
                            @error("group")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="description">描述</label>
                            <input type="text" class="form-control @error("description") is-invalid @enderror" id="description" name="description" value="{{ old("description") }}">
                            @error("description")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 保存
                            </button>
                            <a href="{{ route("admin.payment.settings.index") }}" class="btn btn-default">
                                <i class="fas fa-times"></i> 取消
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
