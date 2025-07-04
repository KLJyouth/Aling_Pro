@extends("admin.layouts.app")

@section("title", "OAuth提供商详情")

@section("content_header")
    <h1>OAuth提供商详情</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">{{ $provider->name }} 提供商详情</h3>
                <div>
                    <a href="{{ route("admin.oauth.providers.edit", $provider->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> 编辑
                    </a>
                    <a href="{{ route("admin.oauth.providers.index") }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> 返回列表
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%">ID</th>
                            <td>{{ $provider->id }}</td>
                        </tr>
                        <tr>
                            <th>名称</th>
                            <td>{{ $provider->name }}</td>
                        </tr>
                        <tr>
                            <th>标识符</th>
                            <td>{{ $provider->identifier }}</td>
                        </tr>
                        <tr>
                            <th>图标</th>
                            <td><i class="{{ $provider->icon }} fa-2x"></i> {{ $provider->icon }}</td>
                        </tr>
                        <tr>
                            <th>状态</th>
                            <td>
                                @if($provider->is_active)
                                    <span class="badge badge-success">启用</span>
                                @else
                                    <span class="badge badge-danger">禁用</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>描述</th>
                            <td>{{ $provider->description ?: "无" }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%">客户端ID</th>
                            <td>{{ $provider->client_id ?: "未设置" }}</td>
                        </tr>
                        <tr>
                            <th>客户端密钥</th>
                            <td>{{ $provider->client_secret ? "已设置 (已加密)" : "未设置" }}</td>
                        </tr>
                        <tr>
                            <th>回调URL</th>
                            <td>{{ $provider->redirect_url ?: "未设置" }}</td>
                        </tr>
                        <tr>
                            <th>授权URL</th>
                            <td>{{ $provider->auth_url ?: "未设置" }}</td>
                        </tr>
                        <tr>
                            <th>令牌URL</th>
                            <td>{{ $provider->token_url ?: "未设置" }}</td>
                        </tr>
                        <tr>
                            <th>用户信息URL</th>
                            <td>{{ $provider->user_info_url ?: "未设置" }}</td>
                        </tr>
                        <tr>
                            <th>权限范围</th>
                            <td>
                                @if($provider->scopes)
                                    @foreach($provider->scopes as $scope)
                                        <span class="badge badge-info">{{ $scope }}</span>
                                    @endforeach
                                @else
                                    未设置
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">使用统计</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">关联用户数</span>
                                    <span class="info-box-number">{{ $provider->user_accounts_count }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-sign-in-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">成功登录次数</span>
                                    <span class="info-box-number">{{ $stats["total_logins"] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-user-plus"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">注册用户数</span>
                                    <span class="info-box-number">{{ $stats["total_registrations"] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">失败次数</span>
                                    <span class="info-box-number">{{ $stats["total_failures"] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">最近活动日志</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>时间</th>
                                <th>操作</th>
                                <th>状态</th>
                                <th>用户</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format("Y-m-d H:i:s") }}</td>
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
                                    <td>
                                        @if($log->user)
                                            {{ $log->user->name }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            
                            @if($logs->isEmpty())
                                <tr>
                                    <td colspan="4" class="text-center">暂无日志记录</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route("admin.oauth.logs.index", ["provider_id" => $provider->id]) }}" class="btn btn-sm btn-info">
                        查看所有日志
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">测试OAuth登录</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 测试前请确保已正确配置OAuth提供商信息，并且提供商状态为启用。
            </div>
            
            <div class="text-center">
                @if($provider->is_active && $provider->client_id && $provider->redirect_url)
                    <a href="{{ route("auth.oauth.redirect", $provider->identifier) }}" class="btn btn-lg btn-primary">
                        <i class="{{ $provider->icon }}"></i> 测试{{ $provider->name }}登录
                    </a>
                @else
                    <button class="btn btn-lg btn-secondary" disabled>
                        <i class="fas fa-exclamation-circle"></i> 无法测试（提供商未启用或配置不完整）
                    </button>
                @endif
            </div>
        </div>
    </div>
@stop
