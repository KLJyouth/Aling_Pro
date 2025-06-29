@extends('admin.layouts.app')

@section('title', '权限管理')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">权限列表</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.management.permissions.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> 添加权限
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <form action="{{ route('admin.management.permissions.index') }}" method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <input type="text" name="name" class="form-control" placeholder="权限名称" value="{{ request('name') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <select name="group_id" class="form-control">
                                        <option value="">所有分组</option>
                                        @foreach($groups as $group)
                                        <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 搜索
                                </button>
                                <a href="{{ route('admin.management.permissions.index') }}" class="btn btn-default">
                                    <i class="fas fa-redo"></i> 重置
                                </a>
                                <a href="{{ route('admin.management.permissions.groups.index') }}" class="btn btn-info float-right">
                                    <i class="fas fa-object-group"></i> 管理权限分组
                                </a>
                            </div>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>权限名称</th>
                                    <th>权限标识</th>
                                    <th>权限分组</th>
                                    <th>描述</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permissions as $permission)
                                <tr>
                                    <td>{{ $permission->id }}</td>
                                    <td>{{ $permission->name }}</td>
                                    <td><code>{{ $permission->slug }}</code></td>
                                    <td>
                                        @if($permission->group)
                                        <span class="badge badge-info">{{ $permission->group->name }}</span>
                                        @else
                                        <span class="badge badge-secondary">未分组</span>
                                        @endif
                                    </td>
                                    <td>{{ $permission->description }}</td>
                                    <td>{{ $permission->created_at }}</td>
                                    <td>
                                        <a href="{{ route('admin.management.permissions.edit', $permission->id) }}" class="btn btn-xs btn-primary">
                                            <i class="fas fa-edit"></i> 编辑
                                        </a>
                                        @if(!$permission->is_protected)
                                        <form action="{{ route('admin.management.permissions.destroy', $permission->id) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('确定要删除该权限吗？')">
                                                <i class="fas fa-trash"></i> 删除
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{ $permissions->appends(request()->except('page'))->links() }}
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>
@endsection 