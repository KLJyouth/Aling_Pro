@extends('admin.layouts.app')

@section('title', '编辑API接口')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">编辑API接口</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.security.api.interfaces.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left"></i> 返回
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <form method="POST" action="{{ route('admin.security.api.interfaces.update', $interface->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">接口名称</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $interface->name) }}" placeholder="输入接口名称" required>
                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="method">请求方法</label>
                                    <select class="form-control @error('method') is-invalid @enderror" id="method" name="method" required>
                                        <option value="GET" {{ old('method', $interface->method) == 'GET' ? 'selected' : '' }}>GET</option>
                                        <option value="POST" {{ old('method', $interface->method) == 'POST' ? 'selected' : '' }}>POST</option>
                                        <option value="PUT" {{ old('method', $interface->method) == 'PUT' ? 'selected' : '' }}>PUT</option>
                                        <option value="DELETE" {{ old('method', $interface->method) == 'DELETE' ? 'selected' : '' }}>DELETE</option>
                                        <option value="PATCH" {{ old('method', $interface->method) == 'PATCH' ? 'selected' : '' }}>PATCH</option>
                                        <option value="OPTIONS" {{ old('method', $interface->method) == 'OPTIONS' ? 'selected' : '' }}>OPTIONS</option>
                                        <option value="HEAD" {{ old('method', $interface->method) == 'HEAD' ? 'selected' : '' }}>HEAD</option>
                                    </select>
                                    @error('method')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="path">接口路径</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">/api/</span>
                                        </div>
                                        <input type="text" class="form-control @error('path') is-invalid @enderror" id="path" name="path" value="{{ old('path', $interface->path) }}" placeholder="例如：users/{id}" required>
                                    </div>
                                    @error('path')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                    <small class="form-text text-muted">使用 {parameter} 表示路径参数，例如：users/{id}/posts/{post_id}</small>
                                </div>
                                <div class="form-group">
                                    <label for="description">接口描述</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="输入接口描述">{{ old('description', $interface->description) }}</textarea>
                                    @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="module">所属模块</label>
                                    <input type="text" class="form-control @error('module') is-invalid @enderror" id="module" name="module" value="{{ old('module', $interface->module) }}" placeholder="输入所属模块，例如：用户管理">
                                    @error('module')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="version">API版本</label>
                                    <input type="text" class="form-control @error('version') is-invalid @enderror" id="version" name="version" value="{{ old('version', $interface->version) }}" placeholder="输入API版本，例如：v1">
                                    @error('version')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="status">状态</label>
                                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                        <option value="active" {{ old('status', $interface->status) == 'active' ? 'selected' : '' }}>启用</option>
                                        <option value="inactive" {{ old('status', $interface->status) == 'inactive' ? 'selected' : '' }}>禁用</option>
                                    </select>
                                    @error('status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="risk_level">风险等级</label>
                                    <select class="form-control @error('risk_level') is-invalid @enderror" id="risk_level" name="risk_level">
                                        <option value="low" {{ old('risk_level', $interface->risk_level) == 'low' ? 'selected' : '' }}>低</option>
                                        <option value="medium" {{ old('risk_level', $interface->risk_level) == 'medium' ? 'selected' : '' }}>中</option>
                                        <option value="high" {{ old('risk_level', $interface->risk_level) == 'high' ? 'selected' : '' }}>高</option>
                                    </select>
                                    @error('risk_level')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">监控设置</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="is_monitored" name="is_monitored" value="1" {{ old('is_monitored', $interface->is_monitored) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="is_monitored">启用监控</label>
                                                    </div>
                                                    <small class="form-text text-muted">启用后，系统将记录该接口的请求日志并进行风控分析</small>
                                                </div>
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="need_auth" name="need_auth" value="1" {{ old('need_auth', $interface->need_auth) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="need_auth">需要认证</label>
                                                    </div>
                                                    <small class="form-text text-muted">标记该接口是否需要用户认证才能访问</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="rate_limit">速率限制（每分钟）</label>
                                                    <input type="number" class="form-control @error('rate_limit') is-invalid @enderror" id="rate_limit" name="rate_limit" value="{{ old('rate_limit', $interface->rate_limit) }}" min="0">
                                                    @error('rate_limit')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                    <small class="form-text text-muted">设置为0表示不限制</small>
                                                </div>
                                                <div class="form-group">
                                                    <label for="timeout">超时时间（毫秒）</label>
                                                    <input type="number" class="form-control @error('timeout') is-invalid @enderror" id="timeout" name="timeout" value="{{ old('timeout', $interface->timeout) }}" min="0">
                                                    @error('timeout')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">参数配置</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="request_params">请求参数定义（JSON格式）</label>
                                            <textarea class="form-control @error('request_params') is-invalid @enderror" id="request_params" name="request_params" rows="5" placeholder='[{"name": "username", "type": "string", "required": true, "description": "用户名"}, {"name": "password", "type": "string", "required": true, "description": "密码"}]'>{{ old('request_params', $interface->request_params) }}</textarea>
                                            @error('request_params')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                            <small class="form-text text-muted">使用JSON数组定义请求参数，每个参数包含name、type、required和description字段</small>
                                        </div>
                                        <div class="form-group">
                                            <label for="response_structure">响应结构定义（JSON格式）</label>
                                            <textarea class="form-control @error('response_structure') is-invalid @enderror" id="response_structure" name="response_structure" rows="5" placeholder='{"code": 200, "message": "success", "data": {"id": 1, "username": "example"}}'>{{ old('response_structure', $interface->response_structure) }}</textarea>
                                            @error('response_structure')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                            <small class="form-text text-muted">使用JSON对象定义响应结构</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">保存</button>
                        <a href="{{ route('admin.security.api.interfaces.index') }}" class="btn btn-default">取消</a>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // 自动格式化JSON
        $('#request_params, #response_structure').on('blur', function() {
            try {
                var value = $(this).val();
                if (value) {
                    var formatted = JSON.stringify(JSON.parse(value), null, 2);
                    $(this).val(formatted);
                }
            } catch (e) {
                // 如果不是有效的JSON，保持原样
            }
        });
    });
</script>
@endsection 