@extends('admin.layouts.app')

@section('title', '添加权限分组')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">添加权限分组</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form role="form" method="POST" action="{{ route('admin.management.permissions.groups.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">分组名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="请输入分组名称" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="slug">分组标识 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" placeholder="请输入分组标识（英文字母、数字和下划线）" value="{{ old('slug') }}" required>
                            @error('slug')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">分组标识只能包含英文字母、数字和下划线，例如：user_management</small>
                        </div>
                        <div class="form-group">
                            <label for="description">分组描述</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="请输入分组描述">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">提交</button>
                        <a href="{{ route('admin.management.permissions.groups.index') }}" class="btn btn-default">取消</a>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">权限分组说明</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> 什么是权限分组？</h5>
                        <p>权限分组用于对系统权限进行分类管理，便于权限的分配和管理。</p>
                    </div>
                    
                    <h5>权限分组的用途：</h5>
                    <ul>
                        <li>对权限进行分类管理</li>
                        <li>在权限列表中进行筛选</li>
                        <li>在角色权限分配时更清晰地展示</li>
                    </ul>
                    
                    <h5>命名建议：</h5>
                    <ul>
                        <li>使用简洁明了的名称</li>
                        <li>使用功能模块相关的名称</li>
                        <li>标识使用小写字母和下划线</li>
                    </ul>
                    
                    <h5>示例：</h5>
                    <table class="table table-sm">
                        <tr>
                            <th>分组名称</th>
                            <th>分组标识</th>
                        </tr>
                        <tr>
                            <td>用户管理</td>
                            <td><code>user_management</code></td>
                        </tr>
                        <tr>
                            <td>内容管理</td>
                            <td><code>content_management</code></td>
                        </tr>
                        <tr>
                            <td>系统设置</td>
                            <td><code>system_settings</code></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // 分组名称自动生成分组标识
        $('#name').on('input', function() {
            var name = $(this).val();
            var slug = name.toLowerCase()
                .replace(/[^a-z0-9\s]/g, '')
                .replace(/\s+/g, '_');
            $('#slug').val(slug);
        });
    });
</script>
@endsection 