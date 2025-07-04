@extends("admin.layouts.app")

@section("title", "编辑通知")

@section("content")
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="row mb-4">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">编辑通知</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route("admin.dashboard") }}">首页</a></li>
                <li class="breadcrumb-item"><a href="{{ route("admin.notifications.index") }}">通知管理</a></li>
                <li class="breadcrumb-item active">编辑通知</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">编辑通知</h3>
                </div>
                <form action="{{ route("admin.notifications.update", $notification->id) }}" method="POST" enctype="multipart/form-data" id="notification-form">
                    @csrf
                    @method("PUT")
                    <div class="card-body">
                        <!-- 通知类型 -->
                        <div class="form-group">
                            <label for="type">通知类型 <span class="text-danger">*</span></label>
                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                <label class="btn btn-outline-info {{ $notification->type == "system" ? "active" : "" }}">
                                    <input type="radio" name="type" value="system" {{ $notification->type == "system" ? "checked" : "" }}> 系统通知
                                </label>
                                <label class="btn btn-outline-primary {{ $notification->type == "user" ? "active" : "" }}">
                                    <input type="radio" name="type" value="user" {{ $notification->type == "user" ? "checked" : "" }}> 用户通知
                                </label>
                                <label class="btn btn-outline-warning {{ $notification->type == "email" ? "active" : "" }}">
                                    <input type="radio" name="type" value="email" {{ $notification->type == "email" ? "checked" : "" }}> 邮件通知
                                </label>
                                <label class="btn btn-outline-secondary {{ $notification->type == "api" ? "active" : "" }}">
                                    <input type="radio" name="type" value="api" {{ $notification->type == "api" ? "checked" : "" }}> API通知
                                </label>
                            </div>
                            @error("type")
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <!-- 通知标题 -->
                                <div class="form-group">
                                    <label for="title">标题 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error("title") is-invalid @enderror" id="title" name="title" value="{{ old("title", $notification->title) }}" required>
                                    @error("title")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <!-- 使用模板 -->
                                <div class="form-group">
                                    <label for="template_id">使用模板</label>
                                    <div class="input-group">
                                        <select class="form-control select2 @error("template_id") is-invalid @enderror" id="template_id" name="template_id">
                                            <option value="">不使用模板</option>
                                            @foreach($templates as $template)
                                                <option value="{{ $template->id }}" {{ old("template_id", $notification->template_id) == $template->id ? "selected" : "" }}>
                                                    {{ $template->name }} ({{ $template->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <a href="{{ route("admin.notification.templates.create") }}" class="btn btn-outline-secondary">
                                                <i class="fas fa-plus"></i> 新建模板
                                            </a>
                                        </div>
                                    </div>
                                    @error("template_id")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <!-- 通知内容 -->
                                <div class="form-group">
                                    <label for="content">通知内容 <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error("content") is-invalid @enderror" id="content" name="content" rows="6" required>{{ old("content", $notification->content) }}</textarea>
                                    @error("content")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">支持变量替换，使用 {变量名} 格式</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <!-- 优先级 -->
                                <div class="form-group">
                                    <label for="priority">优先级</label>
                                    <select class="form-control @error("priority") is-invalid @enderror" id="priority" name="priority">
                                        <option value="low" {{ old("priority", $notification->priority) == "low" ? "selected" : "" }}>低</option>
                                        <option value="normal" {{ old("priority", $notification->priority) == "normal" ? "selected" : "" }}>普通</option>
                                        <option value="high" {{ old("priority", $notification->priority) == "high" ? "selected" : "" }}>高</option>
                                        <option value="urgent" {{ old("priority", $notification->priority) == "urgent" ? "selected" : "" }}>紧急</option>
                                    </select>
                                    @error("priority")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <!-- 计划发送时间 -->
                                <div class="form-group">
                                    <label for="scheduled_at">计划发送时间</label>
                                    <div class="input-group date" id="scheduled-datetime" data-target-input="nearest">
                                        <input type="text" class="form-control datetimepicker-input @error("scheduled_at") is-invalid @enderror" id="scheduled_at" name="scheduled_at" data-target="#scheduled-datetime" value="{{ old("scheduled_at", $notification->scheduled_at ? $notification->scheduled_at->format("Y-m-d H:i:s") : "") }}">
                                        <div class="input-group-append" data-target="#scheduled-datetime" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="far fa-calendar-alt"></i></div>
                                        </div>
                                    </div>
                                    @error("scheduled_at")
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">留空表示立即发送</small>
                                </div>
                                
                                <!-- 状态 -->
                                <div class="form-group">
                                    <label for="status">状态</label>
                                    <select class="form-control @error("status") is-invalid @enderror" id="status" name="status">
                                        <option value="draft" {{ old("status", $notification->status) == "draft" ? "selected" : "" }}>草稿</option>
                                        <option value="sending" {{ old("status", $notification->status) == "sending" ? "selected" : "" }}>发送中</option>
                                        <option value="sent" {{ old("status", $notification->status) == "sent" ? "selected" : "" }}>已发送</option>
                                        <option value="failed" {{ old("status", $notification->status) == "failed" ? "selected" : "" }}>发送失败</option>
                                    </select>
                                    @error("status")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">保存修改</button>
                        <a href="{{ route("admin.notifications.show", $notification->id) }}" class="btn btn-default">取消</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
    $(function () {
        // 初始化日期时间选择器
        $("#scheduled-datetime").datetimepicker({
            format: "YYYY-MM-DD HH:mm:ss",
            icons: {
                time: "far fa-clock",
                date: "far fa-calendar",
                up: "fas fa-arrow-up",
                down: "fas fa-arrow-down"
            }
        });
        
        // 初始化Select2
        $(".select2").select2({
            theme: "bootstrap4"
        });
    });
</script>
@endsection
