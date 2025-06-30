<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ url("/") }}">
            <img src="{{ asset("images/logo.png") }}" alt="{{ config("app.name", "AlingAi Pro") }}" height="40">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __("Toggle navigation") }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- 左侧导航链接 -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is("/") ? "active" : "" }}" href="{{ url("/") }}">首页</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is("features*") ? "active" : "" }}" href="{{ route("features") }}">功能</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is("pricing*") ? "active" : "" }}" href="{{ route("pricing") }}">价格</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is("api-docs*") ? "active" : "" }}" href="{{ route("api-docs") }}">API文档</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is("blog*") ? "active" : "" }}" href="{{ route("blog") }}">博客</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is("contact*") ? "active" : "" }}" href="{{ route("contact") }}">联系我们</a>
                </li>
            </ul>

            <!-- 右侧导航链接 -->
            <ul class="navbar-nav ms-auto">
                <!-- 认证链接 -->
                @guest
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is("login*") ? "active" : "" }}" href="{{ route("login") }}">登录</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is("register*") ? "active" : "" }}" href="{{ route("register") }}">注册</a>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is("dashboard*") ? "active" : "" }}" href="{{ route("dashboard") }}">控制台</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ Auth::user()->name }}
                        </a>

                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route("profile") }}">
                                <i class="fas fa-user fa-fw me-2"></i>个人资料
                            </a>
                            <a class="dropdown-item" href="{{ route("subscription") }}">
                                <i class="fas fa-crown fa-fw me-2"></i>我的会员
                            </a>
                            <a class="dropdown-item" href="{{ route("api-keys") }}">
                                <i class="fas fa-key fa-fw me-2"></i>API密钥
                            </a>
                            <a class="dropdown-item" href="{{ route("billing") }}">
                                <i class="fas fa-credit-card fa-fw me-2"></i>账单与支付
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route("logout") }}"
                               onclick="event.preventDefault();
                                             document.getElementById("logout-form").submit();">
                                <i class="fas fa-sign-out-alt fa-fw me-2"></i>退出登录
                            </a>

                            <form id="logout-form" action="{{ route("logout") }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
