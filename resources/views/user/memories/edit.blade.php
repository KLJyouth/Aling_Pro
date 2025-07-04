@extends("layouts.app")

@section("title", isset($memory) ? "编辑记忆" : "创建记忆")

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
                        <a href="{{ isset($memory) ? route("user.memories.show", $memory->id) : route("user.memories.index") }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <span class="fw-bold">{{ isset($memory) ? "编辑记忆" : "创建记忆" }}</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ isset($memory) ? route("user.memories.update", $memory->id) : route("user.memories.store") }}">
                        @csrf
                        @if(isset($memory))
                            @method("PUT")
                        @endif
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="key" class="form-label">记忆键 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="key" name="key" value="{{ old("key", isset($memory) ? $memory->key : "") }}" required>
                                    <div class="form-text">唯一标识符，用于AI检索记忆</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">分类</label>
                                    <input type="text" class="form-control" id="category" name="category" list="categories" value="{{ old("category", isset($memory) ? $memory->category : "") }}">
                                    <datalist id="categories">
                                        @foreach($categories as $category)
                                            <option value="{{ $category }}">
                                        @endforeach
                                    </datalist>
                                    <div class="form-text">可选，用于分组记忆</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">类型 <span class="text-danger">*</span></label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="text" {{ old("type", isset($memory) ? $memory->type : "") == "text" ? "selected" : "" }}>文本</option>
                                        <option value="json" {{ old("type", isset($memory) ? $memory->type : "") == "json" ? "selected" : "" }}>JSON</option>
                                        <option value="embedding" {{ old("type", isset($memory) ? $memory->type : "") == "embedding" ? "selected" : "" }}>嵌入向量</option>
                                    </select>
                                    <div class="form-text">记忆内容的格式类型</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="importance" class="form-label">重要性 <span class="text-danger">*</span></label>
                                    <input type="range" class="form-range" id="importance" name="importance" min="1" max="10" value="{{ old("importance", isset($memory) ? $memory->importance : "5") }}" oninput="importanceValue.innerText = this.value">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">低 (1)</span>
                                        <span class="fw-bold" id="importanceValue">{{ old("importance", isset($memory) ? $memory->importance : "5") }}</span>
                                        <span class="text-muted">高 (10)</span>
                                    </div>
                                    <div class="form-text">影响AI检索记忆的优先级</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">内容 <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="10" required>{{ old("content", isset($memory) ? $memory->content : "") }}</textarea>
                            <div class="form-text">记忆的具体内容，JSON类型请确保格式正确</div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ isset($memory) ? route("user.memories.show", $memory->id) : route("user.memories.index") }}" class="btn btn-outline-secondary">取消</a>
                            <button type="submit" class="btn btn-primary">{{ isset($memory) ? "更新" : "创建" }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section("scripts")
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const typeSelect = document.getElementById("type");
        const contentTextarea = document.getElementById("content");
        
        typeSelect.addEventListener("change", function() {
            if (this.value === "json") {
                try {
                    // 尝试格式化JSON
                    const content = contentTextarea.value.trim();
                    if (content && content !== "") {
                        const jsonObj = JSON.parse(content);
                        contentTextarea.value = JSON.stringify(jsonObj, null, 2);
                    }
                } catch (e) {
                    // 如果不是有效的JSON，不做任何处理
                }
            }
        });
    });
</script>
@endsection
@endsection
