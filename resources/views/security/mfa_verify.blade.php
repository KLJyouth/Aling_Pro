@extends('layouts.app')

@section('title', '多因素认证')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-lock me-2"></i>多因素认证</h4>
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
                        <h5><i class="fas fa-info-circle me-2"></i>需要额外验证</h5>
                        <p>为了保护您的账户安全，请完成多因素认证。</p>
                    </div>
                    
                    <form method="POST" action="{{ route('auth.mfa.verify') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="method" class="form-label">验证方式</label>
                            <select class="form-select @error('method') is-invalid @enderror" id="method" name="method">
                                @foreach ($methods as $method)
                                    <option value="{{ $method['method'] }}" {{ old('method') == $method['method'] ? 'selected' : '' }}>
                                        @if ($method['method'] == 'app')
                                            <i class="fas fa-mobile-alt"></i> 认证应用
                                        @elseif ($method['method'] == 'sms')
                                            <i class="fas fa-sms"></i> 短信验证 
                                            @if (isset($method['metadata']['phone_number']))
                                                ({{ substr($method['metadata']['phone_number'], 0, 3) }}****{{ substr($method['metadata']['phone_number'], -4) }})
                                            @endif
                                        @elseif ($method['method'] == 'email')
                                            <i class="fas fa-envelope"></i> 邮箱验证
                                            @if (isset($method['metadata']['email']))
                                                ({{ substr($method['metadata']['email'], 0, 3) }}***{{ strstr($method['metadata']['email'], '@') }})
                                            @endif
                                        @elseif ($method['method'] == 'fingerprint')
                                            <i class="fas fa-fingerprint"></i> 指纹验证
                                        @endif
                                    </option>
                                @endforeach
                                <option value="recovery">使用恢复代码</option>
                            </select>
                            @error('method')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="code" class="form-label">验证码</label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" required autofocus
                                       placeholder="请输入验证码">
                                <button type="submit" name="send_code" value="1" class="btn btn-outline-secondary">
                                    获取验证码
                                </button>
                            </div>
                            @error('code')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text" id="code-help">
                                如果选择认证应用，请在应用中查看验证码。
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check-circle me-2"></i>验证
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light">
                    <div class="text-center">
                        <p class="mb-0">
                            <i class="fas fa-question-circle me-1"></i>
                            无法访问您的验证方式？请联系客服：<a href="mailto:support@example.com">support@example.com</a>
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
        const methodSelect = document.getElementById('method');
        const codeHelp = document.getElementById('code-help');
        const sendCodeBtn = document.querySelector('button[name="send_code"]');
        
        function updateHelp() {
            const method = methodSelect.value;
            
            if (method === 'app') {
                codeHelp.textContent = '请在认证应用中查看验证码。';
                sendCodeBtn.style.display = 'none';
            } else if (method === 'sms') {
                codeHelp.textContent = '验证码将发送到您的手机。';
                sendCodeBtn.style.display = '';
            } else if (method === 'email') {
                codeHelp.textContent = '验证码将发送到您的邮箱。';
                sendCodeBtn.style.display = '';
            } else if (method === 'fingerprint') {
                codeHelp.textContent = '请在设备上完成指纹验证。';
                sendCodeBtn.style.display = 'none';
            } else if (method === 'recovery') {
                codeHelp.textContent = '请输入恢复代码。';
                sendCodeBtn.style.display = 'none';
            }
        }
        
        methodSelect.addEventListener('change', updateHelp);
        updateHelp();
    });
</script>
@endpush
@endsection
