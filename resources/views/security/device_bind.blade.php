@extends('layouts.app')

@section('title', '�豸��')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-mobile-alt me-2"></i>�豸��</h4>
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
                    
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle me-2"></i>��Ҫ���豸</h5>
                        <p>Ϊ������˻���ȫ�ԣ��뽫�����豸���˻��󶨡�</p>
                    </div>
                    
                    <div class="text-center mb-4">
                        <div class="qr-code-container mb-3">
                            <img src="{{ $binding_data['qr_code'] }}" alt="�豸�󶨶�ά��" class="img-fluid">
                        </div>
                        <p class="fw-bold">��ʹ���ƶ��豸ɨ���Ϸ���ά��</p>
                        <p class="text-muted">��ά�뽫�� <span id="countdown">10:00</span> ��ʧЧ</p>
                    </div>
                    
                    <div class="mb-4">
                        <h5>����ʹ�ð����ӣ�</h5>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $binding_data['url'] }}" readonly>
                            <button class="btn btn-outline-secondary" type="button" id="copy-link">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <div class="form-text">
                            �������ӷ��͵�����Ҫ�󶨵��豸�ϡ�
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>��Ҫ��ʾ</h5>
                        <ul class="mb-0">
                            <li>���豸������ֻ�����Ѱ󶨵��豸�ϵ�¼��</li>
                            <li>�����Ҫ�����豸�ϵ�¼������Ҫ�Ƚ����豸�󶨡�</li>
                            <li>�������԰� 5 ���豸��</li>
                        </ul>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="text-center">
                        <p class="mb-0">
                            <i class="fas fa-question-circle me-1"></i>
                            �������⣿����ϵ�ͷ���<a href="mailto:support@example.com">support@example.com</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ��������
        document.getElementById('copy-link').addEventListener('click', function() {
            const linkInput = this.previousElementSibling;
            linkInput.select();
            document.execCommand('copy');
            
            // ��ʾ���Ƴɹ���ʾ
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(() => {
                this.innerHTML = originalHTML;
            }, 2000);
        });
        
        // ����ʱ
        const countdownEl = document.getElementById('countdown');
        const expiresAt = {{ $binding_data['expires_at'] }} * 1000;
        
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = expiresAt - now;
            
            if (distance <= 0) {
                countdownEl.textContent = '�ѹ���';
                location.reload();
                return;
            }
            
            const minutes = Math.floor(distance / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            countdownEl.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
        
        // ��ѯ����豸�Ƿ��Ѱ�
        function checkBindingStatus() {
            fetch(`/api/device/binding-status?code={{ $binding_data['binding_code'] }}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'bound') {
                        window.location.href = data.redirect_url || '/';
                    }
                });
        }
        
        setInterval(checkBindingStatus, 5000);
    });
</script>
@endpush
@endsection
