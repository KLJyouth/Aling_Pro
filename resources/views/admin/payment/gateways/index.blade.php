@extends("admin.layouts.app")

@section("title", "支付网关管理")

@section("content")
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">支付网关列表</h3>
                    <div class="card-tools">
                        <a href="{{ route("admin.payment.gateways.create") }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> 添加支付网关
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
                    
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 50px">ID</th>
                                <th style="width: 80px">Logo</th>
                                <th>名称</th>
                                <th>代码</th>
                                <th>描述</th>
                                <th style="width: 100px">状态</th>
                                <th style="width: 100px">测试模式</th>
                                <th style="width: 200px">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($gateways as $gateway)
                                <tr>
                                    <td>{{ $gateway->id }}</td>
                                    <td>
                                        @if($gateway->logo)
                                            <img src="{{ asset("storage/" . $gateway->logo) }}" alt="{{ $gateway->name }}" class="img-thumbnail" style="max-height: 40px;">
                                        @else
                                            <span class="badge badge-secondary">无图片</span>
                                        @endif
                                    </td>
                                    <td>{{ $gateway->name }}</td>
                                    <td>{{ $gateway->code }}</td>
                                    <td>{{ $gateway->description }}</td>
                                    <td>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input toggle-status" id="status_{{ $gateway->id }}" data-id="{{ $gateway->id }}" {{ $gateway->is_active ? "checked" : "" }}>
                                            <label class="custom-control-label" for="status_{{ $gateway->id }}">{{ $gateway->is_active ? "启用" : "停用" }}</label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input toggle-test-mode" id="test_mode_{{ $gateway->id }}" data-id="{{ $gateway->id }}" {{ $gateway->is_test_mode ? "checked" : "" }}>
                                            <label class="custom-control-label" for="test_mode_{{ $gateway->id }}">{{ $gateway->is_test_mode ? "是" : "否" }}</label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route("admin.payment.gateways.show", $gateway->id) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> 查看
                                            </a>
                                            <a href="{{ route("admin.payment.gateways.edit", $gateway->id) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> 编辑
                                            </a>
                                            <button type="button" class="btn btn-success btn-sm test-gateway" data-id="{{ $gateway->id }}">
                                                <i class="fas fa-vial"></i> 测试
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm delete-gateway" data-id="{{ $gateway->id }}" data-name="{{ $gateway->name }}">
                                                <i class="fas fa-trash"></i> 删除
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">暂无支付网关</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
        
        // 切换状态
        $(".toggle-status").change(function() {
            const id = $(this).data("id");
            const active = $(this).prop("checked");
            
            $.ajax({
                url: `{{ url("admin/payment/gateways") }}/${id}/toggle`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    active: active
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        
                        // 更新标签文本
                        $(`#status_${id}`).next("label").text(active ? "启用" : "停用");
                    } else {
                        toastr.error(response.message);
                        
                        // 恢复原状态
                        $(`#status_${id}`).prop("checked", !active);
                    }
                },
                error: function(xhr) {
                    toastr.error("操作失败，请重试");
                    
                    // 恢复原状态
                    $(`#status_${id}`).prop("checked", !active);
                }
            });
        });
        
        // 切换测试模式
        $(".toggle-test-mode").change(function() {
            const id = $(this).data("id");
            const testMode = $(this).prop("checked");
            
            $.ajax({
                url: `{{ url("admin/payment/gateways") }}/${id}/test-mode`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    test_mode: testMode
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        
                        // 更新标签文本
                        $(`#test_mode_${id}`).next("label").text(testMode ? "是" : "否");
                    } else {
                        toastr.error(response.message);
                        
                        // 恢复原状态
                        $(`#test_mode_${id}`).prop("checked", !testMode);
                    }
                },
                error: function(xhr) {
                    toastr.error("操作失败，请重试");
                    
                    // 恢复原状态
                    $(`#test_mode_${id}`).prop("checked", !testMode);
                }
            });
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
