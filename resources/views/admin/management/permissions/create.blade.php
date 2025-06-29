@extends('admin.layouts.app')

@section('title', '添加权限')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">添加权限</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form role="form" method="POST" action="{{ route('admin.management.permissions.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">权限名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="请输入权限名称" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="slug">权限标识 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" placeholder="请输入权限标识（英文字母、数字和点）" value="{{ old('slug') }}" required>
                            @error('slug')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">权限标识只能包含英文字母、数字和点，例如：users.create</small>
                        </div>
                        <div class="form-group">
                            <label for="group_id">权限分组</label>
                            <select class="form-control @error('group_id') is-invalid @enderror" id="group_id" name="group_id">
                                <option value="">选择分组</option>
                                @foreach($groups as $group)
                                <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
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
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="请输入权限描述">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" id="assign_to_admin" name="assign_to_admin" value="1" {{ old('assign_to_admin') ? 'checked' : '' }}>
                                <label for="assign_to_admin" class="custom-control-label">分配给管理员角色</label>
                            </div>
                            <small class="form-text text-muted">选中此项将自动将此权限分配给管理员角色</small>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">提交</button>
                        <a href="{{ route('admin.management.permissions.index') }}" class="btn btn-default">取消</a>
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
        // 权限名称自动生成权限标识
        $('#name').on('input', function() {
            var name = $(this).val();
            var slug = name.toLowerCase()
                .replace(/[^a-z0-9\s]/g, '')
                .replace(/\s+/g, '.');
            $('#slug').val(slug);
        });
        
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