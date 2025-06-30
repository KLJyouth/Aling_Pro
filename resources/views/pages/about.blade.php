@extends("layouts.app")

@section("title", "关于我们")

@section("content")
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 mb-4">关于 AlingAi</h1>
            <p class="lead text-muted">我们致力于为企业和开发者提供先进的人工智能解决方案，帮助他们更好地理解数据、自动化流程并创造价值。</p>
        </div>
    </div>
    
    <!-- 我们的使命 -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h2 class="h3 mb-4">我们的使命</h2>
                    <p>AlingAi 的使命是通过人工智能技术赋能企业和个人，帮助他们更好地理解和利用数据，提高工作效率，创造更大的价值。我们相信，人工智能不仅仅是一种技术，更是一种思维方式和解决问题的工具。</p>
                    <p>我们致力于：</p>
                    <ul>
                        <li>为企业提供易用、高效的人工智能解决方案</li>
                        <li>降低人工智能技术的使用门槛，让更多人能够受益</li>
                        <li>推动人工智能技术的创新和发展</li>
                        <li>培养人工智能人才，推广人工智能教育</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 我们的历史 -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h2 class="h3 mb-4">我们的历史</h2>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h3 class="h5 mb-0">2020年</h3>
                                <p class="text-muted mb-2">公司成立</p>
                                <p>AlingAi 由一群充满激情的人工智能专家和企业家共同创立，致力于为企业提供人工智能解决方案。</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h3 class="h5 mb-0">2021年</h3>
                                <p class="text-muted mb-2">产品发布</p>
                                <p>发布了第一个产品 AlingAi Platform，为企业提供一站式人工智能解决方案。</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h3 class="h5 mb-0">2022年</h3>
                                <p class="text-muted mb-2">业务扩展</p>
                                <p>业务扩展到全国多个城市，服务客户超过100家。</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h3 class="h5 mb-0">2023年</h3>
                                <p class="text-muted mb-2">技术突破</p>
                                <p>在自然语言处理和计算机视觉领域取得重大技术突破，推出了多个创新产品。</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h3 class="h5 mb-0">2024年</h3>
                                <p class="text-muted mb-2">国际化发展</p>
                                <p>开始国际化发展，产品和服务覆盖多个国家和地区。</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 我们的团队 -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h2 class="h3 mb-4">我们的团队</h2>
                    <p>AlingAi 拥有一支由人工智能专家、软件工程师、产品设计师和业务专家组成的优秀团队。我们的团队成员来自世界各地，拥有丰富的行业经验和专业知识。</p>
                    <div class="row row-cols-1 row-cols-md-3 g-4 mt-4">
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm">
                                <img src="{{ asset("images/team/ceo.jpg") }}" class="card-img-top" alt="CEO">
                                <div class="card-body text-center">
                                    <h5 class="card-title mb-1">张明</h5>
                                    <p class="text-muted small">创始人 & CEO</p>
                                    <p class="card-text">人工智能领域专家，拥有10年以上的行业经验。</p>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm">
                                <img src="{{ asset("images/team/cto.jpg") }}" class="card-img-top" alt="CTO">
                                <div class="card-body text-center">
                                    <h5 class="card-title mb-1">李强</h5>
                                    <p class="text-muted small">CTO</p>
                                    <p class="card-text">前谷歌工程师，机器学习和自然语言处理专家。</p>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm">
                                <img src="{{ asset("images/team/cpo.jpg") }}" class="card-img-top" alt="CPO">
                                <div class="card-body text-center">
                                    <h5 class="card-title mb-1">王芳</h5>
                                    <p class="text-muted small">CPO</p>
                                    <p class="card-text">产品设计专家，专注于用户体验和产品创新。</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 我们的价值观 -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h2 class="h3 mb-4">我们的价值观</h2>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-lightbulb fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="h5">创新</h4>
                                    <p>我们鼓励创新思维，不断探索新的技术和解决方案。</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-users fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="h5">协作</h4>
                                    <p>我们相信团队协作的力量，共同创造更大的价值。</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-shield-alt fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="h5">诚信</h4>
                                    <p>我们坚持诚信原则，赢得客户和合作伙伴的信任。</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-chart-line fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="h5">卓越</h4>
                                    <p>我们追求卓越，为客户提供最好的产品和服务。</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 联系我们 -->
    <div class="row">
        <div class="col-lg-8 mx-auto text-center">
            <h2 class="h3 mb-4">联系我们</h2>
            <p>如果您有任何问题或建议，欢迎随时联系我们。</p>
            <div class="d-flex justify-content-center">
                <a href="{{ route("contact") }}" class="btn btn-primary me-2">
                    <i class="fas fa-envelope me-1"></i> 联系我们
                </a>
                <a href="{{ route("careers") }}" class="btn btn-outline-primary">
                    <i class="fas fa-briefcase me-1"></i> 加入我们
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline:before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #e9ecef;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 30px;
    }
    .timeline-marker {
        position: absolute;
        left: -39px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background-color: #0d6efd;
        border: 4px solid #fff;
        box-shadow: 0 0 0 2px #e9ecef;
    }
</style>
@endsection
