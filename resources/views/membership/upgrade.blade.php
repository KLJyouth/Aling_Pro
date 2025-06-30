@extends("layouts.app")

@section("title", "������Ա")

@section("content")
<div class="container py-4">
    <h1 class="h3 mb-4">������Ա</h1>
    
    @if(session("error"))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session("error") }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <div class="row">
        <div class="col-lg-8">
            <!-- ��Ա�ȼ�ѡ�� -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">ѡ���Ա�ȼ�</h5>
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
                                                        <span class="h5">��{{ $level->formatted_monthly_price }}</span>
                                                        <span class="text-muted">/��</span>
                                                    </div>
                                                    <div>
                                                        <span class="h5">��{{ $level->formatted_yearly_price }}</span>
                                                        <span class="text-muted">/��</span>
                                                        <span class="badge bg-success ms-1">ʡ{{ $level->yearly_savings_percent }}%</span>
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
                            <h5 class="mb-3">ѡ��������</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <div class="card h-100 border subscription-type-card" data-type="monthly">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input subscription-type-radio" type="radio" name="subscription_type" id="monthly" value="monthly" checked>
                                                <label class="form-check-label" for="monthly">
                                                    <strong>�¶ȶ���</strong>
                                                </label>
                                            </div>
                                            <p class="text-muted small mt-2 mb-0">ÿ���Զ����ѣ�����ʱȡ��</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 border subscription-type-card" data-type="yearly">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input subscription-type-radio" type="radio" name="subscription_type" id="yearly" value="yearly">
                                                <label class="form-check-label" for="yearly">
                                                    <strong>��ȶ���</strong>
                                                    <span class="badge bg-success ms-1">ʡ20%</span>
                                                </label>
                                            </div>
                                            <p class="text-muted small mt-2 mb-0">һ����֧��ȫ����ã��൱�ڻ��2�������ʹ����</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @error("subscription_type")
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="mb-3">ѡ��֧����ʽ</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border payment-method-card" data-method="alipay">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="alipay" value="alipay" checked>
                                                <label class="form-check-label" for="alipay">
                                                    <img src="{{ asset("images/payment/alipay.png") }}" alt="֧����" height="30">
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
                                                    <img src="{{ asset("images/payment/wechat.png") }}" alt="΢��֧��" height="30">
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
                                                    <img src="{{ asset("images/payment/card.png") }}" alt="���п�" height="30">
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
                                    �����Զ����ѣ�����ʱȡ����
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input @error("agree_terms") is-invalid @enderror" type="checkbox" name="agree_terms" id="agreeTerms" {{ old("agree_terms") ? "checked" : "" }} required>
                                <label class="form-check-label" for="agreeTerms">
                                    �����Ķ���ͬ�� <a href="{{ route("terms") }}" target="_blank">��������</a> �� <a href="{{ route("privacy") }}" target="_blank">��˽����</a>
                                </label>
                                @error("agree_terms")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-shopping-cart me-1"></i> ����֧��
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- ����ժҪ -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">����ժҪ</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>��Ա�ȼ�</span>
                        <span id="summaryLevel">{{ $levels[0]->name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>��������</span>
                        <span id="summaryType">�¶�</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>֧����ʽ</span>
                        <span id="summaryPayment">֧����</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>�Զ�����</span>
                        <span id="summaryAutoRenew">��</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span>С��</span>
                        <span id="summarySubtotal">��{{ $levels[0]->formatted_monthly_price }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>�ۿ�</span>
                        <span id="summaryDiscount">��0.00</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">�ܼ�</span>
                        <span class="fw-bold" id="summaryTotal">��{{ $levels[0]->formatted_monthly_price }}</span>
                    </div>
                </div>
            </div>
            
            <!-- �������� -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">��������</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    ���ѡ���ʺ��ҵĻ�Ա�ȼ���
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    ��������ʹ������ѡ����ʵĻ�Ա�ȼ����������Ҫ�����API���ô����������AIģ�ͷ���Ȩ�޻����Ĵ洢�ռ䣬����ѡ����߼���Ļ�Ա����������ʱ�����򽵼����Ļ�Ա�ȼ���
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    �¶ȶ��ĺ���ȶ�����ʲô����
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    �¶ȶ���ÿ���Զ����ѣ�������ʱȡ������ȶ���һ����֧��ȫ����ã�����¶ȶ��Ŀ��Խ�ʡԼ20%�ķ��ã��൱�ڻ��2���µ����ʹ���ڡ�
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    �ҿ�����ʱȡ��������
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    �ǵģ���������ʱȡ�����ġ�ȡ�������Ļ�Ա�ʸ񽫳�������ǰ�����ڽ��������ǲ��ṩ�������˿�������Լ���ʹ�÷���ֱ�������ڽ�����
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    ֧����ȫ��
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    �ǵģ�����ʹ����ҵ��׼�ļ��ܼ�����������֧����Ϣ�����ǲ���洢�����������ÿ���Ϣ������֧������ͨ����ȫ�ĵ�����֧����������ɡ�
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
        // ��Ա�ȼ�ѡ��
        const levelRadios = document.querySelectorAll(".level-radio");
        const levelCards = document.querySelectorAll(".card");
        
        levelRadios.forEach(radio => {
            radio.addEventListener("change", function() {
                updateOrderSummary();
            });
        });
        
        // ��������ѡ��
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
        
        // ֧����ʽѡ��
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
        
        // �Զ�����ѡ��
        const autoRenewCheckbox = document.getElementById("autoRenew");
        autoRenewCheckbox.addEventListener("change", function() {
            updateOrderSummary();
        });
        
        // ��ʼ��ѡ��״̬
        document.querySelector(".subscription-type-card[data-type='monthly']").classList.add("selected");
        document.querySelector(".payment-method-card[data-method='alipay']").classList.add("selected");
        
        // ���¶���ժҪ
        function updateOrderSummary() {
            // ��ȡѡ�еĻ�Ա�ȼ�
            const selectedLevelRadio = document.querySelector(".level-radio:checked");
            const selectedLevelId = selectedLevelRadio ? selectedLevelRadio.value : "";
            const selectedLevelName = selectedLevelRadio ? selectedLevelRadio.closest(".card").querySelector(".form-check-label strong").textContent.trim() : "";
            
            // ��ȡѡ�еĶ�������
            const selectedType = document.querySelector(".subscription-type-radio:checked").value;
            const selectedTypeName = selectedType === "monthly" ? "�¶�" : "���";
            
            // ��ȡѡ�е�֧����ʽ
            const selectedPayment = document.querySelector(".payment-method-radio:checked").value;
            let selectedPaymentName = "֧����";
            if (selectedPayment === "wechat") selectedPaymentName = "΢��֧��";
            if (selectedPayment === "card") selectedPaymentName = "���п�";
            
            // ��ȡ�Զ�����״̬
            const autoRenew = document.getElementById("autoRenew").checked;
            
            // ����ժҪ
            document.getElementById("summaryLevel").textContent = selectedLevelName;
            document.getElementById("summaryType").textContent = selectedTypeName;
            document.getElementById("summaryPayment").textContent = selectedPaymentName;
            document.getElementById("summaryAutoRenew").textContent = autoRenew ? "��" : "��";
            
            // ����۸�
            // ����Ӧ��ͨ��AJAX��ȡʵ�ʼ۸�Ϊ����ʾ������ʹ��ǰ�˼���
            // ������һ���۸��
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
                document.getElementById("summarySubtotal").textContent = "��" + price.toFixed(2);
                document.getElementById("summaryTotal").textContent = "��" + price.toFixed(2);
            }
        }
        
        // ��ʼ������ժҪ
        updateOrderSummary();
    });
</script>
@endsection
@endsection
