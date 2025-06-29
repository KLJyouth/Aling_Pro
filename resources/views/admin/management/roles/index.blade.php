@extends('admin.layouts.app')

@section('title', '角色管理')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">角色列表</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.management.roles.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> 添加角色
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>角色名称</th>
                                    <th>角色标识</th>
                                    <th>描述</th>
                                    <th>用户数量</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                <tr>
                                    <td>{{ $role->id }}</td>
                                    <td>{{ $role->name }}</td>
                                    <td><code>{{ $role->slug }}</code></td>
                                    <td>{{ $role->description }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $role->users_count }}</span>
                                    </td>
                                    <td>{{ $role->created_at }}</td>
                                    <td>
                                        <a href="{{ route('admin.management.roles.show', $role->id) }}" class="btn btn-xs btn-info">
                                            <i class="fas fa-eye"></i> 查看
                                        </a>
                                        <a href="{{ route('admin.management.roles.edit', $role->id) }}" class="btn btn-xs btn-primary">
                                            <i class="fas fa-edit"></i> 编辑
                                        </a>
                                        @if(!$role->is_protected)
                                        <form action="{{ route('admin.management.roles.destroy', $role->id) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('确定要删除该角色吗？')">
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
                    {{ $roles->links() }}
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>
@endsection 