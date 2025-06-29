@extends("admin.layouts.app")

@section("title", "支付网关详情")

@section("content")
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">支付网关详情</h3>
                    <div class="card-tools">
                        <a href="{{ route("admin.payment.gateways.index") }}" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> 返回列表
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session("success"))
                        <div class="alert alert-success">
                            {{ session("success") }}
                        </div>
                    @endif
                    
                    @if(session("error"))
                        <div class="alert alert-danger">
                            {{ session("error") }}
                        </div>
                    @endif
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">基本信息</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 150px;">ID</th>
                                            <td>{{ $gateway->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>名称</th>
                                            <td>{{ $gateway->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>代码</th>
                                            <td>{{ $gateway->code }}</td>
                                        </tr>
                                        <tr>
                                            <th>描述</th>
                                            <td>{{ $gateway->description }}</td>
                                        </tr>
                                        <tr>
                                            <th>Logo</th>
                                            <td>
                                                @if($gateway->logo)
                                                    <img src="{{ asset("storage/" . $gateway->logo) }}" alt="{{ $gateway->name }}" class="img-thumbnail" style="max-height: 100px;">
                                                @else
                                                    <span class="badge badge-secondary">无图片</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>状态</th>
                                            <td>
                                                @if($gateway->is_active)
                                                    <span class="badge badge-success">启用</span>
                                                @else
                                                    <span class="badge badge-danger">停用</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>测试模式</th>
                                            <td>
                                                @if($gateway->is_test_mode)
                                                    <span class="badge badge-warning">是</span>
                                                @else
                                                    <span class="badge badge-info">否</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>排序</th>
                                            <td>{{ $gateway->sort_order }}</td>
                                        </tr>
                                        <tr>
                                            <th>创建时间</th>
                                            <td>{{ $gateway->created_at }}</td>
                                        </tr>
                                        <tr>
                                            <th>更新时间</th>
                                            <td>{{ $gateway->updated_at }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="card-footer">
                                    <div class="btn-group">
                                        <a href="{{ route("admin.payment.gateways.edit", $gateway->id) }}" class="btn btn-primary">
                                            <i class="fas fa-edit"></i> 编辑
                                        </a>
                                        <button type="button" class="btn btn-success test-gateway" data-id="{{ $gateway->id }}">
                                            <i class="fas fa-vial"></i> 测试连接
                                        </button>
                                        <button type="button" class="btn btn-danger delete-gateway" data-id="{{ $gateway->id }}" data-name="{{ $gateway->name }}">
                                            <i class="fas fa-trash"></i> 删除
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">配置信息</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        @foreach($fields as $field => $label)
                                            <tr>
                                                <th style="width: 150px;">{{ $label }}</th>
                                                <td>
                                                    @if(in_array($field, ["private_key", "key", "secret_key", "client_secret", "cert_password", "alipay_public_key"]))
                                                        <span class="text-muted">******</span>
                                                    @elseif(isset($config[$field]))
                                                        {{ $config[$field] }}
                                                    @else
                                                        <span class="text-danger">未设置</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">最近操作日志</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>操作类型</th>
                                                <th>交易ID</th>
                                                <th>IP地址</th>
                                                <th>状态</th>
                                                <th>错误信息</th>
                                                <th>时间</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($logs as $log)
                                                <tr>
                                                    <td>{{ $log->id }}</td>
                                                    <td>{{ $log->action }}</td>
                                                    <td>{{ $log->transaction_id ?: "-" }}</td>
                                                    <td>{{ $log->ip_address }}</td>
                                                    <td>
                                                        @if($log->is_success)
                                                            <span class="badge badge-success">成功</span>
                                                        @else
                                                            <span class="badge badge-danger">失败</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $log->error_message ?: "-" }}</td>
                                                    <td>{{ $log->created_at }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center">暂无日志记录</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 删除确认模态框 -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">确认删除</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>确定要删除支付网关 <strong id="gatewayName"></strong> 吗？</p>
                <p class="text-danger">此操作不可逆，请谨慎操作！</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method("DELETE")
                    <button type="submit" class="btn btn-danger">确认删除</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 测试结果模态框 -->
<div class="modal fade" id="testResultModal" tabindex="-1" role="dialog" aria-labelledby="testResultModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testResultModalLabel">测试结果</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="testResultContent">
                <!-- 测试结果将在这里显示 -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
    $(function() {
        // 删除确认
        $(".delete-gateway").click(function() {
            const id = $(this).data("id");
            const name = $(this).data("name");
            
            $("#gatewayName").text(name);
            $("#deleteForm").attr("action", `{{ url("admin/payment/gateways") }}/${id}`);
            $("#deleteModal").modal("show");
        });
        
        // 测试网关
        $(".test-gateway").click(function() {
            const id = $(this).data("id");
            
            // 显示加载中
            $("#testResultContent").html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> 正在测试连接，请稍候...</div>');
            $("#testResultModal").modal("show");
            
            $.ajax({
                url: `{{ url("admin/payment/gateways") }}/${id}/test`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        $("#testResultContent").html(`
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> ${response.message}
                            </div>
                        `);
                    } else {
                        $("#testResultContent").html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-times-circle"></i> ${response.message}
                            </div>
                        `);
                    }
                },
                error: function(xhr) {
                    let errorMessage = "测试连接失败，请重试";
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    $("#testResultContent").html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i> ${errorMessage}
                        </div>
                    `);
                }
            });
        });
    });
</script>
@endsection
