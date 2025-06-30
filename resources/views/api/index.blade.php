@extends("layouts.app")

@section("title", "API密钥管理")

@section("content")
<div class="container py-4">
    <h1 class="h3 mb-4">API密钥管理</h1>
    
    @if(session("success"))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session("success") }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session("error"))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session("error") }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session("new_api_key"))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <p><strong>您的API密钥已创建成功！</strong></p>
            <p class="mb-2">请立即复制并安全保存您的API密钥，此密钥只会显示一次：</p>
            <div class="input-group mb-2">
                <input type="text" class="form-control" value="{{ session("new_api_key") }}" id="newApiKey" readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="copyApiKey()">
                    <i class="fas fa-copy"></i> 复制
                </button>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <div class="row">
        <div class="col-lg-8">
            <!-- API密钥列表 -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">API密钥</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createApiKeyModal">
                        <i class="fas fa-plus me-1"></i> 创建API密钥
                    </button>
                </div>
                <div class="card-body">
                    @if(count($apiKeys) > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>名称</th>
                                        <th>前缀</th>
                                        <th>创建日期</th>
                                        <th>过期日期</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($apiKeys as $apiKey)
                                        <tr>
                                            <td>{{ $apiKey->name }}</td>
                                            <td><code>{{ Str::limit($apiKey->api_key, 10) }}</code></td>
                                            <td>{{ $apiKey->created_at->format("Y-m-d") }}</td>
                                            <td>{{ $apiKey->expires_at ? $apiKey->expires_at->format("Y-m-d") : "永不过期" }}</td>
                                            <td>
                                                <span class="badge {{ $apiKey->status === "active" ? "bg-success" : "bg-danger" }}">
                                                    {{ $apiKey->status === "active" ? "有效" : "已禁用" }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editApiKeyModal{{ $apiKey->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteApiKeyModal{{ $apiKey->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                
                                                <!-- 编辑API密钥模态框 -->
                                                <div class="modal fade" id="editApiKeyModal{{ $apiKey->id }}" tabindex="-1" aria-labelledby="editApiKeyModalLabel{{ $apiKey->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="editApiKeyModalLabel{{ $apiKey->id }}">编辑API密钥</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form action="{{ route("api-keys.update", $apiKey->id) }}" method="POST">
                                                                    @csrf
                                                                    @method("PUT")
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="name{{ $apiKey->id }}" class="form-label">名称</label>
                                                                        <input type="text" class="form-control" id="name{{ $apiKey->id }}" name="name" value="{{ $apiKey->name }}" required>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="status{{ $apiKey->id }}" class="form-label">状态</label>
                                                                        <select class="form-select" id="status{{ $apiKey->id }}" name="status">
                                                                            <option value="active" {{ $apiKey->status === "active" ? "selected" : "" }}>有效</option>
                                                                            <option value="inactive" {{ $apiKey->status === "inactive" ? "selected" : "" }}>禁用</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="expires_at{{ $apiKey->id }}" class="form-label">过期日期</label>
                                                                        <input type="date" class="form-control" id="expires_at{{ $apiKey->id }}" name="expires_at" value="{{ $apiKey->expires_at ? $apiKey->expires_at->format("Y-m-d") : "" }}">
                                                                        <div class="form-text">留空表示永不过期</div>
                                                                    </div>
                                                                    
                                                                    <div class="d-grid">
                                                                        <button type="submit" class="btn btn-primary">保存更改</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- 删除API密钥模态框 -->
                                                <div class="modal fade" id="deleteApiKeyModal{{ $apiKey->id }}" tabindex="-1" aria-labelledby="deleteApiKeyModalLabel{{ $apiKey->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteApiKeyModalLabel{{ $apiKey->id }}">删除API密钥</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="alert alert-warning">
                                                                    <i class="fas fa-exclamation-triangle me-2"></i> 警告：删除API密钥后，使用此密钥的所有API调用将立即失败。此操作无法撤销。
                                                                </div>
                                                                
                                                                <p>您确定要删除以下API密钥吗？</p>
                                                                <p><strong>名称：</strong> {{ $apiKey->name }}</p>
                                                                <p><strong>前缀：</strong> <code>{{ Str::limit($apiKey->api_key, 10) }}</code></p>
                                                                
                                                                <form action="{{ route("api-keys.destroy", $apiKey->id) }}" method="POST">
                                                                    @csrf
                                                                    @method("DELETE")
                                                                    
                                                                    <div class="form-check mb-3">
                                                                        <input class="form-check-input" type="checkbox" id="confirmDelete{{ $apiKey->id }}" required>
                                                                        <label class="form-check-label" for="confirmDelete{{ $apiKey->id }}">
                                                                            我确认要删除此API密钥
                                                                        </label>
                                                                    </div>
                                                                    
                                                                    <div class="d-grid">
                                                                        <button type="submit" class="btn btn-danger" id="deleteButton{{ $apiKey->id }}" disabled>
                                                                            确认删除
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <script>
                                                    document.addEventListener("DOMContentLoaded", function() {
                                                        const confirmCheckbox{{ $apiKey->id }} = document.getElementById("confirmDelete{{ $apiKey->id }}");
                                                        const deleteButton{{ $apiKey->id }} = document.getElementById("deleteButton{{ $apiKey->id }}");
                                                        
                                                        if (confirmCheckbox{{ $apiKey->id }} && deleteButton{{ $apiKey->id }}) {
                                                            confirmCheckbox{{ $apiKey->id }}.addEventListener("change", function() {
                                                                deleteButton{{ $apiKey->id }}.disabled = !this.checked;
                                                            });
                                                        }
                                                    });
                                                </script>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-key fa-3x text-muted mb-3"></i>
                            <p>您还没有创建API密钥</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createApiKeyModal">
                                <i class="fas fa-plus me-1"></i> 创建第一个API密钥
                            </button>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- API使用说明 -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">API使用说明</h5>
                </div>
                <div class="card-body">
                    <h6>身份验证</h6>
                    <p>在所有API请求中，您需要在HTTP头中包含您的API密钥：</p>
                    <pre><code>Authorization: Bearer YOUR_API_KEY</code></pre>
                    
                    <h6>示例请求</h6>
                    <div class="mb-3">
                        <pre><code>curl -X POST https://api.alingai.com/v1/ai/generate \
-H "Authorization: Bearer YOUR_API_KEY" \
-H "Content-Type: application/json" \
-d '{
  "prompt": "讲一个关于人工智能的故事",
  "max_tokens": 100,
  "temperature": 0.7
}'</code></pre>
                    </div>
                    
                    <h6>速率限制</h6>
                    <p>API调用受到您的会员等级限制。超过限制的请求将返回429错误。</p>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                        <a href="{{ route("api-docs") }}" class="btn btn-primary">
                            <i class="fas fa-book me-1"></i> 查看完整API文档
                        </a>
                        <a href="{{ route("api-playground") }}" class="btn btn-outline-primary">
                            <i class="fas fa-flask me-1"></i> API测试工具
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- API使用统计 -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">API使用统计</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h3>{{ $apiUsageStats["today"] }}</h3>
                            <small class="text-muted">今日调用</small>
                        </div>
                        <div class="col-4">
                            <h3>{{ $apiUsageStats["month"] }}</h3>
                            <small class="text-muted">本月调用</small>
                        </div>
                        <div class="col-4">
                            <h3>{{ $apiUsageStats["total"] }}</h3>
                            <small class="text-muted">总调用</small>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">状态码分布</h6>
                    <div class="progress mb-3" style="height: 20px;">
                        @php
                            $totalCalls = $apiUsageStats["status"]["success"] + $apiUsageStats["status"]["error"];
                            $successPercent = $totalCalls > 0 ? round(($apiUsageStats["status"]["success"] / $totalCalls) * 100) : 0;
                            $errorPercent = $totalCalls > 0 ? 100 - $successPercent : 0;
                        @endphp
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $successPercent }}%" aria-valuenow="{{ $successPercent }}" aria-valuemin="0" aria-valuemax="100">{{ $successPercent }}%</div>
                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $errorPercent }}%" aria-valuenow="{{ $errorPercent }}" aria-valuemin="0" aria-valuemax="100">{{ $errorPercent }}%</div>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <div>
                            <i class="fas fa-circle text-success me-1"></i> 成功 ({{ $apiUsageStats["status"]["success"] }})
                        </div>
                        <div>
                            <i class="fas fa-circle text-danger me-1"></i> 错误 ({{ $apiUsageStats["status"]["error"] }})
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">热门端点</h6>
                    <ul class="list-group list-group-flush">
                        @forelse($apiUsageStats["endpoints"] as $endpoint => $count)
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span>{{ Str::limit($endpoint, 30) }}</span>
                                <span class="badge bg-primary rounded-pill">{{ $count }}</span>
                            </li>
                        @empty
                            <li class="list-group-item px-0 text-center">
                                <span class="text-muted">暂无数据</span>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
            
            <!-- API安全提示 -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">API安全提示</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0">
                            <i class="fas fa-shield-alt text-primary me-2"></i> 保护您的API密钥安全，不要在公共代码库中暴露它
                        </li>
                        <li class="list-group-item px-0">
                            <i class="fas fa-sync-alt text-primary me-2"></i> 定期轮换您的API密钥以提高安全性
                        </li>
                        <li class="list-group-item px-0">
                            <i class="fas fa-clock text-primary me-2"></i> 为API密钥设置过期时间，减少安全风险
                        </li>
                        <li class="list-group-item px-0">
                            <i class="fas fa-ban text-primary me-2"></i> 如果发现API密钥泄露，立即禁用或删除它
                        </li>
                        <li class="list-group-item px-0">
                            <i class="fas fa-user-shield text-primary me-2"></i> 使用环境变量存储API密钥，而不是硬编码
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 创建API密钥模态框 -->
<div class="modal fade" id="createApiKeyModal" tabindex="-1" aria-labelledby="createApiKeyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createApiKeyModalLabel">创建API密钥</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route("api-keys.store") }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">名称</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="例如：Web应用、移动应用" required>
                        <div class="form-text">为您的API密钥指定一个易于识别的名称</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expires_at" class="form-label">过期日期</label>
                        <input type="date" class="form-control" id="expires_at" name="expires_at">
                        <div class="form-text">留空表示永不过期</div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> 创建后，您的API密钥将只显示一次。请确保安全保存它。
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">创建API密钥</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@section("scripts")
<script>
    function copyApiKey() {
        var copyText = document.getElementById("newApiKey");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        
        // 显示提示
        alert("API密钥已复制到剪贴板！");
    }
</script>
@endsection
@endsection
