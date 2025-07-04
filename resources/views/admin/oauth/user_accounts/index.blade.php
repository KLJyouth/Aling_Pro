@extends("admin.layouts.app")

@section("title", "OAuth用户账号")

@section("content_header")
    <h1>OAuth用户账号</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">OAuth用户账号列表</h3>
                <div>
                    <button type="button" class="btn btn-primary" data-toggle="collapse" data-target="#filterCollapse">
                        <i class="fas fa-filter"></i> 筛选
                    </button>
                </div>
            </div>
        </div>
        
        <div class="collapse" id="filterCollapse">
            <div class="card-body">
                <form action="{{ route("admin.oauth.user-accounts.index") }}" method="GET">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="provider_id">提供商</label>
                                <select class="form-control" id="provider_id" name="provider_id">
                                    <option value="">全部提供商</option>
                                    @foreach($providers as $provider)
                                        <option value="{{ $provider->id }}" {{ request("provider_id") == $provider->id ? "selected" : "" }}>
                                            {{ $provider->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="user_id">用户ID</label>
                                <input type="text" class="form-control" id="user_id" name="user_id" value="{{ request("user_id") }}">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email">邮箱</label>
                                <input type="text" class="form-control" id="email" name="email" value="{{ request("email") }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sort_field">排序字段</label>
                                <select class="form-control" id="sort_field" name="sort_field">
                                    <option value="created_at" {{ request("sort_field", "created_at") == "created_at" ? "selected" : "" }}>关联时间</option>
                                    <option value="id" {{ request("sort_field") == "id" ? "selected" : "" }}>ID</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sort_direction">排序方向</label>
                                <select class="form-control" id="sort_direction" name="sort_direction">
                                    <option value="desc" {{ request("sort_direction", "desc") == "desc" ? "selected" : "" }}>降序</option>
                                    <option value="asc" {{ request("sort_direction") == "asc" ? "selected" : "" }}>升序</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> 搜索
                        </button>
                        <a href="{{ route("admin.oauth.user-accounts.index") }}" class="btn btn-default">
                            <i class="fas fa-redo"></i> 重置
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>提供商</th>
                        <th>用户</th>
                        <th>昵称</th>
                        <th>邮箱</th>
                        <th>令牌状态</th>
                        <th>关联时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($userAccounts as $account)
                        <tr>
                            <td>{{ $account->id }}</td>
                            <td>
                                @if($account->provider)
                                    <i class="{{ $account->provider->icon }}"></i> {{ $account->provider->name }}
                                @else
                                    未知
                                @endif
                            </td>
                            <td>
                                @if($account->user)
                                    {{ $account->user->name }} (ID: {{ $account->user_id }})
                                @else
                                    未知
                                @endif
                            </td>
                            <td>{{ $account->nickname ?: $account->name }}</td>
                            <td>{{ $account->email }}</td>
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
                            <td>{{ $account->created_at }}</td>
                            <td>
                                <a href="{{ route("admin.oauth.user-accounts.show", $account->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> 查看
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    
                    @if($userAccounts->isEmpty())
                        <tr>
                            <td colspan="8" class="text-center">暂无数据</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <div class="card-footer">
            {{ $userAccounts->appends(request()->query())->links() }}
        </div>
    </div>
@stop

@section("js")
<script>
    $(function() {
        // 如果有筛选条件，自动展开筛选面板
        @if(count(request()->query()) > 0)
            $("#filterCollapse").addClass("show");
        @endif
    });
</script>
@stop
