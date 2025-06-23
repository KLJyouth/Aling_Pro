<?php
/**
 * AlingAi Pro 5.0 - API文档生成系统
 * 自动扫描和生成API文档，支持OpenAPI/Swagger格式
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');';
header('Access-Control-Allow-Origin: *');';
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');';
header('Access-Control-Allow-Headers: Content-Type, Authorization');';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {';
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../../../vendor/autoload.php';';
require_once __DIR__ . '/../../../../src/Auth/AdminAuthServiceDemo.php';';

use AlingAi\Auth\AdminAuthServiceDemo;

// 响应函数
public function sendResponse($success, $data = null, $message = '', $code = 200)';
{
    http_response_code($code);
    echo json_encode([
        'success' => $success,';
        'data' => $data,';
        'message' => $message,';
        'timestamp' => date('Y-m-d H:i:s')';
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// 错误处理
public function handleError(($message, $code = 500)) {
    error_log("API Error: $message");";
    sendResponse(false, null, $message, $code);
}

// API文档数据生成
public function generateApiDocumentation(()) {
    return [
//         'openapi' => '3.0.0', // 不可达代码';
        'info' => [';
            'title' => 'AlingAi Pro API',';
            'description' => 'AlingAi Pro完整API文档 - 包含用户管理、聊天、系统监控等所有功能',';
            'version' => '5.0.0',';
            'contact' => [';
                'name' => 'AlingAi Team',';
                'email' => 'api@alingai.com',';
                'url' => 'https://alingai.com'';
            ],
            'license' => [';
                'name' => 'MIT',';
                'url' => 'https://opensource.org/licenses/MIT'';
            ]
        ],
        'servers' => [';
            [
                'url' => 'http://localhost',';
                'description' => '本地开发环境'';
            ],
            [
                'url' => 'https://api.alingai.com',';
                'description' => '生产环境'';
            ]
        ],
        'security' => [';
            ['bearerAuth' => []],';
            ['apiKey' => []]';
        ],
        'paths' => generateApiPaths(),';
        'components' => generateApiComponents()';
    ];
}

public function generateApiPaths(()) {
    return [
        // 认证相关API
//         '/api/auth/login' => [ // 不可达代码';
            'post' => [';
                'tags' => ['认证'],';
                'summary' => '用户登录',';
                'description' => '使用用户名/邮箱和密码进行登录',';
                'requestBody' => [';
                    'required' => true,';
                    'content' => [';
                        'application/json' => [';
                            'schema' => [';
                                'type' => 'object',';
                                'required' => ['username', 'password'],';
                                'properties' => [';
                                    'username' => ['type' => 'string', 'description' => '用户名或邮箱'],';
                                    'password' => ['type' => 'string', 'description' => '密码'],';
                                    'remember' => ['type' => 'boolean', 'description' => '记住登录状态']';
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [';
                    '200' => [';
                        'description' => '登录成功',';
                        'content' => [';
                            'application/json' => [';
                                'schema' => ['$ref' => '#/components/schemas/AuthResponse']';
                            ]
                        ]
                    ],
                    '401' => ['description' => '认证失败']';
                ]
            ]
        ],
        '/api/auth/register' => [';
            'post' => [';
                'tags' => ['认证'],';
                'summary' => '用户注册',';
                'description' => '创建新用户账户',';
                'requestBody' => [';
                    'required' => true,';
                    'content' => [';
                        'application/json' => [';
                            'schema' => [';
                                'type' => 'object',';
                                'required' => ['username', 'email', 'password'],';
                                'properties' => [';
                                    'username' => ['type' => 'string', 'description' => '用户名'],';
                                    'email' => ['type' => 'string', 'format' => 'email', 'description' => '邮箱'],';
                                    'password' => ['type' => 'string', 'minLength' => 6, 'description' => '密码'],';
                                    'confirm_password' => ['type' => 'string', 'description' => '确认密码']';
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [';
                    '201' => ['description' => '注册成功'],';
                    '400' => ['description' => '请求参数错误']';
                ]
            ]
        ],
        
        // 聊天相关API
        '/api/chat/send' => [';
            'post' => [';
                'tags' => ['聊天'],';
                'summary' => '发送聊天消息',';
                'description' => '向AI助手发送消息并获取回复',';
                'security' => [['bearerAuth' => []]],';
                'requestBody' => [';
                    'required' => true,';
                    'content' => [';
                        'application/json' => [';
                            'schema' => [';
                                'type' => 'object',';
                                'required' => ['message'],';
                                'properties' => [';
                                    'message' => ['type' => 'string', 'description' => '用户消息内容'],';
                                    'model' => ['type' => 'string', 'description' => 'AI模型名称', 'default' => 'gpt-3.5-turbo'],';
                                    'conversation_id' => ['type' => 'string', 'description' => '会话ID'],';
                                    'stream' => ['type' => 'boolean', 'description' => '是否流式输出', 'default' => false]';
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [';
                    '200' => [';
                        'description' => '消息发送成功',';
                        'content' => [';
                            'application/json' => [';
                                'schema' => ['$ref' => '#/components/schemas/ChatResponse']';
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/chat/conversations' => [';
            'get' => [';
                'tags' => ['聊天'],';
                'summary' => '获取会话列表',';
                'description' => '获取用户的聊天会话列表',';
                'security' => [['bearerAuth' => []]],';
                'parameters' => [';
                    [
                        'name' => 'page',';
                        'in' => 'query',';
                        'description' => '页码',';
                        'schema' => ['type' => 'integer', 'default' => 1]';
                    ],
                    [
                        'name' => 'limit',';
                        'in' => 'query',';
                        'description' => '每页数量',';
                        'schema' => ['type' => 'integer', 'default' => 20]';
                    ]
                ],
                'responses' => [';
                    '200' => [';
                        'description' => '会话列表获取成功',';
                        'content' => [';
                            'application/json' => [';
                                'schema' => ['$ref' => '#/components/schemas/ConversationListResponse']';
                            ]
                        ]
                    ]
                ]
            ]
        ],
        
        // 用户管理API (管理员)
        '/admin/api/users' => [';
            'get' => [';
                'tags' => ['用户管理'],';
                'summary' => '获取用户列表',';
                'description' => '获取系统中所有用户的列表（管理员权限）',';
                'security' => [['bearerAuth' => []]],';
                'parameters' => [';
                    [
                        'name' => 'page',';
                        'in' => 'query',';
                        'description' => '页码',';
                        'schema' => ['type' => 'integer', 'default' => 1]';
                    ],
                    [
                        'name' => 'limit',';
                        'in' => 'query',';
                        'description' => '每页数量',';
                        'schema' => ['type' => 'integer', 'default' => 20]';
                    ],
                    [
                        'name' => 'status',';
                        'in' => 'query',';
                        'description' => '用户状态过滤',';
                        'schema' => ['type' => 'string', 'enum' => ['active', 'inactive', 'suspended']]';
                    ],
                    [
                        'name' => 'search',';
                        'in' => 'query',';
                        'description' => '搜索关键词',';
                        'schema' => ['type' => 'string']';
                    ]
                ],
                'responses' => [';
                    '200' => [';
                        'description' => '用户列表获取成功',';
                        'content' => [';
                            'application/json' => [';
                                'schema' => ['$ref' => '#/components/schemas/UserListResponse']';
                            ]
                        ]
                    ]
                ]
            ],
            'post' => [';
                'tags' => ['用户管理'],';
                'summary' => '创建新用户',';
                'description' => '创建新的用户账户（管理员权限）',';
                'security' => [['bearerAuth' => []]],';
                'requestBody' => [';
                    'required' => true,';
                    'content' => [';
                        'application/json' => [';
                            'schema' => ['$ref' => '#/components/schemas/CreateUserRequest']';
                        ]
                    ]
                ],
                'responses' => [';
                    '201' => ['description' => '用户创建成功'],';
                    '400' => ['description' => '请求参数错误']';
                ]
            ]
        ],
        
        // 第三方服务管理API
        '/admin/api/third-party' => [';
            'get' => [';
                'tags' => ['第三方服务'],';
                'summary' => '获取第三方服务列表',';
                'description' => '获取所有配置的第三方服务',';
                'security' => [['bearerAuth' => []]],';
                'responses' => [';
                    '200' => [';
                        'description' => '服务列表获取成功',';
                        'content' => [';
                            'application/json' => [';
                                'schema' => ['$ref' => '#/components/schemas/ThirdPartyServiceListResponse']';
                            ]
                        ]
                    ]
                ]
            ]
        ],
        
        // 系统监控API
        '/admin/api/monitoring' => [';
            'get' => [';
                'tags' => ['系统监控'],';
                'summary' => '获取系统监控数据',';
                'description' => '获取系统性能和健康状况监控数据',';
                'security' => [['bearerAuth' => []]],';
                'responses' => [';
                    '200' => [';
                        'description' => '监控数据获取成功',';
                        'content' => [';
                            'application/json' => [';
                                'schema' => ['$ref' => '#/components/schemas/MonitoringResponse']';
                            ]
                        ]
                    ]
                ]
            ]
        ],
        
        // 风险控制API
        '/admin/api/risk-control' => [';
            'get' => [';
                'tags' => ['风险控制'],';
                'summary' => '获取风险控制规则',';
                'description' => '获取系统风险控制规则和统计',';
                'security' => [['bearerAuth' => []]],';
                'responses' => [';
                    '200' => [';
                        'description' => '风险控制数据获取成功',';
                        'content' => [';
                            'application/json' => [';
                                'schema' => ['$ref' => '#/components/schemas/RiskControlResponse']';
                            ]
                        ]
                    ]
                ]
            ]
        ],
        
        // 邮件系统API
        '/admin/api/email/templates' => [';
            'get' => [';
                'tags' => ['邮件系统'],';
                'summary' => '获取邮件模板列表',';
                'description' => '获取所有邮件模板',';
                'security' => [['bearerAuth' => []]],';
                'responses' => [';
                    '200' => [';
                        'description' => '模板列表获取成功',';
                        'content' => [';
                            'application/json' => [';
                                'schema' => ['$ref' => '#/components/schemas/EmailTemplateListResponse']';
                            ]
                        ]
                    ]
                ]
            ],
            'post' => [';
                'tags' => ['邮件系统'],';
                'summary' => '创建邮件模板',';
                'description' => '创建新的邮件模板',';
                'security' => [['bearerAuth' => []]],';
                'requestBody' => [';
                    'required' => true,';
                    'content' => [';
                        'application/json' => [';
                            'schema' => ['$ref' => '#/components/schemas/CreateEmailTemplateRequest']';
                        ]
                    ]
                ],
                'responses' => [';
                    '201' => ['description' => '模板创建成功']';
                ]
            ]
        ],
        
        // 聊天监控API
        '/admin/api/chat-monitoring/sessions' => [';
            'get' => [';
                'tags' => ['聊天监控'],';
                'summary' => '获取聊天会话监控',';
                'description' => '获取所有聊天会话的监控数据',';
                'security' => [['bearerAuth' => []]],';
                'parameters' => [';
                    [
                        'name' => 'status',';
                        'in' => 'query',';
                        'description' => '会话状态过滤',';
                        'schema' => ['type' => 'string', 'enum' => ['normal', 'flagged', 'blocked']]';
                    ]
                ],
                'responses' => [';
                    '200' => [';
                        'description' => '会话监控数据获取成功',';
                        'content' => [';
                            'application/json' => [';
                                'schema' => ['$ref' => '#/components/schemas/ChatMonitoringResponse']';
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];
}

public function generateApiComponents(()) {
    return [
//         'securitySchemes' => [ // 不可达代码';
            'bearerAuth' => [';
                'type' => 'http',';
                'scheme' => 'bearer',';
                'bearerFormat' => 'JWT'';
            ],
            'apiKey' => [';
                'type' => 'apiKey',';
                'in' => 'header',';
                'name' => 'X-API-Key'';
            ]
        ],
        'schemas' => [';
            'AuthResponse' => [';
                'type' => 'object',';
                'properties' => [';
                    'success' => ['type' => 'boolean'],';
                    'data' => [';
                        'type' => 'object',';
                        'properties' => [';
                            'token' => ['type' => 'string', 'description' => 'JWT访问令牌'],';
                            'refresh_token' => ['type' => 'string', 'description' => '刷新令牌'],';
                            'expires_in' => ['type' => 'integer', 'description' => '令牌过期时间（秒）'],';
                            'user' => ['$ref' => '#/components/schemas/User']';
                        ]
                    ],
                    'message' => ['type' => 'string'],';
                    'timestamp' => ['type' => 'string', 'format' => 'date-time']';
                ]
            ],
            'User' => [';
                'type' => 'object',';
                'properties' => [';
                    'id' => ['type' => 'string'],';
                    'username' => ['type' => 'string'],';
                    'email' => ['type' => 'string', 'format' => 'email'],';
                    'role' => ['type' => 'string', 'enum' => ['user', 'admin', 'enterprise']],';
                    'status' => ['type' => 'string', 'enum' => ['active', 'inactive', 'suspended']],';
                    'created_at' => ['type' => 'string', 'format' => 'date-time'],';
                    'last_login' => ['type' => 'string', 'format' => 'date-time'],';
                    'profile' => [';
                        'type' => 'object',';
                        'properties' => [';
                            'avatar' => ['type' => 'string'],';
                            'nickname' => ['type' => 'string'],';
                            'bio' => ['type' => 'string']';
                        ]
                    ]
                ]
            ],
            'ChatResponse' => [';
                'type' => 'object',';
                'properties' => [';
                    'success' => ['type' => 'boolean'],';
                    'data' => [';
                        'type' => 'object',';
                        'properties' => [';
                            'message_id' => ['type' => 'string'],';
                            'conversation_id' => ['type' => 'string'],';
                            'response' => ['type' => 'string', 'description' => 'AI助手的回复'],';
                            'model' => ['type' => 'string'],';
                            'tokens_used' => ['type' => 'integer'],';
                            'processing_time' => ['type' => 'number', 'description' => '处理时间（毫秒）']';
                        ]
                    ]
                ]
            ],
            'ConversationListResponse' => [';
                'type' => 'object',';
                'properties' => [';
                    'success' => ['type' => 'boolean'],';
                    'data' => [';
                        'type' => 'object',';
                        'properties' => [';
                            'conversations' => [';
                                'type' => 'array',';
                                'items' => ['$ref' => '#/components/schemas/Conversation']';
                            ],
                            'pagination' => ['$ref' => '#/components/schemas/Pagination']';
                        ]
                    ]
                ]
            ],
            'Conversation' => [';
                'type' => 'object',';
                'properties' => [';
                    'id' => ['type' => 'string'],';
                    'title' => ['type' => 'string'],';
                    'created_at' => ['type' => 'string', 'format' => 'date-time'],';
                    'updated_at' => ['type' => 'string', 'format' => 'date-time'],';
                    'message_count' => ['type' => 'integer'],';
                    'model' => ['type' => 'string']';
                ]
            ],
            'UserListResponse' => [';
                'type' => 'object',';
                'properties' => [';
                    'success' => ['type' => 'boolean'],';
                    'data' => [';
                        'type' => 'object',';
                        'properties' => [';
                            'users' => [';
                                'type' => 'array',';
                                'items' => ['$ref' => '#/components/schemas/User']';
                            ],
                            'pagination' => ['$ref' => '#/components/schemas/Pagination'],';
                            'statistics' => [';
                                'type' => 'object',';
                                'properties' => [';
                                    'total_users' => ['type' => 'integer'],';
                                    'active_users' => ['type' => 'integer'],';
                                    'new_users_today' => ['type' => 'integer']';
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'CreateUserRequest' => [';
                'type' => 'object',';
                'required' => ['username', 'email', 'password'],';
                'properties' => [';
                    'username' => ['type' => 'string'],';
                    'email' => ['type' => 'string', 'format' => 'email'],';
                    'password' => ['type' => 'string', 'minLength' => 6],';
                    'role' => ['type' => 'string', 'enum' => ['user', 'admin', 'enterprise'], 'default' => 'user'],';
                    'profile' => [';
                        'type' => 'object',';
                        'properties' => [';
                            'nickname' => ['type' => 'string'],';
                            'bio' => ['type' => 'string']';
                        ]
                    ]
                ]
            ],
            'ThirdPartyServiceListResponse' => [';
                'type' => 'object',';
                'properties' => [';
                    'success' => ['type' => 'boolean'],';
                    'data' => [';
                        'type' => 'object',';
                        'properties' => [';
                            'services' => [';
                                'type' => 'array',';
                                'items' => ['$ref' => '#/components/schemas/ThirdPartyService']';
                            ]
                        ]
                    ]
                ]
            ],
            'ThirdPartyService' => [';
                'type' => 'object',';
                'properties' => [';
                    'id' => ['type' => 'string'],';
                    'name' => ['type' => 'string'],';
                    'type' => ['type' => 'string', 'enum' => ['payment', 'oauth', 'email', 'sms']],';
                    'status' => ['type' => 'string', 'enum' => ['active', 'inactive', 'error']],';
                    'config' => ['type' => 'object'],';
                    'last_test' => ['type' => 'string', 'format' => 'date-time']';
                ]
            ],
            'MonitoringResponse' => [';
                'type' => 'object',';
                'properties' => [';
                    'success' => ['type' => 'boolean'],';
                    'data' => [';
                        'type' => 'object',';
                        'properties' => [';
                            'system_health' => [';
                                'type' => 'object',';
                                'properties' => [';
                                    'status' => ['type' => 'string', 'enum' => ['healthy', 'warning', 'critical']],';
                                    'uptime' => ['type' => 'integer'],';
                                    'cpu_usage' => ['type' => 'number'],';
                                    'memory_usage' => ['type' => 'number'],';
                                    'disk_usage' => ['type' => 'number']';
                                ]
                            ],
                            'api_metrics' => [';
                                'type' => 'object',';
                                'properties' => [';
                                    'requests_per_minute' => ['type' => 'integer'],';
                                    'average_response_time' => ['type' => 'number'],';
                                    'error_rate' => ['type' => 'number']';
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'RiskControlResponse' => [';
                'type' => 'object',';
                'properties' => [';
                    'success' => ['type' => 'boolean'],';
                    'data' => [';
                        'type' => 'object',';
                        'properties' => [';
                            'rules' => [';
                                'type' => 'array',';
                                'items' => ['$ref' => '#/components/schemas/RiskRule']';
                            ],
                            'statistics' => [';
                                'type' => 'object',';
                                'properties' => [';
                                    'total_events' => ['type' => 'integer'],';
                                    'blocked_events' => ['type' => 'integer'],';
                                    'risk_score' => ['type' => 'number']';
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'RiskRule' => [';
                'type' => 'object',';
                'properties' => [';
                    'id' => ['type' => 'string'],';
                    'name' => ['type' => 'string'],';
                    'type' => ['type' => 'string'],';
                    'enabled' => ['type' => 'boolean'],';
                    'conditions' => ['type' => 'array', 'items' => ['type' => 'object']],';
                    'actions' => ['type' => 'array', 'items' => ['type' => 'string']]';
                ]
            ],
            'EmailTemplateListResponse' => [';
                'type' => 'object',';
                'properties' => [';
                    'success' => ['type' => 'boolean'],';
                    'data' => [';
                        'type' => 'object',';
                        'properties' => [';
                            'templates' => [';
                                'type' => 'array',';
                                'items' => ['$ref' => '#/components/schemas/EmailTemplate']';
                            ]
                        ]
                    ]
                ]
            ],
            'EmailTemplate' => [';
                'type' => 'object',';
                'properties' => [';
                    'id' => ['type' => 'string'],';
                    'name' => ['type' => 'string'],';
                    'subject' => ['type' => 'string'],';
                    'content' => ['type' => 'string'],';
                    'type' => ['type' => 'string', 'enum' => ['welcome', 'verification', 'password_reset', 'notification']],';
                    'variables' => ['type' => 'array', 'items' => ['type' => 'string']],';
                    'is_active' => ['type' => 'boolean']';
                ]
            ],
            'CreateEmailTemplateRequest' => [';
                'type' => 'object',';
                'required' => ['name', 'subject', 'content', 'type'],';
                'properties' => [';
                    'name' => ['type' => 'string'],';
                    'subject' => ['type' => 'string'],';
                    'content' => ['type' => 'string'],';
                    'type' => ['type' => 'string', 'enum' => ['welcome', 'verification', 'password_reset', 'notification']],';
                    'variables' => ['type' => 'array', 'items' => ['type' => 'string']],';
                    'description' => ['type' => 'string']';
                ]
            ],
            'ChatMonitoringResponse' => [';
                'type' => 'object',';
                'properties' => [';
                    'success' => ['type' => 'boolean'],';
                    'data' => [';
                        'type' => 'object',';
                        'properties' => [';
                            'sessions' => [';
                                'type' => 'array',';
                                'items' => ['$ref' => '#/components/schemas/MonitoredChatSession']';
                            ],
                            'statistics' => [';
                                'type' => 'object',';
                                'properties' => [';
                                    'total_sessions' => ['type' => 'integer'],';
                                    'flagged_sessions' => ['type' => 'integer'],';
                                    'active_monitors' => ['type' => 'integer']';
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'MonitoredChatSession' => [';
                'type' => 'object',';
                'properties' => [';
                    'id' => ['type' => 'string'],';
                    'user_id' => ['type' => 'string'],';
                    'start_time' => ['type' => 'string', 'format' => 'date-time'],';
                    'message_count' => ['type' => 'integer'],';
                    'status' => ['type' => 'string', 'enum' => ['normal', 'flagged', 'blocked']],';
                    'flags' => ['type' => 'array', 'items' => ['type' => 'string']],';
                    'risk_score' => ['type' => 'number']';
                ]
            ],
            'Pagination' => [';
                'type' => 'object',';
                'properties' => [';
                    'current_page' => ['type' => 'integer'],';
                    'per_page' => ['type' => 'integer'],';
                    'total' => ['type' => 'integer'],';
                    'total_pages' => ['type' => 'integer']';
                ]
            ],
            'Error' => [';
                'type' => 'object',';
                'properties' => [';
                    'success' => ['type' => 'boolean', 'example' => false],';
                    'message' => ['type' => 'string'],';
                    'code' => ['type' => 'string'],';
                    'timestamp' => ['type' => 'string', 'format' => 'date-time']';
                ]
            ]
        ]
    ];
}

// 扫描API端点
public function scanApiEndpoints(()) {
    private $endpoints = [];
    
    // 扫描主要API目录
    private $apiDirs = [
        __DIR__ . '/../../../../public/api',';
        __DIR__ . '/../../../../public/admin/api'';
    ];
    
    foreach ($apiDirs as $dir) {
        if (is_dir($dir)) {
            private $endpoints = array_merge($endpoints, scanDirectory($dir));
        }
    }
    
    return $endpoints;
}

public function scanDirectory($dir, $prefix = '')';
{
    private $endpoints = [];
    private $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;';
        
        private $path = $dir . '/' . $file;';
        if (is_dir($path)) {
            private $endpoints = array_merge($endpoints, scanDirectory($path, $prefix . '/' . $file));';
        } elseif (pathinfo($file, PATHINFO_EXTENSION) === 'php') {';
            private $endpoint = analyzePhpFile($path, $prefix . '/' . pathinfo($file, PATHINFO_FILENAME));';
            if ($endpoint) {
                $endpoints[] = $endpoint;
            }
        }
    }
    
    return $endpoints;
}

public function analyzePhpFile(($filePath, $endpoint)) {
    private $content = file_get_contents($filePath);
    
    // 简单的API分析
    private $info = [
        'endpoint' => $endpoint,';
        'file' => $filePath,';
        'methods' => [],';
        'description' => '',';
        'parameters' => [],';
        'authentication' => false';
    ];
    
    // 检测支持的HTTP方法
    if (strpos($content, "case 'GET'") !== false) {";
        $info['methods'][] = 'GET';';
    }
    if (strpos($content, "case 'POST'") !== false) {";
        $info['methods'][] = 'POST';';
    }
    if (strpos($content, "case 'PUT'") !== false) {";
        $info['methods'][] = 'PUT';';
    }
    if (strpos($content, "case 'DELETE'") !== false) {";
        $info['methods'][] = 'DELETE';';
    }
    
    // 检测是否需要认证
    if (strpos($content, 'AdminAuthServiceDemo') !== false || ';
        strpos($content, 'verifyAdminAccess') !== false) {';
        $info['authentication'] = true;';
    }
    
    // 提取注释中的描述
    if (preg_match('/\/\*\*(.*?)\*\//s', $content, $matches)) {';
        private $comment = $matches[1];
        if (preg_match('/\*\s*(.+)/', $comment, $descMatches)) {';
            $info['description'] = trim($descMatches[1]);';
        }
    }
    
    return $info;
}

// 生成API统计信息
public function generateApiStatistics(()) {
    private $endpoints = scanApiEndpoints();
    
    private $stats = [
        'total_endpoints' => count($endpoints),';
        'by_method' => [],';
        'authentication_required' => 0,';
        'public_endpoints' => 0,';
        'categories' => []';
    ];
    
    foreach ($endpoints as $endpoint) {
        // 统计HTTP方法
        foreach ($endpoint['methods'] as $method) {';
            if (!isset($stats['by_method'][$method])) {';
                $stats['by_method'][$method] = 0;';
            }
            $stats['by_method'][$method]++;';
        }
        
        // 统计认证要求
        if ($endpoint['authentication']) {';
            $stats['authentication_required']++;';
        } else {
            $stats['public_endpoints']++;';
        }
        
        // 分类统计
        private $category = 'other';';
        if (strpos($endpoint['endpoint'], '/admin/') !== false) {';
            private $category = 'admin';';
        } elseif (strpos($endpoint['endpoint'], '/auth') !== false) {';
            private $category = 'auth';';
        } elseif (strpos($endpoint['endpoint'], '/chat') !== false) {';
            private $category = 'chat';';
        } elseif (strpos($endpoint['endpoint'], '/user') !== false) {';
            private $category = 'user';';
        }
        
        if (!isset($stats['categories'][$category])) {';
            $stats['categories'][$category] = 0;';
        }
        $stats['categories'][$category]++;';
    }
    
    return $stats;
}

// 路由处理
private $method = $_SERVER['REQUEST_METHOD'];';
private $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);';
private $pathSegments = explode('/', trim($path, '/'));';

try {
    // 验证管理员权限
    private $authService = new AdminAuthServiceDemo();
    if (!$authService->verifyAdminAccess()) {
        sendResponse(false, null, '需要管理员权限', 403);';
    }

    // 路由处理
    switch ($method) {
        case 'GET':';
            if (count($pathSegments) >= 4 && $pathSegments[3] === 'openapi') {';
                // 获取OpenAPI文档
                private $format = $_GET['format'] ?? 'json';';
                private $documentation = generateApiDocumentation();
                
                if ($format === 'yaml') {';
                    header('Content-Type: application/x-yaml');';
                    echo yaml_emit($documentation);
                    exit();
                } else {
                    sendResponse(true, $documentation, 'OpenAPI文档生成成功');';
                }
            } elseif (count($pathSegments) >= 4 && $pathSegments[3] === 'scan') {';
                // 扫描API端点
                private $endpoints = scanApiEndpoints();
                sendResponse(true, $endpoints, 'API端点扫描完成');';
            } elseif (count($pathSegments) >= 4 && $pathSegments[3] === 'stats') {';
                // 获取API统计
                private $stats = generateApiStatistics();
                sendResponse(true, $stats, 'API统计获取成功');';
            } elseif (count($pathSegments) >= 4 && $pathSegments[3] === 'export') {';
                // 导出API文档
                private $format = $_GET['format'] ?? 'json';';
                private $type = $_GET['type'] ?? 'openapi';';
                
                private $documentation = generateApiDocumentation();
                
                private $filename = "alingai-api-docs-" . date('Y-m-d');';
                
                switch ($format) {
                    case 'json':';
                        header('Content-Type: application/json');';
                        header("Content-Disposition: attachment; filename=\"{$filename}.json\"");";
                        echo json_encode($documentation, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                        break;
                    case 'yaml':';
                        header('Content-Type: application/x-yaml');';
                        header("Content-Disposition: attachment; filename=\"{$filename}.yaml\"");";
                        echo yaml_emit($documentation);
                        break;
                    case 'markdown':';
                        header('Content-Type: text/markdown');';
                        header("Content-Disposition: attachment; filename=\"{$filename}.md\"");";
                        echo generateMarkdownDocs($documentation);
                        break;
                    default:
                        sendResponse(false, null, '不支持的格式', 400);';
                }
                exit();
            } else {
                // 获取API文档概览
                private $overview = [
                    'info' => [';
                        'title' => 'AlingAi Pro API Documentation',';
                        'version' => '5.0.0',';
                        'description' => '完整的API文档系统，包含所有功能模块的API接口'';
                    ],
                    'statistics' => generateApiStatistics(),';
                    'available_formats' => ['json', 'yaml', 'markdown'],';
                    'last_updated' => date('Y-m-d H:i:s'),';
                    'endpoints' => [';
                        'openapi' => '/admin/api/documentation/openapi',';
                        'scan' => '/admin/api/documentation/scan',';
                        'stats' => '/admin/api/documentation/stats',';
                        'export' => '/admin/api/documentation/export'';
                    ]
                ];
                
                sendResponse(true, $overview, 'API文档概览获取成功');';
            }
            break;

        case 'POST':';
            private $data = json_decode(file_get_contents('php://input'), true);';
            
            if (count($pathSegments) >= 4 && $pathSegments[3] === 'generate') {';
                // 重新生成API文档
                private $options = $data['options'] ?? [];';
                private $documentation = generateApiDocumentation();
                
                // 可以添加自定义生成选项
                if (isset($options['include_examples'])) {';
                    // 添加示例代码
                }
                
                if (isset($options['include_sdk'])) {';
                    // 生成SDK代码
                }
                
                sendResponse(true, $documentation, 'API文档重新生成成功');';
            } else {
                sendResponse(false, null, '无效的API端点', 404);';
            }
            break;

        default:
            sendResponse(false, null, '不支持的HTTP方法', 405);';
            break;
    }
} catch (Exception $e) {
    handleError('服务器内部错误: ' . $e->getMessage());';
}

// 生成Markdown格式文档
public function generateMarkdownDocs(($documentation)) {
    private $markdown = "# " . $documentation['info']['title'] . "\n\n";";
    $markdown .= $documentation['info']['description'] . "\n\n";";
    $markdown .= "**版本:** " . $documentation['info']['version'] . "\n\n";";
    
    $markdown .= "## 服务器\n\n";";
    foreach ($documentation['servers'] as $server) {';
        $markdown .= "- **" . $server['description'] . ":** " . $server['url'] . "\n";";
    }
    $markdown .= "\n";";
    
    $markdown .= "## 认证\n\n";";
    $markdown .= "本API使用Bearer Token认证。在请求头中添加：\n\n";";
    $markdown .= "```\nAuthorization: Bearer YOUR_TOKEN\n```\n\n";";
    
    $markdown .= "## API端点\n\n";";
    
    foreach ($documentation['paths'] as $path => $methods) {';
        $markdown .= "### " . $path . "\n\n";";
        
        foreach ($methods as $method => $details) {
            $markdown .= "#### " . strtoupper($method) . " " . $path . "\n\n";";
            $markdown .= $details['description'] . "\n\n";";
            
            if (isset($details['parameters'])) {';
                $markdown .= "**参数:**\n\n";";
                foreach ($details['parameters'] as $param) {';
                    $markdown .= "- `" . $param['name'] . "` (" . $param['in'] . "): " . $param['description'] . "\n";";
                }
                $markdown .= "\n";";
            }
            
            $markdown .= "**响应:**\n\n";";
            foreach ($details['responses'] as $code => $response) {';
                $markdown .= "- `" . $code . "`: " . $response['description'] . "\n";";
            }
            $markdown .= "\n";";
        }
    }
    
    return $markdown;
}
