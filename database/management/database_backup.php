<?php
/**
 * 数据库备份脚本 - AlingAi Pro
 */
class DatabaseBackup
{
    private $config = [
        'host' => '',
        'port' => '',
        'database' => '',
        'username' => '',
        'password' => '',
        'backup_path' => 'E:/Backups/AlingAi_Pro/database'
    ];
    
    public function performFullBackup()
    {
        $timestamp = date('Y_m_d_H_i_s');
        $backupFile = $this->config['backup_path'] . "/full_backup_{$timestamp}.sql";
        
        // 创建备份目录
        if (!is_dir($this->config['backup_path'])) {
            mkdir($this->config['backup_path'], 0755, true);
        }
        
        // mysqldump 命令
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --routines --triggers --single-transaction %s > %s',
            $this->config['host'],
            $this->config['port'],
            $this->config['username'],
            $this->config['password'],
            $this->config['database'],
            $backupFile
        );
        
        echo "开始数据库完整备份...
";
        $result = shell_exec($command . ' 2>&1');
        
        if (file_exists($backupFile) && filesize($backupFile) > 0) {
            // 压缩备份文件
            $compressedFile = $backupFile . '.gz';
            shell_exec("gzip {$backupFile}");
            
            echo "数据库备份完成: {$compressedFile}
";
            
            // 清理旧备份
            $this->cleanupOldBackups();
            
            return $compressedFile;
        } else {
            echo "数据库备份失败: {$result}
";
            return false;
        }
    }
    
    public function performIncrementalBackup()
    {
        $timestamp = date('Y_m_d_H_i_s');
        $backupFile = $this->config['backup_path'] . "/incremental_{$timestamp}.sql";
        
        // 获取最后一次备份的时间点
        $lastBackupTime = $this->getLastBackupTime();
        
        // 创建增量备份（基于binlog）
        $command = sprintf(
            'mysqlbinlog --start-datetime="%s" --database=%s /var/log/mysql/mysql-bin.* > %s',
            $lastBackupTime,
            $this->config['database'],
            $backupFile
        );
        
        echo "开始数据库增量备份...
";
        $result = shell_exec($command . ' 2>&1');
        
        if (file_exists($backupFile)) {
            shell_exec("gzip {$backupFile}");
            echo "增量备份完成: {$backupFile}.gz
";
            return $backupFile . '.gz';
        } else {
            echo "增量备份失败: {$result}
";
            return false;
        }
    }
    
    private function getLastBackupTime()
    {
        $lastBackupFile = $this->config['backup_path'] . '/last_backup_time.txt';
        if (file_exists($lastBackupFile)) {
            return trim(file_get_contents($lastBackupFile));
        }
        return date('Y-m-d H:i:s', strtotime('-1 day'));
    }
    
    private function cleanupOldBackups()
    {
        $files = glob($this->config['backup_path'] . '/*.sql.gz');
        if (count($files) > 30) {
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            $filesToDelete = array_slice($files, 0, count($files) - 30);
            foreach ($filesToDelete as $file) {
                unlink($file);
                echo "清理旧备份: {$file}
";
            }
        }
    }
}

// 执行备份
$backup = new DatabaseBackup();
if (isset($argv[1]) && $argv[1] === 'incremental') {
    $backup->performIncrementalBackup();
} else {
    $backup->performFullBackup();
}
?>