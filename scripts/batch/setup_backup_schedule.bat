@echo off
REM AlingAi Pro 备份任务调度配置脚本

echo 配置 AlingAi Pro 备份任务调度...

REM 删除现有任务
schtasks /delete /tn "AlingAi_DatabaseBackup_Full" /f 2>nul
schtasks /delete /tn "AlingAi_DatabaseBackup_Incremental" /f 2>nul
schtasks /delete /tn "AlingAi_FileBackup" /f 2>nul
schtasks /delete /tn "AlingAi_BackupMonitor" /f 2>nul

REM 数据库完整备份 - 每周日凌晨2点
schtasks /create /tn "AlingAi_DatabaseBackup_Full" ^
    /tr "php E:\Code\AlingAi\AlingAi_pro\scripts\database_backup.php full" ^
    /sc weekly /d SUN /st 02:00 /f

REM 数据库增量备份 - 每天凌晨2点（除周日）
schtasks /create /tn "AlingAi_DatabaseBackup_Incremental" ^
    /tr "php E:\Code\AlingAi\AlingAi_pro\scripts\database_backup.php incremental" ^
    /sc daily /st 02:00 /f

REM 文件备份 - 每周日凌晨1点
schtasks /create /tn "AlingAi_FileBackup" ^
    /tr "E:\Code\AlingAi\AlingAi_pro\scripts\file_backup.bat" ^
    /sc weekly /d SUN /st 01:00 /f

REM 备份监控 - 每天上午8点
schtasks /create /tn "AlingAi_BackupMonitor" ^
    /tr "php E:\Code\AlingAi\AlingAi_pro\scripts\backup_monitor.php" ^
    /sc daily /st 08:00 /f

echo 备份任务调度配置完成！

echo.
echo 已配置的任务：
echo - 数据库完整备份：每周日凌晨2点
echo - 数据库增量备份：每天凌晨2点（除周日）
echo - 文件备份：每周日凌晨1点
echo - 备份监控：每天上午8点
echo.
echo 查看任务状态：schtasks /query /tn "AlingAi_*"

pause