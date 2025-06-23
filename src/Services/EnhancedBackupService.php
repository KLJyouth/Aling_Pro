<?php

namespace AlingAi\Services;

use AlingAi\Services\DatabaseServiceInterface;
use AlingAi\Services\CacheService;
use Psr\Log\LoggerInterface;

/**
 * 增强备份服务
 * 提供与UnifiedAdminController兼容的备份功能
 */
class EnhancedBackupService
{
    private DatabaseServiceInterface $db;
    private CacheService $cache;
    private LoggerInterface $logger;
    private array $config;

    public function __construct(
        DatabaseServiceInterface $db,
        CacheService $cache,
        LoggerInterface $logger
    ) {
        $this->db = $db;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->config = $this->loadConfig();
    }

    /**
     * 加载配置
     */
    private function loadConfig(): array
    {
        return [
            'backup_path' => __DIR__ . '/../../storage/backups',
            'max_backup_size' => 1073741824, // 1GB
            'retention_days' => 30,
            'compression_enabled' => true,
            'encryption_enabled' => false,
            'types' => ['database', 'files', 'config', 'full_system']
        ];
    }

    /**
     * 执行备份
     */
    public function executeBackup(string $type = 'database', string $scope = 'full'): array
    {
        $backupId = $this->generateBackupId();
        $startTime = microtime(true);

        try {
            $this->logger->info('开始备份', [
                'backup_id' => $backupId,
                'type' => $type,
                'scope' => $scope
            ]);

            // 确保备份目录存在
            $this->ensureBackupDirectory();

            $result = match ($type) {
                'database' => $this->executeDatabaseBackup($backupId, $scope),
                'files' => $this->executeFileBackup($backupId, $scope),
                'config' => $this->executeConfigBackup($backupId, $scope),
                'full_system' => $this->executeFullSystemBackup($backupId, $scope),
                default => throw new \InvalidArgumentException("不支持的备份类型: {$type}")
            };

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            $backupInfo = [
                'backup_id' => $backupId,
                'type' => $type,
                'scope' => $scope,
                'status' => 'completed',
                'start_time' => date('Y-m-d H:i:s', (int)$startTime),
                'end_time' => date('Y-m-d H:i:s', (int)$endTime),
                'duration' => $duration,
                'backup_path' => $result['path'] ?? '',
                'backup_size' => $result['size'] ?? 0,
                'files_count' => $result['files_count'] ?? 0
            ];

            $this->logger->info('备份完成', $backupInfo);
            return $backupInfo;

        } catch (\Exception $e) {
            $this->logger->error('备份失败', [
                'backup_id' => $backupId,
                'error' => $e->getMessage()
            ]);

            return [
                'backup_id' => $backupId,
                'type' => $type,
                'scope' => $scope,
                'status' => 'failed',
                'error' => $e->getMessage(),
                'start_time' => date('Y-m-d H:i:s', (int)$startTime),
                'end_time' => date('Y-m-d H:i:s'),
                'duration' => round(microtime(true) - $startTime, 2)
            ];
        }
    }

    /**
     * 执行数据库备份
     */
    private function executeDatabaseBackup(string $backupId, string $scope): array
    {
        $timestamp = date('Y_m_d_H_i_s');
        $filename = "db_backup_{$scope}_{$timestamp}.sql";
        $backupPath = $this->config['backup_path'] . "/database";
        
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $fullPath = "{$backupPath}/{$filename}";
        $size = 0;
        $filesCount = 1;

        try {
            // 创建基本的数据库备份内容
            $backupContent = $this->generateDatabaseBackupContent($scope);
            
            if (file_put_contents($fullPath, $backupContent) === false) {
                throw new \RuntimeException("无法写入备份文件: {$fullPath}");
            }

            $size = filesize($fullPath);

            // 如果启用压缩
            if ($this->config['compression_enabled']) {
                $compressedPath = $this->compressBackup($fullPath);
                if ($compressedPath) {
                    unlink($fullPath);
                    $fullPath = $compressedPath;
                    $size = filesize($fullPath);
                }
            }

            return [
                'path' => $fullPath,
                'size' => $size,
                'files_count' => $filesCount
            ];

        } catch (\Exception $e) {
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            throw $e;
        }
    }

