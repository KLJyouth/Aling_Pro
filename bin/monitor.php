#!/usr/bin/env php
<?php
/**
 * AlingAi API监控系统命令行工具
 * 
 * 使用方法:
 *   php bin/monitor.php [命令] [选项]
 * 
 * 可用命令:
 *   start          启动监控系统
 *   stop           停止监控系统
 *   status         查看监控系统状态
 *   check          运行一次健康检查
 *   cleanup        清理过期数据
 *   add-api        添加API配置
 *   list-apis      列出所有API配置
 *   help           显示帮助信息
 */

// 设置应用根目录
define('APP_ROOT', dirname(__DIR__));

// 加载自动加载器
require APP_ROOT . '/vendor/autoload.php';

// 创建应用容器
$container = require APP_ROOT . '/bootstrap/container.php';

// 加载配置
$config = require APP_ROOT . '/config/app.php';
$container->set('config', $config);

// 注册监控服务
$monitoringProvider = new \AlingAi\Monitoring\MonitoringServiceProvider();
$monitoringProvider->register($container);

// 创建日志记录器
$logger = new \Monolog\Logger('monitoring');
$logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::INFO));
$container->set(\Psr\Log\LoggerInterface::class, $logger);

// 处理命令行参数
$command = $argv[1] ?? 'help';
$options = array_slice($argv, 2);

// 解析选项
$parsedOptions = [];
foreach ($options as $option) {
    if (strpos($option, '--') === 0) {
        $parts = explode('=', substr($option, 2), 2);
        $parsedOptions[$parts[0]] = $parts[1] ?? true;
    } elseif (strpos($option, '-') === 0) {
        $parsedOptions[substr($option, 1)] = true;
    }
}

