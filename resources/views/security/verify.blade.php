@extends('layouts.app')

@section('title', '��ȫ��֤')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>��ȫ��֤</h4>
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
                    
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>��⵽�쳣�</h5>
                        <p>Ϊ�˱��������˻���ȫ��������Ҫ��֤������ݡ������������֤���衣</p>
                        
                        @if (!empty($security_result))
                            <div class="mt-2">
                                <p><strong>�������֣�</strong> {{ $security_result['risk_score'] ?? 0 }}/100</p>
                                
                                @if (isset($security_result['checks']['geolocation']['info']['geo']))
                                    <p><strong>��⵽��λ�ã�</strong> 
                                        {{ $security_result['checks']['geolocation']['info']['geo']['city'] ?? ' ' }}
                                        {{ $security_result['checks']['geolocation']['info']['geo']['region'] ?? ' ' }}
                                        {{ $security_result['checks']['geolocation']['info']['geo']['country'] ?? ' ' }}
                                    </p>
                                @endif
                                
                                @if (isset($security_result['checks']['device']['info']['device_type']))
                                    <p><strong>�豸���ͣ�</strong> 
                                        {{ $security_result['checks']['device']['info']['device_type'] ?? ' ' }}
                                        {{ $security_result['checks']['device']['info']['platform'] ?? ' ' }}
                                        {{ $security_result['checks']['device']['info']['platform_version'] ?? ' ' }}
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                    
                    <form method="POST" action="{{ route('security.verify') }}">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ $redirect_to ?? route('home') }}">
                        
                        <div class="mb-4">
                            <label for="verification_code" class="form-label">��֤��</label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('verification_code') is-invalid @enderror" 
                                       id="verification_code" name="verification_code" required autofocus
                                       placeholder="��������֤��">
                                <button type="submit" name="send_code" value="1" class="btn btn-outline-secondary">
                                    ��ȡ��֤��
                                </button>
                            </div>
                            @error('verification_code')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">
                                ��֤�뽫���͵������ֻ������䡣
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
                            <i class="fas fa-info-circle me-1"></i>
                            �������������ϵ�ͷ���<a href="mailto:support@example.com">support@example.com</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
