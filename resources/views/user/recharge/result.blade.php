@extends("layouts.app")

@section("title", "支付结果")

@section("content")
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white text-center">
                    <h5 class="mb-0">支付结果</h5>
                </div>
                <div class="card-body text-center">
                    @if($transaction->status === "success")
                        <div class="mb-4">
                            <div class="display-1 text-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3 class="mt-3">支付成功</h3>
                            <p class="text-muted">您的账户已成功充值 {{ number_format($transaction->amount, 2) }}</p>
                            
                            @if($transaction->points > 0)
                                <div class="alert alert-success d-inline-block">
                                    <i class="fas fa-gift"></i> 已赠送 {{ number_format($transaction->points) }} 积分
                                </div>
                            @endif
                        </div>
                    @elseif($transaction->status === "pending" || $transaction->status === "processing")
                        <div class="mb-4">
                            <div class="display-1 text-warning">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h3 class="mt-3">支付处理中</h3>
                            <p class="text-muted">系统正在处理您的支付，请稍候...</p>
                            
                            <div class="d-flex justify-content-center align-items-center mt-3">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                    <span class="visually-hidden">处理中...</span>
                                </div>
                                <span>正在检查支付状态，请勿关闭页面</span>
                            </div>
                        </div>
                    @else
                        <div class="mb-4">
                            <div class="display-1 text-danger">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <h3 class="mt-3">支付失败</h3>
                            <p class="text-muted">
                                抱歉，您的支付未能完成
                                @if(isset($transaction->metadata["failure_reason"]))
                                    <br>原因: {{ $transaction->metadata["failure_reason"] }}
                                @endif
                            </p>
                        </div>
                    @endif
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">订单信息</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-end fw-bold">订单号:</td>
                                    <td class="text-start">{{ $transaction->transaction_no }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-bold">金额:</td>
                                    <td class="text-start">{{ number_format($transaction->amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-bold">支付方式:</td>
                                    <td class="text-start">{{ $transaction->payment_method }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-bold">创建时间:</td>
                                    <td class="text-start">{{ $transaction->created_at }}</td>
                                </tr>
                                @if($transaction->completed_at)
                                    <tr>
                                        <td class="text-end fw-bold">完成时间:</td>
                                        <td class="text-start">{{ $transaction->completed_at }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    
                    <div>
                        <a href="{{ route("user.recharge.index") }}" class="btn btn-primary">返回充值页面</a>
                        <a href="{{ route("user.recharge.records") }}" class="btn btn-outline-secondary ms-2">查看充值记录</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
@if($transaction->status === "pending" || $transaction->status === "processing")
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const transactionId = "{{ $transaction->id }}";
        
        // 每3秒检查一次支付状态
        const checkInterval = setInterval(function() {
            fetch(`/api/payment/status/${transactionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status !== "pending" && data.status !== "processing") {
                        clearInterval(checkInterval);
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error("检查支付状态出错:", error);
                });
        }, 3000);
    });
</script>
@endif
@endsection
