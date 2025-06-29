@extends('layouts.user')

@section('title', 'æ”¯ä»˜è®¢å•')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">æ”¯ä»˜è®¢å•</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">æ§åˆ¶å°</a></li>
        <li class="breadcrumb-item"><a href="{{ route('user.billing.packages') }}">å¥—é¤è´­ä¹°</a></li>
        <li class="breadcrumb-item active">æ”¯ä»˜è®¢å•</li>
    </ol>

    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-credit-card me-1"></i>
                    è®¢å•æ”¯ä»˜
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5>è®¢å•ä¿¡æ¯</h5>
                        <p>è®¢å•å·ï¼š{{ $order->order_number }}</p>
                        <p>å¥—é¤åç§°ï¼š{{ $order->package_name }}</p>
                        <p>æ”¯ä»˜é‡‘é¢ï¼š<span class="text-danger fw-bold">Â¥{{ number_format($order->total_amount, 2) }}</span></p>
                    </div>

                    @if($order->payment_method == 'alipay')
                        <div class="payment-container text-center">
                            <h5 class="mb-3">è¯·ä½¿ç”¨æ”¯ä»˜å®æ‰«ç æ”¯ä»˜æˆ–ç‚¹å‡»ä¸‹æ–¹æŒ‰é’®</h5>
                            
                            @if(isset($payment_data['qr_code']))
                                <div class="qrcode-container mb-3">
                                    <img src="{{ $payment_data['qr_code'] }}" alt="æ”¯ä»˜å®äºŒç»´ç " class="img-fluid">
                                </div>
                            @endif
                            
                            <div class="d-grid gap-2 col-6 mx-auto">
                                <a href="{{ $payment_data['payment_url'] ?? '#' }}" class="btn btn-primary btn-lg" target="_blank">
                                    <i class="fab fa-alipay"></i> ç‚¹å‡»æ”¯ä»˜
                                </a>
                            </div>
                        </div>
                    @elseif($order->payment_method == 'wechat')
                        <div class="payment-container text-center">
                            <h5 class="mb-3">è¯·ä½¿ç”¨å¾®ä¿¡æ‰«ç æ”¯ä»˜</h5>
                            
                            @if(isset($payment_data['qr_code']))
                                <div class="qrcode-container mb-3">
                                    <img src="{{ $payment_data['qr_code'] }}" alt="å¾®ä¿¡æ”¯ä»˜äºŒç»´ç " class="img-fluid">
                                </div>
                            @elseif(isset($payment_data['payment_url']))
                                <div class="d-grid gap-2 col-6 mx-auto">
                                    <a href="{{ $payment_data['payment_url'] }}" class="btn btn-success btn-lg" target="_blank">
                                        <i class="fab fa-weixin"></i> ç‚¹å‡»æ”¯ä»˜
                                    </a>
                                </div>
                            @elseif(isset($payment_data['payment_data']))
                                <div id="wechat-jsapi-container">
                                    <button id="wechat-jsapi-button" class="btn btn-success btn-lg">
                                        <i class="fab fa-weixin"></i> å¾®ä¿¡æ”¯ä»˜
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
                                                    alert('æ”¯ä»˜å¤±è´¥ï¼Œè¯·é‡è¯•');
                                                }
                                            });
                                        }
                                    });
                                </script>
                            @endif
                        </div>
                    @endif

                    <div class="mt-4 text-center">
                        <p>æ”¯ä»˜å®Œæˆåï¼Œç³»ç»Ÿå°†è‡ªåŠ¨ä¸ºæ‚¨åˆ†é…é¢åº¦</p>
                        <p>å¦‚æœæ‚¨å·²å®Œæˆæ”¯ä»˜ï¼Œä½†é¡µé¢æ²¡æœ‰è·³è½¬ï¼Œè¯·ç‚¹å‡»ä¸‹æ–¹æŒ‰é’®</p>
                        <button id="check-payment" class="btn btn-outline-primary">
                            <i class="fas fa-sync-alt"></i> æˆ‘å·²æ”¯ä»˜ï¼ŒæŸ¥è¯¢ç»“æœ
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    æ”¯ä»˜è¯´æ˜
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check-circle text-success"></i> æ”¯ä»˜æˆåŠŸåç³»ç»Ÿå°†è‡ªåŠ¨ä¸ºæ‚¨åˆ†é…é¢åº¦</li>
                        <li><i class="fas fa-check-circle text-success"></i> å¦‚é‡æ”¯ä»˜é—®é¢˜ï¼Œè¯·è”ç³»å®¢æœ</li>
                        <li><i class="fas fa-check-circle text-success"></i> æ”¯ä»˜æˆåŠŸåå¯åœ¨è®¢å•è®°å½•ä¸­æŸ¥çœ‹è¯¦æƒ…</li>
                    </ul>
                    <hr>
                    <div class="text-center">
                        <a href="{{ route('user.billing.orders') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> æŸ¥çœ‹è®¢å•è®°å½•
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
        // å®šæ—¶æŸ¥è¯¢æ”¯ä»˜çŠ¶æ€
        let checkInterval = setInterval(checkPaymentStatus, 5000);
        
        // æ‰‹åŠ¨æŸ¥è¯¢æ”¯ä»˜çŠ¶æ€
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
                    console.error('æŸ¥è¯¢æ”¯ä»˜çŠ¶æ€å¤±è´¥');
                }
            });
        }
    });
