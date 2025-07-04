<?php
/**
 * 视图类
 * 
 * 负责渲染视图模板
 * 
 * @package App\Core
 */

namespace App\Core;

class View
{
    /**
     * 视图数据
     * 
     * @var array
     */
    protected $data = [];
    
    /**
     * 视图路径
     * 
     * @var string
     */
    protected $viewPath;
    
    /**
     * 布局文件
     * 
     * @var string|null
     */
    protected $layout = null;
    
    /**
     * 构造函数
     * 
     * @param string|null $viewPath 视图路径
     */
    public function __construct($viewPath = null)
    {
        if ($viewPath === null) {
            $this->viewPath = dirname(dirname(dirname(__DIR__))) . "/resources/views";
        } else {
            $this->viewPath = $viewPath;
        }
    }
    
    /**
     * 设置视图数据
     * 
     * @param string $key 数据键
     * @param mixed $value 数据值
     * @return $this
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }
    
    /**
     * 批量设置视图数据
     * 
     * @param array $data 数据数组
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }
    
    /**
     * 设置布局文件
     * 
     * @param string|null $layout 布局文件名
     * @return $this
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }
    
    /**
     * 渲染视图
     * 
     * @param string $template 模板文件
     * @param array $data 视图数据
     * @return string
     */
    public function render($template, array $data = [])
    {
        // 合并数据
        $data = array_merge($this->data, $data);
        
        // 提取变量
        extract($data);
        
        // 启用输出缓冲
        ob_start();
        
        // 加载模板文件
        $templateFile = $this->viewPath . "/" . $template . ".php";
        
        if (!file_exists($templateFile)) {
            throw new \Exception("视图文件不存在: {$templateFile}");
        }
        
        include $templateFile;
        
        // 获取内容
        $content = ob_get_clean();
        
        // 如果设置了布局，则渲染布局
        if ($this->layout !== null) {
            // 将内容存储在变量中，以便在布局中使用
            $pageContent = $content;
            
            // 启用输出缓冲
            ob_start();
            
            // 加载布局文件
            $layoutFile = $this->viewPath . "/layouts/" . $this->layout . ".php";
            
            if (!file_exists($layoutFile)) {
                throw new \Exception("布局文件不存在: {$layoutFile}");
            }
            
            include $layoutFile;
            
            // 获取最终内容
            $content = ob_get_clean();
        }
        
        return $content;
    }
    
    /**
     * 包含子视图
     * 
     * @param string $template 模板文件
     * @param array $data 视图数据
     * @return void
     */
    public function include($template, array $data = [])
    {
        echo $this->render($template, $data);
    }
    
    /**
     * 转义HTML
     * 
     * @param string $value 需要转义的字符串
     * @return string
     */
    public function escape($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
    }
    
    /**
     * 格式化日期
     * 
     * @param string $date 日期字符串
     * @param string $format 格式
     * @return string
     */
    public function formatDate($date, $format = "Y-m-d H:i:s")
    {
        return date($format, strtotime($date));
    }
    
    /**
     * 生成URL
     * 
     * @param string $path 路径
     * @param array $params 查询参数
     * @return string
     */
    public function url($path, array $params = [])
    {
        $url = $path;
        
        if (!empty($params)) {
            $url .= "?" . http_build_query($params);
        }
        
        return $url;
    }
    
    /**
     * 生成CSRF令牌
     * 
     * @param string $formId 表单ID
     * @return string
     */
    public function csrf($formId = "global")
    {
        return Security::generateCsrfToken($formId);
    }
    
    /**
     * 生成CSRF令牌字段
     * 
     * @param string $formId 表单ID
     * @return string
     */
    public function csrfField($formId = "global")
    {
        $token = $this->csrf($formId);
        return "<input type=\"hidden\" name=\"csrf_token\" value=\"{$token}\">";
    }
}
