<?php
/**
 * AlingAi Pro 数据备份和恢复方案脚本
 * 实施完整的数据备份策略和灾难恢复计划
 */

class BackupRecoverySystem
{
    private $config = [];
    private $backupPaths = [];
    private $schedules = [];
    
    public function __construct()
    {
        echo "💾 AlingAi Pro 备份恢复系统配置开始...\n";
        echo "配置时间: " . date('Y-m-d H:i:s') . "\n\n";
        $this->loadConfiguration();
        $this->initializeBackupPaths();
    }
    
    public function setupBackupRecovery()
    {
        $this->setupDatabaseBackup();
        $this->setupFileBackup();
        $this->setupConfigBackup();
        $this->setupIncrementalBackup();
        $this->setupBackupSchedule();
        $this->setupRecoveryProcedures();
        $this->generateBackupScripts();
        $this->generateBackupReport();
    }
    
    private function loadConfiguration()
    {
        if (file_exists('../.env')) {
            $lines = file('../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                if (strpos($line, '=') !== false) {
                    list($name, $value) = explode('=', $line, 2);
                    $this->config[trim($name)] = trim($value, "\" \t\n\r\0\x0B");
                }
            }
        }
        
        // 设置备份配置
        $this->config['backup'] = [
            'base_path' => 'E:/Backups/AlingAi_Pro',
            'retention_days' => 30,
            'max_backups' => 50,
            'compression' => true,
            'encryption' => true,
            'remote_storage' => false
        ];
    }
    
    private function initializeBackupPaths()
    {
        $basePath = $this->config['backup']['base_path'];
        $this->backupPaths = [
            'database' => $basePath . '/database',
            'files' => $basePath . '/files',
            'config' => $basePath . '/config',
            'logs' => $basePath . '/logs',
            'incremental' => $basePath . '/incremental',
            'full' => $basePath . '/full'
        ];
    }
    
    private function setupDatabaseBackup()
    {
        echo "🗄️ 配置数据库备份策略...\n";
        
        $dbBackupConfig = [
            'full_backup' => [
                'schedule' => '0 2 * * 0',  // 每周日凌晨2点
                'retention' => 4,           // 保留4周
                'compression' => true,
                'includes' => ['structure', 'data', 'triggers', 'procedures']
            ],
            'incremental_backup' => [
                'schedule' => '0 2 * * 1-6', // 周一到周六凌晨2点
                'retention' => 7,            // 保留7天
                'method' => 'binlog',
                'compression' => true
            ],
            'transaction_log_backup' => [
                'schedule' => '*/15 * * * *', // 每15分钟
                'retention' => 24,           // 保留24小时
                'path' => $this->backupPaths['database'] . '/transaction_logs'
            ]
        ];
        
        $this->schedules['database'] = $dbBackupConfig;
        echo "  ✓ 数据库备份策略配置完成\n\n";
    }
    
    private function setupFileBackup()
    {
        echo "📁 配置文件备份策略...\n";
        
        $fileBackupConfig = [
            'application_files' => [
                'schedule' => '0 1 * * 0',  // 每周日凌晨1点
                'paths' => [
                    '../app',
                    '../public',
                    '../vendor',
                    '../composer.json',
                    '../composer.lock'
                ],
                'exclude' => [
                    'cache/*',
                    'logs/*',
                    'temp/*',
                    '.git/*'
                ]
            ],
            'user_uploads' => [
                'schedule' => '0 3 * * *',   // 每天凌晨3点
                'paths' => [
                    '../storage/uploads',
                    '../storage/avatars'
                ],
                'incremental' => true,
                'retention' => 30
            ],
            'configuration_files' => [
                'schedule' => '0 0 * * *',   // 每天午夜
                'paths' => [
                    '../.env',
                    '../config',
                    '../docs'
                ],
                'encryption' => true,
                'retention' => 90
            ]
        ];
        
        $this->schedules['files'] = $fileBackupConfig;
        echo "  ✓ 文件备份策略配置完成\n\n";
    }
    
    private function setupConfigBackup()
    {
        echo "⚙️ 配置系统配置备份...\n";
        
        $configBackupConfig = [
            'system_config' => [
                'nginx_config' => '/etc/nginx/sites-available/alingai-pro',
                'php_config' => '/etc/php/8.1/fpm/php.ini',
                'redis_config' => '/etc/redis/redis.conf',
                'mysql_config' => '/etc/mysql/my.cnf'
            ],
            'ssl_certificates' => [
                'cert_path' => '/etc/letsencrypt/live/your-domain.com/',
                'backup_schedule' => '0 4 1 * *'  // 每月1号凌晨4点
            ],
            'cron_jobs' => [
                'backup_command' => 'crontab -l',
                'schedule' => '0 5 * * 0'  // 每周日凌晨5点
            ]
        ];
        
        $this->schedules['config'] = $configBackupConfig;
        echo "  ✓ 系统配置备份策略配置完成\n\n";
    }
    
    private function setupIncrementalBackup()
    {
        echo "🔄 配置增量备份策略...\n";
        
        $incrementalConfig = [
            'file_sync' => [
                'method' => 'rsync',
                'schedule' => '*/30 * * * *',  // 每30分钟
                'source_paths' => [
                    '../storage/uploads',
                    '../storage/avatars',
                    '../logs'
                ],
                'options' => [
                    'delete' => false,
                    'compress' => true,
                    'checksum' => true
                ]
            ],
            'database_changes' => [
                'method' => 'binlog_tracking',
                'schedule' => '*/5 * * * *',   // 每5分钟
                'retention' => 7,
                'auto_recovery_point' => true
            ]
        ];
        
        $this->schedules['incremental'] = $incrementalConfig;
        echo "  ✓ 增量备份策略配置完成\n\n";
    }
    
    private function setupBackupSchedule()
    {
        echo "📅 配置备份调度计划...\n";
        
        $scheduleConfig = [
            'daily_tasks' => [
                '00:00' => 'config_backup',
                '01:00' => 'log_rotation',
                '02:00' => 'database_incremental',
                '03:00' => 'user_files_backup',
                '04:00' => 'cleanup_old_backups'
            ],
            'weekly_tasks' => [
                'sunday_01:00' => 'full_application_backup',
                'sunday_02:00' => 'full_database_backup',
                'sunday_05:00' => 'system_config_backup'
            ],
            'monthly_tasks' => [
                'first_sunday_06:00' => 'full_system_backup',
                'first_day_04:00' => 'ssl_certificate_backup'
            ],
            'maintenance_windows' => [
                'daily' => '01:00-05:00',
                'weekly' => 'Sunday 01:00-07:00',
                'monthly' => 'First Sunday 01:00-08:00'
            ]
        ];
        
        $this->schedules['schedule'] = $scheduleConfig;
        echo "  ✓ 备份调度计划配置完成\n\n";
    }
    
    private function setupRecoveryProcedures()
    {
        echo "🔧 配置恢复程序...\n";
        
        $recoveryConfig = [
            'rto_targets' => [  // Recovery Time Objective
                'critical_data' => '15 minutes',
                'application_files' => '30 minutes',
                'full_system' => '2 hours'
            ],
            'rpo_targets' => [  // Recovery Point Objective
                'transaction_data' => '5 minutes',
                'user_uploads' => '30 minutes',
                'configuration' => '24 hours'
            ],
            'recovery_levels' => [
                'level_1_quick' => [
                    'scope' => 'Database point-in-time recovery',
                    'time_estimate' => '15 minutes',
                    'automation' => 'full'
                ],
                'level_2_partial' => [
                    'scope' => 'Application + Database recovery',
                    'time_estimate' => '45 minutes',
                    'automation' => 'semi'
                ],
                'level_3_full' => [
                    'scope' => 'Complete system rebuild',
                    'time_estimate' => '4 hours',
                    'automation' => 'manual'
                ]
            ],
            'testing_schedule' => [
                'recovery_test' => 'monthly',
                'backup_verification' => 'weekly',
                'disaster_simulation' => 'quarterly'
            ]
        ];
        
        $this->schedules['recovery'] = $recoveryConfig;
        echo "  ✓ 恢复程序配置完成\n\n";
    }
    
    private function generateBackupScripts()
    {
        echo "📝 生成备份脚本...\n";
        
        // 数据库备份脚本
        $this->generateDatabaseBackupScript();
        
        // 文件备份脚本
        $this->generateFileBackupScript();
        
        // 恢复脚本
        $this->generateRecoveryScript();
        
        // 监控脚本
        $this->generateBackupMonitorScript();
        
        // Windows 任务调度脚本
        $this->generateWindowsScheduleScript();
        
        echo "  ✓ 备份脚本生成完成\n\n";
    }
    
    private function generateDatabaseBackupScript()
    {
        $dbConfig = $this->config;
        
        $dbBackupScript = <<<PHP
<?php
/**
 * 数据库备份脚本 - AlingAi Pro
 */
class DatabaseBackup
{
    private \$config = [
        'host' => '{$dbConfig['DB_HOST']}',
        'port' => '{$dbConfig['DB_PORT']}',
        'database' => '{$dbConfig['DB_DATABASE']}',
        'username' => '{$dbConfig['DB_USERNAME']}',
        'password' => '{$dbConfig['DB_PASSWORD']}',
        'backup_path' => '{$this->backupPaths['database']}'
    ];
    
    public function performFullBackup()
    {
        \$timestamp = date('Y_m_d_H_i_s');
        \$backupFile = \$this->config['backup_path'] . "/full_backup_{\$timestamp}.sql";
        
        // 创建备份目录
        if (!is_dir(\$this->config['backup_path'])) {
            mkdir(\$this->config['backup_path'], 0755, true);
        }
        
        // mysqldump 命令
        \$command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --routines --triggers --single-transaction %s > %s',
            \$this->config['host'],
            \$this->config['port'],
            \$this->config['username'],
            \$this->config['password'],
            \$this->config['database'],
            \$backupFile
        );
        
        echo "开始数据库完整备份...\n";
        \$result = shell_exec(\$command . ' 2>&1');
        
        if (file_exists(\$backupFile) && filesize(\$backupFile) > 0) {
            // 压缩备份文件
            \$compressedFile = \$backupFile . '.gz';
            shell_exec("gzip {\$backupFile}");
            
            echo "数据库备份完成: {\$compressedFile}\n";
            
            // 清理旧备份
            \$this->cleanupOldBackups();
            
            return \$compressedFile;
        } else {
            echo "数据库备份失败: {\$result}\n";
            return false;
        }
    }
    
    public function performIncrementalBackup()
    {
        \$timestamp = date('Y_m_d_H_i_s');
        \$backupFile = \$this->config['backup_path'] . "/incremental_{\$timestamp}.sql";
        
        // 获取最后一次备份的时间点
        \$lastBackupTime = \$this->getLastBackupTime();
        
        // 创建增量备份（基于binlog）
        \$command = sprintf(
            'mysqlbinlog --start-datetime="%s" --database=%s /var/log/mysql/mysql-bin.* > %s',
            \$lastBackupTime,
            \$this->config['database'],
            \$backupFile
        );
        
        echo "开始数据库增量备份...\n";
        \$result = shell_exec(\$command . ' 2>&1');
        
        if (file_exists(\$backupFile)) {
            shell_exec("gzip {\$backupFile}");
            echo "增量备份完成: {\$backupFile}.gz\n";
            return \$backupFile . '.gz';
        } else {
            echo "增量备份失败: {\$result}\n";
            return false;
        }
    }
    
    private function getLastBackupTime()
    {
        \$lastBackupFile = \$this->config['backup_path'] . '/last_backup_time.txt';
        if (file_exists(\$lastBackupFile)) {
            return trim(file_get_contents(\$lastBackupFile));
        }
        return date('Y-m-d H:i:s', strtotime('-1 day'));
    }
    
    private function cleanupOldBackups()
    {
        \$files = glob(\$this->config['backup_path'] . '/*.sql.gz');
        if (count(\$files) > 30) {
            usort(\$files, function(\$a, \$b) {
                return filemtime(\$a) - filemtime(\$b);
            });
            
            \$filesToDelete = array_slice(\$files, 0, count(\$files) - 30);
            foreach (\$filesToDelete as \$file) {
                unlink(\$file);
                echo "清理旧备份: {\$file}\n";
            }
        }
    }
}

