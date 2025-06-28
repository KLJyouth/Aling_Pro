<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title') - AlingAi 新闻管理</title>
    
    <!-- 引入CSS文件 -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    
    <!-- 引入富文本编辑器CSS -->
    <link href="{{ asset('assets/plugins/summernote/summernote-bs4.min.css') }}" rel="stylesheet">
    
    <!-- 引入图片上传CSS -->
    <link href="{{ asset('assets/plugins/dropzone/dropzone.min.css') }}" rel="stylesheet">
    
    <!-- 引入标签输入CSS -->
    <link href="{{ asset('assets/plugins/tagsinput/bootstrap-tagsinput.css') }}" rel="stylesheet">
    
    @yield('styles')
</head>
<body>
    <div class="wrapper">
        <!-- 侧边栏 -->
        <nav id="sidebar" class="active">
            <div class="sidebar-header">
                <h3>AlingAi 管理</h3>
                <strong>AI</strong>
            </div>

            <ul class="list-unstyled components">
                <li>
                    <a href="{{ url('/admin/dashboard') }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span class="menu-text">控制面板</span>
                    </a>
                </li>
                <li class="active">
                    <a href="#newsSubmenu" data-bs-toggle="collapse" aria-expanded="true" class="dropdown-toggle">
                        <i class="fas fa-newspaper"></i>
                        <span class="menu-text">新闻管理</span>
                    </a>
                    <ul class="collapse list-unstyled show" id="newsSubmenu">
                        <li>
                            <a href="{{ route('admin.news.index') }}">新闻列表</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.news.create') }}">添加新闻</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.news.categories.index') }}">分类管理</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.news.tags.index') }}">标签管理</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.news.comments.index') }}">评论管理</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="{{ url('/admin/settings') }}">
                        <i class="fas fa-cogs"></i>
                        <span class="menu-text">系统设置</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('/admin/users') }}">
                        <i class="fas fa-users"></i>
                        <span class="menu-text">用户管理</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('/admin/tickets') }}">
                        <i class="fas fa-ticket-alt"></i>
                        <span class="menu-text">工单管理</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- 页面内容 -->
        <div id="content">
            <!-- 顶部导航 -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-info">
                        <i class="fas fa-align-left"></i>
                        <span>切换侧边栏</span>
                    </button>
                    <button class="btn btn-dark d-inline-block d-lg-none ml-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="fas fa-align-justify"></i>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="nav navbar-nav ms-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('/') }}" target="_blank">
                                    <i class="fas fa-home"></i> 网站首页
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user"></i> {{ Auth::user()->name }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="{{ url('/admin/profile') }}">个人资料</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            退出登录
                                        </a>
                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- 主要内容 -->
            <div class="container-fluid">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <!-- 引入JS文件 -->
    <script src="{{ asset('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    
    <!-- 引入富文本编辑器JS -->
    <script src="{{ asset('assets/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/summernote/lang/summernote-zh-CN.min.js') }}"></script>
    
    <!-- 引入图片上传JS -->
    <script src="{{ asset('assets/plugins/dropzone/dropzone.min.js') }}"></script>
    
    <!-- 引入标签输入JS -->
    <script src="{{ asset('assets/plugins/tagsinput/bootstrap-tagsinput.min.js') }}"></script>
    
    <!-- 侧边栏切换 -->
    <script>
        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html>