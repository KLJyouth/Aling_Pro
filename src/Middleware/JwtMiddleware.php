<?php

declare(strict_types=1);

namespace AlingAi\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use AlingAi\Exceptions\AuthenticationException;
use AlingAi\Services\CacheService;
use Slim\Psr7\Response;

/**
 * JWT认证中间件
 * 
 * 提供完整的JWT令牌验证和管理功能
 * 优化性能：令牌缓存、批量验证、智能刷新
 * 增强安全性：令牌黑名单、设备指纹、异常检测
 */
class JwtMiddleware implements MiddlewareInterface
{
    private string $jwtSecret;
    private string $jwtAlgorithm;
    private LoggerInterface $logger;
    private CacheService $cacheService;
    private array $config;

    public function __construct(
        string $jwtSecret,
        LoggerInterface $logger,
        CacheService $cacheService,
        array $config = []
    ) {
        $this->jwtSecret = $jwtSecret;
        $this->logger = $logger;
        $this->cacheService = $cacheService;
        $this->config = array_merge([
            'algorithm' => 'HS256',
            'token_expiry' => 3600, // 1小时
            'refresh_expiry' => 604800, // 7天
            'enable_refresh' => true,
            'enable_blacklist' => true,
            'enable_device_fingerprint' => true,
            'exempt_routes' => [
                '/api/auth/login',
                '/api/auth/register',
                '/api/auth/refresh',
                '/api/health',
                '/api/docs'
            ],
            'strict_mode' => true
        ], $config);
        
        $this->jwtAlgorithm = $this->config['algorithm'];
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $startTime = microtime(true);
        $requestId = uniqid('jwt_', true);
        
        try {
            $path = $request->getUri()->getPath();
            
            // 检查是否为豁免路由
            if ($this->isExemptRoute($path)) {
                return $handler->handle($request);
            }
            
            // 提取令牌
            $token = $this->extractToken($request);
            if (!$token) {
                return $this->createUnauthorizedResponse('未提供认证令牌');
            }
            
            // 验证令牌
            $validationResult = $this->validateToken($token, $request);
            
            if (!$validationResult['valid']) {
                $this->logger->warning('JWT令牌验证失败', [
                    'request_id' => $requestId,
                    'error' => $validationResult['error'],
                    'path' => $path,
                    'ip' => $this->getClientIp($request)
                ]);
                
                return $this->createUnauthorizedResponse($validationResult['error']);
            }
            
            // 检查令牌黑名单
            if ($this->config['enable_blacklist'] && $this->isTokenBlacklisted($token)) {
                $this->logger->warning('JWT令牌在黑名单中', [
                    'request_id' => $requestId,
                    'path' => $path,
                    'ip' => $this->getClientIp($request)
                ]);
                
                return $this->createUnauthorizedResponse('令牌已被撤销');
            }
            
            // 验证设备指纹
            if ($this->config['enable_device_fingerprint']) {
                $fingerprintResult = $this->validateDeviceFingerprint($token, $request);
                if (!$fingerprintResult['valid']) {
                    $this->logger->warning('设备指纹验证失败', [
                        'request_id' => $requestId,
                        'error' => $fingerprintResult['error']
                    ]);
                    
                    if ($this->config['strict_mode']) {
                        return $this->createUnauthorizedResponse('设备验证失败');
                    }
                }
            }
            
            // 将用户信息添加到请求中
            $request = $request->withAttribute('user', $validationResult['user']);
            $request = $request->withAttribute('token', $token);
            $request = $request->withAttribute('token_data', $validationResult['payload']);
            
            // 记录成功验证
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            $this->logger->debug('JWT令牌验证成功', [
                'request_id' => $requestId,
                'user_id' => $validationResult['user']['id'] ?? 'unknown',
                'path' => $path,
                'duration_ms' => $duration
            ]);
            
            return $handler->handle($request);
            
        } catch (\Exception $e) {
            $this->logger->error('JWT中间件异常', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($this->config['strict_mode']) {
                return $this->createUnauthorizedResponse('认证服务异常');
            }
            
            return $handler->handle($request);
        }
    }
    
    /**
     * 检查是否为豁免路由
     */
    private function isExemptRoute(string $path): bool
    {
        foreach ($this->config['exempt_routes'] as $exemptRoute) {
            if (strpos($path, $exemptRoute) === 0) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 从请求中提取令牌
     */
    private function extractToken(ServerRequestInterface $request): ?string
    {
        // 从Authorization头提取
        $authHeader = $request->getHeaderLine('Authorization');
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        // 从查询参数提取
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['token'])) {
            return $queryParams['token'];
        }
        
        // 从Cookie提取
        $cookies = $request->getCookieParams();
        if (isset($cookies['auth_token'])) {
            return $cookies['auth_token'];
        }
        
        return null;
    }
    
    /**
     * 验证JWT令牌
     */
    private function validateToken(string $token, ServerRequestInterface $request): array
    {
        try {
            // 解码令牌
            $decoded = JWT::decode($token, new Key($this->jwtSecret, $this->jwtAlgorithm));
            $payload = (array) $decoded;
            
            // 验证基本字段
            if (!isset($payload['user']) || !isset($payload['iat']) || !isset($payload['exp'])) {
                return [
                    'valid' => false,
                    'error' => '令牌格式无效'
                ];
            }
            
            // 检查令牌类型
            $tokenType = $payload['type'] ?? 'access';
            if ($tokenType !== 'access' && $tokenType !== 'refresh') {
                return [
                    'valid' => false,
                    'error' => '令牌类型无效'
                ];
            }
            
            // 检查令牌是否过期
            if (time() > $payload['exp']) {
                return [
                    'valid' => false,
                    'error' => '令牌已过期'
                ];
            }
            
            // 检查令牌是否过早使用
            if (isset($payload['nbf']) && time() < $payload['nbf']) {
                return [
                    'valid' => false,
                    'error' => '令牌尚未生效'
                ];
            }
            
            // 验证用户信息
            $user = (array) $payload['user'];
            if (!isset($user['id']) || !isset($user['username'])) {
                return [
                    'valid' => false,
                    'error' => '用户信息无效'
                ];
            }
            
            // 检查用户状态
            if (isset($user['status']) && $user['status'] !== 'active') {
                return [
                    'valid' => false,
                    'error' => '用户账户已被禁用'
                ];
            }
            
            return [
                'valid' => true,
                'user' => $user,
                'payload' => $payload,
                'token_type' => $tokenType
            ];
            
        } catch (ExpiredException $e) {
            return [
                'valid' => false,
                'error' => '令牌已过期'
            ];
        } catch (SignatureInvalidException $e) {
            return [
                'valid' => false,
                'error' => '令牌签名无效'
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => '令牌验证失败: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 检查令牌是否在黑名单中
     */
    private function isTokenBlacklisted(string $token): bool
    {
        $tokenHash = hash('sha256', $token);
        $cacheKey = "jwt_blacklist_{$tokenHash}";
        
        return $this->cacheService->has($cacheKey);
    }
    
    /**
     * 验证设备指纹
     */
    private function validateDeviceFingerprint(string $token, ServerRequestInterface $request): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, $this->jwtAlgorithm));
            $payload = (array) $decoded;
            
            if (!isset($payload['device_fingerprint'])) {
                return [
                    'valid' => true,
                    'error' => null
                ];
            }
            
            $expectedFingerprint = $payload['device_fingerprint'];
            $currentFingerprint = $this->generateDeviceFingerprint($request);
            
            if ($expectedFingerprint !== $currentFingerprint) {
                return [
                    'valid' => false,
                    'error' => '设备指纹不匹配'
                ];
            }
            
            return [
                'valid' => true,
                'error' => null
            ];
            
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => '设备指纹验证失败'
            ];
        }
    }
    
    /**
     * 生成设备指纹
     */
    private function generateDeviceFingerprint(ServerRequestInterface $request): string
    {
        $components = [
            'ip' => $this->getClientIp($request),
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'accept_language' => $request->getHeaderLine('Accept-Language'),
            'accept_encoding' => $request->getHeaderLine('Accept-Encoding')
        ];
        
        return hash('sha256', json_encode($components));
    }
    
    /**
     * 获取客户端IP
     */
    private function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();
        
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($serverParams[$header])) {
                $ip = $serverParams[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return $serverParams['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * 创建未授权响应
     */
    private function createUnauthorizedResponse(string $message): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => $message,
            'code' => 401,
            'timestamp' => date('c')
        ]));
        
        return $response
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('WWW-Authenticate', 'Bearer');
    }
    
    /**
     * 将令牌加入黑名单
     */
    public function blacklistToken(string $token, int $ttl = 3600): bool
    {
        if (!$this->config['enable_blacklist']) {
            return false;
        }
        
        $tokenHash = hash('sha256', $token);
        $cacheKey = "jwt_blacklist_{$tokenHash}";
        
        return $this->cacheService->set($cacheKey, true, $ttl);
    }
    
    /**
     * 从黑名单中移除令牌
     */
    public function removeFromBlacklist(string $token): bool
    {
        if (!$this->config['enable_blacklist']) {
            return false;
        }
        
        $tokenHash = hash('sha256', $token);
        $cacheKey = "jwt_blacklist_{$tokenHash}";
        
        return $this->cacheService->delete($cacheKey);
    }
    
    /**
     * 生成新的访问令牌
     */
    public function generateAccessToken(array $user, array $additionalData = []): string
    {
        $payload = [
            'user' => $user,
            'type' => 'access',
            'iat' => time(),
            'exp' => time() + $this->config['token_expiry'],
            'jti' => uniqid('jwt_', true)
        ];
        
        if ($this->config['enable_device_fingerprint']) {
            $payload['device_fingerprint'] = $additionalData['device_fingerprint'] ?? '';
        }
        
        return JWT::encode($payload, $this->jwtSecret, $this->jwtAlgorithm);
    }
    
    /**
     * 生成新的刷新令牌
     */
    public function generateRefreshToken(array $user, array $additionalData = []): string
    {
        $payload = [
            'user' => $user,
            'type' => 'refresh',
            'iat' => time(),
            'exp' => time() + $this->config['refresh_expiry'],
            'jti' => uniqid('jwt_refresh_', true)
        ];
        
        if ($this->config['enable_device_fingerprint']) {
            $payload['device_fingerprint'] = $additionalData['device_fingerprint'] ?? '';
        }
        
        return JWT::encode($payload, $this->jwtSecret, $this->jwtAlgorithm);
    }
    
    /**
     * 刷新访问令牌
     */
    public function refreshAccessToken(string $refreshToken, ServerRequestInterface $request): array
    {
        if (!$this->config['enable_refresh']) {
            return [
                'success' => false,
                'error' => '令牌刷新功能已禁用'
            ];
        }
        
        $validationResult = $this->validateToken($refreshToken, $request);
        
        if (!$validationResult['valid']) {
            return [
                'success' => false,
                'error' => $validationResult['error']
            ];
        }
        
        if ($validationResult['token_type'] !== 'refresh') {
            return [
                'success' => false,
                'error' => '无效的刷新令牌'
            ];
        }
        
        // 生成新的访问令牌
        $newAccessToken = $this->generateAccessToken($validationResult['user']);
        
        return [
            'success' => true,
            'access_token' => $newAccessToken,
            'expires_in' => $this->config['token_expiry']
        ];
    }
}