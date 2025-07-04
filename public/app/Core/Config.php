<?php
/**
 * 配置类
 * 
 * 负责加载和管理应用程序配置
 * 
 * @package App\Core
 */

namespace App\Core;

class Config
{
    /**
     * 存储配置数据的静态数组
     * 
     * @var array
     */
    private static $config = [];
    
    /**
     * 加载所有配置文件
     * 
     * @return void
     */
    public static function loadAll()
    {
        $configPath = dirname(dirname(dirname(__DIR__))) . "/config";
        
        // 加载基础配置
        if (file_exists($configPath . "/config.php")) {
            self::$config = require $configPath . "/config.php";
        }
        
        // 加载其他配置文件
        $configFiles = glob($configPath . "/*.php");
        foreach ($configFiles as $file) {
            $filename = basename($file, ".php");
            if ($filename !== "config") {
                $config = require $file;
                if (is_array($config)) {
                    self::$config[$filename] = $config;
                }
            }
        }
    }
    
    /**
     * 获取配置值
     * 
     * @param string|null $key 配置键，使用点语法访问嵌套配置，如 "app.debug"
     * @param mixed $default 默认值，如果配置不存在则返回此值
     * @return mixed
     */
    public static function get($key = null, $default = null)
    {
        if ($key === null) {
            return self::$config;
        }
        
        $keys = explode(".", $key);
        $value = self::$config;
        
        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        
        return $value;
    }
    
    /**
     * 设置配置值
     * 
     * @param string $key 配置键
     * @param mixed $value 配置值
     * @return void
     */
    public static function set($key, $value)
    {
        $keys = explode(".", $key);
        $config = &self::$config;
        
        foreach ($keys as $i => $segment) {
            if ($i === count($keys) - 1) {
                $config[$segment] = $value;
            } else {
                if (!isset($config[$segment]) || !is_array($config[$segment])) {
                    $config[$segment] = [];
                }
                $config = &$config[$segment];
            }
        }
    }
    
    /**
     * 检查配置是否存在
     * 
     * @param string $key 配置键
     * @return bool
     */
    public static function has($key)
    {
        $keys = explode(".", $key);
        $config = self::$config;
        
        foreach ($keys as $segment) {
            if (!is_array($config) || !array_key_exists($segment, $config)) {
                return false;
            }
            $config = $config[$segment];
        }
        
        return true;
    }
}
