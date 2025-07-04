@extends("admin.layouts.app")

@section("title", "通知详情")

@section("content")
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="row mb-4">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">通知详情</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route("admin.dashboard") }}">首页</a></li>
                <li class="breadcrumb-item"><a href="{{ route("admin.notifications.index") }}">通知管理</a></li>
                <li class="breadcrumb-item active">通知详情</li>
            </ol>
        </div>
    </div>

    <!-- 操作按钮 -->
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{ route("admin.notifications.edit", $notification->id) }}" class="btn btn-primary mr-2">
                <i class="fas fa-edit"></i> 编辑通知
            </a>
            @if($notification->status == "draft")
                <a href="{{ route("admin.notifications.send", $notification->id) }}" class="btn btn-success mr-2">
                    <i class="fas fa-paper-plane"></i> 发送通知
                </a>
            @endif
            <a href="{{ route("admin.notifications.statistics", $notification->id) }}" class="btn btn-info mr-2">
                <i class="fas fa-chart-bar"></i> 统计分析
            </a>
            <form action="{{ route("admin.notifications.destroy", $notification->id) }}" method="POST" class="d-inline">
                @csrf
                @method("DELETE")
                <button type="submit" class="btn btn-danger" onclick="return confirm("确定要删除此通知吗？此操作不可撤销。")">
                    <i class="fas fa-trash"></i> 删除通知
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
