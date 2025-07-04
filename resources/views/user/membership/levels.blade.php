@extends("layouts.app")

@section("title", "会员等级")

@section("content")
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">选择会员等级</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary active" id="monthly-btn">月付</button>
                            <button type="button" class="btn btn-outline-primary" id="yearly-btn">年付</button>
                        </div>
                    </div>

                    <div class="row" id="monthly-plans">
                        @foreach($levels as $level)
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 {{ $level->is_popular ? "border-primary" : "" }} {{ $currentLevel && $currentLevel->id === $level->id ? "bg-light" : "" }}">
                                    @if($level->is_popular)
                                        <div class="ribbon ribbon-top-right"><span>热门</span></div>
                                    @endif
                                    @if($level->is_recommended)
                                        <div class="ribbon ribbon-top-left"><span>推荐</span></div>
                                    @endif
                                    <div class="card-header text-center {{ $level->is_popular ? "bg-primary text-white" : "" }}">
                                        @if($level->icon)
                                            <img src="{{ asset("storage/" . $level->icon) }}" alt="{{ $level->name }}" class="mb-2" style="height: 40px;">
                                        @endif
                                        <h5 class="mb-0">{{ $level->name }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-4">
                                            <h3 class="mb-0">{{ number_format($level->monthly_price, 2) }}<small class="text-muted">/月</small></h3>
                                        </div>
                                        <ul class="list-unstyled">
                                            <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> API请求限制: {{ $level->api_rate_limit }} 次/分钟</li>
                                            <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> API每日限额: {{ number_format($level->api_daily_limit) }} 次/天</li>
                                            <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> 存储空间: {{ $level->storage_limit }} GB</li>
                                            <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> 购买折扣: {{ $level->discount_percent }}%</li>
                                            <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> 团队成员: {{ $level->max_team_members }} 人</li>
                                            <li class="mb-2"><i class="fas fa-{{ $level->priority_support ? "check" : "times" }}-circle {{ $level->priority_support ? "text-success" : "text-danger" }} mr-2"></i> 优先支持</li>

                                            @if($level->features)
                                                @php
                                                    $features = json_decode($level->features, true);
                                                    $featureLabels = [
                                                        "advanced_models" => "高级模型访问权限",
                                                        "early_access" => "新功能抢先体验",
                                                        "api_access" => "API访问权限",
                                                        "custom_domain" => "自定义域名",
                                                        "white_label" => "白标解决方案"
                                                    ];
                                                @endphp
                                                
                                                @foreach($features as $feature)
                                                    @if(isset($featureLabels[$feature]))
                                                        <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> {{ $featureLabels[$feature] }}</li>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </ul>
                                    </div>
                                    <div class="card-footer text-center">
                                        @if($currentLevel && $currentLevel->id === $level->id)
                                            <button class="btn btn-success btn-block" disabled>当前等级</button>
                                        @elseif($currentLevel && $currentLevel->level < $level->level)
                                            <a href="{{ route("user.membership.subscribe", ["id" => $level->id, "period" => "monthly"]) }}" class="btn btn-primary btn-block">升级</a>
                                        @elseif($currentLevel && $currentLevel->level > $level->level)
                                            <a href="{{ route("user.membership.subscribe", ["id" => $level->id, "period" => "monthly"]) }}" class="btn btn-outline-primary btn-block">降级</a>
                                        @else
                                            <a href="{{ route("user.membership.subscribe", ["id" => $level->id, "period" => "monthly"]) }}" class="btn btn-primary btn-block">选择</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row d-none" id="yearly-plans">
                        @foreach($levels as $level)
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 {{ $level->is_popular ? "border-primary" : "" }} {{ $currentLevel && $currentLevel->id === $level->id ? "bg-light" : "" }}">
                                    @if($level->is_popular)
                                        <div class="ribbon ribbon-top-right"><span>热门</span></div>
                                    @endif
                                    @if($level->is_recommended)
                                        <div class="ribbon ribbon-top-left"><span>推荐</span></div>
                                    @endif
                                    <div class="card-header text-center {{ $level->is_popular ? "bg-primary text-white" : "" }}">
                                        @if($level->icon)
                                            <img src="{{ asset("storage/" . $level->icon) }}" alt="{{ $level->name }}" class="mb-2" style="height: 40px;">
                                        @endif
                                        <h5 class="mb-0">{{ $level->name }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-4">
                                            <h3 class="mb-0">{{ number_format($level->yearly_price, 2) }}<small class="text-muted">/年</small></h3>
                                            @if($level->monthly_price * 12 > $level->yearly_price)
                                                <div class="badge badge-danger mt-2">
                                                    节省 {{ round((1 - $level->yearly_price / ($level->monthly_price * 12)) * 100) }}%
                                                </div>
                                            @endif
                                        </div>

                                        <ul class="list-unstyled">
                                            <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> API请求限制: {{ $level->api_rate_limit }} 次/分钟</li>
                                            <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> API每日限额: {{ number_format($level->api_daily_limit) }} 次/天</li>
                                            <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> 存储空间: {{ $level->storage_limit }} GB</li>
                                            <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> 购买折扣: {{ $level->discount_percent }}%</li>
                                            <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> 团队成员: {{ $level->max_team_members }} 人</li>
                                            <li class="mb-2"><i class="fas fa-{{ $level->priority_support ? "check" : "times" }}-circle {{ $level->priority_support ? "text-success" : "text-danger" }} mr-2"></i> 优先支持</li>
                                            @if($level->features)
                                                @php
                                                    $features = json_decode($level->features, true);
                                                    $featureLabels = [
                                                        "advanced_models" => "高级模型访问权限",
                                                        "early_access" => "新功能抢先体验",
                                                        "api_access" => "API访问权限",
                                                        "custom_domain" => "自定义域名",
                                                        "white_label" => "白标解决方案"
                                                    ];
                                                @endphp
                                                
                                                @foreach($features as $feature)
                                                    @if(isset($featureLabels[$feature]))
                                                        <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> {{ $featureLabels[$feature] }}</li>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </ul>
                                    </div>
                                    <div class="card-footer text-center">
                                        @if($currentLevel && $currentLevel->id === $level->id)
                                            <button class="btn btn-success btn-block" disabled>当前等级</button>
                                        @elseif($currentLevel && $currentLevel->level < $level->level)
                                            <a href="{{ route("user.membership.subscribe", ["id" => $level->id, "period" => "yearly"]) }}" class="btn btn-primary btn-block">升级</a>
                                        @elseif($currentLevel && $currentLevel->level > $level->level)
                                            <a href="{{ route("user.membership.subscribe", ["id" => $level->id, "period" => "yearly"]) }}" class="btn btn-outline-primary btn-block">降级</a>
                                        @else
                                            <a href="{{ route("user.membership.subscribe", ["id" => $level->id, "period" => "yearly"]) }}" class="btn btn-primary btn-block">选择</a>
                                        @endif
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
        $("#monthly-btn").click(function() {
            $(this).addClass("active");
            $("#yearly-btn").removeClass("active");
            $("#monthly-plans").removeClass("d-none");
            $("#yearly-plans").addClass("d-none");
        });
        
        $("#yearly-btn").click(function() {
            $(this).addClass("active");
            $("#monthly-btn").removeClass("active");
            $("#yearly-plans").removeClass("d-none");
            $("#monthly-plans").addClass("d-none");
        });
    });
</script>
@endsection
