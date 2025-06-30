@extends("layouts.app")

@section("title", "我的订单")

@section("content")
<div class="container py-4">
    <h1 class="h3 mb-4">我的订单</h1>
    
    @if(session("success"))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session("success") }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session("error"))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session("error") }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a class="nav-link {{ request("type") === null ? "active" : "" }}" href="{{ route("orders") }}">全部订单</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request("type") === "subscription" ? "active" : "" }}" href="{{ route("orders", ["type" => "subscription"]) }}">会员订阅</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request("type") === "point" ? "active" : "" }}" href="{{ route("orders", ["type" => "point"]) }}">积分购买</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request("type") === "product" ? "active" : "" }}" href="{{ route("orders", ["type" => "product"]) }}">产品购买</a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <div class="dropdown text-end">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="statusFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-filter me-1"></i> 状态筛选
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="statusFilterDropdown">
                            <li><a class="dropdown-item {{ request("status") === null ? "active" : "" }}" href="{{ route("orders", ["type" => request("type")]) }}">全部状态</a></li>
                            <li><a class="dropdown-item {{ request("status") === "pending" ? "active" : "" }}" href="{{ route("orders", ["type" => request("type"), "status" => "pending"]) }}">待支付</a></li>
                            <li><a class="dropdown-item {{ request("status") === "completed" ? "active" : "" }}" href="{{ route("orders", ["type" => request("type"), "status" => "completed"]) }}">已完成</a></li>
                            <li><a class="dropdown-item {{ request("status") === "failed" ? "active" : "" }}" href="{{ route("orders", ["type" => request("type"), "status" => "failed"]) }}">已失败</a></li>
                            <li><a class="dropdown-item {{ request("status") === "cancelled" ? "active" : "" }}" href="{{ route("orders", ["type" => request("type"), "status" => "cancelled"]) }}">已取消</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>订单号</th>
                            <th>订单类型</th>
                            <th>金额</th>
                            <th>支付方式</th>
                            <th>创建时间</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>
                                    @if($order->order_type === "subscription")
                                        <span class="badge bg-primary">会员订阅</span>
                                    @elseif($order->order_type === "point")
                                        <span class="badge bg-info">积分购买</span>
                                    @elseif($order->order_type === "product")
                                        <span class="badge bg-success">产品购买</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $order->order_type }}</span>
                                    @endif
                                </td>
                                <td>￥{{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    @if($order->payment_method === "alipay")
                                        <img src="{{ asset("images/payment/alipay.png") }}" alt="支付宝" height="20">
                                    @elseif($order->payment_method === "wechat")
                                        <img src="{{ asset("images/payment/wechat.png") }}" alt="微信支付" height="20">
                                    @elseif($order->payment_method === "card")
                                        <img src="{{ asset("images/payment/card.png") }}" alt="银行卡" height="20">
                                    @else
                                        {{ $order->payment_method ?: "未选择" }}
                                    @endif
                                </td>
                                <td>{{ $order->created_at->format("Y-m-d H:i") }}</td>
                                <td>
                                    @if($order->status === "pending")
                                        <span class="badge bg-warning">待支付</span>
                                    @elseif($order->status === "completed")
                                        <span class="badge bg-success">已完成</span>
                                    @elseif($order->status === "failed")
                                        <span class="badge bg-danger">已失败</span>
                                    @elseif($order->status === "cancelled")
                                        <span class="badge bg-secondary">已取消</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $order->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route("order.show", $order->id) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($order->status === "pending")
                                            <a href="{{ route("payment.show", $order->order_number) }}" class="btn btn-outline-success">
                                                <i class="fas fa-credit-card"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="mb-0">暂无订单记录</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- 分页 -->
            <div class="d-flex justify-content-center mt-4">
                {{ $orders->appends(["type" => request("type"), "status" => request("status")])->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
