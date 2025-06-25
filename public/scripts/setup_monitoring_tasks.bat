@echo off
REM AlingAi Pro 监控任务调度脚本

REM 每分钟执行系统监控
schtasks /create /tn "AlingAi_SystemMonitor" /tr "php E:\Code\AlingAi\AlingAi_pro\scripts\system_monitor.php" /sc minute /mo 1 /f

REM 每5分钟处理告警
schtasks /create /tn "AlingAi_AlertManager" /tr "php E:\Code\AlingAi\AlingAi_pro\scripts\alert_manager.php" /sc minute /mo 5 /f

echo 监控任务已配置完成
pause