@extends('admin.layouts.app')

@section('title', '角色详情')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">角色详情</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.management.roles.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left"></i> 返回
                        </a>
                        <a href="{{ route('admin.management.roles.edit', $role->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> 编辑
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">基本信息</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 200px;">角色ID</th>
                                            <td>{{ $role->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>角色名称</th>
                                            <td>{{ $role->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>角色标识</th>
                                            <td>
                                                <code>{{ $role->slug }}</code>
                                                @if($role->is_protected)
                                                <span class="badge badge-warning">系统保护角色</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>角色描述</th>
                                            <td>{{ $role->description ?: '无' }}</td>
                                        </tr>
                                        <tr>
                                            <th>创建时间</th>
                                            <td>{{ $role->created_at }}</td>
                                        </tr>
                                        <tr>
                                            <th>更新时间</th>
                                            <td>{{ $role->updated_at }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">统计信息</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">用户数量</span>
                                                    <span class="info-box-number">{{ $role->users_count }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success"><i class="fas fa-key"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">权限数量</span>
                                                    <span class="info-box-number">{{ count($rolePermissions) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">最近添加的用户</h3>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>用户名</th>
                                                    <th>邮箱</th>
                                                    <th>添加时间</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($recentUsers as $user)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('admin.management.users.show', $user->id) }}">
                                                            {{ $user->name }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>{{ $user->created_at }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="3" class="text-center">暂无用户</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('admin.management.users.index', ['role_id' => $role->id]) }}" class="btn btn-sm btn-default">
                                        查看全部用户
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">权限列表</h3>
                                </div>
                                <div class="card-body">
                                    @foreach($permissionGroups as $group)
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title">
                                                {{ $group->name }}
                                                <small class="text-muted">{{ $group->description }}</small>
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($group->permissions as $permission)
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" disabled {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                                        <label class="form-check-label">
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
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.management.roles.index') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> 返回列表
                    </a>
                    <a href="{{ route('admin.management.roles.edit', $role->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> 编辑角色
                    </a>
                    @if(!$role->is_protected)
                    <form action="{{ route('admin.management.roles.destroy', $role->id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('确定要删除该角色吗？')">
                            <i class="fas fa-trash"></i> 删除角色
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 