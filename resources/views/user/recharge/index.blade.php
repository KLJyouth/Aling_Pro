@extends("layouts.app")

@section("title", "账户充值")

@section("styles")
<style>
    .package-card {
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }
    .package-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    .package-card.selected {
        border-color: #4e73df;
        background-color: rgba(78, 115, 223, 0.05);
    }
    .package-popular {
        position: absolute;
        top: -10px;
        right: 10px;
        background-color: #f6c23e;
        color: #fff;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }
    .package-limited {
        position: absolute;
        top: -10px;
        left: 10px;
        background-color: #e74a3b;
        color: #fff;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }
    .payment-method {
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }
    .payment-method:hover {
        background-color: #f8f9fc;
    }
    .payment-method.selected {
        border-color: #4e73df;
        background-color: rgba(78, 115, 223, 0.05);
    }
    .payment-method img {
        max-height: 40px;
        max-width: 100px;
    }
    .wallet-card {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
    }
</style>
@endsection

@section("content")
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            @include("user.partials.sidebar")
        </div>
        <div class="col-md-9">
            <!-- 钱包卡片 -->
            <div class="card wallet-card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-1">账户余额</h5>
                            <h2 class="mb-3"> {{ number_format($wallet->balance, 2) }}</h2>
                            <p class="mb-0">
                                <i class="fas fa-coins"></i> 积分: {{ number_format($wallet->points) }}
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <a href="{{ route("user.recharge.records") }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-history"></i> 充值记录
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @if(session("success"))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session("success") }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
                </div>
            @endif
            
            @if(session("error"))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session("error") }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
                </div>
            @endif
            
            <form method="POST" action="{{ route("user.recharge.create") }}" id="rechargeForm">
                @csrf
                <input type="hidden" name="package_id" id="selected_package_id" value="{{ old("package_id") }}">
                <input type="hidden" name="payment_method_id" id="selected_payment_method_id" value="{{ old("payment_method_id") }}">
                
                <!-- 充值套餐 -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">选择充值套餐</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($packages as $package)
                                <div class="col-md-4 mb-4">
                                    <div class="card package-card h-100 position-relative" data-id="{{ $package->id }}" data-amount="{{ $package->amount }}">
                                        @if($package->is_popular)
                                            <div class="package-popular">推荐</div>
                                        @endif
                                        
                                        @if($package->is_limited_time && $package->isLimitedTimeOffer())
                                            <div class="package-limited">限时</div>
                                        @endif
                                        
                                        <div class="card-body text-center">
                                            @if($package->icon)
                                                <img src="{{ $package->icon }}" alt="{{ $package->name }}" class="mb-3" style="max-height: 60px;">
                                            @else
                                                <div class="display-4 mb-3 text-primary">
                                                    <i class="fas fa-coins"></i>
                                                </div>
                                            @endif
                                            
                                            <h5 class="card-title">{{ $package->name }}</h5>
                                            
                                            <div class="mb-2">
                                                <span class="h4 text-primary">{{ number_format($package->amount, 2) }}</span>
                                                @if($package->original_amount && $package->original_amount > $package->amount)
                                                    <span class="text-muted text-decoration-line-through ms-2">{{ number_format($package->original_amount, 2) }}</span>
                                                @endif
                                            </div>
                                            
                                            @if($package->bonus_points > 0)
                                                <div class="badge bg-success mb-2">赠送 {{ number_format($package->bonus_points) }} 积分</div>
                                            @endif
                                            
                                            @if($package->description)
                                                <p class="card-text small text-muted">{{ $package->description }}</p>
                                            @endif
                                            
                                            @if($package->is_limited_time && $package->isLimitedTimeOffer() && $package->limited_end_at)
                                                <div class="mt-2 small text-danger">
                                                    <i class="fas fa-clock"></i> 剩余: <span class="countdown" data-end="{{ $package->limited_end_at->timestamp }}"></span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                            <div class="col-md-4 mb-4">
                                <div class="card package-card h-100 custom-amount-card" data-id="custom">
                                    <div class="card-body text-center">
                                        <div class="display-4 mb-3 text-secondary">
                                            <i class="fas fa-edit"></i>
                                        </div>
                                        
                                        <h5 class="card-title">自定义金额</h5>
                                        
                                        <div class="mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text"></span>
                                                <input type="number" class="form-control" id="custom_amount" name="amount" min="1" step="0.01" value="{{ old("amount") }}" placeholder="输入金额">
                                            </div>
                                        </div>
                                        
                                        <p class="card-text small text-muted">输入您想要充值的金额</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 支付方式 -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">选择支付方式</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($paymentMethods as $method)
                                <div class="col-md-4 mb-3">
                                    <div class="card payment-method h-100" data-id="{{ $method->id }}">
                                        <div class="card-body d-flex align-items-center">
                                            @if($method->icon)
                                                <img src="{{ $method->icon }}" alt="{{ $method->name }}" class="me-3">
                                            @else
                                                <div class="text-primary me-3">
                                                    <i class="fas fa-credit-card fa-2x"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-0">{{ $method->name }}</h6>
                                                @if($method->fee_type && $method->fee_value > 0)
                                                    <small class="text-muted">
                                                        @if($method->fee_type === "fixed")
                                                            手续费: {{ number_format($method->fee_value, 2) }}
                                                        @elseif($method->fee_type === "percentage")
                                                            手续费: {{ $method->fee_value }}%
                                                        @endif
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <!-- 提交按钮 -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                        <i class="fas fa-shopping-cart"></i> 立即支付 <span id="totalAmount">0.00</span>
                    </button>
                </div>
            </form>
            
            <!-- 最近交易记录 -->
            @if($transactions->isNotEmpty())
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">最近充值记录</h5>
                        <a href="{{ route("user.recharge.records") }}" class="btn btn-sm btn-outline-primary">查看全部</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>订单号</th>
                                    <th>金额</th>
                                    <th>支付方式</th>
                                    <th>状态</th>
                                    <th>时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->transaction_no }}</td>
                                        <td>{{ number_format($transaction->amount, 2) }}</td>
                                        <td>{{ $transaction->paymentMethod->name ?? $transaction->payment_method }}</td>
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
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let selectedPackage = null;
        let selectedPaymentMethod = null;
        let customAmount = 0;
        
        // 初始化选中状态
        const initSelectedPackage = function() {
            const packageId = document.getElementById("selected_package_id").value;
            if (packageId) {
                const packageCard = document.querySelector(`.package-card[data-id="${packageId}"]`);
                if (packageCard) {
                    selectPackage(packageCard);
                }
            }
        };
        
        const initSelectedPaymentMethod = function() {
            const paymentMethodId = document.getElementById("selected_payment_method_id").value;
            if (paymentMethodId) {
                const paymentMethodCard = document.querySelector(`.payment-method[data-id="${paymentMethodId}"]`);
                if (paymentMethodCard) {
                    selectPaymentMethod(paymentMethodCard);
                }
            }
        };
        
        // 选择充值套餐
        const selectPackage = function(card) {
            // 移除其他套餐的选中状态
            document.querySelectorAll(".package-card").forEach(function(el) {
                el.classList.remove("selected");
            });
            
            // 添加当前套餐的选中状态
            card.classList.add("selected");
            
            // 设置选中的套餐ID
            const packageId = card.getAttribute("data-id");
            if (packageId === "custom") {
                document.getElementById("selected_package_id").value = "";
                customAmount = parseFloat(document.getElementById("custom_amount").value) || 0;
            } else {
                document.getElementById("selected_package_id").value = packageId;
                customAmount = 0;
                document.getElementById("custom_amount").value = "";
            }
            
            selectedPackage = packageId;
            updateTotalAmount();
            checkFormValidity();
        };
        
        // 选择支付方式
        const selectPaymentMethod = function(card) {
            // 移除其他支付方式的选中状态
            document.querySelectorAll(".payment-method").forEach(function(el) {
                el.classList.remove("selected");
            });
            
            // 添加当前支付方式的选中状态
            card.classList.add("selected");
            
            // 设置选中的支付方式ID
            const paymentMethodId = card.getAttribute("data-id");
            document.getElementById("selected_payment_method_id").value = paymentMethodId;
            
            selectedPaymentMethod = paymentMethodId;
            checkFormValidity();
        };
        
        // 更新总金额
        const updateTotalAmount = function() {
            let amount = 0;
            
            if (selectedPackage === "custom") {
                amount = customAmount;
            } else if (selectedPackage) {
                const packageCard = document.querySelector(`.package-card[data-id="${selectedPackage}"]`);
                if (packageCard) {
                    amount = parseFloat(packageCard.getAttribute("data-amount")) || 0;
                }
            }
            
            document.getElementById("totalAmount").textContent = `${amount.toFixed(2)}`;
        };
        
        // 检查表单有效性
        const checkFormValidity = function() {
            const isValid = (
                (selectedPackage === "custom" && customAmount > 0) || 
                (selectedPackage && selectedPackage !== "custom")
            ) && selectedPaymentMethod;
            
            document.getElementById("submitBtn").disabled = !isValid;
        };
        
        // 绑定套餐卡片点击事件
        document.querySelectorAll(".package-card").forEach(function(card) {
            card.addEventListener("click", function() {
                selectPackage(this);
            });
        });
        
        // 绑定支付方式卡片点击事件
        document.querySelectorAll(".payment-method").forEach(function(card) {
            card.addEventListener("click", function() {
                selectPaymentMethod(this);
            });
        });
        
        // 绑定自定义金额输入事件
        document.getElementById("custom_amount").addEventListener("input", function() {
            customAmount = parseFloat(this.value) || 0;
            if (selectedPackage === "custom") {
                updateTotalAmount();
                checkFormValidity();
            }
        });
        
        // 倒计时功能
        const updateCountdowns = function() {
            document.querySelectorAll(".countdown").forEach(function(el) {
                const endTime = parseInt(el.getAttribute("data-end")) * 1000;
                const now = new Date().getTime();
                const distance = endTime - now;
                
                if (distance <= 0) {
                    el.textContent = "已结束";
                    return;
                }
                
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                let timeStr = "";
                if (days > 0) {
                    timeStr += `${days}天 `;
                }
                timeStr += `${hours.toString().padStart(2, "0")}:${minutes.toString().padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`;
                
                el.textContent = timeStr;
            });
        };
        
        // 初始化
        initSelectedPackage();
        initSelectedPaymentMethod();
        updateCountdowns();
        
        // 每秒更新倒计时
        setInterval(updateCountdowns, 1000);
    });
</script>
@endsection
