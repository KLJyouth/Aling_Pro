@extends('layouts.app')

@section('title', '��ȫ����')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-3">
            @include('user.sidebar')
        </div>
        <div class="col-lg-9">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>��ȫ����</h4>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    <h5 class="border-bottom pb-2 mb-3">��������֤</h5>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">�����õ���֤��ʽ</h6>
                                <p class="text-muted mb-0">��������֤��������˻���ȫ��</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMfaModal">
                                <i class="fas fa-plus me-1"></i>��ӷ�ʽ
                            </button>
                        </div>
                        
                        @if (count($mfa_methods) > 0)
                            <div class="list-group">
                                @foreach ($mfa_methods as $method)
                                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="d-flex align-items-center">
                                                <i class="fas {{ $method['method'] == 'app' ? 'fa-mobile-alt' : ($method['method'] == 'sms' ? 'fa-sms' : ($method['method'] == 'email' ? 'fa-envelope' : 'fa-fingerprint')) }} me-2"></i>
                                                <strong>
                                                    @if ($method['method'] == 'app')
                                                        ��֤Ӧ��
                                                    @elseif ($method['method'] == 'sms')
                                                        ������֤
                                                        @if (isset($method['metadata']['phone_number']))
                                                            ({{ substr($method['metadata']['phone_number'], 0, 3) }}****{{ substr($method['metadata']['phone_number'], -4) }})
                                                        @endif
                                                    @elseif ($method['method'] == 'email')
                                                        ������֤
                                                        @if (isset($method['metadata']['email']))
                                                            ({{ substr($method['metadata']['email'], 0, 3) }}***{{ strstr($method['metadata']['email'], '@') }})
                                                        @endif
                                                    @elseif ($method['method'] == 'fingerprint')
                                                        ָ����֤
                                                    @endif
                                                </strong>
                                                @if ($method['is_primary'])
                                                    <span class="badge bg-success ms-2">��Ҫ</span>
                                                @endif
                                            </div>
                                            <small class="text-muted">����� {{ date('Y-m-d', strtotime($method['created_at'])) }}</small>
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            @if (!$method['is_primary'])
                                                <form action="{{ route('auth.mfa.primary') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="method_id" value="{{ $method['id'] }}">
                                                    <button type="submit" class="btn btn-outline-primary">
                                                        ��Ϊ��Ҫ
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('auth.mfa.disable') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="method_id" value="{{ $method['id'] }}">
                                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('ȷ��Ҫ���ô���֤��ʽ��')">
                                                    ����
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="mt-3">
                                <a href="{{ route('auth.mfa.recovery-codes') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-key me-1"></i>���ɻָ�����
                                </a>
                                <small class="text-muted ms-2">�ָ���������޷�ʹ��������֤��ʽʱʹ��</small>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>����δ���ö�������֤��Ϊ������˻���ȫ�ԣ�������������һ����֤��ʽ��
                            </div>
                        @endif
                    </div>
                    
                    <h5 class="border-bottom pb-2 mb-3 mt-5">�Ѱ��豸</h5>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">�Ѱ󶨵��豸</h6>
                                <p class="text-muted mb-0">ֻ���Ѱ󶨵��豸���ܵ�¼�����˻�</p>
                            </div>
                            <a href="{{ route('auth.device.bind') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>�����豸
                            </a>
                        </div>
                        
                        @if (count($devices) > 0)
                            <div class="list-group">
                                @foreach ($devices as $device)
                                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="d-flex align-items-center">
                                                <i class="fas {{ $device->device_type == 'mobile' ? 'fa-mobile-alt' : ($device->device_type == 'tablet' ? 'fa-tablet-alt' : 'fa-laptop') }} me-2"></i>
                                                <strong>{{ $device->device_name ?: ($device->device_model ?: $device->device_type) }}</strong>
                                                @if ($device->last_active_at && $device->last_active_at->isToday())
                                                    <span class="badge bg-success ms-2">��ǰ�豸</span>
                                                @endif
                                            </div>
                                            <small class="text-muted">
                                                {{ $device->os_type }} {{ $device->os_version }}
                                                 ���: {{ $device->last_active_at ? $device->last_active_at->diffForHumans() : '��δ' }}
                                            </small>
                                        </div>
                                        <form action="{{ route('auth.device.unbind') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="device_id" value="{{ $device->id }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('ȷ��Ҫ�����豸��')">
                                                ���
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>����δ���κ��豸��Ϊ������˻���ȫ�ԣ�����������õ��豸��
                            </div>
                        @endif
                    </div>
                    
                    <h5 class="border-bottom pb-2 mb-3 mt-5">���밲ȫ</h5>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">�޸�����</h6>
                                <p class="text-muted mb-0">���ڸ��������������˻���ȫ��</p>
                            </div>
                            <a href="{{ route('password.change') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-key me-1"></i>�޸�����
                            </a>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>����ʹ�ð�����Сд��ĸ�����ֺ�������ŵ�ǿ���룬�����ڸ�����
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ��Ӷ�������֤��ʽģ̬�� -->
<div class="modal fade" id="addMfaModal" tabindex="-1" aria-labelledby="addMfaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMfaModalLabel">��Ӷ�������֤��ʽ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('user.security.mfa.enable') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="mfa_method" class="form-label">ѡ����֤��ʽ</label>
                        <select class="form-select" id="mfa_method" name="method" required>
                            <option value="app">��֤Ӧ�� (�� Google Authenticator)</option>
                            <option value="sms">������֤</option>
                            <option value="email">������֤</option>
                            <option value="fingerprint">ָ����֤ (��Ҫ֧�ֵ��豸)</option>
                        </select>
                        <div class="form-text mt-2">
                            <div id="app_help" class="method-help">
                                <p>��֤Ӧ�����ȫ�Ķ�������֤��ʽ����ʹ��û���������ӵ������Ҳ��ʹ�á�</p>
                                <p>�Ƽ�Ӧ�ã�Google Authenticator��Microsoft Authenticator��Authy �ȡ�</p>
                            </div>
                            <div id="sms_help" class="method-help d-none">
                                <p>������֤�Ὣ��֤�뷢�͵������ֻ����롣</p>
                                <p>��ȷ�������ڸ��������а����ֻ����롣</p>
                            </div>
                            <div id="email_help" class="method-help d-none">
                                <p>������֤�Ὣ��֤�뷢�͵����ĵ������䡣</p>
                                <p>��ȷ�����������ַ��ȷ�ҿɷ��ʡ�</p>
                            </div>
                            <div id="fingerprint_help" class="method-help d-none">
                                <p>ָ����֤��Ҫ֧��ָ��ʶ����豸��</p>
                                <p>��ȷ�������豸������ָ��ʶ���ܡ�</p>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>���
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const methodSelect = document.getElementById('mfa_method');
        const methodHelps = document.querySelectorAll('.method-help');
        
        methodSelect.addEventListener('change', function() {
            const method = this.value;
            
            methodHelps.forEach(help => {
                help.classList.add('d-none');
            });
            
            document.getElementById(`${method}_help`).classList.remove('d-none');
        });
    });
</script>
@endpush
@endsection
