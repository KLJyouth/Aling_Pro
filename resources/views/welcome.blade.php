@extends("layouts.app")

@section("title", "AlingAi Pro - 人工智能平台")

@section("content")
<!-- 英雄区域 -->
<section class="hero bg-primary text-white py-5">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <h1 class="display-4 fw-bold mb-4">释放AI的无限潜能</h1>
                <p class="lead mb-4">AlingAi Pro提供强大的AI工具和API，帮助开发者和企业快速实现智能化应用，提升效率，创造价值。</p>
                <div class="d-flex gap-3">
                    <a href="{{ route("register") }}" class="btn btn-light btn-lg">免费注册</a>
                    <a href="{{ route("features") }}" class="btn btn-outline-light btn-lg">了解更多</a>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="{{ asset("images/hero-image.svg") }}" alt="AlingAi Pro" class="img-fluid rounded-3 shadow-lg">
            </div>
        </div>
    </div>
</section>

<!-- 特点区域 -->
<section class="features py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">强大功能，简单易用</h2>
            <p class="lead text-muted">我们提供全面的AI解决方案，满足您的各种需求</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-primary bg-gradient text-white rounded-3 p-3 mb-4">
                            <i class="fas fa-brain fa-2x"></i>
                        </div>
                        <h3 class="h4 mb-3">先进的AI模型</h3>
                        <p class="text-muted mb-0">访问最先进的AI模型，包括自然语言处理、图像识别和预测分析等。</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-success bg-gradient text-white rounded-3 p-3 mb-4">
                            <i class="fas fa-code fa-2x"></i>
                        </div>
                        <h3 class="h4 mb-3">简单易用的API</h3>
                        <p class="text-muted mb-0">通过简单易用的API，快速将AI功能集成到您的应用程序中。</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-info bg-gradient text-white rounded-3 p-3 mb-4">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                        <h3 class="h4 mb-3">实时数据分析</h3>
                        <p class="text-muted mb-0">实时分析大量数据，提取有价值的见解，帮助您做出明智的决策。</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 会员计划 -->
<section class="pricing bg-light py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">选择适合您的会员计划</h2>
            <p class="lead text-muted">灵活的会员计划，满足不同规模和需求的用户</p>
        </div>
        
        <div class="row g-4 justify-content-center">
            @foreach($membershipLevels as $level)
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm {{ $level->is_featured ? "border border-primary" : "" }}">
                    @if($level->is_featured)
                    <div class="card-header bg-primary text-white text-center py-3">
                        <span class="badge bg-white text-primary">推荐</span>
                    </div>
                    @endif
                    <div class="card-body p-4">
                        <h3 class="h4 mb-3">{{ $level->name }}</h3>
                        <div class="d-flex align-items-baseline mb-4">
                            <span class="h2 fw-bold">￥{{ $level->formatted_monthly_price }}</span>
                            <span class="text-muted ms-1">/月</span>
                        </div>
                        <ul class="list-unstyled mb-4">
                            @foreach(json_decode($level->benefits) as $benefit)
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i> {{ $benefit }}
                            </li>
                            @endforeach
                        </ul>
                        <div class="d-grid">
                            <a href="{{ route("register") }}" class="btn {{ $level->is_featured ? "btn-primary" : "btn-outline-primary" }}">选择此计划</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 客户案例 -->
<section class="testimonials py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">客户的声音</h2>
            <p class="lead text-muted">看看我们的客户如何使用AlingAi Pro</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-4">"AlingAi Pro帮助我们将客户支持自动化，大大提高了响应速度和客户满意度。"</p>
                        <div class="d-flex align-items-center">
                            <img src="{{ asset("images/testimonial-1.jpg") }}" alt="用户头像" class="rounded-circle me-3" width="48">
                            <div>
                                <h5 class="mb-0">张明</h5>
                                <p class="text-muted mb-0">科技公司CEO</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-4">"通过AlingAi Pro的API，我们能够快速开发智能应用，大大缩短了产品上市时间。"</p>
                        <div class="d-flex align-items-center">
                            <img src="{{ asset("images/testimonial-2.jpg") }}" alt="用户头像" class="rounded-circle me-3" width="48">
                            <div>
                                <h5 class="mb-0">李华</h5>
                                <p class="text-muted mb-0">技术总监</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-4">"AlingAi Pro的数据分析功能帮助我们发现了业务中的隐藏机会，显著提升了销售额。"</p>
                        <div class="d-flex align-items-center">
                            <img src="{{ asset("images/testimonial-3.jpg") }}" alt="用户头像" class="rounded-circle me-3" width="48">
                            <div>
                                <h5 class="mb-0">王丽</h5>
                                <p class="text-muted mb-0">营销总监</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 行动号召 -->
<section class="cta bg-primary text-white py-5">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h2 class="display-5 fw-bold mb-3">准备好开始您的AI之旅了吗？</h2>
                <p class="lead mb-0">立即注册，免费体验AlingAi Pro的强大功能。</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route("register") }}" class="btn btn-light btn-lg">免费注册</a>
                <a href="{{ route("contact") }}" class="btn btn-outline-light btn-lg ms-2">联系我们</a>
            </div>
        </div>
    </div>
</section>
@endsection
