@extends("layouts.app")

@section("title", "我的订单")

@section("content")
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">我的订单</h5>
                </div>
                <div class="card-body">
                    @if(count($orders) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>订单号</th>
                                        <th>商品名称</th>
                                        <th>金额</th>
                                        <th>支付方式</th>
                                        <th>订单状态</th>
                                        <th>创建时间</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>{{ $order->order_number }}</td>
                                            <td>{{ $order->package_name }}</td>
                                            <td>{{ number_format($order->total_amount, 2) }}</td>
                                            <td>
                                                @if($order->payment_method === "alipay")
                                                    支付宝
                                                @elseif($order->payment_method === "wechat")
                                                    微信支付
                                                @else
                                                    {{ $order->payment_method }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($order->status === "paid")
                                                    <span class="badge badge-success">已支付</span>
                                                @elseif($order->status === "pending")
                                                    <span class="badge badge-warning">待支付</span>
                                                @elseif($order->status === "cancelled")
                                                    <span class="badge badge-danger">已取消</span>
                                                @elseif($order->status === "refunded")
                                                    <span class="badge badge-info">已退款</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ $order->status }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $order->created_at->format("Y-m-d H:i:s") }}</td>
                                            <td>
                                                <a href="{{ route("user.billing.order", $order->id) }}" class="btn btn-sm btn-info">查看详情</a>
                                                @if($order->status === "pending")
                                                    <a href="{{ route("user.billing.pay_order", $order->id) }}" class="btn btn-sm btn-primary">继续支付</a>
                                                    <a href="{{ route("user.billing.cancel_order", $order->id) }}" class="btn btn-sm btn-danger" onclick="return confirm("确定要取消此订单吗？")">取消订单</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $orders->links() }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i> 您还没有任何订单，<a href="{{ route("user.billing.packages") }}">立即购买</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
