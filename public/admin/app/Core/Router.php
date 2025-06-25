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
     * 注册GET路由
     * @param string $uri URI路径
     * @param string $controller 控制器@方法
     * @return void
     */
    public function get($uri, $controller)
    {
        $this->routes['GET'][$this->formatUri($uri)] = $controller;
    }

    /**
     * 注册POST路由
     * @param string $uri URI路径
     * @param string $controller 控制器@方法
     * @return void
     */
    public function post($uri, $controller)
    {
        $this->routes['POST'][$this->formatUri($uri)] = $controller;
    }

    /**
     * 注册PUT路由
     * @param string $uri URI路径
     * @param string $controller 控制器@方法
     * @return void
     */
    public function put($uri, $controller)
    {
        $this->routes['PUT'][$this->formatUri($uri)] = $controller;
    }

    /**
     * 注册DELETE路由
     * @param string $uri URI路径
     * @param string $controller 控制器@方法
     * @return void
     */
    public function delete($uri, $controller)
    {
        $this->routes['DELETE'][$this->formatUri($uri)] = $controller;
    }

    /**
     * 格式化URI
     * @param string $uri URI路径
     * @return string 格式化后的URI
     */
    protected function formatUri($uri)
    {
        // 移除前导斜杠
        $uri = trim($uri, '/'];
        // 添加前导斜杠
        return '/' . $uri;
    }

    /**
     * 分发请求到对应的控制�?
     * @param string $uri 请求URI
     * @param string $method 请求方法
     * @return void
     */
    public function dispatch($uri, $method)
    {
        // 获取请求路径部分
        $parsedUrl = parse_url($uri];
        $path = $parsedUrl['path'] ?? '/';
        
        // 检查路由是否存�?
        if (isset($this->routes[$method][$path])) {
            $this->callAction($this->routes[$method][$path]];
            return;
        }
        
        // 检查是否有匹配的动态路�?
        foreach ($this->routes[$method] as $route => $action) {
            if ($this->matchDynamicRoute($route, $path, $params)) {
                $this->callAction($action, $params];
                return;
            }
        }
        
        // 未找到路�?
        $this->notFound(];
    }

    /**
     * 匹配动态路�?
     * @param string $route 路由模式
     * @param string $path 请求路径
     * @param array &$params 参数数组引用
     * @return bool 是否匹配成功
     */
    protected function matchDynamicRoute($route, $path, &$params)
    {
        // 检查路由是否包含动态参�?{param}
        if (strpos($route, '{') === false) {
            return false;
        }
        
        // 将路由模式转换为正则表达�?
        $pattern = preg_replace('/{([a-zA-Z0-9_]+)}/', '([^/]+)', $route];
        $pattern = str_replace('/', '\/', $pattern];
        $pattern = '/^' . $pattern . '$/';
        
        // 尝试匹配
        if (preg_match($pattern, $path, $matches)) {
            array_shift($matches]; // 移除完整匹配
            
            // 提取参数�?
            preg_match_all('/{([a-zA-Z0-9_]+)}/', $route, $paramNames];
            
            // 将参数名和值组�?
            $params = [];
            foreach ($paramNames[1] as $index => $name) {
                $params[$name] = $matches[$index] ?? null;
            }
            
            return true;
        }
        
        return false;
    }

    /**
     * 调用控制器方�?
     * @param string $action 控制器@方法
     * @param array $params 参数数组
     * @return void
     */
    protected function callAction($action, $params = [])
    {
        // 分解控制器和方法
        list($controller, $method) = explode('@', $action];
        
        // 添加命名空间
        $controller = "\\App\\Controllers\\{$controller}";
        
        // 检查控制器是否存在
        if (!class_exists($controller)) {
            $this->serverError("Controller {$controller} not found"];
            return;
        }
        
        // 实例化控制器
        $controllerInstance = new $controller(];
        
        // 检查方法是否存�?
        if (!method_exists($controllerInstance, $method)) {
            $this->serverError("Method {$method} not found in controller {$controller}"];
            return;
        }
        
        // 调用方法
        call_user_func_[[$controllerInstance, $method],  $params];
    }

    /**
     * 404错误处理
     * @return void
     */
    protected function notFound()
    {
        header('HTTP/1.1 404 Not Found'];
        echo '404 - 页面未找�?;
        exit;
    }

    /**
     * 500错误处理
     * @param string $message 错误信息
     * @return void
     */
    protected function serverError($message)
    {
        header('HTTP/1.1 500 Internal Server Error'];
        echo '500 - 服务器错�? ' . $message;
        exit;
    }
} 

