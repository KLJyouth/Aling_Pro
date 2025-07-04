@extends("layouts.app")

@section("title", "购买套餐")

@section("content")
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">选择套餐</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <ul class="nav nav-pills" id="package-type-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="all-tab" data-toggle="pill" href="#all" role="tab">全部</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="api-tab" data-toggle="pill" href="#api" role="tab">API调用额度</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="ai-tab" data-toggle="pill" href="#ai" role="tab">AI使用额度</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="storage-tab" data-toggle="pill" href="#storage" role="tab">存储空间</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="comprehensive-tab" data-toggle="pill" href="#comprehensive" role="tab">综合套餐</a>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content" id="package-type-content">
                        <div class="tab-pane fade show active" id="all" role="tabpanel">
                            <div class="row">
                                @foreach($packages as $package)
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100 {{ $package->is_popular ? "border-primary" : "" }}">
                                            @if($package->is_popular)
                                                <div class="ribbon ribbon-top-right"><span>热门</span></div>
                                            @endif
                                            @if($package->is_recommended)
                                                <div class="ribbon ribbon-top-left"><span>推荐</span></div>
                                            @endif
                                            <div class="card-header text-center {{ $package->is_popular ? "bg-primary text-white" : "" }}">
                                                <h5 class="mb-0">{{ $package->name }}</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="text-center mb-4">
                                                    <h3 class="mb-0">{{ number_format($package->price, 2) }}</h3>
                                                    @if($package->original_price && $package->original_price > $package->price)
                                                        <p class="text-muted mb-0"><del>{{ number_format($package->original_price, 2) }}</del></p>
                                                        <div class="badge badge-danger mt-2">
                                                            节省 {{ round((1 - $package->price / $package->original_price) * 100) }}%
                                                        </div>
                                                    @endif
                                                </div>

                                                <ul class="list-unstyled">
                                                    <li class="mb-2">
                                                        <i class="fas fa-check-circle text-success mr-2"></i>
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
                                                        {{ number_format($package->quota) }} 额度
                                                    </li>
                                                    <li class="mb-2">
                                                        <i class="fas fa-clock text-info mr-2"></i>
                                                        @if($package->duration_days)
                                                            有效期 {{ $package->duration_days }} 天
                                                        @else
                                                            永久有效
                                                        @endif
                                                    </li>
                                                    @if($package->features)
                                                        @foreach(json_decode($package->features, true) as $feature)
                                                            @if(!empty($feature))
                                                                <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> {{ $feature }}</li>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </ul>
                                            </div>
                                            <div class="card-footer text-center">
                                                <a href="{{ route("user.billing.checkout", $package->id) }}" class="btn btn-primary btn-block">立即购买</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>


                        <div class="tab-pane fade" id="api" role="tabpanel">
                            <div class="row">
                                @foreach($packages->where("type", "api") as $package)
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100 {{ $package->is_popular ? "border-primary" : "" }}">
                                            @if($package->is_popular)
                                                <div class="ribbon ribbon-top-right"><span>热门</span></div>
                                            @endif
                                            @if($package->is_recommended)
                                                <div class="ribbon ribbon-top-left"><span>推荐</span></div>
                                            @endif
                                            <div class="card-header text-center {{ $package->is_popular ? "bg-primary text-white" : "" }}">
                                                <h5 class="mb-0">{{ $package->name }}</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="text-center mb-4">
                                                    <h3 class="mb-0">{{ number_format($package->price, 2) }}</h3>
                                                    @if($package->original_price && $package->original_price > $package->price)
                                                        <p class="text-muted mb-0"><del>{{ number_format($package->original_price, 2) }}</del></p>
                                                        <div class="badge badge-danger mt-2">
                                                            节省 {{ round((1 - $package->price / $package->original_price) * 100) }}%
                                                        </div>
                                                    @endif
                                                </div>
                                                <ul class="list-unstyled">
                                                    <li class="mb-2">
                                                        <i class="fas fa-check-circle text-success mr-2"></i>
                                                        <span class="badge badge-info">API调用额度</span>
                                                        {{ number_format($package->quota) }} 额度
                                                    </li>
                                                    <li class="mb-2">
                                                        <i class="fas fa-clock text-info mr-2"></i>
                                                        @if($package->duration_days)
                                                            有效期 {{ $package->duration_days }} 天
                                                        @else
                                                            永久有效
                                                        @endif
                                                    </li>
                                                    @if($package->features)
                                                        @foreach(json_decode($package->features, true) as $feature)
                                                            @if(!empty($feature))
                                                                <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> {{ $feature }}</li>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </ul>
                                            </div>
                                            <div class="card-footer text-center">
                                                <a href="{{ route("user.billing.checkout", $package->id) }}" class="btn btn-primary btn-block">立即购买</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>


                        <div class="tab-pane fade" id="ai" role="tabpanel">
                            <div class="row">
                                @foreach($packages->where("type", "ai") as $package)
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100 {{ $package->is_popular ? "border-primary" : "" }}">
                                            @if($package->is_popular)
                                                <div class="ribbon ribbon-top-right"><span>热门</span></div>
                                            @endif
                                            @if($package->is_recommended)
                                                <div class="ribbon ribbon-top-left"><span>推荐</span></div>
                                            @endif
                                            <div class="card-header text-center {{ $package->is_popular ? "bg-primary text-white" : "" }}">
                                                <h5 class="mb-0">{{ $package->name }}</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="text-center mb-4">
                                                    <h3 class="mb-0">{{ number_format($package->price, 2) }}</h3>
                                                    @if($package->original_price && $package->original_price > $package->price)
                                                        <p class="text-muted mb-0"><del>{{ number_format($package->original_price, 2) }}</del></p>
                                                        <div class="badge badge-danger mt-2">
                                                            节省 {{ round((1 - $package->price / $package->original_price) * 100) }}%
                                                        </div>
                                                    @endif
                                                </div>
                                                <ul class="list-unstyled">
                                                    <li class="mb-2">
                                                        <i class="fas fa-check-circle text-success mr-2"></i>
                                                        <span class="badge badge-success">AI使用额度</span>
                                                        {{ number_format($package->quota) }} 额度
                                                    </li>
                                                    <li class="mb-2">
                                                        <i class="fas fa-clock text-info mr-2"></i>
                                                        @if($package->duration_days)
                                                            有效期 {{ $package->duration_days }} 天
                                                        @else
                                                            永久有效
                                                        @endif
                                                    </li>
                                                    @if($package->features)
                                                        @foreach(json_decode($package->features, true) as $feature)
                                                            @if(!empty($feature))
                                                                <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> {{ $feature }}</li>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </ul>
                                            </div>
                                            <div class="card-footer text-center">
                                                <a href="{{ route("user.billing.checkout", $package->id) }}" class="btn btn-success btn-block">立即购买</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>


                        <div class="tab-pane fade" id="storage" role="tabpanel">
                            <div class="row">
                                @foreach($packages->where("type", "storage") as $package)
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100 {{ $package->is_popular ? "border-primary" : "" }}">
                                            @if($package->is_popular)
                                                <div class="ribbon ribbon-top-right"><span>热门</span></div>
                                            @endif
                                            @if($package->is_recommended)
                                                <div class="ribbon ribbon-top-left"><span>推荐</span></div>
                                            @endif
                                            <div class="card-header text-center {{ $package->is_popular ? "bg-primary text-white" : "" }}">
                                                <h5 class="mb-0">{{ $package->name }}</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="text-center mb-4">
                                                    <h3 class="mb-0">{{ number_format($package->price, 2) }}</h3>
                                                    @if($package->original_price && $package->original_price > $package->price)
                                                        <p class="text-muted mb-0"><del>{{ number_format($package->original_price, 2) }}</del></p>
                                                        <div class="badge badge-danger mt-2">
                                                            节省 {{ round((1 - $package->price / $package->original_price) * 100) }}%
                                                        </div>
                                                    @endif
                                                </div>
                                                <ul class="list-unstyled">
                                                    <li class="mb-2">
                                                        <i class="fas fa-check-circle text-success mr-2"></i>
                                                        <span class="badge badge-warning">存储空间</span>
                                                        {{ number_format($package->quota / 1024, 2) }} GB
                                                    </li>
                                                    <li class="mb-2">
                                                        <i class="fas fa-clock text-info mr-2"></i>
                                                        @if($package->duration_days)
                                                            有效期 {{ $package->duration_days }} 天
                                                        @else
                                                            永久有效
                                                        @endif
                                                    </li>
                                                    @if($package->features)
                                                        @foreach(json_decode($package->features, true) as $feature)
                                                            @if(!empty($feature))
                                                                <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> {{ $feature }}</li>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </ul>
                                            </div>
                                            <div class="card-footer text-center">
                                                <a href="{{ route("user.billing.checkout", $package->id) }}" class="btn btn-warning btn-block">立即购买</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>


                        <div class="tab-pane fade" id="comprehensive" role="tabpanel">
                            <div class="row">
                                @foreach($packages->where("type", "comprehensive") as $package)
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100 {{ $package->is_popular ? "border-primary" : "" }}">
                                            @if($package->is_popular)
                                                <div class="ribbon ribbon-top-right"><span>热门</span></div>
                                            @endif
                                            @if($package->is_recommended)
                                                <div class="ribbon ribbon-top-left"><span>推荐</span></div>
                                            @endif
                                            <div class="card-header text-center {{ $package->is_popular ? "bg-primary text-white" : "" }}">
                                                <h5 class="mb-0">{{ $package->name }}</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="text-center mb-4">
                                                    <h3 class="mb-0">{{ number_format($package->price, 2) }}</h3>
                                                    @if($package->original_price && $package->original_price > $package->price)
                                                        <p class="text-muted mb-0"><del>{{ number_format($package->original_price, 2) }}</del></p>
                                                        <div class="badge badge-danger mt-2">
                                                            节省 {{ round((1 - $package->price / $package->original_price) * 100) }}%
                                                        </div>
                                                    @endif
                                                </div>
                                                <ul class="list-unstyled">
                                                    <li class="mb-2">
                                                        <i class="fas fa-check-circle text-success mr-2"></i>
                                                        <span class="badge badge-secondary">综合套餐</span>
                                                        {{ number_format($package->quota) }} 额度
                                                    </li>
                                                    <li class="mb-2">
                                                        <i class="fas fa-clock text-info mr-2"></i>
                                                        @if($package->duration_days)
                                                            有效期 {{ $package->duration_days }} 天
                                                        @else
                                                            永久有效
                                                        @endif
                                                    </li>
                                                    @if($package->features)
                                                        @foreach(json_decode($package->features, true) as $feature)
                                                            @if(!empty($feature))
                                                                <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> {{ $feature }}</li>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </ul>
                                            </div>
                                            <div class="card-footer text-center">
                                                <a href="{{ route("user.billing.checkout", $package->id) }}" class="btn btn-secondary btn-block">立即购买</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section("styles")
