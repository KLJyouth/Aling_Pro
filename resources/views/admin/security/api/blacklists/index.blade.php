@extends('admin.layouts.app')

@section('title', 'API黑名单管理')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">API黑名单列表</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modal-add-blacklist">
                            <i class="fas fa-plus"></i> 添加黑名单
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" id="btn-batch-delete" disabled>
                            <i class="fas fa-trash"></i> 批量删除
                        </button>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <form action="{{ route('admin.security.api.blacklists.index') }}" method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <input type="text" name="ip_address" class="form-control" placeholder="IP地址" value="{{ request('ip_address') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <select name="status" class="form-control">
                                        <option value="">所有状态</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>生效中</option>
                                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>已过期</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="far fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control float-right" id="date-range" name="date_range" value="{{ request('date_range') }}" placeholder="创建日期范围">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 搜索
                                </button>
                                <a href="{{ route('admin.security.api.blacklists.index') }}" class="btn btn-default">
                                    <i class="fas fa-redo"></i> 重置
                                </a>
                            </div>
                        </div>
                    </form>
                    
                    <form id="batch-form" action="{{ route('admin.security.api.blacklists.batch-delete') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th width="40">
                                            <div class="icheck-primary">
                                                <input type="checkbox" id="check-all">
                                                <label for="check-all"></label>
                                            </div>
                                        </th>
                                        <th>ID</th>
                                        <th>IP地址</th>
                                        <th>位置信息</th>
                                        <th>原因</th>
                                        <th>创建时间</th>
                                        <th>过期时间</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($blacklists as $item)
                                    <tr>
                                        <td>
                                            <div class="icheck-primary">
                                                <input type="checkbox" name="ids[]" value="{{ $item->id }}" id="check-{{ $item->id }}" class="blacklist-check">
                                                <label for="check-{{ $item->id }}"></label>
                                            </div>
                                        </td>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->ip_address }}</td>
                                        <td>{{ $item->ip_location ?: '未知' }}</td>
                                        <td>{{ Str::limit($item->reason, 30) }}</td>
                                        <td>{{ $item->created_at }}</td>
                                        <td>
                                            @if($item->expire_at)
                                            {{ $item->expire_at }}
                                            @else
                                            <span class="badge badge-danger">永不过期</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!$item->expire_at || $item->expire_at > now())
                                            <span class="badge badge-success">生效中</span>
                                            @else
                                            <span class="badge badge-secondary">已过期</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-xs btn-info view-blacklist" 
                                                data-id="{{ $item->id }}"
                                                data-ip="{{ $item->ip_address }}"
                                                data-location="{{ $item->ip_location }}"
                                                data-reason="{{ $item->reason }}"
                                                data-created="{{ $item->created_at }}"
                                                data-expire="{{ $item->expire_at }}"
                                                data-event="{{ $item->risk_event_id }}">
                                                <i class="fas fa-eye"></i> 查看
                                            </button>
                                            <button type="button" class="btn btn-xs btn-warning edit-blacklist"
                                                data-id="{{ $item->id }}"
                                                data-ip="{{ $item->ip_address }}"
                                                data-reason="{{ $item->reason }}"
                                                data-expire="{{ $item->expire_hours }}">
                                                <i class="fas fa-edit"></i> 编辑
                                            </button>
                                            <form action="{{ route('admin.security.api.blacklists.destroy', $item->id) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('确定要删除该黑名单记录吗？')">
                                                    <i class="fas fa-trash"></i> 删除
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{ $blacklists->appends(request()->except('page'))->links() }}
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>

<!-- 添加黑名单模态框 -->
<div class="modal fade" id="modal-add-blacklist">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">添加黑名单</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.security.api.blacklists.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="add-ip">IP地址 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add-ip" name="ip_address" placeholder="请输入IP地址" required>
                    </div>
                    <div class="form-group">
                        <label for="add-reason">原因 <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="add-reason" name="reason" rows="3" placeholder="请输入加入黑名单的原因" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="add-expire">过期时间</label>
                        <select class="form-control" id="add-expire" name="expire_time">
                            <option value="1">1小时</option>
                            <option value="24">24小时</option>
                            <option value="168">7天</option>
                            <option value="720">30天</option>
                            <option value="0" selected>永不过期</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">添加</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- 编辑黑名单模态框 -->
