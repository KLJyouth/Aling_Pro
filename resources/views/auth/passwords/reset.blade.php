@extends("layouts.app")

@section("title", "重置密码")

@section("content")
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">重置密码</h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route("password.update") }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3">
                            <label for="email" class="form-label">电子邮箱</label>
                            <input id="email" type="email" class="form-control @error("email") is-invalid @enderror" name="email" value="{{ $email ?? old("email") }}" required autocomplete="email" autofocus>
                            @error("email")
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">新密码</label>
                            <input id="password" type="password" class="form-control @error("password") is-invalid @enderror" name="password" required autocomplete="new-password">
                            @error("password")
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password-confirm" class="form-label">确认新密码</label>
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                重置密码
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
