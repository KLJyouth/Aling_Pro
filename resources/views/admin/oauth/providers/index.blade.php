@extends("admin.layouts.app")

@section("title", "OAuth提供商管理")

@section("content_header")
    <h1>OAuth提供商管理</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">OAuth提供商列表</h3>
                <div>
                    <a href="{{ route("admin.oauth.providers.create") }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> 添加提供商
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>图标</th>
                        <th>名称</th>
                        <th>标识符</th>
                        <th>状态</th>
                        <th>用户数</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($providers as $provider)
                        <tr>
                            <td>{{ $provider->id }}</td>
                            <td><i class="{{ $provider->icon }} fa-2x"></i></td>
                            <td>{{ $provider->name }}</td>
                            <td>{{ $provider->identifier }}</td>
                            <td>
                                @if($provider->is_active)
                                    <span class="badge badge-success">启用</span>
                                @else
                                    <span class="badge badge-danger">禁用</span>
                                @endif
                            </td>
                            <td>{{ $provider->user_accounts_count }}</td>
                            <td>{{ $provider->created_at }}</td>
                            <td>
                                <a href="{{ route("admin.oauth.providers.show", $provider->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> 查看
                                </a>
                                <a href="{{ route("admin.oauth.providers.edit", $provider->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> 编辑
                                </a>
                                @if($provider->user_accounts_count == 0)
                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal{{ $provider->id }}">
                                        <i class="fas fa-trash"></i> 删除
                                    </button>
                                @endif
                            </td>
                        </tr>
                        
                        <!-- 删除确认模态框 -->
                        <div class="modal fade" id="deleteModal{{ $provider->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel{{ $provider->id }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel{{ $provider->id }}">确认删除</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>确定要删除 <strong>{{ $provider->name }}</strong> 提供商吗？此操作不可逆。</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                                        <form action="{{ route("admin.oauth.providers.destroy", $provider->id) }}" method="POST">
                                            @csrf
                                            @method("DELETE")
                                            <button type="submit" class="btn btn-danger">确认删除</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">OAuth相关功能</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-clipboard-list"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">OAuth日志</span>
                            <span class="info-box-number">查看所有OAuth登录、注册和关联日志</span>
                            <a href="{{ route("admin.oauth.logs.index") }}" class="btn btn-sm btn-info mt-2">查看日志</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">用户OAuth账号</span>
                            <span class="info-box-number">查看所有用户的OAuth关联账号</span>
                            <a href="{{ route("admin.oauth.user-accounts.index") }}" class="btn btn-sm btn-success mt-2">查看账号</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
