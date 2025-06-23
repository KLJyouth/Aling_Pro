<?php

namespace AlingAi\Config;

/**
 * API配置管理器
 * 
 * 管理所有API相关的配置，包括版本、端点、认证、限流等
 * 优化性能：配置缓存、动态加载、环境适配
 * 增强安全性：配置验证、访问控制、敏感信息保护
 */
class api_config
{
    private static $instance = null;
    private array $config = [];
    private bool $loaded = false;
    
    private function __construct()
    {
        $this->loadConfiguration();
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 加载API配置
     */
    private function loadConfiguration(): void
    {
        if ($this->loaded) {
            return;
        }
        
        $this->config = [
            // API版本配置
            'version' => [
                'current' => 'v2.0.0',
                'supported' => ['v1.0.0', 'v1.5.0', 'v2.0.0'],
                'deprecated' => ['v1.0.0'],
                'deprecation_date' => '2024-12-31'
            ],
            
            // API端点配置
            'endpoints' => [
                'base_url' => env('API_BASE_URL', 'https://api.alingai.com'),
                'prefix' => '/api',
                'timeout' => 30,
                'retry_attempts' => 3,
                'retry_delay' => 1000
            ],
            
            // 认证配置
            'auth' => [
                'methods' => ['jwt', 'api_key', 'oauth2'],
                'jwt' => [
                    'secret' => env('JWT_SECRET'),
                    'algorithm' => 'HS256',
                    'expire_time' => 3600,
                    'refresh_expire_time' => 604800,
                    'issuer' => 'alingai-api',
                    'audience' => 'alingai-clients'
                ],
                'api_key' => [
                    'header_name' => 'X-API-Key',
                    'required' => true,
                    'rate_limit' => 1000
                ],
                'oauth2' => [
                    'enabled' => true,
                    'providers' => ['google', 'github', 'microsoft'],
                    'client_id' => env('OAUTH_CLIENT_ID'),
                    'client_secret' => env('OAUTH_CLIENT_SECRET'),
                    'redirect_uri' => env('OAUTH_REDIRECT_URI')
                ]
            ],
            
            // 速率限制配置
            'rate_limit' => [
                'enabled' => true,
                'default' => [
                    'requests' => 100,
                    'window' => 3600,
                    'burst' => 10
                ],
                'endpoints' => [
                    'auth' => [
                        'requests' => 10,
                        'window' => 300
                    ],
        'chat' => [
                        'requests' => 50,
                        'window' => 3600
                    ],
                    'upload' => [
                        'requests' => 20,
                        'window' => 3600
                    ],
                    'admin' => [
                        'requests' => 1000,
                        'window' => 3600
                    ]
                ],
                'user_tiers' => [
                    'free' => [
                        'multiplier' => 1.0,
                        'daily_limit' => 1000
                    ],
                    'pro' => [
                        'multiplier' => 5.0,
                        'daily_limit' => 10000
                    ],
                    'enterprise' => [
                        'multiplier' => 20.0,
                        'daily_limit' => 100000
                    ]
                ]
            ],
            
            // 缓存配置
            'cache' => [
                'enabled' => true,
                'driver' => env('CACHE_DRIVER', 'redis'),
                'ttl' => [
                    'default' => 3600,
                    'user_data' => 300,
                    'api_responses' => 1800,
                    'rate_limits' => 3600
                ],
                'tags' => [
                    'api_responses',
                    'user_data',
                    'rate_limits',
                    'auth_tokens'
                ]
            ],
            
            // 日志配置
            'logging' => [
                'enabled' => true,
                'level' => env('API_LOG_LEVEL', 'info'),
                'channels' => [
                    'api_requests',
                    'api_errors',
                    'rate_limits',
                    'auth_events'
                ],
                'sensitive_fields' => [
                    'password',
                    'token',
                    'api_key',
                    'secret'
                ]
            ],
            
            // 安全配置
            'security' => [
                'cors' => [
                    'enabled' => true,
                    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),
                    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                    'allowed_headers' => ['Content-Type', 'Authorization', 'X-API-Key'],
                    'exposed_headers' => ['X-Rate-Limit-Remaining', 'X-Rate-Limit-Reset'],
                    'max_age' => 86400
                ],
                'headers' => [
                    'x_frame_options' => 'DENY',
                    'x_content_type_options' => 'nosniff',
                    'x_xss_protection' => '1; mode=block',
                    'referrer_policy' => 'strict-origin-when-cross-origin',
                    'content_security_policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'"
                ],
                'validation' => [
                    'strict_mode' => true,
                    'max_request_size' => '10MB',
                    'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
                    'max_file_size' => '5MB'
                ]
            ],
            
            // 监控配置
            'monitoring' => [
                'enabled' => true,
                'metrics' => [
                    'response_time',
                    'error_rate',
                    'throughput',
                    'rate_limit_hits'
                ],
                'alerts' => [
                    'error_threshold' => 0.05,
                    'response_time_threshold' => 2000,
                    'rate_limit_threshold' => 0.8
                ],
                'health_check' => [
                    'enabled' => true,
                    'endpoint' => '/health',
                    'interval' => 300
                ]
            ],
            
            // AI服务配置
            'ai_services' => [
                'providers' => [
                    'deepseek' => [
                        'enabled' => true,
                        'api_key' => env('DEEPSEEK_API_KEY'),
                        'api_url' => env('DEEPSEEK_API_URL', 'https://api.deepseek.com/v1'),
                        'models' => ['deepseek-chat', 'deepseek-coder'],
                        'rate_limit' => 100,
                        'timeout' => 30
                    ],
                    'openai' => [
                        'enabled' => true,
                        'api_key' => env('OPENAI_API_KEY'),
                        'api_url' => env('OPENAI_API_URL', 'https://api.openai.com/v1'),
                        'models' => ['gpt-4', 'gpt-3.5-turbo'],
                        'rate_limit' => 50,
                        'timeout' => 30
                    ],
                    'anthropic' => [
                        'enabled' => true,
                        'api_key' => env('ANTHROPIC_API_KEY'),
                        'api_url' => env('ANTHROPIC_API_URL', 'https://api.anthropic.com'),
                        'models' => ['claude-3-opus', 'claude-3-sonnet'],
                        'rate_limit' => 30,
                        'timeout' => 30
                    ]
                ],
                'fallback' => [
                    'enabled' => true,
                    'strategy' => 'round_robin',
                    'max_retries' => 3
                ]
            ],
            
            // 文档配置
            'documentation' => [
                'enabled' => true,
                'swagger' => [
                    'enabled' => true,
                    'path' => '/docs',
                    'title' => 'AlingAi Pro API Documentation',
                    'version' => '2.0.0',
                    'description' => 'Complete API documentation for AlingAi Pro platform'
                ],
                'postman' => [
                    'enabled' => true,
                    'collection_url' => env('POSTMAN_COLLECTION_URL')
                ]
            ]
        ];
        
        $this->loaded = true;
    }
    
