<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Security\DatabaseSecurityService;
use Illuminate\Support\Facades\Log;

class DatabaseSecurityMonitor extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = 'db:security-monitor {--kill-long-queries : 终止长时间运行的查询} {--monitor-changes : 监控数据库结构变化} {--setup-triggers : 设置审计触发器} {--setup-firewall : 设置数据库防火墙}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '执行数据库安全监控任务';

    /**
     * 数据库安全服务
     *
     * @var DatabaseSecurityService
     */
    protected $securityService;

    /**
     * 创建命令实例
     *
     * @param DatabaseSecurityService $securityService
     * @return void
     */
    public function __construct(DatabaseSecurityService $securityService)
    {
        parent::__construct();
        $this->securityService = $securityService;
    }

    /**
     * 执行命令
     *
     * @return int
     */
    public function handle()
    {
        $this->info('开始执行数据库安全监控任务...');
        
        try {
            // 终止长时间运行的查询
            if ($this->option('kill-long-queries')) {
                $this->info('正在检查并终止长时间运行的查询...');
                $this->securityService->killLongRunningQueries();
                $this->info('完成');
            }
            
            // 监控数据库结构变化
            if ($this->option('monitor-changes')) {
                $this->info('正在监控数据库结构变化...');
                $this->securityService->monitorDatabaseChanges();
                $this->info('完成');
            }
            
            // 设置审计触发器
            if ($this->option('setup-triggers')) {
                $this->info('正在设置数据库审计触发器...');
                $this->securityService->setupAuditTriggers();
                $this->info('完成');
            }
            
            // 设置数据库防火墙
            if ($this->option('setup-firewall')) {
                $this->info('正在设置数据库防火墙规则...');
                $this->securityService->setupDatabaseFirewall();
                $this->info('完成');
            }
            
            // 如果没有指定选项，则执行所有任务
            if (!$this->option('kill-long-queries') && !$this->option('monitor-changes') && !$this->option('setup-triggers') && !$this->option('setup-firewall')) {
                $this->info('正在执行所有安全监控任务...');
                
                $this->securityService->killLongRunningQueries();
                $this->securityService->monitorDatabaseChanges();
                
                $this->info('完成');
            }
            
            $this->info('数据库安全监控任务执行完成');
            return 0;
        } catch (\Exception $e) {
            $this->error('执行数据库安全监控任务时出错：' . $e->getMessage());
            Log::error('数据库安全监控任务失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
