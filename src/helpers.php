<?php

/**
 * AlingAi Pro 全局辅助函数
 * 
 * 包含框架常用的辅助函数，提供便捷的全局访问方式
 * 性能优化：使用静态缓存减少重复计算
 * 安全增强：添加数据过滤和验证函数
 */

if (!function_exists('app')) {
    /**
     * 获取应用实例
     *
     * @param string|null $abstract 要解析的抽象类型
     * @param array $parameters 参数
     * @return \AlingAi\Core\Application|mixed
     */
    function app($abstract = null, array $parameters = [])
    {
        global $app;
        
        if (is_null($abstract)) {
            return $app;
        }
        
        return $app->getContainer()->get($abstract, $parameters);
    }
}

if (!function_exists('config')) {
    /**
     * 获取配置值
     *
     * @param string $key 配置键
     * @param mixed $default 默认值
     * @return mixed
     */
    function config($key, $default = null)
    {
        static $config = null;
        static $configLoaded = false;
        
        if (!$configLoaded) {
            $configPath = __DIR__ . '/../config/config.php';
            $config = file_exists($configPath) ? require $configPath : [];
            $configLoaded = true;
            
            // 合并环境特定配置
            $env = env('APP_ENV', 'production');
            $envConfigPath = __DIR__ . "/../config/{$env}/config.php";
            if (file_exists($envConfigPath)) {
                $envConfig = require $envConfigPath;
                $config = array_merge($config, $envConfig);
            }
        }
        
        $keys = explode('.', $key);
        $value = $config;
        
        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        
        return $value;
    }
}

if (!function_exists('env')) {
    /**
     * 获取环境变量
     *
     * @param string $key 环境变量名
     * @param mixed $default 默认值
     * @return mixed
     */
    function env($key, $default = null)
    {
        static $cache = [];
        
        // 使用缓存避免重复调用getenv
        if (isset($cache[$key])) {
            return $cache[$key];
        }
        
        $value = getenv($key);
        
        if ($value === false) {
            $cache[$key] = $default;
            return $default;
        }
        
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                $cache[$key] = true;
                return true;
            case 'false':
            case '(false)':
                $cache[$key] = false;
                return false;
            case 'null':
            case '(null)':
                $cache[$key] = null;
                return null;
            case 'empty':
            case '(empty)':
                $cache[$key] = '';
                return '';
        }
        
        $cache[$key] = $value;
        return $value;
    }
}

if (!function_exists('base_path')) {
    /**
     * 获取基础路径
     *
     * @param string $path 相对路径
     * @return string
     */
    function base_path($path = '')
    {
        static $basePath = null;
        
        if ($basePath === null) {
            $basePath = realpath(__DIR__ . '/..');
        }
        
        return $path ? $basePath . '/' . $path : $basePath;
    }
}

if (!function_exists('storage_path')) {
    /**
     * 获取存储路径
     *
     * @param string $path 相对路径
     * @return string
     */
    function storage_path($path = '')
    {
        static $storagePath = null;
        
        if ($storagePath === null) {
            $storagePath = realpath(__DIR__ . '/../storage');
            
            // 确保存储目录存在
            if (!$storagePath && !is_dir($storagePath)) {
                $storagePath = __DIR__ . '/../storage';
                if (!is_dir($storagePath)) {
                    mkdir($storagePath, 0755, true);
                }
            }
        }
        
        return $path ? $storagePath . '/' . $path : $storagePath;
    }
}

if (!function_exists('public_path')) {
    /**
     * 获取公共路径
     *
     * @param string $path 相对路径
     * @return string
     */
    function public_path($path = '')
    {
        static $publicPath = null;
        
        if ($publicPath === null) {
            $publicPath = realpath(__DIR__ . '/../public');
        }
        
        return $path ? $publicPath . '/' . $path : $publicPath;
    }
}

if (!function_exists('json_response')) {
    /**
     * 创建JSON响应
     *
     * @param mixed $data 数据
     * @param int $status HTTP状态码
     * @param array $headers 额外的响应头
     * @return \Psr\Http\Message\ResponseInterface
     */
    function json_response($data, $status = 200, array $headers = [])
    {
        $response = app()->getContainer()->get('response');
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        
        $response = $response->withHeader('Content-Type', 'application/json')->withStatus($status);
        
        // 添加额外的响应头
        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        
        return $response;
    }
}

