@extends('admin.layouts.app')

@section('title', '用户详情')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">用户详情</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.management.users.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left"></i> 返回
                        </a>
                        <a href="{{ route('admin.management.users.edit', $user->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> 编辑
                        </a>
                        <a href="{{ route('admin.management.users.edit_password', $user->id) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-key"></i> 修改密码
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card card-primary card-outline">
                                <div class="card-body box-profile">
                                    <div class="text-center">
                                        <img class="profile-user-img img-fluid img-circle" src="{{ $user->avatar ? asset($user->avatar) : asset('assets/admin/img/default-avatar.png') }}" alt="用户头像">
                                    </div>
                                    <h3 class="profile-username text-center">{{ $user->name }}</h3>
                                    <p class="text-muted text-center">
                                        @foreach($user->roles as $role)
                                        <span class="badge badge-info">{{ $role->name }}</span>
                                        @endforeach
                                    </p>
                                    <ul class="list-group list-group-unbordered mb-3">
                                        <li class="list-group-item">
                                            <b>状态</b> 
                                            <a class="float-right">
                                                @if($user->status == 'active')
                                                <span class="badge badge-success">正常</span>
                                                @else
                                                <span class="badge badge-danger">禁用</span>
                                                @endif
                                            </a>
                                        </li>
                                        <li class="list-group-item">
                                            <b>注册时间</b> <a class="float-right">{{ $user->created_at->format('Y-m-d H:i') }}</a>
                                        </li>
                                        <li class="list-group-item">
                                            <b>最后登录</b> 
                                            <a class="float-right">
                                                @if($user->last_login_at)
                                                {{ \Carbon\Carbon::parse($user->last_login_at)->format('Y-m-d H:i') }}
                                                @else
                                                <span class="text-muted">从未登录</span>
                                                @endif
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <!-- /.card-body -->
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="card">
                                <div class="card-header p-2">
                                    <ul class="nav nav-pills">
                                        <li class="nav-item"><a class="nav-link active" href="#basic-info" data-toggle="tab">基本信息</a></li>
                                        <li class="nav-item"><a class="nav-link" href="#login-history" data-toggle="tab">登录历史</a></li>
                                        <li class="nav-item"><a class="nav-link" href="#operation-logs" data-toggle="tab">操作日志</a></li>
                                    </ul>
                                </div><!-- /.card-header -->
                                <div class="card-body">
                                    <div class="tab-content">
                                        <div class="active tab-pane" id="basic-info">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <th style="width: 200px;">用户ID</th>
                                                    <td>{{ $user->id }}</td>
                                                </tr>
                                                <tr>
                                                    <th>用户名</th>
                                                    <td>{{ $user->name }}</td>
                                                </tr>
                                                <tr>
                                                    <th>邮箱</th>
                                                    <td>{{ $user->email }}</td>
                                                </tr>
                                                <tr>
                                                    <th>手机号码</th>
                                                    <td>{{ $user->phone ?: '未设置' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>角色</th>
                                                    <td>
                                                        @foreach($user->roles as $role)
                                                        <a href="{{ route('admin.management.roles.show', $role->id) }}" class="badge badge-info">{{ $role->name }}</a>
                                                        @endforeach
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>状态</th>
                                                    <td>
                                                        @if($user->status == 'active')
                                                        <span class="badge badge-success">正常</span>
                                                        @else
                                                        <span class="badge badge-danger">禁用</span>
                                                        @endif
                                                        
                                                        @if($user->is_protected)
                                                        <span class="badge badge-warning">系统保护用户</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>邮箱验证</th>
                                                    <td>
                                                        @if($user->email_verified_at)
                                                        <span class="badge badge-success">已验证</span> {{ $user->email_verified_at }}
                                                        @else
                                                        <span class="badge badge-warning">未验证</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>注册时间</th>
                                                    <td>{{ $user->created_at }}</td>
                                                </tr>
                                                <tr>
                                                    <th>最后更新</th>
                                                    <td>{{ $user->updated_at }}</td>
                                                </tr>
                                                <tr>
                                                    <th>最后登录时间</th>
                                                    <td>{{ $user->last_login_at ?: '从未登录' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>最后登录IP</th>
                                                    <td>{{ $user->last_login_ip ?: '未知' }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!-- /.tab-pane -->
                                        <div class="tab-pane" id="login-history">
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>登录时间</th>
                                                            <th>IP地址</th>
                                                            <th>设备信息</th>
                                                            <th>登录状态</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($loginLogs as $log)
                                                        <tr>
                                                            <td>{{ $log->created_at }}</td>
                                                            <td>{{ $log->ip_address }}</td>
                                                            <td>{{ $log->user_agent }}</td>
                                                            <td>
                                                                @if($log->status == 'success')
                                                                <span class="badge badge-success">成功</span>
                                                                @else
                                                                <span class="badge badge-danger">失败</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center">暂无登录记录</td>
                                                        </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                            @if($loginLogs->count() > 0)
                                            <div class="mt-3">
                                                {{ $loginLogs->links() }}
                                            </div>
                                            @endif
                                        </div>
                                        <!-- /.tab-pane -->
                                        <div class="tab-pane" id="operation-logs">
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>操作时间</th>
                                                            <th>操作类型</th>
                                                            <th>操作内容</th>
                                                            <th>IP地址</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($operationLogs as $log)
                                                        <tr>
                                                            <td>{{ $log->created_at }}</td>
                                                            <td>{{ $log->action }}</td>
                                                            <td>{{ $log->description }}</td>
                                                            <td>{{ $log->ip_address }}</td>
                                                        </tr>
                                                        @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center">暂无操作记录</td>
                                                        </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                            @if($operationLogs->count() > 0)
                                            <div class="mt-3">
                                                {{ $operationLogs->links() }}
                                            </div>
                                            @endif
                                        </div>
                                        <!-- /.tab-pane -->
                                    </div>
                                    <!-- /.tab-content -->
                                </div><!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.management.users.index') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> 返回列表
                    </a>
                    <a href="{{ route('admin.management.users.edit', $user->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> 编辑用户
                    </a>
                    <a href="{{ route('admin.management.users.edit_password', $user->id) }}" class="btn btn-warning">
                        <i class="fas fa-key"></i> 修改密码
                    </a>
                    @if(!$user->is_protected)
                    <form action="{{ route('admin.management.users.destroy', $user->id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('确定要删除该用户吗？')">
                            <i class="fas fa-trash"></i> 删除用户
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 