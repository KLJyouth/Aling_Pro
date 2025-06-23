<?php

namespace App\Services;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;

/**
 * 视图渲染服务
 * 基于 Twig 模板引擎的视图渲染系统
 */
class ViewService
{
    private Environment $twig;
    private ConfigService $config;
    private array $globalData = [];

    public function __construct(ConfigService $config)
    {
        $this->config = $config;
        $this->initializeTwig();
    }

    /**
     * 初始化 Twig 环境
     */
    private function initializeTwig(): void
    {
        $loader = new FilesystemLoader([
            $this->config->get('app.views_path', BASE_PATH . '/resources/views'),
            BASE_PATH . '/resources/views/layouts',
            BASE_PATH . '/resources/views/components',
            BASE_PATH . '/resources/views/emails'
        ]);

        $this->twig = new Environment($loader, [
            'cache' => $this->config->get('app.debug') ? false : BASE_PATH . '/storage/cache/views',
            'debug' => $this->config->get('app.debug', false),
            'auto_reload' => $this->config->get('app.debug', false),
            'autoescape' => 'html',
            'charset' => 'UTF-8'
        ]);

        if ($this->config->get('app.debug')) {
            $this->twig->addExtension(new DebugExtension());
        }

        $this->addCustomFunctions();
        $this->addCustomFilters();
        $this->addGlobalVariables();
    }

    /**
     * 添加自定义函数
     */
    private function addCustomFunctions(): void
    {
        // URL 生成函数
        $this->twig->addFunction(new TwigFunction('url', function ($path = '', $params = []) {
            $base = rtrim($this->config->get('app.url', ''), '/');
            $path = ltrim($path, '/');
            $url = $base . '/' . $path;
            
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }
            
            return $url;
        }));

        // 资源URL函数
        $this->twig->addFunction(new TwigFunction('asset', function ($path) {
            $base = rtrim($this->config->get('app.url', ''), '/');
            $path = ltrim($path, '/');
            return $base . '/assets/' . $path;
        }));

        // CSRF令牌函数
        $this->twig->addFunction(new TwigFunction('csrf_token', function () {
            return $_SESSION['csrf_token'] ?? '';
        }));

        // 认证检查函数
        $this->twig->addFunction(new TwigFunction('auth_check', function () {
            return isset($_SESSION['user_id']);
        }));

        // 当前用户函数
        $this->twig->addFunction(new TwigFunction('auth_user', function () {
            return $_SESSION['user'] ?? null;
        }));

        // 配置获取函数
        $this->twig->addFunction(new TwigFunction('config', function ($key, $default = null) {
            return $this->config->get($key, $default);
        }));

        // 翻译函数
        $this->twig->addFunction(new TwigFunction('trans', function ($key, $params = []) {
            // 简单的翻译实现，可以扩展为完整的国际化系统
            $translations = [
                'login' => '登录',
                'register' => '注册',
                'logout' => '退出',
                'dashboard' => '仪表板',
                'admin' => '管理',
                'chat' => '聊天',
                'profile' => '个人资料',
                'settings' => '设置',
                'welcome' => '欢迎',
                'error' => '错误',
                'success' => '成功'
            ];
            
            $text = $translations[$key] ?? $key;
            
            foreach ($params as $param => $value) {
                $text = str_replace(':' . $param, $value, $text);
            }
            
            return $text;
        }));

        // 日期格式化函数
        $this->twig->addFunction(new TwigFunction('date_format', function ($date, $format = 'Y-m-d H:i:s') {
            if (is_string($date)) {
                $date = new \DateTime($date);
            }
            return $date->format($format);
        }));
    }

    /**
     * 添加自定义过滤器
     */
    private function addCustomFilters(): void
    {
        // 文件大小格式化过滤器
        $this->twig->addFilter(new TwigFilter('filesize', function ($bytes) {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $bytes = max($bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            
            $bytes /= pow(1024, $pow);
            
            return round($bytes, 2) . ' ' . $units[$pow];
        }));

        // 时间前格式化过滤器
        $this->twig->addFilter(new TwigFilter('time_ago', function ($datetime) {
            $time = time() - strtotime($datetime);
            
            if ($time < 60) return $time . '秒前';
            if ($time < 3600) return floor($time / 60) . '分钟前';
            if ($time < 86400) return floor($time / 3600) . '小时前';
            if ($time < 2592000) return floor($time / 86400) . '天前';
            if ($time < 31536000) return floor($time / 2592000) . '月前';
            
            return floor($time / 31536000) . '年前';
        }));

        // JSON编码过滤器
        $this->twig->addFilter(new TwigFilter('json_encode', function ($data) {
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }));

        // 截断过滤器
        $this->twig->addFilter(new TwigFilter('truncate', function ($text, $length = 100, $suffix = '...') {
            if (mb_strlen($text) <= $length) {
                return $text;
            }
            return mb_substr($text, 0, $length) . $suffix;
        }));
    }

    /**
     * 添加全局变量
     */
    private function addGlobalVariables(): void
    {
        $this->twig->addGlobal('app', [
            'name' => $this->config->get('app.name', 'AlingAi Pro'),
            'version' => $this->config->get('app.version', '1.0.0'),
            'debug' => $this->config->get('app.debug', false),
            'url' => $this->config->get('app.url', ''),
            'timezone' => $this->config->get('app.timezone', 'Asia/Shanghai')
        ]);
    }

    /**
     * 渲染模板
     */
    public function render(string $template, array $data = []): string
    {
        $data = array_merge($this->globalData, $data);
        
        try {
            return $this->twig->render($template, $data);
        } catch (\Exception $e) {
            if ($this->config->get('app.debug')) {
                throw $e;
            }
            
            // 生产环境下返回错误页面
            return $this->renderError(500, 'Template rendering error');
        }
    }

    /**
     * 渲染错误页面
     */
    public function renderError(int $code, string $message = ''): string
    {
        $templates = [
            404 => 'errors/404.twig',
            403 => 'errors/403.twig',
            500 => 'errors/500.twig'
        ];
        
        $template = $templates[$code] ?? 'errors/default.twig';
        
        try {
            return $this->twig->render($template, [
                'error_code' => $code,
                'error_message' => $message
            ]);
        } catch (\Exception $e) {
            // 如果连错误模板都无法渲染，返回简单的HTML
            return $this->renderSimpleError($code, $message);
        }
    }

    /**
     * 渲染简单错误页面
     */
    private function renderSimpleError(int $code, string $message): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <title>错误 {$code}</title>
            <meta charset='utf-8'>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                .error { color: #dc3545; }
            </style>
        </head>
        <body>
            <h1 class='error'>错误 {$code}</h1>
            <p>{$message}</p>
            <a href='/'>返回首页</a>
        </body>
        </html>";
    }

    /**
     * 添加全局数据
     */
    public function addGlobal(string $key, $value): void
    {
        $this->globalData[$key] = $value;
    }

    /**
     * 检查模板是否存在
     */
    public function exists(string $template): bool
    {
        return $this->twig->getLoader()->exists($template);
    }

    /**
     * 获取 Twig 环境实例
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * 渲染JSON响应
     */
    public function renderJson($data, int $status = 200): string
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * 渲染API响应
     */
    public function renderApiResponse($data = null, string $message = '', int $status = 200): string
    {
        $response = [
            'success' => $status >= 200 && $status < 300,
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        return $this->renderJson($response, $status);
    }
}
