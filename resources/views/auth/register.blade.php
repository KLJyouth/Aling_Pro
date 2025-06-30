@extends("layouts.app")

@section("title", "注册")

@section("content")
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">用户注册</h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route("register") }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">姓名</label>
                            <input id="name" type="text" class="form-control @error("name") is-invalid @enderror" name="name" value="{{ old("name") }}" required autocomplete="name" autofocus>
                            @error("name")
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">电子邮箱</label>
                            <input id="email" type="email" class="form-control @error("email") is-invalid @enderror" name="email" value="{{ old("email") }}" required autocomplete="email">
                            @error("email")
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">密码</label>
                            <input id="password" type="password" class="form-control @error("password") is-invalid @enderror" name="password" required autocomplete="new-password">
                            @error("password")
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password-confirm" class="form-label">确认密码</label>
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                        </div>

                        @if (session("referral_code") || old("referral_code"))
                            <div class="mb-3">
                                <label for="referral_code" class="form-label">推荐码</label>
                                <input id="referral_code" type="text" class="form-control" name="referral_code" value="{{ session("referral_code") ?: old("referral_code") }}" readonly>
                                <div class="form-text">您正在使用推荐码注册，注册成功后双方都将获得奖励。</div>
                            </div>
                        @else
                            <div class="mb-3">
                                <label for="referral_code" class="form-label">推荐码（可选）</label>
                                <input id="referral_code" type="text" class="form-control @error("referral_code") is-invalid @enderror" name="referral_code" value="{{ old("referral_code") }}">
                                <div class="form-text">如果您有推荐码，请在此输入。</div>
                                @error("referral_code")
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        @endif

                        <div class="mb-3 form-check">
                            <input class="form-check-input @error("terms") is-invalid @enderror" type="checkbox" name="terms" id="terms" {{ old("terms") ? "checked" : "" }} required>
                            <label class="form-check-label" for="terms">
                                我已阅读并同意 <a href="{{ route("terms") }}" target="_blank">服务条款</a> 和 <a href="{{ route("privacy") }}" target="_blank">隐私政策</a>
                            </label>
                            @error("terms")
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                注册
                            </button>
                        </div>

                        <div class="mt-3 text-center">
                            <span>已有账号？</span>
                            <a class="text-decoration-none" href="{{ route("login") }}">
                                立即登录
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="mt-4 text-center">
                <p>或者使用以下方式注册</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route("login.social", ["provider" => "google"]) }}" class="btn btn-outline-danger">
                        <i class="fab fa-google me-2"></i>Google
                    </a>
                    <a href="{{ route("login.social", ["provider" => "github"]) }}" class="btn btn-outline-dark">
                        <i class="fab fa-github me-2"></i>GitHub
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
