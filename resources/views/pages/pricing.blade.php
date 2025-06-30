@extends("layouts.app")

@section("title", "价格")

@section("content")
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 mb-4">灵活的价格方案</h1>
            <p class="lead text-muted">选择最适合您需求的方案，开始使用 AlingAi 强大的 AI 功能。</p>
        </div>
    </div>
    
    <!-- 价格方案 -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="row row-cols-1 row-cols-md-3 g-4">
                @foreach($membershipLevels as $level)
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm {{ $level->is_popular ? "border border-primary" : "" }}">
                        @if($level->is_popular)
                        <div class="card-header bg-primary text-white text-center py-3">
                            <span class="badge bg-white text-primary">推荐</span>
                        </div>
                        @endif
                        <div class="card-body p-4">
                            <h2 class="h4 card-title text-center mb-4">{{ $level->name }}</h2>
                            <div class="price text-center mb-4">
                                <span class="currency"></span>
                                <span class="amount display-4 fw-bold">{{ number_format($level->monthly_price, 0) }}</span>
                                <span class="period text-muted">/月</span>
                            </div>
                            <p class="text-muted text-center mb-4">{{ $level->description }}</p>
                            <ul class="list-unstyled mb-4">
                                @foreach(json_decode($level->features) as $feature)
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> {{ $feature }}
                                </li>
                                @endforeach
                            </ul>
                            <div class="text-center">
                                @auth
                                <a href="{{ route("membership.subscribe", ["id" => $level->id]) }}" class="btn {{ $level->is_popular ? "btn-primary" : "btn-outline-primary" }} w-100">
                                    选择此方案
                                </a>
                                @else
                                <a href="{{ route("register") }}" class="btn {{ $level->is_popular ? "btn-primary" : "btn-outline-primary" }} w-100">
                                    注册并选择
                                </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- 功能对比 -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <h2 class="h3 mb-4 text-center">功能对比</h2>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>功能</th>
                            @foreach($membershipLevels as $level)
                            <th class="text-center">{{ $level->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>API 调用次数</td>
                            @foreach($membershipLevels as $level)
                            <td class="text-center">{{ number_format($level->api_calls) }}/月</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>并发请求数</td>
                            @foreach($membershipLevels as $level)
                            <td class="text-center">{{ $level->concurrent_requests }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>存储空间</td>
                            @foreach($membershipLevels as $level)
                            <td class="text-center">{{ $level->storage_space }}GB</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>高级模型</td>
                            @foreach($membershipLevels as $level)
                            <td class="text-center">
                                @if($level->has_advanced_models)
                                <i class="fas fa-check text-success"></i>
                                @else
                                <i class="fas fa-times text-danger"></i>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>自定义训练</td>
                            @foreach($membershipLevels as $level)
                            <td class="text-center">
                                @if($level->has_custom_training)
                                <i class="fas fa-check text-success"></i>
                                @else
                                <i class="fas fa-times text-danger"></i>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>批量处理</td>
                            @foreach($membershipLevels as $level)
                            <td class="text-center">
                                @if($level->has_batch_processing)
                                <i class="fas fa-check text-success"></i>
                                @else
                                <i class="fas fa-times text-danger"></i>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>优先支持</td>
                            @foreach($membershipLevels as $level)
                            <td class="text-center">
                                @if($level->has_priority_support)
                                <i class="fas fa-check text-success"></i>
                                @else
                                <i class="fas fa-times text-danger"></i>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>专属技术支持</td>
                            @foreach($membershipLevels as $level)
                            <td class="text-center">
                                @if($level->has_dedicated_support)
                                <i class="fas fa-check text-success"></i>
                                @else
                                <i class="fas fa-times text-danger"></i>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- 常见问题 -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <h2 class="h3 mb-4 text-center">常见问题</h2>
            <div class="accordion" id="pricingFaq">
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h3 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            如何选择适合我的方案？
                        </button>
                    </h3>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#pricingFaq">
                        <div class="accordion-body">
                            选择方案时，您需要考虑您的使用需求，包括 API 调用次数、并发请求数、存储空间等。如果您是初次使用，可以先选择免费方案进行体验，后续根据需求升级。如果您需要更多帮助，可以联系我们的客服团队，我们会为您提供专业的建议。
                        </div>
                    </div>
                </div>
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h3 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            如何计算 API 调用次数？
                        </button>
                    </h3>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#pricingFaq">
                        <div class="accordion-body">
                            每次调用我们的 API 接口都会计算为一次 API 调用。不同的 API 接口可能会消耗不同的调用次数，具体消耗情况请参考我们的 API 文档。您可以在控制面板中查看您的 API 调用使用情况。
                        </div>
                    </div>
                </div>
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h3 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            如何升级或降级我的方案？
                        </button>
                    </h3>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#pricingFaq">
                        <div class="accordion-body">
                            您可以随时在控制面板中的会员中心页面升级或降级您的方案。升级后，新的方案将立即生效，并按比例计算费用。降级后，新的方案将在当前计费周期结束后生效。
                        </div>
                    </div>
                </div>
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h3 class="accordion-header" id="headingFour">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                            是否支持年付方案？
                        </button>
                    </h3>
                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#pricingFaq">
                        <div class="accordion-body">
                            是的，我们支持年付方案，并提供一定的优惠。年付方案可以享受 10 个月的价格使用 12 个月的服务。如果您有兴趣，可以在选择方案时切换到年付选项。
                        </div>
                    </div>
                </div>
                <div class="accordion-item border-0 shadow-sm">
                    <h3 class="accordion-header" id="headingFive">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                            是否提供企业定制方案？
                        </button>
                    </h3>
                    <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#pricingFaq">
                        <div class="accordion-body">
                            是的，我们为企业客户提供定制化的解决方案和价格方案。如果您有特殊需求，请联系我们的销售团队，我们会为您量身定制最适合的方案。
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 企业定制 -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body p-5 text-center">
                    <h2 class="h3 mb-4">需要企业定制方案？</h2>
                    <p class="mb-4">我们为企业客户提供定制化的解决方案和价格方案，满足您的特殊需求。</p>
                    <a href="{{ route("contact") }}" class="btn btn-primary">
                        <i class="fas fa-envelope me-1"></i> 联系我们
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .price .currency {
        font-size: 1.5rem;
        position: relative;
        top: -1.5rem;
    }
    .price .period {
        font-size: 1rem;
    }
</style>
@endsection
