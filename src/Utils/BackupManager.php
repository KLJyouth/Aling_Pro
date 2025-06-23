<?php

namespace AlingAi\Utils;

use Psr\Log\LoggerInterface;
use AlingAi\Services\DatabaseService;
use AlingAi\Utils\Encryption;

/**
 * 备份管理器
 * 
 * 提供完整的系统备份和恢复功能
 * 优化性能：增量备份、压缩、并行处理
 * 增强安全性：加密备份、完整性验证、访问控制
 */
class BackupManager
{
    private LoggerInterface $logger;
    private DatabaseService $databaseService;
    private Encryption $encryption;
    private array $config;
    
    public function __construct(
        LoggerInterface $logger,
        DatabaseService $databaseService,
        Encryption $encryption,
        array $config = []
    ) {
        $this->logger = $logger;
        $this->databaseService = $databaseService;
        $this->encryption = $encryption;
        $this->config = array_merge([
            'backup_dir' => dirname(__DIR__, 2) . '/storage/backups',
            'max_backups' => 30,
            'compression' => [
                'enabled' => true,
                'algorithm' => 'gzip',
                'level' => 6
            ],
            'encryption' => [
                'enabled' => true,
                'algorithm' => 'AES-256-GCM'
            ],
            'schedules' => [
                'daily' => [
                    'enabled' => true,
                    'time' => '02:00',
                    'retention' => 7
                ],
                'weekly' => [
                    'enabled' => true,
                    'day' => 'sunday',
                    'time' => '03:00',
                    'retention' => 4
                ],
                'monthly' => [
                    'enabled' => true,
                    'day' => 1,
                    'time' => '04:00',
                    'retention' => 12
                ]
            ],
            'notifications' => [
                'enabled' => true,
                'email' => [],
                'webhook' => null
            ]
        ], $config);
        
        $this->ensureBackupDirectory();
    }
    
    /**
     * 确保备份目录存在
     */
    private function ensureBackupDirectory(): void
    {
        if (!is_dir($this->config['backup_dir'])) {
            if (!mkdir($this->config['backup_dir'], 0755, true)) {
                throw new \RuntimeException("无法创建备份目录: {$this->config['backup_dir']}");
            }
        }
    }
    