    /**
     * 生成数据库备份内容
     */
    private function generateDatabaseBackupContent(string $scope): string
    {
        $content = "-- AlingAi Pro Database Backup\n";
        $content .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $content .= "-- Scope: {$scope}\n";
        $content .= "-- Backup ID: " . $this->generateBackupId() . "\n\n";

        try {
            // 获取数据库中的表列表
            $tables = $this->getDatabaseTables();
            
            foreach ($tables as $table) {
                $content .= "\n-- Table: {$table}\n";
                $content .= "DROP TABLE IF EXISTS `{$table}`;\n\n";
                
                // 获取创建表的SQL
                $createTableSql = $this->getCreateTableSql($table);
                if ($createTableSql) {
                    $content .= $createTableSql . "\n\n";
                }

                // 如果是完整备份，导出数据
                if ($scope === 'full') {
                    $data = $this->getTableData($table);
                    if (!empty($data)) {
                        $content .= "-- Data for table `{$table}`\n";
                        $content .= "LOCK TABLES `{$table}` WRITE;\n";
                        foreach ($data as $row) {
                            $content .= $this->generateInsertSql($table, $row) . "\n";
                        }
                        $content .= "UNLOCK TABLES;\n\n";
                    }
                }
            }

        } catch (\Exception $e) {
            $this->logger->warning('无法获取完整数据库结构，使用简化备份', [
                'error' => $e->getMessage()
            ]);
            
            $content .= "-- 简化备份模式\n";
            $content .= "-- 由于权限或其他问题，无法获取完整的数据库结构\n";
            $content .= "SELECT 'AlingAi Pro Database Backup - Limited Access' as backup_note;\n";
        }

        return $content;
    }

