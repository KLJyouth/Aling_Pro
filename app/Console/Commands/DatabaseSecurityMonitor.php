<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Security\DatabaseSecurityService;
use Illuminate\Support\Facades\Log;

class DatabaseSecurityMonitor extends Command
{
    /**
     * ��������
     *
     * @var string
     */
    protected $signature = 'db:security-monitor {--kill-long-queries : ��ֹ��ʱ�����еĲ�ѯ} {--monitor-changes : ������ݿ�ṹ�仯} {--setup-triggers : ������ƴ�����} {--setup-firewall : �������ݿ����ǽ}';

    /**
     * ��������
     *
     * @var string
     */
    protected $description = 'ִ�����ݿⰲȫ�������';

    /**
     * ���ݿⰲȫ����
     *
     * @var DatabaseSecurityService
     */
    protected $securityService;

    /**
     * ��������ʵ��
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
     * ִ������
     *
     * @return int
     */
    public function handle()
    {
        $this->info('��ʼִ�����ݿⰲȫ�������...');
        
        try {
            // ��ֹ��ʱ�����еĲ�ѯ
            if ($this->option('kill-long-queries')) {
                $this->info('���ڼ�鲢��ֹ��ʱ�����еĲ�ѯ...');
                $this->securityService->killLongRunningQueries();
                $this->info('���');
            }
            
            // ������ݿ�ṹ�仯
            if ($this->option('monitor-changes')) {
                $this->info('���ڼ�����ݿ�ṹ�仯...');
                $this->securityService->monitorDatabaseChanges();
                $this->info('���');
            }
            
            // ������ƴ�����
            if ($this->option('setup-triggers')) {
                $this->info('�����������ݿ���ƴ�����...');
                $this->securityService->setupAuditTriggers();
                $this->info('���');
            }
            
            // �������ݿ����ǽ
            if ($this->option('setup-firewall')) {
                $this->info('�����������ݿ����ǽ����...');
                $this->securityService->setupDatabaseFirewall();
                $this->info('���');
            }
            
            // ���û��ָ��ѡ���ִ����������
            if (!$this->option('kill-long-queries') && !$this->option('monitor-changes') && !$this->option('setup-triggers') && !$this->option('setup-firewall')) {
                $this->info('����ִ�����а�ȫ�������...');
                
                $this->securityService->killLongRunningQueries();
                $this->securityService->monitorDatabaseChanges();
                
                $this->info('���');
            }
            
            $this->info('���ݿⰲȫ�������ִ�����');
            return 0;
        } catch (\Exception $e) {
            $this->error('ִ�����ݿⰲȫ�������ʱ����' . $e->getMessage());
            Log::error('���ݿⰲȫ�������ʧ��', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
