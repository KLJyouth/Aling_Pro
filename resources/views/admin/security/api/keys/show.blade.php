@extends('admin.layouts.app')

@section('title', 'API密钥详情')

@section('content')
<div class="container-fluid">
    @if(session('full_key'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h5><i class="icon fas fa-check"></i> API密钥已创建/重置!</h5>
        <p>请保存以下API密钥，它只会显示一次：</p>
        <div class="input-group mb-3">
            <input type="text" id="api-key" class="form-control" value="{{ session('full_key') }}" readonly>
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" id="copy-key">
                    <i class="fas fa-copy"></i> 复制
                </button>
            </div>
        </div>
        <p class="mb-0">
            <strong>警告：</strong> 此密钥不会再次显示，请立即保存。如果丢失，您需要重置密钥。
        </p>
    </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">API密钥详情</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.security.api.keys.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left"></i> 返回
                        </a>
                        <a href="{{ route('admin.security.api.keys.edit', $apiKey->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> 编辑
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 150px;">密钥ID</th>
                                    <td>{{ $apiKey->id }}</td>
                                </tr>
                                <tr>
                                    <th>密钥名称</th>
                                    <td>{{ $apiKey->name }}</td>
                                </tr>
                                <tr>
                                    <th>密钥前缀</th>
                                    <td><code>{{ $apiKey->key_prefix }}</code></td>
                                </tr>
                                <tr>
                                    <th>所属用户</th>
                                    <td>
                                        <a href="{{ route('admin.management.users.show', $apiKey->user_id) }}">
                                            {{ $apiKey->user->name }} ({{ $apiKey->user->email }})
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>状态</th>
                                    <td>
                                        @if($apiKey->status == 'active')
                                            <span class="badge badge-success">启用</span>
                                        @else
                                            <span class="badge badge-danger">禁用</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 150px;">过期时间</th>
                                    <td>
                                        @if($apiKey->expiration_date)
                                            @if($apiKey->isExpired())
                                                <span class="badge badge-danger">已过期 {{ $apiKey->expiration_date }}</span>
                                            @else
                                                <span class="badge badge-success">{{ $apiKey->expiration_date }}</span>
                                            @endif
                                        @else
                                            <span class="badge badge-info">永不过期</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>速率限制</th>
                                    <td>
                                        @if($apiKey->rate_limit)
                                            {{ $apiKey->rate_limit }} 请求/分钟
                                        @else
                                            无限制
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>IP限制</th>
                                    <td>
                                        @if($apiKey->ip_restrictions)
                                            <code>{{ $apiKey->ip_restrictions }}</code>
                                        @else
                                            无限制
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>创建时间</th>
                                    <td>{{ $apiKey->created_at }}</td>
                                </tr>
                                <tr>
                                    <th>最后使用</th>
                                    <td>
                                        @if($apiKey->last_used_at)
                                            {{ $apiKey->last_used_at }}
                                        @else
                                            <span class="text-muted">从未使用</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">权限设置</h4>
                                </div>
                                <div class="card-body">
                                    @if($apiKey->permissions)
                                        @foreach(json_decode($apiKey->permissions) as $permission)
                                            @if($permission == '*')
                                                <span class="badge badge-danger">所有权限</span>
                                            @elseif($permission == 'read')
                                                <span class="badge badge-info">读取权限</span>
                                            @elseif($permission == 'write')
                                                <span class="badge badge-success">写入权限</span>
                                            @elseif($permission == 'delete')
                                                <span class="badge badge-warning">删除权限</span>
                                            @elseif($permission == 'admin')
                                                <span class="badge badge-dark">管理权限</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $permission }}</span>
                                            @endif
                                        @endforeach
                                    @else
                                        <span class="text-muted">未设置权限</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">使用统计</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info"><i class="fas fa-exchange-alt"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">总请求数</span>
                                                    <span class="info-box-number">{{ number_format($usageStats->total_requests ?? 0) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">成功请求</span>
                                                    <span class="info-box-number">{{ number_format($usageStats->success_requests ?? 0) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-warning"><i class="fas fa-chart-line"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">平均响应时间</span>
                                                    <span class="info-box-number">{{ number_format($usageStats->avg_response_time ?? 0, 2) }} ms</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">错误率</span>
                                                    <span class="info-box-number">
                                                        @if(isset($usageStats->total_requests) && $usageStats->total_requests > 0)
                                                            {{ number_format(100 - (($usageStats->success_requests / $usageStats->total_requests) * 100), 2) }}%
                                                        @else
                                                            0%
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <a href="{{ route('admin.security.api.request-logs.index', ['api_key_id' => $apiKey->id]) }}" class="btn btn-info">
                                            <i class="fas fa-chart-bar"></i> 查看详细使用统计
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.security.api.keys.edit', $apiKey->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> 编辑
                    </a>
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#resetKeyModal">
                        <i class="fas fa-sync-alt"></i> 重置密钥
                    </button>
                    <form action="{{ route('admin.security.api.keys.destroy', $apiKey->id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('确定要删除该API密钥吗？')">
                            <i class="fas fa-trash"></i> 删除
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">密钥操作日志</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>操作</th>
                                    <th>描述</th>
                                    <th>时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                <tr>
                                    <td>
                                        @if($log->action == 'created')
                                            <span class="badge badge-success">创建</span>
                                        @elseif($log->action == 'updated')
                                            <span class="badge badge-info">更新</span>
                                        @elseif($log->action == 'reset')
                                            <span class="badge badge-warning">重置</span>
                                        @elseif($log->action == 'deleted')
                                            <span class="badge badge-danger">删除</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $log->action }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->description }}</td>
                                    <td>{{ $log->created_at }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    {{ $logs->links() }}
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">API使用说明</h3>
                </div>
                <div class="card-body">
                    <h5>认证方式</h5>
                    <p>在API请求中，可以通过以下方式进行认证：</p>
                    <ol>
                        <li>
                            <strong>HTTP头认证（推荐）</strong>
                            <pre><code>X-API-Key: YOUR_API_KEY</code></pre>
                        </li>
                        <li>
                            <strong>查询参数认证</strong>
                            <pre><code>https://api.example.com/endpoint?api_key=YOUR_API_KEY</code></pre>
                        </li>
                    </ol>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 为了安全起见，建议使用HTTP头方式进行认证。
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 重置密钥模态框 -->
<div class="modal fade" id="resetKeyModal" tabindex="-1" role="dialog" aria-labelledby="resetKeyModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetKeyModalLabel">重置API密钥</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>您确定要重置密钥 <strong>{{ $apiKey->name }}</strong> 吗？</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 警告：重置后，旧密钥将立即失效，使用该密钥的应用程序将无法访问API，直到更新为新密钥。
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <form action="{{ route('admin.security.api.keys.reset', $apiKey->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-warning">确认重置</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // 复制API密钥
        $('#copy-key').click(function() {
            var apiKey = $('#api-key');
            apiKey.select();
            document.execCommand('copy');
            
            $(this).html('<i class="fas fa-check"></i> 已复制');
            setTimeout(function() {
                $('#copy-key').html('<i class="fas fa-copy"></i> 复制');
            }, 2000);
        });
    });
</script>
@endsection 