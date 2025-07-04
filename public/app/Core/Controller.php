<?php
/**
 * 控制器基类
 * 
 * 所有控制器的基类
 * 
 * @package App\Core
 */

namespace App\Core;

class Controller
{
    /**
     * 视图实例
     * 
     * @var View
     */
    protected $view;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->view = new View();
    }
    
    /**
     * 渲染视图
     * 
     * @param string $template 模板文件
     * @param array $data 视图数据
     * @return string
     */
    protected function render($template, array $data = [])
    {
        return $this->view->render($template, $data);
    }
    
    /**
     * 返回JSON响应
     * 
     * @param mixed $data 响应数据
     * @param int $statusCode HTTP状态码
     * @return string
     */
    protected function json($data, $statusCode = 200)
    {
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code($statusCode);
        return json_encode($data);
    }
    
    /**
     * 重定向到指定URL
     * 
     * @param string $url 目标URL
     * @param int $statusCode HTTP状态码
     * @return void
     */
    protected function redirect($url, $statusCode = 302)
    {
        header("Location: {$url}", true, $statusCode);
        exit;
    }
    
    /**
     * 获取请求参数
     * 
     * @param string $key 参数名
     * @param mixed $default 默认值
     * @return mixed
     */
    protected function getParam($key, $default = null)
    {
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }
        
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }
        
        return $default;
    }
    
    /**
     * 获取POST参数
     * 
     * @param string $key 参数名
     * @param mixed $default 默认值
     * @return mixed
     */
    protected function getPostParam($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }
    
    /**
     * 获取GET参数
     * 
     * @param string $key 参数名
     * @param mixed $default 默认值
     * @return mixed
     */
    protected function getQueryParam($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }
    
    /**
     * 验证请求方法
     * 
     * @param string $method 请求方法
     * @return bool
     */
    protected function isMethod($method)
    {
        return strtoupper($_SERVER["REQUEST_METHOD"]) === strtoupper($method);
    }
    
    /**
     * 检查是否为POST请求
     * 
     * @return bool
     */
    protected function isPost()
    {
        return $this->isMethod("POST");
    }
    
    /**
     * 检查是否为GET请求
     * 
     * @return bool
     */
    protected function isGet()
    {
        return $this->isMethod("GET");
    }
    
    /**
     * 检查是否为AJAX请求
     * 
     * @return bool
     */
    protected function isAjax()
    {
        return !empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && 
               strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest";
    }
    
    /**
     * 获取请求头
     * 
     * @param string $name 头名称
     * @return string|null
     */
    protected function getHeader($name)
    {
        $headerName = "HTTP_" . strtoupper(str_replace("-", "_", $name));
        return $_SERVER[$headerName] ?? null;
    }
}