<style>
    .ribbon {
        position: absolute;
        right: -5px;
        top: -5px;
        z-index: 1;
        overflow: hidden;
        width: 75px;
        height: 75px;
        text-align: right;
    }
    .ribbon-top-right {
        top: -5px;
        right: -5px;
    }
    .ribbon-top-left {
        top: -5px;
        left: -5px;
    }
    .ribbon span {
        font-size: 10px;
        font-weight: bold;
        color: #FFF;
        text-transform: uppercase;
        text-align: center;
        line-height: 20px;
        transform: rotate(45deg);
        -webkit-transform: rotate(45deg);
        width: 100px;
        display: block;
        background: #79A70A;
        background: linear-gradient(#9BC90D 0%, #79A70A 100%);
        box-shadow: 0 3px 10px -5px rgba(0, 0, 0, 1);
        position: absolute;
        top: 19px;
        right: -21px;
    }
    .ribbon-top-right span {
        left: -25px;
        top: 30px;
        transform: rotate(45deg);
    }
    .ribbon-top-left span {
        right: -25px;
        top: 30px;
        transform: rotate(-45deg);
    }
    .ribbon-top-right span {
        background: linear-gradient(#2989d8 0%, #1e5799 100%);
    }
    .ribbon-top-left span {
        background: linear-gradient(#F70505 0%, #8F0808 100%);
    }
</style>
@endsection

@section("scripts")
<script>
    $(function() {
        // 如果URL中有type参数，激活对应的标签页
        var urlParams = new URLSearchParams(window.location.search);
        var type = urlParams.get("type");
        if (type) {
            $("#package-type-tabs a[href=\"#" + type + "\"]").tab("show");
        }
    });
</script>
@endsection