    /**
     * 获取配置值
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        
        return $value;
    }
    
    /**
     * 设置配置值
     */
    public function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;
        
        foreach ($keys as $segment) {
            if (!isset($config[$segment]) || !is_array($config[$segment])) {
                $config[$segment] = [];
            }
            $config = &$config[$segment];
        }
        
        $config = $value;
    }
    
    /**
     * 检查配置是否存在
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }
    
    /**
     * 获取所有配置
     */
    public function all(): array
    {
        return $this->config;
    }
    
    /**
     * 验证配置
     */
    public function validate(): array
    {
        $errors = [];
        
        // 验证必需的环境变量
        $requiredEnvVars = [
            'JWT_SECRET' => 'JWT密钥',
            'API_BASE_URL' => 'API基础URL',
            'DEEPSEEK_API_KEY' => 'DeepSeek API密钥'
        ];
        
        foreach ($requiredEnvVars as $var => $description) {
            if (empty(env($var))) {
                $errors[] = "缺少必需的环境变量: {$var} ({$description})";
            }
        }
        
        // 验证API配置
        if (empty($this->config['auth']['jwt']['secret'])) {
            $errors[] = 'JWT密钥未配置';
        }
        
        if (empty($this->config['endpoints']['base_url'])) {
            $errors[] = 'API基础URL未配置';
        }
        
        return $errors;
    }
    
    /**
     * 获取API版本信息
     */
    public function getVersionInfo(): array
    {
        return $this->config['version'];
    }
    
    /**
     * 检查API版本是否支持
     */
    public function isVersionSupported(string $version): bool
    {
        return in_array($version, $this->config['version']['supported']);
    }
    
    /**
     * 检查API版本是否已弃用
     */
    public function isVersionDeprecated(string $version): bool
    {
        return in_array($version, $this->config['version']['deprecated']);
    }
    
    /**
     * 获取速率限制配置
     */
    public function getRateLimitConfig(string $endpoint = 'default'): array
    {
        return $this->config['rate_limit']['endpoints'][$endpoint] ?? $this->config['rate_limit']['default'];
    }
    
    /**
     * 获取用户级别速率限制
     */
    public function getUserTierConfig(string $tier): array
    {
        return $this->config['rate_limit']['user_tiers'][$tier] ?? $this->config['rate_limit']['user_tiers']['free'];
    }
    
    /**
     * 获取AI服务配置
     */
    public function getAIServiceConfig(string $provider): ?array
    {
        return $this->config['ai_services']['providers'][$provider] ?? null;
    }
    
    /**
     * 获取启用的AI服务提供商
     */
    public function getEnabledAIProviders(): array
    {
        $enabled = [];
        foreach ($this->config['ai_services']['providers'] as $provider => $config) {
            if ($config['enabled']) {
                $enabled[$provider] = $config;
            }
        }
        return $enabled;
    }
    
    /**
     * 重新加载配置
     */
    public function reload(): void
    {
        $this->loaded = false;
        $this->loadConfiguration();
    }
}