if (!function_exists('logger')) {
    /**
     * 获取日志记录器实例
     *
     * @param string $channel 日志通道
     * @return \Psr\Log\LoggerInterface
     */
    function logger($channel = 'app')
    {
        static $loggers = [];
        
        if (!isset($loggers[$channel])) {
            $loggers[$channel] = app()->getContainer()->get('logger')->channel($channel);
        }
        
        return $loggers[$channel];
    }
}

if (!function_exists('cache')) {
    /**
     * 获取缓存实例或缓存值
     *
     * @param string|null $key 缓存键
     * @param mixed $default 默认值
     * @return mixed|\AlingAi\Cache\CacheManager
     */
    function cache($key = null, $default = null)
    {
        $cache = app()->getContainer()->get('cache');
        
        if (is_null($key)) {
            return $cache;
        }
        
        return $cache->get($key, $default);
    }
}

if (!function_exists('session')) {
    /**
     * 获取会话实例或会话值
     *
     * @param string|null $key 会话键
     * @param mixed $default 默认值
     * @return mixed|\AlingAi\Core\Session
     */
    function session($key = null, $default = null)
    {
        $session = app()->getContainer()->get('session');
        
        if (is_null($key)) {
            return $session;
        }
        
        return $session->get($key, $default);
    }
}

if (!function_exists('request')) {
    /**
     * 获取当前请求实例
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    function request()
    {
        return app()->getContainer()->get('request');
    }
}

if (!function_exists('response')) {
    /**
     * 创建响应实例
     *
     * @param string $content 响应内容
     * @param int $status 状态码
     * @param array $headers 响应头
     * @return \Psr\Http\Message\ResponseInterface
     */
    function response($content = '', $status = 200, array $headers = [])
    {
        $response = app()->getContainer()->get('response')
            ->withStatus($status);
        
        if (!empty($content)) {
            $response->getBody()->write($content);
        }
        
        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        
        return $response;
    }
}

if (!function_exists('redirect')) {
    /**
     * 创建重定向响应
     *
     * @param string $url 重定向URL
     * @param int $status 状态码
     * @return \Psr\Http\Message\ResponseInterface
     */
    function redirect($url, $status = 302)
    {
        return response('', $status, ['Location' => $url]);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * 获取CSRF令牌
     *
     * @return string
     */
    function csrf_token()
    {
        $csrf = app()->getContainer()->get('csrf');
        return $csrf->getToken();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * 生成CSRF字段HTML
     *
     * @return string
     */
    function csrf_field()
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('e')) {
    /**
     * 转义HTML实体
     * 
     * 安全增强：防止XSS攻击
     *
     * @param string $value
     * @return string
     */
    function e($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }
}

if (!function_exists('clean')) {
    /**
     * 清理和过滤输入数据
     * 
     * 安全增强：过滤不安全的输入
     *
     * @param mixed $data 要清理的数据
     * @param bool $stripTags 是否移除HTML标签
     * @return mixed
     */
    function clean($data, $stripTags = true)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = clean($value, $stripTags);
            }
            return $data;
        }
        
        if (is_string($data)) {
            // 移除不可见字符
            $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $data);
            
            if ($stripTags) {
                $data = strip_tags($data);
            }
            
            // 移除多余空白
            $data = trim($data);
        }
        
        return $data;
    }
}

if (!function_exists('is_secure')) {
    /**
     * 检查当前请求是否安全(HTTPS)
     * 
     * 安全增强：帮助确定连接是否加密
     *
     * @return bool
     */
    function is_secure()
    {
        $request = request();
        $https = $request->getServerParams()['HTTPS'] ?? '';
        $forwardedProto = $request->getHeaderLine('X-Forwarded-Proto');
        
        return !empty($https) && $https !== 'off' || $forwardedProto === 'https';
    }
}

