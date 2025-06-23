<?php

declare(strict_types=1);

namespace AlingAi\Models;

use AlingAi\Models\BaseModel;

/**
 * 密码重置模型
 * 
 * @property int $id
 * @property string $email
 * @property string $token
 * @property bool $used
 * @property string|null $expires_at
 * @property string $created_at
 * @property string $updated_at
 * 
 * @property-read User|null $user
 */
class PasswordReset extends BaseModel
{
    protected $table = 'password_resets';

    protected $fillable = [
        'email',
        'token',
        'used',
        'expires_at'
    ];

    protected $casts = [
        'used' => 'boolean',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'used' => false
    ];    /**
     * 获取关联的用户（通过邮箱）
     */
    public function user(): ?User
    {
        return User::where('email', $this->email)->first();
    }

    /**
     * 检查令牌是否有效
     */
    public function isValid(): bool
    {
        return !$this->used && 
               $this->expires_at && 
               strtotime($this->expires_at) > time();
    }    /**
     * 检查令牌是否已过期
     */
    public function isExpired(): bool
    {
        return $this->expires_at && strtotime($this->expires_at) < time();
    }

    /**
     * 检查令牌是否已使用
     */
    public function isUsed(): bool
    {
        return $this->used;
    }

    /**
     * 标记令牌为已使用
     */
    public function markAsUsed(): self
    {
        $this->used = true;
        $this->save();
        return $this;
    }

    /**
     * 验证令牌
     */
    public function verify(string $token): bool
    {
        return $this->token === $token && $this->isValid();
    }    /**
     * 按邮箱过滤
     */
    public static function byEmail(string $email)
    {
        return static::where('email', $email);
    }

    /**
     * 按令牌过滤
     */
    public static function byToken(string $token)
    {
        return static::where('token', $token);
    }

    /**
     * 只显示有效的令牌
     */
    public static function valid()
    {
        return static::where('used', false)
                    ->where('expires_at', '>', date('Y-m-d H:i:s'));
    }

    /**
     * 只显示已过期的令牌
     */
    public static function expired()
    {
        return static::where('expires_at', '<', date('Y-m-d H:i:s'));
    }

    /**
     * 只显示已使用的令牌
     */
    public static function used()
    {
        return static::where('used', true);
    }

    /**
     * 最近创建的令牌
     */
    public static function recent(int $hours = 24)
    {
        $time = date('Y-m-d H:i:s', time() - ($hours * 3600));
        return static::where('created_at', '>=', $time);
    }    /**
     * 创建新的密码重置令牌
     */
    public static function createToken(string $email, int $expireMinutes = 60): self
    {
        // 删除该邮箱的旧令牌
        static::where('email', $email)->delete();

        // 创建新令牌
        return static::create([
            'email' => $email,
            'token' => static::generateToken(),
            'expires_at' => date('Y-m-d H:i:s', time() + ($expireMinutes * 60)),
            'used' => false
        ]);
    }

    /**
     * 生成随机令牌
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }    /**
     * 验证并获取密码重置记录
     */
    public static function validateToken(string $email, string $token): ?self
    {
        $reset = static::where('email', $email)
                      ->where('token', $token)
                      ->where('used', false)
                      ->where('expires_at', '>', date('Y-m-d H:i:s'))
                      ->first();

        return $reset;
    }

    /**
     * 使用令牌重置密码
     */
    public static function resetPassword(string $email, string $token, string $newPassword): bool
    {
        $reset = static::validateToken($email, $token);
        
        if (!$reset) {
            return false;
        }

        // 找到用户并更新密码
        $user = User::where('email', $email)->first();
        if (!$user) {
            return false;
        }

        $user->password = password_hash($newPassword, PASSWORD_ARGON2ID);
        $user->save();

        // 标记令牌为已使用
        $reset->markAsUsed();

        return true;
    }    /**
     * 清理过期的令牌
     */
    public static function cleanup(): int
    {
        $count = static::where('expires_at', '<', date('Y-m-d H:i:s'))->count();
        static::where('expires_at', '<', date('Y-m-d H:i:s'))->delete();
        return $count;
    }

    /**
     * 清理已使用的令牌
     */
    public static function cleanupUsed(int $days = 7): int
    {
        $cutoffDate = date('Y-m-d H:i:s', time() - ($days * 24 * 3600));
        $count = static::where('used', true)
                    ->where('updated_at', '<', $cutoffDate)
                    ->count();
        static::where('used', true)
                    ->where('updated_at', '<', $cutoffDate)
                    ->delete();
        return $count;
    }    /**
     * 获取统计信息
     */
    public static function getStatistics(): array
    {
        $today = date('Y-m-d');
        $thisWeekStart = date('Y-m-d', strtotime('monday this week'));
        $thisWeekEnd = date('Y-m-d', strtotime('sunday this week'));
        $thisMonth = date('Y-m');
        
        return [
            'total' => static::count(),
            'valid' => static::valid()->count(),
            'expired' => static::expired()->count(),
            'used' => static::used()->count(),
            'today' => static::where('created_at', '>=', $today . ' 00:00:00')
                            ->where('created_at', '<=', $today . ' 23:59:59')
                            ->count(),
            'this_week' => static::where('created_at', '>=', $thisWeekStart . ' 00:00:00')
                                ->where('created_at', '<=', $thisWeekEnd . ' 23:59:59')
                                ->count(),
            'this_month' => static::where('created_at', '>=', $thisMonth . '-01 00:00:00')
                                 ->where('created_at', '<=', $thisMonth . '-31 23:59:59')
                                 ->count(),
        ];
    }    /**
     * 检查邮箱的重置频率
     */
    public static function checkRateLimit(string $email, int $maxAttempts = 5, int $minutes = 60): bool
    {
        $cutoffTime = date('Y-m-d H:i:s', time() - ($minutes * 60));
        $attempts = static::where('email', $email)
                          ->where('created_at', '>=', $cutoffTime)
                          ->count();

        return $attempts < $maxAttempts;
    }
}
