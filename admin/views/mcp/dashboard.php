<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCP控制面板 - AlingAi Pro</title>
    <link rel="stylesheet" href="/admin/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
    <link rel="stylesheet" href="/admin/assets/css/charts.css">
    <script src="/admin/assets/js/chart.min.js"></script>
</head>
<body>
    <?php include_once "../includes/header.php"; ?>
    <?php include_once "../includes/sidebar.php"; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1>MCP控制面板</h1>
                <p>管理控制平台(Management Control Platform)提供系统级别的管理和控制功能</p>
            </div>

            <div class="row">
                <!-- 系统状态卡片 -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">系统状态</h5>
                            <div class="status-indicator" id="systemStatusIndicator">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">加载中...</span>
                                </div>
                            </div>
                            <p class="card-text" id="systemStatusText">正在获取系统状态...</p>
                        </div>
                    </div>
                </div>

                <!-- CPU使用率卡片 -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">CPU使用率</h5>
                            <div class="resource-gauge" id="cpuGauge">
                                <canvas id="cpuChart"></canvas>
                            </div>
                            <p class="card-text" id="cpuText">正在获取CPU使用率...</p>
                        </div>
                    </div>
                </div>

                <!-- 内存使用率卡片 -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">内存使用率</h5>
                            <div class="resource-gauge" id="memoryGauge">
                                <canvas id="memoryChart"></canvas>
                            </div>
                            <p class="card-text" id="memoryText">正在获取内存使用率...</p>
                        </div>
                    </div>
                </div>

                <!-- 磁盘使用率卡片 -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">磁盘使用率</h5>
                            <div class="resource-gauge" id="diskGauge">
                                <canvas id="diskChart"></canvas>
                            </div>
                            <p class="card-text" id="diskText">正在获取磁盘使用率...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- 用户统计卡片 -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">用户统计</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="userStatsChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- API使用统计卡片 -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">API使用统计</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="apiStatsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- 系统维护卡片 -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">系统维护</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <button type="button" class="list-group-item list-group-item-action" onclick="runMaintenanceTask(\
clear_cache\)">
                                    清除缓存
                                </button>
                                <button type="button" class="list-group-item list-group-item-action" onclick="runMaintenanceTask(\optimize_database\)">
                                    优化数据库
                                </button>
                                <button type="button" class="list-group-item list-group-item-action" onclick="runMaintenanceTask(\backup_database\)">
                                    备份数据库
                                </button>
                                <button type="button" class="list-group-item list-group-item-action" onclick="runMaintenanceTask(\update_system_settings\)">
                                    更新系统设置
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 系统配置卡片 -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">系统配置</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="configGroupSelect" class="form-label">配置组</label>
                                <select class="form-select" id="configGroupSelect" onchange="loadSystemConfig(this.value)">
                                    <option value="general">通用配置</option>
                                    <option value="api">API配置</option>
                                    <option value="security">安全配置</option>
                                    <option value="monitoring">监控配置</option>
                                </select>
                            </div>
                            <div id="configEditor">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">加载中...</span>
                                </div>
                                <p>正在加载配置...</p>
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-primary" onclick="saveSystemConfig()">保存配置</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once "../includes/footer.php"; ?>

    <script src="/admin/assets/js/jquery.min.js"></script>
    <script src="/admin/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/admin/assets/js/admin.js"></script>
    <script>
        // 页面加载完成后执行
        $(document).ready(function() {
            // 获取系统状态
            getSystemStatus();
            
            // 获取资源使用情况
            getResourceUsage();
            
            // 获取用户统计
            getUserStats();
            
            // 获取API统计
            getApiStats();
            
            // 加载默认配置组
            loadSystemConfig("general");
            
            // 设置定时刷新
            setInterval(function() {
                getSystemStatus();
                getResourceUsage();
            }, 60000); // 每分钟刷新一次
        });
        
        // 获取系统状态
        function getSystemStatus() {
            $.ajax({
                url: "/api/v1/mcp/system/status",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        const status = response.data;
                        
                        // 更新状态指示器
                        $("#systemStatusIndicator").html("");
                        if (status.status === "normal") {
                            $("#systemStatusIndicator").html("<div class=\"status-dot status-normal\"></div>");
                            $("#systemStatusText").text("系统运行正常");
                        } else if (status.status === "warning") {
                            $("#systemStatusIndicator").html("<div class=\"status-dot status-warning\"></div>");
                            $("#systemStatusText").text("系统警告: " + status.message);
                        } else if (status.status === "error") {
                            $("#systemStatusIndicator").html("<div class=\"status-dot status-error\"></div>");
                            $("#systemStatusText").text("系统错误: " + status.message);
                        }
                    } else {
                        $("#systemStatusIndicator").html("<div class=\"status-dot status-unknown\"></div>");
                        $("#systemStatusText").text("无法获取系统状态");
                    }
                },
                error: function() {
                    $("#systemStatusIndicator").html("<div class=\"status-dot status-unknown\"></div>");
                    $("#systemStatusText").text("无法连接到服务器");
                }
            });
        }
        
        // 获取资源使用情况
        function getResourceUsage() {
            $.ajax({
                url: "/api/v1/mcp/system/resources",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        const resources = response.data;
                        
                        // 更新CPU使用率
                        updateGaugeChart("cpuChart", resources.cpu_usage, "CPU");
                        $("#cpuText").text("CPU使用率: " + resources.cpu_usage + "%");
                        
                        // 更新内存使用率
                        updateGaugeChart("memoryChart", resources.memory_usage, "内存");
                        $("#memoryText").text("内存使用率: " + resources.memory_usage + "%");
                        
                        // 更新磁盘使用率
                        updateGaugeChart("diskChart", resources.disk_usage, "磁盘");
                        $("#diskText").text("磁盘使用率: " + resources.disk_usage + "%");
                    } else {
                        $("#cpuText").text("无法获取CPU使用率");
                        $("#memoryText").text("无法获取内存使用率");
                        $("#diskText").text("无法获取磁盘使用率");
                    }
                },
                error: function() {
                    $("#cpuText").text("无法连接到服务器");
                    $("#memoryText").text("无法连接到服务器");
                    $("#diskText").text("无法连接到服务器");
                }
            });
        }
        
        // 更新仪表盘图表
        function updateGaugeChart(chartId, value, label) {
            const ctx = document.getElementById(chartId).getContext("2d");
            
            // 销毁现有图表
            if (window[chartId + "Instance"]) {
                window[chartId + "Instance"].destroy();
            }
            
            // 创建新图表
            window[chartId + "Instance"] = new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: [label, ""],
                    datasets: [{
                        data: [value, 100 - value],
                        backgroundColor: [getColorByValue(value), "#f0f0f0"],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: "70%",
                    rotation: -90,
                    circumference: 180,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        }
                    },
                    animation: {
                        animateRotate: true,
                        animateScale: false
                    }
                }
            });
        }
        
        // 根据值获取颜色
        function getColorByValue(value) {
            if (value < 60) {
                return "#28a745"; // 绿色
            } else if (value < 80) {
                return "#ffc107"; // 黄色
            } else {
                return "#dc3545"; // 红色
            }
        }
        
        // 获取用户统计
        function getUserStats() {
            $.ajax({
                url: "/api/v1/mcp/users/stats",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        const stats = response.data;
                        
                        // 创建用户统计图表
                        const ctx = document.getElementById("userStatsChart").getContext("2d");
                        
                        // 销毁现有图表
                        if (window.userStatsChartInstance) {
                            window.userStatsChartInstance.destroy();
                        }
                        
                        // 创建新图表
                        window.userStatsChartInstance = new Chart(ctx, {
                            type: "line",
                            data: {
                                labels: stats.dates,
                                datasets: [{
                                    label: "新用户",
                                    data: stats.new_users,
                                    borderColor: "#007bff",
                                    backgroundColor: "rgba(0, 123, 255, 0.1)",
                                    borderWidth: 2,
                                    fill: true
                                }, {
                                    label: "活跃用户",
                                    data: stats.active_users,
                                    borderColor: "#28a745",
                                    backgroundColor: "rgba(40, 167, 69, 0.1)",
                                    borderWidth: 2,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: "top"
                                    },
                                    tooltip: {
                                        mode: "index",
                                        intersect: false
                                    }
                                },
                                scales: {
                                    x: {
                                        display: true,
                                        title: {
                                            display: true,
                                            text: "日期"
                                        }
                                    },
                                    y: {
                                        display: true,
                                        title: {
                                            display: true,
                                            text: "用户数"
                                        },
                                        min: 0
                                    }
                                }
                            }
                        });
                    }
                }
            });
        }
        
        // 获取API统计
        function getApiStats() {
            $.ajax({
                url: "/api/v1/mcp/api/stats",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        const stats = response.data;
                        
                        // 创建API统计图表
                        const ctx = document.getElementById("apiStatsChart").getContext("2d");
                        
                        // 销毁现有图表
                        if (window.apiStatsChartInstance) {
                            window.apiStatsChartInstance.destroy();
                        }
                        
                        // 创建新图表
                        window.apiStatsChartInstance = new Chart(ctx, {
                            type: "bar",
                            data: {
                                labels: stats.endpoints,
                                datasets: [{
                                    label: "API调用次数",
                                    data: stats.calls,
                                    backgroundColor: "rgba(0, 123, 255, 0.7)",
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: "top"
                                    },
                                    tooltip: {
                                        mode: "index",
                                        intersect: false
                                    }
                                },
                                scales: {
                                    x: {
                                        display: true,
                                        title: {
                                            display: true,
                                            text: "API端点"
                                        }
                                    },
                                    y: {
                                        display: true,
                                        title: {
                                            display: true,
                                            text: "调用次数"
                                        },
                                        min: 0
                                    }
                                }
                            }
                        });
                    }
                }
            });
        }
        
        // 执行系统维护任务
        function runMaintenanceTask(task) {
            if (confirm("确定要执行" + getTaskName(task) + "任务吗？")) {
                $.ajax({
                    url: "/api/v1/mcp/system/maintenance/" + task,
                    type: "POST",
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            alert(getTaskName(task) + "任务执行成功！");
                        } else {
                            alert("任务执行失败：" + response.message);
                        }
                    },
                    error: function() {
                        alert("无法连接到服务器");
                    }
                });
            }
        }
        
        // 获取任务名称
        function getTaskName(task) {
            const taskNames = {
                "clear_cache": "清除缓存",
                "optimize_database": "优化数据库",
                "backup_database": "备份数据库",
                "update_system_settings": "更新系统设置"
            };
            
            return taskNames[task] || task;
        }
        
        // 加载系统配置
        function loadSystemConfig(configGroup) {
            $.ajax({
                url: "/api/v1/mcp/system/config/" + configGroup,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        const config = response.data;
                        
                        // 创建配置编辑器
                        let html = "<form id=\"configForm\">";
                        
                        for (const key in config) {
                            const value = config[key];
                            const type = typeof value;
                            
                            html += "<div class=\"mb-3\">";
                            html += "<label for=\"config_" + key + "\" class=\"form-label\">" + formatKey(key) + "</label>";
                            
                            if (type === "boolean") {
                                html += "<select class=\"form-select\" id=\"config_" + key + "\" name=\"" + key + "\">";
                                html += "<option value=\"true\"" + (value ? " selected" : "") + ">是</option>";
                                html += "<option value=\"false\"" + (!value ? " selected" : "") + ">否</option>";
                                html += "</select>";
                            } else if (Array.isArray(value)) {
                                html += "<textarea class=\"form-control\" id=\"config_" + key + "\" name=\"" + key + "\" rows=\"3\">" + JSON.stringify(value) + "</textarea>";
                            } else if (type === "object") {
                                html += "<textarea class=\"form-control\" id=\"config_" + key + "\" name=\"" + key + "\" rows=\"3\">" + JSON.stringify(value) + "</textarea>";
                            } else {
                                html += "<input type=\"text\" class=\"form-control\" id=\"config_" + key + "\" name=\"" + key + "\" value=\"" + value + "\">";
                            }
                            
                            html += "</div>";
                        }
                        
                        html += "</form>";
                        
                        $("#configEditor").html(html);
                    } else {
                        $("#configEditor").html("<div class=\"alert alert-danger\">无法加载配置：" + response.message + "</div>");
                    }
                },
                error: function() {
                    $("#configEditor").html("<div class=\"alert alert-danger\">无法连接到服务器</div>");
                }
            });
        }
        
        // 格式化配置键
        function formatKey(key) {
            return key.replace(/_/g, " ").replace(/\b\w/g, l => l.toUpperCase());
        }
        
        // 保存系统配置
        function saveSystemConfig() {
            const configGroup = $("#configGroupSelect").val();
            const formData = {};
            
            // 收集表单数据
            $("#configForm").serializeArray().forEach(function(item) {
                let value = item.value;
                
                // 尝试解析JSON
                try {
                    if (value.startsWith("[") || value.startsWith("{")) {
                        value = JSON.parse(value);
                    }
                } catch (e) {
                    // 不是有效的JSON，保持原样
                }
                
                // 转换布尔值
                if (value === "true") {
                    value = true;
                } else if (value === "false") {
                    value = false;
                }
                
                formData[item.name] = value;
            });
            
            $.ajax({
                url: "/api/v1/mcp/system/config/" + configGroup,
                type: "PUT",
                dataType: "json",
                contentType: "application/json",
                data: JSON.stringify({
                    config_data: formData
                }),
                success: function(response) {
                    if (response.success) {
                        alert("配置保存成功！");
                    } else {
                        alert("配置保存失败：" + response.message);
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
