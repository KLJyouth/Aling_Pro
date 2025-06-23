<?php
/**
 * AlingAI Pro 5.1 系统管理后台
 * 整合所有测试、检查、调试和系统管理功能
 * @version 2.1.0
 * @author AlingAi Team
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

// 安全检查 - 仅在开发环境或授权用户访问
session_start();

// 简单的安全验证
$isAuthorized = false;
if (isset($_POST['admin_password'])) {
    $adminPassword = $_ENV['ADMIN_PASSWORD'] ?? 'admin123';
    if ($_POST['admin_password'] === $adminPassword) {
        $_SESSION['admin_authorized'] = true;
        $isAuthorized = true;
    }
} elseif (isset($_SESSION['admin_authorized'])) {
    $isAuthorized = true;
}

if (!$isAuthorized) {
    include __DIR__ . '/login.php';
    exit;
}

// 处理AJAX请求
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    require_once __DIR__ . '/SystemManager.php';
    
    $systemManager = new \AlingAi\Admin\SystemManager();
    
    switch ($_GET['action']) {
        case 'system_status':
            echo json_encode($systemManager->getSystemStatus());
            break;
        case 'database_check':
            echo json_encode($systemManager->checkDatabase());
            break;
        case 'run_tests':
            echo json_encode($systemManager->runTests($_GET['test_type'] ?? 'all'));
            break;
        case 'system_health':
            echo json_encode($systemManager->systemHealthCheck());
            break;        case 'debug_info':
            echo json_encode($systemManager->getDebugInfo());
            break;
        case 'intelligent_monitoring':
            echo json_encode($systemManager->getIntelligentMonitoring());
            break;
        case 'ai_services_status':
            $monitoring = $systemManager->getIntelligentMonitoring();
            echo json_encode($monitoring['ai_services'] ?? []);
            break;
        case 'security_monitoring':
            $monitoring = $systemManager->getIntelligentMonitoring();
            echo json_encode($monitoring['security_monitoring'] ?? []);
            break;
        case 'performance_metrics':
            $monitoring = $systemManager->getIntelligentMonitoring();
            echo json_encode($monitoring['performance_metrics'] ?? []);
            break;
        case 'threat_intelligence':
            $monitoring = $systemManager->getIntelligentMonitoring();
            echo json_encode($monitoring['threat_intelligence'] ?? []);
            break;        case 'business_metrics':
            $monitoring = $systemManager->getIntelligentMonitoring();
            echo json_encode($monitoring['business_monitoring'] ?? []);
            break;
        case 'websocket_status':
            echo json_encode($systemManager->getWebSocketStatus());
            break;
        case 'chat_monitoring':
            echo json_encode($systemManager->getChatSystemMonitoring());
            break;
        case 'analytics_report':
            $period = $_GET['period'] ?? '24h';
            echo json_encode($systemManager->generateAnalyticsReport($period));
            break;
        case 'realtime_stream':
            echo json_encode($systemManager->getRealTimeDataStream());
            break;
        case 'cache_management':
            echo json_encode($systemManager->getCacheManagement());
            break;
        case 'database_performance':
            echo json_encode($systemManager->getDatabasePerformanceAnalysis());
            break;
        case 'api_analytics':
            echo json_encode($systemManager->getAPIUsageAnalytics());
            break;
        case 'fix_database':
            echo json_encode($systemManager->fixDatabase());
            break;
        case 'optimize_system':
            echo json_encode($systemManager->optimizeSystem());
            break;
        case 'export_logs':
            echo json_encode($systemManager->exportLogs());
            break;
        case 'advanced_security_check':
            echo json_encode($systemManager->advancedSecurityCheck());
            break;
        case 'run_security_scan':
            $scanType = $_GET['scan_type'] ?? 'quick';
            echo json_encode($systemManager->runSecurityScan($scanType));
            break;
        case 'security_report':
            $reportType = $_GET['report_type'] ?? 'summary';
            echo json_encode($systemManager->getSecurityReport($reportType));
            break;
        case 'handle_security_event':
            $eventId = $_GET['event_id'] ?? '';
            $action = $_GET['action'] ?? 'ignore';
            echo json_encode($systemManager->handleSecurityEvent($eventId, $action));
            break;
        default:
            echo json_encode(['error' => 'Unknown action']);
    }
    exit;
}

// 获取系统版本信息
$systemVersion = '5.1.0';
$apiVersion = '2.1.0';
$buildDate = '2025-06-16';

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlingAI Pro <?php echo $systemVersion; ?> 系统管理后台</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
    
    <!-- 新增 Chart.js 支持 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <style>
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .status-healthy { background-color: #10b981; }
        .status-warning { background-color: #f59e0b; }
        .status-error { background-color: #ef4444; }
        .log-container {
            background: #1f2937;
            color: #f9fafb;
            font-family: 'Courier New', monospace;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- 侧边栏 -->
        <div class="w-64 sidebar text-white">
            <div class="p-6">
                <h1 class="text-xl font-bold mb-8">
                    <i class="fas fa-cogs mr-2"></i>
                    系统管理后台
                    <span class="text-xs opacity-75 block mt-1">v<?php echo $systemVersion; ?></span>
                </h1>
                
                <nav class="space-y-2">
                    <a href="#dashboard" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="dashboard">
                        <i class="fas fa-tachometer-alt mr-3"></i>
                        系统概览
                    </a>
                    <a href="#database" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="database">
                        <i class="fas fa-database mr-3"></i>
                        数据库管理
                    </a>
                    <a href="#testing" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="testing">
                        <i class="fas fa-flask mr-3"></i>
                        系统测试
                    </a>
                    <a href="#health" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="health">
                        <i class="fas fa-heartbeat mr-3"></i>
                        健康检查
                    </a>
                    <a href="#debug" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="debug">
                        <i class="fas fa-bug mr-3"></i>
                        调试工具
                    </a>
                    <a href="#optimization" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="optimization">
                        <i class="fas fa-rocket mr-3"></i>
                        系统优化
                    </a>                    <a href="#logs" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="logs">
                        <i class="fas fa-file-alt mr-3"></i>
                        日志管理
                    </a>
                    <a href="#intelligent" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="intelligent">
                        <i class="fas fa-brain mr-3"></i>
                        智能监控
                    </a>
                    <a href="#ai-services" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="ai-services">
                        <i class="fas fa-robot mr-3"></i>
                        AI服务
                    </a>
                    <a href="#security" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="security">
                        <i class="fas fa-shield-alt mr-3"></i>
                        安全监控
                    </a>
                    <a href="#performance" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="performance">
                        <i class="fas fa-chart-line mr-3"></i>
                        性能监控
                    </a>
                    <a href="#threats" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="threats">
                        <i class="fas fa-exclamation-triangle mr-3"></i>
                        威胁情报
                    </a>                    <a href="#business" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="business">
                        <i class="fas fa-chart-bar mr-3"></i>
                        业务指标
                    </a>
                    <a href="#websocket" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="websocket">
                        <i class="fas fa-wifi mr-3"></i>
                        WebSocket监控
                    </a>
                    <a href="#chat" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="chat">
                        <i class="fas fa-comments mr-3"></i>
                        聊天监控
                    </a>
                    <a href="#analytics" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="analytics">
                        <i class="fas fa-chart-pie mr-3"></i>
                        分析报告
                    </a>
                    <a href="#cache" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="cache">
                        <i class="fas fa-memory mr-3"></i>
                        缓存管理
                    </a>
                    <a href="#database-performance" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="database-performance">
                        <i class="fas fa-database mr-3"></i>
                        数据库性能
                    </a>
                    <a href="#api-analytics" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="api-analytics">
                        <i class="fas fa-plug mr-3"></i>
                        API分析
                    </a>
                    <a href="#advanced-security" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="advanced-security">
                        <i class="fas fa-shield-alt mr-3"></i>
                        高级安全
                    </a>
                    <a href="#realtime-visualization" class="nav-link flex items-center p-3 rounded hover:bg-white hover:bg-opacity-20 transition" data-tab="realtime-visualization">
                        <i class="fas fa-chart-area mr-3"></i>
                        实时可视化
                    </a>
                </nav>
            </div>
            
            <div class="absolute bottom-4 left-4 right-4">
                <div class="text-sm text-white text-opacity-70">
                    <i class="fas fa-info-circle mr-1"></i>
                    AlingAI Pro v5.0
                </div>
            </div>
        </div>

        <!-- 主内容区 -->
        <div class="flex-1 overflow-auto">
            <!-- 顶部栏 -->
            <header class="bg-white shadow-sm border-b p-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-800" id="page-title">系统概览</h2>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <span class="status-indicator" id="system-status-indicator"></span>
                            <span id="system-status-text">检查中...</span>
                        </div>
                        <button onclick="refreshData()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">
                            <i class="fas fa-sync-alt mr-2"></i>刷新
                        </button>
                        <a href="?logout=1" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">
                            <i class="fas fa-sign-out-alt mr-2"></i>退出
                        </a>
                    </div>
                </div>
            </header>

            <!-- 内容区域 -->
            <main class="p-6">
                <!-- 系统概览 -->
                <div id="dashboard" class="tab-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div class="card rounded-lg p-6 shadow-lg">
                            <div class="flex items-center">
                                <div class="bg-blue-500 text-white p-3 rounded-full mr-4">
                                    <i class="fas fa-server"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm text-gray-600">系统状态</h3>
                                    <p class="text-2xl font-bold" id="system-status">检查中...</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card rounded-lg p-6 shadow-lg">
                            <div class="flex items-center">
                                <div class="bg-green-500 text-white p-3 rounded-full mr-4">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm text-gray-600">数据库状态</h3>
                                    <p class="text-2xl font-bold" id="database-status">检查中...</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card rounded-lg p-6 shadow-lg">
                            <div class="flex items-center">
                                <div class="bg-yellow-500 text-white p-3 rounded-full mr-4">
                                    <i class="fas fa-memory"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm text-gray-600">内存使用</h3>
                                    <p class="text-2xl font-bold" id="memory-usage">检查中...</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card rounded-lg p-6 shadow-lg">
                            <div class="flex items-center">
                                <div class="bg-purple-500 text-white p-3 rounded-full mr-4">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm text-gray-600">运行时间</h3>
                                    <p class="text-2xl font-bold" id="uptime">检查中...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 快速操作 -->
                    <div class="card rounded-lg p-6 shadow-lg mb-6">
                        <h3 class="text-lg font-bold mb-4">快速操作</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <button onclick="runQuickTest()" class="bg-blue-500 text-white p-4 rounded hover:bg-blue-600 transition">
                                <i class="fas fa-play-circle mb-2"></i><br>
                                快速测试
                            </button>
                            <button onclick="fixDatabase()" class="bg-green-500 text-white p-4 rounded hover:bg-green-600 transition">
                                <i class="fas fa-wrench mb-2"></i><br>
                                修复数据库
                            </button>
                            <button onclick="optimizeSystem()" class="bg-yellow-500 text-white p-4 rounded hover:bg-yellow-600 transition">
                                <i class="fas fa-rocket mb-2"></i><br>
                                系统优化
                            </button>
                            <button onclick="exportLogs()" class="bg-purple-500 text-white p-4 rounded hover:bg-purple-600 transition">
                                <i class="fas fa-download mb-2"></i><br>
                                导出日志
                            </button>
                        </div>
                    </div>
                    
                    <!-- 系统信息 -->
                    <div class="card rounded-lg p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">系统信息</h3>
                        <div id="system-info" class="space-y-2">
                            <div class="flex justify-between">
                                <span>PHP版本:</span>
                                <span id="php-version">检查中...</span>
                            </div>
                            <div class="flex justify-between">
                                <span>服务器时间:</span>
                                <span id="server-time">检查中...</span>
                            </div>
                            <div class="flex justify-between">
                                <span>系统版本:</span>
                                <span id="system-version">AlingAI Pro 5.0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 数据库管理 -->
                <div id="database" class="tab-content hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="card rounded-lg p-6 shadow-lg">
                            <h3 class="text-lg font-bold mb-4">数据库连接</h3>
                            <div id="database-connection-info">
                                <div class="space-y-2" id="db-info">
                                    <!-- 数据库信息将在这里显示 -->
                                </div>
                            </div>
                            <div class="mt-4 space-x-2">
                                <button onclick="checkDatabase()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                    检查连接
                                </button>
                                <button onclick="fixDatabase()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                    修复数据库
                                </button>
                            </div>
                        </div>
                        
                        <div class="card rounded-lg p-6 shadow-lg">
                            <h3 class="text-lg font-bold mb-4">表结构检查</h3>
                            <div id="tables-info">
                                <!-- 表信息将在这里显示 -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 系统测试 -->
                <div id="testing" class="tab-content hidden">
                    <div class="card rounded-lg p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">系统测试</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <button onclick="runTests('core')" class="bg-blue-500 text-white p-4 rounded hover:bg-blue-600 transition">
                                <i class="fas fa-cog mb-2"></i><br>
                                核心功能测试
                            </button>
                            <button onclick="runTests('api')" class="bg-green-500 text-white p-4 rounded hover:bg-green-600 transition">
                                <i class="fas fa-plug mb-2"></i><br>
                                API测试
                            </button>
                            <button onclick="runTests('integration')" class="bg-purple-500 text-white p-4 rounded hover:bg-purple-600 transition">
                                <i class="fas fa-link mb-2"></i><br>
                                集成测试
                            </button>
                        </div>
                        
                        <div class="log-container p-4 rounded-lg">
                            <div id="test-results">
                                <div class="text-green-400">测试系统就绪...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 健康检查 -->
                <div id="health" class="tab-content hidden">
                    <div class="card rounded-lg p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">系统健康检查</h3>
                        
                        <button onclick="runHealthCheck()" class="bg-green-500 text-white px-6 py-3 rounded hover:bg-green-600 transition mb-4">
                            <i class="fas fa-heartbeat mr-2"></i>
                            运行健康检查
                        </button>
                        
                        <div id="health-results">
                            <!-- 健康检查结果将在这里显示 -->
                        </div>
                    </div>
                </div>

                <!-- 调试工具 -->
                <div id="debug" class="tab-content hidden">
                    <div class="card rounded-lg p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">调试信息</h3>
                        
                        <button onclick="getDebugInfo()" class="bg-yellow-500 text-white px-6 py-3 rounded hover:bg-yellow-600 transition mb-4">
                            <i class="fas fa-bug mr-2"></i>
                            获取调试信息
                        </button>
                        
                        <div class="log-container p-4 rounded-lg">
                            <div id="debug-info">
                                <div class="text-yellow-400">调试工具就绪...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 系统优化 -->
                <div id="optimization" class="tab-content hidden">
                    <div class="card rounded-lg p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">系统优化</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <button onclick="clearCache()" class="bg-blue-500 text-white p-4 rounded hover:bg-blue-600 transition">
                                <i class="fas fa-broom mb-2"></i><br>
                                清理缓存
                            </button>
                            <button onclick="optimizeDatabase()" class="bg-green-500 text-white p-4 rounded hover:bg-green-600 transition">
                                <i class="fas fa-database mb-2"></i><br>
                                优化数据库
                            </button>
                        </div>
                        
                        <div id="optimization-results">
                            <!-- 优化结果将在这里显示 -->
                        </div>
                    </div>
                </div>                <!-- 日志管理 -->
                <div id="logs" class="tab-content hidden">
                    <div class="card rounded-lg p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">日志管理</h3>
                        
                        <div class="mb-4 space-x-2">
                            <button onclick="viewLogs('error')" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                错误日志
                            </button>
                            <button onclick="viewLogs('access')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                访问日志
                            </button>
                            <button onclick="viewLogs('system')" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                系统日志
                            </button>
                            <button onclick="exportLogs()" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                                导出日志
                            </button>
                        </div>
                        
                        <div class="log-container p-4 rounded-lg" style="height: 400px;">
                            <div id="log-content">
                                <div class="text-gray-400">选择要查看的日志类型...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 智能监控 -->
                <div id="intelligent" class="tab-content hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <div class="card rounded-lg p-6 shadow-lg">
                            <h3 class="text-lg font-bold mb-4">
                                <i class="fas fa-brain mr-2 text-purple-500"></i>
                                系统健康评分
                            </h3>
                            <div class="text-center">
                                <div class="text-4xl font-bold text-green-500 mb-2" id="health-score">--</div>
                                <div class="text-gray-600">综合健康度</div>
                                <div class="mt-4">
                                    <div class="bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full transition-all duration-500" style="width: 0%" id="health-bar"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card rounded-lg p-6 shadow-lg">
                            <h3 class="text-lg font-bold mb-4">
                                <i class="fas fa-exclamation-triangle mr-2 text-yellow-500"></i>
                                活跃警报
                            </h3>
                            <div id="active-alerts">
                                <div class="text-gray-400">加载中...</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card rounded-lg p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">
                            <i class="fas fa-cogs mr-2 text-blue-500"></i>
                            系统组件状态
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="component-status">
                            <div class="text-gray-400">加载中...</div>
                        </div>
                    </div>
                </div>

                <!-- AI服务监控 -->
                <div id="ai-services" class="tab-content hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <div class="card rounded-lg p-6 shadow-lg">
                            <h3 class="text-lg font-bold mb-4">
                                <i class="fas fa-robot mr-2 text-blue-500"></i>
                                AI服务概览
                            </h3>
                            <div id="ai-overview">
                                <div class="text-gray-400">加载中...</div>
                            </div>
                        </div>
                        
                        <div class="card rounded-lg p-6 shadow-lg">
                            <h3 class="text-lg font-bold mb-4">
                                <i class="fas fa-chart-line mr-2 text-green-500"></i>
                                性能指标
                            </h3>
                            <div id="ai-performance">
                                <div class="text-gray-400">加载中...</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card rounded-lg p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">
                            <i class="fas fa-server mr-2 text-purple-500"></i>
                            AI服务详情
                        </h3>
                        <div id="ai-services-detail">
                            <div class="text-gray-400">加载中...</div>
                        </div>
                    </div>
                </div>

                <!-- 安全监控 -->
                <div id="security" class="tab-content hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                        <div class="card rounded-lg p-6 shadow-lg">
                            <h3 class="text-lg font-bold mb-4">
                                <i class="fas fa-shield-alt mr-2 text-red-500"></i>
                                威胁等级
                            </h3>
                            <div class="text-center">
                                <div class="text-3xl font-bold mb-2" id="threat-level">--</div>
                                <div class="text-gray-600">当前威胁等级</div>
                            </div>
                        </div>
                        
                        <div class="card rounded-lg p-6 shadow-lg">
                            <h3 class="text-lg font-bold mb-4">
                                <i class="fas fa-star mr-2 text-yellow-500"></i>
                                安全评分
                            </h3>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-500 mb-2" id="security-score">--</div>
                                <div class="text-gray-600">综合安全评分</div>
                            </div>
                        </div>
                        
                        <div class="card rounded-lg p-6 shadow-lg">
                            <h3 class="text-lg font-bold mb-4">
                                <i class="fas fa-bug mr-2 text-orange-500"></i>
                                活跃威胁
                            </h3>
                            <div id="active-threats">
                                <div class="text-gray-400">加载中...</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card rounded-lg p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">
                            <i class="fas fa-lock mr-2 text-blue-500"></i>
                            零信任架构状态
                        </h3>
                        <div id="zero-trust-status">
                            <div class="text-gray-400">加载中...</div>
                        </div>
                    </div>
                </div>

                <!-- 性能监控 -->
                <div id="performance" class="tab-content hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <div class="card rounded-lg p-6 shadow-lg">
                            <h3 class="text-lg font-bold mb-4">
                                <i class="fas fa-tachometer-alt mr-2 text-blue-500"></i>
                                响应时间
                            </h3>
                            <div id="response-times">
                                <div class="text-gray-400">加载中...</div>
                            </div>
                        </div>
                        
                        <div class="card rounded-lg p-6 shadow-lg">
                            <h3 class="text-lg font-bold mb-4">
                                <i class="fas fa-server mr-2 text-green-500"></i>
                                资源利用率
                            </h3>
                            <div id="resource-utilization">
                                <div class="text-gray-400">加载中...</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card rounded-lg p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">
                            <i class="fas fa-exclamation-circle mr-2 text-yellow-500"></i>
                            性能瓶颈与建议
                        </h3>
                        <div id="performance-suggestions">
                            <div class="text-gray-400">加载中...</div>
                        </div>
                    </div>
                </div>

                <!-- 威胁情报 -->
                <div id="threats" class="tab-content hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <div class="card rounded-lg p-6 shadow-lg">
                            <h3 class="text-lg font-bold mb-4">
                                <i class="fas fa-globe mr-2 text-red-500"></i>
                                全球威胁动态
                            </h3>
                            <div id="global-threats">
                                <div class="text-gray-400">加载中...</div>
                            </div>
                        </div>
                        
                        <div class="card rounded-lg p-6 shadow-lg">
                            <h3 class="text-lg font-bold mb-4">
                                <i class="fas fa-home mr-2 text-orange-500"></i>
                                本地威胁检测
                            </h3>
                            <div id="local-threats">
                                <div class="text-gray-400">加载中...</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card rounded-lg p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">
                            <i class="fas fa-brain mr-2 text-purple-500"></i>
                            预测性分析与缓解策略
                        </h3>
                        <div id="threat-analysis">
                            <div class="text-gray-400">加载中...</div>
                        </div>
                    </div>
                </div>

                <!-- 业务指标 -->
                <div id="business" class="tab-content hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
                        <div class="card rounded-lg p-6 shadow-lg text-center">
                            <div class="text-2xl font-bold text-blue-500" id="active-users">--</div>
                            <div class="text-gray-600">在线用户</div>
                        </div>
                        <div class="card rounded-lg p-6 shadow-lg text-center">
                            <div class="text-2xl font-bold text-green-500" id="api-calls">--</div>
                            <div class="text-gray-600">API调用数</div>
                        </div>
                        <div class="card rounded-lg p-6 shadow-lg text-center">
                            <div class="text-2xl font-bold text-purple-500" id="conversations">--</div>
                            <div class="text-gray-600">对话总数</div>
                        </div>
                        <div class="card rounded-lg p-6 shadow-lg text-center">
                            <div class="text-2xl font-bold text-yellow-500" id="satisfaction">--</div>
                            <div class="text-gray-600">满意度评分</div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="card rounded-lg p-6 shadow-lg">
                            <h3 class="text-lg font-bold mb-4">
                                <i class="fas fa-chart-bar mr-2 text-blue-500"></i>
                                用户活动统计
                            </h3>
                            <div id="user-activity">
                                <div class="text-gray-400">加载中...</div>
                            </div>
                        </div>
                        
                        <div class="card rounded-lg p-6 shadow-lg">
                            <h3 class="text-lg font-bold mb-4">
                                <i class="fas fa-exclamation-triangle mr-2 text-red-500"></i>
                                错误率统计
                            </h3>                            <div id="error-rates">
                                <div class="text-gray-400">加载中...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WebSocket监控 -->
                <div id="websocket" class="tab-content hidden">
                    <div class="card rounded-lg p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">
                            <i class="fas fa-wifi mr-2 text-blue-500"></i>
                            WebSocket连接监控
                        </h3>
                        <div id="websocket-status-container">
                            <div class="text-gray-400">加载中...</div>
                        </div>
                    </div>
                </div>

                <!-- 聊天监控 -->
                <div id="chat" class="tab-content hidden">
                    <div class="card rounded-lg p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">
                            <i class="fas fa-comments mr-2 text-green-500"></i>
                            聊天系统监控
                        </h3>
                        <div id="chat-monitoring-container">
                            <div class="text-gray-400">加载中...</div>
                        </div>
                    </div>
                </div>

                <!-- 分析报告 -->
                <div id="analytics" class="tab-content hidden">
                    <div class="card rounded-lg p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">
                            <i class="fas fa-chart-pie mr-2 text-purple-500"></i>
                            系统分析报告
                        </h3>
                        <div class="mb-4 space-x-2">
                            <button onclick="generateAnalyticsReport('today')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                今日报告
                            </button>
                            <button onclick="generateAnalyticsReport('week')" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                本周报告
                            </button>
                            <button onclick="generateAnalyticsReport('month')" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                                本月报告
                            </button>
                            <button onclick="generateAnalyticsReport('quarter')" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                                季度报告
                            </button>
                        </div>
                        <div id="analytics-report-container">
                            <div class="text-gray-400">请选择要生成的报告类型...</div>
                        </div>
                    </div>
                </div>

                <!-- 缓存管理 -->
                <div id="cache" class="tab-content hidden">
                    <div class="card rounded-lg p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">
                            <i class="fas fa-memory mr-2 text-yellow-500"></i>
                            缓存系统管理
                        </h3>
                        <div class="mb-4 space-x-2">
                            <button onclick="loadCacheManagement()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                刷新缓存状态
                            </button>
                            <button onclick="clearCache()" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                清理缓存
                            </button>
                            <button onclick="optimizeCache()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                优化缓存
                            </button>
                        </div>
                        <div id="cache-management-container">
                            <div class="text-gray-400">加载中...</div>
                        </div>
                    </div>
                </div>

                <!-- 数据库性能 -->
                <div id="database-performance" class="tab-content hidden">
                    <div class="card rounded-lg p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">
                            <i class="fas fa-database mr-2 text-red-500"></i>
                            数据库性能分析
                        </h3>
                        <div class="mb-4 space-x-2">
                            <button onclick="loadDatabasePerformance()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                刷新分析
                            </button>
                            <button onclick="optimizeDatabase()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                优化数据库
                            </button>
                            <button onclick="analyzeSlowQueries()" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                                慢查询分析
                            </button>
                        </div>
                        <div id="database-performance-container">
                            <div class="text-gray-400">加载中...</div>
                        </div>
                    </div>
                </div>

                <!-- API分析 -->
                <div id="api-analytics" class="tab-content hidden">
                    <div class="card rounded-lg p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">
                            <i class="fas fa-plug mr-2 text-indigo-500"></i>
                            API使用分析
                        </h3>
                        <div class="mb-4 space-x-2">
                            <button onclick="loadAPIAnalytics()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                刷新分析
                            </button>
                            <button onclick="exportAPIReport()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                导出报告
                            </button>
                            <button onclick="optimizeAPIPerformance()" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                                性能优化
                            </button>
                        </div>
                        <div id="api-analytics-container">
                            <div class="text-gray-400">加载中...</div>
                        </div>
                    </div>
                </div>

                <!-- 实时数据流监控 -->
                <div id="realtime" class="tab-content hidden">
                    <div class="card rounded-lg p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">
                            <i class="fas fa-stream mr-2 text-blue-500"></i>
                            实时数据流监控
                        </h3>
                        <div id="realtime-chart-container">
                            <div class="text-gray-400">初始化实时数据流...</div>
                        </div>
                    </div>
                </div>

                <!-- 新增高级安全标签页 -->
                <div id="advanced-security" class="tab-content hidden p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold" id="page-title">高级安全检查</h2>
                        <button class="btn bg-purple-600 text-white px-4 py-2 rounded" onclick="loadAdvancedSecurityCheck()">
                            <i class="fas fa-sync-alt mr-2"></i>运行检查
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="card p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold mb-2">安全状态</h3>
                            <div class="flex items-center">
                                <div id="security-overall-status" class="text-xl font-bold text-green-500">检查中...</div>
                            </div>
                        </div>
                        
                        <div class="card p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold mb-2">漏洞统计</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">发现漏洞</p>
                                    <p id="vulnerabilities-count" class="text-xl font-bold">0</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">严重问题</p>
                                    <p id="critical-issues-count" class="text-xl font-bold text-red-500">0</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold mb-2">安全分数</h3>
                            <div class="flex items-center justify-center relative">
                                <div class="w-24 h-24 relative">
                                    <canvas id="security-score-chart"></canvas>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span id="security-score-text" class="text-2xl font-bold">--</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="card p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold mb-4">安全检查结果</h3>
                            <div id="security-checks-list" class="space-y-2">
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                    <p>正在加载安全检查结果...</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold mb-4">安全建议</h3>
                            <div id="security-recommendations" class="text-gray-600">
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                    <p>正在生成安全建议...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 新增实时可视化标签页 -->
                <div id="realtime-visualization" class="tab-content hidden p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold" id="page-title">实时数据可视化</h2>
                        <div>
                            <button class="btn bg-blue-600 text-white px-4 py-2 rounded mr-2" onclick="startRealtimeVisualization()">
                                <i class="fas fa-play mr-2"></i>开始监控
                            </button>
                            <button class="btn bg-gray-600 text-white px-4 py-2 rounded" onclick="stopRealtimeVisualization()">
                                <i class="fas fa-stop mr-2"></i>停止
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="card p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold mb-4">系统性能监控</h3>
                            <div class="h-64">
                                <canvas id="system-performance-chart"></canvas>
                            </div>
                        </div>
                        
                        <div class="card p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold mb-4">API请求分析</h3>
                            <div class="h-64">
                                <canvas id="api-requests-chart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6">
                        <div class="card p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold mb-4">资源使用率</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">CPU使用率</p>
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                            <div id="cpu-usage-bar" class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
                                        </div>
                                        <span id="cpu-usage-text">0%</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">内存使用率</p>
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                            <div id="memory-usage-bar" class="bg-purple-600 h-2.5 rounded-full" style="width: 0%"></div>
                                        </div>
                                        <span id="memory-usage-text">0%</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">磁盘使用率</p>
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                            <div id="disk-usage-bar" class="bg-green-600 h-2.5 rounded-full" style="width: 0%"></div>
                                        </div>
                                        <span id="disk-usage-text">0%</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">网络使用率</p>
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                            <div id="network-usage-bar" class="bg-yellow-600 h-2.5 rounded-full" style="width: 0%"></div>
                                        </div>
                                        <span id="network-usage-text">0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- 加载指示器 -->
    <div id="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 flex items-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mr-4"></div>
            <span>处理中...</span>
        </div>
    </div>

    <script src="js/admin.js"></script>
    
    <!-- 新增模块脚本 -->
    <script src="js/modules/admin-utils.js"></script>
    <script src="js/modules/security-monitor.js"></script>
    <script src="js/modules/realtime-visualizer.js"></script>
    
    <script>
        // 初始化安全监控
        const securityMonitor = new SecurityMonitor({
            apiEndpoint: 'index.php',
            refreshInterval: 60000
        });
        
        // 初始化实时可视化
        let systemPerformanceChart, apiRequestsChart;
        
        function initializeCharts() {
            // 系统性能图表
            const sysCtx = document.getElementById('system-performance-chart').getContext('2d');
            systemPerformanceChart = new Chart(sysCtx, {
                type: 'line',
                data: {
                    labels: Array.from({length: 20}, (_, i) => i),
                    datasets: [{
                        label: 'CPU使用率',
                        data: Array(20).fill(0),
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: '内存使用率',
                        data: Array(20).fill(0),
                        borderColor: '#8B5CF6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
            
            // API请求图表
            const apiCtx = document.getElementById('api-requests-chart').getContext('2d');
            apiRequestsChart = new Chart(apiCtx, {
                type: 'bar',
                data: {
                    labels: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                    datasets: [{
                        label: '请求数',
                        data: [0, 0, 0, 0, 0],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.7)',
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(245, 158, 11, 0.7)',
                            'rgba(239, 68, 68, 0.7)',
                            'rgba(139, 92, 246, 0.7)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        // 启动实时可视化
        function startRealtimeVisualization() {
            if (!systemPerformanceChart) {
                initializeCharts();
            }
            
            // 模拟实时数据更新
            window.realtimeInterval = setInterval(() => {
                // 更新系统性能数据
                const cpuUsage = Math.floor(Math.random() * 60) + 10;
                const memoryUsage = Math.floor(Math.random() * 40) + 30;
                
                // 更新图表
                systemPerformanceChart.data.datasets[0].data.shift();
                systemPerformanceChart.data.datasets[0].data.push(cpuUsage);
                
                systemPerformanceChart.data.datasets[1].data.shift();
                systemPerformanceChart.data.datasets[1].data.push(memoryUsage);
                
                systemPerformanceChart.update();
                
                // 更新资源使用率
                document.getElementById('cpu-usage-bar').style.width = `${cpuUsage}%`;
                document.getElementById('cpu-usage-text').textContent = `${cpuUsage}%`;
                
                document.getElementById('memory-usage-bar').style.width = `${memoryUsage}%`;
                document.getElementById('memory-usage-text').textContent = `${memoryUsage}%`;
                
                const diskUsage = Math.floor(Math.random() * 30) + 40;
                document.getElementById('disk-usage-bar').style.width = `${diskUsage}%`;
                document.getElementById('disk-usage-text').textContent = `${diskUsage}%`;
                
                const networkUsage = Math.floor(Math.random() * 70) + 10;
                document.getElementById('network-usage-bar').style.width = `${networkUsage}%`;
                document.getElementById('network-usage-text').textContent = `${networkUsage}%`;
                
                // 随机更新API请求数据
                const apiMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];
                const randomMethod = Math.floor(Math.random() * apiMethods.length);
                apiRequestsChart.data.datasets[0].data[randomMethod] += Math.floor(Math.random() * 5) + 1;
                apiRequestsChart.update();
                
            }, 2000);
        }
        
        // 停止实时可视化
        function stopRealtimeVisualization() {
            if (window.realtimeInterval) {
                clearInterval(window.realtimeInterval);
                window.realtimeInterval = null;
            }
        }
        
        // 加载高级安全检查
        async function loadAdvancedSecurityCheck() {
            try {
                const data = await fetchData('advanced_security_check');
                updateSecurityCheckResults(data);
            } catch (error) {
                console.error('加载高级安全检查失败:', error);
                showNotification('安全检查加载失败', 'error');
            }
        }
        
        // 初始化安全监控
        document.addEventListener('DOMContentLoaded', function() {
            securityMonitor.initialize().catch(console.error);
            
            // 监听安全事件
            securityMonitor.on('threat-detected', (threats) => {
                showNotification(`检测到${threats.length}个新威胁!`, 'warning');
            });
        });
    </script>
</body>
</html>

<?php
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
