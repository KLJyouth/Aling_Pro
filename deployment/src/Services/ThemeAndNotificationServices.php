<?php

namespace AlingAi\Services;

use AlingAi\Database\DatabaseManager;
use AlingAi\Performance\CacheManager;

/**
 * 主题管理服务
 */
class ThemeManager
{
    private static $instance = null;
    private $db;
    private $cache;
    private $currentTheme = null;
    private $themesPath;
    
    private function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->cache = CacheManager::getInstance();
        $this->themesPath = dirname(__DIR__, 2) . '/resources/themes';
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 获取当前主题
     */
    public function getCurrentTheme(): array
    {
        if ($this->currentTheme === null) {
            $this->currentTheme = $this->cache->remember('current_theme', 3600, function() {
                $stmt = $this->db->getConnection()->prepare(
                    "SELECT * FROM themes WHERE is_active = 1 LIMIT 1"
                );
                $stmt->execute();
                $theme = $stmt->fetch();
                
                return $theme ?: $this->getDefaultTheme();
            });
        }
        
        return $this->currentTheme;
    }
    
    /**
     * 设置当前主题
     */
    public function setCurrentTheme(string $themeId): bool
    {
        try {
            $this->db->getConnection()->beginTransaction();
            
            // 取消所有主题的激活状态
            $stmt = $this->db->getConnection()->prepare(
                "UPDATE themes SET is_active = 0"
            );
            $stmt->execute();
            
            // 激活指定主题
            $stmt = $this->db->getConnection()->prepare(
                "UPDATE themes SET is_active = 1 WHERE id = ?"
            );
            $stmt->execute([$themeId]);
            
            $this->db->getConnection()->commit();
            
            // 清除缓存
            $this->cache->delete('current_theme');
            $this->currentTheme = null;
            
            return true;
        } catch (\Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log("主题切换失败: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取所有可用主题
     */
    public function getAvailableThemes(): array
    {
        return $this->cache->remember('available_themes', 1800, function() {
            $stmt = $this->db->getConnection()->prepare(
                "SELECT * FROM themes ORDER BY name"
            );
            $stmt->execute();
            return $stmt->fetchAll();
        });
    }
    
    /**
     * 安装主题
     */
    public function installTheme(array $themeData): bool
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "INSERT INTO themes (name, description, version, author, config, styles, is_active, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, 0, NOW())"
            );
            
            $stmt->execute([
                $themeData['name'],
                $themeData['description'] ?? '',
                $themeData['version'] ?? '1.0.0',
                $themeData['author'] ?? '',
                json_encode($themeData['config'] ?? []),
                json_encode($themeData['styles'] ?? [])
            ]);
            
            // 清除缓存
            $this->cache->delete('available_themes');
            
            return true;
        } catch (\Exception $e) {
            error_log("主题安装失败: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取主题配置
     */
    public function getThemeConfig(string $themeId = null): array
    {
        $theme = $themeId ? $this->getThemeById($themeId) : $this->getCurrentTheme();
        
        if (!$theme || !$theme['config']) {
            return $this->getDefaultConfig();
        }
        
        return json_decode($theme['config'], true) ?: $this->getDefaultConfig();
    }
    
    /**
     * 更新主题配置
     */
    public function updateThemeConfig(string $themeId, array $config): bool
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "UPDATE themes SET config = ?, updated_at = NOW() WHERE id = ?"
            );
            
            $stmt->execute([
                json_encode($config),
                $themeId
            ]);
            
            // 清除相关缓存
            $this->cache->delete('current_theme');
            $this->cache->delete('available_themes');
            $this->cache->delete("theme_config_{$themeId}");
            
            return true;
        } catch (\Exception $e) {
            error_log("主题配置更新失败: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取主题样式
     */
    public function getThemeStyles(string $themeId = null): array
    {
        $theme = $themeId ? $this->getThemeById($themeId) : $this->getCurrentTheme();
        
        if (!$theme || !$theme['styles']) {
            return $this->getDefaultStyles();
        }
        
        return json_decode($theme['styles'], true) ?: $this->getDefaultStyles();
    }
    
    /**
     * 生成主题CSS
     */
    public function generateThemeCSS(string $themeId = null): string
    {
        $styles = $this->getThemeStyles($themeId);
        $config = $this->getThemeConfig($themeId);
        
        $css = ":root {\n";
        
        // 颜色变量
        if (isset($styles['colors'])) {
            foreach ($styles['colors'] as $name => $value) {
                $css .= "  --color-{$name}: {$value};\n";
            }
        }
        
        // 字体变量
        if (isset($styles['fonts'])) {
            foreach ($styles['fonts'] as $name => $value) {
                $css .= "  --font-{$name}: {$value};\n";
            }
        }
        
        // 间距变量
        if (isset($styles['spacing'])) {
            foreach ($styles['spacing'] as $name => $value) {
                $css .= "  --spacing-{$name}: {$value};\n";
            }
        }
        
        $css .= "}\n\n";
        
        // 自定义CSS
        if (isset($config['custom_css'])) {
            $css .= $config['custom_css'];
        }
        
        return $css;
    }
    
    /**
     * 导出主题
     */
    public function exportTheme(string $themeId): array
    {
        $theme = $this->getThemeById($themeId);
        
        if (!$theme) {
            throw new \Exception("主题不存在");
        }
        
        return [
            'name' => $theme['name'],
            'description' => $theme['description'],
            'version' => $theme['version'],
            'author' => $theme['author'],
            'config' => json_decode($theme['config'], true),
            'styles' => json_decode($theme['styles'], true),
            'exported_at' => date('Y-m-d H:i:s')
        ];
    }
    
    private function getThemeById(string $themeId): ?array
    {
        $stmt = $this->db->getConnection()->prepare(
            "SELECT * FROM themes WHERE id = ?"
        );
        $stmt->execute([$themeId]);
        return $stmt->fetch() ?: null;
    }
    
    private function getDefaultTheme(): array
    {
        return [
            'id' => 'default',
            'name' => '默认主题',
            'description' => '系统默认主题',
            'version' => '1.0.0',
            'author' => 'AlingAi',
            'config' => json_encode($this->getDefaultConfig()),
            'styles' => json_encode($this->getDefaultStyles()),
            'is_active' => 1
        ];
    }
    
    private function getDefaultConfig(): array
    {
        return [
            'layout' => 'default',
            'sidebar_position' => 'left',
            'header_style' => 'fixed',
            'footer_style' => 'static',
            'animation_enabled' => true,
            'dark_mode' => false
        ];
    }
    
    private function getDefaultStyles(): array
    {
        return [
            'colors' => [
                'primary' => '#007bff',
                'secondary' => '#6c757d',
                'success' => '#28a745',
                'danger' => '#dc3545',
                'warning' => '#ffc107',
                'info' => '#17a2b8',
                'light' => '#f8f9fa',
                'dark' => '#343a40',
                'background' => '#ffffff',
                'text' => '#212529'
            ],
            'fonts' => [
                'primary' => "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                'secondary' => "'Times New Roman', Times, serif",
                'monospace' => "'Courier New', Courier, monospace"
            ],
            'spacing' => [
                'xs' => '0.25rem',
                'sm' => '0.5rem',
                'md' => '1rem',
                'lg' => '1.5rem',
                'xl' => '3rem'
            ]
        ];
    }
}

/**
 * 通知服务
 */
class NotificationService
{
    private static $instance = null;
    private $db;
    private $cache;
    
    private function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->cache = CacheManager::getInstance();
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 发送通知
     */
    public function send(array $notification): bool
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "INSERT INTO notifications (user_id, type, title, content, data, created_at) 
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            
            $stmt->execute([
                $notification['user_id'],
                $notification['type'],
                $notification['title'],
                $notification['content'],
                json_encode($notification['data'] ?? [])
            ]);
            
            $notificationId = $this->db->getConnection()->lastInsertId();
            
            // 清除用户通知缓存
            $this->cache->delete("user_notifications_{$notification['user_id']}");
            
            // 发送实时通知（WebSocket）
            $this->sendRealTimeNotification($notification['user_id'], $notificationId);
            
            // 发送邮件通知（如果需要）
            if (isset($notification['send_email']) && $notification['send_email']) {
                $this->sendEmailNotification($notification);
            }
            
            return true;
        } catch (\Exception $e) {
            error_log("通知发送失败: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取用户通知
     */
    public function getUserNotifications(int $userId, int $limit = 20, int $offset = 0): array
    {
        $cacheKey = "user_notifications_{$userId}_{$limit}_{$offset}";
        
        return $this->cache->remember($cacheKey, 300, function() use ($userId, $limit, $offset) {
            $stmt = $this->db->getConnection()->prepare(
                "SELECT * FROM notifications WHERE user_id = ? 
                 ORDER BY created_at DESC LIMIT ? OFFSET ?"
            );
            $stmt->execute([$userId, $limit, $offset]);
            return $stmt->fetchAll();
        });
    }
    
    /**
     * 获取未读通知数量
     */
    public function getUnreadCount(int $userId): int
    {
        $cacheKey = "unread_notifications_{$userId}";
        
        return $this->cache->remember($cacheKey, 60, function() use ($userId) {
            $stmt = $this->db->getConnection()->prepare(
                "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0"
            );
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            return (int)$result['count'];
        });
    }
    
    /**
     * 标记通知为已读
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "UPDATE notifications SET is_read = 1, read_at = NOW() 
                 WHERE id = ? AND user_id = ?"
            );
            $stmt->execute([$notificationId, $userId]);
            
            // 清除相关缓存
            $this->cache->delete("user_notifications_{$userId}");
            $this->cache->delete("unread_notifications_{$userId}");
            
            return true;
        } catch (\Exception $e) {
            error_log("标记通知已读失败: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 标记所有通知为已读
     */
    public function markAllAsRead(int $userId): bool
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "UPDATE notifications SET is_read = 1, read_at = NOW() 
                 WHERE user_id = ? AND is_read = 0"
            );
            $stmt->execute([$userId]);
            
            // 清除相关缓存
            $this->cache->delete("user_notifications_{$userId}");
            $this->cache->delete("unread_notifications_{$userId}");
            
            return true;
        } catch (\Exception $e) {
            error_log("标记所有通知已读失败: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 删除通知
     */
    public function deleteNotification(int $notificationId, int $userId): bool
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "DELETE FROM notifications WHERE id = ? AND user_id = ?"
            );
            $stmt->execute([$notificationId, $userId]);
            
            // 清除相关缓存
            $this->cache->delete("user_notifications_{$userId}");
            $this->cache->delete("unread_notifications_{$userId}");
            
            return true;
        } catch (\Exception $e) {
            error_log("删除通知失败: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 批量发送通知
     */
    public function sendBulk(array $userIds, array $notification): bool
    {
        try {
            $this->db->getConnection()->beginTransaction();
            
            $stmt = $this->db->getConnection()->prepare(
                "INSERT INTO notifications (user_id, type, title, content, data, created_at) 
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            
            foreach ($userIds as $userId) {
                $stmt->execute([
                    $userId,
                    $notification['type'],
                    $notification['title'],
                    $notification['content'],
                    json_encode($notification['data'] ?? [])
                ]);
                
                // 清除用户通知缓存
                $this->cache->delete("user_notifications_{$userId}");
            }
            
            $this->db->getConnection()->commit();
            
            return true;
        } catch (\Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log("批量通知发送失败: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 发送实时通知（WebSocket）
     */
    private function sendRealTimeNotification(int $userId, int $notificationId): void
    {
        // 这里可以集成WebSocket服务，如ReactPHP或Swoole
        // 暂时记录日志
        error_log("实时通知发送: 用户{$userId}, 通知{$notificationId}");
    }
    
    /**
     * 发送邮件通知
     */
    private function sendEmailNotification(array $notification): void
    {
        // 这里可以集成邮件服务，如SwiftMailer或PHPMailer
        // 暂时记录日志
        error_log("邮件通知发送: " . json_encode($notification));
    }
}
