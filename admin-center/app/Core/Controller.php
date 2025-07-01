<?php
namespace App\Core;

/**
 * 控制器基类
 * 所有控制器应该继承此类
 */
class Controller
{
    /**
     * 视图实例
     * @var View
     */
    protected $view;
    
    /**
     * 请求参数
     * @var array
     */
    protected $params = [];
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->view = new View();
        
        // 合并所有请求参数
        $this->params = array_merge($_GET, $_POST);
        
        // 如果是JSON请求，解析请求体
        if ($this->isJsonRequest()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            if ($data) {
                $this->params = array_merge($this->params, $data);
            }
        }
    }
    
    /**
     * 渲染视图
     * @param string $view 视图文件路径（相对于视图目录）
     * @param array $data 传递给视图的数据
     * @return void
     */
    protected function view($view, array $data = [])
    {
        $this->view->render($view, $data);
    }
    
    /**
     * 获取请求参数值
     * @param string $key 参数名
     * @param mixed $default 默认值
     * @return mixed 参数值
     */
    protected function input($key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }
    
    /**
     * 获取所有请求参数
     * @return array 所有参数
     */
    protected function all()
    {
        return $this->params;
    }
    
    /**
     * 获取指定的参数子集
     * @param array $keys 要获取的参数键名数组
     * @return array 参数子集
     */
    protected function only(array $keys)
    {
        return array_intersect_key($this->params, array_flip($keys));
    }
    
    /**
     * 获取除指定参数外的所有参数
     * @param array $keys 要排除的参数键名数组
     * @return array 参数子集
     */
    protected function except(array $keys)
    {
        return array_diff_key($this->params, array_flip($keys));
    }
    
    /**
     * 验证请求参数
     * @param array $rules 验证规则
     * @return array 验证结果，包含errors和validated两个键
     */
    protected function validate(array $rules)
    {
        $errors = [];
        $validated = [];
        
        foreach ($rules as $field => $rule) {
            $rules = explode('|', $rule);
            $value = $this->input($field);
            
            foreach ($rules as $singleRule) {
                $params = [];
                
                if (strpos($singleRule, ':') !== false) {
                    list($ruleName, $ruleParams) = explode(':', $singleRule, 2);
                    $params = explode(',', $ruleParams);
                } else {
                    $ruleName = $singleRule;
                }
                
                $valid = true;
                $errorMessage = '';
                
                switch ($ruleName) {
                    case 'required':
                        $valid = $value !== null && $value !== '';
                        $errorMessage = '必填字段';
                        break;
                        
                    case 'email':
                        $valid = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
                        $errorMessage = '无效的邮箱格式';
                        break;
                        
                    case 'numeric':
                        $valid = is_numeric($value);
                        $errorMessage = '必须是数字';
                        break;
                        
                    case 'min':
                        if (is_string($value)) {
                            $valid = mb_strlen($value) >= (int)$params[0];
                            $errorMessage = "长度不能小于{$params[0]}个字符";
                        } elseif (is_numeric($value)) {
                            $valid = $value >= (int)$params[0];
                            $errorMessage = "不能小于{$params[0]}";
                        }
                        break;
                        
                    case 'max':
                        if (is_string($value)) {
                            $valid = mb_strlen($value) <= (int)$params[0];
                            $errorMessage = "长度不能超过{$params[0]}个字符";
                        } elseif (is_numeric($value)) {
                            $valid = $value <= (int)$params[0];
                            $errorMessage = "不能大于{$params[0]}";
                        }
                        break;
                        
                    case 'alpha':
                        $valid = ctype_alpha($value);
                        $errorMessage = '只能包含字母';
                        break;
                        
                    case 'alpha_num':
                        $valid = ctype_alnum($value);
                        $errorMessage = '只能包含字母和数字';
                        break;
                        
                    case 'date':
                        $valid = strtotime($value) !== false;
                        $errorMessage = '无效的日期格式';
                        break;
                        
                    case 'in':
                        $valid = in_array($value, $params);
                        $errorMessage = '值不在允许的范围内';
                        break;
                }
                
                if (!$valid) {
                    if (!isset($errors[$field])) {
                        $errors[$field] = $errorMessage;
                    }
                    break;
                }
            }
            
            if (!isset($errors[$field])) {
                $validated[$field] = $value;
            }
        }
        
        return [
            'errors' => $errors,
            'validated' => $validated,
        ];
    }
    
    /**
     * 重定向到指定URL
     * @param string $url 目标URL
     * @param array $with 闪存数据
     * @return void
     */
    protected function redirect($url, array $with = [])
    {
        // 存储闪存数据
        if (!empty($with)) {
            foreach ($with as $key => $value) {
                $_SESSION["flash_{$key}"] = $value;
            }
        }
        
        header("Location: $url");
        exit;
    }
    
    /**
     * 返回上一页
     * @param array $with 闪存数据
     * @return void
     */
    protected function back(array $with = [])
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer, $with);
    }
    
    /**
     * 输出JSON响应
     * @param mixed $data 响应数据
     * @param int $statusCode HTTP状态码
     * @return void
     */
    protected function json($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * 输出成功的JSON响应
     * @param mixed $data 响应数据
     * @param string $message 成功消息
     * @param int $statusCode HTTP状态码
     * @return void
     */
    protected function success($data = null, $message = '操作成功', $statusCode = 200)
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        $this->json($response, $statusCode);
    }
    
    /**
     * 输出错误的JSON响应
     * @param string $message 错误消息
     * @param int $statusCode HTTP状态码
     * @param mixed $errors 错误详情
     * @return void
     */
    protected function error($message = '操作失败', $statusCode = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        $this->json($response, $statusCode);
    }
    
    /**
     * 检查是否为AJAX请求
     * @return bool 是否为AJAX请求
     */
    protected function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * 检查是否为JSON请求
     * @return bool 是否为JSON请求
     */
    protected function isJsonRequest()
    {
        return isset($_SERVER['CONTENT_TYPE']) && 
            strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
    }
    
    /**
     * 获取当前请求方法
     * @return string 请求方法
     */
    protected function method()
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
    
    /**
     * 检查请求方法是否为指定方法
     * @param string $method 要检查的方法
     * @return bool 是否匹配
     */
    protected function isMethod($method)
    {
        return strtoupper($this->method()) === strtoupper($method);
    }
    
    /**
     * 获取请求URI
     * @return string 请求URI
     */
    protected function getUri()
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }
    
    /**
     * 获取请求路径（不含查询字符串）
     * @return string 请求路径
     */
    protected function getPath()
    {
        $uri = $this->getUri();
        $position = strpos($uri, '?');
        if ($position !== false) {
            return substr($uri, 0, $position);
        }
        return $uri;
    }
} 