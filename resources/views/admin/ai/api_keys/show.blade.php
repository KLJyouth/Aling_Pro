@extends("admin.layouts.app")

@section("title", "API密钥详情")

@section("content_header")
    <h1>API密钥详情</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">{{ $apiKey->name }} 详情</h3>
                <div>
                    <a href="{{ route("admin.ai.api-keys.edit", $apiKey->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> 编辑
                    </a>
                    <a href="{{ route("admin.ai.api-keys.index") }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> 返回列表
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table">
                        <tr>
                            <th style="width: 30%">ID</th>
                            <td>{{ $apiKey->id }}</td>
                        </tr>
                        <tr>
                            <th>名称</th>
                            <td>{{ $apiKey->name }}</td>
                        </tr>
                        <tr>
                            <th>提供商</th>
                            <td>{{ $apiKey->provider->name }}</td>
                        </tr>
                        <tr>
                            <th>密钥掩码</th>
                            <td><code>{{ $apiKey->key_mask }}</code></td>
                        </tr>
                        <tr>
                            <th>使用次数</th>
                            <td>{{ $apiKey->usage_count }}</td>
                        </tr>
                        <tr>
                            <th>配额限制</th>
                            <td>{{ $apiKey->quota_limit > 0 ? $apiKey->quota_limit : "无限制" }}</td>
                        </tr>
                        <tr>
                            <th>状态</th>
                            <td>
                                @if($apiKey->is_active)
                                    <span class="badge badge-success">启用</span>
                                @else
                                    <span class="badge badge-danger">禁用</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>创建时间</th>
                            <td>{{ $apiKey->created_at }}</td>
                        </tr>
                        <tr>
                            <th>更新时间</th>
                            <td>{{ $apiKey->updated_at }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">描述</h4>
                        </div>
                        <div class="card-body">
                            {{ $apiKey->description ?: "暂无描述" }}
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h4 class="card-title">使用统计</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 text-center">
                                    <h5>已使用</h5>
                                    <div class="display-4">{{ $apiKey->usage_count }}</div>
                                </div>
                                <div class="col-6 text-center">
                                    <h5>剩余配额</h5>
                                    <div class="display-4">
                                        @if($apiKey->quota_limit > 0)
                                            {{ max(0, $apiKey->quota_limit - $apiKey->usage_count) }}
                                        @else
                                            
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            @if($apiKey->quota_limit > 0)
                                <div class="progress mt-3">
                                    @php
                                        $percentage = min(100, ($apiKey->usage_count / $apiKey->quota_limit) * 100);
                                        $progressClass = $percentage < 70 ? "bg-success" : ($percentage < 90 ? "bg-warning" : "bg-danger");
                                    @endphp
                                    <div class="progress-bar {{ $progressClass }}" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">{{ round($percentage) }}%</div>
                                </div>
                            @endif
                            
                            <div class="mt-3 text-center">
                                <button type="button" class="btn btn-primary" onclick="resetQuota()">
                                    <i class="fas fa-redo"></i> 重置配额
                                </button>
                            </div>
                            <form id="reset-quota-form" action="{{ route("admin.ai.api-keys.reset-quota", $apiKey->id) }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">使用记录</h4>
                        </div>
                        <div class="card-body">
                            @if($usageRecords->count() > 0)
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>智能体</th>
                                            <th>用户</th>
                                            <th>请求时间</th>
                                            <th>响应时间</th>
                                            <th>输入标记数</th>
                                            <th>输出标记数</th>
                                            <th>状态</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($usageRecords as $record)
                                            <tr>
                                                <td>{{ $record->id }}</td>
                                                <td>{{ $record->agent ? $record->agent->name : "未知" }}</td>
                                                <td>{{ $record->user ? $record->user->name : "系统" }}</td>
                                                <td>{{ $record->created_at }}</td>
                                                <td>{{ $record->response_time }}ms</td>
                                                <td>{{ $record->input_tokens }}</td>
                                                <td>{{ $record->output_tokens }}</td>
                                                <td>
                                                    @if($record->status == "success")
                                                        <span class="badge badge-success">成功</span>
                                                    @elseif($record->status == "error")
                                                        <span class="badge badge-danger">错误</span>
                                                    @else
                                                        <span class="badge badge-warning">处理中</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                
                                {{ $usageRecords->links() }}
                            @else
                                <div class="alert alert-info">
                                    暂无使用记录
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section("js")
<script>
    function resetQuota() {
        if (confirm("确定要重置这个API密钥的使用配额吗？")) {
            document.getElementById("reset-quota-form").submit();
        }
    }
</script>
@stop
