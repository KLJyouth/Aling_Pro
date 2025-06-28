<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title') - AlingAi 新闻中心</title>
    <meta name="description" content="@yield('meta_description', 'AlingAi新闻中心，提供最新的AI技术资讯和行业动态')">
    <meta name="keywords" content="@yield('meta_keywords', 'AlingAi,人工智能,AI新闻,技术资讯')">
    <!-- SEO优化标签 -->
    <meta property="og:title" content="@yield('og_title', 'AlingAi 新闻中心')">
    <meta property="og:description" content="@yield('og_description', 'AlingAi新闻中心，提供最新的AI技术资讯和行业动态')">
    <meta property="og:image" content="@yield('og_image', asset('assets/images/news/default-cover.jpg'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    
    <!-- 引入CSS文件 -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/news.css') }}">
    @yield('styles')
    
    <!-- 结构化数据 -->
    @yield('structured_data')
</head>
<body>
    <!-- 头部导航 -->
    <header class="news-header">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="{{ route('news.index') }}">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="AlingAi Logo" height="40">
                    <span>新闻中心</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#newsNavbar" aria-controls="newsNavbar" aria-expanded="false" aria-label="切换导航">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="newsNavbar">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('news.index') ? 'active' : '' }}" href="{{ route('news.index') }}">首页</a>
                        </li>
                        @foreach(\App\Models\News\NewsCategory::getMainCategories() as $category)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('news/category/'.$category->slug) ? 'active' : '' }}" href="{{ route('news.category', $category->slug) }}">{{ $category->name }}</a>
                        </li>
                        @endforeach
                    </ul>
                    <form class="d-flex" action="{{ route('news.index') }}" method="GET">
                        <input class="form-control me-2" type="search" name="search" placeholder="搜索新闻" aria-label="搜索" value="{{ request()->input('search') }}">
                        <button class="btn btn-outline-light" type="submit">搜索</button>
                    </form>
                </div>
            </div>
        </nav>
    </header>

    <!-- 主要内容 -->
    <main class="news-main py-4">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- 页脚 -->
    <footer class="news-footer bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>关于我们</h5>
                    <p>AlingAi致力于提供最前沿的人工智能技术和解决方案，为企业和个人用户创造价值。</p>
                </div>
                <div class="col-md-4">
                    <h5>快速链接</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('news.index') }}" class="text-light">新闻首页</a></li>
                        <li><a href="{{ url('/') }}" class="text-light">返回主站</a></li>
                        <li><a href="{{ url('/contact') }}" class="text-light">联系我们</a></li>
                        <li><a href="{{ url('/about') }}" class="text-light">关于AlingAi</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>关注我们</h5>
                    <div class="social-links">
                        <a href="#" class="text-light me-2"><i class="fab fa-weixin"></i></a>
                        <a href="#" class="text-light me-2"><i class="fab fa-weibo"></i></a>
                        <a href="#" class="text-light me-2"><i class="fab fa-github"></i></a>
                        <a href="#" class="text-light me-2"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p>&copy; {{ date('Y') }} AlingAi Pro. 保留所有权利。</p>
            </div>
        </div>
    </footer>

    <!-- 引入JS文件 -->
    <script src="{{ asset('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/news.js') }}"></script>
    @yield('scripts')
    
    <!-- 统计和分析代码 -->
    <script>
        // 页面访问统计
        $(document).ready(function() {
            $.post("{{ route('news.analytics.pageview') }}", {
                url: window.location.href,
                title: document.title,
                _token: "{{ csrf_token() }}"
            });
        });
    </script>
</body>
</html>