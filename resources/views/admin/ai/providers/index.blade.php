@extends("admin.layouts.app")

@section("title", "AI模型提供商管理")

@section("content_header")
    <h1>AI模型提供商管理</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">AI模型提供商列表</h3>
                <a href="{{ route("admin.ai.providers.create") }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> 添加提供商
                </a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>名称</th>
                        <th>标识符</th>
                        <th>API基础URL</th>
                        <th>状态</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($providers as $provider)
                        <tr>
                            <td>{{ $provider->id }}</td>
                            <td>{{ $provider->name }}</td>
                            <td>{{ $provider->identifier }}</td>
                            <td>{{ $provider->base_url }}</td>
                            <td>
                                @if($provider->is_active)
                                    <span class="badge badge-success">启用</span>
                                @else
                                    <span class="badge badge-danger">禁用</span>
                                @endif
                            </td>
                            <td>{{ $provider->created_at }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route("admin.ai.providers.show", $provider->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route("admin.ai.providers.edit", $provider->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $provider->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-{{ $provider->is_active ? "secondary" : "success" }}" 
                                            onclick="toggleStatus({{ $provider->id }}, {{ $provider->is_active ? "false" : "true" }})">
                                        <i class="fas fa-{{ $provider->is_active ? "ban" : "check" }}"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="testConnection({{ $provider->id }})">
                                        <i class="fas fa-vial"></i> 测试
                                    </button>
                                </div>
                                <form id="delete-form-{{ $provider->id }}" action="{{ route("admin.ai.providers.destroy", $provider->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method("DELETE")
                                </form>
                                <form id="toggle-form-{{ $provider->id }}" action="{{ route("admin.ai.providers.toggle", $provider->id) }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                                <form id="test-form-{{ $provider->id }}" action="{{ route("admin.ai.providers.test", $provider->id) }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            {{ $providers->links() }}
        </div>
    </div>
@stop

@section("js")
<script>
    function confirmDelete(id) {
        if (confirm("确定要删除这个模型提供商吗？此操作不可逆。")) {
            document.getElementById("delete-form-" + id).submit();
        }
    }
    
    function toggleStatus(id, status) {
        const action = status ? "启用" : "禁用";
        if (confirm(`确定要${action}这个模型提供商吗？`)) {
            document.getElementById("toggle-form-" + id).submit();
        }
    }
    
    function testConnection(id) {
        if (confirm("确定要测试与该模型提供商的连接吗？")) {
            document.getElementById("test-form-" + id).submit();
        }
    }
</script>
@stop
