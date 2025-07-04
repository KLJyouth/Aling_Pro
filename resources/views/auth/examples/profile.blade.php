@extends("layouts.app")

@section("title", "个人资料")

@section("content")
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">个人资料</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route("profile.update") }}">
                        @csrf
                        @method("PUT")
                        
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">姓名</label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error("name") is-invalid @enderror" name="name" value="{{ old("name", auth()->user()->name) }}" required autocomplete="name" autofocus>
                                @error("name")
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">邮箱</label>
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error("email") is-invalid @enderror" name="email" value="{{ old("email", auth()->user()->email) }}" required autocomplete="email">
                                @error("email")
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    更新资料
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">修改密码</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route("profile.password") }}">
                        @csrf
                        @method("PUT")
                        
                        <div class="form-group row">
                            <label for="current_password" class="col-md-4 col-form-label text-md-right">当前密码</label>
                            <div class="col-md-6">
                                <input id="current_password" type="password" class="form-control @error("current_password") is-invalid @enderror" name="current_password" required autocomplete="current-password">
                                @error("current_password")
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">新密码</label>
                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error("password") is-invalid @enderror" name="password" required autocomplete="new-password">
                                @error("password")
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="password_confirmation" class="col-md-4 col-form-label text-md-right">确认密码</label>
                            <div class="col-md-6">
                                <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>
                        
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    更新密码
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- 引入OAuth账号关联组件 -->
            @include("auth.partials.oauth-account-links")
        </div>
    </div>
</div>
@endsection
