@extends('admin.layouts.app')

@section('title', '权限组详情')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">权限组详情</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.permission-groups.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left"></i> 返回
                        </a>
                        <a href="{{ route('admin.permission-groups.edit', $permissionGroup->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> 编辑
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>权限组名称</label>
                                <p class="form-control-static">{{ $permissionGroup->name }}</p>
                            </div>
                            <div class="form-group">
                                <label>显示名称</label>
                                <p class="form-control-static">{{ $permissionGroup->display_name }}</p>
                            </div>
                            <div class="form-group">
                                <label>描述</label>
                                <p class="form-control-static">{{ $permissionGroup->description ?: '无' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>创建时间</label>
                                <p class="form-control-static">{{ $permissionGroup->created_at }}</p>
                            </div>
                            <div class="form-group">
                                <label>更新时间</label>
                                <p class="form-control-static">{{ $permissionGroup->updated_at }}</p>
                            </div>
                            <div class="form-group">
                                <label>包含权限数量</label>
                                <p class="form-control-static">
                                    <span class="badge badge-info">{{ $permissions->count() }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">包含的权限</h4>
                                </div>
                                <div class="card-body">
                                    @if($permissions->count() > 0)
                                    <div class="row">
                                        @php
                                            $permissionsByModule = $permissions->groupBy('module');
                                        @endphp
                                        
                                        @foreach($permissionsByModule as $module => $modulePermissions)
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="card-title">{{ $module }}</h5>
                                                </div>
                                                <div class="card-body">
                                                    <ul class="list-group">
                                                        @foreach($modulePermissions as $permission)
                                                        <li class="list-group-item">
                                                            <strong>{{ $permission->display_name }}</strong>
                                                            @if($permission->description)
                                                            <p class="text-muted mb-0">{{ $permission->description }}</p>
                                                            @endif
                                                        </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @else
                                    <div class="alert alert-warning">
                                        该权限组未包含任何权限
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">使用该权限组的角色</h4>
                                </div>
                                <div class="card-body">
                                    @if($roles->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>角色名称</th>
                                                    <th>显示名称</th>
                                                    <th>状态</th>
                                                    <th>操作</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($roles as $role)
                                                <tr>
                                                    <td>{{ $role->id }}</td>
                                                    <td>{{ $role->name }}</td>
                                                    <td>{{ $role->display_name }}</td>
                                                    <td>
                                                        @if($role->status == 'active')
                                                        <span class="badge badge-success">启用</span>
                                                        @else
                                                        <span class="badge badge-danger">禁用</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.roles.show', $role->id) }}" class="btn btn-xs btn-info">
                                                            <i class="fas fa-eye"></i> 查看
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="alert alert-info">
                                        暂无角色使用该权限组
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <a href="{{ route('admin.permission-groups.edit', $permissionGroup->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> 编辑
                    </a>
                    <form action="{{ route('admin.permission-groups.destroy', $permissionGroup->id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('确定要删除该权限组吗？')">
                            <i class="fas fa-trash"></i> 删除
                        </button>
                    </form>
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>
@endsection 