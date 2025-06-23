<?php

declare(strict_types=1);

namespace AlingAi\Documentation;

use PDO;
use Psr\Log\LoggerInterface;

/**
 * API文档自动生成器
 * 支持OpenAPI 3.0、Postman Collection、SDK生成等功能
 */
class ApiDocumentationGenerator
{
    private PDO $pdo;
    private LoggerInterface $logger;
    private array $config;
    private array $apiEndpoints;
    
    public function __construct(PDO $pdo, LoggerInterface $logger, array $config = [])
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
        $this->config = array_merge([
            'output_dir' => 'public/docs',
            'sdk_dir' => 'public/sdk',
            'openapi_version' => '3.0.3',
            'api_title' => 'AlingAi Pro API',
            'api_version' => '6.0.0',
            'api_description' => 'AlingAi Pro 企业级AI平台API文档',
            'contact_email' => 'support@alingai.com',
            'license_name' => 'MIT',
            'license_url' => 'https://opensource.org/licenses/MIT',
            'server_url' => 'https://api.alingai.com',
            'enable_sdk_generation' => true,
            'supported_languages' => ['php', 'javascript', 'python', 'java', 'csharp', 'go']
        ], $config);
        
        $this->loadApiEndpoints();
    }
    
    /**
     * 生成完整的API文档
     */
    public function generateAll(): array
    {
        $results = [];
        
        try {
            // 生成OpenAPI规范
            $results['openapi'] = $this->generateOpenAPISpec();
            
            // 生成Postman Collection
            $results['postman'] = $this->generatePostmanCollection();
            
            // 生成Markdown文档
            $results['markdown'] = $this->generateMarkdownDocs();
            
            // 生成HTML文档
            $results['html'] = $this->generateHTMLDocs();
            
            // 生成SDK
            if ($this->config['enable_sdk_generation']) {
                $results['sdk'] = $this->generateSDKs();
            }
            
            $this->logger->info('API文档生成完成', $results);
            
        } catch (\Exception $e) {
            $this->logger->error('API文档生成失败', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
        
        return $results;
    }
    
    /**
     * 生成OpenAPI 3.0规范
     */
    public function generateOpenAPISpec(): array
    {
        $openapi = [
            'openapi' => $this->config['openapi_version'],
            'info' => [
                'title' => $this->config['api_title'],
                'version' => $this->config['api_version'],
                'description' => $this->config['api_description'],
                'contact' => [
                    'email' => $this->config['contact_email']
                ],
                'license' => [
                    'name' => $this->config['license_name'],
                    'url' => $this->config['license_url']
                ]
            ],
            'servers' => [
                [
                    'url' => $this->config['server_url'],
                    'description' => '生产环境'
                ]
            ],
            'paths' => $this->generatePaths(),
            'components' => $this->generateComponents(),
            'security' => [
                ['bearerAuth' => []],
                ['apiKeyAuth' => []]
            ]
        ];
        
        $filepath = $this->config['output_dir'] . '/openapi.json';
        $this->ensureDirectoryExists(dirname($filepath));
        file_put_contents($filepath, json_encode($openapi, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return [
            'file' => $filepath,
            'size' => filesize($filepath),
            'endpoints' => count($this->apiEndpoints)
        ];
    }
    
    /**
     * 加载API端点信息
     */
    private function loadApiEndpoints(): void
    {
        $this->apiEndpoints = [
            [
                'path' => '/api/v1/auth/login',
                'method' => 'POST',
                'summary' => '用户登录',
                'description' => '通过用户名和密码进行用户登录认证',
                'tags' => ['认证'],
                'parameters' => [
                    [
                        'name' => 'username',
                        'in' => 'body',
                        'required' => true,
                        'schema' => ['type' => 'string']
                    ],
                    [
                        'name' => 'password',
                        'in' => 'body',
                        'required' => true,
                        'schema' => ['type' => 'string']
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => '登录成功',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'token' => ['type' => 'string'],
                                        'user' => ['type' => 'object']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                'path' => '/api/v1/ai/chat',
                'method' => 'POST',
                'summary' => 'AI对话',
                'description' => '与AI进行对话交互',
                'tags' => ['AI服务'],
                'parameters' => [
                    [
                        'name' => 'message',
                        'in' => 'body',
                        'required' => true,
                        'schema' => ['type' => 'string']
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => '对话成功',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'response' => ['type' => 'string'],
                                        'usage' => ['type' => 'object']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    /**
     * 生成OpenAPI路径
     */
    private function generatePaths(): array
    {
        $paths = [];
        
        foreach ($this->apiEndpoints as $endpoint) {
            $path = $endpoint['path'];
            $method = strtolower($endpoint['method']);
            
            if (!isset($paths[$path])) {
                $paths[$path] = [];
            }
            
            $paths[$path][$method] = [
                'summary' => $endpoint['summary'],
                'description' => $endpoint['description'],
                'tags' => $endpoint['tags'],
                'parameters' => $endpoint['parameters'] ?? [],
                'responses' => $endpoint['responses'] ?? []
            ];
        }
        
        return $paths;
    }
    
    /**
     * 生成OpenAPI组件
     */
    private function generateComponents(): array
    {
        return [
            'securitySchemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT'
                ],
                'apiKeyAuth' => [
                    'type' => 'apiKey',
                    'in' => 'header',
                    'name' => 'X-API-Key'
                ]
            ]
        ];
    }
    
    /**
     * 生成Postman Collection
     */
    public function generatePostmanCollection(): array
    {
        $collection = [
            'info' => [
                'name' => $this->config['api_title'],
                'description' => $this->config['api_description'],
                'version' => $this->config['api_version'],
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
            ],
            'auth' => [
                'type' => 'bearer',
                'bearer' => [
                    [
                        'key' => 'token',
                        'value' => '{{api_token}}',
                        'type' => 'string'
                    ]
                ]
            ],
            'variable' => [
                [
                    'key' => 'base_url',
                    'value' => $this->config['server_url'],
                    'type' => 'string'
                ],
                [
                    'key' => 'api_token',
                    'value' => 'your_api_token_here',
                    'type' => 'string'
                ]
            ],
            'item' => $this->generatePostmanItems()
        ];
        
        $filepath = $this->config['output_dir'] . '/postman_collection.json';
        $this->ensureDirectoryExists(dirname($filepath));
        file_put_contents($filepath, json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return [
            'file' => $filepath,
            'size' => filesize($filepath),
            'requests' => count($this->apiEndpoints)
        ];
    }
    
    /**
     * 生成Postman项目
     */
    private function generatePostmanItems(): array
    {
        $items = [];
        
        foreach ($this->apiEndpoints as $endpoint) {
            $items[] = [
                'name' => $endpoint['summary'],
                'request' => [
                    'method' => $endpoint['method'],
                    'header' => [
                        [
                            'key' => 'Content-Type',
                            'value' => 'application/json'
                        ]
                    ],
                    'url' => [
                        'raw' => '{{base_url}}' . $endpoint['path'],
                        'host' => ['{{base_url}}'],
                        'path' => explode('/', trim($endpoint['path'], '/'))
                    ],
                    'description' => $endpoint['description']
                ]
            ];
        }
        
        return $items;
    }
    
    /**
     * 生成Markdown文档
     */
    public function generateMarkdownDocs(): array
    {
        $docs = [];
        
        // 生成主文档
        $mainDoc = $this->generateMainMarkdownDoc();
        $filepath = $this->config['output_dir'] . '/README.md';
        $this->ensureDirectoryExists(dirname($filepath));
        file_put_contents($filepath, $mainDoc);
        $docs['main'] = $filepath;
        
        return $docs;
    }
    
    /**
     * 生成主Markdown文档
     */
    private function generateMainMarkdownDoc(): string
    {
        return "# {$this->config['api_title']} 文档\n\n" .
               "## 概述\n\n" .
               "{$this->config['api_description']}\n\n" .
               "## 快速开始\n\n" .
               "### 认证\n\n" .
               "所有API请求都需要进行身份验证。支持以下认证方式：\n\n" .
               "- Bearer Token认证\n" .
               "- API Key认证\n\n" .
               "### 基础URL\n\n" .
               "- 生产环境：`{$this->config['server_url']}`\n" .
               "- 开发环境：`https://dev-api.alingai.com`\n\n" .
               "### 请求格式\n\n" .
               "所有请求都应使用JSON格式，并设置以下请求头：\n\n" .
               "```\n" .
               "Content-Type: application/json\n" .
               "Authorization: Bearer YOUR_TOKEN\n" .
               "```\n\n" .
               "## API端点\n\n" .
               $this->generateEndpointsMarkdown() .
               "## 错误处理\n\n" .
               "API使用标准HTTP状态码表示请求结果：\n\n" .
               "- 200: 成功\n" .
               "- 400: 请求参数错误\n" .
               "- 401: 未授权\n" .
               "- 403: 禁止访问\n" .
               "- 404: 资源不存在\n" .
               "- 500: 服务器内部错误\n\n" .
               "## SDK下载\n\n" .
               "我们提供了多种编程语言的SDK：\n\n" .
               "- [PHP SDK](sdk/php/)\n" .
               "- [JavaScript SDK](sdk/javascript/)\n" .
               "- [Python SDK](sdk/python/)\n" .
               "- [Java SDK](sdk/java/)\n" .
               "- [C# SDK](sdk/csharp/)\n" .
               "- [Go SDK](sdk/go/)\n\n" .
               "## 支持\n\n" .
               "如有问题，请联系：{$this->config['contact_email']}\n";
    }
    
    /**
     * 生成端点Markdown
     */
    private function generateEndpointsMarkdown(): string
    {
        $markdown = "";
        
        foreach ($this->apiEndpoints as $endpoint) {
            $markdown .= "### {$endpoint['method']} {$endpoint['path']}\n\n";
            $markdown .= "**{$endpoint['summary']}**\n\n";
            $markdown .= "{$endpoint['description']}\n\n";
            
            if (!empty($endpoint['parameters'])) {
                $markdown .= "**参数：**\n\n";
                foreach ($endpoint['parameters'] as $param) {
                    $markdown .= "- `{$param['name']}` ({$param['in']})" . 
                                ($param['required'] ? ' **必需**' : ' 可选') . 
                                ": {$param['schema']['type']}\n";
                }
                $markdown .= "\n";
            }
            
            $markdown .= "---\n\n";
        }
        
        return $markdown;
    }
    
    /**
     * 生成HTML文档
     */
    public function generateHTMLDocs(): array
    {
        $html = $this->generateHTMLTemplate();
        $filepath = $this->config['output_dir'] . '/index.html';
        $this->ensureDirectoryExists(dirname($filepath));
        file_put_contents($filepath, $html);
        
        return [
            'file' => $filepath,
            'size' => filesize($filepath)
        ];
    }
    
    /**
     * 生成HTML模板
     */
    private function generateHTMLTemplate(): string
    {
        return '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $this->config['api_title'] . '</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <nav class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900">' . $this->config['api_title'] . '</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="#quickstart" class="text-gray-600 hover:text-gray-900">快速开始</a>
                        <a href="#endpoints" class="text-gray-600 hover:text-gray-900">API端点</a>
                        <a href="#sdks" class="text-gray-600 hover:text-gray-900">SDK</a>
                    </div>
                </div>
            </div>
        </nav>
        
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">概述</h2>
                        <p class="text-gray-600 mb-6">' . $this->config['api_description'] . '</p>
                        
                        <div id="quickstart" class="mb-8">
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">快速开始</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">认证</h4>
                                <p class="text-gray-600 mb-4">所有API请求都需要进行身份验证。支持Bearer Token和API Key认证。</p>
                                
                                <h4 class="font-medium text-gray-900 mb-2">基础URL</h4>
                                <ul class="text-gray-600 mb-4">
                                    <li>生产环境：<code class="bg-gray-200 px-2 py-1 rounded">' . $this->config['server_url'] . '</code></li>
                                    <li>开发环境：<code class="bg-gray-200 px-2 py-1 rounded">https://dev-api.alingai.com</code></li>
                                </ul>
                            </div>
                        </div>
                        
                        <div id="endpoints" class="mb-8">
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">API端点</h3>
                            ' . $this->generateEndpointsHTML() . '
                        </div>
                        
                        <div id="sdks" class="mb-8">
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">SDK下载</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div class="border rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-2">PHP SDK</h4>
                                    <a href="sdk/php/" class="text-blue-600 hover:text-blue-800">下载</a>
                                </div>
                                <div class="border rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-2">JavaScript SDK</h4>
                                    <a href="sdk/javascript/" class="text-blue-600 hover:text-blue-800">下载</a>
                                </div>
                                <div class="border rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-2">Python SDK</h4>
                                    <a href="sdk/python/" class="text-blue-600 hover:text-blue-800">下载</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>';
    }
    
    /**
     * 生成端点HTML
     */
    private function generateEndpointsHTML(): string
    {
        $html = "";
        
        foreach ($this->apiEndpoints as $endpoint) {
            $methodColor = $this->getMethodColor($endpoint['method']);
            
            $html .= '<div class="border rounded-lg p-4 mb-4">';
            $html .= '<div class="flex items-center mb-2">';
            $html .= '<span class="px-2 py-1 text-xs font-medium rounded ' . $methodColor . ' mr-2">' . $endpoint['method'] . '</span>';
            $html .= '<code class="text-sm bg-gray-100 px-2 py-1 rounded">' . $endpoint['path'] . '</code>';
            $html .= '</div>';
            $html .= '<h4 class="font-medium text-gray-900 mb-2">' . $endpoint['summary'] . '</h4>';
            $html .= '<p class="text-gray-600 mb-3">' . $endpoint['description'] . '</p>';
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * 获取方法颜色
     */
    private function getMethodColor(string $method): string
    {
        $colors = [
            'GET' => 'bg-green-100 text-green-800',
            'POST' => 'bg-blue-100 text-blue-800',
            'PUT' => 'bg-yellow-100 text-yellow-800',
            'DELETE' => 'bg-red-100 text-red-800',
            'PATCH' => 'bg-purple-100 text-purple-800'
        ];
        
        return $colors[$method] ?? 'bg-gray-100 text-gray-800';
    }
    
    /**
     * 生成SDK
     */
    public function generateSDKs(): array
    {
        $sdks = [];
        
        foreach ($this->config['supported_languages'] as $language) {
            $sdk = $this->generateSDK($language);
            if ($sdk) {
                $sdks[$language] = $sdk;
            }
        }
        
        return $sdks;
    }
    
    /**
     * 生成特定语言的SDK
     */
    private function generateSDK(string $language): ?array
    {
        $sdkDir = $this->config['sdk_dir'] . '/' . $language;
        $this->ensureDirectoryExists($sdkDir);
        
        switch ($language) {
            case 'php':
                return $this->generatePHPSDK($sdkDir);
            case 'javascript':
                return $this->generateJavaScriptSDK($sdkDir);
            default:
                return null;
        }
    }
    
    /**
     * 生成PHP SDK
     */
    private function generatePHPSDK(string $sdkDir): array
    {
        $files = [];
        
        // 生成composer.json
        $composer = [
            'name' => 'alingai/alingai-php-sdk',
            'description' => 'AlingAi Pro PHP SDK',
            'version' => $this->config['api_version'],
            'type' => 'library',
            'license' => $this->config['license_name'],
            'authors' => [
                [
                    'name' => 'AlingAi Team',
                    'email' => $this->config['contact_email']
                ]
            ],
            'require' => [
                'php' => '>=8.0',
                'guzzlehttp/guzzle' => '^7.0',
                'ext-json' => '*'
            ],
            'autoload' => [
                'psr-4' => [
                    'AlingAi\\' => 'src/'
                ]
            ],
            'minimum-stability' => 'stable'
        ];
        
        $files['composer.json'] = $sdkDir . '/composer.json';
        file_put_contents($files['composer.json'], json_encode($composer, JSON_PRETTY_PRINT));
        
        // 生成README
        $readme = $this->generateSDKReadme('PHP');
        $files['README.md'] = $sdkDir . '/README.md';
        file_put_contents($files['README.md'], $readme);
        
        return [
            'directory' => $sdkDir,
            'files' => $files,
            'language' => 'php'
        ];
    }
    
    /**
     * 生成JavaScript SDK
     */
    private function generateJavaScriptSDK(string $sdkDir): array
    {
        $files = [];
        
        // 生成package.json
        $package = [
            'name' => '@alingai/alingai-js-sdk',
            'version' => $this->config['api_version'],
            'description' => 'AlingAi Pro JavaScript SDK',
            'main' => 'dist/index.js',
            'types' => 'dist/index.d.ts',
            'scripts' => [
                'build' => 'tsc',
                'test' => 'jest'
            ],
            'dependencies' => [
                'axios' => '^1.0.0'
            ],
            'devDependencies' => [
                'typescript' => '^4.9.0',
                '@types/node' => '^18.0.0',
                'jest' => '^29.0.0'
            ],
            'keywords' => ['alingai', 'ai', 'api', 'sdk'],
            'author' => 'AlingAi Team',
            'license' => $this->config['license_name']
        ];
        
        $files['package.json'] = $sdkDir . '/package.json';
        file_put_contents($files['package.json'], json_encode($package, JSON_PRETTY_PRINT));
        
        // 生成README
        $readme = $this->generateSDKReadme('JavaScript');
        $files['README.md'] = $sdkDir . '/README.md';
        file_put_contents($files['README.md'], $readme);
        
        return [
            'directory' => $sdkDir,
            'files' => $files,
            'language' => 'javascript'
        ];
    }
    
    /**
     * 生成SDK README
     */
    private function generateSDKReadme(string $language): string
    {
        $languageInfo = [
            'PHP' => [
                'install' => 'composer require alingai/alingai-php-sdk',
                'example' => '<?php
require_once \'vendor/autoload.php\';

use AlingAi\\AlingAiClient;

$client = new AlingAiClient(\'your-api-key\');

// 登录
$result = $client->login(\'username\', \'password\');
$client->setToken($result[\'token\']);

// AI对话
$response = $client->chat(\'Hello, AI!\');
echo $response[\'response\'];'
            ],
            'JavaScript' => [
                'install' => 'npm install @alingai/alingai-js-sdk',
                'example' => 'import AlingAiClient from \'@alingai/alingai-js-sdk\';

const client = new AlingAiClient({
  apiKey: \'your-api-key\'
});

// 登录
const result = await client.login({
  username: \'username\',
  password: \'password\'
});
client.setToken(result.token);

// AI对话
const response = await client.chat({
  message: \'Hello, AI!\'
});
console.log(response.response);'
            ]
        ];
        
        $info = $languageInfo[$language] ?? ['install' => '', 'example' => ''];
        
        return "# AlingAi Pro {$language} SDK\n\n" .
               "## 安装\n\n" .
               "```bash\n" .
               $info['install'] . "\n" .
               "```\n\n" .
               "## 快速开始\n\n" .
               "```{$language}\n" .
               $info['example'] . "\n" .
               "```\n\n" .
               "## API参考\n\n" .
               "查看[完整API文档](../)了解所有可用方法。\n\n" .
               "## 支持\n\n" .
               "如有问题，请联系：{$this->config['contact_email']}\n";
    }
    
    /**
     * 确保目录存在
     */
    private function ensureDirectoryExists(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
