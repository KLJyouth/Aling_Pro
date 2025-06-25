<?php
/**
 * AlingAi Pro 5.0 - 性能基线管理�?
 * 提供性能基线建立、管理和对比功能
 */

session_start(];

// 基本安全检�?
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php'];
    exit;
}

// 包含性能基线服务
require_once __DIR__ . '/../../src/Services/PerformanceBaselineService.php';

use AlingAi\Services\PerformanceBaselineService;

$action = $_GET['action'] ?? 'dashboard';
$baselineService = new PerformanceBaselineService(];

// 处理AJAX请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json'];
    
    try {
        switch ($_POST['ajax_action']) {
            case 'establish_baseline':
                $result = $baselineService->establishBaseline(];
                echo json_encode(['success' => true, 'data' => $result]];
                break;
                
            case 'get_baseline_history':
                $history = $baselineService->getBaselineHistory(];
                echo json_encode(['success' => true, 'data' => $history]];
                break;
                
            case 'compare_baselines':
                $baseline1 = $_POST['baseline1'] ?? '';
                $baseline2 = $_POST['baseline2'] ?? '';
                $comparison = $baselineService->compareBaselines($baseline1, $baseline2];
                echo json_encode(['success' => true, 'data' => $comparison]];
                break;
                
            case 'get_current_metrics':
                $metrics = $baselineService->getCurrentMetrics(];
                echo json_encode(['success' => true, 'data' => $metrics]];
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => '未知操作']];
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]];
    }
    exit;
}

// 获取基线历史
$baselineHistory = $baselineService->getBaselineHistory(];
$latestBaseline = $baselineService->getLatestBaseline(];

