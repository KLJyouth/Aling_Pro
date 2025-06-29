@extends('layouts.app')

@section('title', '֧����ȫ��֤')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-lock me-2"></i>֧����ȫ��֤</h4>
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
                        <h5><i class="fas fa-info-circle me-2"></i>��Ҫ������֤</h5>
                        <p>Ϊ�˱��������˻����ʽ�ȫ������ɶ���İ�ȫ��֤��</p>
                    </div>
                    
                    <div class="order-summary mb-4">
                        <h5 class="border-bottom pb-2">������Ϣ</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>������ţ�</strong> {{ $order->order_no }}</p>
                                <p><strong>��Ʒ���ƣ�</strong> {{ $order->product_name }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>������</strong> {{ number_format($order->amount, 2) }}</p>
                                <p><strong>֧����ʽ��</strong> 
                                    @if ($payment_method == 'alipay')
                                        ֧����
                                    @elseif ($payment_method == 'wechat')
                                        ΢��֧��
                                    @elseif ($payment_method == 'bank_card')
                                        ���п�
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
                            <label for="method" class="form-label">��֤��ʽ</label>
                            <select class="form-select @error('method') is-invalid @enderror" id="method" name="method">
                                @foreach ($mfa_methods as $method)
                                    <option value="{{ $method['method'] }}" {{ old('method') == $method['method'] ? 'selected' : '' }}>
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
                            <label for="code" class="form-label">��֤��</label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" required autofocus
                                       placeholder="��������֤��">
                                <button type="submit" name="send_code" value="1" class="btn btn-outline-secondary">
                                    ��ȡ��֤��
                                </button>
                            </div>
                            @error('code')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text" id="code-help">
                                ���ѡ����֤Ӧ�ã�����Ӧ���в鿴��֤�롣
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check-circle me-2"></i>��֤������֧��
                            </button>
                            <a href="{{ route('user.orders') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times-circle me-2"></i>ȡ��֧��
                            </a>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light">
                    <div class="text-center">
                        <p class="mb-0">
                            <i class="fas fa-shield-alt me-1"></i>
                            ��ȫ֧���������ΰ�ȫ��ܱ���
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
                codeHelp.textContent = '������֤Ӧ���в鿴��֤�롣';
                sendCodeBtn.style.display = 'none';
            } else if (method === 'sms') {
                codeHelp.textContent = '��֤�뽫���͵������ֻ���';
                sendCodeBtn.style.display = '';
            } else if (method === 'email') {
                codeHelp.textContent = '��֤�뽫���͵��������䡣';
                sendCodeBtn.style.display = '';
            } else if (method === 'fingerprint') {
                codeHelp.textContent = '�����豸�����ָ����֤��';
                sendCodeBtn.style.display = 'none';
            }
        }
        
        methodSelect.addEventListener('change', updateHelp);
        updateHelp();
    });
</script>
@endpush
@endsection
