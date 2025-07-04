@extends("admin.layouts.app")

@section("title", "创建通知规则")

@section("content")
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="row mb-4">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">创建通知规则</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route("admin.dashboard") }}">首页</a></li>
                <li class="breadcrumb-item"><a href="{{ route("admin.notifications.index") }}">通知管理</a></li>
                <li class="breadcrumb-item"><a href="{{ route("admin.notification.rules.index") }}">自动规则</a></li>
                <li class="breadcrumb-item active">创建规则</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">新建通知规则</h3>
                </div>
                <form action="{{ route("admin.notification.rules.store") }}" method="POST" id="rule-form">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <!-- 规则名称 -->
                                <div class="form-group">
                                    <label for="name">规则名称 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error("name") is-invalid @enderror" id="name" name="name" value="{{ old("name") }}" required>
                                    @error("name")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- 规则描述 -->
                                <div class="form-group">
                                    <label for="description">规则描述</label>
                                    <textarea class="form-control @error("description") is-invalid @enderror" id="description" name="description" rows="3">{{ old("description") }}</textarea>
                                    @error("description")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- 事件类型 -->
                                <div class="form-group">
                                    <label for="event_type">触发事件类型 <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error("event_type") is-invalid @enderror" id="event_type" name="event_type" required>
                                        <option value="">请选择事件类型</option>
                                        @foreach($eventTypes as $type => $name)
                                            <option value="{{ $type }}" {{ old("event_type") == $type ? "selected" : "" }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error("event_type")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- 通知模板 -->
                                <div class="form-group">
                                    <label for="template_id">使用模板</label>
                                    <div class="input-group">
                                        <select class="form-control select2 @error("template_id") is-invalid @enderror" id="template_id" name="template_id">
                                            <option value="">不使用模板</option>
                                            @foreach($templates as $template)
                                                <option value="{{ $template->id }}" {{ old("template_id") == $template->id ? "selected" : "" }}>
                                                    {{ $template->name }} ({{ $template->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <a href="{{ route("admin.notification.templates.create") }}" class="btn btn-outline-secondary" target="_blank">
                                                <i class="fas fa-plus"></i> 新建模板
                                            </a>
                                        </div>
                                    </div>
                                    @error("template_id")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- 是否激活 -->
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old("is_active", "1") == "1" ? "checked" : "" }}>
                                        <label class="custom-control-label" for="is_active">立即激活此规则</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- 触发条件 -->
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h3 class="card-title">触发条件</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>条件逻辑</label>
                                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                                <label class="btn btn-outline-primary active">
                                                    <input type="radio" name="conditions[logic]" value="AND" checked> 满足所有条件 (AND)
                                                </label>
                                                <label class="btn btn-outline-primary">
                                                    <input type="radio" name="conditions[logic]" value="OR"> 满足任一条件 (OR)
                                                </label>
                                            </div>
                                        </div>

                                        <div id="conditions-container">
                                            <div class="condition-item card mb-3">
                                                <div class="card-body pb-1">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>字段</label>
                                                                <input type="text" class="form-control condition-field" name="conditions[items][0][field]" placeholder="字段名">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>操作符</label>
                                                                <select class="form-control condition-operator" name="conditions[items][0][operator]">
                                                                    <option value="=">等于 (=)</option>
                                                                    <option value="!=">不等于 (!=)</option>
                                                                    <option value=">">大于 (>)</option>
                                                                    <option value=">=">大于等于 (>=)</option>
                                                                    <option value="<">小于 (<)</option>
                                                                    <option value="<=">小于等于 (<=)</option>
                                                                    <option value="in">包含于列表 (in)</option>
                                                                    <option value="not_in">不包含于列表 (not in)</option>
                                                                    <option value="contains">包含字符串 (contains)</option>
                                                                    <option value="not_contains">不包含字符串 (not contains)</option>
                                                                    <option value="starts_with">开头是 (starts with)</option>
                                                                    <option value="ends_with">结尾是 (ends with)</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>值</label>
                                                                <input type="text" class="form-control condition-value" name="conditions[items][0][value]" placeholder="值">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-1">
                                                            <div class="form-group">
                                                                <label>&nbsp;</label>
                                                                <button type="button" class="btn btn-danger btn-block remove-condition">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button" class="btn btn-info btn-sm" id="add-condition">
                                            <i class="fas fa-plus"></i> 添加条件
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">保存规则</button>
                        <a href="{{ route("admin.notification.rules.index") }}" class="btn btn-default">取消</a>
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
        // 初始化Select2
        $(".select2").select2({
            theme: "bootstrap4",
            width: "100%"
        });
        
        // 添加条件
        let conditionIndex = 1;
        
        $("#add-condition").click(function() {
            const template = `
                <div class="condition-item card mb-3">
                    <div class="card-body pb-1">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>字段</label>
                                    <input type="text" class="form-control condition-field" name="conditions[items][${conditionIndex}][field]" placeholder="字段名">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>操作符</label>
                                    <select class="form-control condition-operator" name="conditions[items][${conditionIndex}][operator]">
                                        <option value="=">等于 (=)</option>
                                        <option value="!=">不等于 (!=)</option>
                                        <option value=">">大于 (>)</option>
                                        <option value=">=">大于等于 (>=)</option>
                                        <option value="<">小于 (<)</option>
                                        <option value="<=">小于等于 (<=)</option>
                                        <option value="in">包含于列表 (in)</option>
                                        <option value="not_in">不包含于列表 (not in)</option>
                                        <option value="contains">包含字符串 (contains)</option>
                                        <option value="not_contains">不包含字符串 (not contains)</option>
                                        <option value="starts_with">开头是 (starts with)</option>
                                        <option value="ends_with">结尾是 (ends with)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>值</label>
                                    <input type="text" class="form-control condition-value" name="conditions[items][${conditionIndex}][value]" placeholder="值">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-danger btn-block remove-condition">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $("#conditions-container").append(template);
            conditionIndex++;
        });
        
        // 删除条件
        $(document).on("click", ".remove-condition", function() {
            $(this).closest(".condition-item").remove();
        });
        
        // 根据事件类型显示相关字段提示
        $("#event_type").change(function() {
            const eventType = $(this).val();
            let fieldHints = [];
            
            // 根据事件类型设置字段提示
            switch(eventType) {
                case "user_registered":
                    fieldHints = ["user_id", "name", "email", "created_at"];
                    break;
                case "order_created":
                    fieldHints = ["order_id", "user_id", "amount", "status", "created_at"];
                    break;
                case "payment_received":
                    fieldHints = ["payment_id", "order_id", "user_id", "amount", "payment_method", "created_at"];
                    break;
                // 可以添加更多事件类型的字段提示
            }
            
            // 更新字段输入框的placeholder
            if (fieldHints.length > 0) {
                $(".condition-field").attr("placeholder", "可用字段: " + fieldHints.join(", "));
            } else {
                $(".condition-field").attr("placeholder", "字段名");
            }
        });
    });
</script>
@endsection
