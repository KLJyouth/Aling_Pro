@echo off
REM AlingAi Pro 文件备份脚本

set BACKUP_BASE=E:\Backups\AlingAi_Pro\files
set SOURCE_BASE=E:\Code\AlingAi\AlingAi_pro
set TIMESTAMP=%date:~0,4%%date:~5,2%%date:~8,2%_%time:~0,2%%time:~3,2%%time:~6,2%

echo 开始文件备份 - %date% %time%

REM 创建备份目录
if not exist "%BACKUP_BASE%" mkdir "%BACKUP_BASE%"
if not exist "%BACKUP_BASE%\%TIMESTAMP%" mkdir "%BACKUP_BASE%\%TIMESTAMP%"

REM 备份应用程序文件
echo 备份应用程序文件...
robocopy "%SOURCE_BASE%\app" "%BACKUP_BASE%\%TIMESTAMP%\app" /MIR /R:3 /W:10 /LOG+:"%BACKUP_BASE%\backup_%TIMESTAMP%.log"
robocopy "%SOURCE_BASE%\public" "%BACKUP_BASE%\%TIMESTAMP%\public" /MIR /R:3 /W:10 /LOG+:"%BACKUP_BASE%\backup_%TIMESTAMP%.log"
robocopy "%SOURCE_BASE%\config" "%BACKUP_BASE%\%TIMESTAMP%\config" /MIR /R:3 /W:10 /LOG+:"%BACKUP_BASE%\backup_%TIMESTAMP%.log"

REM 备份用户上传文件
echo 备份用户上传文件...
robocopy "%SOURCE_BASE%\storage\uploads" "%BACKUP_BASE%\%TIMESTAMP%\uploads" /MIR /R:3 /W:10 /LOG+:"%BACKUP_BASE%\backup_%TIMESTAMP%.log"
robocopy "%SOURCE_BASE%\storage\avatars" "%BACKUP_BASE%\%TIMESTAMP%\avatars" /MIR /R:3 /W:10 /LOG+:"%BACKUP_BASE%\backup_%TIMESTAMP%.log"

REM 备份配置文件
echo 备份配置文件...
copy "%SOURCE_BASE%\.env" "%BACKUP_BASE%\%TIMESTAMP%\.env" 2>nul
copy "%SOURCE_BASE%\composer.json" "%BACKUP_BASE%\%TIMESTAMP%\composer.json" 2>nul
copy "%SOURCE_BASE%\composer.lock" "%BACKUP_BASE%\%TIMESTAMP%\composer.lock" 2>nul

REM 压缩备份
echo 压缩备份文件...
powershell -Command "Compress-Archive -Path '%BACKUP_BASE%\%TIMESTAMP%' -DestinationPath '%BACKUP_BASE%\backup_%TIMESTAMP%.zip' -Force"

REM 清理临时目录
rmdir /s /q "%BACKUP_BASE%\%TIMESTAMP%"

REM 清理旧备份
echo 清理旧备份文件...
forfiles /p "%BACKUP_BASE%" /m *.zip /d -30 /c "cmd /c del @path" 2>nul

echo 文件备份完成 - %date% %time%
echo 备份文件: %BACKUP_BASE%\backup_%TIMESTAMP%.zip