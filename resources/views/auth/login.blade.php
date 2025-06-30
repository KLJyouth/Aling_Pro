@extends("layouts.app")

@section("title", "��¼")

@section("content")
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">�û���¼</h4>
                </div>
                <div class="card-body p-4">
                    @if (session("status"))
                        <div class="alert alert-success" role="alert">
                            {{ session("status") }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route("login") }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">��������</label>
                            <input id="email" type="email" class="form-control @error("email") is-invalid @enderror" name="email" value="{{ old("email") }}" required autocomplete="email" autofocus>
                            @error("email")
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">����</label>
                            <input id="password" type="password" class="form-control @error("password") is-invalid @enderror" name="password" required autocomplete="current-password">
                            @error("password")
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old("remember") ? "checked" : "" }}>
                            <label class="form-check-label" for="remember">
                                ��ס��
                            </label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                ��¼
                            </button>
                        </div>

                        <div class="mt-3 text-center">
                            @if (Route::has("password.request"))
                                <a class="text-decoration-none" href="{{ route("password.request") }}">
                                    �������룿
                                </a>
                            @endif
                            <span class="mx-2">|</span>
                            <a class="text-decoration-none" href="{{ route("register") }}">
                                û���˺ţ�����ע��
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="mt-4 text-center">
                <p>����ʹ�����·�ʽ��¼</p>
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
