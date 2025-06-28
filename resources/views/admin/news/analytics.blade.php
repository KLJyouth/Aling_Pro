@extends('admin.news.layout')

@section('title', '新闻统计分析')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/chart.js/Chart.min.css') }}">
<style>
    .stats-card {
        transition: all 0.3s;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- 筛选和时间范围选择 -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" method="GET" class="row g-3 align-items-center">
                <div class="col-md-3">
                    <label for="period" class="form-label">时间范围</label>
                    <select class="form-select" id="period" name="period">
                        <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>今天</option>
                        <option value="yesterday" {{ request('period') == 'yesterday' ? 'selected' : '' }}>昨天</option>
                        <option value="week" {{ request('period', 'week') == 'week' ? 'selected' : '' }}>最近7天</option>
                        <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>最近30天</option>
                        <option value="quarter" {{ request('period') == 'quarter' ? 'selected' : '' }}>最近90天</option>
                        <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>最近一年</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="category_id" class="form-label">分类</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">所有分类</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">应用筛选</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="row">
        <div class="col-md-3">
            <div class="card stats-card bg-primary text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">总新闻数</h5>
                    <h2 class="display-4">{{ $stats['total_news'] }}</h2>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span>已发布: {{ $stats['published_news'] }}</span>
                    <div class="small text-white">
                        <i class="fas fa-newspaper"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card bg-success text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">总浏览量</h5>
                    <h2 class="display-4">{{ $stats['total_views'] }}</h2>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span>本期间: {{ $stats['period_views'] }}</span>
                    <div class="small text-white">
                        <i class="fas fa-eye"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card bg-info text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">新发布文章</h5>
                    <h2 class="display-4">{{ $stats['new_published'] }}</h2>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span>本期间新发布</span>
                    <div class="small text-white">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card bg-warning text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">平均阅读量</h5>
                    <h2 class="display-4">{{ $stats['published_news'] > 0 ? round($stats['total_views'] / $stats['published_news']) : 0 }}</h2>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span>每篇文章</span>
                    <div class="small text-white">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 访问趋势图 -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-area me-1"></i>
                    访问趋势
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="visitsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 设备类型分布 -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-mobile-alt me-1"></i>
                    设备类型分布
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 250px;">
                        <canvas id="deviceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 热门文章 -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-fire me-1"></i>
                    热门文章
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>标题</th>
                                    <th>分类</th>
                                    <th>发布时间</th>
                                    <th>浏览量</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['popular_news'] as $news)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.news.analytics.detail', $news->id) }}">{{ $news->title }}</a>
                                    </td>
                                    <td>{{ $news->category->name ?? '无分类' }}</td>
                                    <td>{{ $news->published_at->format('Y-m-d') }}</td>
                                    <td>{{ $news->view_count }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 分类统计 -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-folder me-1"></i>
                    分类统计
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 标签统计 -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-tags me-1"></i>
                    热门标签
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="tagChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 来源统计 -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-link me-1"></i>
                    流量来源
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>来源</th>
                                    <th>访问次数</th>
                                    <th>占比</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalReferrers = array_sum(array_column($stats['referrer_stats'], 'count')); @endphp
                                @foreach($stats['referrer_stats'] as $referrer)
                                <tr>
                                    <td>{{ $referrer['referrer'] ?: '直接访问' }}</td>
                                    <td>{{ $referrer['count'] }}</td>
                                    <td>{{ $totalReferrers > 0 ? round(($referrer['count'] / $totalReferrers) * 100, 1) . '%' : '0%' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/plugins/chart.js/Chart.min.js') }}"></script>
<script>
    $(document).ready(function() {
        // 访问趋势图
        var visitsCtx = document.getElementById('visitsChart').getContext('2d');
        var visitsChart = new Chart(visitsCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($stats['daily_trends'], 'date')) !!},
                datasets: [{
                    label: '访问量',
                    data: {!! json_encode(array_column($stats['daily_trends'], 'views')) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        
        // 设备类型分布图
        var deviceCtx = document.getElementById('deviceChart').getContext('2d');
        var deviceChart = new Chart(deviceCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_column($stats['device_stats'], 'device_type')) !!},
                datasets: [{
                    data: {!! json_encode(array_column($stats['device_stats'], 'count')) !!},
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(255, 206, 86, 0.8)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 206, 86, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // 分类统计图
        var categoryCtx = document.getElementById('categoryChart').getContext('2d');
        var categoryChart = new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_column($stats['category_stats'], 'name')) !!},
                datasets: [{
                    label: '文章数',
                    data: {!! json_encode(array_column($stats['category_stats'], 'news_count')) !!},
                    backgroundColor: 'rgba(75, 192, 192, 0.8)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        
        // 标签统计图
        var tagCtx = document.getElementById('tagChart').getContext('2d');
        var tagChart = new Chart(tagCtx, {
            type: 'horizontalBar',
            data: {
                labels: {!! json_encode(array_column($stats['tag_stats'], 'name')) !!},
                datasets: [{
                    label: '文章数',
                    data: {!! json_encode(array_column($stats['tag_stats'], 'news_count')) !!},
                    backgroundColor: 'rgba(153, 102, 255, 0.8)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        
        // 时间范围选择变更时自动提交表单
        $('#period, #category_id').change(function() {
            $('#filterForm').submit();
        });
    });
</script>
@endsection