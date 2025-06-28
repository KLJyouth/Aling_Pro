@extends('admin.layouts.app')

@section('title', '通知管理')

@section('content')
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="row mb-4">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">通知管理</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">首页</a></li>
                <li class="breadcrumb-item active">通知管理</li>
            </ol>
        </div>
    </div>

    <!-- 通知统计卡片 -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] ?? 0 }}</h3>
                    <p>总通知数</p>
                </div>
                <div class="icon">
                    <i class="fas fa-bell"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['sent'] ?? 0 }}</h3>
                    <p>已发送</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['pending'] ?? 0 }}</h3>
                    <p>待发送</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['failed'] ?? 0 }}</h3>
                    <p>发送失败</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 操作按钮和筛选器 -->
    <div class="row mb-3">
        <div class="col-md-8">
            <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary mr-2">
                <i class="fas fa-plus"></i> 创建通知
            </a>
            <a href="{{ route('admin.notification.templates.index') }}" class="btn btn-info mr-2">
                <i class="fas fa-file-alt"></i> 通知模板
            </a>
            <a href="{{ route('admin.notification.rules.index') }}" class="btn btn-success mr-2">
                <i class="fas fa-cogs"></i> 自动规则
            </a>
            <a href="{{ route('admin.notification.email-providers.index') }}" class="btn btn-secondary">
                <i class="fas fa-envelope"></i> 邮件接口
            </a>
            <button id="bulkDelete" class="btn btn-danger ml-2" disabled>
                <i class="fas fa-trash"></i> 批量删除
            </button>
        </div>
        <div class="col-md-4">
            <form action="{{ route('admin.notifications.index') }}" method="GET" class="form-inline float-right">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="搜索通知..." value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 筛选器 -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">筛选选项</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.notifications.index') }}" method="GET" id="filter-form">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>通知类型</label>
                        <select name="type" class="form-control select2" onchange="this.form.submit()">
                            <option value="">全部类型</option>
                            <option value="system" {{ request('type') == 'system' ? 'selected' : '' }}>系统通知</option>
                            <option value="user" {{ request('type') == 'user' ? 'selected' : '' }}>用户通知</option>
                            <option value="email" {{ request('type') == 'email' ? 'selected' : '' }}>邮件通知</option>
                            <option value="api" {{ request('type') == 'api' ? 'selected' : '' }}>API通知</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>状态</label>
                        <select name="status" class="form-control select2" onchange="this.form.submit()">
                            <option value="">全部状态</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>草稿</option>
                            <option value="sending" {{ request('status') == 'sending' ? 'selected' : '' }}>发送中</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>已发送</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>发送失败</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>优先级</label>
                        <select name="priority" class="form-control select2" onchange="this.form.submit()">
                            <option value="">全部优先级</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>低</option>
                            <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>普通</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>高</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>紧急</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>日期范围</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="far fa-calendar-alt"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control float-right" id="date-range" name="date_range" value="{{ request('date_range') }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter"></i> 应用筛选
                        </button>
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-default btn-sm">
                            <i class="fas fa-sync"></i> 重置筛选
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 通知列表 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">通知列表</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th width="30px">
                            <div class="icheck-primary">
                                <input type="checkbox" id="selectAll">
                                <label for="selectAll"></label>
                            </div>
                        </th>
                        <th>ID</th>
                        <th>标题</th>
                        <th>类型</th>
                        <th>状态</th>
                        <th>优先级</th>
                        <th>接收者</th>
                        <th>计划发送时间</th>
                        <th>实际发送时间</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notification)
                    <tr>
                        <td>
                            <div class="icheck-primary">
                                <input type="checkbox" class="notification-checkbox" id="notification{{ $notification->id }}" value="{{ $notification->id }}">
                                <label for="notification{{ $notification->id }}"></label>
                            </div>
                        </td>
                        <td>{{ $notification->id }}</td>
                        <td>
                            <a href="{{ route('admin.notifications.show', $notification->id) }}" class="text-bold">
                                {{ Str::limit($notification->title, 40) }}
                            </a>
                        </td>
                        <td>
                            @switch($notification->type)
                                @case('system')
                                    <span class="badge badge-info">系统通知</span>
                                    @break
                                @case('user')
                                    <span class="badge badge-primary">用户通知</span>
                                    @break
                                @case('email')
                                    <span class="badge badge-warning">邮件通知</span>
                                    @break
                                @case('api')
                                    <span class="badge badge-secondary">API通知</span>
                                    @break
                                @default
                                    <span class="badge badge-light">{{ $notification->type }}</span>
                            @endswitch
                        </td>
                        <td>
                            @switch($notification->status)
                                @case('draft')
                                    <span class="badge badge-secondary">草稿</span>
                                    @break
                                @case('sending')
                                    <span class="badge badge-warning">发送中</span>
                                    @break
                                @case('sent')
                                    <span class="badge badge-success">已发送</span>
                                    @break
                                @case('failed')
                                    <span class="badge badge-danger">发送失败</span>
                                    @break
                                @default
                                    <span class="badge badge-light">{{ $notification->status }}</span>
                            @endswitch
                        </td>
                        <td>
                            @switch($notification->priority)
                                @case('low')
                                    <span class="badge badge-info">低</span>
                                    @break
                                @case('normal')
                                    <span class="badge badge-primary">普通</span>
                                    @break
                                @case('high')
                                    <span class="badge badge-warning">高</span>
                                    @break
                                @case('urgent')
                                    <span class="badge badge-danger">紧急</span>
                                    @break
                                @default
                                    <span class="badge badge-light">{{ $notification->priority }}</span>
                            @endswitch
                        </td>
                        <td>
                            <span class="badge badge-primary">{{ $notification->recipients_count ?? 0 }}</span>
                        </td>
                        <td>
                            {{ $notification->scheduled_at ? $notification->scheduled_at->format('Y-m-d H:i') : '-' }}
                        </td>
                        <td>
                            {{ $notification->sent_at ? $notification->sent_at->format('Y-m-d H:i') : '-' }}
                        </td>
                        <td>
                            {{ $notification->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.notifications.show', $notification->id) }}" class="btn btn-sm btn-info" title="查看">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($notification->status == 'draft')
                                <a href="{{ route('admin.notifications.edit', $notification->id) }}" class="btn btn-sm btn-primary" title="编辑">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.notifications.send', $notification->id) }}" class="btn btn-sm btn-success send-notification" title="发送" data-id="{{ $notification->id }}">
                                    <i class="fas fa-paper-plane"></i>
                                </a>
                                @endif
                                @if($notification->status != 'sending')
                                <button type="button" class="btn btn-sm btn-danger delete-notification" title="删除" data-id="{{ $notification->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                                @if($notification->type == 'email' && $notification->status == 'sent')
                                <a href="{{ route('admin.notifications.statistics', $notification->id) }}" class="btn btn-sm btn-secondary" title="统计">
                                    <i class="fas fa-chart-bar"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center">暂无通知数据</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $notifications->appends(request()->except('page'))->links() }}
        </div>
    </div>
</div>

<!-- 删除确认模态框 -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">确认删除</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>您确定要删除这条通知吗？此操作不可撤销。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">确认删除</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 批量删除确认模态框 -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" role="dialog" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkDeleteModalLabel">确认批量删除</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>您确定要删除所选的通知吗？此操作不可撤销。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <form id="bulkDeleteForm" method="POST" action="{{ route('admin.notifications.bulk-delete') }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="ids" id="bulkDeleteIds">
                    <button type="submit" class="btn btn-danger">确认删除</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        // 日期范围选择器
        $('#date-range').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD',
                applyLabel: '确定',
                cancelLabel: '取消',
                fromLabel: '从',
                toLabel: '至',
                customRangeLabel: '自定义范围',
                weekLabel: 'W',
                daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'],
                monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
                firstDay: 1
            },
            autoUpdateInput: false
        });

        $('#date-range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            $('#filter-form').submit();
        });

        $('#date-range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        // 初始化Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        // 删除通知
        $('.delete-notification').click(function() {
            var notificationId = $(this).data('id');
            $('#deleteForm').attr('action', '/admin/notifications/' + notificationId);
            $('#deleteModal').modal('show');
        });

        // 全选/取消全选
        $('#selectAll').click(function() {
            $('.notification-checkbox').prop('checked', this.checked);
            updateBulkDeleteButton();
        });

        // 更新批量删除按钮状态
        $('.notification-checkbox').click(function() {
            updateBulkDeleteButton();
        });

        function updateBulkDeleteButton() {
            var checkedCount = $('.notification-checkbox:checked').length;
            $('#bulkDelete').prop('disabled', checkedCount === 0);
        }

        // 批量删除
        $('#bulkDelete').click(function() {
            var ids = [];
            $('.notification-checkbox:checked').each(function() {
                ids.push($(this).val());
            });
            $('#bulkDeleteIds').val(ids.join(','));
            $('#bulkDeleteModal').modal('show');
        });

        // 发送通知确认
        $('.send-notification').click(function(e) {
            e.preventDefault();
            if (confirm('确定要发送此通知吗？')) {
                window.location.href = $(this).attr('href');
            }
        });
    });
</script>
@endsection 