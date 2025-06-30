@extends("layouts.app")

@section("title", "支付订单")

@section("content")
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">订单支付</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h4>订单编号：{{ $order->order_number }}</h4>
                        <h2 class="text-primary">￥{{ number_format($order->total_amount, 2) }}</h2>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>订单类型：</strong> 
                                @if($order->order_type === "subscription")
                                    会员订阅
                                @elseif($order->order_type === "point")
                                    积分购买
                                @elseif($order->order_type === "product")
                                    产品购买
                                @else
                                    {{ $order->order_type }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>创建时间：</strong> {{ $order->created_at->format("Y-m-d H:i:s") }}</p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="payment-methods mb-4">
                        <h5 class="mb-3">选择支付方式</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 payment-method-card {{ $order->payment_method === "alipay" ? "selected" : "" }}" data-method="alipay">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="alipay" value="alipay" {{ $order->payment_method === "alipay" ? "checked" : "" }}>
                                            <label class="form-check-label" for="alipay">
                                                <img src="{{ asset("images/payment/alipay.png") }}" alt="支付宝" height="30">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 payment-method-card {{ $order->payment_method === "wechat" ? "selected" : "" }}" data-method="wechat">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="wechat" value="wechat" {{ $order->payment_method === "wechat" ? "checked" : "" }}>
                                            <label class="form-check-label" for="wechat">
                                                <img src="{{ asset("images/payment/wechat.png") }}" alt="微信支付" height="30">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 payment-method-card {{ $order->payment_method === "card" ? "selected" : "" }}" data-method="card">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="card" value="card" {{ $order->payment_method === "card" ? "checked" : "" }}>
                                            <label class="form-check-label" for="card">
                                                <img src="{{ asset("images/payment/card.png") }}" alt="银行卡" height="30">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="paymentContent" class="text-center">
                        <div id="qrCodeContainer" class="mb-4" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> 请使用手机扫描下方二维码完成支付
                            </div>
                            <div class="mb-3">
                                <img id="qrCode" src="" alt="支付二维码" class="img-fluid" style="max-width: 200px;">
                            </div>
                        </div>
                        
                        <div id="cardPaymentForm" style="display: none;">
                            <form id="paymentForm" class="mb-4">
                                <div class="mb-3">
                                    <label for="cardNumber" class="form-label">卡号</label>
                                    <input type="text" class="form-control" id="cardNumber" placeholder="1234 5678 9012 3456" required>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="expiryDate" class="form-label">有效期</label>
                                        <input type="text" class="form-control" id="expiryDate" placeholder="MM/YY" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cvv" class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="cvv" placeholder="123" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="cardholderName" class="form-label">持卡人姓名</label>
                                    <input type="text" class="form-control" id="cardholderName" placeholder="张三" required>
                                </div>
                            </form>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button id="payButton" class="btn btn-primary btn-lg">
                                <i class="fas fa-lock me-1"></i> 立即支付
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> 返回
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">订单详情</h5>
                </div>
                <div class="card-body">
                    @if($order->order_type === "subscription")
                        <div class="mb-3">
                            <strong>会员等级：</strong> {{ $order->subscription->membershipLevel->name }}
                        </div>
                        <div class="mb-3">
                            <strong>订阅类型：</strong> {{ $order->subscription->subscription_type === "monthly" ? "月度" : "年度" }}
                        </div>
                        <div class="mb-3">
                            <strong>有效期：</strong> {{ $order->subscription->start_date->format("Y-m-d") }} 至 {{ $order->subscription->end_date->format("Y-m-d") }}
                        </div>
                    @elseif($order->order_type === "point")
                        <div class="mb-3">
                            <strong>积分数量：</strong> {{ $order->meta["points"] ?? 0 }} 积分
                        </div>
                    @elseif($order->order_type === "product")
                        <div class="mb-3">
                            <strong>产品名称：</strong> {{ $order->meta["product_name"] ?? "" }}
                        </div>
                        <div class="mb-3">
                            <strong>产品数量：</strong> {{ $order->meta["quantity"] ?? 1 }}
                        </div>
                    @endif
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>小计</span>
                        <span>￥{{ number_format($order->subtotal_amount, 2) }}</span>
                    </div>
                    
                    @if($order->discount_amount > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span>折扣</span>
                            <span>-￥{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    
                    <div class="d-flex justify-content-between fw-bold">
                        <span>总计</span>
                        <span>￥{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">支付说明</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-shield-alt text-primary me-2"></i> 支付过程采用SSL加密，确保您的支付信息安全
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-primary me-2"></i> 支付成功后，订单将立即生效
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-history text-primary me-2"></i> 支付记录可在个人中心查看
                        </li>
                        <li>
                            <i class="fas fa-question-circle text-primary me-2"></i> 如有问题，请联系客服
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 支付处理中模态框 -->
<div class="modal fade" id="processingModal" tabindex="-1" aria-labelledby="processingModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 id="processingModalLabel">支付处理中，请稍候...</h5>
                <p class="text-muted">请不要关闭此页面</p>
            </div>
        </div>
    </div>
</div>

<!-- 支付成功模态框 -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success fa-4x"></i>
                </div>
                <h5 id="successModalLabel">支付成功！</h5>
                <p>您的订单已支付成功，感谢您的购买。</p>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="{{ route("dashboard") }}" class="btn btn-primary">返回仪表盘</a>
                <a href="#" id="viewOrderButton" class="btn btn-outline-primary">查看订单</a>
            </div>
        </div>
    </div>
</div>

<!-- 支付失败模态框 -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-times-circle text-danger fa-4x"></i>
                </div>
                <h5 id="errorModalLabel">支付失败</h5>
                <p id="errorMessage">处理您的支付时出现错误，请稍后再试。</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">重试</button>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">返回</a>
            </div>
        </div>
    </div>
</div>

<style>
    .payment-method-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .payment-method-card.selected {
        border-color: #0d6efd !important;
        background-color: rgba(13, 110, 253, 0.05);
    }
</style>

@section("scripts")
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 支付方式选择
        const paymentMethodRadios = document.querySelectorAll(".payment-method-radio");
        const paymentMethodCards = document.querySelectorAll(".payment-method-card");
        const qrCodeContainer = document.getElementById("qrCodeContainer");
        const cardPaymentForm = document.getElementById("cardPaymentForm");
        const qrCodeImage = document.getElementById("qrCode");
        const payButton = document.getElementById("payButton");
        
        let selectedMethod = "{{ $order->payment_method ?: "alipay" }}";
        
        // 初始化选中状态
        updatePaymentUI();
        
        paymentMethodRadios.forEach(radio => {
            radio.addEventListener("change", function() {
                selectedMethod = this.value;
                
                paymentMethodCards.forEach(card => {
                    if (card.dataset.method === selectedMethod) {
                        card.classList.add("selected");
                    } else {
                        card.classList.remove("selected");
                    }
                });
                
                updatePaymentUI();
            });
        });
        
        // 点击卡片也可以选择支付方式
        paymentMethodCards.forEach(card => {
            card.addEventListener("click", function() {
                const method = this.dataset.method;
                const radio = document.querySelector(".payment-method-radio[value='" + method + "']");
                radio.checked = true;
                
                selectedMethod = method;
                
                paymentMethodCards.forEach(c => {
                    c.classList.remove("selected");
                });
                this.classList.add("selected");
                
                updatePaymentUI();
            });
        });
        
        // 更新支付UI
        function updatePaymentUI() {
            if (selectedMethod === "alipay" || selectedMethod === "wechat") {
                qrCodeContainer.style.display = "block";
                cardPaymentForm.style.display = "none";
                payButton.textContent = "立即支付";
            } else if (selectedMethod === "card") {
                qrCodeContainer.style.display = "none";
                cardPaymentForm.style.display = "block";
                payButton.textContent = "确认支付";
            }
        }
        
        // 支付按钮点击事件
        payButton.addEventListener("click", function() {
            // 显示处理中模态框
            const processingModal = new bootstrap.Modal(document.getElementById("processingModal"));
            processingModal.show();
            
            // 发送支付请求
            fetch("{{ route("payment.process", $order->order_number) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    payment_method: selectedMethod
                })
            })
            .then(response => response.json())
            .then(data => {
                processingModal.hide();
                
                if (data.success) {
                    if (selectedMethod === "alipay" || selectedMethod === "wechat") {
                        // 显示二维码
                        qrCodeImage.src = data.qr_code;
                        qrCodeContainer.style.display = "block";
                        
                        // 开始轮询支付状态
                        startPollingPaymentStatus();
                    } else if (selectedMethod === "card") {
                        // 模拟银行卡支付处理
                        setTimeout(() => {
                            // 显示支付成功模态框
                            const successModal = new bootstrap.Modal(document.getElementById("successModal"));
                            document.getElementById("viewOrderButton").href = "/orders/" + {{ $order->id }};
                            successModal.show();
                        }, 2000);
                    }
                } else {
                    // 显示错误模态框
                    const errorModal = new bootstrap.Modal(document.getElementById("errorModal"));
                    document.getElementById("errorMessage").textContent = data.message;
                    errorModal.show();
                }
            })
            .catch(error => {
                processingModal.hide();
                
                // 显示错误模态框
                const errorModal = new bootstrap.Modal(document.getElementById("errorModal"));
                document.getElementById("errorMessage").textContent = "支付请求失败，请稍后再试";
                errorModal.show();
                
                console.error("支付请求失败:", error);
            });
        });
        
        // 轮询支付状态
        function startPollingPaymentStatus() {
            const pollingInterval = 3000; // 3秒
            let pollingCount = 0;
            const maxPollingCount = 60; // 最多轮询60次（3分钟）
            
            const polling = setInterval(() => {
                pollingCount++;
                
                if (pollingCount > maxPollingCount) {
                    clearInterval(polling);
                    return;
                }
                
                // 查询支付状态
                fetch("{{ route("payment.query", $order->order_number) }}", {
                    method: "GET",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.status === "paid") {
                            // 支付成功，停止轮询
                            clearInterval(polling);
                            
                            // 显示支付成功模态框
                            const successModal = new bootstrap.Modal(document.getElementById("successModal"));
                            document.getElementById("viewOrderButton").href = data.redirect;
                            successModal.show();
                        }
                    } else {
                        // 查询失败，停止轮询
                        clearInterval(polling);
                        
                        // 显示错误模态框
                        const errorModal = new bootstrap.Modal(document.getElementById("errorModal"));
                        document.getElementById("errorMessage").textContent = data.message;
                        errorModal.show();
                    }
                })
                .catch(error => {
                    console.error("查询支付状态失败:", error);
                });
            }, pollingInterval);
        }
    });
</script>
@endsection
@endsection
