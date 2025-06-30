@extends("layouts.app")

@section("title", "升级会员")

@section("content")
<div class="container py-4">
    <h1 class="h3 mb-4">升级会员</h1>
    
    @if(session("error"))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session("error") }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <div class="row">
        <div class="col-lg-8">
            <!-- 会员等级选择 -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">选择会员等级</h5>
                </div>
                <div class="card-body">
                    <form id="upgradeForm" action="{{ route("subscription.upgrade.process") }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <div class="row">
                                @foreach($levels as $level)
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 {{ $currentLevel && $currentLevel->id === $level->id ? "border-primary" : "border" }}">
                                            <div class="card-header {{ $currentLevel && $currentLevel->id === $level->id ? "bg-primary text-white" : "bg-light" }}">
                                                <div class="form-check">
                                                    <input class="form-check-input level-radio" type="radio" name="level_id" id="level{{ $level->id }}" value="{{ $level->id }}" {{ $currentLevel && $currentLevel->id === $level->id ? "checked" : "" }}>
                                                    <label class="form-check-label" for="level{{ $level->id }}">
                                                        <strong>{{ $level->name }}</strong>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="text-center mb-3">
                                                    <div class="membership-icon mx-auto mb-2" style="background-color: {{ $level->color }}">
                                                        <i class="fas {{ $level->icon }} fa-lg text-white"></i>
                                                    </div>
                                                    <p class="text-muted small">{{ $level->description }}</p>
                                                </div>
                                                
                                                <ul class="list-unstyled mb-0 small">
                                                    @foreach(json_decode($level->benefits) as $benefit)
                                                        <li class="mb-2">
                                                            <i class="fas fa-check text-success me-1"></i> {{ $benefit }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="card-footer bg-white">
                                                <div class="text-center">
                                                    <div class="mb-2">
                                                        <span class="h5">￥{{ $level->formatted_monthly_price }}</span>
                                                        <span class="text-muted">/月</span>
                                                    </div>
                                                    <div>
                                                        <span class="h5">￥{{ $level->formatted_yearly_price }}</span>
                                                        <span class="text-muted">/年</span>
                                                        <span class="badge bg-success ms-1">省{{ $level->yearly_savings_percent }}%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            @error("level_id")
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="mb-3">选择订阅周期</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <div class="card h-100 border subscription-type-card" data-type="monthly">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input subscription-type-radio" type="radio" name="subscription_type" id="monthly" value="monthly" checked>
                                                <label class="form-check-label" for="monthly">
                                                    <strong>月度订阅</strong>
                                                </label>
                                            </div>
                                            <p class="text-muted small mt-2 mb-0">每月自动续费，可随时取消</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 border subscription-type-card" data-type="yearly">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input subscription-type-radio" type="radio" name="subscription_type" id="yearly" value="yearly">
                                                <label class="form-check-label" for="yearly">
                                                    <strong>年度订阅</strong>
                                                    <span class="badge bg-success ms-1">省20%</span>
                                                </label>
                                            </div>
                                            <p class="text-muted small mt-2 mb-0">一次性支付全年费用，相当于获得2个月免费使用期</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @error("subscription_type")
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="mb-3">选择支付方式</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border payment-method-card" data-method="alipay">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="alipay" value="alipay" checked>
                                                <label class="form-check-label" for="alipay">
                                                    <img src="{{ asset("images/payment/alipay.png") }}" alt="支付宝" height="30">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border payment-method-card" data-method="wechat">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="wechat" value="wechat">
                                                <label class="form-check-label" for="wechat">
                                                    <img src="{{ asset("images/payment/wechat.png") }}" alt="微信支付" height="30">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card h-100 border payment-method-card" data-method="card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="card" value="card">
                                                <label class="form-check-label" for="card">
                                                    <img src="{{ asset("images/payment/card.png") }}" alt="银行卡" height="30">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @error("payment_method")
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="auto_renew" id="autoRenew" checked>
                                <label class="form-check-label" for="autoRenew">
                                    启用自动续费（可随时取消）
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input @error("agree_terms") is-invalid @enderror" type="checkbox" name="agree_terms" id="agreeTerms" {{ old("agree_terms") ? "checked" : "" }} required>
                                <label class="form-check-label" for="agreeTerms">
                                    我已阅读并同意 <a href="{{ route("terms") }}" target="_blank">服务条款</a> 和 <a href="{{ route("privacy") }}" target="_blank">隐私政策</a>
                                </label>
                                @error("agree_terms")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-shopping-cart me-1"></i> 立即支付
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- 订单摘要 -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">订单摘要</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>会员等级</span>
                        <span id="summaryLevel">{{ $levels[0]->name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>订阅周期</span>
                        <span id="summaryType">月度</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>支付方式</span>
                        <span id="summaryPayment">支付宝</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>自动续费</span>
                        <span id="summaryAutoRenew">是</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span>小计</span>
                        <span id="summarySubtotal">￥{{ $levels[0]->formatted_monthly_price }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>折扣</span>
                        <span id="summaryDiscount">￥0.00</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">总计</span>
                        <span class="fw-bold" id="summaryTotal">￥{{ $levels[0]->formatted_monthly_price }}</span>
                    </div>
                </div>
            </div>
            
            <!-- 常见问题 -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">常见问题</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    如何选择适合我的会员等级？
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    根据您的使用需求选择合适的会员等级。如果您需要更多的API调用次数、更多的AI模型访问权限或更大的存储空间，可以选择更高级别的会员。您可以随时升级或降级您的会员等级。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    月度订阅和年度订阅有什么区别？
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    月度订阅每月自动续费，可以随时取消。年度订阅一次性支付全年费用，相比月度订阅可以节省约20%的费用，相当于获得2个月的免费使用期。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    我可以随时取消订阅吗？
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    是的，您可以随时取消订阅。取消后，您的会员资格将持续到当前订阅期结束。我们不提供按比例退款，但您可以继续使用服务直到订阅期结束。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    支付安全吗？
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    是的，我们使用行业标准的加密技术保护您的支付信息。我们不会存储您的完整信用卡信息，所有支付处理都通过安全的第三方支付处理商完成。
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .membership-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .payment-method-card, .subscription-type-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .payment-method-card.selected, .subscription-type-card.selected {
        border-color: #0d6efd !important;
        background-color: rgba(13, 110, 253, 0.05);
    }
</style>

@section("scripts")
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 会员等级选择
        const levelRadios = document.querySelectorAll(".level-radio");
        const levelCards = document.querySelectorAll(".card");
        
        levelRadios.forEach(radio => {
            radio.addEventListener("change", function() {
                updateOrderSummary();
            });
        });
        
        // 订阅周期选择
        const typeRadios = document.querySelectorAll(".subscription-type-radio");
        const typeCards = document.querySelectorAll(".subscription-type-card");
        
        typeRadios.forEach(radio => {
            radio.addEventListener("change", function() {
                typeCards.forEach(card => {
                    if (card.dataset.type === radio.value) {
                        card.classList.add("selected");
                    } else {
                        card.classList.remove("selected");
                    }
                });
                updateOrderSummary();
            });
        });
        
        // 支付方式选择
        const paymentRadios = document.querySelectorAll(".payment-method-radio");
        const paymentCards = document.querySelectorAll(".payment-method-card");
        
        paymentRadios.forEach(radio => {
            radio.addEventListener("change", function() {
                paymentCards.forEach(card => {
                    if (card.dataset.method === radio.value) {
                        card.classList.add("selected");
                    } else {
                        card.classList.remove("selected");
                    }
                });
                updateOrderSummary();
            });
        });
        
        // 自动续费选择
        const autoRenewCheckbox = document.getElementById("autoRenew");
        autoRenewCheckbox.addEventListener("change", function() {
            updateOrderSummary();
        });
        
        // 初始化选中状态
        document.querySelector(".subscription-type-card[data-type='monthly']").classList.add("selected");
        document.querySelector(".payment-method-card[data-method='alipay']").classList.add("selected");
        
        // 更新订单摘要
        function updateOrderSummary() {
            // 获取选中的会员等级
            const selectedLevelRadio = document.querySelector(".level-radio:checked");
            const selectedLevelId = selectedLevelRadio ? selectedLevelRadio.value : "";
            const selectedLevelName = selectedLevelRadio ? selectedLevelRadio.closest(".card").querySelector(".form-check-label strong").textContent.trim() : "";
            
            // 获取选中的订阅周期
            const selectedType = document.querySelector(".subscription-type-radio:checked").value;
            const selectedTypeName = selectedType === "monthly" ? "月度" : "年度";
            
            // 获取选中的支付方式
            const selectedPayment = document.querySelector(".payment-method-radio:checked").value;
            let selectedPaymentName = "支付宝";
            if (selectedPayment === "wechat") selectedPaymentName = "微信支付";
            if (selectedPayment === "card") selectedPaymentName = "银行卡";
            
            // 获取自动续费状态
            const autoRenew = document.getElementById("autoRenew").checked;
            
            // 更新摘要
            document.getElementById("summaryLevel").textContent = selectedLevelName;
            document.getElementById("summaryType").textContent = selectedTypeName;
            document.getElementById("summaryPayment").textContent = selectedPaymentName;
            document.getElementById("summaryAutoRenew").textContent = autoRenew ? "是" : "否";
            
            // 计算价格
            // 这里应该通过AJAX获取实际价格，为了演示，我们使用前端计算
            // 假设有一个价格表
            const prices = {
                @foreach($levels as $level)
                    "{{ $level->id }}": {
                        "monthly": {{ $level->price_monthly }},
                        "yearly": {{ $level->price_yearly }}
                    },
                @endforeach
            };
            
            if (selectedLevelId && prices[selectedLevelId]) {
                const price = prices[selectedLevelId][selectedType];
                document.getElementById("summarySubtotal").textContent = "￥" + price.toFixed(2);
                document.getElementById("summaryTotal").textContent = "￥" + price.toFixed(2);
            }
        }
        
        // 初始化订单摘要
        updateOrderSummary();
    });
</script>
@endsection
@endsection
