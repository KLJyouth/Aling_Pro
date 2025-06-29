@extends("admin.layouts.app")

@section("title", "交易详情")

@section("content")
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">交易详情</h3>
                    <div class="card-tools">
                        <a href="{{ route("admin.payment.transactions.index") }}" class="btn btn-default btn-sm">
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
                                            <th style="width: 150px;">交易ID</th>
                                            <td>{{ $transaction->transaction_id }}</td>
                                        </tr>
                                        <tr>
                                            <th>订单ID</th>
                                            <td>{{ $transaction->order_id }}</td>
                                        </tr>
                                        <tr>
                                            <th>用户ID</th>
                                            <td>{{ $transaction->user_id ?: "-" }}</td>
                                        </tr>
                                        <tr>
                                            <th>支付网关</th>
                                            <td>{{ $transaction->gateway_name }} ({{ $transaction->gateway_code }})</td>
                                        </tr>
                                        <tr>
                                            <th>金额</th>
                                            <td>{{ $transaction->amount }} {{ $transaction->currency }}</td>
                                        </tr>
                                        <tr>
                                            <th>状态</th>
                                            <td>
                                                @if($transaction->status === "pending")
                                                    <span class="badge badge-warning">待支付</span>
                                                @elseif($transaction->status === "completed")
                                                    <span class="badge badge-success">已完成</span>
                                                @elseif($transaction->status === "failed")
                                                    <span class="badge badge-danger">失败</span>
                                                @elseif($transaction->status === "refunded")
                                                    <span class="badge badge-info">已退款</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ $transaction->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>支付方式</th>
                                            <td>{{ $transaction->payment_method ?: "-" }}</td>
                                        </tr>
                                        <tr>
                                            <th>客户端IP</th>
                                            <td>{{ $transaction->client_ip ?: "-" }}</td>
                                        </tr>
                                        <tr>
                                            <th>错误信息</th>
                                            <td>{{ $transaction->error_message ?: "-" }}</td>
                                        </tr>
                                        <tr>
                                            <th>支付时间</th>
                                            <td>{{ $transaction->paid_at ?: "-" }}</td>
                                        </tr>
                                        <tr>
                                            <th>创建时间</th>
                                            <td>{{ $transaction->created_at }}</td>
                                        </tr>
                                        <tr>
                                            <th>更新时间</th>
                                            <td>{{ $transaction->updated_at }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="card-footer">
                                    <div class="btn-group">
                                        @if($transaction->status === "pending")
                                            <button type="button" class="btn btn-success update-status" data-status="completed">
                                                <i class="fas fa-check"></i> 标记为已完成
                                            </button>
                                            <button type="button" class="btn btn-danger update-status" data-status="failed">
                                                <i class="fas fa-times"></i> 标记为失败
                                            </button>
                                        @elseif($transaction->status === "completed")
                                            <button type="button" class="btn btn-info create-refund">
                                                <i class="fas fa-undo"></i> 创建退款
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">网关响应数据</h4>
                                </div>
                                <div class="card-body">
                                    @if($transaction->gateway_response)
                                        <pre class="bg-light p-3">{{ json_encode($gatewayResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    @else
                                        <div class="alert alert-info">
                                            暂无网关响应数据
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">退款记录</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>退款ID</th>
                                                <th>金额</th>
                                                <th>状态</th>
                                                <th>原因</th>
                                                <th>退款时间</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($refunds as $refund)
                                                <tr>
                                                    <td>{{ $refund->refund_id }}</td>
                                                    <td>{{ $refund->amount }} {{ $transaction->currency }}</td>
                                                    <td>
                                                        @if($refund->status === "pending")
                                                            <span class="badge badge-warning">处理中</span>
                                                        @elseif($refund->status === "completed")
                                                            <span class="badge badge-success">已完成</span>
                                                        @elseif($refund->status === "failed")
                                                            <span class="badge badge-danger">失败</span>
                                                        @else
                                                            <span class="badge badge-secondary">{{ $refund->status }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $refund->reason }}</td>
                                                    <td>{{ $refund->refunded_at ?: "-" }}</td>
                                                    <td>
                                                        @if($refund->status === "pending")
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-success btn-sm update-refund-status" data-refund-id="{{ $refund->refund_id }}" data-status="completed">
                                                                    <i class="fas fa-check"></i> 完成
                                                                </button>
                                                                <button type="button" class="btn btn-danger btn-sm update-refund-status" data-refund-id="{{ $refund->refund_id }}" data-status="failed">
                                                                    <i class="fas fa-times"></i> 失败
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">暂无退款记录</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">操作日志</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>操作类型</th>
                                                <th>IP地址</th>
                                                <th>状态</th>
                                                <th>时间</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($logs as $log)
                                                <tr>
                                                    <td>{{ $log->id }}</td>
                                                    <td>{{ $log->action }}</td>
                                                    <td>{{ $log->ip_address }}</td>
                                                    <td>
                                                        @if($log->is_success)
                                                            <span class="badge badge-success">成功</span>
                                                        @else
                                                            <span class="badge badge-danger">失败</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $log->created_at }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">暂无日志记录</td>
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

<!-- 更新状态模态框 -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" role="dialog" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">更新交易状态</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="updateStatusForm" method="POST" action="{{ route("admin.payment.transactions.update-status", $transaction->transaction_id) }}">
                @csrf
                <input type="hidden" name="status" id="status_value">
                <div class="modal-body">
                    <p id="status_confirm_message"></p>
                    <div class="form-group" id="reason_group" style="display: none;">
                        <label for="reason">失败原因</label>
                        <input type="text" class="form-control" id="reason" name="reason">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">确认</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 创建退款模态框 -->
<div class="modal fade" id="createRefundModal" tabindex="-1" role="dialog" aria-labelledby="createRefundModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createRefundModalLabel">创建退款</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="createRefundForm" method="POST" action="{{ route("admin.payment.transactions.refund", $transaction->transaction_id) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="amount">退款金额 <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" max="{{ $transaction->amount }}" value="{{ $transaction->amount }}" required>
                            <div class="input-group-append">
                                <span class="input-group-text">{{ $transaction->currency }}</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">最大可退款金额: {{ $transaction->amount }} {{ $transaction->currency }}</small>
                    </div>
                    <div class="form-group">
                        <label for="refund_reason">退款原因 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="refund_reason" name="reason" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">提交退款</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 更新退款状态模态框 -->
<div class="modal fade" id="updateRefundStatusModal" tabindex="-1" role="dialog" aria-labelledby="updateRefundStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateRefundStatusModalLabel">更新退款状态</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="updateRefundStatusForm" method="POST" action="">
                @csrf
                <input type="hidden" name="status" id="refund_status_value">
                <div class="modal-body">
                    <p id="refund_status_confirm_message"></p>
                    <div class="form-group" id="refund_reason_group" style="display: none;">
                        <label for="refund_fail_reason">失败原因</label>
                        <input type="text" class="form-control" id="refund_fail_reason" name="reason">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">确认</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
    $(function() {
        // 更新交易状态
        $(".update-status").click(function() {
            const status = $(this).data("status");
            $("#status_value").val(status);
            
            if (status === "completed") {
                $("#status_confirm_message").text("确定要将此交易标记为已完成吗？");
                $("#reason_group").hide();
            } else if (status === "failed") {
                $("#status_confirm_message").text("确定要将此交易标记为失败吗？");
                $("#reason_group").show();
            }
            
            $("#updateStatusModal").modal("show");
        });
        
        // 创建退款
        $(".create-refund").click(function() {
            $("#createRefundModal").modal("show");
        });
        
        // 更新退款状态
        $(".update-refund-status").click(function() {
            const refundId = $(this).data("refund-id");
            const status = $(this).data("status");
            $("#refund_status_value").val(status);
            
            if (status === "completed") {
                $("#refund_status_confirm_message").text("确定要将此退款标记为已完成吗？");
                $("#refund_reason_group").hide();
            } else if (status === "failed") {
                $("#refund_status_confirm_message").text("确定要将此退款标记为失败吗？");
                $("#refund_reason_group").show();
            }
            
            $("#updateRefundStatusForm").attr("action", `{{ url("admin/payment/transactions/{$transaction->transaction_id}/refund") }}/${refundId}/status`);
            $("#updateRefundStatusModal").modal("show");
        });
    });
</script>
@endsection
