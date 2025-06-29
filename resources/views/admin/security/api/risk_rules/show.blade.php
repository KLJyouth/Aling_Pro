@extends('admin.layouts.app')

@section('title', '风控规则详情')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">风控规则详情</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.security.api.risk-rules.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left"></i> 返回
                        </a>
                        <a href="{{ route('admin.security.api.risk-rules.edit', $riskRule->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> 编辑
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-shield-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">规则名称</span>
                                    <span class="info-box-number">{{ $riskRule->name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    @if($riskRule->risk_level == 'high')
                                    <i class="fas fa-radiation"></i>
                                    @elseif($riskRule->risk_level == 'medium')
                                    <i class="fas fa-exclamation-triangle"></i>
                                    @else
                                    <i class="fas fa-info-circle"></i>
                                    @endif
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">风险等级</span>
                                    <span class="info-box-number">
                                        @if($riskRule->risk_level == 'high')
                                        <span class="badge badge-danger">高</span>
                                        @elseif($riskRule->risk_level == 'medium')
                                        <span class="badge badge-warning">中</span>
                                        @else
                                        <span class="badge badge-info">低</span>
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
                                            <div class="form-group">
                                                <label>关联接口</label>
                                                <p class="form-control-static">
                                                    @if($riskRule->api_interface)
                                                    <a href="{{ route('admin.security.api.interfaces.show', $riskRule->api_interface->id) }}">
                                                        {{ $riskRule->api_interface->name }} 
                                                        ({{ $riskRule->api_interface->method }} /api/{{ $riskRule->api_interface->path }})
                                                    </a>
                                                    @else
                                                    <span class="badge badge-secondary">全局规则</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="form-group">
                                                <label>风险类型</label>
                                                <p class="form-control-static">{{ $riskRule->risk_type }}</p>
                                            </div>
                                            <div class="form-group">
                                                <label>触发动作</label>
                                                <p class="form-control-static">
                                                    @if($riskRule->action == 'log')
                                                    <span class="badge badge-info">仅记录日志</span>
                                                    @elseif($riskRule->action == 'block')
                                                    <span class="badge badge-danger">阻止请求</span>
                                                    @elseif($riskRule->action == 'captcha')
                                                    <span class="badge badge-warning">要求验证码</span>
                                                    @elseif($riskRule->action == 'add_to_blacklist')
                                                    <span class="badge badge-dark">加入黑名单</span>
                                                    @elseif($riskRule->action == 'custom_action')
                                                    <span class="badge badge-primary">自定义动作</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="form-group">
                                                <label>规则描述</label>
                                                <p class="form-control-static">{{ $riskRule->description ?: '无描述' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>状态</label>
                                                <p class="form-control-static">
                                                    @if($riskRule->status == 'active')
                                                    <span class="badge badge-success">启用</span>
                                                    @else
                                                    <span class="badge badge-danger">禁用</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="form-group">
                                                <label>优先级</label>
                                                <p class="form-control-static">{{ $riskRule->priority }}</p>
                                            </div>
                                            <div class="form-group">
                                                <label>创建时间</label>
                                                <p class="form-control-static">{{ $riskRule->created_at }}</p>
                                            </div>
                                            <div class="form-group">
                                                <label>更新时间</label>
                                                <p class="form-control-static">{{ $riskRule->updated_at }}</p>
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
                                    <h4 class="card-title">规则配置</h4>
                                </div>
                                <div class="card-body">
                                    @if($riskRule->risk_type == 'rate_limit')
                                    <div class="form-group">
                                        <label>请求次数限制</label>
                                        <p class="form-control-static">{{ $config['rate_limit_count'] ?? 60 }} 次 / {{ $config['rate_limit_period'] ?? 60 }} 秒</p>
                                    </div>
                                    <div class="form-group">
                                        <label>限制方式</label>
                                        <p class="form-control-static">
                                            @if(isset($config['rate_limit_per_ip']) && $config['rate_limit_per_ip'])
                                            <span class="badge badge-info">按IP限制</span>
                                            @endif
                                            
                                            @if(isset($config['rate_limit_per_user']) && $config['rate_limit_per_user'])
                                            <span class="badge badge-info">按用户限制</span>
                                            @endif
                                            
                                            @if((!isset($config['rate_limit_per_ip']) || !$config['rate_limit_per_ip']) && (!isset($config['rate_limit_per_user']) || !$config['rate_limit_per_user']))
                                            <span class="badge badge-secondary">全局限制</span>
                                            @endif
                                        </p>
                                    </div>
                                    @elseif($riskRule->risk_type == 'parameter_check')
                                    <div class="form-group">
                                        <label>参数名称</label>
                                        <p class="form-control-static">{{ $config['param_name'] ?? '未设置' }}</p>
                                    </div>
                                    <div class="form-group">
                                        <label>检查类型</label>
                                        <p class="form-control-static">
                                            @if(isset($config['check_type']))
                                                @if($config['check_type'] == 'required')
                                                必填
                                                @elseif($config['check_type'] == 'regex')
                                                正则表达式
                                                @elseif($config['check_type'] == 'length')
                                                长度限制
                                                @elseif($config['check_type'] == 'enum')
                                                枚举值
                                                @elseif($config['check_type'] == 'type')
                                                类型检查
                                                @else
                                                {{ $config['check_type'] }}
                                                @endif
                                            @else
                                            未设置
                                            @endif
                                        </p>
                                    </div>
                                    <div class="form-group">
                                        <label>检查值</label>
                                        <p class="form-control-static">{{ $config['check_value'] ?? '未设置' }}</p>
                                    </div>
                                    @elseif($riskRule->risk_type == 'custom')
                                    <div class="form-group">
                                        <label>规则条件</label>
                                        <pre><code class="json">{{ json_encode(json_decode($config['rule_condition'] ?? '{}'), JSON_PRETTY_PRINT) }}</code></pre>
                                    </div>
                                    @else
                                    <p class="text-muted">该规则类型无需额外配置</p>
                                    @endif
                                    
                                    @if($riskRule->action == 'add_to_blacklist' && isset($config['blacklist_duration']))
                                    <div class="form-group">
                                        <label>黑名单时长</label>
                                        <p class="form-control-static">
                                            @if($config['blacklist_duration'] == 0)
                                            永久
                                            @else
                                            {{ $config['blacklist_duration'] }} 小时
                                            @endif
                                        </p>
                                    </div>
                                    @endif
                                    
                                    @if($riskRule->action == 'custom_action' && isset($config['custom_action_code']))
                                    <div class="form-group">
                                        <label>自定义动作代码</label>
                                        <pre><code class="php">{{ $config['custom_action_code'] }}</code></pre>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">规则统计</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info"><i class="fas fa-bolt"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">触发次数</span>
                                                    <span class="info-box-number">{{ number_format($riskRule->trigger_count) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success"><i class="fas fa-calendar-alt"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">今日触发</span>
                                                    <span class="info-box-number">{{ number_format($todayTriggerCount) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-warning"><i class="fas fa-ban"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">阻止请求数</span>
                                                    <span class="info-box-number">{{ number_format($blockedCount) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-danger"><i class="fas fa-user-slash"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">加入黑名单数</span>
                                                    <span class="info-box-number">{{ number_format($blacklistedCount) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="chart-container mt-4">
                                        <canvas id="triggerChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">最近触发的风险事件</h4>
                                </div>
                                <div class="card-body">
                                    @if(count($recentEvents) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>时间</th>
                                                    <th>接口</th>
                                                    <th>IP地址</th>
                                                    <th>用户ID</th>
                                                    <th>风险等级</th>
                                                    <th>处理结果</th>
                                                    <th>操作</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentEvents as $event)
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
                                                    <td>{{ $event->ip_address }}</td>
                                                    <td>{{ $event->user_id ?: '-' }}</td>
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
                                    @else
                                    <p class="text-muted">暂无风险事件记录</p>
                                    @endif
                                    
                                    <div class="mt-3">
                                        <a href="{{ route('admin.security.api.risk-events.index', ['rule_id' => $riskRule->id]) }}" class="btn btn-primary">
                                            <i class="fas fa-list"></i> 查看全部风险事件
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.security.api.risk-rules.edit', $riskRule->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> 编辑规则
                    </a>
                    @if($riskRule->status == 'active')
                    <form action="{{ route('admin.security.api.risk-rules.toggle-status', $riskRule->id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="inactive">
                        <button type="submit" class="btn btn-warning" onclick="return confirm('确定要禁用该规则吗？')">
                            <i class="fas fa-ban"></i> 禁用规则
                        </button>
                    </form>
                    @else
                    <form action="{{ route('admin.security.api.risk-rules.toggle-status', $riskRule->id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="active">
                        <button type="submit" class="btn btn-success" onclick="return confirm('确定要启用该规则吗？')">
                            <i class="fas fa-check"></i> 启用规则
                        </button>
                    </form>
                    @endif
                    <form action="{{ route('admin.security.api.risk-rules.destroy', $riskRule->id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('确定要删除该规则吗？')">
                            <i class="fas fa-trash"></i> 删除规则
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
        // 高亮代码
        document.querySelectorAll('pre code').forEach((block) => {
            hljs.highlightBlock(block);
        });
        
        // 触发统计图表
        var ctx = document.getElementById('triggerChart').getContext('2d');
        var triggerChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: [{
                    label: '触发次数',
                    data: {!! json_encode($chartData['data']) !!},
                    borderColor: '#3490dc',
                    backgroundColor: 'rgba(52, 144, 220, 0.1)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
@endsection 