</script>
@endsection@extends('layouts.user')

@section('title', 'Ö§¸¶¶©µ¥')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Ö§¸¶¶©µ¥</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">¿ØÖÆÌ¨</a></li>
        <li class="breadcrumb-item"><a href="{{ route('user.billing.packages') }}">Ì×²Í¹ºÂò</a></li>
        <li class="breadcrumb-item active">Ö§¸¶¶©µ¥</li>
    </ol>

    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-credit-card me-1"></i>
                    ¶©µ¥Ö§¸¶
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5>¶©µ¥ĞÅÏ¢</h5>
                        <p>¶©µ¥ºÅ£º{{ $order->order_number }}</p>
                        <p>Ì×²ÍÃû³Æ£º{{ $order->package_name }}</p>
                        <p>Ö§¸¶½ğ¶î£º<span class="text-danger fw-bold">{{ number_format($order->total_amount, 2) }}</span></p>
                    </div>

                    @if($order->payment_method == 'alipay')
                        <div class="payment-container text-center">
                            <h5 class="mb-3">ÇëÊ¹ÓÃÖ§¸¶±¦É¨ÂëÖ§¸¶»òµã»÷ÏÂ·½°´Å¥</h5>
                            
                            @if(isset($payment_data['qr_code']))
                                <div class="qrcode-container mb-3">
                                    <img src="{{ $payment_data['qr_code'] }}" alt="Ö§¸¶±¦¶şÎ¬Âë" class="img-fluid">
                                </div>
                            @endif
                            
                            <div class="d-grid gap-2 col-6 mx-auto">
                                <a href="{{ $payment_data['payment_url'] ?? '#' }}" class="btn btn-primary btn-lg" target="_blank">
                                    <i class="fab fa-alipay"></i> µã»÷Ö§¸¶
                                </a>
                            </div>
                        </div>
                    @elseif($order->payment_method == 'wechat')
                        <div class="payment-container text-center">
                            <h5 class="mb-3">ÇëÊ¹ÓÃÎ¢ĞÅÉ¨ÂëÖ§¸¶</h5>
                            
                            @if(isset($payment_data['qr_code']))
                                <div class="qrcode-container mb-3">
                                    <img src="{{ $payment_data['qr_code'] }}" alt="Î¢ĞÅÖ§¸¶¶şÎ¬Âë" class="img-fluid">
                                </div>
                            @elseif(isset($payment_data['payment_url']))
                                <div class="d-grid gap-2 col-6 mx-auto">
                                    <a href="{{ $payment_data['payment_url'] }}" class="btn btn-success btn-lg" target="_blank">
                                        <i class="fab fa-weixin"></i> µã»÷Ö§¸¶
                                    </a>
                                </div>
                            @elseif(isset($payment_data['payment_data']))
                                <div id="wechat-jsapi-container">
                                    <button id="wechat-jsapi-button" class="btn btn-success btn-lg">
                                        <i class="fab fa-weixin"></i> Î¢ĞÅÖ§¸¶
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
                                                    alert('Ö§¸¶Ê§°Ü£¬ÇëÖØÊÔ');
                                                }
                                            });
                                        }
                                    });
                                </script>
                            @endif
                        </div>
                    @endif

                    <div class="mt-4 text-center">
                        <p>Ö§¸¶Íê³Éºó£¬ÏµÍ³½«×Ô¶¯ÎªÄú·ÖÅä¶î¶È</p>
                        <p>Èç¹ûÄúÒÑÍê³ÉÖ§¸¶£¬µ«Ò³ÃæÃ»ÓĞÌø×ª£¬Çëµã»÷ÏÂ·½°´Å¥</p>
                        <button id="check-payment" class="btn btn-outline-primary">
                            <i class="fas fa-sync-alt"></i> ÎÒÒÑÖ§¸¶£¬²éÑ¯½á¹û
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Ö§¸¶ËµÃ÷
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check-circle text-success"></i> Ö§¸¶³É¹¦ºóÏµÍ³½«×Ô¶¯ÎªÄú·ÖÅä¶î¶È</li>
                        <li><i class="fas fa-check-circle text-success"></i> ÈçÓöÖ§¸¶ÎÊÌâ£¬ÇëÁªÏµ¿Í·ş</li>
                        <li><i class="fas fa-check-circle text-success"></i> Ö§¸¶³É¹¦ºó¿ÉÔÚ¶©µ¥¼ÇÂ¼ÖĞ²é¿´ÏêÇé</li>
                    </ul>
                    <hr>
                    <div class="text-center">
                        <a href="{{ route('user.billing.orders') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> ²é¿´¶©µ¥¼ÇÂ¼
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
        // ¶¨Ê±²éÑ¯Ö§¸¶×´Ì¬
        let checkInterval = setInterval(checkPaymentStatus, 5000);
        
        // ÊÖ¶¯²éÑ¯Ö§¸¶×´Ì¬
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
                    console.error('²éÑ¯Ö§¸¶×´Ì¬Ê§°Ü');
                }
            });
        }
    });
</script>
@endsection
