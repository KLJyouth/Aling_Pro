<?php
/**
 * AlingAi Pro 辅助函数文件
 * 
 * 提供系统所需的通用辅助函数
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */

if (!function_exists('env')) {
    /**
     * 获取环境变量的值
     * 
     * @param string $key 环境变量名称
     * @param mixed $default 默认值
     * @return mixed 环境变量值或默认值
     */
    function env($key, $default = null) {
        $value = getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case 'empty':
            case '(empty)':
                return '';
        }
        
        return $value;
    }
}

if (!function_exists('config')) {
    /**
     * 获取配置值
     * 
     * @param string $key 配置键名
     * @param mixed $default 默认值
     * @return mixed 配置值或默认值
     */
    function config($key, $default = null) {
        if (class_exists('\App\Core\Config')) {
            return \App\Core\Config::get($key, $default);
        }
        
        return $default;
    }
}

if (!function_exists('base_path')) {
    /**
     * 获取应用根路径
     * 
     * @param string $path 相对路径
     * @return string 完整路径
     */
    function base_path($path = '') {
        return dirname(dirname(dirname(__DIR__))) . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('app_path')) {
    /**
     * 获取应用路径
     * 
     * @param string $path 相对路径
     * @return string 完整路径
     */
    function app_path($path = '') {
        return base_path('app') . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('storage_path')) {
    /**
     * 获取存储路径
     * 
     * @param string $path 相对路径
     * @return string 完整路径
     */
    function storage_path($path = '') {
        return base_path('storage') . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('public_path')) {
    /**
     * 获取公共路径
     * 
     * @param string $path 相对路径
     * @return string 完整路径
     */
    function public_path($path = '') {
        return base_path('public') . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('redirect')) {
    /**
     * 重定向到指定URL
     * 
     * @param string $url 重定向URL
     * @param int $status 状态码
     * @return void
     */
    function redirect($url, $status = 302) {
        header('Location: ' . $url, true, $status);
        exit;
    }
}

if (!function_exists('e')) {
    /**
     * HTML实体转义
     * 
     * @param string $value 需要转义的字符串
     * @return string 转义后的字符串
     */
    function e($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }
}

if (!function_exists('asset')) {
    /**
     * 获取资源URL
     * 
     * @param string $path 资源路径
     * @return string 资源URL
     */
    function asset($path) {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('csrf_token')) {
    /**
     * 获取CSRF令牌
     * 
     * @return string CSRF令牌
     */
    function csrf_token() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * 生成CSRF字段HTML
     * 
     * @return string CSRF字段HTML
     */
    function csrf_field() {
        return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('method_field')) {
    /**
     * 生成HTTP方法字段HTML
     * 
     * @param string $method HTTP方法
     * @return string 方法字段HTML
     */
    function method_field($method) {
        return '<input type="hidden" name="_method" value="' . $method . '">';
    }
}

if (!function_exists('old')) {
    /**
     * 获取旧输入值
     * 
     * @param string $key 输入键名
     * @param mixed $default 默认值
     * @return mixed 旧输入值或默认值
     */
    function old($key, $default = '') {
        return $_SESSION['old_input'][$key] ?? $default;
    }
}

if (!function_exists('logger')) {
    /**
     * 记录日志
     * 
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    function logger($message, array $context = []) {
        if (class_exists('\App\Core\Logger')) {
            \App\Core\Logger::info($message, $context);
        } else {
            error_log($message);
        }
    }
}

if (!function_exists('dd')) {
    /**
     * 打印变量并终止脚本执行
     * 
     * @param mixed $vars 要打印的变量
     * @return void
     */
    function dd(...$vars) {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        
        exit(1);
    }
}
