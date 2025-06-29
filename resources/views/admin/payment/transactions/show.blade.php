@extends("admin.layouts.app")

@section("title", "��������")

@section("content")
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">��������</h3>
                    <div class="card-tools">
                        <a href="{{ route("admin.payment.transactions.index") }}" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> �����б�
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
                                    <h4 class="card-title">������Ϣ</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 150px;">����ID</th>
                                            <td>{{ $transaction->transaction_id }}</td>
                                        </tr>
                                        <tr>
                                            <th>����ID</th>
                                            <td>{{ $transaction->order_id }}</td>
                                        </tr>
                                        <tr>
                                            <th>�û�ID</th>
                                            <td>{{ $transaction->user_id ?: "-" }}</td>
                                        </tr>
                                        <tr>
                                            <th>֧������</th>
                                            <td>{{ $transaction->gateway_name }} ({{ $transaction->gateway_code }})</td>
                                        </tr>
                                        <tr>
                                            <th>���</th>
                                            <td>{{ $transaction->amount }} {{ $transaction->currency }}</td>
                                        </tr>
                                        <tr>
                                            <th>״̬</th>
                                            <td>
                                                @if($transaction->status === "pending")
                                                    <span class="badge badge-warning">��֧��</span>
                                                @elseif($transaction->status === "completed")
                                                    <span class="badge badge-success">�����</span>
                                                @elseif($transaction->status === "failed")
                                                    <span class="badge badge-danger">ʧ��</span>
                                                @elseif($transaction->status === "refunded")
                                                    <span class="badge badge-info">���˿�</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ $transaction->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>֧����ʽ</th>
                                            <td>{{ $transaction->payment_method ?: "-" }}</td>
                                        </tr>
                                        <tr>
                                            <th>�ͻ���IP</th>
                                            <td>{{ $transaction->client_ip ?: "-" }}</td>
                                        </tr>
                                        <tr>
                                            <th>������Ϣ</th>
                                            <td>{{ $transaction->error_message ?: "-" }}</td>
                                        </tr>
                                        <tr>
                                            <th>֧��ʱ��</th>
                                            <td>{{ $transaction->paid_at ?: "-" }}</td>
                                        </tr>
                                        <tr>
                                            <th>����ʱ��</th>
                                            <td>{{ $transaction->created_at }}</td>
                                        </tr>
                                        <tr>
                                            <th>����ʱ��</th>
                                            <td>{{ $transaction->updated_at }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="card-footer">
                                    <div class="btn-group">
                                        @if($transaction->status === "pending")
                                            <button type="button" class="btn btn-success update-status" data-status="completed">
                                                <i class="fas fa-check"></i> ���Ϊ�����
                                            </button>
                                            <button type="button" class="btn btn-danger update-status" data-status="failed">
                                                <i class="fas fa-times"></i> ���Ϊʧ��
                                            </button>
                                        @elseif($transaction->status === "completed")
                                            <button type="button" class="btn btn-info create-refund">
                                                <i class="fas fa-undo"></i> �����˿�
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">������Ӧ����</h4>
                                </div>
                                <div class="card-body">
                                    @if($transaction->gateway_response)
                                        <pre class="bg-light p-3">{{ json_encode($gatewayResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    @else
                                        <div class="alert alert-info">
                                            ����������Ӧ����
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
                                    <h4 class="card-title">�˿��¼</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>�˿�ID</th>
                                                <th>���</th>
                                                <th>״̬</th>
                                                <th>ԭ��</th>
                                                <th>�˿�ʱ��</th>
                                                <th>����</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($refunds as $refund)
                                                <tr>
                                                    <td>{{ $refund->refund_id }}</td>
                                                    <td>{{ $refund->amount }} {{ $transaction->currency }}</td>
                                                    <td>
                                                        @if($refund->status === "pending")
                                                            <span class="badge badge-warning">������</span>
                                                        @elseif($refund->status === "completed")
                                                            <span class="badge badge-success">�����</span>
                                                        @elseif($refund->status === "failed")
                                                            <span class="badge badge-danger">ʧ��</span>
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
                                                                    <i class="fas fa-check"></i> ���
                                                                </button>
                                                                <button type="button" class="btn btn-danger btn-sm update-refund-status" data-refund-id="{{ $refund->refund_id }}" data-status="failed">
                                                                    <i class="fas fa-times"></i> ʧ��
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">�����˿��¼</td>
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
                                    <h4 class="card-title">������־</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>��������</th>
                                                <th>IP��ַ</th>
                                                <th>״̬</th>
                                                <th>ʱ��</th>
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
                                                            <span class="badge badge-success">�ɹ�</span>
                                                        @else
                                                            <span class="badge badge-danger">ʧ��</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $log->created_at }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">������־��¼</td>
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

<!-- ����״̬ģ̬�� -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" role="dialog" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">���½���״̬</h5>
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
                        <label for="reason">ʧ��ԭ��</label>
                        <input type="text" class="form-control" id="reason" name="reason">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ȡ��</button>
                    <button type="submit" class="btn btn-primary">ȷ��</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- �����˿�ģ̬�� -->
<div class="modal fade" id="createRefundModal" tabindex="-1" role="dialog" aria-labelledby="createRefundModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createRefundModalLabel">�����˿�</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="createRefundForm" method="POST" action="{{ route("admin.payment.transactions.refund", $transaction->transaction_id) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="amount">�˿��� <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" max="{{ $transaction->amount }}" value="{{ $transaction->amount }}" required>
                            <div class="input-group-append">
                                <span class="input-group-text">{{ $transaction->currency }}</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">�����˿���: {{ $transaction->amount }} {{ $transaction->currency }}</small>
                    </div>
                    <div class="form-group">
                        <label for="refund_reason">�˿�ԭ�� <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="refund_reason" name="reason" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ȡ��</button>
                    <button type="submit" class="btn btn-primary">�ύ�˿�</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- �����˿�״̬ģ̬�� -->
<div class="modal fade" id="updateRefundStatusModal" tabindex="-1" role="dialog" aria-labelledby="updateRefundStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateRefundStatusModalLabel">�����˿�״̬</h5>
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
                        <label for="refund_fail_reason">ʧ��ԭ��</label>
                        <input type="text" class="form-control" id="refund_fail_reason" name="reason">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ȡ��</button>
                    <button type="submit" class="btn btn-primary">ȷ��</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
    $(function() {
        // ���½���״̬
        $(".update-status").click(function() {
            const status = $(this).data("status");
            $("#status_value").val(status);
            
            if (status === "completed") {
                $("#status_confirm_message").text("ȷ��Ҫ���˽��ױ��Ϊ�������");
                $("#reason_group").hide();
            } else if (status === "failed") {
                $("#status_confirm_message").text("ȷ��Ҫ���˽��ױ��Ϊʧ����");
                $("#reason_group").show();
            }
            
            $("#updateStatusModal").modal("show");
        });
        
        // �����˿�
        $(".create-refund").click(function() {
            $("#createRefundModal").modal("show");
        });
        
        // �����˿�״̬
        $(".update-refund-status").click(function() {
            const refundId = $(this).data("refund-id");
            const status = $(this).data("status");
            $("#refund_status_value").val(status);
            
            if (status === "completed") {
                $("#refund_status_confirm_message").text("ȷ��Ҫ�����˿���Ϊ�������");
                $("#refund_reason_group").hide();
            } else if (status === "failed") {
                $("#refund_status_confirm_message").text("ȷ��Ҫ�����˿���Ϊʧ����");
                $("#refund_reason_group").show();
            }
            
            $("#updateRefundStatusForm").attr("action", `{{ url("admin/payment/transactions/{$transaction->transaction_id}/refund") }}/${refundId}/status`);
            $("#updateRefundStatusModal").modal("show");
        });
    });
</script>
@endsection
