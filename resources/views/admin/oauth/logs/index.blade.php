@extends("admin.layouts.app")

@section("title", "OAuth日志")

@section("content_header")
    <h1>OAuth日志</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">OAuth操作日志列表</h3>
                <div>
                    <button type="button" class="btn btn-primary" data-toggle="collapse" data-target="#filterCollapse">
                        <i class="fas fa-filter"></i> 筛选
                    </button>
                </div>
            </div>
        </div>
        
        <div class="collapse" id="filterCollapse">
            <div class="card-body">
                <form action="{{ route("admin.oauth.logs.index") }}" method="GET">
                    <div class="row">
                        <div class="col-md-3">
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
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="action">操作类型</label>
                                <select class="form-control" id="action" name="action">
                                    <option value="">全部操作</option>
                                    <option value="login" {{ request("action") == "login" ? "selected" : "" }}>登录</option>
                                    <option value="register" {{ request("action") == "register" ? "selected" : "" }}>注册</option>
                                    <option value="link" {{ request("action") == "link" ? "selected" : "" }}>关联</option>
                                    <option value="unlink" {{ request("action") == "unlink" ? "selected" : "" }}>解除关联</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">状态</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="">全部状态</option>
                                    <option value="success" {{ request("status") == "success" ? "selected" : "" }}>成功</option>
                                    <option value="failed" {{ request("status") == "failed" ? "selected" : "" }}>失败</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="user_id">用户ID</label>
                                <input type="text" class="form-control" id="user_id" name="user_id" value="{{ request("user_id") }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="start_date">开始日期</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request("start_date") }}">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="end_date">结束日期</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request("end_date") }}">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="sort_field">排序字段</label>
                                <select class="form-control" id="sort_field" name="sort_field">
                                    <option value="created_at" {{ request("sort_field", "created_at") == "created_at" ? "selected" : "" }}>时间</option>
                                    <option value="id" {{ request("sort_field") == "id" ? "selected" : "" }}>ID</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
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
                        <a href="{{ route("admin.oauth.logs.index") }}" class="btn btn-default">
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
                        <th>操作</th>
                        <th>状态</th>
                        <th>IP地址</th>
                        <th>时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>
                                @if($log->provider)
                                    <i class="{{ $log->provider->icon }}"></i> {{ $log->provider->name }}
                                @else
                                    未知
                                @endif
                            </td>
                            <td>
                                @if($log->user)
                                    {{ $log->user->name }}
                                @else
                                    -
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
                            <td>{{ $log->created_at }}</td>
                            <td>
                                <a href="{{ route("admin.oauth.logs.show", $log->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> 查看
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    
                    @if($logs->isEmpty())
                        <tr>
                            <td colspan="8" class="text-center">暂无日志记录</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <div class="card-footer">
            {{ $logs->appends(request()->query())->links() }}
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
