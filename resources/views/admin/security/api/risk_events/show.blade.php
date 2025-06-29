@extends('admin.layouts.app')

@section('title', '风险事件详情')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">风险事件详情</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.security.api.risk-events.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left"></i> 返回
                        </a>
                        @if(!$riskEvent->is_handled)
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-warning dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-cog"></i> 处理事件
                            </button>
                            <div class="dropdown-menu">
                                <form action="{{ route('admin.security.api.risk-events.handle', $riskEvent->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="action" value="ignore">
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-check"></i> 标记为已处理
                                    </button>
                                </form>
                                <form action="{{ route('admin.security.api.risk-events.handle', $riskEvent->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="action" value="block">
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-ban"></i> 阻止并标记
                                    </button>
                                </form>
                                <div class="dropdown-divider"></div>
                                <a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-blacklist">
                                    <i class="fas fa-user-slash"></i> 加入黑名单
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    @if($riskEvent->risk_level == 'high')
                                    <i class="fas fa-radiation"></i>
                                    @elseif($riskEvent->risk_level == 'medium')
                                    <i class="fas fa-exclamation-triangle"></i>
                                    @else
                                    <i class="fas fa-info-circle"></i>
                                    @endif
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">风险等级</span>
                                    <span class="info-box-number">
                                        @if($riskEvent->risk_level == 'high')
                                        <span class="badge badge-danger">高</span>
                                        @elseif($riskEvent->risk_level == 'medium')
                                        <span class="badge badge-warning">中</span>
                                        @else
                                        <span class="badge badge-info">低</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    @if($riskEvent->action_taken == 'blocked')
                                    <i class="fas fa-ban"></i>
                                    @elseif($riskEvent->action_taken == 'blacklisted')
                                    <i class="fas fa-user-slash"></i>
                                    @elseif($riskEvent->action_taken == 'captcha')
                                    <i class="fas fa-robot"></i>
                                    @else
                                    <i class="fas fa-clipboard-list"></i>
                                    @endif
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">处理结果</span>
                                    <span class="info-box-number">
                                        @if($riskEvent->action_taken == 'blocked')
                                        <span class="badge badge-danger">已阻止</span>
                                        @elseif($riskEvent->action_taken == 'blacklisted')
                                        <span class="badge badge-dark">已加入黑名单</span>
                                        @elseif($riskEvent->action_taken == 'captcha')
                                        <span class="badge badge-warning">要求验证码</span>
                                        @elseif($riskEvent->action_taken == 'logged')
                                        <span class="badge badge-info">已记录</span>
                                        @else
                                        <span class="badge badge-secondary">{{ $riskEvent->action_taken }}</span>
                                        @endif
                                        
                                        @if($riskEvent->is_handled)
                                        <span class="badge badge-success ml-2">已处理</span>
                                        @else
                                        <span class="badge badge-warning ml-2">未处理</span>
                                        @endif
                                    </span>
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
                                            <table class="table table-bordered">
                                                <tr>
                                                    <th style="width: 30%">事件ID</th>
                                                    <td>{{ $riskEvent->id }}</td>
                                                </tr>
                                                <tr>
                                                    <th>发生时间</th>
                                                    <td>{{ $riskEvent->created_at }}</td>
                                                </tr>
                                                <tr>
                                                    <th>风险类型</th>
                                                    <td>{{ $riskEvent->risk_type }}</td>
                                                </tr>
                                                <tr>
                                                    <th>触发规则</th>
                                                    <td>
                                                        @if($riskEvent->risk_rule)
                                                        <a href="{{ route('admin.security.api.risk-rules.show', $riskEvent->risk_rule->id) }}">
                                                            {{ $riskEvent->risk_rule->name }}
                                                        </a>
                                                        @else
                                                        未知规则
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>接口信息</th>
                                                    <td>
                                                        @if($riskEvent->api_interface)
                                                        <a href="{{ route('admin.security.api.interfaces.show', $riskEvent->api_interface->id) }}">
                                                            {{ $riskEvent->api_interface->name }}
                                                        </a>
                                                        <br>
                                                        <small class="text-muted">{{ $riskEvent->api_interface->method }} /api/{{ $riskEvent->api_interface->path }}</small>
                                                        @else
                                                        未知接口
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <th style="width: 30%">IP地址</th>
                                                    <td>
                                                        {{ $riskEvent->ip_address }}
                                                        <a href="{{ route('admin.security.api.risk-events.index', ['ip_address' => $riskEvent->ip_address]) }}" class="btn btn-xs btn-info ml-2">
                                                            <i class="fas fa-search"></i> 查看该IP的所有事件
                                                        </a>
                                                        
                                                        @if($isInBlacklist)
                                                        <span class="badge badge-danger ml-2">黑名单</span>
                                                        @else
                                                        <a href="#" class="btn btn-xs btn-warning ml-2" data-toggle="modal" data-target="#modal-blacklist">
                                                            <i class="fas fa-user-slash"></i> 加入黑名单
                                                        </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>IP位置</th>
                                                    <td>{{ $riskEvent->ip_location ?: '未知' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>用户ID</th>
                                                    <td>
                                                        @if($riskEvent->user_id)
                                                        {{ $riskEvent->user_id }}
                                                        <a href="{{ route('admin.security.api.risk-events.index', ['user_id' => $riskEvent->user_id]) }}" class="btn btn-xs btn-info ml-2">
                                                            <i class="fas fa-search"></i> 查看该用户的所有事件
                                                        </a>
                                                        @else
                                                        未登录用户
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>User Agent</th>
                                                    <td>
                                                        <small>{{ $riskEvent->user_agent ?: '未知' }}</small>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Referer</th>
                                                    <td>
                                                        <small>{{ $riskEvent->referer ?: '无' }}</small>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">请求详情</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>请求URL</label>
                                                <input type="text" class="form-control" value="{{ $riskEvent->request_url }}" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label>请求方法</label>
                                                <input type="text" class="form-control" value="{{ $riskEvent->request_method }}" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label>请求头</label>
                                                <div class="card">
                                                    <div class="card-body p-0">
                                                        <pre class="m-0 p-2"><code class="json">{{ json_encode(json_decode($riskEvent->request_headers), JSON_PRETTY_PRINT) }}</code></pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>请求参数</label>
                                                <div class="card">
                                                    <div class="card-body p-0">
                                                        <pre class="m-0 p-2"><code class="json">{{ json_encode(json_decode($riskEvent->request_params), JSON_PRETTY_PRINT) }}</code></pre>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>请求体</label>
                                                <div class="card">
                                                    <div class="card-body p-0">
                                                        <pre class="m-0 p-2"><code class="json">{{ json_encode(json_decode($riskEvent->request_body), JSON_PRETTY_PRINT) }}</code></pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">风险详情</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>风险描述</label>
                                                <textarea class="form-control" rows="3" readonly>{{ $riskEvent->risk_description }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>风险详情</label>
                                                <div class="card">
                                                    <div class="card-body p-0">
                                                        <pre class="m-0 p-2"><code class="json">{{ json_encode(json_decode($riskEvent->risk_details), JSON_PRETTY_PRINT) }}</code></pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>处理详情</label>
                                                <div class="card">
                                                    <div class="card-body p-0">
                                                        <pre class="m-0 p-2"><code class="json">{{ json_encode(json_decode($riskEvent->action_details), JSON_PRETTY_PRINT) }}</code></pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if(count($relatedEvents) > 0)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">相关风险事件</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>时间</th>
                                                    <th>接口</th>
                                                    <th>风险类型</th>
                                                    <th>风险等级</th>
                                                    <th>处理结果</th>
                                                    <th>操作</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($relatedEvents as $event)
                                                <tr>
                                                    <td>{{ $event->id }}</td>
                                                    <td>{{ $event->created_at }}</td>
                                                    <td>
                                                        @if($event->api_interface)
                                                        {{ $event->api_interface->name }}
                                                        @else
                                                        未知接口
                                                        @endif
                                                    </td>
                                                    <td>{{ $event->risk_type }}</td>
                                                    <td>
                                                        @if($event->risk_level == 'high')
                                                        <span class="badge badge-danger">高</span>
                                                        @elseif($event->risk_level == 'medium')
                                                        <span class="badge badge-warning">中</span>
                                                        @else
                                                        <span class="badge badge-info">低</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($event->action_taken == 'blocked')
                                                        <span class="badge badge-danger">已阻止</span>
                                                        @elseif($event->action_taken == 'blacklisted')
                                                        <span class="badge badge-dark">已加入黑名单</span>
                                                        @elseif($event->action_taken == 'captcha')
                                                        <span class="badge badge-warning">要求验证码</span>
                                                        @elseif($event->action_taken == 'logged')
                                                        <span class="badge badge-info">已记录</span>
                                                        @else
                                                        <span class="badge badge-secondary">{{ $event->action_taken }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.security.api.risk-events.show', $event->id) }}" class="btn btn-xs btn-info">
                                                            <i class="fas fa-eye"></i> 查看
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.security.api.risk-events.index') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> 返回列表
                    </a>
                    @if(!$riskEvent->is_handled)
                    <form action="{{ route('admin.security.api.risk-events.handle', $riskEvent->id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="action" value="ignore">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> 标记为已处理
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 黑名单模态框 -->
<div class="modal fade" id="modal-blacklist">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">添加到黑名单</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.security.api.blacklists.store') }}">
                @csrf
                <input type="hidden" name="event_id" value="{{ $riskEvent->id }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="blacklist-ip">IP地址</label>
                        <input type="text" class="form-control" id="blacklist-ip" name="ip_address" value="{{ $riskEvent->ip_address }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="blacklist-reason">原因</label>
                        <textarea class="form-control" id="blacklist-reason" name="reason" rows="3" placeholder="请输入加入黑名单的原因" required>风险事件ID: {{ $riskEvent->id }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="blacklist-expire">过期时间</label>
                        <select class="form-control" id="blacklist-expire" name="expire_time">
                            <option value="1">1小时</option>
                            <option value="24">24小时</option>
                            <option value="168">7天</option>
                            <option value="720">30天</option>
                            <option value="0" selected>永不过期</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-warning">添加到黑名单</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
@endsection

@section('scripts')
<script>
    $(function() {
        // 高亮代码
        document.querySelectorAll('pre code').forEach((block) => {
            hljs.highlightBlock(block);
        });
    });
</script>
@endsection 