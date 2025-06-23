<?php

namespace AlingAi\Security;

use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Exception;
use InvalidArgumentException;

/**
 * JWT管理工具类
 *
 * 封装JWT的生成和验证
 *
 * @package AlingAi\Security
 * @version 6.0.0
 */
class JWTManager
{
    private string $secretKey;
    private string $algorithm;
    private int $expiresIn;
    private ?LoggerInterface $logger;
    
    /**
     * 构造函数
     *
     * @param string $secretKey 密钥
     * @param LoggerInterface|null $logger 日志记录器
     */
    public function __construct(string $secretKey, ?LoggerInterface $logger = null)
    {
        if (empty($secretKey)) {
            throw new InvalidArgumentException("JWT secret key cannot be empty.");
        }
        $this->secretKey = $secretKey;
        $this->logger = $logger;
        $this->algorithm = $_ENV['JWT_ALGORITHM'] ?? 'HS256';
        $this->expiresIn = (int)($_ENV['JWT_EXPIRES_IN'] ?? 3600); // 默认1小时
    }

    /**
     * 生成令牌
     *
     * @param array $payload 载荷数据
     * @param int|null $expiresIn 自定义过期时间（秒）
     * @return string 令牌
     */
    public function generateToken(array $payload, ?int $expiresIn = null): string
    {
        $payload['exp'] = time() + ($expiresIn ?? $this->expiresIn);
        $payload['iat'] = time();
        
        try {
            $token = FirebaseJWT::encode($payload, $this->secretKey, $this->algorithm);
            
            if ($this->logger) {
                $this->logger->debug('JWT令牌已生成', [
                    'user_id' => $payload['sub'] ?? null,
                    'expires_in' => $expiresIn ?? $this->expiresIn
                ]);
            }
            
            return $token;
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('JWT令牌生成失败', [
                    'error' => $e->getMessage()
                ]);
            }
            
            throw new RuntimeException('生成令牌失败: ' . $e->getMessage());
        }
    }

    /**
     * 解码令牌
     *
     * @param string $token 令牌
     * @return object 解码后的载荷
     * @throws RuntimeException 如果令牌无效
     */
    public function decodeToken(string $token): object
    {
        try {
            $decoded = FirebaseJWT::decode($token, new Key($this->secretKey, $this->algorithm));
            
            if ($this->logger) {
                $this->logger->debug('JWT令牌已解码', [
                    'user_id' => $decoded->sub ?? null
                ]);
            }
            
            return $decoded;
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->warning('JWT令牌解码失败', [
                    'error' => $e->getMessage(),
                    'token' => substr($token, 0, 10) . '...'
                ]);
            }
            
            throw new RuntimeException('令牌验证失败: ' . $e->getMessage());
        }
    }

    /**
     * 验证令牌
     *
     * @param string $token 令牌
     * @return bool 是否有效
     */
    public function validateToken(string $token): bool
    {
        try {
            $this->decodeToken($token);
            return true;
        } catch (RuntimeException $e) {
            return false;
        }
    }

    /**
     * 获取令牌过期时间
     *
     * @return int 过期时间（秒）
     */
    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    /**
     * 设置令牌过期时间
     *
     * @param int $expiresIn 过期时间（秒）
     * @return self
     */
    public function setExpiresIn(int $expiresIn): self
    {
        $this->expiresIn = $expiresIn;
        return $this;
    }

    /**
     * 设置加密算法
     *
     * @param string $algorithm 算法
     * @return self
     */
    public function setAlgorithm(string $algorithm): self
    {
        $this->algorithm = $algorithm;
        return $this;
    }

    /**
     * 从请求头提取令牌
     *
     * @param string $header Authorization header content
     * @param string $prefix Prefix, default 'Bearer '
     * @return string|null Token or null
     */
    public function extractTokenFromHeader(string $header, string $prefix = 'Bearer '): ?string
    {
        if (empty($header)) {
            return null;
        }
        
        if (strpos($header, $prefix) === 0) {
            return trim(substr($header, strlen($prefix)));
        }
        
        return null;
    }
}
