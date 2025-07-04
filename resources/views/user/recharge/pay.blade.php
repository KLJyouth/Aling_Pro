@extends("layouts.app")

@section("title", "支付订单")

@section("content")
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white text-center">
                    <h5 class="mb-0">订单支付</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="display-4 text-primary mb-3">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h4>订单金额: <span class="text-primary">{{ number_format($transaction->amount, 2) }}</span></h4>
                        <p class="text-muted">订单号: {{ $transaction->transaction_no }}</p>
                    </div>
                    
                    <div class="alert alert-info">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-info-circle fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="alert-heading">支付说明</h5>
                                <p class="mb-0">请使用 {{ $paymentMethod->name }} 完成支付，支付完成后系统将自动为您充值。</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mb-4">
                        @if($paymentMethod->code === "alipay")
                            <div class="mb-3" id="alipay-container">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">加载中...</span>
                                </div>
                                <p class="mt-2">正在加载支付宝支付...</p>
                            </div>
                        @elseif($paymentMethod->code === "wechat")
                            <div class="mb-3">
                                <div id="wechat-qrcode" class="d-inline-block p-3 border rounded"></div>
                                <p class="mt-2">请使用微信扫描二维码支付</p>
                            </div>
                        @elseif($paymentMethod->code === "paypal")
                            <div class="mb-3" id="paypal-button-container">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">加载中...</span>
                                </div>
                                <p class="mt-2">正在加载PayPal支付...</p>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> 暂不支持该支付方式
                            </div>
                        @endif
                    </div>
                    
                    <div class="text-center">
                        <div class="mb-3">
                            <span class="text-muted">支付遇到问题?</span>
                            <a href="#" id="refreshPayment" class="ms-2">刷新</a>
                            <span class="mx-2">|</span>
                            <a href="{{ route("user.recharge.index") }}">返回充值页面</a>
                        </div>
                        
                        <div class="mt-3">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status" id="checkingPayment">
                                    <span class="visually-hidden">检查中...</span>
                                </div>
                                <span id="paymentStatus">正在检查支付状态...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
@if($paymentMethod->code === "alipay")
    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 模拟支付宝支付
            setTimeout(function() {
                const container = document.getElementById("alipay-container");
                container.innerHTML = `
                    <form action="{{ $payParams["return_url"] }}" method="post" id="alipayForm">
                        <input type="hidden" name="out_trade_no" value="{{ $payParams["out_trade_no"] }}">
                        <button type="submit" class="btn btn-primary btn-lg">点击此处模拟支付完成</button>
                    </form>
                    <p class="mt-2 text-muted">实际环境中，将会跳转到支付宝支付页面</p>
                `;
            }, 2000);
            
            // 检查支付状态
            checkPaymentStatus();
        });
        
        function checkPaymentStatus() {
            const transactionId = "{{ $transaction->id }}";
            const checkInterval = setInterval(function() {
                fetch(`/api/payment/status/${transactionId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === "success") {
                            clearInterval(checkInterval);
                            document.getElementById("checkingPayment").style.display = "none";
                            document.getElementById("paymentStatus").innerHTML = "<span class=\"text-success\"><i class=\"fas fa-check-circle\"></i> 支付成功，正在跳转...</span>";
                            setTimeout(function() {
                                window.location.href = "{{ route("user.recharge.result", $transaction->id) }}";
                            }, 2000);
                        } else if (data.status === "failed") {
                            clearInterval(checkInterval);
                            document.getElementById("checkingPayment").style.display = "none";
                            document.getElementById("paymentStatus").innerHTML = "<span class=\"text-danger\"><i class=\"fas fa-times-circle\"></i> 支付失败</span>";
                        }
                    })
                    .catch(error => {
                        console.error("检查支付状态出错:", error);
                    });
            }, 3000);
        }
    </script>
@elseif($paymentMethod->code === "wechat")
    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 生成二维码
            new QRCode(document.getElementById("wechat-qrcode"), {
                text: "{{ $transaction->transaction_no }}",
                width: 200,
                height: 200,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
            
            // 检查支付状态
            checkPaymentStatus();
            
            // 刷新按钮
            document.getElementById("refreshPayment").addEventListener("click", function(e) {
                e.preventDefault();
                location.reload();
            });
        });
        
        function checkPaymentStatus() {
            const transactionId = "{{ $transaction->id }}";
            const checkInterval = setInterval(function() {
                fetch(`/api/payment/status/${transactionId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === "success") {
                            clearInterval(checkInterval);
                            document.getElementById("checkingPayment").style.display = "none";
                            document.getElementById("paymentStatus").innerHTML = "<span class=\"text-success\"><i class=\"fas fa-check-circle\"></i> 支付成功，正在跳转...</span>";
                            setTimeout(function() {
                                window.location.href = "{{ route("user.recharge.result", $transaction->id) }}";
                            }, 2000);
                        } else if (data.status === "failed") {
                            clearInterval(checkInterval);
                            document.getElementById("checkingPayment").style.display = "none";
                            document.getElementById("paymentStatus").innerHTML = "<span class=\"text-danger\"><i class=\"fas fa-times-circle\"></i> 支付失败</span>";
                        }
                    })
                    .catch(error => {
                        console.error("检查支付状态出错:", error);
                    });
            }, 3000);
        }
    </script>
@elseif($paymentMethod->code === "paypal")
    <script src="https://www.paypal.com/sdk/js?client-id=test&currency=USD"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // PayPal按钮
            paypal.Buttons({
                createOrder: function(data, actions) {
                    return actions.order.create({
                        purchase_units: [{
                            amount: {
                                value: "{{ $transaction->amount }}"
                            },
                            invoice_id: "{{ $transaction->transaction_no }}"
                        }]
                    });
                },
                onApprove: function(data, actions) {
                    return actions.order.capture().then(function(details) {
                        document.getElementById("checkingPayment").style.display = "none";
                        document.getElementById("paymentStatus").innerHTML = "<span class=\"text-success\"><i class=\"fas fa-check-circle\"></i> 支付成功，正在跳转...</span>";
                        
                        // 发送支付成功通知
                        fetch("/api/payment/notify/paypal", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify({
                                transaction_no: "{{ $transaction->transaction_no }}",
                                payment_id: data.orderID,
                                status: "COMPLETED"
                            })
                        }).then(function() {
                            setTimeout(function() {
                                window.location.href = "{{ route("user.recharge.result", $transaction->id) }}";
                            }, 2000);
                        });
                    });
                }
            }).render("#paypal-button-container");
            
            // 检查支付状态
            checkPaymentStatus();
        });
        
        function checkPaymentStatus() {
            const transactionId = "{{ $transaction->id }}";
            const checkInterval = setInterval(function() {
                fetch(`/api/payment/status/${transactionId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === "success") {
                            clearInterval(checkInterval);
                            document.getElementById("checkingPayment").style.display = "none";
                            document.getElementById("paymentStatus").innerHTML = "<span class=\"text-success\"><i class=\"fas fa-check-circle\"></i> 支付成功，正在跳转...</span>";
                            setTimeout(function() {
                                window.location.href = "{{ route("user.recharge.result", $transaction->id) }}";
                            }, 2000);
                        } else if (data.status === "failed") {
                            clearInterval(checkInterval);
                            document.getElementById("checkingPayment").style.display = "none";
                            document.getElementById("paymentStatus").innerHTML = "<span class=\"text-danger\"><i class=\"fas fa-times-circle\"></i> 支付失败</span>";
                        }
                    })
                    .catch(error => {
                        console.error("检查支付状态出错:", error);
                    });
            }, 3000);
        }
    </script>
@endif
@endsection
