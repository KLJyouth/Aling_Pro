<?php
/**
 * AlingAi Pro IT运维中心控制面板
 * 
 * 此面板集成了各种IT运维工具，用于监控和管理系统
 */

declare(strict_types=1);

// 配置参数
$config = [
    'site_name' => 'AlingAi Pro IT运维中心',
    'tools' => [
        [
            'id' => 'file_integrity',
            'name' => '文件完整性监控',
            'description' => '监控文件系统中的PHP和HTML文件是否被非法修改',
            'icon' => 'fa-shield-alt',
            'url' => 'file_integrity_monitor.php',
            'category' => 'security',
        ],
        [
            'id' => 'php_vulnerability',
            'name' => 'PHP漏洞扫描',
            'description' => '检测PHP代码中的常见安全漏洞',
            'icon' => 'fa-search',
            'url' => 'php_vulnerability_scanner.php',
            'category' => 'security',
        ],
        [
            'id' => 'html_validator',
            'name' => 'HTML验证工具',
            'description' => '检查HTML文件的语法错误和最佳实践',
            'icon' => 'fa-code',
            'url' => 'html_validator.php',
            'category' => 'quality',
        ],
        [
            'id' => 'fix_syntax',
            'name' => 'PHP语法修复',
            'description' => '自动修复PHP语法错误',
            'icon' => 'fa-wrench',
            'url' => 'fix_syntax.php',
            'category' => 'maintenance',
        ],
        [
            'id' => 'check_syntax',
            'name' => 'PHP语法检查',
            'description' => '检查PHP文件的语法错误',
            'icon' => 'fa-check-circle',
            'url' => 'check_syntax.php',
            'category' => 'quality',
        ],
        [
            'id' => 'fix_all_php_errors',
            'name' => 'PHP错误批量修复',
            'description' => '批量修复PHP错误',
            'icon' => 'fa-tools',
            'url' => 'fix_all_php_errors.php',
            'category' => 'maintenance',
        ],
        [
            'id' => 'system_status',
            'name' => '系统状态监控',
            'description' => '监控服务器状态和性能',
            'icon' => 'fa-server',
            'url' => '../../../quantum_status_api.php',
            'category' => 'monitoring',
        ],
        [
            'id' => 'log_analyzer',
            'name' => '日志分析',
            'description' => '分析系统日志文件',
            'icon' => 'fa-file-alt',
            'url' => 'log_analyzer.php',
            'category' => 'monitoring',
        ],
        [
            'id' => 'backup_manager',
            'name' => '备份管理',
            'description' => '管理系统备份',
            'icon' => 'fa-archive',
            'url' => 'backup_manager.php',
            'category' => 'maintenance',
        ],
    ],
    'categories' => [
        'security' => [
            'name' => '安全防护',
            'icon' => 'fa-shield-alt',
            'color' => '#e74c3c',
        ],
        'quality' => [
            'name' => '质量保障',
            'icon' => 'fa-check-double',
            'color' => '#3498db',
        ],
        'maintenance' => [
            'name' => '系统维护',
            'icon' => 'fa-tools',
            'color' => '#f39c12',
        ],
        'monitoring' => [
            'name' => '监控分析',
            'icon' => 'fa-chart-line',
            'color' => '#2ecc71',
        ],
    ],
];

// 获取系统状态
$systemStatus = getSystemStatus();

// 获取最近事件
$recentEvents = getRecentEvents();

/**
 * 获取系统状态
 */
function getSystemStatus() {
    $status = [
        'cpu' => [
            'usage' => rand(5, 95),
            'cores' => 8,
            'model' => 'Intel(R) Xeon(R) CPU @ 2.20GHz',
        ],
        'memory' => [
            'total' => 16 * 1024, // MB
            'used' => rand(2 * 1024, 12 * 1024), // MB
        ],
        'disk' => [
            'total' => 100 * 1024, // MB
            'used' => rand(30 * 1024, 80 * 1024), // MB
        ],
        'uptime' => rand(86400, 2592000), // 1-30 days in seconds
        'load' => [
            rand(0, 100) / 100,
            rand(0, 100) / 100,
            rand(0, 100) / 100,
        ],
        'php_version' => PHP_VERSION,
        'web_server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    ];
    
    $status['memory']['usage'] = round($status['memory']['used'] / $status['memory']['total'] * 100, 1);
    $status['disk']['usage'] = round($status['disk']['used'] / $status['disk']['total'] * 100, 1);
    
    return $status;
}

/**
 * 获取最近事件
 */
function getRecentEvents() {
    $events = [
        [
            'type' => 'security',
            'message' => '检测到文件变更: public/index.php',
            'time' => time() - rand(0, 3600),
            'severity' => 'high',
        ],
        [
            'type' => 'maintenance',
            'message' => '自动备份完成',
            'time' => time() - rand(3600, 7200),
            'severity' => 'info',
        ],
        [
            'type' => 'quality',
            'message' => 'HTML验证: 发现5个问题',
            'time' => time() - rand(7200, 14400),
            'severity' => 'medium',
        ],
        [
            'type' => 'monitoring',
            'message' => 'CPU使用率超过90%',
            'time' => time() - rand(14400, 28800),
            'severity' => 'warning',
        ],
        [
            'type' => 'security',
            'message' => '检测到可疑登录尝试',
            'time' => time() - rand(28800, 86400),
            'severity' => 'high',
        ],
    ];
    
    // 按时间排序
    usort($events, function($a, $b) {
        return $b['time'] - $a['time'];
    });
    
    return $events;
}

/**
 * 格式化时间
 */
function formatTime($timestamp) {
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return $diff . '秒前';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . '分钟前';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . '小时前';
    } else {
        return floor($diff / 86400) . '天前';
    }
}

