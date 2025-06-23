<?php

namespace AlingAi\Services;

use AlingAi\Services\EnhancedConfigService;

/**
 * API文档生成服务
 */
class APIDocumentationGenerator
{
    private $config;
    private $routes = [];
    private $models = [];
    
    public function __construct()
    {
        $this->config = EnhancedConfigService::getInstance();
    }
    
    /**
     * 注册API路由
     */
    public function registerRoute(array $route): void
    {
        $this->routes[] = $route;
    }
    
    /**
     * 注册数据模型
     */
    public function registerModel(array $model): void
    {
        $this->models[] = $model;
    }
    
    /**
     * 生成OpenAPI文档
     */
    public function generateOpenAPISpec(): array
    {
        $spec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'AlingAi API',
                'description' => 'AlingAi人工智能内容生成平台API文档',
                'version' => '1.0.0',
                'contact' => [
                    'name' => 'AlingAi开发团队',
                    'email' => 'dev@alingai.com'
                ]
            ],
            'servers' => [
                [
                    'url' => $this->config->get('APP_URL', 'http://localhost'),
                    'description' => '生产环境'
                ]
            ],
            'paths' => $this->generatePaths(),
            'components' => [
                'schemas' => $this->generateSchemas(),
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT'
                    ],
                    'apiKey' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'X-API-Key'
                    ]
                ]
            ]
        ];
        
        return $spec;
    }
    
    /**
     * 生成HTML文档
     */
    public function generateHTMLDocumentation(): string
    {
        $spec = $this->generateOpenAPISpec();
        
        $html = '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlingAi API 文档</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { padding: 30px; border-bottom: 1px solid #eee; }
        .header h1 { margin: 0; color: #333; }
        .header p { margin: 10px 0 0; color: #666; }
        .nav { padding: 20px; background: #f8f9fa; border-bottom: 1px solid #eee; }
        .nav a { margin-right: 20px; color: #007bff; text-decoration: none; }
        .nav a:hover { text-decoration: underline; }
        .content { padding: 30px; }
        .endpoint { margin-bottom: 40px; border: 1px solid #e1e5e9; border-radius: 6px; }
        .endpoint-header { padding: 15px; background: #f6f8fa; border-bottom: 1px solid #e1e5e9; }
        .endpoint-method { display: inline-block; padding: 4px 8px; border-radius: 4px; font-weight: bold; color: white; margin-right: 10px; }
        .method-get { background: #28a745; }
        .method-post { background: #007bff; }
        .method-put { background: #ffc107; color: #212529; }
        .method-delete { background: #dc3545; }
        .endpoint-body { padding: 20px; }
        .parameter-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .parameter-table th, .parameter-table td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #e1e5e9; }
        .parameter-table th { background: #f6f8fa; font-weight: 600; }
        .code-block { background: #f6f8fa; padding: 15px; border-radius: 6px; font-family: "SFMono-Regular", Consolas, monospace; overflow-x: auto; }
        .model { margin-bottom: 30px; border: 1px solid #e1e5e9; border-radius: 6px; }
        .model-header { padding: 15px; background: #f6f8fa; border-bottom: 1px solid #e1e5e9; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>' . htmlspecialchars($spec['info']['title']) . '</h1>
            <p>' . htmlspecialchars($spec['info']['description']) . '</p>
            <p><strong>版本:</strong> ' . htmlspecialchars($spec['info']['version']) . '</p>
        </div>
        
        <div class="nav">
            <a href="#endpoints">API端点</a>
            <a href="#models">数据模型</a>
            <a href="#authentication">认证方式</a>
        </div>
        
        <div class="content">
            <h2 id="endpoints">API端点</h2>
            ' . $this->generateEndpointsHTML($spec['paths']) . '
            
            <h2 id="models">数据模型</h2>
            ' . $this->generateModelsHTML($spec['components']['schemas']) . '
            
            <h2 id="authentication">认证方式</h2>
            ' . $this->generateAuthHTML($spec['components']['securitySchemes']) . '
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * 保存文档到文件
     */
    public function saveDocumentation(string $format = 'html'): string
    {
        $docsDir = dirname(__DIR__, 2) . '/docs/api';
        if (!is_dir($docsDir)) {
            mkdir($docsDir, 0755, true);
        }
        
        if ($format === 'json') {
            $content = json_encode($this->generateOpenAPISpec(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $filename = $docsDir . '/openapi.json';
        } else {
            $content = $this->generateHTMLDocumentation();
            $filename = $docsDir . '/index.html';
        }
        
        file_put_contents($filename, $content);
        return $filename;
    }
    
    private function generatePaths(): array
    {
        $paths = [];
        
        foreach ($this->routes as $route) {
            $path = $route['path'];
            $method = strtolower($route['method']);
            
            if (!isset($paths[$path])) {
                $paths[$path] = [];
            }
            
            $paths[$path][$method] = [
                'summary' => $route['summary'] ?? '',
                'description' => $route['description'] ?? '',
                'tags' => $route['tags'] ?? [],
                'parameters' => $route['parameters'] ?? [],
                'requestBody' => $route['requestBody'] ?? null,
                'responses' => $route['responses'] ?? [
                    '200' => [
                        'description' => '成功响应'
                    ]
                ]
            ];
            
            if (isset($route['security'])) {
                $paths[$path][$method]['security'] = $route['security'];
            }
        }
        
        return $paths;
    }
    
    private function generateSchemas(): array
    {
        $schemas = [];
        
        foreach ($this->models as $model) {
            $schemas[$model['name']] = [
                'type' => 'object',
                'description' => $model['description'] ?? '',
                'properties' => $model['properties'] ?? []
            ];
            
            if (isset($model['required'])) {
                $schemas[$model['name']]['required'] = $model['required'];
            }
        }
        
        return $schemas;
    }
    
    private function generateEndpointsHTML(array $paths): string
    {
        $html = '';
        
        foreach ($paths as $path => $methods) {
            foreach ($methods as $method => $details) {
                $methodClass = 'method-' . $method;
                $html .= '<div class="endpoint">';
                $html .= '<div class="endpoint-header">';
                $html .= '<span class="endpoint-method ' . $methodClass . '">' . strtoupper($method) . '</span>';
                $html .= '<span>' . htmlspecialchars($path) . '</span>';
                $html .= '</div>';
                $html .= '<div class="endpoint-body">';
                $html .= '<h4>' . htmlspecialchars($details['summary'] ?? '未命名端点') . '</h4>';
                
                if (!empty($details['description'])) {
                    $html .= '<p>' . htmlspecialchars($details['description']) . '</p>';
                }
                
                if (!empty($details['parameters'])) {
                    $html .= '<h5>参数</h5>';
                    $html .= '<table class="parameter-table">';
                    $html .= '<tr><th>名称</th><th>类型</th><th>位置</th><th>必需</th><th>描述</th></tr>';
                    foreach ($details['parameters'] as $param) {
                        $html .= '<tr>';
                        $html .= '<td>' . htmlspecialchars($param['name'] ?? '') . '</td>';
                        $html .= '<td>' . htmlspecialchars($param['schema']['type'] ?? 'string') . '</td>';
                        $html .= '<td>' . htmlspecialchars($param['in'] ?? 'query') . '</td>';
                        $html .= '<td>' . ($param['required'] ?? false ? '是' : '否') . '</td>';
                        $html .= '<td>' . htmlspecialchars($param['description'] ?? '') . '</td>';
                        $html .= '</tr>';
                    }
                    $html .= '</table>';
                }
                
                $html .= '</div>';
                $html .= '</div>';
            }
        }
        
        return $html;
    }
    
    private function generateModelsHTML(array $schemas): string
    {
        $html = '';
        
        foreach ($schemas as $name => $schema) {
            $html .= '<div class="model">';
            $html .= '<div class="model-header">' . htmlspecialchars($name) . '</div>';
            $html .= '<div class="endpoint-body">';
            
            if (!empty($schema['description'])) {
                $html .= '<p>' . htmlspecialchars($schema['description']) . '</p>';
            }
            
            if (!empty($schema['properties'])) {
                $html .= '<table class="parameter-table">';
                $html .= '<tr><th>属性</th><th>类型</th><th>必需</th><th>描述</th></tr>';
                foreach ($schema['properties'] as $propName => $propDetails) {
                    $html .= '<tr>';
                    $html .= '<td>' . htmlspecialchars($propName) . '</td>';
                    $html .= '<td>' . htmlspecialchars($propDetails['type'] ?? 'string') . '</td>';
                    $html .= '<td>' . (in_array($propName, $schema['required'] ?? []) ? '是' : '否') . '</td>';
                    $html .= '<td>' . htmlspecialchars($propDetails['description'] ?? '') . '</td>';
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }
            
            $html .= '</div>';
            $html .= '</div>';
        }
        
        return $html;
    }
    
    private function generateAuthHTML(array $securitySchemes): string
    {
        $html = '';
        
        foreach ($securitySchemes as $name => $scheme) {
            $html .= '<div class="model">';
            $html .= '<div class="model-header">' . htmlspecialchars($name) . '</div>';
            $html .= '<div class="endpoint-body">';
            $html .= '<p><strong>类型:</strong> ' . htmlspecialchars($scheme['type']) . '</p>';
            
            if (isset($scheme['scheme'])) {
                $html .= '<p><strong>方案:</strong> ' . htmlspecialchars($scheme['scheme']) . '</p>';
            }
            
            if (isset($scheme['in'])) {
                $html .= '<p><strong>位置:</strong> ' . htmlspecialchars($scheme['in']) . '</p>';
            }
            
            if (isset($scheme['name'])) {
                $html .= '<p><strong>名称:</strong> ' . htmlspecialchars($scheme['name']) . '</p>';
            }
            
            $html .= '</div>';
            $html .= '</div>';
        }
        
        return $html;
    }
}
