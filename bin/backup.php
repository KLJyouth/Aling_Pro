<?php
/**
 * AlingAi Pro 自动备份系统
 * 数据库和文件的完整备份解决方案
 * 
 * @author AlingAi Team
 * @version 1.0.0
 * @license MIT
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

class BackupSystem
{
    private array $config;
    private string $backupDir;
    private string $dbConfig;
    
    public function __construct()
    {
        $this->loadConfig();
        $this->backupDir = $this->config['backup_dir'] ?? __DIR__ . '/../storage/backup';
        $this->ensureBackupDirectory();
    }
    
    private function loadConfig(): void
    {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $env = parse_ini_file($envFile);
            $this->config = [
                'db_host' => $env['DB_HOST'] ?? 'localhost',
                'db_port' => $env['DB_PORT'] ?? '3306',
                'db_name' => $env['DB_DATABASE'] ?? 'alingai_pro',
                'db_user' => $env['DB_USERNAME'] ?? 'root',
                'db_pass' => $env['DB_PASSWORD'] ?? '',
                'backup_dir' => __DIR__ . '/../storage/backup',
                'keep_days' => 30, // 保留30天的备份
            ];
        } else {
            throw new Exception('.env文件不存在');
        }
    }
    
    private function ensureBackupDirectory(): void
    {
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    public function createFullBackup(): array
    {
        $timestamp = date('Y-m-d_H-i-s');
        $results = [
            'timestamp' => $timestamp,
            'database' => null,
            'files' => null,
            'config' => null,
        ];
        
        try {
            // 1. 数据库备份
            $results['database'] = $this->backupDatabase($timestamp);
            
            // 2. 文件备份
            $results['files'] = $this->backupFiles($timestamp);
            
            // 3. 配置备份
            $results['config'] = $this->backupConfiguration($timestamp);
            
            // 4. 清理旧备份
            $this->cleanOldBackups();
            
            // 5. 创建备份清单
            $this->createBackupManifest($timestamp, $results);
            
            $this->log("✅ 完整备份创建成功: {$timestamp}");
            
        } catch (Exception $e) {
            $this->log("❌ 备份失败: " . $e->getMessage());
            throw $e;
        }
        
        return $results;
    }
    
    private function backupDatabase(string $timestamp): array
    {
        $result = [
            'status' => 'started',
            'file' => null,
            'size' => 0,
            'duration' => 0,
        ];
        
        $startTime = microtime(true);
        
        try {
            $filename = "database_{$timestamp}.sql";
            $filepath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;
            
            // 使用mysqldump创建数据库备份
            $command = sprintf(
                'mysqldump -h%s -P%s -u%s %s %s > %s',
                escapeshellarg($this->config['db_host']),
                escapeshellarg($this->config['db_port']),
                escapeshellarg($this->config['db_user']),
                !empty($this->config['db_pass']) ? '-p' . escapeshellarg($this->config['db_pass']) : '',
                escapeshellarg($this->config['db_name']),
                escapeshellarg($filepath)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($filepath)) {
                $result['status'] = 'success';
                $result['file'] = $filename;
                $result['size'] = filesize($filepath);
                $result['duration'] = round(microtime(true) - $startTime, 2);
                
                // 压缩备份文件
                $this->compressFile($filepath);
                
                $this->log("✅ 数据库备份完成: {$filename}");
            } else {
                throw new Exception('mysqldump命令执行失败');
            }
            
        } catch (Exception $e) {
            $result['status'] = 'failed';
            $result['error'] = $e->getMessage();
            $this->log("❌ 数据库备份失败: " . $e->getMessage());
        }
        
        return $result;
    }
    
    private function backupFiles(string $timestamp): array
    {
        $result = [
            'status' => 'started',
            'file' => null,
            'size' => 0,
            'duration' => 0,
            'file_count' => 0,
        ];
        
        $startTime = microtime(true);
        
        try {
            $filename = "files_{$timestamp}.tar.gz";
            $filepath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;
            $sourceDir = dirname(__DIR__);
            
            // 要备份的目录和文件
            $includes = [
                'public',
                'src',
                'config',
                'resources',
                'storage/logs',
                '.env',
                'composer.json',
                'composer.lock',
            ];
            
            // 排除的目录和文件
            $excludes = [
                'storage/backup',
                'storage/cache',
                'storage/sessions',
                'vendor',
                'node_modules',
                '.git',
                'tests',
            ];
            
            // 创建文件列表
            $fileList = $this->createFileList($sourceDir, $includes, $excludes);
            $result['file_count'] = count($fileList);
            
            // 创建tar.gz档案
            if ($this->createTarArchive($filepath, $sourceDir, $fileList)) {
                $result['status'] = 'success';
                $result['file'] = $filename;
                $result['size'] = filesize($filepath);
                $result['duration'] = round(microtime(true) - $startTime, 2);
                
                $this->log("✅ 文件备份完成: {$filename} ({$result['file_count']} 个文件)");
            } else {
                throw new Exception('文件归档创建失败');
            }
            
        } catch (Exception $e) {
            $result['status'] = 'failed';
            $result['error'] = $e->getMessage();
            $this->log("❌ 文件备份失败: " . $e->getMessage());
        }
        
        return $result;
    }
    
    private function backupConfiguration(string $timestamp): array
    {
        $result = [
            'status' => 'started',
            'file' => null,
            'size' => 0,
            'duration' => 0,
        ];
        
        $startTime = microtime(true);
        
        try {
            $filename = "config_{$timestamp}.json";
            $filepath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;
            
            // 收集系统配置信息
            $configData = [
                'timestamp' => $timestamp,
                'php_version' => PHP_VERSION,
                'system_info' => [
                    'os' => php_uname(),
                    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
                ],
                'php_extensions' => get_loaded_extensions(),
                'env_config' => $this->config,
                'composer_packages' => $this->getComposerPackages(),
                'backup_settings' => [
                    'backup_dir' => $this->backupDir,
                    'keep_days' => $this->config['keep_days'],
                ],
            ];
            
            // 保存配置文件
            file_put_contents($filepath, json_encode($configData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            $result['status'] = 'success';
            $result['file'] = $filename;
            $result['size'] = filesize($filepath);
            $result['duration'] = round(microtime(true) - $startTime, 2);
            
            $this->log("✅ 配置备份完成: {$filename}");
            
        } catch (Exception $e) {
            $result['status'] = 'failed';
            $result['error'] = $e->getMessage();
            $this->log("❌ 配置备份失败: " . $e->getMessage());
        }
        
        return $result;
    }
    
    private function createFileList(string $sourceDir, array $includes, array $excludes): array
    {
        $fileList = [];
        
        foreach ($includes as $include) {
            $fullPath = $sourceDir . DIRECTORY_SEPARATOR . $include;
            
            if (is_file($fullPath)) {
                $fileList[] = $include;
            } elseif (is_dir($fullPath)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($fullPath)
                );
                
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $relativePath = str_replace($sourceDir . DIRECTORY_SEPARATOR, '', $file->getPathname());
                        $relativePath = str_replace('\\', '/', $relativePath);
                        
                        // 检查是否在排除列表中
                        $shouldExclude = false;
                        foreach ($excludes as $exclude) {
                            if (strpos($relativePath, $exclude) === 0) {
                                $shouldExclude = true;
                                break;
                            }
                        }
                        
                        if (!$shouldExclude) {
                            $fileList[] = $relativePath;
                        }
                    }
                }
            }
        }
        
        return $fileList;
    }
    
    private function createTarArchive(string $filepath, string $sourceDir, array $fileList): bool
    {
        $tar = new PharData($filepath);
        
        foreach ($fileList as $file) {
            $fullPath = $sourceDir . DIRECTORY_SEPARATOR . $file;
            if (file_exists($fullPath)) {
                $tar->addFile($fullPath, $file);
            }
        }
        
        return file_exists($filepath);
    }
    
    private function compressFile(string $filepath): void
    {
        $compressedPath = $filepath . '.gz';
        
        $data = file_get_contents($filepath);
        $compressed = gzencode($data, 9);
        file_put_contents($compressedPath, $compressed);
        
        // 删除原始文件
        unlink($filepath);
    }
    
    private function getComposerPackages(): array
    {
        $composerLock = __DIR__ . '/../composer.lock';
        if (file_exists($composerLock)) {
            $lockData = json_decode(file_get_contents($composerLock), true);
            return $lockData['packages'] ?? [];
        }
        return [];
    }
    
    private function cleanOldBackups(): void
    {
        $keepUntil = time() - ($this->config['keep_days'] * 24 * 60 * 60);
        $deleted = 0;
        
        $files = glob($this->backupDir . DIRECTORY_SEPARATOR . '*');
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $keepUntil) {
                unlink($file);
                $deleted++;
            }
        }
        
        if ($deleted > 0) {
            $this->log("🧹 清理了 {$deleted} 个旧备份文件");
        }
    }
    
    private function createBackupManifest(string $timestamp, array $results): void
    {
        $manifest = [
            'backup_id' => $timestamp,
            'created_at' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'results' => $results,
            'total_size' => 0,
            'files' => [],
        ];
        
        // 计算总大小和文件列表
        $files = glob($this->backupDir . DIRECTORY_SEPARATOR . "*{$timestamp}*");
        foreach ($files as $file) {
            if (is_file($file)) {
                $fileInfo = [
                    'name' => basename($file),
                    'size' => filesize($file),
                    'type' => pathinfo($file, PATHINFO_EXTENSION),
                ];
                $manifest['files'][] = $fileInfo;
                $manifest['total_size'] += $fileInfo['size'];
            }
        }
        
        $manifestFile = $this->backupDir . DIRECTORY_SEPARATOR . "manifest_{$timestamp}.json";
        file_put_contents($manifestFile, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    private function log(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        echo $logMessage;
        
        // 写入日志文件
        $logFile = $this->backupDir . DIRECTORY_SEPARATOR . 'backup.log';
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    public function listBackups(): array
    {
        $backups = [];
        $manifests = glob($this->backupDir . DIRECTORY_SEPARATOR . 'manifest_*.json');
        
        foreach ($manifests as $manifest) {
            $data = json_decode(file_get_contents($manifest), true);
            if ($data) {
                $backups[] = $data;
            }
        }
        
        // 按时间倒序排列
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $backups;
    }
}

// 主程序
if (php_sapi_name() === 'cli') {
    echo "================================================================" . PHP_EOL;
    echo "    AlingAi Pro 自动备份系统 v1.0.0" . PHP_EOL;
    echo "    数据库和文件的完整备份解决方案" . PHP_EOL;
    echo "================================================================" . PHP_EOL;
    
    try {
        $backup = new BackupSystem();
        
        if (isset($argv[1]) && $argv[1] === 'list') {
            // 列出所有备份
            $backups = $backup->listBackups();
            
            if (empty($backups)) {
                echo "📂 没有找到备份文件" . PHP_EOL;
            } else {
                echo "📂 已有备份列表:" . PHP_EOL;
                foreach ($backups as $b) {
                    $size = round($b['total_size'] / 1024 / 1024, 2);
                    echo "  🗂️  {$b['backup_id']} ({$b['created_at']}) - {$size}MB" . PHP_EOL;
                }
            }
        } else {
            // 创建新备份
            echo "🚀 开始创建完整备份..." . PHP_EOL;
            $results = $backup->createFullBackup();
            
            echo PHP_EOL . "📊 备份完成报告:" . PHP_EOL;
            echo "  📅 时间戳: {$results['timestamp']}" . PHP_EOL;
            echo "  💾 数据库: " . ($results['database']['status'] === 'success' ? '✅ 成功' : '❌ 失败') . PHP_EOL;
            echo "  📁 文件: " . ($results['files']['status'] === 'success' ? '✅ 成功' : '❌ 失败') . PHP_EOL;
            echo "  ⚙️  配置: " . ($results['config']['status'] === 'success' ? '✅ 成功' : '❌ 失败') . PHP_EOL;
            
            if ($results['files']['status'] === 'success') {
                echo "  📋 文件数量: {$results['files']['file_count']}" . PHP_EOL;
            }
            
            echo PHP_EOL . "🎉 备份系统运行完成！" . PHP_EOL;
        }
        
    } catch (Exception $e) {
        echo "❌ 备份系统错误: " . $e->getMessage() . PHP_EOL;
        exit(1);
    }
}
