<?php
/**
 * 环境变量加载器
 */

namespace AlingAi\Utils;

class EnvLoader
{
    private static $loaded = false;
      public static function load($envFile = null)
    {
        if (self::$loaded) {
            return;
        }
        
        if ($envFile === null) {
            // 尝试多个可能的位置
            $possiblePaths = [
                __DIR__ . '/../../.env',
                dirname(dirname(__DIR__)) . '/.env',
                getcwd() . '/.env'
            ];
            
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    $envFile = $path;
                    break;
                }
            }
        }
        
        if (!$envFile || !file_exists($envFile)) {
            // 如果找不到.env文件，不抛出异常，而是继续使用默认值
            self::$loaded = true;
            return;
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // 跳过注释和空行
            if (empty($line) || $line[0] === '#') {
                continue;
            }
            
            // 解析 KEY=VALUE 格式
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // 去除引号
                if (preg_match('/^"(.*)"$/', $value, $matches)) {
                    $value = $matches[1];
                } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
                    $value = $matches[1];
                }
                
                // 处理 base64: 前缀
                if (strpos($value, 'base64:') === 0) {
                    $value = base64_decode(substr($value, 7));
                }
                
                // 设置环境变量
                if (!getenv($key)) {
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                }
            }
        }
        
        self::$loaded = true;
    }
    
    public static function get($key, $default = null)
    {
        self::load();
        
        $value = getenv($key);
        if ($value === false) {
            $value = $_ENV[$key] ?? $default;
        }
        
        // 转换布尔值
        if (is_string($value)) {
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
            }
        }
        
        return $value;
    }
}
