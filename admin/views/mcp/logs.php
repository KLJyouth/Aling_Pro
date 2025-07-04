<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCP日志分析 - AlingAi Pro</title>
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
                <h1>MCP日志分析</h1>
                <p>分析MCP接口调用日志和性能指标</p>
            </div>

            <div class="row">
                <!-- 接口调用统计卡片 -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">总调用次数</h5>
                            <h2 class="text-primary" id="totalCalls">--</h2>
                            <p class="card-text">过去30天</p>
                        </div>
                    </div>
                </div>

                <!-- 成功率卡片 -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">成功率</h5>
                            <h2 class="text-success" id="successRate">--</h2>
                            <p class="card-text">过去30天</p>
                        </div>
                    </div>
                </div>

                <!-- 平均响应时间卡片 -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">平均响应时间</h5>
                            <h2 class="text-info" id="avgResponseTime">--</h2>
                            <p class="card-text">过去30天</p>
                        </div>
                    </div>
                </div>

                <!-- 错误率卡片 -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">错误率</h5>
                            <h2 class="text-danger" id="errorRate">--</h2>
                            <p class="card-text">过去30天</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- 每日调用趋势图 -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">每日调用趋势</h5>
                                <div>
                                    <select class="form-select form-select-sm" id="dailyTrendPeriod">
                                        <option value="7">过去7天</option>
                                        <option value="30" selected>过去30天</option>
                                        <option value="90">过去90天</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="dailyTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- 状态码分布图 -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">状态码分布</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="statusCodeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- 接口调用排行 -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">接口调用排行</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>接口</th>
                                            <th>调用次数</th>
                                            <th>占比</th>
                                        </tr>
                                    </thead>
                                    <tbody id="interfaceRankingBody">
                                        <!-- 数据将通过AJAX加载 -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 响应时间分布 -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">响应时间分布</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="responseTimeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- 错误分析 -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">错误分析</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>状态码</th>
                                            <th>接口</th>
                                            <th>次数</th>
                                            <th>最近发生</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="errorAnalysisBody">
                                        <!-- 数据将通过AJAX加载 -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 查看错误详情模态框 -->
    <div class="modal fade" id="viewErrorModal" tabindex="-1" aria-labelledby="viewErrorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewErrorModalLabel">错误详情</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">接口</label>
                        <p id="error_interface" class="fw-bold"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">状态码</label>
                        <p id="error_status_code" class="fw-bold"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">请求数据</label>
                        <pre id="error_request_data" class="bg-light p-3 rounded"></pre>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">响应数据</label>
                        <pre id="error_response_data" class="bg-light p-3 rounded"></pre>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">IP地址</label>
                        <p id="error_ip_address"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">用户代理</label>
                        <pre id="error_user_agent" class="bg-light p-3 rounded"></pre>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">时间</label>
                        <p id="error_created_at"></p>
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
    <script src="/admin/assets/js/moment.min.js"></script>
    <script src="/admin/assets/js/admin.js"></script>
    <script>
        // 页面加载完成后执行
        $(document).ready(function() {
            // 加载统计数据
            loadStatistics();
            
            // 加载每日趋势数据
            loadDailyTrend(30);
            
            // 加载状态码分布
            loadStatusCodeDistribution();
            
            // 加载接口调用排行
            loadInterfaceRanking();
            
            // 加载响应时间分布
            loadResponseTimeDistribution();
            
            // 加载错误分析
            loadErrorAnalysis();
            
            // 监听每日趋势周期选择
            $("#dailyTrendPeriod").change(function() {
                loadDailyTrend($(this).val());
            });
        });
        
        // 加载统计数据
        function loadStatistics() {
            $.ajax({
                url: "/api/v1/mcp/logs/statistics",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        const stats = response.data;
                        
                        // 更新统计卡片
                        $("#totalCalls").text(stats.total_calls.toLocaleString());
                        $("#successRate").text(stats.success_rate.toFixed(2) + "%");
                        $("#avgResponseTime").text(stats.avg_response_time.toFixed(2) + " ms");
                        $("#errorRate").text(stats.error_rate.toFixed(2) + "%");
                    }
                }
            });
        }
        
        // 加载每日趋势数据
        function loadDailyTrend(days) {
            $.ajax({
                url: "/api/v1/mcp/logs/daily-trend?days=" + days,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        
                        // 创建趋势图表
                        const ctx = document.getElementById("dailyTrendChart").getContext("2d");
                        
                        // 销毁现有图表
                        if (window.dailyTrendChartInstance) {
                            window.dailyTrendChartInstance.destroy();
                        }
                        
                        // 创建新图表
                        window.dailyTrendChartInstance = new Chart(ctx, {
                            type: "line",
                            data: {
                                labels: data.dates,
                                datasets: [{
                                    label: "总调用",
                                    data: data.total_calls,
                                    borderColor: "#007bff",
                                    backgroundColor: "rgba(0, 123, 255, 0.1)",
                                    borderWidth: 2,
                                    fill: true
                                }, {
                                    label: "成功调用",
                                    data: data.success_calls,
                                    borderColor: "#28a745",
                                    backgroundColor: "rgba(40, 167, 69, 0.1)",
                                    borderWidth: 2,
                                    fill: true
                                }, {
                                    label: "失败调用",
                                    data: data.error_calls,
                                    borderColor: "#dc3545",
                                    backgroundColor: "rgba(220, 53, 69, 0.1)",
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
        
        // 加载状态码分布
        function loadStatusCodeDistribution() {
            $.ajax({
                url: "/api/v1/mcp/logs/status-distribution",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        
                        // 创建状态码分布图表
                        const ctx = document.getElementById("statusCodeChart").getContext("2d");
                        
                        // 销毁现有图表
                        if (window.statusCodeChartInstance) {
                            window.statusCodeChartInstance.destroy();
                        }
                        
                        // 创建新图表
                        window.statusCodeChartInstance = new Chart(ctx, {
                            type: "doughnut",
                            data: {
                                labels: data.status_codes,
                                datasets: [{
                                    data: data.counts,
                                    backgroundColor: getStatusCodeColors(data.status_codes),
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: "right"
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label || "";
                                                const value = context.raw || 0;
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const percentage = Math.round((value / total) * 100);
                                                return label + ": " + value + " (" + percentage + "%)";
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                }
            });
        }
        
        // 获取状态码颜色
        function getStatusCodeColors(statusCodes) {
            const colors = [];
            
            statusCodes.forEach(function(code) {
                if (code >= 200 && code < 300) {
                    colors.push("#28a745"); // 绿色
                } else if (code >= 300 && code < 400) {
                    colors.push("#17a2b8"); // 青色
                } else if (code >= 400 && code < 500) {
                    colors.push("#ffc107"); // 黄色
                } else if (code >= 500) {
                    colors.push("#dc3545"); // 红色
                } else {
                    colors.push("#6c757d"); // 灰色
                }
            });
            
            return colors;
        }
        
        // 加载接口调用排行
        function loadInterfaceRanking() {
            $.ajax({
                url: "/api/v1/mcp/logs/interface-ranking",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        let html = "";
                        
                        data.forEach(function(item) {
                            html += "<tr>";
                            html += "<td>" + item.interface_name + "</td>";
                            html += "<td>" + item.calls.toLocaleString() + "</td>";
                            html += "<td>" + item.percentage.toFixed(2) + "%</td>";
                            html += "</tr>";
                        });
                        
                        $("#interfaceRankingBody").html(html);
                    }
                }
            });
        }
        
        // 加载响应时间分布
        function loadResponseTimeDistribution() {
            $.ajax({
                url: "/api/v1/mcp/logs/response-time-distribution",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        
                        // 创建响应时间分布图表
                        const ctx = document.getElementById("responseTimeChart").getContext("2d");
                        
                        // 销毁现有图表
                        if (window.responseTimeChartInstance) {
                            window.responseTimeChartInstance.destroy();
                        }
                        
                        // 创建新图表
                        window.responseTimeChartInstance = new Chart(ctx, {
                            type: "bar",
                            data: {
                                labels: data.ranges,
                                datasets: [{
                                    label: "响应时间分布",
                                    data: data.counts,
                                    backgroundColor: "rgba(0, 123, 255, 0.7)",
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                },
                                scales: {
                                    x: {
                                        display: true,
                                        title: {
                                            display: true,
                                            text: "响应时间 (ms)"
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
        
        // 加载错误分析
        function loadErrorAnalysis() {
            $.ajax({
                url: "/api/v1/mcp/logs/error-analysis",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        let html = "";
                        
                        data.forEach(function(item) {
                            html += "<tr>";
                            html += "<td><span class=\"badge bg-danger\">" + item.status_code + "</span></td>";
                            html += "<td>" + item.interface_name + "</td>";
                            html += "<td>" + item.count.toLocaleString() + "</td>";
                            html += "<td>" + moment(item.latest_occurrence).format("YYYY-MM-DD HH:mm:ss") + "</td>";
                            html += "<td><button type=\"button\" class=\"btn btn-sm btn-info\" onclick=\"viewError(" + item.log_id + ")\">查看</button></td>";
                            html += "</tr>";
                        });
                        
                        $("#errorAnalysisBody").html(html);
                    }
                }
            });
        }
        
        // 查看错误详情
        function viewError(logId) {
            $.ajax({
                url: "/api/v1/mcp/logs/" + logId,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        
                        // 填充模态框
                        $("#error_interface").text(data.interface_name);
                        $("#error_status_code").text(data.status_code);
                        $("#error_request_data").text(JSON.stringify(data.request_data, null, 2));
                        $("#error_response_data").text(JSON.stringify(data.response_data, null, 2));
                        $("#error_ip_address").text(data.ip_address);
                        $("#error_user_agent").text(data.user_agent || "");
                        $("#error_created_at").text(moment(data.created_at).format("YYYY-MM-DD HH:mm:ss"));
                        
                        // 显示模态框
                        $("#viewErrorModal").modal("show");
                    } else {
                        alert("获取错误详情失败：" + response.message);
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
