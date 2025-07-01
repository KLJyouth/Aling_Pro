<?php
namespace App\Core;

/**
 * 路由器类
 * 负责处理HTTP请求路由
 */
class Router
{
    /**
     * 存储所有注册的路由
     * @var array
     */
    protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];

    /**
     * 当前路由前缀
     * @var string
     */
    protected $currentPrefix = '';

    /**
     * 路由别名映射
     * @var array
     */
    protected $aliases = [];

    /**
     * 添加路由（支持从bootstrap中注册的路由格式）
     * @param string $uri URI路径
     * @param array|string $handler 处理器配置
     * @param string $method 请求方法，默认为GET
     * @return void
     */
    public function addRoute($uri, $handler, $method = 'GET')
    {
        // 如果handler是数组格式 ['Controller', 'method']
        if (is_array($handler)) {
            $controller = $handler[0];
            $method = $handler[1];
            
            // 转换为Router类期望的格式
            $action = "{$controller}@{$method}";
            
            // 注册路由
            $this->routes['GET'][$this->formatUri($uri)] = $action;
        } else {
            // 直接注册路由（handler已经是字符串格式）
            $this->routes['GET'][$this->formatUri($uri)] = $handler;
        }
    }

    /**
     * 添加GET路由
     * @param string $uri 路由路径
     * @param string $action 控制器动作
     * @return void
     */
    public function get($uri, $action)
    {
        $this->registerRoute('GET', $uri, $action);
    }

    /**
     * 添加POST路由
     * @param string $uri 路由路径
     * @param string $action 控制器动作
     * @return void
     */
    public function post($uri, $action)
    {
        $this->registerRoute('POST', $uri, $action);
    }

    /**
     * 添加PUT路由
     * @param string $uri 路由路径
     * @param string $action 控制器动作
     * @return void
     */
    public function put($uri, $action)
    {
        $this->registerRoute('PUT', $uri, $action);
    }

    /**
     * 添加PATCH路由
     * @param string $uri 路由路径
     * @param string $action 控制器动作
     * @return void
     */
    public function patch($uri, $action)
    {
        $this->registerRoute('PATCH', $uri, $action);
    }

    /**
     * 添加DELETE路由
     * @param string $uri 路由路径
     * @param string $action 控制器动作
     * @return void
     */
    public function delete($uri, $action)
    {
        $this->registerRoute('DELETE', $uri, $action);
    }

    /**
     * 添加路由
     * @param string $method HTTP方法
     * @param string $uri 路由路径
     * @param string $action 控制器动作
     * @return void
     */
    protected function registerRoute($method, $uri, $action)
    {
        // 添加当前前缀
        $uri = $this->formatUri($this->currentPrefix . $uri);
        
        // 注册路由
        $this->routes[$method][$uri] = $action;
    }

    /**
     * 格式化URI
     * @param string $uri URI路径
     * @return string 格式化后的URI
     */
    protected function formatUri($uri)
    {
        // 移除前导斜杠
        $uri = trim($uri, '/');
        // 添加前导斜杠
        return '/' . $uri;
    }

    /**
     * 分发请求到对应的控制器
     * @param string $uri 请求URI
     * @param string $method 请求方法
     * @return void
     */
    public function dispatch($uri, $method)
    {
        // 确保URI是规范化的路径
        $uri = $this->formatUri($uri);
        
        // 先检查路由别名
        if (isset($this->aliases[$uri])) {
            $uri = $this->aliases[$uri];
        }
        
        // 检查是否是静态资源请求
        if ($this->isStaticResource($uri)) {
            // 尝试从public目录提供静态资源
            $publicFilePath = PUBLIC_PATH . $uri;
            if (file_exists($publicFilePath)) {
                $this->serveStaticFile($publicFilePath, pathinfo($uri, PATHINFO_EXTENSION));
                return;
            }
            
            // 尝试从根目录提供静态资源
            $rootFilePath = dirname(PUBLIC_PATH) . $uri;
            if (file_exists($rootFilePath)) {
                $this->serveStaticFile($rootFilePath, pathinfo($uri, PATHINFO_EXTENSION));
                return;
            }
        }
        
        // 检查路由是否存在
        if (isset($this->routes[$method][$uri])) {
            $this->callAction($this->routes[$method][$uri]);
            return;
        }
        
        // 检查是否有匹配的动态路由
        foreach ($this->routes[$method] as $route => $action) {
            if ($this->matchDynamicRoute($route, $uri, $params)) {
                $this->callAction($action, $params);
                return;
            }
        }
        
        // 处理别名路由 - 工具路由
        $toolRouteMap = [
            '/system-info.php' => '/tools/system-info',
            '/database-management.php' => '/tools/database-management',
            '/phpinfo.php' => '/tools/phpinfo',
            '/server-status.php' => '/tools/server-status',
            '/database-info.php' => '/tools/database-info',
            '/cache-optimizer.php' => '/tools/cache-optimizer',
            '/security-checker.php' => '/tools/security-checker',
            '/logs-viewer.php' => '/tools/logs-viewer',
            '/monitoring-dashboard.php' => '/monitoring',
            '/reports-generator.php' => '/reports',
            '/users-manager.php' => '/users',
            '/settings-panel.php' => '/settings'
        ];
        
        // 如果请求了旧版工具路径，重定向到新版路径
        if (isset($toolRouteMap[$uri])) {
            $mappedUri = $toolRouteMap[$uri];
            header("Location: $mappedUri");
            exit;
        }
        
        // 处理静态资源
        $extension = pathinfo($uri, PATHINFO_EXTENSION);
        if ($extension && in_array(strtolower($extension), ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico'])) {
            // 尝试从多个目录查找静态资源
            $searchPaths = [
                PUBLIC_PATH . $uri,                     // public目录
                PUBLIC_PATH . '/assets' . $uri,         // public/assets目录
                dirname(PUBLIC_PATH) . $uri,            // 根目录
                dirname(PUBLIC_PATH) . '/public' . $uri  // 根目录下的public目录
            ];
            
            foreach ($searchPaths as $filePath) {
                if (file_exists($filePath)) {
                    $this->serveStaticFile($filePath, $extension);
                    return;
                }
            }
        }
        
        // 如果没有匹配的路由，显示404错误
        $this->notFound();
    }

    /**
     * 检查是否为静态资源
     * @param string $uri 请求URI
     * @return bool 是否为静态资源
     */
    protected function isStaticResource($uri)
    {
        $staticExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot'];
        $extension = pathinfo($uri, PATHINFO_EXTENSION);
        return in_array(strtolower($extension), $staticExtensions);
    }
    
    /**
     * 提供静态文件
     * @param string $filePath 文件路径
     * @param string $extension 文件扩展名
     * @return void
     */
    protected function serveStaticFile($filePath, $extension)
    {
        // 设置内容类型
        $contentTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon'
        ];
        
        $contentType = $contentTypes[strtolower($extension)] ?? 'application/octet-stream';
        header('Content-Type: ' . $contentType);
        
        // 设置缓存控制
        $expires = 60 * 60 * 24 * 7; // 7天
        header('Cache-Control: public, max-age=' . $expires);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
        
        // 输出文件内容
        readfile($filePath);
        exit;
    }

    /**
     * 匹配动态路由
     * @param string $route 路由模式
     * @param string $uri 请求URI
     * @param array &$params 参数数组（通过引用传递）
     * @return bool 是否匹配成功
     */
    protected function matchDynamicRoute($route, $uri, &$params)
    {
        // 如果路由中不包含参数标记，则不是动态路由
        if (strpos($route, '{') === false) {
            return false;
        }
        
        // 将路由模式转换为正则表达式
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';
        
        // 尝试匹配
        if (preg_match($pattern, $uri, $matches)) {
            // 提取参数
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            return true;
        }
        
        return false;
    }

    /**
     * 调用控制器方法
     * @param string $action 控制器@方法
     * @param array $params 参数数组
     * @return void
     */
    protected function callAction($action, $params = [])
    {
        // 分解控制器和方法
        list($controller, $method) = explode('@', $action);
        
        // 添加命名空间
        $controller = "\\App\\Controllers\\{$controller}";
        
        // 检查控制器是否存在
        if (!class_exists($controller)) {
            $this->serverError("Controller {$controller} not found");
            return;
        }
        
        // 实例化控制器
        $controllerInstance = new $controller();
        
        // 检查方法是否存在
        if (!method_exists($controllerInstance, $method)) {
            $this->serverError("Method {$method} not found in controller {$controller}");
            return;
        }
        
        // 调用方法
        call_user_func_array([$controllerInstance, $method], $params);
    }

    /**
     * 404错误处理
     * @return void
     */
    protected function notFound()
    {
        header('HTTP/1.1 404 Not Found');
        
        // 尝试加载404错误页面
        $errorPagePath = VIEWS_PATH . '/errors/404.php';
        if (file_exists($errorPagePath)) {
            // 设置变量供错误页面使用
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            $referrer = $_SERVER['HTTP_REFERER'] ?? '';
            
            // 包含错误页面
            include $errorPagePath;
        } else {
            // 如果没有自定义404页面，显示简单的错误信息
            echo '<div style="text-align:center; font-family: Arial, sans-serif; margin-top: 50px;">';
            echo '<h1 style="color:#444;">404 - 页面未找到</h1>';
            echo '<p style="color:#666;">抱歉，您请求的页面不存在。</p>';
            echo '<p><a href="/admin" style="color:#0066cc; text-decoration:none;">返回首页</a></p>';
            echo '</div>';
        }
        exit;
    }

    /**
     * 500错误处理
     * @param string $message 错误信息
     * @return void
     */
    protected function serverError($message)
    {
        header('HTTP/1.1 500 Internal Server Error');
        echo '500 - 服务器错误: ' . $message;
        exit;
    }

    /**
     * 路由组
     * @param string $prefix 路由前缀
     * @param callable $callback 回调函数
     * @return void
     */
    public function group($prefix, $callback)
    {
        // 保存当前路由前缀
        $previousPrefix = $this->currentPrefix;
        
        // 设置新路由前缀
        $this->currentPrefix = $previousPrefix . $prefix;
        
        // 调用回调函数
        $callback($this);
        
        // 恢复之前的路由前缀
        $this->currentPrefix = $previousPrefix;
    }

    /**
     * 添加REST路由
     * @param string $uri 路由路径
     * @param string $controller 控制器
     * @return void
     */
    public function resource($uri, $controller)
    {
        // 索引页
        $this->get($uri, $controller . '@index');
        
        // 创建表单
        $this->get($uri . '/create', $controller . '@create');
        
        // 存储资源
        $this->post($uri, $controller . '@store');
        
        // 显示资源
        $this->get($uri . '/{id}', $controller . '@show');
        
        // 编辑表单
        $this->get($uri . '/{id}/edit', $controller . '@edit');
        
        // 更新资源
        $this->put($uri . '/{id}', $controller . '@update');
        $this->patch($uri . '/{id}', $controller . '@update');
        
        // 删除资源
        $this->delete($uri . '/{id}', $controller . '@destroy');
    }

    /**
     * 添加路由别名映射
     * @param string $original 原始路径
     * @param string $alias 别名路径
     * @return void
     */
    public function alias($original, $alias)
    {
        $this->aliases[$alias] = $original;
    }
} 