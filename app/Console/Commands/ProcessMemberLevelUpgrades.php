<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Membership\LevelUpgradeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessMemberLevelUpgrades extends Command
{
    /**
     * ��������
     *
     * @var string
     */
    protected $signature = "membership:process-upgrades";

    /**
     * ��������
     *
     * @var string
     */
    protected $description = "�����Ա�ȼ��Զ�����";

    /**
     * �ȼ���������
     *
     * @var LevelUpgradeService
     */
    protected $upgradeService;

    /**
     * ��������ʵ��
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
     * ִ������
     *
     * @return int
     */
    public function handle()
    {
        $this->info("��ʼ�����Ա�ȼ��Զ�����...");
        
        // ��ȡ���л�Ծ��Ա
        $users = User::whereHas("subscriptions", function ($query) {
            $query->where("status", "active")
                  ->where("end_date", ">", now());
        })->get();
        
        $this->info("�ҵ� {$users->count()} ����Ծ��Ա");
        
        $upgradedCount = 0;
        
        // ����ÿ���û����������
        foreach ($users as $user) {
            $this->output->write("���ڼ���û� {$user->id}...");
            
            try {
                $result = $this->upgradeService->checkAndUpgradeLevel($user);
                
                if ($result) {
                    $upgradedCount++;
                    $this->output->writeln(" [������]");
                } else {
                    $this->output->writeln(" [�ޱ仯]");
                }
            } catch (\Exception $e) {
                $this->output->writeln(" [ʧ��]");
                Log::error("�����Ա����ʧ��", [
                    "error" => $e->getMessage(),
                    "user_id" => $user->id,
                ]);
            }
        }
        
        $this->info("��Ա�ȼ��Զ�����������ɣ������� {$upgradedCount} ����Ա");
        
        return Command::SUCCESS;
    }
}
