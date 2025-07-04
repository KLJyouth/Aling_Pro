<?php
/**
 * 路由器类
 * 
 * 负责处理路由请求
 * 
 * @package App\Core
 */

namespace App\Core;

class Router
{
    /**
     * 路由集合
     * 
     * @var array
     */
    protected $routes = [];
    
    /**
     * 当前请求的URI
     * 
     * @var string
     */
    protected $requestUri;
    
    /**
     * 当前请求的方法
     * 
     * @var string
     */
    protected $requestMethod;
    
    /**
     * 基础路径
     * 
     * @var string
     */
    protected $basePath = "";
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->requestUri = $this->getRequestUri();
        $this->requestMethod = $_SERVER["REQUEST_METHOD"] ?? "GET";
    }
    
    /**
     * 设置基础路径
     * 
     * @param string $basePath 基础路径
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
        return $this;
    }
    
    /**
     * 获取请求URI
     * 
     * @return string
     */
    protected function getRequestUri()
    {
        $uri = $_SERVER["REQUEST_URI"] ?? "/";
        
        // 移除查询字符串
        $position = strpos($uri, "?");
        if ($position !== false) {
            $uri = substr($uri, 0, $position);
        }
        
        // 移除基础路径
        if (!empty($this->basePath) && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }
        
        // 确保URI以/开头
        if (empty($uri) || $uri[0] !== "/") {
            $uri = "/" . $uri;
        }
        
        return $uri;
    }
    
    /**
     * 添加GET路由
     * 
     * @param string $pattern 路由模式
     * @param mixed $handler 处理器
     * @return $this
     */
    public function get($pattern, $handler)
    {
        return $this->addRoute("GET", $pattern, $handler);
    }
    
    /**
     * 添加POST路由
     * 
     * @param string $pattern 路由模式
     * @param mixed $handler 处理器
     * @return $this
     */
    public function post($pattern, $handler)
    {
        return $this->addRoute("POST", $pattern, $handler);
    }
    
    /**
     * 添加PUT路由
     * 
     * @param string $pattern 路由模式
     * @param mixed $handler 处理器
     * @return $this
     */
    public function put($pattern, $handler)
    {
        return $this->addRoute("PUT", $pattern, $handler);
    }
    
    /**
     * 添加DELETE路由
     * 
     * @param string $pattern 路由模式
     * @param mixed $handler 处理器
     * @return $this
     */
    public function delete($pattern, $handler)
    {
        return $this->addRoute("DELETE", $pattern, $handler);
    }
    
    /**
     * 添加任意方法路由
     * 
     * @param string $pattern 路由模式
     * @param mixed $handler 处理器
     * @return $this
     */
    public function any($pattern, $handler)
    {
        return $this->addRoute("ANY", $pattern, $handler);
    }
    
    /**
     * 添加路由
     * 
     * @param string $method 请求方法
     * @param string $pattern 路由模式
     * @param mixed $handler 处理器
     * @return $this
     */
    public function addRoute($method, $pattern, $handler)
    {
        // 确保路由模式以/开头
        if ($pattern !== "/" && $pattern[0] !== "/") {
            $pattern = "/" . $pattern;
        }
        
        $this->routes[] = [
            "method" => $method,
            "pattern" => $pattern,
            "handler" => $handler
        ];
        
        return $this;
    }
    
    /**
     * 分发请求
     * 
     * @return mixed
     */
    public function dispatch()
    {
        // 遍历路由
        foreach ($this->routes as $route) {
            // 检查请求方法是否匹配
            if ($route["method"] !== "ANY" && $route["method"] !== $this->requestMethod) {
                continue;
            }
            
            // 检查路由模式是否匹配
            $pattern = $this->preparePattern($route["pattern"]);
            if (preg_match($pattern, $this->requestUri, $matches)) {
                // 提取路由参数
                array_shift($matches);
                
                // 处理路由
                return $this->handleRoute($route["handler"], $matches);
            }
        }
        
        // 未找到匹配的路由
        return $this->handleNotFound();
    }
    
    /**
     * 准备路由模式
     * 
     * @param string $pattern 路由模式
     * @return string
     */
    protected function preparePattern($pattern)
    {
        // 转换路由参数，例如 /users/{id} 转换为 /users/([^/]+)
        $pattern = preg_replace("/{([^}]+)}/", "([^/]+)", $pattern);
        
        // 添加开始和结束标记
        return "#^" . $pattern . "$#";
    }
    
    /**
     * 处理路由
     * 
     * @param mixed $handler 处理器
     * @param array $params 路由参数
     * @return mixed
     */
    protected function handleRoute($handler, array $params = [])
    {
        if (is_callable($handler)) {
            // 处理闭包
            return call_user_func_array($handler, $params);
        } elseif (is_string($handler) && strpos($handler, "@") !== false) {
            // 处理控制器@方法
            list($controller, $method) = explode("@", $handler);
            
            // 添加命名空间
            if (strpos($controller, "\\") === false) {
                $controller = "App\\Controllers\\" . $controller;
            }
            
            // 实例化控制器
            $controllerInstance = new $controller();
            
            // 调用方法
            return call_user_func_array([$controllerInstance, $method], $params);
        } elseif (is_array($handler) && count($handler) === 2) {
            // 处理[控制器, 方法]
            list($controller, $method) = $handler;
            
            // 如果控制器是字符串，则实例化它
            if (is_string($controller)) {
                // 添加命名空间
                if (strpos($controller, "\\") === false) {
                    $controller = "App\\Controllers\\" . $controller;
                }
                
                $controller = new $controller();
            }
            
            // 调用方法
            return call_user_func_array([$controller, $method], $params);
        }
        
        throw new \Exception("无效的路由处理器");
    }
    
    /**
     * 处理未找到路由
     * 
     * @return void
     */
    protected function handleNotFound()
    {
        header("HTTP/1.1 404 Not Found");
        include dirname(dirname(dirname(__DIR__))) . "/public/404.php";
        exit;
    }
}
