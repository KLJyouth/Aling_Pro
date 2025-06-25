<?php
namespace App\Core;

/**
 * 基础控制器类
 * 所有控制器的父类
 */
class Controller
{
    /**
     * 渲染视图
     * @param string $view 视图名称
     * @param array $data 视图数据
     * @return void
     */
    protected function view($view, $data = [])
    {
        // 提取数据到变量
        extract($data);
        
        // 构建视图文件路径
        $viewPath = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        
        // 检查视图文件是否存在
        if (!file_exists($viewPath)) {
            throw new \Exception("View {$view} not found");
        }
        
        // 启动输出缓冲
        ob_start();
        
        // 包含视图文件
        include $viewPath;
        
        // 获取缓冲内容
        $content = ob_get_clean();
        
        // 输出内容
        echo $content;
    }
    
    /**
     * 重定向到指定URL
     * @param string $url 目标URL
     * @return void
     */
    protected function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }
    
    /**
     * 返回JSON响应
     * @param mixed $data 响应数据
     * @param int $statusCode HTTP状态码
     * @return void
     */
    protected function json($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
    
    /**
     * 获取请求参数
     * @param string $key 参数名
     * @param mixed $default 默认值
     * @return mixed 参数值
     */
    protected function input($key, $default = null)
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
     * 验证请求参数
     * @param array $rules 验证规则
     * @return array 错误信息
     */
    protected function validate($rules)
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            // 分割规则
            $ruleItems = explode('|', $rule);
            
            foreach ($ruleItems as $ruleItem) {
                // 检查是否为必填
                if ($ruleItem === 'required') {
                    if (!isset($_REQUEST[$field]) || empty($_REQUEST[$field])) {
                        $errors[$field][] = "{$field} 是必填项";
                    }
                }
                
                // 如果字段不存在且不是必填，跳过其他验证
                if (!isset($_REQUEST[$field])) {
                    continue;
                }
                
                // 检查最小长度
                if (strpos($ruleItem, 'min:') === 0) {
                    $min = substr($ruleItem, 4);
                    if (strlen($_REQUEST[$field]) < $min) {
                        $errors[$field][] = "{$field} 长度不能小于 {$min} 个字符";
                    }
                }
                
                // 检查最大长度
                if (strpos($ruleItem, 'max:') === 0) {
                    $max = substr($ruleItem, 4);
                    if (strlen($_REQUEST[$field]) > $max) {
                        $errors[$field][] = "{$field} 长度不能大于 {$max} 个字符";
                    }
                }
                
                // 检查是否为邮箱
                if ($ruleItem === 'email') {
                    if (!filter_var($_REQUEST[$field], FILTER_VALIDATE_EMAIL)) {
                        $errors[$field][] = "{$field} 必须是有效的电子邮件地址";
                    }
                }
            }
        }
        
        return $errors;
    }
} 