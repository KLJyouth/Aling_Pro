@extends('admin.layouts.app')

@section('title', '权限组管理')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">权限组列表</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.permission-groups.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> 添加权限组
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <form action="{{ route('admin.permission-groups.index') }}" method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <input type="text" name="keyword" class="form-control" placeholder="权限组名称/显示名称" value="{{ request('keyword') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 搜索
                                </button>
                                <a href="{{ route('admin.permission-groups.index') }}" class="btn btn-default">
                                    <i class="fas fa-redo"></i> 重置
                                </a>
                            </div>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>权限组名称</th>
                                    <th>显示名称</th>
                                    <th>描述</th>
                                    <th>权限数量</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permissionGroups as $group)
                                <tr>
                                    <td>{{ $group->id }}</td>
                                    <td>{{ $group->name }}</td>
                                    <td>{{ $group->display_name }}</td>
                                    <td>{{ $group->description }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $group->permissions->count() }}</span>
                                    </td>
                                    <td>{{ $group->created_at }}</td>
                                    <td>
                                        <a href="{{ route('admin.permission-groups.show', $group->id) }}" class="btn btn-xs btn-info">
                                            <i class="fas fa-eye"></i> 查看
                                        </a>
                                        <a href="{{ route('admin.permission-groups.edit', $group->id) }}" class="btn btn-xs btn-primary">
                                            <i class="fas fa-edit"></i> 编辑
                                        </a>
                                        <form action="{{ route('admin.permission-groups.destroy', $group->id) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('确定要删除该权限组吗？')">
                                                <i class="fas fa-trash"></i> 删除
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{ $permissionGroups->appends(request()->except('page'))->links() }}
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>
@endsection 