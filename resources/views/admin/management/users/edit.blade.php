@extends('admin.layouts.app')

@section('title', '编辑用户')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">编辑用户</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form role="form" method="POST" action="{{ route('admin.management.users.update', $user->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">用户名 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="请输入用户名" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="email">邮箱 <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="请输入邮箱" value="{{ old('email', $user->email) }}" required {{ $user->is_protected ? 'readonly' : '' }}>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            @if($user->is_protected)
                            <small class="form-text text-warning">此用户为系统保护用户，邮箱不可修改</small>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="phone">手机号码</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" placeholder="请输入手机号码" value="{{ old('phone', $user->phone) }}">
                            @error('phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>角色分配</label>
                            <div class="select2-purple">
                                <select class="select2" name="roles[]" multiple="multiple" data-placeholder="选择角色" style="width: 100%;" {{ $user->is_protected ? 'disabled' : '' }}>
                                    @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ in_array($role->id, old('roles', $userRoles)) ? 'selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('roles')
                                <span class="text-danger">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            @if($user->is_protected)
                            <small class="form-text text-warning">此用户为系统保护用户，角色不可修改</small>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="status">状态</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" {{ $user->is_protected ? 'disabled' : '' }}>
                                <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>正常</option>
                                <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>禁用</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            @if($user->is_protected)
                            <small class="form-text text-warning">此用户为系统保护用户，状态不可修改</small>
                            @endif
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">保存</button>
                        <a href="{{ route('admin.management.users.index') }}" class="btn btn-default">取消</a>
                        <a href="{{ route('admin.management.users.edit_password', $user->id) }}" class="btn btn-warning float-right">
                            <i class="fas fa-key"></i> 修改密码
                        </a>
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