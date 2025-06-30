@extends("layouts.app")

@section("title", "订单详情")

@section("content")
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">订单详情</h1>
        <a href="{{ route("orders") }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> 返回订单列表
        </a>
    </div>
    
    @if(session("success"))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session("success") }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <div class="row">
        <div class="col-lg-8">
            <!-- 订单信息 -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">订单信息</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="ps-0">订单号</th>
                                    <td>{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <th class="ps-0">订单类型</th>
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
                                </tr>
                                <tr>
                                    <th class="ps-0">创建时间</th>
                                    <td>{{ $order->created_at->format("Y-m-d H:i:s") }}</td>
                                </tr>
                                <tr>
                                    <th class="ps-0">订单状态</th>
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
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="ps-0">支付方式</th>
                                    <td>
                                        @if($order->payment_method === "alipay")
                                            <img src="{{ asset("images/payment/alipay.png") }}" alt="支付宝" height="20"> 支付宝
                                        @elseif($order->payment_method === "wechat")
                                            <img src="{{ asset("images/payment/wechat.png") }}" alt="微信支付" height="20"> 微信支付
                                        @elseif($order->payment_method === "card")
                                            <img src="{{ asset("images/payment/card.png") }}" alt="银行卡" height="20"> 银行卡
                                        @else
                                            {{ $order->payment_method ?: "未选择" }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="ps-0">支付状态</th>
                                    <td>
                                        @if($order->payment_status === "pending")
                                            <span class="badge bg-warning">待支付</span>
                                        @elseif($order->payment_status === "paid")
                                            <span class="badge bg-success">已支付</span>
                                        @elseif($order->payment_status === "failed")
                                            <span class="badge bg-danger">支付失败</span>
                                        @elseif($order->payment_status === "refunded")
                                            <span class="badge bg-info">已退款</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $order->payment_status }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="ps-0">支付时间</th>
                                    <td>{{ $order->paid_at ? $order->paid_at->format("Y-m-d H:i:s") : "未支付" }}</td>
                                </tr>
                                <tr>
                                    <th class="ps-0">更新时间</th>
                                    <td>{{ $order->updated_at->format("Y-m-d H:i:s") }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($order->status === "pending" && $order->payment_status === "pending")
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <a href="{{ route("payment.show", $order->order_number) }}" class="btn btn-primary">
                                <i class="fas fa-credit-card me-1"></i> 去支付
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- 订单详情 -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">订单详情</h5>
                </div>
                <div class="card-body">
                    @if($order->order_type === "subscription")
                        @if($order->subscription)
                            <div class="mb-4">
                                <h6>会员订阅信息</h6>
                                <div class="table-responsive">
                                    <table class="table">
                                        <tr>
                                            <th>会员等级</th>
                                            <td>{{ $order->subscription->membershipLevel->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>订阅编号</th>
                                            <td>{{ $order->subscription->subscription_no }}</td>
                                        </tr>
                                        <tr>
                                            <th>订阅类型</th>
                                            <td>{{ $order->subscription->subscription_type === "monthly" ? "月度" : "年度" }}</td>
                                        </tr>
                                        <tr>
                                            <th>开始日期</th>
                                            <td>{{ $order->subscription->start_date->format("Y-m-d") }}</td>
                                        </tr>
                                        <tr>
                                            <th>结束日期</th>
                                            <td>{{ $order->subscription->end_date->format("Y-m-d") }}</td>
                                        </tr>
                                        <tr>
                                            <th>自动续费</th>
                                            <td>{{ $order->subscription->auto_renew ? "是" : "否" }}</td>
                                        </tr>
                                        <tr>
                                            <th>状态</th>
                                            <td>
                                                @if($order->subscription->status === "active")
                                                    <span class="badge bg-success">有效</span>
                                                @elseif($order->subscription->status === "pending")
                                                    <span class="badge bg-warning">待处理</span>
                                                @elseif($order->subscription->status === "cancelled")
                                                    <span class="badge bg-danger">已取消</span>
                                                @elseif($order->subscription->status === "expired")
                                                    <span class="badge bg-secondary">已过期</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $order->subscription->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div>
                                <h6>会员特权</h6>
                                <div class="row">
                                    @foreach($order->subscription->membershipLevel->privileges as $privilege)
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="fas {{ $privilege->icon }} fa-lg text-primary"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-0">{{ $privilege->name }}</h6>
                                                    <small class="text-muted">{{ $privilege->pivot->value }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i> 订阅信息不存在
                            </div>
                        @endif
                    @elseif($order->order_type === "point")
                        <div class="mb-4">
                            <h6>积分购买信息</h6>
                            <div class="table-responsive">
                                <table class="table">
                                    <tr>
                                        <th>积分数量</th>
                                        <td>{{ $order->meta["points"] ?? 0 }} 积分</td>
                                    </tr>
                                    <tr>
                                        <th>单价</th>
                                        <td>￥{{ number_format(($order->meta["price_per_point"] ?? 0), 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    @elseif($order->order_type === "product")
                        <div class="mb-4">
                            <h6>产品购买信息</h6>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>产品名称</th>
                                            <th>数量</th>
                                            <th>单价</th>
                                            <th>小计</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ $order->meta["product_name"] ?? "" }}</td>
                                            <td>{{ $order->meta["quantity"] ?? 1 }}</td>
                                            <td>￥{{ number_format(($order->meta["price"] ?? 0), 2) }}</td>
                                            <td>￥{{ number_format(($order->meta["subtotal"] ?? 0), 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- 金额信息 -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">金额信息</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>小计</span>
                        <span>￥{{ number_format($order->subtotal_amount, 2) }}</span>
                    </div>
                    
                    @if($order->discount_amount > 0)
                        <div class="d-flex justify-content-between mb-3">
                            <span>折扣</span>
                            <span class="text-danger">-￥{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">总计</span>
                        <span class="fw-bold">￥{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
            
            <!-- 操作记录 -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">操作记录</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0">
                            <div class="d-flex justify-content-between">
                                <span>创建订单</span>
                                <span>{{ $order->created_at->format("Y-m-d H:i") }}</span>
                            </div>
                        </li>
                        
                        @if($order->payment_status === "paid")
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <span>支付完成</span>
                                    <span>{{ $order->paid_at->format("Y-m-d H:i") }}</span>
                                </div>
                            </li>
                        @endif
                        
                        @if($order->status === "completed")
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <span>订单完成</span>
                                    <span>{{ $order->updated_at->format("Y-m-d H:i") }}</span>
                                </div>
                            </li>
                        @endif
                        
                        @if($order->status === "cancelled")
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <span>订单取消</span>
                                    <span>{{ $order->updated_at->format("Y-m-d H:i") }}</span>
                                </div>
                            </li>
                        @endif
                        
                        @if($order->status === "failed")
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <span>订单失败</span>
                                    <span>{{ $order->updated_at->format("Y-m-d H:i") }}</span>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
