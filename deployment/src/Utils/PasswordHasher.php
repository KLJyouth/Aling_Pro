<?php

namespace AlingAi\Utils;

use AlingAi\Exceptions\SecurityException;

/**
 * 密码哈希工具类
 * 提供安全的密码哈希和验证功能
 */
class PasswordHasher
{
    private const HASH_ALGORITHM = PASSWORD_ARGON2ID;
    private const MIN_PASSWORD_LENGTH = 8;
    private const MAX_PASSWORD_LENGTH = 128;
    
    // Argon2ID 参数配置
    private const MEMORY_COST = 65536;  // 64 MB
    private const TIME_COST = 4;        // 4 iterations
    private const THREADS = 3;          // 3 threads
    
    /**
     * 哈希密码
     */
    public static function hash(string $password): string
    {
        self::validatePassword($password);
        
        $options = [
            'memory_cost' => self::MEMORY_COST,
            'time_cost' => self::TIME_COST,
            'threads' => self::THREADS,
        ];
        
        $hash = password_hash($password, self::HASH_ALGORITHM, $options);
        
        if ($hash === false) {
            throw new SecurityException('密码哈希失败');
        }
        
        return $hash;
    }
    
    /**
     * 验证密码
     */
    public static function verify(string $password, string $hash): bool
    {
        if (empty($password) || empty($hash)) {
            return false;
        }
        
        return password_verify($password, $hash);
    }
    
    /**
     * 检查哈希是否需要重新哈希
     */
    public static function needsRehash(string $hash): bool
    {
        $options = [
            'memory_cost' => self::MEMORY_COST,
            'time_cost' => self::TIME_COST,
            'threads' => self::THREADS,
        ];
        
        return password_needs_rehash($hash, self::HASH_ALGORITHM, $options);
    }
    
    /**
     * 生成随机密码
     */
    public static function generateRandomPassword(int $length = 16): string
    {
        if ($length < self::MIN_PASSWORD_LENGTH || $length > self::MAX_PASSWORD_LENGTH) {
            throw new SecurityException('密码长度必须在 ' . self::MIN_PASSWORD_LENGTH . ' 到 ' . self::MAX_PASSWORD_LENGTH . ' 之间');
        }
        
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $password;
    }
    
    /**
     * 验证密码强度
     */
    public static function validatePasswordStrength(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            $errors[] = '密码长度至少为 ' . self::MIN_PASSWORD_LENGTH . ' 个字符';
        }
        
        if (strlen($password) > self::MAX_PASSWORD_LENGTH) {
            $errors[] = '密码长度不能超过 ' . self::MAX_PASSWORD_LENGTH . ' 个字符';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = '密码必须包含至少一个小写字母';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = '密码必须包含至少一个大写字母';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = '密码必须包含至少一个数字';
        }
        
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $password)) {
            $errors[] = '密码必须包含至少一个特殊字符';
        }
        
        // 检查常见弱密码
        $weakPasswords = [
            'password', '123456', '123456789', 'qwerty', 'abc123',
            'password123', 'admin', 'root', '111111', '000000'
        ];
        
        if (in_array(strtolower($password), $weakPasswords)) {
            $errors[] = '密码过于简单，请使用更复杂的密码';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'strength' => self::calculatePasswordStrength($password)
        ];
    }
    
    /**
     * 计算密码强度分数 (0-100)
     */
    private static function calculatePasswordStrength(string $password): int
    {
        $score = 0;
        $length = strlen($password);
        
        // 长度分数 (最高30分)
        if ($length >= 8) $score += 10;
        if ($length >= 12) $score += 10;
        if ($length >= 16) $score += 10;
        
        // 字符类型分数 (最高40分)
        if (preg_match('/[a-z]/', $password)) $score += 10;
        if (preg_match('/[A-Z]/', $password)) $score += 10;
        if (preg_match('/[0-9]/', $password)) $score += 10;
        if (preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $password)) $score += 10;
        
        // 复杂度分数 (最高30分)
        $uniqueChars = count(array_unique(str_split($password)));
        if ($uniqueChars >= 6) $score += 10;
        if ($uniqueChars >= 10) $score += 10;
        if ($uniqueChars >= 15) $score += 10;
        
        return min(100, $score);
    }
    
    /**
     * 验证密码基本要求
     */
    private static function validatePassword(string $password): void
    {
        if (empty($password)) {
            throw new SecurityException('密码不能为空');
        }
        
        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            throw new SecurityException('密码长度至少为 ' . self::MIN_PASSWORD_LENGTH . ' 个字符');
        }
        
        if (strlen($password) > self::MAX_PASSWORD_LENGTH) {
            throw new SecurityException('密码长度不能超过 ' . self::MAX_PASSWORD_LENGTH . ' 个字符');
        }
    }
    
    /**
     * 生成密码重置令牌
     */
    public static function generateResetToken(): string
    {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * 生成邮箱验证令牌
     */
    public static function generateEmailVerificationToken(): string
    {
        return bin2hex(random_bytes(16));
    }
}