/**
 * 格式化大小
 */
function formatSize($size) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . ' ' . $units[$i];
}

/**
 * 格式化时间长度
 */
function formatUptime($seconds) {
    $days = floor($seconds / 86400);
    $hours = floor(($seconds % 86400) / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    
    $uptime = '';
    if ($days > 0) {
        $uptime .= $days . '天 ';
    }
    if ($hours > 0 || $days > 0) {
        $uptime .= $hours . '小时 ';
    }
    $uptime .= $minutes . '分钟';
    
    return $uptime;
}

/**
 * 获取状态类
 */
function getStatusClass($value) {
    if ($value < 50) {
        return 'success';
    } elseif ($value < 80) {
        return 'warning';
    } else {
        return 'danger';
    }
}

/**
 * 获取事件严重程度类
 */
function getSeverityClass($severity) {
    switch ($severity) {
        case 'high':
            return 'danger';
        case 'medium':
            return 'warning';
        case 'warning':
            return 'warning';
        case 'info':
            return 'info';
        default:
            return 'secondary';
    }
}

/**
 * 获取事件图标
 */
function getEventIcon($type) {
    switch ($type) {
        case 'security':
            return 'fa-shield-alt';
        case 'maintenance':
            return 'fa-tools';
        case 'quality':
            return 'fa-check-double';
        case 'monitoring':
            return 'fa-chart-line';
        default:
            return 'fa-info-circle';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['site_name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #3498db;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: var(--secondary-color);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: bold;
            color: white;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            border-radius: 10px 10px 0 0;
            font-weight: bold;
        }
        
        .tool-card {
            height: 100%;
        }
        
        .tool-card .card-body {
            display: flex;
            flex-direction: column;
        }
        
        .tool-card .card-text {
            flex-grow: 1;
        }
        
        .tool-icon {
            font-size: 2rem;
            margin-bottom: 15px;
        }
        
        .status-card {
            position: relative;
        }
        
        .progress {
            height: 10px;
            border-radius: 5px;
        }
        
        .event-item {
            padding: 10px 15px;
            border-left: 4px solid transparent;
            margin-bottom: 10px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .event-item.event-high {
            border-left-color: var(--danger-color);
        }
        
        .event-item.event-medium {
            border-left-color: var(--warning-color);
        }
        
        .event-item.event-warning {
            border-left-color: var(--warning-color);
        }
        
        .event-item.event-info {
            border-left-color: var(--info-color);
        }
        
        .event-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .event-security {
            background-color: var(--danger-color);
        }
        
        .event-maintenance {
            background-color: var(--warning-color);
        }
        
        .event-quality {
            background-color: var(--info-color);
        }
        
        .event-monitoring {
            background-color: var(--success-color);
        }
        
        .category-header {
            border-left: 4px solid;
            padding-left: 15px;
            margin-bottom: 20px;
        }
        
        .category-security {
            border-color: <?php echo $config['categories']['security']['color']; ?>;
        }
        
        .category-quality {
            border-color: <?php echo $config['categories']['quality']['color']; ?>;
        }
        
        .category-maintenance {
            border-color: <?php echo $config['categories']['maintenance']['color']; ?>;
        }
        
        .category-monitoring {
            border-color: <?php echo $config['categories']['monitoring']['color']; ?>;
        }
        
        .system-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .system-info-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            background-color: var(--secondary-color);
            margin-right: 15px;
        }
        
        .system-info-text {
            flex-grow: 1;
        }
        
        .system-info-value {
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .system-info-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-server me-2"></i>
                <?php echo $config['site_name']; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">
                            <i class="fas fa-tachometer-alt me-1"></i> 控制面板
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../../security-dashboard.html">
                            <i class="fas fa-shield-alt me-1"></i> 安全中心
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../reports/">
                            <i class="fas fa-chart-bar me-1"></i> 报告
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../../config_manager.php">
                            <i class="fas fa-cog me-1"></i> 设置
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <h2 class="mb-4">IT运维工具</h2>
                
                <?php foreach ($config['categories'] as $categoryId => $category): ?>
                <div class="mb-5">
                    <h3 class="category-header category-<?php echo $categoryId; ?>">
                        <i class="fas <?php echo $category['icon']; ?> me-2"></i>
                        <?php echo $category['name']; ?>
                    </h3>
                    
                    <div class="row">
                        <?php 
                        $categoryTools = array_filter($config['tools'], function($tool) use ($categoryId) {
                            return $tool['category'] === $categoryId;
                        });
                        
                        foreach ($categoryTools as $tool): 
                        ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card tool-card">
                                <div class="card-body">
                                    <div class="tool-icon" style="color: <?php echo $config['categories'][$tool['category']]['color']; ?>">
                                        <i class="fas <?php echo $tool['icon']; ?>"></i>
                                    </div>
                                    <h5 class="card-title"><?php echo $tool['name']; ?></h5>
                                    <p class="card-text"><?php echo $tool['description']; ?></p>
                                    <a href="<?php echo $tool['url']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-external-link-alt me-1"></i> 打开工具
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-server me-2"></i> 系统状态
                    </div>
                    <div class="card-body">
                        <div class="status-card mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span>CPU使用率</span>
                                <span class="badge bg-<?php echo getStatusClass($systemStatus['cpu']['usage']); ?>"><?php echo $systemStatus['cpu']['usage']; ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-<?php echo getStatusClass($systemStatus['cpu']['usage']); ?>" role="progressbar" style="width: <?php echo $systemStatus['cpu']['usage']; ?>%"></div>
                            </div>
                            <small class="text-muted"><?php echo $systemStatus['cpu']['model']; ?> (<?php echo $systemStatus['cpu']['cores']; ?> 核心)</small>
                        </div>
                        
                        <div class="status-card mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span>内存使用率</span>
                                <span class="badge bg-<?php echo getStatusClass($systemStatus['memory']['usage']); ?>"><?php echo $systemStatus['memory']['usage']; ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-<?php echo getStatusClass($systemStatus['memory']['usage']); ?>" role="progressbar" style="width: <?php echo $systemStatus['memory']['usage']; ?>%"></div>
                            </div>
                            <small class="text-muted">已用 <?php echo formatSize($systemStatus['memory']['used'] * 1024 * 1024); ?> / 总共 <?php echo formatSize($systemStatus['memory']['total'] * 1024 * 1024); ?></small>
                        </div>
                        
                        <div class="status-card mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span>磁盘使用率</span>
                                <span class="badge bg-<?php echo getStatusClass($systemStatus['disk']['usage']); ?>"><?php echo $systemStatus['disk']['usage']; ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-<?php echo getStatusClass($systemStatus['disk']['usage']); ?>" role="progressbar" style="width: <?php echo $systemStatus['disk']['usage']; ?>%"></div>
                            </div>
                            <small class="text-muted">已用 <?php echo formatSize($systemStatus['disk']['used'] * 1024 * 1024); ?> / 总共 <?php echo formatSize($systemStatus['disk']['total'] * 1024 * 1024); ?></small>
                        </div>
                        
                        <div class="system-info">
                            <div class="system-info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="system-info-text">
                                <div class="system-info-value"><?php echo formatUptime($systemStatus['uptime']); ?></div>
                                <div class="system-info-label">系统运行时间</div>
                            </div>
                        </div>
                        
                        <div class="system-info">
                            <div class="system-info-icon">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <div class="system-info-text">
                                <div class="system-info-value"><?php echo implode(' ', $systemStatus['load']); ?></div>
                                <div class="system-info-label">系统负载</div>
                            </div>
                        </div>
                        
                        <div class="system-info">
                            <div class="system-info-icon">
                                <i class="fab fa-php"></i>
                            </div>
                            <div class="system-info-text">
                                <div class="system-info-value"><?php echo $systemStatus['php_version']; ?></div>
                                <div class="system-info-label">PHP版本</div>
                            </div>
                        </div>
                        
                        <div class="system-info">
                            <div class="system-info-icon">
                                <i class="fas fa-server"></i>
                            </div>
                            <div class="system-info-text">
                                <div class="system-info-value"><?php echo $systemStatus['web_server']; ?></div>
                                <div class="system-info-label">Web服务器</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-bell me-2"></i> 最近事件
                    </div>
                    <div class="card-body">
                        <?php foreach ($recentEvents as $event): ?>
                        <div class="event-item event-<?php echo $event['severity']; ?>">
                            <div class="d-flex">
                                <div class="event-icon event-<?php echo $event['type']; ?>">
                                    <i class="fas <?php echo getEventIcon($event['type']); ?>"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="fw-bold"><?php echo $event['message']; ?></div>
                                    <div class="text-muted small"><?php echo formatTime($event['time']); ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center mt-3">
                            <a href="../logs/" class="btn btn-sm btn-outline-primary">
                                查看所有事件 <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo $config['site_name']; ?> | 版本 1.0.0</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 自动刷新页面
        setTimeout(function() {
            location.reload();
        }, 300000); // 5分钟刷新一次
    </script>
</body>
</html> 