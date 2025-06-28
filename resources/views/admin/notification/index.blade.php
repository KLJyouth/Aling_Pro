@extends('admin.layouts.app')

@section('title', '通知管理')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">通知列表</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.notification.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> 创建通知
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- 筛选表单 -->
                    <form action="{{ route('admin.notification.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>通知类型</label>
                                    <select name="type" class="form-control">
                                        <option value="">全部类型</option>
                                        @foreach($types as $key => $value)
                                            <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>状态</label>
                                    <select name="status" class="form-control">
                                        <option value="">全部状态</option>
                                        @foreach($statuses as $key => $value)
                                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>优先级</label>
                                    <select name="priority" class="form-control">
                                        <option value="">全部优先级</option>
                                        @foreach($priorities as $key => $value)
                                            <option value="{{ $key }}" {{ request('priority') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>搜索</label>
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="标题或内容" value="{{ request('search') }}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- 通知列表 -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>标题</th>
                                    <th>类型</th>
                                    <th>状态</th>
                                    <th>优先级</th>
                                    <th>接收者数</th>
                                    <th>发送者</th>
                                    <th>计划发送时间</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($notifications as $notification)
                                <tr>
                                    <td>{{ $notification->id }}</td>
                                    <td>{{ Str::limit($notification->title, 30) }}</td>
                                    <td>
                                        @if($notification->type == 'system')
                                            <span class="badge badge-info">{{ $types[$notification->type] }}</span>
                                        @elseif($notification->type == 'user')
                                            <span class="badge badge-primary">{{ $types[$notification->type] }}</span>
                                        @elseif($notification->type == 'email')
                                            <span class="badge badge-success">{{ $types[$notification->type] }}</span>
                                        @elseif($notification->type == 'api')
                                            <span class="badge badge-warning">{{ $types[$notification->type] }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($notification->status == 'draft')
                                            <span class="badge badge-secondary">{{ $statuses[$notification->status] }}</span>
                                        @elseif($notification->status == 'sending')
                                            <span class="badge badge-info">{{ $statuses[$notification->status] }}</span>
                                        @elseif($notification->status == 'sent')
                                            <span class="badge badge-success">{{ $statuses[$notification->status] }}</span>
                                        @elseif($notification->status == 'failed')
                                            <span class="badge badge-danger">{{ $statuses[$notification->status] }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($notification->priority == 'low')
                                            <span class="badge badge-secondary">{{ $priorities[$notification->priority] }}</span>
                                        @elseif($notification->priority == 'normal')
                                            <span class="badge badge-info">{{ $priorities[$notification->priority] }}</span>
                                        @elseif($notification->priority == 'high')
                                            <span class="badge badge-warning">{{ $priorities[$notification->priority] }}</span>
                                        @elseif($notification->priority == 'urgent')
                                            <span class="badge badge-danger">{{ $priorities[$notification->priority] }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $notification->recipients_count }}</td>
                                    <td>{{ $notification->sender ? $notification->sender->name : '系统' }}</td>
                                    <td>{{ $notification->scheduled_at ? $notification->scheduled_at->format('Y-m-d H:i') : '-' }}</td>
                                    <td>{{ $notification->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.notification.show', $notification) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($notification->status == 'draft')
                                            <a href="{{ route('admin.notification.edit', $notification) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.notification.send', $notification) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('确定要发送此通知吗？')">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </form>
                                            @endif
                                            <form action="{{ route('admin.notification.duplicate', $notification) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.notification.destroy', $notification) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('确定要删除此通知吗？')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- 分页 -->
                    <div class="mt-4">
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 