<?php
namespace App\Core;

/**
 * 配置加载器类
 * 负责加载和管理应用配置
 */
class Config
{
    /**
     * 存储配置数据
     * @var array
     */
    private static $config = [];
    
    /**
     * 加载配置文件
     * @param string $file 配置文件路径（不包含扩展名）
     * @return array 配置数据
     */
    public static function load($file)
    {
        $filePath = CONFIG_PATH . '/' . $file . '.php';
        
        if (file_exists($filePath)) {
            $config = require $filePath;
            
            if (is_array($config)) {
                // 将配置合并到全局配置中
                self::$config = array_merge(self::$config, $config);
                return $config;
            }
        }
        
        return [];
    }
    
    /**
     * 获取配置项
     * @param string $key 配置键名，支持点号分隔多级键名，如'app.name'
     * @param mixed $default 默认值
     * @return mixed 配置值或默认值
     */
    public static function get($key = null, $default = null)
    {
        if ($key === null) {
            return self::$config;
        }
        
        // 处理点号分隔的键名
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $value = self::$config;
            
            foreach ($keys as $k) {
                if (!isset($value[$k])) {
                    return $default;
                }
                
                $value = $value[$k];
            }
            
            return $value;
        }
        
        return self::$config[$key] ?? $default;
    }
    
    /**
     * 设置配置项
     * @param string|array $key 配置键名或配置数组
     * @param mixed $value 配置值
     * @return void
     */
    public static function set($key, $value = null)
    {
        if (is_array($key)) {
            self::$config = array_merge(self::$config, $key);
            return;
        }
        
        // 处理点号分隔的键名
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $config = &self::$config;
            
            foreach ($keys as $i => $k) {
                if ($i === count($keys) - 1) {
                    $config[$k] = $value;
                } else {
                    if (!isset($config[$k]) || !is_array($config[$k])) {
                        $config[$k] = [];
                    }
                    
                    $config = &$config[$k];
                }
            }
        } else {
            self::$config[$key] = $value;
        }
    }
    
    /**
     * 检查配置项是否存在
     * @param string $key 配置键名
     * @return bool 是否存在
     */
    public static function has($key)
    {
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $value = self::$config;
            
            foreach ($keys as $k) {
                if (!isset($value[$k])) {
                    return false;
                }
                
                $value = $value[$k];
            }
            
            return true;
        }
        
        return isset(self::$config[$key]);
    }
    
    /**
     * 加载所有配置文件
     * @param string $path 配置文件目录
     * @return void
     */
    public static function loadAll($path = null)
    {
        $path = $path ?? CONFIG_PATH;
        
        if (!is_dir($path)) {
            return;
        }
        
        $files = scandir($path);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $name = pathinfo($file, PATHINFO_FILENAME);
                self::load($name);
            }
        }
    }
} 