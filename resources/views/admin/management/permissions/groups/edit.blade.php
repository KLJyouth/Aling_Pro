@extends('admin.layouts.app')

@section('title', '编辑权限分组')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">编辑权限分组</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form role="form" method="POST" action="{{ route('admin.management.permissions.groups.update', $group->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">分组名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="请输入分组名称" value="{{ old('name', $group->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="slug">分组标识 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" placeholder="请输入分组标识（英文字母、数字和下划线）" value="{{ old('slug', $group->slug) }}" {{ $group->is_protected ? 'readonly' : '' }} required>
                            @error('slug')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">分组标识只能包含英文字母、数字和下划线，例如：user_management</small>
                            @if($group->is_protected)
                            <small class="form-text text-warning">此分组为系统保护分组，标识不可修改</small>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="description">分组描述</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="请输入分组描述">{{ old('description', $group->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        @if($group->is_protected)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 注意：此分组为系统保护分组，修改可能会影响系统功能。
                        </div>
                        @endif
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">保存</button>
                        <a href="{{ route('admin.management.permissions.groups.index') }}" class="btn btn-default">取消</a>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">分组权限列表</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.management.permissions.index', ['group_id' => $group->id]) }}" class="btn btn-xs btn-info">
                            <i class="fas fa-list"></i> 查看全部
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($permissions) > 0)
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>权限名称</th>
                                    <th>权限标识</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permissions as $permission)
                                <tr>
                                    <td>{{ $permission->name }}</td>
                                    <td><code>{{ $permission->slug }}</code></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">此分组下暂无权限</p>
                    @endif
                </div>
                <div class="card-footer">
                    <span class="badge badge-info">共 {{ $permissions->count() }} 个权限</span>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">操作提示</h3>
                </div>
                <div class="card-body">
                    <ul>
                        <li>修改分组名称不会影响已分配的权限</li>
                        <li>修改分组标识可能会影响开发中的权限检查逻辑</li>
                        <li>如果需要删除分组，请先将分组下的权限移动到其他分组</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // 分组名称自动生成分组标识（仅当标识不是只读时）
        if (!$('#slug').attr('readonly')) {
            $('#name').on('input', function() {
                var name = $(this).val();
                var slug = name.toLowerCase()
                    .replace(/[^a-z0-9\s]/g, '')
                    .replace(/\s+/g, '_');
                $('#slug').val(slug);
            });
        }
    });
</script>
@endsection 