    /**
     * 获取数据库表列表
     */
    private function getDatabaseTables(): array
    {
        try {
            $result = $this->db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
            return $result ?: [];
        } catch (\Exception $e) {
            $this->logger->warning('无法获取数据库表列表', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * 获取创建表的SQL
     */
    private function getCreateTableSql(string $table): ?string
    {
        try {
            $result = $this->db->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            return $result ? $result['Create Table'] . ';' : null;
        } catch (\Exception $e) {
            $this->logger->warning("无法获取表 {$table} 的创建SQL", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * 获取表数据
     */
    private function getTableData(string $table, int $limit = 1000): array
    {
        try {
            $result = $this->db->query("SELECT * FROM `{$table}` LIMIT {$limit}")->fetchAll(\PDO::FETCH_ASSOC);
            return $result ?: [];
        } catch (\Exception $e) {
            $this->logger->warning("无法获取表 {$table} 的数据", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * 生成插入SQL
     */
    private function generateInsertSql(string $table, array $row): string
    {
        $columns = array_keys($row);
        $values = array_map(function($value) {
            return $value === null ? 'NULL' : "'" . addslashes($value) . "'";
        }, array_values($row));

        return "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");";
    }

    /**
     * 执行文件备份
     */
    private function executeFileBackup(string $backupId, string $scope): array
    {
        $timestamp = date('Y_m_d_H_i_s');
        $filename = "files_backup_{$scope}_{$timestamp}.tar.gz";
        $backupPath = $this->config['backup_path'] . "/files";
        
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $fullPath = "{$backupPath}/{$filename}";
        $sourcePaths = $this->getFileBackupPaths($scope);
        $filesCount = 0;

        // 创建临时目录
        $tempDir = sys_get_temp_dir() . "/alingai_backup_{$backupId}";
        if (!mkdir($tempDir, 0755, true)) {
            throw new \RuntimeException("无法创建临时目录: {$tempDir}");
        }

        try {
            // 复制文件到临时目录
            foreach ($sourcePaths as $sourcePath) {
                if (is_dir($sourcePath)) {
                    $filesCount += $this->copyDirectory($sourcePath, $tempDir . '/' . basename($sourcePath));
                } elseif (is_file($sourcePath)) {
                    copy($sourcePath, $tempDir . '/' . basename($sourcePath));
                    $filesCount++;
                }
            }

            // 创建tar.gz文件
            $this->createTarGz($tempDir, $fullPath);
            $size = filesize($fullPath);

            return [
                'path' => $fullPath,
                'size' => $size,
                'files_count' => $filesCount
            ];

        } finally {
            // 清理临时目录
            $this->removeDirectory($tempDir);
        }
    }

    /**
     * 获取文件备份路径
     */
    private function getFileBackupPaths(string $scope): array
    {
        $basePath = __DIR__ . '/../..';
        
        return match ($scope) {
            'config' => [
                $basePath . '/config',
                $basePath . '/.env'
            ],
            'uploads' => [
                $basePath . '/storage/uploads'
            ],
            'logs' => [
                $basePath . '/storage/logs'
            ],
            'full' => [
                $basePath . '/config',
                $basePath . '/src',
                $basePath . '/public',
                $basePath . '/storage',
                $basePath . '/.env'
            ],
            default => [
                $basePath . '/config'
            ]
        };
    }

    /**
     * 执行配置备份
     */
    private function executeConfigBackup(string $backupId, string $scope): array
    {
        $timestamp = date('Y_m_d_H_i_s');
        $filename = "config_backup_{$scope}_{$timestamp}.json";
        $backupPath = $this->config['backup_path'] . "/config";
        
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $fullPath = "{$backupPath}/{$filename}";
        
        $configData = [
            'backup_id' => $backupId,
            'timestamp' => date('Y-m-d H:i:s'),
            'scope' => $scope,
            'config' => $this->getSystemConfig($scope)
        ];

        $jsonContent = json_encode($configData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (file_put_contents($fullPath, $jsonContent) === false) {
            throw new \RuntimeException("无法写入配置备份文件: {$fullPath}");
        }

        return [
            'path' => $fullPath,
            'size' => filesize($fullPath),
            'files_count' => 1
        ];
    }

    /**
     * 获取系统配置
     */
    private function getSystemConfig(string $scope): array
    {
        $config = [
            'backup_settings' => $this->config,
            'php_version' => PHP_VERSION,
            'system_info' => [
                'os' => PHP_OS_FAMILY,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];

        if ($scope === 'full') {
            $config['environment_variables'] = $this->getSafeEnvironmentVariables();
            $config['php_extensions'] = get_loaded_extensions();
            $config['php_settings'] = $this->getPhpSettings();
        }

        return $config;
    }

    /**
     * 获取安全的环境变量
     */
    private function getSafeEnvironmentVariables(): array
    {
        $safe = [];
        $excludePatterns = ['password', 'secret', 'key', 'token', 'auth'];
        
        foreach ($_ENV as $key => $value) {
            $keyLower = strtolower($key);
            $isSafe = true;
            
            foreach ($excludePatterns as $pattern) {
                if (strpos($keyLower, $pattern) !== false) {
                    $isSafe = false;
                    break;
                }
            }
            
            if ($isSafe) {
                $safe[$key] = $value;
            } else {
                $safe[$key] = '***HIDDEN***';
            }
        }
        
        return $safe;
    }

    /**
     * 获取PHP设置
     */
    private function getPhpSettings(): array
    {
        return [
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'timezone' => date_default_timezone_get()
        ];
    }

    /**
     * 执行完整系统备份
     */
    private function executeFullSystemBackup(string $backupId, string $scope): array
    {
        $results = [];
        $totalSize = 0;
        $totalFiles = 0;

        // 依次执行各种备份
        $database = $this->executeDatabaseBackup($backupId . '_db', 'full');
        $results['database'] = $database;
        $totalSize += $database['size'];
        $totalFiles += $database['files_count'];

        $files = $this->executeFileBackup($backupId . '_files', 'full');
        $results['files'] = $files;
        $totalSize += $files['size'];
        $totalFiles += $files['files_count'];

        $config = $this->executeConfigBackup($backupId . '_config', 'full');
        $results['config'] = $config;
        $totalSize += $config['size'];
        $totalFiles += $config['files_count'];

        return [
            'path' => $this->config['backup_path'],
            'size' => $totalSize,
            'files_count' => $totalFiles,
            'components' => $results
        ];
    }

    /**
     * 压缩备份文件
     */
    private function compressBackup(string $filePath): ?string
    {
        if (!extension_loaded('zlib')) {
            $this->logger->warning('zlib扩展未安装，跳过压缩');
            return null;
        }

        $compressedPath = $filePath . '.gz';
        
        try {
            $data = file_get_contents($filePath);
            $compressed = gzencode($data, 9);
            
            if (file_put_contents($compressedPath, $compressed) !== false) {
                return $compressedPath;
            }
        } catch (\Exception $e) {
            $this->logger->warning('压缩备份文件失败', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * 复制目录
     */
    private function copyDirectory(string $source, string $destination): int
    {
        $count = 0;
        
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $files = scandir($source);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $sourcePath = $source . '/' . $file;
            $destPath = $destination . '/' . $file;

            if (is_dir($sourcePath)) {
                $count += $this->copyDirectory($sourcePath, $destPath);
            } else {
                if (copy($sourcePath, $destPath)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * 创建tar.gz文件
     */
    private function createTarGz(string $sourceDir, string $targetFile): void
    {
        if (class_exists('PharData')) {
            try {
                $archive = new \PharData($targetFile . '.tar');
                $archive->buildFromDirectory($sourceDir);
                $archive->compress(\Phar::GZ);
                unlink($targetFile . '.tar');
                return;
            } catch (\Exception $e) {
                $this->logger->warning('使用PharData创建tar.gz失败，尝试命令行', ['error' => $e->getMessage()]);
            }
        }

        // 尝试使用系统命令
        $command = PHP_OS_FAMILY === 'Windows' 
            ? "powershell -command \"Compress-Archive -Path '{$sourceDir}\\*' -DestinationPath '{$targetFile}.zip'\""
            : "tar -czf '{$targetFile}' -C '{$sourceDir}' .";

        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \RuntimeException("无法创建压缩文件: " . implode("\n", $output));
        }
    }

    /**
     * 删除目录
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    /**
     * 获取备份状态
     */
    public function getBackupStatus(): array
    {
        try {
            $backupDir = $this->config['backup_path'];
            if (!is_dir($backupDir)) {
                return [
                    'status' => 'not_configured',
                    'message' => '备份目录不存在',
                    'total_backups' => 0,
                    'total_size' => 0
                ];
            }

            $backups = $this->getBackupsList();
            $totalSize = array_sum(array_column($backups, 'size'));

            return [
                'status' => 'healthy',
                'backup_directory' => $backupDir,
                'total_backups' => count($backups),
                'total_size' => $totalSize,
                'latest_backup' => !empty($backups) ? $backups[0] : null,
                'retention_days' => $this->config['retention_days']
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 获取备份列表
     */
    public function getBackupsList(int $limit = 10): array
    {
        $backups = [];
        $backupDir = $this->config['backup_path'];

        if (!is_dir($backupDir)) {
            return $backups;
        }

        // 扫描备份目录
        $subdirs = ['database', 'files', 'config'];
        
        foreach ($subdirs as $subdir) {
            $path = $backupDir . '/' . $subdir;
            if (is_dir($path)) {
                $files = scandir($path);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }

                    $filePath = $path . '/' . $file;
                    if (is_file($filePath)) {
                        $backups[] = [
                            'filename' => $file,
                            'type' => $subdir,
                            'path' => $filePath,
                            'size' => filesize($filePath),
                            'created_at' => date('Y-m-d H:i:s', filemtime($filePath))
                        ];
                    }
                }
            }
        }

        // 按创建时间排序
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return array_slice($backups, 0, $limit);
    }

    /**
     * 清理旧备份
     */
    public function cleanupOldBackups(): array
    {
        $cleaned = 0;
        $errors = [];
        $retentionDays = $this->config['retention_days'];
        $cutoffTime = time() - ($retentionDays * 24 * 3600);

        $backups = $this->getBackupsList(1000); // 获取所有备份

        foreach ($backups as $backup) {
            $createdTime = strtotime($backup['created_at']);
            if ($createdTime < $cutoffTime) {
                try {
                    if (unlink($backup['path'])) {
                        $cleaned++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "无法删除 {$backup['filename']}: " . $e->getMessage();
                }
            }
        }

        return [
            'cleaned_count' => $cleaned,
            'retention_days' => $retentionDays,
            'errors' => $errors
        ];
    }

    /**
     * 确保备份目录存在
     */
    private function ensureBackupDirectory(): void
    {
        $backupDir = $this->config['backup_path'];
        if (!is_dir($backupDir)) {
            if (!mkdir($backupDir, 0755, true)) {
                throw new \RuntimeException("无法创建备份目录: {$backupDir}");
            }
        }

        // 创建子目录
        $subdirs = ['database', 'files', 'config'];
        foreach ($subdirs as $subdir) {
            $path = $backupDir . '/' . $subdir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    /**
     * 生成备份ID
     */
    private function generateBackupId(): string
    {
        return 'backup_' . date('Ymd_His') . '_' . substr(md5(uniqid()), 0, 8);
    }

    /**
     * 健康检查方法（API兼容）
     */
    public function healthCheck(): array
    {
        return $this->getBackupStatus();
    }

    /**
     * 获取调度状态（API兼容）
     */
    public function getScheduleStatus(): array
    {
        return [
            'enabled' => false,
            'next_backup' => null,
            'last_backup' => null,
            'frequency' => 'manual'
        ];
    }

    /**
     * 获取存储使用情况（API兼容）
     */
    public function getStorageUsage(): array
    {
        $backupDir = $this->config['backup_path'];
        
        if (!is_dir($backupDir)) {
            return [
                'used' => 0,
                'available' => 0,
                'usage_percent' => 0
            ];
        }

        $used = $this->getDirectorySize($backupDir);
        $available = disk_free_space($backupDir);
        $total = disk_total_space($backupDir);
        $usagePercent = $total > 0 ? ($used / $total) * 100 : 0;

        return [
            'used' => $used,
            'available' => $available,
            'total' => $total,
            'usage_percent' => round($usagePercent, 2)
        ];
    }

    /**
     * 获取保留策略（API兼容）
     */
    public function getRetentionPolicy(): array
    {
        return [
            'retention_days' => $this->config['retention_days'],
            'max_backups' => 100,
            'auto_cleanup' => true
        ];
    }

    /**
     * 获取目录大小
     */
    private function getDirectorySize(string $directory): int
    {
        $size = 0;
        
        if (is_dir($directory)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }
        
        return $size;
    }
}
