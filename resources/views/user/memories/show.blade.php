@extends("layouts.app")

@section("title", "记忆详情 - " . $memory->key)

@section("content")
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            @include("user.partials.sidebar")
        </div>
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route("user.memories.index") }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <span class="fw-bold">记忆详情</span>
                    </div>
                    <div>
                        <a href="{{ route("user.memories.edit", $memory->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> 编辑
                        </a>
                        <button type="button" class="btn btn-danger btn-sm" 
                            onclick="if(confirm("确定要删除这个记忆吗？此操作不可恢复。")) document.getElementById("delete-form").submit();">
                            <i class="fas fa-trash"></i> 删除
                        </button>
                        <form id="delete-form" action="{{ route("user.memories.destroy", $memory->id) }}" method="POST" style="display: none;">
                            @csrf
                            @method("DELETE")
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    @if(session("success"))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session("success") }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
                        </div>
                    @endif
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">记忆键</label>
                                <p class="form-control-plaintext">{{ $memory->key }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">分类</label>
                                <p class="form-control-plaintext">
                                    @if($memory->category)
                                        <span class="badge bg-info">{{ $memory->category }}</span>
                                    @else
                                        <span class="text-muted">未分类</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">类型</label>
                                <p class="form-control-plaintext">
                                    @if($memory->type == "text")
                                        <span class="badge bg-secondary">文本</span>
                                    @elseif($memory->type == "json")
                                        <span class="badge bg-primary">JSON</span>
                                    @elseif($memory->type == "embedding")
                                        <span class="badge bg-warning">嵌入向量</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">重要性</label>
                                <p class="form-control-plaintext">
                                    @if($memory->importance >= 8)
                                        <span class="badge bg-danger">{{ $memory->importance }}</span>
                                    @elseif($memory->importance >= 5)
                                        <span class="badge bg-warning">{{ $memory->importance }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $memory->importance }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">访问次数</label>
                                <p class="form-control-plaintext">{{ $memory->access_count }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">内容</label>
                        @if($memory->type == "text")
                            <div class="border rounded p-3 bg-light">
                                <pre class="mb-0" style="white-space: pre-wrap;">{{ $memory->content }}</pre>
                            </div>
                        @elseif($memory->type == "json")
                            <div class="border rounded p-3 bg-light">
                                <pre class="mb-0" style="white-space: pre-wrap;">{{ json_encode($memory->getContent(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        @elseif($memory->type == "embedding")
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 嵌入向量内容无法直接显示
                            </div>
                        @endif
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">创建时间</label>
                                <p class="form-control-plaintext">{{ $memory->created_at }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">最后更新</label>
                                <p class="form-control-plaintext">{{ $memory->updated_at }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">最后访问</label>
                                <p class="form-control-plaintext">
                                    @if($memory->last_accessed_at)
                                        {{ $memory->last_accessed_at }}
                                    @else
                                        <span class="text-muted">从未</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">元数据</label>
                                <div class="border rounded p-3 bg-light">
                                    <pre class="mb-0" style="white-space: pre-wrap;">{{ json_encode($memory->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