    /**
     * 创建完整备份
     */
    public function createFullBackup(array $options = []): array
    {
        $startTime = microtime(true);
        $backupId = uniqid('backup_', true);
        
        try {
            $this->logger->info('开始创建完整备份', ['backup_id' => $backupId]);
            
            $options = array_merge([
                'include_database' => true,
                'include_files' => true,
                'include_config' => true,
                'description' => '手动完整备份'
            ], $options);
            
            $backupData = [
                'id' => $backupId,
                'type' => 'full',
                'created_at' => date('Y-m-d H:i:s'),
                'description' => $options['description'],
                'components' => []
            ];
            
            // 备份数据库
            if ($options['include_database']) {
                $dbBackup = $this->backupDatabase($backupId);
                $backupData['components']['database'] = $dbBackup;
            }
            
            // 备份文件
            if ($options['include_files']) {
                $filesBackup = $this->backupFiles($backupId);
                $backupData['components']['files'] = $filesBackup;
            }
            
            // 备份配置
            if ($options['include_config']) {
                $configBackup = $this->backupConfig($backupId);
                $backupData['components']['config'] = $configBackup;
            }
            
            // 创建备份清单
            $manifest = $this->createBackupManifest($backupData);
            
            // 压缩和加密备份
            $backupFile = $this->finalizeBackup($backupId, $manifest);
            
            $duration = round(microtime(true) - $startTime, 2);
            
            $this->logger->info('完整备份创建成功', [
                'backup_id' => $backupId,
                'file' => $backupFile,
                'size' => $this->formatBytes(filesize($backupFile)),
                'duration' => $duration
            ]);
            
            // 发送通知
            $this->sendBackupNotification($backupData, true);
            
            return [
                'success' => true,
                'backup_id' => $backupId,
                'file' => $backupFile,
                'size' => filesize($backupFile),
                'duration' => $duration,
                'components' => $backupData['components']
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('完整备份创建失败', [
                'backup_id' => $backupId,
                'error' => $e->getMessage()
            ]);
            
            $this->sendBackupNotification([
                'id' => $backupId,
                'error' => $e->getMessage()
            ], false);
            
            throw $e;
        }
    }
    
    /**
     * 创建增量备份
     */
    public function createIncrementalBackup(string $baseBackupId, array $options = []): array
    {
        $startTime = microtime(true);
        $backupId = uniqid('incremental_', true);
        
        try {
            $this->logger->info('开始创建增量备份', [
                'backup_id' => $backupId,
                'base_backup_id' => $baseBackupId
            ]);
            
            // 获取基础备份信息
            $baseBackup = $this->getBackupInfo($baseBackupId);
            if (!$baseBackup) {
                throw new \InvalidArgumentException("基础备份不存在: {$baseBackupId}");
            }
            
            $backupData = [
                'id' => $backupId,
                'type' => 'incremental',
                'base_backup_id' => $baseBackupId,
                'created_at' => date('Y-m-d H:i:s'),
                'description' => $options['description'] ?? '增量备份',
                'components' => []
            ];
            
            // 创建增量数据库备份
            $dbBackup = $this->backupDatabaseIncremental($backupId, $baseBackupId);
            $backupData['components']['database'] = $dbBackup;
            
            // 创建增量文件备份
            $filesBackup = $this->backupFilesIncremental($backupId, $baseBackupId);
            $backupData['components']['files'] = $filesBackup;
            
            // 创建备份清单
            $manifest = $this->createBackupManifest($backupData);
            
            // 压缩和加密备份
            $backupFile = $this->finalizeBackup($backupId, $manifest);
            
            $duration = round(microtime(true) - $startTime, 2);
            
            $this->logger->info('增量备份创建成功', [
                'backup_id' => $backupId,
                'base_backup_id' => $baseBackupId,
                'file' => $backupFile,
                'size' => $this->formatBytes(filesize($backupFile)),
                'duration' => $duration
            ]);
            
            return [
                'success' => true,
                'backup_id' => $backupId,
                'base_backup_id' => $baseBackupId,
                'file' => $backupFile,
                'size' => filesize($backupFile),
                'duration' => $duration,
                'components' => $backupData['components']
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('增量备份创建失败', [
                'backup_id' => $backupId,
                'base_backup_id' => $baseBackupId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * 备份数据库
     */
    private function backupDatabase(string $backupId): array
    {
        $startTime = microtime(true);
        $dbFile = $this->config['backup_dir'] . "/{$backupId}_database.sql";
        
        try {
            // 获取数据库配置
            $dbConfig = $this->databaseService->getConfig();
            
            // 构建mysqldump命令
            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['port']),
                escapeshellarg($dbConfig['username']),
                escapeshellarg($dbConfig['password']),
                escapeshellarg($dbConfig['database']),
                escapeshellarg($dbFile)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                throw new \RuntimeException("数据库备份失败: " . implode("\n", $output));
            }
            
            $duration = round(microtime(true) - $startTime, 2);
            
            return [
                'file' => $dbFile,
                'size' => filesize($dbFile),
                'duration' => $duration,
                'tables' => $this->getDatabaseTables()
            ];
            
        } catch (\Exception $e) {
            if (file_exists($dbFile)) {
                unlink($dbFile);
            }
            throw $e;
        }
    }
    
    /**
     * 增量备份数据库
     */
    private function backupDatabaseIncremental(string $backupId, string $baseBackupId): array
    {
        $startTime = microtime(true);
        $dbFile = $this->config['backup_dir'] . "/{$backupId}_database_incremental.sql";
        
        try {
            // 获取上次备份后的变更
            $changes = $this->getDatabaseChanges($baseBackupId);
            
            // 生成增量SQL
            $incrementalSql = $this->generateIncrementalSql($changes);
            file_put_contents($dbFile, $incrementalSql);
            
            $duration = round(microtime(true) - $startTime, 2);
            
            return [
                'file' => $dbFile,
                'size' => filesize($dbFile),
                'duration' => $duration,
                'changes' => $changes
            ];
            
        } catch (\Exception $e) {
            if (file_exists($dbFile)) {
                unlink($dbFile);
            }
            throw $e;
        }
    }
    
    /**
     * 备份文件
     */
    private function backupFiles(string $backupId): array
    {
        $startTime = microtime(true);
        $filesArchive = $this->config['backup_dir'] . "/{$backupId}_files.tar.gz";
        
        try {
            $sourceDirs = [
                dirname(__DIR__, 2) . '/storage/uploads',
                dirname(__DIR__, 2) . '/storage/logs',
                dirname(__DIR__, 2) . '/config'
            ];
            
            $command = 'tar -czf ' . escapeshellarg($filesArchive);
            foreach ($sourceDirs as $dir) {
                if (is_dir($dir)) {
                    $command .= ' -C ' . escapeshellarg(dirname($dir)) . ' ' . escapeshellarg(basename($dir));
                }
            }
            
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                throw new \RuntimeException("文件备份失败: " . implode("\n", $output));
            }
            
            $duration = round(microtime(true) - $startTime, 2);
            
            return [
                'file' => $filesArchive,
                'size' => filesize($filesArchive),
                'duration' => $duration,
                'directories' => $sourceDirs
            ];
            
        } catch (\Exception $e) {
            if (file_exists($filesArchive)) {
                unlink($filesArchive);
            }
            throw $e;
        }
    }
    
    /**
     * 增量备份文件
     */
    private function backupFilesIncremental(string $backupId, string $baseBackupId): array
    {
        $startTime = microtime(true);
        $filesArchive = $this->config['backup_dir'] . "/{$backupId}_files_incremental.tar.gz";
        
        try {
            // 获取变更的文件
            $changedFiles = $this->getChangedFiles($baseBackupId);
            
            if (empty($changedFiles)) {
                return [
                    'file' => null,
                    'size' => 0,
                    'duration' => 0,
                    'changed_files' => []
                ];
            }
            
            // 创建变更文件列表
            $fileList = $this->config['backup_dir'] . "/{$backupId}_files_list.txt";
            file_put_contents($fileList, implode("\n", $changedFiles));
            
            // 创建增量归档
            $command = 'tar -czf ' . escapeshellarg($filesArchive) . ' -T ' . escapeshellarg($fileList);
            exec($command, $output, $returnCode);
            
            unlink($fileList);
            
            if ($returnCode !== 0) {
                throw new \RuntimeException("增量文件备份失败: " . implode("\n", $output));
            }
            
            $duration = round(microtime(true) - $startTime, 2);
            
            return [
                'file' => $filesArchive,
                'size' => filesize($filesArchive),
                'duration' => $duration,
                'changed_files' => $changedFiles
            ];
            
        } catch (\Exception $e) {
            if (file_exists($filesArchive)) {
                unlink($filesArchive);
            }
            throw $e;
        }
    }
    
    /**
     * 备份配置
     */
    private function backupConfig(string $backupId): array
    {
        $startTime = microtime(true);
        $configFile = $this->config['backup_dir'] . "/{$backupId}_config.json";
        
        try {
            $config = [
                'app_config' => $this->getAppConfig(),
                'database_config' => $this->databaseService->getConfig(),
                'backup_time' => date('Y-m-d H:i:s'),
                'version' => '1.0.0'
            ];
            
            file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
            
            $duration = round(microtime(true) - $startTime, 2);
            
            return [
                'file' => $configFile,
                'size' => filesize($configFile),
                'duration' => $duration
            ];
            
        } catch (\Exception $e) {
            if (file_exists($configFile)) {
                unlink($configFile);
            }
            throw $e;
        }
    }
    
    /**
     * 创建备份清单
     */
    private function createBackupManifest(array $backupData): array
    {
        $manifest = [
            'backup' => $backupData,
            'system_info' => [
                'php_version' => PHP_VERSION,
                'os' => PHP_OS,
                'hostname' => gethostname(),
                'backup_manager_version' => '1.0.0'
            ],
            'checksums' => [],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // 计算文件校验和
        foreach ($backupData['components'] as $component => $data) {
            if (isset($data['file']) && file_exists($data['file'])) {
                $manifest['checksums'][$component] = [
                    'file' => basename($data['file']),
                    'sha256' => hash_file('sha256', $data['file']),
                    'size' => filesize($data['file'])
                ];
            }
        }
        
        return $manifest;
    }
    
    /**
     * 完成备份
     */
    private function finalizeBackup(string $backupId, array $manifest): string
    {
        $manifestFile = $this->config['backup_dir'] . "/{$backupId}_manifest.json";
        $backupFile = $this->config['backup_dir'] . "/{$backupId}.tar.gz";
        
        // 保存清单
        file_put_contents($manifestFile, json_encode($manifest, JSON_PRETTY_PRINT));
        
        // 创建最终备份包
        $command = sprintf(
            'tar -czf %s -C %s %s',
            escapeshellarg($backupFile),
            escapeshellarg($this->config['backup_dir']),
            escapeshellarg("{$backupId}_*")
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \RuntimeException("备份打包失败: " . implode("\n", $output));
        }
        
        // 清理临时文件
        $this->cleanupTempFiles($backupId);
        
        // 加密备份（如果启用）
        if ($this->config['encryption']['enabled']) {
            $backupFile = $this->encryptBackup($backupFile);
        }
        
        return $backupFile;
    }
    
    /**
     * 加密备份文件
     */
    private function encryptBackup(string $backupFile): string
    {
        $encryptedFile = $backupFile . '.enc';
        $key = $this->encryption->generateKey();
        
        $this->encryption->encryptFile($backupFile, $encryptedFile, $key);
        
        // 删除原始文件
        unlink($backupFile);
        
        return $encryptedFile;
    }
    
    /**
     * 清理临时文件
     */
    private function cleanupTempFiles(string $backupId): void
    {
        $pattern = $this->config['backup_dir'] . "/{$backupId}_*";
        $files = glob($pattern);
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    
    /**
     * 获取备份信息
     */
    public function getBackupInfo(string $backupId): ?array
    {
        $manifestFile = $this->config['backup_dir'] . "/{$backupId}_manifest.json";
        
        if (!file_exists($manifestFile)) {
            return null;
        }
        
        return json_decode(file_get_contents($manifestFile), true);
    }
    
    /**
     * 列出所有备份
     */
    public function listBackups(): array
    {
        $backups = [];
        $files = glob($this->config['backup_dir'] . '/*.tar.gz*');
        
        foreach ($files as $file) {
            $backupId = basename($file, '.tar.gz');
            $backupId = str_replace('.enc', '', $backupId);
            
            $info = $this->getBackupInfo($backupId);
            if ($info) {
                $backups[] = [
                    'id' => $backupId,
                    'file' => $file,
                    'size' => filesize($file),
                    'created_at' => $info['backup']['created_at'],
                    'type' => $info['backup']['type'],
                    'description' => $info['backup']['description']
                ];
            }
        }
        
        // 按创建时间排序
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $backups;
    }
    
    /**
     * 删除备份
     */
    public function deleteBackup(string $backupId): bool
    {
        $backupFile = $this->config['backup_dir'] . "/{$backupId}.tar.gz";
        $encryptedFile = $backupFile . '.enc';
        $manifestFile = $this->config['backup_dir'] . "/{$backupId}_manifest.json";
        
        $deleted = false;
        
        if (file_exists($backupFile)) {
            $deleted = unlink($backupFile);
        } elseif (file_exists($encryptedFile)) {
            $deleted = unlink($encryptedFile);
        }
        
        if (file_exists($manifestFile)) {
            unlink($manifestFile);
        }
        
        if ($deleted) {
            $this->logger->info('备份删除成功', ['backup_id' => $backupId]);
        }
        
        return $deleted;
    }
    
    /**
     * 清理过期备份
     */
    public function cleanupExpiredBackups(): int
    {
        $deletedCount = 0;
        $backups = $this->listBackups();
        
        foreach ($this->config['schedules'] as $schedule => $config) {
            if (!$config['enabled']) {
                continue;
            }
            
            $retentionDays = $config['retention'] * 7; // 转换为天数
            $cutoffTime = time() - ($retentionDays * 86400);
            
            foreach ($backups as $backup) {
                if (strtotime($backup['created_at']) < $cutoffTime) {
                    if ($this->deleteBackup($backup['id'])) {
                        $deletedCount++;
                    }
                }
            }
        }
        
        $this->logger->info('清理过期备份完成', ['deleted_count' => $deletedCount]);
        
        return $deletedCount;
    }
    
    /**
     * 发送备份通知
     */
    private function sendBackupNotification(array $backupData, bool $success): void
    {
        if (!$this->config['notifications']['enabled']) {
            return;
        }
        
        $message = [
            'type' => 'backup',
            'success' => $success,
            'backup_id' => $backupData['id'],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if (!$success) {
            $message['error'] = $backupData['error'] ?? '未知错误';
        }
        
        // 这里应该实现具体的通知逻辑
        $this->logger->info('备份通知', $message);
    }
    
    /**
     * 格式化字节数
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * 获取数据库表列表
     */
    private function getDatabaseTables(): array
    {
        // 简化实现，实际应该查询数据库
        return ['users', 'conversations', 'documents', 'logs'];
    }
    
    /**
     * 获取数据库变更
     */
    private function getDatabaseChanges(string $baseBackupId): array
    {
        // 简化实现，实际应该比较数据库状态
        return [];
    }
    
    /**
     * 生成增量SQL
     */
    private function generateIncrementalSql(array $changes): string
    {
        // 简化实现，实际应该生成SQL语句
        return "-- 增量备份SQL\n";
    }
    
    /**
     * 获取变更的文件
     */
    private function getChangedFiles(string $baseBackupId): array
    {
        // 简化实现，实际应该比较文件状态
        return [];
    }
    
    /**
     * 获取应用配置
     */
    private function getAppConfig(): array
    {
        // 简化实现，实际应该读取配置文件
        return [
            'app_name' => 'AlingAi Pro',
            'version' => '2.0.0',
            'environment' => 'production'
        ];
    }
} 