<div class="modal fade" id="modal-edit-blacklist">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">编辑黑名单</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" id="edit-form">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit-ip">IP地址</label>
                        <input type="text" class="form-control" id="edit-ip" name="ip_address" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit-reason">原因 <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit-reason" name="reason" rows="3" placeholder="请输入加入黑名单的原因" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit-expire">过期时间</label>
                        <select class="form-control" id="edit-expire" name="expire_time">
                            <option value="1">1小时</option>
                            <option value="24">24小时</option>
                            <option value="168">7天</option>
                            <option value="720">30天</option>
                            <option value="0">永不过期</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- 查看黑名单模态框 -->
<div class="modal fade" id="modal-view-blacklist">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">黑名单详情</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 30%">ID</th>
                        <td id="view-id"></td>
                    </tr>
                    <tr>
                        <th>IP地址</th>
                        <td id="view-ip"></td>
                    </tr>
                    <tr>
                        <th>位置信息</th>
                        <td id="view-location"></td>
                    </tr>
                    <tr>
                        <th>原因</th>
                        <td id="view-reason"></td>
                    </tr>
                    <tr>
                        <th>创建时间</th>
                        <td id="view-created"></td>
                    </tr>
                    <tr>
                        <th>过期时间</th>
                        <td id="view-expire"></td>
                    </tr>
                    <tr id="view-event-row">
                        <th>关联风险事件</th>
                        <td>
                            <a href="#" id="view-event-link" class="btn btn-xs btn-info">
                                <i class="fas fa-eye"></i> 查看风险事件
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
@endsection

@section('scripts')
<script>
    $(function() {
        // 日期范围选择器
        $('#date-range').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD',
                applyLabel: '确定',
                cancelLabel: '取消',
                fromLabel: '从',
                toLabel: '到',
                customRangeLabel: '自定义',
                weekLabel: 'W',
                daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'],
                monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
                firstDay: 1
            },
            ranges: {
               '今天': [moment(), moment()],
               '昨天': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
               '最近7天': [moment().subtract(6, 'days'), moment()],
               '最近30天': [moment().subtract(29, 'days'), moment()],
               '本月': [moment().startOf('month'), moment().endOf('month')],
               '上个月': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            autoUpdateInput: false
        });
        
        $('#date-range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });
        
        $('#date-range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
        
        // 全选/取消全选
        $('#check-all').click(function() {
            $('.blacklist-check').prop('checked', $(this).prop('checked'));
            updateBatchDeleteButton();
        });
        
        // 单个复选框点击
        $('.blacklist-check').click(function() {
            updateBatchDeleteButton();
        });
        
        // 更新批量删除按钮状态
        function updateBatchDeleteButton() {
            var checkedCount = $('.blacklist-check:checked').length;
            $('#btn-batch-delete').prop('disabled', checkedCount === 0);
        }
        
        // 批量删除
        $('#btn-batch-delete').click(function() {
            if(confirm('确定要删除选中的黑名单记录吗？')) {
                $('#batch-form').submit();
            }
        });
        
        // 查看黑名单
        $('.view-blacklist').click(function() {
            var id = $(this).data('id');
            var ip = $(this).data('ip');
            var location = $(this).data('location') || '未知';
            var reason = $(this).data('reason');
            var created = $(this).data('created');
            var expire = $(this).data('expire') || '<span class="badge badge-danger">永不过期</span>';
            var eventId = $(this).data('event');
            
            $('#view-id').text(id);
            $('#view-ip').text(ip);
            $('#view-location').text(location);
            $('#view-reason').text(reason);
            $('#view-created').text(created);
            $('#view-expire').html(expire);
            
            if(eventId) {
                $('#view-event-row').show();
                $('#view-event-link').attr('href', '{{ route("admin.security.api.risk-events.show", "") }}/' + eventId);
            } else {
                $('#view-event-row').hide();
            }
            
            $('#modal-view-blacklist').modal('show');
        });
        
        // 编辑黑名单
        $('.edit-blacklist').click(function() {
            var id = $(this).data('id');
            var ip = $(this).data('ip');
            var reason = $(this).data('reason');
            var expire = $(this).data('expire');
            
            $('#edit-form').attr('action', '{{ route("admin.security.api.blacklists.update", "") }}/' + id);
            $('#edit-ip').val(ip);
            $('#edit-reason').val(reason);
            $('#edit-expire').val(expire);
            
            $('#modal-edit-blacklist').modal('show');
        });
    });
</script>
@endsection 