@extends("layouts.app")

@section("title", "订单详情")

@section("content")
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">订单详情</h5>
                    <a href="{{ route("user.billing.orders") }}" class="btn btn-sm btn-outline-secondary">返回订单列表</a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="text-muted mb-3">订单状态</h6>
                            <div class="d-flex align-items-center">
                                @if($order->status === "paid")
                                    <div class="badge badge-success p-2">
                                        <i class="fas fa-check-circle mr-1"></i> 已支付
                                    </div>
                                    <div class="ml-3 text-muted">
                                        支付时间：{{ $order->paid_at ? $order->paid_at->format("Y-m-d H:i:s") : "未知" }}
                                    </div>
                                @elseif($order->status === "pending")
                                    <div class="badge badge-warning p-2">
                                        <i class="fas fa-clock mr-1"></i> 待支付
                                    </div>
                                    <div class="ml-3">
                                        <a href="{{ route("user.billing.pay_order", $order->id) }}" class="btn btn-sm btn-primary">立即支付</a>
                                        <a href="{{ route("user.billing.cancel_order", $order->id) }}" class="btn btn-sm btn-danger ml-2" onclick="return confirm("确定要取消此订单吗？")">取消订单</a>
                                    </div>
                                @elseif($order->status === "cancelled")
                                    <div class="badge badge-danger p-2">
                                        <i class="fas fa-times-circle mr-1"></i> 已取消
                                    </div>
                                    <div class="ml-3 text-muted">
                                        取消时间：{{ $order->cancelled_at ? $order->cancelled_at->format("Y-m-d H:i:s") : "未知" }}
                                    </div>
                                @elseif($order->status === "refunded")
                                    <div class="badge badge-info p-2">
                                        <i class="fas fa-undo mr-1"></i> 已退款
                                    </div>
                                    <div class="ml-3 text-muted">
                                        退款时间：{{ $order->refunded_at ? $order->refunded_at->format("Y-m-d H:i:s") : "未知" }}
                                    </div>
                                @else
                                    <div class="badge badge-secondary p-2">
                                        {{ $order->status }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>


                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="text-muted mb-3">订单信息</h6>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">订单号</th>
                                    <td>{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <th>创建时间</th>
                                    <td>{{ $order->created_at->format("Y-m-d H:i:s") }}</td>
                                </tr>
                                <tr>
                                    <th>支付方式</th>
                                    <td>
                                        @if($order->payment_method === "alipay")
                                            支付宝
                                        @elseif($order->payment_method === "wechat")
                                            微信支付
                                        @else
                                            {{ $order->payment_method }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>交易号</th>
                                    <td>{{ $order->transaction_id ?: "暂无" }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="text-muted mb-3">商品信息</h6>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>商品名称</th>
                                        <th>商品类型</th>
                                        <th>额度</th>
                                        <th>单价</th>
                                        <th>数量</th>
                                        <th>小计</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td>{{ $item->package_name }}</td>
                                            <td>
                                                @switch($item->package_type)
                                                    @case("api")
                                                        <span class="badge badge-info">API调用额度</span>
                                                        @break
                                                    @case("ai")
                                                        <span class="badge badge-success">AI使用额度</span>
                                                        @break
                                                    @case("storage")
                                                        <span class="badge badge-warning">存储空间</span>
                                                        @break
                                                    @case("bandwidth")
                                                        <span class="badge badge-primary">带宽流量</span>
                                                        @break
                                                    @case("comprehensive")
                                                        <span class="badge badge-secondary">综合套餐</span>
                                                        @break
                                                    @default
                                                        <span class="badge badge-secondary">{{ $item->package_type }}</span>
                                                @endswitch
                                            </td>
                                            <td>{{ number_format($item->quota) }}</td>
                                            <td>{{ number_format($item->price, 2) }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ number_format($item->price * $item->quantity, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="text-muted mb-3">金额信息</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>商品金额：</span>
                                        <span>{{ number_format($order->subtotal_amount, 2) }}</span>
                                    </div>
                                    @if($order->discount_amount > 0)
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <span>会员折扣：</span>
                                            <span class="text-success">- {{ number_format($order->discount_amount, 2) }}</span>
                                        </div>
                                    @endif
                                    @if($order->coupon_amount > 0)
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <span>优惠券：</span>
                                            <span class="text-success">- {{ number_format($order->coupon_amount, 2) }}</span>
                                        </div>
                                    @endif
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">实付金额：</h6>
                                        <h5 class="text-danger mb-0">{{ number_format($order->total_amount, 2) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($order->status === "paid")
                        <div class="text-center mt-4">
                            <a href="{{ route("user.billing.invoice", $order->id) }}" class="btn btn-outline-secondary" target="_blank">
                                <i class="fas fa-file-invoice mr-1"></i> 查看发票
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
