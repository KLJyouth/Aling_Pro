@extends("layouts.app")

@section("title", "֧������")

@section("content")
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">����֧��</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h4>������ţ�{{ $order->order_number }}</h4>
                        <h2 class="text-primary">��{{ number_format($order->total_amount, 2) }}</h2>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>�������ͣ�</strong> 
                                @if($order->order_type === "subscription")
                                    ��Ա����
                                @elseif($order->order_type === "point")
                                    ���ֹ���
                                @elseif($order->order_type === "product")
                                    ��Ʒ����
                                @else
                                    {{ $order->order_type }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>����ʱ�䣺</strong> {{ $order->created_at->format("Y-m-d H:i:s") }}</p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="payment-methods mb-4">
                        <h5 class="mb-3">ѡ��֧����ʽ</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 payment-method-card {{ $order->payment_method === "alipay" ? "selected" : "" }}" data-method="alipay">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="alipay" value="alipay" {{ $order->payment_method === "alipay" ? "checked" : "" }}>
                                            <label class="form-check-label" for="alipay">
                                                <img src="{{ asset("images/payment/alipay.png") }}" alt="֧����" height="30">
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
                                                <img src="{{ asset("images/payment/wechat.png") }}" alt="΢��֧��" height="30">
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
                                                <img src="{{ asset("images/payment/card.png") }}" alt="���п�" height="30">
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
                                <i class="fas fa-info-circle me-2"></i> ��ʹ���ֻ�ɨ���·���ά�����֧��
                            </div>
                            <div class="mb-3">
                                <img id="qrCode" src="" alt="֧����ά��" class="img-fluid" style="max-width: 200px;">
                            </div>
                        </div>
                        
                        <div id="cardPaymentForm" style="display: none;">
                            <form id="paymentForm" class="mb-4">
                                <div class="mb-3">
                                    <label for="cardNumber" class="form-label">����</label>
                                    <input type="text" class="form-control" id="cardNumber" placeholder="1234 5678 9012 3456" required>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="expiryDate" class="form-label">��Ч��</label>
                                        <input type="text" class="form-control" id="expiryDate" placeholder="MM/YY" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cvv" class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="cvv" placeholder="123" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="cardholderName" class="form-label">�ֿ�������</label>
                                    <input type="text" class="form-control" id="cardholderName" placeholder="����" required>
                                </div>
                            </form>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button id="payButton" class="btn btn-primary btn-lg">
                                <i class="fas fa-lock me-1"></i> ����֧��
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> ����
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">��������</h5>
                </div>
                <div class="card-body">
                    @if($order->order_type === "subscription")
                        <div class="mb-3">
                            <strong>��Ա�ȼ���</strong> {{ $order->subscription->membershipLevel->name }}
                        </div>
                        <div class="mb-3">
                            <strong>�������ͣ�</strong> {{ $order->subscription->subscription_type === "monthly" ? "�¶�" : "���" }}
                        </div>
                        <div class="mb-3">
                            <strong>��Ч�ڣ�</strong> {{ $order->subscription->start_date->format("Y-m-d") }} �� {{ $order->subscription->end_date->format("Y-m-d") }}
                        </div>
                    @elseif($order->order_type === "point")
                        <div class="mb-3">
                            <strong>����������</strong> {{ $order->meta["points"] ?? 0 }} ����
                        </div>
                    @elseif($order->order_type === "product")
                        <div class="mb-3">
                            <strong>��Ʒ���ƣ�</strong> {{ $order->meta["product_name"] ?? "" }}
                        </div>
                        <div class="mb-3">
                            <strong>��Ʒ������</strong> {{ $order->meta["quantity"] ?? 1 }}
                        </div>
                    @endif
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>С��</span>
                        <span>��{{ number_format($order->subtotal_amount, 2) }}</span>
                    </div>
                    
                    @if($order->discount_amount > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span>�ۿ�</span>
                            <span>-��{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    
                    <div class="d-flex justify-content-between fw-bold">
                        <span>�ܼ�</span>
                        <span>��{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">֧��˵��</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-shield-alt text-primary me-2"></i> ֧�����̲���SSL���ܣ�ȷ������֧����Ϣ��ȫ
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-primary me-2"></i> ֧���ɹ��󣬶�����������Ч
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-history text-primary me-2"></i> ֧����¼���ڸ������Ĳ鿴
                        </li>
                        <li>
                            <i class="fas fa-question-circle text-primary me-2"></i> �������⣬����ϵ�ͷ�
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ֧��������ģ̬�� -->
<div class="modal fade" id="processingModal" tabindex="-1" aria-labelledby="processingModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 id="processingModalLabel">֧�������У����Ժ�...</h5>
                <p class="text-muted">�벻Ҫ�رմ�ҳ��</p>
            </div>
        </div>
    </div>
</div>

<!-- ֧���ɹ�ģ̬�� -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success fa-4x"></i>
                </div>
                <h5 id="successModalLabel">֧���ɹ���</h5>
                <p>���Ķ�����֧���ɹ�����л���Ĺ���</p>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="{{ route("dashboard") }}" class="btn btn-primary">�����Ǳ���</a>
                <a href="#" id="viewOrderButton" class="btn btn-outline-primary">�鿴����</a>
            </div>
        </div>
    </div>
</div>

<!-- ֧��ʧ��ģ̬�� -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-times-circle text-danger fa-4x"></i>
                </div>
                <h5 id="errorModalLabel">֧��ʧ��</h5>
                <p id="errorMessage">��������֧��ʱ���ִ������Ժ����ԡ�</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">����</button>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">����</a>
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
        // ֧����ʽѡ��
        const paymentMethodRadios = document.querySelectorAll(".payment-method-radio");
        const paymentMethodCards = document.querySelectorAll(".payment-method-card");
        const qrCodeContainer = document.getElementById("qrCodeContainer");
        const cardPaymentForm = document.getElementById("cardPaymentForm");
        const qrCodeImage = document.getElementById("qrCode");
        const payButton = document.getElementById("payButton");
        
        let selectedMethod = "{{ $order->payment_method ?: "alipay" }}";
        
        // ��ʼ��ѡ��״̬
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
        
        // �����ƬҲ����ѡ��֧����ʽ
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
        
        // ����֧��UI
        function updatePaymentUI() {
            if (selectedMethod === "alipay" || selectedMethod === "wechat") {
                qrCodeContainer.style.display = "block";
                cardPaymentForm.style.display = "none";
                payButton.textContent = "����֧��";
            } else if (selectedMethod === "card") {
                qrCodeContainer.style.display = "none";
                cardPaymentForm.style.display = "block";
                payButton.textContent = "ȷ��֧��";
            }
        }
        
        // ֧����ť����¼�
        payButton.addEventListener("click", function() {
            // ��ʾ������ģ̬��
            const processingModal = new bootstrap.Modal(document.getElementById("processingModal"));
            processingModal.show();
            
            // ����֧������
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
                        // ��ʾ��ά��
                        qrCodeImage.src = data.qr_code;
                        qrCodeContainer.style.display = "block";
                        
                        // ��ʼ��ѯ֧��״̬
                        startPollingPaymentStatus();
                    } else if (selectedMethod === "card") {
                        // ģ�����п�֧������
                        setTimeout(() => {
                            // ��ʾ֧���ɹ�ģ̬��
                            const successModal = new bootstrap.Modal(document.getElementById("successModal"));
                            document.getElementById("viewOrderButton").href = "/orders/" + {{ $order->id }};
                            successModal.show();
                        }, 2000);
                    }
                } else {
                    // ��ʾ����ģ̬��
                    const errorModal = new bootstrap.Modal(document.getElementById("errorModal"));
                    document.getElementById("errorMessage").textContent = data.message;
                    errorModal.show();
                }
            })
            .catch(error => {
                processingModal.hide();
                
                // ��ʾ����ģ̬��
                const errorModal = new bootstrap.Modal(document.getElementById("errorModal"));
                document.getElementById("errorMessage").textContent = "֧������ʧ�ܣ����Ժ�����";
                errorModal.show();
                
                console.error("֧������ʧ��:", error);
            });
        });
        
        // ��ѯ֧��״̬
        function startPollingPaymentStatus() {
            const pollingInterval = 3000; // 3��
            let pollingCount = 0;
            const maxPollingCount = 60; // �����ѯ60�Σ�3���ӣ�
            
            const polling = setInterval(() => {
                pollingCount++;
                
                if (pollingCount > maxPollingCount) {
                    clearInterval(polling);
                    return;
                }
                
                // ��ѯ֧��״̬
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
                            // ֧���ɹ���ֹͣ��ѯ
                            clearInterval(polling);
                            
                            // ��ʾ֧���ɹ�ģ̬��
                            const successModal = new bootstrap.Modal(document.getElementById("successModal"));
                            document.getElementById("viewOrderButton").href = data.redirect;
                            successModal.show();
                        }
                    } else {
                        // ��ѯʧ�ܣ�ֹͣ��ѯ
                        clearInterval(polling);
                        
                        // ��ʾ����ģ̬��
                        const errorModal = new bootstrap.Modal(document.getElementById("errorModal"));
                        document.getElementById("errorMessage").textContent = data.message;
                        errorModal.show();
                    }
                })
                .catch(error => {
                    console.error("��ѯ֧��״̬ʧ��:", error);
                });
            }, pollingInterval);
        }
    });
</script>
@endsection
@endsection
