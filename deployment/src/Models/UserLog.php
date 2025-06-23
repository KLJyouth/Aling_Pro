<?php

declare(strict_types=1);

namespace AlingAi\Models;

use AlingAi\Models\BaseModel;
use AlingAi\Models\User;

/**
 * 用户日志模型
 * 
 * @property int $id
 * @property int $user_id
 * @property string $action
 * @property string $description
 * @property string $level
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array|null $data
 * @property string $created_at
 * @property string $updated_at
 * 
 * @property-read User $user
 */
class UserLog extends BaseModel
{
    protected $table = 'user_logs';

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'level',
        'ip_address',
        'user_agent',
        'data'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'level' => 'info',
        'data' => '[]'
    ];

    // 日志级别常量
    public const LEVEL_DEBUG = 'debug';
    public const LEVEL_INFO = 'info';
    public const LEVEL_NOTICE = 'notice';
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_ERROR = 'error';
    public const LEVEL_CRITICAL = 'critical';
    public const LEVEL_ALERT = 'alert';
    public const LEVEL_EMERGENCY = 'emergency';

    // 常见操作类型
    public const ACTION_LOGIN = 'login';
    public const ACTION_LOGOUT = 'logout';
    public const ACTION_REGISTER = 'register';
    public const ACTION_PASSWORD_CHANGE = 'password_change';
    public const ACTION_PASSWORD_RESET = 'password_reset';
    public const ACTION_EMAIL_VERIFY = 'email_verify';
    public const ACTION_PROFILE_UPDATE = 'profile_update';
    public const ACTION_USER_CREATED = 'user_created';
    public const ACTION_USER_UPDATED = 'user_updated';
    public const ACTION_USER_DELETED = 'user_deleted';
    public const ACTION_CONVERSATION_CREATED = 'conversation_created';
    public const ACTION_CONVERSATION_UPDATED = 'conversation_updated';
    public const ACTION_CONVERSATION_DELETED = 'conversation_deleted';
    public const ACTION_DOCUMENT_CREATED = 'document_created';
    public const ACTION_DOCUMENT_UPDATED = 'document_updated';
    public const ACTION_DOCUMENT_DELETED = 'document_deleted';
    public const ACTION_DOCUMENT_DOWNLOADED = 'document_downloaded';
    public const ACTION_CONFIG_UPDATED = 'config_updated';
    public const ACTION_CACHE_CLEARED = 'cache_cleared';
    public const ACTION_DATABASE_MAINTENANCE = 'database_maintenance';
    public const ACTION_NOTIFICATION_SENT = 'notification_sent';    /**
     * 关联到用户 - 简化版本
     */
    public function user()
    {
        // 简化的关联，不使用Eloquent的BelongsTo
        if ($this->user_id) {
            return User::find($this->user_id);
        }
        return null;
    }    /**
     * 获取格式化的日志级别
     */
    public function getFormattedLevelAttribute(): string
    {
        switch($this->level) {
            case self::LEVEL_DEBUG:
                return '调试';
            case self::LEVEL_INFO:
                return '信息';
            case self::LEVEL_NOTICE:
                return '通知';
            case self::LEVEL_WARNING:
                return '警告';
            case self::LEVEL_ERROR:
                return '错误';
            case self::LEVEL_CRITICAL:
                return '严重';
            case self::LEVEL_ALERT:
                return '警报';
            case self::LEVEL_EMERGENCY:
                return '紧急';
            default:
                return '未知';
        }
    }    /**
     * 获取格式化的操作类型
     */
    public function getFormattedActionAttribute(): string
    {
        switch($this->action) {
            case self::ACTION_LOGIN:
                return '用户登录';
            case self::ACTION_LOGOUT:
                return '用户退出';
            case self::ACTION_REGISTER:
                return '用户注册';
            case self::ACTION_PASSWORD_CHANGE:
                return '密码修改';
            case self::ACTION_PASSWORD_RESET:
                return '密码重置';
            case self::ACTION_EMAIL_VERIFY:
                return '邮箱验证';
            case self::ACTION_PROFILE_UPDATE:
                return '资料更新';
            case self::ACTION_USER_CREATED:
                return '创建用户';
            case self::ACTION_USER_UPDATED:
                return '更新用户';
            case self::ACTION_USER_DELETED:
                return '删除用户';
            case self::ACTION_CONVERSATION_CREATED:
                return '创建对话';
            case self::ACTION_CONVERSATION_UPDATED:
                return '更新对话';
            case self::ACTION_CONVERSATION_DELETED:
                return '删除对话';
            case self::ACTION_DOCUMENT_CREATED:
                return '创建文档';
            case self::ACTION_DOCUMENT_UPDATED:
                return '更新文档';
            case self::ACTION_DOCUMENT_DELETED:
                return '删除文档';
            case self::ACTION_DOCUMENT_DOWNLOADED:
                return '下载文档';
            case self::ACTION_CONFIG_UPDATED:
                return '更新配置';
            case self::ACTION_CACHE_CLEARED:
                return '清理缓存';
            case self::ACTION_DATABASE_MAINTENANCE:
                return '数据库维护';
            case self::ACTION_NOTIFICATION_SENT:
                return '发送通知';
            default:
                return $this->action;
        }
    }    /**
     * 获取日志级别的颜色类
     */
    public function getLevelColorAttribute(): string
    {
        switch($this->level) {
            case self::LEVEL_DEBUG:
                return 'text-gray-500';
            case self::LEVEL_INFO:
                return 'text-blue-500';
            case self::LEVEL_NOTICE:
                return 'text-green-500';
            case self::LEVEL_WARNING:
                return 'text-yellow-500';
            case self::LEVEL_ERROR:
                return 'text-red-500';
            case self::LEVEL_CRITICAL:
                return 'text-red-700';
            case self::LEVEL_ALERT:
                return 'text-red-800';
            case self::LEVEL_EMERGENCY:
                return 'text-red-900';
            default:
                return 'text-gray-500';
        }
    }

    /**
     * 检查是否为安全相关的日志
     */
    public function isSecurityRelated(): bool
    {
        $securityActions = [
            self::ACTION_LOGIN,
            self::ACTION_LOGOUT,
            self::ACTION_REGISTER,
            self::ACTION_PASSWORD_CHANGE,
            self::ACTION_PASSWORD_RESET,
            self::ACTION_EMAIL_VERIFY
        ];

        return in_array($this->action, $securityActions);
    }

    /**
     * 检查是否为管理操作
     */
    public function isAdminAction(): bool
    {
        $adminActions = [
            self::ACTION_USER_CREATED,
            self::ACTION_USER_UPDATED,
            self::ACTION_USER_DELETED,
            self::ACTION_CONFIG_UPDATED,
            self::ACTION_CACHE_CLEARED,
            self::ACTION_DATABASE_MAINTENANCE,
            self::ACTION_NOTIFICATION_SENT
        ];

        return in_array($this->action, $adminActions) || 
               str_contains($this->action, 'batch_');
    }

    /**
     * 按日志级别过滤
     */
    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    /**
     * 按操作类型过滤
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * 按用户过滤
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * 按IP地址过滤
     */
    public function scopeByIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * 只显示安全相关日志
     */
    public function scopeSecurityRelated($query)
    {
        return $query->whereIn('action', [
            self::ACTION_LOGIN,
            self::ACTION_LOGOUT,
            self::ACTION_REGISTER,
            self::ACTION_PASSWORD_CHANGE,
            self::ACTION_PASSWORD_RESET,
            self::ACTION_EMAIL_VERIFY
        ]);
    }

    /**
     * 只显示管理操作日志
     */
    public function scopeAdminActions($query)
    {
        return $query->whereIn('action', [
            self::ACTION_USER_CREATED,
            self::ACTION_USER_UPDATED,
            self::ACTION_USER_DELETED,
            self::ACTION_CONFIG_UPDATED,
            self::ACTION_CACHE_CLEARED,
            self::ACTION_DATABASE_MAINTENANCE,
            self::ACTION_NOTIFICATION_SENT
        ])->orWhere('action', 'LIKE', 'batch_%');
    }

    /**
     * 按时间范围过滤
     */
    public function scopeInTimeRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * 今天的日志
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * 本周的日志
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * 本月的日志
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month);
    }

    /**
     * 错误级别及以上的日志
     */
    public function scopeErrors($query)
    {
        return $query->whereIn('level', [
            self::LEVEL_ERROR,
            self::LEVEL_CRITICAL,
            self::LEVEL_ALERT,
            self::LEVEL_EMERGENCY
        ]);
    }

    /**
     * 搜索日志
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('action', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%")
              ->orWhere('ip_address', 'LIKE', "%{$search}%")
              ->orWhere('user_agent', 'LIKE', "%{$search}%");
        });
    }

    /**
     * 创建日志记录的静态方法
     */
    public static function log(
        int $userId,
        string $action,
        string $description,
        string $level = self::LEVEL_INFO,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        array $data = []
    ): self {
        return static::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'level' => $level,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'data' => $data
        ]);
    }

    /**
     * 创建信息级别日志
     */
    public static function info(
        int $userId,
        string $action,
        string $description,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        array $data = []
    ): self {
        return static::log($userId, $action, $description, self::LEVEL_INFO, $ipAddress, $userAgent, $data);
    }

    /**
     * 创建警告级别日志
     */
    public static function warning(
        int $userId,
        string $action,
        string $description,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        array $data = []
    ): self {
        return static::log($userId, $action, $description, self::LEVEL_WARNING, $ipAddress, $userAgent, $data);
    }

    /**
     * 创建错误级别日志
     */
    public static function error(
        int $userId,
        string $action,
        string $description,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        array $data = []
    ): self {
        return static::log($userId, $action, $description, self::LEVEL_ERROR, $ipAddress, $userAgent, $data);
    }    /**
     * 获取日志统计信息 - 简化版本
     */
    public static function getStatistics(): array
    {
        $total = static::count();
        
        return [
            'total' => $total,
            'today' => static::whereDate('created_at', date('Y-m-d'))->count(),
            'this_week' => static::whereBetween('created_at', [
                date('Y-m-d', strtotime('monday this week')),
                date('Y-m-d', strtotime('sunday this week'))
            ])->count(),
            'this_month' => static::whereMonth('created_at', date('m'))->count(),
        ];
    }

    /**
     * 清理过期日志 - 简化版本
     */    public static function cleanup(int $days = 30): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        // 使用原生SQL删除
        if (!self::$databaseService) {
            // 创建一个简单的Monolog Logger实例
            $logger = new \Monolog\Logger('database');
            $logger->pushHandler(new \Monolog\Handler\NullHandler());
            self::$databaseService = new \AlingAi\Services\DatabaseService($logger);
        }
        
        $sql = "DELETE FROM user_logs WHERE created_at < ?";
        return self::$databaseService->execute($sql, [$cutoffDate]) ? 1 : 0;
    }
}
