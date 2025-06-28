<?php

namespace App\Console\Commands;

use App\Services\Monitoring\AlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckSystemAndGenerateAlerts extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = 'system:check-alerts';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '检查系统状态并生成告警';

    /**
     * 告警服务
     *
     * @var AlertService
     */
    protected $alertService;

    /**
     * 创建一个新的命令实例
     *
     * @param AlertService $alertService
     * @return void
     */
    public function __construct(AlertService $alertService)
    {
        parent::__construct();
        $this->alertService = $alertService;
    }

    /**
     * 执行命令
     *
     * @return int
     */
    public function handle()
    {
        $this->info('开始检查系统状态...');
        
        try {
            $alerts = $this->alertService->checkAndGenerateAlerts();
            
            $criticalCount = 0;
            $warningCount = 0;
            $infoCount = 0;
            
            foreach ($alerts as $alert) {
                if (isset($alert['level'])) {
                    switch ($alert['level']) {
                        case 'critical':
                            $criticalCount++;
                            break;
                        case 'warning':
                            $warningCount++;
                            break;
                        case 'info':
                            $infoCount++;
                            break;
                    }
                }
            }
            
            $this->info('系统检查完成，生成告警：');
            $this->info("- 严重告警: {$criticalCount}");
            $this->info("- 警告告警: {$warningCount}");
            $this->info("- 信息告警: {$infoCount}");
            
            return 0;
        } catch (\Exception $e) {
            $this->error('系统检查失败: ' . $e->getMessage());
            Log::error('系统检查失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return 1;
        }
    }
} 