@extends("admin.layouts.app")

@section("title", "AI模型提供商详情")

@section("content_header")
    <h1>AI模型提供商详情</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">{{ $provider->name }} 详情</h3>
                <div>
                    <a href="{{ route("admin.ai.providers.edit", $provider->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> 编辑
                    </a>
                    <a href="{{ route("admin.ai.providers.index") }}" class="btn btn-default">
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
                            <td>{{ $provider->id }}</td>
                        </tr>
                        <tr>
                            <th>名称</th>
                            <td>{{ $provider->name }}</td>
                        </tr>
                        <tr>
                            <th>标识符</th>
                            <td>{{ $provider->identifier }}</td>
                        </tr>
                        <tr>
                            <th>API基础URL</th>
                            <td>{{ $provider->base_url }}</td>
                        </tr>
                        <tr>
                            <th>认证头</th>
                            <td>{{ $provider->auth_header }}</td>
                        </tr>
                        <tr>
                            <th>认证方案</th>
                            <td>{{ $provider->auth_scheme }}</td>
                        </tr>
                        <tr>
                            <th>状态</th>
                            <td>
                                @if($provider->is_active)
                                    <span class="badge badge-success">启用</span>
                                @else
                                    <span class="badge badge-danger">禁用</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>创建时间</th>
                            <td>{{ $provider->created_at }}</td>
                        </tr>
                        <tr>
                            <th>更新时间</th>
                            <td>{{ $provider->updated_at }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">描述</h4>
                        </div>
                        <div class="card-body">
                            {{ $provider->description ?: "暂无描述" }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="card-title">可用模型</h4>
                                <a href="{{ route("admin.ai.models.create", ["provider_id" => $provider->id]) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> 添加模型
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($provider->models->count() > 0)
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>名称</th>
                                            <th>标识符</th>
                                            <th>类型</th>
                                            <th>状态</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($provider->models as $model)
                                            <tr>
                                                <td>{{ $model->id }}</td>
                                                <td>{{ $model->name }}</td>
                                                <td>{{ $model->identifier }}</td>
                                                <td>{{ $model->type }}</td>
                                                <td>
                                                    @if($model->is_active)
                                                        <span class="badge badge-success">启用</span>
                                                    @else
                                                        <span class="badge badge-danger">禁用</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="{{ route("admin.ai.models.edit", $model->id) }}" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmDeleteModel({{ $model->id }})">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                    <form id="delete-model-form-{{ $model->id }}" action="{{ route("admin.ai.models.destroy", $model->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method("DELETE")
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="alert alert-info">
                                    该提供商暂无可用模型，请点击"添加模型"按钮添加。
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">API密钥</h4>
                        </div>
                        <div class="card-body">
                            @if($provider->apiKeys->count() > 0)
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>名称</th>
                                            <th>状态</th>
                                            <th>使用次数</th>
                                            <th>创建时间</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($provider->apiKeys as $key)
                                            <tr>
                                                <td>{{ $key->id }}</td>
                                                <td>{{ $key->name }}</td>
                                                <td>
                                                    @if($key->is_active)
                                                        <span class="badge badge-success">启用</span>
                                                    @else
                                                        <span class="badge badge-danger">禁用</span>
                                                    @endif
                                                </td>
                                                <td>{{ $key->usage_count }}</td>
                                                <td>{{ $key->created_at }}</td>
                                                <td>
                                                    <a href="{{ route("admin.ai.api-keys.show", $key->id) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> 查看
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="alert alert-info">
                                    该提供商暂无API密钥，请在API密钥管理中添加。
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
    function confirmDeleteModel(id) {
        if (confirm("确定要删除这个模型吗？此操作不可逆。")) {
            document.getElementById("delete-model-form-" + id).submit();
        }
    }
</script>
@stop
