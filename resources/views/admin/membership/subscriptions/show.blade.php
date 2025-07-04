
@extends('admin.layouts.admin')

@section('title', '会员订阅详情')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">会员订阅详情</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.membership.subscriptions.index') }}" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> 返回列表
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
                    </div>
                </div>
                <!-- /.card-header -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">会员订阅详情</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.membership.subscriptions.index') }}" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> 返回列表
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
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">订阅信息</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 30%">ID</th>
                                            <td>{{ $subscription->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>会员等级</th>
                                            <td>{{ $subscription->membershipLevel->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>订阅类型</th>
                                            <td>
                                                @if($subscription->subscription_type == 'monthly')
                                                <span class="badge badge-info">月付</span>
                                                @elseif($subscription->subscription_type == 'yearly')
                                                <span class="badge badge-primary">年付</span>
                                                @else
                                                <span class="badge badge-secondary">{{ $subscription->subscription_type }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>价格</th>
                                            <td>{{ $subscription->price }}</td>
                                        </tr>
                                        <tr>
                                            <th>开始时间</th>
                                            <td>{{ $subscription->start_date }}</td>
                                        </tr>
                                        <tr>
                                            <th>到期时间</th>
                                            <td>{{ $subscription->end_date }}</td>
                                        </tr>
                                        <tr>
                                            <th>状态</th>
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
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">用户信息</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 30%">用户ID</th>
                                            <td>{{ $subscription->user->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>用户名</th>
                                            <td>{{ $subscription->user->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>邮箱</th>
                                            <td>{{ $subscription->user->email }}</td>
                                        </tr>
                                        <tr>
                                            <th>注册时间</th>
                                            <td>{{ $subscription->user->created_at }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">支付记录</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>交易号</th>
                                                <th>金额</th>
                                                <th>支付方式</th>
                                                <th>支付时间</th>
                                                <th>状态</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($payments as $payment)
                                            <tr>
                                                <td>{{ $payment->id }}</td>
                                                <td>{{ $payment->transaction_id }}</td>
                                                <td>{{ $payment->amount }}</td>
                                                <td>{{ $payment->payment_method }}</td>
                                                <td>{{ $payment->created_at }}</td>
                                                <td>
                                                    @if($payment->status == 'success')
                                                    <span class="badge badge-success">成功</span>
                                                    @elseif($payment->status == 'failed')
                                                    <span class="badge badge-danger">失败</span>
                                                    @elseif($payment->status == 'pending')
                                                    <span class="badge badge-warning">处理中</span>
                                                    @else
                                                    <span class="badge badge-secondary">{{ $payment->status }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</div>
@endsection
