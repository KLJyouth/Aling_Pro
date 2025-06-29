@extends('layouts.app')

@section('title', '安全验证')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>安全验证</h4>
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
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>检测到异常活动</h5>
                        <p>为了保护您的账户安全，我们需要验证您的身份。请完成以下验证步骤。</p>
                        
                        @if (!empty($security_result))
                            <div class="mt-2">
                                <p><strong>风险评分：</strong> {{ $security_result['risk_score'] ?? 0 }}/100</p>
                                
                                @if (isset($security_result['checks']['geolocation']['info']['geo']))
                                    <p><strong>检测到的位置：</strong> 
                                        {{ $security_result['checks']['geolocation']['info']['geo']['city'] ?? ' ' }}
                                        {{ $security_result['checks']['geolocation']['info']['geo']['region'] ?? ' ' }}
                                        {{ $security_result['checks']['geolocation']['info']['geo']['country'] ?? ' ' }}
                                    </p>
                                @endif
                                
                                @if (isset($security_result['checks']['device']['info']['device_type']))
                                    <p><strong>设备类型：</strong> 
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
                            <label for="verification_code" class="form-label">验证码</label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('verification_code') is-invalid @enderror" 
                                       id="verification_code" name="verification_code" required autofocus
                                       placeholder="请输入验证码">
                                <button type="submit" name="send_code" value="1" class="btn btn-outline-secondary">
                                    获取验证码
                                </button>
                            </div>
                            @error('verification_code')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">
                                验证码将发送到您的手机或邮箱。
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
                            <i class="fas fa-info-circle me-1"></i>
                            如需帮助，请联系客服：<a href="mailto:support@example.com">support@example.com</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
