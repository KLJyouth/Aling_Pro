<footer class="bg-dark text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="mb-3">关于我们</h5>
                <p class="text-muted">AlingAi Pro是一个强大的人工智能平台，提供先进的AI工具和API服务，帮助开发者和企业快速实现AI赋能。</p>
                <div class="d-flex gap-2 mt-3">
                    <a href="#" class="text-white"><i class="fab fa-facebook-f fa-lg"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-linkedin-in fa-lg"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-github fa-lg"></i></a>
                </div>
            </div>
            <div class="col-md-2 mb-4 mb-md-0">
                <h5 class="mb-3">产品</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route("features") }}" class="text-muted text-decoration-none">功能</a></li>
                    <li class="mb-2"><a href="{{ route("pricing") }}" class="text-muted text-decoration-none">价格</a></li>
                    <li class="mb-2"><a href="{{ route("api-docs") }}" class="text-muted text-decoration-none">API文档</a></li>
                    <li class="mb-2"><a href="{{ route("examples") }}" class="text-muted text-decoration-none">示例</a></li>
                </ul>
            </div>
            <div class="col-md-2 mb-4 mb-md-0">
                <h5 class="mb-3">资源</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route("blog") }}" class="text-muted text-decoration-none">博客</a></li>
                    <li class="mb-2"><a href="{{ route("tutorials") }}" class="text-muted text-decoration-none">教程</a></li>
                    <li class="mb-2"><a href="{{ route("faq") }}" class="text-muted text-decoration-none">常见问题</a></li>
                    <li class="mb-2"><a href="{{ route("support") }}" class="text-muted text-decoration-none">支持</a></li>
                </ul>
            </div>
            <div class="col-md-2 mb-4 mb-md-0">
                <h5 class="mb-3">公司</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route("about") }}" class="text-muted text-decoration-none">关于我们</a></li>
                    <li class="mb-2"><a href="{{ route("team") }}" class="text-muted text-decoration-none">团队</a></li>
                    <li class="mb-2"><a href="{{ route("careers") }}" class="text-muted text-decoration-none">招聘</a></li>
                    <li class="mb-2"><a href="{{ route("contact") }}" class="text-muted text-decoration-none">联系我们</a></li>
                </ul>
            </div>
            <div class="col-md-2 mb-4 mb-md-0">
                <h5 class="mb-3">法律</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route("terms") }}" class="text-muted text-decoration-none">服务条款</a></li>
                    <li class="mb-2"><a href="{{ route("privacy") }}" class="text-muted text-decoration-none">隐私政策</a></li>
                    <li class="mb-2"><a href="{{ route("security") }}" class="text-muted text-decoration-none">安全</a></li>
                </ul>
            </div>
        </div>
        <hr class="my-4 bg-secondary">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                <p class="text-muted mb-0">&copy; {{ date("Y") }} AlingAi Pro. 保留所有权利。</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-globe me-1"></i> 简体中文
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                        <li><a class="dropdown-item active" href="#">简体中文</a></li>
                        <li><a class="dropdown-item" href="#">English</a></li>
                        <li><a class="dropdown-item" href="#">日本語</a></li>
                        <li><a class="dropdown-item" href="#">???</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
