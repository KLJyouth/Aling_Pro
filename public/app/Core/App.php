<?php
/**
 * 应用程序类
 * 
 * 负责启动和运行应用程序
 * 
 * @package App\Core
 */

namespace App\Core;

class App
{
    /**
     * 路由器实例
     * 
     * @var Router
     */
    protected $router;
    
    /**
     * 构造函数
     * 
     * @param Router $router 路由器实例
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
    
    /**
     * 运行应用程序
     * 
     * @return mixed
     */
    public function run()
    {
        try {
            // 分发请求
            return $this->router->dispatch();
        } catch (\Exception $e) {
            // 记录错误
            Logger::error("应用程序错误: " . $e->getMessage(), [
                "file" => $e->getFile(),
                "line" => $e->getLine(),
                "trace" => $e->getTraceAsString()
            ]);
            
            // 显示错误页面
            $this->handleError($e);
        }
    }
    
    /**
     * 处理错误
     * 
     * @param \Exception $e 异常
     * @return void
     */
    protected function handleError(\Exception $e)
    {
        // 设置HTTP状态码
        http_response_code(500);
        
        // 检查是否为AJAX请求
        if (!empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest") {
            // 返回JSON错误
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode([
                "error" => true,
                "message" => "服务器错误",
                "code" => 500
            ]);
        } else {
            // 显示错误页面
            $errorFile = dirname(dirname(dirname(__DIR__))) . "/public/error.php";
            
            if (file_exists($errorFile)) {
                include $errorFile;
            } else {
                echo "<h1>服务器错误</h1>";
                echo "<p>很抱歉，服务器遇到了一个错误，请稍后再试。</p>";
                
                // 在调试模式下显示详细错误信息
                if (Config::get("app.debug", false)) {
                    echo "<h2>错误详情</h2>";
                    echo "<p><strong>消息:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
                    echo "<p><strong>文件:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
                    echo "<p><strong>行号:</strong> " . $e->getLine() . "</p>";
                    echo "<h3>堆栈跟踪</h3>";
                    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
                }
            }
        }
        
        exit;
    }
}
