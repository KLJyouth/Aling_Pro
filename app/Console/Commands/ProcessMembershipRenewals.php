<?php

namespace App\Console\Commands;

use App\Services\Membership\AutoRenewalService;
use Illuminate\Console\Command;

class ProcessMembershipRenewals extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected \ = 'membership:process-renewals';

    /**
     * 命令描述
     *
     * @var string
     */
    protected \ = '处理会员自动续费';

    /**
     * 自动续费服务
     *
     * @var AutoRenewalService
     */
    protected \;

    /**
     * 创建命令实例
     *
     * @param AutoRenewalService \
     * @return void
     */
    public function __construct(AutoRenewalService \)
    {
        parent::__construct();
        \->renewalService = \;
    }

    /**
     * 执行命令
     *
     * @return int
     */
    public function handle()
    {
        \->info('开始处理会员自动续费...');
        
        try {
            \->renewalService->processAutoRenewals();
            \->info('会员自动续费处理完成');
            return Command::SUCCESS;
        } catch (\Exception \) {
            \->error('处理会员自动续费失败: ' . \->getMessage());
            return Command::FAILURE;
        }
    }
}
