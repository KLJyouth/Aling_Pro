<?php

declare(strict_types=1);

namespace AlingAi\Services;

use AlingAi\Utils\Logger;
use Illuminate\Database\Connection;

/**
 * 备份服务
 * 提供数据库备份、文件备份和恢复功能
 */
class BackupService
{
    private Connection $db;
    private Logger $logger;
    private CacheService $cache;
    private array $config;

    public function __construct(Connection $db, Logger $logger, CacheService $cache)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->cache = $cache;
        $this->loadConfiguration();
    }

    /**
     * 执行备份操作
     */
    public function executeBackup(string $type, string $scope): array
    {
        $backupId = $this->generateBackupId();
        
        try {
            // 记录备份开始
            $this->recordBackupStart($backupId, $type, $scope);
            
            $result = match ($scope) {
                'database' => $this->executeDatabaseBackup($backupId, $type),
                'files' => $this->executeFileBackup($backupId, $type),
                'config' => $this->executeConfigBackup($backupId, $type),
                'full_system' => $this->executeFullSystemBackup($backupId, $type),
                default => throw new \InvalidArgumentException("不支持的备份范围: {$scope}")
            };

            // 更新备份记录为完成状态
            $this->updateBackupRecord($backupId, 'completed', $result);
            
            return [
                'backup_id' => $backupId,
                'estimated_time' => $result['duration'] ?? 0,
                'backup_path' => $result['path'] ?? '',
                'backup_size' => $result['size'] ?? 0
            ];
            
        } catch (\Exception $e) {
            $this->updateBackupRecord($backupId, 'failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * 执行数据库备份
     */
    private function executeDatabaseBackup(string $backupId, string $type): array
    {
        $startTime = microtime(true);
        $timestamp = date('Y_m_d_H_i_s');
        $backupPath = $this->config['backup_path'] . "/database";
        
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $filename = "db_backup_{$type}_{$timestamp}.sql";
        $fullPath = "{$backupPath}/{$filename}";

        // 根据备份类型执行不同的备份策略
        switch ($type) {
            case 'full':
                $this->executeFullDatabaseBackup($fullPath);
                break;
            case 'incremental':
                $this->executeIncrementalBackup($fullPath);
                break;
            case 'differential':
                $this->executeDifferentialBackup($fullPath);
                break;
            default:
                throw new \InvalidArgumentException("不支持的数据库备份类型: {$type}");
        }

        // 压缩备份文件
        if ($this->config['compression_enabled']) {
            $compressedPath = $this->compressBackup($fullPath);
            unlink($fullPath); // 删除原始文件
            $fullPath = $compressedPath;
        }

        // 加密备份文件
        if ($this->config['encryption_enabled']) {
            $encryptedPath = $this->encryptBackup($fullPath);
            unlink($fullPath); // 删除未加密文件
            $fullPath = $encryptedPath;
        }

        $duration = microtime(true) - $startTime;
        $size = filesize($fullPath);

        return [
            'path' => $fullPath,
            'size' => $size,
            'duration' => round($duration, 2),
            'compression_ratio' => $this->calculateCompressionRatio($fullPath),
            'hash' => hash_file('sha256', $fullPath)
        ];
    }

    /**
     * 执行完整数据库备份
     */
    private function executeFullDatabaseBackup(string $outputPath): void
    {
        $dbConfig = config('database.connections.mysql');
        
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
            escapeshellarg($dbConfig['host']),
            escapeshellarg($dbConfig['port']),
            escapeshellarg($dbConfig['username']),
            escapeshellarg($dbConfig['password']),
            escapeshellarg($dbConfig['database']),
            escapeshellarg($outputPath)
        );

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \RuntimeException('数据库备份失败: ' . implode("\n", $output));
        }

        $this->logger->info('数据库完整备份完成', ['path' => $outputPath]);
    }

    /**
     * 执行增量备份
     */
    private function executeIncrementalBackup(string $outputPath): void
    {
        // 获取上次备份时间
        $lastBackup = $this->getLastBackupTime('incremental');
        
        if (!$lastBackup) {
            // 如果没有上次备份，执行完整备份
            $this->executeFullDatabaseBackup($outputPath);
            return;
        }

        // 备份自上次备份以来更改的数据
        $this->backupChangedData($outputPath, $lastBackup);
    }

    /**
     * 执行文件备份
     */
    private function executeFileBackup(string $backupId, string $type): array
    {
        $startTime = microtime(true);
        $timestamp = date('Y_m_d_H_i_s');
        $backupPath = $this->config['backup_path'] . "/files";
        
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $archiveName = "files_backup_{$type}_{$timestamp}.tar.gz";
        $fullPath = "{$backupPath}/{$archiveName}";

        // 要备份的目录
        $directoriesToBackup = [
            __DIR__ . '/../../storage/uploads',
            __DIR__ . '/../../storage/avatars',
            __DIR__ . '/../../public/assets',
            __DIR__ . '/../../resources/views'
        ];

        // 创建tar.gz压缩包
        $this->createTarArchive($directoriesToBackup, $fullPath);

        $duration = microtime(true) - $startTime;
        $size = filesize($fullPath);

        return [
            'path' => $fullPath,
            'size' => $size,
            'duration' => round($duration, 2),
            'hash' => hash_file('sha256', $fullPath)
        ];
    }

    /**
     * 获取备份状态
     */
    public function getBackupStatus(): array
    {
        return [
            'last_backup' => $this->getLastBackupInfo(),
            'backup_schedule' => $this->getScheduleStatus(),
            'storage_usage' => $this->getStorageUsage(),
            'retention_policy' => $this->getRetentionPolicy(),
            'backup_health' => $this->checkBackupHealth()
        ];
    }

    /**
     * 获取调度状态
     */
    public function getScheduleStatus(): array
    {
        return [
            'full_backup' => [
                'enabled' => true,
                'schedule' => '0 2 * * 0', // 每周日凌晨2点
                'next_run' => $this->calculateNextRun('0 2 * * 0'),
                'last_run' => $this->getLastScheduledRun('full')
            ],
            'incremental_backup' => [
                'enabled' => true,
                'schedule' => '0 2 * * 1-6', // 周一到周六凌晨2点
                'next_run' => $this->calculateNextRun('0 2 * * 1-6'),
                'last_run' => $this->getLastScheduledRun('incremental')
            ],
            'file_backup' => [
                'enabled' => true,
                'schedule' => '0 3 * * 0', // 每周日凌晨3点
                'next_run' => $this->calculateNextRun('0 3 * * 0'),
                'last_run' => $this->getLastScheduledRun('files')
            ]
        ];
    }

    /**
     * 获取存储使用情况
     */
    public function getStorageUsage(): array
    {
        $backupPath = $this->config['backup_path'];
        
        if (!file_exists($backupPath)) {
            return [
                'total_size' => 0,
                'file_count' => 0,
                'disk_usage' => 0
            ];
        }

        $totalSize = 0;
        $fileCount = 0;
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($backupPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $totalSize += $file->getSize();
                $fileCount++;
            }
        }

        return [
            'total_size' => $totalSize,
            'file_count' => $fileCount,
            'disk_usage' => $this->getDiskUsage($backupPath),
            'oldest_backup' => $this->getOldestBackupDate(),
            'newest_backup' => $this->getNewestBackupDate()
        ];
    }

    /**
     * 获取保留策略
     */
    public function getRetentionPolicy(): array
    {
        return [
            'full_backups' => [
                'retention_days' => 90,
                'max_count' => 12,
                'cleanup_enabled' => true
            ],
            'incremental_backups' => [
                'retention_days' => 30,
                'max_count' => 30,
                'cleanup_enabled' => true
            ],
            'file_backups' => [
                'retention_days' => 60,
                'max_count' => 8,
                'cleanup_enabled' => true
            ]
        ];
    }

    /**
     * 执行备份清理
     */
    public function cleanupOldBackups(): array
    {
        $cleaned = [];
        $retentionPolicy = $this->getRetentionPolicy();

        foreach ($retentionPolicy as $backupType => $policy) {
            if (!$policy['cleanup_enabled']) {
                continue;
            }

            $expiredBackups = $this->db->table('backup_records')
                ->where('backup_type', str_replace('_backups', '', $backupType))
                ->where('retention_until', '<', now())
                ->orWhere('created_at', '<', now()->subDays($policy['retention_days']))
                ->get();

            foreach ($expiredBackups as $backup) {
                if (file_exists($backup->backup_path)) {
                    unlink($backup->backup_path);
                    $cleaned[] = $backup->backup_path;
                }

                $this->db->table('backup_records')
                    ->where('id', $backup->id)
                    ->delete();
            }
        }

        $this->logger->info('备份清理完成', ['cleaned_files' => count($cleaned)]);
        
        return [
            'cleaned_count' => count($cleaned),
            'cleaned_files' => $cleaned
        ];
    }

    /**
     * 验证备份完整性
     */
    public function verifyBackupIntegrity(string $backupId): array
    {
        $backup = $this->db->table('backup_records')
            ->where('id', $backupId)
            ->first();

        if (!$backup) {
            throw new \RuntimeException("备份记录不存在: {$backupId}");
        }

        $verificationResults = [];

        // 检查文件是否存在
        if (!file_exists($backup->backup_path)) {
            $verificationResults['file_exists'] = false;
            return $verificationResults;
        }

        $verificationResults['file_exists'] = true;

        // 验证文件哈希
        $currentHash = hash_file('sha256', $backup->backup_path);
        $verificationResults['hash_match'] = ($currentHash === $backup->backup_hash);

        // 如果是数据库备份，尝试解析SQL
        if ($backup->backup_scope === 'database') {
            $verificationResults['sql_valid'] = $this->validateSqlBackup($backup->backup_path);
        }

        // 如果是压缩文件，检查压缩包完整性
        if (str_ends_with($backup->backup_path, '.gz')) {
            $verificationResults['archive_valid'] = $this->validateArchive($backup->backup_path);
        }

        return $verificationResults;
    }

    // 辅助方法

    private function loadConfiguration(): void
    {
        $this->config = [
            'backup_path' => __DIR__ . '/../../storage/backups',
            'compression_enabled' => true,
            'encryption_enabled' => false,
            'retention_days' => 30,
            'max_backup_size' => 1024 * 1024 * 1024 // 1GB
        ];
    }

    private function generateBackupId(): string
    {
        return 'backup_' . uniqid() . '_' . time();
    }

    private function recordBackupStart(string $backupId, string $type, string $scope): void
    {
        $this->db->table('backup_records')->insert([
            'id' => $backupId,
            'backup_type' => $type,
            'backup_scope' => $scope,
            'backup_name' => "Backup_{$type}_{$scope}_" . date('Y_m_d_H_i_s'),
            'backup_path' => '',
            'status' => 'started',
            'start_time' => now(),
            'created_at' => now()
        ]);
    }

    private function updateBackupRecord(string $backupId, string $status, array $data = []): void
    {
        $updateData = [
            'status' => $status,
            'updated_at' => now()
        ];

        if ($status === 'completed') {
            $updateData['end_time'] = now();
            $updateData['backup_path'] = $data['path'] ?? '';
            $updateData['backup_size'] = $data['size'] ?? 0;
            $updateData['backup_hash'] = $data['hash'] ?? '';
            $updateData['compression_ratio'] = $data['compression_ratio'] ?? 0;
            $updateData['duration'] = $data['duration'] ?? 0;
            $updateData['retention_until'] = now()->addDays($this->config['retention_days']);
        } elseif ($status === 'failed') {
            $updateData['error_message'] = $data['error'] ?? '';
            $updateData['end_time'] = now();
        }

        $this->db->table('backup_records')
            ->where('id', $backupId)
            ->update($updateData);
    }

    private function compressBackup(string $filePath): string
    {
        $compressedPath = $filePath . '.gz';
        
        $input = fopen($filePath, 'rb');
        $output = gzopen($compressedPath, 'wb9');
        
        while (!feof($input)) {
            gzwrite($output, fread($input, 8192));
        }
        
        fclose($input);
        gzclose($output);
        
        return $compressedPath;
    }

    private function encryptBackup(string $filePath): string
    {
        // 实现备份文件加密逻辑
        // 这里简化处理，实际应该使用更安全的加密方法
        return $filePath;
    }

    private function calculateCompressionRatio(string $filePath): float
    {
        // 简化的压缩比计算
        return 0.7; // 假设70%的压缩率
    }

    private function createTarArchive(array $directories, string $outputPath): void
    {
        $command = sprintf(
            'tar -czf %s %s',
            escapeshellarg($outputPath),
            implode(' ', array_map('escapeshellarg', $directories))
        );

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \RuntimeException('文件备份失败: ' . implode("\n", $output));
        }
    }

    private function getLastBackupTime(string $type): ?string
    {
        $backup = $this->db->table('backup_records')
            ->where('backup_type', $type)
            ->where('status', 'completed')
            ->orderBy('start_time', 'desc')
            ->first();

        return $backup ? $backup->start_time : null;
    }

    private function backupChangedData(string $outputPath, string $lastBackupTime): void
    {
        // 实现增量备份逻辑
        // 这里需要根据具体的数据库设计实现
        $this->logger->info('执行增量备份', ['since' => $lastBackupTime]);
    }

    private function getLastBackupInfo(): ?array
    {
        $backup = $this->db->table('backup_records')
            ->orderBy('start_time', 'desc')
            ->first();

        return $backup ? (array)$backup : null;
    }

    private function calculateNextRun(string $cronExpression): string
    {
        // 实现cron表达式解析和下次运行时间计算
        return date('Y-m-d H:i:s', strtotime('+1 day'));
    }

    private function getLastScheduledRun(string $type): ?string
    {
        $backup = $this->db->table('backup_records')
            ->where('backup_type', $type)
            ->orderBy('start_time', 'desc')
            ->first();

        return $backup ? $backup->start_time : null;
    }

    private function getDiskUsage(string $path): array
    {
        $total = disk_total_space($path);
        $free = disk_free_space($path);
        
        return [
            'total' => $total,
            'free' => $free,
            'used' => $total - $free,
            'usage_percent' => (($total - $free) / $total) * 100
        ];
    }

    private function getOldestBackupDate(): ?string
    {
        $backup = $this->db->table('backup_records')
            ->orderBy('start_time', 'asc')
            ->first();

        return $backup ? $backup->start_time : null;
    }

    private function getNewestBackupDate(): ?string
    {
        $backup = $this->db->table('backup_records')
            ->orderBy('start_time', 'desc')
            ->first();

        return $backup ? $backup->start_time : null;
    }

    private function checkBackupHealth(): array
    {
        $recentBackups = $this->db->table('backup_records')
            ->where('start_time', '>=', now()->subDays(7))
            ->get();

        $totalBackups = $recentBackups->count();
        $successfulBackups = $recentBackups->where('status', 'completed')->count();
        $failedBackups = $recentBackups->where('status', 'failed')->count();

        return [
            'total_backups_7d' => $totalBackups,
            'successful_backups_7d' => $successfulBackups,
            'failed_backups_7d' => $failedBackups,
            'success_rate' => $totalBackups > 0 ? ($successfulBackups / $totalBackups) * 100 : 0,
            'health_status' => $this->determineBackupHealthStatus($successfulBackups, $failedBackups)
        ];
    }

    private function determineBackupHealthStatus(int $successful, int $failed): string
    {
        if ($failed === 0 && $successful > 0) {
            return 'healthy';
        } elseif ($failed > 0 && $successful > $failed) {
            return 'warning';
        } else {
            return 'critical';
        }
    }

    private function validateSqlBackup(string $filePath): bool
    {
        // 简单的SQL文件验证
        $content = file_get_contents($filePath);
        return str_contains($content, 'CREATE TABLE') || str_contains($content, 'INSERT INTO');
    }

    private function validateArchive(string $filePath): bool
    {
        // 验证压缩文件完整性
        $output = [];
        $returnCode = 0;
        exec("gzip -t " . escapeshellarg($filePath), $output, $returnCode);
        
        return $returnCode === 0;
    }
}
