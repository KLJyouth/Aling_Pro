<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Security;

/**
 * 系统设置控制器
 * 负责处理系统设置相关请求
 */
class SettingController extends Controller
{
    /**
     * 系统设置页面
     */
    public function index()
    {
        // 获取所有设置
        $settings = $this->getAllSettings();
        
        // 获取系统信息
        $systemInfo = $this->getSystemInfo();
        
        // 渲染视图
        View::display('settings.index', [
            'pageTitle' => '系统设置 - IT运维中心',
            'pageHeader' => '系统设置',
            'currentPage' => 'settings',
            'breadcrumbs' => [
                '/admin' => '首页',
                '/admin/settings' => '系统设置'
            ],
            'settings' => $settings,
            'systemInfo' => $systemInfo
        ]);
    }
    
    /**
     * 保存系统设置
     */
    public function save()
    {
        // 验证CSRF令牌
        if (!Security::validateCsrfToken()) {
            $_SESSION['flash_message'] = '安全验证失败，请重试';
            $_SESSION['flash_message_type'] = 'danger';
            header('Location: /admin/settings');
            exit;
        }
        
        // 获取表单数据
        $settings = $_POST['settings'] ?? [];
        
        // 验证数据
        $errors = [];
        
        // 网站名称不能为空
        if (empty($settings['site_name'])) {
            $errors[] = '网站名称不能为空';
        }
        
        // 管理员邮箱必须是有效的邮箱
        if (!empty($settings['admin_email']) && !filter_var($settings['admin_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = '管理员邮箱格式不正确';
        }
        
        // 如果有错误，重新显示表单
        if (!empty($errors)) {
            $_SESSION['flash_message'] = implode('<br>', $errors);
            $_SESSION['flash_message_type'] = 'danger';
            $_SESSION['form_data'] = $settings;
            header('Location: /admin/settings');
            exit;
        }
        
        // 保存设置
        $success = $this->saveSettings($settings);
        
        if ($success) {
            $_SESSION['flash_message'] = '设置保存成功';
            $_SESSION['flash_message_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = '设置保存失败，请重试';
            $_SESSION['flash_message_type'] = 'danger';
            $_SESSION['form_data'] = $settings;
        }
        
        header('Location: /admin/settings');
        exit;
    }
    
    /**
     * 获取所有系统设置
     * 
     * @return array 系统设置
     */
    private function getAllSettings()
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT * FROM settings");
            $settings = [];
            
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $settings[$row['key']] = $row['value'];
            }
            
            // 设置默认值
            $defaults = [
                'site_name' => 'AlingAi Pro IT运维中心',
                'site_description' => '高效的IT运维管理平台',
                'admin_email' => 'admin@example.com',
                'items_per_page' => '10',
                'enable_registration' => '0',
                'maintenance_mode' => '0',
                'log_level' => 'warning',
                'backup_auto' => '1',
                'backup_interval' => 'daily',
                'theme' => 'default',
                'timezone' => 'Asia/Shanghai',
                'date_format' => 'Y-m-d H:i:s'
            ];
            
            foreach ($defaults as $key => $value) {
                if (!isset($settings[$key])) {
                    $settings[$key] = $value;
                }
            }
            
            return $settings;
        } catch (\Exception $e) {
            Logger::error('获取系统设置失败: ' . $e->getMessage());
            
            // 返回默认设置
            return [
                'site_name' => 'AlingAi Pro IT运维中心',
                'site_description' => '高效的IT运维管理平台',
                'admin_email' => 'admin@example.com',
                'items_per_page' => '10',
                'enable_registration' => '0',
                'maintenance_mode' => '0',
                'log_level' => 'warning',
                'backup_auto' => '1',
                'backup_interval' => 'daily',
                'theme' => 'default',
                'timezone' => 'Asia/Shanghai',
                'date_format' => 'Y-m-d H:i:s'
            ];
        }
    }
    
    /**
     * 保存系统设置
     * 
     * @param array $settings 设置数组
     * @return bool 是否成功
     */
    private function saveSettings($settings)
    {
        try {
            $db = Database::getInstance();
            
            foreach ($settings as $key => $value) {
                // 检查设置是否存在
                $stmt = $db->prepare("SELECT COUNT(*) FROM settings WHERE `key` = ?");
                $stmt->execute([$key]);
                $exists = (int)$stmt->fetchColumn() > 0;
                
                if ($exists) {
                    // 更新设置
                    $stmt = $db->prepare("UPDATE settings SET `value` = ?, `updated_at` = ? WHERE `key` = ?");
                    $stmt->execute([$value, date('Y-m-d H:i:s'), $key]);
                } else {
                    // 创建新设置
                    $stmt = $db->prepare("INSERT INTO settings (`key`, `value`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$key, $value, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);
                }
            }
            
            return true;
        } catch (\Exception $e) {
            Logger::error('保存系统设置失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取系统信息
     * 
     * @return array 系统信息
     */
    private function getSystemInfo()
    {
        return [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'operating_system' => PHP_OS,
            'database_version' => $this->getDatabaseVersion(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time') . ' 秒',
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'server_time' => date('Y-m-d H:i:s'),
            'timezone' => date_default_timezone_get(),
            'disk_free_space' => $this->formatBytes(disk_free_space('/')),
            'disk_total_space' => $this->formatBytes(disk_total_space('/'))
        ];
    }
    
    /**
     * 获取数据库版本
     * 
     * @return string 数据库版本
     */
    private function getDatabaseVersion()
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT VERSION() as version");
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return $row['version'] ?? 'Unknown';
        } catch (\Exception $e) {
            Logger::error('获取数据库版本失败: ' . $e->getMessage());
            return 'Unknown';
        }
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