// 辅助函数：获取评分样式类
function getScoreClass($score) {
    if ($score >= 90) return 'excellent';
    if ($score >= 80) return 'good';
    if ($score >= 70) return 'warning';
    return 'danger';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>性能基线管理�?- AlingAi Pro 5.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .metric-card {
            transition: transform 0.2s;
        }
        .metric-card:hover {
            transform: translateY(-2px];
            box-shadow: 0 4px 8px rgba(0,0,0,0.1];
        }
        .performance-score {
            font-size: 2rem;
            font-weight: bold;
        }
        .score-excellent { color: #28a745; }
        .score-good { color: #17a2b8; }
        .score-warning { color: #ffc107; }
        .score-danger { color: #dc3545; }
        .loading-spinner {
            display: none;
        }
        .comparison-chart {
            height: 300px;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-speedometer2"></i> AlingAi Pro 5.0 Admin
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="tools_manager.php">
                    <i class="bi bi-arrow-left"></i> 返回工具管理�?
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- 侧边�?-->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-speedometer"></i> 基线管理</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary" onclick="establishBaseline()">
                                <i class="bi bi-play-fill"></i> 建立新基�?
                            </button>
                            <button type="button" class="btn btn-info" onclick="showCurrentMetrics()">
                                <i class="bi bi-graph-up"></i> 当前性能指标
                            </button>
                            <button type="button" class="btn btn-warning" onclick="showComparison()">
                                <i class="bi bi-bar-chart"></i> 基线对比
                            </button>
                            <button type="button" class="btn btn-success" onclick="showHistory()">
                                <i class="bi bi-clock-history"></i> 历史记录
                            </button>
                        </div>
                    </div>
                </div>

                <?php if ($latestBaseline): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h6><i class="bi bi-bookmark"></i> 最新基�?/h6>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            <small class="text-muted">建立时间:</small><br>
                            <?= htmlspecialchars($latestBaseline['timestamp']) ?>
                        </p>                        <div class="performance-score score-<?= getScoreClass($latestBaseline['baseline_score'] ?? 0) ?>">
                            <?= number_format($latestBaseline['baseline_score'] ?? 0, 1) ?>�?
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- 主内容区 -->
            <div class="col-md-9">
                <!-- 加载指示�?-->
                <div class="loading-spinner text-center mb-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">正在处理�?..</p>
                </div>

                <!-- 仪表�?-->
                <div id="dashboard-section">
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5><i class="bi bi-speedometer2"></i> 性能基线仪表�?/h5>
                                    <span class="badge bg-info">AlingAi Pro 5.0</span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="card metric-card h-100">
                                                <div class="card-body text-center">
                                                    <i class="bi bi-hdd text-primary" style="font-size: 2rem;"></i>
                                                    <h6 class="mt-2">系统性能</h6>
                                                    <div class="performance-score score-excellent" id="system-score">
                                                        <?= $latestBaseline ? number_format($latestBaseline['baseline_score'],  1) : '---' ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card metric-card h-100">
                                                <div class="card-body text-center">
                                                    <i class="bi bi-lightning text-warning" style="font-size: 2rem;"></i>
                                                    <h6 class="mt-2">API响应</h6>
                                                    <div class="performance-score score-good" id="api-score">
                                                        <?= $latestBaseline ? number_format($latestBaseline['api_performance']['average_response_time'] ?? 0, 0) . 'ms' : '---' ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card metric-card h-100">
                                                <div class="card-body text-center">
                                                    <i class="bi bi-memory text-info" style="font-size: 2rem;"></i>
                                                    <h6 class="mt-2">内存使用</h6>
                                                    <div class="performance-score score-warning" id="memory-score">
                                                        <?= $latestBaseline ? number_format(($latestBaseline['system_metrics']['memory_usage'] ?? 0) * 100, 1) . '%' : '---' ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card metric-card h-100">
                                                <div class="card-body text-center">
                                                    <i class="bi bi-database text-success" style="font-size: 2rem;"></i>
                                                    <h6 class="mt-2">数据�?/h6>
                                                    <div class="performance-score score-excellent" id="db-score">
                                                        <?= $latestBaseline ? number_format($latestBaseline['database_performance']['query_time'] ?? 0, 0) . 'ms' : '---' ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (count($baselineHistory) > 0): ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="bi bi-graph-up-arrow"></i> 性能趋势图表</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="performanceChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- 动态内容区�?-->
                <div id="dynamic-content"></div>
            </div>
        </div>
    </div>

    <!-- 脚本�?-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // 全局变量
        let currentChart = null;
        
        // 显示加载指示�?
        function showLoading() {
            document.querySelector('.loading-spinner').style.display = 'block';
        }
        
        // 隐藏加载指示�?
        function hideLoading() {
            document.querySelector('.loading-spinner').style.display = 'none';
        }
        
        // 建立新基�?
        async function establishBaseline() {
            if (!confirm('建立新基线将需要几分钟时间，是否继续？')) {
                return;
            }
            
            showLoading(];
            
            try {
                const response = await fetch('baseline_manager.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'ajax_action=establish_baseline'
                }];
                
                const result = await response.json(];
                
                if (result.success) {
                    alert('性能基线建立成功�?];
                    location.reload(];
                } else {
                    alert('建立基线失败: ' + result.error];
                }
            } catch (error) {
                alert('请求失败: ' + error.message];
            } finally {
                hideLoading(];
            }
        }
        
        // 显示当前性能指标
        async function showCurrentMetrics() {
            showLoading(];
            
            try {
                const response = await fetch('baseline_manager.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'ajax_action=get_current_metrics'
                }];
                
                const result = await response.json(];
                
                if (result.success) {
                    displayCurrentMetrics(result.data];
                } else {
                    alert('获取指标失败: ' + result.error];
                }
            } catch (error) {
                alert('请求失败: ' + error.message];
            } finally {
                hideLoading(];
            }
        }
        
        // 显示当前指标
        function displayCurrentMetrics(metrics) {
            const content = `
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-graph-up"></i> 当前性能指标</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>系统指标</h6>
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>CPU使用�?/span>
                                        <span class="badge bg-info">${(metrics.system_metrics.cpu_usage * 100).toFixed(1)}%</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>内存使用�?/span>
                                        <span class="badge bg-warning">${(metrics.system_metrics.memory_usage * 100).toFixed(1)}%</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>磁盘使用�?/span>
                                        <span class="badge bg-success">${(metrics.system_metrics.disk_usage * 100).toFixed(1)}%</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>性能指标</h6>
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>API响应时间</span>
                                        <span class="badge bg-primary">${metrics.api_performance.average_response_time}ms</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>数据库查询时�?/span>
                                        <span class="badge bg-secondary">${metrics.database_performance.query_time}ms</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>缓存命中�?/span>
                                        <span class="badge bg-info">${(metrics.cache_performance.hit_rate * 100).toFixed(1)}%</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('dynamic-content').innerHTML = content;
            document.getElementById('dashboard-section').style.display = 'none';
        }
        
        // 显示基线对比
        function showComparison() {
            // 这里可以添加基线对比的实�?
            alert('基线对比功能正在开发中...'];
        }
        
        // 显示历史记录
        async function showHistory() {
            showLoading(];
            
            try {
                const response = await fetch('baseline_manager.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'ajax_action=get_baseline_history'
                }];
                
                const result = await response.json(];
                
                if (result.success) {
                    displayHistory(result.data];
                } else {
                    alert('获取历史记录失败: ' + result.error];
                }
            } catch (error) {
                alert('请求失败: ' + error.message];
            } finally {
                hideLoading(];
            }
        }
        
        // 显示历史记录
        function displayHistory(history) {
            let content = `
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-clock-history"></i> 基线历史记录</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>时间</th>
                                        <th>版本</th>
                                        <th>总体评分</th>
                                        <th>API性能</th>
                                        <th>数据库性能</th>
                                        <th>内存使用</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;
            
            history.forEach(baseline => {
                content += `
                    <tr>
                        <td>${baseline.timestamp}</td>
                        <td>${baseline.version}</td>
                        <td><span class="badge bg-${getScoreBadgeClass(baseline.baseline_score)}">${baseline.baseline_score.toFixed(1)}</span></td>
                        <td>${baseline.api_performance?.average_response_time || 'N/A'}ms</td>
                        <td>${baseline.database_performance?.query_time || 'N/A'}ms</td>
                        <td>${((baseline.system_metrics?.memory_usage || 0) * 100).toFixed(1)}%</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewBaseline('${baseline.timestamp}')">
                                <i class="bi bi-eye"></i> 查看
                            </button>
                        </td>
                    </tr>
                `;
            }];
            
            content += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('dynamic-content').innerHTML = content;
            document.getElementById('dashboard-section').style.display = 'none';
        }
        
        // 获取评分对应的样式类
        function getScoreBadgeClass(score) {
            if (score >= 90) return 'success';
            if (score >= 80) return 'info';
            if (score >= 70) return 'warning';
            return 'danger';
        }
        
        // 查看具体基线详情
        function viewBaseline(timestamp) {
            alert(`查看基线详情功能正在开发中...\n时间: ${timestamp}`];
        }
        
        // 返回仪表�?
        function showDashboard() {
            document.getElementById('dashboard-section').style.display = 'block';
            document.getElementById('dynamic-content').innerHTML = '';
        }
        
        // 页面加载完成后的初始�?
        document.addEventListener('DOMContentLoaded', function() {
            // 如果有基线历史数据，绘制图表
            <?php if (count($baselineHistory) > 0): ?>
            drawPerformanceChart(];
            <?php endif; ?>
        }];
        
        // 绘制性能趋势图表
        function drawPerformanceChart() {
            const ctx = document.getElementById('performanceChart').getContext('2d'];
            
            const chartData = {
                labels: <?= json_encode(array_column($baselineHistory, 'timestamp')) ?>,
                datasets: [{
                    label: '总体性能评分',
                    data: <?= json_encode(array_column($baselineHistory, 'baseline_score')) ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            };
            
            currentChart = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            }];
        }
    </script>
</body>
</html>
