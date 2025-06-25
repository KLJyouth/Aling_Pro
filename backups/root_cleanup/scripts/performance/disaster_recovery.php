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