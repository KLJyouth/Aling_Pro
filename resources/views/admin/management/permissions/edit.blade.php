@extends('admin.layouts.app')

@section('title', '编辑权限')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">编辑权限</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form role="form" method="POST" action="{{ route('admin.management.permissions.update', $permission->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">权限名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="请输入权限名称" value="{{ old('name', $permission->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="slug">权限标识 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" placeholder="请输入权限标识（英文字母、数字和点）" value="{{ old('slug', $permission->slug) }}" {{ $permission->is_protected ? 'readonly' : '' }} required>
                            @error('slug')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">权限标识只能包含英文字母、数字和点，例如：users.create</small>
                            @if($permission->is_protected)
                            <small class="form-text text-warning">此权限为系统保护权限，标识不可修改</small>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="group_id">权限分组</label>
                            <select class="form-control @error('group_id') is-invalid @enderror" id="group_id" name="group_id">
                                <option value="">选择分组</option>
                                @foreach($groups as $group)
                                <option value="{{ $group->id }}" {{ old('group_id', $permission->group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                            @error('group_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <div class="mt-2">
                                <a href="{{ route('admin.management.permissions.groups.create') }}" target="_blank">
                                    <i class="fas fa-plus-circle"></i> 添加新分组
                                </a>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description">权限描述</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="请输入权限描述">{{ old('description', $permission->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        @if($permission->is_protected)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 注意：此权限为系统保护权限，修改可能会影响系统功能。
                        </div>
                        @endif
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">保存</button>
                        <a href="{{ route('admin.management.permissions.index') }}" class="btn btn-default">取消</a>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">拥有此权限的角色</h3>
                </div>
                <div class="card-body">
                    @if(count($roles) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>角色名称</th>
                                    <th>角色标识</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                <tr>
                                    <td>{{ $role->name }}</td>
                                    <td><code>{{ $role->slug }}</code></td>
                                    <td>
                                        <a href="{{ route('admin.management.roles.show', $role->id) }}" class="btn btn-xs btn-info">
                                            <i class="fas fa-eye"></i> 查看角色
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">暂无角色拥有此权限</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // 刷新分组列表
        $('#refresh-groups').click(function(e) {
            e.preventDefault();
            
            $.ajax({
                url: '{{ route("admin.management.permissions.groups.list") }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        var groupSelect = $('#group_id');
                        var selectedValue = groupSelect.val();
                        
                        groupSelect.empty();
                        groupSelect.append('<option value="">选择分组</option>');
                        
                        $.each(response.groups, function(index, group) {
                            groupSelect.append('<option value="' + group.id + '">' + group.name + '</option>');
                        });
                        
                        if(selectedValue) {
                            groupSelect.val(selectedValue);
                        }
                        
                        toastr.success('分组列表已更新');
                    }
                },
                error: function() {
                    toastr.error('更新分组列表失败');
                }
            });
        });
    });
</script>
@endsection 