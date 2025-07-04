@extends("admin.layouts.app")

@section("title", "审计日志")

@section("content_header")
    <h1>审计日志</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">审计日志列表</h3>
                <div>
                    <button type="button" class="btn btn-primary" data-toggle="collapse" data-target="#filterCollapse">
                        <i class="fas fa-filter"></i> 筛选
                    </button>
                    <a href="{{ route("admin.ai.logs.audit.export", request()->query()) }}" class="btn btn-success">
                        <i class="fas fa-file-export"></i> 导出CSV
                    </a>
                </div>
            </div>
        </div>
        
        <div class="collapse" id="filterCollapse">
            <div class="card-body">
                <form action="{{ route("admin.ai.logs.audit") }}" method="GET">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="action">操作类型</label>
                                <select class="form-control" id="action" name="action">
                                    <option value="">全部操作</option>
                                    <option value="create" {{ request("action") == "create" ? "selected" : "" }}>创建</option>
                                    <option value="update" {{ request("action") == "update" ? "selected" : "" }}>更新</option>
                                    <option value="delete" {{ request("action") == "delete" ? "selected" : "" }}>删除</option>
                                    <option value="view" {{ request("action") == "view" ? "selected" : "" }}>查看</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="resource_type">资源类型</label>
                                <select class="form-control" id="resource_type" name="resource_type">
                                    <option value="">全部资源</option>
                                    <option value="provider" {{ request("resource_type") == "provider" ? "selected" : "" }}>提供商</option>
                                    <option value="model" {{ request("resource_type") == "model" ? "selected" : "" }}>模型</option>
                                    <option value="agent" {{ request("resource_type") == "agent" ? "selected" : "" }}>智能体</option>
                                    <option value="api_key" {{ request("resource_type") == "api_key" ? "selected" : "" }}>API密钥</option>
                                    <option value="setting" {{ request("resource_type") == "setting" ? "selected" : "" }}>设置</option>
                                    <option value="provider_style" {{ request("resource_type") == "provider_style" ? "selected" : "" }}>提供商样式</option>
                                    <option value="model_style" {{ request("resource_type") == "model_style" ? "selected" : "" }}>模型样式</option>
                                    <option value="agent_style" {{ request("resource_type") == "agent_style" ? "selected" : "" }}>智能体样式</option>
                                    <option value="advanced_settings" {{ request("resource_type") == "advanced_settings" ? "selected" : "" }}>高级设置</option>
                                </select>
                            </div>
                        </div>
                        
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
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> 搜索
                        </button>
                        <a href="{{ route("admin.ai.logs.audit") }}" class="btn btn-default">
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
                        <th>用户</th>
                        <th>操作</th>
                        <th>资源类型</th>
                        <th>资源ID</th>
                        <th>IP地址</th>
                        <th>操作时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>{{ $log->user ? $log->user->name : "系统" }}</td>
                            <td>
                                @if($log->action == "create")
                                    <span class="badge badge-success">创建</span>
                                @elseif($log->action == "update")
                                    <span class="badge badge-info">更新</span>
                                @elseif($log->action == "delete")
                                    <span class="badge badge-danger">删除</span>
                                @elseif($log->action == "view")
                                    <span class="badge badge-secondary">查看</span>
                                @else
                                    <span class="badge badge-primary">{{ $log->action }}</span>
                                @endif
                            </td>
                            <td>
                                @if($log->resource_type == "provider")
                                    提供商
                                @elseif($log->resource_type == "model")
                                    模型
                                @elseif($log->resource_type == "agent")
                                    智能体
                                @elseif($log->resource_type == "api_key")
                                    API密钥
                                @elseif($log->resource_type == "setting")
                                    设置
                                @elseif($log->resource_type == "provider_style")
                                    提供商样式
                                @elseif($log->resource_type == "model_style")
                                    模型样式
                                @elseif($log->resource_type == "agent_style")
                                    智能体样式
                                @elseif($log->resource_type == "advanced_settings")
                                    高级设置
                                @else
                                    {{ $log->resource_type }}
                                @endif
                            </td>
                            <td>{{ $log->resource_id ?: "N/A" }}</td>
                            <td>{{ $log->ip_address }}</td>
                            <td>{{ $log->created_at }}</td>
                            <td>
                                <a href="{{ route("admin.ai.logs.audit.show", $log->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> 查看
                                </a>
                            </td>
                        </tr>
                    @endforeach
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
