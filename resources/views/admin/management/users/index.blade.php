@extends('admin.layouts.app')

@section('title', '用户管理')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">用户列表</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.management.users.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> 添加用户
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <form action="{{ route('admin.management.users.index') }}" method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <input type="text" name="name" class="form-control" placeholder="用户名" value="{{ request('name') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <input type="text" name="email" class="form-control" placeholder="邮箱" value="{{ request('email') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select name="role_id" class="form-control">
                                        <option value="">所有角色</option>
                                        @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select name="status" class="form-control">
                                        <option value="">所有状态</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>正常</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>禁用</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 搜索
                                </button>
                                <a href="{{ route('admin.management.users.index') }}" class="btn btn-default">
                                    <i class="fas fa-redo"></i> 重置
                                </a>
                                <button type="submit" name="export" value="1" class="btn btn-success">
                                    <i class="fas fa-download"></i> 导出
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>用户名</th>
                                    <th>邮箱</th>
                                    <th>角色</th>
                                    <th>状态</th>
                                    <th>最后登录</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @foreach($user->roles as $role)
                                        <span class="badge badge-info">{{ $role->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if($user->status == 'active')
                                        <span class="badge badge-success">正常</span>
                                        @else
                                        <span class="badge badge-danger">禁用</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->last_login_at)
                                        {{ $user->last_login_at }}
                                        @else
                                        <span class="text-muted">从未登录</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at }}</td>
                                    <td>
                                        <a href="{{ route('admin.management.users.show', $user->id) }}" class="btn btn-xs btn-info">
                                            <i class="fas fa-eye"></i> 查看
                                        </a>
                                        <a href="{{ route('admin.management.users.edit', $user->id) }}" class="btn btn-xs btn-primary">
                                            <i class="fas fa-edit"></i> 编辑
                                        </a>
                                        <a href="{{ route('admin.management.users.edit_password', $user->id) }}" class="btn btn-xs btn-warning">
                                            <i class="fas fa-key"></i> 修改密码
                                        </a>
                                        @if(!$user->is_protected)
                                        <form action="{{ route('admin.management.users.destroy', $user->id) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('确定要删除该用户吗？')">
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
                    {{ $users->appends(request()->except('page'))->links() }}
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>
@endsection 