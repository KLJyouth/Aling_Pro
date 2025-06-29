@extends('admin.layouts.app')

@section('title', '会员等级管理')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>会员等级管理</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">首页</a></li>
                <li class="breadcrumb-item active">会员等级管理</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">会员等级列表</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.membership.levels.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> 创建会员等级
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="mb-3">
                        <form action="{{ route('admin.membership.levels.index') }}" method="GET" class="form-inline">
                            <div class="form-group mr-2">
                                <select name="status" class="form-control">
                                    <option value="">全部状态</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>启用</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>禁用</option>
                                </select>
                            </div>
                            <div class="form-group mr-2">
                                <select name="is_featured" class="form-control">
                                    <option value="">全部</option>
                                    <option value="1" {{ request('is_featured') == '1' ? 'selected' : '' }}>特色会员</option>
                                    <option value="0" {{ request('is_featured') == '0' ? 'selected' : '' }}>普通会员</option>
                                </select>
                            </div>
                            <div class="form-group mr-2">
                                <input type="text" name="search" class="form-control" placeholder="搜索..." value="{{ request('search') }}">
                            </div>
                            <button type="submit" class="btn btn-primary">筛选</button>
                            <a href="{{ route('admin.membership.levels.index') }}" class="btn btn-default ml-2">重置</a>
                        </form>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>名称</th>
                                    <th>代码</th>
                                    <th>价格</th>
                                    <th>有效期(天)</th>
                                    <th>API额度</th>
                                    <th>AI额度</th>
                                    <th>存储额度</th>
                                    <th>带宽额度</th>
                                    <th>折扣率</th>
                                    <th>排序</th>
                                    <th>状态</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($membershipLevels as $level)
                                    <tr>
                                        <td>{{ $level->id }}</td>
                                        <td>
                                            {{ $level->name }}
                                            @if($level->is_featured)
                                                <span class="badge badge-info">特色</span>
                                            @endif
                                        </td>
                                        <td>{{ $level->code }}</td>
                                        <td>¥{{ number_format($level->price, 2) }}</td>
                                        <td>{{ $level->duration_days ?: '永久' }}</td>
                                        <td>{{ number_format($level->api_quota) }}</td>
                                        <td>{{ number_format($level->ai_quota) }}</td>
                                        <td>{{ number_format($level->storage_quota) }}MB</td>
                                        <td>{{ number_format($level->bandwidth_quota) }}MB</td>
                                        <td>{{ $level->discount_percent }}%</td>
                                        <td>{{ $level->sort_order }}</td>
                                        <td>
                                            @if($level->status == 'active')
                                                <span class="badge badge-success">启用</span>
                                            @else
                                                <span class="badge badge-danger">禁用</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.membership.levels.show', $level) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.membership.levels.edit', $level) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal{{ $level->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            
                                            <!-- 删除确认模态框 -->
                                            <div class="modal fade" id="deleteModal{{ $level->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel{{ $level->id }}" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteModalLabel{{ $level->id }}">确认删除</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>确定要删除会员等级 "{{ $level->name }}" 吗？此操作不可逆。</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                                                            <form action="{{ route('admin.membership.levels.destroy', $level) }}" method="POST" style="display: inline-block;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">确认删除</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{ $membershipLevels->appends(request()->query())->links() }}
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection
