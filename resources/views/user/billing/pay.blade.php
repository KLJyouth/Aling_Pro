@extends('layouts.user')

@section('title', '支付订单')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">支付订单</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">控制台</a></li>
        <li class="breadcrumb-item"><a href="{{ route('user.billing.packages') }}">套餐购买</a></li>
        <li class="breadcrumb-item active">支付订单</li>
    </ol>

    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-credit-card me-1"></i>
                    订单支付
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5>订单信息</h5>
                        <p>订单号：{{ $order->order_number }}</p>
                        <p>套餐名称：{{ $order->package_name }}</p>
                        <p>支付金额：<span class="text-danger fw-bold">¥{{ number_format($order->total_amount, 2) }}</span></p>
                    </div>

                    @if($order->payment_method == 'alipay')
                        <div class="payment-container text-center">
                            <h5 class="mb-3">请使用支付宝扫码支付或点击下方按钮</h5>
                            
                            @if(isset($payment_data['qr_code']))
                                <div class="qrcode-container mb-3">
                                    <img src="{{ $payment_data['qr_code'] }}" alt="支付宝二维码" class="img-fluid">
                                </div>
                            @endif
                            
                            <div class="d-grid gap-2 col-6 mx-auto">
                                <a href="{{ $payment_data['payment_url'] ?? '#' }}" class="btn btn-primary btn-lg" target="_blank">
                                    <i class="fab fa-alipay"></i> 点击支付
                                </a>
                            </div>
                        </div>
                    @elseif($order->payment_method == 'wechat')
                        <div class="payment-container text-center">
                            <h5 class="mb-3">请使用微信扫码支付</h5>
                            
                            @if(isset($payment_data['qr_code']))
                                <div class="qrcode-container mb-3">
                                    <img src="{{ $payment_data['qr_code'] }}" alt="微信支付二维码" class="img-fluid">
                                </div>
                            @elseif(isset($payment_data['payment_url']))
                                <div class="d-grid gap-2 col-6 mx-auto">
                                    <a href="{{ $payment_data['payment_url'] }}" class="btn btn-success btn-lg" target="_blank">
                                        <i class="fab fa-weixin"></i> 点击支付
                                    </a>
                                </div>
                            @elseif(isset($payment_data['payment_data']))
                                <div id="wechat-jsapi-container">
                                    <button id="wechat-jsapi-button" class="btn btn-success btn-lg">
                                        <i class="fab fa-weixin"></i> 微信支付
                                    </button>
                                </div>
                                <script>
                                    document.getElementById('wechat-jsapi-button').addEventListener('click', function() {
                                        const paymentData = @json($payment_data['payment_data']);
                                        
                                        if (typeof WeixinJSBridge === 'undefined') {
                                            if (document.addEventListener) {
                                                document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
                                            } else if (document.attachEvent) {
                                                document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
                                                document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
                                            }
                                        } else {
                                            onBridgeReady();
                                        }
                                        
                                        function onBridgeReady() {
                                            WeixinJSBridge.invoke('getBrandWCPayRequest', paymentData, function(res) {
                                                if (res.err_msg === 'get_brand_wcpay_request:ok') {
                                                    checkPaymentStatus();
                                                } else {
                                                    alert('支付失败，请重试');
                                                }
                                            });
                                        }
                                    });
                                </script>
                            @endif
                        </div>
                    @endif

                    <div class="mt-4 text-center">
                        <p>支付完成后，系统将自动为您分配额度</p>
                        <p>如果您已完成支付，但页面没有跳转，请点击下方按钮</p>
                        <button id="check-payment" class="btn btn-outline-primary">
                            <i class="fas fa-sync-alt"></i> 我已支付，查询结果
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    支付说明
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check-circle text-success"></i> 支付成功后系统将自动为您分配额度</li>
                        <li><i class="fas fa-check-circle text-success"></i> 如遇支付问题，请联系客服</li>
                        <li><i class="fas fa-check-circle text-success"></i> 支付成功后可在订单记录中查看详情</li>
                    </ul>
                    <hr>
                    <div class="text-center">
                        <a href="{{ route('user.billing.orders') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> 查看订单记录
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // 定时查询支付状态
        let checkInterval = setInterval(checkPaymentStatus, 5000);
        
        // 手动查询支付状态
        $('#check-payment').on('click', function() {
            checkPaymentStatus();
        });
        
        function checkPaymentStatus() {
            $.ajax({
                url: '{{ route("payment.query", ["order" => $order->id]) }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.code === 0 && response.data.is_paid) {
                        clearInterval(checkInterval);
                        window.location.href = response.data.redirect_url;
                    }
                },
                error: function(xhr) {
                    console.error('查询支付状态失败');
                }
            });
        }
    });
</script>
@endsection@extends('layouts.user')

