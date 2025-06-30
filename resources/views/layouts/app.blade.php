<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield("title", config("app.name", "AlingAi Pro"))</title>
    
    <!-- 网站图标 -->
    <link rel="icon" href="{{ asset("favicon.ico") }}">
    
    <!-- 字体 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- 样式 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset("css/app.css") }}" rel="stylesheet">
    
    <!-- 页面特定样式 -->
    @yield("styles")
</head>
<body>
    <div id="app">
        <!-- 导航栏 -->
        @include("layouts.partials.navbar")
        
        <!-- 主要内容 -->
        <main>
            @yield("content")
        </main>
        
        <!-- 页脚 -->
        @include("layouts.partials.footer")
    </div>
    
    <!-- 脚本 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset("js/app.js") }}"></script>
    
    <!-- 页面特定脚本 -->
    @yield("scripts")
</body>
</html>
