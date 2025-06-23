<?php

declare(strict_types=1);

namespace AlingAi\Controllers\Api;

use AlingAi\Services\SimpleDiagnosticsService;
use AlingAi\Services\DiagnosticsExportService;
use Exception;

/**
 * 系统API控制器
 * 
 * 处理系统相关的API请求，包括状态检查、诊断等
 */
class SystemApiController extends SimpleBaseApiController
{
    private SimpleDiagnosticsService $diagnosticsService;
    private DiagnosticsExportService $diagnosticsExportService;

    public function __construct()
    {
        parent::__construct();
        $this->diagnosticsService = new SimpleDiagnosticsService();
        $this->diagnosticsExportService = new DiagnosticsExportService();
    }

    /**
     * 测试端点
     */
    public function test(): array
    {
        return $this->sendSuccessResponse([
            'message' => 'System API is working',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
    }

    /**
     * 获取系统状态
     * GET /api/system/status
     */
    public function getStatus(): array
    {
        try {
            $statusData = [
                'system' => 'AlingAi Pro',
                'version' => '1.0.0',
                'status' => 'running',
                'timestamp' => date('Y-m-d H:i:s'),
                'server_time' => time(),
                'php_version' => PHP_VERSION,
                'memory_usage' => memory_get_usage(true),
                'uptime' => $this->getSystemUptime(),
                'load_average' => function_exists('sys_getloadavg') ? sys_getloadavg() : ['N/A', 'N/A', 'N/A'],
                'memory' => [
                    'used' => memory_get_usage(true),
                    'peak' => memory_get_peak_usage(true),
                    'limit' => ini_get('memory_limit')
                ]
            ];

            return $this->sendSuccessResponse($statusData, 'System status retrieved');
        } catch (Exception $e) {
            return $this->sendErrorResponse('获取系统状态失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 执行系统诊断
     * POST /api/system/diagnostics
     */
    public function runDiagnostics(): array
    {
        try {
            $results = $this->diagnosticsService->runDiagnostics();
            return $this->sendSuccessResponse($results, 'System diagnostics completed');
        } catch (Exception $e) {
            return $this->sendErrorResponse('系统诊断失败: ' . $e->getMessage(), 500);
        }
    }    /**
     * 导出诊断报告
     * GET /api/system/diagnostics/export
     */
    public function exportDiagnostics(): array
    {
        try {
            // 获取查询参数
            $format = $_GET['format'] ?? 'json';
            $formats = isset($_GET['formats']) ? explode(',', $_GET['formats']) : [$format];
            
            // 先运行诊断
            $diagnosticData = $this->diagnosticsService->runDiagnostics();
            
            // 导出报告
            if (count($formats) > 1) {
                $results = $this->diagnosticsExportService->exportMultipleFormats($diagnosticData, $formats);
            } else {
                switch ($format) {
                    case 'csv':
                        $results = $this->diagnosticsExportService->exportToCsv($diagnosticData);
                        break;
                    case 'html':
                        $results = $this->diagnosticsExportService->exportToHtml($diagnosticData);
                        break;
                    case 'txt':
                        $results = $this->diagnosticsExportService->exportToText($diagnosticData);
                        break;
                    case 'json':
                    default:
                        $results = $this->diagnosticsExportService->exportToJson($diagnosticData);
                        break;
                }
            }
            
            return $this->sendSuccessResponse($results, '诊断报告导出成功');
        } catch (Exception $e) {
            return $this->sendErrorResponse('导出诊断报告失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取系统信息
     * GET /api/system/info
     */
    public function getSystemInfo(): array
    {
        try {
            $info = [
                'php' => [
                    'version' => PHP_VERSION,
                    'sapi' => PHP_SAPI,
                    'memory_limit' => ini_get('memory_limit'),
                    'max_execution_time' => ini_get('max_execution_time'),
                    'upload_max_filesize' => ini_get('upload_max_filesize'),
                    'post_max_size' => ini_get('post_max_size'),
                ],
                'server' => [
                    'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                    'protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown',
                    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
                    'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
                    'server_port' => $_SERVER['SERVER_PORT'] ?? 'Unknown',
                ],
                'system' => [
                    'os' => PHP_OS,
                    'architecture' => php_uname('m'),
                    'hostname' => gethostname(),
                    'load_average' => $this->getLoadAverage(),
                ],
                'disk' => [
                    'free_space' => disk_free_space('.'),
                    'total_space' => disk_total_space('.'),
                ],
                'memory' => [
                    'current_usage' => memory_get_usage(true),
                    'peak_usage' => memory_get_peak_usage(true),
                    'limit' => $this->parseMemoryLimit(ini_get('memory_limit')),
                ]
            ];

            return $this->sendSuccessResponse($info, 'System information retrieved');
        } catch (Exception $e) {
            return $this->sendErrorResponse('获取系统信息失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 健康检查
     * GET /api/system/health
     */
    public function healthCheck(): array
    {
        try {
            $checks = [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'storage' => $this->checkStorage(),
                'memory' => $this->checkMemory(),
            ];

            $allHealthy = true;
            foreach ($checks as $check) {
                if (!$check['healthy']) {
                    $allHealthy = false;
                    break;
                }
            }

            $healthData = [
                'overall_status' => $allHealthy ? 'healthy' : 'unhealthy',
                'timestamp' => date('Y-m-d H:i:s'),
                'checks' => $checks
            ];

            return $this->sendSuccessResponse($healthData, 'Health check completed');
        } catch (Exception $e) {
            return $this->sendErrorResponse('健康检查失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 下载导出的报告文件
     * GET /api/system/diagnostics/download/{filename}
     */
    public function downloadReport(): array
    {
        try {
            $filename = $_GET['filename'] ?? '';
            if (empty($filename)) {
                return $this->sendErrorResponse('文件名不能为空', 400);
            }
            
            $tempDir = sys_get_temp_dir();
            $filepath = $tempDir . DIRECTORY_SEPARATOR . $filename;
            
            // 安全检查：只允许下载以diagnostic_report开头的文件
            if (!preg_match('/^diagnostic_report_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.(json|csv|html|txt)$/', $filename)) {
                return $this->sendErrorResponse('无效的文件名', 400);
            }
            
            if (!file_exists($filepath)) {
                return $this->sendErrorResponse('文件不存在', 404);
            }
            
            // 设置适当的Content-Type
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $contentTypes = [
                'json' => 'application/json',
                'csv' => 'text/csv',
                'html' => 'text/html',
                'txt' => 'text/plain'
            ];
            
            $contentType = $contentTypes[$extension] ?? 'application/octet-stream';
            
            // 设置响应头
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($filepath));
            header('Cache-Control: no-cache, must-revalidate');
            
            // 输出文件内容
            readfile($filepath);
            
            // 清理文件（可选）
            // unlink($filepath);
            
            exit;
        } catch (Exception $e) {
            return $this->sendErrorResponse('下载文件失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 清理临时文件
     * DELETE /api/system/diagnostics/cleanup
     */
    public function cleanupTempFiles(): array
    {
        try {
            $olderThanHours = (int)($_GET['hours'] ?? 24);
            $deletedCount = $this->diagnosticsExportService->cleanupTempFiles($olderThanHours);
            
            return $this->sendSuccessResponse([
                'deleted_files' => $deletedCount,
                'message' => "清理了 {$deletedCount} 个临时文件"
            ]);
        } catch (Exception $e) {
            return $this->sendErrorResponse('清理临时文件失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取系统运行时间
     */
    private function getSystemUptime(): ?string
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $uptime = @file_get_contents('/proc/uptime');
            if ($uptime !== false) {
                $uptimeSeconds = (float)explode(' ', trim($uptime))[0];
                return $this->formatUptime($uptimeSeconds);
            }
        }
        return null;
    }

    /**
     * 格式化运行时间
     */
    private function formatUptime(float $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        return sprintf('%d天 %d小时 %d分钟', $days, $hours, $minutes);
    }

    /**
     * 获取系统负载
     */
    private function getLoadAverage(): ?array
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                '1min' => $load[0],
                '5min' => $load[1],
                '15min' => $load[2]
            ];
        }
        return null;
    }

    /**
     * 解析内存限制
     */
    private function parseMemoryLimit(string $limit): ?int
    {
        if ($limit === '-1') {
            return null; // 无限制
        }
        
        $unit = strtoupper(substr($limit, -1));
        $value = (int)substr($limit, 0, -1);
        
        switch ($unit) {
            case 'G':
                return $value * 1024 * 1024 * 1024;
            case 'M':
                return $value * 1024 * 1024;
            case 'K':
                return $value * 1024;
            default:
                return (int)$limit;
        }
    }

    /**
     * 检查数据库连接
     */
    private function checkDatabase(): array
    {
        try {
            $host = getenv('DB_HOST') ?: 'localhost';
            $port = getenv('DB_PORT') ?: '3306';
            $dbname = getenv('DB_NAME') ?: 'alingai_pro';
            $username = getenv('DB_USER') ?: 'root';
            $password = getenv('DB_PASSWORD') ?: '';
            
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_TIMEOUT => 5
            ]);
            
            $pdo->query('SELECT 1');
            
            return [
                'healthy' => true,
                'message' => '数据库连接正常',
                'details' => "连接到 {$host}:{$port}"
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => '数据库连接失败',
                'details' => $e->getMessage()
            ];
        }
    }    /**
     * 检查缓存系统
     */
    private function checkCache(): array
    {
        try {
            $host = getenv('REDIS_HOST') ?: '127.0.0.1';
            $port = getenv('REDIS_PORT') ?: 6379;
            $password = getenv('REDIS_PASSWORD') ?: null;
            
            if (class_exists('Redis')) {
                $redis = new \Redis();
                $result = $redis->connect($host, (int)$port, 5);
                
                if (!$result) {
                    throw new \Exception('无法连接到Redis服务器');
                }
                
                if ($password) {
                    $redis->auth($password);
                }
                
                $redis->ping();
                $redis->close();
                
                return [
                    'healthy' => true,
                    'message' => 'Redis连接正常',
                    'details' => "连接到 {$host}:{$port}"
                ];
            } else {
                return [
                    'healthy' => false,
                    'message' => 'Redis扩展未安装',
                    'details' => 'PHP Redis扩展未找到'
                ];
            }
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => 'Redis连接失败',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * 检查存储系统
     */
    private function checkStorage(): array
    {
        try {
            $paths = [
                dirname(__DIR__, 3) . '/storage',
                dirname(__DIR__, 3) . '/public',
                dirname(__DIR__, 3) . '/logs'
            ];

            $issues = [];
            foreach ($paths as $path) {
                if (!is_dir($path)) {
                    $issues[] = "目录不存在: {$path}";
                } elseif (!is_writable($path)) {
                    $issues[] = "目录不可写: {$path}";
                }
            }

            return [
                'healthy' => empty($issues),
                'message' => empty($issues) ? '存储系统正常' : '存储系统异常',
                'details' => empty($issues) ? '所有目录权限正常' : implode(', ', $issues)
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => '存储检查失败',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * 检查内存使用
     */
    private function checkMemory(): array
    {
        try {
            $memoryLimit = ini_get('memory_limit');
            $memoryUsage = memory_get_usage(true);
            
            $limitBytes = $this->parseMemoryLimit($memoryLimit);
            $usagePercent = $limitBytes ? ($memoryUsage / $limitBytes) * 100 : 0;
            
            $healthy = $usagePercent < 80;
            
            return [
                'healthy' => $healthy,
                'message' => "内存使用率: " . round($usagePercent, 2) . "%",
                'details' => "当前使用: " . $this->formatBytes($memoryUsage) . ", 限制: {$memoryLimit}"
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => '内存检查失败',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * 格式化字节数
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}
