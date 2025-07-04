@extends("admin.layouts.app")

@section("title", "通知统计分析")

@section("content")
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="row mb-4">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">通知统计分析</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route("admin.dashboard") }}">首页</a></li>
                <li class="breadcrumb-item"><a href="{{ route("admin.notifications.index") }}">通知管理</a></li>
                <li class="breadcrumb-item active">统计分析</li>
            </ol>
        </div>
    </div>

    <!-- 通知信息 -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">通知信息</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">标题</th>
                                    <td>{{ $notification->title }}</td>
                                </tr>
                                <tr>
                                    <th>类型</th>
                                    <td>
                                        @switch($notification->type)
                                            @case("system")
                                                <span class="badge badge-info">系统通知</span>
                                                @break
                                            @case("user")
                                                <span class="badge badge-primary">用户通知</span>
                                                @break
                                            @case("email")
                                                <span class="badge badge-warning">邮件通知</span>
                                                @break
                                            @case("api")
                                                <span class="badge badge-secondary">API通知</span>
                                                @break
                                            @default
                                                <span class="badge badge-light">{{ $notification->type }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <th>发送时间</th>
                                    <td>{{ $notification->sent_at ? $notification->sent_at->format("Y-m-d H:i:s") : "-" }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">总接收者</th>
                                    <td>{{ $stats["total_recipients"] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <th>送达率</th>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $stats["delivery_rate"] ?? 0 }}%" aria-valuenow="{{ $stats["delivery_rate"] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">{{ $stats["delivery_rate"] ?? 0 }}%</div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>阅读率</th>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $stats["read_rate"] ?? 0 }}%" aria-valuenow="{{ $stats["read_rate"] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">{{ $stats["read_rate"] ?? 0 }}%</div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats["sent_count"] ?? 0 }}</h3>
                    <p>发送总数</p>
                </div>
                <div class="icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats["delivered_count"] ?? 0 }}</h3>
                    <p>送达数量</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats["read_count"] ?? 0 }}</h3>
                    <p>阅读数量</p>
                </div>
                <div class="icon">
                    <i class="fas fa-eye"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats["failed_count"] ?? 0 }}</h3>
                    <p>失败数量</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 日期筛选 -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">日期范围筛选</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route("admin.notifications.statistics", $notification->id) }}" method="GET" class="form-inline">
                        <div class="form-group mr-3">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="far fa-calendar-alt"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control float-right" id="date-range" name="date_range" value="{{ request("date_range", $defaultDateRange) }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">应用</button>
                        <a href="{{ route("admin.notifications.statistics", $notification->id) }}" class="btn btn-default ml-2">重置</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 趋势图表 -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">通知数据趋势</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="trendChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 设备分布 -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">设备分布</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="deviceChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script src="{{ asset("plugins/chart.js/Chart.min.js") }}"></script>
<script>
    $(function () {
        // 日期范围选择器
        $("#date-range").daterangepicker({
            locale: {
                format: "YYYY-MM-DD",
                applyLabel: "确定",
                cancelLabel: "取消",
                fromLabel: "从",
                toLabel: "至",
                customRangeLabel: "自定义范围",
                weekLabel: "W",
                daysOfWeek: ["日", "一", "二", "三", "四", "五", "六"],
                monthNames: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
                firstDay: 1
            }
        });

        // 趋势图表
        var trendCtx = document.getElementById("trendChart").getContext("2d");
        var trendChart = new Chart(trendCtx, {
            type: "line",
            data: {
                labels: {!! json_encode($chartData["dates"]) !!},
                datasets: [
                    {
                        label: "发送数",
                        data: {!! json_encode($chartData["sent"]) !!},
                        backgroundColor: "rgba(60, 141, 188, 0.2)",
                        borderColor: "rgba(60, 141, 188, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(60, 141, 188, 1)",
                        pointBorderColor: "#fff",
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: "#fff",
                        pointHoverBorderColor: "rgba(60, 141, 188, 1)",
                        fill: true
                    },
                    {
                        label: "送达数",
                        data: {!! json_encode($chartData["delivered"]) !!},
                        backgroundColor: "rgba(40, 167, 69, 0.2)",
                        borderColor: "rgba(40, 167, 69, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(40, 167, 69, 1)",
                        pointBorderColor: "#fff",
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: "#fff",
                        pointHoverBorderColor: "rgba(40, 167, 69, 1)",
                        fill: true
                    },
                    {
                        label: "阅读数",
                        data: {!! json_encode($chartData["read"]) !!},
                        backgroundColor: "rgba(255, 193, 7, 0.2)",
                        borderColor: "rgba(255, 193, 7, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(255, 193, 7, 1)",
                        pointBorderColor: "#fff",
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: "#fff",
                        pointHoverBorderColor: "rgba(255, 193, 7, 1)",
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                tooltips: {
                    mode: "index",
                    intersect: false,
                },
                hover: {
                    mode: "nearest",
                    intersect: true
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: "日期"
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: "数量"
                        },
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });

        // 设备分布图表
        var deviceCtx = document.getElementById("deviceChart").getContext("2d");
        var deviceChart = new Chart(deviceCtx, {
            type: "doughnut",
            data: {
                labels: {!! json_encode(array_keys($stats["device_stats"] ?? [])) !!},
                datasets: [{
                    data: {!! json_encode(array_values($stats["device_stats"] ?? [])) !!},
                    backgroundColor: [
                        "rgba(255, 99, 132, 0.8)",
                        "rgba(54, 162, 235, 0.8)",
                        "rgba(255, 206, 86, 0.8)",
                        "rgba(75, 192, 192, 0.8)",
                        "rgba(153, 102, 255, 0.8)",
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: "right",
                }
            }
        });
    });
</script>
@endsection
