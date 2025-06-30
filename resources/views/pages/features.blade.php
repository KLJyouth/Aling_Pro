@extends("layouts.app")

@section("title", "功能特性")

@section("content")
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 mb-4">强大的 AI 功能</h1>
            <p class="lead text-muted">探索 AlingAi 平台提供的丰富功能，助力您的业务发展和创新。</p>
        </div>
    </div>
    
    <!-- 核心功能 -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <h2 class="h3 mb-4 text-center">核心功能</h2>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="icon-box mb-4">
                                <i class="fas fa-robot fa-3x text-primary"></i>
                            </div>
                            <h3 class="h5 card-title">自然语言处理</h3>
                            <p class="card-text">强大的自然语言处理能力，支持文本分析、情感分析、实体识别等功能。</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="icon-box mb-4">
                                <i class="fas fa-image fa-3x text-primary"></i>
                            </div>
                            <h3 class="h5 card-title">计算机视觉</h3>
                            <p class="card-text">先进的图像识别和处理技术，支持物体检测、人脸识别、场景理解等功能。</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="icon-box mb-4">
                                <i class="fas fa-brain fa-3x text-primary"></i>
                            </div>
                            <h3 class="h5 card-title">机器学习</h3>
                            <p class="card-text">强大的机器学习框架，支持数据分析、预测建模、异常检测等功能。</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 自然语言处理 -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="row g-0">
                    <div class="col-md-6">
                        <div class="card-body p-5">
                            <h2 class="h3 mb-4">自然语言处理</h2>
                            <p class="mb-4">我们的自然语言处理技术能够理解和生成人类语言，帮助您从文本数据中提取有价值的信息。</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 文本分类与情感分析
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 命名实体识别与关系提取
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 自动文本摘要与生成
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 多语言翻译与理解
                                </li>
                                <li>
                                    <i class="fas fa-check text-primary me-2"></i> 问答系统与对话机器人
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 bg-light d-flex align-items-center">
                        <img src="{{ asset("images/features/nlp.jpg") }}" class="img-fluid" alt="自然语言处理">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 计算机视觉 -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="row g-0">
                    <div class="col-md-6 bg-light d-flex align-items-center order-md-1 order-2">
                        <img src="{{ asset("images/features/cv.jpg") }}" class="img-fluid" alt="计算机视觉">
                    </div>
                    <div class="col-md-6 order-md-2 order-1">
                        <div class="card-body p-5">
                            <h2 class="h3 mb-4">计算机视觉</h2>
                            <p class="mb-4">我们的计算机视觉技术能够理解和分析图像和视频内容，为您提供强大的视觉智能。</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 图像分类与识别
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 物体检测与追踪
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 人脸识别与分析
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 场景理解与分割
                                </li>
                                <li>
                                    <i class="fas fa-check text-primary me-2"></i> 视频分析与理解
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 机器学习 -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="row g-0">
                    <div class="col-md-6">
                        <div class="card-body p-5">
                            <h2 class="h3 mb-4">机器学习</h2>
                            <p class="mb-4">我们的机器学习技术能够从数据中学习规律和模式，为您提供智能化的决策支持。</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 预测分析与建模
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 异常检测与防欺诈
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 推荐系统与个性化
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 聚类分析与分类
                                </li>
                                <li>
                                    <i class="fas fa-check text-primary me-2"></i> 时序分析与预测
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 bg-light d-flex align-items-center">
                        <img src="{{ asset("images/features/ml.jpg") }}" class="img-fluid" alt="机器学习">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- API 功能 -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <h2 class="h3 mb-4 text-center">API 功能</h2>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <p class="mb-4">我们提供丰富的 API 接口，让您轻松集成 AI 能力到您的应用中。</p>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-code fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="h5">RESTful API</h4>
                                    <p>标准的 RESTful API 接口，支持多种编程语言和平台。</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-plug fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="h5">SDK 支持</h4>
                                    <p>提供多种编程语言的 SDK，简化集成过程。</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-lock fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="h5">安全认证</h4>
                                    <p>强大的安全认证机制，保护您的数据和请求。</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-tachometer-alt fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="h5">高性能</h4>
                                    <p>高性能的 API 服务，支持高并发和低延迟。</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="{{ route("api-docs") }}" class="btn btn-primary">
                            <i class="fas fa-book me-1"></i> 查看 API 文档
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 会员特权 -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <h2 class="h3 mb-4 text-center">会员特权</h2>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box me-3">
                                    <i class="fas fa-rocket text-primary"></i>
                                </div>
                                <h3 class="h5 card-title mb-0">更高配额</h3>
                            </div>
                            <p class="card-text">会员用户享有更高的 API 调用配额和并发请求数。</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> 更多 API 调用次数
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> 更高并发请求数
                                </li>
                                <li>
                                    <i class="fas fa-check text-success me-2"></i> 更大存储空间
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box me-3">
                                    <i class="fas fa-star text-primary"></i>
                                </div>
                                <h3 class="h5 card-title mb-0">高级功能</h3>
                            </div>
                            <p class="card-text">会员用户可以使用更多高级功能和模型。</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> 高级 AI 模型
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> 自定义训练
                                </li>
                                <li>
                                    <i class="fas fa-check text-success me-2"></i> 批量处理
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box me-3">
                                    <i class="fas fa-headset text-primary"></i>
                                </div>
                                <h3 class="h5 card-title mb-0">优先支持</h3>
                            </div>
                            <p class="card-text">会员用户享有优先技术支持和咨询服务。</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> 优先响应
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> 专属技术支持
                                </li>
                                <li>
                                    <i class="fas fa-check text-success me-2"></i> 定制化解决方案
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="{{ route("pricing") }}" class="btn btn-primary">
                    <i class="fas fa-tag me-1"></i> 查看会员价格
                </a>
            </div>
        </div>
    </div>
    
    <!-- 行业解决方案 -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <h2 class="h3 mb-4 text-center">行业解决方案</h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="h5 card-title">金融行业</h3>
                            <p class="card-text">为金融行业提供风险评估、欺诈检测、智能客服等解决方案。</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 风险评估与管理
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 欺诈检测与防范
                                </li>
                                <li>
                                    <i class="fas fa-check text-primary me-2"></i> 智能客服与咨询
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="h5 card-title">零售行业</h3>
                            <p class="card-text">为零售行业提供个性化推荐、库存管理、客户分析等解决方案。</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 个性化推荐系统
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 智能库存管理
                                </li>
                                <li>
                                    <i class="fas fa-check text-primary me-2"></i> 客户行为分析
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="h5 card-title">医疗行业</h3>
                            <p class="card-text">为医疗行业提供疾病预测、医学影像分析、健康管理等解决方案。</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 疾病预测与诊断
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 医学影像分析
                                </li>
                                <li>
                                    <i class="fas fa-check text-primary me-2"></i> 健康数据管理
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="h5 card-title">制造行业</h3>
                            <p class="card-text">为制造行业提供预测性维护、质量控制、生产优化等解决方案。</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 预测性维护
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> 质量控制与检测
                                </li>
                                <li>
                                    <i class="fas fa-check text-primary me-2"></i> 生产流程优化
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 开始使用 -->
    <div class="row">
        <div class="col-lg-8 mx-auto text-center">
            <h2 class="h3 mb-4">开始使用 AlingAi</h2>
            <p class="mb-4">立即注册，体验强大的 AI 功能，助力您的业务发展。</p>
            <div class="d-flex justify-content-center">
                <a href="{{ route("register") }}" class="btn btn-primary me-2">
                    <i class="fas fa-user-plus me-1"></i> 免费注册
                </a>
                <a href="{{ route("contact") }}" class="btn btn-outline-primary">
                    <i class="fas fa-envelope me-1"></i> 联系我们
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .icon-box {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background-color: rgba(13, 110, 253, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }
</style>
@endsection
