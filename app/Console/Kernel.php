<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * 应用程序提供的 Artisan 命令
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\DatabaseSecurityMonitor::class,
        \App\Console\Commands\ProcessMembershipRenewals::class,
        \App\Console\Commands\ProcessMemberLevelUpgrades::class,
    ];

    /**
     * 定义应用程序的命令调度
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 每5分钟执行一次系统检查并生成告警
        $schedule->command("monitoring:check-system")
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/monitoring.log"));
            
        // 每10分钟执行一次安全威胁检测
        $schedule->command("security:check-threats")
            ->everyTenMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/security.log"));
            
        // 每5分钟执行一次数据库安全监控任务
        $schedule->command("db:security-monitor")
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/db-security.log"));
            
        // 每小时终止一次超时过长的查询
        $schedule->command("db:security-monitor --kill-long-queries")
            ->hourly()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/db-security.log"));
            
        // 每天监控数据库结构变化
        $schedule->command("db:security-monitor --monitor-changes")
            ->daily()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/db-security.log"));
            
        // 每天凌晨2点处理会员自动续费
        $schedule->command("membership:process-renewals")
            ->dailyAt("02:00")
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/membership.log"));
            
        // 每天凌晨3点处理会员等级自动升级
        $schedule->command("membership:process-upgrades")
            ->dailyAt("03:00")
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/membership.log"));
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
