<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Logger;

/**
 * 数据库控制器
 * 负责处理数据库相关请求
 */
class DatabaseController extends Controller
{
    /**
     * 优化数据库
     */
    public function optimize()
    {
        try {
            $db = Database::getInstance();
            
            // 获取所有表
            $stmt = $db->query("SHOW TABLES");
            $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            $optimizedTables = [];
            $errors = [];
            
            // 优化每个表
            foreach ($tables as $table) {
                try {
                    $db->exec("OPTIMIZE TABLE `$table`");
                    $optimizedTables[] = $table;
                } catch (\Exception $e) {
                    $errors[$table] = $e->getMessage();
                }
            }
            
            // 记录日志
            Logger::info('数据库已优化', [
                'user_id' => $_SESSION['user_id'] ?? 0,
                'ip' => $_SERVER['REMOTE_ADDR'],
                'optimized_tables' => count($optimizedTables),
                'total_tables' => count($tables),
                'errors' => count($errors)
            ]);
            
            if (count($errors) > 0) {
                $_SESSION['flash_message'] = '数据库部分优化成功，' . count($optimizedTables) . ' 个表已优化，' . count($errors) . ' 个表失败';
                $_SESSION['flash_message_type'] = 'warning';
            } else {
                $_SESSION['flash_message'] = '数据库优化成功，共优化 ' . count($optimizedTables) . ' 个表';
                $_SESSION['flash_message_type'] = 'success';
            }
        } catch (\Exception $e) {
            // 记录错误
            Logger::error('数据库优化失败: ' . $e->getMessage());
            
            $_SESSION['flash_message'] = '数据库优化失败: ' . $e->getMessage();
            $_SESSION['flash_message_type'] = 'danger';
        }
        
        // 重定向回上一页或工具页
        $referer = $_SERVER['HTTP_REFERER'] ?? '/admin/tools';
        header('Location: ' . $referer);
        exit;
    }
    
    /**
     * 修复数据库
     */
    public function repair()
    {
        try {
            $db = Database::getInstance();
            
            // 获取所有表
            $stmt = $db->query("SHOW TABLES");
            $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            $repairedTables = [];
            $errors = [];
            
            // 修复每个表
            foreach ($tables as $table) {
                try {
                    $db->exec("REPAIR TABLE `$table`");
                    $repairedTables[] = $table;
                } catch (\Exception $e) {
                    $errors[$table] = $e->getMessage();
                }
            }
            
            // 记录日志
            Logger::info('数据库已修复', [
                'user_id' => $_SESSION['user_id'] ?? 0,
                'ip' => $_SERVER['REMOTE_ADDR'],
                'repaired_tables' => count($repairedTables),
                'total_tables' => count($tables),
                'errors' => count($errors)
            ]);
            
            if (count($errors) > 0) {
                $_SESSION['flash_message'] = '数据库部分修复成功，' . count($repairedTables) . ' 个表已修复，' . count($errors) . ' 个表失败';
                $_SESSION['flash_message_type'] = 'warning';
            } else {
                $_SESSION['flash_message'] = '数据库修复成功，共修复 ' . count($repairedTables) . ' 个表';
                $_SESSION['flash_message_type'] = 'success';
            }
        } catch (\Exception $e) {
            // 记录错误
            Logger::error('数据库修复失败: ' . $e->getMessage());
            
            $_SESSION['flash_message'] = '数据库修复失败: ' . $e->getMessage();
            $_SESSION['flash_message_type'] = 'danger';
        }
        
        // 重定向回上一页或工具页
        $referer = $_SERVER['HTTP_REFERER'] ?? '/admin/tools';
        header('Location: ' . $referer);
        exit;
    }
    
    /**
     * 分析数据库
     */
    public function analyze()
    {
        try {
            $db = Database::getInstance();
            
            // 获取所有表
            $stmt = $db->query("SHOW TABLES");
            $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            $analyzedTables = [];
            $errors = [];
            
            // 分析每个表
            foreach ($tables as $table) {
                try {
                    $db->exec("ANALYZE TABLE `$table`");
                    $analyzedTables[] = $table;
                } catch (\Exception $e) {
                    $errors[$table] = $e->getMessage();
                }
            }
            
            // 记录日志
            Logger::info('数据库已分析', [
                'user_id' => $_SESSION['user_id'] ?? 0,
                'ip' => $_SERVER['REMOTE_ADDR'],
                'analyzed_tables' => count($analyzedTables),
                'total_tables' => count($tables),
                'errors' => count($errors)
            ]);
            
            if (count($errors) > 0) {
                $_SESSION['flash_message'] = '数据库部分分析成功，' . count($analyzedTables) . ' 个表已分析，' . count($errors) . ' 个表失败';
                $_SESSION['flash_message_type'] = 'warning';
            } else {
                $_SESSION['flash_message'] = '数据库分析成功，共分析 ' . count($analyzedTables) . ' 个表';
                $_SESSION['flash_message_type'] = 'success';
            }
        } catch (\Exception $e) {
            // 记录错误
            Logger::error('数据库分析失败: ' . $e->getMessage());
            
            $_SESSION['flash_message'] = '数据库分析失败: ' . $e->getMessage();
            $_SESSION['flash_message_type'] = 'danger';
        }
        
        // 重定向回上一页或工具页
        $referer = $_SERVER['HTTP_REFERER'] ?? '/admin/tools';
        header('Location: ' . $referer);
        exit;
    }
} 