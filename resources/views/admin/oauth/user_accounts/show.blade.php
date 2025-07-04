@extends("admin.layouts.app")

@section("title", "OAuth用户账号详情")

@section("content_header")
    <h1>OAuth用户账号详情</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">用户账号详情 #{{ $userAccount->id }}</h3>
                <a href="{{ route("admin.oauth.user-accounts.index") }}" class="btn btn-default">
                    <i class="fas fa-arrow-left"></i> 返回列表
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary">
                            <h3 class="card-title">基本信息</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">ID</th>
                                    <td>{{ $userAccount->id }}</td>
                                </tr>
                                <tr>
                                    <th>提供商</th>
                                    <td>
                                        <i class="{{ $userAccount->provider->icon }}"></i> {{ $userAccount->provider->name }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>提供商用户ID</th>
                                    <td>{{ $userAccount->provider_user_id }}</td>
                                </tr>
                                <tr>
                                    <th>昵称</th>
                                    <td>{{ $userAccount->nickname ?: "未设置" }}</td>
                                </tr>
                                <tr>
                                    <th>姓名</th>
                                    <td>{{ $userAccount->name ?: "未设置" }}</td>
                                </tr>
                                <tr>
                                    <th>邮箱</th>
                                    <td>{{ $userAccount->email ?: "未设置" }}</td>
                                </tr>
                                <tr>
                                    <th>头像</th>
                                    <td>
                                        @if($userAccount->avatar)
                                            <img src="{{ $userAccount->avatar }}" alt="头像" class="img-thumbnail" style="max-width: 100px;">
                                        @else
                                            未设置
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>关联时间</th>
                                    <td>{{ $userAccount->created_at }}</td>
                                </tr>
                                <tr>
                                    <th>最后更新</th>
                                    <td>{{ $userAccount->updated_at }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h3 class="card-title">令牌信息</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">访问令牌</th>
                                    <td>{{ $userAccount->access_token ? "已设置 (已加密)" : "未设置" }}</td>
                                </tr>
                                <tr>
                                    <th>刷新令牌</th>
                                    <td>{{ $userAccount->refresh_token ? "已设置 (已加密)" : "未设置" }}</td>
                                </tr>
                                <tr>
                                    <th>令牌过期时间</th>
                                    <td>
                                        @if($userAccount->token_expires_at)
                                            {{ $userAccount->token_expires_at }}
                                            @if($userAccount->isTokenExpired())
                                                <span class="badge badge-danger">已过期</span>
                                            @else
                                                <span class="badge badge-success">有效</span>
                                            @endif
                                        @else
                                            未设置
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-header bg-success">
                            <h3 class="card-title">关联用户信息</h3>
                        </div>
                        <div class="card-body">
                            @if($userAccount->user)
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 30%">用户ID</th>
                                        <td>{{ $userAccount->user->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>用户名</th>
                                        <td>{{ $userAccount->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>邮箱</th>
                                        <td>{{ $userAccount->user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>邮箱验证</th>
                                        <td>
                                            @if($userAccount->user->email_verified_at)
                                                <span class="badge badge-success">已验证</span> {{ $userAccount->user->email_verified_at }}
                                            @else
                                                <span class="badge badge-warning">未验证</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>注册时间</th>
                                        <td>{{ $userAccount->user->created_at }}</td>
                                    </tr>
                                </table>
                            @else
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i> 关联的用户不存在或已被删除
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">原始用户数据</h3>
                        </div>
                        <div class="card-body">
                            @if($userAccount->user_data)
                                <pre class="json-viewer">{{ json_encode($userAccount->user_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @else
                                <p class="text-muted">无原始用户数据</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @if($otherAccounts->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">该用户的其他OAuth账号</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>提供商</th>
                            <th>昵称</th>
                            <th>邮箱</th>
                            <th>关联时间</th>
                            <th>令牌状态</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($otherAccounts as $account)
                            <tr>
                                <td>
                                    <i class="{{ $account->provider->icon }}"></i> {{ $account->provider->name }}
                                </td>
                                <td>{{ $account->nickname ?: $account->name }}</td>
                                <td>{{ $account->email }}</td>
                                <td>{{ $account->created_at }}</td>
                                <td>
                                    @if($account->token_expires_at)
                                        @if($account->isTokenExpired())
                                            <span class="badge badge-danger">已过期</span>
                                        @else
                                            <span class="badge badge-success">有效</span>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary">未设置</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route("admin.oauth.user-accounts.show", $account->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> 查看
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    
    @if($logs->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">最近活动日志</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>时间</th>
                            <th>提供商</th>
                            <th>操作</th>
                            <th>状态</th>
                            <th>IP地址</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>{{ $log->created_at }}</td>
                                <td>
                                    @if($log->provider)
                                        <i class="{{ $log->provider->icon }}"></i> {{ $log->provider->name }}
                                    @else
                                        未知
                                    @endif
                                </td>
                                <td>
                                    @if($log->action == "login")
                                        <span class="badge badge-primary">登录</span>
                                    @elseif($log->action == "register")
                                        <span class="badge badge-success">注册</span>
                                    @elseif($log->action == "link")
                                        <span class="badge badge-info">关联</span>
                                    @elseif($log->action == "unlink")
                                        <span class="badge badge-warning">解除关联</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $log->action }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->status == "success")
                                        <span class="badge badge-success">成功</span>
                                    @else
                                        <span class="badge badge-danger">失败</span>
                                    @endif
                                </td>
                                <td>{{ $log->ip_address }}</td>
                                <td>
                                    <a href="{{ route("admin.oauth.logs.show", $log->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> 查看
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <a href="{{ route("admin.oauth.logs.index", ["user_id" => $userAccount->user_id]) }}" class="btn btn-sm btn-info">
                    查看所有日志
                </a>
            </div>
        </div>
    @endif
@stop

@section("css")
<style>
    .json-viewer {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 3px;
        padding: 10px;
        overflow: auto;
        max-height: 300px;
        font-family: monospace;
        font-size: 13px;
        white-space: pre-wrap;
    }
</style>
@stop
