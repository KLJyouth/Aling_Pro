@extends('admin.layouts.app')

@section('title', 'API接口管理')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">API接口列表</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.security.api.interfaces.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> 添加接口
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <form action="{{ route('admin.security.api.interfaces.index') }}" method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <input type="text" name="keyword" class="form-control" placeholder="接口名称/路径" value="{{ request('keyword') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select name="method" class="form-control">
                                        <option value="">所有方法</option>
                                        <option value="GET" {{ request('method') == 'GET' ? 'selected' : '' }}>GET</option>
                                        <option value="POST" {{ request('method') == 'POST' ? 'selected' : '' }}>POST</option>
                                        <option value="PUT" {{ request('method') == 'PUT' ? 'selected' : '' }}>PUT</option>
                                        <option value="DELETE" {{ request('method') == 'DELETE' ? 'selected' : '' }}>DELETE</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select name="status" class="form-control">
                                        <option value="">所有状态</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>启用</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>禁用</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 搜索
                                </button>
                                <a href="{{ route('admin.security.api.interfaces.index') }}" class="btn btn-default">
                                    <i class="fas fa-redo"></i> 重置
                                </a>
                            </div>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>接口名称</th>
                                    <th>请求方法</th>
                                    <th>路径</th>
                                    <th>状态</th>
                                    <th>风险等级</th>
                                    <th>监控状态</th>
                                    <th>今日请求数</th>
                                    <th>平均响应时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($interfaces as $interface)
                                <tr>
                                    <td>{{ $interface->id }}</td>
                                    <td>{{ $interface->name }}</td>
                                    <td>
                                        @if($interface->method == 'GET')
                                        <span class="badge badge-success">{{ $interface->method }}</span>
                                        @elseif($interface->method == 'POST')
                                        <span class="badge badge-primary">{{ $interface->method }}</span>
                                        @elseif($interface->method == 'PUT')
                                        <span class="badge badge-warning">{{ $interface->method }}</span>
                                        @elseif($interface->method == 'DELETE')
                                        <span class="badge badge-danger">{{ $interface->method }}</span>
                                        @else
                                        <span class="badge badge-info">{{ $interface->method }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $interface->path }}</td>
                                    <td>
                                        @if($interface->status == 'active')
                                        <span class="badge badge-success">启用</span>
                                        @else
                                        <span class="badge badge-danger">禁用</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($interface->risk_level == 'high')
                                        <span class="badge badge-danger">高</span>
                                        @elseif($interface->risk_level == 'medium')
                                        <span class="badge badge-warning">中</span>
                                        @elseif($interface->risk_level == 'low')
                                        <span class="badge badge-info">低</span>
                                        @else
                                        <span class="badge badge-secondary">未设置</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($interface->is_monitored)
                                        <span class="badge badge-success">已监控</span>
                                        @else
                                        <span class="badge badge-secondary">未监控</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($interface->today_requests_count) }}</td>
                                    <td>
                                        @if($interface->avg_response_time)
                                        {{ $interface->avg_response_time }} ms
                                        @if($interface->avg_response_time > 500)
                                        <span class="badge badge-danger">慢</span>
                                        @elseif($interface->avg_response_time > 200)
                                        <span class="badge badge-warning">中</span>
                                        @else
                                        <span class="badge badge-success">快</span>
                                        @endif
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.security.api.interfaces.show', $interface->id) }}" class="btn btn-xs btn-info">
                                                <i class="fas fa-eye"></i> 查看
                                            </a>
                                            <a href="{{ route('admin.security.api.interfaces.edit', $interface->id) }}" class="btn btn-xs btn-primary">
                                                <i class="fas fa-edit"></i> 编辑
                                            </a>
                                            <form action="{{ route('admin.security.api.interfaces.destroy', $interface->id) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('确定要删除该接口吗？')">
                                                    <i class="fas fa-trash"></i> 删除
                                                </button>
                                            </form>
                                        </div>
                                        <div class="btn-group mt-1">
                                            <a href="{{ route('admin.security.api.interfaces.monitor', $interface->id) }}" class="btn btn-xs btn-success">
                                                <i class="fas fa-chart-line"></i> 监控详情
                                            </a>
                                            <a href="{{ route('admin.security.api.risk-rules.index', ['interface_id' => $interface->id]) }}" class="btn btn-xs btn-warning">
                                                <i class="fas fa-shield-alt"></i> 风控规则
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{ $interfaces->appends(request()->except('page'))->links() }}
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>
@endsection 