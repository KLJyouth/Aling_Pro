<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Security;

/**
 * 备份管理控制器
 * 负责处理系统备份相关请求
 */
class BackupController extends Controller
{
    /**
     * 备份目录
     */
    private $backupDir;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        
        // 设置备份目录
        $this->backupDir = dirname(dirname(__DIR__)) . '/storage/backups';
        
        // 确保备份目录存在
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    /**
     * 备份列表页面
     */
    public function index()
    {
        // 获取所有备份文件
        $backups = $this->getBackups();
        
        // 渲染视图
        View::display('backup.index', [
            'pageTitle' => '备份管理 - IT运维中心',
            'pageHeader' => '备份管理',
            'currentPage' => 'backup',
            'breadcrumbs' => [
                '/admin' => '首页',
                '/admin/backup' => '备份管理'
            ],
            'backups' => $backups
        ]);
    }
    
    /**
     * 创建备份
     */
    public function create()
    {
        try {
            // 生成备份文件名
            $filename = 'backup_' . date('Y-m-d_His') . '.sql';
            $filepath = $this->backupDir . '/' . $filename;
            
            // 获取数据库连接信息
            $db = Database::getInstance();
            $dbConfig = $db->getConfig();
            
            // 构建mysqldump命令
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --port=%s %s > %s',
                escapeshellarg($dbConfig['username']),
                escapeshellarg($dbConfig['password']),
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['port'] ?? '3306'),
                escapeshellarg($dbConfig['database']),
                escapeshellarg($filepath)
            );
            
            // 执行命令
            exec($command, $output, $returnVar);
            
            if ($returnVar !== 0) {
                throw new \Exception('备份创建失败，请检查数据库连接和权限');
            }
            
            // 压缩备份文件
            $zipFilepath = $filepath . '.gz';
            $zipCommand = sprintf('gzip %s', escapeshellarg($filepath));
            exec($zipCommand, $output, $returnVar);
            
            if ($returnVar !== 0) {
                throw new \Exception('备份压缩失败');
            }
            
            // 记录日志
            Logger::info('创建数据库备份', [
                'filename' => $filename . '.gz',
                'size' => filesize($zipFilepath),
                'user_id' => $_SESSION['user_id'] ?? 0
            ]);
            
