@extends("admin.layouts.app")

@section("title", "OAuth日志详情")

@section("content_header")
    <h1>OAuth日志详情</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">日志详情 #{{ $log->id }}</h3>
                <a href="{{ route("admin.oauth.logs.index") }}" class="btn btn-default">
                    <i class="fas fa-arrow-left"></i> 返回列表
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%">ID</th>
                            <td>{{ $log->id }}</td>
                        </tr>
                        <tr>
                            <th>提供商</th>
                            <td>
                                @if($log->provider)
                                    <i class="{{ $log->provider->icon }}"></i> {{ $log->provider->name }}
                                @else
                                    未知
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>用户</th>
                            <td>
                                @if($log->user)
                                    {{ $log->user->name }} (ID: {{ $log->user_id }})
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>操作</th>
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
                        </tr>
                        <tr>
                            <th>状态</th>
                            <td>
                                @if($log->status == "success")
                                    <span class="badge badge-success">成功</span>
                                @else
                                    <span class="badge badge-danger">失败</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>IP地址</th>
                            <td>{{ $log->ip_address }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%">用户代理</th>
                            <td>{{ $log->user_agent }}</td>
                        </tr>
                        <tr>
                            <th>错误信息</th>
                            <td>{{ $log->error_message ?: "无" }}</td>
                        </tr>
                        <tr>
                            <th>创建时间</th>
                            <td>{{ $log->created_at }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">请求数据</h3>
                        </div>
                        <div class="card-body">
                            @if($log->request_data)
                                <pre class="json-viewer">{{ json_encode($log->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @else
                                <p class="text-muted">无请求数据</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">响应数据</h3>
                        </div>
                        <div class="card-body">
                            @if($log->response_data)
                                <pre class="json-viewer">{{ json_encode($log->response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @else
                                <p class="text-muted">无响应数据</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @if($log->user)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">用户OAuth账号</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>提供商</th>
                            <th>昵称</th>
                            <th>邮箱</th>
                            <th>关联时间</th>
                            <th>令牌过期时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($log->user->oauthAccounts ?? [] as $account)
                            <tr>
                                <td>
                                    <i class="{{ $account->provider->icon }}"></i> {{ $account->provider->name }}
                                </td>
                                <td>{{ $account->nickname ?: $account->name }}</td>
                                <td>{{ $account->email }}</td>
                                <td>{{ $account->created_at }}</td>
                                <td>
                                    @if($account->token_expires_at)
                                        {{ $account->token_expires_at }}
                                        @if($account->isTokenExpired())
                                            <span class="badge badge-danger">已过期</span>
                                        @else
                                            <span class="badge badge-success">有效</span>
                                        @endif
                                    @else
                                        未设置
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route("admin.oauth.user-accounts.show", $account->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> 查看
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        
                        @if(empty($log->user->oauthAccounts) || $log->user->oauthAccounts->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center">该用户没有关联的OAuth账号</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
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
