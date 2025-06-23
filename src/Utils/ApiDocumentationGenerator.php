<?php

namespace AlingAi\Utils;

use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionMethod;

/**
 * API文档生成器
 * 
 * 自动生成完整的API文档和测试用例
 * 优化性能：智能解析、缓存生成、增量更新
 * 增强功能：示例生成、测试用例、文档版本管理
 */
class ApiDocumentationGenerator
{
    private LoggerInterface $logger;
    private array $config;
    private array $controllers = [];
    private array $routes = [];
    private array $documentation = [];
    
    public function __construct(LoggerInterface $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = array_merge([
            'output_dir' => dirname(__DIR__, 2) . '/public/docs',
            'formats' => ['html', 'json', 'yaml', 'markdown'],
            'include_examples' => true,
            'include_tests' => true,
            'include_schemas' => true,
            'auto_generate' => true,
            'version' => '2.0.0',
            'title' => 'AlingAi Pro API Documentation',
            'description' => 'Complete API documentation for AlingAi Pro',
            'contact' => [
                'name' => 'API Support',
                'email' => 'support@alingai.com',
                'url' => 'https://alingai.com'
            ],
            'servers' => [
                [
                    'url' => 'https://api.alingai.com',
                    'description' => 'Production server'
                ],
                [
                    'url' => 'https://dev-api.alingai.com',
                    'description' => 'Development server'
                ]
            ],
            'security' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT'
                ]
            ]
        ], $config);
    }
    
    /**
     * 生成API文档
     */
    public function generateDocumentation(array $options = []): array
    {
        $startTime = microtime(true);
        
        try {
            $options = array_merge([
                'controllers' => null,
                'format' => 'all',
                'include_examples' => $this->config['include_examples'],
                'include_tests' => $this->config['include_tests'],
                'output_file' => null
            ], $options);
            
            $this->logger->info('开始生成API文档', $options);
            
            // 扫描控制器
            $this->scanControllers($options['controllers']);
            
            // 解析路由
            $this->parseRoutes();
            
            // 生成文档结构
            $this->generateDocumentationStructure();
            
            // 生成示例
            if ($options['include_examples']) {
                $this->generateExamples();
            }
            
            // 生成测试用例
            if ($options['include_tests']) {
                $this->generateTestCases();
            }
            
            // 生成模式定义
            if ($this->config['include_schemas']) {
                $this->generateSchemas();
            }
            
            // 输出文档
            $outputFiles = $this->outputDocumentation($options);
            
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->logger->info('API文档生成完成', [
                'endpoints' => count($this->documentation['paths'] ?? []),
                'output_files' => count($outputFiles),
                'duration_ms' => $duration
            ]);
            
            return [
                'success' => true,
                'documentation' => $this->documentation,
                'output_files' => $outputFiles,
                'duration' => $duration
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('API文档生成失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * 扫描控制器
     */
    private function scanControllers(?array $specificControllers): void
    {
        if ($specificControllers) {
            $this->controllers = $specificControllers;
            return;
        }
        
        $controllerDir = dirname(__DIR__) . '/Controllers';
        $files = glob($controllerDir . '/*.php');
        
        foreach ($files as $file) {
            $className = 'AlingAi\\Controllers\\' . basename($file, '.php');
            
            if (class_exists($className)) {
                $reflection = new ReflectionClass($className);
                
                if ($reflection->isInstantiable()) {
                    $this->controllers[$className] = [
                        'file' => $file,
                        'reflection' => $reflection,
                        'methods' => $this->getControllerMethods($reflection)
                    ];
                }
            }
        }
    }
    
    /**
     * 获取控制器方法
     */
    private function getControllerMethods(ReflectionClass $reflection): array
    {
        $methods = [];
        
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class === $reflection->getName() && !$method->isConstructor()) {
                $methods[$method->getName()] = [
                    'reflection' => $method,
                    'doc_comment' => $method->getDocComment(),
                    'parameters' => $this->getMethodParameters($method),
                    'annotations' => $this->parseAnnotations($method->getDocComment())
                ];
            }
        }
        
        return $methods;
    }
    
    /**
     * 获取方法参数
     */
    private function getMethodParameters(ReflectionMethod $method): array
    {
        $parameters = [];
        
        foreach ($method->getParameters() as $param) {
            $parameters[$param->getName()] = [
                'type' => $param->getType() ? $param->getType()->getName() : 'mixed',
                'required' => !$param->isOptional(),
                'default' => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null
            ];
        }
        
        return $parameters;
    }
    
    /**
     * 解析注释
     */
    private function parseAnnotations(?string $docComment): array
    {
        if (!$docComment) {
            return [];
        }
        
        $annotations = [];
        $lines = explode("\n", $docComment);
        
        foreach ($lines as $line) {
            $line = trim($line, " \t\n\r\0\x0B*/");
            
            if (preg_match('/^@(\w+)\s+(.+)$/', $line, $matches)) {
                $annotation = $matches[1];
                $value = trim($matches[2]);
                
                if (!isset($annotations[$annotation])) {
                    $annotations[$annotation] = [];
                }
                
                $annotations[$annotation][] = $value;
            }
        }
        
        return $annotations;
    }
    
    /**
     * 解析路由
     */
    private function parseRoutes(): void
    {
        // 预定义的路由映射
        $this->routes = [
            'POST /api/auth/login' => [
                'controller' => 'AuthController',
                'method' => 'login',
                'tags' => ['Authentication'],
                'summary' => '用户登录',
                'description' => '用户通过邮箱和密码登录系统，返回访问令牌',
                'security' => false
            ],
            'POST /api/auth/register' => [
                'controller' => 'AuthController',
                'method' => 'register',
                'tags' => ['Authentication'],
                'summary' => '用户注册',
                'description' => '新用户注册账户',
                'security' => false
            ],
            'POST /api/auth/logout' => [
                'controller' => 'AuthController',
                'method' => 'logout',
                'tags' => ['Authentication'],
                'summary' => '用户登出',
                'description' => '用户退出登录，使当前令牌失效',
                'security' => true
            ],
            'GET /api/user/profile' => [
                'controller' => 'UserController',
                'method' => 'getProfile',
                'tags' => ['User Management'],
                'summary' => '获取用户资料',
                'description' => '获取当前登录用户的详细资料信息',
                'security' => true
            ],
            'PUT /api/user/profile' => [
                'controller' => 'UserController',
                'method' => 'updateProfile',
                'tags' => ['User Management'],
                'summary' => '更新用户资料',
                'description' => '更新当前登录用户的资料信息',
                'security' => true
            ],
            'POST /api/chat/send' => [
                'controller' => 'ChatController',
                'method' => 'sendMessage',
                'tags' => ['Chat'],
                'summary' => '发送聊天消息',
                'description' => '向AI发送消息并获取智能回复',
                'security' => true
            ],
            'GET /api/chat/history' => [
                'controller' => 'ChatController',
                'method' => 'getHistory',
                'tags' => ['Chat'],
                'summary' => '获取聊天历史',
                'description' => '获取用户的聊天历史记录',
                'security' => true
            ],
            'GET /api/admin/users' => [
                'controller' => 'AdminController',
                'method' => 'getUsers',
                'tags' => ['Admin'],
                'summary' => '获取用户列表',
                'description' => '管理员获取所有用户列表',
                'security' => true
            ],
            'GET /api/admin/stats' => [
                'controller' => 'AdminController',
                'method' => 'getStats',
                'tags' => ['Admin'],
                'summary' => '获取系统统计',
                'description' => '获取系统运行统计数据',
                'security' => true
            ]
        ];
    }
    
    /**
     * 生成文档结构
     */
    private function generateDocumentationStructure(): void
    {
        $this->documentation = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => $this->config['title'],
                'description' => $this->config['description'],
                'version' => $this->config['version'],
                'contact' => $this->config['contact']
            ],
            'servers' => $this->config['servers'],
            'security' => $this->config['security'],
            'tags' => $this->generateTags(),
            'paths' => $this->generatePaths(),
            'components' => [
                'schemas' => [],
                'securitySchemes' => $this->generateSecuritySchemes()
            ]
        ];
    }
    
    /**
     * 生成标签
     */
    private function generateTags(): array
    {
        $tags = [
            [
                'name' => 'Authentication',
                'description' => '用户认证相关接口'
            ],
            [
                'name' => 'User Management',
                'description' => '用户管理相关接口'
            ],
            [
                'name' => 'Chat',
                'description' => '聊天功能相关接口'
            ],
            [
                'name' => 'Admin',
                'description' => '管理员功能相关接口'
            ]
        ];
        
        return $tags;
    }
    
    /**
     * 生成路径
     */
    private function generatePaths(): array
    {
        $paths = [];
        
        foreach ($this->routes as $route => $routeInfo) {
            [$method, $path] = explode(' ', $route, 2);
            $method = strtolower($method);
            
            if (!isset($paths[$path])) {
                $paths[$path] = [];
            }
            
            $paths[$path][$method] = $this->generatePathItem($routeInfo);
        }
        
        return $paths;
    }
    
    /**
     * 生成路径项
     */
    private function generatePathItem(array $routeInfo): array
    {
        $pathItem = [
            'tags' => $routeInfo['tags'],
            'summary' => $routeInfo['summary'],
            'description' => $routeInfo['description'],
            'parameters' => $this->generateParameters($routeInfo),
            'requestBody' => $this->generateRequestBody($routeInfo),
            'responses' => $this->generateResponses($routeInfo),
            'security' => $routeInfo['security'] ? [$this->config['security']] : []
        ];
        
        // 移除空值
        return array_filter($pathItem, function($value) {
            return $value !== null && $value !== [];
        });
    }
    
    /**
     * 生成参数
     */
    private function generateParameters(array $routeInfo): array
    {
        $parameters = [];
        
        // 路径参数
        if (preg_match_all('/\{([^}]+)\}/', $routeInfo['summary'], $matches)) {
            foreach ($matches[1] as $param) {
                $parameters[] = [
                    'name' => $param,
                    'in' => 'path',
                    'required' => true,
                    'schema' => [
                        'type' => 'string'
                    ],
                    'description' => "路径参数: {$param}"
                ];
            }
        }
        
        // 查询参数
        if (in_array($routeInfo['method'], ['get', 'list', 'search'])) {
            $parameters[] = [
                'name' => 'page',
                'in' => 'query',
                'required' => false,
                'schema' => [
                    'type' => 'integer',
                    'default' => 1,
                    'minimum' => 1
                ],
                'description' => '页码'
            ];
            
            $parameters[] = [
                'name' => 'limit',
                'in' => 'query',
                'required' => false,
                'schema' => [
                    'type' => 'integer',
                    'default' => 20,
                    'minimum' => 1,
                    'maximum' => 100
                ],
                'description' => '每页数量'
            ];
        }
        
        return $parameters;
    }
    
    /**
     * 生成请求体
     */
    private function generateRequestBody(array $routeInfo): ?array
    {
        $method = strtolower($routeInfo['method']);
        
        if (!in_array($method, ['post', 'put', 'patch'])) {
            return null;
        }
        
        $schema = $this->generateRequestSchema($routeInfo);
        
        if (!$schema) {
            return null;
        }
        
        return [
            'required' => true,
            'content' => [
                'application/json' => [
                    'schema' => $schema,
                    'examples' => $this->generateRequestExamples($routeInfo)
                ]
            ]
        ];
    }
    
    /**
     * 生成请求模式
     */
    private function generateRequestSchema(array $routeInfo): ?array
    {
        $schemas = [
            'login' => [
                'type' => 'object',
                'required' => ['email', 'password'],
                'properties' => [
                    'email' => [
                        'type' => 'string',
                        'format' => 'email',
                        'description' => '用户邮箱地址',
                        'example' => 'user@example.com'
                    ],
                    'password' => [
                        'type' => 'string',
                        'minLength' => 6,
                        'description' => '用户密码',
                        'example' => 'password123'
                    ],
                    'remember' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => '记住登录状态',
                        'example' => true
                    ]
                ]
            ],
            'register' => [
                'type' => 'object',
                'required' => ['username', 'email', 'password', 'password_confirmation'],
                'properties' => [
                    'username' => [
                        'type' => 'string',
                        'minLength' => 3,
                        'maxLength' => 50,
                        'description' => '用户名',
                        'example' => 'newuser'
                    ],
                    'email' => [
                        'type' => 'string',
                        'format' => 'email',
                        'description' => '用户邮箱地址',
                        'example' => 'newuser@example.com'
                    ],
                    'password' => [
                        'type' => 'string',
                        'minLength' => 8,
                        'description' => '密码',
                        'example' => 'password123'
                    ],
                    'password_confirmation' => [
                        'type' => 'string',
                        'description' => '确认密码',
                        'example' => 'password123'
                    ]
                ]
            ],
            'sendMessage' => [
                'type' => 'object',
                'required' => ['message'],
                'properties' => [
                    'message' => [
                        'type' => 'string',
                        'description' => '聊天消息内容',
                        'example' => '你好，请介绍一下人工智能'
                    ],
                    'conversation_id' => [
                        'type' => 'string',
                        'description' => '对话ID（可选）',
                        'example' => 'conv_123456'
                    ],
                    'model' => [
                        'type' => 'string',
                        'enum' => ['gpt-3.5-turbo', 'gpt-4', 'claude-3'],
                        'default' => 'gpt-3.5-turbo',
                        'description' => 'AI模型',
                        'example' => 'gpt-3.5-turbo'
                    ]
                ]
            ]
        ];
        
        return $schemas[$routeInfo['method']] ?? null;
    }
    
    /**
     * 生成请求示例
     */
    private function generateRequestExamples(array $routeInfo): array
    {
        $examples = [
            'login' => [
                'summary' => '登录示例',
                'value' => [
                    'email' => 'user@example.com',
                    'password' => 'password123',
                    'remember' => true
                ]
            ],
            'register' => [
                'summary' => '注册示例',
                'value' => [
                    'username' => 'newuser',
                    'email' => 'newuser@example.com',
                    'password' => 'password123',
                    'password_confirmation' => 'password123'
                ]
            ],
            'sendMessage' => [
                'summary' => '发送消息示例',
                'value' => [
                    'message' => '你好，请介绍一下人工智能',
                    'model' => 'gpt-3.5-turbo'
                ]
            ]
        ];
        
        return $examples[$routeInfo['method']] ?? [];
    }
    
    /**
     * 生成响应
     */
    private function generateResponses(array $routeInfo): array
    {
        $responses = [
            '200' => [
                'description' => '成功',
                'content' => [
                    'application/json' => [
                        'schema' => $this->generateResponseSchema($routeInfo),
                        'examples' => $this->generateResponseExamples($routeInfo)
                    ]
                ]
            ],
            '400' => [
                'description' => '请求错误',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ErrorResponse'
                        ],
                        'example' => [
                            'success' => false,
                            'error' => '请求参数错误',
                            'code' => 400
                        ]
                    ]
                ]
            ],
            '401' => [
                'description' => '未授权',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ErrorResponse'
                        ],
                        'example' => [
                            'success' => false,
                            'error' => '未授权访问',
                            'code' => 401
                        ]
                    ]
                ]
            ],
            '403' => [
                'description' => '禁止访问',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ErrorResponse'
                        ],
                        'example' => [
                            'success' => false,
                            'error' => '权限不足',
                            'code' => 403
                        ]
                    ]
                ]
            ],
            '404' => [
                'description' => '未找到',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ErrorResponse'
                        ],
                        'example' => [
                            'success' => false,
                            'error' => '资源未找到',
                            'code' => 404
                        ]
                    ]
                ]
            ],
            '500' => [
                'description' => '服务器错误',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ErrorResponse'
                        ],
                        'example' => [
                            'success' => false,
                            'error' => '服务器内部错误',
                            'code' => 500
                        ]
                    ]
                ]
            ]
        ];
        
        return $responses;
    }
    
    /**
     * 生成响应模式
     */
    private function generateResponseSchema(array $routeInfo): array
    {
        $schemas = [
            'login' => [
                'type' => 'object',
                'properties' => [
                    'success' => [
                        'type' => 'boolean',
                        'example' => true
                    ],
                    'data' => [
                        'type' => 'object',
                        'properties' => [
                            'token' => [
                                'type' => 'string',
                                'description' => '访问令牌'
                            ],
                            'user' => [
                                '$ref' => '#/components/schemas/User'
                            ]
                        ]
                    ],
                    'message' => [
                        'type' => 'string',
                        'example' => '登录成功'
                    ]
                ]
            ],
            'getProfile' => [
                'type' => 'object',
                'properties' => [
                    'success' => [
                        'type' => 'boolean',
                        'example' => true
                    ],
                    'data' => [
                        '$ref' => '#/components/schemas/User'
                    ]
                ]
            ],
            'sendMessage' => [
                'type' => 'object',
                'properties' => [
                    'success' => [
                        'type' => 'boolean',
                        'example' => true
                    ],
                    'data' => [
                        'type' => 'object',
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'description' => 'AI回复内容'
                            ],
                            'conversation_id' => [
                                'type' => 'string',
                                'description' => '对话ID'
                            ],
                            'tokens_used' => [
                                'type' => 'integer',
                                'description' => '使用的令牌数'
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        return $schemas[$routeInfo['method']] ?? [
            'type' => 'object',
            'properties' => [
                'success' => [
                    'type' => 'boolean'
                ],
                'data' => [
                    'type' => 'object'
                ],
                'message' => [
                    'type' => 'string'
                ]
            ]
        ];
    }
    
    /**
     * 生成响应示例
     */
    private function generateResponseExamples(array $routeInfo): array
    {
        $examples = [
            'login' => [
                'summary' => '登录成功',
                'value' => [
                    'success' => true,
                    'data' => [
                        'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
                        'user' => [
                            'id' => 1,
                            'username' => 'testuser',
                            'email' => 'test@example.com'
                        ]
                    ],
                    'message' => '登录成功'
                ]
            ],
            'sendMessage' => [
                'summary' => 'AI回复',
                'value' => [
                    'success' => true,
                    'data' => [
                        'message' => '人工智能（AI）是计算机科学的一个分支，致力于创建能够执行通常需要人类智能的任务的系统...',
                        'conversation_id' => 'conv_123456',
                        'tokens_used' => 150
                    ]
                ]
            ]
        ];
        
        return $examples[$routeInfo['method']] ?? [];
    }
    
    /**
     * 生成安全方案
     */
    private function generateSecuritySchemes(): array
    {
        return [
            'bearerAuth' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'JWT'
            ]
        ];
    }
    
    /**
     * 生成示例
     */
    private function generateExamples(): void
    {
        $this->documentation['components']['examples'] = [
            'UserLogin' => [
                'summary' => '用户登录示例',
                'value' => [
                    'email' => 'user@example.com',
                    'password' => 'password123'
                ]
            ],
            'UserRegistration' => [
                'summary' => '用户注册示例',
                'value' => [
                    'username' => 'newuser',
                    'email' => 'newuser@example.com',
                    'password' => 'password123',
                    'password_confirmation' => 'password123'
                ]
            ],
            'ChatMessage' => [
                'summary' => '聊天消息示例',
                'value' => [
                    'message' => '你好，请介绍一下人工智能',
                    'model' => 'gpt-3.5-turbo'
                ]
            ]
        ];
    }
    
    /**
     * 生成测试用例
     */
    private function generateTestCases(): void
    {
        $testCases = [];
        
        foreach ($this->routes as $route => $routeInfo) {
            [$method, $path] = explode(' ', $route, 2);
            
            $testCase = [
                'name' => $routeInfo['summary'],
                'method' => strtoupper($method),
                'path' => $path,
                'description' => $routeInfo['description'],
                'request' => $this->generateTestCaseRequest($routeInfo),
                'response' => $this->generateTestCaseResponse($routeInfo)
            ];
            
            $testCases[] = $testCase;
        }
        
        $this->documentation['testCases'] = $testCases;
    }
    
    /**
     * 生成测试用例请求
     */
    private function generateTestCaseRequest(array $routeInfo): array
    {
        $method = strtolower($routeInfo['method']);
        
        $request = [
            'method' => strtoupper($method),
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ];
        
        if ($routeInfo['security']) {
            $request['headers']['Authorization'] = 'Bearer {token}';
        }
        
        if (in_array($method, ['post', 'put', 'patch'])) {
            $request['body'] = $this->generateRequestExamples($routeInfo);
        }
        
        return $request;
    }
    
    /**
     * 生成测试用例响应
     */
    private function generateTestCaseResponse(array $routeInfo): array
    {
        return [
            'status' => 200,
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => $this->generateResponseExamples($routeInfo)
        ];
    }
    
    /**
     * 生成模式定义
     */
    private function generateSchemas(): void
    {
        $this->documentation['components']['schemas'] = [
            'User' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'integer',
                        'description' => '用户ID'
                    ],
                    'username' => [
                        'type' => 'string',
                        'description' => '用户名'
                    ],
                    'email' => [
                        'type' => 'string',
                        'format' => 'email',
                        'description' => '邮箱地址'
                    ],
                    'created_at' => [
                        'type' => 'string',
                        'format' => 'date-time',
                        'description' => '创建时间'
                    ],
                    'updated_at' => [
                        'type' => 'string',
                        'format' => 'date-time',
                        'description' => '更新时间'
                    ]
                ]
            ],
            'ErrorResponse' => [
                'type' => 'object',
                'properties' => [
                    'success' => [
                        'type' => 'boolean',
                        'example' => false
                    ],
                    'error' => [
                        'type' => 'string',
                        'description' => '错误信息'
                    ],
                    'code' => [
                        'type' => 'integer',
                        'description' => '错误代码'
                    ],
                    'timestamp' => [
                        'type' => 'string',
                        'format' => 'date-time',
                        'description' => '时间戳'
                    ]
                ]
            ]
        ];
    }
    
    /**
     * 输出文档
     */
    private function outputDocumentation(array $options): array
    {
        $outputFiles = [];
        $formats = $options['format'] === 'all' ? $this->config['formats'] : [$options['format']];
        
        foreach ($formats as $format) {
            $outputFile = $this->generateOutputFile($format, $options);
            if ($outputFile) {
                $outputFiles[] = $outputFile;
            }
        }
        
        return $outputFiles;
    }
    
    /**
     * 生成输出文件
     */
    private function generateOutputFile(string $format, array $options): ?string
    {
        $this->ensureOutputDirectory();
        
        switch ($format) {
            case 'html':
                return $this->generateHtmlFile($options);
                
            case 'json':
                return $this->generateJsonFile($options);
                
            case 'yaml':
                return $this->generateYamlFile($options);
                
            case 'markdown':
                return $this->generateMarkdownFile($options);
                
            default:
                $this->logger->warning('不支持的输出格式', ['format' => $format]);
                return null;
        }
    }
    
    /**
     * 确保输出目录存在
     */
    private function ensureOutputDirectory(): void
    {
        if (!is_dir($this->config['output_dir'])) {
            if (!mkdir($this->config['output_dir'], 0755, true)) {
                throw new \RuntimeException("无法创建输出目录: {$this->config['output_dir']}");
            }
        }
    }
    
    /**
     * 生成HTML文件
     */
    private function generateHtmlFile(array $options): string
    {
        $htmlContent = $this->getHtmlTemplate();
        $htmlContent = str_replace('{{SWAGGER_JSON}}', json_encode($this->documentation), $htmlContent);
        
        $outputFile = $this->config['output_dir'] . '/api-docs.html';
        file_put_contents($outputFile, $htmlContent);
        
        return $outputFile;
    }
    
    /**
     * 生成JSON文件
     */
    private function generateJsonFile(array $options): string
    {
        $outputFile = $this->config['output_dir'] . '/api-docs.json';
        file_put_contents($outputFile, json_encode($this->documentation, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return $outputFile;
    }
    
    /**
     * 生成YAML文件
     */
    private function generateYamlFile(array $options): string
    {
        $yamlContent = $this->arrayToYaml($this->documentation);
        $outputFile = $this->config['output_dir'] . '/api-docs.yaml';
        file_put_contents($outputFile, $yamlContent);
        
        return $outputFile;
    }
    
    /**
     * 生成Markdown文件
     */
    private function generateMarkdownFile(array $options): string
    {
        $markdownContent = $this->generateMarkdownContent();
        $outputFile = $this->config['output_dir'] . '/api-docs.md';
        file_put_contents($outputFile, $markdownContent);
        
        return $outputFile;
    }
    
    /**
     * 获取HTML模板
     */
    private function getHtmlTemplate(): string
    {
        return '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $this->config['title'] . '</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui.css" />
    <style>
        html { box-sizing: border-box; overflow: -moz-scrollbars-vertical; overflow-y: scroll; }
        *, *:before, *:after { box-sizing: inherit; }
        body { margin:0; background: #fafafa; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                spec: {{SWAGGER_JSON}},
                dom_id: "#swagger-ui",
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout"
            });
        };
    </script>
</body>
</html>';
    }
    
    /**
     * 数组转YAML
     */
    private function arrayToYaml(array $array, int $indent = 0): string
    {
        $yaml = '';
        $indentStr = str_repeat('  ', $indent);
        
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (empty($value)) {
                    $yaml .= "{$indentStr}{$key}: []\n";
                } else {
                    $yaml .= "{$indentStr}{$key}:\n";
                    $yaml .= $this->arrayToYaml($value, $indent + 1);
                }
            } else {
                $yaml .= "{$indentStr}{$key}: " . json_encode($value, JSON_UNESCAPED_UNICODE) . "\n";
            }
        }
        
        return $yaml;
    }
    
    /**
     * 生成Markdown内容
     */
    private function generateMarkdownContent(): string
    {
        $markdown = "# {$this->config['title']}\n\n";
        $markdown .= "{$this->config['description']}\n\n";
        
        $markdown .= "## 基本信息\n\n";
        $markdown .= "- **版本**: {$this->config['version']}\n";
        $markdown .= "- **联系人**: {$this->config['contact']['name']}\n";
        $markdown .= "- **邮箱**: {$this->config['contact']['email']}\n\n";
        
        $markdown .= "## 接口列表\n\n";
        
        foreach ($this->routes as $route => $routeInfo) {
            [$method, $path] = explode(' ', $route, 2);
            
            $markdown .= "### {$routeInfo['summary']}\n\n";
            $markdown .= "**{$method}** `{$path}`\n\n";
            $markdown .= "{$routeInfo['description']}\n\n";
            
            if (!empty($routeInfo['tags'])) {
                $markdown .= "**标签**: " . implode(', ', $routeInfo['tags']) . "\n\n";
            }
        }
        
        return $markdown;
    }
} 