<?php
namespace App\Core;

/**
 * 配置管理类
 * 负责加载和管理应用程序配置
 */
class Config
{
    /**
     * 存储所有配置
     * @var array
     */
    private static $config = [];
    
    /**
     * 已加载的配置文件
     * @var array
     */
    private static $loaded = [];
    
    /**
     * 设置配置项
     * @param string $key 配置键名，支持点号语法
     * @param mixed $value 配置值
     * @return void
     */
    public static function set($key, $value)
    {
        $keys = explode('.', $key);
        $config = &self::$config;
        
        while (count($keys) > 1) {
            $current = array_shift($keys);
            if (!isset($config[$current]) || !is_array($config[$current])) {
                $config[$current] = [];
            }
            $config = &$config[$current];
        }
        
        $config[array_shift($keys)] = $value;
    }
    
    /**
     * 获取配置项
     * @param string|null $key 配置键名，支持点号语法，null表示获取所有配置
     * @param mixed $default 默认值，当配置项不存在时返回此值
     * @return mixed 配置值
     */
    public static function get($key = null, $default = null)
    {
        // 如果未提供键名，返回所有配置
        if ($key === null) {
            return self::$config;
        }
        
        // 支持点号语法
        $keys = explode('.', $key);
        $value = self::$config;
        
        foreach ($keys as $segment) {
            if (!isset($value[$segment])) {
                return $default;
            }
            $value = $value[$segment];
        }
        
        return $value;
    }
    
    /**
     * 检查配置项是否存在
     * @param string $key 配置键名，支持点号语法
     * @return bool 配置项是否存在
     */
    public static function has($key)
    {
        $keys = explode('.', $key);
        $value = self::$config;
        
        foreach ($keys as $segment) {
            if (!isset($value[$segment])) {
                return false;
            }
            $value = $value[$segment];
        }
        
        return true;
    }
    
    /**
     * 加载配置文件
     * @param string $file 配置文件名（不含扩展名）
     * @param string|null $key 存储配置的键名，默认为文件名
     * @return bool 是否加载成功
     */
    public static function load($file, $key = null)
    {
        // 如果配置已加载，直接返回
        if (isset(self::$loaded[$file])) {
            return true;
        }
        
        $path = CONFIG_PATH . '/' . $file . '.php';
        
        if (file_exists($path)) {
            $config = require $path;
            $key = $key ?: $file;
            self::set($key, $config);
            self::$loaded[$file] = true;
            return true;
        }
        
        return false;
    }
    
    /**
     * 加载目录下所有配置文件
     * @param string $directory 配置目录路径，默认为CONFIG_PATH
     * @return void
     */
    public static function loadAll($directory = null)
    {
        $directory = $directory ?: CONFIG_PATH;
        
        if (!is_dir($directory)) {
            return;
        }
        
        // 遍历目录下的所有PHP文件
        $files = glob($directory . '/*.php');
        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            self::load($filename);
        }
    }
    
    /**
     * 重载配置文件
     * @param string $file 配置文件名（不含扩展名）
     * @param string|null $key 存储配置的键名，默认为文件名
     * @return bool 是否重载成功
     */
    public static function reload($file, $key = null)
    {
        // 移除加载标记
        if (isset(self::$loaded[$file])) {
            unset(self::$loaded[$file]);
        }
        
        // 重新加载
        return self::load($file, $key);
    }
    
    /**
     * 将配置写入文件
     * @param string $file 配置文件名（不含扩展名）
     * @param string $key 要写入的配置键名
     * @return bool 是否写入成功
     */
    public static function write($file, $key)
    {
        $path = CONFIG_PATH . '/' . $file . '.php';
        $config = self::get($key);
        
        if ($config === null) {
            return false;
        }
        
        // 生成PHP配置文件内容
        $content = "<?php\n/**\n * {$file}配置文件\n */\n\nreturn " . self::varExport($config, true) . ";\n";
        
        // 写入文件
        try {
            if (file_put_contents($path, $content) !== false) {
                return true;
            }
        } catch (\Exception $e) {
            Logger::error('写入配置文件失败', [
                'file' => $file,
                'error' => $e->getMessage()
            ]);
        }
        
        return false;
    }
    
    /**
     * 导出变量为字符串（改进版var_export）
     * @param mixed $var 要导出的变量
     * @param bool $indent 是否缩进
     * @param int $level 当前缩进级别
     * @return string 导出的字符串
     */
    private static function varExport($var, $indent = false, $level = 0)
    {
        if (is_array($var)) {
            $spaces = str_repeat('    ', $level);
            $output = [];
            
            // 检查是否为关联数组
            $isAssoc = array_keys($var) !== range(0, count($var) - 1);
            
            foreach ($var as $key => $value) {
                $keyStr = $isAssoc ? "'" . addcslashes($key, "'") . "'" : $key;
                $output[] = $spaces . '    ' . $keyStr . ' => ' . self::varExport($value, $indent, $level + 1);
            }
            
            return "[\n" . implode(",\n", $output) . "\n" . $spaces . "]";
        } elseif (is_string($var)) {
            return "'" . addcslashes($var, "'") . "'";
        } elseif (is_bool($var)) {
            return $var ? 'true' : 'false';
        } elseif (is_null($var)) {
            return 'null';
        } else {
            return var_export($var, true);
        }
    }
    
    /**
     * 清除所有配置
     * @return void
     */
    public static function clear()
    {
        self::$config = [];
        self::$loaded = [];
    }
} 