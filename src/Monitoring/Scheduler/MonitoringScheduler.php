<?php
namespace AlingAi\Monitoring\Scheduler;

use AlingAi\Monitoring\HealthCheck\HealthCheckService;
use AlingAi\Monitoring\Storage\MetricsStorageInterface;
use AlingAi\Monitoring\Alert\AlertManager;
use Psr\Log\LoggerInterface;

/**
 * 监控调度器 - 定期执行监控任务
 */
class MonitoringScheduler
{
    /**
     * @var HealthCheckService
     */
    private $healthCheckService;
    
    /**
     * @var MetricsStorageInterface
     */
    private $metricsStorage;
    
    /**
     * @var AlertManager
     */
    private $alertManager;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var array 调度任务
     */
    private $tasks = [];
    
    /**
     * @var bool 是否正在运行
     */
    private $isRunning = false;
    
    /**
     * @var int 上次执行清理的时间戳
     */
    private $lastCleanupTime = 0;

    /**
     * 构造函数
     */
    public function __construct(
        HealthCheckService $healthCheckService,
        MetricsStorageInterface $metricsStorage,
        AlertManager $alertManager,
        LoggerInterface $logger
    ) {
        $this->healthCheckService = $healthCheckService;
        $this->metricsStorage = $metricsStorage;
        $this->alertManager = $alertManager;
        $this->logger = $logger;
        
        $this->initDefaultTasks();
    }

    /**
     * 初始化默认任务
     */
    private function initDefaultTasks(): void
    {
        // 健康检查任务 - 每分钟执行
        $this->addTask('health_check', function() {
            $this->healthCheckService->runChecks();
        }, 60);
        
        // 告警缓存清理任务 - 每小时执行
        $this->addTask('alert_cache_cleanup', function() {
            $this->alertManager->cleanupAlertCache();
        }, 3600);
        
        // 指标数据清理任务 - 每天执行
        $this->addTask('metrics_cleanup', function() {
            // 保留30天的数据
            $this->metricsStorage->cleanupOldData(30 * 86400);
        }, 86400);
    }

    /**
     * 添加任务
     *
     * @param string $name 任务名称
     * @param callable $callback 回调函数
     * @param int $interval 执行间隔(秒)
     * @param bool $runImmediately 是否立即执行一次
     */
    public function addTask(string $name, callable $callback, int $interval, bool $runImmediately = false): void
    {
        $this->tasks[$name] = [
            'callback' => $callback,
            'interval' => $interval,
            'last_run' => $runImmediately ? 0 : time(),
            'enabled' => true,
        ];
    }

    /**
     * 启用任务
     */
    public function enableTask(string $name): void
    {
        if (isset($this->tasks[$name])) {
            $this->tasks[$name]['enabled'] = true;
        }
    }

    /**
     * 禁用任务
     */
    public function disableTask(string $name): void
    {
        if (isset($this->tasks[$name])) {
            $this->tasks[$name]['enabled'] = false;
        }
    }

    /**
     * 删除任务
     */
    public function removeTask(string $name): void
    {
        unset($this->tasks[$name]);
    }

    /**
     * 获取所有任务
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    /**
     * 开始调度器
     */
    public function start(): void
    {
        if ($this->isRunning) {
            return;
        }
        
        $this->isRunning = true;
        $this->logger->info("监控调度器已启动");
        
        while ($this->isRunning) {
            $this->tick();
            sleep(1); // 每秒检查一次
        }
    }

    /**
     * 停止调度器
     */
    public function stop(): void
    {
        $this->isRunning = false;
        $this->logger->info("监控调度器已停止");
    }

    /**
     * 执行一次调度检查
     */
    public function tick(): void
    {
        $now = time();
        
        foreach ($this->tasks as $name => &$task) {
            if (!$task['enabled']) {
                continue;
            }
            
            if ($now - $task['last_run'] >= $task['interval']) {
                $this->executeTask($name, $task);
                $task['last_run'] = $now;
            }
        }
    }

    /**
     * 立即执行任务
     */
    public function executeTask(string $name, array &$task): void
    {
        try {
            $this->logger->debug("执行调度任务", ['task' => $name]);
            
            $start = microtime(true);
            $callback = $task['callback'];
            $callback();
            $duration = microtime(true) - $start;
            
            $this->logger->debug("调度任务执行完成", [
                'task' => $name,
                'duration' => round($duration, 3),
            ]);
        } catch (\Exception $e) {
            $this->logger->error("执行调度任务失败", [
                'task' => $name,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 从命令行运行调度器
     */
    public static function runFromCli(): void
    {
        // 这是一个简单的入口点，用于从命令行运行调度器
        // 实际实现中，应该使用依赖注入容器来创建服务实例
        
        // 设置信号处理器
        pcntl_signal(SIGTERM, function() use (&$scheduler) {
            if (isset($scheduler)) {
                $scheduler->stop();
            }
            exit(0);
        });
        
        pcntl_signal(SIGINT, function() use (&$scheduler) {
            if (isset($scheduler)) {
                $scheduler->stop();
            }
            exit(0);
        });
        
        // 创建日志记录器
        $logger = new \Monolog\Logger('monitoring_scheduler');
        $logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::INFO));
        
        // 创建服务实例
        // 注意：这里需要实际的服务实例化代码
        // 这里只是一个示例
        
        $scheduler = new self(
            new HealthCheckService(/* ... */),
            new MetricsStorageInterface(/* ... */),
            new AlertManager(/* ... */),
            $logger
        );
        
        // 启动调度器
        $scheduler->start();
    }
} 