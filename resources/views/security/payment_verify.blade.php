@extends('layouts.app')

@section('title', '支付安全验证')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-lock me-2"></i>支付安全验证</h4>
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
                        <p>为了保护您的账户和资金安全，请完成额外的安全验证。</p>
                    </div>
                    
                    <div class="order-summary mb-4">
                        <h5 class="border-bottom pb-2">订单信息</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>订单编号：</strong> {{ $order->order_no }}</p>
                                <p><strong>商品名称：</strong> {{ $order->product_name }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>订单金额：</strong> {{ number_format($order->amount, 2) }}</p>
                                <p><strong>支付方式：</strong> 
                                    @if ($payment_method == 'alipay')
                                        支付宝
                                    @elseif ($payment_method == 'wechat')
                                        微信支付
                                    @elseif ($payment_method == 'bank_card')
                                        银行卡
                                    @else
                                        {{ $payment_method }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" action="{{ route('payment.security.verify') }}">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <input type="hidden" name="payment_method" value="{{ $payment_method }}">
                        
                        <div class="mb-4">
                            <label for="method" class="form-label">验证方式</label>
                            <select class="form-select @error('method') is-invalid @enderror" id="method" name="method">
                                @foreach ($mfa_methods as $method)
                                    <option value="{{ $method['method'] }}" {{ old('method') == $method['method'] ? 'selected' : '' }}>
                                        @if ($method['method'] == 'app')
                                            认证应用
                                        @elseif ($method['method'] == 'sms')
                                            短信验证 
                                            @if (isset($method['metadata']['phone_number']))
                                                ({{ substr($method['metadata']['phone_number'], 0, 3) }}****{{ substr($method['metadata']['phone_number'], -4) }})
                                            @endif
                                        @elseif ($method['method'] == 'email')
                                            邮箱验证
                                            @if (isset($method['metadata']['email']))
                                                ({{ substr($method['metadata']['email'], 0, 3) }}***{{ strstr($method['metadata']['email'], '@') }})
                                            @endif
                                        @elseif ($method['method'] == 'fingerprint')
                                            指纹验证
                                        @endif
                                    </option>
                                @endforeach
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
                                <i class="fas fa-check-circle me-2"></i>验证并继续支付
                            </button>
                            <a href="{{ route('user.orders') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times-circle me-2"></i>取消支付
                            </a>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light">
                    <div class="text-center">
                        <p class="mb-0">
                            <i class="fas fa-shield-alt me-1"></i>
                            安全支付由零信任安全框架保障
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
            }
        }
        
        methodSelect.addEventListener('change', updateHelp);
        updateHelp();
    });
</script>
@endpush
@endsection
