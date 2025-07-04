@extends("admin.layouts.app")

@section("title", "API调用日志")

@section("content_header")
    <h1>API调用日志</h1>
@stop

@section("content")
    <!-- 统计卡片 -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($stats["total_requests"]) }}</h3>
                    <p>总请求次数</p>
                </div>
                <div class="icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($stats["success_rate"], 1) }}%</h3>
                    <p>成功率</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($stats["total_tokens"]) }}</h3>
                    <p>总标记数</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>${{ number_format($stats["total_cost"], 4) }}</h3>
                    <p>总成本</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">API调用日志列表</h3>
                <div>
                    <button type="button" class="btn btn-primary" data-toggle="collapse" data-target="#filterCollapse">
                        <i class="fas fa-filter"></i> 筛选
                    </button>
                    <a href="{{ route("admin.ai.logs.api.export", request()->query()) }}" class="btn btn-success">
                        <i class="fas fa-file-export"></i> 导出CSV
                    </a>
                </div>
            </div>
        </div>
        
        <div class="collapse" id="filterCollapse">
            <div class="card-body">
                <form action="{{ route("admin.ai.logs.api") }}" method="GET">
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
                                <label for="model_id">模型</label>
                                <select class="form-control" id="model_id" name="model_id">
                                    <option value="">全部模型</option>
                                    @foreach($models as $model)
                                        <option value="{{ $model->id }}" {{ request("model_id") == $model->id ? "selected" : "" }}>
                                            {{ $model->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="agent_id">智能体</label>
                                <select class="form-control" id="agent_id" name="agent_id">
                                    <option value="">全部智能体</option>
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}" {{ request("agent_id") == $agent->id ? "selected" : "" }}>
                                            {{ $agent->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">状态</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="">全部状态</option>
                                    <option value="success" {{ request("status") == "success" ? "selected" : "" }}>成功</option>
                                    <option value="error" {{ request("status") == "error" ? "selected" : "" }}>错误</option>
                                    <option value="pending" {{ request("status") == "pending" ? "selected" : "" }}>处理中</option>
                                </select>
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
                                    <option value="created_at" {{ request("sort_field", "created_at") == "created_at" ? "selected" : "" }}>请求时间</option>
                                    <option value="response_time" {{ request("sort_field") == "response_time" ? "selected" : "" }}>响应时间</option>
                                    <option value="input_tokens" {{ request("sort_field") == "input_tokens" ? "selected" : "" }}>输入标记数</option>
                                    <option value="output_tokens" {{ request("sort_field") == "output_tokens" ? "selected" : "" }}>输出标记数</option>
                                    <option value="cost" {{ request("sort_field") == "cost" ? "selected" : "" }}>成本</option>
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
                        <a href="{{ route("admin.ai.logs.api") }}" class="btn btn-default">
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
                        <th>模型/智能体</th>
                        <th>用户</th>
                        <th>请求时间</th>
                        <th>响应时间</th>
                        <th>标记数</th>
                        <th>成本</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>{{ $log->provider ? $log->provider->name : "未知" }}</td>
                            <td>
                                @if($log->agent)
                                    <span class="badge badge-primary">智能体</span> {!! $log->agent->formatted_name !!}
                                @elseif($log->model)
                                    <span class="badge badge-info">模型</span> {!! $log->model->formatted_name !!}
                                @else
                                    未知
                                @endif
                            </td>
                            <td>{{ $log->user ? $log->user->name : "系统" }}</td>
                            <td>{{ $log->created_at }}</td>
                            <td>{{ $log->response_time }}ms</td>
                            <td>
                                <span title="输入: {{ $log->input_tokens }}, 输出: {{ $log->output_tokens }}">
                                    {{ $log->input_tokens + $log->output_tokens }}
                                </span>
                            </td>
                            <td>${{ number_format($log->cost, 4) }}</td>
                            <td>
                                @if($log->status == "success")
                                    <span class="badge badge-success">成功</span>
                                @elseif($log->status == "error")
                                    <span class="badge badge-danger">错误</span>
                                @else
                                    <span class="badge badge-warning">处理中</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route("admin.ai.logs.api.show", $log->id) }}" class="btn btn-sm btn-info">
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
        
        // 初始化提示工具
        $("[title]").tooltip();
    });
</script>
@stop
