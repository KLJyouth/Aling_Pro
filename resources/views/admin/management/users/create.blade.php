@extends('admin.layouts.app')

@section('title', '添加用户')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">添加用户</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form role="form" method="POST" action="{{ route('admin.management.users.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">用户名 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="请输入用户名" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="email">邮箱 <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="请输入邮箱" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="password">密码 <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="请输入密码" required>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">确认密码 <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="请再次输入密码" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">手机号码</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" placeholder="请输入手机号码" value="{{ old('phone') }}">
                            @error('phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>角色分配</label>
                            <div class="select2-purple">
                                <select class="select2" name="roles[]" multiple="multiple" data-placeholder="选择角色" style="width: 100%;">
                                    @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ in_array($role->id, old('roles', [])) ? 'selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('roles')
                                <span class="text-danger">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="status">状态</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>正常</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>禁用</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" id="send_welcome_email" name="send_welcome_email" value="1" {{ old('send_welcome_email') ? 'checked' : '' }}>
                                <label for="send_welcome_email" class="custom-control-label">发送欢迎邮件</label>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">提交</button>
                        <a href="{{ route('admin.management.users.index') }}" class="btn btn-default">取消</a>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        //Initialize Select2 Elements
        $('.select2').select2();
    });
</script>
@endsection 