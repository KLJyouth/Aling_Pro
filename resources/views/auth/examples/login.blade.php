@extends("layouts.auth")

@section("title", "登录")

@section("content")
<div class="login-box">
    <div class="login-logo">
        <a href="{{ url("/") }}"><b>Aling</b>AI</a>
    </div>
    
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">登录您的账号</p>

            <form action="{{ route("login") }}" method="post">
                @csrf
                
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control @error("email") is-invalid @enderror" placeholder="邮箱" value="{{ old("email") }}" required autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                    @error("email")
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control @error("password") is-invalid @enderror" placeholder="密码" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                    @error("password")
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember" {{ old("remember") ? "checked" : "" }}>
                            <label for="remember">
                                记住我
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">登录</button>
                    </div>
                </div>
            </form>

            <!-- 引入OAuth登录按钮 -->
            @include("auth.partials.oauth-buttons")

            <p class="mb-1">
                <a href="{{ route("password.request") }}">忘记密码</a>
            </p>
            <p class="mb-0">
                <a href="{{ route("register") }}" class="text-center">注册新账号</a>
            </p>
        </div>
    </div>
</div>
@endsection
