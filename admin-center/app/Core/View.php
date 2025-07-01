<?php
namespace App\Core;

/**
 * 视图类
 * 负责渲染视图文件
 */
class View
{
    /**
     * 共享数据，适用于所有视图
     * @var array
     */
    protected static $shared = [];
    
    /**
     * 视图数据
     * @var array
     */
    protected $data = [];
    
    /**
     * 设置视图数据
     * @param string $key 键名
     * @param mixed $value 值
     * @return $this
     */
    public function with($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }
    
    /**
     * 共享数据到所有视图
     * @param string $key 键名
     * @param mixed $value 值
     * @return void
     */
    public static function share($key, $value)
    {
        self::$shared[$key] = $value;
    }
    
    /**
     * 渲染视图
     * @param string $view 视图文件路径（相对于视图目录）
     * @param array $data 视图数据
     * @param bool $return 是否返回而不是输出
     * @return string|null 如果$return为true，则返回渲染结果
     */
    public function render($view, array $data = [], $return = false)
    {
        // 合并共享数据和视图数据
        $data = array_merge(self::$shared, $this->data, $data);
        
        // 将视图名称转换为文件路径
        $viewPath = $this->resolvePath($view);
        
        // 检查视图文件是否存在
        if (!file_exists($viewPath)) {
            throw new \Exception("视图文件不存在: {$viewPath}");
        }
        
        // 提取变量
        extract($data);
        
        // 开始输出缓冲
        ob_start();
        
        // 包含视图文件
        include $viewPath;
        
        // 获取渲染内容
        $content = ob_get_clean();
        
        // 如果需要返回，则返回内容
        if ($return) {
            return $content;
        }
        
        // 否则直接输出
        echo $content;
        return null;
    }
    
    /**
     * 渲染并返回视图内容
     * @param string $view 视图文件路径
     * @param array $data 视图数据
     * @return string 渲染结果
     */
    public function fetch($view, array $data = [])
    {
        return $this->render($view, $data, true);
    }
    
    /**
     * 静态渲染方法
     * @param string $view 视图文件路径
     * @param array $data 视图数据
     * @return void
     */
    public static function display($view, array $data = [])
    {
        $instance = new self();
        $instance->render($view, $data);
    }
    
    /**
     * 解析视图路径
     * @param string $view 视图名称，如 'auth.login'
     * @return string 完整的视图文件路径
     */
    protected function resolvePath($view)
    {
        // 将点号语法转换为目录分隔符
        $path = str_replace('.', '/', $view);
        
        // 构建完整路径
        return VIEWS_PATH . '/' . $path . '.php';
    }
    
    /**
     * 包含一个子视图
     * @param string $view 子视图名称
     * @param array $data 子视图数据
     * @return void
     */
    public static function include($view, array $data = [])
    {
        self::display($view, $data);
    }
    
    /**
     * 扩展布局视图
     * @param string $layout 布局视图名称
     * @param array $data 布局视图数据
     * @return void
     */
    public function extends($layout, array $data = [])
    {
        // 将当前渲染的内容保存，以便后续在布局中使用
        $content = ob_get_clean();
        
        // 将内容添加到数据中
        $data['content'] = $content;
        
        // 渲染布局视图
        $this->render($layout, $data);
        
        // 确保脚本终止
        exit;
    }
    
    /**
     * 转义HTML字符
     * @param string $value 要转义的字符串
     * @return string 转义后的字符串
     */
    public static function escape($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * 生成CSRF令牌输入字段
     * @return string HTML表单字段
     */
    public static function csrf()
    {
        if (class_exists('\\App\\Core\\Security')) {
            return Security::csrfField();
        }
        
        // 如果安全类不存在，使用简单的CSRF保护
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return '<input type="hidden" name="csrf_token" value="' . self::escape($_SESSION['csrf_token']) . '">';
    }
    
    /**
     * 生成表单方法覆盖输入字段
     * @param string $method HTTP方法（PUT, PATCH, DELETE）
     * @return string HTML表单字段
     */
    public static function method($method)
    {
        return '<input type="hidden" name="_method" value="' . self::escape($method) . '">';
    }
    
    /**
     * 获取旧输入值
     * @param string $key 输入字段名
     * @param mixed $default 默认值
     * @return mixed 旧输入值或默认值
     */
    public static function old($key, $default = '')
    {
        return $_SESSION['old_input'][$key] ?? $default;
    }
    
    /**
     * 检查是否有错误
     * @param string $field 字段名
     * @return bool 是否有错误
     */
    public static function hasError($field)
    {
        return isset($_SESSION['errors'][$field]);
    }
    
    /**
     * 获取错误消息
     * @param string $field 字段名
     * @return string|null 错误消息
     */
    public static function getError($field)
    {
        return $_SESSION['errors'][$field] ?? null;
    }
    
    /**
     * 获取所有错误
     * @return array 错误数组
     */
    public static function getErrors()
    {
        return $_SESSION['errors'] ?? [];
    }
    
    /**
     * 获取闪存消息
     * @param string $key 消息键名
     * @param mixed $default 默认值
     * @return mixed 闪存消息或默认值
     */
    public static function flash($key, $default = null)
    {
        $value = $_SESSION["flash_{$key}"] ?? $default;
        
        // 读取后删除闪存消息
        if (isset($_SESSION["flash_{$key}"])) {
            unset($_SESSION["flash_{$key}"]);
        }
        
        return $value;
    }
} 