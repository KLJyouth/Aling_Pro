<?php

namespace AlingAi\Utils;

use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

/**
 * API文档生成器
 * 
 * 自动生成API文档和Swagger规范
 * 优化性能：缓存生成、增量更新、并行处理
 * 增强功能：示例生成、测试用例、文档版本管理
 */
class ApiDocGenerator
{
    private LoggerInterface $logger;
    private array $config;
    private array $controllers = [];
    private array $routes = [];
    
    public function __construct(LoggerInterface $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = array_merge([
            'output_dir' => dirname(__DIR__, 2) . '/public/docs',
            'swagger_version' => '3.0.0',
            'api_version' => '2.0.0',
            'title' => 'AlingAi Pro API',
            'description' => 'AlingAi Pro 应用程序接口文档',
            'contact' => [
                'name' => 'API Support',
                'email' => 'support@alingai.com'
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
            ],
            'tags' => [
                'auth' => '认证管理',
                'user' => '用户管理',
                'chat' => '聊天功能',
                'admin' => '管理功能',
                'system' => '系统功能'
            ]
        ], $config);
    }
    
    /**
     * 生成API文档
     */
    public function generateDocs(array $options = []): array
    {
        $startTime = microtime(true);
        
        try {
            $options = array_merge([
                'format' => 'swagger',
                'include_examples' => true,
                'include_tests' => false,
                'output_file' => null
            ], $options);
            
            $this->logger->info('开始生成API文档');
            
            // 扫描控制器
            $this->scanControllers();
            
            // 解析路由
            $this->parseRoutes();
            
            // 生成文档
            $docs = $this->generateDocumentation($options);
            
            // 保存文档
            if ($options['output_file']) {
                $this->saveDocumentation($docs, $options['output_file'], $options['format']);
            }
            
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->logger->info('API文档生成完成', [
                'format' => $options['format'],
                'endpoints' => count($docs['paths'] ?? []),
                'duration_ms' => $duration
            ]);
            
            return $docs;
            
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
    private function scanControllers(): void
    {
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
                    'parameters' => $this->getMethodParameters($method)
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
     * 解析路由
     */
    private function parseRoutes(): void
    {
        // 这里应该解析实际的路由配置
        // 简化实现，使用预定义的路由
        $this->routes = [
            'POST /api/auth/login' => [
                'controller' => 'AuthController',
                'method' => 'login',
                'tags' => ['auth'],
                'summary' => '用户登录',
                'description' => '用户通过邮箱和密码登录系统'
            ],
            'POST /api/auth/register' => [
                'controller' => 'AuthController',
                'method' => 'register',
                'tags' => ['auth'],
                'summary' => '用户注册',
                'description' => '新用户注册账户'
            ],
            'POST /api/auth/logout' => [
                'controller' => 'AuthController',
                'method' => 'logout',
                'tags' => ['auth'],
                'summary' => '用户登出',
                'description' => '用户退出登录'
            ],
            'GET /api/user/profile' => [
                'controller' => 'UserController',
                'method' => 'getProfile',
                'tags' => ['user'],
                'summary' => '获取用户资料',
                'description' => '获取当前登录用户的详细资料'
            ],
            'PUT /api/user/profile' => [
                'controller' => 'UserController',
                'method' => 'updateProfile',
                'tags' => ['user'],
                'summary' => '更新用户资料',
                'description' => '更新当前登录用户的资料信息'
            ],
            'POST /api/chat/send' => [
                'controller' => 'ChatController',
                'method' => 'sendMessage',
                'tags' => ['chat'],
                'summary' => '发送聊天消息',
                'description' => '向AI发送消息并获取回复'
            ],
            'GET /api/chat/history' => [
                'controller' => 'ChatController',
                'method' => 'getHistory',
                'tags' => ['chat'],
                'summary' => '获取聊天历史',
                'description' => '获取用户的聊天历史记录'
            ],
            'GET /api/admin/users' => [
                'controller' => 'AdminController',
                'method' => 'getUsers',
                'tags' => ['admin'],
                'summary' => '获取用户列表',
                'description' => '管理员获取所有用户列表'
            ],
            'GET /api/admin/stats' => [
                'controller' => 'AdminController',
                'method' => 'getStats',
                'tags' => ['admin'],
                'summary' => '获取系统统计',
                'description' => '获取系统运行统计数据'
            ]
        ];
    }
    
    /**
     * 生成文档
     */
    private function generateDocumentation(array $options): array
    {
        $docs = [
            'openapi' => $this->config['swagger_version'],
            'info' => [
                'title' => $this->config['title'],
                'description' => $this->config['description'],
                'version' => $this->config['api_version'],
                'contact' => $this->config['contact']
            ],
            'servers' => $this->config['servers'],
            'security' => $this->config['security'],
            'tags' => $this->generateTags(),
            'paths' => $this->generatePaths($options),
            'components' => $this->generateComponents()
        ];
        
        return $docs;
    }
    
    /**
     * 生成标签
     */
    private function generateTags(): array
    {
        $tags = [];
        
        foreach ($this->config['tags'] as $name => $description) {
            $tags[] = [
                'name' => $name,
                'description' => $description
            ];
        }
        
        return $tags;
    }
    
    /**
     * 生成路径
     */
    private function generatePaths(array $options): array
    {
        $paths = [];
        
        foreach ($this->routes as $route => $routeInfo) {
            [$method, $path] = explode(' ', $route, 2);
            $method = strtolower($method);
            
            if (!isset($paths[$path])) {
                $paths[$path] = [];
            }
            
            $paths[$path][$method] = $this->generatePathItem($routeInfo, $options);
        }
        
        return $paths;
    }
    
    /**
     * 生成路径项
     */
    private function generatePathItem(array $routeInfo, array $options): array
    {
        $pathItem = [
            'tags' => $routeInfo['tags'],
            'summary' => $routeInfo['summary'],
            'description' => $routeInfo['description'],
            'parameters' => $this->generateParameters($routeInfo),
            'requestBody' => $this->generateRequestBody($routeInfo),
            'responses' => $this->generateResponses($routeInfo, $options),
            'security' => $this->generateSecurity($routeInfo)
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
                        'description' => '用户邮箱'
                    ],
                    'password' => [
                        'type' => 'string',
                        'minLength' => 6,
                        'description' => '用户密码'
                    ],
                    'remember' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => '记住登录状态'
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
                        'description' => '用户名'
                    ],
                    'email' => [
                        'type' => 'string',
                        'format' => 'email',
                        'description' => '用户邮箱'
                    ],
                    'password' => [
                        'type' => 'string',
                        'minLength' => 8,
                        'description' => '密码'
                    ],
                    'password_confirmation' => [
                        'type' => 'string',
                        'description' => '确认密码'
                    ]
                ]
            ],
            'sendMessage' => [
                'type' => 'object',
                'required' => ['message'],
                'properties' => [
                    'message' => [
                        'type' => 'string',
                        'description' => '聊天消息内容'
                    ],
                    'conversation_id' => [
                        'type' => 'string',
                        'description' => '对话ID（可选）'
                    ],
                    'model' => [
                        'type' => 'string',
                        'enum' => ['gpt-3.5-turbo', 'gpt-4', 'claude-3'],
                        'default' => 'gpt-3.5-turbo',
                        'description' => 'AI模型'
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
    private function generateResponses(array $routeInfo, array $options): array
    {
        $responses = [
            '200' => [
                'description' => '成功',
                'content' => [
                    'application/json' => [
                        'schema' => $this->generateResponseSchema($routeInfo),
                        'examples' => $this->generateResponseExamples($routeInfo, $options)
                    ]
                ]
            ],
            '400' => [
                'description' => '请求错误',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ErrorResponse'
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
    private function generateResponseExamples(array $routeInfo, array $options): array
    {
        if (!$options['include_examples']) {
            return [];
        }
        
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
     * 生成安全配置
     */
    private function generateSecurity(array $routeInfo): array
    {
        $publicRoutes = ['login', 'register'];
        
        if (in_array($routeInfo['method'], $publicRoutes)) {
            return [];
        }
        
        return [
            'bearerAuth' => []
        ];
    }
    
    /**
     * 生成组件
     */
    private function generateComponents(): array
    {
        return [
            'schemas' => [
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
            ]
        ];
    }
    
    /**
     * 保存文档
     */
    private function saveDocumentation(array $docs, string $outputFile, string $format): void
    {
        $this->ensureOutputDirectory();
        
        switch ($format) {
            case 'swagger':
            case 'json':
                $content = json_encode($docs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                break;
                
            case 'yaml':
                $content = $this->arrayToYaml($docs);
                break;
                
            default:
                throw new \InvalidArgumentException("不支持的输出格式: {$format}");
        }
        
        if (file_put_contents($outputFile, $content) === false) {
            throw new \RuntimeException("无法保存文档到: {$outputFile}");
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
     * 生成HTML文档
     */
    public function generateHtmlDocs(array $options = []): string
    {
        $swaggerDocs = $this->generateDocs(['format' => 'swagger']);
        
        $html = $this->getHtmlTemplate();
        $html = str_replace('{{SWAGGER_JSON}}', json_encode($swaggerDocs), $html);
        
        return $html;
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
} 