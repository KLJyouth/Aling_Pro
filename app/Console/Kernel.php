<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Ӧ�ó����ṩ�� Artisan ����
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\DatabaseSecurityMonitor::class,
    ];

    /**
     * ����Ӧ�ó�����������
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // ÿ5����ִ��һ��ϵͳ��鲢���ɸ澯
        $schedule->command("monitoring:check-system")
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/monitoring.log"));
            
        // ÿ10����ִ��һ�ΰ�ȫ��в���
        $schedule->command("security:check-threats")
            ->everyTenMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/security.log"));
            
        // ÿ5����ִ��һ�����ݿⰲȫ�������
        $schedule->command("db:security-monitor")
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/db-security.log"));
            
        // ÿСʱ��ֹһ�γ�ʱ�����еĲ�ѯ
        $schedule->command("db:security-monitor --kill-long-queries")
            ->hourly()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/db-security.log"));
            
        // ÿ����һ�����ݿ�ṹ�仯
        $schedule->command("db:security-monitor --monitor-changes")
            ->daily()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/db-security.log"));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__."/Commands");

        require base_path("routes/console.php");
    }
}