@section('title', '֧������')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">֧������</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">����̨</a></li>
        <li class="breadcrumb-item"><a href="{{ route('user.billing.packages') }}">�ײ͹���</a></li>
        <li class="breadcrumb-item active">֧������</li>
    </ol>

    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-credit-card me-1"></i>
                    ����֧��
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5>������Ϣ</h5>
                        <p>�����ţ�{{ $order->order_number }}</p>
                        <p>�ײ����ƣ�{{ $order->package_name }}</p>
                        <p>֧����<span class="text-danger fw-bold">{{ number_format($order->total_amount, 2) }}</span></p>
                    </div>

                    @if($order->payment_method == 'alipay')
                        <div class="payment-container text-center">
                            <h5 class="mb-3">��ʹ��֧����ɨ��֧�������·���ť</h5>
                            
                            @if(isset($payment_data['qr_code']))
                                <div class="qrcode-container mb-3">
                                    <img src="{{ $payment_data['qr_code'] }}" alt="֧������ά��" class="img-fluid">
                                </div>
                            @endif
                            
                            <div class="d-grid gap-2 col-6 mx-auto">
                                <a href="{{ $payment_data['payment_url'] ?? '#' }}" class="btn btn-primary btn-lg" target="_blank">
                                    <i class="fab fa-alipay"></i> ���֧��
                                </a>
                            </div>
                        </div>
                    @elseif($order->payment_method == 'wechat')
                        <div class="payment-container text-center">
                            <h5 class="mb-3">��ʹ��΢��ɨ��֧��</h5>
                            
                            @if(isset($payment_data['qr_code']))
                                <div class="qrcode-container mb-3">
                                    <img src="{{ $payment_data['qr_code'] }}" alt="΢��֧����ά��" class="img-fluid">
                                </div>
                            @elseif(isset($payment_data['payment_url']))
                                <div class="d-grid gap-2 col-6 mx-auto">
                                    <a href="{{ $payment_data['payment_url'] }}" class="btn btn-success btn-lg" target="_blank">
                                        <i class="fab fa-weixin"></i> ���֧��
                                    </a>
                                </div>
                            @elseif(isset($payment_data['payment_data']))
                                <div id="wechat-jsapi-container">
                                    <button id="wechat-jsapi-button" class="btn btn-success btn-lg">
                                        <i class="fab fa-weixin"></i> ΢��֧��
                                    </button>
                                </div>
                                <script>
                                    document.getElementById('wechat-jsapi-button').addEventListener('click', function() {
                                        const paymentData = @json($payment_data['payment_data']);
                                        
                                        if (typeof WeixinJSBridge === 'undefined') {
                                            if (document.addEventListener) {
                                                document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
                                            } else if (document.attachEvent) {
                                                document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
                                                document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
                                            }
                                        } else {
                                            onBridgeReady();
                                        }
                                        
                                        function onBridgeReady() {
                                            WeixinJSBridge.invoke('getBrandWCPayRequest', paymentData, function(res) {
                                                if (res.err_msg === 'get_brand_wcpay_request:ok') {
                                                    checkPaymentStatus();
                                                } else {
                                                    alert('֧��ʧ�ܣ�������');
                                                }
                                            });
                                        }
                                    });
                                </script>
                            @endif
                        </div>
                    @endif

                    <div class="mt-4 text-center">
                        <p>֧����ɺ�ϵͳ���Զ�Ϊ��������</p>
                        <p>����������֧������ҳ��û����ת�������·���ť</p>
                        <button id="check-payment" class="btn btn-outline-primary">
                            <i class="fas fa-sync-alt"></i> ����֧������ѯ���
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    ֧��˵��
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check-circle text-success"></i> ֧���ɹ���ϵͳ���Զ�Ϊ��������</li>
                        <li><i class="fas fa-check-circle text-success"></i> ����֧�����⣬����ϵ�ͷ�</li>
                        <li><i class="fas fa-check-circle text-success"></i> ֧���ɹ�����ڶ�����¼�в鿴����</li>
                    </ul>
                    <hr>
                    <div class="text-center">
                        <a href="{{ route('user.billing.orders') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> �鿴������¼
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // ��ʱ��ѯ֧��״̬
        let checkInterval = setInterval(checkPaymentStatus, 5000);
        
        // �ֶ���ѯ֧��״̬
        $('#check-payment').on('click', function() {
            checkPaymentStatus();
        });
        
        function checkPaymentStatus() {
            $.ajax({
                url: '{{ route("payment.query", ["order" => $order->id]) }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.code === 0 && response.data.is_paid) {
                        clearInterval(checkInterval);
                        window.location.href = response.data.redirect_url;
                    }
                },
                error: function(xhr) {
                    console.error('��ѯ֧��״̬ʧ��');
                }
            });
        }
    });
</script>
@endsection
