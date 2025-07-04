@extends("admin.layouts.app")

@section("title", "创建通知")

@section("content")
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="row mb-4">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">创建通知</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route("admin.dashboard") }}">首页</a></li>
                <li class="breadcrumb-item"><a href="{{ route("admin.notifications.index") }}">通知管理</a></li>
                <li class="breadcrumb-item active">创建通知</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">新建通知</h3>
                </div>
                <form action="{{ route("admin.notifications.store") }}" method="POST" enctype="multipart/form-data" id="notification-form">
                    @csrf
                    <div class="card-body">
                        <!-- 通知类型选择 -->
                        <div class="form-group">
                            <label for="type">通知类型 <span class="text-danger">*</span></label>
                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                <label class="btn btn-outline-info active">
                                    <input type="radio" name="type" value="system" checked> 系统通知
                                </label>
                                <label class="btn btn-outline-primary">
                                    <input type="radio" name="type" value="user"> 用户通知
                                </label>
                                <label class="btn btn-outline-warning">
                                    <input type="radio" name="type" value="email"> 邮件通知
                                </label>
                                <label class="btn btn-outline-secondary">
                                    <input type="radio" name="type" value="api"> API通知
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
                                    <input type="text" class="form-control @error("title") is-invalid @enderror" id="title" name="title" value="{{ old("title") }}" required>
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
                                                <option value="{{ $template->id }}" data-type="{{ $template->type }}" data-content="{{ $template->content }}" data-html-content="{{ $template->html_content }}" data-subject="{{ $template->subject }}">
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
                                    <textarea class="form-control @error("content") is-invalid @enderror" id="content" name="content" rows="6" required>{{ old("content") }}</textarea>
                                    @error("content")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">支持变量替换，使用 {变量名} 格式</small>
                                </div>
                                
                                <!-- HTML内容 (邮件通知) -->
                                <div class="form-group email-field" style="display: none;">
                                    <label for="html_content">HTML内容</label>
                                    <textarea class="form-control html-editor @error("html_content") is-invalid @enderror" id="html_content" name="html_content" rows="10">{{ old("html_content") }}</textarea>
                                    @error("html_content")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <!-- 邮件主题 (邮件通知) -->
                                <div class="form-group email-field" style="display: none;">
                                    <label for="subject">邮件主题 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error("subject") is-invalid @enderror" id="subject" name="subject" value="{{ old("subject") }}">
                                    @error("subject")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <!-- API端点 (API通知) -->
                                <div class="form-group api-field" style="display: none;">
                                    <label for="api_endpoint">API端点 <span class="text-danger">*</span></label>
                                    <input type="url" class="form-control @error("api_endpoint") is-invalid @enderror" id="api_endpoint" name="api_endpoint" value="{{ old("api_endpoint") }}">
                                    @error("api_endpoint")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <!-- API认证令牌 (API通知) -->
                                <div class="form-group api-field" style="display: none;">
                                    <label for="api_token">API认证令牌</label>
                                    <input type="text" class="form-control @error("api_token") is-invalid @enderror" id="api_token" name="api_token" value="{{ old("api_token") }}">
                                    @error("api_token")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <!-- 附件 -->
                                <div class="form-group email-field" style="display: none;">
                                    <label for="attachments">附件</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="attachments" name="attachments[]" multiple>
                                            <label class="custom-file-label" for="attachments">选择文件</label>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">最多上传5个文件，每个文件不超过10MB</small>
                                    @error("attachments")
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <div id="attachment-list" class="mt-2"></div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <!-- 优先级 -->
                                <div class="form-group">
                                    <label for="priority">优先级</label>
                                    <select class="form-control @error("priority") is-invalid @enderror" id="priority" name="priority">
                                        <option value="low" {{ old("priority") == "low" ? "selected" : "" }}>低</option>
                                        <option value="normal" {{ old("priority") == "normal" ? "selected" : (old("priority") ? "" : "selected") }}>普通</option>
                                        <option value="high" {{ old("priority") == "high" ? "selected" : "" }}>高</option>
                                        <option value="urgent" {{ old("priority") == "urgent" ? "selected" : "" }}>紧急</option>
                                    </select>
                                    @error("priority")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <!-- 计划发送时间 -->
                                <div class="form-group">
                                    <label for="scheduled_at">计划发送时间</label>
                                    <div class="input-group date" id="scheduled-datetime" data-target-input="nearest">
                                        <input type="text" class="form-control datetimepicker-input @error("scheduled_at") is-invalid @enderror" id="scheduled_at" name="scheduled_at" data-target="#scheduled-datetime" value="{{ old("scheduled_at") }}">
                                        <div class="input-group-append" data-target="#scheduled-datetime" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="far fa-calendar-alt"></i></div>
                                        </div>
                                    </div>
                                    @error("scheduled_at")
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">留空表示立即发送</small>
                                </div>
                                
                                <!-- 邮件接口 (邮件通知) -->
                                <div class="form-group email-field" style="display: none;">
                                    <label for="email_provider_id">邮件接口</label>
                                    <select class="form-control @error("email_provider_id") is-invalid @enderror" id="email_provider_id" name="email_provider_id">
                                        <option value="">使用默认接口</option>
                                        @foreach($emailProviders as $provider)
                                            <option value="{{ $provider->id }}" {{ old("email_provider_id") == $provider->id ? "selected" : "" }}>
                                                {{ $provider->name }} ({{ $provider->from_email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("email_provider_id")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <!-- 发件人邮箱 (邮件通知) -->
                                <div class="form-group email-field" style="display: none;">
                                    <label for="from_email">发件人邮箱</label>
                                    <input type="email" class="form-control @error("from_email") is-invalid @enderror" id="from_email" name="from_email" value="{{ old("from_email") }}" placeholder="留空使用接口默认值">
                                    @error("from_email")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <!-- 发件人名称 (邮件通知) -->
                                <div class="form-group email-field" style="display: none;">
                                    <label for="from_name">发件人名称</label>
                                    <input type="text" class="form-control @error("from_name") is-invalid @enderror" id="from_name" name="from_name" value="{{ old("from_name") }}" placeholder="留空使用接口默认值">
                                    @error("from_name")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <!-- 回复邮箱 (邮件通知) -->
                                <div class="form-group email-field" style="display: none;">
                                    <label for="reply_to">回复邮箱</label>
                                    <input type="email" class="form-control @error("reply_to") is-invalid @enderror" id="reply_to" name="reply_to" value="{{ old("reply_to") }}" placeholder="留空使用接口默认值">
                                    @error("reply_to")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <!-- 元数据 -->
                                <div class="form-group">
                                    <label for="metadata">元数据 (JSON)</label>
                                    <textarea class="form-control @error("metadata") is-invalid @enderror" id="metadata" name="metadata" rows="3">{{ old("metadata") }}</textarea>
                                    @error("metadata")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
