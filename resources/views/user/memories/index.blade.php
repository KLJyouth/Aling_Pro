@extends("layouts.app")

@section("title", "长期记忆")

@section("content")
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            @include("user.partials.sidebar")
        </div>
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">长期记忆</h5>
                    <div>
                        <a href="{{ route("user.memories.create") }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> 创建记忆
                        </a>
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-download"></i> 导出
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                <li><a class="dropdown-item" href="{{ route("user.memories.export", ["format" => "json"]) }}">JSON 格式</a></li>
                                <li><a class="dropdown-item" href="{{ route("user.memories.export", ["format" => "csv"]) }}">CSV 格式</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session("success"))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session("success") }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
                        </div>
                    @endif
                    
                    <!-- 过滤器 -->
                    <div class="mb-4">
                        <form method="GET" action="{{ route("user.memories.index") }}" class="row g-3">
                            <div class="col-md-3">
                                <select name="category" class="form-select form-select-sm">
                                    <option value="">所有分类</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" {{ request("category") == $category ? "selected" : "" }}>{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="type" class="form-select form-select-sm">
                                    <option value="">所有类型</option>
                                    <option value="text" {{ request("type") == "text" ? "selected" : "" }}>文本</option>
                                    <option value="json" {{ request("type") == "json" ? "selected" : "" }}>JSON</option>
                                    <option value="embedding" {{ request("type") == "embedding" ? "selected" : "" }}>嵌入向量</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="importance" class="form-select form-select-sm">
                                    <option value="">所有重要性</option>
                                    <option value="8" {{ request("importance") == "8" ? "selected" : "" }}>高 (8-10)</option>
                                    <option value="5" {{ request("importance") == "5" ? "selected" : "" }}>中 (5-7)</option>
                                    <option value="1" {{ request("importance") == "1" ? "selected" : "" }}>低 (1-4)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-sm btn-outline-primary me-2">筛选</button>
                                    <a href="{{ route("user.memories.index") }}" class="btn btn-sm btn-outline-secondary">重置</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    @if($memories->isEmpty())
                        <div class="text-center py-5">
                            <img src="{{ asset("images/empty-state.svg") }}" alt="暂无记忆" class="img-fluid mb-3" style="max-width: 200px;">
                            <h5>暂无长期记忆</h5>
                            <p class="text-muted">创建记忆以帮助AI助手更好地了解您</p>
                            <a href="{{ route("user.memories.create") }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> 创建记忆
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>键</th>
                                        <th>分类</th>
                                        <th>类型</th>
                                        <th>重要性</th>
                                        <th>最后访问</th>
                                        <th>访问次数</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($memories as $memory)
                                        <tr>
                                            <td>
                                                <a href="{{ route("user.memories.show", $memory->id) }}" class="text-decoration-none">
                                                    {{ $memory->key }}
                                                </a>
                                            </td>
                                            <td>
                                                @if($memory->category)
                                                    <span class="badge bg-info">{{ $memory->category }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($memory->type == "text")
                                                    <span class="badge bg-secondary">文本</span>
                                                @elseif($memory->type == "json")
                                                    <span class="badge bg-primary">JSON</span>
                                                @elseif($memory->type == "embedding")
                                                    <span class="badge bg-warning">嵌入向量</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($memory->importance >= 8)
                                                    <span class="badge bg-danger">{{ $memory->importance }}</span>
                                                @elseif($memory->importance >= 5)
                                                    <span class="badge bg-warning">{{ $memory->importance }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $memory->importance }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($memory->last_accessed_at)
                                                    <span title="{{ $memory->last_accessed_at }}">{{ $memory->last_accessed_at->diffForHumans() }}</span>
                                                @else
                                                    <span class="text-muted">从未</span>
                                                @endif
                                            </td>
                                            <td>{{ $memory->access_count }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route("user.memories.show", $memory->id) }}" class="btn btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route("user.memories.edit", $memory->id) }}" class="btn btn-outline-secondary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                        onclick="if(confirm("确定要删除这个记忆吗？此操作不可恢复。")) document.getElementById("delete-form-{{ $memory->id }}").submit();">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <form id="delete-form-{{ $memory->id }}" action="{{ route("user.memories.destroy", $memory->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method("DELETE")
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center mt-4">
                            {{ $memories->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
