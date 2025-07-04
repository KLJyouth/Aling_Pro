@extends("layouts.app")

@section("title", "结算中心")

@section("content")
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">确认订单</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <p class="text-muted mb-1">商品信息</p>
                            <h5>{{ $package->name }}</h5>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted mb-1">商品类型</p>
                            <h5>
                                @switch($package->type)
                                    @case("api")
                                        <span class="badge badge-info">API调用额度</span>
                                        @break
                                    @case("ai")
                                        <span class="badge badge-success">AI使用额度</span>
                                        @break
                                    @case("storage")
                                        <span class="badge badge-warning">存储空间</span>
                                        @break
                                    @case("bandwidth")
                                        <span class="badge badge-primary">带宽流量</span>
                                        @break
                                    @case("comprehensive")
                                        <span class="badge badge-secondary">综合套餐</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ $package->type }}</span>
                                @endswitch
                            </h5>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted mb-1">额度数量</p>
                            <h5>{{ number_format($package->quota) }}</h5>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <p class="text-muted mb-1">有效期</p>
                            <h5>
                                @if($package->duration_days)
                                    {{ $package->duration_days }} 天
                                @else
                                    永久有效
                                @endif
                            </h5>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted mb-1">原价</p>
                            <h5>
                                @if($package->original_price && $package->original_price > $package->price)
                                    <del>{{ number_format($package->original_price, 2) }}</del>
                                @else
                                    {{ number_format($package->price, 2) }}
                                @endif
                            </h5>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted mb-1">优惠价</p>
                            <h5 class="text-danger">{{ number_format($package->price, 2) }}</h5>
                        </div>
                    </div>


                    <hr class="my-4">

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5>会员折扣</h5>
                            @if($membershipDiscount > 0)
                                <div class="alert alert-success">
                                    <i class="fas fa-tag mr-2"></i> 您是 {{ $currentSubscription->level->name }} 会员，享受 {{ $currentSubscription->level->discount_percent }}% 折扣
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="text-muted mb-1">折扣金额</p>
                                        <h5 class="text-success">- {{ number_format($membershipDiscount, 2) }}</h5>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-muted mb-1">折后价格</p>
                                        <h5>{{ number_format($finalPrice, 2) }}</h5>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i> 您当前不是会员，无法享受会员折扣。<a href="{{ route("user.membership.levels") }}">立即开通会员</a> 享受更多特权！
                                </div>
                            @endif
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5>支付方式</h5>
                            <div class="form-group">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="payment_alipay" name="payment_method" class="custom-control-input" value="alipay" checked>
                                    <label class="custom-control-label" for="payment_alipay">
                                        <img src="{{ asset("images/payment/alipay.png") }}" alt="支付宝" height="30">
                                        支付宝
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="payment_wechat" name="payment_method" class="custom-control-input" value="wechat">
                                    <label class="custom-control-label" for="payment_wechat">
                                        <img src="{{ asset("images/payment/wechat.png") }}" alt="微信支付" height="30">
                                        微信支付
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>


                    <hr class="my-4">

                    <div class="row">
                        <div class="col-md-12">
                            <h5>订单总计</h5>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span>商品金额：</span>
                                <span>{{ number_format($package->price, 2) }}</span>
                            </div>
                            @if($membershipDiscount > 0)
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span>会员折扣：</span>
                                    <span class="text-success">- {{ number_format($membershipDiscount, 2) }}</span>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span>优惠券：</span>
                                <span>- 0.00</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <h5>应付金额：</h5>
                                <h5 class="text-danger">{{ number_format($finalPrice, 2) }}</h5>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <form action="{{ route("user.billing.pay", $package->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="payment_method" id="payment_method_input" value="alipay">
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="agree_terms" required>
                                <label class="form-check-label" for="agree_terms">我已阅读并同意 <a href="{{ route("terms") }}" target="_blank">服务条款</a> 和 <a href="{{ route("privacy") }}" target="_blank">隐私政策</a></label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg btn-block">立即支付</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
    $(function() {
        // 更新支付方式
        $("input[name=payment_method]").change(function() {
            $("#payment_method_input").val($(this).val());
        });
    });
</script>
@endsection
