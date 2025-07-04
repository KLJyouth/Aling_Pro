@extends('admin.layouts.app')

@section('title', 'SDK文档')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">目录</h3>
                </div>
                <div class="card-body p-0">
                    <div class="nav flex-column nav-pills">
                        <a class="nav-link active" href="#overview" data-toggle="tab">概述</a>
                        <a class="nav-link" href="#installation" data-toggle="tab">安装</a>
                        <a class="nav-link" href="#authentication" data-toggle="tab">认证</a>
                        <a class="nav-link" href="#quickstart" data-toggle="tab">快速开始</a>
                        <a class="nav-link" href="#api-reference" data-toggle="tab">API参考</a>
                        <a class="nav-link" href="#error-handling" data-toggle="tab">错误处理</a>
                        <a class="nav-link" href="#examples" data-toggle="tab">示例代码</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">SDK文档</h3>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- 概述 -->
                        <div class="tab-pane active" id="overview">
                            <h2>概述</h2>
                            <p>SDK提供了便捷的方式来访问AlingAi API。通过使用SDK，您可以轻松地将AlingAi的功能集成到您的应用程序中。</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('title', 'SDK文档 - ' . $sdk->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">目录</h3>
                </div>
                <div class="card-body p-0">
                    <div class="nav flex-column nav-pills">
                        <a class="nav-link active" href="#overview" data-toggle="tab">概述</a>
                        <a class="nav-link" href="#installation" data-toggle="tab">安装</a>
                        <a class="nav-link" href="#authentication" data-toggle="tab">认证</a>
                        <a class="nav-link" href="#quickstart" data-toggle="tab">快速开始</a>
                        <a class="nav-link" href="#api-reference" data-toggle="tab">API参考</a>
                        <a class="nav-link" href="#error-handling" data-toggle="tab">错误处理</a>
                        <a class="nav-link" href="#examples" data-toggle="tab">示例代码</a>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">SDK信息</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>名称</th>
                            <td>{{ $sdk->name }}</td>
                        </tr>
                        <tr>
                            <th>语言</th>
                            <td>{{ $sdk->language_name }}</td>
                        </tr>
                        <tr>
                            <th>当前版本</th>
                            <td>
                                @if($sdk->currentVersion)
                                    {{ $sdk->currentVersion->version }}
                                @else
                                    <span class="text-muted">无版本</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                    
                    <div class="mt-3">
                        <a href="{{ route('admin.security.api.sdks.show', $sdk->id) }}" class="btn btn-default btn-block">
                            <i class="fas fa-arrow-left"></i> 返回SDK详情
                        </a>
                        @if($sdk->currentVersion)
                        <a href="{{ route('admin.security.api.sdks.download', ['id' => $sdk->id, 'version_id' => $sdk->currentVersion->id]) }}" class="btn btn-primary btn-block">
                            <i class="fas fa-download"></i> 下载SDK
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $sdk->name }} SDK文档</h3>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- 概述 -->
                        <div class="tab-pane active" id="overview">
                            <h2>概述</h2>
                            <p>{{ $sdk->name }} SDK提供了便捷的方式来访问AlingAi API。通过使用SDK，您可以轻松地将AlingAi的功能集成到您的应用程序中。</p>
                            
                            <h3>功能特点</h3>
                            <ul>
                                <li>简化的API调用方式</li>
                                <li>自动处理认证和请求签名</li>
                                <li>类型安全的接口（适用于支持类型的语言）</li>
                                <li>内置错误处理和重试机制</li>
                                <li>详细的日志记录</li>
                            </ul>
                            
                            <h3>支持的API</h3>
                            <p>该SDK支持以下API接口：</p>
                            <ul>
                                @foreach($sdk->interfaces as $interface)
                                <li>{{ $interface->name }} - <code>{{ $interface->path }}</code></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
