@extends('admin.layouts.app')

@section('title', '添加角色')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">添加角色</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form role="form" method="POST" action="{{ route('admin.management.roles.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">角色名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="请输入角色名称" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="slug">角色标识 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" placeholder="请输入角色标识（英文字母、数字和下划线）" value="{{ old('slug') }}" required>
                            @error('slug')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">角色标识只能包含英文字母、数字和下划线，例如：admin_manager</small>
                        </div>
                        <div class="form-group">
                            <label for="description">角色描述</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="请输入角色描述">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>权限分配</label>
                            <div class="card">
                                <div class="card-header">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select-all">
                                        <label class="form-check-label" for="select-all">
                                            全选/取消全选
                                        </label>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @foreach($permissionGroups as $group)
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="form-check">
                                                <input class="form-check-input group-select" type="checkbox" id="group-{{ $group->id }}">
                                                <label class="form-check-label" for="group-{{ $group->id }}">
                                                    <strong>{{ $group->name }}</strong> - {{ $group->description }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($group->permissions as $permission)
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input permission-checkbox group-{{ $group->id }}" type="checkbox" id="permission-{{ $permission->id }}" name="permissions[]" value="{{ $permission->id }}" {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="permission-{{ $permission->id }}">
                                                            {{ $permission->name }}
                                                        </label>
                                                        <small class="form-text text-muted">{{ $permission->description }}</small>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">提交</button>
                        <a href="{{ route('admin.management.roles.index') }}" class="btn btn-default">取消</a>
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
        // 角色名称自动生成角色标识
        $('#name').on('input', function() {
            var name = $(this).val();
            var slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
            $('#slug').val(slug);
        });
        
        // 全选/取消全选
        $('#select-all').change(function() {
            $('.permission-checkbox').prop('checked', $(this).prop('checked'));
            $('.group-select').prop('checked', $(this).prop('checked'));
        });
        
        // 分组选择
        $('.group-select').change(function() {
            var groupId = $(this).attr('id').replace('group-', '');
            $('.group-' + groupId).prop('checked', $(this).prop('checked'));
            
            updateSelectAllCheckbox();
        });
        
        // 单个权限选择
        $('.permission-checkbox').change(function() {
            updateGroupCheckboxes();
            updateSelectAllCheckbox();
        });
        
        // 更新分组复选框状态
        function updateGroupCheckboxes() {
            $('.group-select').each(function() {
                var groupId = $(this).attr('id').replace('group-', '');
                var totalPermissions = $('.group-' + groupId).length;
                var checkedPermissions = $('.group-' + groupId + ':checked').length;
                
                $(this).prop('checked', totalPermissions === checkedPermissions && totalPermissions > 0);
            });
        }
        
        // 更新全选复选框状态
        function updateSelectAllCheckbox() {
            var totalPermissions = $('.permission-checkbox').length;
            var checkedPermissions = $('.permission-checkbox:checked').length;
            
            $('#select-all').prop('checked', totalPermissions === checkedPermissions && totalPermissions > 0);
        }
        
        // 初始化复选框状态
        updateGroupCheckboxes();
        updateSelectAllCheckbox();
    });
</script>
@endsection 