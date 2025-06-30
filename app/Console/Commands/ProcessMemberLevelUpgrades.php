<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Membership\LevelUpgradeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessMemberLevelUpgrades extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = "membership:process-upgrades";

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = "处理会员等级自动升级";

    /**
     * 等级升级服务
     *
     * @var LevelUpgradeService
     */
    protected $upgradeService;

    /**
     * 创建命令实例
     *
     * @param LevelUpgradeService $upgradeService
     * @return void
     */
    public function __construct(LevelUpgradeService $upgradeService)
    {
        parent::__construct();
        $this->upgradeService = $upgradeService;
    }

    /**
     * 执行命令
     *
     * @return int
     */
    public function handle()
    {
        $this->info("开始处理会员等级自动升级...");
        
        // 获取所有活跃会员
        $users = User::whereHas("subscriptions", function ($query) {
            $query->where("status", "active")
                  ->where("end_date", ">", now());
        })->get();
        
        $this->info("找到 {$users->count()} 个活跃会员");
        
        $upgradedCount = 0;
        
        // 处理每个用户的升级检查
        foreach ($users as $user) {
            $this->output->write("正在检查用户 {$user->id}...");
            
            try {
                $result = $this->upgradeService->checkAndUpgradeLevel($user);
                
                if ($result) {
                    $upgradedCount++;
                    $this->output->writeln(" [已升级]");
                } else {
                    $this->output->writeln(" [无变化]");
                }
            } catch (\Exception $e) {
                $this->output->writeln(" [失败]");
                Log::error("处理会员升级失败", [
                    "error" => $e->getMessage(),
                    "user_id" => $user->id,
                ]);
            }
        }
        
        $this->info("会员等级自动升级处理完成，共升级 {$upgradedCount} 个会员");
        
        return Command::SUCCESS;
    }
}