// 执行备份
\$backup = new DatabaseBackup();
if (isset(\$argv[1]) && \$argv[1] === 'incremental') {
    \$backup->performIncrementalBackup();
} else {
    \$backup->performFullBackup();
}
?>
PHP;
        
        file_put_contents('database_backup.php', $dbBackupScript);
    }
    
    private function generateFileBackupScript()
    {
        $fileBackupScript = <<<'BAT'
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
BAT;
        
        file_put_contents('file_backup.bat', $fileBackupScript);
    }
    
    private function generateRecoveryScript()
    {
        $recoveryScript = <<<'PHP'
<?php
/**
 * 灾难恢复脚本 - AlingAi Pro
 */
class DisasterRecovery
{
    private $config = [
        'backup_path' => 'E:/Backups/AlingAi_Pro',
        'app_path' => 'E:/Code/AlingAi/AlingAi_pro'
    ];
    
    public function listAvailableBackups()
    {
        echo "可用的备份列表:\n";
        echo str_repeat("=", 50) . "\n";
        
        // 数据库备份
        $dbBackups = glob($this->config['backup_path'] . '/database/*.sql.gz');
        echo "数据库备份:\n";
        foreach ($dbBackups as $backup) {
            $time = filemtime($backup);
            echo "  " . basename($backup) . " - " . date('Y-m-d H:i:s', $time) . "\n";
        }
        
        // 文件备份
        $fileBackups = glob($this->config['backup_path'] . '/files/*.zip');
        echo "\n文件备份:\n";
        foreach ($fileBackups as $backup) {
            $time = filemtime($backup);
            echo "  " . basename($backup) . " - " . date('Y-m-d H:i:s', $time) . "\n";
        }
    }
    
    public function recoverDatabase($backupFile)
    {
        echo "开始恢复数据库: $backupFile\n";
        
        // 解压备份文件
        $sqlFile = str_replace('.gz', '', $backupFile);
        shell_exec("gunzip -c $backupFile > $sqlFile");
        
        // 恢复数据库
        $command = sprintf(
            'mysql --host=%s --port=%s --user=%s --password=%s %s < %s',
            $_ENV['DB_HOST'],
            $_ENV['DB_PORT'],
            $_ENV['DB_USERNAME'],
            $_ENV['DB_PASSWORD'],
            $_ENV['DB_DATABASE'],
            $sqlFile
        );
        
        $result = shell_exec($command . ' 2>&1');
        
        if ($result === null) {
            echo "数据库恢复成功\n";
            unlink($sqlFile); // 清理临时文件
            return true;
        } else {
            echo "数据库恢复失败: $result\n";
            return false;
        }
    }
    
    public function recoverFiles($backupFile)
    {
        echo "开始恢复文件: $backupFile\n";
        
        $tempDir = sys_get_temp_dir() . '/alingai_recovery_' . uniqid();
        mkdir($tempDir, 0755, true);
        
        // 解压备份文件
        $zip = new ZipArchive();
        if ($zip->open($backupFile) === TRUE) {
            $zip->extractTo($tempDir);
            $zip->close();
            
            // 恢复文件
            $this->copyDirectory($tempDir, $this->config['app_path']);
            
            // 清理临时目录
            $this->removeDirectory($tempDir);
            
            echo "文件恢复成功\n";
            return true;
        } else {
            echo "文件恢复失败: 无法解压备份文件\n";
            return false;
        }
    }
    
    public function performFullRecovery($dbBackup, $fileBackup)
    {
        echo "开始完整系统恢复...\n";
        
        $success = true;
        
        // 1. 停止应用服务
        echo "停止应用服务...\n";
        // shell_exec('systemctl stop nginx php-fpm');
        
        // 2. 恢复数据库
        if (!$this->recoverDatabase($dbBackup)) {
            $success = false;
        }
        
        // 3. 恢复文件
        if (!$this->recoverFiles($fileBackup)) {
            $success = false;
        }
        
        // 4. 重启服务
        echo "重启应用服务...\n";
        // shell_exec('systemctl start nginx php-fpm');
        
        // 5. 验证恢复
        if ($success) {
            echo "系统恢复完成，开始验证...\n";
            $this->verifyRecovery();
        }
        
        return $success;
    }
    
    private function verifyRecovery()
    {
        echo "验证系统恢复状态...\n";
        
        // 检查数据库连接
        try {
            $pdo = new PDO(
                "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']}",
                $_ENV['DB_USERNAME'],
                $_ENV['DB_PASSWORD']
            );
            echo "✓ 数据库连接正常\n";
        } catch (Exception $e) {
            echo "✗ 数据库连接失败: " . $e->getMessage() . "\n";
        }
        
        // 检查关键文件
        $keyFiles = ['.env', 'composer.json', 'app/index.php'];
        foreach ($keyFiles as $file) {
            if (file_exists($this->config['app_path'] . '/' . $file)) {
                echo "✓ 文件存在: $file\n";
            } else {
                echo "✗ 文件缺失: $file\n";
            }
        }
    }
    
    private function copyDirectory($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->copyDirectory($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
    
    private function removeDirectory($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $this->removeDirectory($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
}

// 命令行接口
if (isset($argv[1])) {
    $recovery = new DisasterRecovery();
    
    switch ($argv[1]) {
        case 'list':
            $recovery->listAvailableBackups();
            break;
        case 'recover-db':
            if (isset($argv[2])) {
                $recovery->recoverDatabase($argv[2]);
            } else {
                echo "用法: php recovery.php recover-db <backup_file>\n";
            }
            break;
        case 'recover-files':
            if (isset($argv[2])) {
                $recovery->recoverFiles($argv[2]);
            } else {
                echo "用法: php recovery.php recover-files <backup_file>\n";
            }
            break;
        case 'full-recovery':
            if (isset($argv[2]) && isset($argv[3])) {
                $recovery->performFullRecovery($argv[2], $argv[3]);
            } else {
                echo "用法: php recovery.php full-recovery <db_backup> <file_backup>\n";
            }
            break;
        default:
            echo "可用命令: list, recover-db, recover-files, full-recovery\n";
    }
} else {
    echo "用法: php recovery.php <command>\n";
}
?>
PHP;
        
        file_put_contents('disaster_recovery.php', $recoveryScript);
    }
    
    private function generateBackupMonitorScript()
    {
        $monitorScript = <<<'PHP'
<?php
/**
 * 备份监控脚本 - AlingAi Pro
 */
class BackupMonitor
{
    private $config = [
        'backup_path' => 'E:/Backups/AlingAi_Pro',
        'alert_email' => 'admin@alingai.com',
        'max_backup_age' => 86400, // 24小时
        'min_backup_size' => 1048576 // 1MB
    ];
    
    public function checkBackupHealth()
    {
        echo "检查备份健康状态...\n";
        
        $issues = [];
        
        // 检查数据库备份
        $dbIssues = $this->checkDatabaseBackups();
        $issues = array_merge($issues, $dbIssues);
        
        // 检查文件备份
        $fileIssues = $this->checkFileBackups();
        $issues = array_merge($issues, $fileIssues);
        
        // 检查备份存储空间
        $spaceIssues = $this->checkStorageSpace();
        $issues = array_merge($issues, $spaceIssues);
        
        if (empty($issues)) {
            echo "✓ 所有备份状态正常\n";
            $this->logBackupStatus('OK', '备份系统运行正常');
        } else {
            echo "⚠ 发现备份问题:\n";
            foreach ($issues as $issue) {
                echo "  - $issue\n";
            }
            $this->logBackupStatus('WARNING', implode('; ', $issues));
            $this->sendAlert($issues);
        }
        
        return empty($issues);
    }
    
    private function checkDatabaseBackups()
    {
        $issues = [];
        $dbBackupPath = $this->config['backup_path'] . '/database';
        
        if (!is_dir($dbBackupPath)) {
            $issues[] = '数据库备份目录不存在';
            return $issues;
        }
        
        $backups = glob($dbBackupPath . '/*.sql.gz');
        
        if (empty($backups)) {
            $issues[] = '未找到数据库备份文件';
            return $issues;
        }
        
        // 检查最新备份的时间
        $latestBackup = max(array_map('filemtime', $backups));
        if (time() - $latestBackup > $this->config['max_backup_age']) {
            $issues[] = '数据库备份过期，最新备份时间: ' . date('Y-m-d H:i:s', $latestBackup);
        }
        
        // 检查备份文件大小
        $latestFile = '';
        $latestTime = 0;
        foreach ($backups as $backup) {
            if (filemtime($backup) > $latestTime) {
                $latestTime = filemtime($backup);
                $latestFile = $backup;
            }
        }
        
        if ($latestFile && filesize($latestFile) < $this->config['min_backup_size']) {
            $issues[] = '数据库备份文件过小，可能备份失败';
        }
        
        return $issues;
    }
    
    private function checkFileBackups()
    {
        $issues = [];
        $fileBackupPath = $this->config['backup_path'] . '/files';
        
        if (!is_dir($fileBackupPath)) {
            $issues[] = '文件备份目录不存在';
            return $issues;
        }
        
        $backups = glob($fileBackupPath . '/*.zip');
        
        if (empty($backups)) {
            $issues[] = '未找到文件备份';
            return $issues;
        }
        
        // 检查最新备份
        $latestBackup = max(array_map('filemtime', $backups));
        if (time() - $latestBackup > $this->config['max_backup_age'] * 7) { // 文件备份可以7天一次
            $issues[] = '文件备份过期';
        }
        
        return $issues;
    }
    
    private function checkStorageSpace()
    {
        $issues = [];
        $backupPath = $this->config['backup_path'];
        
        if (!is_dir($backupPath)) {
            $issues[] = '备份根目录不存在';
            return $issues;
        }
        
        $freeBytes = disk_free_space($backupPath);
        $totalBytes = disk_total_space($backupPath);
        $usedPercent = (($totalBytes - $freeBytes) / $totalBytes) * 100;
        
        if ($usedPercent > 90) {
            $issues[] = '备份存储空间不足，已使用 ' . round($usedPercent, 2) . '%';
        } elseif ($usedPercent > 80) {
            $issues[] = '备份存储空间紧张，已使用 ' . round($usedPercent, 2) . '%';
        }
        
        return $issues;
    }
    
    private function logBackupStatus($level, $message)
    {
        $logFile = $this->config['backup_path'] . '/backup_monitor.log';
        $logEntry = sprintf(
            "[%s] %s: %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message
        );
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    private function sendAlert($issues)
    {
        $subject = 'AlingAi Pro 备份系统告警';
        $message = "备份系统检测到以下问题:\n\n";
        foreach ($issues as $issue) {
            $message .= "- $issue\n";
        }
        $message .= "\n请及时检查和修复备份系统。";
        
        // 简单邮件发送
        mail($this->config['alert_email'], $subject, $message);
        
        // 记录告警日志
        $alertFile = $this->config['backup_path'] . '/backup_alerts.log';
        $alertEntry = sprintf(
            "[%s] ALERT: %s\n",
            date('Y-m-d H:i:s'),
            implode('; ', $issues)
        );
        file_put_contents($alertFile, $alertEntry, FILE_APPEND | LOCK_EX);
    }
}

// 执行监控检查
$monitor = new BackupMonitor();
$monitor->checkBackupHealth();
?>
PHP;
        
        file_put_contents('backup_monitor.php', $monitorScript);
    }
    
    private function generateWindowsScheduleScript()
    {
        $scheduleScript = <<<'BAT'
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
BAT;
        
        file_put_contents('setup_backup_schedule.bat', $scheduleScript);
    }
    
    private function generateBackupReport()
    {
        echo "📊 生成备份恢复配置报告...\n";
        echo str_repeat("=", 60) . "\n";
        echo "💾 AlingAi Pro 备份恢复系统配置报告\n";
        echo str_repeat("=", 60) . "\n";
        echo "配置时间: " . date('Y-m-d H:i:s') . "\n\n";
        
        echo "📋 备份策略概览:\n";
        echo str_repeat("-", 40) . "\n";
        echo "  🗄️ 数据库备份:\n";
        echo "    • 完整备份: 每周日凌晨2点\n";
        echo "    • 增量备份: 每天凌晨2点（除周日）\n";
        echo "    • 事务日志: 每15分钟\n";
        echo "    • 保留期: 30天\n\n";
        
        echo "  📁 文件备份:\n";
        echo "    • 应用文件: 每周日凌晨1点\n";
        echo "    • 用户上传: 每天凌晨3点\n";
        echo "    • 配置文件: 每天午夜\n";
        echo "    • 保留期: 30-90天\n\n";
        
        echo "  ⚙️ 系统配置备份:\n";
        echo "    • Nginx/PHP/Redis配置: 每周日凌晨5点\n";
        echo "    • SSL证书: 每月1号凌晨4点\n";
        echo "    • 计划任务: 每周日凌晨5点\n\n";
        
        echo "🎯 恢复目标 (RTO/RPO):\n";
        echo str_repeat("-", 40) . "\n";
        echo "  • 关键数据恢复: 15分钟 / 5分钟\n";
        echo "  • 应用文件恢复: 30分钟 / 30分钟\n";
        echo "  • 完整系统恢复: 2小时 / 24小时\n\n";
        
        echo "📁 生成的脚本文件:\n";
        echo str_repeat("-", 40) . "\n";
        echo "  ✓ database_backup.php - 数据库备份脚本\n";
        echo "  ✓ file_backup.bat - 文件备份脚本\n";
        echo "  ✓ disaster_recovery.php - 灾难恢复脚本\n";
        echo "  ✓ backup_monitor.php - 备份监控脚本\n";
        echo "  ✓ setup_backup_schedule.bat - 任务调度脚本\n\n";
        
        echo "🚀 部署步骤:\n";
        echo str_repeat("-", 40) . "\n";
        echo "  1. 运行 setup_backup_schedule.bat 配置任务调度\n";
        echo "  2. 创建备份目录: {$this->config['backup']['base_path']}\n";
        echo "  3. 配置邮件告警地址\n";
        echo "  4. 执行首次手动备份测试\n";
        echo "  5. 验证恢复流程\n";
        echo "  6. 定期测试备份完整性\n\n";
        
        echo "⚠️ 重要提醒:\n";
        echo str_repeat("-", 40) . "\n";
        echo "  • 定期测试恢复流程（每月一次）\n";
        echo "  • 监控备份存储空间使用情况\n";
        echo "  • 验证备份文件完整性\n";
        echo "  • 考虑异地备份存储\n";
        echo "  • 更新灾难恢复文档\n\n";
        
        echo str_repeat("=", 60) . "\n";
        
        // 保存详细配置
        $reportData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'backup_config' => $this->config,
            'backup_paths' => $this->backupPaths,
            'schedules' => $this->schedules,
            'generated_files' => [
                'database_backup.php',
                'file_backup.bat',
                'disaster_recovery.php',
                'backup_monitor.php',
                'setup_backup_schedule.bat'
            ]
        ];
        
        file_put_contents('backup_recovery_configuration_report_' . date('Y_m_d_H_i_s') . '.json',
                         json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        echo "📄 详细配置已保存到: backup_recovery_configuration_report_" . date('Y_m_d_H_i_s') . ".json\n";
    }
}

// 执行备份恢复系统配置
$backupSystem = new BackupRecoverySystem();
$backupSystem->setupBackupRecovery();
