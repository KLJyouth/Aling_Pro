<?php

declare(strict_types=1);

namespace AlingAi\Models;

use AlingAi\Models\BaseModel;
use AlingAi\Models\User;

/**
 * API令牌模型
 * 
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $token
 * @property array $abilities
 * @property array $metadata
 * @property int $usage_count
 * @property string|null $last_used_at
 * @property string|null $expires_at
 * @property string $created_at
 * @property string $updated_at
 * 
 * @property-read User $user
 */
class ApiToken extends BaseModel
{
    protected $table = 'api_tokens';

    protected $fillable = [
        'user_id',
        'name',
        'token',
        'abilities',
        'metadata',
        'usage_count',
        'last_used_at',
        'expires_at'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'abilities' => 'array',
        'metadata' => 'array',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $hidden = [
        'token'
    ];

    protected $attributes = [
        'abilities' => '[]',
        'metadata' => '[]',
        'usage_count' => 0
    ];

    // 令牌能力常量
    public const ABILITY_READ = 'read';
    public const ABILITY_WRITE = 'write';
    public const ABILITY_DELETE = 'delete';
    public const ABILITY_ADMIN = 'admin';

    // 资源类型常量
    public const RESOURCE_USERS = 'users';
    public const RESOURCE_CONVERSATIONS = 'conversations';
    public const RESOURCE_DOCUMENTS = 'documents';
    public const RESOURCE_LOGS = 'logs';

    /**
     * 关联到用户
     */
    public function user()
    {
        // 模拟关联关系
        $user = new User();
        return $user->where('id', $this->user_id)->first();
    }

    /**
     * 检查令牌是否有效
     */
    public function isValid(): bool
    {
        return !$this->isExpired();
    }

    /**
     * 检查令牌是否已过期
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        $expiresAt = new \DateTime($this->expires_at);
        return $expiresAt < new \DateTime();
    }

    /**
     * 检查是否具有指定能力
     */
    public function hasAbility(string $ability, string $resource = '*'): bool
    {
        $abilities = $this->abilities ?? [];

        // 检查是否有全局管理员权限
        if (in_array('*', $abilities)) {
            return true;
        }

        // 检查是否有指定资源的全部权限
        if (in_array("{$resource}:*", $abilities)) {
            return true;
        }

        // 检查是否有指定资源的指定能力
        if (in_array("{$resource}:{$ability}", $abilities)) {
            return true;
        }

        // 检查是否有全局指定能力
        if (in_array("*:{$ability}", $abilities)) {
            return true;
        }

        return false;
    }

    /**
     * 检查是否可以读取指定资源
     */
    public function canRead(string $resource = '*'): bool
    {
        return $this->hasAbility(self::ABILITY_READ, $resource);
    }

    /**
     * 检查是否可以写入指定资源
     */
    public function canWrite(string $resource = '*'): bool
    {
        return $this->hasAbility(self::ABILITY_WRITE, $resource);
    }

    /**
     * 检查是否可以删除指定资源
     */
    public function canDelete(string $resource = '*'): bool
    {
        return $this->hasAbility(self::ABILITY_DELETE, $resource);
    }

    /**
     * 检查是否有管理员权限
     */
    public function canAdmin(string $resource = '*'): bool
    {
        return $this->hasAbility(self::ABILITY_ADMIN, $resource);
    }

    /**
     * 记录令牌使用
     */
    public function recordUsage(): self
    {
        $this->usage_count = ($this->usage_count ?? 0) + 1;
        $this->last_used_at = date('Y-m-d H:i:s');
        $this->save();
        return $this;
    }

    /**
     * 获取令牌的显示值（部分隐藏）
     */
    public function getDisplayToken(): string
    {
        if (strlen($this->token) < 16) {
            return $this->token;
        }

        return substr($this->token, 0, 8) . '...' . substr($this->token, -8);
    }

    /**
     * 获取格式化的能力列表
     */
    public function getFormattedAbilities(): array
    {
        $abilities = $this->abilities ?? [];
        $formatted = [];

        foreach ($abilities as $ability) {
            if ($ability === '*') {
                $formatted[] = '全部权限';
                continue;
            }

            if (strpos($ability, ':') === false) {
                $formatted[] = $ability;
                continue;
            }
            
            list($resource, $action) = explode(':', $ability, 2);

            switch($resource) {
                case self::RESOURCE_USERS:
                    $resourceName = '用户';
                    break;
                case self::RESOURCE_CONVERSATIONS:
                    $resourceName = '对话';
                    break;
                case self::RESOURCE_DOCUMENTS:
                    $resourceName = '文档';
                    break;
                case self::RESOURCE_LOGS:
                    $resourceName = '日志';
                    break;
                case '*':
                    $resourceName = '全部资源';
                    break;
                default:
                    $resourceName = $resource;
                    break;
            }

            switch($action) {
                case self::ABILITY_READ:
                    $actionName = '读取';
                    break;
                case self::ABILITY_WRITE:
                    $actionName = '写入';
                    break;
                case self::ABILITY_DELETE:
                    $actionName = '删除';
                    break;
                case self::ABILITY_ADMIN:
                    $actionName = '管理';
                    break;
                case '*':
                    $actionName = '全部操作';
                    break;
                default:
                    $actionName = $action;
                    break;
            }

            $formatted[] = "{$resourceName}:{$actionName}";
        }

        return $formatted;
    }

    /**
     * 生成新的API令牌
     */
    public static function generate(
        int $userId,
        string $name,
        array $abilities = [],
        ?int $expireMinutes = null
    ): array {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        
        $apiToken = new static();
        $apiToken->fill([
            'user_id' => $userId,
            'name' => $name,
            'token' => $hashedToken,
            'abilities' => $abilities,
            'expires_at' => $expireMinutes ? date('Y-m-d H:i:s', time() + ($expireMinutes * 60)) : null,
        ]);
        $apiToken->save();
        
        return [
            'token' => $token,
            'api_token' => $apiToken
        ];
    }

    /**
     * 通过令牌查找API令牌
     */
    public static function findByToken(string $token): ?self
    {
        $hashedToken = hash('sha256', $token);
        $apiToken = new static();
        return $apiToken->where('token', $hashedToken)->first();
    }

    /**
     * 撤销令牌
     */
    public function revoke(): bool
    {
        return $this->delete();
    }

    /**
     * 更新最后使用时间
     */
    public function touch(): bool
    {
        $this->last_used_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * 清理过期令牌
     */
    public static function cleanupExpired(): int
    {
        // 这里可以实现清理逻辑
        return 0;
    }

    /**
     * 获取令牌统计信息
     */
    public static function getStatistics(): array
    {
        return [
            'total' => 0, // 简化实现
            'valid' => 0,
            'expired' => 0,
            'recently_used' => 0,
            'unused' => 0,
            'today' => 0,
            'this_week' => 0,
            'this_month' => 0,
            'total_usage' => 0,
        ];
    }

    /**
     * 检查用户令牌数量限制
     */
    public static function checkUserTokenLimit(int $userId, int $limit = 10): bool
    {
        $apiToken = new static();
        $count = $apiToken->where('user_id', $userId)->count();
        return $count < $limit;
    }

    /**
     * 获取预定义的能力集合
     */
    public static function getAbilityPresets(): array
    {
        return [
            'readonly' => [
                'users:read',
                'conversations:read',
                'documents:read'
            ],
            'editor' => [
                'users:read',
                'conversations:*',
                'documents:*'
            ],
            'admin' => [
                '*'
            ],
            'api_user' => [
                'conversations:read',
                'conversations:write',
                'documents:read',
                'documents:write'
            ]
        ];
    }
}
