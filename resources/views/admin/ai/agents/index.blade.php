@extends("admin.layouts.app")

@section("title", "AI智能体管理")

@section("content_header")
    <h1>AI智能体管理</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">AI智能体列表</h3>
                <a href="{{ route("admin.ai.agents.create") }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> 添加智能体
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
                        <th>类型</th>
                        <th>状态</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agents as $agent)
                        <tr>
                            <td>{{ $agent->id }}</td>
                            <td>{{ $agent->name }}</td>
                            <td>{{ $agent->provider->name }}</td>
                            <td>{{ $agent->type }}</td>
                            <td>
                                @if($agent->is_active)
                                    <span class="badge badge-success">启用</span>
                                @else
                                    <span class="badge badge-danger">禁用</span>
                                @endif
                            </td>
                            <td>{{ $agent->created_at }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route("admin.ai.agents.show", $agent->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route("admin.ai.agents.edit", $agent->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $agent->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-{{ $agent->is_active ? "secondary" : "success" }}" 
                                            onclick="toggleStatus({{ $agent->id }}, {{ $agent->is_active ? "false" : "true" }})">
                                        <i class="fas fa-{{ $agent->is_active ? "ban" : "check" }}"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="testConnection({{ $agent->id }})">
                                        <i class="fas fa-vial"></i> 测试
                                    </button>
                                </div>
                                <form id="delete-form-{{ $agent->id }}" action="{{ route("admin.ai.agents.destroy", $agent->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method("DELETE")
                                </form>
                                <form id="toggle-form-{{ $agent->id }}" action="{{ route("admin.ai.agents.toggle", $agent->id) }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                                <form id="test-form-{{ $agent->id }}" action="{{ route("admin.ai.agents.test", $agent->id) }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            {{ $agents->links() }}
        </div>
    </div>
@stop

@section("js")
<script>
    function confirmDelete(id) {
        if (confirm("确定要删除这个智能体吗？此操作不可逆。")) {
            document.getElementById("delete-form-" + id).submit();
        }
    }
    
    function toggleStatus(id, status) {
        const action = status ? "启用" : "禁用";
        if (confirm(`确定要${action}这个智能体吗？`)) {
            document.getElementById("toggle-form-" + id).submit();
        }
    }
    
    function testConnection(id) {
        if (confirm("确定要测试与该智能体的连接吗？")) {
            document.getElementById("test-form-" + id).submit();
        }
    }
</script>
@stop
