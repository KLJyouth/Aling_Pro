@extends("admin.layouts.app")

@section("title", "֧����������")

@section("content")
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">֧����������</h3>
                    <div class="card-tools">
                        <a href="{{ route("admin.payment.gateways.index") }}" class="btn btn-default btn-sm">
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
                                            <th style="width: 150px;">ID</th>
                                            <td>{{ $gateway->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>����</th>
                                            <td>{{ $gateway->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>����</th>
                                            <td>{{ $gateway->code }}</td>
                                        </tr>
                                        <tr>
                                            <th>����</th>
                                            <td>{{ $gateway->description }}</td>
                                        </tr>
                                        <tr>
                                            <th>Logo</th>
                                            <td>
                                                @if($gateway->logo)
                                                    <img src="{{ asset("storage/" . $gateway->logo) }}" alt="{{ $gateway->name }}" class="img-thumbnail" style="max-height: 100px;">
                                                @else
                                                    <span class="badge badge-secondary">��ͼƬ</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>״̬</th>
                                            <td>
                                                @if($gateway->is_active)
                                                    <span class="badge badge-success">����</span>
                                                @else
                                                    <span class="badge badge-danger">ͣ��</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>����ģʽ</th>
                                            <td>
                                                @if($gateway->is_test_mode)
                                                    <span class="badge badge-warning">��</span>
                                                @else
                                                    <span class="badge badge-info">��</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>����</th>
                                            <td>{{ $gateway->sort_order }}</td>
                                        </tr>
                                        <tr>
                                            <th>����ʱ��</th>
                                            <td>{{ $gateway->created_at }}</td>
                                        </tr>
                                        <tr>
                                            <th>����ʱ��</th>
                                            <td>{{ $gateway->updated_at }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="card-footer">
                                    <div class="btn-group">
                                        <a href="{{ route("admin.payment.gateways.edit", $gateway->id) }}" class="btn btn-primary">
                                            <i class="fas fa-edit"></i> �༭
                                        </a>
                                        <button type="button" class="btn btn-success test-gateway" data-id="{{ $gateway->id }}">
                                            <i class="fas fa-vial"></i> ��������
                                        </button>
                                        <button type="button" class="btn btn-danger delete-gateway" data-id="{{ $gateway->id }}" data-name="{{ $gateway->name }}">
                                            <i class="fas fa-trash"></i> ɾ��
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">������Ϣ</h4>
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
                                                        <span class="text-danger">δ����</span>
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
                                    <h4 class="card-title">���������־</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>��������</th>
                                                <th>����ID</th>
                                                <th>IP��ַ</th>
                                                <th>״̬</th>
                                                <th>������Ϣ</th>
                                                <th>ʱ��</th>
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
                                                            <span class="badge badge-success">�ɹ�</span>
                                                        @else
                                                            <span class="badge badge-danger">ʧ��</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $log->error_message ?: "-" }}</td>
                                                    <td>{{ $log->created_at }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center">������־��¼</td>
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

<!-- ɾ��ȷ��ģ̬�� -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">ȷ��ɾ��</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>ȷ��Ҫɾ��֧������ <strong id="gatewayName"></strong> ��</p>
                <p class="text-danger">�˲��������棬�����������</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ȡ��</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method("DELETE")
                    <button type="submit" class="btn btn-danger">ȷ��ɾ��</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ���Խ��ģ̬�� -->
<div class="modal fade" id="testResultModal" tabindex="-1" role="dialog" aria-labelledby="testResultModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testResultModalLabel">���Խ��</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="testResultContent">
                <!-- ���Խ������������ʾ -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">�ر�</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
    $(function() {
        // ɾ��ȷ��
        $(".delete-gateway").click(function() {
            const id = $(this).data("id");
            const name = $(this).data("name");
            
            $("#gatewayName").text(name);
            $("#deleteForm").attr("action", `{{ url("admin/payment/gateways") }}/${id}`);
            $("#deleteModal").modal("show");
        });
        
        // ��������
        $(".test-gateway").click(function() {
            const id = $(this).data("id");
            
            // ��ʾ������
            $("#testResultContent").html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> ���ڲ������ӣ����Ժ�...</div>');
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
                    let errorMessage = "��������ʧ�ܣ�������";
                    
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
