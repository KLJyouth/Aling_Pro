@extends('admin.layouts.app')

@section('title', 'API接口详情')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">API接口详情</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.security.api.interfaces.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left"></i> 返回
                        </a>
                        <a href="{{ route('admin.security.api.interfaces.edit', $interface->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> 编辑
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-link"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">接口名称</span>
                                    <span class="info-box-number">{{ $interface->name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    @if($interface->method == 'GET')
                                    <i class="fas fa-download"></i>
                                    @elseif($interface->method == 'POST')
                                    <i class="fas fa-upload"></i>
                                    @elseif($interface->method == 'PUT')
                                    <i class="fas fa-edit"></i>
                                    @elseif($interface->method == 'DELETE')
                                    <i class="fas fa-trash"></i>
                                    @else
                                    <i class="fas fa-exchange-alt"></i>
                                    @endif
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">请求方法</span>
                                    <span class="info-box-number">{{ $interface->method }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">基本信息</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>接口路径</label>
                                                <p class="form-control-static">
                                                    <code>/api/{{ $interface->path }}</code>
                                                </p>
                                            </div>
                                            <div class="form-group">
                                                <label>所属模块</label>
                                                <p class="form-control-static">{{ $interface->module ?: '未分类' }}</p>
                                            </div>
                                            <div class="form-group">
                                                <label>API版本</label>
                                                <p class="form-control-static">{{ $interface->version }}</p>
                                            </div>
                                            <div class="form-group">
                                                <label>接口描述</label>
                                                <p class="form-control-static">{{ $interface->description ?: '无描述' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>状态</label>
                                                <p class="form-control-static">
                                                    @if($interface->status == 'active')
                                                    <span class="badge badge-success">启用</span>
                                                    @else
                                                    <span class="badge badge-danger">禁用</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="form-group">
                                                <label>风险等级</label>
                                                <p class="form-control-static">
                                                    @if($interface->risk_level == 'high')
                                                    <span class="badge badge-danger">高</span>
                                                    @elseif($interface->risk_level == 'medium')
                                                    <span class="badge badge-warning">中</span>
                                                    @elseif($interface->risk_level == 'low')
                                                    <span class="badge badge-info">低</span>
                                                    @else
                                                    <span class="badge badge-secondary">未设置</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="form-group">
                                                <label>创建时间</label>
                                                <p class="form-control-static">{{ $interface->created_at }}</p>
                                            </div>
                                            <div class="form-group">
                                                <label>更新时间</label>
                                                <p class="form-control-static">{{ $interface->updated_at }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">监控设置</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>监控状态</label>
                                        <p class="form-control-static">
                                            @if($interface->is_monitored)
                                            <span class="badge badge-success">已启用</span>
                                            @else
                                            <span class="badge badge-secondary">未启用</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="form-group">
                                        <label>认证要求</label>
                                        <p class="form-control-static">
                                            @if($interface->need_auth)
                                            <span class="badge badge-primary">需要认证</span>
                                            @else
                                            <span class="badge badge-secondary">无需认证</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="form-group">
                                        <label>速率限制</label>
                                        <p class="form-control-static">
                                            @if($interface->rate_limit > 0)
                                            {{ $interface->rate_limit }} 请求/分钟
                                            @else
                                            无限制
                                            @endif
                                        </p>
                                    </div>
                                    <div class="form-group">
                                        <label>超时时间</label>
                                        <p class="form-control-static">{{ $interface->timeout }} 毫秒</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">统计信息</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>今日请求数</label>
                                        <p class="form-control-static">{{ number_format($todayStats->total_requests ?? 0) }}</p>
                                    </div>
                                    <div class="form-group">
                                        <label>今日成功率</label>
                                        <p class="form-control-static">
                                            @if(isset($todayStats->total_requests) && $todayStats->total_requests > 0)
                                            {{ number_format(($todayStats->success_requests / $todayStats->total_requests) * 100, 2) }}%
                                            @else
                                            0%
                                            @endif
                                        </p>
                                    </div>
                                    <div class="form-group">
                                        <label>平均响应时间</label>
                                        <p class="form-control-static">
                                            @if(isset($todayStats->avg_response_time))
                                            {{ number_format($todayStats->avg_response_time, 2) }} ms
                                            @if($todayStats->avg_response_time > 500)
                                            <span class="badge badge-danger">慢</span>
                                            @elseif($todayStats->avg_response_time > 200)
                                            <span class="badge badge-warning">中</span>
                                            @else
                                            <span class="badge badge-success">快</span>
                                            @endif
                                            @else
                                            - ms
                                            @endif
                                        </p>
                                    </div>
                                    <div class="form-group">
                                        <label>今日风险事件</label>
                                        <p class="form-control-static">{{ number_format($todayStats->risk_events ?? 0) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">请求参数定义</h4>
                                </div>
                                <div class="card-body">
                                    @if($interface->request_params)
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>参数名</th>
                                                    <th>类型</th>
                                                    <th>是否必须</th>
                                                    <th>描述</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(json_decode($interface->request_params) as $param)
                                                <tr>
                                                    <td>{{ $param->name }}</td>
                                                    <td>{{ $param->type }}</td>
                                                    <td>
                                                        @if($param->required)
                                                        <span class="badge badge-danger">必须</span>
                                                        @else
                                                        <span class="badge badge-secondary">可选</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $param->description }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <p class="text-muted">未定义请求参数</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">响应结构定义</h4>
                                </div>
                                <div class="card-body">
                                    @if($interface->response_structure)
                                    <pre><code class="json">{{ json_encode(json_decode($interface->response_structure), JSON_PRETTY_PRINT) }}</code></pre>
                                    @else
                                    <p class="text-muted">未定义响应结构</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">关联的风控规则</h4>
                                </div>
                                <div class="card-body">
                                    @if(count($riskRules) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>规则名称</th>
                                                    <th>风险类型</th>
                                                    <th>风险等级</th>
                                                    <th>状态</th>
                                                    <th>操作</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($riskRules as $rule)
                                                <tr>
                                                    <td>{{ $rule->name }}</td>
                                                    <td>{{ $rule->risk_type }}</td>
                                                    <td>
                                                        @if($rule->risk_level == 'high')
                                                        <span class="badge badge-danger">高</span>
                                                        @elseif($rule->risk_level == 'medium')
                                                        <span class="badge badge-warning">中</span>
                                                        @else
                                                        <span class="badge badge-info">低</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($rule->status == 'active')
                                                        <span class="badge badge-success">启用</span>
                                                        @else
                                                        <span class="badge badge-danger">禁用</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.security.api.risk-rules.edit', $rule->id) }}" class="btn btn-xs btn-primary">
                                                            <i class="fas fa-edit"></i> 编辑
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <p class="text-muted">暂无关联的风控规则</p>
                                    @endif
                                    
                                    <div class="mt-3">
                                        <a href="{{ route('admin.security.api.risk-rules.create', ['interface_id' => $interface->id]) }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> 添加风控规则
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.security.api.interfaces.edit', $interface->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> 编辑接口
                    </a>
                    <a href="{{ route('admin.security.api.interfaces.monitor', $interface->id) }}" class="btn btn-success">
                        <i class="fas fa-chart-line"></i> 查看监控
                    </a>
                    <form action="{{ route('admin.security.api.interfaces.destroy', $interface->id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('确定要删除该接口吗？')">
                            <i class="fas fa-trash"></i> 删除接口
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // 高亮JSON代码
        document.querySelectorAll('pre code').forEach((block) => {
            hljs.highlightBlock(block);
        });
    });
</script>
@endsection 