if (!function_exists('asset')) {
    /**
     * 生成资源URL
     *
     * @param string $path 资源路径
     * @param bool $secure 是否使用HTTPS
     * @return string
     */
    function asset($path, $secure = null)
    {
        $secure = $secure ?? is_secure();
        $root = config('app.url', '');
        
        if (empty($root)) {
            $request = request();
            $host = $request->getHeaderLine('Host') ?: $request->getServerParams()['HTTP_HOST'] ?? '';
            $scheme = $secure ? 'https' : 'http';
            $root = $scheme . '://' . $host;
        }
        
        return rtrim($root, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('now')) {
    /**
     * 获取当前时间
     *
     * @param string|null $timezone 时区
     * @return \DateTime
     */
    function now($timezone = null)
    {
        $timezone = $timezone ?: config('app.timezone', 'UTC');
        return new \DateTime('now', new \DateTimeZone($timezone));
    }
}

if (!function_exists('encrypt')) {
    /**
     * 加密数据
     * 
     * 安全增强：提供数据加密功能
     *
     * @param mixed $value 要加密的值
     * @return string|false
     */
    function encrypt($value)
    {
        $encrypter = app()->getContainer()->get('encrypter');
        return $encrypter->encrypt($value);
    }
}

if (!function_exists('decrypt')) {
    /**
     * 解密数据
     * 
     * 安全增强：提供数据解密功能
     *
     * @param string $value 要解密的值
     * @return mixed|false
     */
    function decrypt($value)
    {
        $encrypter = app()->getContainer()->get('encrypter');
        return $encrypter->decrypt($value);
    }
}

if (!function_exists('hash_password')) {
    /**
     * 哈希密码
     * 
     * 安全增强：提供安全的密码哈希功能
     *
     * @param string $password 原始密码
     * @return string
     */
    function hash_password($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}

if (!function_exists('verify_password')) {
    /**
     * 验证密码
     * 
     * 安全增强：提供安全的密码验证功能
     *
     * @param string $password 原始密码
     * @param string $hash 哈希值
     * @return bool
     */
    function verify_password($password, $hash)
    {
        return password_verify($password, $hash);
    }
}

if (!function_exists('generate_token')) {
    /**
     * 生成安全随机令牌
     * 
     * 安全增强：提供安全的随机令牌生成功能
     *
     * @param int $length 令牌长度
     * @return string
     */
    function generate_token($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }
}

if (!function_exists('is_ajax')) {
    /**
     * 检查是否为AJAX请求
     *
     * @return bool
     */
    function is_ajax()
    {
        $request = request();
        return $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }
}

if (!function_exists('is_json')) {
    /**
     * 检查是否为JSON请求
     *
     * @return bool
     */
    function is_json()
    {
        $request = request();
        $contentType = $request->getHeaderLine('Content-Type');
        return strpos($contentType, 'application/json') !== false;
    }
}

if (!function_exists('trans')) {
    /**
     * 翻译文本
     *
     * @param string $key 翻译键
     * @param array $replace 替换参数
     * @param string|null $locale 语言
     * @return string
     */
    function trans($key, array $replace = [], $locale = null)
    {
        static $translations = [];
        
        $locale = $locale ?: config('app.locale', 'en');
        
        if (!isset($translations[$locale])) {
            $path = __DIR__ . "/../resources/lang/{$locale}.php";
            $translations[$locale] = file_exists($path) ? require $path : [];
        }
        
        $keys = explode('.', $key);
        $value = $translations[$locale];
        
        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $key;
            }
            $value = $value[$segment];
        }
        
        if (!is_string($value)) {
            return $key;
        }
        
        if (!empty($replace)) {
            foreach ($replace as $k => $v) {
                $value = str_replace(":{$k}", $v, $value);
            }
        }
        
        return $value;
    }
}

if (!function_exists('ai_service')) {
    /**
     * 获取AI服务实例
     * 
     * AI功能扩展：提供便捷访问AI服务的方法
     *
     * @param string $service 服务名称
     * @return mixed
     */
    function ai_service($service)
    {
        $aiManager = app()->getContainer()->get('ai.manager');
        return $aiManager->service($service);
    }
}

if (!function_exists('memory_usage')) {
    /**
     * 获取内存使用情况
     * 
     * 性能优化：帮助监控内存使用
     *
     * @param bool $realUsage 是否获取真实使用量
     * @return string 格式化的内存使用量
     */
    function memory_usage($realUsage = false)
    {
        $memory = memory_get_usage($realUsage);
        
        if ($memory < 1024) {
            return $memory . ' B';
        } elseif ($memory < 1048576) {
            return round($memory / 1024, 2) . ' KB';
        } else {
            return round($memory / 1048576, 2) . ' MB';
        }
    }
}

if (!function_exists('execution_time')) {
    /**
     * 获取脚本执行时间
     * 
     * 性能优化：帮助监控执行时间
     *
     * @return float 执行时间（秒）
     */
    function execution_time()
    {
        static $startTime = null;
        
        if ($startTime === null) {
            $startTime = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
        }
        
        return microtime(true) - $startTime;
    }
}

if (!function_exists('ai_analyze')) {
    /**
     * 使用AI分析文本内容
     * 
     * AI功能扩展：提供便捷的文本分析功能
     *
     * @param string $text 要分析的文本
     * @param string $type 分析类型 (sentiment|entities|keywords|summary)
     * @param array $options 分析选项
     * @return mixed
     */
    function ai_analyze($text, $type = 'sentiment', array $options = [])
    {
        $aiService = ai_service('nlp');
        return $aiService->analyze($text, $type, $options);
    }
}

if (!function_exists('ai_generate')) {
    /**
     * 使用AI生成内容
     * 
     * AI功能扩展：提供便捷的内容生成功能
     *
     * @param string $prompt 提示词
     * @param array $options 生成选项
     * @return string
     */
    function ai_generate($prompt, array $options = [])
    {
        $aiService = ai_service('generator');
        return $aiService->generate($prompt, $options);
    }
}

if (!function_exists('ai_translate')) {
    /**
     * 使用AI翻译文本
     * 
     * AI功能扩展：提供便捷的翻译功能
     *
     * @param string $text 要翻译的文本
     * @param string $targetLang 目标语言
     * @param string $sourceLang 源语言 (自动检测为null)
     * @return string
     */
    function ai_translate($text, $targetLang, $sourceLang = null)
    {
        $aiService = ai_service('translator');
        return $aiService->translate($text, $targetLang, $sourceLang);
    }
}

if (!function_exists('ai_image_analyze')) {
    /**
     * 使用AI分析图像
     * 
     * AI功能扩展：提供便捷的图像分析功能
     *
     * @param string $imagePath 图像路径或URL
     * @param string $type 分析类型 (objects|faces|text|nsfw|scene)
     * @param array $options 分析选项
     * @return mixed
     */
    function ai_image_analyze($imagePath, $type = 'objects', array $options = [])
    {
        $aiService = ai_service('vision');
        return $aiService->analyze($imagePath, $type, $options);
    }
}

if (!function_exists('ai_image_generate')) {
    /**
     * 使用AI生成图像
     * 
     * AI功能扩展：提供便捷的图像生成功能
     *
     * @param string $prompt 提示词
     * @param array $options 生成选项
     * @return string 生成的图像路径
     */
    function ai_image_generate($prompt, array $options = [])
    {
        $aiService = ai_service('image_generator');
        return $aiService->generate($prompt, $options);
    }
}

if (!function_exists('ai_chat')) {
    /**
     * 使用AI进行对话
     * 
     * AI功能扩展：提供便捷的对话功能
     *
     * @param string $message 用户消息
     * @param string $sessionId 会话ID
     * @param array $options 对话选项
     * @return string AI回复
     */
    function ai_chat($message, $sessionId = null, array $options = [])
    {
        $aiService = ai_service('chat');
        return $aiService->reply($message, $sessionId, $options);
    }
}

if (!function_exists('ai_audio_transcribe')) {
    /**
     * 使用AI转录音频
     * 
     * AI功能扩展：提供便捷的音频转录功能
     *
     * @param string $audioPath 音频文件路径
     * @param array $options 转录选项
     * @return string 转录文本
     */
    function ai_audio_transcribe($audioPath, array $options = [])
    {
        $aiService = ai_service('speech');
        return $aiService->transcribe($audioPath, $options);
    }
}

if (!function_exists('ai_text_to_speech')) {
    /**
     * 使用AI将文本转换为语音
     * 
     * AI功能扩展：提供便捷的文本到语音功能
     *
     * @param string $text 要转换的文本
     * @param array $options 转换选项
     * @return string 生成的音频文件路径
     */
    function ai_text_to_speech($text, array $options = [])
    {
        $aiService = ai_service('speech');
        return $aiService->synthesize($text, $options);
    }
}

if (!function_exists('ai_agent')) {
    /**
     * 获取智能代理实例
     * 
     * AI功能扩展：提供便捷的智能代理访问
     *
     * @param string $agentName 代理名称
     * @param array $options 代理选项
     * @return \AlingAi\AI\Agent\AgentInterface
     */
    function ai_agent($agentName, array $options = [])
    {
        $agentManager = app()->getContainer()->get('ai.agent_manager');
        return $agentManager->get($agentName, $options);
    }
}

if (!function_exists('ai_task')) {
    /**
     * 创建并执行AI任务
     * 
     * AI功能扩展：提供便捷的AI任务执行功能
     *
     * @param string $taskType 任务类型
     * @param array $params 任务参数
     * @param bool $async 是否异步执行
     * @return mixed
     */
    function ai_task($taskType, array $params = [], $async = false)
    {
        $taskManager = app()->getContainer()->get('ai.task_manager');
        $task = $taskManager->create($taskType, $params);
        
        if ($async) {
            return $taskManager->scheduleAsync($task);
        }
        
        return $taskManager->execute($task);
    }
}