// 执行命令
try {
    switch ($command) {
        case 'start':
            startMonitor($container, $parsedOptions);
            break;
            
        case 'stop':
            stopMonitor($container);
            break;
            
        case 'status':
            checkStatus($container);
            break;
            
        case 'check':
            runHealthChecks($container, $parsedOptions);
            break;
            
        case 'cleanup':
            cleanupData($container, $parsedOptions);
            break;
            
        case 'add-api':
            addApiConfig($container, $parsedOptions);
            break;
            
        case 'list-apis':
            listApis($container);
            break;
            
        case 'help':
        default:
            showHelp();
            break;
    }
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

/**
 * 启动监控系统
 */
function startMonitor($container, $options)
{
    $logger = $container->get(\Psr\Log\LoggerInterface::class);
    $logger->info("正在启动API监控系统...");
    
    $background = isset($options['background']) || isset($options['b']);
    
    if ($background) {
        // 在后台启动
        $pidFile = $options['pid-file'] ?? sys_get_temp_dir() . '/alingai_monitor.pid';
        
        if (isMonitorRunning($pidFile)) {
            $logger->error("监控系统已经在运行中");
            exit(1);
        }
        
        // 派生进程
        if (!function_exists('pcntl_fork')) {
            $logger->error("无法在后台启动：pcntl扩展未启用");
            exit(1);
        }
        
        $pid = pcntl_fork();
        
        if ($pid == -1) {
            $logger->error("无法派生进程");
            exit(1);
        } elseif ($pid) {
            // 父进程
            $logger->info("监控系统已在后台启动，PID: $pid");
            file_put_contents($pidFile, $pid);
            exit(0);
        } else {
            // 子进程
            posix_setsid();
            
            // 关闭标准输出/错误
            if (!isset($options['debug'])) {
                fclose(STDOUT);
                fclose(STDERR);
                fclose(STDIN);
                
                // 重定向输出到日志文件
                $logFile = $options['log-file'] ?? APP_ROOT . '/logs/monitor.log';
                $logDir = dirname($logFile);
                
                if (!is_dir($logDir)) {
                    mkdir($logDir, 0755, true);
                }
                
                $fileLogger = new \Monolog\Logger('monitoring');
                $fileLogger->pushHandler(new \Monolog\Handler\StreamHandler($logFile, \Monolog\Logger::INFO));
                $container->set(\Psr\Log\LoggerInterface::class, $fileLogger);
                
                $logger = $fileLogger;
            }
        }
    }
    
    // 启动调度器
    $scheduler = $container->get(\AlingAi\Monitoring\Scheduler\MonitoringScheduler::class);
    $logger->info("监控调度器已启动");
    $scheduler->start();
}

/**
 * 停止监控系统
 */
function stopMonitor($container)
{
    $logger = $container->get(\Psr\Log\LoggerInterface::class);
    
    $pidFile = sys_get_temp_dir() . '/alingai_monitor.pid';
    
    if (!file_exists($pidFile)) {
        $logger->error("找不到PID文件，监控系统可能未在运行");
        exit(1);
    }
    
    $pid = (int) file_get_contents($pidFile);
    
    if (!$pid) {
        $logger->error("无效的PID文件内容");
        exit(1);
    }
    
    $logger->info("正在停止监控系统 (PID: $pid)...");
    
    if (!posix_kill($pid, SIGTERM)) {
        $logger->error("无法向进程发送终止信号: " . posix_strerror(posix_get_last_error()));
        exit(1);
    }
    
    // 等待进程终止
    $timeout = 10; // 等待10秒
    $start = time();
    
    while (posix_kill($pid, 0) && time() - $start < $timeout) {
        sleep(1);
    }
    
    if (posix_kill($pid, 0)) {
        $logger->warning("进程未在超时时间内终止，尝试强制终止...");
        posix_kill($pid, SIGKILL);
    }
    
    unlink($pidFile);
    $logger->info("监控系统已停止");
}

/**
 * 检查监控系统状态
 */
function checkStatus($container)
{
    $logger = $container->get(\Psr\Log\LoggerInterface::class);
    $pidFile = sys_get_temp_dir() . '/alingai_monitor.pid';
    
    if (!file_exists($pidFile)) {
        $logger->info("监控系统未运行");
        exit(0);
    }
    
    $pid = (int) file_get_contents($pidFile);
    
    if (!$pid || !posix_kill($pid, 0)) {
        $logger->warning("监控系统已停止运行，但PID文件仍然存在");
        unlink($pidFile);
        exit(0);
    }
    
    $logger->info("监控系统正在运行 (PID: $pid)");
    
    // 显示调度任务状态
    try {
        // 这里可以通过某种方式（如共享内存、数据库等）获取调度器状态
        // 简化版本中，我们只显示进程状态
        $processInfo = `ps -p $pid -o pid,ppid,command,etime,rss,pcpu --no-headers`;
        echo "进程信息:\n$processInfo\n";
    } catch (Exception $e) {
        $logger->error("获取进程信息失败: " . $e->getMessage());
    }
}

/**
 * 运行健康检查
 */
function runHealthChecks($container, $options)
{
    $logger = $container->get(\Psr\Log\LoggerInterface::class);
    $logger->info("运行API健康检查...");
    
    $healthCheckService = $container->get(\AlingAi\Monitoring\HealthCheck\HealthCheckService::class);
    $healthCheckService->runChecks();
    
    $logger->info("健康检查完成");
}

/**
 * 清理过期数据
 */
function cleanupData($container, $options)
{
    $logger = $container->get(\Psr\Log\LoggerInterface::class);
    $maxAge = isset($options['days']) ? (int) $options['days'] * 86400 : 30 * 86400; // 默认30天
    
    $logger->info("清理{$maxAge}秒前的监控数据...");
    
    $metricsStorage = $container->get(\AlingAi\Monitoring\Storage\MetricsStorageInterface::class);
    $result = $metricsStorage->cleanupOldData($maxAge);
    
    if ($result) {
        $logger->info("数据清理完成");
    } else {
        $logger->error("数据清理失败");
        exit(1);
    }
}

/**
 * 添加API配置
 */
function addApiConfig($container, $options)
{
    $logger = $container->get(\Psr\Log\LoggerInterface::class);
    
    // 验证必要参数
    if (!isset($options['name']) || !isset($options['url'])) {
        $logger->error("缺少必要参数：name和url是必需的");
        echo "用法: php bin/monitor.php add-api --name=api名称 --url=API基础URL [--timeout=30] [--auth-type=none|basic|bearer|api_key]\n";
        exit(1);
    }
    
    $gatewayConfig = $container->get(\AlingAi\Monitoring\Config\GatewayConfig::class);
    
    $config = [
        'base_url' => $options['url'],
        'timeout' => isset($options['timeout']) ? (int) $options['timeout'] : 30,
        'default_headers' => [],
        'auth_type' => $options['auth-type'] ?? 'none',
    ];
    
    // 添加认证配置
    if ($config['auth_type'] === 'basic') {
        if (!isset($options['username']) || !isset($options['password'])) {
            $logger->error("使用Basic认证时，需要提供username和password参数");
            exit(1);
        }
        
        $config['auth'] = [
            'username' => $options['username'],
            'password' => $options['password'],
        ];
    } elseif ($config['auth_type'] === 'bearer') {
        if (!isset($options['token'])) {
            $logger->error("使用Bearer认证时，需要提供token参数");
            exit(1);
        }
        
        $config['auth'] = [
            'token' => $options['token'],
        ];
    } elseif ($config['auth_type'] === 'api_key') {
        if (!isset($options['key-name']) || !isset($options['key-value'])) {
            $logger->error("使用API Key认证时，需要提供key-name和key-value参数");
            exit(1);
        }
        
        $config['auth'] = [
            'key_name' => $options['key-name'],
            'key_value' => $options['key-value'],
            'key_in' => $options['key-in'] ?? 'header',
        ];
    }
    
    // 设置配置
    $gatewayConfig->setProviderConfig($options['name'], $config);
    
    $logger->info("API配置已添加：{$options['name']}");
    
    // 添加健康检查
    if (isset($options['health-endpoint'])) {
        $healthCheckService = $container->get(\AlingAi\Monitoring\HealthCheck\HealthCheckService::class);
        
        $healthCheck = [
            'api_name' => $options['name'] . ':health',
            'type' => 'external',
            'method' => $options['health-method'] ?? 'GET',
            'endpoint' => $options['health-endpoint'],
            'expected_status' => isset($options['health-status']) ? (int) $options['health-status'] : 200,
            'timeout' => isset($options['health-timeout']) ? (int) $options['health-timeout'] : 5,
            'interval' => isset($options['health-interval']) ? (int) $options['health-interval'] : 60,
            'retries' => isset($options['health-retries']) ? (int) $options['health-retries'] : 3,
            'enabled' => true,
        ];
        
        $healthCheckService->addOrUpdateCheck($healthCheck);
        $logger->info("API健康检查已添加");
    }
}

/**
 * 列出所有API配置
 */
function listApis($container)
{
    $gatewayConfig = $container->get(\AlingAi\Monitoring\Config\GatewayConfig::class);
    $providers = $gatewayConfig->getAllProviders();
    
    if (empty($providers)) {
        echo "未配置任何API\n";
        return;
    }
    
    echo "已配置的API:\n";
    echo str_repeat('-', 80) . "\n";
    echo sprintf("%-20s %-40s %-10s %-10s\n", "名称", "基础URL", "超时(秒)", "认证类型");
    echo str_repeat('-', 80) . "\n";
    
    foreach ($providers as $name => $config) {
        echo sprintf(
            "%-20s %-40s %-10s %-10s\n",
            $name,
            $config['base_url'],
            $config['timeout'] ?? 30,
            $config['auth_type'] ?? 'none'
        );
    }
    
    echo str_repeat('-', 80) . "\n";
}

/**
 * 显示帮助信息
 */
function showHelp()
{
    echo <<<HELP
AlingAi API监控系统命令行工具

用法: php bin/monitor.php [命令] [选项]

可用命令:
  start          启动监控系统
                 选项:
                   --background, -b     在后台运行
                   --pid-file=FILE      指定PID文件位置
                   --log-file=FILE      指定日志文件位置
                   --debug              调试模式(不关闭标准输出)
                   
  stop           停止监控系统
  
  status         查看监控系统状态
  
  check          运行一次健康检查
  
  cleanup        清理过期数据
                 选项:
                   --days=N             指定要保留的天数(默认30天)
                   
  add-api        添加API配置
                 选项:
                   --name=NAME          API名称(必需)
                   --url=URL            API基础URL(必需)
                   --timeout=N          超时时间(秒，默认30)
                   --auth-type=TYPE     认证类型(none|basic|bearer|api_key)
                   --username=USER      Basic认证用户名
                   --password=PASS      Basic认证密码
                   --token=TOKEN        Bearer认证令牌
                   --key-name=NAME      API Key名称
                   --key-value=VALUE    API Key值
                   --key-in=LOCATION    API Key位置(header|query，默认header)
                   --health-endpoint=EP 健康检查端点
                   --health-method=M    健康检查方法(默认GET)
                   --health-status=N    预期状态码(默认200)
                   --health-interval=N  检查间隔(秒，默认60)
                   
  list-apis      列出所有API配置
  
  help           显示此帮助信息

HELP;
}

/**
 * 检查监控系统是否正在运行
 */
function isMonitorRunning($pidFile)
{
    if (!file_exists($pidFile)) {
        return false;
    }
    
    $pid = (int) file_get_contents($pidFile);
    
    if (!$pid) {
        return false;
    }
    
    // 检查进程是否存在
    return posix_kill($pid, 0);
} 