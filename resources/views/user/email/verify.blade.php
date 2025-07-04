@extends("layouts.app")

@section("title", "验证邮箱")

@section("content")
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            @include("user.partials.sidebar")
        </div>
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">验证邮箱</h5>
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
                        <h5><i class="fas fa-envelope"></i> 验证您的邮箱</h5>
                        <p>我们已向 <strong>{{ $user->email }}</strong> 发送了一封包含验证码的邮件。</p>
                        <p class="mb-0">请查收邮件并在下方输入验证码，或点击邮件中的验证链接完成验证。</p>
                    </div>
                    
                    <form method="POST" action="{{ route("user.email.verify") }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="code" class="form-label">验证码</label>
                            <input type="text" class="form-control form-control-lg text-center" id="code" name="code" maxlength="6" style="letter-spacing: 0.5em;" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                验证邮箱
                            </button>
                            <a href="{{ route("user.email.resend") }}" class="btn btn-outline-secondary">
                                重新发送验证邮件
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
