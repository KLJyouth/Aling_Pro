@extends('layouts.app')

@section('title', '��������֤')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-lock me-2"></i>��������֤</h4>
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
                        <p>Ϊ�˱��������˻���ȫ������ɶ�������֤��</p>
                    </div>
                    
                    <form method="POST" action="{{ route('auth.mfa.verify') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="method" class="form-label">��֤��ʽ</label>
                            <select class="form-select @error('method') is-invalid @enderror" id="method" name="method">
                                @foreach ($methods as $method)
                                    <option value="{{ $method['method'] }}" {{ old('method') == $method['method'] ? 'selected' : '' }}>
                                        @if ($method['method'] == 'app')
                                            <i class="fas fa-mobile-alt"></i> ��֤Ӧ��
                                        @elseif ($method['method'] == 'sms')
                                            <i class="fas fa-sms"></i> ������֤ 
                                            @if (isset($method['metadata']['phone_number']))
                                                ({{ substr($method['metadata']['phone_number'], 0, 3) }}****{{ substr($method['metadata']['phone_number'], -4) }})
                                            @endif
                                        @elseif ($method['method'] == 'email')
                                            <i class="fas fa-envelope"></i> ������֤
                                            @if (isset($method['metadata']['email']))
                                                ({{ substr($method['metadata']['email'], 0, 3) }}***{{ strstr($method['metadata']['email'], '@') }})
                                            @endif
                                        @elseif ($method['method'] == 'fingerprint')
                                            <i class="fas fa-fingerprint"></i> ָ����֤
                                        @endif
                                    </option>
                                @endforeach
                                <option value="recovery">ʹ�ûָ�����</option>
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
                                <i class="fas fa-check-circle me-2"></i>��֤
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light">
                    <div class="text-center">
                        <p class="mb-0">
                            <i class="fas fa-question-circle me-1"></i>
                            �޷�����������֤��ʽ������ϵ�ͷ���<a href="mailto:support@example.com">support@example.com</a>
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
            } else if (method === 'recovery') {
                codeHelp.textContent = '������ָ����롣';
                sendCodeBtn.style.display = 'none';
            }
        }
        
        methodSelect.addEventListener('change', updateHelp);
        updateHelp();
    });
</script>
@endpush
@endsection
