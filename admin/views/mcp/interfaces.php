<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCP接口管理 - AlingAi Pro</title>
    <link rel="stylesheet" href="/admin/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
    <link rel="stylesheet" href="/admin/assets/css/datatables.min.css">
</head>
<body>
    <?php include_once "../includes/header.php"; ?>
    <?php include_once "../includes/sidebar.php"; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1>MCP接口管理</h1>
                <p>管理和监控MCP接口的使用情况</p>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">接口列表</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInterfaceModal">
                            添加接口
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="interfacesTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>名称</th>
                                    <th>端点</th>
                                    <th>方法</th>
                                    <th>状态</th>
                                    <th>需要认证</th>
                                    <th>速率限制</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- 数据将通过AJAX加载 -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">接口调用日志</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 row">
                        <div class="col-md-3">
                            <label for="interfaceFilter" class="form-label">接口</label>
                            <select class="form-select" id="interfaceFilter">
                                <option value="">全部接口</option>
                                <!-- 接口选项将通过AJAX加载 -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="statusFilter" class="form-label">状态码</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">全部状态</option>
                                <option value="200">200 - 成功</option>
                                <option value="400">400 - 请求错误</option>
                                <option value="401">401 - 未授权</option>
                                <option value="403">403 - 禁止访问</option>
                                <option value="404">404 - 未找到</option>
                                <option value="500">500 - 服务器错误</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="dateRangeFilter" class="form-label">日期范围</label>
                            <input type="text" class="form-control" id="dateRangeFilter" placeholder="选择日期范围">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary" onclick="filterLogs()">筛选</button>
                            <button type="button" class="btn btn-secondary ms-2" onclick="resetFilters()">重置</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="logsTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>接口</th>
                                    <th>方法</th>
                                    <th>端点</th>
                                    <th>状态码</th>
                                    <th>响应时间</th>
                                    <th>IP地址</th>
                                    <th>用户ID</th>
                                    <th>时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- 数据将通过AJAX加载 -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 添加接口模态框 -->
    <div class="modal fade" id="addInterfaceModal" tabindex="-1" aria-labelledby="addInterfaceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addInterfaceModalLabel">添加接口</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
                </div>
                <div class="modal-body">
                    <form id="addInterfaceForm">
                        <div class="mb-3">
                            <label for="name" class="form-label">名称</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="endpoint" class="form-label">端点</label>
                            <input type="text" class="form-control" id="endpoint" name="endpoint" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">描述</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="method" class="form-label">请求方法</label>
                            <select class="form-select" id="method" name="method" required>
                                <option value="GET">GET</option>
                                <option value="POST">POST</option>
                                <option value="PUT">PUT</option>
                                <option value="DELETE">DELETE</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="parameters" class="form-label">参数定义 (JSON)</label>
                            <textarea class="form-control" id="parameters" name="parameters" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="response_format" class="form-label">响应格式 (JSON)</label>
                            <textarea class="form-control" id="response_format" name="response_format" rows="3"></textarea>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                            <label class="form-check-label" for="is_active">激活</label>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="requires_auth" name="requires_auth" checked>
                            <label class="form-check-label" for="requires_auth">需要认证</label>
                        </div>
                        <div class="mb-3">
                            <label for="rate_limit" class="form-label">速率限制 (每分钟)</label>
                            <input type="number" class="form-control" id="rate_limit" name="rate_limit" value="60" min="1">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" onclick="saveInterface()">保存</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 编辑接口模态框 -->
    <div class="modal fade" id="editInterfaceModal" tabindex="-1" aria-labelledby="editInterfaceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editInterfaceModalLabel">编辑接口</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
                </div>
                <div class="modal-body">
                    <form id="editInterfaceForm">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">名称</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_endpoint" class="form-label">端点</label>
                            <input type="text" class="form-control" id="edit_endpoint" name="endpoint" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">描述</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_method" class="form-label">请求方法</label>
                            <select class="form-select" id="edit_method" name="method" required>
                                <option value="GET">GET</option>
                                <option value="POST">POST</option>
                                <option value="PUT">PUT</option>
                                <option value="DELETE">DELETE</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_parameters" class="form-label">参数定义 (JSON)</label>
                            <textarea class="form-control" id="edit_parameters" name="parameters" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_response_format" class="form-label">响应格式 (JSON)</label>
                            <textarea class="form-control" id="edit_response_format" name="response_format" rows="3"></textarea>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active">
                            <label class="form-check-label" for="edit_is_active">激活</label>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="edit_requires_auth" name="requires_auth">
                            <label class="form-check-label" for="edit_requires_auth">需要认证</label>
                        </div>
                        <div class="mb-3">
                            <label for="edit_rate_limit" class="form-label">速率限制 (每分钟)</label>
                            <input type="number" class="form-control" id="edit_rate_limit" name="rate_limit" min="1">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" onclick="updateInterface()">更新</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 查看日志详情模态框 -->
    <div class="modal fade" id="viewLogModal" tabindex="-1" aria-labelledby="viewLogModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewLogModalLabel">日志详情</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">请求数据</label>
                        <pre id="log_request_data" class="bg-light p-3 rounded"></pre>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">响应数据</label>
                        <pre id="log_response_data" class="bg-light p-3 rounded"></pre>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">用户代理</label>
                        <pre id="log_user_agent" class="bg-light p-3 rounded"></pre>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                </div>
            </div>
        </div>
    </div>

    <?php include_once "../includes/footer.php"; ?>

    <script src="/admin/assets/js/jquery.min.js"></script>
    <script src="/admin/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/admin/assets/js/datatables.min.js"></script>
    <script src="/admin/assets/js/moment.min.js"></script>
    <script src="/admin/assets/js/daterangepicker.min.js"></script>
    <script src="/admin/assets/js/admin.js"></script>
    <script>
        // 页面加载完成后执行
        $(document).ready(function() {
            // 初始化接口表格
            window.interfacesTable = $("#interfacesTable").DataTable({
                ajax: {
                    url: "/api/v1/mcp/interfaces",
                    dataSrc: function(json) {
                        return json.data || [];
                    }
                },
                columns: [
                    { data: "id" },
                    { data: "name" },
                    { data: "endpoint" },
                    { data: "method" },
                    { 
                        data: "is_active",
                        render: function(data) {
                            return data ? 
                                "<span class=\"badge bg-success\">激活</span>" : 
                                "<span class=\"badge bg-danger\">禁用</span>";
                        }
                    },
                    { 
                        data: "requires_auth",
                        render: function(data) {
                            return data ? 
                                "<span class=\"badge bg-info\">是</span>" : 
                                "<span class=\"badge bg-warning\">否</span>";
                        }
                    },
                    { data: "rate_limit" },
                    {
                        data: null,
                        render: function(data) {
                            return "<div class=\"btn-group\" role=\"group\">" +
                                "<button type=\"button\" class=\"btn btn-sm btn-primary\" onclick=\"editInterface(" + data.id + ")\">编辑</button>" +
                                "<button type=\"button\" class=\"btn btn-sm btn-danger\" onclick=\"deleteInterface(" + data.id + ")\">删除</button>" +
                                "</div>";
                        }
                    }
                ],
                order: [[0, "desc"]],
                language: {
                    url: "/admin/assets/js/datatables.chinese.json"
                }
            });
            
            // 初始化日志表格
            window.logsTable = $("#logsTable").DataTable({
                ajax: {
                    url: "/api/v1/mcp/logs",
                    dataSrc: function(json) {
                        return json.data || [];
                    }
                },
                columns: [
                    { data: "id" },
                    { data: "interface_name" },
                    { data: "method" },
                    { data: "endpoint" },
                    { 
                        data: "status_code",
                        render: function(data) {
                            let badgeClass = "bg-success";
                            
                            if (data >= 400 && data < 500) {
                                badgeClass = "bg-warning";
                            } else if (data >= 500) {
                                badgeClass = "bg-danger";
                            }
                            
                            return "<span class=\"badge " + badgeClass + "\">" + data + "</span>";
                        }
                    },
                    { 
                        data: "response_time",
                        render: function(data) {
                            return data ? data.toFixed(2) + " ms" : "-";
                        }
                    },
                    { data: "ip_address" },
                    { data: "user_id" },
                    { 
                        data: "created_at",
                        render: function(data) {
                            return moment(data).format("YYYY-MM-DD HH:mm:ss");
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            return "<button type=\"button\" class=\"btn btn-sm btn-info\" onclick=\"viewLog(" + data.id + ")\">查看</button>";
                        }
                    }
                ],
                order: [[0, "desc"]],
                language: {
                    url: "/admin/assets/js/datatables.chinese.json"
                }
            });
            
            // 加载接口下拉列表
            loadInterfacesDropdown();
            
            // 初始化日期范围选择器
            $("#dateRangeFilter").daterangepicker({
                locale: {
                    format: "YYYY-MM-DD",
                    applyLabel: "确定",
                    cancelLabel: "取消",
                    fromLabel: "从",
                    toLabel: "至",
                    customRangeLabel: "自定义",
                    daysOfWeek: ["日", "一", "二", "三", "四", "五", "六"],
                    monthNames: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"]
                },
                startDate: moment().subtract(7, "days"),
                endDate: moment()
            });
        });
        
        // 加载接口下拉列表
        function loadInterfacesDropdown() {
            $.ajax({
                url: "/api/v1/mcp/interfaces",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        const interfaces = response.data || [];
                        let options = "<option value=\"\">全部接口</option>";
                        
                        interfaces.forEach(function(item) {
                            options += "<option value=\"" + item.id + "\">" + item.name + " (" + item.endpoint + ")</option>";
                        });
                        
                        $("#interfaceFilter").html(options);
                    }
                }
            });
        }
        
        // 筛选日志
        function filterLogs() {
            const interfaceId = $("#interfaceFilter").val();
            const statusCode = $("#statusFilter").val();
            const dateRange = $("#dateRangeFilter").val();
            
            let url = "/api/v1/mcp/logs?";
            
            if (interfaceId) {
                url += "interface_id=" + interfaceId + "&";
            }
            
            if (statusCode) {
                url += "status_code=" + statusCode + "&";
            }
            
            if (dateRange) {
                const dates = dateRange.split(" - ");
                url += "start_date=" + dates[0] + "&end_date=" + dates[1] + "&";
            }
            
            // 重新加载表格数据
            window.logsTable.ajax.url(url).load();
        }
        
        // 重置筛选条件
        function resetFilters() {
            $("#interfaceFilter").val("");
            $("#statusFilter").val("");
            $("#dateRangeFilter").data("daterangepicker").setStartDate(moment().subtract(7, "days"));
            $("#dateRangeFilter").data("daterangepicker").setEndDate(moment());
            
            // 重新加载表格数据
            window.logsTable.ajax.url("/api/v1/mcp/logs").load();
        }
        
        // 保存接口
        function saveInterface() {
            const formData = {};
            
            // 收集表单数据
            $("#addInterfaceForm").serializeArray().forEach(function(item) {
                formData[item.name] = item.value;
            });
            
            // 处理复选框
            formData.is_active = $("#is_active").is(":checked");
            formData.requires_auth = $("#requires_auth").is(":checked");
            
            // 处理JSON字段
            try {
                if (formData.parameters) {
                    formData.parameters = JSON.parse(formData.parameters);
                } else {
                    formData.parameters = {};
                }
                
                if (formData.response_format) {
                    formData.response_format = JSON.parse(formData.response_format);
                } else {
                    formData.response_format = {};
                }
            } catch (e) {
                alert("参数定义或响应格式JSON格式错误");
                return;
            }
            
            $.ajax({
                url: "/api/v1/mcp/interfaces",
                type: "POST",
                dataType: "json",
                contentType: "application/json",
                data: JSON.stringify(formData),
                success: function(response) {
                    if (response.success) {
                        alert("接口添加成功！");
                        $("#addInterfaceModal").modal("hide");
                        window.interfacesTable.ajax.reload();
                        loadInterfacesDropdown();
                    } else {
                        alert("接口添加失败：" + response.message);
                    }
                },
                error: function() {
                    alert("无法连接到服务器");
                }
            });
        }
        
        // 编辑接口
        function editInterface(id) {
            $.ajax({
                url: "/api/v1/mcp/interfaces/" + id,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        
                        // 填充表单
                        $("#edit_id").val(data.id);
                        $("#edit_name").val(data.name);
                        $("#edit_endpoint").val(data.endpoint);
                        $("#edit_description").val(data.description);
                        $("#edit_method").val(data.method);
                        $("#edit_parameters").val(JSON.stringify(data.parameters, null, 2));
                        $("#edit_response_format").val(JSON.stringify(data.response_format, null, 2));
                        $("#edit_is_active").prop("checked", data.is_active);
                        $("#edit_requires_auth").prop("checked", data.requires_auth);
                        $("#edit_rate_limit").val(data.rate_limit);
                        
                        // 显示模态框
                        $("#editInterfaceModal").modal("show");
                    } else {
                        alert("获取接口详情失败：" + response.message);
                    }
                },
                error: function() {
                    alert("无法连接到服务器");
                }
            });
        }
        
        // 更新接口
        function updateInterface() {
            const id = $("#edit_id").val();
            const formData = {};
            
            // 收集表单数据
            $("#editInterfaceForm").serializeArray().forEach(function(item) {
                if (item.name !== "id") {
                    formData[item.name] = item.value;
                }
            });
            
            // 处理复选框
            formData.is_active = $("#edit_is_active").is(":checked");
            formData.requires_auth = $("#edit_requires_auth").is(":checked");
            
            // 处理JSON字段
            try {
                if (formData.parameters) {
                    formData.parameters = JSON.parse(formData.parameters);
                } else {
                    formData.parameters = {};
                }
                
                if (formData.response_format) {
                    formData.response_format = JSON.parse(formData.response_format);
                } else {
                    formData.response_format = {};
                }
            } catch (e) {
                alert("参数定义或响应格式JSON格式错误");
                return;
            }
            
            $.ajax({
                url: "/api/v1/mcp/interfaces/" + id,
                type: "PUT",
                dataType: "json",
                contentType: "application/json",
                data: JSON.stringify(formData),
                success: function(response) {
                    if (response.success) {
                        alert("接口更新成功！");
                        $("#editInterfaceModal").modal("hide");
                        window.interfacesTable.ajax.reload();
                        loadInterfacesDropdown();
                    } else {
                        alert("接口更新失败：" + response.message);
                    }
                },
                error: function() {
                    alert("无法连接到服务器");
                }
            });
        }
        
        // 删除接口
        function deleteInterface(id) {
            if (confirm("确定要删除这个接口吗？")) {
                $.ajax({
                    url: "/api/v1/mcp/interfaces/" + id,
                    type: "DELETE",
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            alert("接口删除成功！");
                            window.interfacesTable.ajax.reload();
                            loadInterfacesDropdown();
                        } else {
                            alert("接口删除失败：" + response.message);
                        }
                    },
                    error: function() {
                        alert("无法连接到服务器");
                    }
                });
            }
        }
        
        // 查看日志详情
        function viewLog(id) {
            $.ajax({
                url: "/api/v1/mcp/logs/" + id,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        
                        // 填充模态框
                        $("#log_request_data").text(JSON.stringify(data.request_data, null, 2));
                        $("#log_response_data").text(JSON.stringify(data.response_data, null, 2));
                        $("#log_user_agent").text(data.user_agent || "");
                        
                        // 显示模态框
                        $("#viewLogModal").modal("show");
                    } else {
                        alert("获取日志详情失败：" + response.message);
                    }
                },
                error: function() {
                    alert("无法连接到服务器");
                }
            });
        }
    </script>
</body>
</html>
