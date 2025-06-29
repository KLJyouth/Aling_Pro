@extends("admin.layouts.app")

@section("title", "֧�����ع���")

@section("content")
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">֧�������б�</h3>
                    <div class="card-tools">
                        <a href="{{ route("admin.payment.gateways.create") }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> ���֧������
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
                                <th>����</th>
                                <th>����</th>
                                <th>����</th>
                                <th style="width: 100px">״̬</th>
                                <th style="width: 100px">����ģʽ</th>
                                <th style="width: 200px">����</th>
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
                                            <span class="badge badge-secondary">��ͼƬ</span>
                                        @endif
                                    </td>
                                    <td>{{ $gateway->name }}</td>
                                    <td>{{ $gateway->code }}</td>
                                    <td>{{ $gateway->description }}</td>
                                    <td>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input toggle-status" id="status_{{ $gateway->id }}" data-id="{{ $gateway->id }}" {{ $gateway->is_active ? "checked" : "" }}>
                                            <label class="custom-control-label" for="status_{{ $gateway->id }}">{{ $gateway->is_active ? "����" : "ͣ��" }}</label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input toggle-test-mode" id="test_mode_{{ $gateway->id }}" data-id="{{ $gateway->id }}" {{ $gateway->is_test_mode ? "checked" : "" }}>
                                            <label class="custom-control-label" for="test_mode_{{ $gateway->id }}">{{ $gateway->is_test_mode ? "��" : "��" }}</label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route("admin.payment.gateways.show", $gateway->id) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> �鿴
                                            </a>
                                            <a href="{{ route("admin.payment.gateways.edit", $gateway->id) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> �༭
                                            </a>
                                            <button type="button" class="btn btn-success btn-sm test-gateway" data-id="{{ $gateway->id }}">
                                                <i class="fas fa-vial"></i> ����
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm delete-gateway" data-id="{{ $gateway->id }}" data-name="{{ $gateway->name }}">
                                                <i class="fas fa-trash"></i> ɾ��
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">����֧������</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
        
        // �л�״̬
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
                        
                        // ���±�ǩ�ı�
                        $(`#status_${id}`).next("label").text(active ? "����" : "ͣ��");
                    } else {
                        toastr.error(response.message);
                        
                        // �ָ�ԭ״̬
                        $(`#status_${id}`).prop("checked", !active);
                    }
                },
                error: function(xhr) {
                    toastr.error("����ʧ�ܣ�������");
                    
                    // �ָ�ԭ״̬
                    $(`#status_${id}`).prop("checked", !active);
                }
            });
        });
        
        // �л�����ģʽ
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
                        
                        // ���±�ǩ�ı�
                        $(`#test_mode_${id}`).next("label").text(testMode ? "��" : "��");
                    } else {
                        toastr.error(response.message);
                        
                        // �ָ�ԭ״̬
                        $(`#test_mode_${id}`).prop("checked", !testMode);
                    }
                },
                error: function(xhr) {
                    toastr.error("����ʧ�ܣ�������");
                    
                    // �ָ�ԭ״̬
                    $(`#test_mode_${id}`).prop("checked", !testMode);
                }
            });
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
