<?php
/**
 * 安全仪表盘视图
 * @version 1.0.0
 * @author AlingAi Team
 */

// 防止直接访问
if (!defined('SECURITY_SYSTEM')) {
    header('HTTP/1.0 403 Forbidden');
    exit('禁止直接访问');
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlingAi Pro - 安全防护系统</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #3498db;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .dashboard-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin: 20px;
            padding: 30px;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .metric-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 5px solid var(--primary-color);
            margin-bottom: 20px;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .metric-card.critical {
            border-left-color: var(--danger-color);
        }

        .metric-card.high {
            border-left-color: var(--warning-color);
        }

        .metric-card.medium {
            border-left-color: var(--info-color);
        }

        .metric-card.low {
            border-left-color: var(--success-color);
        }

        .metric-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .metric-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .status-critical { background-color: var(--danger-color); }
        .status-high { background-color: var(--warning-color); }
        .status-medium { background-color: var(--info-color); }
        .status-low { background-color: var(--success-color); }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .event-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .event-item {
            padding: 15px;
            border-left: 4px solid var(--info-color);
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .event-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .event-item.critical {
            border-left-color: var(--danger-color);
            background: #fff5f5;
        }

        .event-item.high {
            border-left-color: var(--warning-color);
            background: #fffbf0;
        }

        .event-item.medium {
            border-left-color: var(--info-color);
            background: #f0f8ff;
        }

        .event-item.low {
            border-left-color: var(--success-color);
            background: #f0fff4;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="dashboard-container">
            <div class="header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1><i class="bi bi-shield-lock"></i> AlingAi Pro 安全防护系统</h1>
                        <p class="mb-0">全面的安全防护与攻击检测反击方案</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <button class="btn btn-light" id="refreshDashboard"><i class="bi bi-arrow-clockwise"></i> 刷新数据</button>
                        <a href="../index.php" class="btn btn-outline-light ms-2"><i class="bi bi-house"></i> 返回管理后台</a>
                    </div>
                </div>
            </div>

            <!-- 安全评分和威胁级别 -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="metric-card <?php echo $data['threat_level']; ?>">
                        <div class="metric-label">安全评分</div>
                        <div class="metric-value"><?php echo $data['security_score']; ?>/100</div>
                        <div class="progress progress-custom mt-2">
                            <div class="progress-bar 
                                <?php 
                                    if ($data['security_score'] >= 80) echo 'bg-success';
                                    else if ($data['security_score'] >= 60) echo 'bg-info';
                                    else if ($data['security_score'] >= 40) echo 'bg-warning';
                                    else echo 'bg-danger';
                                ?>" 
                                role="progressbar" 
                                style="width: <?php echo $data['security_score']; ?>%" 
                                aria-valuenow="<?php echo $data['security_score']; ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="metric-card <?php echo $data['threat_level']; ?>">
                        <div class="metric-label">当前威胁级别</div>
                        <div class="metric-value">
                            <span class="status-indicator status-<?php echo $data['threat_level']; ?>"></span>
                            <?php 
                                switch ($data['threat_level']) {
                                    case 'critical':
                                        echo '严重';
                                        break;
                                    case 'high':
                                        echo '高';
                                        break;
                                    case 'medium':
                                        echo '中';
                                        break;
                                    default:
                                        echo '低';
                                }
                            ?>
                        </div>
                        <p class="mt-2">
                            <?php 
                                switch ($data['threat_level']) {
                                    case 'critical':
                                        echo '检测到严重威胁，需要立即处理！';
                                        break;
                                    case 'high':
                                        echo '检测到高风险威胁，请尽快处理。';
                                        break;
                                    case 'medium':
                                        echo '存在中等风险威胁，建议关注。';
                                        break;
                                    default:
                                        echo '当前系统安全状态良好。';
                                }
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- 活跃威胁 -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="chart-container">
                        <h4 class="mb-4"><i class="bi bi-exclamation-triangle"></i> 活跃威胁</h4>
                        <?php if (empty($data['active_threats'])): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> 当前未检测到活跃威胁
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>威胁类型</th>
                                            <th>严重程度</th>
                                            <th>数量</th>
                                            <th>最近发生时间</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['active_threats'] as $threat): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($threat['event_type']); ?></td>
                                                <td>
                                                    <span class="badge 
                                                        <?php 
                                                            switch ($threat['severity']) {
                                                                case 'critical':
                                                                    echo 'bg-danger';
                                                                    break;
                                                                case 'high':
                                                                    echo 'bg-warning text-dark';
                                                                    break;
                                                                case 'medium':
                                                                    echo 'bg-info text-dark';
                                                                    break;
                                                                default:
                                                                    echo 'bg-success';
                                                            }
                                                        ?>">
                                                        <?php echo htmlspecialchars($threat['severity']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($threat['count']); ?></td>
                                                <td><?php echo htmlspecialchars($threat['latest']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 最近安全事件 -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="chart-container">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4><i class="bi bi-clock-history"></i> 最近安全事件</h4>
                            <a href="?action=security_events" class="btn btn-sm btn-primary">查看全部</a>
                        </div>
                        <?php if (empty($data['recent_events'])): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> 暂无安全事件记录
                            </div>
                        <?php else: ?>
                            <div class="event-list">
                                <?php foreach ($data['recent_events'] as $event): ?>
                                    <div class="event-item <?php echo htmlspecialchars($event['severity']); ?>">
                                        <div class="d-flex justify-content-between">
                                            <strong><?php echo htmlspecialchars($event['event_type']); ?></strong>
                                            <span class="text-muted"><?php echo htmlspecialchars($event['created_at']); ?></span>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars($event['description']); ?></p>
                                        <small class="text-muted">
                                            IP: <?php echo htmlspecialchars($event['ip_address']); ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 快速操作 -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="chart-container">
                        <h4 class="mb-4"><i class="bi bi-lightning"></i> 快速操作</h4>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <a href="?action=scan" class="btn btn-primary w-100 py-3">
                                    <i class="bi bi-search"></i> 执行安全扫描
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="?action=blacklist" class="btn btn-warning w-100 py-3">
                                    <i class="bi bi-shield-x"></i> 管理IP黑名单
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="?action=config" class="btn btn-info w-100 py-3">
                                    <i class="bi bi-gear"></i> 安全配置
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('refreshDashboard').addEventListener('click', function() {
            location.reload();
        });
    </script>
</body>
</html>
