@extends("admin.layouts.app")

@section("title", "֧������")

@section("content")
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">֧������</h3>
                    <div class="card-tools">
                        <a href="{{ route("admin.payment.settings.create") }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> �������
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
                    
                    <form action="{{ route("admin.payment.settings.update") }}" method="POST">
                        @csrf
                        @method("PUT")
                        
                        <div class="nav-tabs-custom">
                            <ul class="nav nav-tabs" id="settings-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true">��������</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="notification-tab" data-toggle="tab" href="#notification" role="tab" aria-controls="notification" aria-selected="false">֪ͨ����</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="security-tab" data-toggle="tab" href="#security" role="tab" aria-controls="security" aria-selected="false">��ȫ����</a>
                                </li>
                            </ul>
                            
                            <div class="tab-content mt-3" id="settings-tab-content">
                                <!-- �������� -->
                                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                    <div class="card card-body bg-light">
                                        <h5 class="mb-3">��������</h5>
                                        
                                        @forelse($generalSettings as $setting)
                                            <div class="form-group">
                                                <label for="{{ $setting->key }}">{{ $setting->description }}</label>
                                                
                                                @if($setting->key === "payment_currency")
                                                    <select class="form-control" id="{{ $setting->key }}" name="general.{{ $setting->key }}">
                                                        <option value="CNY" {{ $setting->value === "CNY" ? "selected" : "" }}>����� (CNY)</option>
                                                        <option value="USD" {{ $setting->value === "USD" ? "selected" : "" }}>��Ԫ (USD)</option>
                                                        <option value="EUR" {{ $setting->value === "EUR" ? "selected" : "" }}>ŷԪ (EUR)</option>
                                                        <option value="GBP" {{ $setting->value === "GBP" ? "selected" : "" }}>Ӣ�� (GBP)</option>
                                                        <option value="JPY" {{ $setting->value === "JPY" ? "selected" : "" }}>��Ԫ (JPY)</option>
                                                    </select>
                                                @elseif($setting->key === "payment_expire_time")
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" id="{{ $setting->key }}" name="general.{{ $setting->key }}" value="{{ $setting->value }}" min="1">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">����</span>
                                                        </div>
                                                    </div>
                                                @elseif($setting->key === "auto_complete_payment")
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="{{ $setting->key }}" name="general.{{ $setting->key }}" value="true" {{ $setting->value === "true" ? "checked" : "" }}>
                                                        <label class="custom-control-label" for="{{ $setting->key }}">�����Զ����֧��</label>
                                                    </div>
                                                @else
                                                    <input type="text" class="form-control" id="{{ $setting->key }}" name="general.{{ $setting->key }}" value="{{ $setting->value }}">
                                                @endif
                                                
                                                @if(!$setting->is_system)
                                                    <div class="mt-1">
                                                        <button type="button" class="btn btn-danger btn-sm delete-setting" data-id="{{ $setting->id }}" data-key="{{ $setting->key }}">
                                                            <i class="fas fa-trash"></i> ɾ��
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="alert alert-info">
                                                ���޻�������
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                                
                                <!-- ֪ͨ���� -->
                                <div class="tab-pane fade" id="notification" role="tabpanel" aria-labelledby="notification-tab">
                                    <div class="card card-body bg-light">
                                        <h5 class="mb-3">֪ͨ����</h5>
                                        
                                        @forelse($notificationSettings as $setting)
                                            <div class="form-group">
                                                <label for="{{ $setting->key }}">{{ $setting->description }}</label>
                                                
                                                @if($setting->key === "payment_notification_email")
                                                    <input type="email" class="form-control" id="{{ $setting->key }}" name="notification.{{ $setting->key }}" value="{{ $setting->value }}">
                                                @elseif(in_array($setting->key, ["payment_success_template", "payment_failed_template"]))
                                                    <textarea class="form-control" id="{{ $setting->key }}" name="notification.{{ $setting->key }}" rows="3">{{ $setting->value }}</textarea>
                                                    <small class="form-text text-muted">
                                                        ���ñ���: {order_id}, {amount}, {currency}, {gateway}, {reason}
                                                    </small>
                                                @else
                                                    <input type="text" class="form-control" id="{{ $setting->key }}" name="notification.{{ $setting->key }}" value="{{ $setting->value }}">
                                                @endif
                                                
                                                @if(!$setting->is_system)
                                                    <div class="mt-1">
                                                        <button type="button" class="btn btn-danger btn-sm delete-setting" data-id="{{ $setting->id }}" data-key="{{ $setting->key }}">
                                                            <i class="fas fa-trash"></i> ɾ��
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="alert alert-info">
                                                ����֪ͨ����
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                                
                                <!-- ��ȫ���� -->
                                <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                                    <div class="card card-body bg-light">
                                        <h5 class="mb-3">��ȫ����</h5>
                                        
                                        @forelse($securitySettings as $setting)
                                            <div class="form-group">
                                                <label for="{{ $setting->key }}">{{ $setting->description }}</label>
                                                
                                                @if(strpos($setting->key, "enable") !== false || strpos($setting->key, "allow") !== false)
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="{{ $setting->key }}" name="security.{{ $setting->key }}" value="true" {{ $setting->value === "true" ? "checked" : "" }}>
                                                        <label class="custom-control-label" for="{{ $setting->key }}">����</label>
                                                    </div>
                                                @else
                                                    <input type="text" class="form-control" id="{{ $setting->key }}" name="security.{{ $setting->key }}" value="{{ $setting->value }}">
                                                @endif
                                                
                                                @if(!$setting->is_system)
                                                    <div class="mt-1">
                                                        <button type="button" class="btn btn-danger btn-sm delete-setting" data-id="{{ $setting->id }}" data-key="{{ $setting->key }}">
                                                            <i class="fas fa-trash"></i> ɾ��
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="alert alert-info">
                                                ���ް�ȫ����
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> ��������
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ɾ��ȷ��ģ̬�� -->
<div class="modal fade" id="deleteSettingModal" tabindex="-1" role="dialog" aria-labelledby="deleteSettingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSettingModalLabel">ȷ��ɾ��</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>ȷ��Ҫɾ������ <strong id="settingKey"></strong> ��</p>
                <p class="text-danger">�˲��������棬�����������</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ȡ��</button>
                <form id="deleteSettingForm" method="POST" action="">
                    @csrf
                    @method("DELETE")
                    <button type="submit" class="btn btn-danger">ȷ��ɾ��</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
    $(function() {
        // ɾ��ȷ��
        $(".delete-setting").click(function() {
            const id = $(this).data("id");
            const key = $(this).data("key");
            
            $("#settingKey").text(key);
            $("#deleteSettingForm").attr("action", `{{ url("admin/payment/settings") }}/${id}`);
            $("#deleteSettingModal").modal("show");
        });
    });
</script>
@endsection
