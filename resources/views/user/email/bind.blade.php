@extends("layouts.app")

@section("title", "绑定邮箱")

@section("content")
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            @include("user.partials.sidebar")
        </div>
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">绑定邮箱</h5>
                </div>
                <div class="card-body">
                    @if(session("success"))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session("success") }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
                        </div>
                    @endif
                    
                    @if(session("error"))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session("error") }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
                        </div>
                    @endif
                    
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> 为什么需要绑定邮箱？</h5>
                        <p class="mb-0">绑定邮箱可以增强账户安全性，帮助您找回密码，并接收重要通知。</p>
                    </div>
                    
                    <form method="POST" action="{{ route("user.email.bind") }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">电子邮箱</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old("email", $user->email) }}" required>
                            @if($user->email_verified_at)
                                <div class="form-text text-success">
                                    <i class="fas fa-check-circle"></i> 此邮箱已验证
                                </div>
                            @elseif($user->email)
                                <div class="form-text text-warning">
                                    <i class="fas fa-exclamation-circle"></i> 此邮箱未验证，请前往 <a href="{{ route("user.email.verify") }}">验证页面</a>
                                </div>
                            @endif
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">当前密码</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">为了保护您的账户安全，请输入当前密码</div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                {{ $user->email ? "更新邮箱" : "绑定邮箱" }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
