@extends('layouts.app')

@section('title', '安全设置')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-3">
            @include('user.sidebar')
        </div>
        <div class="col-lg-9">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>安全设置</h4>
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
                    
                    <h5 class="border-bottom pb-2 mb-3">多因素认证</h5>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">已启用的验证方式</h6>
                                <p class="text-muted mb-0">多因素认证可以提高账户安全性</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMfaModal">
                                <i class="fas fa-plus me-1"></i>添加方式
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
                                                </strong>
                                                @if ($method['is_primary'])
                                                    <span class="badge bg-success ms-2">主要</span>
                                                @endif
                                            </div>
                                            <small class="text-muted">添加于 {{ date('Y-m-d', strtotime($method['created_at'])) }}</small>
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            @if (!$method['is_primary'])
                                                <form action="{{ route('auth.mfa.primary') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="method_id" value="{{ $method['id'] }}">
                                                    <button type="submit" class="btn btn-outline-primary">
                                                        设为主要
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('auth.mfa.disable') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="method_id" value="{{ $method['id'] }}">
                                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('确定要禁用此验证方式吗？')">
                                                    禁用
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="mt-3">
                                <a href="{{ route('auth.mfa.recovery-codes') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-key me-1"></i>生成恢复代码
                                </a>
                                <small class="text-muted ms-2">恢复代码可在无法使用其他验证方式时使用</small>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>您尚未启用多因素认证。为了提高账户安全性，建议启用至少一种验证方式。
                            </div>
                        @endif
                    </div>
                    
                    <h5 class="border-bottom pb-2 mb-3 mt-5">已绑定设备</h5>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">已绑定的设备</h6>
                                <p class="text-muted mb-0">只有已绑定的设备才能登录您的账户</p>
                            </div>
                            <a href="{{ route('auth.device.bind') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>绑定新设备
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
                                                    <span class="badge bg-success ms-2">当前设备</span>
                                                @endif
                                            </div>
                                            <small class="text-muted">
                                                {{ $device->os_type }} {{ $device->os_version }}
                                                 最后活动: {{ $device->last_active_at ? $device->last_active_at->diffForHumans() : '从未' }}
                                            </small>
                                        </div>
                                        <form action="{{ route('auth.device.unbind') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="device_id" value="{{ $device->id }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('确定要解绑此设备吗？')">
                                                解绑
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>您尚未绑定任何设备。为了提高账户安全性，建议绑定您常用的设备。
                            </div>
                        @endif
                    </div>
                    
                    <h5 class="border-bottom pb-2 mb-3 mt-5">密码安全</h5>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">修改密码</h6>
                                <p class="text-muted mb-0">定期更换密码可以提高账户安全性</p>
                            </div>
                            <a href="{{ route('password.change') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-key me-1"></i>修改密码
                            </a>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>建议使用包含大小写字母、数字和特殊符号的强密码，并定期更换。
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 添加多因素认证方式模态框 -->
<div class="modal fade" id="addMfaModal" tabindex="-1" aria-labelledby="addMfaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMfaModalLabel">添加多因素认证方式</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('user.security.mfa.enable') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="mfa_method" class="form-label">选择验证方式</label>
                        <select class="form-select" id="mfa_method" name="method" required>
                            <option value="app">认证应用 (如 Google Authenticator)</option>
                            <option value="sms">短信验证</option>
                            <option value="email">邮箱验证</option>
                            <option value="fingerprint">指纹验证 (需要支持的设备)</option>
                        </select>
                        <div class="form-text mt-2">
                            <div id="app_help" class="method-help">
                                <p>认证应用是最安全的多因素认证方式，即使在没有网络连接的情况下也能使用。</p>
                                <p>推荐应用：Google Authenticator、Microsoft Authenticator、Authy 等。</p>
                            </div>
                            <div id="sms_help" class="method-help d-none">
                                <p>短信验证会将验证码发送到您的手机号码。</p>
                                <p>请确保您已在个人资料中绑定了手机号码。</p>
                            </div>
                            <div id="email_help" class="method-help d-none">
                                <p>邮箱验证会将验证码发送到您的电子邮箱。</p>
                                <p>请确保您的邮箱地址正确且可访问。</p>
                            </div>
                            <div id="fingerprint_help" class="method-help d-none">
                                <p>指纹验证需要支持指纹识别的设备。</p>
                                <p>请确保您的设备已设置指纹识别功能。</p>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>添加
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
