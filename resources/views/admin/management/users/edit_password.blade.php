@extends('admin.layouts.app')

@section('title', '修改密码')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">修改用户密码</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form role="form" method="POST" action="{{ route('admin.management.users.update_password', $user->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label>用户名</label>
                            <input type="text" class="form-control" value="{{ $user->name }}" disabled>
                        </div>
                        <div class="form-group">
                            <label>邮箱</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                        </div>
                        <div class="form-group">
                            <label for="password">新密码 <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="请输入新密码" required>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">密码必须至少包含8个字符，并且包含字母和数字</small>
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">确认新密码 <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="请再次输入新密码" required>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" id="send_password_notification" name="send_password_notification" value="1" {{ old('send_password_notification', '1') ? 'checked' : '' }}>
                                <label for="send_password_notification" class="custom-control-label">发送密码修改通知邮件</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" id="force_logout" name="force_logout" value="1" {{ old('force_logout', '1') ? 'checked' : '' }}>
                                <label for="force_logout" class="custom-control-label">强制用户退出登录</label>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">保存</button>
                        <a href="{{ route('admin.management.users.show', $user->id) }}" class="btn btn-default">取消</a>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">密码安全提示</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> 密码要求</h5>
                        <ul>
                            <li>密码长度至少8个字符</li>
                            <li>必须包含至少一个字母和一个数字</li>
                            <li>不要使用容易猜到的密码，如生日、姓名等</li>
                            <li>建议使用大小写字母、数字和特殊字符的组合</li>
                        </ul>
                    </div>
                    <div class="alert alert-warning">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> 注意事项</h5>
                        <ul>
                            <li>如果选择"强制用户退出登录"，用户将需要使用新密码重新登录</li>
                            <li>如果选择"发送密码修改通知邮件"，系统将向用户发送密码已被修改的通知</li>
                            <li>请确保用户有一个有效的邮箱地址，以便接收通知</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // 密码强度检测
        $('#password').on('input', function() {
            var password = $(this).val();
            var strength = 0;
            
            // 长度检查
            if (password.length >= 8) {
                strength += 1;
            }
            
            // 包含字母
            if (password.match(/[a-zA-Z]/)) {
                strength += 1;
            }
            
            // 包含数字
            if (password.match(/[0-9]/)) {
                strength += 1;
            }
            
            // 包含特殊字符
            if (password.match(/[^a-zA-Z0-9]/)) {
                strength += 1;
            }
            
            // 显示强度
            var strengthClass = '';
            var strengthText = '';
            
            switch (strength) {
                case 0:
                case 1:
                    strengthClass = 'bg-danger';
                    strengthText = '弱';
                    break;
                case 2:
                case 3:
                    strengthClass = 'bg-warning';
                    strengthText = '中';
                    break;
                case 4:
                    strengthClass = 'bg-success';
                    strengthText = '强';
                    break;
            }
            
            // 如果密码输入框后面没有强度指示器，则添加一个
            if ($('#password-strength').length === 0) {
                $(this).after('<div id="password-strength" class="mt-2"><div class="progress"><div class="progress-bar" role="progressbar" style="width: 0%"></div></div><small class="form-text mt-1">密码强度: <span id="strength-text"></span></small></div>');
            }
            
            // 更新强度指示器
            $('#password-strength .progress-bar').removeClass('bg-danger bg-warning bg-success').addClass(strengthClass).css('width', (strength * 25) + '%');
            $('#strength-text').text(strengthText);
        });
    });
</script>
@endsection 