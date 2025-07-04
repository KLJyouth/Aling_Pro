@extends("admin.layouts.app")

@section("title", "API密钥管理")

@section("content_header")
    <h1>API密钥管理</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">API密钥列表</h3>
                <a href="{{ route("admin.ai.api-keys.create") }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> 添加API密钥
                </a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>名称</th>
                        <th>提供商</th>
                        <th>密钥掩码</th>
                        <th>使用次数</th>
                        <th>状态</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($apiKeys as $key)
                        <tr>
                            <td>{{ $key->id }}</td>
                            <td>{{ $key->name }}</td>
                            <td>{{ $key->provider->name }}</td>
                            <td><code>{{ $key->key_mask }}</code></td>
                            <td>{{ $key->usage_count }}</td>
                            <td>
                                @if($key->is_active)
                                    <span class="badge badge-success">启用</span>
                                @else
                                    <span class="badge badge-danger">禁用</span>
                                @endif
                            </td>
                            <td>{{ $key->created_at }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route("admin.ai.api-keys.show", $key->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route("admin.ai.api-keys.edit", $key->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $key->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-{{ $key->is_active ? "secondary" : "success" }}" 
                                            onclick="toggleStatus({{ $key->id }}, {{ $key->is_active ? "false" : "true" }})">
                                        <i class="fas fa-{{ $key->is_active ? "ban" : "check" }}"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="resetQuota({{ $key->id }})">
                                        <i class="fas fa-redo"></i> 重置配额
                                    </button>
                                </div>
                                <form id="delete-form-{{ $key->id }}" action="{{ route("admin.ai.api-keys.destroy", $key->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method("DELETE")
                                </form>
                                <form id="toggle-form-{{ $key->id }}" action="{{ route("admin.ai.api-keys.toggle", $key->id) }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                                <form id="reset-form-{{ $key->id }}" action="{{ route("admin.ai.api-keys.reset-quota", $key->id) }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            {{ $apiKeys->links() }}
        </div>
    </div>
@stop

@section("js")
<script>
    function confirmDelete(id) {
        if (confirm("确定要删除这个API密钥吗？此操作不可逆。")) {
            document.getElementById("delete-form-" + id).submit();
        }
    }
    
    function toggleStatus(id, status) {
        const action = status ? "启用" : "禁用";
        if (confirm(`确定要${action}这个API密钥吗？`)) {
            document.getElementById("toggle-form-" + id).submit();
        }
    }
    
    function resetQuota(id) {
        if (confirm("确定要重置这个API密钥的使用配额吗？")) {
            document.getElementById("reset-form-" + id).submit();
        }
    }
</script>
@stop
