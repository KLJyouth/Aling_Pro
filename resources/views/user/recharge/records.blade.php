@extends("layouts.app")

@section("title", "充值记录")

@section("content")
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            @include("user.partials.sidebar")
        </div>
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">充值记录</h5>
                    <a href="{{ route("user.recharge.index") }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> 去充值
                    </a>
                </div>
                <div class="card-body">
                    <!-- 过滤器 -->
                    <div class="mb-4">
                        <form method="GET" action="{{ route("user.recharge.records") }}" class="row g-3">
                            <div class="col-md-3">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">所有状态</option>
                                    <option value="success" {{ request("status") == "success" ? "selected" : "" }}>成功</option>
                                    <option value="pending" {{ request("status") == "pending" ? "selected" : "" }}>待处理</option>
                                    <option value="processing" {{ request("status") == "processing" ? "selected" : "" }}>处理中</option>
                                    <option value="failed" {{ request("status") == "failed" ? "selected" : "" }}>失败</option>
                                    <option value="cancelled" {{ request("status") == "cancelled" ? "selected" : "" }}>已取消</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="start_date" class="form-control form-control-sm" placeholder="开始日期" value="{{ request("start_date") }}">
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="end_date" class="form-control form-control-sm" placeholder="结束日期" value="{{ request("end_date") }}">
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-sm btn-outline-primary me-2">筛选</button>
                                    <a href="{{ route("user.recharge.records") }}" class="btn btn-sm btn-outline-secondary">重置</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    @if($transactions->isEmpty())
                        <div class="text-center py-5">
                            <img src="{{ asset("images/empty-state.svg") }}" alt="暂无记录" class="img-fluid mb-3" style="max-width: 200px;">
                            <h5>暂无充值记录</h5>
                            <p class="text-muted">您还没有进行过充值操作</p>
                            <a href="{{ route("user.recharge.index") }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> 去充值
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>订单号</th>
                                        <th>金额</th>
                                        <th>赠送积分</th>
                                        <th>支付方式</th>
                                        <th>状态</th>
                                        <th>创建时间</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction->transaction_no }}</td>
                                            <td>{{ number_format($transaction->amount, 2) }}</td>
                                            <td>
                                                @if($transaction->points > 0)
                                                    <span class="badge bg-success">+{{ number_format($transaction->points) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $transaction->payment_method }}</td>
                                            <td>
                                                @if($transaction->status === "success")
                                                    <span class="badge bg-success">{{ $transaction->status_text }}</span>
                                                @elseif($transaction->status === "pending" || $transaction->status === "processing")
                                                    <span class="badge bg-warning">{{ $transaction->status_text }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ $transaction->status_text }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $transaction->created_at->format("Y-m-d H:i") }}</td>
                                            <td>
                                                <a href="{{ route("user.recharge.result", $transaction->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center mt-4">
                            {{ $transactions->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
