@extends('layouts.app')

@section('title', '设备绑定')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-mobile-alt me-2"></i>设备绑定</h4>
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
                        <h5><i class="fas fa-info-circle me-2"></i>需要绑定设备</h5>
                        <p>为了提高账户安全性，请将您的设备与账户绑定。</p>
                    </div>
                    
                    <div class="text-center mb-4">
                        <div class="qr-code-container mb-3">
                            <img src="{{ $binding_data['qr_code'] }}" alt="设备绑定二维码" class="img-fluid">
                        </div>
                        <p class="fw-bold">请使用移动设备扫描上方二维码</p>
                        <p class="text-muted">二维码将在 <span id="countdown">10:00</span> 后失效</p>
                    </div>
                    
                    <div class="mb-4">
                        <h5>或者使用绑定链接：</h5>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $binding_data['url'] }}" readonly>
                            <button class="btn btn-outline-secondary" type="button" id="copy-link">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <div class="form-text">
                            将此链接发送到您想要绑定的设备上。
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>重要提示</h5>
                        <ul class="mb-0">
                            <li>绑定设备后，您将只能在已绑定的设备上登录。</li>
                            <li>如果需要在新设备上登录，您需要先进行设备绑定。</li>
                            <li>您最多可以绑定 5 个设备。</li>
                        </ul>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="text-center">
                        <p class="mb-0">
                            <i class="fas fa-question-circle me-1"></i>
                            遇到问题？请联系客服：<a href="mailto:support@example.com">support@example.com</a>
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
        // 复制链接
        document.getElementById('copy-link').addEventListener('click', function() {
            const linkInput = this.previousElementSibling;
            linkInput.select();
            document.execCommand('copy');
            
            // 显示复制成功提示
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(() => {
                this.innerHTML = originalHTML;
            }, 2000);
        });
        
        // 倒计时
        const countdownEl = document.getElementById('countdown');
        const expiresAt = {{ $binding_data['expires_at'] }} * 1000;
        
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = expiresAt - now;
            
            if (distance <= 0) {
                countdownEl.textContent = '已过期';
                location.reload();
                return;
            }
            
            const minutes = Math.floor(distance / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            countdownEl.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
        
        // 轮询检查设备是否已绑定
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
