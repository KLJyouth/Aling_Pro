<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield("title", config("app.name", "AlingAi Pro"))</title>
    
    <!-- ��վͼ�� -->
    <link rel="icon" href="{{ asset("favicon.ico") }}">
    
    <!-- ���� -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- ��ʽ -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset("css/app.css") }}" rel="stylesheet">
    
    <!-- ҳ���ض���ʽ -->
    @yield("styles")
</head>
<body>
    <div id="app">
        <!-- ������ -->
        @include("layouts.partials.navbar")
        
        <!-- ��Ҫ���� -->
        <main>
            @yield("content")
        </main>
        
        <!-- ҳ�� -->
        @include("layouts.partials.footer")
    </div>
    
    <!-- �ű� -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset("js/app.js") }}"></script>
    
    <!-- ҳ���ض��ű� -->
    @yield("scripts")
</body>
</html>
