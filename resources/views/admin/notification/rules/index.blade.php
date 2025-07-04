@extends("admin.layouts.app")

@section("title", "通知自动规则")

@section("content")
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="row mb-4">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">通知自动规则</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route("admin.dashboard") }}">首页</a></li>
                <li class="breadcrumb-item"><a href="{{ route("admin.notifications.index") }}">通知管理</a></li>
                <li class="breadcrumb-item active">自动规则</li>
            </ol>
        </div>
    </div>

    <!-- 操作按钮和筛选器 -->
    <div class="row mb-3">
        <div class="col-md-8">
            <a href="{{ route("admin.notification.rules.create") }}" class="btn btn-primary mr-2">
                <i class="fas fa-plus"></i> 创建规则
            </a>
            <button id="bulkToggle" class="btn btn-warning mr-2" disabled>
                <i class="fas fa-toggle-on"></i> 批量启用/禁用
            </button>
            <button id="bulkDelete" class="btn btn-danger" disabled>
                <i class="fas fa-trash"></i> 批量删除
            </button>
        </div>
        <div class="col-md-4">
            <form action="{{ route("admin.notification.rules.index") }}" method="GET" class="form-inline float-right">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="搜索规则..." value="{{ request("search") }}">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 规则列表 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">规则列表</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th width="30px">
                            <div class="icheck-primary">
                                <input type="checkbox" id="selectAll">
                                <label for="selectAll"></label>
                            </div>
                        </th>
                        <th>ID</th>
                        <th>名称</th>
                        <th>事件类型</th>
                        <th>模板</th>
                        <th>状态</th>
                        <th>创建者</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $rule)
                    <tr>
                        <td>
                            <div class="icheck-primary">
                                <input type="checkbox" class="rule-checkbox" id="rule{{ $rule->id }}" value="{{ $rule->id }}">
                                <label for="rule{{ $rule->id }}"></label>
                            </div>
                        </td>
                        <td>{{ $rule->id }}</td>
                        <td>
                            <a href="{{ route("admin.notification.rules.show", $rule->id) }}" class="text-bold">
                                {{ $rule->name }}
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $eventTypes[$rule->event_type] ?? $rule->event_type }}</span>
                        </td>
                        <td>
                            @if($rule->template)
                                <a href="{{ route("admin.notification.templates.show", $rule->template_id) }}">
                                    {{ $rule->template->name }}
                                </a>
                            @else
                                <span class="text-muted">无模板</span>
                            @endif
                        </td>
                        <td>
                            @if($rule->is_active)
                                <span class="badge badge-success">启用</span>
                            @else
                                <span class="badge badge-secondary">禁用</span>
                            @endif
                        </td>
                        <td>
                            {{ $rule->creator ? $rule->creator->name : "系统" }}
                        </td>
                        <td>
                            {{ $rule->created_at->format("Y-m-d H:i") }}
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route("admin.notification.rules.show", $rule->id) }}" class="btn btn-sm btn-info" title="查看">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route("admin.notification.rules.edit", $rule->id) }}" class="btn btn-sm btn-primary" title="编辑">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-{{ $rule->is_active ? "warning" : "success" }} toggle-rule" title="{{ $rule->is_active ? "禁用" : "启用" }}" data-id="{{ $rule->id }}" data-status="{{ $rule->is_active ? 1 : 0 }}">
                                    <i class="fas fa-{{ $rule->is_active ? "toggle-off" : "toggle-on" }}"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-rule" title="删除" data-id="{{ $rule->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">暂无规则数据</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $rules->appends(request()->except("page"))->links() }}
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
    $(function () {
        // 删除规则
        $(".delete-rule").click(function() {
            var ruleId = $(this).data("id");
            if (confirm("确定要删除此规则吗？此操作不可撤销。")) {
                var form = $("<form method=\"POST\"></form>");
                form.attr("action", "{{ route("admin.notification.rules.index") }}/" + ruleId);
                form.append("@csrf");
                form.append("@method("DELETE")");
                $("body").append(form);
                form.submit();
            }
        });

        // 启用/禁用规则
        $(".toggle-rule").click(function() {
            var ruleId = $(this).data("id");
            var currentStatus = $(this).data("status");
            var newStatus = currentStatus == 1 ? 0 : 1;
            var confirmMsg = currentStatus == 1 ? "确定要禁用此规则吗？" : "确定要启用此规则吗？";
            
            if (confirm(confirmMsg)) {
                $.ajax({
                    url: "{{ route("admin.notification.rules.toggle") }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: ruleId,
                        status: newStatus
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert("操作失败: " + response.message);
                        }
                    },
                    error: function(xhr) {
                        alert("操作失败: " + xhr.responseText);
                    }
                });
            }
        });

        // 全选/取消全选
        $("#selectAll").click(function() {
            $(".rule-checkbox").prop("checked", this.checked);
            updateBulkButtons();
        });

        // 更新批量操作按钮状态
        $(".rule-checkbox").click(function() {
            updateBulkButtons();
        });

        function updateBulkButtons() {
            var checkedCount = $(".rule-checkbox:checked").length;
            $("#bulkDelete, #bulkToggle").prop("disabled", checkedCount === 0);
        }

        // 批量删除
        $("#bulkDelete").click(function() {
            var ids = [];
            $(".rule-checkbox:checked").each(function() {
                ids.push($(this).val());
            });
            
            if (confirm("确定要删除所选的规则吗？此操作不可撤销。")) {
                var form = $("<form method=\"POST\"></form>");
                form.attr("action", "{{ route("admin.notification.rules.bulk-delete") }}");
                form.append("@csrf");
                form.append("@method("DELETE")");
                form.append("<input type=\"hidden\" name=\"ids\" value=\"" + ids.join(",") + "\">");
                $("body").append(form);
                form.submit();
            }
        });

        // 批量启用/禁用
        $("#bulkToggle").click(function() {
            var ids = [];
            $(".rule-checkbox:checked").each(function() {
                ids.push($(this).val());
            });
            
            var status = confirm("选择操作：\n确定 - 启用所选规则\n取消 - 禁用所选规则") ? 1 : 0;
            var confirmMsg = status == 1 ? "确定要启用所选规则吗？" : "确定要禁用所选规则吗？";
            
            if (confirm(confirmMsg)) {
                var form = $("<form method=\"POST\"></form>");
                form.attr("action", "{{ route("admin.notification.rules.bulk-toggle") }}");
                form.append("@csrf");
                form.append("<input type=\"hidden\" name=\"ids\" value=\"" + ids.join(",") + "\">");
                form.append("<input type=\"hidden\" name=\"status\" value=\"" + status + "\">");
                $("body").append(form);
                form.submit();
            }
        });
    });
</script>
@endsection
