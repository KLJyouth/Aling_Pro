
@extends('admin.layouts.admin')

@section('title', '会员订阅管理')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">会员订阅列表</h3>
                    <div class="card-tools">
                        <form action="{{ route('admin.membership.subscriptions.index') }}" method="GET" class="form-inline">
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <input type="text" name="search" class="form-control float-right" placeholder="搜索用户名/邮箱" value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>用户</th>
                                <th>会员等级</th>
                                <th>订阅类型</th>
                                <th>开始时间</th>
                                <th>到期时间</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subscriptions as $subscription)
                            <tr>
                                <td>{{ $subscription->id }}</td>
                                <td>{{ $subscription->user->name }} ({{ $subscription->user->email }})</td>
                                <td>{{ $subscription->membershipLevel->name }}</td>
                                <td>
                                    @if($subscription->subscription_type == 'monthly')
                                    <span class="badge badge-info">月付</span>
                                    @elseif($subscription->subscription_type == 'yearly')
                                    <span class="badge badge-primary">年付</span>
                                    @else
                                    <span class="badge badge-secondary">{{ $subscription->subscription_type }}</span>
                                    @endif
                                </td>
                                <td>{{ $subscription->start_date }}</td>
                                <td>{{ $subscription->end_date }}</td>
                                <td>
                                    @if($subscription->status == 'active')
                                    <span class="badge badge-success">活跃</span>
                                    @elseif($subscription->status == 'expired')
                                    <span class="badge badge-danger">已过期</span>
                                    @elseif($subscription->status == 'cancelled')
                                    <span class="badge badge-warning">已取消</span>
                                    @else
                                    <span class="badge badge-secondary">{{ $subscription->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.membership.subscriptions.show', $subscription->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> 查看
                                    </a>
                                    @if($subscription->status == 'active')
                                    <form action="{{ route('admin.membership.subscriptions.cancel', $subscription->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('确定要取消该会员订阅吗？')">
                                            <i class="fas fa-ban"></i> 取消订阅
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{ $subscriptions->links() }}
                </div>
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        $('#subscription-table').DataTable({
            "paging": false,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "responsive": true,
        });
    });
</script>
@endsection
