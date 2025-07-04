@extends('layouts.user')

@section('title', '支付成功')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">支付成功</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">控制台</a></li>
        <li class="breadcrumb-item"><a href="{{ route('user.billing.packages') }}">套餐购买</a></li>
        <li class="breadcrumb-item active">支付成功</li>
    </ol>

    <div class="row justify-content-center">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-check-circle me-1"></i>
                    支付成功
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>
                    
                    <h4>恭喜您，支付已成功！</h4>
                    <p class="mb-4">您的订单已处理完成，系统已为您分配相应的额度或会员权益</p>
                    
                    <div class="alert alert-info">
                        <h5>订单信息</h5>
                        <p>订单号：{{ $order->order_number }}</p>
                        <p>套餐名称：{{ $order->package_name }}</p>
                        <p>支付金额：<span class="text-danger fw-bold">{{ number_format($order->total_amount, 2) }}</span></p>
                        <p>支付时间：{{ $order->paid_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                    
                    <div class="mt-4">
                        @if($order->items->first()->package_type == 'quota')
                            <p>您当前的剩余额度为：<span class="text-primary fw-bold">{{ auth()->user()->quota }}</span></p>
                            <a href="{{ route('user.billing.quota') }}" class="btn btn-primary">
                                <i class="fas fa-chart-pie"></i> 查看额度详情
                            </a>
                        @elseif($order->items->first()->package_type == 'membership')
                            <p>您的会员有效期至：<span class="text-primary fw-bold">{{ auth()->user()->member_expired_at->format('Y-m-d') }}</span></p>
                            <a href="{{ route('user.membership.index') }}" class="btn btn-primary">
                                <i class="fas fa-crown"></i> 查看会员详情
                            </a>
                        @endif
                        
                        <a href="{{ route('user.billing.orders') }}" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-list"></i> 查看订单记录
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
