@extends("layouts.app")

@section("title", "额度使用统计")

@section("content")
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">额度使用统计</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="btn-group">
                                <a href="{{ route("user.billing.stats", ["period" => "day"]) }}" class="btn btn-outline-primary {{ $period === "day" ? "active" : "" }}">今日</a>
                                <a href="{{ route("user.billing.stats", ["period" => "week"]) }}" class="btn btn-outline-primary {{ $period === "week" ? "active" : "" }}">本周</a>
                                <a href="{{ route("user.billing.stats", ["period" => "month"]) }}" class="btn btn-outline-primary {{ $period === "month" ? "active" : "" }}">本月</a>
                                <a href="{{ route("user.billing.stats", ["period" => "year"]) }}" class="btn btn-outline-primary {{ $period === "year" ? "active" : "" }}">今年</a>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="text-muted">API调用额度消耗</h6>
                                    <h3>{{ number_format($stats["api_usage"]) }}</h3>
                                    <div class="progress mt-3" style="height: 5px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $stats["api_usage_percent"] }}%" aria-valuenow="{{ $stats["api_usage_percent"] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <small class="text-muted">占总额度的 {{ number_format($stats["api_usage_percent"], 1) }}%</small>
                                        <small class="text-muted">总额度: {{ number_format($stats["api_quota"]) }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="text-muted">AI使用额度消耗</h6>
                                    <h3>{{ number_format($stats["ai_usage"]) }}</h3>
                                    <div class="progress mt-3" style="height: 5px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $stats["ai_usage_percent"] }}%" aria-valuenow="{{ $stats["ai_usage_percent"] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <small class="text-muted">占总额度的 {{ number_format($stats["ai_usage_percent"], 1) }}%</small>
                                        <small class="text-muted">总额度: {{ number_format($stats["ai_quota"]) }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="text-muted">存储空间使用</h6>
                                    <h3>{{ number_format($stats["storage_usage"] / 1024, 2) }} GB</h3>
                                    <div class="progress mt-3" style="height: 5px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $stats["storage_usage_percent"] }}%" aria-valuenow="{{ $stats["storage_usage_percent"] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <small class="text-muted">占总空间的 {{ number_format($stats["storage_usage_percent"], 1) }}%</small>
                                        <small class="text-muted">总空间: {{ number_format($stats["storage_quota"] / 1024, 2) }} GB</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h6 class="mb-0">API调用额度使用趋势</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="apiUsageChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h6 class="mb-0">AI使用额度使用趋势</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="aiUsageChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h6 class="mb-0">API调用类型分布</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="apiTypeChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h6 class="mb-0">AI模型使用分布</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="aiModelChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section("scripts")
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    $(function() {
        // API调用额度使用趋势图
        var apiUsageCtx = document.getElementById("apiUsageChart").getContext("2d");
        var apiUsageChart = new Chart(apiUsageCtx, {
            type: "line",
            data: {
                labels: {!! json_encode($stats["api_usage_labels"]) !!},
                datasets: [{
                    label: "API调用额度使用",
                    data: {!! json_encode($stats["api_usage_data"]) !!},
                    backgroundColor: "rgba(54, 162, 235, 0.2)",
                    borderColor: "rgba(54, 162, 235, 1)",
                    borderWidth: 2,
                    tension: 0.3,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // AI使用额度使用趋势图
        var aiUsageCtx = document.getElementById("aiUsageChart").getContext("2d");
        var aiUsageChart = new Chart(aiUsageCtx, {
            type: "line",
            data: {
                labels: {!! json_encode($stats["ai_usage_labels"]) !!},
                datasets: [{
                    label: "AI使用额度使用",
                    data: {!! json_encode($stats["ai_usage_data"]) !!},
                    backgroundColor: "rgba(75, 192, 192, 0.2)",
                    borderColor: "rgba(75, 192, 192, 1)",
                    borderWidth: 2,
                    tension: 0.3,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // API调用类型分布图
        var apiTypeCtx = document.getElementById("apiTypeChart").getContext("2d");
        var apiTypeChart = new Chart(apiTypeCtx, {
            type: "doughnut",
            data: {
                labels: {!! json_encode($stats["api_type_labels"]) !!},
                datasets: [{
                    data: {!! json_encode($stats["api_type_data"]) !!},
                    backgroundColor: [
                        "rgba(255, 99, 132, 0.7)",
                        "rgba(54, 162, 235, 0.7)",
                        "rgba(255, 206, 86, 0.7)",
                        "rgba(75, 192, 192, 0.7)",
                        "rgba(153, 102, 255, 0.7)"
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "bottom"
                    }
                }
            }
        });

        // AI模型使用分布图
        var aiModelCtx = document.getElementById("aiModelChart").getContext("2d");
        var aiModelChart = new Chart(aiModelCtx, {
            type: "doughnut",
            data: {
                labels: {!! json_encode($stats["ai_model_labels"]) !!},
                datasets: [{
                    data: {!! json_encode($stats["ai_model_data"]) !!},
                    backgroundColor: [
                        "rgba(255, 159, 64, 0.7)",
                        "rgba(255, 99, 132, 0.7)",
                        "rgba(54, 162, 235, 0.7)",
                        "rgba(75, 192, 192, 0.7)",
                        "rgba(153, 102, 255, 0.7)"
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "bottom"
                    }
                }
            }
        });
    });
</script>
@endsection
