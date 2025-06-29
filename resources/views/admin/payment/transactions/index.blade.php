@extends("admin.layouts.app")

@section("title", "交易管理")

@section("content")
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">交易列表</h3>
                    <div class="card-tools">
                        <a href="{{ route("admin.payment.transactions.export", request()->query()) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-export"></i> 导出数据
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session("success"))
                        <div class="alert alert-success">
                            {{ session("success") }}
                        </div>
                    @endif
                    
                    @if(session("error"))
                        <div class="alert alert-danger">
                            {{ session("error") }}
                        </div>
                    @endif
                    
                    <!-- 筛选表单 -->
                    <div class="card card-body bg-light mb-4">
                        <form action="{{ route("admin.payment.transactions.index") }}" method="GET">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="gateway_id">支付网关</label>
                                        <select class="form-control" id="gateway_id" name="gateway_id">
                                            <option value="">全部</option>
                                            @foreach($gateways as $gateway)
                                                <option value="{{ $gateway->id }}" {{ request("gateway_id") == $gateway->id ? "selected" : "" }}>{{ $gateway->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="status">状态</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="">全部</option>
                                            <option value="pending" {{ request("status") === "pending" ? "selected" : "" }}>待支付</option>
                                            <option value="completed" {{ request("status") === "completed" ? "selected" : "" }}>已完成</option>
                                            <option value="failed" {{ request("status") === "failed" ? "selected" : "" }}>失败</option>
                                            <option value="refunded" {{ request("status") === "refunded" ? "selected" : "" }}>已退款</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="transaction_id">交易ID</label>
                                        <input type="text" class="form-control" id="transaction_id" name="transaction_id" value="{{ request("transaction_id") }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="order_id">订单ID</label>
                                        <input type="text" class="form-control" id="order_id" name="order_id" value="{{ request("order_id") }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="user_id">用户ID</label>
                                        <input type="text" class="form-control" id="user_id" name="user_id" value="{{ request("user_id") }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="date_from">开始日期</label>
                                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request("date_from") }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="date_to">结束日期</label>
                                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request("date_to") }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search"></i> 搜索
                                            </button>
                                            <a href="{{ route("admin.payment.transactions.index") }}" class="btn btn-default">
                                                <i class="fas fa-redo"></i> 重置
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>交易ID</th>
                                <th>订单ID</th>
                                <th>用户ID</th>
                                <th>支付网关</th>
                                <th>金额</th>
                                <th>状态</th>
                                <th>支付方式</th>
                                <th>支付时间</th>
                                <th>创建时间</th>
                                <th style="width: 120px">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->transaction_id }}</td>
                                    <td>{{ $transaction->order_id }}</td>
                                    <td>{{ $transaction->user_id ?: "-" }}</td>
                                    <td>{{ $transaction->gateway_name }}</td>
                                    <td>{{ $transaction->amount }} {{ $transaction->currency }}</td>
                                    <td>
                                        @if($transaction->status === "pending")
                                            <span class="badge badge-warning">待支付</span>
                                        @elseif($transaction->status === "completed")
                                            <span class="badge badge-success">已完成</span>
                                        @elseif($transaction->status === "failed")
                                            <span class="badge badge-danger">失败</span>
                                        @elseif($transaction->status === "refunded")
                                            <span class="badge badge-info">已退款</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $transaction->status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $transaction->payment_method ?: "-" }}</td>
                                    <td>{{ $transaction->paid_at ?: "-" }}</td>
                                    <td>{{ $transaction->created_at }}</td>
                                    <td>
                                        <a href="{{ route("admin.payment.transactions.show", $transaction->transaction_id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> 详情
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">暂无交易记录</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="mt-4">
                        {{ $transactions->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
