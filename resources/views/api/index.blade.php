@extends("layouts.app")

@section("title", "API��Կ����")

@section("content")
<div class="container py-4">
    <h1 class="h3 mb-4">API��Կ����</h1>
    
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
            <p><strong>����API��Կ�Ѵ����ɹ���</strong></p>
            <p class="mb-2">���������Ʋ���ȫ��������API��Կ������Կֻ����ʾһ�Σ�</p>
            <div class="input-group mb-2">
                <input type="text" class="form-control" value="{{ session("new_api_key") }}" id="newApiKey" readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="copyApiKey()">
                    <i class="fas fa-copy"></i> ����
                </button>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <div class="row">
        <div class="col-lg-8">
            <!-- API��Կ�б� -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">API��Կ</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createApiKeyModal">
                        <i class="fas fa-plus me-1"></i> ����API��Կ
                    </button>
                </div>
                <div class="card-body">
                    @if(count($apiKeys) > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>����</th>
                                        <th>ǰ׺</th>
                                        <th>��������</th>
                                        <th>��������</th>
                                        <th>״̬</th>
                                        <th>����</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($apiKeys as $apiKey)
                                        <tr>
                                            <td>{{ $apiKey->name }}</td>
                                            <td><code>{{ Str::limit($apiKey->api_key, 10) }}</code></td>
                                            <td>{{ $apiKey->created_at->format("Y-m-d") }}</td>
                                            <td>{{ $apiKey->expires_at ? $apiKey->expires_at->format("Y-m-d") : "��������" }}</td>
                                            <td>
                                                <span class="badge {{ $apiKey->status === "active" ? "bg-success" : "bg-danger" }}">
                                                    {{ $apiKey->status === "active" ? "��Ч" : "�ѽ���" }}
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
                                                
                                                <!-- �༭API��Կģ̬�� -->
                                                <div class="modal fade" id="editApiKeyModal{{ $apiKey->id }}" tabindex="-1" aria-labelledby="editApiKeyModalLabel{{ $apiKey->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="editApiKeyModalLabel{{ $apiKey->id }}">�༭API��Կ</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form action="{{ route("api-keys.update", $apiKey->id) }}" method="POST">
                                                                    @csrf
                                                                    @method("PUT")
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="name{{ $apiKey->id }}" class="form-label">����</label>
                                                                        <input type="text" class="form-control" id="name{{ $apiKey->id }}" name="name" value="{{ $apiKey->name }}" required>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="status{{ $apiKey->id }}" class="form-label">״̬</label>
                                                                        <select class="form-select" id="status{{ $apiKey->id }}" name="status">
                                                                            <option value="active" {{ $apiKey->status === "active" ? "selected" : "" }}>��Ч</option>
                                                                            <option value="inactive" {{ $apiKey->status === "inactive" ? "selected" : "" }}>����</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="expires_at{{ $apiKey->id }}" class="form-label">��������</label>
                                                                        <input type="date" class="form-control" id="expires_at{{ $apiKey->id }}" name="expires_at" value="{{ $apiKey->expires_at ? $apiKey->expires_at->format("Y-m-d") : "" }}">
                                                                        <div class="form-text">���ձ�ʾ��������</div>
                                                                    </div>
                                                                    
                                                                    <div class="d-grid">
                                                                        <button type="submit" class="btn btn-primary">�������</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- ɾ��API��Կģ̬�� -->
                                                <div class="modal fade" id="deleteApiKeyModal{{ $apiKey->id }}" tabindex="-1" aria-labelledby="deleteApiKeyModalLabel{{ $apiKey->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteApiKeyModalLabel{{ $apiKey->id }}">ɾ��API��Կ</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="alert alert-warning">
                                                                    <i class="fas fa-exclamation-triangle me-2"></i> ���棺ɾ��API��Կ��ʹ�ô���Կ������API���ý�����ʧ�ܡ��˲����޷�������
                                                                </div>
                                                                
                                                                <p>��ȷ��Ҫɾ������API��Կ��</p>
                                                                <p><strong>���ƣ�</strong> {{ $apiKey->name }}</p>
                                                                <p><strong>ǰ׺��</strong> <code>{{ Str::limit($apiKey->api_key, 10) }}</code></p>
                                                                
                                                                <form action="{{ route("api-keys.destroy", $apiKey->id) }}" method="POST">
                                                                    @csrf
                                                                    @method("DELETE")
                                                                    
                                                                    <div class="form-check mb-3">
                                                                        <input class="form-check-input" type="checkbox" id="confirmDelete{{ $apiKey->id }}" required>
                                                                        <label class="form-check-label" for="confirmDelete{{ $apiKey->id }}">
                                                                            ��ȷ��Ҫɾ����API��Կ
                                                                        </label>
                                                                    </div>
                                                                    
                                                                    <div class="d-grid">
                                                                        <button type="submit" class="btn btn-danger" id="deleteButton{{ $apiKey->id }}" disabled>
                                                                            ȷ��ɾ��
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
                            <p>����û�д���API��Կ</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createApiKeyModal">
                                <i class="fas fa-plus me-1"></i> ������һ��API��Կ
                            </button>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- APIʹ��˵�� -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">APIʹ��˵��</h5>
                </div>
                <div class="card-body">
                    <h6>�����֤</h6>
                    <p>������API�����У�����Ҫ��HTTPͷ�а�������API��Կ��</p>
                    <pre><code>Authorization: Bearer YOUR_API_KEY</code></pre>
                    
                    <h6>ʾ������</h6>
                    <div class="mb-3">
                        <pre><code>curl -X POST https://api.alingai.com/v1/ai/generate \
-H "Authorization: Bearer YOUR_API_KEY" \
-H "Content-Type: application/json" \
-d '{
  "prompt": "��һ�������˹����ܵĹ���",
  "max_tokens": 100,
  "temperature": 0.7
}'</code></pre>
                    </div>
                    
                    <h6>��������</h6>
                    <p>API�����ܵ����Ļ�Ա�ȼ����ơ��������Ƶ����󽫷���429����</p>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                        <a href="{{ route("api-docs") }}" class="btn btn-primary">
                            <i class="fas fa-book me-1"></i> �鿴����API�ĵ�
                        </a>
                        <a href="{{ route("api-playground") }}" class="btn btn-outline-primary">
                            <i class="fas fa-flask me-1"></i> API���Թ���
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- APIʹ��ͳ�� -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">APIʹ��ͳ��</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h3>{{ $apiUsageStats["today"] }}</h3>
                            <small class="text-muted">���յ���</small>
                        </div>
                        <div class="col-4">
                            <h3>{{ $apiUsageStats["month"] }}</h3>
                            <small class="text-muted">���µ���</small>
                        </div>
                        <div class="col-4">
                            <h3>{{ $apiUsageStats["total"] }}</h3>
                            <small class="text-muted">�ܵ���</small>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">״̬��ֲ�</h6>
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
                            <i class="fas fa-circle text-success me-1"></i> �ɹ� ({{ $apiUsageStats["status"]["success"] }})
                        </div>
                        <div>
                            <i class="fas fa-circle text-danger me-1"></i> ���� ({{ $apiUsageStats["status"]["error"] }})
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">���Ŷ˵�</h6>
                    <ul class="list-group list-group-flush">
                        @forelse($apiUsageStats["endpoints"] as $endpoint => $count)
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span>{{ Str::limit($endpoint, 30) }}</span>
                                <span class="badge bg-primary rounded-pill">{{ $count }}</span>
                            </li>
                        @empty
                            <li class="list-group-item px-0 text-center">
                                <span class="text-muted">��������</span>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
            
            <!-- API��ȫ��ʾ -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">API��ȫ��ʾ</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0">
                            <i class="fas fa-shield-alt text-primary me-2"></i> ��������API��Կ��ȫ����Ҫ�ڹ���������б�¶��
                        </li>
                        <li class="list-group-item px-0">
                            <i class="fas fa-sync-alt text-primary me-2"></i> �����ֻ�����API��Կ����߰�ȫ��
                        </li>
                        <li class="list-group-item px-0">
                            <i class="fas fa-clock text-primary me-2"></i> ΪAPI��Կ���ù���ʱ�䣬���ٰ�ȫ����
                        </li>
                        <li class="list-group-item px-0">
                            <i class="fas fa-ban text-primary me-2"></i> �������API��Կй¶���������û�ɾ����
                        </li>
                        <li class="list-group-item px-0">
                            <i class="fas fa-user-shield text-primary me-2"></i> ʹ�û��������洢API��Կ��������Ӳ����
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ����API��Կģ̬�� -->
<div class="modal fade" id="createApiKeyModal" tabindex="-1" aria-labelledby="createApiKeyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createApiKeyModalLabel">����API��Կ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route("api-keys.store") }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">����</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="���磺WebӦ�á��ƶ�Ӧ��" required>
                        <div class="form-text">Ϊ����API��Կָ��һ������ʶ�������</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expires_at" class="form-label">��������</label>
                        <input type="date" class="form-control" id="expires_at" name="expires_at">
                        <div class="form-text">���ձ�ʾ��������</div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> ����������API��Կ��ֻ��ʾһ�Ρ���ȷ����ȫ��������
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">����API��Կ</button>
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
        
        // ��ʾ��ʾ
        alert("API��Կ�Ѹ��Ƶ������壡");
    }
</script>
@endsection
@endsection
