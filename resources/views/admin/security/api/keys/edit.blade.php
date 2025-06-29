@extends('admin.layouts.app')

@section('title', '编辑API密钥')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">编辑API密钥</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form role="form" method="POST" action="{{ route('admin.security.api.keys.update', $apiKey->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label>用户</label>
                            <input type="text" class="form-control" value="{{ $apiKey->user->name }} ({{ $apiKey->user->email }})" disabled>
                            <small class="form-text text-muted">API密钥所属用户不可修改，如需更换用户请创建新密钥</small>
                        </div>
                        <div class="form-group">
                            <label>密钥前缀</label>
                            <input type="text" class="form-control" value="{{ $apiKey->key_prefix }}" disabled>
                            <small class="form-text text-muted">密钥前缀不可修改，如需更换请重置密钥</small>
                        </div>
                        <div class="form-group">
                            <label for="name">密钥名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="请输入密钥名称" value="{{ old('name', $apiKey->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">用于标识密钥的用途，例如：Web应用、移动应用等</small>
                        </div>
                        <div class="form-group">
                            <label for="expiration_date">过期时间</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="far fa-calendar-alt"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control @error('expiration_date') is-invalid @enderror" id="expiration_date" name="expiration_date" placeholder="选择过期时间" value="{{ old('expiration_date', $apiKey->expiration_date ? $apiKey->expiration_date->format('Y-m-d') : '') }}">
                            </div>
                            @error('expiration_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">留空表示永不过期</small>
                        </div>
                        <div class="form-group">
                            <label for="rate_limit">速率限制（每分钟请求数）</label>
                            <input type="number" class="form-control @error('rate_limit') is-invalid @enderror" id="rate_limit" name="rate_limit" placeholder="请输入速率限制" value="{{ old('rate_limit', $apiKey->rate_limit) }}" min="1">
                            @error('rate_limit')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">留空表示无限制</small>
                        </div>
                        <div class="form-group">
                            <label for="ip_restrictions">IP限制</label>
                            <textarea class="form-control @error('ip_restrictions') is-invalid @enderror" id="ip_restrictions" name="ip_restrictions" placeholder="请输入允许的IP地址，多个IP用逗号分隔">{{ old('ip_restrictions', $apiKey->ip_restrictions) }}</textarea>
                            @error('ip_restrictions')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">支持单个IP（如：192.168.1.1）或CIDR格式（如：192.168.1.0/24），多个IP用逗号分隔。留空表示不限制IP</small>
                        </div>
                        <div class="form-group">
                            <label>权限设置</label>
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">选择API权限</h3>
                                </div>
                                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="permission_all" name="permissions[]" value="*" {{ in_array('*', old('permissions', json_decode($apiKey->permissions) ?? [])) ? 'checked' : '' }}>
                                        <label class="form-check-label font-weight-bold" for="permission_all">
                                            所有权限
                                        </label>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" id="permission_read" name="permissions[]" value="read" {{ in_array('read', old('permissions', json_decode($apiKey->permissions) ?? [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="permission_read">
                                                    读取权限
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" id="permission_write" name="permissions[]" value="write" {{ in_array('write', old('permissions', json_decode($apiKey->permissions) ?? [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="permission_write">
                                                    写入权限
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" id="permission_delete" name="permissions[]" value="delete" {{ in_array('delete', old('permissions', json_decode($apiKey->permissions) ?? [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="permission_delete">
                                                    删除权限
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" id="permission_admin" name="permissions[]" value="admin" {{ in_array('admin', old('permissions', json_decode($apiKey->permissions) ?? [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="permission_admin">
                                                    管理权限
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @error('permissions')
                                <span class="text-danger">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="status">状态</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="active" {{ old('status', $apiKey->status) == 'active' ? 'selected' : '' }}>启用</option>
                                <option value="inactive" {{ old('status', $apiKey->status) == 'inactive' ? 'selected' : '' }}>禁用</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">保存</button>
                        <a href="{{ route('admin.security.api.keys.show', $apiKey->id) }}" class="btn btn-default">取消</a>
                        <button type="button" class="btn btn-warning float-right" data-toggle="modal" data-target="#resetKeyModal">
                            <i class="fas fa-sync-alt"></i> 重置密钥
                        </button>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">API密钥信息</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>密钥ID</th>
                            <td>{{ $apiKey->id }}</td>
                        </tr>
                        <tr>
                            <th>创建时间</th>
                            <td>{{ $apiKey->created_at }}</td>
                        </tr>
                        <tr>
                            <th>最后更新</th>
                            <td>{{ $apiKey->updated_at }}</td>
                        </tr>
                        <tr>
                            <th>最后使用</th>
                            <td>
                                @if($apiKey->last_used_at)
                                    {{ $apiKey->last_used_at }}
                                @else
                                    <span class="text-muted">从未使用</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                    
                    <div class="alert alert-info mt-3">
                        <h5><i class="icon fas fa-info"></i> 提示</h5>
                        <p>如需更改密钥本身，请使用"重置密钥"功能。重置后，旧密钥将立即失效。</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 重置密钥模态框 -->
<div class="modal fade" id="resetKeyModal" tabindex="-1" role="dialog" aria-labelledby="resetKeyModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetKeyModalLabel">重置API密钥</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>您确定要重置密钥 <strong>{{ $apiKey->name }}</strong> 吗？</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 警告：重置后，旧密钥将立即失效，使用该密钥的应用程序将无法访问API，直到更新为新密钥。
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <form action="{{ route('admin.security.api.keys.reset', $apiKey->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-warning">确认重置</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // 日期选择器
        $('#expiration_date').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            minDate: moment().add(1, 'day'),
            locale: {
                format: 'YYYY-MM-DD',
                applyLabel: '确定',
                cancelLabel: '取消',
                daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'],
                monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
                firstDay: 1
            },
            autoUpdateInput: false
        });
        
        $('#expiration_date').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });
        
        $('#expiration_date').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
        
        // 所有权限选择
        $('#permission_all').change(function() {
            if(this.checked) {
                $('input[name="permissions[]"]').prop('checked', true);
            }
        });
        
        // 其他权限选择
        $('input[name="permissions[]"]').not('#permission_all').change(function() {
            if(!this.checked) {
                $('#permission_all').prop('checked', false);
            }
        });
    });
</script>
@endsection 