            $_SESSION['flash_message'] = '备份创建成功';
            $_SESSION['flash_message_type'] = 'success';
        } catch (\Exception $e) {
            Logger::error('创建备份失败: ' . $e->getMessage());
            $_SESSION['flash_message'] = '备份创建失败: ' . $e->getMessage();
            $_SESSION['flash_message_type'] = 'danger';
        }
        
        header('Location: /admin/backup');
        exit;
    }
    
    /**
     * 下载备份
     * 
     * @param string $filename 备份文件名
     */
    public function download($filename)
    {
        // 验证文件名
        if (!$this->isValidBackupFile($filename)) {
            $_SESSION['flash_message'] = '无效的备份文件';
            $_SESSION['flash_message_type'] = 'danger';
            header('Location: /admin/backup');
            exit;
        }
        
        $filepath = $this->backupDir . '/' . $filename;
        
        // 检查文件是否存在
        if (!file_exists($filepath)) {
            $_SESSION['flash_message'] = '备份文件不存在';
            $_SESSION['flash_message_type'] = 'danger';
            header('Location: /admin/backup');
            exit;
        }
        
        // 设置下载头
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        
        // 清除输出缓冲
        ob_clean();
        flush();
        
        // 输出文件
        readfile($filepath);
        exit;
    }
    
    /**
     * 删除备份
     * 
     * @param string $filename 备份文件名
     */
    public function delete($filename)
    {
        // 验证CSRF令牌
        if (!Security::validateCsrfToken()) {
            $_SESSION['flash_message'] = '安全验证失败，请重试';
            $_SESSION['flash_message_type'] = 'danger';
            header('Location: /admin/backup');
            exit;
        }
        
        // 验证文件名
        if (!$this->isValidBackupFile($filename)) {
            $_SESSION['flash_message'] = '无效的备份文件';
            $_SESSION['flash_message_type'] = 'danger';
            header('Location: /admin/backup');
            exit;
        }
        
        $filepath = $this->backupDir . '/' . $filename;
        
        // 检查文件是否存在
        if (!file_exists($filepath)) {
            $_SESSION['flash_message'] = '备份文件不存在';
            $_SESSION['flash_message_type'] = 'danger';
            header('Location: /admin/backup');
            exit;
        }
        
        // 删除文件
        if (unlink($filepath)) {
            // 记录日志
            Logger::info('删除数据库备份', [
                'filename' => $filename,
                'user_id' => $_SESSION['user_id'] ?? 0
            ]);
            
            $_SESSION['flash_message'] = '备份删除成功';
            $_SESSION['flash_message_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = '备份删除失败，请检查文件权限';
            $_SESSION['flash_message_type'] = 'danger';
        }
        
        header('Location: /admin/backup');
        exit;
    }
    
    /**
     * 恢复备份
     * 
     * @param string $filename 备份文件名
     */
    public function restore($filename)
    {
        // 验证CSRF令牌
        if (!Security::validateCsrfToken()) {
            $_SESSION['flash_message'] = '安全验证失败，请重试';
            $_SESSION['flash_message_type'] = 'danger';
            header('Location: /admin/backup');
            exit;
        }
        
        // 验证文件名
        if (!$this->isValidBackupFile($filename)) {
            $_SESSION['flash_message'] = '无效的备份文件';
            $_SESSION['flash_message_type'] = 'danger';
            header('Location: /admin/backup');
            exit;
        }
        
        $filepath = $this->backupDir . '/' . $filename;
        
        // 检查文件是否存在
        if (!file_exists($filepath)) {
            $_SESSION['flash_message'] = '备份文件不存在';
            $_SESSION['flash_message_type'] = 'danger';
            header('Location: /admin/backup');
            exit;
        }
        
        try {
            // 获取数据库连接信息
            $db = Database::getInstance();
            $dbConfig = $db->getConfig();
            
            // 如果是压缩文件，先解压
            $tempFile = null;
            if (substr($filepath, -3) === '.gz') {
                $tempFile = tempnam(sys_get_temp_dir(), 'backup_');
                $command = sprintf('gunzip -c %s > %s', escapeshellarg($filepath), escapeshellarg($tempFile));
                exec($command, $output, $returnVar);
                
                if ($returnVar !== 0) {
                    throw new \Exception('备份解压失败');
                }
                
                $sqlFile = $tempFile;
            } else {
                $sqlFile = $filepath;
            }
            
            // 构建mysql命令
            $command = sprintf(
                'mysql --user=%s --password=%s --host=%s --port=%s %s < %s',
                escapeshellarg($dbConfig['username']),
                escapeshellarg($dbConfig['password']),
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['port'] ?? '3306'),
                escapeshellarg($dbConfig['database']),
                escapeshellarg($sqlFile)
            );
            
            // 执行命令
            exec($command, $output, $returnVar);
            
            // 清理临时文件
            if ($tempFile) {
                unlink($tempFile);
            }
            
            if ($returnVar !== 0) {
                throw new \Exception('备份恢复失败，请检查数据库连接和权限');
            }
            
            // 记录日志
            Logger::info('恢复数据库备份', [
                'filename' => $filename,
                'user_id' => $_SESSION['user_id'] ?? 0
            ]);
            
            $_SESSION['flash_message'] = '备份恢复成功';
            $_SESSION['flash_message_type'] = 'success';
        } catch (\Exception $e) {
            Logger::error('恢复备份失败: ' . $e->getMessage());
            $_SESSION['flash_message'] = '备份恢复失败: ' . $e->getMessage();
            $_SESSION['flash_message_type'] = 'danger';
        }
        
        header('Location: /admin/backup');
        exit;
    }
    
    /**
     * 获取所有备份文件
     * 
     * @return array 备份文件列表
     */
    private function getBackups()
    {
        $backups = [];
        
        // 获取备份目录中的所有文件
        $files = glob($this->backupDir . '/*.{sql,gz}', GLOB_BRACE);
        
        foreach ($files as $file) {
            $filename = basename($file);
            $backups[] = [
                'filename' => $filename,
                'size' => $this->formatBytes(filesize($file)),
                'date' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        
        // 按日期降序排序
        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $backups;
    }
    
    /**
     * 验证备份文件名是否有效
     * 
     * @param string $filename 文件名
     * @return bool 是否有效
     */
    private function isValidBackupFile($filename)
    {
        // 检查文件名格式
        return preg_match('/^backup_\d{4}-\d{2}-\d{2}_\d{6}\.sql(\.gz)?$/', $filename);
    }
    
    /**
     * 格式化字节数为人类可读格式
     * 
     * @param int $bytes 字节数
     * @param int $precision 精度
     * @return string 格式化后的字符串
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
} 