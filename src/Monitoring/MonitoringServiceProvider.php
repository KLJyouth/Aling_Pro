<?php
namespace AlingAi\Monitoring;

use AlingAi\Monitoring\ApiGateway;
use AlingAi\Monitoring\Storage\MetricsStorageInterface;
use AlingAi\Monitoring\Storage\TimescaleDbStorage;
use AlingAi\Monitoring\Config\GatewayConfig;
use AlingAi\Monitoring\Alert\AlertManager;
use AlingAi\Monitoring\Alert\Channel\EmailChannel;
use AlingAi\Monitoring\Alert\Channel\SmsChannel;
use AlingAi\Monitoring\Alert\Channel\WebhookChannel;
use AlingAi\Monitoring\HealthCheck\HealthCheckService;
use AlingAi\Monitoring\Scheduler\MonitoringScheduler;
use AlingAi\Core\Container;
use Psr\Log\LoggerInterface;
use PDO;

/**
 * 监控服务提供者 - 将所有监控组件注册到容器中
 */
class MonitoringServiceProvider
{
    /**
     * 注册服务
     */
    public function register(Container $container): void
    {
        // 注册配置
        $container->set(GatewayConfig::class, function(Container $c) {
            $configPath = $c->get('config')['monitoring']['config_path'] ?? __DIR__ . '/../../config/monitoring.json';
            return new GatewayConfig($configPath, $c->get(LoggerInterface::class));
        });
        
        // 注册存储
        $container->set(MetricsStorageInterface::class, function(Container $c) {
            $dbConfig = $c->get('config')['monitoring']['database'] ?? [];
            
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $dbConfig['host'] ?? 'localhost',
                $dbConfig['port'] ?? '5432',
                $dbConfig['database'] ?? 'alingai_monitoring'
            );
            
            $pdo = new PDO(
                $dsn,
                $dbConfig['username'] ?? 'postgres',
                $dbConfig['password'] ?? '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
            
            $storage = new TimescaleDbStorage($pdo, $c->get(LoggerInterface::class));
            $storage->initialize(); // 确保表和索引存在
            
            return $storage;
        });
        
        // 注册告警管理器
        $container->set(AlertManager::class, function(Container $c) {
            $alertManager = new AlertManager($c->get(LoggerInterface::class));
            
            // 添加告警通道
            $alertConfig = $c->get('config')['monitoring']['alerts'] ?? [];
            
            // 邮件通道
            if (isset($alertConfig['email']) && $alertConfig['email']['enabled']) {
                $alertManager->addChannel('email', new EmailChannel(
                    $alertConfig['email'],
                    $c->get(LoggerInterface::class)
                ));
            }
            
            // 短信通道(如果有配置)
            if (isset($alertConfig['sms']) && $alertConfig['sms']['enabled']) {
                $alertManager->addChannel('sms', new SmsChannel(
                    $alertConfig['sms'],
                    $c->get(LoggerInterface::class)
                ));
            }
            
            // Webhook通道(如果有配置)
            if (isset($alertConfig['webhook']) && $alertConfig['webhook']['enabled']) {
                $alertManager->addChannel('webhook', new WebhookChannel(
                    $alertConfig['webhook'],
                    $c->get(LoggerInterface::class)
                ));
            }
            
            // 设置告警阈值
            if (isset($alertConfig['thresholds'])) {
                $alertManager->setSeveritySettings($alertConfig['thresholds']);
            }
            
            return $alertManager;
        });
        
        // 注册API网关
        $container->set(ApiGateway::class, function(Container $c) {
            return new ApiGateway(
                $c->get(MetricsStorageInterface::class),
                $c->get(GatewayConfig::class),
                $c->get(LoggerInterface::class)
            );
        });
        
        // 注册健康检查服务
        $container->set(HealthCheckService::class, function(Container $c) {
            return new HealthCheckService(
                $c->get(MetricsStorageInterface::class),
                $c->get(ApiGateway::class),
                $c->get(GatewayConfig::class),
                $c->get(LoggerInterface::class)
            );
        });
        
        // 注册监控调度器
        $container->set(MonitoringScheduler::class, function(Container $c) {
            return new MonitoringScheduler(
                $c->get(HealthCheckService::class),
                $c->get(MetricsStorageInterface::class),
                $c->get(AlertManager::class),
                $c->get(LoggerInterface::class)
            );
        });
    }

    /**
     * 启动服务
     */
    public function boot(Container $container): void
    {
        // 如果需要在应用启动时执行一些操作，可以在这里实现
        // 例如，启动监控调度器
        
        $schedulerAutostart = $container->get('config')['monitoring']['scheduler_autostart'] ?? false;
        
        if ($schedulerAutostart) {
            // 在后台启动调度器
            $this->startSchedulerInBackground($container);
        }
    }

    /**
     * 在后台启动调度器
     */
    private function startSchedulerInBackground(Container $container): void
    {
        $logger = $container->get(LoggerInterface::class);
        
        try {
            // 检查是否支持pcntl扩展
            if (!function_exists('pcntl_fork')) {
                $logger->warning("无法在后台启动监控调度器: pcntl扩展未启用");
                return;
            }
            
            // 派生进程
            $pid = pcntl_fork();
            
            if ($pid == -1) {
                // 派生失败
                $logger->error("无法派生监控调度器进程");
            } elseif ($pid) {
                // 父进程
                $logger->info("监控调度器已在后台启动，PID: $pid");
                
                // 将PID保存到文件
                $pidFile = $container->get('config')['monitoring']['pid_file'] ?? sys_get_temp_dir() . '/alingai_monitor.pid';
                file_put_contents($pidFile, $pid);
            } else {
                // 子进程
                // 分离会话
                posix_setsid();
                
                // 关闭标准输出/错误
                fclose(STDOUT);
                fclose(STDERR);
                
                // 启动调度器
                $scheduler = $container->get(MonitoringScheduler::class);
                $scheduler->start();
                
                exit(0);
            }
        } catch (\Exception $e) {
            $logger->error("启动监控调度器失败: " . $e->getMessage());
        }
    }
} 